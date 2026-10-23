<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header"><h3>🔐 Login History</h3></div>
            <table class="data-table">
                <thead><tr><th>Time</th><th>User</th><th>Email</th><th>IP</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($data['logs'] as $l): ?>
                    <tr>
                        <td><?= date('M d H:i', strtotime($l['login_at'])) ?></td>
                        <td><?= e(($l['first_name'] ?? '—') . ' ' . ($l['last_name'] ?? '')) ?></td>
                        <td><?= e($l['email_attempted']) ?></td>
                        <td><?= e($l['ip_address']) ?></td>
                        <td>
                            <span class="status-pill <?= $l['status'] === 'success' ? 'paid' : ($l['status'] === 'failed' ? 'cancelled' : 'pending') ?>">
                                <?= ucfirst($l['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>