<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Database connection using MySQLi
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'breyer_marketplace';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = 'Semua field mesti diisi.';
        $messageType = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = 'Kata laluan baru minimum 6 aksara.';
        $messageType = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Kata laluan baru tidak sepadan.';
        $messageType = 'error';
    } elseif ($current_password === $new_password) {
        $message = 'Kata laluan baru mesti berbeza dengan kata laluan lama.';
        $messageType = 'error';
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Check if current password is correct
            // Check if password is hashed or plain text
            if (password_verify($current_password, $row['password_hash']) || $current_password === $row['password_hash']) {
                // Update password with proper hashing
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $update_stmt->bind_param("si", $new_password_hash, $_SESSION['user_id']);
                
                if ($update_stmt->execute()) {
                    $message = 'Kata laluan berjaya dikemaskini!';
                    $messageType = 'success';
                } else {
                    $message = 'Ralat semasa mengemaskini kata laluan.';
                    $messageType = 'error';
                }
                $update_stmt->close();
            } else {
                $message = 'Kata laluan semasa tidak betul.';
                $messageType = 'error';
            }
        } else {
            $message = 'Pengguna tidak dijumpai.';
            $messageType = 'error';
        }
        $stmt->close();
    }
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
            /* Hide scrollbar completely */
            -ms-overflow-style: none;  /* Internet Explorer 10+ */
            scrollbar-width: none;  /* Firefox */
            overflow-x: hidden;
            overflow-y: hidden; /* Hide vertical scrollbar too */
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        body::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for all elements */
        * {
            -ms-overflow-style: none;  /* Internet Explorer 10+ */
            scrollbar-width: none;  /* Firefox */
        }

        *::-webkit-scrollbar {
            display: none;
        }

        html {
            overflow: hidden; /* Prevent scrolling on html element */
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
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .submit-btn:hover {
            background: #002a70;
        }
        
        input:focus {
            border-color: #003B95;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 59, 149, 0.3);
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
    <div class="password-container">
        <div class="password-header">
            <h1>Tukar Kata Laluan</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
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
    
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const currentPassword = document.querySelector('input[name="current_password"]').value;
            const newPassword = document.querySelector('input[name="new_password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                alert('Semua field mesti diisi.');
                e.preventDefault();
                return false;
            }
            
            if (newPassword.length < 6) {
                alert('Kata laluan baru minimum 6 aksara.');
                e.preventDefault();
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                alert('Kata laluan baru tidak sepadan.');
                e.preventDefault();
                return false;
            }
            
            if (currentPassword === newPassword) {
                alert('Kata laluan baru mesti berbeza dengan kata laluan lama.');
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('.submit-btn');
            submitBtn.textContent = 'Mengemaskini...';
            submitBtn.disabled = true;
            
            return true;
        });
        
        // Auto-hide success message after 3 seconds
        const successMessage = document.querySelector('.message.success');
        if (successMessage) {
            setTimeout(function() {
                successMessage.style.transition = 'opacity 0.5s';
                successMessage.style.opacity = '0';
                setTimeout(function() {
                    successMessage.style.display = 'none';
                }, 500);
            }, 3000);
        }
    </script>
</body>
</html>
