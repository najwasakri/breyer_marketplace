<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'db_connect.php';

$studentName = trim((string) ($_SESSION['name'] ?? 'Pelajar'));
$studentIc = trim((string) ($_SESSION['ic'] ?? ''));
$studentTicketLabel = $studentName;

if ($studentIc !== '') {
    $studentTicketLabel .= ' (IC: ' . $studentIc . ')';
}

$supportTicketsTableSql = "CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    admin_reply TEXT DEFAULT NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

$complaintFlash = $_SESSION['complaint_flash'] ?? null;
$complaintOld = $_SESSION['complaint_old'] ?? [
    'complaint_type' => '',
    'complaint_title' => '',
    'complaint_details' => ''
];
unset($_SESSION['complaint_flash']);
unset($_SESSION['complaint_old']);

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && (
        isset($_POST['submit_complaint'])
        || isset($_POST['complaint_type'], $_POST['complaint_title'], $_POST['complaint_details'])
    )
) {
    $complaintType = trim($_POST['complaint_type'] ?? '');
    $complaintTitle = trim($_POST['complaint_title'] ?? '');
    $complaintDetails = trim($_POST['complaint_details'] ?? '');

    $typeLabels = [
        'pembayaran' => 'Masalah Pembayaran',
        'produk' => 'Masalah Produk',
        'perkhidmatan' => 'Masalah Perkhidmatan',
        'sistem' => 'Masalah Sistem/Website',
        'lain-lain' => 'Lain-lain'
    ];

    if ($complaintType === '' || $complaintTitle === '' || $complaintDetails === '' || !isset($typeLabels[$complaintType])) {
        $_SESSION['complaint_old'] = [
            'complaint_type' => $complaintType,
            'complaint_title' => $complaintTitle,
            'complaint_details' => $complaintDetails
        ];
        $_SESSION['complaint_flash'] = [
            'type' => 'error',
            'message' => 'Sila lengkapkan semua maklumat aduan yang diperlukan.'
        ];
    } else {
        $subject = '[' . strtoupper($complaintType) . '] ' . $complaintTitle;
        $subject = function_exists('mb_substr') ? mb_substr($subject, 0, 255) : substr($subject, 0, 255);

        $message = "Jenis Aduan: {$typeLabels[$complaintType]}\n";
        $message .= "Nama Pelajar: {$studentName}\n";

        if ($studentIc !== '') {
            $message .= "No. IC: {$studentIc}\n";
        }

        $message .= "\nButiran Aduan:\n{$complaintDetails}";

        try {
            $pdo->exec($supportTicketsTableSql);

            $insertStmt = $pdo->prepare(
                'INSERT INTO support_tickets (user_email, subject, message, status) VALUES (?, ?, ?, ?)' 
            );
            $insertStmt->execute([$studentTicketLabel, $subject, $message, 'open']);

            unset($_SESSION['complaint_old']);
            $_SESSION['complaint_flash'] = [
                'type' => 'success',
                'message' => 'Aduan anda telah berjaya dihantar ke Help & Support.'
            ];
        } catch (Throwable $exception) {
            $_SESSION['complaint_old'] = [
                'complaint_type' => $complaintType,
                'complaint_title' => $complaintTitle,
                'complaint_details' => $complaintDetails
            ];
            $_SESSION['complaint_flash'] = [
                'type' => 'error',
                'message' => 'Aduan gagal dihantar. Sila cuba lagi.'
            ];
        }
    }

    header('Location: dashboard.php');
    exit;
}

$complaintTickets = [];

try {
    $pdo->exec($supportTicketsTableSql);

    $ticketStmt = $pdo->prepare(
        'SELECT id, subject, message, admin_reply, status, created_at
         FROM support_tickets
         WHERE user_email = ?
         ORDER BY created_at DESC'
    );
    $ticketStmt->execute([$studentTicketLabel]);
    $complaintTickets = $ticketStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $exception) {
    $complaintTickets = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Breyer iklan</title>
    <style>
        /* Reset and Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
            font-family: Arial, sans-serif;
            background: url('dashboard/bg.homepage.png') center center/cover no-repeat;
            /* Tukar path dan nama fail ikut gambar anda sendiri */
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: url('dashboard/bg.homepage.png') center center/cover no-repeat;
            filter: blur(0px);
            z-index: -1;
        }



        /* Header Styles */
        .header {
            padding: 2rem 3rem;
            display: flex;
            flex-direction: column;
            gap: 2rem;
            align-items: center;
            position: relative;
            z-index: 2;
            height: auto;
            min-height: 200px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .top-nav {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 0.5rem;
            position: relative;
        }

        .logo-container {
            width: 200px; /* Reduced from 250px */
            padding: 0.25rem;
        }

        .logo {
            width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
            max-height: 60px; /* Reduced from 80px */
            transform: scale(1.5); /* Besarkan 1.5x tanpa ubah layout*/
            transition: transform 0.3s ease;
        }

        /* Update Navigation Styles */
        .nav-container {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: auto;
            z-index: 1;
        }

        .nav-menu {
            display: flex;
            gap: 2rem;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
        }

        /* Make nav items consistent width */
        .nav-menu > a,
        .kategori-wrapper {
            width: 200px; /* Increased to match kategori button */
            text-align: center;
        }

        .nav-menu a,
        .kategori-btn {
            width: 200px; /* Increased to match all buttons */
            font-size: 1.1rem; /* Slightly larger font */
            padding: 0.5rem 1.2rem; /* Increased padding */
            height: 45px; /* Increased height */
            line-height: 27px; /* Adjusted line height */
            background: rgba(255, 255, 255, 0.95);
            color: #003B95;
            text-decoration: none;
            border: 2px solid rgba(0, 59, 149, 0.3);
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .nav-menu a:hover,
        .kategori-btn:hover {
            background: rgba(0, 59, 149, 0.95);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 59, 149, 0.3);
            border-color: rgba(255, 255, 255, 0.3);
        }

        /* Active page styling */
        .nav-menu a.active-page {
            background: linear-gradient(135deg, #003B95 0%, #0056b3 100%);
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 8px 25px rgba(0, 59, 149, 0.4),
                0 0 20px rgba(0, 59, 149, 0.3);
            transform: translateY(-2px);
        }

        .nav-menu a.active-page:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004080 100%);
            transform: translateY(-4px);
            box-shadow: 
                0 10px 30px rgba(0, 59, 149, 0.5),
                0 0 25px rgba(0, 59, 149, 0.4);
        }

        .kategori-btn {
            width: 200px; /* Increased from 150px */
            margin: 0; /* Remove any default margins */
        }

        .kategori-wrapper {
            display: flex;
            justify-content: center;
            width: 200px;
        }

        /* Settings Icon */
        .settings-icon {
            background: linear-gradient(135deg, #003B95 0%, #0056b3 100%);
            color: white;
            width: 50px;            /* Increased from 40px */
            height: 50px;           /* Increased from 40px */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-left: 1rem;
            transition: all 0.3s ease;
            font-size: 24px;        /* Added font-size to make the gear icon bigger */
            box-shadow: 0 4px 15px rgba(0, 59, 149, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .settings-icon:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004080 100%);
            transform: scale(1.15) rotate(90deg);   /* Added rotation effect */
            box-shadow: 0 6px 20px rgba(0, 59, 149, 0.4);
        }

        /* Main Content */
        .main-content {
            padding: 1rem;
            height: calc(100vh - 530px);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        /* Add decorative elements */
        .main-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: 
                radial-gradient(circle at 10% 20%, rgba(255, 228, 92, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(74, 144, 226, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(0, 59, 149, 0.02) 0%, transparent 60%);
            pointer-events: none;
            z-index: -1;
        }

        /* Decorative Elements */
        .hand-shape {
            position: absolute;
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg,rgb(220, 215, 54) 50%, transparent 50%);
            clip-path: polygon(0 0, 100% 0, 100% 30%, 60% 30%, 60% 50%, 40% 50%, 40% 30%, 0 30%);
            z-index: 1;
        }

        .top-left {
            top: 0;
            left: 0;
        }

        .bottom-right {
            bottom: 0;
            right: 0;
            transform: rotate(180deg);
        }

        .shape {
            position: absolute;
            background-color: rgba(255, 215, 0, 0.3);
            border-radius: 50%;
            z-index: 1;
        }

        .circle {
            width: 20px;
            height: 20px;
        }

        .big-circle {
            width: 50px;
            height: 50px;
        }

        .dot-group {
            position: absolute;
            top: 50%;
            left: 10%;
            display: grid;
            grid-template-columns: repeat(5, 5px);
            gap: 5px;
            z-index: 1;
        }

        .dot-group div {
            width: 5px;
            height: 5px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
        }

        /* Update banner for better contrast */
        .banner {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .banner h1 {
            color: #003B95;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }

        .banner p {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }

        /* Wave Design - Adjust colors to match new background */
        .wave-design {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 50%;
            background: linear-gradient(45deg, 
                rgba(0, 59, 149, 0.9) 0%, 
                rgba(0, 86, 179, 0.9) 100%
            );
            clip-path: polygon(0 100%, 100% 70%, 100% 100%, 0% 100%);
            z-index: 1;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-name {
            color: #003B95;
            font-weight: bold;
        }

        .settings-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: transparent;
            backdrop-filter: none;
            border-radius: 20px;
            box-shadow: none;
            display: none;
            z-index: 2000;
            min-width: 260px;
            margin-top: 18px;
            border: none;
            overflow: visible;
            padding: 12px 0;
        }

        .settings-dropdown::before {
            display: none;
        }

        .settings-dropdown.show {
            display: block;
            animation: settingsDropdownFadeIn 0.18s ease-out;
        }

        @keyframes settingsDropdownFadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .settings-dropdown a {
            display: flex;
            align-items: center;
            padding: 18px 28px;
            color: #003B95;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            border-bottom: none;
            margin: 6px 12px;
            border-radius: 14px;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            letter-spacing: 0.5px;
            position: relative;
            background: rgba(255, 255, 255, 0.95);
            overflow: hidden;
            border: 1px solid rgba(0, 59, 149, 0.2);
            backdrop-filter: blur(15px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .settings-dropdown a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg, 
                transparent, 
                rgba(255, 255, 255, 0.6), 
                transparent
            );
            transition: left 0.8s ease;
        }

        .settings-dropdown a::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 59, 149, 0.03) 0%, rgba(74, 144, 226, 0.03) 100%);
            border-radius: 14px;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .settings-dropdown a:hover {
            background: linear-gradient(135deg, #003B95 0%, #0056b3 20%, #4A90E2 100%);
            color: white;
            transform: translateX(5px) translateY(-2px) scale(1.03);
            box-shadow: 
                0 12px 35px rgba(0, 59, 149, 0.5),
                0 5px 15px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            margin: 6px 8px;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .settings-dropdown a:hover::before {
            left: 100%;
        }

        .settings-dropdown a:hover::after {
            opacity: 1;
        }

        .settings-dropdown a:last-child {
            margin-bottom: 0;
        }

        .settings-dropdown a:first-child {
            margin-top: 0;
        }

        .settings-wrapper {
            position: relative;
            z-index: 1500;
        }

        .slider-container {
            width: 90%;
            max-width: 900px;
            aspect-ratio: 1280 / 542;
            height: auto;
            max-height: 380px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            background: rgba(7, 27, 64, 0.18);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
        }

        .slider {
            display: flex;
            width: 300%;
            height: 100%;
            transition: transform 0.5s ease-in-out;
        }

        .slide {
            width: 33.333%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }

        .slide::after {
            display: none;
        }

        .slide-content {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: white;
            z-index: 1;
        }

        .slide-content h3 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .slide-content p {
            font-size: 1rem;
            opacity: 0.9;
        }

        .slider-dots {
            position: absolute;
            bottom: 10px;
            right: 20px;
            display: flex;
            gap: 8px;
            z-index: 2;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: background 0.3s;
        }

        .dot.active {
            background: white;
        }

        .kategori-wrapper {
            position: relative;
            display: inline-block;
            z-index: 1500;
        }

        .kategori-dropdown {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: transparent;
            backdrop-filter: none;
            border-radius: 20px;
            box-shadow: none;
            display: none;
            z-index: 2000;
            min-width: 200px;
            width: 200px;
            margin-top: 18px;
            border: none;
            overflow: visible;
            padding: 12px 0;
        }

        .kategori-dropdown::before {
            display: none;
        }

        .kategori-dropdown.show {
            display: block;
            animation: dropdownSlideIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes dropdownSlideIn {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px) scale(0.9);
                filter: blur(5px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0) scale(1);
                filter: blur(0px);
            }
        }

        .kategori-dropdown a {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px 20px;
            color: #003B95;
            text-decoration: none;
            font-size: 14px;
            font-weight: 800;
            border-bottom: none;
            margin: 6px 0;
            border-radius: 14px;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            letter-spacing: 1px;
            text-transform: uppercase;
            position: relative;
            background: rgba(255, 255, 255, 0.95);
            overflow: hidden;
            border: 1px solid rgba(0, 59, 149, 0.2);
            backdrop-filter: blur(15px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .kategori-dropdown a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg, 
                transparent, 
                rgba(255, 255, 255, 0.6), 
                transparent
            );
            transition: left 0.8s ease;
        }

        .kategori-dropdown a::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 59, 149, 0.03) 0%, rgba(74, 144, 226, 0.03) 100%);
            border-radius: 14px;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .kategori-dropdown a:hover {
            background: linear-gradient(135deg, #003B95 0%, #0056b3 20%, #4A90E2 100%);
            color: white;
            transform: translateY(-3px) scale(1.05);
            box-shadow: 
                0 12px 35px rgba(0, 59, 149, 0.5),
                0 5px 15px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            margin: 6px 0;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .kategori-dropdown a:hover::before {
            left: 100%;
        }

        .kategori-dropdown a:hover::after {
            opacity: 1;
        }

        .kategori-dropdown a:last-child {
            margin-bottom: 0;
        }

        .kategori-dropdown a:first-child {
            margin-top: 0;
        }

        .right-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-left: auto;  /* Push to right */
            flex-shrink: 0;
        }

        /* Add to Cart Button */
        .cart-icon {
            background: linear-gradient(135deg, #003B95 0%, #0056b3 100%);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 24px;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 59, 149, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .cart-icon:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004080 100%);
            transform: scale(1.15);
            box-shadow: 0 6px 20px rgba(0, 59, 149, 0.4);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: linear-gradient(135deg, #4A90E2 0%, #003B95 20%, rgba(255, 255, 255, 0.95) 40%, rgba(255, 255, 255, 0.95) 100%);
            margin: 0;
            padding: 30px 25px;
            border-radius: 20px;
            width: 90%;
            max-width: 800px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            border: 3px solid #4A90E2;
            backdrop-filter: blur(10px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .modal.show .modal-content {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }

        .modal-content h2 {
            margin-top: 0;
            text-align: center;
            color: white;
            font-size: 22px;
            margin-bottom: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #4A90E2 0%, #003B95 100%);
            padding: 15px 20px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(74, 144, 226, 0.4);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .contact-container {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: stretch;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: nowrap;
            padding: 0 10px;
        }

        .contact-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px 20px;
            width: 100%;
            flex: 1;
            text-align: center;
            border-radius: 15px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            border: 3px solid #4A90E2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            min-height: 200px;
            position: relative;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 35px rgba(74, 144, 226, 0.4);
            border-color: #4A90E2;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(74, 144, 226, 0.05) 100%);
        }

        .contact-card img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .contact-info {
            width: 100%;
            text-align: center;
            flex-grow: 1;
        }

        .contact-card h3 {
            font-size: 18px;
            font-weight: 700;
            color: #4A90E2;
            margin-bottom: 12px;
            margin-top: 8px;
            letter-spacing: 0.3px;
        }

        .contact-card p {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
            font-weight: 500;
            line-height: 1.4;
        }

        @media screen and (max-width: 600px) {
            .contact-container {
                flex-direction: column;
                align-items: center;
                gap: 15px;
                padding: 0 5px;
            }

            .contact-card {
                max-width: 100%;
                width: 100%;
                min-height: 160px;
                padding: 20px 15px;
            }

            .contact-card img {
                width: 50px;
                height: 50px;
            }

            .contact-card h3 {
                font-size: 16px;
            }

            .contact-card p {
                font-size: 13px;
            }

            .modal-content {
                width: 95%;
                max-width: 95%;
                padding: 25px 20px;
            }
        }

        .contact-button {
            display: inline-block;
            background: linear-gradient(135deg, #00C851 0%, #00A040 100%);
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
            letter-spacing: 0.3px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 200, 81, 0.3);
            width: 80%;
            text-align: center;
            margin-top: auto;
        }

        /* Update hover style for all buttons */
        .contact-button:hover {
            background: linear-gradient(135deg, #00A040 0%, #007030 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 200, 81, 0.4);
        }

        /* Add this to your existing CSS */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            cursor: pointer;
            background: #4A90E2;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 2px 8px rgba(74, 144, 226, 0.4);
        }

        .close-btn:hover {
            background: linear-gradient(135deg, #4A90E2 0%, #003B95 100%);
            transform: rotate(90deg) scale(1.1);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.6);
        }

        /* Additional Complaint Form Styles */
        .modal-content form input:focus,
        .modal-content form select:focus,
        .modal-content form textarea:focus {
            outline: none;
            border-color: #003B95;
            box-shadow: 0 0 0 3px rgba(0, 59, 149, 0.1);
        }

        .modal-content form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 59, 149, 0.3);
        }

        .modal-content form button[type="button"]:hover {
            background: #bbb !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .complaint-feedback {
            display: none;
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid transparent;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .complaint-feedback.show {
            display: block;
        }

        .complaint-feedback.success {
            background: #e8f7ec;
            color: #166534;
            border-color: #86efac;
        }

        .complaint-feedback.error {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #fca5a5;
        }

        .complaint-modal-content {
            max-width: 760px;
            max-height: 85vh;
            overflow-y: auto;
        }

        .complaint-layout {
            display: grid;
            gap: 22px;
        }

        .complaint-panel,
        .complaint-history-panel {
            background: rgba(255, 255, 255, 0.94);
            border-radius: 18px;
            border: 1px solid rgba(0, 59, 149, 0.12);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            padding: 22px;
        }

        .complaint-panel {
            margin-top: 8px;
        }

        .complaint-history-header {
            margin-bottom: 16px;
        }

        .complaint-history-header h3 {
            color: #003B95;
            font-size: 1.1rem;
            margin-bottom: 6px;
        }

        .complaint-history-header p {
            color: #5b6472;
            font-size: 0.94rem;
            line-height: 1.5;
        }

        .complaint-history-list {
            display: grid;
            gap: 14px;
        }

        .complaint-ticket-card {
            border: 1px solid rgba(0, 59, 149, 0.12);
            border-radius: 16px;
            background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
            padding: 16px;
        }

        .complaint-ticket-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .complaint-ticket-subject {
            color: #003B95;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 6px;
        }

        .complaint-ticket-date {
            color: #6b7280;
            font-size: 0.88rem;
        }

        .ticket-status-pill {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .ticket-status-pill.open {
            background: #fff7d6;
            color: #9a6700;
            border: 1px solid #f9d66c;
        }

        .ticket-status-pill.closed {
            background: #e8f7ec;
            color: #166534;
            border: 1px solid #86efac;
        }

        .ticket-message-block + .ticket-message-block {
            margin-top: 12px;
        }

        .ticket-message-label {
            display: block;
            color: #003B95;
            font-weight: 700;
            margin-bottom: 6px;
            font-size: 0.9rem;
        }

        .ticket-message-text {
            color: #334155;
            font-size: 0.93rem;
            line-height: 1.6;
            white-space: normal;
        }

        .ticket-message-block.reply .ticket-message-text {
            background: #eef6ff;
            border-left: 4px solid #1d4ed8;
            padding: 12px 14px;
            border-radius: 12px;
        }

        .ticket-message-block.pending .ticket-message-text {
            background: #fffaf0;
            border-left: 4px solid #f59e0b;
            padding: 12px 14px;
            border-radius: 12px;
        }

        .ticket-empty-state {
            text-align: center;
            color: #5b6472;
            padding: 18px;
            border: 1px dashed rgba(0, 59, 149, 0.2);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.72);
            line-height: 1.6;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 1024px) {
            .nav-container {
                position: static;
                transform: none;
                width: 100%;
                order: 3;
            }

            .nav-menu {
                width: 100%;
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .nav-menu > a,
            .kategori-wrapper,
            .nav-menu a,
            .kategori-btn {
                width: 100%;
                max-width: 240px;
            }

            .slider-container {
                width: min(94%, 900px);
            }
        }

        @media (max-width: 768px) {
            body {
                overflow-y: auto;
                height: auto;
                min-height: 100vh;
            }

            .header {
                padding: 1rem 0.9rem 1.1rem;
                gap: 1rem;
            }

            .top-nav {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: center;
                gap: 0.75rem;
                margin-bottom: 0;
            }

            .logo-container {
                width: min(140px, 42vw);
                padding: 0;
                align-self: center;
            }

            .logo {
                max-height: 40px;
            }

            .right-controls {
                position: static;
                margin-left: 0;
                gap: 0.55rem;
                justify-self: end;
            }

            .nav-container {
                grid-column: 1 / -1;
                margin-top: 0.35rem;
                width: 100%;
            }

            .nav-menu {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.55rem;
                width: 100%;
                align-items: stretch;
            }

            .nav-menu > a,
            .kategori-wrapper {
                width: 100%;
                max-width: none;
            }

            .nav-menu a,
            .kategori-btn {
                width: 100%;
                max-width: none;
                font-size: 0.88rem;
                min-height: 44px;
                height: auto;
                line-height: 1.2;
                padding: 0.7rem 0.55rem;
                margin: 0;
                border-radius: 14px;
                font-weight: 600;
                white-space: normal;
                text-align: center;
            }

            .kategori-btn {
                width: 100%;
                max-width: none;
            }

            .kategori-wrapper {
                width: 100%;
            }

            .settings-icon {
                width: 40px;
                height: 40px;
                font-size: 20px;
                margin-left: 0;
            }

            .cart-icon {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }

            .cart-count {
                width: 18px;
                height: 18px;
                font-size: 11px;
                top: -4px;
                right: -4px;
            }

            .settings-dropdown {
                right: 0;
                left: auto;
                min-width: min(76vw, 220px);
                max-width: min(76vw, 220px);
                margin-top: 10px;
            }

            .settings-dropdown a {
                padding: 12px 18px;
                font-size: 14px;
            }

            .slider-container {
                width: min(100%, 92vw);
                aspect-ratio: 1280 / 542;
                height: auto;
                max-height: none;
                margin: 8px auto 0;
                border-radius: 16px;
            }

            .slide {
                background-size: contain;
                background-position: center center;
                background-color: rgba(10, 28, 58, 0.18);
            }

            .slide-content {
                bottom: 15px;
                left: 15px;
            }

            .slide-content h3 {
                font-size: 1.1rem;
                margin-bottom: 4px;
            }

            .slide-content p {
                font-size: 0.85rem;
            }

            .slider-dots {
                bottom: 10px;
                right: 15px;
                gap: 6px;
            }

            .dot {
                width: 8px;
                height: 8px;
            }

            .main-content {
                padding: 0.75rem;
                height: auto;
                min-height: 150px;
                overflow: visible;
            }

            .kategori-dropdown {
                left: 50%;
                transform: translateX(-50%);
                min-width: min(88vw, 320px);
                width: min(88vw, 320px);
                max-width: min(88vw, 320px);
                margin-top: 10px;
            }

            .modal-content {
                width: 95%;
                margin: 5% auto;
                padding: 20px 15px;
            }

            .modal-content h2 {
                font-size: 20px;
                margin-bottom: 20px;
            }

            .contact-container {
                flex-direction: column;
                gap: 15px;
                margin-top: 20px;
            }

            .contact-card {
                width: 100%;
                max-width: none;
                padding: 15px;
            }

            .contact-card img {
                width: 60px;
                height: 60px;
                margin-bottom: 10px;
            }

            .complaint-panel,
            .complaint-history-panel {
                padding: 18px;
            }

            .complaint-ticket-card {
                padding: 14px;
            }

            .contact-button {
                width: 140px;
                height: 40px;
                line-height: 20px;
                font-size: 14px;
            }

            .close-btn {
                width: 30px;
                height: 30px;
                font-size: 20px;
                top: 8px;
                right: 8px;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 0.85rem 0.7rem 1rem;
            }

            .top-nav {
                grid-template-columns: 1fr auto;
                gap: 0.65rem 0.5rem;
                margin-bottom: 0;
            }

            .logo-container {
                width: min(112px, 36vw);
                align-self: center;
            }

            .logo {
                max-height: 35px;
            }

            .right-controls {
                position: static;
                margin-left: 0;
                gap: 0.5rem;
                justify-self: end;
            }

            .nav-menu {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.45rem;
            }

            .hubungi-link {
                grid-column: 1 / -1;
            }

            .nav-menu a,
            .kategori-btn {
                font-size: 0.82rem;
                min-height: 40px;
                line-height: 1.15;
                max-width: none;
                border-radius: 12px;
                padding: 0.65rem 0.45rem;
            }

            .settings-icon {
                width: 36px;
                height: 36px;
                font-size: 18px;
            }

            .cart-icon {
                width: 36px;
                height: 36px;
                font-size: 18px;
            }

            .cart-count {
                width: 16px;
                height: 16px;
                font-size: 10px;
                top: -3px;
                right: -3px;
            }

            .settings-dropdown {
                min-width: min(82vw, 210px);
                max-width: min(82vw, 210px);
            }

            .settings-dropdown a {
                padding: 10px 15px;
                font-size: 13px;
            }

            .slider-container {
                width: min(100%, 94vw);
                aspect-ratio: 1280 / 542;
                height: auto;
                margin: 10px auto 0;
                border-radius: 12px;
            }

            .slide-content {
                bottom: 12px;
                left: 12px;
            }

            .slide-content h3 {
                font-size: 1rem;
                margin-bottom: 3px;
            }

            .slide-content p {
                font-size: 0.75rem;
            }

            .slider-dots {
                bottom: 8px;
                right: 12px;
                gap: 4px;
            }

            .dot {
                width: 6px;
                height: 6px;
            }

            .main-content {
                padding: 0.5rem;
                min-height: 120px;
            }

            .kategori-dropdown {
                min-width: min(88vw, 280px);
                width: min(88vw, 280px);
                max-width: min(88vw, 280px);
            }

            .modal-content {
                width: 98%;
                margin: 2% auto;
                padding: 15px 12px;
            }

            .modal-content h2 {
                font-size: 18px;
                margin-bottom: 15px;
            }

            .contact-container {
                gap: 12px;
                margin-top: 15px;
            }

            .contact-card {
                padding: 12px;
            }

            .contact-card img {
                width: 50px;
                height: 50px;
                margin-bottom: 8px;
            }

            .contact-card h4 {
                font-size: 14px;
                margin-bottom: 5px;
            }

            .contact-card p {
                font-size: 12px;
            }

            .contact-button {
                width: 120px;
                height: 36px;
                line-height: 16px;
                font-size: 12px;
                padding: 8px 15px;
            }

            .close-btn {
                width: 28px;
                height: 28px;
                font-size: 18px;
                top: 6px;
                right: 6px;
            }
        }

        /* AI Chatbot Character Styles */
        .ai-chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999; /* Increased z-index */
            animation: chatbotEnter 1s ease-out;
        }

        @keyframes chatbotEnter {
            0% { 
                opacity: 0; 
                transform: translateY(100px) scale(0.5) rotate(45deg);
            }
            50% { 
                opacity: 0.8; 
                transform: translateY(-10px) scale(1.1) rotate(-5deg);
            }
            100% { 
                opacity: 1; 
                transform: translateY(0) scale(1) rotate(0deg);
            }
        }

        .chatbot-character {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border-radius: 50%;
            border: 3px solid #ffffff;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }

        .chatbot-character:hover {
            transform: scale(1.1);
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.6);
        }

        /* Chat indicator styles */
        .chat-indicator {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 11px;
            font-weight: bold;
            display: none;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Chat Avatar Styles */
        .ai-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border-radius: 50%;
            border: 2px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            color: white;
            font-size: 18px;
        }

        .hand-left {
            top: 45%;
            left: -8px; /* Just slightly outside body edge */
            animation-delay: 0s;
        }

        .hand-right {
            top: 45%;
            right: -8px; /* Just slightly outside body edge */
            animation-delay: 1s;
        }

        @keyframes handWaveInside {
            0%, 50%, 100% { 
                transform: rotate(0deg) translateY(0px) scale(1);
                opacity: 0.8;
            }
            25% { 
                transform: rotate(15deg) translateY(-3px) scale(1.1);
                opacity: 1;
            }
            75% { 
                transform: rotate(-15deg) translateY(-2px) scale(1.05);
                opacity: 0.9;
            }
        }

        /* Enhanced hover animation for hands - back to simple */
        .chatbot-character:hover .character-hand {
            animation: handWaveExcited 0.3s ease infinite;
            box-shadow: 0 4px 15px rgba(255, 228, 92, 0.6);
        }

        @keyframes handWaveExcited {
            0%, 100% { 
                transform: rotate(0deg) translateY(0px) scale(1);
            }
            25% { 
                transform: rotate(25deg) translateY(-5px) scale(1.2);
            }
            50% { 
                transform: rotate(-25deg) translateY(-8px) scale(1.3);
            }
            75% { 
                transform: rotate(20deg) translateY(-3px) scale(1.1);
            }
        }

        /* Eyes - Comel Version */
        .character-eyes {
            display: flex;
            gap: 10px;
            margin-bottom: 8px;
        }

        .eye {
            width: 16px;
            height: 16px;
            background: #333;
            border-radius: 50%;
            position: relative;
            animation: eyeBlink 4s ease-in-out infinite;
            border: 2px solid #666;
        }

        @keyframes eyeBlink {
            0%, 85%, 100% { height: 16px; }
            90% { height: 3px; }
        }

        /* Mata sparkle yang comel */
        .eye::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 4px;
            width: 6px;
            height: 6px;
            background: white;
            border-radius: 50%;
            box-shadow: 2px 2px 0px rgba(255, 255, 255, 0.8);
        }

        /* Chat indicator styles */
        .chat-indicator {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 11px;
            font-weight: bold;
            display: none;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Chat Modal */
        .chatbot-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000; /* Increased z-index */
            backdrop-filter: blur(5px);
        }

        .chatbot-modal-content {
            position: absolute;
            bottom: 120px;
            right: 30px;
            width: 360px;
            max-height: 520px; /* Reduced since no input area */
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 3px solid #FFE45C;
            overflow: hidden;
            animation: chatAppear 0.3s ease-out;
            z-index: 10002;
        }

        /* Chat Body */
        .chat-body {
            height: 300px; /* Increased since no input area */
            overflow-y: auto;
            padding: 15px 20px;
            background: #fafafa;
            scroll-behavior: smooth;
        }

        /* Chat Header */
        .chat-header {
            background: linear-gradient(135deg, #FFE45C 0%, #4A90E2 100%);
            padding: 12px 18px; /* Reduced from 15px 20px */
            display: flex;
            align-items: center;
            gap: 12px; /* Reduced from 15px */
            border-bottom: 2px solid #003B95;
        }

        .chat-avatar {
            width: 35px; /* Reduced from 40px */
            height: 35px; /* Reduced from 40px */
            background: white;
            border-radius: 50%;
            border: 2px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px; /* Reduced from 20px */
        }

        .chat-title h3 {
            margin: 0;
            color: #003B95;
            font-size: 16px; /* Reduced from 18px */
            font-weight: bold;
        }

        .chat-title p {
            margin: 0;
            color: #666;
            font-size: 11px; /* Reduced from 12px */
        }

        /* Message avatar styles */
        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 16px;
        }

        .user-avatar {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }

        .bot-avatar {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        /* Quick Questions */
        .quick-questions {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            max-height: 140px;
            overflow-y: auto;
        }

        .quick-questions h4 {
            margin: 0 0 12px 0;
            color: #003B95;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
        }

        .question-btn {
            display: block;
            width: 100%;
            padding: 10px 15px;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #e9ecef;
            border-radius: 15px;
            text-align: left;
            font-size: 12px;
            color: #555;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            font-weight: 500;
        }

        .question-btn:hover {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            border-color: #007bff;
        }

        .question-btn:active {
            transform: translateY(0px);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
        }

        /* Ensure last question button has proper spacing */
        .question-btn:last-child {
            margin-bottom: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .chatbot-modal-content {
                width: 90%;
                right: 5%;
                bottom: 20px;
                max-height: 70vh; /* Adjusted for removed input */
            }
            
            .chat-body {
                height: calc(70vh - 200px); /* Dynamic height for mobile */
                padding: 12px 15px;
            }
            
            .chatbot-character {
                width: 60px;
                height: 60px;
            }
        }

        /* Typing indicator */
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            opacity: 0.7;
        }

        .typing-dot {
            width: 6px;
            height: 6px;
            background: #666;
            border-radius: 50%;
            animation: typingDot 1.4s infinite;
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typingDot {
            0%, 60%, 100% { opacity: 0.3; }
            30% { opacity: 1; }
        }

        /* Beautiful Close Button Styling */
        .chat-close {
            position: absolute;
            top: 10px;
            right: 15px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
            z-index: 1000;
        }

        .chat-close:hover {
            background: linear-gradient(135deg, #ff5252, #d63031);
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.5);
        }

        .chat-close:active {
            transform: scale(0.95) rotate(90deg);
            box-shadow: 0 2px 5px rgba(255, 107, 107, 0.4);
        }

        .chat-close:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.3);
        }

        /* Animation for close button entrance */
        @keyframes closeButtonEntrance {
            0% {
                opacity: 0;
                transform: scale(0) rotate(-180deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }

        .chat-close {
            animation: closeButtonEntrance 0.5s ease-out;
        }

        /* Enhanced message content styling */
        .message-content {
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 15px;
            max-width: 80%;
            word-wrap: break-word;
            line-height: 1.5;
            font-size: 14px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .user-message .message-content {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }

        .bot-message .message-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            color: #333;
            border: 1px solid #e9ecef;
        }

        /* Add some spacing and animations */
        .chat-message {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            align-items: flex-start;
            animation: messageSlideIn 0.3s ease-out;
        }

        @keyframes messageSlideIn {
            0% {
                opacity: 0;
                transform: translateY(10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="top-nav">
            <div class="logo-container">
                <img src="logo/breyer.gombak.png" alt="Breyer Logo" class="logo">
            </div>
            
            <div class="nav-container">
                <nav class="nav-menu">
                    <a href="#home" class="active-page">HOME</a>
                    <div class="kategori-wrapper">
                        <a href="#" class="kategori-btn">KATEGORI</a>
                        <div class="kategori-dropdown">
                            <a href="cs.php">CS</a>
                            <a href="am.php">AM</a>
                            <a href="culinary.php">CULINARY</a>
                            <a href="electrical.php">ELECTRICAL</a>
                            <a href="fnb.php">LAIN-LAIN</a>
                        </div>
                    </div>
                    <a href="#hubungi" class="hubungi-link">HUBUNGI</a>
                </nav>
            </div>
            
            <div class="right-controls">
                <div class="cart-icon" title="Troli Belanja">
                    🛒
                    <span class="cart-count">0</span>
                </div>
                <div class="settings-wrapper">
                    <div class="settings-icon" title="Tetapan">⚙️</div>
                    <div class="settings-dropdown">
                        <a href="student_profile.php" onclick="viewProfile(event)">Profile</a>
                        <a href="payment_history.php" onclick="viewPaymentHistory(event)">Sejarah Pembayaran</a>
                        <a href="#" onclick="openComplaintForm(event)">Aduan &amp; Balasan</a>
                        <a href="change_password.php" onclick="changePassword(event)">Tukar Kata Laluan</a>
                        <a href="logout.php" onclick="logout(event)">Log Keluar</a>
                    </div>
                </div>
            </div>
        </div>

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
                <div class="slide" style="background-image: url('banner3/banner-guarentee.png');">
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
    </header>

    <main class="main-content">
        <!-- Banner and decorative elements removed -->
    </main>

    <div id="hubungiModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeBtn">&times;</span>
            <h2>HUBUNGI KAMI</h2>
            <div class="contact-container">
                <div class="contact-card">
                    <img src="ads7/breyer-banner8.png" alt="Waktu Operasi">
                    <div class="contact-info">
                        <h3>Waktu Operasi</h3>
                        <p>8:30 PAGI – 5:30 PETANG</p>
                    </div>
                </div>
                <div class="contact-card">
                    <img src="ads5/breyer-banner5.png" alt="Telefon">
                    <div class="contact-info">
                        <h3>Telefon</h3>
                        <p>Hubungi kami melalui WhatsApp</p>
                    </div>
                    <a href="https://wa.me/60102509941" class="contact-button" target="_blank">
                        WhatsApp
                    </a>
                </div>
                <div class="contact-card">
                    <img src="ads6/breyer-banner6.png" alt="Emel">
                    <div class="contact-info">
                        <h3>Emel</h3>
                        <p>Hantar mesej kepada kami</p>
                    </div>
                    <a href="mailto:Cashier.sg@cqbreyer.edu.my" class="contact-button" target="_blank">
                        Hantar Emel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Aduan -->
    <div id="complaintModal" class="modal">
        <div class="modal-content complaint-modal-content">
            <span class="close-btn" onclick="closeComplaintModal()">&times;</span>
            <h2>📝 ADUAN &amp; BALASAN ADMIN</h2>
            <div class="complaint-layout">
                <div class="complaint-panel">
                    <form id="complaintForm" method="post" action="dashboard.php" style="margin-top: 0;">
                        <input type="hidden" name="submit_complaint" value="1">
                        <div id="complaintFeedback" class="complaint-feedback<?php echo $complaintFlash ? ' show ' . htmlspecialchars($complaintFlash['type']) : ''; ?>">
                            <?php if ($complaintFlash): ?>
                                <?php echo htmlspecialchars($complaintFlash['message']); ?>
                            <?php endif; ?>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: bold; color: #003B95; margin-bottom: 8px;">Jenis Aduan:</label>
                            <select id="complaintType" name="complaint_type" required style="width: 100%; padding: 12px; border: 2px solid #90caf9; border-radius: 8px; font-size: 14px; background: white;">
                                <option value="">Pilih jenis aduan</option>
                                <option value="pembayaran" <?php echo ($complaintOld['complaint_type'] ?? '') === 'pembayaran' ? 'selected' : ''; ?>>Masalah Pembayaran</option>
                                <option value="produk" <?php echo ($complaintOld['complaint_type'] ?? '') === 'produk' ? 'selected' : ''; ?>>Masalah Produk</option>
                                <option value="perkhidmatan" <?php echo ($complaintOld['complaint_type'] ?? '') === 'perkhidmatan' ? 'selected' : ''; ?>>Masalah Perkhidmatan</option>
                                <option value="sistem" <?php echo ($complaintOld['complaint_type'] ?? '') === 'sistem' ? 'selected' : ''; ?>>Masalah Sistem/Website</option>
                                <option value="lain-lain" <?php echo ($complaintOld['complaint_type'] ?? '') === 'lain-lain' ? 'selected' : ''; ?>>Lain-lain</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: bold; color: #003B95; margin-bottom: 8px;">Tajuk Aduan:</label>
                            <input type="text" id="complaintTitle" name="complaint_title" required style="width: 100%; padding: 12px; border: 2px solid #90caf9; border-radius: 8px; font-size: 14px;" placeholder="Masukkan tajuk aduan" value="<?php echo htmlspecialchars($complaintOld['complaint_title'] ?? ''); ?>">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: bold; color: #003B95; margin-bottom: 8px;">Butiran Aduan:</label>
                            <textarea id="complaintDetails" name="complaint_details" required rows="5" style="width: 100%; padding: 12px; border: 2px solid #90caf9; border-radius: 8px; font-size: 14px; resize: vertical;" placeholder="Terangkan butiran aduan anda dengan jelas"><?php echo htmlspecialchars($complaintOld['complaint_details'] ?? ''); ?></textarea>
                        </div>

                        <div style="text-align: center; margin-top: 25px;">
                            <button type="button" onclick="closeComplaintModal()" style="background: #ccc; color: #666; padding: 12px 25px; border: none; border-radius: 8px; font-weight: bold; margin-right: 10px; cursor: pointer;">
                                Batal
                            </button>
                            <button type="submit" style="background: linear-gradient(135deg, #003B95 0%, #0056b3 100%); color: white; padding: 12px 25px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                                Hantar Aduan
                            </button>
                        </div>
                    </form>
                </div>

                <div class="complaint-history-panel">
                    <div class="complaint-history-header">
                        <h3>Rekod Aduan Anda</h3>
                        <p>Student boleh lihat status tiket dan mesej balas admin di bahagian ini sebaik sahaja admin menghantar balasan.</p>
                    </div>

                    <?php if (!empty($complaintTickets)): ?>
                        <div class="complaint-history-list">
                            <?php foreach ($complaintTickets as $ticket): ?>
                                <?php
                                $ticketStatus = ($ticket['status'] ?? '') === 'closed' ? 'closed' : 'open';
                                $ticketStatusLabel = $ticketStatus === 'closed' ? 'Sudah Dibalas' : 'Menunggu Balasan';
                                $ticketDate = !empty($ticket['created_at']) ? date('d/m/Y h:i A', strtotime($ticket['created_at'])) : '-';
                                ?>
                                <div class="complaint-ticket-card">
                                    <div class="complaint-ticket-top">
                                        <div>
                                            <div class="complaint-ticket-subject"><?php echo htmlspecialchars($ticket['subject'] ?? 'Tanpa tajuk'); ?></div>
                                            <div class="complaint-ticket-date">Dihantar pada <?php echo htmlspecialchars($ticketDate); ?></div>
                                        </div>
                                        <span class="ticket-status-pill <?php echo $ticketStatus; ?>"><?php echo htmlspecialchars($ticketStatusLabel); ?></span>
                                    </div>

                                    <div class="ticket-message-block">
                                        <span class="ticket-message-label">Aduan anda</span>
                                        <div class="ticket-message-text"><?php echo nl2br(htmlspecialchars($ticket['message'] ?? '')); ?></div>
                                    </div>

                                    <?php if (!empty($ticket['admin_reply'])): ?>
                                        <div class="ticket-message-block reply">
                                            <span class="ticket-message-label">Balasan admin</span>
                                            <div class="ticket-message-text"><?php echo nl2br(htmlspecialchars($ticket['admin_reply'])); ?></div>
                                        </div>
                                    <?php else: ?>
                                        <div class="ticket-message-block pending">
                                            <span class="ticket-message-label">Status semasa</span>
                                            <div class="ticket-message-text">Admin belum menghantar balasan untuk tiket ini.</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="ticket-empty-state">
                            Belum ada aduan direkodkan untuk akaun ini. Hantar aduan baharu menggunakan borang di atas dan balasan admin akan muncul di sini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Chatbot -->
    <div class="ai-chatbot-container">
        <div class="chatbot-character" onclick="openChatbot()">
            💬
            <div class="chat-indicator" id="chatIndicator">!</div>
        </div>
    </div>

    <!-- Chatbot Modal -->
    <div id="chatbotModal" class="chatbot-modal">
        <div class="chatbot-modal-content">
            <div class="chat-header">
                <div class="chat-avatar ai-avatar">
                    🤖
                </div>
                <div class="chat-title">
                    <h3>💬 Breyer Assistant</h3>
                    <p>✨ Saya sedia membantu anda 24/7! ✨</p>
                </div>
                <button class="chat-close" onclick="closeChatbot()">&times;</button>
            </div>
            <div class="chat-body" id="chatBody">
                <div class="chat-message bot-message">
                    <div class="message-avatar bot-avatar">
                        🤖
                    </div>
                    <div class="message-content">
                        🎉 Hai! Selamat datang ke dunia pembelajaran Breyer Marketplace! 
                        <br><br>🤝 Saya adalah pembantu pintar anda dan saya SANGAT excited untuk membantu! 
                        <br><br>💡 Apa yang boleh saya bantu hari ini? ✨
                    </div>
                </div>
            </div>
            <div class="quick-questions">
                <h4>🚀 Soalan Popular:</h4>
                <button class="question-btn" onclick="askQuestion('🛒 Macam mana nak beli produk?')">🛒 Macam mana nak beli produk?</button>
                <button class="question-btn" onclick="askQuestion('💳 Kaedah pembayaran apa yang ada?')">💳 Kaedah pembayaran apa yang ada?</button>
                <button class="question-btn" onclick="askQuestion('📞 Nak hubungi support macam mana?')">📞 Nak hubungi support macam mana?</button>
                <button class="question-btn" onclick="askQuestion('📚 Course apa yang boleh pilih?')">📚 Course apa yang boleh pilih?</button>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('.settings-icon').addEventListener('click', (e) => {
            e.stopPropagation();
            const dropdown = document.querySelector('.settings-dropdown');
            dropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const settingsDropdown = document.querySelector('.settings-dropdown');
            const kategoriDropdown = document.querySelector('.kategori-dropdown');
            
            if (!e.target.closest('.settings-wrapper')) {
                settingsDropdown.classList.remove('show');
            }
            if (!e.target.closest('.kategori-wrapper')) {
                kategoriDropdown.classList.remove('show');
            }
        });

        // Auto Slider
        const slider = document.querySelector('.slider');
        const dots = document.querySelectorAll('.dot');
        let currentSlide = 0;

        function nextSlide() {
            currentSlide = (currentSlide + 1) % 3;
            updateSlider();
        }

        function updateSlider() {
            slider.style.transform = 'translateX(-' + (currentSlide * 33.333) + '%)';
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }

        // Manual navigation with dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                updateSlider();
            });
        });

        // Auto slide every 5 seconds
        setInterval(nextSlide, 5000);

        // Add this before the slider code
        const kategoriBtn = document.querySelector('.kategori-btn');
        const kategoriDropdown = document.querySelector('.kategori-dropdown');

        kategoriBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            kategoriDropdown.classList.toggle('show');
            
            // Remove active-page class from all navigation items
            document.querySelectorAll('.nav-menu a').forEach(link => {
                link.classList.remove('active-page');
            });
            // Set KATEGORI button as active when dropdown is opened
            kategoriBtn.classList.add('active-page');
        });

        // Close dropdown when clicking outside and return HOME to active
        document.addEventListener('click', (e) => {
            if (!kategoriBtn.contains(e.target) && !kategoriDropdown.contains(e.target)) {
                kategoriDropdown.classList.remove('show');
                kategoriBtn.classList.remove('active-page');
                document.querySelector('a[href="#home"]').classList.add('active-page');
            }
        });

        // HUBUNGI button functionality - same as KATEGORI
        document.querySelector('.hubungi-link').addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Remove active-page class from all navigation items
            document.querySelectorAll('.nav-menu a').forEach(link => {
                link.classList.remove('active-page');
            });
            // Set HUBUNGI button as active when modal is opened
            e.target.classList.add('active-page');
            
            const modal = document.getElementById('hubungiModal');
            modal.style.display = "block";
            setTimeout(() => modal.classList.add('show'), 10);
        });

        document.getElementById('closeBtn').addEventListener('click', () => {
            const modal = document.getElementById('hubungiModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = "none", 300);
            
            // Return HOME button to active state when modal closes
            document.querySelector('.hubungi-link').classList.remove('active-page');
            document.querySelector('a[href="#home"]').classList.add('active-page');
        });

        window.addEventListener('click', (e) => {
            const modal = document.getElementById('hubungiModal');
            if (e.target === modal) {
                modal.classList.remove('show');
                setTimeout(() => modal.style.display = "none", 300);
                
                // Return HOME button to active state when modal closes
                document.querySelector('.hubungi-link').classList.remove('active-page');
                document.querySelector('a[href="#home"]').classList.add('active-page');
            }
        });

        // Complaint Modal Functions
        function openComplaintForm(e) {
            e.preventDefault();
            // Close settings dropdown first
            document.querySelector('.settings-dropdown').classList.remove('show');
            
            const modal = document.getElementById('complaintModal');
            modal.style.display = "block";
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function closeComplaintModal() {
            const modal = document.getElementById('complaintModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = "none", 300);
        }

        function setComplaintFeedback(type, message) {
            const feedback = document.getElementById('complaintFeedback');
            if (!feedback) {
                return;
            }

            feedback.textContent = message;
            feedback.className = 'complaint-feedback show ' + type;
        }

        // Handle complaint form submission
        document.getElementById('complaintForm').addEventListener('submit', function(e) {
            const type = document.getElementById('complaintType').value.trim();
            const title = document.getElementById('complaintTitle').value.trim();
            const details = document.getElementById('complaintDetails').value.trim();
            const submitButton = this.querySelector('button[type="submit"]');

            if (!type || !title || !details) {
                e.preventDefault();
                setComplaintFeedback('error', 'Sila lengkapkan semua maklumat yang diperlukan.');
                return;
            }

            setComplaintFeedback('success', 'Aduan sedang dihantar. Sila tunggu sebentar...');

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Menghantar...';
            }
        });

        window.addEventListener('load', function() {
            const feedback = document.getElementById('complaintFeedback');
            if (!feedback || !feedback.classList.contains('show')) {
                return;
            }

            const modal = document.getElementById('complaintModal');
            modal.style.display = 'block';
            setTimeout(() => modal.classList.add('show'), 10);
        });

        // Close complaint modal when clicking outside
        window.addEventListener('click', (e) => {
            const modal = document.getElementById('complaintModal');
            if (e.target === modal) {
                closeComplaintModal();
            }
        });

        // Add this to your existing script section
        function viewProfile(e) {
            e.preventDefault();
            window.location.href = 'student_profile.php';
        }

        function viewPaymentHistory(e) {
            e.preventDefault();
            window.location.href = 'payment_history.php';
        }

        function changePassword(e) {
            e.preventDefault();
            window.location.href = 'change_password.php';
        }

        function logout(e) {
            e.preventDefault();
            if(confirm('Adakah anda pasti untuk log keluar?')) {
                window.location.href = 'logout.php';
            }
        }

        // Cart functionality
        let cartItems = JSON.parse(localStorage.getItem('cartItems') || '[]');
        
        function updateCartCount() {
            const cartCount = document.querySelector('.cart-count');
            const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
            cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
        }

        // Cart click handler
        document.querySelector('.cart-icon').addEventListener('click', () => {
            window.location.href = 'cart.php';
        });

        // Initialize cart count on page load
        updateCartCount();

        // Chatbot functionality
        function openChatbot() {
            document.getElementById('chatbotModal').style.display = 'block';
            // Scroll to bottom when chat is opened
            setTimeout(() => {
                const chatBody = document.getElementById('chatBody');
                chatBody.scrollTop = chatBody.scrollHeight;
            }, 100);
        }

        function closeChatbot() {
            document.getElementById('chatbotModal').style.display = 'none';
        }

        function askQuestion(question) {
            const chatBody = document.getElementById('chatBody');
            
            // Add user message directly
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message user-message';
            messageDiv.innerHTML =
                '<div class="message-avatar user-avatar">' +
                    '👤' +
                '</div>' +
                '<div class="message-content">' + question + '</div>';
            chatBody.appendChild(messageDiv);
            
            // Add bot response
            setTimeout(() => {
                const botResponse = getBotResponse(question);
                addMessage(botResponse, false);
                // Scroll to bottom
                setTimeout(() => {
                    chatBody.scrollTop = chatBody.scrollHeight;
                }, 100);
            }, 1000);
            
            // Scroll to bottom after question
            setTimeout(() => {
                chatBody.scrollTop = chatBody.scrollHeight;
            }, 100);
        }

        function addMessage(message, isUser) {
            const chatBody = document.getElementById('chatBody');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message ' + (isUser ? 'user-message' : 'bot-message');
            
            if (isUser) {
                messageDiv.innerHTML =
                    '<div class="message-avatar user-avatar">' +
                        '👤' +
                    '</div>' +
                    '<div class="message-content">' + message + '</div>';
            } else {
                messageDiv.innerHTML =
                    '<div class="message-avatar bot-avatar">' +
                        '🤖' +
                    '</div>' +
                    '<div class="message-content">' + message + '</div>';
            }
            
            chatBody.appendChild(messageDiv);
            
            // Smooth scroll to bottom with a small delay to ensure content is rendered
            setTimeout(() => {
                chatBody.scrollTop = chatBody.scrollHeight;
            }, 50);
        }

        function getBotResponse(message) {
            const lowerMessage = message.toLowerCase();
            
            if (lowerMessage.includes('beli') || lowerMessage.includes('produk')) {
                return '🛒 BESTNYA! Nak beli produk ye? <br><br>📍 Caranya MUDAH gila:<br>1️⃣ Klik menu "KATEGORI" kat atas tu<br>2️⃣ Pilih course yang awak minat<br>3️⃣ Klik "ADD TO CART" ⬅️ SIMPLE!<br><br>🎯 Lepas tu checkout je! Senang kan? 💯';
            } else if (lowerMessage.includes('bayar') || lowerMessage.includes('payment')) {
                return '💳 WOW! Payment kat sini SUPER convenient! <br><br>🏦 Boleh bayar guna:<br>✅ Online Banking (Maybank, CIMB, Public Bank)<br>✅ FPX - All Malaysian banks<br>✅ Credit/Debit Card<br><br>🔒 100% SECURE & FAST process! Trust me! 💪';
            } else if (lowerMessage.includes('hubungi') || lowerMessage.includes('contact')) {
                return '📞 NAK contact kiteorang? BOLEH je! <br><br>🔥 Cara PANTAS:<br>📱 WhatsApp: 010-250-9941 (REPLY CEPAT!)<br>📧 Email: Cashier.sg@cqbreyer.edu.my<br><br>⏰ Waktu Operation:<br>🌅 8:30 PAGI - 5:30 PETANG<br><br>💯 Confirm kiteorang reply ASAP!';
            } else if (lowerMessage.includes('kategori') || lowerMessage.includes('jenis') || lowerMessage.includes('course')) {
                return '📚 OMG! Course kiteorang POWER habis! <br><br>🔥 Check out kategori HOT ni:<br>💻 CS (Computer System) - Tech lovers!<br>📋 AM (Admin Management) - Business minded!<br>👨‍🍳 CULINARY - Food passionate!<br>⚡ ELECTRICAL - Future engineers!<br>🎯 LAIN-LAIN - Special courses!<br><br>✨ Semua course ada future bright! 🌟';
            } else if (lowerMessage.includes('selamat') || lowerMessage.includes('hello') || lowerMessage.includes('hi')) {
                return '🎉 HOYEAH! Selamat datang ke Breyer family! <br><br>🤗 Saya SUPER happy dapat jumpa awak! Nak tanya apa-apa ke? <br><br>💡 Pro tip: Try klik quick questions kat bawah tu untuk shortcut! 🚀';
            } else if (lowerMessage.includes('terima kasih') || lowerMessage.includes('thanks')) {
                return '🥰 Awww, sama-sama! <br><br>🌟 PLEASURE bantu awak! Kalau ada apa-apa lagi, jangan segan-segan tanya ye! <br><br>💪 Kiteorang always ready to help! 24/7 support! ✨';
            } else {
                return '🤔 Hmm, interesting question! <br><br>💭 Saya try faham maksud awak... Tapi maybe boleh elaborate sikit? <br><br>🎯 ATAU try tanya pasal:<br>🛒 Cara beli produk<br>💳 Payment methods<br>📞 Contact details<br>📚 Course categories<br><br>🚀 Saya ready nak help!';
            }
        }

        function handleChatEnter(event) {
            // Input removed - no longer needed
        }

        // Close chatbot when clicking outside
        window.onclick = function(event) {
            const chatbotModal = document.getElementById('chatbotModal');
            if (event.target === chatbotModal) {
                closeChatbot();
            }
        }

        // Simple notification system
        function showChatNotification() {
            const indicator = document.getElementById('chatIndicator');
            if (indicator) {
                indicator.style.display = 'flex';
                
                // Hide after 10 seconds
                setTimeout(() => {
                    indicator.style.display = 'none';
                }, 10000);
            }
        }

        // Show notification after 30 seconds
        setTimeout(showChatNotification, 30000);
    </script>
</body>
</html>
