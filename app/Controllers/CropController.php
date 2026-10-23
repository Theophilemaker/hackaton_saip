<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Crop;
use App\Models\DiseaseDetector;

class CropController extends Controller {
    private $crop;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        $this->crop = new Crop();
    }

    /* ============ CROP CATALOG ============ */
    public function index() {
        $data = [
            'title' => 'Crop Intelligence',
            'name'  => $_SESSION['name'],
            'crops' => $this->crop->all(),
            'farms' => $this->crop->getFarms($_SESSION['user_id']),
            'active_crops' => $this->crop->getActiveCrops($_SESSION['user_id']),
            'yield_stats'  => $this->crop->getYieldStats($_SESSION['user_id']),
            'production'   => $this->crop->getProductionHistory($_SESSION['user_id']),
        ];
        $this->view('crops/index', $data);
    }

    /* ============ MY FARMS ============ */
    public function farms() {
        $data = [
            'title' => 'My Farms',
            'name'  => $_SESSION['name'],
            'farms' => $this->crop->getFarms($_SESSION['user_id']),
        ];
        $this->view('crops/farms', $data);
    }

    public function newFarm() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->crop->createFarm([
                'user_id'        => $_SESSION['user_id'],
                'name'           => trim($_POST['name']),
                'district_id'    => $_POST['district_id'] ?? null,
                'sector'         => $_POST['sector'] ?? null,
                'cell'           => $_POST['cell'] ?? null,
                'village'        => $_POST['village'] ?? null,
                'size_ha'        => $_POST['size_ha'] ?? null,
                'soil_type'      => $_POST['soil_type'] ?? 'loam',
                'irrigation_type'=> $_POST['irrigation_type'] ?? 'none',
                'lat'            => $_POST['lat'] ?? null,
                'lng'            => $_POST['lng'] ?? null,
            ]);
            header("Location: " . BASE_URL . "/crops/farms");
            exit;
        }
        $this->view('crops/new_farm', ['title'=>'Add Farm','name'=>$_SESSION['name']]);
    }

    /* ============ PLANT A CROP ============ */
    public function plant() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $crop = $this->crop->find($_POST['crop_id']);
            $this->crop->plantCrop([
                'user_id'     => $_SESSION['user_id'],
                'farm_id'     => $_POST['farm_id'],
                'crop_id'     => $_POST['crop_id'],
                'season'      => $_POST['season'] ?? 'A',
                'year'        => $_POST['year'] ?? date('Y'),
                'planted_date'=> $_POST['planted_date'],
                'area_ha'     => $_POST['area_ha'] ?? null,
                'quantity_planted_kg' => $_POST['quantity_planted_kg'] ?? null,
                'growing_days'=> $crop['growing_days'] ?? 90,
                'notes'       => $_POST['notes'] ?? null,
            ]);
            header("Location: " . BASE_URL . "/crops");
            exit;
        }
        $data = [
            'title' => 'Plant a Crop',
            'name'  => $_SESSION['name'],
            'crops' => $this->crop->all(),
            'farms' => $this->crop->getFarms($_SESSION['user_id']),
        ];
        $this->view('crops/plant', $data);
    }

    /* ============ FARM CROP DETAIL ============ */
    /* ═══════════════════════════════════════════════════════════
   URL ACTION for /crops/view/{id}
   Signature MUST match Controller::view($view, $data = [])
   to satisfy PHP's LSP rules.
   ═══════════════════════════════════════════════════════════ */
public function view($view = null, $data = []) {
    // If called with a numeric ID from the URL → render detail page
    if (is_numeric($view)) {
        return $this->details((int)$view);
    }
    // If called with a string name → fall through to parent template renderer
    return parent::view($view, $data);
}

/* ═══════════════════════════════════════════════════════════
   Real detail handler — internal only
   ═══════════════════════════════════════════════════════════ */
public function details($id = null) {
    $id = (int)$id;
    if ($id <= 0) {
        header("Location: " . BASE_URL . "/crops");
        exit;
    }

    $farmCrop = $this->crop->getFarmCrop($id, $_SESSION['user_id']);
    if (!$farmCrop) {
        header("Location: " . BASE_URL . "/crops");
        exit;
    }

    $yield      = \App\Models\DiseaseDetector::predictYield($farmCrop);
    $fertilizer = \App\Models\DiseaseDetector::recommendFertilizer($farmCrop, null);

    $this->crop->saveYieldPrediction(
        $id,
        $yield['predicted_yield_kg'],
        $yield['confidence'],
        $yield['features'] ?? []
    );

    parent::view('crops/view', [
        'title'          => $farmCrop['crop_name'] . ' — ' . $farmCrop['farm_name'],
        'name'           => $_SESSION['name'] ?? 'User',
        'farm_crop'      => $farmCrop,
        'growth_logs'    => $this->crop->getGrowthLogs($id),
        'prediction'     => $yield,
        'fertilizer'     => $fertilizer,
        'price_forecast' => $this->crop->getPriceForecast($farmCrop['crop_id'], 14),
    ]);
}

    public function updateStage($id) {
        header('Content-Type: application/json');
        $stage = $_POST['stage'] ?? 'vegetative';
        $this->crop->updateStage($id, $stage, $_SESSION['user_id']);
        echo json_encode(['ok' => true]);
        exit;
    }

    /* ============ AI DISEASE DETECTION ============ */
    /* ═══════════════════════════════════════════════════════════
   DISEASE DETECTION — GET form + POST handler
   URL: /crops/detect
   ═══════════════════════════════════════════════════════════ */
public function detect() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            /* Validate upload */
            if (empty($_FILES['image']['tmp_name'])) {
                echo json_encode(['success' => false, 'error' => 'No image uploaded']);
                exit;
            }

            $up = \App\Core\Security::validateImage($_FILES['image'], 5);
            if (!$up['ok']) {
                echo json_encode(['success' => false, 'error' => $up['error']]);
                exit;
            }

            /* Save image */
            $dir = __DIR__ . '/../../public/uploads/detections/' . date('Y/m/');
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            $ext   = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'][$up['mime']] ?? 'jpg';
            $fname = 'det_' . bin2hex(random_bytes(10)) . '.' . $ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dir . $fname)) {
                echo json_encode(['success' => false, 'error' => 'Could not save upload']);
                exit;
            }

            $targetPath = $dir . $fname;
            $publicUrl  = BASE_URL . '/uploads/detections/' . date('Y/m/') . $fname;
            $cropId     = !empty($_POST['crop_id']) ? (int)$_POST['crop_id'] : null;

            /* Run detection */
            $result = \App\Models\DiseaseDetector::detect($targetPath, $cropId);

            /* Persist to crop_detections so it shows in history */
            try {
                $this->crop->recordDetection([
                    'user_id'        => (int)$_SESSION['user_id'],
                    'farm_crop_id'   => null,
                    'image_url'      => $publicUrl,
                    'disease_id'     => $result['disease_id'] ?? null,
                    'disease_name'   => $result['disease_name'] ?? 'Unknown',
                    'confidence'     => $result['confidence'] ?? 0,
                    'severity'       => $result['severity'] ?? 'medium',
                    'treatment'      => $result['treatment'] ?? '',
                    'recommendation' => $result['recommendation'] ?? '',
                ]);
            } catch (\Throwable $e) { /* non-fatal */ }

            /* Return enriched payload */
            echo json_encode([
                'success' => true,
                'result'  => array_merge($result, [
                    'image_url' => $publicUrl,
                ]),
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* GET — show the form + history */
    $crops      = $this->crop->all();
    $detections = [];
    try {
        $detections = $this->crop->getDetections((int)$_SESSION['user_id']);
    } catch (\Throwable $e) { $detections = []; }

    parent::view('crops/detect', [
        'title'      => 'Disease Detection',
        'name'       => $_SESSION['name'] ?? 'User',
        'crops'      => $crops,
        'detections' => $detections,
    ]);
}
    /* ============ AI AGRIPOT CHAT ============ */
      /* ============================================================
     *  AGRIBOT — Multilingual AI Chat
     * ============================================================ */
    public function agribot() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $msg = trim($_POST['message'] ?? '');
            if (empty($msg)) {
                echo json_encode(['error' => 'Empty message']);
                exit;
            }

            $lang = $_SESSION['lang'] ?? 'en';

            try {
                $bot    = new \App\Models\AgriBot();
                $convId = $bot->getOrCreateConversation((int)$_SESSION['user_id']);

                $bot->saveMessage($convId, 'user', $msg, $lang);
                $reply = $bot->respond($msg, $lang);
                $bot->saveMessage($convId, 'assistant', $reply, $lang);

                echo json_encode([
                    'success' => true,
                    'reply'   => $reply,
                    'lang'    => $lang,
                ]);
            } catch (\Throwable $e) {
                echo json_encode([
                    'success' => true,
                    'reply'   => "I'm having trouble connecting right now. Please try again. 🌱",
                ]);
            }
            exit;
        }

        // GET — show the chat page
        $history = [];
        try {
            $bot    = new \App\Models\AgriBot();
            $convId = $bot->getOrCreateConversation((int)$_SESSION['user_id']);
            $history = $bot->getHistory($convId);
        } catch (\Throwable $e) {
            $history = [];
        }

        $data = [
            'title'   => 'AgriBot Assistant',
            'name'    => $_SESSION['name'] ?? 'User',
            'history' => $history,
        ];

        $viewFile = __DIR__ . '/../Views/crops/agribot.php';
        if (file_exists($viewFile)) {
            parent::view('crops/agribot', $data);
        } else {
            // Inline fallback if view missing
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html><head><title>AgriBot</title></head><body style="background:#081C15;color:#fff;padding:40px;font-family:Arial;">';
            echo '<h1>AgriBot works!</h1><p>Create the view file <code>app/Views/crops/agribot.php</code>.</p>';
            echo '</body></html>';
        }
    }
        public function detectPest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            if (empty($_FILES['image']['name'])) {
                echo json_encode(['error' => 'No image uploaded']); exit;
            }

            $dir = __DIR__ . '/../../public/uploads/pests/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            $name = uniqid('pest_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $target = $dir . $name;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                echo json_encode(['error' => 'Upload failed']); exit;
            }

            $result = \App\Models\PestDetector::detect($target, $_POST['crop_id'] ?? null);

            echo json_encode([
                'success' => true,
                'result'  => $result,
                'image_url' => BASE_URL . '/uploads/pests/' . $name,
            ]);
            exit;
        }

        $data = [
            'title' => 'Pest Detection',
            'name'  => $_SESSION['name'],
            'crops' => $this->crop->all(),
        ];
        $this->view('crops/detect_pest', $data);
    }
}
?>