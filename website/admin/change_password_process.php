<?php
// filepath: c:\xampp\htdocs\breyer_marketplace\website\admin\change_password_process.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = $_SESSION['admin_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['password_error'] = "Semua field mesti diisi.";
        header('Location: dashboard.php');
        exit;
    }
    
    // Check kata laluan baru sama dengan confirm
    if ($new_password !== $confirm_password) {
        $_SESSION['password_error'] = "Kata laluan baru tidak sepadan.";
        header('Location: dashboard.php');
        exit;
    }
    
    // Check minimum length
    if (strlen($new_password) < 6) {
        $_SESSION['password_error'] = "Kata laluan baru minimum 6 aksara.";
        header('Location: dashboard.php');
        exit;
    }
    
    // Verify kata laluan lama
    $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close(); // Tambah ini untuk tutup statement

    if (!$admin || !password_verify($old_password, $admin['password'])) {
        $_SESSION['password_error'] = "Kata laluan lama tidak betul.";
        header('Location: dashboard.php');
        exit;
    }
    
    // Hash kata laluan baru
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update kata laluan dalam database
    $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $admin_id);

    if ($stmt->execute()) {
        $_SESSION['password_success'] = "Kata laluan berjaya ditukar.";
    } else {
        $_SESSION['password_error'] = "Ralat semasa menukar kata laluan.";
    }

    $stmt->close();
    $conn->close();
    
    header('Location: dashboard.php');
    exit;
}
?>