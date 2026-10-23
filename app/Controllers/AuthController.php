<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;

class AuthController extends Controller {

    /* ============================================================
     *  Helper: safe redirect (writes session to disk first)
     * ============================================================ */
    private function safeRedirect($path) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header("Location: " . BASE_URL . $path);
        exit;
    }

    /* ============================================================
     *  Helper: pick redirect target based on role
     * ============================================================ */
    private function redirectForUser($roleId) {
        // Role 7 (or role name Admin) → /admin
        // Everyone else → /dashboard
        return ((int)$roleId === 7) ? '/admin' : '/dashboard';
    }

    /* ============================================================
     *  LOGIN
     * ============================================================ */
    public function login() {
        // Already logged in → go to their home
        if (!empty($_SESSION['user_id'])) {
            $this->safeRedirect($this->redirectForUser($_SESSION['role_id'] ?? 0));
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view('auth/login');
            return;
        }

        // Rate limit (silent on failure)
        try { Security::rateLimit('login', 20, 60); } catch (\Throwable $e) {}

        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->view('auth/login', ['error' => 'Email and password are required.']);
            return;
        }

        try {
            $db = new \App\Core\Database();

            // Case-insensitive lookup
            $db->query("SELECT * FROM users WHERE LOWER(email) = :e LIMIT 1");
            $db->bind(':e', $email);
            $user = $db->single();

            error_log("=== LOGIN === email={$email} | found=" . ($user ? 'YES' : 'NO'));

            if (!$user) {
                try { Security::recordFailedLogin($email); } catch (\Throwable $e) {}
                $this->view('auth/login', ['error' => 'Invalid email or password.']);
                return;
            }

            // Verify password
            $verify = password_verify($password, $user['password_hash']);
            error_log("=== LOGIN === password_verify=" . ($verify ? 'PASS' : 'FAIL'));

            if (!$verify) {
                try { Security::recordFailedLogin($email); } catch (\Throwable $e) {}
                $this->view('auth/login', ['error' => 'Invalid email or password.']);
                return;
            }

            // Status check
            if (isset($user['status']) && $user['status'] !== 'active') {
                $this->view('auth/login', ['error' => 'Your account is ' . $user['status'] . '. Contact support.']);
                return;
            }

            // ============ LOGIN SUCCESS ============
            // NOTE: NO session_regenerate_id() — it breaks on WAMP+FastCGI as your comment noted

            $_SESSION['user_id']            = (int)$user['id'];
            $_SESSION['role_id']            = (int)($user['role_id'] ?? 1);
            $_SESSION['name']               = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            $_SESSION['email']              = $user['email'];
            $_SESSION['preferred_language'] = $user['preferred_language'] ?? 'en';
            $_SESSION['lang']               = $user['preferred_language'] ?? 'en';
            $_SESSION['last_activity']      = time();
            $_SESSION['logged_in']          = true;

            // Role name
            try {
                $db->query("SELECT name FROM roles WHERE id = :id LIMIT 1");
                $db->bind(':id', $_SESSION['role_id']);
                $r = $db->single();
                if ($r) $_SESSION['role_name'] = $r['name'];
            } catch (\Throwable $e) {}

            // Audit log
            try {
                Security::recordLogin($user['id'], $email, 'success');
                Security::clearFailedLogins($email);
            } catch (\Throwable $e) {}

            // Remember-me cookie
            if (!empty($_POST['remember'])) {
                try {
                    $selector  = bin2hex(random_bytes(16));
                    $validator = bin2hex(random_bytes(32));
                    $db->query("INSERT INTO remember_tokens
                                (user_id, selector, validator_hash, expires_at)
                                VALUES (:u, :s, :vh, DATE_ADD(NOW(), INTERVAL 30 DAY))");
                    $db->bind(':u', $user['id']);
                    $db->bind(':s', $selector);
                    $db->bind(':vh', hash('sha256', $validator));
                    $db->execute();

                    setcookie('saip_remember', $selector . ':' . $validator, [
                        'expires'  => time() + 60 * 60 * 24 * 30,
                        'path'     => '/',
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                } catch (\Throwable $e) {}
            }

            // Save session & redirect
            $target = $this->redirectForUser($_SESSION['role_id']);
            error_log("=== LOGIN === success | redirect to {$target} | SESSION=" . json_encode($_SESSION));

            $this->safeRedirect($target);

        } catch (\Throwable $e) {
            error_log("LOGIN ERROR: " . $e->getMessage());
            $this->view('auth/login', ['error' => 'Server error: ' . $e->getMessage()]);
            return;
        }
    }

    /* ============================================================
     *  REGISTER
     * ============================================================ */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view('auth/register');
            return;
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $email     = strtolower(trim($_POST['email'] ?? ''));
        $phone     = trim($_POST['phone'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';
        $roleId    = (int)($_POST['role_id'] ?? 1);
        $language  = $_POST['language'] ?? 'en';

        // Validation
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            $this->view('auth/register', ['error' => 'All required fields must be filled.']);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view('auth/register', ['error' => 'Please enter a valid email address.']);
            return;
        }
        if (strlen($password) < 8) {
            $this->view('auth/register', ['error' => 'Password must be at least 8 characters.']);
            return;
        }
        if ($password !== $confirm) {
            $this->view('auth/register', ['error' => 'Passwords do not match.']);
            return;
        }

        // Normalize phone
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        $phone = substr($phone, 0, 20);
        if ($phone === '' || $phone === '+') $phone = null;

        try {
            $db = new \App\Core\Database();

            // Duplicate email
            $db->query("SELECT id FROM users WHERE LOWER(email) = :e LIMIT 1");
            $db->bind(':e', $email);
            if ($db->single()) {
                $this->view('auth/register', ['error' => 'Email is already registered. Try logging in instead.']);
                return;
            }

            // Duplicate phone
            if ($phone !== null) {
                $db->query("SELECT id FROM users WHERE phone = :p LIMIT 1");
                $db->bind(':p', $phone);
                if ($db->single()) {
                    $this->view('auth/register', ['error' => 'Phone number is already registered.']);
                    return;
                }
            }

            // Insert user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

            $db->query("INSERT INTO users
                (uuid, first_name, last_name, email, phone, password_hash,
                 role_id, preferred_language, status, verification_status, created_at)
                VALUES (:u, :fn, :ln, :e, :p, :h, :r, :l, 'active', 'unverified', NOW())");
            $db->bind(':u',  $uuid);
            $db->bind(':fn', $firstName);
            $db->bind(':ln', $lastName);
            $db->bind(':e',  $email);
            $db->bind(':p',  $phone);
            $db->bind(':h',  $hash);
            $db->bind(':r',  $roleId);
            $db->bind(':l',  $language);
            $db->execute();

            $userId = $db->lastInsertId();

            // Non-fatal preference rows
            try {
                $db->query("INSERT INTO user_preferences (user_id, language_code) VALUES (:u, :l)");
                $db->bind(':u', $userId);
                $db->bind(':l', $language);
                $db->execute();

                $db->query("INSERT INTO user_profiles (user_id) VALUES (:u)");
                $db->bind(':u', $userId);
                $db->execute();
            } catch (\Throwable $e) {}

            $this->safeRedirect('/auth/login?registered=1');

        } catch (\Throwable $e) {
            error_log("REGISTER ERROR: " . $e->getMessage());
            $this->view('auth/register', ['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    /* ============================================================
 *  LOGOUT — redirect to home page
 * ============================================================ */
public function logout() {
    // Record the logout event before destroying the session
    if (!empty($_SESSION['user_id'])) {
        try {
            Security::recordLogin($_SESSION['user_id'], '', 'logout');
            Security::endSession();
        } catch (\Throwable $e) { /* silent */ }
    }

    // Clear all session data
    $_SESSION = [];

    // Clear the remember-me cookie
    if (!empty($_COOKIE['saip_remember'])) {
        setcookie('saip_remember', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    // Destroy the session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    // ✅ Redirect to the HOME PAGE (not login)
    header("Location: " . BASE_URL . "/");
    exit;
}

    /* ============================================================
     *  FORGOT PASSWORD
     * ============================================================ */
    public function forgot() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = strtolower(trim($_POST['email'] ?? ''));

            if ($email) {
                try {
                    $db = new \App\Core\Database();
                    $token = bin2hex(random_bytes(32));
                    $db->query("INSERT INTO password_resets (email, token_hash, expires_at)
                                VALUES (:e, :h, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
                    $db->bind(':e', $email);
                    $db->bind(':h', hash('sha256', $token));
                    $db->execute();
                    // TODO: send email with reset link
                } catch (\Throwable $e) {
                    error_log("FORGOT ERROR: " . $e->getMessage());
                }
            }

            $this->safeRedirect('/auth/login?forgot=1');
        }

        $this->view('auth/forgot');
    }

    /* ============================================================
     *  RESET PASSWORD
     * ============================================================ */
    public function reset() {
        $email = strtolower(trim($_GET['email'] ?? ''));
        $token = $_GET['token'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm'] ?? '';

            if (strlen($password) < 8) {
                $this->view('auth/reset', [
                    'error' => 'Password must be at least 8 characters.',
                    'email' => $email, 'token' => $token
                ]);
                return;
            }
            if ($password !== $confirm) {
                $this->view('auth/reset', [
                    'error' => 'Passwords do not match.',
                    'email' => $email, 'token' => $token
                ]);
                return;
            }

            try {
                $db = new \App\Core\Database();
                $db->query("SELECT * FROM password_resets
                            WHERE email = :e AND token_hash = :h
                            AND used = 0 AND expires_at > NOW()");
                $db->bind(':e', $email);
                $db->bind(':h', hash('sha256', $token));
                $row = $db->single();

                if (!$row) {
                    $this->view('auth/reset', ['error' => 'Invalid or expired reset link.']);
                    return;
                }

                $db->query("UPDATE users SET password_hash = :p WHERE LOWER(email) = :e");
                $db->bind(':p', password_hash($password, PASSWORD_DEFAULT));
                $db->bind(':e', $email);
                $db->execute();

                $db->query("UPDATE password_resets SET used = 1 WHERE id = :id");
                $db->bind(':id', $row['id']);
                $db->execute();

                $this->safeRedirect('/auth/login?reset=1');

            } catch (\Throwable $e) {
                $this->view('auth/reset', ['error' => 'Error: ' . $e->getMessage()]);
                return;
            }
        }

        $this->view('auth/reset', ['email' => $email, 'token' => $token]);
    }
}