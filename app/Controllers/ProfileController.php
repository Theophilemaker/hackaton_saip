<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Security;

class ProfileController extends Controller {

    private $db;

    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        try {
            $this->db = new Database();
        } catch (\Throwable $e) {
            $this->db = null;
        }
    }

    /* ═══════════════════════════════════════════════════════════
       VIEW — /profile  or  /profile/{id}
       ═══════════════════════════════════════════════════════════ */
    public function index($id = null) {
        $viewerId = (int)$_SESSION['user_id'];
        $targetId = $id ? (int)$id : $viewerId;
        $isOwn    = ($targetId === $viewerId);

        $user = $this->fetchUser($targetId);
        if (!$user) {
            $_SESSION['flash_error'] = 'User not found.';
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }

        if (!$isOwn && empty($user['is_public']) && (int)($_SESSION['role_id'] ?? 0) !== 7) {
            $_SESSION['flash_error'] = 'This profile is private.';
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }

        $stats = $this->getUserStats($targetId);

        parent::view('profile/index', [
            'title'  => ($isOwn ? 'My Profile' : $user['first_name'] . "'s Profile"),
            'name'   => $_SESSION['name'] ?? 'User',
            'user'   => $user,
            'stats'  => $stats,
            'isOwn'  => $isOwn,
            'viewer' => $_SESSION,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       EDIT — /profile/edit
       ═══════════════════════════════════════════════════════════ */
    public function edit() {
        $userId = (int)$_SESSION['user_id'];
        $user   = $this->fetchUser($userId);
        if (!$user) {
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }

        $districts = [];
        try {
            $this->db->query("SELECT id, name FROM districts ORDER BY name");
            $districts = $this->db->resultSet() ?: [];
        } catch (\Throwable $e) {}

        $crops = [];
        try {
            $this->db->query("SELECT id, name, icon FROM crops WHERE is_active = 1 ORDER BY name");
            $crops = $this->db->resultSet() ?: [];
        } catch (\Throwable $e) {}

        parent::view('profile/edit', [
            'title'     => 'Edit Profile',
            'name'      => $_SESSION['name'] ?? 'User',
            'user'      => $user,
            'districts' => $districts,
            'crops'     => $crops,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       SAVE — POST /profile/save
       ═══════════════════════════════════════════════════════════ */
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/profile");
            exit;
        }

        $userId = (int)$_SESSION['user_id'];

        try {
            Security::verifyCsrf();
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            header("Location: " . BASE_URL . "/profile/edit");
            exit;
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name']  ?? '');
        $email     = trim($_POST['email']      ?? '');
        $phone     = trim($_POST['phone']      ?? '');
        $whatsapp  = trim($_POST['whatsapp']   ?? '');
        $district  = trim($_POST['district']   ?? '');
        $sector    = trim($_POST['sector']     ?? '');
        $bio       = trim($_POST['bio']        ?? '');
        $crops     = trim($_POST['primary_crops'] ?? '');
        $farmSize  = ($_POST['farm_size_ha'] ?? '') !== '' ? (float)$_POST['farm_size_ha'] : null;
        $experience= ($_POST['years_experience'] ?? '') !== '' ? (int)$_POST['years_experience'] : null;
        $isPublic  = !empty($_POST['is_public']) ? 1 : 0;

        if ($firstName === '') {
            $_SESSION['flash_error'] = 'First name is required.';
            header("Location: " . BASE_URL . "/profile/edit");
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Invalid email address.';
            header("Location: " . BASE_URL . "/profile/edit");
            exit;
        }

        /* Avatar */
        $avatarPath = $_POST['existing_avatar'] ?? null;
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $up = $this->handleImage($_FILES['avatar'], 'avatars', 3);
            if ($up['success']) {
                if ($avatarPath) {
                    $oldPath = __DIR__ . '/../../public' . parse_url($avatarPath, PHP_URL_PATH);
                    if (is_file($oldPath)) @unlink($oldPath);
                }
                $avatarPath = $up['url'];
            } else {
                $_SESSION['flash_error'] = 'Avatar: ' . $up['error'];
                header("Location: " . BASE_URL . "/profile/edit");
                exit;
            }
        }

        /* Cover */
        $coverPath = $_POST['existing_cover'] ?? null;
        if (!empty($_FILES['cover']['name']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $up = $this->handleImage($_FILES['cover'], 'covers', 5);
            if ($up['success']) {
                if ($coverPath) {
                    $oldPath = __DIR__ . '/../../public' . parse_url($coverPath, PHP_URL_PATH);
                    if (is_file($oldPath)) @unlink($oldPath);
                }
                $coverPath = $up['url'];
            }
        }

        /* Update */
        try {
            $this->db->query("
                UPDATE users SET
                    first_name = :fn, last_name = :ln, email = :em,
                    phone = :ph, whatsapp = :wa, district = :d, sector = :sec,
                    bio = :bio, primary_crops = :pc,
                    farm_size_ha = :fs, years_experience = :ye,
                    profile_photo = :av, cover_photo = :cv,
                    is_public = :pub, updated_at = NOW()
                WHERE id = :id
            ");
            $this->db->bind(':fn',  $firstName);
            $this->db->bind(':ln',  $lastName ?: null);
            $this->db->bind(':em',  $email);
            $this->db->bind(':ph',  $phone ?: null);
            $this->db->bind(':wa',  $whatsapp ?: null);
            $this->db->bind(':d',   $district ?: null);
            $this->db->bind(':sec', $sector ?: null);
            $this->db->bind(':bio', $bio ?: null);
            $this->db->bind(':pc',  $crops ?: null);
            $this->db->bind(':fs',  $farmSize);
            $this->db->bind(':ye',  $experience);
            $this->db->bind(':av',  $avatarPath);
            $this->db->bind(':cv',  $coverPath);
            $this->db->bind(':pub', $isPublic);
            $this->db->bind(':id',  $userId);
            $this->db->execute();

            $_SESSION['name']          = trim($firstName . ' ' . $lastName);
            $_SESSION['profile_photo'] = $avatarPath;

            $_SESSION['flash_success'] = 'Profile updated successfully.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Save failed: ' . $e->getMessage();
        }

        header("Location: " . BASE_URL . "/profile");
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       PASSWORD — POST /profile/password
       ═══════════════════════════════════════════════════════════ */
    public function password() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/profile/edit");
            exit;
        }
        $userId = (int)$_SESSION['user_id'];

        try { Security::verifyCsrf(); } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Invalid token.';
            header("Location: " . BASE_URL . "/profile/edit");
            exit;
        }

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (strlen($new) < 6) {
            $_SESSION['flash_error'] = 'New password must be at least 6 characters.';
            header("Location: " . BASE_URL . "/profile/edit");
            exit;
        }
        if ($new !== $confirm) {
            $_SESSION['flash_error'] = 'New passwords do not match.';
            header("Location: " . BASE_URL . "/profile/edit");
            exit;
        }

        try {
            $this->db->query("SELECT password_hash FROM users WHERE id = :id LIMIT 1");
            $this->db->bind(':id', $userId);
            $row = $this->db->single();

            if (!$row || !password_verify($current, $row['password_hash'])) {
                $_SESSION['flash_error'] = 'Current password is incorrect.';
                header("Location: " . BASE_URL . "/profile/edit");
                exit;
            }

            $hash = password_hash($new, PASSWORD_DEFAULT);
            $this->db->query("UPDATE users SET password_hash = :h WHERE id = :id");
            $this->db->bind(':h',  $hash);
            $this->db->bind(':id', $userId);
            $this->db->execute();

            $_SESSION['flash_success'] = 'Password changed successfully.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed: ' . $e->getMessage();
        }

        header("Location: " . BASE_URL . "/profile/edit");
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       HELPERS
       ═══════════════════════════════════════════════════════════ */

    private function fetchUser($id) {
        try {
            $this->db->query("
                SELECT u.*, r.name AS role_name, d.name AS district_name
                FROM users u
                LEFT JOIN roles     r ON r.id = u.role_id
                LEFT JOIN districts d ON d.id = u.district_id
                WHERE u.id = :id LIMIT 1
            ");
            $this->db->bind(':id', (int)$id);
            return $this->db->single();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getUserStats($userId) {
        $stats = ['posts'=>0,'farms'=>0,'active_crops'=>0,'detections'=>0];
        foreach ([
            'posts'        => "SELECT COUNT(*) AS c FROM feed_posts WHERE user_id = :u",
            'farms'        => "SELECT COUNT(*) AS c FROM farms WHERE user_id = :u",
            'active_crops' => "SELECT COUNT(*) AS c FROM farm_crops fc JOIN farms f ON f.id = fc.farm_id WHERE f.user_id = :u AND fc.is_active = 1",
            'detections'   => "SELECT COUNT(*) AS c FROM crop_detections WHERE user_id = :u",
        ] as $key => $sql) {
            try {
                $this->db->query($sql);
                $this->db->bind(':u', $userId);
                $stats[$key] = (int)($this->db->single()['c'] ?? 0);
            } catch (\Throwable $e) {}
        }
        return $stats;
    }

    private function handleImage(array $file, string $folder, int $maxMB): array {
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];

        if ($file['size'] > $maxMB * 1024 * 1024) {
            return ['success'=>false,'error'=>"Max {$maxMB} MB"];
        }
        $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : ($file['type'] ?? '');
        if (!isset($allowed[$mime])) {
            return ['success'=>false,'error'=>'Only JPG/PNG/WebP/GIF'];
        }

        $dir = __DIR__ . '/../../public/uploads/' . $folder . '/' . date('Y/m/');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $fname = 'img_' . bin2hex(random_bytes(10)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . $fname)) {
            return ['success'=>false,'error'=>'Save failed'];
        }
        return ['success'=>true,'url'=>BASE_URL . '/uploads/' . $folder . '/' . date('Y/m/') . $fname];
    }
}