<?php 
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'includes/db_connect.php';

if (!isset($_GET['no'])) {
    header('Location: manage_receipts.php');
    exit;
}

$receipt_no = $_GET['no'];
$sql = "SELECT r.*, p.amount, p.payment_date, s.full_name, s.email 
        FROM receipts r 
        JOIN payments p ON r.payment_id = p.id 
        JOIN students s ON p.student_id = s.id 
        WHERE r.receipt_no = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $receipt_no);
$stmt->execute();
$result = $stmt->get_result();
$receipt = $result->fetch_assoc();

if (!$receipt) {
    header('Location: manage_receipts.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Resit - <?php echo $receipt_no; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .print-receipt {
            max-width: 800px;
            margin: 0 auto;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .receipt-info {
            margin-bottom: 30px;
        }

        .receipt-amount {
            text-align: right;
            font-size: 24px;
            font-weight: bold;
            margin-top: 30px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-receipt">
        <div class="receipt-header">
            <h1>RESIT PEMBAYARAN</h1>
            <p>No. Resit: <?php echo htmlspecialchars($receipt['receipt_no']); ?></p>
        </div>

        <div class="receipt-info">
            <p><strong>Nama:</strong> <?php echo htmlspecialchars($receipt['full_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($receipt['email']); ?></p>
            <p><strong>Tarikh:</strong> <?php echo date('d/m/Y', strtotime($receipt['payment_date'])); ?></p>
            <p><strong>No. Rujukan:</strong> <?php echo $receipt['payment_id']; ?></p>
        </div>

        <div class="receipt-amount">
            <p>Jumlah: RM <?php echo number_format($receipt['amount'], 2); ?></p>
        </div>

        <div class="no-print">
            <button onclick="window.print()">Cetak</button>
            <button onclick="window.close()">Tutup</button>
        </div>
    </div>
</body>
</html>