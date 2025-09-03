<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auth System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(120deg, #a18cd1 0%, #fbc2eb 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            transition: background 1s;
        }

        .input-group input[name="ic_number"] {
            border: 2px solid #a18cd1;
            transition: box-shadow 0.4s, border-color 0.4s;
            animation: pulseInput 1.2s;
        }

        .input-group input[name="ic_number"]:focus {
            border-color: #8f5de8;
            box-shadow: 0 0 0 4px #a18cd144;
            animation: shakeInput 0.4s;
        }

        @keyframes pulseInput {
            0% { box-shadow: 0 0 0 0 #a18cd1; }
            70% { box-shadow: 0 0 12px 4px #a18cd1; }
            100% { box-shadow: 0 0 0 0 #a18cd1; }
        }

        @keyframes shakeInput {
            0% { transform: translateX(0); }
            20% { transform: translateX(-4px); }
            40% { transform: translateX(4px); }
            60% { transform: translateX(-2px); }
            80% { transform: translateX(2px); }
            100% { transform: translateX(0); }
        }
    </style>
</head>
<body>
<div class="container">
    <div id="login-form" class="form">
    <img src="logo-breyer1.png" alt="Breyer Logo" style="display: block; margin: 0 auto 20px; max-width: 200px;">
        <h2>Student Login</h2>
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
