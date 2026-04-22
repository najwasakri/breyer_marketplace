<?php

function getManualTransferConfig()
{
    return [
        'bank_name' => 'Maybank',
        'account_name' => 'Breyer Marketplace',
        'account_number' => '1234567890',
        'instructions' => 'Sila buat pindahan manual mengikut jumlah pesanan dan muat naik bukti bayaran selepas transaksi berjaya.',
        'manual_banking_label' => 'Manual Banking',
        'duitnow_qr_label' => 'QR DuitNow',
        'duitnow_receiver_name' => 'Breyer Marketplace',
        'duitnow_qr_image_path' => '',
        'duitnow_instructions' => 'Jika anda memilih QR DuitNow, scan kod QR rasmi dan gunakan nombor rujukan transaksi semasa memuat naik bukti bayaran.',
    ];
}

function getManualPaymentColumnsDefinition()
{
    return [
        'manual_bank_name' => "ALTER TABLE payments ADD COLUMN manual_bank_name VARCHAR(100) NULL AFTER payment_method",
        'payment_reference' => "ALTER TABLE payments ADD COLUMN payment_reference VARCHAR(120) NULL AFTER manual_bank_name",
        'payer_name' => "ALTER TABLE payments ADD COLUMN payer_name VARCHAR(150) NULL AFTER payment_reference",
        'proof_image_path' => "ALTER TABLE payments ADD COLUMN proof_image_path VARCHAR(255) NULL AFTER payer_name",
        'proof_uploaded_at' => "ALTER TABLE payments ADD COLUMN proof_uploaded_at DATETIME NULL AFTER proof_image_path",
        'verification_status' => "ALTER TABLE payments ADD COLUMN verification_status VARCHAR(40) NULL AFTER proof_uploaded_at",
        'verification_notes' => "ALTER TABLE payments ADD COLUMN verification_notes TEXT NULL AFTER verification_status",
    ];
}

function ensureManualPaymentColumnsPDO(PDO $pdo)
{
    foreach (getManualPaymentColumnsDefinition() as $columnName => $alterSql) {
        $checkStmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?'
        );
        $checkStmt->execute(['payments', $columnName]);
        $columnExists = (int) $checkStmt->fetchColumn() > 0;

        if (!$columnExists) {
            $pdo->exec($alterSql);
        }
    }
}

function ensureManualPaymentColumnsMysqli(mysqli $conn)
{
    foreach (getManualPaymentColumnsDefinition() as $columnName => $alterSql) {
        $checkStmt = $conn->prepare(
            'SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?'
        );
        $tableName = 'payments';
        $checkStmt->bind_param('ss', $tableName, $columnName);
        $checkStmt->execute();
        $checkStmt->bind_result($columnCount);
        $checkStmt->fetch();
        $checkStmt->close();

        $columnExists = (int) $columnCount > 0;

        if (!$columnExists) {
            $conn->query($alterSql);
        }
    }
}

function getManualPaymentUploadDirectory()
{
    return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'payment_proofs';
}

function getManualPaymentPublicPath($fileName)
{
    return '../uploads/payment_proofs/' . $fileName;
}
