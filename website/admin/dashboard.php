<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: #f0f0f0;
            padding: 20px 0;
        }

        .menu-title {
            background: #FFE45C;
            padding: 15px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .menu-items {
            list-style: none;
        }

        .menu-items li {
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            transition: background 0.3s;
        }

        .menu-items li:hover {
            background: #e0e0e0;
        }

        .menu-items li a {
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .menu-items li a::after {
            content: '→';
            position: absolute;
            right: 10px;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .menu-items li:hover a::after {
            opacity: 1;
            right: 0;
        }

        .menu-items li.active {
            background: #FFE45C;
        }

        .menu-items li.active a {
            color: #000;
            font-weight: bold;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 20px;
            background: #fff;
        }

        /* Profile Section */
        .profile-section {
            background: #FFE45C;
            padding: 15px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
        }

        .profile-icon {
            width: 40px;
            height: 40px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Dashboard Stats Styles */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 24px;
            font-weight: bold;
            color: #FFE45C;
        }

        /* Slider Styles */
        .slider-container {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .slider {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        .slide {
            min-width: 100%;
            height: 300px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .slide-content {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: white;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }

        .slider-dots {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .dot {
            width: 12px;
            height: 12px;
            background: rgba(255,255,255,0.5);
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .dot.active {
            background: #FFE45C;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="menu-title">MENU UTAMA</div>
        <ul class="menu-items">
            <li class="active"><a href="dashboard.php">HALAMAN UTAMA</a></li>
            <li><a href="manage_courses.php">SENARAI KURSUS</a></li>
            <li><a href="manage_inventory.php">INVENTORI</a></li>
            <li><a href="manage_payments.php">PEMBAYARAN</a></li>
            <li><a href="manage_receipts.php">RESIT</a></li>
            <li><a href="support_tickets.php">HELP & SUPPORT</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="profile-section">
            <span>ADMIN</span>
            <div class="profile-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
        </div>

        <!-- Add Slider Container -->
        <div class="slider-container">
            <div class="slider">
                <div class="slide" style="background-image: url('ads2/breyer-banner.png');">
                    <div class="slide-content">
                        <h3></h3>
                        <p></p>
                    </div>
                </div>
                <div class="slide" style="background-image: url('ads3/breyerbanner.png');">
                    <div class="slide-content">
                        <h3></h3>
                        <p></p>
                    </div>
                </div>
                <div class="slide" style="background-image: url('ads4/breyer-banner1.png');">
                    <div class="slide-content">
                        <h3></h3>
                        <p></p>
                    </div>
                </div>
            </div>
            <div class="slider-dots">
                <div class="dot active"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Kursus</h3>
                <div class="number">25</div>
            </div>
            <div class="stat-card">
                <h3>Total Pelajar</h3>
                <div class="number">150</div>
            </div>
            <div class="stat-card">
                <h3>Jumlah Jualan</h3>
                <div class="number">RM 15,000</div>
            </div>
            <div class="stat-card">
                <h3>Inventori</h3>
                <div class="number">45</div>
            </div>
        </div>
    </div>

    <!-- Add JavaScript for Slider -->
    <script>
        const slider = document.querySelector('.slider');
        const dots = document.querySelectorAll('.dot');
        let currentSlide = 0;

        function updateSlider() {
            slider.style.transform = `translateX(-${currentSlide * 100}%)`;
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }

        // Auto advance slides
        setInterval(() => {
            currentSlide = (currentSlide + 1) % 3;
            updateSlider();
        }, 5000);

        // Click on dots to change slides
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                updateSlider();
            });
        });
    </script>
</body>
</html>
