<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card coop-hero">
            <div>
                <h1>👥 <?= e($data['coop']['name']) ?></h1>
                <p><?= e($data['coop']['district_name'] ?? '') ?> · Reg: <?= e($data['coop']['registration_no'] ?? '—') ?></p>
            </div>
            <div class="action-buttons-row">
                <a href="<?= BASE_URL ?>/cooperative/members/<?= $data['coop']['id'] ?>" class="btn-secondary">👥 Members</a>
                <a href="<?= BASE_URL ?>/cooperative/harvests/<?= $data['coop']['id'] ?>" class="btn-secondary">🌾 Harvests</a>
                <a href="<?= BASE_URL ?>/cooperative/contributions/<?= $data['coop']['id'] ?>" class="btn-secondary">💰 Contributions</a>
                <a href="<?= BASE_URL ?>/cooperative/equipment/<?= $data['coop']['id'] ?>" class="btn-secondary">🔧 Equipment</a>
                <a href="<?= BASE_URL ?>/cooperative/meetings/<?= $data['coop']['id'] ?>" class="btn-secondary">📅 Meetings</a>
            </div>
        </section>

        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">👥</div>
                <div><p class="kpi-label">Members</p><h3 class="kpi-value"><?= number_format($data['stats']['members']) ?></h3></div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#84CC16,#16A34A)">🌾</div>
                <div><p class="kpi-label">Total Harvest</p><h3 class="kpi-value"><?= number_format($data['stats']['total_harvest']/1000, 1) ?> <small>t</small></h3></div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">💰</div>
                <div><p class="kpi-label">Contributions</p><h3 class="kpi-value"><?= number_format($data['stats']['total_contributions']/1e6, 1) ?> <small>M RWF</small></h3></div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">🔧</div>
                <div><p class="kpi-label">Equipment</p><h3 class="kpi-value"><?= $data['stats']['equipment_count'] ?></h3></div>
            </div>
        </section>

        <section class="gov-grid-2">
            <div class="glass-card">
                <div class="card-header"><h3>🌾 Recent Harvests</h3></div>
                <?php if (empty($data['harvests'])): ?>
                    <p class="empty-state">No harvests recorded.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead><tr><th>Crop</th><th>Yield</th><th>Season</th><th>Date</th></tr></thead>
                        <tbody>
                        <?php foreach (array_slice($data['harvests'], 0, 6) as $h): ?>
                            <tr>
                                <td><?= $h['icon'] ?> <?= e($h['crop_name']) ?></td>
                                <td><?= number_format($h['total_yield_kg']) ?> kg</td>
                                <td><?= e($h['season']) ?> <?= $h['year'] ?></td>
                                <td><?= date('M d', strtotime($h['harvest_date'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="glass-card">
                <div class="card-header"><h3>📅 Upcoming Meetings</h3></div>
                <?php if (empty($data['meetings'])): ?>
                    <p class="empty-state">No meetings scheduled.</p>
                <?php else: ?>
                    <?php foreach (array_slice($data['meetings'], 0, 5) as $m): ?>
                        <div class="meeting-row">
                            <div class="meeting-date-badge"><?= date('d', strtotime($m['meeting_date'])) ?><small><?= date('M', strtotime($m['meeting_date'])) ?></small></div>
                            <div>
                                <strong><?= e($m['title']) ?></strong>
                                <small><?= e($m['location']) ?> · <?= $m['attended_count'] ?> attended</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>