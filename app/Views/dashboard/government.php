<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card gov-hero">
            <div>
                <h1>🏛️ Government Analytics</h1>
                <p>National agricultural intelligence · Evidence-based decision support</p>
                <small>Last updated: <?= date('F d, Y H:i') ?></small>
            </div>
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>/government" class="btn-primary">📊 Full Dashboard</a>
                <a href="<?= BASE_URL ?>/government/reports" class="btn-secondary">📄 Reports</a>
            </div>
        </section>

        <!-- National KPIs -->
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">👨‍🌾</div>
                <div>
                    <p class="kpi-label">Registered Farmers</p>
                    <h3 class="kpi-value"><?= number_format($data['kpis']['registered_farmers'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#84CC16,#16A34A)">🌾</div>
                <div>
                    <p class="kpi-label">Total Production</p>
                    <h3 class="kpi-value"><?= number_format(($data['kpis']['total_production_kg'] ?? 0) / 1000, 1) ?> <small>t</small></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">💰</div>
                <div>
                    <p class="kpi-label">Market Value</p>
                    <h3 class="kpi-value"><?= number_format(($data['kpis']['market_value_rwf'] ?? 0) / 1e9, 2) ?> <small>B RWF</small></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">🏡</div>
                <div>
                    <p class="kpi-label">Active Farms</p>
                    <h3 class="kpi-value"><?= number_format($data['kpis']['active_farms'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#EF4444,#DC2626)">🚨</div>
                <div>
                    <p class="kpi-label">Active Alerts</p>
                    <h3 class="kpi-value"><?= number_format($data['kpis']['active_alerts'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#6366F1,#8B5CF6)">📍</div>
                <div>
                    <p class="kpi-label">Districts Covered</p>
                    <h3 class="kpi-value"><?= number_format($data['kpis']['districts_covered'] ?? 0) ?>/30</h3>
                </div>
            </div>
        </section>

        <!-- Map + Insights -->
        <section class="gov-main-grid">
            <div class="glass-card gov-map-card">
                <div class="card-header">
                    <h3>🗺️ Production Heatmap</h3>
                    <select id="govMapLayer" onchange="switchGovLayer()">
                        <option value="production">Production (kg)</option>
                        <option value="rainfall">Rainfall (mm)</option>
                        <option value="irrigation">Irrigation Coverage</option>
                    </select>
                </div>
                <div id="govMap" style="height: 450px; border-radius: 16px;"></div>
                <div class="map-legend">
                    <span><?= __('weather.legend.low') ?></span>
                    <div class="legend-bar"></div>
                    <span><?= __('weather.legend.high') ?></span>
                </div>
            </div>

            <div class="glass-card gov-insights-card">
                <div class="card-header"><h3>🤖 AI Insights</h3></div>
                <div class="insights-list">
                    <?php if (empty($data['insights'])): ?>
                        <p class="empty-state">No insights available.</p>
                    <?php else: ?>
                        <?php foreach ($data['insights'] as $ins): ?>
                            <div class="insight-item">
                                <span class="insight-icon"><?= $ins['icon'] ?></span>
                                <div>
                                    <strong><?= e($ins['title']) ?></strong>
                                    <p><?= e($ins['body']) ?></p>
                                    <small>Confidence: <?= round($ins['confidence']*100) ?>%</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- NST2 Tracking -->
        <section class="glass-card">
            <div class="card-header"><h3>🎯 NST2 Performance Tracker</h3></div>
            <div class="nst2-grid">
                <?php if (!empty($data['nst2'])): ?>
                    <?php foreach ($data['nst2'] as $n): 
                        $progress = min(100, round(($n['current_value'] / max(1, $n['target_2029'])) * 100));
                        $pillarColor = $n['pillar'] === 'economic' ? '#22C55E' : ($n['pillar'] === 'social' ? '#0EA5E9' : '#F59E0B');
                    ?>
                        <div class="nst2-card" style="border-left: 3px solid <?= $pillarColor ?>">
                            <span class="nst2-pillar" style="color:<?= $pillarColor ?>">
                                <?= strtoupper($n['pillar']) ?>
                            </span>
                            <strong><?= e($n['name']) ?></strong>
                            <div class="nst2-progress">
                                <div class="nst2-fill" style="width:<?= $progress ?>%; background:<?= $pillarColor ?>"></div>
                            </div>
                            <div class="nst2-values">
                                <span><?= number_format($n['current_value'], 1) ?> <?= $n['unit'] ?></span>
                                <small>Target: <?= number_format($n['target_2029'], 1) ?></small>
                                <span class="trend <?= $n['trend'] ?>">
                                    <?= $n['trend'] === 'up' ? '▲' : ($n['trend'] === 'down' ? '▼' : '—') ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-state">NST2 data loading...</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Alerts -->
        <section class="glass-card">
            <div class="card-header">
                <h3>🚨 Active Alerts</h3>
                <a href="<?= BASE_URL ?>/government/alerts" class="btn-sm">Manage</a>
            </div>
            <?php if (empty($data['alerts'])): ?>
                <p class="empty-state">No active alerts. All clear ✅</p>
            <?php else: ?>
                <?php foreach (array_slice($data['alerts'], 0, 5) as $a): ?>
                    <div class="alert-row">
                        <span class="severity-pill <?= $a['severity'] ?>"><?= strtoupper($a['severity']) ?></span>
                        <div>
                            <strong><?= e($a['title']) ?></strong>
                            <small><?= e($a['district_name'] ?? 'National') ?> · <?= date('M d, H:i', strtotime($a['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= BASE_URL ?>/js/government.js" defer></script>

<style>
.gov-hero {
    padding: 30px !important;
    max-width: none;
    background: linear-gradient(135deg, rgba(22,163,74,0.2), rgba(132,204,22,0.08));
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.gov-hero h1 { margin: 0 0 6px; font-size: 26px; }
.gov-hero p { color: var(--text-sub); margin: 0 0 4px; font-size: 13px; }
.gov-hero small { color: var(--text-sub); font-size: 11px; }

.gov-main-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}
.gov-map-card { padding: 22px !important; max-width: none; }
.gov-map-card select {
    background: rgba(0,0,0,0.3);
    border: 1px solid var(--border);
    color: white;
    padding: 8px 14px;
    border-radius: 10px;
    font-family: inherit;
    font-size: 13px;
}
.gov-insights-card {
    padding: 22px !important;
    max-width: none;
    max-height: 620px;
    overflow-y: auto;
}
.insight-item {
    display: flex;
    gap: 12px;
    padding: 14px 0;
    border-bottom: 1px solid var(--border);
}
.insight-item:last-child { border-bottom: none; }
.insight-icon { font-size: 24px; flex-shrink: 0; }
.insight-item strong { display: block; font-size: 13px; margin-bottom: 4px; }
.insight-item p { margin: 0; color: var(--text-sub); font-size: 12px; line-height: 1.5; }
.insight-item small { color: var(--accent); font-size: 11px; font-weight: 600; }

.nst2-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 12px;
}
.nst2-card {
    padding: 16px;
    background: rgba(255,255,255,0.03);
    border-radius: 12px;
}
.nst2-pillar { font-size: 10px; font-weight: 700; letter-spacing: 1px; }
.nst2-card strong { display: block; margin: 6px 0 10px; font-size: 13px; }
.nst2-progress { height: 6px; background: rgba(255,255,255,0.08); border-radius: 3px; overflow: hidden; margin-bottom: 8px; }
.nst2-fill { height: 100%; border-radius: 3px; transition: width 500ms; }
.nst2-values { display: flex; justify-content: space-between; align-items: center; font-size: 12px; }
.nst2-values small { color: var(--text-sub); font-size: 11px; }
.nst2-values .trend.up { color: #22C55E; }
.nst2-values .trend.down { color: #EF4444; }

.alert-row {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
}
.alert-row:last-child { border-bottom: none; }
.alert-row strong { display: block; font-size: 13px; }
.alert-row small { color: var(--text-sub); font-size: 11px; }

.severity-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.5px;
}
.severity-pill.low { background: rgba(34,197,94,0.2); color: #22C55E; }
.severity-pill.medium { background: rgba(245,158,11,0.2); color: #F59E0B; }
.severity-pill.high { background: rgba(239,68,68,0.2); color: #EF4444; }
.severity-pill.critical { background: #EF4444; color: white; animation: pulse 1.5s infinite; }
@keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.6;} }

@media (max-width: 900px) {
    .gov-main-grid { grid-template-columns: 1fr; }
}
</style>