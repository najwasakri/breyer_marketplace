<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Get admin details for password display
require_once 'includes/db_connect.php';
$admin_password_display = "********"; // Default hidden
if (isset($_SESSION['admin_id'])) {
    $stmt = $conn->prepare("SELECT password_hash FROM admin WHERE admin_id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        // We'll show dots by default, actual password will be fetched via AJAX for security
        $admin_password_display = "********";
    }
    $stmt->close();
}

// Get dashboard stats from database
$stats = [
    'courses' => '5',
    'students' => '150', 
    'sales' => 'RM 150',
    'inventory' => '7'
];

// Try to load stats from database if table exists (prioritize saved stats)
try {
    $result = $conn->query("SELECT stat_type, stat_value FROM dashboard_stats");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stats[$row['stat_type']] = $row['stat_value'];
        }
    }
} catch (Exception $e) {
    // Table doesn't exist yet, use default values
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: #ffffff;
            padding: 20px 0;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            position: relative;
            border-right: 1px solid #e0e0e0;
        }

        .sidebar::before {
            display: none;
        }

        .menu-title {
            background: linear-gradient(135deg, #ff9500 0%, #ffb347 100%);
            color: #ffffff;
            padding: 15px;
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
            border-radius: 0 25px 25px 0;
            margin-right: 10px;
            box-shadow: 0 6px 15px rgba(255, 149, 0, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .menu-items {
            list-style: none;
        }

        .menu-items li {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 2px 0;
            position: relative;
            z-index: 1;
        }

        .menu-items li:hover {
            background: linear-gradient(135deg, #ff9500 0%, #ffa500 100%);
            transform: translateX(8px);
            border-radius: 0 30px 30px 0;
            margin-right: 10px;
            box-shadow: 0 4px 12px rgba(255, 149, 0, 0.4);
        }

        .menu-items li a {
            text-decoration: none;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            font-weight: 500;
        }

        .menu-items li:hover a {
            color: #ffffff;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }

        .menu-items li a::after {
            content: '→';
            position: absolute;
            right: 10px;
            opacity: 0;
            transition: all 0.3s ease;
            color: #ffd700;
        }

        .menu-items li:hover a::after {
            opacity: 1;
            right: 0;
            color: #ffffff;
        }

        .menu-items li.active {
            background: linear-gradient(135deg, #dc143c 0%, #ff6347 100%);
            border-radius: 0 30px 30px 0;
            margin-right: 10px;
            box-shadow: 0 6px 20px rgba(220, 20, 60, 0.5);
        }

        .menu-items li.active a {
            color: white;
            font-weight: bold;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 20px;
            background: linear-gradient(135deg, #fff8dc 0%, #ffeb9c 100%);
            min-height: 100vh;
            position: relative;
        }

        .main-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,165,0,0.1) 25%, transparent 25%, transparent 75%, rgba(255,165,0,0.1) 75%), 
                        linear-gradient(45deg, rgba(255,165,0,0.1) 25%, transparent 25%, transparent 75%, rgba(255,165,0,0.1) 75%);
            background-size: 60px 60px;
            background-position: 0 0, 30px 30px;
            pointer-events: none;
            opacity: 0.4;
        }

        /* Profile Section */
        .profile-section {
            background: linear-gradient(135deg, #ffffff 0%, #fff8dc 100%);
            color: #2c3e50;
            padding: 15px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            position: relative;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(255, 149, 0, 0.3);
            border: 2px solid rgba(255, 165, 0, 0.3);
        }

        .profile-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 149, 0, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 149, 0, 0.3);
        }

        .profile-icon:hover {
            background: rgba(255, 149, 0, 0.2);
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(255, 149, 0, 0.4);
        }

        .profile-icon svg {
            color: #ff9500;
            stroke: #ff9500;
        }

        /* Dashboard Stats Styles */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #fffaf0 100%);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(255, 149, 0, 0.2);
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 165, 0, 0.3);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff9500 0%, #ffa500 100%);
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px rgba(255, 149, 0, 0.3);
            border-color: rgba(255, 149, 0, 0.5);
        }

        .stat-card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: none;
        }

        /* Auto Refresh Controls */
        .stats-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 0 5px;
        }

        .stats-title {
            font-size: 1.4rem;
            font-weight: bold;
            color: #ff9500;
            text-shadow: 0 2px 8px rgba(255, 149, 0, 0.3);
        }
            color: #333;
            margin: 0;
        }

        .auto-refresh-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .auto-refresh-btn {
            background: linear-gradient(135deg, #ff9500 0%, #ffa500 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 8px 25px rgba(255, 149, 0, 0.4);
            border: 2px solid rgba(255,255,255,0.3);
        }

        .auto-refresh-btn:hover {
            background: linear-gradient(135deg, #e68900 0%, #ff8c00 100%);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(255, 149, 0, 0.5);
        }

        .auto-refresh-btn.loading {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            cursor: not-allowed;
            transform: none;
        }

        .auto-refresh-btn .spinner {
            width: 14px;
            height: 14px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: none;
        }

        .auto-refresh-btn.loading .spinner {
            display: block;
        }

        .refresh-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: #666;
        }

        .toggle-switch {
            position: relative;
            width: 40px;
            height: 20px;
            background: #ccc;
            border-radius: 20px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .toggle-switch.active {
            background: #2056a8;
        }

        .toggle-slider {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 16px;
            height: 16px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s;
        }

        .toggle-switch.active .toggle-slider {
            transform: translateX(20px);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Auto sync indicator */
        .auto-sync-indicator {
            position: fixed;
            top: 70px;
            right: 20px;
            background: rgba(32, 86, 168, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: none;
            align-items: center;
            gap: 8px;
            z-index: 9999;
            animation: slideInRight 0.3s ease-out;
        }

        .auto-sync-indicator.show {
            display: flex;
        }

        .auto-sync-indicator .dot-pulse {
            width: 8px;
            height: 8px;
            background: #FFE45C;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        @keyframes pulseScale {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Slider Styles */
        .slider-container {
            width: 100%;
            max-width: 1200px;
            margin: 10px auto;
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .slider {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        .slide {
            min-width: 100%;
            height: 350px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .slide-content {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: white;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }

        .slider-dots {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .dot {
            width: 12px;
            height: 12px;
            background: rgba(255,255,255,0.5);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dot.active {
            background: #ff9500;
            transform: scale(1.2);
            box-shadow: 0 2px 8px rgba(255, 149, 0, 0.5);
        }

        /* Profile Dropdown Styles */
        .profile-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            min-width: 220px;
            z-index: 100;
            overflow: hidden;
            display: flex;
            flex-direction: column; /* Pastikan menegak */
            padding: 0;
        }

        .profile-dropdown a {
            padding: 18px 24px;
            color: #2c3e50;
            text-decoration: none;
            font-size: 1.15rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 1px solid #ecf0f1;
            display: block;
            text-align: left;
        }

        .profile-dropdown a:last-child {
            border-bottom: none;
        }

        .profile-dropdown a:hover {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            transform: translateX(5px);
        }

        /* Modal Profile Admin Styles */
        .modal-profile-admin {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.18);
            justify-content: center;
            align-items: center;
        }

        .modal-content-profile {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            padding: 24px 18px 18px 18px;
            max-width: 320px;
            width: 98%;
            text-align: center;
            position: relative;
            margin: auto;
            border: 1px solid rgba(52, 152, 219, 0.1);
        }

        .close-profile {
            position: absolute;
            top: 18px;
            right: 24px;
            font-size: 2rem;
            color: #e74c3c;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .close-profile:hover {
            color: #c0392b;
            transform: scale(1.1);
        }

        .avatar-profile {
            width: 90px;
            height: 90px;
            margin: 0 auto 18px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #3498db;
            border-radius: 50%;
            background: linear-gradient(135deg, #ebf3fd 0%, #d6eaff 100%);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }
        .avatar-profile svg {
            width: 56px;
            height: 56px;
            color: #3498db;
        }

        .profile-title {
            margin-bottom: 20px;
            font-size: 1.6rem;
            color: #2c3e50;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .profile-label {
            margin-top: 12px;
            margin-bottom: 5px;
            font-size: 1.08rem;
            color: #34495e;
            font-weight: bold;
            text-align: left;
        }

        .profile-input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 10px;
            border: 2px solid #bdc3c7;
            background: #ffffff;
            font-size: 1rem;
            margin-bottom: 2px;
            color: #2c3e50;
            outline: none;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .profile-input[readonly] {
            background: #ecf0f1;
            border: 2px solid #d5dbdb;
        }
        .profile-input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        /* Password Input Container */
        .password-input-container {
            position: relative;
            width: 100%;
        }

        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #3498db;
            font-size: 16px;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .password-toggle-btn:hover {
            color: #2980b9;
            transform: translateY(-50%) scale(1.1);
        }

        .password-toggle-btn svg {
            width: 18px;
            height: 18px;
        }

        /* Password hidden message styling */
        .password-hidden-message {
            font-size: 0.85rem !important;
            color: #7f8c8d !important;
            font-style: italic !important;
            text-align: center !important;
            letter-spacing: 0.3px !important;
            background: linear-gradient(135deg, #ecf0f1 0%, #d5dbdb 100%) !important;
            border: 1.5px dashed #bdc3c7 !important;
        }

        .btn-change-password {
            margin-top: 18px;
            padding: 14px 0;
            width: 100%;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1.08rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        .btn-change-password:hover {
            background: linear-gradient(135deg, #2980b9 0%, #21618c 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .btn-change-password:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* Success and Error Messages */
        .alert-success {
            background: linear-gradient(135deg, #d5f4e6 0%, #c8e6c9 100%);
            color: #1b5e20;
            padding: 16px 20px;
            margin: 0;
            border-radius: 12px;
            border: 2px solid #4caf50;
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.25);
            font-size: 1.1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
            animation: slideInRight 0.5s ease-out, fadeOut 0.5s ease-in 4.5s forwards;
            cursor: pointer;
        }

        .alert-success::before {
            content: "✓";
            background: #4caf50;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
        }

        .alert-success::after {
            content: "×";
            font-size: 18px;
            font-weight: bold;
            color: #1b5e20;
            margin-left: auto;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .alert-success:hover::after {
            opacity: 1;
        }

        .alert-error {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            color: #b71c1c;
            padding: 16px 20px;
            margin: 0;
            border-radius: 12px;
            border: 2px solid #f44336;
            box-shadow: 0 6px 20px rgba(244, 67, 54, 0.25);
            font-size: 1.1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
            animation: slideInRight 0.5s ease-out, fadeOut 0.5s ease-in 4.5s forwards;
            cursor: pointer;
        }

        .alert-error::before {
            content: "✗";
            background: #f44336;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
        }

        .alert-error::after {
            content: "×";
            font-size: 18px;
            font-weight: bold;
            color: #b71c1c;
            margin-left: auto;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .alert-error:hover::after {
            opacity: 1;
        }
            opacity: 1;
        }

        /* Animations */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        /* Change Password Dropdown Styles */
        .change-password-dropdown {
            background: #f6f8fa;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(32,86,168,0.07);
            margin-top: 16px;
            padding: 16px 10px 10px 10px;
            width: 100%;
        }

        .change-password-dropdown form {
            padding: 18px;
        }

        .modal-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .modal-title-row .profile-title {
            margin-bottom: 0;
            font-size: 1.3rem;
        }
        .modal-title-row .close-profile {
            position: static;
            font-size: 2rem;
            margin-left: 12px;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                padding: 10px 0;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
            }

            .menu-title {
                border-radius: 10px;
                margin: 0 10px;
                font-size: 1rem;
                padding: 12px;
            }

            .menu-items {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 5px;
                padding: 10px;
            }

            .menu-items li {
                flex: 1;
                min-width: 140px;
                max-width: 160px;
                margin: 2px;
                padding: 10px 8px;
                text-align: center;
                border-radius: 8px;
                border-bottom: none;
            }

            .menu-items li:hover {
                transform: none;
                border-radius: 8px;
                margin: 2px;
            }

            .menu-items li a {
                font-size: 0.85rem;
                justify-content: center;
                text-align: center;
            }

            .menu-items li.active {
                border-radius: 8px;
                margin: 2px;
            }

            .main-content {
                padding: 15px;
                min-height: auto;
            }

            .profile-section {
                padding: 12px;
                margin-bottom: 15px;
                border-radius: 10px;
            }

            .profile-section span {
                font-size: 0.9rem;
            }

            .profile-icon {
                width: 35px;
                height: 35px;
            }

            .stats-controls {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .stats-title {
                font-size: 1.2rem;
            }

            .auto-refresh-controls {
                width: 100%;
                justify-content: center;
            }

            .auto-refresh-btn {
                padding: 10px 16px;
                font-size: 0.9rem;
            }

            .dashboard-stats {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
                margin-top: 15px;
            }

            .stat-card {
                padding: 20px 15px;
                border-radius: 15px;
            }

            .stat-card h3 {
                font-size: 1rem;
                margin-bottom: 10px;
            }

            .stat-card .number {
                font-size: 28px;
            }

            .slider-container {
                margin: 15px auto;
                height: 200px;
            }

            .slide {
                height: 200px;
            }

            .modal-content-profile {
                max-width: 300px;
                padding: 20px 15px 15px 15px;
                margin: 20px;
            }

            .profile-title {
                font-size: 1.4rem;
            }

            .profile-input {
                padding: 10px 12px;
                font-size: 0.95rem;
            }

            .btn-change-password {
                padding: 12px 0;
                font-size: 1rem;
            }

            .auto-sync-indicator {
                top: 80px;
                right: 10px;
                font-size: 0.75rem;
                padding: 6px 10px;
            }

            .profile-dropdown {
                right: -10px;
                min-width: 180px;
            }

            .profile-dropdown a {
                padding: 15px 20px;
                font-size: 1rem;
            }
        }

        /* Extra Small Mobile Styles */
        @media (max-width: 480px) {
            .sidebar {
                padding: 8px 0;
            }

            .menu-title {
                font-size: 0.9rem;
                padding: 10px;
            }

            .menu-items {
                padding: 8px;
                gap: 3px;
            }

            .menu-items li {
                min-width: 120px;
                max-width: 140px;
                padding: 8px 6px;
            }

            .menu-items li a {
                font-size: 0.8rem;
            }

            .main-content {
                padding: 10px;
            }

            .profile-section {
                padding: 10px;
                margin-bottom: 12px;
            }

            .profile-section span {
                font-size: 0.85rem;
            }

            .profile-icon {
                width: 32px;
                height: 32px;
            }

            .stats-title {
                font-size: 1.1rem;
            }

            .auto-refresh-btn {
                padding: 8px 12px;
                font-size: 0.85rem;
            }

            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .stat-card {
                padding: 15px 12px;
            }

            .stat-card h3 {
                font-size: 0.9rem;
                margin-bottom: 8px;
            }

            .stat-card .number {
                font-size: 24px;
            }

            .slider-container {
                height: 160px;
                margin: 10px auto;
            }

            .slide {
                height: 160px;
            }

            .modal-content-profile {
                max-width: 280px;
                padding: 18px 12px 12px 12px;
                margin: 15px;
            }

            .profile-title {
                font-size: 1.3rem;
            }

            .avatar-profile {
                width: 80px;
                height: 80px;
                margin-bottom: 15px;
            }

            .avatar-profile svg {
                width: 48px;
                height: 48px;
            }

            .profile-input {
                padding: 9px 10px;
                font-size: 0.9rem;
            }

            .btn-change-password {
                padding: 10px 0;
                font-size: 0.95rem;
            }

            .auto-sync-indicator {
                top: 70px;
                right: 8px;
                font-size: 0.7rem;
                padding: 5px 8px;
            }

            .profile-dropdown {
                right: -15px;
                min-width: 160px;
            }

            .profile-dropdown a {
                padding: 12px 16px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <!-- Tambah alert messages selepas <body> -->
    <?php if (isset($_SESSION['password_success'])): ?>
        <div class="alert-success">
            <?php echo $_SESSION['password_success']; unset($_SESSION['password_success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['password_error'])): ?>
        <div class="alert-error">
            <?php echo $_SESSION['password_error']; unset($_SESSION['password_error']); ?>
        </div>
    <?php endif; ?>

    <!-- Auto Sync Indicator -->
    <div class="auto-sync-indicator" id="autoSyncIndicator">
        <div class="dot-pulse"></div>
        <span>Auto sync aktif</span>
    </div>

    <div class="sidebar">
        <div class="menu-title">MENU UTAMA</div>
        <ul class="menu-items">
            <li class="active"><a href="dashboard.php">HALAMAN UTAMA</a></li>
            <li><a href="manage_courses.php">SENARAI KURSUS</a></li>
            <li><a href="manage_inventory.php">INVENTORI</a></li>
            <li><a href="manage_payments.php">PESANAN</a></li>
            <li><a href="manage_receipts.php">RESIT</a></li>
            <li><a href="support_tickets.php">HELP & SUPPORT</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="profile-section" id="profileSection">
            <span>ADMIN</span>
            <div class="profile-icon" id="profileIcon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="profile-dropdown" id="profileDropdown" style="display:none;">
                <a href="profile_admin.php">Profile</a>
                <a href="payment_history.php">Sejarah Pembayaran</a>
                <a href="change_password.php">Tukar Kata Laluan</a>
                <a href="logout.php">Log Keluar</a>
            </div>
        </div>

        <!-- Add Slider Container -->
        <div class="slider-container">
            <div class="slider">
                <div class="slide" style="background-image: url('banner1/banner-logo.png');">
                    <div class="slide-content">
                        <h3></h3>
                        <p></p>
                    </div>
                </div>
                <div class="slide" style="background-image: url('banner2/banner-course.png');">
                    <div class="slide-content">
                        <h3></h3>
                        <p></p>
                    </div>
                </div>
                <div class="slide" style="background-image: url('banner3/banner-guarantee.png');">
                    <div class="slide-content">
                        <h3></h3>
                        <p></p>
                    </div>
                </div>
            </div>
            <div class="slider-dots">
                <div class="dot active"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>

        <!-- Stats Controls -->
        <div class="stats-controls">
            <h2 class="stats-title">Statistik Dashboard</h2>
            <div class="auto-refresh-controls">
                <button class="auto-refresh-btn" id="syncRealDataBtn" style="background: #28a745;" title="Sync dengan data sebenar dari sistem">
                    <div class="spinner"></div>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                    Sync Data
                </button>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Kursus</h3>
                <div class="number" data-type="courses"><?php echo htmlspecialchars($stats['courses']); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Pelajar</h3>
                <div class="number" data-type="students"><?php echo htmlspecialchars($stats['students']); ?></div>
            </div>
            <div class="stat-card">
                <h3>Jumlah Jualan</h3>
                <div class="number" data-type="sales"><?php echo htmlspecialchars($stats['sales']); ?></div>
            </div>
            <div class="stat-card">
                <h3>Inventori</h3>
                <div class="number" data-type="inventory"><?php echo htmlspecialchars($stats['inventory']); ?></div>
            </div>
        </div>
    </div>

    <!-- Modal Profile Admin -->
    <div id="profileAdminModal" class="modal-profile-admin">
        <div class="modal-content-profile">
            <span class="close-profile" id="closeProfileModal">&times;</span>
            <div class="avatar-profile">
                <svg width="80" height="80" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="40" cy="40" r="38" fill="#eaf2fb" stroke="#1976d2" stroke-width="3" />
                    <circle cx="40" cy="32" r="14" fill="#b0bec5" />
                    <ellipse cx="40" cy="56" rx="20" ry="12" fill="#b0bec5" />
                </svg>
            </div>
            <h2 class="profile-title">Profile Admin</h2>
            <div class="profile-label">Nama</div>
            <input class="profile-input" type="text" value="<?php echo htmlspecialchars($_SESSION['admin_nama']); ?>" readonly>
            <div class="profile-label">No. Kad Pengenalan</div>
            <input class="profile-input" type="text" value="<?php echo htmlspecialchars($_SESSION['admin_ic']); ?>" readonly>
            <div class="profile-label">Kata Laluan</div>
            <div class="password-input-container">
                <input class="profile-input" type="password" value="••••••••" readonly id="adminPasswordField">
                <button type="button" class="password-toggle-btn" id="toggleAdminPassword">
                    <svg id="eyeIcon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg id="eyeOffIcon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
            </div>
            <button class="btn-change-password" id="showChangePassword">Tukar Kata Laluan</button>
        </div>
    </div>

    <!-- Modal Tukar Kata Laluan -->
    <div id="changePasswordModal" class="modal-profile-admin" style="display:none;">
        <div class="modal-content-profile">
            <div class="modal-title-row">
                <h2 class="profile-title" style="margin-bottom:0;">Tukar Kata Laluan</h2>
                <span class="close-profile" id="closeChangePasswordModal">&times;</span>
            </div>
            <form method="post" action="change_password_process.php" id="changePasswordForm">
                <div id="changePasswordError" style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px; border-radius: 5px; display: none;"></div>
                
                <div class="profile-label">Kata Laluan Lama</div>
                <input class="profile-input" type="password" name="old_password" id="old_password" required>
                
                <div class="profile-label">Kata Laluan Baru</div>
                <input class="profile-input" type="password" name="new_password" id="new_password" required minlength="6">
                
                <div class="profile-label">Sahkan Kata Laluan Baru</div>
                <input class="profile-input" type="password" name="confirm_password" id="confirm_password" required>
                
                <button type="submit" class="btn-change-password" style="margin-top:10px;">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Add JavaScript for Slider -->
    <script>
        const slider = document.querySelector('.slider');
        const dots = document.querySelectorAll('.dot');
        let currentSlide = 0;

        function updateSlider() {
            slider.style.transform = `translateX(-${currentSlide * 100}%)`;
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }

        // Auto advance slides
        setInterval(() => {
            currentSlide = (currentSlide + 1) % 3;
            updateSlider();
        }, 5000);

        // Click on dots to change slides
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                updateSlider();
            });
        });
    </script>
    <script>
const profileIcon = document.getElementById('profileIcon');
const profileDropdown = document.getElementById('profileDropdown');
const profileSection = document.getElementById('profileSection');

profileIcon.addEventListener('click', function(e) {
    e.stopPropagation();
    // TUTUP dropdown, BUKA modal
    profileDropdown.style.display = 'none';
    document.getElementById('profileAdminModal').style.display = 'flex';
});

// Tutup modal bila klik X atau luar modal
document.getElementById('closeProfileModal').onclick = function() {
    document.getElementById('profileAdminModal').style.display = 'none';
};
window.onclick = function(event) {
    const modal = document.getElementById('profileAdminModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
};

// Toggle modal tukar kata laluan
document.getElementById('showChangePassword').onclick = function() {
    document.getElementById('profileAdminModal').style.display = 'none';
    document.getElementById('changePasswordModal').style.display = 'flex';
};

// Tutup modal tukar kata laluan
document.getElementById('closeChangePasswordModal').onclick = function() {
    document.getElementById('changePasswordModal').style.display = 'none';
    // Clear form when closing
    document.getElementById('changePasswordForm').reset();
    document.getElementById('changePasswordError').style.display = 'none';
};
window.onclick = function(event) {
    const profileModal = document.getElementById('profileAdminModal');
    const changeModal = document.getElementById('changePasswordModal');
    if (event.target === profileModal) {
        profileModal.style.display = 'none';
    }
    if (event.target === changeModal) {
        changeModal.style.display = 'none';
        // Clear form when closing
        document.getElementById('changePasswordForm').reset();
        document.getElementById('changePasswordError').style.display = 'none';
    }
};

// Form validation for change password
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const oldPassword = document.getElementById('old_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const errorDiv = document.getElementById('changePasswordError');
    
    // Reset error display
    errorDiv.style.display = 'none';
    
    // Validation
    if (!oldPassword || !newPassword || !confirmPassword) {
        e.preventDefault();
        errorDiv.textContent = 'Semua field mesti diisi.';
        errorDiv.style.display = 'block';
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        errorDiv.textContent = 'Kata laluan baru minimum 6 aksara.';
        errorDiv.style.display = 'block';
        return false;
    }
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        errorDiv.textContent = 'Kata laluan baru tidak sepadan.';
        errorDiv.style.display = 'block';
        return false;
    }
    
    if (oldPassword === newPassword) {
        e.preventDefault();
        errorDiv.textContent = 'Kata laluan baru mesti berbeza dengan kata laluan lama.';
        errorDiv.style.display = 'block';
        return false;
    }
    
    // If validation passes, show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.textContent = 'Menyimpan...';
    submitBtn.disabled = true;
    
    return true;
});

// Handle notification dismissal
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-success, .alert-error');
    alerts.forEach(function(alert) {
        alert.addEventListener('click', function() {
            this.style.animation = 'fadeOut 0.3s ease-in forwards';
        });
    });
});

// Password visibility toggle for admin profile
document.getElementById('toggleAdminPassword').addEventListener('click', function() {
    const passwordField = document.getElementById('adminPasswordField');
    const eyeIcon = document.getElementById('eyeIcon');
    const eyeOffIcon = document.getElementById('eyeOffIcon');
    
    if (passwordField.type === 'password') {
        // Show informative message with better styling
        passwordField.type = 'text';
        passwordField.value = '🔒 Password dilindungi untuk keselamatan';
        passwordField.className = 'profile-input password-hidden-message';
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'block';
    } else {
        // Hide password - back to dots with normal styling
        passwordField.type = 'password';
        passwordField.value = '••••••••';
        passwordField.className = 'profile-input';
        eyeIcon.style.display = 'block';
        eyeOffIcon.style.display = 'none';
    }
});

// Auto Refresh Functionality
document.addEventListener('DOMContentLoaded', function() {
    const syncRealDataBtn = document.getElementById('syncRealDataBtn');
    
    // Sync Real Data button
    syncRealDataBtn.addEventListener('click', function() {
        syncRealData();
    });
    
    // Function to sync with real data from actual system tables
    function syncRealData() {
        const btn = syncRealDataBtn;
        const spinner = btn.querySelector('.spinner');
        const icon = btn.querySelector('svg');
        
        // Show loading state
        btn.classList.add('loading');
        btn.disabled = true;
        spinner.style.display = 'block';
        icon.style.display = 'none';
        btn.innerHTML = btn.innerHTML.replace('Sync Data Sebenar', 'Menyegerak...');
        
        fetch('sync_real_data.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update each stat with animation
                    Object.keys(data.stats).forEach(statType => {
                        const element = document.querySelector(`[data-type="${statType}"]`);
                        if (element) {
                            const oldValue = element.textContent;
                            const newValue = data.stats[statType];
                            
                            // Always animate to show sync happened
                            element.style.animation = 'none';
                            setTimeout(() => {
                                element.style.animation = 'pulseScale 0.6s ease-in-out';
                                element.textContent = newValue;
                                element.style.color = '#28a745';
                                setTimeout(() => {
                                    element.style.color = '#FFE45C';
                                }, 600);
                            }, 50);
                        }
                    });
                    
                    // Show clean success message
                    showNotification('✅ Data berjaya disegerakkan dan KEKAL dalam database!', 'success', 4000);
                    
                } else {
                    showNotification('Ralat: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Ralat rangkaian semasa menyegerak data', 'error');
            })
            .finally(() => {
                // Reset loading state
                btn.classList.remove('loading');
                btn.disabled = false;
                spinner.style.display = 'none';
                icon.style.display = 'block';
                btn.innerHTML = btn.innerHTML.replace('Menyegerak...', 'Sync Data Sebenar');
            });
    }
    
    // Function to show notification (for sync)
    function showNotification(message, type, duration = 3000) {
        // Remove existing notifications
        const existingNotifs = document.querySelectorAll('.temp-notification');
        existingNotifs.forEach(notif => notif.remove());
        
        const notification = document.createElement('div');
        notification.className = type === 'success' ? 'alert-success temp-notification' : 'alert-error temp-notification';
        notification.innerHTML = message; // Use innerHTML instead of textContent for HTML content
        
        document.body.appendChild(notification);
        
        // Auto remove after specified duration
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, duration);
        
        // Remove on click
        notification.addEventListener('click', function() {
            this.remove();
        });
    }

    // Mobile optimizations
    function handleMobileInteractions() {
        // Touch-friendly interactions for mobile
        const menuItems = document.querySelectorAll('.menu-items li');
        
        menuItems.forEach(item => {
            // Add touch feedback
            item.addEventListener('touchstart', function() {
                this.style.opacity = '0.7';
            });
            
            item.addEventListener('touchend', function() {
                this.style.opacity = '1';
            });
        });

        // Optimize modal for mobile
        const modal = document.getElementById('profileAdminModal');
        const changePasswordModal = document.getElementById('changePasswordModal');
        
        // Prevent body scroll when modal is open on mobile
        function preventBodyScroll(modalElement) {
            if (modalElement && window.innerWidth <= 768) {
                if (modalElement.style.display === 'flex') {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = 'auto';
                }
            }
        }

        // Watch for modal changes
        if (modal) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        preventBodyScroll(modal);
                    }
                });
            });
            observer.observe(modal, { attributes: true });
        }

        if (changePasswordModal) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        preventBodyScroll(changePasswordModal);
                    }
                });
            });
            observer.observe(changePasswordModal, { attributes: true });
        }
    }

    // Initialize mobile optimizations when DOM is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', handleMobileInteractions);
    } else {
        handleMobileInteractions();
    }
});
</script>
</body>
</html>
