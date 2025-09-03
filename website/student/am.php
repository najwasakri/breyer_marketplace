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
    <title>Account Management - Breyer</title>
    <style>
        /* Base styles */
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, 
                #E6E6FA 0%,    /* Lavender */
                #D8BFD8 40%,   /* Pastel purple */
                #9370DB 70%,   /* Medium purple */
                #663399 100%   /* Rebecca purple */
            );
            font-family: Arial, sans-serif;
        }

        .back-btn {
            position: fixed;
            left: 2rem;
            top: 2rem;
            background: #663399;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
            background: #9370DB;
            transform: translateX(-3px);
        }

        .cart-icon-page {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: #003b96;
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
            background: #002B70;
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 1rem;
            color: #663399;
            padding-top: 80px;
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
            border: none;
            padding: 15px;
            width: 200px;
            text-align: center;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }

        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .course-card img {
            width: 100%;
            height: 160px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 6px;
            margin-bottom: 10px;
        }

        .course-card h3 {
            font-size: 0.95rem;
            color: #663399;
            margin: 8px 0;
            letter-spacing: 0.5px;
            font-weight: bold;
            line-height: 1.2;
        }

        .course-card p {
            font-size: 14px;
            margin: 8px 0;
            color: #1e3d59;
        }

        .course-card .price {
            font-weight: bold;
            color: #25D366;
            font-size: 1.3rem;
            margin: 8px 0;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
        }

        .beli-btn {
            background: #663399;
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
            background: #9370DB;
        }

        .cart-btn {
            background: #25d366;
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
            background: #25d366;
            transform: translateY(-1px);
        }

        .header {
            text-align: center;
            margin-bottom: 1rem;
            color: #663399;
            padding-top: 80px;
        }

        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 18px;
            width: 280px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .size-selector h3 {
            text-align: center;
            margin-bottom: 20px;  /* Reduced from 25px */
            font-size: 20px;     /* Reduced from 24px */
            color: #663399;
            font-weight: bold;
            padding: 8px 0;      /* Reduced from 10px */
        }

        .size-grid {
            display: flex;
            justify-content: center;
            gap: 10px;           /* Reduced from 12px */
            margin: 15px 0;      /* Reduced from 25px */
        }

        .size-btn {
            width: 35px;         /* Reduced from 40px */
            height: 35px;        /* Reduced from 40px */
            font-size: 13px;     /* Reduced from 14px */
        }

        .size-btn.selected {
            background: #663399;
            color: white;
            border-color: #663399;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            cursor: pointer;
            color: #663399;
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
            background: #663399;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .quantity-display {
            font-size: 18px;
            min-width: 40px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
            width: 100%;
            max-width: 220px;       /* Decreased from 260px for consistency */
            margin-left: auto;
            margin-right: auto;
            text-align: center;     /* Added for centering */
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;     /* Decreased from 8px */
            color: #333;
            font-weight: normal;
            font-size: 14px;        /* Decreased from 15px */
            text-align: left;
        }

        .form-group select,
        .form-group input {
            width: 100%;            /* Changed to use full width */
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
            box-sizing: border-box; /* Added to include padding in width */
        }

        .modal-content .beli-btn {
            display: block;
            width: 60%;  /* Reduced from 80% */
            margin: 15px auto;
            font-size: 16px;  /* Reduced from 18px */
            padding: 8px 0;   /* Reduced from 12px */
        }

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

        #purchaseModal .modal-content {
            width: 270px;           /* Added specific width for purchase modal */
            padding: 18px;
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

            .course-card .price {
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
            <h1>Administration Management Products</h1>
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
                echo '<h3>'.$product['type'].'</h3>';
                echo '<p class="price">'.$product['price'].'</p>';
                echo '<div class="button-group">';
                echo '<button class="beli-btn" onclick="openModal(\''.$product['type'].'\', \''.$product['price'].'\')">BELI</button>';
                echo '<button class="cart-btn" onclick="addToCart(\''.$product['type'].'\', \''.$product['price'].'\', \'AM\')">🛒 ADD TO CART</button>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <!-- Size Selection Modal -->
    <div id="sizeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="size-selector">
                <h3>HARGA: <span id="selectedPrice"></span></h3>
                <div class="size-grid">
                    <button class="size-btn" data-size="S">S</button>
                    <button class="size-btn" data-size="M">M</button>
                    <button class="size-btn" data-size="L">L</button>
                    <button class="size-btn" data-size="XL">XL</button>
                </div>
                <div class="quantity-selector">
                    <button class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                    <span class="quantity-display">1</span>
                    <button class="quantity-btn" onclick="updateQuantity(1)">+</button>
                </div>
                <button class="beli-btn" onclick="confirmPurchase()">BELI</button>
            </div>
        </div>
    </div>

    <!-- Purchase Form Modal -->
    <div id="purchaseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePurchaseModal()">&times;</span>
            <h2 style="color: #1e3d59; text-align: center; margin-bottom: 25px;">Borang Pembelian</h2>
            <form id="purchaseForm" onsubmit="submitPurchase(event)">
                <div class="form-group">
                    <label>Saiz:</label>
                    <select id="size" required>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Kuantiti:</label>
                    <input type="number" id="quantity" min="1" value="1" required>
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

                <button type="submit" class="beli-btn">TERUSKAN PEMBAYARAN</button>
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
            <div class="notification-icon">⚙️</div>
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

        function openModal(productType, productPrice) {
            currentProduct = { type: productType, price: productPrice };
            document.getElementById('selectedPrice').textContent = productPrice;
            sizeModal.style.display = "block";
            resetSelections();
        }

        function resetSelections() {
            selectedSize = '';
            currentQuantity = 1;
            document.querySelector('.quantity-display').textContent = '1';
            document.querySelectorAll('.size-btn').forEach(btn => btn.classList.remove('selected'));
        }

        function closeModal() {
            sizeModal.style.display = "none";
        }

        function updateQuantity(change) {
            currentQuantity = Math.max(1, currentQuantity + change);
            document.querySelector('.quantity-display').textContent = currentQuantity;
        }

        document.querySelectorAll('.size-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                selectedSize = this.dataset.size;
            });
        });

        function confirmPurchase() {
            if (!selectedSize) {
                alert('Sila pilih saiz');
                return;
            }
            sizeModal.style.display = "none";
            purchaseModal.style.display = "block";
        }

        function closePurchaseModal() {
            purchaseModal.style.display = "none";
        }

        function submitPurchase(e) {
            e.preventDefault();
            purchaseModal.style.display = "none";
            paymentModal.style.display = "block";
        }

        function closePaymentModal() {
            paymentModal.style.display = "none";
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
            showNotification('Berjaya Ditambah!', `${itemName} telah ditambah ke troli belanja`, '⚙️');
        }

        function getProductImage(itemName) {
            // Define product images for AM category
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
        function showNotification(title, message, icon = '⚙️') {
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

        // Initialize cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCountPage();
        });
    </script>
</body>
</html>
