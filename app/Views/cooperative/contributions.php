<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <a href="<?= BASE_URL ?>/cooperative/dashboard/<?= $data['coop']['id'] ?>" class="btn-secondary" style="display:inline-block; margin-bottom:12px;">← Back</a>
            <div class="card-header">
                <h3>💰 Contributions</h3>
                <button onclick="document.getElementById('addContribModal').style.display='flex'" class="btn-sm">+ Record</button>
            </div>
            <table class="data-table">
                <thead><tr><th>Member</th><th>Amount (RWF)</th><th>Purpose</th><th>Method</th><th>Status</th><th>Receipt</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($data['contributions'] as $c): ?>
                    <tr>
                        <td><?= e($c['first_name'].' '.$c['last_name']) ?></td>
                        <td><strong><?= number_format($c['amount_rwf']) ?></strong></td>
                        <td><?= e($c['purpose']) ?></td>
                        <td><?= strtoupper($c['payment_method']) ?></td>
                        <td><span class="status-pill <?= $c['status'] === 'paid' ? 'paid' : 'pending' ?>"><?= ucfirst($c['status']) ?></span></td>
                        <td><?= e($c['receipt_no']) ?></td>
                        <td><?= date('M d', strtotime($c['contribution_date'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<div id="addContribModal" class="modal-overlay" style="display:none;">
    <div class="glass-card modal">
        <h3>Record Contribution</h3>
        <form method="POST" action="<?= BASE_URL ?>/cooperative/addContribution/<?= $data['coop']['id'] ?>">
            <div class="form-group"><label>User ID</label><input type="number" name="user_id" required></div>
            <div class="form-group"><label>Amount (RWF)</label><input type="number" step="0.01" name="amount_rwf" required></div>
            <div class="form-group"><label>Purpose</label><input type="text" name="purpose" placeholder="Monthly contribution"></div>
            <div class="form-group">
                <label>Method</label>
                <select name="payment_method"><option value="cash">Cash</option><option value="momo">MoMo</option><option value="bank">Bank</option><option value="in_kind">In Kind</option></select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status"><option value="paid">Paid</option><option value="pending">Pending</option><option value="overdue">Overdue</option></select>
            </div>
            <div class="action-buttons">
                <button class="btn-primary">Save</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('addContribModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>