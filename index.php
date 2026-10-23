<?php
/**
 * htdocs/index.php — Standalone front controller
 * SAIP · Smart Agriculture Intelligence Platform
 *
 * Fixes:
 *   ✅ POST to /index.php?url=auth/register now reaches AuthController
 *   ✅ Auth pages share base CSS variables & classes
 *   ✅ Asset URLs auto-prefixed with /public/
 */

/* ═══════════════════════════════════════════════════════════════
   OUTPUT BUFFER
   ═══════════════════════════════════════════════════════════════ */
ob_start(function ($html) {

    /* 1. Translation keys */
    $html = str_replace(
        [
            '>app_name<', '>app_tagline<', '>app_fullname<',
            '>auth.login<', '>auth.register<', '>auth.logout<',
            '>auth.email<', '>auth.password<', '>auth.confirm_password<',
            '>auth.first_name<', '>auth.last_name<', '>auth.phone<',
            '>auth.role<', '>auth.language<', '>auth.create<',
            '>auth.register_sub<', '>auth.have_account<',
            '>auth.forgot<', '>auth.remember<',
        ],
        [
            '>SAIP<',
            '>Smart Agriculture Intelligence Platform<',
            '>Smart Agriculture Intelligence Platform<',
            '>Login<', '>Register<', '>Logout<',
            '>Email<', '>Password<', '>Confirm Password<',
            '>First Name<', '>Last Name<', '>Phone Number<',
            '>Role<', '>Language<', '>Create Account<',
            '>Join thousands of Rwandan farmers<',
            '>Already have an account?<',
            '>Forgot password?<', '>Remember me<',
        ],
        $html
    );

    /* 2. Prefix static asset URLs with /public/ */
    $__scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
    $__host   = $_SERVER['HTTP_HOST'] ?? 'smartap.totalh.net';
    $__base   = $__scheme . '://' . $__host;

    $html = str_replace(
        [
            'href="' . $__base . '/css/',
            'href="' . $__base . '/js/',
            'src="'  . $__base . '/js/',
            'href="' . $__base . '/assets/',
            'src="'  . $__base . '/assets/',
            'href="' . $__base . '/manifest.json',
            'src="'  . $__base . '/sw.js',
            'href="' . $__base . '/uploads/',
            'src="'  . $__base . '/uploads/',
            'href="' . $__base . '/data/',
            'src="'  . $__base . '/data/',
            'href="' . $__base . '/icons/',
            'src="'  . $__base . '/icons/',
            'href="' . $__base . '/images/',
            'src="'  . $__base . '/images/',
        ],
        [
            'href="' . $__base . '/public/css/',
            'href="' . $__base . '/public/js/',
            'src="'  . $__base . '/public/js/',
            'href="' . $__base . '/public/assets/',
            'src="'  . $__base . '/public/assets/',
            'href="' . $__base . '/public/manifest.json',
            'src="'  . $__base . '/public/sw.js',
            'href="' . $__base . '/public/uploads/',
            'src="'  . $__base . '/public/uploads/',
            'href="' . $__base . '/public/data/',
            'src="'  . $__base . '/public/data/',
            'href="' . $__base . '/public/icons/',
            'src="'  . $__base . '/public/icons/',
            'href="' . $__base . '/public/images/',
            'src="'  . $__base . '/public/images/',
        ],
        $html
    );

    return $html;
});

/* ═══ 0. Errors → log only ═══ */
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
$__logDir = __DIR__ . '/storage/logs';
if (!is_dir($__logDir)) @mkdir($__logDir, 0755, true);
@ini_set('error_log', $__logDir . '/php_error.log');

try {

/* ═══ 1. BASE_URL (no /public) ═══ */
if (!defined('BASE_URL')) {
    $__scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
    $__host   = $_SERVER['HTTP_HOST'] ?? 'smartap.totalh.net';
    define('BASE_URL', $__scheme . '://' . $__host);
}

/* ═══ 2. Config ═══ */
if (is_file(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
}

/* ═══ 3. Session ═══ */
if (session_status() === PHP_SESSION_NONE) {
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.cookie_samesite', 'Lax');
    @ini_set('session.use_strict_mode', '0');
    @session_start();
}
if (empty($_SESSION['lang'])) $_SESSION['lang'] = 'en';

/* ═══ 4. Seed translations ═══ */
$GLOBALS['__lang'] = [
    'app_name'     => 'SAIP',
    'app_tagline'  => 'Smart Agriculture Intelligence Platform',
    'app_fullname' => 'Smart Agriculture Intelligence Platform',
    'auth' => [
        'login'            => 'Login',
        'register'         => 'Register',
        'logout'           => 'Logout',
        'email'            => 'Email',
        'password'         => 'Password',
        'confirm_password' => 'Confirm Password',
        'first_name'       => 'First Name',
        'last_name'        => 'Last Name',
        'phone'            => 'Phone Number',
        'role'             => 'Role',
        'language'         => 'Language',
        'create'           => 'Create Account',
        'register_sub'     => 'Join thousands of Rwandan farmers',
        'have_account'     => 'Already have an account?',
        'forgot'           => 'Forgot password?',
        'remember'         => 'Remember me',
    ],
];

foreach ([
    __DIR__ . '/app/Languages/' . $_SESSION['lang'] . '.json',
    __DIR__ . '/app/Languages/en.json',
] as $__lf) {
    if (is_file($__lf)) {
        $__j = json_decode((string)file_get_contents($__lf), true);
        if (is_array($__j)) {
            $GLOBALS['__lang'] = array_replace_recursive($GLOBALS['__lang'], $__j);
            break;
        }
    }
}

/* ═══ 5. __() and e() ═══ */
if (!function_exists('__')) {
    function __($key, $fallback = null) {
        $lang = $GLOBALS['__lang'] ?? [];
        $node = $lang;
        foreach (explode('.', $key) as $p) {
            if (!is_array($node) || !array_key_exists($p, $node)) { $node = null; break; }
            $node = $node[$p];
        }
        if (is_string($node) && $node !== '') return $node;
        if ($fallback !== null) return $fallback;
        $known = [
            'app_name'    => 'SAIP',
            'app_tagline' => 'Smart Agriculture Intelligence Platform',
        ];
        if (isset($known[$key])) return $known[$key];
        $parts = explode('.', $key);
        return ucfirst(str_replace(['_','-'], ' ', end($parts)));
    }
}
if (!function_exists('e')) {
    function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

/* ═══ 6. Autoloader ═══ */
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') !== 0) return;
    $rel  = str_replace('\\', '/', substr($class, 4));
    $file = __DIR__ . '/app/' . $rel . '.php';
    if (is_file($file)) require_once $file;
});

/* ═══ 7. Core files ═══ */
foreach ([
    '/app/Core/Database.php',
    '/app/Core/Security.php',
    '/app/Core/Language.php',
    '/app/Helpers/functions.php',
    '/app/Helpers/Translator.php',
    '/app/Core/Controller.php',
] as $__f) {
    $__p = __DIR__ . $__f;
    if (is_file($__p)) {
        try { require_once $__p; }
        catch (\Throwable $__e) { error_log('[SAIP] ' . $__f . ': ' . $__e->getMessage()); }
    }
}

/* ═══ 8. Language shim ═══ */
if (!class_exists('App\\Core\\Language', false)) {
    if (!class_exists('SaipLanguageStub', false)) {
        class SaipLanguageStub {
            public static function init()    {}
            public static function current() { return $_SESSION['lang'] ?? 'en'; }
            public static function set($c)   { $_SESSION['lang'] = $c; }
            public static function supported() {
                return [
                    'en' => ['name' => 'English',     'flag' => '🇬🇧'],
                    'rw' => ['name' => 'Kinyarwanda', 'flag' => '🇷🇼'],
                    'fr' => ['name' => 'Français',    'flag' => '🇫🇷'],
                    'sw' => ['name' => 'Kiswahili',   'flag' => '🇹🇿'],
                ];
            }
        }
    }
    @class_alias('SaipLanguageStub', 'App\\Core\\Language');
}

/* ═══ 9. Parse URI ═══ */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$uri = '/' . ltrim($uri, '/');
if (substr($uri, -10) === '/index.php') $uri = substr($uri, 0, -10) ?: '/';
if ($uri === '/public')                 $uri = '/';
elseif (strpos($uri, '/public/') === 0) $uri = substr($uri, 7);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/* ═══ 10. POST auth → framework ═══
   Handles both:
     POST /auth/register
     POST /index.php?url=auth/register   ← your register form's action */
if ($method === 'POST' && (
    strpos($uri, '/auth/') === 0 ||
    (!empty($_GET['url']) && strpos($_GET['url'], 'auth/') === 0)
)) {
    $routeUrl = !empty($_GET['url']) ? $_GET['url'] : ltrim($uri, '/');
    $_GET['url']             = $routeUrl;
    $_SERVER['QUERY_STRING'] = 'url=' . rawurlencode($routeUrl);

    if (is_file(__DIR__ . '/app/Core/App.php')) {
        require_once __DIR__ . '/app/Core/App.php';
        new \App\Core\App();
        exit;
    }
}

/* ═══ 11. HOME → landing page ═══ */
if ($uri === '/' || $uri === '') {
    $stats = ['farmers' => 1240, 'farms' => 3400, 'production' => 4800000, 'districts' => 30];
    try {
        if (class_exists('App\\Core\\Database')) {
            $db = new \App\Core\Database();
            foreach ([
                "SELECT COUNT(*) AS c FROM users"                     => 'farmers',
                "SELECT COUNT(*) AS c FROM farms"                     => 'farms',
                "SELECT COALESCE(SUM(quantity),0) AS c FROM harvests" => 'production',
                "SELECT COUNT(DISTINCT district_id) AS c FROM farms"  => 'districts',
            ] as $sql => $k) {
                try { $db->query($sql); $r = $db->single();
                      if (is_array($r) && isset($r['c'])) $stats[$k] = (float)$r['c']; }
                catch (\Throwable $e) {}
            }
        }
    } catch (\Throwable $e) {}

    $data  = ['stats' => $stats];
    $title = 'SAIP — Smart Agriculture Intelligence Platform';
    $lang  = $_SESSION['lang'];

    $headerFile = __DIR__ . '/app/Views/layouts/public_header.php';
    $landingF   = __DIR__ . '/app/Views/landing.php';
    $footerFile = __DIR__ . '/app/Views/layouts/public_footer.php';

    if (is_file($headerFile)) {
        try { include $headerFile; }
        catch (\Throwable $e) { error_log('[SAIP] header: ' . $e->getMessage()); }
    } else {
        ?><!DOCTYPE html><html lang="<?= e($lang) ?>"><head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?></title>
        <link rel="stylesheet" href="<?= e(BASE_URL) ?>/css/style.css">
        <link rel="stylesheet" href="<?= e(BASE_URL) ?>/css/landing.css">
        </head><body class="landing-body"><?php
    }

    if (is_file($landingF)) {
        try { include $landingF; }
        catch (\Throwable $e) {
            error_log('[SAIP] landing: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            echo '<div style="padding:60px;text-align:center;color:#f87171;font-family:sans-serif">'
               . '<h1>Landing error</h1><p>' . e($e->getMessage()) . '</p></div>';
        }
    } else {
        echo '<p style="padding:40px;text-align:center;color:#f87171">landing.php not found</p>';
    }

    if (is_file($footerFile)) {
        try { include $footerFile; }
        catch (\Throwable $e) { error_log('[SAIP] footer: ' . $e->getMessage()); }
    } else {
        echo '</body></html>';
    }
    exit;
}

/* ═══ 12. GET auth → styled shell + view ═══ */
$authMap = [
    '/auth/login'    => 'login',
    '/auth/register' => 'register',
    '/auth/forgot'   => 'forgot',
    '/auth/reset'    => 'reset',
    '/auth/2fa'      => '2fa',
    '/login'         => 'login',
    '/register'      => 'register',
];

if (isset($authMap[$uri])) {
    $viewFile = __DIR__ . '/app/Views/auth/' . $authMap[$uri] . '.php';
    if (!is_file($viewFile)) {
        http_response_code(500);
        exit('Auth view missing: ' . htmlspecialchars($viewFile));
    }

    $data = [
        'error'      => $_SESSION['login_error'] ?? $_SESSION['register_error'] ?? null,
        'csrf_token' => $_SESSION['csrf_token'] ?? bin2hex(random_bytes(16)),
    ];
    unset($_SESSION['login_error'], $_SESSION['register_error']);

    $title = ucfirst($authMap[$uri]) . ' — SAIP · Smart Agriculture Intelligence Platform';
    $lang  = $_SESSION['lang'];

    $GLOBALS['__lang']['app_name']    = 'SAIP';
    $GLOBALS['__lang']['app_tagline'] = 'Smart Agriculture Intelligence Platform';

    ?><!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#10b981">
<title><?= e($title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">
<style>
/* ═══════════════════════════════════════════════════════════════
   SHARED AUTH STYLES — always loaded for every auth view.
   Login/Register add their own rules on top.
   ═══════════════════════════════════════════════════════════════ */
:root {
    --bg-deep: #081C15;
    --bg-mid: #0b1120;
    --accent-green: #10b981;
    --accent-lime: #84cc16;
    --accent: #10b981;
    --primary: #10b981;
    --text-main: #f1f5f9;
    --text-sub: #94a3b8;
    --border: rgba(255, 255, 255, 0.08);
}
* { box-sizing: border-box; }
html, body {
    margin: 0; padding: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--bg-deep);
    color: var(--text-main);
}
body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.auth-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    max-width: 100%;
    min-height: 100vh;
    padding: 20px;
    background: linear-gradient(135deg, #081C15 0%, #0F3D2E 50%, #0b1120 100%);
}
.glass-card {
    background: rgba(30, 41, 59, 0.75) !important;
    backdrop-filter: blur(20px) !important;
    -webkit-backdrop-filter: blur(20px) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 24px !important;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.5) !important;
}
.auth-card {
    max-width: 440px;
    width: 100%;
    padding: 40px 36px;
    box-sizing: border-box;
    animation: authCardIn 0.5s ease-out;
    position: relative;
    z-index: 1;
}
@keyframes authCardIn {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
.auth-logo {
    display: flex;
    align-items: center;
    gap: 14px;
    justify-content: center;
    margin-bottom: 24px;
    padding-bottom: 22px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.auth-logo .logo-icon {
    font-size: 40px;
    line-height: 1;
    filter: drop-shadow(0 0 16px rgba(34, 197, 94, 0.6));
}
.auth-logo h1 {
    font-size: 24px;
    font-weight: 800;
    color: #10b981;
    margin: 0;
    letter-spacing: 1.5px;
    line-height: 1;
}
.auth-logo small {
    display: block;
    font-size: 10px;
    color: var(--text-sub);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-top: 4px;
}
.auth-card h2 {
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 8px;
    text-align: center;
    color: var(--text-main);
}
.auth-subtitle {
    text-align: center;
    color: var(--text-sub);
    font-size: 13.5px;
    margin: 0 0 26px;
}
.form-group { margin-bottom: 16px; }
.form-group label {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
    color: var(--text-sub);
    font-size: 13px;
    font-weight: 500;
}
.form-group input {
    width: 100%;
    padding: 13px 16px;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #f1f5f9;
    font-family: inherit;
    font-size: 14px;
    outline: none;
    transition: all 250ms ease;
    box-sizing: border-box;
}
.form-group input::placeholder { color: rgba(148, 163, 184, 0.5); }
.form-group input:focus {
    border-color: var(--accent);
    background: rgba(0, 0, 0, 0.4);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}
.password-input-wrap { position: relative; }
.password-input-wrap input { padding-right: 48px; }
.toggle-password {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    font-size: 16px;
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    opacity: 0.6;
    transition: 200ms;
    color: inherit;
}
.toggle-password:hover { opacity: 1; background: rgba(255, 255, 255, 0.05); }
.btn-primary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px 24px;
    width: 100%;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 12px !important;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all 250ms ease;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
    font-family: inherit;
    margin-top: 8px;
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.55);
}
.alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 13px 16px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-size: 13px;
    line-height: 1.5;
}
.alert span:first-child { font-size: 16px; flex-shrink: 0; }
.alert.error {
    background: rgba(239, 68, 68, 0.12);
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, 0.35);
}
.alert.success {
    background: rgba(34, 197, 94, 0.12);
    color: #86efac;
    border: 1px solid rgba(34, 197, 94, 0.35);
}
.auth-link {
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
    color: var(--text-sub);
}
.auth-link a {
    color: #10b981;
    text-decoration: none;
    font-weight: 600;
    margin-left: 4px;
    transition: 200ms;
}
.auth-link a:hover { color: #34d399; }
.link-subtle {
    color: #10b981;
    text-decoration: none;
    font-weight: 500;
}
.link-subtle:hover { color: #34d399; text-decoration: underline; }
[hidden] { display: none !important; }
</style>
</head>
<body>
<?php
    try { include $viewFile; }
    catch (\Throwable $e) {
        error_log('[SAIP] auth view: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        echo '<div style="padding:60px;color:#f87171;font-family:sans-serif;text-align:center">'
           . '<h1>Auth view error</h1><p>' . e($e->getMessage()) . '</p></div>';
    }
?>
</body>
</html><?php
    exit;
}

/* ═══ 13. Fallback router ═══ */
if (class_exists('App\\Core\\App')) {
    $_GET['url']             = ltrim($uri, '/');
    $_SERVER['QUERY_STRING'] = 'url=' . rawurlencode($_GET['url']);
    new \App\Core\App();
    exit;
}

http_response_code(404);
?><!DOCTYPE html><html><head><meta charset="UTF-8"><title>404</title>
<style>body{font-family:sans-serif;background:#0f1d2e;color:#e2e8f0;padding:60px;text-align:center}
a{color:#10b981}h1{font-size:52px;margin:0 0 12px;color:#34d399}</style>
</head><body>
<h1>404</h1>
<p>Route <code><?= e($uri) ?></code> not found.</p>
<p><a href="<?= e(BASE_URL) ?>/">← Home</a></p>
</body></html>

<?php
} catch (\Throwable $__fatal) {
    if (ob_get_level()) ob_end_clean();
    error_log('[SAIP FATAL] ' . $__fatal->getMessage()
            . ' @ ' . $__fatal->getFile() . ':' . $__fatal->getLine());
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    ?><!DOCTYPE html><html><head><meta charset="UTF-8"><title>SAIP — Error</title>
    <style>body{font-family:sans-serif;background:#0f1d2e;color:#e2e8f0;padding:60px;text-align:center}
    a{color:#10b981}h1{font-size:44px;color:#f87171;margin:0 0 16px}
    pre{background:#1e293b;padding:20px;border-radius:8px;text-align:left;color:#a7f3d0;overflow:auto;font-size:12px}</style>
    </head><body>
    <h1>SAIP Error</h1>
    <p>The page failed to load.</p>
    <pre><?= e($__fatal->getMessage()) ?>
File: <?= e($__fatal->getFile()) ?>:<?= (int)$__fatal->getLine() ?></pre>
    <p><a href="<?= e(BASE_URL) ?>/">← Home</a></p>
    </body></html><?php
}
exit;