<?php
session_start();
include 'koneksi.php'; // Pastikan path ke koneksi.php benar

// Pastikan hanya petugas atau admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'petugas' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$transaction_id = isset($_GET['transaction_id']) ? intval($_GET['transaction_id']) : 0;

if ($transaction_id === 0) {
    echo "ID Transaksi tidak valid.";
    exit();
}

// Ambil detail transaksi utama
$sql_transaction = "SELECT t.id, t.transaction_date, t.total_amount, t.uang_dibayar, t.kembalian, t.payment_method, t.customer_name, t.table_number, u.username AS cashier_name
                    FROM transactions t
                    JOIN users u ON t.user_id = u.id
                    WHERE t.id = ?";
$stmt_transaction = $conn->prepare($sql_transaction);
if ($stmt_transaction === false) {
    die("Error preparing transaction statement: " . $conn->error);
}
$stmt_transaction->bind_param("i", $transaction_id);
$stmt_transaction->execute();
$result_transaction = $stmt_transaction->get_result();
$transaction = $result_transaction->fetch_assoc();
$stmt_transaction->close();

if (!$transaction) {
    echo "Transaksi tidak ditemukan.";
    exit();
}

// Ambil detail item transaksi
$sql_items = "SELECT ti.quantity, ti.price_per_unit, ti.subtotal, m.nama_menu
              FROM transaction_items ti
              JOIN menus m ON ti.menu_id = m.id
              WHERE ti.transaction_id = ?";
$stmt_items = $conn->prepare($sql_items);
if ($stmt_items === false) {
    die("Error preparing items statement: " . $conn->error);
}
$stmt_items->bind_param("i", $transaction_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$items = [];
while ($row = $result_items->fetch_assoc()) {
    $items[] = $row;
}
$stmt_items->close();
$conn->close();

// Format tanggal
$transaction_date = new DateTime($transaction['transaction_date']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran #<?php echo htmlspecialchars($transaction['id']); ?></title>
    <style>
        body {
            font-family: 'Consolas', 'Courier New', monospace; /* Font monospace untuk tampilan struk */
            font-size: 12px;
            width: 80mm; /* Lebar standar struk kasir */
            margin: 0 auto;
            padding: 5mm;
            box-sizing: border-box;
            background-color: #fff;
            color: #000;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 5mm;
        }
        .header h1 {
            font-size: 1.2em;
            margin-bottom: 2mm;
        }
        .info-block {
            margin-bottom: 5mm;
            line-height: 1.4;
        }
        .info-block p {
            margin: 0;
        }
        .item-list {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5mm;
        }
        .item-list th, .item-list td {
            padding: 2mm 0;
            text-align: left;
            border-bottom: 1px dashed #ccc;
        }
        /* Penyesuaian lebar kolom untuk tampilan yang rapi */
        .item-list th:nth-child(1), .item-list td:nth-child(1) { width: 50%; } /* Menu */
        .item-list th:nth-child(2), .item-list td:nth-child(2) { width: 15%; text-align: center; } /* Qty */
        .item-list th:nth-child(3), .item-list td:nth-child(3) { width: 15%; text-align: right; } /* Harga */
        .item-list th:nth-child(4), .item-list td:nth-child(4) { width: 20%; text-align: right; } /* Subtotal */

        .totals {
            width: 100%;
            margin-top: 5mm;
            line-height: 1.5;
        }
        .totals p {
            display: flex;
            justify-content: space-between;
            margin: 0;
            font-weight: bold;
        }
        .dot-line {
            border-top: 1px dashed #000;
            margin: 3mm 0;
        }
        @media print {
            body {
                width: auto; /* Biarkan browser menentukan lebar saat mencetak */
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()"> <div class="header">
        <h1>RM PADANG TANGSEL JAYA</h1>
        <p>Jl. TANGERANG SELATAN </p>
        <p>Telp: 0812-3456-7890</p>
    </div>

    <div class="dot-line"></div>

    <div class="info-block">
        <p><strong>Transaksi ID:</strong> <?php echo htmlspecialchars($transaction['id']); ?></p>
        <p><strong>Tanggal:</strong> <?php echo $transaction_date->format('d-m-Y H:i:s'); ?></p>
        <p><strong>Kasir:</strong> <?php echo htmlspecialchars($transaction['cashier_name']); ?></p>
        <?php if ($transaction['customer_name']): ?>
            <p><strong>Pelanggan:</strong> <?php echo htmlspecialchars($transaction['customer_name']); ?></p>
        <?php endif; ?>
        <?php if ($transaction['table_number']): ?>
            <p><strong>Meja:</strong> <?php echo htmlspecialchars($transaction['table_number']); ?></p>
        <?php endif; ?>
    </div>

    <div class="dot-line"></div>

    <table class="item-list">
        <thead>
            <tr>
                <th>Menu</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nama_menu']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td><?php echo number_format($item['price_per_unit'], 0, ',', '.'); ?></td>
                    <td><?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="dot-line"></div>

    <div class="totals">
        <p><span>Total Belanja:</span> <span>Rp <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></span></p>
        <p><span>Metode Bayar:</span> <span><?php echo htmlspecialchars($transaction['payment_method']); ?></span></p>
        <p><span>Uang Dibayar:</span> <span>Rp <?php echo number_format($transaction['uang_dibayar'], 0, ',', '.'); ?></span></p>
        <p><span>Kembalian:</span> <span>Rp <?php echo number_format($transaction['kembalian'], 0, ',', '.'); ?></span></p>
    </div>

    <div class="dot-line"></div>

    <div class="footer">
        <p>Terima Kasih Atas Kunjungan Anda!</p>
        <p>Datang Kembali Ya!</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 15px;">
        <button onclick="window.close()" style="padding: 10px 20px; background-color: #f44336; color: white; border: none; border-radius: 5px; cursor: pointer;">Tutup Halaman</button>
    </div>
</body>
</html>