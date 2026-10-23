<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Chat;

class FeedController extends Controller {

    private $chat;
    private $db;

    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        try {
            $this->chat = new Chat();
        } catch (\Throwable $e) {
            $this->chat = null;
        }

        try {
            $this->db = new Database();
        } catch (\Throwable $e) {
            $this->db = null;
        }
    }

    /* ═══════════════════════════════════════════════════════════
       MAIN FEED — GET /feed
       ═══════════════════════════════════════════════════════════ */
    public function index() {
        $userId = (int)$_SESSION['user_id'];

        /* ── Posts ── */
        $posts = [];
        try {
            $posts = $this->chat ? $this->chat->getFeed(30) : [];
        } catch (\Throwable $e) {
            $posts = [];
        }
        $posts = $this->enrichPosts($posts, $userId);

        /* ── Trending tags (top 5 from recent posts) ── */
        $trending = [];
        try {
            $this->db->query("
                SELECT tag, COUNT(*) AS count
                FROM (
                    SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(content, '#', -1), ' ', 1)) AS tag
                    FROM feed_posts
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                      AND content LIKE '%#%'
                ) t
                WHERE tag <> ''
                GROUP BY tag
                ORDER BY count DESC
                LIMIT 5
            ");
            $rows = $this->db->resultSet();
            if (is_array($rows)) {
                foreach ($rows as $r) {
                    $trending[] = [
                        'tag'      => $r['tag'],
                        'count'    => (int)$r['count'],
                        'category' => 'topic',
                    ];
                }
            }
        } catch (\Throwable $e) {}

        /* ── Top contributors (by post count this month) ── */
        $contributors = [];
        try {
            $this->db->query("
                SELECT u.id,
                       CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) AS name,
                       r.name AS role,
                       COUNT(p.id) AS points
                FROM users u
                LEFT JOIN roles r ON r.id = u.role_id
                LEFT JOIN feed_posts p ON p.user_id = u.id
                    AND p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                WHERE u.status = 'active'
                GROUP BY u.id, u.first_name, u.last_name, r.name
                HAVING points > 0
                ORDER BY points DESC
                LIMIT 4
            ");
            $rows = $this->db->resultSet();
            if (is_array($rows)) {
                foreach ($rows as $r) {
                    $contributors[] = [
                        'name'     => trim($r['name']),
                        'role'     => $r['role'] ?? 'Farmer',
                        'points'   => (int)$r['points'] * 120 + 500, // weighted score
                        'verified' => in_array($r['role'] ?? '', ['Agronomist','Administrator','Agricultural Officer'], true),
                    ];
                }
            }
        } catch (\Throwable $e) {}

        /* ── Suggested follows ── */
        $suggested = [];
        try {
            $this->db->query("
                SELECT u.id,
                       CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) AS name,
                       COALESCE(r.name, 'Farmer') AS role
                FROM users u
                LEFT JOIN roles r ON r.id = u.role_id
                WHERE u.id != :me
                  AND u.status = 'active'
                  AND u.role_id IN (2, 3, 4, 6)
                ORDER BY RAND()
                LIMIT 3
            ");
            $this->db->bind(':me', $userId);
            $rows = $this->db->resultSet();
            if (is_array($rows)) {
                foreach ($rows as $r) {
                    $name = trim($r['name']);
                    $suggested[] = [
                        'id'       => (int)$r['id'],
                        'name'     => $name,
                        'role'     => $r['role'],
                        'initials' => strtoupper(substr($name, 0, 1) . (strpos($name, ' ') ? substr($name, strpos($name, ' ') + 1, 1) : '')),
                    ];
                }
            }
        } catch (\Throwable $e) {}

        /* ── Crops for filter chips ── */
        $crops = [];
        try {
            $this->db->query("SELECT id, name, icon FROM crops WHERE is_active = 1 ORDER BY name LIMIT 8");
            $crops = $this->db->resultSet() ?: [];
        } catch (\Throwable $e) {}

        /* ── Weather (from session if set) ── */
        $weather = null;
        if (!empty($_SESSION['weather'])) {
            $weather = $_SESSION['weather'];
        }

        /* ── Market snapshot ── */
        $market = [];
        try {
            $this->db->query("
                SELECT crop, AVG(price_per_kg) AS price, 0 AS change
                FROM market_prices
                WHERE recorded_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY crop
                ORDER BY price DESC
                LIMIT 5
            ");
            $rows = $this->db->resultSet();
            if (is_array($rows)) {
                foreach ($rows as $r) {
                    $market[] = [
                        'icon'   => '🌾',
                        'crop'   => $r['crop'],
                        'price'  => (float)$r['price'],
                        'change' => 0,
                    ];
                }
            }
        } catch (\Throwable $e) {}

        /* ── Stats ── */
        $stats = ['posts'=>count($posts), 'members'=>0, 'today'=>0, 'active'=>0];
        try {
            $this->db->query("SELECT COUNT(*) AS c FROM users WHERE status = 'active'");
            $stats['members'] = (int)($this->db->single()['c'] ?? 0);

            $this->db->query("SELECT COUNT(*) AS c FROM feed_posts WHERE DATE(created_at) = CURDATE()");
            $stats['today'] = (int)($this->db->single()['c'] ?? 0);

            $this->db->query("SELECT COUNT(*) AS c FROM user_presence WHERE status = 'online' AND last_seen >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
            $stats['active'] = (int)($this->db->single()['c'] ?? 0);
        } catch (\Throwable $e) {}

        /* ── Render view ── */
        $viewFile = __DIR__ . '/../Views/feed/index.php';
        if (!file_exists($viewFile)) {
            // Fallback to old location
            $viewFile = __DIR__ . '/../Views/chat/feed.php';
        }

        if (file_exists($viewFile)) {
            parent::view(str_contains($viewFile, 'feed/index') ? 'feed/index' : 'chat/feed', [
                'title'        => 'Community Feed',
                'name'         => $_SESSION['name'] ?? 'User',
                'posts'        => $posts,
                'trending'     => $trending,
                'contributors' => $contributors,
                'suggested'    => $suggested,
                'weather'      => $weather,
                'market'       => $market,
                'crops'        => $crops,
                'stats'        => $stats,
            ]);
        } else {
            die('Feed view not found. Create app/Views/feed/index.php');
        }
    }

    /* ═══════════════════════════════════════════════════════════
       CREATE POST — POST /feed/create
       ═══════════════════════════════════════════════════════════ */
    public function create() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $userId  = (int)$_SESSION['user_id'];
            $content = trim($_POST['content'] ?? '');
            $type    = $_POST['type'] ?? 'tip';

            if ($content === '') {
                echo json_encode(['success'=>false, 'error'=>'Post content is empty']);
                exit;
            }

            /* Whitelist type */
            if (!in_array($type, ['tip','question','market','photo','event','text'], true)) {
                $type = 'tip';
            }

            /* ── Upload image if provided ── */
            $mediaUrl = null;
            if (!empty($_FILES['media']['name']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
                $up = \App\Core\Security::validateImage($_FILES['media'], 5);
                if (!$up['ok']) {
                    echo json_encode(['success'=>false, 'error'=>$up['error']]);
                    exit;
                }

                $dir = __DIR__ . '/../../public/uploads/feed/' . date('Y/m/');
                if (!is_dir($dir)) mkdir($dir, 0755, true);

                $ext = [
                    'image/jpeg'=>'jpg',
                    'image/png'=>'png',
                    'image/webp'=>'webp',
                    'image/gif'=>'gif',
                ][$up['mime']] ?? 'jpg';

                $fname = 'post_' . bin2hex(random_bytes(10)) . '.' . $ext;

                if (!move_uploaded_file($_FILES['media']['tmp_name'], $dir . $fname)) {
                    echo json_encode(['success'=>false, 'error'=>'Upload failed']);
                    exit;
                }

                $mediaUrl = BASE_URL . '/uploads/feed/' . date('Y/m/') . $fname;
            }

            /* ── Insert ── */
            $newId = null;

            // Try Chat model first (matches your existing pattern)
            if ($this->chat) {
                try {
                    $ok = $this->chat->createPost([
                        'user_id'  => $userId,
                        'type'     => $type,
                        'title'    => $_POST['title'] ?? null,
                        'content'  => mb_substr($content, 0, 4000),
                        'media_url'=> $mediaUrl,
                        'district_id' => $_POST['district_id'] ?? null,
                    ]);

                    if ($ok && $this->db) {
                        $newId = (int)$this->db->lastInsertId();
                    }
                } catch (\Throwable $e) {
                    $newId = null;
                }
            }

            // Fallback: direct insert
            if (!$newId && $this->db) {
                $this->db->query("
                    INSERT INTO feed_posts (user_id, post_type, content, media_url, created_at)
                    VALUES (:u, :t, :c, :m, NOW())
                ");
                $this->db->bind(':u', $userId);
                $this->db->bind(':t', $type);
                $this->db->bind(':c', mb_substr($content, 0, 4000));
                $this->db->bind(':m', $mediaUrl);
                $this->db->execute();
                $newId = (int)$this->db->lastInsertId();
            }

            if (!$newId) {
                echo json_encode(['success'=>false, 'error'=>'Could not save post']);
                exit;
            }

            echo json_encode([
                'success'  => true,
                'id'       => $newId,
                'message'  => 'Post published',
                'post'     => [
                    'id'         => $newId,
                    'content'    => $content,
                    'type'       => $type,
                    'media_url'  => $mediaUrl,
                    'created_at' => date('c'),
                ],
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       LIKE — POST /feed/like
       ═══════════════════════════════════════════════════════════ */
    public function like() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $postId = (int)($_POST['post_id'] ?? $_GET['post_id'] ?? 0);
            $userId = (int)$_SESSION['user_id'];

            if ($postId <= 0) {
                echo json_encode(['success'=>false, 'error'=>'Invalid post ID']);
                exit;
            }

            /* Ensure the post exists */
            $this->db->query("SELECT id FROM feed_posts WHERE id = :id LIMIT 1");
            $this->db->bind(':id', $postId);
            if (!$this->db->single()) {
                echo json_encode(['success'=>false, 'error'=>'Post not found']);
                exit;
            }

            /* Toggle */
            $this->db->query("SELECT id FROM feed_likes WHERE post_id = :p AND user_id = :u LIMIT 1");
            $this->db->bind(':p', $postId);
            $this->db->bind(':u', $userId);
            $existing = $this->db->single();

            $liked = false;
            if ($existing) {
                $this->db->query("DELETE FROM feed_likes WHERE id = :id");
                $this->db->bind(':id', (int)$existing['id']);
                $this->db->execute();
                $liked = false;
            } else {
                $this->db->query("INSERT INTO feed_likes (post_id, user_id, created_at) VALUES (:p, :u, NOW())");
                $this->db->bind(':p', $postId);
                $this->db->bind(':u', $userId);
                $this->db->execute();
                $liked = true;
            }

            /* Refresh counter */
            $this->db->query("SELECT COUNT(*) AS c FROM feed_likes WHERE post_id = :p");
            $this->db->bind(':p', $postId);
            $count = (int)($this->db->single()['c'] ?? 0);

            echo json_encode([
                'success' => true,
                'liked'   => $liked,
                'count'   => $count,
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       SAVE / BOOKMARK — POST /feed/save
       ═══════════════════════════════════════════════════════════ */
    public function save() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $postId = (int)($_POST['post_id'] ?? 0);
            $userId = (int)$_SESSION['user_id'];

            if ($postId <= 0) {
                echo json_encode(['success'=>false, 'error'=>'Invalid post ID']);
                exit;
            }

            /* Ensure table exists (auto-create if missing) */
            try {
                $this->db->query("
                    CREATE TABLE IF NOT EXISTS feed_saves (
                        id         INT AUTO_INCREMENT PRIMARY KEY,
                        post_id    INT NOT NULL,
                        user_id    INT NOT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY uq_save (post_id, user_id)
                    )
                ");
                $this->db->execute();
            } catch (\Throwable $e) { /* ignore */ }

            /* Toggle */
            $this->db->query("SELECT id FROM feed_saves WHERE post_id = :p AND user_id = :u LIMIT 1");
            $this->db->bind(':p', $postId);
            $this->db->bind(':u', $userId);
            $existing = $this->db->single();

            $saved = false;
            if ($existing) {
                $this->db->query("DELETE FROM feed_saves WHERE id = :id");
                $this->db->bind(':id', (int)$existing['id']);
                $this->db->execute();
                $saved = false;
            } else {
                $this->db->query("INSERT INTO feed_saves (post_id, user_id) VALUES (:p, :u)");
                $this->db->bind(':p', $postId);
                $this->db->bind(':u', $userId);
                $this->db->execute();
                $saved = true;
            }

            echo json_encode(['success'=>true, 'saved'=>$saved]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       DELETE — POST /feed/delete
       ═══════════════════════════════════════════════════════════ */
    public function delete() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $postId = (int)($_POST['post_id'] ?? 0);
            $userId = (int)$_SESSION['user_id'];

            if ($postId <= 0) {
                echo json_encode(['success'=>false, 'error'=>'Invalid post ID']);
                exit;
            }

            /* Ownership check: only author can delete */
            $this->db->query("SELECT user_id, media_url FROM feed_posts WHERE id = :id LIMIT 1");
            $this->db->bind(':id', $postId);
            $row = $this->db->single();

            if (!$row) {
                echo json_encode(['success'=>false, 'error'=>'Post not found']);
                exit;
            }

            if ((int)$row['user_id'] !== $userId && (int)($_SESSION['role_id'] ?? 0) !== 7) {
                echo json_encode(['success'=>false, 'error'=>'Not authorized']);
                exit;
            }

            /* Delete associated image file */
            if (!empty($row['media_url'])) {
                $path = __DIR__ . '/../../public' . parse_url($row['media_url'], PHP_URL_PATH);
                if (is_file($path)) @unlink($path);
            }

            /* Delete post + likes + comments + saves */
            $this->db->query("DELETE FROM feed_posts WHERE id = :id");
            $this->db->bind(':id', $postId);
            $this->db->execute();

            foreach (['feed_likes','feed_saves','feed_comments'] as $tbl) {
                try {
                    $this->db->query("DELETE FROM $tbl WHERE post_id = :p");
                    $this->db->bind(':p', $postId);
                    $this->db->execute();
                } catch (\Throwable $e) { /* table may not exist */ }
            }

            echo json_encode(['success'=>true, 'message'=>'Post deleted']);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       COMMENTS — GET /feed/comments?post_id=N
       ═══════════════════════════════════════════════════════════ */
    public function comments() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $postId = (int)($_GET['post_id'] ?? 0);
            if ($postId <= 0) {
                echo json_encode(['success'=>false, 'comments'=>[]]);
                exit;
            }

            /* Auto-create table if missing */
            try {
                $this->db->query("
                    CREATE TABLE IF NOT EXISTS feed_comments (
                        id         INT AUTO_INCREMENT PRIMARY KEY,
                        post_id    INT NOT NULL,
                        user_id    INT NOT NULL,
                        content    TEXT NOT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        INDEX (post_id, created_at)
                    )
                ");
                $this->db->execute();
            } catch (\Throwable $e) { /* ignore */ }

            $this->db->query("
                SELECT c.id, c.content, c.created_at,
                       CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) AS author
                FROM feed_comments c
                JOIN users u ON u.id = c.user_id
                WHERE c.post_id = :p
                ORDER BY c.created_at ASC
                LIMIT 50
            ");
            $this->db->bind(':p', $postId);
            $rows = $this->db->resultSet() ?: [];

            $comments = [];
            foreach ($rows as $r) {
                $comments[] = [
                    'id'      => (int)$r['id'],
                    'author'  => trim($r['author']),
                    'content' => $r['content'],
                    'time'    => date('M d, H:i', strtotime($r['created_at'])),
                ];
            }

            echo json_encode(['success'=>true, 'comments'=>$comments]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false, 'comments'=>[], 'error'=>$e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       POST COMMENT — POST /feed/comment
       ═══════════════════════════════════════════════════════════ */
    public function comment() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $postId  = (int)($_POST['post_id'] ?? 0);
            $userId  = (int)$_SESSION['user_id'];
            $content = trim($_POST['content'] ?? '');

            if ($postId <= 0 || $content === '') {
                echo json_encode(['success'=>false, 'error'=>'Missing content']);
                exit;
            }

            /* Auto-create table if missing */
            try {
                $this->db->query("
                    CREATE TABLE IF NOT EXISTS feed_comments (
                        id         INT AUTO_INCREMENT PRIMARY KEY,
                        post_id    INT NOT NULL,
                        user_id    INT NOT NULL,
                        content    TEXT NOT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        INDEX (post_id, created_at)
                    )
                ");
                $this->db->execute();
            } catch (\Throwable $e) { /* ignore */ }

            $this->db->query("
                INSERT INTO feed_comments (post_id, user_id, content, created_at)
                VALUES (:p, :u, :c, NOW())
            ");
            $this->db->bind(':p', $postId);
            $this->db->bind(':u', $userId);
            $this->db->bind(':c', mb_substr($content, 0, 1000));
            $this->db->execute();

            echo json_encode([
                'success' => true,
                'id'      => (int)$this->db->lastInsertId(),
                'message' => 'Comment posted',
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       SHARE — POST /feed/share
       ═══════════════════════════════════════════════════════════ */
    public function share() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $postId = (int)($_POST['post_id'] ?? 0);
            if ($postId <= 0) {
                echo json_encode(['success'=>false, 'error'=>'Invalid post']);
                exit;
            }
            echo json_encode([
                'success' => true,
                'url'     => BASE_URL . '/feed/post/' . $postId,
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       SINGLE POST — GET /feed/post/{id}
       ═══════════════════════════════════════════════════════════ */
    public function post($id = null) {
        $id = (int)$id;
        if ($id <= 0) {
            header("Location: " . BASE_URL . "/feed");
            exit;
        }

        /* Increment view counter (best effort) */
        try {
            $this->db->query("UPDATE feed_posts SET views = COALESCE(views, 0) + 1 WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();
        } catch (\Throwable $e) { /* column may not exist */ }

        header("Location: " . BASE_URL . "/feed?focus=" . $id);
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       SEARCH — GET /feed/search?q=...
       ═══════════════════════════════════════════════════════════ */
    public function search() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $q = trim($_GET['q'] ?? '');
            if (mb_strlen($q) < 2) {
                echo json_encode(['success'=>true, 'posts'=>[]]);
                exit;
            }

            $this->db->query("
                SELECT p.id, p.content, p.post_type, p.created_at,
                       CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) AS author
                FROM feed_posts p
                JOIN users u ON u.id = p.user_id
                WHERE p.content LIKE :q
                ORDER BY p.created_at DESC
                LIMIT 20
            ");
            $this->db->bind(':q', '%' . $q . '%');
            $rows = $this->db->resultSet() ?: [];

            $posts = [];
            foreach ($rows as $r) {
                $posts[] = [
                    'id'      => (int)$r['id'],
                    'author'  => trim($r['author']),
                    'content' => mb_substr($r['content'], 0, 120),
                    'type'    => $r['post_type'],
                    'time'    => date('M d', strtotime($r['created_at'])),
                ];
            }

            echo json_encode(['success'=>true, 'posts'=>$posts]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false, 'posts'=>[], 'error'=>$e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       HELPERS
       ═══════════════════════════════════════════════════════════ */

    /**
     * Normalize raw posts from Chat::getFeed() into the shape
     * the view expects (with `author`, `reactions`, `liked_by_me`, etc.)
     */
    private function enrichPosts(array $rows, int $userId): array {
        if (empty($rows)) return [];

        /* Fetch all liked post IDs for this user in one query */
        $likedIds = [];
        $savedIds = [];
        try {
            $this->db->query("SELECT post_id FROM feed_likes WHERE user_id = :u");
            $this->db->bind(':u', $userId);
            $rowsL = $this->db->resultSet() ?: [];
            foreach ($rowsL as $r) $likedIds[(int)$r['post_id']] = true;

            $this->db->query("SELECT post_id FROM feed_saves WHERE user_id = :u");
            $this->db->bind(':u', $userId);
            $rowsS = $this->db->resultSet() ?: [];
            foreach ($rowsS as $r) $savedIds[(int)$r['post_id']] = true;
        } catch (\Throwable $e) { /* tables might not exist yet */ }

        $out = [];
        foreach ($rows as $r) {
            $author   = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
            $initials = strtoupper(substr($author ?: 'U', 0, 1));
            if (strpos($author, ' ') !== false) {
                $p = explode(' ', $author);
                $initials = strtoupper(substr($p[0], 0, 1) . substr(end($p), 0, 1));
            }

            $pid = (int)$r['id'];
            $likeCount = (int)($r['likes_count'] ?? 0);

            $out[] = [
                'id'          => $pid,
                'author_id'   => (int)($r['user_id'] ?? 0),
                'author'      => [
                    'name'     => $author ?: 'Farmer',
                    'initials' => $initials,
                    'role'     => $r['role_label'] ?? 'Farmer',
                    'location' => $r['district_name'] ?? null,
                    'verified' => !empty($r['badge_type']),
                ],
                'type'        => $r['post_type'] ?? 'tip',
                'time'        => $this->relativeTime($r['created_at'] ?? 'now'),
                'content'     => $r['content'] ?? '',
                'image_url'   => $r['media_url'] ?? null,
                'tags'        => $this->extractTags($r['content'] ?? ''),
                'reactions'   => ['like'=>$likeCount, 'love'=>0, 'insightful'=>0],
                'comments'    => (int)($r['comments_count'] ?? 0),
                'shares'      => 0,
                'views'       => (int)($r['views'] ?? 0),
                'liked_by_me' => isset($likedIds[$pid]),
                'saved_by_me' => isset($savedIds[$pid]),
            ];
        }
        return $out;
    }

    /**
     * Convert a timestamp into "12 min ago", "1 hour ago", etc.
     */
    private function relativeTime(string $ts): string {
        $t = strtotime($ts);
        if (!$t) return 'now';
        $diff = time() - $t;
        if ($diff < 60)     return 'just now';
        if ($diff < 3600)   return floor($diff / 60) . ' min ago';
        if ($diff < 86400)  return floor($diff / 3600) . ' hour' . (floor($diff / 3600) === 1 ? '' : 's') . ' ago';
        if ($diff < 604800) return floor($diff / 86400) . ' day' . (floor($diff / 86400) === 1 ? '' : 's') . ' ago';
        return date('M d, Y', $t);
    }

    /**
     * Extract hashtags from post content.
     */
    private function extractTags(string $content): array {
        if (preg_match_all('/#(\w+)/u', $content, $m)) {
            return array_map(fn($t) => '#' . $t, array_slice($m[1], 0, 5));
        }
        return [];
    }
}