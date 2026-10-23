<?php
$crop    = $crop    ?? null;
$flashes = $flashes ?? [];
$isEdit  = $crop !== null;
?>
<div class="dashboard-layout admin-crops-page">
    <?php require_once __DIR__ . '/../../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../../layouts/topbar.php'; ?>
        <main class="main-content">

            <?php foreach ($flashes as $type => $msg): ?>
                <div class="flash flash-<?= e($type) ?>"><?= e($msg) ?></div>
            <?php endforeach; ?>

            <section class="glass-card ad-hero">
                <div>
                    <a href="<?= BASE_URL ?>/admin/crops" class="back-link">← Back to crops</a>
                    <h1><?= $isEdit ? '✏️ Edit Crop' : '➕ Add New Crop' ?></h1>
                    <p><?= $isEdit ? 'Update crop details, season, and growing requirements.' : 'Fill in the crop details to add it to the catalog.' ?></p>
                </div>
            </section>

            <form method="POST" action="<?= BASE_URL ?>/admin/crops/save"
                  enctype="multipart/form-data" class="crop-form">
                <input type="hidden" name="id" value="<?= $isEdit ? (int)$crop['id'] : 0 ?>">
                <input type="hidden" name="existing_image" value="<?= e($crop['image_url'] ?? '') ?>">

                <div class="form-layout">
                    <div class="form-main">

                        <!-- BASIC -->
                        <section class="glass-card">
                            <div class="sec-head">
                                <span class="sec-num">1</span>
                                <div><h3>Basic Info</h3><p>Name, category, and identity</p></div>
                            </div>

                            <div class="grid-2">
                                <div class="fg">
                                    <label>Name *</label>
                                    <input type="text" name="name" required maxlength="100"
                                           value="<?= e($crop['name'] ?? '') ?>"
                                           placeholder="e.g., Maize">
                                </div>
                                <div class="fg">
                                    <label>Scientific Name</label>
                                    <input type="text" name="scientific_name" maxlength="150"
                                           value="<?= e($crop['scientific_name'] ?? '') ?>"
                                           placeholder="e.g., Zea mays">
                                </div>
                            </div>

                            <div class="grid-2">
                                <div class="fg">
                                    <label>Category</label>
                                    <select name="category">
                                        <?php foreach (['cereal','legume','vegetable','fruit','tuber','cash','spice','other'] as $cat): ?>
                                            <option value="<?= $cat ?>"
                                                <?= ($crop['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                                <?= ucfirst($cat) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="fg">
                                    <label>Icon (emoji)</label>
                                    <input type="text" name="icon" maxlength="8"
                                           value="<?= e($crop['icon'] ?? '🌱') ?>"
                                           placeholder="🌱">
                                </div>
                            </div>

                            <div class="fg">
                                <label>Description</label>
                                <textarea name="description" rows="3" maxlength="1000"
                                          placeholder="Short description…"><?= e($crop['description'] ?? '') ?></textarea>
                            </div>

                            <div class="fg">
                                <label>Image (JPG/PNG/WebP, max 2 MB)</label>
                                <input type="file" name="image" accept="image/*">
                                <?php if (!empty($crop['image_url'])): ?>
                                    <small class="fg-hint">
                                        Current: <img src="<?= e($crop['image_url']) ?>" style="height:26px;vertical-align:middle;margin-left:6px;border-radius:4px">
                                    </small>
                                <?php endif; ?>
                            </div>
                        </section>

                        <!-- GROWING -->
                        <section class="glass-card">
                            <div class="sec-head">
                                <span class="sec-num">2</span>
                                <div><h3>Growing Conditions</h3><p>Season, duration, and environment</p></div>
                            </div>

                            <div class="grid-2">
                                <div class="fg">
                                    <label>Season(s)</label>
                                    <input type="text" name="season" maxlength="20"
                                           value="<?= e($crop['season'] ?? 'A,B') ?>"
                                           placeholder="A,B">
                                    <small class="fg-hint">Comma-separated (A, B, C)</small>
                                </div>
                                <div class="fg">
                                    <label>Growing Days</label>
                                    <input type="number" name="growing_days" min="1" max="1000"
                                           value="<?= (int)($crop['growing_days'] ?? 90) ?>">
                                </div>
                            </div>

                            <div class="grid-4">
                                <div class="fg">
                                    <label>pH Min</label>
                                    <input type="number" step="0.1" name="optimal_ph_min" min="0" max="14"
                                           value="<?= (float)($crop['optimal_ph_min'] ?? 5.5) ?>">
                                </div>
                                <div class="fg">
                                    <label>pH Max</label>
                                    <input type="number" step="0.1" name="optimal_ph_max" min="0" max="14"
                                           value="<?= (float)($crop['optimal_ph_max'] ?? 7.0) ?>">
                                </div>
                                <div class="fg">
                                    <label>Temp Min (°C)</label>
                                    <input type="number" step="0.1" name="optimal_temp_min"
                                           value="<?= (float)($crop['optimal_temp_min'] ?? 15) ?>">
                                </div>
                                <div class="fg">
                                    <label>Temp Max (°C)</label>
                                    <input type="number" step="0.1" name="optimal_temp_max"
                                           value="<?= (float)($crop['optimal_temp_max'] ?? 30) ?>">
                                </div>
                            </div>
                        </section>

                        <!-- STATUS -->
                        <section class="glass-card">
                            <div class="sec-head">
                                <span class="sec-num">3</span>
                                <div><h3>Status</h3><p>Visibility in the catalog</p></div>
                            </div>

                            <label class="toggle-row">
                                <div>
                                    <strong>Active</strong>
                                    <small>Show this crop to farmers</small>
                                </div>
                                <input type="checkbox" name="is_active" value="1" class="toggle-input"
                                       <?= (!$isEdit || !empty($crop['is_active'])) ? 'checked' : '' ?>>
                                <span class="toggle-switch"></span>
                            </label>
                        </section>

                        <div class="form-actions">
                            <a href="<?= BASE_URL ?>/admin/crops" class="btn-ghost">Cancel</a>
                            <button type="submit" class="btn-hero-primary">
                                💾 <?= $isEdit ? 'Update Crop' : 'Create Crop' ?>
                            </button>
                        </div>
                    </div>
                </div>
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

.flash{padding:14px 18px;border-radius:12px;font-size:13.5px;font-weight:700}
.flash-success{background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.35)}
.flash-error{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.35)}

.ad-hero{padding:24px 28px!important;background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(56,189,248,.08))!important}
.back-link{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:#94a3b8;text-decoration:none;font-weight:600;margin-bottom:12px}
.back-link:hover{color:#34d399}
.ad-hero h1{font-size:24px;font-weight:800;color:#f1f5f9;margin:0 0 6px}
.ad-hero p{color:#94a3b8;font-size:13.5px;margin:0;max-width:600px}

.crop-form{width:100%}
.form-layout{width:100%}
.form-main{display:flex;flex-direction:column;gap:18px}

.sec-head{display:flex;align-items:center;gap:14px;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,.06)}
.sec-num{width:34px;height:34px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;flex-shrink:0;box-shadow:0 4px 12px rgba(16,185,129,.35)}
.sec-head h3{font-size:15px;color:#f1f5f9;margin:0 0 2px;font-weight:800}
.sec-head p{font-size:12px;color:#94a3b8;margin:0}

.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px}
.fg{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.fg:last-child{margin-bottom:0}
.fg label{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px}
.fg input[type="text"],.fg input[type="number"],.fg select,.fg textarea{padding:12px 15px;background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.12);border-radius:11px;color:#f1f5f9;font-size:14px;font-family:inherit;outline:none;box-sizing:border-box;width:100%;transition:.2s}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.15)}
.fg select option{background:#1e293b}
.fg textarea{resize:vertical;min-height:70px}
.fg-hint{font-size:11px;color:#64748b;font-style:italic}

.toggle-row{display:flex;align-items:center;gap:14px;padding:14px 16px;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.06);border-radius:12px;cursor:pointer}
.toggle-row div{flex:1}
.toggle-row strong{display:block;font-size:13.5px;color:#f1f5f9;font-weight:700;margin-bottom:2px}
.toggle-row small{display:block;font-size:11.5px;color:#94a3b8}
.toggle-input{display:none}
.toggle-switch{width:46px;height:26px;background:rgba(255,255,255,.1);border-radius:999px;position:relative;transition:.25s;flex-shrink:0}
.toggle-switch::after{content:'';position:absolute;top:3px;left:3px;width:20px;height:20px;background:#fff;border-radius:50%;transition:.25s cubic-bezier(.4,0,.2,1);box-shadow:0 2px 6px rgba(0,0,0,.3)}
.toggle-input:checked ~ .toggle-switch{background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 0 16px rgba(16,185,129,.4)}
.toggle-input:checked ~ .toggle-switch::after{left:23px}

.form-actions{display:flex;justify-content:flex-end;gap:10px;padding:18px 22px;background:rgba(30,41,59,.7);border:1px solid rgba(255,255,255,.08);border-radius:20px;flex-wrap:wrap}
.btn-hero-primary{display:inline-flex;align-items:center;gap:8px;padding:13px 26px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:12px;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;box-shadow:0 4px 15px rgba(16,185,129,.35);transition:.2s;font-family:inherit}
.btn-hero-primary:hover{transform:translateY(-2px);box-shadow:0 6px 22px rgba(16,185,129,.55)}
.btn-ghost{padding:13px 24px;background:transparent;color:#94a3b8;border:1px solid rgba(255,255,255,.12);border-radius:12px;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}
.btn-ghost:hover{background:rgba(255,255,255,.05);color:#cbd5e1}

@media(max-width:900px){
    .admin-crops-page .main-wrap{margin-left:0!important;width:100%!important;padding-top:64px!important}
    .admin-crops-page .main-content{padding:14px!important}
    .grid-2,.grid-4{grid-template-columns:1fr!important}
}
</style>