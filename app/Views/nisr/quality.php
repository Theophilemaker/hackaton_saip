<div class="dashboard-layout quality-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <?php 
        // Compute summary stats
        $totalCols = count($data['quality'] ?? []);
        $totalOutliers = 0;
        $sumMissingPct = 0;
        $numericCols = 0;
        $colsWithIssues = 0;
        foreach ($data['quality'] ?? [] as $q) {
            $totalOutliers += $q['outlier_count'] ?? 0;
            $sumMissingPct += $q['missing_pct'] ?? 0;
            if (($q['data_type'] ?? '') === 'number') $numericCols++;
            if (($q['missing_pct'] ?? 0) > 5 || ($q['outlier_count'] ?? 0) > 0) $colsWithIssues++;
        }
        $avgMissing = $totalCols > 0 ? $sumMissingPct / $totalCols : 0;
        ?>

        <!-- ============ HEADER CARD ============ -->
        <section class="glass-card quality-header-card">
            <div class="quality-header-top">
                <div>
                    <a href="<?= BASE_URL ?>/nisr/dataset/<?= $data['dataset']['id'] ?>" class="back-link">
                        ← Back to Dataset
                    </a>
                    <h1 class="quality-title">
                        ✅ Data Quality Report
                    </h1>
                    <p class="quality-dataset-name">
                        <?= e($data['dataset']['name']) ?>
                    </p>
                    <div class="quality-meta">
                        <span class="meta-item">🏢 <?= e($data['dataset']['source'] ?? 'NISR') ?></span>
                        <span class="meta-item">📅 <?= e($data['dataset']['year'] ?? date('Y')) ?></span>
                        <span class="meta-item">📊 <?= number_format($data['dataset']['row_count'] ?? 0) ?> rows</span>
                        <span class="meta-item">📋 <?= $totalCols ?> columns</span>
                    </div>
                </div>
                <div class="quality-header-actions">
                    <a href="<?= BASE_URL ?>/nisr/recomputeQuality/<?= $data['dataset']['id'] ?>" class="btn-action primary">
                        🔄 Recompute
                    </a>
                    <a href="<?= BASE_URL ?>/nisr/exportReport?dataset=<?= $data['dataset']['id'] ?>" target="_blank" class="btn-action">
                        📄 Export PDF
                    </a>
                </div>
            </div>
        </section>

        <!-- ============ SUMMARY KPI CARDS ============ -->
        <section class="kpi-grid">
            <div class="kpi-card columns">
                <div class="kpi-icon">📋</div>
                <div class="kpi-content">
                    <p class="kpi-label">Total Columns</p>
                    <h3 class="kpi-value"><?= $totalCols ?></h3>
                </div>
            </div>

            <div class="kpi-card missing">
                <div class="kpi-icon">⚠️</div>
                <div class="kpi-content">
                    <p class="kpi-label">Avg Missing</p>
                    <h3 class="kpi-value"><?= number_format($avgMissing, 1) ?>%</h3>
                </div>
            </div>

            <div class="kpi-card numeric">
                <div class="kpi-icon">🔢</div>
                <div class="kpi-content">
                    <p class="kpi-label">Numeric Columns</p>
                    <h3 class="kpi-value"><?= $numericCols ?></h3>
                </div>
            </div>

            <div class="kpi-card outliers">
                <div class="kpi-icon">🎯</div>
                <div class="kpi-content">
                    <p class="kpi-label">Total Outliers</p>
                    <h3 class="kpi-value"><?= number_format($totalOutliers) ?></h3>
                </div>
            </div>
        </section>

        <!-- ============ EMPTY STATE ============ -->
        <?php if (empty($data['quality'])): ?>
            <section class="glass-card empty-quality">
                <div class="empty-icon">📊</div>
                <h3>No Quality Metrics Available</h3>
                <p>
                    Quality metrics haven't been computed for this dataset yet. 
                    Click the <strong>🔄 Recompute</strong> button above to analyze the data.
                </p>
            </section>
        <?php else: ?>
            <!-- ============ QUALITY TABLE ============ -->
            <section class="glass-card quality-table-card">
                <div class="card-header">
                    <h3>📊 Column-by-Column Analysis</h3>
                    <span class="badge <?= $colsWithIssues > 0 ? 'orange' : 'green' ?>">
                        <?= $colsWithIssues > 0 ? $colsWithIssues . ' column(s) need attention' : 'All columns look healthy' ?>
                    </span>
                </div>

                <div class="table-wrapper">
                    <table class="quality-table">
                        <thead>
                            <tr>
                                <th>Column</th>
                                <th>Type</th>
                                <th class="col-missing">Missing %</th>
                                <th>Unique</th>
                                <th>Min</th>
                                <th>Max</th>
                                <th>Mean</th>
                                <th>Outliers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['quality'] as $q): 
                                $missingPct = (float)($q['missing_pct'] ?? 0);
                                $outliers = (int)($q['outlier_count'] ?? 0);
                                $missingClass = $missingPct > 20 ? 'critical' : ($missingPct > 5 ? 'warning' : 'good');
                                $outlierClass = $outliers > 0 ? 'has-outliers' : '';
                            ?>
                                <tr class="<?= $outlierClass ?>">
                                    <td class="col-name">
                                        <strong><?= e($q['column_name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="type-badge <?= $q['data_type'] === 'number' ? 'numeric' : 'string' ?>">
                                            <?= e($q['data_type']) ?>
                                        </span>
                                    </td>
                                    <td class="col-missing">
                                        <div class="quality-bar-wrap">
                                            <div class="quality-bar">
                                                <div class="quality-fill <?= $missingClass ?>" 
                                                     style="width:<?= min(100, $missingPct) ?>%"></div>
                                            </div>
                                            <span class="quality-pct <?= $missingClass ?>">
                                                <?= number_format($missingPct, 1) ?>%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="num-cell">
                                        <?= number_format((int)$q['unique_count']) ?>
                                    </td>
                                    <td class="num-cell">
                                        <?= $q['min_value'] !== null ? number_format((float)$q['min_value'], 2) : '<span class="empty-val">—</span>' ?>
                                    </td>
                                    <td class="num-cell">
                                        <?= $q['max_value'] !== null ? number_format((float)$q['max_value'], 2) : '<span class="empty-val">—</span>' ?>
                                    </td>
                                    <td class="num-cell">
                                        <?= $q['mean_value'] !== null ? number_format((float)$q['mean_value'], 2) : '<span class="empty-val">—</span>' ?>
                                    </td>
                                    <td class="num-cell">
                                        <?php if ($outliers > 0): ?>
                                            <span class="outlier-badge">🎯 <?= number_format($outliers) ?></span>
                                        <?php else: ?>
                                            <span class="outlier-clean">✓ 0</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Legend -->
                <div class="quality-legend">
                    <span class="legend-item"><span class="dot good"></span> Healthy (&lt;5% missing)</span>
                    <span class="legend-item"><span class="dot warning"></span> Warning (5–20%)</span>
                    <span class="legend-item"><span class="dot critical"></span> Critical (&gt;20%)</span>
                    <span class="legend-item"><span class="dot outlier"></span> Rows with outliers</span>
                </div>
            </section>
        <?php endif; ?>
    </main>
</div>

<style>
/* =========================================
   DATA QUALITY — FULL WIDTH DARK THEME
   ========================================= */

/* 1. FORCE FULL WIDTH LAYOUT */
.quality-page.dashboard-layout {
    display: flex !important;
    width: 100vw !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
    overflow-x: hidden !important;
}

.quality-page .main-content {
    flex: 1 !important;
    width: calc(100% - 250px) !important;
    max-width: none !important;
    min-width: 0 !important;
    padding: 32px !important;
    box-sizing: border-box !important;
}

/* 2. CARD BASE STYLES */
.quality-header-card,
.quality-table-card,
.empty-quality {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 32px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
}

/* 3. HEADER CARD */
.quality-header-top {
    display: flex !important;
    justify-content: space-between !important;
    align-items: flex-start !important;
    gap: 24px !important;
    flex-wrap: wrap !important;
}

.back-link {
    display: inline-block !important;
    color: #38bdf8 !important;
    text-decoration: none !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    margin-bottom: 16px !important;
}
.back-link:hover { text-decoration: underline !important; }

.quality-title {
    font-size: 28px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 0 6px 0 !important;
    line-height: 1.2 !important;
}

.quality-dataset-name {
    font-size: 15px !important;
    color: #38bdf8 !important;
    font-weight: 600 !important;
    margin: 0 0 16px 0 !important;
}

.quality-meta {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
}

.meta-item {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 7px 14px !important;
    background: rgba(255, 255, 255, 0.04) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 999px !important;
    font-size: 12px !important;
    color: #cbd5e1 !important;
    font-weight: 500 !important;
}

.quality-header-actions {
    display: flex !important;
    gap: 10px !important;
    flex-wrap: wrap !important;
}

.btn-action {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 12px 22px !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    background: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
}

.btn-action:hover {
    transform: translateY(-2px) !important;
    background: rgba(56, 189, 248, 0.25) !important;
}

.btn-action.primary {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important;
    border-color: transparent !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
}

.btn-action.primary:hover {
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5) !important;
}

/* 4. KPI CARDS */
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
    top: 0 !important;
    left: 0 !important;
    width: 4px !important;
    height: 100% !important;
}

.kpi-card.columns::before  { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.kpi-card.missing::before  { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.kpi-card.numeric::before  { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.kpi-card.outliers::before { background: linear-gradient(180deg, #f87171, #dc2626) !important; }

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
    font-size: 26px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 !important;
    line-height: 1.1 !important;
    font-variant-numeric: tabular-nums !important;
}

/* 5. TABLE CARD HEADER */
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
    font-size: 18px !important;
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
    white-space: nowrap !important;
}
.badge.green  { background: rgba(52, 211, 153, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(52, 211, 153, 0.3) !important; }
.badge.orange { background: rgba(251, 191, 36, 0.15) !important; color: #fbbf24 !important; border: 1px solid rgba(251, 191, 36, 0.3) !important; }

/* 6. TABLE WRAPPER */
.table-wrapper {
    overflow-x: auto !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: rgba(0, 0, 0, 0.15) !important;
    max-height: 600px !important;
}

/* 7. QUALITY TABLE */
.quality-table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 13px !important;
    min-width: 900px !important;
}

.quality-table thead th {
    position: sticky !important;
    top: 0 !important;
    z-index: 2 !important;
    background: #1e293b !important;
    color: #94a3b8 !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    font-size: 11px !important;
    padding: 14px 16px !important;
    text-align: left !important;
    border-bottom: 2px solid rgba(56, 189, 248, 0.3) !important;
    white-space: nowrap !important;
}

.quality-table thead th.col-missing {
    min-width: 180px !important;
}

.quality-table tbody td {
    padding: 14px 16px !important;
    color: #f1f5f9 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    vertical-align: middle !important;
}

.quality-table tbody tr {
    transition: background 0.15s !important;
}

.quality-table tbody tr:hover td {
    background: rgba(56, 189, 248, 0.04) !important;
}

.quality-table tbody tr.has-outliers td {
    background: rgba(248, 113, 113, 0.04) !important;
}
.quality-table tbody tr.has-outliers:hover td {
    background: rgba(248, 113, 113, 0.08) !important;
}

.col-name strong {
    color: #f1f5f9 !important;
    font-weight: 700 !important;
}

.num-cell {
    text-align: right !important;
    font-variant-numeric: tabular-nums !important;
    color: #cbd5e1 !important;
}

.empty-val {
    color: #475569 !important;
}

/* 8. TYPE BADGE */
.type-badge {
    display: inline-flex !important;
    padding: 4px 10px !important;
    border-radius: 6px !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}
.type-badge.numeric {
    background: rgba(167, 139, 250, 0.15) !important;
    color: #a78bfa !important;
    border: 1px solid rgba(167, 139, 250, 0.3) !important;
}
.type-badge.string {
    background: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.25) !important;
}

/* 9. MISSING BAR */
.quality-bar-wrap {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
}

.quality-bar {
    flex: 1 !important;
    height: 8px !important;
    background: rgba(255, 255, 255, 0.06) !important;
    border-radius: 999px !important;
    overflow: hidden !important;
    min-width: 80px !important;
    max-width: 120px !important;
}

.quality-fill {
    height: 100% !important;
    border-radius: 999px !important;
    transition: width 0.3s ease !important;
}

.quality-fill.good     { background: linear-gradient(90deg, #22c55e, #16a34a) !important; }
.quality-fill.warning  { background: linear-gradient(90deg, #fbbf24, #f59e0b) !important; }
.quality-fill.critical { background: linear-gradient(90deg, #ef4444, #dc2626) !important; }

.quality-pct {
    font-size: 12px !important;
    font-weight: 700 !important;
    font-variant-numeric: tabular-nums !important;
    min-width: 42px !important;
    text-align: right !important;
}
.quality-pct.good     { color: #4ade80 !important; }
.quality-pct.warning  { color: #fbbf24 !important; }
.quality-pct.critical { color: #f87171 !important; }

/* 10. OUTLIER BADGE */
.outlier-badge {
    display: inline-flex !important;
    padding: 4px 10px !important;
    background: rgba(248, 113, 113, 0.15) !important;
    color: #f87171 !important;
    border: 1px solid rgba(248, 113, 113, 0.3) !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    font-variant-numeric: tabular-nums !important;
}

.outlier-clean {
    color: #4ade80 !important;
    font-size: 12px !important;
    font-weight: 600 !important;
}

/* 11. LEGEND */
.quality-legend {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 20px !important;
    margin-top: 20px !important;
    padding-top: 16px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.legend-item {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    font-size: 12px !important;
    color: #94a3b8 !important;
    font-weight: 500 !important;
}

.legend-item .dot {
    width: 10px !important;
    height: 10px !important;
    border-radius: 50% !important;
    display: inline-block !important;
}
.legend-item .dot.good     { background: #22c55e !important; }
.legend-item .dot.warning  { background: #fbbf24 !important; }
.legend-item .dot.critical { background: #ef4444 !important; }
.legend-item .dot.outlier  { background: #f87171 !important; }

/* 12. EMPTY STATE */
.empty-quality {
    text-align: center !important;
    padding: 70px 24px !important;
}

.empty-quality .empty-icon {
    font-size: 60px !important;
    margin-bottom: 16px !important;
    opacity: 0.6 !important;
}

.empty-quality h3 {
    font-size: 20px !important;
    color: #f1f5f9 !important;
    margin: 0 0 10px 0 !important;
    font-weight: 700 !important;
}

.empty-quality p {
    color: #94a3b8 !important;
    font-size: 14px !important;
    max-width: 500px !important;
    margin: 0 auto !important;
    line-height: 1.6 !important;
}

.empty-quality strong { color: #38bdf8 !important; }

/* 13. RESPONSIVE */
@media (max-width: 768px) {
    .quality-page .main-content { padding: 16px !important; }
    .quality-header-card, .quality-table-card { padding: 20px !important; }
    .quality-header-top { flex-direction: column !important; }
    .quality-title { font-size: 22px !important; }
    .quality-header-actions { width: 100% !important; }
    .btn-action { flex: 1 !important; justify-content: center !important; }
    .kpi-grid { grid-template-columns: 1fr !important; }
    .kpi-value { font-size: 22px !important; }
    .kpi-icon { width: 46px !important; height: 46px !important; font-size: 22px !important; }
    .quality-legend { gap: 12px !important; }
}
</style>