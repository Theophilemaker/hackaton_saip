<?php
namespace App\Models;

use App\Core\Database;
use App\Helpers\NisrEngine;

/**
 * Recommendation Engine
 * Generates personalized advice for farmers & cooperatives.
 */
class Recommendation {

    private $db;
    private $engine;
    private $analytics;

    public function __construct() {
        $this->db        = new Database();
        $this->engine    = new NisrEngine();
        $this->analytics = new NisrAnalytics();
    }

    /* ═══════════════════════════════════════════════════════════
       FEATURE 1: "What Should I Plant?"
       ═══════════════════════════════════════════════════════════
       Returns ranked crop recommendations for a farmer.
    */
    public function recommendCrops(array $profile, int $limit = 3): array {
        $district = $profile['district'] ?? null;
        $landSize = (float)($profile['land_size_ha'] ?? 1.0);

        if (!$district) return [];

        // Find the most recent published agricultural dataset
        $this->db->query("
            SELECT id FROM nisr_datasets
            WHERE survey_type = 'agricultural_survey'
              AND status = 'published'
            ORDER BY year DESC, created_at DESC
            LIMIT 1
        ");
        $dataset = $this->db->single();
        if (!$dataset) return [];

        $datasetId = (int)$dataset['id'];

        // Get the schema to find relevant columns
        $this->db->query("SELECT columns_schema FROM nisr_datasets WHERE id = :id");
        $this->db->bind(':id', $datasetId);
        $ds = $this->db->single();
        $schema = json_decode($ds['columns_schema'] ?? '[]', true);

        // Identify column names
        $cropCol   = $this->findColumn($schema, ['crop', 'crop_name']);
        $distCol   = $this->findColumn($schema, ['district']);
        $yieldCol  = $this->findColumn($schema, ['yield_kg', 'yield', 'production_kg', 'production']);
        $areaCol   = $this->findColumn($schema, ['area_ha', 'area', 'hectares']);

        if (!$cropCol || !$distCol || !$yieldCol) {
            return []; // Not enough data
        }

        // Use C++ engine for fast pivot
        $csvPath = $this->getCsvPath($datasetId);
        $yieldsByCrop = [];

        if ($csvPath && $this->engine->isAvailable()) {
            // Pivot: crop × district → sum(yield)
            $pivot = $this->engine->pivot($csvPath, $cropCol, $distCol, $yieldCol, 'avg');
            if ($pivot && !empty($pivot['data'])) {
                foreach ($pivot['data'] as $crop => $byDistrict) {
                    // Get district-specific or national average
                    $yieldsByCrop[$crop] = $byDistrict[$district] ?? $this->avg($byDistrict);
                }
            }
        }

        // Fallback to PHP if C++ unavailable
        if (empty($yieldsByCrop)) {
            $pivot = $this->analytics->pivot($datasetId, $cropCol, $distCol, $yieldCol, 'avg');
            foreach ($pivot['data'] as $crop => $byDistrict) {
                $yieldsByCrop[$crop] = $byDistrict[$district] ?? $this->avg($byDistrict);
            }
        }

        if (empty($yieldsByCrop)) return [];

        // Get market prices (latest per crop)
        $prices = $this->getLatestPrices();

        // Compute projected revenue for each crop
        $recommendations = [];
        foreach ($yieldsByCrop as $crop => $yieldPerHa) {
            $pricePerKg = $prices[$crop] ?? $this->defaultPrice($crop);
            if ($yieldPerKg <= 0) continue;

            // yieldPerHa is in kg/ha (typical for NISR data)
            $projectedRevenue = $yieldPerHa * $pricePerKg * $landSize;

            $recommendations[] = [
                'crop'              => $crop,
                'yield_per_ha'      => round($yieldPerHa, 1),
                'price_per_kg'      => $pricePerKg,
                'projected_revenue' => round($projectedRevenue),
                'land_size_ha'      => $landSize,
                'difficulty'        => $this->cropDifficulty($crop),
                'risk'              => $this->cropRisk($crop, $district),
                'growth_cycle_days' => $this->cropGrowthDays($crop),
            ];
        }

        // Sort by projected revenue descending
        usort($recommendations, fn($a, $b) => $b['projected_revenue'] <=> $a['projected_revenue']);

        // Take top N
        $recommendations = array_slice($recommendations, 0, $limit);

        // Log the recommendation
        $this->logRecommendation(
            $profile['user_id'] ?? 0,
            'crops',
            ['district' => $district, 'land_size_ha' => $landSize],
            ['top_crops' => array_column($recommendations, 'crop')]
        );

        return $recommendations;
    }

    /* ═══════════════════════════════════════════════════════════
       FEATURE 6: Benchmarking vs District Average
       ═══════════════════════════════════════════════════════════ */
    public function benchmark(array $profile, string $crop): ?array {
        $district = $profile['district'] ?? null;
        if (!$district || !$crop) return null;

        // Get the district average for this crop
        $this->db->query("
            SELECT id FROM nisr_datasets
            WHERE survey_type = 'agricultural_survey' AND status = 'published'
            ORDER BY year DESC LIMIT 1
        ");
        $dataset = $this->db->single();
        if (!$dataset) return null;

        $datasetId = (int)$dataset['id'];
        $csvPath = $this->getCsvPath($datasetId);

        // District average yield for this crop
        $districtAvg = 0;
        if ($csvPath && $this->engine->isAvailable()) {
            $pivot = $this->engine->pivot($csvPath, 'crop', 'district', 'yield_kg', 'avg');
            $districtAvg = $pivot['data'][$crop][$district] ?? 0;
        }

        // National average
        $nationalAvg = 0;
        if ($csvPath && $this->engine->isAvailable()) {
            $pivot2 = $this->engine->pivot($csvPath, 'crop', 'crop', 'yield_kg', 'avg');
            $nationalAvg = $pivot2['data'][$crop][$crop] ?? 0;
        }

        // Farmer's own yield (from their saved harvest logs — optional)
        $farmerYield = (float)($profile['last_yield_kg_ha'] ?? 0);

        return [
            'crop'         => $crop,
            'district'     => $district,
            'farmer_yield' => $farmerYield,
            'district_avg' => round($districtAvg, 1),
            'national_avg' => round($nationalAvg, 1),
            'vs_district'  => $districtAvg > 0 ? round((($farmerYield - $districtAvg) / $districtAvg) * 100, 1) : 0,
        ];
    }

    /* ═══════════════════════════════════════════════════════════
       FEATURE 10: District League Table
       ═══════════════════════════════════════════════════════════ */
    public function leagueTable(string $crop): array {
        $this->db->query("
            SELECT id FROM nisr_datasets
            WHERE survey_type = 'agricultural_survey' AND status = 'published'
            ORDER BY year DESC LIMIT 1
        ");
        $dataset = $this->db->single();
        if (!$dataset) return [];

        $datasetId = (int)$dataset['id'];
        $csvPath = $this->getCsvPath($datasetId);

        $table = [];
        if ($csvPath && $this->engine->isAvailable()) {
            $pivot = $this->engine->pivot($csvPath, 'district', 'crop', 'yield_kg', 'avg');
            foreach ($pivot['data'] as $district => $crops) {
                if (isset($crops[$crop]) && $crops[$crop] > 0) {
                    $table[] = ['district' => $district, 'yield' => round($crops[$crop], 1)];
                }
            }
        }

        usort($table, fn($a, $b) => $b['yield'] <=> $a['yield']);
        return $table;
    }

    /* ═══════════════════════════════════════════════════════════
       HELPERS
       ═══════════════════════════════════════════════════════════ */

    private function findColumn(array $schema, array $candidates): ?string {
        $names = array_map(fn($c) => strtolower($c['name'] ?? ''), $schema);
        foreach ($candidates as $cand) {
            foreach ($names as $i => $name) {
                if ($name === $cand || strpos($name, $cand) !== false) {
                    return $schema[$i]['name'];
                }
            }
        }
        return null;
    }

    private function avg(array $values): float {
        $nums = array_filter($values, 'is_numeric');
        return count($nums) ? array_sum($nums) / count($nums) : 0;
    }

    private function getLatestPrices(): array {
        $this->db->query("
            SELECT crop, AVG(price_per_kg) AS avg_price
            FROM market_prices
            WHERE recorded_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY crop
        ");
        $rows = $this->db->resultSet();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['crop']] = (float)$r['avg_price'];
        }
        return $out;
    }

    private function defaultPrice(string $crop): float {
        return match(strtolower($crop)) {
            'maize'     => 450,
            'potatoes'  => 380,
            'beans'     => 850,
            'rice'      => 720,
            'tomatoes'  => 650,
            'coffee'    => 2400,
            'tea'       => 950,
            'bananas'   => 280,
            'cassava'   => 320,
            default     => 500,
        };
    }

    private function cropDifficulty(string $crop): string {
        return match(strtolower($crop)) {
            'potatoes', 'tomatoes', 'coffee' => 'Hard',
            'rice', 'tea'                    => 'Medium',
            'maize', 'beans', 'cassava'      => 'Easy',
            default                          => 'Medium',
        };
    }

    private function cropRisk(string $crop, string $district): string {
        // Simple heuristic — could be replaced with real disease data
        $risky = ['potatoes', 'tomatoes'];
        if (in_array(strtolower($crop), $risky)) return 'Medium';
        return 'Low';
    }

    private function cropGrowthDays(string $crop): int {
        return match(strtolower($crop)) {
            'beans'    => 90,
            'maize'    => 120,
            'potatoes' => 100,
            'tomatoes' => 90,
            'rice'     => 140,
            'cassava'  => 270,
            'bananas'  => 365,
            default    => 120,
        };
    }

    private function getCsvPath(int $datasetId): ?string {
        $this->db->query("SELECT file_path FROM nisr_datasets WHERE id = :id");
        $this->db->bind(':id', $datasetId);
        $row = $this->db->single();
        if (!$row || empty($row['file_path'])) return null;

        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $path = $base . '/public/' . ltrim($row['file_path'], '/');
        $real = realpath($path);
        $allowed = realpath($base . '/public/uploads/nisr');
        if (!$real || !$allowed || strpos($real, $allowed) !== 0) return null;
        return is_file($real) ? $real : null;
    }

    private function logRecommendation(int $userId, string $type, array $input, array $output): void {
        if ($userId <= 0) return;
        $this->db->query("INSERT INTO recommendations_log
            (user_id, recommendation_type, input_context, output_data)
            VALUES (:u, :t, :i, :o)");
        $this->db->bind(':u', $userId);
        $this->db->bind(':t', $type);
        $this->db->bind(':i', json_encode($input));
        $this->db->bind(':o', json_encode($output));
        $this->db->execute();
    }
}