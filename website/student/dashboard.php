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
    <title>Breyer iklan</title>
    <style>
        /* Reset and Global Styles */
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

        /* Header Styles */
        .header {
            padding: 1.5rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .top-nav {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            position: relative;
        }

        .logo-container {
            width: 250px; /* Increased from 180px */
            padding: 0.5rem;
        }

        .logo {
            width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
            max-height: 80px; /* Added to ensure vertical control */
        }

        /* Update Navigation Styles */
        .nav-container {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: auto;
            z-index: 1;
        }

        .nav-menu {
            display: flex;
            gap: 2rem;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
        }

        /* Make nav items consistent width */
        .nav-menu > a,
        .kategori-wrapper {
            width: 150px; /* Increased from 120px */
            text-align: center;
        }

        .nav-menu a,
        .kategori-btn {
            width: 150px; /* Increased from 120px */
            font-size: 1.1rem; /* Slightly larger font */
            padding: 0.5rem 1.2rem; /* Increased padding */
            height: 45px; /* Increased height */
            line-height: 27px; /* Adjusted line height */
            background: white;
            color: #003B95;
            text-decoration: none;
            border: 2px solid #003B95;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .nav-menu a:hover,
        .kategori-btn:hover {
            background: #003B95;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .kategori-btn {
            width: 200px; /* Increased from 150px */
            margin: 0; /* Remove any default margins */
        }

        .kategori-wrapper {
            display: flex;
            justify-content: center;
        }

        /* Settings Icon */
        .settings-icon {
            background: #003B95;
            color: white;
            width: 50px;            /* Increased from 40px */
            height: 50px;           /* Increased from 40px */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-left: 1rem;
            transition: background-color 0.3s ease;
            font-size: 24px;        /* Added font-size to make the gear icon bigger */
        }

        .settings-icon:hover {
            background: #0056b3;
            transform: scale(1.1);   /* Added scale effect on hover */
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            min-height: calc(100vh - 80px);
            position: relative;
        }

        /* Add decorative elements */
        .main-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: repeating-linear-gradient(
                45deg,
                rgba(255, 255, 255, 0.05) 0px,
                rgba(255, 255, 255, 0.05) 20px,
                transparent 20px,
                transparent 40px
            );
            pointer-events: none;
        }

        /* Decorative Elements */
        .hand-shape {
            position: absolute;
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg,rgb(220, 215, 54) 50%, transparent 50%);
            clip-path: polygon(0 0, 100% 0, 100% 30%, 60% 30%, 60% 50%, 40% 50%, 40% 30%, 0 30%);
            z-index: 1;
        }

        .top-left {
            top: 0;
            left: 0;
        }

        .bottom-right {
            bottom: 0;
            right: 0;
            transform: rotate(180deg);
        }

        .shape {
            position: absolute;
            background-color: rgba(255, 215, 0, 0.3);
            border-radius: 50%;
            z-index: 1;
        }

        .circle {
            width: 20px;
            height: 20px;
        }

        .big-circle {
            width: 50px;
            height: 50px;
        }

        .dot-group {
            position: absolute;
            top: 50%;
            left: 10%;
            display: grid;
            grid-template-columns: repeat(5, 5px);
            gap: 5px;
            z-index: 1;
        }

        .dot-group div {
            width: 5px;
            height: 5px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
        }

        /* Update banner for better contrast */
        .banner {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .banner h1 {
            color: #003B95;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }

        .banner p {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }

        /* Wave Design - Adjust colors to match new background */
        .wave-design {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 50%;
            background: linear-gradient(45deg, 
                rgba(0, 59, 149, 0.9) 0%, 
                rgba(0, 86, 179, 0.9) 100%
            );
            clip-path: polygon(0 100%, 100% 70%, 100% 100%, 0% 100%);
            z-index: 1;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-name {
            color: #003B95;
            font-weight: bold;
        }

        .settings-icon {
            background: #003B95;
            color: white;
            width: 50px;            /* Increased from 40px */
            height: 50px;           /* Increased from 40px */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-left: 1rem;
            transition: background-color 0.3s ease;
            font-size: 24px;        /* Added font-size to make the gear icon bigger */
        }

        .settings-icon:hover {
            background: #0056b3;
            transform: scale(1.1);   /* Added scale effect on hover */
        }

        .settings-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
            z-index: 1000;
            min-width: 200px; /* Increased width */
        }

        .settings-dropdown.show {
            display: block;
        }

        .settings-dropdown a {
            display: block;
            padding: 15px 25px; /* Increased padding */
            color: #003B95;
            text-decoration: none;
            font-size: 16px; /* Increased font size */
            transition: all 0.3s;
            font-weight: 500; /* Added font weight */
            border-bottom: 1px solid #eee; /* Added separator */
        }

        .settings-dropdown a:last-child {
            border-bottom: none; /* Remove border from last item */
        }

        .settings-dropdown a:hover {
            background-color: #f5f5f5;
            padding-left: 30px; /* Indent on hover */
        }

        .settings-wrapper {
            position: relative;
        }

        .slider-container {
            width: 90%;
            max-width: 1200px;
            height: 300px;
            margin: 40px auto;
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .slider {
            display: flex;
            width: 300%;
            height: 100%;
            transition: transform 0.5s ease-in-out;
        }

        .slide {
            width: 33.333%;
            height: 100%;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .slide::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
        }

        .slide-content {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: white;
            z-index: 1;
        }

        .slide-content h3 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .slide-content p {
            font-size: 1rem;
            opacity: 0.9;
        }

        .slider-dots {
            position: absolute;
            bottom: 10px;
            right: 20px;
            display: flex;
            gap: 8px;
            z-index: 2;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: background 0.3s;
        }

        .dot.active {
            background: white;
        }

        .kategori-wrapper {
            position: relative;
            display: inline-block;
        }

        .kategori-dropdown {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
            z-index: 1000;
            min-width: 150px; /* Match width with buttons */
            margin-top: 5px;
        }

        .kategori-dropdown.show {
            display: block;
        }

        .kategori-dropdown a {
            display: block;
            padding: 12px 20px;
            color: #003B95;
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s;
            font-weight: 500;
            border-bottom: 1px solid #eee;
            text-align: center;
            width: 100%; /* Ensure full width */
            box-sizing: border-box;
            white-space: nowrap; /* Prevent text wrapping */
        }

        .kategori-dropdown a:last-child {
            border-bottom: none;
        }

        .kategori-dropdown a:hover {
            background-color: #f5f5f5;
            padding-left: 20px; /* Keep padding consistent */
            color: #0056b3; /* Add color change on hover */
        }

        .right-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-left: auto;  /* Push to right */
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: linear-gradient(to bottom, #FFE45C, #4A90E2);
            margin: 10% auto;
            padding: 30px 20px;
            border-radius: 12px;
            width: 60%;
            max-width: 700px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }

        .modal-content h2 {
            margin-top: 0;
            text-align: center;
            color: #003B95;
            font-size: 24px;
            margin-bottom: 30px;
        }

        .contact-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .contact-card {
            background: white;
            padding: 20px;
            width: 200px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-5px);
        }

        .contact-card img {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
            object-fit: contain;
        }

        @media screen and (max-width: 600px) {
            .contact-card {
                width: 80%;
            }

            .modal-content {
                width: 90%;
                margin-top: 20%;
            }
        }

        .contact-button {
            display: inline-block;
            background: #25D366; /* WhatsApp green */
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 160px;
            text-align: center;
            height: 45px;
            line-height: 25px;
        }

        /* Update hover style for all buttons */
        .contact-button:hover {
            background: #128C7E; /* Darker green on hover */
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Add this to your existing CSS */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            background: #003B95;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            background: #0056b3;
            transform: rotate(90deg);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="top-nav">
            <div class="logo-container">
                <img src="logo-breyer1.png" alt="Breyer Logo" class="logo">
            </div>
            
            <div class="nav-container">
                <nav class="nav-menu">
                    <a href="#home">HOME</a>
                    <div class="kategori-wrapper">
                        <a href="#" class="kategori-btn">KATEGORI</a>
                        <div class="kategori-dropdown">
                            <a href="cs.php">CS</a>
                            <a href="am.php">AM</a>
                            <a href="culinary.php">CULINARY</a>
                            <a href="electrical.php">ELECTRICAL</a>
                            <a href="fnb.php">LAIN-LAIN</a>
                        </div>
                    </div>
                    <a href="#hubungi">HUBUNGI</a>
                </nav>
            </div>
            
            <div class="right-controls">
                <div class="settings-wrapper">
                    <div class="settings-icon" title="Tetapan">⚙️</div>
                    <div class="settings-dropdown">
                        <a href="student_profile.php" onclick="viewProfile(event)">Profile</a>
                        <a href="payment_history.php" onclick="viewPaymentHistory(event)">Sejarah Pembayaran</a>
                        <a href="change_password.php" onclick="changePassword(event)">Tukar Kata Laluan</a>
                        <a href="logout.php" onclick="logout(event)">Log Keluar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="slider-container">
            <div class="slider">
                <div class="slide" style="background-image: url('ads/breyer-banner.png');">
                    <div class="slide-content">
                        <h3></h3>
                        <p></p>
                    </div>
                </div>
                <div class="slide" style="background-image: url('ads2/breyer-banner1.png');">
                    <div class="slide-content">
                        <h3></h3>
                        <p></p>
                    </div>
                </div>
                <div class="slide" style="background-image: url('ads3/breyer-banner2.png');">
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
    </header>

    <main class="main-content">
        <!-- Banner and decorative elements removed -->
    </main>

    <div id="hubungiModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeBtn">&times;</span>
            <h2>HUBUNGI KAMI</h2>
            <div class="contact-container">
                <div class="contact-card">
                    <img src="ads30/breyer-banner9.png" alt="Waktu Operasi">
                    <h4>Waktu Operasi</h4>
                    <p>8:30 PAGI – 5:30 PETANG</p>
                </div>
                <div class="contact-card">
                    <img src="ads31/breyer-banner21.png" alt="Telefon"> <!-- Removed inline style -->
                    <h4>Telefon</h4>
                    <a href="https://wa.me/60102509941" class="contact-button" target="_blank">
                        WhatsApp
                    </a>
                </div>
                <div class="contact-card">
                    <img src="ads32/breyer-banner22.png" alt="Emel">
                    <h4>Emel</h4>
                    <a href="mailto:Cashier.sg@cqbreyer.edu.my" class="contact-button" target="_blank">
                        Hantar Emel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('.settings-icon').addEventListener('click', (e) => {
            e.stopPropagation();
            const dropdown = document.querySelector('.settings-dropdown');
            dropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const settingsDropdown = document.querySelector('.settings-dropdown');
            const kategoriDropdown = document.querySelector('.kategori-dropdown');
            
            if (!e.target.closest('.settings-wrapper')) {
                settingsDropdown.classList.remove('show');
            }
            if (!e.target.closest('.kategori-wrapper')) {
                kategoriDropdown.classList.remove('show');
            }
        });

        // Auto Slider
        const slider = document.querySelector('.slider');
        const dots = document.querySelectorAll('.dot');
        let currentSlide = 0;

        function nextSlide() {
            currentSlide = (currentSlide + 1) % 3;
            updateSlider();
        }

        function updateSlider() {
            slider.style.transform = `translateX(-${currentSlide * 33.333}%)`;
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }

        // Manual navigation with dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                updateSlider();
            });
        });

        // Auto slide every 5 seconds
        setInterval(nextSlide, 5000);

        // Add this before the slider code
        const kategoriBtn = document.querySelector('.kategori-btn');
        const kategoriDropdown = document.querySelector('.kategori-dropdown');

        kategoriBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            kategoriDropdown.classList.toggle('show');
        });

        // Add this to your existing <script> section
        document.querySelector('a[href="#hubungi"]').addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('hubungiModal').style.display = "block";
        });

        document.getElementById('closeBtn').addEventListener('click', () => {
            document.getElementById('hubungiModal').style.display = "none";
        });

        window.addEventListener('click', (e) => {
            if (e.target === document.getElementById('hubungiModal')) {
                document.getElementById('hubungiModal').style.display = "none";
            }
        });

        // Add this to your existing script section
        function viewProfile(e) {
            e.preventDefault();
            window.location.href = 'student_profile.php';
        }

        function viewPaymentHistory(e) {
            e.preventDefault();
            window.location.href = 'payment_history.php';
        }

        function changePassword(e) {
            e.preventDefault();
            window.location.href = 'change_password.php';
        }

        function logout(e) {
            e.preventDefault();
            if(confirm('Adakah anda pasti untuk log keluar?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>
