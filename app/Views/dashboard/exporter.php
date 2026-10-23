<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card exporter-hero">
            <div>
                <h1>🚢 Exporter Dashboard</h1>
                <p>Source premium Rwandan agricultural products for export</p>
            </div>
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>/marketplace" class="btn-primary">🔍 Find Suppliers</a>
                <a href="<?= BASE_URL ?>/marketplace/exports" class="btn-secondary">+ Post Request</a>
            </div>
        </section>

        <!-- KPIs -->
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">💰</div>
                <div>
                    <p class="kpi-label">Active Orders</p>
                    <h3 class="kpi-value"><?= number_format($data['active_orders'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">🚢</div>
                <div>
                    <p class="kpi-label">In Transit</p>
                    <h3 class="kpi-value"><?= number_format($data['in_transit'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">📦</div>
                <div>
                    <p class="kpi-label">Delivered (MTD)</p>
                    <h3 class="kpi-value"><?= number_format($data['delivered_mtd'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#7C3AED,#C084FC)">✅</div>
                <div>
                    <p class="kpi-label">Verified Suppliers</p>
                    <h3 class="kpi-value"><?= number_format($data['verified_suppliers'] ?? 0) ?></h3>
                </div>
            </div>
        </section>

        <!-- Featured categories -->
        <section class="glass-card">
            <div class="card-header"><h3>🌾 Source by Category</h3></div>
            <div class="export-categories">
                <a href="<?= BASE_URL ?>/marketplace?category=6" class="export-cat">
                    <span class="ec-icon">☕</span>
                    <strong>Coffee</strong>
                    <small>Grade A Arabica</small>
                </a>
                <a href="<?= BASE_URL ?>/marketplace?category=6" class="export-cat">
                    <span class="ec-icon">🍵</span>
                    <strong>Tea</strong>
                    <small>Premium black tea</small>
                </a>
                <a href="<?= BASE_URL ?>/marketplace?category=1" class="export-cat">
                    <span class="ec-icon">🌽</span>
                    <strong>Grains</strong>
                    <small>Maize, beans, rice</small>
                </a>
                <a href="<?= BASE_URL ?>/marketplace?category=2" class="export-cat">
                    <span class="ec-icon">🥬</span>
                    <strong>Vegetables</strong>
                    <small>Fresh produce</small>
                </a>
                <a href="<?= BASE_URL ?>/marketplace?category=3" class="export-cat">
                    <span class="ec-icon">🍎</span>
                    <strong>Fruits</strong>
                    <small>Horticulture</small>
                </a>
                <a href="<?= BASE_URL ?>/marketplace?category=7" class="export-cat">
                    <span class="ec-icon">🐄</span>
                    <strong>Livestock</strong>
                    <small>Animal products</small>
                </a>
            </div>
        </section>

        <!-- Recent purchase orders -->
        <section class="gov-grid-2">
            <div class="glass-card">
                <div class="card-header">
                    <h3>📦 Recent Purchase Orders</h3>
                    <a href="<?= BASE_URL ?>/marketplace/myOrders" class="btn-sm">View All</a>
                </div>
                <?php if (empty($data['recent_orders'])): ?>
                    <p class="empty-state">No purchase orders yet.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead><tr><th>Order #</th><th>Product</th><th>Qty</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach (array_slice($data['recent_orders'], 0, 6) as $o): ?>
                            <tr>
                                <td><?= e($o['order_number']) ?></td>
                                <td><?= e($o['product_title'] ?? '') ?></td>
                                <td><?= number_format($o['quantity']) ?></td>
                                <td><span class="status-pill <?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="glass-card">
                <div class="card-header">
                    <h3>📋 My Export Requests</h3>
                    <a href="<?= BASE_URL ?>/marketplace/exports" class="btn-sm">Manage</a>
                </div>
                <div class="empty-state" style="padding:30px;">
                    <p>Post buying requests to attract suppliers.</p>
                    <a href="<?= BASE_URL ?>/marketplace/exports" class="btn-sm">Post Request</a>
                </div>
            </div>
        </section>

        <!-- Export volume chart -->
        <section class="glass-card chart-card">
            <div class="card-header"><h3>📊 Export Volume (Last 6 months)</h3></div>
            <canvas id="exportVolumeChart" height="120"></canvas>
        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('exportVolumeChart');
    if (!el) return;
    new Chart(el.getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'],
            datasets: [{
                label: 'Volume (MT)',
                data: [450, 620, 580, 720, 890, 750],
                backgroundColor: 'rgba(34,197,94,0.7)',
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#C8D5D1' } } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#C8D5D1' } },
                x: { grid: { display: false }, ticks: { color: '#C8D5D1' } }
            }
        }
    });
});
</script>

<style>
.exporter-hero {
    padding: 30px !important;
    max-width: none;
    background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(239,68,68,0.05));
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.exporter-hero h1 { margin: 0 0 4px; font-size: 24px; }
.exporter-hero p { color: var(--text-sub); margin: 0; }
.export-categories {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 14px;
}
.export-cat {
    display: block;
    padding: 20px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 14px;
    text-decoration: none;
    color: white;
    transition: 250ms;
    text-align: center;
}
.export-cat:hover {
    background: rgba(245,158,11,0.1);
    border-color: #F59E0B;
    transform: translateY(-4px);
}
.ec-icon { font-size: 32px; display: block; margin-bottom: 8px; }
.export-cat strong { display: block; font-size: 14px; margin-bottom: 4px; }
.export-cat small { color: var(--text-sub); font-size: 11px; }
</style>