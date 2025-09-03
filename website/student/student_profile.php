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
        .profile-container {
            max-width: 450px;
            margin: 20px auto;
            background: linear-gradient(145deg, 
                #ffffff 0%,
                #f8fbff 25%,
                #f0f7ff 50%,
                #e6f2ff 75%,
                #ffffff 100%
            );
            padding: 30px;
            border-radius: 25px;
            box-shadow: 
                0 15px 35px rgba(0, 59, 149, 0.15),
                0 5px 15px rgba(0,0,0,0.1),
                inset 0 1px 0 rgba(255,255,255,0.8);
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .profile-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transform: rotate(45deg);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            50% { transform: translateX(0%) translateY(0%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        .profile-header {
            text-align: center;
            margin-bottom: 35px;
            color: #003B95;
            position: relative;
            z-index: 2;
        }

        .profile-header h1 {
            color: #003B95;
            font-size: 2.2rem;
            font-weight: 700;
            margin: 15px 0;
            text-shadow: 0 2px 4px rgba(0, 59, 149, 0.2);
            letter-spacing: 1px;
        }
        .form-group {
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            color: #003B95;
            font-weight: 600;
            font-size: 1.1rem;
            text-shadow: 0 1px 2px rgba(0, 59, 149, 0.1);
        }
        
        input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1ecf7;
            border-radius: 15px;
            font-size: 16px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 
                inset 0 2px 4px rgba(0,0,0,0.05),
                0 4px 8px rgba(0, 59, 149, 0.1);
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        input:focus {
            outline: none;
            border-color: #003B95;
            box-shadow: 
                inset 0 2px 4px rgba(0,0,0,0.05),
                0 0 20px rgba(0, 59, 149, 0.2),
                0 4px 15px rgba(0, 59, 149, 0.15);
            transform: translateY(-2px);
        }

        input:hover {
            border-color: #4a90e2;
            box-shadow: 
                inset 0 2px 4px rgba(0,0,0,0.05),
                0 6px 12px rgba(0, 59, 149, 0.15);
        }
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: linear-gradient(135deg, #003B95 0%, #4a90e2 100%);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 
                0 8px 20px rgba(0, 59, 149, 0.3),
                0 4px 10px rgba(0,0,0,0.1);
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
            border-width: 10px 15px 10px 0;
            border-color: transparent white transparent transparent;
            transform: translateX(-2px);
        }

        .back-btn:hover {
            background: linear-gradient(135deg, #002b70 0%, #003B95 100%);
            transform: translateY(-3px) scale(1.1);
            box-shadow: 
                0 12px 25px rgba(0, 59, 149, 0.4),
                0 6px 15px rgba(0,0,0,0.2);
        }
        .profile-image-container {
            width: 140px;
            height: 140px;
            margin: 0 auto 20px;
            position: relative;
            z-index: 2;
        }

        .profile-image {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #003B95;
            box-shadow: 
                0 8px 25px rgba(0, 59, 149, 0.3),
                0 0 20px rgba(0, 59, 149, 0.1);
            transition: all 0.3s ease;
        }

        .profile-image:hover {
            transform: scale(1.05);
            box-shadow: 
                0 12px 35px rgba(0, 59, 149, 0.4),
                0 0 30px rgba(0, 59, 149, 0.2);
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
