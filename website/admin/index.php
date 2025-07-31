<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auth System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div id="login-form" class="form">
    <img src="ads1/Breyer Gombak.png" alt="Breyer Logo" style="display: block; margin: 0 auto 20px; max-width: 200px;">
        <h2>Admin Login</h2>
        <div class="error" id="login-error"></div>
        <form onsubmit="return handleLogin(event)">
            <div class="input-group">
                <input type="text" 
                       name="ic_number" 
                       placeholder="NO KAD PENGENALAN" 
                       pattern="[0-9]{12}" 
                       title="Sila masukkan 12 digit nombor kad pengenalan tanpa simbol" 
                       required>
            </div>
            <div class="input-group">
                <input type="password" 
                       name="password" 
                       placeholder="KATA LALUAN" 
                       required>
            </div>
            <div class="switch"><a onclick="toggleForm('signup')">Pendaftaran baru</a></div>
            <button class="button" type="submit">MASUK</button>
        </form>
    </div>

    <div id="signup-form" class="form" style="display:none;">
        <h2>Daftar Akaun</h2>
        <div class="error" id="signup-error"></div>
        <form onsubmit="return handleSignup(event)">
            <div class="input-group">
                <input type="text" 
                       name="name" 
                       placeholder="Nama Penuh" 
                       required>
            </div>
            <div class="input-group">
                <input type="text" 
                       name="ic_number" 
                       placeholder="NO KAD PENGENALAN" 
                       pattern="[0-9]{12}" 
                       title="Sila masukkan 12 digit nombor kad pengenalan tanpa simbol" 
                       required>
            </div>
            <div class="input-group">
                <input type="password" 
                       name="password" 
                       placeholder="Kata Laluan" 
                       required 
                       minlength="6">
            </div>
            <button class="button" type="submit">Daftar</button>
        </form>
        <div class="switch">Sudah ada akaun? <a onclick="toggleForm('login')">Log Masuk</a></div>
    </div>
</div>

<script>
function toggleForm(form) {
    document.getElementById('login-form').style.display = form === 'login' ? 'block' : 'none';
    document.getElementById('signup-form').style.display = form === 'signup' ? 'block' : 'none';
}

async function handleLogin(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const res = await fetch('login.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.success) {
        window.location.href = 'dashboard.php';
    } else {
        const error = document.getElementById('login-error');
        error.textContent = data.message;
        error.style.display = 'block';
    }
}

async function handleSignup(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const res = await fetch('signup.php', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.success) {
        alert('Pendaftaran berjaya! Sila log masuk.');
        toggleForm('login');
    } else {
        const error = document.getElementById('signup-error');
        error.textContent = data.message;
        error.style.display = 'block';
    }
}
</script>
</body>
</html>
