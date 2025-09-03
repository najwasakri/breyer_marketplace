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
            height: 100vh;
            overflow: hidden;
            background: linear-gradient(135deg, 
                #FFE45C 0%,
                #FFE45C 30%,
                #4A90E2 70%,
                #003B95 100%
            );
            font-family: Arial, sans-serif;
        }

        .marketplace-grid {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
            padding: 15px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            height: calc(100vh - 140px);
            overflow: hidden;
        }

        .product-card {
            width: 280px;
            background: white;  
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 59, 149, 0.1);
            padding: 7px;
            text-align: center;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            height: 160px;
            object-fit: contain;
            margin-bottom: 10px;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-info {
            padding: 10px 0;
        }

        .product-title {
            font-size: 0.9rem;
            color: #003B95;
            margin: 8px 0;
            font-weight: bold;
            line-height: 1.2;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-price {
            font-size: 1.4rem;
            color: #25D366;
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

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
        }

        .contact-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 8px 0;
            background: #003B95;
            color: white;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .contact-button:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        .cart-btn {
            background: #25D366;
            color: white;
            width: 100%;
            padding: 7px;
            border: none;
            border-radius: 8px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .cart-btn:hover {
            background: #128C7E;
            transform: translateY(-2px);
        }

        .cart-icon-page {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #003B95;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 24px;
            z-index: 100;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .cart-icon-page:hover {
            background: #002b70;
            transform: scale(1.1);
        }

        .cart-count-page {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .marketplace-title {
            text-align: center;
            margin: 6.5rem 0 0.5rem 0;
        }

        .marketplace-title h1 {
            font-size: 2rem;
            color: #003B95;
            margin-bottom: 10px;
            font-weight: 700;
            letter-spacing: 1px;
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
            background: #003B95;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0;
        }

        .back-btn::before {
            content: '';
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 8px 12px 8px 0;
            border-color: transparent white transparent transparent;
            transform: translateX(-2px);
        }

        .back-btn:hover {
            background: #0056b3;
            transform: translateX(-3px);
        }

        .main-content {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            padding-top: 20px;
            box-sizing: border-box;
            overflow: hidden;
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

        /* Custom Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.3);
            z-index: 9999;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            min-width: 300px;
            max-width: 400px;
        }

        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notification-icon {
            font-size: 24px;
            animation: bounce 0.6s ease-in-out;
        }

        .notification-text {
            flex: 1;
        }

        .notification-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .notification-message {
            font-size: 12px;
            opacity: 0.9;
        }

        .notification-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            margin-left: 8px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .notification-close:hover {
            opacity: 1;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        @media (max-width: 768px) {
            body {
                height: 100vh;
                overflow: hidden;
            }

            .main-content {
                height: 100vh;
                overflow: hidden;
            }

            .back-btn {
                width: 45px;
                height: 45px;
            }

            .back-btn::before {
                border-width: 7px 10px 7px 0;
            }

            .marketplace-grid {
                padding: 10px;
                gap: 15px;
                height: calc(100vh - 160px);
                overflow: hidden;
            }

            .product-card {
                width: 170px;
                padding: 12px;
            }

            .product-image {
                height: 130px;
            }

            .product-title {
                font-size: 0.85rem;
            }

            .contact-button {
                font-size: 0.8rem;
                padding: 8px;
            }

            .cart-btn {
                font-size: 0.75rem;
                padding: 6px;
            }

            .notification {
                top: 10px;
                right: 10px;
                left: 10px;
                transform: translateY(-100px);
                min-width: auto;
                max-width: none;
            }

            .notification.show {
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .back-btn {
                width: 40px;
                height: 40px;
            }

            .back-btn::before {
                border-width: 6px 8px 6px 0;
            }
        }

        /* AI Chatbot Character Styles */
        .ai-chatbot-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
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
            width: 80px;
            height: 80px;
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
            animation: eyeWink 0.8s ease infinite;
        }

        .chatbot-character:hover .character-mouth {
            animation: mouthSmile 1s ease infinite;
        }

        .chatbot-character:hover .character-cheeks {
            animation: cheekBlush 0.5s ease infinite;
            opacity: 1;
        }

        .chatbot-character:hover .character-hair {
            animation: hairExcited 0.7s ease infinite;
        }

        @keyframes eyeWink {
            0%, 70%, 100% { height: 12px; }
            85% { height: 2px; }
        }

        @keyframes mouthSmile {
            0%, 100% { 
                width: 20px; 
                border-radius: 0 0 20px 20px;
                transform: translateY(0);
            }
            50% { 
                width: 25px; 
                border-radius: 0 0 25px 25px;
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

        /* Eyes */
        .character-eyes {
            display: flex;
            gap: 8px;
            margin-bottom: 5px;
        }

        .eye {
            width: 12px;
            height: 12px;
            background: #333;
            border-radius: 50%;
            position: relative;
            animation: eyeBlink 3s ease-in-out infinite;
        }

        @keyframes eyeBlink {
            0%, 90%, 100% { height: 12px; }
            95% { height: 2px; }
        }

        .eye::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 3px;
            width: 4px;
            height: 4px;
            background: white;
            border-radius: 50%;
        }

        /* Mouth */
        .character-mouth {
            width: 20px;
            height: 10px;
            border: 2px solid #333;
            border-top: none;
            border-radius: 0 0 20px 20px;
            animation: mouthTalk 2s ease-in-out infinite;
        }

        @keyframes mouthTalk {
            0%, 100% { width: 20px; }
            50% { width: 15px; }
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
            z-index: 1001;
            backdrop-filter: blur(5px);
        }

        .chatbot-modal-content {
            position: absolute;
            bottom: 120px;
            right: 30px;
            width: 400px;
            max-height: 500px;
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 3px solid #FFE45C;
            overflow: hidden;
            animation: chatAppear 0.3s ease-out;
        }

        @keyframes chatAppear {
            0% { 
                opacity: 0; 
                transform: translateY(50px) scale(0.8); 
            }
            100% { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }

        /* Chat Header */
        .chat-header {
            background: linear-gradient(135deg, #FFE45C 0%, #4A90E2 100%);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 2px solid #003B95;
        }

        .chat-avatar {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            border: 2px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .chat-title {
            flex: 1;
        }

        .chat-title h3 {
            margin: 0;
            color: #003B95;
            font-size: 18px;
            font-weight: bold;
        }

        .chat-title p {
            margin: 0;
            color: #666;
            font-size: 12px;
        }

        .chat-close {
            background: #ff4444;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .chat-close:hover {
            background: #cc0000;
            transform: scale(1.1);
        }

        /* Chat Body */
        .chat-body {
            height: 350px;
            overflow-y: auto;
            padding: 20px;
            background: #fafafa;
        }

        .chat-message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            animation: messageSlide 0.3s ease-out;
        }

        @keyframes messageSlide {
            0% { opacity: 0; transform: translateX(-20px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        .user-message {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .bot-avatar {
            background: linear-gradient(145deg, #ffffff 0%, #f0f0f0 100%);
            border: 2px solid #333;
        }

        .user-avatar {
            background: linear-gradient(145deg, #4A90E2 0%, #003B95 100%);
            color: white;
        }

        .message-content {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
        }

        .bot-message .message-content {
            background: white;
            border: 2px solid #e0e0e0;
            margin-left: 0;
        }

        .user-message .message-content {
            background: linear-gradient(145deg, #4A90E2 0%, #003B95 100%);
            color: white;
            margin-right: 0;
        }

        /* Chat Input */
        .chat-input-container {
            padding: 15px 20px;
            background: white;
            border-top: 2px solid #e0e0e0;
            display: flex;
            gap: 10px;
        }

        .chat-input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .chat-input:focus {
            border-color: #4A90E2;
            box-shadow: 0 0 10px rgba(74, 144, 226, 0.3);
        }

        .chat-send-btn {
            background: linear-gradient(145deg, #4A90E2 0%, #003B95 100%);
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .chat-send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.4);
        }

        /* Quick Questions */
        .quick-questions {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }

        .quick-questions h4 {
            margin: 0 0 10px 0;
            color: #003B95;
            font-size: 14px;
        }

        .question-btn {
            display: block;
            width: 100%;
            padding: 8px 12px;
            margin-bottom: 5px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            text-align: left;
            font-size: 12px;
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
                width: 90%;
                right: 5%;
                bottom: 100px;
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
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-btn" title="Kembali ke Dashboard"></a>
    <a href="cart.php" class="cart-icon-page" title="Troli Belanja">
        🛒
        <span class="cart-count-page" id="cartCountPage">0</span>
    </a>

    <div class="marketplace-title">
        <h1>Lain-Lain</h1>
        <p></p>
    </div>

    <main class="main-content">
        <div class="marketplace-grid">
            <div class="product-card">
                <img src="ads9/breyer-fail1.png" alt="File" class="product-image">
                <div class="product-info">
                    <h3 class="product-title">FILE</h3>
                    <p class="product-price">RM10.00</p>
                    <div class="button-group">
                        <button onclick="openModal('FILE', 10.00)" class="contact-button">
                            <span>BELI</span>
                        </button>
                        <button onclick="addToCart('FILE', 'RM10.00', 'LAIN-LAIN')" class="cart-btn">
                            🛒 ADD TO CART
                        </button>
                    </div>
                </div>
            </div>

            <div class="product-card">
                <img src="ads10/breyer-lanyard2.png" alt="Lanyard" class="product-image">
                <div class="product-info">
                    <h3 class="product-title">LANYARD</h3>
                    <p class="product-price">RM5.00</p>
                    <div class="button-group">
                        <button onclick="openModal('LANYARD', 5.00)" class="contact-button">
                            <span>BELI</span>
                        </button>
                        <button onclick="addToCart('LANYARD', 'RM5.00', 'LAIN-LAIN')" class="cart-btn">
                            🛒 ADD TO CART
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Custom Notification -->
    <div id="notification" class="notification">
        <div class="notification-content">
            <div class="notification-icon">📦</div>
            <div class="notification-text">
                <div class="notification-title">Berjaya Ditambah!</div>
                <div class="notification-message" id="notification-message">Item telah ditambah ke troli</div>
            </div>
            <button class="notification-close" onclick="hideNotification()">&times;</button>
        </div>
    </div>

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

        // Cart functionality
        function addToCart(itemName, itemPrice, category) {
            let cartItems = JSON.parse(localStorage.getItem('cartItems') || '[]');
            const price = parseFloat(itemPrice.replace('RM', ''));
            
            const existingItemIndex = cartItems.findIndex(item => 
                item.name === itemName && item.category === category
            );
            
            if (existingItemIndex > -1) {
                cartItems[existingItemIndex].quantity += 1;
            } else {
                cartItems.push({
                    name: itemName,
                    price: price,
                    category: category,
                    quantity: 1,
                    image: getProductImage(itemName) // Add image path
                });
            }
            
            localStorage.setItem('cartItems', JSON.stringify(cartItems));
            updateCartCountPage();
            showNotification('Berjaya Ditambah!', `${itemName} telah ditambah ke troli belanja`, '📦');
        }

        function getProductImage(itemName) {
            // Define product images for LAIN-LAIN category
            const productImages = {
                'FILE': 'ads9/breyer-fail1.png',
                'LANYARD': 'ads10/breyer-lanyard2.png'
            };
            
            return productImages[itemName] || 'ads9/breyer-fail1.png';
        }

        function updateCartCountPage() {
            const cartItems = JSON.parse(localStorage.getItem('cartItems') || '[]');
            const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
            const cartCountElement = document.getElementById('cartCountPage');
            
            cartCountElement.textContent = totalItems;
            cartCountElement.style.display = totalItems > 0 ? 'flex' : 'none';
        }

        // Custom Notification Functions
        function showNotification(title, message, icon = '📦') {
            const notification = document.getElementById('notification');
            const titleElement = notification.querySelector('.notification-title');
            const messageElement = notification.querySelector('.notification-message');
            const iconElement = notification.querySelector('.notification-icon');

            titleElement.textContent = title;
            messageElement.textContent = message;
            iconElement.textContent = icon;

            notification.classList.add('show');

            setTimeout(() => {
                hideNotification();
            }, 3000);
        }

        function hideNotification() {
            const notification = document.getElementById('notification');
            notification.classList.remove('show');
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateCartCountPage();
        });
    </script>
</body>
</html>
