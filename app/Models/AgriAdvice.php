<?php
namespace App\Models;

/**
 * AgriAdvice
 * Rule-based agricultural decision engine.
 * Replaces the old WeatherAdvice class.
 */
class AgriAdvice {

    /* ============================================================
     *  MAIN — Generate all advice
     * ============================================================ */
    public static function generate($current, $forecast) {
        $advice = [
            'planting'   => [],
            'irrigation' => [],
            'fertilizer' => [],
            'alerts'     => [],
        ];

        if (empty($current)) {
            return $advice;
        }

        $rainToday = (float)($current['rainfall']  ?? 0);
        $temp      = (float)($current['temperature'] ?? 25);
        $humidity  = (int)  ($current['humidity']   ?? 60);
        $wind      = (float)($current['wind_speed'] ?? 0);

        // ---- 5-day summary ----
        $rain5d    = 0;
        $minTemp5d = 100;
        $maxTemp5d = -100;

        if (is_array($forecast)) {
            foreach ($forecast as $day) {
                $rain5d    += (float)($day['rain']     ?? 0);
                $minTemp5d  = min($minTemp5d, (float)($day['temp_min'] ?? 20));
                $maxTemp5d  = max($maxTemp5d, (float)($day['temp_max'] ?? 20));
            }
        }

        if ($minTemp5d === 100) $minTemp5d = 20;
        if ($maxTemp5d === -100) $maxTemp5d = 20;

        /* ============================================================
         *  PLANTING ADVICE
         * ============================================================ */
        if ($rain5d >= 20 && $rain5d <= 80 && $minTemp5d > 12) {
            $advice['planting'][] = [
                'level' => 'success',
                'icon'  => '🌱',
                'text'  => 'Optimal planting window. Expected rainfall of '
                          . round($rain5d) . ' mm over 5 days is ideal for maize, beans, and potatoes.',
            ];
        } elseif ($rain5d > 80) {
            $advice['planting'][] = [
                'level' => 'warning',
                'icon'  => '⚠️',
                'text'  => 'Heavy rain expected (' . round($rain5d) . ' mm). Delay planting to avoid seed rot.',
            ];
        } elseif ($rain5d < 10) {
            $advice['planting'][] = [
                'level' => 'danger',
                'icon'  => '🚫',
                'text'  => 'Very low rainfall expected (' . round($rain5d) . ' mm). Prepare irrigation before planting.',
            ];
        }

        if ($minTemp5d < 10) {
            $advice['planting'][] = [
                'level' => 'warning',
                'icon'  => '❄️',
                'text'  => 'Low temperature alert (' . round($minTemp5d) . '°C). Protect frost-sensitive crops.',
            ];
        }

        /* ============================================================
         *  IRRIGATION ADVICE
         * ============================================================ */
        if ($rainToday > 5) {
            $advice['irrigation'][] = [
                'level' => 'success',
                'icon'  => '💧',
                'text'  => 'No irrigation needed today. ' . round($rainToday) . ' mm of rain received.',
            ];
        } elseif ($rainToday < 1 && $humidity < 50 && $temp > 25) {
            $advice['irrigation'][] = [
                'level' => 'warning',
                'icon'  => '🚿',
                'text'  => 'Irrigate early morning or evening. Low humidity ('
                          . $humidity . '%) and high temp (' . round($temp) . '°C) increase water loss.',
            ];
        }

        if (isset($forecast[1]) && (float)($forecast[1]['rain'] ?? 0) > 10) {
            $advice['irrigation'][] = [
                'level' => 'info',
                'icon'  => '🌧️',
                'text'  => 'Rain expected tomorrow (' . round($forecast[1]['rain']) . ' mm). Skip today\'s irrigation.',
            ];
        }

        /* ============================================================
         *  FERTILIZER ADVICE
         * ============================================================ */
        if ($rainToday > 2 && $rainToday < 20) {
            $advice['fertilizer'][] = [
                'level' => 'success',
                'icon'  => '🧪',
                'text'  => 'Good conditions for fertilizer application. Light rain will carry nutrients into the soil.',
            ];
        } elseif ($rainToday > 30) {
            $advice['fertilizer'][] = [
                'level' => 'danger',
                'icon'  => '🚫',
                'text'  => 'Do NOT apply fertilizer today. Heavy rain will wash nutrients away.',
            ];
        } elseif ($wind > 25) {
            $advice['fertilizer'][] = [
                'level' => 'warning',
                'icon'  => '💨',
                'text'  => 'High wind (' . round($wind) . ' km/h). Avoid spraying fertilizers or pesticides.',
            ];
        }

        /* ============================================================
         *  ALERTS
         * ============================================================ */
        if ($rain5d > 100) {
            $advice['alerts'][] = [
                'type'     => 'flood',
                'severity' => 'high',
                'text'     => 'Flood risk: ' . round($rain5d) . ' mm rain expected in 5 days.',
            ];
        }

        if ($rain5d < 5 && $maxTemp5d > 28) {
            $advice['alerts'][] = [
                'type'     => 'drought',
                'severity' => 'high',
                'text'     => 'Drought risk: high temperature and low rainfall.',
            ];
        }

        if ($minTemp5d < 5) {
            $advice['alerts'][] = [
                'type'     => 'frost',
                'severity' => 'medium',
                'text'     => 'Frost risk: temperatures dropping to ' . round($minTemp5d) . '°C.',
            ];
        }

        return $advice;
    }

    /* ============================================================
     *  Colour for a level
     * ============================================================ */
    public static function levelColor($level) {
        switch ($level) {
            case 'success': return '#22C55E';
            case 'warning': return '#F59E0B';
            case 'danger':  return '#EF4444';
            case 'info':    return '#0EA5E9';
            default:        return '#C8D5D1';
        }
    }
}
?>