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
            position: fixed;
            top: 20px;
            left: 20px;
            background: #003B95;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0;
        }

        .back-btn::before {
            content: '';
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 8px 12px 8px 0;
            border-color: transparent white transparent transparent;
            transform: translateX(-2px);
        }

        .back-btn:hover {
            background: #002b70;
            transform: translateX(-3px);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .back-btn {
                width: 45px;
                height: 45px;
                top: 15px;
                left: 15px;
            }

            .back-btn::before {
                border-width: 7px 10px 7px 0;
            }
        }

        @media (max-width: 480px) {
            .back-btn {
                width: 40px;
                height: 40px;
                top: 10px;
                left: 10px;
            }

            .back-btn::before {
                border-width: 6px 8px 6px 0;
            }
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-btn" title="Kembali ke Dashboard"></a>
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
