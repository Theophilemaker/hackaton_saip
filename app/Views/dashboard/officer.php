<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card officer-hero">
            <div>
                <h1>🏛️ Agricultural Officer Dashboard</h1>
                <p><?= e($_SESSION['district_name'] ?? 'District Monitoring') ?> · Field Operations Center</p>
            </div>
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>/government" class="btn-primary">📊 District Analytics</a>
                <a href="<?= BASE_URL ?>/forum" class="btn-secondary">💬 Forums</a>
            </div>
        </section>

        <!-- KPIs -->
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">👨‍🌾</div>
                <div>
                    <p class="kpi-label">Farmers in District</p>
                    <h3 class="kpi-value" id="officerFarmers">—</h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#84CC16,#16A34A)">🌾</div>
                <div>
                    <p class="kpi-label">Major Crops</p>
                    <h3 class="kpi-value">8</h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#06B6D4)">🌧️</div>
                <div>
                    <p class="kpi-label">Rain Coverage</p>
                    <h3 class="kpi-value">78<small>%</small></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">🐛</div>
                <div>
                    <p class="kpi-label">Pest Alerts</p>
                    <h3 class="kpi-value">12</h3>
                </div>
            </div>
        </section>

        <!-- Tasks + Map -->
        <section class="officer-grid">
            <!-- Task Panel -->
            <div class="glass-card">
                <div class="card-header">
                    <h3>✅ Today's Tasks</h3>
                    <a href="#" class="btn-sm">+ New</a>
                </div>
                <div class="task-list">
                    <div class="task-item">
                        <span class="task-check">☐</span>
                        <div>
                            <strong>Farm inspection — Bugesera Sector</strong>
                            <small>📅 Today, 10:00 AM</small>
                        </div>
                    </div>
                    <div class="task-item">
                        <span class="task-check">☐</span>
                        <div>
                            <strong>Verify crop disease reports</strong>
                            <small>📅 Today, 2:00 PM</small>
                        </div>
                    </div>
                    <div class="task-item">
                        <span class="task-check done">☑</span>
                        <div>
                            <strong>Publish weather alert</strong>
                            <small>✅ Completed at 8:30 AM</small>
                        </div>
                    </div>
                    <div class="task-item">
                        <span class="task-check">☐</span>
                        <div>
                            <strong>Answer forum questions</strong>
                            <small>📅 3 pending questions</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div class="glass-card">
                <div class="card-header"><h3>🗺️ District Hotspots</h3></div>
                <div id="officerMap" style="height: 320px; border-radius: 14px;"></div>
                <p class="input-hint" style="margin-top: 10px;">
                    🔴 Pest hotspots · 🌊 Flood risk · 💧 Irrigation coverage
                </p>
            </div>
        </section>

        <!-- Recent Farmer Reports -->
        <section class="glass-card">
            <div class="card-header">
                <h3>📋 Recent Farmer Reports</h3>
                <a href="<?= BASE_URL ?>/forum" class="btn-sm">View Forum</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>Farmer</th><th>Report Type</th><th>Location</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Jean Mugisha</td>
                        <td>🐛 Pest Report</td>
                        <td>Bugesera — Ruhuha</td>
                        <td><span class="status-pill pending">Pending</span></td>
                        <td><button class="btn-sm">Verify</button></td>
                    </tr>
                    <tr>
                        <td>Marie Uwase</td>
                        <td>🌾 Disease Detection</td>
                        <td>Bugesera — Nyamata</td>
                        <td><span class="status-pill paid">Verified</span></td>
                        <td><button class="btn-sm">View</button></td>
                    </tr>
                    <tr>
                        <td>Eric Habimana</td>
                        <td>💧 Irrigation Issue</td>
                        <td>Bugesera — Juru</td>
                        <td><span class="status-pill pending">Pending</span></td>
                        <td><button class="btn-sm">Verify</button></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Actions -->
        <section class="glass-card">
            <div class="card-header"><h3>⚡ Officer Actions</h3></div>
            <div class="admin-tools-grid">
                <a href="<?= BASE_URL ?>/government/publishAlert" class="admin-tool">
                    <span class="at-icon">🚨</span>
                    <strong>Publish Alert</strong>
                    <small>Notify farmers</small>
                </a>
                <a href="<?= BASE_URL ?>/forum" class="admin-tool">
                    <span class="at-icon">💬</span>
                    <strong>Answer Forums</strong>
                    <small>Verify answers</small>
                </a>
                <a href="<?= BASE_URL ?>/government" class="admin-tool">
                    <span class="at-icon">📊</span>
                    <strong>District Analytics</strong>
                    <small>Production + weather</small>
                </a>
                <a href="<?= BASE_URL ?>/crops/detect" class="admin-tool">
                    <span class="at-icon">🔬</span>
                    <strong>Disease Detection</strong>
                    <small>AI-powered</small>
                </a>
            </div>
        </section>
    </main>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Init map
    const map = L.map('officerMap').setView([-2.2167, 30.1500], 9);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // Sample hotspots
    L.circleMarker([-2.2167, 30.1500], {
        radius: 12,
        color: '#EF4444',
        fillColor: '#EF4444',
        fillOpacity: 0.6,
        weight: 2
    }).addTo(map).bindPopup('🐛 Fall Armyworm hotspot');

    L.circleMarker([-2.2500, 30.1000], {
        radius: 10,
        color: '#0EA5E9',
        fillColor: '#0EA5E9',
        fillOpacity: 0.5,
        weight: 2
    }).addTo(map).bindPopup('🌊 Flood risk');

    L.circleMarker([-2.1800, 30.2000], {
        radius: 8,
        color: '#84CC16',
        fillColor: '#84CC16',
        fillOpacity: 0.5,
        weight: 2
    }).addTo(map).bindPopup('💧 Irrigation active');

    // Fake farmer count
    document.getElementById('officerFarmers').textContent = '<?= number_format(rand(800, 1500)) ?>';
});
</script>

<style>
.officer-hero {
    padding: 30px !important;
    max-width: none;
    background: linear-gradient(135deg, rgba(14,165,233,0.15), rgba(34,197,94,0.05));
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.officer-hero h1 { margin: 0 0 4px; font-size: 24px; }
.officer-hero p { color: var(--text-sub); margin: 0; font-size: 13px; }

.officer-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 20px;
}

.task-list { display: flex; flex-direction: column; gap: 10px; }
.task-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    background: rgba(255,255,255,0.03);
    border-radius: 10px;
    border-left: 3px solid transparent;
    transition: 200ms;
}
.task-item:hover { background: rgba(34,197,94,0.08); border-left-color: var(--accent); }
.task-check { font-size: 18px; color: var(--text-sub); flex-shrink: 0; }
.task-check.done { color: #22C55E; }
.task-item strong { display: block; font-size: 13px; margin-bottom: 4px; }
.task-item small { color: var(--text-sub); font-size: 11px; }

@media (max-width: 900px) {
    .officer-grid { grid-template-columns: 1fr; }
}
</style>