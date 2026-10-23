<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header"><h3>🛡️ Audit Logs</h3></div>
            <table class="data-table">
                <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>IP</th></tr></thead>
                <tbody>
                <?php foreach ($data['logs'] as $l): ?>
                    <tr>
                        <td><?= date('M d H:i:s', strtotime($l['created_at'])) ?></td>
                        <td><?= e(($l['first_name'] ?? '—') . ' ' . ($l['last_name'] ?? '')) ?></td>
                        <td><span class="badge green"><?= e($l['action']) ?></span></td>
                        <td><?= e($l['entity']) ?> #<?= $l['entity_id'] ?></td>
                        <td><?= e($l['ip_address']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>