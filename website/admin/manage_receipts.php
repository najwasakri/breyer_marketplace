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

// Handle receipt generation
if (isset($_POST['generate_receipt'])) {
    try {
        $payment_id = $_POST['payment_id'];
        $receipt_no = 'R' . date('Ymd') . rand(1000, 9999);
        
        $sql = "INSERT INTO receipts (payment_id, receipt_no) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $payment_id, $receipt_no);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $success_message = "Receipt generated successfully!";
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
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

        .receipt-table {
            width: 100%;
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
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
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
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 0 -15px; /* Extend to screen edges */
                padding: 0 15px;
            }

            .receipt-table {
                min-width: 750px;
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
                margin: 0 -10px;
                padding: 0 10px;
            }

            .receipt-table {
                min-width: 700px;
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
                        <th>Tarikh</th>
                        <th>Jumlah (RM)</th>
                        <th>Status</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
            <tbody>
                <?php
                try {
                    $sql = "SELECT r.receipt_no, s.full_name, p.payment_date, p.amount, p.status
                            FROM receipts r
                            JOIN payments p ON r.payment_id = p.id
                            JOIN students s ON p.student_id = s.id
                            WHERE p.status = 'Selesai'
                            ORDER BY r.created_at DESC";
                    
                    if (!($result = $conn->query($sql))) {
                        throw new Exception("Query failed: " . $conn->error);
                    }

                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['receipt_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['payment_date'])); ?></td>
                            <td><?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td>
                                <button class="action-btn" onclick="viewReceipt('<?php echo $row['receipt_no']; ?>')">
                                    Lihat
                                </button>
                                <button class="action-btn print-btn" onclick="printReceipt('<?php echo $row['receipt_no']; ?>')">
                                    Cetak
                                </button>
                            </td>
                        </tr>
                    <?php endwhile;
                } catch (Exception $e) {
                    echo '<tr><td colspan="6">Error: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>

    <script>
        function generateNewReceipt() {
            let paymentId = prompt("Masukkan ID Pembayaran:");
            if (paymentId) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'payment_id';
                input.value = paymentId;

                let submitInput = document.createElement('input');
                submitInput.type = 'hidden';
                submitInput.name = 'generate_receipt';
                submitInput.value = '1';

                form.appendChild(input);
                form.appendChild(submitInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

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
            // Add your filter logic here
        }
    </script>
</body>
</html>