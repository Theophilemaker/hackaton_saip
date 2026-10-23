<?php
// Ensure location helpers are available
if (!function_exists('current_district')) {
    require_once __DIR__ . '/../../Helpers/functions.php';
}

$userName    = $_SESSION['name'] ?? 'Guest';
$district    = current_district();
$coords      = current_coords();
$hasLocation = has_location();
$isAdmin     = (int)($_SESSION['role_id'] ?? 0) === 7;
$roleName    = $_SESSION['role_name'] ?? 'Member';
$initials    = initials($userName);
?>

<header class="topbar">
    <script src="<?= BASE_URL ?>/public/js/messages-fix.js"></script>
    
    <!-- Left: greeting -->
    <div class="topbar-left">
        <h1 class="topbar-title"><?= e($data['title'] ?? 'Dashboard') ?></h1>
        <p class="topbar-sub">Welcome back, <?= e(explode(' ', $userName)[0]) ?> 🌱</p>
    </div>

    <!-- Right: actions -->
    <div class="topbar-right">

        <!-- NEW: NISR Data Freshness Badge -->
        <div class="topbar-chip" style="background: rgba(16,185,129,.12); border-color: rgba(16,185,129,.35); color: #34d399; cursor: default;" title="RWA-NISR-SAS-2025-v01 & RWA-NISR-RAHS-2024-V0.1">
            <span class="chip-icon">📊</span>
            <span class="chip-text" style="font-size: 11px; font-weight: 700;">NISR SAS 2025</span>
        </div>

        <!-- Location chip (click to open weather page) -->
        <a href="<?= BASE_URL ?>/weather"
           class="topbar-chip location-chip"
           id="locationChip"
           title="<?= $hasLocation ? 'Your current location — click to change' : 'Click to detect your location' ?>">
            <span class="chip-icon">📍</span>
            <span class="chip-text" id="chipDistrict"><?= e($district) ?></span>
        </a>

        <!-- Language switcher -->
        <div class="topbar-dropdown">
            <button type="button" class="topbar-chip" id="langToggle">
                <span class="chip-icon">🌐</span>
                <span class="chip-text"><?= strtoupper(\App\Core\Language::current() ?? 'en') ?></span>
                <span class="chip-caret">▾</span>
            </button>
            <div class="dropdown-menu" id="langMenu">
                <?php
                $langs = class_exists('App\Core\Language') ? \App\Core\Language::supported() : ['en' => ['name' => 'English', 'flag' => '🇬🇧']];
                $current = class_exists('App\Core\Language') ? \App\Core\Language::current() : 'en';
                foreach ($langs as $code => $info): ?>
                    <a href="<?= lang_url($code) ?>" class="dropdown-item <?= $current === $code ? 'active' : '' ?>">
                        <span><?= $info['flag'] ?? '🌐' ?></span>
                        <span><?= e($info['name'] ?? $code) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Notifications -->
        <a href="<?= BASE_URL ?>/notifications" class="topbar-chip notif-chip" title="Notifications">
            <span class="chip-icon">🔔</span>
            <span class="notif-dot"></span>
        </a>

        <!-- Profile dropdown -->
        <div class="topbar-dropdown">
            <button type="button" class="topbar-avatar" id="avatarToggle">
                <?= e($initials) ?>
            </button>
            <div class="dropdown-menu profile-menu" id="avatarMenu">
                <div class="menu-header">
                    <div class="avatar-lg"><?= e($initials) ?></div>
                    <div>
                        <strong><?= e($userName) ?></strong>
                        <small><?= e($roleName) ?></small>
                    </div>
                </div>

                <div class="menu-divider"></div>

                <a href="<?= BASE_URL ?>/admin/profile" class="dropdown-item">
                    <span>👤</span> My Profile
                </a>

                <a href="<?= BASE_URL ?>/preferences" class="dropdown-item">
                    <span>⚙️</span> Preferences
                </a>

                <a href="<?= BASE_URL ?>/weather" class="dropdown-item">
                    <span>🌤️</span> Weather (<?= e($district) ?>)
                </a>

                <?php if ($isAdmin): ?>
                    <a href="<?= BASE_URL ?>/admin" class="dropdown-item">
                        <span>🛡️</span> Admin Panel
                    </a>
                <?php endif; ?>

                <div class="menu-divider"></div>

                <a href="<?= BASE_URL ?>/auth/logout"
                   class="dropdown-item logout"
                   onclick="return confirm('Log out?');">
                    <span>🚪</span> Logout
                </a>
            </div>
        </div>

    </div>
</header>

<script>
(function () {
    'use strict';

    // ═══════ DROPDOWN TOGGLES ═══════
    function setupDropdown(btnId, menuId) {
        var btn = document.getElementById(btnId);
        var menu = document.getElementById(menuId);
        if (!btn || !menu) return;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            document.querySelectorAll('.dropdown-menu').forEach(function (m) {
                if (m !== menu) m.classList.remove('open');
            });
            menu.classList.toggle('open');
        });
    }

    setupDropdown('langToggle', 'langMenu');
    setupDropdown('avatarToggle', 'avatarMenu');

    // Close on outside click
    document.addEventListener('click', function () {
        document.querySelectorAll('.dropdown-menu').forEach(function (m) {
            m.classList.remove('open');
        });
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu').forEach(function (m) {
                m.classList.remove('open');
            });
        }
    });

    // ═══════ LOCATION CHIP — Pulse on update ═══════
    window.updateLocationChip = function (district) {
        var chip = document.getElementById('chipDistrict');
        var wrap = document.getElementById('locationChip');
        if (!chip) return;
        chip.textContent = district;
        if (wrap) {
            wrap.style.animation = 'chipPulse 0.8s ease-out';
            setTimeout(function () { wrap.style.animation = ''; }, 900);
        }
    };
})();
</script>

<style>
/* ═══════════════════════════════════════════════════════════════
   TOPBAR
   ═══════════════════════════════════════════════════════════════ */

.topbar {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 20px !important;
    padding: 20px 32px !important;
    background: transparent !important;
    flex-wrap: wrap !important;
    margin: 0 !important;
}

.topbar-left { min-width: 0 !important; flex: 1 !important; }

.topbar-title {
    font-size: 24px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 0 4px 0 !important;
    line-height: 1.2 !important;
}

.topbar-sub {
    font-size: 13.5px !important;
    color: #94a3b8 !important;
    margin: 0 !important;
}

.topbar-right {
    display: flex !important;
    gap: 10px !important;
    align-items: center !important;
    flex-wrap: wrap !important;
}

/* CHIP BASE */
.topbar-chip {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 9px 16px !important;
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(12px) !important;
    -webkit-backdrop-filter: blur(12px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 999px !important;
    color: #cbd5e1 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
    font-family: inherit !important;
}

.topbar-chip:hover {
    background: rgba(30, 41, 59, 0.9) !important;
    border-color: rgba(56, 189, 248, 0.4) !important;
    color: #f1f5f9 !important;
    transform: translateY(-1px) !important;
}

.chip-icon { font-size: 14px !important; }
.chip-text { color: #f1f5f9 !important; }
.chip-caret { font-size: 10px !important; color: #64748b !important; margin-left: 2px !important; }

/* LOCATION CHIP — highlighted */
.location-chip {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.18), rgba(56, 189, 248, 0.12)) !important;
    border-color: rgba(16, 185, 129, 0.4) !important;
    color: #34d399 !important;
}

.location-chip .chip-text {
    color: #f1f5f9 !important;
    font-weight: 700 !important;
}

.location-chip:hover {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.28), rgba(56, 189, 248, 0.2)) !important;
    border-color: rgba(16, 185, 129, 0.6) !important;
}

@keyframes chipPulse {
    0%   { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); transform: scale(1); }
    50%  { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); transform: scale(1.04); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); transform: scale(1); }
}

/* NOTIFICATIONS */
.notif-chip { position: relative !important; padding: 9px 14px !important; }

.notif-dot {
    position: absolute !important;
    top: 8px !important;
    right: 10px !important;
    width: 8px !important;
    height: 8px !important;
    background: linear-gradient(135deg, #ef4444, #dc2626) !important;
    border-radius: 50% !important;
    border: 2px solid #0b1120 !important;
    animation: notifPulse 2s infinite !important;
}

@keyframes notifPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%      { opacity: 0.7; transform: scale(1.15); }
}

/* AVATAR */
.topbar-avatar {
    width: 42px !important;
    height: 42px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #10b981, #38bdf8) !important;
    color: #ffffff !important;
    border: none !important;
    font-size: 15px !important;
    font-weight: 800 !important;
    cursor: pointer !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
    transition: all 0.2s !important;
    font-family: inherit !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
}

.topbar-avatar:hover {
    transform: scale(1.06) !important;
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.5) !important;
}

/* DROPDOWNS */
.topbar-dropdown { position: relative !important; }

.dropdown-menu {
    position: absolute !important;
    top: calc(100% + 8px) !important;
    right: 0 !important;
    min-width: 220px !important;
    background: #1e293b !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 14px !important;
    padding: 8px !important;
    box-shadow: 0 20px 48px rgba(0, 0, 0, 0.5) !important;
    z-index: 9999 !important;
    opacity: 0 !important;
    visibility: hidden !important;
    transform: translateY(-6px) !important;
    transition: all 0.2s ease !important;
    box-sizing: border-box !important;
}

.dropdown-menu.open {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
}

.menu-header {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 10px 12px !important;
}

.menu-header strong {
    display: block !important;
    font-size: 13.5px !important;
    color: #f1f5f9 !important;
    font-weight: 700 !important;
    margin-bottom: 2px !important;
}

.menu-header small {
    display: block !important;
    font-size: 11px !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}

.avatar-lg {
    width: 42px !important;
    height: 42px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #10b981, #38bdf8) !important;
    color: #ffffff !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-weight: 800 !important;
    font-size: 15px !important;
    flex-shrink: 0 !important;
}

.menu-divider {
    height: 1px !important;
    background: rgba(255, 255, 255, 0.06) !important;
    margin: 8px 0 !important;
}

.dropdown-item {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    padding: 10px 12px !important;
    border-radius: 10px !important;
    color: #cbd5e1 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    transition: all 0.15s !important;
}

.dropdown-item:hover {
    background: rgba(56, 189, 248, 0.1) !important;
    color: #38bdf8 !important;
}

.dropdown-item.active {
    background: rgba(16, 185, 129, 0.12) !important;
    color: #34d399 !important;
}

.dropdown-item.logout:hover {
    background: rgba(239, 68, 68, 0.15) !important;
    color: #f87171 !important;
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .topbar { padding: 16px 20px !important; }
    .topbar-title { font-size: 20px !important; }
    .topbar-sub { font-size: 12.5px !important; }
}

@media (max-width: 600px) {
    .topbar { flex-direction: column !important; align-items: stretch !important; gap: 12px !important; }
    .topbar-right { justify-content: space-between !important; }
    .location-chip .chip-text,
    .topbar-chip#langToggle .chip-text { display: none !important; }
    .location-chip { padding: 9px 12px !important; }
}
</style>