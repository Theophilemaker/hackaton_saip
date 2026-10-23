<?php
/**
 * Shared legal page header — with stats, quick actions, and search
 */
$sections = $sections ?? [];
$readTime = $readTime ?? '4 min';
$version  = $version  ?? 'v2.1';
?>
<div class="dashboard-layout legal-page">
    <?php require_once __DIR__ . '/../../layouts/sidebar.php'; ?>

    <div class="main-wrap">
        <?php require_once __DIR__ . '/../../layouts/topbar.php'; ?>

        <main class="main-content">

            <!-- Reading progress bar -->
            <div class="legal-progress" id="legalProgress">
                <div class="legal-progress-fill" id="legalProgressFill"></div>
            </div>

            <!-- ═══ HERO ═══ -->
            <section class="glass-card legal-hero">
                <div class="legal-hero-brand">
                    <span class="legal-hero-logo">🌱</span>
                    <div>
                        <h2 class="legal-hero-name">SAIP</h2>
                        <small class="legal-hero-tag">Smart Agriculture Intelligence Platform</small>
                    </div>
                </div>

                <div class="legal-hero-content">
                    <span class="legal-badge">
                        <span class="lb-icon"><?= $badge ?></span>
                        <span>Legal Document</span>
                    </span>
                    <h1><?= htmlspecialchars($title) ?></h1>
                    <p><?= htmlspecialchars($subtitle) ?></p>
                    <span class="legal-updated">
                        📅 Last updated: <strong><?= htmlspecialchars($lastUpdated) ?></strong>
                    </span>
                </div>
            </section>

            <!-- ═══ KPI STRIP ═══ -->
            <section class="legal-kpis">
                <div class="kpi-box kpi-sections">
                    <div class="kpi-box-icon">📑</div>
                    <div>
                        <span class="kpi-box-label">Sections</span>
                        <strong class="kpi-box-value"><?= count($sections) ?></strong>
                    </div>
                </div>
                <div class="kpi-box kpi-read">
                    <div class="kpi-box-icon">⏱️</div>
                    <div>
                        <span class="kpi-box-label">Read Time</span>
                        <strong class="kpi-box-value"><?= htmlspecialchars($readTime) ?></strong>
                    </div>
                </div>
                <div class="kpi-box kpi-version">
                    <div class="kpi-box-icon">🔖</div>
                    <div>
                        <span class="kpi-box-label">Version</span>
                        <strong class="kpi-box-value"><?= htmlspecialchars($version) ?></strong>
                    </div>
                </div>
                <div class="kpi-box kpi-status">
                    <div class="kpi-box-icon">✅</div>
                    <div>
                        <span class="kpi-box-label">Status</span>
                        <strong class="kpi-box-value">Active</strong>
                    </div>
                </div>
            </section>

            <!-- ═══ QUICK ACTIONS BAR ═══ -->
            <section class="glass-card legal-actions-bar">
                <div class="lab-left">
                    <span class="lab-label">⚡ Quick Actions</span>
                </div>

                <div class="lab-search">
                    <span class="lab-search-icon">🔍</span>
                    <input type="text" id="legalSearch" placeholder="Search within this document…" autocomplete="off">
                    <button type="button" class="lab-search-clear" id="labSearchClear" onclick="clearLegalSearch()" hidden>✕</button>
                </div>

                <div class="lab-buttons">
                    <button type="button" class="lab-btn" onclick="window.print()" title="Print">
                        <span>🖨️</span>
                        <span class="lab-btn-text">Print</span>
                    </button>
                    <button type="button" class="lab-btn" onclick="downloadPDF()" title="Download">
                        <span>📥</span>
                        <span class="lab-btn-text">PDF</span>
                    </button>
                    <button type="button" class="lab-btn" onclick="copyPageLink(this)" title="Copy link">
                        <span>🔗</span>
                        <span class="lab-btn-text">Copy Link</span>
                    </button>
                    <button type="button" class="lab-btn" onclick="shareDoc(this)" title="Share">
                        <span>📤</span>
                        <span class="lab-btn-text">Share</span>
                    </button>
                </div>
            </section>

            <!-- ═══ 2-COLUMN: TOC + CONTENT ═══ -->
            <div class="legal-layout">

                <!-- TOC -->
                <aside class="legal-toc glass-card">
                    <div class="toc-head">
                        <h3>📑 Contents</h3>
                        <span class="toc-count"><?= count($sections) ?></span>
                    </div>
                    <nav class="toc-nav">
                        <?php foreach ($sections as $i => $s): ?>
                            <a href="#<?= htmlspecialchars($s['id']) ?>" class="toc-item">
                                <span class="toc-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                                <span class="toc-icon"><?= $s['icon'] ?></span>
                                <span class="toc-text"><?= htmlspecialchars($s['title']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>

                    <div class="toc-footer">
                        <a href="<?= BASE_URL ?>/dashboard" class="toc-back">← Back to Dashboard</a>
                    </div>
                </aside>

                <!-- CONTENT -->
                <article class="legal-content glass-card">