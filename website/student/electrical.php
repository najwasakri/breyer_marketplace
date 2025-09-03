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
            height: 100vh;
            overflow: hidden;
            background: #0a1f4d;  /* Changed from gradient to solid navy */
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding-top: 60px;
            box-sizing: border-box;
            overflow: hidden;
        }

        .course-grid {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
            padding: 10px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            height: calc(100vh - 140px);
            overflow: hidden;
        }

        .course-card {
            background: white;
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
            color: #ff6347;
            font-weight: bold;
            line-height: 1.2;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .price {
            font-size: 1.3rem;
            margin: 8px 0;
            color: #25D366;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin: 0.5rem 0;
            color: #ff6347;
        }

        .header h1 {
            font-size: 2.8rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #ff6347;
            letter-spacing: 1px;
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #ff6347;
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
            background: #ff4726;
            transform: translateX(-3px);
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

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
        }

        .beli-btn {
            background: #ff6347;
            color: white;
            width: 100%;
            padding: 8px 0;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
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

        .cart-btn {
            background: #25D366;
            color: white;
            width: 100%;
            padding: 7px;
            border: none;
            border-radius: 8px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .cart-btn:hover {
            background: #128C7E;
            transform: translateY(-2px);
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

            .course-grid {
                padding: 10px;
                gap: 15px;
                height: calc(100vh - 160px);
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

            .price {
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
            .back-btn {
                width: 40px;
                height: 40px;
            }

            .back-btn::before {
                border-width: 6px 8px 6px 0;
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
                echo '<h3 style="font-size: 0.9rem; text-transform: uppercase;">'.$product['type'].'</h3>';
                echo '<p class="price">'.$product['price'].'</p>';
                echo '<div class="button-group">';
                echo '<button class="beli-btn" onclick="openModal(\''.$product['type'].'\', \''.$product['price'].'\')">BELI</button>';
                echo '<button class="cart-btn" onclick="addToCart(\''.$product['type'].'\', \''.$product['price'].'\', \'ELECTRICAL\')">🛒 ADD TO CART</button>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <!-- Custom Notification -->
    <div id="notification" class="notification">
        <div class="notification-content">
            <div class="notification-icon">⚡</div>
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
            showNotification('Berjaya Ditambah!', `${itemName} telah ditambah ke troli belanja`, '⚡');
        }

        function getProductImage(itemName) {
            // Define product images for ELECTRICAL category
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
        function showNotification(title, message, icon = '⚡') {
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