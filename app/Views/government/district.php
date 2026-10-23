<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <a href="<?= BASE_URL ?>/government" class="btn-secondary" style="display:inline-block;margin-bottom:16px;">← Back to National</a>
            <h1>📍 <?= __('gov.district') ?> #<?= $data['district']['district_id'] ?></h1>
            <p>Year <?= $data['district']['year'] ?> · <?= $data['district']['farmer_count'] ?> farmers</p>
        </section>

        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">🌾</div>
                <div>
                    <p class="kpi-label">Total Yield</p>
                    <h3 class="kpi-value"><?= number_format($data['district']['total_yield']/1000, 1) ?> <small>t</small></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">💰</div>
                <div>
                    <p class="kpi-label">Revenue</p>
                    <h3 class="kpi-value"><?= number_format($data['district']['total_revenue']/1e6, 1) ?> <small>M RWF</small></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">👨‍🌾</div>
                <div>
                    <p class="kpi-label">Farmers</p>
                    <h3 class="kpi-value"><?= $data['district']['farmer_count'] ?></h3>
                </div>
            </div>
        </section>

        <section class="glass-card">
            <div class="card-header"><h3>🌱 Production by Crop</h3></div>
            <table class="data-table">
                <thead><tr><th>Crop</th><th>Yield (kg)</th><th>Revenue (RWF)</th><th>Farmers</th></tr></thead>
                <tbody>
                <?php foreach ($data['district']['crops'] as $c): ?>
                    <tr>
                        <td><?= $c['icon'] ?> <?= e($c['name']) ?></td>
                        <td><?= number_format($c['total_yield']) ?></td>
                        <td><?= number_format($c['revenue']) ?></td>
                        <td><?= $c['farmers'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <?php if (!empty($data['district']['alerts'])): ?>
            <section class="glass-card">
                <div class="card-header"><h3>🚨 Recent Alerts</h3></div>
                <?php foreach ($data['district']['alerts'] as $a): ?>
                    <div class="alert-row">
                        <span class="severity-pill <?= $a['severity'] ?>"><?= strtoupper($a['severity']) ?></span>
                        <div>
                            <strong><?= e($a['title']) ?></strong>
                            <small><?= date('M d, Y', strtotime($a['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</div>