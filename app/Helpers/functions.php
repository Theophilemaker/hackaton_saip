<?php
use App\Core\Language;

/* ═══════════════════════════════════════════════════════════════════
   TRANSLATION
   ═══════════════════════════════════════════════════════════════════ */

if (!function_exists('__')) {
    function __($key, $default = null) {
        try {
            $value = Language::get($key, $default);
            if ($value === null || $value === '') {
                return $default ?? $key;
            }
            return $value;
        } catch (\Throwable $e) {
            return $default ?? $key;
        }
    }
}

/* ═══════════════════════════════════════════════════════════════════
   ESCAPING & OUTPUT SAFETY
   ═══════════════════════════════════════════════════════════════════ */

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)($string ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($string) {
        return htmlspecialchars((string)($string ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('esc_js')) {
    function esc_js($value) {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    }
}

/* ═══════════════════════════════════════════════════════════════════
   NUMBER & STRING FORMATTING
   ═══════════════════════════════════════════════════════════════════ */

if (!function_exists('safe_num')) {
    function safe_num($value, $decimals = 0, $decimalPoint = '.', $thousandsSep = ',') {
        return number_format((float)($value ?? 0), $decimals, $decimalPoint, $thousandsSep);
    }
}

if (!function_exists('format_money')) {
    function format_money($amount, $currency = 'RWF') {
        return number_format((float)($amount ?? 0), 0) . ' ' . $currency;
    }
}

if (!function_exists('format_bytes')) {
    function format_bytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max((float)$bytes, 0);
        $pow = $bytes > 0 ? floor(log($bytes) / log(1024)) : 0;
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('excerpt')) {
    function excerpt($text, $length = 150, $suffix = '…') {
        $text = trim((string)($text ?? ''));
        if (mb_strlen($text) <= $length) return $text;
        return mb_substr($text, 0, $length) . $suffix;
    }
}

/* ═══════════════════════════════════════════════════════════════════
   URL HELPERS
   ═══════════════════════════════════════════════════════════════════ */

if (!function_exists('lang_url')) {
    function lang_url($lang) {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        $parsed = parse_url($url);
        $query = [];
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
        }
        $query['lang'] = $lang;
        return ($parsed['path'] ?? '/') . '?' . http_build_query($query);
    }
}

if (!function_exists('base_url')) {
    function base_url($path = '') {
        $base = defined('BASE_URL') ? BASE_URL : '';
        return rtrim($base, '/') . '/' . ltrim((string)$path, '/');
    }
}

if (!function_exists('current_path')) {
    function current_path() {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return parse_url($uri, PHP_URL_PATH) ?: '/';
    }
}

if (!function_exists('is_active')) {
    function is_active($path) {
        $current = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($current, $path) !== false ? 'active' : '';
    }
}

/* ═══════════════════════════════════════════════════════════════════
   DATE & TIME
   ═══════════════════════════════════════════════════════════════════ */

if (!function_exists('time_ago')) {
    function time_ago($datetime) {
        if (empty($datetime)) return '—';
        $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
        if (!$timestamp) return '—';

        $diff = time() - $timestamp;
        if ($diff < 60) return 'just now';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        if ($diff < 2592000) return floor($diff / 604800) . 'w ago';
        if ($diff < 31536000) return floor($diff / 2592000) . 'mo ago';
        return floor($diff / 31536000) . 'y ago';
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'M d, Y') {
        if (empty($date)) return '—';
        $ts = is_numeric($date) ? $date : strtotime($date);
        return $ts ? date($format, $ts) : '—';
    }
}

/* ═══════════════════════════════════════════════════════════════════
   ARRAY HELPERS
   ═══════════════════════════════════════════════════════════════════ */

if (!function_exists('arr_get')) {
    function arr_get($array, $key, $default = null) {
        if (!is_array($array)) return $default;
        return $array[$key] ?? $default;
    }
}

if (!function_exists('arr_dot_get')) {
    function arr_dot_get($array, $path, $default = null) {
        if (!is_array($array)) return $default;
        $parts = explode('.', $path);
        $current = $array;
        foreach ($parts as $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return $default;
            }
            $current = $current[$part];
        }
        return $current;
    }
}

/* ═══════════════════════════════════════════════════════════════════
   FLASH MESSAGES
   ═══════════════════════════════════════════════════════════════════ */

if (!function_exists('flash')) {
    function flash($type, $message) {
        $_SESSION['flash_' . $type] = $message;
    }
}

if (!function_exists('has_flash')) {
    function has_flash($type) {
        return !empty($_SESSION['flash_' . $type]);
    }
}

if (!function_exists('get_flash')) {
    function get_flash($type) {
        if (empty($_SESSION['flash_' . $type])) return null;
        $msg = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $msg;
    }
}

/* ═══════════════════════════════════════════════════════════════════
   USER & AUTH
   ═══════════════════════════════════════════════════════════════════ */

if (!function_exists('current_user_id')) {
    function current_user_id() {
        return (int)($_SESSION['user_id'] ?? 0);
    }
}

if (!function_exists('current_user_name')) {
    function current_user_name() {
        return $_SESSION['name'] ?? 'Guest';
    }
}

if (!function_exists('current_role_id')) {
    function current_role_id() {
        return (int)($_SESSION['role_id'] ?? 0);
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return current_role_id() === 7;
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return !empty($_SESSION['user_id']);
    }
}

if (!function_exists('initials')) {
    function initials($name) {
        $name = trim((string)($name ?? ''));
        if ($name === '') return '?';
        $parts = preg_split('/\s+/', $name);
        if (count($parts) === 1) return strtoupper(substr($parts[0], 0, 2));
        return strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
    }
}

/* ═══════════════════════════════════════════════════════════════════
   ⭐ LOCATION HELPERS — one source of truth across the app
   ═══════════════════════════════════════════════════════════════════ */

if (!function_exists('current_district')) {
    /**
     * Get user's district — priority:
     *   1. Session (fastest, set when location is detected)
     *   2. users.current_district
     *   3. users.district_id → districts.name
     *   4. farmer_profiles.district
     *   5. Fallback: 'Kigali'
     */
    function current_district(): string {
        // 1. Session
        if (!empty($_SESSION['current_district'])) {
            return $_SESSION['current_district'];
        }

        if (empty($_SESSION['user_id'])) return 'Kigali';

        try {
            $db = new \App\Core\Database();

            // 2. users.current_district
            $db->query("SELECT current_district, district_id FROM users WHERE id = :id LIMIT 1");
            $db->bind(':id', (int)$_SESSION['user_id']);
            $row = $db->single();

            if (!empty($row['current_district'])) {
                $_SESSION['current_district'] = $row['current_district'];
                return $row['current_district'];
            }

            // 3. users.district_id → districts.name
            if (!empty($row['district_id'])) {
                $db->query("SELECT name FROM districts WHERE id = :id LIMIT 1");
                $db->bind(':id', (int)$row['district_id']);
                $d = $db->single();
                if (!empty($d['name'])) {
                    $_SESSION['current_district'] = $d['name'];
                    return $d['name'];
                }
            }

            // 4. farmer_profiles.district
            $db->query("SELECT district FROM farmer_profiles WHERE user_id = :u LIMIT 1");
            $db->bind(':u', (int)$_SESSION['user_id']);
            $p = $db->single();
            if (!empty($p['district'])) {
                $_SESSION['current_district'] = $p['district'];
                return $p['district'];
            }
        } catch (\Throwable $e) {
            // silent — fall through
        }

        return 'Kigali';
    }
}

if (!function_exists('current_coords')) {
    /**
     * Get current lat/lng if known.
     */
    function current_coords(): ?array {
        if (!empty($_SESSION['current_lat']) && !empty($_SESSION['current_lng'])) {
            return [
                'lat' => (float)$_SESSION['current_lat'],
                'lng' => (float)$_SESSION['current_lng'],
            ];
        }

        if (empty($_SESSION['user_id'])) return null;

        try {
            $db = new \App\Core\Database();
            $db->query("SELECT current_lat, current_lng FROM users WHERE id = :id LIMIT 1");
            $db->bind(':id', (int)$_SESSION['user_id']);
            $row = $db->single();

            if (!empty($row['current_lat']) && !empty($row['current_lng'])) {
                $_SESSION['current_lat'] = (float)$row['current_lat'];
                $_SESSION['current_lng'] = (float)$row['current_lng'];
                return [
                    'lat' => (float)$row['current_lat'],
                    'lng' => (float)$row['current_lng'],
                ];
            }
        } catch (\Throwable $e) {}

        return null;
    }
}

if (!function_exists('has_location')) {
    function has_location(): bool {
        return !empty($_SESSION['current_district'])
            || (!empty($_SESSION['current_lat']) && !empty($_SESSION['current_lng']));
    }
}

/* ═══════════════════════════════════════════════════════════════════
   STRING HELPERS
   ═══════════════════════════════════════════════════════════════════ */

if (!function_exists('str_contains_any')) {
    function str_contains_any($haystack, $needles) {
        $haystack = (string)$haystack;
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('slugify')) {
    function slugify($text) {
        $text = (string)$text;
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return $text ?: 'n-a';
    }
}

/* ═══════════════════════════════════════════════════════════════════
   RESPONSE HELPERS
   ═══════════════════════════════════════════════════════════════════ */

if (!function_exists('json_response')) {
    function json_response($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header("Location: " . $url);
        exit;
    }
}

/* ═══════════════════════════════════════════════════════════════════
   WEATHER ICON HELPER
   ═══════════════════════════════════════════════════════════════════ */

if (!function_exists('wx_icon')) {
    function wx_icon($code) {
        return match($code) {
            '01d' => '☀️',  '01n' => '🌙',
            '02d' => '⛅',  '02n' => '☁️',
            '03d', '03n', '04d', '04n' => '☁️',
            '09d', '09n' => '🌧️',
            '10d' => '🌦️', '10n' => '🌧️',
            '11d', '11n' => '⛈️',
            '13d', '13n' => '❄️',
            '50d', '50n' => '🌫️',
            default => '🌤️',
        };
    }
}