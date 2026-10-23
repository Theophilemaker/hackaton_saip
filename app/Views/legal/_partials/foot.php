<?php
/**
 * Shared legal page footer — with feedback, related docs, timeline
 */
$viewSlug = $viewSlug ?? 'terms';

$relatedDocs = $relatedDocs ?? [
    'terms'         => ['📜', 'Terms & Conditions',  'The rules governing your use of SAIP.'],
    'privacy'       => ['🔒', 'Security & Privacy',  'How we protect your data.'],
    'cookies'       => ['🍪', 'Cookie Policy',       'How cookies are used.'],
    'accessibility' => ['♿', 'Accessibility',        'Our commitment to inclusion.'],
];
unset($relatedDocs[$viewSlug]);

$versionHistory = $versionHistory ?? [
    ['v' => '2.1', 'date' => 'Oct 4, 2025',  'note' => 'Added explanatory notes to each section', 'current' => true],
    ['v' => '2.0', 'date' => 'Aug 12, 2025', 'note' => 'Expanded rights section'],
    ['v' => '1.5', 'date' => 'May 3, 2025',  'note' => 'Clarified legal framework'],
];
?>

                    <!-- ═══ FEEDBACK WIDGET ═══ -->
                    <section class="legal-feedback">
                        <div class="lfb-content">
                            <div class="lfb-icon">💬</div>
                            <div class="lfb-text">
                                <strong>Was this document helpful?</strong>
                                <small>Your feedback helps us improve our legal documentation.</small>
                            </div>
                            <div class="lfb-buttons">
                                <button type="button" class="lfb-btn lfb-yes" onclick="legalFeedback(true, this)">
                                    <span>👍</span> Yes
                                </button>
                                <button type="button" class="lfb-btn lfb-no" onclick="legalFeedback(false, this)">
                                    <span>👎</span> No
                                </button>
                            </div>
                        </div>
                        <div class="lfb-thanks" id="lfbThanks" hidden>
                            ✅ Thank you! We appreciate your feedback.
                        </div>
                    </section>

                    <!-- ═══ RELATED DOCUMENTS ═══ -->
                    <section class="legal-related">
                        <div class="lrel-head">
                            <h3>📚 Related Documents</h3>
                            <small>Other legal resources you may find useful</small>
                        </div>
                        <div class="lrel-grid">
                            <?php foreach ($relatedDocs as $slug => $meta): ?>
                                <a href="<?= BASE_URL ?>/<?= $slug ?>" class="lrel-card">
                                    <span class="lrel-icon"><?= $meta[0] ?></span>
                                    <div>
                                        <strong><?= htmlspecialchars($meta[1]) ?></strong>
                                        <small><?= htmlspecialchars($meta[2]) ?></small>
                                    </div>
                                    <span class="lrel-arrow">→</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- ═══ VERSION TIMELINE ═══ -->
                    <section class="legal-timeline">
                        <div class="ltl-head">
                            <h3>📅 Version History</h3>
                            <small>See how this document has evolved</small>
                        </div>
                        <div class="ltl-list">
                            <?php foreach ($versionHistory as $v): ?>
                                <div class="ltl-item <?= !empty($v['current']) ? 'current' : '' ?>">
                                    <div class="ltl-dot"></div>
                                    <div class="ltl-body">
                                        <div class="ltl-head-row">
                                            <span class="ltl-version">v<?= htmlspecialchars($v['v']) ?></span>
                                            <?php if (!empty($v['current'])): ?>
                                                <span class="ltl-current-badge">Current</span>
                                            <?php endif; ?>
                                            <span class="ltl-date"><?= htmlspecialchars($v['date']) ?></span>
                                        </div>
                                        <p><?= htmlspecialchars($v['note']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- ═══ FOOTER ═══ -->
                    <footer class="legal-footer">
                        <div class="legal-footer-copy">
                            © <?= date('Y') ?> <strong>SAIP</strong> · AI-powered agriculture intelligence for Rwanda's farmers.
                            <small>A University of Rwanda innovation</small>
                        </div>
                        <div class="legal-footer-nav">
                            <?php
                            $links = [
                                'terms'         => ['📜', 'Terms'],
                                'privacy'       => ['🔒', 'Privacy'],
                                'cookies'       => ['🍪', 'Cookies'],
                                'accessibility' => ['♿', 'Accessibility'],
                            ];
                            foreach ($links as $slug => $meta):
                                $isActive = ($viewSlug) === $slug;
                            ?>
                                <a href="<?= BASE_URL ?>/<?= $slug ?>" class="lfn-link <?= $isActive ? 'active' : '' ?>">
                                    <span><?= $meta[0] ?></span>
                                    <span><?= $meta[1] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </footer>

                </article>
            </div>

        </main>
    </div>
</div>

<!-- ═══ SHARED LEGAL STYLES ═══ -->
<style>
/* ═══════════════════════════════════════════════════════════
   LEGAL PAGE — Full dashboard with all features
   ═══════════════════════════════════════════════════════════ */
html, body {
    margin: 0 !important;
    padding: 0 !important;
    overflow-x: hidden !important;
    max-width: 100vw !important;
}

.legal-page.dashboard-layout {
    display: block !important; width: 100% !important; max-width: 100% !important;
    min-height: 100vh !important; background: #0b1120 !important;
    margin: 0 !important; padding: 0 !important; box-sizing: border-box !important;
    overflow-x: hidden !important;
}

.legal-page .main-wrap {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    min-height: 100vh !important;
    box-sizing: border-box !important;
    position: relative !important;
    z-index: 1 !important;
    overflow-x: hidden !important;
}

.legal-page .main-wrap > .main-content {
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    padding: 24px 32px 140px !important;
    box-sizing: border-box !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 22px !important;
    overflow-x: hidden !important;
}

.legal-page .glass-card {
    background: rgba(30, 41, 59, 0.7) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 26px !important;
    box-sizing: border-box !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
}

/* Reading progress */
.legal-progress {
    position: fixed !important;
    top: 0 !important; left: 260px !important; right: 0 !important;
    height: 3px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    z-index: 9998 !important;
    pointer-events: none !important;
}
.legal-progress-fill {
    height: 100% !important; width: 0% !important;
    background: linear-gradient(90deg, #10b981, #38bdf8) !important;
    transition: width 0.1s ease-out !important;
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.5) !important;
}

/* Hero */
.legal-hero {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08)) !important;
    position: relative !important; overflow: hidden !important;
    padding: 28px 32px !important;
}
.legal-hero::before {
    content: '' !important;
    position: absolute !important;
    top: -50% !important; right: -15% !important;
    width: 420px !important; height: 420px !important;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.18), transparent 70%) !important;
    pointer-events: none !important;
}
.legal-hero-brand {
    display: flex !important; align-items: center !important; gap: 12px !important;
    margin-bottom: 22px !important; padding-bottom: 18px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    position: relative !important; z-index: 1 !important;
}
.legal-hero-logo {
    font-size: 40px !important; line-height: 1 !important;
    filter: drop-shadow(0 0 14px rgba(34, 197, 94, 0.5)) !important;
}
.legal-hero-name {
    font-size: 22px !important; font-weight: 800 !important;
    color: #10b981 !important; letter-spacing: 3px !important;
    line-height: 1 !important; margin: 0 0 3px !important;
}
.legal-hero-tag {
    font-size: 10px !important; color: #64748b !important;
    text-transform: uppercase !important; letter-spacing: 1.2px !important;
    font-weight: 700 !important;
}
.legal-hero-content { position: relative !important; z-index: 1 !important; }
.legal-badge {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    padding: 5px 14px !important;
    background: rgba(16, 185, 129, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(16, 185, 129, 0.35) !important;
    border-radius: 999px !important;
    font-size: 10.5px !important; font-weight: 800 !important;
    letter-spacing: 1.2px !important; text-transform: uppercase !important;
    margin-bottom: 14px !important;
}
.legal-badge .lb-icon { font-size: 13px !important; line-height: 1 !important; }
.legal-hero h1 {
    font-size: 30px !important; font-weight: 800 !important;
    color: #f1f5f9 !important; margin: 0 0 10px !important;
    line-height: 1.15 !important;
}
.legal-hero p {
    color: #94a3b8 !important; font-size: 14.5px !important;
    line-height: 1.6 !important; margin: 0 0 16px !important;
    max-width: 720px !important;
}
.legal-updated {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    padding: 7px 14px !important;
    background: rgba(0, 0, 0, 0.3) !important;
    border-radius: 9px !important;
    font-size: 12px !important; color: #94a3b8 !important;
}
.legal-updated strong { color: #34d399 !important; }

/* KPIs */
.legal-kpis {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important;
    gap: 14px !important;
}
.kpi-box {
    display: flex !important; align-items: center !important; gap: 14px !important;
    padding: 16px 20px !important;
    background: rgba(30, 41, 59, 0.7) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 16px !important;
    position: relative !important; overflow: hidden !important;
    transition: all 0.25s !important; box-sizing: border-box !important;
}
.kpi-box::before {
    content: '' !important; position: absolute !important;
    top: 0 !important; left: 0 !important;
    width: 4px !important; height: 100% !important;
}
.kpi-sections::before { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.kpi-read::before     { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.kpi-version::before  { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.kpi-status::before   { background: linear-gradient(180deg, #10b981, #059669) !important; }
.kpi-box:hover { transform: translateY(-3px) !important; border-color: rgba(16, 185, 129, 0.3) !important; }
.kpi-box-icon { font-size: 26px !important; line-height: 1 !important; flex-shrink: 0 !important; }
.kpi-box-label {
    display: block !important; font-size: 10.5px !important;
    font-weight: 700 !important; color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.6px !important; margin-bottom: 4px !important;
}
.kpi-box-value {
    display: block !important; font-size: 20px !important;
    font-weight: 800 !important; color: #f1f5f9 !important;
    line-height: 1.1 !important;
}

/* Actions bar */
.legal-actions-bar {
    display: flex !important; align-items: center !important;
    gap: 16px !important; flex-wrap: wrap !important;
    padding: 14px 20px !important;
}
.lab-label {
    font-size: 11px !important; font-weight: 800 !important;
    color: #10b981 !important; text-transform: uppercase !important;
    letter-spacing: 1.2px !important;
}
.lab-search {
    flex: 1 1 240px !important;
    display: flex !important; align-items: center !important; gap: 8px !important;
    padding: 0 14px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 10px !important;
    min-height: 40px !important;
    transition: all 0.2s !important;
}
.lab-search:focus-within { border-color: #10b981 !important; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important; }
.lab-search-icon { font-size: 14px !important; opacity: 0.6 !important; }
.lab-search input {
    flex: 1 !important; background: transparent !important;
    border: none !important; outline: none !important;
    color: #f1f5f9 !important; font-size: 13px !important;
    font-family: inherit !important; padding: 10px 0 !important;
}
.lab-search input::placeholder { color: #64748b !important; }
.lab-search-clear {
    width: 26px !important; height: 26px !important;
    background: rgba(255, 255, 255, 0.06) !important;
    color: #94a3b8 !important;
    border: none !important; border-radius: 50% !important;
    cursor: pointer !important; font-size: 11px !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    padding: 0 !important;
}
.lab-buttons { display: flex !important; gap: 6px !important; flex-wrap: wrap !important; }
.lab-btn {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    padding: 9px 14px !important;
    background: rgba(255, 255, 255, 0.04) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 9px !important;
    font-size: 12.5px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    transition: all 0.2s !important; white-space: nowrap !important;
}
.lab-btn:hover {
    background: rgba(16, 185, 129, 0.1) !important;
    border-color: rgba(16, 185, 129, 0.3) !important;
    color: #34d399 !important; transform: translateY(-1px) !important;
}

/* Layout */
.legal-layout {
    display: grid !important;
    grid-template-columns: 280px minmax(0, 1fr) !important;
    gap: 22px !important;
    align-items: start !important;
    width: 100% !important; max-width: 100% !important;
    box-sizing: border-box !important;
}

/* TOC */
.legal-toc {
    position: sticky !important; top: 20px !important;
    padding: 20px !important;
    max-height: calc(100vh - 40px) !important;
    overflow-y: auto !important;
}
.toc-head {
    display: flex !important; justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 14px !important; padding-bottom: 12px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
}
.toc-head h3 {
    font-size: 12.5px !important; font-weight: 800 !important;
    color: #f1f5f9 !important; margin: 0 !important;
    text-transform: uppercase !important; letter-spacing: 1.1px !important;
}
.toc-count {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    min-width: 22px !important; height: 20px !important; padding: 0 8px !important;
    background: rgba(16, 185, 129, 0.15) !important;
    color: #34d399 !important;
    border-radius: 999px !important;
    font-size: 10.5px !important; font-weight: 800 !important;
}
.toc-nav { display: flex !important; flex-direction: column !important; gap: 2px !important; }
.toc-item {
    display: flex !important; align-items: center !important; gap: 10px !important;
    padding: 9px 10px !important;
    border-radius: 9px !important;
    text-decoration: none !important;
    color: #94a3b8 !important;
    font-size: 12.5px !important; font-weight: 600 !important;
    transition: all 0.18s ease !important;
}
.toc-item:hover { background: rgba(16, 185, 129, 0.08) !important; color: #34d399 !important; transform: translateX(3px) !important; }
.toc-item.active { background: rgba(16, 185, 129, 0.12) !important; color: #34d399 !important; }
.toc-num {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    min-width: 22px !important; height: 20px !important; padding: 0 5px !important;
    background: rgba(16, 185, 129, 0.12) !important;
    color: #34d399 !important;
    border-radius: 5px !important;
    font-size: 10px !important; font-weight: 800 !important;
    flex-shrink: 0 !important;
    font-family: 'Courier New', monospace !important;
}
.toc-icon { font-size: 14px !important; line-height: 1 !important; flex-shrink: 0 !important; }
.toc-text { flex: 1 !important; min-width: 0 !important; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; }
.toc-footer { margin-top: 16px !important; padding-top: 14px !important; border-top: 1px solid rgba(255, 255, 255, 0.06) !important; }
.toc-back {
    display: block !important; text-align: center !important;
    padding: 10px 14px !important;
    background: rgba(255, 255, 255, 0.04) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 9px !important;
    text-decoration: none !important;
    font-size: 12.5px !important; font-weight: 700 !important;
    transition: all 0.2s !important;
}
.toc-back:hover { background: rgba(16, 185, 129, 0.1) !important; border-color: rgba(16, 185, 129, 0.3) !important; color: #34d399 !important; }

/* Content */
.legal-content {
    padding: 36px 40px !important;
    min-width: 0 !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    overflow-wrap: break-word !important;
    word-break: break-word !important;
}
.legal-section {
    padding-bottom: 30px !important;
    margin-bottom: 30px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    scroll-margin-top: 100px !important;
}
.legal-section:last-of-type { border-bottom: none !important; margin-bottom: 0 !important; }
.legal-section.hidden-by-search { display: none !important; }
.legal-section.search-match {
    background: rgba(251, 191, 36, 0.04) !important;
    padding: 20px !important; margin-left: -20px !important; margin-right: -20px !important;
    border-radius: 12px !important;
}

.section-head {
    display: flex !important; align-items: flex-start !important;
    gap: 16px !important; margin-bottom: 20px !important;
}
.sec-icon {
    font-size: 26px !important; line-height: 1 !important;
    width: 56px !important; height: 56px !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.14), rgba(56, 189, 248, 0.08)) !important;
    border: 1px solid rgba(16, 185, 129, 0.25) !important;
    border-radius: 14px !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    flex-shrink: 0 !important;
}
.sec-title-block { flex: 1 !important; min-width: 0 !important; }
.sec-num {
    display: inline-block !important;
    font-size: 10.5px !important; font-weight: 800 !important;
    color: #10b981 !important; letter-spacing: 1.5px !important;
    text-transform: uppercase !important; margin-bottom: 4px !important;
}
.legal-section h3 {
    font-size: 20px !important; font-weight: 800 !important;
    color: #f1f5f9 !important; margin: 0 !important;
    line-height: 1.25 !important;
}
.legal-section p {
    font-size: 13.5px !important; color: #cbd5e1 !important;
    line-height: 1.75 !important; margin: 0 0 14px !important;
}
.legal-section p:last-child { margin-bottom: 0 !important; }
.legal-section strong { color: #34d399 !important; font-weight: 700 !important; }
.legal-section a {
    color: #38bdf8 !important; text-decoration: none !important;
    font-weight: 600 !important;
    border-bottom: 1px dotted rgba(56, 189, 248, 0.4) !important;
    transition: all 0.15s !important;
}
.legal-section a:hover { color: #34d399 !important; border-bottom-color: rgba(52, 211, 153, 0.6) !important; }

.legal-list {
    list-style: none !important; padding: 0 !important;
    margin: 0 0 14px !important;
    display: flex !important; flex-direction: column !important; gap: 9px !important;
}
.legal-list li {
    padding: 12px 18px 12px 44px !important;
    position: relative !important;
    background: rgba(0, 0, 0, 0.22) !important;
    border-left: 3px solid #10b981 !important;
    border-radius: 10px !important;
    font-size: 13px !important; color: #cbd5e1 !important;
    line-height: 1.55 !important;
}
.legal-list li::before {
    content: '✓' !important;
    position: absolute !important;
    left: 16px !important; top: 12px !important;
    color: #34d399 !important; font-weight: 800 !important; font-size: 13px !important;
}
.legal-list.danger li { border-left-color: #ef4444 !important; }
.legal-list.danger li::before { content: '✕' !important; color: #f87171 !important; }

.card-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)) !important;
    gap: 12px !important; margin-bottom: 16px !important;
}
.grid-card {
    display: flex !important; flex-direction: column !important; gap: 6px !important;
    padding: 16px !important;
    background: rgba(0, 0, 0, 0.22) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 12px !important;
    transition: all 0.2s !important;
}
.grid-card:hover {
    border-color: rgba(16, 185, 129, 0.35) !important;
    background: rgba(16, 185, 129, 0.05) !important;
    transform: translateY(-2px) !important;
}
.grid-card .gc-icon { font-size: 24px !important; line-height: 1 !important; }
.grid-card strong { font-size: 13.5px !important; color: #f1f5f9 !important; font-weight: 800 !important; }
.grid-card small { font-size: 11.5px !important; color: #94a3b8 !important; line-height: 1.45 !important; }
.grid-card.hz { flex-direction: row !important; align-items: center !important; gap: 12px !important; }

.callout {
    display: flex !important; gap: 14px !important; align-items: flex-start !important;
    padding: 16px 18px !important;
    border-radius: 12px !important; margin: 16px 0 !important;
    border-left: 4px solid !important;
}
.callout .co-icon { font-size: 22px !important; line-height: 1 !important; flex-shrink: 0 !important; }
.callout strong { display: block !important; font-size: 13px !important; margin-bottom: 4px !important; }
.callout p { margin: 0 !important; font-size: 12.5px !important; color: #cbd5e1 !important; line-height: 1.55 !important; }
.callout.info    { background: rgba(56, 189, 248, 0.08) !important;  border-left-color: #38bdf8 !important; }
.callout.info strong    { color: #38bdf8 !important; }
.callout.warn    { background: rgba(251, 191, 36, 0.08) !important;  border-left-color: #fbbf24 !important; }
.callout.warn strong    { color: #fbbf24 !important; }
.callout.success { background: rgba(16, 185, 129, 0.08) !important;  border-left-color: #10b981 !important; }
.callout.success strong { color: #34d399 !important; }

/* ═══ SECTION NOTE — Explanatory block at end of each section ═══ */
.section-note {
    display: flex !important;
    gap: 14px !important;
    align-items: flex-start !important;
    padding: 18px 20px !important;
    margin-top: 18px !important;
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.08), rgba(167, 139, 250, 0.04)) !important;
    border-left: 4px solid #38bdf8 !important;
    border-radius: 12px !important;
    position: relative !important;
    overflow: hidden !important;
}
.section-note::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important; left: 0 !important;
    width: 100% !important; height: 100% !important;
    background: radial-gradient(circle at top right, rgba(56, 189, 248, 0.06), transparent 70%) !important;
    pointer-events: none !important;
}
.sn-icon {
    font-size: 22px !important;
    line-height: 1 !important;
    flex-shrink: 0 !important;
    width: 40px !important;
    height: 40px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: rgba(56, 189, 248, 0.15) !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    border-radius: 10px !important;
    position: relative !important;
    z-index: 1 !important;
}
.sn-content {
    flex: 1 !important;
    min-width: 0 !important;
    position: relative !important;
    z-index: 1 !important;
}
.sn-content strong {
    display: block !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    color: #38bdf8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    margin-bottom: 6px !important;
}
.sn-content p {
    font-size: 13px !important;
    color: #cbd5e1 !important;
    line-height: 1.65 !important;
    margin: 0 !important;
}

.cookie-table {
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 12px !important; overflow: hidden !important;
    margin-bottom: 16px !important;
}
.ct-head, .ct-row {
    display: grid !important;
    grid-template-columns: 180px 1fr 120px !important;
    gap: 16px !important; padding: 14px 20px !important;
    align-items: center !important;
}
.ct-head {
    background: rgba(0, 0, 0, 0.3) !important;
    font-size: 11px !important; font-weight: 800 !important;
    color: #94a3b8 !important; text-transform: uppercase !important;
    letter-spacing: 1px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.ct-row {
    font-size: 12.5px !important; color: #cbd5e1 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
}
.ct-row:last-child { border-bottom: none !important; }
.ct-name {
    font-family: 'Courier New', monospace !important;
    color: #34d399 !important; font-weight: 700 !important; font-size: 12px !important;
}
.ct-badge {
    display: inline-flex !important; padding: 4px 10px !important;
    border-radius: 999px !important;
    font-size: 10px !important; font-weight: 800 !important;
    text-transform: uppercase !important; letter-spacing: 0.5px !important;
    justify-self: start !important;
}
.ct-badge.essential { background: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important; border: 1px solid rgba(239, 68, 68, 0.3) !important; }
.ct-badge.pref      { background: rgba(56, 189, 248, 0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(56, 189, 248, 0.3) !important; }
.ct-badge.analytics { background: rgba(167, 139, 250, 0.15) !important; color: #a78bfa !important; border: 1px solid rgba(167, 139, 250, 0.3) !important; }

.kbd-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)) !important;
    gap: 10px !important; margin-bottom: 16px !important;
}
.kbd-item {
    display: flex !important; align-items: center !important; gap: 8px !important;
    padding: 12px 14px !important;
    background: rgba(0, 0, 0, 0.22) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 10px !important;
    font-size: 12.5px !important; color: #cbd5e1 !important;
    flex-wrap: wrap !important;
}
kbd {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    padding: 4px 10px !important; min-width: 28px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    border-bottom-width: 2px !important;
    border-radius: 6px !important;
    font-family: 'Courier New', monospace !important;
    font-size: 11.5px !important; font-weight: 800 !important;
    color: #f1f5f9 !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
}

/* Feedback */
.legal-feedback {
    margin-top: 40px !important;
    padding: 22px 26px !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(56, 189, 248, 0.04)) !important;
    border: 1px solid rgba(16, 185, 129, 0.2) !important;
    border-radius: 16px !important;
}
.lfb-content { display: flex !important; align-items: center !important; gap: 16px !important; flex-wrap: wrap !important; }
.lfb-icon { font-size: 36px !important; line-height: 1 !important; flex-shrink: 0 !important; }
.lfb-text { flex: 1 !important; min-width: 180px !important; }
.lfb-text strong { display: block !important; font-size: 14.5px !important; color: #f1f5f9 !important; font-weight: 800 !important; margin-bottom: 3px !important; }
.lfb-text small { font-size: 12px !important; color: #94a3b8 !important; }
.lfb-buttons { display: flex !important; gap: 8px !important; flex-shrink: 0 !important; }
.lfb-btn {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    padding: 10px 18px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 13px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    transition: all 0.2s !important;
}
.lfb-btn:hover { transform: translateY(-2px) !important; }
.lfb-yes:hover { background: rgba(16, 185, 129, 0.15) !important; border-color: rgba(16, 185, 129, 0.4) !important; color: #34d399 !important; }
.lfb-no:hover { background: rgba(239, 68, 68, 0.12) !important; border-color: rgba(239, 68, 68, 0.35) !important; color: #f87171 !important; }
.lfb-btn.selected.lfb-yes { background: rgba(16, 185, 129, 0.2) !important; border-color: #10b981 !important; color: #34d399 !important; }
.lfb-btn.selected.lfb-no { background: rgba(239, 68, 68, 0.18) !important; border-color: #ef4444 !important; color: #f87171 !important; }
.lfb-thanks {
    margin-top: 14px !important;
    padding: 12px 16px !important;
    background: rgba(16, 185, 129, 0.12) !important;
    border-left: 3px solid #10b981 !important;
    border-radius: 10px !important;
    color: #34d399 !important;
    font-size: 13px !important; font-weight: 700 !important;
}

/* Related docs */
.legal-related {
    margin-top: 24px !important;
    padding: 24px 26px !important;
    background: rgba(0, 0, 0, 0.15) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 16px !important;
}
.lrel-head { margin-bottom: 16px !important; padding-bottom: 12px !important; border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important; }
.lrel-head h3 { font-size: 15px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 3px !important; }
.lrel-head small { font-size: 12px !important; color: #94a3b8 !important; }
.lrel-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)) !important;
    gap: 10px !important;
}
.lrel-card {
    display: flex !important; align-items: center !important; gap: 12px !important;
    padding: 14px 16px !important;
    background: rgba(255, 255, 255, 0.03) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 12px !important;
    text-decoration: none !important;
    color: #cbd5e1 !important;
    transition: all 0.2s !important;
}
.lrel-card:hover {
    background: rgba(16, 185, 129, 0.08) !important;
    border-color: rgba(16, 185, 129, 0.3) !important;
    transform: translateY(-2px) !important;
}
.lrel-icon { font-size: 22px !important; line-height: 1 !important; flex-shrink: 0 !important; }
.lrel-card div { flex: 1 !important; min-width: 0 !important; }
.lrel-card strong { display: block !important; font-size: 13px !important; color: #f1f5f9 !important; font-weight: 800 !important; margin-bottom: 2px !important; }
.lrel-card small { display: block !important; font-size: 11px !important; color: #94a3b8 !important; line-height: 1.4 !important; }
.lrel-arrow { color: #475569 !important; font-size: 16px !important; transition: all 0.2s !important; flex-shrink: 0 !important; }
.lrel-card:hover .lrel-arrow { color: #34d399 !important; transform: translateX(3px) !important; }

/* Timeline */
.legal-timeline {
    margin-top: 24px !important;
    padding: 24px 26px !important;
    background: rgba(0, 0, 0, 0.15) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 16px !important;
}
.ltl-head { margin-bottom: 20px !important; padding-bottom: 12px !important; border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important; }
.ltl-head h3 { font-size: 15px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 3px !important; }
.ltl-head small { font-size: 12px !important; color: #94a3b8 !important; }
.ltl-list { position: relative !important; padding-left: 24px !important; }
.ltl-list::before {
    content: '' !important;
    position: absolute !important;
    left: 7px !important; top: 8px !important; bottom: 8px !important;
    width: 2px !important;
    background: linear-gradient(180deg, #10b981, rgba(16, 185, 129, 0.1)) !important;
    border-radius: 2px !important;
}
.ltl-item { position: relative !important; padding: 10px 0 14px !important; }
.ltl-dot {
    position: absolute !important;
    left: -24px !important; top: 14px !important;
    width: 16px !important; height: 16px !important;
    background: #334155 !important;
    border: 3px solid #1e293b !important;
    border-radius: 50% !important;
    z-index: 1 !important;
}
.ltl-item.current .ltl-dot {
    background: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2), 0 0 12px rgba(16, 185, 129, 0.5) !important;
}
.ltl-body { padding-left: 4px !important; }
.ltl-head-row { display: flex !important; align-items: center !important; gap: 8px !important; flex-wrap: wrap !important; margin-bottom: 4px !important; }
.ltl-version { font-size: 13px !important; font-weight: 800 !important; color: #34d399 !important; font-family: 'Courier New', monospace !important; }
.ltl-current-badge {
    padding: 2px 8px !important;
    background: rgba(16, 185, 129, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(16, 185, 129, 0.3) !important;
    border-radius: 999px !important;
    font-size: 9.5px !important; font-weight: 800 !important;
    text-transform: uppercase !important; letter-spacing: 0.5px !important;
}
.ltl-date { font-size: 11.5px !important; color: #64748b !important; font-weight: 600 !important; }
.ltl-body p { font-size: 12.5px !important; color: #cbd5e1 !important; margin: 0 !important; line-height: 1.55 !important; }

/* Footer */
.legal-footer {
    margin-top: 34px !important; padding-top: 24px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    display: flex !important; justify-content: space-between !important;
    align-items: center !important; gap: 20px !important;
    flex-wrap: wrap !important;
}
.legal-footer-copy { font-size: 12.5px !important; color: #94a3b8 !important; line-height: 1.6 !important; }
.legal-footer-copy strong { color: #10b981 !important; }
.legal-footer-copy small { display: inline-block !important; margin-top: 3px !important; font-size: 11px !important; color: #64748b !important; }
.legal-footer-nav { display: flex !important; gap: 6px !important; flex-wrap: wrap !important; }
.lfn-link {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    padding: 7px 12px !important;
    font-size: 12px !important; color: #94a3b8 !important;
    background: rgba(255, 255, 255, 0.04) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 8px !important;
    text-decoration: none !important;
    font-weight: 600 !important;
    transition: all 0.18s !important;
}
.lfn-link:hover { color: #34d399 !important; background: rgba(16, 185, 129, 0.1) !important; border-color: rgba(16, 185, 129, 0.3) !important; transform: translateY(-1px) !important; }
.lfn-link.active { color: #34d399 !important; background: rgba(16, 185, 129, 0.15) !important; border-color: rgba(16, 185, 129, 0.35) !important; }

/* ═══ RESPONSIVE ═══ */
@media (max-width: 1000px) {
    .legal-layout { grid-template-columns: 1fr !important; }
    .legal-toc { position: static !important; max-height: none !important; }
}
@media (max-width: 900px) {
    .legal-page .main-wrap {
        margin-left: 0 !important;
        width: 100% !important; max-width: 100% !important;
        padding-top: 64px !important;
    }
    .legal-page .main-wrap > .main-content { padding: 14px 14px 120px !important; }
    .legal-page .glass-card { padding: 20px !important; border-radius: 16px !important; }
    .legal-hero { padding: 22px 20px !important; }
    .legal-hero h1 { font-size: 22px !important; }
    .legal-content { padding: 22px 20px !important; }
    .legal-section h3 { font-size: 17px !important; }
    .sec-icon { width: 46px !important; height: 46px !important; font-size: 22px !important; }
    .legal-footer { flex-direction: column !important; text-align: center !important; }
    .ct-head, .ct-row { grid-template-columns: 1fr !important; gap: 6px !important; }
    .ct-row .ct-badge { justify-self: start !important; margin-top: 4px !important; }
    .legal-progress { left: 0 !important; }
    .lab-btn-text { display: none !important; }
    .lab-btn { padding: 9px 12px !important; }
}
@media (max-width: 520px) {
    .kbd-grid, .card-grid, .lrel-grid { grid-template-columns: 1fr !important; }
    .legal-footer-nav { justify-content: center !important; }
    .legal-kpis { grid-template-columns: 1fr 1fr !important; }
    .kpi-box { padding: 14px 16px !important; }
    .kpi-box-icon { font-size: 22px !important; }
    .kpi-box-value { font-size: 17px !important; }
    .lfb-content { flex-direction: column !important; align-items: flex-start !important; }
    .lfb-buttons { width: 100% !important; }
    .lfb-btn { flex: 1 !important; justify-content: center !important; }
    .section-note { flex-direction: column !important; gap: 10px !important; padding: 16px !important; }
    .sn-icon { width: 36px !important; height: 36px !important; font-size: 18px !important; }
}

@media print {
    .legal-progress, .legal-actions-bar, .legal-toc,
    .legal-feedback, .legal-related, .legal-timeline,
    .legal-footer-nav, .sidebar, .topbar, .sidebar-toggle,
    .sidebar-overlay { display: none !important; }
    .legal-page .main-wrap { margin-left: 0 !important; width: 100% !important; }
}
</style>

<script>
(function () {
    'use strict';

    /* Reading progress */
    var fill = document.getElementById('legalProgressFill');
    var content = document.querySelector('.legal-content');
    if (fill && content) {
        window.addEventListener('scroll', function () {
            var rect = content.getBoundingClientRect();
            var winH = window.innerHeight;
            var total = content.offsetHeight;
            var scrolled = Math.max(0, Math.min(total, winH - rect.top));
            var pct = total > winH ? (scrolled / (total - winH * 0.5)) * 100 : 0;
            fill.style.width = Math.min(100, Math.max(0, pct)) + '%';
        }, { passive: true });
    }

    /* Smooth scroll for TOC */
    document.querySelectorAll('.toc-item').forEach(function (link) {
        link.addEventListener('click', function (e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.replaceState(null, '', this.getAttribute('href'));
            }
        });
    });

    /* Active section highlighting */
    var sections = document.querySelectorAll('.legal-section');
    var tocItems = document.querySelectorAll('.toc-item');
    if (sections.length && tocItems.length) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var id = entry.target.id;
                    tocItems.forEach(function (item) {
                        item.classList.toggle('active', item.getAttribute('href') === '#' + id);
                    });
                }
            });
        }, { rootMargin: '-20% 0px -70% 0px', threshold: 0 });
        sections.forEach(function (s) { observer.observe(s); });
    }

    /* Search */
    var searchInput = document.getElementById('legalSearch');
    var searchClear = document.getElementById('labSearchClear');
    var searchTimer;

    window.clearLegalSearch = function () {
        if (searchInput) searchInput.value = '';
        if (searchClear) searchClear.hidden = true;
        sections.forEach(function (s) { s.classList.remove('hidden-by-search', 'search-match'); });
    };

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            if (searchClear) searchClear.hidden = !q;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                if (!q) { clearLegalSearch(); return; }
                var matches = 0;
                sections.forEach(function (s) {
                    var text = s.textContent.toLowerCase();
                    var isMatch = text.indexOf(q) !== -1;
                    s.classList.toggle('hidden-by-search', !isMatch);
                    s.classList.toggle('search-match', isMatch);
                    if (isMatch) matches++;
                });
                if (matches === 0) {
                    sections.forEach(function (s) { s.classList.remove('hidden-by-search'); });
                }
            }, 200);
        });
    }

    window.copyPageLink = function (btn) {
        var url = window.location.href;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function () {
                var orig = btn.innerHTML;
                btn.innerHTML = '<span>✅</span><span class="lab-btn-text">Copied</span>';
                setTimeout(function () { btn.innerHTML = orig; }, 1500);
            });
        }
    };

    window.shareDoc = function (btn) {
        var data = { title: document.title, url: window.location.href };
        if (navigator.share) {
            navigator.share(data).catch(function () {});
        } else {
            copyPageLink(btn);
        }
    };

    window.downloadPDF = function () { window.print(); };

    window.legalFeedback = function (positive, btn) {
        document.querySelectorAll('.lfb-btn').forEach(function (b) {
            b.classList.remove('selected');
        });
        btn.classList.add('selected');
        var thanks = document.getElementById('lfbThanks');
        if (thanks) {
            thanks.hidden = false;
            thanks.textContent = positive
                ? '✅ Thank you! We\'re glad this was helpful.'
                : '📝 Thanks for the feedback. We\'ll work to improve.';
        }
    };
})();
</script>