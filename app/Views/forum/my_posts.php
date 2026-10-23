<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <a href="<?= BASE_URL ?>/forum" class="btn-secondary" style="display:inline-block; margin-bottom:12px;">
                ← <?= __('forum.title') ?>
            </a>
            <div class="card-header">
                <h3>📝 <?= __('forum.my_posts') ?></h3>
                <span class="badge green"><?= count($data['posts']) ?> posts</span>
            </div>
        </section>

        <!-- Stats -->
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">📝</div>
                <div>
                    <p class="kpi-label">Total Posts</p>
                    <h3 class="kpi-value"><?= count($data['posts']) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">🏆</div>
                <div>
                    <p class="kpi-label">Accepted Answers</p>
                    <h3 class="kpi-value"><?= (int)($data['accepted_count'] ?? 0) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">👍</div>
                <div>
                    <p class="kpi-label">Total Likes</p>
                    <h3 class="kpi-value"><?= array_sum(array_column($data['posts'], 'likes_count')) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#7C3AED,#C084FC)">💬</div>
                <div>
                    <p class="kpi-label">Total Replies</p>
                    <h3 class="kpi-value"><?= array_sum(array_column($data['posts'], 'comments_count')) ?></h3>
                </div>
            </div>
        </section>

        <!-- Posts List -->
        <section class="glass-card">
            <?php if (empty($data['posts'])): ?>
                <p class="empty-state">
                    You haven't posted anything yet.
                    <a href="<?= BASE_URL ?>/forum" class="link-subtle">Ask your first question</a>
                </p>
            <?php else: ?>
                <div class="my-posts-list">
                    <?php foreach ($data['posts'] as $p): ?>
                        <div class="my-post-item">
                            <div class="my-post-info">
                                <div class="mp-title-row">
                                    <?php if (!empty($p['title'])): ?>
                                        <h4><?= e($p['title']) ?></h4>
                                    <?php else: ?>
                                        <h4><?= e(mb_substr($p['content'], 0, 60)) ?>…</h4>
                                    <?php endif; ?>
                                    <span class="badge grade"><?= ucfirst($p['post_type']) ?></span>
                                </div>
                                <p><?= e(mb_substr($p['content'], 0, 180)) ?>…</p>
                                <div class="my-post-meta">
                                    <small>📅 <?= date('M d, Y H:i', strtotime($p['created_at'])) ?></small>
                                    <?php if (!empty($p['district_name'])): ?>
                                        <small>📍 <?= e($p['district_name']) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($p['crop_name'])): ?>
                                        <small><?= $p['crop_icon'] ?? '🌾' ?> <?= e($p['crop_name']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="my-post-stats">
                                <div><strong><?= (int)$p['likes_count'] ?></strong><small>👍</small></div>
                                <div><strong><?= (int)$p['comments_count'] ?></strong><small>💬</small></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<style>
.my-posts-list { display: flex; flex-direction: column; gap: 12px; margin-top: 16px; }
.my-post-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: rgba(255,255,255,0.03);
    border-radius: 12px;
    transition: 200ms;
    border-left: 3px solid transparent;
}
.my-post-item:hover { background: rgba(34,197,94,0.08); border-left-color: var(--accent); }
.my-post-info { flex-grow: 1; min-width: 0; }
.mp-title-row { display: flex; gap: 10px; align-items: center; margin-bottom: 6px; }
.mp-title-row h4 { margin: 0; font-size: 15px; }
.my-post-info p { color: var(--text-sub); font-size: 12px; margin: 0 0 8px; line-height: 1.5; }
.my-post-meta { display: flex; gap: 14px; flex-wrap: wrap; font-size: 11px; color: var(--text-sub); }
.my-post-stats {
    display: flex;
    gap: 16px;
    flex-shrink: 0;
}
.my-post-stats div { text-align: center; }
.my-post-stats strong { display: block; font-size: 18px; color: var(--accent); }
.my-post-stats small { color: var(--text-sub); font-size: 14px; }
</style>