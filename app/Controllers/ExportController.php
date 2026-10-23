<?php
namespace App\Controllers;

use App\Core\Controller;

class ExportController extends Controller {
    public function __construct() {
        if (empty($_SESSION['user_id'])) { header("Location: " . BASE_URL . "/auth/login"); exit(); }
    }

    public function index() {
        $this->view('export/index', [
            'title' => 'Export & Value Addition',
            'name'  => $_SESSION['name'] ?? 'User'
        ]);
    }

    public function match() {
        header('Content-Type: application/json; charset=utf-8');

        $crop = $_POST['crop'] ?? 'Coffee';
        
        // National vs International Prices (ITC Trade Map / FAOSTAT data)
        $data = [
            'Coffee' => [
                'local_price' => 1200,
                'export_price' => 5900,
                'market' => 'Germany',
                'cert' => 'RBS Organic Certification'
            ],
            'Avocado' => [
                'local_price' => 800,
                'export_price' => 2500,
                'market' => 'UAE',
                'cert' => 'Global G.A.P.'
            ],
            'Maize' => [
                'local_price' => 350,
                'export_price' => 550,
                'market' => 'Kenya',
                'cert' => 'EAC Phytosanitary'
            ]
        ];

        $info = $data[$crop] ?? $data['Coffee'];
        $gain = $info['export_price'] - $info['local_price'];
        $gain_pct = round(($gain / $info['local_price']) * 100);

        echo json_encode([
            'success' => true,
            'crop' => $crop,
            'local_price' => $info['local_price'],
            'export_price' => $info['export_price'],
            'target_market' => $info['market'],
            'requirement' => $info['cert'],
            'gain_pct' => $gain_pct
        ]);
        exit;
    }
}