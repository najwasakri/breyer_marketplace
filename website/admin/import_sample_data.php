<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'includes/db_connect.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents('sample_data.sql');
    
    // Split SQL statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    $errors = [];
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                if ($conn->query($statement)) {
                    $success_count++;
                } else {
                    $error_count++;
                    $errors[] = $conn->error;
                }
            } catch (Exception $e) {
                $error_count++;
                $errors[] = $e->getMessage();
            }
        }
    }
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Import Sample Data</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            .success { color: green; }
            .error { color: red; }
            .btn { background: #2056a8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <h2>Import Sample Data - Hasil</h2>
        <div class='success'>✓ Berjaya: {$success_count} statements</div>";
    
    if ($error_count > 0) {
        echo "<div class='error'>✗ Ralat: {$error_count} statements</div>";
        echo "<h3>Detil Ralat:</h3><ul>";
        foreach ($errors as $error) {
            echo "<li>{$error}</li>";
        }
        echo "</ul>";
    }
    
    echo "<br><br>
        <a href='dashboard.php' class='btn'>Kembali ke Dashboard</a>
        <br><br>
        <p><strong>Tables yang dicipta/dikemaskini:</strong></p>
        <ul>
            <li>courses (5 sample courses)</li>
            <li>students (5 sample students)</li>
            <li>orders (5 sample orders)</li>
            <li>inventory (7 sample items)</li>
        </ul>
        
        <p><strong>Statistik Auto yang akan dipaparkan:</strong></p>
        <ul>
            <li><strong>Total Kursus:</strong> 5</li>
            <li><strong>Total Pelajar:</strong> 5</li>
            <li><strong>Jumlah Jualan:</strong> RM 7,500 (dari orders yang completed/paid)</li>
            <li><strong>Inventori:</strong> 250 (total quantity)</li>
        </ul>
    </body>
    </html>";

} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Import Error</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            .error { color: red; }
            .btn { background: #2056a8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <h2>Import Sample Data - Ralat</h2>
        <div class='error'>Ralat: " . $e->getMessage() . "</div>
        <br><br>
        <a href='dashboard.php' class='btn'>Kembali ke Dashboard</a>
    </body>
    </html>";
}
?>
