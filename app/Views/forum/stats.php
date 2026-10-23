<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <a href="<?= BASE_URL ?>/forum" class="btn-secondary" style="display:inline-block; margin-bottom:12px;">
                ← <?= __('forum.title') ?>
            </a>
            <div class="card-header">
                <h3>📊 <?= __('forum.stats') ?></h3>
                <span class="badge green">Officer Analytics</span>
            </div>
        </section>

        <!-- KPIs -->
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">📝</div>
                <div>
                    <p class="kpi-label">Total Posts</p>
                    <h3 class="kpi-value"><?= number_format($data['total_posts']) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">💬</div>
                <div>
                    <p class="kpi-label">Total Replies</p>
                    <h3 class="kpi-value"><?= number_format($data['total_comments']) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">✓</div>
                <div>
                    <p class="kpi-label">Verified Answers</p>
                    <h3 class="kpi-value"><?= number_format($data['verified']) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#7C3AED,#C084FC)">❓</div>
                <div>
                    <p class="kpi-label">Questions</p>
                    <h3 class="kpi-value"><?= number_format($data['questions']) ?></h3>
                </div>
            </div>
        </section>

        <!-- Top Districts -->
        <section class="glass-card">
            <div class="card-header"><h3>🏆 <?= __('forum.top_districts') ?></h3></div>
            <?php if (empty($data['top_districts'])): ?>
                <p class="empty-state">No district activity yet.</p>
            <?php else: ?>
                <div class="top-districts-list">
                    <?php 
                    $max = max(array_column($data['top_districts'], 'post_count')) ?: 1;
                    foreach ($data['top_districts'] as $idx => $d): 
                        $pct = round(($d['post_count'] / $max) * 100);
                    ?>
                        <div class="district-row">
                            <span class="rank">#<?= $idx + 1 ?></span>
                            <strong><?= e($d['name']) ?></strong>
                            <div class="district-bar">
                                <div class="district-fill" style="width:<?= $pct ?>%"></div>
                            </div>
                            <span class="count"><?= (int)$d['post_count'] ?> posts</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Verification Rate -->
        <section class="glass-card">
            <div class="card-header"><h3>📈 Answer Verification Rate</h3></div>
            <?php 
            $rate = $data['total_comments'] > 0 
                ? round(($data['verified'] / $data['total_comments']) * 100, 1) 
                : 0;
            ?>
            <div class="verification-meter">
                <div class="vm-ring">
                    <svg viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="8"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#22C55E" stroke-width="8"
                                stroke-dasharray="<?= ($rate * 2.51) ?> 251"
                                stroke-dashoffset="0"
                                transform="rotate(-90 50 50)"
                                stroke-linecap="round"/>
                    </svg>
                    <div class="vm-label">
                        <strong><?= $rate ?>%</strong>
                        <small>verified</small>
                    </div>
                </div>
                <div class="vm-info">
                    <p><strong><?= number_format($data['verified']) ?></strong> of <?= number_format($data['total_comments']) ?> replies have been verified by agricultural officers.</p>
                    <p style="color:var(--text-sub); font-size:12px;">Verified answers are marked with the ✓ badge and appear first in threads.</p>
                </div>
            </div>
        </section>
    </main>
</div>

<style>
.top-districts-list { display: flex; flex-direction: column; gap: 12px; margin-top: 12px; }
.district-row {
    display: grid;
    grid-template-columns: 40px 1fr 3fr auto;
    gap: 14px;
    align-items: center;
    padding: 12px;
    background: rgba(255,255,255,0.03);
    border-radius: 10px;
}
.rank {
    font-size: 14px;
    font-weight: 800;
    color: var(--accent);
}
.district-row strong { font-size: 13px; }
.district-bar {
    height: 8px;
    background: rgba(255,255,255,0.08);
    border-radius: 4px;
    overflow: hidden;
}
.district-fill {
    height: 100%;
    background: linear-gradient(90deg, #16A34A, #84CC16);
    border-radius: 4px;
    transition: width 500ms;
}
.district-row .count {
    font-size: 12px;
    color: var(--text-sub);
    white-space: nowrap;
}

.verification-meter {
    display: flex;
    gap: 40px;
    align-items: center;
    margin-top: 16px;
    flex-wrap: wrap;
}
.vm-ring {
    position: relative;
    width: 140px;
    height: 140px;
    flex-shrink: 0;
}
.vm-ring svg { width: 100%; height: 100%; }
.vm-label {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.vm-label strong { font-size: 28px; color: var(--accent); }
.vm-label small { font-size: 11px; color: var(--text-sub); text-transform: uppercase; letter-spacing: 1px; }
.vm-info p { margin: 0 0 6px; font-size: 13px; line-height: 1.6; }

@media (max-width: 700px) {
    .district-row { grid-template-columns: 30px 1fr auto; }
    .district-bar { grid-column: 1 / -1; }
}
</style>