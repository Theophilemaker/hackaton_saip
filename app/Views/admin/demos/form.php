<?php
$video = $video ?? null;
$categories = $categories ?? ['Demo','Features','Tutorials','Impact'];
$isEdit = $video !== null;
?>
<div class="dashboard-layout admin-page">
    <?php require_once __DIR__ . '/../../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../../layouts/topbar.php'; ?>
        <main class="main-content">

            <section class="glass-card ad-hero">
                <div>
                    <a href="<?= BASE_URL ?>/demoAdmin" class="back-link">← Back to videos</a>
                    <h1><?= $isEdit ? '✏️ Edit Video' : '➕ Add Demo Video' ?></h1>
                    <p><?= $isEdit ? 'Update the details, thumbnail, or replace the video file.' : 'Fill in the details to add a new demo video.' ?></p>
                </div>
            </section>

            <form method="POST" action="<?= BASE_URL ?>/demoAdmin/save" enctype="multipart/form-data" class="video-form">
                <?= \App\Core\Security::csrfField() ?>
                <input type="hidden" name="id" value="<?= $isEdit ? (int)$video['id'] : 0 ?>">
                <input type="hidden" name="existing_thumbnail" value="<?= e($video['thumbnail'] ?? '') ?>">

                <!-- ═══ 1. BASIC INFO ═══ -->
                <section class="glass-card">
                    <div class="sec-head"><span class="sec-num">1</span><div><h3>Basic Information</h3><p>Title, category, and description</p></div></div>

                    <div class="grid-2">
                        <div class="fg">
                            <label>Title *</label>
                            <input type="text" name="title" required maxlength="200"
                                   value="<?= e($video['title'] ?? '') ?>"
                                   placeholder="e.g., SAIP Platform Overview">
                        </div>
                        <div class="fg">
                            <label>Category *</label>
                            <input type="text" name="category" required maxlength="60" list="catList"
                                   value="<?= e($video['category'] ?? 'Demo') ?>"
                                   placeholder="e.g., Demo, Features, Tutorials">
                            <datalist id="catList">
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= e($c) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="fg">
                            <label>Duration</label>
                            <input type="text" name="duration" maxlength="20"
                                   value="<?= e($video['duration'] ?? '') ?>"
                                   placeholder="e.g., 2:45">
                        </div>
                        <div class="fg">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" min="0"
                                   value="<?= (int)($video['sort_order'] ?? 0) ?>">
                        </div>
                    </div>

                    <div class="fg">
                        <label>Description</label>
                        <textarea name="description" rows="3" maxlength="500"
                                  placeholder="Short description of the video…"><?= e($video['description'] ?? '') ?></textarea>
                    </div>
                </section>

                <!-- ═══ 2. VIDEO SOURCE ═══ -->
                <section class="glass-card">
                    <div class="sec-head"><span class="sec-num">2</span><div><h3>Video Source</h3><p>Upload a file or paste a YouTube/Vimeo URL</p></div></div>

                    <div class="grid-2">
                        <div class="fg">
                            <label>Type</label>
                            <select name="type" id="videoType" onchange="updateTypeUI()">
                                <?php foreach (['mp4'=>'Local MP4 file','youtube'=>'YouTube embed','vimeo'=>'Vimeo embed'] as $k=>$label): ?>
                                    <option value="<?= $k ?>" <?= ($video['type'] ?? 'mp4') === $k ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label>Source URL</label>
                            <input type="text" name="src" id="srcInput" maxlength="500"
                                   value="<?= e($video['src'] ?? '') ?>"
                                   placeholder="/assets/videos/demo.mp4 or https://www.youtube.com/embed/VIDEO_ID">
                            <small class="fg-hint" id="srcHint">
                                For YouTube, use the /embed/ URL. For MP4, use the full path or upload below.
                            </small>
                        </div>
                    </div>

                    <div class="fg">
                        <label>Or Upload MP4 (max 200 MB)</label>
                        <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime">
                        <small class="fg-hint">
                            Uploading a file overrides the Source URL above.
                            <?php if ($isEdit && !empty($video['src'])): ?>
                                <br>Current: <code><?= e($video['src']) ?></code>
                            <?php endif; ?>
                        </small>
                    </div>
                </section>

                <!-- ═══ 3. THUMBNAIL ═══ -->
                <section class="glass-card">
                    <div class="sec-head"><span class="sec-num">3</span><div><h3>Thumbnail</h3><p>Preview image shown in the playlist</p></div></div>

                    <?php if (!empty($video['thumbnail'])): ?>
                        <div class="thumb-preview">
                            <img src="<?= e($video['thumbnail']) ?>" alt="">
                        </div>
                    <?php endif; ?>

                    <div class="fg">
                        <label>Upload Thumbnail (max 3 MB)</label>
                        <input type="file" name="thumbnail" accept="image/*">
                        <small class="fg-hint">Recommended: 480×270 px (16:9)</small>
                    </div>
                </section>

                <!-- ═══ 4. STATUS ═══ -->
                <section class="glass-card">
                    <div class="sec-head"><span class="sec-num">4</span><div><h3>Status</h3><p>Control visibility and priority</p></div></div>

                    <label class="toggle-row">
                        <div>
                            <strong>Active</strong>
                            <small>Show this video on the landing page</small>
                        </div>
                        <input type="checkbox" name="is_active" value="1" class="toggle-input"
                               <?= (!$isEdit || !empty($video['is_active'])) ? 'checked' : '' ?>>
                        <span class="toggle-switch"></span>
                    </label>

                    <label class="toggle-row">
                        <div>
                            <strong>Featured</strong>
                            <small>Auto-play this video when the demo modal opens</small>
                        </div>
                        <input type="checkbox" name="is_featured" value="1" class="toggle-input"
                               <?= !empty($video['is_featured']) ? 'checked' : '' ?>>
                        <span class="toggle-switch"></span>
                    </label>
                </section>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/demoAdmin" class="btn-ghost">Cancel</a>
                    <button type="submit" class="btn-hero-primary">💾 <?= $isEdit ? 'Update Video' : 'Add Video' ?></button>
                </div>
            </form>

        </main>
    </div>
</div>

<style>
html,body{margin:0!important;padding:0!important;overflow-x:hidden!important}
.admin-page.dashboard-layout{display:block!important;width:100%!important;background:#0b1120!important;min-height:100vh!important}
.admin-page .main-wrap{margin-left:260px!important;width:calc(100% - 260px)!important;min-height:100vh!important;box-sizing:border-box!important}
.admin-page .main-wrap > .main-content{padding:20px 28px 60px!important;display:flex!important;flex-direction:column!important;gap:18px!important;box-sizing:border-box!important}
.admin-page .glass-card{background:rgba(30,41,59,.7)!important;backdrop-filter:blur(16px)!important;border:1px solid rgba(255,255,255,.08)!important;border-radius:20px!important;padding:22px!important;box-sizing:border-box!important;width:100%!important}

.ad-hero{padding:24px 28px!important;background:linear-gradient(135deg,rgba(167,139,250,.15),rgba(56,189,248,.08))!important}
.back-link{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:#94a3b8;text-decoration:none;font-weight:600;margin-bottom:12px}
.back-link:hover{color:#a78bfa}
.ad-hero h1{font-size:24px;font-weight:800;color:#f1f5f9;margin:0 0 6px}
.ad-hero p{color:#94a3b8;font-size:13.5px;margin:0;max-width:600px}

.video-form{display:flex;flex-direction:column;gap:18px;width:100%}

.sec-head{display:flex;align-items:center;gap:14px;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,.06)}
.sec-num{width:34px;height:34px;background:linear-gradient(135deg,#a78bfa,#7c3aed);color:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;flex-shrink:0;box-shadow:0 4px 12px rgba(167,139,250,.35)}
.sec-head h3{font-size:15px;color:#f1f5f9;margin:0 0 2px;font-weight:800}
.sec-head p{font-size:12px;color:#94a3b8;margin:0}

.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
.fg{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.fg:last-child{margin-bottom:0}
.fg label{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px}
.fg input,.fg select,.fg textarea{padding:12px 15px;background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.12);border-radius:11px;color:#f1f5f9;font-size:14px;font-family:inherit;outline:none;box-sizing:border-box;width:100%;transition:.2s}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:#a78bfa;box-shadow:0 0 0 3px rgba(167,139,250,.15)}
.fg select option{background:#1e293b}
.fg textarea{resize:vertical;min-height:70px}
.fg-hint{font-size:11px;color:#64748b;font-style:italic}
.fg code{background:rgba(0,0,0,.35);padding:2px 6px;border-radius:4px;font-size:11px;color:#38bdf8}

.thumb-preview{margin-bottom:14px;border-radius:12px;overflow:hidden;max-width:320px;border:1px solid rgba(255,255,255,.08)}
.thumb-preview img{width:100%;display:block}

.toggle-row{display:flex;align-items:center;gap:14px;padding:14px 16px;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.06);border-radius:12px;cursor:pointer;margin-bottom:10px}
.toggle-row div{flex:1}
.toggle-row strong{display:block;font-size:13.5px;color:#f1f5f9;font-weight:700;margin-bottom:2px}
.toggle-row small{display:block;font-size:11.5px;color:#94a3b8}
.toggle-input{display:none}
.toggle-switch{width:46px;height:26px;background:rgba(255,255,255,.1);border-radius:999px;position:relative;transition:.25s;flex-shrink:0}
.toggle-switch::after{content:'';position:absolute;top:3px;left:3px;width:20px;height:20px;background:#fff;border-radius:50%;transition:.25s cubic-bezier(.4,0,.2,1);box-shadow:0 2px 6px rgba(0,0,0,.3)}
.toggle-input:checked ~ .toggle-switch{background:linear-gradient(135deg,#a78bfa,#7c3aed);box-shadow:0 0 16px rgba(167,139,250,.4)}
.toggle-input:checked ~ .toggle-switch::after{left:23px}

.form-actions{display:flex;justify-content:flex-end;gap:10px;padding:18px 22px;background:rgba(30,41,59,.7);border:1px solid rgba(255,255,255,.08);border-radius:20px;flex-wrap:wrap}
.btn-hero-primary{display:inline-flex;align-items:center;gap:8px;padding:13px 26px;background:linear-gradient(135deg,#a78bfa,#7c3aed);color:#fff;border:none;border-radius:12px;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;box-shadow:0 4px 15px rgba(167,139,250,.35);transition:.2s;font-family:inherit}
.btn-hero-primary:hover{transform:translateY(-2px);box-shadow:0 6px 22px rgba(167,139,250,.55)}
.btn-ghost{padding:13px 24px;background:transparent;color:#94a3b8;border:1px solid rgba(255,255,255,.12);border-radius:12px;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}
.btn-ghost:hover{background:rgba(255,255,255,.05);color:#cbd5e1}

@media(max-width:900px){
    .admin-page .main-wrap{margin-left:0!important;width:100%!important;padding-top:64px!important}
    .admin-page .main-wrap > .main-content{padding:14px!important}
    .grid-2{grid-template-columns:1fr!important}
    .form-actions{flex-direction:column-reverse}
    .form-actions .btn-hero-primary,.form-actions .btn-ghost{width:100%;justify-content:center}
}
</style>

<script>
function updateTypeUI() {
    var t = document.getElementById('videoType').value;
    var input = document.getElementById('srcInput');
    var hint = document.getElementById('srcHint');
    if (t === 'youtube') {
        input.placeholder = 'https://www.youtube.com/embed/VIDEO_ID';
        hint.innerHTML = 'Use the YouTube **embed** URL (watch → Share → Embed). Format: <code>https://www.youtube.com/embed/VIDEO_ID</code>';
    } else if (t === 'vimeo') {
        input.placeholder = 'https://player.vimeo.com/video/VIDEO_ID';
        hint.innerHTML = 'Use the Vimeo player URL. Format: <code>https://player.vimeo.com/video/VIDEO_ID</code>';
    } else {
        input.placeholder = '/assets/videos/demo.mp4';
        hint.innerHTML = 'Use a full path like <code>/assets/videos/demo.mp4</code> or upload a file below.';
    }
}
document.addEventListener('DOMContentLoaded', updateTypeUI);
</script>