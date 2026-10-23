<?php
namespace App\Models;

use App\Services\NisrDataService;

/**
 * YieldPredictor — ML-based crop yield prediction.
 *
 * Strategy:
 *   1. If models/yield_model.json exists (exported by train_yield_model.py),
 *      evaluate an XGBoost-style additive model in PHP.
 *   2. Otherwise, use a rule-based estimator calibrated against NISR SAS 2025.
 */
class YieldPredictor {

    private $nisr;
    private $model = null;

    public function __construct() {
        $this->nisr = new NisrDataService();
        $this->loadModel();
    }

    /* ═══════════════ PUBLIC ═══════════════ */

    /**
     * @param array $input {district, crop, area_ha, fertilizer_kg_ha,
     *                      irrigation_pct, soil_ph, rainfall_mm}
     * @return array Full prediction with confidence, comparison, interventions
     */
    public function predict(array $input): array {
        $district       = $input['district'] ?? 'Kigali';
        $crop           = $input['crop'] ?? 'Maize';
        $area           = (float)($input['area_ha'] ?? 1);
        $fertilizer     = (float)($input['fertilizer_kg_ha'] ?? 45);
        $irrigation     = (float)($input['irrigation_pct'] ?? 12);
        $soilPh         = (float)($input['soil_ph'] ?? 5.8);
        $rainfall       = (float)($input['rainfall_mm'] ?? 900);

        // Feature vector (order must match training)
        $features = [
            'district_code'      => $this->encodeDistrict($district),
            'crop_code'          => $this->encodeCrop($crop),
            'fertilizer_kg_ha'   => $fertilizer,
            'irrigation_pct'     => $irrigation,
            'soil_ph'            => $soilPh,
            'rainfall_mm'        => $rainfall,
        ];

        // Predict kg/ha
        $predictionKgHa = $this->model
            ? $this->evalModel($features)
            : $this->ruleBasedEstimate($crop, $fertilizer, $irrigation, $soilPh, $rainfall);

        $predictionKgHa = max(200, min(50000, $predictionKgHa));

        // Confidence (based on how close inputs are to typical values)
        $confidence = $this->computeConfidence($features);

        // Total production
        $totalKg = round($predictionKgHa * $area);

        // Comparison
        $districtAvg = $this->nisr->getYield($district, $crop) ?: $this->nisr->getAvgDistrictYield($district);
        $nationalAvg = $this->nisr->getNationalAverages()[$crop] ?? 2.1;
        $nationalAvgKgHa = $nationalAvg * 1000;

        // Best practice (top 25% of district)
        $bestPracticeKgHa = $districtAvg * 1.35;

        // Interventions
        $interventions = $this->suggestInterventions($crop, $fertilizer, $irrigation, $soilPh, $rainfall, $predictionKgHa);

        // ═══════════════ PREDICTIVE MARKET ALERT ═══════════════
        $market_alert = null;
        
        // Simulated market trend (in a real scenario, query your market_prices table)
        // Let's assume if the predicted yield is very high, the market price drops
        if ($predictionKgHa > ($districtAvg * 1.2)) {
            $market_alert = [
                'type' => 'warning',
                'title' => 'Bumper Harvest Alert 📉',
                'message' => "Your predicted yield is 20% above district average. Expect market prices to drop due to oversupply. Consider value addition (e.g., processing into flour) or storage."
            ];
        } elseif ($predictionKgHa < ($districtAvg * 0.8)) {
            $market_alert = [
                'type' => 'info',
                'title' => 'Supply Shortage Alert 📈',
                'message' => "Your predicted yield is below average. Market prices are likely to be high. Hold your stock if possible to maximize revenue."
            ];
        }
        // ═══════════════════════════════════════════════════════

        return [
            'predicted_yield_kg_ha' => round($predictionKgHa, 0),
            'predicted_yield_mt_ha' => round($predictionKgHa / 1000, 2),
            'total_production_kg'   => $totalKg,
            'total_production_tons' => round($totalKg / 1000, 2),
            'confidence'            => $confidence,
            'model_used'            => $this->model ? 'xgboost_v1' : 'nisr_rule_based',
            'district_avg_kg_ha'    => round($districtAvg, 0),
            'national_avg_kg_ha'    => round($nationalAvgKgHa, 0),
            'best_practice_kg_ha'   => round($bestPracticeKgHa, 0),
            'gap_to_district_pct'   => $districtAvg > 0 ? round((($predictionKgHa - $districtAvg) / $districtAvg) * 100, 1) : 0,
            'gap_to_national_pct'   => $nationalAvgKgHa > 0 ? round((($predictionKgHa - $nationalAvgKgHa) / $nationalAvgKgHa) * 100, 1) : 0,
            'gap_to_best_pct'       => $bestPracticeKgHa > 0 ? round((($predictionKgHa - $bestPracticeKgHa) / $bestPracticeKgHa) * 100, 1) : 0,
            'revenue_estimate_rwf'  => $this->estimateRevenue($crop, $totalKg),
            'interventions'         => $interventions,
            'estimated_gain_rwf'    => $this->totalGainEstimate($interventions, $area, $crop),
            'market_alert'          => $market_alert, // NEW: Market Alert data
            'inputs_used'           => $input,
            'generated_at'          => date('c'),
        ];
    }

    /* ═══════════════ PRIVATE ═══════════════ */

    private function loadModel(): void {
        $path = __DIR__ . '/../../models/yield_model.json';
        if (file_exists($path)) {
            $json = json_decode(file_get_contents($path), true);
            if (is_array($json) && isset($json['base_score'])) {
                $this->model = $json;
            }
        }
    }

    private function evalModel(array $f): float {
        // Simple additive model: score = base + Σ(coefficient × feature)
        $m     = $this->model;
        $score = (float)$m['base_score'];
        foreach ($m['coefficients'] as $feature => $coef) {
            if (isset($f[$feature])) $score += $coef * (float)$f[$feature];
        }
        return $score;
    }

    private function ruleBasedEstimate(string $crop, float $fert, float $irrig, float $ph, float $rain): float {
        // Published NISR base yields (kg/ha) under average conditions
        $base = [
            'Maize'         => 2100,
            'Beans'         => 1200,
            'Rice'          => 4800,
            'Irish Potato'  => 12500,
            'Sweet Potato'  => 8400,
            'Cassava'       => 14200,
            'Banana'        => 12000,
            'Coffee'        => 800,
            'Tea'           => 2200,
            'Tomato'        => 18000,
            'Onion'         => 15500,
            'Cabbage'       => 22000,
        ][$crop] ?? 2000;

        // Fertilizer response: every extra 50kg/ha → +15% (diminishing)
        $fertFactor = 1 + min(0.5, ($fert / 50) * 0.15);

        // Irrigation response: every extra 10% coverage → +8%
        $irrigFactor = 1 + min(0.4, ($irrig / 10) * 0.08);

        // Soil pH optimum 6.0-6.8 for most crops; below 5.5 → major loss
        $phFactor = 1.0;
        if ($ph < 5.0) $phFactor = 0.60;
        elseif ($ph < 5.5) $phFactor = 0.78;
        elseif ($ph < 6.0) $phFactor = 0.92;
        elseif ($ph <= 7.0) $phFactor = 1.0;
        else $phFactor = 0.88;

        // Rainfall optimum ~900-1200mm per year
        $rainFactor = 1.0;
        if ($rain < 600) $rainFactor = 0.72;
        elseif ($rain < 800) $rainFactor = 0.88;
        elseif ($rain <= 1200) $rainFactor = 1.0;
        elseif ($rain <= 1500) $rainFactor = 0.94;
        else $rainFactor = 0.85;

        return $base * $fertFactor * $irrigFactor * $phFactor * $rainFactor;
    }

    private function computeConfidence(array $f): float {
        // Higher confidence when inputs are near typical values
        $conf = 0.85;
        if ($f['fertilizer_kg_ha'] < 20 || $f['fertilizer_kg_ha'] > 200) $conf -= 0.08;
        if ($f['soil_ph'] < 4.5 || $f['soil_ph'] > 8) $conf -= 0.10;
        if ($f['rainfall_mm'] < 500 || $f['rainfall_mm'] > 1800) $conf -= 0.07;
        return round(max(0.55, $conf), 2);
    }

    private function suggestInterventions(string $crop, float $fert, float $irrig, float $ph, float $rain, float $current): array {
        $interventions = [];

        // 1. Fertilizer
        if ($fert < 100) {
            $extra = 50;
            $gainPct = min(0.22, ($extra / 50) * 0.15);
            $gainKg = $current * $gainPct;
            $interventions[] = [
                'action'          => 'Apply ' . $extra . ' kg/ha more NPK 17-17-17',
                'cost_rwf'        => $extra * 550, // ~550 RWF/kg
                'yield_gain_pct'  => round($gainPct * 100, 1),
                'yield_gain_kg'   => round($gainKg),
                'roi_months'      => 4,
                'priority'        => 'high',
            ];
        }

        // 2. Soil pH correction (lime)
        if ($ph < 5.5) {
            $limeKgHa = ($ph < 5.0) ? 400 : 250;
            $gainPct = 0.35; // Published: +35% yield with lime
            $gainKg = $current * $gainPct;
            $interventions[] = [
                'action'          => 'Apply ' . $limeKgHa . ' kg/ha agricultural lime',
                'cost_rwf'        => $limeKgHa * 180,
                'yield_gain_pct'  => round($gainPct * 100, 1),
                'yield_gain_kg'   => round($gainKg),
                'roi_months'      => 8,
                'priority'        => 'critical',
            ];
        }

        // 3. Irrigation
        if ($irrig < 30) {
            $gainPct = 0.18;
            $gainKg = $current * $gainPct;
            $interventions[] = [
                'action'          => 'Extend irrigation coverage by 20%',
                'cost_rwf'        => 250000,
                'yield_gain_pct'  => round($gainPct * 100, 1),
                'yield_gain_kg'   => round($gainKg),
                'roi_months'      => 18,
                'priority'        => 'medium',
            ];
        }

        // 4. Organic matter
        if ($fert < 60 && $irrig < 20) {
            $interventions[] = [
                'action'          => 'Apply 5 t/ha compost / manure',
                'cost_rwf'        => 120000,
                'yield_gain_pct'  => 12.0,
                'yield_gain_kg'   => round($current * 0.12),
                'roi_months'      => 6,
                'priority'        => 'medium',
            ];
        }

        return $interventions;
    }

    private function totalGainEstimate(array $interventions, float $area, string $crop): float {
        $gainKg = 0;
        foreach ($interventions as $i) $gainKg += $i['yield_gain_kg'] * $area;
        return $this->estimateRevenue($crop, $gainKg);
    }

    private function estimateRevenue(string $crop, float $kg): float {
        // Published market prices (RWF/kg)
        $priceRwf = [
            'Maize'         => 350,
            'Beans'         => 700,
            'Rice'          => 850,
            'Irish Potato'  => 320,
            'Sweet Potato'  => 180,
            'Cassava'       => 250,
            'Banana'        => 200,
            'Coffee'        => 1200,
            'Tea'           => 450,
            'Tomato'        => 500,
            'Onion'         => 600,
            'Cabbage'       => 400,
        ][$crop] ?? 300;
        return round($kg * $priceRwf);
    }

    private function encodeDistrict(string $d): int {
        $map = array_flip(array_keys(\App\Services\NisrDataService::class ? [
            'Bugesera'=>0,'Burera'=>1,'Gakenke'=>2,'Gasabo'=>3,'Gatsibo'=>4,
            'Gicumbi'=>5,'Gisagara'=>6,'Huye'=>7,'Kamonyi'=>8,'Karongi'=>9,
            'Kayonza'=>10,'Kicukiro'=>11,'Kirehe'=>12,'Muhanga'=>13,'Musanze'=>14,
            'Ngoma'=>15,'Ngororero'=>16,'Nyabihu'=>17,'Nyagatare'=>18,'Nyamagabe'=>19,
            'Nyamasheke'=>20,'Nyanza'=>21,'Nyarugenge'=>22,'Nyaruguru'=>23,'Rubavu'=>24,
            'Ruhango'=>25,'Rulindo'=>26,'Rusizi'=>27,'Rutsiro'=>28,'Rwamagana'=>29,
        ] : []));
        return $map[$d] ?? 15;
    }

    private function encodeCrop(string $c): int {
        $map = array_flip(['Maize','Beans','Rice','Irish Potato','Sweet Potato','Cassava','Banana','Coffee','Tea','Tomato','Onion','Cabbage']);
        return $map[$c] ?? 0;
    }
}