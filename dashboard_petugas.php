<?php
session_start();
<<<<<<< HEAD
include 'koneksi.php'; 

=======
include 'koneksi.php'; // Pastikan path ke koneksi.php benar

// Cek apakah user sudah login dan perannya adalah petugas
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
<<<<<<< HEAD
if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

$success_message = "";
$error_message = "";

// --- START: LOGIKA AJAX UPDATE DELIVERY ---
if (isset($_POST['action']) && $_POST['action'] === 'update_delivery_status') {
    header('Content-Type: application/json');
    $transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
=======

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

>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
    if ($transaction_id > 0 && ($status === 'pending' || $status === 'delivered')) {
        $stmt = $conn->prepare("UPDATE transactions SET delivery_status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $status, $transaction_id);
<<<<<<< HEAD
            if ($stmt->execute()) { echo json_encode(['success' => true]); } 
            else { echo json_encode(['success' => false, 'message' => $stmt->error]); }
            $stmt->close();
        }
    }
    $conn->close();
    exit();
}
// --- END: LOGIKA AJAX ---

// Ambil daftar menu
$sql_menus = "SELECT id, nama_menu, harga, gambar_url FROM menus ORDER BY nama_menu ASC";
$result_menus = $conn->query($sql_menus);

// Ambil pesanan aktif (Pending)
$active_transactions = [];
$sql_active_transactions = "SELECT t.id, t.total_amount, t.transaction_date, t.customer_name, t.table_number FROM transactions t WHERE t.delivery_status = 'pending' ORDER BY t.transaction_date ASC";
$result_active = $conn->query($sql_active_transactions);
if ($result_active) {
    while ($row = $result_active->fetch_assoc()) { $active_transactions[] = $row; }
}

// --- LOGIKA KERANJANG (Sama Seperti Kode Anda) ---
if (isset($_POST['add_to_cart'])) {
    $menu_id = $_POST['menu_id'];
=======
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

>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
    if (isset($_SESSION['cart'][$menu_id])) {
        $_SESSION['cart'][$menu_id]['quantity']++;
        $_SESSION['cart'][$menu_id]['total_price'] = $_SESSION['cart'][$menu_id]['quantity'] * $_SESSION['cart'][$menu_id]['price'];
    } else {
<<<<<<< HEAD
        $_SESSION['cart'][$menu_id] = ['id' => $menu_id, 'name' => $_POST['menu_name'], 'price' => $_POST['menu_price'], 'quantity' => 1, 'total_price' => $_POST['menu_price']];
    }
    header("Location: dashboard_petugas.php"); exit();
}

if (isset($_POST['remove_from_cart'])) {
    unset($_SESSION['cart'][$_POST['menu_id']]);
    header("Location: dashboard_petugas.php"); exit();
}

// --- LOGIKA PEMBAYARAN ---
if (isset($_POST['process_payment'])) {
    if (!empty($_SESSION['cart'])) {
        $total_amount = 0;
        foreach ($_SESSION['cart'] as $item) { $total_amount += $item['total_price']; }
        $uang_dibayar = floatval(str_replace('.', '', $_POST['uang_dibayar']));
        $kembalian = $uang_dibayar - $total_amount;

        $conn->begin_transaction();
        try {
            $sql_ins = "INSERT INTO transactions (user_id, total_amount, uang_dibayar, kembalian, payment_method, customer_name, table_number, delivery_status, transaction_date) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
            $stmt = $conn->prepare($sql_ins);
            $stmt->bind_param("idddsss", $_SESSION['user_id'], $total_amount, $uang_dibayar, $kembalian, $_POST['payment_method'], $_POST['customer_name'], $_POST['table_number']);
            $stmt->execute();
            $transaction_id = $conn->insert_id;

            $sql_item = "INSERT INTO transaction_items (transaction_id, menu_id, quantity, price_per_unit, subtotal) VALUES (?, ?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_item);
            foreach ($_SESSION['cart'] as $item) {
                $sub = $item['quantity'] * $item['price'];
                $stmt_item->bind_param("iiidd", $transaction_id, $item['id'], $item['quantity'], $item['price'], $sub);
                $stmt_item->execute();
            }
            $conn->commit();
            $_SESSION['last_transaction_id'] = $transaction_id;
            $_SESSION['payment_success_details'] = ['total' => $total_amount, 'uang_dibayar' => $uang_dibayar, 'kembalian' => $kembalian];
            $_SESSION['cart'] = [];
        } catch (Exception $e) { $conn->rollback(); }
    }
    header("Location: dashboard_petugas.php"); exit();
}

// Pesan Notifikasi
if (isset($_SESSION['payment_success_details'])) {
    $d = $_SESSION['payment_success_details'];
    $last_id = $_SESSION['last_transaction_id'];
    $success_message = "Berhasil! Kembalian: Rp ".number_format($d['kembalian'],0,',','.')." <a href='print_struk.php?transaction_id=$last_id' target='_blank' class='underline ml-2 font-bold'><i class='fas fa-print'></i> CETAK STRUK</a>";
    unset($_SESSION['payment_success_details'], $_SESSION['last_transaction_id']);
=======
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
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>SIR - Kasir Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet"/>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fffaf0; }
        .sidebar-orange { background: linear-gradient(180deg, #ea580c 0%, #9a3412 100%); }
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(251, 191, 36, 0.3); }
        .menu-card { transition: all 0.3s ease; }
        .menu-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(234, 88, 12, 0.2); }
        .custom-scroll::-webkit-scrollbar { width: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #f97316; border-radius: 10px; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 sidebar-orange text-white flex flex-col shadow-2xl sticky top-0 h-screen">
        <div class="p-8 text-center border-b border-orange-400/30">
            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center mx-auto mb-3 shadow-lg rotate-3">
                <i class="fas fa-utensils text-orange-600 text-xl"></i>
            </div>
            <h2 class="font-black tracking-tighter text-lg uppercase">RM PADANG</h2>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="#" class="flex items-center bg-white/20 px-4 py-3 rounded-xl font-bold transition">
                <i class="fas fa-cash-register mr-3"></i> Penjualan
            </a>
            <a href="change_password_petugas.php" class="flex items-center hover:bg-white/10 px-4 py-3 rounded-xl transition">
                <i class="fas fa-key mr-3"></i> Keamanan
            </a>
            <div class="pt-4 border-t border-orange-400/30">
                <a href="logout.php" class="flex items-center text-orange-200 hover:text-white px-4 py-3 transition">
                    <i class="fas fa-power-off mr-3"></i> Keluar
                </a>
            </div>
        </nav>
        <div class="p-6 bg-orange-900/30 text-center text-[10px] font-bold tracking-widest uppercase">
            Petugas: <?php echo $username; ?>
        </div>
    </aside>

    <main class="flex-1 p-8 overflow-y-auto custom-scroll">
        <?php if($success_message): ?>
            <div class="bg-green-500 text-white p-4 rounded-2xl mb-6 shadow-lg flex items-center animate-pulse">
                <i class="fas fa-check-circle mr-3 text-xl"></i> <span><?php echo $success_message; ?></span>
=======
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
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
            </div>
        <?php endif; ?>

        <div class="flex gap-8">
<<<<<<< HEAD
            <div class="flex-1">
                <h2 class="text-xl font-black text-orange-900 mb-6 uppercase tracking-tight">Daftar Menu Makanan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while($row = $result_menus->fetch_assoc()): ?>
                    <div class="bg-white p-3 rounded-[30px] menu-card shadow-sm border border-orange-100">
                        <img src="<?php echo $row['gambar_url']; ?>" class="w-full h-40 object-cover rounded-[25px] mb-4">
                        <div class="px-2 pb-2">
                            <h3 class="font-bold text-slate-800 text-sm truncate uppercase tracking-tighter"><?php echo $row['nama_menu']; ?></h3>
                            <p class="text-orange-600 font-black text-lg italic mb-3">Rp <?php echo number_format($row['harga'],0,',','.'); ?></p>
                            <form method="POST">
                                <input type="hidden" name="menu_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="menu_name" value="<?php echo $row['nama_menu']; ?>">
                                <input type="hidden" name="menu_price" value="<?php echo $row['harga']; ?>">
                                <button type="submit" name="add_to_cart" class="w-full bg-orange-600 text-white py-3 rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-orange-700 transition shadow-md shadow-orange-100">
                                    <i class="fas fa-plus mr-2"></i> Tambah Item
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="w-96">
                <div class="glass p-6 rounded-[35px] shadow-xl border-b-4 border-orange-600 sticky top-8">
                    <h2 class="text-lg font-black text-slate-800 mb-6 uppercase border-b pb-4"><i class="fas fa-shopping-cart mr-2 text-orange-600"></i> Keranjang</h2>
                    <div class="space-y-3 max-h-60 overflow-y-auto custom-scroll pr-2 mb-6">
                        <?php $gt = 0; foreach($_SESSION['cart'] as $id => $item): $gt += $item['total_price']; ?>
                        <div class="flex justify-between items-center bg-orange-50/50 p-3 rounded-2xl border border-orange-100">
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-[11px] uppercase truncate text-slate-700"><?php echo $item['name']; ?></p>
                                <p class="text-[10px] text-orange-600 font-bold"><?php echo $item['quantity']; ?>x @ Rp <?php echo number_format($item['price'],0,',','.'); ?></p>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="menu_id" value="<?php echo $id; ?>">
                                <button type="submit" name="remove_from_cart" class="text-red-400 hover:text-red-600 ml-2"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="pt-4 border-t-2 border-dashed border-orange-200">
                        <div class="flex justify-between items-center mb-6">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Belanja</span>
                            <span id="total_display" class="text-2xl font-black text-orange-700 italic tracking-tighter">Rp <?php echo number_format($gt,0,',','.'); ?></span>
                        </div>

                        <form method="POST" class="space-y-3">
                            <input type="text" name="customer_name" class="w-full bg-white border border-orange-100 p-3 rounded-xl text-xs font-bold" placeholder="NAMA PELANGGAN">
                            <input type="text" name="table_number" class="w-full bg-white border border-orange-100 p-3 rounded-xl text-xs font-bold" placeholder="NOMOR MEJA">
                            <select id="payment_method" name="payment_method" class="w-full bg-white border border-orange-100 p-3 rounded-xl text-xs font-bold uppercase">
                                <option value="Tunai">💰 TUNAI</option>
                                <option value="Non-Tunai">💳 NON-TUNAI</option>
                            </select>
                            <div class="bg-orange-900 p-3 rounded-2xl">
                                <label class="text-orange-400 text-[9px] font-black uppercase mb-1 block">Bayar (Rp)</label>
                                <input type="number" id="uang_dibayar" name="uang_dibayar" class="w-full bg-transparent border-none p-0 text-white text-xl font-black focus:ring-0" required>
                            </div>
                            <div class="flex justify-between px-2 text-[10px] font-black text-slate-500 uppercase">
                                <span>Kembalian:</span>
                                <span id="kembalian_display" class="text-green-600 font-black">Rp 0</span>
                            </div>
                            <button type="submit" name="process_payment" class="w-full bg-orange-600 text-white py-4 rounded-2xl font-black shadow-lg shadow-orange-200 hover:bg-orange-500 transition-all uppercase text-xs tracking-widest">
                                <i class="fas fa-print mr-2"></i> Proses & Bayar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-12 glass p-8 rounded-[40px] shadow-sm border border-orange-100">
            <h2 class="text-xl font-black text-orange-900 mb-8 uppercase flex items-center">
                <i class="fas fa-clock mr-3 text-orange-500"></i> Antrian Pesanan
            </h2>
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-black uppercase text-slate-400 border-b border-orange-100">
                        <th class="pb-4">No Transaksi</th>
                        <th class="pb-4">Waktu</th>
                        <th class="pb-4">Meja/Nama</th>
                        <th class="pb-4">Total</th>
                        <th class="pb-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-orange-50 text-xs font-bold text-slate-700">
                    <?php foreach ($active_transactions as $t): ?>
                    <tr>
                        <td class="py-4 text-orange-600">#<?php echo $t['id']; ?></td>
                        <td class="py-4"><?php echo date('H:i', strtotime($t['transaction_date'])); ?> WIB</td>
                        <td class="py-4 uppercase"><?php echo $t['customer_name'] ?: 'MEJA: '.$t['table_number']; ?></td>
                        <td class="py-4 italic">Rp <?php echo number_format($t['total_amount'],0,',','.'); ?></td>
                        <td class="py-4 text-center">
                            <button onclick="markAsDelivered(<?php echo $t['id']; ?>)" class="bg-slate-900 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition text-[9px] uppercase tracking-widest">
                                Tandai Diantar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        const uangInput = document.getElementById('uang_dibayar');
        const kembalianDisplay = document.getElementById('kembalian_display');
        const totalText = "<?php echo $gt; ?>";

        // Hitung Kembalian Otomatis
        uangInput.addEventListener('input', () => {
            const bayar = parseFloat(uangInput.value) || 0;
            const kembalian = bayar - totalText;
            if(kembalian < 0) {
                kembalianDisplay.innerText = "Rp 0 (Kurang)";
                kembalianDisplay.style.color = "red";
            } else {
                kembalianDisplay.innerText = "Rp " + new Intl.NumberFormat('id-ID').format(kembalian);
                kembalianDisplay.style.color = "#16a34a";
            }
        });

        // AJAX Mark as Delivered
        function markAsDelivered(id) {
            if (confirm('Pesanan #' + id + ' sudah diantar ke meja?')) {
                const formData = new FormData();
                formData.append('action', 'update_delivery_status');
                formData.append('transaction_id', id);
                formData.append('status', 'delivered');

                fetch('dashboard_petugas.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => { if(data.success) location.reload(); });
=======
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
>>>>>>> 3d33e59de29485dbc5c0a3fa748595769751f536
            }
        }
    </script>
</body>
</html>