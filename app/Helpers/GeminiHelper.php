<?php
namespace App\Helpers;

/**
 * Google Gemini AI helper.
 *
 * Reads GEMINI_API_KEY and GEMINI_MODEL from config.php.
 * Returns null on ANY failure so the caller can fall back to local rules.
 *
 * Model: gemini-3.6-flash
 * Get key: https://aistudio.google.com/app/apikey
 */
class GeminiHelper {

    private const API_BASE = 'https://generativelanguage.googleapis.com/v1beta/models/';

    private $apiKey;
    private $model;
    private $systemPrompt;
    private $lastError = '';

    public function __construct(?string $apiKey = null, ?string $systemPrompt = null) {
        $this->apiKey = $apiKey !== null
            ? $apiKey
            : (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '');

        $this->model = defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-3.6-flash';

        $this->systemPrompt = $systemPrompt ?: $this->defaultSystemPrompt();
    }

    /* ═══════════════ CONFIG CHECK ═══════════════ */
    public function isConfigured(): bool {
        if (empty($this->apiKey)) return false;
        if (strpos($this->apiKey, 'YOUR_') === 0) return false;
        if (strlen($this->apiKey) < 20) return false;
        return true;
    }

    public function getLastError(): string {
        return $this->lastError;
    }

    /* ═══════════════ MAIN CHAT ═══════════════
       Returns reply string, or null if AI unavailable.
       Never throws — caller always falls back safely. */
    public function chat(string $userMessage, array $history = [], string $lang = 'en'): ?string {
        if (!$this->isConfigured()) {
            $this->lastError = 'API key not configured';
            return null;
        }

        $userMessage = trim($userMessage);
        if ($userMessage === '') {
            $this->lastError = 'Empty message';
            return null;
        }

        /* ── Build request ── */
        $url = self::API_BASE . urlencode($this->model) . ':generateContent?key=' . urlencode($this->apiKey);

        $contents = [];

        /* System instructions as first exchange */
        $contents[] = [
            'role'  => 'user',
            'parts' => [[ 'text' => $this->systemPrompt . "\n\nPreferred language: " . $this->langName($lang) ]],
        ];
        $contents[] = [
            'role'  => 'model',
            'parts' => [[ 'text' => 'Understood. I am AgriBot, ready to help Rwandan farmers.' ]],
        ];

        /* Prior conversation — last 10 turns only (token budget) */
        $recent = array_slice($history, -10);
        foreach ($recent as $msg) {
            if (!is_array($msg)) continue;
            $role = (($msg['role'] ?? '') === 'user') ? 'user' : 'model';
            $text = trim((string)($msg['content'] ?? ''));
            if ($text === '') continue;
            $contents[] = [
                'role'  => $role,
                'parts' => [[ 'text' => $text ]],
            ];
        }

        /* Current user message */
        $contents[] = [
            'role'  => 'user',
            'parts' => [[ 'text' => $userMessage ]],
        ];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature'     => 0.7,
                'topK'            => 40,
                'topP'            => 0.95,
                'maxOutputTokens' => 500,
            ],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH',       'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ],
        ];

        $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($jsonBody === false) {
            $this->lastError = 'JSON encode failed';
            return null;
        }

        /* ── Send ── */
        $response = $this->httpPost($url, $jsonBody);
        if ($response === false) {
            /* lastError already set by httpPost */
            return null;
        }

        /* ── Parse ── */
        $data = json_decode($response, true);
        if (!is_array($data)) {
            $this->lastError = 'Non-JSON response from Gemini';
            $this->log('Non-JSON: ' . substr($response, 0, 300));
            return null;
        }

        if (!empty($data['error'])) {
            $this->lastError = $data['error']['message'] ?? 'API error';
            $this->log('API error: ' . $this->lastError);
            return null;
        }

        /* Extract text */
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($text === null) {
            /* Maybe blocked by safety filter */
            $reason = $data['candidates'][0]['finishReason'] ?? 'unknown';
            $this->lastError = 'No text returned (reason: ' . $reason . ')';
            $this->log('Empty response: ' . json_encode($data));
            return null;
        }

        return trim((string)$text);
    }

    /* ═══════════════ HTTP POST ═══════════════
       Tries cURL first, falls back to file_get_contents. */
    private function httpPost(string $url, string $jsonBody) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $jsonBody,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_TIMEOUT        => 25,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT      => 'SAIP/2.0 (AgriBot)',
            ]);
            $out  = curl_exec($ch);
            $err  = curl_error($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($out === false) {
                $this->lastError = 'cURL: ' . $err;
                $this->log($this->lastError);
                return false;
            }

            if ($code >= 500) {
                $this->lastError = 'HTTP ' . $code;
                $this->log('HTTP ' . $code . ': ' . substr($out, 0, 200));
            }

            return $out;
        }

        /* Fallback for hosts where cURL is disabled */
        $ctx = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content'       => $jsonBody,
                'timeout'       => 25,
                'ignore_errors' => true,   /* keep body on 4xx */
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        $out = @file_get_contents($url, false, $ctx);
        if ($out === false) {
            $this->lastError = 'file_get_contents failed (network or allow_url_fopen disabled)';
            $this->log($this->lastError);
            return false;
        }

        return $out;
    }

    /* ═══════════════ SYSTEM PROMPT ═══════════════ */
    private function defaultSystemPrompt(): string {
        return <<<PROMPT
You are AgriBot, an expert agricultural AI assistant for Rwandan farmers.

Your expertise covers:
- Crop planning and rotation for Rwanda's districts and seasons
- Fertilizer recommendations (NPK, urea, DAP, compost, organic)
- Pest and disease identification and treatment
- Weather-based decisions (planting windows, irrigation timing)
- Post-harvest storage and handling
- Market prices and selling strategies
- Cooperative and government support programs (MINAGRI, RAB, NAEB)
- Coffee, tea, maize, beans, potatoes, bananas, rice, cassava, wheat farming

Rwandan seasons:
- Season A: September – January
- Season B: February – June
- Season C: June – August (marshlands)

Rules:
1. Be concise — 2 to 5 short paragraphs maximum
2. Give concrete, actionable advice with specific numbers when possible
3. If the user writes in Kinyarwanda, respond in Kinyarwanda
4. If in French, respond in French. If in Kiswahili, respond in Kiswahili.
5. Redirect non-agriculture questions back to farming topics politely
6. Never invent market prices — say "check the Marketplace page" if unsure
7. Use 1–2 emojis per reply, no more
8. End with a short helpful follow-up question when useful
9. Do not use markdown code fences in replies
10. Keep answers under 250 words unless the user asks for detail
PROMPT;
    }

    private function langName(string $lang): string {
        $map = [
            'rw' => 'Kinyarwanda',
            'en' => 'English',
            'fr' => 'French',
            'sw' => 'Kiswahili',
        ];
        return $map[$lang] ?? 'English';
    }

    /* ═══════════════ LOGGING ═══════════════ */
    private function log(string $message): void {
        $logDir = defined('STORAGE_PATH')
            ? STORAGE_PATH . '/logs'
            : dirname(__DIR__, 2) . '/storage/logs';

        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);

        @error_log('[GeminiHelper] ' . $message . PHP_EOL, 3, $logDir . '/gemini.log');
    }
}