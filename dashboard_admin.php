<?php
session_start();
include 'koneksi.php'; // Pastikan path ke koneksi.php benar

// Cek apakah user sudah login dan perannya adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Inisialisasi variabel filter
$filter_date = '';
$filter_month = '';
$filter_year = '';

// Variabel untuk menyimpan penghasilan
$penghasilan_harian = 0;
$penghasilan_bulanan = 0;

// Logika filter dan pengambilan data
$sql_transactions = "SELECT t.id, t.total_amount, t.transaction_date, t.payment_method, t.uang_dibayar, t.kembalian, u.username AS cashier_name, t.customer_name, t.table_number, t.delivery_status
                     FROM transactions t
                     JOIN users u ON t.user_id = u.id";

$conditions = [];
$params = [];
$param_types = '';

// Filter Tanggal
if (isset($_GET['filter_date']) && !empty($_GET['filter_date'])) {
    $filter_date = $_GET['filter_date']; // Format dd/mm/yyyy
    $date_obj = DateTime::createFromFormat('d/m/Y', $filter_date);
    if ($date_obj) {
        $formatted_date = $date_obj->format('Y-m-d');
        $conditions[] = "DATE(t.transaction_date) = ?";
        $params[] = $formatted_date;
        $param_types .= 's';
    } else {
        // Anda bisa menambahkan penanganan error untuk format tanggal yang tidak valid di sini
        // Misalnya, set $filter_date menjadi kosong atau tampilkan pesan error
    }
}

// Filter Bulan
if (isset($_GET['filter_month']) && !empty($_GET['filter_month'])) {
    $filter_month = $_GET['filter_month'];
    $conditions[] = "MONTH(t.transaction_date) = ?";
    $params[] = $filter_month;
    $param_types .= 'i';

    // Tambahkan filter tahun jika bulan dipilih
    if (isset($_GET['filter_year']) && !empty($_GET['filter_year'])) {
        $filter_year = $_GET['filter_year'];
        $conditions[] = "YEAR(t.transaction_date) = ?";
        $params[] = $filter_year;
        $param_types .= 'i';
    } else {
        // Default ke tahun sekarang jika hanya bulan yang dipilih
        $filter_year = date('Y');
        $conditions[] = "YEAR(t.transaction_date) = ?";
        $params[] = $filter_year;
        $param_types .= 'i';
    }
} elseif (isset($_GET['filter_year']) && !empty($_GET['filter_year'])) {
    // Jika hanya tahun yang dipilih tanpa bulan
    $filter_year = $_GET['filter_year'];
    $conditions[] = "YEAR(t.transaction_date) = ?";
    $params[] = $filter_year;
    $param_types .= 'i';
}

if (!empty($conditions)) {
    $sql_transactions .= " WHERE " . implode(" AND ", $conditions);
}
$sql_transactions .= " ORDER BY t.transaction_date DESC";

$stmt_transactions = $conn->prepare($sql_transactions);
if ($stmt_transactions === false) {
    die("Error preparing statement for transactions: " . $conn->error);
}
if (!empty($params)) {
    $stmt_transactions->bind_param($param_types, ...$params);
}
$stmt_transactions->execute();
$result_transactions = $stmt_transactions->get_result();

// Hitung Penghasilan Harian (untuk hari ini)
$today = date('Y-m-d');
$sql_daily_revenue = "SELECT SUM(total_amount) AS daily_total FROM transactions WHERE DATE(transaction_date) = ?";
$stmt_daily = $conn->prepare($sql_daily_revenue);
if ($stmt_daily === false) {
    die("Error preparing statement for daily revenue: " . $conn->error);
}
$stmt_daily->bind_param("s", $today);
$stmt_daily->execute();
$result_daily = $stmt_daily->get_result();
if ($row_daily = $result_daily->fetch_assoc()) {
    $penghasilan_harian = $row_daily['daily_total'] ?? 0;
}
$stmt_daily->close();

// Hitung Penghasilan Bulanan (untuk bulan ini)
$this_month = date('m');
$this_year = date('Y');
$sql_monthly_revenue = "SELECT SUM(total_amount) AS monthly_total FROM transactions WHERE MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?";
$stmt_monthly = $conn->prepare($sql_monthly_revenue);
if ($stmt_monthly === false) {
    die("Error preparing statement for monthly revenue: " . $conn->error);
}
$stmt_monthly->bind_param("ii", $this_month, $this_year);
$stmt_monthly->execute();
$result_monthly = $stmt_monthly->get_result();
if ($row_monthly = $result_monthly->fetch_assoc()) {
    $penghasilan_bulanan = $row_monthly['monthly_total'] ?? 0;
}
$stmt_monthly->close();

// Tutup koneksi database sebelum output HTML
// Pastikan $conn ada sebelum mencoba menutupnya untuk menghindari error jika koneksi gagal di awal
if (isset($conn)) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - RM Padang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&amp;display=swap" rel="stylesheet"/>
    <link href="css/style.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 flex">
    <aside class="w-64 bg-yellow-700 text-white min-h-screen p-6">
        <div class="text-3xl font-bold mb-8 text-center">Admin Padang</div>
        <nav>
            <ul>
                <li class="mb-4">
                    <a href="dashboard_admin.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-tachometer-alt mr-3"></i> Laporan
                    </a>
                </li>
                <li class="mb-4">
                    <a href="manage_users.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-users-cog mr-3"></i> Manajemen Pengguna
                    </a>
                </li>
                <li class="mb-4">
                    <a href="manage_menus.php" class="flex items-center text-white hover:bg-yellow-800 p-3 rounded-lg transition duration-200">
                        <i class="fas fa-utensils mr-3"></i> Manajemen Menu
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
                <i class="fas fa-user-circle mr-2"></i> Admin
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-yellow-800">Penghasilan Harian</h2>
                    <p class="text-3xl font-bold text-green-600">Rp <?php echo number_format($penghasilan_harian, 0, ',', '.'); ?></p>
                </div>
                <i class="fas fa-money-bill-wave text-5xl text-yellow-500"></i>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-yellow-800">Penghasilan Bulanan</h2>
                    <p class="text-3xl font-bold text-green-600">Rp <?php echo number_format($penghasilan_bulanan, 0, ',', '.'); ?></p>
                </div>
                <i class="fas fa-calendar-alt text-5xl text-yellow-500"></i>
            </div>
        </div>

        <section class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-yellow-800 mb-6">Detail Transaksi</h2>

            <form action="dashboard_admin.php" method="GET" class="mb-6 flex flex-wrap items-end gap-4">
                <div>
                    <label for="filter_date" class="block text-gray-700 text-sm font-bold mb-2">Filter Tanggal:</label>
                    <input type="text" id="filter_date" name="filter_date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="dd/mm/yyyy" value="<?php echo htmlspecialchars($filter_date); ?>">
                </div>
                <div>
                    <label for="filter_month" class="block text-gray-700 text-sm font-bold mb-2">Filter Bulan:</label>
                    <select id="filter_month" name="filter_month" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">--- Pilih Bulan ---</option>
                        <?php
                        $months = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        foreach ($months as $num => $name) {
                            $selected = ($filter_month == $num) ? 'selected' : '';
                            echo "<option value='{$num}' {$selected}>{$name}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="filter_year" class="block text-gray-700 text-sm font-bold mb-2">Filter Tahun:</label>
                    <select id="filter_year" name="filter_year" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">--- Pilih Tahun ---</option>
                        <?php
                        $current_year = date('Y');
                        for ($y = $current_year; $y >= $current_year - 5; $y--) {
                            $selected = ($filter_year == $y) ? 'selected' : '';
                            echo "<option value='{$y}' {$selected}>{$y}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline flex items-center">
                    <i class="fas fa-filter mr-2"></i> Lihat
                </button>
                <a href="dashboard_admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline flex items-center">
                    <i class="fas fa-redo mr-2"></i> Reset Filter
                </a>
                <a href="print_report.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline flex items-center">
                    <i class="fas fa-print mr-2"></i> Cetak Laporan
                </a>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left border-b">ID Transaksi</th>
                            <th class="py-3 px-6 text-left border-b">Petugas</th>
                            <th class="py-3 px-6 text-right border-b">Total</th>
                            <th class="py-3 px-6 text-right border-b">Dibayar</th>
                            <th class="py-3 px-6 text-right border-b">Kembalian</th>
                            <th class="py-3 px-6 text-left border-b">Metode Pembayaran</th>
                            <th class="py-3 px-6 text-left border-b">Waktu Transaksi</th>
                            <th class="py-3 px-6 text-left border-b">Pelanggan/Meja</th>
                            <th class="py-3 px-6 text-center border-b">Status Antar</th>
                            <th class="py-3 px-6 text-center border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm font-light">
                        <?php if ($result_transactions->num_rows > 0): ?>
                            <?php while($row = $result_transactions->fetch_assoc()): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['cashier_name']); ?></td>
                                    <td class="py-3 px-6 text-right">Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-6 text-right">Rp <?php echo number_format($row['uang_dibayar'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-6 text-right">Rp <?php echo number_format($row['kembalian'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                    <td class="py-3 px-6 text-left"><?php echo htmlspecialchars((new DateTime($row['transaction_date']))->format('d M Y H:i:s')); ?></td>
                                    <td class="py-3 px-6 text-left">
                                        <?php
                                            if (!empty($row['customer_name'])) {
                                                echo htmlspecialchars($row['customer_name']);
                                            } elseif (!empty($row['table_number'])) {
                                                echo "Meja: " . htmlspecialchars($row['table_number']);
                                            } else {
                                                echo "-";
                                            }
                                        ?>
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                                            <?php
                                                if ($row['delivery_status'] == 'delivered') {
                                                    echo 'bg-green-200 text-green-800';
                                                } else {
                                                    echo 'bg-orange-200 text-orange-800';
                                                }
                                            ?>">
                                            <?php echo ucfirst(htmlspecialchars($row['delivery_status'])); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <div class="flex items-center justify-center">
                                            <a href="print_struk.php?transaction_id=<?php echo $row['id']; ?>" target="_blank" class="w-8 h-8 rounded-full bg-blue-500 hover:bg-blue-600 text-white flex items-center justify-center mx-1 tooltip" title="Cetak Struk">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="py-3 px-6 text-center">Tidak ada data transaksi ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        // Contoh sederhana untuk input tanggal tanpa library datepicker
        // Jika Anda ingin datepicker interaktif, Anda perlu menambahkan library seperti jQuery UI Datepicker atau Flatpickr
        document.addEventListener('DOMContentLoaded', function() {
            const filterDateInput = document.getElementById('filter_date');
            // Menambahkan event listener untuk memastikan format dd/mm/yyyy saat input
            filterDateInput.addEventListener('input', function(e) {
                let value = e.target.value;
                // Hapus semua karakter non-digit
                value = value.replace(/\D/g, '');

                if (value.length > 2) {
                    value = value.substring(0,2) + '/' + value.substring(2);
                }
                if (value.length > 5) {
                    value = value.substring(0,5) + '/' + value.substring(5,9);
                }
                e.target.value = value;
            });
        });
    </script>
</body>
</html>