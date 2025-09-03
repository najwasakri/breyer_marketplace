<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengurusan Pembayaran</title>
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background: #fcfcfc;
            font-family: Arial, sans-serif;
        }
        .container-payment {
            max-width: 1700px;
            margin: 40px auto;
            background: transparent;
            border-radius: 20px;
            padding: 0;
        }
        .admin-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: 0;
            margin-top: 32px;
            color: #222;
            letter-spacing: 1px;
        }
        .admin-title svg {
            width: 32px;
            height: 32px;
            vertical-align: middle;
        }
        h2 {
            color: #222;
            margin: 0 0 18px 0;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: 1px;
        }
        .btn-add {
            background: #ffe45c;
            color: #222;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            margin: 0;
            transition: background 0.2s;
            box-shadow: 0 2px 8px rgba(32,86,168,0.04);
        }
        .btn-add:hover {
            background: #ffe97a;
        }
        .table-wrapper {
            margin-top: 40px;
            border-radius: 0px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 16px rgba(32,86,168,0.08);
            /* max-width to limit table width */
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border: 2px solid #90caf9;
            min-width: 800px; /* Ensure minimum width for all columns */
        }
        th, td {
            padding: 8px 12px;
            border-bottom: 1px solid #f2f2f2;
            text-align: left;
            font-size: 1rem;
            border: 1px solid #90caf9;
        }
        th {
            background: #90caf9;
            color: #222;
            font-weight: bold;
            font-size: 1.08rem;
        }
        tr:nth-child(even) td {
            background: #fcfcfc;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .status-in {
            background: #4caf50;
            color: #fff;
            padding: 6px 18px;
            font-size: 1rem;
            border-radius: 8px;
            display: inline-block;
        }
        .status-low {
            background: #ffb300;
            color: #fff;
            padding: 6px 18px;
            font-size: 1rem;
            border-radius: 8px;
            display: inline-block;
        }
        .status-out {
            background: #f44336;
            color: #fff;
            padding: 6px 18px;
            font-size: 1rem;
            border-radius: 8px;
            display: inline-block;
        }
        .btn-edit, .btn-delete {
            padding: 4px 14px;
            font-size: 0.95rem;
            border-radius: 7px;
            font-weight: bold;
        }
        .btn-edit {
            background: #90caf9;
            color: #222;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-edit:hover {
            background: #bbdefb;
        }
        .btn-delete {
            background: #f44336;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-delete:hover {
            background: #d32f2f;
        }
        /* Responsive */
        @media (max-width: 1100px) {
            .container-payment, .table-wrapper { max-width: 98vw; }
            th, td { font-size: 1rem; padding: 10px 6px; }
            .btn-edit, .btn-delete { padding: 7px 14px; font-size: 1rem; }
        }
        @media (max-width: 700px) {
            .admin-title, h2 { font-size: 1.2rem; }
            .btn-add { padding: 8px 12px; font-size: 1rem; }
        }
        .header-row {
            display: flex;
            align-items: center;
            max-width: 1200px;      /* sama dengan .table-wrapper */
            margin: 0 auto 0 auto;  /* tengah secara horizontal */
            margin-bottom: 0;       /* rapat dengan jadual */
            padding-left: 0;
            padding-right: 0;
        }
        .btn-back {
           display: inline-block;
            margin: 12px 0 12px 0;
            padding: 3px 12px;      /* kecilkan padding */
            background: #90caf9;
            color: #222;
            border-radius: 50px;    /* kecilkan radius */
            font-weight: bold;      /* pastikan sudah ada */
            font-family: Arial Black, Arial, sans-serif; /* tambah ini untuk lebih tebal */
            text-decoration: none;
            font-size: 1.05rem;     /* kecilkan font */
            box-shadow: 0 2px 8px rgba(44,62,80,0.08);
            transition: background 0.2s;
            border: none;
            letter-spacing: 1px; 
        }
        .btn-back:hover {
            background: #bbdefb;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .container-payment {
                margin: 5px !important;
                padding: 10px;
            }

            .header-row {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 10px !important;
            }

            .header-row h2 {
                font-size: 1.3rem !important;
                margin: 10px 0 0 0 !important;
            }

            .btn-back {
                padding: 8px 12px;
                font-size: 0.9rem;
            }

            .table-wrapper {
                margin: 0 -10px; /* Extend to screen edges */
                padding: 0 10px;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                min-width: 750px;
                font-size: 0.9rem;
            }

            th, td {
                padding: 10px 8px;
                font-size: 0.85rem;
                white-space: nowrap;
            }

            /* Set minimum widths for each column */
            th:first-child,
            td:first-child {
                min-width: 40px;
            }

            th:nth-child(2),
            td:nth-child(2) {
                min-width: 120px;
            }

            th:nth-child(3),
            td:nth-child(3) {
                min-width: 140px;
            }

            th:nth-child(4),
            td:nth-child(4) {
                min-width: 90px;
            }

            th:nth-child(5),
            td:nth-child(5) {
                min-width: 120px;
            }

            th:nth-child(6),
            td:nth-child(6) {
                min-width: 90px;
            }

            th:nth-child(7),
            td:nth-child(7) {
                min-width: 80px;
            }

            th:nth-child(8),
            td:nth-child(8) {
                min-width: 100px;
            }

            .btn-edit {
                padding: 6px 10px;
                font-size: 0.8rem;
                margin: 1px;
                display: inline-block;
            }

            .status-in,
            .status-low,
            .status-out {
                font-size: 0.75rem;
                padding: 4px 8px;
            }

            .admin-title {
                font-size: 1.1rem;
                margin-top: 20px;
            }

            .admin-title svg {
                width: 24px;
                height: 24px;
            }
        }

        @media (max-width: 480px) {
            .container-payment {
                margin: 2px !important;
                padding: 8px;
            }

            .header-row h2 {
                font-size: 1.2rem !important;
            }

            .table-wrapper {
                margin: 0 -8px;
                padding: 0 8px;
            }

            table {
                min-width: 700px;
                font-size: 0.8rem;
            }

            th, td {
                padding: 8px 6px;
                font-size: 0.75rem;
            }

            .btn-edit {
                padding: 5px 8px;
                font-size: 0.7rem;
                width: 100%;
                display: block;
            }

            .status-in,
            .status-low,
            .status-out {
                font-size: 0.7rem;
                padding: 3px 6px;
            }

            /* Add scroll indicator */
            .table-wrapper::after {
                content: "← Swipe untuk lihat lebih →";
                display: block;
                text-align: center;
                font-size: 0.7rem;
                color: #666;
                padding: 5px 0;
                background: rgba(144, 202, 249, 0.1);
                margin-top: -1px;
            }

            /* Modal responsive */
            #viewModalContent {
                padding: 20px 15px !important;
                min-width: 280px !important;
                max-width: 90vw !important;
            }

            #viewModalContent h3 {
                font-size: 1.3rem !important;
                margin-bottom: 16px !important;
            }

            #viewDetail {
                font-size: 0.95rem !important;
            }

            #viewModalContent button {
                padding: 8px 16px !important;
                font-size: 0.9rem !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-payment" style="margin-top: 10px;">
        <div class="header-row" style="flex-direction: column; align-items: flex-start; gap: 0;">
                        <a href="dashboard.php" class="btn-back" aria-label="Back" style="
                            position: relative;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            margin: 12px 0 12px 0;
                            background: #90caf9;
                            color: #2c3e50;
                            width: 40px;
                            height: 40px;
                            border-radius: 50%;
                            text-decoration: none;
                            font-weight: bold;
                            font-size: 1.5rem;
                            box-shadow: 0 2px 8px rgba(144, 202, 249, 0.3);
                            transition: all 0.3s ease;
                            border: none;
                            z-index: 1000;
                            padding: 0;
                            cursor: pointer;
                        ">
                                <svg width="28" height="28" viewBox="0 0 28 28" style="display:block; margin:auto;" xmlns="http://www.w3.org/2000/svg">
                                        <polygon points="19,7 19,21 7,14" fill="#fff" />
                                </svg>
                        </a>
            <h2 style="margin:18px 0 0 0;">Senarai Pesanan</h2>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">Bil</th>
                        <th style="width:180px;">Nama Pelajar</th>
                        <th style="width:180px;">No Kad Pengenalan</th>
                        <th style="width:110px;">Tarikh</th>
                        <th style="width:160px;">Nama Item</th> <!-- Tambah kolum Item di sini -->
                        <th style="width:110px;">Jumlah (RM)</th>
                        <th style="width:110px;">Status</th>
                        <th style="width:110px;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
<?php
require_once 'includes/db_connect.php';
$result = $conn->query("SELECT * FROM payments ORDER BY id DESC");
$bil = 1;
while ($row = $result->fetch_assoc()) {
    $status_class = 'status-in';
    if ($row['status'] == 'Menunggu') $status_class = 'status-low';
    if ($row['status'] == 'Gagal') $status_class = 'status-out';

    // Tukar paparan "Selesai" kepada "Dibayar"
    $status_display = $row['status'] == 'Selesai' ? 'Dibayar' : $row['status'];

    echo "<tr>
        <td>{$bil}</td>
        <td>{$row['student_name']}</td>
        <td>{$row['student_ic']}</td>
        <td>" . date('d/m/Y', strtotime($row['order_date'])) . "</td>
        <td>{$row['item_name']}</td>
        <td>RM " . number_format($row['amount'], 2) . "</td>
        <td><span class='{$status_class}' id='status-{$row['id']}'>{$status_display}</span></td>
        <td>
            <button class='btn-edit' onclick=\"showViewModal('{$row['student_name']}','{$row['student_ic']}','" . date('d/m/Y', strtotime($row['order_date'])) . "','RM " . number_format($row['amount'], 2) . "','{$status_display}','{$row['item_name']}', {$row['id']})\">View</button>
        </td>
    </tr>";
    $bil++;
}
?>
</tbody>
            </table>
        </div>
    </div>

    <!-- Tambah sebelum </body> -->
    <div id="viewModal" style="display:none; position:fixed; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:999; align-items:center; justify-content:center;">
        <div id="viewModalContent" style="background:#fff; padding:32px 32px 28px 32px; border-radius:16px; min-width:340px; max-width:95vw; box-shadow:0 8px 32px rgba(0,0,0,0.15); position:relative; font-family:inherit;">
            <button onclick="closeViewModal()" style="position:absolute; top:18px; right:22px; background:none; border:none; font-size:2rem; cursor:pointer; color:#222;">&times;</button>
            <h3 style="margin-top:0; margin-bottom:22px; font-size:1.6rem; font-weight:700; color:#222; letter-spacing:1px;">Maklumat Pembelian</h3>
            <div id="viewDetail" style="font-size:1.08rem; color:#222; line-height:1.7;"></div>
            <div style="display:flex; justify-content:center; gap:16px; margin-top:32px;">
                <button onclick="acceptAction()" style="background:#4caf50; color:#fff; border:none; padding:10px 28px; border-radius:8px; font-size:1rem; font-weight:bold; cursor:pointer; box-shadow:0 2px 8px rgba(32,86,168,0.04); transition:background 0.2s;">
                    Terima
                </button>
                <button onclick="cancelOrderAction()" style="background:#f44336; color:#fff; border:none; padding:10px 28px; border-radius:8px; font-size:1rem; font-weight:bold; cursor:pointer; box-shadow:0 2px 8px rgba(32,86,168,0.04); transition:background 0.2s;">
                    Batal
                </button>
            </div>
        </div>
    </div>
    <script>
let currentOrderId = null;

function showViewModal(nama, ic, tarikh, jumlah, status, item = '-', id = null) {
    currentOrderId = id;
    document.getElementById('viewDetail').innerHTML = `
        <div style="margin-bottom:12px;"><span style="font-weight:600;">Nama Pelajar:</span> <span style="font-weight:400;">${nama}</span></div>
        <div style="margin-bottom:12px;"><span style="font-weight:600;">No Kad Pengenalan:</span> <span style="font-weight:400;">${ic}</span></div>
        <div style="margin-bottom:12px;"><span style="font-weight:600;">Tarikh:</span> <span style="font-weight:400;">${tarikh}</span></div>
        <div style="margin-bottom:12px;"><span style="font-weight:600;">Nama Item:</span> <span style="font-weight:400;">${item}</span></div>
        <div style="margin-bottom:12px;"><span style="font-weight:600;">Jumlah:</span> <span style="font-weight:400;">${jumlah}</span></div>
        <div style="margin-bottom:7px;"><span style="font-weight:600;">Status:</span> <span style="font-weight:400;" id="modal-status">${status}</span></div>
    `;
    document.getElementById('viewModal').style.display = 'flex';
}
function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}
function acceptAction() {
    if (!currentOrderId) return;
    updateStatus(currentOrderId, 'Selesai');
}
function cancelOrderAction() {
    if (!currentOrderId) return;
    if (confirm('Anda pasti ingin membatalkan pesanan ini?')) {
        updateStatus(currentOrderId, 'Gagal');
    }
}
function updateStatus(orderId, status) {
    fetch('update_payment_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${orderId}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Gagal kemaskini status!');
        }
    });
    closeViewModal();
}
    </script>
</body>
</html>