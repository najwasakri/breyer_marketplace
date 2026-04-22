<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'includes/db_connect.php';

if (!isset($_GET['no'])) {
    header('Location: manage_receipts.php');
    exit;
}

function valueOrDash($value) {
    $text = trim((string) $value);
    return $text === '' ? '-' : $text;
}

$receipt_no = $_GET['no'];
$sql = "SELECT r.*, r.created_at AS receipt_created_at, p.amount,
           COALESCE(p.payment_date, p.order_date) AS payment_date,
           p.status, p.student_name, p.student_ic, p.item_name, p.item_category,
           p.item_size, p.quantity, p.transaction_id, p.customer_class,
           p.customer_phone, p.payment_method, p.fpx_bank_id, p.bank_code
    FROM receipts r
    JOIN payments p ON r.payment_id = p.id
    WHERE r.receipt_no = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $receipt_no);
$stmt->execute();
$result = $stmt->get_result();
$receipt = $result->fetch_assoc();

if (!$receipt) {
    header('Location: manage_receipts.php');
    exit;
}

$receiptDateRaw = $receipt['payment_date'] ?: ($receipt['receipt_created_at'] ?? null);
$receiptDate = $receiptDateRaw ? date('d/m/Y', strtotime($receiptDateRaw)) : '-';
$issuedBy = valueOrDash($_SESSION['full_name'] ?? $_SESSION['admin_nama'] ?? 'Admin Breyer');
$bankName = valueOrDash($receipt['fpx_bank_id'] ?: $receipt['bank_code']);
$paymentMethod = valueOrDash($receipt['payment_method'] ?: 'FPX');
$referenceNo = valueOrDash($receipt['transaction_id'] ?: $receipt['receipt_no']);
$statusLabel = $receipt['status'] === 'Selesai' ? 'Paid' : valueOrDash($receipt['status']);

$descriptionParts = [];
if (!empty($receipt['item_category'])) {
    $descriptionParts[] = $receipt['item_category'];
}
if (!empty($receipt['item_size'])) {
    $descriptionParts[] = 'Saiz ' . $receipt['item_size'];
}
if (!empty($receipt['quantity'])) {
    $descriptionParts[] = 'Kuantiti ' . (int) $receipt['quantity'];
}

$description = valueOrDash($receipt['item_name']);
if (!empty($descriptionParts)) {
    $description .= ' - ' . implode(', ', $descriptionParts);
}

$remarksParts = [
    'Receipt No ' . valueOrDash($receipt['receipt_no']),
    'Reference ' . $referenceNo,
    'Bank ' . $bankName
];
$remarks = implode(' | ', $remarksParts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Resit - <?php echo htmlspecialchars($receipt_no); ?></title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: #1f2937;
            background: #eef2f6;
            padding: 24px;
        }

        .print-actions {
            width: min(860px, 100%);
            margin: 0 auto 14px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .print-actions button {
            border: 0;
            border-radius: 999px;
            padding: 10px 18px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
        }

        .print-actions button:first-child {
            background: #123f7a;
            color: #fff;
        }

        .print-actions button:last-child {
            background: #dbe4ee;
            color: #0f172a;
        }

        .receipt-paper {
            width: min(860px, 100%);
            margin: 0 auto;
            background: #fff;
            border: 1px solid #d6dbe3;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
            padding: 32px 36px;
        }

        .brand-header {
            display: flex;
            gap: 18px;
            align-items: center;
            padding-bottom: 16px;
            border-bottom: 2px solid #dce3eb;
        }

        .brand-header img {
            width: 120px;
            max-width: 28%;
            height: auto;
            object-fit: contain;
        }

        .brand-copy {
            flex: 1;
            text-align: center;
        }

        .brand-copy h1 {
            margin: 0 0 3px;
            font-size: 1.72rem;
            letter-spacing: 0.04em;
            color: #1e293b;
        }

        .brand-copy p {
            margin: 2px 0;
            color: #475569;
            font-size: 0.84rem;
            line-height: 1.35;
        }

        .receipt-label {
            margin: 18px 0 12px;
            color: #334155;
            font-size: 0.92rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .receipt-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }

        .meta-card {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 10px;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .meta-label,
        .field-label,
        .payment-label {
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .meta-value,
        .field-value,
        .payment-value {
            color: #0f172a;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .info-columns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 18px;
        }

        .info-table {
            display: grid;
            gap: 8px;
        }

        .field-row {
            display: grid;
            grid-template-columns: 112px 1fr;
            gap: 10px;
            align-items: start;
        }

        .receipt-finance {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 24px;
            align-items: start;
            margin-bottom: 16px;
        }

        .detail-table {
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            padding: 10px 0;
            margin-bottom: 0;
        }

        .detail-head,
        .detail-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 120px;
            gap: 12px;
            align-items: start;
        }

        .detail-head {
            padding-bottom: 8px;
            color: #475569;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .detail-row {
            padding-top: 8px;
        }

        .detail-description {
            color: #0f172a;
            line-height: 1.45;
            font-size: 0.9rem;
        }

        .detail-amount {
            text-align: right;
            font-weight: 700;
            color: #0f172a;
        }

        .summary-block {
            width: min(360px, 100%);
            margin-left: auto;
            display: grid;
            gap: 6px;
        }

        .payment-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: center;
            padding-bottom: 4px;
            border-bottom: 1px dashed #d4dbe5;
        }

        .payment-row.total {
            margin-top: 4px;
            padding-top: 6px;
            border-top: 1px solid #94a3b8;
            border-bottom: 0;
        }

        .payment-row.total .payment-label,
        .payment-row.total .payment-value {
            font-size: 0.94rem;
            color: #0f172a;
        }

        .remarks {
            margin-top: 14px;
            color: #475569;
            font-size: 0.82rem;
            line-height: 1.45;
        }

        .receipt-note {
            margin-top: 12px;
            color: #475569;
            font-size: 0.82rem;
            line-height: 1.45;
        }

        .tagline {
            margin-top: 42px;
            text-align: center;
            color: #475569;
            font-size: 1rem;
        }

        @media (max-width: 860px) {
            body {
                padding: 12px;
            }

            .receipt-paper {
                padding: 24px 20px;
            }

            .brand-header,
            .receipt-meta,
            .info-columns,
            .receipt-finance,
            .detail-head,
            .detail-row {
                grid-template-columns: 1fr;
            }

            .brand-header {
                display: block;
                text-align: center;
            }

            .brand-header img {
                max-width: 180px;
                width: 100%;
                margin-bottom: 14px;
            }

            .meta-card,
            .field-row {
                grid-template-columns: 1fr;
                gap: 4px;
            }

            .detail-amount,
            .summary-block {
                text-align: left;
                margin-left: 0;
            }
        }

        @media print {
            @page {
                size: A4;
                margin: 5mm;
            }

            html,
            body {
                width: 210mm;
                min-height: auto;
                height: auto;
            }

            body {
                margin: 0;
                padding: 0;
                background: #fff;
                zoom: 0.86;
            }

            .print-actions {
                display: none;
            }

            .receipt-paper {
                width: 100%;
                border: 0;
                box-shadow: none;
                padding: 6mm 6mm;
            }

            .brand-header {
                gap: 12px;
                padding-bottom: 10px;
            }

            .brand-header img {
                width: 88px;
            }

            .brand-copy h1 {
                font-size: 1.28rem;
            }

            .brand-copy p {
                font-size: 0.7rem;
                line-height: 1.2;
            }

            .receipt-label {
                margin: 10px 0 8px;
                font-size: 0.76rem;
            }

            .receipt-meta,
            .info-columns,
            .receipt-finance {
                gap: 10px;
                margin-bottom: 10px;
            }

            .meta-card {
                padding: 6px 8px;
            }

            .field-row {
                grid-template-columns: 86px 1fr;
                gap: 8px;
            }

            .meta-label,
            .field-label,
            .payment-label,
            .detail-head {
                font-size: 0.64rem;
            }

            .meta-value,
            .field-value,
            .payment-value,
            .detail-description,
            .detail-amount {
                font-size: 0.78rem;
            }

            .receipt-finance {
                grid-template-columns: minmax(0, 1fr) 250px;
            }

            .detail-table {
                padding: 6px 0;
            }

            .detail-head,
            .detail-row {
                grid-template-columns: minmax(0, 1fr) 90px;
                gap: 8px;
            }

            .summary-block {
                gap: 4px;
            }

            .payment-row {
                gap: 8px;
                padding-bottom: 2px;
            }

            .remarks,
            .receipt-note {
                margin-top: 8px;
                font-size: 0.72rem;
                line-height: 1.25;
            }

            .tagline {
                margin-top: 14px;
                font-size: 0.82rem;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-actions">
        <button type="button" onclick="window.print()">Cetak</button>
        <button type="button" onclick="window.close()">Tutup</button>
    </div>

    <div class="receipt-paper">
        <div class="brand-header">
            <img src="logo-breyer.png" alt="Breyer logo">
            <div class="brand-copy">
                <h1>KOLEJ BREYER GOMBAK</h1>
                <p>(Wholly owned by Breyer Sdn Bhd - No.537674-A)</p>
                <p>No. 1C, Jalan SG 3/19, Taman Sri Gombak, 68100 Batu Caves, Selangor Darul Ehsan.</p>
                <p>Tel: 603-6185 4643 &nbsp;&nbsp; Website: www.breyer.edu.my</p>
            </div>
        </div>

        <div class="receipt-label">Receipt</div>

        <div class="receipt-meta">
            <div class="meta-card">
                <div class="meta-label">Document No</div>
                <div class="meta-value"><?php echo htmlspecialchars(valueOrDash($receipt['receipt_no'])); ?></div>
            </div>
            <div class="meta-card">
                <div class="meta-label">Date</div>
                <div class="meta-value"><?php echo htmlspecialchars($receiptDate); ?></div>
            </div>
        </div>

        <div class="info-columns">
            <div class="info-table">
                <div class="field-row">
                    <div class="field-label">Name</div>
                    <div class="field-value"><?php echo htmlspecialchars(valueOrDash($receipt['student_name'])); ?></div>
                </div>
                <div class="field-row">
                    <div class="field-label">ID No</div>
                    <div class="field-value"><?php echo htmlspecialchars(valueOrDash($receipt['student_ic'])); ?></div>
                </div>
                <div class="field-row">
                    <div class="field-label">Issued By</div>
                    <div class="field-value"><?php echo htmlspecialchars($issuedBy); ?></div>
                </div>
                <div class="field-row">
                    <div class="field-label">Program</div>
                    <div class="field-value"><?php echo htmlspecialchars(valueOrDash($receipt['item_category'] ?: $receipt['item_name'])); ?></div>
                </div>
            </div>

            <div class="info-table">
                <div class="field-row">
                    <div class="field-label">Reference No</div>
                    <div class="field-value"><?php echo htmlspecialchars($referenceNo); ?></div>
                </div>
                <div class="field-row">
                    <div class="field-label">Class</div>
                    <div class="field-value"><?php echo htmlspecialchars(valueOrDash($receipt['customer_class'])); ?></div>
                </div>
                <div class="field-row">
                    <div class="field-label">Phone</div>
                    <div class="field-value"><?php echo htmlspecialchars(valueOrDash($receipt['customer_phone'])); ?></div>
                </div>
                <div class="field-row">
                    <div class="field-label">Status</div>
                    <div class="field-value"><?php echo htmlspecialchars($statusLabel); ?></div>
                </div>
            </div>
        </div>

        <div class="receipt-finance">
            <div class="detail-table">
                <div class="detail-head">
                    <div>Description</div>
                    <div style="text-align: right;">Total Amount (RM)</div>
                </div>
                <div class="detail-row">
                    <div class="detail-description"><?php echo htmlspecialchars($description); ?></div>
                    <div class="detail-amount"><?php echo number_format((float) $receipt['amount'], 2); ?></div>
                </div>
            </div>

            <div class="summary-block">
                <div class="payment-row">
                    <div class="payment-label">Method of Payment Online</div>
                    <div class="payment-value"><?php echo htmlspecialchars($paymentMethod); ?></div>
                </div>
                <div class="payment-row">
                    <div class="payment-label">Bank Name</div>
                    <div class="payment-value"><?php echo htmlspecialchars($bankName); ?></div>
                </div>
                <div class="payment-row">
                    <div class="payment-label">Reference No</div>
                    <div class="payment-value"><?php echo htmlspecialchars($referenceNo); ?></div>
                </div>
                <div class="payment-row">
                    <div class="payment-label">Online Date</div>
                    <div class="payment-value"><?php echo htmlspecialchars($receiptDate); ?></div>
                </div>
                <div class="payment-row">
                    <div class="payment-label">Overpayment (RM)</div>
                    <div class="payment-value">0.00</div>
                </div>
                <div class="payment-row">
                    <div class="payment-label">Amount (RM)</div>
                    <div class="payment-value"><?php echo number_format((float) $receipt['amount'], 2); ?></div>
                </div>
                <div class="payment-row total">
                    <div class="payment-label">Total Amount (RM)</div>
                    <div class="payment-value"><?php echo number_format((float) $receipt['amount'], 2); ?></div>
                </div>
            </div>
        </div>

        <div class="remarks"><strong>Remarks:</strong> <?php echo htmlspecialchars($remarks); ?></div>

        <div class="receipt-note">This receipt is only valid upon verification of the transaction by the Finance department.</div>

        <div class="tagline">We Build Career Through Character</div>
    </div>
</body>
</html>