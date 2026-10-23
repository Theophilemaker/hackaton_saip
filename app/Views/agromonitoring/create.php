<?php $allDistricts = $all_districts ?? []; ?>
<div class="dashboard-layout agro-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card hero-card">
            <div class="hero-left">
                <a href="<?= BASE_URL ?>/agromonitoring" class="back-link">← Back to Agromonitoring</a>
                <h1>🛰️ Register New Field</h1>
                <p>Enter your field details. Satellites will begin tracking vegetation indices within 24 hours.</p>
            </div>
        </section>

        <section class="glass-card">
            <form id="fieldForm" onsubmit="event.preventDefault(); submitField();">
                <div class="form-grid-2">
                    <div class="fg">
                        <label>Field Name *</label>
                        <input type="text" id="name" required maxlength="150"
                               placeholder="e.g., North Maize Plot">
                    </div>
                    <div class="fg">
                        <label>Crop Grown</label>
                        <input type="text" id="crop_name" maxlength="100"
                               placeholder="e.g., Maize, Beans">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="fg">
                        <label>District *</label>
                        <select id="district" required>
                            <?php foreach ($allDistricts as $d): ?>
                                <option value="<?= e($d) ?>"><?= e($d) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="fg">
                        <label>Area (hectares) *</label>
                        <input type="number" id="area_ha" required min="0.01" step="0.01" placeholder="1.5">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="fg">
                        <label>Latitude (optional)</label>
                        <input type="number" id="lat" step="0.000001" placeholder="-2.6077">
                    </div>
                    <div class="fg">
                        <label>Longitude (optional)</label>
                        <input type="number" id="lng" step="0.000001" placeholder="29.7420">
                    </div>
                </div>

                <div class="fg">
                    <label>Notes</label>
                    <textarea id="notes" rows="3" maxlength="1000"
                              placeholder="Soil type, irrigation, planting dates…"></textarea>
                </div>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/agromonitoring" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary" id="saveBtn">💾 Register Field</button>
                </div>
            </form>
        </section>
    </main>
</div>

<style>
.agro-page.dashboard-layout { display: flex !important; width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; }
.agro-page .main-content {
    flex: 1 !important;
    width: calc(100% - 260px) !important;
    padding: 24px 32px 60px !important;
    box-sizing: border-box !important;
}
.agro-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 24px !important;
    margin-bottom: 20px !important;
}
.hero-card {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08)) !important;
    padding: 26px 30px !important;
}
.back-link {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    color: #94a3b8 !important; text-decoration: none !important;
    font-size: 12.5px !important; font-weight: 600 !important;
    margin-bottom: 12px !important;
}
.back-link:hover { color: #34d399 !important; }
.hero-card h1 { font-size: 24px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 6px !important; }
.hero-card p { color: #94a3b8 !important; font-size: 13.5px !important; margin: 0 !important; }

.form-grid-2 { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 16px !important; margin-bottom: 16px !important; }
.fg { display: flex !important; flex-direction: column !important; gap: 6px !important; margin-bottom: 16px !important; }
.fg label {
    font-size: 11px !important; font-weight: 800 !important;
    color: #94a3b8 !important; text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
}
.fg input, .fg select, .fg textarea {
    padding: 12px 15px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 11px !important;
    color: #f1f5f9 !important; font-size: 14px !important;
    font-family: inherit !important; outline: none !important;
    box-sizing: border-box !important; width: 100% !important;
    transition: all 0.2s !important;
}
.fg input:focus, .fg select:focus, .fg textarea:focus {
    border-color: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
}
.fg select option { background: #1e293b !important; color: #f1f5f9 !important; }

.form-actions {
    display: flex !important; justify-content: flex-end !important;
    gap: 10px !important; padding-top: 18px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
}
.btn-primary {
    display: inline-flex !important; align-items: center !important; gap: 8px !important;
    padding: 12px 26px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important; border: none !important;
    border-radius: 10px !important;
    font-size: 13.5px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35) !important;
    transition: all 0.2s !important; text-decoration: none !important;
}
.btn-primary:hover:not(:disabled) { transform: translateY(-2px) !important; box-shadow: 0 6px 22px rgba(16, 185, 129, 0.55) !important; }
.btn-primary:disabled { opacity: 0.6 !important; cursor: not-allowed !important; }
.btn-secondary {
    display: inline-flex !important; align-items: center !important; gap: 8px !important;
    padding: 12px 22px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 13.5px !important; font-weight: 700 !important;
    text-decoration: none !important;
    cursor: pointer !important; font-family: inherit !important;
}
.btn-secondary:hover { background: rgba(255, 255, 255, 0.1) !important; }

.toast {
    position: fixed !important; bottom: 28px !important; right: 28px !important;
    padding: 14px 22px !important; border-radius: 12px !important;
    font-size: 13.5px !important; font-weight: 600 !important;
    color: #fff !important;
    transform: translateY(20px) !important; opacity: 0 !important;
    transition: all 0.3s !important; z-index: 99999 !important;
    pointer-events: none !important;
}
.toast.show { transform: translateY(0) !important; opacity: 1 !important; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.toast.error   { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }

@media (max-width: 900px) {
    .agro-page .main-content {
        width: 100% !important; max-width: 100% !important;
        padding: 20px !important; padding-top: 72px !important;
    }
}
@media (max-width: 640px) {
    .form-grid-2 { grid-template-columns: 1fr !important; }
    .form-actions { flex-direction: column-reverse !important; }
    .form-actions .btn-primary,
    .form-actions .btn-secondary { width: 100% !important; justify-content: center !important; }
}
</style>

<script>
(function () {
    var BASE = '<?= BASE_URL ?>';

    window.submitField = async function () {
        var btn = document.getElementById('saveBtn');
        var orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ Registering…';

        var payload = new URLSearchParams();
        payload.append('name',      document.getElementById('name').value.trim());
        payload.append('crop_name', document.getElementById('crop_name').value.trim());
        payload.append('district',  document.getElementById('district').value);
        payload.append('area_ha',   document.getElementById('area_ha').value);
        payload.append('lat',       document.getElementById('lat').value);
        payload.append('lng',       document.getElementById('lng').value);
        payload.append('notes',     document.getElementById('notes').value.trim());

        try {
            var res = await fetch(BASE + '/agromonitoring/store', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: payload.toString(),
                credentials: 'same-origin'
            });
            var data = await res.json();

            if (data.success) {
                toast('✅ Field registered', 'success');
                setTimeout(function () { window.location.href = data.redirect; }, 700);
            } else {
                toast(data.error || 'Save failed', 'error');
                btn.disabled = false;
                btn.innerHTML = orig;
            }
        } catch (e) {
            toast('Error: ' + e.message, 'error');
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    };

    function toast(msg, type) {
        var t = document.createElement('div');
        t.className = 'toast ' + (type || 'info');
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { t.classList.add('show'); }, 10);
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { t.remove(); }, 300);
        }, 2600);
    }
})();
</script>