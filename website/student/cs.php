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
    <title>Computer Science - Breyer</title>
    <style>
        /* Base styles from dashboard.php */
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, 
                #D4E5FF 0%,    /* Deeper light blue */
                #ADD8FF 30%,   /* Deeper medium blue */
                #66A5FF 70%,   /* Saturated blue */
                #3B7DD3 100%   /* Deep royal blue */
            );
            font-family: Arial, sans-serif;
        }

        .product-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            padding: 20px;
            flex-wrap: wrap;
            max-width: 1000px;
            margin: 0 auto;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            width: 250px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card img {
            width: 100%;
            height: 220px;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .product-card h2 {
            color: #003B95;
            font-size: 1.4rem;
            margin: 10px 0;
        }

        .price {
            color: #003B95;
            font-size: 1.6rem;
            font-weight: bold;
            margin: 10px 0;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
        }

        .beli-btn {
            background: #003B95;
            color: white;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .beli-btn:hover {
            background: #002b70;
            transform: translateY(-2px);
        }

        .cart-btn {
            background: #003B95;
            color: white;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .cart-btn:hover {
            background: #002b70;
            transform: translateY(-2px);
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            color: #003B95;
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #003B95;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
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
            background: #002b70;
            transform: translateX(-3px);
        }

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

        .course-grid {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            padding: 15px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            min-height: calc(100vh - 200px);
        }

        .course-card {
            background: linear-gradient(135deg, #ffffff 0%, #f5f9ff 100%);
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
            color: #003B95;
            font-size: 0.95rem;
            margin: 8px 0;
            font-weight: bold;
            line-height: 1.2;
        }

        .price {
            color: #25D366;
            font-size: 1.3rem;
            font-weight: bold;
            margin: 8px 0;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
        }

        .beli-btn {
            background: #003B95;
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
            background: #002b70;
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
            background: #1DAA52;
            transform: translateY(-1px);
        }

        .header {
            text-align: center;
            margin-bottom: 1rem;
            color: #003B95;
            padding-top: 80px;
        }

        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
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
            background: linear-gradient(145deg, 
                #ffffff 0%,
                #f0f7ff 35%,
                #e6f2ff 65%,
                #ffffff 100%
            );
            box-shadow: 0 4px 20px rgba(73, 144, 226, 0.15);
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            width: 270px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(47, 128, 237, 0.15);
        }

        .form-group {
            margin-bottom: 15px;
            width: 100%;
            max-width: 220px;
            margin-left: auto;
            margin-right: auto;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #003B95;
            font-size: 14px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
            color: #333;
            background: linear-gradient(to bottom, #ffffff, #f8fbff);
            box-sizing: border-box;
        }

        .modal h3 {
            color: #003B95;
            font-size: 1.3rem;
            text-align: center;
            margin-bottom: 20px;
            padding-top: 5px;
        }

        #purchaseModal .modal-content {
            width: 280px;
            padding: 20px;
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        #purchaseModal .beli-btn {
            width: 80%;
            margin: 20px auto 10px;
            display: block;
        }

        .size-grid {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 15px 0;
        }

        .size-btn {
            width: 35px;
            height: 35px;
            border: 1px solid #ddd;
            background: linear-gradient(to bottom, #ffffff, #f5f9ff);
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .size-btn.selected {
            background: linear-gradient(145deg, #003B95, #4A90E2);
            color: white;
            border-color: #003B95;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 15px 0;
        }

        .quantity-btn {
            width: 25px;
            height: 25px;
            border: none;
            background: #003B95;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        /* Update bank grid styles */
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

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .back-btn {
                top: 15px;
                left: 15px;
                width: 45px;
                height: 45px;
            }

            .back-btn::before {
                border-width: 7px 10px 7px 0;
            }

            .cart-icon-page {
                top: 15px;
                right: 15px;
                width: 45px;
                height: 45px;
                font-size: 18px;
            }

            .cart-count-page {
                width: 16px;
                height: 16px;
                font-size: 10px;
                top: -3px;
                right: -3px;
            }

            .header {
                padding-top: 70px;
            }

            .header h1 {
                font-size: 1.4rem;
            }

            .course-grid {
                padding: 10px;
                gap: 15px;
                min-height: calc(100vh - 150px);
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

            .price {
                font-size: 1.1rem;
            }

            .button-group {
                gap: 5px;
            }

            .beli-btn {
                padding: 7px;
                font-size: 0.8rem;
            }

            .cart-btn {
                padding: 6px;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 480px) {
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
                min-height: calc(100vh - 120px);
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

            .price {
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

        /* Custom Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #003B95 0%, #002b70 100%);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 59, 149, 0.3);
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
            <h1>Computer System Products</h1>
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

            foreach ($products as $index => $product) {
                echo '<div class="course-card">';
                echo '<img src="'.$product['image'].'" alt="'.$product['type'].'">';
                echo '<h3 style="font-size: 1.1rem; text-transform: uppercase;">'.$product['type'].'</h3>';
                echo '<p class="price">'.$product['price'].'</p>';
                echo '<div class="button-group">';
                echo '<button class="beli-btn" onclick="openModal(\''.$product['type'].'\', \''.$product['price'].'\')">BELI</button>';
                echo '<button class="cart-btn" onclick="addToCart(\''.$product['type'].'\', \''.$product['price'].'\', \'CS\')">🛒 ADD TO CART</button>';
                echo '</div>';
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

    <!-- Custom Notification -->
    <div id="notification" class="notification">
        <div class="notification-content">
            <div class="notification-icon">💻</div>
            <div class="notification-text">
                <div class="notification-title">Berjaya Ditambah!</div>
                <div class="notification-message" id="notification-message">Item telah ditambah ke troli</div>
            </div>
            <button class="notification-close" onclick="hideNotification()">&times;</button>
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

        // Cart functionality
        function addToCart(itemName, itemPrice, category) {
            // Get existing cart items
            let cartItems = JSON.parse(localStorage.getItem('cartItems') || '[]');
            
            // Extract numeric price from RM format
            const price = parseFloat(itemPrice.replace('RM', ''));
            
            // Check if item already exists in cart
            const existingItemIndex = cartItems.findIndex(item => 
                item.name === itemName && item.category === category
            );
            
            if (existingItemIndex > -1) {
                // Item exists, increase quantity
                cartItems[existingItemIndex].quantity += 1;
            } else {
                // Add new item to cart with image
                cartItems.push({
                    name: itemName,
                    price: price,
                    category: category,
                    quantity: 1,
                    image: getProductImage(itemName) // Add image path
                });
            }
            
            // Save to localStorage
            localStorage.setItem('cartItems', JSON.stringify(cartItems));
            
            // Update cart count
            updateCartCountPage();
            
            // Show custom notification
            showNotification('Berjaya Ditambah!', `${itemName} telah ditambah ke troli belanja`, '💻');
        }

        function getProductImage(itemName) {
            // Define product images for CS category
            const productImages = {
                'BAJU KORPORAT': 'ads8/breyer-baju1.png',
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
        function showNotification(title, message, icon = '💻') {
            const notification = document.getElementById('notification');
            const titleElement = notification.querySelector('.notification-title');
            const messageElement = notification.querySelector('.notification-message');
            const iconElement = notification.querySelector('.notification-icon');

            titleElement.textContent = title;
            messageElement.textContent = message;
            iconElement.textContent = icon;

            notification.classList.add('show');

            // Auto hide after 3 seconds
            setTimeout(() => {
                hideNotification();
            }, 3000);
        }

        function hideNotification() {
            const notification = document.getElementById('notification');
            notification.classList.remove('show');
        }

        // Initialize cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCountPage();
        });

        document.getElementById('purchaseForm').onsubmit = function(e) {
            e.preventDefault();
            let finalQuantity = document.getElementById('orderQuantity').value;
            let finalSize = document.getElementById('orderSize').value;
            // Store values before moving to payment
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
