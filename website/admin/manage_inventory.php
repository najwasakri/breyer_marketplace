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
        $quantity = (int)$_POST['quantity'];
        $price = (float)$_POST['price'];
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

if (isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $conn->query("DELETE FROM inventory WHERE id = $delete_id");
    header("Location: manage_inventory.php");
    exit;
}

// Edit item handling
if (isset($_POST['edit_item'])) {
    $edit_id = (int)$_POST['edit_id'];
    $item_name = $_POST['edit_item_name'];
    $quantity = (int)$_POST['edit_quantity'];
    $price = (float)$_POST['edit_price'];
    $category = $_POST['edit_category'];

    $sql = "UPDATE inventory SET item_name=?, quantity=?, price=?, category=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sidsi", $item_name, $quantity, $price, $category, $edit_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_inventory.php");
    exit;
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
            display: inline-block;
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
            position: relative;
        }

        .modal-content h3 {
            text-align: center;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 1.8rem; font-weight: bold;
            font-weight: bold;
            color: #888;
            cursor: pointer;
            transition: color 0.2s;
            z-index: 10;
        }
        .close-modal:hover {
            color: #f44336;
       
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

        .btn-edit {
            background-color: #FFE45C;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 5px;
        }

        .btn-delete {
            background-color: #ff4444;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-edit:hover {
            background-color: #ffd700;
        }

        .btn-delete:hover {
            background-color: #cc0000;
        }

        .add-item-actions {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 10px;
        }

        .profile-section {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .profile-icon {
            width: 24px;
            height: 24px;
        }

        .profile-icon svg {
            width: 100%;
            height: 100%;
        }

        .profile-section span {
            font-size: 1.5rem;
            font-weight: bold;
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
            background: #fff176;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <a href="dashboard.php" class="btn-back">&#11013;</a>
        <div class="inventory-header">
            <h2 style="margin:0;">Inventori</h2>
            <button class="add-item-btn" onclick="showAddItemModal()">Tambah Item Baru</button>
        </div>

        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Bil</th>
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

                $bil = 1;
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
                        <td><?php echo $bil++; ?></td>
                        <td><?php echo $row['item_name']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>RM <?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td><span class="stock-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        <td>
                            <button class="btn-edit" onclick="editItem(<?php echo $row['id']; ?>)">Edit</button>
                            <button class="btn-delete" onclick="deleteItem(<?php echo $row['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Add Item Modal -->
    <div id="addItemModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
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
                <div class="add-item-actions">
                    <button type="submit" name="add_item" class="add-item-btn">Simpan</button>
                    <button type="button" onclick="closeModal()" class="add-item-btn">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div id="editItemModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
            <h3>Edit Item</h3>
            <form method="POST" action="">
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="form-group">
                    <label>Nama Item</label>
                    <input type="text" name="edit_item_name" id="edit_item_name" required>
                </div>
                <div class="form-group">
                    <label>Kuantiti</label>
                    <input type="number" name="edit_quantity" id="edit_quantity" required>
                </div>
                <div class="form-group">
                    <label>Harga (RM)</label>
                    <input type="number" step="0.01" name="edit_price" id="edit_price" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="edit_category" id="edit_category" required>
                </div>
                <div class="add-item-actions">
                    <button type="submit" name="edit_item" class="add-item-btn">Simpan</button>
                    <button type="button" onclick="closeEditModal()" class="add-item-btn">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="delete_id" id="delete_id">
    </form>

    <script>
        function showAddItemModal() {
            document.getElementById('addItemModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addItemModal').style.display = 'none';
        }

        function editItem(id) {
            // Cari data baris dari jadual
            var row = document.querySelector('button[onclick="editItem(' + id + ')"]').closest('tr');
            var item_name = row.children[1].textContent.trim();
            var quantity = row.children[2].textContent.trim();
            var price = row.children[3].textContent.replace('RM', '').trim();
            var category = row.children[4].textContent.trim();

            // Isi data ke dalam modal
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_item_name').value = item_name;
            document.getElementById('edit_quantity').value = quantity;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_category').value = category;

            // Papar modal
            document.getElementById('editItemModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editItemModal').style.display = 'none';
        }

        function deleteItem(id) {
            if (confirm('Adakah anda pasti untuk padam item ini?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>