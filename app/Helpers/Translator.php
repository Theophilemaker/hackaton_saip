<?php
namespace App\Helpers;

class Translator {
    /**
     * Translate text between any two languages.
     * Uses MyMemory free API (5000 chars/day free).
     */
    public static function translate($text, $from = 'en', $to = 'rw') {
        if ($from === $to || empty(trim($text))) return $text;

        $url = 'https://api.mymemory.translated.net/get?q=' 
             . urlencode($text) . '&langpair=' . $from . '|' . $to;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($res, true);
        return $json['responseData']['translatedText'] ?? $text;
    }
}
?>