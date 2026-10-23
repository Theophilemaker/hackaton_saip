<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card admin-hero">
            <div>
                <h1>⚙️ Admin Control Center</h1>
                <p>Full system administration · Monitor · Manage · Secure</p>
            </div>
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>/admin/users" class="btn-primary">👥 Users</a>
                <a href="<?= BASE_URL ?>/security/auditLogs" class="btn-secondary">📋 Audit</a>
            </div>
        </section>

        <!-- System KPIs -->
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">👥</div>
                <div>
                    <p class="kpi-label">Total Users</p>
                    <h3 class="kpi-value" id="kpiUsers">—</h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#84CC16,#16A34A)">🏡</div>
                <div>
                    <p class="kpi-label">Active Farms</p>
                    <h3 class="kpi-value" id="kpiFarms">—</h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">🛒</div>
                <div>
                    <p class="kpi-label">Orders</p>
                    <h3 class="kpi-value" id="kpiOrders">—</h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">💬</div>
                <div>
                    <p class="kpi-label">Messages</p>
                    <h3 class="kpi-value" id="kpiMessages">—</h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#7C3AED,#C084FC)">📦</div>
                <div>
                    <p class="kpi-label">Active Products</p>
                    <h3 class="kpi-value" id="kpiProducts">—</h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#06B6D4,#0EA5E9)">🔐</div>
                <div>
                    <p class="kpi-label">Logins (24h)</p>
                    <h3 class="kpi-value" id="kpiLogins">—</h3>
                </div>
            </div>
        </section>

        <!-- Admin Tools Grid -->
        <section class="glass-card">
            <div class="card-header"><h3>🛠️ Admin Tools</h3></div>
            <div class="admin-tools-grid">
                <a href="<?= BASE_URL ?>/admin/users" class="admin-tool">
                    <span class="at-icon">👥</span>
                    <strong>User Management</strong>
                    <small>View, suspend, edit users</small>
                </a>
                <a href="<?= BASE_URL ?>/admin/roles" class="admin-tool">
                    <span class="at-icon">🛡️</span>
                    <strong>Roles & Permissions</strong>
                    <small>RBAC configuration</small>
                </a>
                <a href="<?= BASE_URL ?>/admin/translations" class="admin-tool">
                    <span class="at-icon">🌐</span>
                    <strong>Translations</strong>
                    <small>Edit UI text per language</small>
                </a>
                <a href="<?= BASE_URL ?>/admin/apiKeys" class="admin-tool">
                    <span class="at-icon">🔑</span>
                    <strong>API Keys</strong>
                    <small>Manage developer tokens</small>
                </a>
                <a href="<?= BASE_URL ?>/admin/models" class="admin-tool">
                    <span class="at-icon">🤖</span>
                    <strong>AI Models</strong>
                    <small>Model versions & accuracy</small>
                </a>
                <a href="<?= BASE_URL ?>/security/auditLogs" class="admin-tool">
                    <span class="at-icon">📋</span>
                    <strong>Audit Logs</strong>
                    <small>Every action tracked</small>
                </a>
                <a href="<?= BASE_URL ?>/security/loginHistory" class="admin-tool">
                    <span class="at-icon">🔐</span>
                    <strong>Login History</strong>
                    <small>Success & failures</small>
                </a>
                <a href="<?= BASE_URL ?>/security/sessions" class="admin-tool">
                    <span class="at-icon">💻</span>
                    <strong>Active Sessions</strong>
                    <small>Kill sessions</small>
                </a>
            </div>
        </section>

        <!-- Recent Activity -->
        <section class="gov-grid-2">
            <div class="glass-card">
                <div class="card-header">
                    <h3>📋 Recent Logins</h3>
                    <a href="<?= BASE_URL ?>/security/loginHistory" class="btn-sm">View All</a>
                </div>
                <div id="recentLogins" class="activity-list">
                    <p class="empty-state">Loading...</p>
                </div>
            </div>

            <div class="glass-card">
                <div class="card-header">
                    <h3>🔔 System Alerts</h3>
                    <a href="<?= BASE_URL ?>/notifications" class="btn-sm">View All</a>
                </div>
                <div id="systemAlerts" class="activity-list">
                    <p class="empty-state">Loading...</p>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Simulate loading KPIs (replace with real API endpoints)
    const kpis = {
        kpiUsers:    '<?= number_format(rand(1000, 5000)) ?>',
        kpiFarms:    '<?= number_format(rand(200, 800)) ?>',
        kpiOrders:   '<?= number_format(rand(50, 400)) ?>',
        kpiMessages: '<?= number_format(rand(5000, 25000)) ?>',
        kpiProducts: '<?= number_format(rand(80, 300)) ?>',
        kpiLogins:   '<?= number_format(rand(20, 200)) ?>'
    };
    Object.entries(kpis).forEach(([id, val]) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    });
});
</script>

<style>
.admin-hero {
    padding: 30px !important;
    max-width: none;
    background: linear-gradient(135deg, rgba(22,163,74,0.2), rgba(99,102,241,0.08));
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.admin-hero h1 { margin: 0 0 4px; font-size: 26px; }
.admin-hero p { color: var(--text-sub); margin: 0; }
.admin-tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 14px;
}
.admin-tool {
    display: block;
    padding: 20px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 14px;
    text-decoration: none;
    color: white;
    transition: 250ms;
}
.admin-tool:hover {
    background: rgba(34,197,94,0.1);
    border-color: var(--accent);
    transform: translateY(-4px);
}
.at-icon { font-size: 30px; display: block; margin-bottom: 8px; }
.admin-tool strong { display: block; font-size: 14px; margin-bottom: 4px; }
.admin-tool small { color: var(--text-sub); font-size: 11px; }
.activity-list { display: flex; flex-direction: column; gap: 10px; }
</style>