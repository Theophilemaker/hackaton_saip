<?php
$p       = $data['profile'] ?? [];
$name    = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
$initials = strtoupper(substr($name ?: 'A', 0, 1));
if (strpos($name, ' ') !== false) {
    $pp = explode(' ', $name);
    $initials = strtoupper(substr($pp[0], 0, 1) . substr(end($pp), 0, 1));
}
$avatar  = $p['avatar']      ?? null;
$cover   = $p['cover_photo'] ?? null;
?>
<div class="dashboard-layout admin-page profile-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ═══════════ HERO ═══════════ -->
        <section class="glass-card profile-hero">
            <div class="hero-content">
                <span class="hero-tag">Account Settings</span>
                <h1>👤 My Profile</h1>
                <p>Manage your personal information, security settings, and preferences.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin" class="btn-hero-secondary">← Back to Dashboard</a>
        </section>

        <!-- ═══════════ PROFILE FORM ═══════════ -->
        <section class="glass-card">
            <div class="card-header">
                <div>
                    <h3>📝 Personal Information</h3>
                    <p class="card-subtitle">Update your profile details, photos, and settings</p>
                </div>
                <span class="badge blue">ID #<?= (int)($p['id'] ?? 0) ?></span>
            </div>

            <form id="profileForm" enctype="multipart/form-data"
                  onsubmit="event.preventDefault(); saveProfile();">
                <?= \App\Core\Security::csrfField() ?>
                <input type="hidden" name="existing_avatar" value="<?= e($avatar ?? '') ?>">
                <input type="hidden" name="existing_cover"  value="<?= e($cover  ?? '') ?>">

                <!-- ══ AVATAR + FIELDS ══ -->
                <div class="profile-grid">

                    <!-- LEFT: AVATAR -->
                    <div class="avatar-section">
                        <div class="avatar-preview" id="avatarContainer">
                            <?php if ($avatar): ?>
                                <img src="<?= e($avatar) ?>?v=<?= time() ?>" alt="" id="avatarImg" class="avatar-img">
                            <?php else: ?>
                                <div class="avatar-placeholder" id="avatarImg"><?= e($initials) ?></div>
                            <?php endif; ?>
                        </div>

                        <label class="avatar-btn">
                            📷 Change Photo
                            <input type="file" name="avatar" id="avatarInput"
                                   accept="image/png,image/jpeg,image/webp" hidden>
                        </label>

                        <?php if ($avatar): ?>
                            <button type="button" class="avatar-remove" onclick="removeAvatar()">🗑️ Remove</button>
                        <?php endif; ?>

                        <small class="avatar-hint">JPG · PNG · WebP<br>Max 3 MB</small>
                    </div>

                    <!-- RIGHT: FIELDS -->
                    <div class="fields-section">
                        <div class="form-row-2">
                            <div class="form-group">
                                <label>First Name <span class="req">*</span></label>
                                <input type="text" id="firstName" name="first_name" required maxlength="100"
                                       value="<?= e($p['first_name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" id="lastName" name="last_name" maxlength="100"
                                       value="<?= e($p['last_name'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label>Email <span class="req">*</span></label>
                                <input type="email" id="email" name="email" required maxlength="150"
                                       value="<?= e($p['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" id="phone" name="phone" maxlength="30"
                                       placeholder="+250 7XX XXX XXX"
                                       value="<?= e($p['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label>WhatsApp</label>
                                <input type="tel" id="whatsapp" name="whatsapp" maxlength="30"
                                       placeholder="+250 7XX XXX XXX"
                                       value="<?= e($p['whatsapp'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>District</label>
                                <input type="text" id="district" name="district" maxlength="100"
                                       placeholder="e.g., Huye"
                                       value="<?= e($p['district'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Bio / About</label>
                            <textarea id="bio" name="bio" rows="3" maxlength="500"
                                      placeholder="Tell us a bit about yourself…"><?= e($p['bio'] ?? '') ?></textarea>
                            <small class="char-hint"><span id="bioCount">0</span>/500</small>
                        </div>
                    </div>
                </div>

                <!-- ══ COVER PHOTO ══ -->
                <h4 class="section-title">🖼️ Cover Image</h4>
                <div class="cover-upload-wrap">
                    <div class="cover-preview" id="coverPreview"
                         <?= $cover ? 'style="background-image:url(' . e($cover) . ')"' : '' ?>>
                        <?php if (!$cover): ?>
                            <span>🖼️ No cover photo yet</span>
                        <?php endif; ?>
                        <label class="cover-edit-btn">
                            📷 Change cover
                            <input type="file" name="cover" id="coverInput" accept="image/*" hidden>
                        </label>
                    </div>
                    <small class="cover-hint">Recommended: 1600×400 px · Max 5 MB</small>
                </div>

                <!-- ══ PREFERENCES ══ -->
                <h4 class="section-title">⚙️ Preferences</h4>

                <div class="form-row-3">
                    <div class="form-group">
                        <label>Timezone</label>
                        <select id="timezone" name="timezone">
                            <?php
                            $tzs = ['Africa/Kigali','UTC','Africa/Nairobi','Africa/Lagos','Europe/London','Europe/Paris','America/New_York','Asia/Dubai'];
                            $curTz = $p['timezone'] ?? 'Africa/Kigali';
                            foreach ($tzs as $tz): ?>
                                <option value="<?= $tz ?>" <?= $curTz === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Language</label>
                        <select id="language" name="language">
                            <?php $curLang = $p['language'] ?? ($_SESSION['lang'] ?? 'en'); ?>
                            <option value="en" <?= $curLang === 'en' ? 'selected' : '' ?>>🇬🇧 English</option>
                            <option value="rw" <?= $curLang === 'rw' ? 'selected' : '' ?>>🇷🇼 Kinyarwanda</option>
                            <option value="fr" <?= $curLang === 'fr' ? 'selected' : '' ?>>🇫🇷 Français</option>
                            <option value="sw" <?= $curLang === 'sw' ? 'selected' : '' ?>>🇹🇿 Kiswahili</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date Format</label>
                        <select id="date_format" name="date_format">
                            <?php $curFmt = $p['date_format'] ?? 'M d, Y'; ?>
                            <option value="M d, Y"  <?= $curFmt === 'M d, Y'  ? 'selected' : '' ?>>Jan 15, 2025</option>
                            <option value="d/m/Y"   <?= $curFmt === 'd/m/Y'   ? 'selected' : '' ?>>15/01/2025</option>
                            <option value="Y-m-d"   <?= $curFmt === 'Y-m-d'   ? 'selected' : '' ?>>2025-01-15</option>
                        </select>
                    </div>
                </div>

                <!-- ══ SECURITY ══ -->
                <h4 class="section-title">🔒 Security</h4>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>New Password <span class="opt">(leave blank to keep current)</span></label>
                        <input type="password" id="password" name="password" minlength="6"
                               placeholder="••••••••" oninput="checkPasswordStrength(this.value)">
                        <div class="pwd-strength" id="pwdStrength" style="display:none;">
                            <div class="pwd-bar"><div class="pwd-fill" id="pwdFill"></div></div>
                            <small id="pwdLabel">Weak</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" id="password_confirm" name="password_confirm" minlength="6"
                               placeholder="••••••••" oninput="checkPasswordMatch()">
                        <small id="pwdMatch" style="display:none;"></small>
                    </div>
                </div>

                <!-- 2FA -->
                <label class="toggle-card">
                    <input type="checkbox" id="two_factor" name="two_factor_enabled" value="1"
                           <?= !empty($p['two_factor_enabled']) ? 'checked' : '' ?>>
                    <span class="toggle-switch"></span>
                    <span class="toggle-info">
                        <strong>Two-Factor Authentication</strong>
                        <small>Require a verification code when signing in from new devices</small>
                    </span>
                </label>

                <!-- Privacy -->
                <label class="toggle-card">
                    <input type="checkbox" id="is_public" name="is_public" value="1"
                           <?= (!isset($p['is_public']) || !empty($p['is_public'])) ? 'checked' : '' ?>>
                    <span class="toggle-switch"></span>
                    <span class="toggle-info">
                        <strong>Public Profile</strong>
                        <small>Allow other farmers and officers to view your profile</small>
                    </span>
                </label>

                <!-- ACTIONS -->
                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/admin" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-save" id="saveBtn">💾 Save Changes</button>
                </div>
            </form>
        </section>

        <!-- ═══════════ ACCOUNT INFO ═══════════ -->
        <section class="glass-card">
            <div class="card-header">
                <div>
                    <h3>ℹ️ Account Information</h3>
                    <p class="card-subtitle">Read-only metadata about your account</p>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">User ID</span>
                    <span class="info-value">#<?= (int)($p['id'] ?? 0) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Role</span>
                    <span class="info-value"><?= e($_SESSION['role_name'] ?? 'Administrator') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="status-pill <?= ($p['status'] ?? 'active') === 'active' ? 'ok' : 'bad' ?>">
                            <?= ucfirst($p['status'] ?? 'active') ?>
                        </span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Member Since</span>
                    <span class="info-value">
                        <?= !empty($p['created_at']) ? date('M d, Y', strtotime($p['created_at'])) : '—' ?>
                    </span>
                </div>
            </div>
        </section>

        <!-- ═══════════ RECENT ACTIVITY ═══════════ -->
        <section class="glass-card">
            <div class="card-header">
                <div>
                    <h3>📋 Recent Activity</h3>
                    <p class="card-subtitle">Your last 20 actions</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/activity" class="btn-view-all">View All →</a>
            </div>

            <?php if (empty($data['activity'])): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>No Activity Yet</h3>
                    <p>Your actions will appear here as you use the platform.</p>
                </div>
            <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($data['activity'] as $a):
                        $action = $a['action'] ?? '';
                        $icon = '📌';
                        if (strpos($action, 'login')    !== false) $icon = '🔓';
                        elseif (strpos($action, 'password') !== false) $icon = '🔒';
                        elseif (strpos($action, 'profile')  !== false) $icon = '👤';
                        elseif (strpos($action, 'backup')   !== false) $icon = '💾';
                        elseif (strpos($action, 'delete')   !== false) $icon = '🗑️';
                        elseif (strpos($action, 'create') !== false || strpos($action, 'add') !== false) $icon = '➕';
                        elseif (strpos($action, 'update') !== false || strpos($action, 'edit') !== false) $icon = '✏️';
                    ?>
                        <div class="activity-item">
                            <div class="activity-icon"><?= $icon ?></div>
                            <div class="activity-body">
                                <strong><?= e(str_replace('_', ' ', ucfirst($action))) ?></strong>
                                <?php if (!empty($a['description'])): ?>
                                    <p><?= e($a['description']) ?></p>
                                <?php endif; ?>
                                <small>
                                    🕐 <?= date('M d, Y H:i', strtotime($a['created_at'])) ?>
                                    <?php if (!empty($a['ip_address'])): ?> · 🌐 <?= e($a['ip_address']) ?><?php endif; ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>
</div>

<script>
(function () {
    'use strict';

    var CSRF = '<?= e(\App\Core\Security::csrfToken()) ?>';
    var BASE = '<?= BASE_URL ?>';

    /* ═══════ AVATAR PREVIEW ═══════ */
    var avatarInput = document.getElementById('avatarInput');
    if (avatarInput) {
        avatarInput.addEventListener('change', function () {
            if (!this.files.length) return;
            var f = this.files[0];
            if (f.size > 3 * 1024 * 1024) {
                showToast('Image must be under 3 MB.', 'error');
                this.value = '';
                return;
            }
            var r = new FileReader();
            r.onload = function (e) {
                document.getElementById('avatarContainer').innerHTML =
                    '<img src="' + e.target.result + '" alt="" id="avatarImg" class="avatar-img">';
            };
            r.readAsDataURL(f);
        });
    }

    /* ═══════ COVER PREVIEW ═══════ */
    var coverInput = document.getElementById('coverInput');
    if (coverInput) {
        coverInput.addEventListener('change', function () {
            if (!this.files.length) return;
            var f = this.files[0];
            if (f.size > 5 * 1024 * 1024) {
                showToast('Cover must be under 5 MB.', 'error');
                this.value = '';
                return;
            }
            var r = new FileReader();
            r.onload = function (e) {
                var prev = document.getElementById('coverPreview');
                prev.style.backgroundImage = 'url(' + e.target.result + ')';
                prev.style.backgroundSize = 'cover';
                prev.style.backgroundPosition = 'center';
                var label = prev.querySelector('.cover-edit-btn');
                prev.innerHTML = '';
                if (label) prev.appendChild(label);
            };
            r.readAsDataURL(f);
        });
    }

    /* ═══════ BIO COUNTER ═══════ */
    var bio = document.getElementById('bio');
    if (bio) {
        bio.addEventListener('input', function () {
            document.getElementById('bioCount').textContent = this.value.length;
        });
        document.getElementById('bioCount').textContent = bio.value.length;
    }

    /* ═══════ PASSWORD STRENGTH ═══════ */
    window.checkPasswordStrength = function (pwd) {
        var wrap = document.getElementById('pwdStrength');
        var fill = document.getElementById('pwdFill');
        var label = document.getElementById('pwdLabel');
        if (!pwd) { wrap.style.display = 'none'; return; }
        wrap.style.display = 'block';

        var score = 0;
        if (pwd.length >= 6) score++;
        if (pwd.length >= 10) score++;
        if (/[A-Z]/.test(pwd)) score++;
        if (/[0-9]/.test(pwd)) score++;
        if (/[^A-Za-z0-9]/.test(pwd)) score++;

        var levels = [
            { pct: 20,  color: '#ef4444', text: 'Very Weak' },
            { pct: 40,  color: '#f97316', text: 'Weak' },
            { pct: 60,  color: '#fbbf24', text: 'Fair' },
            { pct: 80,  color: '#84cc16', text: 'Good' },
            { pct: 100, color: '#10b981', text: 'Strong' },
        ];
        var lvl = levels[Math.min(score, 4)];
        fill.style.width = lvl.pct + '%';
        fill.style.background = lvl.color;
        label.textContent = lvl.text;
        label.style.color = lvl.color;
    };

    /* ═══════ PASSWORD MATCH ═══════ */
    window.checkPasswordMatch = function () {
        var pwd = document.getElementById('password').value;
        var cnf = document.getElementById('password_confirm').value;
        var el = document.getElementById('pwdMatch');
        if (!cnf) { el.style.display = 'none'; return; }
        el.style.display = 'block';
        if (pwd === cnf) {
            el.textContent = '✅ Passwords match';
            el.style.color = '#34d399';
        } else {
            el.textContent = '❌ Passwords do not match';
            el.style.color = '#f87171';
        }
    };

    /* ═══════ REMOVE AVATAR ═══════ */
    window.removeAvatar = function () {
        if (!confirm('Remove your profile photo?')) return;
        var fd = new FormData();
        fd.append('remove_avatar', '1');
        fd.append('_csrf', CSRF);
        fetch(BASE + '/admin/updateProfile', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.success) {
                    showToast('Avatar removed.', 'success');
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    showToast(d.error || 'Failed', 'error');
                }
            })
            .catch(function () { showToast('Network error', 'error'); });
    };

    /* ═══════ SAVE PROFILE ═══════ */
    window.saveProfile = async function () {
        var pwd  = document.getElementById('password').value;
        var pwd2 = document.getElementById('password_confirm').value;

        if (pwd && pwd !== pwd2) { showToast('Passwords do not match.', 'error'); return; }
        if (pwd && pwd.length < 6) { showToast('Password must be at least 6 characters.', 'error'); return; }

        var btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.innerHTML = '⏳ Saving...';

        var fd = new FormData(document.getElementById('profileForm'));
        fd.set('two_factor_enabled', document.getElementById('two_factor').checked ? '1' : '0');
        fd.set('is_public',          document.getElementById('is_public').checked    ? '1' : '0');

        try {
            var res = await fetch(BASE + '/admin/updateProfile', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin'
            });
            var data = await res.json();
            if (data.success) {
                showToast(data.message || 'Profile updated!', 'success');
                setTimeout(function () { location.reload(); }, 900);
            } else {
                showToast(data.error || 'Failed to save', 'error');
                btn.disabled = false;
                btn.innerHTML = '💾 Save Changes';
            }
        } catch (err) {
            showToast('Error: ' + err.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '💾 Save Changes';
        }
    };

    /* ═══════ TOAST ═══════ */
    window.showToast = function (msg, type) {
        var t = document.createElement('div');
        t.className = 'toast ' + (type || 'success');
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { t.classList.add('show'); }, 10);
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { t.remove(); }, 300);
        }, 2600);
    };
})();
</script>

<style>
/* ═══════════════════════════════════════════════════════════
   LAYOUT
   ═══════════════════════════════════════════════════════════ */
html, body { margin: 0 !important; padding: 0 !important; overflow-x: hidden !important; }

.profile-page.dashboard-layout {
    display: block !important;
    width: 100% !important;
    min-height: 100vh !important;
    background: #0b1120 !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
}
.profile-page .main-content {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    min-height: 100vh !important;
    padding: 32px !important;
    box-sizing: border-box !important;
}
.profile-page .glass-card {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 28px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
}

/* ═══ HERO ═══ */
.profile-hero {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.12), rgba(167, 139, 250, 0.08)) !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 24px !important;
    flex-wrap: wrap !important;
}
.hero-content { flex: 1 !important; min-width: 280px !important; }
.hero-tag {
    display: inline-block !important;
    padding: 5px 14px !important;
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    margin-bottom: 12px !important;
}
.profile-hero h1 { font-size: 28px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.profile-hero p  { color: #94a3b8 !important; font-size: 14px !important; margin: 0 !important; max-width: 620px !important; }
.btn-hero-secondary {
    display: inline-flex !important; align-items: center !important; gap: 8px !important;
    padding: 13px 24px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #f1f5f9 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 13.5px !important; font-weight: 700 !important;
    text-decoration: none !important; white-space: nowrap !important;
    transition: all 0.2s !important;
}
.btn-hero-secondary:hover { background: rgba(255, 255, 255, 0.1) !important; transform: translateY(-2px) !important; }

/* ═══ CARD HEADER ═══ */
.card-header {
    display: flex !important; justify-content: space-between !important;
    align-items: center !important; margin-bottom: 24px !important;
    padding-bottom: 16px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    flex-wrap: wrap !important; gap: 12px !important;
}
.card-header h3 { font-size: 18px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 !important; }
.card-subtitle { font-size: 12.5px !important; color: #94a3b8 !important; margin: 4px 0 0 0 !important; }

.badge {
    display: inline-flex !important; align-items: center !important;
    padding: 5px 12px !important; border-radius: 999px !important;
    font-size: 11px !important; font-weight: 700 !important;
    letter-spacing: 0.5px !important; text-transform: uppercase !important;
}
.badge.blue { background: rgba(56,189,248,0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(56,189,248,0.3) !important; }

/* ═══ AVATAR + FIELDS GRID ═══ */
.profile-grid {
    display: grid !important;
    grid-template-columns: 220px 1fr !important;
    gap: 40px !important;
    margin-bottom: 8px !important;
    align-items: start !important;
}

.avatar-section {
    display: flex !important; flex-direction: column !important;
    align-items: center !important; gap: 12px !important;
    padding: 20px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 16px !important;
}

.avatar-preview {
    width: 160px !important; height: 160px !important;
    border-radius: 50% !important; overflow: hidden !important;
    border: 3px solid rgba(56, 189, 248, 0.3) !important;
    background: linear-gradient(135deg, #38bdf8, #a78bfa) !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    box-shadow: 0 8px 24px rgba(56, 189, 248, 0.2) !important;
    position: relative !important; flex-shrink: 0 !important;
}
.avatar-preview img,
.avatar-img {
    position: absolute !important; top: 0 !important; left: 0 !important;
    width: 100% !important; height: 100% !important;
    object-fit: cover !important; object-position: center !important;
    display: block !important; border-radius: 50% !important;
}
.avatar-placeholder {
    font-size: 60px !important; color: #fff !important;
    font-weight: 800 !important; letter-spacing: 2px !important;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important; line-height: 1 !important;
}

.avatar-btn {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    gap: 6px !important; padding: 10px 18px !important;
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    border-radius: 10px !important;
    font-size: 12.5px !important; font-weight: 700 !important;
    cursor: pointer !important; transition: all 0.2s !important;
    white-space: nowrap !important;
}
.avatar-btn:hover { background: rgba(56, 189, 248, 0.25) !important; transform: translateY(-2px) !important; }

.avatar-remove {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    gap: 6px !important; padding: 8px 16px !important;
    background: rgba(239, 68, 68, 0.1) !important;
    color: #f87171 !important;
    border: 1px solid rgba(239, 68, 68, 0.3) !important;
    border-radius: 10px !important;
    font-size: 12px !important; font-weight: 700 !important;
    cursor: pointer !important; transition: all 0.2s !important;
    font-family: inherit !important;
}
.avatar-remove:hover { background: rgba(239, 68, 68, 0.2) !important; }

.avatar-hint {
    color: #64748b !important; font-size: 11px !important;
    text-align: center !important; line-height: 1.4 !important;
}

.fields-section { min-width: 0 !important; }

/* ═══ FORM CONTROLS ═══ */
.form-row-2 { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 16px !important; margin-bottom: 16px !important; }
.form-row-3 { display: grid !important; grid-template-columns: repeat(3, 1fr) !important; gap: 16px !important; margin-bottom: 16px !important; }

.form-group {
    display: flex !important; flex-direction: column !important;
    gap: 6px !important; margin-bottom: 16px !important;
    min-width: 0 !important;
}
.form-group label {
    font-size: 11px !important; font-weight: 700 !important;
    color: #94a3b8 !important; text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
}
.req { color: #ef4444 !important; }
.opt { color: #64748b !important; font-weight: 400 !important; text-transform: none !important; font-size: 11px !important; }

.form-group input,
.form-group select,
.form-group textarea {
    width: 100% !important; padding: 12px 14px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important; font-size: 14px !important;
    font-family: inherit !important; outline: none !important;
    transition: border-color 0.2s, box-shadow 0.2s !important;
    box-sizing: border-box !important;
    resize: vertical !important;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
}
.form-group select {
    cursor: pointer !important;
    -webkit-appearance: none !important; appearance: none !important;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 16px !important;
    padding-right: 40px !important;
}
.form-group select option { background: #1e293b !important; color: #f1f5f9 !important; }

.char-hint { font-size: 11px !important; color: #64748b !important; text-align: right !important; margin-top: 4px !important; }

/* ═══ PASSWORD STRENGTH ═══ */
.pwd-strength { margin-top: 6px !important; }
.pwd-bar {
    width: 100% !important; height: 5px !important;
    background: rgba(255,255,255,0.08) !important;
    border-radius: 999px !important; overflow: hidden !important;
    margin-bottom: 4px !important;
}
.pwd-fill {
    height: 100% !important; width: 0 !important;
    background: #ef4444 !important; border-radius: 999px !important;
    transition: all 0.3s ease !important;
}
#pwdLabel { font-size: 11px !important; font-weight: 700 !important; }
#pwdMatch { font-size: 11px !important; font-weight: 700 !important; margin-top: 4px !important; }

/* ═══ COVER UPLOAD ═══ */
.cover-upload-wrap { margin-bottom: 16px !important; }
.cover-preview {
    position: relative !important;
    height: 200px !important;
    border-radius: 14px !important;
    background: rgba(0, 0, 0, 0.3) !important;
    border: 2px dashed rgba(56, 189, 248, 0.3) !important;
    background-size: cover !important;
    background-position: center !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    color: #94a3b8 !important; font-size: 13px !important;
    overflow: hidden !important;
    transition: all 0.2s !important;
}
.cover-preview:hover { border-color: rgba(56, 189, 248, 0.5) !important; }
.cover-edit-btn {
    position: absolute !important; bottom: 12px !important; right: 12px !important;
    padding: 8px 14px !important;
    background: rgba(0, 0, 0, 0.75) !important;
    color: #fff !important; border-radius: 9px !important;
    font-size: 12px !important; font-weight: 700 !important;
    cursor: pointer !important;
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    backdrop-filter: blur(8px) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    transition: all 0.15s !important;
}
.cover-edit-btn:hover { background: rgba(56, 189, 248, 0.9) !important; }
.cover-hint { display: block !important; font-size: 11px !important; color: #64748b !important; margin-top: 6px !important; }

/* ═══ TOGGLES ═══ */
.toggle-card {
    display: flex !important; align-items: flex-start !important;
    gap: 14px !important; padding: 16px 20px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 12px !important;
    cursor: pointer !important;
    margin-top: 8px !important; margin-bottom: 8px !important;
    transition: all 0.2s !important;
}
.toggle-card:hover { background: rgba(0, 0, 0, 0.3) !important; border-color: rgba(56, 189, 248, 0.3) !important; }
.toggle-card input[type="checkbox"] {
    position: absolute !important; opacity: 0 !important;
    pointer-events: none !important;
}
.toggle-switch {
    position: relative !important;
    width: 40px !important; height: 22px !important;
    background: rgba(255, 255, 255, 0.1) !important;
    border-radius: 999px !important;
    transition: background 0.2s !important;
    flex-shrink: 0 !important; margin-top: 2px !important;
}
.toggle-switch::after {
    content: '' !important;
    position: absolute !important; top: 2px !important; left: 2px !important;
    width: 18px !important; height: 18px !important;
    background: #94a3b8 !important;
    border-radius: 50% !important;
    transition: all 0.2s !important;
}
.toggle-card input:checked + .toggle-switch { background: rgba(56, 189, 248, 0.4) !important; }
.toggle-card input:checked + .toggle-switch::after { left: 20px !important; background: #38bdf8 !important; }
.toggle-info { flex: 1 !important; min-width: 0 !important; }
.toggle-info strong { display: block !important; font-size: 13px !important; color: #f1f5f9 !important; margin-bottom: 2px !important; font-weight: 700 !important; }
.toggle-info small { display: block !important; font-size: 12px !important; color: #94a3b8 !important; line-height: 1.4 !important; }

/* ═══ SECTION TITLES ═══ */
.section-title {
    font-size: 12px !important; font-weight: 700 !important;
    color: #38bdf8 !important; text-transform: uppercase !important;
    letter-spacing: 1.2px !important;
    margin: 32px 0 16px 0 !important;
    padding-bottom: 8px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}

/* ═══ FORM ACTIONS ═══ */
.form-actions {
    display: flex !important; justify-content: flex-end !important;
    gap: 12px !important; margin-top: 28px !important;
    padding-top: 20px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    flex-wrap: wrap !important;
}
.btn-save, .btn-cancel {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    gap: 8px !important; padding: 13px 28px !important;
    border-radius: 10px !important;
    font-size: 13.5px !important; font-weight: 700 !important;
    text-decoration: none !important; cursor: pointer !important;
    border: none !important; transition: all 0.2s !important;
    font-family: inherit !important;
}
.btn-save {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
}
.btn-save:hover:not(:disabled) {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5) !important;
}
.btn-save:disabled { opacity: 0.5 !important; cursor: not-allowed !important; }
.btn-cancel {
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
}
.btn-cancel:hover { background: rgba(255, 255, 255, 0.1) !important; color: #f1f5f9 !important; }

/* ═══ ACCOUNT INFO ═══ */
.info-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
    gap: 16px !important;
}
.info-item {
    display: flex !important; flex-direction: column !important;
    gap: 6px !important; padding: 16px 18px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 12px !important;
}
.info-label {
    font-size: 11px !important; font-weight: 700 !important;
    color: #94a3b8 !important; text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
}
.info-value { font-size: 15px !important; font-weight: 700 !important; color: #f1f5f9 !important; }
.status-pill {
    display: inline-flex !important; padding: 4px 12px !important;
    border-radius: 999px !important;
    font-size: 11px !important; font-weight: 700 !important;
    text-transform: capitalize !important;
}
.status-pill.ok  { background: rgba(52,211,153,0.15) !important; color: #34d399 !important; border: 1px solid rgba(52,211,153,0.3) !important; }
.status-pill.bad { background: rgba(248,113,113,0.15) !important; color: #f87171 !important; border: 1px solid rgba(248,113,113,0.3) !important; }

/* ═══ ACTIVITY ═══ */
.activity-list { display: flex !important; flex-direction: column !important; gap: 10px !important; }
.activity-item {
    display: flex !important; gap: 14px !important;
    padding: 14px 18px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 12px !important;
    transition: all 0.2s !important;
}
.activity-item:hover { background: rgba(56, 189, 248, 0.04) !important; border-color: rgba(56, 189, 248, 0.2) !important; }
.activity-icon {
    font-size: 18px !important;
    width: 40px !important; height: 40px !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    background: rgba(56, 189, 248, 0.08) !important;
    border-radius: 10px !important; flex-shrink: 0 !important;
}
.activity-body { flex: 1 !important; min-width: 0 !important; }
.activity-body strong { display: block !important; font-size: 13px !important; color: #f1f5f9 !important; margin-bottom: 3px !important; font-weight: 700 !important; }
.activity-body p { font-size: 12.5px !important; color: #cbd5e1 !important; margin: 0 0 6px 0 !important; line-height: 1.5 !important; }
.activity-body small { font-size: 11px !important; color: #64748b !important; }

.btn-view-all {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    padding: 9px 16px !important;
    background: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    border-radius: 8px !important;
    font-size: 12.5px !important; font-weight: 700 !important;
    text-decoration: none !important;
    transition: all 0.2s !important; white-space: nowrap !important;
}
.btn-view-all:hover { background: rgba(56, 189, 248, 0.25) !important; transform: translateY(-1px) !important; }

/* ═══ EMPTY STATE ═══ */
.empty-state { text-align: center !important; padding: 48px 24px !important; }
.empty-icon { font-size: 52px !important; margin-bottom: 12px !important; opacity: 0.6 !important; }
.empty-state h3 { font-size: 17px !important; color: #f1f5f9 !important; margin: 0 0 6px 0 !important; font-weight: 700 !important; }
.empty-state p { color: #94a3b8 !important; font-size: 13px !important; margin: 0 !important; }

/* ═══ TOAST ═══ */
.toast {
    position: fixed !important;
    bottom: 32px !important; right: 32px !important;
    padding: 14px 22px !important;
    border-radius: 12px !important;
    font-size: 13.5px !important; font-weight: 600 !important;
    color: #fff !important;
    transform: translateY(20px) !important; opacity: 0 !important;
    transition: all 0.3s ease !important;
    z-index: 99999 !important; max-width: 400px !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5) !important;
    pointer-events: none !important;
}
.toast.show { transform: translateY(0) !important; opacity: 1 !important; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.toast.error   { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }

/* ═══ RESPONSIVE ═══ */
@media (max-width: 900px) {
    .profile-grid { grid-template-columns: 1fr !important; gap: 24px !important; }
    .avatar-section { max-width: 300px !important; margin: 0 auto !important; }
}
@media (max-width: 768px) {
    .profile-page .main-content {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 16px !important;
        padding-top: 72px !important;
    }
    .profile-page .glass-card { padding: 20px !important; }
    .profile-hero { flex-direction: column !important; align-items: flex-start !important; }
    .profile-hero h1 { font-size: 22px !important; }
    .btn-hero-secondary { width: 100% !important; justify-content: center !important; }
    .form-row-2, .form-row-3 { grid-template-columns: 1fr !important; }
    .form-actions { flex-direction: column !important; }
    .btn-save, .btn-cancel { width: 100% !important; justify-content: center !important; }
    .avatar-preview { width: 140px !important; height: 140px !important; }
    .avatar-placeholder { font-size: 48px !important; }
    .toast { left: 16px !important; right: 16px !important; bottom: 16px !important; max-width: none !important; }
}
</style>