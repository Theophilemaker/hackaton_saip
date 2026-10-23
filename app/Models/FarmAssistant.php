<?php
namespace App\Models;

use App\Core\Database;

class FarmAssistant {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /* ═══════════ SESSION ═══════════ */
    public function getOrCreateSession(int $userId, string $lang = 'en'): int {
        try {
            $this->db->query("SELECT id FROM assistant_sessions
                              WHERE user_id = :u AND is_active = 1
                              ORDER BY updated_at DESC LIMIT 1");
            $this->db->bind(':u', $userId);
            $row = $this->db->single();
            if ($row) return (int)$row['id'];

            $this->db->query("INSERT INTO assistant_sessions (user_id, language) VALUES (:u, :l)");
            $this->db->bind(':u', $userId);
            $this->db->bind(':l', $lang);
            $this->db->execute();
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log("FarmAssistant::getOrCreateSession error: " . $e->getMessage());
            return 0;
        }
    }

    public function getHistory(int $sessionId, int $limit = 50): array {
        if (!$sessionId) return [];
        try {
            $this->db->query("SELECT * FROM assistant_messages
                              WHERE session_id = :s
                              ORDER BY id ASC LIMIT :l");
            $this->db->bind(':s', $sessionId);
            $this->db->bind(':l', $limit);
            return $this->db->resultSet();
        } catch (\Throwable $e) {
            error_log("FarmAssistant::getHistory error: " . $e->getMessage());
            return [];
        }
    }

    public function saveMessage(int $sessionId, string $role, string $content, ?string $intent = null): void {
        if (!$sessionId) return;
        try {
            $this->db->query("INSERT INTO assistant_messages (session_id, role, content, intent)
                              VALUES (:s, :r, :c, :i)");
            $this->db->bind(':s', $sessionId);
            $this->db->bind(':r', $role);
            $this->db->bind(':c', $content);
            $this->db->bind(':i', $intent);
            $this->db->execute();

            $this->db->query("UPDATE assistant_sessions SET updated_at = NOW() WHERE id = :s");
            $this->db->bind(':s', $sessionId);
            $this->db->execute();
        } catch (\Throwable $e) {
            error_log("FarmAssistant::saveMessage error: " . $e->getMessage());
        }
    }

    public function resetSession(int $sessionId): void {
        try {
            $this->db->query("UPDATE assistant_sessions SET is_active = 0 WHERE id = :s");
            $this->db->bind(':s', $sessionId);
            $this->db->execute();
        } catch (\Throwable $e) {}
    }

    /* ═══════════ MAIN RESPOND ═══════════ */
    public function respond(int $userId, string $message, array $profile): array {
        $text   = mb_strtolower(trim($message));
        $intent = $this->detectIntent($text);

        $reply = match($intent) {
            'greeting' => $this->replyGreeting($profile),
            'crops'    => $this->replyCrops($profile),
            'prices'   => $this->replyPrices($text),
            'yield'    => $this->replyYield($profile, $text),
            'sell'     => $this->replySell($text),
            'weather'  => $this->replyWeather($profile),
            'pests'    => $this->replyPests($text),
            'finance'  => $this->replyFinance(),
            'help'     => $this->replyHelp(),
            'thanks'   => ['text' => "🙏 Murakoze! Ask me anytime."],
            default    => $this->replyFallback(),
        };

        return [
            'intent'  => $intent,
            'reply'   => $reply['text'] ?? 'No response.',
            'actions' => $reply['actions'] ?? [],
        ];
    }

    private function detectIntent(string $text): string {
        if ($text === '') return 'fallback';

        $patterns = [
            'greeting' => ['hello','hi','hey','muraho','mwaramutse','mwiriwe','bite','amakuru','salamu'],
            'thanks'   => ['thank','murakoze','asante','urakoze'],
            'help'     => ['help','what can you do','ubufasha','nshobora','what can'],
            'crops'    => ['what should i plant','which crop','plant what','recommend crop',
                          'ndatera iki','igihingwa','crop advice','what to plant','plant this'],
            'prices'   => ['price','cost','how much','igiciro','market price','selling price','amafaranga'],
            'yield'    => ['yield','harvest','production','umusaruro','compare','benchmark','how am i'],
            'sell'     => ['where to sell','sell my','market for','buyer','nagurisha he','sell'],
            'weather'  => ['weather','rain','forecast','ikirere','imvura'],
            'pests'    => ['pest','disease','blight','bug','insect','indwara','uduhumbi','dying','problem'],
            'finance'  => ['loan','subsidy','credit','finance','inguzanyo','inkunga','funding','apply'],
        ];

        foreach ($patterns as $intent => $keywords) {
            foreach ($keywords as $kw) {
                if (strpos($text, $kw) !== false) return $intent;
            }
        }
        return 'fallback';
    }

    private function detectCrop(string $text): ?string {
        $crops = ['maize','potatoes','beans','rice','tomatoes','coffee','tea','bananas','cassava','sorghum','wheat'];
        foreach ($crops as $c) {
            if (strpos($text, $c) !== false) return $c;
        }
        return null;
    }

    /* ═══════════ REPLY HANDLERS ═══════════ */

    private function replyGreeting(array $profile): array {
        $name = $profile['first_name'] ?? 'farmer';
        $district = $profile['district'] ?? null;

        $text = "👋 **Muraho, {$name}!**\n\n";
        if ($district) $text .= "I see you farm in **{$district}**. ";
        $text .= "I'm your farm assistant. Ask me:\n\n"
               . "• What should I plant?\n"
               . "• Market prices\n"
               . "• Yield comparison\n"
               . "• Pest & disease help\n\n"
               . "**English** or **Kinyarwanda** — your choice.";

        return [
            'text' => $text,
            'actions' => [
                ['label' => '🌾 What to plant?', 'message' => 'What should I plant?'],
                ['label' => '💰 Prices', 'message' => 'Current market prices'],
                ['label' => '📊 My yield', 'message' => 'How does my yield compare?'],
            ],
        ];
    }

    private function replyCrops(array $profile): array {
        if (empty($profile['district'])) {
            return [
                'text' => "I need to know your district first. Please complete your profile.",
                'actions' => [['label' => '⚙️ Complete Profile', 'url' => BASE_URL . '/advice/profile']],
            ];
        }

        try {
            require_once __DIR__ . '/Recommendation.php';
            $rec = new Recommendation();
            $recs = $rec->recommendCrops($profile, 3);
        } catch (\Throwable $e) {
            $recs = [];
            error_log("replyCrops error: " . $e->getMessage());
        }

        if (empty($recs)) {
            return ['text' => "No crop data available yet. Please ask your admin to upload an agricultural survey dataset."];
        }

        $text = "🌾 **Top crops for {$profile['district']}:**\n\n";
        foreach ($recs as $i => $r) {
            $medal = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : '🥉');
            $text .= "{$medal} **{$r['crop']}**\n"
                   . "   • Yield: " . number_format($r['yield_per_ha']) . " kg/ha\n"
                   . "   • Market: " . number_format($r['price_per_kg']) . " RWF/kg\n"
                   . "   • **Projected: " . number_format($r['projected_revenue']) . " RWF**\n\n";
        }

        return [
            'text' => $text,
            'actions' => [['label' => '📖 Full Crop Advisor', 'url' => BASE_URL . '/advice/crops']],
        ];
    }

    private function replyPrices(string $text): array {
        try {
            $crop = $this->detectCrop($text);
            $this->db->query("SELECT crop, market, district, price_per_kg
                              FROM market_prices
                              WHERE recorded_at >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)"
                              . ($crop ? " AND LOWER(crop) = :c" : "")
                              . " ORDER BY price_per_kg DESC LIMIT 12");
            if ($crop) $this->db->bind(':c', $crop);
            $rows = $this->db->resultSet();
        } catch (\Throwable $e) {
            $rows = [];
        }

        if (empty($rows)) {
            return ['text' => "Market prices are being updated. Please check again later."];
        }

        $text = "💰 **" . ($crop ? ucfirst($crop) . " prices:" : "Latest market prices:") . "**\n\n";
        foreach ($rows as $r) {
            $text .= "• **{$r['crop']}** — {$r['market']} ({$r['district']}): **"
                   . number_format($r['price_per_kg']) . " RWF/kg**\n";
        }

        return [
            'text' => $text,
            'actions' => [['label' => '🏪 Where to sell?', 'message' => 'Where can I sell?']],
        ];
    }

    private function replyYield(array $profile, string $text): array {
        if (empty($profile['district'])) {
            return ['text' => "Please set your district in your profile first."];
        }

        try {
            require_once __DIR__ . '/Recommendation.php';
            $rec = new Recommendation();
            $crop = $this->detectCrop($text) ?? 'maize';
            $bench = $rec->benchmark($profile, ucfirst($crop));
        } catch (\Throwable $e) {
            $bench = null;
        }

        if (!$bench || empty($bench['district_avg'])) {
            return ['text' => "No yield data for this crop in your district yet."];
        }

        $text = "📊 **Yield Analysis**\n\n"
              . "📍 District: **{$profile['district']}**\n"
              . "📈 District avg: **" . number_format($bench['district_avg'], 1) . " kg/ha**\n"
              . "🌍 National avg: **" . number_format($bench['national_avg'], 1) . " kg/ha**\n";

        return [
            'text' => $text,
            'actions' => [
                ['label' => '📖 Full Benchmark', 'url' => BASE_URL . '/advice/benchmark?crop=' . ucfirst($crop)],
            ],
        ];
    }

    private function replySell(string $text): array {
        try {
            $crop = $this->detectCrop($text);
            $this->db->query("SELECT crop, market, district, price_per_kg
                              FROM market_prices
                              WHERE recorded_at >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)"
                              . ($crop ? " AND LOWER(crop) = :c" : "")
                              . " ORDER BY price_per_kg DESC LIMIT 5");
            if ($crop) $this->db->bind(':c', $crop);
            $rows = $this->db->resultSet();
        } catch (\Throwable $e) {
            $rows = [];
        }

        if (empty($rows)) return ['text' => "No selling options available right now."];

        $text = "🏪 **Best markets**" . ($crop ? " for " . ucfirst($crop) : "") . ":\n\n";
        foreach ($rows as $i => $r) {
            $medal = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : '🥉');
            $text .= "{$medal} **{$r['market']}** ({$r['district']})\n"
                   . "   💰 " . number_format($r['price_per_kg']) . " RWF/kg\n\n";
        }

        return ['text' => $text];
    }

    private function replyWeather(array $profile): array {
        $d = $profile['district'] ?? 'your area';
        return [
            'text' => "🌤️ **Weather for {$d}**\n\nLive weather integration coming soon. Check the Weather page for current forecasts.",
            'actions' => [['label' => '🌤️ Weather page', 'url' => BASE_URL . '/weather']],
        ];
    }

    private function replyPests(string $text): array {
        $map = [
            'blight'   => "🍅 **Late Blight**\n• Apply Mancozeb or Ridomil\n• Remove infected plants\n• Avoid overhead watering",
            'armyworm' => "🐛 **Fall Armyworm**\n• Scout morning/evening\n• Handpick larvae\n• Apply Emamectin benzoate",
            'aphid'    => "🐜 **Aphids**\n• Spray soapy water\n• Introduce ladybugs\n• Apply neem oil",
        ];
        foreach ($map as $k => $r) {
            if (strpos($text, $k) !== false) return ['text' => $r];
        }
        return [
            'text' => "🐛 **Pest & Disease Help**\n\nTell me:\n• Which crop?\n• What symptoms?\n\n"
                    . "Common issues I help with:\n• Late blight (potatoes/tomatoes)\n• Fall armyworm (maize)\n• Aphids (many crops)",
        ];
    }

    private function replyFinance(): array {
        return [
            'text' => "💳 **Loans & Subsidies**\n\n"
                    . "**Programs:**\n"
                    . "• 🌾 Fertilizer Subsidy — via your cooperative\n"
                    . "• 🏦 SACCO Loans — 8-12% interest\n"
                    . "• 🌱 Seed Support — free for registered farmers\n\n"
                    . "**Where to apply:**\n"
                    . "• RAB district office\n• Local SACCO branch\n• MINAGRI online portal",
            'actions' => [['label' => '📄 RAB Website', 'url' => 'https://www.rab.gov.rw']],
        ];
    }

    private function replyHelp(): array {
        return [
            'text' => "🤖 **I can help with:**\n\n"
                    . "🌾 Crop advice — what to plant\n"
                    . "💰 Market prices\n"
                    . "📊 Yield analysis\n"
                    . "🏪 Where to sell\n"
                    . "🌤️ Weather\n"
                    . "🐛 Pest & disease\n"
                    . "💳 Finance & loans\n\n"
                    . "Ask in **English** or **Kinyarwanda**.",
            'actions' => [
                ['label' => '🌾 What to plant?', 'message' => 'What should I plant?'],
                ['label' => '💰 Prices', 'message' => 'Market prices'],
            ],
        ];
    }

    private function replyFallback(): array {
        return [
            'text' => "I'm not sure I understood. 🤔\n\nTry:\n"
                    . "• \"What should I plant?\"\n"
                    . "• \"Market prices for maize\"\n"
                    . "• \"How does my yield compare?\"\n"
                    . "• \"I have pests\"\n\n"
                    . "Or type **help**.",
            'actions' => [['label' => '🤖 What can you do?', 'message' => 'help']],
        ];
    }
}