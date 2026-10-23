<div class="dashboard-layout explorer-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ============ STATISTICAL EXPLORER FORM ============ -->
        <section class="glass-card explorer-card">
            <div class="card-header">
                <h3>🔍 Statistical Explorer</h3>
                <span class="badge blue">Pivot Tables</span>
            </div>

            <p class="explorer-intro">
                Cross-tabulate your dataset. Choose a <strong>Row Field</strong>, <strong>Column Field</strong>, 
                and a <strong>Value Field</strong> to aggregate (e.g., Sum of yield by district and season).
            </p>

            <form method="GET" class="explorer-form" id="explorerForm">
                <div class="explorer-controls">
                    <div class="form-group">
                        <label>Dataset</label>
                        <select name="dataset" onchange="window.location='<?= BASE_URL ?>/nisr/explorer/'+this.value">
                            <?php foreach ($data['datasets'] as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($data['dataset'] ?? null) && $data['dataset']['id'] == $d['id'] ? 'selected' : '' ?>>
                                    <?= e($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Row Field</label>
                        <select name="row">
                            <option value="">— Select —</option>
                            <?php foreach ($data['schema'] as $c): ?>
                                <option value="<?= e($c['name']) ?>" <?= ($data['row_field'] ?? '') === $c['name'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Column Field</label>
                        <select name="col">
                            <option value="">— Select —</option>
                            <?php foreach ($data['schema'] as $c): ?>
                                <option value="<?= e($c['name']) ?>" <?= ($data['col_field'] ?? '') === $c['name'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Value Field</label>
                        <select name="val">
                            <option value="">— Select —</option>
                            <?php foreach ($data['schema'] as $c): ?>
                                <option value="<?= e($c['name']) ?>" <?= ($data['val_field'] ?? '') === $c['name'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?> (<?= e($c['type'] ?? 'string') ?>)
                                </option>
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
                        <button class="btn-run" id="runBtn">▶ Run Pivot</button>
                    </div>
                </div>
            </form>
        </section>

        <!-- ============ EMPTY STATE ============ -->
        <?php if (!$data['pivot']): ?>
            <section class="glass-card empty-explorer">
                <div class="empty-icon">📊</div>
                <h3>No Pivot Generated Yet</h3>
                <p>Select a Row Field, Column Field, and Value Field above, then click <strong>Run Pivot</strong> to see your cross-tabulated results here.</p>
            </section>
        <?php endif; ?>

        <!-- ============ PIVOT TABLE RESULT ============ -->
        <?php if ($data['pivot']): ?>
            <section class="glass-card pivot-result-card">
                <div class="card-header">
                    <div>
                        <h3>📊 Pivot Table Result</h3>
                        <p class="pivot-subtitle">
                            <strong><?= e($data['row_field']) ?></strong> × 
                            <strong><?= e($data['col_field']) ?></strong> → 
                            <strong><?= e($data['val_field']) ?></strong> 
                            (<?= strtoupper($data['agg']) ?>)
                        </p>
                    </div>
                    <div class="export-buttons">
                        <button onclick="exportPivot('csv')" class="btn-sm-export csv">
                            📊 CSV
                        </button>
                        <button onclick="exportPivot('excel')" class="btn-sm-export excel">
                            📗 Excel
                        </button>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="pivot-table">
                        <thead>
                            <tr>
                                <th class="corner-cell">
                                    <span><?= e($data['row_field']) ?></span>
                                    <span class="corner-divider">\</span>
                                    <span><?= e($data['col_field']) ?></span>
                                </th>
                                <?php foreach ($data['pivot']['columns'] as $c): ?>
                                    <th><?= e($c) ?></th>
                                <?php endforeach; ?>
                                <th class="total-col">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grandTotal = 0;
                            foreach ($data['pivot']['rows'] as $r): 
                                $rowTotal = 0;
                            ?>
                                <tr>
                                    <td class="row-label"><strong><?= e($r) ?></strong></td>
                                    <?php foreach ($data['pivot']['columns'] as $c): 
                                        $v = $data['pivot']['data'][$r][$c] ?? 0;
                                        $rowTotal += $v;
                                        $grandTotal += $v;
                                    ?>
                                        <td><?= number_format($v, 2) ?></td>
                                    <?php endforeach; ?>
                                    <td class="row-total"><?= number_format($rowTotal, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="total-label">TOTAL</td>
                                <?php 
                                $colTotals = [];
                                foreach ($data['pivot']['columns'] as $c) {
                                    $colTotal = 0;
                                    foreach ($data['pivot']['rows'] as $r) {
                                        $colTotal += $data['pivot']['data'][$r][$c] ?? 0;
                                    }
                                    $colTotals[$c] = $colTotal;
                                }
                                foreach ($data['pivot']['columns'] as $c): ?>
                                    <td class="col-total"><?= number_format($colTotals[$c], 2) ?></td>
                                <?php endforeach; ?>
                                <td class="grand-total"><?= number_format($grandTotal, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </main>
</div>

<script>
function exportPivot(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('format', format);
    window.location = '<?= BASE_URL ?>/nisr/exportPivot?' + params.toString();
}

// Loading state on form submit
document.getElementById('explorerForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('runBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '⏳ Running...';
    }
});
</script>

<style>
/* =========================================
   STATISTICAL EXPLORER — FULL WIDTH DARK THEME
   ========================================= */

/* 1. FORCE FULL WIDTH LAYOUT */
.explorer-page.dashboard-layout {
    display: flex !important;
    width: 100vw !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
    overflow-x: hidden !important;
}

.explorer-page .main-content {
    flex: 1 !important;
    width: calc(100% - 250px) !important;
    max-width: none !important;
    min-width: 0 !important;
    padding: 32px !important;
    box-sizing: border-box !important;
}

/* 2. CARD BASE STYLES */
.explorer-card,
.pivot-result-card,
.empty-explorer {
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

.badge.blue {
    display: inline-flex !important;
    padding: 5px 12px !important;
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 0.5px !important;
    text-transform: uppercase !important;
}

/* 3. INTRO TEXT */
.explorer-intro {
    color: #94a3b8 !important;
    font-size: 14px !important;
    line-height: 1.6 !important;
    margin: 0 0 24px 0 !important;
}
.explorer-intro strong {
    color: #38bdf8 !important;
}

/* 4. FORM CONTROLS GRID (RESPONSIVE) */
.explorer-controls {
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

/* 6. EMPTY STATE */
.empty-explorer {
    text-align: center !important;
    padding: 60px 24px !important;
}

.empty-icon {
    font-size: 56px !important;
    margin-bottom: 12px !important;
    opacity: 0.6 !important;
}

.empty-explorer h3 {
    font-size: 18px !important;
    color: #f1f5f9 !important;
    margin: 0 0 8px 0 !important;
}

.empty-explorer p {
    color: #94a3b8 !important;
    font-size: 14px !important;
    max-width: 500px !important;
    margin: 0 auto !important;
    line-height: 1.6 !important;
}

/* 7. PIVOT TABLE RESULT */
.pivot-subtitle {
    font-size: 13px !important;
    color: #94a3b8 !important;
    margin: 6px 0 0 0 !important;
}
.pivot-subtitle strong { color: #38bdf8 !important; }

.export-buttons {
    display: flex !important;
    gap: 8px !important;
}

.btn-sm-export {
    padding: 8px 16px !important;
    border-radius: 8px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    border: 1px solid transparent !important;
    transition: all 0.2s !important;
    white-space: nowrap !important;
}
.btn-sm-export.csv {
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border-color: rgba(56, 189, 248, 0.3) !important;
}
.btn-sm-export.csv:hover { background: rgba(56, 189, 248, 0.3) !important; }
.btn-sm-export.excel {
    background: rgba(34, 197, 94, 0.15) !important;
    color: #22c55e !important;
    border-color: rgba(34, 197, 94, 0.3) !important;
}
.btn-sm-export.excel:hover { background: rgba(34, 197, 94, 0.3) !important; }

/* 8. PIVOT TABLE STYLING */
.table-wrapper {
    overflow-x: auto !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: rgba(0, 0, 0, 0.15) !important;
    max-height: 600px !important;
}

.pivot-table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 13px !important;
    min-width: 500px !important;
}

.pivot-table thead th {
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
    text-align: center !important;
    border-bottom: 2px solid rgba(56, 189, 248, 0.3) !important;
    white-space: nowrap !important;
}

.pivot-table .corner-cell {
    text-align: left !important;
    color: #38bdf8 !important;
    background: #162031 !important;
    position: sticky !important;
    left: 0 !important;
    top: 0 !important;
    z-index: 3 !important;
    font-size: 11px !important;
    min-width: 150px !important;
}

.corner-divider {
    color: #64748b !important;
    padding: 0 6px !important;
}

.pivot-table tbody td {
    padding: 12px 16px !important;
    text-align: right !important;
    color: #f1f5f9 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    font-variant-numeric: tabular-nums !important;
    transition: background 0.15s !important;
}

.pivot-table tbody td.row-label {
    text-align: left !important;
    color: #f1f5f9 !important;
    background: #162031 !important;
    position: sticky !important;
    left: 0 !important;
    z-index: 1 !important;
    border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
    min-width: 150px !important;
}

.pivot-table tbody tr:hover td {
    background: rgba(56, 189, 248, 0.06) !important;
}
.pivot-table tbody tr:hover td.row-label {
    background: #1e2e4a !important;
}

.pivot-table .total-col {
    background: rgba(251, 191, 36, 0.1) !important;
    color: #fbbf24 !important;
    border-left: 2px solid rgba(251, 191, 36, 0.3) !important;
}

.pivot-table .row-total {
    background: rgba(251, 191, 36, 0.08) !important;
    color: #fbbf24 !important;
    font-weight: 700 !important;
    border-left: 2px solid rgba(251, 191, 36, 0.3) !important;
}

/* Footer / Grand total */
.pivot-table tfoot td {
    padding: 14px 16px !important;
    background: #1e293b !important;
    font-weight: 800 !important;
    color: #38bdf8 !important;
    text-align: right !important;
    border-top: 2px solid rgba(56, 189, 248, 0.3) !important;
    position: sticky !important;
    bottom: 0 !important;
    z-index: 1 !important;
    font-variant-numeric: tabular-nums !important;
}

.pivot-table tfoot .total-label {
    text-align: left !important;
    color: #38bdf8 !important;
    background: #162031 !important;
    position: sticky !important;
    left: 0 !important;
    z-index: 2 !important;
    border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
    letter-spacing: 1px !important;
}

.pivot-table tfoot .grand-total {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.15)) !important;
    color: #6ee7b7 !important;
    border-left: 2px solid rgba(16, 185, 129, 0.4) !important;
}

/* 9. RESPONSIVE */
@media (max-width: 768px) {
    .explorer-page .main-content {
        padding: 16px !important;
    }
    .explorer-card, .pivot-result-card {
        padding: 20px !important;
    }
    .explorer-controls {
        grid-template-columns: 1fr !important;
    }
    .card-header {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    .export-buttons { width: 100% !important; }
    .btn-sm-export { flex: 1 !important; }
}
</style>