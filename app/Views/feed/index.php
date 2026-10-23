<?php
$posts        = $posts        ?? ($data['posts']        ?? []);
$trending     = $trending     ?? ($data['trending']     ?? []);
$contributors = $contributors ?? ($data['contributors'] ?? []);
$suggested    = $suggested    ?? ($data['suggested']    ?? []);
$weather      = $weather      ?? ($data['weather']      ?? null);
$market       = $market       ?? ($data['market']       ?? []);
$crops        = $crops        ?? ($data['crops']        ?? []);
$districts    = $districts    ?? ($data['districts']    ?? []);
$stats        = $stats        ?? ($data['stats']        ?? ['posts'=>0,'members'=>0,'today'=>0,'active'=>0]);

$myId    = (int)($_SESSION['user_id'] ?? 0);
$myName  = $_SESSION['name'] ?? 'User';
$myInit  = strtoupper(substr($myName, 0, 1));

/* ── Demo seed if empty ── */
if (empty($posts)) {
    $posts = [
        [
            'id' => 1,
            'author' => ['name'=>'Jean Paul K.','initials'=>'JP','role'=>'Cooperative Leader','location'=>'Musanze','verified'=>true,'avatar'=>null],
            'type' => 'tip', 'time' => '12 min ago',
            'content' => "Just wrapped up our **Season B potato planting** in Musanze. Spacing at **25cm × 75cm** gave 18% higher yield than 30cm last year.\n\nAnyone else tried closer spacing?",
            'tags' => ['#Potatoes','#SeasonB','#Musanze'],
            'image_url' => null,
            'reactions' => ['like'=>84,'love'=>12,'insightful'=>26],
            'comments' => 14, 'shares' => 6, 'views' => 312,
            'liked_by_me' => false, 'saved_by_me' => false,
        ],
        [
            'id' => 2,
            'author' => ['name'=>'Marie U.','initials'=>'MU','role'=>'Agronomist','location'=>'Kigali','verified'=>true],
            'type' => 'question', 'time' => '1 hour ago',
            'content' => "⚠️ **Fall armyworm alert** for Eastern Province — early-stage larvae spotted around Kirehe and Nyagatare.\n\nRecommendation: scout early morning, handpick if <5 larvae/plant, else Emamectin benzoate. **Avoid broad-spectrum pesticides.**",
            'tags' => ['#FallArmyworm','#Maize','#Eastern'],
            'image_url' => null,
            'reactions' => ['like'=>156,'love'=>8,'insightful'=>92],
            'comments' => 47, 'shares' => 38, 'views' => 1842,
            'liked_by_me' => true, 'saved_by_me' => false,
        ],
        [
            'id' => 3,
            'author' => ['name'=>'Alice M.','initials'=>'AM','role'=>'Researcher','location'=>'Huye','verified'=>false],
            'type' => 'market', 'time' => '3 hours ago',
            'content' => "📈 **Maize prices in Huye Market** climbed to **465 RWF/kg** — highest in 8 months. If you're holding stock, consider selling before November's new harvest floods the market.",
            'tags' => ['#MaizePrices','#Huye','#Market'],
            'image_url' => null,
            'reactions' => ['like'=>210,'love'=>34,'insightful'=>78],
            'comments' => 62, 'shares' => 91, 'views' => 3210,
            'liked_by_me' => false, 'saved_by_me' => true,
        ],
    ];
}

if (empty($trending)) {
    $trending = [
        ['tag'=>'Fall armyworm','count'=>128,'category'=>'pest'],
        ['tag'=>'Season B 2026','count'=>96,'category'=>'season'],
        ['tag'=>'Maize prices','count'=>74,'category'=>'market'],
        ['tag'=>'Irrigation tips','count'=>58,'category'=>'tip'],
        ['tag'=>'Coffee rust','count'=>42,'category'=>'disease'],
    ];
}
if (empty($contributors)) {
    $contributors = [
        ['name'=>'Marie U.','role'=>'Agronomist','points'=>2480,'verified'=>true],
        ['name'=>'Jean Paul K.','role'=>'Cooperative Lead','points'=>1920,'verified'=>true],
        ['name'=>'Alice M.','role'=>'Researcher','points'=>1560,'verified'=>false],
        ['name'=>'Eric N.','role'=>'Agricultural Officer','points'=>1340,'verified'=>true],
    ];
}
if (empty($suggested)) {
    $suggested = [
        ['id'=>101,'name'=>'Kamonyi Agri Hub','role'=>'Official Cooperative','initials'=>'KA'],
        ['id'=>102,'name'=>'RAB Rwanda','role'=>'Research Board','initials'=>'RN'],
        ['id'=>103,'name'=>'MINAGRI','role'=>'Ministry','initials'=>'MN'],
    ];
}

function feedTypeMeta(string $type): array {
    return match($type) {
        'question' => ['icon'=>'❓','label'=>'Question','cls'=>'ft-question'],
        'tip'      => ['icon'=>'💡','label'=>'Tip','cls'=>'ft-tip'],
        'market'   => ['icon'=>'📈','label'=>'Market Alert','cls'=>'ft-market'],
        'photo'    => ['icon'=>'📷','label'=>'Photo','cls'=>'ft-photo'],
        'event'    => ['icon'=>'📅','label'=>'Event','cls'=>'ft-event'],
        default    => ['icon'=>'📝','label'=>'Post','cls'=>'ft-default'],
    };
}
?>
<div class="dashboard-layout feed-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="main-content">

            <!-- ═══════════════ HERO ═══════════════ -->
            <section class="glass-card feed-hero">
                <div class="hero-left">
                    <span class="hero-tag">
                        <span class="live-dot"></span> LIVE COMMUNITY
                    </span>
                    <h1>🌾 Community Feed</h1>
                    <p>Learn from <strong><?= number_format(max(1240, $stats['members'])) ?>+ Rwandan farmers</strong>, agronomists, and cooperatives. Ask, share, and grow together.</p>
                </div>
                <div class="hero-stats">
                    <div class="hs-item">
                        <strong><?= number_format($stats['posts']) ?></strong>
                        <small>Posts</small>
                    </div>
                    <div class="hs-item">
                        <strong><?= number_format($stats['members']) ?></strong>
                        <small>Members</small>
                    </div>
                    <div class="hs-item highlight">
                        <strong><?= number_format($stats['today']) ?></strong>
                        <small>Today</small>
                    </div>
                    <div class="hs-item">
                        <strong><?= number_format($stats['active']) ?></strong>
                        <small>Online</small>
                    </div>
                </div>
            </section>

            <!-- ═══════════════ 3-COLUMN LAYOUT ═══════════════ -->
            <div class="feed-layout">

                <!-- ═══ LEFT SIDEBAR ═══ -->
                <aside class="feed-side-left">

                    <!-- Feed Tabs -->
                    <section class="glass-card side-card">
                        <h3 class="side-title">📚 Feed</h3>
                        <nav class="feed-tabs" id="feedTabs">
                            <button type="button" class="ft-tab active" data-type="all">
                                <span class="ft-icon">🌐</span>
                                <span>All Posts</span>
                                <span class="ft-count"><?= count($posts) ?></span>
                            </button>
                            <button type="button" class="ft-tab" data-type="question">
                                <span class="ft-icon">❓</span>
                                <span>Questions</span>
                            </button>
                            <button type="button" class="ft-tab" data-type="tip">
                                <span class="ft-icon">💡</span>
                                <span>Tips</span>
                            </button>
                            <button type="button" class="ft-tab" data-type="market">
                                <span class="ft-icon">📈</span>
                                <span>Market</span>
                            </button>
                            <button type="button" class="ft-tab" data-type="photo">
                                <span class="ft-icon">📷</span>
                                <span>Photos</span>
                            </button>
                            <button type="button" class="ft-tab" data-type="saved">
                                <span class="ft-icon">🔖</span>
                                <span>Saved</span>
                            </button>
                        </nav>
                    </section>

                    <!-- Crop Filter -->
                    <?php if (!empty($crops)): ?>
                    <section class="glass-card side-card">
                        <h3 class="side-title">🌱 Filter by Crop</h3>
                        <div class="crop-mini-list" id="cropFilter">
                            <button type="button" class="crop-mini active" data-crop="0">
                                <span>🌾</span><span>All Crops</span>
                            </button>
                            <?php foreach (array_slice($crops, 0, 6) as $c): ?>
                                <button type="button" class="crop-mini" data-crop="<?= (int)$c['id'] ?>">
                                    <span><?= e($c['icon'] ?? '🌱') ?></span>
                                    <span><?= e($c['name']) ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Quick Links -->
                    <section class="glass-card side-card">
                        <h3 class="side-title">⚡ Quick Actions</h3>
                        <div class="quick-links">
                            <a href="<?= BASE_URL ?>/crops/agribot" class="q-link">
                                <span class="ql-icon">🤖</span>
                                <div><strong>Ask AgriBot</strong><small>AI farming assistant</small></div>
                            </a>
                            <a href="<?= BASE_URL ?>/crops/detect" class="q-link">
                                <span class="ql-icon">🔬</span>
                                <div><strong>Disease Check</strong><small>Scan a crop photo</small></div>
                            </a>
                            <a href="<?= BASE_URL ?>/marketplace" class="q-link">
                                <span class="ql-icon">🛒</span>
                                <div><strong>Marketplace</strong><small>Buy & sell produce</small></div>
                            </a>
                            <a href="<?= BASE_URL ?>/weather" class="q-link">
                                <span class="ql-icon">🌤️</span>
                                <div><strong>Weather</strong><small>5-day forecast</small></div>
                            </a>
                        </div>
                    </section>
                </aside>

                <!-- ═══ MAIN FEED ═══ -->
                <div class="feed-main">

                    <!-- COMPOSER -->
                    <section class="glass-card composer">
                        <form id="composerForm" method="POST" action="<?= BASE_URL ?>/feed/create" enctype="multipart/form-data">
                            <?= \App\Core\Security::csrfField() ?>

                            <div class="composer-row">
                                <div class="composer-avatar"><?= e($myInit) ?></div>
                                <textarea id="postContent" name="content" rows="2"
                                          placeholder="Share a farming question, tip, or photo, <?= e(explode(' ', $myName)[0]) ?>…"
                                          required></textarea>
                            </div>

                            <!-- Image preview -->
                            <div class="composer-preview" id="composerPreview" style="display:none">
                                <img id="previewImg" alt="">
                                <button type="button" class="preview-remove" onclick="removePreview()">✕</button>
                            </div>

                            <div class="composer-toolbar">
                                <div class="composer-tools">
                                    <label class="tool-btn" title="Add photo">
                                        <span>📷</span>
                                        <input type="file" name="media" id="mediaInput" accept="image/*" hidden>
                                    </label>
                                    <button type="button" class="tool-btn" onclick="insertEmoji('🌾')" title="Add emoji">😊</button>
                                    <button type="button" class="tool-btn" onclick="toggleTagInput()" title="Tag crop">🌱</button>
                                    <button type="button" class="tool-btn" onclick="getLocation()" title="Add location" id="locBtn">📍</button>
                                    <input type="hidden" name="location" id="locationField">
                                    <input type="hidden" name="crop_tag" id="cropTagField">
                                </div>

                                <div class="composer-submit">
                                    <select name="type" class="type-select">
                                        <option value="tip">💡 Tip</option>
                                        <option value="question">❓ Question</option>
                                        <option value="market">📈 Market</option>
                                        <option value="photo">📷 Photo</option>
                                        <option value="event">📅 Event</option>
                                    </select>
                                    <button type="submit" class="btn-post" id="btnPost">
                                        <span>Post</span>
                                        <span class="arrow">→</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Crop tag input (hidden until clicked) -->
                            <div class="crop-tag-row" id="cropTagRow" style="display:none">
                                <input type="text" id="cropTagInput" placeholder="Tag a crop (e.g., Maize)">
                                <button type="button" onclick="applyCropTag()">Add</button>
                            </div>
                        </form>
                    </section>

                    <!-- FILTER BAR -->
                    <div class="feed-filter-bar glass-card">
                        <div class="filter-left">
                            <span class="filter-label">Sort by:</span>
                            <button type="button" class="fb-chip active" data-sort="newest">🕐 Newest</button>
                            <button type="button" class="fb-chip" data-sort="trending">🔥 Trending</button>
                            <button type="button" class="fb-chip" data-sort="top">❤️ Top</button>
                        </div>
                        <div class="filter-right">
                            <span class="results-count" id="resultsCount"><?= count($posts) ?> posts</span>
                        </div>
                    </div>

                    <!-- POSTS -->
                    <section class="posts-list" id="postsList">
                        <?php foreach ($posts as $p):
                            $author = $p['author'] ?? [];
                            $name   = $author['name'] ?? 'Unknown';
                            $inits  = $author['initials'] ?? strtoupper(substr($name, 0, 1));
                            $role   = $author['role'] ?? 'Farmer';
                            $loc    = $author['location'] ?? null;
                            $v      = !empty($author['verified']);
                            $type   = $p['type'] ?? 'tip';
                            $r      = $p['reactions'] ?? ['like'=>0,'love'=>0,'insightful'=>0];
                            $total  = array_sum($r);
                            $meta   = feedTypeMeta($type);
                            $postId = (int)$p['id'];
                            $liked  = !empty($p['liked_by_me']);
                            $saved  = !empty($p['saved_by_me']);
                        ?>
                            <article class="glass-card post-card"
                                     data-type="<?= e($type) ?>"
                                     data-post-id="<?= $postId ?>"
                                     data-likes="<?= (int)($r['like'] ?? 0) ?>"
                                     data-time="<?= strtotime($p['time'] ?? 'now') ?>">

                                <!-- HEADER -->
                                <header class="post-head">
                                    <div class="post-avatar"><?= e($inits) ?></div>
                                    <div class="post-meta">
                                        <div class="post-author-row">
                                            <strong><?= e($name) ?></strong>
                                            <?php if ($v): ?>
                                                <span class="verified-badge" title="Verified">✓</span>
                                            <?php endif; ?>
                                            <span class="role-pill"><?= e($role) ?></span>
                                        </div>
                                        <div class="post-sub">
                                            <?php if ($loc): ?><span>📍 <?= e($loc) ?></span><span class="dot">·</span><?php endif; ?>
                                            <span><?= e($p['time'] ?? 'now') ?></span>
                                            <span class="dot">·</span>
                                            <span class="type-badge <?= $meta['cls'] ?>"><?= $meta['icon'] ?> <?= $meta['label'] ?></span>
                                        </div>
                                    </div>
                                    <div class="post-head-actions">
                                        <?php if ((int)($p['author_id'] ?? 0) === $myId): ?>
                                            <button type="button" class="icon-btn" title="Delete post" onclick="deletePost(<?= $postId ?>)">🗑️</button>
                                        <?php endif; ?>
                                        <button type="button" class="icon-btn" title="More" onclick="openPostMenu(<?= $postId ?>, event)">⋯</button>
                                    </div>
                                </header>

                                <!-- BODY -->
                                <div class="post-body">
                                    <?= nl2br(preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', e($p['content'] ?? ''))) ?>
                                </div>

                                <!-- IMAGE -->
                                <?php if (!empty($p['image_url'])): ?>
                                    <div class="post-image">
                                        <img src="<?= e($p['image_url']) ?>" alt="" loading="lazy" onclick="openLightbox(this.src)">
                                    </div>
                                <?php endif; ?>

                                <!-- TAGS -->
                                <?php if (!empty($p['tags'])): ?>
                                    <div class="post-tags">
                                        <?php foreach ($p['tags'] as $t): ?>
                                            <a href="#" class="tag-chip"><?= e($t) ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- STATS -->
                                <div class="post-stats-row">
                                    <div class="stats-left">
                                        <span class="react-emojis">
                                            <span>👍</span><span>❤️</span><span>💡</span>
                                        </span>
                                        <span class="stats-text" id="reactCount-<?= $postId ?>"><?= number_format($total) ?> reactions</span>
                                    </div>
                                    <div class="stats-right">
                                        <button type="button" class="stats-link" onclick="openComments(<?= $postId ?>)">
                                            <?= number_format($p['comments'] ?? 0) ?> comments
                                        </button>
                                        <span class="dot">·</span>
                                        <span><?= number_format($p['shares'] ?? 0) ?> shares</span>
                                        <span class="dot">·</span>
                                        <span><?= number_format($p['views'] ?? 0) ?> views</span>
                                    </div>
                                </div>

                                <!-- ACTION BUTTONS -->
                                <div class="post-actions">
                                    <button type="button"
                                            class="act-btn like <?= $liked ? 'active' : '' ?>"
                                            onclick="toggleLike(<?= $postId ?>, this)">
                                        <span class="act-icon">👍</span>
                                        <span class="act-label"><?= $liked ? 'Liked' : 'Like' ?></span>
                                    </button>
                                    <button type="button" class="act-btn" onclick="openComments(<?= $postId ?>)">
                                        <span class="act-icon">💬</span>
                                        <span class="act-label">Comment</span>
                                    </button>
                                    <button type="button" class="act-btn" onclick="openShareModal(<?= $postId ?>)">
                                        <span class="act-icon">🔄</span>
                                        <span class="act-label">Share</span>
                                    </button>
                                    <button type="button"
                                            class="act-btn save <?= $saved ? 'active' : '' ?>"
                                            onclick="toggleSave(<?= $postId ?>, this)">
                                        <span class="act-icon">🔖</span>
                                        <span class="act-label"><?= $saved ? 'Saved' : 'Save' ?></span>
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </section>

                    <!-- LOAD MORE -->
                    <button type="button" class="load-more" id="loadMore">
                        <span>Load more posts</span>
                        <span class="lm-icon">↓</span>
                    </button>
                </div>

                <!-- ═══ RIGHT SIDEBAR ═══ -->
                <aside class="feed-side-right">

                    <!-- Weather Widget -->
                    <?php if ($weather): ?>
                    <section class="glass-card weather-card">
                        <div class="weather-top">
                            <span class="weather-icon"><?= $weather['icon'] ?? '🌤️' ?></span>
                            <div>
                                <strong><?= $weather['temp'] ?? 24 ?>°C</strong>
                                <small><?= e($weather['condition'] ?? 'Partly cloudy') ?></small>
                            </div>
                        </div>
                        <div class="weather-loc">
                            📍 <?= e($weather['district'] ?? 'Your District') ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Trending -->
                    <section class="glass-card side-card">
                        <div class="side-head">
                            <h3>🔥 Trending</h3>
                            <span class="side-sub">Last 24h</span>
                        </div>
                        <div class="trending-list">
                            <?php foreach ($trending as $i => $t): ?>
                                <a href="#" class="trend-item" data-tag="<?= e($t['tag']) ?>">
                                    <span class="trend-rank"><?= $i + 1 ?></span>
                                    <div class="trend-info">
                                        <strong>#<?= e($t['tag']) ?></strong>
                                        <small><?= number_format($t['count']) ?> posts</small>
                                    </div>
                                    <span class="trend-arrow">→</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Top Contributors -->
                    <section class="glass-card side-card">
                        <div class="side-head">
                            <h3>🏆 Top Contributors</h3>
                            <span class="side-sub">This month</span>
                        </div>
                        <div class="contrib-list">
                            <?php foreach ($contributors as $i => $c): ?>
                                <div class="contrib-item">
                                    <span class="contrib-medal">
                                        <?= $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '#' . ($i + 1))) ?>
                                    </span>
                                    <div class="avatar-sm"><?= e(substr($c['name'], 0, 1)) ?></div>
                                    <div class="contrib-info">
                                        <strong>
                                            <?= e($c['name']) ?>
                                            <?php if (!empty($c['verified'])): ?><span class="mini-verified">✓</span><?php endif; ?>
                                        </strong>
                                        <small><?= e($c['role']) ?></small>
                                    </div>
                                    <span class="contrib-points"><?= number_format($c['points']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Suggested Follows -->
                    <section class="glass-card side-card">
                        <div class="side-head">
                            <h3>✨ Suggested for You</h3>
                        </div>
                        <div class="suggest-list">
                            <?php foreach ($suggested as $s): ?>
                                <div class="suggest-item">
                                    <div class="avatar-sm"><?= e($s['initials'] ?? substr($s['name'], 0, 2)) ?></div>
                                    <div class="suggest-info">
                                        <strong><?= e($s['name']) ?></strong>
                                        <small><?= e($s['role']) ?></small>
                                    </div>
                                    <button type="button" class="btn-follow" data-uid="<?= (int)($s['id'] ?? 0) ?>">Follow</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Market Ticker -->
                    <?php if (!empty($market)): ?>
                    <section class="glass-card side-card">
                        <div class="side-head">
                            <h3>💹 Market Prices</h3>
                            <span class="side-sub">Today</span>
                        </div>
                        <div class="market-list">
                            <?php foreach (array_slice($market, 0, 5) as $m): ?>
                                <div class="market-item">
                                    <div>
                                        <strong><?= e($m['icon'] ?? '🌾') ?> <?= e($m['crop'] ?? 'Crop') ?></strong>
                                        <small><?= number_format((float)($m['price'] ?? 0)) ?> RWF/kg</small>
                                    </div>
                                    <span class="market-trend <?= ($m['change'] ?? 0) >= 0 ? 'up' : 'down' ?>">
                                        <?= ($m['change'] ?? 0) >= 0 ? '▲' : '▼' ?>
                                        <?= abs(round((float)($m['change'] ?? 0), 1)) ?>%
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Footer -->
                    <div class="feed-footer">
                        <small>SAIP Community · Made in Rwanda 🇷🇼</small>
                    </div>
                </aside>
            </div>
        </main>
    </div>
</div>

<!-- ═══════ SHARE MODAL ═══════ -->
<div class="modal-overlay" id="shareModal" style="display:none">
    <div class="modal">
        <div class="modal-header">
            <h3>Share Post</h3>
            <button type="button" class="modal-close" onclick="closeModal('shareModal')">✕</button>
        </div>
        <div class="share-grid">
            <button type="button" class="share-opt" onclick="shareVia('copy')">
                <span class="share-icon">🔗</span><span>Copy Link</span>
            </button>
            <button type="button" class="share-opt" onclick="shareVia('whatsapp')">
                <span class="share-icon">💬</span><span>WhatsApp</span>
            </button>
            <button type="button" class="share-opt" onclick="shareVia('sms')">
                <span class="share-icon">📱</span><span>SMS</span>
            </button>
            <button type="button" class="share-opt" onclick="shareVia('native')">
                <span class="share-icon">📤</span><span>More</span>
            </button>
        </div>
    </div>
</div>

<!-- ═══════ COMMENTS MODAL ═══════ -->
<div class="modal-overlay comments-overlay" id="commentsModal" style="display:none">
    <div class="modal comments-modal">
        <div class="modal-header">
            <h3>💬 Comments</h3>
            <button type="button" class="modal-close" onclick="closeModal('commentsModal')">✕</button>
        </div>
        <div class="comments-body" id="commentsBody">
            <div class="comments-loading">Loading comments…</div>
        </div>
        <div class="comment-composer">
            <div class="avatar-xs"><?= e($myInit) ?></div>
            <input type="text" id="commentInput" placeholder="Write a comment…">
            <button type="button" onclick="postComment()" class="comment-send">➤</button>
        </div>
    </div>
</div>

<!-- ═══════ LIGHTBOX ═══════ -->
<div class="lightbox" id="lightbox" style="display:none" onclick="closeLightbox()">
    <img id="lightboxImg" alt="">
</div>

<style>
/* ═══════════════════════════════════════════════════════════
   BASE
   ═══════════════════════════════════════════════════════════ */
html,body{margin:0!important;padding:0!important;overflow-x:hidden!important}
.feed-page.dashboard-layout{display:block!important;width:100%!important;background:#0b1120!important;min-height:100vh!important}
.feed-page .main-wrap{margin-left:260px!important;width:calc(100% - 260px)!important;min-height:100vh!important;box-sizing:border-box!important}
.feed-page .main-content{padding:20px 28px 60px!important;display:flex!important;flex-direction:column!important;gap:18px!important;box-sizing:border-box!important}
.feed-page .glass-card{background:rgba(30,41,59,.7)!important;backdrop-filter:blur(16px)!important;border:1px solid rgba(255,255,255,.08)!important;border-radius:18px!important;padding:20px!important;box-sizing:border-box!important;width:100%!important}

/* ═══════════════════════════════════════════════════════════
   HERO
   ═══════════════════════════════════════════════════════════ */
.feed-hero{display:flex!important;justify-content:space-between!important;align-items:center!important;gap:24px!important;flex-wrap:wrap!important;padding:24px 28px!important;background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(56,189,248,.08))!important;position:relative!important;overflow:hidden!important}
.feed-hero::before{content:'';position:absolute;top:-40%;right:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(16,185,129,.15),transparent 70%);pointer-events:none}
.hero-left{flex:1;min-width:280px;position:relative;z-index:1}
.hero-tag{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.35);border-radius:999px;font-size:10.5px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;margin-bottom:10px}
.live-dot{width:6px;height:6px;background:#34d399;border-radius:50%;animation:pulse 1.6s infinite;box-shadow:0 0 8px #34d399}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.feed-hero h1{font-size:26px;font-weight:800;color:#f1f5f9;margin:0 0 8px}
.feed-hero p{color:#94a3b8;font-size:13.5px;margin:0;line-height:1.55;max-width:640px}
.feed-hero strong{color:#34d399}
.hero-stats{display:flex;gap:10px;position:relative;z-index:1;flex-wrap:wrap}
.hs-item{display:flex;flex-direction:column;gap:2px;padding:12px 18px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:12px;min-width:90px}
.hs-item strong{font-size:20px;font-weight:800;color:#f1f5f9;line-height:1}
.hs-item small{font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;font-weight:700}
.hs-item.highlight strong{color:#34d399}

/* ═══════════════════════════════════════════════════════════
   3-COLUMN GRID
   ═══════════════════════════════════════════════════════════ */
.feed-layout{display:grid;grid-template-columns:240px minmax(0,1fr) 320px;gap:18px;align-items:start}
.feed-side-left,.feed-side-right{display:flex;flex-direction:column;gap:14px;position:sticky;top:20px}
.feed-main{display:flex;flex-direction:column;gap:16px;min-width:0}

/* ═══════════════════════════════════════════════════════════
   SIDEBARS
   ═══════════════════════════════════════════════════════════ */
.side-card{padding:16px!important}
.side-title{font-size:11px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin:0 0 12px}
.side-head{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06)}
.side-head h3{font-size:13.5px;font-weight:800;color:#f1f5f9;margin:0}
.side-sub{font-size:10.5px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;font-weight:700}

/* FEED TABS */
.feed-tabs{display:flex;flex-direction:column;gap:2px}
.ft-tab{display:flex;align-items:center;gap:10px;padding:10px 12px;background:transparent;color:#94a3b8;border:none;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;transition:.15s;text-align:left;width:100%}
.ft-tab:hover{background:rgba(255,255,255,.04);color:#e2e8f0}
.ft-tab.active{background:linear-gradient(135deg,rgba(16,185,129,.18),rgba(5,150,105,.1));color:#34d399;box-shadow:inset 3px 0 0 #10b981}
.ft-icon{font-size:15px}
.ft-count{margin-left:auto;font-size:10.5px;font-weight:800;padding:2px 7px;background:rgba(16,185,129,.2);color:#34d399;border-radius:999px}

/* CROP MINI LIST */
.crop-mini-list{display:flex;flex-direction:column;gap:2px}
.crop-mini{display:flex;align-items:center;gap:8px;padding:8px 12px;background:transparent;color:#cbd5e1;border:none;border-radius:9px;font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit;text-align:left;transition:.15s}
.crop-mini:hover{background:rgba(255,255,255,.04)}
.crop-mini.active{background:rgba(16,185,129,.12);color:#34d399}

/* QUICK LINKS */
.quick-links{display:flex;flex-direction:column;gap:4px}
.q-link{display:flex;align-items:center;gap:10px;padding:10px 12px;background:transparent;border-radius:10px;color:#cbd5e1;text-decoration:none;transition:.15s;border:1px solid transparent}
.q-link:hover{background:rgba(255,255,255,.04);border-color:rgba(16,185,129,.2)}
.q-link .ql-icon{font-size:18px;flex-shrink:0}
.q-link div strong{display:block;font-size:12.5px;color:#f1f5f9;font-weight:700;margin-bottom:1px}
.q-link div small{display:block;font-size:10.5px;color:#94a3b8}

/* ═══════════════════════════════════════════════════════════
   COMPOSER
   ═══════════════════════════════════════════════════════════ */
.composer{padding:18px!important}
.composer-row{display:flex;gap:12px;margin-bottom:14px}
.composer-avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#10b981,#38bdf8);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;color:#fff;flex-shrink:0;box-shadow:0 4px 12px rgba(16,185,129,.3)}
#postContent{flex:1;background:rgba(0,0,0,.25);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:12px 16px;color:#f1f5f9;font-size:14px;font-family:inherit;outline:none;resize:none;transition:.2s;line-height:1.5;min-height:64px;max-height:240px;box-sizing:border-box}
#postContent:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.12)}
#postContent::placeholder{color:#64748b}

.composer-preview{position:relative;margin:0 0 14px 56px;max-width:320px;border-radius:12px;overflow:hidden;border:1px solid rgba(16,185,129,.3)}
.composer-preview img{width:100%;display:block}
.preview-remove{position:absolute;top:8px;right:8px;width:28px;height:28px;background:rgba(0,0,0,.7);color:#fff;border:none;border-radius:50%;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;font-family:inherit;padding:0;backdrop-filter:blur(6px)}
.preview-remove:hover{background:rgba(239,68,68,.9)}

.composer-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding-top:12px;border-top:1px solid rgba(255,255,255,.06)}
.composer-tools{display:flex;gap:4px;flex-wrap:wrap}
.tool-btn{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;background:transparent;border:none;color:#94a3b8;border-radius:9px;cursor:pointer;font-size:17px;transition:.15s;font-family:inherit;padding:0}
.tool-btn:hover{background:rgba(16,185,129,.12);color:#34d399}
.composer-submit{display:flex;gap:8px;align-items:center}
.type-select{padding:9px 30px 9px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#cbd5e1;font-size:12.5px;font-weight:600;font-family:inherit;cursor:pointer;outline:none;-webkit-appearance:none;appearance:none;background-image:url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e");background-repeat:no-repeat;background-position:right 8px center;background-size:12px}
.type-select option{background:#1e293b}
.btn-post{display:inline-flex;align-items:center;gap:6px;padding:10px 22px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:10px;font-size:13.5px;font-weight:800;cursor:pointer;font-family:inherit;box-shadow:0 4px 15px rgba(16,185,129,.35);transition:.2s;letter-spacing:.3px}
.btn-post:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 6px 20px rgba(16,185,129,.5)}
.btn-post:disabled{opacity:.5;cursor:not-allowed;transform:none}
.btn-post .arrow{transition:.2s}
.btn-post:hover:not(:disabled) .arrow{transform:translateX(3px)}

.crop-tag-row{display:flex;gap:8px;margin-top:12px;margin-left:56px}
.crop-tag-row input{flex:1;padding:9px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(16,185,129,.3);border-radius:9px;color:#f1f5f9;font-size:13px;font-family:inherit;outline:none}
.crop-tag-row button{padding:9px 16px;background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.35);border-radius:9px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit}

/* ═══════════════════════════════════════════════════════════
   FILTER BAR
   ═══════════════════════════════════════════════════════════ */
.feed-filter-bar{display:flex!important;justify-content:space-between!important;align-items:center!important;gap:12px!important;padding:12px 18px!important;flex-wrap:wrap!important}
.filter-left{display:flex;gap:6px;align-items:center;flex-wrap:wrap}
.filter-label{font-size:11.5px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-right:4px}
.fb-chip{padding:7px 14px;background:rgba(255,255,255,.03);color:#94a3b8;border:1px solid rgba(255,255,255,.06);border-radius:999px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s}
.fb-chip:hover{color:#e2e8f0;background:rgba(255,255,255,.06)}
.fb-chip.active{background:rgba(16,185,129,.15);color:#34d399;border-color:rgba(16,185,129,.35)}
.results-count{font-size:12px;color:#64748b;font-weight:700}

/* ═══════════════════════════════════════════════════════════
   POST CARD
   ═══════════════════════════════════════════════════════════ */
.posts-list{display:flex;flex-direction:column;gap:14px}
.post-card{padding:20px!important;transition:.25s;animation:postIn .3s ease-out}
@keyframes postIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.post-card:hover{border-color:rgba(16,185,129,.25)!important;transform:translateY(-2px);box-shadow:0 12px 30px rgba(0,0,0,.3)}

.post-head{display:flex!important;align-items:flex-start!important;gap:12px!important;margin-bottom:14px!important}
.post-avatar{width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,#10b981,#38bdf8);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;color:#fff;flex-shrink:0;box-shadow:0 4px 12px rgba(16,185,129,.25)}
.post-meta{flex:1;min-width:0}
.post-author-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px}
.post-author-row strong{font-size:14.5px;font-weight:800;color:#f1f5f9;line-height:1.2}
.verified-badge{display:inline-flex;align-items:center;justify-content:center;width:15px;height:15px;background:linear-gradient(135deg,#38bdf8,#0284c7);color:#fff;border-radius:50%;font-size:9px;font-weight:900;box-shadow:0 2px 6px rgba(56,189,248,.4)}
.role-pill{padding:3px 10px;background:rgba(167,139,250,.15);color:#a78bfa;border:1px solid rgba(167,139,250,.3);border-radius:999px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
.post-sub{display:flex;align-items:center;gap:6px;font-size:12px;color:#94a3b8;flex-wrap:wrap}
.post-sub .dot{color:#475569}
.type-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;font-size:10.5px;font-weight:700;white-space:nowrap}
.ft-tip{background:rgba(52,211,153,.15);color:#34d399;border:1px solid rgba(52,211,153,.3)}
.ft-question{background:rgba(56,189,248,.15);color:#38bdf8;border:1px solid rgba(56,189,248,.3)}
.ft-market{background:rgba(251,191,36,.15);color:#fbbf24;border:1px solid rgba(251,191,36,.3)}
.ft-photo{background:rgba(244,114,182,.15);color:#f472b6;border:1px solid rgba(244,114,182,.3)}
.ft-event{background:rgba(167,139,250,.15);color:#a78bfa;border:1px solid rgba(167,139,250,.3)}
.ft-default{background:rgba(148,163,184,.15);color:#94a3b8;border:1px solid rgba(148,163,184,.3)}

.post-head-actions{display:flex;gap:4px;flex-shrink:0}
.icon-btn{width:32px;height:32px;background:transparent;border:none;color:#94a3b8;cursor:pointer;border-radius:8px;font-size:15px;font-family:inherit;transition:.15s;padding:0;display:inline-flex;align-items:center;justify-content:center}
.icon-btn:hover{background:rgba(255,255,255,.06);color:#f1f5f9}

.post-body{font-size:14px;color:#e2e8f0;line-height:1.65;margin:0 0 14px;word-wrap:break-word;overflow-wrap:break-word}
.post-body strong{color:#34d399;font-weight:700}

.post-image{margin:0 0 14px;border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.06);cursor:pointer}
.post-image img{width:100%;max-height:520px;object-fit:cover;display:block;transition:.3s}
.post-image:hover img{transform:scale(1.02)}

.post-tags{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.tag-chip{display:inline-flex;padding:4px 11px;background:rgba(56,189,248,.08);color:#38bdf8;border:1px solid rgba(56,189,248,.22);border-radius:999px;font-size:11.5px;font-weight:600;text-decoration:none;transition:.15s}
.tag-chip:hover{background:rgba(56,189,248,.2);transform:translateY(-1px)}

.post-stats-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;margin-bottom:8px;border-top:1px solid rgba(255,255,255,.05);border-bottom:1px solid rgba(255,255,255,.05);font-size:12.5px;color:#94a3b8;flex-wrap:wrap;gap:8px}
.stats-left,.stats-right{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.react-emojis{display:inline-flex;gap:-2px}
.react-emojis span{display:inline-flex;width:18px;height:18px;align-items:center;justify-content:center;border-radius:50%;background:rgba(0,0,0,.4);font-size:11px;margin-right:-4px;border:1px solid rgba(255,255,255,.1)}
.stats-text{font-weight:600}
.stats-link{background:none;border:none;color:#94a3b8;font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit;padding:0}
.stats-link:hover{color:#34d399;text-decoration:underline}

.post-actions{display:grid;grid-template-columns:repeat(4,1fr);gap:4px}
.act-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 8px;background:transparent;border:none;color:#94a3b8;border-radius:9px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s}
.act-btn:hover{background:rgba(255,255,255,.05);color:#e2e8f0}
.act-btn.active.like{background:rgba(16,185,129,.15);color:#34d399}
.act-btn.active.save{background:rgba(251,191,36,.15);color:#fbbf24}
.act-icon{font-size:14px}
.act-label{white-space:nowrap}

/* LOAD MORE */
.load-more{display:flex;align-items:center;justify-content:center;gap:8px;padding:14px;background:rgba(30,41,59,.5);color:#cbd5e1;border:1px solid rgba(255,255,255,.08);border-radius:14px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:.2s;margin-top:6px}
.load-more:hover{background:rgba(30,41,59,.8);border-color:rgba(16,185,129,.35);color:#34d399}
.load-more .lm-icon{transition:.2s}
.load-more:hover .lm-icon{transform:translateY(3px)}

/* ═══════════════════════════════════════════════════════════
   RIGHT SIDEBAR WIDGETS
   ═══════════════════════════════════════════════════════════ */
.weather-card{display:flex!important;flex-direction:column!important;gap:12px!important;padding:16px!important;background:linear-gradient(135deg,rgba(56,189,248,.12),rgba(167,139,250,.08))!important}
.weather-top{display:flex;align-items:center;gap:12px}
.weather-icon{font-size:36px}
.weather-top strong{display:block;font-size:22px;color:#f1f5f9;font-weight:800;line-height:1}
.weather-top small{display:block;font-size:11.5px;color:#94a3b8;margin-top:3px}
.weather-loc{font-size:12px;color:#cbd5e1;font-weight:600}

/* TRENDING */
.trending-list{display:flex;flex-direction:column;gap:4px}
.trend-item{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:9px;text-decoration:none;transition:.15s}
.trend-item:hover{background:rgba(16,185,129,.08);transform:translateX(2px)}
.trend-rank{width:22px;font-size:13px;font-weight:800;color:#64748b;text-align:center;flex-shrink:0}
.trend-info{flex:1;min-width:0}
.trend-info strong{display:block;font-size:12.5px;color:#f1f5f9;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:2px}
.trend-info small{font-size:10.5px;color:#94a3b8}
.trend-arrow{color:#475569;font-size:13px;transition:.2s}
.trend-item:hover .trend-arrow{color:#34d399;transform:translateX(3px)}

/* CONTRIBUTORS */
.contrib-list{display:flex;flex-direction:column;gap:8px}
.contrib-item{display:flex;align-items:center;gap:10px;padding:6px 8px;border-radius:9px;transition:.15s}
.contrib-item:hover{background:rgba(255,255,255,.04)}
.contrib-medal{font-size:15px;width:22px;text-align:center;flex-shrink:0}
.avatar-sm{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#38bdf8,#a78bfa);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:11.5px;color:#fff;flex-shrink:0}
.contrib-info{flex:1;min-width:0}
.contrib-info strong{display:block;font-size:12.5px;color:#f1f5f9;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:1px}
.mini-verified{color:#38bdf8;font-size:9px;margin-left:2px}
.contrib-info small{font-size:10.5px;color:#94a3b8}
.contrib-points{font-size:11.5px;font-weight:800;color:#34d399;flex-shrink:0}

/* SUGGESTED */
.suggest-list{display:flex;flex-direction:column;gap:10px}
.suggest-item{display:flex;align-items:center;gap:10px}
.suggest-info{flex:1;min-width:0}
.suggest-info strong{display:block;font-size:12.5px;color:#f1f5f9;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:1px}
.suggest-info small{font-size:10.5px;color:#94a3b8;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.btn-follow{padding:6px 14px;background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.35);border-radius:8px;font-size:11.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s;flex-shrink:0}
.btn-follow:hover{background:rgba(16,185,129,.28)}
.btn-follow.following{background:rgba(148,163,184,.15);color:#94a3b8;border-color:rgba(148,163,184,.3)}

/* MARKET */
.market-list{display:flex;flex-direction:column;gap:6px}
.market-item{display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:rgba(0,0,0,.2);border-radius:9px}
.market-item strong{display:block;font-size:12.5px;color:#f1f5f9;font-weight:700;margin-bottom:2px}
.market-item small{font-size:11px;color:#94a3b8}
.market-trend{font-size:11.5px;font-weight:800;padding:3px 8px;border-radius:6px}
.market-trend.up{background:rgba(16,185,129,.15);color:#34d399}
.market-trend.down{background:rgba(239,68,68,.15);color:#f87171}

.feed-footer{text-align:center;padding:10px;font-size:10.5px;color:#475569}

/* ═══════════════════════════════════════════════════════════
   MODALS
   ═══════════════════════════════════════════════════════════ */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);align-items:center;justify-content:center;z-index:9999;padding:20px;box-sizing:border-box}
.modal-overlay[style*="display:flex"],.modal-overlay[style*="display: flex"]{display:flex!important}
.modal{background:#1e293b;border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:24px;width:100%;max-width:520px;max-height:85vh;overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.6);box-sizing:border-box;animation:modalIn .2s ease-out}
@keyframes modalIn{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}
.modal-header{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid rgba(255,255,255,.08)}
.modal-header h3{font-size:16px;color:#f1f5f9;margin:0;font-weight:800}
.modal-close{width:32px;height:32px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:9px;color:#cbd5e1;font-size:14px;cursor:pointer;font-family:inherit;padding:0;display:flex;align-items:center;justify-content:center}
.modal-close:hover{background:rgba(239,68,68,.2);color:#f87171}

/* SHARE */
.share-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
.share-opt{display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px;background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.06);border-radius:12px;color:#cbd5e1;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s}
.share-opt:hover{background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.35);color:#34d399}
.share-icon{font-size:24px}

/* COMMENTS */
.comments-overlay .modal{max-width:600px;display:flex;flex-direction:column;padding:0;overflow:hidden}
.comments-overlay .modal-header{padding:18px 22px;margin:0;border-radius:20px 20px 0 0}
.comments-body{flex:1;overflow-y:auto;padding:18px 22px;max-height:60vh;min-height:200px}
.comments-loading{text-align:center;color:#64748b;padding:40px;font-size:13px}
.comment-composer{display:flex;align-items:center;gap:10px;padding:14px 18px;border-top:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.2)}
.avatar-xs{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#10b981,#38bdf8);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;color:#fff;flex-shrink:0}
#commentInput{flex:1;padding:10px 14px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:999px;color:#f1f5f9;font-size:13px;font-family:inherit;outline:none}
#commentInput:focus{border-color:#10b981}
.comment-send{width:38px;height:38px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:50%;cursor:pointer;font-size:14px;font-family:inherit;padding:0;display:flex;align-items:center;justify-content:center}

/* LIGHTBOX */
.lightbox{position:fixed;inset:0;background:rgba(0,0,0,.95);z-index:99999;align-items:center;justify-content:center;padding:40px;cursor:zoom-out}
.lightbox[style*="display:flex"],.lightbox[style*="display: flex"]{display:flex!important}
.lightbox img{max-width:100%;max-height:100%;object-fit:contain;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.8)}

/* TOAST */
.toast{position:fixed;bottom:28px;right:28px;padding:14px 22px;border-radius:12px;font-size:13.5px;font-weight:600;color:#fff;transform:translateY(20px);opacity:0;transition:.3s;z-index:999999;box-shadow:0 12px 32px rgba(0,0,0,.5);pointer-events:none;max-width:400px}
.toast.show{transform:translateY(0);opacity:1}
.toast.success{background:linear-gradient(135deg,#10b981,#059669)}
.toast.error{background:linear-gradient(135deg,#ef4444,#dc2626)}
.toast.info{background:linear-gradient(135deg,#38bdf8,#0284c7)}

/* ═══════════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════════ */
@media (max-width:1280px){.feed-layout{grid-template-columns:220px minmax(0,1fr)}.feed-side-right{display:none}}
@media (max-width:1024px){.feed-layout{grid-template-columns:1fr}.feed-side-left{position:static;display:grid;grid-template-columns:1fr 1fr;gap:14px}}
@media (max-width:768px){
    .feed-page .main-wrap{margin-left:0!important;width:100%!important;padding-top:64px!important}
    .feed-page .main-content{padding:14px!important;gap:14px!important}
    .feed-page .glass-card{padding:16px!important;border-radius:16px!important}
    .feed-hero h1{font-size:20px!important}
    .hero-stats{width:100%}
    .hs-item{flex:1;min-width:0}
    .feed-side-left{grid-template-columns:1fr}
    .post-actions{grid-template-columns:repeat(2,1fr)}
    .act-label{font-size:11.5px}
    .composer-preview{margin-left:0;max-width:100%}
    .crop-tag-row{margin-left:0}
    .share-grid{grid-template-columns:1fr}
    .toast{left:16px;right:16px;bottom:16px}
}
</style>

<script>
(function () {
    'use strict';

    var BASE = '<?= BASE_URL ?>';
    var CSRF = '<?= e(\App\Core\Security::csrfToken()) ?>';

    /* ═══════ TOAST ═══════ */
    window.toast = function (msg, type) {
        var t = document.createElement('div');
        t.className = 'toast ' + (type || 'info');
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { t.classList.add('show'); }, 10);
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { t.remove(); }, 300);
        }, 2400);
    };

    /* ═══════ MODAL HELPERS ═══════ */
    window.closeModal = function (id) {
        var m = document.getElementById(id);
        if (m) m.style.display = 'none';
        document.body.style.overflow = '';
    };
    window.openModal = function (id) {
        var m = document.getElementById(id);
        if (m) m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    document.querySelectorAll('.modal-overlay').forEach(function (o) {
        o.addEventListener('click', function (e) {
            if (e.target === o) { o.style.display = 'none'; document.body.style.overflow = ''; }
        });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(function (m) { m.style.display = 'none'; });
            document.getElementById('lightbox').style.display = 'none';
            document.body.style.overflow = '';
        }
    });

    /* ═══════ COMPOSER ═══════ */
    var textarea = document.getElementById('postContent');
    textarea.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 240) + 'px';
    });

    /* Image upload preview */
    var mediaInput = document.getElementById('mediaInput');
    var composerPreview = document.getElementById('composerPreview');
    var previewImg = document.getElementById('previewImg');

    mediaInput.addEventListener('change', function () {
        if (this.files.length) {
            var r = new FileReader();
            r.onload = function (e) {
                previewImg.src = e.target.result;
                composerPreview.style.display = 'block';
            };
            r.readAsDataURL(this.files[0]);
        }
    });

    window.removePreview = function () {
        mediaInput.value = '';
        composerPreview.style.display = 'none';
    };

    window.insertEmoji = function (emoji) {
        var t = textarea;
        var start = t.selectionStart;
        t.value = t.value.slice(0, start) + emoji + t.value.slice(t.selectionEnd);
        t.selectionStart = t.selectionEnd = start + emoji.length;
        t.focus();
    };

    window.toggleTagInput = function () {
        var row = document.getElementById('cropTagRow');
        row.style.display = row.style.display === 'none' ? 'flex' : 'none';
        if (row.style.display === 'flex') document.getElementById('cropTagInput').focus();
    };

    window.applyCropTag = function () {
        var v = document.getElementById('cropTagInput').value.trim();
        if (!v) return;
        document.getElementById('cropTagField').value = v;
        textarea.value = textarea.value + (textarea.value ? ' ' : '') + '#' + v.replace(/\s+/g, '');
        document.getElementById('cropTagRow').style.display = 'none';
        document.getElementById('cropTagInput').value = '';
    };

    window.getLocation = function () {
        if (!navigator.geolocation) return toast('Geolocation not supported', 'error');
        var btn = document.getElementById('locBtn');
        btn.textContent = '⏳';
        navigator.geolocation.getCurrentPosition(function (pos) {
            document.getElementById('locationField').value =
                pos.coords.latitude.toFixed(4) + ',' + pos.coords.longitude.toFixed(4);
            btn.textContent = '📍✓';
            toast('Location added', 'success');
        }, function () {
            btn.textContent = '📍';
            toast('Location denied', 'error');
        });
    };

    /* Composer submit */
    document.getElementById('composerForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('btnPost');
        var content = textarea.value.trim();
        if (!content) { textarea.focus(); return; }

        btn.disabled = true;
        btn.innerHTML = '<span>Posting…</span>';

        var fd = new FormData(this);
        fetch(BASE + '/feed/create', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.success) {
                    prependPost(content, fd.get('type'));
                    textarea.value = '';
                    textarea.style.height = 'auto';
                    removePreview();
                    toast('✅ Post shared', 'success');
                } else {
                    toast('⚠️ ' + (d.error || 'Failed'), 'error');
                }
            })
            .catch(function () { toast('⚠️ Network error', 'error'); })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = '<span>Post</span><span class="arrow">→</span>';
            });
    });

    function prependPost(content, type) {
        var list = document.getElementById('postsList');
        var myName = '<?= addslashes(e($myName)) ?>';
        var myInit = '<?= addslashes($myInit) ?>';
        var meta = { tip:'💡 Tip', question:'❓ Question', market:'📈 Market', photo:'📷 Photo', event:'📅 Event' };
        var cls  = { tip:'ft-tip', question:'ft-question', market:'ft-market', photo:'ft-photo', event:'ft-event' };

        var safe = content.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                          .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                          .replace(/\n/g, '<br>');

        var el = document.createElement('article');
        el.className = 'glass-card post-card';
        el.setAttribute('data-type', type);
        el.style.animation = 'postIn .4s ease-out';
        el.innerHTML =
            '<header class="post-head">' +
                '<div class="post-avatar">' + myInit + '</div>' +
                '<div class="post-meta">' +
                    '<div class="post-author-row"><strong>' + myName + '</strong>' +
                    '<span class="role-pill">You</span></div>' +
                    '<div class="post-sub"><span>just now</span><span class="dot">·</span>' +
                    '<span class="type-badge ' + (cls[type]||'ft-default') + '">' + (meta[type]||'📝 Post') + '</span></div>' +
                '</div>' +
            '</header>' +
            '<div class="post-body">' + safe + '</div>' +
            '<div class="post-stats-row"><div class="stats-left"><span>Be the first to react</span></div></div>' +
            '<div class="post-actions">' +
                '<button type="button" class="act-btn like"><span class="act-icon">👍</span><span class="act-label">Like</span></button>' +
                '<button type="button" class="act-btn"><span class="act-icon">💬</span><span class="act-label">Comment</span></button>' +
                '<button type="button" class="act-btn"><span class="act-icon">🔄</span><span class="act-label">Share</span></button>' +
                '<button type="button" class="act-btn save"><span class="act-icon">🔖</span><span class="act-label">Save</span></button>' +
            '</div>';
        list.insertBefore(el, list.firstChild);
    }

    /* ═══════ FILTERS ═══════ */
    document.querySelectorAll('.ft-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.ft-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            applyFilters();
        });
    });

    document.querySelectorAll('.fb-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.fb-chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            applyFilters();
        });
    });

    function applyFilters() {
        var active = document.querySelector('.ft-tab.active');
        var sort   = document.querySelector('.fb-chip.active');
        var type   = active ? active.dataset.type : 'all';
        var order  = sort ? sort.dataset.sort : 'newest';

        var list = document.getElementById('postsList');
        var cards = Array.from(list.querySelectorAll('.post-card'));

        /* Filter */
        var visible = 0;
        cards.forEach(function (c) {
            var show = type === 'all' || type === 'saved'
                ? true
                : c.getAttribute('data-type') === type;
            c.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        /* Sort */
        cards.sort(function (a, b) {
            if (order === 'trending' || order === 'top') {
                return (parseInt(b.getAttribute('data-likes')) || 0) -
                       (parseInt(a.getAttribute('data-likes')) || 0);
            }
            return (parseInt(b.getAttribute('data-time')) || 0) -
                   (parseInt(a.getAttribute('data-time')) || 0);
        });
        cards.forEach(function (c) { list.appendChild(c); });

        var rc = document.getElementById('resultsCount');
        if (rc) rc.textContent = visible + ' post' + (visible === 1 ? '' : 's');
    }

    /* ═══════ LIKE / SAVE ═══════ */
    window.toggleLike = function (postId, btn) {
        btn.classList.toggle('active');
        var active = btn.classList.contains('active');
        btn.querySelector('.act-label').textContent = active ? 'Liked' : 'Like';

        var countEl = document.getElementById('reactCount-' + postId);
        if (countEl) {
            var cur = parseInt(countEl.textContent.replace(/[^\d]/g, ''), 10) || 0;
            cur = active ? cur + 1 : Math.max(0, cur - 1);
            countEl.textContent = cur.toLocaleString() + ' reactions';
        }

        var card = btn.closest('.post-card');
        if (card) {
            var cur2 = parseInt(card.getAttribute('data-likes')) || 0;
            card.setAttribute('data-likes', active ? cur2 + 1 : Math.max(0, cur2 - 1));
        }

        var fd = new URLSearchParams();
        fd.append('post_id', postId);
        fd.append('_csrf', CSRF);
        fetch(BASE + '/feed/like', { method: 'POST', body: fd.toString(), credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
            .catch(function () {});
    };

    window.toggleSave = function (postId, btn) {
        btn.classList.toggle('active');
        var active = btn.classList.contains('active');
        btn.querySelector('.act-label').textContent = active ? 'Saved' : 'Save';
        toast(active ? '🔖 Saved to bookmarks' : 'Removed from bookmarks', 'success');

        var fd = new URLSearchParams();
        fd.append('post_id', postId);
        fd.append('_csrf', CSRF);
        fetch(BASE + '/feed/save', { method: 'POST', body: fd.toString(), credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
            .catch(function () {});
    };

    window.deletePost = function (postId) {
        if (!confirm('Delete this post? This cannot be undone.')) return;
        var fd = new URLSearchParams();
        fd.append('post_id', postId);
        fd.append('_csrf', CSRF);
        fetch(BASE + '/feed/delete', { method: 'POST', body: fd.toString(), credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.success) {
                    var card = document.querySelector('.post-card[data-post-id="' + postId + '"]');
                    if (card) card.remove();
                    toast('🗑️ Post deleted', 'success');
                }
            });
    };

    /* ═══════ SHARE ═══════ */
    var currentShareId = null;
    window.openShareModal = function (postId) {
        currentShareId = postId;
        openModal('shareModal');
    };

    window.shareVia = function (channel) {
        var url = window.location.origin + BASE + '/feed/post/' + currentShareId;
        if (channel === 'copy') {
            navigator.clipboard.writeText(url).then(function () {
                toast('🔗 Link copied', 'success');
                closeModal('shareModal');
            });
        } else if (channel === 'whatsapp') {
            window.open('https://wa.me/?text=' + encodeURIComponent(url), '_blank');
            closeModal('shareModal');
        } else if (channel === 'sms') {
            window.location.href = 'sms:?body=' + encodeURIComponent(url);
            closeModal('shareModal');
        } else if (navigator.share) {
            navigator.share({ title: 'SAIP Post', url: url }).catch(function () {});
            closeModal('shareModal');
        }
    };

    /* ═══════ COMMENTS ═══════ */
    var currentCommentPost = null;
    window.openComments = function (postId) {
        currentCommentPost = postId;
        var body = document.getElementById('commentsBody');
        body.innerHTML = '<div class="comments-loading">Loading comments…</div>';
        openModal('commentsModal');

        fetch(BASE + '/feed/comments?post_id=' + postId, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success || !d.comments || !d.comments.length) {
                    body.innerHTML = '<div class="comments-loading">No comments yet. Be the first!</div>';
                    return;
                }
                body.innerHTML = d.comments.map(function (c) {
                    var init = (c.author || 'U').substring(0, 1).toUpperCase();
                    return '<div class="comment-row">' +
                        '<div class="avatar-xs">' + init + '</div>' +
                        '<div class="comment-main"><strong>' + escapeHtml(c.author) + '</strong>' +
                        '<p>' + escapeHtml(c.content) + '</p>' +
                        '<small>' + escapeHtml(c.time) + '</small></div></div>';
                }).join('');
            })
            .catch(function () {
                body.innerHTML = '<div class="comments-loading">Could not load comments.</div>';
            });
    };

    window.postComment = function () {
        var input = document.getElementById('commentInput');
        var content = input.value.trim();
        if (!content || !currentCommentPost) return;

        var fd = new URLSearchParams();
        fd.append('post_id', currentCommentPost);
        fd.append('content', content);
        fd.append('_csrf', CSRF);

        fetch(BASE + '/feed/comment', { method: 'POST', body: fd.toString(), credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.success) {
                    input.value = '';
                    openComments(currentCommentPost);
                    toast('💬 Comment posted', 'success');
                } else {
                    toast('⚠️ ' + (d.error || 'Failed'), 'error');
                }
            });
    };

    document.getElementById('commentInput').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); window.postComment(); }
    });

    /* ═══════ FOLLOW ═══════ */
    document.querySelectorAll('.btn-follow').forEach(function (btn) {
        btn.addEventListener('click', function () {
            this.classList.toggle('following');
            this.textContent = this.classList.contains('following') ? 'Following' : 'Follow';
            toast(this.classList.contains('following') ? '✅ Following' : 'Unfollowed', 'success');
        });
    });

    /* ═══════ LIGHTBOX ═══════ */
    window.openLightbox = function (src) {
        var lb = document.getElementById('lightbox');
        document.getElementById('lightboxImg').src = src;
        lb.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };
    window.closeLightbox = function () {
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = '';
    };

    /* ═══════ LOAD MORE (demo) ═══════ */
    document.getElementById('loadMore').addEventListener('click', function () {
        var btn = this;
        btn.innerHTML = '<span>Loading…</span>';
        setTimeout(function () {
            btn.innerHTML = '<span>No more posts yet</span>';
            btn.disabled = true;
            btn.style.opacity = '.5';
            btn.style.cursor = 'not-allowed';
        }, 800);
    });

    /* ═══════ POST MENU (⋯) ═══════ */
    var menu = null;
    window.openPostMenu = function (postId, e) {
        e.stopPropagation();
        if (menu) menu.remove();
        menu = document.createElement('div');
        menu.className = 'post-menu';
        menu.style.cssText = 'position:fixed;background:#1e293b;border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:6px;box-shadow:0 12px 32px rgba(0,0,0,.5);z-index:99999;min-width:180px';
        menu.innerHTML =
            '<button type="button" style="display:flex;gap:8px;align-items:center;width:100%;padding:9px 12px;background:transparent;border:none;color:#cbd5e1;font-size:13px;font-weight:600;cursor:pointer;border-radius:8px;text-align:left;font-family:inherit" onmouseover="this.style.background=\'rgba(255,255,255,.05)\'" onmouseout="this.style.background=\'transparent\'">🔗 Copy link</button>' +
            '<button type="button" style="display:flex;gap:8px;align-items:center;width:100%;padding:9px 12px;background:transparent;border:none;color:#cbd5e1;font-size:13px;font-weight:600;cursor:pointer;border-radius:8px;text-align:left;font-family:inherit" onmouseover="this.style.background=\'rgba(255,255,255,.05)\'" onmouseout="this.style.background=\'transparent\'">⚠️ Report</button>';
        menu.style.left = Math.min(e.pageX, window.innerWidth - 200) + 'px';
        menu.style.top = e.pageY + 'px';
        document.body.appendChild(menu);
        setTimeout(function () {
            document.addEventListener('click', function closeMenu() {
                if (menu) { menu.remove(); menu = null; }
                document.removeEventListener('click', closeMenu);
            }, { once: true });
        }, 10);
    };

    /* ═══════ HELPERS ═══════ */
    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
})();
</script>