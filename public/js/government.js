let govMap = null;
let govLayer = null;
let heatmapData = [];
let currentMapMode = 'production';

document.addEventListener('DOMContentLoaded', () => {
    initGovMap();
});

/* ============ MAP ============ */
function initGovMap() {
    const el = document.getElementById('govMap');
    if (!el) return;

    govMap = L.map('govMap').setView([-1.9403, 29.8739], 8);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO'
    }).addTo(govMap);

    // Add scale bar
    L.control.scale({ imperial: false }).addTo(govMap);

    // Load GeoJSON districts
    fetch(BASE_URL + '/gis/geojson')
        .then(r => r.json())
        .then(geojson => {
            govLayer = L.geoJSON(geojson, {
                style: districtStyle,
                onEachFeature: bindDistrict
            }).addTo(govMap);

            loadHeatmap();
        });
}

function districtStyle() {
    return {
        fillColor: '#16A34A',
        weight: 1.5,
        color: 'rgba(255,255,255,0.4)',
        fillOpacity: 0.5
    };
}

function bindDistrict(feature, layer) {
    const name = feature.properties.name || feature.properties.DISTRICT || 'District';
    layer.bindTooltip(name, { sticky: true, className: 'district-tooltip' });

    layer.on('mouseover', () => layer.setStyle({ fillOpacity: 0.85, weight: 2.5, color: '#84CC16' }));
    layer.on('mouseout', () => govLayer.resetStyle(layer));
    layer.on('click', () => openDistrictPanel(feature, layer));
}

/* ============ HEATMAP ============ */
function loadHeatmap() {
    fetch(BASE_URL + '/government/ajaxHeatmap')
        .then(r => r.json())
        .then(data => {
            heatmapData = data;
            applyChoropleth();
        });
}

function applyChoropleth() {
    if (!govLayer || !heatmapData.length) return;
    const max = Math.max(...heatmapData.map(d => parseFloat(d.total_yield) || 0), 1);

    govLayer.eachLayer(layer => {
        const name = layer.feature.properties.name || layer.feature.properties.DISTRICT;
        const match = heatmapData.find(d => d.name === name);
        const val = match ? parseFloat(match.total_yield || 0) : 0;
        const ratio = val / max;

        let color;
        if (currentMapMode === 'production') {
            color = ratio < 0.33 ? '#16A34A' : ratio < 0.66 ? '#84CC16' : ratio < 0.85 ? '#F59E0B' : '#EF4444';
        } else if (currentMapMode === 'rainfall') {
            color = ratio < 0.33 ? '#0EA5E9' : ratio < 0.66 ? '#06B6D4' : ratio < 0.85 ? '#3B82F6' : '#6366F1';
        } else {
            color = ratio < 0.33 ? '#F59E0B' : ratio < 0.66 ? '#84CC16' : '#22C55E';
        }
        layer.setStyle({ fillColor: color, fillOpacity: 0.75 });
    });
}

function switchGovLayer() {
    currentMapMode = document.getElementById('govMapLayer').value;
    applyChoropleth();
}

/* ============ DISTRICT PANEL ============ */
function openDistrictPanel(feature, layer) {
    const name = feature.properties.name || feature.properties.DISTRICT;
    const match = heatmapData.find(d => d.name === name);
    const revenue = match ? parseFloat(match.total_revenue || 0) : 0;
    const farmers = match ? parseInt(match.farmer_count || 0) : 0;

    const html = `
        <div class="district-popup">
            <h4>📍 ${name}</h4>
            <p>🌾 Yield: <strong>${(parseFloat(match?.total_yield||0)/1000).toFixed(1)} t</strong></p>
            <p>💰 Revenue: <strong>${(revenue/1e6).toFixed(1)} M RWF</strong></p>
            <p>👨‍🌾 Farmers: <strong>${farmers}</strong></p>
            <a href="${BASE_URL}/government/district/${match?.id || ''}" class="btn-sm" style="display:inline-block;margin-top:8px;">Open Full Analytics →</a>
        </div>
    `;
    L.popup().setLatLng(layer.getBounds().getCenter()).setContent(html).openOn(govMap);

    // Fly to district
    govMap.fitBounds(layer.getBounds(), { padding: [40, 40] });
}

/* ============ EXPORT ============ */
function exportData(dataType, format) {
    window.location = `${BASE_URL}/government/export?data=${dataType}&format=${format}`;
}