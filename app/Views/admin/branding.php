<div class="dashboard-layout admin-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card hero-card">
            <div class="hero-content">
                <span class="hero-tag">Customization</span>
                <h1>🎨 System Branding</h1>
                <p>Customize the look, feel, and identity of your platform — logo, colors, contact info, and footer text.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin" class="btn-hero-secondary">← Back</a>
        </section>

        <form id="brandingForm" enctype="multipart/form-data" onsubmit="event.preventDefault(); saveBranding();">
            <section class="glass-card">
                <div class="card-header">
                    <div><h3>🖼️ Logo & Favicon</h3><p class="card-subtitle">Upload your brand assets</p></div>
                </div>

                <div class="uploads-grid">
                    <div class="upload-box">
                        <label class="upload-label">Site Logo</label>
                        <div class="upload-preview logo-preview" id="logoPreview">
                            <?php if (!empty($data['settings']['site_logo'])): ?>
                                <img src="<?= BASE_URL ?>/<?= e($data['settings']['site_logo']) ?>" alt="Logo">
                            <?php else: ?>
                                <span class="upload-placeholder">🌱</span>
                            <?php endif; ?>
                        </div>
                        <label class="upload-btn">
                            📁 Upload Logo
                            <input type="file" name="logo" accept="image/*" onchange="previewFile(this, 'logoPreview')" hidden>
                        </label>
                        <small>PNG, JPG, SVG · Recommended 200×60px</small>
                    </div>

                    <div class="upload-box">
                        <label class="upload-label">Favicon</label>
                        <div class="upload-preview favicon-preview" id="faviconPreview">
                            <?php if (!empty($data['settings']['site_favicon'])): ?>
                                <img src="<?= BASE_URL ?>/<?= e($data['settings']['site_favicon']) ?>" alt="Favicon">
                            <?php else: ?>
                                <span class="upload-placeholder">⭐</span>
                            <?php endif; ?>
                        </div>
                        <label class="upload-btn">
                            📁 Upload Favicon
                            <input type="file" name="favicon" accept="image/*" onchange="previewFile(this, 'faviconPreview')" hidden>
                        </label>
                        <small>PNG, ICO, SVG · 32×32px or 64×64px</small>
                    </div>
                </div>
            </section>

            <section class="glass-card">
                <div class="card-header">
                    <div><h3>📝 Identity</h3><p class="card-subtitle">Site name, tagline and description</p></div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Site Name *</label>
                        <input type="text" name="site_name" maxlength="100" required
                               value="<?= e($data['settings']['site_name'] ?? 'SAIP') ?>">
                    </div>
                    <div class="form-group">
                        <label>Tagline</label>
                        <input type="text" name="site_tagline" maxlength="200"
                               value="<?= e($data['settings']['site_tagline'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Contact Email</label>
                        <input type="email" name="contact_email" maxlength="150"
                               value="<?= e($data['settings']['contact_email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="text" name="contact_phone" maxlength="50"
                               value="<?= e($data['settings']['contact_phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Footer Text</label>
                    <input type="text" name="footer_text" maxlength="255"
                           value="<?= e($data['settings']['footer_text'] ?? '') ?>">
                </div>
            </section>

            <section class="glass-card">
                <div class="card-header">
                    <div><h3>🎨 Theme Colors</h3><p class="card-subtitle">Pick your brand's primary and accent colors</p></div>
                </div>

                <div class="color-grid">
                    <div class="color-picker">
                        <label>Primary Color</label>
                        <div class="color-input-group">
                            <input type="color" name="primary_color" id="primaryColor"
                                   value="<?= e($data['settings']['primary_color'] ?? '#10b981') ?>"
                                   onchange="updatePreview()">
                            <input type="text" id="primaryHex"
                                   value="<?= e($data['settings']['primary_color'] ?? '#10b981') ?>"
                                   onchange="document.getElementById('primaryColor').value=this.value; updatePreview()" maxlength="7">
                        </div>
                    </div>

                    <div class="color-picker">
                        <label>Secondary Color</label>
                        <div class="color-input-group">
                            <input type="color" name="secondary_color" id="secondaryColor"
                                   value="<?= e($data['settings']['secondary_color'] ?? '#38bdf8') ?>"
                                   onchange="updatePreview()">
                            <input type="text" id="secondaryHex"
                                   value="<?= e($data['settings']['secondary_color'] ?? '#38bdf8') ?>"
                                   onchange="document.getElementById('secondaryColor').value=this.value; updatePreview()" maxlength="7">
                        </div>
                    </div>
                </div>

                <div class="live-preview" id="livePreview">
                    <div class="preview-header">
                        <div class="preview-logo" id="previewLogo">🌱</div>
                        <div class="preview-name" id="previewName"><?= e($data['settings']['site_name'] ?? 'SAIP') ?></div>
                    </div>
                    <div class="preview-body">
                        <button class="preview-btn" id="previewBtn">Sample Button</button>
                    </div>
                </div>
            </section>

            <div class="action-bar">
                <a href="<?= BASE_URL ?>/admin" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" id="saveBtn">💾 Save Branding</button>
            </div>
        </form>
    </main>
</div>

<script>
function previewFile(input, targetId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById(targetId).innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function updatePreview() {
    const primary = document.getElementById('primaryColor').value;
    const secondary = document.getElementById('secondaryColor').value;
    document.getElementById('primaryHex').value = primary;
    document.getElementById('secondaryHex').value = secondary;
    document.getElementById('previewBtn').style.background = `linear-gradient(135deg, ${primary}, ${secondary})`;
}

async function saveBranding() {
    const btn = document.getElementById('saveBtn');
    btn.disabled = true; btn.innerHTML = '⏳ Saving...';
    const fd = new FormData(document.getElementById('brandingForm'));

    try {
        const res = await fetch('<?= BASE_URL ?>/admin/saveBranding', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            showToast(data.message || 'Saved!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed', 'error');
            btn.disabled = false; btn.innerHTML = '💾 Save Branding';
        }
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
        btn.disabled = false; btn.innerHTML = '💾 Save Branding';
    }
}

function showToast(msg, type) {
    const t = document.createElement('div');
    t.className = 'toast ' + type; t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 2600);
}

document.addEventListener('DOMContentLoaded', updatePreview);
</script>

<style>
.admin-page.dashboard-layout { display: flex !important; width: 100vw !important; min-height: 100vh !important; background-color: #0b1120 !important; }
.admin-page .main-content { flex: 1 !important; width: calc(100% - 250px) !important; padding: 32px !important; box-sizing: border-box !important; }
.admin-page .glass-card { background: rgba(30,41,59,0.65) !important; backdrop-filter: blur(16px) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 20px !important; padding: 28px !important; margin-bottom: 24px !important; }

.hero-card { background: linear-gradient(135deg, rgba(244,114,182,0.12), rgba(167,139,250,0.08)) !important; display: flex !important; justify-content: space-between !important; align-items: center !important; gap: 24px !important; flex-wrap: wrap !important; }
.hero-content { flex: 1 !important; min-width: 280px !important; }
.hero-tag { display: inline-block !important; padding: 5px 14px !important; background: rgba(244,114,182,0.15) !important; color: #f472b6 !important; border: 1px solid rgba(244,114,182,0.3) !important; border-radius: 999px !important; font-size: 11px !important; font-weight: 700 !important; letter-spacing: 1px !important; text-transform: uppercase !important; margin-bottom: 12px !important; }
.hero-card h1 { font-size: 28px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.hero-card p { color: #94a3b8 !important; font-size: 14px !important; margin: 0 !important; max-width: 620px !important; }
.btn-hero-secondary { display: inline-flex !important; align-items: center !important; gap: 8px !important; padding: 13px 24px !important; background: rgba(255,255,255,0.05) !important; color: #f1f5f9 !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; font-weight: 700 !important; text-decoration: none !important; font-size: 13.5px !important; }

.card-header { display: flex !important; justify-content: space-between !important; align-items: center !important; margin-bottom: 20px !important; padding-bottom: 14px !important; border-bottom: 1px solid rgba(255,255,255,0.08) !important; }
.card-header h3 { font-size: 18px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 !important; }
.card-subtitle { font-size: 12.5px !important; color: #94a3b8 !important; margin: 4px 0 0 0 !important; }

.uploads-grid { display: grid !important; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)) !important; gap: 24px !important; }
.upload-box { display: flex !important; flex-direction: column !important; align-items: center !important; gap: 12px !important; padding: 24px !important; background: rgba(0,0,0,0.2) !important; border: 1px solid rgba(255,255,255,0.06) !important; border-radius: 14px !important; }
.upload-label { font-size: 12px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.8px !important; }
.upload-preview { background: rgba(0,0,0,0.4) !important; border: 2px dashed rgba(255,255,255,0.1) !important; border-radius: 12px !important; display: flex !important; align-items: center !important; justify-content: center !important; overflow: hidden !important; }
.logo-preview { width: 200px !important; height: 80px !important; }
.favicon-preview { width: 64px !important; height: 64px !important; }
.upload-preview img { width: 100% !important; height: 100% !important; object-fit: contain !important; }
.upload-placeholder { font-size: 40px !important; opacity: 0.5 !important; }
.upload-btn { display: inline-flex !important; align-items: center !important; gap: 6px !important; padding: 10px 18px !important; background: rgba(56,189,248,0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(56,189,248,0.3) !important; border-radius: 10px !important; font-size: 12.5px !important; font-weight: 700 !important; cursor: pointer !important; }
.upload-btn:hover { background: rgba(56,189,248,0.25) !important; }
.upload-box small { color: #64748b !important; font-size: 11px !important; text-align: center !important; }

.form-row-2 { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 16px !important; margin-bottom: 16px !important; }
.form-group { display: flex !important; flex-direction: column !important; gap: 6px !important; margin-bottom: 16px !important; }
.form-group label { font-size: 11px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.6px !important; }
.form-group input { padding: 12px 14px !important; background: rgba(0,0,0,0.35) !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; color: #f1f5f9 !important; font-size: 14px !important; outline: none !important; box-sizing: border-box !important; }
.form-group input:focus { border-color: #38bdf8 !important; box-shadow: 0 0 0 3px rgba(56,189,248,0.2) !important; }

.color-grid { display: grid !important; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)) !important; gap: 20px !important; margin-bottom: 24px !important; }
.color-picker label { display: block !important; font-size: 11px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.6px !important; margin-bottom: 8px !important; }
.color-input-group { display: flex !important; gap: 10px !important; align-items: center !important; }
.color-input-group input[type="color"] { width: 60px !important; height: 44px !important; border: 1px solid rgba(255,255,255,0.15) !important; border-radius: 10px !important; cursor: pointer !important; background: transparent !important; }
.color-input-group input[type="text"] { flex: 1 !important; padding: 12px 14px !important; background: rgba(0,0,0,0.35) !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; color: #f1f5f9 !important; font-family: 'Courier New', monospace !important; font-size: 13px !important; text-transform: uppercase !important; }

.live-preview { padding: 24px !important; background: linear-gradient(135deg, rgba(0,0,0,0.4), rgba(0,0,0,0.2)) !important; border: 1px solid rgba(255,255,255,0.06) !important; border-radius: 14px !important; text-align: center !important; }
.preview-header { display: flex !important; align-items: center !important; justify-content: center !important; gap: 12px !important; margin-bottom: 16px !important; }
.preview-logo { font-size: 32px !important; }
.preview-name { font-size: 22px !important; font-weight: 800 !important; color: #f1f5f9 !important; letter-spacing: -0.5px !important; }
.preview-btn { padding: 12px 32px !important; color: #fff !important; border: none !important; border-radius: 10px !important; font-size: 14px !important; font-weight: 700 !important; cursor: pointer !important; background: linear-gradient(135deg, #10b981, #38bdf8) !important; }

.action-bar { display: flex !important; justify-content: flex-end !important; gap: 12px !important; padding: 20px 0 !important; }
.btn-primary, .btn-secondary { display: inline-flex !important; align-items: center !important; gap: 8px !important; padding: 13px 28px !important; border-radius: 10px !important; font-size: 13.5px !important; font-weight: 700 !important; cursor: pointer !important; text-decoration: none !important; border: none !important; }
.btn-primary { background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; box-shadow: 0 4px 15px rgba(16,185,129,0.3) !important; }
.btn-primary:disabled { opacity: 0.5 !important; }
.btn-secondary { background: rgba(255,255,255,0.05) !important; color: #cbd5e1 !important; border: 1px solid rgba(255,255,255,0.12) !important; }

.toast { position: fixed !important; bottom: 32px !important; right: 32px !important; padding: 14px 22px !important; border-radius: 12px !important; font-size: 13.5px !important; font-weight: 600 !important; color: #fff !important; transform: translateY(20px) !important; opacity: 0 !important; transition: all 0.3s !important; z-index: 99999 !important; }
.toast.show { transform: translateY(0) !important; opacity: 1 !important; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.toast.error { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }

@media (max-width: 768px) {
    .admin-page .main-content { padding: 16px !important; }
    .form-row-2 { grid-template-columns: 1fr !important; }
    .action-bar { flex-direction: column !important; }
    .btn-primary, .btn-secondary { width: 100% !important; justify-content: center !important; }
    .toast { left: 16px !important; right: 16px !important; bottom: 16px !important; }
}
</style>