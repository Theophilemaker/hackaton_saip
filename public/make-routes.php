<?php
/**
 * Route generator — htdocs/public/make-routes.php
 * Visit: http://smartap.totalh.net/public/make-routes.php
 * Then DELETE this file immediately.
 */

header('Content-Type: text/plain; charset=utf-8');

$routes = [
    'chat'          => 'chat',
    'auth/login'    => 'auth/login',
    'auth/register' => 'auth/register',
    'auth/forgot'   => 'auth/forgot',
    'auth/reset'    => 'auth/reset',
    'auth/logout'   => 'auth/logout',
    'auth/2fa'      => 'auth/2fa',
    'dashboard'     => 'dashboard',
    'crops'         => 'crops',
    'crops/plant'   => 'crops/plant',
    'crops/farms'   => 'crops/farms',
    'crops/detect'  => 'crops/detect',
    'crops/agribot' => 'crops/agribot',
    'marketplace'   => 'marketplace',
    'weather'       => 'weather',
    'forum'         => 'forum',
    'nisr'          => 'nisr',
    'logistics'     => 'logistics',
    'cooperative'   => 'cooperative',
    'storage'       => 'storage',
    'notifications' => 'notifications',
    'government'    => 'government',
    'profile'       => 'profile',
    'feed'          => 'feed',
    'agribot'       => 'agribot',
    'admin'         => 'admin',
    'gis'           => 'gis',
    'settings'      => 'settings',
    'search'        => 'search',
    'language'      => 'language',
];

$tpl = <<<'PHP'
<?php
/**
 * Auto-generated route forwarder.
 * Route: /public/__ROUTE__
 */
$_GET['url'] = __URL__;

$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__, 2) . '/index.php';
$_SERVER['PHP_SELF']        = '/index.php';
$_SERVER['REQUEST_URI']     = '/index.php';

require $_SERVER['SCRIPT_FILENAME'];
PHP;

$ok = 0; $skipped = 0; $failed = 0;

foreach ($routes as $route => $url) {
    $dir  = __DIR__ . '/' . $route;
    $file = $dir . '/index.php';

    // Don't clobber a real file
    if (is_file(__DIR__ . '/' . $route)) {
        echo "SKIP  /public/{$route}   (real file, untouched)\n";
        $skipped++;
        continue;
    }

    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) {
            echo "FAIL  /public/{$route}/  (mkdir)\n";
            $failed++;
            continue;
        }
    }

    $content = str_replace(
        ['__ROUTE__', '__URL__'],
        [$route, var_export($url, true)],
        $tpl
    );

    if (@file_put_contents($file, $content) !== false) {
        echo "OK    /public/{$route}/index.php\n";
        $ok++;
    } else {
        echo "FAIL  /public/{$route}/index.php\n";
        $failed++;
    }
}

echo "\n──────────────────────────────\n";
echo "Created : {$ok}\n";
echo "Skipped : {$skipped}\n";
echo "Failed  : {$failed}\n";
echo "\n⚠️  DELETE this file now: /public/make-routes.php\n";