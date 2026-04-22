<?php
if (!function_exists('render_admin_notification_center')) {
    function render_admin_notification_center(): void
    {
        ?>
        <style>
            .admin-notify-shell {
                position: fixed;
                right: 24px;
                bottom: 24px;
                z-index: 1400;
            }

            .admin-notify-bell {
                width: 62px;
                height: 62px;
                border: none;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
                color: #ffffff;
                box-shadow: 0 18px 36px rgba(245, 158, 11, 0.36);
                cursor: pointer;
                position: relative;
                transition: transform 0.24s ease, box-shadow 0.24s ease;
            }

            .admin-notify-bell:hover {
                transform: translateY(-2px) scale(1.02);
                box-shadow: 0 22px 42px rgba(249, 115, 22, 0.42);
            }

            .admin-notify-bell svg {
                width: 28px;
                height: 28px;
                fill: currentColor;
            }

            .admin-notify-bell.attention {
                box-shadow: 0 0 0 8px rgba(251, 191, 36, 0.18), 0 18px 36px rgba(245, 158, 11, 0.36);
            }

            .admin-notify-bell.has-update {
                animation: adminNotifyShake 0.7s ease;
            }

            .admin-notify-badge {
                position: absolute;
                top: -4px;
                right: -2px;
                min-width: 24px;
                height: 24px;
                padding: 0 6px;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #dc2626;
                color: #ffffff;
                font-size: 0.75rem;
                font-weight: 700;
                border: 2px solid #ffffff;
                opacity: 0;
                transform: scale(0.7);
                transition: opacity 0.2s ease, transform 0.2s ease;
            }

            .admin-notify-badge.show {
                opacity: 1;
                transform: scale(1);
            }

            .admin-notify-panel {
                position: absolute;
                right: 0;
                bottom: 78px;
                width: min(380px, calc(100vw - 32px));
                max-height: min(76vh, 720px);
                display: flex;
                flex-direction: column;
                background: #ffffff;
                border: 1px solid rgba(148, 163, 184, 0.18);
                border-radius: 24px;
                box-shadow: 0 28px 60px rgba(15, 23, 42, 0.18);
                overflow: hidden;
                opacity: 0;
                pointer-events: none;
                transform: translateY(16px) scale(0.98);
                transition: opacity 0.24s ease, transform 0.24s ease;
            }

            .admin-notify-panel.open {
                opacity: 1;
                pointer-events: auto;
                transform: translateY(0) scale(1);
            }

            .admin-notify-header {
                padding: 18px 20px 14px;
                display: flex;
                justify-content: space-between;
                gap: 12px;
                align-items: flex-start;
                background: linear-gradient(135deg, #fff7cc 0%, #ffedd5 100%);
                border-bottom: 1px solid rgba(245, 158, 11, 0.16);
            }

            .admin-notify-title {
                margin: 0;
                font-size: 1rem;
                font-weight: 800;
                color: #1f2937;
            }

            .admin-notify-subtitle {
                margin: 6px 0 0;
                font-size: 0.82rem;
                color: #6b7280;
                line-height: 1.45;
            }

            .admin-notify-sound-toggle {
                border: none;
                border-radius: 999px;
                padding: 9px 14px;
                background: rgba(255, 255, 255, 0.9);
                color: #92400e;
                font-size: 0.8rem;
                font-weight: 700;
                cursor: pointer;
                white-space: nowrap;
            }

            .admin-notify-sound-toggle.muted {
                background: #e5e7eb;
                color: #4b5563;
            }

            .admin-notify-body {
                padding: 18px 20px 20px;
                overflow-y: auto;
                background: linear-gradient(180deg, #ffffff 0%, #fffaf2 100%);
            }

            .admin-notify-summary-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
                margin-bottom: 18px;
            }

            .admin-notify-summary-card {
                padding: 14px;
                border-radius: 18px;
                background: #ffffff;
                border: 1px solid rgba(226, 232, 240, 0.9);
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            }

            .admin-notify-summary-card.order {
                background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
            }

            .admin-notify-summary-card.proof {
                background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 100%);
            }

            .admin-notify-summary-label {
                display: block;
                font-size: 0.76rem;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                font-weight: 700;
                color: #64748b;
                margin-bottom: 8px;
            }

            .admin-notify-summary-value {
                font-size: 1.85rem;
                font-weight: 800;
                color: #111827;
                line-height: 1;
            }

            .admin-notify-summary-note {
                margin-top: 8px;
                font-size: 0.82rem;
                color: #6b7280;
                line-height: 1.45;
            }

            .admin-notify-list-block + .admin-notify-list-block {
                margin-top: 18px;
            }

            .admin-notify-list-title {
                margin: 0 0 10px;
                font-size: 0.92rem;
                font-weight: 800;
                color: #1f2937;
            }

            .admin-notify-items {
                display: grid;
                gap: 10px;
            }

            .admin-notify-item {
                display: grid;
                grid-template-columns: 42px minmax(0, 1fr);
                gap: 12px;
                align-items: flex-start;
                padding: 12px 13px;
                border-radius: 16px;
                background: #ffffff;
                border: 1px solid rgba(226, 232, 240, 0.9);
            }

            .admin-notify-item-icon {
                width: 42px;
                height: 42px;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.15rem;
                background: rgba(59, 130, 246, 0.12);
                color: #1d4ed8;
            }

            .admin-notify-item.proof .admin-notify-item-icon {
                background: rgba(16, 185, 129, 0.12);
                color: #047857;
            }

            .admin-notify-item-title {
                margin: 0;
                font-size: 0.9rem;
                font-weight: 700;
                color: #111827;
            }

            .admin-notify-item-meta {
                margin: 6px 0 0;
                font-size: 0.8rem;
                color: #6b7280;
                line-height: 1.5;
            }

            .admin-notify-empty {
                padding: 14px;
                border-radius: 16px;
                background: rgba(248, 250, 252, 0.92);
                border: 1px dashed rgba(148, 163, 184, 0.38);
                color: #64748b;
                font-size: 0.84rem;
                line-height: 1.5;
            }

            .admin-notify-toast {
                position: fixed;
                right: 24px;
                bottom: 104px;
                max-width: min(360px, calc(100vw - 32px));
                padding: 14px 16px;
                border-radius: 16px;
                color: #ffffff;
                font-size: 0.92rem;
                font-weight: 600;
                line-height: 1.45;
                box-shadow: 0 18px 38px rgba(15, 23, 42, 0.22);
                opacity: 0;
                transform: translateY(16px);
                transition: opacity 0.24s ease, transform 0.24s ease;
                z-index: 1401;
                pointer-events: none;
            }

            .admin-notify-toast.show {
                opacity: 1;
                transform: translateY(0);
            }

            .admin-notify-toast.success {
                background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            }

            .admin-notify-toast.error {
                background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            }

            @keyframes adminNotifyShake {
                0%, 100% { transform: rotate(0deg); }
                20% { transform: rotate(-10deg); }
                40% { transform: rotate(8deg); }
                60% { transform: rotate(-5deg); }
                80% { transform: rotate(3deg); }
            }

            @media (max-width: 768px) {
                .admin-notify-shell {
                    right: 16px;
                    bottom: 16px;
                }

                .admin-notify-panel {
                    right: 0;
                    bottom: 76px;
                    width: min(360px, calc(100vw - 20px));
                }

                .admin-notify-summary-grid {
                    grid-template-columns: 1fr;
                }

                .admin-notify-header {
                    flex-direction: column;
                    align-items: stretch;
                }

                .admin-notify-sound-toggle {
                    align-self: flex-start;
                }

                .admin-notify-toast {
                    right: 16px;
                    bottom: 94px;
                }
            }
        </style>

        <div class="admin-notify-shell">
            <button type="button" id="adminGlobalNotificationBell" class="admin-notify-bell" aria-expanded="false" aria-controls="adminGlobalNotificationPanel" title="Pusat notifikasi pembayaran">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 2a5 5 0 0 0-5 5v2.09c0 .7-.19 1.38-.55 1.97L5.2 13.1A2 2 0 0 0 6.9 16h10.2a2 2 0 0 0 1.7-2.9l-1.25-2.04A3.78 3.78 0 0 1 17 9.09V7a5 5 0 0 0-5-5Zm0 20a3 3 0 0 0 2.83-2H9.17A3 3 0 0 0 12 22Z" />
                </svg>
                <span id="adminGlobalNotificationBadge" class="admin-notify-badge">0</span>
            </button>

            <div id="adminGlobalNotificationPanel" class="admin-notify-panel" aria-hidden="true">
                <div class="admin-notify-header">
                    <div>
                        <h3 class="admin-notify-title">Notifikasi Pembayaran</h3>
                        <p id="adminGlobalNotificationLastChecked" class="admin-notify-subtitle">Memuatkan kemas kini terkini...</p>
                    </div>
                    <button type="button" id="adminGlobalNotificationSoundToggle" class="admin-notify-sound-toggle">Bunyi ON</button>
                </div>
                <div class="admin-notify-body">
                    <div class="admin-notify-summary-grid">
                        <div class="admin-notify-summary-card order">
                            <span class="admin-notify-summary-label">Pesanan Baru</span>
                            <div id="adminGlobalOrdersPendingCount" class="admin-notify-summary-value">0</div>
                            <div class="admin-notify-summary-note">Pesanan menunggu tindakan awal admin.</div>
                        </div>
                        <div class="admin-notify-summary-card proof">
                            <span class="admin-notify-summary-label">Bukti Bayaran</span>
                            <div id="adminGlobalProofsPendingCount" class="admin-notify-summary-value">0</div>
                            <div class="admin-notify-summary-note">Slip bayaran yang menunggu semakan admin.</div>
                        </div>
                    </div>

                    <div class="admin-notify-list-block">
                        <h4 class="admin-notify-list-title">Pesanan Terkini</h4>
                        <div id="adminGlobalOrderNotificationItems" class="admin-notify-items"></div>
                    </div>

                    <div class="admin-notify-list-block">
                        <h4 class="admin-notify-list-title">Bukti Bayaran Terkini</h4>
                        <div id="adminGlobalProofNotificationItems" class="admin-notify-items"></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="adminGlobalNotificationToast" class="admin-notify-toast" role="status" aria-live="polite"></div>

        <script>
            (function() {
                const bell = document.getElementById('adminGlobalNotificationBell');
                const badge = document.getElementById('adminGlobalNotificationBadge');
                const panel = document.getElementById('adminGlobalNotificationPanel');
                const lastChecked = document.getElementById('adminGlobalNotificationLastChecked');
                const ordersCountElement = document.getElementById('adminGlobalOrdersPendingCount');
                const proofsCountElement = document.getElementById('adminGlobalProofsPendingCount');
                const ordersList = document.getElementById('adminGlobalOrderNotificationItems');
                const proofsList = document.getElementById('adminGlobalProofNotificationItems');
                const soundToggle = document.getElementById('adminGlobalNotificationSoundToggle');
                const toast = document.getElementById('adminGlobalNotificationToast');

                if (!bell || !panel) {
                    return;
                }

                let notificationState = {
                    initialized: false,
                    latestOrderId: 0,
                    latestProofTimestamp: 0,
                };
                let soundEnabled = localStorage.getItem('managePaymentsSoundEnabled') !== '0';
                let audioContext = null;
                let toastTimer = null;

                function updateSoundToggleState() {
                    if (!soundToggle) {
                        return;
                    }

                    soundToggle.textContent = soundEnabled ? 'Bunyi ON' : 'Bunyi OFF';
                    soundToggle.classList.toggle('muted', !soundEnabled);
                }

                function initAudio() {
                    if (audioContext || !(window.AudioContext || window.webkitAudioContext)) {
                        return;
                    }

                    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                    audioContext = new AudioContextClass();
                }

                async function unlockAudio() {
                    try {
                        initAudio();
                        if (audioContext && audioContext.state === 'suspended') {
                            await audioContext.resume();
                        }
                    } catch (error) {
                        console.error('Audio notification gagal diaktifkan:', error);
                    }
                }

                function playSound(type) {
                    if (!soundEnabled) {
                        return;
                    }

                    unlockAudio().then(() => {
                        if (!audioContext) {
                            return;
                        }

                        const context = audioContext;
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
                        // Bunyi gagal tidak patut mengganggu aliran utama.
                    });
                }

                function showToast(message, type) {
                    if (!toast) {
                        return;
                    }

                    toast.className = 'admin-notify-toast ' + (type || 'success');
                    toast.textContent = message;
                    toast.classList.add('show');

                    if (toastTimer) {
                        window.clearTimeout(toastTimer);
                    }

                    toastTimer = window.setTimeout(() => {
                        toast.classList.remove('show');
                    }, 3200);
                }

                function renderItems(container, items, type) {
                    if (!container) {
                        return;
                    }

                    if (!Array.isArray(items) || items.length === 0) {
                        container.innerHTML = '<div class="admin-notify-empty">Tiada kemas kini baharu dalam kategori ini buat masa ini.</div>';
                        return;
                    }

                    container.innerHTML = items.map((item) => `
                        <div class="admin-notify-item ${type}">
                            <div class="admin-notify-item-icon">${type === 'proof' ? '&#128179;' : '&#128276;'}</div>
                            <div>
                                <p class="admin-notify-item-title">${item.studentName || '-'} • ${item.itemName || '-'}</p>
                                <p class="admin-notify-item-meta">Rujukan: ${item.transactionId || '-'}<br>Jumlah: ${item.amount || '-'}<br>${type === 'proof' ? 'Bukti dimuat naik' : 'Pesanan masuk'}: ${item.timeLabel || '-'}</p>
                            </div>
                        </div>
                    `).join('');
                }

                function updateUI(payload) {
                    const totalCount = Number(payload.totalAttentionCount || 0);
                    const ordersCount = Number(payload.ordersPendingCount || 0);
                    const proofsCount = Number(payload.proofsPendingCount || 0);

                    if (badge) {
                        badge.textContent = totalCount > 99 ? '99+' : String(totalCount);
                        badge.classList.toggle('show', totalCount > 0);
                    }

                    bell.classList.toggle('attention', totalCount > 0);
                    bell.setAttribute('aria-expanded', panel.classList.contains('open') ? 'true' : 'false');

                    if (ordersCountElement) {
                        ordersCountElement.textContent = String(ordersCount);
                    }
                    if (proofsCountElement) {
                        proofsCountElement.textContent = String(proofsCount);
                    }
                    if (lastChecked) {
                        lastChecked.textContent = `Dikemas kini pada ${payload.checkedAt || '-'}`;
                    }

                    renderItems(ordersList, payload.recentOrders || [], 'order');
                    renderItems(proofsList, payload.recentProofs || [], 'proof');
                }

                async function fetchNotifications() {
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

                        updateUI(payload);

                        if (hasNewOrder || hasNewProof) {
                            bell.classList.remove('has-update');
                            void bell.offsetWidth;
                            bell.classList.add('has-update');

                            if (hasNewProof && hasNewOrder) {
                                showToast('Pesanan baharu dan bukti bayaran baharu diterima. Sila semak notifikasi.', 'success');
                                playSound('proof');
                            } else if (hasNewProof) {
                                showToast('Bukti bayaran baharu telah dimuat naik. Sila semak notifikasi.', 'success');
                                playSound('proof');
                            } else {
                                showToast('Pesanan baharu telah masuk. Sila semak notifikasi.', 'success');
                                playSound('order');
                            }
                        }

                        notificationState = {
                            initialized: true,
                            latestOrderId: Number(payload.latestOrderId || 0),
                            latestProofTimestamp: Number(payload.latestProofTimestamp || 0),
                        };
                    } catch (error) {
                        console.error('Gagal memuatkan notifikasi pembayaran:', error);
                        if (lastChecked) {
                            lastChecked.textContent = 'Notifikasi tidak dapat dimuatkan buat masa ini.';
                        }
                    }
                }

                function togglePanel(forceOpen) {
                    const shouldOpen = typeof forceOpen === 'boolean'
                        ? forceOpen
                        : !panel.classList.contains('open');

                    panel.classList.toggle('open', shouldOpen);
                    panel.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
                    bell.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');

                    if (shouldOpen) {
                        bell.classList.remove('has-update');
                    }
                }

                updateSoundToggleState();
                fetchNotifications();
                window.setInterval(fetchNotifications, 15000);

                bell.addEventListener('click', function(event) {
                    event.stopPropagation();
                    togglePanel();
                });

                if (soundToggle) {
                    soundToggle.addEventListener('click', async function(event) {
                        event.stopPropagation();
                        soundEnabled = !soundEnabled;
                        localStorage.setItem('managePaymentsSoundEnabled', soundEnabled ? '1' : '0');
                        updateSoundToggleState();
                        if (soundEnabled) {
                            await unlockAudio();
                        }
                    });
                }

                document.addEventListener('click', function(event) {
                    const clickedInsidePanel = panel.contains(event.target);
                    const clickedBell = bell.contains(event.target);
                    if (!clickedInsidePanel && !clickedBell) {
                        togglePanel(false);
                    }
                });

                window.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') {
                        togglePanel(false);
                    }
                });

                document.addEventListener('click', unlockAudio, { passive: true });
                document.addEventListener('keydown', unlockAudio, { passive: true });
            })();
        </script>
        <?php
    }
}
