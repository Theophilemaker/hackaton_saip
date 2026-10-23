<?php
$current   = $current   ?? ($_SESSION['lang'] ?? 'en');
$supported = $supported ?? [
    'en' => ['name' => 'English',     'flag' => '🇬🇧'],
    'rw' => ['name' => 'Kinyarwanda', 'flag' => '🇷🇼'],
    'fr' => ['name' => 'Français',    'flag' => '🇫🇷'],
    'sw' => ['name' => 'Kiswahili',   'flag' => '🇹🇿'],
];
?>
<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../../../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../../../layouts/topbar.php'; ?>

        <!-- HERO -->
        <section class="glass-card lang-hero">
            <div>
                <span class="hero-tag">
                    <span class="live-dot"></span> LANGUAGE
                </span>
                <h1>🌍 Language Settings</h1>
                <p>Choose the language you want to use across the entire SAIP platform. Your choice is saved to your account.</p>
            </div>
            <a href="<?= BASE_URL ?>/dashboard" class="btn-secondary">← Back to Dashboard</a>
        </section>

        <!-- LANGUAGE GRID -->
        <section class="glass-card">
            <div class="card-header">
                <div>
                    <h3>🗣️ Available Languages</h3>
                    <p class="card-subtitle">Select one to apply it immediately</p>
                </div>
            </div>

            <div class="lang-grid">
                <?php foreach ($supported as $code => $info): ?>
                    <a href="<?= BASE_URL ?>/language/switch?lang=<?= $code ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                       class="lang-card <?= $code === $current ? 'active' : '' ?>">
                        <span class="lang-flag"><?= $info['flag'] ?></span>
                        <strong><?= htmlspecialchars($info['name']) ?></strong>
                        <?php if ($code === $current): ?>
                            <span class="lang-check">✓ Currently Active</span>
                        <?php else: ?>
                            <span class="lang-action">Switch →</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="info-note">
                💡 Your language choice affects the dashboard, messages, forms, and AI responses throughout the platform.
            </div>
        </section>
    </main>
</div>

<style>
html,body{margin:0!important;padding:0!important;overflow-x:hidden!important}
.dashboard-layout{display:block!important;width:100%!important;background:#0b1120!important;min-height:100vh!important}
.main-content{margin-left:260px!important;width:calc(100% - 260px)!important;min-height:100vh!important;padding:20px 28px 60px!important;box-sizing:border-box!important;display:flex!important;flex-direction:column!important;gap:18px!important}
.glass-card{background:rgba(30,41,59,.7)!important;backdrop-filter:blur(16px)!important;border:1px solid rgba(255,255,255,.08)!important;border-radius:20px!important;padding:24px!important;box-sizing:border-box!important;width:100%!important}

.lang-hero{display:flex!important;justify-content:space-between!important;align-items:center!important;gap:20px!important;flex-wrap:wrap!important;padding:24px 28px!important;background:linear-gradient(135deg,rgba(56,189,248,.15),rgba(167,139,250,.08))!important}
.hero-tag{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:rgba(56,189,248,.15);color:#38bdf8;border:1px solid rgba(56,189,248,.35);border-radius:999px;font-size:10.5px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;margin-bottom:10px}
.live-dot{width:6px;height:6px;background:#38bdf8;border-radius:50%;animation:pulse 1.6s infinite;box-shadow:0 0 8px #38bdf8}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.lang-hero h1{font-size:26px;font-weight:800;color:#f1f5f9;margin:0 0 6px}
.lang-hero p{color:#94a3b8;font-size:13.5px;margin:0;max-width:600px;line-height:1.55}

.btn-secondary{display:inline-flex;align-items:center;gap:8px;padding:12px 22px;background:rgba(255,255,255,.06);color:#f1f5f9;border:1px solid rgba(255,255,255,.14);border-radius:12px;font-size:13px;font-weight:700;text-decoration:none;transition:.2s}
.btn-secondary:hover{background:rgba(255,255,255,.12);transform:translateY(-2px)}

.card-header{display:flex!important;justify-content:space-between!important;align-items:center!important;margin-bottom:22px!important;padding-bottom:14px!important;border-bottom:1px solid rgba(255,255,255,.08)!important}
.card-header h3{font-size:16px!important;font-weight:800!important;color:#f1f5f9!important;margin:0!important}
.card-subtitle{font-size:12.5px!important;color:#94a3b8!important;margin:4px 0 0 0!important}

.lang-grid{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))!important;gap:16px!important}
.lang-card{display:flex!important;flex-direction:column!important;align-items:center!important;gap:10px!important;padding:26px 20px!important;background:rgba(0,0,0,.2)!important;border:2px solid rgba(255,255,255,.06)!important;border-radius:16px!important;text-decoration:none!important;transition:all .2s!important;cursor:pointer!important;position:relative!important}
.lang-card:hover{border-color:rgba(56,189,248,.4)!important;transform:translateY(-4px)!important;background:rgba(56,189,248,.05)!important;box-shadow:0 12px 32px rgba(0,0,0,.35)!important}
.lang-card.active{border-color:#10b981!important;background:linear-gradient(135deg,rgba(16,185,129,.12),rgba(5,150,105,.06))!important;box-shadow:0 0 0 4px rgba(16,185,129,.1)!important}
.lang-flag{font-size:48px!important;line-height:1!important;filter:drop-shadow(0 4px 12px rgba(0,0,0,.3))!important}
.lang-card strong{font-size:15px!important;color:#f1f5f9!important;font-weight:800!important}
.lang-check{font-size:11px!important;color:#34d399!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.6px!important}
.lang-action{font-size:12px!important;color:#94a3b8!important;font-weight:700!important;transition:.2s!important}
.lang-card:hover .lang-action{color:#38bdf8!important}

.info-note{margin-top:20px;padding:14px 18px;background:rgba(251,191,36,.06);border-left:3px solid #fbbf24;border-radius:10px;font-size:12.5px;color:#fbbf24;line-height:1.55}

@media(max-width:900px){
    .main-content{margin-left:0!important;width:100%!important;padding:14px!important;padding-top:72px!important}
    .lang-hero{flex-direction:column!important;align-items:flex-start!important}
    .lang-hero h1{font-size:20px!important}
    .btn-secondary{width:100%!important;justify-content:center!important}
}
</style>