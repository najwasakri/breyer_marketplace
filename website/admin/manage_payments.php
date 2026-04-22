<?php
session_start();
require_once dirname(__DIR__) . '/manual_payment_helpers.php';
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
            overflow-x: hidden;
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
            display: block;
            width: 100%;
            margin-top: 40px;
            border-radius: 0px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 16px rgba(32,86,168,0.08);
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-x;
        }
        .action-message {
            max-width: 1200px;
            margin: 18px auto 0;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 0.98rem;
            font-weight: 600;
            display: none;
            box-sizing: border-box;
        }
        .action-message.success {
            background: #e8f7eb;
            border: 1px solid #7bc67e;
            color: #1d6f30;
        }
        .action-message.error {
            background: #fdecec;
            border: 1px solid #ef9a9a;
            color: #b3261e;
        }
        table {
            width: 100%;
            max-width: none;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border: 2px solid #90caf9;
            min-width: 800px;
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
            max-width: 1200px;
            margin: 0 auto 0 auto;
            margin-bottom: 0;
            padding-left: 0;
            padding-right: 0;
        }
        .payment-page-header {
            display: block;
        }
        .header-main {
            width: 100%;
        }
        .payment-command-center {
            position: relative;
            display: grid;
            gap: 24px;
            padding: 24px 26px;
            border-radius: 32px;
            background:
                radial-gradient(circle at top right, rgba(255, 214, 102, 0.42), transparent 28%),
                radial-gradient(circle at bottom left, rgba(147, 197, 253, 0.34), transparent 32%),
                linear-gradient(135deg, #fffef7 0%, #f8fbff 52%, #eef5ff 100%);
            border: 1px solid rgba(147, 197, 253, 0.42);
            box-shadow: 0 24px 55px rgba(30, 64, 175, 0.12);
            overflow: hidden;
        }
        .payment-command-center::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(125deg, rgba(255,255,255,0.62) 0%, rgba(255,255,255,0) 36%),
                linear-gradient(180deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
            pointer-events: none;
        }
        .command-center-topline,
        .command-center-body {
            position: relative;
            z-index: 1;
        }
        .command-center-topline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }
        .command-center-eyebrow {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .header-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.68);
            color: #8a5a00;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            box-shadow: inset 0 0 0 1px rgba(250, 204, 21, 0.22);
        }
        .header-kicker::before {
            content: '';
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            box-shadow: 0 0 0 6px rgba(251, 191, 36, 0.16);
        }
        .header-live-pill {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 9px 14px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.9);
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 700;
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.18);
        }
        .header-live-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #4ade80;
            box-shadow: 0 0 0 6px rgba(74, 222, 128, 0.18);
        }
        .command-center-body {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.95fr);
            gap: 20px;
            align-items: stretch;
        }
        .command-center-copy {
            display: grid;
            gap: 12px;
            align-content: start;
        }
        .page-title {
            margin: 0;
            font-size: clamp(2rem, 3vw, 2.65rem);
            line-height: 1;
            letter-spacing: -0.03em;
            color: #0f172a;
        }
        .header-subtitle {
            margin: 0;
            max-width: 560px;
            color: #334155;
            font-size: 1rem;
            line-height: 1.7;
        }
        .header-status-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            align-content: start;
        }
        .header-status-chip {
            min-height: 124px;
            padding: 16px 18px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(255, 255, 255, 0.75);
            box-shadow: 0 16px 32px rgba(148, 163, 184, 0.12);
            display: grid;
            gap: 8px;
            align-content: start;
        }
        .header-status-chip.order {
            background: linear-gradient(180deg, rgba(239, 246, 255, 0.94) 0%, rgba(255, 255, 255, 0.92) 100%);
        }
        .header-status-chip.proof {
            background: linear-gradient(180deg, rgba(240, 253, 250, 0.98) 0%, rgba(255, 255, 255, 0.92) 100%);
        }
        .header-status-chip.focus {
            background: linear-gradient(180deg, rgba(255, 247, 205, 0.98) 0%, rgba(255, 255, 255, 0.92) 100%);
            grid-column: span 2;
            min-height: auto;
        }
        .header-status-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .header-status-label::before {
            content: '';
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #60a5fa;
        }
        .header-status-chip.proof .header-status-label::before {
            background: #34d399;
        }
        .header-status-chip.focus .header-status-label::before {
            background: #f59e0b;
        }
        .header-status-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            color: #0f172a;
        }
        .header-status-meta {
            font-size: 0.88rem;
            line-height: 1.55;
            color: #475569;
        }
        .header-tools {
            display: grid;
            align-content: start;
            position: relative;
        }
        .notification-wrapper {
            position: relative;
            height: 100%;
        }
        .notification-bell {
            width: 100%;
            min-height: 136px;
            border: none;
            border-radius: 28px;
            background: linear-gradient(145deg, #0f172a 0%, #1e3a8a 62%, #1d4ed8 100%);
            box-shadow: 0 24px 44px rgba(30, 64, 175, 0.22);
            display: grid;
            grid-template-columns: 64px minmax(0, 1fr);
            gap: 16px;
            align-items: center;
            text-align: left;
            cursor: pointer;
            position: relative;
            padding: 18px 22px;
            overflow: hidden;
            transition: transform 0.24s ease, box-shadow 0.24s ease;
        }
        .notification-bell:hover {
            transform: translateY(-3px);
            box-shadow: 0 28px 48px rgba(30, 64, 175, 0.28);
        }
        .notification-bell::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(125deg, rgba(255,255,255,0.22), rgba(255,255,255,0) 36%);
            pointer-events: none;
        }
        .notification-bell-icon {
            width: 64px;
            height: 64px;
            border-radius: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12);
        }
        .notification-bell svg {
            width: 30px;
            height: 30px;
            fill: #ffffff;
        }
        .notification-bell.attention {
            box-shadow: 0 0 0 8px rgba(96, 165, 250, 0.18), 0 24px 44px rgba(30, 64, 175, 0.22);
        }
        .notification-bell.has-update {
            animation: bellShake 0.8s ease;
        }
        .notification-trigger-copy {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 8px;
        }
        .notification-trigger-eyebrow {
            color: rgba(255, 255, 255, 0.76);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .notification-trigger-title {
            color: #ffffff;
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .notification-trigger-meta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.84);
            font-size: 0.9rem;
            line-height: 1.45;
        }
        .notification-trigger-live {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .notification-trigger-live-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #4ade80;
            box-shadow: 0 0 0 5px rgba(74, 222, 128, 0.16);
        }
        .notification-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            min-width: 28px;
            height: 28px;
            padding: 0 8px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            font-weight: 800;
            box-shadow: 0 8px 18px rgba(239, 68, 68, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.92);
        }
        .notification-badge.show {
            display: inline-flex;
        }
        .notification-panel {
            position: absolute;
            top: calc(100% + 14px);
            right: 0;
            width: min(92vw, 420px);
            background: #fff;
            border-radius: 28px;
            border: 1px solid rgba(191, 219, 254, 0.9);
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.22);
            overflow: hidden;
            display: none;
            z-index: 1200;
        }
        .notification-panel.open {
            display: block;
        }
        .notification-panel-header {
            padding: 20px 20px 16px;
            background: linear-gradient(135deg, #eff6ff 0%, #fff7cc 100%);
            border-bottom: 1px solid rgba(219, 234, 254, 0.9);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
        }
        .notification-panel-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
        }
        .notification-panel-subtitle {
            margin: 6px 0 0;
            font-size: 0.85rem;
            color: #475569;
            line-height: 1.5;
        }
        .sound-toggle {
            border: none;
            border-radius: 999px;
            padding: 9px 13px;
            background: #0f172a;
            color: #fff;
            font-size: 0.78rem;
            font-weight: 800;
            cursor: pointer;
            white-space: nowrap;
        }
        .sound-toggle.muted {
            background: #cbd5e1;
            color: #334155;
        }
        .notification-panel-body {
            padding: 18px 20px 20px;
            display: grid;
            gap: 16px;
            max-height: min(65vh, 520px);
            overflow-y: auto;
            background: linear-gradient(180deg, #ffffff 0%, #fffaf2 100%);
        }
        .notification-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .notification-summary-card {
            border-radius: 18px;
            padding: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid rgba(219, 234, 254, 0.95);
            box-shadow: 0 12px 24px rgba(148, 163, 184, 0.08);
        }
        .notification-summary-card.proof {
            border-color: #bfdbfe;
            background: linear-gradient(180deg, #f0fdf4 0%, #ecfeff 100%);
        }
        .notification-summary-label {
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            color: #64748b;
            margin-bottom: 6px;
            font-weight: 800;
        }
        .notification-summary-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f172a;
        }
        .notification-summary-note {
            margin-top: 6px;
            font-size: 0.82rem;
            color: #475569;
            line-height: 1.45;
        }
        .notification-list-block {
            display: grid;
            gap: 10px;
        }
        .notification-list-title {
            margin: 0;
            font-size: 0.92rem;
            font-weight: 800;
            color: #1e3a8a;
        }
        .notification-items {
            display: grid;
            gap: 10px;
        }
        .notification-item {
            display: grid;
            grid-template-columns: 44px 1fr;
            gap: 12px;
            align-items: start;
            padding: 12px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 20px rgba(148, 163, 184, 0.08);
        }
        .notification-item-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            background: #dbeafe;
        }
        .notification-item.proof .notification-item-icon {
            background: #d1fae5;
        }
        .notification-item-title {
            margin: 0 0 5px;
            font-size: 0.9rem;
            font-weight: 800;
            color: #0f172a;
        }
        .notification-item-meta {
            margin: 0;
            font-size: 0.83rem;
            color: #475569;
            line-height: 1.5;
        }
        .notification-empty {
            padding: 14px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            color: #64748b;
            font-size: 0.86rem;
            line-height: 1.55;
            text-align: center;
        }
        @media (max-width: 960px) {
            .command-center-body {
                grid-template-columns: 1fr;
            }

            .header-tools {
                width: 100%;
            }

            .notification-panel {
                right: 0;
            }
        }
        @keyframes bellShake {
            0%, 100% { transform: rotate(0deg); }
            20% { transform: rotate(8deg); }
            40% { transform: rotate(-7deg); }
            60% { transform: rotate(5deg); }
            80% { transform: rotate(-4deg); }
        }
        .btn-back {
           display: inline-block;
            margin: 12px 0 12px 0;
            padding: 3px 12px;
            background: #90caf9;
            color: #222;
            border-radius: 50px;
            font-weight: bold;
            font-family: Arial Black, Arial, sans-serif;
            text-decoration: none;
            font-size: 1.05rem;
            box-shadow: 0 2px 8px rgba(44,62,80,0.08);
            transition: background 0.2s;
            border: none;
            letter-spacing: 1px;
        }
        .btn-back:hover {
            background: #bbdefb;
        }

        .payment-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.46);
            backdrop-filter: blur(6px);
            z-index: 999;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .payment-modal-card {
            width: min(100%, 720px);
            max-height: min(88vh, 860px);
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border-radius: 24px;
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.24);
            border: 1px solid rgba(148, 163, 184, 0.25);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .payment-modal-header {
            padding: 24px 28px 18px;
            background: linear-gradient(135deg, #ffffff 0%, #eef5ff 100%);
            border-bottom: 1px solid #dbeafe;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
        }

        .payment-modal-title-wrap {
            max-width: 90%;
        }

        .payment-modal-kicker {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 10px;
        }

        .payment-modal-title {
            margin: 0;
            font-size: 1.65rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.2px;
        }

        .payment-modal-subtitle {
            margin: 8px 0 0;
            color: #475569;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .payment-modal-close {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            border: none;
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 1.8rem;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease, background 0.2s ease;
            flex-shrink: 0;
        }

        .payment-modal-close:hover {
            background: #dbeafe;
            transform: scale(1.04);
        }

        .payment-modal-body {
            padding: 22px 28px;
            overflow-y: auto;
            display: grid;
            gap: 18px;
        }

        .payment-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
        }

        .payment-summary-card {
            padding: 16px 18px;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid #dbeafe;
            box-shadow: 0 10px 24px rgba(30, 64, 175, 0.06);
        }

        .payment-section {
            padding: 18px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            background: #fff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        .payment-section-title {
            margin: 0 0 14px;
            font-size: 1rem;
            font-weight: 800;
            color: #1e3a8a;
        }

        .detail-grid {
            display: grid;
            gap: 10px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: minmax(140px, 180px) 1fr;
            gap: 12px;
            align-items: start;
        }

        .detail-label {
            font-weight: 700;
            color: #1e293b;
        }

        .detail-value {
            color: #334155;
            word-break: break-word;
            line-height: 1.6;
        }

        .payment-status-pill {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            background: #fff7d6;
            color: #8a6200;
            font-weight: 800;
            font-size: 0.85rem;
        }

        .payment-status-pill.completed {
            background: #dcfce7;
            color: #166534;
        }

        .payment-status-pill.failed {
            background: #fee2e2;
            color: #b91c1c;
        }

        .proof-box {
            padding: 16px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid #dbeafe;
        }

        .proof-box img {
            width: 100%;
            max-width: 240px;
            border-radius: 14px;
            border: 1px solid #cbd5e1;
            display: block;
            margin: 14px 0 10px;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        }

        .proof-link {
            color: #003B95;
            font-weight: 700;
            text-decoration: none;
        }

        .proof-link:hover {
            text-decoration: underline;
        }

        .proof-warning {
            padding: 14px 16px;
            border-radius: 14px;
            background: #fff7d6;
            color: #8a6200;
            font-weight: 700;
            line-height: 1.6;
        }

        .payment-modal-footer {
            padding: 18px 28px 22px;
            border-top: 1px solid #dbeafe;
            background: linear-gradient(180deg, rgba(255,255,255,0.96) 0%, #f8fbff 100%);
            display: flex;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
            box-shadow: 0 -10px 20px rgba(148, 163, 184, 0.08);
        }

        .payment-action-btn {
            border: none;
            min-width: 124px;
            padding: 12px 22px;
            border-radius: 12px;
            font-size: 0.98rem;
            font-weight: 800;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.12);
        }

        .payment-action-btn:hover {
            transform: translateY(-1px);
        }

        .payment-action-btn.accept {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: #fff;
        }

        .payment-action-btn.reject {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
        }

        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }

            .container-payment {
                margin: 5px !important;
                padding: 10px;
            }

            .header-row {
                display: block;
            }

            .header-tools {
                width: 100%;
                margin-top: 0;
            }

            .payment-command-center {
                padding: 20px 18px;
                gap: 20px;
            }

            .command-center-topline {
                align-items: flex-start;
            }

            .command-center-eyebrow {
                gap: 10px;
            }

            .header-status-grid {
                grid-template-columns: 1fr;
            }

            .header-status-chip.focus {
                grid-column: auto;
            }

            .notification-bell {
                grid-template-columns: 56px minmax(0, 1fr);
                min-height: auto;
                padding: 16px 18px;
            }

            .notification-bell-icon {
                width: 56px;
                height: 56px;
            }

            .notification-trigger-title {
                font-size: 1.05rem;
            }

            .notification-trigger-meta {
                flex-wrap: wrap;
            }

            .header-row h2 {
                font-size: 1.95rem !important;
                margin: 0 !important;
            }

            .btn-back {
                padding: 8px 12px;
                font-size: 0.9rem;
            }

            .table-wrapper {
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 0 0 10px;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
            }

            table {
                min-width: 750px;
                width: max-content;
                font-size: 0.9rem;
            }

            th, td {
                padding: 10px 8px;
                font-size: 0.85rem;
                white-space: nowrap;
            }

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

            .payment-modal-overlay {
                padding: 12px;
            }

            .payment-modal-card {
                width: 100%;
                max-height: 92vh;
                border-radius: 20px;
            }

            .payment-modal-header,
            .payment-modal-body,
            .payment-modal-footer {
                padding-left: 18px;
                padding-right: 18px;
            }

            .payment-modal-title {
                font-size: 1.35rem;
            }

            .detail-row {
                grid-template-columns: 1fr;
                gap: 4px;
            }

            .payment-action-btn {
                flex: 1 1 160px;
            }
        }

        @media (max-width: 480px) {
            .container-payment {
                margin: 2px !important;
                padding: 8px;
            }

            .header-row h2 {
                font-size: 1.55rem !important;
            }

            .payment-command-center {
                border-radius: 24px;
                padding: 18px 16px;
            }

            .header-kicker,
            .header-live-pill {
                width: 100%;
                justify-content: center;
            }

            .notification-panel {
                width: min(92vw, 360px);
            }

            .table-wrapper {
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 0 0 10px;
                overflow-x: auto;
                overflow-y: hidden;
            }

            table {
                min-width: 700px;
                width: max-content;
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

            .table-wrapper::after {
                content: "<- Swipe untuk lihat lebih ->";
                display: block;
                text-align: center;
                font-size: 0.7rem;
                color: #666;
                padding: 5px 0;
                background: rgba(144, 202, 249, 0.1);
                margin-top: -1px;
            }

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

            .payment-modal-card {
                max-height: 94vh;
            }

            .payment-modal-header,
            .payment-modal-body,
            .payment-modal-footer {
                padding-left: 16px;
                padding-right: 16px;
            }

            .payment-modal-footer {
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container-payment" style="margin-top: 10px;">
        <div class="header-row payment-page-header">
            <div class="header-main">
                <div class="payment-command-center">
                    <div class="command-center-topline">
                        <div class="command-center-eyebrow">
                            <a href="dashboard.php" class="btn-back" aria-label="Back" style="
                                position: relative;
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                margin: 0;
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
                            <span class="header-kicker">Payment Command Center</span>
                        </div>
                        <span class="header-live-pill"><span class="header-live-dot"></span>Auto semak setiap 15 saat</span>
                    </div>
                    <div class="command-center-body">
                        <div class="command-center-copy">
                            <h2 class="page-title">Senarai Pesanan</h2>
                            <p class="header-subtitle">Pantau pesanan masuk, bukti bayaran dan tindakan yang masih tertunggak dari satu ruang kawalan yang lebih jelas dan cepat dibaca.</p>
                            <div class="header-status-grid">
                                <div class="header-status-chip order">
                                    <span class="header-status-label">Pesanan Baru</span>
                                    <div id="headerOrdersPendingCount" class="header-status-value">0</div>
                                    <div class="header-status-meta">Masuk daripada pelajar dan menunggu semakan awal admin.</div>
                                </div>
                                <div class="header-status-chip proof">
                                    <span class="header-status-label">Bukti Bayaran</span>
                                    <div id="headerProofsPendingCount" class="header-status-value">0</div>
                                    <div class="header-status-meta">Slip atau imej bayaran yang sudah dimuat naik dan perlu disahkan.</div>
                                </div>
                                <div class="header-status-chip focus">
                                    <span class="header-status-label">Keutamaan Semasa</span>
                                    <div id="headerAttentionCount" class="header-status-value">0</div>
                                    <div id="headerNotificationLastChecked" class="header-status-meta">Memuatkan status langsung notifikasi pembayaran...</div>
                                </div>
                            </div>
                        </div>
                        <div class="header-tools">
                            <div class="notification-wrapper">
                                <button type="button" id="notificationBell" class="notification-bell" aria-expanded="false" aria-controls="notificationPanel" title="Pusat notifikasi pembayaran">
                                    <span class="notification-bell-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 2a5 5 0 0 0-5 5v2.09c0 .7-.19 1.38-.55 1.97L5.2 13.1A2 2 0 0 0 6.9 16h10.2a2 2 0 0 0 1.7-2.9l-1.25-2.04A3.78 3.78 0 0 1 17 9.09V7a5 5 0 0 0-5-5Zm0 20a3 3 0 0 0 2.83-2H9.17A3 3 0 0 0 12 22Z" />
                                        </svg>
                                    </span>
                                    <span class="notification-trigger-copy">
                                        <span class="notification-trigger-eyebrow">Pusat Amaran</span>
                                        <span id="notificationTriggerTitle" class="notification-trigger-title">Tiada tindakan tertunggak</span>
                                        <span class="notification-trigger-meta">
                                            <span class="notification-trigger-live"><span class="notification-trigger-live-dot"></span>Live monitoring</span>
                                            <span id="notificationTriggerMeta">Klik untuk lihat pesanan dan bukti bayaran terkini</span>
                                        </span>
                                    </span>
                                    <span id="notificationBadge" class="notification-badge">0</span>
                                </button>
                                <div id="notificationPanel" class="notification-panel" aria-hidden="true">
                                    <div class="notification-panel-header">
                                        <div>
                                            <h3 class="notification-panel-title">Notifikasi Pembayaran</h3>
                                            <p id="notificationLastChecked" class="notification-panel-subtitle">Memuatkan kemas kini terkini...</p>
                                        </div>
                                        <button type="button" id="notificationSoundToggle" class="sound-toggle">Bunyi ON</button>
                                    </div>
                                    <div class="notification-panel-body">
                                        <div class="notification-summary-grid">
                                            <div class="notification-summary-card order">
                                                <span class="notification-summary-label">Pesanan Baru</span>
                                                <div id="ordersPendingCount" class="notification-summary-value">0</div>
                                                <div class="notification-summary-note">Pesanan menunggu tindakan awal admin.</div>
                                            </div>
                                            <div class="notification-summary-card proof">
                                                <span class="notification-summary-label">Bukti Bayaran</span>
                                                <div id="proofsPendingCount" class="notification-summary-value">0</div>
                                                <div class="notification-summary-note">Slip bayaran yang sudah dimuat naik dan menunggu semakan.</div>
                                            </div>
                                        </div>
                                        <div class="notification-list-block">
                                            <h4 class="notification-list-title">Pesanan Terkini</h4>
                                            <div id="orderNotificationItems" class="notification-items"></div>
                                        </div>
                                        <div class="notification-list-block">
                                            <h4 class="notification-list-title">Bukti Bayaran Terkini</h4>
                                            <div id="proofNotificationItems" class="notification-items"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                <div id="actionMessage" class="action-message" role="status" aria-live="polite"></div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">Bil</th>
                        <th style="width:180px;">Nama Pelajar</th>
                        <th style="width:180px;">No Kad Pengenalan</th>
                        <th style="width:110px;">Tarikh</th>
                        <th style="width:160px;">Nama Item</th>
                        <th style="width:110px;">Jumlah (RM)</th>
                        <th style="width:110px;">Status</th>
                        <th style="width:110px;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
<?php
require_once 'includes/db_connect.php';
ensureManualPaymentColumnsMysqli($conn);
$result = $conn->query("SELECT * FROM payments ORDER BY id DESC");
$bil = 1;
while ($row = $result->fetch_assoc()) {
    $status_class = 'status-in';
    if ($row['status'] == 'Menunggu') $status_class = 'status-low';
    if ($row['status'] == 'Gagal') $status_class = 'status-out';

    $status_display = $row['status'] == 'Selesai' ? 'Dibayar' : $row['status'];

    $modalPayload = htmlspecialchars(json_encode([
        'name' => $row['student_name'],
        'ic' => $row['student_ic'],
        'date' => date('d/m/Y', strtotime($row['order_date'])),
        'amount' => 'RM ' . number_format($row['amount'], 2),
        'status' => $status_display,
        'item' => $row['item_name'],
        'id' => (int) $row['id'],
        'transactionId' => $row['transaction_id'] ?? '',
        'customerClass' => $row['customer_class'] ?? '',
        'customerPhone' => $row['customer_phone'] ?? '',
        'bankName' => $row['manual_bank_name'] ?: ($row['fpx_bank_id'] ?: $row['bank_code']),
        'paymentMethod' => $row['payment_method'] ?? '',
        'paymentReference' => $row['payment_reference'] ?? '',
        'payerName' => $row['payer_name'] ?? '',
        'proofImagePath' => $row['proof_image_path'] ?? '',
        'proofUploadedAt' => !empty($row['proof_uploaded_at']) ? date('d/m/Y h:i A', strtotime($row['proof_uploaded_at'])) : '',
        'verificationStatus' => $row['verification_status'] ?? '',
        'verificationNotes' => $row['verification_notes'] ?? ''
    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

    echo "<tr>
        <td>{$bil}</td>
        <td>{$row['student_name']}</td>
        <td>{$row['student_ic']}</td>
        <td>" . date('d/m/Y', strtotime($row['order_date'])) . "</td>
        <td>{$row['item_name']}</td>
        <td>RM " . number_format($row['amount'], 2) . "</td>
        <td><span class='{$status_class}' id='status-{$row['id']}'>{$status_display}</span></td>
        <td>
            <button class='btn-edit' data-payment=\"{$modalPayload}\" onclick=\"showViewModal(this)\">View</button>
        </td>
    </tr>";
    $bil++;
}
?>
</tbody>
            </table>
        </div>
    </div>

    <div id="viewModal" class="payment-modal-overlay">
        <div id="viewModalContent" class="payment-modal-card">
            <div class="payment-modal-header">
                <div class="payment-modal-title-wrap">
                    <div class="payment-modal-kicker">Semakan Pembayaran</div>
                    <h3 class="payment-modal-title">Maklumat Pembelian</h3>
                    <p class="payment-modal-subtitle">Semak maklumat pesanan, kaedah bayaran, dan bukti yang dimuat naik sebelum membuat keputusan.</p>
                </div>
                <button class="payment-modal-close" onclick="closeViewModal()" aria-label="Tutup modal">&times;</button>
            </div>
            <div id="viewDetail" class="payment-modal-body"></div>
            <div class="payment-modal-footer">
                <button onclick="acceptAction()" class="payment-action-btn accept">Terima</button>
                <button onclick="cancelOrderAction()" class="payment-action-btn reject">Batal</button>
            </div>
        </div>
    </div>
    <script>
let currentOrderId = null;
let currentOrderHasProof = false;
const actionMessageKey = 'managePaymentsActionMessage';
const notificationBell = document.getElementById('notificationBell');
const notificationBadge = document.getElementById('notificationBadge');
const notificationPanel = document.getElementById('notificationPanel');
const notificationLastChecked = document.getElementById('notificationLastChecked');
const ordersPendingCountElement = document.getElementById('ordersPendingCount');
const proofsPendingCountElement = document.getElementById('proofsPendingCount');
const headerOrdersPendingCountElement = document.getElementById('headerOrdersPendingCount');
const headerProofsPendingCountElement = document.getElementById('headerProofsPendingCount');
const headerAttentionCountElement = document.getElementById('headerAttentionCount');
const headerNotificationLastChecked = document.getElementById('headerNotificationLastChecked');
const orderNotificationItems = document.getElementById('orderNotificationItems');
const proofNotificationItems = document.getElementById('proofNotificationItems');
const notificationSoundToggle = document.getElementById('notificationSoundToggle');
const notificationTriggerTitle = document.getElementById('notificationTriggerTitle');
const notificationTriggerMeta = document.getElementById('notificationTriggerMeta');
let notificationState = {
    initialized: false,
    latestOrderId: 0,
    latestProofTimestamp: 0,
};
let notificationSoundEnabled = localStorage.getItem('managePaymentsSoundEnabled') !== '0';
let notificationAudioContext = null;

function showActionMessage(message, type = 'success') {
    const messageBox = document.getElementById('actionMessage');
    if (!messageBox) return;

    messageBox.className = `action-message ${type}`;
    messageBox.textContent = message;
    messageBox.style.display = 'block';
}

function storeActionMessage(message, type = 'success') {
    sessionStorage.setItem(actionMessageKey, JSON.stringify({ message, type }));
}

function restoreActionMessage() {
    const savedMessage = sessionStorage.getItem(actionMessageKey);
    if (!savedMessage) return;

    sessionStorage.removeItem(actionMessageKey);

    try {
        const parsedMessage = JSON.parse(savedMessage);
        if (parsedMessage.message) {
            showActionMessage(parsedMessage.message, parsedMessage.type || 'success');
        }
    } catch (error) {
        console.error('Gagal membaca mesej tindakan:', error);
    }
}

document.addEventListener('DOMContentLoaded', restoreActionMessage);

function updateSoundToggleState() {
    if (!notificationSoundToggle) return;

    notificationSoundToggle.textContent = notificationSoundEnabled ? 'Bunyi ON' : 'Bunyi OFF';
    notificationSoundToggle.classList.toggle('muted', !notificationSoundEnabled);
}

function initNotificationAudio() {
    if (notificationAudioContext || !(window.AudioContext || window.webkitAudioContext)) {
        return;
    }

    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    notificationAudioContext = new AudioContextClass();
}

async function unlockNotificationAudio() {
    try {
        initNotificationAudio();
        if (notificationAudioContext && notificationAudioContext.state === 'suspended') {
            await notificationAudioContext.resume();
        }
    } catch (error) {
        console.error('Audio notification gagal diaktifkan:', error);
    }
}

function playNotificationSound(type = 'order') {
    if (!notificationSoundEnabled) {
        return;
    }

    unlockNotificationAudio().then(() => {
        if (!notificationAudioContext) {
            return;
        }

        const context = notificationAudioContext;
        const now = context.currentTime;
        const gainNode = context.createGain();
        gainNode.connect(context.destination);
        gainNode.gain.setValueAtTime(0.0001, now);

        const primaryTone = type === 'proof' ? 740 : 880;
        const followTone = type === 'proof' ? 620 : 660;

        const osc1 = context.createOscillator();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(primaryTone, now);
        osc1.connect(gainNode);
        gainNode.gain.exponentialRampToValueAtTime(0.08, now + 0.02);
        gainNode.gain.exponentialRampToValueAtTime(0.0001, now + 0.18);
        osc1.start(now);
        osc1.stop(now + 0.18);

        const osc2 = context.createOscillator();
        osc2.type = 'triangle';
        osc2.frequency.setValueAtTime(followTone, now + 0.2);
        osc2.connect(gainNode);
        gainNode.gain.setValueAtTime(0.0001, now + 0.19);
        gainNode.gain.exponentialRampToValueAtTime(0.06, now + 0.22);
        gainNode.gain.exponentialRampToValueAtTime(0.0001, now + 0.36);
        osc2.start(now + 0.2);
        osc2.stop(now + 0.36);
    }).catch(() => {
    });
}

function renderNotificationItems(container, items, type) {
    if (!container) return;

    if (!Array.isArray(items) || items.length === 0) {
        container.innerHTML = '<div class="notification-empty">Tiada kemas kini baharu dalam kategori ini buat masa ini.</div>';
        return;
    }

    container.innerHTML = items.map((item) => `
        <div class="notification-item ${type}">
            <div class="notification-item-icon">${type === 'proof' ? '&#128179;' : '&#128276;'}</div>
            <div>
                <p class="notification-item-title">${item.studentName || '-'} • ${item.itemName || '-'}</p>
                <p class="notification-item-meta">Rujukan: ${item.transactionId || '-'}<br>Jumlah: ${item.amount || '-'}<br>${type === 'proof' ? 'Bukti dimuat naik' : 'Pesanan masuk'}: ${item.timeLabel || '-'}</p>
            </div>
        </div>
    `).join('');
}

function updateNotificationUI(payload) {
    const totalCount = Number(payload.totalAttentionCount || 0);
    const ordersCount = Number(payload.ordersPendingCount || 0);
    const proofsCount = Number(payload.proofsPendingCount || 0);
    const checkedAtLabel = payload.checkedAt || '-';

    if (notificationBadge) {
        notificationBadge.textContent = totalCount > 99 ? '99+' : String(totalCount);
        notificationBadge.classList.toggle('show', totalCount > 0);
    }

    if (notificationBell) {
        notificationBell.classList.toggle('attention', totalCount > 0);
        notificationBell.setAttribute('aria-expanded', notificationPanel && notificationPanel.classList.contains('open') ? 'true' : 'false');
    }

    if (ordersPendingCountElement) {
        ordersPendingCountElement.textContent = String(ordersCount);
    }
    if (proofsPendingCountElement) {
        proofsPendingCountElement.textContent = String(proofsCount);
    }
    if (headerOrdersPendingCountElement) {
        headerOrdersPendingCountElement.textContent = String(ordersCount);
    }
    if (headerProofsPendingCountElement) {
        headerProofsPendingCountElement.textContent = String(proofsCount);
    }
    if (headerAttentionCountElement) {
        headerAttentionCountElement.textContent = String(totalCount);
    }
    if (notificationLastChecked) {
        notificationLastChecked.textContent = `Dikemas kini pada ${checkedAtLabel}`;
    }
    if (headerNotificationLastChecked) {
        headerNotificationLastChecked.textContent = totalCount > 0
            ? `${totalCount} tindakan perlu diberi perhatian. Semakan terakhir pada ${checkedAtLabel}.`
            : `Semua pesanan dalam keadaan terkawal. Semakan terakhir pada ${checkedAtLabel}.`;
    }
    if (notificationTriggerTitle) {
        notificationTriggerTitle.textContent = totalCount > 0
            ? `${totalCount} tindakan perlu disemak`
            : 'Tiada tindakan tertunggak';
    }
    if (notificationTriggerMeta) {
        notificationTriggerMeta.textContent = totalCount > 0
            ? `Pesanan baru: ${ordersCount} • Bukti bayaran: ${proofsCount}`
            : 'Klik untuk lihat pesanan dan bukti bayaran terkini';
    }

    renderNotificationItems(orderNotificationItems, payload.recentOrders || [], 'order');
    renderNotificationItems(proofNotificationItems, payload.recentProofs || [], 'proof');
}

async function fetchPaymentNotifications() {
    try {
        const response = await fetch('get_payment_notifications.php', {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const payload = await response.json();
        if (!payload.success) {
            throw new Error(payload.message || 'Gagal memuatkan notifikasi pembayaran.');
        }

        const hasNewOrder = notificationState.initialized && Number(payload.latestOrderId || 0) > notificationState.latestOrderId;
        const hasNewProof = notificationState.initialized && Number(payload.latestProofTimestamp || 0) > notificationState.latestProofTimestamp;

        updateNotificationUI(payload);

        if (hasNewOrder || hasNewProof) {
            if (notificationBell) {
                notificationBell.classList.remove('has-update');
                void notificationBell.offsetWidth;
                notificationBell.classList.add('has-update');
            }

            if (hasNewProof && hasNewOrder) {
                showActionMessage('Pesanan baharu dan bukti bayaran baharu diterima. Sila semak notifikasi.', 'success');
                playNotificationSound('proof');
            } else if (hasNewProof) {
                showActionMessage('Bukti bayaran baharu telah dimuat naik. Sila semak notifikasi.', 'success');
                playNotificationSound('proof');
            } else if (hasNewOrder) {
                showActionMessage('Pesanan baharu telah masuk. Sila semak notifikasi.', 'success');
                playNotificationSound('order');
            }
        }

        notificationState = {
            initialized: true,
            latestOrderId: Number(payload.latestOrderId || 0),
            latestProofTimestamp: Number(payload.latestProofTimestamp || 0),
        };
    } catch (error) {
        console.error('Gagal memuatkan notifikasi pembayaran:', error);
        if (notificationLastChecked) {
            notificationLastChecked.textContent = 'Notifikasi tidak dapat dimuatkan buat masa ini.';
        }
        if (headerNotificationLastChecked) {
            headerNotificationLastChecked.textContent = 'Status live tidak dapat dimuatkan buat masa ini. Cuba semula sebentar lagi.';
        }
        if (notificationTriggerTitle) {
            notificationTriggerTitle.textContent = 'Semakan notifikasi tergendala';
        }
        if (notificationTriggerMeta) {
            notificationTriggerMeta.textContent = 'Muat semula halaman atau cuba semula sebentar lagi';
        }
    }
}

function toggleNotificationPanel(forceOpen = null) {
    if (!notificationPanel) return;

    const shouldOpen = typeof forceOpen === 'boolean'
        ? forceOpen
        : !notificationPanel.classList.contains('open');

    notificationPanel.classList.toggle('open', shouldOpen);
    notificationPanel.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');

    if (notificationBell) {
        notificationBell.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        if (shouldOpen) {
            notificationBell.classList.remove('has-update');
        }
    }
}

function initPaymentNotifications() {
    updateSoundToggleState();
    fetchPaymentNotifications();
    window.setInterval(fetchPaymentNotifications, 15000);

    if (notificationBell) {
        notificationBell.addEventListener('click', function(event) {
            event.stopPropagation();
            toggleNotificationPanel();
        });
    }

    if (notificationSoundToggle) {
        notificationSoundToggle.addEventListener('click', async function(event) {
            event.stopPropagation();
            notificationSoundEnabled = !notificationSoundEnabled;
            localStorage.setItem('managePaymentsSoundEnabled', notificationSoundEnabled ? '1' : '0');
            updateSoundToggleState();
            if (notificationSoundEnabled) {
                await unlockNotificationAudio();
            }
        });
    }

    document.addEventListener('click', function(event) {
        if (!notificationPanel || !notificationBell) {
            return;
        }

        const clickedInsidePanel = notificationPanel.contains(event.target);
        const clickedBell = notificationBell.contains(event.target);

        if (!clickedInsidePanel && !clickedBell) {
            toggleNotificationPanel(false);
        }
    });

    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            toggleNotificationPanel(false);
        }
    });

    document.addEventListener('click', unlockNotificationAudio, { passive: true });
    document.addEventListener('keydown', unlockNotificationAudio, { passive: true });
}

document.addEventListener('DOMContentLoaded', initPaymentNotifications);

function showViewModal(button) {
    const rawData = button.dataset.payment || '{}';
    const payment = JSON.parse(rawData);
    currentOrderId = payment.id || null;
    currentOrderHasProof = Boolean(payment.proofImagePath);

    const normalizedStatus = (payment.status || '').toLowerCase();
    let statusClass = '';
    if (normalizedStatus.includes('dibayar') || normalizedStatus.includes('selesai')) {
        statusClass = 'completed';
    } else if (normalizedStatus.includes('gagal') || normalizedStatus.includes('batal')) {
        statusClass = 'failed';
    }

    const proofMarkup = payment.proofImagePath
        ? `<div class="proof-box">
                <div class="detail-row">
                    <div class="detail-label">Lampiran</div>
                    <div class="detail-value">
                        <img src="${payment.proofImagePath}" alt="Bukti bayaran">
                        <a href="${payment.proofImagePath}" target="_blank" class="proof-link">Lihat bukti penuh</a>
                    </div>
                </div>
           </div>`
        : `<div class="proof-warning">Bukti bayaran belum dimuat naik oleh pelajar.</div>`;

    document.getElementById('viewDetail').innerHTML = `
        <div class="payment-summary-grid">
            <div class="payment-summary-card">
                <div class="detail-label">Nama Pelajar</div>
                <div class="detail-value">${payment.name || '-'}</div>
            </div>
            <div class="payment-summary-card">
                <div class="detail-label">No Kad Pengenalan</div>
                <div class="detail-value">${payment.ic || '-'}</div>
            </div>
            <div class="payment-summary-card">
                <div class="detail-label">Jumlah Bayaran</div>
                <div class="detail-value">${payment.amount || '-'}</div>
            </div>
            <div class="payment-summary-card">
                <div class="detail-label">Status Pesanan</div>
                <div class="detail-value"><span class="payment-status-pill ${statusClass}" id="modal-status">${payment.status || '-'}</span></div>
            </div>
        </div>

        <div class="payment-section">
            <h4 class="payment-section-title">Butiran Pesanan</h4>
            <div class="detail-grid">
                <div class="detail-row"><div class="detail-label">Tarikh</div><div class="detail-value">${payment.date || '-'}</div></div>
                <div class="detail-row"><div class="detail-label">Nama Item</div><div class="detail-value">${payment.item || '-'}</div></div>
                <div class="detail-row"><div class="detail-label">Rujukan Pesanan</div><div class="detail-value">${payment.transactionId || '-'}</div></div>
            </div>
        </div>

        <div class="payment-section">
            <h4 class="payment-section-title">Butiran Pembayaran</h4>
            <div class="detail-grid">
                <div class="detail-row"><div class="detail-label">Kaedah Bayaran</div><div class="detail-value">${payment.paymentMethod || '-'}</div></div>
                <div class="detail-row"><div class="detail-label">Bank / Saluran</div><div class="detail-value">${payment.bankName || '-'}</div></div>
                <div class="detail-row"><div class="detail-label">Nama Pengirim</div><div class="detail-value">${payment.payerName || '-'}</div></div>
                <div class="detail-row"><div class="detail-label">Rujukan Bayaran</div><div class="detail-value">${payment.paymentReference || '-'}</div></div>
                <div class="detail-row"><div class="detail-label">Bukti Dimuat Naik</div><div class="detail-value">${payment.proofUploadedAt || '-'}</div></div>
                <div class="detail-row"><div class="detail-label">Status Semakan</div><div class="detail-value">${payment.verificationStatus || '-'}</div></div>
                ${payment.verificationNotes ? `<div class="detail-row"><div class="detail-label">Nota</div><div class="detail-value">${payment.verificationNotes}</div></div>` : ''}
            </div>
        </div>

        <div class="payment-section">
            <h4 class="payment-section-title">Bukti Bayaran</h4>
            ${proofMarkup}
        </div>
    `;
    document.getElementById('viewModal').style.display = 'flex';
}
function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}
function acceptAction() {
    if (!currentOrderId) return;
    if (!currentOrderHasProof) {
        alert('Bukti bayaran belum dimuat naik. Sila tunggu pelajar memuat naik slip sebelum mengesahkan pembayaran.');
        return;
    }
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
            const successMessage = status === 'Selesai'
                ? `Status dikemaskini dan resit berjaya dijana${data.receiptNo ? ` (${data.receiptNo})` : ''}.`
                : 'Status dikemaskini dengan berjaya.';

            storeActionMessage(successMessage, 'success');
            location.reload();
        } else {
            showActionMessage(data.message || 'Gagal kemaskini status!', 'error');
        }
    })
    .catch(() => {
        showActionMessage('Gagal berhubung dengan pelayan semasa mengemaskini status.', 'error');
    });
    closeViewModal();
}
    </script>
</body>
</html>