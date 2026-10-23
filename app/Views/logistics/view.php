<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header">
                <h3>🚚 Transport #<?= $data['request']['id'] ?></h3>
                <span class="status-pill <?= $data['request']['status'] ?>"><?= ucfirst(str_replace('_',' ', $data['request']['status'])) ?></span>
            </div>

            <div class="gov-grid-2">
                <div>
                    <p><strong>📍 Pickup:</strong> <?= e($data['request']['pickup_location']) ?></p>
                    <p><strong>🎯 Dropoff:</strong> <?= e($data['request']['dropoff_location']) ?></p>
                    <p><strong>📦 Cargo:</strong> <?= e($data['request']['cargo_description']) ?></p>
                    <p><strong>⚖️ Weight:</strong> <?= number_format($data['request']['weight_kg']) ?> kg</p>
                    <p><strong>📏 Distance:</strong> <?= number_format($data['request']['distance_km'], 1) ?> km</p>
                    <p><strong>💰 Fee:</strong> <?= number_format($data['request']['fee_rwf']) ?> RWF</p>
                </div>
                <div>
                    <p><strong>🚛 Vehicle:</strong> <?= e($data['request']['plate_number'] ?? 'Not assigned') ?></p>
                    <p><strong>👤 Driver:</strong> <?= e($data['request']['driver_name'] ?? '—') ?></p>
                    <p><strong>📞 Phone:</strong> <?= e($data['request']['driver_phone'] ?? '—') ?></p>
                </div>
            </div>

            <div id="trackingMap" style="height: 400px; border-radius: 16px; margin-top: 20px;"></div>

            <div class="action-buttons" style="margin-top: 20px;">
                <button onclick="updateStatus('in_transit')" class="btn-secondary">▶ In Transit</button>
                <button onclick="updateStatus('delivered')" class="btn-primary">✅ Delivered</button>
            </div>
        </section>
    </main>
</div>

<script>
const reqId = <?= $data['request']['id'] ?>;
const pickupLat = <?= $data['request']['pickup_lat'] ?? 'null' ?>;
const pickupLng = <?= $data['request']['pickup_lng'] ?? 'null' ?>;
const dropLat = <?= $data['request']['dropoff_lat'] ?? 'null' ?>;
const dropLng = <?= $data['request']['dropoff_lng'] ?? 'null' ?>;

let map, vehicleMarker;
document.addEventListener('DOMContentLoaded', () => {
    const center = pickupLat ? [pickupLat, pickupLng] : [-1.9403, 29.8739];
    map = L.map('trackingMap').setView(center, 9);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

    if (pickupLat) L.marker([pickupLat, pickupLng]).addTo(map).bindPopup('📍 Pickup');
    if (dropLat) L.marker([dropLat, dropLng]).addTo(map).bindPopup('🎯 Dropoff');

    // Poll every 5s for vehicle position
    setInterval(pollTrack, 5000);
});

async function pollTrack() {
    const res = await fetch(`<?= BASE_URL ?>/logistics/track/${reqId}`);
    const data = await res.json();
    if (data.lat && data.lng) {
        if (vehicleMarker) {
            vehicleMarker.setLatLng([data.lat, data.lng]);
        } else {
            vehicleMarker = L.marker([data.lat, data.lng], { icon: L.divIcon({ html: '🚚', className: 'vehicle-marker', iconSize: [30,30] }) }).addTo(map);
        }
    }
}

async function updateStatus(status) {
    await fetch(`<?= BASE_URL ?>/logistics/updateStatus/${reqId}`, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `status=${status}`
    });
    window.location.reload();
}
</script>