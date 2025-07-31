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
            background: #e0e0e0;
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
            position: relative;
        }

        .profile-icon {
            width: 40px;
            height: 40px;
            background: #fff; /* putih */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .profile-icon svg {
            color: #2056a8;   /* biru */
            stroke: #2056a8;  /* biru */
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

        /* Profile Dropdown Styles */
        .profile-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            min-width: 220px;
            z-index: 100;
            overflow: hidden;
            display: flex;
            flex-direction: column; /* Pastikan menegak */
            padding: 0;
        }

        .profile-dropdown a {
            padding: 18px 24px;
            color: #2056a8;
            text-decoration: none;
            font-size: 1.15rem;
            transition: background 0.2s;
            border-bottom: 1px solid #f0f0f0;
            display: block;
            text-align: left;
        }

        .profile-dropdown a:last-child {
            border-bottom: none;
        }

        .profile-dropdown a:hover {
            background: #f5f7fa;
        }

        /* Modal Profile Admin Styles */
        .modal-profile-admin {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.18);
            justify-content: center;
            align-items: center;
        }

        .modal-content-profile {
            background: #f8fbff;
            border-radius: 24px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
            padding: 24px 18px 18px 18px;
            max-width: 320px;
            width: 98%;
            text-align: center;
            position: relative;
            margin: auto;
        }

        .close-profile {
            position: absolute;
            top: 18px;
            right: 24px;
            font-size: 2rem;
            color: #2056a8;
            cursor: pointer;
            font-weight: bold;
            transition: color 0.2s;
        }
        .close-profile:hover {
            color: #163e7a;
        }

        .avatar-profile {
            width: 90px;
            height: 90px;
            margin: 0 auto 18px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #2056a8;
            border-radius: 50%;
            background: #e6eefc;
        }
        .avatar-profile svg {
            width: 56px;
            height: 56px;
            color: #b0b8c9;
        }

        .profile-title {
            margin-bottom: 20px;
            font-size: 1.6rem;
            color: #2056a8;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .profile-label {
            margin-top: 12px;
            margin-bottom: 5px;
            font-size: 1.08rem;
            color: #2056a8;
            font-weight: bold;
            text-align: left;
        }

        .profile-input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1.5px solid #e0e0e0;
            background: #f6f8fa;
            font-size: 1rem;
            margin-bottom: 2px;
            color: #333;
            outline: none;
            transition: border 0.2s;
            box-sizing: border-box;
        }
        .profile-input[readonly] {
            background: #f6f8fa;
            border: 1.5px solid #e0e0e0;
        }

        .btn-change-password {
            margin-top: 18px;
            padding: 12px 0;
            width: 100%;
            background: #2056a8;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.08rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            box-shadow: 0 2px 8px rgba(32,86,168,0.07);
        }
        .btn-change-password:hover {
            background: #163e7a;
        }

        /* Change Password Dropdown Styles */
        .change-password-dropdown {
            background: #f6f8fa;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(32,86,168,0.07);
            margin-top: 16px;
            padding: 16px 10px 10px 10px;
            width: 100%;
        }

        .change-password-dropdown form {
            padding: 18px;
        }

        .modal-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .modal-title-row .profile-title {
            margin-bottom: 0;
            font-size: 1.3rem;
        }
        .modal-title-row .close-profile {
            position: static;
            font-size: 2rem;
            margin-left: 12px;
        }
    </style>
</head>
<body>
    <!-- Tambah alert messages selepas <body> -->
    <?php if (isset($_SESSION['password_success'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; margin: 10px; border-radius: 5px;">
            <?php echo $_SESSION['password_success']; unset($_SESSION['password_success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['password_error'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border-radius: 5px;">
            <?php echo $_SESSION['password_error']; unset($_SESSION['password_error']); ?>
        </div>
    <?php endif; ?>

    <div class="sidebar">
        <div class="menu-title">MENU UTAMA</div>
        <ul class="menu-items">
            <li class="active"><a href="dashboard.php">HALAMAN UTAMA</a></li>
            <li><a href="manage_courses.php">SENARAI KURSUS</a></li>
            <li><a href="manage_inventory.php">INVENTORI</a></li>
            <li><a href="manage_payments.php">PESANAN</a></li>
            <li><a href="manage_receipts.php">RESIT</a></li>
            <li><a href="support_tickets.php">HELP & SUPPORT</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="profile-section" id="profileSection">
            <span>ADMIN</span>
            <div class="profile-icon" id="profileIcon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="profile-dropdown" id="profileDropdown" style="display:none;">
                <a href="profile_admin.php">Profile</a>
                <a href="payment_history.php">Sejarah Pembayaran</a>
                <a href="change_password.php">Tukar Kata Laluan</a>
                <a href="logout.php">Log Keluar</a>
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
                <div class="number">5</div>
            </div>
            <div class="stat-card">
                <h3>Total Pelajar</h3>
                <div class="number">150</div>
            </div>
            <div class="stat-card">
                <h3>Jumlah Jualan</h3>
                <div class="number">RM 150</div>
            </div>
            <div class="stat-card">
                <h3>Inventori</h3>
                <div class="number">7</div>
            </div>
        </div>
    </div>

    <!-- Modal Profile Admin -->
    <div id="profileAdminModal" class="modal-profile-admin">
        <div class="modal-content-profile">
            <span class="close-profile" id="closeProfileModal">&times;</span>
            <div class="avatar-profile">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 20v-1a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v1"/>
                </svg>
            </div>
            <h2 class="profile-title">Profile Admin</h2>
            <div class="profile-label">Nama</div>
            <input class="profile-input" type="text" value="<?php echo htmlspecialchars($_SESSION['admin_nama']); ?>" readonly>
            <div class="profile-label">No. Kad Pengenalan</div>
            <input class="profile-input" type="text" value="<?php echo htmlspecialchars($_SESSION['admin_ic']); ?>" readonly>
            <div class="profile-label">Kata Laluan</div>
            <input class="profile-input" type="password" value="********" readonly>
            <button class="btn-change-password" id="showChangePassword">Tukar Kata Laluan</button>
        </div>
    </div>

    <!-- Modal Tukar Kata Laluan -->
    <div id="changePasswordModal" class="modal-profile-admin" style="display:none;">
        <div class="modal-content-profile">
            <div class="modal-title-row">
                <h2 class="profile-title" style="margin-bottom:0;">Tukar Kata Laluan</h2>
                <span class="close-profile" id="closeChangePasswordModal">&times;</span>
            </div>
            <form method="post" action="change_password_process.php">
                <div class="profile-label">Kata Laluan Lama</div>
                <input class="profile-input" type="password" name="old_password" required>
                <div class="profile-label">Kata Laluan Baru</div>
                <input class="profile-input" type="password" name="new_password" required>
                <div class="profile-label">Sahkan Kata Laluan Baru</div>
                <input class="profile-input" type="password" name="confirm_password" required>
                <button type="submit" class="btn-change-password" style="margin-top:10px;">Simpan</button>
            </form>
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
    <script>
const profileIcon = document.getElementById('profileIcon');
const profileDropdown = document.getElementById('profileDropdown');
const profileSection = document.getElementById('profileSection');

profileIcon.addEventListener('click', function(e) {
    e.stopPropagation();
    // TUTUP dropdown, BUKA modal
    profileDropdown.style.display = 'none';
    document.getElementById('profileAdminModal').style.display = 'flex';
});

// Tutup modal bila klik X atau luar modal
document.getElementById('closeProfileModal').onclick = function() {
    document.getElementById('profileAdminModal').style.display = 'none';
};
window.onclick = function(event) {
    const modal = document.getElementById('profileAdminModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
};

// Toggle modal tukar kata laluan
document.getElementById('showChangePassword').onclick = function() {
    document.getElementById('profileAdminModal').style.display = 'none';
    document.getElementById('changePasswordModal').style.display = 'flex';
};

// Tutup modal tukar kata laluan
document.getElementById('closeChangePasswordModal').onclick = function() {
    document.getElementById('changePasswordModal').style.display = 'none';
};
window.onclick = function(event) {
    const profileModal = document.getElementById('profileAdminModal');
    const changeModal = document.getElementById('changePasswordModal');
    if (event.target === profileModal) {
        profileModal.style.display = 'none';
    }
    if (event.target === changeModal) {
        changeModal.style.display = 'none';
    }
};
</script>
</body>
</html>
