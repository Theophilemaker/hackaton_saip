<div class="dashboard-layout admin-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header">
                <div><h3>💾 System Backup</h3><p class="card-subtitle">Create and manage database and file backups</p></div>
                <a href="<?= BASE_URL ?>/admin" class="btn-secondary-sm">← Back</a>
            </div>

            <div class="backup-stats">
                <div class="stat-box">
                    <div class="stat-icon">🗄️</div>
                    <div><span class="stat-label">Database Size</span><span class="stat-value"><?= round($data['dbSize'] / 1024 / 1024, 2) ?> MB</span></div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">📁</div>
                    <div><span class="stat-label">Uploads Size</span><span class="stat-value"><?= round($data['diskUsage'] / 1024 / 1024, 2) ?> MB</span></div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">📦</div>
                    <div><span class="stat-label">Total Backups</span><span class="stat-value"><?= count($data['backups']) ?></span></div>
                </div>
            </div>

            <div class="backup-actions">
                <button class="btn-primary" onclick="createBackup('database')">
                    🗄️ Backup Database
                </button>
                <button class="btn-primary" onclick="createBackup('files')">
                    📁 Backup Files
                </button>
            </div>
        </section>

        <section class="glass-card">
            <div class="card-header">
                <div><h3>📋 Backup History</h3><p class="card-subtitle">All previous backups</p></div>
            </div>

            <?php if (empty($data['backups'])): ?>
                <div class="empty-state"><div class="empty-icon">💾</div><h3>No Backups Yet</h3><p>Create your first backup above.</p></div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Type</th>
                                <th class="num-col">Size</th>
                                <th>Created By</th>
                                <th>Date</th>
                                <th class="actions-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['backups'] as $b): ?>
                                <tr>
                                    <td><code class="file-code"><?= e($b['filename']) ?></code></td>
                                    <td><span class="type-badge"><?= strtoupper(e($b['backup_type'])) ?></span></td>
                                    <td class="num-col"><?= round($b['file_size'] / 1024, 1) ?> KB</td>
                                    <td><?= e(($b['first_name'] ?? '') . ' ' . ($b['last_name'] ?? '')) ?></td>
                                    <td class="date-cell"><?= date('M d, Y H:i', strtotime($b['created_at'])) ?></td>
                                    <td class="actions-cell">
                                        <a href="<?= BASE_URL ?>/admin/downloadBackup/<?= $b['id'] ?>" class="icon-action download" title="Download">⬇️</a>
                                        <button class="icon-action delete" title="Delete" onclick="deleteBackup(<?= $b['id'] ?>)">🗑️</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<script>
async function createBackup(type) {
    if (!confirm(`Create a new ${type} backup? This may take a moment.`)) return;
    showToast(`⏳ Creating ${type} backup...`, 'success');

    const fd = new FormData();
    fd.append('type', type);

    try {
        const res = await fetch('<?= BASE_URL ?>/admin/createBackup', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            showToast(`✅ Backup created (${data.size})`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Backup failed', 'error');
        }
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
    }
}

async function deleteBackup(id) {
    if (!confirm('Delete this backup file? This cannot be undone.')) return;
    try {
        const res = await fetch('<?= BASE_URL ?>/admin/deleteBackup/' + id);
        const data = await res.json();
        if (data.success) {
            showToast('Backup deleted.', 'success');
            setTimeout(() => location.reload(), 700);
        } else {
            showToast(data.error || 'Delete failed', 'error');
        }
    } catch (e) { showToast('Error: ' + e.message, 'error'); }
}

function showToast(msg, type) {
    const t = document.createElement('div');
    t.className = 'toast ' + type; t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 2600);
}
</script>

<style>
.admin-page.dashboard-layout { display: flex !important; width: 100vw !important; min-height: 100vh !important; background-color: #0b1120 !important; }
.admin-page .main-content { flex: 1 !important; width: calc(100% - 250px) !important; padding: 32px !important; box-sizing: border-box !important; }
.admin-page .glass-card { background: rgba(30,41,59,0.65) !important; backdrop-filter: blur(16px) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 20px !important; padding: 28px !important; margin-bottom: 24px !important; }

.card-header { display: flex !important; justify-content: space-between !important; align-items: center !important; margin-bottom: 20px !important; padding-bottom: 14px !important; border-bottom: 1px solid rgba(255,255,255,0.08) !important; flex-wrap: wrap !important; gap: 12px !important; }
.card-header h3 { font-size: 18px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 !important; }
.card-subtitle { font-size: 12.5px !important; color: #94a3b8 !important; margin: 4px 0 0 0 !important; }
.btn-secondary-sm { padding: 10px 18px !important; background: rgba(255,255,255,0.05) !important; color: #cbd5e1 !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; font-weight: 700 !important; text-decoration: none !important; font-size: 12.5px !important; }

.backup-stats { display: grid !important; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important; gap: 16px !important; margin-bottom: 24px !important; }
.stat-box { display: flex !important; align-items: center !important; gap: 16px !important; padding: 20px !important; background: rgba(0,0,0,0.2) !important; border: 1px solid rgba(255,255,255,0.06) !important; border-radius: 14px !important; }
.stat-icon { width: 48px !important; height: 48px !important; border-radius: 12px !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 22px !important; background: rgba(56,189,248,0.1) !important; border: 1px solid rgba(56,189,248,0.2) !important; }
.stat-label { display: block !important; font-size: 11px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.8px !important; margin-bottom: 4px !important; }
.stat-value { display: block !important; font-size: 20px !important; font-weight: 800 !important; color: #f1f5f9 !important; }

.backup-actions { display: flex !important; gap: 12px !important; flex-wrap: wrap !important; }
.btn-primary { display: inline-flex !important; align-items: center !important; gap: 8px !important; padding: 14px 28px !important; background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; border: none !important; border-radius: 10px !important; font-size: 14px !important; font-weight: 700 !important; cursor: pointer !important; box-shadow: 0 4px 15px rgba(16,185,129,0.3) !important; }
.btn-primary:hover { transform: translateY(-2px) !important; }

.table-wrapper { overflow-x: auto !important; border-radius: 12px !important; border: 1px solid rgba(255,255,255,0.08) !important; background: rgba(0,0,0,0.15) !important; }
.data-table { width: 100% !important; border-collapse: collapse !important; font-size: 13px !important; min-width: 800px !important; }
.data-table thead th { background: #1e293b !important; color: #94a3b8 !important; font-weight: 700 !important; text-transform: uppercase !important; font-size: 11px !important; padding: 14px 16px !important; text-align: left !important; border-bottom: 2px solid rgba(56,189,248,0.25) !important; }
.data-table tbody td { padding: 13px 16px !important; color: #f1f5f9 !important; border-bottom: 1px solid rgba(255,255,255,0.04) !important; }
.data-table tbody tr:hover td { background: rgba(56,189,248,0.04) !important; }
.file-code { background: rgba(0,0,0,0.3) !important; padding: 4px 10px !important; border-radius: 6px !important; color: #38bdf8 !important; font-family: 'Courier New', monospace !important; font-size: 12px !important; }
.type-badge { display: inline-flex !important; padding: 4px 10px !important; border-radius: 6px !important; font-size: 10.5px !important; font-weight: 700 !important; background: rgba(167,139,250,0.15) !important; color: #a78bfa !important; border: 1px solid rgba(167,139,250,0.3) !important; }
.num-col { text-align: right !important; font-variant-numeric: tabular-nums !important; color: #cbd5e1 !important; }
.date-cell { color: #94a3b8 !important; font-size: 12.5px !important; white-space: nowrap !important; }
.actions-cell { text-align: center !important; white-space: nowrap !important; }
.icon-action { display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 32px !important; height: 32px !important; border-radius: 8px !important; background: rgba(255,255,255,0.05) !important; border: 1px solid rgba(255,255,255,0.1) !important; cursor: pointer !important; text-decoration: none !important; margin: 0 2px !important; }
.icon-action.download:hover { background: rgba(56,189,248,0.2) !important; }
.icon-action.delete:hover { background: rgba(239,68,68,0.2) !important; }
.empty-state { text-align: center !important; padding: 60px 24px !important; }
.empty-icon { font-size: 56px !important; margin-bottom: 16px !important; opacity: 0.6 !important; }
.empty-state h3 { font-size: 18px !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.toast { position: fixed !important; bottom: 32px !important; right: 32px !important; padding: 14px 22px !important; border-radius: 12px !important; font-size: 13.5px !important; font-weight: 600 !important; color: #fff !important; transform: translateY(20px) !important; opacity: 0 !important; transition: all 0.3s !important; z-index: 99999 !important; }
.toast.show { transform: translateY(0) !important; opacity: 1 !important; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.toast.error { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }
@media (max-width: 768px) { .admin-page .main-content { padding: 16px !important; } }
</style>