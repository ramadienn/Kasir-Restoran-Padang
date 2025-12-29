<?php
session_start();
include 'koneksi.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

$success_message = "";
$error_message = "";

// --- START: LOGIKA AJAX UPDATE DELIVERY ---
if (isset($_POST['action']) && $_POST['action'] === 'update_delivery_status') {
    header('Content-Type: application/json');
    $transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    if ($transaction_id > 0 && ($status === 'pending' || $status === 'delivered')) {
        $stmt = $conn->prepare("UPDATE transactions SET delivery_status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $status, $transaction_id);
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
    if (isset($_SESSION['cart'][$menu_id])) {
        $_SESSION['cart'][$menu_id]['quantity']++;
        $_SESSION['cart'][$menu_id]['total_price'] = $_SESSION['cart'][$menu_id]['quantity'] * $_SESSION['cart'][$menu_id]['price'];
    } else {
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
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            </div>
        <?php endif; ?>

        <div class="flex gap-8">
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
            }
        }
    </script>
</body>
</html>