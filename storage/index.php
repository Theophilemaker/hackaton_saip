<?php
/**
 * htdocs/storage/index.php — Forwarder
 *
 * Why this exists:
 *   The physical folder /htdocs/storage/ holds logs and cache.
 *   When a visitor hits /storage/, Apache finds the real folder
 *   instead of routing through the app.
 *
 *   This file catches that request and forwards it to the root
 *   index.php with url=storage — which then runs StorageController.
 */

/* Tell the router which route we want */
$_GET['url'] = 'storage';

/* Fix SCRIPT_* so the router and BASE_URL detection work correctly */
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__) . '/index.php';
$_SERVER['PHP_SELF']        = '/index.php';

/* Include the real app */
require dirname(__DIR__) . '/index.php';