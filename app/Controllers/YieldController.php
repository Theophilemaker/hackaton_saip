<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\NisrDataService;
use App\Models\YieldPredictor;

class YieldController extends Controller {

    private $nisr;
    private $predictor;

    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        $this->nisr      = new NisrDataService();
        $this->predictor = new YieldPredictor();
    }

    /* ═══════════════ MAIN DASHBOARD ═══════════════ */
    public function index() {
        $this->view('yield/index', [
            'title'       => 'Yield Predictor',
            'name'        => $_SESSION['name'] ?? 'User',
            'districts'   => array_keys($this->districtsMap()),
            'crops'       => $this->nisr->getCrops(),
            'national'    => $this->nisr->getNationalAverages(),
            'data_status' => $this->nisr->getDataStatus(),
        ]);
    }

    /* ═══════════════ PREDICT (AJAX) ═══════════════ */
    public function predict() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $input = [
                'district'        => trim($_POST['district'] ?? 'Musanze'),
                'crop'            => trim($_POST['crop'] ?? 'Maize'),
                'area_ha'         => (float)($_POST['area_ha'] ?? 1),
                'fertilizer_kg_ha'=> (float)($_POST['fertilizer_kg_ha'] ?? 45),
                'irrigation_pct'  => (float)($_POST['irrigation_pct'] ?? 12),
                'soil_ph'         => (float)($_POST['soil_ph'] ?? 5.8),
                'rainfall_mm'     => (float)($_POST['rainfall_mm'] ?? 900),
            ];

            $prediction = $this->predictor->predict($input);

            echo json_encode([
                'success'    => true,
                'prediction' => $prediction,
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            error_log("[Yield predict] " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error'   => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /* ═══════════════ DISTRICT MAP VIEW ═══════════════ */
    public function district() {
        $districts = [];
        $crop = $_GET['crop'] ?? 'Maize';

        foreach ($this->districtsMap() as $name => $coords) {
            $districts[] = [
                'name'         => $name,
                'lat'          => $coords[0],
                'lng'          => $coords[1],
                'yield_kg_ha'  => $this->nisr->getYield($name, $crop) ?: 0,
                'avg_yield'    => $this->nisr->getAvgDistrictYield($name),
                'households'   => $this->nisr->getHouseholdStats($name),
                'fertilizer'   => $this->nisr->getFertilizerUse($name),
            ];
        }

        // Sort descending by yield
        usort($districts, fn($a, $b) => $b['yield_kg_ha'] <=> $a['yield_kg_ha']);

        $this->view('yield/district', [
            'title'       => 'District Yield Comparison',
            'name'        => $_SESSION['name'] ?? 'User',
            'crop'        => $crop,
            'crops'       => $this->nisr->getCrops(),
            'districts'   => $districts,
            'national'    => $this->nisr->getNationalAverages(),
            'data_status' => $this->nisr->getDataStatus(),
        ]);
    }

    /* ═══════════════ DISTRICT INFO (AJAX) ═══════════════ */
    public function districtInfo() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        $name = trim($_GET['name'] ?? '');
        $crop = trim($_GET['crop'] ?? 'Maize');

        if ($name === '') {
            echo json_encode(['success' => false, 'error' => 'Missing district']);
            exit;
        }

        try {
            $data = [
                'district'    => $name,
                'crop'        => $crop,
                'yield'       => $this->nisr->getYield($name, $crop),
                'avg_yield'   => $this->nisr->getAvgDistrictYield($name),
                'households'  => $this->nisr->getHouseholdStats($name),
                'fertilizer'  => $this->nisr->getFertilizerUse($name),
                'national'    => $this->nisr->getNationalAverages(),
            ];
            echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ DATA STATUS ═══════════════ */
    public function dataStatus() {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'  => true,
            'datasets' => $this->nisr->getDataStatus(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ═══════════════ HELPERS ═══════════════ */
    private function districtsMap(): array {
        return [
            'Bugesera'=>[-2.2194,30.1244],'Burera'=>[-1.4667,29.9167],
            'Gakenke'=>[-1.7000,29.7833],'Gasabo'=>[-1.9167,30.1167],
            'Gatsibo'=>[-1.6000,30.4333],'Gicumbi'=>[-1.5833,30.1000],
            'Gisagara'=>[-2.4500,29.8333],'Huye'=>[-2.5967,29.7392],
            'Kamonyi'=>[-2.0000,29.9000],'Karongi'=>[-2.0500,29.3500],
            'Kayonza'=>[-1.8833,30.6167],'Kicukiro'=>[-1.9833,30.1000],
            'Kirehe'=>[-2.2833,30.7167],'Muhanga'=>[-2.0833,29.7500],
            'Musanze'=>[-1.4999,29.6349],'Ngoma'=>[-2.1500,30.5667],
            'Ngororero'=>[-1.8667,29.6000],'Nyabihu'=>[-1.6667,29.5000],
            'Nyagatare'=>[-1.2939,30.3275],'Nyamagabe'=>[-2.4667,29.4500],
            'Nyamasheke'=>[-2.3500,29.1333],'Nyanza'=>[-2.3500,29.7500],
            'Nyarugenge'=>[-1.9500,30.0500],'Nyaruguru'=>[-2.6167,29.5667],
            'Rubavu'=>[-1.6779,29.2579],'Ruhango'=>[-2.1500,29.7833],
            'Rulindo'=>[-1.7667,30.0667],'Rusizi'=>[-2.4833,29.0000],
            'Rutsiro'=>[-1.9333,29.3500],'Rwamagana'=>[-1.9500,30.4333],
        ];
    }
}