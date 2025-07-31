<?php
$host = 'localhost';
$db   = 'breyer_marketplace';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Untuk tangkap error dengan try-catch
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Biar $stmt->fetch() bagi associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Elak SQL injection lebih baik
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500); // Respons error ke browser
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}
?>

