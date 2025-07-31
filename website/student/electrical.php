<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Electrical Category - Breyer</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: #0a1f4d;  /* Changed from gradient to solid navy */
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 800px;    /* Decreased from 1200px */
            margin: 0 auto;
            padding: 1rem;
            min-height: 90vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            margin-top: -50px;
        }

        .course-grid {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 35px;          /* Increased from 25px */
            flex-wrap: wrap;
            padding: 10px;
            margin-top: 0;
            width: 100%;
        }

        .course-card {
            background: white;
            border-radius: 16px;
            padding: 10px;
            width: 190px;       /* Increased from 180px */
            text-align: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .course-card img {
            width: 100%;
            height: 160px;       /* Decreased from 180px */
            object-fit: contain;
            margin-bottom: 10px;
        }

        .course-card h3 {
            font-size: 1.2rem;   /* Decreased from 1.5rem */
            margin: 10px 0;
            color: #ff6347;
        }

        .price {
            font-size: 1.4rem;   /* Decreased from 1.8rem */
            margin: 12px 0;
            color: #ff6347;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin: 3rem 0;
            color: #ff6347;
        }

        .header h1 {
            font-size: 2.8rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #ff6347;
            letter-spacing: 1px;
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #ff6347;
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .back-btn:hover {
            background: #ff4726;
            transform: translateX(-5px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 18px;
            width: 270px;          /* Balanced medium size */
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1001;
        }

        .close-btn {
            position: absolute;
            right: 12px;          /* Decreased from 15px */
            top: 12px;            /* Decreased from 15px */
            color: #666;
            font-size: 20px;      /* Decreased from 24px */
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }

        .close-btn:hover {
            color: #ff6347;
        }

        .beli-btn {
            background: #ff6347;
            color: white;
            width: 100%;
            padding: 12px 0;     /* Decreased from 15px */
            border: none;
            border-radius: 8px;
            font-size: 1rem;     /* Decreased from 1.1rem */
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .beli-btn:hover {
            background: #ff4726;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 99, 71, 0.3);
        }

        .size-selector {
            text-align: center;
            margin: 20px 0;
        }

        .size-selector h3 {
            color: #ff6347;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .size-grid {
            display: flex;
            justify-content: center;
            gap: 10px;             /* Decreased from 15px */
            margin: 15px 0;        /* Decreased from 25px */
        }

        .size-btn {
            width: 35px;           /* Decreased from 45px */
            height: 35px;          /* Decreased from 45px */
            border: 1px solid #ddd; /* Decreased border width */
            background: white;
            border-radius: 4px;
            font-size: 14px;       /* Decreased from 16px */
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .size-btn:hover {
            border-color: #ff6347;
        }

        .size-btn.selected {
            background: #ff6347;
            color: white;
            border-color: #ff6347;
        }

        .quantity-selector {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;             /* Decreased from 20px */
            margin: 15px 0;        /* Decreased from 25px */
        }

        .quantity-btn {
            width: 25px;           /* Decreased from 30px */
            height: 25px;          /* Decreased from 30px */
            border: none;
            background: #ff6347;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;       /* Decreased from 18px */
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            color: #333;
            margin-bottom: 6px;
            text-align: left;
            font-weight: 500;
        }

        .form-group input {
            width: calc(100% - 16px);
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
        }

        .modal h3 {
            color: #663399;  /* Changed to purple */
            font-size: 1.3rem;     /* Decreased from 1.8rem */
            margin-bottom: 15px;    /* Decreased from 20px */
            text-align: center;
            font-weight: 600;
        }

        /* Bank Payment styles */
        .bank-grid {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 12px;
            max-width: 250px;
            margin: 0 auto;
            max-height: 350px;
            overflow-y: auto;
        }

        .bank-btn {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
            padding: 8px 12px;
            background: #ffffff;
            border: 1px solid #E6F3FF;
            border-radius: 8px;
            text-decoration: none;
            color: #004C99;
            font-weight: 600;
            transition: all 0.3s ease;
            height: 45px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .bank-btn img {
            width: 25px;
            height: 25px;
            margin-right: 12px;
        }

        .bank-btn span {
            font-size: 12px;
        }

        #paymentModal .modal-content {
            width: 300px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-btn">← Kembali</a>
        <div class="header">
            <h1>Electrical Products</h1>
        </div>
        <div class="course-grid">
            <?php
            $products = [
                [
                    'type' => 'BAJU KORPORAT',
                    'price' => 'RM85.00',
                    'image' => 'ads8/breyer-baju1.png'
                ],
                [
                    'type' => 'BAJU T-SHIRT KOLEJ',
                    'price' => 'RM28.00',
                    'image' => 'ads8/breyer-baju1.png'
                ]
            ];

            foreach ($products as $product) {
                echo '<div class="course-card">';
                echo '<img src="'.$product['image'].'" alt="'.$product['type'].'">';
                echo '<h3 style="font-size: 1.1rem; text-transform: uppercase;">'.$product['type'].'</h3>';
                echo '<p class="price">'.$product['price'].'</p>';
                echo '<button class="beli-btn" onclick="openModal(\''.$product['type'].'\', \''.$product['price'].'\')">BELI</button>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <!-- Size Selection Modal -->
    <div id="sizeModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal()">&times;</button>
            <h3>HARGA : <span id="modalPrice"></span></h3>
            <div class="size-grid">
                <button class="size-btn" onclick="selectSize(this, 'S')">S</button>
                <button class="size-btn" onclick="selectSize(this, 'M')">M</button>
                <button class="size-btn" onclick="selectSize(this, 'L')">L</button>
                <button class="size-btn" onclick="selectSize(this, 'XL')">XL</button>
            </div>
            <div class="quantity-selector">
                <button class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                <span id="quantity">1</span>
                <button class="quantity-btn" onclick="updateQuantity(1)">+</button>
            </div>
            <button class="beli-btn" onclick="confirmPurchase()">TERUSKAN</button>
        </div>
    </div>

    <!-- Purchase Form Modal -->
    <div id="purchaseModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closePurchaseModal()">&times;</button>
            <h3>Borang Pembelian</h3>
            <form id="purchaseForm">
                <div class="form-group">
                    <label>Saiz:</label>
                    <input type="text" id="orderSize" readonly>
                </div>
                <div class="form-group">
                    <label>Kuantiti:</label>
                    <input type="number" id="orderQuantity" min="1" required>
                </div>
                <div class="form-group">
                    <label>Nama:</label>
                    <input type="text" id="customerName" required>
                </div>
                <div class="form-group">
                    <label>Kelas:</label>
                    <input type="text" id="customerClass" required>
                </div>
                <div class="form-group">
                    <label>No. Telefon:</label>
                    <input type="tel" id="customerPhone" required>
                </div>
                <button type="submit" class="beli-btn">Teruskan Pembayaran</button>
            </form>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closePaymentModal()">&times;</button>
            <h3>Pilih Bank</h3>
            <div class="bank-grid">
                <a href="https://www.maybank2u.com.my" target="_blank" class="bank-btn">
                    <img src="ads27/breyer-logo-maybank2.png" alt="Maybank">
                    <span>Maybank</span>
                </a>
                <a href="https://www.cimbclicks.com.my" target="_blank" class="bank-btn">
                    <img src="ads26/breyer-logo-cimb2.png" alt="CIMB">
                    <span>CIMB</span>
                </a>
                <a href="https://www.pbebank.com" target="_blank" class="bank-btn">
                    <img src="ads29/breyer-logo-publicbank2.png" alt="Public Bank">
                    <span>Public Bank</span>
                </a>
                <a href="https://www.hlb.com.my" target="_blank" class="bank-btn">
                    <img src="ads23/breyer-logo-hongleong2.png" alt="Hong Leong">
                    <span>Hong Leong</span>
                </a>
                <a href="https://www.ambank.com.my" target="_blank" class="bank-btn">
                    <img src="ads21/breyer-logo-ambank.png" alt="AmBank">
                    <span>AmBank</span>
                </a>
                <a href="https://www.muamalat.com.my" target="_blank" class="bank-btn">
                    <img src="ads28/breyer-logo-muamalat2.png" alt="Bank Muamalat">
                    <span>Bank Muamalat</span>
                </a>
                <a href="https://www.affinbank.com.my" target="_blank" class="bank-btn">
                    <img src="ads20/breyer-logo-affin2.png" alt="Affin Bank">
                    <span>Affin Bank</span>
                </a>
                <a href="https://www.agrobank.com.my" target="_blank" class="bank-btn">
                    <img src="ads25/breyer-logo-agrbank2.png" alt="Agrobank">
                    <span>Agrobank</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        const sizeModal = document.getElementById('sizeModal');
        const purchaseModal = document.getElementById('purchaseModal');
        const paymentModal = document.getElementById('paymentModal');
        let selectedSize = '';
        let currentQuantity = 1;
        let currentProduct = {};

        function openModal(type, price) {
            currentProduct = { type, price };
            document.getElementById('modalPrice').textContent = price;
            sizeModal.style.display = "block";
            resetSelections();
        }

        function selectSize(button, size) {
            document.querySelectorAll('.size-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            button.classList.add('selected');
            selectedSize = size;
        }

        function updateQuantity(change) {
            currentQuantity = Math.max(1, currentQuantity + change);
            document.getElementById('quantity').textContent = currentQuantity;
        }

        function resetSelections() {
            selectedSize = '';
            currentQuantity = 1;
            document.getElementById('quantity').textContent = '1';
            document.querySelectorAll('.size-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
        }

        function confirmPurchase() {
            if (!selectedSize) {
                alert('Sila pilih saiz terlebih dahulu');
                return;
            }
            document.getElementById('orderSize').value = selectedSize;
            document.getElementById('orderQuantity').value = currentQuantity;
            sizeModal.style.display = "none";
            purchaseModal.style.display = "block";
        }

        function closeModal() {
            sizeModal.style.display = "none";
            resetSelections();
        }

        function closePurchaseModal() {
            purchaseModal.style.display = "none";
        }

        function closePaymentModal() {
            paymentModal.style.display = "none";
        }

        document.getElementById('purchaseForm').onsubmit = function(e) {
            e.preventDefault();
            purchaseModal.style.display = "none";
            paymentModal.style.display = "block";
        }

        window.onclick = function(event) {
            if (event.target == sizeModal) {
                sizeModal.style.display = "none";
                resetSelections();
            }
        }
    </script>
</body>
</html>