<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak dibenarkan']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['type']) || !isset($input['value'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$type = $input['type'];
$value = $input['value'];

// Validate stat types
$allowedTypes = ['courses', 'students', 'sales', 'inventory'];
if (!in_array($type, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Jenis statistik tidak sah']);
    exit;
}

// Validate values
if ($type === 'sales') {
    // For sales, allow RM prefix
    if (!preg_match('/^RM\s*\d+$/', $value)) {
        echo json_encode(['success' => false, 'message' => 'Format jualan tidak sah (contoh: RM 150)']);
        exit;
    }
} else {
    // For other types, must be numeric
    if (!is_numeric($value) || $value < 0) {
        echo json_encode(['success' => false, 'message' => 'Nilai mesti nombor positif']);
        exit;
    }
}

try {
    require_once 'includes/db_connect.php';
    
    // Create stats table if it doesn't exist
    $createTable = "CREATE TABLE IF NOT EXISTS dashboard_stats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        stat_type VARCHAR(20) NOT NULL UNIQUE,
        stat_value VARCHAR(50) NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by INT,
        FOREIGN KEY (updated_by) REFERENCES admin(admin_id)
    )";
    $conn->query($createTable);
    
    // Insert or update the stat
    $stmt = $conn->prepare("INSERT INTO dashboard_stats (stat_type, stat_value, updated_by) 
                           VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE 
                           stat_value = VALUES(stat_value), 
                           updated_by = VALUES(updated_by),
                           updated_at = CURRENT_TIMESTAMP");
    
    $stmt->bind_param("ssi", $type, $value, $_SESSION['admin_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Statistik berjaya dikemaskini']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ralat database: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ralat sistem: ' . $e->getMessage()]);
}
?>
