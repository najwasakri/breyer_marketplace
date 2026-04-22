<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'db_connect.php';
require_once dirname(__DIR__) . '/manual_payment_helpers.php';

ensureManualPaymentColumnsPDO($pdo);

$manualTransferConfig = getManualTransferConfig();
$flashMessage = $_SESSION['payment_history_flash'] ?? null;
unset($_SESSION['payment_history_flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_payment_proof'])) {
    $paymentId = filter_var($_POST['payment_id'] ?? null, FILTER_VALIDATE_INT);
    $paymentChannel = trim($_POST['payment_channel'] ?? 'manual_banking');
    $payerName = trim($_POST['payer_name'] ?? '');
    $paymentReference = trim($_POST['payment_reference'] ?? '');
    $proofFile = $_FILES['proof_image'] ?? null;

    $paymentChannelMap = [
        'manual_banking' => [
            'method' => $manualTransferConfig['manual_banking_label'],
            'bank_name' => $manualTransferConfig['bank_name'],
        ],
        'duitnow_qr' => [
            'method' => $manualTransferConfig['duitnow_qr_label'],
            'bank_name' => $manualTransferConfig['duitnow_qr_label'],
        ],
    ];

    if (!isset($paymentChannelMap[$paymentChannel])) {
        $_SESSION['payment_history_flash'] = [
            'type' => 'error',
            'message' => 'Kaedah bayaran yang dipilih tidak sah.'
        ];
        header('Location: payment_history.php');
        exit;
    }

    if ($paymentId === false || $paymentId <= 0) {
        $_SESSION['payment_history_flash'] = [
            'type' => 'error',
            'message' => 'Rekod pembayaran tidak sah.'
        ];
        header('Location: payment_history.php');
        exit;
    }

    if ($payerName === '' || $paymentReference === '' || !$proofFile || ($proofFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $_SESSION['payment_history_flash'] = [
            'type' => 'error',
            'message' => 'Sila lengkapkan nama pengirim, rujukan bayaran dan fail bukti bayaran.'
        ];
        header('Location: payment_history.php');
        exit;
    }

    $paymentStmt = $pdo->prepare(
        'SELECT id, status, proof_image_path
         FROM payments
         WHERE id = ? AND user_id = ?
         LIMIT 1'
    );
    $paymentStmt->execute([$paymentId, $_SESSION['user_id']]);
    $payment = $paymentStmt->fetch();

    if (!$payment) {
        $_SESSION['payment_history_flash'] = [
            'type' => 'error',
            'message' => 'Pembayaran yang dipilih tidak ditemui.'
        ];
        header('Location: payment_history.php');
        exit;
    }

    if (($payment['status'] ?? '') === 'Selesai') {
        $_SESSION['payment_history_flash'] = [
            'type' => 'error',
            'message' => 'Pembayaran yang telah disahkan tidak boleh diganti bukti bayaran.'
        ];
        header('Location: payment_history.php');
        exit;
    }

    $originalName = $proofFile['name'] ?? '';
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowedExtensions, true)) {
        $_SESSION['payment_history_flash'] = [
            'type' => 'error',
            'message' => 'Hanya fail JPG, JPEG, PNG atau WEBP dibenarkan.'
        ];
        header('Location: payment_history.php');
        exit;
    }

    $temporaryFile = $proofFile['tmp_name'] ?? '';
    if ($temporaryFile === '' || @getimagesize($temporaryFile) === false) {
        $_SESSION['payment_history_flash'] = [
            'type' => 'error',
            'message' => 'Fail bukti bayaran tidak sah atau rosak.'
        ];
        header('Location: payment_history.php');
        exit;
    }

    if (($proofFile['size'] ?? 0) > 5 * 1024 * 1024) {
        $_SESSION['payment_history_flash'] = [
            'type' => 'error',
            'message' => 'Saiz fail terlalu besar. Maksimum 5MB.'
        ];
        header('Location: payment_history.php');
        exit;
    }

    $uploadDirectory = getManualPaymentUploadDirectory();
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true) && !is_dir($uploadDirectory)) {
        $_SESSION['payment_history_flash'] = [
            'type' => 'error',
            'message' => 'Folder upload tidak dapat disediakan buat masa ini.'
        ];
        header('Location: payment_history.php');
        exit;
    }

    $targetFileName = 'proof_' . $paymentId . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $targetFilePath = $uploadDirectory . DIRECTORY_SEPARATOR . $targetFileName;
    $publicPath = getManualPaymentPublicPath($targetFileName);

    if (!move_uploaded_file($temporaryFile, $targetFilePath)) {
        $_SESSION['payment_history_flash'] = [
            'type' => 'error',
            'message' => 'Bukti bayaran gagal dimuat naik. Sila cuba lagi.'
        ];
        header('Location: payment_history.php');
        exit;
    }

    if (!empty($payment['proof_image_path'])) {
        $oldFilePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim(str_replace('../', '', $payment['proof_image_path']), '/\\');
        if (is_file($oldFilePath) && strpos(realpath($oldFilePath) ?: '', realpath($uploadDirectory) ?: '') === 0) {
            @unlink($oldFilePath);
        }
    }

    $updateStmt = $pdo->prepare(
        'UPDATE payments
         SET payment_method = ?,
             manual_bank_name = ?,
             payment_reference = ?,
             payer_name = ?,
             proof_image_path = ?,
             proof_uploaded_at = NOW(),
             verification_status = ?,
             verification_notes = NULL
         WHERE id = ? AND user_id = ?'
    );
    $updateStmt->execute([
        $paymentChannelMap[$paymentChannel]['method'],
        $paymentChannelMap[$paymentChannel]['bank_name'],
        $paymentReference,
        $payerName,
        $publicPath,
        'uploaded',
        $paymentId,
        $_SESSION['user_id'],
    ]);

    $_SESSION['payment_history_flash'] = [
        'type' => 'success',
        'message' => 'Bukti bayaran berjaya dimuat naik. Admin akan menyemak pesanan anda.'
    ];
    header('Location: payment_history.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, item_name, item_category, item_size, quantity, amount, order_date, status, transaction_id, bank_code, fpx_bank_id, customer_class, customer_phone,
            payment_method, manual_bank_name, payment_reference, payer_name, proof_image_path, proof_uploaded_at, verification_status, verification_notes
     FROM payments
     WHERE user_id = ?
     ORDER BY created_at DESC, id DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sejarah Pembayaran - Breyer</title>
    <style>
        body {
            background: linear-gradient(135deg, #D4E5FF 0%, #3B7DD3 100%);
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .history-container {
            max-width: 980px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .history-header {
            text-align: center;
            margin-bottom: 30px;
            color: #003B95;
        }
        .flash-message {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: bold;
        }
        .flash-success {
            background: #e8f7ec;
            color: #166534;
            border: 1px solid #86efac;
        }
        .flash-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .manual-transfer-card {
            background: linear-gradient(145deg, #eef6ff 0%, #d8e9ff 52%, #eef8ff 100%);
            border: 1px solid #93c5fd;
            border-radius: 22px;
            padding: 26px;
            margin-bottom: 28px;
            box-shadow: 0 20px 45px rgba(29, 78, 216, 0.12);
        }
        .manual-transfer-hero {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }
        .manual-transfer-copy {
            max-width: 620px;
        }
        .manual-transfer-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,0.92);
            color: #1d4ed8;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            box-shadow: 0 8px 18px rgba(30, 64, 175, 0.08);
            margin-bottom: 12px;
        }
        .payment-option-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 18px;
        }
        .payment-option-card {
            background: rgba(255,255,255,0.96);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(0, 59, 149, 0.12);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
            position: relative;
            overflow: hidden;
        }
        .payment-option-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #1d4ed8 0%, #60a5fa 100%);
        }
        .payment-option-card.qr-option::before {
            background: linear-gradient(90deg, #059669 0%, #34d399 100%);
        }
        .payment-option-header {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            margin-bottom: 14px;
        }
        .payment-option-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        }
        .qr-option .payment-option-icon {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }
        .payment-option-meta {
            flex: 1;
        }
        .payment-channel-chip {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.35px;
            text-transform: uppercase;
            color: #1e40af;
            background: #dbeafe;
            margin-bottom: 8px;
        }
        .qr-option .payment-channel-chip {
            color: #047857;
            background: #d1fae5;
        }
        .payment-option-card h3 {
            margin: 0 0 8px;
            color: #003B95;
            font-size: 1.12rem;
        }
        .payment-option-card p {
            margin: 0 0 12px;
            color: #334155;
            line-height: 1.55;
            font-size: 0.93rem;
        }
        .manual-transfer-card h2 {
            margin: 0 0 10px;
            color: #003B95;
            font-size: 1.8rem;
        }
        .manual-transfer-card p {
            color: #1e3a5f;
            margin: 0 0 18px;
            line-height: 1.6;
        }
        .payment-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 12px;
            min-width: 260px;
        }
        .payment-step {
            background: rgba(255,255,255,0.9);
            border-radius: 16px;
            padding: 14px;
            border: 1px solid rgba(29, 78, 216, 0.14);
            box-shadow: 0 10px 22px rgba(30, 64, 175, 0.08);
        }
        .payment-step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #1d4ed8;
            color: #fff;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .payment-step strong {
            display: block;
            color: #0f172a;
            margin-bottom: 6px;
            font-size: 0.95rem;
        }
        .payment-step p {
            margin: 0;
            color: #475569;
            font-size: 0.88rem;
            line-height: 1.55;
        }
        .manual-transfer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
        }
        .manual-transfer-item {
            background: rgba(255,255,255,0.88);
            border-radius: 12px;
            padding: 14px;
            border: 1px solid rgba(0, 59, 149, 0.12);
        }
        .manual-transfer-item span {
            display: block;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #4b5563;
            margin-bottom: 6px;
        }
        .manual-transfer-item strong {
            color: #0f172a;
            font-size: 1rem;
            word-break: break-word;
        }
        .manual-transfer-note {
            margin-top: 16px;
            padding: 16px 18px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(255,255,255,0.96) 0%, rgba(239,246,255,0.95) 100%);
            color: #1f2937;
            line-height: 1.6;
            border: 1px solid rgba(59, 130, 246, 0.18);
        }
        .payment-highlight-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        .payment-highlight {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #dbeafe;
            color: #334155;
            font-size: 0.82rem;
            font-weight: 700;
        }
        .duitnow-qr-preview {
            margin-top: 12px;
            border: 1px dashed #93c5fd;
            border-radius: 14px;
            padding: 14px;
            background: #f8fbff;
            text-align: center;
        }
        .duitnow-qr-preview img {
            width: 100%;
            max-width: 220px;
            border-radius: 12px;
            display: block;
            margin: 0 auto 10px;
            border: 1px solid #cbd5e1;
        }
        .duitnow-qr-empty {
            padding: 20px 14px;
            border-radius: 12px;
            background: #fff7d6;
            color: #8a6200;
            font-weight: 600;
            line-height: 1.6;
        }
        .transaction {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 20px;
            display: grid;
            gap: 18px;
            margin-bottom: 18px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        }
        .transaction:last-child {
            margin-bottom: 0;
        }
        .transaction-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            flex-wrap: wrap;
        }
        .transaction-details h3 {
            color: #003B95;
            margin: 0 0 6px;
            font-size: 1.1rem;
        }
        .transaction-details p {
            color: #555;
            margin: 3px 0;
            font-size: 0.95rem;
            line-height: 1.55;
        }
        .transaction-summary {
            text-align: right;
            min-width: 150px;
        }
        .transaction-amount {
            color: #003B95;
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .transaction-status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .status-waiting {
            background: #fff3cd;
            color: #856404;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        .status-review {
            background: #e0f2fe;
            color: #075985;
        }
        .proof-section {
            border-top: 1px solid #e5e7eb;
            padding-top: 18px;
            display: grid;
            gap: 16px;
        }
        .proof-status-box {
            background: #f8fafc;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            padding: 16px;
        }
        .proof-status-box h4,
        .proof-upload-form h4 {
            margin: 0 0 10px;
            color: #003B95;
            font-size: 1rem;
        }
        .proof-status-box p,
        .proof-upload-form p {
            margin: 4px 0;
            color: #4b5563;
            line-height: 1.55;
            font-size: 0.93rem;
        }
        .proof-preview {
            margin-top: 14px;
        }
        .proof-preview img {
            width: 100%;
            max-width: 260px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            display: block;
        }
        .proof-link {
            display: inline-block;
            margin-top: 10px;
            color: #003B95;
            font-weight: bold;
            text-decoration: none;
        }
        .proof-link:hover {
            text-decoration: underline;
        }
        .proof-upload-form {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 14px;
            padding: 16px;
        }
        .proof-upload-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-top: 14px;
        }
        .proof-upload-field {
            display: grid;
            gap: 6px;
        }
        .proof-upload-field label {
            font-weight: bold;
            color: #1f2937;
            font-size: 0.92rem;
        }
        .proof-upload-field input,
        .proof-upload-field select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 0.94rem;
        }
        .proof-upload-submit {
            margin-top: 14px;
            background: #003B95;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .proof-upload-submit:hover {
            background: #002b70;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        .empty-state h2 {
            color: #003B95;
            margin-bottom: 10px;
        }
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #003B95;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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
            background: #002b70;
            transform: translateX(-3px);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .back-btn {
                width: 45px;
                height: 45px;
                top: 15px;
                left: 15px;
            }

            .history-container {
                margin: 80px auto 20px;
                padding: 20px;
            }

            .manual-transfer-card {
                padding: 20px;
            }

            .manual-transfer-hero {
                flex-direction: column;
            }

            .payment-steps {
                width: 100%;
            }

            .transaction-summary {
                text-align: left;
                min-width: 0;
            }

            .transaction-top {
                flex-direction: column;
            }

            .back-btn::before {
                border-width: 7px 10px 7px 0;
            }
        }

        @media (max-width: 480px) {
            .back-btn {
                width: 40px;
                height: 40px;
                top: 10px;
                left: 10px;
            }

            .back-btn::before {
                border-width: 6px 8px 6px 0;
            }
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-btn" title="Kembali ke Dashboard"></a>
    <div class="history-container">
        <div class="history-header">
            <h1>Sejarah Pembayaran</h1>
        </div>
<?php if ($flashMessage): ?>
        <div class="flash-message <?php echo ($flashMessage['type'] ?? '') === 'success' ? 'flash-success' : 'flash-error'; ?>">
            <?php echo htmlspecialchars($flashMessage['message'] ?? ''); ?>
        </div>
<?php endif; ?>

        <div class="manual-transfer-card">
            <div class="manual-transfer-hero">
                <div class="manual-transfer-copy">
                    <div class="manual-transfer-badge">Pilihan Bayaran Tersedia</div>
                    <h2>Pilihan Bayaran Manual</h2>
                    <p>Pilih saluran yang paling mudah untuk anda. Kedua-dua pilihan di bawah menggunakan semakan bukti bayaran, jadi student hanya perlu selesaikan transaksi dan muat naik slip pada pesanan berkaitan.</p>
                    <div class="payment-highlight-row">
                        <div class="payment-highlight">Rujukan pesanan digunakan untuk semakan</div>
                        <div class="payment-highlight">Bukti bayaran boleh dikemas kini semula</div>
                        <div class="payment-highlight">Admin akan sahkan selepas semakan</div>
                    </div>
                </div>
                <div class="payment-steps">
                    <div class="payment-step">
                        <div class="payment-step-number">1</div>
                        <strong>Pilih Saluran</strong>
                        <p>Pilih sama ada Manual Banking atau scan QR DuitNow.</p>
                    </div>
                    <div class="payment-step">
                        <div class="payment-step-number">2</div>
                        <strong>Buat Pembayaran</strong>
                        <p>Pastikan jumlah bayaran sama seperti jumlah pada pesanan anda.</p>
                    </div>
                    <div class="payment-step">
                        <div class="payment-step-number">3</div>
                        <strong>Muat Naik Slip</strong>
                        <p>Hantar bukti bayaran yang jelas supaya semakan admin lebih cepat.</p>
                    </div>
                </div>
            </div>
            <div class="payment-option-grid">
                <div class="payment-option-card">
                    <div class="payment-option-header">
                        <div class="payment-option-icon">🏦</div>
                        <div class="payment-option-meta">
                            <div class="payment-channel-chip">Akaun Bank</div>
                            <h3><?php echo htmlspecialchars($manualTransferConfig['manual_banking_label']); ?></h3>
                            <p><?php echo htmlspecialchars($manualTransferConfig['instructions']); ?></p>
                        </div>
                    </div>
                    <div class="manual-transfer-grid">
                        <div class="manual-transfer-item">
                            <span>Nama Bank</span>
                            <strong><?php echo htmlspecialchars($manualTransferConfig['bank_name']); ?></strong>
                        </div>
                        <div class="manual-transfer-item">
                            <span>Nama Akaun</span>
                            <strong><?php echo htmlspecialchars($manualTransferConfig['account_name']); ?></strong>
                        </div>
                        <div class="manual-transfer-item">
                            <span>No. Akaun</span>
                            <strong><?php echo htmlspecialchars($manualTransferConfig['account_number']); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="payment-option-card qr-option">
                    <div class="payment-option-header">
                        <div class="payment-option-icon">📱</div>
                        <div class="payment-option-meta">
                            <div class="payment-channel-chip">Scan QR</div>
                            <h3><?php echo htmlspecialchars($manualTransferConfig['duitnow_qr_label']); ?></h3>
                            <p><?php echo htmlspecialchars($manualTransferConfig['duitnow_instructions']); ?></p>
                        </div>
                    </div>
                    <div class="manual-transfer-grid">
                        <div class="manual-transfer-item">
                            <span>Penerima</span>
                            <strong><?php echo htmlspecialchars($manualTransferConfig['duitnow_receiver_name']); ?></strong>
                        </div>
                        <div class="manual-transfer-item">
                            <span>Rujukan</span>
                            <strong>Guna nombor pesanan anda</strong>
                        </div>
                    </div>
                    <div class="duitnow-qr-preview">
<?php if (!empty($manualTransferConfig['duitnow_qr_image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($manualTransferConfig['duitnow_qr_image_path']); ?>" alt="QR DuitNow">
                        <div>Scan kod QR ini menggunakan aplikasi bank atau e-wallet anda.</div>
<?php else: ?>
                        <div class="duitnow-qr-empty">
                            Gambar QR DuitNow belum ditetapkan. Masukkan path QR rasmi anda dalam fail manual_payment_helpers.php pada tetapan duitnow_qr_image_path.
                        </div>
<?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="manual-transfer-note">
                Tip: Gunakan nombor rujukan pesanan semasa bank transfer atau semasa anda muat naik slip supaya semakan admin lebih cepat dan tepat.
            </div>
        </div>

        <div class="transactions">
<?php if (empty($transactions)): ?>
            <div class="empty-state">
                <h2>Belum Ada Rekod</h2>
                <p>Belian anda akan muncul di sini selepas anda membuat pesanan.</p>
            </div>
<?php else: ?>
<?php foreach ($transactions as $transaction): ?>
<?php
    $statusClass = 'status-waiting';
    $statusLabel = $transaction['status'] === 'Selesai' ? 'Dibayar' : $transaction['status'];
    $verificationStatus = $transaction['verification_status'] ?? '';
    if ($transaction['status'] === 'Selesai') {
        $statusClass = 'status-paid';
    } elseif ($transaction['status'] === 'Gagal') {
        $statusClass = 'status-failed';
    } elseif ($verificationStatus === 'uploaded') {
        $statusClass = 'status-review';
        $statusLabel = 'Menunggu Semakan';
    }

    $itemMeta = [];
    if (!empty($transaction['item_category'])) {
        $itemMeta[] = 'Kategori: ' . $transaction['item_category'];
    }
    if (!empty($transaction['item_size'])) {
        $itemMeta[] = 'Saiz: ' . $transaction['item_size'];
    }
    if (!empty($transaction['quantity'])) {
        $itemMeta[] = 'Kuantiti: ' . $transaction['quantity'];
    }
    $bankLabel = $transaction['fpx_bank_id'] ?: $transaction['bank_code'];
    if (!empty($bankLabel)) {
        $itemMeta[] = 'Bank: ' . $bankLabel;
    }
    if (!empty($transaction['customer_class'])) {
        $itemMeta[] = 'Kelas: ' . $transaction['customer_class'];
    }
    if (!empty($transaction['customer_phone'])) {
        $itemMeta[] = 'Telefon: ' . $transaction['customer_phone'];
    }

    $proofUploadedAt = !empty($transaction['proof_uploaded_at']) ? date('d/m/Y h:i A', strtotime($transaction['proof_uploaded_at'])) : null;
?>
            <div class="transaction">
                <div class="transaction-top">
                    <div class="transaction-details">
                        <h3><?php echo htmlspecialchars($transaction['item_name']); ?></h3>
                        <p>Tarikh: <?php echo date('d/m/Y', strtotime($transaction['order_date'])); ?></p>
                        <?php if (!empty($transaction['transaction_id'])): ?>
                        <p>Rujukan Pesanan: <?php echo htmlspecialchars($transaction['transaction_id']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($itemMeta)): ?>
                        <p><?php echo htmlspecialchars(implode(' | ', $itemMeta)); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="transaction-summary">
                        <div class="transaction-amount">RM<?php echo number_format((float) $transaction['amount'], 2); ?></div>
                        <span class="transaction-status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                    </div>
                </div>

                <div class="proof-section">
                    <div class="proof-status-box">
                        <h4>Status Bukti Bayaran</h4>
                        <p>Kaedah: <?php echo htmlspecialchars($transaction['payment_method'] ?: 'Belum dipilih'); ?></p>
                        <p>Bank penerima: <?php echo htmlspecialchars($transaction['manual_bank_name'] ?: $manualTransferConfig['bank_name']); ?></p>
                        <p>Rujukan bayaran: <?php echo htmlspecialchars($transaction['payment_reference'] ?: '-'); ?></p>
                        <p>Nama pengirim: <?php echo htmlspecialchars($transaction['payer_name'] ?: '-'); ?></p>
                        <p>Dimuat naik pada: <?php echo htmlspecialchars($proofUploadedAt ?: '-'); ?></p>
                        <?php if (!empty($transaction['verification_notes'])): ?>
                        <p>Nota semakan: <?php echo htmlspecialchars($transaction['verification_notes']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($transaction['proof_image_path'])): ?>
                        <div class="proof-preview">
                            <img src="<?php echo htmlspecialchars($transaction['proof_image_path']); ?>" alt="Bukti bayaran <?php echo (int) $transaction['id']; ?>">
                            <a href="<?php echo htmlspecialchars($transaction['proof_image_path']); ?>" target="_blank" class="proof-link">Lihat bukti bayaran penuh</a>
                        </div>
                        <?php else: ?>
                        <p>Bukti bayaran belum dimuat naik.</p>
                        <?php endif; ?>
                    </div>

                    <?php if (($transaction['status'] ?? '') !== 'Selesai'): ?>
                    <form method="post" enctype="multipart/form-data" class="proof-upload-form">
                        <input type="hidden" name="upload_payment_proof" value="1">
                        <input type="hidden" name="payment_id" value="<?php echo (int) $transaction['id']; ?>">
                        <h4>Muat Naik / Kemaskini Bukti Bayaran</h4>
                        <p>Gunakan slip pindahan yang jelas. Anda boleh kemaskini semula jika perlu sebelum admin mengesahkan bayaran.</p>
                        <div class="proof-upload-grid">
                            <div class="proof-upload-field">
                                <label for="payment-channel-<?php echo (int) $transaction['id']; ?>">Kaedah Bayaran</label>
                                <select id="payment-channel-<?php echo (int) $transaction['id']; ?>" name="payment_channel" required>
                                    <option value="manual_banking" <?php echo (($transaction['payment_method'] ?? '') === $manualTransferConfig['duitnow_qr_label']) ? '' : 'selected'; ?>><?php echo htmlspecialchars($manualTransferConfig['manual_banking_label']); ?></option>
                                    <option value="duitnow_qr" <?php echo (($transaction['payment_method'] ?? '') === $manualTransferConfig['duitnow_qr_label']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($manualTransferConfig['duitnow_qr_label']); ?></option>
                                </select>
                            </div>
                            <div class="proof-upload-field">
                                <label for="payer-name-<?php echo (int) $transaction['id']; ?>">Nama Pengirim</label>
                                <input type="text" id="payer-name-<?php echo (int) $transaction['id']; ?>" name="payer_name" value="<?php echo htmlspecialchars($transaction['payer_name'] ?: ''); ?>" required>
                            </div>
                            <div class="proof-upload-field">
                                <label for="payment-reference-<?php echo (int) $transaction['id']; ?>">Rujukan Bayaran / Ref. Bank</label>
                                <input type="text" id="payment-reference-<?php echo (int) $transaction['id']; ?>" name="payment_reference" value="<?php echo htmlspecialchars($transaction['payment_reference'] ?: ($transaction['transaction_id'] ?: '')); ?>" required>
                            </div>
                            <div class="proof-upload-field">
                                <label for="proof-image-<?php echo (int) $transaction['id']; ?>">Fail Bukti Bayaran</label>
                                <input type="file" id="proof-image-<?php echo (int) $transaction['id']; ?>" name="proof_image" accept=".jpg,.jpeg,.png,.webp" required>
                            </div>
                        </div>
                        <button type="submit" class="proof-upload-submit">Hantar Bukti Bayaran</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
<?php endforeach; ?>
<?php endif; ?>
        </div>
    </div>
</body>
</html>
