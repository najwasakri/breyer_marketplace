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
        }

        .main-content {
            flex: 1;
            padding: 20px;
            background: #fff;
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .receipt-table th,
        .receipt-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .receipt-table th {
            background: #FFE45C;
            font-weight: bold;
        }

        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background: #FFE45C;
            font-weight: bold;
        }

        .receipt-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .receipt-filters input,
        .receipt-filters select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .print-btn {
            background: #4CAF50;
            color: white;
        }

        @media print {
            .sidebar, .profile-section, .receipt-filters, .action-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="profile-section">
            <span>ADMIN</span>
            <div class="profile-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
        </div>

        <div class="receipt-header">
            <h2>Senarai Resit</h2>
            <button class="action-btn" onclick="generateNewReceipt()">Resit Baru</button>
        </div>

        <div class="receipt-filters">
            <input type="date" id="dateFilter" placeholder="Filter by date">
            <input type="text" id="searchInput" placeholder="Cari resit...">
            <select id="statusFilter">
                <option value="">Semua Status</option>
                <option value="paid">Dibayar</option>
                <option value="pending">Pending</option>
            </select>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert error"><?php echo $error_message; ?></div>
        <?php endif; ?>

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
                    $sql = "SELECT r.*, p.amount, s.full_name, p.payment_date, p.status 
                            FROM receipts r 
                            JOIN payments p ON r.payment_id = p.id 
                            JOIN students s ON p.student_id = s.id 
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
        document.getElementById('statusFilter').addEventListener('change', filterReceipts);

        function filterReceipts() {
            // Add your filter logic here
        }
    </script>
</body>
</html>