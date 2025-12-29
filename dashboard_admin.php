<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// --- POSISI PERBAIKAN: INISIALISASI VARIABEL AGAR TIDAK UNDEFINED ---
$penghasilan_harian = 0;
$penghasilan_bulanan = 0;
$filter_date = $_GET['filter_date'] ?? '';
$filter_month = $_GET['filter_month'] ?? '';
$filter_year = $_GET['filter_year'] ?? date('Y');

// 1. Hitung Penghasilan Harian
$today = date('Y-m-d');
$query_daily = "SELECT SUM(total_amount) AS total FROM transactions WHERE DATE(transaction_date) = '$today'";
$res_daily = $conn->query($query_daily);
if ($row = $res_daily->fetch_assoc()) {
    $penghasilan_harian = $row['total'] ?? 0;
}

// 2. Hitung Penghasilan Bulanan
$this_month = date('m');
$this_year = date('Y');
$query_monthly = "SELECT SUM(total_amount) AS total FROM transactions WHERE MONTH(transaction_date) = '$this_month' AND YEAR(transaction_date) = '$this_year'";
$res_monthly = $conn->query($query_monthly);
if ($row = $res_monthly->fetch_assoc()) {
    $penghasilan_bulanan = $row['total'] ?? 0;
}

// 3. Logika Filter Tabel Transaksi
$sql = "SELECT t.*, u.username AS cashier_name FROM transactions t JOIN users u ON t.user_id = u.id WHERE 1=1";

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
    <title>Dashboard Admin</title>
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
                <p class="text-gray-500">Berikut ringkasan penjualan restoran Anda hari ini.</p>
            </div>
            <div class="bg-yellow-600 text-white px-4 py-2 rounded-xl font-bold">Admin</div>
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
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Riwayat Transaksi</h2>
                <a href="print_report.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                    <i class="fas fa-print mr-2"></i> Cetak Laporan
                </a>
            </div>

            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <input type="text" name="filter_date" id="filter_date" placeholder="Tgl: dd/mm/yyyy" class="border p-2 rounded-lg text-sm" value="<?php echo htmlspecialchars($filter_date); ?>">
                <select name="filter_month" class="border p-2 rounded-lg text-sm">
                    <option value="">Semua Bulan</option>
                    <?php for($i=1; $i<=12; $i++) echo "<option value='$i' ".($filter_month==$i?'selected':'').">Bulan $i</option>"; ?>
                </select>
                <button type="submit" class="bg-yellow-600 text-white rounded-lg font-bold hover:bg-yellow-700 transition">Filter</button>
                <a href="dashboard_admin.php" class="bg-gray-200 text-center p-2 rounded-lg font-bold">Reset</a>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-gray-400 text-xs uppercase border-b">
                            <th class="pb-3">ID</th>
                            <th class="pb-3">Kasir</th>
                            <th class="pb-3 text-right">Total</th>
                            <th class="pb-3 text-center">Metode</th>
                            <th class="pb-3">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-600">
                        <?php while($row = $result_transactions->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="py-4">#<?php echo $row['id']; ?></td>
                            <td><?php echo $row['cashier_name']; ?></td>
                            <td class="text-right font-bold text-gray-800">Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></td>
                            <td class="text-center"><?php echo $row['payment_method']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['transaction_date'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        // Formatter otomatis untuk input tanggal
        document.getElementById('filter_date').addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 8);
            if (v.length >= 5) v = v.slice(0, 2) + '/' + v.slice(2, 4) + '/' + v.slice(4);
            else if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
            e.target.value = v;
        });
    </script>
</body>
</html>