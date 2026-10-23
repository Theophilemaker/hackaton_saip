<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card coop-hero">
            <div>
                <h1>👥 Cooperative Dashboard</h1>
                <p>Manage members, harvests, contributions, and equipment</p>
            </div>
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>/cooperative" class="btn-primary">📊 My Cooperatives</a>
                <a href="<?= BASE_URL ?>/cooperative/create" class="btn-secondary">+ Create New</a>
            </div>
        </section>

        <!-- KPIs -->
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">👥</div>
                <div>
                    <p class="kpi-label">Total Members</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['members'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#84CC16,#16A34A)">🌾</div>
                <div>
                    <p class="kpi-label">Total Harvest</p>
                    <h3 class="kpi-value"><?= number_format(($data['stats']['total_harvest'] ?? 0) / 1000, 1) ?> <small>t</small></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">💰</div>
                <div>
                    <p class="kpi-label">Contributions</p>
                    <h3 class="kpi-value"><?= number_format(($data['stats']['total_contributions'] ?? 0) / 1e6, 1) ?> <small>M RWF</small></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">🔧</div>
                <div>
                    <p class="kpi-label">Equipment</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['equipment_count'] ?? 0) ?></h3>
                </div>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="glass-card">
            <div class="card-header"><h3>⚡ Quick Actions</h3></div>
            <div class="admin-tools-grid">
                <a href="<?= BASE_URL ?>/cooperative" class="admin-tool">
                    <span class="at-icon">👥</span>
                    <strong>Members</strong>
                    <small>View and manage members</small>
                </a>
                <a href="<?= BASE_URL ?>/cooperative" class="admin-tool">
                    <span class="at-icon">🌾</span>
                    <strong>Harvests</strong>
                    <small>Record production</small>
                </a>
                <a href="<?= BASE_URL ?>/cooperative" class="admin-tool">
                    <span class="at-icon">💰</span>
                    <strong>Contributions</strong>
                    <small>Track payments</small>
                </a>
                <a href="<?= BASE_URL ?>/cooperative" class="admin-tool">
                    <span class="at-icon">📅</span>
                    <strong>Meetings</strong>
                    <small>Schedule & minutes</small>
                </a>
            </div>
        </section>

        <!-- Two column: production + market -->
        <section class="gov-grid-2">
            <div class="glass-card chart-card">
                <div class="card-header"><h3>📈 Production Trends</h3></div>
                <canvas id="coopProductionChart" height="140"></canvas>
            </div>

            <div class="glass-card">
                <div class="card-header"><h3>🏆 Top Members</h3></div>
                <div class="empty-state" style="padding:30px;">
                    <p>Member rankings by production</p>
                </div>
            </div>
        </section>

        <!-- Marketplace CTA -->
        <section class="glass-card coop-cta">
            <div>
                <h3>🛒 Sell Cooperative Products</h3>
                <p>List harvests on the marketplace as a cooperative to reach buyers nationwide.</p>
            </div>
            <a href="<?= BASE_URL ?>/marketplace/sell" class="btn-primary">List Products</a>
        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('coopProductionChart');
    if (!el) return;
    new Chart(el.getContext('2d'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Yield (kg)',
                data: [2200, 2800, 3400, 4100, 5200, 6100, 6800],
                borderColor: '#22C55E',
                backgroundColor: 'rgba(34,197,94,0.15)',
                fill: true,
                tension: 0.4,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#C8D5D1' } } },
            scales: {
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#C8D5D1' } },
                x: { grid: { display: false }, ticks: { color: '#C8D5D1' } }
            }
        }
    });
});
</script>

<style>
.coop-hero {
    padding: 30px !important;
    max-width: none;
    background: linear-gradient(135deg, rgba(22,163,74,0.15), rgba(132,204,22,0.05));
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.coop-hero h1 { margin: 0 0 4px; font-size: 24px; }
.coop-hero p { color: var(--text-sub); margin: 0; }
.coop-cta {
    padding: 25px !important;
    max-width: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    background: linear-gradient(135deg, rgba(132,204,22,0.12), rgba(34,197,94,0.05));
}
.coop-cta h3 { margin: 0 0 6px; font-size: 18px; }
.coop-cta p { color: var(--text-sub); margin: 0; font-size: 13px; }
</style>