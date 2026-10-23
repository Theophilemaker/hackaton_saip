<?php
namespace App\Models;

class WeatherAdvice {

    public static function generate($current, $forecast) {
        $advice = ['planting' => [], 'irrigation' => [], 'fertilizer' => [], 'alerts' => []];

        if (!$current) return $advice;

        $rainToday = $current['rainfall'] ?? 0;
        $temp      = $current['temperature'] ?? 25;
        $humidity  = $current['humidity'] ?? 60;
        $wind      = $current['wind_speed'] ?? 0;

        $rain5d = 0;
        $minTemp5d = 100;
        foreach ($forecast as $day) {
            $rain5d += $day['rain'] ?? 0;
            $minTemp5d = min($minTemp5d, $day['temp_min'] ?? 20);
        }

        // PLANTING
        if ($rain5d >= 20 && $rain5d <= 80 && $minTemp5d > 12) {
            $advice['planting'][] = [
                'level' => 'success', 'icon' => '🌱',
                'text'  => 'Optimal planting window. Expected rainfall (' . round($rain5d) . 'mm over 5 days) is ideal.',
            ];
        } elseif ($rain5d > 80) {
            $advice['planting'][] = [
                'level' => 'warning', 'icon' => '⚠️',
                'text'  => 'Heavy rain expected (' . round($rain5d) . 'mm). Delay planting.',
            ];
        } elseif ($rain5d < 10) {
            $advice['planting'][] = [
                'level' => 'danger', 'icon' => '🚫',
                'text'  => 'Very low rainfall (' . round($rain5d) . 'mm). Prepare irrigation.',
            ];
        }

        if ($minTemp5d < 10) {
            $advice['planting'][] = [
                'level' => 'warning', 'icon' => '❄️',
                'text'  => 'Low temperature alert (' . round($minTemp5d) . '°C).',
            ];
        }

        // IRRIGATION
        if ($rainToday > 5) {
            $advice['irrigation'][] = [
                'level' => 'success', 'icon' => '💧',
                'text'  => 'No irrigation needed. ' . round($rainToday) . 'mm rain received.',
            ];
        } elseif ($rainToday < 1 && $humidity < 50 && $temp > 25) {
            $advice['irrigation'][] = [
                'level' => 'warning', 'icon' => '🚿',
                'text'  => 'Irrigate early morning or evening.',
            ];
        }

        if (isset($forecast[1]) && ($forecast[1]['rain'] ?? 0) > 10) {
            $advice['irrigation'][] = [
                'level' => 'info', 'icon' => '🌧️',
                'text'  => 'Rain expected tomorrow. Skip irrigation.',
            ];
        }

        // FERTILIZER
        if ($rainToday > 2 && $rainToday < 20) {
            $advice['fertilizer'][] = [
                'level' => 'success', 'icon' => '🧪',
                'text'  => 'Good conditions for fertilizer application.',
            ];
        } elseif ($rainToday > 30) {
            $advice['fertilizer'][] = [
                'level' => 'danger', 'icon' => '🚫',
                'text'  => 'Do NOT apply fertilizer today. Heavy rain will wash it away.',
            ];
        } elseif ($wind > 25) {
            $advice['fertilizer'][] = [
                'level' => 'warning', 'icon' => '💨',
                'text'  => 'High wind (' . $wind . ' km/h). Avoid spraying.',
            ];
        }

        // ALERTS
        if ($rain5d > 100) {
            $advice['alerts'][] = ['type' => 'flood', 'severity' => 'high',
                'text' => 'Flood risk: ' . round($rain5d) . 'mm in 5 days.'];
        }

        return $advice;
    }

    public static function levelColor($level) {
        return match($level) {
            'success' => '#22C55E',
            'warning' => '#F59E0B',
            'danger'  => '#EF4444',
            'info'    => '#0EA5E9',
            default   => '#C8D5D1',
        };
    }
}
?>