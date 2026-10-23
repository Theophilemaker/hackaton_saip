<?php
header('Content-Type: text/plain; charset=utf-8');
echo "=== ROUTE DEBUG ===\n\n";
echo "REQUEST_URI:   " . ($_SERVER['REQUEST_URI'] ?? '—') . "\n";
echo "SCRIPT_NAME:   " . ($_SERVER['SCRIPT_NAME'] ?? '—') . "\n";
echo "PHP_SELF:      " . ($_SERVER['PHP_SELF'] ?? '—') . "\n";
echo "QUERY_STRING:  " . ($_SERVER['QUERY_STRING'] ?? '—') . "\n";
echo "\$_GET['url']:  " . ($_GET['url'] ?? '(not set)') . "\n";
echo "\n=== FILE CHECKS ===\n";
$root = dirname(__DIR__);
foreach ([
    'index.php'                              => $root . '/index.php',
    'app/Core/App.php'                       => $root . '/app/Core/App.php',
    'app/Controllers/AdminController.php'    => $root . '/app/Controllers/AdminController.php',
    'app/Controllers/AuthController.php'     => $root . '/app/Controllers/AuthController.php',
] as $label => $path) {
    echo str_pad($label, 42) . (is_file($path) ? '✅ FOUND' : '❌ MISSING') . "\n";
}