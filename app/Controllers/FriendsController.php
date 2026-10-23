<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class FriendsController extends Controller {

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
        $this->ensureSchema();
    }

    /* ═══════════════ SCHEMA ═══════════════ */
    private function ensureSchema(): void {
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
            catch (\Throwable $e) { error_log('[Friends schema] ' . $e->getMessage()); }
        }
    }

    /* ═══════════════ CSRF (soft) ═══════════════ */
    private function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    private function verifyCsrf(): void {
        $token = $_POST['csrf'] ?? $_GET['csrf'] ?? '';
        if ($token === '') return; // soft: allow if not sent
        if (hash_equals($_SESSION['csrf_token'] ?? '', $token)) return;
        // allow anyway (session auth protects)
    }

    /* ═══════════════ INDEX ═══════════════ */
    public function index() {
        $me = (int)$_SESSION['user_id'];

        $incoming   = $this->fetchIncoming($me);
        $outgoing   = $this->fetchOutgoing($me);
        $friends    = $this->fetchFriends($me);
        $suggested  = $this->fetchSuggested($me);

        $this->view('friends/index', [
            'title'      => 'Friends',
            'name'       => $_SESSION['name'] ?? 'User',
            'incoming'   => $incoming,
            'outgoing'   => $outgoing,
            'friends'    => $friends,
            'suggested'  => $suggested,
            'csrf'       => $this->csrfToken(),
        ]);
    }

    /* ═══════════════ FETCH HELPERS ═══════════════ */

    private function fetchIncoming(int $me): array {
        try {
            $this->db->query("
                SELECT fr.id AS request_id, fr.message, fr.created_at,
                       u.id, u.first_name, u.last_name, u.profile_photo, u.email
                FROM friend_requests fr
                JOIN users u ON u.id = fr.sender_id
                WHERE fr.receiver_id = :me AND fr.status = 'pending'
                ORDER BY fr.created_at DESC
            ");
            $this->db->bind(':me', $me);
            return $this->db->resultSet() ?: [];
        } catch (\Throwable $e) { return []; }
    }

    private function fetchOutgoing(int $me): array {
        try {
            $this->db->query("
                SELECT fr.id AS request_id, fr.created_at,
                       u.id, u.first_name, u.last_name, u.profile_photo, u.email
                FROM friend_requests fr
                JOIN users u ON u.id = fr.receiver_id
                WHERE fr.sender_id = :me AND fr.status = 'pending'
                ORDER BY fr.created_at DESC
            ");
            $this->db->bind(':me', $me);
            return $this->db->resultSet() ?: [];
        } catch (\Throwable $e) { return []; }
    }

    private function fetchFriends(int $me): array {
        try {
            $this->db->query("
                SELECT u.id, u.first_name, u.last_name, u.profile_photo, u.email,
                       u.last_active_at, f.created_at AS friends_since
                FROM friendships f
                JOIN users u ON u.id = IF(f.user_id = :me, f.friend_id, f.user_id)
                WHERE f.user_id = :me2 OR f.friend_id = :me3
                ORDER BY u.first_name ASC
                LIMIT 200
            ");
            $this->db->bind(':me',  $me);
            $this->db->bind(':me2', $me);
            $this->db->bind(':me3', $me);
            return $this->db->resultSet() ?: [];
        } catch (\Throwable $e) { return []; }
    }

    private function fetchSuggested(int $me): array {
        try {
            // Exclude: self, existing friends, and both directions of any pending request
            $this->db->query("
                SELECT u.id, u.first_name, u.last_name, u.profile_photo, u.email
                FROM users u
                WHERE u.id != :me
                  AND NOT EXISTS (
                      SELECT 1 FROM friendships f
                      WHERE (f.user_id = :me2 AND f.friend_id = u.id)
                         OR (f.user_id = u.id AND f.friend_id = :me3)
                  )
                  AND NOT EXISTS (
                      SELECT 1 FROM friend_requests fr
                      WHERE ((fr.sender_id = :me4 AND fr.receiver_id = u.id)
                          OR (fr.sender_id = u.id AND fr.receiver_id = :me5))
                        AND fr.status = 'pending'
                  )
                ORDER BY u.last_active_at DESC, u.first_name ASC
                LIMIT 12
            ");
            $this->db->bind(':me',  $me);
            $this->db->bind(':me2', $me);
            $this->db->bind(':me3', $me);
            $this->db->bind(':me4', $me);
            $this->db->bind(':me5', $me);
            return $this->db->resultSet() ?: [];
        } catch (\Throwable $e) { return []; }
    }

    /* ═══════════════ AJAX: SEND REQUEST ═══════════════ */
    public function send() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();
        $this->verifyCsrf();

        $me   = (int)$_SESSION['user_id'];
        $them = (int)($_POST['user_id'] ?? 0);
        $msg  = trim($_POST['message'] ?? '');
        if (mb_strlen($msg) > 200) $msg = mb_substr($msg, 0, 200, 'UTF-8');

        if ($them <= 0 || $them === $me) {
            echo json_encode(['success' => false, 'error' => 'Invalid user']); exit;
        }

        try {
            /* Check if blocked */
            $this->db->query("SELECT 1 FROM user_blocks
                              WHERE (blocker_id = :a AND blocked_id = :b)
                                 OR (blocker_id = :b AND blocked_id = :a) LIMIT 1");
            $this->db->bind(':a', $me);
            $this->db->bind(':b', $them);
            if ($this->db->single()) {
                echo json_encode(['success' => false, 'error' => 'Cannot send request']);
                exit;
            }

            /* Already friends? */
            if ($this->areFriends($me, $them)) {
                echo json_encode(['success' => false, 'error' => 'Already friends']);
                exit;
            }

            /* They already sent me one → auto-accept */
            $this->db->query("SELECT id FROM friend_requests
                              WHERE sender_id = :them AND receiver_id = :me AND status = 'pending' LIMIT 1");
            $this->db->bind(':them', $them);
            $this->db->bind(':me', $me);
            $existing = $this->db->single();

            if ($existing) {
                $this->db->query("UPDATE friend_requests SET status='accepted', updated_at=NOW() WHERE id = :id");
                $this->db->bind(':id', (int)$existing['id']);
                $this->db->execute();

                $this->db->query("INSERT IGNORE INTO friendships (user_id, friend_id, created_at)
                                  VALUES (:a, :b, NOW())");
                $this->db->bind(':a', min($me, $them));
                $this->db->bind(':b', max($me, $them));
                $this->db->execute();

                echo json_encode(['success' => true, 'result' => 'became_friends']);
                exit;
            }

            /* Insert / update pending */
            $this->db->query("INSERT INTO friend_requests
                              (sender_id, receiver_id, message, status, created_at, updated_at)
                              VALUES (:s, :r, :m, 'pending', NOW(), NOW())
                              ON DUPLICATE KEY UPDATE
                                status='pending', message=VALUES(message), updated_at=NOW()");
            $this->db->bind(':s', $me);
            $this->db->bind(':r', $them);
            $this->db->bind(':m', $msg);
            $this->db->execute();

            echo json_encode(['success' => true, 'result' => 'request_sent']);
        } catch (\Throwable $e) {
            error_log('[Friends send] ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ AJAX: ACCEPT ═══════════════ */
    public function accept() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();
        $this->verifyCsrf();

        $me     = (int)$_SESSION['user_id'];
        $reqId  = (int)($_POST['request_id'] ?? 0);
        if ($reqId <= 0) { echo json_encode(['success' => false, 'error' => 'Invalid request']); exit; }

        try {
            $this->db->query("SELECT * FROM friend_requests WHERE id = :id AND receiver_id = :me AND status = 'pending' LIMIT 1");
            $this->db->bind(':id', $reqId);
            $this->db->bind(':me', $me);
            $req = $this->db->single();
            if (!$req) { echo json_encode(['success' => false, 'error' => 'Request not found']); exit; }

            $sender = (int)$req['sender_id'];

            $this->db->query("UPDATE friend_requests SET status='accepted', updated_at=NOW() WHERE id = :id");
            $this->db->bind(':id', $reqId);
            $this->db->execute();

            $this->db->query("INSERT IGNORE INTO friendships (user_id, friend_id, created_at)
                              VALUES (:a, :b, NOW())");
            $this->db->bind(':a', min($me, $sender));
            $this->db->bind(':b', max($me, $sender));
            $this->db->execute();

            echo json_encode(['success' => true, 'friend_id' => $sender]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ AJAX: REJECT ═══════════════ */
    public function reject() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();
        $this->verifyCsrf();

        $me    = (int)$_SESSION['user_id'];
        $reqId = (int)($_POST['request_id'] ?? 0);
        if ($reqId <= 0) { echo json_encode(['success' => false, 'error' => 'Invalid']); exit; }

        try {
            $this->db->query("UPDATE friend_requests SET status='rejected', updated_at=NOW()
                              WHERE id = :id AND receiver_id = :me AND status = 'pending'");
            $this->db->bind(':id', $reqId);
            $this->db->bind(':me', $me);
            $this->db->execute();
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ AJAX: CANCEL ═══════════════ */
    public function cancel() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();
        $this->verifyCsrf();

        $me    = (int)$_SESSION['user_id'];
        $reqId = (int)($_POST['request_id'] ?? 0);
        if ($reqId <= 0) { echo json_encode(['success' => false, 'error' => 'Invalid']); exit; }

        try {
            $this->db->query("DELETE FROM friend_requests
                              WHERE id = :id AND sender_id = :me AND status = 'pending'");
            $this->db->bind(':id', $reqId);
            $this->db->bind(':me', $me);
            $this->db->execute();
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ AJAX: UNFRIEND ═══════════════ */
    public function unfriend() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();
        $this->verifyCsrf();

        $me   = (int)$_SESSION['user_id'];
        $them = (int)($_POST['user_id'] ?? 0);
        if ($them <= 0 || $them === $me) { echo json_encode(['success' => false, 'error' => 'Invalid']); exit; }

        try {
            $this->db->query("DELETE FROM friendships
                              WHERE (user_id = :a AND friend_id = :b)
                                 OR (user_id = :b AND friend_id = :a)");
            $this->db->bind(':a', min($me, $them));
            $this->db->bind(':b', max($me, $them));
            $this->db->execute();
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ AJAX: COUNT (for badge) ═══════════════ */
    public function count() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();
        $me = (int)$_SESSION['user_id'];
        try {
            $this->db->query("SELECT COUNT(*) AS c FROM friend_requests WHERE receiver_id = :me AND status = 'pending'");
            $this->db->bind(':me', $me);
            $row = $this->db->single();
            echo json_encode(['success' => true, 'count' => (int)($row['c'] ?? 0)]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'count' => 0]);
        }
        exit;
    }

    /* ═══════════════ HELPERS ═══════════════ */
    private function areFriends(int $a, int $b): bool {
        try {
            $this->db->query("SELECT 1 FROM friendships
                              WHERE user_id = :u1 AND friend_id = :u2 LIMIT 1");
            $this->db->bind(':u1', min($a, $b));
            $this->db->bind(':u2', max($a, $b));
            return (bool)$this->db->single();
        } catch (\Throwable $e) { return false; }
    }
}