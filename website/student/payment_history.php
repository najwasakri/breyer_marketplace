<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sejarah Pembayaran - Breyer</title>
    <style>
        body {
            background: linear-gradient(135deg, #D4E5FF 0%, #3B7DD3 100%);
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .history-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .history-header {
            text-align: center;
            margin-bottom: 30px;
            color: #003B95;
        }
        .transaction {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .back-btn {
            background: #003B95;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-btn">← Kembali</a>
    <div class="history-container">
        <div class="history-header">
            <h1>Sejarah Pembayaran</h1>
        </div>
        <div class="transactions">
            <!-- Sample transactions - replace with database data -->
            <div class="transaction">
                <div>
                    <h3>Baju Korporat</h3>
                    <p>Tarikh: 2023-10-01</p>
                </div>
                <div>RM85.00</div>
            </div>
        </div>
    </div>
</body>
</html>
