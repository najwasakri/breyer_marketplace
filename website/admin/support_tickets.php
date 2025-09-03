<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'includes/db_connect.php';

// Proses balasan admin
if (isset($_POST['reply']) && isset($_POST['ticket_id'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $reply = $conn->real_escape_string($_POST['reply_message']);
    $sql = "UPDATE support_tickets SET admin_reply = '$reply', status = 'closed' WHERE id = $ticket_id";
    $conn->query($sql);
    $success_message = "Balasan berjaya dihantar!";
}

// Papar semua tiket
$tickets = [];
$sql = "SELECT * FROM support_tickets ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center Header</title>
    <style>
        html, body {
            overflow: hidden;
            height: 100%;
        }
        body {
            margin: 0;
            background: #f7f8fa;
            font-family: 'Poppins', Arial, sans-serif;
        }
        .help-header {
            background: linear-gradient(135deg, #fff8dc 0%, #ffeb9c 100%);
            color: #222;
            padding: 60px 0 80px 0;
            text-align: center;
            position: relative;
        }
        .help-header h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 32px;
            text-align: center;
        }
        .search-bar {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0;
        }
        .search-bar input[type="text"] {
            padding: 14px 18px;
            border: none;
            border-radius: 4px 0 0 4px;
            width: 340px;
            font-size: 1rem;
            outline: none;
        }
        .search-bar button {
            padding: 14px 28px;
            border: none;
            border-radius: 0 4px 4px 0;
            background: #ff6b6b;
            color: #fff;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        .search-bar button:hover {
            background: #ff4b4b;
        }
        /* Simple decorative lines */
        .line-left, .line-right {
            position: absolute;
            width: 80px;
            height: 80px;
            pointer-events: none;
        }
        .line-left {
            left: 8%;
            top: 30px;
        }
        .line-right {
            right: 8%;
            top: 10px;
        }
        .main-content {
            max-width: 1400px;
            width: 90%;
            margin: 20px auto;
            background: #fff;
            padding: 1px 32px;
            border-radius: 20px;
            border: 3px solid #ffeb9c; /* Border warna kuning muda */
        }

        .tickets-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0;
            padding: 0;
        }

        .tickets-wrapper {
            min-width: 800px; /* Minimum width to show all content */
        }
        h2 { margin-bottom: 24px; }
        .ticket { 
            border-bottom: 1px solid #eee; 
            padding: 16px 0; 
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .ticket:last-child { border-bottom: none; }
        .ticket-status { font-size: 0.9em; padding: 2px 8px; border-radius: 8px; margin-left: 8px;}
        .open { background: #ffe45c; }
        .closed { background: #bdbdbd; color: #fff; }
        .reply-form textarea { width: 100%; min-height: 60px; margin-top: 8px; }
        .reply-form button { background: #ffe45c; border: none; padding: 8px 20px; border-radius: 5px; font-weight: bold; margin-top: 8px; cursor: pointer; }
        .success { color: green; margin-bottom: 16px; }
        .support-header-bg {
            min-height: 300px;
            width: 100%;
            background: url('https://www.svgbackgrounds.com/wp-content/uploads/2021/05/poly-colorful.svg');
            background-size: cover;
            background-position: center;
        }
        .category-cards {
            display: flex;
            gap: 24px;
            justify-content: center;
            align-items: center;
            margin: 40px 0;
            flex-wrap: wrap;
        }
        .category-card {
            background: #ffffffff; /* warna kelabu lembut */
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(44,62,80,0.06);
            padding: 20px 12px;    /* tambah sedikit padding */
            width: 200px;         /* besarkan sedikit dari kecil */
            text-align: center;
            transition: box-shadow 0.2s;
            border: 3px solid #ffeb9c;
        }
        .category-card:hover {
            box-shadow: 0 4px 16px rgba(44,62,80,0.12);
        }
        .category-icon {
            background: #fff;           /* warna putih atau boleh tukar warna lain */
            border-radius: 50%;         /* bulat */
            width: 90px;                /* saiz bulatan */
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;   /* center dan jarak bawah */
            box-shadow: 0 2px 8px rgba(44,62,80,0.06); /* opsyenal: bayang lembut */
        }
        .category-icon img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }
        .category-title {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 8px;
            color: #222;
        }
        .category-link {
            color: #2563ff;
            text-decoration: none;
            font-size: 1rem;
            transition: text-decoration 0.2s;
            display: inline-block;
            padding: 5px 0;
            min-height: 44px;
            line-height: 1.4;
        }
        .category-link:hover {
            text-decoration: underline;
        }
        /* Tambah pada CSS anda */
        .help-title {
            text-align: center;
            width: 100%;
            margin: 0 auto 24px auto;
            display: block;
        }

        /* Back Button Styles */
        .btn-back {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #90caf9;
            color: #2c3e50;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.5rem;
            box-shadow: 0 2px 8px rgba(144, 202, 249, 0.3);
            transition: all 0.3s ease;
            border: none;
            z-index: 1000;
            padding: 0;
        }

        .btn-back:hover {
            background: #bbdefb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(144, 202, 249, 0.4);
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            html, body {
                overflow: auto;
                height: auto;
            }

            .btn-back {
                position: relative;
                top: auto;
                left: auto;
                margin: 0 15px 15px 15px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                font-size: 1.1rem;
                border-radius: 50%;
                padding: 0;
            }

            .help-header {
                padding: 20px 15px;
                height: auto;
                min-height: 200px;
            }

            .help-title {
                font-size: 1.5rem;
                margin-bottom: 15px;
            }

            .help-subtitle {
                font-size: 1rem;
                margin-bottom: 20px;
            }

            .search-bar {
                flex-direction: column;
                gap: 10px;
                align-items: center;
            }

            .search-bar input[type="text"] {
                width: 90%;
                max-width: 300px;
                border-radius: 4px;
            }

            .search-bar button {
                width: 90%;
                max-width: 300px;
                border-radius: 4px;
            }

            .main-content {
                width: 95%;
                padding: 15px 20px;
                margin: 10px auto;
                border-radius: 15px;
            }

            .tickets-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 0 -20px; /* Extend to screen edges */
                padding: 0 20px;
            }

            .tickets-wrapper {
                min-width: 600px; /* Mobile minimum width */
            }

            .main-content h2 {
                font-size: 1.3rem;
                margin-bottom: 20px;
            }

            /* Support Tickets Mobile Optimization */
            .ticket {
                padding: 15px 0;
                border-bottom: 1px solid #eee;
                word-break: break-word;
                overflow-wrap: break-word;
            }

            .ticket strong {
                font-size: 1rem;
                display: block;
                margin-bottom: 8px;
                line-height: 1.3;
            }

            .ticket-status {
                font-size: 0.8rem;
                padding: 3px 8px;
                margin-left: 0;
                margin-top: 5px;
                display: inline-block;
            }

            .ticket div {
                font-size: 0.9rem;
                line-height: 1.4;
                margin-bottom: 8px;
            }

            .reply-form {
                margin-top: 15px;
                width: 100%;
            }

            .reply-form textarea {
                width: 100%;
                min-height: 80px;
                padding: 10px;
                border-radius: 8px;
                border: 1px solid #ddd;
                font-size: 0.9rem;
                resize: vertical;
                box-sizing: border-box;
            }

            .reply-form button {
                padding: 10px 20px;
                font-size: 0.9rem;
                margin-top: 10px;
                border-radius: 8px;
                width: auto;
                min-width: 120px;
            }

            /* Category Cards Mobile Optimization */
            .category-cards {
                flex-direction: column;
                gap: 15px;
                margin: 30px 0;
                align-items: center;
                justify-content: center;
                display: flex;
            }

            .category-card {
                width: 90%;
                max-width: 300px;
                padding: 20px 15px;
                border-radius: 12px;
                margin: 0 auto;
            }

            .category-icon {
                width: 70px;
                height: 70px;
                margin-bottom: 15px;
            }

            .category-icon img {
                width: 42px;
                height: 42px;
            }

            .category-title {
                font-size: 1rem;
                margin-bottom: 8px;
            }

            .category-link {
                font-size: 0.9rem;
            }

            .line-left,
            .line-right {
                width: 60px;
                height: 60px;
            }
        }

        @media (max-width: 480px) {
            .help-header {
                padding: 15px 10px;
                min-height: 150px;
            }

            .help-title {
                font-size: 1.3rem;
            }

            .help-subtitle {
                font-size: 0.9rem;
            }

            .search-bar input[type="text"] {
                width: 95%;
                max-width: none;
                padding: 12px 15px;
            }

            .search-bar button {
                width: 95%;
                max-width: none;
                padding: 12px 15px;
            }

            .btn-back {
                margin: 0 10px 10px 10px;
                width: 36px;
                height: 36px;
                font-size: 0.85rem;
                border-radius: 50%;
                padding: 0;
            }

            .main-content {
                width: 98%;
                padding: 10px 15px;
                margin: 5px auto;
                border-radius: 12px;
            }

            .tickets-container {
                margin: 0 -15px;
                padding: 0 15px;
            }

            .tickets-wrapper {
                min-width: 500px;
            }

            /* Add scroll indicator */
            .tickets-container::after {
                content: "← Swipe untuk lihat lebih →";
                display: block;
                text-align: center;
                font-size: 0.7rem;
                color: #666;
                padding: 5px 0;
                background: rgba(144, 202, 249, 0.1);
                margin-top: 10px;
                border-radius: 4px;
            }

            .main-content h2 {
                font-size: 1.2rem;
                margin-bottom: 15px;
            }

            /* Support Tickets Extra Small Mobile */
            .ticket {
                padding: 12px 0;
                border-left: 3px solid #ffeb9c;
                padding-left: 12px;
                margin-bottom: 15px;
            }

            .ticket strong {
                font-size: 0.95rem;
                line-height: 1.3;
                color: #333;
                font-weight: 600;
            }

            .ticket-status {
                font-size: 0.75rem;
                padding: 2px 6px;
                border-radius: 10px;
                margin-top: 8px;
            }

            .ticket div {
                font-size: 0.85rem;
                line-height: 1.4;
                margin-bottom: 8px;
            }

            .ticket div b {
                font-weight: 600;
                color: #444;
            }

            .reply-form {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                margin-top: 15px;
            }

            .reply-form label {
                font-weight: 600;
                color: #333;
                margin-bottom: 8px;
                display: block;
            }

            .reply-form textarea {
                min-height: 70px;
                padding: 8px;
                font-size: 0.85rem;
                width: 100%;
                border: 1px solid #ddd;
                border-radius: 6px;
            }

            .reply-form button {
                padding: 8px 16px;
                font-size: 0.85rem;
                width: 100%;
                background: #ffeb9c;
                border: none;
                border-radius: 6px;
                font-weight: 600;
                margin-top: 10px;
                cursor: pointer;
                transition: background 0.2s;
            }

            .reply-form button:hover {
                background: #ffe45c;
            }

            /* Category Cards Extra Small Mobile */
            .category-cards {
                gap: 12px;
                margin: 20px 0;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                display: flex;
            }

            .category-card {
                width: 95%;
                padding: 15px 12px;
                border-radius: 10px;
                margin: 0 auto;
            }

            .category-icon {
                width: 60px;
                height: 60px;
                margin-bottom: 12px;
            }

            .category-icon img {
                width: 36px;
                height: 36px;
            }

            .category-title {
                font-size: 0.95rem;
                margin-bottom: 6px;
            }

            .category-link {
                font-size: 0.85rem;
                word-break: break-all;
            }

            .line-left,
            .line-right {
                width: 40px;
                height: 40px;
            }

            .success {
                font-size: 0.9rem;
                padding: 8px 12px;
                border-radius: 6px;
                background: #d4edda;
                border: 1px solid #c3e6cb;
                margin-bottom: 15px;
            }

            /* Admin reply styling for mobile */
            .ticket div[style*="background: #f9f9f9"] {
                background: #f0f8ff !important;
                padding: 12px !important;
                border-radius: 8px !important;
                margin: 10px 0 !important;
                border-left: 3px solid #90caf9 !important;
            }
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="btn-back" aria-label="Back">
        <svg width="28" height="28" viewBox="0 0 28 28" style="display:block; margin:auto;" xmlns="http://www.w3.org/2000/svg">
            <polygon points="19,7 19,21 7,14" fill="#fff" />
        </svg>
    </a>
    <div class="help-header">
        <svg class="line-left" viewBox="0 0 80 80">
            <path d="M10,60 Q30,10 70,40" stroke="#fff" stroke-width="2" fill="none" />
        </svg>
        <svg class="line-right" viewBox="0 0 80 80">
            <path d="M10,20 Q50,60 70,10" stroke="#fff" stroke-width="2" fill="none" />
        </svg>
        <h1 class="help-title">What can we help you with?</h1>
    </div>
    <div class="main-content">
        <h2>Help & Support</h2>
        <?php if (!empty($success_message)): ?>
            <div class="success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (empty($tickets)): ?>
            <!-- <p>Tiada tiket sokongan.</p> -->
        <?php else: ?>
            <div class="tickets-container">
                <div class="tickets-wrapper">
                    <?php foreach ($tickets as $ticket): ?>
                        <div class="ticket">
                            <strong><?php echo htmlspecialchars($ticket['subject']); ?></strong>
                            <span class="ticket-status <?php echo $ticket['status']; ?>">
                                <?php echo ucfirst($ticket['status']); ?>
                            </span>
                            <div style="margin: 8px 0 4px 0;">
                                <b>Daripada:</b> <?php echo htmlspecialchars($ticket['user_email']); ?>
                                <br>
                                <b>Tarikh:</b> <?php echo $ticket['created_at']; ?>
                            </div>
                            <div style="margin-bottom: 8px;"><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></div>
                            <?php if ($ticket['admin_reply']): ?>
                                <div style="background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 8px;">
                                    <b>Balasan Admin:</b><br>
                                    <?php echo nl2br(htmlspecialchars($ticket['admin_reply'])); ?>
                                </div>
                            <?php elseif ($ticket['status'] == 'open'): ?>
                                <form method="post" class="reply-form">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                    <label>Balas:</label>
                                    <textarea name="reply_message" required></textarea>
                                    <button type="submit" name="reply">Hantar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="category-cards">
            <div class="category-card">
                <div class="category-icon" style="background:#f7d6ef;">
                    <img src="ads5/juruteknik-logo.png" alt="Juruteknik" style="width:72px; height:72px;">
                </div>
                <div class="category-title">Juruteknik</div>
                <a href="#">010-12345678</a>
            </div>
            <div class="category-card">
                <div class="category-icon" style="background:#f0f9e7;">
                    <img src="ads6/email.juruteknik.png" alt="Email" style="width:72px; height:72px;">
                </div>
                <div class="category-title">Juruteknik</div>
                <a href="#">juruteknik@gmail.com</a>
            </div>
            <div class="category-card">
                <div class="category-icon" style="background:#e6f6fa;">
                    <img src="ads7/logo.jam.png" alt="Masa" style="width:72px; height:72px;">
                </div>
                <div class="category-title">Masa</div>
                <a href="#">9 pagi - 4 petang</a>
            </div>
        </div>
    </div>
</body>
</html>