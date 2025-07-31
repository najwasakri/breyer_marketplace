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
            flex-direction: column;
            align-items: flex-start;
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

    <div class="main-content">
        <div class="receipt-header" style="flex-direction: column; align-items: flex-start;">
            <button onclick="window.history.back()" style="
              display: inline-block;
              margin: 12px 0 12px 0;
              padding: 3px 12px;      /* kecilkan padding */
              background: #FFE45C;
              color: #222;
              border-radius: 50px;    /* kecilkan radius */
              font-weight: bold;      /* pastikan sudah ada */
              font-family: Arial Black, Arial, sans-serif; /* tambah ini untuk lebih tebal */
              text-decoration: none;
              font-size: 1.05rem;     /* kecilkan font */
              box-shadow: 0 2px 8px rgba(44,62,80,0.08);
              transition: background 0.2s;
              border: none;
              letter-spacing: 1px;
              cursor: pointer;
              display: flex;
              align-items: center;
              justify-content: center;
              width: 56px;
              height: 40px;
           ">
                <svg width="24" height="24" viewBox="0 0 24 24">
                    <line x1="18" y1="12" x2="6" y2="12" stroke="#222" stroke-width="3.5" stroke-linecap="round"/>
                    <polyline points="10,8 6,12 10,16" fill="none" stroke="#222" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
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