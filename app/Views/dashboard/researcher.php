<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card researcher-hero">
            <div>
                <h1>📊 Researcher Portal</h1>
                <p>Explore NISR datasets · Run analyses · Download reports</p>
            </div>
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>/nisr" class="btn-primary">🔬 NISR Portal</a>
                <a href="<?= BASE_URL ?>/nisr/apiDocs" class="btn-secondary">🔑 API Access</a>
            </div>
        </section>

        <!-- KPI Cards -->
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">📁</div>
                <div>
                    <p class="kpi-label">Available Datasets</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['total'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">📊</div>
                <div>
                    <p class="kpi-label">Total Records</p>
                    <h3 class="kpi-value"><?= number_format(($data['stats']['total_rows'] ?? 0) / 1000, 1) ?>K</h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">📈</div>
                <div>
                    <p class="kpi-label">My Analyses</p>
                    <h3 class="kpi-value"><?= number_format($data['my_analyses'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#7C3AED,#C084FC)">🔑</div>
                <div>
                    <p class="kpi-label">API Tokens</p>
                    <h3 class="kpi-value"><?= number_format($data['api_tokens'] ?? 0) ?></h3>
                </div>
            </div>
        </section>

        <!-- Research Tools -->
        <section class="glass-card">
            <div class="card-header"><h3>🔬 Research Tools</h3></div>
            <div class="admin-tools-grid">
                <a href="<?= BASE_URL ?>/nisr/datasets" class="admin-tool">
                    <span class="at-icon">📁</span>
                    <strong>Dataset Registry</strong>
                    <small>Browse NISR datasets</small>
                </a>
                <a href="<?= BASE_URL ?>/nisr/explorer" class="admin-tool">
                    <span class="at-icon">🔍</span>
                    <strong>Statistical Explorer</strong>
                    <small>Pivot tables & cross-tabs</small>
                </a>
                <a href="<?= BASE_URL ?>/nisr/chartBuilder" class="admin-tool">
                    <span class="at-icon">📈</span>
                    <strong>Chart Builder</strong>
                    <small>Custom visualizations</small>
                </a>
                <a href="<?= BASE_URL ?>/nisr/compare" class="admin-tool">
                    <span class="at-icon">⚖️</span>
                    <strong>Compare Districts</strong>
                    <small>Year vs Year</small>
                </a>
                <a href="<?= BASE_URL ?>/nisr/timeseries" class="admin-tool">
                    <span class="at-icon">📉</span>
                    <strong>Time Series</strong>
                    <small>Trends & moving averages</small>
                </a>
                <a href="<?= BASE_URL ?>/nisr/publications" class="admin-tool">
                    <span class="at-icon">📰</span>
                    <strong>Publications</strong>
                    <small>NISR-style reports</small>
                </a>
                <a href="<?= BASE_URL ?>/nisr/apiDocs" class="admin-tool">
                    <span class="at-icon">🔑</span>
                    <strong>API Access</strong>
                    <small>Programmatic data access</small>
                </a>
                <a href="<?= BASE_URL ?>/nisr/uploadForm" class="admin-tool">
                    <span class="at-icon">📤</span>
                    <strong>Upload Dataset</strong>
                    <small>Add your own CSV</small>
                </a>
            </div>
        </section>

        <!-- Recent Datasets + Charts -->
        <section class="gov-grid-2">
            <div class="glass-card">
                <div class="card-header">
                    <h3>📁 Recent Datasets</h3>
                    <a href="<?= BASE_URL ?>/nisr/datasets" class="btn-sm">View All</a>
                </div>
                <?php if (empty($data['recent_datasets'])): ?>
                    <p class="empty-state">No datasets available.</p>
                <?php else: ?>
                    <?php foreach (array_slice($data['recent_datasets'], 0, 5) as $d): ?>
                        <a href="<?= BASE_URL ?>/nisr/dataset/<?= $d['id'] ?>" class="dataset-item-mini">
                            <div>
                                <strong><?= e($d['name']) ?></strong>
                                <small><?= strtoupper($d['survey_type']) ?> · <?= $d['year'] ?> · <?= number_format($d['row_count']) ?> rows</small>
                            </div>
                            <span class="badge green">Open</span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="glass-card chart-card">
                <div class="card-header"><h3>📊 National Production Trends</h3></div>
                <canvas id="researchChart" height="140"></canvas>
            </div>
        </section>

        <!-- CTA -->
        <section class="glass-card research-cta">
            <div>
                <h3>📖 Want to publish?</h3>
                <p>Generate a NISR-style statistical report from any dataset in one click.</p>
            </div>
            <a href="<?= BASE_URL ?>/nisr/publications" class="btn-primary">Create Publication</a>
        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('researchChart');
    if (!el) return;
    new Chart(el.getContext('2d'), {
        type: 'line',
        data: {
            labels: ['2019', '2020', '2021', '2022', '2023', '2024'],
            datasets: [
                {
                    label: 'Maize (t)',
                    data: [820, 890, 1020, 1180, 1350, 1420],
                    borderColor: '#22C55E',
                    tension: 0.4,
                    borderWidth: 2
                },
                {
                    label: 'Beans (t)',
                    data: [420, 480, 560, 620, 680, 750],
                    borderColor: '#84CC16',
                    tension: 0.4,
                    borderWidth: 2,
                    borderDash: [5, 5]
                }
            ]
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
.researcher-hero {
    padding: 30px !important;
    max-width: none;
    background: linear-gradient(135deg, rgba(124,58,237,0.15), rgba(34,197,94,0.08));
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.researcher-hero h1 { margin: 0 0 4px; font-size: 24px; }
.researcher-hero p { color: var(--text-sub); margin: 0; font-size: 13px; }

.dataset-item-mini {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(255,255,255,0.03);
    border-radius: 10px;
    margin-bottom: 8px;
    text-decoration: none;
    color: white;
    transition: 200ms;
}
.dataset-item-mini:hover {
    background: rgba(34,197,94,0.08);
    transform: translateX(4px);
}
.dataset-item-mini strong { display: block; font-size: 13px; margin-bottom: 4px; }
.dataset-item-mini small { color: var(--text-sub); font-size: 11px; }

.research-cta {
    padding: 25px !important;
    max-width: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    background: linear-gradient(135deg, rgba(124,58,237,0.1), rgba(34,197,94,0.05));
}
.research-cta h3 { margin: 0 0 6px; font-size: 18px; }
.research-cta p { color: var(--text-sub); margin: 0; font-size: 13px; }
</style>