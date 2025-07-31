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
    <title>F&B Category - Breyer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, 
                #FFE45C 0%,
                #FFE45C 30%,
                #4A90E2 70%,
                #003B95 100%
            );
        }

        .marketplace-grid {
            display: flex;
            justify-content: center;
            gap: 2rem;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .product-card {
            width: 280px; /* Set fixed width for cards */
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 59, 149, 0.1);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-info {
            padding: 1.5rem;
            background: linear-gradient(to bottom, white, #f8f9fa);
        }

        .product-title {
            font-size: 1.3rem;
            color: #003B95;
            margin-bottom: 0.8rem;
            font-weight: 600;
        }

        .product-price {
            font-size: 1.4rem;
            color: #e63946;
            font-weight: bold;
            margin-bottom: 0.8rem;
        }

        .product-seller {
            font-size: 1rem;
            color: #555;
            margin: 0.8rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .product-location {
            font-size: 0.95rem;
            color: #666;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .contact-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 1rem;
            background: #003B95;
            color: white;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .contact-button:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        .marketplace-title {
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
        }

        .marketplace-title h1 {
            font-size: 2.5rem;
            color: #003B95;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .marketplace-title p {
            color: #666;
            font-size: 1.2rem;
        }

        .header {
            position: relative;
            padding: 1rem 2rem;
        }

        .back-btn {
            position: fixed;
            left: 2rem;
            top: 2rem;
            padding: 0.8rem 1.5rem;
            background: #003B95;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .back-btn:hover {
            background: #0056b3;
            transform: translateX(-5px);
        }

        .back-btn::before {
            content: '←';
            font-size: 1.2rem;
        }

        .main-content {
            padding: 2rem;
        }

        /* Modal and Quantity Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }

        .quantity-btn {
            background: #000;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 5px;
            cursor: pointer;
        }

        .quantity {
            font-size: 18px;
            font-weight: bold;
        }

        .confirm-btn {
            background: #000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        /* Purchase Form Modal Styles */
        .purchase-form {
            text-align: left;
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .bank-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 15px;
            max-height: 400px;
            overflow-y: auto;
        }

        .bank-btn {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
            padding: 10px 15px;
            background: #E6F3FF;
            border: 2px solid #B3D9FF;
            border-radius: 10px;
            text-decoration: none;
            color: #004C99;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .bank-btn:hover {
            background: #CCE7FF;  /* Slightly darker pastel blue on hover */
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(179, 217, 255, 0.5);  /* Pastel blue shadow */
        }

        .bank-btn img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            margin: 0;
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-btn">Kembali</a>

    <div class="marketplace-title">
        <h1>LAIN-LAIN</h1>
        <p></p>
    </div>

    <main class="main-content">
        <div class="marketplace-grid">
            <div class="product-card">
                <img src="ads9/breyer-fail1.png" alt="File" class="product-image">
                <div class="product-info">
                    <h3 class="product-title">FILE</h3>
                    <button onclick="openModal('FILE', 10.00)" class="contact-button">
                        <span>BELI</span>
                    </button>
                </div>
            </div>

            <div class="product-card">
                <img src="ads10/breyer-lanyard2.png" alt="Lanyard" class="product-image">
                <div class="product-info">
                    <h3 class="product-title">LANYARD</h3>
                    <button onclick="openModal('LANYARD', 5.00)" class="contact-button">
                        <span>BELI</span>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Quantity Modal -->
    <div id="quantityModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle"></h3>
            <p>Harga: RM <span id="modalPrice"></span></p>
            <div class="quantity-selector">
                <button class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                <span id="quantity" class="quantity">1</span>
                <button class="quantity-btn" onclick="updateQuantity(1)">+</button>
            </div>
            <button class="confirm-btn" onclick="confirmPurchase()">Teruskan</button>
        </div>
    </div>

    <!-- Purchase Form Modal -->
    <div id="purchaseModal" class="modal">
        <div class="modal-content">
            <h3>Borang Pembelian</h3>
            <form id="purchaseForm" class="purchase-form">
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
                <div class="form-group">
                    <label>Kuantiti:</label>
                    <input type="text" id="orderQuantity" readonly>
                </div>
                <button type="submit" class="confirm-btn" onclick="showBankModal(event)">BELI</button>
            </form>
        </div>
    </div>

    <!-- Bank Selection Modal -->
    <div id="bankModal" class="modal">
        <div class="modal-content">
            <h3>Pilih Bank</h3>
            <div class="bank-grid">
                <a href="https://www.maybank2u.com.my" target="_blank" class="bank-btn">
                    <img src="ads27/breyer-logo-maybank2.png" alt="Maybank">
                    Maybank
                </a>
                <a href="https://www.cimbclicks.com.my" target="_blank" class="bank-btn">
                    <img src="ads26/breyer-logo-cimb2.png" alt="CIMB">
                    CIMB
                </a>
                <a href="https://www.pbebank.com" target="_blank" class="bank-btn">
                    <img src="ads29/breyer-logo-publicbank2.png" alt="Public Bank">
                    Public Bank
                </a>
                <a href="https://www.hlb.com.my" target="_blank" class="bank-btn">
                    <img src="ads23/breyer-logo-hongleong2.png" alt="Hong Leong">
                    Hong Leong
                </a>
                <a href="https://www.ambank.com.my" target="_blank" class="bank-btn">
                    <img src="ads21/breyer-logo-ambank.png" alt="AmBank">
                    AmBank
                </a>
                <a href="https://www.muamalat.com.my" target="_blank" class="bank-btn">
                    <img src="ads28/breyer-logo-muamalat2.png" alt="Bank Muamalat">
                    Bank Muamalat
                </a>
                <a href="https://www.affinbank.com.my" target="_blank" class="bank-btn">
                    <img src="ads20/breyer-logo-affin2.png" alt="Affin Bank">
                    Affin Bank
                </a>
                <a href="https://www.agrobank.com.my" target="_blank" class="bank-btn">
                    <img src="ads25/breyer-logo-agrbank2.png" alt="Agrobank">
                    Agrobank
                </a>
            </div>
        </div>
    </div>

    <script>
        let currentQuantity = 1;
        let currentPrice = 0;
        let currentProduct = '';

        function openModal(product, price) {
            currentProduct = product;
            currentPrice = price;
            currentQuantity = 1;
            document.getElementById('modalTitle').textContent = product;
            document.getElementById('modalPrice').textContent = price.toFixed(2);
            document.getElementById('quantity').textContent = '1';
            document.getElementById('quantityModal').style.display = 'flex';
        }

        function updateQuantity(change) {
            currentQuantity = Math.max(1, currentQuantity + change);
            document.getElementById('quantity').textContent = currentQuantity;
            document.getElementById('modalPrice').textContent = 
                (currentPrice * currentQuantity).toFixed(2);
        }

        function confirmPurchase() {
            document.getElementById('quantityModal').style.display = 'none';
            document.getElementById('purchaseModal').style.display = 'flex';
            document.getElementById('orderQuantity').value = currentQuantity;
            document.getElementById('totalPrice').value = 'RM ' + (currentPrice * currentQuantity).toFixed(2);
        }

        function showBankModal(e) {
            e.preventDefault();
            document.getElementById('purchaseModal').style.display = 'none';
            document.getElementById('bankModal').style.display = 'flex';
        }

        document.getElementById('purchaseForm').onsubmit = function(e) {
            e.preventDefault();
            alert('Pembelian anda telah berjaya!');
            document.getElementById('purchaseModal').style.display = 'none';
            // Reset form
            this.reset();
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('quantityModal')) {
                document.getElementById('quantityModal').style.display = 'none';
            }
            if (event.target == document.getElementById('purchaseModal')) {
                document.getElementById('purchaseModal').style.display = 'none';
            }
            if (event.target == document.getElementById('bankModal')) {
                document.getElementById('bankModal').style.display = 'none';
            }
        }
    </script>
</body>
</html>
