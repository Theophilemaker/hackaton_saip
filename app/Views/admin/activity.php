<?php
// Pagination URLs
$baseUrl = BASE_URL . '/admin/activity?';
if (!empty($data['filter_action'])) $baseUrl .= 'action=' . urlencode($data['filter_action']) . '&';

$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="dashboard-layout admin-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card hero-card">
            <div class="hero-content">
                <span class="hero-tag">Audit Trail</span>
                <h1>📋 Activity Log</h1>
                <p>Complete audit trail of all actions performed on the platform.</p>
            </div>
            <div class="hero-actions">
                <button type="button" class="btn-hero-secondary" onclick="openClearModal()">🧹 Clear Old Logs</button>
                <a href="<?= BASE_URL ?>/admin" class="btn-hero-secondary">← Back</a>
            </div>
        </section>

        <!-- FILTERS -->
        <section class="glass-card">
            <div class="card-header">
                <div>
                    <h3>🔍 Filter Activity</h3>
                    <p class="card-subtitle"><?= number_format($data['total']) ?> total log entries</p>
                </div>
            </div>

            <form method="GET" class="filter-row">
                <select name="action" class="filter-select">
                    <option value="">All Actions</option>
                    <?php foreach ($data['actions'] as $a): ?>
                        <option value="<?= e($a['action']) ?>" <?= $data['filter_action'] === $a['action'] ? 'selected' : '' ?>>
                            <?= e(str_replace('_', ' ', ucfirst($a['action']))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary-sm">🔍 Filter</button>
                <?php if (!empty($data['filter_action'])): ?>
                    <a href="<?= BASE_URL ?>/admin/activity" class="btn-secondary-sm">✕ Clear</a>
                <?php endif; ?>
            </form>
        </section>

        <!-- LOGS TABLE -->
        <section class="glass-card">
            <div class="card-header">
                <div>
                    <h3>📜 Log Entries</h3>
                    <p class="card-subtitle">Page <?= $data['page'] ?> of <?= $data['total_pages'] ?></p>
                </div>
            </div>

            <?php if (empty($data['logs'])): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>No Activity Logs</h3>
                    <p>No actions recorded for this filter.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th>Date &amp; Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['logs'] as $log):
                                $action = $log['action'] ?? '';
                                $badgeClass = 'info';
                                if (strpos($action, 'delete') !== false) $badgeClass = 'red';
                                elseif (strpos($action, 'create') !== false || strpos($action, 'add') !== false) $badgeClass = 'green';
                                elseif (strpos($action, 'update') !== false || strpos($action, 'edit') !== false) $badgeClass = 'blue';
                                elseif (strpos($action, 'login') !== false) $badgeClass = 'purple';
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= e(trim(($log['first_name'] ?? 'System') . ' ' . ($log['last_name'] ?? ''))) ?></strong>
                                    </td>
                                    <td>
                                        <span class="action-badge <?= $badgeClass ?>">
                                            <?= e(str_replace('_', ' ', $action)) ?>
                                        </span>
                                    </td>
                                    <td class="desc-cell"><?= e(mb_strimwidth($log['description'] ?? '', 0, 80, '...')) ?></td>
                                    <td><code class="ip-cell"><?= e($log['ip_address'] ?? '—') ?></code></td>
                                    <td class="date-cell"><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <?php if ($data['total_pages'] > 1): ?>
                    <div class="pagination">
                        <?php if ($data['page'] > 1): ?>
                            <a href="<?= $baseUrl ?>page=<?= $data['page'] - 1 ?>" class="page-btn">← Previous</a>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $data['page'] - 2);
                        $end = min($data['total_pages'], $data['page'] + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <a href="<?= $baseUrl ?>page=<?= $i ?>"
                               class="page-btn <?= $i === $data['page'] ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($data['page'] < $data['total_pages']): ?>
                            <a href="<?= $baseUrl ?>page=<?= $data['page'] + 1 ?>" class="page-btn">Next →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
</div>

<!-- CLEAR MODAL — uses .open class instead of inline style, no !important on display -->
<div class="modal-overlay" id="clearModal" hidden>
    <div class="modal" style="max-width: 480px;">
        <div class="modal-header">
            <h3>🧹 Clear Activity Logs</h3>
            <button type="button" class="modal-close" onclick="closeClearModal()" aria-label="Close">✕</button>
        </div>
        <form id="clearForm" onsubmit="event.preventDefault(); confirmClear();">
            <div class="form-group">
                <label>Delete logs older than:</label>
                <select id="clearDays">
                    <option value="7">7 days</option>
                    <option value="30" selected>30 days</option>
                    <option value="90">90 days</option>
                    <option value="180">6 months</option>
                    <option value="365">1 year</option>
                </select>
            </div>
            <div class="warning-box">⚠️ This action cannot be undone. All matching log entries will be permanently deleted.</div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeClearModal()">Cancel</button>
                <button type="submit" class="btn-danger" id="clearBtn">🗑️ Clear Logs</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    'use strict';

    var modal = document.getElementById('clearModal');
    var csrf  = <?= json_encode($csrf) ?>;

    window.openClearModal = function () {
        modal.hidden = false;
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
        /* Focus the confirm button so Esc/keyboard works nicely */
        setTimeout(function () {
            var b = document.getElementById('clearBtn');
            if (b) b.focus();
        }, 50);
    };

    window.closeClearModal = function () {
        modal.classList.remove('open');
        modal.hidden = true;
        document.body.style.overflow = '';
    };

    /* Close on Escape */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) closeClearModal();
    });

    /* Close on backdrop click */
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeClearModal();
    });

    window.confirmClear = async function () {
        var days = document.getElementById('clearDays').value;
        if (!confirm('Delete all logs older than ' + days + ' days? This cannot be undone.')) return;

        var btn      = document.getElementById('clearBtn');
        var original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ Clearing…';

        /* Global loading overlay if available */
        var stopOverlay = function () {};
        if (window.SAIP && window.SAIP.loading) {
            window.SAIP.loading.start('Clearing activity logs…');
            stopOverlay = window.SAIP.loading.stop;
        }

        try {
            var fd = new FormData();
            fd.append('days', days);
            fd.append('_csrf', csrf);
            fd.append('csrf', csrf);

            var res = await fetch('<?= BASE_URL ?>/admin/clearActivity', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            var text = await res.text();
            var data;
            try { data = JSON.parse(text); }
            catch (pe) { throw new Error('Server returned non-JSON. Preview: ' + text.slice(0, 120)); }

            if (data.success) {
                showToast(data.message || 'Activity logs cleared.', 'success');
                closeClearModal();
                setTimeout(function () { location.reload(); }, 900);
            } else {
                showToast(data.error || 'Could not clear logs.', 'error');
            }
        } catch (err) {
            showToast('Error: ' + err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = original;
            stopOverlay();
        }
    };

    window.showToast = function (msg, type) {
        /* Reuse the global toast if defined by dashboard-loading.js */
        if (window.SAIP && window.SAIP.toast) { window.SAIP.toast(msg, type); return; }
        var t = document.createElement('div');
        t.className = 'toast ' + (type || 'info');
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { t.classList.add('show'); }, 10);
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { t.remove(); }, 300);
        }, 2600);
    };
})();
</script>

<style>
/* ═══════════════════════════════════════════════════════════
   LAYOUT
   ═══════════════════════════════════════════════════════════ */
.admin-page.dashboard-layout {
    display: flex !important;
    width: 100vw !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
}
.admin-page .main-content {
    flex: 1 !important;
    width: calc(100% - 250px) !important;
    max-width: 100% !important;
    padding: 28px 32px !important;
    box-sizing: border-box !important;
    overflow-x: hidden !important;
}
}
.admin-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 28px !important;
    margin-bottom: 24px !important;
}

/* ── Hero card ── */
.hero-card {
    background: linear-gradient(135deg, rgba(6, 182, 212, 0.12), rgba(56, 189, 248, 0.08)) !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 24px !important;
    flex-wrap: wrap !important;
}
.hero-content { flex: 1 !important; min-width: 280px !important; }
.hero-tag {
    display: inline-block !important;
    padding: 5px 14px !important;
    background: rgba(6, 182, 212, 0.15) !important;
    color: #06b6d4 !important;
    border: 1px solid rgba(6, 182, 212, 0.3) !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    margin-bottom: 12px !important;
}
.hero-card h1 { font-size: 28px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.hero-card p  { color: #94a3b8 !important; font-size: 14px !important; margin: 0 !important; }
.hero-actions { display: flex !important; gap: 10px !important; flex-wrap: wrap !important; }
.btn-hero-secondary {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 13px 24px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #f1f5f9 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    font-size: 13.5px !important;
    cursor: pointer !important;
    transition: all 0.2s !important;
    font-family: inherit !important;
}
.btn-hero-secondary:hover { background: rgba(255, 255, 255, 0.1) !important; transform: translateY(-1px) !important; }

/* ── Card header ── */
.card-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 20px !important;
    padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
}
.card-header h3 { font-size: 18px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 !important; }
.card-subtitle { font-size: 12.5px !important; color: #94a3b8 !important; margin: 4px 0 0 0 !important; }

/* ── Filters ── */
.filter-row { display: flex !important; gap: 12px !important; flex-wrap: wrap !important; align-items: center !important; }
.filter-select {
    padding: 12px 40px 12px 16px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important;
    font-size: 13.5px !important;
    min-width: 220px !important;
    cursor: pointer !important;
    appearance: none !important;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 16px !important;
    font-family: inherit !important;
}
.filter-select option { background: #1e293b !important; }

.btn-primary-sm {
    padding: 12px 24px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3) !important;
}
.btn-primary-sm:hover { transform: translateY(-1px) !important; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.5) !important; }

.btn-secondary-sm {
    padding: 12px 20px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: all 0.2s !important;
}
.btn-secondary-sm:hover { background: rgba(255, 255, 255, 0.1) !important; }

/* ── Table ── */
.table-wrapper {
    overflow-x: auto !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: rgba(0, 0, 0, 0.15) !important;
}
.data-table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 13px !important;
    min-width: 0 !important;
    table-layout: fixed !important;
}
.data-table thead th {
    background: #1e293b !important;
    color: #94a3b8 !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    font-size: 11px !important;
    padding: 14px 16px !important;
    text-align: left !important;
    border-bottom: 2px solid rgba(56, 189, 248, 0.25) !important;
    white-space: nowrap !important;
}
.data-table tbody td {
    padding: 13px 16px !important;
    color: #f1f5f9 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    vertical-align: middle !important;
}
.data-table tbody tr:hover td { background: rgba(56, 189, 248, 0.04) !important; }

.action-badge {
    display: inline-flex !important;
    padding: 4px 10px !important;
    border-radius: 6px !important;
    font-size: 10.5px !important;
    font-weight: 700 !important;
    text-transform: capitalize !important;
    letter-spacing: 0.3px !important;
    white-space: nowrap !important;
}
.action-badge.green  { background: rgba(52, 211, 153, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(52, 211, 153, 0.3) !important; }
.action-badge.red    { background: rgba(248, 113, 113, 0.15) !important; color: #f87171 !important; border: 1px solid rgba(248, 113, 113, 0.3) !important; }
.action-badge.blue   { background: rgba(56, 189, 248, 0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(56, 189, 248, 0.3) !important; }
.action-badge.purple { background: rgba(167, 139, 250, 0.15) !important; color: #a78bfa !important; border: 1px solid rgba(167, 139, 250, 0.3) !important; }
.action-badge.info   { background: rgba(148, 163, 184, 0.15) !important; color: #94a3b8 !important; border: 1px solid rgba(148, 163, 184, 0.3) !important; }

.desc-cell { color: #cbd5e1 !important; }
.ip-cell { background: rgba(0, 0, 0, 0.3) !important; padding: 3px 9px !important; border-radius: 5px !important; color: #38bdf8 !important; font-family: 'Courier New', monospace !important; font-size: 11.5px !important; }
.date-cell { color: #94a3b8 !important; font-size: 12.5px !important; white-space: nowrap !important; }

/* ── Pagination ── */
.pagination { display: flex !important; justify-content: center !important; gap: 6px !important; margin-top: 20px !important; flex-wrap: wrap !important; }
.page-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 38px !important;
    height: 38px !important;
    padding: 0 12px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 8px !important;
    text-decoration: none !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    transition: all 0.2s !important;
}
.page-btn:hover { background: rgba(56, 189, 248, 0.15) !important; color: #38bdf8 !important; }
.page-btn.active {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important;
    border-color: transparent !important;
}

/* ── Empty state ── */
.empty-state { text-align: center !important; padding: 60px 24px !important; }
.empty-icon { font-size: 56px !important; margin-bottom: 12px !important; opacity: 0.6 !important; }
.empty-state h3 { font-size: 18px !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }

/* ═══════════════════════════════════════════════════════════
   MODAL — fixed
   ═══════════════════════════════════════════════════════════ */
.modal-overlay {
    position: fixed !important;
    inset: 0 !important;
    background: rgba(0, 0, 0, 0.75) !important;
    backdrop-filter: blur(6px) !important;
    -webkit-backdrop-filter: blur(6px) !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 9999 !important;
    padding: 20px !important;
    /* ⚠️ NO `display` here — that's what broke it.
       Visibility is controlled by the `hidden` attribute + `.open` class below. */
}
.modal-overlay.open {
    display: flex !important;
}
/* `hidden` attribute always wins, no matter what */
.modal-overlay[hidden] {
    display: none !important;
}

.modal {
    background: #1e293b !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 20px !important;
    padding: 28px !important;
    width: 100% !important;
    box-shadow: 0 32px 80px rgba(0, 0, 0, 0.6) !important;
    animation: modalIn 0.28s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(0.94) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 20px !important;
    padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.modal-header h3 { font-size: 18px !important; color: #f1f5f9 !important; margin: 0 !important; font-weight: 800 !important; }
.modal-close {
    width: 34px !important;
    height: 34px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 8px !important;
    color: #cbd5e1 !important;
    cursor: pointer !important;
    font-size: 14px !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
}
.modal-close:hover {
    background: rgba(239, 68, 68, 0.15) !important;
    border-color: rgba(239, 68, 68, 0.4) !important;
    color: #f87171 !important;
}

.form-group { margin-bottom: 16px !important; }
.form-group label {
    display: block !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
    margin-bottom: 6px !important;
}
.form-group select {
    width: 100% !important;
    padding: 12px 14px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important;
    font-size: 14px !important;
    outline: none !important;
    font-family: inherit !important;
    cursor: pointer !important;
}
.form-group select:focus { border-color: #10b981 !important; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important; }
.form-group select option { background: #1e293b !important; color: #f1f5f9 !important; }

.warning-box {
    padding: 12px 16px !important;
    background: rgba(239, 68, 68, 0.1) !important;
    border-left: 3px solid #ef4444 !important;
    border-radius: 8px !important;
    font-size: 12.5px !important;
    color: #fca5a5 !important;
    line-height: 1.5 !important;
    margin-bottom: 16px !important;
}

.modal-actions {
    display: flex !important;
    justify-content: flex-end !important;
    gap: 10px !important;
    padding-top: 16px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.btn-secondary {
    padding: 12px 22px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    font-size: 13.5px !important;
    transition: all 0.2s !important;
}
.btn-secondary:hover { background: rgba(255, 255, 255, 0.1) !important; color: #f1f5f9 !important; }

.btn-danger {
    padding: 12px 22px !important;
    background: linear-gradient(135deg, #ef4444, #dc2626) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    font-size: 13.5px !important;
    transition: all 0.2s !important;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35) !important;
}
.btn-danger:hover:not(:disabled) {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(239, 68, 68, 0.55) !important;
}
.btn-danger:disabled { opacity: 0.6 !important; cursor: not-allowed !important; transform: none !important; }

/* ── Toast ── */
.toast {
    position: fixed !important;
    bottom: 32px !important;
    right: 32px !important;
    padding: 14px 22px !important;
    border-radius: 12px !important;
    font-size: 13.5px !important;
    font-weight: 600 !important;
    color: #fff !important;
    transform: translateY(20px) !important;
    opacity: 0 !important;
    transition: all 0.3s !important;
    z-index: 99999 !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5) !important;
}
.toast.show { transform: translateY(0) !important; opacity: 1 !important; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.toast.error { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }
.toast.info { background: linear-gradient(135deg, #38bdf8, #0284c7) !important; }

/* ── Responsive ── */
@media (max-width: 768px) {
    .admin-page .main-content { padding: 16px !important; }
    .filter-row { flex-direction: column !important; }
    .filter-select, .btn-primary-sm, .btn-secondary-sm { width: 100% !important; }
    .toast { left: 16px !important; right: 16px !important; bottom: 16px !important; }
    .modal-actions { flex-direction: column !important; }
    .modal-actions button { width: 100% !important; }
}
    /* ═══════════════════════════════════════════════════════════
   FIX: Fill the screen + table fits without horizontal scroll
   ═══════════════════════════════════════════════════════════ */

/* 1. Force the whole layout to fill the viewport */
.admin-page.dashboard-layout,
.admin-page.dashboard-layout .main-content {
    width: 100% !important;
    max-width: 100% !important;
}
.admin-page .main-content {
    width: calc(100% - 250px) !important;
    padding: 28px 32px !important;
    overflow-x: hidden !important;
}
.admin-page .main-content > section.glass-card {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

/* 2. Remove the forced min-width so the table can shrink */
.table-wrapper {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: auto !important;
}
.data-table {
    width: 100% !important;
    min-width: 0 !important;         /* ← this was the culprit */
    table-layout: fixed !important;  /* equal column widths that adapt */
    border-collapse: collapse !important;
}

/* 3. Assign balanced column widths */
.data-table thead th:nth-child(1) { width: 16%; }  /* User        */
.data-table thead th:nth-child(2) { width: 16%; }  /* Action      */
.data-table thead th:nth-child(3) { width: 34%; }  /* Description */
.data-table thead th:nth-child(4) { width: 14%; }  /* IP Address  */
.data-table thead th:nth-child(5) { width: 20%; }  /* Date & Time */

/* 4. Wrap long content instead of pushing the table wider */
.data-table td {
    padding: 12px 14px !important;
    word-break: break-word !important;
    overflow-wrap: anywhere !important;
    vertical-align: top !important;
}
.data-table thead th {
    padding: 12px 14px !important;
}
.desc-cell {
    color: #cbd5e1 !important;
    font-size: 12.5px !important;
    line-height: 1.45 !important;
}
.ip-cell {
    font-size: 11px !important;
    padding: 3px 8px !important;
    display: inline-block !important;
}
.date-cell {
    font-size: 12px !important;
    white-space: normal !important;
}

/* 5. Tame the action badge so it never widens a column */
.action-badge {
    display: inline-block !important;
    max-width: 100% !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    font-size: 10.5px !important;
    padding: 4px 9px !important;
}

/* 6. Compact hero card on narrow screens */
.hero-card { padding: 22px 26px !important; }
.hero-card h1 { font-size: 24px !important; }

/* 7. Mobile — sidebar collapses, give table full width */
@media (max-width: 992px) {
    .admin-page .main-content {
        width: 100% !important;
        padding: 20px !important;
    }
}
@media (max-width: 640px) {
    .data-table thead th,
    .data-table td {
        padding: 10px 8px !important;
        font-size: 11.5px !important;
    }
    .data-table thead th:nth-child(4),
    .data-table tbody td:nth-child(4) {
        display: none !important;   /* hide IP column on mobile */
    }
    .data-table thead th:nth-child(1) { width: 22%; }
    .data-table thead th:nth-child(2) { width: 24%; }
    .data-table thead th:nth-child(3) { width: 34%; }
    .data-table thead th:nth-child(5) { width: 20%; }
}
</style>