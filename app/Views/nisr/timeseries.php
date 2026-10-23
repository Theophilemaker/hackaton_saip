<div class="dashboard-layout timeseries-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ============ TIME SERIES FORM ============ -->
        <section class="glass-card ts-form-card">
            <div class="card-header">
                <div>
                    <h3>📉 Time Series Analyzer</h3>
                    <p class="ts-subtitle">
                        Track a numeric metric over a date field and detect trends
                    </p>
                </div>
                <span class="badge blue">Trend Analysis</span>
            </div>

            <form method="GET" id="tsForm">
                <div class="ts-controls">
                    <div class="form-group">
                        <label>Dataset</label>
                        <select name="dataset" onchange="window.location='<?= BASE_URL ?>/nisr/timeseries/'+this.value">
                            <?php foreach ($data['datasets'] as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($data['dataset'] ?? null) && $data['dataset']['id'] == $d['id'] ? 'selected' : '' ?>>
                                    <?= e($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Date Field</label>
                        <select name="date">
                            <option value="">— Select —</option>
                            <?php foreach ($data['schema'] as $c): ?>
                                <option value="<?= e($c['name']) ?>" <?= ($data['date_field'] ?? '') === $c['name'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Metric</label>
                        <select name="metric">
                            <option value="">— Select —</option>
                            <?php foreach ($data['schema'] as $c): ?>
                                <?php if (($c['type'] ?? '') === 'number'): ?>
                                    <option value="<?= e($c['name']) ?>" <?= ($data['metric'] ?? '') === $c['name'] ? 'selected' : '' ?>>
                                        <?= e($c['name']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Aggregation</label>
                        <select name="agg">
                            <?php foreach (['sum','avg','count'] as $a): ?>
                                <option value="<?= $a ?>" <?= ($data['agg'] ?? 'sum') === $a ? 'selected' : '' ?>>
                                    <?= ucfirst($a) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Moving Average (periods)</label>
                        <input type="number" name="smooth" 
                               value="<?= (int)($data['smooth'] ?? 0) ?>" 
                               min="0" max="12"
                               placeholder="0">
                    </div>

                    <div class="form-group run-group">
                        <button class="btn-run" id="analyzeBtn">📈 Analyze</button>
                    </div>
                </div>
            </form>
        </section>

        <!-- ============ EMPTY STATE ============ -->
        <?php if (!$data['series'] || empty($data['series']['points'])): ?>
            <section class="glass-card empty-ts">
                <div class="empty-icon">📉</div>
                <h3>No Time Series Generated Yet</h3>
                <p>
                    Select a <strong>Date Field</strong>, a numeric <strong>Metric</strong>, 
                    and optionally a <strong>Moving Average</strong> period, then click 
                    <strong>Analyze</strong> to see the trend.
                </p>
            </section>
        <?php else: 
            // Compute summary stats
            $points = $data['series']['points'];
            $values = array_map(fn($p) => $p['y'], $points);
            $totalPoints = count($points);
            $peak = !empty($values) ? max($values) : 0;
            $min  = !empty($values) ? min($values) : 0;
            $avg  = !empty($values) ? array_sum($values) / count($values) : 0;
            
            // Determine trend direction: compare first half vs second half
            $half = (int)floor(count($values) / 2);
            $firstHalfAvg = $half > 0 ? array_sum(array_slice($values, 0, $half)) / $half : 0;
            $secondHalfAvg = $half > 0 ? array_sum(array_slice($values, $half)) / (count($values) - $half) : 0;
            $trendPct = $firstHalfAvg > 0 ? (($secondHalfAvg - $firstHalfAvg) / $firstHalfAvg) * 100 : 0;
            $trendUp = $trendPct >= 0;
        ?>

            <!-- ============ SUMMARY KPI CARDS ============ -->
            <section class="kpi-grid">
                <div class="kpi-card points">
                    <div class="kpi-icon">📍</div>
                    <div class="kpi-content">
                        <p class="kpi-label">Data Points</p>
                        <h3 class="kpi-value"><?= number_format($totalPoints) ?></h3>
                    </div>
                </div>

                <div class="kpi-card peak">
                    <div class="kpi-icon">🔝</div>
                    <div class="kpi-content">
                        <p class="kpi-label">Peak Value</p>
                        <h3 class="kpi-value"><?= number_format($peak, 2) ?></h3>
                    </div>
                </div>

                <div class="kpi-card avg">
                    <div class="kpi-icon">📊</div>
                    <div class="kpi-content">
                        <p class="kpi-label">Average</p>
                        <h3 class="kpi-value"><?= number_format($avg, 2) ?></h3>
                    </div>
                </div>

                <div class="kpi-card trend <?= $trendUp ? 'up' : 'down' ?>">
                    <div class="kpi-icon"><?= $trendUp ? '📈' : '📉' ?></div>
                    <div class="kpi-content">
                        <p class="kpi-label">Trend</p>
                        <h3 class="kpi-value <?= $trendUp ? 'positive' : 'negative' ?>">
                            <?= $trendUp ? '+' : '' ?><?= number_format($trendPct, 1) ?>%
                        </h3>
                    </div>
                </div>
            </section>

            <!-- ============ CHART ============ -->
            <section class="glass-card ts-chart-card">
                <div class="card-header">
                    <div>
                        <h3>📈 Trend Chart</h3>
                        <p class="ts-chart-subtitle">
                            <strong><?= e($data['metric']) ?></strong> over 
                            <strong><?= e($data['date_field']) ?></strong> 
                            (<?= strtoupper($data['agg']) ?>)
                            <?php if (($data['smooth'] ?? 0) > 0): ?>
                                · Moving Avg: <strong><?= (int)$data['smooth'] ?></strong> periods
                            <?php endif; ?>
                        </p>
                    </div>
                    <span class="badge <?= $trendUp ? 'green' : 'red' ?>">
                        <?= $trendUp ? '▲ Trending Up' : '▼ Trending Down' ?>
                    </span>
                </div>

                <div class="chart-wrapper">
                    <canvas id="tsChart"></canvas>
                </div>
            </section>
        <?php endif; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if ($data['series'] && !empty($data['series']['points'])): ?>
<script>
const points = <?= json_encode($data['series']['points']) ?>;
const smoothed = <?= json_encode($data['series']['smoothed'] ?? []) ?>;

// Build gradient fill for the main line
const canvas = document.getElementById('tsChart');
const ctx = canvas.getContext('2d');
const gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(34, 197, 94, 0.35)');
gradient.addColorStop(1, 'rgba(34, 197, 94, 0.02)');

const datasets = [{
    label: '<?= e($data['metric']) ?>',
    data: points.map(p => p.y),
    borderColor: '#22C55E',
    backgroundColor: gradient,
    fill: true,
    tension: 0.35,
    borderWidth: 3,
    pointRadius: 3,
    pointHoverRadius: 7,
    pointBackgroundColor: '#22C55E',
    pointBorderColor: '#0b1120',
    pointBorderWidth: 2,
}];

if (smoothed && smoothed.length) {
    datasets.push({
        label: 'Moving Average',
        data: smoothed,
        borderColor: '#F59E0B',
        backgroundColor: 'transparent',
        tension: 0.4,
        borderWidth: 2,
        borderDash: [6, 4],
        pointRadius: 0,
        pointHoverRadius: 5,
        pointBackgroundColor: '#F59E0B',
    });
}

new Chart(canvas, {
    type: 'line',
    data: {
        labels: points.map(p => p.x),
        datasets: datasets
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 900,
            easing: 'easeOutQuart'
        },
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                labels: {
                    color: '#cbd5e1',
                    font: { size: 13, weight: '600' },
                    padding: 16,
                    usePointStyle: true,
                }
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                titleColor: '#f1f5f9',
                bodyColor: '#cbd5e1',
                borderColor: 'rgba(34, 197, 94, 0.4)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8,
                titleFont: { size: 13, weight: '700' },
                bodyFont: { size: 13 },
                callbacks: {
                    label: function(context) {
                        const val = context.parsed.y;
                        return context.dataset.label + ': ' + 
                               (val !== null && val !== undefined ? Number(val).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '—');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(255, 255, 255, 0.05)',
                    drawBorder: false,
                },
                ticks: {
                    color: '#94a3b8',
                    font: { size: 12 }
                }
            },
            x: {
                grid: { display: false },
                ticks: {
                    color: '#cbd5e1',
                    font: { size: 12, weight: '500' },
                    maxRotation: 45,
                    minRotation: 0,
                }
            }
        }
    }
});

// Loading state
document.getElementById('tsForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('analyzeBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '⏳ Analyzing...';
    }
});
</script>
<?php endif; ?>

<style>
/* =========================================
   TIME SERIES ANALYZER — FULL WIDTH DARK THEME
   ========================================= */

/* 1. FORCE FULL WIDTH LAYOUT */
.timeseries-page.dashboard-layout {
    display: flex !important;
    width: 100vw !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
    overflow-x: hidden !important;
}

.timeseries-page .main-content {
    flex: 1 !important;
    width: calc(100% - 250px) !important;
    max-width: none !important;
    min-width: 0 !important;
    padding: 32px !important;
    box-sizing: border-box !important;
}

/* 2. CARD BASE STYLES */
.ts-form-card,
.ts-chart-card,
.empty-ts {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 28px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
}

.card-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 20px !important;
    padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
}

.card-header h3 {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    margin: 0 !important;
}

.ts-subtitle {
    font-size: 13px !important;
    color: #94a3b8 !important;
    margin: 6px 0 0 0 !important;
}

.ts-chart-subtitle {
    font-size: 13px !important;
    color: #94a3b8 !important;
    margin: 6px 0 0 0 !important;
}
.ts-chart-subtitle strong { color: #38bdf8 !important; }

/* 3. BADGES */
.badge {
    display: inline-flex !important;
    align-items: center !important;
    padding: 5px 12px !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 0.5px !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
}
.badge.blue  { background: rgba(56, 189, 248, 0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(56, 189, 248, 0.3) !important; }
.badge.green { background: rgba(52, 211, 153, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(52, 211, 153, 0.3) !important; }
.badge.red   { background: rgba(248, 113, 113, 0.15) !important; color: #f87171 !important; border: 1px solid rgba(248, 113, 113, 0.3) !important; }

/* 4. FORM CONTROLS GRID (RESPONSIVE) */
.ts-controls {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important;
    gap: 16px !important;
    align-items: end !important;
    width: 100% !important;
}

.form-group {
    display: flex !important;
    flex-direction: column !important;
    gap: 8px !important;
    min-width: 0 !important;
}

.form-group label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
}

.form-group select,
.form-group input[type="number"] {
    width: 100% !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    padding: 12px 14px !important;
    color: #f1f5f9 !important;
    font-size: 14px !important;
    outline: none !important;
    cursor: pointer !important;
    transition: border-color 0.2s, box-shadow 0.2s !important;
    box-sizing: border-box !important;
}

.form-group select {
    -webkit-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 16px !important;
    padding-right: 40px !important;
}

.form-group select:focus,
.form-group input:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
}

.form-group select option {
    background: #1e293b !important;
    color: #f1f5f9 !important;
}

/* 5. RUN BUTTON */
.run-group { justify-content: flex-end !important; }

.btn-run {
    padding: 12px 28px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
    width: 100% !important;
    white-space: nowrap !important;
}

.btn-run:hover:not(:disabled) {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5) !important;
}

.btn-run:disabled {
    opacity: 0.6 !important;
    cursor: not-allowed !important;
}

/* 6. KPI CARDS */
.kpi-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
    gap: 20px !important;
    margin-bottom: 24px !important;
}

.kpi-card {
    display: flex !important;
    align-items: center !important;
    gap: 18px !important;
    padding: 24px !important;
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 18px !important;
    position: relative !important;
    overflow: hidden !important;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease !important;
    box-sizing: border-box !important;
}

.kpi-card::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important; left: 0 !important;
    width: 4px !important;
    height: 100% !important;
}

.kpi-card.points::before { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.kpi-card.peak::before   { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.kpi-card.avg::before    { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.kpi-card.trend.up::before   { background: linear-gradient(180deg, #10b981, #059669) !important; }
.kpi-card.trend.down::before { background: linear-gradient(180deg, #ef4444, #dc2626) !important; }

.kpi-card:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4) !important;
    border-color: rgba(56, 189, 248, 0.3) !important;
}

.kpi-icon {
    width: 54px !important;
    height: 54px !important;
    border-radius: 14px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 26px !important;
    flex-shrink: 0 !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.kpi-content { flex: 1 !important; min-width: 0 !important; }

.kpi-label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    margin: 0 0 6px 0 !important;
}

.kpi-value {
    font-size: 24px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 !important;
    line-height: 1.1 !important;
    font-variant-numeric: tabular-nums !important;
}

.kpi-value.positive { color: #6ee7b7 !important; }
.kpi-value.negative { color: #fca5a5 !important; }

/* 7. CHART WRAPPER */
.chart-wrapper {
    position: relative !important;
    width: 100% !important;
    height: 440px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border-radius: 14px !important;
    padding: 20px !important;
    box-sizing: border-box !important;
}

/* 8. EMPTY STATE */
.empty-ts {
    text-align: center !important;
    padding: 70px 24px !important;
}

.empty-ts .empty-icon {
    font-size: 60px !important;
    margin-bottom: 16px !important;
    opacity: 0.6 !important;
}

.empty-ts h3 {
    font-size: 20px !important;
    color: #f1f5f9 !important;
    margin: 0 0 10px 0 !important;
    font-weight: 700 !important;
}

.empty-ts p {
    color: #94a3b8 !important;
    font-size: 14px !important;
    max-width: 520px !important;
    margin: 0 auto !important;
    line-height: 1.6 !important;
}

.empty-ts strong { color: #38bdf8 !important; }

/* 9. RESPONSIVE */
@media (max-width: 768px) {
    .timeseries-page .main-content { padding: 16px !important; }
    .ts-form-card, .ts-chart-card { padding: 20px !important; }
    .ts-controls { grid-template-columns: 1fr !important; }
    .card-header {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    .kpi-grid { grid-template-columns: 1fr !important; }
    .kpi-value { font-size: 20px !important; }
    .kpi-icon { width: 46px !important; height: 46px !important; font-size: 22px !important; }
    .chart-wrapper { height: 320px !important; padding: 12px !important; }
}
</style>