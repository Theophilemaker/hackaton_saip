<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header">
                <h3>🌱 Plant a New Crop</h3>
                <a href="<?= BASE_URL ?>/crops" class="btn-secondary">← Back</a>
            </div>

            <?php if (empty($data['farms'])): ?>
                <div class="empty-state-card">
                    <span style="font-size:48px;">🏡</span>
                    <h3>No farms yet</h3>
                    <p>You need at least one farm before planting crops.</p>
                    <a href="<?= BASE_URL ?>/crops/newFarm" class="btn-primary" style="width:auto; padding:12px 28px;">+ Add Farm</a>
                </div>
            <?php else: ?>
                <?php if (!empty($data['error'])): ?>
                    <div class="alert error"><?= e($data['error']) ?></div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/crops/plant" method="POST" class="alert-form">
                    <?= \App\Core\Security::csrfField() ?>

                    <div class="form-group">
                        <label>Farm *</label>
                        <select name="farm_id" required>
                            <option value="">Select farm</option>
                            <?php foreach ($data['farms'] as $f): ?>
                                <option value="<?= $f['id'] ?>"
                                    <?= ((int)($_GET['farm_id'] ?? $_POST['farm_id'] ?? 0) === (int)$f['id']) ? 'selected' : '' ?>>
                                    🏡 <?= e($f['name']) ?> (<?= number_format($f['size_ha'] ?? 0, 2) ?> ha)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Crop *</label>
                        <select name="crop_id" required onchange="showCropInfo(this.value)">
                            <option value="">Select crop</option>
                            <?php foreach ($data['crops'] as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                    data-days="<?= $c['growing_days'] ?>"
                                    data-temp-min="<?= $c['optimal_temp_min'] ?>"
                                    data-temp-max="<?= $c['optimal_temp_max'] ?>"
                                    data-rain="<?= $c['optimal_rainfall_mm'] ?>"
                                    <?= ((int)($_POST['crop_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                                    <?= $c['icon'] ?> <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Crop Info Panel -->
                    <div id="cropInfo" class="crop-info-box" style="display:none;">
                        <h4>📊 Crop Requirements</h4>
                        <div class="crop-info-grid">
                            <div><small>Growing days</small><strong id="ciDays">—</strong></div>
                            <div><small>Temperature</small><strong id="ciTemp">—</strong></div>
                            <div><small>Rainfall</small><strong id="ciRain">—</strong></div>
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Season</label>
                            <select name="season">
                                <option value="A">Season A (Sept–Jan)</option>
                                <option value="B">Season B (Feb–Jun)</option>
                                <option value="C">Season C (Jul–Sept)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Year</label>
                            <input type="number" name="year" min="2020" max="2050"
                                   value="<?= e($_POST['year'] ?? date('Y')) ?>">
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Planting Date *</label>
                            <input type="date" name="planted_date" required
                                   value="<?= e($_POST['planted_date'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="form-group">
                            <label>Area Planted (ha)</label>
                            <input type="number" name="area_ha" step="0.01" min="0.01"
                                   placeholder="1.5"
                                   value="<?= e($_POST['area_ha'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Quantity Planted (kg)</label>
                        <input type="number" name="quantity_planted_kg" step="0.01" min="0"
                               placeholder="25"
                               value="<?= e($_POST['quantity_planted_kg'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" rows="3" maxlength="500"
                                  placeholder="Additional notes about this planting..."
                                  ><?= e($_POST['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" class="btn-primary">🌱 Plant Crop</button>
                        <a href="<?= BASE_URL ?>/crops" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </main>
</div>

<script>
function showCropInfo(cropId) {
    const select = document.querySelector('select[name="crop_id"]');
    const opt = select.options[select.selectedIndex];
    const box = document.getElementById('cropInfo');

    if (!cropId) { box.style.display = 'none'; return; }

    document.getElementById('ciDays').textContent  = opt.dataset.days + ' days';
    document.getElementById('ciTemp').textContent  = opt.dataset.tempMin + '–' + opt.dataset.tempMax + '°C';
    document.getElementById('ciRain').textContent  = opt.dataset.rain + ' mm';
    box.style.display = 'block';
}

// Auto-show on page load if crop is preselected
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.querySelector('select[name="crop_id"]');
    if (sel && sel.value) showCropInfo(sel.value);
});
</script>

<style>
.crop-info-box {
    padding: 16px;
    background: rgba(34,197,94,0.08);
    border: 1px solid rgba(34,197,94,0.25);
    border-radius: 12px;
    margin-bottom: 20px;
    animation: alertIn 0.3s ease-out;
}
.crop-info-box h4 {
    margin: 0 0 12px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--accent);
}
.crop-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
}
.crop-info-grid div { text-align: center; }
.crop-info-grid small {
    display: block;
    font-size: 10px;
    color: var(--text-sub);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.crop-info-grid strong { font-size: 14px; color: white; }
</style>