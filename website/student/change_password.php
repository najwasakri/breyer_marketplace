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
    <title>Tukar Kata Laluan - Breyer</title>
    <style>
        body {
            background: linear-gradient(135deg, #D4E5FF 0%, #3B7DD3 100%);
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .password-container {
            max-width: 500px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .password-header {
            text-align: center;
            margin-bottom: 30px;
            color: #003B95;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #003B95;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .submit-btn {
            background: #003B95;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
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
    <div class="password-container">
        <div class="password-header">
            <h1>Tukar Kata Laluan</h1>
        </div>
        <form method="POST">
            <div class="form-group">
                <label>Kata Laluan Semasa</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>Kata Laluan Baru</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="form-group">
                <label>Sahkan Kata Laluan Baru</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="submit-btn">Tukar Kata Laluan</button>
        </form>
    </div>
</body>
</html>
