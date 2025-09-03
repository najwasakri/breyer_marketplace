<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'includes/db_connect.php';

// Function to setup test data for real sync demonstration
function setupTestData($conn) {
    $messages = [];
    
    try {
        // 1. Setup courses with student counts
        $conn->query("CREATE TABLE IF NOT EXISTS courses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_name VARCHAR(255) NOT NULL,
            student_count INT DEFAULT 0,
            price DECIMAL(10,2) DEFAULT 0,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert sample courses with student counts
        $sample_courses = [
            ['COMPUTER SYSTEM', 15, 1200.00],
            ['ADMINISTRATION MANAGEMENT', 12, 1000.00],
            ['CULINARY', 18, 1500.00],
            ['ELECTRICAL', 10, 1800.00],
            ['F&B', 8, 800.00]
        ];
        
        foreach ($sample_courses as $course) {
            $stmt = $conn->prepare("INSERT INTO courses (course_name, student_count, price) VALUES (?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE student_count = VALUES(student_count), price = VALUES(price)");
            $stmt->bind_param("sid", $course[0], $course[1], $course[2]);
            $stmt->execute();
            $stmt->close();
        }
        
        $messages[] = "✅ Courses table updated with student counts";
        
        // 2. Setup students table
        $conn->query("CREATE TABLE IF NOT EXISTS students (
            student_id INT AUTO_INCREMENT PRIMARY KEY,
            student_name VARCHAR(100) NOT NULL,
            student_ic VARCHAR(20),
            email VARCHAR(100),
            phone VARCHAR(20),
            course_enrolled VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Generate students based on course enrollments
        $students_data = [
            // Computer System (15 students)
            ['Ahmad Zulkifli', '001122334455', 'ahmad@email.com', '012-3456789', 'COMPUTER SYSTEM'],
            ['Siti Hajar', '001122334456', 'siti@email.com', '012-3456790', 'COMPUTER SYSTEM'],
            ['Muhammad Hakim', '001122334457', 'hakim@email.com', '012-3456791', 'COMPUTER SYSTEM'],
            ['Nur Aisyah', '001122334458', 'aisyah@email.com', '012-3456792', 'COMPUTER SYSTEM'],
            ['Zul Ariffin', '001122334459', 'zul@email.com', '012-3456793', 'COMPUTER SYSTEM'],
            
            // Administration Management (12 students)  
            ['Faridah Abdullah', '001122334460', 'faridah@email.com', '012-3456794', 'ADMINISTRATION MANAGEMENT'],
            ['Rahman Ali', '001122334461', 'rahman@email.com', '012-3456795', 'ADMINISTRATION MANAGEMENT'],
            ['Aminah Sulaiman', '001122334462', 'aminah@email.com', '012-3456796', 'ADMINISTRATION MANAGEMENT'],
            ['Ismail Hassan', '001122334463', 'ismail@email.com', '012-3456797', 'ADMINISTRATION MANAGEMENT'],
            
            // Culinary (18 students)
            ['Sarah Yaacob', '001122334464', 'sarah@email.com', '012-3456798', 'CULINARY'],
            ['Hafiz Ahmad', '001122334465', 'hafiz@email.com', '012-3456799', 'CULINARY'],
            ['Noor Azlina', '001122334466', 'azlina@email.com', '012-3456800', 'CULINARY'],
            
            // Add more as needed to match course enrollments...
        ];
        
        foreach ($students_data as $student) {
            $stmt = $conn->prepare("INSERT IGNORE INTO students (student_name, student_ic, email, phone, course_enrolled) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $student[0], $student[1], $student[2], $student[3], $student[4]);
            $stmt->execute();
            $stmt->close();
        }
        
        $messages[] = "✅ Students table updated with course enrollments";
        
        // 3. Setup orders table
        $conn->query("CREATE TABLE IF NOT EXISTS orders (
            order_id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT,
            course_name VARCHAR(100),
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'completed', 'paid', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Generate orders based on enrollments
        $sample_orders = [
            [1, 'COMPUTER SYSTEM', 1200.00, 'completed'],
            [2, 'COMPUTER SYSTEM', 1200.00, 'paid'],
            [3, 'ADMINISTRATION MANAGEMENT', 1000.00, 'completed'],
            [4, 'CULINARY', 1500.00, 'paid'],
            [5, 'ELECTRICAL', 1800.00, 'completed'],
            [6, 'F&B', 800.00, 'paid'],
            [7, 'COMPUTER SYSTEM', 1200.00, 'completed'],
            [8, 'CULINARY', 1500.00, 'paid']
        ];
        
        foreach ($sample_orders as $order) {
            $stmt = $conn->prepare("INSERT IGNORE INTO orders (order_id, student_id, course_name, total_amount, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisds", $order[0], $order[1], $order[2], $order[3], $order[4]);
            $stmt->execute();
            $stmt->close();
        }
        
        $messages[] = "✅ Orders table updated with sample transactions";
        
        // 4. Setup inventory table
        $conn->query("CREATE TABLE IF NOT EXISTS inventory (
            item_id INT AUTO_INCREMENT PRIMARY KEY,
            item_name VARCHAR(100) NOT NULL,
            item_code VARCHAR(20),
            quantity INT DEFAULT 0,
            price DECIMAL(10,2) DEFAULT 0,
            category VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $inventory_items = [
            ['Computer Hardware Set', 'CH001', 25, 500.00, 'Equipment'],
            ['Office Supplies', 'OS001', 100, 50.00, 'Supplies'],
            ['Cooking Equipment', 'CE001', 15, 300.00, 'Equipment'],
            ['Electrical Tools', 'ET001', 30, 200.00, 'Tools'],
            ['F&B Utensils', 'FU001', 40, 75.00, 'Utensils'],
            ['Learning Materials', 'LM001', 200, 25.00, 'Materials'],
            ['Safety Equipment', 'SE001', 50, 100.00, 'Safety']
        ];
        
        foreach ($inventory_items as $item) {
            $stmt = $conn->prepare("INSERT INTO inventory (item_name, item_code, quantity, price, category) VALUES (?, ?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), price = VALUES(price)");
            $stmt->bind_param("ssids", $item[0], $item[1], $item[2], $item[3], $item[4]);
            $stmt->execute();
            $stmt->close();
        }
        
        $messages[] = "✅ Inventory table updated with equipment and supplies";
        
        return ['success' => true, 'messages' => $messages];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Process the setup
$result = setupTestData($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup Test Data - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #2056a8;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #163e7a;
        }
        .info {
            background: #cce7ff;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Setup Data Ujian untuk Sistem Sync</h1>
        
        <?php if ($result['success']): ?>
            <div class="success">
                <strong>✅ Data ujian berjaya disetup!</strong>
                <ul>
                    <?php foreach ($result['messages'] as $msg): ?>
                        <li><?php echo htmlspecialchars($msg); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="error">
                <strong>❌ Ralat semasa setup data:</strong><br>
                <?php echo htmlspecialchars($result['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <h3>📊 Data yang telah disetup:</h3>
            <ul>
                <li><strong>Courses:</strong> 5 kursus dengan jumlah pelajar (COMPUTER SYSTEM: 15, ADMINISTRATION: 12, CULINARY: 18, ELECTRICAL: 10, F&B: 8)</li>
                <li><strong>Students:</strong> Data pelajar yang mendaftar dalam kursus</li>
                <li><strong>Orders:</strong> Transaksi pembayaran kursus (completed/paid)</li>
                <li><strong>Inventory:</strong> 7 item dengan kuantiti total: 460 unit</li>
            </ul>
            
            <h3>🔍 Statistik yang dijangka:</h3>
            <ul>
                <li><strong>Total Kursus:</strong> 5</li>
                <li><strong>Total Pelajar:</strong> 63 (15+12+18+10+8)</li>
                <li><strong>Jumlah Jualan:</strong> RM 10,300 (dari orders completed/paid)</li>
                <li><strong>Total Inventori:</strong> 460 unit</li>
            </ul>
        </div>
        
        <a href="dashboard.php" class="btn">🏠 Kembali ke Dashboard</a>
        <a href="manage_courses.php" class="btn">📚 Lihat Senarai Kursus</a>
        
        <p><em style="color: #666; font-size: 0.9em;">
            Sekarang pergi ke Dashboard dan klik butang "Sync Data Sebenar" untuk melihat statistik dikemaskini secara automatik dari data yang betul!
        </em></p>
    </div>
</body>
</html>
