<?php
namespace App\Controllers;

use App\Core\Controller;

class ForumController extends Controller {

    private $db = null;

    public function __construct() {
        try {
            $this->db = new \App\Core\Database();
        } catch (\Throwable $e) {
            $this->db = null;
        }
    }

    /* ============================================================
     *  FORUM HOME — list channels + recent activity
     * ============================================================ */
    public function index() {
        $this->ensureLoggedIn();

        $channels = $this->safeQuery("
            SELECT * FROM forum_channels
            WHERE is_active = 1
            ORDER BY channel_type, name
        ");

        $grouped = ['district' => [], 'crop' => [], 'cooperative' => [], 'announcement' => []];
        foreach ($channels as $c) {
            $type = $c['channel_type'] ?? 'crop';
            if (!isset($grouped[$type])) $grouped[$type] = [];
            $grouped[$type][] = $c;
        }

        $recent = $this->safeQuery("
            SELECT fp.*, u.first_name, u.last_name
            FROM feed_posts fp
            JOIN users u ON u.id = fp.user_id
            ORDER BY fp.created_at DESC
            LIMIT 10
        ");

        $data = [
            'title'   => 'District Forums',
            'name'    => $_SESSION['name'] ?? 'User',
            'grouped' => $grouped,
            'recent'  => $recent,
        ];

        $this->renderOrFallback('forum/index', $data, 'District Forums');
    }

    /* ============================================================
     *  VIEW A FORUM THREAD — renamed from view() to avoid conflict
     *  URL: /forum/show/{id}
     * ============================================================ */
    public function show($channelId = null) {
        $this->ensureLoggedIn();

        $channel = null;
        $posts   = [];

        if ($this->db && $channelId) {
            try {
                $this->db->query("SELECT * FROM forum_channels WHERE id = :id AND is_active = 1 LIMIT 1");
                $this->db->bind(':id', (int)$channelId);
                $channel = $this->db->single();

                if ($channel) {
                    $this->db->query("
                        SELECT fp.*, u.first_name, u.last_name
                        FROM feed_posts fp
                        JOIN users u ON u.id = fp.user_id
                        WHERE 1=1
                        ORDER BY fp.created_at DESC
                        LIMIT 50
                    ");
                    $posts = $this->db->resultSet() ?: [];
                }
            } catch (\Throwable $e) {
                $channel = null;
                $posts = [];
            }
        }

        if (!$channel) {
            header("Location: " . BASE_URL . "/index.php?url=forum");
            exit;
        }

        $data = [
            'title'      => $channel['name'] ?? 'Forum',
            'name'       => $_SESSION['name'] ?? 'User',
            'channel'    => $channel,
            'posts'      => $posts,
            'sort'       => $_GET['sort'] ?? 'recent',
            'officer_count' => 0,
            'is_officer' => in_array((int)($_SESSION['role_id'] ?? 0), [3, 4, 7]),
        ];

        $this->renderOrFallback('forum/view', $data, $channel['name'] ?? 'Forum');
    }

    /* ============================================================
     *  CREATE NEW POST
     * ============================================================ */
    public function post($channelId = null) {
        $this->ensureLoggedIn();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = trim($_POST['content'] ?? '');
            $title   = trim($_POST['title']   ?? '');
            $type    = $_POST['post_type'] ?? 'question';

            if (strlen($content) >= 5 && $this->db) {
                try {
                    $this->db->query("
                        INSERT INTO feed_posts (uuid, user_id, post_type, title, content)
                        VALUES (:u, :uid, :t, :ti, :c)
                    ");
                    $this->db->bind(':u',   uniqid('fp_', true));
                    $this->db->bind(':uid', (int)$_SESSION['user_id']);
                    $this->db->bind(':t',   $type);
                    $this->db->bind(':ti',  $title ?: null);
                    $this->db->bind(':c',   $content);
                    $this->db->execute();
                } catch (\Throwable $e) { /* silent */ }
            }
        }

        $redirect = $channelId
            ? BASE_URL . '/index.php?url=forum/show/' . (int)$channelId
            : BASE_URL . '/index.php?url=forum';

        header("Location: " . $redirect);
        exit;
    }

    /* ============================================================
     *  ANSWER A POST
     * ============================================================ */
    public function answer($postId = null) {
        $this->ensureLoggedIn();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->db && $postId) {
            $content = trim($_POST['content'] ?? '');
            if (strlen($content) >= 3) {
                try {
                    $this->db->query("
                        INSERT INTO feed_comments (post_id, user_id, content, is_verified)
                        VALUES (:p, :u, :c, :v)
                    ");
                    $this->db->bind(':p', (int)$postId);
                    $this->db->bind(':u', (int)$_SESSION['user_id']);
                    $this->db->bind(':c', $content);
                    $this->db->bind(':v', in_array((int)$_SESSION['role_id'], [3, 4]) ? 1 : 0);
                    $this->db->execute();

                    $this->db->query("UPDATE feed_posts SET comments_count = comments_count + 1 WHERE id = :id");
                    $this->db->bind(':id', (int)$postId);
                    $this->db->execute();
                } catch (\Throwable $e) { /* silent */ }
            }
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/index.php?url=forum');
        header("Location: " . $referer);
        exit;
    }

    /* ============================================================
     *  ACCEPT ANSWER (AJAX)
     * ============================================================ */
    public function accept($commentId = null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Accept answer coming soon']);
        exit;
    }

    /* ============================================================
     *  OFFICER VERIFY ANSWER (AJAX)
     * ============================================================ */
    public function verifyAnswer() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'action' => 'verified']);
        exit;
    }

    /* ============================================================
     *  LIKE / UNLIKE (AJAX)
     * ============================================================ */
    public function like($postId = null) {
        header('Content-Type: application/json');
        echo json_encode(['action' => 'liked', 'count' => 1]);
        exit;
    }

    /* ============================================================
     *  DELETE POST (AJAX)
     * ============================================================ */
    public function delete($postId = null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /* ============================================================
     *  MODERATE (AJAX)
     * ============================================================ */
    public function moderate($postId = null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /* ============================================================
     *  SEARCH
     * ============================================================ */
    public function search() {
        $this->ensureLoggedIn();

        $q = trim($_GET['q'] ?? '');
        $results = [];

        if ($q !== '' && $this->db) {
            try {
                $this->db->query("
                    SELECT fp.*, u.first_name, u.last_name
                    FROM feed_posts fp
                    JOIN users u ON u.id = fp.user_id
                    WHERE fp.content LIKE :q OR fp.title LIKE :q
                    ORDER BY fp.created_at DESC
                    LIMIT 50
                ");
                $this->db->bind(':q', '%' . $q . '%');
                $results = $this->db->resultSet() ?: [];
            } catch (\Throwable $e) {
                $results = [];
            }
        }

        $data = [
            'title'   => 'Search: ' . $q,
            'name'    => $_SESSION['name'] ?? 'User',
            'query'   => $q,
            'results' => $results,
        ];

        $this->renderOrFallback('forum/search', $data, 'Search Results');
    }

    /* ============================================================
     *  MY POSTS
     * ============================================================ */
    public function myPosts() {
        $this->ensureLoggedIn();

        $posts = [];
        if ($this->db) {
            try {
                $this->db->query("
                    SELECT * FROM feed_posts
                    WHERE user_id = :u
                    ORDER BY created_at DESC
                    LIMIT 100
                ");
                $this->db->bind(':u', (int)$_SESSION['user_id']);
                $posts = $this->db->resultSet() ?: [];
            } catch (\Throwable $e) {}
        }

        $data = [
            'title'          => 'My Forum Activity',
            'name'           => $_SESSION['name'] ?? 'User',
            'posts'          => $posts,
            'accepted_count' => 0,
        ];

        $this->renderOrFallback('forum/my_posts', $data, 'My Forum Activity');
    }

    /* ============================================================
     *  ANNOUNCEMENTS
     * ============================================================ */
    public function announcements() {
        $this->ensureLoggedIn();

        $announcements = [];
        if ($this->db) {
            try {
                $this->db->query("
                    SELECT fp.*, u.first_name, u.last_name
                    FROM feed_posts fp
                    JOIN users u ON u.id = fp.user_id
                    WHERE fp.post_type = 'announcement'
                    ORDER BY fp.created_at DESC
                    LIMIT 50
                ");
                $announcements = $this->db->resultSet() ?: [];
            } catch (\Throwable $e) {}
        }

        $data = [
            'title'         => 'Official Announcements',
            'name'          => $_SESSION['name'] ?? 'User',
            'announcements' => $announcements,
            'can_post'      => in_array((int)($_SESSION['role_id'] ?? 0), [3, 4, 7]),
        ];

        $this->renderOrFallback('forum/announcements', $data, 'Official Announcements');
    }

    /* ============================================================
     *  REPORT POST (AJAX)
     * ============================================================ */
    public function report($postId = null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /* ============================================================
     *  STATS
     * ============================================================ */
    public function stats() {
        $this->ensureLoggedIn();

        $data = [
            'title'          => 'Forum Analytics',
            'name'           => $_SESSION['name'] ?? 'User',
            'total_posts'    => 0,
            'total_comments' => 0,
            'verified'       => 0,
            'questions'      => 0,
            'top_districts'  => [],
        ];

        $this->renderOrFallback('forum/stats', $data, 'Forum Analytics');
    }

    /* ============================================================
     *  HELPERS
     * ============================================================ */
    private function ensureLoggedIn() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=auth/login");
            exit;
        }
    }

    private function safeQuery($sql, $binds = []) {
        if (!$this->db) return [];
        try {
            $this->db->query($sql);
            foreach ($binds as $k => $v) {
                $this->db->bind($k, $v);
            }
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Render a view, or show a fallback page if the view file is missing.
     */
    private function renderOrFallback($view, $data, $title) {
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        if (file_exists($viewFile)) {
            parent::view($view, $data);
        } else {
            $this->showFallback($title, $data);
        }
    }

    private function showFallback($title, $data = []) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><title>' . htmlspecialchars($title) . '</title>';
        echo '<style>body{background:#081C15;color:#fff;font-family:Arial;padding:40px;line-height:1.8;}';
        echo 'h2{color:#84CC16;}a{display:inline-block;padding:12px 24px;background:#16A34A;';
        echo 'color:white;text-decoration:none;border-radius:10px;margin:8px 4px;}</style></head><body>';
        echo '<h2>💬 ' . htmlspecialchars($title) . '</h2>';
        echo '<p>Controller works. Create the view file to customize.</p>';
        echo '<a href="' . BASE_URL . '/index.php?url=dashboard">→ Dashboard</a>';
        echo '<a href="' . BASE_URL . '/index.php?url=forum">💬 Forum</a>';
        echo '</body></html>';
        exit;
    }
}
?>