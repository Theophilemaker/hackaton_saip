<?php
$currentLang = \App\Core\Language::current();
$langs       = \App\Core\Language::supported();

/* Detect if we're on the landing page (for smart anchors) */
$uri = $_SERVER['REQUEST_URI'] ?? '';
$isLanding = (bool) preg_match('#/public/?(\?.*)?$#', $uri)
          || (bool) preg_match('#/public/index\.php(\?.*)?$#', $uri);
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'SAIP · Smart Agriculture Intelligence Platform') ?></title>
    <meta name="description" content="Smart Agriculture Intelligence Platform for Rwanda — AI, GIS, marketplace, and multilingual tools for farmers, cooperatives, and government.">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <meta name="theme-color" content="#16A34A">
</head>
<body class="landing-body">

<!-- ═══════════════════════════════════════════════════════════
     PUBLIC NAV — shared across all public pages
     ═══════════════════════════════════════════════════════════ -->
<nav class="lp-nav" id="lpNav" data-landing="<?= $isLanding ? '1' : '0' ?>">
    <div class="lp-nav-inner">

        <!-- Brand -->
        <a href="<?= BASE_URL ?>/" class="lp-brand">
            <span class="lp-brand-icon">🌱</span>
            <div>
                <strong>SAIP</strong>
                <small>Smart Agriculture</small>
            </div>
        </a>

        <!-- Center links -->
        <div class="lp-menu">
            <a href="<?= BASE_URL ?>/#features" class="lp-link" data-anchor="features">
                <span class="lp-link-icon">✨</span>
                <span>Features</span>
            </a>
            <a href="<?= BASE_URL ?>/#modules" class="lp-link" data-anchor="modules">
                <span class="lp-link-icon">📦</span>
                <span>Modules</span>
            </a>
            <a href="<?= BASE_URL ?>/#impact" class="lp-link" data-anchor="impact">
                <span class="lp-link-icon">🎯</span>
                <span>Impact</span>
            </a>

            <span class="lp-divider"></span>

            <div class="lp-legal-group">
                <a href="<?= BASE_URL ?>/terms" class="lp-link lp-link-mini" title="Terms & Conditions">
                    <span>📜</span><span class="lp-mini-text">Terms</span>
                </a>
                <a href="<?= BASE_URL ?>/privacy" class="lp-link lp-link-mini" title="Privacy">
                    <span>🔒</span><span class="lp-mini-text">Privacy</span>
                </a>
                <a href="<?= BASE_URL ?>/cookies" class="lp-link lp-link-mini" title="Cookies">
                    <span>🍪</span><span class="lp-mini-text">Cookies</span>
                </a>
                <a href="<?= BASE_URL ?>/accessibility" class="lp-link lp-link-mini" title="Accessibility">
                    <span>♿</span><span class="lp-mini-text">Accessibility</span>
                </a>
            </div>

            <span class="lp-divider"></span>

            <a href="<?= BASE_URL ?>/#contact" class="lp-link lp-link-contact" data-anchor="contact">
                <span class="lp-link-icon">✉️</span>
                <span>Contact</span>
            </a>
        </div>

        <!-- Right actions -->
        <div class="lp-actions">

            <!-- Language switcher -->
            <div class="lp-lang" id="lpLang">
                <button type="button" class="lp-lang-btn" onclick="lpToggleLang(event)">
                    <span class="lp-lang-flag"><?= $langs[$currentLang]['flag'] ?></span>
                    <span class="lp-lang-code"><?= strtoupper($currentLang) ?></span>
                    <span class="lp-lang-chev">▾</span>
                </button>
                <div class="lp-lang-menu">
                    <?php foreach ($langs as $code => $info): ?>
                        <a href="<?= BASE_URL ?>/language/switch/<?= $code ?>"
                           class="lp-lang-item <?= $currentLang === $code ? 'active' : '' ?>">
                            <span class="lp-lang-flag"><?= $info['flag'] ?></span>
                            <span><?= $info['name'] ?></span>
                            <?php if ($currentLang === $code): ?>
                                <span class="lp-lang-check">✓</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Login / Register -->
            <a href="<?= BASE_URL ?>/auth/login" class="lp-btn-login">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                <span><?= __('auth.login') ?: 'Login' ?></span>
            </a>
            <a href="<?= BASE_URL ?>/auth/register" class="lp-btn-register">
                <span><?= __('auth.register') ?: 'Register' ?></span>
                <span class="lp-btn-arrow">→</span>
            </a>

            <!-- Mobile menu button -->
            <button type="button" class="lp-menu-toggle" id="lpMenuToggle"
                    onclick="lpToggleMobile()" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <!-- Mobile drawer -->
    <div class="lp-mobile-drawer" id="lpMobileDrawer">
        <a href="<?= BASE_URL ?>/#features" data-anchor="features">✨ Features</a>
        <a href="<?= BASE_URL ?>/#modules"  data-anchor="modules">📦 Modules</a>
        <a href="<?= BASE_URL ?>/#impact"   data-anchor="impact">🎯 Impact</a>
        <div class="lp-mobile-divider"></div>
        <span class="lp-mobile-label">Legal</span>
        <a href="<?= BASE_URL ?>/terms">📜 Terms &amp; Conditions</a>
        <a href="<?= BASE_URL ?>/privacy">🔒 Security &amp; Privacy</a>
        <a href="<?= BASE_URL ?>/cookies">🍪 Cookie Policy</a>
        <a href="<?= BASE_URL ?>/accessibility">♿ Accessibility</a>
        <div class="lp-mobile-divider"></div>
        <a href="<?= BASE_URL ?>/#contact" data-anchor="contact">✉️ Contact</a>
        <div class="lp-mobile-divider"></div>
        <a href="<?= BASE_URL ?>/auth/login" class="lp-mobile-login">Login</a>
        <a href="<?= BASE_URL ?>/auth/register" class="lp-mobile-register">Register →</a>
    </div>
</nav>

<!-- ═══════════════════════════════════════════════════════════
     NAV STYLES — moved here from landing.php
     ═══════════════════════════════════════════════════════════ -->
<style>
/* ═══ NAV CONTAINER ═══ */
.lp-nav {
    position: sticky;
    top: 0;
    z-index: 999;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    background: rgba(8, 18, 32, 0.85);
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    transition: box-shadow 0.25s ease, background 0.25s ease;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.lp-nav.scrolled {
    background: rgba(8, 18, 32, 0.95);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}

.lp-nav-inner {
    max-width: 1500px;
    margin: 0 auto;
    padding: 14px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

/* ═══ BRAND ═══ */
.lp-brand {
    display: flex; align-items: center; gap: 12px;
    text-decoration: none; color: inherit; flex-shrink: 0;
}
.lp-brand-icon {
    font-size: 34px; line-height: 1;
    filter: drop-shadow(0 0 12px rgba(34, 197, 94, 0.45));
}
.lp-brand div { display: flex; flex-direction: column; gap: 2px; }
.lp-brand strong {
    font-size: 20px; font-weight: 800;
    color: #10b981; letter-spacing: 2.5px; line-height: 1;
}
.lp-brand small {
    font-size: 9px; color: #64748b;
    text-transform: uppercase; letter-spacing: 1.2px;
    font-weight: 700; white-space: nowrap;
}

/* ═══ CENTER MENU ═══ */
.lp-menu {
    display: flex; align-items: center; gap: 2px;
    flex: 1; justify-content: center; min-width: 0;
}
.lp-link {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px;
    color: #cbd5e1; font-size: 13px; font-weight: 600;
    text-decoration: none; border-radius: 9px;
    transition: all 0.2s ease;
    white-space: nowrap; position: relative;
}
.lp-link:hover { color: #34d399; background: rgba(16, 185, 129, 0.08); }
.lp-link-icon { font-size: 14px; line-height: 1; }

.lp-link:not(.lp-link-mini):not(.lp-link-contact)::after {
    content: '';
    position: absolute; left: 14px; right: 14px; bottom: 3px;
    height: 2px;
    background: linear-gradient(90deg, #10b981, #38bdf8);
    border-radius: 2px;
    transform: scaleX(0); transform-origin: center;
    transition: transform 0.25s ease;
}
.lp-link:not(.lp-link-mini):not(.lp-link-contact):hover::after {
    transform: scaleX(1);
}

.lp-divider {
    display: inline-block; width: 1px; height: 18px;
    background: rgba(255, 255, 255, 0.1);
    margin: 0 10px; flex-shrink: 0;
}

.lp-legal-group { display: flex; align-items: center; gap: 2px; }

.lp-link-mini {
    padding: 7px 10px; font-size: 12px; color: #94a3b8;
}
.lp-link-mini:hover {
    color: #cbd5e1; background: rgba(255, 255, 255, 0.04);
}

.lp-link-contact {
    padding: 8px 16px; color: #34d399;
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.25);
    font-weight: 700;
}
.lp-link-contact:hover {
    background: rgba(16, 185, 129, 0.18);
    border-color: rgba(16, 185, 129, 0.5);
    color: #34d399; transform: translateY(-1px);
}

/* ═══ ACTIONS ═══ */
.lp-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

/* Language switcher */
.lp-lang { position: relative; }
.lp-lang-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 12px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px; color: #cbd5e1;
    font-size: 12.5px; font-weight: 700;
    cursor: pointer; font-family: inherit;
    transition: all 0.2s ease;
}
.lp-lang-btn:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(16, 185, 129, 0.4);
    color: #34d399;
}
.lp-lang-flag { font-size: 14px; line-height: 1; }
.lp-lang-code { letter-spacing: 0.5px; }
.lp-lang-chev { font-size: 9px; opacity: 0.7; }

.lp-lang-menu {
    position: absolute; top: calc(100% + 10px); right: 0;
    min-width: 190px; padding: 6px;
    background: #0f1d2e;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6);
    opacity: 0; visibility: hidden;
    transform: translateY(-6px);
    transition: all 0.2s ease;
    z-index: 1000;
}
.lp-lang.open .lp-lang-menu {
    opacity: 1; visibility: visible; transform: translateY(0);
}
.lp-lang-item {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 12px; border-radius: 8px;
    color: #cbd5e1; text-decoration: none;
    font-size: 13px; font-weight: 600;
    transition: all 0.15s;
}
.lp-lang-item:hover { background: rgba(16, 185, 129, 0.1); color: #34d399; }
.lp-lang-item.active { background: rgba(16, 185, 129, 0.12); color: #34d399; }
.lp-lang-check { margin-left: auto; color: #34d399; font-weight: 800; }

/* Login button */
.lp-btn-login {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 18px;
    background: transparent; color: #cbd5e1;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px; font-size: 13px; font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease; white-space: nowrap;
}
.lp-btn-login:hover {
    background: rgba(255, 255, 255, 0.06);
    color: #f1f5f9;
    border-color: rgba(255, 255, 255, 0.25);
    transform: translateY(-1px);
}
.lp-btn-login svg { flex-shrink: 0; }

/* Register button */
.lp-btn-register {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff; border-radius: 10px;
    font-size: 13px; font-weight: 800;
    text-decoration: none; letter-spacing: 0.3px;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    transition: all 0.2s ease; white-space: nowrap;
}
.lp-btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.55);
    color: #fff;
}
.lp-btn-arrow { transition: transform 0.2s ease; font-weight: 900; }
.lp-btn-register:hover .lp-btn-arrow { transform: translateX(3px); }

/* Mobile toggle */
.lp-menu-toggle {
    display: none; width: 42px; height: 42px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px; cursor: pointer;
    padding: 11px; flex-direction: column;
    justify-content: space-around;
    box-sizing: border-box; font-family: inherit;
}
.lp-menu-toggle span {
    display: block; height: 2px;
    background: #34d399; border-radius: 2px;
    transition: 0.3s;
}
.lp-menu-toggle.active span:nth-child(1) { transform: translateY(8px) rotate(45deg); }
.lp-menu-toggle.active span:nth-child(2) { opacity: 0; }
.lp-menu-toggle.active span:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }

/* Mobile drawer */
.lp-mobile-drawer {
    display: none; flex-direction: column; gap: 2px;
    padding: 12px 20px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(8, 18, 32, 0.98);
}
.lp-mobile-drawer.open { display: flex; }
.lp-mobile-drawer a {
    padding: 12px 14px; color: #cbd5e1;
    text-decoration: none; font-size: 14px; font-weight: 600;
    border-radius: 9px; transition: all 0.15s;
}
.lp-mobile-drawer a:hover {
    background: rgba(16, 185, 129, 0.1); color: #34d399;
}
.lp-mobile-divider {
    height: 1px; background: rgba(255, 255, 255, 0.06); margin: 8px 0;
}
.lp-mobile-label {
    padding: 8px 14px 4px;
    font-size: 10.5px; font-weight: 800;
    color: #10b981; text-transform: uppercase; letter-spacing: 1.4px;
}
.lp-mobile-login, .lp-mobile-register {
    text-align: center; margin-top: 4px;
}
.lp-mobile-login {
    background: rgba(255, 255, 255, 0.04) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #f1f5f9 !important;
}
.lp-mobile-register {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important; font-weight: 800 !important;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
}

/* ═══ RESPONSIVE ═══ */
@media (max-width: 1350px) {
    .lp-nav-inner { padding: 14px 24px; }
    .lp-link { padding: 8px 10px; font-size: 12.5px; }
    .lp-link-mini { padding: 7px 8px; font-size: 11.5px; }
    .lp-mini-text { display: none; }
    .lp-divider { margin: 0 6px; }
}
@media (max-width: 1150px) {
    .lp-menu { display: none; }
    .lp-menu-toggle { display: flex; }
    .lp-btn-login span:not(svg) { display: none; }
}
@media (max-width: 720px) {
    .lp-nav-inner { padding: 12px 16px; gap: 10px; }
    .lp-brand small { display: none; }
    .lp-brand strong { font-size: 18px; }
    .lp-btn-login, .lp-btn-register { padding: 9px 14px; font-size: 12.5px; }
    .lp-btn-login span { display: none; }
    .lp-lang-btn .lp-lang-code { display: none; }
}
</style>

<!-- ═══════════════════════════════════════════════════════════
     NAV SCRIPT
     ═══════════════════════════════════════════════════════════ -->
<script>
(function () {
    'use strict';

    var nav = document.getElementById('lpNav');
    var isLanding = nav && nav.dataset.landing === '1';

    /* Scroll shadow */
    window.addEventListener('scroll', function () {
        if (nav) nav.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });

    /* Language dropdown */
    window.lpToggleLang = function (e) {
        e.stopPropagation();
        var lang = document.getElementById('lpLang');
        if (lang) lang.classList.toggle('open');
    };
    document.addEventListener('click', function (e) {
        var lang = document.getElementById('lpLang');
        if (lang && !lang.contains(e.target)) lang.classList.remove('open');
    });

    /* Mobile menu */
    window.lpToggleMobile = function () {
        var drawer = document.getElementById('lpMobileDrawer');
        var btn = document.getElementById('lpMenuToggle');
        if (drawer) drawer.classList.toggle('open');
        if (btn) btn.classList.toggle('active');
    };
    document.querySelectorAll('#lpMobileDrawer a').forEach(function (a) {
        a.addEventListener('click', function () {
            var drawer = document.getElementById('lpMobileDrawer');
            var btn = document.getElementById('lpMenuToggle');
            if (drawer) drawer.classList.remove('open');
            if (btn) btn.classList.remove('active');
        });
    });

    /* Escape closes everything */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var lang = document.getElementById('lpLang');
            var drawer = document.getElementById('lpMobileDrawer');
            var btn = document.getElementById('lpMenuToggle');
            if (lang) lang.classList.remove('open');
            if (drawer) drawer.classList.remove('open');
            if (btn) btn.classList.remove('active');
        }
    });

    /* Smart anchor scroll:
       - On landing page → smooth scroll, no reload
       - On other pages   → let the browser navigate to /#anchor */
    document.querySelectorAll('.lp-link[data-anchor], .lp-mobile-drawer a[data-anchor]')
        .forEach(function (link) {
            link.addEventListener('click', function (e) {
                if (!isLanding) return; // let it navigate to landing page
                var anchor = this.getAttribute('data-anchor');
                var target = document.getElementById(anchor);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    // Close mobile drawer if open
                    var drawer = document.getElementById('lpMobileDrawer');
                    var btn = document.getElementById('lpMenuToggle');
                    if (drawer) drawer.classList.remove('open');
                    if (btn) btn.classList.remove('active');
                }
            });
        });
})();
</script>