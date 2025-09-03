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
    <meta charset="UTF-8">    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            background: #90caf9;
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
            background: #90caf9;
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
            background: #90caf9;
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
            background: #90caf9;
            padding: 10px 15px;
            text-align: left;
            font-weight: bold;
            border: none;
        }
        
        tr {
            border: 2px solid #90caf9;
        }
        
        td {
            padding: 10px 15px;
            text-align: left;
            border: none;
        }
        
        tr:nth-child(even) {
            background: white;
        }
        
        tr:nth-child(odd) {
            background: white;
        }

        .btn-back {
            display: inline-block;
            margin: 12px 0 12px 0;
            padding: 3px 12px;      /* kecilkan padding */
            background: #90caf9;
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
            background: #bbdefb;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .btn-back {
                padding: 8px 12px;
                font-size: 0.9rem;
                margin-bottom: 15px;
                display: block;
                text-align: center;
                width: fit-content;
            }

            .inventory-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .inventory-header h2 {
                font-size: 1.4rem;
                width: 100%;
            }

            .add-item-btn {
                width: 100%;
                padding: 12px;
                font-size: 1rem;
                border-radius: 8px;
            }

            /* Table Mobile Optimization */
            .inventory-table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }

            .inventory-table {
                min-width: 100%;
                font-size: 0.9rem;
                width: 100%;
            }

            /* Ensure white background shows on mobile */
            .inventory-table tr:nth-child(even) {
                background: white !important;
            }

            .inventory-table tr:nth-child(odd) {
                background: white !important;
            }

            .inventory-table th,
            .inventory-table td {
                padding: 10px 8px;
                font-size: 0.85rem;
                vertical-align: top;
            }

            .inventory-table th {
                font-size: 0.9rem;
                white-space: nowrap;
            }

            /* Mobile Button Styling */
            .btn {
                padding: 6px 10px;
                font-size: 0.8rem;
                margin: 2px;
                border-radius: 5px;
                min-width: 50px;
            }

            .btn-primary,
            .btn-secondary {
                padding: 6px 10px;
                font-size: 0.8rem;
                margin: 1px;
            }

            .btn-danger {
                padding: 6px 10px;
                font-size: 0.8rem;
                margin: 1px;
            }

            /* Modal Mobile Optimization */
            .modal-content {
                width: 95%;
                margin: 20px auto;
                max-height: 85vh;
                overflow-y: auto;
                padding: 20px;
                border-radius: 12px;
            }

            .modal-content h3 {
                font-size: 1.2rem;
                margin-bottom: 20px;
            }

            .close-modal {
                font-size: 1.5rem;
                top: 12px;
                right: 15px;
            }

            .form-group {
                margin-bottom: 18px;
            }

            .form-group label {
                font-size: 0.95rem;
                margin-bottom: 8px;
                font-weight: bold;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                font-size: 1rem;
                padding: 12px;
                border-radius: 6px;
                border: 2px solid #ddd;
            }

            .form-group input:focus,
            .form-group select:focus,
            .form-group textarea:focus {
                border-color: #90caf9;
                outline: none;
            }

            .modal-footer {
                margin-top: 25px;
                flex-direction: column;
                gap: 10px;
            }

            .modal-footer .btn {
                width: 100%;
                padding: 12px;
                font-size: 1rem;
                margin: 0;
            }

            /* Student count section optimization */
            .inventory-table td:nth-child(3) {
                white-space: nowrap;
            }

            /* Action buttons optimization */
            .inventory-table td:nth-child(4) {
                white-space: nowrap;
            }

            .inventory-table td:nth-child(4) form {
                display: block;
                margin-top: 5px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 10px;
            }

            .inventory-header h2 {
                font-size: 1.2rem;
            }

            .btn-back {
                font-size: 0.85rem;
                padding: 6px 10px;
            }

            /* Table becomes card-like on very small screens */
            .inventory-table {
                font-size: 0.8rem;
                border: none;
            }

            .inventory-table thead {
                display: none;
            }

            .inventory-table tbody tr {
                display: block;
                margin-bottom: 15px;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                position: relative;
            }

            /* Maintain white background in mobile card view */
            .inventory-table tbody tr:nth-child(even) {
                background: white !important;
            }

            .inventory-table tbody tr:nth-child(odd) {
                background: white !important;
            }

            .inventory-table tbody td {
                display: block;
                padding: 5px 0;
                border: none;
                text-align: left;
            }

            .inventory-table tbody td:before {
                content: attr(data-label) ": ";
                font-weight: bold;
                color: #333;
                display: inline-block;
                width: 100px;
            }

            .inventory-table tbody td:nth-child(1):before {
                content: "Bil: ";
            }

            .inventory-table tbody td:nth-child(2):before {
                content: "Kursus: ";
            }

            .inventory-table tbody td:nth-child(3):before {
                content: "Pelajar: ";
            }

            .inventory-table tbody td:nth-child(4):before {
                content: "Tindakan: ";
            }

            .inventory-table tbody td:nth-child(4) {
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid #eee;
            }

            .inventory-table tbody td:nth-child(4) form {
                display: inline-block;
                margin-right: 5px;
                margin-top: 5px;
            }

            /* Button optimizations for card layout */
            .btn {
                padding: 8px 12px;
                font-size: 0.8rem;
                margin: 2px;
                min-width: 60px;
            }

            .btn-primary,
            .btn-secondary {
                padding: 6px 10px;
                font-size: 0.8rem;
            }

            /* Modal optimizations for small screens */
            .modal-content {
                width: 98%;
                margin: 10px auto;
                padding: 15px;
                max-height: 90vh;
            }

            .modal-content h3 {
                font-size: 1.1rem;
                margin-bottom: 15px;
            }

            .close-modal {
                font-size: 1.3rem;
                top: 10px;
                right: 12px;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-group label {
                font-size: 0.9rem;
                margin-bottom: 6px;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                font-size: 0.9rem;
                padding: 10px;
                border-radius: 5px;
            }

            .modal-footer {
                margin-top: 20px;
            }

            .modal-footer .btn {
                padding: 10px;
                font-size: 0.9rem;
            }

            /* Add item button optimization */
            .add-item-btn {
                padding: 14px;
                font-size: 0.95rem;
                border-radius: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
                <a href="dashboard.php" class="btn-back" aria-label="Back" style="
                    position: relative;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    margin: 12px 0 12px 0;
                    background: #90caf9;
                    color: #fff;
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    text-decoration: none;
                    font-weight: bold;
                    font-size: 1.5rem;
                    box-shadow: 0 2px 8px rgba(21, 101, 192, 0.3);
                    transition: all 0.3s ease;
                    border: none;
                    z-index: 1000;
                    padding: 0;
                    cursor: pointer;
                ">
                        <svg width="28" height="28" viewBox="0 0 28 28" style="display:block; margin:auto;" xmlns="http://www.w3.org/2000/svg">
                                <polygon points="19,7 19,21 7,14" fill="#fff" />
                        </svg>
                </a>
        <div class="inventory-header">
            <h2 style="margin:0;">Senarai Kursus</h2>
            <button class="add-item-btn" onclick="showAddItemModal()">+ Tambah Kursus</button>
        </div>

        <div class="inventory-table-container">
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
            preventBodyScroll(true);
        }
        function closeEditModal() {
            document.getElementById('editKursusModal').style.display = 'none';
            preventBodyScroll(false);
        }
        function showAddItemModal() {
            document.getElementById('addItemModal').style.display = 'block';
            preventBodyScroll(true);
        }
        function closeModal() {
            document.getElementById('addItemModal').style.display = 'none';
            preventBodyScroll(false);
        }
        function showAddStudentModal(course) {
            document.getElementById('student_course').value = course;
            document.getElementById('addStudentModal').style.display = 'block';
            preventBodyScroll(true);
        }
        function closeAddStudentModal() {
            document.getElementById('addStudentModal').style.display = 'none';
            preventBodyScroll(false);
        }
        function showEditStudentCountModal(course, count) {
            document.getElementById('edit_student_course').value = course;
            document.getElementById('edit_student_count').value = count;
            document.getElementById('editStudentCountModal').style.display = 'block';
            preventBodyScroll(true);
        }
        function closeEditStudentCountModal() {
            document.getElementById('editStudentCountModal').style.display = 'none';
            preventBodyScroll(false);
        }

        // Prevent body scroll when modal is open (mobile optimization)
        function preventBodyScroll(prevent) {
            if (window.innerWidth <= 768) {
                if (prevent) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = 'auto';
                }
            }
        }

        // Close modal when clicking outside (mobile-friendly)
        window.onclick = function(event) {
            const modals = ['addItemModal', 'editKursusModal', 'addStudentModal', 'editStudentCountModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    modal.style.display = 'none';
                    preventBodyScroll(false);
                }
            });
        }

        // Mobile touch optimization for buttons
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('touchstart', function() {
                    this.style.opacity = '0.7';
                });
                button.addEventListener('touchend', function() {
                    this.style.opacity = '1';
                });
            });
        });
    </script>
</body>
</html>