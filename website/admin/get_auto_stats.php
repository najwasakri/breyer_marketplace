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
    
    $stats = [];
    
    // First, check if we have synced stats in dashboard_stats table
    $savedStats = [];
    try {
        $result = $conn->query("SELECT stat_type, stat_value FROM dashboard_stats");
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $savedStats[$row['stat_type']] = $row['stat_value'];
            }
        }
    } catch (Exception $e) {
        // Table doesn't exist yet
    }
    
    // If we have saved stats, use them (they are the permanent synced data)
    if (!empty($savedStats)) {
        $stats = $savedStats;
    } else {
        // Otherwise, calculate from database tables (fallback)
    
    // 1. Count Total Courses from courses table
    try {
        $result = $conn->query("SELECT COUNT(*) as total FROM courses");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['courses'] = $row['total'];
        } else {
            $stats['courses'] = 0;
        }
    } catch (Exception $e) {
        // Table might not exist, set default
        $stats['courses'] = 0;
    }
    
    // 2. Count Total Students - try multiple approaches
    try {
        $total_students = 0;
        
        // Approach 1: Sum student_count from courses table (from manage_courses.php)
        $result = $conn->query("SELECT SUM(student_count) as total FROM courses WHERE student_count > 0");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['total'] > 0) {
                $total_students = $row['total'];
            }
        }
        
        // Approach 2: If no data from courses, try students table
        if ($total_students == 0) {
            $result = $conn->query("SELECT COUNT(*) as total FROM students");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $total_students = $row['total'];
            }
        }
        
        // Approach 3: If still no data, try users table
        if ($total_students == 0) {
            $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'student' OR role IS NULL");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $total_students = $row['total'];
            }
        }
        
        $stats['students'] = $total_students;
        
    } catch (Exception $e) {
        // Tables might not exist, set default
        $stats['students'] = 0;
    }
    
    // 3. Calculate Total Sales from orders/payments table
    try {
        $total_sales = 0;
        
        // Approach 1: Try orders table with completed/paid status
        $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status IN ('completed', 'paid')");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['total'] > 0) {
                $total_sales = $row['total'];
            }
        }
        
        // Approach 2: If no orders data, try payments table
        if ($total_sales == 0) {
            $result = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status IN ('success', 'completed', 'paid')");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row['total'] > 0) {
                    $total_sales = $row['total'];
                }
            }
        }
        
        // Approach 3: If still no data, calculate from course prices * student enrollments
        if ($total_sales == 0) {
            $result = $conn->query("
                SELECT SUM(c.price * c.student_count) as total 
                FROM courses c 
                WHERE c.student_count > 0 AND c.price > 0
            ");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row['total'] > 0) {
                    $total_sales = $row['total'];
                }
            }
        }
        
        $stats['sales'] = 'RM ' . number_format($total_sales, 0);
        
    } catch (Exception $e) {
        // Tables might not exist, set default
        $stats['sales'] = 'RM 0';
    }
    
    // 4. Count Total Inventory from inventory/products table
    try {
        // Try inventory table first
        $result = $conn->query("SELECT SUM(quantity) as total FROM inventory WHERE quantity > 0");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['inventory'] = $row['total'] ? $row['total'] : 0;
        } else {
            // Try products table
            $result = $conn->query("SELECT SUM(stock) as total FROM products WHERE stock > 0");
            if ($result) {
                $row = $result->fetch_assoc();
                $stats['inventory'] = $row['total'] ? $row['total'] : 0;
            } else {
                $stats['inventory'] = 0;
            }
        }
    } catch (Exception $e) {
        // Tables might not exist, set default
        $stats['inventory'] = 0;
    }
    
    } // End of else block for fallback calculation
    
    // Return the calculated stats
    echo json_encode([
        'success' => true, 
        'stats' => $stats,
        'message' => 'Statistik dikemaskini dari database (kekal selepas sync)'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Ralat sistem: ' . $e->getMessage()
    ]);
}
?>
