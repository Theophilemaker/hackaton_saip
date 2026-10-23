<?php
$recent = $recent ?? [];
$roleId = $roleId ?? 1;
?>
<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="main-content" style="padding:24px;">

            <!-- ═══════════ HEADER ═══════════ -->
            <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
                <div>
                    <h1 style="font-size:26px;font-weight:800;color:#f1f5f9;margin:0 0 6px;">
                        📊 Reports &amp; Analytics
                    </h1>
                    <p style="color:#94a3b8;font-size:14px;margin:0;">
                        Generate, download, and share platform data.
                    </p>
                </div>
                <button type="button" class="btn-primary" onclick="document.getElementById('genPanel').scrollIntoView({behavior:'smooth'})">
                    ✨ New Report
                </button>
            </div>

            <!-- ═══════════ GENERATOR PANEL ═══════════ -->
            <div class="glass-card" id="genPanel" style="padding:24px;margin-bottom:24px;border-radius:16px;background:rgba(30,41,59,0.6);border:1px solid rgba(255,255,255,0.08);">
                <h2 style="font-size:16px;font-weight:700;color:#f1f5f9;margin:0 0 20px;">
                    Generate a Report
                </h2>

                <form id="reportForm" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                    <div>
                        <label style="display:block;color:#94a3b8;font-size:13px;font-weight:500;margin-bottom:8px;">Report type</label>
                        <select name="type" style="width:100%;padding:12px 14px;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.1);border-radius:10px;color:#f1f5f9;font-family:inherit;font-size:14px;outline:none;">
                            <option value="summary">📈 Platform Summary</option>
                            <option value="harvests">🌾 Harvests</option>
                            <option value="orders">🛒 Marketplace Orders</option>
                            <option value="users">👥 Users</option>
                            <option value="crops">🌱 Crops</option>
                        </select>
                    </div>

                    <div>
                        <label style="display:block;color:#94a3b8;font-size:13px;font-weight:500;margin-bottom:8px;">Format</label>
                        <select name="format" style="width:100%;padding:12px 14px;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.1);border-radius:10px;color:#f1f5f9;font-family:inherit;font-size:14px;outline:none;">
                            <option value="csv">📄 CSV</option>
                            <option value="xlsx">📊 Excel (XLSX)</option>
                            <option value="pdf">📕 PDF</option>
                        </select>
                    </div>

                    <div>
                        <label style="display:block;color:#94a3b8;font-size:13px;font-weight:500;margin-bottom:8px;">From</label>
                        <input type="date" name="from" value="<?= date('Y-m-d', strtotime('-30 days')) ?>"
                               style="width:100%;padding:12px 14px;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.1);border-radius:10px;color:#f1f5f9;font-family:inherit;font-size:14px;outline:none;">
                    </div>

                    <div>
                        <label style="display:block;color:#94a3b8;font-size:13px;font-weight:500;margin-bottom:8px;">To</label>
                        <input type="date" name="to" value="<?= date('Y-m-d') ?>"
                               style="width:100%;padding:12px 14px;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.1);border-radius:10px;color:#f1f5f9;font-family:inherit;font-size:14px;outline:none;">
                    </div>

                    <div style="display:flex;align-items:flex-end;">
                        <button type="submit" class="btn-primary" style="width:100%;">
                            ⬇️ Generate &amp; Download
                        </button>
                    </div>
                </form>

                <p id="reportStatus" style="margin:16px 0 0;font-size:13px;color:#94a3b8;min-height:20px;"></p>
            </div>

            <!-- ═══════════ RECENT REPORTS ═══════════ -->
            <div class="glass-card" style="padding:24px;border-radius:16px;background:rgba(30,41,59,0.6);border:1px solid rgba(255,255,255,0.08);">
                <h2 style="font-size:16px;font-weight:700;color:#f1f5f9;margin:0 0 20px;">
                    Recent Reports
                </h2>

                <?php if (empty($recent)): ?>
                    <div style="text-align:center;padding:40px 20px;color:#94a3b8;">
                        <div style="font-size:52px;opacity:0.4;margin-bottom:12px;">📄</div>
                        <p style="margin:0 0 6px;font-size:14px;">No reports yet</p>
                        <small style="font-size:12px;">Generate your first report using the panel above.</small>
                    </div>
                <?php else: ?>
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:1px solid rgba(255,255,255,0.08);">
                                <th style="text-align:left;padding:12px 8px;color:#94a3b8;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Name</th>
                                <th style="text-align:left;padding:12px 8px;color:#94a3b8;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Type</th>
                                <th style="text-align:left;padding:12px 8px;color:#94a3b8;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Format</th>
                                <th style="text-align:left;padding:12px 8px;color:#94a3b8;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Created</th>
                                <th style="text-align:right;padding:12px 8px;color:#94a3b8;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $r): ?>
                                <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                                    <td style="padding:14px 8px;color:#f1f5f9;font-size:13.5px;"><?= e($r['name'] ?? 'Report') ?></td>
                                    <td style="padding:14px 8px;color:#cbd5e1;font-size:13px;"><?= e($r['type'] ?? '—') ?></td>
                                    <td style="padding:14px 8px;color:#34d399;font-size:12px;font-weight:700;text-transform:uppercase;"><?= e($r['format'] ?? '—') ?></td>
                                    <td style="padding:14px 8px;color:#94a3b8;font-size:12.5px;"><?= e($r['created_at'] ?? '—') ?></td>
                                    <td style="padding:14px 8px;text-align:right;">
                                        <a href="<?= BASE_URL ?>/reports/download/<?= (int)($r['id'] ?? 0) ?>"
                                           style="color:#10b981;text-decoration:none;font-size:13px;font-weight:600;">
                                            ⬇️ Download
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<script>
document.getElementById('reportForm')?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const status = document.getElementById('reportStatus');
    const btn = this.querySelector('button[type="submit"]');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ Generating…';
    status.textContent = '';
    status.style.color = '#94a3b8';

    try {
        const fd = new FormData(this);
        const res = await fetch('<?= BASE_URL ?>/reports/generate', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        });

        if (!res.ok) throw new Error('Server returned ' + res.status);

        const ctype = res.headers.get('Content-Type') || '';
        if (ctype.includes('application/json')) {
            const d = await res.json();
            if (!d.success) throw new Error(d.error || 'Generation failed');
        } else {
            // Treat as file download
            const blob = await res.blob();
            const cd = res.headers.get('Content-Disposition') || '';
            const match = /filename="?([^"]+)"?/.exec(cd);
            const filename = match ? match[1] : 'report.csv';
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        }

        status.textContent = '✅ Report generated successfully.';
        status.style.color = '#34d399';
    } catch (err) {
        status.textContent = '⚠️ ' + err.message;
        status.style.color = '#f87171';
    } finally {
        btn.disabled = false;
        btn.innerHTML = original;
    }
});
</script>

<style>
    /* Fallback for [hidden] and layout basics */
    [hidden] { display: none !important; }
    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(16, 185, 129, 0.55); }
    .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    select option { background: #0f1d2e; color: #f1f5f9; }
</style>