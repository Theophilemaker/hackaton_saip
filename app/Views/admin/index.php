<div class="dashboard-layout admin-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ============ FLASH MESSAGES ============ -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="flash success">✅ <?= $_SESSION['flash_success'] ?></div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="flash error">⚠️ <?= $_SESSION['flash_error'] ?></div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- ============ HERO ============ -->
        <section class="glass-card admin-hero">
            <div class="hero-content">
                <span class="hero-tag">🛡️ Control Center</span>
                <h1>Admin Dashboard</h1>
                <p>Full control over users, content, settings, backups, and system health.</p>
                <div class="hero-actions">
                    <a href="<?= BASE_URL ?>/admin/profile" class="btn-hero-primary">👤 My Profile</a>
                    <a href="<?= BASE_URL ?>/admin/backup" class="btn-hero-secondary">💾 Backup</a>
                    <a href="<?= BASE_URL ?>/admin/health" class="btn-hero-secondary">⚙️ Health</a>
                </div>
            </div>
            <div class="hero-meta">
                <div class="hero-meta-item">
                    <span class="hero-meta-label">Signed in as</span>
                    <strong><?= e($_SESSION['name'] ?? 'Admin') ?></strong>
                </div>
                <div class="hero-meta-item">
                    <span class="hero-meta-label">Role</span>
                    <strong><?= e($_SESSION['role_name'] ?? 'Administrator') ?></strong>
                </div>
                <div class="hero-meta-item">
                    <span class="hero-meta-label">Time</span>
                    <strong id="liveClock">--:--</strong>
                </div>
            </div>
        </section>

        <!-- ============ KPI GRID ============ -->
        <section class="kpi-grid">
            <div class="kpi-card users">
                <div class="kpi-icon">👥</div>
                <div class="kpi-content">
                    <p class="kpi-label">Total Users</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['users'] ?? 0) ?></h3>
                </div>
            </div>

            <div class="kpi-card products">
                <div class="kpi-icon">📦</div>
                <div class="kpi-content">
                    <p class="kpi-label">Active Products</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['products'] ?? 0) ?></h3>
                </div>
            </div>

            <div class="kpi-card orders">
                <div class="kpi-icon">🛒</div>
                <div class="kpi-content">
                    <p class="kpi-label">Total Orders</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['orders'] ?? 0) ?></h3>
                </div>
            </div>

            <div class="kpi-card farms">
                <div class="kpi-icon">🚜</div>
                <div class="kpi-content">
                    <p class="kpi-label">Active Farms</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['farms'] ?? 0) ?></h3>
                </div>
            </div>

            <div class="kpi-card messages">
                <div class="kpi-icon">💬</div>
                <div class="kpi-content">
                    <p class="kpi-label">Messages</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['messages'] ?? 0) ?></h3>
                </div>
            </div>

            <div class="kpi-card logins">
                <div class="kpi-icon">🔓</div>
                <div class="kpi-content">
                    <p class="kpi-label">Logins (24h)</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['logins_today'] ?? 0) ?></h3>
                </div>
            </div>
        </section>

        <!-- ============ QUICK ACTIONS SECTION ============ -->
        <div class="section-header">
            <h3 class="section-heading">⚡ Admin Tools</h3>
            <p class="section-subheading">Manage every aspect of your platform</p>
        </div>

        <section class="tools-grid">
            <!-- My Profile -->
            <a href="<?= BASE_URL ?>/admin/profile" class="tool-card profile">
                <div class="tool-icon-wrap">👤</div>
                <div class="tool-info">
                    <strong>My Profile</strong>
                    <small>Edit your account & preferences</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <!-- Users -->
            <a href="<?= BASE_URL ?>/admin/users" class="tool-card users">
                <div class="tool-icon-wrap">👥</div>
                <div class="tool-info">
                    <strong>User Management</strong>
                    <small>Add, edit, suspend, delete</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <!-- Roles -->
            <a href="<?= BASE_URL ?>/admin/roles" class="tool-card roles">
                <div class="tool-icon-wrap">🔐</div>
                <div class="tool-info">
                    <strong>Roles & Permissions</strong>
                    <small>Full access matrix</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <!-- Branding -->
            <a href="<?= BASE_URL ?>/admin/branding" class="tool-card branding">
                <div class="tool-icon-wrap">🎨</div>
                <div class="tool-info">
                    <strong>System Branding</strong>
                    <small>Logo, colors, contact info</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <!-- Email -->
            <a href="<?= BASE_URL ?>/admin/email" class="tool-card email">
                <div class="tool-icon-wrap">📧</div>
                <div class="tool-info">
                    <strong>Email Settings</strong>
                    <small>SMTP config & templates</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <!-- SMS -->
            <a href="<?= BASE_URL ?>/admin/sms" class="tool-card sms">
                <div class="tool-icon-wrap">📱</div>
                <div class="tool-info">
                    <strong>SMS Settings</strong>
                    <small>Provider & templates</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <!-- Backup -->
            <a href="<?= BASE_URL ?>/admin/backup" class="tool-card backup">
                <div class="tool-icon-wrap">💾</div>
                <div class="tool-info">
                    <strong>System Backup</strong>
                    <small>Database & files snapshots</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <!-- Activity -->
            <a href="<?= BASE_URL ?>/admin/activity" class="tool-card activity">
                <div class="tool-icon-wrap">📋</div>
                <div class="tool-info">
                    <strong>Activity Log</strong>
                    <small>Audit trail of all actions</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <!-- Health -->
            <a href="<?= BASE_URL ?>/admin/health" class="tool-card health">
                <div class="tool-icon-wrap">⚙️</div>
                <div class="tool-info">
                    <strong>System Health</strong>
                    <small>PHP, DB, disk, memory info</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <!-- Translations -->
            <a href="<?= BASE_URL ?>/admin/translations" class="tool-card trans">
                <div class="tool-icon-wrap">🌐</div>
                <div class="tool-info">
                    <strong>Translations</strong>
                    <small>Language strings</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <!-- API Keys -->
            <a href="<?= BASE_URL ?>/admin/apiKeys" class="tool-card api">
                <div class="tool-icon-wrap">🔑</div>
                <div class="tool-info">
                    <strong>API Keys</strong>
                    <small>Developer access tokens</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <!-- AI Models -->
            <a href="<?= BASE_URL ?>/admin/models" class="tool-card models">
                <div class="tool-icon-wrap">🤖</div>
                <div class="tool-info">
                    <strong>AI Models</strong>
                    <small>ML model versions</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <!-- Back to main -->
            <a href="<?= BASE_URL ?>/dashboard" class="tool-card back">
                <div class="tool-icon-wrap">🏠</div>
                <div class="tool-info">
                    <strong>Main Dashboard</strong>
                    <small>Return to user view</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>
        </section>

        <!-- ============ RECENT LOGINS ============ -->
        <section class="glass-card">
            <div class="card-header">
                <div>
                    <h3>🕐 Recent Logins</h3>
                    <p class="card-subtitle">Last 8 login events across the platform</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/activity" class="btn-secondary-sm">View All →</a>
            </div>

            <?php if (empty($data['recentLogins'])): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>No Recent Logins</h3>
                    <p>Login events will appear here.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>IP Address</th>
                                <th class="date-col">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['recentLogins'] as $l): ?>
                                <tr>
                                    <td class="name-cell">
                                        <strong><?= e(($l['first_name'] ?? 'Unknown') . ' ' . ($l['last_name'] ?? '')) ?></strong>
                                    </td>
                                    <td class="email-cell"><?= e($l['email'] ?? '—') ?></td>
                                    <td><code class="ip-cell"><?= e($l['ip_address'] ?? '—') ?></code></td>
                                    <td class="date-cell"><?= date('M d, Y H:i', strtotime($l['login_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<script>
/* Live clock */
function updateClock() {
    const el = document.getElementById('liveClock');
    if (!el) return;
    const now = new Date();
    el.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
}
updateClock();
setInterval(updateClock, 30000);
</script>

<style>
/* =========================================
   ADMIN DASHBOARD — FULL WIDTH DARK THEME
   ========================================= */

.admin-page.dashboard-layout {
    display: flex !important;
    width: 100vw !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
    overflow-x: hidden !important;
}

.admin-page .main-content {
    flex: 1 !important;
    width: calc(100% - 250px) !important;
    max-width: none !important;
    min-width: 0 !important;
    padding: 32px !important;
    box-sizing: border-box !important;
}

.admin-page .glass-card {
    width: 100% !important;
    box-sizing: border-box !important;
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 28px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
}

/* FLASH */
.flash {
    padding: 14px 22px !important;
    border-radius: 12px !important;
    margin-bottom: 20px !important;
    font-weight: 600 !important;
    font-size: 13.5px !important;
}
.flash.success { background: rgba(16, 185, 129, 0.15) !important; border: 1px solid rgba(16, 185, 129, 0.4) !important; color: #6ee7b7 !important; }
.flash.error   { background: rgba(239, 68, 68, 0.15) !important; border: 1px solid rgba(239, 68, 68, 0.4) !important; color: #fca5a5 !important; }

/* HERO */
.admin-hero {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(167, 139, 250, 0.08)) !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 32px !important;
    flex-wrap: wrap !important;
    position: relative !important;
    overflow: hidden !important;
}

.admin-hero::before {
    content: '' !important;
    position: absolute !important;
    top: -50% !important;
    right: -10% !important;
    width: 400px !important;
    height: 400px !important;
    background: radial-gradient(circle, rgba(239, 68, 68, 0.15), transparent 70%) !important;
    pointer-events: none !important;
}

.hero-content {
    flex: 1 !important;
    min-width: 300px !important;
    position: relative !important;
    z-index: 1 !important;
}

.hero-tag {
    display: inline-block !important;
    padding: 6px 14px !important;
    background: rgba(239, 68, 68, 0.15) !important;
    color: #f87171 !important;
    border: 1px solid rgba(239, 68, 68, 0.3) !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    margin-bottom: 14px !important;
}

.admin-hero h1 {
    font-size: 32px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 0 10px 0 !important;
    line-height: 1.15 !important;
}

.admin-hero p {
    color: #94a3b8 !important;
    font-size: 14.5px !important;
    line-height: 1.6 !important;
    margin: 0 0 20px 0 !important;
    max-width: 620px !important;
}

.hero-actions {
    display: flex !important;
    gap: 10px !important;
    flex-wrap: wrap !important;
}

.btn-hero-primary,
.btn-hero-secondary {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 13px 24px !important;
    border-radius: 10px !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
    border: none !important;
    cursor: pointer !important;
}

.btn-hero-primary {
    background: linear-gradient(135deg, #ef4444, #dc2626) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3) !important;
}
.btn-hero-primary:hover { transform: translateY(-2px) !important; box-shadow: 0 8px 25px rgba(239, 68, 68, 0.5) !important; }

.btn-hero-secondary {
    background: rgba(255, 255, 255, 0.05) !important;
    color: #f1f5f9 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
}
.btn-hero-secondary:hover { background: rgba(255, 255, 255, 0.1) !important; transform: translateY(-2px) !important; }

.hero-meta {
    display: flex !important;
    gap: 20px !important;
    flex-wrap: wrap !important;
    position: relative !important;
    z-index: 1 !important;
}

.hero-meta-item {
    display: flex !important;
    flex-direction: column !important;
    gap: 4px !important;
    padding: 14px 20px !important;
    background: rgba(0, 0, 0, 0.25) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 12px !important;
    min-width: 130px !important;
}

.hero-meta-label {
    font-size: 10.5px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
}

.hero-meta-item strong {
    font-size: 14px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
}

/* KPI GRID */
.kpi-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
    gap: 18px !important;
    margin-bottom: 32px !important;
}

.kpi-card {
    display: flex !important;
    align-items: center !important;
    gap: 18px !important;
    padding: 22px !important;
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 18px !important;
    position: relative !important;
    overflow: hidden !important;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease !important;
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

.kpi-card.users::before    { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.kpi-card.products::before { background: linear-gradient(180deg, #10b981, #059669) !important; }
.kpi-card.orders::before   { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.kpi-card.farms::before    { background: linear-gradient(180deg, #84cc16, #16a34a) !important; }
.kpi-card.messages::before { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.kpi-card.logins::before   { background: linear-gradient(180deg, #f472b6, #be185d) !important; }

.kpi-card:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4) !important;
    border-color: rgba(56, 189, 248, 0.3) !important;
}

.kpi-icon {
    width: 52px !important;
    height: 52px !important;
    border-radius: 14px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 24px !important;
    flex-shrink: 0 !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
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

.kpi-value {
    font-size: 26px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 !important;
    line-height: 1.1 !important;
    font-variant-numeric: tabular-nums !important;
}

/* SECTION HEADER */
.section-header {
    margin: 32px 0 20px 0 !important;
}

.section-heading {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    margin: 0 0 6px 0 !important;
}

.section-subheading {
    font-size: 13px !important;
    color: #94a3b8 !important;
    margin: 0 !important;
}

/* TOOLS GRID */
.tools-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
    gap: 18px !important;
    margin-bottom: 32px !important;
}

.tool-card {
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
    padding: 20px 22px !important;
    background: rgba(30, 41, 59, 0.55) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 16px !important;
    text-decoration: none !important;
    color: inherit !important;
    transition: all 0.25s ease !important;
    cursor: pointer !important;
    position: relative !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
}

.tool-card::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 4px !important;
    height: 100% !important;
    transition: width 0.3s ease !important;
}

/* Color coding by tool */
.tool-card.profile::before  { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.tool-card.users::before    { background: linear-gradient(180deg, #60a5fa, #2563eb) !important; }
.tool-card.roles::before    { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.tool-card.branding::before { background: linear-gradient(180deg, #f472b6, #be185d) !important; }
.tool-card.email::before    { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.tool-card.sms::before      { background: linear-gradient(180deg, #22c55e, #16a34a) !important; }
.tool-card.backup::before   { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.tool-card.activity::before { background: linear-gradient(180deg, #06b6d4, #0891b2) !important; }
.tool-card.health::before   { background: linear-gradient(180deg, #f87171, #dc2626) !important; }
.tool-card.trans::before    { background: linear-gradient(180deg, #10b981, #059669) !important; }
.tool-card.api::before      { background: linear-gradient(180deg, #f59e0b, #d97706) !important; }
.tool-card.models::before   { background: linear-gradient(180deg, #f472b6, #be185d) !important; }
.tool-card.back::before     { background: linear-gradient(180deg, #64748b, #334155) !important; }

.tool-card:hover {
    transform: translateY(-4px) !important;
    background: rgba(30, 41, 59, 0.85) !important;
    border-color: rgba(56, 189, 248, 0.4) !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4) !important;
}

.tool-card:hover::before {
    width: 6px !important;
}

.tool-icon-wrap {
    width: 52px !important;
    height: 52px !important;
    border-radius: 14px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 24px !important;
    flex-shrink: 0 !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.tool-info {
    flex: 1 !important;
    min-width: 0 !important;
}

.tool-info strong {
    display: block !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    margin-bottom: 4px !important;
}

.tool-info small {
    display: block !important;
    font-size: 12px !important;
    color: #94a3b8 !important;
    line-height: 1.4 !important;
}

.tool-arrow {
    font-size: 20px !important;
    color: #475569 !important;
    flex-shrink: 0 !important;
    transition: transform 0.25s ease, color 0.25s ease !important;
}

.tool-card:hover .tool-arrow {
    transform: translateX(4px) !important;
    color: #38bdf8 !important;
}

/* CARD HEADERS */
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

.card-header h3 {
    font-size: 18px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    margin: 0 !important;
}

.card-subtitle {
    font-size: 12.5px !important;
    color: #94a3b8 !important;
    margin: 4px 0 0 0 !important;
}

.btn-secondary-sm {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 9px 16px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    transition: all 0.2s !important;
    white-space: nowrap !important;
}
.btn-secondary-sm:hover {
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border-color: rgba(56, 189, 248, 0.4) !important;
}

/* TABLE */
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
    min-width: 700px !important;
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

.data-table thead th.date-col {
    text-align: right !important;
}

.data-table tbody td {
    padding: 13px 16px !important;
    color: #f1f5f9 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    vertical-align: middle !important;
}

.data-table tbody tr {
    transition: background 0.15s ease !important;
}

.data-table tbody tr:hover td {
    background: rgba(56, 189, 248, 0.04) !important;
}

.data-table tbody tr:last-child td {
    border-bottom: none !important;
}

.name-cell strong {
    color: #f1f5f9 !important;
    font-weight: 700 !important;
}

.email-cell {
    color: #94a3b8 !important;
    font-size: 12.5px !important;
}

.ip-cell {
    background: rgba(0, 0, 0, 0.3) !important;
    padding: 3px 9px !important;
    border-radius: 5px !important;
    color: #38bdf8 !important;
    font-family: 'Courier New', monospace !important;
    font-size: 11.5px !important;
}

.date-cell {
    color: #94a3b8 !important;
    font-size: 12.5px !important;
    white-space: nowrap !important;
    text-align: right !important;
}

/* EMPTY STATE */
.empty-state {
    text-align: center !important;
    padding: 48px 24px !important;
}

.empty-icon {
    font-size: 52px !important;
    margin-bottom: 12px !important;
    opacity: 0.6 !important;
}

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

/* RESPONSIVE */
@media (max-width: 1024px) {
    .admin-hero {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    .hero-meta {
        width: 100% !important;
    }
}

@media (max-width: 768px) {
    .admin-page .main-content { padding: 16px !important; }
    .admin-page .glass-card { padding: 20px !important; }
    .admin-hero h1 { font-size: 22px !important; }
    .hero-actions { flex-direction: column !important; width: 100% !important; }
    .btn-hero-primary,
    .btn-hero-secondary { width: 100% !important; justify-content: center !important; }
    .hero-meta { flex-direction: column !important; }
    .hero-meta-item { width: 100% !important; }
    .kpi-grid { grid-template-columns: 1fr 1fr !important; gap: 12px !important; }
    .kpi-card { padding: 16px !important; gap: 12px !important; }
    .kpi-icon { width: 42px !important; height: 42px !important; font-size: 20px !important; }
    .kpi-value { font-size: 20px !important; }
    .tools-grid { grid-template-columns: 1fr !important; }
    .card-header { flex-direction: column !important; align-items: flex-start !important; }
    .btn-secondary-sm { width: 100% !important; justify-content: center !important; }
}

@media (max-width: 480px) {
    .kpi-grid { grid-template-columns: 1fr !important; }
}
</style>