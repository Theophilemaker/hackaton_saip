<div class="dashboard-layout advice-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card advice-hero">
            <div>
                <span class="hero-tag">⚙️ Setup</span>
                <h1>My Farmer Profile</h1>
                <p>Tell us about your farm so we can generate accurate recommendations.</p>
            </div>
            <a href="<?= BASE_URL ?>/advice" class="btn-hero-secondary">← Back</a>
        </section>

        <form method="POST" action="<?= BASE_URL ?>/advice/profile">
            <?= \App\Core\Security::csrfField() ?>

            <section class="glass-card">
                <h3 class="section-title">📍 Location</h3>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>District *</label>
                        <select name="district" required>
                            <option value="">— Select —</option>
                            <?php 
                            $districts = ['Bugesera','Burera','Gakenke','Gasabo','Gatsibo','Gicumbi','Gisagara','Huye','Kamonyi','Karongi','Kayonza','Kicukiro','Kirehe','Muhanga','Musanze','Ngoma','Ngororero','Nyabihu','Nyagatare','Nyamagabe','Nyamasheke','Nyanza','Nyarugenge','Nyaruguru','Rubavu','Ruhango','Rulindo','Rusizi','Rutsiro','Rwamagana'];
                            foreach ($districts as $d): ?>
                                <option value="<?= $d ?>" <?= ($data['profile']['district'] ?? '') === $d ? 'selected' : '' ?>><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sector</label>
                        <input type="text" name="sector" maxlength="100" 
                               value="<?= e($data['profile']['sector'] ?? '') ?>"
                               placeholder="e.g., Busogo">
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Cell</label>
                        <input type="text" name="cell" maxlength="100"
                               value="<?= e($data['profile']['cell'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Province</label>
                        <select name="province">
                            <option value="">— Select —</option>
                            <?php foreach (['Kigali','Northern','Southern','Eastern','Western'] as $p): ?>
                                <option value="<?= $p ?>" <?= ($data['profile']['province'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </section>

            <section class="glass-card">
                <h3 class="section-title">🌾 Farm Details</h3>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Land Size (hectares) *</label>
                        <input type="number" name="land_size_ha" step="0.01" min="0.01" max="1000" required
                               value="<?= e($data['profile']['land_size_ha'] ?? '1.00') ?>">
                    </div>
                    <div class="form-group">
                        <label>Soil Type</label>
                        <select name="soil_type">
                            <?php foreach (['loam','clay','sandy','silt','peat'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($data['profile']['soil_type'] ?? 'loam') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Primary Crops <small style="color:#64748b;">(comma separated)</small></label>
                    <?php 
                    $cropsArr = json_decode($data['profile']['primary_crops'] ?? '[]', true) ?: [];
                    ?>
                    <input type="text" name="primary_crops" 
                           placeholder="maize, beans, potatoes"
                           value="<?= e(implode(', ', $cropsArr)) ?>">
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Farming Experience (years)</label>
                        <input type="number" name="farming_experience_years" min="0" max="80"
                               value="<?= e($data['profile']['farming_experience_years'] ?? 0) ?>">
                    </div>
                    <div class="form-group">
                        <label>Irrigation</label>
                        <label class="toggle-card" style="margin-top:8px;">
                            <input type="checkbox" name="has_irrigation" value="1"
                                   <?= !empty($data['profile']['has_irrigation']) ? 'checked' : '' ?>>
                            <span class="toggle-switch"></span>
                            <span>I have irrigation</span>
                        </label>
                    </div>
                </div>
            </section>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>/advice" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">💾 Save & Continue</button>
            </div>
        </form>
    </main>
</div>

<style>
.advice-page.dashboard-layout { display: flex !important; width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; }
.advice-page .main-content { flex: 1 !important; margin-left: 260px !important; padding: 32px !important; box-sizing: border-box !important; }
.advice-page .glass-card { background: rgba(30,41,59,0.65) !important; backdrop-filter: blur(16px) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 20px !important; padding: 28px !important; margin-bottom: 24px !important; }

.advice-hero { background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(56,189,248,0.08)) !important; display: flex !important; justify-content: space-between !important; align-items: center !important; gap: 24px !important; flex-wrap: wrap !important; }
.hero-tag { display: inline-block !important; padding: 5px 14px !important; background: rgba(16,185,129,0.15) !important; color: #34d399 !important; border: 1px solid rgba(16,185,129,0.3) !important; border-radius: 999px !important; font-size: 11px !important; font-weight: 700 !important; text-transform: uppercase !important; margin-bottom: 12px !important; }
.advice-hero h1 { font-size: 28px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.advice-hero p { color: #94a3b8 !important; margin: 0 !important; font-size: 14px !important; }
.btn-hero-secondary { padding: 12px 22px !important; background: rgba(255,255,255,0.05) !important; color: #f1f5f9 !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; font-weight: 700 !important; text-decoration: none !important; }

.section-title { font-size: 12px !important; font-weight: 700 !important; color: #38bdf8 !important; text-transform: uppercase !important; letter-spacing: 1.2px !important; margin: 0 0 20px 0 !important; padding-bottom: 10px !important; border-bottom: 1px solid rgba(255,255,255,0.08) !important; }

.form-row-2 { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 16px !important; margin-bottom: 16px !important; }
.form-group { display: flex !important; flex-direction: column !important; gap: 6px !important; margin-bottom: 16px !important; }
.form-group label { font-size: 11px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.6px !important; }
.form-group input, .form-group select { padding: 12px 14px !important; background: rgba(0,0,0,0.35) !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; color: #f1f5f9 !important; font-size: 14px !important; outline: none !important; box-sizing: border-box !important; font-family: inherit !important; }
.form-group input:focus, .form-group select:focus { border-color: #38bdf8 !important; box-shadow: 0 0 0 3px rgba(56,189,248,0.2) !important; }
.form-group select option { background: #1e293b !important; }

.toggle-card { display: flex !important; align-items: center !important; gap: 12px !important; padding: 12px 16px !important; background: rgba(0,0,0,0.2) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 10px !important; cursor: pointer !important; }
.toggle-card input { position: absolute !important; opacity: 0 !important; }
.toggle-switch { position: relative !important; width: 38px !important; height: 22px !important; background: rgba(255,255,255,0.1) !important; border-radius: 999px !important; transition: 0.2s !important; flex-shrink: 0 !important; }
.toggle-switch::after { content: '' !important; position: absolute !important; top: 2px !important; left: 2px !important; width: 18px !important; height: 18px !important; background: #94a3b8 !important; border-radius: 50% !important; transition: 0.2s !important; }
.toggle-card input:checked + .toggle-switch { background: rgba(56,189,248,0.4) !important; }
.toggle-card input:checked + .toggle-switch::after { left: 18px !important; background: #38bdf8 !important; }
.toggle-card span:last-child { font-size: 13px !important; color: #cbd5e1 !important; }

.form-actions { display: flex !important; justify-content: flex-end !important; gap: 12px !important; margin-top: 20px !important; }
.btn-primary, .btn-secondary { display: inline-flex !important; align-items: center !important; gap: 8px !important; padding: 13px 28px !important; border-radius: 10px !important; font-weight: 700 !important; text-decoration: none !important; cursor: pointer !important; border: none !important; font-size: 14px !important; font-family: inherit !important; }
.btn-primary { background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; box-shadow: 0 4px 15px rgba(16,185,129,0.3) !important; }
.btn-secondary { background: rgba(255,255,255,0.05) !important; color: #cbd5e1 !important; border: 1px solid rgba(255,255,255,0.12) !important; }

@media (max-width: 768px) {
    .advice-page .main-content { margin-left: 0 !important; padding: 16px !important; padding-top: 72px !important; }
    .form-row-2 { grid-template-columns: 1fr !important; }
}
</style>