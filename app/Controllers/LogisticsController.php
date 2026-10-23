<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Logistics;

class LogisticsController extends Controller {

    private $logistics;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        $this->logistics = new Logistics();
    }

    /* ═══════════════ MAIN DASHBOARD ═══════════════ */
    public function index() {
        $userId = (int)$_SESSION['user_id'];

        $stats    = $this->logistics->getDashboardStats($userId);
        $requests = $this->logistics->getRecentRequests($userId, 15);
        $fleet    = $this->logistics->getFleet($userId);

        foreach ($requests as &$r) {
            $r['tracking'] = $this->logistics->getTracking((int)$r['id']);
        }
        unset($r);

        $this->view('logistics/index', [
            'title'    => 'Logistics',
            'name'     => $_SESSION['name'] ?? 'User',
            'stats'    => $stats,
            'requests' => $requests,
            'fleet'    => $fleet,
            'district' => current_district(),
        ]);
    }

    /* ═══════════════ LIVE TRACKING VIEW ═══════════════ */
    public function tracking() {
        $userId = (int)$_SESSION['user_id'];

        $stats    = $this->logistics->getDashboardStats($userId);
        $requests = $this->logistics->getRecentRequests($userId, 20);
        $fleet    = $this->logistics->getFleet($userId);

        foreach ($requests as &$r) {
            $r['tracking'] = $this->logistics->getTracking((int)$r['id']);
        }
        unset($r);

        $this->view('logistics/tracking', [
            'title'    => 'Live Tracking',
            'name'     => $_SESSION['name'] ?? 'User',
            'stats'    => $stats,
            'requests' => $requests,
            'fleet'    => $fleet,
            'district' => current_district(),
        ]);
    }

    /* ═══════════════ CREATE REQUEST ═══════════════ */
    public function create() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $userId = (int)$_SESSION['user_id'];

            $required = ['title', 'cargo_type', 'weight_kg', 'pickup_district', 'dropoff_district', 'pickup_date'];
            foreach ($required as $f) {
                if (empty($_POST[$f])) {
                    echo json_encode(['success' => false, 'error' => "Missing: {$f}"]);
                    exit;
                }
            }

            if (empty($_POST['offered_price_rwf'])) {
                $estimate = $this->logistics->estimateCost(
                    $_POST['pickup_district'],
                    $_POST['dropoff_district'],
                    (float)$_POST['weight_kg'],
                    !empty($_POST['requires_refrigeration'])
                );
                $_POST['offered_price_rwf'] = $estimate['estimated_total'] ?? 0;
            }

            $id = $this->logistics->createRequest($userId, $_POST);

            if (!$id) throw new \Exception('Failed to create request');

            echo json_encode([
                'success' => true,
                'id'      => $id,
                'message' => 'Request created successfully',
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ ESTIMATE COST ═══════════════ */
    public function estimate() {
        header('Content-Type: application/json; charset=utf-8');
        if (ob_get_level() > 0) @ob_clean();

        try {
            $from   = $_GET['from'] ?? '';
            $to     = $_GET['to'] ?? '';
            $kg     = (float)($_GET['kg'] ?? 0);
            $refrig = !empty($_GET['refrigeration']);

            if (!$from || !$to) {
                echo json_encode(['success' => false, 'error' => 'Missing from/to']);
                exit;
            }

            $result = $this->logistics->estimateCost($from, $to, $kg, $refrig);
            echo json_encode(['success' => true, 'estimate' => $result]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ REQUEST DETAIL ═══════════════ */
    public function viewRequest($id = 0) {
        $id = (int)$id;
        $request = $this->logistics->getRequest($id);

        if (!$request) {
            header("Location: " . BASE_URL . "/logistics");
            exit;
        }

        $tracking = $this->logistics->getTracking($id);

        $this->view('logistics/request', [
            'title'    => 'Request #' . $id,
            'name'     => $_SESSION['name'],
            'request'  => $request,
            'tracking' => $tracking,
        ]);
    }

    /* ═══════════════ UPDATE STATUS ═══════════════ */
    public function updateStatus($id = 0) {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $status = $_POST['status'] ?? '';
            $allowed = ['accepted', 'in_transit', 'delivered', 'cancelled'];

            if (!in_array($status, $allowed, true)) {
                echo json_encode(['success' => false, 'error' => 'Invalid status']);
                exit;
            }

            $this->logistics->updateStatus((int)$id, $status, (int)$_SESSION['user_id']);
            echo json_encode(['success' => true, 'status' => $status]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ ADD VEHICLE ═══════════════ */
    public function addVehicle() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (empty($_POST['plate_number'])) {
                echo json_encode(['success' => false, 'error' => 'Plate number required']);
                exit;
            }

            $id = $this->logistics->addVehicle((int)$_SESSION['user_id'], $_POST);

            if (!$id) throw new \Exception('Failed to add vehicle');

            echo json_encode(['success' => true, 'id' => $id]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /* ═══════════════ ADD TRACKING EVENT ═══════════════ */
    public function addEvent($id = 0) {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $type  = $_POST['event_type'] ?? 'checkpoint';
            $title = $_POST['title'] ?? 'Update';
            $desc  = $_POST['description'] ?? '';
            $lat   = isset($_POST['lat']) ? (float)$_POST['lat'] : null;
            $lng   = isset($_POST['lng']) ? (float)$_POST['lng'] : null;

            $ok = $this->logistics->addTracking(
                (int)$id, $type, $title, $desc, (int)$_SESSION['user_id'], $lat, $lng
            );

            echo json_encode(['success' => $ok]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}