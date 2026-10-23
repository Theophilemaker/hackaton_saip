<?php
$u = $user ?? [];
$districts = $districts ?? [];
$crops     = $crops     ?? [];

$name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
$initials = strtoupper(substr($name ?: 'U', 0, 1));
if (strpos($name, ' ') !== false) {
    $p = explode(' ', $name);
    $initials = strtoupper(substr($p[0],0,1) . substr(end($p),0,1));
}
?>
<div class="dashboard-layout profile-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="main-content">

            <section class="glass-card edit-hero">
                <div>
                    <a href="<?= BASE_URL ?>/profile" class="back-link">← Back to profile</a>
                    <h1>✏️ Edit Profile</h1>
                    <p>Update your personal information, photos, and privacy settings.</p>
                </div>
            </section>

            <form method="POST" action="<?= BASE_URL ?>/profile/save"
                  enctype="multipart/form-data" class="edit-form" id="profileEditForm">
                <?= \App\Core\Security::csrfField() ?>
                <input type="hidden" name="existing_avatar" value="<?= e($u['profile_photo'] ?? '') ?>">
                <input type="hidden" name="existing_cover" value="<?= e($u['cover_photo'] ?? '') ?>">

                <div class="edit-layout">

                    <!-- LEFT: FORM -->
                    <div class="edit-main">

                        <!-- Section 1 — Photos -->
                        <section class="glass-card">
                            <div class="sec-head"><span class="sec-num">1</span>
                                <div><h3>Photos</h3><p>Profile picture and cover image</p></div></div>

                            <div class="photo-upload-row">
                                <div class="avatar-upload-wrap">
                                    <div class="avatar-preview" id="avatarPreview">
                                        <?php if (!empty($u['profile_photo'])): ?>
                                            <img src="<?= e($u['profile_photo']) ?>" id="avatarImg" alt="">
                                        <?php else: ?>
                                            <div class="preview-initials" id="initialsPreview"><?= e($initials) ?></div>
                                        <?php endif; ?>
                                        <label class="avatar-edit-btn" for="avatarInput">
                                            📷
                                            <input type="file" name="avatar" id="avatarInput" accept="image/*" hidden>
                                        </label>
                                    </div>
                                    <small>Profile photo · Max 3 MB</small>
                                </div>

                                <div class="cover-upload-wrap">
                                    <div class="cover-preview" id="coverPreview"
                                         <?= !empty($u['cover_photo']) ? 'style="background-image:url(' . e($u['cover_photo']) . ')"' : '' ?>>
                                        <span id="coverPlaceholder" <?= !empty($u['cover_photo']) ? 'style="display:none"' : '' ?>>🖼️ No cover photo</span>
                                        <label class="cover-edit-btn" for="coverInput">
                                            📷 Change cover
                                            <input type="file" name="cover" id="coverInput" accept="image/*" hidden>
                                        </label>
                                    </div>
                                    <small>Cover image · Max 5 MB</small>
                                </div>
                            </div>
                        </section>

                        <!-- Section 2 — Personal -->
                        <section class="glass-card">
                            <div class="sec-head"><span class="sec-num">2</span>
                                <div><h3>Personal Information</h3><p>Your name and contact details</p></div></div>

                            <div class="grid-2">
                                <div class="fg">
                                    <label>First Name *</label>
                                    <input type="text" name="first_name" required maxlength="100"
                                           value="<?= e($u['first_name'] ?? '') ?>">
                                </div>
                                <div class="fg">
                                    <label>Last Name</label>
                                    <input type="text" name="last_name" maxlength="100"
                                           value="<?= e($u['last_name'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="grid-2">
                                <div class="fg">
                                    <label>Email *</label>
                                    <input type="email" name="email" required maxlength="150"
                                           value="<?= e($u['email'] ?? '') ?>">
                                </div>
                                <div class="fg">
                                    <label>Phone</label>
                                    <input type="tel" name="phone" maxlength="30"
                                           value="<?= e($u['phone'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="grid-2">
                                <div class="fg">
                                    <label>WhatsApp</label>
                                    <input type="tel" name="whatsapp" maxlength="30"
                                           placeholder="+250 7xx xxx xxx"
                                           value="<?= e($u['whatsapp'] ?? '') ?>">
                                </div>
                                <div class="fg">
                                    <label>District</label>
                                    <select name="district">
                                        <option value="">— Select district —</option>
                                        <?php foreach ($districts as $d): ?>
                                            <option value="<?= e($d['name']) ?>"
                                                <?= (($u['district'] ?? '') === $d['name']) ? 'selected' : '' ?>>
                                                <?= e($d['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="fg">
                                <label>Sector</label>
                                <input type="text" name="sector" maxlength="100"
                                       value="<?= e($u['sector'] ?? '') ?>">
                            </div>

                            <div class="fg">
                                <label>Bio</label>
                                <textarea name="bio" rows="4" maxlength="500"
                                          placeholder="Tell us about yourself, your experience, and what you grow…"><?= e($u['bio'] ?? '') ?></textarea>
                                <small class="fg-hint">This appears on your profile (max 500 characters)</small>
                            </div>
                        </section>

                        <!-- Section 3 — Farming -->
                        <section class="glass-card">
                            <div class="sec-head"><span class="sec-num">3</span>
                                <div><h3>Farming Details</h3><p>Optional — helps personalize your experience</p></div></div>

                            <div class="grid-2">
                                <div class="fg">
                                    <label>Farm Size (hectares)</label>
                                    <input type="number" name="farm_size_ha" step="0.01" min="0"
                                           value="<?= e($u['farm_size_ha'] ?? '') ?>">
                                </div>
                                <div class="fg">
                                    <label>Years of Experience</label>
                                    <input type="number" name="years_experience" min="0" max="80"
                                           value="<?= e($u['years_experience'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="fg">
                                <label>Primary Crops</label>
                                <input type="text" name="primary_crops" maxlength="255"
                                       placeholder="e.g., Maize, Beans, Coffee"
                                       value="<?= e($u['primary_crops'] ?? '') ?>">
                                <small class="fg-hint">Comma-separated list</small>
                            </div>
                        </section>

                        <!-- Section 4 — Privacy -->
                        <section class="glass-card">
                            <div class="sec-head"><span class="sec-num">4</span>
                                <div><h3>Privacy</h3><p>Control who can see your profile</p></div></div>

                            <label class="toggle-row">
                                <div>
                                    <strong>Public profile</strong>
                                    <small>Allow other farmers and officers to view your profile</small>
                                </div>
                                <input type="checkbox" name="is_public" value="1" class="toggle-input"
                                       <?= (!isset($u['is_public']) || !empty($u['is_public'])) ? 'checked' : '' ?>>
                                <span class="toggle-switch"></span>
                            </label>
                        </section>

                        <div class="form-actions">
                            <a href="<?= BASE_URL ?>/profile" class="btn-ghost">Cancel</a>
                            <button type="submit" class="btn-hero-primary" id="saveBtn">💾 Save Changes</button>
                        </div>
                    </div>

                    <!-- RIGHT: Sidebar with avatar preview + password -->
                    <aside class="edit-side">
                        <div class="glass-card preview-card">
                            <h4 class="preview-title">Preview</h4>
                            <div class="pv-avatar" id="pvAvatar">
                                <?php if (!empty($u['profile_photo'])): ?>
                                    <img src="<?= e($u['profile_photo']) ?>" id="pvAvatarImg" alt="">
                                <?php else: ?>
                                    <div id="pvAvatarInitials"><?= e($initials) ?></div>
                                <?php endif; ?>
                            </div>
                            <strong class="pv-name" id="pvName"><?= e($name ?: 'Your Name') ?></strong>
                            <small class="pv-role"><?= e($u['role_name'] ?? 'Farmer') ?></small>
                        </div>

                        <div class="glass-card password-card">
                            <h4 class="preview-title">🔒 Change Password</h4>
                            <form method="POST" action="<?= BASE_URL ?>/profile/password">
                                <?= \App\Core\Security::csrfField() ?>
                                <div class="fg">
                                    <label>Current password</label>
                                    <input type="password" name="current_password" required>
                                </div>
                                <div class="fg">
                                    <label>New password</label>
                                    <input type="password" name="new_password" required minlength="6">
                                </div>
                                <div class="fg">
                                    <label>Confirm new</label>
                                    <input type="password" name="confirm_password" required minlength="6">
                                </div>
                                <button type="submit" class="btn-pwd">Update Password</button>
                            </form>
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

    var avatarInput = document.getElementById('avatarInput');
    var coverInput  = document.getElementById('coverInput');
    var form        = document.getElementById('profileEditForm');
    var saveBtn     = document.getElementById('saveBtn');

    /* ───────────────────────────────────────────────
       AVATAR — preview only, NEVER destroys the input
       ─────────────────────────────────────────────── */
    if (avatarInput) {
        avatarInput.addEventListener('change', function () {
            if (!this.files || !this.files.length) return;
            var file = this.files[0];

            if (!file.type.match(/^image\/(jpeg|png|webp|gif)$/i)) {
                alert('Please choose a JPG, PNG, WebP, or GIF image.');
                this.value = '';
                return;
            }
            if (file.size > 3 * 1024 * 1024) {
                alert('Profile photo must be under 3 MB.');
                this.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                /* Update the edit-page avatar preview */
                var previewBox = document.getElementById('avatarPreview');
                if (previewBox) {
                    var existingImg = document.getElementById('avatarImg');
                    if (existingImg) {
                        existingImg.src = e.target.result;
                    } else {
                        var initialsDiv = document.getElementById('initialsPreview');
                        if (initialsDiv) initialsDiv.remove();
                        var img = document.createElement('img');
                        img.src = e.target.result;
                        img.id  = 'avatarImg';
                        img.alt = '';
                        /* Insert as first child so the 📷 button stays visible */
                        previewBox.insertBefore(img, previewBox.firstChild);
                    }
                }
                /* Update the sidebar preview */
                var pv = document.getElementById('pvAvatar');
                if (pv) {
                    pv.innerHTML = '<img src="' + e.target.result + '" alt="">';
                }
            };
            reader.readAsDataURL(file);
        });
    }

    /* ───────────────────────────────────────────────
       COVER — preview only, NEVER destroys the input
       ─────────────────────────────────────────────── */
    if (coverInput) {
        coverInput.addEventListener('change', function () {
            if (!this.files || !this.files.length) return;
            var file = this.files[0];

            if (!file.type.match(/^image\/(jpeg|png|webp|gif)$/i)) {
                alert('Please choose a JPG, PNG, WebP, or GIF image.');
                this.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('Cover image must be under 5 MB.');
                this.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                var preview = document.getElementById('coverPreview');
                if (!preview) return;
                preview.style.backgroundImage = 'url(' + e.target.result + ')';
                preview.style.backgroundSize = 'cover';
                preview.style.backgroundPosition = 'center';

                /* Hide the "No cover photo" placeholder span, keep the button */
                var ph = document.getElementById('coverPlaceholder');
                if (ph) ph.style.display = 'none';
            };
            reader.readAsDataURL(file);
        });
    }

    /* ───────────────────────────────────────────────
       LIVE NAME PREVIEW
       ─────────────────────────────────────────────── */
    var fn = document.querySelector('input[name="first_name"]');
    var ln = document.querySelector('input[name="last_name"]');
    function updatePreviewName() {
        if (!fn) return;
        var v = (fn.value + ' ' + (ln && ln.value ? ln.value : '')).trim() || 'Your Name';
        var pvName = document.getElementById('pvName');
        if (pvName) pvName.textContent = v;
    }
    if (fn) fn.addEventListener('input', updatePreviewName);
    if (ln) ln.addEventListener('input', updatePreviewName);

    /* ───────────────────────────────────────────────
       PREVENT DOUBLE SUBMIT + show saving state
       ─────────────────────────────────────────────── */
    if (form && saveBtn) {
        form.addEventListener('submit', function () {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '⏳ Saving…';
            setTimeout(function () {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '💾 Save Changes';
            }, 15000);
        });
    }
})();
</script>

<style>
html,body{margin:0!important;padding:0!important;overflow-x:hidden!important}
.profile-page.dashboard-layout{display:block!important;width:100%!important;background:#0b1120!important;min-height:100vh!important}
.profile-page .main-wrap{margin-left:260px!important;width:calc(100% - 260px)!important;min-height:100vh!important;box-sizing:border-box!important}
.profile-page .main-content{padding:20px 28px 60px!important;display:flex!important;flex-direction:column!important;gap:18px!important;box-sizing:border-box!important}
.profile-page .glass-card{background:rgba(30,41,59,.7)!important;backdrop-filter:blur(16px)!important;border:1px solid rgba(255,255,255,.08)!important;border-radius:20px!important;padding:22px!important;box-sizing:border-box!important;width:100%!important}

.edit-hero{padding:22px 28px!important;background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(56,189,248,.08))!important}
.back-link{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:#94a3b8;text-decoration:none;font-weight:600;margin-bottom:12px}
.back-link:hover{color:#34d399}
.edit-hero h1{font-size:22px;font-weight:800;color:#f1f5f9;margin:0 0 6px}
.edit-hero p{color:#94a3b8;font-size:13.5px;margin:0;max-width:600px}

.edit-layout{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:18px;align-items:start}
.edit-main{display:flex;flex-direction:column;gap:18px;min-width:0}
.edit-side{position:sticky;top:20px;display:flex;flex-direction:column;gap:18px}

.sec-head{display:flex;align-items:center;gap:14px;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,.06)}
.sec-num{width:34px;height:34px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;flex-shrink:0;box-shadow:0 4px 12px rgba(16,185,129,.35)}
.sec-head h3{font-size:15px;color:#f1f5f9;margin:0 0 2px;font-weight:800}
.sec-head p{font-size:12px;color:#94a3b8;margin:0}

.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
.fg{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.fg:last-child{margin-bottom:0}
.fg label{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px}
.fg input,.fg select,.fg textarea{padding:12px 15px;background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.12);border-radius:11px;color:#f1f5f9;font-size:14px;font-family:inherit;outline:none;box-sizing:border-box;width:100%;transition:.2s}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.15)}
.fg select option{background:#1e293b}
.fg textarea{resize:vertical;min-height:80px;line-height:1.55}
.fg-hint{font-size:11px;color:#64748b;font-style:italic}

/* Photo upload row */
.photo-upload-row{display:flex;gap:24px;flex-wrap:wrap;align-items:flex-start}
.avatar-upload-wrap{display:flex;flex-direction:column;align-items:center;gap:8px;flex-shrink:0}
.avatar-upload-wrap small{font-size:11px;color:#64748b}
.avatar-preview{position:relative;width:130px;height:130px;border-radius:50%;overflow:visible;border:4px solid rgba(16,185,129,.35);background:rgba(0,0,0,.3)}
.avatar-preview img,.avatar-preview .preview-initials{width:100%;height:100%;border-radius:50%;object-fit:cover;display:flex;align-items:center;justify-content:center;font-size:44px;font-weight:800;color:#fff;background:linear-gradient(135deg,#10b981,#38bdf8)}
.avatar-edit-btn{position:absolute;bottom:0;right:0;width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-size:15px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:3px solid #1e293b;box-shadow:0 4px 12px rgba(16,185,129,.4);transition:.2s;z-index:5}
.avatar-edit-btn:hover{transform:scale(1.1)}

.cover-upload-wrap{flex:1;min-width:240px;display:flex;flex-direction:column;gap:8px}
.cover-upload-wrap small{font-size:11px;color:#64748b}
.cover-preview{position:relative;height:160px;border-radius:14px;background:rgba(0,0,0,.3);border:2px dashed rgba(16,185,129,.3);background-size:cover;background-position:center;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:13px;overflow:hidden}
.cover-edit-btn{position:absolute;bottom:12px;right:12px;padding:8px 14px;background:rgba(0,0,0,.75);color:#fff;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);transition:.15s;z-index:5}
.cover-edit-btn:hover{background:rgba(16,185,129,.9)}

/* Toggle */
.toggle-row{display:flex;align-items:center;gap:14px;padding:14px 16px;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.06);border-radius:12px;cursor:pointer}
.toggle-row div{flex:1}
.toggle-row strong{display:block;font-size:13.5px;color:#f1f5f9;font-weight:700;margin-bottom:2px}
.toggle-row small{display:block;font-size:11.5px;color:#94a3b8}
.toggle-input{display:none}
.toggle-switch{width:46px;height:26px;background:rgba(255,255,255,.1);border-radius:999px;position:relative;transition:.25s;flex-shrink:0}
.toggle-switch::after{content:'';position:absolute;top:3px;left:3px;width:20px;height:20px;background:#fff;border-radius:50%;transition:.25s;box-shadow:0 2px 6px rgba(0,0,0,.3)}
.toggle-input:checked ~ .toggle-switch{background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 0 16px rgba(16,185,129,.4)}
.toggle-input:checked ~ .toggle-switch::after{left:23px}

.form-actions{display:flex;justify-content:flex-end;gap:10px;padding:18px 22px;background:rgba(30,41,59,.7);border:1px solid rgba(255,255,255,.08);border-radius:20px;flex-wrap:wrap}
.btn-hero-primary{display:inline-flex;align-items:center;gap:8px;padding:13px 26px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:12px;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;box-shadow:0 4px 15px rgba(16,185,129,.35);transition:.2s;font-family:inherit}
.btn-hero-primary:hover{transform:translateY(-2px);box-shadow:0 6px 22px rgba(16,185,129,.55)}
.btn-hero-primary:disabled{opacity:.6;cursor:not-allowed;transform:none}
.btn-ghost{padding:13px 24px;background:transparent;color:#94a3b8;border:1px solid rgba(255,255,255,.12);border-radius:12px;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}
.btn-ghost:hover{background:rgba(255,255,255,.05);color:#cbd5e1}

.preview-title{font-size:12px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin:0 0 14px}
.preview-card{display:flex!important;flex-direction:column!important;align-items:center!important;text-align:center!important;padding:24px!important}
.pv-avatar{width:90px;height:90px;border-radius:50%;overflow:hidden;background:linear-gradient(135deg,#10b981,#38bdf8);display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:800;color:#fff;margin-bottom:12px;border:4px solid #1e293b;box-shadow:0 6px 20px rgba(16,185,129,.3)}
.pv-avatar img{width:100%;height:100%;object-fit:cover}
.pv-name{font-size:15px;font-weight:800;color:#f1f5f9;margin:0 0 3px}
.pv-role{font-size:11.5px;color:#94a3b8}

.password-card form{display:flex;flex-direction:column;gap:12px}
.password-card .fg{margin:0}
.password-card .fg input{padding:10px 13px;font-size:13px}
.btn-pwd{padding:11px 18px;background:rgba(56,189,248,.15);color:#38bdf8;border:1px solid rgba(56,189,248,.35);border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;margin-top:6px;transition:.15s}
.btn-pwd:hover{background:rgba(56,189,248,.28)}

@media(max-width:1100px){.edit-layout{grid-template-columns:1fr}.edit-side{position:static}}
@media(max-width:900px){
    .profile-page .main-wrap{margin-left:0!important;width:100%!important;padding-top:64px!important}
    .profile-page .main-content{padding:14px!important}
    .grid-2{grid-template-columns:1fr!important}
    .photo-upload-row{flex-direction:column;align-items:stretch}
    .avatar-upload-wrap{align-self:center}
    .form-actions{flex-direction:column-reverse}
    .form-actions .btn-hero-primary,.form-actions .btn-ghost{width:100%;justify-content:center}
}
</style>