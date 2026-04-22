<?php
session_start();
header('Content-Type: application/json');
$conn = mysqli_connect("localhost", "root", "", "breyer_marketplace");

// Periksa sambungan
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ic_number = preg_replace('/[^0-9]/', '', $_POST['ic_number']); // Remove non-numeric characters
    $password = $_POST['password'];

    // Validate IC number format (12 digits)
    if (strlen($ic_number) !== 12) {
        echo json_encode(['success' => false, 'message' => 'IC number must be 12 digits']);
        exit;
    }

    $query = "SELECT * FROM users WHERE ic_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $ic_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {  // Changed from password to password_hash
            $_SESSION['user_id'] = $row['user_id'];  // Changed from id to user_id
            $_SESSION['name'] = $row['full_name'];    // Changed from name to full_name
            $_SESSION['ic'] = $row['ic_number'];
            unset($_SESSION['class']); // Remove class as it's not in the database
            
            echo json_encode(['success' => true]);
            exit;
        }
    }
    
    // Add logging for debugging
    error_log("Login failed for IC: " . $ic_number);
    
    echo json_encode([
        'success' => false, 
        'message' => 'No. Kad Pengenalan atau kata laluan tidak sah'
    ]);
}
?>

