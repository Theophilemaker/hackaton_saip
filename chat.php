<?php
/**
 * htdocs/chat.php — Standalone Chat Entry Point
 *
 * Fixes 403 on /chat by giving Apache a real file to serve.
 * Loads config + core itself, dispatches to ChatController,
 * and always returns JSON for AJAX sub-routes.
 *
 * Handles:
 *   /chat                    → ChatController::index()
 *   /chat/search?q=...       → ChatController::search()
 *   /chat/users?q=...        → ChatController::users()
 *   /chat/start              → ChatController::start()
 *   /chat/send               → ChatController::send()
 *   /chat/poll               → ChatController::poll()
 *   /chat/info               → ChatController::info()
 *   /chat/typing             → ChatController::typing()
 *   /chat/typingUsers        → ChatController::typingUsers()
 *   /chat/pin, /mute, /leave, /editMessage, /deleteMessage, /react
 */

declare(strict_types=1);

/* ═══════════ 0. Error handling ═══════════ */
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

$HTDOCS = __DIR__;

/* ═══════════ 1. Locate project root ═══════════ */
$BASE_PATH = null;
foreach ([$HTDOCS, dirname($HTDOCS)] as $c) {
    if (is_dir($c . '/app') && is_file($c . '/config/config.php')) {
        $BASE_PATH = $c;
        break;
    }
}
if ($BASE_PATH === null) {
    http_response_code(500);
    exit('SAIP: app/ + config/config.php not found.');
}
if (!defined('BASE_PATH')) define('BASE_PATH', $BASE_PATH);
if (!defined('APP_PATH'))  define('APP_PATH',  BASE_PATH . '/app');

/* ═══════════ 2. Load config ═══════════ */
require_once BASE_PATH . '/config/config.php';

/* ═══════════ 3. Error log ═══════════ */
$logDir = BASE_PATH . '/storage/logs';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
@ini_set('error_log', $logDir . '/php_error.log');

/* ═══════════ 4. Autoloader ═══════════ */
spl_autoload_register(function ($class) {
    if (strncmp($class, 'App\\', 4) !== 0) return;
    $rel  = str_replace('\\', '/', substr($class, 4));
    $file = APP_PATH . '/' . $rel . '.php';
    if (is_file($file)) require_once $file;
});

/* ═══════════ 5. Session ═══════════ */
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    @session_set_cookie_params([
        'lifetime' => 0, 'path' => '/',
        'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax',
    ]);
    session_name('SAIPSESS');
    @session_start();
}

/* ═══════════ 6. Helpers ═══════════ */
if (!function_exists('e')) {
    function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

/* ═══════════ 7. Determine action from URL ═══════════ */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/chat', PHP_URL_PATH) ?: '/chat';
$uri = '/' . ltrim($uri, '/');

/* Strip /public prefix */
if (strpos($uri, '/public/') === 0) $uri = substr($uri, 7);

/* Extract action after /chat or /chat.php */
$action = 'index';
if ($uri === '/chat' || $uri === '/chat/' || $uri === '/chat.php') {
    $action = 'index';
} elseif (preg_match('#^/(chat|chat\.php)/([a-zA-Z_]+).*$#', $uri, $m)) {
    $action = $m[2];
}

/* ═══════════ 8. Security: require login ═══════════ */
$isAjax = (
    (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || $action !== 'index'
);

if (empty($_SESSION['user_id'])) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/auth/login');
    exit;
}

/* ═══════════ 9. Load helpers if present ═══════════ */
$helpers = APP_PATH . '/Helpers/functions.php';
if (is_file($helpers)) {
    try { require_once $helpers; } catch (\Throwable $e) {}
}

/* ═══════════ 10. Make sure CSRF token exists for AJAX ═══════════ */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ═══════════ 11. Load ChatController ═══════════ */
$controllerFile = APP_PATH . '/Controllers/ChatController.php';
if (!is_file($controllerFile)) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'ChatController.php missing']);
        exit;
    }
    http_response_code(500);
    exit('ChatController.php not found at ' . htmlspecialchars($controllerFile));
}
require_once $controllerFile;

$controllerClass = 'App\\Controllers\\ChatController';
if (!class_exists($controllerClass)) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'ChatController class missing']);
        exit;
    }
    http_response_code(500);
    exit('ChatController class not found');
}

/* ═══════════ 12. Route to the action ═══════════ */
/* Whitelist — only allowed methods reachable via URL */
$allowed = [
    'index', 'search', 'users', 'start', 'send', 'poll', 'info',
    'typing', 'typingUsers', 'pin', 'mute', 'leave',
    'editMessage', 'deleteMessage', 'react', 'joinForum',
];
if (!in_array($action, $allowed, true)) $action = 'index';

try {
    $controller = new $controllerClass();

    if (!method_exists($controller, $action)) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => "Method {$action} not found"]);
            exit;
        }
        http_response_code(404);
        exit("Chat action '{$action}' not found");
    }

    /* CSRF check for state-changing POST actions */
    $postActions = ['start', 'send', 'typing', 'pin', 'mute', 'leave',
                    'editMessage', 'deleteMessage', 'react'];
    if (in_array($action, $postActions, true) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $token    = $_POST['csrf'] ?? '';
        $expected = $_SESSION['csrf_token'] ?? '';
        /* Only enforce if both are non-empty and don't match */
        if ($expected !== '' && $token !== '' && !hash_equals($expected, $token)) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(419);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit;
        }
    }

    /* Call the action */
    $controller->$action();

} catch (\Throwable $e) {
    error_log('[chat.php] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error'   => $e->getMessage(),
            'file'    => basename($e->getFile()),
            'line'    => $e->getLine(),
        ]);
        exit;
    }

    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    ?><!DOCTYPE html>
    <html><head><meta charset="UTF-8"><title>SAIP Chat Error</title>
    <style>
        body { font-family: sans-serif; background: #0f1d2e; color: #e2e8f0; padding: 60px; text-align: center; }
        h1   { color: #f87171; font-size: 32px; }
        pre  { background: #1e293b; padding: 20px; border-radius: 8px; text-align: left; color: #a7f3d0;
               overflow: auto; font-size: 12px; max-width: 900px; margin: 20px auto; }
        a    { color: #10b981; }
    </style></head><body>
        <h1>SAIP Chat Error</h1>
        <p>The chat page failed to load.</p>
        <pre><?= e($e->getMessage()) ?>
File: <?= e($e->getFile()) ?>:<?= (int)$e->getLine() ?></pre>
        <p><a href="<?= e(defined('BASE_URL') ? BASE_URL : '/') ?>/">← Home</a></p>
    </body></html>
    <?php
    exit;
}