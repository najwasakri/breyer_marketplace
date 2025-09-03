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
            height: 100vh;
            overflow: hidden;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, 
                #FFE45C 0%,
                #FFEB73 15%,
                #FFD93D 25%,
                #6BB6FF 45%,
                #4A90E2 65%,
                #2E5BBA 80%,
                #003B95 100%
            );
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            position: relative;
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
                radial-gradient(circle at 20% 80%, rgba(255, 228, 92, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(74, 144, 226, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(0, 59, 149, 0.05) 0%, transparent 50%);
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
                    transparent 60px,
                    rgba(255, 255, 255, 0.02) 60px,
                    rgba(255, 255, 255, 0.02) 120px
                );
            animation: patternMove 20s linear infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes patternMove {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(60px, 60px);
            }
        }

        /* Header Styles */
        .header {
            padding: 1rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
            position: relative;
            z-index: 2;
            height: auto;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .top-nav {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            position: relative;
        }

        .logo-container {
            width: 200px; /* Reduced from 250px */
            padding: 0.25rem;
        }

        .logo {
            width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
            max-height: 60px; /* Reduced from 80px */
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
            width: 200px; /* Increased to match kategori button */
            text-align: center;
        }

        .nav-menu a,
        .kategori-btn {
            width: 200px; /* Increased to match all buttons */
            font-size: 1.1rem; /* Slightly larger font */
            padding: 0.5rem 1.2rem; /* Increased padding */
            height: 45px; /* Increased height */
            line-height: 27px; /* Adjusted line height */
            background: rgba(255, 255, 255, 0.95);
            color: #003B95;
            text-decoration: none;
            border: 2px solid rgba(0, 59, 149, 0.3);
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .nav-menu a:hover,
        .kategori-btn:hover {
            background: rgba(0, 59, 149, 0.95);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 59, 149, 0.3);
            border-color: rgba(255, 255, 255, 0.3);
        }

        /* Active page styling */
        .nav-menu a.active-page {
            background: linear-gradient(135deg, #003B95 0%, #0056b3 100%);
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 8px 25px rgba(0, 59, 149, 0.4),
                0 0 20px rgba(0, 59, 149, 0.3);
            transform: translateY(-2px);
        }

        .nav-menu a.active-page:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004080 100%);
            transform: translateY(-4px);
            box-shadow: 
                0 10px 30px rgba(0, 59, 149, 0.5),
                0 0 25px rgba(0, 59, 149, 0.4);
        }

        .kategori-btn {
            width: 200px; /* Increased from 150px */
            margin: 0; /* Remove any default margins */
        }

        .kategori-wrapper {
            display: flex;
            justify-content: center;
            width: 200px;
        }

        /* Settings Icon */
        .settings-icon {
            background: linear-gradient(135deg, #003B95 0%, #0056b3 100%);
            color: white;
            width: 50px;            /* Increased from 40px */
            height: 50px;           /* Increased from 40px */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-left: 1rem;
            transition: all 0.3s ease;
            font-size: 24px;        /* Added font-size to make the gear icon bigger */
            box-shadow: 0 4px 15px rgba(0, 59, 149, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .settings-icon:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004080 100%);
            transform: scale(1.15) rotate(90deg);   /* Added rotation effect */
            box-shadow: 0 6px 20px rgba(0, 59, 149, 0.4);
        }

        /* Main Content */
        .main-content {
            padding: 1rem;
            height: calc(100vh - 530px);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        /* Add decorative elements */
        .main-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: 
                radial-gradient(circle at 10% 20%, rgba(255, 228, 92, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(74, 144, 226, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(0, 59, 149, 0.02) 0%, transparent 60%);
            pointer-events: none;
            z-index: -1;
        }
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

        .settings-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: transparent;
            backdrop-filter: none;
            border-radius: 20px;
            box-shadow: none;
            display: none;
            z-index: 2000;
            min-width: 260px;
            margin-top: 18px;
            border: none;
            overflow: visible;
            padding: 12px 0;
        }

        .settings-dropdown::before {
            display: none;
        }

        .settings-dropdown.show {
            display: block;
            animation: dropdownSlideIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .settings-dropdown a {
            display: flex;
            align-items: center;
            padding: 18px 28px;
            color: #003B95;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            border-bottom: none;
            margin: 6px 12px;
            border-radius: 14px;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            letter-spacing: 0.5px;
            position: relative;
            background: rgba(255, 255, 255, 0.95);
            overflow: hidden;
            border: 1px solid rgba(0, 59, 149, 0.2);
            backdrop-filter: blur(15px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .settings-dropdown a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg, 
                transparent, 
                rgba(255, 255, 255, 0.6), 
                transparent
            );
            transition: left 0.8s ease;
        }

        .settings-dropdown a::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 59, 149, 0.03) 0%, rgba(74, 144, 226, 0.03) 100%);
            border-radius: 14px;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .settings-dropdown a:hover {
            background: linear-gradient(135deg, #003B95 0%, #0056b3 20%, #4A90E2 100%);
            color: white;
            transform: translateX(5px) translateY(-2px) scale(1.03);
            box-shadow: 
                0 12px 35px rgba(0, 59, 149, 0.5),
                0 5px 15px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            margin: 6px 8px;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .settings-dropdown a:hover::before {
            left: 100%;
        }

        .settings-dropdown a:hover::after {
            opacity: 1;
        }

        .settings-dropdown a:last-child {
            margin-bottom: 0;
        }

        .settings-dropdown a:first-child {
            margin-top: 0;
        }

        .settings-wrapper {
            position: relative;
            z-index: 1500;
        }

        .slider-container {
            width: 90%;
            max-width: 1200px;
            height: 380px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
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
            display: none;
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
            z-index: 1500;
        }

        .kategori-dropdown {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: transparent;
            backdrop-filter: none;
            border-radius: 20px;
            box-shadow: none;
            display: none;
            z-index: 2000;
            min-width: 200px;
            width: 200px;
            margin-top: 18px;
            border: none;
            overflow: visible;
            padding: 12px 0;
        }

        .kategori-dropdown::before {
            display: none;
        }

        .kategori-dropdown.show {
            display: block;
            animation: dropdownSlideIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes dropdownSlideIn {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px) scale(0.9);
                filter: blur(5px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0) scale(1);
                filter: blur(0px);
            }
        }

        .kategori-dropdown a {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px 20px;
            color: #003B95;
            text-decoration: none;
            font-size: 14px;
            font-weight: 800;
            border-bottom: none;
            margin: 6px 0;
            border-radius: 14px;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            letter-spacing: 1px;
            text-transform: uppercase;
            position: relative;
            background: rgba(255, 255, 255, 0.95);
            overflow: hidden;
            border: 1px solid rgba(0, 59, 149, 0.2);
            backdrop-filter: blur(15px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .kategori-dropdown a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg, 
                transparent, 
                rgba(255, 255, 255, 0.6), 
                transparent
            );
            transition: left 0.8s ease;
        }

        .kategori-dropdown a::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 59, 149, 0.03) 0%, rgba(74, 144, 226, 0.03) 100%);
            border-radius: 14px;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .kategori-dropdown a:hover {
            background: linear-gradient(135deg, #003B95 0%, #0056b3 20%, #4A90E2 100%);
            color: white;
            transform: translateY(-3px) scale(1.05);
            box-shadow: 
                0 12px 35px rgba(0, 59, 149, 0.5),
                0 5px 15px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            margin: 6px 0;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .kategori-dropdown a:hover::before {
            left: 100%;
        }

        .kategori-dropdown a:hover::after {
            opacity: 1;
        }

        .kategori-dropdown a:last-child {
            margin-bottom: 0;
        }

        .kategori-dropdown a:first-child {
            margin-top: 0;
        }

        .right-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-left: auto;  /* Push to right */
        }

        /* Add to Cart Button */
        .cart-icon {
            background: linear-gradient(135deg, #003B95 0%, #0056b3 100%);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 24px;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 59, 149, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .cart-icon:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004080 100%);
            transform: scale(1.15);
            box-shadow: 0 6px 20px rgba(0, 59, 149, 0.4);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            margin: 0;
            padding: 30px 25px;
            border-radius: 20px;
            width: 90%;
            max-width: 800px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            border: 3px solid #003B95;
            backdrop-filter: blur(10px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .modal.show .modal-content {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }

        .modal-content h2 {
            margin-top: 0;
            text-align: center;
            color: #003B95;
            font-size: 22px;
            margin-bottom: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .contact-container {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: stretch;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: nowrap;
            padding: 0 10px;
        }

        .contact-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px 20px;
            width: 100%;
            flex: 1;
            text-align: center;
            border-radius: 15px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            border: 3px solid #003B95;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            min-height: 200px;
            position: relative;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 35px rgba(0, 59, 149, 0.25);
            border-color: #0056b3;
        }

        .contact-card img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .contact-info {
            width: 100%;
            text-align: center;
            flex-grow: 1;
        }

        .contact-card h3 {
            font-size: 18px;
            font-weight: 700;
            color: #003B95;
            margin-bottom: 12px;
            margin-top: 8px;
            letter-spacing: 0.3px;
        }

        .contact-card p {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
            font-weight: 500;
            line-height: 1.4;
        }

        @media screen and (max-width: 600px) {
            .contact-container {
                flex-direction: column;
                align-items: center;
                gap: 15px;
                padding: 0 5px;
            }

            .contact-card {
                max-width: 100%;
                width: 100%;
                min-height: 160px;
                padding: 20px 15px;
            }

            .contact-card img {
                width: 50px;
                height: 50px;
            }

            .contact-card h3 {
                font-size: 16px;
            }

            .contact-card p {
                font-size: 13px;
            }

            .modal-content {
                width: 95%;
                max-width: 95%;
                padding: 25px 20px;
            }
        }

        .contact-button {
            display: inline-block;
            background: linear-gradient(135deg, #00C851 0%, #00A040 100%);
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
            letter-spacing: 0.3px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 200, 81, 0.3);
            width: 80%;
            text-align: center;
            margin-top: auto;
        }

        /* Update hover style for all buttons */
        .contact-button:hover {
            background: linear-gradient(135deg, #00A040 0%, #007030 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 200, 81, 0.4);
        }

        /* Add this to your existing CSS */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            cursor: pointer;
            background: #003B95;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 2px 8px rgba(0, 59, 149, 0.3);
        }

        .close-btn:hover {
            background: #0056b3;
            transform: rotate(90deg) scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 59, 149, 0.4);
        }

        /* Additional Complaint Form Styles */
        .modal-content form input:focus,
        .modal-content form select:focus,
        .modal-content form textarea:focus {
            outline: none;
            border-color: #003B95;
            box-shadow: 0 0 0 3px rgba(0, 59, 149, 0.1);
        }

        .modal-content form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 59, 149, 0.3);
        }

        .modal-content form button[type="button"]:hover {
            background: #bbb !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            body {
                overflow-y: auto;
                height: auto;
                min-height: 100vh;
            }

            .header {
                padding: 0.75rem 1rem;
                gap: 0.75rem;
            }

            .top-nav {
                flex-direction: column;
                gap: 0.75rem;
                margin-bottom: 0.5rem;
            }

            .logo-container {
                width: 120px;
                padding: 0.25rem;
                align-self: center;
            }

            .logo {
                max-height: 40px;
            }

            .right-controls {
                position: absolute;
                top: 0.75rem;
                right: 1rem;
                margin-left: 0;
            }

            .nav-container {
                position: static;
                transform: none;
                width: 100%;
                order: 3;
                margin-top: 0.5rem;
            }

            .nav-menu {
                flex-direction: column;
                gap: 0.5rem;
                width: 100%;
                align-items: center;
            }

            .nav-menu > a,
            .kategori-wrapper {
                width: 100%;
                max-width: 300px;
            }

            .nav-menu a,
            .kategori-btn {
                width: 100%;
                max-width: 300px;
                font-size: 1rem;
                height: 42px;
                line-height: 24px;
                padding: 0.5rem 1rem;
                margin: 0 auto;
                border-radius: 8px;
                font-weight: 600;
            }

            .kategori-btn {
                width: 100%;
                max-width: 300px;
            }

            .settings-icon {
                width: 40px;
                height: 40px;
                font-size: 20px;
                margin-left: 0;
            }

            .cart-icon {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }

            .cart-count {
                width: 18px;
                height: 18px;
                font-size: 11px;
                top: -4px;
                right: -4px;
            }

            .settings-dropdown {
                right: 0;
                min-width: 180px;
            }

            .settings-dropdown a {
                padding: 12px 18px;
                font-size: 14px;
            }

            .slider-container {
                width: 95%;
                height: 220px;
                margin: 15px auto;
                border-radius: 12px;
            }

            .slide-content {
                bottom: 15px;
                left: 15px;
            }

            .slide-content h3 {
                font-size: 1.1rem;
                margin-bottom: 4px;
            }

            .slide-content p {
                font-size: 0.85rem;
            }

            .slider-dots {
                bottom: 10px;
                right: 15px;
                gap: 6px;
            }

            .dot {
                width: 8px;
                height: 8px;
            }

            .main-content {
                padding: 0.75rem;
                height: auto;
                min-height: 150px;
                overflow: visible;
            }

            .kategori-dropdown {
                left: 0;
                transform: none;
                min-width: 100%;
                margin-top: 3px;
                max-width: 300px;
                margin-left: auto;
                margin-right: auto;
            }

            .modal-content {
                width: 95%;
                margin: 5% auto;
                padding: 20px 15px;
            }

            .modal-content h2 {
                font-size: 20px;
                margin-bottom: 20px;
            }

            .contact-container {
                flex-direction: column;
                gap: 15px;
                margin-top: 20px;
            }

            .contact-card {
                width: 100%;
                max-width: none;
                padding: 15px;
            }

            .contact-card img {
                width: 60px;
                height: 60px;
                margin-bottom: 10px;
            }

            .contact-button {
                width: 140px;
                height: 40px;
                line-height: 20px;
                font-size: 14px;
            }

            .close-btn {
                width: 30px;
                height: 30px;
                font-size: 20px;
                top: 8px;
                right: 8px;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 0.6rem 0.75rem;
            }

            .top-nav {
                flex-direction: column;
                gap: 0.6rem;
                margin-bottom: 0.4rem;
            }

            .logo-container {
                width: 100px;
                align-self: center;
            }

            .logo {
                max-height: 35px;
            }

            .right-controls {
                position: absolute;
                top: 0.6rem;
                right: 0.75rem;
                margin-left: 0;
            }

            .nav-menu {
                gap: 0.4rem;
            }

            .nav-menu a,
            .kategori-btn {
                font-size: 0.9rem;
                height: 38px;
                line-height: 20px;
                max-width: 280px;
                border-radius: 6px;
            }

            .settings-icon {
                width: 36px;
                height: 36px;
                font-size: 18px;
            }

            .cart-icon {
                width: 36px;
                height: 36px;
                font-size: 18px;
            }

            .cart-count {
                width: 16px;
                height: 16px;
                font-size: 10px;
                top: -3px;
                right: -3px;
            }

            .settings-dropdown {
                min-width: 160px;
            }

            .settings-dropdown a {
                padding: 10px 15px;
                font-size: 13px;
            }

            .slider-container {
                height: 200px;
                margin: 12px auto;
                border-radius: 10px;
            }

            .slide-content {
                bottom: 12px;
                left: 12px;
            }

            .slide-content h3 {
                font-size: 1rem;
                margin-bottom: 3px;
            }

            .slide-content p {
                font-size: 0.75rem;
            }

            .slider-dots {
                bottom: 8px;
                right: 12px;
                gap: 4px;
            }

            .dot {
                width: 6px;
                height: 6px;
            }

            .main-content {
                padding: 0.5rem;
                min-height: 120px;
            }

            .kategori-dropdown {
                max-width: 280px;
            }

            .modal-content {
                width: 98%;
                margin: 2% auto;
                padding: 15px 12px;
            }

            .modal-content h2 {
                font-size: 18px;
                margin-bottom: 15px;
            }

            .contact-container {
                gap: 12px;
                margin-top: 15px;
            }

            .contact-card {
                padding: 12px;
            }

            .contact-card img {
                width: 50px;
                height: 50px;
                margin-bottom: 8px;
            }

            .contact-card h4 {
                font-size: 14px;
                margin-bottom: 5px;
            }

            .contact-card p {
                font-size: 12px;
            }

            .contact-button {
                width: 120px;
                height: 36px;
                line-height: 16px;
                font-size: 12px;
                padding: 8px 15px;
            }

            .close-btn {
                width: 28px;
                height: 28px;
                font-size: 18px;
                top: 6px;
                right: 6px;
            }
        }

        /* AI Chatbot Character Styles */
        .ai-chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999; /* Increased z-index */
            animation: chatbotEnter 1s ease-out;
        }

        @keyframes chatbotEnter {
            0% { 
                opacity: 0; 
                transform: translateY(100px) scale(0.5) rotate(45deg);
            }
            50% { 
                opacity: 0.8; 
                transform: translateY(-10px) scale(1.1) rotate(-5deg);
            }
            100% { 
                opacity: 1; 
                transform: translateY(0) scale(1) rotate(0deg);
            }
        }

        .chatbot-character {
            width: 50px;
            height: 50px;
            background: linear-gradient(145deg, #ffffff 0%, #f0f0f0 100%);
            border-radius: 50%;
            border: 3px solid #333;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: 
                0 8px 25px rgba(0, 0, 0, 0.15),
                0 0 20px rgba(255, 228, 92, 0.3);
            animation: characterFloat 3s ease-in-out infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        @keyframes characterFloat {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-10px) scale(1.05); }
        }

        .chatbot-character:hover {
            transform: scale(1.1) translateY(-5px);
            box-shadow: 
                0 15px 35px rgba(0, 0, 0, 0.2),
                0 0 30px rgba(255, 228, 92, 0.5);
            animation: characterBounce 0.5s ease infinite;
        }

        /* Hover effects for all character parts */
        .chatbot-character:hover .character-eyes .eye {
            animation: eyeWink 1s ease infinite;
            transform: scale(1.1);
        }

        .chatbot-character:hover .character-mouth {
            animation: mouthComelHover 0.8s ease infinite;
            background: linear-gradient(145deg, #ff1493 0%, #ff69b4 100%);
            width: 16px;
        }

        .chatbot-character:hover .character-cheeks {
            animation: cheekBlush 0.5s ease infinite;
            opacity: 1;
        }

        .chatbot-character:hover .character-hair {
            animation: hairExcited 0.7s ease infinite;
        }

        @keyframes eyeWink {
            0%, 60%, 100% { 
                height: 16px; 
                transform: scale(1.1);
            }
            80% { 
                height: 4px; 
                transform: scale(1.2);
            }
        }

        @keyframes mouthComelHover {
            0%, 100% { 
                width: 16px; 
                height: 8px;
                border-radius: 50%;
                background: linear-gradient(145deg, #ff1493 0%, #ff69b4 100%);
                transform: translateY(0px);
            }
            50% { 
                width: 18px; 
                height: 9px;
                border-radius: 50%;
                background: linear-gradient(145deg, #ff69b4 0%, #ffb3ba 100%);
                transform: translateY(-1px);
            }
        }

        @keyframes cheekBlush {
            0%, 100% { opacity: 0.8; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.3); }
        }

        @keyframes hairExcited {
            0%, 100% { transform: translateX(-50%) scale(1) rotate(0deg); }
            25% { transform: translateX(-50%) scale(1.1) rotate(2deg); }
            75% { transform: translateX(-50%) scale(1.1) rotate(-2deg); }
        }

        @keyframes characterBounce {
            0%, 100% { transform: scale(1.1) translateY(-5px) rotate(0deg); }
            25% { transform: scale(1.15) translateY(-8px) rotate(-2deg); }
            75% { transform: scale(1.15) translateY(-8px) rotate(2deg); }
        }

        /* Character Face */
        .character-face {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Character Arms/Hands - BACK INSIDE THE BODY */
        .character-arms {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 1;
        }

        .character-hand {
            position: absolute;
            width: 16px;
            height: 16px;
            background: linear-gradient(145deg, #ffffff 0%, #f0f0f0 100%);
            border: 2px solid #333;
            border-radius: 50%;
            animation: handWaveInside 2s ease-in-out infinite;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Little palm inside hand */
        .character-hand::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            background: #ffb3ba;
            border-radius: 50%;
            opacity: 0.7;
        }

        .hand-left {
            top: 45%;
            left: -8px; /* Just slightly outside body edge */
            animation-delay: 0s;
        }

        .hand-right {
            top: 45%;
            right: -8px; /* Just slightly outside body edge */
            animation-delay: 1s;
        }

        @keyframes handWaveInside {
            0%, 50%, 100% { 
                transform: rotate(0deg) translateY(0px) scale(1);
                opacity: 0.8;
            }
            25% { 
                transform: rotate(15deg) translateY(-3px) scale(1.1);
                opacity: 1;
            }
            75% { 
                transform: rotate(-15deg) translateY(-2px) scale(1.05);
                opacity: 0.9;
            }
        }

        /* Enhanced hover animation for hands - back to simple */
        .chatbot-character:hover .character-hand {
            animation: handWaveExcited 0.3s ease infinite;
            box-shadow: 0 4px 15px rgba(255, 228, 92, 0.6);
        }

        @keyframes handWaveExcited {
            0%, 100% { 
                transform: rotate(0deg) translateY(0px) scale(1);
            }
            25% { 
                transform: rotate(25deg) translateY(-5px) scale(1.2);
            }
            50% { 
                transform: rotate(-25deg) translateY(-8px) scale(1.3);
            }
            75% { 
                transform: rotate(20deg) translateY(-3px) scale(1.1);
            }
        }

        /* "TANYA LA SAYA" Speech Bubble Tooltip */
        .chatbot-tooltip {
            position: absolute;
            bottom: 90px;
            right: -10px;
            background: linear-gradient(135deg, #FFE45C 0%, #FFB347 100%);
            color: #003B95;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            white-space: nowrap;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            border: 2px solid #003B95;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px) scale(0.8);
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 1002;
            animation: tooltipFloat 2s ease-in-out infinite;
        }

        /* Speech bubble arrow */
        .chatbot-tooltip::after {
            content: '';
            position: absolute;
            bottom: -8px;
            right: 20px;
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-top: 8px solid #FFE45C;
        }

        .chatbot-tooltip::before {
            content: '';
            position: absolute;
            bottom: -10px;
            right: 18px;
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-top: 10px solid #003B95;
        }

        @keyframes tooltipFloat {
            0%, 100% { transform: translateY(10px) scale(0.8); }
            50% { transform: translateY(5px) scale(0.85); }
        }

        /* Show tooltip on hover */
        .chatbot-character:hover .chatbot-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
            animation: tooltipBounce 0.6s ease-out, tooltipFloat 2s ease-in-out infinite 0.6s;
        }

        @keyframes tooltipBounce {
            0% { 
                opacity: 0; 
                visibility: hidden;
                transform: translateY(20px) scale(0.3); 
            }
            50% { 
                opacity: 0.8; 
                transform: translateY(-5px) scale(1.1); 
            }
            70% { 
                transform: translateY(2px) scale(0.95); 
            }
            100% { 
                opacity: 1; 
                visibility: visible;
                transform: translateY(0) scale(1); 
            }
        }

        /* Add cute emoji animation to tooltip */
        .tooltip-emoji {
            display: inline-block;
            animation: emojiWave 1s ease-in-out infinite;
            margin-left: 5px;
        }

        @keyframes emojiWave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(15deg); }
            75% { transform: rotate(-15deg); }
        }

        /* Eyes - Comel Version */
        .character-eyes {
            display: flex;
            gap: 10px;
            margin-bottom: 8px;
        }

        .eye {
            width: 16px;
            height: 16px;
            background: #333;
            border-radius: 50%;
            position: relative;
            animation: eyeBlink 4s ease-in-out infinite;
            border: 2px solid #666;
        }

        @keyframes eyeBlink {
            0%, 85%, 100% { height: 16px; }
            90% { height: 3px; }
        }

        /* Mata sparkle yang comel */
        .eye::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 4px;
            width: 6px;
            height: 6px;
            background: white;
            border-radius: 50%;
            box-shadow: 2px 2px 0px rgba(255, 255, 255, 0.8);
        }

        /* Tambah eyelashes yang comel */
        .eye::before {
            content: '';
            position: absolute;
            top: -3px;
            left: 2px;
            width: 12px;
            height: 4px;
            border-top: 2px solid #333;
            border-radius: 50% 50% 0 0;
            opacity: 0.7;
        }

        /* Mouth - Kecik Comel Tutup */
        .character-mouth {
            width: 12px;
            height: 6px;
            background: linear-gradient(145deg, #ff69b4 0%, #ff1493 100%);
            border-radius: 50%;
            animation: mouthTalk 2s ease-in-out infinite;
            border: 2px solid #333;
            margin-top: 6px;
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.3);
        }

        @keyframes mouthTalk {
            0%, 100% { 
                width: 12px; 
                height: 6px;
                transform: scale(1);
                border-radius: 50%;
            }
            50% { 
                width: 14px; 
                height: 7px;
                transform: scale(1.05);
                border-radius: 60%;
            }
        }

        /* Character Hair/Top */
        .character-hair {
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 20px;
            background: #666;
            border-radius: 50% 50% 20% 20%;
            animation: hairBounce 3s ease-in-out infinite;
        }

        @keyframes hairBounce {
            0%, 100% { transform: translateX(-50%) scale(1); }
            50% { transform: translateX(-50%) scale(1.05); }
        }

        /* Add cute cheeks */
        .character-cheeks {
            position: absolute;
            width: 8px;
            height: 8px;
            background: #ffb3ba;
            border-radius: 50%;
            opacity: 0.6;
            animation: cheekGlow 2s ease-in-out infinite alternate;
        }

        .cheek-left {
            top: 35px;
            left: 15px;
        }

        .cheek-right {
            top: 35px;
            right: 15px;
        }

        @keyframes cheekGlow {
            0% { opacity: 0.4; transform: scale(0.8); }
            100% { opacity: 0.8; transform: scale(1.1); }
        }

        /* Chat bubble indicator */
        .chat-indicator {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 20px;
            height: 20px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            animation: indicatorPulse 1s ease-in-out infinite;
            opacity: 0;
        }

        .chat-indicator.show {
            opacity: 1;
        }

        @keyframes indicatorPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Chat Modal */
        .chatbot-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000; /* Increased z-index */
            backdrop-filter: blur(5px);
        }

        .chatbot-modal-content {
            position: absolute;
            bottom: 120px;
            right: 30px;
            width: 350px; /* Reduced from 400px */
            max-height: 450px; /* Reduced from 500px */
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 3px solid #FFE45C;
            overflow: hidden;
            animation: chatAppear 0.3s ease-out;
            z-index: 10002;
        }

        /* Chat Body */
        .chat-body {
            height: 280px; /* Reduced from 350px */
            overflow-y: auto;
            padding: 15px 20px; /* Reduced padding */
            background: #fafafa;
        }

        /* Chat Header */
        .chat-header {
            background: linear-gradient(135deg, #FFE45C 0%, #4A90E2 100%);
            padding: 12px 18px; /* Reduced from 15px 20px */
            display: flex;
            align-items: center;
            gap: 12px; /* Reduced from 15px */
            border-bottom: 2px solid #003B95;
        }

        .chat-avatar {
            width: 35px; /* Reduced from 40px */
            height: 35px; /* Reduced from 40px */
            background: white;
            border-radius: 50%;
            border: 2px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px; /* Reduced from 20px */
        }

        .chat-title h3 {
            margin: 0;
            color: #003B95;
            font-size: 16px; /* Reduced from 18px */
            font-weight: bold;
        }

        .chat-title p {
            margin: 0;
            color: #666;
            font-size: 11px; /* Reduced from 12px */
        }

        /* Chat Input */
        .chat-input-container {
            padding: 12px 18px; /* Reduced from 15px 20px */
            background: white;
            border-top: 2px solid #e0e0e0;
            display: flex;
            gap: 8px; /* Reduced from 10px */
        }

        .chat-input {
            flex: 1;
            padding: 10px 12px; /* Reduced from 12px 15px */
            border: 2px solid #e0e0e0;
            border-radius: 20px; /* Reduced from 25px */
            outline: none;
            font-size: 13px; /* Reduced from 14px */
            transition: all 0.3s ease;
        }

        .chat-send-btn {
            background: linear-gradient(145deg, #4A90E2 0%, #003B95 100%);
            color: white;
            border: none;
            width: 40px; /* Reduced from 45px */
            height: 40px; /* Reduced from 45px */
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px; /* Reduced from 18px */
            transition: all 0.3s ease;
        }

        /* Quick Questions */
        .quick-questions {
            padding: 12px 18px; /* Reduced from 15px 20px */
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }

        .quick-questions h4 {
            margin: 0 0 8px 0; /* Reduced from 10px */
            color: #003B95;
            font-size: 13px; /* Reduced from 14px */
        }

        .question-btn {
            display: block;
            width: 100%;
            padding: 6px 10px; /* Reduced from 8px 12px */
            margin-bottom: 4px; /* Reduced from 5px */
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px; /* Reduced from 15px */
            text-align: left;
            font-size: 11px; /* Reduced from 12px */
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .question-btn:hover {
            background: #4A90E2;
            color: white;
            transform: translateX(5px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .chatbot-modal-content {
                width: 85%; /* Reduced from 90% */
                right: 7.5%; /* Adjusted to center */
                bottom: 80px; /* Reduced from 100px */
                max-height: 60vh; /* Further reduced for mobile */
            }
            
            .chat-body {
                height: 220px; /* Reduced for mobile */
                padding: 12px 15px;
            }
            
            .chatbot-character {
                width: 60px;
                height: 60px;
            }
        }

        /* Typing indicator */
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            opacity: 0.7;
        }

        .typing-dot {
            width: 6px;
            height: 6px;
            background: #666;
            border-radius: 50%;
            animation: typingDot 1.4s infinite;
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typingDot {
            0%, 60%, 100% { opacity: 0.3; }
            30% { opacity: 1; }
        }

        /* Beautiful Close Button Styling */
        .chat-close {
            position: absolute;
            top: 10px;
            right: 15px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
            z-index: 1000;
        }

        .chat-close:hover {
            background: linear-gradient(135deg, #ff5252, #d63031);
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.5);
        }

        .chat-close:active {
            transform: scale(0.95) rotate(90deg);
            box-shadow: 0 2px 5px rgba(255, 107, 107, 0.4);
        }

        .chat-close:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.3);
        }

        /* Animation for close button entrance */
        @keyframes closeButtonEntrance {
            0% {
                opacity: 0;
                transform: scale(0) rotate(-180deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }

        .chat-close {
            animation: closeButtonEntrance 0.5s ease-out;
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
                    <a href="#home" class="active-page">HOME</a>
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
                    <a href="#hubungi" class="hubungi-link">HUBUNGI</a>
                </nav>
            </div>
            
            <div class="right-controls">
                <div class="cart-icon" title="Troli Belanja">
                    🛒
                    <span class="cart-count">0</span>
                </div>
                <div class="settings-wrapper">
                    <div class="settings-icon" title="Tetapan">⚙️</div>
                    <div class="settings-dropdown">
                        <a href="student_profile.php" onclick="viewProfile(event)">Profile</a>
                        <a href="payment_history.php" onclick="viewPaymentHistory(event)">Sejarah Pembayaran</a>
                        <a href="#" onclick="openComplaintForm(event)">Aduan</a>
                        <a href="change_password.php" onclick="changePassword(event)">Tukar Kata Laluan</a>
                        <a href="logout.php" onclick="logout(event)">Log Keluar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="slider-container">
            <div class="slider">
                <div class="slide" style="background-image: url('banner1/banner-logo.png');">
                    <div class="slide-content">
                        <h3></h3>
                        <p></p>
                    </div>
                </div>
                <div class="slide" style="background-image: url('banner2/banner-course.png');">
                    <div class="slide-content">
                        <h3></h3>
                        <p></p>
                    </div>
                </div>
                <div class="slide" style="background-image: url('banner3/banner-guarentee.png');">
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
                    <img src="ads7/breyer-banner8.png" alt="Waktu Operasi">
                    <div class="contact-info">
                        <h3>Waktu Operasi</h3>
                        <p>8:30 PAGI – 5:30 PETANG</p>
                    </div>
                </div>
                <div class="contact-card">
                    <img src="ads5/breyer-banner5.png" alt="Telefon">
                    <div class="contact-info">
                        <h3>Telefon</h3>
                        <p>Hubungi kami melalui WhatsApp</p>
                    </div>
                    <a href="https://wa.me/60102509941" class="contact-button" target="_blank">
                        WhatsApp
                    </a>
                </div>
                <div class="contact-card">
                    <img src="ads6/breyer-banner6.png" alt="Emel">
                    <div class="contact-info">
                        <h3>Emel</h3>
                        <p>Hantar mesej kepada kami</p>
                    </div>
                    <a href="mailto:Cashier.sg@cqbreyer.edu.my" class="contact-button" target="_blank">
                        Hantar Emel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Aduan -->
    <div id="complaintModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close-btn" onclick="closeComplaintModal()">&times;</span>
            <h2>📝 BORANG ADUAN</h2>
            <form id="complaintForm" style="margin-top: 20px;">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; color: #003B95; margin-bottom: 8px;">Jenis Aduan:</label>
                    <select id="complaintType" required style="width: 100%; padding: 12px; border: 2px solid #90caf9; border-radius: 8px; font-size: 14px; background: white;">
                        <option value="">Pilih jenis aduan</option>
                        <option value="pembayaran">Masalah Pembayaran</option>
                        <option value="produk">Masalah Produk</option>
                        <option value="perkhidmatan">Masalah Perkhidmatan</option>
                        <option value="sistem">Masalah Sistem/Website</option>
                        <option value="lain-lain">Lain-lain</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; color: #003B95; margin-bottom: 8px;">Tajuk Aduan:</label>
                    <input type="text" id="complaintTitle" required style="width: 100%; padding: 12px; border: 2px solid #90caf9; border-radius: 8px; font-size: 14px;" placeholder="Masukkan tajuk aduan">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; color: #003B95; margin-bottom: 8px;">Butiran Aduan:</label>
                    <textarea id="complaintDetails" required rows="5" style="width: 100%; padding: 12px; border: 2px solid #90caf9; border-radius: 8px; font-size: 14px; resize: vertical;" placeholder="Terangkan butiran aduan anda dengan jelas"></textarea>
                </div>
                
                <div style="text-align: center; margin-top: 25px;">
                    <button type="button" onclick="closeComplaintModal()" style="background: #ccc; color: #666; padding: 12px 25px; border: none; border-radius: 8px; font-weight: bold; margin-right: 10px; cursor: pointer;">
                        Batal
                    </button>
                    <button type="submit" style="background: linear-gradient(135deg, #003B95 0%, #0056b3 100%); color: white; padding: 12px 25px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                        Hantar Aduan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- AI Chatbot -->
    <div class="ai-chatbot-container">
        <div class="chatbot-character" onclick="openChatbot()">
            <div class="chatbot-tooltip">
                TANYA LA SAYA <span class="tooltip-emoji">👋</span>
            </div>
            <div class="character-hair"></div>
            <div class="character-arms">
                <div class="character-hand hand-left"></div>
                <div class="character-hand hand-right"></div>
            </div>
            <div class="character-face">
                <div class="character-eyes">
                    <div class="eye"></div>
                    <div class="eye"></div>
                </div>
                <div class="character-mouth"></div>
            </div>
            <div class="character-cheeks">
                <div class="cheek-left"></div>
                <div class="cheek-right"></div>
            </div>
            <div class="chat-indicator" id="chatIndicator">!</div>
        </div>
    </div>

    <!-- Chatbot Modal -->
    <div id="chatbotModal" class="chatbot-modal">
        <div class="chatbot-modal-content">
            <div class="chat-header">
                <div class="chat-avatar">🤖</div>
                <div class="chat-title">
                    <h3>AI Assistant</h3>
                    <p>Saya di sini untuk membantu anda</p>
                </div>
                <button class="chat-close" onclick="closeChatbot()">&times;</button>
            </div>
            <div class="chat-body" id="chatBody">
                <div class="chat-message bot-message">
                    <div class="message-avatar bot-avatar">🤖</div>
                    <div class="message-content">
                        Hai! Saya adalah pembantu AI untuk Breyer Marketplace. Bagaimana saya boleh membantu anda hari ini?
                    </div>
                </div>
            </div>
            <div class="quick-questions">
                <h4>Soalan Pantas:</h4>
                <button class="question-btn" onclick="askQuestion('Bagaimana nak beli produk?')">Bagaimana nak beli produk?</button>
                <button class="question-btn" onclick="askQuestion('Kaedah pembayaran apa yang tersedia?')">Kaedah pembayaran apa yang tersedia?</button>
                <button class="question-btn" onclick="askQuestion('Bagaimana nak hubungi sokongan?')">Bagaimana nak hubungi sokongan?</button>
            </div>
            <div class="chat-input-container">
                <input type="text" class="chat-input" id="chatInput" placeholder="Taip mesej anda di sini..." onkeypress="handleChatEnter(event)">
                <button class="chat-send-btn" onclick="sendMessage()">➤</button>
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
            
            // Remove active-page class from all navigation items
            document.querySelectorAll('.nav-menu a').forEach(link => {
                link.classList.remove('active-page');
            });
            // Set KATEGORI button as active when dropdown is opened
            kategoriBtn.classList.add('active-page');
        });

        // Close dropdown when clicking outside and return HOME to active
        document.addEventListener('click', (e) => {
            if (!kategoriBtn.contains(e.target) && !kategoriDropdown.contains(e.target)) {
                kategoriDropdown.classList.remove('show');
                kategoriBtn.classList.remove('active-page');
                document.querySelector('a[href="#home"]').classList.add('active-page');
            }
        });

        // HUBUNGI button functionality - same as KATEGORI
        document.querySelector('.hubungi-link').addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Remove active-page class from all navigation items
            document.querySelectorAll('.nav-menu a').forEach(link => {
                link.classList.remove('active-page');
            });
            // Set HUBUNGI button as active when modal is opened
            e.target.classList.add('active-page');
            
            const modal = document.getElementById('hubungiModal');
            modal.style.display = "block";
            setTimeout(() => modal.classList.add('show'), 10);
        });

        document.getElementById('closeBtn').addEventListener('click', () => {
            const modal = document.getElementById('hubungiModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = "none", 300);
            
            // Return HOME button to active state when modal closes
            document.querySelector('.hubungi-link').classList.remove('active-page');
            document.querySelector('a[href="#home"]').classList.add('active-page');
        });

        window.addEventListener('click', (e) => {
            const modal = document.getElementById('hubungiModal');
            if (e.target === modal) {
                modal.classList.remove('show');
                setTimeout(() => modal.style.display = "none", 300);
                
                // Return HOME button to active state when modal closes
                document.querySelector('.hubungi-link').classList.remove('active-page');
                document.querySelector('a[href="#home"]').classList.add('active-page');
            }
        });

        // Complaint Modal Functions
        function openComplaintForm(e) {
            e.preventDefault();
            // Close settings dropdown first
            document.querySelector('.settings-dropdown').classList.remove('show');
            
            const modal = document.getElementById('complaintModal');
            modal.style.display = "block";
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function closeComplaintModal() {
            const modal = document.getElementById('complaintModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = "none", 300);
        }

        // Handle complaint form submission
        document.getElementById('complaintForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const type = document.getElementById('complaintType').value;
            const title = document.getElementById('complaintTitle').value;
            const details = document.getElementById('complaintDetails').value;
            
            if (!type || !title || !details) {
                alert('Sila lengkapkan semua maklumat yang diperlukan.');
                return;
            }
            
            // Here you would normally send the data to your server
            // For now, we'll just show a success message
            alert('Aduan anda telah berjaya dihantar. Kami akan menghubungi anda dalam masa 3 hari bekerja.');
            
            // Reset form and close modal
            document.getElementById('complaintForm').reset();
            closeComplaintModal();
        });

        // Close complaint modal when clicking outside
        window.addEventListener('click', (e) => {
            const modal = document.getElementById('complaintModal');
            if (e.target === modal) {
                closeComplaintModal();
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

        // Cart functionality
        let cartItems = JSON.parse(localStorage.getItem('cartItems') || '[]');
        
        function updateCartCount() {
            const cartCount = document.querySelector('.cart-count');
            const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
            cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
        }

        // Cart click handler
        document.querySelector('.cart-icon').addEventListener('click', () => {
            window.location.href = 'cart.php';
        });

        // Initialize cart count on page load
        updateCartCount();

        // Chatbot functionality
        function openChatbot() {
            document.getElementById('chatbotModal').style.display = 'block';
        }

        function closeChatbot() {
            document.getElementById('chatbotModal').style.display = 'none';
        }

        function sendMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            
            if (!message) return;
            
            // Add user message
            addMessage(message, true);
            
            // Clear input
            input.value = '';
            
            // Simulate bot response
            setTimeout(() => {
                const botResponse = getBotResponse(message);
                addMessage(botResponse, false);
            }, 1000);
        }

        function addMessage(message, isUser) {
            const chatBody = document.getElementById('chatBody');
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${isUser ? 'user-message' : 'bot-message'}`;
            
            messageDiv.innerHTML = `
                <div class="message-avatar ${isUser ? 'user-avatar' : 'bot-avatar'}">
                    ${isUser ? '👤' : '🤖'}
                </div>
                <div class="message-content">${message}</div>
            `;
            
            chatBody.appendChild(messageDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function getBotResponse(message) {
            const lowerMessage = message.toLowerCase();
            
            if (lowerMessage.includes('beli') || lowerMessage.includes('produk')) {
                return 'Untuk membeli produk, anda boleh pilih kategori dari menu KATEGORI, kemudian pilih produk yang anda inginkan dan klik "BELI" atau "ADD TO CART".';
            } else if (lowerMessage.includes('bayar') || lowerMessage.includes('payment')) {
                return 'Kami menerima pelbagai kaedah pembayaran termasuk online banking dari pelbagai bank seperti Maybank, CIMB, Public Bank, dan lain-lain.';
            } else if (lowerMessage.includes('hubungi') || lowerMessage.includes('contact')) {
                return 'Anda boleh menghubungi kami melalui WhatsApp di 010-250 9941 atau email ke Cashier.sg@cqbreyer.edu.my. Waktu operasi kami adalah 8:30 PAGI – 5:30 PETANG.';
            } else if (lowerMessage.includes('kategori') || lowerMessage.includes('jenis')) {
                return 'Kami ada 5 kategori utama: CS (Computer System), AM (Administration Management), CULINARY, ELECTRICAL, dan LAIN-LAIN. Setiap kategori mempunyai produk-produk khusus.';
            } else {
                return 'Terima kasih atas pertanyaan anda! Saya akan cuba membantu sebaik mungkin. Jika anda memerlukan bantuan lanjut, sila hubungi tim sokongan kami.';
            }
        }

        function askQuestion(question) {
            document.getElementById('chatInput').value = question;
            sendMessage();
        }

        function handleChatEnter(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        // Close chatbot when clicking outside
        window.onclick = function(event) {
            const chatbotModal = document.getElementById('chatbotModal');
            if (event.target === chatbotModal) {
                closeChatbot();
            }
        }
    </script>
</body>
</html>
