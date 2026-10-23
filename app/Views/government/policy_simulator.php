<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="main-content" style="background:#0b1120; padding:30px;">
            
            <h1 style="color:#fff; margin-bottom:20px;">📊 NST2 Policy Simulator</h1>
            <p style="color:#94a3b8; margin-bottom:30px;">Simulate the impact of national agricultural investments based on NISR elasticity data.</p>

            <div class="glass-card" style="background:rgba(30,41,59,0.7); padding:30px; border-radius:15px; border:1px solid rgba(255,255,255,0.1); max-width:600px;">
                <form id="simForm">
                    <div style="margin-bottom:20px;">
                        <label style="color:#94a3b8; display:block; margin-bottom:8px;">Select Intervention</label>
                        <select name="intervention" style="width:100%; padding:12px; background:#0f172a; color:#fff; border:1px solid #334155; border-radius:8px;">
                            <option value="irrigation">Expand Irrigation Access</option>
                            <option value="fertilizer">Increase Fertilizer Subsidy</option>
                            <option value="lime">Distribute Agricultural Lime</option>
                        </select>
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="color:#94a3b8; display:block; margin-bottom:8px;">Magnitude of Investment (%)</label>
                        <input type="number" name="magnitude" value="20" style="width:100%; padding:12px; background:#0f172a; color:#fff; border:1px solid #334155; border-radius:8px;">
                    </div>
                    <button type="submit" style="background:#10b981; color:#fff; padding:14px 24px; border:none; border-radius:8px; font-weight:bold; width:100%; cursor:pointer;">Run Simulation</button>
                </form>

                <div id="simResult" style="display:none; margin-top:30px; padding:20px; background:rgba(16,185,129,0.1); border-left:4px solid #10b981; border-radius:8px;">
                    <!-- Results injected here -->
                </div>
            </div>

        </main>
    </div>
</div>

<script>
document.getElementById('simForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const res = await fetch('<?= BASE_URL ?>/government/simulate', {
        method: 'POST', body: new FormData(form)
    });
    const data = await res.json();
    
    if(data.success) {
        const box = document.getElementById('simResult');
        box.style.display = 'block';
        box.innerHTML = `
            <h3 style="color:#34d399; margin-top:0;">Simulation Results</h3>
            <p style="color:#cbd5e1; font-size:14px;">A <b>${data.magnitude}%</b> increase in <b>${data.intervention}</b> will result in:</p>
            <ul style="color:#f1f5f9; font-size:16px; line-height:1.8;">
                <li>Yield Increase: <b style="color:#10b981;">+${data.yield_gain_pct}%</b></li>
                <li>Projected Production: <b>${data.projected_production_tons.toLocaleString()} Tons</b></li>
                <li>Projected Revenue: <b>${data.projected_revenue_rwf.toLocaleString()} RWF</b></li>
                <li>Economic Impact: <b>${data.gdp_impact}</b></li>
            </ul>
        `;
    }
});
</script>
