<?php
session_start();
include 'koneksi.php'; 

// 1. Validasi Login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

$success_message = "";
$error_message = "";

// 2. Logika AJAX Update Status Pengantaran
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

// 3. Logika Keranjang (Tambah & Hapus)
if (isset($_POST['add_to_cart'])) {
    $menu_id = $_POST['menu_id'];
    if (isset($_SESSION['cart'][$menu_id])) {
        $_SESSION['cart'][$menu_id]['quantity']++;
        $_SESSION['cart'][$menu_id]['total_price'] = $_SESSION['cart'][$menu_id]['quantity'] * $_SESSION['cart'][$menu_id]['price'];
    } else {
        $_SESSION['cart'][$menu_id] = [
            'id' => $menu_id, 
            'name' => $_POST['menu_name'], 
            'price' => $_POST['menu_price'], 
            'quantity' => 1, 
            'total_price' => $_POST['menu_price']
        ];
    }
    header("Location: dashboard_petugas.php"); exit();
}

if (isset($_POST['remove_from_cart'])) {
    unset($_SESSION['cart'][$_POST['menu_id']]);
    header("Location: dashboard_petugas.php"); exit();
}

// 4. Logika Proses Pembayaran
if (isset($_POST['process_payment'])) {
    if (!empty($_SESSION['cart'])) {
        $total_amount = 0;
        foreach ($_SESSION['cart'] as $item) { $total_amount += $item['total_price']; }
        
        $uang_dibayar = floatval(str_replace('.', '', $_POST['uang_dibayar']));
        $kembalian = $uang_dibayar - $total_amount;

        if ($uang_dibayar < $total_amount && $_POST['payment_method'] == 'Tunai') {
            $_SESSION['error_msg'] = "Uang tidak cukup!";
        } else {
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
                $_SESSION['last_id'] = $transaction_id;
                $_SESSION['pay_success'] = $kembalian;
                $_SESSION['cart'] = [];
            } catch (Exception $e) { $conn->rollback(); }
        }
    }
    header("Location: dashboard_petugas.php"); exit();
}

// 5. Pesan Notifikasi
if (isset($_SESSION['pay_success'])) {
    $last_id = $_SESSION['last_id'];
    $success_message = "Berhasil! Kembalian: Rp ".number_format($_SESSION['pay_success'],0,',','.')." <a href='print_struk.php?transaction_id=$last_id' target='_blank' class='underline ml-2 font-bold'><i class='fas fa-print'></i> CETAK STRUK</a>";
    unset($_SESSION['pay_success'], $_SESSION['last_id']);
}
if (isset($_SESSION['error_msg'])) {
    $error_message = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

// 6. Ambil Data Menu & Antrian
$result_menus = $conn->query("SELECT * FROM menus ORDER BY nama_menu ASC");
$active_transactions = $conn->query("SELECT * FROM transactions WHERE delivery_status = 'pending' ORDER BY transaction_date ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SIR - Kasir Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <style>
        body { background-color: #fffaf0; font-family: 'Poppins', sans-serif; }
        .sidebar-orange { background: linear-gradient(180deg, #ea580c 0%, #9a3412 100%); }
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(251, 191, 36, 0.3); }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 sidebar-orange text-white flex flex-col shadow-2xl sticky top-0 h-screen">
        <div class="p-8 text-center border-b border-orange-400/30">
            <h2 class="font-black tracking-tighter text-lg">RM PADANG</h2>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="#" class="flex items-center bg-white/20 px-4 py-3 rounded-xl font-bold"><i class="fas fa-cash-register mr-3"></i> Penjualan</a>
            <a href="logout.php" class="flex items-center text-orange-200 hover:text-white px-4 py-3"><i class="fas fa-power-off mr-3"></i> Keluar</a>
        </nav>
        <div class="p-6 bg-orange-900/30 text-center text-xs">Petugas: <?php echo htmlspecialchars($username); ?></div>
    </aside>

    <main class="flex-1 p-8">
        <?php if($success_message): ?>
            <div class="bg-green-500 text-white p-4 rounded-xl mb-6 shadow-lg"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if($error_message): ?>
            <div class="bg-red-500 text-white p-4 rounded-xl mb-6 shadow-lg"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="flex gap-8">
            <div class="flex-1">
                <h2 class="text-xl font-black text-orange-900 mb-6 uppercase">Menu</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php while($row = $result_menus->fetch_assoc()): ?>
                    <div class="bg-white p-4 rounded-[30px] shadow-sm border border-orange-100">
                        <img src="<?php echo $row['gambar_url']; ?>" class="w-full h-32 object-cover rounded-2xl mb-4">
                        <h3 class="font-bold text-sm uppercase"><?php echo $row['nama_menu']; ?></h3>
                        <p class="text-orange-600 font-black mb-3">Rp <?php echo number_format($row['harga'],0,',','.'); ?></p>
                        <form method="POST">
                            <input type="hidden" name="menu_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="menu_name" value="<?php echo $row['nama_menu']; ?>">
                            <input type="hidden" name="menu_price" value="<?php echo $row['harga']; ?>">
                            <button type="submit" name="add_to_cart" class="w-full bg-orange-600 text-white py-2 rounded-xl text-xs font-bold uppercase">Tambah</button>
                        </form>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="w-96">
                <div class="glass p-6 rounded-[35px] shadow-xl border-b-4 border-orange-600 sticky top-8">
                    <h2 class="text-lg font-black mb-6 uppercase border-b pb-4">Keranjang</h2>
                    <div class="space-y-3 mb-6">
                        <?php $gt = 0; foreach($_SESSION['cart'] as $id => $item): $gt += $item['total_price']; ?>
                        <div class="flex justify-between items-center bg-orange-50 p-3 rounded-2xl">
                            <div class="flex-1">
                                <p class="font-bold text-xs uppercase"><?php echo $item['name']; ?></p>
                                <p class="text-[10px] text-orange-600"><?php echo $item['quantity']; ?>x</p>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="menu_id" value="<?php echo $id; ?>">
                                <button type="submit" name="remove_from_cart" class="text-red-400"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex justify-between font-black text-orange-700 text-xl mb-6">
                        <span>Total:</span>
                        <span>Rp <?php echo number_format($gt,0,',','.'); ?></span>
                    </div>
                    <form method="POST" class="space-y-3">
                        <input type="text" name="customer_name" class="w-full border p-3 rounded-xl text-xs" placeholder="NAMA PELANGGAN">
                        <input type="text" name="table_number" class="w-full border p-3 rounded-xl text-xs" placeholder="MEJA">
                        <select name="payment_method" class="w-full border p-3 rounded-xl text-xs">
                            <option value="Tunai">TUNAI</option>
                            <option value="Non-Tunai">NON-TUNAI</option>
                        </select>
                        <input type="number" name="uang_dibayar" class="w-full bg-orange-900 text-white p-3 rounded-xl font-bold" placeholder="BAYAR (RP)" required>
                        <button type="submit" name="process_payment" class="w-full bg-orange-600 text-white py-4 rounded-2xl font-black uppercase text-xs">Bayar & Cetak</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        function markAsDelivered(id) {
            if (confirm('Pesanan sudah diantar?')) {
                const formData = new FormData();
                formData.append('action', 'update_delivery_status');
                formData.append('transaction_id', id);
                formData.append('status', 'delivered');
                fetch('dashboard_petugas.php', { method: 'POST', body: formData })
                .then(() => location.reload());
            }
        }
    </script>
</body>
</html>