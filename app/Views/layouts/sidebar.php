<?php
/**
 * SAIP Global Sidebar — v9
 * ✅ Homepage button
 * ✅ Friends link with badge
 * ✅ Messages link → /Messages (InfinityFree-safe)
 * ✅ Structure: aside > brand + nav + footer
 */

$currentUrl = $_SERVER['REQUEST_URI'] ?? '';
$active = fn($path) => str_contains($currentUrl, $path) ? 'active' : '';

$roleId = (int)($_SESSION['role_id'] ?? 0);
$userId = (int)($_SESSION['user_id'] ?? 0);

/* Unread notification count */
$sidebarNotifCount = 0;
if ($userId > 0) {
    try {
        $notifModel = new \App\Models\Notification();
        $sidebarNotifCount = $notifModel->countUnread($userId);
    } catch (\Throwable $e) {
        $sidebarNotifCount = 0;
    }
}

/* Pending friend request count */
$sidebarFriendCount = 0;
if ($userId > 0) {
    try {
        $db = new \App\Core\Database();
        $db->query("SELECT COUNT(*) AS c FROM friend_requests WHERE receiver_id = :me AND status = 'pending'");
        $db->bind(':me', $userId);
        $r = $db->single();
        $sidebarFriendCount = (int)($r['c'] ?? 0);
    } catch (\Throwable $e) {
        $sidebarFriendCount = 0;
    }
}

/* User display data */
$userName  = $_SESSION['name']  ?? 'User';
$userEmail = $_SESSION['email'] ?? '';
$userPhoto = $_SESSION['profile_photo'] ?? null;
$initials  = strtoupper(mb_substr($userName, 0, 1, 'UTF-8'));
if (strpos($userName, ' ') !== false) {
    $parts    = explode(' ', $userName);
    $initials = strtoupper(mb_substr($parts[0], 0, 1, 'UTF-8') . mb_substr(end($parts), 0, 1, 'UTF-8'));
}

$roleLabels = [
    1 => 'Farmer', 2 => 'Cooperative Leader', 3 => 'Agricultural Officer',
    4 => 'Government Officer', 5 => 'Exporter', 6 => 'Researcher', 7 => 'Administrator',
];
$roleLabel = $roleLabels[$roleId] ?? 'User';

$settingsOpen = (strpos($currentUrl, '/admin') !== false)
             || (strpos($currentUrl, '/settings') !== false)
             || (strpos($currentUrl, '/preferences') !== false)
             || (strpos($currentUrl, '/profile') !== false);
?>

<!-- ═══════════ MOBILE TOGGLE ═══════════ -->
<button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Toggle menu">
    <span></span><span></span><span></span>
</button>

<!-- ═══════════ SIDEBAR ═══════════ -->
<aside class="sidebar" id="sidebar">

    <!-- 1. BRAND -->
    <div class="sidebar-brand">
        <!-- /chat URL rewriter — MUST load before any chat JS -->
        <script src="<?= BASE_URL ?>/public/js/messages-fix.js"></script>
        <a href="<?= BASE_URL ?>/dashboard" class="brand-link">
            <span class="brand-logo">🌱</span>
            <div class="brand-text">
                <h3 class="brand-name"><?= __('app_name') ?: 'SAIP' ?></h3>
                <small class="brand-tagline"><?= __('app_tagline') ?: 'Smart Agriculture' ?></small>
            </div>
        </a>
    </div>

    <!-- 2. NAVIGATION (scrolls) -->
    <nav class="sidebar-nav">

        <!-- ═══ HOMEPAGE ═══ -->
        <a href="<?= BASE_URL ?>/home" class="nav-item nav-item-home" target="_blank" rel="noopener">
            <span class="nav-icon">🏠</span>
            <span class="nav-text">Homepage</span>
        </a>

        <!-- ─── MAIN ─── -->
        <div class="nav-section-label">Main</div>

        <a href="<?= BASE_URL ?>/dashboard" class="nav-item <?= $active('/dashboard') ?>">
            <span class="nav-icon">📊</span>
            <span class="nav-text"><?= __('nav.dashboard') ?: 'Dashboard' ?></span>
        </a>

        <a href="<?= BASE_URL ?>/weather" class="nav-item <?= $active('/weather') ?>">
            <span class="nav-icon">🌤️</span>
            <span class="nav-text"><?= __('nav.weather') ?: 'Weather' ?></span>
        </a>

        <a href="<?= BASE_URL ?>/crops" class="nav-item <?= $active('/crops') ?>">
            <span class="nav-icon">🌾</span>
            <span class="nav-text"><?= __('nav.crops') ?: 'Crop Intelligence' ?></span>
        </a>

        <!-- ─── MY FARM ─── -->
        <div class="nav-section-label">My Farm</div>
        <a href="<?= BASE_URL ?>/yield" class="nav-item">
    <span class="nav-icon">📈</span>
    <span class="nav-text">Yield Predictor</span>
</a>

<a href="<?= BASE_URL ?>/soil" class="nav-item">
    <span class="nav-icon">🪨</span>
    <span class="nav-text">Soil Health</span>
</a>

<a href="<?= BASE_URL ?>/irrigation" class="nav-item">
    <span class="nav-icon">💧</span>
    <span class="nav-text">Irrigation</span>
</a>

<a href="<?= BASE_URL ?>/postharvest" class="nav-item">
    <span class="nav-icon">📦</span>
    <span class="nav-text">Post-Harvest</span>
</a>

<a href="<?= BASE_URL ?>/export" class="nav-item">
    <span class="nav-icon">🌍</span>
    <span class="nav-text">Export Markets</span>
</a>

        <a href="<?= BASE_URL ?>/agromonitoring" class="nav-item <?= $active('/agromonitoring') ?>">
            <span class="nav-icon">🛰️</span>
            <span class="nav-text">Agromonitoring</span>
        </a>

        <a href="<?= BASE_URL ?>/crops/agribot" class="nav-item <?= $active('/crops/agribot') ?>">
            <span class="nav-icon">🤖</span>
            <span class="nav-text">AgriBot Assistant</span>
            <span class="nav-badge" style="background:linear-gradient(135deg,#16A34A,#7C3AED)!important;animation:none!important;">AI</span>
        </a>

        <a href="<?= BASE_URL ?>/demoAdmin" class="nav-item nav-sub">
            <span class="nav-icon">🎬</span>
            <span class="nav-text">Demo Videos</span>
        </a>

        <a href="<?= BASE_URL ?>/assistant" class="nav-item <?= $active('/assistant') ?>">
            <span class="nav-icon">🧑‍🌾</span>
            <span class="nav-text">Farm Assistant</span>
            <span class="nav-badge" style="background:linear-gradient(135deg,#a78bfa,#7c3aed)!important;animation:none!important;">AI</span>
        </a>

        <a href="<?= BASE_URL ?>/advice" class="nav-item <?= $active('/advice') && !str_contains($currentUrl, '/advice/') ?>">
            <span class="nav-icon">🌱</span>
            <span class="nav-text">My Advice</span>
        </a>

        <a href="<?= BASE_URL ?>/advice/crops" class="nav-item <?= $active('/advice/crops') ?>">
            <span class="nav-icon">🌾</span>
            <span class="nav-text">What to Plant</span>
        </a>

        <a href="<?= BASE_URL ?>/advice/benchmark" class="nav-item <?= $active('/advice/benchmark') ?>">
            <span class="nav-icon">📊</span>
            <span class="nav-text">Benchmark</span>
        </a>

        <a href="<?= BASE_URL ?>/advice/league" class="nav-item <?= $active('/advice/league') ?>">
            <span class="nav-icon">🏆</span>
            <span class="nav-text">District League</span>
        </a>

        <!-- ─── COMMERCE ─── -->
        <div class="nav-section-label">Commerce</div>

        <a href="<?= BASE_URL ?>/marketplace" class="nav-item <?= $active('/marketplace') ?>">
            <span class="nav-icon">🛒</span>
            <span class="nav-text"><?= __('nav.marketplace') ?: 'Marketplace' ?></span>
        </a>

        <a href="<?= BASE_URL ?>/cooperative" class="nav-item <?= $active('/cooperative') ?>">
            <span class="nav-icon">👥</span>
            <span class="nav-text"><?= __('nav.coops') ?: 'Cooperatives' ?></span>
        </a>

        <a href="<?= BASE_URL ?>/logistics" class="nav-item <?= $active('/logistics') && !str_contains($currentUrl, '/logistics/tracking') ?>">
            <span class="nav-icon">🚚</span>
            <span class="nav-text"><?= __('nav.logistics') ?: 'Logistics' ?></span>
        </a>

        <a href="<?= BASE_URL ?>/logistics/tracking" class="nav-item <?= $active('/logistics/tracking') ?>">
            <span class="nav-icon">📡</span>
            <span class="nav-text">Live Tracking</span>
        </a>

        <a href="<?= BASE_URL ?>/storage" class="nav-item <?= $active('/storage') ?>">
            <span class="nav-icon">🏭</span>
            <span class="nav-text"><?= __('nav.storage') ?: 'Storage' ?></span>
        </a>

        <!-- ─── COMMUNITY ─── -->
        <div class="nav-section-label">Community</div>

        <!-- ★ MESSAGES — uses /Messages because InfinityFree blocks "chat" -->
        <a href="<?= BASE_URL ?>/Messages" class="nav-item <?= ($active('/Messages') || $active('/Chat')) ?>">
            <span class="nav-icon">💬</span>
            <span class="nav-text">Messages</span>
        </a>

        <!-- ★ FRIENDS — with pending request badge -->
        <a href="<?= BASE_URL ?>/Friends" class="nav-item <?= $active('/Friends') ?>">
            <span class="nav-icon">👫</span>
            <span class="nav-text">Friends</span>
            <?php if ($sidebarFriendCount > 0): ?>
                <span class="nav-badge" id="sidebarFriendBadge"><?= $sidebarFriendCount > 99 ? '99+' : $sidebarFriendCount ?></span>
            <?php else: ?>
                <span class="nav-badge" id="sidebarFriendBadge" style="display:none;"></span>
            <?php endif; ?>
        </a>

        <a href="<?= BASE_URL ?>/feed" class="nav-item <?= $active('/feed') ?>">
            <span class="nav-icon">📰</span>
            <span class="nav-text"><?= __('chat.feed') ?: 'Community Feed' ?></span>
        </a>

        <!-- ─── ANALYTICS ─── -->
        <?php if (in_array($roleId, [3, 4, 6, 7])): ?>
            <div class="nav-section-label">Analytics</div>

            <a href="<?= BASE_URL ?>/government" class="nav-item <?= $active('/government') ?>">
                <span class="nav-icon">🏛️</span>
                <span class="nav-text"><?= __('nav.government') ?: 'Government' ?></span>
            </a>

            <a href="<?= BASE_URL ?>/nisr" class="nav-item <?= $active('/nisr') ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-text"><?= __('nav.nisr') ?: 'NISR Portal' ?></span>
            </a>

            <a href="<?= BASE_URL ?>/gis" class="nav-item <?= $active('/gis') ?>">
                <span class="nav-icon">🗺️</span>
                <span class="nav-text"><?= __('nav.gis') ?: 'GIS Maps' ?></span>
            </a>

            <a href="<?= BASE_URL ?>/reports" class="nav-item <?= $active('/reports') ?>">
                <span class="nav-icon">📄</span>
                <span class="nav-text"><?= __('nav.reports') ?: 'Reports' ?></span>
            </a>
        <?php endif; ?>

        <!-- ─── CONFIGURATION ─── -->
        <div class="nav-section-label">Configuration</div>

        <a href="<?= BASE_URL ?>/profile" class="nav-item <?= $active('/profile') ?>">
            <span class="nav-icon">👤</span>
            <span class="nav-text">My Profile</span>
        </a>

        <div class="nav-group <?= $settingsOpen ? 'open' : '' ?>" id="settingsGroup">
            <a href="javascript:void(0)" class="nav-item nav-group-toggle" data-toggle="settings">
                <span class="nav-icon">⚙️</span>
                <span class="nav-text">Settings</span>
                <span class="nav-chevron">▾</span>
            </a>

            <div class="nav-group-content">

                <?php if ($roleId === 7): ?>
                    <a href="<?= BASE_URL ?>/admin" class="nav-item nav-sub">
                        <span class="nav-icon">🛡️</span>
                        <span class="nav-text">Admin Dashboard</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/users" class="nav-item nav-sub">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text">Users</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/roles" class="nav-item nav-sub">
                        <span class="nav-icon">🔐</span>
                        <span class="nav-text">Roles & Permissions</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/crops" class="nav-item nav-sub">
                        <span class="nav-icon">🌾</span>
                        <span class="nav-text">Manage Crops</span>
                    </a>
                    <a href="<?= BASE_URL ?>/diseaseAdmin" class="nav-item nav-sub">
                        <span class="nav-icon">🧬</span>
                        <span class="nav-text">Disease Library</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/uploads" class="nav-item nav-sub">
                        <span class="nav-icon">🗂️</span>
                        <span class="nav-text">Uploaded Data</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/branding" class="nav-item nav-sub">
                        <span class="nav-icon">🎨</span>
                        <span class="nav-text">Branding</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/email" class="nav-item nav-sub">
                        <span class="nav-icon">📧</span>
                        <span class="nav-text">Email Settings</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/sms" class="nav-item nav-sub">
                        <span class="nav-icon">📱</span>
                        <span class="nav-text">SMS Settings</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/backup" class="nav-item nav-sub">
                        <span class="nav-icon">💾</span>
                        <span class="nav-text">Backup</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/activity" class="nav-item nav-sub">
                        <span class="nav-icon">📋</span>
                        <span class="nav-text">Activity Log</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/health" class="nav-item nav-sub">
                        <span class="nav-icon">⚙️</span>
                        <span class="nav-text">System Health</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/translations" class="nav-item nav-sub">
                        <span class="nav-icon">🌐</span>
                        <span class="nav-text">Translations</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/apiKeys" class="nav-item nav-sub">
                        <span class="nav-icon">🔑</span>
                        <span class="nav-text">API Keys</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/models" class="nav-item nav-sub">
                        <span class="nav-icon">🤖</span>
                        <span class="nav-text">AI Models</span>
                    </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/notifications" class="nav-item nav-sub">
                    <span class="nav-icon">🔔</span>
                    <span class="nav-text">Notifications</span>
                    <?php if (!empty($sidebarNotifCount) && $sidebarNotifCount > 0): ?>
                        <span class="nav-badge"><?= $sidebarNotifCount > 99 ? '99+' : $sidebarNotifCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>/preferences" class="nav-item nav-sub">
                    <span class="nav-icon">🎨</span>
                    <span class="nav-text">Preferences</span>
                </a>
                <a href="<?= BASE_URL ?>/language" class="nav-item nav-sub">
                    <span class="nav-icon">🌍</span>
                    <span class="nav-text">Language</span>
                </a>

            </div>
        </div>

    </nav>

    <!-- 3. FOOTER (pinned) -->
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">
                <?php if (!empty($userPhoto)): ?>
                    <img src="<?= htmlspecialchars($userPhoto, ENT_QUOTES, 'UTF-8') ?>" alt="">
                <?php else: ?>
                    <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
                <small class="user-role"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></small>
            </div>
        </div>

        <a href="<?= BASE_URL ?>/auth/logout" class="logout-btn"
           onclick="return confirm('Are you sure you want to logout?');">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Logout</span>
        </a>
    </div>

</aside>

<!-- Mobile overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ═══════════════ STYLES (unchanged from your original — kept intact) ═══════════════ -->
<style id="sidebar-layout-fix">
html, body { margin: 0 !important; padding: 0 !important; overflow-x: hidden !important; }

@media (min-width: 769px) {
    .dashboard-layout > .main-content {
        margin-left: 260px !important;
        width: calc(100% - 260px) !important;
        max-width: calc(100% - 260px) !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
        padding: 32px !important;
    }
    .dashboard-layout .main-wrap {
        margin-left: 260px !important;
        width: calc(100% - 260px) !important;
        max-width: calc(100% - 260px) !important;
        min-width: 0 !important;
        min-height: 100vh !important;
        box-sizing: border-box !important;
    }
    .dashboard-layout .main-wrap > .main-content {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
        padding: 20px 28px 60px !important;
    }
    .dashboard-layout {
        display: block !important;
        width: 100% !important;
        min-height: 100vh !important;
        padding-left: 0 !important;
        margin: 0 !important;
    }
}

@media (max-width: 768px) {
    .dashboard-layout > .main-content,
    .dashboard-layout .main-wrap,
    .dashboard-layout .main-wrap > .main-content {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 16px !important;
        padding-top: 72px !important;
    }
}

:root {
    --sidebar-width: 260px;
    --sidebar-bg: linear-gradient(180deg, #0f172a 0%, #081C15 100%);
    --sidebar-border: rgba(255, 255, 255, 0.06);
    --nav-text: #94a3b8;
    --nav-active-bg: linear-gradient(135deg, rgba(16, 185, 129, 0.18), rgba(5, 150, 105, 0.1));
    --nav-active-text: #34d399;
    --nav-active-border: #10b981;
}

.sidebar {
    position: fixed !important;
    top: 0 !important; left: 0 !important;
    width: 260px !important;
    max-width: 260px !important;
    height: 100vh !important;
    max-height: 100vh !important;
    background: var(--sidebar-bg) !important;
    border-right: 1px solid var(--sidebar-border) !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
    z-index: 1000 !important;
    box-sizing: border-box !important;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    transition: transform 0.3s ease !important;
}

.sidebar .sidebar-brand {
    flex: 0 0 auto !important;
    padding: 22px 20px 18px !important;
    border-bottom: 1px solid var(--sidebar-border) !important;
    box-sizing: border-box !important;
    width: 100% !important;
}

.brand-link { display: flex !important; align-items: center !important; gap: 12px !important; text-decoration: none !important; color: inherit !important; }
.brand-logo { font-size: 34px !important; line-height: 1 !important; filter: drop-shadow(0 0 12px rgba(34, 197, 94, 0.5)) !important; flex-shrink: 0 !important; }
.brand-text { display: flex !important; flex-direction: column !important; gap: 2px !important; min-width: 0 !important; overflow: hidden !important; }
.brand-name { font-size: 20px !important; font-weight: 800 !important; color: #10b981 !important; letter-spacing: 2px !important; line-height: 1 !important; margin: 0 !important; }
.brand-tagline { font-size: 9px !important; color: #64748b !important; text-transform: uppercase !important; letter-spacing: 1px !important; font-weight: 600 !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; }

.sidebar .sidebar-nav {
    flex: 1 1 auto !important;
    min-height: 0 !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    padding: 14px 12px 8px !important;
    box-sizing: border-box !important;
    scrollbar-width: thin !important;
    scrollbar-color: rgba(255, 255, 255, 0.1) transparent !important;
}
.sidebar .sidebar-nav::-webkit-scrollbar { width: 6px; }
.sidebar .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
.sidebar .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 999px; }
.sidebar .sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }

.nav-section-label {
    display: block !important;
    padding: 14px 12px 6px !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    color: #475569 !important;
    text-transform: uppercase !important;
    letter-spacing: 1.2px !important;
}
.nav-section-label:first-child { padding-top: 6px !important; }

.nav-item {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 10px 14px !important;
    border-radius: 10px !important;
    text-decoration: none !important;
    color: var(--nav-text) !important;
    font-size: 13.5px !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
    margin-bottom: 2px !important;
    position: relative !important;
    border-left: 3px solid transparent !important;
    cursor: pointer !important;
    background: transparent !important;
    width: 100% !important;
    text-align: left !important;
    box-sizing: border-box !important;
    font-family: inherit !important;
    min-width: 0 !important;
}
.nav-item:hover { background: rgba(255, 255, 255, 0.04) !important; color: #f1f5f9 !important; }
.nav-item.active {
    background: var(--nav-active-bg) !important;
    color: var(--nav-active-text) !important;
    font-weight: 600 !important;
    border-left-color: var(--nav-active-border) !important;
    padding-left: 11px !important;
}
.nav-item.active .nav-icon { transform: scale(1.1) !important; }

.nav-item-home {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.12), rgba(167, 139, 250, 0.06)) !important;
    color: #38bdf8 !important;
    font-weight: 700 !important;
    border: 1px solid rgba(56, 189, 248, 0.25) !important;
    margin-bottom: 10px !important;
    border-left: 3px solid #38bdf8 !important;
}
.nav-item-home:hover {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.22), rgba(167, 139, 250, 0.1)) !important;
    border-color: rgba(56, 189, 248, 0.55) !important;
    color: #38bdf8 !important;
    transform: translateX(3px) !important;
    box-shadow: 0 4px 14px rgba(56, 189, 248, 0.2) !important;
}
.nav-item-home .nav-icon { font-size: 17px !important; }

.nav-icon {
    font-size: 16px !important;
    width: 22px !important;
    text-align: center !important;
    flex-shrink: 0 !important;
    transition: transform 0.2s ease !important;
}
.nav-text {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}
.nav-badge {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 22px !important;
    height: 18px !important;
    padding: 0 6px !important;
    background: linear-gradient(135deg, #ef4444, #dc2626) !important;
    color: #ffffff !important;
    font-size: 9.5px !important;
    font-weight: 800 !important;
    border-radius: 999px !important;
    flex: 0 0 auto !important;
    margin-left: 2px !important;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4) !important;
}

.nav-group { margin-bottom: 2px !important; }
.nav-group-toggle {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    user-select: none !important;
}
.nav-chevron {
    font-size: 12px !important;
    transition: transform 0.3s ease !important;
    color: #64748b !important;
    flex-shrink: 0 !important;
    margin-left: 4px !important;
}
.nav-group.open .nav-chevron { transform: rotate(180deg) !important; color: #34d399 !important; }
.nav-group-content {
    max-height: 0 !important;
    overflow: hidden !important;
    transition: max-height 0.4s ease !important;
}
.nav-group.open .nav-group-content { max-height: 1200px !important; }
.nav-item.nav-sub {
    padding: 9px 14px 9px 36px !important;
    font-size: 12.5px !important;
    color: #94a3b8 !important;
    border-left: none !important;
    margin-bottom: 1px !important;
    border-radius: 8px !important;
}
.nav-item.nav-sub:hover { background: rgba(255, 255, 255, 0.04) !important; color: #f1f5f9 !important; }
.nav-item.nav-sub.active {
    background: rgba(16, 185, 129, 0.12) !important;
    color: #34d399 !important;
    font-weight: 600 !important;
    border-left: none !important;
    padding-left: 36px !important;
}
.nav-item.nav-sub .nav-icon { font-size: 13px !important; width: 18px !important; }

.sidebar .sidebar-footer {
    flex: 0 0 auto !important;
    flex-shrink: 0 !important;
    padding: 14px 12px 16px !important;
    border-top: 1px solid var(--sidebar-border) !important;
    background: rgba(0, 0, 0, 0.25) !important;
    box-sizing: border-box !important;
    width: 100% !important;
}
.user-card {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 10px 12px !important;
    border-radius: 10px !important;
    background: rgba(255, 255, 255, 0.03) !important;
    border: 1px solid rgba(255, 255, 255, 0.04) !important;
    margin-bottom: 10px !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
    width: 100% !important;
}
.user-avatar {
    width: 42px !important;
    height: 42px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #10b981, #38bdf8) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 15px !important;
    font-weight: 800 !important;
    color: #ffffff !important;
    flex-shrink: 0 !important;
    overflow: hidden !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25) !important;
}
.user-avatar img { width: 100% !important; height: 100% !important; object-fit: cover !important; }
.user-info { flex: 1 !important; min-width: 0 !important; overflow: hidden !important; }
.user-name {
    display: block !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    line-height: 1.2 !important;
    margin-bottom: 2px !important;
}
.user-role {
    display: block !important;
    font-size: 10px !important;
    color: #64748b !important;
    text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
    font-weight: 700 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}
.logout-btn {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 10px !important;
    width: 100% !important;
    padding: 12px 16px !important;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.08)) !important;
    border: 1px solid rgba(239, 68, 68, 0.35) !important;
    border-radius: 10px !important;
    color: #fca5a5 !important;
    text-decoration: none !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    letter-spacing: 0.5px !important;
    transition: all 0.25s ease !important;
    cursor: pointer !important;
    box-sizing: border-box !important;
    font-family: inherit !important;
}
.logout-btn:hover {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.3), rgba(220, 38, 38, 0.18)) !important;
    border-color: #ef4444 !important;
    color: #ffffff !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.35) !important;
}

.main-wrap > .sidebar-footer,
.main-content .sidebar-footer,
.dashboard-layout > .sidebar-footer,
body > .sidebar-footer {
    display: none !important;
}

.sidebar-toggle {
    display: none !important;
    position: fixed !important;
    top: 16px !important; left: 16px !important;
    z-index: 1002 !important;
    width: 44px !important;
    height: 44px !important;
    background: rgba(15, 23, 42, 0.95) !important;
    backdrop-filter: blur(12px) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 12px !important;
    cursor: pointer !important;
    padding: 12px !important;
    flex-direction: column !important;
    justify-content: space-around !important;
    box-sizing: border-box !important;
}
.sidebar-toggle span { display: block !important; height: 2px !important; background: #10b981 !important; border-radius: 2px !important; transition: 0.3s ease !important; }
.sidebar-toggle.active span:nth-child(1) { transform: translateY(8px) rotate(45deg) !important; }
.sidebar-toggle.active span:nth-child(2) { opacity: 0 !important; }
.sidebar-toggle.active span:nth-child(3) { transform: translateY(-8px) rotate(-45deg) !important; }

.sidebar-overlay {
    display: none !important;
    position: fixed !important;
    inset: 0 !important;
    background: rgba(0, 0, 0, 0.7) !important;
    backdrop-filter: blur(2px) !important;
    z-index: 999 !important;
    opacity: 0 !important;
    transition: opacity 0.3s ease !important;
}
.sidebar-overlay.active { display: block !important; opacity: 1 !important; }

@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%) !important; }
    .sidebar.open { transform: translateX(0) !important; box-shadow: 8px 0 32px rgba(0, 0, 0, 0.5) !important; }
    .sidebar-toggle { display: flex !important; }
}
</style>

<!-- ═══════════════ SCRIPTS ═══════════════ -->
<script>
document.body.classList.add('has-sidebar');

(function() {
    'use strict';

    /* Settings toggle */
    document.addEventListener('click', function(e) {
        var toggle = e.target.closest('[data-toggle="settings"]');
        if (toggle) {
            e.preventDefault();
            e.stopPropagation();
            var group = document.getElementById('settingsGroup');
            if (group) group.classList.toggle('open');
        }
    });

    /* Mobile sidebar */
    document.addEventListener('DOMContentLoaded', function() {
        var sidebar = document.getElementById('sidebar');
        var toggle  = document.getElementById('sidebarToggle');
        var overlay = document.getElementById('sidebarOverlay');

        if (toggle && sidebar) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('open');
                toggle.classList.toggle('active');
                if (overlay) overlay.classList.toggle('active');
            });
        }

        if (overlay && sidebar) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                if (toggle) toggle.classList.remove('active');
                overlay.classList.remove('active');
            });
        }

        document.querySelectorAll('.sidebar-nav a:not([data-toggle])').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768 && sidebar) {
                    sidebar.classList.remove('open');
                    if (toggle) toggle.classList.remove('active');
                    if (overlay) overlay.classList.remove('active');
                }
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                if (toggle) toggle.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
            }
        });

        var activeItem = document.querySelector('.nav-item.active');
        if (activeItem) {
            setTimeout(function() {
                activeItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }, 100);
        }
    });

    /* Live friend-request badge refresh every 30s */
    function refreshFriendBadge() {
        fetch('<?= BASE_URL ?>/Friends/count', { credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                var b = document.getElementById('sidebarFriendBadge');
                if (!b) return;
                if (d.count > 0) {
                    b.textContent = d.count > 99 ? '99+' : d.count;
                    b.style.display = 'inline-flex';
                } else {
                    b.style.display = 'none';
                }
            })
            .catch(function(){});
    }
    refreshFriendBadge();
    setInterval(refreshFriendBadge, 30000);
})();
</script>