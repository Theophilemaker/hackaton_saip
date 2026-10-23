// Weather page interactivity + map
document.addEventListener('DOMContentLoaded', () => {
    initRwandaMap();
    initHeatmapSelector();
});

let rwandaMap;
let districtLayer;
let productionData = [];

// ============ MAP INIT ============
function initRwandaMap() {
    const mapEl = document.getElementById('rwandaMap');
    if (!mapEl) return;

    // Rwanda center
    rwandaMap = L.map('rwandaMap').setView([-1.9403, 29.8739], 8);

    // Tile layer (dark theme matches SAIP)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        maxZoom: 19
    }).addTo(rwandaMap);

    // Load GeoJSON districts
    fetch(BASE_URL + '/gis/geojson')
        .then(res => res.json())
        .then(geojson => {
            districtLayer = L.geoJSON(geojson, {
                style: defaultDistrictStyle,
                onEachFeature: onEachDistrict
            }).addTo(rwandaMap);

            // Load production heatmap data
            fetch(BASE_URL + '/weather/heatmap')
                .then(res => res.json())
                .then(data => {
                    productionData = data;
                    applyChoropleth();
                });
        })
        .catch(err => console.error('GeoJSON load error:', err));
}

// ============ STYLING ============
function defaultDistrictStyle() {
    return {
        fillColor: '#16A34A',
        weight: 1.5,
        opacity: 1,
        color: 'rgba(255,255,255,0.4)',
        fillOpacity: 0.4
    };
}

function onEachDistrict(feature, layer) {
    const name = feature.properties.name || feature.properties.DISTRICT || 'District';
    layer.bindTooltip(name, { sticky: true, className: 'district-tooltip' });

    layer.on('click', () => {
        // Zoom + fetch live weather for this district
        rwandaMap.fitBounds(layer.getBounds());
        const center = layer.getBounds().getCenter();
        fetchWeatherForDistrict(center.lat, center.lng, name);
    });

    layer.on('mouseover', () => {
        layer.setStyle({ fillOpacity: 0.7, weight: 2.5, color: '#84CC16' });
    });
    layer.on('mouseout', () => {
        districtLayer.resetStyle(layer);
    });
}

// ============ CHOROPLETH HEATMAP ============
function applyChoropleth() {
    if (!districtLayer || !productionData.length) return;

    const max = Math.max(...productionData.map(d => d.total_yield || 0), 1);

    districtLayer.eachLayer(layer => {
        const name = layer.feature.properties.name || layer.feature.properties.DISTRICT;
        const match = productionData.find(d => d.name === name);
        const value = match ? (match.total_yield || 0) : 0;
        const color = getHeatColor(value / max);
        layer.setStyle({ fillColor: color, fillOpacity: 0.7 });
    });
}

function getHeatColor(ratio) {
    // Green → Yellow → Red gradient
    if (ratio < 0.33) return '#16A34A';
    if (ratio < 0.66) return '#84CC16';
    if (ratio < 0.85) return '#F59E0B';
    return '#EF4444';
}

// ============ DISTRICT WEATHER FETCH ============
function fetchWeatherForDistrict(lat, lng, name) {
    fetch(`${BASE_URL}/weather/api?lat=${lat}&lng=${lng}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;
            showDistrictPopup(name, data);
        });
}

function showDistrictPopup(name, data) {
    const html = `
        <div class="district-popup">
            <h4>${name}</h4>
            <p>🌡️ ${data.current.temperature}°C — ${data.current.description}</p>
            <p>💧 Humidity: ${data.current.humidity}%</p>
            <p>🌬️ Wind: ${data.current.wind_speed} km/h</p>
            <hr>
            <small>${data.advice.planting[0]?.text || 'No planting advice'}</small>
        </div>
    `;
    L.popup().setLatLng([lat, lng]).setContent(html).openOn(rwandaMap);
}

// ============ LAYER SWITCHER ============
function initHeatmapSelector() {
    const sel = document.getElementById('heatmapLayer');
    if (!sel) return;
    sel.addEventListener('change', (e) => {
        // Future: load different datasets per layer
        applyChoropleth();
    });
}