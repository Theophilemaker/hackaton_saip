<?php
namespace App\Middleware;

use App\Core\Security;

class AuthMiddleware {

    /** Require login. Call at top of any protected controller. */
    public static function requireLogin() {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        // Verify session is still active in DB
        if (!Security::sessionBelongsToUser($_SESSION['user_id'])) {
            session_destroy();
            header('Location: ' . BASE_URL . '/auth/login?expired=1');
            exit;
        }
    }

    /** Require one of the given roles. */
    public static function requireRole($roles = []) {
        self::requireLogin();
        if (!in_array($_SESSION['role_id'] ?? 0, $roles)) {
            http_response_code(403);
            die('Access denied — insufficient privileges.');
        }
    }

    /** Verify CSRF for POST requests. */
    public static function requireCsrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrf();
        }
    }
}
?>