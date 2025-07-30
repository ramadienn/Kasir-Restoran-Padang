<?php
session_start();
include 'koneksi.php'; // Pastikan path ke koneksi.php benar

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Inisialisasi variabel filter dari GET request
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$filter_month = isset($_GET['filter_month']) ? $_GET['filter_month'] : '';
$filter_year = isset($_GET['filter_year']) ? $_GET['filter_year'] : '';

// Logika filter dan pengambilan data
$sql_transactions = "SELECT t.id, t.total_amount, t.transaction_date, t.payment_method, t.uang_dibayar, t.kembalian, u.username AS cashier_name
                     FROM transactions t
                     JOIN users u ON t.user_id = u.id";

$conditions = [];
$params = [];
$param_types = '';

// Filter Tanggal
if (!empty($filter_date)) {
    $date_obj = DateTime::createFromFormat('d/m/Y', $filter_date);
    if ($date_obj) {
        $formatted_date = $date_obj->format('Y-m-d');
        $conditions[] = "DATE(t.transaction_date) = ?";
        $params[] = $formatted_date;
        $param_types .= 's';
    }
}

// Filter Bulan
if (!empty($filter_month)) {
    $conditions[] = "MONTH(t.transaction_date) = ?";
    $params[] = $filter_month;
    $param_types .= 'i';

    // Tambahkan filter tahun jika bulan dipilih
    if (!empty($filter_year)) {
        $conditions[] = "YEAR(t.transaction_date) = ?";
        $params[] = $filter_year;
        $param_types .= 'i';
    } else {
        // Default ke tahun sekarang jika hanya bulan yang dipilih
        $filter_year = date('Y'); // Pastikan filter_year punya nilai
        $conditions[] = "YEAR(t.transaction_date) = ?";
        $params[] = $filter_year;
        $param_types .= 'i';
    }
} elseif (!empty($filter_year)) {
    // Jika hanya tahun yang dipilih tanpa bulan
    $conditions[] = "YEAR(t.transaction_date) = ?";
    $params[] = $filter_year;
    $param_types .= 'i';
}

if (!empty($conditions)) {
    $sql_transactions .= " WHERE " . implode(" AND ", $conditions);
}
$sql_transactions .= " ORDER BY t.transaction_date ASC"; // Urutkan berdasarkan tanggal untuk laporan

$stmt_transactions = $conn->prepare($sql_transactions);
if ($stmt_transactions === false) {
    die("Error preparing statement: " . $conn->error);
}
if (!empty($params)) {
    $stmt_transactions->bind_param($param_types, ...$params);
}
$stmt_transactions->execute();
$result_transactions = $stmt_transactions->get_result();

$total_all_transactions = 0; // Untuk menghitung total keseluruhan laporan

// Informasi periode laporan
$report_period = "Semua Transaksi";
if (!empty($filter_date)) {
    $report_period = "Tanggal: " . htmlspecialchars($filter_date);
} elseif (!empty($filter_month) && !empty($filter_year)) {
    $report_period = "Bulan: " . (new DateTime("2000-" . $filter_month . "-01"))->format('F') . " " . htmlspecialchars($filter_year);
} elseif (!empty($filter_year)) {
    $report_period = "Tahun: " . htmlspecialchars($filter_year);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi - RM Padang</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            margin: 20mm; /* Margin untuk cetak */
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 15mm;
        }
        .header h1 {
            font-size: 1.8em;
            margin-bottom: 5mm;
        }
        .header p {
            margin: 1mm 0;
            font-size: 0.9em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15mm;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            background-color: #e6e6e6;
        }
        .footer {
            text-align: center;
            margin-top: 20mm;
            font-size: 0.8em;
        }
        @media print {
            body {
                margin: 0; /* Hapus margin agar sesuai halaman kertas */
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()"> <div class="header">
        <h1>Laporan Transaksi RM PADANG TANGSEL JAYA</h1>
        <p>Jl.  No. 123, South Tangerang SELATAN, Banten, Indonesia</p>
        <p>Telp: 0812-3456-7890</p>
        <p>Periode Laporan: <?php echo $report_period; ?></p>
        <p>Dicetak Pada: <?php echo date('d-m-Y H:i:s'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Transaksi</th>
                <th>Tanggal & Waktu</th>
                <th>Kasir</th>
                <th class="text-right">Total Belanja</th>
                <th class="text-right">Uang Dibayar</th>
                <th class="text-right">Kembalian</th>
                <th>Metode Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_transactions->num_rows > 0): ?>
                <?php while($row = $result_transactions->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center"><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars((new DateTime($row['transaction_date']))->format('d-m-Y H:i:s')); ?></td>
                        <td><?php echo htmlspecialchars($row['cashier_name']); ?></td>
                        <td class="text-right">Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></td>
                        <td class="text-right">Rp <?php echo number_format($row['uang_dibayar'], 0, ',', '.'); ?></td>
                        <td class="text-right">Rp <?php echo number_format($row['kembalian'], 0, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                    </tr>
                    <?php $total_all_transactions += $row['total_amount']; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data transaksi untuk periode ini.</td>
                </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL PENDAPATAN LAPORAN INI:</td>
                <td class="text-right">Rp <?php echo number_format($total_all_transactions, 0, ',', '.'); ?></td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem.</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 15px;">
        <button onclick="window.close()" style="padding: 10px 20px; background-color: #f44336; color: white; border: none; border-radius: 5px; cursor: pointer;">Tutup Halaman Cetak</button>
    </div>
</body>
</html>