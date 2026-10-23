<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="main-content" style="background:#0b1120; padding:30px;">
            
            <h1 style="color:#fff; margin-bottom:10px;">📊 NISR Data Explorer</h1>
            <p style="color:#94a3b8; margin-bottom:30px;">Interactive visualization of official NISR SAS 2025 & AHS 2024 datasets.</p>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                <!-- Chart 1: Yields -->
                <div class="glass-card" style="background:rgba(30,41,59,0.7); padding:20px; border-radius:15px; border:1px solid rgba(255,255,255,0.1);">
                    <h3 style="color:#fff; margin-bottom:15px;">Average Crop Yield by District (kg/ha)</h3>
                    <canvas id="yieldChart" height="200"></canvas>
                </div>

                <!-- Chart 2: Households -->
                <div class="glass-card" style="background:rgba(30,41,59,0.7); padding:20px; border-radius:15px; border:1px solid rgba(255,255,255,0.1);">
                    <h3 style="color:#fff; margin-bottom:15px;">Agri-Households per District</h3>
                    <canvas id="householdChart" height="200"></canvas>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
async function loadChart(chartId, type, color, title) {
    const res = await fetch(`<?= BASE_URL ?>/explorer/apiChartData?type=${type}`);
    const data = await res.json();
    
    new Chart(document.getElementById(chartId), {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: title,
                data: data.data,
                backgroundColor: color,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: '#f1f5f9' } }
            },
            scales: {
                x: { ticks: { color: '#94a3b8', maxRotation: 90, minRotation: 90 } },
                y: { ticks: { color: '#94a3b8' }, beginAtZero: true }
            }
        }
    });
}

loadChart('yieldChart', 'yields', '#10b981', 'Yield (kg/ha)');
loadChart('householdChart', 'households', '#38bdf8', 'Households');
</script>