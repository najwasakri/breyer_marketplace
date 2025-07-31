<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ic_number = preg_replace('/[^0-9]/', '', $_POST['ic_number']); // Remove non-numeric characters
    $password = $_POST['password'];

    // Validate IC number format (12 digits)
    if (strlen($ic_number) !== 12) {
        echo json_encode(['success' => false, 'message' => 'IC number must be 12 digits']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT admin_id, full_name, password_hash FROM admin WHERE ic_number = ? AND status = 'active'");
        $stmt->execute([$ic_number]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['full_name'] = $admin['full_name'];

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No Kad Pengenalan atau kata laluan tidak sah']);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Log masuk gagal']);
    }
    exit;
}
?>

