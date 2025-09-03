<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $customer_name = $_POST['customer_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $quantity = $_POST['quantity'];
    
    // Here you would typically:
    // 1. Validate the data
    // 2. Save to database
    // 3. Send confirmation email
    
    // For now, just redirect back with success message
    $_SESSION['message'] = 'Pesanan anda telah berjaya dihantar!';
    header('Location: cs.php');
    exit;
} else {
    header('Location: cs.php');
    exit;
}
?>
