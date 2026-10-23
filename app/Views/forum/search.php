<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <a href="<?= BASE_URL ?>/forum" class="btn-secondary" style="display:inline-block; margin-bottom:12px;">
                ← <?= __('forum.title') ?>
            </a>
            <div class="card-header">
                <h3>🔍 Search Results</h3>
                <span class="badge green"><?= count($data['results']) ?> results</span>
            </div>

            <form action="<?= BASE_URL ?>/forum/search" method="GET" class="forum-search" style="margin-top:16px;">
                <input type="text" name="q" value="<?= e($data['query']) ?>" placeholder="Search forums..." autofocus>
                <button type="submit" class="btn-primary"><?= __('common.search') ?></button>
            </form>
        </section>

        <!-- Results -->
        <section class="glass-card">
            <?php if (empty($data['results'])): ?>
                <div class="empty-state-card">
                    <span style="font-size:48px;">🔍</span>
                    <h3>No results found</h3>
                    <p>No forum posts matched "<strong><?= e($data['query']) ?></strong>".</p>
                    <p>Try different keywords or browse the forums.</p>
                    <a href="<?= BASE_URL ?>/forum" class="btn-primary" style="width:auto; padding:12px 28px;">
                        Browse Forums
                    </a>
                </div>
            <?php else: ?>
                <div class="search-results">
                    <?php foreach ($data['results'] as $r): ?>
                        <div class="search-result-item">
                            <div class="sr-avatar">
                                <?= strtoupper(substr($r['first_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="sr-body">
                                <div class="sr-meta">
                                    <strong><?= e(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?></strong>
                                    <?php if (!empty($r['badge_type'])): ?>
                                        <span class="verified-badge">✓</span>
                                    <?php endif; ?>
                                    <small><?= date('M d, Y', strtotime($r['created_at'])) ?></small>
                                    <?php if (!empty($r['district_name'])): ?>
                                        <span class="badge grade">📍 <?= e($r['district_name']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($r['crop_name'])): ?>
                                        <span class="badge grade"><?= $r['crop_icon'] ?> <?= e($r['crop_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($r['title'])): ?>
                                    <h4><?= e($r['title']) ?></h4>
                                <?php endif; ?>
                                <p><?= e(mb_substr($r['content'], 0, 250)) ?>…</p>
                                <div class="sr-footer">
                                    <span>👍 <?= (int)$r['likes_count'] ?></span>
                                    <span>💬 <?= (int)$r['comments_count'] ?></span>
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
.forum-search { display: flex; gap: 10px; }
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

.search-results { display: flex; flex-direction: column; gap: 12px; margin-top: 12px; }
.search-result-item {
    display: flex;
    gap: 14px;
    padding: 16px;
    background: rgba(255,255,255,0.03);
    border-radius: 12px;
    border-left: 3px solid transparent;
    transition: 200ms;
}
.search-result-item:hover { background: rgba(34,197,94,0.08); border-left-color: var(--accent); }
.sr-avatar {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex; align-items: center; justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
}
.sr-body { flex-grow: 1; min-width: 0; }
.sr-meta { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 6px; }
.sr-meta strong { font-size: 13px; }
.sr-meta small { color: var(--text-sub); font-size: 11px; }
.sr-body h4 { margin: 4px 0; font-size: 15px; }
.sr-body p { color: var(--text-sub); font-size: 13px; margin: 6px 0; line-height: 1.5; }
.sr-footer { display: flex; gap: 14px; font-size: 12px; color: var(--text-sub); }

.verified-badge {
    background: #0EA5E9;
    color: white;
    padding: 1px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
}
.empty-state-card {
    text-align: center;
    padding: 60px 20px;
}
.empty-state-card h3 { margin: 12px 0 8px; font-size: 18px; }
.empty-state-card p { color: var(--text-sub); margin: 0 0 20px; font-size: 13px; }
</style>