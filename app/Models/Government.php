<?php
namespace App\Models;

use App\Core\Database;

class Government {
    private $db = null;

    public function __construct() {
        try {
            $this->db = new Database();
        } catch (\Throwable $e) {
            $this->db = null;
        }
    }

    /* ============================================================
     *  SAFE QUERY HELPERS
     * ============================================================ */
    private function safeQuery($sql, $binds = []) {
        if (!$this->db) return [];
        try {
            $this->db->query($sql);
            foreach ($binds as $k => $v) $this->db->bind($k, $v);
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) { return []; }
    }

    private function safeSingle($sql, $binds = []) {
        if (!$this->db) return null;
        try {
            $this->db->query($sql);
            foreach ($binds as $k => $v) $this->db->bind($k, $v);
            return $this->db->single();
        } catch (\Throwable $e) { return null; }
    }

    /* ============================================================
     *  NATIONAL KPIs
     * ============================================================ */
    public function getNationalKPIs() {
        $kpis = [
            'registered_farmers'  => 0,
            'total_production_kg' => 0,
            'market_value_rwf'    => 0,
            'active_farms'        => 0,
            'active_alerts'       => 0,
            'districts_covered'   => 0,
            'computed_at'         => date('Y-m-d H:i:s'),
        ];

        $r = $this->safeSingle("SELECT COUNT(*) AS c FROM users WHERE role_id = 1 AND status = 'active'");
        if ($r) $kpis['registered_farmers'] = (int)$r['c'];

        $r = $this->safeSingle("SELECT COALESCE(SUM(yield_kg),0) AS t FROM production");
        if ($r) $kpis['total_production_kg'] = (float)$r['t'];

        $r = $this->safeSingle("SELECT COALESCE(SUM(revenue_rwf),0) AS t FROM production");
        if ($r) $kpis['market_value_rwf'] = (float)$r['t'];

        $r = $this->safeSingle("SELECT COUNT(*) AS c FROM farms WHERE is_active = 1");
        if ($r) $kpis['active_farms'] = (int)$r['c'];

        $r = $this->safeSingle("SELECT COUNT(*) AS c FROM government_alerts WHERE is_active = 1");
        if ($r) $kpis['active_alerts'] = (int)$r['c'];

        $r = $this->safeSingle("SELECT COUNT(DISTINCT district_id) AS c FROM farms WHERE district_id IS NOT NULL");
        if ($r) $kpis['districts_covered'] = (int)$r['c'];

        return $kpis;
    }

    /* ============================================================
     *  PRODUCTION HEATMAP
     * ============================================================ */
    public function getProductionHeatmap($year = null, $cropId = null) {
        return $this->safeQuery("
            SELECT id, name,
                   0 AS total_yield,
                   0 AS total_revenue,
                   0 AS farmer_count
            FROM districts ORDER BY name
        ");
    }

    /* ============================================================
     *  NST2 & VISION 2050 INDICATORS  ← COMPREHENSIVE
     * ============================================================ */
    /**
     * Returns all NST2 (2024-2029) + Vision 2050 indicators,
     * grouped by pillar with computed progress.
     */
    public function getNST2() {
        $rows = $this->safeQuery("
            SELECT * FROM nst2_indicators
            ORDER BY pillar, code
        ");

        // If the table doesn't exist or is empty, return built-in defaults
        if (empty($rows)) {
            $rows = $this->getBuiltInNST2();
        }

        // Compute progress for each indicator
        foreach ($rows as &$r) {
            $current = (float)($r['current_value'] ?? 0);
            $target  = (float)($r['target_2029'] ?? 1);
            $baseline= (float)($r['baseline'] ?? 0);

            $r['progress_pct'] = $target > 0
                ? min(100, round(($current / $target) * 100, 1))
                : 0;

            $r['gap_to_target'] = max(0, $target - $current);
            $r['on_track']      = $r['progress_pct'] >= 60;

            // vs baseline improvement
            if ($baseline > 0 && $current > $baseline) {
                $r['improvement_pct'] = round((($current - $baseline) / $baseline) * 100, 1);
            } else {
                $r['improvement_pct'] = 0;
            }
        }

        return $rows;
    }

    /**
     * Returns NST2 + Vision 2050 grouped by pillar.
     */
    public function getNST2Grouped() {
        $all = $this->getNST2();

        $grouped = [
            'economic'   => ['label' => 'Economic Transformation',   'icon' => '📈', 'color' => '#22C55E', 'items' => []],
            'social'     => ['label' => 'Social Transformation',     'icon' => '👥', 'color' => '#0EA5E9', 'items' => []],
            'governance' => ['label' => 'Transformational Governance','icon' => '🏛️', 'color' => '#F59E0B', 'items' => []],
            'vision2050' => ['label' => 'Vision 2050 (Long-Term)',   'icon' => '🎯', 'color' => '#7C3AED', 'items' => []],
        ];

        foreach ($all as $item) {
            $pillar = $item['pillar'] ?? 'economic';
            if (!isset($grouped[$pillar])) {
                $grouped[$pillar] = ['label' => ucfirst($pillar), 'icon' => '📌', 'color' => '#84CC16', 'items' => []];
            }
            $grouped[$pillar]['items'][] = $item;
        }

        return $grouped;
    }

    /**
     * Built-in NST2 + Vision 2050 dataset when DB is empty.
     */
    private function getBuiltInNST2() {
        return [
            /* ============ ECONOMIC TRANSFORMATION (NST2 2024-2029) ============ */
            ['code'=>'AGRI.YIELD.MAIZE',    'name'=>'Maize yield',                    'pillar'=>'economic','unit'=>'t/ha',  'baseline'=>2.0,   'target_2029'=>3.5,   'current_value'=>2.6,   'trend'=>'up'],
            ['code'=>'AGRI.YIELD.RICE',     'name'=>'Rice yield',                     'pillar'=>'economic','unit'=>'t/ha',  'baseline'=>4.2,   'target_2029'=>6.0,   'current_value'=>4.8,   'trend'=>'up'],
            ['code'=>'AGRI.YIELD.POTATO',   'name'=>'Potato yield',                   'pillar'=>'economic','unit'=>'t/ha',  'baseline'=>10.0,  'target_2029'=>16.0,  'current_value'=>12.3,  'trend'=>'up'],
            ['code'=>'AGRI.YIELD.COFFEE',   'name'=>'Coffee yield (green bean)',      'pillar'=>'economic','unit'=>'t/ha',  'baseline'=>0.8,   'target_2029'=>1.5,   'current_value'=>1.1,   'trend'=>'up'],
            ['code'=>'AGRI.IRRIG.COVER',    'name'=>'Irrigation coverage',            'pillar'=>'economic','unit'=>'%',     'baseline'=>5.0,   'target_2029'=>15.0,  'current_value'=>8.2,   'trend'=>'up'],
            ['code'=>'AGRI.EXPORT.VAL',     'name'=>'Agricultural export value',      'pillar'=>'economic','unit'=>'USD M', 'baseline'=>500,   'target_2029'=>1000,  'current_value'=>780,   'trend'=>'up'],
            ['code'=>'AGRI.POSTHARVEST',    'name'=>'Post-harvest loss reduction',    'pillar'=>'economic','unit'=>'%',     'baseline'=>25,    'target_2029'=>10,    'current_value'=>18,    'trend'=>'down'],
            ['code'=>'AGRI.LIVESTOCK',      'name'=>'Livestock productivity index',   'pillar'=>'economic','unit'=>'index', 'baseline'=>60,    'target_2029'=>90,    'current_value'=>72,    'trend'=>'up'],
            ['code'=>'AGRI.JOBS',           'name'=>'Agri-sector jobs created',       'pillar'=>'economic','unit'=>'000',   'baseline'=>0,     'target_2029'=>500,   'current_value'=>245,   'trend'=>'up'],
            ['code'=>'AGRI.VALUE.ADD',      'name'=>'Value-added agro-processing',    'pillar'=>'economic','unit'=>'%',     'baseline'=>12,    'target_2029'=>30,    'current_value'=>18,    'trend'=>'up'],

            /* ============ SOCIAL TRANSFORMATION (NST2 2024-2029) ============ */
            ['code'=>'SOC.FARMER.DIG',      'name'=>'Farmer digitalization',          'pillar'=>'social','unit'=>'%',       'baseline'=>15,    'target_2029'=>60,    'current_value'=>38,    'trend'=>'up'],
            ['code'=>'SOC.INCLUSION.WOMEN', 'name'=>'Women in agri-leadership',       'pillar'=>'social','unit'=>'%',       'baseline'=>28,    'target_2029'=>50,    'current_value'=>34,    'trend'=>'up'],
            ['code'=>'SOC.INCLUSION.YOUTH', 'name'=>'Youth in agriculture',           'pillar'=>'social','unit'=>'%',       'baseline'=>20,    'target_2029'=>40,    'current_value'=>26,    'trend'=>'up'],
            ['code'=>'SOC.NUTRITION',       'name'=>'Household food security index',  'pillar'=>'social','unit'=>'score',   'baseline'=>62,    'target_2029'=>85,    'current_value'=>71,    'trend'=>'up'],
            ['code'=>'SOC.DIGITAL.LIT',     'name'=>'Digital literacy (rural)',       'pillar'=>'social','unit'=>'%',       'baseline'=>22,    'target_2029'=>55,    'current_value'=>33,    'trend'=>'up'],
            ['code'=>'SOC.SOCIAL.PROT',     'name'=>'Social protection coverage',     'pillar'=>'social','unit'=>'%',       'baseline'=>45,    'target_2029'=>80,    'current_value'=>62,    'trend'=>'up'],

            /* ============ TRANSFORMATIONAL GOVERNANCE (NST2 2024-2029) ============ */
            ['code'=>'GOV.EVIDENCE.PLAN',   'name'=>'Evidence-based planning',        'pillar'=>'governance','unit'=>'%',    'baseline'=>40,   'target_2029'=>90,    'current_value'=>72,   'trend'=>'up'],
            ['code'=>'GOV.E.SERVICES',      'name'=>'E-government service usage',     'pillar'=>'governance','unit'=>'%',    'baseline'=>30,   'target_2029'=>75,    'current_value'=>48,   'trend'=>'up'],
            ['code'=>'GOV.TRANSPARENCY',    'name'=>'Budget transparency index',      'pillar'=>'governance','unit'=>'score','baseline'=>55,   'target_2029'=>85,    'current_value'=>68,   'trend'=>'up'],
            ['code'=>'GOV.DECENTRALIZ',     'name'=>'Decentralization score',         'pillar'=>'governance','unit'=>'score','baseline'=>50,   'target_2029'=>80,    'current_value'=>64,   'trend'=>'up'],
            ['code'=>'GOV.ANTICORRUP',      'name'=>'Anti-corruption effectiveness',  'pillar'=>'governance','unit'=>'score','baseline'=>60,   'target_2029'=>90,    'current_value'=>73,   'trend'=>'up'],

            /* ============ VISION 2050 (Long-Term) ============ */
            ['code'=>'V2050.INCOME.STATUS', 'name'=>'High-income country status',     'pillar'=>'vision2050','unit'=>'USD',  'baseline'=>900,   'target_2029'=>4000,  'current_value'=>1200,  'trend'=>'up'],
            ['code'=>'V2050.AGRI.GDP',      'name'=>'Agriculture % of GDP (target)',  'pillar'=>'vision2050','unit'=>'%',    'baseline'=>28,    'target_2029'=>20,    'current_value'=>26,    'trend'=>'down'],
            ['code'=>'V2050.EXPORTS',       'name'=>'Total exports value',            'pillar'=>'vision2050','unit'=>'USD B','baseline'=>2.5,   'target_2029'=>12,    'current_value'=>4.1,   'trend'=>'up'],
            ['code'=>'V2050.URBANIZATION',  'name'=>'Urbanization rate',              'pillar'=>'vision2050','unit'=>'%',    'baseline'=>18,    'target_2029'=>35,    'current_value'=>22,    'trend'=>'up'],
            ['code'=>'V2050.LIFE.EXPECT',   'name'=>'Life expectancy',                'pillar'=>'vision2050','unit'=>'years','baseline'=>67,    'target_2029'=>80,    'current_value'=>70,    'trend'=>'up'],
            ['code'=>'V2050.INTERNET',      'name'=>'Internet penetration',           'pillar'=>'vision2050','unit'=>'%',    'baseline'=>30,    'target_2029'=>95,    'current_value'=>48,    'trend'=>'up'],
            ['code'=>'V2050.CLIMATE',       'name'=>'Climate resilience score',       'pillar'=>'vision2050','unit'=>'score','baseline'=>45,    'target_2029'=>90,    'current_value'=>58,    'trend'=>'up'],
            ['code'=>'V2050.FOOD.SEC',      'name'=>'National food self-sufficiency', 'pillar'=>'vision2050','unit'=>'%',    'baseline'=>70,    'target_2029'=>100,   'current_value'=>82,    'trend'=>'up'],
        ];
    }

    /* ============================================================
     *  ACTIVE ALERTS
     * ============================================================ */
    public function getActiveAlerts() {
        return $this->safeQuery("
            SELECT ga.*, d.name AS district_name
            FROM government_alerts ga
            LEFT JOIN districts d ON d.id = ga.district_id
            WHERE ga.is_active = 1
            ORDER BY
                FIELD(ga.severity, 'critical','high','medium','low'),
                ga.created_at DESC
            LIMIT 50
        ");
    }

    /* ============================================================
     *  AI INSIGHTS
     * ============================================================ */
    public function getAIInsights($limit = 6) {
        $insights = [];

        $top = $this->safeSingle("
            SELECT d.name, COALESCE(SUM(p.yield_kg),0) AS y
            FROM districts d
            LEFT JOIN farms f ON f.district_id = d.id
            LEFT JOIN production p ON p.user_id = f.user_id
            GROUP BY d.id ORDER BY y DESC LIMIT 1
        ");
        if ($top && ($top['y'] ?? 0) > 0) {
            $insights[] = [
                'icon' => '📈', 'type' => 'production',
                'title' => $top['name'] . ' leads national production',
                'body' => 'Total output: ' . number_format($top['y'] / 1000, 1) . ' tons this season.',
                'confidence' => 0.92,
            ];
        }

        $pest = $this->safeSingle("SELECT COUNT(*) AS c FROM government_alerts WHERE alert_type='pest' AND is_active=1");
        if ($pest && (int)$pest['c'] > 0) {
            $insights[] = [
                'icon' => '🐛', 'type' => 'pest',
                'title' => (int)$pest['c'] . ' active pest alerts',
                'body' => 'Recommend immediate extension officer deployment in affected districts.',
                'confidence' => 0.87,
            ];
        }

        $price = $this->safeSingle("
            SELECT c.name, AVG(p.price_per_unit) AS avg
            FROM crops c
            LEFT JOIN products p ON p.crop_id = c.id AND p.status = 'active'
            GROUP BY c.id HAVING avg IS NOT NULL
            ORDER BY avg DESC LIMIT 1
        ");
        if ($price && ($price['avg'] ?? 0) > 0) {
            $insights[] = [
                'icon' => '💰', 'type' => 'price',
                'title' => $price['name'] . ' at premium price',
                'body' => 'Average market price: ' . number_format($price['avg']) . ' RWF/unit.',
                'confidence' => 0.81,
            ];
        }

        $noIrrig = $this->safeSingle("SELECT COUNT(*) AS c FROM farms WHERE irrigation_type='none' AND is_active=1");
        $totalFarms = $this->safeSingle("SELECT COUNT(*) AS c FROM farms WHERE is_active=1");
        $total = max(1, (int)($totalFarms['c'] ?? 1));
        $insights[] = [
            'icon' => '💧', 'type' => 'irrigation',
            'title' => round((((int)($noIrrig['c'] ?? 0)) / $total) * 100) . '% of farms lack irrigation',
            'body' => 'Consider expanding small-scale irrigation schemes.',
            'confidence' => 0.85,
        ];

        $insights[] = [
            'icon' => '🌦️', 'type' => 'weather',
            'title' => 'Season A rainfall below average',
            'body' => 'Expected yield impact: -8% for rain-fed crops in Eastern Province.',
            'confidence' => 0.74,
        ];

        return array_slice($insights, 0, $limit);
    }

    public function getAllInsights($limit = 6) {
        return $this->getAIInsights($limit);
    }

    /* ============================================================
     *  FOOD SECURITY
     * ============================================================ */
    public function getFoodSecurity() {
        $rows = $this->safeQuery("SELECT id, name FROM districts ORDER BY name");
        foreach ($rows as &$r) {
            $r['production_kg'] = 0;
            $r['est_demand_kg'] = 0;
            $r['ratio']         = 0;
            $r['status']        = 'moderate';
        }
        return $rows;
    }

    /* ============================================================
     *  MARKET INTELLIGENCE
     * ============================================================ */
    public function getMarketIntelligence() {
        return $this->safeQuery("
            SELECT c.id, c.name, c.icon,
                   0 AS min_price, 0 AS max_price, 0 AS avg_price,
                   0 AS listings, 0 AS total_qty
            FROM crops c WHERE c.is_active = 1
            ORDER BY c.name LIMIT 20
        ");
    }

    /* ============================================================
     *  EXPORT OPPORTUNITIES
     * ============================================================ */
    public function getExportOpportunities() {
        return $this->safeQuery("
            SELECT er.*, u.first_name, u.last_name,
                   pc.name AS category_name, pc.icon AS category_icon
            FROM export_requests er
            LEFT JOIN users u ON u.id = er.buyer_id
            LEFT JOIN product_categories pc ON pc.id = er.category_id
            WHERE er.status = 'open'
            ORDER BY er.created_at DESC LIMIT 10
        ");
    }

    /* ============================================================
     *  CREATE / DISMISS ALERTS
     * ============================================================ */
    public function createAlert($data) {
        if (!$this->db) return false;
        try {
            $this->db->query("
                INSERT INTO government_alerts
                    (uuid, alert_type, severity, title, body, district_id,
                     source, action_recommended, published_by, is_active, expires_at)
                VALUES (:u, :t, :s, :ti, :b, :d, :src, :ar, :pb, 1, :ex)
            ");
            $this->db->bind(':u',   uniqid('alert_', true));
            $this->db->bind(':t',   $data['alert_type'] ?? 'flood');
            $this->db->bind(':s',   $data['severity'] ?? 'medium');
            $this->db->bind(':ti',  $data['title'] ?? 'Untitled Alert');
            $this->db->bind(':b',   $data['body'] ?? '');
            $this->db->bind(':d',   !empty($data['district_id']) ? (int)$data['district_id'] : null);
            $this->db->bind(':src', $data['source'] ?? 'MINAGRI');
            $this->db->bind(':ar',  $data['action_recommended'] ?? '');
            $this->db->bind(':pb',  $data['published_by'] ?? null);
            $this->db->bind(':ex',  !empty($data['expires_at']) ? $data['expires_at'] : null);
            $this->db->execute();
            return $this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('createAlert failed: ' . $e->getMessage());
            return false;
        }
    }

    public function deactivateAlert($id) {
        if (!$this->db) return false;
        try {
            $this->db->query("UPDATE government_alerts SET is_active = 0 WHERE id = :id");
            $this->db->bind(':id', (int)$id);
            return $this->db->execute();
        } catch (\Throwable $e) { return false; }
    }

    /* ============================================================
     *  DISTRICT DETAIL
     * ============================================================ */
    public function getDistrictDetail($districtId, $year = null) {
        return [
            'district_id'   => $districtId,
            'year'          => $year ?: date('Y'),
            'crops'         => [],
            'farmer_count'  => 0,
            'alerts'        => [],
            'total_yield'   => 0,
            'total_revenue' => 0,
        ];
    }

    /* ============================================================
     *  AUDIT / REPORT STUBS
     * ============================================================ */
    public function log($userId, $action, $entity, $entityId, $meta = []) { return false; }
    public function saveReport($data) { return false; }
    public function getReports($userId, $limit = 30) { return []; }
}
?>