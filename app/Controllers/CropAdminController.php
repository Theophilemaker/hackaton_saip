<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class CropAdminController extends Controller {

    private $db = null;

    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        // ⛔ ADMIN ONLY
        if ((int)($_SESSION['role_id'] ?? 0) !== 7) {
            http_response_code(403);
            die('Access denied — admin only.');
        }
        try {
            $this->db = new Database();
        } catch (\Throwable $e) {
            $this->db = null;
        }
    }

    /* ═══════════════════════════════════════════════════════════
       LIST — /admin/crops
       ═══════════════════════════════════════════════════════════ */
    public function index() {
        $q      = trim($_GET['q']     ?? '');
        $status = $_GET['status']     ?? 'all';
        $sort   = $_GET['sort']       ?? 'name';

        $sql    = "SELECT c.*,
                          (SELECT COUNT(*) FROM farm_crops WHERE crop_id = c.id) AS usage_count,
                          (SELECT COUNT(*) FROM diseases  WHERE crop_id = c.id) AS disease_count
                   FROM crops c
                   WHERE 1 = 1";
        $binds  = [];

        if ($q !== '') {
            $sql .= " AND (c.name LIKE :q OR c.scientific_name LIKE :q)";
            $binds[':q'] = '%' . $q . '%';
        }
        if ($status === 'active') {
            $sql .= " AND c.is_active = 1";
        } elseif ($status === 'inactive') {
            $sql .= " AND c.is_active = 0";
        }

        $orderMap = [
            'name'   => 'c.name ASC',
            'newest' => 'c.created_at DESC',
            'oldest' => 'c.created_at ASC',
            'usage'  => 'usage_count DESC',
        ];
        $sql .= " ORDER BY " . ($orderMap[$sort] ?? 'c.name ASC');

        $crops = [];
        try {
            $this->db->query($sql);
            foreach ($binds as $k => $v) $this->db->bind($k, $v);
            $crops = $this->db->resultSet() ?: [];
        } catch (\Throwable $e) {
            $crops = [];
        }

        /* KPI counts */
        $totals = ['all' => 0, 'active' => 0, 'inactive' => 0];
        try {
            $this->db->query("SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) AS inactive
                FROM crops");
            $row = $this->db->single();
            if ($row) {
                $totals['all']      = (int)$row['total'];
                $totals['active']   = (int)$row['active'];
                $totals['inactive'] = (int)$row['inactive'];
            }
        } catch (\Throwable $e) {}

        parent::view('admin/crops/index', [
            'title'    => 'Manage Crops',
            'name'     => $_SESSION['name'] ?? 'Admin',
            'crops'    => $crops,
            'totals'   => $totals,
            'filters'  => ['q' => $q, 'status' => $status, 'sort' => $sort],
            'flashes'  => $this->getFlashes(),
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       FORM — add + edit
       /admin/crops/form          → create
       /admin/crops/form/{id}     → edit
       ═══════════════════════════════════════════════════════════ */
    public function form($id = null) {
        $id   = (int)$id;
        $crop = null;

        if ($id > 0) {
            try {
                $this->db->query("SELECT * FROM crops WHERE id = :id LIMIT 1");
                $this->db->bind(':id', $id);
                $crop = $this->db->single();
            } catch (\Throwable $e) { $crop = null; }

            if (!$crop) {
                $_SESSION['flash_error'] = 'Crop not found.';
                header("Location: " . BASE_URL . "/admin/crops");
                exit;
            }
        }

        parent::view('admin/crops/form', [
            'title'  => $crop ? ('Edit — ' . $crop['name']) : 'Add Crop',
            'name'   => $_SESSION['name'] ?? 'Admin',
            'crop'   => $crop,
            'flashes'=> $this->getFlashes(),
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       SAVE — POST handler (create or update)
       ═══════════════════════════════════════════════════════════ */
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/crops");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        /* ── Sanitize ── */
        $data = [
            'name'             => trim($_POST['name'] ?? ''),
            'scientific_name'  => trim($_POST['scientific_name'] ?? ''),
            'category'         => trim($_POST['category'] ?? 'cereal'),
            'season'           => trim($_POST['season'] ?? 'A,B'),
            'growing_days'     => (int)($_POST['growing_days'] ?? 90),
            'optimal_ph_min'   => (float)($_POST['optimal_ph_min'] ?? 5.5),
            'optimal_ph_max'   => (float)($_POST['optimal_ph_max'] ?? 7.0),
            'optimal_temp_min' => (float)($_POST['optimal_temp_min'] ?? 15),
            'optimal_temp_max' => (float)($_POST['optimal_temp_max'] ?? 30),
            'description'      => trim($_POST['description'] ?? ''),
            'icon'             => trim($_POST['icon'] ?? '🌱'),
            'is_active'        => !empty($_POST['is_active']) ? 1 : 0,
        ];

        /* ── Validation ── */
        if ($data['name'] === '' || mb_strlen($data['name']) < 2) {
            $_SESSION['flash_error'] = 'Crop name is required (min 2 chars).';
            header("Location: " . BASE_URL . "/admin/crops/form" . ($id ? "/$id" : ""));
            exit;
        }

        /* ── Handle image upload ── */
        $imagePath = $_POST['existing_image'] ?? null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = $this->handleImageUpload($_FILES['image']);
            if ($upload['success']) {
                /* Delete old image if replacing */
                if ($imagePath && file_exists(__DIR__ . '/../../public' . parse_url($imagePath, PHP_URL_PATH))) {
                    @unlink(__DIR__ . '/../../public' . parse_url($imagePath, PHP_URL_PATH));
                }
                $imagePath = $upload['url'];
            } else {
                $_SESSION['flash_error'] = 'Image upload failed: ' . $upload['error'];
                header("Location: " . BASE_URL . "/admin/crops/form" . ($id ? "/$id" : ""));
                exit;
            }
        }
        $data['image_url'] = $imagePath;

        /* ── Insert or update ── */
        try {
            if ($id > 0) {
                $this->db->query("
                    UPDATE crops SET
                        name = :name,
                        scientific_name = :sci,
                        category = :cat,
                        season = :season,
                        growing_days = :gdays,
                        optimal_ph_min = :phmin,
                        optimal_ph_max = :phmax,
                        optimal_temp_min = :tmin,
                        optimal_temp_max = :tmax,
                        description = :desc,
                        icon = :icon,
                        image_url = :img,
                        is_active = :active
                    WHERE id = :id
                ");
                $this->db->bind(':id', $id);
            } else {
                $this->db->query("
                    INSERT INTO crops
                        (uuid, name, scientific_name, category, season, growing_days,
                         optimal_ph_min, optimal_ph_max, optimal_temp_min, optimal_temp_max,
                         description, icon, image_url, is_active)
                    VALUES
                        (:uuid, :name, :sci, :cat, :season, :gdays,
                         :phmin, :phmax, :tmin, :tmax,
                         :desc, :icon, :img, :active)
                ");
                $this->db->bind(':uuid', bin2hex(random_bytes(8)));
            }

            $this->db->bind(':name',   $data['name']);
            $this->db->bind(':sci',    $data['scientific_name'] ?: null);
            $this->db->bind(':cat',    $data['category']);
            $this->db->bind(':season', $data['season']);
            $this->db->bind(':gdays',  $data['growing_days']);
            $this->db->bind(':phmin',  $data['optimal_ph_min']);
            $this->db->bind(':phmax',  $data['optimal_ph_max']);
            $this->db->bind(':tmin',   $data['optimal_temp_min']);
            $this->db->bind(':tmax',   $data['optimal_temp_max']);
            $this->db->bind(':desc',   $data['description'] ?: null);
            $this->db->bind(':icon',   $data['icon'] ?: '🌱');
            $this->db->bind(':img',    $data['image_url']);
            $this->db->bind(':active', $data['is_active']);
            $this->db->execute();

            if ($id === 0) $id = (int)$this->db->lastInsertId();

            $this->logAudit($id, $id > 0 && $_POST['id'] ? 'update' : 'create', $data['name']);
            $_SESSION['flash_success'] = 'Crop saved successfully.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Save failed: ' . $e->getMessage();
        }

        header("Location: " . BASE_URL . "/admin/crops");
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       DELETE — single crop (soft delete if FK exists, hard if not)
       ═══════════════════════════════════════════════════════════ */
    public function delete($id = null) {
        $id = (int)$id;
        if ($id <= 0) {
            header("Location: " . BASE_URL . "/admin/crops");
            exit;
        }

        try {
            /* Check usage first */
            $this->db->query("
                SELECT
                    (SELECT COUNT(*) FROM farm_crops WHERE crop_id = :id) AS uses,
                    (SELECT COUNT(*) FROM diseases  WHERE crop_id = :id) AS diseases
            ");
            $this->db->bind(':id', $id);
            $row = $this->db->single();
            $uses = (int)($row['uses'] ?? 0);

            if ($uses > 0) {
                /* Soft delete — just deactivate */
                $this->db->query("UPDATE crops SET is_active = 0 WHERE id = :id");
                $this->db->bind(':id', $id);
                $this->db->execute();
                $_SESSION['flash_success'] = "Crop is used by {$uses} farm(s) — deactivated instead of deleted.";
            } else {
                $this->db->query("DELETE FROM crops WHERE id = :id");
                $this->db->bind(':id', $id);
                $this->db->execute();
                $_SESSION['flash_success'] = 'Crop deleted permanently.';
            }

            $this->logAudit($id, 'delete', '');
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Delete failed: ' . $e->getMessage();
        }

        header("Location: " . BASE_URL . "/admin/crops");
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       BULK ACTION — POST with selected IDs + action
       ═══════════════════════════════════════════════════════════ */
    public function bulk() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/crops");
            exit;
        }

        $ids    = array_filter(array_map('intval', (array)($_POST['ids'] ?? [])));
        $action = $_POST['action'] ?? '';

        if (empty($ids)) {
            $_SESSION['flash_error'] = 'No crops selected.';
            header("Location: " . BASE_URL . "/admin/crops");
            exit;
        }

        $in = implode(',', array_map('intval', $ids));

        try {
            if ($action === 'activate') {
                $this->db->query("UPDATE crops SET is_active = 1 WHERE id IN ($in)");
                $this->db->execute();
                $_SESSION['flash_success'] = count($ids) . ' crop(s) activated.';
            } elseif ($action === 'deactivate') {
                $this->db->query("UPDATE crops SET is_active = 0 WHERE id IN ($in)");
                $this->db->execute();
                $_SESSION['flash_success'] = count($ids) . ' crop(s) deactivated.';
            } elseif ($action === 'delete') {
                /* Only delete unused crops */
                $this->db->query("DELETE FROM crops WHERE id IN ($in)
                                  AND id NOT IN (SELECT DISTINCT crop_id FROM farm_crops)");
                $deleted = $this->db->rowCount();
                $_SESSION['flash_success'] = "$deleted crop(s) deleted. Skipped ones in use.";
            } else {
                $_SESSION['flash_error'] = 'Unknown action.';
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Bulk action failed: ' . $e->getMessage();
        }

        header("Location: " . BASE_URL . "/admin/crops");
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       UPLOADED DATA MANAGER — /admin/uploads
       ═══════════════════════════════════════════════════════════ */
    public function uploads() {
        $stats = [
            'detections'  => $this->dirStats(__DIR__ . '/../../public/uploads/detections'),
            'pests'       => $this->dirStats(__DIR__ . '/../../public/uploads/pests'),
            'chat'        => $this->dirStats(__DIR__ . '/../../public/uploads/chat'),
            'crops'       => $this->dirStats(__DIR__ . '/../../public/uploads/crops'),
            'avatars'     => $this->dirStats(__DIR__ . '/../../public/uploads/avatars'),
        ];

        parent::view('admin/uploads/index', [
            'title'   => 'Uploaded Data',
            'name'    => $_SESSION['name'] ?? 'Admin',
            'stats'   => $stats,
            'flashes' => $this->getFlashes(),
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       CLEAR UPLOADS — POST
       POST body: scope = detections|pests|chat|crops|avatars|all
       Also:      older_than = days (0 = all)
       ═══════════════════════════════════════════════════════════ */
    public function clearUploads() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/uploads");
            exit;
        }

        $scope = $_POST['scope'] ?? '';
        $days  = max(0, (int)($_POST['older_than'] ?? 0));
        $allowed = ['detections', 'pests', 'chat', 'crops', 'avatars'];

        $targets = ($scope === 'all') ? $allowed : [$scope];
        $totalDeleted = 0;
        $totalBytes   = 0;

        foreach ($targets as $dir) {
            if (!in_array($dir, $allowed, true)) continue;
            $path = __DIR__ . '/../../public/uploads/' . $dir;
            if (!is_dir($path)) continue;

            [$files, $bytes] = $this->purgeDirectory($path, $days);
            $totalDeleted += $files;
            $totalBytes   += $bytes;
        }

        /* Also clear DB references for detections if scope includes them */
        if (in_array('detections', $targets, true) || $scope === 'all') {
            try {
                if ($days > 0) {
                    $this->db->query("DELETE FROM crop_detections
                                      WHERE created_at < DATE_SUB(NOW(), INTERVAL :d DAY)");
                    $this->db->bind(':d', $days);
                    $this->db->execute();
                } else {
                    $this->db->query("DELETE FROM crop_detections");
                    $this->db->execute();
                }
            } catch (\Throwable $e) {}
        }

        $humanBytes = $this->humanBytes($totalBytes);
        $_SESSION['flash_success'] = "Cleared $totalDeleted file(s) · freed $humanBytes.";
        $this->logAudit(0, 'clear_uploads', "$scope / older_than=$days / $totalDeleted files / $humanBytes");

        header("Location: " . BASE_URL . "/admin/uploads");
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       HELPERS
       ═══════════════════════════════════════════════════════════ */

    private function handleImageUpload(array $file): array {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'image/gif'];
        $extMap  = [
            'image/jpeg'    => 'jpg',
            'image/png'     => 'png',
            'image/webp'    => 'webp',
            'image/svg+xml' => 'svg',
            'image/gif'     => 'gif',
        ];

        if ($file['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Max 2 MB'];
        }

        $mime = function_exists('mime_content_type')
            ? mime_content_type($file['tmp_name'])
            : ($file['type'] ?? '');

        if (!in_array($mime, $allowed, true)) {
            return ['success' => false, 'error' => 'Only JPG/PNG/WebP/SVG/GIF'];
        }

        $dir = __DIR__ . '/../../public/uploads/crops/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $name = 'crop_' . bin2hex(random_bytes(10)) . '.' . $extMap[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
            return ['success' => false, 'error' => 'Move failed'];
        }

        return ['success' => true, 'url' => BASE_URL . '/uploads/crops/' . $name];
    }

    private function dirStats(string $path): array {
        if (!is_dir($path)) return ['files' => 0, 'bytes' => 0, 'size' => '0 B'];

        $files = 0;
        $bytes = 0;
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $f) {
            if ($f->isFile()) {
                $files++;
                $bytes += $f->getSize();
            }
        }
        return ['files' => $files, 'bytes' => $bytes, 'size' => $this->humanBytes($bytes)];
    }

    private function purgeDirectory(string $path, int $days = 0): array {
        if (!is_dir($path)) return [0, 0];

        $cutoff = $days > 0 ? time() - ($days * 86400) : PHP_INT_MAX;
        $files = 0;
        $bytes = 0;

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $f) {
            if ($f->isFile()) {
                if ($f->getMTime() <= $cutoff) {
                    $bytes += $f->getSize();
                    @unlink($f->getPathname());
                    $files++;
                }
            } elseif ($f->isDir()) {
                @rmdir($f->getPathname()); // only removes empty dirs
            }
        }
        return [$files, $bytes];
    }

    private function humanBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    private function logAudit(int $targetId, string $action, string $detail): void {
        try {
            $this->db->query("INSERT INTO admin_audit_log
                (user_id, target_type, target_id, action, detail, created_at)
                VALUES (:u, 'crop', :tid, :a, :d, NOW())");
            $this->db->bind(':u',   (int)$_SESSION['user_id']);
            $this->db->bind(':tid', $targetId);
            $this->db->bind(':a',   $action);
            $this->db->bind(':d',   mb_substr($detail, 0, 500));
            $this->db->execute();
        } catch (\Throwable $e) { /* silent */ }
    }
}