<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$filter_month = isset($_GET['filter_month']) ? $_GET['filter_month'] : '';
$filter_year = isset($_GET['filter_year']) ? $_GET['filter_year'] : '';

$sql_transactions = "SELECT t.id, t.total_amount, t.transaction_date, t.payment_method, t.uang_dibayar, t.kembalian, u.username AS cashier_name
                     FROM transactions t
                     JOIN users u ON t.user_id = u.id";

$conditions = [];
$params = [];
$param_types = '';

if (!empty($filter_date)) {
    $date_obj = DateTime::createFromFormat('d/m/Y', $filter_date);
    if ($date_obj) {
        $conditions[] = "DATE(t.transaction_date) = ?";
        $params[] = $date_obj->format('Y-m-d');
        $param_types .= 's';
    }
}

if (!empty($filter_month)) {
    $conditions[] = "MONTH(t.transaction_date) = ?";
    $params[] = $filter_month;
    $param_types .= 'i';
    
    $filter_year = !empty($filter_year) ? $filter_year : date('Y');
    $conditions[] = "YEAR(t.transaction_date) = ?";
    $params[] = $filter_year;
    $param_types .= 'i';
} elseif (!empty($filter_year)) {
    $conditions[] = "YEAR(t.transaction_date) = ?";
    $params[] = $filter_year;
    $param_types .= 'i';
}

if (!empty($conditions)) {
    $sql_transactions .= " WHERE " . implode(" AND ", $conditions);
}
$sql_transactions .= " ORDER BY t.transaction_date ASC";

$stmt = $conn->prepare($sql_transactions);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Nama Bulan Indonesia
$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$report_period = "Semua Transaksi";
if (!empty($filter_date)) {
    $report_period = "Tanggal: " . htmlspecialchars($filter_date);
} elseif (!empty($filter_month)) {
    $report_period = "Bulan: " . $nama_bulan[(int)$filter_month] . " " . htmlspecialchars($filter_year);
} elseif (!empty($filter_year)) {
    $report_period = "Tahun: " . htmlspecialchars($filter_year);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan - RM Padang</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 11pt; color: #333; line-height: 1.5; padding: 10px; }
        .header { text-align: center; border-bottom: 2px solid #444; margin-bottom: 20px; padding-bottom: 10px; }
        .header h1 { margin: 0; text-transform: uppercase; font-size: 20pt; }
        .header p { margin: 2px 0; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; font-weight: bold; border: 1px solid #999; padding: 10px; text-align: center; font-size: 10pt; }
        td { border: 1px solid #ccc; padding: 8px; font-size: 10pt; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background-color: #eee !important; -webkit-print-color-adjust: exact; }
        
        .footer-info { margin-top: 30px; display: flex; justify-content: space-between; }
        .signature { text-align: center; width: 200px; margin-top: 50px; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h1>RM PADANG TANGSEL JAYA</h1>
        <p>Jl. Suryakencana No. 1, Pamulang, Tangerang Selatan, Banten</p>
        <p>Telp: 0812-3456-7890 | Email: admin@rmpadang.com</p>
    </div>

    <div style="margin-bottom: 15px;">
        <table style="border: none; width: auto;">
            <tr><td style="border: none; padding: 2px;">Periode</td><td style="border: none; padding: 2px;">: <strong><?php echo $report_period; ?></strong></td></tr>
            <tr><td style="border: none; padding: 2px;">Dicetak Oleh</td><td style="border: none; padding: 2px;">: <?php echo htmlspecialchars($_SESSION['username']); ?></td></tr>
            <tr><td style="border: none; padding: 2px;">Waktu Cetak</td><td style="border: none; padding: 2px;">: <?php echo date('d/m/Y H:i'); ?></td></tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal & Waktu</th>
                <th>Kasir</th>
                <th class="text-right">Total Belanja</th>
                <th class="text-right">Bayar</th>
                <th class="text-right">Kembali</th>
                <th>Metode</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_all = 0;
            if ($result->num_rows > 0): 
                while($row = $result->fetch_assoc()): 
                    $total_all += $row['total_amount'];
            ?>
                <tr>
                    <td class="text-center">#<?php echo $row['id']; ?></td>
                    <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($row['transaction_date'])); ?></td>
                    <td><?php echo htmlspecialchars($row['cashier_name']); ?></td>
                    <td class="text-right">Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></td>
                    <td class="text-right">Rp <?php echo number_format($row['uang_dibayar'], 0, ',', '.'); ?></td>
                    <td class="text-right">Rp <?php echo number_format($row['kembalian'], 0, ',', '.'); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($row['payment_method']); ?></td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="7" class="text-center">Data tidak ditemukan.</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL PENDAPATAN</td>
                <td class="text-right">Rp <?php echo number_format($total_all, 0, ',', '.'); ?></td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-info">
        <div>
            <p style="font-size: 9pt; color: #666;">* Dokumen ini sah dicetak melalui sistem manajemen kasir.</p>
        </div>
        <div class="signature">
            <p>Tangerang Selatan, <?php echo date('d F Y'); ?></p>
            <p style="margin-top: 60px;">( ____________________ )</p>
            <p>Admin Operasional</p>
        </div>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #2196F3; color: white; border: none; border-radius: 4px;">Cetak Ulang</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; background: #f44336; color: white; border: none; border-radius: 4px;">Tutup</button>
    </div>

</body>
</html>