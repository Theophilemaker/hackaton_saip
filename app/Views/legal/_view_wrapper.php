<?php
/** Shared wrapper for all 4 legal pages */
$pageTitle = $title ?? 'Legal';
?>
<div class="legal-page">
    <div class="legal-shell">

        <!-- HERO -->
        <header class="legal-hero">
            <div class="legal-hero-brand">
                <span class="legal-hero-logo">🌱</span>
                <div>
                    <strong>SAIP</strong>
                    <small>Smart Agriculture Intelligence Platform</small>
                </div>
            </div>

            <div class="legal-hero-title">
                <span class="legal-badge">📄 Legal Document</span>
                <h1><?= htmlspecialchars($title) ?></h1>
                <p class="legal-hero-sub"><?= htmlspecialchars($subtitle ?? 'Official SAIP legal documentation.') ?></p>
                <span class="legal-updated">
                    Last updated: <strong><?= htmlspecialchars($lastUpdated ?? 'October 4, 2025') ?></strong>
                </span>
            </div>
        </header>

        <div class="legal-grid">

            <!-- TOC -->
            <aside class="legal-toc">
                <h3>📑 Contents</h3>
                <nav>
                    <?php foreach ($sections as $i => $s): ?>
                        <a href="#<?= $s['id'] ?>" class="toc-link">
                            <span class="toc-num"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></span>
                            <span class="toc-title"><?= htmlspecialchars($s['title']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </aside>

            <!-- Content — child view includes the sections -->
            <article class="legal-body">
                <?php
                // Include the actual section content for this legal page
                $contentFile = __DIR__ . '/_' . $slug . '_content.php';
                if (file_exists($contentFile)) {
                    require $contentFile;
                } else {
                    echo '<section class="legal-section">';
                    echo '<h3>Content coming soon</h3>';
                    echo '<p>For assistance, contact <a href="mailto:theophilen298@gmail.com">theophilen298@gmail.com</a>.</p>';
                    echo '</section>';
                }
                ?>

                <footer class="legal-footer">
                    <p>© <?= date('Y') ?> SAIP · AI-powered agriculture intelligence for Rwanda's farmers.</p>
                    <div class="legal-footer-links">
                        <a href="<?= BASE_URL ?>/terms">Terms</a>
                        <span>·</span>
                        <a href="<?= BASE_URL ?>/privacy">Privacy</a>
                        <span>·</span>
                        <a href="<?= BASE_URL ?>/cookies">Cookies</a>
                        <span>·</span>
                        <a href="<?= BASE_URL ?>/accessibility">Accessibility</a>
                    </div>
                </footer>
            </article>
        </div>
    </div>
</div>

<style>
/* ═══════════════════════════════════════════════════════════
   LEGAL PAGES — Standalone content styling
   ═══════════════════════════════════════════════════════════ */
.legal-page {
    background: #0b1120;
    min-height: 80vh;
    padding: 32px 24px 80px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.legal-shell {
    max-width: 1300px;
    margin: 0 auto;
}

/* HERO */
.legal-hero {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08));
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.legal-hero::before {
    content: '';
    position: absolute;
    top: -40%; right: -10%;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.18), transparent 70%);
    pointer-events: none;
}

.legal-hero-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 22px;
    padding-bottom: 18px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    position: relative; z-index: 1;
}

.legal-hero-logo {
    font-size: 40px;
    line-height: 1;
    filter: drop-shadow(0 0 12px rgba(34, 197, 94, 0.45));
}

.legal-hero-brand strong {
    font-size: 22px;
    font-weight: 800;
    color: #10b981;
    letter-spacing: 3px;
    line-height: 1;
    display: block;
    margin-bottom: 3px;
}

.legal-hero-brand small {
    font-size: 10px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    font-weight: 700;
}

.legal-hero-title { position: relative; z-index: 1; }

.legal-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.35);
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    margin-bottom: 12px;
}

.legal-hero h1 {
    font-size: 30px;
    font-weight: 800;
    color: #f1f5f9;
    margin: 0 0 10px;
    line-height: 1.15;
}

.legal-hero-sub {
    color: #94a3b8;
    font-size: 14px;
    line-height: 1.6;
    margin: 0 0 16px;
    max-width: 720px;
}

.legal-updated {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    font-size: 12px;
    color: #94a3b8;
}

.legal-updated strong { color: #34d399; }

/* LAYOUT */
.legal-grid {
    display: grid;
    grid-template-columns: 260px minmax(0, 1fr);
    gap: 24px;
    align-items: start;
}

/* TOC */
.legal-toc {
    position: sticky;
    top: 90px;
    background: rgba(30, 41, 59, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    padding: 20px;
}

.legal-toc h3 {
    font-size: 12px;
    font-weight: 800;
    color: #f1f5f9;
    margin: 0 0 14px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.legal-toc nav {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.toc-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 9px;
    text-decoration: none;
    color: #94a3b8;
    font-size: 12.5px;
    font-weight: 600;
    transition: all 0.15s;
}

.toc-link:hover {
    background: rgba(16, 185, 129, 0.08);
    color: #34d399;
    transform: translateX(3px);
}

.toc-link.active {
    background: rgba(16, 185, 129, 0.12);
    color: #34d399;
}

.toc-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 20px;
    padding: 0 5px;
    background: rgba(16, 185, 129, 0.12);
    color: #34d399;
    border-radius: 5px;
    font-size: 10px;
    font-weight: 800;
    flex-shrink: 0;
}

/* BODY */
.legal-body {
    background: rgba(30, 41, 59, 0.55);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 32px 36px;
}

.legal-section {
    padding-bottom: 28px;
    margin-bottom: 28px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    scroll-margin-top: 100px;
}

.legal-section:last-of-type { border-bottom: none; }

.legal-section h3 {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 19px;
    font-weight: 800;
    color: #f1f5f9;
    margin: 0 0 18px;
    line-height: 1.25;
}

.legal-section h3::before {
    content: '';
    display: inline-block;
    width: 4px;
    height: 26px;
    background: linear-gradient(180deg, #10b981, #38bdf8);
    border-radius: 2px;
    flex-shrink: 0;
}

.legal-section p {
    font-size: 13.5px;
    color: #cbd5e1;
    line-height: 1.75;
    margin: 0 0 14px;
}

.legal-section p:last-child { margin-bottom: 0; }

.legal-section strong { color: #34d399; font-weight: 700; }

.legal-section ul {
    list-style: none;
    padding: 0;
    margin: 0 0 14px;
    display: flex;
    flex-direction: column;
    gap: 9px;
}

.legal-section ul li {
    padding: 10px 16px 10px 40px;
    position: relative;
    background: rgba(0, 0, 0, 0.2);
    border-left: 3px solid #10b981;
    border-radius: 10px;
    font-size: 13px;
    color: #cbd5e1;
    line-height: 1.55;
}

.legal-section ul li::before {
    content: '✓';
    position: absolute;
    left: 16px;
    top: 10px;
    color: #34d399;
    font-weight: 800;
}

/* FOOTER */
.legal-footer {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.legal-footer p {
    font-size: 11.5px;
    color: #64748b;
    margin: 0;
}

.legal-footer-links {
    display: flex;
    gap: 8px;
    align-items: center;
    font-size: 11.5px;
}

.legal-footer-links a {
    color: #94a3b8;
    text-decoration: none;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    transition: all 0.15s;
}

.legal-footer-links a:hover {
    color: #34d399;
    background: rgba(16, 185, 129, 0.08);
}

.legal-footer-links span { color: #334155; }

/* RESPONSIVE */
@media (max-width: 900px) {
    .legal-grid { grid-template-columns: 1fr; }
    .legal-toc { position: static; }
    .legal-hero { padding: 24px; }
    .legal-hero h1 { font-size: 22px; }
    .legal-body { padding: 24px 22px; }
    .legal-footer { flex-direction: column; text-align: center; }
}
</style>

<script>
/* Smooth scroll + active TOC link */
(function () {
    'use strict';

    document.querySelectorAll('.toc-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.replaceState(null, '', this.getAttribute('href'));
            }
        });
    });

    var sections = document.querySelectorAll('.legal-section');
    var tocLinks = document.querySelectorAll('.toc-link');
    if (!sections.length || !tocLinks.length) return;

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var id = entry.target.id;
                tocLinks.forEach(function (item) {
                    item.classList.toggle('active', item.getAttribute('href') === '#' + id);
                });
            }
        });
    }, { rootMargin: '-20% 0px -70% 0px', threshold: 0 });

    sections.forEach(function (s) { observer.observe(s); });
})();
</script>