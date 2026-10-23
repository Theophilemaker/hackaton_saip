<?php
/**
 * =====================================================================
 * SAIP — Smart Agriculture Intelligence Platform
 * Configuration Template (Safe to Commit)
 * =====================================================================
 *
 * Copy this file to `config.php` and fill in your own credentials.
 *
 *   cp config.example.php config.php
 *   # edit config.php
 *
 * @version  2.0 Enterprise Edition
 * @project  University of Rwanda BIT Capstone · NISR Big Data Hackathon 2026
 */

// =====================================================================
// PREVENT DIRECT ACCESS
// =====================================================================
if (basename($_SERVER['PHP_SELF']) === 'config.example.php') {
    http_response_code(403);
    die('Access denied.');
}

// =====================================================================
// ENVIRONMENT
// =====================================================================
// 'development' → shows errors, uses local URLs
// 'production'  → hides errors, uses HTTPS, secure cookies
define('APP_ENV', 'development');

// =====================================================================
// DATABASE
// =====================================================================
// XAMPP / WAMP defaults: host=localhost, user=root, password=''
// InfinityFree: host=sqlXXX.infinityfree.com, user=if0_xxxxx
// Railway:      host=containers-us-west-XX.railway.app, port=XXXX
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'saip_enterprise');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// =====================================================================
// APPLICATION URLS
// =====================================================================
// Local (XAMPP):       http://localhost/SAIP/public
// Local (WAMP vhost):  http://saip
// InfinityFree:        http://yoursite.infinityfreeapp.com
// Railway:             https://yourapp.up.railway.app
// NO TRAILING SLASH!
define('BASE_URL', 'http://localhost/SAIP/public');

// =====================================================================
// APP METADATA
// =====================================================================
define('APP_NAME',        'SAIP');
define('APP_FULL_NAME',   'Smart Agriculture Intelligence Platform');
define('APP_TAGLINE',     'Smart Insights for Healthy Crops & Sustainable Farms');
define('APP_VERSION',     '2.0.0');
define('APP_AUTHOR',      'University of Rwanda BIT');

// =====================================================================
// EXTERNAL APIS
// =====================================================================
// Weather: https://openweathermap.org/api (free tier)
define('OPENWEATHER_API_KEY', 'YOUR_OPENWEATHERMAP_API_KEY_HERE');
define('OPENWEATHER_BASE_URL', 'https://api.openweathermap.org/data/2.5/');

// Translation: https://mymemory.translated.net (free, no key needed)
define('TRANSLATION_API_URL', 'https://api.mymemory.translated.net/get');

// SMS (Africa's Talking — optional)
define('AT_API_KEY',   'YOUR_AFRICASTALKING_API_KEY');
define('AT_USERNAME',  'YOUR_AFRICASTALKING_USERNAME');
define('AT_SMS_FROM',  'SAIP');

// Email (SMTP — optional; falls back to PHP mail() if empty)
define('SMTP_HOST',     '');
define('SMTP_PORT',     '587');
define('SMTP_USER',     '');
define('SMTP_PASS',     '');
define('SMTP_FROM',     'noreply@saip.rw');
define('SMTP_FROM_NAME','SAIP Notifications');

// Payments (MTN MoMo / Airtel Money)
define('MOMO_API_KEY',    'YOUR_MOMO_API_KEY');
define('MOMO_SUBSCRIPTION_KEY', 'YOUR_MOMO_SUB_KEY');
define('AIRTEL_CLIENT_ID',      'YOUR_AIRTEL_CLIENT_ID');
define('AIRTEL_CLIENT_SECRET',  'YOUR_AIRTEL_CLIENT_SECRET');

// Google Maps (optional — we use Leaflet by default)
define('GOOGLE_MAPS_API_KEY', '');

// =====================================================================
// GEOGRAPHY DEFAULTS (Rwanda)
// =====================================================================
define('RWANDA_CENTER_LAT',  -1.9403);
define('RWANDA_CENTER_LNG',  29.8739);
define('RWANDA_DEFAULT_ZOOM', 8);
define('RWANDA_TIMEZONE',     'Africa/Kigali');

// =====================================================================
// LOCALIZATION
// =====================================================================
define('DEFAULT_LANGUAGE', 'en');
define('SUPPORTED_LANGUAGES', 'en,rw,fr,sw');
define('DEFAULT_THEME', 'green-harvest');

// =====================================================================
// SECURITY
// =====================================================================
// Generate with: php -r "echo bin2hex(random_bytes(32));"
define('APP_KEY', 'CHANGE_THIS_TO_A_RANDOM_64_CHAR_HEX_STRING');
define('SESSION_LIFETIME', 7200);         // 2 hours (seconds)
define('REMEMBER_ME_DAYS', 30);
define('PASSWORD_MIN_LENGTH', 8);
define('RATE_LIMIT_LOGIN', 10);           // attempts per minute per IP
define('RATE_LIMIT_REGISTER', 5);
define('LOCKOUT_THRESHOLD', 5);           // failed attempts before lockout
define('LOCKOUT_DURATION_MINUTES', 15);
define('CRON_SECRET', 'CHANGE_THIS_CRON_SECRET');

// =====================================================================
// FILE UPLOADS
// =====================================================================
define('UPLOAD_MAX_IMAGE_MB',  5);
define('UPLOAD_MAX_VIDEO_MB',  20);
define('UPLOAD_MAX_DOCUMENT_MB', 10);
define('UPLOAD_MAX_CSV_MB',    50);

// =====================================================================
// FEATURE FLAGS
// =====================================================================
define('FEATURE_AI_DISEASE_DETECTION', true);
define('FEATURE_AGRIBOT',              true);
define('FEATURE_PUBLIC_API',           true);
define('FEATURE_2FA',                  true);
define('FEATURE_PWA',                  true);
define('FEATURE_SMS',                  false);   // requires Africa's Talking
define('FEATURE_EMAIL',                false);   // uses PHP mail() fallback
define('FEATURE_MOMO_PAYMENTS',        false);   // mock payments when false

// =====================================================================
// APPLY ENVIRONMENT SETTINGS
// =====================================================================
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/php_error.log');
}

// =====================================================================
// TIMEZONE
// =====================================================================
date_default_timezone_set(RWANDA_TIMEZONE);

// =====================================================================
// ERROR LOG DIRECTORY
// =====================================================================
$logDir = __DIR__ . '/../storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

// =====================================================================
// PRODUCTION HTTPS ENFORCEMENT
// =====================================================================
if (APP_ENV === 'production') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        header("Location: https://$host$uri", true, 301);
        exit;
    }
}
?>