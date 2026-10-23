<?php
$diseases = $diseases ?? [];
$crops    = $crops    ?? [];
$totals   = $totals   ?? ['all'=>0,'active'=>0,'critical'=>0,'with_images'=>0];
$filters  = $filters  ?? ['q'=>'','crop'=>0,'severity'=>'all'];

function sevClass(string $s): string {
    return match($s) {
        'critical' => 'sev-critical',
        'high'     => 'sev-high',
        'medium'   => 'sev-medium',
        'low'      => 'sev-low',
        default    => 'sev-medium',
    };
}
?>
<div class="dashboard-layout admin-disease-page">
    <?php require_once __DIR__ . '/../../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../../layouts/topbar.php'; ?>
        <main class="main-content">

            <!-- HERO -->
            <section class="glass-card ad-hero">
                <div>
                    <span class="hero-tag">🔬 AI TRAINING DATA</span>
                    <h1>Disease Library</h1>
                    <p>Curate the disease records that power AI detection in the farmer app. Add photos, symptoms, treatments, and prevention guides.</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/diseases/form" class="btn-hero-primary">➕ Add Disease</a>
            </section>

            <!-- KPI STRIP -->
            <section class="kpi-strip">
                <div class="kpi-card kpi-total">
                    <span class="kpi-icon">🧬</span>
                    <div><span class="kpi-label">Total Diseases</span>
                    <strong class="kpi-value"><?= number_format($totals['all']) ?></strong></div>
                </div>
                <div class="kpi-card kpi-active">
                    <span class="kpi-icon">✅</span>
                    <div><span class="kpi-label">Active</span>
                    <strong class="kpi-value"><?= number_format($totals['active']) ?></strong></div>
                </div>
                <div class="kpi-card kpi-critical">
                    <span class="kpi-icon">🚨</span>
                    <div><span class="kpi-label">Critical Severity</span>
                    <strong class="kpi-value"><?= number_format($totals['critical']) ?></strong></div>
                </div>
                <div class="kpi-card kpi-images">
                    <span class="kpi-icon">📸</span>
                    <div><span class="kpi-label">With Hero Image</span>
                    <strong class="kpi-value"><?= number_format($totals['with_images']) ?></strong></div>
                </div>
            </section>

            <!-- FILTERS -->
            <section class="glass-card ad-filters">
                <form method="GET" action="<?= BASE_URL ?>/admin/diseases" class="filter-row">
                    <div class="filter-search">
                        <span>🔍</span>
                        <input type="text" name="q" placeholder="Search by name, pathogen, tag…"
                               value="<?= e($filters['q']) ?>">
                    </div>
                    <select name="crop" class="filter-select">
                        <option value="0">All crops</option>
                        <?php foreach ($crops as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= $filters['crop'] == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['icon'] . ' ' . $c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="severity" class="filter-select">
                        <?php foreach (['all'=>'All severities','critical'=>'Critical','high'=>'High','medium'=>'Medium','low'=>'Low'] as $k=>$v): ?>
                            <option value="<?= $k ?>" <?= $filters['severity']===$k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn-secondary" type="submit">Apply</button>
                    <a href="<?= BASE_URL ?>/admin/diseases" class="btn-ghost">Reset</a>
                </form>
            </section>

            <!-- LIST -->
            <?php if (empty($diseases)): ?>
                <section class="glass-card">
                    <div class="empty-state">
                        <div class="empty-emoji">🧬</div>
                        <h3>No diseases yet</h3>
                        <p>Add your first disease record to start training the AI.</p>
                        <a href="<?= BASE_URL ?>/admin/diseases/form" class="btn-hero-primary">➕ Add Disease</a>
                    </div>
                </section>
            <?php else: ?>
                <section class="disease-grid">
                    <?php foreach ($diseases as $d):
                        $galleryCount = !empty($d['gallery']) ? count(json_decode($d['gallery'], true) ?: []) : 0;
                    ?>
                        <article class="glass-card disease-card">
                            <div class="dc-image">
                                <?php if (!empty($d['image_url'])): ?>
                                    <img src="<?= e($d['image_url']) ?>" alt="" loading="lazy">
                                <?php else: ?>
                                    <div class="dc-placeholder">🧬</div>
                                <?php endif; ?>
                                <span class="dc-sev <?= sevClass($d['severity'] ?? 'medium') ?>">
                                    <?= strtoupper($d['severity'] ?? 'medium') ?>
                                </span>
                                <?php if ($galleryCount > 0): ?>
                                    <span class="dc-gallery-badge">📸 <?= $galleryCount ?></span>
                                <?php endif; ?>
                                <?php if (empty($d['is_active'])): ?>
                                    <span class="dc-inactive">INACTIVE</span>
                                <?php endif; ?>
                            </div>

                            <div class="dc-body">
                                <div class="dc-title-row">
                                    <div>
                                        <strong><?= e($d['name']) ?></strong>
                                        <?php if (!empty($d['crop_name'])): ?>
                                            <small><?= e($d['crop_icon'] ?? '🌾') ?> <?= e($d['crop_name']) ?></small>
                                        <?php else: ?>
                                            <small>All crops</small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!empty($d['pathogen_type'])): ?>
                                    <span class="dc-pathogen"><?= e($d['pathogen_type']) ?></span>
                                <?php endif; ?>

                                <?php if (!empty($d['symptoms'])): ?>
                                    <p class="dc-symptoms"><?= e(mb_substr($d['symptoms'], 0, 110)) ?>…</p>
                                <?php endif; ?>

                                <?php if (!empty($d['tags'])): ?>
                                    <div class="dc-tags">
                                        <?php foreach (array_slice(explode(',', $d['tags']), 0, 3) as $t): ?>
                                            <span class="dc-tag">#<?= e(trim($t)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="dc-actions">
                                <a href="<?= BASE_URL ?>/admin/diseases/form/<?= (int)$d['id'] ?>" class="dc-btn edit">✏️ Edit</a>
                                <a href="<?= BASE_URL ?>/admin/diseases/toggle/<?= (int)$d['id'] ?>" class="dc-btn toggle">
                                    <?= !empty($d['is_active']) ? '⏸️' : '▶️' ?>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/diseases/delete/<?= (int)$d['id'] ?>"
                                   class="dc-btn danger"
                                   onclick="return confirm('Delete <?= e(addslashes($d['name'])) ?>?')">🗑️</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

        </main>
    </div>
</div>

<style>
html,body{margin:0!important;padding:0!important;overflow-x:hidden!important}
.admin-disease-page.dashboard-layout{display:block!important;width:100%!important;background:#0b1120!important;min-height:100vh!important}
.admin-disease-page .main-wrap{margin-left:260px!important;width:calc(100% - 260px)!important;min-height:100vh!important;box-sizing:border-box!important}
.admin-disease-page .main-content{padding:20px 28px 60px!important;display:flex!important;flex-direction:column!important;gap:18px!important;box-sizing:border-box!important}
.admin-disease-page .glass-card{background:rgba(30,41,59,.7)!important;backdrop-filter:blur(16px)!important;border:1px solid rgba(255,255,255,.08)!important;border-radius:20px!important;padding:22px!important;box-sizing:border-box!important;width:100%!important}

.ad-hero{display:flex!important;justify-content:space-between!important;align-items:center!important;gap:20px!important;flex-wrap:wrap!important;padding:24px 28px!important;background:linear-gradient(135deg,rgba(167,139,250,.15),rgba(56,189,248,.08))!important}
.hero-tag{display:inline-flex;padding:4px 12px;background:rgba(167,139,250,.15);color:#a78bfa;border:1px solid rgba(167,139,250,.35);border-radius:999px;font-size:10.5px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;margin-bottom:10px}
.ad-hero h1{font-size:26px;font-weight:800;color:#f1f5f9;margin:0 0 6px}
.ad-hero p{color:#94a3b8;font-size:13.5px;margin:0;max-width:640px;line-height:1.55}
.btn-hero-primary{display:inline-flex;align-items:center;gap:8px;padding:13px 24px;background:linear-gradient(135deg,#a78bfa,#7c3aed);color:#fff;border:none;border-radius:12px;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;box-shadow:0 4px 15px rgba(167,139,250,.35);transition:.2s;font-family:inherit}
.btn-hero-primary:hover{transform:translateY(-2px);box-shadow:0 6px 22px rgba(167,139,250,.55)}

.kpi-strip{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))!important;gap:14px!important}
.kpi-card{display:flex;align-items:center;gap:14px;padding:18px 20px;background:rgba(30,41,59,.7);border:1px solid rgba(255,255,255,.08);border-radius:16px;position:relative;overflow:hidden;transition:.25s}
.kpi-card::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%}
.kpi-total::before{background:linear-gradient(180deg,#a78bfa,#7c3aed)}
.kpi-active::before{background:linear-gradient(180deg,#10b981,#059669)}
.kpi-critical::before{background:linear-gradient(180deg,#ef4444,#dc2626)}
.kpi-images::before{background:linear-gradient(180deg,#38bdf8,#0284c7)}
.kpi-card:hover{transform:translateY(-3px);border-color:rgba(167,139,250,.3)}
.kpi-icon{font-size:26px}
.kpi-label{display:block;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px}
.kpi-value{display:block;font-size:22px;font-weight:800;color:#f1f5f9;line-height:1.1}

.ad-filters{padding:16px 20px!important}
.filter-row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.filter-search{flex:1;min-width:200px;position:relative}
.filter-search span{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#64748b}
.filter-search input{width:100%;padding:11px 16px 11px 40px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:12px;color:#f1f5f9;font-size:13.5px;font-family:inherit;outline:none;box-sizing:border-box}
.filter-search input:focus{border-color:#a78bfa;box-shadow:0 0 0 3px rgba(167,139,250,.15)}
.filter-select{padding:11px 34px 11px 14px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:12px;color:#cbd5e1;font-size:12.5px;font-weight:600;font-family:inherit;cursor:pointer;outline:none;-webkit-appearance:none;appearance:none;background-image:url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e");background-repeat:no-repeat;background-position:right 10px center;background-size:14px}
.filter-select option{background:#1e293b}
.btn-secondary,.btn-ghost{padding:11px 20px;border-radius:12px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;border:none;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:rgba(167,139,250,.12);color:#a78bfa;border:1px solid rgba(167,139,250,.35)}
.btn-secondary:hover{background:rgba(167,139,250,.25)}
.btn-ghost{background:transparent;color:#94a3b8;border:1px solid rgba(255,255,255,.1)}
.btn-ghost:hover{background:rgba(255,255,255,.05)}

.disease-grid{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(300px,1fr))!important;gap:16px!important}
.disease-card{padding:0!important;overflow:hidden!important;transition:.25s!important;display:flex!important;flex-direction:column!important}
.disease-card:hover{transform:translateY(-4px)!important;border-color:rgba(167,139,250,.4)!important;box-shadow:0 16px 40px rgba(0,0,0,.35)!important}

.dc-image{position:relative;height:180px;background:linear-gradient(135deg,rgba(167,139,250,.15),rgba(56,189,248,.08));display:flex;align-items:center;justify-content:center;overflow:hidden}
.dc-image img{width:100%;height:100%;object-fit:cover}
.dc-placeholder{font-size:64px;opacity:.5}
.dc-sev{position:absolute;top:12px;left:12px;padding:4px 10px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;backdrop-filter:blur(8px)}
.sev-critical{background:rgba(239,68,68,.9);color:#fff}
.sev-high{background:rgba(245,158,11,.9);color:#fff}
.sev-medium{background:rgba(251,191,36,.9);color:#1e293b}
.sev-low{background:rgba(56,189,248,.9);color:#fff}
.dc-gallery-badge{position:absolute;bottom:12px;right:12px;padding:4px 10px;background:rgba(0,0,0,.7);color:#fff;border-radius:999px;font-size:10.5px;font-weight:700;backdrop-filter:blur(8px)}
.dc-inactive{position:absolute;top:12px;right:12px;padding:4px 10px;background:rgba(148,163,184,.9);color:#fff;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.5px}

.dc-body{padding:16px 18px;flex:1;display:flex;flex-direction:column;gap:8px}
.dc-title-row strong{display:block;font-size:15px;color:#f1f5f9;font-weight:800;margin-bottom:3px;line-height:1.3}
.dc-title-row small{display:block;font-size:11.5px;color:#94a3b8}
.dc-pathogen{display:inline-flex;align-self:flex-start;padding:3px 10px;background:rgba(167,139,250,.12);color:#a78bfa;border-radius:999px;font-size:10.5px;font-weight:700;border:1px solid rgba(167,139,250,.3)}
.dc-symptoms{font-size:12.5px;color:#cbd5e1;margin:0;line-height:1.5}
.dc-tags{display:flex;gap:5px;flex-wrap:wrap;margin-top:auto}
.dc-tag{padding:2px 8px;background:rgba(56,189,248,.1);color:#38bdf8;border-radius:999px;font-size:10.5px;font-weight:600}

.dc-actions{display:flex;gap:6px;padding:12px 18px;border-top:1px solid rgba(255,255,255,.05);background:rgba(0,0,0,.15)}
.dc-btn{flex:1;padding:8px 10px;border-radius:9px;font-size:12px;font-weight:700;text-align:center;text-decoration:none;transition:.15s;cursor:pointer;border:1px solid transparent;font-family:inherit}
.dc-btn.edit{background:rgba(167,139,250,.12);color:#a78bfa;border-color:rgba(167,139,250,.3)}
.dc-btn.edit:hover{background:rgba(167,139,250,.25)}
.dc-btn.toggle{flex:0 0 40px;background:rgba(251,191,36,.12);color:#fbbf24;border-color:rgba(251,191,36,.3)}
.dc-btn.toggle:hover{background:rgba(251,191,36,.25)}
.dc-btn.danger{flex:0 0 40px;background:rgba(239,68,68,.12);color:#f87171;border-color:rgba(239,68,68,.3)}
.dc-btn.danger:hover{background:rgba(239,68,68,.25)}

.empty-state{text-align:center;padding:60px 30px}
.empty-emoji{font-size:64px;margin-bottom:14px;opacity:.5}
.empty-state h3{font-size:18px;color:#f1f5f9;margin:0 0 6px;font-weight:800}
.empty-state p{color:#94a3b8;font-size:13.5px;margin:0 0 20px;max-width:400px;margin-left:auto;margin-right:auto}

@media(max-width:900px){
    .admin-disease-page .main-wrap{margin-left:0!important;width:100%!important;padding-top:64px!important}
    .admin-disease-page .main-content{padding:14px!important}
    .kpi-strip{grid-template-columns:repeat(2,1fr)!important}
    .disease-grid{grid-template-columns:1fr!important}
}
</style>