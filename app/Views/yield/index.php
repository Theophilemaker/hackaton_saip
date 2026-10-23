<?php
$districts   = $districts   ?? [];
$crops       = $crops       ?? [];
$national    = $national    ?? [];
$data_status = $data_status ?? [];
?>
<div class="dashboard-layout yield-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="main-content">

            <!-- HERO -->
            <section class="glass-card yield-hero">
                <div>
                    <h1>🌾 Yield Predictor</h1>
                    <p>AI-powered harvest prediction trained on NISR SAS 2025 data</p>
                    <div class="nist-badge">
                        <?php foreach ($data_status as $ds): ?>
                            <span class="ds-pill" title="<?= htmlspecialchars($ds['reference']) ?>">
                                📊 <?= htmlspecialchars($ds['name']) ?>
                                <small><?= number_format($ds['records']) ?> records</small>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- INPUT FORM -->
            <section class="glass-card">
                <h2 class="sec-title">📝 Enter Your Farm Details</h2>
                <form id="predictForm" class="predict-form">

                    <div class="grid-2">
                        <div class="fg">
                            <label>District</label>
                            <select name="district" required>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?= htmlspecialchars($d) ?>" <?= $d==='Musanze'?'selected':'' ?>>
                                        <?= htmlspecialchars($d) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label>Crop</label>
                            <select name="crop" required>
                                <?php foreach ($crops as $c): ?>
                                    <option value="<?= htmlspecialchars($c) ?>" <?= $c==='Maize'?'selected':'' ?>>
                                        <?= htmlspecialchars($c) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="fg">
                            <label>Plot Area (hectares)</label>
                            <input type="number" name="area_ha" value="1" step="0.1" min="0.1" max="100">
                        </div>
                        <div class="fg">
                            <label>Fertilizer used (kg/ha)</label>
                            <input type="number" name="fertilizer_kg_ha" value="45" step="5" min="0" max="300">
                            <small>NPK 17-17-17 equivalent</small>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="fg">
                            <label>Irrigation coverage (%)</label>
                            <input type="number" name="irrigation_pct" value="12" step="1" min="0" max="100">
                        </div>
                        <div class="fg">
                            <label>Soil pH</label>
                            <input type="number" name="soil_ph" value="5.8" step="0.1" min="3" max="9">
                            <small>Kigali average: 5.8 · Acidic below 5.5</small>
                        </div>
                    </div>

                    <div class="fg">
                        <label>Annual rainfall (mm)</label>
                        <input type="number" name="rainfall_mm" value="900" step="50" min="200" max="2000">
                    </div>

                    <button type="submit" class="btn-primary-lg" id="predictBtn">
                        🔮 Predict My Yield
                    </button>
                </form>
            </section>

            <!-- RESULT -->
            <section class="glass-card" id="resultCard" style="display:none;">
                <div id="resultContent"></div>
            </section>

        </main>
    </div>
</div>

<style>
.yield-page.dashboard-layout { display: block !important; width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; }
.yield-page .main-wrap { margin-left: 260px !important; width: calc(100% - 260px) !important; min-height: 100vh !important; box-sizing: border-box !important; }
.yield-page .main-content { padding: 20px 28px 60px !important; display: flex !important; flex-direction: column !important; gap: 18px !important; box-sizing: border-box !important; }
.yield-page .glass-card { background: rgba(30,41,59,.7) !important; backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,.08) !important; border-radius: 20px !important; padding: 24px !important; box-sizing: border-box !important; width: 100% !important; }

.yield-hero { background: linear-gradient(135deg, rgba(16,185,129,.15), rgba(56,189,248,.08)) !important; }
.yield-hero h1 { margin: 0 0 8px; font-size: 26px; font-weight: 800; color: #f1f5f9; }
.yield-hero p { margin: 0 0 16px; color: #94a3b8; font-size: 14px; }
.nist-badge { display: flex; flex-wrap: wrap; gap: 8px; }
.ds-pill {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(16,185,129,.12); color: #34d399;
    border: 1px solid rgba(16,185,129,.35);
    padding: 6px 14px; border-radius: 20px;
    font-size: 12px; font-weight: 700;
}
.ds-pill small { opacity: .8; font-weight: 500; }

.sec-title { margin: 0 0 18px; font-size: 16px; font-weight: 800; color: #f1f5f9; }

.predict-form { display: flex; flex-direction: column; gap: 14px; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.fg { display: flex; flex-direction: column; gap: 6px; }
.fg label { font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
.fg input, .fg select {
    padding: 12px 15px; background: rgba(0,0,0,.3);
    border: 1px solid rgba(255,255,255,.12); border-radius: 11px;
    color: #f1f5f9; font-size: 14px; font-family: inherit;
    outline: none; box-sizing: border-box; width: 100%;
}
.fg input:focus, .fg select:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.15); }
.fg select option { background: #1e293b; }
.fg small { font-size: 11px; color: #64748b; font-style: italic; }

.btn-primary-lg {
    padding: 14px 28px; background: linear-gradient(135deg, #10b981, #059669);
    color: white; border: none; border-radius: 12px;
    font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit;
    margin-top: 8px; box-shadow: 0 4px 15px rgba(16,185,129,.35);
    transition: transform .15s;
}
.btn-primary-lg:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16,185,129,.5); }
.btn-primary-lg:disabled { opacity: .5; cursor: not-allowed; }

/* Result card */
.result-header { display: flex; align-items: center; gap: 16px; margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid rgba(255,255,255,.08); }
.result-icon { width: 60px; height: 60px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
.result-title { font-size: 22px; font-weight: 800; color: #f1f5f9; }
.result-sub { color: #94a3b8; font-size: 13px; }

.kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 24px; }
.kpi-card { background: rgba(15,23,42,.6); border: 1px solid rgba(255,255,255,.06); border-radius: 14px; padding: 18px; }
.kpi-label { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800; letter-spacing: .5px; margin-bottom: 6px; }
.kpi-value { font-size: 26px; font-weight: 800; color: #34d399; line-height: 1.1; }
.kpi-value.small { font-size: 18px; }
.kpi-sub { font-size: 11px; color: #94a3b8; margin-top: 4px; }

/* Comparison bars */
.comparison { display: flex; flex-direction: column; gap: 10px; margin-bottom: 22px; }
.comp-row { display: flex; align-items: center; gap: 12px; }
.comp-label { width: 120px; font-size: 12px; color: #94a3b8; font-weight: 700; text-align: right; }
.comp-bar-wrap { flex: 1; height: 28px; background: rgba(0,0,0,.3); border-radius: 8px; overflow: hidden; position: relative; }
.comp-bar { height: 100%; border-radius: 8px; display: flex; align-items: center; padding-left: 10px; color: #fff; font-size: 12px; font-weight: 800; transition: width .6s ease; }
.comp-bar.you      { background: linear-gradient(90deg, #10b981, #059669); }
.comp-bar.district { background: linear-gradient(90deg, #38bdf8, #0284c7); }
.comp-bar.national { background: linear-gradient(90deg, #a78bfa, #7c3aed); }
.comp-bar.best     { background: linear-gradient(90deg, #fbbf24, #d97706); }
.comp-value { width: 90px; font-size: 12px; font-weight: 700; color: #f1f5f9; }

/* Interventions */
.interventions-title { font-size: 14px; font-weight: 800; color: #f1f5f9; margin: 20px 0 12px; }
.int-card {
    display: flex; gap: 14px; align-items: flex-start;
    padding: 14px; border-radius: 12px;
    background: rgba(15,23,42,.5);
    border: 1px solid rgba(255,255,255,.06);
    margin-bottom: 10px;
}
.int-card.high     { border-left: 4px solid #fbbf24; }
.int-card.critical { border-left: 4px solid #ef4444; }
.int-card.medium   { border-left: 4px solid #38bdf8; }
.int-icon { font-size: 22px; flex-shrink: 0; }
.int-body { flex: 1; }
.int-action { font-size: 14px; font-weight: 700; color: #f1f5f9; margin-bottom: 4px; }
.int-meta { display: flex; gap: 16px; flex-wrap: wrap; font-size: 12px; color: #94a3b8; }
.int-meta span { display: inline-flex; align-items: center; gap: 4px; }
.int-meta strong { color: #34d399; }

@media (max-width: 900px) {
    .yield-page .main-wrap { margin-left: 0 !important; width: 100% !important; padding-top: 64px !important; }
    .grid-2 { grid-template-columns: 1fr; }
}
</style>

<script>
(function () {
    'use strict';
    const BASE = '<?= BASE_URL ?>';
    const form = document.getElementById('predictForm');
    const btn  = document.getElementById('predictBtn');
    const card = document.getElementById('resultCard');
    const cont = document.getElementById('resultContent');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        btn.disabled = true;
        btn.innerHTML = '⏳ Predicting…';

        const data = new FormData(form);
        try {
            const r = await fetch(BASE + '/yield/predict', {
                method: 'POST',
                credentials: 'same-origin',
                body: data
            });
            const res = await r.json();

            if (!res.success) {
                alert(res.error || 'Prediction failed');
                return;
            }

            renderResult(res.prediction);
            card.style.display = 'block';
            card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (err) {
            alert('Network error: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '🔮 Predict My Yield';
        }
    });

    function renderResult(p) {
        const maxVal = Math.max(p.predicted_yield_kg_ha, p.district_avg_kg_ha, p.national_avg_kg_ha, p.best_practice_kg_ha);
        const w = (v) => Math.round((v / maxVal) * 100);

        cont.innerHTML = `
            <div class="result-header">
                <div class="result-icon">🌾</div>
                <div>
                    <div class="result-title">${p.predicted_yield_mt_ha} MT/ha predicted</div>
                    <div class="result-sub">
                        For ${p.inputs_used.area_ha} ha of ${p.inputs_used.crop} in ${p.inputs_used.district}
                        · Confidence ${Math.round(p.confidence*100)}%
                        · Model: ${p.model_used}
                    </div>
                </div>
            </div>

            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-label">Total Production</div>
                    <div class="kpi-value">${p.total_production_tons} t</div>
                    <div class="kpi-sub">${p.total_production_kg.toLocaleString()} kg</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Est. Revenue</div>
                    <div class="kpi-value small">${p.revenue_estimate_rwf.toLocaleString()} RWF</div>
                    <div class="kpi-sub">At current market prices</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">vs District</div>
                    <div class="kpi-value small" style="color:${p.gap_to_district_pct >= 0 ? '#34d399' : '#f87171'}">
                        ${p.gap_to_district_pct >= 0 ? '+' : ''}${p.gap_to_district_pct}%
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">vs Best Practice</div>
                    <div class="kpi-value small" style="color:${p.gap_to_best_pct >= 0 ? '#34d399' : '#fbbf24'}">
                        ${p.gap_to_best_pct >= 0 ? '+' : ''}${p.gap_to_best_pct}%
                    </div>
                </div>
            </div>

            <h3 class="interventions-title">📊 How you compare</h3>
            <div class="comparison">
                <div class="comp-row">
                    <div class="comp-label">Your prediction</div>
                    <div class="comp-bar-wrap"><div class="comp-bar you" style="width:${w(p.predicted_yield_kg_ha)}%">${p.predicted_yield_kg_ha.toLocaleString()} kg/ha</div></div>
                    <div class="comp-value"></div>
                </div>
                <div class="comp-row">
                    <div class="comp-label">District avg</div>
                    <div class="comp-bar-wrap"><div class="comp-bar district" style="width:${w(p.district_avg_kg_ha)}%">${p.district_avg_kg_ha.toLocaleString()} kg/ha</div></div>
                    <div class="comp-value"></div>
                </div>
                <div class="comp-row">
                    <div class="comp-label">National avg</div>
                    <div class="comp-bar-wrap"><div class="comp-bar national" style="width:${w(p.national_avg_kg_ha)}%">${p.national_avg_kg_ha.toLocaleString()} kg/ha</div></div>
                    <div class="comp-value"></div>
                </div>
                <div class="comp-row">
                    <div class="comp-label">Best practice</div>
                    <div class="comp-bar-wrap"><div class="comp-bar best" style="width:${w(p.best_practice_kg_ha)}%">${p.best_practice_kg_ha.toLocaleString()} kg/ha</div></div>
                    <div class="comp-value"></div>
                </div>
            </div>

            ${p.interventions.length ? `
                <h3 class="interventions-title">🚀 Actions to increase your yield</h3>
                ${p.interventions.map(i => `
                    <div class="int-card ${i.priority}">
                        <div class="int-icon">${i.priority === 'critical' ? '🔥' : i.priority === 'high' ? '⭐' : '💡'}</div>
                        <div class="int-body">
                            <div class="int-action">${i.action}</div>
                            <div class="int-meta">
                                <span>💰 Cost: <strong>${i.cost_rwf.toLocaleString()} RWF</strong></span>
                                <span>📈 Gain: <strong>+${i.yield_gain_pct}%</strong></span>
                                <span>🌾 +${i.yield_gain_kg.toLocaleString()} kg/ha</span>
                                <span>⏱ ROI: <strong>${i.roi_months} months</strong></span>
                            </div>
                        </div>
                    </div>
                `).join('')}
                <div style="margin-top:14px;padding:12px;background:rgba(16,185,129,.1);border-radius:10px;border-left:3px solid #10b981;">
                    <strong style="color:#34d399;">💵 Total potential gain:</strong>
                    <span style="color:#f1f5f9;font-weight:700;"> ${p.estimated_gain_rwf.toLocaleString()} RWF</span>
                    across ${p.inputs_used.area_ha} ha
                </div>
            ` : ''}
        `;
    }
})();
</script>