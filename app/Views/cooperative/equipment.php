<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <a href="<?= BASE_URL ?>/cooperative/dashboard/<?= $data['coop']['id'] ?>" class="btn-secondary" style="display:inline-block; margin-bottom:12px;">← Back</a>
            <div class="card-header">
                <h3>🔧 Equipment</h3>
                <button onclick="document.getElementById('addEqModal').style.display='flex'" class="btn-sm">+ Add</button>
            </div>
            <table class="data-table">
                <thead><tr><th>Name</th><th>Category</th><th>Qty</th><th>Condition</th><th>Location</th><th>Assigned</th></tr></thead>
                <tbody>
                <?php foreach ($data['equipment'] as $e): ?>
                    <tr>
                        <td><?= e($e['name']) ?></td>
                        <td><span class="badge grade"><?= ucfirst($e['category']) ?></span></td>
                        <td><?= $e['quantity'] ?></td>
                        <td><span class="status-pill <?= $e['condition_status'] === 'excellent' || $e['condition_status'] === 'good' ? 'paid' : 'cancelled' ?>"><?= ucfirst(str_replace('_',' ', $e['condition_status'])) ?></span></td>
                        <td><?= e($e['location']) ?></td>
                        <td><?= e($e['assigned_name'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<div id="addEqModal" class="modal-overlay" style="display:none;">
    <div class="glass-card modal">
        <h3>Add Equipment</h3>
        <form method="POST" action="<?= BASE_URL ?>/cooperative/addEquipment/<?= $data['coop']['id'] ?>">
            <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="tractor">Tractor</option><option value="irrigation">Irrigation</option>
                    <option value="harvesting">Harvesting</option><option value="processing">Processing</option>
                    <option value="transport">Transport</option><option value="tool">Tool</option><option value="other">Other</option>
                </select>
            </div>
            <div class="form-group"><label>Quantity</label><input type="number" name="quantity" value="1"></div>
            <div class="form-group">
                <label>Condition</label>
                <select name="condition_status"><option value="excellent">Excellent</option><option value="good" selected>Good</option><option value="fair">Fair</option><option value="needs_repair">Needs Repair</option><option value="broken">Broken</option></select>
            </div>
            <div class="form-group"><label>Location</label><input type="text" name="location"></div>
            <div class="action-buttons">
                <button class="btn-primary">Save</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('addEqModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>