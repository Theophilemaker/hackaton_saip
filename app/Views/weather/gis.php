<?php
$currentYear = (int)date('Y');
$districts = ['Bugesera','Burera','Gakenke','Gasabo','Gatsibo','Gicumbi','Gisagara','Huye','Kamonyi','Karongi','Kayonza','Kicukiro','Kirehe','Muhanga','Musanze','Ngoma','Ngororero','Nyabihu','Nyagatare','Nyamagabe','Nyamasheke','Nyanza','Nyarugenge','Nyaruguru','Rubavu','Ruhango','Rulindo','Rusizi','Rutsiro','Rwamagana'];
?>
<div class="dashboard-layout gis-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="main-content">

            <!-- ═══════════ HERO ═══════════ -->
            <section class="glass-card gis-hero">
                <div class="hero-left">
                    <span class="hero-tag"><span class="live-dot"></span> SPATIAL INTELLIGENCE</span>
                    <h1>🗺️ Rwanda GIS Analytics</h1>
                    <p>Interactive district-level intelligence across production, weather, irrigation, and pest risk.</p>
                </div>
                <div class="hero-actions">
                    <button type="button" class="btn-hero-secondary" id="btnFullscreen">⛶ Fullscreen</button>
                    <button type="button" class="btn-hero-secondary" onclick="window.print()">📥 Export</button>
                    <a href="<?= BASE_URL ?>/weather" class="btn-hero-primary">🌤️ Weather</a>
                </div>
            </section>

            <!-- ═══════════ CONTROL BAR (EXTENDED) ═══════════ -->
            <section class="glass-card control-bar">

                <!-- Base Map Switcher -->
                <div class="control-group">
                    <label class="control-label">🛰️ Base Map</label>
                    <div class="base-tiles">
                        <button type="button" class="tile-btn active" data-base="dark">
                            <span class="tile-preview tile-dark"></span>
                            <span>Dark</span>
                        </button>
                        <button type="button" class="tile-btn" data-base="satellite">
                            <span class="tile-preview tile-satellite"></span>
                            <span>Satellite</span>
                        </button>
                        <button type="button" class="tile-btn" data-base="streets">
                            <span class="tile-preview tile-streets"></span>
                            <span>Streets</span>
                        </button>
                        <button type="button" class="tile-btn" data-base="terrain">
                            <span class="tile-preview tile-terrain"></span>
                            <span>Terrain</span>
                        </button>
                        <button type="button" class="tile-btn" data-base="topo">
                            <span class="tile-preview tile-topo"></span>
                            <span>Topo</span>
                        </button>
                        <button type="button" class="tile-btn" data-base="humanitarian">
                            <span class="tile-preview tile-humanitarian"></span>
                            <span>Humanitarian</span>
                        </button>
                    </div>
                </div>

                <!-- Year + Date -->
                <div class="control-group">
                    <label class="control-label">📅 Period</label>
                    <select class="year-select" id="yearSelect">
                        <?php for ($y = $currentYear; $y >= 2000; $y--): ?>
                            <option value="<?= $y ?>" <?= $y === $currentYear ? 'selected' : '' ?>>
                                <?= $y ?><?= $y === $currentYear ? ' (Current)' : '' ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <input type="date" class="date-input" id="dateSelect"
                           value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                </div>

                <!-- Data Layer -->
                <div class="control-group">
                    <label class="control-label">🎯 Data Layer</label>
                    <select class="layer-select" id="layerSelect">
                        <option value="production">🌾 Crop Production</option>
                        <option value="rainfall">🌧️ Rainfall</option>
                        <option value="irrigation">💧 Irrigation</option>
                        <option value="pests">🐛 Pest Outbreaks</option>
                        <option value="soil">🌱 Soil Fertility</option>
                    </select>
                </div>

                <!-- Overlays -->
                <div class="control-group">
                    <label class="control-label">👁️ Overlays</label>
                    <div class="overlay-toggles">
                        <label class="mini-toggle">
                            <input type="checkbox" id="overlayLabels" checked>
                            <span>🏷️ Labels</span>
                        </label>
                        <label class="mini-toggle">
                            <input type="checkbox" id="overlayGraticule">
                            <span>📐 Grid</span>
                        </label>
                        <label class="mini-toggle">
                            <input type="checkbox" id="overlayScale" checked>
                            <span>📏 Scale</span>
                        </label>
                    </div>
                </div>

                <!-- ⭐ NEW: Live Weather Chip -->
                <div class="control-group">
                    <label class="control-label">🌤️ Current Weather</label>
                    <div class="live-weather-chip" id="liveWeatherChip">
                        <div class="weather-row">
                            <span class="weather-emoji" id="weatherEmoji">⛅</span>
                            <div class="weather-text">
                                <strong id="weatherTemp">--°C</strong>
                                <small id="weatherDesc">Loading…</small>
                            </div>
                        </div>
                        <div class="weather-meta">
                            <span>💧 <span id="weatherHumidity">--</span>%</span>
                            <span>💨 <span id="weatherWind">--</span> km/h</span>
                        </div>
                    </div>
                </div>

                <!-- ⭐ NEW: Quick District Jump -->
                <div class="control-group">
                    <label class="control-label">🔍 Jump to District</label>
                    <select class="district-jump" id="districtJump">
                        <option value="">— Select district —</option>
                        <?php foreach ($districts as $d): ?>
                            <option value="<?= $d ?>"><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </section>

            <!-- ═══════════ MAIN GRID: MAP + RIGHT WIDGETS ═══════════ -->
            <div class="main-grid">

                <!-- LEFT: MAP (takes most space) -->
                <section class="glass-card map-card">
                    <div class="map-head">
                        <div>
                            <h3 id="mapTitle">🌾 Crop Production Heatmap</h3>
                            <p id="mapSubtitle">Total yield (kg) by district · <?= $currentYear ?></p>
                        </div>
                        <div class="map-head-actions">
                            <span class="coord-display" id="coordDisplay">0.0000° N, 0.0000° E</span>
                            <button type="button" class="icon-btn" onclick="resetView()" title="Reset">🏠</button>
                            <button type="button" class="icon-btn" onclick="locateMe()" title="My location">🎯</button>
                        </div>
                    </div>

                    <div class="map-wrapper">
                        <div id="map"></div>

                        <div class="legend-overlay">
                            <h5 id="legendTitle">🌾 Crop Production</h5>
                            <div class="legend-bar">
                                <span class="legend-min">Low</span>
                                <div class="legend-gradient" id="legendGradient"></div>
                                <span class="legend-max">High</span>
                            </div>
                            <div class="legend-values">
                                <span id="legendLowVal">0</span>
                                <span id="legendMidVal">—</span>
                                <span id="legendHighVal">—</span>
                            </div>
                        </div>

                        <div class="stats-overlay">
                            <div class="stat-mini">
                                <span class="stat-mini-label">Districts</span>
                                <strong class="stat-mini-value" id="miniDistricts">31</strong>
                            </div>
                            <div class="stat-mini">
                                <span class="stat-mini-label">Total</span>
                                <strong class="stat-mini-value" id="miniTotal">—</strong>
                            </div>
                            <div class="stat-mini">
                                <span class="stat-mini-label">Average</span>
                                <strong class="stat-mini-value" id="miniAverage">—</strong>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Strip -->
                    <div class="map-stats-grid">
                        <div class="stat-box">
                            <span class="stat-label">📍 Districts</span>
                            <strong class="stat-value" id="statDistricts">31</strong>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">🏆 Highest</span>
                            <strong class="stat-value" id="statHighest">—</strong>
                            <small class="stat-sub" id="statHighestName"></small>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">📊 Average</span>
                            <strong class="stat-value" id="statAverage">—</strong>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">Σ Total</span>
                            <strong class="stat-value" id="statTotal">—</strong>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">📉 Lowest</span>
                            <strong class="stat-value" id="statLowest">—</strong>
                            <small class="stat-sub" id="statLowestName"></small>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">🎯 Coverage</span>
                            <strong class="stat-value" id="statCoverage">100%</strong>
                        </div>
                    </div>
                </section>

                <!-- RIGHT: SIDEBAR WIDGETS ⭐ -->
                <aside class="right-widgets">

                    <!-- Top Districts -->
                    <section class="glass-card widget-card">
                        <div class="widget-head">
                            <div>
                                <h4>🏆 Top Performers</h4>
                                <p id="topSubtitle">By production</p>
                            </div>
                            <span class="widget-badge">Top 5</span>
                        </div>
                        <div class="ranked-list" id="topDistricts">Loading…</div>
                    </section>

                    <!-- Needs Attention -->
                    <section class="glass-card widget-card">
                        <div class="widget-head">
                            <div>
                                <h4>⚠️ Needs Attention</h4>
                                <p id="bottomSubtitle">Lowest performers</p>
                            </div>
                            <span class="widget-badge warn">Bottom 5</span>
                        </div>
                        <div class="ranked-list" id="bottomDistricts">Loading…</div>
                    </section>

                    <!-- ⭐ NEW: Regional Comparison -->
                    <section class="glass-card widget-card">
                        <div class="widget-head">
                            <div>
                                <h4>🌍 Regional Comparison</h4>
                                <p>District vs national average</p>
                            </div>
                        </div>
                        <div class="region-compare" id="regionCompare">
                            <div class="region-row">
                                <span class="region-label">🇷🇼 National</span>
                                <div class="region-bar-wrap">
                                    <div class="region-bar national" style="width: 100%"></div>
                                </div>
                                <strong class="region-value" id="nationalAvg">—</strong>
                            </div>
                            <div class="region-row">
                                <span class="region-label" id="selectedDistrictLabel">📍 Select district</span>
                                <div class="region-bar-wrap">
                                    <div class="region-bar district" id="districtBar" style="width: 0%"></div>
                                </div>
                                <strong class="region-value" id="districtAvg">—</strong>
                            </div>
                        </div>
                    </section>

                    <!-- ⭐ NEW: Insights -->
                    <section class="glass-card widget-card insights-widget">
                        <div class="widget-head">
                            <div>
                                <h4>💡 Auto Insights</h4>
                                <p>Generated from current view</p>
                            </div>
                        </div>
                        <div class="insights-list" id="insightsList">
                            <div class="insight-item">
                                <span class="insight-icon">📊</span>
                                <span>Analyzing data…</span>
                            </div>
                        </div>
                    </section>

                    <!-- Quick Actions -->
                    <section class="glass-card widget-card">
                        <div class="widget-head">
                            <div>
                                <h4>⚡ Quick Actions</h4>
                            </div>
                        </div>
                        <div class="quick-actions-list">
                            <a href="<?= BASE_URL ?>/advice" class="quick-action">
                                <span class="qa-icon">🌱</span>
                                <div>
                                    <strong>Crop Advice</strong>
                                    <small>What to plant</small>
                                </div>
                                <span class="qa-arrow">→</span>
                            </a>
                            <a href="<?= BASE_URL ?>/marketplace" class="quick-action">
                                <span class="qa-icon">🛒</span>
                                <div>
                                    <strong>Marketplace</strong>
                                    <small>Buy & sell</small>
                                </div>
                                <span class="qa-arrow">→</span>
                            </a>
                            <a href="<?= BASE_URL ?>/weather" class="quick-action">
                                <span class="qa-icon">🌤️</span>
                                <div>
                                    <strong>Weather</strong>
                                    <small>5-day forecast</small>
                                </div>
                                <span class="qa-arrow">→</span>
                            </a>
                            <a href="<?= BASE_URL ?>/logistics" class="quick-action">
                                <span class="qa-icon">🚛</span>
                                <div>
                                    <strong>Logistics</strong>
                                    <small>Transport</small>
                                </div>
                                <span class="qa-arrow">→</span>
                            </a>
                        </div>
                    </section>

                    <!-- Coord Info -->
                    <section class="glass-card widget-card">
                        <div class="widget-head">
                            <div>
                                <h4>🌐 Map Info</h4>
                            </div>
                        </div>
                        <div class="coord-grid">
                            <div class="coord-item">
                                <span class="coord-label">Center Lat</span>
                                <strong id="coordLat">-1.9403</strong>
                            </div>
                            <div class="coord-item">
                                <span class="coord-label">Center Lng</span>
                                <strong id="coordLng">29.8739</strong>
                            </div>
                            <div class="coord-item">
                                <span class="coord-label">Zoom</span>
                                <strong id="coordZoom">8</strong>
                            </div>
                            <div class="coord-item">
                                <span class="coord-label">Base</span>
                                <strong id="coordBase">Dark</strong>
                            </div>
                        </div>
                    </section>

                </aside>
            </div>

        </main>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
(function () {
    'use strict';

    var BASE = '<?= BASE_URL ?>';
    var CURRENT_YEAR = <?= $currentYear ?>;

    // ═══════ INIT MAP ═══════
    // ═══════ INIT MAP — with robust size detection ═══════
var map = L.map('map', {
    center: [-1.9403, 29.8739],
    zoom: 8,
    zoomControl: true,
    attributionControl: true,
    preferCanvas: true
});

// Force the correct starting view (in case Leaflet got confused)
map.setView([-1.9403, 29.8739], 8);

// Recalculate size at multiple intervals to handle slow layout
(function ensureMapSize() {
    var tries = 0;
    var maxTries = 20;

    function attempt() {
        tries++;
        var el = document.getElementById('map');
        if (el && el.offsetWidth > 0 && el.offsetHeight > 0) {
            map.invalidateSize();
            // Only reset view if it's still wrong
            var c = map.getCenter();
            if (Math.abs(c.lat - (-1.9403)) > 10 || Math.abs(c.lng - 29.8739) > 10 || map.getZoom() === 0) {
                map.setView([-1.9403, 29.8739], 8);
            }
        }
        if (tries < maxTries) {
            setTimeout(attempt, 100);
        }
    }
    attempt();
})();

// Listen for window resize
window.addEventListener('resize', function () {
    map.invalidateSize();
});

// Listen for container resize (if supported)
if (window.ResizeObserver) {
    new ResizeObserver(function () {
        map.invalidateSize();
    }).observe(document.getElementById('map'));
}

    // ═══════ BASE TILE LAYERS ═══════
    var BASE_LAYERS = {
        dark: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 16,
            attribution: '&copy; <a href="https://www.esri.com/">Esri</a>'
        }),
        streets: L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }),
        satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: '&copy; Esri, Maxar'
        }),
        terrain: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            subdomains: 'abc',
            maxZoom: 17,
            attribution: '&copy; OpenTopoMap'
        }),
        topo: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: '&copy; Esri'
        }),
        humanitarian: L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
            subdomains: 'abc',
            maxZoom: 19,
            attribution: '&copy; OSM France'
        })
    };

    var currentBase = 'dark';
    BASE_LAYERS.dark.addTo(map);

    // ═══════ DARK THEME CSS ═══════
    var styleEl = document.createElement('style');
    styleEl.textContent = `
        .leaflet-container { background: #0b1120 !important; font-family: inherit !important; }
        .leaflet-control-attribution {
            background: rgba(15,23,42,0.9) !important;
            color: #94a3b8 !important;
            font-size: 10px !important;
            padding: 2px 8px !important;
            border-radius: 6px 0 0 0 !important;
        }
        .leaflet-control-attribution a { color: #38bdf8 !important; }
        .leaflet-bar { background: rgba(30,41,59,0.95) !important; backdrop-filter: blur(12px) !important; border: 1px solid rgba(255,255,255,0.1) !important; box-shadow: 0 8px 24px rgba(0,0,0,0.5) !important; border-radius: 12px !important; overflow: hidden !important; }
        .leaflet-bar a { background: transparent !important; color: #cbd5e1 !important; border-bottom: 1px solid rgba(255,255,255,0.06) !important; font-size: 18px !important; width: 36px !important; height: 36px !important; line-height: 36px !important; }
        .leaflet-bar a:hover { background: rgba(56,189,248,0.15) !important; color: #38bdf8 !important; }
        .leaflet-popup-content-wrapper { background: rgba(30,41,59,0.98) !important; color: #f1f5f9 !important; border: 1px solid rgba(56,189,248,0.3) !important; border-radius: 14px !important; box-shadow: 0 16px 48px rgba(0,0,0,0.6) !important; }
        .leaflet-popup-tip { background: rgba(30,41,59,0.98) !important; }
        .leaflet-popup-close-button { color: #94a3b8 !important; font-size: 18px !important; }
        .district-label { background: transparent !important; border: none !important; pointer-events: none !important; }
        .district-label span { display: inline-block !important; padding: 3px 9px !important; background: rgba(15,23,42,0.9) !important; color: #f1f5f9 !important; font-size: 10.5px !important; font-weight: 700 !important; border-radius: 6px !important; border: 1px solid rgba(56,189,248,0.35) !important; white-space: nowrap !important; box-shadow: 0 3px 10px rgba(0,0,0,0.5) !important; }
        .leaflet-control-scale-line { background: rgba(15,23,42,0.9) !important; color: #cbd5e1 !important; border: 1px solid rgba(56,189,248,0.3) !important; border-top: none !important; font-size: 10px !important; padding: 2px 6px !important; }
    `;
    document.head.appendChild(styleEl);

    var scaleControl = L.control.scale({ position: 'bottomright', imperial: false }).addTo(map);

    // ═══════ COORDINATE TRACKING ═══════
    map.on('mousemove', function (e) {
        document.getElementById('coordDisplay').textContent =
            e.latlng.lat.toFixed(4) + '° ' + e.latlng.lng.toFixed(4) + '°';
    });

    map.on('move zoom', function () {
        var c = map.getCenter();
        setText('coordLat', c.lat.toFixed(4));
        setText('coordLng', c.lng.toFixed(4));
        setText('coordZoom', map.getZoom());
    });

    // ═══════ DISTRICTS DATA ═══════
    var districts = [
        { name: 'Kigali',     lat: -1.9441, lng: 30.0619, production: 45000,  rainfall: 950,  irrigation: 42, pests: 1, soil: 78 },
        { name: 'Musanze',    lat: -1.4999, lng: 29.6349, production: 120000, rainfall: 1200, irrigation: 28, pests: 2, soil: 85 },
        { name: 'Huye',       lat: -2.5967, lng: 29.7392, production: 78000,  rainfall: 1100, irrigation: 22, pests: 1, soil: 72 },
        { name: 'Nyagatare',  lat: -1.2939, lng: 30.3275, production: 92000,  rainfall: 800,  irrigation: 18, pests: 3, soil: 65 },
        { name: 'Rubavu',     lat: -1.6779, lng: 29.2579, production: 65000,  rainfall: 1350, irrigation: 35, pests: 1, soil: 80 },
        { name: 'Rusizi',     lat: -2.4833, lng: 29.0000, production: 88000,  rainfall: 1400, irrigation: 30, pests: 2, soil: 82 },
        { name: 'Kayonza',    lat: -1.8833, lng: 30.6167, production: 71000,  rainfall: 850,  irrigation: 15, pests: 3, soil: 68 },
        { name: 'Karongi',    lat: -2.0500, lng: 29.3500, production: 58000,  rainfall: 1250, irrigation: 24, pests: 1, soil: 76 },
        { name: 'Nyamagabe',  lat: -2.4667, lng: 29.4500, production: 62000,  rainfall: 1300, irrigation: 20, pests: 2, soil: 74 },
        { name: 'Rwamagana',  lat: -1.9500, lng: 30.4333, production: 75000,  rainfall: 900,  irrigation: 26, pests: 2, soil: 70 },
        { name: 'Muhanga',    lat: -2.0833, lng: 29.7500, production: 68000,  rainfall: 1050, irrigation: 23, pests: 1, soil: 73 },
        { name: 'Gicumbi',    lat: -1.5833, lng: 30.1000, production: 72000,  rainfall: 1150, irrigation: 25, pests: 2, soil: 79 },
        { name: 'Rulindo',    lat: -1.7667, lng: 30.0667, production: 56000,  rainfall: 1080, irrigation: 21, pests: 1, soil: 77 },
        { name: 'Gakenke',    lat: -1.7000, lng: 29.7833, production: 64000,  rainfall: 1220, irrigation: 22, pests: 1, soil: 81 },
        { name: 'Burera',     lat: -1.4667, lng: 29.9167, production: 70000,  rainfall: 1280, irrigation: 27, pests: 2, soil: 83 },
        { name: 'Nyabihu',    lat: -1.6667, lng: 29.5000, production: 66000,  rainfall: 1320, irrigation: 29, pests: 1, soil: 84 },
        { name: 'Ngororero',  lat: -1.8667, lng: 29.6000, production: 52000,  rainfall: 1180, irrigation: 19, pests: 1, soil: 75 },
        { name: 'Rutsiro',    lat: -1.9333, lng: 29.3500, production: 54000,  rainfall: 1240, irrigation: 21, pests: 1, soil: 76 },
        { name: 'Nyamasheke', lat: -2.3500, lng: 29.1333, production: 60000,  rainfall: 1380, irrigation: 26, pests: 1, soil: 78 },
        { name: 'Gisagara',   lat: -2.4500, lng: 29.8333, production: 58000,  rainfall: 1020, irrigation: 20, pests: 2, soil: 71 },
        { name: 'Nyanza',     lat: -2.3500, lng: 29.7500, production: 61000,  rainfall: 1080, irrigation: 22, pests: 1, soil: 72 },
        { name: 'Ruhango',    lat: -2.1500, lng: 29.7833, production: 63000,  rainfall: 1050, irrigation: 21, pests: 1, soil: 73 },
        { name: 'Kamonyi',    lat: -2.0000, lng: 29.9000, production: 64000,  rainfall: 980,  irrigation: 23, pests: 1, soil: 74 },
        { name: 'Nyaruguru',  lat: -2.6167, lng: 29.5667, production: 54000,  rainfall: 1150, irrigation: 18, pests: 2, soil: 72 },
        { name: 'Bugesera',   lat: -2.2194, lng: 30.1244, production: 67000,  rainfall: 850,  irrigation: 16, pests: 3, soil: 66 },
        { name: 'Ngoma',      lat: -2.1500, lng: 30.5667, production: 69000,  rainfall: 900,  irrigation: 17, pests: 3, soil: 67 },
        { name: 'Kirehe',     lat: -2.2833, lng: 30.7167, production: 73000,  rainfall: 880,  irrigation: 15, pests: 3, soil: 68 },
        { name: 'Gatsibo',    lat: -1.6000, lng: 30.4333, production: 77000,  rainfall: 870,  irrigation: 16, pests: 2, soil: 69 },
        { name: 'Gasabo',     lat: -1.9167, lng: 30.1167, production: 48000,  rainfall: 940,  irrigation: 45, pests: 1, soil: 76 },
        { name: 'Kicukiro',   lat: -1.9833, lng: 30.1000, production: 42000,  rainfall: 930,  irrigation: 48, pests: 1, soil: 75 },
        { name: 'Nyarugenge', lat: -1.9500, lng: 30.0500, production: 44000,  rainfall: 920,  irrigation: 50, pests: 1, soil: 74 }
    ];

    var LAYER_CONFIG = {
        production: { title: '🌾 Crop Production Heatmap', subtitle: 'Total yield (kg) by district', unit: ' kg', palette: ['#065f46', '#10b981', '#34d399'], gradient: 'linear-gradient(90deg, #065f46, #10b981, #34d399)' },
        rainfall:   { title: '🌧️ Rainfall Distribution',  subtitle: 'Annual rainfall (mm) by district', unit: ' mm', palette: ['#0c4a6e', '#0284c7', '#38bdf8'], gradient: 'linear-gradient(90deg, #0c4a6e, #0284c7, #38bdf8)' },
        irrigation: { title: '💧 Irrigation Coverage',    subtitle: '% of farms with irrigation',    unit: '%',    palette: ['#134e4a', '#0891b2', '#22d3ee'], gradient: 'linear-gradient(90deg, #134e4a, #0891b2, #22d3ee)' },
        pests:      { title: '🐛 Pest Outbreaks',         subtitle: 'Active pest alerts per district', unit: ' alerts', palette: ['#7f1d1d', '#dc2626', '#f87171'], gradient: 'linear-gradient(90deg, #7f1d1d, #dc2626, #f87171)' },
        soil:       { title: '🌱 Soil Fertility',         subtitle: 'Nutrient levels (out of 100)',  unit: '/100', palette: ['#4c1d95', '#7c3aed', '#a78bfa'], gradient: 'linear-gradient(90deg, #4c1d95, #7c3aed, #a78bfa)' }
    };

    var currentLayer = 'production';
    var currentYear = CURRENT_YEAR;
    var layers = [];
    var labelsVisible = true;

    // ═══════ RENDER LAYER ═══════
    function renderLayer(key, year) {
        var cfg = LAYER_CONFIG[key];

        layers.forEach(function (l) { map.removeLayer(l); });
        layers = [];

        setText('mapTitle', cfg.title);
        setText('mapSubtitle', cfg.subtitle + ' · ' + year);
        setText('legendTitle', cfg.title);
        var lg = document.getElementById('legendGradient');
        if (lg) lg.style.background = cfg.gradient;

        var values = districts.map(function (d) { return d[key] || 0; });
        var maxVal = Math.max.apply(null, values);
        var minVal = Math.min.apply(null, values.filter(function (v) { return v > 0; })) || 0;
        var sumVal = values.reduce(function (a, b) { return a + b; }, 0);
        var avgVal = sumVal / (values.length || 1);

        var sorted = districts.slice().sort(function (a, b) { return (b[key] || 0) - (a[key] || 0); });
        var top = sorted[0];
        var bottom = sorted[sorted.length - 1];

        setText('statDistricts', districts.length);
        setText('statHighest', fmt(maxVal) + cfg.unit);
        setText('statHighestName', top.name);
        setText('statAverage', fmt(Math.round(avgVal)) + cfg.unit);
        setText('statTotal', fmt(sumVal) + cfg.unit);
        setText('statLowest', fmt(minVal) + cfg.unit);
        setText('statLowestName', bottom.name);
        setText('statCoverage', '100%');

        setText('miniDistricts', districts.length);
        setText('miniTotal', fmtShort(sumVal) + cfg.unit);
        setText('miniAverage', fmtShort(avgVal) + cfg.unit);

        setText('legendLowVal', fmt(minVal));
        setText('legendMidVal', fmt(Math.round((minVal + maxVal) / 2)));
        setText('legendHighVal', fmt(maxVal));

        districts.forEach(function (d) {
            var val = d[key] || 0;
            var ratio = maxVal > minVal ? (val - minVal) / (maxVal - minVal) : 0.5;
            var color = ratio > 0.66 ? cfg.palette[2] : (ratio > 0.33 ? cfg.palette[1] : cfg.palette[0]);
            var radius = 10000 + ratio * 25000;

            var circle = L.circle([d.lat, d.lng], {
                radius: radius,
                fillColor: color,
                color: color,
                weight: 2,
                opacity: 0.9,
                fillOpacity: 0.55
            });

            circle.bindPopup(buildPopup(d, color));
            circle.on('click', function () { updateRegionalCompare(d, avgVal, key, cfg.unit); });
            circle.addTo(map);
            layers.push(circle);

            if (labelsVisible) {
                var label = L.marker([d.lat, d.lng], {
                    icon: L.divIcon({
                        className: 'district-label',
                        html: '<span>' + d.name + '</span>',
                        iconSize: [80, 20],
                        iconAnchor: [40, -18]
                    })
                });
                label.addTo(map);
                layers.push(label);
            }
        });

        renderTopBottom(sorted, key, cfg);
        updateInsights(sorted, key, cfg, avgVal, sumVal);
        setText('nationalAvg', fmtShort(avgVal) + cfg.unit);
    }

    function buildPopup(d, color) {
        return '<div style="min-width: 220px;">' +
            '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid rgba(255,255,255,0.08);">' +
                '<strong style="font-size:16px; color:' + color + ';">' + d.name + '</strong>' +
                '<span style="font-size:10px; color:#64748b; text-transform:uppercase;">Rwanda</span>' +
            '</div>' +
            '<div style="font-size:12.5px; line-height:1.8;">' +
                '<div style="display:flex; justify-content:space-between;"><span>🌾 Production</span><strong>' + fmt(d.production) + ' kg</strong></div>' +
                '<div style="display:flex; justify-content:space-between;"><span>🌧️ Rainfall</span><strong>' + d.rainfall + ' mm</strong></div>' +
                '<div style="display:flex; justify-content:space-between;"><span>💧 Irrigation</span><strong>' + d.irrigation + '%</strong></div>' +
                '<div style="display:flex; justify-content:space-between;"><span>🐛 Pest alerts</span><strong>' + d.pests + '</strong></div>' +
                '<div style="display:flex; justify-content:space-between;"><span>🌱 Soil</span><strong>' + d.soil + '/100</strong></div>' +
            '</div>' +
        '</div>';
    }

    function renderTopBottom(sorted, key, cfg) {
        var topEl = document.getElementById('topDistricts');
        var bottomEl = document.getElementById('bottomDistricts');

        if (topEl) {
            topEl.innerHTML = sorted.slice(0, 5).map(function (d, i) {
                var medal = i === 0 ? '🥇' : (i === 1 ? '🥈' : (i === 2 ? '🥉' : '#' + (i + 1)));
                return '<div class="rank-row">' +
                    '<span class="rank-medal">' + medal + '</span>' +
                    '<span class="rank-name">' + d.name + '</span>' +
                    '<strong class="rank-value">' + fmtShort(d[key]) + cfg.unit + '</strong>' +
                '</div>';
            }).join('');
        }
        if (bottomEl) {
            bottomEl.innerHTML = sorted.slice(-5).reverse().map(function (d, i) {
                return '<div class="rank-row">' +
                    '<span class="rank-medal warn">#' + (i + 1) + '</span>' +
                    '<span class="rank-name">' + d.name + '</span>' +
                    '<strong class="rank-value warn">' + fmtShort(d[key]) + cfg.unit + '</strong>' +
                '</div>';
            }).join('');
        }
    }

    function updateRegionalCompare(d, avg, key, unit) {
        setText('selectedDistrictLabel', '📍 ' + d.name);
        var val = d[key] || 0;
        var pct = avg > 0 ? Math.min(100, (val / (avg * 2)) * 100) : 0;
        var bar = document.getElementById('districtBar');
        if (bar) bar.style.width = pct + '%';
        setText('districtAvg', fmtShort(val) + unit);
    }

    function updateInsights(sorted, key, cfg, avg, sum) {
        var top = sorted[0];
        var bottom = sorted[sorted.length - 1];
        var topPct = sum > 0 ? ((top[key] / sum) * 100).toFixed(1) : 0;
        var aboveAvg = districts.filter(function (d) { return (d[key] || 0) > avg; }).length;

        var insights = [
            { icon: '🏆', text: '<strong>' + top.name + '</strong> leads with ' + fmt(top[key]) + cfg.unit + ' (' + topPct + '% of total)' },
            { icon: '📉', text: '<strong>' + bottom.name + '</strong> needs attention at ' + fmt(bottom[key]) + cfg.unit },
            { icon: '📊', text: '<strong>' + aboveAvg + '</strong> of ' + districts.length + ' districts perform above average' },
            { icon: '💡', text: 'National average: <strong>' + fmt(Math.round(avg)) + cfg.unit + '</strong>' }
        ];

        var html = insights.map(function (i) {
            return '<div class="insight-item"><span class="insight-icon">' + i.icon + '</span><span>' + i.text + '</span></div>';
        }).join('');

        var el = document.getElementById('insightsList');
        if (el) el.innerHTML = html;
    }

    // ═══════ LIVE WEATHER CHIP ═══════
    function loadWeather() {
        var district = '<?= e(current_district()) ?>';
        fetch(BASE + '/weather/byDistrict?district=' + encodeURIComponent(district), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success && data.weather && data.weather.current) {
                    var c = data.weather.current;
                    var icons = { '01d':'☀️','01n':'🌙','02d':'⛅','02n':'☁️','03d':'☁️','04d':'☁️','09d':'🌧️','10d':'🌦️','11d':'⛈️','13d':'❄️','50d':'🌫️' };
                    setText('weatherEmoji', icons[c.icon] || '🌤️');
                    setText('weatherTemp', c.temp + '°C');
                    setText('weatherDesc', c.description + ' · ' + district);
                    setText('weatherHumidity', c.humidity);
                    setText('weatherWind', c.wind_speed);
                }
            })
            .catch(function () { setText('weatherDesc', 'Unavailable'); });
    }

    // ═══════ HELPERS ═══════
    function fmt(n) { return Number(n || 0).toLocaleString('en-US', { maximumFractionDigits: 0 }); }
    function fmtShort(n) {
        n = Number(n || 0);
        if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
        if (n >= 1000) return (n / 1000).toFixed(0) + 'K';
        return Math.round(n);
    }
    function setText(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    // ═══════ BASE LAYER SWITCHING ═══════
    document.querySelectorAll('.tile-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.tile-btn').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');

            var newBase = this.getAttribute('data-base');
            Object.keys(BASE_LAYERS).forEach(function (k) {
                if (map.hasLayer(BASE_LAYERS[k])) map.removeLayer(BASE_LAYERS[k]);
            });
            BASE_LAYERS[newBase].addTo(map);
            BASE_LAYERS[newBase].bringToBack();
            currentBase = newBase;

            var filterMap = {
                dark: 'none',
                satellite: 'brightness(0.85) contrast(1.1) saturate(1.05)',
                streets: 'brightness(0.92)',
                terrain: 'brightness(0.9)',
                topo: 'brightness(0.9)',
                humanitarian: 'brightness(0.95)'
            };
            var filterStyle = document.getElementById('map-tile-filter');
            if (!filterStyle) {
                filterStyle = document.createElement('style');
                filterStyle.id = 'map-tile-filter';
                document.head.appendChild(filterStyle);
            }
            filterStyle.textContent = '.leaflet-tile-pane { filter: ' + (filterMap[newBase] || 'none') + ' !important; }';
            setText('coordBase', newBase.charAt(0).toUpperCase() + newBase.slice(1));
        });
    });

    // ═══════ LAYER SELECT ═══════
    document.getElementById('layerSelect')?.addEventListener('change', function () {
        currentLayer = this.value;
        renderLayer(currentLayer, currentYear);
    });

    // ═══════ YEAR SELECT ═══════
    document.getElementById('yearSelect')?.addEventListener('change', function () {
        currentYear = this.value;
        renderLayer(currentLayer, currentYear);
    });

    // ═══════ OVERLAYS ═══════
    document.getElementById('overlayLabels')?.addEventListener('change', function () {
        labelsVisible = this.checked;
        renderLayer(currentLayer, currentYear);
    });

    document.getElementById('overlayScale')?.addEventListener('change', function () {
        if (this.checked) scaleControl.addTo(map);
        else map.removeControl(scaleControl);
    });

    var graticuleLayer = null;
    document.getElementById('overlayGraticule')?.addEventListener('change', function () {
        if (this.checked) {
            var lines = [];
            for (var lat = -3; lat <= -1; lat += 0.5) {
                lines.push(L.polyline([[-lat, 28.5], [-lat, 31]], { color: '#38bdf8', weight: 1, opacity: 0.25, dashArray: '4,4', interactive: false }));
            }
            for (var lng = 29; lng <= 31; lng += 0.5) {
                lines.push(L.polyline([[-3, lng], [-1, lng]], { color: '#38bdf8', weight: 1, opacity: 0.25, dashArray: '4,4', interactive: false }));
            }
            graticuleLayer = L.layerGroup(lines).addTo(map);
        } else if (graticuleLayer) {
            map.removeLayer(graticuleLayer);
        }
    });

    // ═══════ DISTRICT JUMP ═══════
    document.getElementById('districtJump')?.addEventListener('change', function () {
        var name = this.value;
        if (!name) return;
        var d = districts.find(function (x) { return x.name === name; });
        if (d) {
            map.setView([d.lat, d.lng], 11);
            // Find and open the corresponding circle
            layers.forEach(function (l) {
                if (l instanceof L.Circle && l.getLatLng().lat === d.lat && l.getLatLng().lng === d.lng) {
                    l.openPopup();
                }
            });
        }
    });

    // ═══════ FULLSCREEN ═══════
    document.getElementById('btnFullscreen')?.addEventListener('click', function () {
        var el = document.querySelector('.map-wrapper');
        if (!document.fullscreenElement) {
            el.requestFullscreen();
            setTimeout(function () { map.invalidateSize(); }, 200);
            this.textContent = '⛶ Exit';
        } else {
            document.exitFullscreen();
            setTimeout(function () { map.invalidateSize(); }, 200);
            this.textContent = '⛶ Fullscreen';
        }
    });

    // ═══════ GLOBAL HELPERS ═══════
    window.resetView = function () { map.setView([-1.9403, 29.8739], 8); };
    window.locateMe = function () {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(function (pos) {
            map.setView([pos.coords.latitude, pos.coords.longitude], 12);
            L.marker([pos.coords.latitude, pos.coords.longitude], {
                icon: L.divIcon({
                    className: 'user-pin',
                    html: '<div style="background:#10b981; width:16px; height:16px; border-radius:50%; border:3px solid #fff; box-shadow:0 0 12px rgba(16,185,129,0.8);"></div>',
                    iconSize: [16, 16]
                })
            }).addTo(map).bindPopup('📍 You are here').openPopup();
        });
    };

    // ═══════ INIT ═══════
    renderLayer('production', currentYear);
    loadWeather();

})();
</script>

<style>
/* ═══════════════════════════════════════════════════════════════
   BASE LAYOUT
   ═══════════════════════════════════════════════════════════════ */
html, body { margin: 0 !important; padding: 0 !important; width: 100% !important; overflow-x: hidden !important; }

.gis-page.dashboard-layout { display: block !important; width: 100% !important; max-width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; margin: 0 !important; padding: 0 !important; }

.gis-page .main-wrap { margin-left: 260px !important; width: calc(100% - 260px) !important; max-width: calc(100% - 260px) !important; min-height: 100vh !important; box-sizing: border-box !important; }

.gis-page .main-content { padding: 18px 24px 60px !important; margin: 0 !important; width: 100% !important; box-sizing: border-box !important; display: flex !important; flex-direction: column !important; gap: 16px !important; }

.gis-page .glass-card { background: rgba(30, 41, 59, 0.65) !important; backdrop-filter: blur(16px) !important; -webkit-backdrop-filter: blur(16px) !important; border: 1px solid rgba(255, 255, 255, 0.08) !important; border-radius: 20px !important; padding: 20px !important; margin: 0 !important; box-sizing: border-box !important; width: 100% !important; }

/* HERO */
.gis-hero { display: flex !important; justify-content: space-between !important; align-items: center !important; gap: 24px !important; flex-wrap: wrap !important; padding: 22px 28px !important; background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08)) !important; }
.hero-left { flex: 1 !important; min-width: 260px !important; }
.hero-tag { display: inline-flex !important; align-items: center !important; gap: 6px !important; padding: 5px 14px !important; background: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(16, 185, 129, 0.35) !important; border-radius: 999px !important; font-size: 10.5px !important; font-weight: 800 !important; letter-spacing: 1.2px !important; text-transform: uppercase !important; margin-bottom: 10px !important; }
.live-dot { width: 6px !important; height: 6px !important; background: #34d399 !important; border-radius: 50% !important; animation: pulse 1.6s infinite !important; box-shadow: 0 0 8px #34d399 !important; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
.gis-hero h1 { font-size: 26px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 6px 0 !important; }
.gis-hero p { color: #94a3b8 !important; font-size: 13.5px !important; margin: 0 !important; max-width: 720px !important; line-height: 1.55 !important; }
.hero-actions { display: flex !important; gap: 10px !important; flex-wrap: wrap !important; }
.btn-hero-primary, .btn-hero-secondary { display: inline-flex !important; align-items: center !important; gap: 8px !important; padding: 12px 22px !important; border: none !important; border-radius: 10px !important; font-size: 13px !important; font-weight: 700 !important; cursor: pointer !important; font-family: inherit !important; white-space: nowrap !important; text-decoration: none !important; transition: all 0.2s !important; }
.btn-hero-primary { background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35) !important; }
.btn-hero-primary:hover { transform: translateY(-2px) !important; }
.btn-hero-secondary { background: rgba(255, 255, 255, 0.06) !important; color: #f1f5f9 !important; border: 1px solid rgba(255, 255, 255, 0.14) !important; }
.btn-hero-secondary:hover { background: rgba(255, 255, 255, 0.12) !important; }

/* ═══════ CONTROL BAR — EXPANDED WITH WIDGETS ═══════ */
.control-bar {
    display: grid !important;
    grid-template-columns: 2.2fr 1fr 1fr 1fr 1.1fr 1.1fr !important;
    gap: 18px !important;
    align-items: start !important;
    padding: 20px 24px !important;
}

.control-group { display: flex !important; flex-direction: column !important; gap: 10px !important; min-width: 0 !important; }
.control-label { font-size: 11px !important; font-weight: 800 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.8px !important; }

.base-tiles { display: flex !important; gap: 8px !important; flex-wrap: wrap !important; }

.tile-btn { display: flex !important; flex-direction: column !important; align-items: center !important; gap: 4px !important; padding: 6px !important; background: rgba(0, 0, 0, 0.25) !important; border: 2px solid rgba(255, 255, 255, 0.08) !important; border-radius: 10px !important; cursor: pointer !important; font-family: inherit !important; font-size: 10.5px !important; font-weight: 700 !important; color: #94a3b8 !important; transition: all 0.2s !important; min-width: 68px !important; }
.tile-btn:hover { border-color: rgba(56, 189, 248, 0.4) !important; transform: translateY(-2px) !important; }
.tile-btn.active { border-color: #10b981 !important; color: #34d399 !important; background: rgba(16, 185, 129, 0.12) !important; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25) !important; }
.tile-preview { width: 56px !important; height: 40px !important; border-radius: 6px !important; background: #1e293b !important; position: relative !important; overflow: hidden !important; display: block !important; }
.tile-dark { background: linear-gradient(135deg, #0f172a, #334155) !important; }
.tile-satellite { background: linear-gradient(135deg, #1e4620, #6b8e4e, #8b7355) !important; }
.tile-streets { background: linear-gradient(135deg, #e8e0d5, #c9b79a, #8b7355) !important; }
.tile-terrain { background: linear-gradient(135deg, #d4c5a9, #a89678, #6b5b3f) !important; }
.tile-topo { background: linear-gradient(135deg, #f0e6d2, #d4c5a9, #7a9e7e) !important; }
.tile-humanitarian { background: linear-gradient(135deg, #fef3c7, #fbbf24, #dc2626) !important; }
.tile-dark::after { content: '🌑'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 14px; }
.tile-satellite::after { content: '🛰️'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 14px; }
.tile-streets::after { content: '🛣️'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 14px; }
.tile-terrain::after { content: '⛰️'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 14px; }
.tile-topo::after { content: '🗻'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 14px; }
.tile-humanitarian::after { content: '🚑'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 14px; }

.year-select, .date-input, .layer-select, .district-jump {
    padding: 11px 32px 11px 14px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    font-family: inherit !important;
    cursor: pointer !important;
    outline: none !important;
    -webkit-appearance: none !important;
    appearance: none !important;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 14px !important;
    width: 100% !important;
    box-sizing: border-box !important;
}
.year-select option, .layer-select option, .district-jump option { background: #1e293b !important; }
.date-input { background-image: none !important; padding-right: 14px !important; }
.date-input::-webkit-calendar-picker-indicator { filter: invert(0.6); cursor: pointer; }

.overlay-toggles { display: flex !important; flex-direction: column !important; gap: 6px !important; }
.mini-toggle { display: flex !important; align-items: center !important; gap: 8px !important; padding: 8px 12px !important; background: rgba(0, 0, 0, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.06) !important; border-radius: 8px !important; cursor: pointer !important; font-size: 12px !important; color: #cbd5e1 !important; font-weight: 600 !important; transition: all 0.2s !important; }
.mini-toggle:hover { background: rgba(56, 189, 248, 0.08) !important; }
.mini-toggle input { accent-color: #10b981 !important; cursor: pointer !important; }

/* ⭐ LIVE WEATHER CHIP */
.live-weather-chip {
    display: flex !important;
    flex-direction: column !important;
    gap: 10px !important;
    padding: 12px 14px !important;
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.12), rgba(16, 185, 129, 0.08)) !important;
    border: 1px solid rgba(56, 189, 248, 0.25) !important;
    border-radius: 12px !important;
    transition: all 0.2s !important;
}
.weather-row { display: flex !important; align-items: center !important; gap: 10px !important; }
.weather-emoji { font-size: 30px !important; line-height: 1 !important; }
.weather-text { display: flex !important; flex-direction: column !important; gap: 2px !important; }
.weather-text strong { font-size: 18px !important; color: #f1f5f9 !important; font-weight: 800 !important; }
.weather-text small { font-size: 10.5px !important; color: #94a3b8 !important; line-height: 1.3 !important; }
.weather-meta { display: flex !important; gap: 12px !important; font-size: 11px !important; color: #cbd5e1 !important; font-weight: 600 !important; }

/* ═══════ MAIN GRID: MAP + WIDGETS ═══════ */
.main-grid {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) 340px !important;
    gap: 16px !important;
    align-items: start !important;
}

/* MAP CARD */
.map-card { padding: 22px !important; }
.map-head { display: flex !important; justify-content: space-between !important; align-items: flex-start !important; gap: 12px !important; margin-bottom: 16px !important; flex-wrap: wrap !important; }
.map-head h3 { font-size: 18px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 4px 0 !important; }
.map-head p { font-size: 12.5px !important; color: #94a3b8 !important; margin: 0 !important; }
.map-head-actions { display: flex !important; align-items: center !important; gap: 10px !important; }
.coord-display { font-family: 'Courier New', Consolas, monospace !important; font-size: 11.5px !important; color: #38bdf8 !important; font-weight: 700 !important; padding: 8px 14px !important; background: rgba(0, 0, 0, 0.3) !important; border: 1px solid rgba(56, 189, 248, 0.25) !important; border-radius: 8px !important; white-space: nowrap !important; }
.icon-btn { width: 36px !important; height: 36px !important; background: rgba(0, 0, 0, 0.3) !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; border-radius: 10px !important; color: #cbd5e1 !important; font-size: 15px !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; padding: 0 !important; transition: all 0.2s !important; }
.icon-btn:hover { background: rgba(56, 189, 248, 0.15) !important; color: #38bdf8 !important; }

.map-wrapper { position: relative !important; width: 100% !important; height: 540px !important; border-radius: 16px !important; overflow: hidden !important; background: #0b1120 !important; border: 1px solid rgba(255, 255, 255, 0.06) !important; }
#map { width: 100% !important; height: 100% !important; background: #0b1120 !important; position: absolute !important; inset: 0 !important; z-index: 1 !important; }

.legend-overlay { position: absolute !important; bottom: 20px !important; left: 20px !important; z-index: 400 !important; padding: 14px 18px !important; background: rgba(15, 23, 42, 0.92) !important; backdrop-filter: blur(16px) !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; border-radius: 14px !important; min-width: 200px !important; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5) !important; }
.legend-overlay h5 { font-size: 12px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 0 10px 0 !important; padding-bottom: 8px !important; border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important; }
.legend-bar { display: flex !important; align-items: center !important; gap: 8px !important; margin-bottom: 6px !important; }
.legend-min, .legend-max { font-size: 10.5px !important; color: #94a3b8 !important; font-weight: 700 !important; }
.legend-gradient { flex: 1 !important; height: 10px !important; border-radius: 999px !important; background: linear-gradient(90deg, #065f46, #10b981, #34d399) !important; }
.legend-values { display: flex !important; justify-content: space-between !important; font-size: 11px !important; color: #cbd5e1 !important; font-weight: 700 !important; font-family: 'Courier New', monospace !important; }

.stats-overlay { position: absolute !important; top: 20px !important; right: 20px !important; z-index: 400 !important; display: flex !important; gap: 8px !important; }
.stat-mini { display: flex !important; flex-direction: column !important; gap: 2px !important; padding: 10px 14px !important; background: rgba(15, 23, 42, 0.92) !important; backdrop-filter: blur(16px) !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; border-radius: 12px !important; min-width: 80px !important; }
.stat-mini-label { font-size: 9.5px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; }
.stat-mini-value { font-size: 15px !important; font-weight: 800 !important; color: #34d399 !important; font-variant-numeric: tabular-nums !important; }

.map-stats-grid { display: grid !important; grid-template-columns: repeat(6, 1fr) !important; gap: 12px !important; margin-top: 18px !important; }
.stat-box { padding: 14px 16px !important; background: rgba(0, 0, 0, 0.25) !important; border: 1px solid rgba(255, 255, 255, 0.06) !important; border-radius: 12px !important; display: flex !important; flex-direction: column !important; gap: 4px !important; transition: all 0.2s !important; }
.stat-box:hover { background: rgba(56, 189, 248, 0.06) !important; border-color: rgba(56, 189, 248, 0.25) !important; transform: translateY(-2px) !important; }
.stat-label { font-size: 10.5px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; }
.stat-value { font-size: 17px !important; font-weight: 800 !important; color: #34d399 !important; font-variant-numeric: tabular-nums !important; line-height: 1.2 !important; }
.stat-sub { font-size: 10.5px !important; color: #64748b !important; font-weight: 600 !important; }

/* ⭐ RIGHT WIDGETS */
.right-widgets { display: flex !important; flex-direction: column !important; gap: 16px !important; min-width: 0 !important; }

.widget-card { padding: 18px !important; }
.widget-head { display: flex !important; justify-content: space-between !important; align-items: flex-start !important; gap: 10px !important; margin-bottom: 14px !important; padding-bottom: 12px !important; border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important; }
.widget-head h4 { font-size: 14px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 0 2px 0 !important; }
.widget-head p { font-size: 11px !important; color: #94a3b8 !important; margin: 0 !important; }
.widget-badge { padding: 3px 9px !important; background: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(16, 185, 129, 0.3) !important; border-radius: 999px !important; font-size: 9.5px !important; font-weight: 800 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; white-space: nowrap !important; }
.widget-badge.warn { background: rgba(248, 113, 113, 0.15) !important; color: #f87171 !important; border-color: rgba(248, 113, 113, 0.3) !important; }

.ranked-list { display: flex !important; flex-direction: column !important; gap: 6px !important; }
.rank-row { display: flex !important; align-items: center !important; gap: 10px !important; padding: 8px 12px !important; background: rgba(0, 0, 0, 0.2) !important; border-radius: 10px !important; font-size: 12.5px !important; transition: all 0.2s !important; }
.rank-row:hover { background: rgba(56, 189, 248, 0.08) !important; }
.rank-medal { font-size: 13px !important; width: 22px !important; text-align: center !important; flex-shrink: 0 !important; }
.rank-medal.warn { color: #f87171 !important; }
.rank-name { flex: 1 !important; color: #cbd5e1 !important; font-weight: 600 !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; }
.rank-value { color: #34d399 !important; font-weight: 800 !important; font-variant-numeric: tabular-nums !important; }
.rank-value.warn { color: #f87171 !important; }

.region-compare { display: flex !important; flex-direction: column !important; gap: 12px !important; }
.region-row { display: flex !important; align-items: center !important; gap: 10px !important; font-size: 12px !important; }
.region-label { min-width: 110px !important; color: #cbd5e1 !important; font-weight: 700 !important; font-size: 11.5px !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; }
.region-bar-wrap { flex: 1 !important; height: 10px !important; background: rgba(255, 255, 255, 0.06) !important; border-radius: 999px !important; overflow: hidden !important; }
.region-bar { height: 100% !important; border-radius: 999px !important; transition: width 0.5s ease !important; }
.region-bar.national { background: linear-gradient(90deg, #38bdf8, #0284c7) !important; }
.region-bar.district { background: linear-gradient(90deg, #10b981, #34d399) !important; }
.region-value { min-width: 65px !important; text-align: right !important; color: #34d399 !important; font-weight: 800 !important; font-size: 12px !important; font-variant-numeric: tabular-nums !important; }

.insights-list { display: flex !important; flex-direction: column !important; gap: 8px !important; }
.insight-item { display: flex !important; align-items: flex-start !important; gap: 10px !important; padding: 10px 12px !important; background: rgba(0, 0, 0, 0.2) !important; border-left: 3px solid #38bdf8 !important; border-radius: 8px !important; font-size: 12px !important; color: #cbd5e1 !important; line-height: 1.5 !important; }
.insight-item strong { color: #38bdf8 !important; font-weight: 700 !important; }
.insight-icon { font-size: 14px !important; flex-shrink: 0 !important; }

.quick-actions-list { display: flex !important; flex-direction: column !important; gap: 6px !important; }
.quick-action { display: flex !important; align-items: center !important; gap: 12px !important; padding: 10px 12px !important; background: rgba(0, 0, 0, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.05) !important; border-radius: 10px !important; text-decoration: none !important; color: inherit !important; transition: all 0.2s !important; }
.quick-action:hover { background: rgba(56, 189, 248, 0.08) !important; border-color: rgba(56, 189, 248, 0.3) !important; transform: translateX(3px) !important; }
.qa-icon { font-size: 18px !important; flex-shrink: 0 !important; }
.quick-action > div { flex: 1 !important; min-width: 0 !important; }
.quick-action strong { display: block !important; font-size: 12.5px !important; color: #f1f5f9 !important; font-weight: 700 !important; }
.quick-action small { display: block !important; font-size: 10.5px !important; color: #94a3b8 !important; }
.qa-arrow { color: #475569 !important; font-size: 14px !important; transition: all 0.2s !important; }
.quick-action:hover .qa-arrow { color: #38bdf8 !important; transform: translateX(3px) !important; }

.coord-grid { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 10px !important; }
.coord-item { display: flex !important; flex-direction: column !important; gap: 4px !important; padding: 10px 12px !important; background: rgba(0, 0, 0, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.05) !important; border-radius: 10px !important; }
.coord-label { font-size: 10px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; }
.coord-item strong { font-size: 13px !important; color: #38bdf8 !important; font-family: 'Courier New', monospace !important; font-weight: 800 !important; }

/* ═══════ RESPONSIVE ═══════ */
@media (max-width: 1400px) {
    .control-bar { grid-template-columns: repeat(3, 1fr) !important; }
    .main-grid { grid-template-columns: minmax(0, 1fr) 300px !important; }
}

@media (max-width: 1100px) {
    .control-bar { grid-template-columns: repeat(2, 1fr) !important; }
    .main-grid { grid-template-columns: 1fr !important; }
    .right-widgets { display: grid !important; grid-template-columns: repeat(2, 1fr) !important; }
    .map-wrapper { height: 480px !important; }
}

@media (max-width: 768px) {
    .gis-page .main-wrap { margin-left: 0 !important; width: 100% !important; max-width: 100% !important; padding-top: 64px !important; }
    .gis-page .main-content { padding: 14px !important; }
    .gis-page .glass-card { padding: 16px !important; border-radius: 16px !important; }
    .gis-hero { flex-direction: column !important; align-items: flex-start !important; padding: 20px !important; }
    .gis-hero h1 { font-size: 20px !important; }
    .hero-actions { width: 100% !important; }
    .btn-hero-primary, .btn-hero-secondary { flex: 1 !important; justify-content: center !important; }
    .control-bar { grid-template-columns: 1fr !important; }
    .map-stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
    .map-wrapper { height: 400px !important; }
    .stats-overlay { flex-direction: column !important; top: 12px !important; right: 12px !important; }
    .legend-overlay { bottom: 12px !important; left: 12px !important; padding: 10px 14px !important; min-width: 160px !important; }
    .coord-grid { grid-template-columns: 1fr !important; }
    .right-widgets { grid-template-columns: 1fr !important; }
}
/* ═══════════════════════════════════════════════════════════════
   FULL WIDTH OVERRIDES — fills the entire dashboard space
   Paste at the VERY END of your <style> block
   ═══════════════════════════════════════════════════════════════ */

/* 1. Force container to use full width */
.gis-page.dashboard-layout {
    width: 100% !important;
    max-width: none !important;
    padding: 0 !important;
    margin: 0 !important;
}

.gis-page .main-wrap {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    max-width: none !important;
    display: flex !important;
    flex-direction: column !important;
    padding: 0 !important;
    box-sizing: border-box !important;
}

.gis-page .main-content {
    width: 100% !important;
    max-width: none !important;
    padding: 20px 28px 60px !important;
    margin: 0 !important;
    box-sizing: border-box !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 18px !important;
}

/* 2. Every card must stretch the full width */
.gis-page .glass-card {
    width: 100% !important;
    max-width: none !important;
    box-sizing: border-box !important;
}

/* 3. HERO — should span full width (was only taking 1/3) */
.gis-page .gis-hero {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    width: 100% !important;
    max-width: none !important;
    padding: 24px 28px !important;
    box-sizing: border-box !important;
}

.gis-page .hero-left {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    max-width: 70% !important;
}

.gis-page .hero-actions {
    flex: 0 0 auto !important;
    display: flex !important;
    gap: 10px !important;
    flex-wrap: wrap !important;
}

/* 4. CONTROL BAR — force into one long horizontal row */
.gis-page .control-bar {
    display: grid !important;
    grid-template-columns: 2.2fr 1fr 1fr 1fr 1.1fr 1.1fr !important;
    gap: 18px !important;
    align-items: start !important;
    width: 100% !important;
    max-width: none !important;
    padding: 22px 26px !important;
    box-sizing: border-box !important;
}

.gis-page .control-group {
    width: 100% !important;
    min-width: 0 !important;
}

/* 5. MAIN GRID — MAP + RIGHT WIDGETS fill full width */
.gis-page .main-grid {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) 380px !important;
    gap: 18px !important;
    align-items: start !important;
    width: 100% !important;
    max-width: none !important;
    box-sizing: border-box !important;
}

/* 6. Map card takes most of the space */
.gis-page .map-card {
    width: 100% !important;
    max-width: none !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
    padding: 22px 24px !important;
}

.gis-page .map-wrapper {
    width: 100% !important;
    max-width: none !important;
    height: 620px !important;
}

/* 7. Right widgets column */
.gis-page .right-widgets {
    width: 100% !important;
    max-width: 380px !important;
    min-width: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 16px !important;
    box-sizing: border-box !important;
}

.gis-page .right-widgets .widget-card {
    width: 100% !important;
    max-width: 100% !important;
}

/* 8. Map stats grid — full width, 6 equal columns */
.gis-page .map-stats-grid {
    display: grid !important;
    grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
    gap: 12px !important;
    width: 100% !important;
    max-width: none !important;
    margin-top: 18px !important;
}

/* 9. Bottom info-grid if it still exists */
.gis-page .info-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
    gap: 16px !important;
    width: 100% !important;
    max-width: none !important;
}

/* ═══════════ RESPONSIVE — different column counts per width ═══════════ */

/* Large desktop (1400px+): keep 6-col control bar */
@media (min-width: 1400px) {
    .gis-page .control-bar {
        grid-template-columns: 2.2fr 1fr 1fr 1fr 1.1fr 1.1fr !important;
    }
    .gis-page .main-grid {
        grid-template-columns: minmax(0, 1fr) 400px !important;
    }
}

/* Medium desktop (1200px–1399px): compress control bar to 3 columns */
@media (max-width: 1399px) and (min-width: 1000px) {
    .gis-page .control-bar {
        grid-template-columns: repeat(3, 1fr) !important;
    }
    .gis-page .main-grid {
        grid-template-columns: minmax(0, 1fr) 340px !important;
    }
    .gis-page .hero-left {
        max-width: 100% !important;
    }
}

/* Tablet (768px–999px): stack control bar in 2 cols, main grid as single column */
@media (max-width: 999px) {
    .gis-page .control-bar {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .gis-page .main-grid {
        grid-template-columns: 1fr !important;
    }
    .gis-page .right-widgets {
        max-width: 100% !important;
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .gis-page .map-stats-grid {
        grid-template-columns: repeat(3, 1fr) !important;
    }
}

/* Mobile (< 768px): everything stacks */
@media (max-width: 767px) {
    .gis-page .main-wrap {
        margin-left: 0 !important;
        width: 100% !important;
        padding-top: 64px !important;
    }
    .gis-page .main-content {
        padding: 14px !important;
    }
    .gis-page .gis-hero {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    .gis-page .hero-left {
        max-width: 100% !important;
    }
    .gis-page .hero-actions {
        width: 100% !important;
    }
    .gis-page .hero-actions .btn-hero-primary,
    .gis-page .hero-actions .btn-hero-secondary {
        flex: 1 !important;
        justify-content: center !important;
    }
    .gis-page .control-bar {
        grid-template-columns: 1fr !important;
    }
    .gis-page .main-grid {
        grid-template-columns: 1fr !important;
    }
    .gis-page .right-widgets {
        grid-template-columns: 1fr !important;
        max-width: 100% !important;
    }
    .gis-page .map-stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .gis-page .map-wrapper {
        height: 400px !important;
    }
    .gis-page .coord-grid {
        grid-template-columns: 1fr !important;
    }
}
/* ═══════════════════════════════════════════════════════════════
   BALANCED LAYOUT — sticky right column + big map
   ═══════════════════════════════════════════════════════════════ */

/* 1. Main grid — use FLEX (more reliable than grid) */
.gis-page .main-grid {
    display: flex !important;
    flex-direction: row !important;
    gap: 18px !important;
    align-items: flex-start !important;
    width: 100% !important;
    max-width: none !important;
    box-sizing: border-box !important;
}

/* 2. Map column — takes all remaining width */
.gis-page .map-card {
    flex: 1 1 0% !important;
    min-width: 0 !important;
    width: auto !important;
    max-width: none !important;
    box-sizing: border-box !important;
    padding: 22px 24px !important;
}

/* 3. Map wrapper — big and fixed height */
.gis-page .map-wrapper {
    position: relative !important;
    width: 100% !important;
    height: 720px !important;
    min-height: 720px !important;
    max-height: 720px !important;
    display: block !important;
    overflow: hidden !important;
    border-radius: 16px !important;
    background: #0b1120 !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    box-sizing: border-box !important;
}

/* 4. Leaflet map itself — must fill parent */
.gis-page #map {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    min-height: 720px !important;
    z-index: 1 !important;
}

/* 5. Right widgets — fixed width, STICKY + SCROLLABLE */
.gis-page .right-widgets {
    flex: 0 0 380px !important;
    width: 380px !important;
    min-width: 380px !important;
    max-width: 380px !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 14px !important;

    /* ⭐ Key: sticky + scrollable so it doesn't stretch the page */
    position: sticky !important;
    top: 20px !important;
    max-height: calc(100vh - 40px) !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    padding-right: 6px !important;

    /* Custom thin scrollbar */
    scrollbar-width: thin !important;
    scrollbar-color: rgba(255, 255, 255, 0.15) transparent !important;
    box-sizing: border-box !important;
}

.gis-page .right-widgets::-webkit-scrollbar {
    width: 6px !important;
}
.gis-page .right-widgets::-webkit-scrollbar-track {
    background: transparent !important;
}
.gis-page .right-widgets::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.15) !important;
    border-radius: 999px !important;
}
.gis-page .right-widgets::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.25) !important;
}

/* 6. Ensure right widgets don't shrink */
.gis-page .right-widgets .widget-card {
    flex-shrink: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
}

/* 7. Control bar remains 6-column horizontal on desktop */
.gis-page .control-bar {
    display: grid !important;
    grid-template-columns: 2.2fr 1fr 1fr 1fr 1.1fr 1.1fr !important;
    gap: 18px !important;
    align-items: start !important;
    width: 100% !important;
    box-sizing: border-box !important;
    padding: 20px 24px !important;
}

/* 8. Hero remains full width */
.gis-page .gis-hero {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

/* 9. Map stats strip — 6 columns */
.gis-page .map-stats-grid {
    display: grid !important;
    grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
    gap: 12px !important;
    width: 100% !important;
    margin-top: 18px !important;
}

/* ═══════════════════════════════════════════════════════════════
   RESPONSIVE BREAKPOINTS
   ═══════════════════════════════════════════════════════════════ */

/* Medium desktop */
@media (max-width: 1399px) and (min-width: 1000px) {
    .gis-page .right-widgets {
        flex: 0 0 340px !important;
        width: 340px !important;
        min-width: 340px !important;
        max-width: 340px !important;
    }
    .gis-page .control-bar {
        grid-template-columns: repeat(3, 1fr) !important;
    }
    .gis-page .map-wrapper {
        height: 620px !important;
        min-height: 620px !important;
        max-height: 620px !important;
    }
    .gis-page #map {
        min-height: 620px !important;
    }
}

/* Tablet — stack columns */
@media (max-width: 999px) {
    .gis-page .main-grid {
        flex-direction: column !important;
    }
    .gis-page .map-card {
        flex: 0 0 auto !important;
        width: 100% !important;
    }
    .gis-page .right-widgets {
        flex: 0 0 auto !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        position: static !important;
        max-height: none !important;
        overflow-y: visible !important;
        padding-right: 0 !important;
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .gis-page .control-bar {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .gis-page .map-stats-grid {
        grid-template-columns: repeat(3, 1fr) !important;
    }
    .gis-page .map-wrapper {
        height: 480px !important;
        min-height: 480px !important;
        max-height: 480px !important;
    }
    .gis-page #map {
        min-height: 480px !important;
    }
}

/* Mobile */
@media (max-width: 767px) {
    .gis-page .main-wrap {
        margin-left: 0 !important;
        width: 100% !important;
        padding-top: 64px !important;
    }
    .gis-page .main-content {
        padding: 14px !important;
    }
    .gis-page .gis-hero {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    .gis-page .hero-actions {
        width: 100% !important;
    }
    .gis-page .hero-actions .btn-hero-primary,
    .gis-page .hero-actions .btn-hero-secondary {
        flex: 1 !important;
        justify-content: center !important;
    }
    .gis-page .control-bar {
        grid-template-columns: 1fr !important;
    }
    .gis-page .right-widgets {
        grid-template-columns: 1fr !important;
    }
    .gis-page .map-stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .gis-page .map-wrapper {
        height: 420px !important;
        min-height: 420px !important;
        max-height: 420px !important;
    }
    .gis-page #map {
        min-height: 420px !important;
    }
}
/* ⭐ FIX: Guarantee the map container has size */
.gis-page .map-wrapper {
    position: relative !important;
    width: 100% !important;
    height: 720px !important;
    min-height: 720px !important;
    display: block !important;
}

.gis-page #map {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: #0b1120 !important;
    z-index: 1 !important;
}

/* Make sure Leaflet fills its container */
.gis-page .leaflet-container {
    width: 100% !important;
    height: 100% !important;
    background: #0b1120 !important;
}
</style>