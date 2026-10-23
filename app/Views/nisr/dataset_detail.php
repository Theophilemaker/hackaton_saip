<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="dashboard-layout dataset-detail-page">
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ============ BREADCRUMB ============ -->
        <nav class="breadcrumb">
            <a href="<?= BASE_URL ?>/nisr">🏠 Portal</a>
            <span class="sep">›</span>
            <a href="<?= BASE_URL ?>/nisr/datasets">📂 Registry</a>
            <span class="sep">›</span>
            <span class="current"><?= e(mb_substr($data['dataset']['name'], 0, 50)) ?><?= mb_strlen($data['dataset']['name']) > 50 ? '…' : '' ?></span>
        </nav>

        <!-- ============ FLASH MESSAGES ============ -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="flash-message success">
                <span class="flash-icon">✅</span>
                <div class="flash-text"><?= $_SESSION['flash_success'] ?></div>
                <button class="flash-close" onclick="this.parentElement.remove()">✕</button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="flash-message error">
                <span class="flash-icon">⚠️</span>
                <div class="flash-text"><?= $_SESSION['flash_error'] ?></div>
                <button class="flash-close" onclick="this.parentElement.remove()">✕</button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- ============ DATASET HEADER CARD ============ -->
        <div class="glass-card dataset-header-card">
            <div class="header-top-row">
                <div class="dataset-badges">
                    <span class="badge blue">
                        <?= strtoupper(str_replace('_', ' ', $data['dataset']['survey_type'])) ?>
                    </span>
                    <span class="badge orange">📅 <?= (int)$data['dataset']['year'] ?></span>
                    <?php if (!empty($data['dataset']['is_public'])): ?>
                        <span class="badge green">🌐 PUBLIC</span>
                    <?php endif; ?>
                </div>
                <div class="header-actions">
                    <button class="icon-btn" onclick="copyDatasetLink()" title="Copy shareable link">
                        🔗 Share
                    </button>
                </div>
            </div>

            <h1 class="dataset-title"><?= e($data['dataset']['name']) ?></h1>
            
            <?php if (!empty($data['dataset']['description'])): ?>
                <p class="dataset-desc"><?= e($data['dataset']['description']) ?></p>
            <?php endif; ?>

            <div class="dataset-meta">
                <span class="meta-item">🏢 <?= e($data['dataset']['source'] ?? 'NISR') ?></span>
                <span class="meta-item">📊 <?= number_format((int)($data['dataset']['row_count'] ?? 0)) ?> rows</span>
                <span class="meta-item">📋 <?= count($data['schema']) ?> columns</span>
                <span class="meta-item">📅 Uploaded <?= date('M d, Y', strtotime($data['dataset']['created_at'] ?? 'now')) ?></span>
                <?php if (!empty($data['dataset']['file_path'])): ?>
                    <span class="meta-item">📁 <?= strtoupper(pathinfo($data['dataset']['file_path'], PATHINFO_EXTENSION)) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============ DATASET STATS STRIP ============ -->
        <section class="dataset-stats">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <span class="stat-label">Total Rows</span>
                    <span class="stat-value"><?= number_format((int)($data['dataset']['row_count'] ?? 0)) ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <span class="stat-label">Columns</span>
                    <span class="stat-value"><?= count($data['schema']) ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔢</div>
                <div class="stat-info">
                    <span class="stat-label">Numeric Cols</span>
                    <span class="stat-value">
                        <?= count(array_filter($data['schema'], fn($c) => ($c['type'] ?? '') === 'number')) ?>
                    </span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <span class="stat-label">Quality Checked</span>
                    <span class="stat-value">
                        <?= !empty($data['quality']) ? 'Yes' : 'No' ?>
                    </span>
                </div>
            </div>
        </section>

        <!-- ============ ACTION CARDS ============ -->
        <div class="section-header">
            <h3 class="section-heading">🚀 What would you like to do?</h3>
            <p class="section-subheading">Six powerful tools to analyze this dataset end-to-end.</p>
        </div>

        <div class="action-grid">
            <!-- Step 1: Explore -->
            <a href="<?= BASE_URL ?>/nisr/explorer/<?= $data['dataset']['id'] ?>" class="action-card explore">
                <div class="action-step">1</div>
                <div class="action-icon-wrap">🔍</div>
                <div class="action-info">
                    <h4>Explore Data</h4>
                    <p>Pivot tables & cross-tabulations</p>
                </div>
                <span class="action-arrow">→</span>
            </a>

            <!-- Step 2: Charts -->
            <a href="<?= BASE_URL ?>/nisr/chartBuilder/<?= $data['dataset']['id'] ?>" class="action-card charts">
                <div class="action-step">2</div>
                <div class="action-icon-wrap">📊</div>
                <div class="action-info">
                    <h4>Draw Charts</h4>
                    <p>Bar, Line, Pie & Radar visualizations</p>
                </div>
                <span class="action-arrow">→</span>
            </a>

            <!-- Step 3: Compare -->
            <a href="<?= BASE_URL ?>/nisr/compare/<?= $data['dataset']['id'] ?>" class="action-card compare">
                <div class="action-step">3</div>
                <div class="action-icon-wrap">⚖️</div>
                <div class="action-info">
                    <h4>Compare</h4>
                    <p>Compare dimensions across metrics</p>
                </div>
                <span class="action-arrow">→</span>
            </a>

            <!-- Step 4: Time Series -->
            <a href="<?= BASE_URL ?>/nisr/timeseries/<?= $data['dataset']['id'] ?>" class="action-card timeseries">
                <div class="action-step">4</div>
                <div class="action-icon-wrap">📈</div>
                <div class="action-info">
                    <h4>Time Series</h4>
                    <p>Analyze trends over time</p>
                </div>
                <span class="action-arrow">→</span>
            </a>

            <!-- Step 5: Quality -->
            <a href="<?= BASE_URL ?>/nisr/quality/<?= $data['dataset']['id'] ?>" class="action-card quality">
                <div class="action-step">5</div>
                <div class="action-icon-wrap">✅</div>
                <div class="action-info">
                    <h4>Data Quality</h4>
                    <p>Missing values, outliers & stats</p>
                </div>
                <span class="action-arrow">→</span>
            </a>

            <!-- Step 6: Report -->
            <a href="<?= BASE_URL ?>/nisr/exportReport?dataset=<?= $data['dataset']['id'] ?>" 
               target="_blank" class="action-card report">
                <div class="action-step">6</div>
                <div class="action-icon-wrap">📄</div>
                <div class="action-info">
                    <h4>PDF Report</h4>
                    <p>Generate a printable summary</p>
                </div>
                <span class="action-arrow">→</span>
            </a>
        </div>

        <!-- ============ QUICK DATA PREVIEW ============ -->
        <?php if (!empty($data['preview'])): ?>
            <div class="glass-card preview-card">
                <div class="card-header">
                    <div>
                        <h3>👀 Quick Data Preview</h3>
                        <p class="card-subtitle">
                            First <?= count($data['preview']) ?> rows · 
                            <?= count($data['schema']) ?> columns
                        </p>
                    </div>
                    <span class="badge blue">Preview</span>
                </div>

                <div class="table-container">
                    <table class="preview-table">
                        <thead>
                            <tr>
                                <th class="row-num-col">#</th>
                                <?php foreach (array_keys($data['preview'][0] ?? []) as $col): ?>
                                    <th><?= e($col) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['preview'] as $i => $row): ?>
                                <tr>
                                    <td class="row-num-col"><?= $i + 1 ?></td>
                                    <?php foreach ($row as $val): ?>
                                        <td>
                                            <?php if ($val === null || $val === ''): ?>
                                                <span class="empty-val">—</span>
                                            <?php else: ?>
                                                <?= e(mb_strimwidth((string)$val, 0, 60, '…')) ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============ DANGER ZONE ============ -->
        <div class="glass-card danger-card">
            <div class="danger-header">
                <div>
                    <h3>⚠️ Danger Zone</h3>
                    <p class="card-subtitle">Irreversible operations — proceed with care.</p>
                </div>
            </div>
            <div class="danger-actions">
                <a href="<?= BASE_URL ?>/nisr/recomputeQuality/<?= $data['dataset']['id'] ?>" 
                   class="btn-danger-secondary"
                   onclick="return confirm('Recompute quality metrics for this dataset?');">
                    🔄 Recompute Quality
                </a>
                <a href="<?= BASE_URL ?>/nisr/deleteDataset/<?= $data['dataset']['id'] ?>" 
                   class="btn-danger"
                   onclick="return confirm('Are you sure you want to delete this dataset? This cannot be undone.');">
                    🗑️ Delete Dataset
                </a>
            </div>
        </div>

    </main>
</div>

<script>
function copyDatasetLink() {
    const url = '<?= BASE_URL ?>/nisr/dataset/<?= $data['dataset']['id'] ?>';
    navigator.clipboard.writeText(url).then(() => {
        showToast('Link copied to clipboard!', 'success');
    }).catch(() => {
        // Fallback
        const ta = document.createElement('textarea');
        ta.value = url;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        showToast('Link copied!', 'success');
    });
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
    }, 2500);
}
</script>

<style>
/* =========================================
   DATASET DETAIL PAGE — FULL WIDTH DARK THEME
   ========================================= */

/* 1. FORCE FULL WIDTH LAYOUT */
.dataset-detail-page.dashboard-layout {
    display: flex !important;
    width: 100vw !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
    overflow-x: hidden !important;
}

.dataset-detail-page .main-content {
    flex: 1 !important;
    width: calc(100% - 250px) !important;
    max-width: none !important;
    min-width: 0 !important;
    padding: 32px !important;
    box-sizing: border-box !important;
}

/* 2. BREADCRUMB */
.breadcrumb {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    margin-bottom: 20px !important;
    padding: 10px 16px !important;
    background: rgba(30, 41, 59, 0.4) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 10px !important;
    font-size: 12.5px !important;
    flex-wrap: wrap !important;
}

.breadcrumb a {
    color: #38bdf8 !important;
    text-decoration: none !important;
    font-weight: 600 !important;
    transition: color 0.2s !important;
}
.breadcrumb a:hover { color: #7dd3fc !important; text-decoration: underline !important; }

.breadcrumb .sep { color: #475569 !important; font-weight: 700 !important; }
.breadcrumb .current { color: #94a3b8 !important; font-weight: 500 !important; }

/* 3. FLASH MESSAGES */
.flash-message {
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
    padding: 16px 22px !important;
    border-radius: 14px !important;
    margin-bottom: 22px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    animation: slideDown 0.4s ease-out !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2) !important;
    box-sizing: border-box !important;
}

.flash-message.success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.1)) !important;
    border: 1px solid rgba(16, 185, 129, 0.4) !important;
    color: #6ee7b7 !important;
}

.flash-message.error {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.1)) !important;
    border: 1px solid rgba(239, 68, 68, 0.4) !important;
    color: #fca5a5 !important;
}

.flash-icon { font-size: 22px !important; flex-shrink: 0 !important; }
.flash-text { flex: 1 !important; }
.flash-close {
    background: none !important;
    border: none !important;
    color: inherit !important;
    font-size: 18px !important;
    cursor: pointer !important;
    opacity: 0.6 !important;
    transition: opacity 0.2s !important;
}
.flash-close:hover { opacity: 1 !important; }

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* 4. DATASET HEADER CARD */
.dataset-header-card {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.75), rgba(15, 23, 42, 0.85)) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 32px !important;
    margin-bottom: 24px !important;
    position: relative !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
}

.dataset-header-card::before {
    content: '' !important;
    position: absolute !important;
    top: -50% !important;
    right: -10% !important;
    width: 400px !important;
    height: 400px !important;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.12), transparent 70%) !important;
    pointer-events: none !important;
}

.header-top-row {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 16px !important;
    margin-bottom: 20px !important;
    flex-wrap: wrap !important;
    position: relative !important;
    z-index: 1 !important;
}

.dataset-badges {
    display: flex !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
}

.badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    padding: 6px 14px !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    letter-spacing: 0.8px !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
}

.badge.blue {
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
}
.badge.orange {
    background: rgba(251, 191, 36, 0.15) !important;
    color: #fbbf24 !important;
    border: 1px solid rgba(251, 191, 36, 0.3) !important;
}
.badge.green {
    background: rgba(52, 211, 153, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(52, 211, 153, 0.3) !important;
}

.header-actions {
    display: flex !important;
    gap: 8px !important;
    position: relative !important;
    z-index: 1 !important;
}

.icon-btn {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 9px 16px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
}
.icon-btn:hover {
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border-color: rgba(56, 189, 248, 0.4) !important;
    transform: translateY(-1px) !important;
}

.dataset-title {
    font-size: 30px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 0 12px 0 !important;
    line-height: 1.2 !important;
    word-break: break-word !important;
    position: relative !important;
    z-index: 1 !important;
}

.dataset-desc {
    font-size: 15px !important;
    color: #94a3b8 !important;
    line-height: 1.6 !important;
    margin: 0 0 20px 0 !important;
    max-width: 900px !important;
    position: relative !important;
    z-index: 1 !important;
}

.dataset-meta {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
    position: relative !important;
    z-index: 1 !important;
}

.meta-item {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 8px 14px !important;
    background: rgba(255, 255, 255, 0.04) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 999px !important;
    font-size: 12.5px !important;
    color: #cbd5e1 !important;
    font-weight: 500 !important;
}

/* 5. DATASET STATS STRIP */
.dataset-stats {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
    gap: 16px !important;
    margin-bottom: 28px !important;
}

.stat-card {
    display: flex !important;
    align-items: center !important;
    gap: 14px !important;
    padding: 18px 20px !important;
    background: rgba(30, 41, 59, 0.5) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 14px !important;
    transition: all 0.2s ease !important;
    box-sizing: border-box !important;
}

.stat-card:hover {
    border-color: rgba(56, 189, 248, 0.3) !important;
    transform: translateY(-2px) !important;
}

.stat-icon {
    width: 44px !important;
    height: 44px !important;
    border-radius: 12px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 20px !important;
    flex-shrink: 0 !important;
    background: rgba(56, 189, 248, 0.1) !important;
    border: 1px solid rgba(56, 189, 248, 0.2) !important;
}

.stat-info {
    display: flex !important;
    flex-direction: column !important;
    gap: 3px !important;
    min-width: 0 !important;
}

.stat-label {
    font-size: 10.5px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
}

.stat-value {
    font-size: 18px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    line-height: 1.2 !important;
    font-variant-numeric: tabular-nums !important;
}

/* 6. SECTION HEADER */
.section-header {
    margin-bottom: 20px !important;
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

/* 7. ACTION CARDS GRID */
.action-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
    gap: 18px !important;
    margin-bottom: 32px !important;
}

.action-card {
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
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

/* Left accent bar */
.action-card::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 4px !important;
    height: 100% !important;
    transition: width 0.3s ease !important;
}

.action-card.explore::before    { background: linear-gradient(180deg, #10b981, #059669) !important; }
.action-card.charts::before     { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.action-card.compare::before    { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.action-card.timeseries::before { background: linear-gradient(180deg, #f472b6, #be185d) !important; }
.action-card.quality::before    { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.action-card.report::before     { background: linear-gradient(180deg, #f87171, #dc2626) !important; }

.action-card:hover {
    background: rgba(30, 41, 59, 0.8) !important;
    transform: translateY(-4px) !important;
    border-color: rgba(56, 189, 248, 0.4) !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4) !important;
}

.action-card:hover::before {
    width: 6px !important;
}

/* Step number badge */
.action-step {
    position: absolute !important;
    top: 12px !important;
    right: 14px !important;
    width: 22px !important;
    height: 22px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
}

.action-icon-wrap {
    width: 56px !important;
    height: 56px !important;
    flex-shrink: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 26px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border-radius: 14px !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.action-info {
    flex: 1 !important;
    min-width: 0 !important;
}

.action-info h4 {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    margin: 0 0 4px 0 !important;
    line-height: 1.3 !important;
}

.action-info p {
    font-size: 12.5px !important;
    color: #94a3b8 !important;
    margin: 0 !important;
    line-height: 1.45 !important;
}

.action-arrow {
    font-size: 20px !important;
    color: #475569 !important;
    flex-shrink: 0 !important;
    transition: transform 0.25s ease, color 0.25s ease !important;
}

.action-card:hover .action-arrow {
    transform: translateX(4px) !important;
    color: #38bdf8 !important;
}

/* 8. PREVIEW CARD */
.preview-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 28px !important;
    margin-bottom: 24px !important;
    box-sizing: border-box !important;
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
    font-size: 12.5px !important;
    color: #94a3b8 !important;
    margin: 4px 0 0 0 !important;
}

/* 9. PREVIEW TABLE */
.table-container {
    overflow-x: auto !important;
    overflow-y: auto !important;
    max-height: 560px !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: rgba(0, 0, 0, 0.2) !important;
}

.preview-table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 13px !important;
    min-width: 600px !important;
}

.preview-table thead th {
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
    border-bottom: 2px solid rgba(56, 189, 248, 0.25) !important;
    white-space: nowrap !important;
}

.preview-table tbody td {
    padding: 12px 16px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    color: #f1f5f9 !important;
    white-space: nowrap !important;
    max-width: 300px !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.preview-table tbody tr:hover td {
    background: rgba(56, 189, 248, 0.04) !important;
}

.preview-table tbody tr:last-child td {
    border-bottom: none !important;
}

.row-num-col {
    width: 50px !important;
    text-align: center !important;
    color: #64748b !important;
    font-weight: 700 !important;
    font-variant-numeric: tabular-nums !important;
    background: rgba(0, 0, 0, 0.15) !important;
}

.empty-val {
    color: #475569 !important;
    font-style: italic !important;
}

/* 10. DANGER ZONE */
.danger-card {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), rgba(220, 38, 38, 0.03)) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(239, 68, 68, 0.25) !important;
    border-radius: 20px !important;
    padding: 28px !important;
    box-sizing: border-box !important;
}

.danger-header {
    margin-bottom: 18px !important;
    padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(239, 68, 68, 0.2) !important;
}

.danger-header h3 {
    font-size: 18px !important;
    font-weight: 700 !important;
    color: #fca5a5 !important;
    margin: 0 !important;
}

.danger-actions {
    display: flex !important;
    gap: 12px !important;
    flex-wrap: wrap !important;
}

.btn-danger,
.btn-danger-secondary {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 12px 22px !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626) !important;
    color: #ffffff !important;
    border: none !important;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3) !important;
}
.btn-danger:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(239, 68, 68, 0.5) !important;
}

.btn-danger-secondary {
    background: rgba(239, 68, 68, 0.1) !important;
    color: #fca5a5 !important;
    border: 1px solid rgba(239, 68, 68, 0.3) !important;
}
.btn-danger-secondary:hover {
    background: rgba(239, 68, 68, 0.2) !important;
    transform: translateY(-2px) !important;
}

/* 11. TOAST */
.toast {
    position: fixed !important;
    bottom: 32px !important;
    right: 32px !important;
    padding: 14px 22px !important;
    border-radius: 12px !important;
    font-size: 13.5px !important;
    font-weight: 600 !important;
    color: #f1f5f9 !important;
    background: #1e293b !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5) !important;
    transform: translateY(20px) !important;
    opacity: 0 !important;
    transition: all 0.3s ease !important;
    z-index: 99999 !important;
}

.toast.show {
    transform: translateY(0) !important;
    opacity: 1 !important;
}

.toast.success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.95), rgba(5, 150, 105, 0.95)) !important;
    border-color: rgba(52, 211, 153, 0.5) !important;
    color: #ffffff !important;
}

/* 12. RESPONSIVE */
@media (max-width: 768px) {
    .dataset-detail-page .main-content { padding: 16px !important; }
    .dataset-header-card { padding: 22px !important; }
    .dataset-title { font-size: 22px !important; }
    .header-top-row { flex-direction: column !important; align-items: flex-start !important; }
    .header-actions { width: 100% !important; }
    .icon-btn { flex: 1 !important; justify-content: center !important; }
    .dataset-stats { grid-template-columns: 1fr 1fr !important; }
    .action-grid { grid-template-columns: 1fr !important; }
    .action-card { padding: 18px 20px !important; }
    .action-icon-wrap { width: 48px !important; height: 48px !important; font-size: 22px !important; }
    .preview-card, .danger-card { padding: 20px !important; }
    .danger-actions { flex-direction: column !important; }
    .btn-danger, .btn-danger-secondary { width: 100% !important; justify-content: center !important; }
    .toast { left: 16px !important; right: 16px !important; bottom: 16px !important; }
}

@media (max-width: 480px) {
    .dataset-stats { grid-template-columns: 1fr !important; }
}
</style>