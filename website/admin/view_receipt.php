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
$sql = "SELECT r.*, p.amount, p.payment_date, p.status, s.full_name, s.email 
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
    <title>Lihat Resit - <?php echo $receipt_no; ?></title>
    <style>
        .receipt-detail {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .receipt-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .receipt-amount {
            font-size: 24px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }

        .print-button {
            background: #FFE45C;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            float: right;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="receipt-detail">
            <div class="receipt-header">
                <h2>RESIT PEMBAYARAN</h2>
                <p>No. Resit: <?php echo htmlspecialchars($receipt['receipt_no']); ?></p>
            </div>

            <div class="receipt-info">
                <div>
                    <p><strong>Nama:</strong> <?php echo htmlspecialchars($receipt['full_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($receipt['email']); ?></p>
                    <p><strong>Tarikh:</strong> <?php echo date('d/m/Y', strtotime($receipt['payment_date'])); ?></p>
                </div>
                <div>
                    <p><strong>Status:</strong> <?php echo $receipt['status']; ?></p>
                    <p><strong>No. Rujukan:</strong> <?php echo $receipt['payment_id']; ?></p>
                </div>
            </div>

            <div class="receipt-amount">
                <p>Jumlah: RM <?php echo number_format($receipt['amount'], 2); ?></p>
            </div>

            <button class="print-button" onclick="window.print()">Cetak Resit</button>
        </div>
    </div>
</body>
</html>