<?php
namespace App\Controllers;

use App\Core\Controller;

class IrrigationController extends Controller {
    public function __construct() {
        if (empty($_SESSION['user_id'])) { header("Location: " . BASE_URL . "/auth/login"); exit(); }
    }

    public function index() {
        $this->view('irrigation/index', [
            'title' => 'Smart Irrigation',
            'name'  => $_SESSION['name'] ?? 'User'
        ]);
    }

    public function schedule() {
        header('Content-Type: application/json; charset=utf-8');
        
        $crop = $_POST['crop'] ?? 'Maize';
        $stage = $_POST['stage'] ?? 'vegetative';
        $area = (float)($_POST['area_ha'] ?? 1);

        // Crop Water Requirement (mm/day) based on FAO-56
        $water_needs = [
            'Maize' => ['seedling'=>3, 'vegetative'=>5, 'flowering'=>7, 'maturity'=>3],
            'Beans' => ['seedling'=>2, 'vegetative'=>4, 'flowering'=>6, 'maturity'=>2],
            'Rice'  => ['seedling'=>5, 'vegetative'=>8, 'flowering'=>10, 'maturity'=>5]
        ];
        
        $mm_per_day = $water_needs[$crop][$stage] ?? 4;
        
        // Check Weather API (simulated for hackathon)
        $rain_forecast = rand(0, 15); // mm of rain expected today
        
        $net_need = max(0, $mm_per_day - $rain_forecast);
        $liters_per_ha = $net_need * 10000; // 1mm = 10,000 liters/ha
        $total_liters = $liters_per_ha * $area;

        echo json_encode([
            'success' => true,
            'crop' => $crop,
            'stage' => ucfirst($stage),
            'mm_needed' => $mm_per_day,
            'rain_expected' => $rain_forecast,
            'irrigation_needed_mm' => $net_need,
            'total_liters' => $total_liters,
            'action' => $net_need > 0 ? "Irrigate {$net_need}mm today." : "Skip irrigation. Rain expected.",
            'tip' => "Only 12% of Rwandan land is irrigated. Smart scheduling saves water and boosts yield."
        ]);
        exit;
    }
}