<?php
$crops   = $crops   ?? [];
$totals  = $totals  ?? ['all'=>0,'active'=>0,'inactive'=>0];
$filters = $filters ?? ['q'=>'','status'=>'all','sort'=>'name'];
$flashes = $flashes ?? [];
?>
<div class="dashboard-layout admin-crops-page">
    <?php require_once __DIR__ . '/../../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../../layouts/topbar.php'; ?>
        <main class="main-content">

            <?php foreach ($flashes as $type => $msg): ?>
                <div class="flash flash-<?= e($type) ?>"><?= e($msg) ?></div>
            <?php endforeach; ?>

            <!-- HERO -->
            <section class="glass-card ad-hero">
                <div>
                    <span class="hero-tag">🌾 CROP CATALOG</span>
                    <h1>Manage Crops</h1>
                    <p>Add, edit, deactivate, or remove crops from the SAIP catalog.</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/crops/form" class="btn-hero-primary">
                    ➕ Add Crop
                </a>
            </section>

            <!-- KPI STRIP -->
            <section class="kpi-strip">
                <div class="kpi-card kpi-total">
                    <span class="kpi-icon">🌾</span>
                    <div><span class="kpi-label">Total Crops</span>
                    <strong class="kpi-value"><?= number_format($totals['all']) ?></strong></div>
                </div>
                <div class="kpi-card kpi-active">
                    <span class="kpi-icon">✅</span>
                    <div><span class="kpi-label">Active</span>
                    <strong class="kpi-value"><?= number_format($totals['active']) ?></strong></div>
                </div>
                <div class="kpi-card kpi-inactive">
                    <span class="kpi-icon">⏸️</span>
                    <div><span class="kpi-label">Inactive</span>
                    <strong class="kpi-value"><?= number_format($totals['inactive']) ?></strong></div>
                </div>
                <div class="kpi-card kpi-uploads">
                    <span class="kpi-icon">📁</span>
                    <div><span class="kpi-label">Uploads Manager</span>
                    <a href="<?= BASE_URL ?>/admin/uploads" class="kpi-link">Manage →</a></div>
                </div>
            </section>

            <!-- FILTERS -->
            <section class="glass-card ad-filters">
                <form method="GET" action="<?= BASE_URL ?>/admin/crops" class="filter-row">
                    <div class="filter-search">
                        <span>🔍</span>
                        <input type="text" name="q" placeholder="Search crops…"
                               value="<?= e($filters['q']) ?>">
                    </div>
                    <select name="status" class="filter-select">
                        <option value="all"      <?= $filters['status']==='all'?'selected':'' ?>>All statuses</option>
                        <option value="active"   <?= $filters['status']==='active'?'selected':'' ?>>Active</option>
                        <option value="inactive" <?= $filters['status']==='inactive'?'selected':'' ?>>Inactive</option>
                    </select>
                    <select name="sort" class="filter-select">
                        <option value="name"   <?= $filters['sort']==='name'?'selected':'' ?>>Sort: Name</option>
                        <option value="newest" <?= $filters['sort']==='newest'?'selected':'' ?>>Sort: Newest</option>
                        <option value="oldest" <?= $filters['sort']==='oldest'?'selected':'' ?>>Sort: Oldest</option>
                        <option value="usage"  <?= $filters['sort']==='usage'?'selected':'' ?>>Sort: Most used</option>
                    </select>
                    <button class="btn-secondary" type="submit">Apply</button>
                    <a href="<?= BASE_URL ?>/admin/crops" class="btn-ghost">Reset</a>
                </form>
            </section>

            <!-- BULK ACTION FORM -->
            <form method="POST" action="<?= BASE_URL ?>/admin/crops/bulk" id="bulkForm">
                <section class="glass-card ad-table-wrap">
                    <div class="bulk-bar">
                        <label class="cbx">
                            <input type="checkbox" id="selectAll">
                            <span>Select all</span>
                        </label>
                        <div class="bulk-actions">
                            <select name="action" class="filter-select" required>
                                <option value="">Choose action…</option>
                                <option value="activate">✅ Activate</option>
                                <option value="deactivate">⏸️ Deactivate</option>
                                <option value="delete">🗑️ Delete</option>
                            </select>
                            <button type="submit" class="btn-danger"
                                    onclick="return confirm('Apply to selected crops?')">Apply</button>
                        </div>
                    </div>

                    <?php if (empty($crops)): ?>
                        <div class="empty-state">
                            <div class="empty-emoji">🌱</div>
                            <h3>No crops found</h3>
                            <p>Try adjusting your filters or add a new crop.</p>
                        </div>
                    <?php else: ?>
                        <table class="ad-table">
                            <thead>
                                <tr>
                                    <th style="width:44px"></th>
                                    <th>Crop</th>
                                    <th>Category</th>
                                    <th>Season</th>
                                    <th>Growing</th>
                                    <th>Usage</th>
                                    <th>Status</th>
                                    <th style="width:150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($crops as $c): ?>
                                <tr>
                                    <td>
                                        <label class="cbx">
                                            <input type="checkbox" name="ids[]" value="<?= (int)$c['id'] ?>" class="row-cbx">
                                        </label>
                                    </td>
                                    <td>
                                        <div class="crop-cell">
                                            <div class="crop-thumb">
                                                <?php if (!empty($c['image_url'])): ?>
                                                    <img src="<?= e($c['image_url']) ?>" alt="">
                                                <?php else: ?>
                                                    <span><?= e($c['icon'] ?? '🌱') ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <strong><?= e($c['name']) ?></strong>
                                                <small><?= e($c['scientific_name'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="pill"><?= e(ucfirst($c['category'] ?? '—')) ?></span></td>
                                    <td><?= e($c['season'] ?? '—') ?></td>
                                    <td><?= (int)($c['growing_days'] ?? 0) ?> days</td>
                                    <td>
                                        <span class="usage"><?= (int)($c['usage_count'] ?? 0) ?> farms</span><br>
                                        <small><?= (int)($c['disease_count'] ?? 0) ?> diseases</small>
                                    </td>
                                    <td>
                                        <?php if ((int)$c['is_active'] === 1): ?>
                                            <span class="status-pill st-active">● Active</span>
                                        <?php else: ?>
                                            <span class="status-pill st-inactive">● Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a href="<?= BASE_URL ?>/admin/crops/form/<?= (int)$c['id'] ?>"
                                               class="act-btn" title="Edit">✏️</a>
                                            <a href="<?= BASE_URL ?>/admin/crops/delete/<?= (int)$c['id'] ?>"
                                               class="act-btn act-danger"
                                               onclick="return confirm('Delete <?= e(addslashes($c['name'])) ?>?')"
                                               title="Delete">🗑️</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </section>
            </form>

        </main>
    </div>
</div>

<style>
html,body{margin:0!important;padding:0!important;overflow-x:hidden!important}
.admin-crops-page.dashboard-layout{display:block!important;width:100%!important;background:#0b1120!important;min-height:100vh!important}
.admin-crops-page .main-wrap{margin-left:260px!important;width:calc(100% - 260px)!important;min-height:100vh!important;box-sizing:border-box!important}
.admin-crops-page .main-content{padding:20px 28px 60px!important;display:flex!important;flex-direction:column!important;gap:18px!important;box-sizing:border-box!important}
.admin-crops-page .glass-card{background:rgba(30,41,59,.7)!important;backdrop-filter:blur(16px)!important;border:1px solid rgba(255,255,255,.08)!important;border-radius:20px!important;padding:22px!important;box-sizing:border-box!important;width:100%!important}

.flash{padding:14px 18px;border-radius:12px;font-size:13.5px;font-weight:700;animation:flashIn .25s}
.flash-success{background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.35)}
.flash-error{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.35)}
@keyframes flashIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}

.ad-hero{display:flex!important;justify-content:space-between!important;align-items:center!important;gap:20px!important;flex-wrap:wrap!important;padding:24px 28px!important;background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(56,189,248,.08))!important}
.hero-tag{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.35);border-radius:999px;font-size:10.5px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;margin-bottom:10px}
.ad-hero h1{font-size:26px;font-weight:800;color:#f1f5f9;margin:0 0 6px}
.ad-hero p{color:#94a3b8;font-size:13.5px;margin:0;max-width:600px}
.btn-hero-primary{display:inline-flex;align-items:center;gap:8px;padding:13px 24px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:12px;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;box-shadow:0 4px 15px rgba(16,185,129,.35);transition:.2s;font-family:inherit}
.btn-hero-primary:hover{transform:translateY(-2px);box-shadow:0 6px 22px rgba(16,185,129,.55)}

.kpi-strip{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))!important;gap:14px!important}
.kpi-card{display:flex;align-items:center;gap:14px;padding:18px 20px;background:rgba(30,41,59,.7);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.08);border-radius:16px;position:relative;overflow:hidden;transition:.25s}
.kpi-card::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%}
.kpi-total::before{background:linear-gradient(180deg,#10b981,#059669)}
.kpi-active::before{background:linear-gradient(180deg,#38bdf8,#0284c7)}
.kpi-inactive::before{background:linear-gradient(180deg,#fbbf24,#d97706)}
.kpi-uploads::before{background:linear-gradient(180deg,#a78bfa,#7c3aed)}
.kpi-card:hover{transform:translateY(-3px);border-color:rgba(16,185,129,.3)}
.kpi-icon{font-size:26px;flex-shrink:0}
.kpi-label{display:block;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px}
.kpi-value{display:block;font-size:22px;font-weight:800;color:#f1f5f9;line-height:1.1}
.kpi-link{color:#34d399;font-size:13px;font-weight:700;text-decoration:none}
.kpi-link:hover{text-decoration:underline}

.ad-filters{padding:16px 20px!important}
.filter-row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.filter-search{flex:1;min-width:220px;position:relative}
.filter-search span{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#64748b}
.filter-search input{width:100%;padding:11px 16px 11px 40px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:12px;color:#f1f5f9;font-size:13.5px;font-family:inherit;outline:none;box-sizing:border-box}
.filter-search input:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.15)}
.filter-select{padding:11px 34px 11px 14px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:12px;color:#cbd5e1;font-size:12.5px;font-weight:600;font-family:inherit;cursor:pointer;outline:none;-webkit-appearance:none;appearance:none;background-image:url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e");background-repeat:no-repeat;background-position:right 10px center;background-size:14px}
.filter-select option{background:#1e293b}
.btn-secondary,.btn-ghost,.btn-danger{padding:11px 20px;border-radius:12px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;border:none;transition:.15s;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:rgba(56,189,248,.12);color:#38bdf8;border:1px solid rgba(56,189,248,.35)}
.btn-secondary:hover{background:rgba(56,189,248,.25)}
.btn-ghost{background:transparent;color:#94a3b8;border:1px solid rgba(255,255,255,.1)}
.btn-ghost:hover{background:rgba(255,255,255,.05);color:#cbd5e1}
.btn-danger{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.35)}
.btn-danger:hover{background:rgba(239,68,68,.28)}

.ad-table-wrap{padding:0!important;overflow:hidden}
.bulk-bar{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 20px;background:rgba(0,0,0,.15);border-bottom:1px solid rgba(255,255,255,.06);flex-wrap:wrap}
.bulk-actions{display:flex;gap:8px;align-items:center}
.cbx{display:inline-flex;align-items:center;gap:8px;cursor:pointer;color:#94a3b8;font-size:12.5px;font-weight:600}
.cbx input{accent-color:#10b981;width:16px;height:16px;cursor:pointer}

.ad-table{width:100%;border-collapse:collapse;font-size:13.5px}
.ad-table thead th{background:rgba(0,0,0,.25);color:#94a3b8;text-align:left;padding:14px 16px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;border-bottom:1px solid rgba(255,255,255,.08)}
.ad-table tbody td{padding:14px 16px;color:#cbd5e1;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle}
.ad-table tbody tr:hover{background:rgba(16,185,129,.04)}
.ad-table tbody tr:last-child td{border-bottom:none}

.crop-cell{display:flex;align-items:center;gap:12px}
.crop-thumb{width:44px;height:44px;border-radius:10px;background:rgba(16,185,129,.12);display:flex;align-items:center;justify-content:center;font-size:22px;overflow:hidden;flex-shrink:0;border:1px solid rgba(16,185,129,.2)}
.crop-thumb img{width:100%;height:100%;object-fit:cover}
.crop-cell strong{display:block;font-size:13.5px;color:#f1f5f9;font-weight:700;margin-bottom:2px}
.crop-cell small{display:block;font-size:11px;color:#64748b;font-style:italic}

.pill{padding:4px 10px;background:rgba(167,139,250,.15);color:#a78bfa;border-radius:999px;font-size:11px;font-weight:700;border:1px solid rgba(167,139,250,.3)}
.usage{color:#34d399;font-weight:700}

.status-pill{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:999px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}
.st-active{background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.35)}
.st-inactive{background:rgba(148,163,184,.15);color:#94a3b8;border:1px solid rgba(148,163,184,.35)}

.row-actions{display:flex;gap:6px}
.act-btn{width:34px;height:34px;display:flex;align-items:center;justify-content:center;background:rgba(56,189,248,.1);color:#38bdf8;border:1px solid rgba(56,189,248,.3);border-radius:9px;text-decoration:none;transition:.15s;font-size:14px}
.act-btn:hover{background:rgba(56,189,248,.25);transform:translateY(-1px)}
.act-danger{background:rgba(239,68,68,.1);color:#f87171;border-color:rgba(239,68,68,.3)}
.act-danger:hover{background:rgba(239,68,68,.25)}

.empty-state{text-align:center;padding:60px 30px}
.empty-emoji{font-size:60px;margin-bottom:12px;opacity:.5}
.empty-state h3{font-size:17px;color:#f1f5f9;margin:0 0 6px;font-weight:700}
.empty-state p{color:#94a3b8;font-size:13px;margin:0}

@media(max-width:900px){
    .admin-crops-page .main-wrap{margin-left:0!important;width:100%!important;padding-top:64px!important}
    .admin-crops-page .main-content{padding:14px!important}
    .ad-table{font-size:12.5px}
    .ad-table thead th,.ad-table tbody td{padding:10px 12px}
    .kpi-strip{grid-template-columns:repeat(2,1fr)!important}
}
</style>

<script>
(function(){
    var selAll = document.getElementById('selectAll');
    if (selAll) {
        selAll.addEventListener('change', function(){
            document.querySelectorAll('.row-cbx').forEach(function(cb){
                cb.checked = selAll.checked;
            });
        });
    }
    var bulkForm = document.getElementById('bulkForm');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e){
            var checked = document.querySelectorAll('.row-cbx:checked');
            if (!checked.length) {
                e.preventDefault();
                alert('Select at least one crop.');
            }
        });
    }
})();
</script>