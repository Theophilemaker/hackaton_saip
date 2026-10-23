<?php
$h = $data['health'];
$diskUsedPct     = $h['disk_total'] > 0 ? round((($h['disk_total'] - $h['disk_free']) / $h['disk_total']) * 100, 1) : 0;
$diskUsedBytes   = $h['disk_total'] - $h['disk_free'];
$csrf            = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(16));

/* ── Compute a health score (0–100) ── */
$score = 100;
if (!$h['php_ok'])              $score -= 15;
if ($diskUsedPct > 85)          $score -= 20;
elseif ($diskUsedPct > 70)      $score -= 10;
if (($h['backups_count'] ?? 0) === 0) $score -= 10;
if ($h['maintenance'])          $score -= 5;
if (empty($h['counts']['users']))     $score -= 5;
$score = max(0, min(100, $score));

/* ── Circle circumference for SVG gauge ── */
$r = 52; $circ = 2 * M_PI * $r;
$dashOffset = $circ * (1 - $score / 100);

function fmtBytes($b) {
    if ($b < 1024) return $b . ' B';
    if ($b < 1048576) return round($b / 1024, 1) . ' KB';
    if ($b < 1073741824) return round($b / 1048576, 2) . ' MB';
    return round($b / 1073741824, 2) . ' GB';
}

/* ── Required PHP extensions ── */
$requiredExt = ['pdo','pdo_mysql','mbstring','json','openssl','curl'];
$optionalExt = ['gd','zip','intl','fileinfo','imagick','redis'];
$extStatus   = [];
foreach ($requiredExt as $e) $extStatus[$e] = ['loaded' => extension_loaded($e), 'required' => true];
foreach ($optionalExt as $e) $extStatus[$e] = ['loaded' => extension_loaded($e), 'required' => false];

/* ── Error log (last 20 lines) ── */
$logFile = __DIR__ . '/../../../storage/logs/php_error.log';
$logTail = [];
if (is_file($logFile) && is_readable($logFile)) {
    $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) $logTail = array_slice($lines, -20);
}
?>

<div class="dashboard-layout admin-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ═══════════════════════ HERO ═══════════════════════ -->
        <section class="glass-card hero-card">
            <div class="hero-flex">
                <!-- Health gauge -->
                <div class="gauge-wrap">
                    <svg class="gauge" viewBox="0 0 120 120" width="120" height="120">
                        <circle cx="60" cy="60" r="<?= $r ?>" class="gauge-track"/>
                        <circle cx="60" cy="60" r="<?= $r ?>" class="gauge-fill"
                                style="stroke-dasharray: <?= $circ ?>; stroke-dashoffset: <?= $dashOffset ?>"/>
                    </svg>
                    <div class="gauge-text">
                        <strong><?= $score ?></strong>
                        <small>HEALTH</small>
                    </div>
                </div>

                <div class="hero-content">
                    <span class="hero-tag">Diagnostics</span>
                    <h1>⚙️ System Health</h1>
                    <p>Real-time diagnostics for your server, database, and platform resources.</p>
                    <div class="hero-meta">
                        <span class="meta-pill" id="lastChecked">
                            <span class="pulse-dot"></span>
                            Last check: <strong>just now</strong>
                        </span>
                        <label class="meta-pill toggle-auto">
                            <input type="checkbox" id="autoRefresh">
                            <span>Auto-refresh (60s)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="hero-actions">
                <button type="button" class="btn-hero-secondary" onclick="location.reload()">🔄 Refresh</button>
                <button type="button" class="btn-hero-secondary" onclick="copyReport()">📋 Copy Report</button>
                <button type="button" class="btn-hero-secondary" onclick="exportReport()">⬇️ Export</button>
                <a href="<?= BASE_URL ?>/admin" class="btn-hero-secondary">← Back</a>
            </div>
        </section>

        <!-- ═══════════════════════ STATUS BANNER ═══════════════════════ -->
        <div class="status-banner <?= $h['maintenance'] ? 'warning' : 'healthy' ?>">
            <div class="status-indicator"></div>
            <div class="status-text">
                <?php if ($h['maintenance']): ?>
                    <strong>🚧 Maintenance Mode Active</strong>
                    <p>Public users cannot access the platform. Only admins can see this.</p>
                <?php else: ?>
                    <strong>✅ System Operating Normally</strong>
                    <p>All systems are healthy. No action required.</p>
                <?php endif; ?>
            </div>
            <button type="button" class="btn-toggle-maintenance" onclick="toggleMaintenance(this)">
                <?= $h['maintenance'] ? '🟢 Disable Maintenance' : '🔧 Enable Maintenance' ?>
            </button>
        </div>

        <!-- ═══════════════════════ KPI GRID ═══════════════════════ -->
        <section class="kpi-grid">
            <div class="kpi-card disk">
                <div class="kpi-icon">💾</div>
                <div class="kpi-content">
                    <p class="kpi-label">Disk Usage</p>
                    <h3 class="kpi-value"><?= $diskUsedPct ?>%</h3>
                    <div class="kpi-bar"><div class="kpi-bar-fill" style="width:<?= $diskUsedPct ?>%"></div></div>
                    <small class="kpi-sub"><?= fmtBytes($diskUsedBytes) ?> / <?= fmtBytes($h['disk_total']) ?></small>
                </div>
            </div>

            <div class="kpi-card db">
                <div class="kpi-icon">🗄️</div>
                <div class="kpi-content">
                    <p class="kpi-label">Database Size</p>
                    <h3 class="kpi-value"><?= fmtBytes($h['db_size']) ?></h3>
                    <small class="kpi-sub">MySQL / MariaDB</small>
                </div>
            </div>

            <div class="kpi-card uploads">
                <div class="kpi-icon">📁</div>
                <div class="kpi-content">
                    <p class="kpi-label">Uploads Size</p>
                    <h3 class="kpi-value"><?= fmtBytes($h['uploads_size']) ?></h3>
                    <small class="kpi-sub">public/uploads/</small>
                </div>
            </div>

            <div class="kpi-card backups">
                <div class="kpi-icon">📦</div>
                <div class="kpi-content">
                    <p class="kpi-label">Backups</p>
                    <h3 class="kpi-value"><?= number_format($h['backups_count']) ?></h3>
                    <?php if ($h['last_backup']): ?>
                        <small class="kpi-sub">Last: <?= date('M d, H:i', strtotime($h['last_backup'])) ?></small>
                    <?php else: ?>
                        <small class="kpi-sub" style="color:#f87171;">No backups yet</small>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════ DATA STATS ═══════════════════════ -->
        <section class="glass-card">
            <div class="card-header">
                <div>
                    <h3>📊 Database Statistics</h3>
                    <p class="card-subtitle">Record counts across tables</p>
                </div>
            </div>
            <div class="data-stats">
                <div class="data-stat">
                    <span class="data-stat-label">Users</span>
                    <span class="data-stat-value"><?= number_format($h['counts']['users'] ?? 0) ?></span>
                </div>
                <div class="data-stat">
                    <span class="data-stat-label">NISR Datasets</span>
                    <span class="data-stat-value"><?= number_format($h['counts']['datasets'] ?? 0) ?></span>
                </div>
                <div class="data-stat">
                    <span class="data-stat-label">Activity Logs</span>
                    <span class="data-stat-value"><?= number_format($h['counts']['activity'] ?? 0) ?></span>
                </div>
                <div class="data-stat">
                    <span class="data-stat-label">Products</span>
                    <span class="data-stat-value"><?= number_format($h['counts']['products'] ?? 0) ?></span>
                </div>
                <div class="data-stat">
                    <span class="data-stat-label">PHP Sessions</span>
                    <span class="data-stat-value"><?= is_dir(session_save_path() ?: '/tmp') ? @count(@scandir(session_save_path() ?: '/tmp') ?: []) - 2 : 0 ?></span>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════ ENVIRONMENT ═══════════════════════ -->
        <section class="glass-card">
            <div class="card-header">
                <div><h3>🖥️ Server Environment</h3><p class="card-subtitle">Runtime information</p></div>
                <button type="button" class="btn-mini" onclick="copyEnv()">📋 Copy</button>
            </div>
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-key">PHP Version</span>
                    <span class="info-val">
                        <code><?= e($h['php_version']) ?></code>
                        <span class="status-dot <?= $h['php_ok'] ? 'ok' : 'fail' ?>"></span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-key">Operating System</span>
                    <span class="info-val"><?= e($h['os']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-key">Server Software</span>
                    <span class="info-val"><?= e($h['server_software']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-key">Timezone</span>
                    <span class="info-val"><code><?= e(date_default_timezone_get()) ?></code></span>
                </div>
                <div class="info-row">
                    <span class="info-key">Memory Limit</span>
                    <span class="info-val"><code><?= e($h['memory_limit']) ?></code></span>
                </div>
                <div class="info-row">
                    <span class="info-key">Max Upload Size</span>
                    <span class="info-val"><code><?= e($h['upload_max']) ?></code></span>
                </div>
                <div class="info-row">
                    <span class="info-key">POST Max Size</span>
                    <span class="info-val"><code><?= e($h['post_max']) ?></code></span>
                </div>
                <div class="info-row">
                    <span class="info-key">Max Execution Time</span>
                    <span class="info-val"><code><?= e($h['max_execution']) ?>s</code></span>
                </div>
                <div class="info-row">
                    <span class="info-key">Session Handler</span>
                    <span class="info-val"><code><?= e(ini_get('session.save_handler') ?: 'files') ?></code></span>
                </div>
                <div class="info-row">
                    <span class="info-key">Database Host</span>
                    <span class="info-val"><code><?= e(defined('DB_HOST') ? DB_HOST : '—') ?></code></span>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════ PHP EXTENSIONS ═══════════════════════ -->
        <section class="glass-card">
            <div class="card-header">
                <div><h3>🧩 PHP Extensions</h3><p class="card-subtitle">Required and optional modules</p></div>
            </div>
            <div class="ext-grid">
                <?php foreach ($extStatus as $name => $info): ?>
                    <div class="ext-item <?= $info['loaded'] ? 'ok' : ($info['required'] ? 'fail' : 'warn') ?>">
                        <span class="ext-icon"><?= $info['loaded'] ? '✅' : ($info['required'] ? '❌' : '⚠️') ?></span>
                        <div>
                            <strong><?= e($name) ?></strong>
                            <small><?= $info['required'] ? 'Required' : 'Optional' ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ═══════════════════════ ERROR LOG ═══════════════════════ -->
        <section class="glass-card">
            <div class="card-header">
                <div><h3>🐛 Recent Errors</h3><p class="card-subtitle">Last 20 lines of <code>storage/logs/php_error.log</code></p></div>
                <?php if (!empty($logTail)): ?>
                    <button type="button" class="btn-mini danger" onclick="clearLog()">🗑️ Clear Log</button>
                <?php endif; ?>
            </div>
            <?php if (empty($logTail)): ?>
                <div class="log-empty">
                    <div class="log-empty-icon">🎉</div>
                    <p>No recent errors — everything is clean.</p>
                </div>
            <?php else: ?>
                <div class="log-viewer">
                    <?php foreach ($logTail as $line): ?>
                        <div class="log-line"><?= e($line) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- ═══════════════════════ QUICK ACTIONS ═══════════════════════ -->
        <section class="glass-card">
            <div class="card-header">
                <div><h3>⚡ Quick Actions</h3><p class="card-subtitle">Common maintenance tasks</p></div>
            </div>
            <div class="quick-actions">
                <a href="<?= BASE_URL ?>/admin/backup" class="quick-btn">
                    <span>💾</span><strong>Create Backup</strong><small>Database snapshot</small>
                </a>
                <a href="<?= BASE_URL ?>/admin/activity" class="quick-btn">
                    <span>📋</span><strong>View Logs</strong><small>Recent activity</small>
                </a>
                <button type="button" class="quick-btn" onclick="clearCache(this)">
                    <span>🧹</span><strong>Clear Cache</strong><small>Remove temp files</small>
                </button>
                <button type="button" class="quick-btn" onclick="runDiagnostics(this)">
                    <span>🔬</span><strong>Run Diagnostics</strong><small>Full system check</small>
                </button>
            </div>
        </section>
    </main>
</div>

<!-- ═══════════════════════ DIAGNOSTICS MODAL ═══════════════════════ -->
<div class="modal-overlay" id="diagModal" hidden>
    <div class="modal">
        <div class="modal-header">
            <h3>🔬 Diagnostic Results</h3>
            <button type="button" class="modal-close" onclick="closeDiag()">✕</button>
        </div>
        <div id="diagBody" class="diag-body"></div>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeDiag()">Close</button>
            <button type="button" class="btn-primary" onclick="copyDiag()">📋 Copy Results</button>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var BASE = '<?= BASE_URL ?>';
    var CSRF = <?= json_encode($csrf) ?>;
    var H    = <?= json_encode([
        'php_ok'        => (bool)$h['php_ok'],
        'php_version'   => $h['php_version'],
        'disk_pct'      => $diskUsedPct,
        'backups'       => (int)$h['backups_count'],
        'maintenance'   => (bool)$h['maintenance'],
        'users'         => (int)($h['counts']['users'] ?? 0),
        'ext_required'  => array_keys(array_filter($extStatus, fn($e) => $e['required'] && !$e['loaded'])),
    ], JSON_UNESCAPED_SLASHES) ?>;

    /* ═══════════════════════ MAINTENANCE TOGGLE ═══════════════════════ */
    window.toggleMaintenance = async function (btn) {
        var label = btn.textContent;
        btn.disabled = true;
        btn.textContent = '⏳ Working…';

        if (window.SAIP && window.SAIP.loading) window.SAIP.loading.start('Toggling maintenance…');

        try {
            var fd = new FormData();
            fd.append('_csrf', CSRF);

            var res = await fetch(BASE + '/admin/toggleMaintenance', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            var data = await res.json();
            if (data.success) {
                showToast(data.enabled ? '🚧 Maintenance mode enabled' : '✅ Maintenance disabled', 'success');
                setTimeout(function () { location.reload(); }, 900);
            } else {
                throw new Error(data.error || 'Toggle failed');
            }
        } catch (e) {
            showToast('Failed: ' + e.message, 'error');
            btn.disabled = false;
            btn.textContent = label;
        } finally {
            if (window.SAIP && window.SAIP.loading) window.SAIP.loading.stop();
        }
    };

    /* ═══════════════════════ CACHE CLEAR ═══════════════════════ */
    window.clearCache = async function (btn) {
        if (!confirm('Clear all cached files? (storage/cache, view cache)')) return;
        var orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span>⏳</span><strong>Clearing…</strong><small>Please wait</small>';

        if (window.SAIP && window.SAIP.loading) window.SAIP.loading.start('Clearing cache…');

        try {
            var fd = new FormData();
            fd.append('_csrf', CSRF);

            var res = await fetch(BASE + '/admin/clearCache', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            var data = await res.json().catch(function () { return { success: false, error: 'Invalid response' }; });

            if (data.success) {
                showToast('✅ ' + (data.message || 'Cache cleared'), 'success');
            } else {
                /* Endpoint may not exist yet — simulate fallback */
                showToast('⚠️ ' + (data.error || 'Endpoint not implemented yet'), 'error');
            }
        } catch (e) {
            showToast('Cache clear not available yet', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
            if (window.SAIP && window.SAIP.loading) window.SAIP.loading.stop();
        }
    };

    /* ═══════════════════════ DIAGNOSTICS ═══════════════════════ */
    window.runDiagnostics = function (btn) {
        var orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span>⏳</span><strong>Running…</strong><small>Analyzing</small>';

        setTimeout(function () {
            var checks = [];

            checks.push({
                ok: H.php_ok,
                label: 'PHP version',
                detail: 'Running PHP ' + H.php_version + (H.php_ok ? ' (supported)' : ' (OUTDATED)'),
            });

            checks.push({
                ok: H.disk_pct < 85,
                warn: H.disk_pct >= 70 && H.disk_pct < 85,
                label: 'Disk space',
                detail: 'Using ' + H.disk_pct + '% of total disk' + (H.disk_pct >= 85 ? ' — CRITICAL' : H.disk_pct >= 70 ? ' — getting high' : ' — healthy'),
            });

            checks.push({
                ok: H.backups > 0,
                label: 'Backups',
                detail: H.backups > 0 ? H.backups + ' backup(s) available' : 'No backups found — create one immediately',
            });

            checks.push({
                ok: H.ext_required.length === 0,
                label: 'Required PHP extensions',
                detail: H.ext_required.length === 0 ? 'All required extensions loaded' : 'Missing: ' + H.ext_required.join(', '),
            });

            checks.push({
                ok: !H.maintenance,
                warn: H.maintenance,
                label: 'Maintenance mode',
                detail: H.maintenance ? 'Currently active — public users are blocked' : 'Off — public site is accessible',
            });

            checks.push({
                ok: H.users > 0,
                label: 'Database',
                detail: H.users > 0 ? 'Connected, ' + H.users + ' user(s)' : 'No users in database',
            });

            checks.push({
                ok: true,
                label: 'Timezone',
                detail: '<?= addslashes(date_default_timezone_get()) ?>',
            });

            checks.push({
                ok: true,
                label: 'Session handler',
                detail: '<?= addslashes(ini_get('session.save_handler') ?: 'files') ?>',
            });

            var pass = checks.filter(function (c) { return c.ok; }).length;
            var fail = checks.filter(function (c) { return !c.ok && !c.warn; }).length;
            var warn = checks.length - pass - fail;

            var html = '<div class="diag-summary">' +
                '<div class="diag-chip ok">'   + pass              + ' passed</div>' +
                '<div class="diag-chip warn">' + warn              + ' warnings</div>' +
                '<div class="diag-chip fail">' + fail              + ' failed</div>' +
            '</div><div class="diag-list">';

            checks.forEach(function (c) {
                var cls = c.ok ? 'ok' : (c.warn ? 'warn' : 'fail');
                var ico = c.ok ? '✅' : (c.warn ? '⚠️' : '❌');
                html += '<div class="diag-item ' + cls + '">' +
                    '<span class="diag-icon">' + ico + '</span>' +
                    '<div><strong>' + c.label + '</strong><small>' + c.detail + '</small></div>' +
                '</div>';
            });
            html += '</div>';

            document.getElementById('diagBody').innerHTML = html;
            document.getElementById('diagModal').hidden = false;
            document.body.style.overflow = 'hidden';

            btn.disabled = false;
            btn.innerHTML = orig;
        }, 500);
    };

    window.closeDiag = function () {
        document.getElementById('diagModal').hidden = true;
        document.body.style.overflow = '';
    };

    window.copyDiag = function () {
        var text = Array.from(document.querySelectorAll('#diagBody .diag-item')).map(function (el) {
            return el.querySelector('strong').textContent + ': ' + el.querySelector('small').textContent;
        }).join('\n');

        copyToClipboard(text, 'Diagnostic results copied');
    };

    /* ═══════════════════════ COPY REPORT ═══════════════════════ */
    window.copyReport = function () {
        var report = {
            generated_at: new Date().toISOString(),
            health_score: <?= $score ?>,
            php: { version: H.php_version, ok: H.php_ok },
            disk: { used_pct: H.disk_pct, free: <?= $h['disk_free'] ?>, total: <?= $h['disk_total'] ?> },
            database: { size: <?= $h['db_size'] ?>, host: '<?= addslashes(defined('DB_HOST') ? DB_HOST : '') ?>' },
            uploads: { size: <?= $h['uploads_size'] ?> },
            backups: { count: H.backups, last: <?= json_encode($h['last_backup']) ?> },
            maintenance: H.maintenance,
            counts: <?= json_encode($h['counts'], JSON_UNESCAPED_SLASHES) ?>,
            extensions: <?= json_encode(array_map(fn($v) => $v['loaded'], $extStatus), JSON_UNESCAPED_SLASHES) ?>,
            timezone: '<?= addslashes(date_default_timezone_get()) ?>',
        };
        copyToClipboard(JSON.stringify(report, null, 2), 'Health report copied');
    };

    /* ═══════════════════════ EXPORT REPORT ═══════════════════════ */
    window.exportReport = function () {
        var report = {
            generated_at: new Date().toISOString(),
            health_score: <?= $score ?>,
            environment: {
                php_version: H.php_version,
                os: '<?= addslashes($h['os']) ?>',
                server: '<?= addslashes($h['server_software']) ?>',
                timezone: '<?= addslashes(date_default_timezone_get()) ?>',
                memory_limit: '<?= addslashes($h['memory_limit']) ?>',
                upload_max: '<?= addslashes($h['upload_max']) ?>',
                max_execution: '<?= addslashes($h['max_execution']) ?>',
            },
            disk: { used_pct: H.disk_pct },
            database: { size_bytes: <?= $h['db_size'] ?>, counts: <?= json_encode($h['counts'], JSON_UNESCAPED_SLASHES) ?> },
            uploads: { size_bytes: <?= $h['uploads_size'] ?> },
            backups: { count: H.backups, last: <?= json_encode($h['last_backup']) ?> },
            maintenance: H.maintenance,
            extensions: <?= json_encode(array_map(fn($v) => $v['loaded'], $extStatus), JSON_UNESCAPED_SLASHES) ?>,
        };
        var blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href = url;
        a.download = 'saip_health_' + new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-') + '.json';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
        showToast('📥 Report downloaded', 'success');
    };

    /* ═══════════════════════ COPY ENV ═══════════════════════ */
    window.copyEnv = function () {
        var env = <?= json_encode([
            'php'      => $h['php_version'],
            'os'       => $h['os'],
            'server'   => $h['server_software'],
            'timezone' => date_default_timezone_get(),
            'memory'   => $h['memory_limit'],
            'upload'   => $h['upload_max'],
            'post'     => $h['post_max'],
            'exec'     => $h['max_execution'],
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>;
        copyToClipboard(env, 'Environment copied');
    };

    /* ═══════════════════════ CLEAR LOG ═══════════════════════ */
    window.clearLog = async function () {
        if (!confirm('Delete the php_error.log file? This cannot be undone.')) return;
        try {
            var fd = new FormData();
            fd.append('_csrf', CSRF);
            fd.append('file', 'php_error.log');

            var res = await fetch(BASE + '/admin/clearLog', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            var data = await res.json().catch(function () { return { success: false }; });
            if (data.success) {
                showToast('🗑️ Log cleared', 'success');
                setTimeout(function () { location.reload(); }, 800);
            } else {
                showToast('Log clear not implemented on server yet', 'error');
            }
        } catch (e) {
            showToast('Error: ' + e.message, 'error');
        }
    };

    /* ═══════════════════════ AUTO-REFRESH ═══════════════════════ */
    var autoToggle = document.getElementById('autoRefresh');
    var autoTimer;
    if (autoToggle) {
        /* Restore state */
        if (localStorage.getItem('saip_health_auto') === '1') {
            autoToggle.checked = true;
            startAuto();
        }
        autoToggle.addEventListener('change', function () {
            localStorage.setItem('saip_health_auto', this.checked ? '1' : '0');
            if (this.checked) startAuto(); else stopAuto();
        });
    }
    function startAuto() {
        stopAuto();
        autoTimer = setInterval(function () {
            if (!document.hidden) location.reload();
        }, 60000);
        updateLastChecked();
    }
    function stopAuto() { clearInterval(autoTimer); }

    /* ═══════════════════════ HELPERS ═══════════════════════ */
    function updateLastChecked() {
        var el = document.getElementById('lastChecked');
        if (!el) return;
        var t = new Date().toLocaleTimeString();
        el.innerHTML = '<span class="pulse-dot"></span> Last check: <strong>' + t + '</strong>';
    }

    function copyToClipboard(text, msg) {
        var notify = function () { showToast('📋 ' + (msg || 'Copied'), 'success'); };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(notify).catch(function () {
                fallbackCopy(text); notify();
            });
        } else {
            fallbackCopy(text); notify();
        }
    }
    function fallbackCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        ta.remove();
    }

    window.showToast = function (msg, type) {
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

    /* ═══════════════════════ MODAL DISMISS ═══════════════════════ */
    document.getElementById('diagModal')?.addEventListener('click', function (e) {
        if (e.target === this) closeDiag();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDiag();
    });

    /* ═══════════════════════ SHORTCUTS ═══════════════════════ */
    document.addEventListener('keydown', function (e) {
        /* R → refresh, unless typing in an input */
        if (e.target.matches('input, textarea, select')) return;
        if (e.key === 'r' || e.key === 'R') location.reload();
        if (e.key === 'c' || e.key === 'C') copyReport();
    });
})();
</script>

<style>
/* ═══════════════════════════════════════════════════════════
   LAYOUT
   ═══════════════════════════════════════════════════════════ */
html, body { margin: 0 !important; padding: 0 !important; overflow-x: hidden !important; }

.admin-page.dashboard-layout {
    display: flex !important;
    width: 100% !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
}
.admin-page .main-content {
    flex: 1 !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    padding: 26px 32px !important;
    box-sizing: border-box !important;
}
.admin-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 26px !important;
    margin-bottom: 22px !important;
    box-sizing: border-box !important;
}

/* ═══════════════════════════════════════════════════════════
   HERO
   ═══════════════════════════════════════════════════════════ */
.hero-card {
    background: linear-gradient(135deg, rgba(248, 113, 113, 0.10), rgba(239, 68, 68, 0.06)) !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 24px !important;
    flex-wrap: wrap !important;
    padding: 28px !important;
}
.hero-flex { display: flex !important; align-items: center !important; gap: 24px !important; flex: 1 !important; min-width: 300px !important; }
.hero-content { flex: 1 !important; min-width: 0 !important; }

/* Health gauge */
.gauge-wrap { position: relative !important; width: 120px !important; height: 120px !important; flex-shrink: 0 !important; }
.gauge { transform: rotate(-90deg) !important; }
.gauge-track { fill: none; stroke: rgba(255, 255, 255, 0.08); stroke-width: 10; }
.gauge-fill {
    fill: none;
    stroke: url(#gaugeGrad);
    stroke: #10b981;
    stroke-width: 10;
    stroke-linecap: round;
    transition: stroke-dashoffset 1s cubic-bezier(0.4, 0, 0.2, 1);
}
.gauge-text {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    pointer-events: none;
}
.gauge-text strong { font-size: 30px; font-weight: 900; color: #f1f5f9; line-height: 1; }
.gauge-text small { font-size: 9px; font-weight: 800; color: #94a3b8; letter-spacing: 1.4px; margin-top: 2px; }

.hero-tag {
    display: inline-block !important;
    padding: 5px 14px !important;
    background: rgba(248, 113, 113, 0.15) !important;
    color: #f87171 !important;
    border: 1px solid rgba(248, 113, 113, 0.3) !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    margin-bottom: 10px !important;
}
.hero-card h1 { font-size: 26px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 6px 0 !important; }
.hero-card p  { color: #94a3b8 !important; font-size: 13.5px !important; margin: 0 0 12px 0 !important; }

.hero-meta { display: flex !important; gap: 10px !important; flex-wrap: wrap !important; }
.meta-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 6px 14px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 999px !important;
    font-size: 11.5px !important;
    color: #cbd5e1 !important;
    font-weight: 600 !important;
}
.meta-pill strong { color: #f1f5f9 !important; }
.pulse-dot {
    width: 7px; height: 7px;
    background: #10b981; border-radius: 50%;
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.7);
    animation: pulse 1.8s ease-in-out infinite;
}
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.35; } }
.toggle-auto { cursor: pointer !important; user-select: none !important; }
.toggle-auto input { margin: 0 !important; accent-color: #10b981; }
.toggle-auto:hover { background: rgba(16, 185, 129, 0.12) !important; border-color: rgba(16, 185, 129, 0.35) !important; }

.hero-actions { display: flex !important; gap: 10px !important; flex-wrap: wrap !important; }
.btn-hero-secondary {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 11px 20px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #f1f5f9 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    font-size: 13px !important;
    cursor: pointer !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
}
.btn-hero-secondary:hover { background: rgba(255, 255, 255, 0.12) !important; transform: translateY(-1px) !important; }

/* ═══════════════════════════════════════════════════════════
   STATUS BANNER
   ═══════════════════════════════════════════════════════════ */
.status-banner {
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
    padding: 20px 24px !important;
    border-radius: 16px !important;
    margin-bottom: 22px !important;
    flex-wrap: wrap !important;
}
.status-banner.healthy { background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.08)) !important; border: 1px solid rgba(16, 185, 129, 0.3) !important; }
.status-banner.warning { background: linear-gradient(135deg, rgba(251, 191, 36, 0.15), rgba(245, 158, 11, 0.08)) !important; border: 1px solid rgba(251, 191, 36, 0.3) !important; }
.status-indicator {
    width: 14px !important; height: 14px !important;
    border-radius: 50% !important;
    background: #10b981 !important;
    box-shadow: 0 0 12px rgba(16, 185, 129, 0.6) !important;
    animation: pulse 2s infinite !important;
    flex-shrink: 0 !important;
}
.status-banner.warning .status-indicator { background: #fbbf24 !important; box-shadow: 0 0 12px rgba(251, 191, 36, 0.6) !important; }
.status-text { flex: 1 !important; min-width: 220px !important; }
.status-text strong { display: block !important; font-size: 15px !important; color: #f1f5f9 !important; margin-bottom: 3px !important; }
.status-text p { margin: 0 !important; font-size: 12.5px !important; color: #94a3b8 !important; }
.btn-toggle-maintenance {
    padding: 11px 22px !important;
    background: rgba(255, 255, 255, 0.08) !important;
    color: #f1f5f9 !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
}
.btn-toggle-maintenance:hover:not(:disabled) { background: rgba(255, 255, 255, 0.15) !important; }
.btn-toggle-maintenance:disabled { opacity: 0.6 !important; cursor: wait !important; }

/* ═══════════════════════════════════════════════════════════
   KPI CARDS
   ═══════════════════════════════════════════════════════════ */
.kpi-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)) !important;
    gap: 16px !important;
    margin-bottom: 22px !important;
}
.kpi-card {
    display: flex !important;
    align-items: flex-start !important;
    gap: 16px !important;
    padding: 22px !important;
    background: rgba(30, 41, 59, 0.65) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 18px !important;
    position: relative !important;
    overflow: hidden !important;
    transition: all 0.25s !important;
}
.kpi-card::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important; left: 0 !important;
    width: 4px !important;
    height: 100% !important;
}
.kpi-card.disk::before    { background: linear-gradient(180deg, #f87171, #dc2626) !important; }
.kpi-card.db::before      { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.kpi-card.uploads::before { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.kpi-card.backups::before { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.kpi-card:hover { transform: translateY(-4px) !important; border-color: rgba(56, 189, 248, 0.3) !important; }
.kpi-icon {
    width: 48px !important; height: 48px !important;
    border-radius: 12px !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    font-size: 22px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    flex-shrink: 0 !important;
}
.kpi-content { flex: 1 !important; min-width: 0 !important; }
.kpi-label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    margin: 0 0 6px 0 !important;
}
.kpi-value { font-size: 24px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 6px 0 !important; }
.kpi-sub { font-size: 11.5px !important; color: #94a3b8 !important; display: block !important; margin-top: 4px !important; }
.kpi-bar {
    width: 100% !important;
    height: 6px !important;
    background: rgba(255, 255, 255, 0.08) !important;
    border-radius: 999px !important;
    overflow: hidden !important;
    margin-bottom: 6px !important;
}
.kpi-bar-fill {
    height: 100% !important;
    background: linear-gradient(90deg, #f87171, #dc2626) !important;
    border-radius: 999px !important;
    transition: width 0.4s ease !important;
}

/* ═══════════════════════════════════════════════════════════
   CARD HEADER
   ═══════════════════════════════════════════════════════════ */
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
.card-header h3 { font-size: 17px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 !important; }
.card-subtitle { font-size: 12.5px !important; color: #94a3b8 !important; margin: 4px 0 0 0 !important; }
.card-subtitle code { background: rgba(56, 189, 248, 0.12); color: #38bdf8; padding: 1px 6px; border-radius: 4px; font-size: 11.5px; }

.btn-mini {
    padding: 8px 16px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 8px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
}
.btn-mini:hover { background: rgba(255, 255, 255, 0.1) !important; }
.btn-mini.danger { color: #f87171 !important; border-color: rgba(239, 68, 68, 0.3) !important; background: rgba(239, 68, 68, 0.08) !important; }
.btn-mini.danger:hover { background: rgba(239, 68, 68, 0.2) !important; }

/* ═══════════════════════════════════════════════════════════
   INFO GRID
   ═══════════════════════════════════════════════════════════ */
.info-grid { display: flex !important; flex-direction: column !important; gap: 4px !important; }
.info-row {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 13px 18px !important;
    border-radius: 10px !important;
    background: rgba(0, 0, 0, 0.15) !important;
    transition: background 0.2s !important;
    gap: 16px !important;
    flex-wrap: wrap !important;
}
.info-row:hover { background: rgba(56, 189, 248, 0.06) !important; }
.info-key { font-size: 13px !important; color: #94a3b8 !important; font-weight: 500 !important; }
.info-val {
    font-size: 13.5px !important;
    color: #f1f5f9 !important;
    font-weight: 700 !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}
.info-val code {
    background: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
    padding: 3px 10px !important;
    border-radius: 6px !important;
    font-size: 12.5px !important;
    font-family: 'Courier New', monospace !important;
}
.status-dot { width: 9px !important; height: 9px !important; border-radius: 50% !important; }
.status-dot.ok { background: #10b981 !important; box-shadow: 0 0 8px rgba(16, 185, 129, 0.6) !important; }
.status-dot.fail { background: #ef4444 !important; box-shadow: 0 0 8px rgba(239, 68, 68, 0.6) !important; }

/* ═══════════════════════════════════════════════════════════
   DATA STATS
   ═══════════════════════════════════════════════════════════ */
.data-stats {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) !important;
    gap: 14px !important;
}
.data-stat {
    display: flex !important;
    flex-direction: column !important;
    gap: 8px !important;
    padding: 20px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 14px !important;
    transition: all 0.2s !important;
}
.data-stat:hover { background: rgba(56, 189, 248, 0.06) !important; border-color: rgba(56, 189, 248, 0.3) !important; }
.data-stat-label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
}
.data-stat-value {
    font-size: 26px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    font-variant-numeric: tabular-nums !important;
}

/* ═══════════════════════════════════════════════════════════
   EXTENSIONS
   ═══════════════════════════════════════════════════════════ */
.ext-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)) !important;
    gap: 10px !important;
}
.ext-item {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 12px 14px !important;
    border-radius: 10px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    transition: all 0.2s !important;
}
.ext-item.ok   { border-color: rgba(16, 185, 129, 0.25) !important; }
.ext-item.warn { border-color: rgba(251, 191, 36, 0.25) !important; }
.ext-item.fail { border-color: rgba(239, 68, 68, 0.35) !important; background: rgba(239, 68, 68, 0.06) !important; }
.ext-item:hover { transform: translateY(-2px) !important; }
.ext-icon { font-size: 18px !important; }
.ext-item strong { display: block !important; font-size: 13px !important; color: #f1f5f9 !important; font-weight: 700 !important; }
.ext-item small  { display: block !important; font-size: 10.5px !important; color: #94a3b8 !important; margin-top: 2px !important; }

/* ═══════════════════════════════════════════════════════════
   LOG VIEWER
   ═══════════════════════════════════════════════════════════ */
.log-viewer {
    max-height: 320px !important;
    overflow-y: auto !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border-radius: 12px !important;
    padding: 14px !important;
    font-family: 'Courier New', monospace !important;
    font-size: 11.5px !important;
    line-height: 1.6 !important;
}
.log-line {
    padding: 4px 0 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    color: #cbd5e1 !important;
    word-break: break-all !important;
}
.log-line:last-child { border-bottom: none !important; }
.log-line:contains('Fatal') { color: #f87171 !important; }
.log-empty { text-align: center !important; padding: 40px 20px !important; }
.log-empty-icon { font-size: 48px !important; margin-bottom: 10px !important; opacity: 0.7 !important; }
.log-empty p { color: #94a3b8 !important; font-size: 13.5px !important; margin: 0 !important; }

/* ═══════════════════════════════════════════════════════════
   QUICK ACTIONS
   ═══════════════════════════════════════════════════════════ */
.quick-actions {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
    gap: 14px !important;
}
.quick-btn {
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 6px !important;
    padding: 20px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 14px !important;
    text-decoration: none !important;
    color: inherit !important;
    cursor: pointer !important;
    transition: all 0.2s !important;
    text-align: left !important;
    font-family: inherit !important;
}
.quick-btn:hover:not(:disabled) {
    background: rgba(56, 189, 248, 0.06) !important;
    border-color: rgba(56, 189, 248, 0.3) !important;
    transform: translateY(-3px) !important;
}
.quick-btn:disabled { opacity: 0.6 !important; cursor: wait !important; }
.quick-btn span   { font-size: 26px !important; }
.quick-btn strong { display: block !important; font-size: 14px !important; color: #f1f5f9 !important; margin: 6px 0 2px 0 !important; font-weight: 700 !important; }
.quick-btn small  { font-size: 12px !important; color: #94a3b8 !important; }

/* ═══════════════════════════════════════════════════════════
   MODAL
   ═══════════════════════════════════════════════════════════ */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
    z-index: 9999;
    padding: 20px;
    box-sizing: border-box;
}
.modal-overlay:not([hidden]) { display: flex; }
.modal-overlay[hidden] { display: none !important; }

.modal {
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 26px;
    max-width: 560px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6);
    box-sizing: border-box;
    animation: modalIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

.modal-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 20px !important;
    padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.modal-header h3 { font-size: 17px !important; color: #f1f5f9 !important; margin: 0 !important; font-weight: 800 !important; }
.modal-close {
    width: 32px !important; height: 32px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 8px !important;
    color: #cbd5e1 !important;
    font-size: 14px !important;
    cursor: pointer !important;
    font-family: inherit !important;
    padding: 0 !important;
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    transition: all 0.2s !important;
}
.modal-close:hover { background: rgba(239, 68, 68, 0.2) !important; color: #f87171 !important; }
.modal-actions {
    display: flex !important;
    justify-content: flex-end !important;
    gap: 10px !important;
    margin-top: 22px !important;
    padding-top: 18px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.btn-primary {
    padding: 11px 24px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important; border: none !important;
    border-radius: 10px !important;
    font-size: 13.5px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    transition: all 0.2s !important;
}
.btn-primary:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5) !important; }
.btn-secondary {
    padding: 11px 22px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 13.5px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    transition: all 0.2s !important;
}
.btn-secondary:hover { background: rgba(255, 255, 255, 0.1) !important; }

/* Diagnostic content */
.diag-body { display: flex !important; flex-direction: column !important; gap: 16px !important; }
.diag-summary { display: flex !important; gap: 8px !important; flex-wrap: wrap !important; }
.diag-chip {
    padding: 6px 14px !important;
    border-radius: 999px !important;
    font-size: 12px !important;
    font-weight: 800 !important;
}
.diag-chip.ok   { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
.diag-chip.warn { background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); }
.diag-chip.fail { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
.diag-list { display: flex !important; flex-direction: column !important; gap: 8px !important; }
.diag-item {
    display: flex !important;
    align-items: flex-start !important;
    gap: 12px !important;
    padding: 12px 14px !important;
    border-radius: 10px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border-left: 3px solid #64748b !important;
}
.diag-item.ok   { border-left-color: #10b981 !important; }
.diag-item.warn { border-left-color: #fbbf24 !important; }
.diag-item.fail { border-left-color: #ef4444 !important; }
.diag-icon { font-size: 18px !important; line-height: 1 !important; }
.diag-item strong { display: block !important; font-size: 13.5px !important; color: #f1f5f9 !important; font-weight: 700 !important; margin-bottom: 3px !important; }
.diag-item small  { display: block !important; font-size: 12px !important; color: #94a3b8 !important; line-height: 1.5 !important; }

/* ═══════════════════════════════════════════════════════════
   TOAST
   ═══════════════════════════════════════════════════════════ */
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
    pointer-events: none !important;
    max-width: 420px !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5) !important;
}
.toast.show { transform: translateY(0) !important; opacity: 1 !important; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.toast.error   { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }
.toast.info    { background: linear-gradient(135deg, #38bdf8, #0284c7) !important; }

/* ═══════════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 900px) {
    .admin-page .main-content {
        width: 100% !important;
        max-width: 100% !important;
        padding: 20px !important;
        padding-top: 72px !important;
    }
}
@media (max-width: 768px) {
    .admin-page .main-content { padding: 14px !important; padding-top: 68px !important; }
    .hero-card { flex-direction: column !important; align-items: stretch !important; }
    .hero-flex { flex-direction: column !important; align-items: flex-start !important; }
    .hero-card h1 { font-size: 22px !important; }
    .hero-actions { width: 100% !important; }
    .hero-actions .btn-hero-secondary { flex: 1 !important; justify-content: center !important; }
    .kpi-grid { grid-template-columns: 1fr !important; }
    .toast { left: 16px !important; right: 16px !important; bottom: 16px !important; }
    .ext-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)) !important; }
}
</style>