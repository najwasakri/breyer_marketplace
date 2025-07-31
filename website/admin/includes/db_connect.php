<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "breyer_marketplace";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

$conn->set_charset("utf8mb4");
?>