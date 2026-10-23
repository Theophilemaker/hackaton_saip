<div class="dashboard-layout compare-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ============ COMPARISON FORM ============ -->
        <section class="glass-card compare-card">
            <div class="card-header">
                <h3>⚖️ Comparison Tool</h3>
                <span class="badge blue">Analyze Differences</span>
            </div>

            <p class="compare-intro">
                Compare two values within a dimension (e.g., <strong>Maize vs Potatoes</strong>), 
                or analyze any metric across a category. Choose a <strong>Dimension</strong>, 
                <strong>Metric</strong>, and <strong>Aggregation</strong>.
            </p>

            <form method="GET" id="compareForm">
                <div class="compare-controls">
                    <div class="form-group">
                        <label>Dataset</label>
                        <select name="dataset" onchange="window.location='<?= BASE_URL ?>/nisr/compare/'+this.value">
                            <?php foreach ($data['datasets'] as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($data['dataset'] ?? null) && $data['dataset']['id'] == $d['id'] ? 'selected' : '' ?>>
                                    <?= e($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Dimension</label>
                        <select name="dim">
                            <option value="">— Select —</option>
                            <?php foreach ($data['schema'] as $c): ?>
                                <option value="<?= e($c['name']) ?>" <?= ($data['dimension'] ?? '') === $c['name'] ? 'selected' : '' ?>>
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
                            <?php foreach (['sum','avg','count','min','max'] as $a): ?>
                                <option value="<?= $a ?>" <?= ($data['agg'] ?? 'sum') === $a ? 'selected' : '' ?>>
                                    <?= ucfirst($a) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group run-group">
                        <button class="btn-run" id="compareBtn">⚖️ Compare</button>
                    </div>
                </div>
            </form>
        </section>

        <!-- ============ EMPTY STATE ============ -->
        <?php if (!$data['comparison']): ?>
            <section class="glass-card empty-compare">
                <div class="empty-icon">⚖️</div>
                <h3>No Comparison Generated Yet</h3>
                <p>
                    Select a <strong>Dimension</strong>, a numeric <strong>Metric</strong>, 
                    and an <strong>Aggregation</strong> method above, then click 
                    <strong>Compare</strong> to visualize the differences.
                </p>
            </section>
        <?php endif; ?>

        <!-- ============ RESULTS ============ -->
        <?php if ($data['comparison']): ?>
            <!-- CHART -->
            <section class="glass-card compare-chart-card">
                <div class="card-header">
                    <div>
                        <h3>📊 Comparison Chart</h3>
                        <p class="compare-subtitle">
                            <strong><?= e($data['metric']) ?></strong> by 
                            <strong><?= e($data['dimension']) ?></strong> 
                            (<?= strtoupper($data['agg']) ?>)
                        </p>
                    </div>
                    <span class="badge green">
                        <?= count($data['comparison']['data']) ?> categories
                    </span>
                </div>

                <div class="chart-wrapper">
                    <canvas id="compareChart"></canvas>
                </div>
            </section>

            <!-- KPI DELTA CARDS -->
            <?php if (!empty($data['comparison']['delta'])): 
                $d = $data['comparison']['delta'];
                $isPositive = $d['percent'] > 0;
            ?>
                <section class="kpi-grid">
                    <div class="glass-card kpi-card first">
                        <div class="kpi-icon first-icon">📈</div>
                        <div class="kpi-content">
                            <p class="kpi-label">Top Value</p>
                            <p class="kpi-sublabel"><?= e($d['first']) ?></p>
                            <h3 class="kpi-value">
                                <?= number_format($data['comparison']['data'][$d['first']] ?? 0, 2) ?>
                            </h3>
                        </div>
                    </div>

                    <div class="glass-card kpi-card second">
                        <div class="kpi-icon second-icon">📉</div>
                        <div class="kpi-content">
                            <p class="kpi-label">Lowest Value</p>
                            <p class="kpi-sublabel"><?= e($d['second']) ?></p>
                            <h3 class="kpi-value">
                                <?= number_format($data['comparison']['data'][$d['second']] ?? 0, 2) ?>
                            </h3>
                        </div>
                    </div>

                    <div class="glass-card kpi-card delta <?= $isPositive ? 'up' : 'down' ?>">
                        <div class="kpi-icon delta-icon">
                            <?= $isPositive ? '▲' : '▼' ?>
                        </div>
                        <div class="kpi-content">
                            <p class="kpi-label">Difference</p>
                            <p class="kpi-sublabel">
                                <?= e($d['first']) ?> vs <?= e($d['second']) ?>
                            </p>
                            <h3 class="kpi-value <?= $isPositive ? 'positive' : 'negative' ?>">
                                <?= $isPositive ? '+' : '' ?><?= number_format($d['percent'], 2) ?>%
                            </h3>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if ($data['comparison']): ?>
<script>
// Determine chart colors based on values
const labels = <?= json_encode(array_keys($data['comparison']['data'])) ?>;
const values = <?= json_encode(array_values($data['comparison']['data'])) ?>;

// Gradient palette for each bar
const colors = [
    'rgba(16, 185, 129, 0.85)',  // green
    'rgba(56, 189, 248, 0.85)',  // blue
    'rgba(167, 139, 250, 0.85)', // purple
    'rgba(251, 191, 36, 0.85)',  // amber
    'rgba(244, 114, 182, 0.85)', // pink
    'rgba(248, 113, 113, 0.85)', // red
    'rgba(34, 197, 94, 0.85)',   // emerald
    'rgba(14, 165, 233, 0.85)',  // sky
];

const borderColors = colors.map(c => c.replace('0.85', '1'));

new Chart(document.getElementById('compareChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: '<?= e($data['metric']) ?>',
            data: values,
            backgroundColor: colors,
            borderColor: borderColors,
            borderWidth: 2,
            borderRadius: 10,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 800,
            easing: 'easeOutQuart'
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
                borderColor: 'rgba(56, 189, 248, 0.4)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8,
                titleFont: { size: 13, weight: '700' },
                bodyFont: { size: 13 },
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
                    font: { size: 12, weight: '600' }
                }
            }
        }
    }
});

// Loading state on form submit
document.getElementById('compareForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('compareBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '⏳ Comparing...';
    }
});
</script>
<?php endif; ?>

<style>
/* =========================================
   COMPARISON TOOL — FULL WIDTH DARK THEME
   ========================================= */

/* 1. FORCE FULL WIDTH LAYOUT */
.compare-page.dashboard-layout {
    display: flex !important;
    width: 100vw !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
    overflow-x: hidden !important;
}

.compare-page .main-content {
    flex: 1 !important;
    width: calc(100% - 250px) !important;
    max-width: none !important;
    min-width: 0 !important;
    padding: 32px !important;
    box-sizing: border-box !important;
}

/* 2. CARD BASE STYLES */
.compare-card,
.compare-chart-card,
.empty-compare {
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

.badge {
    display: inline-flex !important;
    align-items: center !important;
    padding: 5px 12px !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 0.5px !important;
    text-transform: uppercase !important;
}

.badge.blue {
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
}

.badge.green {
    background: rgba(52, 211, 153, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(52, 211, 153, 0.3) !important;
}

/* 3. INTRO TEXT */
.compare-intro {
    color: #94a3b8 !important;
    font-size: 14px !important;
    line-height: 1.6 !important;
    margin: 0 0 24px 0 !important;
}
.compare-intro strong {
    color: #38bdf8 !important;
}

/* 4. FORM CONTROLS GRID (RESPONSIVE) */
.compare-controls {
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

.form-group select {
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
    -webkit-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 16px !important;
    padding-right: 40px !important;
}

.form-group select:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
}

.form-group select option {
    background: #1e293b !important;
    color: #f1f5f9 !important;
}

/* 5. RUN BUTTON */
.run-group {
    justify-content: flex-end !important;
}

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

/* 6. EMPTY STATE */
.empty-compare {
    text-align: center !important;
    padding: 60px 24px !important;
}

.empty-compare .empty-icon {
    font-size: 56px !important;
    margin-bottom: 12px !important;
    opacity: 0.6 !important;
}

.empty-compare h3 {
    font-size: 18px !important;
    color: #f1f5f9 !important;
    margin: 0 0 8px 0 !important;
}

.empty-compare p {
    color: #94a3b8 !important;
    font-size: 14px !important;
    max-width: 520px !important;
    margin: 0 auto !important;
    line-height: 1.6 !important;
}

.empty-compare strong {
    color: #38bdf8 !important;
}

/* 7. CHART WRAPPER (fixed height for Chart.js) */
.compare-subtitle {
    font-size: 13px !important;
    color: #94a3b8 !important;
    margin: 6px 0 0 0 !important;
}
.compare-subtitle strong { color: #38bdf8 !important; }

.chart-wrapper {
    position: relative !important;
    width: 100% !important;
    height: 420px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border-radius: 14px !important;
    padding: 20px !important;
    box-sizing: border-box !important;
}

/* 8. KPI CARDS */
.kpi-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)) !important;
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
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease !important;
    position: relative !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
}

.kpi-card::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 4px !important;
    height: 100% !important;
    background: #38bdf8 !important;
}

.kpi-card.first::before  { background: linear-gradient(180deg, #10b981, #22c55e) !important; }
.kpi-card.second::before { background: linear-gradient(180deg, #f59e0b, #ef4444) !important; }
.kpi-card.delta.up::before   { background: linear-gradient(180deg, #84cc16, #16a34a) !important; }
.kpi-card.delta.down::before { background: linear-gradient(180deg, #ef4444, #dc2626) !important; }

.kpi-card:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4) !important;
    border-color: rgba(56, 189, 248, 0.3) !important;
}

.kpi-icon {
    width: 56px !important;
    height: 56px !important;
    border-radius: 14px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 26px !important;
    flex-shrink: 0 !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.kpi-icon.first-icon  { background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(34, 197, 94, 0.15)) !important; }
.kpi-icon.second-icon { background: linear-gradient(135deg, rgba(245, 158, 11, 0.25), rgba(239, 68, 68, 0.15)) !important; }
.kpi-icon.delta-icon  { background: linear-gradient(135deg, rgba(132, 204, 22, 0.25), rgba(22, 163, 74, 0.15)) !important; }

.kpi-card.delta.down .kpi-icon {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.25), rgba(220, 38, 38, 0.15)) !important;
}

.kpi-content {
    flex: 1 !important;
    min-width: 0 !important;
}

.kpi-label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    margin: 0 0 4px 0 !important;
}

.kpi-sublabel {
    font-size: 12px !important;
    color: #64748b !important;
    margin: 0 0 6px 0 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.kpi-value {
    font-size: 24px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 !important;
    line-height: 1.2 !important;
    font-variant-numeric: tabular-nums !important;
}

.kpi-value.positive { color: #6ee7b7 !important; }
.kpi-value.negative { color: #fca5a5 !important; }

/* 9. RESPONSIVE */
@media (max-width: 768px) {
    .compare-page .main-content {
        padding: 16px !important;
    }
    .compare-card, .compare-chart-card {
        padding: 20px !important;
    }
    .compare-controls {
        grid-template-columns: 1fr !important;
    }
    .card-header {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    .chart-wrapper {
        height: 320px !important;
        padding: 12px !important;
    }
    .kpi-grid {
        grid-template-columns: 1fr !important;
    }
    .kpi-value { font-size: 20px !important; }
    .kpi-icon { width: 48px !important; height: 48px !important; font-size: 22px !important; }
}
</style>