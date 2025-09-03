<?php
session_start();
header('Content-Type: application/json');

// Check admin permission
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak dibenarkan']);
    exit;
}

try {
    require_once 'includes/db_connect.php';
    
    // Function to get real statistics from actual data sources
    function getRealStatistics($conn) {
        $stats = [
            'courses' => 0,
            'students' => 0, 
            'sales' => 'RM 0',
            'inventory' => 0
        ];
        
        // === 1. TOTAL KURSUS ===
        // Count from courses table
        try {
            $result = $conn->query("SELECT COUNT(*) as total FROM courses");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stats['courses'] = (int)$row['total'];
            }
        } catch (Exception $e) {
            $stats['courses'] = 0;
        }
        
        // === 2. TOTAL PELAJAR ===
        // Priority: courses.student_count > students table > users table
        try {
            $total_students = 0;
            
            // First: Sum from courses table (from Senarai Kursus - Bil. Pelajar)
            $result = $conn->query("SELECT SUM(student_count) as total FROM courses WHERE student_count > 0");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row['total'] > 0) {
                    $total_students = (int)$row['total'];
                }
            }
            
            // Fallback: Count from students table if courses.student_count is empty
            if ($total_students == 0) {
                $result = $conn->query("SELECT COUNT(*) as total FROM students");
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $total_students = (int)$row['total'];
                }
            }
            
            // Final fallback: Count from users table
            if ($total_students == 0) {
                $result = $conn->query("SELECT COUNT(*) as total FROM users");
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $total_students = (int)$row['total'];
                }
            }
            
            $stats['students'] = $total_students;
            
        } catch (Exception $e) {
            $stats['students'] = 0;
        }
        
        // === 3. TOTAL JUALAN ===
        // Priority: orders table > payments table > calculated from courses
        try {
            $total_sales = 0;
            
            // First: Sum from orders table (completed/paid orders)
            $result = $conn->query("
                SELECT SUM(total_amount) as total 
                FROM orders 
                WHERE status IN ('completed', 'paid', 'success')
            ");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row['total'] > 0) {
                    $total_sales = (float)$row['total'];
                }
            }
            
            // Fallback: Calculate from course prices and enrollments
            if ($total_sales == 0) {
                $result = $conn->query("
                    SELECT SUM(COALESCE(price, 0) * COALESCE(student_count, 0)) as total 
                    FROM courses 
                    WHERE student_count > 0 AND price > 0
                ");
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    if ($row['total'] > 0) {
                        $total_sales = (float)$row['total'];
                    }
                }
            }
            
            $stats['sales'] = 'RM ' . number_format($total_sales, 0);
            
        } catch (Exception $e) {
            $stats['sales'] = 'RM 0';
        }
        
        // === 4. TOTAL INVENTORI ===
        // Sum quantity from inventory table
        try {
            $result = $conn->query("SELECT SUM(COALESCE(quantity, 0)) as total FROM inventory");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stats['inventory'] = (int)($row['total'] ?? 0);
            }
        } catch (Exception $e) {
            $stats['inventory'] = 0;
        }
        
        return $stats;
    }
    
    // Get current real statistics
    $realStats = getRealStatistics($conn);
    
    // Save the real stats to dashboard_stats table for persistence
    try {
        // Create dashboard_stats table if not exists
        $conn->query("CREATE TABLE IF NOT EXISTS dashboard_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stat_type VARCHAR(50) NOT NULL UNIQUE,
            stat_value VARCHAR(100) NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Update/Insert each statistic
        foreach ($realStats as $statType => $statValue) {
            $stmt = $conn->prepare("INSERT INTO dashboard_stats (stat_type, stat_value) VALUES (?, ?) 
                                   ON DUPLICATE KEY UPDATE stat_value = VALUES(stat_value)");
            $stmt->bind_param("ss", $statType, $statValue);
            $stmt->execute();
            $stmt->close();
        }
        
    } catch (Exception $e) {
        // Log error but continue
        error_log("Error saving stats to database: " . $e->getMessage());
    }
    
    // Return the results
    echo json_encode([
        'success' => true,
        'stats' => $realStats,
        'message' => 'Data statistik berjaya disegerakkan dari sistem sebenar',
        'timestamp' => date('Y-m-d H:i:s'),
        'sources' => [
            'courses' => 'Jadual: courses (COUNT)',
            'students' => 'Jadual: courses.student_count > students > users',
            'sales' => 'Jadual: orders > courses (harga × pelajar)',
            'inventory' => 'Jadual: inventory (SUM quantity)'
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ralat sistem: ' . $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ]);
}
?>
