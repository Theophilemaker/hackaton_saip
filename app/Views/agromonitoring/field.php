<?php
$field   = $field   ?? [];
$indices = $indices ?? [];
$weather = $weather ?? [];

/* Prepare chart data (reversed for chronological order) */
$chart = array_reverse($indices);
$labels = array_map(fn($i) => date('M d', strtotime($i['sample_date'])), $chart);
$ndviValues = array_map(fn($i) => round((float)($i['ndvi'] ?? 0), 3), $chart);
$eviValues  = array_map(fn($i) => round((float)($i['evi']  ?? 0), 3), $chart);
$ndwiValues = array_map(fn($i) => round((float)($i['ndwi'] ?? 0), 3), $chart);

$current = $indices[0] ?? null;
?>
<div class="dashboard-layout agro-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- HERO -->
        <section class="glass-card hero-card">
            <div class="hero-left">
                <a href="<?= BASE_URL ?>/agromonitoring" class="back-link">← All fields</a>
                <h1>🌾 <?= e($field['name'] ?? 'Field') ?></h1>
                <p>
                    📍 <?= e($field['district'] ?? '—') ?>
                    <?php if (!empty($field['crop_name'])): ?>
                        · 🌱 <?= e($field['crop_name']) ?>
                    <?php endif; ?>
                    · 📐 <?= number_format((float)($field['area_ha'] ?? 0), 2) ?> ha
                </p>
            </div>
            <div class="hero-right">
                <div class="weather-chip">
                    <span class="wc-icon"><?= $weather['icon'] ?? '⛅' ?></span>
                    <div>
                        <strong><?= $weather['temp'] ?? '—' ?>°C</strong>
                        <small><?= e($weather['condition'] ?? '') ?></small>
                    </div>
                </div>
                <button type="button" class="btn-danger" onclick="deleteField(<?= (int)($field['id'] ?? 0) ?>)">
                    🗑️ Delete Field
                </button>
            </div>
        </section>

        <!-- CURRENT INDICES -->
        <?php if ($current): ?>
            <section class="kpi-strip">
                <div class="kpi-card kpi-ndvi">
                    <div class="kpi-icon">🌿</div>
                    <div>
                        <span class="kpi-label">NDVI</span>
                        <strong class="kpi-value"><?= number_format((float)$current['ndvi'], 3) ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-evi">
                    <div class="kpi-icon">🌱</div>
                    <div>
                        <span class="kpi-label">EVI</span>
                        <strong class="kpi-value"><?= $current['evi'] !== null ? number_format((float)$current['evi'], 3) : '—' ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-ndwi">
                    <div class="kpi-icon">💧</div>
                    <div>
                        <span class="kpi-label">NDWI</span>
                        <strong class="kpi-value"><?= $current['ndwi'] !== null ? number_format((float)$current['ndwi'], 3) : '—' ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-moisture">
                    <div class="kpi-icon">🌊</div>
                    <div>
                        <span class="kpi-label">Soil Moisture</span>
                        <strong class="kpi-value"><?= $current['soil_moisture'] !== null ? number_format((float)$current['soil_moisture'], 1) : '—' ?><span class="kpi-unit">%</span></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-cloud">
                    <div class="kpi-icon">☁️</div>
                    <div>
                        <span class="kpi-label">Cloud Cover</span>
                        <strong class="kpi-value"><?= $current['cloud_cover'] !== null ? (int)$current['cloud_cover'] : '—' ?><span class="kpi-unit">%</span></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-date">
                    <div class="kpi-icon">📅</div>
                    <div>
                        <span class="kpi-label">Last Sample</span>
                        <strong class="kpi-value" style="font-size:14px;">
                            <?= date('M d, Y', strtotime($current['sample_date'])) ?>
                        </strong>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- NDVI CHART -->
        <section class="glass-card">
            <div class="card-header">
                <div>
                    <h3>📈 Vegetation Index Trend</h3>
                    <p>NDVI · EVI · NDWI over the last 30 samples</p>
                </div>
            </div>
            <div class="chart-wrap">
                <canvas id="ndviChart" height="90"></canvas>
            </div>
        </section>

        <!-- SAMPLES TABLE -->
        <section class="glass-card">
            <div class="card-header">
                <div>
                    <h3>📋 Sample History</h3>
                    <p><?= count($indices) ?> satellite reading<?= count($indices) !== 1 ? 's' : '' ?></p>
                </div>
            </div>
            <?php if (empty($indices)): ?>
                <div class="empty-state">
                    <div class="empty-emoji">📡</div>
                    <h3>No satellite samples yet</h3>
                    <p>The first reading usually arrives within 24–48 hours.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>NDVI</th>
                                <th>EVI</th>
                                <th>NDWI</th>
                                <th>Soil Moisture</th>
                                <th>Cloud Cover</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($indices as $i): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($i['sample_date'])) ?></td>
                                    <td>
                                        <span class="value-pill <?= (float)$i['ndvi'] >= 0.6 ? 'ok' : ((float)$i['ndvi'] >= 0.3 ? 'warn' : 'danger') ?>">
                                            <?= number_format((float)$i['ndvi'], 3) ?>
                                        </span>
                                    </td>
                                    <td><?= $i['evi']  !== null ? number_format((float)$i['evi'],  3) : '—' ?></td>
                                    <td><?= $i['ndwi'] !== null ? number_format((float)$i['ndwi'], 3) : '—' ?></td>
                                    <td><?= $i['soil_moisture'] !== null ? number_format((float)$i['soil_moisture'], 1) . '%' : '—' ?></td>
                                    <td><?= $i['cloud_cover'] !== null ? (int)$i['cloud_cover'] . '%' : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    var labels = <?= json_encode($labels) ?>;
    var ndvi   = <?= json_encode($ndviValues) ?>;
    var evi    = <?= json_encode($eviValues) ?>;
    var ndwi   = <?= json_encode($ndwiValues) ?>;

    var ctx = document.getElementById('ndviChart');
    if (!ctx || !labels.length) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'NDVI',
                    data: ndvi,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.15)',
                    borderWidth: 2.5,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#10b981',
                },
                {
                    label: 'EVI',
                    data: evi,
                    borderColor: '#38bdf8',
                    backgroundColor: 'rgba(56, 189, 248, 0.05)',
                    borderWidth: 2,
                    tension: 0.35,
                    pointRadius: 2,
                },
                {
                    label: 'NDWI',
                    data: ndwi,
                    borderColor: '#a78bfa',
                    backgroundColor: 'rgba(167, 139, 250, 0.05)',
                    borderWidth: 2,
                    tension: 0.35,
                    pointRadius: 2,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#cbd5e1', font: { size: 12, weight: '600' } }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    titleColor: '#f1f5f9',
                    bodyColor: '#cbd5e1',
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: { color: '#94a3b8', font: { size: 11 } },
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: { color: '#94a3b8', font: { size: 11 } },
                    min: -0.2,
                    max: 1.0,
                }
            }
        }
    });

    window.deleteField = async function (id) {
        if (!id) return;
        if (!confirm('Delete this field and all its satellite data?')) return;
        try {
            var res = await fetch('<?= BASE_URL ?>/agromonitoring/delete/' + id, {
                method: 'POST',
                credentials: 'same-origin'
            });
            var data = await res.json();
            if (data.success) {
                window.location.href = '<?= BASE_URL ?>/agromonitoring';
            } else {
                alert(data.error || 'Delete failed');
            }
        } catch (e) { alert(e.message); }
    };
})();
</script>

<style>
html, body { margin: 0 !important; padding: 0 !important; overflow-x: hidden !important; }

.agro-page.dashboard-layout { display: flex !important; width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; }
.agro-page .main-content {
    flex: 1 !important;
    width: calc(100% - 260px) !important;
    padding: 24px 32px 60px !important;
    box-sizing: border-box !important;
}
.agro-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 22px !important;
    margin-bottom: 20px !important;
}

.hero-card {
    display: flex !important; justify-content: space-between !important;
    align-items: center !important; gap: 24px !important; flex-wrap: wrap !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08)) !important;
    padding: 26px 30px !important;
}
.hero-left { flex: 1 !important; min-width: 260px !important; }
.back-link {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    color: #94a3b8 !important; text-decoration: none !important;
    font-size: 12.5px !important; font-weight: 600 !important;
    margin-bottom: 12px !important;
}
.back-link:hover { color: #34d399 !important; }
.hero-card h1 { font-size: 26px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px !important; }
.hero-card p { color: #94a3b8 !important; font-size: 13.5px !important; margin: 0 !important; }

.hero-right { display: flex !important; gap: 12px !important; align-items: center !important; flex-wrap: wrap !important; }
.weather-chip {
    display: flex !important; align-items: center !important; gap: 10px !important;
    padding: 10px 16px !important;
    background: rgba(0, 0, 0, 0.3) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 14px !important;
}
.wc-icon { font-size: 26px !important; }
.weather-chip strong { display: block !important; font-size: 17px !important; color: #f1f5f9 !important; font-weight: 800 !important; }
.weather-chip small { font-size: 11px !important; color: #94a3b8 !important; }

.btn-danger {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    padding: 11px 20px !important;
    background: rgba(239, 68, 68, 0.12) !important;
    color: #f87171 !important;
    border: 1px solid rgba(239, 68, 68, 0.35) !important;
    border-radius: 10px !important;
    font-size: 13px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    transition: all 0.2s !important;
}
.btn-danger:hover { background: rgba(239, 68, 68, 0.25) !important; }

.kpi-strip {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) !important;
    gap: 12px !important; margin-bottom: 20px !important;
}
.kpi-card {
    display: flex !important; align-items: center !important; gap: 12px !important;
    padding: 16px 18px !important;
    background: rgba(30, 41, 59, 0.7) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 14px !important;
    position: relative !important; overflow: hidden !important;
}
.kpi-card::before {
    content: '' !important; position: absolute !important;
    top: 0 !important; left: 0 !important;
    width: 4px !important; height: 100% !important;
}
.kpi-ndvi::before    { background: linear-gradient(180deg, #10b981, #34d399) !important; }
.kpi-evi::before     { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.kpi-ndwi::before    { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.kpi-moisture::before{ background: linear-gradient(180deg, #38bdf8, #0ea5e9) !important; }
.kpi-cloud::before   { background: linear-gradient(180deg, #94a3b8, #64748b) !important; }
.kpi-date::before    { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.kpi-icon { font-size: 22px !important; flex-shrink: 0 !important; }
.kpi-label { display: block !important; font-size: 10.5px !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.6px !important; font-weight: 700 !important; margin-bottom: 3px !important; }
.kpi-value { display: block !important; font-size: 19px !important; color: #f1f5f9 !important; font-weight: 800 !important; }
.kpi-unit { font-size: 12px !important; color: #64748b !important; margin-left: 2px !important; }

.card-header {
    display: flex !important; justify-content: space-between !important;
    align-items: flex-start !important; gap: 12px !important;
    margin-bottom: 18px !important; padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    flex-wrap: wrap !important;
}
.card-header h3 { font-size: 16px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 4px !important; }
.card-header p  { font-size: 12.5px !important; color: #94a3b8 !important; margin: 0 !important; }

.chart-wrap {
    position: relative !important;
    height: 340px !important;
    width: 100% !important;
}

.table-wrap { overflow-x: auto !important; border-radius: 12px !important; border: 1px solid rgba(255, 255, 255, 0.05) !important; }
.data-table { width: 100% !important; border-collapse: collapse !important; font-size: 13.5px !important; }
.data-table thead th {
    background: #1e293b !important; color: #94a3b8 !important;
    font-weight: 800 !important; text-transform: uppercase !important;
    letter-spacing: 0.7px !important; font-size: 10.5px !important;
    padding: 14px 16px !important; text-align: left !important;
    border-bottom: 2px solid rgba(16, 185, 129, 0.2) !important;
    white-space: nowrap !important;
}
.data-table tbody td {
    padding: 12px 16px !important;
    color: #f1f5f9 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
}
.data-table tbody tr:hover td { background: rgba(16, 185, 129, 0.04) !important; }

.value-pill {
    padding: 3px 10px !important;
    border-radius: 999px !important;
    font-weight: 800 !important;
    font-size: 12px !important;
}
.value-pill.ok     { background: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }
.value-pill.warn   { background: rgba(251, 191, 36, 0.15) !important; color: #fbbf24 !important; }
.value-pill.danger { background: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important; }

.empty-state { text-align: center !important; padding: 60px 30px !important; }
.empty-emoji { font-size: 56px !important; margin-bottom: 14px !important; opacity: 0.5 !important; }
.empty-state h3 { font-size: 17px !important; color: #f1f5f9 !important; margin: 0 0 6px !important; font-weight: 800 !important; }
.empty-state p { color: #94a3b8 !important; font-size: 13.5px !important; margin: 0 !important; }

@media (max-width: 900px) {
    .agro-page .main-content {
        width: 100% !important; max-width: 100% !important;
        padding: 20px !important; padding-top: 72px !important;
    }
    .hero-card { flex-direction: column !important; align-items: flex-start !important; }
}
@media (max-width: 640px) {
    .kpi-strip { grid-template-columns: repeat(2, 1fr) !important; }
    .chart-wrap { height: 260px !important; }
}
</style>