<?php
$u       = $user   ?? [];
$stats   = $stats  ?? [];
$isOwn   = $isOwn  ?? false;
$name    = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
$initials = strtoupper(substr($name ?: 'U', 0, 1));
if (strpos($name, ' ') !== false) {
    $p = explode(' ', $name);
    $initials = strtoupper(substr($p[0],0,1) . substr(end($p),0,1));
}
$avatar  = $u['profile_photo'] ?? null;
$cover   = $u['cover_photo']   ?? null;

$flashes = [];
foreach (['success','error'] as $t) {
    if (!empty($_SESSION['flash_'.$t])) {
        $flashes[$t] = $_SESSION['flash_'.$t];
        unset($_SESSION['flash_'.$t]);
    }
}
?>
<div class="dashboard-layout profile-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="main-content">

            <?php foreach ($flashes as $t => $msg): ?>
                <div class="flash flash-<?= $t ?>"><?= e($msg) ?></div>
            <?php endforeach; ?>

            <!-- ═══════════ PROFILE HERO ═══════════ -->
            <section class="glass-card profile-hero">
                <div class="cover-wrap" <?= $cover ? 'style="background-image:url(' . e($cover) . ')"' : '' ?>>
                    <?php if (!$cover): ?>
                        <div class="cover-placeholder"></div>
                    <?php endif; ?>
                </div>

                <div class="hero-body">
                    <div class="avatar-wrap">
                        <?php if ($avatar): ?>
                            <img src="<?= e($avatar) ?>" alt="" class="profile-avatar-img">
                        <?php else: ?>
                            <div class="profile-avatar-initials"><?= e($initials) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="hero-info">
                        <div class="hero-name-row">
                            <h1><?= e($name ?: 'User') ?></h1>
                            <?php if (($u['role_name'] ?? '') !== ''): ?>
                                <span class="role-chip"><?= e($u['role_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="hero-bio"><?= e($u['bio'] ?? 'No bio yet.') ?></p>

                        <div class="hero-meta">
                            <?php if (!empty($u['district'])): ?>
                                <span>📍 <?= e($u['district']) ?><?= !empty($u['sector']) ? ', ' . e($u['sector']) : '' ?></span>
                            <?php endif; ?>
                            <?php if (!empty($u['email'])): ?>
                                <span>📧 <?= e($u['email']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($u['phone'])): ?>
                                <span>📞 <?= e($u['phone']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($u['primary_crops'])): ?>
                                <span>🌾 <?= e($u['primary_crops']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($u['years_experience'])): ?>
                                <span>⏳ <?= (int)$u['years_experience'] ?> yrs experience</span>
                            <?php endif; ?>
                        </div>

                        <div class="hero-actions">
                            <?php if ($isOwn): ?>
                                <a href="<?= BASE_URL ?>/profile/edit" class="btn-hero-primary">✏️ Edit Profile</a>
                                <a href="<?= BASE_URL ?>/chat" class="btn-hero-secondary">💬 Messages</a>
                            <?php else: ?>
                                <button type="button" class="btn-hero-primary" onclick="messageUser(<?= (int)$u['id'] ?>)">
                                    💬 Send Message
                                </button>
                                <button type="button" class="btn-hero-secondary" onclick="followUser(<?= (int)$u['id'] ?>, this)">
                                    ➕ Follow
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════ STATS ═══════════ -->
            <section class="stats-strip">
                <div class="stat-card"><span class="stat-icon">📝</span>
                    <div><strong><?= number_format($stats['posts'] ?? 0) ?></strong><small>Posts</small></div>
                </div>
                <div class="stat-card"><span class="stat-icon">🏡</span>
                    <div><strong><?= number_format($stats['farms'] ?? 0) ?></strong><small>Farms</small></div>
                </div>
                <div class="stat-card"><span class="stat-icon">🌱</span>
                    <div><strong><?= number_format($stats['active_crops'] ?? 0) ?></strong><small>Active Crops</small></div>
                </div>
                <div class="stat-card"><span class="stat-icon">🔬</span>
                    <div><strong><?= number_format($stats['detections'] ?? 0) ?></strong><small>Scans</small></div>
                </div>
            </section>

            <!-- ═══════════ INFO PANELS ═══════════ -->
            <div class="profile-layout">
                <section class="glass-card">
                    <h3 class="panel-title">📋 About</h3>
                    <div class="info-list">
                        <div class="info-row"><span>Full name</span><strong><?= e($name) ?></strong></div>
                        <div class="info-row"><span>Role</span><strong><?= e($u['role_name'] ?? '—') ?></strong></div>
                        <div class="info-row"><span>Email</span><strong><?= e($u['email'] ?? '—') ?></strong></div>
                        <div class="info-row"><span>Phone</span><strong><?= e($u['phone'] ?? '—') ?></strong></div>
                        <div class="info-row"><span>WhatsApp</span><strong><?= e($u['whatsapp'] ?? '—') ?></strong></div>
                        <div class="info-row"><span>District</span><strong><?= e($u['district'] ?? '—') ?></strong></div>
                        <div class="info-row"><span>Member since</span><strong><?= !empty($u['created_at']) ? date('M Y', strtotime($u['created_at'])) : '—' ?></strong></div>
                    </div>
                </section>

                <section class="glass-card">
                    <h3 class="panel-title">🌾 Farming Details</h3>
                    <div class="info-list">
                        <div class="info-row"><span>Primary crops</span><strong><?= e($u['primary_crops'] ?? '—') ?></strong></div>
                        <div class="info-row"><span>Farm size</span><strong><?= !empty($u['farm_size_ha']) ? number_format((float)$u['farm_size_ha'], 2) . ' ha' : '—' ?></strong></div>
                        <div class="info-row"><span>Experience</span><strong><?= !empty($u['years_experience']) ? (int)$u['years_experience'] . ' years' : '—' ?></strong></div>
                        <div class="info-row"><span>Public profile</span><strong><?= !empty($u['is_public']) ? '✅ Yes' : '🔒 Private' ?></strong></div>
                    </div>
                </section>
            </div>

        </main>
    </div>
</div>

<script>
function messageUser(id) {
    fetch('<?= BASE_URL ?>/chat/start', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'user_id=' + id
    }).then(r => r.json()).then(d => {
        if (d.success) window.location.href = '<?= BASE_URL ?>/chat?c=' + d.conversation_id;
        else alert(d.error || 'Could not start chat');
    });
}
function followUser(id, btn) {
    btn.textContent = '✓ Following';
    btn.classList.add('following');
}
</script>

<style>
html,body{margin:0!important;padding:0!important;overflow-x:hidden!important}
.profile-page.dashboard-layout{display:block!important;width:100%!important;background:#0b1120!important;min-height:100vh!important}
.profile-page .main-wrap{margin-left:260px!important;width:calc(100% - 260px)!important;min-height:100vh!important;box-sizing:border-box!important}
.profile-page .main-content{padding:20px 28px 60px!important;display:flex!important;flex-direction:column!important;gap:18px!important;box-sizing:border-box!important}
.profile-page .glass-card{background:rgba(30,41,59,.7)!important;backdrop-filter:blur(16px)!important;border:1px solid rgba(255,255,255,.08)!important;border-radius:20px!important;padding:22px!important;box-sizing:border-box!important;width:100%!important}

.flash{padding:14px 18px;border-radius:12px;font-size:13.5px;font-weight:700}
.flash-success{background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.35)}
.flash-error{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.35)}

.profile-hero{padding:0!important;overflow:hidden!important;position:relative!important}
.cover-wrap{height:160px;background:linear-gradient(135deg,#10b981,#7c3aed);background-size:cover;background-position:center;position:relative}
.cover-placeholder{position:absolute;inset:0;background:linear-gradient(135deg,rgba(16,185,129,.9),rgba(124,58,237,.85))}
.cover-placeholder::after{content:'🌾';position:absolute;right:30px;bottom:20px;font-size:80px;opacity:.15}
.hero-body{padding:0 28px 28px;display:flex;gap:22px;align-items:flex-start;position:relative}

.avatar-wrap{margin-top:-60px;position:relative;z-index:2;flex-shrink:0}
.profile-avatar-img,.profile-avatar-initials{width:130px;height:130px;border-radius:50%;border:5px solid #1e293b;box-shadow:0 8px 24px rgba(0,0,0,.4);display:block;object-fit:cover}
.profile-avatar-initials{background:linear-gradient(135deg,#10b981,#38bdf8);color:#fff;font-size:42px;font-weight:800;display:flex;align-items:center;justify-content:center;font-family:inherit}

.hero-info{flex:1;min-width:0;padding-top:12px}
.hero-name-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:6px}
.hero-name-row h1{font-size:26px;font-weight:800;color:#f1f5f9;margin:0}
.role-chip{padding:4px 12px;background:rgba(167,139,250,.15);color:#a78bfa;border:1px solid rgba(167,139,250,.3);border-radius:999px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px}
.hero-bio{color:#cbd5e1;font-size:13.5px;margin:0 0 14px;line-height:1.55;max-width:600px}
.hero-meta{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:16px;font-size:12.5px;color:#94a3b8}
.hero-meta span{display:inline-flex;align-items:center;gap:4px}

.hero-actions{display:flex;gap:10px;flex-wrap:wrap}
.btn-hero-primary,.btn-hero-secondary{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:12px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:.2s;text-decoration:none;border:none}
.btn-hero-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 4px 15px rgba(16,185,129,.35)}
.btn-hero-primary:hover{transform:translateY(-2px);box-shadow:0 6px 22px rgba(16,185,129,.55)}
.btn-hero-secondary{background:rgba(255,255,255,.06);color:#f1f5f9;border:1px solid rgba(255,255,255,.14)}
.btn-hero-secondary:hover{background:rgba(255,255,255,.12);transform:translateY(-2px)}
.btn-hero-secondary.following{background:rgba(16,185,129,.15);color:#34d399;border-color:rgba(16,185,129,.35)}

.stats-strip{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(160px,1fr))!important;gap:12px!important}
.stat-card{display:flex;align-items:center;gap:12px;padding:16px 18px;background:rgba(30,41,59,.7);border:1px solid rgba(255,255,255,.08);border-radius:14px;transition:.2s}
.stat-card:hover{transform:translateY(-2px);border-color:rgba(16,185,129,.3)}
.stat-icon{font-size:26px}
.stat-card strong{display:block;font-size:20px;font-weight:800;color:#f1f5f9;line-height:1}
.stat-card small{display:block;font-size:10.5px;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;font-weight:700;margin-top:3px}

.profile-layout{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.panel-title{font-size:14px;font-weight:800;color:#f1f5f9;margin:0 0 16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06)}
.info-list{display:flex;flex-direction:column;gap:2px}
.info-row{display:flex;justify-content:space-between;gap:12px;padding:10px 12px;border-radius:9px;transition:.15s}
.info-row:hover{background:rgba(255,255,255,.03)}
.info-row span{font-size:12.5px;color:#94a3b8;font-weight:600}
.info-row strong{font-size:12.5px;color:#f1f5f9;font-weight:700;text-align:right;word-break:break-word}

@media(max-width:900px){
    .profile-page .main-wrap{margin-left:0!important;width:100%!important;padding-top:64px!important}
    .profile-page .main-content{padding:14px!important}
    .hero-body{flex-direction:column;padding:0 18px 20px;align-items:center;text-align:center}
    .avatar-wrap{margin-top:-60px}
    .profile-avatar-img,.profile-avatar-initials{width:110px;height:110px}
    .hero-name-row{justify-content:center}
    .hero-meta{justify-content:center}
    .hero-actions{justify-content:center;width:100%}
    .hero-actions a,.hero-actions button{flex:1;justify-content:center}
    .profile-layout{grid-template-columns:1fr}
}
</style>