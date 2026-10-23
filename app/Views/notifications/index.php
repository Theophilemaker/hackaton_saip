<?php
$notifications = $notifications ?? ($notifs ?? []);
$unread        = $unread        ?? 0;

if (empty($unread) && !empty($notifications)) {
    foreach ($notifications as $n) {
        if (empty($n['is_read'])) $unread++;
    }
}

function notifIcon(string $type): string {
    return match($type) {
        'alert'    => '🚨',
        'warning'  => '⚠️',
        'success'  => '✅',
        'market'   => '📈',
        'weather'  => '🌤️',
        'message'  => '💬',
        'crop'     => '🌾',
        'system'   => '⚙️',
        default    => '🔔',
    };
}
?>
<div class="dashboard-layout notif-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="main-content">

            <!-- ═══════════ HERO ═══════════ -->
            <section class="glass-card notif-hero">
                <div class="hero-left">
                    <span class="hero-tag">
                        <span class="live-dot"></span> UPDATES
                    </span>
                    <h1>🔔 Notifications</h1>
                    <p>Stay up to date with alerts, market prices, weather, and system updates.</p>
                </div>
                <div class="hero-actions">
                    <?php if ($unread > 0): ?>
                        <a href="<?= BASE_URL ?>/notifications/markAllRead" class="btn-hero-primary">
                            ✓ Mark all read
                        </a>
                    <?php endif; ?>
                </div>
            </section>

            <!-- ═══════════ STATS ═══════════ -->
            <section class="kpi-strip">
                <div class="kpi-card kpi-total">
                    <span class="kpi-icon">🔔</span>
                    <div>
                        <span class="kpi-label">Total</span>
                        <strong class="kpi-value"><?= number_format(count($notifications)) ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-unread">
                    <span class="kpi-icon">🔴</span>
                    <div>
                        <span class="kpi-label">Unread</span>
                        <strong class="kpi-value"><?= number_format($unread) ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-read">
                    <span class="kpi-icon">✅</span>
                    <div>
                        <span class="kpi-label">Read</span>
                        <strong class="kpi-value"><?= number_format(max(0, count($notifications) - $unread)) ?></strong>
                    </div>
                </div>
            </section>

            <!-- ═══════════ LIST ═══════════ -->
            <section class="glass-card notif-card">
                <div class="card-head">
                    <div>
                        <h3>📥 Recent Activity</h3>
                        <p><?= count($notifications) ?> notification<?= count($notifications) === 1 ? '' : 's' ?></p>
                    </div>
                </div>

                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <div class="empty-emoji">🔕</div>
                        <h3>No notifications</h3>
                        <p>You're all caught up. New alerts will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="notif-list">
                        <?php foreach ($notifications as $n): ?>
                            <?php $isUnread = empty($n['is_read']); ?>
                            <div class="notif-row <?= $isUnread ? 'unread' : '' ?>">
                                <div class="notif-ico"><?= notifIcon($n['type'] ?? 'default') ?></div>
                                <div class="notif-content">
                                    <strong><?= e($n['title'] ?? 'Notification') ?></strong>
                                    <?php if (!empty($n['message'])): ?>
                                        <p><?= e($n['message']) ?></p>
                                    <?php endif; ?>
                                    <small>🕐 <?= !empty($n['created_at']) ? date('M d, Y H:i', strtotime($n['created_at'])) : '' ?></small>
                                </div>
                                <?php if ($isUnread): ?>
                                    <a href="<?= BASE_URL ?>/notifications/markRead/<?= (int)$n['id'] ?>"
                                       class="notif-check" title="Mark as read">✓</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        </main>
    </div>
</div>

<style>
/* ═══════════════════════════════════════════════════════════
   LAYOUT — matches the pattern used by other working views
   ═══════════════════════════════════════════════════════════ */
html, body {
    margin: 0 !important;
    padding: 0 !important;
    overflow-x: hidden !important;
}

.notif-page.dashboard-layout {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    min-height: 100vh !important;
    background: #0b1120 !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
}

.notif-page .main-wrap {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    min-height: 100vh !important;
    box-sizing: border-box !important;
}

.notif-page .main-wrap > .main-content {
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    padding: 20px 28px 60px !important;
    box-sizing: border-box !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 18px !important;
}

.notif-page .glass-card {
    background: rgba(30, 41, 59, 0.7) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 22px !important;
    margin: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

/* ═══════════════════════════════════════════════════════════
   HERO
   ═══════════════════════════════════════════════════════════ */
.notif-hero {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 20px !important;
    flex-wrap: wrap !important;
    padding: 24px 28px !important;
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(16, 185, 129, 0.08)) !important;
    position: relative !important;
    overflow: hidden !important;
}

.notif-hero::before {
    content: '' !important;
    position: absolute !important;
    top: -40% !important;
    right: -10% !important;
    width: 380px !important;
    height: 380px !important;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.15), transparent 70%) !important;
    pointer-events: none !important;
}

.hero-left { flex: 1 !important; min-width: 260px !important; position: relative !important; z-index: 1 !important; }

.hero-tag {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 4px 12px !important;
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.35) !important;
    border-radius: 999px !important;
    font-size: 10.5px !important;
    font-weight: 800 !important;
    letter-spacing: 1.2px !important;
    text-transform: uppercase !important;
    margin-bottom: 10px !important;
}

.live-dot {
    width: 6px !important;
    height: 6px !important;
    background: #38bdf8 !important;
    border-radius: 50% !important;
    animation: pulse 1.6s infinite !important;
    box-shadow: 0 0 8px #38bdf8 !important;
}

@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }

.notif-hero h1 {
    font-size: 26px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 0 8px 0 !important;
}

.notif-hero p {
    color: #94a3b8 !important;
    font-size: 13.5px !important;
    margin: 0 !important;
    line-height: 1.55 !important;
    max-width: 600px !important;
}

.hero-actions { display: flex !important; gap: 10px !important; position: relative !important; z-index: 1 !important; }

.btn-hero-primary {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 12px 22px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 12px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    text-decoration: none !important;
    font-family: inherit !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35) !important;
    transition: all 0.2s !important;
}

.btn-hero-primary:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 22px rgba(16, 185, 129, 0.55) !important;
}

/* ═══════════════════════════════════════════════════════════
   KPI STRIP
   ═══════════════════════════════════════════════════════════ */
.kpi-strip {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important;
    gap: 14px !important;
}

.kpi-card {
    display: flex !important;
    align-items: center !important;
    gap: 14px !important;
    padding: 18px 20px !important;
    background: rgba(30, 41, 59, 0.7) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 16px !important;
    position: relative !important;
    overflow: hidden !important;
    transition: all 0.25s !important;
    box-sizing: border-box !important;
}

.kpi-card::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 4px !important;
    height: 100% !important;
}

.kpi-total::before  { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.kpi-unread::before { background: linear-gradient(180deg, #ef4444, #dc2626) !important; }
.kpi-read::before   { background: linear-gradient(180deg, #10b981, #059669) !important; }

.kpi-card:hover { transform: translateY(-3px) !important; border-color: rgba(56, 189, 248, 0.3) !important; }

.kpi-icon { font-size: 26px !important; flex-shrink: 0 !important; }

.kpi-label {
    display: block !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
    margin-bottom: 4px !important;
}

.kpi-value {
    display: block !important;
    font-size: 22px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    line-height: 1.1 !important;
    font-variant-numeric: tabular-nums !important;
}

/* ═══════════════════════════════════════════════════════════
   NOTIFICATIONS CARD
   ═══════════════════════════════════════════════════════════ */
.notif-card { padding: 22px !important; }

.card-head {
    display: flex !important;
    justify-content: space-between !important;
    align-items: flex-start !important;
    gap: 12px !important;
    margin-bottom: 16px !important;
    padding-bottom: 12px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    flex-wrap: wrap !important;
}

.card-head h3 {
    font-size: 16px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 0 4px 0 !important;
}

.card-head p {
    font-size: 12.5px !important;
    color: #94a3b8 !important;
    margin: 0 !important;
}

.notif-list { display: flex !important; flex-direction: column !important; gap: 8px !important; }

.notif-row {
    display: flex !important;
    align-items: flex-start !important;
    gap: 14px !important;
    padding: 14px 18px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
    border-left: 4px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 12px !important;
    transition: all 0.2s !important;
}

.notif-row:hover {
    background: rgba(56, 189, 248, 0.05) !important;
    border-color: rgba(56, 189, 248, 0.2) !important;
}

.notif-row.unread {
    border-left-color: #38bdf8 !important;
    background: rgba(56, 189, 248, 0.06) !important;
}

.notif-ico {
    font-size: 22px !important;
    flex-shrink: 0 !important;
    width: 44px !important;
    height: 44px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: rgba(56, 189, 248, 0.1) !important;
    border-radius: 11px !important;
}

.notif-content { flex: 1 !important; min-width: 0 !important; }

.notif-content strong {
    display: block !important;
    font-size: 13.5px !important;
    color: #f1f5f9 !important;
    font-weight: 700 !important;
    margin-bottom: 3px !important;
}

.notif-content p {
    font-size: 12.5px !important;
    color: #cbd5e1 !important;
    margin: 0 0 6px 0 !important;
    line-height: 1.5 !important;
}

.notif-content small {
    font-size: 11px !important;
    color: #64748b !important;
}

.notif-check {
    width: 32px !important;
    height: 32px !important;
    background: rgba(16, 185, 129, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(16, 185, 129, 0.35) !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-decoration: none !important;
    font-size: 13px !important;
    flex-shrink: 0 !important;
    transition: all 0.15s !important;
}

.notif-check:hover {
    background: rgba(16, 185, 129, 0.3) !important;
    transform: scale(1.1) !important;
}

/* ═══════════════════════════════════════════════════════════
   EMPTY STATE
   ═══════════════════════════════════════════════════════════ */
.empty-state {
    text-align: center !important;
    padding: 60px 24px !important;
}

.empty-emoji { font-size: 60px !important; margin-bottom: 14px !important; opacity: 0.5 !important; }

.empty-state h3 {
    font-size: 17px !important;
    color: #f1f5f9 !important;
    margin: 0 0 6px 0 !important;
    font-weight: 700 !important;
}

.empty-state p {
    color: #94a3b8 !important;
    font-size: 13px !important;
    margin: 0 !important;
}

/* ═══════════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 900px) {
    .notif-page .main-wrap {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding-top: 64px !important;
    }
    .notif-page .main-wrap > .main-content {
        padding: 14px !important;
    }
    .notif-page .glass-card {
        padding: 18px !important;
        border-radius: 16px !important;
    }
    .notif-hero {
        flex-direction: column !important;
        align-items: flex-start !important;
        padding: 20px !important;
    }
    .notif-hero h1 { font-size: 20px !important; }
    .hero-actions { width: 100% !important; }
    .btn-hero-primary { width: 100% !important; justify-content: center !important; }
    .kpi-strip { grid-template-columns: repeat(2, 1fr) !important; }
    .kpi-value { font-size: 18px !important; }
    .notif-row { flex-direction: column !important; }
    .notif-check { align-self: flex-end !important; }
}
</style>