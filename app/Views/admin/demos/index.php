<?php
$videos = $videos ?? [];
$stats  = $stats  ?? ['total'=>0,'active'=>0,'featured'=>0,'categories'=>[]];
$flashes = [];
foreach (['success','error'] as $t) {
    if (!empty($_SESSION['flash_'.$t])) { $flashes[$t] = $_SESSION['flash_'.$t]; unset($_SESSION['flash_'.$t]); }
}
?>
<div class="dashboard-layout admin-page">
    <?php require_once __DIR__ . '/../../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../../layouts/topbar.php'; ?>
        <main class="main-content">

            <?php foreach ($flashes as $t => $msg): ?>
                <div class="flash flash-<?= $t ?>"><?= e($msg) ?></div>
            <?php endforeach; ?>

            <!-- HERO -->
            <section class="glass-card ad-hero">
                <div>
                    <span class="hero-tag">🎬 CONTENT LIBRARY</span>
                    <h1>Demo Video Manager</h1>
                    <p>Add, edit, and manage demo videos that appear on the landing page.</p>
                </div>
                <a href="<?= BASE_URL ?>/demoAdmin/form" class="btn-hero-primary">➕ Add Video</a>
            </section>

            <!-- KPI STRIP -->
            <section class="kpi-strip">
                <div class="kpi-card kpi-total">
                    <span class="kpi-icon">🎬</span>
                    <div><span class="kpi-label">Total Videos</span>
                    <strong class="kpi-value"><?= number_format($stats['total']) ?></strong></div>
                </div>
                <div class="kpi-card kpi-active">
                    <span class="kpi-icon">✅</span>
                    <div><span class="kpi-label">Active</span>
                    <strong class="kpi-value"><?= number_format($stats['active']) ?></strong></div>
                </div>
                <div class="kpi-card kpi-featured">
                    <span class="kpi-icon">⭐</span>
                    <div><span class="kpi-label">Featured</span>
                    <strong class="kpi-value"><?= number_format($stats['featured']) ?></strong></div>
                </div>
                <div class="kpi-card kpi-cats">
                    <span class="kpi-icon">📂</span>
                    <div><span class="kpi-label">Categories</span>
                    <strong class="kpi-value"><?= count($stats['categories']) ?></strong></div>
                </div>
            </section>

            <!-- LIST -->
            <section class="glass-card">
                <?php if (empty($videos)): ?>
                    <div class="empty-state">
                        <div class="empty-emoji">🎬</div>
                        <h3>No demo videos yet</h3>
                        <p>Add your first video to get started.</p>
                        <a href="<?= BASE_URL ?>/demoAdmin/form" class="btn-hero-primary">➕ Add Video</a>
                    </div>
                <?php else: ?>
                    <div class="video-grid">
                        <?php foreach ($videos as $v): ?>
                            <article class="video-card">
                                <div class="vc-thumb">
                                    <?php if (!empty($v['thumbnail'])): ?>
                                        <img src="<?= e($v['thumbnail']) ?>" alt="" loading="lazy">
                                    <?php else: ?>
                                        <div class="vc-thumb-placeholder">🎬</div>
                                    <?php endif; ?>
                                    <span class="vc-duration"><?= e($v['duration'] ?: '—') ?></span>
                                    <?php if (!empty($v['is_featured'])): ?>
                                        <span class="vc-badge vc-feat">⭐ Featured</span>
                                    <?php endif; ?>
                                    <?php if (empty($v['is_active'])): ?>
                                        <span class="vc-badge vc-inactive">INACTIVE</span>
                                    <?php endif; ?>
                                    <span class="vc-type"><?= strtoupper(e($v['type'])) ?></span>
                                </div>
                                <div class="vc-body">
                                    <span class="vc-cat"><?= e($v['category']) ?></span>
                                    <strong><?= e($v['title']) ?></strong>
                                    <?php if (!empty($v['description'])): ?>
                                        <p><?= e(mb_substr($v['description'], 0, 100)) ?>…</p>
                                    <?php endif; ?>
                                </div>
                                <div class="vc-actions">
                                    <a href="<?= BASE_URL ?>/demoAdmin/form/<?= (int)$v['id'] ?>" class="vc-btn vc-btn-edit" title="Edit">✏️ Edit</a>
                                    <a href="<?= BASE_URL ?>/demoAdmin/feature/<?= (int)$v['id'] ?>" class="vc-btn vc-btn-feat" title="Feature">
                                        <?= !empty($v['is_featured']) ? '⭐' : '☆' ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/demoAdmin/toggle/<?= (int)$v['id'] ?>" class="vc-btn vc-btn-toggle" title="Toggle active">
                                        <?= !empty($v['is_active']) ? '⏸️' : '▶️' ?>
                                    </a>
                                    <a href="<?= BASE_URL ?>/demoAdmin/delete/<?= (int)$v['id'] ?>" class="vc-btn vc-btn-del" title="Delete"
                                       onclick="return confirm('Delete &quot;<?= e(addslashes($v['title'])) ?>&quot;?')">🗑️</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        </main>
    </div>
</div>

<style>
html,body{margin:0!important;padding:0!important;overflow-x:hidden!important}
.admin-page.dashboard-layout{display:block!important;width:100%!important;background:#0b1120!important;min-height:100vh!important}
.admin-page .main-wrap{margin-left:260px!important;width:calc(100% - 260px)!important;min-height:100vh!important;box-sizing:border-box!important}
.admin-page .main-wrap > .main-content{padding:20px 28px 60px!important;display:flex!important;flex-direction:column!important;gap:18px!important;box-sizing:border-box!important}
.admin-page .glass-card{background:rgba(30,41,59,.7)!important;backdrop-filter:blur(16px)!important;border:1px solid rgba(255,255,255,.08)!important;border-radius:20px!important;padding:22px!important;box-sizing:border-box!important;width:100%!important}

.flash{padding:14px 18px;border-radius:12px;font-size:13.5px;font-weight:700}
.flash-success{background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.35)}
.flash-error{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.35)}

.ad-hero{display:flex!important;justify-content:space-between!important;align-items:center!important;gap:20px!important;flex-wrap:wrap!important;padding:24px 28px!important;background:linear-gradient(135deg,rgba(167,139,250,.15),rgba(56,189,248,.08))!important}
.hero-tag{display:inline-block;padding:4px 12px;background:rgba(167,139,250,.15);color:#a78bfa;border:1px solid rgba(167,139,250,.35);border-radius:999px;font-size:10.5px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;margin-bottom:10px}
.ad-hero h1{font-size:26px;font-weight:800;color:#f1f5f9;margin:0 0 6px}
.ad-hero p{color:#94a3b8;font-size:13.5px;margin:0;max-width:600px}
.btn-hero-primary{display:inline-flex;align-items:center;gap:8px;padding:13px 24px;background:linear-gradient(135deg,#a78bfa,#7c3aed);color:#fff;border:none;border-radius:12px;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;box-shadow:0 4px 15px rgba(167,139,250,.35);transition:.2s;font-family:inherit}
.btn-hero-primary:hover{transform:translateY(-2px);box-shadow:0 6px 22px rgba(167,139,250,.55)}

.kpi-strip{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(180px,1fr))!important;gap:14px!important}
.kpi-card{display:flex;align-items:center;gap:14px;padding:18px 20px;background:rgba(30,41,59,.7);border:1px solid rgba(255,255,255,.08);border-radius:16px;position:relative;overflow:hidden;transition:.25s}
.kpi-card::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%}
.kpi-total::before{background:linear-gradient(180deg,#a78bfa,#7c3aed)}
.kpi-active::before{background:linear-gradient(180deg,#10b981,#059669)}
.kpi-featured::before{background:linear-gradient(180deg,#fbbf24,#d97706)}
.kpi-cats::before{background:linear-gradient(180deg,#38bdf8,#0284c7)}
.kpi-card:hover{transform:translateY(-3px);border-color:rgba(167,139,250,.3)}
.kpi-icon{font-size:26px}
.kpi-label{display:block;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px}
.kpi-value{display:block;font-size:22px;font-weight:800;color:#f1f5f9;line-height:1.1}

.video-grid{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(300px,1fr))!important;gap:16px!important}
.video-card{display:flex;flex-direction:column;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;transition:.25s}
.video-card:hover{transform:translateY(-4px);border-color:rgba(167,139,250,.4);box-shadow:0 16px 40px rgba(0,0,0,.4)}

.vc-thumb{position:relative;aspect-ratio:16/9;background:linear-gradient(135deg,rgba(167,139,250,.15),rgba(56,189,248,.08));display:flex;align-items:center;justify-content:center;overflow:hidden}
.vc-thumb img{width:100%;height:100%;object-fit:cover}
.vc-thumb-placeholder{font-size:52px;opacity:.5}
.vc-duration{position:absolute;bottom:10px;right:10px;padding:3px 9px;background:rgba(0,0,0,.75);color:#fff;border-radius:6px;font-size:10.5px;font-weight:800;font-family:'Courier New',monospace;backdrop-filter:blur(4px)}
.vc-type{position:absolute;top:10px;right:10px;padding:3px 9px;background:rgba(56,189,248,.9);color:#fff;border-radius:6px;font-size:9.5px;font-weight:800;letter-spacing:.5px}
.vc-badge{position:absolute;padding:3px 9px;border-radius:999px;font-size:9.5px;font-weight:800;letter-spacing:.5px;text-transform:uppercase}
.vc-feat{top:10px;left:10px;background:rgba(251,191,36,.9);color:#1e293b}
.vc-inactive{bottom:10px;left:10px;background:rgba(148,163,184,.9);color:#fff}

.vc-body{padding:14px 16px;flex:1;display:flex;flex-direction:column;gap:4px}
.vc-cat{display:inline-block;align-self:flex-start;padding:3px 10px;background:rgba(167,139,250,.15);color:#a78bfa;border-radius:999px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.vc-body strong{font-size:14px;color:#f1f5f9;font-weight:800;line-height:1.3}
.vc-body p{font-size:12px;color:#94a3b8;margin:4px 0 0;line-height:1.5}

.vc-actions{display:flex;gap:4px;padding:10px 14px;border-top:1px solid rgba(255,255,255,.05);background:rgba(0,0,0,.15)}
.vc-btn{flex:1;padding:8px 10px;border-radius:9px;font-size:12px;font-weight:700;text-align:center;text-decoration:none;cursor:pointer;font-family:inherit;border:1px solid transparent;transition:.15s;color:#cbd5e1}
.vc-btn:hover{transform:translateY(-1px)}
.vc-btn-edit{background:rgba(167,139,250,.12);color:#a78bfa;border-color:rgba(167,139,250,.3)}
.vc-btn-edit:hover{background:rgba(167,139,250,.25)}
.vc-btn-feat{flex:0 0 40px;background:rgba(251,191,36,.12);color:#fbbf24;border-color:rgba(251,191,36,.3);text-align:center}
.vc-btn-feat:hover{background:rgba(251,191,36,.25)}
.vc-btn-toggle{flex:0 0 40px;background:rgba(56,189,248,.12);color:#38bdf8;border-color:rgba(56,189,248,.3);text-align:center}
.vc-btn-toggle:hover{background:rgba(56,189,248,.25)}
.vc-btn-del{flex:0 0 40px;background:rgba(239,68,68,.12);color:#f87171;border-color:rgba(239,68,68,.3);text-align:center}
.vc-btn-del:hover{background:rgba(239,68,68,.25)}

.empty-state{text-align:center;padding:60px 24px}
.empty-emoji{font-size:60px;margin-bottom:12px;opacity:.5}
.empty-state h3{font-size:17px;color:#f1f5f9;margin:0 0 6px;font-weight:700}
.empty-state p{color:#94a3b8;font-size:13px;margin:0 0 18px}

@media(max-width:900px){
    .admin-page .main-wrap{margin-left:0!important;width:100%!important;padding-top:64px!important}
    .admin-page .main-wrap > .main-content{padding:14px!important}
    .kpi-strip{grid-template-columns:repeat(2,1fr)!important}
    .video-grid{grid-template-columns:1fr!important}
}
</style>