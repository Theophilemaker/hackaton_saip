<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header">
                <h3>🏡 Add New Farm</h3>
                <a href="<?= BASE_URL ?>/crops/farms" class="btn-secondary">← Back</a>
            </div>

            <?php if (!empty($data['error'])): ?>
                <div class="alert error"><?= e($data['error']) ?></div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/crops/newFarm" method="POST" class="alert-form">
                <?= \App\Core\Security::csrfField() ?>

                <div class="form-group">
                    <label>Farm Name *</label>
                    <input type="text" name="name" required maxlength="150"
                           placeholder="e.g., Kigali Hillside Farm"
                           value="<?= e($_POST['name'] ?? '') ?>">
                </div>

                <h4 class="section-title">📍 Location</h4>

                <!-- Cascading geography dropdowns -->
                <?php 
                $prefix = 'Farm';
                require __DIR__ . '/../partials/geo_select.php';
                ?>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Farm Size (hectares)</label>
                        <input type="number" name="size_ha" step="0.01" min="0.01"
                               placeholder="2.5"
                               value="<?= e($_POST['size_ha'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Soil Type</label>
                        <select name="soil_type">
                            <?php foreach (['clay','sandy','loam','silt','peat','chalk'] as $st): ?>
                                <option value="<?= $st ?>" <?= (($_POST['soil_type'] ?? 'loam') === $st) ? 'selected' : '' ?>>
                                    <?= ucfirst($st) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Irrigation Type</label>
                    <select name="irrigation_type">
                        <option value="none">None (rain-fed)</option>
                        <option value="drip">Drip irrigation</option>
                        <option value="sprinkler">Sprinkler</option>
                        <option value="furrow">Furrow</option>
                        <option value="flood">Flood</option>
                    </select>
                </div>

                <h4 class="section-title">📌 GPS Coordinates (optional)</h4>
                <p class="input-hint" style="margin-bottom:12px;">
                    Used for weather + map features. Auto-fill with your device location.
                </p>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Latitude</label>
                        <input type="number" name="lat" step="0.0000001" id="farmLat"
                               placeholder="-1.9403" value="<?= e($_POST['lat'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Longitude</label>
                        <input type="number" name="lng" step="0.0000001" id="farmLng"
                               placeholder="29.8739" value="<?= e($_POST['lng'] ?? '') ?>">
                    </div>
                </div>

                <button type="button" class="btn-secondary" onclick="getFarmLocation()" style="margin-bottom:20px;">
                    📍 Use My Current Location
                </button>

                <div class="action-buttons">
                    <button type="submit" class="btn-primary">💾 Save Farm</button>
                    <a href="<?= BASE_URL ?>/crops/farms" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </section>
    </main>
</div>

<script>
function getFarmLocation() {
    if (!navigator.geolocation) {
        alert('Geolocation not supported');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            document.getElementById('farmLat').value = pos.coords.latitude.toFixed(7);
            document.getElementById('farmLng').value = pos.coords.longitude.toFixed(7);
        },
        (err) => alert('Could not get location: ' + err.message),
        { enableHighAccuracy: true, timeout: 10000 }
    );
}
</script>