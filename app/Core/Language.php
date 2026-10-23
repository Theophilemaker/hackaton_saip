<?php
namespace App\Core;

class Language {
    private static $strings = [];
    private static $currentLang = 'en';
    private static $supported = ['en', 'rw', 'fr', 'sw'];

    public static function init() {
        // 1. Check URL param
        if (isset($_GET['lang']) && in_array($_GET['lang'], self::$supported)) {
            self::$currentLang = $_GET['lang'];
            $_SESSION['lang'] = self::$currentLang;
        }
        // 2. Check session
        elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], self::$supported)) {
            self::$currentLang = $_SESSION['lang'];
        }
        // 3. Check user preference (if logged in)
        elseif (isset($_SESSION['preferred_language']) && in_array($_SESSION['preferred_language'], self::$supported)) {
            self::$currentLang = $_SESSION['preferred_language'];
        }

        // Load JSON file
        $file = __DIR__ . '/../Languages/' . self::$currentLang . '.json';
        if (file_exists($file)) {
            self::$strings = json_decode(file_get_contents($file), true) ?: [];
        } else {
            self::$strings = [];
        }
    }

    public static function get($key, $default = null) {
        return self::$strings[$key] ?? $default ?? $key;
    }

    public static function current() {
        return self::$currentLang;
    }

    public static function supported() {
        return [
            'en' => ['name' => 'English', 'flag' => '🇬🇧'],
            'rw' => ['name' => 'Kinyarwanda', 'flag' => '🇷🇼'],
            'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
            'sw' => ['name' => 'Kiswahili', 'flag' => '🇹🇿'],
        ];
    }
}
?>