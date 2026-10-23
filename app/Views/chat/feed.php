<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card feed-composer">
            <form action="<?= BASE_URL ?>/feed/create" method="POST" enctype="multipart/form-data">
                <textarea name="content" placeholder="Share a farming question, tip, or photo..." rows="3" required></textarea>
                <div class="composer-actions">
                    <label>📎 <input type="file" name="media" hidden></label>
                    <select name="type">
                        <option value="text">Post</option>
                        <option value="question">Question</option>
                        <option value="tip">Tip</option>
                        <option value="image">Photo</option>
                    </select>
                    <button type="submit" class="btn-sm">Post</button>
                </div>
            </form>
        </section>

        <section class="feed-stream">
            <?php foreach ($data['posts'] as $p): ?>
                <article class="glass-card feed-post">
                    <header class="post-header">
                        <div class="post-avatar"><?= strtoupper(substr($p['first_name'],0,1)) ?></div>
                        <div>
                            <strong><?= e($p['first_name'].' '.$p['last_name']) ?></strong>
                            <?php if (!empty($p['badge_type'])): ?>
                                <span class="verified-badge">✓ <?= ucfirst($p['badge_type']) ?></span>
                            <?php endif; ?>
                            <small><?= date('M d, H:i', strtotime($p['created_at'])) ?></small>
                        </div>
                    </header>
                    <?php if ($p['title']): ?><h3><?= e($p['title']) ?></h3><?php endif; ?>
                    <p><?= nl2br(e($p['content'])) ?></p>
                    <?php if ($p['media_url']): ?>
                        <img src="<?= e($p['media_url']) ?>" class="post-media">
                    <?php endif; ?>
                    <footer class="post-actions">
                        <button onclick="likePost(<?= $p['id'] ?>, this)">👍 <span><?= $p['likes_count'] ?></span></button>
                        <button>💬 <?= $p['comments_count'] ?></button>
                        <button>↗ Share</button>
                    </footer>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</div>