<?php
/* ── Safe data resolution ── */
$fields       = isset($fields)  && is_array($fields)  ? $fields  : (isset($data['fields'])  && is_array($data['fields'])  ? $data['fields']  : []);
$stats        = isset($stats)   && is_array($stats)   ? $stats   : (isset($data['stats'])   && is_array($data['stats'])   ? $data['stats']   : []);
$weather      = isset($weather) && is_array($weather) ? $weather : (isset($data['weather']) && is_array($data['weather']) ? $data['weather'] : []);
$district     = isset($district) ? $district : (isset($data['district']) ? $data['district'] : 'Huye');
$allDistricts = isset($allDistricts) ? $allDistricts : (isset($data['all_districts']) && is_array($data['all_districts']) ? $data['all_districts'] : []);

/* ── Helper functions (guarded to prevent re-declaration) ── */
if (!function_exists('ag_ndvi_class')) {
    function ag_ndvi_class($v) {
        if (!is_numeric($v)) return 'neutral';
        $n = (float)$v;
        if ($n >= 0.6) return 'healthy';
        if ($n >= 0.3) return 'moderate';
        return 'poor';
    }
}
if (!function_exists('ag_ndvi_label')) {
    function ag_ndvi_label($v) {
        if (!is_numeric($v)) return 'No data';
        $n = (float)$v;
        if ($n >= 0.6) return 'Healthy';
        if ($n >= 0.3) return 'Moderate';
        return 'Stressed';
    }
}
if (!function_exists('ag_esc')) {
    function ag_esc($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}
?>
<div class="dashboard-layout agro-page">
    <?php
    $sidebar = __DIR__ . '/../layouts/sidebar.php';
    if (is_file($sidebar)) require_once $sidebar;
    ?>
    <main class="main-content">
        <?php
        $topbar = __DIR__ . '/../layouts/topbar.php';
        if (is_file($topbar)) require_once $topbar;
        ?>

        <!-- ═════════════════ HERO ═════════════════ -->
        <section class="glass-card hero-card">
            <div class="hero-left">
                <span class="hero-tag">
                    <span class="live-dot"></span> SATELLITE INTELLIGENCE
                </span>
                <h1>🛰️ Agromonitoring</h1>
                <p>Satellite imagery, vegetation indices, and weather data for precision farming — in fast and easy ways.</p>
                <div class="hero-badges">
                    <span class="badge-pill">📡 Sentinel-2</span>
                    <span class="badge-pill">🌿 NDVI · EVI · NDWI</span>
                    <span class="badge-pill">🌤️ OpenWeather</span>
                </div>
            </div>
            <div class="hero-right">
                <div class="weather-chip">
                    <span class="wc-icon"><?= ag_esc(isset($weather['icon']) ? $weather['icon'] : '⛅') ?></span>
                    <div>
                        <strong><?= ag_esc(isset($weather['temp']) ? $weather['temp'] : '—') ?>°C</strong>
                        <small><?= ag_esc(isset($weather['condition']) ? $weather['condition'] : '—') ?></small>
                        <small class="wc-district">📍 <?= ag_esc($district) ?></small>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/agromonitoring/create" class="btn-hero-primary">
                    ➕ New Field
                </a>
            </div>
        </section>

        <!-- ═════════════════ KPI STRIP ═════════════════ -->
        <section class="kpi-strip">
            <div class="kpi-card kpi-fields">
                <div class="kpi-icon">🌾</div>
                <div>
                    <span class="kpi-label">Fields</span>
                    <strong class="kpi-value"><?= (int)(isset($stats['total_fields']) ? $stats['total_fields'] : 0) ?></strong>
                </div>
            </div>
            <div class="kpi-card kpi-area">
                <div class="kpi-icon">📐</div>
                <div>
                    <span class="kpi-label">Total Area</span>
                    <strong class="kpi-value"><?= ag_esc(isset($stats['total_area_ha']) ? $stats['total_area_ha'] : 0) ?><span class="kpi-unit">ha</span></strong>
                </div>
            </div>
            <div class="kpi-card kpi-healthy">
                <div class="kpi-icon">✅</div>
                <div>
                    <span class="kpi-label">Healthy</span>
                    <strong class="kpi-value"><?= (int)(isset($stats['healthy']) ? $stats['healthy'] : 0) ?></strong>
                </div>
            </div>
            <div class="kpi-card kpi-risk">
                <div class="kpi-icon">⚠️</div>
                <div>
                    <span class="kpi-label">At Risk</span>
                    <strong class="kpi-value"><?= (int)(isset($stats['at_risk']) ? $stats['at_risk'] : 0) ?></strong>
                </div>
            </div>
            <div class="kpi-card kpi-critical">
                <div class="kpi-icon">🚨</div>
                <div>
                    <span class="kpi-label">Critical</span>
                    <strong class="kpi-value"><?= (int)(isset($stats['critical']) ? $stats['critical'] : 0) ?></strong>
                </div>
            </div>
            <div class="kpi-card kpi-ndvi">
                <div class="kpi-icon">🌿</div>
                <div>
                    <span class="kpi-label">Avg NDVI</span>
                    <strong class="kpi-value">
                        <?php if (isset($stats['avg_ndvi']) && $stats['avg_ndvi'] !== null): ?>
                            <?= ag_esc($stats['avg_ndvi']) ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </strong>
                </div>
            </div>
        </section>

        <!-- ═════════════════ FIELDS GRID ═════════════════ -->
        <section class="glass-card fields-section">
            <div class="card-header">
                <div>
                    <h3>🌾 Monitored Fields</h3>
                    <p>Each field is tracked via satellite imagery and vegetation indices.</p>
                </div>
                <a href="<?= BASE_URL ?>/agromonitoring/create" class="head-btn">➕ Add Field</a>
            </div>

            <?php if (empty($fields)): ?>
                <div class="empty-state">
                    <div class="empty-emoji">🛰️</div>
                    <h3>No fields monitored yet</h3>
                    <p>Register your first field to receive satellite vegetation indices, weather alerts, and crop health trends.</p>
                    <a href="<?= BASE_URL ?>/agromonitoring/create" class="btn-primary">➕ Register Field</a>
                </div>
            <?php else: ?>
                <div class="fields-grid">
                    <?php foreach ($fields as $f):
                        if (!is_array($f)) continue;

                        $ndvi = (isset($f['last_ndvi']) && is_numeric($f['last_ndvi'])) ? (float)$f['last_ndvi'] : null;
                        $cls  = ag_ndvi_class($ndvi);
                        $pct  = $ndvi !== null ? min(100, max(4, round($ndvi * 100))) : 0;
                        $fid  = (int)(isset($f['id']) ? $f['id'] : 0);

                        /* Solid stroke color per class — avoids SVG gradient complexity */
                        $strokeColor = '#64748b'; /* neutral */
                        if ($cls === 'healthy')  $strokeColor = '#10b981';
                        if ($cls === 'moderate') $strokeColor = '#fbbf24';
                        if ($cls === 'poor')     $strokeColor = '#ef4444';

                        /* Circle math */
                        $radius     = 42;
                        $circ       = 2 * 3.14159 * $radius;
                        $dashOffset = $circ * (1 - $pct / 100);

                        $fieldName    = isset($f['name'])     ? $f['name']     : 'Field';
                        $fieldCrop    = isset($f['crop_name']) ? $f['crop_name'] : '';
                        $fieldDist    = isset($f['district']) ? $f['district'] : '—';
                        $fieldArea    = isset($f['area_ha'])  ? (float)$f['area_ha'] : 0;
                        $lastSample   = isset($f['last_sample']) ? $f['last_sample'] : null;
                    ?>
                        <a href="<?= BASE_URL ?>/agromonitoring/field/<?= $fid ?>"
                           class="field-card glass-inner">

                            <!-- NDVI Ring -->
                            <div class="fc-ring">
                                <svg viewBox="0 0 100 100" width="72" height="72">
                                    <circle cx="50" cy="50" r="<?= $radius ?>"
                                            fill="none"
                                            stroke="rgba(255,255,255,0.08)"
                                            stroke-width="8"/>
                                    <circle cx="50" cy="50" r="<?= $radius ?>"
                                            fill="none"
                                            stroke="<?= $strokeColor ?>"
                                            stroke-width="8"
                                            stroke-linecap="round"
                                            stroke-dasharray="<?= $circ ?>"
                                            stroke-dashoffset="<?= $dashOffset ?>"
                                            transform="rotate(-90 50 50)"/>
                                    <text x="50" y="54" text-anchor="middle"
                                          fill="#f1f5f9" font-size="18" font-weight="800">
                                        <?= $ndvi !== null ? number_format($ndvi, 2) : '—' ?>
                                    </text>
                                    <text x="50" y="68" text-anchor="middle"
                                          fill="#94a3b8" font-size="7" font-weight="800"
                                          letter-spacing="1">NDVI</text>
                                </svg>
                            </div>

                            <div class="fc-info">
                                <div class="fc-name">
                                    <strong><?= ag_esc($fieldName) ?></strong>
                                    <span class="fc-status fc-status-<?= ag_esc($cls) ?>"><?= ag_esc(ag_ndvi_label($ndvi)) ?></span>
                                </div>
                                <small class="fc-crop">
                                    <?= $fieldCrop !== '' ? ag_esc($fieldCrop) : 'Unspecified crop' ?>
                                </small>
                                <div class="fc-meta">
                                    <span>📍 <?= ag_esc($fieldDist) ?></span>
                                    <span>📐 <?= number_format($fieldArea, 1) ?> ha</span>
                                </div>
                            </div>

                            <div class="fc-footer">
                                <?php if ($lastSample): ?>
                                    <small>Last sample: <?= date('M d', strtotime((string)$lastSample)) ?></small>
                                <?php else: ?>
                                    <small>No samples yet</small>
                                <?php endif; ?>
                                <span class="fc-arrow">→</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- ═════════════════ LEGEND ═════════════════ -->
        <section class="glass-card legend-card">
            <div class="card-header">
                <div>
                    <h3>🌿 Vegetation Index Guide</h3>
                    <p>Understanding what each index tells you about your crops.</p>
                </div>
            </div>
            <div class="legend-grid">
                <div class="legend-item">
                    <span class="legend-icon">🌿</span>
                    <div>
                        <strong>NDVI</strong>
                        <small>Normalized Difference Vegetation Index — overall vegetation health (0 = bare soil, 1 = dense green).</small>
                    </div>
                </div>
                <div class="legend-item">
                    <span class="legend-icon">🌱</span>
                    <div>
                        <strong>EVI</strong>
                        <small>Enhanced Vegetation Index — reduces atmospheric distortion, better for dense canopies.</small>
                    </div>
                </div>
                <div class="legend-item">
                    <span class="legend-icon">💧</span>
                    <div>
                        <strong>NDWI</strong>
                        <small>Normalized Difference Water Index — detects plant water content and drought stress.</small>
                    </div>
                </div>
                <div class="legend-item">
                    <span class="legend-icon">📊</span>
                    <div>
                        <strong>NDVI Scale</strong>
                        <small>
                            <span class="scale scale-poor">0.0–0.3 Stressed</span>
                            <span class="scale scale-mod">0.3–0.6 Moderate</span>
                            <span class="scale scale-good">0.6–1.0 Healthy</span>
                        </small>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<style>
/* ═══════════ LAYOUT ═══════════ */
html, body { margin: 0 !important; padding: 0 !important; overflow-x: hidden !important; }

.agro-page.dashboard-layout {
    display: flex !important; width: 100% !important;
    min-height: 100vh !important; background-color: #0b1120 !important;
}
.agro-page .main-content {
    flex: 1 !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    padding: 24px 32px 60px !important;
    box-sizing: border-box !important;
}
.agro-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 22px !important;
    margin-bottom: 20px !important;
    box-sizing: border-box !important;
}
.glass-inner {
    background: rgba(15, 23, 42, 0.55) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 16px !important;
}

/* ═══════════ HERO ═══════════ */
.hero-card {
    display: flex !important; justify-content: space-between !important;
    align-items: center !important; gap: 24px !important;
    flex-wrap: wrap !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08)) !important;
    padding: 28px 32px !important;
    position: relative !important; overflow: hidden !important;
}
.hero-card::before {
    content: '' !important; position: absolute !important;
    top: -40% !important; right: -10% !important;
    width: 480px !important; height: 480px !important;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.18), transparent 70%) !important;
    pointer-events: none !important;
}
.hero-left { flex: 1 !important; min-width: 300px !important; position: relative !important; z-index: 1 !important; }
.hero-tag {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    padding: 4px 12px !important;
    background: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important;
    border: 1px solid rgba(16, 185, 129, 0.35) !important;
    border-radius: 999px !important;
    font-size: 10.5px !important; font-weight: 800 !important;
    letter-spacing: 1.2px !important; text-transform: uppercase !important;
    margin-bottom: 10px !important;
}
.live-dot {
    width: 6px !important; height: 6px !important;
    background: #34d399 !important; border-radius: 50% !important;
    animation: pulse 1.6s infinite !important;
    box-shadow: 0 0 8px #34d399 !important;
}
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }

.hero-card h1 { font-size: 28px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px !important; }
.hero-card p  { color: #94a3b8 !important; font-size: 14px !important; margin: 0 0 14px !important; line-height: 1.55 !important; max-width: 620px !important; }
.hero-badges { display: flex !important; gap: 8px !important; flex-wrap: wrap !important; }
.badge-pill {
    padding: 4px 12px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 999px !important;
    font-size: 11px !important; font-weight: 700 !important;
}

.hero-right {
    display: flex !important; align-items: center !important;
    gap: 16px !important;
    position: relative !important; z-index: 1 !important;
    flex-wrap: wrap !important;
}
.weather-chip {
    display: flex !important; align-items: center !important; gap: 12px !important;
    padding: 12px 18px !important;
    background: rgba(0, 0, 0, 0.3) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 14px !important;
}
.wc-icon { font-size: 32px !important; }
.weather-chip strong { display: block !important; font-size: 20px !important; color: #f1f5f9 !important; font-weight: 800 !important; line-height: 1 !important; }
.weather-chip small { display: block !important; font-size: 11.5px !important; color: #94a3b8 !important; margin-top: 3px !important; }
.wc-district { font-weight: 700 !important; color: #38bdf8 !important; }

.btn-hero-primary {
    display: inline-flex !important; align-items: center !important; gap: 8px !important;
    padding: 13px 24px !important; border: none !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important; border-radius: 12px !important;
    font-size: 13.5px !important; font-weight: 700 !important;
    cursor: pointer !important; text-decoration: none !important;
    font-family: inherit !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35) !important;
    transition: all 0.2s !important; white-space: nowrap !important;
}
.btn-hero-primary:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 22px rgba(16, 185, 129, 0.55) !important; }

/* ═══════════ KPI ═══════════ */
.kpi-strip {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) !important;
    gap: 12px !important; margin-bottom: 20px !important;
}
.kpi-card {
    display: flex !important; align-items: center !important; gap: 12px !important;
    padding: 16px 18px !important;
    background: rgba(30, 41, 59, 0.7) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 14px !important;
    position: relative !important; overflow: hidden !important;
    transition: all 0.25s !important; box-sizing: border-box !important;
}
.kpi-card::before {
    content: '' !important; position: absolute !important;
    top: 0 !important; left: 0 !important;
    width: 4px !important; height: 100% !important;
}
.kpi-fields::before   { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.kpi-area::before     { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.kpi-healthy::before  { background: linear-gradient(180deg, #10b981, #059669) !important; }
.kpi-risk::before     { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.kpi-critical::before { background: linear-gradient(180deg, #ef4444, #dc2626) !important; }
.kpi-ndvi::before     { background: linear-gradient(180deg, #34d399, #10b981) !important; }
.kpi-card:hover { transform: translateY(-3px) !important; }
.kpi-icon { font-size: 24px !important; flex-shrink: 0 !important; }
.kpi-label {
    display: block !important; font-size: 10.5px !important;
    color: #94a3b8 !important; text-transform: uppercase !important;
    letter-spacing: 0.6px !important; font-weight: 700 !important;
    margin-bottom: 3px !important;
}
.kpi-value {
    display: block !important; font-size: 20px !important;
    color: #f1f5f9 !important; font-weight: 800 !important;
    line-height: 1.1 !important; font-variant-numeric: tabular-nums !important;
}
.kpi-unit { font-size: 12px !important; color: #64748b !important; margin-left: 2px !important; font-weight: 700 !important; }

/* ═══════════ CARD HEADER ═══════════ */
.card-header {
    display: flex !important; justify-content: space-between !important;
    align-items: flex-start !important; gap: 12px !important;
    margin-bottom: 18px !important; padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    flex-wrap: wrap !important;
}
.card-header h3 {
    font-size: 17px !important; font-weight: 800 !important;
    color: #f1f5f9 !important; margin: 0 0 4px !important;
}
.card-header p { font-size: 12.5px !important; color: #94a3b8 !important; margin: 0 !important; }

.head-btn {
    padding: 10px 18px !important;
    background: rgba(16, 185, 129, 0.12) !important;
    color: #34d399 !important;
    border: 1px solid rgba(16, 185, 129, 0.35) !important;
    border-radius: 10px !important;
    font-size: 12.5px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    text-decoration: none !important;
    transition: all 0.2s !important;
}
.head-btn:hover { background: rgba(16, 185, 129, 0.25) !important; transform: translateY(-1px) !important; }

/* ═══════════ FIELD GRID ═══════════ */
.fields-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)) !important;
    gap: 14px !important;
}
.field-card {
    display: grid !important;
    grid-template-columns: auto 1fr !important;
    gap: 16px !important;
    padding: 18px !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: all 0.25s !important;
    align-items: center !important;
    position: relative !important;
}
.field-card:hover {
    transform: translateY(-4px) !important;
    border-color: rgba(16, 185, 129, 0.4) !important;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35) !important;
}
.fc-ring { flex-shrink: 0 !important; position: relative !important; }
.fc-ring circle { transition: stroke-dashoffset 0.8s ease-out !important; }

.fc-info { min-width: 0 !important; }
.fc-name {
    display: flex !important; align-items: center !important;
    gap: 8px !important; flex-wrap: wrap !important;
    margin-bottom: 4px !important;
}
.fc-name strong {
    font-size: 15px !important; color: #f1f5f9 !important;
    font-weight: 800 !important; white-space: nowrap !important;
    overflow: hidden !important; text-overflow: ellipsis !important;
    max-width: 180px !important;
}
.fc-status {
    padding: 3px 8px !important;
    border-radius: 999px !important;
    font-size: 10px !important; font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}
.fc-status-healthy  { background: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }
.fc-status-moderate { background: rgba(251, 191, 36, 0.15) !important; color: #fbbf24 !important; }
.fc-status-poor     { background: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important; }
.fc-status-neutral  { background: rgba(148, 163, 184, 0.12) !important; color: #94a3b8 !important; }

.fc-crop { font-size: 12px !important; color: #94a3b8 !important; display: block !important; margin-bottom: 8px !important; }
.fc-meta { display: flex !important; gap: 12px !important; font-size: 11.5px !important; color: #64748b !important; flex-wrap: wrap !important; }

.fc-footer {
    grid-column: 1 / -1 !important;
    display: flex !important; justify-content: space-between !important;
    align-items: center !important;
    padding-top: 12px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
    margin-top: 4px !important;
}
.fc-footer small { font-size: 11.5px !important; color: #94a3b8 !important; }
.fc-arrow {
    color: #34d399 !important; font-size: 16px !important;
    font-weight: 800 !important; transition: transform 0.2s !important;
}
.field-card:hover .fc-arrow { transform: translateX(4px) !important; }

/* ═══════════ LEGEND ═══════════ */
.legend-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
    gap: 12px !important;
}
.legend-item {
    display: flex !important; gap: 12px !important;
    padding: 16px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
    border-radius: 12px !important;
    align-items: flex-start !important;
}
.legend-icon { font-size: 22px !important; flex-shrink: 0 !important; }
.legend-item strong {
    display: block !important; font-size: 13.5px !important;
    color: #f1f5f9 !important; font-weight: 800 !important;
    margin-bottom: 4px !important;
}
.legend-item small {
    font-size: 11.5px !important; color: #94a3b8 !important;
    line-height: 1.55 !important; display: block !important;
}
.scale {
    display: inline-block !important;
    padding: 3px 8px !important;
    border-radius: 6px !important;
    font-size: 10.5px !important; font-weight: 700 !important;
    margin: 4px 4px 0 0 !important;
}
.scale-poor { background: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important; }
.scale-mod  { background: rgba(251, 191, 36, 0.15) !important; color: #fbbf24 !important; }
.scale-good { background: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }

/* ═══════════ EMPTY STATE ═══════════ */
.empty-state { text-align: center !important; padding: 60px 30px !important; }
.empty-emoji { font-size: 64px !important; margin-bottom: 14px !important; opacity: 0.55 !important; }
.empty-state h3 { font-size: 17px !important; color: #f1f5f9 !important; margin: 0 0 6px !important; font-weight: 800 !important; }
.empty-state p { color: #94a3b8 !important; font-size: 13.5px !important; margin: 0 0 20px !important; max-width: 460px !important; margin-left: auto !important; margin-right: auto !important; line-height: 1.55 !important; }
.btn-primary {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    padding: 12px 26px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important; border-radius: 10px !important;
    font-size: 13px !important; font-weight: 700 !important;
    text-decoration: none !important; border: none !important;
    cursor: pointer !important; font-family: inherit !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
    transition: all 0.2s !important;
}
.btn-primary:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5) !important; }

/* ═══════════ RESPONSIVE ═══════════ */
@media (max-width: 900px) {
    .agro-page .main-content {
        width: 100% !important; max-width: 100% !important;
        padding: 20px !important; padding-top: 72px !important;
    }
    .hero-card { flex-direction: column !important; align-items: flex-start !important; }
    .hero-right { width: 100% !important; justify-content: space-between !important; }
    .fields-grid { grid-template-columns: 1fr !important; }
}
@media (max-width: 640px) {
    .kpi-strip { grid-template-columns: repeat(2, 1fr) !important; }
    .hero-card h1 { font-size: 22px !important; }
    .weather-chip { width: 100% !important; }
}
</style>