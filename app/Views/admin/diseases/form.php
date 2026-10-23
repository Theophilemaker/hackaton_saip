<?php
$disease = $disease ?? null;
$crops   = $crops   ?? [];
$isEdit  = $disease !== null;

$gallery = [];
if ($isEdit && !empty($disease['gallery'])) {
    $gallery = json_decode($disease['gallery'], true) ?: [];
}
?>
<div class="dashboard-layout admin-disease-page">
    <?php require_once __DIR__ . '/../../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../../layouts/topbar.php'; ?>
        <main class="main-content">

            <section class="glass-card ad-hero">
                <div>
                    <a href="<?= BASE_URL ?>/admin/diseases" class="back-link">← Back to library</a>
                    <h1><?= $isEdit ? '✏️ Edit Disease' : '➕ Add New Disease' ?></h1>
                    <p><?= $isEdit ? 'Update disease details and images.' : 'Add a disease record to power AI detection for farmers.' ?></p>
                </div>
            </section>

            <form method="POST" action="<?= BASE_URL ?>/admin/diseases/save"
                  enctype="multipart/form-data" class="disease-form" id="diseaseForm">
                <?= \App\Core\Security::csrfField() ?>
                <input type="hidden" name="id" value="<?= $isEdit ? (int)$disease['id'] : 0 ?>">
                <input type="hidden" name="existing_image" value="<?= e($disease['image_url'] ?? '') ?>">
                <input type="hidden" name="existing_gallery" id="existingGallery"
                       value="<?= e(json_encode($gallery)) ?>">
                <input type="hidden" name="remove_gallery" id="removeGallery" value="[]">

                <div class="form-layout">

                    <!-- LEFT: MAIN FIELDS -->
                    <div class="form-main">

                        <!-- 1. BASIC -->
                        <section class="glass-card">
                            <div class="sec-head">
                                <span class="sec-num">1</span>
                                <div><h3>Basic Information</h3><p>Name and crop association</p></div>
                            </div>

                            <div class="grid-2">
                                <div class="fg">
                                    <label>Disease Name *</label>
                                    <input type="text" name="name" required maxlength="150"
                                           value="<?= e($disease['name'] ?? '') ?>"
                                           placeholder="e.g., Coffee Leaf Rust"
                                           oninput="updatePreview()">
                                </div>
                                <div class="fg">
                                    <label>Affected Crop</label>
                                    <select name="crop_id" onchange="updatePreview()">
                                        <option value="">All crops</option>
                                        <?php foreach ($crops as $c): ?>
                                            <option value="<?= (int)$c['id'] ?>"
                                                <?= ((int)($disease['crop_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                                                <?= e($c['icon'] . ' ' . $c['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="grid-2">
                                <div class="fg">
                                    <label>Pathogen Type</label>
                                    <select name="pathogen_type">
                                        <?php
                                        $types = ['Fungal','Bacterial','Viral','Pest','Nematode','Abiotic','Nutrient Deficiency','Other'];
                                        $selectedType = $disease['pathogen_type'] ?? '';
                                        foreach ($types as $t):
                                        ?>
                                            <option value="<?= $t ?>" <?= $selectedType === $t ? 'selected' : '' ?>>
                                                <?= $t ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="fg">
                                    <label>Severity *</label>
                                    <select name="severity" required onchange="updatePreview()">
                                        <?php foreach (['low'=>'🟢 Low','medium'=>'🟡 Medium','high'=>'🟠 High','critical'=>'🔴 Critical'] as $k=>$v): ?>
                                            <option value="<?= $k ?>" <?= ($disease['severity'] ?? 'medium') === $k ? 'selected' : '' ?>>
                                                <?= $v ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="fg">
                                <label>Tags (comma-separated)</label>
                                <input type="text" name="tags" maxlength="255"
                                       value="<?= e($disease['tags'] ?? '') ?>"
                                       placeholder="e.g., coffee, rust, fungus, harvest-loss">
                            </div>

                            <div class="fg">
                                <label>Short Description</label>
                                <textarea name="description" rows="2" maxlength="500"
                                          placeholder="What is this disease? What causes it?"><?= e($disease['description'] ?? '') ?></textarea>
                            </div>
                        </section>

                        <!-- 2. IMAGES -->
                        <section class="glass-card">
                            <div class="sec-head">
                                <span class="sec-num">2</span>
                                <div><h3>Photos</h3><p>Hero image and reference gallery</p></div>
                            </div>

                            <!-- Hero image -->
                            <div class="fg">
                                <label>Hero Image (max 4 MB)</label>
                                <div class="hero-upload" id="heroUpload">
                                    <?php if ($isEdit && !empty($disease['image_url'])): ?>
                                        <img src="<?= e($disease['image_url']) ?>" id="heroPreview" alt="">
                                    <?php else: ?>
                                        <img id="heroPreview" style="display:none" alt="">
                                        <div class="upload-prompt" id="heroPrompt">
                                            <span>📸</span>
                                            <p>Click or drop a photo</p>
                                            <small>JPG, PNG, WebP, GIF — max 4 MB</small>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="image" id="heroInput" accept="image/*" hidden>
                                </div>
                            </div>

                            <!-- Gallery -->
                            <div class="fg">
                                <label>Reference Gallery (up to 6 images, max 2 MB each)</label>
                                <div class="gallery-grid" id="galleryGrid">
                                    <?php foreach ($gallery as $i => $url): ?>
                                        <div class="gal-item" data-url="<?= e($url) ?>">
                                            <img src="<?= e($url) ?>" alt="">
                                            <button type="button" class="gal-remove" onclick="removeGalleryItem(this)" title="Remove">✕</button>
                                        </div>
                                    <?php endforeach; ?>
                                    <label class="gal-add">
                                        <span>➕</span>
                                        <small>Add photos</small>
                                        <input type="file" name="gallery[]" id="galleryInput" accept="image/*" multiple hidden>
                                    </label>
                                </div>
                            </div>
                        </section>

                        <!-- 3. SYMPTOMS -->
                        <section class="glass-card">
                            <div class="sec-head">
                                <span class="sec-num">3</span>
                                <div><h3>Symptoms</h3><p>What farmers should look for</p></div>
                            </div>
                            <div class="fg">
                                <textarea name="symptoms" rows="5" maxlength="2000"
                                          placeholder="• Yellowing leaves starting from lower nodes&#10;• Orange-brown powdery spots on leaf underside&#10;• Premature leaf drop"><?= e($disease['symptoms'] ?? '') ?></textarea>
                                <small class="fg-hint">Use line breaks and bullet points (•) for readability</small>
                            </div>
                        </section>

                        <!-- 4. TREATMENT -->
                        <section class="glass-card">
                            <div class="sec-head">
                                <span class="sec-num">4</span>
                                <div><h3>Treatment</h3><p>How to cure or manage</p></div>
                            </div>
                            <div class="fg">
                                <textarea name="treatment" rows="5" maxlength="2000"
                                          placeholder="• Apply copper-based fungicide every 14 days&#10;• Prune affected branches and burn them&#10;• Improve air circulation between plants"><?= e($disease['treatment'] ?? '') ?></textarea>
                            </div>
                        </section>

                        <!-- 5. PREVENTION -->
                        <section class="glass-card">
                            <div class="sec-head">
                                <span class="sec-num">5</span>
                                <div><h3>Prevention</h3><p>How to avoid future outbreaks</p></div>
                            </div>
                            <div class="fg">
                                <textarea name="prevention" rows="4" maxlength="2000"
                                          placeholder="• Plant resistant varieties&#10;• Rotate crops every season&#10;• Maintain proper spacing"><?= e($disease['prevention'] ?? '') ?></textarea>
                            </div>

                            <label class="toggle-row">
                                <div>
                                    <strong>Active in AI Detection</strong>
                                    <small>Farmers can detect this disease when active</small>
                                </div>
                                <input type="checkbox" name="is_active" value="1" class="toggle-input"
                                       <?= (!$isEdit || !empty($disease['is_active'])) ? 'checked' : '' ?>>
                                <span class="toggle-switch"></span>
                            </label>
                        </section>

                        <div class="form-actions">
                            <a href="<?= BASE_URL ?>/admin/diseases" class="btn-ghost">Cancel</a>
                            <button type="submit" class="btn-hero-primary">
                                💾 <?= $isEdit ? 'Update Disease' : 'Create Disease' ?>
                            </button>
                        </div>
                    </div>

                    <!-- RIGHT: LIVE PREVIEW -->
                    <aside class="form-side">
                        <div class="glass-card preview-card">
                            <div class="preview-label">
                                <span class="pulse-dot"></span> LIVE PREVIEW
                            </div>

                            <div class="pv-image">
                                <img id="pvImage" src="<?= e($disease['image_url'] ?? '') ?>"
                                     style="<?= $isEdit && !empty($disease['image_url']) ? '' : 'display:none' ?>">
                                <div id="pvPlaceholder" class="pv-placeholder"
                                     style="<?= $isEdit && !empty($disease['image_url']) ? 'display:none' : '' ?>">🧬</div>
                                <span class="pv-sev sev-medium" id="pvSev">MEDIUM</span>
                            </div>

                            <div class="pv-body">
                                <strong id="pvName">Untitled Disease</strong>
                                <small id="pvCrop">All crops</small>
                                <p class="pv-symptoms" id="pvSymptoms">Symptoms will appear here…</p>
                            </div>

                            <div class="pv-note">
                                💡 This is how farmers will see the disease after a successful AI detection.
                            </div>
                        </div>
                    </aside>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
(function () {
    'use strict';

    /* ═══════ HERO IMAGE ═══════ */
    var heroInput = document.getElementById('heroInput');
    var heroUpload = document.getElementById('heroUpload');
    var heroPreview = document.getElementById('heroPreview');
    var heroPrompt = document.getElementById('heroPrompt');

    heroUpload.addEventListener('click', function (e) {
        if (e.target === heroPreview) return;
        heroInput.click();
    });
    heroUpload.addEventListener('dragover', function (e) {
        e.preventDefault();
        heroUpload.classList.add('dragging');
    });
    heroUpload.addEventListener('dragleave', function () {
        heroUpload.classList.remove('dragging');
    });
    heroUpload.addEventListener('drop', function (e) {
        e.preventDefault();
        heroUpload.classList.remove('dragging');
        if (e.dataTransfer.files.length) {
            heroInput.files = e.dataTransfer.files;
            showHero(e.dataTransfer.files[0]);
        }
    });
    heroInput.addEventListener('change', function () {
        if (this.files.length) showHero(this.files[0]);
    });

    function showHero(file) {
        if (!file.type.startsWith('image/')) return;
        var r = new FileReader();
        r.onload = function (e) {
            heroPreview.src = e.target.result;
            heroPreview.style.display = 'block';
            if (heroPrompt) heroPrompt.style.display = 'none';
            document.getElementById('pvImage').src = e.target.result;
            document.getElementById('pvImage').style.display = 'block';
            document.getElementById('pvPlaceholder').style.display = 'none';
        };
        r.readAsDataURL(file);
    }

    /* ═══════ GALLERY ═══════ */
    var galleryInput = document.getElementById('galleryInput');
    galleryInput.addEventListener('change', function () {
        var grid = document.getElementById('galleryGrid');
        var addBtn = grid.querySelector('.gal-add');
        Array.from(this.files).slice(0, 6).forEach(function (f) {
            if (!f.type.startsWith('image/')) return;
            var r = new FileReader();
            r.onload = function (e) {
                var div = document.createElement('div');
                div.className = 'gal-item is-new';
                div.dataset.url = '';
                div.innerHTML = '<img src="' + e.target.result + '">' +
                                '<button type="button" class="gal-remove" onclick="removeGalleryItem(this)">✕</button>';
                grid.insertBefore(div, addBtn);
            };
            r.readAsDataURL(f);
        });
    });

    window.removeGalleryItem = function (btn) {
        var item = btn.closest('.gal-item');
        var url = item.dataset.url;
        if (url) {
            var removed = JSON.parse(document.getElementById('removeGallery').value || '[]');
            removed.push(url);
            document.getElementById('removeGallery').value = JSON.stringify(removed);
        }
        item.remove();
    };

    /* ═══════ LIVE PREVIEW ═══════ */
    window.updatePreview = function () {
        var name = document.querySelector('input[name="name"]').value.trim() || 'Untitled Disease';
        var cropSel = document.querySelector('select[name="crop_id"]');
        var cropText = cropSel.options[cropSel.selectedIndex]?.text || 'All crops';
        var sev = document.querySelector('select[name="severity"]').value;
        var symptoms = document.querySelector('textarea[name="symptoms"]').value.trim();

        document.getElementById('pvName').textContent = name;
        document.getElementById('pvCrop').textContent = cropText;

        var sevEl = document.getElementById('pvSev');
        sevEl.textContent = sev.toUpperCase();
        sevEl.className = 'pv-sev sev-' + sev;

        var symEl = document.getElementById('pvSymptoms');
        symEl.textContent = symptoms
            ? symptoms.substring(0, 120) + (symptoms.length > 120 ? '…' : '')
            : 'Symptoms will appear here…';
    };

    document.querySelector('textarea[name="symptoms"]').addEventListener('input', updatePreview);
    updatePreview();
})();
</script>

<style>
html,body{margin:0!important;padding:0!important;overflow-x:hidden!important}
.admin-disease-page.dashboard-layout{display:block!important;width:100%!important;background:#0b1120!important;min-height:100vh!important}
.admin-disease-page .main-wrap{margin-left:260px!important;width:calc(100% - 260px)!important;min-height:100vh!important;box-sizing:border-box!important}
.admin-disease-page .main-content{padding:20px 28px 60px!important;display:flex!important;flex-direction:column!important;gap:18px!important;box-sizing:border-box!important}
.admin-disease-page .glass-card{background:rgba(30,41,59,.7)!important;backdrop-filter:blur(16px)!important;border:1px solid rgba(255,255,255,.08)!important;border-radius:20px!important;padding:22px!important;box-sizing:border-box!important;width:100%!important}

.ad-hero{padding:24px 28px!important;background:linear-gradient(135deg,rgba(167,139,250,.15),rgba(56,189,248,.08))!important}
.back-link{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:#94a3b8;text-decoration:none;font-weight:600;margin-bottom:12px}
.back-link:hover{color:#a78bfa}
.ad-hero h1{font-size:24px;font-weight:800;color:#f1f5f9;margin:0 0 6px}
.ad-hero p{color:#94a3b8;font-size:13.5px;margin:0;max-width:600px}

.form-layout{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:18px;align-items:start}
.form-main{display:flex;flex-direction:column;gap:18px;min-width:0}
.form-side{position:sticky;top:20px}

.sec-head{display:flex;align-items:center;gap:14px;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,.06)}
.sec-num{width:34px;height:34px;background:linear-gradient(135deg,#a78bfa,#7c3aed);color:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;flex-shrink:0;box-shadow:0 4px 12px rgba(167,139,250,.35)}
.sec-head h3{font-size:15px;color:#f1f5f9;margin:0 0 2px;font-weight:800}
.sec-head p{font-size:12px;color:#94a3b8;margin:0}

.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
.fg{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.fg:last-child{margin-bottom:0}
.fg label{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px}
.fg input[type="text"],.fg select,.fg textarea{padding:12px 15px;background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.12);border-radius:11px;color:#f1f5f9;font-size:14px;font-family:inherit;outline:none;box-sizing:border-box;width:100%;transition:.2s}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:#a78bfa;box-shadow:0 0 0 3px rgba(167,139,250,.15)}
.fg select option{background:#1e293b}
.fg textarea{resize:vertical;min-height:70px;font-family:inherit;line-height:1.6}
.fg-hint{font-size:11px;color:#64748b;font-style:italic}

/* HERO UPLOAD */
.hero-upload{position:relative;height:240px;background:rgba(0,0,0,.25);border:2px dashed rgba(167,139,250,.4);border-radius:14px;display:flex;align-items:center;justify-content:center;overflow:hidden;cursor:pointer;transition:.2s}
.hero-upload:hover,.hero-upload.dragging{border-color:#a78bfa;background:rgba(167,139,250,.08)}
.hero-upload img{width:100%;height:100%;object-fit:cover}
.upload-prompt{text-align:center;color:#94a3b8}
.upload-prompt span{font-size:44px;display:block;margin-bottom:8px}
.upload-prompt p{margin:0 0 4px;font-size:14px;color:#cbd5e1;font-weight:600}
.upload-prompt small{font-size:11px}

/* GALLERY */
.gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px}
.gal-item{position:relative;aspect-ratio:1;background:rgba(0,0,0,.25);border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.08)}
.gal-item img{width:100%;height:100%;object-fit:cover}
.gal-item.is-new{border-color:rgba(167,139,250,.5);box-shadow:0 0 0 2px rgba(167,139,250,.2)}
.gal-remove{position:absolute;top:6px;right:6px;width:26px;height:26px;background:rgba(0,0,0,.75);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;font-family:inherit;padding:0;backdrop-filter:blur(4px)}
.gal-remove:hover{background:rgba(239,68,68,.9)}
.gal-add{aspect-ratio:1;background:rgba(167,139,250,.06);border:2px dashed rgba(167,139,250,.4);border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;cursor:pointer;transition:.2s}
.gal-add:hover{background:rgba(167,139,250,.15);border-color:#a78bfa}
.gal-add span{font-size:24px;color:#a78bfa}
.gal-add small{font-size:10.5px;color:#94a3b8;font-weight:600}

/* TOGGLE */
.toggle-row{display:flex;align-items:center;gap:14px;padding:14px 16px;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.06);border-radius:12px;cursor:pointer;margin-top:12px}
.toggle-row div{flex:1}
.toggle-row strong{display:block;font-size:13.5px;color:#f1f5f9;font-weight:700;margin-bottom:2px}
.toggle-row small{display:block;font-size:11.5px;color:#94a3b8}
.toggle-input{display:none}
.toggle-switch{width:46px;height:26px;background:rgba(255,255,255,.1);border-radius:999px;position:relative;transition:.25s;flex-shrink:0}
.toggle-switch::after{content:'';position:absolute;top:3px;left:3px;width:20px;height:20px;background:#fff;border-radius:50%;transition:.25s;box-shadow:0 2px 6px rgba(0,0,0,.3)}
.toggle-input:checked ~ .toggle-switch{background:linear-gradient(135deg,#a78bfa,#7c3aed);box-shadow:0 0 16px rgba(167,139,250,.4)}
.toggle-input:checked ~ .toggle-switch::after{left:23px}

/* ACTIONS */
.form-actions{display:flex;justify-content:flex-end;gap:10px;padding:18px 22px;background:rgba(30,41,59,.7);border:1px solid rgba(255,255,255,.08);border-radius:20px;flex-wrap:wrap}
.btn-hero-primary{display:inline-flex;align-items:center;gap:8px;padding:13px 26px;background:linear-gradient(135deg,#a78bfa,#7c3aed);color:#fff;border:none;border-radius:12px;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;box-shadow:0 4px 15px rgba(167,139,250,.35);transition:.2s;font-family:inherit}
.btn-hero-primary:hover{transform:translateY(-2px);box-shadow:0 6px 22px rgba(167,139,250,.55)}
.btn-ghost{padding:13px 24px;background:transparent;color:#94a3b8;border:1px solid rgba(255,255,255,.12);border-radius:12px;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}
.btn-ghost:hover{background:rgba(255,255,255,.05);color:#cbd5e1}

/* PREVIEW CARD */
.preview-card{position:relative;overflow:hidden;background:linear-gradient(160deg,rgba(167,139,250,.08),rgba(30,41,59,.7))!important;padding:0!important}
.preview-label{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:rgba(167,139,250,.15);color:#a78bfa;border:1px solid rgba(167,139,250,.3);border-radius:999px;font-size:10px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;margin:20px 20px 0}
.pulse-dot{width:6px;height:6px;background:#a78bfa;border-radius:50%;animation:pulse 1.6s infinite;box-shadow:0 0 8px #a78bfa}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}

.pv-image{position:relative;height:180px;margin-top:16px;background:linear-gradient(135deg,rgba(167,139,250,.15),rgba(56,189,248,.08));display:flex;align-items:center;justify-content:center;overflow:hidden}
.pv-image img{width:100%;height:100%;object-fit:cover}
.pv-placeholder{font-size:56px;opacity:.5}
.pv-sev{position:absolute;top:10px;left:10px;padding:3px 10px;border-radius:999px;font-size:9.5px;font-weight:800;letter-spacing:.6px}
.sev-critical{background:rgba(239,68,68,.95);color:#fff}
.sev-high{background:rgba(245,158,11,.95);color:#fff}
.sev-medium{background:rgba(251,191,36,.95);color:#1e293b}
.sev-low{background:rgba(56,189,248,.95);color:#fff}

.pv-body{padding:16px 20px}
.pv-body strong{display:block;font-size:15px;color:#f1f5f9;font-weight:800;margin-bottom:3px;line-height:1.3}
.pv-body small{display:block;font-size:11.5px;color:#94a3b8;margin-bottom:8px}
.pv-symptoms{font-size:12.5px;color:#cbd5e1;margin:0;line-height:1.5;font-style:italic}

.pv-note{margin:0 20px 20px;padding:12px 14px;background:rgba(251,191,36,.08);border-left:3px solid #fbbf24;border-radius:10px;font-size:11.5px;color:#fbbf24;line-height:1.5}

@media(max-width:1100px){.form-layout{grid-template-columns:1fr}.form-side{position:static}}
@media(max-width:900px){
    .admin-disease-page .main-wrap{margin-left:0!important;width:100%!important;padding-top:64px!important}
    .admin-disease-page .main-content{padding:14px!important}
    .grid-2{grid-template-columns:1fr!important}
    .form-actions{flex-direction:column-reverse}
    .form-actions .btn-hero-primary,.form-actions .btn-ghost{width:100%;justify-content:center}
}
</style>