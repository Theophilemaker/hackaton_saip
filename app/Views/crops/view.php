<?php
// Compute days to harvest
$expectedTs = strtotime($data['farm_crop']['expected_harvest']);
$daysLeft = max(0, round(($expectedTs - time()) / 86400));

// Compute progress
$plantedTs = strtotime($data['farm_crop']['planted_date']);
$totalDuration = max(1, $expectedTs - $plantedTs);
$progress = min(100, max(0, round(((time() - $plantedTs) / $totalDuration) * 100)));

// Stage order
$stages = ['planting','germination','vegetative','flowering','fruiting','maturity','harvested'];
$currentStageIdx = array_search($data['farm_crop']['growth_stage'], $stages);
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ============ HERO ============ -->
        <section class="glass-card crop-hero">
            <div class="crop-hero-left">
                <div class="crop-icon-large"><?= $data['farm_crop']['crop_icon'] ?></div>
                <div>
                    <h1><?= e($data['farm_crop']['crop_name']) ?></h1>
                    <p>
                        <?= e($data['farm_crop']['farm_name']) ?>
                        · <em><?= e($data['farm_crop']['scientific_name']) ?></em>
                    </p>
                    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-top:8px;">
                        <span class="stage-pill <?= $data['farm_crop']['growth_stage'] ?>">
                            <?= ucfirst($data['farm_crop']['growth_stage']) ?>
                        </span>
                        <span class="badge green">Season <?= e($data['farm_crop']['season']) ?> <?= $data['farm_crop']['year'] ?></span>
                    </div>
                </div>
            </div>
            <div class="crop-hero-right">
                <label style="display:block; font-size:11px; color:var(--text-sub); margin-bottom:6px;">
                    Update growth stage:
                </label>
                <select id="stageSelect" onchange="changeStage(<?= $data['farm_crop']['id'] ?>)">
                    <?php foreach ($stages as $s): ?>
                        <option value="<?= $s ?>" <?= $data['farm_crop']['growth_stage'] === $s ? 'selected' : '' ?>>
                            <?= ucfirst($s) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </section>

        <!-- ============ PROGRESS BAR ============ -->
        <section class="glass-card">
            <div class="growth-progress">
                <div class="gp-header">
                    <span>📅 Planted: <strong><?= date('M d, Y', $plantedTs) ?></strong></span>
                    <span>🎯 Harvest: <strong><?= date('M d, Y', $expectedTs) ?></strong></span>
                    <span>⏳ <strong><?= $daysLeft ?> days</strong> remaining</span>
                </div>
                <div class="gp-track">
                    <div class="gp-fill" style="width:<?= $progress ?>%"></div>
                    <?php foreach ($stages as $i => $s): 
                        $stagePct = round((($i + 0.5) / count($stages)) * 100);
                    ?>
                        <div class="gp-dot <?= $i <= $currentStageIdx ? 'done' : '' ?>" 
                             style="left:<?= $stagePct ?>%"
                             title="<?= ucfirst($s) ?>"></div>
                    <?php endforeach; ?>
                </div>
                <div class="gp-stages">
                    <?php foreach ($stages as $s): ?>
                        <span><?= ucfirst($s) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- ============ KPI CARDS ============ -->
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">🌾</div>
                <div>
                    <p class="kpi-label"><?= __('crop.predicted_yield') ?></p>
                    <h3 class="kpi-value"><?= number_format($data['prediction']['predicted_yield_kg']) ?> <small>kg</small></h3>
                    <span class="kpi-trend">🤖 <?= round($data['prediction']['confidence']*100) ?>% confidence</span>
                </div>
            </div>

            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">📅</div>
                <div>
                    <p class="kpi-label"><?= __('crop.days_to_harvest') ?></p>
                    <h3 class="kpi-value"><?= $daysLeft ?> <small>days</small></h3>
                </div>
            </div>

            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">📐</div>
                <div>
                    <p class="kpi-label"><?= __('crop.area') ?></p>
                    <h3 class="kpi-value"><?= number_format($data['farm_crop']['area_ha'] ?? 0, 2) ?> <small>ha</small></h3>
                </div>
            </div>

            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#84CC16,#16A34A)">💚</div>
                <div>
                    <p class="kpi-label"><?= __('crop.health') ?></p>
                    <h3 class="kpi-value" style="text-transform:capitalize;"><?= e($data['farm_crop']['health_status']) ?></h3>
                </div>
            </div>
        </section>

        <!-- ============ FERTILIZER ============ -->
        <section class="glass-card">
            <div class="card-header"><h3>🧪 <?= __('crop.fertilizer_ai') ?></h3></div>
            <?php if (empty($data['fertilizer'])): ?>
                <p class="empty-state">No fertilizer recommendations yet.</p>
            <?php else: ?>
                <div class="fertilizer-grid">
                    <?php foreach ($data['fertilizer'] as $f): ?>
                        <div class="fertilizer-item">
                            <div class="fert-icon">🧪</div>
                            <div>
                                <strong><?= e($f['type']) ?></strong>
                                <p><?= number_format($f['qty']) ?> kg · <?= e($f['method']) ?></p>
                                <small><?= e($f['timing']) ?> — <?= e($f['reason']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- ============ GROWTH TIMELINE ============ -->
        <section class="glass-card">
            <div class="card-header">
                <h3>📈 <?= __('crop.growth_timeline') ?></h3>
                <button onclick="document.getElementById('addLogModal').style.display='flex'" class="btn-sm">+ Add Log</button>
            </div>
            <?php if (empty($data['growth_logs'])): ?>
                <p class="empty-state">No timeline entries yet.</p>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($data['growth_logs'] as $log): ?>
                        <div class="timeline-item done">
                            <span class="dot"></span>
                            <div>
                                <strong><?= ucfirst($log['stage']) ?></strong>
                                <p><?= e($log['note']) ?></p>
                                <small><?= date('M d, Y H:i', strtotime($log['recorded_at'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- ============ PRICE FORECAST ============ -->
        <section class="glass-card chart-card">
            <div class="card-header"><h3>💰 <?= __('crop.price_forecast') ?></h3></div>
            <?php if (empty($data['price_forecast'])): ?>
                <p class="empty-state">No price forecast available.</p>
            <?php else: ?>
                <canvas id="priceForecastChart" height="120"></canvas>
            <?php endif; ?>
        </section>

        <!-- ============ ACTIONS ============ -->
        <section class="glass-card">
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>/crops/detect?farm_crop_id=<?= $data['farm_crop']['id'] ?>" class="btn-primary">
                    🔬 Check for Disease
                </a>
                <a href="<?= BASE_URL ?>/crops/detectPest" class="btn-secondary">
                    🐛 Check for Pests
                </a>
                <a href="<?= BASE_URL ?>/marketplace/sell" class="btn-secondary">
                    🛒 Sell This Harvest
                </a>
                <a href="<?= BASE_URL ?>/crops" class="btn-secondary">
                    ← Back to Crops
                </a>
            </div>
        </section>
    </main>
</div>

<!-- ============ ADD LOG MODAL ============ -->
<div id="addLogModal" class="modal-overlay" style="display:none;">
    <div class="glass-card modal">
        <h3>📝 Add Growth Log Entry</h3>
        <form method="POST" action="<?= BASE_URL ?>/crops/updateStage/<?= $data['farm_crop']['id'] ?>">
            <?= \App\Core\Security::csrfField() ?>
            <div class="form-group">
                <label>Stage</label>
                <select name="stage" required>
                    <?php foreach ($stages as $s): ?>
                        <option value="<?= $s ?>" <?= $data['farm_crop']['growth_stage'] === $s ? 'selected' : '' ?>>
                            <?= ucfirst($s) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Note (optional)</label>
                <textarea name="note" rows="3" placeholder="What did you observe?"></textarea>
            </div>
            <div class="action-buttons">
                <button type="submit" class="btn-primary">Save</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('addLogModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* ============ PRICE CHART ============ */
<?php if (!empty($data['price_forecast'])): ?>
const pfData = <?= json_encode($data['price_forecast']) ?>;
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('priceForecastChart');
    if (!el) return;
    new Chart(el.getContext('2d'), {
        type: 'line',
        data: {
            labels: pfData.map(p => p.forecast_date),
            datasets: [
                {
                    label: 'Upper',
                    data: pfData.map(p => p.upper_bound),
                    borderColor: 'rgba(132, 204, 22, 0.4)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false
                },
                {
                    label: 'Predicted (RWF)',
                    data: pfData.map(p => p.predicted_price),
                    borderColor: '#22C55E',
                    backgroundColor: 'rgba(34,197,94,0.15)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
                },
                {
                    label: 'Lower',
                    data: pfData.map(p => p.lower_bound),
                    borderColor: 'rgba(132, 204, 22, 0.4)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#C8D5D1', boxWidth: 12 } }
            },
            scales: {
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#C8D5D1' } },
                x: { grid: { display: false }, ticks: { color: '#C8D5D1' } }
            }
        }
    });
});
<?php endif; ?>

/* ============ STAGE CHANGE ============ */
function changeStage(id) {
    const stage = document.getElementById('stageSelect').value;
    if (!confirm('Change stage to "' + stage + '"?')) return;

    fetch(`<?= BASE_URL ?>/crops/updateStage/${id}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `stage=${stage}`
    })
    .then(r => r.json())
    .then(() => window.location.reload());
}
</script>

<style>
/* Hero */
.crop-hero {
    padding: 25px !important;
    max-width: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}
.crop-hero-left { display: flex; gap: 20px; align-items: center; }
.crop-icon-large {
    width: 80px; height: 80px;
    border-radius: 20px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex; align-items: center; justify-content: center;
    font-size: 42px;
    flex-shrink: 0;
}
.crop-hero h1 { margin: 0; font-size: 24px; }
.crop-hero p { color: var(--text-sub); margin: 4px 0 0; font-size: 13px; }
.crop-hero-right select {
    padding: 10px 16px;
    background: rgba(0,0,0,0.3);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: white;
    font-family: inherit;
    cursor: pointer;
}

/* Progress */
.growth-progress { padding: 10px 0; }
.gp-header {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: var(--text-sub);
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}
.gp-header strong { color: var(--accent); }
.gp-track {
    position: relative;
    height: 10px;
    background: rgba(255,255,255,0.08);
    border-radius: 5px;
    margin-bottom: 12px;
}
.gp-fill {
    height: 100%;
    background: linear-gradient(90deg, #16A34A, #84CC16);
    border-radius: 5px;
    transition: width 500ms;
}
.gp-dot {
    position: absolute;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    border: 2px solid #081C15;
    transition: 300ms;
}
.gp-dot.done {
    background: #84CC16;
    box-shadow: 0 0 10px rgba(132,204,22,0.7);
}
.gp-stages {
    display: flex;
    justify-content: space-between;
    font-size: 10px;
    color: var(--text-sub);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 24px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: rgba(132,204,22,0.2);
}
.timeline-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 12px 0;
    position: relative;
}
.timeline-item .dot {
    position: absolute;
    left: -20px;
    top: 18px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--accent);
    box-shadow: 0 0 10px var(--accent);
}
.timeline-item strong { display: block; font-size: 13px; }
.timeline-item p { margin: 4px 0; color: var(--text-sub); font-size: 12px; }
.timeline-item small { color: var(--text-sub); font-size: 11px; opacity: 0.7; }

/* Fertilizer */
.fertilizer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 12px;
}
.fertilizer-item {
    display: flex;
    gap: 12px;
    padding: 14px;
    background: rgba(132,204,22,0.06);
    border: 1px solid rgba(132,204,22,0.2);
    border-radius: 12px;
}
.fert-icon { font-size: 24px; flex-shrink: 0; }
.fertilizer-item strong { display: block; font-size: 14px; margin-bottom: 4px; }
.fertilizer-item p { margin: 0; color: var(--text-sub); font-size: 12px; }
.fertilizer-item small { color: var(--text-sub); font-size: 11px; display: block; margin-top: 4px; }

/* KPI */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}
.kpi-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px !important;
    max-width: none;
}
.kpi-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}
.kpi-label {
    font-size: 11px;
    text-transform: uppercase;
    color: var(--text-sub);
    letter-spacing: 0.5px;
    margin: 0 0 4px;
}
.kpi-value { font-size: 22px; font-weight: 800; margin: 0; }
.kpi-value small { font-size: 12px; color: var(--text-sub); font-weight: 500; }
.kpi-trend {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    color: var(--accent);
    margin-top: 4px;
}

@media (max-width: 700px) {
    .crop-hero { flex-direction: column; align-items: flex-start; }
    .crop-icon-large { width: 60px; height: 60px; font-size: 30px; }
    .gp-header { flex-direction: column; }
    .gp-stages { font-size: 8px; }
}
</style>