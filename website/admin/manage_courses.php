<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Database connection
require_once 'includes/db_connect.php';

// Buat jadual courses jika belum ada
$conn->query("CREATE TABLE IF NOT EXISTS courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_name VARCHAR(255) NOT NULL UNIQUE,
    student_count INT DEFAULT 0
)");

// Insert default courses jika jadual kosong
$check = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc();
if ($check['count'] == 0) {
    $default_courses = ['COMPUTER SYSTEM', 'ADMINISTRATION MANAGMENT', 'CULINARY', 'ELECTRICAL', 'F&B'];
    foreach ($default_courses as $course) {
        $stmt = $conn->prepare("INSERT INTO courses (course_name) VALUES (?)");
        $stmt->bind_param("s", $course);
        $stmt->execute();
        $stmt->close();
    }
}

// Ubah bilangan pelajar - simpan dalam database
if (isset($_POST['edit_student_count'])) {
    $course = $_POST['student_course'];
    $count = max(0, (int)$_POST['student_count']);
    
    $stmt = $conn->prepare("UPDATE courses SET student_count = ? WHERE course_name = ?");
    $stmt->bind_param("is", $count, $course);
    $stmt->execute();
    $stmt->close();
}

// Load courses dari database
$courses_result = $conn->query("SELECT course_name, student_count FROM courses ORDER BY course_name");
$courses = [];
$student_counts = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row['course_name'];
    $student_counts[$row['course_name']] = $row['student_count'];
}

// Simpan kursus dalam session supaya boleh edit/padam
if (!isset($_SESSION['courses'])) {
    $_SESSION['courses'] = [
        'COMPUTER SYSTEM',
        'ADMINISTRATION MANAGMENT',
        'CULINARY',
        'ELECTRICAL',
        'F&B'
    ];
}

// Tambah kursus baru
if (isset($_POST['add_item'])) {
    $new = trim($_POST['item_name']);
    if ($new !== '') {
        $_SESSION['courses'][] = $new;
    }
}

// Edit kursus
if (isset($_POST['edit_kursus'])) {
    $bil = (int)$_POST['edit_bil'] - 1;
    $name = trim($_POST['edit_kursus_name']);
    if ($name !== '' && isset($_SESSION['courses'][$bil])) {
        $_SESSION['courses'][$bil] = $name;
    }
}

// Delete kursus
if (isset($_POST['delete_bil'])) {
    $bil = (int)$_POST['delete_bil'] - 1;
    if (isset($_SESSION['courses'][$bil])) {
        array_splice($_SESSION['courses'], $bil, 1);
    }
}

// Papar kursus
$courses = $_SESSION['courses'];

// Tambah pelajar ke kursus (proses)
if (isset($_POST['add_student'])) {
    $course = $_POST['student_course'];
    $student = trim($_POST['student_name']);
    if ($student !== '') {
        if (!isset($_SESSION['students'][$course])) $_SESSION['students'][$course] = [];
        $_SESSION['students'][$course][] = $student;
    }
}

// Ubah bilangan pelajar
if (isset($_POST['edit_student_count'])) {
    $course = $_POST['student_course'];
    $count = max(0, (int)$_POST['student_count']);
    $_SESSION['students'][$course] = array_fill(0, $count, 'Pelajar');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Senarai Kursus</title>
    <style>
        /* Base styles from dashboard.php */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        /* Inventory specific styles */
        .inventory-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .add-item-btn {
            background: #FFE45C;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .inventory-table th,
        .inventory-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .inventory-table th {
            background: #FFE45C;
            font-weight: bold;
        }

        .stock-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .in-stock {
            background: #4CAF50;
            color: white;
        }

        .low-stock {
            background: #ff9800;
            color: white;
        }

        .out-of-stock {
            background: #f44336;
            color: white;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            position: relative; /* Tambah ini */
        }

        .modal-content h3 {
            text-align: center;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 1.8rem;
            font-weight: bold;
            color: #888;
            cursor: pointer;
            transition: color 0.2s;
            z-index: 10;
        }
        .close-modal:hover {
            color: #f44336;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Add new button styles */
        .btn {
            padding: 4px 10px;    /* kecilkan padding */
            font-size: 0.9em;    /* kecilkan font */
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            min-width: 60px;      /* kecilkan min width */
            border: none;
        }

        .btn-primary,
        .btn-secondary {
            background: #FFE45C;
            color: #000;
            border: none;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        /* Update these specific table styles in your <style> section */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: none;
        }
        
        th {
            background-color: #FFE45C;
            padding: 10px 15px;
            text-align: left;
            font-weight: bold;
            border: none;
        }
        
        tr {
            border: none;
        }
        
        td {
            padding: 10px 15px;
            text-align: left;
            border: none;
        }
        
        tr:nth-child(even) {
            background-color: #fefdfdff;
        }
        
        tr:nth-child(odd) {
            background-color: #fff;
        }

        .btn-back {
            display: inline-block;
            margin: 12px 0 12px 0;
            padding: 3px 12px;      /* kecilkan padding */
            background: #FFE45C;
            color: #222;
            border-radius: 50px;    /* kecilkan radius */
            font-weight: bold;      /* pastikan sudah ada */
            font-family: Arial Black, Arial, sans-serif; /* tambah ini untuk lebih tebal */
            text-decoration: none;
            font-size: 1.05rem;     /* kecilkan font */
            box-shadow: 0 2px 8px rgba(44,62,80,0.08);
            transition: background 0.2s;
            border: none;
            letter-spacing: 1px;
        }
        .btn-back:hover {
            background: #1;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <a href="dashboard.php" class="btn-back">&#11013;</a>
        <div class="inventory-header">
            <h2 style="margin:0;">Senarai Kursus</h2>
            <button class="add-item-btn" onclick="showAddItemModal()">+ Tambah Kursus</button>
        </div>

        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Bil</th>
                    <th>Nama kursus</th>
                    <th>Bil. Pelajar</th> <!-- Kolum bilangan pelajar -->
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $bil = 1;
                // Simpan bilangan pelajar dalam session (contoh)
                if (!isset($_SESSION['students'])) $_SESSION['students'] = [];
                foreach ($courses as $course) {
                    $count = $student_counts[$course];
                    echo "<tr>";
                    echo "<td>$bil</td>";
                    echo "<td>$course</td>";
                    echo "<td>
                        $count
                        <button class='btn btn-primary' onclick=\"showEditStudentCountModal('".htmlspecialchars($course, ENT_QUOTES)."', $count)\">Ubah</button>
                    </td>";
                    echo "<td>
                        <button class=\"btn btn-primary\" onclick=\"showEditModal($bil, '".htmlspecialchars($course, ENT_QUOTES)."')\">Edit</button>
                        <form method='POST' action='' style='display:inline;'>
                            <input type='hidden' name='delete_bil' value='$bil'>
                            <button type='submit' class='btn btn-danger' style='background:#ff4444;color:#fff;'>Delete</button>
                        </form>
                    </td>";
                    echo "</tr>";
                    $bil++;
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Add Item Modal -->
    <div id="addItemModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3>Tambah Kursus Baru</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Nama Kursus</label>
                    <input type="text" name="item_name" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_item" class="btn btn-primary">Simpan</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Kursus Modal -->
    <div id="editKursusModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
            <h3>Edit Kursus</h3>
            <form method="POST" action="">
                <input type="hidden" name="edit_bil" id="edit_bil">
                <div class="form-group">
                    <label>Nama Kursus</label>
                    <input type="text" name="edit_kursus_name" id="edit_kursus_name" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="edit_kursus" class="btn btn-primary">Simpan</button>
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeAddStudentModal()">&times;</span>
            <h3>Tambah Pelajar ke Kursus</h3>
            <form method="POST" action="">
                <input type="hidden" name="student_course" id="student_course">
                <div class="form-group">
                    <label>Nama Pelajar</label>
                    <input type="text" name="student_name" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_student" class="btn btn-primary">Simpan</button>
                    <button type="button" onclick="closeAddStudentModal()" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ubah Bilangan Pelajar -->
    <div id="editStudentCountModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditStudentCountModal()">&times;</span>
            <h3>Ubah Bilangan Pelajar</h3>
            <form method="POST" action="">
                <input type="hidden" name="student_course" id="edit_student_course">
                <div class="form-group">
                    <label>Bilangan Pelajar</label>
                    <input type="number" name="student_count" id="edit_student_count" min="0" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="edit_student_count" class="btn btn-primary">Simpan</button>
                    <button type="button" onclick="closeEditStudentCountModal()" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showEditModal(bil, name) {
            document.getElementById('edit_bil').value = bil;
            document.getElementById('edit_kursus_name').value = name;
            document.getElementById('editKursusModal').style.display = 'block';
        }
        function closeEditModal() {
            document.getElementById('editKursusModal').style.display = 'none';
        }
        function showAddItemModal() {
            document.getElementById('addItemModal').style.display = 'block';
        }
        function closeModal() {
            document.getElementById('addItemModal').style.display = 'none';
        }
        function showAddStudentModal(course) {
            document.getElementById('student_course').value = course;
            document.getElementById('addStudentModal').style.display = 'block';
        }
        function closeAddStudentModal() {
            document.getElementById('addStudentModal').style.display = 'none';
        }
        function showEditStudentCountModal(course, count) {
            document.getElementById('edit_student_course').value = course;
            document.getElementById('edit_student_count').value = count;
            document.getElementById('editStudentCountModal').style.display = 'block';
        }
        function closeEditStudentCountModal() {
            document.getElementById('editStudentCountModal').style.display = 'none';
        }
    </script>
</body>
</html>