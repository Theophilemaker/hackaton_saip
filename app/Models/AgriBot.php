<?php
namespace App\Models;

use App\Core\Database;

class AgriBot {
    private $db = null;

    public function __construct() {
        try {
            $this->db = new Database();
        } catch (\Throwable $e) {
            $this->db = null;
        }
    }

    /* ============================================================
     *  MAIN RESPONSE ENGINE — Gemini first, local fallback
     * ============================================================ */
    public function respond($message, $lang = 'en', $context = []) {
        $message = (string)$message;
        $lang    = in_array($lang, ['en', 'rw', 'fr', 'sw'], true) ? $lang : 'en';

        /* ── Try Gemini AI first ── */
        if (class_exists('App\\Helpers\\GeminiHelper')) {
            try {
                $cls = 'App\\Helpers\\GeminiHelper';
                $gemini = new $cls();
                if (method_exists($gemini, 'isConfigured') && $gemini->isConfigured()) {
                    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
                    $convId = $userId ? $this->getOrCreateConversation($userId) : null;
                    $history = $convId ? $this->getHistory($convId) : [];
                    $reply = $gemini->chat($message, $history, $lang);
                    if (is_string($reply) && trim($reply) !== '') {
                        return $reply;
                    }
                }
            } catch (\Throwable $e) {
                error_log('[AgriBot Gemini] ' . $e->getMessage());
                /* fall through to local */
            }
        }

        /* ── Fallback: local rule-based responses ── */
        return $this->localRespond($message, $lang);
    }

    /* ============================================================
     *  LOCAL RULES ENGINE
     * ============================================================ */
    private function localRespond($message, $lang = 'en') {
        $m = mb_strtolower(trim($message));

        /* Greetings */
        if ($this->matches($m, ['hello', '^hi\b', 'hey', 'muraho', 'bonjour', 'salut', 'habari', 'jambo', 'hola'])) {
            return $this->t('greeting', $lang);
        }

        /* Thanks */
        if ($this->matches($m, ['thank', 'asante', 'merci', 'murakoze', 'grazie'])) {
            return $this->t('thanks', $lang);
        }

        /* Planting timing */
        if ($this->matches($m, ['when.*plant', 'planting time', 'igihe cyo gutera', 'quand planter', 'lini kupanda', 'best time', 'season'])) {
            $crop = $this->detectCrop($m);
            return $this->t('planting', $lang, ['crop' => $crop ?: 'your crop']);
        }

        /* Fertilizer */
        if ($this->matches($m, ['fertilizer', 'ifumbire', 'engrais', 'mbolea', 'npk', 'urea', 'dap', 'compost', 'manure'])) {
            $crop = $this->detectCrop($m);
            return $this->t('fertilizer', $lang, ['crop' => $crop ?: 'your crop']);
        }

        /* Disease / pest */
        if ($this->matches($m, ['disease', 'pest', 'indwara', 'uduhumbi', 'maladie', 'ugonjwa', 'worm', 'blight', 'rust', 'aphid', 'fungus', 'yellow leaves', 'wilting'])) {
            $crop = $this->detectCrop($m);
            return $this->t('disease', $lang, ['crop' => $crop ?: 'your crop']);
        }

        /* Weather */
        if ($this->matches($m, ['weather', 'rain', 'ikirere', 'imvura', 'météo', 'pluie', 'hali ya hewa', 'mvua', 'forecast', 'climate'])) {
            return $this->t('weather', $lang);
        }

        /* Market / price */
        if ($this->matches($m, ['price', 'market', 'sell', 'igiciro', 'isoko', 'prix', 'marché', 'bei', 'soko', 'buyer'])) {
            $crop = $this->detectCrop($m);
            return $this->t('market', $lang, ['crop' => $crop ?: 'your crop']);
        }

        /* Irrigation */
        if ($this->matches($m, ['irrigat', 'water', 'kubuhira', 'amazi', 'eau', 'umwagiliaji', 'maji', 'watering'])) {
            return $this->t('irrigation', $lang);
        }

        /* Yield */
        if ($this->matches($m, ['yield', 'harvest', 'umusaruro', 'isarura', 'rendement', 'récolte', 'mavuno', 'production'])) {
            $crop = $this->detectCrop($m);
            return $this->t('yield', $lang, ['crop' => $crop ?: 'your crop']);
        }

        /* Soil */
        if ($this->matches($m, ['soil', 'ubutaka', 'sol', 'udongo', 'ph', 'nutrient'])) {
            return $this->t('soil', $lang);
        }

        /* Storage */
        if ($this->matches($m, ['stor', 'ububiko', 'stock', 'post.harvest', 'warehouse'])) {
            return $this->t('storage', $lang);
        }

        /* Government / subsidy */
        if ($this->matches($m, ['subsid', 'government', 'serikali', 'gouvernement', 'guverinoma', 'funding', 'loan', 'credit'])) {
            return $this->t('government', $lang);
        }

        /* Fallback */
        return $this->t('fallback', $lang);
    }

    private function matches($text, $patterns) {
        foreach ($patterns as $p) {
            if (preg_match('/' . $p . '/i', $text)) return true;
        }
        return false;
    }

    private function detectCrop($text) {
        $map = [
            'maize' => 'Maize', 'corn' => 'Maize', 'ibigori' => 'Maize', 'maïs' => 'Maize', 'mahindi' => 'Maize',
            'bean'  => 'Beans', 'ibishyimbo' => 'Beans', 'haricot' => 'Beans', 'maharage' => 'Beans',
            'rice'  => 'Rice',  'umuceri'    => 'Rice',  'riz'     => 'Rice',  'mchele'   => 'Rice',
            'potato'=> 'Potatoes', 'ibirayi'=>'Potatoes','pomme'  => 'Potatoes','viazi'  => 'Potatoes',
            'coffee'=> 'Coffee', 'ikahawa'   => 'Coffee','café'   => 'Coffee','kahawa'  => 'Coffee',
            'tomato'=> 'Tomatoes','inyanya'  => 'Tomatoes','tomate'=>'Tomatoes','nyanya'=>'Tomatoes',
            'banana'=> 'Bananas', 'igitoki'  => 'Bananas','banane' => 'Bananas',
            'tea'   => 'Tea',   'icyayi'    => 'Tea',
            'cassava'=>'Cassava','imyumbati'=>'Cassava',
            'wheat' => 'Wheat', 'ble'       => 'Wheat', 'ngano'  => 'Wheat',
        ];
        foreach ($map as $key => $crop) {
            if (str_contains($text, $key)) return $crop;
        }
        return null;
    }

    /* ============================================================
     *  LOCALISED RESPONSES
     * ============================================================ */
    private function t($intent, $lang, $vars = []) {
        $responses = [
            'en' => [
                'greeting'   => "Hello! I'm **AgriBot** 🌱 — your AI farming assistant for Rwanda.\n\nI can help with:\n• 🌾 Planting schedules\n• 🧪 Fertilizer & soil advice\n• 🐛 Disease and pest identification\n• 🌦️ Weather-based recommendations\n• 💰 Market prices\n• 💧 Irrigation planning\n\nWhat would you like to know?",
                'thanks'     => "You're welcome! 🌾 Feel free to ask me anything else about your farm.",
                'planting'   => "**Best planting window for {crop}:**\n\n🌱 **Season A** — September to October\n🌱 **Season B** — February to March\n\nEnsure:\n• Soil moisture is adequate\n• Temperature stays between 18–28°C\n• Use certified seeds for higher yields\n\n💡 Check the *Weather* page for a 5-day forecast before planting.",
                'fertilizer' => "**Recommended fertilizer for {crop}:**\n\n🧪 **At planting:** NPK 17-17-17 (50%)\n🧪 **At flowering:** NPK 17-17-17 (50%)\n🧪 **Top-dress:** Urea (if nitrogen is low)\n\n📊 Typical rates: 100–150 kg/ha\n\n💡 Test your soil first for precise dosing — check the *Crops → Soil Tests* feature.",
                'disease'    => "**Managing diseases in {crop}:**\n\n📷 **Step 1:** Upload a photo on the *Crop Intelligence → Disease Detection* page\n\n🔬 **Common threats:**\n• Fall Armyworm (maize)\n• Potato Blight\n• Coffee Leaf Rust\n• Banana Wilt\n\n⚡ Early detection can save 30% of your yield.\n\n👨‍🌾 Contact your district agricultural officer for a free diagnosis.",
                'weather'    => "**Weather Intelligence:**\n\n🌤️ Visit the *Weather* page for:\n• Live temperature & humidity\n• 5-day forecast\n• Rainfall prediction\n• AI planting/irrigation advice\n\n📱 Alerts are sent automatically when heavy rain or drought is expected in your district.",
                'market'     => "**Market prices for {crop}:**\n\n📊 Live prices are on the *Marketplace* page.\n💰 **Maize:** ~350 RWF/kg\n💰 **Beans:** ~620 RWF/kg\n💰 **Potatoes:** ~280 RWF/kg\n\n💡 Use the *Negotiate* button on any listing to get a better price from sellers.\n\n🤖 AI Price Advisor suggests the best selling window based on supply and demand.",
                'irrigation' => "**Irrigation guidance:**\n\n💧 **Best times:** early morning (6–8 AM) or late evening (5–7 PM)\n💧 **Skip irrigation if:** rainfall today exceeded 5mm\n💧 **Drip irrigation** saves up to 40% water vs. furrow\n\n🌧️ If rain is forecast tomorrow, wait to conserve water.",
                'yield'      => "**Yield expectations for {crop}:**\n\n🌾 **Maize:** ~2.5 t/ha\n🌾 **Beans:** ~1.2 t/ha\n🌾 **Potatoes:** ~12 t/ha\n🌾 **Coffee:** ~1.1 t/ha (green bean)\n\n📈 Boost yields with:\n• Certified seeds\n• Correct spacing\n• Timely fertilizer\n• Proper weeding\n\n🤖 Check the *Crop Intelligence* page for AI-predicted yield for your farm.",
                'soil'       => "**Soil health tips:**\n\n🪨 **Test pH:** Ideal is 6.0–6.8 for most crops\n🪨 **Organic matter:** Add compost or manure\n🪨 **Nutrients:** Balance N-P-K with soil tests\n🪨 **Erosion control:** Use terraces on slopes\n\n📊 Visit the *Crops → Soil Tests* page to record your test results.",
                'storage'    => "**Post-harvest storage:**\n\n🏭 **Hermetic bags** keep grain safe from weevils\n🏭 **Dry to <13% moisture** before storage\n🏭 **Temperature:** 15–20°C ideal for most crops\n🏭 **Check monthly** for pests and mold\n\n🏭 Track your bins on the *Storage* page — get automatic expiry alerts.",
                'government' => "**Government support programs:**\n\n🏛️ **MINAGRI** provides:\n• Subsidized seeds and fertilizer\n• Irrigation scheme access\n• Extension officer visits\n• Post-harvest facility support\n\n📞 Contact your **district agricultural officer** for enrollment.\n\n📊 The *Government Analytics* page shows national programs and alerts.",
                'fallback'   => "I didn't quite understand that. 🤔\n\nTry asking:\n• *\"When should I plant maize?\"*\n• *\"What fertilizer for potatoes?\"*\n• *\"How to treat coffee rust?\"*\n• *\"What's the price of beans?\"*\n• *\"When to irrigate tomatoes?\"*",
            ],

            'rw' => [
                'greeting'   => "Muraho! Ndi **AgriBot** 🌱 — umufasha wawe wa AI mu buhinzi bw'u Rwanda.\n\nNshobora kugufasha kuri:\n• 🌾 Igihe cyo gutera\n• 🧪 Ifumbire n'ubutaka\n• 🐛 Indwara n'udukoko\n• 🌦️ Inama zishingiye ku kirere\n• 💰 Ibiciro ku isoko\n• 💧 Kubuhira\n\nNi iki ushaka kumenya?",
                'thanks'     => "Murakoze! 🌾 Baza ikindi kintu cyose ku murima wawe.",
                'planting'   => "**Igihe cyiza cyo gutera {crop}:**\n\n🌱 **Igihembwe A** — Nzeli–Ukwakira\n🌱 **Igihembwe B** — Gashyantare–Werurwe\n\nMenya neza:\n• Ubuhehere bw'ubutaka buhagije\n• Ubushyuhe 18–28°C\n• Imbuto zemejwe\n\n💡 Reba *Ikirere* mbere yo gutera.",
                'fertilizer' => "**Ifumbire yifuzwa kuri {crop}:**\n\n🧪 **Mu gutera:** NPK 17-17-17 (50%)\n🧪 **Mu gihe cy'indabo:** NPK 17-17-17 (50%)\n🧪 **Kongeraho:** Urea (niba azote ari nke)\n\n📊 Dosage: 100–150 kg/ha\n\n💡 Gerageza ubutaka mbere — reba *Ibihingwa → Isuzuma ry'Ubutaka*.",
                'disease'    => "**Kurwanya indwara kuri {crop}:**\n\n📷 **Intambwe 1:** Ohereza ifoto kuri *Ubumenyi bw'Ibihingwa → Kumenya Indwara*\n\n🔬 **Indwara zikunze kuboneka:**\n• Armyworm (ibigori)\n• Blight y'ibirayi\n• Rust y'ikahawa\n• Wilt y'igitoki\n\n⚡ Kumenya hakiri kare bituma ubona 30% by'umusaruro.\n\n👨‍🌾 Vugana n'umukozi w'ubuhinzi wo mu karere kawe.",
                'weather'    => "**Amakuru y'Ikirere:**\n\n🌤️ Sura ipaji *Ikirere* kubona:\n• Ubushyuhe n'ubuhehere\n• Iteganyagihe ry'iminsi 5\n• Imvura iteganyijwe\n• Inama za AI\n\n📱 Uhabwa amatangazo iyo imvura nyinshi cyangwa amapfa ateganyijwe.",
                'market'     => "**Ibiciro bya {crop}:**\n\n📊 Ibiciro biri kuri *Isoko*.\n💰 **Ibigori:** ~350 RWF/kg\n💰 **Ibishyimbo:** ~620 RWF/kg\n💰 **Ibirayi:** ~280 RWF/kg\n\n💡 Koresha buto ya *Ganira* ku rutonde kugira ngo ubone igiciro cyiza.",
                'irrigation' => "**Inama zo Kubuhira:**\n\n💧 **Igihe cyiza:** mu gitondo (6–8) cyangwa nimugoroba (17–19)\n💧 **Ntukabuhire niba** imvura irenze 5mm uyu munsi\n💧 **Drip irrigation** izigama amazi kugeza kuri 40%\n\n🌧️ Niba imvura iteganyijwe ejo, tegereza kugira ngo uzigame amazi.",
                'yield'      => "**Umusaruro uteganyijwe kuri {crop}:**\n\n🌾 **Ibigori:** ~2.5 t/ha\n🌾 **Ibishyimbo:** ~1.2 t/ha\n🌾 **Ibirayi:** ~12 t/ha\n🌾 **Ikawa:** ~1.1 t/ha\n\n📈 Kongera umusaruro ukoresheje:\n• Imbuto zemejwe\n• Intera ikwiye\n• Ifumbire ku gihe\n\n🤖 Reba *Ubumenyi bw'Ibihingwa* kubona iteganyagihe ry'umurima wawe.",
                'soil'       => "**Ubuzima bw'ubutaka:**\n\n🪨 **PH:** 6.0–6.8 ni byiza ku bihingwa byinshi\n🪨 **Organic matter:** Ongeraho compost\n🪨 **Intungamubiri:** N-P-K biringanye\n🪨 **Kurwanya isuri:** Koresha amaterasi\n\n📊 Sura *Ibihingwa → Isuzuma ry'Ubutaka*.",
                'storage'    => "**Kubika umusaruro:**\n\n🏭 **Hermetic bags** zirinda ibigori udukoko\n🏭 **Kuma kugeza <13%** mbere yo kubika\n🏭 **Ubushyuhe:** 15–20°C\n🏭 **Genzura buri kwezi** udukoko\n\n🏭 Kurikirana ibice kuri *Ububiko* — ubona amatangazo y'igihe kirangiye.",
                'government' => "**Inkunga z'ubutegetsi:**\n\n🏛️ **MINAGRI** itanga:\n• Imbuto n'ifumbire byoroheje\n• Uburyo bwo kubuhira\n• Gusurwa n'umukozi w'ubuhinzi\n• Inkunga y'ububiko\n\n📞 Vugana n'umukozi w'ubuhinzi wo mu karere kawe.",
                'fallback'   => "Sinumvise neza. 🤔\n\nGerageza ubaze:\n• *\"Ni ryari gutera ibigori?\"*\n• *\"Ifumbire iyihe ku birayi?\"*\n• *\"Bavura gute rust y'ikahawa?\"*\n• *\"Igiciro cy'ibishyimbo ni angahe?\"*\n• *\"Ni ryari kubuhira inyanya?\"*",
            ],

            'fr' => [
                'greeting'   => "Bonjour ! Je suis **AgriBot** 🌱 — votre assistant agricole IA pour le Rwanda.\n\nJe peux vous aider avec :\n• 🌾 Calendrier de plantation\n• 🧪 Engrais et sol\n• 🐛 Maladies et ravageurs\n• 🌦️ Conseils météo\n• 💰 Prix du marché\n• 💧 Planification d'irrigation\n\nQue voulez-vous savoir ?",
                'thanks'     => "De rien ! 🌾 N'hésitez pas à poser d'autres questions sur votre ferme.",
                'planting'   => "**Meilleure période pour {crop} :**\n\n🌱 **Saison A** — Septembre à Octobre\n🌱 **Saison B** — Février à Mars\n\nAssurez-vous que :\n• L'humidité du sol est adéquate\n• La température reste entre 18–28°C\n• Utilisez des semences certifiées\n\n💡 Consultez la page *Météo* pour les prévisions 5 jours.",
                'fertilizer' => "**Engrais recommandé pour {crop} :**\n\n🧪 **À la plantation :** NPK 17-17-17 (50%)\n🧪 **À la floraison :** NPK 17-17-17 (50%)\n🧪 **Top-dress :** Urée\n\n📊 Taux : 100–150 kg/ha\n\n💡 Testez votre sol d'abord — voir *Cultures → Tests de Sol*.",
                'disease'    => "**Gestion des maladies de {crop} :**\n\n📷 **Étape 1 :** Téléchargez une photo sur *Intelligence Cultures → Détection*\n\n🔬 **Menaces courantes :**\n• Chenille légionnaire (maïs)\n• Mildiou (pommes de terre)\n• Rouille du caféier\n• Flétrissement du bananier\n\n⚡ La détection précoce peut sauver 30% du rendement.",
                'weather'    => "**Intelligence Météo :**\n\n🌤️ Visitez la page *Météo* pour :\n• Température et humidité en direct\n• Prévisions 5 jours\n• Prédiction de pluie\n• Conseils IA",
                'market'     => "**Prix du marché pour {crop} :**\n\n📊 Prix en direct sur la page *Marché*.\n💰 **Maïs :** ~350 RWF/kg\n💰 **Haricots :** ~620 RWF/kg\n💰 **Pommes de terre :** ~280 RWF/kg",
                'irrigation' => "**Conseils d'irrigation :**\n\n💧 **Meilleurs moments :** tôt le matin ou tard le soir\n💧 **Sautez** si la pluie dépasse 5mm\n💧 **Goutte-à-goutte** économise 40% d'eau",
                'yield'      => "**Rendements attendus pour {crop} :**\n\n🌾 **Maïs :** ~2.5 t/ha\n🌾 **Haricots :** ~1.2 t/ha\n🌾 **Pommes de terre :** ~12 t/ha\n🌾 **Café :** ~1.1 t/ha",
                'soil'       => "**Santé du sol :**\n\n🪨 **pH :** 6.0–6.8 idéal\n🪨 **Matière organique :** Ajoutez du compost\n🪨 **Nutriments :** Équilibrez N-P-K",
                'storage'    => "**Stockage après récolte :**\n\n🏭 **Sacs hermétiques** protègent le grain\n🏭 **Séchez à <13%** d'humidité\n🏭 **Température :** 15–20°C",
                'government' => "**Programmes gouvernementaux :**\n\n🏛️ **MINAGRI** fournit :\n• Semences et engrais subventionnés\n• Accès aux systèmes d'irrigation\n• Visites d'agents de vulgarisation\n\n📞 Contactez votre agent agricole de district.",
                'fallback'   => "Je n'ai pas bien compris. 🤔\n\nEssayez :\n• *« Quand planter le maïs ? »*\n• *« Quel engrais pour les pommes de terre ? »*\n• *« Comment traiter la rouille du café ? »*",
            ],

            'sw' => [
                'greeting'   => "Habari! Mimi ni **AgriBot** 🌱 — msaidizi wako wa kilimo wa AI kwa Rwanda.\n\nNaweza kukusaidia na:\n• 🌾 Ratiba ya kupanda\n• 🧪 Mbolea na udongo\n• 🐛 Magonjwa na wadudu\n• 🌦️ Ushauri wa hali ya hewa\n• 💰 Bei za soko\n• 💧 Umwagiliaji\n\nUnataka kujua nini?",
                'thanks'     => "Karibu! 🌾 Uliza kitu chochote kuhusu shamba lako.",
                'planting'   => "**Wakati bora wa kupanda {crop}:**\n\n🌱 **Msimu A** — Septemba–Oktoba\n🌱 **Msimu B** — Februari–Machi\n\nHakikisha:\n• Unyevu wa udongo unatosha\n• Joto ni 18–28°C\n• Mbegu zilizothibitishwa",
                'fertilizer' => "**Mbolea inayopendekezwa kwa {crop}:**\n\n🧪 **Wakati wa kupanda:** NPK 17-17-17 (50%)\n🧪 **Wakati wa maua:** NPK 17-17-17 (50%)\n🧪 **Kuweka juu:** Urea\n\n📊 Kiwango: 100–150 kg/ha",
                'disease'    => "**Kudhibiti magonjwa ya {crop}:**\n\n📷 **Hatua 1:** Pakia picha kwenye *Akili ya Mazao → Kugundua*\n\n🔬 **Hatari za kawaida:**\n• Viwavi jeshi (mahindi)\n• Baka jani (viazi)\n• Kuvu ya majani (kahawa)",
                'weather'    => "**Akili ya Hali ya Hewa:**\n\n🌤️ Tembelea ukurasa wa *Hali ya Hewa* kwa:\n• Joto na unyevu wa moja kwa moja\n• Utabiri wa siku 5\n• Utabiri wa mvua",
                'market'     => "**Bei za soko za {crop}:**\n\n📊 Bei za moja kwa moja kwenye ukurasa wa *Soko*.\n💰 **Mahindi:** ~350 RWF/kg\n💰 **Maharage:** ~620 RWF/kg",
                'irrigation' => "**Ushauri wa Umwagiliaji:**\n\n💧 **Nyakati bora:** asubuhi mapema au jioni\n💧 **Ruka** ikiwa mvua ilizidi 5mm",
                'yield'      => "**Mavuno yanayotarajiwa kwa {crop}:**\n\n🌾 **Mahindi:** ~2.5 t/ha\n🌾 **Maharage:** ~1.2 t/ha",
                'soil'       => "**Afya ya udongo:**\n\n🪨 **pH:** 6.0–6.8 bora\n🪨 **Mboji:** Ongeza komposti",
                'storage'    => "**Kuhifadhi baada ya mavuno:**\n\n🏭 **Mifuko ya hermetic** inalinda nafaka\n🏭 **Kausha hadi <13%** unyevu",
                'government' => "**Programu za serikali:**\n\n🏛️ **MINAGRI** inatoa:\n• Mbegu na mbolea zilizopunguzwa bei\n• Ufikiaji wa umwagiliaji\n\n📞 Wasiliana na afisa wa kilimo wa wilaya yako.",
                'fallback'   => "Sikuelewa vizuri. 🤔\n\nJaribu:\n• *\"Ni lini kupanda mahindi?\"*\n• *\"Mbolea gani kwa viazi?\"*",
            ],
        ];

        $msg = $responses[$lang][$intent] ?? $responses['en'][$intent] ?? $responses['en']['fallback'];
        foreach ($vars as $k => $v) {
            $msg = str_replace('{' . $k . '}', (string)$v, $msg);
        }
        return $msg;
    }

    /* ============================================================
     *  CONVERSATION STORAGE
     * ============================================================ */
    public function getOrCreateConversation($userId) {
        if (!$this->db) return null;
        try {
            $this->db->query("SELECT * FROM ai_conversations WHERE user_id = :u ORDER BY created_at DESC LIMIT 1");
            $this->db->bind(':u', (int)$userId);
            $conv = $this->db->single();
            if ($conv && !empty($conv['id'])) return (int)$conv['id'];

            $this->db->query("INSERT INTO ai_conversations (uuid, user_id, title) VALUES (:u, :uid, 'New Chat')");
            $this->db->bind(':u', uniqid('ai_', true));
            $this->db->bind(':uid', (int)$userId);
            $this->db->execute();
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[AgriBot conv] ' . $e->getMessage());
            return null;
        }
    }

    public function saveMessage($convId, $role, $content, $lang = 'en') {
        if (!$this->db || !$convId) return false;
        try {
            $this->db->query("INSERT INTO ai_messages (conversation_id, role, content, language)
                              VALUES (:c, :r, :ct, :l)");
            $this->db->bind(':c', (int)$convId);
            $this->db->bind(':r', $role);
            $this->db->bind(':ct', $content);
            $this->db->bind(':l', $lang);
            return $this->db->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getHistory($convId, $limit = 50) {
        if (!$this->db || !$convId) return [];
        try {
            $this->db->query("SELECT * FROM ai_messages WHERE conversation_id = :c ORDER BY created_at ASC LIMIT :n");
            $this->db->bind(':c', (int)$convId);
            $this->db->bind(':n', (int)$limit);
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}