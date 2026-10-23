<div class="dashboard-layout soil-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="main-content">

            <!-- HERO -->
            <section class="glass-card soil-hero">
                <div>
                    <h1>🪨 Soil Health & Lime Advisor</h1>
                    <p>Combat soil acidity and unlock up to <strong>+35% yield</strong> — powered by RAB & NISR data.</p>
                    <div class="nist-badge">
                        <span class="ds-pill">📊 NISR SAS 2025</span>
                        <span class="ds-pill">🧪 RAB Soil Survey</span>
                    </div>
                </div>
            </section>

            <!-- FORM + RESULT GRID -->
            <div class="soil-grid">
                
                <!-- LEFT: INPUT FORM -->
                <section class="glass-card">
                    <h2 class="sec-title">📝 Analyze Your Soil</h2>
                    <form id="soilForm" class="soil-form">
                        
                        <div class="fg">
                            <label>Current Soil pH</label>
                            <input type="number" name="soil_ph" value="5.2" step="0.1" min="3" max="9" required>
                            <small>86% of Rwanda's soils are below pH 5.5 (acidic).</small>
                        </div>

                        <div class="fg">
                            <label>Main Crop</label>
                            <select name="crop" required>
                                <option value="Maize">Maize</option>
                                <option value="Beans">Beans</option>
                                <option value="Irish Potato">Irish Potato</option>
                                <option value="Rice">Rice</option>
                                <option value="Coffee">Coffee</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label>Plot Area (hectares)</label>
                            <input type="number" name="area_ha" value="1" step="0.1" min="0.1" max="50" required>
                        </div>

                        <button type="submit" class="btn-primary" id="btnSubmit">
                            🧪 Calculate Lime Requirement
                        </button>
                    </form>
                </section>

                <!-- RIGHT: RESULT PANEL -->
                <section class="glass-card" id="resultCard" style="display:none;">
                    <h2 class="sec-title">📊 Analysis Results</h2>
                    <div id="resultContent"></div>
                </section>

            </div>

        </main>
    </div>
</div>

<style>
.soil-page.dashboard-layout { display: block !important; width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; }
.soil-page .main-wrap { margin-left: 260px !important; width: calc(100% - 260px) !important; min-height: 100vh !important; box-sizing: border-box !important; }
.soil-page .main-content { padding: 20px 28px 60px !important; display: flex !important; flex-direction: column !important; gap: 18px !important; box-sizing: border-box !important; }
.soil-page .glass-card { background: rgba(30,41,59,.7) !important; backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,.08) !important; border-radius: 20px !important; padding: 24px !important; box-sizing: border-box !important; }

.soil-hero { background: linear-gradient(135deg, rgba(245,158,11,.15), rgba(217,119,6,.08)) !important; }
.soil-hero h1 { margin: 0 0 8px; font-size: 26px; font-weight: 800; color: #f1f5f9; }
.soil-hero p { margin: 0 0 16px; color: #94a3b8; font-size: 14px; }
.soil-hero strong { color: #fbbf24; }
.nist-badge { display: flex; gap: 8px; flex-wrap: wrap; }
.ds-pill { background: rgba(255,255,255,.1); color: #cbd5e1; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }

.soil-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; align-items: start; }
@media (max-width: 900px) { .soil-grid { grid-template-columns: 1fr; } }

.sec-title { margin: 0 0 20px; font-size: 16px; font-weight: 800; color: #f1f5f9; }

.soil-form { display: flex; flex-direction: column; gap: 16px; }
.fg { display: flex; flex-direction: column; gap: 6px; }
.fg label { font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
.fg input, .fg select { padding: 12px 15px; background: rgba(0,0,0,.3); border: 1px solid rgba(255,255,255,.12); border-radius: 11px; color: #f1f5f9; font-size: 14px; font-family: inherit; outline: none; }
.fg input:focus, .fg select:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.15); }
.fg select option { background: #1e293b; }
.fg small { font-size: 11px; color: #64748b; font-style: italic; }

.btn-primary { padding: 14px 24px; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit; margin-top: 10px; transition: transform .15s; box-shadow: 0 4px 15px rgba(245,158,11,.3); }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(245,158,11,.5); }
.btn-primary:disabled { opacity: .5; cursor: not-allowed; }

/* Result Styles */
.alert { padding: 16px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; font-size: 14px; line-height: 1.5; }
.alert-warning { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); border-left: 4px solid #ef4444; }
.alert-success { background: rgba(16, 185, 129, 0.15); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.3); border-left: 4px solid #10b981; }

.kpi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
.kpi-card { background: rgba(15,23,42,.6); border: 1px solid rgba(255,255,255,.06); border-radius: 12px; padding: 16px; text-align: center; }
.kpi-label { font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 4px; }
.kpi-value { font-size: 20px; font-weight: 800; color: #f1f5f9; }
.kpi-value.gold { color: #fbbf24; }
.kpi-value.green { color: #34d399; }
</style>

<script>
document.getElementById('soilForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerHTML = '⏳ Calculating...';

    const formData = new FormData(this);
    const card = document.getElementById('resultCard');
    const content = document.getElementById('resultContent');

    try {
        const res = await fetch('<?= BASE_URL ?>/soil/analyze', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        });
        const data = await res.json();

        if (!data.success) throw new Error(data.error || 'Analysis failed');

        card.style.display = 'block';
        
        let alertClass = data.is_acidic ? 'alert-warning' : 'alert-success';
        
        let html = `
            <div class="alert ${alertClass}">
                ${data.is_acidic ? '⚠️' : '✅'} ${data.message}
            </div>
        `;

        if (data.is_acidic) {
            html += `
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-label">Lime Required</div>
                        <div class="kpi-value gold">${data.total_lime_kg.toLocaleString()} kg</div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:4px;">(${data.lime_per_ha} kg/ha)</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Estimated Cost</div>
                        <div class="kpi-value">${data.cost_rwf.toLocaleString()} RWF</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Expected Yield Gain</div>
                        <div class="kpi-value green">+${data.yield_gain_pct}%</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Return on Investment</div>
                        <div class="kpi-value green">${data.roi_multiplier}x</div>
                    </div>
                </div>
                <p style="color:#cbd5e1;font-size:13.5px;line-height:1.6;">
                    <strong>Why?</strong> Applying lime neutralizes soil acidity, unlocking nutrients like Phosphorus that are currently locked up in your soil. Based on RAB trials, this can increase your yield from ${data.current_ph} pH to the optimal level.
                </p>
            `;
        } else {
            html += `
                <p style="color:#cbd5e1;font-size:14px;line-height:1.6;">
                    Your soil pH is currently optimal for most crops. Maintain this by adding organic matter (compost or manure) regularly to prevent future acidification.
                </p>
            `;
        }

        content.innerHTML = html;
        card.scrollIntoView({ behavior: 'smooth', block: 'start' });

    } catch (err) {
        alert(err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '🧪 Calculate Lime Requirement';
    }
});
</script>