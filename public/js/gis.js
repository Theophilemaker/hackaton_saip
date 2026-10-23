/* =====================================================================
 * SAIP — GIS Map Controller
 * Rwanda district choropleth + tools
 * Used by: /gis, /weather/gis
 * ===================================================================== */

(function () {
    'use strict';

    /* ============================================================
     *  STATE
     * ============================================================ */
    const state = {
        map: null,
        geoLayer: null,
        heatmapData: [],
        currentLayer: 'production',
        currentBase: null,
        districtMarkers: null,
        measureHandler: null,
        userMarker: null,
        initialized: false,
    };

    /* ============================================================
     *  CONFIG
     * ============================================================ */
    const BASE_URL = window.BASE_URL || (window.SAIP && window.SAIP.baseUrl) || '/';

    const BASE_LAYERS = {
        dark: {
            url: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
            attribution: '&copy; OpenStreetMap &copy; CARTO',
        },
        streets: {
            url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            attribution: '&copy; OpenStreetMap contributors',
        },
        satellite: {
            url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            attribution: '&copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics',
        },
        terrain: {
            url: 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
            attribution: '&copy; OpenTopoMap (CC-BY-SA)',
        },
    };

    const LAYER_COLORS = {
        production: ['#16A34A', '#84CC16', '#F59E0B', '#EF4444'],
        rainfall:   ['#0EA5E9', '#06B6D4', '#3B82F6', '#6366F1'],
        irrigation: ['#F59E0B', '#84CC16', '#22C55E', '#16A34A'],
        pest:       ['#22C55E', '#F59E0B', '#F97316', '#DC2626'],
        soil:       ['#A16207', '#CA8A04', '#EAB308', '#FACC15'],
    };

    const LAYER_LABELS = {
        production: { title: '🌾 Crop Production Heatmap', low: 'Low yield', high: 'High yield' },
        rainfall:   { title: '🌧️ Annual Rainfall',        low: 'Low rainfall', high: 'High rainfall' },
        irrigation: { title: '💧 Irrigation Coverage',     low: 'No irrigation', high: 'Full irrigation' },
        pest:       { title: '🐛 Pest Outbreak Risk',      low: 'No risk', high: 'High risk' },
        soil:       { title: '🪨 Soil Fertility',          low: 'Poor', high: 'Fertile' },
    };

    /* Rwanda district coordinates (simplified) */
    const DISTRICT_COORDS = {
        'Gasabo':     [-1.9167, 30.1333],
        'Kicukiro':   [-1.9833, 30.1000],
        'Nyarugenge': [-1.9500, 30.0500],
        'Burera':     [-1.4667, 29.8333],
        'Gakenke':    [-1.7000, 29.7833],
        'Gicumbi':    [-1.5833, 30.1000],
        'Musanze':    [-1.4997, 29.6349],
        'Rulindo':    [-1.7333, 30.0667],
        'Gisagara':   [-2.5500, 29.8333],
        'Huye':       [-2.5967, 29.7394],
        'Kamonyi':    [-2.0000, 29.9000],
        'Muhanga':    [-2.0833, 29.7500],
        'Nyamagabe':  [-2.4667, 29.4667],
        'Nyanza':     [-2.3500, 29.7500],
        'Nyaruguru':  [-2.6167, 29.5333],
        'Ruhango':    [-2.2333, 29.7833],
        'Bugesera':   [-2.2167, 30.1500],
        'Gatsibo':    [-1.6000, 30.4333],
        'Kayonza':    [-1.8833, 30.6167],
        'Kirehe':     [-2.2833, 30.7167],
        'Ngoma':      [-2.1500, 30.4667],
        'Nyagatare':  [-1.3000, 30.3333],
        'Rwamagana':  [-1.9500, 30.4333],
        'Karongi':    [-2.0000, 29.3833],
        'Ngororero':  [-1.8667, 29.6167],
        'Nyabihu':    [-1.6667, 29.5000],
        'Nyamasheke': [-2.3333, 29.1333],
        'Rubavu':     [-1.6833, 29.2667],
        'Rusizi':     [-2.4833, 29.0000],
        'Rutsiro':    [-1.9333, 29.3333],
    };

    /* ============================================================
     *  INITIALIZATION
     * ============================================================ */
    function init() {
        const mapEl = document.getElementById('rwandaMap') || document.getElementById('govMap');
        if (!mapEl) return;

        if (typeof L === 'undefined') {
            console.warn('Leaflet not loaded — GIS module disabled');
            return;
        }

        state.map = L.map(mapEl, {
            center: [-1.9403, 29.8739],
            zoom: 8,
            minZoom: 7,
            maxZoom: 18,
            zoomControl: false,
        });

        // Base layer
        state.currentBase = L.tileLayer(BASE_LAYERS.dark.url, {
            attribution: BASE_LAYERS.dark.attribution,
            maxZoom: 19,
        }).addTo(state.map);

        // Scale bar
        L.control.scale({ imperial: false, position: 'bottomleft' }).addTo(state.map);

        // Zoom control
        L.control.zoom({ position: 'topright' }).addTo(state.map);

        // District markers group
        state.districtMarkers = L.layerGroup().addTo(state.map);

        // Load data
        loadGeoJSON();
        loadHeatmap();

        state.initialized = true;
    }

    /* ============================================================
     *  GEOJSON LOAD
     * ============================================================ */
    function loadGeoJSON() {
        const url = BASE_URL.replace(/\/$/, '') + '/gis/geojson';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => {
                if (!r.ok) throw new Error('GeoJSON HTTP ' + r.status);
                return r.json();
            })
            .then(geojson => {
                state.geoLayer = L.geoJSON(geojson, {
                    style: defaultDistrictStyle,
                    onEachFeature: bindDistrict,
                }).addTo(state.map);

                applyChoropleth();

                setTimeout(() => {
                    if (state.geoLayer) {
                        state.map.fitBounds(state.geoLayer.getBounds(), { padding: [30, 30] });
                    }
                }, 300);
            })
            .catch(err => {
                console.warn('GeoJSON load failed:', err);
                showToast('⚠️ Map boundaries could not be loaded');
            });
    }

    function defaultDistrictStyle() {
        return {
            fillColor: '#16A34A',
            weight: 1.5,
            opacity: 1,
            color: 'rgba(255,255,255,0.4)',
            fillOpacity: 0.5,
        };
    }

    function bindDistrict(feature, layer) {
        const props = feature.properties || {};
        const name = props.name || props.DISTRICT || props.district || 'District';
        const districtId = props.id || props.DISTRICT_ID || null;

        layer.bindTooltip(
            `<strong>${escapeHtml(name)}</strong>`,
            {
                sticky: true,
                className: 'district-tooltip',
                direction: 'top',
                offset: [0, -10],
            }
        );

        layer.on('mouseover', () => {
            layer.setStyle({
                fillOpacity: 0.85,
                weight: 3,
                color: '#84CC16',
            });
            layer.bringToFront();
        });

        layer.on('mouseout', () => {
            if (state.geoLayer) state.geoLayer.resetStyle(layer);
        });

        layer.on('click', () => {
            showDistrictPanel(name, districtId, layer);
        });
    }

    /* ============================================================
     *  HEATMAP DATA
     * ============================================================ */
    function loadHeatmap() {
        const url = BASE_URL.replace(/\/$/, '') + '/government/ajaxHeatmap';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                state.heatmapData = Array.isArray(data) ? data : [];
                if (!state.heatmapData.length) state.heatmapData = generateSimulatedData();
                applyChoropleth();
                addDistrictMarkers();
                updateNationalStats();
            })
            .catch(() => {
                state.heatmapData = generateSimulatedData();
                applyChoropleth();
                addDistrictMarkers();
                updateNationalStats();
            });
    }

    function generateSimulatedData() {
        return Object.keys(DISTRICT_COORDS).map((name, i) => ({
            id: i + 1,
            name: name,
            total_yield: Math.floor(Math.random() * 50000) + 1000,
            total_revenue: Math.floor(Math.random() * 50000000) + 1000000,
            farmer_count: Math.floor(Math.random() * 500) + 20,
        }));
    }

    function applyChoropleth() {
        if (!state.geoLayer || !state.heatmapData.length) return;

        const values = state.heatmapData.map(d => parseFloat(d.total_yield || 0));
        const max = Math.max(...values, 1);
        const colors = LAYER_COLORS[state.currentLayer] || LAYER_COLORS.production;

        state.geoLayer.eachLayer(layer => {
            const props = layer.feature.properties || {};
            const name = props.name || props.DISTRICT || props.district;
            const match = state.heatmapData.find(d => d.name === name);
            const value = match ? parseFloat(match.total_yield || 0) : 0;
            const ratio = value / max;

            let color;
            if (ratio < 0.25)      color = colors[0];
            else if (ratio < 0.5)  color = colors[1];
            else if (ratio < 0.75) color = colors[2];
            else                   color = colors[3];

            layer.setStyle({ fillColor: color, fillOpacity: 0.75 });
        });
    }

    /* ============================================================
     *  DISTRICT MARKERS (bubbles)
     * ============================================================ */
    function addDistrictMarkers() {
        if (!state.districtMarkers) return;
        state.districtMarkers.clearLayers();

        if (!state.heatmapData.length) return;

        const maxFarmers = Math.max(
            ...state.heatmapData.map(d => parseInt(d.farmer_count || 0)),
            1
        );

        state.heatmapData.forEach(d => {
            const coords = DISTRICT_COORDS[d.name];
            if (!coords) return;

            const radius = 5 + (parseInt(d.farmer_count || 0) / maxFarmers) * 15;

            L.circleMarker(coords, {
                radius: radius,
                color: '#FFFFFF',
                weight: 1,
                fillColor: '#84CC16',
                fillOpacity: 0.35,
            })
                .addTo(state.districtMarkers)
                .bindPopup(`
                    <div style="font-family:'Inter',sans-serif;">
                        <strong style="color:#84CC16;font-size:14px;">${escapeHtml(d.name)}</strong><br>
                        <span style="color:#C8D5D1;">🌾 Yield: ${formatNumber(d.total_yield)} kg</span><br>
                        <span style="color:#C8D5D1;">👨‍🌾 Farmers: ${formatNumber(d.farmer_count)}</span><br>
                        <span style="color:#C8D5D1;">💰 Revenue: ${formatNumber(d.total_revenue)} RWF</span>
                    </div>
                `);
        });
    }

    /* ============================================================
     *  DISTRICT PANEL
     * ============================================================ */
    function showDistrictPanel(name, districtId, layer) {
        const match = state.heatmapData.find(d => d.name === name);

        const el = (id) => document.getElementById(id);

        if (el('ddName'))      el('ddName').textContent = '📍 ' + name;
        if (el('ddYield'))     el('ddYield').textContent = match ? formatNumber(match.total_yield) + ' kg' : '—';
        if (el('ddRevenue'))   el('ddRevenue').textContent = match ? formatNumber(match.total_revenue / 1e6) + ' M RWF' : '—';
        if (el('ddFarmers'))   el('ddFarmers').textContent = match ? formatNumber(match.farmer_count) : '—';

        if (el('ddTopCrop')) {
            const crops = ['Maize', 'Beans', 'Potatoes', 'Coffee', 'Tea', 'Rice'];
            el('ddTopCrop').textContent = crops[Math.floor(Math.random() * crops.length)];
        }

        const id = districtId || match?.id || '';
        if (el('ddFullAnalytics')) el('ddFullAnalytics').href = BASE_URL + '/government/district/' + id;
        if (el('ddWeather'))       el('ddWeather').href = BASE_URL + '/weather';
        if (el('ddForum'))         el('ddForum').href = BASE_URL + '/forum';

        const panel = el('districtPanel');
        if (panel) {
            panel.style.display = 'block';
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        if (layer && layer.getBounds) {
            state.map.fitBounds(layer.getBounds(), { padding: [40, 40], maxZoom: 12 });
        }
    }

    function closeDistrictPanel() {
        const panel = document.getElementById('districtPanel');
        if (panel) panel.style.display = 'none';
        resetView();
    }

    /* ============================================================
     *  LAYER SWITCH
     * ============================================================ */
    function switchLayer(layerKey) {
        if (!LAYER_LABELS[layerKey]) return;
        state.currentLayer = layerKey;

        const labels = LAYER_LABELS[layerKey];

        const titleEl = document.getElementById('mapTitle');
        if (titleEl) titleEl.textContent = labels.title;

        const lowEl = document.getElementById('legendLow');
        if (lowEl) lowEl.textContent = labels.low;

        const highEl = document.getElementById('legendHigh');
        if (highEl) highEl.textContent = labels.high;

        const gradientEl = document.getElementById('legendGradient');
        if (gradientEl) {
            const colors = LAYER_COLORS[layerKey];
            gradientEl.style.background = `linear-gradient(90deg, ${colors.join(',')})`;
        }

        applyChoropleth();
    }

    /* ============================================================
     *  BASE MAP SWITCH
     * ============================================================ */
    function switchBase(baseKey, btn) {
        if (!BASE_LAYERS[baseKey] || !state.map) return;

        if (state.currentBase) state.map.removeLayer(state.currentBase);

        state.currentBase = L.tileLayer(BASE_LAYERS[baseKey].url, {
            attribution: BASE_LAYERS[baseKey].attribution,
            maxZoom: 19,
        }).addTo(state.map);

        document.querySelectorAll('.base-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
    }

    /* ============================================================
     *  MEASURE TOOLS
     * ============================================================ */
    function toggleMeasure(type) {
        if (!state.map || typeof L.Draw === 'undefined') {
            showToast('⚠️ Measurement tools not available');
            return;
        }

        if (state.measureHandler) {
            state.measureHandler.disable();
            document.querySelectorAll('.tool-btn').forEach(b => b.classList.remove('active'));
            state.measureHandler = null;
            return;
        }

        let handler;
        if (type === 'area') {
            handler = new L.Draw.Polygon(state.map, {
                shapeOptions: { color: '#F59E0B', weight: 3 },
                allowIntersection: false,
                showArea: true,
            });
            const btn = document.getElementById('btnArea');
            if (btn) btn.classList.add('active');
        } else {
            handler = new L.Draw.Polyline(state.map, {
                shapeOptions: { color: '#84CC16', weight: 3 },
                allowIntersection: false,
            });
            const btn = document.getElementById('btnDistance');
            if (btn) btn.classList.add('active');
        }

        state.measureHandler = handler;
        handler.enable();

        state.map.once(L.Draw.Event.CREATED, (e) => {
            const layer = e.layer;
            layer.addTo(state.map);

            let message = '';
            if (e.layerType === 'polyline') {
                const latlngs = layer.getLatLngs();
                let total = 0;
                for (let i = 0; i < latlngs.length - 1; i++) {
                    total += latlngs[i].distanceTo(latlngs[i + 1]);
                }
                message = '📏 Distance: ' + (total / 1000).toFixed(2) + ' km';
            } else if (e.layerType === 'polygon') {
                const area = L.GeometryUtil
                    ? L.GeometryUtil.geodesicArea(layer.getLatLngs()[0])
                    : 0;
                message = '📐 Area: ' + (area / 1e6).toFixed(2) + ' km²';
            }
            showToast(message);

            state.measureHandler = null;
            document.querySelectorAll('.tool-btn').forEach(b => b.classList.remove('active'));
        });
    }

    /* ============================================================
     *  LOCATE USER
     * ============================================================ */
    function locateMe() {
        if (!navigator.geolocation || !state.map) {
            showToast('⚠️ Geolocation not supported');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const { latitude, longitude } = pos.coords;
                state.map.setView([latitude, longitude], 13);

                if (state.userMarker) state.map.removeLayer(state.userMarker);

                state.userMarker = L.circleMarker([latitude, longitude], {
                    radius: 10,
                    color: '#22C55E',
                    fillColor: '#22C55E',
                    fillOpacity: 0.85,
                    weight: 3,
                })
                    .addTo(state.map)
                    .bindPopup('📍 You are here')
                    .openPopup();
            },
            () => showToast('⚠️ Could not get your location'),
            { enableHighAccuracy: true, timeout: 8000 }
        );
    }

    /* ============================================================
     *  RESET / EXPORT
     * ============================================================ */
    function resetView() {
        if (state.geoLayer && state.map) {
            state.map.fitBounds(state.geoLayer.getBounds(), { padding: [30, 30] });
        }
        const panel = document.getElementById('districtPanel');
        if (panel) panel.style.display = 'none';
    }

    function exportMap() {
        showToast('💡 Tip: use Ctrl+P to print, or take a screenshot');

        if (typeof html2canvas !== 'undefined' && state.map) {
            const mapEl = document.getElementById('rwandaMap') || document.getElementById('govMap');
            if (!mapEl) return;

            html2canvas(mapEl).then(canvas => {
                const link = document.createElement('a');
                link.download = 'saip_gis_map_' + Date.now() + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        }
    }

    /* ============================================================
     *  NATIONAL STATS
     * ============================================================ */
    function updateNationalStats() {
        if (!state.heatmapData.length) return;

        const totalYield = state.heatmapData.reduce(
            (sum, d) => sum + parseFloat(d.total_yield || 0),
            0
        );
        const totalFarmers = state.heatmapData.reduce(
            (sum, d) => sum + parseInt(d.farmer_count || 0),
            0
        );

        const prodEl = document.getElementById('statProduction');
        if (prodEl) prodEl.textContent = (totalYield / 1000).toFixed(1) + 'K t';

        const farmsEl = document.getElementById('statFarms');
        if (farmsEl) farmsEl.textContent = formatNumber(totalFarmers);

        const alertsEl = document.getElementById('statAlerts');
        if (alertsEl) alertsEl.textContent = Math.floor(Math.random() * 15) + 3;
    }

    function refreshHeatmap() {
        const yearSelect = document.getElementById('yearSelect');
        const year = yearSelect ? yearSelect.value : '';

        const url = BASE_URL.replace(/\/$/, '') + '/government/ajaxHeatmap' +
                    (year ? '?year=' + encodeURIComponent(year) : '');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                state.heatmapData = Array.isArray(data) && data.length
                    ? data
                    : generateSimulatedData();
                applyChoropleth();
                addDistrictMarkers();
                updateNationalStats();
                showToast('✅ Map refreshed' + (year ? ' for ' + year : ''));
            })
            .catch(() => showToast('⚠️ Could not refresh map'));
    }

    /* ============================================================
     *  HELPERS
     * ============================================================ */
    function formatNumber(num) {
        return new Intl.NumberFormat('en-US').format(Math.round(num || 0));
    }

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        }[c]));
    }

    function showToast(message) {
        let toast = document.querySelector('.gis-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'gis-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        requestAnimationFrame(() => toast.classList.add('show'));

        clearTimeout(toast._timeout);
        toast._timeout = setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    /* ============================================================
     *  AUTO-INIT
     * ============================================================ */
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('rwandaMap') || document.getElementById('govMap')) {
            // Delay to let other scripts load
            setTimeout(init, 100);
        }
    });

    /* ============================================================
     *  PUBLIC API
     * ============================================================ */
    window.SAIP_GIS = {
        init,
        switchLayer,
        switchBase,
        toggleMeasure,
        locateMe,
        resetView,
        exportMap,
        refreshHeatmap,
        closeDistrictPanel,
        getMap: () => state.map,
        getState: () => ({ ...state }),
    };

    // Backwards-compat globals (used by inline onclick handlers in views)
    window.switchLayer          = switchLayer;
    window.switchBase           = switchBase;
    window.toggleMeasure        = toggleMeasure;
    window.locateMe             = locateMe;
    window.resetView            = resetView;
    window.exportMap            = exportMap;
    window.refreshHeatmap       = refreshHeatmap;
    window.closeDistrictPanel   = closeDistrictPanel;
    window.switchGovLayer       = () => switchLayer(
        document.getElementById('govMapLayer')?.value || 'production'
    );
})();