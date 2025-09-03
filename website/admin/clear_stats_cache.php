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
    
    // Clear any cached sync data
    $conn->query("DELETE FROM dashboard_stats WHERE 1=1");
    
    // Reset localStorage cache flag
    echo json_encode([
        'success' => true,
        'message' => 'Cache data statistik telah dibersihkan',
        'action' => 'cache_cleared'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ralat sistem: ' . $e->getMessage()
    ]);
}
?>
