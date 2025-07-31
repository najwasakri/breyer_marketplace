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
            background: #ffe45c;
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
            border: 3px solid #f1ececff; /* Tambah border kuning */
        }
        h2 { margin-bottom: 24px; }
        .ticket { border-bottom: 1px solid #eee; padding: 16px 0; }
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
            margin: 40px 0;
        }
        .category-card {
            background: #ffffffff; /* warna kelabu lembut */
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(44,62,80,0.06);
            padding: 20px 12px;    /* tambah sedikit padding */
            width: 200px;         /* besarkan sedikit dari kecil */
            text-align: center;
            transition: box-shadow 0.2s;
            border: 3px solid #ffe45c;
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
    </style>
</head>
<body>
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