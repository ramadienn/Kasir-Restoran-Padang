<?php
session_start();
include 'koneksi.php'; // Pastikan path ke koneksi.php benar

// Cek apakah user sudah login dan perannya adalah petugas
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Inisialisasi keranjang belanja di session jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Inisialisasi variabel pesan sukses/error
$success_message = "";
$error_message = "";

// **START: Tangani permintaan AJAX untuk mengupdate status pengiriman**
if (isset($_POST['action']) && $_POST['action'] === 'update_delivery_status') {
    header('Content-Type: application/json'); // Penting untuk respons JSON

    $transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    if ($transaction_id > 0 && ($status === 'pending' || $status === 'delivered')) {
        $stmt = $conn->prepare("UPDATE transactions SET delivery_status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $status, $transaction_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Status pengiriman berhasil diperbarui.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status di database: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyiapkan statement: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
    }
    $conn->close(); // Tutup koneksi karena ini adalah AJAX request
    exit(); // Penting untuk menghentikan eksekusi setelah respons JSON
}
// **END: Tangani permintaan AJAX**


// Ambil daftar menu dari database
$sql_menus = "SELECT id, nama_menu, harga, gambar_url FROM menus ORDER BY nama_menu ASC";
$result_menus = $conn->query($sql_menus);

// **START: Ambil data pesanan aktif (pending) untuk tampilan "Pesanan Perlu Diantar"**
$active_transactions = [];
$sql_active_transactions = "SELECT t.id, t.total_amount, t.transaction_date, t.customer_name, t.table_number, u.username AS cashier_name
                            FROM transactions t
                            JOIN users u ON t.user_id = u.id
                            WHERE t.delivery_status = 'pending'
                            ORDER BY t.transaction_date ASC"; // Urutkan dari yang paling lama

$stmt_active = $conn->prepare($sql_active_transactions);
if ($stmt_active) {
    $stmt_active->execute();
    $result_active = $stmt_active->get_result();
    while ($row = $result_active->fetch_assoc()) {
        $active_transactions[] = $row;
    }
    $stmt_active->close();
} else {
    // Handle error jika query gagal
    error_log("Error preparing active transactions statement: " . $conn->error); // Log error untuk debugging
}
// **END: Ambil data pesanan aktif**


// Tangani penambahan menu ke keranjang
if (isset($_POST['add_to_cart'])) {
    $menu_id = $_POST['menu_id'];
    $menu_name = $_POST['menu_name'];
    $menu_price = $_POST['menu_price'];

    if (isset($_SESSION['cart'][$menu_id])) {
        $_SESSION['cart'][$menu_id]['quantity']++;
        $_SESSION['cart'][$menu_id]['total_price'] = $_SESSION['cart'][$menu_id]['quantity'] * $_SESSION['cart'][$menu_id]['price'];
    } else {
        $_SESSION['cart'][$menu_id] = [
            'id' => $menu_id,
            'name' => $menu_name,
            'price' => $menu_price,
            'quantity' => 1,
            'total_price' => $menu_price
        ];
    }
    header("Location: dashboard_petugas.php"); // Redirect untuk mencegah resubmission form
    exit();
}

// Tangani update kuantitas di keranjang
if (isset($_POST['update_cart'])) {
    $menu_id = $_POST['menu_id'];
    $new_quantity = max(1, (int)$_POST['quantity']); // Pastikan kuantitas minimal 1

    if (isset($_SESSION['cart'][$menu_id])) {
        $_SESSION['cart'][$menu_id]['quantity'] = $new_quantity;
        $_SESSION['cart'][$menu_id]['total_price'] = $new_quantity * $_SESSION['cart'][$menu_id]['price'];
    }
    header("Location: dashboard_petugas.php");
    exit();
}

// Tangani penghapusan item dari keranjang
if (isset($_POST['remove_from_cart'])) {
    $menu_id = $_POST['menu_id'];
    if (isset($_SESSION['cart'][$menu_id])) {
        unset($_SESSION['cart'][$menu_id]);
    }
    header("Location: dashboard_petugas.php");
    exit();
}

// Tangani proses pembayaran
if (isset($_POST['process_payment'])) {
    if (!empty($_SESSION['cart'])) {
        $total_amount = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_amount += $item['total_price'];
        }
        $payment_method = $_POST['payment_method'];
        $customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : NULL;
        $table_number = isset($_POST['table_number']) ? trim($_POST['table_number']) : NULL;

        // Ambil Uang Dibayar dari input form dan hitung kembalian
        $uang_dibayar_str = $_POST['uang_dibayar'] ?? '0';
        // Hapus titik ribuan (jika user input 10.000 menjadi 10000)
        $uang_dibayar = floatval(str_replace('.', '', $uang_dibayar_str));
        $kembalian = $uang_dibayar - $total_amount;

        // Validasi uang dibayar (hanya untuk metode 'Tunai')
        if ($payment_method == 'Tunai' && $uang_dibayar < $total_amount) {
            $_SESSION['transaction_error'] = "Uang yang dibayarkan kurang dari total belanja. Kurang Rp " . number_format(abs($kembalian), 0, ',', '.');
            header("Location: dashboard_petugas.php");
            exit();
        }

        // Mulai transaksi database
        $conn->begin_transaction();
        try {
            // Masukkan data ke tabel transactions, termasuk uang_dibayar, kembalian, dan delivery_status
            $sql_insert_transaction = "INSERT INTO transactions (user_id, total_amount, uang_dibayar, kembalian, payment_method, customer_name, table_number, delivery_status, transaction_date) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
            $stmt_transaction = $conn->prepare($sql_insert_transaction);
            if ($stmt_transaction === false) {
                throw new mysqli_sql_exception("Prepare transaction failed: " . $conn->error);
            }
            // Perhatikan string tipe: "idddsss" (integer, decimal, decimal, decimal, string, string, string)
            $stmt_transaction->bind_param("idddsss", $_SESSION['user_id'], $total_amount, $uang_dibayar, $kembalian, $payment_method, $customer_name, $table_number);
            $stmt_transaction->execute();
            $transaction_id = $conn->insert_id; // Dapatkan ID transaksi yang baru dibuat
            $_SESSION['last_transaction_id'] = $transaction_id; // Simpan ID transaksi untuk cetak struk
            $stmt_transaction->close();

            // Masukkan detail item ke tabel transaction_items
            $sql_insert_item = "INSERT INTO transaction_items (transaction_id, menu_id, quantity, price_per_unit, subtotal) VALUES (?, ?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_insert_item);
            if ($stmt_item === false) {
                throw new mysqli_sql_exception("Prepare item failed: " . $conn->error);
            }

            foreach ($_SESSION['cart'] as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $stmt_item->bind_param("iiidd", $transaction_id, $item['id'], $item['quantity'], $item['price'], $subtotal);
                $stmt_item->execute();
            }
            $stmt_item->close();

            $conn->commit(); // Komit transaksi

            // Set pesan sukses dengan detail kembalian untuk ditampilkan
            $_SESSION['payment_success_details'] = [
                'total' => $total_amount,
                'uang_dibayar' => $uang_dibayar,
                'kembalian' => $kembalian
            ];
            $_SESSION['cart'] = []; // Kosongkan keranjang

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback(); // Rollback jika ada error
            $_SESSION['transaction_error'] = "Terjadi kesalahan database saat memproses pembayaran: " . $exception->getMessage();
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['transaction_error'] = "Terjadi kesalahan umum: " . $e->getMessage();
        }
    } else {
        $_SESSION['transaction_error'] = "Keranjang belanja kosong!";
    }
    header("Location: dashboard_petugas.php"); // Redirect untuk mencegah resubmission form
    exit();
}

// Ambil pesan sukses/error dari session untuk ditampilkan di awal halaman
if (isset($_SESSION['transaction_success'])) {
    $success_message = $_SESSION['transaction_success'];
    unset($_SESSION['transaction_success']);
}
if (isset($_SESSION['transaction_error'])) {
    $error_message = $_SESSION['transaction_error'];
    unset($_SESSION['transaction_error']);
}
// Ambil detail pembayaran sukses jika ada dan format untuk pesan
if (isset($_SESSION['payment_success_details'])) {
    $details = $_SESSION['payment_success_details'];
    $total_formatted = number_format($details['total'], 0, ',', '.');
    $dibayar_formatted = number_format($details['uang_dibayar'], 0, ',', '.');
    $kembalian_formatted = number_format($details['kembalian'], 0, ',', '.');

    // Dapatkan transaction_id terakhir dari sesi untuk tombol cetak
    $last_transaction_id_for_print = $_SESSION['last_transaction_id'] ?? null;
    unset($_SESSION['last_transaction_id']); // Hapus setelah digunakan di tampilan

    $success_message = "Pembayaran berhasil! Total: Rp {$total_formatted}. Uang Dibayar: Rp {$dibayar_formatted}. Kembalian: Rp {$kembalian_formatted}.";

    // Tambahkan tombol Cetak Struk jika transaction_id tersedia
    if ($last_transaction_id_for_print) {
        $success_message .= "<br><a href='print_struk.php?transaction_id={$last_transaction_id_for_print}' target='_blank' class='inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-3'><i class='fas fa-print mr-2'></i> Cetak Struk</a>";
    }

    unset($_SESSION['payment_success_details']);
}

// Tutup koneksi database setelah semua operasi selesai (kecuali jika sudah ditutup oleh AJAX handler)
if ($conn->ping()) { // Cek apakah koneksi masih hidup sebelum menutup
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&amp;display=swap" rel="stylesheet"/>
    <link href="css/style.css" rel="stylesheet"/> </head>
<body class="bg-gray-100 flex">
    <aside class="w-64 bg-yellow-700 text-white min-h-screen p-6">
        <div class="text-3xl font-bold mb-8 text-center">Kasir Padang</div>
        <nav>
            <ul>
                <li class="mb-4">
                    <a href="#" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-cash-register mr-3"></i> Penjualan
                    </a>
                </li>

                <li class="mb-4">
                    <a href="change_password.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-key mr-3"></i> Ganti Password
                    </a>
                </li>

                <li class="mb-4">
                    <a href="logout.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-sign-out-alt mr-3"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-yellow-800">Selamat Datang, <?php echo htmlspecialchars($username); ?>!</h1>
            <div class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-lg">
                <i class="fas fa-user-circle mr-2"></i> Petugas
            </div>
        </header>

        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Sukses!</strong>
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <div class="flex gap-8">
            <section class="flex-1 bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-yellow-800 mb-6">Daftar Menu</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if ($result_menus->num_rows > 0): ?>
                        <?php while($row = $result_menus->fetch_assoc()): ?>
                            <div class="bg-yellow-50 rounded-lg shadow-sm overflow-hidden menu-card">
                                <img src="<?php echo htmlspecialchars($row['gambar_url']); ?>" alt="<?php echo htmlspecialchars($row['nama_menu']); ?>" class="w-full h-32 object-cover">
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-yellow-900 mb-1"><?php echo htmlspecialchars($row['nama_menu']); ?></h3>
                                    <p class="text-yellow-700 font-bold mb-3">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                                    <form action="dashboard_petugas.php" method="POST">
                                        <input type="hidden" name="menu_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="menu_name" value="<?php echo htmlspecialchars($row['nama_menu']); ?>">
                                        <input type="hidden" name="menu_price" value="<?php echo $row['harga']; ?>">
                                        <button type="submit" name="add_to_cart" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition duration-200">
                                            <i class="fas fa-cart-plus mr-2"></i> Tambah
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="col-span-full text-center text-gray-600">Tidak ada menu tersedia.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="w-1/3 bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-yellow-800 mb-6">Keranjang Belanja</h2>
                <?php $grand_total = 0; ?>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <div class="space-y-4">
                        <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                            <div class="flex items-center justify-between border-b pb-2">
                                <div>
                                    <p class="font-semibold text-yellow-900"><?php echo htmlspecialchars($item['name']); ?></p>
                                    <p class="text-sm text-gray-600">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?> x <?php echo $item['quantity']; ?></p>
                                </div>
                                <div class="flex items-center">
                                    <form action="dashboard_petugas.php" method="POST" class="flex items-center">
                                        <input type="hidden" name="menu_id" value="<?php echo $id; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="w-16 text-center border rounded-md py-1 px-2 mr-2">
                                        <button type="submit" name="update_cart" class="bg-blue-500 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-600 mr-1">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <button type="submit" name="remove_from_cart" class="bg-red-500 text-white px-3 py-1 rounded-md text-sm hover:bg-red-600">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php $grand_total += $item['total_price']; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-6 border-t pt-4">
                        <div class="flex justify-between items-center text-xl font-bold text-yellow-800">
                            <span>Total:</span>
                            <span id="total_display">Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></span>
                        </div>
                        <form action="dashboard_petugas.php" method="POST" class="mt-4">
                            <div class="mb-4">
                                <label for="customer_name" class="block text-yellow-700 text-sm font-semibold mb-2">Nama Pelanggan (Opsional):</label>
                                <input type="text" id="customer_name" name="customer_name" class="w-full px-4 py-2 border border-yellow-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" placeholder="Nama pelanggan">
                            </div>
                            <div class="mb-4">
                                <label for="table_number" class="block text-yellow-700 text-sm font-semibold mb-2">Nomor Meja (Opsional):</label>
                                <input type="text" id="table_number" name="table_number" class="w-full px-4 py-2 border border-yellow-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" placeholder="Contoh: Meja 5, A1">
                            </div>

                            <div class="mb-4">
                                <label for="payment_method" class="block text-yellow-700 text-sm font-semibold mb-2">Metode Pembayaran:</label>
                                <select id="payment_method" name="payment_method" class="w-full px-4 py-2 border border-yellow-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                                    <option value="Tunai" selected>Tunai</option>
                                    <option value="Non-Tunai">Non-Tunai</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="uang_dibayar" class="block text-yellow-700 text-sm font-semibold mb-2">Uang Dibayar (Tunai):</label>
                                <input type="number" id="uang_dibayar" name="uang_dibayar"
                                        class="w-full px-4 py-2 border border-yellow-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                        placeholder="Masukkan jumlah uang" min="0" required>
                            </div>

                            <div class="mb-4">
                                <p class="block text-yellow-700 text-sm font-semibold mb-2">Kembalian:</p>
                                <p id="kembalian_display" class="text-lg font-bold text-green-600">Rp 0</p>
                            </div>

                            <button type="submit" name="process_payment" class="w-full bg-green-600 text-white px-4 py-3 rounded-lg font-semibold shadow-md hover:bg-green-700 transition">
                                <i class="fas fa-money-check-alt mr-2"></i> Proses Pembayaran
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-600">Keranjang belanja kosong.</p>
                <?php endif; ?>
            </section>
        </div>

        <section class="mt-8 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-yellow-800 mb-6">Pesanan Perlu Diantar (Status: Pending)</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left border-b">ID Transaksi</th>
                            <th class="py-3 px-6 text-left border-b">Waktu Pesan</th>
                            <th class="py-3 px-6 text-left border-b">Pelanggan/Meja</th>
                            <th class="py-3 px-6 text-right border-b">Total</th>
                            <th class="py-3 px-6 text-center border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm font-light">
                        <?php if (!empty($active_transactions)): ?>
                            <?php foreach ($active_transactions as $transaction): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($transaction['id']); ?></td>
                                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars((new DateTime($transaction['transaction_date']))->format('H:i:s, d M Y')); ?></td>
                                    <td class="py-3 px-6 text-left">
                                        <?php
                                            if (!empty($transaction['customer_name'])) {
                                                echo htmlspecialchars($transaction['customer_name']);
                                            } elseif (!empty($transaction['table_number'])) {
                                                echo "Meja: " . htmlspecialchars($transaction['table_number']);
                                            } else {
                                                echo "-"; // Jika keduanya kosong
                                            }
                                        ?>
                                    </td>
                                    <td class="py-3 px-6 text-right">Rp <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-6 text-center">
                                        <button onclick="markAsDelivered(<?php echo $transaction['id']; ?>)" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg text-xs flex items-center justify-center">
                                            <i class="fas fa-check-circle mr-1"></i> Tandai Diantar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-3 px-6 text-center text-gray-500">Tidak ada pesanan yang perlu diantar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        // Fungsi untuk memformat angka menjadi format Rupiah
        function formatRupiah(angka) {
            var reverse = angka.toString().split('').reverse().join(''),
                ribuan = reverse.match(/\d{1,3}/g);
            ribuan = ribuan.join('.').split('').reverse().join('');
            return 'Rp ' + ribuan;
        }

        // Dapatkan elemen-elemen yang diperlukan
        const totalDisplayElement = document.getElementById('total_display');
        const uangDibayarInput = document.getElementById('uang_dibayar');
        const kembalianDisplay = document.getElementById('kembalian_display');
        const paymentMethodSelect = document.getElementById('payment_method'); // Ambil elemen select metode pembayaran

        // Fungsi untuk menghitung dan menampilkan kembalian
        function hitungKembalian() {
            let totalText = totalDisplayElement.innerText.replace('Rp ', '').replace(/\./g, '').replace(/,/g, '.');
            let total = parseFloat(totalText) || 0;

            let uangDibayar = parseFloat(uangDibayarInput.value) || 0;
            let kembalian = uangDibayar - total;

            const paymentMethod = paymentMethodSelect.value;

            // Hanya tampilkan kembalian jika metode pembayaran adalah Tunai
            if (paymentMethod === 'Tunai') {
                uangDibayarInput.closest('div').style.display = 'block'; // Tampilkan input uang dibayar
                kembalianDisplay.closest('div').style.display = 'block'; // Tampilkan display kembalian
                uangDibayarInput.setAttribute('required', 'required'); // Wajib diisi

                if (kembalian < 0) {
                    kembalianDisplay.textContent = 'Rp ' + formatRupiah(Math.abs(kembalian)) + ' (Kurang)';
                    kembalianDisplay.classList.remove('text-green-600');
                    kembalianDisplay.classList.add('text-red-600');
                } else {
                    kembalianDisplay.textContent = formatRupiah(kembalian);
                    kembalianDisplay.classList.remove('text-red-600');
                    kembalianDisplay.classList.add('text-green-600');
                }
            } else {
                uangDibayarInput.closest('div').style.display = 'none'; // Sembunyikan input uang dibayar
                kembalianDisplay.closest('div').style.display = 'none'; // Sembunyikan display kembalian
                uangDibayarInput.removeAttribute('required'); // Tidak wajib diisi
                uangDibayarInput.value = total; // Set uang dibayar otomatis sama dengan total untuk non-tunai
            }
        }

        // Tambahkan event listener ke input uang_dibayar dan select payment_method
        uangDibayarInput.addEventListener('input', hitungKembalian);
        paymentMethodSelect.addEventListener('change', hitungKembalian); // Panggil juga saat metode pembayaran berubah

        // Panggil hitungKembalian saat halaman pertama kali dimuat
        hitungKembalian();


        // Fungsi untuk menandai pesanan sebagai sudah diantar
        function markAsDelivered(transactionId) {
            if (confirm('Yakin ingin menandai pesanan #' + transactionId + ' sudah diantar?')) {
                // Mengirim permintaan POST ke halaman ini sendiri
                fetch('dashboard_petugas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=update_delivery_status&transaction_id=' + transactionId + '&status=delivered'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pesanan #' + transactionId + ' berhasil ditandai sebagai sudah diantar.');
                        location.reload(); // Muat ulang halaman untuk memperbarui daftar
                    } else {
                        alert('Gagal menandai pesanan: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengupdate status pengiriman.');
                });
            }
        }
    </script>
</body>
</html>