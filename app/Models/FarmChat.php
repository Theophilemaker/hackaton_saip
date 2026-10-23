<?php
namespace App\Models;

use App\Core\Database;
use App\Helpers\NisrEngine;

/**
 * FarmChat — conversational assistant for farmers.
 * Rule-based intent detection + data-driven answers.
 */
class FarmChat {

    private $db;
    private $engine;
    private $recommender;

    public function __construct() {
        $this->db          = new Database();
        $this->engine      = new NisrEngine();
        $this->recommender = new Recommendation();
    }

    /* ═══════════════════════════════════════════════════════════════
       SESSION MANAGEMENT
       ═══════════════════════════════════════════════════════════════ */
    public function getOrCreateSession(int $userId, string $lang = 'en'): int {
        $this->db->query("SELECT id FROM chat_sessions
                          WHERE user_id = :u AND is_active = 1
                          ORDER BY updated_at DESC LIMIT 1");
        $this->db->bind(':u', $userId);
        $row = $this->db->single();

        if ($row) return (int)$row['id'];

        $this->db->query("INSERT INTO chat_sessions (user_id, language) VALUES (:u, :l)");
        $this->db->bind(':u', $userId);
        $this->db->bind(':l', $lang);
        $this->db->execute();
        return (int)$this->db->lastInsertId();
    }

    public function getHistory(int $sessionId, int $limit = 50): array {
        $this->db->query("SELECT * FROM chat_messages
                          WHERE session_id = :s
                          ORDER BY created_at ASC LIMIT :l");
        $this->db->bind(':s', $sessionId);
        $this->db->bind(':l', $limit);
        return $this->db->resultSet();
    }

    public function saveMessage(int $sessionId, string $role, string $content, ?string $intent = null, ?array $meta = null): void {
        $this->db->query("INSERT INTO chat_messages (session_id, role, content, intent, metadata)
                          VALUES (:s, :r, :c, :i, :m)");
        $this->db->bind(':s', $sessionId);
        $this->db->bind(':r', $role);
        $this->db->bind(':c', $content);
        $this->db->bind(':i', $intent);
        $this->db->bind(':m', $meta ? json_encode($meta) : null);
        $this->db->execute();

        // Bump session
        $this->db->query("UPDATE chat_sessions SET updated_at = NOW() WHERE id = :s");
        $this->db->bind(':s', $sessionId);
        $this->db->execute();
    }

    public function resetSession(int $sessionId): void {
        $this->db->query("UPDATE chat_sessions SET is_active = 0 WHERE id = :s");
        $this->db->bind(':s', $sessionId);
        $this->db->execute();
    }

    /* ═══════════════════════════════════════════════════════════════
       MAIN ENTRY: process a user message → return assistant reply
       ═══════════════════════════════════════════════════════════════ */
    public function respond(int $userId, string $message, array $profile): array {
        $text = mb_strtolower(trim($message));

        // Detect intent
        $intent = $this->detectIntent($text);

        // Route to handler
        $reply = match($intent) {
            'greeting' => $this->handleGreeting($profile),
            'crops'    => $this->handleCrops($profile),
            'prices'   => $this->handlePrices($profile, $text),
            'yield'    => $this->handleYield($profile, $text),
            'sell'     => $this->handleSell($profile, $text),
            'weather'  => $this->handleWeather($profile),
            'pests'    => $this->handlePests($text),
            'finance'  => $this->handleFinance(),
            'help'     => $this->handleHelp(),
            'thanks'   => $this->handleThanks(),
            default    => $this->handleFallback($profile),
        };

        return [
            'intent'  => $intent,
            'reply'   => $reply['text'],
            'actions' => $reply['actions'] ?? [],
            'data'    => $reply['data'] ?? null,
        ];
    }

    /* ═══════════════════════════════════════════════════════════════
       INTENT DETECTION
       ═══════════════════════════════════════════════════════════════ */
    private function detectIntent(string $text): string {
        $patterns = [
            'greeting' => ['hello', 'hi ', 'hey', 'muraho', 'mwaramutse', 'mwiriwe', 'bite', 'amakuru'],
            'thanks'   => ['thank', 'thanks', 'murakoze', 'asante', 'urakoze'],
            'help'     => ['help', 'what can you do', 'how do you work', 'ubufasha', 'nshobora'],
            'crops'    => ['what should i plant', 'which crop', 'plant what', 'recommend crop',
                          'ndatera iki', 'umusaruro', 'igihingwa', 'crop advice'],
            'prices'   => ['price', 'cost', 'how much', 'igiciro', 'market price', 'selling price',
                          'amafaranga', 'biza'],
            'yield'    => ['yield', 'harvest', 'production', 'umusaruro wanjye', 'compare',
                          'how am i doing', 'benchmark'],
            'sell'     => ['where to sell', 'sell my', 'market for', 'buyer', 'kugurisha',
                          'nagurisha he', 'market opportunities'],
            'weather'  => ['weather', 'rain', 'forecast', 'ikirere', 'imvura', 'climate'],
            'pests'    => ['pest', 'disease', 'bug', 'insect', 'blight', 'uduhumbi', 'indwara',
                          'my plants are', 'dying'],
            'finance'  => ['loan', 'subsidy', 'credit', 'finance', 'inguzanyo', 'inkunga',
                          'funding', 'apply for'],
        ];

        foreach ($patterns as $intent => $keywords) {
            foreach ($keywords as $kw) {
                if (strpos($text, $kw) !== false) return $intent;
            }
        }
        return 'fallback';
    }

    /* ═══════════════════════════════════════════════════════════════
       HANDLERS
       ═══════════════════════════════════════════════════════════════ */

    private function handleGreeting(array $profile): array {
        $name = $profile['first_name'] ?? ($_SESSION['name'] ?? 'farmer');
        $district = $profile['district'] ?? null;

        $text = "👋 **Muraho, {$name}!**\n\n";
        if ($district) {
            $text .= "I can see you farm in **{$district}**. ";
        }
        $text .= "I'm your farm assistant. Ask me anything:\n\n"
               . "• What should I plant?\n"
               . "• Market prices for my crops\n"
               . "• How does my yield compare?\n"
               . "• Pest & disease help\n"
               . "• Weather forecast\n\n"
               . "You can write in **English** or **Kinyarwanda**.";

        return [
            'text' => $text,
            'actions' => [
                ['label' => '🌾 What to plant?', 'message' => 'What should I plant?'],
                ['label' => '💰 Prices', 'message' => 'Current market prices'],
                ['label' => '📊 My yield', 'message' => 'How does my yield compare?'],
            ],
        ];
    }

    private function handleCrops(array $profile): array {
        if (empty($profile['district']) || empty($profile['land_size_ha'])) {
            return [
                'text' => "I need to know a bit about your farm first. Please complete your profile:\n\n"
                        . "📍 District\n📐 Land size\n🌾 Current crops",
                'actions' => [
                    ['label' => '⚙️ Complete Profile', 'url' => BASE_URL . '/advice/profile'],
                ],
            ];
        }

        $recs = $this->recommender->recommendCrops($profile, 3);

        if (empty($recs)) {
            return [
                'text' => "I couldn't generate crop recommendations — the NISR dataset might not be loaded yet.\n\n"
                        . "Ask your admin to upload an agricultural survey dataset.",
            ];
        }

        $text = "🌾 **Top crops for you in {$profile['district']}:**\n\n";
        foreach ($recs as $i => $r) {
            $medal = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : '🥉');
            $text .= "{$medal} **{$r['crop']}**\n";
            $text .= "   • Yield: " . number_format($r['yield_per_ha']) . " kg/ha\n";
            $text .= "   • Market: " . number_format($r['price_per_kg']) . " RWF/kg\n";
            $text .= "   • **Projected: " . number_format($r['projected_revenue']) . " RWF**\n";
            $text .= "   • Difficulty: {$r['difficulty']} · Risk: {$r['risk']}\n\n";
        }

        $text .= "Want more detail? Open the full Crop Advisor.";

        return [
            'text' => $text,
            'actions' => [
                ['label' => '📖 Full Crop Advisor', 'url' => BASE_URL . '/advice/crops'],
                ['label' => '💰 Check prices', 'message' => 'Current market prices'],
            ],
            'data' => $recs,
        ];
    }

    private function handlePrices(array $profile, string $text): array {
        // Detect which crop
        $crop = $this->detectCrop($text);

        $this->db->query("SELECT crop, market, district, price_per_kg, recorded_at
                          FROM market_prices
                          WHERE recorded_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
                          . ($crop ? " AND LOWER(crop) = :c" : "")
                          . " ORDER BY price_per_kg DESC LIMIT 20");
        if ($crop) $this->db->bind(':c', $crop);
        $rows = $this->db->resultSet();

        if (empty($rows)) {
            return [
                'text' => "I don't have market prices right now. Try again later or contact your cooperative.",
            ];
        }

        $text = $crop
            ? "💰 **Current prices for " . ucfirst($crop) . ":**\n\n"
            : "💰 **Latest market prices (last 30 days):**\n\n";

        // Group by crop
        $byCrop = [];
        foreach ($rows as $r) {
            $byCrop[$r['crop']][] = $r;
        }

        foreach ($byCrop as $c => $prices) {
            $text .= "**{$c}**\n";
            $top = array_slice($prices, 0, 3);
            foreach ($top as $p) {
                $text .= "   • {$p['market']} ({$p['district']}): " . number_format($p['price_per_kg']) . " RWF/kg\n";
            }
            $text .= "\n";
        }

        return [
            'text' => trim($text),
            'actions' => [
                ['label' => '🏪 Where to sell?', 'message' => 'Where can I sell my produce?'],
                ['label' => '📊 Compare yield', 'message' => 'How does my yield compare?'],
            ],
        ];
    }

    private function handleYield(array $profile, string $text): array {
        if (empty($profile['district'])) {
            return [
                'text' => "Please set your district in your profile so I can compare your yield with the local average.",
                'actions' => [['label' => '⚙️ Complete Profile', 'url' => BASE_URL . '/advice/profile']],
            ];
        }

        $crop = $this->detectCrop($text) ?? 'Maize';
        $bench = $this->recommender->benchmark($profile, ucfirst($crop));

        if (!$bench || !$bench['district_avg']) {
            return [
                'text' => "I don't have yield data for **{$crop}** in {$profile['district']} yet.",
                'actions' => [
                    ['label' => '🌾 See other crops', 'url' => BASE_URL . '/advice/crops'],
                ],
            ];
        }

        $text = "📊 **Yield Analysis — " . ucfirst($crop) . "**\n\n"
              . "📍 Your district: **{$profile['district']}**\n"
              . "📈 District avg: **" . number_format($bench['district_avg'], 1) . " kg/ha**\n"
              . "🌍 National avg: **" . number_format($bench['national_avg'], 1) . " kg/ha**\n\n";

        if ($bench['farmer_yield'] > 0) {
            $diff = $bench['vs_district'];
            $sign = $diff >= 0 ? '+' : '';
            $text .= "Your yield: **" . number_format($bench['farmer_yield'], 1) . " kg/ha** ($sign{$diff}% vs district)";
        } else {
            $text .= "💡 Tell me your own yield to see how you compare:\n"
                   . "Type: \"My yield is 8000 kg\"";
        }

        return [
            'text' => $text,
            'actions' => [
                ['label' => '📖 Full Benchmark', 'url' => BASE_URL . '/advice/benchmark?crop=' . ucfirst($crop)],
                ['label' => '🏆 District ranking', 'url' => BASE_URL . '/advice/league?crop=' . ucfirst($crop)],
            ],
        ];
    }

    private function handleSell(array $profile, string $text): array {
        $crop = $this->detectCrop($text);

        $this->db->query("SELECT crop, market, district, price_per_kg
                          FROM market_prices
                          WHERE recorded_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
                          . ($crop ? " AND LOWER(crop) = :c" : "")
                          . " ORDER BY price_per_kg DESC LIMIT 5");
        if ($crop) $this->db->bind(':c', $crop);
        $rows = $this->db->resultSet();

        if (empty($rows)) {
            return ['text' => "I don't have selling options right now. Try again later."];
        }

        $text = "🏪 **Best markets for selling**" . ($crop ? " " . ucfirst($crop) : "") . ":\n\n";
        foreach ($rows as $i => $r) {
            $medal = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : '🥉');
            $text .= "{$medal} **{$r['market']}** ({$r['district']})\n";
            $text .= "   💰 " . number_format($r['price_per_kg']) . " RWF/kg — " . $r['crop'] . "\n\n";
        }

        $text .= "💡 **Tip:** Consider transport cost when choosing a market.";

        return [
            'text' => $text,
            'actions' => [
                ['label' => '💰 All prices', 'message' => 'Show all market prices'],
            ],
        ];
    }

    private function handleWeather(array $profile): array {
        $district = $profile['district'] ?? 'your area';

        // Check cached weather data
        $this->db->query("SELECT * FROM weather_data
                          WHERE district = :d AND date >= CURDATE()
                          ORDER BY date ASC LIMIT 7");
        $this->db->bind(':d', $profile['district'] ?? '');
        $rows = $this->db->resultSet();

        if (empty($rows)) {
            return [
                'text' => "🌤️ **Weather for {$district}:**\n\n"
                        . "Live weather data isn't available yet. Weather integration with Meteo Rwanda is coming soon!\n\n"
                        . "In the meantime, check the Weather page for forecasts.",
                'actions' => [
                    ['label' => '🌤️ Weather page', 'url' => BASE_URL . '/weather'],
                ],
            ];
        }

        $text = "🌤️ **7-day forecast for {$district}:**\n\n";
        foreach ($rows as $r) {
            $date = date('D, M d', strtotime($r['date']));
            $rain = (float)$r['rainfall_mm'];
            $rainIcon = $rain > 10 ? '🌧️' : ($rain > 0 ? '🌦️' : '☀️');
            $text .= "{$rainIcon} **{$date}** — {$rain} mm · "
                   . number_format((float)$r['temp_min'], 0) . "°C – "
                   . number_format((float)$r['temp_max'], 0) . "°C\n";
        }

        return ['text' => $text];
    }

    private function handlePests(string $text): array {
        $issues = [
            'late blight' => "🍅 **Late blight** (potatoes/tomatoes):\n"
                           . "• Apply fungicide (Mancozeb or Ridomil)\n"
                           . "• Remove infected plants immediately\n"
                           . "• Avoid overhead watering\n"
                           . "• Improve air circulation",

            'fall armyworm' => "🐛 **Fall Armyworm** (maize):\n"
                             . "• Scout early morning or evening\n"
                             . "• Handpick larvae if few\n"
                             . "• Apply Emamectin benzoate\n"
                             . "• Plant early maturing varieties",

            'aphids' => "🐜 **Aphids:**\n"
                      . "• Spray with soapy water\n"
                      . "• Introduce ladybugs\n"
                      . "• Apply neem oil extract\n"
                      . "• Avoid excess nitrogen fertilizer",

            'coffee berry disease' => "☕ **Coffee Berry Disease:**\n"
                                    . "• Apply copper fungicide\n"
                                    . "• Remove infected berries\n"
                                    . "• Prune for air circulation",

            'default' => "🐛 **Pest & Disease Help**\n\n"
                       . "Tell me more about your problem:\n"
                       . "• Which crop?\n"
                       . "• What are the symptoms?\n"
                       . "• When did you notice it?\n\n"
                       . "Common issues I can help with:\n"
                       . "• Late blight (potatoes, tomatoes)\n"
                       . "• Fall armyworm (maize)\n"
                       . "• Aphids (many crops)\n"
                       . "• Coffee berry disease",
        ];

        foreach ($issues as $key => $reply) {
            if ($key !== 'default' && strpos($text, $key) !== false) {
                return ['text' => $reply];
            }
        }
        return ['text' => $issues['default']];
    }

    private function handleFinance(): array {
        return [
            'text' => "💳 **Loans & Subsidies for Farmers**\n\n"
                    . "**Government Programs:**\n"
                    . "• 🌾 Fertilizer Subsidy — Apply via your cooperative\n"
                    . "• 🏦 SACCO Loans — Low interest (8-12%)\n"
                    . "• 🌱 Seed Support Program — Free for registered farmers\n\n"
                    . "**Requirements:**\n"
                    . "• Land title or lease agreement\n"
                    . "• National ID\n"
                    . "• Cooperative membership (recommended)\n\n"
                    . "**Where to apply:**\n"
                    . "• RAB district office\n"
                    . "• Your local SACCO branch\n"
                    . "• MINAGRI online portal",
            'actions' => [
                ['label' => '📄 RAB Website', 'url' => 'https://www.rab.gov.rw'],
                ['label' => '💬 Ask something else', 'message' => 'help'],
            ],
        ];
    }

    private function handleHelp(): array {
        return [
            'text' => "🤖 **I can help you with:**\n\n"
                    . "🌾 **Crop advice** — What to plant, when, and where\n"
                    . "💰 **Market prices** — Current prices for your crops\n"
                    . "📊 **Yield analysis** — Compare your output vs district\n"
                    . "🏪 **Where to sell** — Best markets for your produce\n"
                    . "🌤️ **Weather** — Forecast for your district\n"
                    . "🐛 **Pest & disease** — Identify and treat problems\n"
                    . "💳 **Finance** — Loans, subsidies, and grants\n\n"
                    . "Just type your question in **English** or **Kinyarwanda**.",

            'actions' => [
                ['label' => '🌾 What to plant?', 'message' => 'What should I plant?'],
                ['label' => '💰 Market prices', 'message' => 'Current market prices'],
                ['label' => '🐛 Pest help', 'message' => 'I have pests in my crops'],
            ],
        ];
    }

    private function handleThanks(): array {
        return ['text' => "🙏 You're welcome! Feel free to ask anytime. Mwiriwe!"];
    }

    private function handleFallback(array $profile): array {
        return [
            'text' => "I'm not sure I understood that. 🤔\n\n"
                    . "Try asking:\n"
                    . "• \"What should I plant?\"\n"
                    . "• \"Market prices for maize\"\n"
                    . "• \"How does my yield compare?\"\n"
                    . "• \"Where can I sell potatoes?\"\n"
                    . "• \"I have pests in my crops\"\n\n"
                    . "Or type **help** to see everything I can do.",
            'actions' => [
                ['label' => '🤖 What can you do?', 'message' => 'help'],
                ['label' => '🌾 Crops', 'message' => 'What should I plant?'],
            ],
        ];
    }

    /* ═══════════════════════════════════════════════════════════════
       HELPERS
       ═══════════════════════════════════════════════════════════════ */
    private function detectCrop(string $text): ?string {
        $crops = ['maize','potatoes','beans','rice','tomatoes','coffee','tea','bananas','cassava','sorghum','wheat'];
        foreach ($crops as $c) {
            if (strpos($text, $c) !== false) return $c;
        }
        return null;
    }
}