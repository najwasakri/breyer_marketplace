<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Breyer Marketplace</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="admin/admin_styles.css"> <!-- Tambah baris ini -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e3f0ff 0%, #b3d8ff 100%);
            min-height: 100vh;
        }
        header {
            background: #003B95;
            color: #fff;
            padding: 30px 0 20px 0;
            box-shadow: 0 4px 18px rgba(0,59,149,0.08);
        }
        header h1 {
            margin: 0;
            font-size: 2.5rem;
            letter-spacing: 2px;
            text-align: center;
        }
        nav ul {
            display: flex;
            justify-content: center;
            gap: 30px;
            list-style: none;
            padding: 0;
            margin: 20px 0 0 0;
        }
        nav a {
            color: #fff;
            font-weight: 500;
            text-decoration: none;
            font-size: 1.1rem;
            transition: color 0.2s;
        }
        nav a:hover {
            color: #ffe45c;
        }
        .header-right {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 18px;
            margin-top: 18px;
        }
        .search-container {
            display: flex;
            align-items: center;
            background: #fff;
            border-radius: 25px;
            padding: 4px 12px;
            box-shadow: 0 2px 8px rgba(0,59,149,0.08);
        }
        .search-input {
            border: none;
            outline: none;
            padding: 8px 10px;
            border-radius: 20px;
            font-size: 1rem;
        }
        .search-button {
            background: none;
            border: none;
            color: #003B95;
            font-size: 1.2rem;
            cursor: pointer;
        }
        .settings-button {
            background: #ffe45c;
            border: none;
            border-radius: 50%;
            padding: 8px 10px;
            color: #003B95;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,59,149,0.08);
            transition: background 0.2s;
        }
        .settings-button:hover {
            background: #fff176;
        }
        main {
            padding: 40px 0;
        }
        section h2 {
            text-align: center;
            color: #003B95;
            font-size: 2rem;
            margin-bottom: 30px;
        }
        .product-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
        }
        .product-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0,59,149,0.10);
            width: 260px;
            padding: 22px 18px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid #e3f0ff;
        }
        .product-card:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 12px 32px rgba(0,59,149,0.18);
            border-color: #90caf9;
        }
        .product-image {
            width: 100%;
            height: 140px;
            object-fit: contain;
            margin-bottom: 18px;
        }
        .product-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #003B95;
            margin-bottom: 8px;
        }
        .product-price {
            color: #25D366;
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 12px;
        }
        .product-btn {
            background: #003B95;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 0;
            width: 100%;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 8px;
        }
        .product-btn:hover {
            background: #0056b3;
        }
        footer {
            background: #003B95;
            color: #fff;
            text-align: center;
            padding: 18px 0;
            font-size: 1rem;
            margin-top: 40px;
            border-radius: 0 0 18px 18px;
        }
        @media (max-width: 600px) {
            .product-list {
                flex-direction: column;
                gap: 18px;
            }
            .product-card {
                width: 95vw;
                max-width: 340px;
            }
            header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Breyer Marketplace</h1>
        <nav>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">Products</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>
        <div class="header-right">
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Cari...">
                <button class="search-button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <button class="settings-button">
                <i class="fas fa-cog"></i>
            </button>
        </div>
    </header>
    <main>
        <section>
            <h2>Featured Products</h2>
            <div class="product-list">
                <!-- Contoh produk, boleh tambah dinamik dari PHP -->
                <div class="product-card">
                    <img src="https://cdn-icons-png.flaticon.com/512/3075/3075977.png" alt="Product" class="product-image">
                    <div class="product-title">Baju Korporat</div>
                    <div class="product-price">RM85.00</div>
                    <button class="product-btn">Add to Cart</button>
                </div>
                <div class="product-card">
                    <img src="https://cdn-icons-png.flaticon.com/512/3075/3075977.png" alt="Product" class="product-image">
                    <div class="product-title">Baju T-Shirt Kolej</div>
                    <div class="product-price">RM28.00</div>
                    <button class="product-btn">Add to Cart</button>
                </div>
                <!-- Tambah produk lain di sini -->
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2023 Breyer Marketplace. All rights reserved.</p>
    </footer>
</body>
</html>