<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <a href="<?= BASE_URL ?>/logistics" class="btn-secondary" style="display:inline-block; margin-bottom:12px;">← Back</a>
            <div class="card-header">
                <h3>🚛 My Fleet</h3>
                <button onclick="document.getElementById('addVehicleModal').style.display='flex'" class="btn-sm">+ Add Vehicle</button>
            </div>
            <div class="fleet-grid">
                <?php foreach ($data['vehicles'] as $v): ?>
                    <div class="glass-card fleet-card">
                        <div class="fleet-icon">🚚</div>
                        <div>
                            <strong><?= e($v['plate_number']) ?></strong>
                            <span class="badge grade"><?= ucfirst($v['vehicle_type']) ?></span>
                            <p>Capacity: <?= number_format($v['capacity_kg']) ?> kg</p>
                            <small>👤 <?= e($v['driver_name']) ?> · 📞 <?= e($v['driver_phone']) ?></small>
                            <span class="status-pill <?= $v['is_available'] ? 'paid' : 'pending' ?>"><?= $v['is_available'] ? 'Available' : 'Busy' ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>

<div id="addVehicleModal" class="modal-overlay" style="display:none;">
    <div class="glass-card modal">
        <h3>Add Vehicle</h3>
        <form method="POST" action="<?= BASE_URL ?>/logistics/addVehicle">
            <div class="form-group"><label>Plate Number</label><input type="text" name="plate_number" required placeholder="RAD 123 A"></div>
            <div class="form-group">
                <label>Type</label>
                <select name="vehicle_type">
                    <option value="truck">Truck</option><option value="van">Van</option>
                    <option value="pickup">Pickup</option><option value="motorcycle">Motorcycle</option>
                </select>
            </div>
            <div class="form-group"><label>Capacity (kg)</label><input type="number" name="capacity_kg" value="1000"></div>
            <div class="form-group"><label>Driver Name</label><input type="text" name="driver_name"></div>
            <div class="form-group"><label>Driver Phone</label><input type="tel" name="driver_phone"></div>
            <div class="action-buttons">
                <button class="btn-primary">Save</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('addVehicleModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>