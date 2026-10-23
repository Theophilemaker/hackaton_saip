<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- Channel Header -->
        <section class="glass-card channel-hero">
            <div class="channel-hero-left">
                <div class="channel-icon"><?= $data['channel']['icon'] ?? '💬' ?></div>
                <div>
                    <a href="<?= BASE_URL ?>/forum" class="back-link">← <?= __('forum.title') ?></a>
                    <h1><?= e($data['channel']['name']) ?></h1>
                    <p><?= e($data['channel']['description'] ?? '') ?></p>
                </div>
            </div>
            <?php if (!empty($data['is_officer'])): ?>
                <span class="badge green">🏛️ Officer Access</span>
            <?php endif; ?>
        </section>

        <!-- Sort Tabs -->
        <section class="glass-card forum-tabs-card">
            <div class="forum-tabs">
                <a href="?sort=recent" class="<?= ($data['sort'] ?? 'recent') === 'recent' ? 'active' : '' ?>">
                    🔥 <?= __('forum.sort_recent') ?>
                </a>
                <a href="?sort=popular" class="<?= $data['sort'] === 'popular' ? 'active' : '' ?>">
                    ⭐ <?= __('forum.sort_popular') ?>
                </a>
                <a href="?sort=unanswered" class="<?= $data['sort'] === 'unanswered' ? 'active' : '' ?>">
                    ❓ <?= __('forum.sort_unanswered') ?>
                </a>
            </div>
        </section>

        <!-- Ask Question Form -->
        <section class="glass-card">
            <div class="card-header">
                <h3>✍️ <?= __('forum.new_post') ?></h3>
            </div>
            <form action="<?= BASE_URL ?>/forum/post/<?= $data['channel']['id'] ?>" method="POST" enctype="multipart/form-data" class="forum-form">
                <?= \App\Core\Security::csrfField() ?>
                <input type="hidden" name="post_type" value="question">

                <div class="form-group">
                    <input type="text" name="title" placeholder="<?= __('forum.post_title') ?>" maxlength="200">
                </div>

                <div class="form-group">
                    <textarea name="content" rows="4" required minlength="10"
                              placeholder="<?= __('forum.post_content') ?>"></textarea>
                </div>

                <div class="forum-form-actions">
                    <label class="attach-btn" title="Attach image">
                        📎 <input type="file" name="media" accept="image/*" hidden>
                    </label>
                    <button type="submit" class="btn-primary">📤 <?= __('forum.submit') ?></button>
                </div>
            </form>
        </section>

        <!-- Posts -->
        <section class="posts-list">
            <?php if (empty($data['posts'])): ?>
                <div class="glass-card">
                    <p class="empty-state"><?= __('forum.no_posts') ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($data['posts'] as $p): ?>
                    <div class="glass-card post-card" id="post-<?= $p['id'] ?>">
                        <!-- Post Header -->
                        <div class="post-header">
                            <div class="post-avatar">
                                <?= strtoupper(substr($p['first_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="post-meta">
                                <strong><?= e(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?></strong>
                                <?php if (!empty($p['badge_type'])): ?>
                                    <span class="verified-badge">✓ <?= ucfirst($p['badge_type']) ?></span>
                                <?php endif; ?>
                                <small><?= date('M d, Y H:i', strtotime($p['created_at'])) ?></small>
                            </div>
                            <span class="badge grade" style="margin-left:auto;">
                                <?= ucfirst($p['post_type']) ?>
                            </span>
                        </div>

                        <!-- Post Body -->
                        <?php if (!empty($p['title'])): ?>
                            <h3 class="post-title"><?= e($p['title']) ?></h3>
                        <?php endif; ?>
                        <p class="post-content"><?= nl2br(e($p['content'])) ?></p>

                        <?php if (!empty($p['media_url'])): ?>
                            <img src="<?= e($p['media_url']) ?>" class="post-media" onclick="window.open(this.src)">
                        <?php endif; ?>

                        <!-- Post Actions -->
                        <div class="post-actions">
                            <button onclick="likePost(<?= $p['id'] ?>, this)" class="action-btn">
                                👍 <span><?= (int)$p['likes_count'] ?></span>
                            </button>
                            <button onclick="document.getElementById('reply-<?= $p['id'] ?>').focus()" class="action-btn">
                                💬 <?= (int)$p['comment_count'] ?>
                            </button>
                            <button onclick="reportPost(<?= $p['id'] ?>)" class="action-btn">
                                🚩 <?= __('forum.report') ?>
                            </button>
                            <?php if ($p['user_id'] == $_SESSION['user_id'] || $_SESSION['role_id'] == 7): ?>
                                <button onclick="deletePost(<?= $p['id'] ?>)" class="action-btn danger">
                                    🗑️ <?= __('common.delete') ?>
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Comments -->
                        <?php if (!empty($p['comments'])): ?>
                            <div class="comments-section">
                                <?php foreach ($p['comments'] as $c): ?>
                                    <div class="comment <?= $c['is_verified'] ? 'verified' : '' ?>">
                                        <div class="comment-avatar">
                                            <?= strtoupper(substr($c['first_name'] ?? 'U', 0, 1)) ?>
                                        </div>
                                        <div class="comment-body">
                                            <div class="comment-meta">
                                                <strong><?= e(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')) ?></strong>
                                                <?php if ($c['is_verified']): ?>
                                                    <span class="verified-badge">✓ <?= __('forum.verified') ?></span>
                                                <?php endif; ?>
                                                <small><?= date('M d H:i', strtotime($c['created_at'])) ?></small>
                                            </div>
                                            <p><?= nl2br(e($c['content'])) ?></p>

                                            <div class="comment-actions">
                                                <?php if ($p['user_id'] == $_SESSION['user_id'] && !$c['is_verified']): ?>
                                                    <button onclick="acceptAnswer(<?= $c['id'] ?>)" class="btn-sm">
                                                        ✅ <?= __('forum.accept') ?>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($data['is_officer']): ?>
                                                    <?php if (!$c['is_verified']): ?>
                                                        <button onclick="verifyAnswer(<?= $c['id'] ?>, 'verify')" class="btn-sm">
                                                            ✓ <?= __('forum.verify') ?>
                                                        </button>
                                                    <?php else: ?>
                                                        <button onclick="verifyAnswer(<?= $c['id'] ?>, 'unverify')" class="btn-sm ghost">
                                                            ✕ Unverify
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Reply Form -->
                        <form action="<?= BASE_URL ?>/forum/answer/<?= $p['id'] ?>" method="POST" class="reply-form">
                            <?= \App\Core\Security::csrfField() ?>
                            <textarea name="content" id="reply-<?= $p['id'] ?>" rows="2" required minlength="5"
                                      placeholder="<?= __('forum.answer_hint') ?>"></textarea>
                            <button type="submit" class="btn-sm"><?= __('forum.answer') ?></button>
                        </form>
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
        btn.querySelector('span').textContent = data.count;
    }
}

async function acceptAnswer(commentId) {
    if (!confirm('Accept this as the best answer?')) return;
    const res = await fetch(`<?= BASE_URL ?>/forum/accept/${commentId}`, { method: 'POST' });
    const data = await res.json();
    if (data.success) location.reload();
}

async function verifyAnswer(commentId, action) {
    const res = await fetch('<?= BASE_URL ?>/forum/verifyAnswer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `entity_type=feed_comment&entity_id=${commentId}&action=${action}`
    });
    const data = await res.json();
    if (data.success) location.reload();
}

async function reportPost(postId) {
    const reason = prompt('Why are you reporting this post?');
    if (!reason) return;
    await fetch(`<?= BASE_URL ?>/forum/report/${postId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'reason=' + encodeURIComponent(reason)
    });
    alert('Thank you. Admins will review this post.');
}

async function deletePost(postId) {
    if (!confirm('Delete this post?')) return;
    const res = await fetch(`<?= BASE_URL ?>/forum/delete/${postId}`, { method: 'POST' });
    const data = await res.json();
    if (data.success) {
        document.getElementById('post-' + postId).remove();
    }
}
</script>

<style>
.channel-hero {
    padding: 25px !important;
    max-width: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.channel-hero-left { display: flex; gap: 18px; align-items: center; }
.channel-icon {
    width: 70px; height: 70px;
    border-radius: 18px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex; align-items: center; justify-content: center;
    font-size: 34px;
}
.channel-hero h1 { margin: 4px 0; font-size: 22px; }
.channel-hero p { color: var(--text-sub); margin: 0; font-size: 13px; }
.back-link {
    color: var(--accent);
    text-decoration: none;
    font-size: 12px;
    display: inline-block;
}
.back-link:hover { text-decoration: underline; }

.forum-tabs-card { padding: 0 !important; }
.forum-tabs {
    display: flex;
    gap: 4px;
    padding: 8px;
    overflow-x: auto;
}
.forum-tabs a {
    padding: 10px 18px;
    border-radius: 10px;
    color: var(--text-sub);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
    transition: 200ms;
}
.forum-tabs a:hover { background: rgba(34,197,94,0.1); color: var(--accent); }
.forum-tabs a.active {
    background: linear-gradient(135deg, rgba(22,163,74,0.35), rgba(132,204,22,0.15));
    color: white;
    font-weight: 600;
}

.forum-form textarea,
.reply-form textarea {
    width: 100%;
    padding: 14px 16px;
    background: rgba(0,0,0,0.25);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: white;
    font-family: inherit;
    font-size: 14px;
    outline: none;
    resize: vertical;
    min-height: 60px;
}
.forum-form input[type="text"] {
    width: 100%;
    padding: 12px 16px;
    background: rgba(0,0,0,0.25);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: white;
    font-family: inherit;
    font-size: 14px;
    outline: none;
}
.forum-form textarea:focus,
.forum-form input:focus,
.reply-form textarea:focus { border-color: var(--accent); }

.forum-form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 12px;
    gap: 12px;
}
.attach-btn {
    font-size: 20px;
    cursor: pointer;
    padding: 8px 14px;
    border-radius: 10px;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border);
    transition: 200ms;
}
.attach-btn:hover { border-color: var(--accent); background: rgba(34,197,94,0.1); }
.forum-form-actions button { width: auto; padding: 12px 24px; }

.posts-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-top: 16px;
}
.post-card { padding: 22px !important; max-width: none; }

.post-header {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--border);
}
.post-avatar {
    width: 46px; height: 46px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex; align-items: center; justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
}
.post-meta strong { display: block; font-size: 14px; }
.post-meta small { color: var(--text-sub); font-size: 11px; }

.post-title { margin: 0 0 10px; font-size: 18px; }
.post-content { color: var(--text-sub); font-size: 14px; line-height: 1.6; margin: 0 0 14px; }
.post-media {
    max-width: 100%;
    border-radius: 12px;
    margin: 12px 0;
    cursor: pointer;
    display: block;
}

.post-actions {
    display: flex;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid var(--border);
    flex-wrap: wrap;
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
}
.action-btn:hover {
    border-color: var(--accent);
    color: var(--accent);
    background: rgba(34,197,94,0.08);
}
.action-btn.danger:hover {
    border-color: #EF4444;
    color: #EF4444;
    background: rgba(239,68,68,0.08);
}

.comments-section {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.comment {
    display: flex;
    gap: 10px;
    padding: 12px;
    background: rgba(255,255,255,0.03);
    border-radius: 10px;
    border-left: 3px solid transparent;
}
.comment.verified {
    background: rgba(34,197,94,0.08);
    border-left-color: var(--accent);
}
.comment-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700;
    flex-shrink: 0;
}
.comment-body { flex-grow: 1; min-width: 0; }
.comment-meta { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 4px; }
.comment-meta strong { font-size: 13px; }
.comment-meta small { color: var(--text-sub); font-size: 11px; margin-left: auto; }
.comment-body p { margin: 4px 0; color: var(--text-sub); font-size: 13px; line-height: 1.5; }
.comment-actions { display: flex; gap: 8px; margin-top: 8px; }

.reply-form {
    display: flex;
    gap: 10px;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid var(--border);
    align-items: flex-start;
}
.reply-form textarea { flex-grow: 1; min-height: 44px; }
.reply-form button { width: auto; padding: 12px 20px; flex-shrink: 0; }

.verified-badge {
    background: #0EA5E9;
    color: white;
    padding: 1px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
}

@media (max-width: 700px) {
    .channel-hero { flex-direction: column; align-items: flex-start; }
    .reply-form { flex-direction: column; }
    .reply-form button { width: 100%; }
}
</style>