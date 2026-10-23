<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class GisController extends Controller {

    /* ═══════════════════════════════════════════════════════════════
       MAIN GIS PAGE
       ═══════════════════════════════════════════════════════════════ */
    public function index() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        $data = [
            'title' => 'GIS Analytics',
            'name'  => $_SESSION['name'] ?? 'User',
        ];

        // Try both possible view paths
        $viewFile1 = __DIR__ . '/../Views/weather/gis.php';
        $viewFile2 = __DIR__ . '/../Views/gis/index.php';

        if (file_exists($viewFile1)) {
            $this->view('weather/gis', $data);
            return;
        }

        if (file_exists($viewFile2)) {
            $this->view('gis/index', $data);
            return;
        }

        http_response_code(500);
        die('GIS view not found. Expected: app/Views/weather/gis.php or app/Views/gis/index.php');
    }

    /* ═══════════════════════════════════════════════════════════════
       API — DISTRICT DATA FOR THE MAP
       Returns districts with coordinates + production + rainfall
       ═══════════════════════════════════════════════════════════════ */
    public function apiData() {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('X-Content-Type-Options: nosniff');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $db = new Database();

            // ── 1. Base list of districts with coordinates ──
            $districts = [];

            try {
                $db->query("SELECT name, lat, lng 
                            FROM districts 
                            WHERE lat IS NOT NULL AND lng IS NOT NULL 
                            ORDER BY name");
                $districts = $db->resultSet();
            } catch (\Throwable $e) {
                // districts table might not have lat/lng
                try {
                    $db->query("SELECT name FROM districts ORDER BY name");
                    $districts = $db->resultSet();
                } catch (\Throwable $e2) {
                    $districts = [];
                }
            }

            // ── 2. Fallback to hardcoded district coordinates ──
            // (used when the districts table has no lat/lng)
            $defaultCoords = [
                'Bugesera'   => [-2.2194, 30.1244],
                'Burera'     => [-1.4667, 29.9167],
                'Gakenke'    => [-1.7000, 29.7833],
                'Gasabo'     => [-1.9167, 30.1167],
                'Gatsibo'    => [-1.6000, 30.4333],
                'Gicumbi'    => [-1.5833, 30.1000],
                'Gisagara'   => [-2.4500, 29.8333],
                'Huye'       => [-2.5967, 29.7392],
                'Kamonyi'    => [-2.0000, 29.9000],
                'Karongi'    => [-2.0500, 29.3500],
                'Kayonza'    => [-1.8833, 30.6167],
                'Kicukiro'   => [-1.9833, 30.1000],
                'Kirehe'     => [-2.2833, 30.7167],
                'Muhanga'    => [-2.0833, 29.7500],
                'Musanze'    => [-1.4999, 29.6349],
                'Ngoma'      => [-2.1500, 30.5667],
                'Ngororero'  => [-1.8667, 29.6000],
                'Nyabihu'    => [-1.6667, 29.5000],
                'Nyagatare'  => [-1.2939, 30.3275],
                'Nyamagabe'  => [-2.4667, 29.4500],
                'Nyamasheke' => [-2.3500, 29.1333],
                'Nyanza'     => [-2.3500, 29.7500],
                'Nyarugenge' => [-1.9500, 30.0500],
                'Nyaruguru'  => [-2.6167, 29.5667],
                'Rubavu'     => [-1.6779, 29.2579],
                'Ruhango'    => [-2.1500, 29.7833],
                'Rulindo'    => [-1.7667, 30.0667],
                'Rusizi'     => [-2.4833, 29.0000],
                'Rutsiro'    => [-1.9333, 29.3500],
                'Rwamagana'  => [-1.9500, 30.4333],
            ];

            // If districts table returned nothing, build from defaults
            if (empty($districts)) {
                foreach ($defaultCoords as $name => $coords) {
                    $districts[] = [
                        'name' => $name,
                        'lat'  => $coords[0],
                        'lng'  => $coords[1],
                    ];
                }
            } else {
                // Enrich any missing lat/lng from defaults
                foreach ($districts as &$d) {
                    if (empty($d['lat']) || empty($d['lng'])) {
                        if (isset($defaultCoords[$d['name']])) {
                            $d['lat'] = $defaultCoords[$d['name']][0];
                            $d['lng'] = $defaultCoords[$d['name']][1];
                        }
                    }
                }
                unset($d);
            }

            // ── 3. Enrich with production + rainfall + extras ──
            foreach ($districts as &$d) {
                $d['lat'] = (float)($d['lat'] ?? 0);
                $d['lng'] = (float)($d['lng'] ?? 0);

                // Production sum
                $d['production'] = 0;
                try {
                    $db->query("SELECT COALESCE(SUM(p.yield_kg), 0) AS total
                                FROM production p
                                JOIN users u ON u.id = p.user_id
                                JOIN farms f ON f.user_id = u.id
                                JOIN districts dd ON dd.id = f.district_id
                                WHERE dd.name = :n");
                    $db->bind(':n', $d['name']);
                    $d['production'] = (float)($db->single()['total'] ?? 0);
                } catch (\Throwable $e) {
                    // table might not exist
                }

                // Rainfall
                $d['rainfall'] = 0;
                try {
                    $db->query("SELECT COALESCE(AVG(rainfall_mm), 0) AS avg_r
                                FROM weather_data
                                WHERE district = :n
                                AND date >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)");
                    $db->bind(':n', $d['name']);
                    $d['rainfall'] = (float)($db->single()['avg_r'] ?? 0);
                } catch (\Throwable $e) {
                    // table might not exist
                }

                // Irrigation %
                $d['irrigation'] = 0;
                try {
                    $db->query("SELECT COUNT(*) AS total,
                                       SUM(CASE WHEN has_irrigation = 1 THEN 1 ELSE 0 END) AS irrigated
                                FROM farmer_profiles
                                WHERE district = :n");
                    $db->bind(':n', $d['name']);
                    $row = $db->single();
                    if ($row && (int)$row['total'] > 0) {
                        $d['irrigation'] = round(((int)$row['irrigated'] / (int)$row['total']) * 100);
                    }
                } catch (\Throwable $e) {
                    // table might not exist
                }

                // Pest alerts count
                $d['pests'] = 0;
                try {
                    $db->query("SELECT COUNT(*) AS c 
                                FROM farmer_alerts
                                WHERE alert_type LIKE '%pest%'
                                AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)");
                    $d['pests'] = (int)($db->single()['c'] ?? 0);
                } catch (\Throwable $e) {
                    // table might not exist
                }

                // Soil fertility (0-100) — placeholder calculation
                // In production, replace with actual soil health data
                $d['soil'] = rand(60, 90);
            }
            unset($d);

            // ── 4. Output ──
            echo json_encode([
                'success'   => true,
                'districts' => $districts,
                'count'     => count($districts),
                'timestamp' => date('c'),
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Throwable $e) {
            error_log("GisController::apiData error: " . $e->getMessage());
            echo json_encode([
                'success'   => false,
                'error'     => 'Server error: ' . $e->getMessage(),
                'districts' => [],
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /* ═══════════════════════════════════════════════════════════════
       GEOJSON — Rwanda district boundaries
       ═══════════════════════════════════════════════════════════════ */
    public function geojson() {
        $paths = [
            __DIR__ . '/../../public/data/rwanda_districts.geojson',
            __DIR__ . '/../../public/data/rwanda.json',
            __DIR__ . '/../../public/assets/rwanda_districts.geojson',
            __DIR__ . '/../../public/assets/data/rwanda_districts.geojson',
        ];

        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        if (ob_get_level() > 0) @ob_clean();

        foreach ($paths as $path) {
            if (file_exists($path)) {
                readfile($path);
                exit;
            }
        }

        http_response_code(404);
        echo json_encode([
            'success'  => false,
            'error'    => 'GeoJSON file not found',
            'searched' => $paths,
            'hint'     => 'Place rwanda_districts.geojson in public/data/ or public/assets/',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ═══════════════════════════════════════════════════════════════
       MAP STATS — aggregate numbers for the sidebar
       ═══════════════════════════════════════════════════════════════ */
    public function stats() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $db = new Database();
            $stats = [
                'total_districts'   => 30,
                'total_production'  => 0,
                'total_farms'       => 0,
                'avg_rainfall'      => 0,
                'active_alerts'     => 0,
            ];

            // Total production
            try {
                $db->query("SELECT COALESCE(SUM(yield_kg), 0) AS total FROM production");
                $stats['total_production'] = (float)($db->single()['total'] ?? 0);
            } catch (\Throwable $e) {}

            // Total farms
            try {
                $db->query("SELECT COUNT(*) AS c FROM farms WHERE is_active = 1");
                $stats['total_farms'] = (int)($db->single()['c'] ?? 0);
            } catch (\Throwable $e) {}

            // Average rainfall
            try {
                $db->query("SELECT COALESCE(AVG(rainfall_mm), 0) AS avg_r 
                            FROM weather_data
                            WHERE date >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)");
                $stats['avg_rainfall'] = round((float)($db->single()['avg_r'] ?? 0), 1);
            } catch (\Throwable $e) {}

            // Active alerts
            try {
                $db->query("SELECT COUNT(*) AS c FROM farmer_alerts 
                            WHERE is_read = 0 AND severity IN ('warning','critical')");
                $stats['active_alerts'] = (int)($db->single()['c'] ?? 0);
            } catch (\Throwable $e) {}

            echo json_encode(['success' => true, 'stats' => $stats], JSON_UNESCAPED_UNICODE);

        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'error'   => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}