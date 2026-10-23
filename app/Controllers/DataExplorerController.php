<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\NisrDataService;

class DataExplorerController extends Controller {
    private $nisr;

    public function __construct() {
        if (empty($_SESSION['user_id'])) { header("Location: " . BASE_URL . "/auth/login"); exit(); }
        $this->nisr = new NisrDataService();
    }

    public function index() {
        $this->view('explorer/index', [
            'title' => 'NISR Data Explorer',
            'name'  => $_SESSION['name'] ?? 'User',
            'districts' => $this->nisr->getDistricts(),
            'national_averages' => $this->nisr->getNationalAverages(),
        ]);
    }

    public function apiChartData() {
        header('Content-Type: application/json; charset=utf-8');
        
        $type = $_GET['type'] ?? 'yields';
        $districts = $this->nisr->getDistricts();
        
        $labels = [];
        $data = [];

        foreach ($districts as $d) {
            $labels[] = $d['name'];
            if ($type === 'yields') {
                $data[] = $this->nisr->getAvgDistrictYield($d['name']);
            } elseif ($type === 'households') {
                $data[] = (int)($d['household_data']['total_agri_households'] ?? 0);
            } elseif ($type === 'fertilizer') {
                $data[] = (float)($this->nisr->getFertilizerUse($d['name'])['pct_using_inorganic'] ?? 0);
            }
        }

        echo json_encode(['labels' => $labels, 'data' => $data]);
        exit;
    }
}