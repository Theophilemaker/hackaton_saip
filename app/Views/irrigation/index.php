<div class="dashboard-layout irrigation-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="main-content">

            <!-- HERO -->
            <section class="glass-card irrigation-hero">
                <div>
                    <h1>💧 Smart Irrigation Advisor</h1>
                    <p>Optimize water usage and boost yields with precision scheduling — powered by FAO-56 & NISR climate data.</p>
                    <div class="nist-badge">
                        <span class="ds-pill">🌦️ Live Weather Sync</span>
                        <span class="ds-pill">📊 NISR SAS 2025</span>
                    </div>
                </div>
            </section>

            <!-- FORM + RESULT GRID -->
            <div class="irrigation-grid">
                
                <!-- LEFT: INPUT FORM -->
                <section class="glass-card">
                    <h2 class="sec-title">🌱 Setup Your Irrigation</h2>
                    <form id="irrigationForm" class="irrigation-form">
                        
                        <div class="fg">
                            <label>Crop Type</label>
                            <select name="crop" required>
                                <option value="Maize">Maize</option>
                                <option value="Beans">Beans</option>
                                <option value="Rice">Rice</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label>Growth Stage</label>
                            <select name="stage" required>
                                <option value="seedling">Seedling (0-3 weeks)</option>
                                <option value="vegetative" selected>Vegetative (4-8 weeks)</option>
                                <option value="flowering">Flowering / Tasseling</option>
                                <option value="maturity">Maturity</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label>Plot Area (hectares)</label>
                            <input type="number" name="area_ha" value="1" step="0.1" min="0.1" max="50" required>
                        </div>

                        <button type="submit" class="btn-primary" id="btnSubmit">
                            💧 Calculate Water Needs
                        </button>
                    </form>
                </section>

                <!-- RIGHT: RESULT PANEL -->
                <section class="glass-card" id="resultCard" style="display:none;">
                    <h2 class="sec-title">📊 Irrigation Schedule</h2>
                    <div id="resultContent"></div>
                </section>

            </div>

        </main>
    </div>
</div>

<style>
.irrigation-page.dashboard-layout { display: block !important; width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; }
.irrigation-page .main-wrap { margin-left: 260px !important; width: calc(100% - 260px) !important; min-height: 100vh !important; box-sizing: border-box !important; }
.irrigation-page .main-content { padding: 20px 28px 60px !important; display: flex !important; flex-direction: column !important; gap: 18px !important; box-sizing: border-box !important; }
.irrigation-page .glass-card { background: rgba(30,41,59,.7) !important; backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,.08) !important; border-radius: 20px !important; padding: 24px !important; box-sizing: border-box !important; }

.irrigation-hero { background: linear-gradient(135deg, rgba(56,189,248,.15), rgba(2,132,199,.08)) !important; }
.irrigation-hero h1 { margin: 0 0 8px; font-size: 26px; font-weight: 800; color: #f1f5f9; }
.irrigation-hero p { margin: 0 0 16px; color: #94a3b8; font-size: 14px; }
.nist-badge { display: flex; gap: 8px; flex-wrap: wrap; }
.ds-pill { background: rgba(255,255,255,.1); color: #cbd5e1; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }

.irrigation-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; align-items: start; }
@media (max-width: 900px) { .irrigation-grid { grid-template-columns: 1fr; } }

.sec-title { margin: 0 0 20px; font-size: 16px; font-weight: 800; color: #f1f5f9; }

.irrigation-form { display: flex; flex-direction: column; gap: 16px; }
.fg { display: flex; flex-direction: column; gap: 6px; }
.fg label { font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
.fg input, .fg select { padding: 12px 15px; background: rgba(0,0,0,.3); border: 1px solid rgba(255,255,255,.12); border-radius: 11px; color: #f1f5f9; font-size: 14px; font-family: inherit; outline: none; }
.fg input:focus, .fg select:focus { border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56,189,248,.15); }
.fg select option { background: #1e293b; }

.btn-primary { padding: 14px 24px; background: linear-gradient(135deg, #38bdf8, #0284c7); color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit; margin-top: 10px; transition: transform .15s; box-shadow: 0 4px 15px rgba(56,189,248,.3); }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(56,189,248,.5); }
.btn-primary:disabled { opacity: .5; cursor: not-allowed; }

/* Result Styles */
.kpi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
.kpi-card { background: rgba(15,23,42,.6); border: 1px solid rgba(255,255,255,.06); border-radius: 12px; padding: 16px; text-align: center; }
.kpi-label { font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 4px; }
.kpi-value { font-size: 20px; font-weight: 800; color: #f1f5f9; }
.kpi-value.blue { color: #38bdf8; }
.kpi-value.green { color: #34d399; }

.action-box { background: rgba(56,189,248,.1); border-left: 4px solid #38bdf8; padding: 16px; border-radius: 8px; margin-bottom: 16px; }
.action-box h4 { margin: 0 0 6px; color: #38bdf8; font-size: 14px; }
.action-box p { margin: 0; color: #f1f5f9; font-weight: 700; font-size: 16px; }

.tip-box { background: rgba(16,185,129,.1); border-left: 4px solid #10b981; padding: 12px 16px; border-radius: 8px; font-size: 13px; color: #cbd5e1; display: flex; gap: 8px; }
</style>

<script>
document.getElementById('irrigationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerHTML = '⏳ Calculating...';

    const formData = new FormData(this);
    const card = document.getElementById('resultCard');
    const content = document.getElementById('resultContent');

    try {
        const res = await fetch('<?= BASE_URL ?>/irrigation/schedule', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        });
        const data = await res.json();

        if (!data.success) throw new Error(data.error || 'Calculation failed');

        card.style.display = 'block';
        
        let html = `
            <div class="action-box">
                <h4>Recommended Action for ${data.crop} (${data.stage})</h4>
                <p>${data.action}</p>
            </div>

            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-label">Crop Water Needs</div>
                    <div class="kpi-value blue">${data.mm_needed} mm/day</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Expected Rain</div>
                    <div class="kpi-value">${data.rain_expected} mm</div>
                </div>
                <div class="kpi-card" style="grid-column: 1 / -1; background: rgba(56,189,248,.1); border-color: rgba(56,189,248,.3);">
                    <div class="kpi-label">Total Irrigation Volume Needed</div>
                    <div class="kpi-value blue">${data.total_liters.toLocaleString()} Liters</div>
                    <div style="font-size:12px;color:#94a3b8;margin-top:4px;">For your entire plot</div>
                </div>
            </div>

            <div class="tip-box">
                <span>💡</span>
                <div>${data.tip}</div>
            </div>
        `;

        content.innerHTML = html;
        card.scrollIntoView({ behavior: 'smooth', block: 'start' });

    } catch (err) {
        alert(err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '💧 Calculate Water Needs';
    }
});
</script>