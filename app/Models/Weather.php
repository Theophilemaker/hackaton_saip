<?php
namespace App\Models;

use App\Core\Database;

/**
 * Weather Service — OpenWeatherMap API wrapper with caching.
 * Fetches current + 5-day forecast based on lat/lng or district name.
 */
class Weather {

    private $db;
    private $apiKey;
    private $baseUrl = 'https://api.openweathermap.org/data/2.5';
    private $cacheMinutes = 30;

    public function __construct() {
        $this->db = new Database();

        // Store your API key in config or environment variable
        $this->apiKey = defined('OPENWEATHER_API_KEY')
            ? OPENWEATHER_API_KEY
            : ($_ENV['OPENWEATHER_API_KEY'] ?? 'YOUR_API_KEY_HERE');
    }

    /* ═══════════════════════════════════════════════════════════
       MAIN ENTRY — returns current + forecast, cached
       ═══════════════════════════════════════════════════════════ */
    public function getByDistrict(string $district): ?array {
        $district = trim($district);
        if ($district === '') return null;

        // 1. Look up coordinates for this district
        $this->db->query("SELECT lat, lng FROM district_coords WHERE LOWER(district) = LOWER(:d) LIMIT 1");
        $this->db->bind(':d', $district);
        $coords = $this->db->single();

        if (!$coords) {
            // Unknown district → default to Kigali
            $coords = ['lat' => -1.9500, 'lng' => 30.0500];
        }

        return $this->getByCoords((float)$coords['lat'], (float)$coords['lng'], $district);
    }

    public function getByCoords(float $lat, float $lng, ?string $district = null): ?array {
        // Round coords to 2 decimals for cache key (city-level precision)
        $latR = round($lat, 2);
        $lngR = round($lng, 2);
        $cacheKey = "wx_{$latR}_{$lngR}";

        // 1. Check cache
        $cached = $this->getCached($cacheKey);
        if ($cached) {
            $cached['source'] = 'cache';
            return $cached;
        }

        // 2. Fetch from API
        $current = $this->fetchCurrent($lat, $lng);
        $forecast = $this->fetchForecast($lat, $lng);

        if (!$current && !$forecast) {
            // API failed — return stale cache if available
            $stale = $this->getStaleCache($cacheKey);
            if ($stale) {
                $stale['source'] = 'stale-cache';
                $stale['warning'] = 'Live data unavailable — showing last cached values.';
                return $stale;
            }
            return null;
        }

        $data = [
            'current'  => $current,
            'forecast' => $forecast,
            'district' => $district,
            'lat'      => $latR,
            'lng'      => $lngR,
            'source'   => 'api',
            'fetched'  => date('c'),
        ];

        // 3. Store in cache
        $this->saveCache($cacheKey, $district, $latR, $lngR, $current, $forecast);

        return $data;
    }

    /* ═══════════════════════════════════════════════════════════
       OPENWEATHER API CALLS
       ═══════════════════════════════════════════════════════════ */
    private function fetchCurrent(float $lat, float $lng): ?array {
        if ($this->apiKey === 'YOUR_API_KEY_HERE') {
            return $this->mockCurrent($lat, $lng);
        }

        $url = $this->baseUrl . '/weather?'
             . http_build_query([
                'lat'   => $lat,
                'lon'   => $lng,
                'units' => 'metric',
                'appid' => $this->apiKey,
             ]);

        $raw = $this->httpGet($url);
        if (!$raw) return null;

        $data = json_decode($raw, true);
        if (!isset($data['main'])) return null;

        return [
            'temp'         => round($data['main']['temp']),
            'feels_like'   => round($data['main']['feels_like']),
            'temp_min'     => round($data['main']['temp_min']),
            'temp_max'     => round($data['main']['temp_max']),
            'humidity'     => (int)$data['main']['humidity'],
            'pressure'     => (int)($data['main']['pressure'] ?? 0),
            'wind_speed'   => round(($data['wind']['speed'] ?? 0) * 3.6, 1), // m/s → km/h
            'wind_deg'     => (int)($data['wind']['deg'] ?? 0),
            'clouds'       => (int)($data['clouds']['all'] ?? 0),
            'rain_1h'      => (float)($data['rain']['1h'] ?? 0),
            'description'  => ucfirst($data['weather'][0]['description'] ?? 'Clear'),
            'icon'         => $data['weather'][0]['icon'] ?? '01d',
            'sunrise'      => (int)($data['sys']['sunrise'] ?? 0),
            'sunset'       => (int)($data['sys']['sunset'] ?? 0),
            'city'         => $data['name'] ?? '',
        ];
    }

    private function fetchForecast(float $lat, float $lng): ?array {
        if ($this->apiKey === 'YOUR_API_KEY_HERE') {
            return $this->mockForecast();
        }

        $url = $this->baseUrl . '/forecast?'
             . http_build_query([
                'lat'   => $lat,
                'lon'   => $lng,
                'units' => 'metric',
                'appid' => $this->apiKey,
             ]);

        $raw = $this->httpGet($url);
        if (!$raw) return null;

        $data = json_decode($raw, true);
        if (!isset($data['list'])) return null;

        // Group 3-hour forecast into daily summaries
        $days = [];
        foreach ($data['list'] as $slot) {
            $date = date('Y-m-d', $slot['dt']);
            if (!isset($days[$date])) {
                $days[$date] = [
                    'date'      => $date,
                    'temp_min'  => $slot['main']['temp_min'],
                    'temp_max'  => $slot['main']['temp_max'],
                    'rain'      => 0,
                    'icons'     => [],
                    'desc'      => [],
                ];
            }
            $days[$date]['temp_min'] = min($days[$date]['temp_min'], $slot['main']['temp_min']);
            $days[$date]['temp_max'] = max($days[$date]['temp_max'], $slot['main']['temp_max']);
            $days[$date]['rain']    += (float)($slot['rain']['3h'] ?? 0);
            $days[$date]['icons'][]  = $slot['weather'][0]['icon'] ?? '01d';
            $days[$date]['desc'][]   = $slot['weather'][0]['description'] ?? '';
        }

        $out = [];
        foreach (array_slice($days, 0, 5) as $d) {
            // Pick most common icon
            $iconCounts = array_count_values($d['icons']);
            arsort($iconCounts);
            $mainIcon = array_key_first($iconCounts) ?: '01d';

            $descCounts = array_count_values($d['desc']);
            arsort($descCounts);
            $mainDesc = array_key_first($descCounts) ?: 'Clear';

            $out[] = [
                'date'      => $d['date'],
                'day'       => date('D', strtotime($d['date'])),
                'temp_min'  => round($d['temp_min']),
                'temp_max'  => round($d['temp_max']),
                'rain'      => round($d['rain'], 1),
                'icon'      => $mainIcon,
                'desc'      => ucfirst($mainDesc),
            ];
        }

        return $out;
    }

    /* ═══════════════════════════════════════════════════════════
       HTTP + CACHE
       ═══════════════════════════════════════════════════════════ */
    private function httpGet(string $url): ?string {
        // Try cURL first
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT      => 'SAIP-Weather/1.0',
            ]);
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($body !== false && $code === 200) return $body;
        }

        // Fallback to file_get_contents
        $ctx = stream_context_create([
            'http' => ['timeout' => 8, 'user_agent' => 'SAIP-Weather/1.0'],
            'ssl'  => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        return $body ?: null;
    }

    private function getCached(string $key): ?array {
        try {
            $this->db->query("SELECT * FROM weather_cache WHERE cache_key = :k AND expires_at > NOW() LIMIT 1");
            $this->db->bind(':k', $key);
            $row = $this->db->single();
            if (!$row) return null;

            return [
                'current'  => json_decode($row['current_json'], true),
                'forecast' => json_decode($row['forecast_json'], true),
                'district' => $row['district'],
                'lat'      => (float)$row['lat'],
                'lng'      => (float)$row['lng'],
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getStaleCache(string $key): ?array {
        try {
            $this->db->query("SELECT * FROM weather_cache WHERE cache_key = :k ORDER BY fetched_at DESC LIMIT 1");
            $this->db->bind(':k', $key);
            $row = $this->db->single();
            if (!$row) return null;

            return [
                'current'  => json_decode($row['current_json'], true),
                'forecast' => json_decode($row['forecast_json'], true),
                'district' => $row['district'],
                'lat'      => (float)$row['lat'],
                'lng'      => (float)$row['lng'],
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function saveCache(string $key, ?string $district, float $lat, float $lng, ?array $current, ?array $forecast): void {
        try {
            $this->db->query("INSERT INTO weather_cache
                (cache_key, district, lat, lng, current_json, forecast_json, fetched_at, expires_at)
                VALUES (:k, :d, :la, :lo, :cj, :fj, NOW(), DATE_ADD(NOW(), INTERVAL :m MINUTE))
                ON DUPLICATE KEY UPDATE
                    district = VALUES(district),
                    current_json = VALUES(current_json),
                    forecast_json = VALUES(forecast_json),
                    fetched_at = NOW(),
                    expires_at = VALUES(expires_at)");
            $this->db->bind(':k', $key);
            $this->db->bind(':d', $district);
            $this->db->bind(':la', $lat);
            $this->db->bind(':lo', $lng);
            $this->db->bind(':cj', json_encode($current));
            $this->db->bind(':fj', json_encode($forecast));
            $this->db->bind(':m', $this->cacheMinutes);
            $this->db->execute();
        } catch (\Throwable $e) {
            error_log("Weather cache save error: " . $e->getMessage());
        }
    }

    /* ═══════════════════════════════════════════════════════════
       MOCK DATA (when no API key set)
       ═══════════════════════════════════════════════════════════ */
    private function mockCurrent(float $lat, float $lng): array {
        return [
            'temp'        => 24,
            'feels_like'  => 26,
            'temp_min'    => 18,
            'temp_max'    => 27,
            'humidity'    => 65,
            'pressure'    => 1013,
            'wind_speed'  => 12,
            'wind_deg'    => 180,
            'clouds'      => 40,
            'rain_1h'     => 0,
            'description' => 'Partly cloudy',
            'icon'        => '02d',
            'sunrise'     => strtotime('today 6:00'),
            'sunset'      => strtotime('today 18:00'),
            'city'        => 'Kigali',
        ];
    }

    private function mockForecast(): array {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
        $icons = ['02d', '01d', '10d', '04d', '10d'];
        $rains = [0, 0, 5, 2, 8];
        $out = [];
        for ($i = 0; $i < 5; $i++) {
            $out[] = [
                'date'     => date('Y-m-d', strtotime("+{$i} day")),
                'day'      => $days[$i],
                'temp_min' => 17 + $i,
                'temp_max' => 25 + $i,
                'rain'     => $rains[$i],
                'icon'     => $icons[$i],
                'desc'     => 'Partly cloudy',
            ];
        }
        return $out;
    }
}