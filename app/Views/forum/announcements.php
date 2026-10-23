<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card ann-hero">
            <div>
                <h1>📢 <?= __('forum.announcements') ?></h1>
                <p>Official notices from government, officers, and cooperatives</p>
            </div>
            <?php if (!empty($data['can_post'])): ?>
                <a href="<?= BASE_URL ?>/government/publishAlert" class="btn-primary">+ Post Announcement</a>
            <?php endif; ?>
        </section>

        <!-- Info banner -->
        <section class="glass-card info-banner">
            <span class="info-icon">ℹ️</span>
            <div>
                <strong>Official channel</strong>
                <p>Only verified agricultural officers, government officials, and admins can post here.</p>
            </div>
        </section>

        <!-- Announcements List -->
        <section class="ann-list">
            <?php if (empty($data['announcements'])): ?>
                <div class="glass-card">
                    <div class="empty-state-card">
                        <span style="font-size:48px;">📢</span>
                        <h3>No announcements yet</h3>
                        <p>Check back later for updates from MINAGRI, NISR, and district offices.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($data['announcements'] as $a): ?>
                    <div class="glass-card ann-card">
                        <div class="ann-header">
                            <div class="ann-avatar">
                                <?= strtoupper(substr($a['first_name'] ?? 'A', 0, 1)) ?>
                            </div>
                            <div class="ann-meta">
                                <strong><?= e(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')) ?></strong>
                                <?php if (!empty($a['badge_type'])): ?>
                                    <span class="verified-badge">✓ <?= ucfirst($a['badge_type']) ?></span>
                                <?php endif; ?>
                                <small><?= date('M d, Y H:i', strtotime($a['created_at'])) ?></small>
                            </div>
                            <span class="badge green">📢 Official</span>
                        </div>

                        <?php if (!empty($a['title'])): ?>
                            <h3 class="ann-title"><?= e($a['title']) ?></h3>
                        <?php endif; ?>

                        <p class="ann-content"><?= nl2br(e($a['content'])) ?></p>

                        <?php if (!empty($a['media_url'])): ?>
                            <img src="<?= e($a['media_url']) ?>" class="ann-media" onclick="window.open(this.src)">
                        <?php endif; ?>

                        <div class="ann-footer">
                            <span>👍 <?= (int)$a['likes_count'] ?></span>
                            <span>💬 <?= (int)$a['comments_count'] ?></span>
                            <button onclick="likePost(<?= $a['id'] ?>, this)" class="action-btn">
                                👍 Like
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</div>

<script>
async function likePost(postId, btn) {
    const res = await fetch(`<?= BASE_URL ?>/forum/like/${postId}`, { method: 'POST' });
    const data = await res.json();
    if (data.count !== undefined) {
        btn.textContent = '👍 Liked (' + data.count + ')';
    }
}
</script>

<style>
.ann-hero {
    padding: 30px !important;
    max-width: none;
    background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(239,68,68,0.05));
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.ann-hero h1 { margin: 0 0 4px; font-size: 24px; }
.ann-hero p { color: var(--text-sub); margin: 0; font-size: 13px; }

.info-banner {
    padding: 16px 20px !important;
    max-width: none;
    display: flex;
    gap: 14px;
    align-items: center;
    background: rgba(14,165,233,0.08);
    border-left: 3px solid #0EA5E9;
}
.info-icon { font-size: 24px; flex-shrink: 0; }
.info-banner strong { display: block; font-size: 13px; margin-bottom: 2px; }
.info-banner p { margin: 0; font-size: 12px; color: var(--text-sub); }

.ann-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-top: 16px;
}
.ann-card { padding: 22px !important; max-width: none; }
.ann-header {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--border);
}
.ann-avatar {
    width: 46px; height: 46px;
    border-radius: 50%;
    background: linear-gradient(135deg, #F59E0B, #EF4444);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700;
    font-size: 18px;
    flex-shrink: 0;
}
.ann-meta { flex-grow: 1; }
.ann-meta strong { display: block; font-size: 14px; }
.ann-meta small { color: var(--text-sub); font-size: 11px; }

.ann-title { margin: 0 0 10px; font-size: 18px; color: var(--accent); }
.ann-content { color: var(--text-sub); font-size: 14px; line-height: 1.6; margin: 0 0 14px; }
.ann-media {
    max-width: 100%;
    border-radius: 12px;
    margin: 12px 0;
    cursor: pointer;
    display: block;
}
.ann-footer {
    display: flex;
    gap: 14px;
    padding-top: 12px;
    border-top: 1px solid var(--border);
    align-items: center;
    font-size: 12px;
    color: var(--text-sub);
}
.action-btn {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text-sub);
    padding: 6px 14px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 12px;
    transition: 200ms;
    font-family: inherit;
    margin-left: auto;
}
.action-btn:hover {
    border-color: var(--accent);
    color: var(--accent);
    background: rgba(34,197,94,0.08);
}

.verified-badge {
    background: #0EA5E9;
    color: white;
    padding: 1px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    display: inline-block;
    margin-left: 6px;
}
.empty-state-card { text-align: center; padding: 60px 20px; }
.empty-state-card h3 { margin: 12px 0 8px; }
.empty-state-card p { color: var(--text-sub); margin: 0; font-size: 13px; }
</style>