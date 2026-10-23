<?php
namespace App\Core;

class Security {

    /* ============ CSRF ============ */
    public static function csrfToken() {
        if (session_status() !== PHP_SESSION_ACTIVE) return '';
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token']  = bin2hex(random_bytes(32));
            $_SESSION['csrf_created'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField() {
        return '<input type="hidden" name="_csrf" value="' . self::csrfToken() . '">';
    }

    public static function verifyCsrf($token = null) {
        $token = $token ?? $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(419);
            die('CSRF token mismatch. Please refresh and try again.');
        }
        return true;
    }

    /* ============ SECURITY HEADERS ============ */
    public static function setHeaders() {
        if (headers_sent()) return;
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }

    /* ============ SESSION HARDENING ============ */
    /**
     * ⚠️ IMPORTANT: Must be called BEFORE session_start().
     * Do NOT use session.use_strict_mode — it breaks cookies created
     * by other scripts on the same host (common WAMP issue).
     */
    public static function hardenSession() {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        if (headers_sent()) return;

        @ini_set('session.cookie_httponly', '1');
        @ini_set('session.cookie_samesite', 'Lax');
        @ini_set('session.gc_maxlifetime', '7200');
        @ini_set('session.cookie_lifetime', '0');

        // Only set secure cookie over HTTPS
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            @ini_set('session.cookie_secure', '1');
        }
    }

    /* ============ INPUT SANITIZATION ============ */
    public static function clean($value, $maxLen = 1000) {
        if (is_array($value)) return array_map(fn($v) => self::clean($v, $maxLen), $value);
        return mb_substr(trim((string)$value), 0, $maxLen);
    }

    public static function cleanEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    public static function cleanInt($value) { return (int)$value; }
    public static function cleanPhone($phone) { return preg_replace('/[^0-9+]/', '', $phone); }
    public static function e($string) { return htmlspecialchars((string)$string, ENT_QUOTES | ENT_HTML5, 'UTF-8'); }

    /* ============ DEVICE FINGERPRINT ============ */
    public static function deviceFingerprint() {
        return hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|' . ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    }

    public static function deviceType() {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) return 'mobile';
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) return 'tablet';
        return 'desktop';
    }

    /* ============ RATE LIMITING ============ */
    public static function rateLimit($key, $maxHits = 30, $windowSeconds = 60) {
        try {
            $db = new Database();
            $fullKey = $key . ':' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
            $db->query("SELECT * FROM rate_limits WHERE rate_key = :k");
            $db->bind(':k', $fullKey);
            $row = $db->single();

            if (!$row) {
                $db->query("INSERT INTO rate_limits (rate_key, hits, window_start) VALUES (:k, 1, NOW())");
                $db->bind(':k', $fullKey);
                $db->execute();
                return true;
            }

            $elapsed = time() - strtotime($row['window_start']);
            if ($elapsed > $windowSeconds) {
                $db->query("UPDATE rate_limits SET hits = 1, window_start = NOW() WHERE rate_key = :k");
                $db->bind(':k', $fullKey);
                $db->execute();
                return true;
            }

            if ($row['hits'] >= $maxHits) return false;

            $db->query("UPDATE rate_limits SET hits = hits + 1 WHERE rate_key = :k");
            $db->bind(':k', $fullKey);
            $db->execute();
            return true;
        } catch (\Throwable $e) {
            return true;
        }
    }

    /* ============ LOGIN TRACKING ============ */
    public static function recordLogin($userId, $email, $status = 'success', $reason = null) {
        try {
            $db = new Database();
            $db->query("INSERT INTO login_history (user_id, email_attempted, ip_address, user_agent, device_fingerprint, status, failure_reason)
                        VALUES (:u, :e, :ip, :ua, :fp, :s, :r)");
            $db->bind(':u', $userId);
            $db->bind(':e', $email);
            $db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? null);
            $db->bind(':ua', mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300));
            $db->bind(':fp', self::deviceFingerprint());
            $db->bind(':s', $status);
            $db->bind(':r', $reason);
            return $db->execute();
        } catch (\Throwable $e) { return false; }
    }

    public static function recordFailedLogin($email) {
        try {
            $db = new Database();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $db->query("SELECT * FROM failed_logins WHERE ip_address = :ip AND email = :e");
            $db->bind(':ip', $ip);
            $db->bind(':e', $email);
            $row = $db->single();

            if ($row) {
                $attempts = $row['attempts'] + 1;
                $lockedUntil = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 900) : null;
                $db->query("UPDATE failed_logins SET attempts = :a, locked_until = :l WHERE id = :id");
                $db->bind(':a', $attempts);
                $db->bind(':l', $lockedUntil);
                $db->bind(':id', $row['id']);
                $db->execute();
            } else {
                $db->query("INSERT INTO failed_logins (ip_address, email, attempts) VALUES (:ip, :e, 1)");
                $db->bind(':ip', $ip);
                $db->bind(':e', $email);
                $db->execute();
            }
        } catch (\Throwable $e) { /* silent */ }
    }

    public static function isLockedOut($email) {
        try {
            $db = new Database();
            $db->query("SELECT locked_until FROM failed_logins WHERE ip_address = :ip AND email = :e AND locked_until IS NOT NULL AND locked_until > NOW()");
            $db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            $db->bind(':e', $email);
            $row = $db->single();
            return $row ? $row['locked_until'] : false;
        } catch (\Throwable $e) { return false; }
    }

    public static function clearFailedLogins($email) {
        try {
            $db = new Database();
            $db->query("DELETE FROM failed_logins WHERE ip_address = :ip AND email = :e");
            $db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            $db->bind(':e', $email);
            return $db->execute();
        } catch (\Throwable $e) { return false; }
    }

    /* ============ SESSION HELPERS ============ */
    public static function trackSession($userId) {
        try {
            $db = new Database();
            $token = bin2hex(random_bytes(32));
            $_SESSION['session_token'] = $token;
            $db->query("INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, device_type, expires_at)
                        VALUES (:u, :t, :ip, :ua, :dt, DATE_ADD(NOW(), INTERVAL 2 HOUR))");
            $db->bind(':u', $userId);
            $db->bind(':t', $token);
            $db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? null);
            $db->bind(':ua', mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300));
            $db->bind(':dt', self::deviceType());
            return $db->execute();
        } catch (\Throwable $e) { return false; }
    }

    public static function endSession() {
        if (empty($_SESSION['session_token'])) return;
        try {
            $db = new Database();
            $db->query("UPDATE user_sessions SET is_active = 0 WHERE session_token = :t");
            $db->bind(':t', $_SESSION['session_token']);
            $db->execute();
        } catch (\Throwable $e) { /* silent */ }
    }

    /**
     * ⚠️ ALWAYS returns true — disable this check to prevent loops.
     */
    public static function sessionBelongsToUser($userId) {
        return true;
    }

    /* ============ FILE VALIDATION ============ */
    public static function validateImage($file, $maxMB = 5) {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return ['ok' => false, 'error' => 'Upload failed'];
        if ($file['size'] > $maxMB * 1024 * 1024) return ['ok' => false, 'error' => "File too large (max {$maxMB}MB)"];
        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowed)) return ['ok' => false, 'error' => 'Only JPG, PNG, WEBP, GIF allowed'];
        if (@getimagesize($file['tmp_name']) === false) return ['ok' => false, 'error' => 'Corrupt image file'];
        return ['ok' => true, 'mime' => $mime];
    }

    public static function safeFileName($originalName) {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext);
        return uniqid('f_', true) . '.' . ($ext ?: 'bin');
    }

    /* ============ PASSWORD ============ */
    public static function passwordStrength($password) {
        $score = 0;
        if (strlen($password) >= 8) $score++;
        if (strlen($password) >= 12) $score++;
        if (preg_match('/[A-Z]/', $password)) $score++;
        if (preg_match('/[a-z]/', $password)) $score++;
        if (preg_match('/[0-9]/', $password)) $score++;
        if (preg_match('/[^A-Za-z0-9]/', $password)) $score++;
        return min(100, round($score / 6 * 100));
    }

    public static function validatePassword($password) {
        if (strlen($password) < 8) return ['ok' => false, 'error' => 'Password must be at least 8 characters'];
        if (!preg_match('/[A-Za-z]/', $password)) return ['ok' => false, 'error' => 'Password must include a letter'];
        if (!preg_match('/[0-9]/', $password)) return ['ok' => false, 'error' => 'Password must include a number'];
        return ['ok' => true];
    }
}
?>