<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'includes/db_connect.php';

$success_message = '';
$error_message = '';

if (isset($_POST['reply'], $_POST['ticket_id'])) {
    $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
    $reply = trim($_POST['reply_message'] ?? '');

    if ($ticket_id <= 0 || $reply === '') {
        $error_message = 'Balasan tidak sah. Sila semak semula maklumat tiket.';
    } else {
        $replyStmt = $conn->prepare("UPDATE support_tickets SET admin_reply = ?, status = 'closed' WHERE id = ?");

        if ($replyStmt) {
            $replyStmt->bind_param('si', $reply, $ticket_id);

            if ($replyStmt->execute()) {
                $success_message = 'Balasan berjaya dihantar dan tiket telah ditutup.';
            } else {
                $error_message = 'Balasan gagal dihantar. Sila cuba lagi.';
            }

            $replyStmt->close();
        } else {
            $error_message = 'Sistem tidak dapat memproses balasan buat masa ini.';
        }
    }
}

$tickets = [];
$stats = [
    'total' => 0,
    'open' => 0,
    'closed' => 0
];

$sql = "SELECT * FROM support_tickets
        ORDER BY CASE WHEN status = 'open' THEN 0 ELSE 1 END, created_at DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
        $stats['total']++;

        if (($row['status'] ?? '') === 'open') {
            $stats['open']++;
        } else {
            $stats['closed']++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - Admin</title>
    <style>
        :root {
            --bg: #f7f4ea;
            --panel: #ffffff;
            --panel-soft: #fffaf0;
            --line: #eadfbe;
            --text: #1f2937;
            --muted: #6b7280;
            --accent: #ffd43b;
            --accent-deep: #f59f00;
            --accent-soft: #fff3bf;
            --danger: #ef4444;
            --danger-deep: #dc2626;
            --ok: #15803d;
            --ok-soft: #dcfce7;
            --shadow: 0 20px 40px rgba(84, 66, 15, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(255, 212, 59, 0.22), transparent 28%),
                linear-gradient(180deg, #fffdf6 0%, #f7f4ea 100%);
            font-family: 'Poppins', Arial, sans-serif;
            color: var(--text);
        }

        a {
            color: inherit;
        }

        .help-header {
            background: linear-gradient(135deg, #fff7d1 0%, #ffe27a 100%);
            padding: 32px 0 48px;
            position: relative;
            overflow: hidden;
        }

        .help-shell {
            width: min(1200px, calc(100% - 32px));
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .hero-copy {
            max-width: 720px;
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.55);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #7c5b00;
            margin-bottom: 14px;
        }

        .search-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            max-width: 720px;
        }

        .search-bar input[type="text"] {
            flex: 1;
            min-width: 240px;
            padding: 15px 18px;
            border: 1px solid rgba(124, 91, 0, 0.15);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.92);
            font-size: 1rem;
            outline: none;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .search-bar button {
            padding: 15px 22px;
            border: none;
            border-radius: 14px;
            background: #111827;
            color: #fff;
            font-size: 0.98rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .search-bar button:hover {
            background: #000;
            transform: translateY(-1px);
        }

        .help-title {
            margin: 0 0 12px;
            font-size: clamp(2rem, 4vw, 3.25rem);
            line-height: 1.05;
            font-weight: 800;
            color: #221a00;
        }

        .help-subtitle {
            margin: 0 0 22px;
            max-width: 640px;
            color: rgba(34, 26, 0, 0.75);
            font-size: 1rem;
            line-height: 1.65;
        }

        .results-caption {
            margin-top: 10px;
            color: rgba(34, 26, 0, 0.7);
            font-size: 0.92rem;
        }

        .line-left, .line-right {
            position: absolute;
            width: 80px;
            height: 80px;
            pointer-events: none;
            opacity: 0.45;
        }

        .line-left {
            left: 8%;
            top: 30px;
        }
        .line-right {
            right: 8%;
            top: 10px;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.72);
            color: #4b3600;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 10px 25px rgba(124, 91, 0, 0.12);
            transition: transform 0.2s ease, background 0.2s ease;
            flex-shrink: 0;
        }

        .btn-back:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.92);
        }

        .main-content {
            width: min(1200px, calc(100% - 32px));
            margin: -26px auto 32px;
            display: grid;
            grid-template-columns: minmax(0, 1.8fr) minmax(280px, 0.9fr);
            gap: 24px;
            align-items: start;
            position: relative;
            z-index: 2;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: var(--shadow);
        }

        .ticket-panel {
            padding: 26px;
        }

        .support-panel {
            padding: 24px;
            background: linear-gradient(180deg, #fffdf7 0%, #fff8e4 100%);
            position: sticky;
            top: 20px;
        }

        .panel-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .panel-head h2,
        .support-panel h2 {
            margin: 0;
            font-size: 1.5rem;
            color: #221a00;
        }

        .panel-head p,
        .support-panel p {
            margin: 8px 0 0;
            color: var(--muted);
            line-height: 1.55;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            width: 100%;
        }

        .stat-card {
            background: var(--panel-soft);
            border: 1px solid #f1e5bd;
            border-radius: 18px;
            padding: 14px 16px;
        }

        .stat-label {
            display: block;
            color: var(--muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 1.45rem;
            font-weight: 800;
            color: #221a00;
        }

        .feedback {
            padding: 14px 16px;
            border-radius: 16px;
            margin-bottom: 18px;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .feedback.success {
            background: var(--ok-soft);
            color: var(--ok);
            border: 1px solid #86efac;
        }

        .feedback.error {
            background: #fee2e2;
            color: var(--danger-deep);
            border: 1px solid #fca5a5;
        }

        .ticket-list {
            display: grid;
            gap: 16px;
        }

        .ticket-card {
            border: 1px solid #efe7cf;
            border-radius: 20px;
            padding: 18px 18px 16px;
            background: linear-gradient(180deg, #fffdf8 0%, #ffffff 100%);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .ticket-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(84, 66, 15, 0.07);
        }

        .ticket-card-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .ticket-card h3 {
            margin: 0 0 8px;
            font-size: 1.1rem;
            line-height: 1.4;
            color: #1f1a00;
        }

        .ticket-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 12px;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #faf6ea;
            border: 1px solid #eee4c7;
        }

        .ticket-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .status-open {
            background: #fff3bf;
            color: #7c5b00;
        }

        .status-closed {
            background: #e5e7eb;
            color: #374151;
        }

        .ticket-message,
        .admin-reply-body {
            font-size: 0.96rem;
            line-height: 1.72;
            color: #2a3140;
            white-space: normal;
            word-break: break-word;
        }

        .ticket-message {
            margin-bottom: 14px;
        }

        .admin-reply {
            background: #f7fbff;
            border: 1px solid #d8e7ff;
            border-radius: 16px;
            padding: 14px 16px;
            margin-top: 14px;
        }

        .admin-reply-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 0.86rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #2454a6;
        }

        .reply-form {
            margin-top: 14px;
            padding: 16px;
            border: 1px solid #efe3b7;
            border-radius: 16px;
            background: #fffdf6;
        }

        .reply-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #4b3600;
        }

        .reply-form textarea {
            width: 100%;
            min-height: 120px;
            border: 1px solid #e5d7a4;
            border-radius: 14px;
            padding: 14px 16px;
            font: inherit;
            resize: vertical;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .reply-form textarea:focus,
        .search-bar input[type="text"]:focus {
            border-color: #f59f00;
            box-shadow: 0 0 0 4px rgba(245, 159, 0, 0.12);
        }

        .reply-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 12px;
        }

        .reply-form button {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-deep) 100%);
            color: #1f1a00;
            border: none;
            padding: 11px 18px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 0.96rem;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 12px 20px rgba(245, 159, 0, 0.2);
        }

        .reply-form button:hover {
            transform: translateY(-1px);
        }

        .empty-state {
            padding: 36px 20px;
            text-align: center;
            border: 1px dashed #e6d6a0;
            border-radius: 20px;
            background: #fffdf6;
        }

        .empty-state h3 {
            margin: 0 0 8px;
            font-size: 1.15rem;
        }

        .empty-state p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .contact-list {
            display: grid;
            gap: 14px;
            margin-top: 20px;
        }

        .contact-card {
            display: grid;
            grid-template-columns: 58px 1fr;
            gap: 14px;
            align-items: center;
            padding: 14px;
            border: 1px solid #f0e4bc;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.82);
        }

        .contact-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
        }

        .contact-icon img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .contact-card h3 {
            margin: 0 0 4px;
            font-size: 1rem;
        }

        .contact-card p,
        .contact-card a {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
            text-decoration: none;
        }

        .contact-card a:hover {
            color: #1f1a00;
        }

        .support-note {
            margin-top: 20px;
            padding: 16px;
            border-radius: 18px;
            background: #1f2937;
            color: #fff;
        }

        .support-note h3 {
            margin: 0 0 8px;
            font-size: 1rem;
        }

        .support-note p {
            margin: 0;
            color: rgba(255, 255, 255, 0.8);
        }

        @media (max-width: 768px) {
            .btn-back {
                width: 44px;
                height: 44px;
            }

            .help-header {
                padding: 24px 0 40px;
            }

            .main-content {
                grid-template-columns: 1fr;
                width: min(100%, calc(100% - 20px));
                margin-top: -18px;
                gap: 18px;
            }

            .search-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-bar input[type="text"] {
                width: 100%;
            }

            .search-bar button {
                width: 100%;
            }

            .ticket-panel,
            .support-panel {
                padding: 20px;
            }

            .panel-head {
                margin-bottom: 16px;
            }

            .stat-grid {
                grid-template-columns: 1fr;
            }

            .ticket-card-head {
                flex-direction: column;
            }

            .reply-actions {
                justify-content: stretch;
            }

            .reply-form button {
                width: 100%;
            }

            .contact-card {
                grid-template-columns: 52px 1fr;
            }

            .contact-icon {
                width: 52px;
                height: 52px;
            }

            .contact-icon img {
                width: 34px;
                height: 34px;
            }

            .support-panel {
                position: static;
            }

            .line-left,
            .line-right {
                width: 56px;
                height: 56px;
            }
        }

        @media (max-width: 480px) {
            .help-header {
                padding: 20px 0 36px;
            }

            .help-shell,
            .main-content {
                width: min(100%, calc(100% - 16px));
            }

            .search-bar button {
                padding: 12px 15px;
            }

            .btn-back {
                width: 36px;
                height: 36px;
            }

            .ticket-panel,
            .support-panel {
                padding: 16px;
            }

            .panel-head h2,
            .support-panel h2 {
                font-size: 1.3rem;
            }

            .ticket-card {
                padding: 15px;
            }

            .ticket-status {
                font-size: 0.75rem;
                padding: 7px 10px;
            }

            .ticket-meta,
            .ticket-message,
            .admin-reply-body,
            .contact-card p,
            .contact-card a,
            .support-note p {
                font-size: 0.88rem;
            }

            .reply-form {
                padding: 14px;
            }

            .reply-form textarea {
                min-height: 100px;
                padding: 12px 14px;
            }

            .line-left,
            .line-right {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="help-header">
        <svg class="line-left" viewBox="0 0 80 80">
            <path d="M10,60 Q30,10 70,40" stroke="#7c5b00" stroke-width="2" fill="none" />
        </svg>
        <svg class="line-right" viewBox="0 0 80 80">
            <path d="M10,20 Q50,60 70,10" stroke="#7c5b00" stroke-width="2" fill="none" />
        </svg>

        <div class="help-shell">
            <div class="hero-top">
                <a href="dashboard.php" class="btn-back" aria-label="Back">
                    <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14.75 5.5L8.25 12l6.5 6.5" fill="none" stroke="#4b3600" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"/>
                    </svg>
                </a>
            </div>

            <div class="hero-copy">
                <div class="hero-kicker">Admin Support Desk</div>
                <h1 class="help-title">Urus tiket bantuan dengan lebih teratur.</h1>
                <p class="help-subtitle">Semak isu pelajar, balas terus dari halaman ini, dan pantau tiket yang masih terbuka tanpa perlu skrol dalam paparan yang sempit atau berserabut.</p>
            </div>

            <div class="search-bar">
                <input type="text" id="ticketSearch" placeholder="Cari subjek, emel, mesej atau status tiket...">
                <button type="button" id="searchReset">Kosongkan Carian</button>
            </div>
            <div class="results-caption" id="resultsCaption">Memaparkan <?php echo count($tickets); ?> tiket.</div>
        </div>
    </div>

    <div class="main-content">
        <section class="panel ticket-panel">
            <div class="panel-head">
                <div>
                    <h2>Senarai Tiket Sokongan</h2>
                    <p>Fokus pada tiket yang masih `open`, kemudian tutup tiket secara automatik selepas anda membalas.</p>
                </div>
                <div class="stat-grid">
                    <div class="stat-card">
                        <span class="stat-label">Jumlah Tiket</span>
                        <span class="stat-value"><?php echo $stats['total']; ?></span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Masih Open</span>
                        <span class="stat-value"><?php echo $stats['open']; ?></span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Selesai</span>
                        <span class="stat-value"><?php echo $stats['closed']; ?></span>
                    </div>
                </div>
            </div>

            <?php if ($success_message !== ''): ?>
                <div class="feedback success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if ($error_message !== ''): ?>
                <div class="feedback error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (empty($tickets)): ?>
                <div class="empty-state">
                    <h3>Tiada tiket sokongan buat masa ini.</h3>
                    <p>Semua permintaan bantuan sudah kosong. Halaman ini akan terisi semula apabila pelajar menghantar tiket baru.</p>
                </div>
            <?php else: ?>
                <div class="ticket-list" id="ticketList">
                    <?php foreach ($tickets as $ticket): ?>
                        <?php
                            $status = ($ticket['status'] ?? '') === 'open' ? 'open' : 'closed';
                            $statusLabel = $status === 'open' ? 'Open' : 'Closed';
                            $searchBlob = strtolower(trim(implode(' ', [
                                $ticket['subject'] ?? '',
                                $ticket['user_email'] ?? '',
                                $ticket['message'] ?? '',
                                $ticket['admin_reply'] ?? '',
                                $statusLabel
                            ])));
                        ?>
                        <article class="ticket-card" data-ticket-card data-search="<?php echo htmlspecialchars($searchBlob); ?>">
                            <div class="ticket-card-head">
                                <div>
                                    <h3><?php echo htmlspecialchars($ticket['subject']); ?></h3>
                                    <div class="ticket-meta">
                                        <span class="meta-pill"><strong>ID</strong> #<?php echo (int) $ticket['id']; ?></span>
                                        <span class="meta-pill"><strong>Daripada</strong> <?php echo htmlspecialchars($ticket['user_email']); ?></span>
                                        <span class="meta-pill"><strong>Tarikh</strong> <?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></span>
                                    </div>
                                </div>
                                <span class="ticket-status status-<?php echo $status; ?>"><?php echo $statusLabel; ?></span>
                            </div>

                            <div class="ticket-message"><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></div>

                            <?php if (!empty($ticket['admin_reply'])): ?>
                                <div class="admin-reply">
                                    <div class="admin-reply-title">Balasan Admin</div>
                                    <div class="admin-reply-body"><?php echo nl2br(htmlspecialchars($ticket['admin_reply'])); ?></div>
                                </div>
                            <?php elseif ($status === 'open'): ?>
                                <form method="post" class="reply-form">
                                    <input type="hidden" name="ticket_id" value="<?php echo (int) $ticket['id']; ?>">
                                    <label for="reply-<?php echo (int) $ticket['id']; ?>">Balas tiket ini</label>
                                    <textarea id="reply-<?php echo (int) $ticket['id']; ?>" name="reply_message" placeholder="Tulis balasan yang jelas dan terus kepada pengguna..." required></textarea>
                                    <div class="reply-actions">
                                        <button type="submit" name="reply">Hantar Balasan</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="empty-state" id="searchEmptyState" hidden>
                    <h3>Tiada tiket yang sepadan dengan carian anda.</h3>
                    <p>Cuba kata kunci lain seperti subjek, emel, status atau sebahagian kandungan mesej.</p>
                </div>
            <?php endif; ?>
        </section>

        <aside class="panel support-panel">
            <h2>Saluran Sokongan</h2>
            <p>Maklumat ringkas untuk tindakan susulan jika isu tidak dapat diselesaikan melalui balasan tiket biasa.</p>

            <div class="contact-list">
                <div class="contact-card">
                    <div class="contact-icon" style="background:#f7d6ef;">
                        <img src="ads5/juruteknik-logo.png" alt="Juruteknik">
                    </div>
                    <div>
                        <h3>Juruteknik</h3>
                        <a href="tel:01012345678">010-12345678</a>
                    </div>
                </div>

                <div class="contact-card">
                    <div class="contact-icon" style="background:#f0f9e7;">
                        <img src="ads6/email.juruteknik.png" alt="Email Juruteknik">
                    </div>
                    <div>
                        <h3>Email Sokongan</h3>
                        <a href="mailto:juruteknik@gmail.com">juruteknik@gmail.com</a>
                    </div>
                </div>

                <div class="contact-card">
                    <div class="contact-icon" style="background:#e6f6fa;">
                        <img src="ads7/logo.jam.png" alt="Waktu Operasi">
                    </div>
                    <div>
                        <h3>Waktu Operasi</h3>
                        <p>Isnin hingga Jumaat<br>9 pagi - 4 petang</p>
                    </div>
                </div>
            </div>

            <div class="support-note">
                <h3>Nota Penting</h3>
                <p>Selepas balasan dihantar, tiket akan ditukar kepada `closed`. Jika isu masih berlanjutan, pengguna perlu buka tiket baru untuk susulan seterusnya.</p>
            </div>
        </aside>
    </div>

    <script>
        const searchInput = document.getElementById('ticketSearch');
        const searchReset = document.getElementById('searchReset');
        const ticketCards = Array.from(document.querySelectorAll('[data-ticket-card]'));
        const resultsCaption = document.getElementById('resultsCaption');
        const searchEmptyState = document.getElementById('searchEmptyState');

        function updateTicketFilter() {
            const keyword = (searchInput?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            ticketCards.forEach((card) => {
                const haystack = card.dataset.search || '';
                const matches = keyword === '' || haystack.includes(keyword);
                card.hidden = !matches;

                if (matches) {
                    visibleCount += 1;
                }
            });

            if (resultsCaption) {
                resultsCaption.textContent = keyword === ''
                    ? `Memaparkan ${visibleCount} tiket.`
                    : `Jumpa ${visibleCount} tiket untuk carian "${searchInput.value.trim()}".`;
            }

            if (searchEmptyState) {
                searchEmptyState.hidden = visibleCount !== 0 || ticketCards.length === 0;
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', updateTicketFilter);
        }

        if (searchReset) {
            searchReset.addEventListener('click', () => {
                if (!searchInput) {
                    return;
                }

                searchInput.value = '';
                updateTicketFilter();
                searchInput.focus();
            });
        }
    </script>
<?php
require_once __DIR__ . '/includes/admin_notifications.php';
render_admin_notification_center();
?>
</body>
</html>