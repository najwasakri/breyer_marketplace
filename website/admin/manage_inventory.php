<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Database connection
require_once 'includes/db_connect.php';

// Add new inventory item with error handling
if (isset($_POST['add_item'])) {
    try {
        $item_name = $_POST['item_name'];
        $quantity = $_POST['quantity'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        
        $sql = "INSERT INTO inventory (item_name, quantity, price, category) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        
        $stmt->bind_param("sids", $item_name, $quantity, $price, $category);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $success_message = "Item successfully added!";
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventori - Admin</title>
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
    </style>
</head>
<body>

    <div class="main-content">
        <div class="profile-section">
            <span>PROFILE</span>
            <div class="profile-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
        </div>

        <div class="inventory-header">
            <h2>Inventori</h2>
            <button class="add-item-btn" onclick="showAddItemModal()">Tambah Item Baru</button>
        </div>

        <table class="inventory-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Item</th>
                    <th>Kuantiti</th>
                    <th>Harga</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Update the query section with error handling
                $sql = "SELECT * FROM inventory ORDER BY id DESC";
                try {
                    $result = $conn->query($sql);
                    if (!$result) {
                        throw new Exception($conn->error);
                    }
                } catch (Exception $e) {
                    die("Error fetching inventory: " . $e->getMessage());
                }

                while ($row = $result->fetch_assoc()) {
                    $status_class = '';
                    if ($row['quantity'] > 10) {
                        $status_class = 'in-stock';
                        $status_text = 'In Stock';
                    } elseif ($row['quantity'] > 0) {
                        $status_class = 'low-stock';
                        $status_text = 'Low Stock';
                    } else {
                        $status_class = 'out-of-stock';
                        $status_text = 'Out of Stock';
                    }
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['item_name']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>RM <?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td><span class="stock-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        <td>
                            <button onclick="editItem(<?php echo $row['id']; ?>)">Edit</button>
                            <button onclick="deleteItem(<?php echo $row['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Add Item Modal -->
    <div id="addItemModal" class="modal">
        <div class="modal-content">
            <h3>Tambah Item Baru</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Nama Item</label>
                    <input type="text" name="item_name" required>
                </div>
                <div class="form-group">
                    <label>Kuantiti</label>
                    <input type="number" name="quantity" required>
                </div>
                <div class="form-group">
                    <label>Harga (RM)</label>
                    <input type="number" step="0.01" name="price" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="category" required>
                </div>
                <button type="submit" name="add_item" class="add-item-btn">Simpan</button>
                <button type="button" onclick="closeModal()" style="margin-left: 10px;">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function showAddItemModal() {
            document.getElementById('addItemModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addItemModal').style.display = 'none';
        }

        function editItem(id) {
            // Implement edit functionality
            console.log('Edit item:', id);
        }

        function deleteItem(id) {
            if (confirm('Adakah anda pasti untuk padam item ini?')) {
                // Implement delete functionality
                console.log('Delete item:', id);
            }
        }
    </script>
</body>
</html>