<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller {
    private $db = null;

    public function __construct() {
        // ⚠️ NO session check here — that caused the redirect loop.
        // Session check happens inside index() with a debug fallback.
        try {
            $this->db = new Database();
        } catch (\Throwable $e) {
            $this->db = null;
        }
    }

    /* ============================================================
     *  MAIN ENTRY
     * ============================================================ */
    public function index() {
        // ===== SESSION CHECK (NO REDIRECT — shows debug info instead) =====
        if (empty($_SESSION['user_id'])) {
            $this->showSessionDebug();
            return;
        }

        $roleId = (int)($_SESSION['role_id'] ?? 1);
        $userId = (int)($_SESSION['user_id'] ?? 0);

        $data = [
            'title'   => 'Dashboard',
            'name'    => $_SESSION['name'] ?? 'User',
            'role_id' => $roleId,
            'user_id' => $userId,
            'notifications_count' => 0,
        ];

        // Route by role
        switch ($roleId) {
            case 2:
                $data = array_merge($data, $this->loadCooperativeData($userId));
                $this->safeView('dashboard/cooperative', $data);
                return;
            case 3:
                $data = array_merge($data, $this->loadOfficerData($userId));
                $this->safeView('dashboard/officer', $data);
                return;
            case 4:
                $data = array_merge($data, $this->loadGovernmentData($userId));
                $this->safeView('dashboard/government', $data);
                return;
            case 5:
                $data = array_merge($data, $this->loadExporterData($userId));
                $this->safeView('dashboard/exporter', $data);
                return;
            case 6:
                $data = array_merge($data, $this->loadResearcherData($userId));
                $this->safeView('dashboard/researcher', $data);
                return;
            case 7:
                $data = array_merge($data, $this->loadAdminData($userId));
                $this->safeView('dashboard/admin', $data);
                return;
            case 1:
            default:
                $data = array_merge($data, $this->loadFarmerData($userId));
                $this->safeView('dashboard/farmer', $data);
                return;
        }
    }

    /* ============================================================
     *  Show debug info when session is missing — NO redirect
     * ============================================================ */
    private function showSessionDebug() {
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html><head><title>Session Lost — Debug</title>
        <style>
            body{background:#081C15;color:#fff;font-family:Arial;padding:40px;line-height:1.8;}
            h2{color:#EF4444;}
            h3{color:#84CC16;margin-top:24px;}
            pre{background:#0F3D2E;padding:15px;border-radius:8px;overflow-x:auto;font-size:13px;}
            .btn{display:inline-block;padding:12px 24px;background:#16A34A;color:white;
                 text-decoration:none;border-radius:10px;margin:8px 4px;font-weight:bold;}
            .btn-alt{background:#0EA5E9;}
            .note{background:rgba(245,158,11,0.12);border-left:4px solid #F59E0B;
                  padding:14px;border-radius:8px;margin-top:20px;color:#FCD34D;}
        </style></head><body>

        <h2>❌ Session is empty on this request</h2>
        <p>PHP loaded the dashboard but <code>$_SESSION['user_id']</code> is not set.</p>

        <h3>$_SESSION contents</h3>
        <pre><?= htmlspecialchars(print_r($_SESSION, true)) ?></pre>

        <h3>$_COOKIE contents</h3>
        <pre><?= htmlspecialchars(print_r($_COOKIE, true)) ?></pre>

        <h3>Session metadata</h3>
        <pre><?php
            echo 'session_status: '   . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'NOT ACTIVE') . "\n";
            echo 'session_id: '       . (session_status() === PHP_SESSION_ACTIVE ? session_id() : 'N/A') . "\n";
            echo 'session_name: '     . session_name() . "\n";
            echo 'save_path: '        . session_save_path() . "\n";
            echo 'cookie_path: '      . ini_get('session.cookie_path') . "\n";
            echo 'cookie_domain: '    . ini_get('session.cookie_domain') . "\n";
            echo 'cookie_lifetime: '  . ini_get('session.cookie_lifetime') . "\n";
            echo 'use_strict_mode: '  . ini_get('session.use_strict_mode') . "\n";
            echo 'use_only_cookies: ' . ini_get('session.use_only_cookies') . "\n";
        ?></pre>

        <div class="note">
            <strong>⚠️ If $_COOKIE does NOT contain <code>PHPSESSID</code>:</strong><br>
            The browser is not sending the session cookie back. This is a cookie path or domain issue.
        </div>

        <h3>Actions</h3>
        <a class="btn" href="<?= BASE_URL ?>/direct-login.php">🔑 Direct Login (bypass)</a>
        <a class="btn btn-alt" href="<?= BASE_URL ?>/index.php?url=auth/login">→ Go to Login Form</a>
        <a class="btn btn-alt" href="<?= BASE_URL ?>/whoami.php">🔍 Check Session</a>

        </body></html>
        <?php
        exit;
    }

    /* ============================================================
     *  safeView — falls back to inline page if view file is missing
     * ============================================================ */
    private function safeView($view, $data) {
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        if (file_exists($viewFile)) {
            $this->view($view, $data);
            return;
        }

        // Fallback: show a functional page
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html><head><title>Dashboard</title>
        <style>
            body{background:#081C15;color:#fff;font-family:Arial;padding:40px;line-height:1.8;}
            h2{color:#84CC16;}
            pre{background:#0F3D2E;padding:15px;border-radius:8px;}
            a.btn{display:inline-block;padding:12px 24px;background:#16A34A;color:white;
                  text-decoration:none;border-radius:10px;margin:8px 4px;font-weight:bold;}
            .ok{color:#22C55E;font-weight:bold;}
        </style></head><body>

        <h2>✅ Session is ACTIVE — you are logged in</h2>
        <p>But the view <code><?= htmlspecialchars($view) ?>.php</code> is missing.</p>

        <pre><?= htmlspecialchars(print_r($data, true)) ?></pre>

        <h3>Try these pages:</h3>
        <a class="btn" href="<?= BASE_URL ?>/index.php?url=weather">Weather</a>
        <a class="btn" href="<?= BASE_URL ?>/index.php?url=crops">Crops</a>
        <a class="btn" href="<?= BASE_URL ?>/index.php?url=marketplace">Marketplace</a>
        <a class="btn" href="<?= BASE_URL ?>/index.php?url=chat">Chat</a>
        <a class="btn" href="<?= BASE_URL ?>/index.php?url=forum">Forum</a>
        <a class="btn" href="<?= BASE_URL ?>/index.php?url=gis">GIS</a>
        <a class="btn" href="<?= BASE_URL ?>/index.php?url=auth/logout">Logout</a>

        </body></html>
        <?php
        exit;
    }

    /* ============================================================
     *  FARMER (role 1)
     * ============================================================ */
    private function loadFarmerData($userId) {
        return [
            'kpis'          => $this->getFarmerKPIs($userId),
            'active_crops'  => $this->safeQuery("
                SELECT fc.*, c.name AS crop_name, c.icon AS crop_icon
                FROM farm_crops fc
                JOIN crops c ON c.id = fc.crop_id
                JOIN farms f ON f.id = fc.farm_id
                WHERE f.user_id = :u AND fc.is_active = 1
                ORDER BY fc.planted_date DESC LIMIT 6
            ", [':u' => $userId]),
            'recent_orders' => [],
            'notifications' => $this->getRecentNotifications($userId, 5),
            'weather'       => null,
            'market_prices' => $this->safeQuery("
                SELECT c.name, c.icon, AVG(p.price_per_unit) AS avg_price
                FROM crops c
                LEFT JOIN products p ON p.crop_id = c.id AND p.status = 'active'
                GROUP BY c.id HAVING avg_price IS NOT NULL
                ORDER BY avg_price DESC LIMIT 5
            "),
        ];
    }

    private function getFarmerKPIs($userId) {
        $kpis = [
            'total_production_kg' => 0,
            'active_farms'        => 0,
            'market_value_rwf'    => 0,
            'active_crops'        => 0,
        ];
        if (!$this->db) return $kpis;

        try {
            $r = $this->safeQuery("SELECT COALESCE(SUM(yield_kg),0) AS t FROM production WHERE user_id = :u", [':u' => $userId]);
            $kpis['total_production_kg'] = (float)($r[0]['t'] ?? 0);

            $r = $this->safeQuery("SELECT COALESCE(SUM(revenue_rwf),0) AS t FROM production WHERE user_id = :u", [':u' => $userId]);
            $kpis['market_value_rwf'] = (float)($r[0]['t'] ?? 0);

            $r = $this->safeQuery("SELECT COUNT(*) AS c FROM farms WHERE user_id = :u AND is_active = 1", [':u' => $userId]);
            $kpis['active_farms'] = (int)($r[0]['c'] ?? 0);

            $r = $this->safeQuery("
                SELECT COUNT(*) AS c FROM farm_crops fc
                JOIN farms f ON f.id = fc.farm_id
                WHERE f.user_id = :u AND fc.is_active = 1
            ", [':u' => $userId]);
            $kpis['active_crops'] = (int)($r[0]['c'] ?? 0);
        } catch (\Throwable $e) { /* silent */ }

        return $kpis;
    }

    /* ============================================================
     *  COOPERATIVE (role 2)
     * ============================================================ */
    private function loadCooperativeData($userId) {
        return [
            'coop'  => null,
            'stats' => [
                'members' => 0, 'total_harvest' => 0, 'total_contributions' => 0,
                'equipment_count' => 0, 'meetings_count' => 0,
            ],
            'recent_harvests'   => [],
            'upcoming_meetings' => [],
        ];
    }

    /* ============================================================
     *  OFFICER (role 3)
     * ============================================================ */
    private function loadOfficerData($userId) {
        return [
            'district_id'    => 0,
            'district_name'  => 'District',
            'farmer_count'   => 0,
            'top_crops'      => [],
            'pending_tasks'  => [],
            'active_alerts'  => [],
            'district_stats' => [],
        ];
    }

    /* ============================================================
     *  GOVERNMENT (role 4)
     * ============================================================ */
    private function loadGovernmentData($userId) {
        return [
            'kpis'     => $this->getNationalKPIs(),
            'heatmap'  => [],
            'nst2'     => $this->safeQuery("SELECT * FROM nst2_indicators ORDER BY pillar, code"),
            'alerts'   => [],
            'insights' => [],
            'food'     => [],
            'market'   => [],
            'exports'  => [],
        ];
    }

    private function getNationalKPIs() {
        $kpis = [
            'registered_farmers'  => 0,
            'total_production_kg' => 0,
            'market_value_rwf'    => 0,
            'active_farms'        => 0,
            'active_alerts'       => 0,
            'districts_covered'   => 0,
        ];
        if (!$this->db) return $kpis;

        try {
            $r = $this->safeQuery("SELECT COUNT(*) AS c FROM users WHERE role_id = 1 AND status = 'active'");
            $kpis['registered_farmers'] = (int)($r[0]['c'] ?? 0);

            $r = $this->safeQuery("SELECT COALESCE(SUM(yield_kg),0) AS t FROM production");
            $kpis['total_production_kg'] = (float)($r[0]['t'] ?? 0);

            $r = $this->safeQuery("SELECT COALESCE(SUM(revenue_rwf),0) AS t FROM production");
            $kpis['market_value_rwf'] = (float)($r[0]['t'] ?? 0);

            $r = $this->safeQuery("SELECT COUNT(*) AS c FROM farms WHERE is_active = 1");
            $kpis['active_farms'] = (int)($r[0]['c'] ?? 0);

            $r = $this->safeQuery("SELECT COUNT(*) AS c FROM government_alerts WHERE is_active = 1");
            $kpis['active_alerts'] = (int)($r[0]['c'] ?? 0);

            $r = $this->safeQuery("SELECT COUNT(DISTINCT district_id) AS c FROM farms WHERE district_id IS NOT NULL");
            $kpis['districts_covered'] = (int)($r[0]['c'] ?? 0);
        } catch (\Throwable $e) { /* silent */ }

        return $kpis;
    }

    /* ============================================================
     *  EXPORTER (role 5)
     * ============================================================ */
    private function loadExporterData($userId) {
        return [
            'active_orders'      => 0,
            'in_transit'         => 0,
            'delivered_mtd'      => 0,
            'verified_suppliers' => 0,
            'recent_orders'      => [],
        ];
    }

    /* ============================================================
     *  RESEARCHER (role 6)
     * ============================================================ */
    private function loadResearcherData($userId) {
        $stats = ['total' => 0, 'total_rows' => 0, 'public' => 0, 'published' => 0];
        $recentDatasets = [];

        if ($this->db) {
            try {
                $r = $this->safeQuery("
                    SELECT COUNT(*) AS total,
                           COALESCE(SUM(row_count),0) AS total_rows,
                           SUM(is_public = 1) AS public,
                           SUM(status = 'published') AS published
                    FROM nisr_datasets
                ");
                if (!empty($r[0])) $stats = $r[0];

                $recentDatasets = $this->safeQuery("
                    SELECT id, name, survey_type, year, row_count, status, is_public
                    FROM nisr_datasets
                    WHERE is_public = 1 OR uploaded_by = :u
                    ORDER BY year DESC, created_at DESC
                    LIMIT 8
                ", [':u' => $userId]);
            } catch (\Throwable $e) { /* silent */ }
        }

        return [
            'stats'           => $stats,
            'recent_datasets' => $recentDatasets,
            'my_analyses'     => 0,
            'api_tokens'      => 0,
        ];
    }

    /* ============================================================
     *  ADMIN (role 7)
     * ============================================================ */
    private function loadAdminData($userId) {
        $stats = [
            'users' => 0, 'farms' => 0, 'products' => 0, 'orders' => 0,
            'messages' => 0, 'logins_today' => 0, 'active_alerts' => 0, 'datasets' => 0,
        ];

        if ($this->db) {
            try {
                foreach ([
                    'users'    => "SELECT COUNT(*) AS c FROM users",
                    'farms'    => "SELECT COUNT(*) AS c FROM farms WHERE is_active = 1",
                    'products' => "SELECT COUNT(*) AS c FROM products WHERE status = 'active'",
                    'orders'   => "SELECT COUNT(*) AS c FROM orders",
                    'messages' => "SELECT COUNT(*) AS c FROM messages",
                    'logins_today' => "SELECT COUNT(*) AS c FROM login_history WHERE login_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                    'datasets' => "SELECT COUNT(*) AS c FROM nisr_datasets",
                ] as $key => $sql) {
                    $r = $this->safeQuery($sql);
                    $stats[$key] = (int)($r[0]['c'] ?? 0);
                }
            } catch (\Throwable $e) { /* silent */ }
        }

        return [
            'stats'         => $stats,
            'recent_logins' => [],
            'recent_audit'  => [],
        ];
    }

    /* ============================================================
     *  SHARED HELPERS
     * ============================================================ */
    private function getRecentNotifications($userId, $limit = 5) {
        return $this->safeQuery("
            SELECT * FROM notifications
            WHERE user_id = :u
            ORDER BY created_at DESC
            LIMIT :n
        ", [':u' => (int)$userId, ':n' => (int)$limit]);
    }

    private function safeQuery($sql, $binds = []) {
        if (!$this->db) return [];
        try {
            $this->db->query($sql);
            foreach ($binds as $key => $value) {
                $this->db->bind($key, $value);
            }
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function widgets() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
}
?>