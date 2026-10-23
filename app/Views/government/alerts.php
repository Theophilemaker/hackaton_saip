<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card alerts-hero">
            <div>
                <h1>🚨 Alert Management Center</h1>
                <p>Publish, monitor, and dismiss government alerts across Rwanda.</p>
            </div>
            <a href="<?= BASE_URL ?>/index.php?url=government/publishAlert" class="btn-primary publish-cta">
                + Publish Alert
            </a>
        </section>

        <!-- Success / Error flash -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="glass-card flash-success" id="flashMessage">
                <span class="flash-icon">✅</span>
                <div>
                    <strong>Success!</strong>
                    <p><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
                </div>
                <button onclick="this.parentElement.remove()" class="flash-close">✕</button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="glass-card flash-error">
                <span class="flash-icon">⚠️</span>
                <div>
                    <strong>Error</strong>
                    <p><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
                </div>
                <button onclick="this.parentElement.remove()" class="flash-close">✕</button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Stats row -->
        <?php
        $total    = count($data['alerts'] ?? []);
        $critical = 0; $high = 0; $medium = 0; $low = 0;
        foreach (($data['alerts'] ?? []) as $a) {
            $s = $a['severity'] ?? 'medium';
            if ($s === 'critical') $critical++;
            elseif ($s === 'high') $high++;
            elseif ($s === 'low')  $low++;
            else                   $medium++;
        }
        ?>
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background:linear-gradient(135deg,#EF4444,#DC2626)">🚨</div>
                <div>
                    <p class="kpi-label">Total Active</p>
                    <h3 class="kpi-value"><?= $total ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background:linear-gradient(135deg,#DC2626,#991B1B)">🔥</div>
                <div>
                    <p class="kpi-label">Critical</p>
                    <h3 class="kpi-value"><?= $critical ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background:linear-gradient(135deg,#F97316,#EA580C)">⚠️</div>
                <div>
                    <p class="kpi-label">High</p>
                    <h3 class="kpi-value"><?= $high ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background:linear-gradient(135deg,#F59E0B,#D97706)">🟡</div>
                <div>
                    <p class="kpi-label">Medium</p>
                    <h3 class="kpi-value"><?= $medium ?></h3>
                </div>
            </div>
        </section>

        <!-- Alerts list -->
        <section class="glass-card alerts-list-card">
            <div class="card-header">
                <h3>📋 Active Alerts</h3>
                <span class="badge green"><?= $total ?> active</span>
            </div>

            <?php if (empty($data['alerts'])): ?>
                <div class="empty-state-card">
                    <span style="font-size:48px;">✅</span>
                    <h3>No active alerts</h3>
                    <p>All clear. Publish one if there's an emergency.</p>
                    <a href="<?= BASE_URL ?>/index.php?url=government/publishAlert"
                       class="btn-primary" style="display:inline-block; padding:12px 28px; width:auto;">
                        + Publish Alert
                    </a>
                </div>
            <?php else: ?>
                <div class="alerts-grid">
                    <?php foreach ($data['alerts'] as $a): ?>
                        <?php $sev = $a['severity'] ?? 'medium'; ?>
                        <div class="alert-card alert-<?= htmlspecialchars($sev) ?>">
                            <div class="alert-card-header">
                                <span class="severity-badge sev-<?= htmlspecialchars($sev) ?>">
                                    <?= strtoupper(htmlspecialchars($sev)) ?>
                                </span>
                                <span class="alert-type">
                                    <?= htmlspecialchars(str_replace('_', ' ', $a['alert_type'] ?? 'alert')) ?>
                                </span>
                                <form method="POST"
                                      action="<?= BASE_URL ?>/index.php?url=government/dismissAlert/<?= (int)$a['id'] ?>"
                                      class="dismiss-form"
                                      onsubmit="return confirm('Dismiss this alert?');">
                                    <?= \App\Core\Security::csrfField() ?>
                                    <button type="submit" class="dismiss-btn" title="Dismiss">✕</button>
                                </form>
                            </div>

                            <h4><?= htmlspecialchars($a['title'] ?? '') ?></h4>
                            <p><?= htmlspecialchars(mb_substr($a['body'] ?? '', 0, 200)) ?><?= mb_strlen($a['body'] ?? '') > 200 ? '…' : '' ?></p>

                            <?php if (!empty($a['action_recommended'])): ?>
                                <div class="alert-action">
                                    <strong>Action:</strong> <?= htmlspecialchars($a['action_recommended']) ?>
                                </div>
                            <?php endif; ?>

                            <div class="alert-footer">
                                <span>📍 <?= htmlspecialchars($a['district_name'] ?? 'National') ?></span>
                                <span>📅 <?= date('M d, H:i', strtotime($a['created_at'] ?? 'now')) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<style>
.alerts-hero {
    padding: 25px 30px !important;
    max-width: none;
    background: linear-gradient(135deg, rgba(239,68,68,0.15), rgba(245,158,11,0.08));
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.alerts-hero h1 { margin: 0 0 4px; font-size: 24px; }
.alerts-hero p { color: var(--text-sub); margin: 0; font-size: 13px; }
.publish-cta { width: auto; padding: 12px 24px; }

/* Flash messages */
.flash-success, .flash-error {
    padding: 16px 20px !important;
    max-width: none;
    display: flex;
    gap: 14px;
    align-items: center;
    animation: flashIn 0.4s ease-out;
}
.flash-success {
    background: rgba(34,197,94,0.12);
    border: 1px solid rgba(34,197,94,0.35);
    border-left: 4px solid #22C55E;
}
.flash-error {
    background: rgba(239,68,68,0.12);
    border: 1px solid rgba(239,68,68,0.35);
    border-left: 4px solid #EF4444;
}
.flash-icon { font-size: 24px; flex-shrink: 0; }
.flash-success strong { color: #86EFAC; display: block; font-size: 14px; }
.flash-error strong { color: #FCA5A5; display: block; font-size: 14px; }
.flash-success p, .flash-error p { margin: 4px 0 0; font-size: 13px; color: var(--text-sub); }
.flash-close {
    margin-left: auto;
    background: transparent;
    border: none;
    color: var(--text-sub);
    font-size: 16px;
    cursor: pointer;
    padding: 4px 10px;
    border-radius: 8px;
}
.flash-close:hover { background: rgba(255,255,255,0.08); color: white; }

@keyframes flashIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Alerts grid */
.alerts-list-card { padding: 22px !important; max-width: none; }
.alerts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 16px;
    margin-top: 12px;
}
.alert-card {
    padding: 18px;
    background: rgba(255,255,255,0.03);
    border-radius: 14px;
    border-left: 4px solid;
    transition: 250ms;
}
.alert-card:hover { transform: translateY(-3px); background: rgba(255,255,255,0.05); }
.alert-card.alert-low { border-left-color: #22C55E; }
.alert-card.alert-medium { border-left-color: #F59E0B; }
.alert-card.alert-high { border-left-color: #F97316; }
.alert-card.alert-critical {
    border-left-color: #DC2626;
    background: rgba(239,68,68,0.05);
}

.alert-card-header {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 12px;
}
.severity-badge {
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.5px;
}
.sev-low { background: rgba(34,197,94,0.2); color: #22C55E; }
.sev-medium { background: rgba(245,158,11,0.2); color: #F59E0B; }
.sev-high { background: rgba(249,115,22,0.2); color: #F97316; }
.sev-critical { background: #DC2626; color: white; }

.alert-type {
    font-size: 11px;
    color: var(--text-sub);
    text-transform: uppercase;
    letter-spacing: 1px;
}
.dismiss-form { margin-left: auto; }
.dismiss-btn {
    width: 28px; height: 28px;
    border-radius: 8px;
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text-sub);
    cursor: pointer;
    font-size: 13px;
    transition: 200ms;
}
.dismiss-btn:hover {
    background: rgba(239,68,68,0.15);
    border-color: #EF4444;
    color: #FCA5A5;
}

.alert-card h4 { margin: 0 0 8px; font-size: 15px; }
.alert-card p { color: var(--text-sub); font-size: 13px; margin: 0 0 12px; line-height: 1.5; }

.alert-action {
    padding: 10px 12px;
    background: rgba(132,204,22,0.08);
    border-left: 3px solid var(--accent);
    border-radius: 8px;
    font-size: 12px;
    color: var(--text-sub);
    margin-bottom: 12px;
}
.alert-action strong { color: var(--accent); }

.alert-footer {
    display: flex;
    justify-content: space-between;
    padding-top: 10px;
    border-top: 1px solid var(--border);
    color: var(--text-sub);
    font-size: 11px;
}

.empty-state-card {
    text-align: center;
    padding: 60px 20px;
}
.empty-state-card h3 { margin: 14px 0 8px; font-size: 18px; }
.empty-state-card p { color: var(--text-sub); margin: 0 0 20px; font-size: 13px; }

@media (max-width: 900px) {
    .alerts-grid { grid-template-columns: 1fr; }
    .alerts-hero { flex-direction: column; align-items: flex-start; }
}

/* Auto-hide flash after 4 seconds */
</style>

<script>
// Auto-hide success message after 4 seconds
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.getElementById('flashMessage');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 400ms, transform 400ms';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 400);
        }, 4000);
    }
});
</script>