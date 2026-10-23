<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header">
                <h3>🏡 <?= __('crop.my_farms') ?></h3>
                <a href="<?= BASE_URL ?>/crops/newFarm" class="btn-sm">+ Add Farm</a>
            </div>

            <?php if (empty($data['farms'])): ?>
                <div class="empty-state-card">
                    <span style="font-size:48px; display:block; margin-bottom:12px;">🌾</span>
                    <h3>No farms yet</h3>
                    <p>Add your first farm to start tracking crops and getting AI advice.</p>
                    <a href="<?= BASE_URL ?>/crops/newFarm" class="btn-primary" style="width:auto; padding:12px 28px;">+ Add Farm</a>
                </div>
            <?php else: ?>
                <div class="farms-grid">
                    <?php foreach ($data['farms'] as $f): ?>
                        <div class="glass-card farm-card">
                            <div class="farm-header">
                                <div class="farm-icon">🏡</div>
                                <div>
                                    <h4><?= e($f['name']) ?></h4>
                                    <small><?= e($f['district_name'] ?? 'No district set') ?></small>
                                </div>
                            </div>

                            <div class="farm-stats">
                                <div>
                                    <small>Size</small>
                                    <strong><?= number_format($f['size_ha'] ?? 0, 2) ?> ha</strong>
                                </div>
                                <div>
                                    <small>Soil</small>
                                    <strong><?= ucfirst($f['soil_type']) ?></strong>
                                </div>
                                <div>
                                    <small>Irrigation</small>
                                    <strong><?= ucfirst($f['irrigation_type']) ?></strong>
                                </div>
                            </div>

                            <div class="farm-footer">
                                <span class="badge green">
                                    🌱 <?= (int)$f['active_crops'] ?> active crops
                                </span>
                                <div style="display:flex; gap:8px;">
                                    <a href="<?= BASE_URL ?>/crops/plant?farm_id=<?= $f['id'] ?>" class="btn-sm">Plant</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<style>
.farms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
    margin-top: 20px;
}
.farm-card {
    padding: 20px !important;
    max-width: none;
    transition: 250ms;
}
.farm-card:hover {
    transform: translateY(-4px);
    border-color: var(--accent);
}
.farm-header {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--border);
}
.farm-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}
.farm-header h4 {
    margin: 0 0 4px;
    font-size: 16px;
}
.farm-header small {
    color: var(--text-sub);
    font-size: 11px;
}
.farm-stats {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 10px;
    margin-bottom: 16px;
}
.farm-stats div {
    text-align: center;
    padding: 10px;
    background: rgba(255,255,255,0.03);
    border-radius: 10px;
}
.farm-stats small {
    display: block;
    font-size: 10px;
    text-transform: uppercase;
    color: var(--text-sub);
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.farm-stats strong {
    font-size: 13px;
    color: var(--accent);
}
.farm-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 14px;
    border-top: 1px solid var(--border);
    flex-wrap: wrap;
    gap: 8px;
}
.empty-state-card {
    text-align: center;
    padding: 60px 20px;
}
.empty-state-card h3 {
    margin: 0 0 8px;
    font-size: 18px;
}
.empty-state-card p {
    color: var(--text-sub);
    margin: 0 0 20px;
    font-size: 13px;
}
</style>