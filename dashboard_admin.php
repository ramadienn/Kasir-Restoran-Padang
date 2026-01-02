<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login dan perannya adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Inisialisasi variabel filter
$filter_date = $_GET['filter_date'] ?? '';
$filter_month = $_GET['filter_month'] ?? '';
$filter_year = $_GET['filter_year'] ?? date('Y');

// Inisialisasi penghasilan
$penghasilan_harian = 0;
$penghasilan_bulanan = 0;

// 1. Hitung Penghasilan Harian (Hari ini)
$today = date('Y-m-d');
$query_daily = "SELECT SUM(total_amount) AS total FROM transactions WHERE DATE(transaction_date) = '$today'";
$res_daily = $conn->query($query_daily);
if ($row = $res_daily->fetch_assoc()) {
    $penghasilan_harian = $row['total'] ?? 0;
}

// 2. Hitung Penghasilan Bulanan (Bulan ini)
$this_month = date('m');
$this_year = date('Y');
$query_monthly = "SELECT SUM(total_amount) AS total FROM transactions WHERE MONTH(transaction_date) = '$this_month' AND YEAR(transaction_date) = '$this_year'";
$res_monthly = $conn->query($query_monthly);
if ($row = $res_monthly->fetch_assoc()) {
    $penghasilan_bulanan = $row['total'] ?? 0;
}

// 3. Logika Filter Tabel Transaksi
$sql = "SELECT t.*, u.username AS cashier_name 
        FROM transactions t 
        JOIN users u ON t.user_id = u.id 
        WHERE 1=1";

if (!empty($filter_date)) {
    $date_obj = DateTime::createFromFormat('d/m/Y', $filter_date);
    if ($date_obj) {
        $formatted = $date_obj->format('Y-m-d');
        $sql .= " AND DATE(t.transaction_date) = '$formatted'";
    }
}
if (!empty($filter_month)) {
    $sql .= " AND MONTH(t.transaction_date) = '$filter_month'";
}
if (!empty($filter_year)) {
    $sql .= " AND YEAR(t.transaction_date) = '$filter_year'";
}

$sql .= " ORDER BY t.transaction_date DESC";
$result_transactions = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - RM Padang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 min-h-screen p-8">
        <header class="flex justify-between items-center mb-8 bg-white p-6 rounded-2xl shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, <?php echo htmlspecialchars($username); ?>!</h1>
                <p class="text-gray-500">Berikut ringkasan penjualan restoran Anda.</p>
            </div>
            <div class="bg-yellow-600 text-white px-4 py-2 rounded-xl font-bold shadow-lg">
                <i class="fas fa-user-shield mr-2"></i>Admin
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-yellow-500 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase">Omzet Hari Ini</p>
                    <h3 class="text-3xl font-extrabold text-gray-800">Rp <?php echo number_format($penghasilan_harian, 0, ',', '.'); ?></h3>
                </div>
                <i class="fas fa-money-bill-wave text-4xl text-yellow-500 opacity-30"></i>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-green-500 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase">Omzet Bulan Ini</p>
                    <h3 class="text-3xl font-extrabold text-gray-800">Rp <?php echo number_format($penghasilan_bulanan, 0, ',', '.'); ?></h3>
                </div>
                <i class="fas fa-calendar-check text-4xl text-green-500 opacity-30"></i>
            </div>
        </div>

        <section class="bg-white p-6 rounded-2xl shadow-sm">
            <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                <h2 class="text-xl font-bold text-gray-800">Riwayat Transaksi</h2>
                <a href="print_report.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="bg-blue-600 text-white px-6 py-2 rounded-xl text-sm font-bold hover:bg-blue-700 transition shadow-md">
                    <i class="fas fa-print mr-2"></i> Cetak Laporan
                </a>
            </div>

            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 bg-gray-50 p-4 rounded-xl">
                <input type="text" name="filter_date" id="filter_date" placeholder="Tgl: dd/mm/yyyy" class="border p-3 rounded-xl text-sm focus:ring-2 focus:ring-yellow-500 outline-none" value="<?php echo htmlspecialchars($filter_date); ?>">
                
                <select name="filter_month" class="border p-3 rounded-xl text-sm focus:ring-2 focus:ring-yellow-500 outline-none">
                    <option value="">Semua Bulan</option>
                    <?php 
                    $months = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                    foreach($months as $num => $name) {
                        echo "<option value='$num' ".($filter_month == $num ? 'selected' : '').">$name</option>";
                    }
                    ?>
                </select>

                <button type="submit" class="bg-yellow-600 text-white rounded-xl font-bold hover:bg-yellow-700 transition py-3">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="dashboard_admin.php" class="bg-gray-200 text-center py-3 rounded-xl font-bold text-gray-700 hover:bg-gray-300 transition">Reset</a>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-gray-400 text-xs uppercase border-b">
                            <th class="pb-4 px-2">ID</th>
                            <th class="pb-4">Kasir</th>
                            <th class="pb-4">Pelanggan/Meja</th>
                            <th class="pb-4 text-right">Total</th>
                            <th class="pb-4 text-center">Metode</th>
                            <th class="pb-4 text-center">Status</th>
                            <th class="pb-4">Waktu</th>
                            <th class="pb-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600">
                        <?php if ($result_transactions->num_rows > 0): ?>
                            <?php while($row = $result_transactions->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-4 px-2 font-medium">#<?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['cashier_name']); ?></td>
                                <td>
                                    <?php 
                                    echo !empty($row['customer_name']) ? htmlspecialchars($row['customer_name']) : 
                                         (!empty($row['table_number']) ? "Meja: ".htmlspecialchars($row['table_number']) : "-"); 
                                    ?>
                                </td>
                                <td class="text-right font-bold text-gray-800">Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <span class="bg-gray-100 px-2 py-1 rounded-md text-xs"><?php echo $row['payment_method']; ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase <?php echo ($row['delivery_status'] == 'delivered') ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'; ?>">
                                        <?php echo $row['delivery_status']; ?>
                                    </span>
                                </td>
                                <td class="text-gray-400"><?php echo date('d/m/y H:i', strtotime($row['transaction_date'])); ?></td>
                                <td class="text-center">
                                    <a href="print_struk.php?transaction_id=<?php echo $row['id']; ?>" target="_blank" class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="py-10 text-center text-gray-400">Tidak ada data transaksi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        // Formatter otomatis untuk input tanggal dd/mm/yyyy
        const dateInput = document.getElementById('filter_date');
        dateInput.addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 8);
            if (v.length >= 5) v = v.slice(0, 2) + '/' + v.slice(2, 4) + '/' + v.slice(4);
            else if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
            e.target.value = v;
        });
    </script>
</body>
</html>