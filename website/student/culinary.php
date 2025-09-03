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
            height: 100vh;
            overflow: hidden;
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
            margin: 0 auto;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding-top: 80px;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Card and Grid styles */
        .course-grid {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 15px;
            flex-wrap: wrap;
            padding: 5px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            height: calc(100vh - 140px);
            overflow: hidden;
        }

        .course-card {
            background: linear-gradient(135deg, #ffffff 0%, #f9f5f1 100%);
            border-radius: 12px;
            padding: 15px;
            width: 200px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .course-card img {
            width: 100%;
            height: 160px;
            object-fit: contain;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .course-card h3 {
            font-size: 0.9rem;
            margin: 8px 0;
            color: #8B4513;
            font-weight: bold;
            line-height: 1.2;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .course-card .price {
            font-size: 1.3rem;
            margin: 8px 0;
            color: #25D366;
            font-weight: bold;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
        }

        .beli-btn {
            background: #8B4513;
            color: white;
            width: 100%;
            padding: 8px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .beli-btn:hover {
            background: #A0522D;
            transform: translateY(-1px);
        }

        .cart-btn {
            background: #25D366;
            color: white;
            width: 100%;
            padding: 7px;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .cart-btn:hover {
            background: #128C7E;
            transform: translateY(-1px);
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
            background: #8B4513;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0;
        }

        .back-btn::before {
            content: '';
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 8px 12px 8px 0;
            border-color: transparent white transparent transparent;
            transform: translateX(-2px);
        }

        .back-btn:hover {
            background: #A0522D;
            transform: translateX(-3px);
            box-shadow: 0 3px 8px rgba(0,0,0,0.3);
        }

        /* Button styles - Remove duplicate section */

        .cart-icon-page {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #003B95;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 24px;
            z-index: 100;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .cart-icon-page:hover {
            background: #002b70;
            transform: scale(1.1);
        }

        .cart-count-page {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        /* Custom Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.3);
            z-index: 9999;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            min-width: 300px;
            max-width: 400px;
        }

        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notification-icon {
            font-size: 24px;
            animation: bounce 0.6s ease-in-out;
        }

        .notification-text {
            flex: 1;
        }

        .notification-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .notification-message {
            font-size: 12px;
            opacity: 0.9;
        }

        .notification-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            margin-left: 8px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .notification-close:hover {
            opacity: 1;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        @media (max-width: 768px) {
            body {
                height: 100vh;
                overflow: hidden;
            }

            .container {
                height: 100vh;
                overflow: hidden;
            }

            .back-btn {
                width: 45px;
                height: 45px;
            }

            .back-btn::before {
                border-width: 7px 10px 7px 0;
            }

            .cart-icon-page {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .cart-count-page {
                width: 18px;
                height: 18px;
                font-size: 10px;
            }

            .header {
                padding-top: 80px;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .course-grid {
                padding: 10px;
                gap: 15px;
                height: calc(100vh - 140px);
                overflow: hidden;
            }

            .course-card {
                width: 160px;
                padding: 12px;
            }

            .course-card img {
                height: 130px;
            }

            .course-card h3 {
                font-size: 0.85rem;
            }

            .course-card .price {
                font-size: 1.1rem;
            }

            .beli-btn {
                font-size: 0.8rem;
                padding: 8px;
            }

            .cart-btn {
                font-size: 0.75rem;
                padding: 6px;
            }

            .notification {
                top: 10px;
                right: 10px;
                left: 10px;
                transform: translateY(-100px);
                min-width: auto;
                max-width: none;
            }

            .notification.show {
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            body {
                height: 100vh;
                overflow: hidden;
            }

            .container {
                height: 100vh;
                overflow: hidden;
            }

            .back-btn {
                width: 40px;
                height: 40px;
            }

            .back-btn::before {
                border-width: 6px 8px 6px 0;
            }

            .cart-icon-page {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .cart-count-page {
                width: 14px;
                height: 14px;
                font-size: 9px;
            }

            .header {
                padding-top: 60px;
            }

            .header h1 {
                font-size: 1.2rem;
            }

            .course-grid {
                padding: 8px;
                gap: 12px;
                height: calc(100vh - 100px);
                overflow: hidden;
            }

            .course-card {
                width: 140px;
                padding: 10px;
            }

            .course-card img {
                height: 110px;
            }

            .course-card h3 {
                font-size: 0.8rem;
            }

            .course-card .price {
                font-size: 1rem;
            }

            .beli-btn {
                font-size: 0.75rem;
                padding: 6px;
            }

            .cart-btn {
                font-size: 0.7rem;
                padding: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-btn" title="Kembali ke Dashboard"></a>
        <a href="cart.php" class="cart-icon-page" title="Troli Belanja">
            🛒
            <span class="cart-count-page" id="cartCountPage">0</span>
        </a>
        <div class="header">
            <h1 style="color: #8B4513; font-size: 1.8rem; text-align: center; margin-bottom: 15px; font-weight: bold;">Culinary Products</h1>
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
                echo '<h3 style="font-size: 0.9rem; text-transform: uppercase;">'.$product['type'].'</h3>';
                echo '<p class="price">'.$product['price'].'</p>';
                echo '<div class="button-group">';
                echo '<button class="beli-btn" onclick="openModal(\''.$product['type'].'\', \''.$product['price'].'\')">BELI</button>';
                echo '<button class="cart-btn" onclick="addToCart(\''.$product['type'].'\', \''.$product['price'].'\', \'CULINARY\')">🛒 ADD TO CART</button>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <!-- Custom Notification -->
    <div id="notification" class="notification">
        <div class="notification-content">
            <div class="notification-icon">🍳</div>
            <div class="notification-text">
                <div class="notification-title">Berjaya Ditambah!</div>
                <div class="notification-message" id="notification-message">Item telah ditambah ke troli</div>
            </div>
            <button class="notification-close" onclick="hideNotification()">&times;</button>
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

        // Cart functionality
        function addToCart(itemName, itemPrice, category) {
            let cartItems = JSON.parse(localStorage.getItem('cartItems') || '[]');
            const price = parseFloat(itemPrice.replace('RM', ''));
            
            const existingItemIndex = cartItems.findIndex(item => 
                item.name === itemName && item.category === category
            );
            
            if (existingItemIndex > -1) {
                cartItems[existingItemIndex].quantity += 1;
            } else {
                cartItems.push({
                    name: itemName,
                    price: price,
                    category: category,
                    quantity: 1,
                    image: getProductImage(itemName) // Add image path
                });
            }
            
            localStorage.setItem('cartItems', JSON.stringify(cartItems));
            updateCartCountPage();
            showNotification('Berjaya Ditambah!', `${itemName} telah ditambah ke troli belanja`, '🍳');
        }

        function getProductImage(itemName) {
            // Define product images for CULINARY category
            const productImages = {
                'BAJU CHEF': 'ads8/breyer-baju1.png',
                'BAJU T-SHIRT KOLEJ': 'ads8/breyer-baju1.png'
            };
            
            return productImages[itemName] || 'ads8/breyer-baju1.png';
        }

        function updateCartCountPage() {
            const cartItems = JSON.parse(localStorage.getItem('cartItems') || '[]');
            const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
            const cartCountElement = document.getElementById('cartCountPage');
            
            cartCountElement.textContent = totalItems;
            cartCountElement.style.display = totalItems > 0 ? 'flex' : 'none';
        }

        // Custom Notification Functions
        function showNotification(title, message, icon = '🍳') {
            const notification = document.getElementById('notification');
            const titleElement = notification.querySelector('.notification-title');
            const messageElement = notification.querySelector('.notification-message');
            const iconElement = notification.querySelector('.notification-icon');

            titleElement.textContent = title;
            messageElement.textContent = message;
            iconElement.textContent = icon;

            notification.classList.add('show');

            setTimeout(() => {
                hideNotification();
            }, 3000);
        }

        function hideNotification() {
            const notification = document.getElementById('notification');
            notification.classList.remove('show');
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateCartCountPage();
        });
    </script>
</body>
</html>
