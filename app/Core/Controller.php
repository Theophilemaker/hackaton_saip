<?php
namespace App\Core;

/**
 * Base Controller
 *
 * Every controller extends this class.
 * Provides view rendering + model loading + shared helpers.
 */
class Controller {

    /**
     * Render a view.
     *
     * Behavior:
     *   • auth/* views  → bare HTML shell (they ship their own full-page CSS)
     *   • everything else → dashboard layout (header.php + footer.php)
     *
     * Signature: view($view, $data = [])
     * If a child controller overrides this method as a public URL action,
     * it MUST keep both parameters to satisfy PHP's LSP rules.
     */
    public function view($view, $data = []) {
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("View does not exist: " . htmlspecialchars((string)$view));
        }

        $data = is_array($data) ? $data : [];
        extract($data, EXTR_SKIP);
        $GLOBALS['__controller_data'] = $data;

        /* ── Auth views (login, register, forgot, reset, 2fa) are full-page
              standalone. They ship their own <style> for html/body and
              .auth-wrapper, so we render them inside a bare HTML shell
              instead of the dashboard layout. ───────────────────────── */
        if (strpos($view, 'auth/') === 0) {
            $lang  = $_SESSION['lang'] ?? (defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en');
            $title = (defined('APP_FULL_NAME') ? APP_FULL_NAME : 'SAIP') . ' — Rwanda';
            ?><!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10b981">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">
</head>
<body>
<?php require $viewFile; ?>
</body>
</html><?php
            return;
        }

        /* ── Default: dashboard layout (sidebar + topbar) ── */
        require __DIR__ . '/../Views/layouts/header.php';
        require $viewFile;
        require __DIR__ . '/../Views/layouts/footer.php';
    }

    /**
     * Render a view WITHOUT dashboard layout (auth pages, landing, etc.).
     */
    public function viewPublic($view, $data = []) {
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("View does not exist: " . htmlspecialchars((string)$view));
        }

        $data = is_array($data) ? $data : [];
        extract($data, EXTR_SKIP);
        $GLOBALS['__controller_data'] = $data;

        $publicHeader = __DIR__ . '/../Views/layouts/public_header.php';
        $publicFooter = __DIR__ . '/../Views/layouts/public_footer.php';

        if (file_exists($publicHeader)) require $publicHeader;
        require $viewFile;
        if (file_exists($publicFooter)) require $publicFooter;
    }

    /**
     * Load a model and return an instance.
     */
    public function model($model) {
        $modelFile = __DIR__ . '/../Models/' . $model . '.php';

        if (!file_exists($modelFile)) {
            http_response_code(500);
            die("Model does not exist: " . htmlspecialchars($model));
        }

        require_once $modelFile;

        $fqcn = 'App\\Models\\' . $model;
        return new $fqcn();
    }

    /**
     * Convert OpenWeatherMap icon code to emoji.
     */
    public function iconEmoji($iconCode) {
        $map = [
            '01d' => '☀️',  '01n' => '🌙',
            '02d' => '⛅',  '02n' => '☁️',
            '03d' => '☁️',  '03n' => '☁️',
            '04d' => '☁️',  '04n' => '☁️',
            '09d' => '🌧️', '09n' => '🌧️',
            '10d' => '🌦️', '10n' => '🌧️',
            '11d' => '⛈️', '11n' => '⛈️',
            '13d' => '❄️', '13n' => '❄️',
            '50d' => '🌫️', '50n' => '🌫️',
        ];
        return $map[$iconCode] ?? '🌤️';
    }

    /**
     * Send a JSON response and exit.
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to a URL and exit.
     */
    protected function redirect($url) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header("Location: " . $url);
        exit;
    }

    /**
     * Require the user to be logged in, else redirect to login.
     */
    protected function requireLogin() {
        if (empty($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . '/auth/login');
        }
    }

    /**
     * Require one of the given roles.
     */
    protected function requireRole($roles = []) {
        $this->requireLogin();
        if (!in_array((int)($_SESSION['role_id'] ?? 0), $roles, true)) {
            http_response_code(403);
            die('Access denied — insufficient privileges.');
        }
    }

    /**
     * Escape helper (HTML output safety).
     */
    protected function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Set a flash message (success/error) for the next request.
     */
    protected function flash($type, $message) {
        $_SESSION['flash_' . $type] = $message;
    }

    /**
     * Pull and clear all flash messages.
     */
    protected function getFlashes() {
        $out = [];
        foreach (['success', 'error', 'warning', 'info'] as $t) {
            if (!empty($_SESSION['flash_' . $t])) {
                $out[$t] = $_SESSION['flash_' . $t];
                unset($_SESSION['flash_' . $t]);
            }
        }
        return $out;
    }
}