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
    <title>Profile Pelajar - Breyer</title>
    <style>
        body {
            background: linear-gradient(135deg, #D4E5FF 0%, #3B7DD3 100%);
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .profile-container {
            max-width: 400px;
            margin: 20px auto;
            background: linear-gradient(145deg, 
                #ffffff 0%,
                #f0f7ff 35%,
                #e6f2ff 65%,
                #ffffff 100%
            );
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            color: #003B95;
        }
        .form-group {
            margin-bottom: 15px; /* Reduced from 20px */
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #003B95;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 8px;     /* Reduced from 10px */
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;  /* Reduced from 16px */
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
        .profile-image-container {
            width: 120px;      /* Reduced from 150px */
            height: 120px;     /* Reduced from 150px */
            margin: 0 auto 15px;
            position: relative;
        }

        .profile-image {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #003B95;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }

        .default-avatar {
            width: 100%;
            height: 100%;
            background: #003B95;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-btn">← Kembali</a>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-image-container">
                <img src="ads0/breyer-logo-profile.png" alt="Profile" class="profile-image">
            </div>
            <h1>Profile Pelajar</h1>
        </div>
        <form>
            <div class="form-group">
                <label>Nama</label>
                <input type="text" value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" readonly>
            </div>
            <div class="form-group">
                <label>No. Kad Pengenalan</label>
                <input type="text" value="<?php echo htmlspecialchars($_SESSION['ic'] ?? ''); ?>" readonly>
            </div>
        </form>
    </div>
</body>
</html>
