<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <a href="<?= BASE_URL ?>/cooperative/dashboard/<?= $data['coop']['id'] ?>" class="btn-secondary" style="display:inline-block; margin-bottom:12px;">← Back</a>
            <div class="card-header">
                <h3>🌾 Harvest Records</h3>
                <button onclick="document.getElementById('addHarvestModal').style.display='flex'" class="btn-sm">+ Record Harvest</button>
            </div>
            <table class="data-table">
                <thead><tr><th>Crop</th><th>Yield (kg)</th><th>Area (ha)</th><th>Grade</th><th>Season</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($data['harvests'] as $h): ?>
                    <tr>
                        <td><?= $h['icon'] ?> <?= e($h['crop_name']) ?></td>
                        <td><?= number_format($h['total_yield_kg']) ?></td>
                        <td><?= number_format($h['total_area_ha'], 2) ?></td>
                        <td><span class="badge grade"><?= $h['quality_grade'] ?></span></td>
                        <td><?= e($h['season']) ?> <?= $h['year'] ?></td>
                        <td><?= date('M d, Y', strtotime($h['harvest_date'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<div id="addHarvestModal" class="modal-overlay" style="display:none;">
    <div class="glass-card modal">
        <h3>Record Harvest</h3>
        <form method="POST" action="<?= BASE_URL ?>/cooperative/addHarvest/<?= $data['coop']['id'] ?>">
            <div class="form-group">
                <label>Crop ID</label>
                <input type="number" name="crop_id" required placeholder="1=Maize, 2=Beans, 5=Potatoes">
            </div>
            <div class="form-group"><label>Total Yield (kg)</label><input type="number" step="0.01" name="total_yield_kg" required></div>
            <div class="form-group"><label>Area (ha)</label><input type="number" step="0.01" name="total_area_ha"></div>
            <div class="form-group">
                <label>Quality Grade</label>
                <select name="quality_grade"><option>A</option><option>B</option><option>C</option><option>Organic</option><option selected>Standard</option></select>
            </div>
            <div class="form-group">
                <label>Season</label>
                <select name="season"><option>A</option><option>B</option><option>C</option></select>
            </div>
            <div class="form-group"><label>Harvest Date</label><input type="date" name="harvest_date" value="<?= date('Y-m-d') ?>"></div>
            <div class="action-buttons">
                <button class="btn-primary">Save</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('addHarvestModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>