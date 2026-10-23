<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header"><h3>💻 Active Sessions</h3></div>
            <table class="data-table">
                <thead><tr><th>User</th><th>Device</th><th>IP</th><th>Last Activity</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($data['sessions'] as $s): ?>
                    <tr>
                        <td><?= e($s['first_name'] . ' ' . $s['last_name']) ?></td>
                        <td><?= e($s['device_type']) ?></td>
                        <td><?= e($s['ip_address']) ?></td>
                        <td><?= date('M d H:i', strtotime($s['last_activity'])) ?></td>
                        <td>
                            <form method="POST" action="<?= BASE_URL ?>/security/killSession/<?= $s['id'] ?>" style="display:inline;">
                                <?= \App\Core\Security::csrfField() ?>
                                <button class="btn-sm danger">Kill</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>