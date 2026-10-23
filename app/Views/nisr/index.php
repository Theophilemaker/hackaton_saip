<div class="dashboard-layout nisr-home-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ============ HERO ============ -->
        <section class="glass-card nisr-hero">
            <div class="hero-content">
                <span class="hero-tag">Official Statistical Workbench</span>
                <h1>📊 NISR Data Analysis Portal</h1>
                <p>
                    Rwanda's National Institute of Statistics — explore, analyze, and publish 
                    official datasets with powerful analytics and public APIs.
                </p>
                <div class="hero-actions">
                    <a href="<?= BASE_URL ?>/nisr/uploadForm" class="btn-hero-primary">
                        ➕ Upload Dataset
                    </a>
                    <a href="<?= BASE_URL ?>/nisr/apiDocs" class="btn-hero-secondary">
                        🔑 Public API
                    </a>
                </div>
            </div>
        </section>

        <!-- ============ KPI CARDS ============ -->
        <section class="kpi-grid">
            <div class="kpi-card datasets">
                <div class="kpi-icon">📁</div>
                <div class="kpi-content">
                    <p class="kpi-label">Total Datasets</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['total'] ?? 0) ?></h3>
                </div>
            </div>

            <div class="kpi-card public">
                <div class="kpi-icon">🌐</div>
                <div class="kpi-content">
                    <p class="kpi-label">Public Datasets</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['public_count'] ?? 0) ?></h3>
                </div>
            </div>

            <div class="kpi-card rows">
                <div class="kpi-icon">📊</div>
                <div class="kpi-content">
                    <p class="kpi-label">Total Rows</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['total_rows'] ?? 0) ?></h3>
                </div>
            </div>

            <div class="kpi-card published">
                <div class="kpi-icon">✅</div>
                <div class="kpi-content">
                    <p class="kpi-label">Published</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['published'] ?? 0) ?></h3>
                </div>
            </div>
        </section>

        <!-- ============ TOOLS GRID ============ -->
        <section class="section-heading-wrap">
            <h3 class="section-heading">🛠️ Analysis Tools</h3>
            <p class="section-subheading">Six powerful tools to explore, visualize, and publish your data.</p>
        </section>

        <section class="tools-grid">
            <a href="<?= BASE_URL ?>/nisr/datasets" class="tool-card registry">
                <div class="tool-icon-wrap">📂</div>
                <div class="tool-info">
                    <strong>Dataset Registry</strong>
                    <small>Browse all NISR datasets</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <a href="<?= BASE_URL ?>/nisr/explorer" class="tool-card explorer">
                <div class="tool-icon-wrap">🔍</div>
                <div class="tool-info">
                    <strong>Statistical Explorer</strong>
                    <small>Pivot tables & cross-tabs</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <a href="<?= BASE_URL ?>/nisr/chartBuilder" class="tool-card charts">
                <div class="tool-icon-wrap">📈</div>
                <div class="tool-info">
                    <strong>Chart Builder</strong>
                    <small>Bar, Line, Pie & Radar charts</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <a href="<?= BASE_URL ?>/nisr/compare" class="tool-card compare">
                <div class="tool-icon-wrap">⚖️</div>
                <div class="tool-info">
                    <strong>Comparison Tool</strong>
                    <small>District vs District, Year vs Year</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <a href="<?= BASE_URL ?>/nisr/timeseries" class="tool-card timeseries">
                <div class="tool-icon-wrap">📉</div>
                <div class="tool-info">
                    <strong>Time Series</strong>
                    <small>Trends & moving averages</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>

            <a href="<?= BASE_URL ?>/nisr/publications" class="tool-card publications">
                <div class="tool-icon-wrap">📰</div>
                <div class="tool-info">
                    <strong>Publications</strong>
                    <small>Statistical yearbook reports</small>
                </div>
                <span class="tool-arrow">→</span>
            </a>
        </section>

        <!-- ============ RECENT DATASETS ============ -->
        <section class="glass-card recent-card">
            <div class="card-header">
                <div>
                    <h3>📁 Recent Datasets</h3>
                    <p class="card-subtitle">
                        The most recently uploaded datasets on the portal
                    </p>
                </div>
                <a href="<?= BASE_URL ?>/nisr/datasets" class="btn-view-all">
                    View All →
                </a>
            </div>

            <?php if (empty($data['datasets'])): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>No Datasets Yet</h3>
                    <p>Upload your first dataset to get started with the NISR analytics tools.</p>
                    <a href="<?= BASE_URL ?>/nisr/uploadForm" class="btn-hero-primary">
                        ➕ Upload Dataset
                    </a>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="recent-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Year</th>
                                <th>Rows</th>
                                <th>Status</th>
                                <th class="actions-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach (array_slice($data['datasets'], 0, 8) as $d): 
                            $statusClass = $d['status'] === 'published' ? 'published' : 'pending';
                        ?>
                            <tr>
                                <td class="name-cell">
                                    <strong><?= e($d['name']) ?></strong>
                                </td>
                                <td>
                                    <span class="type-badge">
                                        <?= strtoupper(str_replace('_', ' ', $d['survey_type'])) ?>
                                    </span>
                                </td>
                                <td class="num-cell"><?= (int)$d['year'] ?></td>
                                <td class="num-cell"><?= number_format((int)$d['row_count']) ?></td>
                                <td>
                                    <span class="status-pill <?= $statusClass ?>">
                                        <?= ucfirst($d['status']) ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <a href="<?= BASE_URL ?>/nisr/dataset/<?= $d['id'] ?>" class="action-link view">
                                        👁 View
                                    </a>
                                    <a href="<?= BASE_URL ?>/nisr/explorer/<?= $d['id'] ?>" class="action-link explore">
                                        🔍 Explore
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<style>
/* =========================================
   NISR HOME PORTAL — FULL WIDTH DARK THEME
   ========================================= */

/* 1. FORCE FULL WIDTH LAYOUT */
.nisr-home-page.dashboard-layout {
    display: flex !important;
    width: 100vw !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
    overflow-x: hidden !important;
}

.nisr-home-page .main-content {
    flex: 1 !important;
    width: calc(100% - 250px) !important;
    max-width: none !important;
    min-width: 0 !important;
    padding: 32px !important;
    box-sizing: border-box !important;
}

/* 2. CARD BASE */
.nisr-home-page .glass-card {
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
.nisr-hero {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08)) !important;
    position: relative !important;
    overflow: hidden !important;
}

.nisr-hero::before {
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
    max-width: 800px !important;
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

.nisr-hero h1 {
    font-size: 30px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 0 10px 0 !important;
    line-height: 1.2 !important;
}

.nisr-hero p {
    color: #94a3b8 !important;
    font-size: 14px !important;
    line-height: 1.6 !important;
    margin: 0 0 24px 0 !important;
    max-width: 620px !important;
}

.hero-actions {
    display: flex !important;
    gap: 12px !important;
    flex-wrap: wrap !important;
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
    cursor: pointer !important;
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
    cursor: pointer !important;
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
    padding: 24px !important;
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
    top: 0 !important;
    left: 0 !important;
    width: 4px !important;
    height: 100% !important;
}

.kpi-card.datasets::before  { background: linear-gradient(180deg, #10b981, #22c55e) !important; }
.kpi-card.public::before    { background: linear-gradient(180deg, #0ea5e9, #6366f1) !important; }
.kpi-card.rows::before      { background: linear-gradient(180deg, #f59e0b, #ef4444) !important; }
.kpi-card.published::before { background: linear-gradient(180deg, #84cc16, #16a34a) !important; }

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

/* 5. SECTION HEADING */
.section-heading-wrap {
    margin: 32px 0 20px 0 !important;
}

.section-heading {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    margin: 0 0 6px 0 !important;
}

.section-subheading {
    font-size: 13px !important;
    color: #94a3b8 !important;
    margin: 0 !important;
}

/* 6. TOOLS GRID */
.tools-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
    gap: 20px !important;
    margin-bottom: 32px !important;
}

.tool-card {
    display: flex !important;
    align-items: center !important;
    gap: 18px !important;
    padding: 22px 24px !important;
    background: rgba(30, 41, 59, 0.55) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 16px !important;
    text-decoration: none !important;
    color: inherit !important;
    transition: all 0.25s ease !important;
    cursor: pointer !important;
    position: relative !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
}

.tool-card::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 4px !important;
    height: 100% !important;
    transition: width 0.3s ease !important;
}

/* Color accents per tool */
.tool-card.registry::before     { background: linear-gradient(180deg, #10b981, #059669) !important; }
.tool-card.explorer::before     { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.tool-card.charts::before       { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.tool-card.compare::before      { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.tool-card.timeseries::before   { background: linear-gradient(180deg, #f472b6, #be185d) !important; }
.tool-card.publications::before { background: linear-gradient(180deg, #f87171, #dc2626) !important; }

.tool-card:hover {
    transform: translateY(-4px) !important;
    background: rgba(30, 41, 59, 0.8) !important;
    border-color: rgba(56, 189, 248, 0.4) !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4) !important;
}

.tool-card:hover::before { width: 6px !important; }

.tool-icon-wrap {
    width: 52px !important;
    height: 52px !important;
    border-radius: 14px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 26px !important;
    flex-shrink: 0 !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.tool-info { flex: 1 !important; min-width: 0 !important; }

.tool-info strong {
    display: block !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    margin-bottom: 4px !important;
}

.tool-info small {
    display: block !important;
    font-size: 12px !important;
    color: #94a3b8 !important;
    line-height: 1.4 !important;
}

.tool-arrow {
    font-size: 20px !important;
    color: #64748b !important;
    transition: transform 0.25s ease, color 0.25s ease !important;
    flex-shrink: 0 !important;
}

.tool-card:hover .tool-arrow {
    transform: translateX(4px) !important;
    color: #38bdf8 !important;
}

/* 7. RECENT DATASETS CARD */
.recent-card {
    margin-top: 0 !important;
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
    font-size: 18px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    margin: 0 !important;
}

.card-subtitle {
    font-size: 12px !important;
    color: #94a3b8 !important;
    margin: 4px 0 0 0 !important;
}

.btn-view-all {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 8px 16px !important;
    background: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    border-radius: 8px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
}
.btn-view-all:hover {
    background: rgba(56, 189, 248, 0.25) !important;
    transform: translateY(-1px) !important;
}

/* 8. RECENT TABLE */
.table-wrapper {
    overflow-x: auto !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: rgba(0, 0, 0, 0.15) !important;
}

.recent-table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 13px !important;
    min-width: 700px !important;
}

.recent-table thead th {
    background: #1e293b !important;
    color: #94a3b8 !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    font-size: 11px !important;
    padding: 14px 16px !important;
    text-align: left !important;
    border-bottom: 2px solid rgba(56, 189, 248, 0.2) !important;
    white-space: nowrap !important;
}

.recent-table tbody td {
    padding: 14px 16px !important;
    color: #f1f5f9 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    vertical-align: middle !important;
}

.recent-table tbody tr {
    transition: background 0.15s !important;
}
.recent-table tbody tr:hover td {
    background: rgba(56, 189, 248, 0.04) !important;
}
.recent-table tbody tr:last-child td {
    border-bottom: none !important;
}

.name-cell strong {
    color: #f1f5f9 !important;
    font-weight: 700 !important;
}

.num-cell {
    text-align: right !important;
    font-variant-numeric: tabular-nums !important;
    color: #cbd5e1 !important;
}

.type-badge {
    display: inline-flex !important;
    padding: 4px 10px !important;
    border-radius: 6px !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    background: rgba(167, 139, 250, 0.15) !important;
    color: #a78bfa !important;
    border: 1px solid rgba(167, 139, 250, 0.3) !important;
    white-space: nowrap !important;
}

.status-pill {
    display: inline-flex !important;
    padding: 4px 12px !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: capitalize !important;
    letter-spacing: 0.3px !important;
}

.status-pill.published {
    background: rgba(52, 211, 153, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(52, 211, 153, 0.3) !important;
}

.status-pill.pending {
    background: rgba(251, 191, 36, 0.15) !important;
    color: #fbbf24 !important;
    border: 1px solid rgba(251, 191, 36, 0.3) !important;
}

.actions-cell {
    display: flex !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
}

.action-link {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    padding: 6px 12px !important;
    border-radius: 8px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
}

.action-link.view {
    background: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
}
.action-link.view:hover {
    background: rgba(56, 189, 248, 0.25) !important;
    transform: translateY(-1px) !important;
}

.action-link.explore {
    background: rgba(16, 185, 129, 0.12) !important;
    color: #34d399 !important;
    border: 1px solid rgba(16, 185, 129, 0.3) !important;
}
.action-link.explore:hover {
    background: rgba(16, 185, 129, 0.25) !important;
    transform: translateY(-1px) !important;
}

/* 9. EMPTY STATE */
.empty-state {
    text-align: center !important;
    padding: 60px 24px !important;
}

.empty-state .empty-icon {
    font-size: 56px !important;
    margin-bottom: 16px !important;
    opacity: 0.6 !important;
}

.empty-state h3 {
    font-size: 18px !important;
    color: #f1f5f9 !important;
    margin: 0 0 8px 0 !important;
    font-weight: 700 !important;
}

.empty-state p {
    color: #94a3b8 !important;
    font-size: 14px !important;
    margin: 0 0 20px 0 !important;
    max-width: 480px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    line-height: 1.6 !important;
}

/* 10. RESPONSIVE */
@media (max-width: 768px) {
    .nisr-home-page .main-content { padding: 16px !important; }
    .nisr-home-page .glass-card { padding: 20px !important; }
    .nisr-hero h1 { font-size: 22px !important; }
    .hero-actions { flex-direction: column !important; }
    .btn-hero-primary, .btn-hero-secondary { width: 100% !important; justify-content: center !important; }
    .kpi-grid { grid-template-columns: 1fr 1fr !important; gap: 12px !important; }
    .kpi-card { padding: 18px !important; gap: 12px !important; }
    .kpi-icon { width: 44px !important; height: 44px !important; font-size: 22px !important; }
    .kpi-value { font-size: 20px !important; }
    .kpi-label { font-size: 10px !important; }
    .tools-grid { grid-template-columns: 1fr !important; }
    .card-header { flex-direction: column !important; align-items: flex-start !important; }
    .btn-view-all { width: 100% !important; justify-content: center !important; }
    .actions-cell { flex-direction: column !important; }
    .action-link { justify-content: center !important; }
}

@media (max-width: 480px) {
    .kpi-grid { grid-template-columns: 1fr !important; }
}
</style>