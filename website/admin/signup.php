<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $ic_number = preg_replace('/[^0-9]/', '', $_POST['ic_number']); // Remove non-numeric characters
    $password = $_POST['password'];

    // Validate IC number format (12 digits)
    if (strlen($ic_number) !== 12) {
        echo json_encode(['success' => false, 'message' => 'IC number must be 12 digits']);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT admin_id FROM admin WHERE ic_number = ?");
        $stmt->execute([$ic_number]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'IC number already registered']);
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin (full_name, ic_number, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$name, $ic_number, $passwordHash]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Signup failed']);
    }
    exit;
}
?>
