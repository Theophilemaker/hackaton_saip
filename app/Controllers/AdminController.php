<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class AdminController extends Controller {

    private $db;

    /* ═══════════════════════════════════════════════════════════
       CONSTRUCTOR — Auth, role check, schema bootstrap
       ═══════════════════════════════════════════════════════════ */
    public function __construct() {
        /* 1. Require login */
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        /* 2. Load DB */
        $this->db = new Database();

        /* 3. Verify user from DB */
        $this->db->query("SELECT u.role_id, u.status, r.name AS role_name
                          FROM users u
                          LEFT JOIN roles r ON r.id = u.role_id
                          WHERE u.id = :id");
        $this->db->bind(':id', $_SESSION['user_id']);
        $user = $this->db->single();

        if (!$user || ($user['status'] ?? '') !== 'active') {
            $_SESSION['flash_error'] = 'Account inactive.';
            session_destroy();
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        /* 4. Refresh session role info */
        $_SESSION['role_id']   = (int)$user['role_id'];
        $_SESSION['role_name'] = $user['role_name'] ?? '';

        /* 5. Determine current method from URL */
        $uri      = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        $segments = explode('/', $uri);
        $methodFromUrl = $segments[1] ?? 'index';

        /* 6. Methods any logged-in user may access */
        $publicMethods = ['profile', 'updateProfile', 'updateProfileAjax', 'signOutAll', 'diagnose'];
        $isPublicMethod = in_array($methodFromUrl, $publicMethods, true);

        /* 7. Admin gate */
        $isAdmin = ((int)$user['role_id'] === 7)
                || in_array(strtolower($user['role_name'] ?? ''), [
                    'admin', 'administrator', 'super admin', 'super_admin', 'superadmin'
                ], true);

        if (!$isAdmin && !$isPublicMethod) {
            $_SESSION['flash_error'] = 'Admin access required.';
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }

        /* 8. Bootstrap schema (admins only) */
        if ($isAdmin) {
            $this->ensureSchema();
        }
    }

    /* ═══════════════════════════════════════════════════════════
       JSON HELPERS
       ═══════════════════════════════════════════════════════════ */
    private function jsonExit($data, $statusCode = 200) {
        if (ob_get_level() > 0) @ob_clean();
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function jsonOk($data = []) {
        $this->jsonExit(array_merge(['success' => true], $data));
    }

    private function jsonError($message, $statusCode = 400) {
        $this->jsonExit(['success' => false, 'error' => $message], $statusCode);
    }

    /* ═══════════════════════════════════════════════════════════
       ENSURE SCHEMA
       ═══════════════════════════════════════════════════════════ */
    private function ensureSchema() {
        $requiredCols = [
            'bio'                => 'TEXT NULL',
            'avatar'             => 'VARCHAR(255) NULL',
            'timezone'           => "VARCHAR(50) DEFAULT 'Africa/Kigali'",
            'language'           => "VARCHAR(10) DEFAULT 'en'",
            'two_factor_enabled' => 'TINYINT(1) DEFAULT 0',
        ];

        foreach ($requiredCols as $col => $def) {
            try {
                $this->db->query("SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS
                                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = :c");
                $this->db->bind(':c', $col);
                $row = $this->db->single();
                if ((int)($row['c'] ?? 0) === 0) {
                    $this->db->query("ALTER TABLE users ADD COLUMN `{$col}` {$def}");
                    $this->db->execute();
                }
            } catch (\Throwable $e) { /* silent */ }
        }

        $tables = [
            "CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT NULL,
                setting_group VARCHAR(50) DEFAULT 'general',
                updated_by INT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_group (setting_group)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS activity_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                action VARCHAR(100) NOT NULL,
                description TEXT NULL,
                ip_address VARCHAR(45) NULL,
                user_agent VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_action (action),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS backups (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_size BIGINT DEFAULT 0,
                backup_type VARCHAR(20) DEFAULT 'full',
                status VARCHAR(20) DEFAULT 'completed',
                created_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS email_templates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                template_key VARCHAR(100) UNIQUE NOT NULL,
                subject VARCHAR(255) NOT NULL,
                body_html TEXT NOT NULL,
                is_active TINYINT(1) DEFAULT 1,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            "CREATE TABLE IF NOT EXISTS sms_templates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                template_key VARCHAR(100) UNIQUE NOT NULL,
                message TEXT NOT NULL,
                is_active TINYINT(1) DEFAULT 1,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        ];

        foreach ($tables as $sql) {
            try {
                $this->db->query($sql);
                $this->db->execute();
            } catch (\Throwable $e) { /* silent */ }
        }
    }

    /* ═══════════════════════════════════════════════════════════
       DASHBOARD
       ═══════════════════════════════════════════════════════════ */
    public function index() {
        $stats = [];
        try {
            $this->db->query("SELECT COUNT(*) AS c FROM users");
            $stats['users'] = (int)($this->db->single()['c'] ?? 0);

            $this->db->query("SELECT COUNT(*) AS c FROM products WHERE status='active'");
            $stats['products'] = (int)($this->db->single()['c'] ?? 0);

            $this->db->query("SELECT COUNT(*) AS c FROM orders");
            $stats['orders'] = (int)($this->db->single()['c'] ?? 0);

            $this->db->query("SELECT COUNT(*) AS c FROM farms WHERE is_active=1");
            $stats['farms'] = (int)($this->db->single()['c'] ?? 0);

            $this->db->query("SELECT COUNT(*) AS c FROM messages");
            $stats['messages'] = (int)($this->db->single()['c'] ?? 0);

            $this->db->query("SELECT COUNT(*) AS c FROM login_history WHERE login_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stats['logins_today'] = (int)($this->db->single()['c'] ?? 0);
        } catch (\Throwable $e) {
            $stats = array_merge([
                'users' => 0, 'products' => 0, 'orders' => 0,
                'farms' => 0, 'messages' => 0, 'logins_today' => 0,
            ], $stats);
        }

        $recentLogins = [];
        try {
            $this->db->query("SELECT lh.*, u.first_name, u.last_name, u.email
                              FROM login_history lh
                              LEFT JOIN users u ON u.id = lh.user_id
                              ORDER BY lh.login_at DESC LIMIT 8");
            $recentLogins = $this->db->resultSet();
        } catch (\Throwable $e) {}

        $this->view('admin/index', [
            'title'        => 'Admin Dashboard',
            'name'         => $_SESSION['name'],
            'stats'        => $stats,
            'recentLogins' => $recentLogins,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       USERS
       ═══════════════════════════════════════════════════════════ */
    public function users() {
        $this->db->query("SELECT u.*, r.name AS role_name, d.name AS district_name,
                                 (SELECT COUNT(*) FROM login_history WHERE user_id=u.id) AS login_count
                          FROM users u
                          LEFT JOIN roles r ON r.id = u.role_id
                          LEFT JOIN districts d ON d.id = u.district_id
                          ORDER BY u.created_at DESC LIMIT 500");
        $users = $this->db->resultSet();

        $this->db->query("SELECT id, name FROM roles ORDER BY id");
        $roles = $this->db->resultSet();

        $this->db->query("SELECT id, name FROM districts ORDER BY name");
        $districts = $this->db->resultSet();

        $this->view('admin/users', [
            'title' => 'Users', 'name' => $_SESSION['name'],
            'users' => $users, 'roles' => $roles, 'districts' => $districts,
        ]);
    }

    public function addUser() {
        try {
            $first  = trim($_POST['first_name'] ?? '');
            $last   = trim($_POST['last_name']  ?? '');
            $email  = trim($_POST['email']      ?? '');
            $phone  = trim($_POST['phone']      ?? '');
            $pwd    = $_POST['password']        ?? '';
            $role   = (int)($_POST['role_id']   ?? 0);
            $dist   = (int)($_POST['district_id'] ?? 0) ?: null;
            $status = $_POST['status'] ?? 'active';

            if (!$first || !$email || !$pwd || !$role) {
                $this->jsonError('Required: first name, email, password, role.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->jsonError('Invalid email address.');
            }
            if (strlen($pwd) < 6) {
                $this->jsonError('Password must be at least 6 characters.');
            }

            $this->db->query("SELECT id FROM users WHERE email = :e");
            $this->db->bind(':e', $email);
            if ($this->db->single()) {
                $this->jsonError('Email already in use.');
            }

            $hash = password_hash($pwd, PASSWORD_DEFAULT);

            $this->db->query("INSERT INTO users (uuid, first_name, last_name, email, phone, password_hash, role_id, district_id, status, created_at)
                              VALUES (:uuid, :f, :l, :e, :p, :h, :r, :d, :s, NOW())");
            $this->db->bind(':uuid', sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            ));
            $this->db->bind(':f', $first);
            $this->db->bind(':l', $last);
            $this->db->bind(':e', $email);
            $this->db->bind(':p', $phone);
            $this->db->bind(':h', $hash);
            $this->db->bind(':r', $role);
            $this->db->bind(':d', $dist);
            $this->db->bind(':s', $status);
            $this->db->execute();

            $newId = (int)$this->db->lastInsertId();
            $this->logActivity('user_created', "Created user: {$email} (ID {$newId})");

            $this->jsonOk(['message' => 'User created successfully.', 'id' => $newId]);
        } catch (\Throwable $e) {
            error_log("addUser error: " . $e->getMessage());
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function editUser() {
        try {
            $id     = (int)($_POST['id'] ?? 0);
            $first  = trim($_POST['first_name'] ?? '');
            $last   = trim($_POST['last_name']  ?? '');
            $email  = trim($_POST['email']      ?? '');
            $phone  = trim($_POST['phone']      ?? '');
            $role   = (int)($_POST['role_id']   ?? 0);
            $dist   = (int)($_POST['district_id'] ?? 0) ?: null;
            $status = $_POST['status'] ?? 'active';
            $pwd    = $_POST['password'] ?? '';

            if (!$id || !$first || !$email || !$role) {
                $this->jsonError('Missing required fields.');
            }

            $this->db->query("SELECT id FROM users WHERE email = :e AND id != :id");
            $this->db->bind(':e', $email);
            $this->db->bind(':id', $id);
            if ($this->db->single()) {
                $this->jsonError('Email already used by another user.');
            }

            if ($pwd !== '') {
                if (strlen($pwd) < 6) $this->jsonError('Password must be 6+ characters.');
                $hash = password_hash($pwd, PASSWORD_DEFAULT);
                $this->db->query("UPDATE users SET first_name=:f, last_name=:l, email=:e, phone=:p,
                                                    password_hash=:h, role_id=:r, district_id=:d, status=:s
                                  WHERE id=:id");
                $this->db->bind(':h', $hash);
            } else {
                $this->db->query("UPDATE users SET first_name=:f, last_name=:l, email=:e, phone=:p,
                                                    role_id=:r, district_id=:d, status=:s
                                  WHERE id=:id");
            }
            $this->db->bind(':f', $first);
            $this->db->bind(':l', $last);
            $this->db->bind(':e', $email);
            $this->db->bind(':p', $phone);
            $this->db->bind(':r', $role);
            $this->db->bind(':d', $dist);
            $this->db->bind(':s', $status);
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->logActivity('user_updated', "Updated user ID {$id}");
            $this->jsonOk(['message' => 'User updated.']);
        } catch (\Throwable $e) {
            error_log("editUser error: " . $e->getMessage());
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function deleteUser($id = 0) {
        try {
            $id = (int)$id;

            if ($id === (int)$_SESSION['user_id']) {
                $this->jsonError('Cannot delete your own account.');
            }

            $this->db->query("DELETE FROM users WHERE id=:id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->logActivity('user_deleted', "Deleted user ID {$id}");
            $this->jsonOk(['message' => 'User deleted.']);
        } catch (\Throwable $e) {
            error_log("deleteUser error: " . $e->getMessage());
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function toggleUser($id = 0) {
        try {
            $this->db->query("UPDATE users SET status = IF(status='active','suspended','active') WHERE id=:id");
            $this->db->bind(':id', (int)$id);
            $this->db->execute();
            $_SESSION['flash_success'] = 'User status updated.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Update failed: ' . $e->getMessage();
        }
        header("Location: " . BASE_URL . "/admin/users");
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       ROLES
       ═══════════════════════════════════════════════════════════ */
    public function roles() {
        try {
            $this->db->query("SELECT r.*,
                                     (SELECT COUNT(*) FROM users WHERE role_id=r.id) AS user_count,
                                     (SELECT COUNT(*) FROM role_permissions WHERE role_id=r.id) AS perm_count
                              FROM roles r ORDER BY r.id");
            $roles = $this->db->resultSet();

            $this->db->query("SELECT * FROM permissions ORDER BY module, permission_name");
            $permissions = $this->db->resultSet();

            $this->db->query("SELECT role_id, permission_id FROM role_permissions");
            $mapping = $this->db->resultSet();
        } catch (\Throwable $e) {
            $roles = $permissions = $mapping = [];
        }

        $this->view('admin/roles', [
            'title' => 'Roles & Permissions', 'name' => $_SESSION['name'],
            'roles' => $roles, 'permissions' => $permissions, 'mapping' => $mapping,
        ]);
    }

    public function addRole() {
        try {
            $name = trim($_POST['name'] ?? '');
            $desc = trim($_POST['description'] ?? '');

            if ($name === '') $this->jsonError('Role name is required.');
            if (mb_strlen($name) > 100) $this->jsonError('Role name is too long (max 100 characters).');

            $this->db->query("SELECT id FROM roles WHERE LOWER(name) = LOWER(:n)");
            $this->db->bind(':n', $name);
            if ($this->db->single()) $this->jsonError('A role with this name already exists.');

            $this->db->query("INSERT INTO roles (name, description) VALUES (:n, :d)");
            $this->db->bind(':n', $name);
            $this->db->bind(':d', $desc);
            $this->db->execute();

            $newId = (int)$this->db->lastInsertId();
            $this->logActivity('role_created', "Created role: {$name} (ID {$newId})");
            $this->jsonOk(['id' => $newId, 'message' => 'Role created successfully.']);
        } catch (\Throwable $e) {
            error_log("addRole error: " . $e->getMessage());
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function editRole() {
        try {
            $id   = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $desc = trim($_POST['description'] ?? '');

            if (!$id || $name === '') $this->jsonError('Missing role ID or name.');

            $this->db->query("SELECT id FROM roles WHERE LOWER(name) = LOWER(:n) AND id != :id");
            $this->db->bind(':n', $name);
            $this->db->bind(':id', $id);
            if ($this->db->single()) $this->jsonError('Another role already uses this name.');

            $this->db->query("UPDATE roles SET name=:n, description=:d WHERE id=:id");
            $this->db->bind(':n', $name);
            $this->db->bind(':d', $desc);
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->logActivity('role_updated', "Updated role ID {$id}");
            $this->jsonOk(['message' => 'Role updated successfully.']);
        } catch (\Throwable $e) {
            error_log("editRole error: " . $e->getMessage());
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function deleteRole($id = 0) {
        try {
            $id = (int)$id;

            if (!$id) $this->jsonError('Missing role ID.');
            if ($id === 7) $this->jsonError('Cannot delete the base Admin role.');

            $this->db->query("SELECT COUNT(*) AS c FROM users WHERE role_id=:id");
            $this->db->bind(':id', $id);
            $count = (int)($this->db->single()['c'] ?? 0);

            if ($count > 0) {
                $this->jsonError("Cannot delete — {$count} user(s) still have this role.");
            }

            $this->db->query("DELETE FROM role_permissions WHERE role_id=:id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->db->query("DELETE FROM roles WHERE id=:id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->logActivity('role_deleted', "Deleted role ID {$id}");
            $this->jsonOk(['message' => 'Role deleted.']);
        } catch (\Throwable $e) {
            error_log("deleteRole error: " . $e->getMessage());
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function togglePermission() {
        try {
            $roleId = (int)($_POST['role_id'] ?? 0);
            $permId = (int)($_POST['permission_id'] ?? 0);

            if (!$roleId || !$permId) $this->jsonError('Missing role or permission ID.');

            $this->db->query("SELECT 1 FROM role_permissions WHERE role_id=:r AND permission_id=:p");
            $this->db->bind(':r', $roleId);
            $this->db->bind(':p', $permId);
            $exists = $this->db->single();

            if ($exists) {
                $this->db->query("DELETE FROM role_permissions WHERE role_id=:r AND permission_id=:p");
                $this->db->bind(':r', $roleId);
                $this->db->bind(':p', $permId);
                $this->db->execute();
                $this->jsonOk(['action' => 'removed']);
            } else {
                $this->db->query("INSERT INTO role_permissions (role_id, permission_id) VALUES (:r, :p)");
                $this->db->bind(':r', $roleId);
                $this->db->bind(':p', $permId);
                $this->db->execute();
                $this->jsonOk(['action' => 'added']);
            }
        } catch (\Throwable $e) {
            error_log("togglePermission error: " . $e->getMessage());
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       TRANSLATIONS
       ═══════════════════════════════════════════════════════════ */
    public function translations() {
        $this->db->query("SELECT * FROM translation_categories ORDER BY name");
        $categories = $this->db->resultSet();

        $this->db->query("SELECT * FROM languages WHERE is_active=1 ORDER BY is_default DESC, name");
        $languages = $this->db->resultSet();

        $filter = $_GET['lang'] ?? 'en';
        $this->db->query("SELECT t.*, c.name AS category_name FROM translations t
                          LEFT JOIN translation_categories c ON c.id = t.category_id
                          WHERE t.lang_code = :l ORDER BY t.translation_key LIMIT 500");
        $this->db->bind(':l', $filter);
        $translations = $this->db->resultSet();

        $this->view('admin/translations', [
            'title' => 'Translations', 'name' => $_SESSION['name'],
            'categories' => $categories, 'languages' => $languages,
            'translations' => $translations, 'filter_lang' => $filter,
        ]);
    }

    public function addTranslation() {
        try {
            $key  = trim($_POST['translation_key'] ?? '');
            $lang = $_POST['lang_code'] ?? '';
            $text = $_POST['translated_text'] ?? '';
            $cat  = (int)($_POST['category_id'] ?? 0) ?: null;

            if (!$key || !$lang) $this->jsonError('Key and language required.');

            $this->db->query("INSERT INTO translations (translation_key, lang_code, translated_text, category_id, updated_by)
                              VALUES (:k, :l, :t, :c, :u)
                              ON DUPLICATE KEY UPDATE translated_text=VALUES(translated_text),
                                                      category_id=VALUES(category_id),
                                                      updated_by=VALUES(updated_by),
                                                      updated_at=NOW()");
            $this->db->bind(':k', $key);
            $this->db->bind(':l', $lang);
            $this->db->bind(':t', $text);
            $this->db->bind(':c', $cat);
            $this->db->bind(':u', $_SESSION['user_id']);
            $this->db->execute();
            $this->jsonOk(['message' => 'Translation saved.']);
        } catch (\Throwable $e) {
            error_log("addTranslation error: " . $e->getMessage());
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function editTranslation() {
        try {
            $id   = (int)($_POST['id'] ?? 0);
            $text = $_POST['translated_text'] ?? '';
            $cat  = (int)($_POST['category_id'] ?? 0) ?: null;

            if (!$id) $this->jsonError('Missing ID.');

            $this->db->query("UPDATE translations SET translated_text=:t, category_id=:c, updated_by=:u, updated_at=NOW() WHERE id=:id");
            $this->db->bind(':t', $text);
            $this->db->bind(':c', $cat);
            $this->db->bind(':u', $_SESSION['user_id']);
            $this->db->bind(':id', $id);
            $this->db->execute();
            $this->jsonOk(['message' => 'Updated.']);
        } catch (\Throwable $e) {
            error_log("editTranslation error: " . $e->getMessage());
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function deleteTranslation($id = 0) {
        try {
            $this->db->query("DELETE FROM translations WHERE id=:id");
            $this->db->bind(':id', (int)$id);
            $this->db->execute();
            $this->jsonOk(['message' => 'Deleted.']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function saveTranslation() {
        try {
            $key  = trim($_POST['key'] ?? '');
            $lang = $_POST['lang'] ?? '';
            $text = $_POST['text'] ?? '';
            if (!$key || !$lang) $this->jsonError('Missing key/lang');

            $this->db->query("INSERT INTO translations (translation_key, lang_code, translated_text, updated_by)
                              VALUES (:k, :l, :t, :u)
                              ON DUPLICATE KEY UPDATE translated_text=:t2, updated_by=:u2, updated_at=NOW()");
            $this->db->bind(':k', $key);
            $this->db->bind(':l', $lang);
            $this->db->bind(':t', $text);
            $this->db->bind(':t2', $text);
            $this->db->bind(':u', $_SESSION['user_id']);
            $this->db->bind(':u2', $_SESSION['user_id']);
            $this->db->execute();
            $this->jsonOk();
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       API KEYS
       ═══════════════════════════════════════════════════════════ */
    public function apiKeys() {
        $this->db->query("SELECT ak.*, u.first_name, u.last_name, u.email FROM api_keys ak
                          JOIN users u ON u.id = ak.user_id
                          ORDER BY ak.created_at DESC");
        $this->view('admin/api_keys', [
            'title' => 'API Keys',
            'name'  => $_SESSION['name'],
            'keys'  => $this->db->resultSet(),
        ]);
    }

    /* ───────────────────────────────────────────────────────────
       Generate API Key
       Returns JSON. Called via AJAX from admin/api_keys view.
       Response shape:
         { success, id, api_key, name, owner, message }
       ─────────────────────────────────────────────────────────── */
    public function generateApiKey() {
        try {
            $name   = trim($_POST['key_name'] ?? 'Default key');
            $userId = (int)($_POST['user_id'] ?? $_SESSION['user_id']);
            $key    = bin2hex(random_bytes(32));

            if ($name === '') {
                $this->jsonError('Please enter a name for the key.');
            }
            if (mb_strlen($name) > 80) {
                $this->jsonError('Key name is too long (max 80 characters).');
            }

            $this->db->query("INSERT INTO api_keys (user_id, key_name, api_key, created_at)
                              VALUES (:u, :n, :k, NOW())");
            $this->db->bind(':u', $userId);
            $this->db->bind(':n', $name);
            $this->db->bind(':k', $key);
            $this->db->execute();

            $newId = (int)$this->db->lastInsertId();

            $this->logActivity('api_key_created', "Generated API key: {$name} (ID {$newId})");

            $this->jsonOk([
                'id'      => $newId,
                'api_key' => $key,
                'name'    => $name,
                'owner'   => $_SESSION['name'] ?? 'You',
                'message' => 'API key generated.',
            ]);
        } catch (\Throwable $e) {
            error_log("generateApiKey error: " . $e->getMessage());
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function editApiKey() {
        try {
            $id   = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['key_name'] ?? '');
            if (!$id || !$name) $this->jsonError('Missing fields.');

            $this->db->query("UPDATE api_keys SET key_name=:n WHERE id=:id");
            $this->db->bind(':n', $name);
            $this->db->bind(':id', $id);
            $this->db->execute();
            $this->jsonOk(['message' => 'API key updated.']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function deleteApiKey($id = 0) {
        try {
            $this->db->query("DELETE FROM api_keys WHERE id=:id");
            $this->db->bind(':id', (int)$id);
            $this->db->execute();
            $this->jsonOk(['message' => 'API key deleted.']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       AI MODELS
       ═══════════════════════════════════════════════════════════ */
    public function models() {
        $this->db->query("SELECT * FROM model_versions ORDER BY model_type, version DESC");
        $this->view('admin/models', [
            'title'  => 'AI Models',
            'name'   => $_SESSION['name'],
            'models' => $this->db->resultSet(),
        ]);
    }

    public function addModel() {
        try {
            $name    = trim($_POST['model_name'] ?? '');
            $type    = trim($_POST['model_type'] ?? '');
            $version = trim($_POST['version'] ?? '1.0');
            $desc    = trim($_POST['description'] ?? '');
            $acc     = (float)($_POST['accuracy'] ?? 0);
            $status  = $_POST['status'] ?? 'active';

            if (!$name || !$type) $this->jsonError('Name and type required.');

            $this->db->query("INSERT INTO model_versions (model_name, model_type, version, description, accuracy, status, created_at)
                              VALUES (:n, :t, :v, :d, :a, :s, NOW())");
            $this->db->bind(':n', $name);
            $this->db->bind(':t', $type);
            $this->db->bind(':v', $version);
            $this->db->bind(':d', $desc);
            $this->db->bind(':a', $acc);
            $this->db->bind(':s', $status);
            $this->db->execute();
            $this->jsonOk(['message' => 'Model added.']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function editModel() {
        try {
            $id      = (int)($_POST['id'] ?? 0);
            $name    = trim($_POST['model_name'] ?? '');
            $type    = trim($_POST['model_type'] ?? '');
            $version = trim($_POST['version'] ?? '1.0');
            $desc    = trim($_POST['description'] ?? '');
            $acc     = (float)($_POST['accuracy'] ?? 0);
            $status  = $_POST['status'] ?? 'active';

            if (!$id || !$name) $this->jsonError('Missing fields.');

            $this->db->query("UPDATE model_versions SET model_name=:n, model_type=:t, version=:v,
                                                        description=:d, accuracy=:a, status=:s
                              WHERE id=:id");
            $this->db->bind(':n', $name);
            $this->db->bind(':t', $type);
            $this->db->bind(':v', $version);
            $this->db->bind(':d', $desc);
            $this->db->bind(':a', $acc);
            $this->db->bind(':s', $status);
            $this->db->bind(':id', $id);
            $this->db->execute();
            $this->jsonOk(['message' => 'Model updated.']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function deleteModel($id = 0) {
        try {
            $this->db->query("DELETE FROM model_versions WHERE id=:id");
            $this->db->bind(':id', (int)$id);
            $this->db->execute();
            $this->jsonOk(['message' => 'Model deleted.']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       ADMIN PROFILE
       ═══════════════════════════════════════════════════════════ */
    public function profile() {
        $this->db->query("SELECT * FROM users WHERE id = :id");
        $this->db->bind(':id', $_SESSION['user_id']);
        $profile = $this->db->single();

        $this->db->query("SELECT * FROM activity_log WHERE user_id = :id ORDER BY created_at DESC LIMIT 20");
        $this->db->bind(':id', $_SESSION['user_id']);
        $activity = $this->db->resultSet();

        $this->view('admin/profile', [
            'title'    => 'My Profile',
            'name'     => $_SESSION['name'],
            'profile'  => $profile,
            'activity' => $activity,
        ]);
    }

    public function updateProfile() {
        try {
            if (!empty($_POST['remove_avatar'])) {
                $this->db->query("SELECT avatar FROM users WHERE id = :id");
                $this->db->bind(':id', $_SESSION['user_id']);
                $old = $this->db->single();
                if (!empty($old['avatar'])) {
                    $path = __DIR__ . '/../../public/' . $old['avatar'];
                    if (file_exists($path)) @unlink($path);
                }
                $this->db->query("UPDATE users SET avatar = NULL WHERE id = :id");
                $this->db->bind(':id', $_SESSION['user_id']);
                $this->db->execute();
                $this->jsonOk(['message' => 'Avatar removed.']);
            }

            $first = trim($_POST['first_name'] ?? '');
            $last  = trim($_POST['last_name']  ?? '');
            $email = trim($_POST['email']      ?? '');
            $phone = trim($_POST['phone']      ?? '');
            $bio   = trim($_POST['bio']        ?? '');
            $tz    = $_POST['timezone'] ?? 'Africa/Kigali';
            $lang  = $_POST['language'] ?? 'en';
            $pwd   = $_POST['password'] ?? '';
            $tfa   = !empty($_POST['two_factor_enabled']) ? 1 : 0;

            if (!$first || !$email) $this->jsonError('Name and email required.');

            $this->db->query("SELECT id FROM users WHERE email = :e AND id != :id");
            $this->db->bind(':e', $email);
            $this->db->bind(':id', $_SESSION['user_id']);
            if ($this->db->single()) $this->jsonError('Email already in use.');

            $avatar = null;
            if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $this->jsonError('Avatar must be JPG/PNG/GIF/WEBP.');
                }
                if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
                    $this->jsonError('Avatar must be under 2 MB.');
                }
                $dir = __DIR__ . '/../../public/uploads/avatars/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $filename)) {
                    $avatar = 'uploads/avatars/' . $filename;
                }
            }

            $fields = [
                'first_name = :f', 'last_name = :l', 'email = :e', 'phone = :p',
                'bio = :b', 'timezone = :t', 'language = :g', 'two_factor_enabled = :tfa',
            ];
            $binds = [
                ':f' => $first, ':l' => $last, ':e' => $email, ':p' => $phone,
                ':b' => $bio, ':t' => $tz, ':g' => $lang, ':tfa' => $tfa,
            ];

            if ($avatar) {
                $fields[] = 'avatar = :a';
                $binds[':a'] = $avatar;
            }
            if ($pwd !== '') {
                if (strlen($pwd) < 6) $this->jsonError('Password must be 6+ characters.');
                $fields[] = 'password_hash = :h';
                $binds[':h'] = password_hash($pwd, PASSWORD_DEFAULT);
            }

            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
            $this->db->query($sql);
            foreach ($binds as $k => $v) $this->db->bind($k, $v);
            $this->db->bind(':id', $_SESSION['user_id']);
            $this->db->execute();

            $_SESSION['name'] = $first . ' ' . $last;
            $this->logActivity('profile_update', 'Updated own profile');
            $this->jsonOk(['message' => 'Profile updated.']);
        } catch (\Throwable $e) {
            error_log("updateProfile error: " . $e->getMessage());
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function signOutAll() {
        try {
            $this->logActivity('signout_all', 'Signed out from all devices');
            session_destroy();
            $this->jsonOk(['message' => 'Signed out from all devices.']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       SYSTEM BACKUP
       ═══════════════════════════════════════════════════════════ */
    public function backup() {
        $this->db->query("SELECT b.*, u.first_name, u.last_name FROM backups b
                          LEFT JOIN users u ON u.id = b.created_by
                          ORDER BY b.created_at DESC LIMIT 50");
        $backups = $this->db->resultSet();

        $uploadDir = __DIR__ . '/../../public/uploads/';
        $diskUsage = $this->dirSize($uploadDir);

        $this->view('admin/backup', [
            'title'     => 'System Backup',
            'name'      => $_SESSION['name'],
            'backups'   => $backups,
            'diskUsage' => $diskUsage,
            'dbSize'    => $this->getDbSize(),
        ]);
    }

    public function createBackup() {
        try {
            $type = $_POST['type'] ?? 'database';
            $backupDir = __DIR__ . '/../../backups/';
            if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);

            $timestamp = date('Y-m-d_H-i-s');
            $filename  = "backup_{$type}_{$timestamp}.sql";
            $filepath  = $backupDir . $filename;

            if ($type === 'database') {
                $this->exportDatabase($filepath);
            } elseif ($type === 'files') {
                $filename = "backup_files_{$timestamp}.zip";
                $filepath = $backupDir . $filename;
                $this->zipDirectory(__DIR__ . '/../../public/uploads/', $filepath);
            } else {
                $this->jsonError('Invalid backup type.');
            }

            if (!file_exists($filepath)) $this->jsonError('Backup file was not created.');

            $size = filesize($filepath);
            $this->db->query("INSERT INTO backups (filename, file_path, file_size, backup_type, status, created_by, created_at)
                              VALUES (:f, :p, :s, :t, 'completed', :u, NOW())");
            $this->db->bind(':f', $filename);
            $this->db->bind(':p', 'backups/' . $filename);
            $this->db->bind(':s', $size);
            $this->db->bind(':t', $type);
            $this->db->bind(':u', $_SESSION['user_id']);
            $this->db->execute();

            $this->logActivity('backup_created', "Created {$type} backup: {$filename}");
            $this->jsonOk([
                'message' => 'Backup created successfully.',
                'file'    => $filename,
                'size'    => $this->formatBytes($size),
            ]);
        } catch (\Throwable $e) {
            error_log("createBackup error: " . $e->getMessage());
            $this->jsonError('Backup failed: ' . $e->getMessage(), 500);
        }
    }

    public function downloadBackup($id = 0) {
        try {
            $this->db->query("SELECT * FROM backups WHERE id = :id");
            $this->db->bind(':id', (int)$id);
            $b = $this->db->single();
            if (!$b) { http_response_code(404); exit('Not found'); }

            $path = __DIR__ . '/../../' . $b['file_path'];
            if (!file_exists($path)) { http_response_code(404); exit('File missing'); }

            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $b['filename'] . '"');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            exit('Download failed.');
        }
    }

    public function deleteBackup($id = 0) {
        try {
            $this->db->query("SELECT * FROM backups WHERE id = :id");
            $this->db->bind(':id', (int)$id);
            $b = $this->db->single();
            if ($b) {
                $path = __DIR__ . '/../../' . $b['file_path'];
                if (file_exists($path)) @unlink($path);
                $this->db->query("DELETE FROM backups WHERE id = :id");
                $this->db->bind(':id', (int)$id);
                $this->db->execute();
                $this->logActivity('backup_deleted', 'Deleted backup: ' . $b['filename']);
            }
            $this->jsonOk(['message' => 'Backup deleted.']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       BRANDING
       ═══════════════════════════════════════════════════════════ */
    public function branding() {
        $settings = $this->getSettingsGroup('branding');
        $this->view('admin/branding', [
            'title' => 'System Branding',
            'name'  => $_SESSION['name'],
            'settings' => $settings,
        ]);
    }

    public function saveBranding() {
        try {
            $keys = ['site_name', 'site_tagline', 'primary_color', 'secondary_color', 'contact_email', 'contact_phone', 'footer_text'];
            foreach ($keys as $k) {
                if (isset($_POST[$k])) $this->saveSetting($k, trim($_POST[$k]), 'branding');
            }

            if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg', 'svg', 'webp'])) {
                    $dir = __DIR__ . '/../../public/uploads/branding/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $filename = 'logo_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $dir . $filename)) {
                        $this->saveSetting('site_logo', 'uploads/branding/' . $filename, 'branding');
                    }
                }
            }

            if (!empty($_FILES['favicon']['name']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'ico', 'svg'])) {
                    $dir = __DIR__ . '/../../public/uploads/branding/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $filename = 'favicon_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['favicon']['tmp_name'], $dir . $filename)) {
                        $this->saveSetting('site_favicon', 'uploads/branding/' . $filename, 'branding');
                    }
                }
            }

            $this->logActivity('branding_update', 'Updated branding');
            $this->jsonOk(['message' => 'Branding saved.']);
        } catch (\Throwable $e) {
            error_log("saveBranding error: " . $e->getMessage());
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       EMAIL
       ═══════════════════════════════════════════════════════════ */
    public function email() {
        $settings = $this->getSettingsGroup('email');
        $this->db->query("SELECT * FROM email_templates ORDER BY template_key");
        $templates = $this->db->resultSet();
        $this->view('admin/email', [
            'title' => 'Email Settings',
            'name'  => $_SESSION['name'],
            'settings'  => $settings,
            'templates' => $templates,
        ]);
    }

    public function saveEmail() {
        try {
            $keys = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure', 'smtp_from_name', 'smtp_from_email', 'email_enabled'];
            foreach ($keys as $k) {
                if (isset($_POST[$k])) $this->saveSetting($k, trim($_POST[$k]), 'email');
            }
            $this->logActivity('email_settings_update', 'Updated email settings');
            $this->jsonOk(['message' => 'Email settings saved.']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function sendTestEmail() {
        try {
            $to = trim($_POST['to'] ?? '');
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) $this->jsonError('Invalid email address.');

            $host = $this->getSetting('smtp_host');
            $user = $this->getSetting('smtp_user');
            $from = $this->getSetting('smtp_from_email', 'noreply@saip.rw');
            $fromName = $this->getSetting('smtp_from_name', 'SAIP');

            if (!$host || !$user) $this->jsonError('SMTP not configured. Fill in host and user first.');

            $subject = 'SAIP Test Email';
            $body = "This is a test email from your SAIP platform.";
            $headers = "From: {$fromName} <{$from}>\r\n";

            $sent = @mail($to, $subject, $body, $headers);
            if ($sent) {
                $this->logActivity('test_email_sent', "Sent to {$to}");
                $this->jsonOk(['message' => "Test email sent to {$to}."]);
            } else {
                $this->jsonError('mail() failed. Install PHPMailer for production SMTP.');
            }
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function updateEmailTemplate() {
        try {
            $id = (int)($_POST['id'] ?? 0);
            $subject = trim($_POST['subject'] ?? '');
            $body = $_POST['body_html'] ?? '';
            if (!$id || !$subject) $this->jsonError('Missing fields.');

            $this->db->query("UPDATE email_templates SET subject=:s, body_html=:b WHERE id=:id");
            $this->db->bind(':s', $subject);
            $this->db->bind(':b', $body);
            $this->db->bind(':id', $id);
            $this->db->execute();
            $this->jsonOk(['message' => 'Template updated.']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       SMS
       ═══════════════════════════════════════════════════════════ */
    public function sms() {
        $settings = $this->getSettingsGroup('sms');
        $this->db->query("SELECT * FROM sms_templates ORDER BY template_key");
        $templates = $this->db->resultSet();
        $this->view('admin/sms', [
            'title' => 'SMS Settings',
            'name'  => $_SESSION['name'],
            'settings'  => $settings,
            'templates' => $templates,
        ]);
    }

    public function saveSms() {
        try {
            $keys = ['sms_provider', 'sms_api_key', 'sms_api_secret', 'sms_sender_id', 'sms_enabled'];
            foreach ($keys as $k) {
                if (isset($_POST[$k])) $this->saveSetting($k, trim($_POST[$k]), 'sms');
            }
            $this->logActivity('sms_settings_update', 'Updated SMS settings');
            $this->jsonOk(['message' => 'SMS settings saved.']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function sendTestSms() {
        try {
            $phone = trim($_POST['phone'] ?? '');
            if (!$phone) $this->jsonError('Phone number required.');

            $apiKey = $this->getSetting('sms_api_key');
            if (!$apiKey) $this->jsonError('SMS API key missing. Configure it first.');

            $this->logActivity('test_sms_sent', "Queued SMS to {$phone}");
            $this->jsonOk(['message' => "Test SMS queued to {$phone} (integration pending)."]);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    public function updateSmsTemplate() {
        try {
            $id = (int)($_POST['id'] ?? 0);
            $message = trim($_POST['message'] ?? '');
            if (!$id || !$message) $this->jsonError('Missing fields.');

            $this->db->query("UPDATE sms_templates SET message=:m WHERE id=:id");
            $this->db->bind(':m', $message);
            $this->db->bind(':id', $id);
            $this->db->execute();
            $this->jsonOk(['message' => 'SMS template updated.']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       ACTIVITY LOG
       ═══════════════════════════════════════════════════════════ */
    public function activity() {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        $filterAction = $_GET['action'] ?? '';

        $sql = "SELECT a.*, u.first_name, u.last_name FROM activity_log a
                LEFT JOIN users u ON u.id = a.user_id WHERE 1=1";
        if ($filterAction) $sql .= " AND a.action = :act";
        $sql .= " ORDER BY a.created_at DESC LIMIT :l OFFSET :o";

        $this->db->query($sql);
        if ($filterAction) $this->db->bind(':act', $filterAction);
        $this->db->bind(':l', $perPage);
        $this->db->bind(':o', $offset);
        $logs = $this->db->resultSet();

        $countSql = "SELECT COUNT(*) AS c FROM activity_log WHERE 1=1";
        if ($filterAction) $countSql .= " AND action = :act";
        $this->db->query($countSql);
        if ($filterAction) $this->db->bind(':act', $filterAction);
        $total = (int)$this->db->single()['c'];

        $this->db->query("SELECT DISTINCT action FROM activity_log ORDER BY action");
        $actions = $this->db->resultSet();

        $this->view('admin/activity', [
            'title' => 'Activity Log', 'name' => $_SESSION['name'],
            'logs' => $logs, 'actions' => $actions, 'filter_action' => $filterAction,
            'page' => $page, 'total' => $total, 'per_page' => $perPage,
            'total_pages' => max(1, ceil($total / $perPage)),
        ]);
    }

    public function clearActivity() {
        try {
            $days = (int)($_POST['days'] ?? 30);
            $this->db->query("DELETE FROM activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL :d DAY)");
            $this->db->bind(':d', $days);
            $this->db->execute();
            $this->logActivity('activity_cleared', "Cleared logs older than {$days} days");
            $this->jsonOk(['message' => 'Old activity logs cleared.']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       SYSTEM HEALTH
       ═══════════════════════════════════════════════════════════ */
      public function health() {
        /* ── Safe disk space detection ──
           InfinityFree and many shared hosts disable disk_total_space()
           and disk_free_space() for security. We fall back to an
           estimate based on our own folders + database size. */
        $diskTotal = 0;
        $diskFree  = 0;

        if (function_exists('disk_total_space')) {
            @$diskTotal = (float) disk_total_space(__DIR__);
            @$diskFree  = (float) disk_free_space(__DIR__);
        }

        /* Fallback: assume 5 GB total on InfinityFree free tier, compute used */
        if ($diskTotal <= 0) {
            $diskTotal = 5 * 1024 * 1024 * 1024;   // 5 GB
            $used = $this->dirSize(__DIR__ . '/../../public/uploads/')
                  + $this->dirSize(__DIR__ . '/../../storage/')
                  + $this->dirSize(__DIR__ . '/../../backups/')
                  + $this->getDbSize();
            $diskFree = max(0, $diskTotal - $used);
        }

        $health = [
            'php_version'     => PHP_VERSION,
            'php_ok'          => version_compare(PHP_VERSION, '7.4', '>='),
            'disk_free'       => $diskFree,
            'disk_total'      => $diskTotal,
            'memory_limit'    => ini_get('memory_limit'),
            'upload_max'      => ini_get('upload_max_filesize'),
            'post_max'        => ini_get('post_max_size'),
            'max_execution'   => ini_get('max_execution_time'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'os'              => PHP_OS,
            'db_size'         => $this->getDbSize(),
            'uploads_size'    => $this->dirSize(__DIR__ . '/../../public/uploads/'),
            'backups_count'   => 0,
            'last_backup'     => null,
            'maintenance'     => $this->getSetting('maintenance_mode') === '1',
        ];

        try {
            $this->db->query("SELECT COUNT(*) AS c FROM backups");
            $health['backups_count'] = (int)$this->db->single()['c'];

            $this->db->query("SELECT created_at FROM backups ORDER BY created_at DESC LIMIT 1");
            $last = $this->db->single();
            $health['last_backup'] = $last['created_at'] ?? null;

            $this->db->query("
                SELECT
                    (SELECT COUNT(*) FROM users) AS users,
                    (SELECT COUNT(*) FROM nisr_datasets) AS datasets,
                    (SELECT COUNT(*) FROM activity_log) AS activity,
                    (SELECT COUNT(*) FROM products) AS products
            ");
            $health['counts'] = $this->db->single();
        } catch (\Throwable $e) {
            $health['counts'] = ['users' => 0, 'datasets' => 0, 'activity' => 0, 'products' => 0];
        }

        $this->view('admin/health', [
            'title'  => 'System Health',
            'name'   => $_SESSION['name'],
            'health' => $health,
        ]);
    }

    public function toggleMaintenance() {
        try {
            $current = $this->getSetting('maintenance_mode');
            $new = $current === '1' ? '0' : '1';
            $this->saveSetting('maintenance_mode', $new, 'system');
            $this->logActivity('maintenance_toggle', 'Maintenance ' . ($new === '1' ? 'enabled' : 'disabled'));
            $this->jsonOk(['enabled' => $new === '1']);
        } catch (\Throwable $e) {
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       SETTINGS HELPERS
       ═══════════════════════════════════════════════════════════ */
    private function getSetting($key, $default = '') {
        try {
            $this->db->query("SELECT setting_value FROM settings WHERE setting_key = :k");
            $this->db->bind(':k', $key);
            $row = $this->db->single();
            return $row ? $row['setting_value'] : $default;
        } catch (\Throwable $e) { return $default; }
    }

    private function saveSetting($key, $value, $group = 'general') {
        $this->db->query("INSERT INTO settings (setting_key, setting_value, setting_group, updated_by)
                          VALUES (:k, :v, :g, :u)
                          ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value),
                                                  updated_by=VALUES(updated_by),
                                                  updated_at=NOW()");
        $this->db->bind(':k', $key);
        $this->db->bind(':v', $value);
        $this->db->bind(':g', $group);
        $this->db->bind(':u', $_SESSION['user_id'] ?? null);
        return $this->db->execute();
    }

    private function getSettingsGroup($group) {
        $this->db->query("SELECT setting_key, setting_value FROM settings WHERE setting_group = :g");
        $this->db->bind(':g', $group);
        $rows = $this->db->resultSet();
        $out = [];
        foreach ($rows as $r) $out[$r['setting_key']] = $r['setting_value'];
        return $out;
    }

    private function logActivity($action, $description = '') {
        try {
            $this->db->query("INSERT INTO activity_log (user_id, action, description, ip_address, user_agent, created_at)
                              VALUES (:u, :a, :d, :ip, :ua, NOW())");
            $this->db->bind(':u', $_SESSION['user_id'] ?? null);
            $this->db->bind(':a', $action);
            $this->db->bind(':d', $description);
            $this->db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? null);
            $this->db->bind(':ua', substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255));
            return $this->db->execute();
        } catch (\Throwable $e) { return false; }
    }

    /* ═══════════════════════════════════════════════════════════
       BACKUP HELPERS
       ═══════════════════════════════════════════════════════════ */
    private function exportDatabase($filepath) {
        $fp = fopen($filepath, 'w');
        if (!$fp) throw new \Exception('Cannot create backup file.');

        fwrite($fp, "-- SAIP Backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $this->db->query("SHOW TABLES");
        $tables = $this->db->resultSet();

        foreach ($tables as $row) {
            $table = array_values($row)[0];
            $this->db->query("SHOW CREATE TABLE `{$table}`");
            $create = $this->db->single();
            fwrite($fp, "\n-- Table: {$table}\n");
            fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($fp, $create['Create Table'] . ";\n\n");

            $this->db->query("SELECT * FROM `{$table}`");
            $rows = $this->db->resultSet();
            foreach ($rows as $r) {
                $values = array_map(function ($v) {
                    if ($v === null) return 'NULL';
                    return "'" . addslashes($v) . "'";
                }, array_values($r));
                fwrite($fp, "INSERT INTO `{$table}` VALUES (" . implode(',', $values) . ");\n");
            }
        }
        fwrite($fp, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fp);
    }

    private function zipDirectory($source, $dest) {
        if (!class_exists('\ZipArchive')) throw new \Exception('ZipArchive extension not available.');
        $zip = new \ZipArchive();
        if ($zip->open($dest, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Cannot create zip.');
        }
        $source = rtrim($source, '/');
        if (!is_dir($source)) { $zip->close(); return; }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relative = substr($filePath, strlen($source) + 1);
                $zip->addFile($filePath, $relative);
            }
        }
        $zip->close();
    }

    private function dirSize($dir) {
        if (!is_dir($dir)) return 0;
        $size = 0;
        try {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
                $size += $file->getSize();
            }
        } catch (\Throwable $e) {}
        return $size;
    }

    private function getDbSize() {
        try {
            $this->db->query("SELECT ROUND(SUM(data_length + index_length)) AS size
                              FROM information_schema.tables
                              WHERE table_schema = DATABASE()");
            $row = $this->db->single();
            return (int)($row['size'] ?? 0);
        } catch (\Throwable $e) { return 0; }
    }

    private function formatBytes($bytes) {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    /* ═══════════════════════════════════════════════════════════
       DIAGNOSTIC
       ═══════════════════════════════════════════════════════════ */
    public function diagnose() {
        header('Content-Type: text/plain; charset=utf-8');
        echo "==== SAIP ADMIN DIAGNOSTIC ====\n\n";
        echo "PHP Version:     " . PHP_VERSION . "\n";
        echo "Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
        echo "Session role_id: " . ($_SESSION['role_id'] ?? 'NOT SET') . "\n";
        echo "Session name:    " . ($_SESSION['name'] ?? 'NOT SET') . "\n\n";

        echo "==== USERS TABLE COLUMNS ====\n";
        try {
            $this->db->query("SELECT COLUMN_NAME, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
                              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' ORDER BY ORDINAL_POSITION");
            foreach ($this->db->resultSet() as $c) {
                echo "{$c['COLUMN_NAME']} — {$c['COLUMN_TYPE']}\n";
            }
        } catch (\Throwable $e) { echo "Error: " . $e->getMessage() . "\n"; }

        echo "\n==== ADMIN USERS ====\n";
        try {
            $this->db->query("SELECT u.id, u.email, u.first_name, u.status, u.role_id, r.name AS role_name
                              FROM users u LEFT JOIN roles r ON r.id = u.role_id
                              WHERE u.role_id = 7 OR LOWER(r.name) LIKE '%admin%'");
            foreach ($this->db->resultSet() as $a) {
                echo "ID: {$a['id']} | {$a['email']} | Role: {$a['role_name']} | Status: {$a['status']}\n";
            }
        } catch (\Throwable $e) { echo "Error: " . $e->getMessage() . "\n"; }
        exit;
    }
    public function toggleModel($id = 0) {
    try {
        $id = (int)$id;
        if (!$id) $this->jsonError('Missing ID.');

        $this->db->query("UPDATE model_versions SET status = IF(status='active','archived','active') WHERE id=:id");
        $this->db->bind(':id', $id);
        $this->db->execute();

        $this->logActivity('model_toggle', "Toggled model ID {$id}");
        $this->jsonOk(['message' => 'Model status updated.']);
    } catch (\Throwable $e) {
        $this->jsonError('Server error: ' . $e->getMessage(), 500);
    }
}

    /* ═══════════════════════════════════════════════════════════
       DELEGATES — Crop Admin & Disease Admin
       ═══════════════════════════════════════════════════════════ */
    public function crops($id = null) {
        $ctrl = new CropAdminController();
        if ($id === null) $ctrl->index();
        else $ctrl->form($id);
    }

    public function uploads() {
        (new CropAdminController())->uploads();
    }

    public function clearUploads() {
        (new CropAdminController())->clearUploads();
    }

    public function diseases($id = null) {
        $ctrl = new DiseaseAdminController();
        if ($id === null) $ctrl->index();
        else $ctrl->form($id);
    }

    public function saveDisease() {
        (new DiseaseAdminController())->save();
    }

    public function deleteDisease($id = null) {
        (new DiseaseAdminController())->delete($id);
    }

    public function toggleDisease($id = null) {
        (new DiseaseAdminController())->toggle($id);
    }
}