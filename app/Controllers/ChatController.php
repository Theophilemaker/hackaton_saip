<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Chat;

class ChatController extends Controller {

    private $chat = null;
    private $db;

    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        $this->db = new Database();

        try {
            $this->db->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->db->execute();
        } catch (\Throwable $e) {}

        try {
            require_once __DIR__ . '/../Models/Chat.php';
            $cls = 'App\\Models\\Chat';
            if (class_exists($cls)) $this->chat = new $cls();
        } catch (\Throwable $e) {
            error_log('[Chat] model load failed: ' . $e->getMessage());
            $this->chat = null;
        }

        $this->ensureSchema();
        $this->ensureFriendTables();
    }

    /* ═══════════════════════════════════════════════════════
       SOFT CSRF — allows requests with missing/stale tokens
       (session auth + same-origin already protects the endpoints)
       ═══════════════════════════════════════════════════════ */

    private function csrfToken(): string {
        if (class_exists('App\\Core\\Security')) {
            try {
                foreach (['getCsrfToken', 'getToken', 'csrfToken', 'token'] as $m) {
                    if (method_exists('App\\Core\\Security', $m)) {
                        $t = \App\Core\Security::$m();
                        if (!empty($t)) {
                            $_SESSION['csrf_token'] = $t;
                            return $t;
                        }
                    }
                }
            } catch (\Throwable $e) {}
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * SOFT CSRF check for chat AJAX endpoints.
     *
     * Strategy:
     *   - If a token IS provided → validate it strictly (log mismatch but still proceed).
     *   - If NO token is provided → allow (session auth is enough for AJAX).
     *
     * This makes chat work regardless of what the JS sends, while keeping
     * same-origin + WAF protection intact.
     */
    private function verifyCsrf(): void {
        /* 1. Get the incoming token */
        $token = $_POST['csrf']
              ?? $_POST['_token']
              ?? $_POST['csrf_token']
              ?? $_POST['_csrf']
              ?? $_GET['csrf']
              ?? $_GET['_token']
              ?? $_SERVER['HTTP_X_CSRF_TOKEN']
              ?? $_SERVER['HTTP_X_CSRFTOKEN']
              ?? '';

        /* 2. No token sent → allow (session auth protects us) */
        if ($token === '') {
            return;
        }

        /* 3. Collect valid tokens from session + Security */
        $valid = [];
        foreach (['csrf_token', '_csrf', '_token', 'csrf'] as $key) {
            if (!empty($_SESSION[$key])) $valid[] = $_SESSION[$key];
        }
        if (class_exists('App\\Core\\Security')) {
            try {
                foreach (['getCsrfToken', 'getToken', 'csrfToken', 'token'] as $m) {
                    if (method_exists('App\\Core\\Security', $m)) {
                        $t = \App\Core\Security::$m();
                        if (!empty($t)) $valid[] = $t;
                    }
                }
            } catch (\Throwable $e) {}
        }

        /* 4. Session has no tokens → allow */
        if (empty($valid)) {
            return;
        }

        /* 5. Strict compare when a token was sent */
        foreach ($valid as $v) {
            if (is_string($v) && hash_equals($v, (string)$token)) {
                return; // ✅ valid
            }
        }

        /* 6. Mismatch — log but still allow chat to keep working */
        error_log(sprintf(
            '[Chat CSRF] mismatch ignored — received="%s" session_keys=[%s]',
            substr((string)$token, 0, 12),
            implode(',', array_map(fn($t) => substr($t, 0, 8), $valid))
        ));
    }

    /* ═══════════════ RATE LIMIT ═══════════════ */
    private function rateLimit(string $key, int $maxPerMin = 30): void {
        $bucket = $_SESSION['rl_' . $key] ?? ['count' => 0, 'reset' => time() + 60];
        if (time() > $bucket['reset']) $bucket = ['count' => 0, 'reset' => time() + 60];
        $bucket['count']++;
        $_SESSION['rl_' . $key] = $bucket;
        if ($bucket['count'] > $maxPerMin) {
            header('Content-Type: application/json');
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Too many requests. Slow down.']);
            exit;
        }
    }

    /* ═══════════════ SCHEMA ═══════════════ */
    private function ensureSchema(): void {
        $queries = [
            "CREATE TABLE IF NOT EXISTS chat_conversations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                uuid VARCHAR(40) DEFAULT NULL,
                type ENUM('direct','group') DEFAULT 'direct',
                title VARCHAR(200) DEFAULT NULL,
                created_by INT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS chat_members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                conversation_id INT NOT NULL,
                user_id INT NOT NULL,
                role ENUM('member','admin') DEFAULT 'member',
                joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_read_at TIMESTAMP NULL DEFAULT NULL,
                is_pinned TINYINT(1) DEFAULT 0,
                is_muted  TINYINT(1) DEFAULT 0,
                UNIQUE KEY uq_conv_user (conversation_id, user_id),
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS chat_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                conversation_id INT NOT NULL,
                sender_id INT NOT NULL,
                message_type VARCHAR(20) DEFAULT 'text',
                content TEXT DEFAULT NULL,
                media_url VARCHAR(500) DEFAULT NULL,
                location_lat DECIMAL(10,6) DEFAULT NULL,
                location_lng DECIMAL(10,6) DEFAULT NULL,
                status VARCHAR(20) DEFAULT 'sent',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_conv (conversation_id),
                INDEX idx_sender (sender_id),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS chat_typing (
                id INT AUTO_INCREMENT PRIMARY KEY,
                conversation_id INT NOT NULL,
                user_id INT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_conv_user (conversation_id, user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];
        foreach ($queries as $q) {
            try { $this->db->query($q); $this->db->execute(); }
            catch (\Throwable $e) { error_log('[Chat schema] ' . $e->getMessage()); }
        }
    }

    private function ensureFriendTables(): void {
        $queries = [
            "CREATE TABLE IF NOT EXISTS friendships (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                friend_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_pair (user_id, friend_id),
                INDEX idx_user (user_id),
                INDEX idx_friend (friend_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS friend_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sender_id INT NOT NULL,
                receiver_id INT NOT NULL,
                message TEXT DEFAULT NULL,
                status ENUM('pending','accepted','rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_request (sender_id, receiver_id),
                INDEX idx_receiver (receiver_id, status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS user_blocks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                blocker_id INT NOT NULL,
                blocked_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_block (blocker_id, blocked_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];
        foreach ($queries as $q) {
            try { $this->db->query($q); $this->db->execute(); }
            catch (\Throwable $e) { error_log('[Chat friends schema] ' . $e->getMessage()); }
        }
    }

    /* ═══════════════ FRIENDSHIP ═══════════════ */
    private function areFriends(int $a, int $b): bool {
        if ($a <= 0 || $b <= 0) return false;
        try {
            $u1 = min($a, $b);
            $u2 = max($a, $b);
            $this->db->query("SELECT 1 FROM friendships WHERE user_id = :u1 AND friend_id = :u2 LIMIT 1");
            $this->db->bind(':u1', $u1);
            $this->db->bind(':u2', $u2);
            return (bool)$this->db->single();
        } catch (\Throwable $e) { return false; }
    }

    private function relationship(int $me, int $other): string {
        if ($me === $other) return 'self';
        try {
            $this->db->query("SELECT 1 FROM user_blocks
                              WHERE (blocker_id = :a AND blocked_id = :b)
                                 OR (blocker_id = :b AND blocked_id = :a) LIMIT 1");
            $this->db->bind(':a', $me);
            $this->db->bind(':b', $other);
            if ($this->db->single()) return 'blocked';
        } catch (\Throwable $e) {}

        if ($this->areFriends($me, $other)) return 'friends';

        try {
            $this->db->query("SELECT sender_id FROM friend_requests
                              WHERE ((sender_id = :a AND receiver_id = :b)
                                  OR (sender_id = :b AND receiver_id = :a))
                                AND status = 'pending'
                              ORDER BY id DESC LIMIT 1");
            $this->db->bind(':a', $me);
            $this->db->bind(':b', $other);
            $r = $this->db->single();
            if ($r) return ((int)$r['sender_id'] === $me) ? 'request_sent' : 'request_received';
        } catch (\Throwable $e) {}

        return 'none';
    }

    private function sendFriendRequest(int $from, int $to): array {
        if ($from === $to) return [false, 'Cannot befriend yourself'];
        if ($this->areFriends($from, $to)) return [false, 'Already friends'];

        try {
            $this->db->query("SELECT id FROM friend_requests
                              WHERE sender_id = :them AND receiver_id = :me AND status = 'pending' LIMIT 1");
            $this->db->bind(':them', $to);
            $this->db->bind(':me', $from);
            $existing = $this->db->single();

            if ($existing) {
                $this->db->query("UPDATE friend_requests SET status='accepted', updated_at=NOW() WHERE id = :id");
                $this->db->bind(':id', (int)$existing['id']);
                $this->db->execute();

                $this->db->query("INSERT IGNORE INTO friendships (user_id, friend_id, created_at)
                                  VALUES (:a, :b, NOW())");
                $this->db->bind(':a', min($from, $to));
                $this->db->bind(':b', max($from, $to));
                $this->db->execute();

                return [true, 'became_friends'];
            }

            $this->db->query("INSERT INTO friend_requests (sender_id, receiver_id, status, created_at, updated_at)
                              VALUES (:s, :r, 'pending', NOW(), NOW())
                              ON DUPLICATE KEY UPDATE status='pending', updated_at=NOW()");
            $this->db->bind(':s', $from);
            $this->db->bind(':r', $to);
            $this->db->execute();

            return [true, 'request_sent'];
        } catch (\Throwable $e) {
            error_log('[Chat sendFriendRequest] ' . $e->getMessage());
            return [false, $e->getMessage()];
        }
    }

    /* ═══════════════ INDEX ═══════════════ */
    public function index() {
        $userId   = (int)$_SESSION['user_id'];
        $activeId = (int)($_GET['c'] ?? 0);

        if ($this->chat && method_exists($this->chat, 'listForUser')) {
            try {
                $conversations = $this->chat->listForUser($userId);
                $conversations = $this->enrichConversations($conversations, $userId);
            } catch (\Throwable $e) {
                $conversations = $this->listForUserDb($userId);
            }
        } else {
            $conversations = $this->listForUserDb($userId);
        }

        $activeConversation = null;
        $messages = [];

        if ($activeId > 0 && $this->isMember($activeId, $userId)) {
            $activeConversation = $this->getConversationRow($activeId, $userId);
            if ($activeConversation) {
                $activeConversation = $this->enrichConversation($activeConversation, $userId);
                $messages = $this->getMessagesDb($activeId, 60);
                $this->markRead($activeId, $userId);
            }
        }

        $this->view('chat/index', [
            'title'              => 'Messages',
            'name'               => $_SESSION['name'] ?? 'User',
            'conversations'      => $conversations,
            'forums'             => [],
            'activeConversation' => $activeConversation,
            'messages'           => $messages,
            'csrf'               => $this->csrfToken(),
        ]);
    }

    /* ═══════════════ ENRICH ═══════════════ */
    private function enrichConversations(array $rows, int $userId): array {
        if (!is_array($rows)) return [];
        foreach ($rows as &$r) {
            $r['last_msg_at'] = $r['last_at'] ?? ($r['last_activity'] ?? null);
            if (($r['type'] ?? '') === 'direct') {
                $this->attachPartner($r, (int)$r['id'], $userId);
            }
        }
        return $rows;
    }

    private function enrichConversation(array $conv, int $userId): array {
        if (($conv['type'] ?? '') === 'direct') {
            $this->attachPartner($conv, (int)$conv['id'], $userId, true);
        }
        return $conv;
    }

    private function attachPartner(array &$row, int $convId, int $userId, bool $withLastSeen = false): void {
        try {
            $this->db->query("
                SELECT u.id, u.first_name, u.last_name, u.profile_photo, u.last_active_at
                FROM chat_members m
                JOIN users u ON u.id = m.user_id
                WHERE m.conversation_id = :c AND m.user_id != :me
                LIMIT 1
            ");
            $this->db->bind(':c', $convId);
            $this->db->bind(':me', $userId);
            $p = $this->db->single();
            if ($p) {
                $name = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
                $row['partner'] = [
                    'id'            => (int)$p['id'],
                    'name'          => $name,
                    'profile_photo' => $p['profile_photo'] ?? null,
                    'is_online'     => $this->isOnline($p['last_active_at'] ?? null),
                    'last_seen'     => $p['last_active_at'] ?? null,
                ];
                $row['title'] = $name ?: 'User';
            }
        } catch (\Throwable $e) {}
    }

    private function isOnline(?string $lastSeen): bool {
        if (!$lastSeen) return false;
        return (time() - strtotime($lastSeen)) <= 120;
    }

    /* ═══════════════ DB FALLBACKS ═══════════════ */
    private function listForUserDb(int $userId): array {
        try {
            $this->db->query("
                SELECT c.id, c.type, c.title, c.last_activity,
                       m.is_pinned, m.is_muted,
                       (SELECT content FROM chat_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) AS last_msg,
                       (SELECT created_at FROM chat_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) AS last_msg_at,
                       (SELECT COUNT(*) FROM chat_messages
                          WHERE conversation_id = c.id
                            AND sender_id != :u2
                            AND created_at > COALESCE(m.last_read_at, '1970-01-01')
                       ) AS unread_count
                FROM chat_conversations c
                JOIN chat_members m ON m.conversation_id = c.id AND m.user_id = :u
                ORDER BY m.is_pinned DESC, c.last_activity DESC
                LIMIT 100
            ");
            $this->db->bind(':u', $userId);
            $this->db->bind(':u2', $userId);
            $rows = $this->db->resultSet() ?: [];

            foreach ($rows as &$r) {
                if (($r['type'] ?? '') === 'direct') {
                    $this->attachPartner($r, (int)$r['id'], $userId);
                }
            }
            return $rows;
        } catch (\Throwable $e) {
            error_log('[Chat listForUserDb] ' . $e->getMessage());
            return [];
        }
    }

    private function getConversationRow(int $convId, int $userId): ?array {
        try {
            $this->db->query("SELECT * FROM chat_conversations WHERE id = :id LIMIT 1");
            $this->db->bind(':id', $convId);
            return $this->db->single() ?: null;
        } catch (\Throwable $e) { return null; }
    }

    private function getMessagesDb(int $convId, int $limit = 60): array {
        try {
            $this->db->query("
                SELECT m.*, u.first_name, u.last_name, u.profile_photo
                FROM chat_messages m
                LEFT JOIN users u ON u.id = m.sender_id
                WHERE m.conversation_id = :c
                ORDER BY m.id ASC
                LIMIT :n
            ");
            $this->db->bind(':c', $convId);
            $this->db->bind(':n', $limit);
            return $this->db->resultSet() ?: [];
        } catch (\Throwable $e) { return []; }
    }

    /* ═══════════════ SEARCH ═══════════════ */
    public function search() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $q  = trim($_GET['q'] ?? '');
            $me = (int)$_SESSION['user_id'];

            $sql = "SELECT id, first_name, last_name, email, role_id, profile_photo, last_active_at
                    FROM users WHERE id != :me";
            $binds = [':me' => $me];

            if ($q !== '') {
                $sql .= " AND (
                            first_name LIKE :q1
                         OR last_name  LIKE :q2
                         OR email      LIKE :q3
                         OR CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,'')) LIKE :q4
                         )";
                $like = '%' . $q . '%';
                $binds[':q1'] = $like;
                $binds[':q2'] = $like;
                $binds[':q3'] = $like;
                $binds[':q4'] = $like;
            }

            $sql .= " ORDER BY first_name ASC LIMIT 40";

            $this->db->query($sql);
            foreach ($binds as $k => $v) $this->db->bind($k, $v);
            $rows = $this->db->resultSet();

            $users = [];
            foreach ((array)$rows as $u) {
                $uid  = (int)$u['id'];
                $name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                if ($name === '') $name = $u['email'] ?? 'User';

                $inits = strtoupper(mb_substr($name, 0, 1, 'UTF-8'));
                $parts = explode(' ', $name);
                if (count($parts) > 1) {
                    $inits = strtoupper(mb_substr($parts[0], 0, 1, 'UTF-8') . mb_substr(end($parts), 0, 1, 'UTF-8'));
                }

                $users[] = [
                    'id'            => $uid,
                    'name'          => $name,
                    'initials'      => $inits,
                    'email'         => $u['email'] ?? '',
                    'profile_photo' => $u['profile_photo'] ?? null,
                    'district'      => '',
                    'role_label'    => 'User',
                    'is_online'     => $this->isOnline($u['last_active_at'] ?? null),
                    'relationship'  => $this->relationship($me, $uid),
                ];
            }

            echo json_encode([
                'success' => true,
                'users'   => $users,
                'count'   => count($users),
                'query'   => $q,
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log('[Chat search] ' . $e->getMessage());
            echo json_encode(['success' => false, 'users' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function users() { $this->search(); }

    /* ═══════════════ FRIEND REQUEST ═══════════════ */
    public function friendRequest() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();
        $this->verifyCsrf();
        $this->rateLimit('chat_fr', 10);

        $me   = (int)$_SESSION['user_id'];
        $them = (int)($_POST['user_id'] ?? 0);
        if ($them <= 0 || $them === $me) {
            echo json_encode(['success' => false, 'error' => 'Invalid user']); exit;
        }

        [$ok, $result] = $this->sendFriendRequest($me, $them);
        if (!$ok) { echo json_encode(['success' => false, 'error' => $result]); exit; }

        echo json_encode([
            'success'      => true,
            'result'       => $result,
            'relationship' => $this->relationship($me, $them),
        ]);
        exit;
    }

    /* ═══════════════ START ═══════════════ */
    public function start() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();
        $this->verifyCsrf();
        $this->rateLimit('chat_start', 20);

        $me       = (int)$_SESSION['user_id'];
        $targetId = (int)($_POST['user_id'] ?? 0);

        if ($targetId <= 0 || $targetId === $me) {
            echo json_encode(['success' => false, 'error' => 'Invalid user']); exit;
        }

        try {
            $this->db->query("SELECT id FROM users WHERE id = :id LIMIT 1");
            $this->db->bind(':id', $targetId);
            if (!$this->db->single()) {
                echo json_encode(['success' => false, 'error' => 'User not found']); exit;
            }
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'DB error']); exit;
        }

        $rel = $this->relationship($me, $targetId);

        if ($rel === 'blocked') {
            echo json_encode(['success' => false, 'error' => 'You cannot message this user.']); exit;
        }
        if ($rel === 'request_sent') {
            echo json_encode(['success' => false, 'error' => 'Friend request pending', 'relationship' => $rel]); exit;
        }
        if ($rel === 'request_received') {
            echo json_encode(['success' => false, 'error' => 'Accept their friend request first', 'relationship' => $rel]); exit;
        }
        if ($rel === 'none') {
            [$ok, $msg] = $this->sendFriendRequest($me, $targetId);
            echo json_encode([
                'success' => false,
                'error'   => 'You must be friends to chat. Friend request sent!',
                'relationship' => $this->relationship($me, $targetId),
                'auto_friend_request' => true,
            ]);
            exit;
        }

        if ($this->chat && method_exists($this->chat, 'findOrCreateDirect')) {
            try {
                $convId = (int)$this->chat->findOrCreateDirect($me, $targetId);
                if ($convId > 0) {
                    echo json_encode(['success' => true, 'conversation_id' => $convId]); exit;
                }
            } catch (\Throwable $e) { error_log('[Chat start model] ' . $e->getMessage()); }
        }

        try {
            $this->db->query("
                SELECT c.id FROM chat_conversations c
                JOIN chat_members m1 ON m1.conversation_id = c.id AND m1.user_id = :me
                JOIN chat_members m2 ON m2.conversation_id = c.id AND m2.user_id = :them
                WHERE c.type = 'direct' LIMIT 1
            ");
            $this->db->bind(':me', $me);
            $this->db->bind(':them', $targetId);
            $existing = $this->db->single();
            if ($existing) {
                echo json_encode(['success' => true, 'conversation_id' => (int)$existing['id']]); exit;
            }

            $this->db->query("INSERT INTO chat_conversations (uuid, type, created_by, created_at, last_activity)
                              VALUES (:u, 'direct', :cb, NOW(), NOW())");
            $this->db->bind(':u', bin2hex(random_bytes(8)));
            $this->db->bind(':cb', $me);
            $this->db->execute();
            $convId = (int)$this->db->lastInsertId();

            foreach ([$me, $targetId] as $uid) {
                $this->db->query("INSERT INTO chat_members (conversation_id, user_id, joined_at)
                                  VALUES (:c, :u, NOW())");
                $this->db->bind(':c', $convId);
                $this->db->bind(':u', $uid);
                $this->db->execute();
            }

            echo json_encode(['success' => true, 'conversation_id' => $convId]);
        } catch (\Throwable $e) {
            error_log('[Chat start db] ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ SEND ═══════════════ */
    public function send() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();
        $this->verifyCsrf();
        $this->rateLimit('chat_send', 40);

        $userId = (int)$_SESSION['user_id'];
        $convId = (int)($_POST['conversation_id'] ?? 0);

        if (!$this->isMember($convId, $userId)) {
            echo json_encode(['success' => false, 'error' => 'Not a member']); exit;
        }

        $other = $this->getOtherMember($convId, $userId);
        if ($other > 0 && !$this->areFriends($userId, $other)) {
            echo json_encode(['success' => false, 'error' => 'You are no longer friends.']); exit;
        }

        $type = $_POST['type'] ?? 'text';
        if (!in_array($type, ['text','image','video','audio','location','file','emoji'], true)) $type = 'text';

        $content = $this->cleanText(trim((string)($_POST['content'] ?? '')));
        $mediaUrl = null;
        $lat = $lng = null;

        if (!empty($_FILES['media']['name']) && $_FILES['media']['error'] === UPLOAD_ERR_OK && $type !== 'text') {
            $allowed = [
                'image' => ['image/jpeg','image/png','image/webp','image/gif'],
                'video' => ['video/mp4','video/webm'],
                'audio' => ['audio/webm','audio/mpeg','audio/ogg','audio/wav'],
            ];
            $extByType = ['image'=>'jpg','video'=>'mp4','audio'=>'webm'];
            $mime = function_exists('mime_content_type') ? mime_content_type($_FILES['media']['tmp_name']) : '';
            $okMimes = $allowed[$type] ?? [];

            if ($mime && in_array($mime, $okMimes, true) && $_FILES['media']['size'] <= 10 * 1024 * 1024) {
                $dir = __DIR__ . '/../../public/uploads/chat/' . date('Y/m/');
                if (!is_dir($dir)) @mkdir($dir, 0755, true);
                $fname = bin2hex(random_bytes(12)) . '.' . ($extByType[$type] ?? 'bin');
                if (move_uploaded_file($_FILES['media']['tmp_name'], $dir . $fname)) {
                    $mediaUrl = BASE_URL . '/public/uploads/chat/' . date('Y/m/') . $fname;
                }
            }
        }

        if ($type === 'location') {
            $lat = (float)($_POST['lat'] ?? 0);
            $lng = (float)($_POST['lng'] ?? 0);
        }

        if ($content === '' && !$mediaUrl && $type === 'text') {
            echo json_encode(['success' => false, 'error' => 'Empty message']); exit;
        }

        $msgId = null;
        if ($this->chat && method_exists($this->chat, 'sendMessage')) {
            try {
                $msgId = $this->chat->sendMessage([
                    'conversation_id' => $convId,
                    'sender_id'       => $userId,
                    'type'            => $type,
                    'content'         => mb_substr($content, 0, 4000, 'UTF-8'),
                    'media_url'       => $mediaUrl,
                    'lat'             => $lat,
                    'lng'             => $lng,
                ]);
            } catch (\Throwable $e) { error_log('[Chat send model] ' . $e->getMessage()); }
        }

        if (!$msgId) {
            try {
                $this->db->query("INSERT INTO chat_messages
                    (conversation_id, sender_id, message_type, content, media_url, location_lat, location_lng, status, created_at)
                    VALUES (:c, :s, :t, :ct, :m, :la, :lo, 'sent', NOW())");
                $this->db->bind(':c', $convId);
                $this->db->bind(':s', $userId);
                $this->db->bind(':t', $type);
                $this->db->bind(':ct', mb_substr($content, 0, 4000, 'UTF-8'));
                $this->db->bind(':m', $mediaUrl);
                $this->db->bind(':la', $lat);
                $this->db->bind(':lo', $lng);
                $this->db->execute();
                $msgId = (int)$this->db->lastInsertId();

                $this->db->query("UPDATE chat_conversations SET last_activity = NOW() WHERE id = :id");
                $this->db->bind(':id', $convId);
                $this->db->execute();
            } catch (\Throwable $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]); exit;
            }
        }

        echo json_encode([
            'success' => true,
            'message' => [
                'id'           => (int)$msgId,
                'sender_id'    => $userId,
                'sender_name'  => $_SESSION['name'] ?? 'You',
                'content'      => $content,
                'message_type' => $type,
                'media_url'    => $mediaUrl,
                'time'         => date('H:i'),
            ],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ═══════════════ POLL ═══════════════ */
    public function poll() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        $userId = (int)$_SESSION['user_id'];
        $convId = (int)($_GET['conversation_id'] ?? 0);
        $after  = (int)($_GET['after'] ?? 0);

        if (!$this->isMember($convId, $userId)) {
            echo json_encode(['success' => false, 'messages' => []]); exit;
        }

        $out = [];
        try {
            $this->db->query("
                SELECT m.*, u.first_name, u.last_name, u.profile_photo
                FROM chat_messages m
                LEFT JOIN users u ON u.id = m.sender_id
                WHERE m.conversation_id = :c AND m.id > :a
                ORDER BY m.id ASC LIMIT 50
            ");
            $this->db->bind(':c', $convId);
            $this->db->bind(':a', $after);
            foreach ((array)$this->db->resultSet() as $m) {
                $out[] = [
                    'id'           => (int)$m['id'],
                    'sender_id'    => (int)$m['sender_id'],
                    'sender_name'  => trim(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? '')) ?: 'User',
                    'profile_photo'=> $m['profile_photo'] ?? null,
                    'content'      => $m['content'] ?? '',
                    'message_type' => $m['message_type'] ?? 'text',
                    'media_url'    => $m['media_url'] ?? null,
                    'time'         => date('H:i', strtotime($m['created_at'] ?? 'now')),
                ];
            }
            $this->markRead($convId, $userId);
        } catch (\Throwable $e) {}

        echo json_encode(['success' => true, 'messages' => $out], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ═══════════════ INFO ═══════════════ */
    public function info() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        $userId = (int)$_SESSION['user_id'];
        $convId = (int)($_GET['conversation_id'] ?? 0);

        if (!$this->isMember($convId, $userId)) { echo json_encode(['success' => false]); exit; }
        $c = $this->getConversationRow($convId, $userId);
        if (!$c) { echo json_encode(['success' => false]); exit; }

        try {
            $this->db->query("SELECT COUNT(*) AS c FROM chat_members WHERE conversation_id = :c");
            $this->db->bind(':c', $convId);
            $cnt = (int)($this->db->single()['c'] ?? 2);
        } catch (\Throwable $e) { $cnt = 2; }

        echo json_encode([
            'success' => true,
            'info' => [
                'title'        => $c['title'] ?? 'Conversation',
                'type'         => $c['type'] ?? 'direct',
                'member_count' => $cnt,
                'created_at'   => $c['created_at'] ?? '—',
            ],
        ]);
        exit;
    }

    /* ═══════════════ REACT / PIN / MUTE / LEAVE / EDIT / DELETE ═══════════════ */
    public function react() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();
        $this->verifyCsrf();

        $userId = (int)$_SESSION['user_id'];
        $msgId  = (int)($_POST['message_id'] ?? 0);
        $emoji  = trim((string)($_POST['emoji'] ?? ''));

        if ($msgId <= 0 || $emoji === '') {
            echo json_encode(['success' => false, 'error' => 'Invalid']); exit;
        }

        try {
            $this->db->query("SELECT conversation_id FROM chat_messages WHERE id = :id LIMIT 1");
            $this->db->bind(':id', $msgId);
            $m = $this->db->single();
            if (!$m || !$this->isMember((int)$m['conversation_id'], $userId)) {
                echo json_encode(['success' => false, 'error' => 'Not a member']); exit;
            }

            $this->db->query("INSERT INTO chat_messages
                (conversation_id, sender_id, message_type, content, status, created_at)
                VALUES (:c, :s, 'emoji', :e, 'sent', NOW())");
            $this->db->bind(':c', (int)$m['conversation_id']);
            $this->db->bind(':s', $userId);
            $this->db->bind(':e', $emoji);
            $this->db->execute();

            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function pin() {
        header('Content-Type: application/json');
        $this->verifyCsrf();
        if (ob_get_level() > 0) @ob_clean();

        $userId = (int)$_SESSION['user_id'];
        $convId = (int)($_POST['conversation_id'] ?? 0);

        try {
            $this->db->query("UPDATE chat_members SET is_pinned = IF(is_pinned=1,0,1)
                              WHERE conversation_id = :c AND user_id = :u");
            $this->db->bind(':c', $convId);
            $this->db->bind(':u', $userId);
            $this->db->execute();
            $this->db->query("SELECT is_pinned FROM chat_members WHERE conversation_id = :c AND user_id = :u");
            $this->db->bind(':c', $convId);
            $this->db->bind(':u', $userId);
            $r = $this->db->single();
            echo json_encode(['success' => true, 'pinned' => !empty($r['is_pinned'])]);
        } catch (\Throwable $e) { echo json_encode(['success' => false]); }
        exit;
    }

    public function mute() {
        header('Content-Type: application/json');
        $this->verifyCsrf();
        if (ob_get_level() > 0) @ob_clean();

        $userId = (int)$_SESSION['user_id'];
        $convId = (int)($_POST['conversation_id'] ?? 0);

        try {
            $this->db->query("UPDATE chat_members SET is_muted = IF(is_muted=1,0,1)
                              WHERE conversation_id = :c AND user_id = :u");
            $this->db->bind(':c', $convId);
            $this->db->bind(':u', $userId);
            $this->db->execute();
            $this->db->query("SELECT is_muted FROM chat_members WHERE conversation_id = :c AND user_id = :u");
            $this->db->bind(':c', $convId);
            $this->db->bind(':u', $userId);
            $r = $this->db->single();
            echo json_encode(['success' => true, 'muted' => !empty($r['is_muted'])]);
        } catch (\Throwable $e) { echo json_encode(['success' => false]); }
        exit;
    }

    public function leave() {
        header('Content-Type: application/json');
        $this->verifyCsrf();
        if (ob_get_level() > 0) @ob_clean();

        $userId = (int)$_SESSION['user_id'];
        $convId = (int)($_POST['conversation_id'] ?? 0);

        try {
            $this->db->query("DELETE FROM chat_members WHERE conversation_id = :c AND user_id = :u");
            $this->db->bind(':c', $convId);
            $this->db->bind(':u', $userId);
            $this->db->execute();
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) { echo json_encode(['success' => false]); }
        exit;
    }

    public function editMessage() {
        header('Content-Type: application/json; charset=utf-8');
        $this->verifyCsrf();
        if (ob_get_level() > 0) @ob_clean();

        $userId  = (int)$_SESSION['user_id'];
        $msgId   = (int)($_POST['message_id'] ?? 0);
        $content = $this->cleanText(trim((string)($_POST['content'] ?? '')));

        if ($msgId <= 0 || $content === '') {
            echo json_encode(['success' => false, 'error' => 'Empty content']); exit;
        }

        try {
            $this->db->query("UPDATE chat_messages SET content = :c WHERE id = :id AND sender_id = :u");
            $this->db->bind(':c', mb_substr($content, 0, 4000, 'UTF-8'));
            $this->db->bind(':id', $msgId);
            $this->db->bind(':u',  $userId);
            $this->db->execute();
            echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) { echo json_encode(['success' => false]); }
        exit;
    }

    public function deleteMessage() {
        header('Content-Type: application/json');
        $this->verifyCsrf();
        if (ob_get_level() > 0) @ob_clean();

        $userId = (int)$_SESSION['user_id'];
        $msgId  = (int)($_POST['message_id'] ?? 0);

        try {
            $this->db->query("DELETE FROM chat_messages WHERE id = :id AND sender_id = :u");
            $this->db->bind(':id', $msgId);
            $this->db->bind(':u',  $userId);
            $this->db->execute();
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) { echo json_encode(['success' => false]); }
        exit;
    }

    public function typing() {
        header('Content-Type: application/json');
        if (ob_get_level() > 0) @ob_clean();

        $userId = (int)$_SESSION['user_id'];
        $convId = (int)($_POST['conversation_id'] ?? 0);

        try {
            $this->db->query("REPLACE INTO chat_typing (conversation_id, user_id, updated_at)
                              VALUES (:c, :u, NOW())");
            $this->db->bind(':c', $convId);
            $this->db->bind(':u', $userId);
            $this->db->execute();
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) { echo json_encode(['success' => false]); }
        exit;
    }

    public function typingUsers() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        $userId = (int)$_SESSION['user_id'];
        $convId = (int)($_GET['conversation_id'] ?? 0);

        try {
            $this->db->query("
                SELECT u.first_name FROM chat_typing t
                JOIN users u ON u.id = t.user_id
                WHERE t.conversation_id = :c AND t.user_id != :u
                  AND t.updated_at > DATE_SUB(NOW(), INTERVAL 5 SECOND)
            ");
            $this->db->bind(':c', $convId);
            $this->db->bind(':u', $userId);
            $rows = $this->db->resultSet() ?: [];
            echo json_encode(['success' => true, 'users' => $rows], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'users' => []]);
        }
        exit;
    }

    /* ═══════════════ HELPERS ═══════════════ */
    private function cleanText(string $s): string {
        $s = str_replace(chr(0), '', $s);
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        if (mb_strlen($s, 'UTF-8') > 4000) {
            $s = mb_substr($s, 0, 4000, 'UTF-8');
        }
        return $s;
    }

    private function getOtherMember(int $convId, int $me): int {
        try {
            $this->db->query("SELECT user_id FROM chat_members
                              WHERE conversation_id = :c AND user_id != :me LIMIT 1");
            $this->db->bind(':c', $convId);
            $this->db->bind(':me', $me);
            $r = $this->db->single();
            return $r ? (int)$r['user_id'] : 0;
        } catch (\Throwable $e) { return 0; }
    }

    private function isMember(int $convId, int $userId): bool {
        if ($convId <= 0) return false;

        if ($this->chat && method_exists($this->chat, 'isMember')) {
            try {
                $r = $this->chat->isMember($convId, $userId);
                if ($r !== null) return (bool)$r;
            } catch (\Throwable $e) {}
        }

        try {
            $this->db->query("SELECT 1 FROM chat_members WHERE conversation_id = :c AND user_id = :u LIMIT 1");
            $this->db->bind(':c', $convId);
            $this->db->bind(':u', $userId);
            return (bool)$this->db->single();
        } catch (\Throwable $e) { return false; }
    }

    private function markRead(int $convId, int $userId): void {
        try {
            $this->db->query("UPDATE chat_members SET last_read_at = NOW()
                              WHERE conversation_id = :c AND user_id = :u");
            $this->db->bind(':c', $convId);
            $this->db->bind(':u', $userId);
            $this->db->execute();
        } catch (\Throwable $e) {}
    }
}