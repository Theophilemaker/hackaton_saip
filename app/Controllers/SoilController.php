<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\NisrDataService;

class SoilController extends Controller {
    private $nisr;

    public function __construct() {
        if (empty($_SESSION['user_id'])) { header("Location: " . BASE_URL . "/auth/login"); exit(); }
        $this->nisr = new NisrDataService();
    }

    public function index() {
        $this->view('soil/index', [
            'title' => 'Soil Health & Lime Advisor',
            'name'  => $_SESSION['name'] ?? 'User',
            'districts' => ['Musanze','Nyagatare','Kirehe','Huye','Rwamagana','Bugesera','Rubavu'],
            'crops' => $this->nisr->getCrops()
        ]);
    }

    public function analyze() {
        header('Content-Type: application/json; charset=utf-8');
        
        $ph = (float)($_POST['soil_ph'] ?? 5.0);
        $crop = $_POST['crop'] ?? 'Maize';
        $area = (float)($_POST['area_ha'] ?? 1);

        // NISR/RAB Data: 86% of Rwanda soils are acidic (< 5.5)
        $is_acidic = $ph < 5.5;
        $lime_needed = 0;
        $yield_gain_pct = 0;
        $cost_rwf = 0;

        if ($is_acidic) {
            // Rule: 250kg/ha if pH 5.0-5.5, 400kg/ha if pH < 5.0
            $lime_needed = $ph < 5.0 ? 400 : 250;
            $yield_gain_pct = 35; // RAB data shows +35% yield with lime
            $cost_rwf = $lime_needed * 180; // 180 RWF/kg
        }

        $total_lime = $lime_needed * $area;
        $total_cost = $cost_rwf * $area;
        
        // Crop base price (RWF/kg)
        $prices = ['Maize'=>350, 'Beans'=>700, 'Irish Potato'=>320];
        $price = $prices[$crop] ?? 300;
        
        // Estimated gain
        $est_base_yield = 2000; // kg/ha
        $gain_kg = ($est_base_yield * ($yield_gain_pct / 100)) * $area;
        $gain_rwf = $gain_kg * $price;
        $roi = $total_cost > 0 ? round($gain_rwf / $total_cost, 1) : 0;

        echo json_encode([
            'success' => true,
            'is_acidic' => $is_acidic,
            'current_ph' => $ph,
            'lime_per_ha' => $lime_needed,
            'total_lime_kg' => $total_lime,
            'cost_rwf' => $total_cost,
            'yield_gain_pct' => $yield_gain_pct,
            'estimated_gain_rwf' => $gain_rwf,
            'roi_multiplier' => $roi,
            'message' => $is_acidic ? "Your soil is too acidic. Apply agricultural lime to unlock +{$yield_gain_pct}% yield." : "Your soil pH is optimal for {$crop}."
        ]);
        exit;
    }
}