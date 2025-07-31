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
    <title>Culinary Products - Breyer</title>
    <style>
        /* Base styles */
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, 
                #FFFFFF 0%,
                #F5E6D3 30%,
                #D4B69B 70%,
                #8B6B4F 100%
            );
            font-family: Arial, sans-serif;
        }

        /* Container styles */
        .container {
            max-width: 1200px;
            margin: -40px auto 0;
            min-height: 90vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem 2rem 2rem;
        }

        /* Card and Grid styles */
        .course-grid {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            padding: 20px;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .course-card {
            background: linear-gradient(135deg, #ffffff 0%, #f5e6d3 100%);  /* Changed to white and light brown */
            border-radius: 12px;
            padding: 15px;
            width: 200px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .course-card img {
            width: 100%;
            height: 140px;  /* Reduced from 160px */
            object-fit: contain;
            margin-bottom: 12px;
        }

        .course-card h3 {
            font-size: 0.95rem;  /* Reduced from 1.1rem */
            margin: 10px 0 5px;
        }

        .course-card .price {
            font-size: 1.1rem;  /* Reduced from 1.6rem */
            margin: 8px 0;
        }

        /* Modal and Form styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background: linear-gradient(to bottom, #ffffff, #f0f7ff);  /* Reverted back to original white and light blue */
            padding: 25px;
            border-radius: 15px;
            width: 320px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            background: none;
            border: none;
            cursor: pointer;
            line-height: 1;
            padding: 0;
        }

        .close-btn:hover {
            color: #003B95;
        }

        /* Modal header styles */
        .modal h3 {
            color: #0047AB;
            font-size: 1.8rem;
            text-align: center;
            margin: 15px 0 25px;
            font-weight: bold;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        #modalPrice {
            color: #0047AB;
            font-weight: bold;
            font-size: 1.8rem;
        }

        .modal-header {
            text-align: center;
            margin-bottom: 25px;
            padding-top: 10px;
        }

        /* Size selection styles */
        .size-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 20px auto;
            width: 80%;
        }

        .size-btn {
            background: white;
            border: 2px solid #000;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .size-btn:hover {
            background: #f0f0f0;
        }

        .size-btn.selected {
            background: #000000;
            color: white;
            border-color: #000000;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1rem 0;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .quantity-btn:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }

        /* Form Input styles */
        .form-group {
            margin-bottom: 15px;
            width: 100%;
            max-width: 220px;
            margin-left: auto;
            margin-right: auto;
            text-align: left;
        }

        .form-group input,
        .form-group select {
            background: linear-gradient(to bottom, #ffffff, #f8fbff);
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
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

        .back-btn {
            position: fixed;
            top: 25px;
            left: 25px;
            display: flex;
            align-items: center;
            gap: 5px;
            background: #000000;  /* Changed to black */
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 100;
        }

        .back-btn:hover {
            background: #333333;  /* Changed to lighter black */
            transform: translateX(-5px);
            box-shadow: 0 3px 8px rgba(0,0,0,0.3);
        }

        /* Button styles */
        .beli-btn {
            background: #000000;
            color: white;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .beli-btn:hover {
            background: #333333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-btn">← Kembali</a>
        <div class="header">
            <h1>Culinary Products</h1>
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
            <div class="modal-header">
                <h3>HARGA : <span id="modalPrice"></span></h3>
            </div>
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
        // Reset variables
        const sizeModal = document.getElementById('sizeModal');
        const purchaseModal = document.getElementById('purchaseModal');
        const paymentModal = document.getElementById('paymentModal');
        let selectedSize = '';
        let currentQuantity = 1;
        let currentProduct = {};

        function openModal(type, price) {
            currentProduct = { type, price };
            document.getElementById('modalPrice').textContent = price;  // Set price dynamically
            sizeModal.style.display = "flex";  // Change to flex to center content
            centerModal(sizeModal);
            resetSelections();
            
            // Center the modal
            const modalContent = sizeModal.querySelector('.modal-content');
            modalContent.style.top = '50%';
            modalContent.style.left = '50%';
            modalContent.style.transform = 'translate(-50%, -50%)';
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

        // Add modal position fix
        function centerModal(modal) {
            if (modal) {
                modal.style.display = "flex";
                modal.style.alignItems = "center";
                modal.style.justifyContent = "center";
            }
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
            centerModal(purchaseModal);  // Center the purchase modal
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
            let finalQuantity = document.getElementById('orderQuantity').value;
            let finalSize = document.getElementById('orderSize').value;
            localStorage.setItem('orderQuantity', finalQuantity);
            localStorage.setItem('orderSize', finalSize);
            purchaseModal.style.display = "none";
            paymentModal.style.display = "block";
        }

        window.onclick = function(event) {
            if (event.target == sizeModal) {
                closeModal();
            }
            if (event.target == purchaseModal) {
                closePurchaseModal();
            }
            if (event.target == paymentModal) {
                closePaymentModal();
            }
        }
    </script>
</body>
</html>
