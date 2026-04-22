<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auth System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Breyer Marketplace - Premium Student Login Design (Same as Admin) */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, 
                #667eea 0%, 
                #764ba2 25%, 
                #667eea 50%, 
                #764ba2 75%, 
                #667eea 100%);
            background-size: 400% 400%;
            animation: gradientWave 15s ease infinite;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        @keyframes gradientWave {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating Elements Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 2px, transparent 2px),
                radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.1) 3px, transparent 3px),
                radial-gradient(circle at 50% 10%, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 100px 100px, 150px 150px, 75px 75px;
            animation: floatPattern 20s linear infinite;
            pointer-events: none;
        }

        @keyframes floatPattern {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        /* Premium Container Design */
        .container {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 25px;
            padding: 40px 35px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.05) inset;
            text-align: center;
            max-width: 380px;
            width: 85%;
            position: relative;
            animation: slideInUp 1s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo Enhancement */
        .container img {
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
            transition: transform 0.3s ease;
        }

        .container img:hover {
            transform: scale(1.05);
        }

        /* Beautiful Heading */
        .form h2 {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 30px;
            text-shadow: 
                0 5px 15px rgba(0, 0, 0, 0.3),
                0 0 30px rgba(255, 255, 255, 0.2);
            animation: textGlow 3s ease-in-out infinite alternate;
        }

        @keyframes textGlow {
            0% { 
                text-shadow: 
                    0 5px 15px rgba(0, 0, 0, 0.3),
                    0 0 30px rgba(255, 255, 255, 0.2);
            }
            100% { 
                text-shadow: 
                    0 5px 15px rgba(0, 0, 0, 0.3),
                    0 0 40px rgba(255, 255, 255, 0.4);
            }
        }

        /* Input Group Styling */
        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            color: white;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.4s ease;
            outline: none;
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
            font-weight: 400;
        }

        .input-group input:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 228, 92, 0.8);
            box-shadow: 
                0 0 0 4px rgba(255, 228, 92, 0.2),
                0 10px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        /* Switch Link Styling */
        .switch {
            margin: 25px 0;
        }

        .switch a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .switch a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* Premium Button Design */
        .button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, 
                #667eea 0%, 
                #764ba2 50%, 
                #667eea 100%);
            background-size: 200% 200%;
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.05rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 255, 255, 0.4), 
                transparent);
            transition: left 0.6s ease;
        }

        .button:hover {
            background-position: 100% 0;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.6);
        }

        .button:hover::before {
            left: 100%;
        }

        .button:active {
            transform: translateY(-1px) scale(1.01);
        }

        /* Error Message Styling */
        .error {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.3);
            display: none;
            animation: errorSlide 0.5s ease-out;
        }

        @keyframes errorSlide {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Additional Premium Effects */
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.1) 0%, 
                transparent 50%, 
                rgba(255, 255, 255, 0.05) 100%);
            border-radius: 25px;
            pointer-events: none;
        }

        /* Hover effect for entire container */
        .container:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 35px 60px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 35px 25px;
                margin: 20px;
                border-radius: 20px;
                max-width: 320px;
                width: 85%;
            }
            
            .form h2 {
                font-size: 1.8rem;
            }
            
            .input-group input {
                padding: 16px 20px;
            }
            
            .button {
                padding: 16px;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
                max-width: 300px;
                width: 90%;
            }
            
            .form h2 {
                font-size: 1.6rem;
                margin-bottom: 25px;
            }
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
