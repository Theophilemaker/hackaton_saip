<?php
namespace App\Controllers;

use App\Core\Controller;

class StorageController extends Controller {

    private $db = null;

    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        try {
            $this->db = new \App\Core\Database();
        } catch (\Throwable $e) {
            $this->db = null;
        }
    }

    /* ═══════════════════════════════════════════════════════
       DASHBOARD — list warehouses + expiring bins
       URL: /storage
       ═══════════════════════════════════════════════════════ */
    public function index() {
        $data = [
            'title'      => 'Storage Management',
            'name'       => $_SESSION['name'] ?? 'User',
            'warehouses' => $this->getWarehouses(),
            'expiring'   => $this->getExpiringBins(),
        ];
        parent::view('storage/index', $data);
    }

    /* ═══════════════════════════════════════════════════════
       CREATE WAREHOUSE — GET form + POST handler
       URL: /storage/createWarehouse
       ═══════════════════════════════════════════════════════ */
    public function createWarehouse() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newId = $this->insertWarehouse($_POST);
            if ($newId > 0) {
                $_SESSION['flash_success'] = 'Warehouse created successfully.';
                header("Location: " . BASE_URL . "/storage/show/" . $newId);
                exit;
            }
            $_SESSION['flash_error'] = 'Failed to create warehouse.';
            header("Location: " . BASE_URL . "/storage");
            exit;
        }

        parent::view('storage/create', [
            'title' => 'New Warehouse',
            'name'  => $_SESSION['name'] ?? 'User',
        ]);
    }

    /* ═══════════════════════════════════════════════════════
       PUBLIC ACTION — URL: /storage/view/{id}
       Alias for show(). Signature MUST match the parent:
       Controller::view($view, $data = [])
       ═══════════════════════════════════════════════════════ */
    public function view($view = null, $data = []) {
        $this->show($view);
    }

    /* ═══════════════════════════════════════════════════════
       CANONICAL SHOW — URL: /storage/show/{id}
       ═══════════════════════════════════════════════════════ */
    public function show($id = null) {
        $id = (int)$id;
        if ($id <= 0) {
            header("Location: " . BASE_URL . "/storage");
            exit;
        }

        $warehouse = $this->getWarehouse($id);

        if (!$warehouse) {
            $_SESSION['flash_error'] = 'Warehouse not found.';
            header("Location: " . BASE_URL . "/storage");
            exit;
        }

        parent::view('storage/view', [
            'title'     => $warehouse['name'],
            'name'      => $_SESSION['name'] ?? 'User',
            'warehouse' => $warehouse,
            'bins'      => $this->getBins($id),
        ]);
    }

    /* ═══════════════════════════════════════════════════════
       ADD BIN — POST → redirect back to show
       URL: /storage/addBin/{warehouseId}
       ═══════════════════════════════════════════════════════ */
    public function addBin($warehouseId = null) {
        $warehouseId = (int)$warehouseId;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $warehouseId > 0 && $this->db) {
            try {
                $this->db->query("
                    INSERT INTO storage_bins
                        (warehouse_id, bin_code, crop_id, quantity_kg, moisture_pct,
                         temperature_c, entry_date, expiry_date)
                    VALUES (:w, :bc, :c, :q, :m, :t, :e, :ex)
                ");
                $this->db->bind(':w',  $warehouseId);
                $this->db->bind(':bc', trim($_POST['bin_code'] ?? 'BIN-' . time()));
                $this->db->bind(':c',  !empty($_POST['crop_id']) ? (int)$_POST['crop_id'] : null);
                $this->db->bind(':q',  (float)($_POST['quantity_kg'] ?? 0));
                $this->db->bind(':m',  ($_POST['moisture_pct']  ?? '') !== '' ? (float)$_POST['moisture_pct']  : null);
                $this->db->bind(':t',  ($_POST['temperature_c'] ?? '') !== '' ? (float)$_POST['temperature_c'] : null);
                $this->db->bind(':e',  $_POST['entry_date'] ?? date('Y-m-d'));
                $this->db->bind(':ex', !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null);
                $this->db->execute();

                $_SESSION['flash_success'] = 'Bin added.';
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'Failed to add bin.';
            }
        }

        header("Location: " . BASE_URL . "/storage/show/" . $warehouseId);
        exit;
    }

    /* ═══════════════════════════════════════════════════════
       DELETE BIN
       URL: /storage/deleteBin/{binId}
       ═══════════════════════════════════════════════════════ */
    public function deleteBin($binId = null) {
        $binId = (int)$binId;
        $warehouseId = 0;

        if ($binId > 0 && $this->db) {
            try {
                $this->db->query("SELECT warehouse_id FROM storage_bins WHERE id = :id LIMIT 1");
                $this->db->bind(':id', $binId);
                $row = $this->db->single();
                if ($row) $warehouseId = (int)$row['warehouse_id'];

                $this->db->query("DELETE FROM storage_bins WHERE id = :id");
                $this->db->bind(':id', $binId);
                $this->db->execute();

                $_SESSION['flash_success'] = 'Bin deleted.';
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'Failed to delete bin.';
            }
        }

        header("Location: " . ($warehouseId > 0
            ? BASE_URL . "/storage/show/" . $warehouseId
            : BASE_URL . "/storage"));
        exit;
    }

    /* ═══════════════════════════════════════════════════════
       AJAX ENDPOINTS
       ═══════════════════════════════════════════════════════ */
    public function updateBin($binId = null) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    public function iotPing() {
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    /* ═══════════════════════════════════════════════════════
       DB HELPERS
       ═══════════════════════════════════════════════════════ */

    private function insertWarehouse(array $post): int {
        if (!$this->db) return 0;
        try {
            $this->db->query("
                INSERT INTO warehouses
                    (uuid, name, owner_id, district_id, address, capacity_kg,
                     warehouse_type, has_ventilation, has_cooling)
                VALUES (:u, :n, :o, :d, :a, :c, :t, :hv, :hc)
            ");
            $this->db->bind(':u',  uniqid('wh_', true));
            $this->db->bind(':n',  trim($post['name'] ?? 'New Warehouse'));
            $this->db->bind(':o',  (int)$_SESSION['user_id']);
            $this->db->bind(':d',  !empty($post['district_id']) ? (int)$post['district_id'] : null);
            $this->db->bind(':a',  trim($post['address'] ?? ''));
            $this->db->bind(':c',  (float)($post['capacity_kg'] ?? 0));
            $this->db->bind(':t',  $post['warehouse_type'] ?? 'dry');
            $this->db->bind(':hv', !empty($post['has_ventilation']) ? 1 : 0);
            $this->db->bind(':hc', !empty($post['has_cooling'])     ? 1 : 0);
            $this->db->execute();

            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getWarehouses(): array {
        if (!$this->db) return [];
        try {
            $this->db->query("
                SELECT w.*, d.name AS district_name,
                       (SELECT COUNT(*) FROM storage_bins WHERE warehouse_id = w.id) AS bin_count,
                       (SELECT COALESCE(SUM(quantity_kg),0) FROM storage_bins WHERE warehouse_id = w.id) AS stored_kg
                FROM warehouses w
                LEFT JOIN districts d ON d.id = w.district_id
                ORDER BY w.name
            ");
            return $this->db->resultSet() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getWarehouse(int $id): ?array {
        if (!$this->db) return null;
        try {
            $this->db->query("
                SELECT w.*, d.name AS district_name
                FROM warehouses w
                LEFT JOIN districts d ON d.id = w.district_id
                WHERE w.id = :id LIMIT 1
            ");
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getBins(int $warehouseId): array {
        if (!$this->db) return [];
        try {
            $this->db->query("
                SELECT b.*, c.name AS crop_name, c.icon AS crop_icon
                FROM storage_bins b
                LEFT JOIN crops c ON c.id = b.crop_id
                WHERE b.warehouse_id = :w
                ORDER BY b.bin_code
            ");
            $this->db->bind(':w', $warehouseId);
            return $this->db->resultSet() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getExpiringBins(): array {
        if (!$this->db) return [];
        try {
            $this->db->query("
                SELECT b.*, w.name AS warehouse_name, c.name AS crop_name, c.icon
                FROM storage_bins b
                JOIN warehouses w ON w.id = b.warehouse_id
                LEFT JOIN crops c ON c.id = b.crop_id
                WHERE b.expiry_date IS NOT NULL
                  AND b.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                  AND b.expiry_date >= CURDATE()
                ORDER BY b.expiry_date ASC
                LIMIT 20
            ");
            return $this->db->resultSet() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}