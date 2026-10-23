<?php
$stats   = $stats   ?? [];
$flashes = $flashes ?? [];
?>
<div class="dashboard-layout admin-crops-page">
    <?php require_once __DIR__ . '/../../../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../../../layouts/topbar.php'; ?>
        <main class="main-content">

            <?php foreach ($flashes as $type => $msg): ?>
                <div class="flash flash-<?= e($type) ?>"><?= e($msg) ?></div>
            <?php endforeach; ?>

            <section class="glass-card ad-hero">
                <div>
                    <a href="<?= BASE_URL ?>/admin" class="back-link">← Admin Dashboard</a>
                    <h1>🗂️ Uploaded Data Manager</h1>
                    <p>Inspect and clear uploaded files. Frees disk space and removes stale references.</p>
                </div>
            </section>

            <section class="kpi-strip">
                <?php foreach ($stats as $key => $s): ?>
                    <div class="kpi-card">
                        <span class="kpi-icon"><?= ['detections'=>'🔬','pests'=>'🐛','chat'=>'💬','crops'=>'🌾','avatars'=>'👤'][$key] ?? '📁' ?></span>
                        <div>
                            <span class="kpi-label"><?= ucfirst($key) ?></span>
                            <strong class="kpi-value"><?= $s['files'] ?> files</strong>
                            <small style="color:#64748b;font-size:11px"><?= e($s['size']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>

            <section class="glass-card">
                <h3 style="font-size:16px;color:#f1f5f9;margin:0 0 16px;font-weight:800">🧹 Clear Uploaded Data</h3>
                <form method="POST" action="<?= BASE_URL ?>/admin/clearUploads" class="clear-form">
                    <div class="fg">
                        <label>Scope</label>
                        <select name="scope" required>
                            <option value="">Choose what to clear…</option>
                            <option value="detections">🔬 Crop Detections (images from AI disease scans)</option>
                            <option value="pests">🐛 Pest Detections</option>
                            <option value="chat">💬 Chat Attachments (images/voice)</option>
                            <option value="crops">🌾 Crop Images</option>
                            <option value="avatars">👤 User Avatars</option>
                            <option value="all">⚠️ Everything (all uploads)</option>
                        </select>
                    </div>

                    <div class="fg">
                        <label>Older than (days) — leave 0 to delete all</label>
                        <input type="number" name="older_than" min="0" max="3650" value="0">
                        <small class="fg-hint">e.g., 30 = delete files created more than 30 days ago</small>
                    </div>

                    <div class="danger-note">
                        ⚠️ This action is irreversible. Make sure you have a database backup before clearing.
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-hero-primary" style="background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 4px 15px rgba(239,68,68,.35)"
                                onclick="return confirm('Are you sure? This cannot be undone.')">
                            🗑️ Clear Uploads
                        </button>
                    </div>
                </form>
            </section>

        </main>
    </div>
</div>

<style>
html,body{margin:0!important;padding:0!important;overflow-x:hidden!important}
.admin-crops-page.dashboard-layout{display:block!important;width:100%!important;background:#0b1120!important;min-height:100vh!important}
.admin-crops-page .main-wrap{margin-left:260px!important;width:calc(100% - 260px)!important;min-height:100vh!important;box-sizing:border-box!important}
.admin-crops-page .main-content{padding:20px 28px 60px!important;display:flex!important;flex-direction:column!important;gap:18px!important;box-sizing:border-box!important}
.admin-crops-page .glass-card{background:rgba(30,41,59,.7)!important;backdrop-filter:blur(16px)!important;border:1px solid rgba(255,255,255,.08)!important;border-radius:20px!important;padding:22px!important;box-sizing:border-box!important;width:100%!important}

.flash{padding:14px 18px;border-radius:12px;font-size:13.5px;font-weight:700;margin:0 0 12px}
.flash-success{background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.35)}
.flash-error{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.35)}

.ad-hero{padding:24px 28px!important;background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(56,189,248,.08))!important}
.back-link{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:#94a3b8;text-decoration:none;font-weight:600;margin-bottom:12px}
.back-link:hover{color:#34d399}
.ad-hero h1{font-size:24px;font-weight:800;color:#f1f5f9;margin:0 0 6px}
.ad-hero p{color:#94a3b8;font-size:13.5px;margin:0;max-width:600px}

.kpi-strip{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(190px,1fr))!important;gap:14px!important}
.kpi-card{display:flex;align-items:center;gap:14px;padding:18px 20px;background:rgba(30,41,59,.7);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.08);border-radius:16px;transition:.25s}
.kpi-card:hover{transform:translateY(-3px);border-color:rgba(16,185,129,.3)}
.kpi-icon{font-size:26px;flex-shrink:0}
.kpi-label{display:block;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px}
.kpi-value{display:block;font-size:17px;font-weight:800;color:#f1f5f9;line-height:1.1}

.clear-form{display:flex;flex-direction:column;gap:14px;max-width:640px}
.fg{display:flex;flex-direction:column;gap:6px}
.fg label{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px}
.fg select,.fg input{padding:12px 15px;background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.12);border-radius:11px;color:#f1f5f9;font-size:14px;font-family:inherit;outline:none;box-sizing:border-box;width:100%}
.fg select:focus,.fg input:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.15)}
.fg select option{background:#1e293b}
.fg-hint{font-size:11px;color:#64748b;font-style:italic}

.danger-note{padding:12px 16px;background:rgba(239,68,68,.08);border-left:3px solid #ef4444;border-radius:10px;color:#f87171;font-size:12.5px;font-weight:600}

.form-actions{display:flex;justify-content:flex-end}
.btn-hero-primary{display:inline-flex;align-items:center;gap:8px;padding:13px 26px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:12px;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;box-shadow:0 4px 15px rgba(16,185,129,.35);transition:.2s;font-family:inherit}
.btn-hero-primary:hover{transform:translateY(-2px)}

@media(max-width:900px){
    .admin-crops-page .main-wrap{margin-left:0!important;width:100%!important;padding-top:64px!important}
    .admin-crops-page .main-content{padding:14px!important}
    .kpi-strip{grid-template-columns:repeat(2,1fr)!important}
}
</style>