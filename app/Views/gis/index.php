<div class="dashboard-layout gis-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="main-content">

            <!-- ═══════════ HERO ═══════════ -->
            <section class="glass-card gis-hero">
                <div class="hero-left">
                    <span class="hero-tag">🗺️ SPATIAL INTELLIGENCE</span>
                    <h1>Rwanda GIS Analytics</h1>
                    <p>Interactive district-level intelligence across production, weather, irrigation, and pest risk.</p>
                </div>
                <div class="hero-actions">
                    <button type="button" class="btn-hero-secondary" onclick="window.print()">
                        📥 Export Map
                    </button>
                    <a href="<?= BASE_URL ?>/weather" class="btn-hero-primary">
                        🌤️ Weather
                    </a>
                </div>
            </section>

            <!-- ═══════════ MAIN GRID ═══════════ -->
            <div class="gis-grid">

                <!-- LAYERS PANEL -->
                <aside class="layers-panel glass-card">
                    <div class="panel-head">
                        <span class="panel-icon">🗺️</span>
                        <h3>Map Layers</h3>
                    </div>

                    <a href="#" class="layer-item active" data-layer="production">
                        <span class="layer-radio selected"></span>
                        <span class="layer-icon">🌾</span>
                        <div class="layer-info">
                            <strong>Crop Production</strong>
                            <small>Total yield (kg) by district</small>
                        </div>
                    </a>

                    <a href="#" class="layer-item" data-layer="rainfall">
                        <span class="layer-radio"></span>
                        <span class="layer-icon">🌧️</span>
                        <div class="layer-info">
                            <strong>Rainfall</strong>
                            <small>Annual rainfall (mm)</small>
                        </div>
                    </a>

                    <a href="#" class="layer-item" data-layer="irrigation">
                        <span class="layer-radio"></span>
                        <span class="layer-icon">💧</span>
                        <div class="layer-info">
                            <strong>Irrigation Coverage</strong>
                            <small>% of farms with irrigation</small>
                        </div>
                    </a>

                    <a href="#" class="layer-item" data-layer="pests">
                        <span class="layer-radio"></span>
                        <span class="layer-icon">🐛</span>
                        <div class="layer-info">
                            <strong>Pest Outbreaks</strong>
                            <small>Active pest alerts</small>
                        </div>
                    </a>

                    <a href="#" class="layer-item" data-layer="soil">
                        <span class="layer-radio"></span>
                        <span class="layer-icon">🌱</span>
                        <div class="layer-info">
                            <strong>Soil Fertility</strong>
                            <small>Nutrient levels</small>
                        </div>
                    </a>

                    <div class="legend" id="mapLegend">
                        <h4>Legend</h4>
                        <div class="legend-items">
                            <div class="legend-item">
                                <span class="legend-dot high"></span>
                                <span>High value</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot mid"></span>
                                <span>Medium</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot low"></span>
                                <span>Low value</span>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- MAP -->
                <div class="map-container glass-card">
                    <div class="map-head">
                        <div>
                            <h3 id="mapTitle">🌾 Crop Production Heatmap</h3>
                            <p id="mapSubtitle">Interactive map of Rwanda's 30 districts</p>
                        </div>
                        <select class="year-select" id="yearSelect">
                            <option value="current">Current Year</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>
                    </div>

                    <div id="map"></div>

                    <div class="map-stats" id="mapStats">
                        <div class="stat-box">
                            <span class="stat-label">Total Districts</span>
                            <strong class="stat-value" id="statDistricts">30</strong>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">Highest</span>
                            <strong class="stat-value" id="statHighest">—</strong>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">Average</span>
                            <strong class="stat-value" id="statAverage">—</strong>
                        </div>
                        <div class="stat-box">
                            <span class="stat-label">Total</span>
                            <strong class="stat-value" id="statTotal">—</strong>
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
(function () {
    'use strict';

    var BASE = '<?= BASE_URL ?>';

    // ═══════ INIT MAP ═══════
    var map = L.map('map', {
        center: [-1.9403, 29.8739],
        zoom: 8,
        zoomControl: true,
        attributionControl: true,
        preferCanvas: true
    });

    // ═══════ FREE TILE LAYER — NO API KEY ═══════
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        crossOrigin: true
    }).addTo(map);

    // ═══════ DARK THEME CSS ═══════
    var style = document.createElement('style');
    style.textContent = `
        .leaflet-tile-pane {
            filter: invert(1) hue-rotate(180deg) brightness(0.88) contrast(1.15) saturate(0.75) !important;
        }
        .leaflet-container { background: #0b1120 !important; }
        .leaflet-control-attribution {
            background: rgba(15,23,42,0.85) !important;
            color: #94a3b8 !important;
            font-size: 10px !important;
            padding: 2px 8px !important;
            border-radius: 6px 0 0 0 !important;
        }
        .leaflet-control-attribution a { color: #38bdf8 !important; }
        .leaflet-bar {
            background: #1e293b !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4) !important;
            border-radius: 8px !important;
            overflow: hidden !important;
        }
        .leaflet-bar a {
            background: transparent !important;
            color: #cbd5e1 !important;
            border-bottom: 1px solid rgba(255,255,255,0.06) !important;
            font-size: 16px !important;
        }
        .leaflet-bar a:hover {
            background: rgba(56,189,248,0.15) !important;
            color: #38bdf8 !important;
        }
        .leaflet-popup-content-wrapper {
            background: #1e293b !important;
            color: #f1f5f9 !important;
            border: 1px solid rgba(56,189,248,0.3) !important;
            border-radius: 12px !important;
            box-shadow: 0 12px 32px rgba(0,0,0,0.5) !important;
        }
        .leaflet-popup-tip { background: #1e293b !important; border: 1px solid rgba(56,189,248,0.3) !important; }
        .leaflet-popup-close-button { color: #94a3b8 !important; }
        .district-label { background: transparent !important; border: none !important; pointer-events: none !important; }
        .district-label span {
            display: inline-block !important;
            padding: 3px 8px !important;
            background: rgba(15,23,42,0.9) !important;
            color: #f1f5f9 !important;
            font-size: 10.5px !important;
            font-weight: 700 !important;
            border-radius: 6px !important;
            border: 1px solid rgba(56,189,248,0.3) !important;
            white-space: nowrap !important;
            box-shadow: 0 3px 10px rgba(0,0,0,0.4) !important;
        }
    `;
    document.head.appendChild(style);

    // ═══════ DISTRICTS DATA (sample + real via API) ═══════
    var districts = [
        { name: 'Kigali',     lat: -1.9441, lng: 30.0619, production: 45000, rainfall: 950,  irrigation: 42, pests: 1, soil: 78 },
        { name: 'Musanze',    lat: -1.4999, lng: 29.6349, production: 120000, rainfall: 1200, irrigation: 28, pests: 2, soil: 85 },
        { name: 'Huye',       lat: -2.5967, lng: 29.7392, production: 78000, rainfall: 1100, irrigation: 22, pests: 1, soil: 72 },
        { name: 'Nyagatare',  lat: -1.2939, lng: 30.3275, production: 92000, rainfall: 800,  irrigation: 18, pests: 3, soil: 65 },
        { name: 'Rubavu',     lat: -1.6779, lng: 29.2579, production: 65000, rainfall: 1350, irrigation: 35, pests: 1, soil: 80 },
        { name: 'Rusizi',     lat: -2.4833, lng: 29.0000, production: 88000, rainfall: 1400, irrigation: 30, pests: 2, soil: 82 },
        { name: 'Kayonza',    lat: -1.8833, lng: 30.6167, production: 71000, rainfall: 850,  irrigation: 15, pests: 3, soil: 68 },
        { name: 'Karongi',    lat: -2.0500, lng: 29.3500, production: 58000, rainfall: 1250, irrigation: 24, pests: 1, soil: 76 },
        { name: 'Nyamagabe',  lat: -2.4667, lng: 29.4500, production: 62000, rainfall: 1300, irrigation: 20, pests: 2, soil: 74 },
        { name: 'Rwamagana',  lat: -1.9500, lng: 30.4333, production: 75000, rainfall: 900,  irrigation: 26, pests: 2, soil: 70 },
        { name: 'Muhanga',    lat: -2.0833, lng: 29.7500, production: 68000, rainfall: 1050, irrigation: 23, pests: 1, soil: 73 },
        { name: 'Gicumbi',    lat: -1.5833, lng: 30.1000, production: 72000, rainfall: 1150, irrigation: 25, pests: 2, soil: 79 },
        { name: 'Rulindo',    lat: -1.7667, lng: 30.0667, production: 56000, rainfall: 1080, irrigation: 21, pests: 1, soil: 77 },
        { name: 'Gakenke',    lat: -1.7000, lng: 29.7833, production: 64000, rainfall: 1220, irrigation: 22, pests: 1, soil: 81 },
        { name: 'Burera',     lat: -1.4667, lng: 29.9167, production: 70000, rainfall: 1280, irrigation: 27, pests: 2, soil: 83 },
        { name: 'Nyabihu',    lat: -1.6667, lng: 29.5000, production: 66000, rainfall: 1320, irrigation: 29, pests: 1, soil: 84 },
        { name: 'Ngororero',  lat: -1.8667, lng: 29.6000, production: 52000, rainfall: 1180, irrigation: 19, pests: 1, soil: 75 },
        { name: 'Rutsiro',    lat: -1.9333, lng: 29.3500, production: 54000, rainfall: 1240, irrigation: 21, pests: 1, soil: 76 },
        { name: 'Nyamasheke', lat: -2.3500, lng: 29.1333, production: 60000, rainfall: 1380, irrigation: 26, pests: 1, soil: 78 },
        { name: 'Gisagara',   lat: -2.4500, lng: 29.8333, production: 58000, rainfall: 1020, irrigation: 20, pests: 2, soil: 71 },
        { name: 'Nyanza',     lat: -2.3500, lng: 29.7500, production: 61000, rainfall: 1080, irrigation: 22, pests: 1, soil: 72 },
        { name: 'Ruhango',    lat: -2.1500, lng: 29.7833, production: 63000, rainfall: 1050, irrigation: 21, pests: 1, soil: 73 },
        { name: 'Kamonyi',    lat: -2.0000, lng: 29.9000, production: 64000, rainfall: 980,  irrigation: 23, pests: 1, soil: 74 },
        { name: 'Nyaruguru',  lat: -2.6167, lng: 29.5667, production: 54000, rainfall: 1150, irrigation: 18, pests: 2, soil: 72 },
        { name: 'Bugesera',   lat: -2.2194, lng: 30.1244, production: 67000, rainfall: 850,  irrigation: 16, pests: 3, soil: 66 },
        { name: 'Ngoma',      lat: -2.1500, lng: 30.5667, production: 69000, rainfall: 900,  irrigation: 17, pests: 3, soil: 67 },
        { name: 'Kirehe',     lat: -2.2833, lng: 30.7167, production: 73000, rainfall: 880,  irrigation: 15, pests: 3, soil: 68 },
        { name: 'Gatsibo',    lat: -1.6000, lng: 30.4333, production: 77000, rainfall: 870,  irrigation: 16, pests: 2, soil: 69 },
        { name: 'Gasabo',     lat: -1.9167, lng: 30.1167, production: 48000, rainfall: 940,  irrigation: 45, pests: 1, soil: 76 },
        { name: 'Kicukiro',   lat: -1.9833, lng: 30.1000, production: 42000, rainfall: 930,  irrigation: 48, pests: 1, soil: 75 },
        { name: 'Nyarugenge', lat: -1.9500, lng: 30.0500, production: 44000, rainfall: 920,  irrigation: 50, pests: 1, soil: 74 }
    ];

    // Try to load real data from the API (if it exists)
    fetch(BASE + '/gis/apiData', { credentials: 'same-origin' })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (data && data.success && data.districts && data.districts.length) {
                // Merge real data if it has coordinates
                data.districts.forEach(function (d) {
                    if (!d.lat || !d.lng) return;
                    var existing = districts.find(function (x) { return x.name === d.name; });
                    if (existing) {
                        if (d.production > 0) existing.production = d.production;
                        if (d.rainfall > 0) existing.rainfall = d.rainfall;
                    }
                });
                renderLayer('production');
            }
        })
        .catch(function () { /* silent — use sample data */ });

    var currentLayer = 'production';
    var layers = [];

    // ═══════ LAYER CONFIG ═══════
    var LAYER_CONFIG = {
        production: {
            title: '🌾 Crop Production Heatmap',
            subtitle: 'Total yield (kg) by district',
            colors: ['#065f46', '#10b981', '#34d399'],
            unit: ' kg'
        },
        rainfall: {
            title: '🌧️ Rainfall Distribution',
            subtitle: 'Annual rainfall (mm) by district',
            colors: ['#0c4a6e', '#0284c7', '#38bdf8'],
            unit: ' mm'
        },
        irrigation: {
            title: '💧 Irrigation Coverage',
            subtitle: '% of farms with irrigation',
            colors: ['#134e4a', '#0891b2', '#22d3ee'],
            unit: '%'
        },
        pests: {
            title: '🐛 Pest Outbreaks',
            subtitle: 'Active pest alerts per district',
            colors: ['#7f1d1d', '#dc2626', '#f87171'],
            unit: ' alerts'
        },
        soil: {
            title: '🌱 Soil Fertility',
            subtitle: 'Nutrient levels (out of 100)',
            colors: ['#4c1d95', '#7c3aed', '#a78bfa'],
            unit: '/100'
        }
    };

    // ═══════ RENDER LAYER ═══════
    function renderLayer(layerKey) {
        var cfg = LAYER_CONFIG[layerKey];

        // Clear old
        layers.forEach(function (l) { map.removeLayer(l); });
        layers = [];

        // Update title
        document.getElementById('mapTitle').textContent = cfg.title;
        document.getElementById('mapSubtitle').textContent = cfg.subtitle;

        // Compute stats
        var values = districts.map(function (d) { return d[layerKey] || 0; });
        var maxVal = Math.max.apply(null, values);
        var minVal = Math.min.apply(null, values.filter(function (v) { return v > 0; }));
        var sumVal = values.reduce(function (a, b) { return a + b; }, 0);
        var avgVal = sumVal / values.length;

        // Update stat boxes
        document.getElementById('statDistricts').textContent = districts.length;
        document.getElementById('statHighest').textContent = formatNum(maxVal) + cfg.unit;
        document.getElementById('statAverage').textContent = formatNum(Math.round(avgVal)) + cfg.unit;
        document.getElementById('statTotal').textContent = formatNum(sumVal) + cfg.unit;

        // Draw circles
        districts.forEach(function (d) {
            var val = d[layerKey] || 0;
            var ratio = maxVal > minVal ? (val - minVal) / (maxVal - minVal) : 0.5;

            // Pick color from palette based on ratio
            var color = ratio > 0.66 ? cfg.colors[2] :
                       ratio > 0.33 ? cfg.colors[1] : cfg.colors[0];

            // Radius scales with value
            var radius = 9000 + ratio * 22000;

            var circle = L.circle([d.lat, d.lng], {
                radius: radius,
                fillColor: color,
                color: color,
                weight: 2,
                opacity: 0.9,
                fillOpacity: 0.55
            });

            circle.bindPopup(
                '<div style="min-width: 200px;">' +
                    '<strong style="font-size: 15px; color: ' + color + ';">' + d.name + '</strong>' +
                    '<div style="margin-top: 8px; font-size: 12.5px; line-height: 1.7;">' +
                        '<div>🌾 Production: <strong>' + formatNum(d.production) + ' kg</strong></div>' +
                        '<div>🌧️ Rainfall: <strong>' + d.rainfall + ' mm</strong></div>' +
                        '<div>💧 Irrigation: <strong>' + d.irrigation + '%</strong></div>' +
                        '<div>🐛 Pest alerts: <strong>' + d.pests + '</strong></div>' +
                        '<div>🌱 Soil: <strong>' + d.soil + '/100</strong></div>' +
                    '</div>' +
                '</div>'
            );

            circle.addTo(map);
            layers.push(circle);

            // Add label
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
        });
    }

    function formatNum(n) {
        return Number(n || 0).toLocaleString('en-US', { maximumFractionDigits: 0 });
    }

    // ═══════ LAYER SWITCHING ═══════
    document.querySelectorAll('.layer-item').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            document.querySelectorAll('.layer-item').forEach(function (b) {
                b.classList.remove('active');
                var r = b.querySelector('.layer-radio');
                if (r) r.classList.remove('selected');
            });

            this.classList.add('active');
            var radio = this.querySelector('.layer-radio');
            if (radio) radio.classList.add('selected');

            currentLayer = this.getAttribute('data-layer');
            renderLayer(currentLayer);
        });
    });

    // ═══════ YEAR FILTER ═══════
    var yearSelect = document.getElementById('yearSelect');
    if (yearSelect) {
        yearSelect.addEventListener('change', function () {
            renderLayer(currentLayer);
        });
    }

    // ═══════ INITIAL RENDER ═══════
    renderLayer('production');

})();
</script>

<style>
/* ═══════════════════════════════════════════════════════════════
   LAYOUT
   ═══════════════════════════════════════════════════════════════ */

html, body {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    overflow-x: hidden !important;
}

.gis-page.dashboard-layout {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    min-height: 100vh !important;
    background: #0b1120 !important;
    margin: 0 !important;
    padding: 0 !important;
}

.gis-page .main-wrap {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    min-height: 100vh !important;
    box-sizing: border-box !important;
}

.gis-page .main-content {
    padding: 20px 28px 60px !important;
    margin: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 18px !important;
}

.gis-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 20px !important;
    margin: 0 !important;
    box-sizing: border-box !important;
    width: 100% !important;
}

/* HERO */
.gis-hero {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 24px !important;
    flex-wrap: wrap !important;
    padding: 26px 30px !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08)) !important;
}

.hero-left { flex: 1 !important; min-width: 260px !important; }

.hero-tag {
    display: inline-block !important;
    padding: 5px 14px !important;
    background: rgba(16, 185, 129, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(16, 185, 129, 0.35) !important;
    border-radius: 999px !important;
    font-size: 10.5px !important;
    font-weight: 800 !important;
    letter-spacing: 1.2px !important;
    text-transform: uppercase !important;
    margin-bottom: 10px !important;
}

.gis-hero h1 { font-size: 26px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.gis-hero p { color: #94a3b8 !important; font-size: 14px !important; margin: 0 !important; max-width: 640px !important; line-height: 1.55 !important; }

.hero-actions { display: flex !important; gap: 10px !important; flex-wrap: wrap !important; }

.btn-hero-primary, .btn-hero-secondary {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 12px 22px !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    white-space: nowrap !important;
    text-decoration: none !important;
    transition: all 0.2s !important;
}

.btn-hero-primary {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35) !important;
}
.btn-hero-primary:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 22px rgba(16, 185, 129, 0.5) !important; }

.btn-hero-secondary {
    background: rgba(255, 255, 255, 0.06) !important;
    color: #f1f5f9 !important;
    border: 1px solid rgba(255, 255, 255, 0.14) !important;
}
.btn-hero-secondary:hover { background: rgba(255, 255, 255, 0.12) !important; transform: translateY(-2px) !important; }

/* GRID */
.gis-grid {
    display: grid !important;
    grid-template-columns: 300px minmax(0, 1fr) !important;
    gap: 18px !important;
    align-items: start !important;
}

/* LAYERS PANEL */
.layers-panel { padding: 20px !important; }

.panel-head {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    margin-bottom: 16px !important;
    padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
}

.panel-icon { font-size: 20px !important; }
.panel-head h3 { font-size: 15px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 !important; text-transform: uppercase !important; letter-spacing: 0.8px !important; }

.layer-item {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 14px 16px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 12px !important;
    cursor: pointer !important;
    transition: all 0.2s !important;
    margin-bottom: 8px !important;
    text-decoration: none !important;
    color: inherit !important;
}

.layer-item:hover {
    background: rgba(56, 189, 248, 0.06) !important;
    border-color: rgba(56, 189, 248, 0.25) !important;
    transform: translateX(3px) !important;
}

.layer-item.active {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08)) !important;
    border-color: rgba(16, 185, 129, 0.45) !important;
}

.layer-radio {
    width: 16px !important;
    height: 16px !important;
    border-radius: 50% !important;
    border: 2px solid rgba(255, 255, 255, 0.25) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
    transition: all 0.2s !important;
}

.layer-radio.selected {
    border-color: #34d399 !important;
    box-shadow: 0 0 8px rgba(52, 211, 153, 0.5) !important;
}

.layer-radio.selected::after {
    content: '' !important;
    width: 8px !important;
    height: 8px !important;
    background: #34d399 !important;
    border-radius: 50% !important;
}

.layer-icon { font-size: 22px !important; flex-shrink: 0 !important; }

.layer-info { flex: 1 !important; min-width: 0 !important; }
.layer-info strong { display: block !important; font-size: 13px !important; color: #f1f5f9 !important; font-weight: 700 !important; margin-bottom: 2px !important; }
.layer-info small { display: block !important; font-size: 11px !important; color: #94a3b8 !important; line-height: 1.4 !important; }

/* LEGEND */
.legend {
    margin-top: 20px !important;
    padding-top: 16px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
}

.legend h4 {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    margin: 0 0 12px 0 !important;
}

.legend-items { display: flex !important; flex-direction: column !important; gap: 8px !important; }

.legend-item {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    font-size: 12px !important;
    color: #cbd5e1 !important;
}

.legend-dot {
    width: 14px !important;
    height: 14px !important;
    border-radius: 50% !important;
    flex-shrink: 0 !important;
}

.legend-dot.high { background: #34d399 !important; }
.legend-dot.mid  { background: #10b981 !important; }
.legend-dot.low  { background: #065f46 !important; }

/* MAP CONTAINER */
.map-container {
    padding: 20px !important;
    min-width: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 16px !important;
}

.map-head {
    display: flex !important;
    justify-content: space-between !important;
    align-items: flex-start !important;
    gap: 12px !important;
    flex-wrap: wrap !important;
}

.map-head h3 { font-size: 17px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 0 4px 0 !important; }
.map-head p { font-size: 12.5px !important; color: #94a3b8 !important; margin: 0 !important; }

.year-select {
    padding: 9px 32px 9px 14px !important;
    background: rgba(0, 0, 0, 0.3) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 10px !important;
    color: #cbd5e1 !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    font-family: inherit !important;
    cursor: pointer !important;
    outline: none !important;
    -webkit-appearance: none !important;
    appearance: none !important;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 10px center !important;
    background-size: 14px !important;
}

.year-select option { background: #1e293b !important; }

#map {
    width: 100% !important;
    height: 560px !important;
    background: #0b1120 !important;
    border-radius: 14px !important;
    overflow: hidden !important;
    position: relative !important;
    z-index: 1 !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
}

/* MAP STATS */
.map-stats {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 12px !important;
}

.stat-box {
    padding: 14px 16px !important;
    background: rgba(0, 0, 0, 0.25) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 12px !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 4px !important;
}

.stat-label { font-size: 10.5px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.6px !important; }
.stat-value { font-size: 16px !important; font-weight: 800 !important; color: #34d399 !important; font-variant-numeric: tabular-nums !important; }

/* RESPONSIVE */
@media (max-width: 1024px) {
    .gis-grid { grid-template-columns: 1fr !important; }
    .map-stats { grid-template-columns: repeat(2, 1fr) !important; }
}

@media (max-width: 768px) {
    .gis-page .main-wrap {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding-top: 64px !important;
    }
    .gis-page .main-content { padding: 14px !important; }
    .gis-page .glass-card { padding: 16px !important; border-radius: 16px !important; }
    .gis-hero { flex-direction: column !important; align-items: flex-start !important; padding: 20px !important; }
    .gis-hero h1 { font-size: 20px !important; }
    .hero-actions { width: 100% !important; }
    .btn-hero-primary, .btn-hero-secondary { flex: 1 !important; justify-content: center !important; }
    #map { height: 400px !important; }
}
</style>