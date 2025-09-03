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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Troli Belanja - Breyer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, 
                #FFE45C 0%,
                #FFE45C 30%,
                #4A90E2 70%,
                #003B95 100%
            );
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            padding: 20px 0;
            position: relative;
            overflow-x: hidden;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 30%, rgba(255, 228, 92, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(74, 144, 226, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(0, 59, 149, 0.05) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                repeating-linear-gradient(
                    45deg,
                    transparent,
                    transparent 40px,
                    rgba(255, 255, 255, 0.02) 40px,
                    rgba(255, 255, 255, 0.02) 80px
                );
            animation: patternMove 30s linear infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes patternMove {
            0% {
                transform: translate(0, 0) rotate(0deg);
            }
            100% {
                transform: translate(-40px, -40px) rotate(360deg);
            }
        }

        /* Floating Decorative Elements */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.08;
            animation: float 20s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            background: #FFE45C;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 60px;
            height: 60px;
            background: #4A90E2;
            top: 70%;
            left: 80%;
            animation-delay: 7s;
        }

        .shape:nth-child(3) {
            width: 40px;
            height: 40px;
            background: #003B95;
            top: 30%;
            left: 70%;
            animation-delay: 3s;
        }

        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            background: #FFE45C;
            top: 80%;
            left: 20%;
            animation-delay: 10s;
        }

        .shape:nth-child(5) {
            width: 50px;
            height: 50px;
            background: #4A90E2;
            top: 20%;
            left: 90%;
            animation-delay: 5s;
        }

        .shape:nth-child(6) {
            width: 70px;
            height: 70px;
            background: #003B95;
            top: 60%;
            left: 15%;
            animation-delay: 12s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg) scale(1);
                opacity: 0.08;
            }
            25% {
                transform: translateY(-30px) rotate(90deg) scale(1.1);
                opacity: 0.15;
            }
            50% {
                transform: translateY(-60px) rotate(180deg) scale(0.9);
                opacity: 0.12;
            }
            75% {
                transform: translateY(-30px) rotate(270deg) scale(1.05);
                opacity: 0.18;
            }
        }

        /* Particle Effects */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #FFE45C;
            border-radius: 50%;
            opacity: 0.6;
            animation: particleMove 25s infinite linear;
        }

        .particle:nth-child(even) {
            background: #4A90E2;
            animation-duration: 30s;
        }

        .particle:nth-child(3n) {
            background: #003B95;
            animation-duration: 20s;
        }

        @keyframes particleMove {
            0% {
                transform: translateY(100vh) translateX(0) scale(0);
                opacity: 0;
            }
            10% {
                opacity: 0.6;
                transform: scale(1);
            }
            90% {
                opacity: 0.6;
                transform: scale(1);
            }
            100% {
                transform: translateY(-10vh) translateX(50px) scale(0);
                opacity: 0;
            }
        }

        /* Glow Effects */
        .header::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 228, 92, 0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite alternate;
            z-index: -1;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            100% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(255, 228, 92, 0.05) 0%, 
                rgba(74, 144, 226, 0.05) 50%,
                rgba(0, 59, 149, 0.05) 100%
            );
            pointer-events: none;
            z-index: -1;
        }

        .header h1 {
            color: #003B95;
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }

        .btn-back {
            background: #003B95;
            color: white;
            padding: 15px;
            border-radius: 50%;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            box-shadow: 0 4px 15px rgba(0, 59, 149, 0.3);
            overflow: hidden;
            font-size: 0;
        }

        .btn-back::before {
            content: '';
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 8px 12px 8px 0;
            border-color: transparent white transparent transparent;
            transform: translateX(-2px);
        }

        .btn-back:hover {
            background: #002b70;
            transform: translateX(-3px);
        }

        .cart-content {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            min-height: 400px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .cart-content:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 59, 149, 0.2);
        }

        .cart-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(255, 228, 92, 0.03) 0%, 
                rgba(74, 144, 226, 0.03) 50%,
                rgba(0, 59, 149, 0.03) 100%
            );
            pointer-events: none;
            z-index: -1;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart img {
            width: 120px;
            height: 120px;
            opacity: 0.5;
            margin-bottom: 20px;
        }

        .empty-cart h2 {
            color: #666;
            margin-bottom: 10px;
            font-size: 1.5rem;
        }

        .empty-cart p {
            color: #888;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        .btn-shop {
            background: #25D366;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-shop:hover {
            background: #128C7E;
            transform: translateY(-2px);
        }

        .cart-items {
            margin-bottom: 30px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
            border-radius: 12px;
            margin-bottom: 10px;
            position: relative;
            overflow: hidden;
        }

        .cart-item:hover {
            background: linear-gradient(135deg, rgba(255, 228, 92, 0.1) 0%, rgba(74, 144, 226, 0.1) 100%);
            transform: translateX(5px);
            box-shadow: -5px 0 15px rgba(0, 59, 149, 0.1);
        }

        .cart-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: linear-gradient(to bottom, #FFE45C 0%, #4A90E2 100%);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .cart-item:hover::before {
            transform: scaleY(1);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            background: #f0f0f0;
            border-radius: 8px;
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            overflow: hidden;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .item-details {
            flex: 1;
            margin-right: 20px;
        }

        .item-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #003B95;
            margin-bottom: 5px;
        }

        .item-category {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .item-price {
            color: #25D366;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-right: 20px;
        }

        .qty-btn {
            background: #90caf9;
            color: #003B95;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .qty-btn:hover {
            background: #bbdefb;
            transform: scale(1.1);
        }

        .qty-display {
            background: white;
            border: 2px solid #90caf9;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: bold;
            color: #003B95;
            min-width: 50px;
            text-align: center;
        }

        .remove-btn {
            background: #ff4444;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: #cc0000;
            transform: scale(1.05);
        }

        .cart-summary {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border: 2px solid #90caf9;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .summary-row:last-child {
            margin-bottom: 0;
            padding-top: 15px;
            border-top: 2px solid #90caf9;
            font-size: 1.3rem;
            font-weight: bold;
            color: #003B95;
        }

        .checkout-section {
            text-align: center;
            margin-top: 30px;
        }

        .btn-checkout {
            background: #003B95;
            color: white;
            padding: 15px 40px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            display: inline-block;
            border: none;
            cursor: pointer;
        }

        .btn-checkout:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,59,149,0.3);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }

            .header {
                flex-direction: row;
                justify-content: flex-start;
                gap: 15px;
                text-align: left;
                padding: 20px;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .cart-content {
                padding: 20px 15px;
            }

            .cart-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .item-image {
                align-self: center;
                margin-right: 0;
            }

            .item-details {
                text-align: center;
                margin-right: 0;
            }

            .quantity-controls {
                align-self: center;
                margin-right: 0;
            }

            .remove-btn {
                align-self: center;
            }

            .summary-row {
                font-size: 1rem;
            }

            .summary-row:last-child {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 480px) {
            .header {
                gap: 12px;
                padding: 15px;
            }

            .header h1 {
                font-size: 1.3rem;
            }

            .btn-back {
                padding: 12px;
                width: 40px;
                height: 40px;
            }

            .btn-back::before {
                border-width: 6px 8px 6px 0;
            }

            .cart-content {
                padding: 15px 10px;
            }

            .item-name {
                font-size: 1.1rem;
            }

            .qty-btn {
                width: 30px;
                height: 30px;
                font-size: 1rem;
            }

            .qty-display {
                padding: 6px 12px;
                min-width: 40px;
            }

            .btn-checkout {
                width: 100%;
                padding: 15px;
                font-size: 1.1rem;
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

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Mobile responsive for notification */
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

            .notification-content {
                gap: 10px;
            }

            .notification-icon {
                font-size: 20px;
            }

            .notification-title {
                font-size: 13px;
            }

            .notification-message {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Decorative Elements -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Particle Effects -->
    <div class="particles">
        <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="left: 20%; animation-delay: 2s;"></div>
        <div class="particle" style="left: 30%; animation-delay: 4s;"></div>
        <div class="particle" style="left: 40%; animation-delay: 1s;"></div>
        <div class="particle" style="left: 50%; animation-delay: 3s;"></div>
        <div class="particle" style="left: 60%; animation-delay: 5s;"></div>
        <div class="particle" style="left: 70%; animation-delay: 2.5s;"></div>
        <div class="particle" style="left: 80%; animation-delay: 4.5s;"></div>
        <div class="particle" style="left: 90%; animation-delay: 1.5s;"></div>
        <div class="particle" style="left: 15%; animation-delay: 6s;"></div>
        <div class="particle" style="left: 25%; animation-delay: 3.5s;"></div>
        <div class="particle" style="left: 35%; animation-delay: 0.5s;"></div>
    </div>

    <div class="container">
        <div class="header">
            <a href="dashboard.php" class="btn-back" title="Kembali ke Dashboard"></a>
            <h1>🛒 Troli Belanja</h1>
        </div>

        <div class="cart-content">
            <div id="cart-items-container">
                <!-- Cart items will be loaded here by JavaScript -->
            </div>

            <div id="cart-summary" style="display: none;">
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Jumlah Item:</span>
                        <span id="total-items">0</span>
                    </div>
                    <div class="summary-row">
                        <span>Jumlah Harga:</span>
                        <span id="total-price">RM 0.00</span>
                    </div>
                </div>
                
                <div class="checkout-section">
                    <button class="btn-checkout" onclick="proceedToCheckout()">
                        Proceed to Checkout
                    </button>
                </div>
            </div>

            <div id="empty-cart" style="display: none;">
                <div class="empty-cart">
                    <div style="font-size: 4rem; margin-bottom: 20px;">🛒</div>
                    <h2>Troli Anda Kosong</h2>
                    <p>Belum ada item dalam troli belanja anda</p>
                    <a href="dashboard.php" class="btn-shop">Mula Berbelanja</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Notification -->
    <div id="notification" class="notification">
        <div class="notification-content">
            <div class="notification-icon">🛒</div>
            <div class="notification-text">
                <div class="notification-title">Berjaya Ditambah!</div>
                <div class="notification-message" id="notification-message">Item telah ditambah ke troli</div>
            </div>
            <button class="notification-close" onclick="hideNotification()">&times;</button>
        </div>
    </div>

    <script>
        let cartItems = JSON.parse(localStorage.getItem('cartItems') || '[]');

        function loadCartItems() {
            const container = document.getElementById('cart-items-container');
            const summarySection = document.getElementById('cart-summary');
            const emptySection = document.getElementById('empty-cart');

            if (cartItems.length === 0) {
                container.innerHTML = '';
                summarySection.style.display = 'none';
                emptySection.style.display = 'block';
                return;
            }

            emptySection.style.display = 'none';
            summarySection.style.display = 'block';

            container.innerHTML = '<div class="cart-items">' + cartItems.map((item, index) => `
                <div class="cart-item">
                    <div class="item-image">
                        ${getItemImage(item.name, item.category, item.image)}
                    </div>
                    <div class="item-details">
                        <div class="item-name">${item.name}</div>
                        <div class="item-category">${item.category}</div>
                        <div class="item-price">RM ${parseFloat(item.price).toFixed(2)}</div>
                    </div>
                    <div class="quantity-controls">
                        <button class="qty-btn" onclick="updateQuantity(${index}, -1)">−</button>
                        <div class="qty-display">${item.quantity}</div>
                        <button class="qty-btn" onclick="updateQuantity(${index}, 1)">+</button>
                    </div>
                    <button class="remove-btn" onclick="removeItem(${index})">Buang</button>
                </div>
            `).join('') + '</div>';

            updateSummary();
        }

        function getItemImage(itemName, category, savedImage = null) {
            // Use saved image path if available
            if (savedImage) {
                return `<img src="${savedImage}" alt="${itemName}" onerror="this.style.display='none'; this.parentNode.innerHTML='${getItemIcon(category)}';">`;
            }

            // Define product images based on item name and category
            const productImages = {
                // CS Category
                'BAJU KORPORAT': 'ads8/breyer-baju1.png',
                'BAJU T-SHIRT KOLEJ': 'ads8/breyer-baju1.png',
                
                // AM Category
                'BAJU KORPORAT AM': 'ads8/breyer-baju1.png',
                'BAJU T-SHIRT KOLEJ AM': 'ads8/breyer-baju1.png',
                
                // CULINARY Category
                'BAJU CHEF': 'ads8/breyer-baju1.png',
                'BAJU T-SHIRT KOLEJ CULINARY': 'ads8/breyer-baju1.png',
                
                // ELECTRICAL Category
                'BAJU KORPORAT ELECTRICAL': 'ads8/breyer-baju1.png',
                'BAJU T-SHIRT KOLEJ ELECTRICAL': 'ads8/breyer-baju1.png',
                
                // LAIN-LAIN Category
                'FILE': 'ads9/breyer-fail1.png',
                'LANYARD': 'ads10/breyer-lanyard2.png'
            };

            // Get image path for the specific item
            let imagePath = productImages[itemName];
            
            // If no specific image, use category defaults
            if (!imagePath) {
                if (itemName.includes('KORPORAT')) {
                    imagePath = 'ads8/breyer-baju1.png';
                } else if (itemName.includes('T-SHIRT') || itemName.includes('SHIRT')) {
                    imagePath = 'ads8/breyer-baju1.png';
                } else if (itemName.includes('FILE')) {
                    imagePath = 'ads9/breyer-fail1.png';
                } else if (itemName.includes('LANYARD')) {
                    imagePath = 'ads10/breyer-lanyard2.png';
                } else {
                    // Default fallback
                    imagePath = 'ads8/breyer-baju1.png';
                }
            }

            return `<img src="${imagePath}" alt="${itemName}" onerror="this.style.display='none'; this.parentNode.innerHTML='${getItemIcon(category)}';">`;
        }

        function getItemIcon(category) {
            const icons = {
                'CS': '💻',
                'AM': '⚙️',
                'CULINARY': '🍳',
                'ELECTRICAL': '⚡',
                'LAIN-LAIN': '📦'
            };
            return icons[category] || '📦';
        }

        function updateQuantity(index, change) {
            if (cartItems[index]) {
                cartItems[index].quantity += change;
                if (cartItems[index].quantity <= 0) {
                    cartItems.splice(index, 1);
                }
                saveCart();
                loadCartItems();
            }
        }

        function removeItem(index) {
            if (confirm('Adakah anda pasti untuk membuang item ini?')) {
                cartItems.splice(index, 1);
                saveCart();
                loadCartItems();
            }
        }

        function updateSummary() {
            const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
            const totalPrice = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            document.getElementById('total-items').textContent = totalItems;
            document.getElementById('total-price').textContent = 'RM ' + totalPrice.toFixed(2);
        }

        function saveCart() {
            localStorage.setItem('cartItems', JSON.stringify(cartItems));
            // Update cart count in parent window/dashboard if available
            if (window.opener && window.opener.updateCartCount) {
                window.opener.updateCartCount();
            }
        }

        function proceedToCheckout() {
            if (cartItems.length === 0) {
                alert('Troli anda kosong!');
                return;
            }

            // Store checkout data
            localStorage.setItem('checkoutItems', JSON.stringify(cartItems));
            
            // Redirect to checkout page (you can create this page)
            alert('Checkout functionality akan dilaksanakan. Item telah disimpan untuk pemprosesan.');
            
            // For now, clear cart after "checkout"
            if (confirm('Simulasi checkout selesai. Kosongkan troli?')) {
                cartItems = [];
                saveCart();
                loadCartItems();
            }
        }

        // Add sample items for testing (remove this in production)
        function addSampleItems() {
            const sampleItems = [
                { name: 'T-shirt Kolej', category: 'CS', price: 25.00, quantity: 1 },
                { name: 'Baju Chef', category: 'CULINARY', price: 45.00, quantity: 2 },
                { name: 'Lanyard', category: 'LAIN-LAIN', price: 8.00, quantity: 1 }
            ];
            
            cartItems = sampleItems;
            saveCart();
            loadCartItems();
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadCartItems();
            
            // Add sample items button for testing (remove in production)
            if (cartItems.length === 0) {
                const container = document.getElementById('cart-items-container');
                container.innerHTML = `
                    <div style="text-align: center; padding: 20px;">
                        <button onclick="addSampleItems()" style="background: #25D366; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
                            Tambah Item Contoh (Untuk Testing)
                        </button>
                    </div>
                `;
            }
        });

        // Custom Notification Functions
        function showNotification(title, message, icon = '🛒') {
            const notification = document.getElementById('notification');
            const titleElement = notification.querySelector('.notification-title');
            const messageElement = notification.querySelector('.notification-message');
            const iconElement = notification.querySelector('.notification-icon');

            titleElement.textContent = title;
            messageElement.textContent = message;
            iconElement.textContent = icon;

            notification.classList.add('show');

            // Auto hide after 4 seconds
            setTimeout(() => {
                hideNotification();
            }, 4000);
        }

        function hideNotification() {
            const notification = document.getElementById('notification');
            notification.classList.remove('show');
        }

        // Global function for other pages to use
        window.showAddToCartNotification = function(itemName, category) {
            const categoryIcons = {
                'CS': '💻',
                'AM': '⚙️', 
                'CULINARY': '🍳',
                'ELECTRICAL': '⚡',
                'LAIN-LAIN': '📦'
            };
            
            const icon = categoryIcons[category] || '🛒';
            showNotification(
                'Berjaya Ditambah!',
                `${itemName} telah ditambah ke troli belanja`,
                icon
            );
        };
    </script>
</body>
</html>
