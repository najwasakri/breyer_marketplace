<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesi anda telah tamat. Sila log masuk semula.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Kaedah permintaan tidak sah.']);
    exit;
}

require_once 'db_connect.php';
require_once dirname(__DIR__) . '/manual_payment_helpers.php';

function respondJson($success, $message, $extra = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
    ], $extra));
    exit;
}

$catalog = [
    'AM' => [
        'BAJU KORPORAT' => 85.00,
        'BAJU ADMINISTRATION MANAGEMENT' => 85.00,
        'BAJU T-SHIRT KOLEJ' => 28.00,
    ],
    'CS' => [
        'BAJU KORPORAT' => 85.00,
        'BAJU COMPUTER SYSTEM' => 85.00,
        'BAJU T-SHIRT KOLEJ' => 28.00,
    ],
    'CULINARY' => [
        'BAJU KORPORAT' => 85.00,
        'BAJU CULINARY' => 85.00,
        'BAJU T-SHIRT KOLEJ' => 28.00,
    ],
    'ELECTRICAL' => [
        'BAJU KORPORAT' => 85.00,
        'BAJU ELECTRICAL' => 85.00,
        'BAJU T-SHIRT KOLEJ' => 28.00,
    ],
    'LAIN-LAIN' => [
        'FILE' => 10.00,
        'LANYARD' => 5.00,
    ],
    'FNB' => [
        'FILE' => 10.00,
        'LANYARD' => 5.00,
    ],
];

$action = $_POST['action'] ?? 'create_payment';

function normalizeCategory($category) {
    if ($category === 'FNB') {
        return 'LAIN-LAIN';
    }

    return $category;
}

function getCatalogPrice($catalog, $category, $productName) {
    $normalizedCategory = normalizeCategory($category);
    if (!isset($catalog[$normalizedCategory][$productName])) {
        return [null, $normalizedCategory];
    }

    return [(float) $catalog[$normalizedCategory][$productName], $normalizedCategory];
}

function fetchCurrentUser($pdo) {
    $userStmt = $pdo->prepare('SELECT full_name, ic_number FROM users WHERE user_id = ? LIMIT 1');
    $userStmt->execute([$_SESSION['user_id']]);
    return $userStmt->fetch();
}

try {
    ensureManualPaymentColumnsPDO($pdo);
    $manualTransferConfig = getManualTransferConfig();

    if ($action === 'create_payment') {
        $productName = trim($_POST['product_name'] ?? '');
        $productCategory = trim($_POST['product_category'] ?? '');
        $customerClass = trim($_POST['customer_class'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $productSize = trim($_POST['product_size'] ?? '');
        $postedUnitPrice = isset($_POST['unit_price']) ? (float) $_POST['unit_price'] : 0.0;
        $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => 1,
                'max_range' => 99,
            ],
        ]);

        $productCategory = normalizeCategory($productCategory);

        if ($productName === '' || $productCategory === '') {
            respondJson(false, 'Maklumat produk tidak lengkap.');
        }

        if (!isset($catalog[$productCategory][$productName])) {
            respondJson(false, 'Produk yang dipilih tidak sah.');
        }

        if ($quantity === false) {
            respondJson(false, 'Kuantiti tidak sah.');
        }

        if ($customerClass === '' || $customerPhone === '') {
            respondJson(false, 'Sila lengkapkan semua maklumat pembelian.');
        }

        if ($productCategory !== 'LAIN-LAIN' && $productSize === '') {
            respondJson(false, 'Sila pilih saiz produk.');
        }

        if ($productCategory === 'LAIN-LAIN') {
            $productSize = null;
        }

        $unitPrice = (float) $catalog[$productCategory][$productName];
        if ($postedUnitPrice > 0 && abs($postedUnitPrice - $unitPrice) > 0.01) {
            respondJson(false, 'Harga produk tidak sepadan. Sila muat semula halaman.');
        }

        $user = fetchCurrentUser($pdo);

        if (!$user) {
            respondJson(false, 'Maklumat pelajar tidak ditemui.');
        }

        $customerName = trim($user['full_name'] ?? '');
        if ($customerName === '') {
            respondJson(false, 'Nama pelajar pada akaun tidak lengkap.');
        }

        $amount = $unitPrice * $quantity;
        $transactionId = 'TXN' . date('YmdHis') . random_int(1000, 9999);

        $insertStmt = $pdo->prepare(
            'INSERT INTO payments (
                customer_name,
                student_id,
                student_name,
                student_ic,
                order_date,
                item_name,
                amount,
                status,
                item_category,
                item_size,
                user_id,
                transaction_id,
                quantity,
                payment_method,
                manual_bank_name,
                payment_status,
                customer_class,
                customer_phone
            ) VALUES (
                ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )'
        );

        $insertStmt->execute([
            $customerName,
            $_SESSION['user_id'],
            $user['full_name'] ?: $customerName,
            $user['ic_number'] ?? null,
            $productName,
            $amount,
            'Menunggu',
            $productCategory,
            $productSize,
            $_SESSION['user_id'],
            $transactionId,
            $quantity,
            'Manual Transfer',
            $manualTransferConfig['bank_name'],
            'pending',
            $customerClass,
            $customerPhone,
        ]);

        respondJson(true, 'Rekod pembayaran berjaya disimpan.', [
            'paymentId' => (int) $pdo->lastInsertId(),
            'transactionId' => $transactionId,
            'amount' => number_format($amount, 2, '.', ''),
            'redirectUrl' => 'payment_history.php',
            'manualTransfer' => $manualTransferConfig,
        ]);
    }

    if ($action === 'create_cart_payments') {
        $customerClass = trim($_POST['customer_class'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $itemsJson = $_POST['items'] ?? '[]';
        $items = json_decode($itemsJson, true);

        if ($customerClass === '' || $customerPhone === '') {
            respondJson(false, 'Sila lengkapkan semua maklumat checkout.');
        }

        if (!is_array($items) || count($items) === 0) {
            respondJson(false, 'Troli anda kosong.');
        }

        $user = fetchCurrentUser($pdo);
        if (!$user) {
            respondJson(false, 'Maklumat pelajar tidak ditemui.');
        }

        $customerName = trim($user['full_name'] ?? '');
        if ($customerName === '') {
            respondJson(false, 'Nama pelajar pada akaun tidak lengkap.');
        }

        $insertStmt = $pdo->prepare(
            'INSERT INTO payments (
                customer_name,
                student_id,
                student_name,
                student_ic,
                order_date,
                item_name,
                amount,
                status,
                item_category,
                item_size,
                user_id,
                transaction_id,
                quantity,
                payment_method,
                manual_bank_name,
                payment_status,
                customer_class,
                customer_phone
            ) VALUES (
                ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )'
        );

        $paymentIds = [];
        $transactionIds = [];
        $totalAmount = 0;

        foreach ($items as $item) {
            $productName = trim($item['name'] ?? '');
            $productCategory = trim($item['category'] ?? '');
            $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT, [
                'options' => [
                    'min_range' => 1,
                    'max_range' => 99,
                ],
            ]);
            $postedUnitPrice = isset($item['price']) ? (float) $item['price'] : 0.0;

            if ($productName === '' || $productCategory === '' || $quantity === false) {
                respondJson(false, 'Satu atau lebih item troli tidak sah.');
            }

            [$unitPrice, $normalizedCategory] = getCatalogPrice($catalog, $productCategory, $productName);
            if ($unitPrice === null) {
                respondJson(false, 'Terdapat item troli yang tidak sepadan dengan katalog semasa.');
            }

            if ($postedUnitPrice > 0 && abs($postedUnitPrice - $unitPrice) > 0.01) {
                respondJson(false, 'Harga item troli tidak sepadan. Sila muat semula troli.');
            }

            $amount = $unitPrice * $quantity;
            $transactionId = 'TXN' . date('YmdHis') . random_int(1000, 9999);

            $insertStmt->execute([
                $customerName,
                $_SESSION['user_id'],
                $user['full_name'] ?: $customerName,
                $user['ic_number'] ?? null,
                $productName,
                $amount,
                'Menunggu',
                $normalizedCategory,
                null,
                $_SESSION['user_id'],
                $transactionId,
                $quantity,
                'Manual Transfer',
                $manualTransferConfig['bank_name'],
                'pending',
                $customerClass,
                $customerPhone,
            ]);

            $paymentIds[] = (int) $pdo->lastInsertId();
            $transactionIds[] = $transactionId;
            $totalAmount += $amount;
        }

        respondJson(true, 'Checkout troli berjaya direkodkan.', [
            'paymentIds' => $paymentIds,
            'transactionIds' => $transactionIds,
            'amount' => number_format($totalAmount, 2, '.', ''),
            'redirectUrl' => 'payment_history.php',
            'manualTransfer' => $manualTransferConfig,
        ]);
    }

    if ($action === 'select_bank') {
        $paymentId = filter_var($_POST['payment_id'] ?? null, FILTER_VALIDATE_INT);
        $paymentIdsRaw = $_POST['payment_ids'] ?? '';
        $bankName = trim($_POST['bank_name'] ?? '');
        $bankCode = trim($_POST['bank_code'] ?? '');

        $paymentIds = [];
        if (is_string($paymentIdsRaw) && $paymentIdsRaw !== '') {
            $decodedIds = json_decode($paymentIdsRaw, true);
            if (is_array($decodedIds)) {
                foreach ($decodedIds as $decodedId) {
                    $validatedId = filter_var($decodedId, FILTER_VALIDATE_INT);
                    if ($validatedId !== false && $validatedId > 0) {
                        $paymentIds[] = $validatedId;
                    }
                }
            }
        }

        if (empty($paymentIds) && $paymentId !== false && $paymentId > 0) {
            $paymentIds[] = $paymentId;
        }

        if (empty($paymentIds) || $bankName === '') {
            respondJson(false, 'Maklumat bank tidak sah.');
        }

        if ($bankCode === '') {
            $bankCode = preg_replace('/[^A-Z]/', '', strtoupper($bankName));
            $bankCode = substr($bankCode, 0, 10);
        }

        $updateStmt = $pdo->prepare(
            'UPDATE payments
             SET bank_code = ?, fpx_bank_id = ?, payment_method = ?, manual_bank_name = ?
             WHERE id = ? AND user_id = ?'
        );
        foreach ($paymentIds as $singlePaymentId) {
            $updateStmt->execute([$bankCode, $bankName, 'Manual Transfer', $bankName, $singlePaymentId, $_SESSION['user_id']]);
        }

        respondJson(true, 'Bank pilihan berjaya direkodkan.');
    }

    respondJson(false, 'Tindakan tidak dikenali.');
} catch (Throwable $e) {
    http_response_code(500);
    respondJson(false, 'Ralat semasa memproses pesanan.', ['error' => $e->getMessage()]);
}
?>
