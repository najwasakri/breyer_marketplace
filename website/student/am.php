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
            padding: 0.8rem 1.5rem;
            background: #663399;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 100;
        }

        .back-btn:hover {
            background: #9370DB;
            transform: translateX(-5px);
        }

        .container {
            max-width: 1200px;
            margin: -40px auto 0;  /* Added negative top margin */
            min-height: 90vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center; /* Changed to center */
            padding: 4rem 2rem 2rem 2rem; /* Reduced top padding */
        }

        .course-grid {
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .course-card {
            border: none;
            padding: 20px;         /* Decreased from 25px */
            width: 220px;         /* Decreased from 250px */
            text-align: center;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }

        .course-card img {
            width: 100%;
            height: 160px;       /* Decreased from 180px */
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 6px;        /* Decreased from 8px */
        }

        .course-card h3 {
            font-size: 16px;     /* Decreased from 18px */
            color: #663399;
            margin: 15px 0 8px 0; /* Adjusted margins */
            letter-spacing: 0.5px;
        }

        .course-card p {
            font-size: 14px;
            margin: 8px 0;
            color: #1e3d59;
        }

        .course-card .price {
            font-weight: bold;
            color: #663399;
            font-size: 18px;     /* Decreased from 20px */
            margin: 12px 0;      /* Decreased from 15px */
        }

        .beli-btn {
            display: inline-block;
            background: #663399;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 160px;
            text-align: center;
            border: none;
            cursor: pointer;
        }

        .beli-btn:hover {
            background: #9370DB;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .header {
            text-align: center;
            width: 100%;
            margin: -20px 0 1.5rem 0; /* Added negative top margin */
            padding: 0.5rem;
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
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-btn">← Kembali</a>
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
                echo '<button class="beli-btn" onclick="openModal(\''.$product['type'].'\', \''.$product['price'].'\')">BELI</button>';
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
    </script>
</body>
</html>
