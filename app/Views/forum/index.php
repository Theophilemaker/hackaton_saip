<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- Hero -->
        <section class="glass-card forum-hero">
            <div>
                <h1>💬 <?= __('forum.title') ?></h1>
                <p><?= __('forum.districts') ?> · <?= __('forum.crops') ?> · <?= __('forum.announcements') ?></p>
            </div>
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>/forum/myPosts" class="btn-secondary">📝 <?= __('forum.my_posts') ?></a>
                <?php if (in_array($_SESSION['role_id'], [3,4,7])): ?>
                    <a href="<?= BASE_URL ?>/forum/stats" class="btn-secondary">📊 <?= __('forum.stats') ?></a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Search Bar -->
        <section class="glass-card">
            <form action="<?= BASE_URL ?>/forum/search" method="GET" class="forum-search">
                <input type="text" name="q" placeholder="🔍 <?= __('forum.search') ?>" autocomplete="off">
                <button type="submit" class="btn-primary"><?= __('common.search') ?></button>
            </form>
        </section>

        <!-- District Forums -->
        <section class="glass-card">
            <div class="card-header">
                <h3>📍 <?= __('forum.districts') ?></h3>
                <span class="badge green"><?= count($data['grouped']['district'] ?? []) ?> forums</span>
            </div>
            <div class="forum-grid">
                <?php if (empty($data['grouped']['district'])): ?>
                    <p class="empty-state">No district forums yet.</p>
                <?php else: ?>
                    <?php foreach ($data['grouped']['district'] as $ch): ?>
                        <a href="<?= BASE_URL ?>/forum/view/<?= $ch['id'] ?>" class="forum-card">
                            <div class="forum-icon"><?= $ch['icon'] ?? '📍' ?></div>
                            <div class="forum-info">
                                <strong><?= e($ch['name']) ?></strong>
                                <small><?= e($ch['description'] ?? 'Discuss farming in your district') ?></small>
                                <span class="forum-stat">
                                    📝 <?= (int)($ch['district_post_count'] ?? 0) ?> posts
                                    <?php if (!empty($ch['last_activity'])): ?>
                                        · Last: <?= date('M d', strtotime($ch['last_activity'])) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Crop Communities -->
        <section class="glass-card">
            <div class="card-header">
                <h3>🌾 <?= __('forum.crops') ?></h3>
                <span class="badge green"><?= count($data['grouped']['crop'] ?? []) ?> communities</span>
            </div>
            <div class="forum-grid">
                <?php if (empty($data['grouped']['crop'])): ?>
                    <p class="empty-state">No crop communities yet.</p>
                <?php else: ?>
                    <?php foreach ($data['grouped']['crop'] as $ch): ?>
                        <a href="<?= BASE_URL ?>/forum/view/<?= $ch['id'] ?>" class="forum-card">
                            <div class="forum-icon"><?= $ch['icon'] ?? '🌾' ?></div>
                            <div class="forum-info">
                                <strong><?= e($ch['name']) ?></strong>
                                <small><?= e($ch['description'] ?? 'Share knowledge about this crop') ?></small>
                                <span class="forum-stat">
                                    📝 <?= (int)($ch['crop_post_count'] ?? 0) ?> posts
                                    <?php if (!empty($ch['last_activity'])): ?>
                                        · Last: <?= date('M d', strtotime($ch['last_activity'])) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Recent Activity -->
        <section class="glass-card">
            <div class="card-header">
                <h3>🔥 <?= __('forum.recent') ?></h3>
            </div>
            <?php if (empty($data['recent'])): ?>
                <p class="empty-state">No recent activity.</p>
            <?php else: ?>
                <div class="recent-list">
                    <?php foreach ($data['recent'] as $p): ?>
                        <div class="recent-item">
                            <div class="recent-avatar">
                                <?= strtoupper(substr($p['first_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="recent-body">
                                <div class="recent-meta">
                                    <strong><?= e(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?></strong>
                                    <?php if (!empty($p['badge_type'])): ?>
                                        <span class="verified-badge">✓ <?= ucfirst($p['badge_type']) ?></span>
                                    <?php endif; ?>
                                    <small><?= date('M d, H:i', strtotime($p['created_at'])) ?></small>
                                </div>
                                <?php if (!empty($p['title'])): ?>
                                    <h4><?= e($p['title']) ?></h4>
                                <?php endif; ?>
                                <p><?= e(mb_substr($p['content'] ?? '', 0, 180)) ?><?= mb_strlen($p['content'] ?? '') > 180 ? '…' : '' ?></p>
                                <div class="recent-footer">
                                    <span>👍 <?= (int)$p['likes_count'] ?></span>
                                    <span>💬 <?= (int)$p['comments_count'] ?></span>
                                    <span class="badge grade"><?= ucfirst($p['post_type']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<style>
.forum-hero {
    padding: 25px !important;
    max-width: none;
    background: linear-gradient(135deg, rgba(34,197,94,0.15), rgba(132,204,22,0.05));
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.forum-hero h1 { margin: 0 0 4px; font-size: 24px; }
.forum-hero p { color: var(--text-sub); margin: 0; font-size: 13px; }

.forum-search {
    display: flex;
    gap: 10px;
}
.forum-search input {
    flex: 1;
    padding: 12px 16px;
    background: rgba(0,0,0,0.25);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: white;
    font-family: inherit;
    outline: none;
}
.forum-search input:focus { border-color: var(--accent); }
.forum-search button { width: auto; padding: 12px 24px; }

.forum-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 14px;
    margin-top: 16px;
}
.forum-card {
    display: flex;
    gap: 14px;
    padding: 16px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 14px;
    text-decoration: none;
    color: white;
    transition: 250ms;
    align-items: center;
}
.forum-card:hover {
    background: rgba(34,197,94,0.1);
    border-color: var(--accent);
    transform: translateY(-3px);
}
.forum-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}
.forum-info { flex-grow: 1; min-width: 0; }
.forum-info strong { display: block; font-size: 14px; margin-bottom: 4px; }
.forum-info small { color: var(--text-sub); font-size: 11px; display: block; margin-bottom: 4px; }
.forum-stat { font-size: 11px; color: var(--accent); }

.recent-list { display: flex; flex-direction: column; gap: 14px; margin-top: 12px; }
.recent-item {
    display: flex;
    gap: 12px;
    padding: 14px;
    background: rgba(255,255,255,0.03);
    border-radius: 12px;
    border-left: 3px solid var(--accent);
}
.recent-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
}
.recent-body { flex-grow: 1; min-width: 0; }
.recent-meta { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 6px; }
.recent-meta strong { font-size: 13px; }
.recent-meta small { color: var(--text-sub); font-size: 11px; margin-left: auto; }
.recent-body h4 { margin: 4px 0; font-size: 14px; }
.recent-body p { color: var(--text-sub); font-size: 12px; margin: 4px 0 8px; line-height: 1.5; }
.recent-footer { display: flex; gap: 14px; font-size: 12px; color: var(--text-sub); }
</style>