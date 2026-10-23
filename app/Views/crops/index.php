<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- Quick Actions -->
        <section class="glass-card crop-actions">
            <div>
                <h2>🌱 <?= __('crop.title') ?></h2>
                <p><?= __('crop.subtitle') ?></p>
            </div>
            <div class="action-buttons-row">
                <a href="<?= BASE_URL ?>/crops/plant" class="btn-primary" style="width:auto;">🌾 <?= __('crop.plant') ?></a>
                <a href="<?= BASE_URL ?>/crops/farms" class="btn-secondary">🏡 <?= __('crop.my_farms') ?></a>
                <a href="<?= BASE_URL ?>/crops/detect" class="btn-secondary">🔬 <?= __('crop.detect') ?></a>
                <a href="<?= BASE_URL ?>/crops/agribot" class="btn-secondary">🤖 AgriBot</a>
            </div>
        </section>

        <!-- Active Crops -->
        <section class="glass-card">
            <div class="card-header"><h3>🌿 <?= __('crop.active_crops') ?></h3></div>
            <?php if (empty($data['active_crops'])): ?>
                <p class="empty-state"><?= __('crop.no_active') ?> <a href="<?= BASE_URL ?>/crops/plant"><?= __('crop.plant_now') ?></a></p>
            <?php else: ?>
                <div class="crop-tiles">
                    <?php foreach ($data['active_crops'] as $c): 
                        $progress = min(100, round((strtotime('now') - strtotime($c['planted_date'])) / max(1, strtotime($c['expected_harvest']) - strtotime($c['planted_date'])) * 100));
                    ?>
                        <a href="<?= BASE_URL ?>/crops/view/<?= $c['id'] ?>" class="crop-tile glass-card">
                            <div class="crop-icon"><?= $c['crop_icon'] ?></div>
                            <div>
                                <strong><?= e($c['crop_name']) ?></strong>
                                <small><?= e($c['farm_name']) ?></small>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width:<?= $progress ?>%"></div>
                                </div>
                                <span class="stage-pill <?= $c['growth_stage'] ?>"><?= ucfirst($c['growth_stage']) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Yield Stats Chart -->
        <section class="dashboard-grid">
            <div class="glass-card chart-card">
                <div class="card-header"><h3>📊 <?= __('crop.yield_history') ?></h3></div>
                <canvas id="yieldHistoryChart" height="140"></canvas>
            </div>

            <!-- Crop Catalog -->
            <div class="glass-card">
                <div class="card-header"><h3>🌾 <?= __('crop.catalog') ?></h3></div>
                <div class="catalog-grid">
                    <?php foreach ($data['crops'] as $c): ?>
                        <div class="catalog-item">
                            <span class="cat-icon"><?= $c['icon'] ?></span>
                            <strong><?= e($c['name']) ?></strong>
                            <small><?= $c['growing_days'] ?> <?= __('crop.days') ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
const yieldData = <?= json_encode($data['yield_stats']) ?>;
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('yieldHistoryChart');
    if (!el || !yieldData.length) return;
    new Chart(el.getContext('2d'), {
        type: 'bar',
        data: {
            labels: yieldData.map(y => y.icon + ' ' + y.name),
            datasets: [{
                label: 'Total Yield (kg)',
                data: yieldData.map(y => y.total_yield),
                backgroundColor: 'rgba(34, 197, 94, 0.7)',
                borderRadius: 6
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { grid: { display: false } } } }
    });
});
</script>