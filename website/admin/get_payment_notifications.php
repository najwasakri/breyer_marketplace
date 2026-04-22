<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sesi admin tidak sah.'
    ]);
    exit;
}

require_once 'includes/db_connect.php';
require_once dirname(__DIR__) . '/manual_payment_helpers.php';

ensureManualPaymentColumnsMysqli($conn);

$summarySql = "
    SELECT
        COALESCE(MAX(id), 0) AS latest_order_id,
        COALESCE(MAX(CASE WHEN proof_uploaded_at IS NOT NULL THEN UNIX_TIMESTAMP(proof_uploaded_at) ELSE 0 END), 0) AS latest_proof_timestamp,
        SUM(CASE
            WHEN status = 'Menunggu'
             AND (verification_status IS NULL OR verification_status = '' OR verification_status <> 'uploaded')
            THEN 1 ELSE 0 END) AS orders_pending_count,
        SUM(CASE
            WHEN status = 'Menunggu' AND verification_status = 'uploaded'
            THEN 1 ELSE 0 END) AS proofs_pending_count
    FROM payments
";

$summaryResult = $conn->query($summarySql);
$summary = $summaryResult ? $summaryResult->fetch_assoc() : [];

$orders = [];
$ordersSql = "
    SELECT id, student_name, item_name, amount, transaction_id, created_at
    FROM payments
    WHERE status = 'Menunggu'
      AND (verification_status IS NULL OR verification_status = '' OR verification_status <> 'uploaded')
    ORDER BY created_at DESC, id DESC
    LIMIT 4
";
$ordersResult = $conn->query($ordersSql);
if ($ordersResult) {
    while ($row = $ordersResult->fetch_assoc()) {
        $orders[] = [
            'id' => (int) ($row['id'] ?? 0),
            'studentName' => $row['student_name'] ?? '-',
            'itemName' => $row['item_name'] ?? '-',
            'transactionId' => $row['transaction_id'] ?? '-',
            'amount' => 'RM ' . number_format((float) ($row['amount'] ?? 0), 2),
            'timeLabel' => !empty($row['created_at']) ? date('d/m/Y h:i A', strtotime($row['created_at'])) : '-',
        ];
    }
}

$proofs = [];
$proofsSql = "
    SELECT id, student_name, item_name, amount, transaction_id, proof_uploaded_at
    FROM payments
    WHERE status = 'Menunggu'
      AND verification_status = 'uploaded'
    ORDER BY proof_uploaded_at DESC, id DESC
    LIMIT 4
";
$proofsResult = $conn->query($proofsSql);
if ($proofsResult) {
    while ($row = $proofsResult->fetch_assoc()) {
        $proofs[] = [
            'id' => (int) ($row['id'] ?? 0),
            'studentName' => $row['student_name'] ?? '-',
            'itemName' => $row['item_name'] ?? '-',
            'transactionId' => $row['transaction_id'] ?? '-',
            'amount' => 'RM ' . number_format((float) ($row['amount'] ?? 0), 2),
            'timeLabel' => !empty($row['proof_uploaded_at']) ? date('d/m/Y h:i A', strtotime($row['proof_uploaded_at'])) : '-',
        ];
    }
}

$ordersPendingCount = (int) ($summary['orders_pending_count'] ?? 0);
$proofsPendingCount = (int) ($summary['proofs_pending_count'] ?? 0);

echo json_encode([
    'success' => true,
    'latestOrderId' => (int) ($summary['latest_order_id'] ?? 0),
    'latestProofTimestamp' => (int) ($summary['latest_proof_timestamp'] ?? 0),
    'ordersPendingCount' => $ordersPendingCount,
    'proofsPendingCount' => $proofsPendingCount,
    'totalAttentionCount' => $ordersPendingCount + $proofsPendingCount,
    'recentOrders' => $orders,
    'recentProofs' => $proofs,
    'checkedAt' => date('d/m/Y h:i:s A'),
]);