<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <a href="<?= BASE_URL ?>/cooperative/dashboard/<?= $data['coop']['id'] ?>" class="btn-secondary" style="display:inline-block; margin-bottom:12px;">← Back</a>
            <div class="card-header">
                <h3>👥 Members (<?= count($data['members']) ?>)</h3>
                <button onclick="document.getElementById('addMemberModal').style.display='flex'" class="btn-sm">+ Add Member</button>
            </div>
            <table class="data-table">
                <thead><tr><th>Name</th><th>Phone</th><th>Role</th><th>Shares</th><th>Joined</th></tr></thead>
                <tbody>
                <?php foreach ($data['members'] as $m): ?>
                    <tr>
                        <td><?= e($m['first_name'].' '.$m['last_name']) ?></td>
                        <td><?= e($m['phone']) ?></td>
                        <td><span class="role-pill <?= $m['role'] ?>"><?= ucfirst(str_replace('_',' ', $m['role'])) ?></span></td>
                        <td><?= $m['shares'] ?></td>
                        <td><?= date('M d, Y', strtotime($m['joined_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<!-- Add Member Modal -->
<div id="addMemberModal" class="modal-overlay" style="display:none;">
    <div class="glass-card modal">
        <h3>Add Member</h3>
        <form method="POST" action="<?= BASE_URL ?>/cooperative/addMember/<?= $data['coop']['id'] ?>">
            <div class="form-group">
                <label>User ID or Email</label>
                <input type="text" name="user_id" required placeholder="Enter user ID">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="member">Member</option>
                    <option value="secretary">Secretary</option>
                    <option value="treasurer">Treasurer</option>
                    <option value="vice_chair">Vice Chair</option>
                </select>
            </div>
            <div class="form-group">
                <label>Member Number</label>
                <input type="text" name="member_no">
            </div>
            <div class="action-buttons">
                <button type="submit" class="btn-primary">Add</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('addMemberModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>