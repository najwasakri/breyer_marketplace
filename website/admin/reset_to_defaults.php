<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak dibenarkan']);
    exit;
}

try {
    require_once 'includes/db_connect.php';
    
    // Reset to original default values
    $defaultStats = [
        'courses' => '5',
        'students' => '150',
        'sales' => 'RM 150',
        'inventory' => '7'
    ];
    
    // Create table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS dashboard_stats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        stat_type VARCHAR(50) NOT NULL UNIQUE,
        stat_value VARCHAR(100) NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Reset each statistic to default
    foreach ($defaultStats as $statType => $statValue) {
        $stmt = $conn->prepare("INSERT INTO dashboard_stats (stat_type, stat_value) VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE stat_value = VALUES(stat_value)");
        $stmt->bind_param("ss", $statType, $statValue);
        $stmt->execute();
        $stmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $defaultStats,
        'message' => 'Statistik telah direset kepada nilai asal',
        'action' => 'reset_to_defaults'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ralat sistem: ' . $e->getMessage()
    ]);
}
?>
