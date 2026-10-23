<?php
namespace App\Models;

use App\Core\Database;

/**
 * Disease Detector
 *
 * Consumes admin-curated disease records (diseases table).
 * In production, swap the `computeMatch()` method for a real
 * CNN inference call (Python FastAPI endpoint).
 */
class DiseaseDetector {

    /* ═══════════════════════════════════════════════════════════
       DETECTION
       ═══════════════════════════════════════════════════════════ */
    public static function detect($imagePath, $cropId = null): array {
        /* 1. Load admin-curated diseases for this crop */
        $diseases = self::loadDiseases($cropId);

        if (empty($diseases)) {
            return self::fallback();
        }

        /* 2. Deterministic hash → same image always yields same disease */
        $hash = crc32(file_get_contents($imagePath));

        /* 3. Weight by severity — critical diseases get better odds
              (simulates a model that prioritizes high-risk classes) */
        $weighted = [];
        foreach ($diseases as $d) {
            $weight = match ($d['severity'] ?? 'medium') {
                'critical' => 4,
                'high'     => 3,
                'medium'   => 2,
                'low'      => 1,
                default    => 2,
            };
            for ($i = 0; $i < $weight; $i++) $weighted[] = $d;
        }

        $picked = $weighted[$hash % count($weighted)];

        /* 4. Confidence between 0.72 and 0.96 */
        $confidence = 0.72 + (($hash % 24) / 100);

        return [
            'disease_id'     => (int)$picked['id'],
            'disease_name'   => $picked['name'],
            'confidence'     => round($confidence, 2),
            'severity'       => $picked['severity'] ?? 'medium',
            'treatment'      => $picked['treatment'] ?? 'Consult an agricultural officer.',
            'recommendation' => $picked['prevention'] ?? 'Monitor the crop regularly.',
            'pathogen'       => $picked['pathogen_type'] ?? 'unknown',
            'symptoms'       => $picked['symptoms'] ?? '',
            'description'    => $picked['description'] ?? '',
            'image_url'      => $picked['image_url'] ?? null,
            'gallery'        => !empty($picked['gallery'])
                                    ? (json_decode($picked['gallery'], true) ?: [])
                                    : [],
        ];
    }

    /* ═══════════════════════════════════════════════════════════
       YIELD PREDICTION (unchanged from your original)
       ═══════════════════════════════════════════════════════════ */
    public static function predictYield($farmCrop, $weatherData = null): array {
        $area = (float)($farmCrop['area_ha'] ?? 1);

        $baseYieldPerHa = [
            1 => 2500,  2 => 1200,  3 => 4000,  4 => 3000,
            5 => 12000, 6 => 15000, 7 => 18000, 8 => 800,
            9 => 1500,  10 => 25000, 11 => 18000, 12 => 2000,
        ];
        $base = $baseYieldPerHa[(int)($farmCrop['crop_id'] ?? 0)] ?? 2000;

        $adjuster = 1.0;
        if ($weatherData) {
            $rain = (float)($weatherData['rainfall'] ?? 0);
            if ($rain > 5 && $rain < 30) $adjuster += 0.15;
            elseif ($rain > 50) $adjuster -= 0.20;

            $temp = (float)($weatherData['temperature'] ?? 25);
            if ($temp > 35) $adjuster -= 0.15;
            elseif ($temp < 12) $adjuster -= 0.10;
        }

        $stageMult = match ($farmCrop['growth_stage'] ?? 'planting') {
            'planting'    => 1.00,
            'germination' => 1.02,
            'vegetative'  => 1.05,
            'flowering'   => 1.08,
            'fruiting'    => 1.12,
            'maturity'    => 1.15,
            'harvested'   => 1.00,
            default       => 1.00,
        };

        $predicted = $area * $base * $adjuster * $stageMult;

        return [
            'predicted_yield_kg' => (int)round($predicted),
            'confidence'         => 0.78,
            'features' => [
                'area_ha'    => $area,
                'base_yield' => $base,
                'weather'    => $adjuster,
                'stage'      => $stageMult,
            ],
        ];
    }

    /* ═══════════════════════════════════════════════════════════
       FERTILIZER RECOMMENDATION (unchanged)
       ═══════════════════════════════════════════════════════════ */
    public static function recommendFertilizer($farmCrop, $soilTest = null): array {
        $cropId = (int)($farmCrop['crop_id'] ?? 0);
        $recommendations = [];

        $npk = [
            1  => ['N'=>100,'P'=>50, 'K'=>40],
            2  => ['N'=>30, 'P'=>60, 'K'=>40],
            3  => ['N'=>90, 'P'=>45, 'K'=>45],
            5  => ['N'=>120,'P'=>80, 'K'=>150],
            10 => ['N'=>150,'P'=>90, 'K'=>200],
        ];
        $need = $npk[$cropId] ?? ['N'=>60,'P'=>40,'K'=>40];

        if ($soilTest) {
            if (($soilTest['nitrogen_ppm'] ?? 0)   > 40) $need['N'] *= 0.6;
            if (($soilTest['phosphorus_ppm'] ?? 0) > 30) $need['P'] *= 0.6;
            if (($soilTest['potassium_ppm'] ?? 0)  > 40) $need['K'] *= 0.6;
            if (($soilTest['ph'] ?? 7) < 5.5) {
                $recommendations[] = [
                    'type'=>'Lime','qty'=>500,'method'=>'Broadcast',
                    'timing'=>'2 weeks before planting',
                    'reason'=>'Soil is acidic (pH '.$soilTest['ph'].')',
                ];
            }
        }

        $recommendations[] = [
            'type'   => 'NPK 17-17-17',
            'qty'    => (int)round(($need['N'] + $need['P'] + $need['K']) / 3),
            'method' => 'Band placement near roots',
            'timing' => 'Split: 50% at planting, 50% at flowering',
            'reason' => 'Balanced NPK for ' . ($farmCrop['crop_name'] ?? 'this crop'),
        ];

        if ($need['N'] > 80) {
            $recommendations[] = [
                'type'   => 'Urea (46% N)',
                'qty'    => (int)round($need['N'] * 0.4),
                'method' => 'Top dress',
                'timing' => 'Vegetative stage',
                'reason' => 'High nitrogen demand',
            ];
        }

        return $recommendations;
    }

    /* ═══════════════════════════════════════════════════════════
       HELPERS
       ═══════════════════════════════════════════════════════════ */

    /** Load active diseases for the crop — from admin-curated table */
    private static function loadDiseases($cropId): array {
        try {
            $db = new Database();
            $sql = "SELECT id, crop_id, name, pathogen_type, description, symptoms,
                           treatment, prevention, severity, image_url, gallery,
                           tags, is_active
                    FROM diseases
                    WHERE is_active = 1";
            if ($cropId) {
                $sql .= " AND (crop_id = :cid OR crop_id IS NULL)";
            }
            $sql .= " ORDER BY FIELD(severity, 'critical','high','medium','low'), name";

            $db->query($sql);
            if ($cropId) $db->bind(':cid', (int)$cropId);
            $rows = $db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private static function fallback(): array {
        return [
            'disease_id'     => null,
            'disease_name'   => 'Unknown',
            'confidence'     => 0.35,
            'severity'       => 'medium',
            'treatment'      => 'Consult an agricultural officer for a proper diagnosis.',
            'recommendation' => 'Take more photos from different angles, in daylight.',
            'pathogen'       => 'unknown',
            'symptoms'       => '',
            'description'    => '',
            'image_url'      => null,
            'gallery'        => [],
        ];
    }
}