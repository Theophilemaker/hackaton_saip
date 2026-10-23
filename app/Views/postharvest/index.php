<div class="dashboard-layout postharvest-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="main-content">

            <!-- HERO -->
            <section class="glass-card postharvest-hero">
                <div>
                    <h1>📦 Post-Harvest Loss Predictor</h1>
                    <p>Rwanda loses up to <strong>44% of beans and 34% of tomatoes</strong> after harvest. Calculate your spoilage risk and save your crops.</p>
                    <div class="nist-badge">
                        <span class="ds-pill">📊 NISR Post-Harvest Data</span>
                        <span class="ds-pill">🌡️ Temperature & Humidity Model</span>
                    </div>
                </div>
            </section>

            <!-- FORM + RESULT GRID -->
            <div class="postharvest-grid">
                
                <!-- LEFT: INPUT FORM -->
                <section class="glass-card">
                    <h2 class="sec-title">📝 Enter Storage Conditions</h2>
                    <form id="postharvestForm" class="postharvest-form">
                        
                        <div class="fg">
                            <label>Crop Type</label>
                            <select name="crop" required>
                                <option value="Maize">Maize</option>
                                <option value="Beans">Beans</option>
                                <option value="Rice">Rice</option>
                                <option value="Tomato">Tomato</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label>Storage Temperature (°C)</label>
                            <input type="number" name="temp" value="25" step="1" min="0" max="50" required>
                            <small>Ambient temperature where crops are stored.</small>
                        </div>

                        <div class="fg">
                            <label>Humidity (%)</label>
                            <input type="number" name="humidity" value="70" step="5" min="10" max="100" required>
                        </div>

                        <div class="fg">
                            <label>Days in Storage</label>
                            <input type="number" name="days" value="5" step="1" min="1" max="365" required>
                        </div>

                        <button type="submit" class="btn-primary" id="btnSubmit">
                            📉 Calculate Spoilage Risk
                        </button>
                    </form>
                </section>

                <!-- RIGHT: RESULT PANEL -->
                <section class="glass-card" id="resultCard" style="display:none;">
                    <h2 class="sec-title">📊 Risk Analysis</h2>
                    <div id="resultContent"></div>
                </section>

            </div>

        </main>
    </div>
</div>

<style>
.postharvest-page.dashboard-layout { display: block !important; width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; }
.postharvest-page .main-wrap { margin-left: 260px !important; width: calc(100% - 260px) !important; min-height: 100vh !important; box-sizing: border-box !important; }
.postharvest-page .main-content { padding: 20px 28px 60px !important; display: flex !important; flex-direction: column !important; gap: 18px !important; box-sizing: border-box !important; }
.postharvest-page .glass-card { background: rgba(30,41,59,.7) !important; backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,.08) !important; border-radius: 20px !important; padding: 24px !important; box-sizing: border-box !important; }

.postharvest-hero { background: linear-gradient(135deg, rgba(139,92,246,.15), rgba(109,40,217,.08)) !important; }
.postharvest-hero h1 { margin: 0 0 8px; font-size: 26px; font-weight: 800; color: #f1f5f9; }
.postharvest-hero p { margin: 0 0 16px; color: #94a3b8; font-size: 14px; }
.postharvest-hero strong { color: #a78bfa; }
.nist-badge { display: flex; gap: 8px; flex-wrap: wrap; }
.ds-pill { background: rgba(255,255,255,.1); color: #cbd5e1; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }

.postharvest-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; align-items: start; }
@media (max-width: 900px) { .postharvest-grid { grid-template-columns: 1fr; } }

.sec-title { margin: 0 0 20px; font-size: 16px; font-weight: 800; color: #f1f5f9; }

.postharvest-form { display: flex; flex-direction: column; gap: 16px; }
.fg { display: flex; flex-direction: column; gap: 6px; }
.fg label { font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
.fg input, .fg select { padding: 12px 15px; background: rgba(0,0,0,.3); border: 1px solid rgba(255,255,255,.12); border-radius: 11px; color: #f1f5f9; font-size: 14px; font-family: inherit; outline: none; }
.fg input:focus, .fg select:focus { border-color: #a78bfa; box-shadow: 0 0 0 3px rgba(139,92,246,.15); }
.fg select option { background: #1e293b; }
.fg small { font-size: 11px; color: #64748b; font-style: italic; }

.btn-primary { padding: 14px 24px; background: linear-gradient(135deg, #a78bfa, #6d28d9); color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit; margin-top: 10px; transition: transform .15s; box-shadow: 0 4px 15px rgba(139,92,246,.3); }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(139,92,246,.5); }
.btn-primary:disabled { opacity: .5; cursor: not-allowed; }

/* Result Styles */
.risk-display { text-align: center; margin-bottom: 24px; }
.risk-circle { width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 28px; font-weight: 800; color: #fff; }
.risk-circle.green { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 0 30px rgba(16,185,129,.4); }
.risk-circle.orange { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 0 30px rgba(245,158,11,.4); }
.risk-circle.red { background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 0 30px rgba(239,68,68,.4); }
.risk-label { font-size: 16px; font-weight: 700; color: #f1f5f9; }

.recommendation-box { background: rgba(15,23,42,.6); border-radius: 12px; padding: 20px; border-left: 4px solid #a78bfa; }
.recommendation-box h4 { margin: 0 0 8px; color: #a78bfa; font-size: 14px; text-transform: uppercase; }
.recommendation-box p { margin: 0; color: #f1f5f9; font-size: 15px; line-height: 1.5; font-weight: 600; }
</style>

<script>
document.getElementById('postharvestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerHTML = '⏳ Calculating...';

    const formData = new FormData(this);
    const card = document.getElementById('resultCard');
    const content = document.getElementById('resultContent');

    try {
        const res = await fetch('<?= BASE_URL ?>/postharvest/predict', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        });
        const data = await res.json();

        if (!data.success) throw new Error(data.error || 'Calculation failed');

        card.style.display = 'block';
        
        let colorClass = data.color; // green, orange, red
        if(colorClass === 'green') colorClass = 'green';
        else if(colorClass === 'orange') colorClass = 'orange';
        else colorClass = 'red';

        let html = `
            <div class="risk-display">
                <div class="risk-circle ${colorClass}">
                    ${data.risk}%
                </div>
                <div class="risk-label">Spoilage Risk</div>
                <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Base loss for this crop: ${data.base_loss}</div>
            </div>

            <div class="recommendation-box">
                <h4>💡 Recommendation</h4>
                <p>${data.recommendation}</p>
            </div>
        `;

        content.innerHTML = html;
        card.scrollIntoView({ behavior: 'smooth', block: 'start' });

    } catch (err) {
        alert(err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '📉 Calculate Spoilage Risk';
    }
});
</script>