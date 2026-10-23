<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class WeatherController extends Controller {

    private $db;

    private const FALLBACK_API_KEY = 'e95b711ee3ee79213000c575cd80e9e6';

    private const COORDS = [
        'Bugesera'   => [-2.2167, 30.1500], 'Burera'    => [-1.4667, 29.8833],
        'Gakenke'    => [-1.7000, 29.7833], 'Gasabo'    => [-1.9150, 30.0984],
        'Gatsibo'    => [-1.6167, 30.4333], 'Gicumbi'   => [-1.5833, 30.1000],
        'Gisagara'   => [-2.5667, 29.8333], 'Huye'      => [-2.6077, 29.7420],
        'Kamonyi'    => [-2.0000, 29.9000], 'Karongi'   => [-2.0667, 29.3500],
        'Kayonza'    => [-1.8833, 30.6167], 'Kicukiro'  => [-1.9830, 30.1000],
        'Kirehe'     => [-2.2833, 30.7167], 'Muhanga'   => [-2.0833, 29.7500],
        'Musanze'    => [-1.4998, 29.6349], 'Ngoma'     => [-2.1667, 30.4667],
        'Ngororero'  => [-1.8667, 29.6167], 'Nyabihu'   => [-1.6667, 29.5000],
        'Nyagatare'  => [-1.2931, 30.3271], 'Nyamagabe' => [-2.4667, 29.4667],
        'Nyamasheke' => [-2.3500, 29.1333], 'Nyanza'    => [-2.3500, 29.7500],
        'Nyarugenge' => [-1.9536, 30.0606], 'Nyaruguru' => [-2.6167, 29.5333],
        'Rubavu'     => [-1.6779, 29.2564], 'Ruhango'   => [-2.2333, 29.7833],
        'Rulindo'    => [-1.7500, 30.0667], 'Rusizi'    => [-2.4833, 29.0000],
        'Rutsiro'    => [-1.9333, 29.3167], 'Rwamagana' => [-1.9485, 30.4347],
    ];

    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        $this->db = new Database();
    }

    /* ═══════════════ MAIN PAGE ═══════════════ */
    public function index() {
        $district = !empty($_GET['district'])
                  ? trim($_GET['district'])
                  : ($_SESSION['current_district'] ?? 'Huye');
        if (!isset(self::COORDS[$district])) $district = 'Huye';

        $weather = $this->fetchLiveWeather($district);
        $advice  = $this->buildAdvice($weather);

        $this->view('weather/index', [
            'title'         => 'Weather · ' . $district,
            'name'          => $_SESSION['name'] ?? 'User',
            'district'      => $district,
            'weather'       => $weather,
            'advice'        => $advice,
            'all_districts' => array_keys(self::COORDS),
        ]);
    }

    /* ═══════════════ /weather/current ═══════════════ */
    public function current() {
        $district = trim($_GET['district'] ?? $_POST['district'] ?? '');
        if ($district === '' || !isset(self::COORDS[$district])) {
            $district = $_SESSION['current_district'] ?? 'Huye';
        }

        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $weather = $this->fetchLiveWeather($district);
            echo json_encode([
                'success'  => true,
                'district' => $district,
                'weather'  => $weather,
                'advice'   => $this->buildAdvice($weather),
                'fetched'  => date('H:i:s'),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ /weather/byDistrict ═══════════════ */
    public function byDistrict() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $district = trim($_GET['district'] ?? 'Huye');
            if (!isset(self::COORDS[$district])) $district = 'Huye';

            $weather = $this->fetchLiveWeather($district);
            echo json_encode([
                'success'  => true,
                'district' => $district,
                'weather'  => $weather,
                'advice'   => $this->buildAdvice($weather),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ /weather/byCoords ═══════════════ */
    public function byCoords() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $lat = (float)($_GET['lat'] ?? 0);
            $lng = (float)($_GET['lng'] ?? 0);
            if (!$lat || !$lng) {
                echo json_encode(['success' => false, 'error' => 'Missing lat/lng']);
                exit;
            }

            $district = $this->nearestDistrict($lat, $lng);
            $weather  = $this->fetchLiveWeather($district, $lat, $lng);

            echo json_encode([
                'success'  => true,
                'district' => $district,
                'lat'      => $lat,
                'lng'      => $lng,
                'weather'  => $weather,
                'advice'   => $this->buildAdvice($weather),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ /weather/autoDetect ═══════════════
       NEW — IP-based geolocation fallback for HTTP sites. */
    public function autoDetect() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
            if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);

            /* Try ip-api.com — free, no key, 45 req/min */
            $url = 'http://ip-api.com/json/' . urlencode($ip)
                 . '?fields=status,country,countryCode,regionName,city,lat,lon,timezone';

            $raw = $this->httpGet($url);
            $j = $raw !== false ? json_decode($raw, true) : null;

            if (is_array($j) && ($j['status'] ?? '') === 'success') {
                $lat = (float)$j['lat'];
                $lng = (float)$j['lon'];

                /* If outside Rwanda, default to Huye */
                if ($lat < -3.5 || $lat > -0.5 || $lng < 28.5 || $lng > 31.5) {
                    echo json_encode([
                        'success'  => true,
                        'district' => 'Huye',
                        'lat'      => -2.6077,
                        'lng'      => 29.7420,
                        'city'     => $j['city'] ?? 'Unknown',
                        'country'  => $j['country'] ?? '—',
                        'outside'  => true,
                        'message'  => 'Outside Rwanda — defaulted to Huye',
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                $district = $this->nearestDistrict($lat, $lng);
                echo json_encode([
                    'success'  => true,
                    'district' => $district,
                    'lat'      => $lat,
                    'lng'      => $lng,
                    'city'     => $j['city'] ?? '—',
                    'country'  => $j['country'] ?? '—',
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success'  => false,
                    'error'    => 'IP lookup failed',
                    'district' => 'Huye',
                    'lat'      => -2.6077,
                    'lng'      => 29.7420,
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Throwable $e) {
            echo json_encode([
                'success'  => false,
                'error'    => $e->getMessage(),
                'district' => 'Huye',
                'lat'      => -2.6077,
                'lng'      => 29.7420,
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /* ═══════════════ /weather/saveLocation ═══════════════ */
    public function saveLocation() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $lat = (float)($_POST['lat'] ?? 0);
            $lng = (float)($_POST['lng'] ?? 0);
            $district = trim($_POST['district'] ?? '');

            if (!$lat || !$lng) {
                echo json_encode(['success' => false, 'error' => 'Missing coordinates']);
                exit;
            }
            if ($district === '') $district = $this->nearestDistrict($lat, $lng);

            $_SESSION['current_district'] = $district;
            $_SESSION['current_lat']      = $lat;
            $_SESSION['current_lng']      = $lng;

            if (!empty($_SESSION['user_id'])) {
                try {
                    $this->db->query("UPDATE users
                                      SET current_district = :d, current_lat = :la, current_lng = :lo, location_updated_at = NOW()
                                      WHERE id = :id");
                    $this->db->bind(':d',  $district);
                    $this->db->bind(':la', $lat);
                    $this->db->bind(':lo', $lng);
                    $this->db->bind(':id', (int)$_SESSION['user_id']);
                    $this->db->execute();
                } catch (\Throwable $e) {}
            }

            $weather = $this->fetchLiveWeather($district, $lat, $lng);
            echo json_encode([
                'success'  => true,
                'district' => $district,
                'lat'      => $lat,
                'lng'      => $lng,
                'weather'  => $weather,
                'advice'   => $this->buildAdvice($weather),
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ /weather/setDistrict ═══════════════ */
    public function setDistrict() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $district = trim($_POST['district'] ?? '');
            if (!isset(self::COORDS[$district])) {
                echo json_encode(['success' => false, 'error' => 'Unknown district']);
                exit;
            }

            $_SESSION['current_district'] = $district;
            unset($_SESSION['current_lat'], $_SESSION['current_lng']);

            if (!empty($_SESSION['user_id'])) {
                try {
                    $this->db->query("UPDATE users SET current_district = :d, location_updated_at = NOW() WHERE id = :id");
                    $this->db->bind(':d',  $district);
                    $this->db->bind(':id', (int)$_SESSION['user_id']);
                    $this->db->execute();
                } catch (\Throwable $e) {}
            }

            echo json_encode([
                'success'  => true,
                'district' => $district,
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ LIVE FETCH ═══════════════ */
    private function fetchLiveWeather(string $district, ?float $lat = null, ?float $lng = null): array {
        if ($lat === null || $lng === null) {
            $c = self::COORDS[$district] ?? self::COORDS['Huye'];
            $lat = $c[0]; $lng = $c[1];
        }

        $key = '';
        if (defined('OPENWEATHER_API_KEY')) $key = trim(OPENWEATHER_API_KEY);
        if ($key === '' || strpos($key, 'YOUR_') === 0) $key = self::FALLBACK_API_KEY;

        $url = 'https://api.openweathermap.org/data/2.5/weather'
             . '?lat=' . urlencode($lat) . '&lon=' . urlencode($lng)
             . '&units=metric&appid=' . urlencode($key);

        $raw = $this->httpGet($url);
        if ($raw === false) return $this->fallbackPayload($district, 'Network error');

        $j = json_decode($raw, true);
        if (!is_array($j) || empty($j['main'])) {
            return $this->fallbackPayload($district, 'Invalid API response');
        }

        /* 5-day forecast */
        $furl = 'https://api.openweathermap.org/data/2.5/forecast'
              . '?lat=' . urlencode($lat) . '&lon=' . urlencode($lng)
              . '&units=metric&appid=' . urlencode($key);

        $fraw = $this->httpGet($furl);
        $fj = $fraw !== false ? json_decode($fraw, true) : null;
        $forecast = $this->buildForecast($fj);

        $main    = $j['main'];
        $wind    = $j['wind'] ?? [];
        $clouds  = $j['clouds'] ?? [];
        $rain    = $j['rain'] ?? [];
        $sys     = $j['sys'] ?? [];
        $weather = $j['weather'][0] ?? [];

        $windKmh = round(((float)($wind['speed'] ?? 0)) * 3.6, 1);
        $windDeg = (int)($wind['deg'] ?? 0);
        $cloudy  = (int)($clouds['all'] ?? 0);

        $hour = (int)date('G');
        $uvBase = max(0, sin(($hour - 6) / 12 * M_PI)) * 11;
        $uv = (int)round($uvBase * (1 - ($cloudy / 100) * 0.6));

        $uvLevel = 'Low'; $uvClass = 'ok';
        if ($uv >= 11)     { $uvLevel = 'Extreme';   $uvClass = 'danger'; }
        elseif ($uv >= 8)  { $uvLevel = 'Very High'; $uvClass = 'danger'; }
        elseif ($uv >= 6)  { $uvLevel = 'High';      $uvClass = 'warn'; }
        elseif ($uv >= 3)  { $uvLevel = 'Moderate';  $uvClass = 'warn'; }

        return [
            'ok'         => true,
            'source'     => 'live',
            'district'   => $district,
            'coords'     => ['lat' => $lat, 'lng' => $lng],
            'fetched'    => date('c'),
            'updated_at' => date('g:i A'),

            'temp'       => round((float)$main['temp'], 1),
            'feels_like' => round((float)($main['feels_like'] ?? $main['temp']), 1),
            'temp_min'   => round((float)($main['temp_min'] ?? $main['temp']), 1),
            'temp_max'   => round((float)($main['temp_max'] ?? $main['temp']), 1),
            'humidity'   => (int)($main['humidity'] ?? 0),
            'pressure'   => (int)($main['pressure'] ?? 1013),
            'wind_speed' => $windKmh,
            'wind_deg'   => $windDeg,
            'wind_dir'   => $this->windDir($windDeg),
            'clouds'     => $cloudy,
            'rain_1h'    => (float)($rain['1h'] ?? 0),
            'condition'  => $weather['main'] ?? 'Cloudy',
            'description'=> ucfirst($weather['description'] ?? 'cloudy'),
            'icon'       => $weather['icon'] ?? '02d',
            'icon_emoji' => $this->emoji($weather['icon'] ?? '02d'),
            'sunrise'    => isset($sys['sunrise']) ? date('H:i', (int)$sys['sunrise']) : '—',
            'sunset'     => isset($sys['sunset'])  ? date('H:i', (int)$sys['sunset'])  : '—',
            'uv'         => ['index' => $uv, 'level' => $uvLevel, 'class' => $uvClass],
            'forecast'   => $forecast,
        ];
    }

    /* ═══════════════ 5-day forecast ═══════════════ */
    private function buildForecast($fj): array {
        if (!is_array($fj) || empty($fj['list'])) return [];
        $byDay = [];
        foreach ($fj['list'] as $slot) {
            $date = substr($slot['dt_txt'], 0, 10);
            if (!isset($byDay[$date])) {
                $byDay[$date] = [
                    'date'     => $date,
                    'day'      => date('D', strtotime($date)),
                    'temp_min' => (float)$slot['main']['temp_min'],
                    'temp_max' => (float)$slot['main']['temp_max'],
                    'rain'     => 0,
                    'pop'      => isset($slot['pop']) ? (int)round($slot['pop'] * 100) : 0,
                    'icon'     => $slot['weather'][0]['icon'] ?? '01d',
                    'desc'     => $slot['weather'][0]['description'] ?? '',
                ];
            } else {
                $byDay[$date]['temp_min'] = min($byDay[$date]['temp_min'], (float)$slot['main']['temp_min']);
                $byDay[$date]['temp_max'] = max($byDay[$date]['temp_max'], (float)$slot['main']['temp_max']);
                $byDay[$date]['pop'] = max($byDay[$date]['pop'], isset($slot['pop']) ? (int)round($slot['pop'] * 100) : 0);
            }
            if (isset($slot['rain']['3h'])) $byDay[$date]['rain'] += (float)$slot['rain']['3h'];
        }
        $out = [];
        foreach (array_slice(array_values($byDay), 0, 5) as $d) {
            $d['temp_min'] = round($d['temp_min'], 1);
            $d['temp_max'] = round($d['temp_max'], 1);
            $d['rain']     = round($d['rain'], 1);
            $d['emoji']    = $this->emoji($d['icon']);
            $out[] = $d;
        }
        return $out;
    }

    /* ═══════════════ Advice ═══════════════ */
    private function buildAdvice(array $wx): array {
        if (empty($wx['ok'])) {
            return [
                'planting'   => [['icon' => '⚠️', 'text' => 'Live weather unavailable — ' . ($wx['error'] ?? ''), 'level' => 'warn']],
                'irrigation' => [['icon' => '⚠️', 'text' => 'Check API key configuration.', 'level' => 'warn']],
                'protection' => [['icon' => 'ℹ️', 'text' => 'Advice available when live data loads.', 'level' => 'ok']],
            ];
        }

        $temp = (float)$wx['temp'];
        $hum  = (float)$wx['humidity'];
        $wind = (float)$wx['wind_speed'];
        $rain = (float)$wx['rain_1h'];
        $uv   = (int)($wx['uv']['index'] ?? 0);
        $fc   = $wx['forecast'] ?? [];

        $rainNext48 = 0;
        for ($i = 0; $i < min(2, count($fc)); $i++) {
            $rainNext48 += (float)($fc[$i]['rain'] ?? 0);
        }

        $planting = []; $irrigation = []; $protection = [];

        if ($rainNext48 >= 10) {
            $planting[] = ['icon' => '🌱', 'text' => "Good planting window — {$rainNext48}mm rain forecast in 48h.", 'level' => 'good'];
        } elseif ($rainNext48 >= 2) {
            $planting[] = ['icon' => '🌱', 'text' => 'Light rain expected — suitable for hardy crops.', 'level' => 'ok'];
        } else {
            $planting[] = ['icon' => '⚠️', 'text' => 'Dry conditions — delay delicate planting.', 'level' => 'warn'];
        }
        if ($temp > 32) $planting[] = ['icon' => '🔥', 'text' => 'Extreme heat — plant early morning or evening.', 'level' => 'warn'];
        if ($temp < 12) $planting[] = ['icon' => '❄️', 'text' => 'Cold — protect seedlings with mulch.', 'level' => 'warn'];

        if ($rain > 2) {
            $irrigation[] = ['icon' => '🌧️', 'text' => 'Rain detected — skip irrigation today.', 'level' => 'good'];
        } elseif ($rainNext48 >= 10) {
            $irrigation[] = ['icon' => '💧', 'text' => 'Rain coming — reduce irrigation by 50%.', 'level' => 'ok'];
        } elseif ($hum < 40 && $temp > 28) {
            $irrigation[] = ['icon' => '🚿', 'text' => 'Hot + dry — irrigate this evening.', 'level' => 'warn'];
        } else {
            $irrigation[] = ['icon' => '💧', 'text' => 'Normal irrigation schedule.', 'level' => 'ok'];
        }

        if ($wind > 25) $protection[] = ['icon' => '💨', 'text' => "Strong winds ({$wind} km/h) — secure structures.", 'level' => 'warn'];
        if ($uv >= 8)   $protection[] = ['icon' => '☀️', 'text' => 'Extreme UV — wear sun protection for fieldwork.', 'level' => 'warn'];
        if ($hum > 75 && $temp > 18 && $temp < 28) {
            $protection[] = ['icon' => '🍄', 'text' => 'Fungal risk — apply preventative fungicide.', 'level' => 'warn'];
        }
        if ($rainNext48 > 40) $protection[] = ['icon' => '🌊', 'text' => 'Heavy rain expected — check drainage.', 'level' => 'warn'];
        if (empty($protection)) $protection[] = ['icon' => '✅', 'text' => 'No significant weather threats.', 'level' => 'good'];

        return [
            'planting'   => $planting,
            'irrigation' => $irrigation,
            'protection' => $protection,
        ];
    }

    /* ═══════════════ Helpers ═══════════════ */
    private function nearestDistrict(float $lat, float $lng): string {
        $best = 'Huye'; $bestD = PHP_FLOAT_MAX;
        foreach (self::COORDS as $name => $c) {
            $d = pow($lat - $c[0], 2) + pow($lng - $c[1], 2);
            if ($d < $bestD) { $bestD = $d; $best = $name; }
        }
        return $best;
    }

    private function windDir(int $deg): string {
        $dirs = ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'];
        return $dirs[((int)round($deg / 22.5)) % 16];
    }

    private function emoji(string $icon): string {
        $map = [
            '01d'=>'☀️','01n'=>'🌙','02d'=>'⛅','02n'=>'☁️','03d'=>'☁️','03n'=>'☁️',
            '04d'=>'☁️','04n'=>'☁️','09d'=>'🌧️','09n'=>'🌧️','10d'=>'🌦️','10n'=>'🌧️',
            '11d'=>'⛈️','11n'=>'⛈️','13d'=>'❄️','13n'=>'❄️','50d'=>'🌫️','50n'=>'🌫️',
        ];
        return $map[$icon] ?? '🌤️';
    }

    private function httpGet(string $url) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT      => 'SAIP/2.0',
            ]);
            $out = curl_exec($ch);
            curl_close($ch);
            return $out;
        }
        $ctx = stream_context_create([
            'http' => ['timeout' => 8, 'user_agent' => 'SAIP/2.0'],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);
        return @file_get_contents($url, false, $ctx);
    }

    private function fallbackPayload(string $district, string $reason): array {
        return [
            'ok'         => false,
            'source'     => 'fallback',
            'error'      => $reason,
            'district'   => $district,
            'updated_at' => date('g:i A'),
            'temp'       => 24,
            'feels_like' => 25,
            'temp_min'   => 18,
            'temp_max'   => 27,
            'humidity'   => 60,
            'pressure'   => 1013,
            'wind_speed' => 8,
            'wind_deg'   => 180,
            'wind_dir'   => 'S',
            'clouds'     => 45,
            'rain_1h'    => 0,
            'condition'  => 'Partly cloudy',
            'description'=> 'Partly cloudy',
            'icon'       => '02d',
            'icon_emoji' => '⛅',
            'sunrise'    => '06:00',
            'sunset'     => '18:00',
            'uv'         => ['index' => 6, 'level' => 'High', 'class' => 'warn'],
            'forecast'   => [],
        ];
    }
}