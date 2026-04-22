<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'includes/db_connect.php';

// Verify database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function generateReceiptNo($conn) {
    do {
        $receiptNo = 'R' . date('Ymd') . random_int(1000, 9999);
        $checkStmt = $conn->prepare("SELECT id FROM receipts WHERE receipt_no = ? LIMIT 1");
        $checkStmt->bind_param("s", $receiptNo);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();
    } while ($exists);

    return $receiptNo;
}

// Handle receipt generation
if (isset($_POST['generate_receipt'])) {
    try {
        $payment_id = intval($_POST['payment_id'] ?? 0);
        if ($payment_id <= 0) {
            throw new Exception('ID pembayaran tidak sah.');
        }

        $paymentStmt = $conn->prepare("SELECT id, status FROM payments WHERE id = ? LIMIT 1");
        $paymentStmt->bind_param("i", $payment_id);
        $paymentStmt->execute();
        $payment = $paymentStmt->get_result()->fetch_assoc();
        $paymentStmt->close();

        if (!$payment) {
            throw new Exception('Pembayaran tidak ditemui.');
        }

        if ($payment['status'] !== 'Selesai') {
            throw new Exception('Resit hanya boleh dijana untuk pembayaran yang telah dibayar.');
        }

        $existingStmt = $conn->prepare("SELECT receipt_no FROM receipts WHERE payment_id = ? LIMIT 1");
        $existingStmt->bind_param("i", $payment_id);
        $existingStmt->execute();
        $existing = $existingStmt->get_result()->fetch_assoc();
        $existingStmt->close();

        if ($existing) {
            $success_message = "Resit sudah wujud: " . $existing['receipt_no'];
        } else {
            $receipt_no = generateReceiptNo($conn);

            $sql = "INSERT INTO receipts (payment_id, receipt_no) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $payment_id, $receipt_no);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $stmt->close();

            $success_message = "Resit berjaya dijana: " . $receipt_no;
        }
    } catch (Exception $e) {
        $error_message = "Ralat: " . $e->getMessage();
    }
}

$readyPayments = [];
$receipts = [];

try {
    $readySql = "SELECT p.id, p.student_name, p.item_name, p.amount,
                        COALESCE(p.payment_date, p.order_date) AS receipt_date,
                        p.transaction_id
                 FROM payments p
                 LEFT JOIN receipts r ON r.payment_id = p.id
                 WHERE p.status = 'Selesai' AND r.id IS NULL
                 ORDER BY COALESCE(p.payment_date, p.order_date) DESC, p.id DESC";
    $readyResult = $conn->query($readySql);
    if ($readyResult) {
        while ($row = $readyResult->fetch_assoc()) {
            $readyPayments[] = $row;
        }
    }

    $receiptSql = "SELECT r.receipt_no, p.student_name, p.item_name,
                          COALESCE(p.payment_date, p.order_date) AS receipt_date,
                          p.amount, p.status
                   FROM receipts r
                   JOIN payments p ON r.payment_id = p.id
                   ORDER BY r.created_at DESC";
    $receiptResult = $conn->query($receiptSql);
    if ($receiptResult) {
        while ($row = $receiptResult->fetch_assoc()) {
            $receipts[] = $row;
        }
    }
} catch (Exception $e) {
    $error_message = "Ralat semasa memuatkan data resit: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resit - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background: white;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            background: white;
            min-width: 0;
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-direction: column;
            align-items: flex-start;
        }

        .receipt-header h2 {
            color: #333;
            font-size: 1.8rem;
            font-weight: bold;
        }

        .receipt-subtitle {
            color: #666;
            font-size: 0.98rem;
            margin-top: 6px;
        }

        .generation-panel {
            background: #f8fbff;
            border: 2px solid #cfe6ff;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 20px;
        }

        .generation-panel h3 {
            color: #003B95;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .generation-panel p {
            color: #555;
            margin-bottom: 14px;
            line-height: 1.5;
        }

        .ready-payment-list {
            display: grid;
            gap: 12px;
        }

        .ready-payment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid #d8ebff;
            border-radius: 12px;
            background: #fff;
        }

        .ready-payment-info {
            display: grid;
            gap: 4px;
        }

        .ready-payment-info strong {
            color: #003B95;
            font-size: 1rem;
        }

        .ready-payment-meta {
            color: #666;
            font-size: 0.92rem;
        }

        .generate-btn {
            background: #003B95;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 16px;
            cursor: pointer;
            font-weight: bold;
            white-space: nowrap;
        }

        .generate-btn:hover {
            background: #0056b3;
        }

        .receipt-table {
            width: 100%;
            max-width: none;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #90caf9;
            min-width: 800px; /* Ensure minimum width for all columns */
        }

        .receipt-table-container {
            display: block;
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-x;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .receipt-table th,
        .receipt-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            border: 1px solid #90caf9;
        }

        .receipt-table th {
            background: #90caf9;
            color: #333;
            font-weight: bold;
            font-size: 1.1rem;
            border-bottom: 2px solid #e0e0e0;
        }

        .receipt-table tbody tr {
            transition: all 0.3s ease;
        }

        .receipt-table tbody tr:hover {
            background: #f8f9fa;
            transform: none;
            box-shadow: none;
        }

        .action-btn {
            padding: 10px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            background: white;
            color: #333;
            font-weight: bold;
            margin-right: 8px;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: #f8f9fa;
            border-color: #ccc;
        }

        .receipt-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .receipt-filters input,
        .receipt-filters select {
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .receipt-filters input:focus,
        .receipt-filters select:focus {
            outline: none;
            border-color: #999;
            background: white;
        }

        .print-btn {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }

        .print-btn:hover {
            background: #e9ecef;
            border-color: #ccc;
        }

        @media print {
            .sidebar, .profile-section, .receipt-filters, .action-btn {
                display: none;
            }
        }

        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            margin: 15px 0;
            border-radius: 12px;
            font-weight: 500;
            border: 1px solid #ddd;
        }

        .alert.success {
            background: #f8f9fa;
            color: #333;
            border-color: #ddd;
        }

        .alert.error {
            background: #f8f9fa;
            color: #333;
            border-color: #ddd;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }

            .main-content {
                padding: 15px;
            }

            .receipt-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 15px;
            }

            .receipt-header h2 {
                font-size: 1.5rem;
            }

            .ready-payment-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .generate-btn {
                width: 100%;
            }

            .receipt-filters {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }

            .receipt-filters input {
                width: 100%;
                margin-bottom: 5px;
            }

            .receipt-table-container {
                width: 100%;
                max-width: 100%;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
                margin: 0;
                padding: 0 0 10px;
            }

            .receipt-table {
                min-width: 750px;
                width: max-content;
                font-size: 0.9rem;
            }

            .receipt-table th,
            .receipt-table td {
                padding: 10px 8px;
                font-size: 0.85rem;
                white-space: nowrap;
            }

            /* Set minimum widths for each column */
            .receipt-table th:first-child,
            .receipt-table td:first-child {
                min-width: 120px;
            }

            .receipt-table th:nth-child(2),
            .receipt-table td:nth-child(2) {
                min-width: 140px;
            }

            .receipt-table th:nth-child(3),
            .receipt-table td:nth-child(3) {
                min-width: 90px;
            }

            .receipt-table th:nth-child(4),
            .receipt-table td:nth-child(4) {
                min-width: 90px;
            }

            .receipt-table th:nth-child(5),
            .receipt-table td:nth-child(5) {
                min-width: 80px;
            }

            .receipt-table th:nth-child(6),
            .receipt-table td:nth-child(6) {
                min-width: 160px;
            }

            .action-btn {
                padding: 6px 10px;
                font-size: 0.8rem;
                margin: 1px;
                display: inline-block;
            }

            .alert {
                margin: 10px 0;
                padding: 12px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 10px;
            }

            .receipt-header h2 {
                font-size: 1.3rem;
            }

            .receipt-table-container {
                width: 100%;
                max-width: 100%;
                overflow-x: auto;
                overflow-y: hidden;
                margin: 0;
                padding: 0 0 10px;
            }

            .receipt-table {
                min-width: 700px;
                width: max-content;
                font-size: 0.8rem;
            }

            .receipt-table th,
            .receipt-table td {
                padding: 8px 6px;
                font-size: 0.75rem;
            }

            .action-btn {
                padding: 5px 8px;
                font-size: 0.7rem;
                margin: 1px 0;
                display: block;
                width: 100%;
                margin-bottom: 2px;
            }

            /* Add scroll indicator */
            .receipt-table-container::after {
                content: "← Swipe untuk lihat lebih →";
                display: block;
                text-align: center;
                font-size: 0.7rem;
                color: #666;
                padding: 5px 0;
                background: rgba(144, 202, 249, 0.1);
                margin-top: -1px;
            }

            .alert {
                margin: 8px 0;
                padding: 10px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="receipt-header" style="flex-direction: column; align-items: flex-start;">
                        <a href="dashboard.php" class="btn-back" aria-label="Back" style="
                            position: relative;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            margin: 12px 0 12px 0;
                            background: #90caf9;
                            color: #2c3e50;
                            width: 40px;
                            height: 40px;
                            border-radius: 50%;
                            text-decoration: none;
                            font-weight: bold;
                            font-size: 1.5rem;
                            box-shadow: 0 2px 8px rgba(144, 202, 249, 0.3);
                            transition: all 0.3s ease;
                            border: none;
                            z-index: 1000;
                            padding: 0;
                            cursor: pointer;
                        ">
                                <svg width="28" height="28" viewBox="0 0 28 28" style="display:block; margin:auto;" xmlns="http://www.w3.org/2000/svg">
                                        <polygon points="19,7 19,21 7,14" fill="#fff" />
                                </svg>
                        </a>
            <h2 style="margin-left:2px;">Senarai Resit</h2>
            <p class="receipt-subtitle">Resit dijana secara automatik apabila admin menandakan pembayaran sebagai Dibayar. Anda juga boleh jana semula untuk pembayaran lengkap yang belum ada resit.</p>
        </div>

        <div class="generation-panel">
            <h3>Jana Resit Secara Jelas</h3>
            <p>Pembayaran di bawah sudah berstatus Dibayar tetapi belum mempunyai resit. Klik butang `Jana Resit` untuk hasilkan resit dengan serta-merta.</p>

<?php if (empty($readyPayments)): ?>
            <p style="margin-bottom:0;">Tiada pembayaran lengkap yang menunggu resit.</p>
<?php else: ?>
            <div class="ready-payment-list">
<?php foreach ($readyPayments as $payment): ?>
                <div class="ready-payment-item">
                    <div class="ready-payment-info">
                        <strong><?php echo htmlspecialchars($payment['student_name'] ?: 'Pelajar'); ?> - <?php echo htmlspecialchars($payment['item_name']); ?></strong>
                        <div class="ready-payment-meta">Tarikh: <?php echo date('d/m/Y', strtotime($payment['receipt_date'])); ?> | Jumlah: RM <?php echo number_format((float) $payment['amount'], 2); ?></div>
                        <?php if (!empty($payment['transaction_id'])): ?>
                        <div class="ready-payment-meta">Rujukan: <?php echo htmlspecialchars($payment['transaction_id']); ?></div>
                        <?php endif; ?>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="payment_id" value="<?php echo (int) $payment['id']; ?>">
                        <button type="submit" name="generate_receipt" value="1" class="generate-btn">Jana Resit</button>
                    </form>
                </div>
<?php endforeach; ?>
            </div>
<?php endif; ?>
        </div>

        <div class="receipt-filters">
            <input type="date" id="dateFilter" placeholder="Filter by date">
            <input type="text" id="searchInput" placeholder="Cari resit...">
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="receipt-table-container">
            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>No. Resit</th>
                        <th>Nama Pelajar</th>
                        <th>Nama Item</th>
                        <th>Tarikh</th>
                        <th>Jumlah (RM)</th>
                        <th>Status</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
            <tbody>
                <?php if (empty($receipts)): ?>
                        <tr>
                            <td colspan="7">Belum ada resit yang dijana.</td>
                        </tr>
                <?php else: ?>
                <?php foreach ($receipts as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['receipt_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['student_name'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['item_name'] ?: '-'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['receipt_date'])); ?></td>
                            <td><?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo $row['status'] === 'Selesai' ? 'Dibayar' : htmlspecialchars($row['status']); ?></td>
                            <td>
                                <button class="action-btn" onclick="viewReceipt('<?php echo $row['receipt_no']; ?>')">
                                    Lihat
                                </button>
                                <button class="action-btn print-btn" onclick="printReceipt('<?php echo $row['receipt_no']; ?>')">
                                    Cetak
                                </button>
                            </td>
                        </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <script>
        function viewReceipt(receiptNo) {
            window.location.href = `view_receipt.php?no=${receiptNo}`;
        }

        function printReceipt(receiptNo) {
            window.open(`print_receipt.php?no=${receiptNo}`, '_blank');
        }

        // Search and filter functionality
        document.getElementById('searchInput').addEventListener('keyup', filterReceipts);
        document.getElementById('dateFilter').addEventListener('change', filterReceipts);

        function filterReceipts() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const dateValue = document.getElementById('dateFilter').value;
            const rows = document.querySelectorAll('.receipt-table tbody tr');

            rows.forEach((row) => {
                const rowText = row.textContent.toLowerCase();
                const rowDateText = row.children[3] ? row.children[3].textContent.trim() : '';
                let matchesSearch = rowText.includes(searchValue);
                let matchesDate = true;

                if (dateValue) {
                    const [year, month, day] = dateValue.split('-');
                    const formattedDate = `${day}/${month}/${year}`;
                    matchesDate = rowDateText === formattedDate;
                }

                row.style.display = matchesSearch && matchesDate ? '' : 'none';
            });
        }
    </script>
<?php
require_once __DIR__ . '/includes/admin_notifications.php';
render_admin_notification_center();
?>
</body>
</html>