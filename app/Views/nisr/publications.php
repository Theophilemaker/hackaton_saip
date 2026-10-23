<div class="dashboard-layout publications-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ============ PUBLICATIONS CARD ============ -->
        <section class="glass-card publications-card">
            <div class="card-header">
                <div>
                    <h3>📰 NISR Publications</h3>
                    <p class="pub-subtitle">
                        Official reports and analyses generated from NISR datasets
                    </p>
                </div>
                <span class="badge blue">
                    <?= count($data['publications'] ?? []) ?> Publication<?= count($data['publications'] ?? []) !== 1 ? 's' : '' ?>
                </span>
            </div>

            <?php if (empty($data['publications'])): ?>
                <!-- ============ EMPTY STATE ============ -->
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>No Publications Yet</h3>
                    <p>
                        Publications are reports generated from your datasets. 
                        Open any dataset and click <strong>📄 PDF Report</strong> to create one.
                    </p>
                    <a href="<?= BASE_URL ?>/nisr/datasets" class="btn-cta">
                        📂 Browse Datasets
                    </a>
                </div>
            <?php else: ?>
                <!-- ============ PUBLICATIONS GRID ============ -->
                <div class="publications-grid">
                    <?php foreach ($data['publications'] as $p): ?>
                        <div class="publication-card">
                            <div class="pub-header">
                                <span class="pub-type">
                                    <?= strtoupper($p['publication_type'] ?? 'REPORT') ?>
                                </span>
                                <?php 
                                $statusClass = $p['status'] === 'published' ? 'green' : 'orange';
                                ?>
                                <span class="badge <?= $statusClass ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </div>

                            <h4 class="pub-title"><?= e($p['title']) ?></h4>
                            
                            <?php if (!empty($p['subtitle'])): ?>
                                <p class="pub-subtitle-text">
                                    <?= e($p['subtitle']) ?>
                                </p>
                            <?php endif; ?>

                            <div class="pub-footer">
                                <span class="pub-date">
                                    📅 <?= date('M d, Y', strtotime($p['created_at'])) ?>
                                </span>
                                <?php if (!empty($p['file_path'])): ?>
                                    <a href="<?= BASE_URL ?>/<?= e($p['file_path']) ?>" 
                                       target="_blank" 
                                       class="pub-action">
                                        📄 View
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<style>
/* =========================================
   NISR PUBLICATIONS — FULL WIDTH DARK THEME
   ========================================= */

/* 1. FORCE FULL WIDTH LAYOUT */
.publications-page.dashboard-layout {
    display: flex !important;
    width: 100vw !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
    overflow-x: hidden !important;
}

.publications-page .main-content {
    flex: 1 !important;
    width: calc(100% - 250px) !important;
    max-width: none !important;
    min-width: 0 !important;
    padding: 32px !important;
    box-sizing: border-box !important;
}

/* 2. CARD BASE STYLES */
.publications-card {
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

.card-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 24px !important;
    padding-bottom: 16px !important;
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

.pub-subtitle {
    font-size: 13px !important;
    color: #94a3b8 !important;
    margin: 6px 0 0 0 !important;
}

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
.badge.blue   { background: rgba(56, 189, 248, 0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(56, 189, 248, 0.3) !important; }
.badge.green  { background: rgba(52, 211, 153, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(52, 211, 153, 0.3) !important; }
.badge.orange { background: rgba(251, 191, 36, 0.15) !important; color: #fbbf24 !important; border: 1px solid rgba(251, 191, 36, 0.3) !important; }

/* 4. EMPTY STATE */
.empty-state {
    text-align: center !important;
    padding: 70px 24px !important;
}

.empty-icon {
    font-size: 64px !important;
    margin-bottom: 16px !important;
    opacity: 0.7 !important;
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

.empty-state strong {
    color: #38bdf8 !important;
}

.btn-cta {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 14px 28px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important;
    border-radius: 12px !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    transition: all 0.25s ease !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
}

.btn-cta:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5) !important;
}

/* 5. PUBLICATIONS GRID */
.publications-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)) !important;
    gap: 20px !important;
    width: 100% !important;
}

/* 6. INDIVIDUAL PUBLICATION CARD */
.publication-card {
    display: flex !important;
    flex-direction: column !important;
    background: rgba(255, 255, 255, 0.03) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 16px !important;
    padding: 22px !important;
    transition: all 0.25s ease !important;
    position: relative !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
}

/* Left accent bar */
.publication-card::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 4px !important;
    height: 100% !important;
    background: linear-gradient(180deg, #38bdf8, #a78bfa) !important;
    transition: width 0.3s ease !important;
}

.publication-card:hover {
    transform: translateY(-4px) !important;
    background: rgba(255, 255, 255, 0.06) !important;
    border-color: rgba(56, 189, 248, 0.4) !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4) !important;
}

.publication-card:hover::before {
    width: 6px !important;
}

/* Card Header */
.pub-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 14px !important;
    gap: 10px !important;
}

.pub-type {
    font-size: 10px !important;
    font-weight: 800 !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    padding: 5px 12px !important;
    border-radius: 999px !important;
    background: rgba(167, 139, 250, 0.15) !important;
    color: #a78bfa !important;
    border: 1px solid rgba(167, 139, 250, 0.3) !important;
}

/* Card Title */
.pub-title {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    margin: 0 0 8px 0 !important;
    line-height: 1.4 !important;
    word-break: break-word !important;
}

/* Card Subtitle Text */
.pub-subtitle-text {
    font-size: 13px !important;
    color: #94a3b8 !important;
    line-height: 1.5 !important;
    margin: 0 0 16px 0 !important;
    flex-grow: 1 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 3 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
}

/* Card Footer */
.pub-footer {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding-top: 14px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    margin-top: auto !important;
}

.pub-date {
    font-size: 12px !important;
    color: #64748b !important;
    font-weight: 500 !important;
}

.pub-action {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 6px 14px !important;
    background: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    border-radius: 8px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
}

.pub-action:hover {
    background: rgba(56, 189, 248, 0.25) !important;
    transform: translateY(-1px) !important;
}

/* 7. RESPONSIVE */
@media (max-width: 768px) {
    .publications-page .main-content {
        padding: 16px !important;
    }
    .publications-card {
        padding: 20px !important;
    }
    .card-header {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    .publications-grid {
        grid-template-columns: 1fr !important;
    }
    .empty-icon {
        font-size: 52px !important;
    }
}
</style>