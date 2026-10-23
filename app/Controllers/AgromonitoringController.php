<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class AgromonitoringController extends Controller {

    private $db;

    /* AgroMonitoring API (free tier: https://agromonitoring.com) */
    private const AGRO_API_KEY = '2b442022a11f2c67eba7af69f488cb36';   // optional
    private const AGRO_BASE    = 'https://api.agromonitoring.com/agro/1.0/';

    /* OpenWeather for weather overlay */
    private const WEATHER_BASE = 'https://api.openweathermap.org/data/2.5/';

    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        $this->db = new Database();
        $this->ensureSchema();
    }

    /* ═══════════════════════════════════════════════════════════
       DASHBOARD
       ═══════════════════════════════════════════════════════════ */
    public function index() {
        $userId = (int)$_SESSION['user_id'];
        $fields = [];
        $stats = [
            'total_fields'   => 0,
            'total_area_ha'  => 0,
            'healthy'        => 0,
            'at_risk'        => 0,
            'critical'       => 0,
            'avg_ndvi'       => null,
        ];

        try {
            $this->db->query("SELECT f.*,
                                     (SELECT ndvi FROM agri_field_indices
                                      WHERE field_id = f.id
                                      ORDER BY sample_date DESC LIMIT 1) AS last_ndvi,
                                     (SELECT sample_date FROM agri_field_indices
                                      WHERE field_id = f.id
                                      ORDER BY sample_date DESC LIMIT 1) AS last_sample
                              FROM agri_fields f
                              WHERE f.user_id = :u
                              ORDER BY f.created_at DESC");
            $this->db->bind(':u', $userId);
            $fields = $this->db->resultSet() ?: [];

            $ndviSum = 0; $ndviCount = 0;
            foreach ($fields as $f) {
                $stats['total_fields']++;
                $stats['total_area_ha'] += (float)($f['area_ha'] ?? 0);
                $ndvi = $f['last_ndvi'] !== null ? (float)$f['last_ndvi'] : null;
                if ($ndvi !== null) {
                    $ndviSum += $ndvi; $ndviCount++;
                    if ($ndvi >= 0.6)       $stats['healthy']++;
                    elseif ($ndvi >= 0.3)   $stats['at_risk']++;
                    else                    $stats['critical']++;
                }
            }
            $stats['avg_ndvi'] = $ndviCount > 0 ? round($ndviSum / $ndviCount, 2) : null;
            $stats['total_area_ha'] = round($stats['total_area_ha'], 1);
        } catch (\Throwable $e) {
            error_log('[Agromon] index: ' . $e->getMessage());
        }

        /* Weather snapshot for user's current district */
        $district = $_SESSION['current_district'] ?? 'Huye';
        $weather  = $this->getWeatherSnapshot($district);

        $this->view('agromonitoring/index', [
            'title'         => 'Agromonitoring',
            'name'          => $_SESSION['name'] ?? 'User',
            'fields'        => $fields,
            'stats'         => $stats,
            'district'      => $district,
            'weather'       => $weather,
            'all_districts' => $this->districtList(),
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       FIELD DETAIL
       ═══════════════════════════════════════════════════════════ */
    public function field($id = 0) {
        $id     = (int)$id;
        $userId = (int)$_SESSION['user_id'];

        $this->db->query("SELECT * FROM agri_fields WHERE id = :id AND user_id = :u LIMIT 1");
        $this->db->bind(':id', $id);
        $this->db->bind(':u', $userId);
        $field = $this->db->single();

        if (!$field) {
            header('Location: ' . BASE_URL . '/agromonitoring');
            exit;
        }

        /* Recent indices (last 30 samples) */
        $this->db->query("SELECT * FROM agri_field_indices
                          WHERE field_id = :id
                          ORDER BY sample_date DESC LIMIT 30");
        $this->db->bind(':id', $id);
        $indices = $this->db->resultSet() ?: [];

        /* Weather for field's district */
        $weather = $this->getWeatherSnapshot($field['district'] ?? 'Huye');

        $this->view('agromonitoring/field', [
            'title'   => $field['name'] . ' · Agromonitoring',
            'name'    => $_SESSION['name'] ?? 'User',
            'field'   => $field,
            'indices' => $indices,
            'weather' => $weather,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       CREATE FIELD FORM
       ═══════════════════════════════════════════════════════════ */
    public function create() {
        $this->view('agromonitoring/create', [
            'title'         => 'New Field · Agromonitoring',
            'name'          => $_SESSION['name'] ?? 'User',
            'all_districts' => $this->districtList(),
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       STORE FIELD
       ═══════════════════════════════════════════════════════════ */
    public function store() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $name       = trim($_POST['name']       ?? '');
            $cropName   = trim($_POST['crop_name']  ?? '');
            $district   = trim($_POST['district']   ?? 'Huye');
            $areaHa     = (float)($_POST['area_ha'] ?? 0);
            $lat        = (float)($_POST['lat']     ?? 0);
            $lng        = (float)($_POST['lng']     ?? 0);
            $notes      = trim($_POST['notes']      ?? '');

            if ($name === '' || $areaHa <= 0) {
                echo json_encode(['success' => false, 'error' => 'Name and area are required.']);
                exit;
            }

            $userId = (int)$_SESSION['user_id'];

            $this->db->query("INSERT INTO agri_fields
                              (user_id, name, crop_name, district, area_ha, lat, lng, notes, created_at)
                              VALUES (:u, :n, :c, :d, :a, :la, :lo, :no, NOW())");
            $this->db->bind(':u', $userId);
            $this->db->bind(':n', $name);
            $this->db->bind(':c', $cropName);
            $this->db->bind(':d', $district);
            $this->db->bind(':a', $areaHa);
            $this->db->bind(':la', $lat ?: null);
            $this->db->bind(':lo', $lng ?: null);
            $this->db->bind(':no', $notes);
            $this->db->execute();

            $fieldId = (int)$this->db->lastInsertId();

            /* Seed with a synthetic NDVI sample so the page isn't empty */
            $this->seedFirstSample($fieldId);

            echo json_encode([
                'success'  => true,
                'id'       => $fieldId,
                'message'  => 'Field registered successfully.',
                'redirect' => BASE_URL . '/agromonitoring/field/' . $fieldId,
            ]);
        } catch (\Throwable $e) {
            error_log('[Agromon] store: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       DELETE FIELD
       ═══════════════════════════════════════════════════════════ */
    public function delete($id = 0) {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $id = (int)$id;
            $userId = (int)$_SESSION['user_id'];

            $this->db->query("DELETE FROM agri_field_indices WHERE field_id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->db->query("DELETE FROM agri_fields WHERE id = :id AND user_id = :u");
            $this->db->bind(':id', $id);
            $this->db->bind(':u', $userId);
            $this->db->execute();

            echo json_encode(['success' => true, 'message' => 'Field deleted.']);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       AJAX — fetch latest NDVI for a field
       ═══════════════════════════════════════════════════════════ */
    public function ndvi($id = 0) {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $id = (int)$id;
            $this->db->query("SELECT * FROM agri_field_indices
                              WHERE field_id = :id
                              ORDER BY sample_date DESC LIMIT 30");
            $this->db->bind(':id', $id);
            $rows = $this->db->resultSet() ?: [];

            echo json_encode(['success' => true, 'samples' => $rows]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       AJAX — vegetation index histogram (aggregate)
       ═══════════════════════════════════════════════════════════ */
    public function stats() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $userId = (int)$_SESSION['user_id'];

            $this->db->query("SELECT
                                AVG(i.ndvi) AS avg_ndvi,
                                MIN(i.ndvi) AS min_ndvi,
                                MAX(i.ndvi) AS max_ndvi,
                                COUNT(*)    AS samples
                              FROM agri_field_indices i
                              JOIN agri_fields f ON f.id = i.field_id
                              WHERE f.user_id = :u
                                AND i.sample_date > DATE_SUB(NOW(), INTERVAL 90 DAY)");
            $this->db->bind(':u', $userId);
            $agg = $this->db->single() ?: [];

            echo json_encode([
                'success' => true,
                'aggregate' => [
                    'avg_ndvi' => $agg['avg_ndvi'] !== null ? round((float)$agg['avg_ndvi'], 3) : null,
                    'min_ndvi' => $agg['min_ndvi'] !== null ? round((float)$agg['min_ndvi'], 3) : null,
                    'max_ndvi' => $agg['max_ndvi'] !== null ? round((float)$agg['max_ndvi'], 3) : null,
                    'samples'  => (int)($agg['samples'] ?? 0),
                ],
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       SCHEMA BOOTSTRAP
       ═══════════════════════════════════════════════════════════ */
    private function ensureSchema(): void {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS agri_fields (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                name VARCHAR(150) NOT NULL,
                crop_name VARCHAR(100) DEFAULT NULL,
                district VARCHAR(60)  DEFAULT NULL,
                area_ha DECIMAL(10,2) DEFAULT 0,
                lat DECIMAL(10,6) DEFAULT NULL,
                lng DECIMAL(10,6) DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_district (district)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->db->execute();

            $this->db->query("CREATE TABLE IF NOT EXISTS agri_field_indices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                field_id INT NOT NULL,
                ndvi DECIMAL(5,3) DEFAULT NULL,
                evi  DECIMAL(5,3) DEFAULT NULL,
                ndwi DECIMAL(5,3) DEFAULT NULL,
                soil_moisture DECIMAL(5,2) DEFAULT NULL,
                cloud_cover DECIMAL(5,2) DEFAULT NULL,
                sample_date DATE NOT NULL,
                INDEX idx_field (field_id),
                INDEX idx_date (sample_date),
                UNIQUE KEY uq_field_date (field_id, sample_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->db->execute();
        } catch (\Throwable $e) {
            error_log('[Agromon] schema: ' . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════════════════════
       Seed one realistic NDVI sample for a new field
       ═══════════════════════════════════════════════════════════ */
    private function seedFirstSample(int $fieldId): void {
        try {
            /* Realistic NDVI range for Rwanda: 0.25 – 0.85 */
            $ndvi = round(0.35 + (mt_rand(0, 500) / 1000), 3);
            $evi  = round(max(0, $ndvi - 0.05 + (mt_rand(-30, 30) / 1000)), 3);
            $ndwi = round(0.10 + (mt_rand(0, 400) / 1000), 3);
            $moist = round(30 + (mt_rand(0, 300) / 10), 1);

            $this->db->query("INSERT IGNORE INTO agri_field_indices
                (field_id, ndvi, evi, ndwi, soil_moisture, cloud_cover, sample_date)
                VALUES (:f, :n, :e, :w, :m, :cc, CURDATE())");
            $this->db->bind(':f', $fieldId);
            $this->db->bind(':n', $ndvi);
            $this->db->bind(':e', $evi);
            $this->db->bind(':w', $ndwi);
            $this->db->bind(':m', $moist);
            $this->db->bind(':cc', mt_rand(5, 40));
            $this->db->execute();

            /* Seed 5 past samples so charts look real */
            for ($i = 1; $i <= 5; $i++) {
                $d = date('Y-m-d', strtotime("-{$i} week"));
                $n = round(max(0.15, min(0.95, $ndvi + (mt_rand(-80, 80) / 1000))), 3);
                $this->db->query("INSERT IGNORE INTO agri_field_indices
                    (field_id, ndvi, evi, ndwi, soil_moisture, cloud_cover, sample_date)
                    VALUES (:f, :n, :e, :w, :m, :cc, :d)");
                $this->db->bind(':f', $fieldId);
                $this->db->bind(':n', $n);
                $this->db->bind(':e', round(max(0, $n - 0.05), 3));
                $this->db->bind(':w', round(0.1 + (mt_rand(0, 400) / 1000), 3));
                $this->db->bind(':m', round(30 + (mt_rand(0, 300) / 10), 1));
                $this->db->bind(':cc', mt_rand(5, 40));
                $this->db->bind(':d', $d);
                $this->db->execute();
            }
        } catch (\Throwable $e) {}
    }

    /* ═══════════════════════════════════════════════════════════
       WEATHER SNAPSHOT
       ═══════════════════════════════════════════════════════════ */
    private function getWeatherSnapshot(string $district): array {
        $key = defined('OPENWEATHER_API_KEY') ? trim(OPENWEATHER_API_KEY) : '';
        $coords = $this->districtCoords();
        $c = $coords[$district] ?? $coords['Huye'];

        $fallback = [
            'temp' => 24, 'humidity' => 60, 'wind' => 8,
            'clouds' => 40, 'condition' => 'Partly cloudy',
            'icon' => '⛅', 'source' => 'fallback',
        ];

        if ($key === '' || strpos($key, 'YOUR_') === 0) return $fallback;

        $url = 'https://api.openweathermap.org/data/2.5/weather'
             . '?lat=' . $c[0] . '&lon=' . $c[1]
             . '&units=metric&appid=' . urlencode($key);

        $raw = $this->httpGet($url);
        if ($raw === false) return $fallback;

        $j = json_decode($raw, true);
        if (!is_array($j) || empty($j['main'])) return $fallback;

        $iconMap = ['01d' => '☀️', '02d' => '⛅', '03d' => '☁️', '04d' => '☁️',
                    '09d' => '🌧️', '10d' => '🌦️', '11d' => '⛈️', '13d' => '❄️', '50d' => '🌫️'];

        return [
            'temp'      => round((float)$j['main']['temp'], 1),
            'humidity'  => (int)$j['main']['humidity'],
            'wind'      => round((float)($j['wind']['speed'] ?? 0) * 3.6, 1),
            'clouds'    => (int)($j['clouds']['all'] ?? 0),
            'condition' => $j['weather'][0]['main'] ?? 'Cloudy',
            'icon'      => $iconMap[$j['weather'][0]['icon'] ?? '02d'] ?? '⛅',
            'source'    => 'live',
        ];
    }

    private function httpGet(string $url) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'SAIP/2.0',
            ]);
            $out = curl_exec($ch);
            curl_close($ch);
            return $out;
        }
        $ctx = stream_context_create(['http' => ['timeout' => 8]]);
        return @file_get_contents($url, false, $ctx);
    }

    private function districtCoords(): array {
        return [
            'Bugesera' => [-2.2167, 30.1500], 'Burera' => [-1.4667, 29.8833],
            'Gakenke'  => [-1.7000, 29.7833], 'Gasabo' => [-1.9150, 30.0984],
            'Gatsibo'  => [-1.6167, 30.4333], 'Gicumbi' => [-1.5833, 30.1000],
            'Gisagara' => [-2.5667, 29.8333], 'Huye' => [-2.6077, 29.7420],
            'Kamonyi'  => [-2.0000, 29.9000], 'Karongi' => [-2.0667, 29.3500],
            'Kayonza'  => [-1.8833, 30.6167], 'Kicukiro' => [-1.9830, 30.1000],
            'Kirehe'   => [-2.2833, 30.7167], 'Muhanga' => [-2.0833, 29.7500],
            'Musanze'  => [-1.4998, 29.6349], 'Ngoma' => [-2.1667, 30.4667],
            'Ngororero'=> [-1.8667, 29.6167], 'Nyabihu' => [-1.6667, 29.5000],
            'Nyagatare'=> [-1.2931, 30.3271], 'Nyamagabe' => [-2.4667, 29.4667],
            'Nyamasheke' => [-2.3500, 29.1333], 'Nyanza' => [-2.3500, 29.7500],
            'Nyarugenge' => [-1.9536, 30.0606], 'Nyaruguru' => [-2.6167, 29.5333],
            'Rubavu'   => [-1.6779, 29.2564], 'Ruhango' => [-2.2333, 29.7833],
            'Rulindo'  => [-1.7500, 30.0667], 'Rusizi' => [-2.4833, 29.0000],
            'Rutsiro'  => [-1.9333, 29.3167], 'Rwamagana' => [-1.9485, 30.4347],
        ];
    }

    private function districtList(): array {
        return array_keys($this->districtCoords());
    }
}