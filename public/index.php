<?php
/**
 * htdocs/public/index.php — Forwarder to root index.php
 *
 * Purpose:
 *   Any URL under /public/* that isn't a real file gets handed to the
 *   root /index.php, which then dispatches to App\Core\App.
 *
 * Rewrites:
 *   /public/chat          → url=chat
 *   /public/chat?c=5      → url=chat  (keeps $_GET['c'] = 5)
 *   /public/auth/login    → url=auth/login
 *   /public/index.php?url=auth/register → url=auth/register (kept)
 */

/* ═════ 1. Compute the clean route ═════ */
if (empty($_GET['url'])) {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $uri = '/' . ltrim($uri, '/');

    // Strip "/public" prefix
    if ($uri === '/public') {
        $uri = '/';
    } elseif (strpos($uri, '/public/') === 0) {
        $uri = substr($uri, 7);
    }

    // Strip trailing /index.php
    if (substr($uri, -10) === '/index.php') {
        $uri = substr($uri, 0, -10) ?: '/';
    }

    $clean = trim($uri, '/');
    if ($clean !== '' && $clean !== 'index.php') {
        $_GET['url'] = $clean;
    }
}

/* ═════ 2. Fix $_SERVER so the router sees a clean path ═════ */
$rootIndex = dirname(__DIR__) . '/index.php';

$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $rootIndex;
$_SERVER['PHP_SELF']        = '/index.php';

// Rebuild REQUEST_URI without /public so any code reading it gets the clean path
if (!empty($_GET['url'])) {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    if ($qs !== '') {
        // Remove any existing "url=" from the query string before re-adding it
        parse_str($qs, $parts);
        unset($parts['url']);
        $qs = http_build_query($parts);
    }
    $_SERVER['REQUEST_URI'] = '/index.php' . ($qs !== '' ? '?' . $qs : '');
    $_SERVER['QUERY_STRING'] = $qs;
}

/* ═════ 3. Sanity check the target ═════ */
if (!is_file($rootIndex)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit("SAIP setup error: root index.php missing at {$rootIndex}");
}

/* ═════ 4. Hand off ═════ */
require $rootIndex;