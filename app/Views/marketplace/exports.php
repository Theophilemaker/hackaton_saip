<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- Hero -->
        <section class="glass-card exports-hero">
            <div>
                <h1>🚢 <?= __('mkt.export_requests') ?></h1>
                <p>Source premium Rwandan agricultural products for export markets</p>
            </div>
            <button onclick="document.getElementById('newExportModal').style.display='flex'" class="btn-primary">
                + <?= __('mkt.new_export_request') ?? 'Post Export Request' ?>
            </button>
        </section>

        <!-- Stats -->
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">📋</div>
                <div><p class="kpi-label">Open Requests</p><h3 class="kpi-value"><?= count($data['requests'] ?? []) ?></h3></div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">✅</div>
                <div><p class="kpi-label">Fulfilled</p><h3 class="kpi-value">—</h3></div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">🌍</div>
                <div><p class="kpi-label">Countries</p><h3 class="kpi-value">—</h3></div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#7C3AED,#C084FC)">⚖️</div>
                <div><p class="kpi-label">Total Volume</p><h3 class="kpi-value">—</h3></div>
            </div>
        </section>

        <!-- Export Requests List -->
        <section class="glass-card">
            <div class="card-header">
                <h3>📋 Active Export Requests</h3>
                <span class="badge green"><?= count($data['requests'] ?? []) ?> open</span>
            </div>

            <?php if (empty($data['requests'])): ?>
                <div class="empty-state-card">
                    <span style="font-size:48px;">🚢</span>
                    <h3>No open export requests</h3>
                    <p>Be the first to post what you're sourcing.</p>
                    <button onclick="document.getElementById('newExportModal').style.display='flex'" class="btn-primary" style="width:auto; padding:12px 28px;">+ Post Request</button>
                </div>
            <?php else: ?>
                <div class="exports-grid">
                    <?php foreach ($data['requests'] as $r): ?>
                        <div class="export-card">
                            <div class="export-header">
                                <span class="ec-icon"><?= $r['category_icon'] ?? '📦' ?></span>
                                <div>
                                    <strong><?= e($r['category_name'] ?? 'Product') ?></strong>
                                    <small>Posted by <?= e(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?></small>
                                </div>
                                <span class="badge green">Open</span>
                            </div>

                            <div class="export-body">
                                <div class="export-stat">
                                    <small>Quantity Needed</small>
                                    <strong><?= number_format($r['quantity_needed']) ?> <?= e($r['unit']) ?></strong>
                                </div>
                                <?php if (!empty($r['target_price'])): ?>
                                    <div class="export-stat"><small>Target Price</small><strong><?= number_format($r['target_price']) ?> RWF</strong></div>
                                <?php endif; ?>
                                <?php if (!empty($r['destination_country'])): ?>
                                    <div class="export-stat"><small>Destination</small><strong>🌍 <?= e($r['destination_country']) ?></strong></div>
                                <?php endif; ?>
                                <?php if (!empty($r['required_by'])): ?>
                                    <div class="export-stat"><small>Required By</small><strong><?= date('M d, Y', strtotime($r['required_by'])) ?></strong></div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($r['certifications'])): ?>
                                <div class="export-certs">
                                    <small>Certifications:</small>
                                    <span><?= e($r['certifications']) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($r['description'])): ?>
                                <p class="export-desc"><?= nl2br(e($r['description'])) ?></p>
                            <?php endif; ?>

                            <div class="export-actions">
                                <a href="<?= BASE_URL ?>/chat/startDirect/<?= (int)$r['buyer_id'] ?>" class="btn-sm">💬 Contact Buyer</a>
                                <a href="<?= BASE_URL ?>/marketplace/sell" class="btn-sm ghost">📦 List Product</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<!-- ============ NEW EXPORT MODAL ============ -->
<div id="newExportModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); backdrop-filter:blur(5px); z-index:1000; align-items:center; justify-content:center; padding:20px;">
    <div class="glass-card modal" style="max-width:560px; width:100%; max-height:90vh; overflow-y:auto; padding:25px; background:#0f172a; border:1px solid var(--border); border-radius:16px;">
        <h3 style="margin-top:0;">🚢 Post Export Request</h3>
        <form method="POST" action="<?= BASE_URL ?>/marketplace/newExport" class="alert-form">
            <?php if (class_exists('\App\Core\Security')) echo \App\Core\Security::csrfField(); ?>

            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" required style="width:100%; padding:10px; background:#1e293b; color:white; border:1px solid var(--border); border-radius:8px;">
                    <option value="">Select category</option>
                    <?php foreach ($data['categories'] ?? [] as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= $c['icon'] ?? '' ?> <?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label>Quantity Needed *</label>
                    <input type="number" name="quantity_needed" step="0.01" min="0.01" required placeholder="10" style="width:100%; padding:10px; background:#1e293b; color:white; border:1px solid var(--border); border-radius:8px;">
                </div>
                <div class="form-group">
                    <label>Unit *</label>
                    <select name="unit" required style="width:100%; padding:10px; background:#1e293b; color:white; border:1px solid var(--border); border-radius:8px;">
                        <option value="ton">Tons</option>
                        <option value="kg">Kilograms</option>
                        <option value="bag">Bags</option>
                    </select>
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label>Target Price (RWF / unit)</label>
                    <input type="number" name="target_price" step="0.01" min="0" placeholder="500000" style="width:100%; padding:10px; background:#1e293b; color:white; border:1px solid var(--border); border-radius:8px;">
                </div>
                <div class="form-group">
                    <label>Destination Country</label>
                    <input type="text" name="destination_country" maxlength="100" placeholder="France" style="width:100%; padding:10px; background:#1e293b; color:white; border:1px solid var(--border); border-radius:8px;">
                </div>
            </div>

            <div class="form-group">
                <label>Required By</label>
                <input type="date" name="required_by" style="width:100%; padding:10px; background:#1e293b; color:white; border:1px solid var(--border); border-radius:8px;">
            </div>

            <div class="form-group">
                <label>Certifications Required</label>
                <input type="text" name="certifications" maxlength="300" placeholder="Organic, Fair Trade, ISO 22000" style="width:100%; padding:10px; background:#1e293b; color:white; border:1px solid var(--border); border-radius:8px;">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" maxlength="1000" placeholder="Tell suppliers what you need, quality requirements, delivery terms..." style="width:100%; padding:10px; background:#1e293b; color:white; border:1px solid var(--border); border-radius:8px;"></textarea>
            </div>

            <div class="action-buttons" style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="btn-primary" style="flex:1; padding:12px; border-radius:8px; background:var(--primary); color:white; border:none; cursor:pointer;">📤 Post Request</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('newExportModal').style.display='none'" style="flex:1; padding:12px; border-radius:8px; background:transparent; border:1px solid var(--border); color:white; cursor:pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
/* [Full CSS block from your exports text file goes here] */
.exports-hero { padding: 30px !important; max-width: none; background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(239,68,68,0.05)); display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; }
.exports-hero h1 { margin: 0 0 4px; font-size: 24px; }
.exports-hero p { color: var(--text-sub); margin: 0; font-size: 13px; }
.exports-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px; margin-top: 16px; }
.export-card { padding: 20px; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 14px; transition: 250ms; }
.export-card:hover { border-color: var(--accent); transform: translateY(-4px); }
.export-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
.ec-icon { width: 46px; height: 46px; border-radius: 12px; background: linear-gradient(135deg, #F59E0B, #EF4444); display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
.export-header > div { flex-grow: 1; }
.export-header strong { display: block; font-size: 14px; }
.export-header small { color: var(--text-sub); font-size: 11px; }
.export-body { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
.export-stat { padding: 10px; background: rgba(255,255,255,0.03); border-radius: 10px; }
.export-stat small { display: block; font-size: 10px; text-transform: uppercase; color: var(--text-sub); letter-spacing: 0.5px; margin-bottom: 4px; }
.export-stat strong { font-size: 14px; color: var(--accent); }
.export-certs { padding: 10px 12px; background: rgba(132,204,22,0.06); border-left: 3px solid var(--accent); border-radius: 8px; margin-bottom: 12px; font-size: 12px; }
.export-certs small { display: block; color: var(--text-sub); font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.export-desc { color: var(--text-sub); font-size: 12px; line-height: 1.5; margin: 0 0 14px; }
.export-actions { display: flex; gap: 8px; padding-top: 12px; border-top: 1px solid var(--border); }
.export-actions .btn-sm { flex: 1; text-align: center; text-decoration: none; padding: 10px; }
.btn-sm.ghost { background: transparent; border: 1px solid var(--border); color: var(--text-sub); }
.btn-sm.ghost:hover { border-color: var(--accent); color: var(--accent); }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.empty-state-card { text-align: center; padding: 60px 20px; }
.empty-state-card h3 { margin: 12px 0 8px; }
.empty-state-card p { color: var(--text-sub); margin: 0 0 20px; font-size: 13px; }
@media (max-width: 600px) { .form-row-2 { grid-template-columns: 1fr; gap: 0; } .export-body { grid-template-columns: 1fr; } }
</style>