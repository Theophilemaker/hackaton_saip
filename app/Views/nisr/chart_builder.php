<?php 
// Robust schema decode
$schema = $data['schema'] ?? [];
if (empty($schema) && !empty($data['dataset']['columns_schema'])) {
    $schema = json_decode($data['dataset']['columns_schema'], true) ?: [];
}

$numericCols = [];
$stringCols  = [];
foreach ($schema as $c) {
    if (($c['type'] ?? '') === 'number') $numericCols[] = $c['name'];
    else $stringCols[] = $c['name'];
}
?>

<div class="dashboard-layout chart-builder-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ============ HERO ============ -->
        <section class="glass-card cb-hero">
            <div class="hero-content">
                <span class="hero-tag">Intelligence Studio</span>
                <h1>📈 Chart Builder &amp; Analyzer</h1>
                <p>
                    Build visualizations, detect trends, identify outliers, and generate 
                    instant insights — all from your NISR datasets.
                </p>
            </div>
            <div class="hero-badges">
                <span class="hero-badge">📊 <?= count($numericCols) ?> Metrics</span>
                <span class="hero-badge">🏷️ <?= count($stringCols) ?> Dimensions</span>
                <span class="hero-badge">🤖 AI Insights</span>
            </div>
        </section>

        <!-- ============ SCHEMA WARNING ============ -->
        <?php if (empty($schema)): ?>
            <div class="glass-card schema-warning">
                <span class="warning-icon">⚠️</span>
                <div>
                    <strong>No columns found for this dataset</strong>
                    <p>The database column <code>columns_schema</code> is empty. Re-upload the file or check the database for dataset ID <?= $data['dataset']['id'] ?? '?' ?>.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============ QUICK PRESETS ============ -->
        <?php if (!empty($numericCols) && !empty($stringCols)): ?>
            <section class="glass-card presets-card">
                <div class="presets-header">
                    <h3>⚡ Analysis Presets</h3>
                    <p>One-click configurations for common analysis tasks</p>
                </div>
                <div class="presets-grid">
                    <button class="preset-btn" onclick="applyPreset('top5')">
                        <span class="preset-icon">🏆</span>
                        <div class="preset-info">
                            <strong>Top 5 Performers</strong>
                            <small>Highest values ranked</small>
                        </div>
                    </button>
                    <button class="preset-btn" onclick="applyPreset('distribution')">
                        <span class="preset-icon">📊</span>
                        <div class="preset-info">
                            <strong>Distribution</strong>
                            <small>Category breakdown</small>
                        </div>
                    </button>
                    <button class="preset-btn" onclick="applyPreset('trend')">
                        <span class="preset-icon">📈</span>
                        <div class="preset-info">
                            <strong>Trend Analysis</strong>
                            <small>Line chart over time</small>
                        </div>
                    </button>
                    <button class="preset-btn" onclick="applyPreset('proportion')">
                        <span class="preset-icon">🥧</span>
                        <div class="preset-info">
                            <strong>Proportion</strong>
                            <small>Part-to-whole view</small>
                        </div>
                    </button>
                    <button class="preset-btn pareto" onclick="applyPreset('pareto')">
                        <span class="preset-icon">📐</span>
                        <div class="preset-info">
                            <strong>Pareto (80/20)</strong>
                            <small>Cumulative impact</small>
                        </div>
                    </button>
                </div>
            </section>
        <?php endif; ?>

        <!-- ============ MAIN BUILDER CARD ============ -->
        <section class="glass-card chart-builder-card">
            <div class="card-header">
                <div>
                    <h3>🎨 Configuration</h3>
                    <p class="card-subtitle">Customize your visualization in real time.</p>
                </div>
                <span class="badge blue" id="statusBadge">Ready</span>
            </div>

            <div class="builder-tabs">
                <button class="tab-btn active" onclick="switchTab(event,'data')">📊 Data</button>
                <button class="tab-btn" onclick="switchTab(event,'appearance')">🎨 Appearance</button>
                <button class="tab-btn" onclick="switchTab(event,'advanced')">⚙️ Advanced</button>
            </div>

            <form id="chartForm" onsubmit="event.preventDefault();">
                <!-- DATA TAB -->
                <div class="tab-panel active" id="tab-data">
                    <div class="chart-controls">
                        <div class="form-group">
                            <label>Dataset</label>
                            <select name="dataset" id="chartDataset">
                                <?php foreach ($data['datasets'] as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= ($data['dataset'] ?? null) && $data['dataset']['id'] == $d['id'] ? 'selected' : '' ?>>
                                        <?= e($d['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Chart Type</label>
                            <select name="type" id="chartType">
                                <optgroup label="Comparison">
                                    <option value="bar">📊 Bar</option>
                                    <option value="horizontalBar">📊 Horizontal Bar</option>
                                    <option value="radar">🎯 Radar</option>
                                </optgroup>
                                <optgroup label="Trend">
                                    <option value="line">📈 Line</option>
                                    <option value="area">🌊 Area</option>
                                </optgroup>
                                <optgroup label="Proportion">
                                    <option value="pie">🥧 Pie</option>
                                    <option value="doughnut">🍩 Doughnut</option>
                                    <option value="polarArea">⭕ Polar Area</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- CATEGORY (X) -->
<div class="form-group">
    <label>Category (X)</label>
    <select name="x" id="chartX">
        <?php if (empty($schema)): ?>
            <option value="">⚠️ No columns found — re-upload dataset</option>
        <?php else: ?>
            <option value="">— Select column —</option>
            <?php foreach ($schema as $c): 
                $colName = $c['name'] ?? '';
                if ($colName === '') continue;
            ?>
                <option value="<?= e($colName) ?>"><?= e($colName) ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>

<!-- METRIC (Y) -->
<div class="form-group">
    <label>Metric (Y)</label>
    <select name="y" id="chartY">
        <?php 
        // Find numeric columns
        $numericCols = [];
        foreach ($schema as $c) {
            if (($c['type'] ?? '') === 'number' && !empty($c['name'])) {
                $numericCols[] = $c['name'];
            }
        }
        
        // If no numeric, fall back to all columns (so user can still try)
        if (empty($numericCols)) {
            foreach ($schema as $c) {
                if (!empty($c['name'])) $numericCols[] = $c['name'];
            }
        }
        ?>
        
        <?php if (empty($numericCols)): ?>
            <option value="">⚠️ No numeric columns found</option>
        <?php else: ?>
            <option value="">— Select metric —</option>
            <?php foreach ($numericCols as $colName): ?>
                <option value="<?= e($colName) ?>"><?= e($colName) ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>

                        <div class="form-group">
                            <label>Aggregation</label>
                            <select name="agg" id="chartAgg">
                                <option value="sum">Σ Sum</option>
                                <option value="avg">⌀ Average</option>
                                <option value="count"># Count</option>
                                <option value="min">↓ Minimum</option>
                                <option value="max">↑ Maximum</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Top N</label>
                            <select name="limit" id="chartLimit">
                                <option value="0">Show All</option>
                                <option value="5">Top 5</option>
                                <option value="10" selected>Top 10</option>
                                <option value="20">Top 20</option>
                                <option value="50">Top 50</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- APPEARANCE TAB -->
                <div class="tab-panel" id="tab-appearance">
                    <div class="chart-controls">
                        <div class="form-group">
                            <label>Color Scheme</label>
                            <select id="chartPalette">
                                <option value="emerald">🌿 Emerald</option>
                                <option value="ocean">🌊 Ocean</option>
                                <option value="sunset">🌅 Sunset</option>
                                <option value="rainbow">🌈 Rainbow</option>
                                <option value="monochrome">⚫ Monochrome</option>
                                <option value="pastel">🌸 Pastel</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Chart Title</label>
                            <input type="text" id="chartTitle" placeholder="Optional title" maxlength="100">
                        </div>

                        <div class="form-group">
                            <label>Legend Position</label>
                            <select id="chartLegend">
                                <option value="top">Top</option>
                                <option value="bottom">Bottom</option>
                                <option value="left">Left</option>
                                <option value="right">Right</option>
                                <option value="hidden">Hidden</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Line Curve</label>
                            <select id="chartTension">
                                <option value="0">Straight</option>
                                <option value="0.4" selected>Smooth</option>
                                <option value="0.8">Very Curved</option>
                            </select>
                        </div>
                    </div>

                    <div class="toggles-row">
                        <label class="toggle-item">
                            <input type="checkbox" id="optGrid" checked>
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">Grid lines</span>
                        </label>
                        <label class="toggle-item">
                            <input type="checkbox" id="optDataLabels">
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">Data labels</span>
                        </label>
                        <label class="toggle-item">
                            <input type="checkbox" id="optAverageLine">
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">Show average line</span>
                        </label>
                        <label class="toggle-item">
                            <input type="checkbox" id="optAnimate" checked>
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">Animate</span>
                        </label>
                    </div>
                </div>

                <!-- ADVANCED TAB -->
                <div class="tab-panel" id="tab-advanced">
                    <div class="chart-controls">
                        <div class="form-group">
                            <label>Y-Axis Scale</label>
                            <select id="chartYScale">
                                <option value="linear">Linear</option>
                                <option value="logarithmic">Logarithmic</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Chart Height</label>
                            <select id="chartHeight">
                                <option value="350">Compact (350px)</option>
                                <option value="450" selected>Standard (450px)</option>
                                <option value="600">Tall (600px)</option>
                                <option value="750">Extra Tall (750px)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Sort Order</label>
                            <select id="chartSort">
                                <option value="none">Natural</option>
                                <option value="desc">Descending</option>
                                <option value="asc">Ascending</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Outlier Detection</label>
                            <select id="chartOutliers">
                                <option value="off">Off</option>
                                <option value="highlight">Highlight outliers</option>
                                <option value="exclude">Exclude outliers</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="builder-actions">
                    <button type="button" class="btn-primary" id="generateBtn" onclick="renderChart()" <?= empty($schema) ? 'disabled' : '' ?>>
                        🎨 Generate Chart
                    </button>
                    <button type="button" class="btn-secondary" onclick="resetChart()">🔄 Reset</button>
                    <button type="button" class="btn-secondary" id="downloadPngBtn" onclick="downloadChart()" disabled>⬇️ PNG</button>
                    <button type="button" class="btn-secondary" id="downloadCsvBtn" onclick="downloadChartData()" disabled>📄 CSV</button>
                </div>
            </form>
        </section>

        <!-- ============ CHART PREVIEW ============ -->
        <section class="glass-card chart-preview-card">
            <div class="card-header">
                <div>
                    <h3>🖼️ Preview</h3>
                    <p class="card-subtitle" id="previewSubtitle">No chart generated yet</p>
                </div>
                <button class="icon-btn-sm" onclick="toggleFullscreen()" title="Fullscreen">⛶ Fullscreen</button>
            </div>

            <div class="chart-container" id="chartContainer">
                <canvas id="builderChart"></canvas>
                <div class="chart-empty-state" id="emptyChartState">
                    <div class="empty-icon">📊</div>
                    <h4>Your chart will appear here</h4>
                    <p>Configure options above and click <strong>Generate Chart</strong>.</p>
                </div>
            </div>
        </section>

        <!-- ============ AUTO INSIGHTS ============ -->
        <section class="glass-card insights-card" id="insightsCard" style="display:none;">
            <div class="card-header">
                <div>
                    <h3>🤖 Auto-Generated Insights</h3>
                    <p class="card-subtitle">Analysis derived from your current chart data</p>
                </div>
                <span class="badge purple">AI</span>
            </div>
            <div class="insights-list" id="insightsList"></div>
        </section>

        <!-- ============ ADVANCED STATISTICS ============ -->
        <section class="glass-card chart-stats-card" id="chartStatsCard" style="display:none;">
            <div class="card-header">
                <div>
                    <h3>📊 Statistical Summary</h3>
                    <p class="card-subtitle">Distribution metrics for the selected metric</p>
                </div>
            </div>
            <div class="stats-grid">
                <div class="stat-box">
                    <span class="stat-box-label">Data Points</span>
                    <span class="stat-box-value" id="statPoints">—</span>
                </div>
                <div class="stat-box">
                    <span class="stat-box-label">Total</span>
                    <span class="stat-box-value" id="statTotal">—</span>
                </div>
                <div class="stat-box">
                    <span class="stat-box-label">Mean</span>
                    <span class="stat-box-value" id="statAvg">—</span>
                </div>
                <div class="stat-box">
                    <span class="stat-box-label">Median</span>
                    <span class="stat-box-value" id="statMedian">—</span>
                </div>
                <div class="stat-box">
                    <span class="stat-box-label">Std Deviation</span>
                    <span class="stat-box-value" id="statStdDev">—</span>
                </div>
                <div class="stat-box">
                    <span class="stat-box-label">Coeff. Variation</span>
                    <span class="stat-box-value" id="statCV">—</span>
                </div>
                <div class="stat-box">
                    <span class="stat-box-label">Q1</span>
                    <span class="stat-box-value" id="statQ1">—</span>
                </div>
                <div class="stat-box">
                    <span class="stat-box-label">Q3</span>
                    <span class="stat-box-value" id="statQ3">—</span>
                </div>
                <div class="stat-box">
                    <span class="stat-box-label">Range</span>
                    <span class="stat-box-value" id="statRange">—</span>
                </div>
                <div class="stat-box">
                    <span class="stat-box-label">Outliers</span>
                    <span class="stat-box-value" id="statOutliers">—</span>
                </div>
                <div class="stat-box">
                    <span class="stat-box-label">Min</span>
                    <span class="stat-box-value" id="statMin">—</span>
                </div>
                <div class="stat-box">
                    <span class="stat-box-label">Max</span>
                    <span class="stat-box-value" id="statMax">—</span>
                </div>
            </div>
        </section>

        <!-- ============ DATA TABLE ============ -->
        <section class="glass-card chart-data-card" id="chartDataCard" style="display:none;">
            <div class="card-header">
                <div>
                    <h3>🗂️ Chart Data Table</h3>
                    <p class="card-subtitle">Click any column header to sort</p>
                </div>
                <span class="badge green" id="dataTableBadge">0 rows</span>
            </div>
            <div class="data-table-wrapper">
                <table class="chart-data-table" id="chartDataTable">
                    <thead>
                        <tr>
                            <th class="row-num">#</th>
                            <th class="sortable" onclick="sortTable('cat')" id="thCategory">Category</th>
                            <th class="sortable num-col" onclick="sortTable('val')" id="thValue">Value</th>
                            <th class="sortable num-col" onclick="sortTable('pct')" id="thPct">% of Total</th>
                            <th class="num-col">Dev. from Mean</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chartInstance = null;
let lastChartData = null;
let tableSortState = { key: 'val', dir: 'desc' };

const PALETTES = {
    emerald:    ['#10b981','#22c55e','#84cc16','#4ade80','#34d399','#6ee7b7','#a7f3d0','#059669'],
    ocean:      ['#0ea5e9','#38bdf8','#06b6d4','#22d3ee','#0284c7','#0891b2','#67e8f9','#0c4a6e'],
    sunset:     ['#f59e0b','#f97316','#ef4444','#fbbf24','#fb923c','#dc2626','#facc15','#ea580c'],
    rainbow:    ['#ef4444','#f97316','#f59e0b','#eab308','#84cc16','#22c55e','#06b6d4','#3b82f6','#8b5cf6','#ec4899'],
    monochrome: ['#f1f5f9','#cbd5e1','#94a3b8','#64748b','#475569','#334155','#1e293b'],
    pastel:     ['#a7f3d0','#bae6fd','#fde68a','#fecaca','#ddd6fe','#fed7aa','#bbf7d0','#fbcfe8']
};

function switchTab(evt, name) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    evt.currentTarget.classList.add('active');
    document.getElementById('tab-' + name).classList.add('active');
}

function applyPreset(preset) {
    const xSel = document.getElementById('chartX');
    const ySel = document.getElementById('chartY');
    const typeSel = document.getElementById('chartType');
    const limitSel = document.getElementById('chartLimit');
    const aggSel = document.getElementById('chartAgg');

    xSel.value = xSel.options[0]?.value || '';
    ySel.value = ySel.options[0]?.value || '';

    switch (preset) {
        case 'top5':
            typeSel.value = 'bar';
            limitSel.value = '5';
            aggSel.value = 'sum';
            document.getElementById('chartSort').value = 'desc';
            break;
        case 'distribution':
            typeSel.value = 'horizontalBar';
            limitSel.value = '10';
            aggSel.value = 'count';
            break;
        case 'trend':
            typeSel.value = 'line';
            limitSel.value = '0';
            aggSel.value = 'sum';
            break;
        case 'proportion':
            typeSel.value = 'doughnut';
            limitSel.value = '8';
            aggSel.value = 'sum';
            break;
        case 'pareto':
            typeSel.value = 'bar';
            limitSel.value = '0';
            aggSel.value = 'sum';
            document.getElementById('chartSort').value = 'desc';
            document.getElementById('optDataLabels').checked = true;
            break;
    }

    showToast('Preset applied! Click Generate Chart.', 'success');
}

async function renderChart() {
    const datasetId = document.getElementById('chartDataset').value;
    const xField   = document.getElementById('chartX').value;
    const yField   = document.getElementById('chartY').value;
    const type     = document.getElementById('chartType').value;
    const agg      = document.getElementById('chartAgg').value;
    const limit    = parseInt(document.getElementById('chartLimit').value);
    const sort     = document.getElementById('chartSort').value;
    const outlierMode = document.getElementById('chartOutliers').value;

    if (!xField || !yField) {
        showToast('Please select both a Category (X) and Metric (Y) field.', 'error');
        return;
    }

    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Generating...';
    setStatus('Loading data…', 'loading');

    try {
        const url = `<?= BASE_URL ?>/nisr/apiPivot?datasetId=${datasetId}&row=${encodeURIComponent(xField)}&col=${encodeURIComponent(xField)}&val=${encodeURIComponent(yField)}&agg=${agg}`;
        console.log('Fetching:', url);

        const res = await fetch(url, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const text = await res.text();
            console.error('Non-JSON response:', text.substring(0, 400));
            if (res.status === 401 || text.toLowerCase().includes('login')) {
                throw new Error('Session expired. Please refresh the page and log in again.');
            }
            if (res.status === 404) {
                throw new Error('API endpoint /nisr/api/pivot not found. Check your routes.');
            }
            throw new Error(`Server returned ${res.status} (${contentType.split(';')[0]}) instead of JSON.`);
        }

        const data = await res.json();
        if (data.error) throw new Error(data.error);

        let labels = [], values = [];

        if (data.rows && data.data) {
            labels = data.rows.slice();
            values = labels.map(l => (data.data[l] && data.data[l][l]) ? Number(data.data[l][l]) : 0);
        } else if (Array.isArray(data)) {
            labels = data.map(item => item[xField] ?? 'Unknown');
            values = data.map(item => Number(item[yField]) || 0);
        }

        if (labels.length === 0) {
            showToast('No data returned for this combination.', 'error');
            setStatus('No data', 'error');
            btn.disabled = false;
            btn.innerHTML = '🎨 Generate Chart';
            return;
        }

        // ===== Outlier detection (z-score method) =====
        const stats = computeStats(values);
        let outliers = [];
        values.forEach((v, i) => {
            if (stats.stdDev > 0 && Math.abs(v - stats.mean) > 2 * stats.stdDev) {
                outliers.push(i);
            }
        });

        if (outlierMode === 'exclude' && outliers.length > 0) {
            const keep = values.map((_, i) => !outliers.includes(i));
            labels = labels.filter((_, i) => keep[i]);
            values = values.filter((_, i) => keep[i]);
            showToast(`${outliers.length} outlier(s) excluded.`, 'success');
            outliers = [];
        }

        // ===== Sort =====
        if (sort === 'desc' || sort === 'asc') {
            const pairs = labels.map((l, i) => [l, values[i]]);
            pairs.sort((a, b) => sort === 'desc' ? b[1] - a[1] : a[1] - b[1]);
            labels = pairs.map(p => p[0]);
            values = pairs.map(p => p[1]);
        }

        // ===== Limit =====
        if (limit > 0 && labels.length > limit) {
            labels = labels.slice(0, limit);
            values = values.slice(0, limit);
        }

        lastChartData = { labels, values, xField, yField, agg, type, outliers };

        drawChart(type, labels, values, yField, agg, outliers, stats);
        updateStats(stats, outliers.length);
        renderDataTable(labels, values, xField, yField, agg, stats);
        generateInsights(labels, values, xField, yField, agg, stats);
        updateSubtitle(xField, yField, agg, type);

        document.getElementById('downloadPngBtn').disabled = false;
        document.getElementById('downloadCsvBtn').disabled = false;
        document.getElementById('emptyChartState').style.display = 'none';
        document.getElementById('chartStatsCard').style.display = '';
        document.getElementById('chartDataCard').style.display = '';
        document.getElementById('insightsCard').style.display = '';

        setStatus('Chart ready', 'ready');
        showToast('Chart generated successfully!', 'success');

    } catch (err) {
        console.error(err);
        showToast('Failed: ' + err.message, 'error');
        setStatus('Error', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '🎨 Generate Chart';
    }
}

function computeStats(values) {
    const n = values.length;
    if (n === 0) return { count: 0, total: 0, mean: 0, median: 0, stdDev: 0, cv: 0, q1: 0, q3: 0, range: 0, min: 0, max: 0 };
    const sorted = values.slice().sort((a, b) => a - b);
    const total = values.reduce((a, b) => a + b, 0);
    const mean = total / n;
    const median = n % 2 === 0 ? (sorted[n/2 - 1] + sorted[n/2]) / 2 : sorted[Math.floor(n/2)];
    const variance = values.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / n;
    const stdDev = Math.sqrt(variance);
    const cv = mean !== 0 ? (stdDev / Math.abs(mean)) * 100 : 0;
    const q1 = sorted[Math.floor(n * 0.25)];
    const q3 = sorted[Math.floor(n * 0.75)];
    return { count: n, total, mean, median, stdDev, cv, q1, q3, range: sorted[n-1] - sorted[0], min: sorted[0], max: sorted[n-1] };
}

function drawChart(type, labels, values, yField, agg, outliers, stats) {
    const palette = PALETTES[document.getElementById('chartPalette').value] || PALETTES.emerald;
    const showGrid = document.getElementById('optGrid').checked;
    const showLabels = document.getElementById('optDataLabels').checked;
    const showAvgLine = document.getElementById('optAverageLine').checked;
    const animate = document.getElementById('optAnimate').checked;
    const tension = parseFloat(document.getElementById('chartTension').value);
    const legendPos = document.getElementById('chartLegend').value;
    const yScale = document.getElementById('chartYScale').value;
    const chartTitle = document.getElementById('chartTitle').value.trim();

    let chartType = type;
    let isHorizontal = false;
    if (type === 'horizontalBar') { chartType = 'bar'; isHorizontal = true; }
    if (type === 'area') { chartType = 'line'; }

    const colors = labels.map((_, i) => {
        if (outliers.includes(i)) return 'rgba(239, 68, 68, 0.9)';
        return palette[i % palette.length];
    });

    const borderColors = colors.map(c => c);
    const isCircular = ['pie', 'doughnut', 'polarArea'].includes(chartType);

    const dataset = {
        label: `${capitalize(agg)} of ${yField}`,
        data: values,
        backgroundColor: type === 'area' ? hexToRgba(palette[0], 0.35) : colors,
        borderColor: type === 'line' || type === 'area' ? palette[0] : borderColors,
        borderWidth: type === 'line' || type === 'area' ? 3 : 2,
        tension: tension,
        fill: type === 'area',
        pointRadius: (type === 'line' || type === 'area') ? 4 : 0,
        pointHoverRadius: 7,
        pointBackgroundColor: (type === 'line' || type === 'area') ? palette[0] : undefined,
        pointBorderColor: '#0b1120',
        pointBorderWidth: 2,
        borderRadius: chartType === 'bar' ? 8 : 0,
        borderSkipped: false,
    };

    const datasets = [dataset];

    // ===== Average line overlay =====
    if (showAvgLine && !isCircular) {
        datasets.push({
            type: 'line',
            label: `Mean (${stats.mean.toFixed(2)})`,
            data: labels.map(() => stats.mean),
            borderColor: '#f59e0b',
            borderWidth: 2,
            borderDash: [6, 4],
            pointRadius: 0,
            fill: false,
            tension: 0,
        });
    }

    // ===== Data labels plugin =====
    const dataLabelPlugin = {
        id: 'dataLabels',
        afterDatasetsDraw(chart) {
            if (!showLabels) return;
            const { ctx } = chart;
            ctx.save();
            ctx.font = 'bold 11px Inter, sans-serif';
            ctx.fillStyle = '#f1f5f9';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';

            chart.data.datasets.forEach((ds, di) => {
                if (ds.label && ds.label.startsWith('Mean')) return;
                const meta = chart.getDatasetMeta(di);
                meta.data.forEach((el, i) => {
                    const val = ds.data[i];
                    if (val === null || val === undefined) return;
                    const formatted = Number(val).toLocaleString(undefined, { maximumFractionDigits: 2 });
                    if (isCircular) {
                        const pos = el.tooltipPosition();
                        ctx.fillText(formatted, pos.x, pos.y + 4);
                    } else if (isHorizontal) {
                        ctx.textAlign = 'left';
                        ctx.fillText(formatted, el.x + 6, el.y + 4);
                    } else {
                        ctx.fillText(formatted, el.x, el.y - 6);
                    }
                });
            });
            ctx.restore();
        }
    };

    if (chartInstance) chartInstance.destroy();

    const container = document.getElementById('chartContainer');
    container.style.height = parseInt(document.getElementById('chartHeight').value) + 'px';

    chartInstance = new Chart(document.getElementById('builderChart'), {
        type: chartType,
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: isHorizontal ? 'y' : 'x',
            animation: animate ? { duration: 800, easing: 'easeOutQuart' } : false,
            interaction: { mode: isCircular ? 'nearest' : 'index', intersect: isCircular },
            plugins: {
                legend: {
                    display: legendPos !== 'hidden',
                    position: legendPos === 'hidden' ? 'top' : legendPos,
                    labels: {
                        color: '#cbd5e1',
                        font: { size: 12, weight: '600' },
                        padding: 14,
                        usePointStyle: true,
                        boxWidth: 10,
                        boxHeight: 10,
                    }
                },
                title: chartTitle ? {
                    display: true,
                    text: chartTitle,
                    color: '#f1f5f9',
                    font: { size: 16, weight: '700', family: 'Inter, sans-serif' },
                    padding: { top: 4, bottom: 18 }
                } : { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#f1f5f9',
                    bodyColor: '#cbd5e1',
                    borderColor: 'rgba(56, 189, 248, 0.4)',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: { size: 13, weight: '700' },
                    bodyFont: { size: 12.5 },
                    callbacks: {
                        label: function(ctx) {
                            const val = ctx.parsed.y !== undefined && !isCircular ? ctx.parsed.y : ctx.parsed;
                            const num = typeof val === 'object' ? (val.x ?? val.y) : val;
                            const label = ctx.dataset.label || '';
                            return ` ${label}: ${Number(num).toLocaleString(undefined, { maximumFractionDigits: 2 })}`;
                        }
                    }
                }
            },
            scales: isCircular ? {} : {
                y: {
                    beginAtZero: true,
                    type: yScale,
                    grid: { display: showGrid, color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                    ticks: { color: '#94a3b8', font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#cbd5e1',
                        font: { size: 11, weight: '500' },
                        maxRotation: labels.length > 8 ? 45 : 0,
                        autoSkip: true,
                        maxTicksLimit: 20,
                    }
                }
            }
        },
        plugins: [dataLabelPlugin]
    });
}

function updateStats(stats, outlierCount) {
    const fmt = n => Number(n).toLocaleString(undefined, { maximumFractionDigits: 2 });
    document.getElementById('statPoints').textContent = stats.count;
    document.getElementById('statTotal').textContent = fmt(stats.total);
    document.getElementById('statAvg').textContent = fmt(stats.mean);
    document.getElementById('statMedian').textContent = fmt(stats.median);
    document.getElementById('statStdDev').textContent = fmt(stats.stdDev);
    document.getElementById('statCV').textContent = stats.cv.toFixed(1) + '%';
    document.getElementById('statQ1').textContent = fmt(stats.q1);
    document.getElementById('statQ3').textContent = fmt(stats.q3);
    document.getElementById('statRange').textContent = fmt(stats.range);
    document.getElementById('statOutliers').textContent = outlierCount;
    document.getElementById('statMin').textContent = fmt(stats.min);
    document.getElementById('statMax').textContent = fmt(stats.max);
}

function generateInsights(labels, values, xField, yField, agg, stats) {
    const insights = [];
    const total = stats.total;
    const n = stats.count;

    // Concentration
    const sorted = labels.map((l, i) => ({ l, v: values[i] })).sort((a, b) => b.v - a.v);
    const top3Sum = sorted.slice(0, 3).reduce((s, x) => s + x.v, 0);
    const top3Pct = total !== 0 ? (top3Sum / total * 100) : 0;

    if (top3Pct >= 50) {
        insights.push({
            icon: '🎯',
            type: 'success',
            title: 'High concentration',
            text: `The top 3 categories account for <strong>${top3Pct.toFixed(1)}%</strong> of the total ${agg} of <strong>${yField}</strong>. Focus resources here for maximum impact.`
        });
    } else if (top3Pct <= 30) {
        insights.push({
            icon: '📊',
            type: 'info',
            title: 'Even distribution',
            text: `The top 3 categories only account for <strong>${top3Pct.toFixed(1)}%</strong> of the total — values are well spread across categories.`
        });
    }

    // Top performer
    if (sorted[0]) {
        const topPct = total !== 0 ? (sorted[0].v / total * 100) : 0;
        insights.push({
            icon: '🏆',
            type: 'success',
            title: 'Top performer',
            text: `<strong>${escapeHtml(sorted[0].l)}</strong> leads with <strong>${sorted[0].v.toLocaleString(undefined, {maximumFractionDigits: 2})}</strong> (${topPct.toFixed(1)}% of total).`
        });
    }

    // Bottom performer
    if (sorted.length > 1) {
        const bottom = sorted[sorted.length - 1];
        const bottomPct = total !== 0 ? (bottom.v / total * 100) : 0;
        insights.push({
            icon: '📉',
            type: 'warning',
            title: 'Lowest performer',
            text: `<strong>${escapeHtml(bottom.l)}</strong> has the lowest value at <strong>${bottom.v.toLocaleString(undefined, {maximumFractionDigits: 2})}</strong> (${bottomPct.toFixed(1)}% of total).`
        });
    }

    // Variability
    if (stats.cv >= 50) {
        insights.push({
            icon: '⚠️',
            type: 'warning',
            title: 'High variability',
            text: `Coefficient of Variation is <strong>${stats.cv.toFixed(1)}%</strong> — values vary widely. The mean of ${stats.mean.toFixed(2)} may not represent any single category well.`
        });
    } else if (stats.cv <= 15 && stats.count > 2) {
        insights.push({
            icon: '⚖️',
            type: 'info',
            title: 'Low variability',
            text: `CV is just <strong>${stats.cv.toFixed(1)}%</strong> — values are tightly clustered around the mean (${stats.mean.toFixed(2)}). Consistent performance across categories.`
        });
    }

    // Outliers
    const outlierCount = values.filter(v => stats.stdDev > 0 && Math.abs(v - stats.mean) > 2 * stats.stdDev).length;
    if (outlierCount > 0) {
        insights.push({
            icon: '🚨',
            type: 'error',
            title: 'Outliers detected',
            text: `<strong>${outlierCount}</strong> categor${outlierCount === 1 ? 'y' : 'ies'} fall more than 2 standard deviations from the mean — worth investigating.`
        });
    }

    // Median vs Mean skew
    const skewPct = stats.mean !== 0 ? ((stats.mean - stats.median) / Math.abs(stats.mean)) * 100 : 0;
    if (Math.abs(skewPct) > 15) {
        const dir = skewPct > 0 ? 'right (high-value outliers pull the mean up)' : 'left (low-value outliers pull the mean down)';
        insights.push({
            icon: '📐',
            type: 'info',
            title: 'Skewed distribution',
            text: `Distribution is skewed to the <strong>${dir}</strong>. Median (${stats.median.toFixed(2)}) is a better central measure than mean (${stats.mean.toFixed(2)}).`
        });
    }

    // Percentile insight
    const aboveMean = values.filter(v => v > stats.mean).length;
    insights.push({
        icon: '📌',
        type: 'info',
        title: 'Above average',
        text: `<strong>${aboveMean}</strong> out of <strong>${n}</strong> categories (${(aboveMean/n*100).toFixed(0)}%) perform above the mean.`
    });

    const list = document.getElementById('insightsList');
    list.innerHTML = insights.map(i => `
        <div class="insight-item ${i.type}">
            <span class="insight-icon">${i.icon}</span>
            <div class="insight-body">
                <strong>${i.title}</strong>
                <p>${i.text}</p>
            </div>
        </div>
    `).join('');
}

function renderDataTable(labels, values, xField, yField, agg, stats) {
    const tbody = document.querySelector('#chartDataTable tbody');
    const total = stats.total;
    document.getElementById('thCategory').textContent = xField;
    document.getElementById('thValue').textContent = `${capitalize(agg)} of ${yField}`;
    document.getElementById('dataTableBadge').textContent = labels.length + ' rows';

    // Preserve sort state
    const key = tableSortState.key;
    const dir = tableSortState.dir;
    let rows = labels.map((label, i) => ({
        cat: label,
        val: values[i],
        pct: total !== 0 ? (values[i] / total) * 100 : 0,
        dev: values[i] - stats.mean,
        idx: i
    }));

    if (key === 'val') rows.sort((a, b) => dir === 'desc' ? b.val - a.val : a.val - b.val);
    else if (key === 'pct') rows.sort((a, b) => dir === 'desc' ? b.pct - a.pct : a.pct - b.pct);
    else if (key === 'cat') rows.sort((a, b) => dir === 'desc' ? String(b.cat).localeCompare(String(a.cat)) : String(a.cat).localeCompare(String(b.cat)));

    tbody.innerHTML = '';
    rows.forEach((r, i) => {
        const devClass = r.dev > 0 ? 'positive' : r.dev < 0 ? 'negative' : '';
        const devSign = r.dev > 0 ? '+' : '';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="row-num">${i + 1}</td>
            <td>${escapeHtml(String(r.cat))}</td>
            <td class="num-col">${r.val.toLocaleString(undefined, { maximumFractionDigits: 2 })}</td>
            <td class="num-col">${r.pct.toFixed(1)}%</td>
            <td class="num-col ${devClass}">${devSign}${r.dev.toLocaleString(undefined, { maximumFractionDigits: 2 })}</td>
        `;
        tbody.appendChild(tr);
    });
}

function sortTable(key) {
    if (tableSortState.key === key) {
        tableSortState.dir = tableSortState.dir === 'desc' ? 'asc' : 'desc';
    } else {
        tableSortState.key = key;
        tableSortState.dir = 'desc';
    }
    if (lastChartData) {
        const stats = computeStats(lastChartData.values);
        renderDataTable(lastChartData.labels, lastChartData.values, lastChartData.xField, lastChartData.yField, lastChartData.agg, stats);
    }
}

function updateSubtitle(xField, yField, agg, type) {
    const map = { bar:'Bar', horizontalBar:'Horizontal Bar', line:'Line', area:'Area', pie:'Pie', doughnut:'Doughnut', radar:'Radar', polarArea:'Polar Area' };
    document.getElementById('previewSubtitle').innerHTML =
        `<strong>${map[type] || type}</strong> · ${capitalize(agg)} of <strong>${escapeHtml(yField)}</strong> by <strong>${escapeHtml(xField)}</strong>`;
}

function setStatus(text, mode) {
    const badge = document.getElementById('statusBadge');
    badge.textContent = text;
    badge.className = 'badge ' + (mode === 'ready' ? 'green' : mode === 'error' ? 'red' : mode === 'loading' ? 'blue' : 'blue');
}

function downloadChart() {
    if (!chartInstance) return;
    const canvas = document.getElementById('builderChart');
    const tmp = document.createElement('canvas');
    tmp.width = canvas.width;
    tmp.height = canvas.height;
    const ctx = tmp.getContext('2d');
    ctx.fillStyle = '#0b1120';
    ctx.fillRect(0, 0, tmp.width, tmp.height);
    ctx.drawImage(canvas, 0, 0);

    const link = document.createElement('a');
    link.download = `nisr_chart_${Date.now()}.png`;
    link.href = tmp.toDataURL('image/png');
    link.click();
    showToast('Chart downloaded!', 'success');
}

function downloadChartData() {
    if (!lastChartData) return;
    const { labels, values, xField, yField, agg } = lastChartData;
    const stats = computeStats(values);

    let csv = `"${xField}","${capitalize(agg)} of ${yField}","Percent","Dev from Mean"\n`;
    labels.forEach((label, i) => {
        const val = values[i];
        const pct = stats.total !== 0 ? ((val / stats.total) * 100).toFixed(2) : '0';
        const dev = (val - stats.mean).toFixed(2);
        csv += `"${String(label).replace(/"/g, '""')}",${val},${pct}%,${dev}\n`;
    });

    csv += `\n"STATISTICS"\n`;
    csv += `"Count",${stats.count}\n`;
    csv += `"Total",${stats.total}\n`;
    csv += `"Mean",${stats.mean.toFixed(4)}\n`;
    csv += `"Median",${stats.median.toFixed(4)}\n`;
    csv += `"Std Dev",${stats.stdDev.toFixed(4)}\n`;
    csv += `"CV %",${stats.cv.toFixed(2)}\n`;
    csv += `"Q1",${stats.q1}\n`;
    csv += `"Q3",${stats.q3}\n`;
    csv += `"Range",${stats.range}\n`;
    csv += `"Min",${stats.min}\n`;
    csv += `"Max",${stats.max}\n`;

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `nisr_analysis_${Date.now()}.csv`;
    a.click();
    URL.revokeObjectURL(url);
    showToast('Data + statistics exported as CSV!', 'success');
}

function toggleFullscreen() {
    const el = document.getElementById('chartContainer');
    if (!document.fullscreenElement) el.requestFullscreen?.();
    else document.exitFullscreen?.();
}

function resetChart() {
    document.getElementById('chartForm').reset();
    document.getElementById('chartTitle').value = '';
    document.getElementById('chartPalette').value = 'emerald';
    document.getElementById('chartLegend').value = 'top';
    document.getElementById('chartTension').value = '0.4';
    document.getElementById('chartHeight').value = '450';
    document.getElementById('chartSort').value = 'none';
    document.getElementById('chartOutliers').value = 'off';
    document.getElementById('optGrid').checked = true;
    document.getElementById('optDataLabels').checked = false;
    document.getElementById('optAverageLine').checked = false;
    document.getElementById('optAnimate').checked = true;
    ['downloadPngBtn','downloadCsvBtn'].forEach(id => document.getElementById(id).disabled = true);
    ['chartStatsCard','chartDataCard','insightsCard'].forEach(id => document.getElementById(id).style.display = 'none');
    document.getElementById('emptyChartState').style.display = '';
    if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
    lastChartData = null;
    setStatus('Ready', 'ready');
    showToast('Form reset.', 'success');
}

function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

function hexToRgba(hex, alpha) {
    const h = hex.replace('#', '');
    return `rgba(${parseInt(h.substring(0,2),16)}, ${parseInt(h.substring(2,4),16)}, ${parseInt(h.substring(4,6),16)}, ${alpha})`;
}

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    setTimeout(() => {
        t.classList.remove('show');
        setTimeout(() => t.remove(), 300);
    }, 2600);
}

document.addEventListener('DOMContentLoaded', () => {
    const xVal = document.getElementById('chartX')?.value;
    const yVal = document.getElementById('chartY')?.value;
    if (xVal && yVal && !xVal.startsWith('—') && !yVal.startsWith('—')) {
        renderChart();
    }
});
</script>

<style>
/* =========================================
   CHART BUILDER — INTELLIGENCE STUDIO
   ========================================= */

.chart-builder-page.dashboard-layout {
    display: flex !important;
    width: 100vw !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
    overflow-x: hidden !important;
}

.chart-builder-page .main-content {
    flex: 1 !important;
    width: calc(100% - 250px) !important;
    max-width: none !important;
    min-width: 0 !important;
    padding: 32px !important;
    box-sizing: border-box !important;
}

.chart-builder-page .glass-card {
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

/* HERO */
.cb-hero {
    background: linear-gradient(135deg, rgba(167, 139, 250, 0.12), rgba(56, 189, 248, 0.08)) !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 24px !important;
    flex-wrap: wrap !important;
    position: relative !important;
    overflow: hidden !important;
}

.cb-hero::before {
    content: '' !important;
    position: absolute !important;
    top: -50% !important; right: -10% !important;
    width: 400px !important; height: 400px !important;
    background: radial-gradient(circle, rgba(167, 139, 250, 0.15), transparent 70%) !important;
    pointer-events: none !important;
}

.hero-content { position: relative !important; z-index: 1 !important; flex: 1 !important; min-width: 280px !important; }

.hero-tag {
    display: inline-block !important;
    padding: 5px 14px !important;
    background: rgba(167, 139, 250, 0.15) !important;
    color: #a78bfa !important;
    border: 1px solid rgba(167, 139, 250, 0.3) !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    margin-bottom: 14px !important;
}

.cb-hero h1 { font-size: 28px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 10px 0 !important; line-height: 1.2 !important; }
.cb-hero p { color: #94a3b8 !important; font-size: 14px !important; line-height: 1.6 !important; margin: 0 !important; max-width: 700px !important; }

.hero-badges { display: flex !important; gap: 10px !important; flex-wrap: wrap !important; position: relative !important; z-index: 1 !important; }
.hero-badge {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    padding: 8px 16px !important; background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 10px !important;
    font-size: 12.5px !important; font-weight: 700 !important; color: #cbd5e1 !important; white-space: nowrap !important;
}

/* WARNING */
.schema-warning {
    display: flex !important; gap: 16px !important; align-items: flex-start !important;
    background: linear-gradient(135deg, rgba(239,68,68,0.12), rgba(220,38,38,0.05)) !important;
    border-left: 4px solid #ef4444 !important;
}
.warning-icon { font-size: 24px !important; flex-shrink: 0 !important; }
.schema-warning strong { display: block !important; font-size: 14px !important; color: #fca5a5 !important; margin-bottom: 6px !important; }
.schema-warning p { font-size: 13px !important; color: #cbd5e1 !important; margin: 0 !important; line-height: 1.5 !important; }
.schema-warning code { background: rgba(0,0,0,0.3) !important; padding: 2px 8px !important; border-radius: 5px !important; font-family: 'Courier New', monospace !important; color: #38bdf8 !important; }

/* PRESETS */
.presets-header { margin-bottom: 16px !important; }
.presets-header h3 { font-size: 16px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 0 4px 0 !important; }
.presets-header p { font-size: 12.5px !important; color: #94a3b8 !important; margin: 0 !important; }

.presets-grid { display: grid !important; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important; gap: 12px !important; }

.preset-btn {
    display: flex !important; align-items: center !important; gap: 12px !important;
    padding: 14px 16px !important; background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 12px !important;
    cursor: pointer !important; transition: all 0.2s ease !important;
    text-align: left !important; color: inherit !important; font-family: inherit !important;
}
.preset-btn:hover {
    background: rgba(56, 189, 248, 0.08) !important;
    border-color: rgba(56, 189, 248, 0.4) !important;
    transform: translateY(-2px) !important;
}
.preset-btn.pareto:hover {
    background: rgba(167, 139, 250, 0.08) !important;
    border-color: rgba(167, 139, 250, 0.4) !important;
}
.preset-icon { font-size: 24px !important; flex-shrink: 0 !important; }
.preset-info { display: flex !important; flex-direction: column !important; gap: 2px !important; min-width: 0 !important; }
.preset-info strong { font-size: 13px !important; font-weight: 700 !important; color: #f1f5f9 !important; }
.preset-info small { font-size: 11px !important; color: #94a3b8 !important; }

/* HEADERS */
.card-header {
    display: flex !important; justify-content: space-between !important; align-items: center !important;
    margin-bottom: 20px !important; padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255,255,255,0.08) !important;
    flex-wrap: wrap !important; gap: 12px !important;
}
.card-header h3 { font-size: 18px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 !important; }
.card-subtitle { font-size: 12.5px !important; color: #94a3b8 !important; margin: 4px 0 0 0 !important; }

.badge {
    display: inline-flex !important; align-items: center !important;
    padding: 5px 12px !important; border-radius: 999px !important;
    font-size: 11px !important; font-weight: 700 !important;
    letter-spacing: 0.5px !important; text-transform: uppercase !important; white-space: nowrap !important;
}
.badge.blue   { background: rgba(56,189,248,0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(56,189,248,0.3) !important; }
.badge.green  { background: rgba(52,211,153,0.15) !important; color: #34d399 !important; border: 1px solid rgba(52,211,153,0.3) !important; }
.badge.red    { background: rgba(248,113,113,0.15) !important; color: #f87171 !important; border: 1px solid rgba(248,113,113,0.3) !important; }
.badge.purple { background: rgba(167,139,250,0.15) !important; color: #a78bfa !important; border: 1px solid rgba(167,139,250,0.3) !important; }

/* TABS */
.builder-tabs {
    display: flex !important; gap: 4px !important; padding: 4px !important;
    background: rgba(0,0,0,0.25) !important;
    border: 1px solid rgba(255,255,255,0.06) !important;
    border-radius: 12px !important; margin-bottom: 22px !important;
    overflow-x: auto !important;
}
.tab-btn {
    flex: 1 !important; min-width: 120px !important; padding: 10px 16px !important;
    background: transparent !important; color: #94a3b8 !important;
    border: none !important; border-radius: 9px !important;
    font-size: 13px !important; font-weight: 700 !important;
    cursor: pointer !important; transition: all 0.2s ease !important;
    white-space: nowrap !important; font-family: inherit !important;
}
.tab-btn:hover { color: #cbd5e1 !important; background: rgba(255,255,255,0.04) !important; }
.tab-btn.active {
    background: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(167,139,250,0.15)) !important;
    color: #38bdf8 !important; box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
}

.tab-panel { display: none !important; }
.tab-panel.active { display: block !important; }

/* FORM */
.chart-controls {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important;
    gap: 16px !important; margin-bottom: 22px !important; width: 100% !important;
}
.form-group { display: flex !important; flex-direction: column !important; gap: 8px !important; min-width: 0 !important; }
.form-group label {
    font-size: 11px !important; font-weight: 700 !important; color: #94a3b8 !important;
    text-transform: uppercase !important; letter-spacing: 0.8px !important;
}
.form-group select,
.form-group input[type="text"] {
    width: 100% !important; background: rgba(0,0,0,0.4) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    border-radius: 10px !important; padding: 12px 40px 12px 14px !important;
    color: #f1f5f9 !important; font-size: 13.5px !important;
    outline: none !important; cursor: pointer !important;
    transition: border-color 0.2s, box-shadow 0.2s !important;
    box-sizing: border-box !important; -webkit-appearance: none !important; appearance: none !important;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 16px !important; font-family: inherit !important;
}
.form-group input[type="text"] {
    padding-right: 14px !important;
    background-image: none !important;
    cursor: text !important;
}
.form-group select option,
.form-group select optgroup { background: #1e293b !important; color: #f1f5f9 !important; }
.form-group select:focus,
.form-group input:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56,189,248,0.2) !important;
}

/* TOGGLES */
.toggles-row {
    display: flex !important; flex-wrap: wrap !important; gap: 16px !important;
    margin-bottom: 8px !important; padding: 16px 20px !important;
    background: rgba(0,0,0,0.2) !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255,255,255,0.06) !important;
}
.toggle-item {
    display: inline-flex !important; align-items: center !important; gap: 10px !important;
    cursor: pointer !important; font-size: 13px !important; color: #cbd5e1 !important;
    font-weight: 500 !important; user-select: none !important;
}
.toggle-item input[type="checkbox"] { position: absolute !important; opacity: 0 !important; pointer-events: none !important; }
.toggle-slider {
    position: relative !important; width: 38px !important; height: 22px !important;
    background: rgba(255,255,255,0.1) !important; border-radius: 999px !important;
    transition: background 0.2s !important; flex-shrink: 0 !important;
}
.toggle-slider::after {
    content: '' !important; position: absolute !important; top: 2px !important; left: 2px !important;
    width: 18px !important; height: 18px !important;
    background: #94a3b8 !important; border-radius: 50% !important;
    transition: all 0.2s ease !important;
}
.toggle-item input:checked + .toggle-slider { background: rgba(56,189,248,0.4) !important; }
.toggle-item input:checked + .toggle-slider::after { left: 18px !important; background: #38bdf8 !important; }
.toggle-label { line-height: 1.3 !important; }

/* BUTTONS */
.builder-actions {
    display: flex !important; gap: 12px !important; flex-wrap: wrap !important;
    padding-top: 20px !important; border-top: 1px solid rgba(255,255,255,0.08) !important;
}
.btn-primary, .btn-secondary {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    gap: 8px !important; padding: 13px 24px !important; border-radius: 10px !important;
    font-size: 13.5px !important; font-weight: 700 !important; cursor: pointer !important;
    transition: all 0.2s ease !important; border: none !important;
    white-space: nowrap !important; font-family: inherit !important;
}
.btn-primary {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important; box-shadow: 0 4px 15px rgba(16,185,129,0.3) !important;
    flex: 1 !important; min-width: 200px !important;
}
.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(16,185,129,0.5) !important;
}
.btn-primary:disabled { opacity: 0.5 !important; cursor: not-allowed !important; background: #334155 !important; box-shadow: none !important; }
.btn-secondary {
    background: rgba(255,255,255,0.05) !important; color: #cbd5e1 !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
}
.btn-secondary:hover:not(:disabled) { background: rgba(255,255,255,0.1) !important; color: #f1f5f9 !important; transform: translateY(-2px) !important; }
.btn-secondary:disabled { opacity: 0.4 !important; cursor: not-allowed !important; }

/* CHART */
.chart-preview-card { position: relative !important; }
.icon-btn-sm {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    padding: 8px 14px !important; background: rgba(255,255,255,0.05) !important;
    color: #cbd5e1 !important; border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 8px !important; font-size: 12px !important; font-weight: 700 !important;
    cursor: pointer !important; transition: all 0.2s ease !important; font-family: inherit !important;
}
.icon-btn-sm:hover {
    background: rgba(56,189,248,0.15) !important;
    color: #38bdf8 !important;
    border-color: rgba(56,189,248,0.4) !important;
}

.chart-container {
    position: relative !important; width: 100% !important; height: 450px !important;
    background: rgba(0,0,0,0.2) !important; border-radius: 12px !important;
    padding: 20px !important; box-sizing: border-box !important;
    transition: height 0.3s ease !important;
}
.chart-container:fullscreen { background: #0b1120 !important; padding: 40px !important; }

.chart-empty-state {
    position: absolute !important; inset: 0 !important;
    display: flex !important; flex-direction: column !important;
    align-items: center !important; justify-content: center !important;
    gap: 10px !important; text-align: center !important; padding: 40px !important;
    pointer-events: none !important;
}
.chart-empty-state .empty-icon { font-size: 56px !important; opacity: 0.5 !important; margin-bottom: 8px !important; }
.chart-empty-state h4 { font-size: 16px !important; font-weight: 700 !important; color: #cbd5e1 !important; margin: 0 !important; }
.chart-empty-state p { font-size: 13px !important; color: #64748b !important; margin: 0 !important; max-width: 400px !important; }

/* INSIGHTS */
.insights-card {
    background: linear-gradient(135deg, rgba(167,139,250,0.08), rgba(56,189,248,0.05)) !important;
    border: 1px solid rgba(167,139,250,0.2) !important;
}
.insights-list { display: flex !important; flex-direction: column !important; gap: 12px !important; }
.insight-item {
    display: flex !important; gap: 14px !important; align-items: flex-start !important;
    padding: 16px 20px !important; border-radius: 12px !important;
    border-left: 4px solid #38bdf8 !important;
    background: rgba(0,0,0,0.25) !important;
    transition: all 0.2s ease !important;
}
.insight-item:hover { background: rgba(0,0,0,0.35) !important; transform: translateX(4px) !important; }
.insight-item.success { border-left-color: #10b981 !important; }
.insight-item.warning { border-left-color: #f59e0b !important; }
.insight-item.error   { border-left-color: #ef4444 !important; }
.insight-item.info    { border-left-color: #38bdf8 !important; }

.insight-icon { font-size: 22px !important; flex-shrink: 0 !important; line-height: 1 !important; }
.insight-body { flex: 1 !important; min-width: 0 !important; }
.insight-body strong {
    display: block !important; font-size: 13.5px !important; font-weight: 700 !important;
    color: #f1f5f9 !important; margin-bottom: 4px !important;
}
.insight-body p { font-size: 13px !important; color: #cbd5e1 !important; margin: 0 !important; line-height: 1.55 !important; }
.insight-body strong,
.insight-body p strong { color: #38bdf8 !important; font-weight: 700 !important; }

/* STATS GRID */
.stats-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) !important;
    gap: 12px !important;
}
.stat-box {
    display: flex !important; flex-direction: column !important; gap: 4px !important;
    padding: 16px 18px !important; background: rgba(0,0,0,0.25) !important;
    border: 1px solid rgba(255,255,255,0.06) !important; border-radius: 12px !important;
    transition: all 0.2s ease !important;
}
.stat-box:hover { background: rgba(56,189,248,0.06) !important; border-color: rgba(56,189,248,0.3) !important; }
.stat-box-label {
    font-size: 10.5px !important; font-weight: 700 !important; color: #94a3b8 !important;
    text-transform: uppercase !important; letter-spacing: 0.8px !important;
}
.stat-box-value {
    font-size: 18px !important; font-weight: 800 !important; color: #f1f5f9 !important;
    line-height: 1.2 !important; font-variant-numeric: tabular-nums !important;
    word-break: break-all !important;
}

/* DATA TABLE */
.data-table-wrapper {
    overflow-x: auto !important; max-height: 500px !important; overflow-y: auto !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255,255,255,0.08) !important;
    background: rgba(0,0,0,0.2) !important;
}
.chart-data-table { width: 100% !important; border-collapse: collapse !important; font-size: 13px !important; min-width: 500px !important; }
.chart-data-table thead th {
    position: sticky !important; top: 0 !important; z-index: 2 !important;
    background: #1e293b !important; color: #94a3b8 !important;
    font-weight: 700 !important; text-transform: uppercase !important;
    letter-spacing: 0.8px !important; font-size: 11px !important;
    padding: 12px 16px !important; text-align: left !important;
    border-bottom: 2px solid rgba(56,189,248,0.25) !important;
    white-space: nowrap !important;
}
.chart-data-table thead th.sortable {
    cursor: pointer !important; user-select: none !important;
    transition: background 0.2s, color 0.2s !important;
}
.chart-data-table thead th.sortable:hover {
    background: #334155 !important; color: #38bdf8 !important;
}
.chart-data-table tbody td {
    padding: 11px 16px !important;
    border-bottom: 1px solid rgba(255,255,255,0.04) !important;
    color: #f1f5f9 !important;
}
.chart-data-table tbody tr:hover td { background: rgba(56,189,248,0.04) !important; }
.chart-data-table tbody tr:last-child td { border-bottom: none !important; }
.chart-data-table .num-col { text-align: right !important; font-variant-numeric: tabular-nums !important; color: #cbd5e1 !important; }
.chart-data-table .num-col.positive { color: #6ee7b7 !important; }
.chart-data-table .num-col.negative { color: #fca5a5 !important; }
.chart-data-table .row-num {
    width: 50px !important; text-align: center !important;
    color: #64748b !important; font-weight: 700 !important;
    background: rgba(0,0,0,0.15) !important;
}

/* TOAST */
.toast {
    position: fixed !important; bottom: 32px !important; right: 32px !important;
    padding: 14px 22px !important; border-radius: 12px !important;
    font-size: 13.5px !important; font-weight: 600 !important;
    color: #f1f5f9 !important; background: #1e293b !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    box-shadow: 0 12px 32px rgba(0,0,0,0.5) !important;
    transform: translateY(20px) !important; opacity: 0 !important;
    transition: all 0.3s ease !important; z-index: 99999 !important;
    max-width: 400px !important;
}
.toast.show { transform: translateY(0) !important; opacity: 1 !important; }
.toast.success {
    background: linear-gradient(135deg, rgba(16,185,129,0.95), rgba(5,150,105,0.95)) !important;
    border-color: rgba(52,211,153,0.5) !important;
    color: #ffffff !important;
}
.toast.error {
    background: linear-gradient(135deg, rgba(239,68,68,0.95), rgba(220,38,38,0.95)) !important;
    border-color: rgba(248,113,113,0.5) !important;
    color: #ffffff !important;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .chart-builder-page .main-content { padding: 16px !important; }
    .chart-builder-page .glass-card { padding: 18px !important; }
    .cb-hero { flex-direction: column !important; align-items: flex-start !important; }
    .cb-hero h1 { font-size: 22px !important; }
    .chart-controls { grid-template-columns: 1fr !important; }
    .builder-actions { flex-direction: column !important; }
    .btn-primary, .btn-secondary { width: 100% !important; }
    .stats-grid { grid-template-columns: 1fr 1fr !important; }
    .chart-container { height: 320px !important; padding: 12px !important; }
    .builder-tabs { overflow-x: auto !important; }
    .tab-btn { min-width: 90px !important; font-size: 12px !important; padding: 8px 12px !important; }
    .toast { left: 16px !important; right: 16px !important; bottom: 16px !important; max-width: none !important; }
    .presets-grid { grid-template-columns: 1fr !important; }
    .insight-item { padding: 14px 16px !important; }
}

@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr !important; }
    .toggles-row { flex-direction: column !important; gap: 10px !important; }
}
</style>