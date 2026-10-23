<?php 
// Compute summary stats from current list
$list = $data['datasets'] ?? [];
$totalCount  = count($list);
$publicCount = 0;
$totalRows   = 0;
$typeCounts  = [];
foreach ($list as $d) {
    if (!empty($d['is_public'])) $publicCount++;
    $totalRows += (int)($d['row_count'] ?? 0);
    $t = $d['survey_type'] ?? 'custom';
    $typeCounts[$t] = ($typeCounts[$t] ?? 0) + 1;
}
$typeCount = count($typeCounts);
?>

<div class="dashboard-layout registry-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ============ HERO ============ -->
        <section class="glass-card registry-hero">
            <div class="hero-content">
                <span class="hero-tag">NISR Data Catalog</span>
                <h1>📂 Dataset Registry</h1>
                <p>
                    Browse, search, and analyze official NISR datasets. 
                    Click any dataset to explore, chart, or generate reports.
                </p>
            </div>
            <a href="<?= BASE_URL ?>/nisr/uploadForm" class="btn-hero-primary">
                ➕ Upload Dataset
            </a>
        </section>

        <!-- ============ SUMMARY KPI CARDS ============ -->
        <section class="kpi-grid">
            <div class="kpi-card total">
                <div class="kpi-icon">📁</div>
                <div class="kpi-content">
                    <p class="kpi-label">Total Datasets</p>
                    <h3 class="kpi-value"><?= number_format($totalCount) ?></h3>
                </div>
            </div>

            <div class="kpi-card public">
                <div class="kpi-icon">🌐</div>
                <div class="kpi-content">
                    <p class="kpi-label">Public</p>
                    <h3 class="kpi-value"><?= number_format($publicCount) ?></h3>
                </div>
            </div>

            <div class="kpi-card rows">
                <div class="kpi-icon">📊</div>
                <div class="kpi-content">
                    <p class="kpi-label">Total Rows</p>
                    <h3 class="kpi-value"><?= number_format($totalRows) ?></h3>
                </div>
            </div>

            <div class="kpi-card types">
                <div class="kpi-icon">🏷️</div>
                <div class="kpi-content">
                    <p class="kpi-label">Survey Types</p>
                    <h3 class="kpi-value"><?= number_format($typeCount) ?></h3>
                </div>
            </div>
        </section>

        <!-- ============ REGISTRY CARD ============ -->
        <section class="glass-card registry-card">
            <div class="card-header">
                <div>
                    <h3>🗂️ All Datasets</h3>
                    <p class="card-subtitle">
                        <?= $totalCount ?> dataset<?= $totalCount !== 1 ? 's' : '' ?> found
                        <?php if (!empty($data['filters']['search'])): ?>
                            for "<strong><?= e($data['filters']['search']) ?></strong>"
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" class="filters-row">
                <div class="search-wrap">
                    <span class="search-icon">🔍</span>
                    <input type="text" name="q" 
                           placeholder="Search datasets by name or description..." 
                           value="<?= e($data['filters']['search'] ?? '') ?>" 
                           class="search-input">
                </div>
                
                <select name="type" class="filter-select">
                    <option value="">All Survey Types</option>
                    <option value="EICV" <?= ($data['filters']['survey_type'] ?? '') === 'EICV' ? 'selected' : '' ?>>EICV</option>
                    <option value="RPHC" <?= ($data['filters']['survey_type'] ?? '') === 'RPHC' ? 'selected' : '' ?>>RPHC</option>
                    <option value="agricultural_survey" <?= ($data['filters']['survey_type'] ?? '') === 'agricultural_survey' ? 'selected' : '' ?>>Agricultural Survey</option>
                    <option value="household_survey" <?= ($data['filters']['survey_type'] ?? '') === 'household_survey' ? 'selected' : '' ?>>Household Survey</option>
                    <option value="census" <?= ($data['filters']['survey_type'] ?? '') === 'census' ? 'selected' : '' ?>>Census</option>
                    <option value="external" <?= ($data['filters']['survey_type'] ?? '') === 'external' ? 'selected' : '' ?>>External</option>
                    <option value="custom" <?= ($data['filters']['survey_type'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom</option>
                </select>
                
                <button type="submit" class="btn-filter">🔍 Filter</button>
                
                <?php if (!empty($data['filters']['search']) || !empty($data['filters']['survey_type'])): ?>
                    <a href="<?= BASE_URL ?>/nisr/datasets" class="btn-clear">✕ Clear</a>
                <?php endif; ?>
            </form>

            <!-- Datasets Grid -->
            <div class="datasets-grid">
                <?php if (empty($data['datasets'])): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <h3>No Datasets Found</h3>
                        <p>
                            <?php if (!empty($data['filters']['search']) || !empty($data['filters']['survey_type'])): ?>
                                No datasets match your current filters. Try adjusting your search or clearing the filters.
                            <?php else: ?>
                                There are no datasets yet. Upload your first dataset to get started.
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($data['filters']['search']) || !empty($data['filters']['survey_type'])): ?>
                            <a href="<?= BASE_URL ?>/nisr/datasets" class="btn-hero-secondary">Clear Filters</a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/nisr/uploadForm" class="btn-hero-primary">➕ Upload Dataset</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($data['datasets'] as $d): 
                        $typeSlug = strtolower(str_replace('_', '-', $d['survey_type'] ?? 'custom'));
                    ?>
                        <a href="<?= BASE_URL ?>/nisr/dataset/<?= $d['id'] ?>" 
                           class="dataset-card type-<?= e($typeSlug) ?>">
                            
                            <div class="ds-header">
                                <span class="ds-type">
                                    <?= strtoupper(str_replace('_', ' ', $d['survey_type'])) ?>
                                </span>
                                <span class="ds-year"><?= (int)$d['year'] ?></span>
                            </div>

                            <h4 class="ds-title"><?= e($d['name']) ?></h4>
                            
                            <p class="ds-desc">
                                <?= e(mb_substr($d['description'] ?? '', 0, 130)) ?><?= mb_strlen($d['description'] ?? '') > 130 ? '…' : '' ?>
                            </p>

                            <div class="ds-footer">
                                <span class="ds-rows">
                                    📊 <?= number_format((int)$d['row_count']) ?> rows
                                </span>
                                <div class="ds-badges">
                                    <?php if (!empty($d['is_public'])): ?>
                                        <span class="badge-public">🌐 PUBLIC</span>
                                    <?php endif; ?>
                                    <span class="view-hint">View →</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<style>
/* =========================================
   DATASET REGISTRY — FULL WIDTH DARK THEME
   ========================================= */

/* 1. FORCE FULL WIDTH LAYOUT */
.registry-page.dashboard-layout {
    display: flex !important;
    width: 100vw !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
    overflow-x: hidden !important;
}

.registry-page .main-content {
    flex: 1 !important;
    width: calc(100% - 250px) !important;
    max-width: none !important;
    min-width: 0 !important;
    padding: 32px !important;
    box-sizing: border-box !important;
}

/* 2. CARD BASE */
.registry-page .glass-card {
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

/* 3. HERO */
.registry-hero {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.12), rgba(16, 185, 129, 0.08)) !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 24px !important;
    flex-wrap: wrap !important;
    position: relative !important;
    overflow: hidden !important;
}

.registry-hero::before {
    content: '' !important;
    position: absolute !important;
    top: -50% !important;
    right: -10% !important;
    width: 400px !important;
    height: 400px !important;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.15), transparent 70%) !important;
    pointer-events: none !important;
}

.hero-content {
    position: relative !important;
    z-index: 1 !important;
    flex: 1 !important;
    min-width: 280px !important;
}

.hero-tag {
    display: inline-block !important;
    padding: 5px 14px !important;
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    margin-bottom: 14px !important;
}

.registry-hero h1 {
    font-size: 28px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 0 10px 0 !important;
    line-height: 1.2 !important;
}

.registry-hero p {
    color: #94a3b8 !important;
    font-size: 14px !important;
    line-height: 1.6 !important;
    margin: 0 !important;
    max-width: 620px !important;
}

.btn-hero-primary {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 13px 26px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
    white-space: nowrap !important;
    position: relative !important;
    z-index: 1 !important;
}
.btn-hero-primary:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5) !important;
}

.btn-hero-secondary {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 13px 26px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #f1f5f9 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
}
.btn-hero-secondary:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    transform: translateY(-2px) !important;
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
    padding: 22px !important;
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 18px !important;
    position: relative !important;
    overflow: hidden !important;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease !important;
    box-sizing: border-box !important;
    margin-bottom: 0 !important;
}

.kpi-card::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important; left: 0 !important;
    width: 4px !important;
    height: 100% !important;
}

.kpi-card.total::before  { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.kpi-card.public::before { background: linear-gradient(180deg, #10b981, #059669) !important; }
.kpi-card.rows::before   { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.kpi-card.types::before  { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }

.kpi-card:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4) !important;
    border-color: rgba(56, 189, 248, 0.3) !important;
}

.kpi-icon {
    width: 50px !important;
    height: 50px !important;
    border-radius: 14px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 24px !important;
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

/* 5. CARD HEADER */
.card-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 20px !important;
    padding-bottom: 16px !important;
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

.card-subtitle {
    font-size: 12.5px !important;
    color: #94a3b8 !important;
    margin: 4px 0 0 0 !important;
}
.card-subtitle strong { color: #38bdf8 !important; }

/* 6. FILTERS ROW */
.filters-row {
    display: flex !important;
    gap: 12px !important;
    margin-bottom: 24px !important;
    flex-wrap: wrap !important;
    align-items: stretch !important;
}

.search-wrap {
    position: relative !important;
    flex: 1 !important;
    min-width: 240px !important;
}

.search-icon {
    position: absolute !important;
    left: 16px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    font-size: 15px !important;
    pointer-events: none !important;
    opacity: 0.7 !important;
}

.search-input {
    width: 100% !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    padding: 13px 16px 13px 46px !important;
    color: #f1f5f9 !important;
    font-size: 14px !important;
    outline: none !important;
    transition: border-color 0.2s, box-shadow 0.2s !important;
    box-sizing: border-box !important;
}
.search-input::placeholder { color: #64748b !important; }
.search-input:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
}

.filter-select {
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    padding: 13px 40px 13px 16px !important;
    color: #f1f5f9 !important;
    font-size: 14px !important;
    outline: none !important;
    cursor: pointer !important;
    min-width: 180px !important;
    transition: border-color 0.2s, box-shadow 0.2s !important;
    -webkit-appearance: none !important;
    appearance: none !important;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 14px center !important;
    background-size: 16px !important;
    box-sizing: border-box !important;
}
.filter-select:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
}
.filter-select option {
    background: #1e293b !important;
    color: #f1f5f9 !important;
}

.btn-filter {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    padding: 13px 24px !important;
    background: linear-gradient(135deg, #0ea5e9, #0284c7) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3) !important;
    white-space: nowrap !important;
}
.btn-filter:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(14, 165, 233, 0.5) !important;
}

.btn-clear {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    padding: 13px 20px !important;
    background: rgba(239, 68, 68, 0.12) !important;
    color: #f87171 !important;
    border: 1px solid rgba(239, 68, 68, 0.3) !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
}
.btn-clear:hover {
    background: rgba(239, 68, 68, 0.25) !important;
    transform: translateY(-2px) !important;
}

/* 7. DATASETS GRID */
.datasets-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)) !important;
    gap: 20px !important;
    width: 100% !important;
}

/* 8. DATASET CARD */
.dataset-card {
    display: flex !important;
    flex-direction: column !important;
    background: rgba(255, 255, 255, 0.03) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 16px !important;
    padding: 22px !important;
    text-decoration: none !important;
    color: inherit !important;
    transition: all 0.25s ease !important;
    height: 100% !important;
    box-sizing: border-box !important;
    position: relative !important;
    overflow: hidden !important;
}

/* Accent bar per type */
.dataset-card::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important; left: 0 !important;
    width: 4px !important;
    height: 100% !important;
    background: linear-gradient(180deg, #38bdf8, #0284c7) !important;
    transition: width 0.3s ease !important;
}

.dataset-card.type-agricultural-survey::before { background: linear-gradient(180deg, #10b981, #059669) !important; }
.dataset-card.type-eicv::before                { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.dataset-card.type-rphc::before                { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.dataset-card.type-household-survey::before    { background: linear-gradient(180deg, #f472b6, #be185d) !important; }
.dataset-card.type-census::before              { background: linear-gradient(180deg, #f87171, #dc2626) !important; }
.dataset-card.type-external::before            { background: linear-gradient(180deg, #22d3ee, #0891b2) !important; }

.dataset-card:hover {
    background: rgba(255, 255, 255, 0.06) !important;
    transform: translateY(-4px) !important;
    border-color: rgba(56, 189, 248, 0.4) !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4) !important;
}

.dataset-card:hover::before {
    width: 6px !important;
}

/* Card Header */
.ds-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 14px !important;
    gap: 10px !important;
}

.ds-type {
    display: inline-flex !important;
    font-size: 10px !important;
    font-weight: 800 !important;
    letter-spacing: 0.8px !important;
    text-transform: uppercase !important;
    padding: 5px 12px !important;
    border-radius: 999px !important;
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    max-width: 70% !important;
}

.ds-year {
    font-size: 12px !important;
    color: #cbd5e1 !important;
    font-weight: 700 !important;
    background: rgba(255, 255, 255, 0.05) !important;
    padding: 4px 10px !important;
    border-radius: 8px !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    flex-shrink: 0 !important;
}

/* Card Title & Description */
.ds-title {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    margin: 0 0 10px 0 !important;
    line-height: 1.35 !important;
    word-break: break-word !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
}

.ds-desc {
    font-size: 13px !important;
    color: #94a3b8 !important;
    line-height: 1.55 !important;
    margin: 0 0 16px 0 !important;
    flex-grow: 1 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 3 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
}

/* Card Footer */
.ds-footer {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding-top: 14px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    margin-top: auto !important;
    gap: 10px !important;
}

.ds-rows {
    font-size: 12px !important;
    color: #cbd5e1 !important;
    font-weight: 600 !important;
    font-variant-numeric: tabular-nums !important;
    white-space: nowrap !important;
}

.ds-badges {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.badge-public {
    display: inline-flex !important;
    font-size: 9.5px !important;
    font-weight: 800 !important;
    letter-spacing: 0.5px !important;
    padding: 4px 9px !important;
    border-radius: 999px !important;
    background: rgba(52, 211, 153, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(52, 211, 153, 0.3) !important;
    white-space: nowrap !important;
}

.view-hint {
    font-size: 11.5px !important;
    font-weight: 700 !important;
    color: #64748b !important;
    transition: color 0.2s ease, transform 0.2s ease !important;
    white-space: nowrap !important;
}

.dataset-card:hover .view-hint {
    color: #38bdf8 !important;
    transform: translateX(2px) !important;
}

/* 9. EMPTY STATE */
.empty-state {
    grid-column: 1 / -1 !important;
    text-align: center !important;
    padding: 70px 24px !important;
}

.empty-state .empty-icon {
    font-size: 60px !important;
    display: block !important;
    margin-bottom: 16px !important;
    opacity: 0.6 !important;
}

.empty-state h3 {
    font-size: 20px !important;
    color: #f1f5f9 !important;
    margin: 0 0 10px 0 !important;
    font-weight: 700 !important;
}

.empty-state p {
    color: #94a3b8 !important;
    font-size: 14px !important;
    max-width: 500px !important;
    margin: 0 auto 24px !important;
    line-height: 1.6 !important;
}

/* 10. RESPONSIVE */
@media (max-width: 768px) {
    .registry-page .main-content { padding: 16px !important; }
    .registry-page .glass-card { padding: 20px !important; }
    .registry-hero { flex-direction: column !important; align-items: flex-start !important; }
    .registry-hero h1 { font-size: 22px !important; }
    .btn-hero-primary { width: 100% !important; justify-content: center !important; }
    .kpi-grid { grid-template-columns: 1fr 1fr !important; gap: 12px !important; }
    .kpi-card { padding: 16px !important; gap: 12px !important; }
    .kpi-icon { width: 42px !important; height: 42px !important; font-size: 20px !important; }
    .kpi-value { font-size: 20px !important; }
    .filters-row { flex-direction: column !important; }
    .search-input, .filter-select, .btn-filter, .btn-clear {
        width: 100% !important;
    }
    .datasets-grid { grid-template-columns: 1fr !important; }
}

@media (max-width: 480px) {
    .kpi-grid { grid-template-columns: 1fr !important; }
}
</style>