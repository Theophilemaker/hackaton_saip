<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header"><h3>🚚 New Transport Request</h3></div>
            <form method="POST" action="<?= BASE_URL ?>/logistics/request" class="alert-form">
                <div class="form-group">
                    <label>Pickup Location</label>
                    <input type="text" name="pickup_location" required placeholder="Kigali - Nyarugenge">
                    <button type="button" class="btn-sm" onclick="getLocation('pickup')" style="margin-top:6px;">📍 Use My Location</button>
                    <input type="hidden" name="pickup_lat" id="pickup_lat">
                    <input type="hidden" name="pickup_lng" id="pickup_lng">
                </div>
                <div class="form-group">
                    <label>Dropoff Location</label>
                    <input type="text" name="dropoff_location" required placeholder="Musanze - Kinigi">
                    <button type="button" class="btn-sm" onclick="getLocation('dropoff')" style="margin-top:6px;">📍 Use My Location</button>
                    <input type="hidden" name="dropoff_lat" id="dropoff_lat">
                    <input type="hidden" name="dropoff_lng" id="dropoff_lng">
                </div>
                <div class="form-group"><label>Cargo Description</label><input type="text" name="cargo_description" placeholder="500kg Maize bags"></div>
                <div class="form-group"><label>Weight (kg)</label><input type="number" name="weight_kg"></div>
                <div class="form-group"><label>Scheduled At</label><input type="datetime-local" name="scheduled_at"></div>
                <div class="form-group"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
                <button class="btn-primary">Submit Request</button>
            </form>
        </section>
    </main>
</div>

<script>
function getLocation(field) {
    if (!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(pos => {
        document.getElementById(field + '_lat').value = pos.coords.latitude;
        document.getElementById(field + '_lng').value = pos.coords.longitude;
    });
}
</script>