<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Security;

class DiseaseAdminController extends Controller {

    private $db = null;

    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
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
       LIST — /admin/diseases
       ═══════════════════════════════════════════════════════════ */
    public function index() {
        $q      = trim($_GET['q'] ?? '');
        $cropId = (int)($_GET['crop'] ?? 0);
        $sev    = $_GET['severity'] ?? 'all';

        $sql   = "SELECT d.*, c.name AS crop_name, c.icon AS crop_icon
                  FROM diseases d
                  LEFT JOIN crops c ON c.id = d.crop_id
                  WHERE 1=1";
        $binds = [];

        if ($q !== '') {
            $sql .= " AND (d.name LIKE :q OR d.pathogen_type LIKE :q OR d.tags LIKE :q)";
            $binds[':q'] = '%' . $q . '%';
        }
        if ($cropId > 0) {
            $sql .= " AND d.crop_id = :cid";
            $binds[':cid'] = $cropId;
        }
        if (in_array($sev, ['low','medium','high','critical'], true)) {
            $sql .= " AND d.severity = :sev";
            $binds[':sev'] = $sev;
        }
        $sql .= " ORDER BY FIELD(d.severity,'critical','high','medium','low'), d.name";

        $diseases = [];
        try {
            $this->db->query($sql);
            foreach ($binds as $k => $v) $this->db->bind($k, $v);
            $diseases = $this->db->resultSet() ?: [];
        } catch (\Throwable $e) {}

        /* Crops for filter */
        $crops = [];
        try {
            $this->db->query("SELECT id, name, icon FROM crops WHERE is_active = 1 ORDER BY name");
            $crops = $this->db->resultSet() ?: [];
        } catch (\Throwable $e) {}

        /* KPIs */
        $totals = ['all'=>0,'active'=>0,'critical'=>0,'with_images'=>0];
        try {
            $this->db->query("SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN is_active=1 THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN severity='critical' THEN 1 ELSE 0 END) AS critical,
                SUM(CASE WHEN image_url IS NOT NULL THEN 1 ELSE 0 END) AS with_images
                FROM diseases");
            $r = $this->db->single();
            if ($r) {
                $totals['all']         = (int)$r['total'];
                $totals['active']      = (int)$r['active'];
                $totals['critical']    = (int)$r['critical'];
                $totals['with_images'] = (int)$r['with_images'];
            }
        } catch (\Throwable $e) {}

        parent::view('admin/diseases/index', [
            'title'    => 'Disease Library',
            'name'     => $_SESSION['name'] ?? 'Admin',
            'diseases' => $diseases,
            'crops'    => $crops,
            'totals'   => $totals,
            'filters'  => ['q'=>$q, 'crop'=>$cropId, 'severity'=>$sev],
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       FORM — create / edit
       /admin/diseases/form          → create
       /admin/diseases/form/{id}     → edit
       ═══════════════════════════════════════════════════════════ */
    public function form($id = null) {
        $id = (int)$id;
        $disease = null;

        if ($id > 0) {
            try {
                $this->db->query("SELECT * FROM diseases WHERE id = :id LIMIT 1");
                $this->db->bind(':id', $id);
                $disease = $this->db->single();
            } catch (\Throwable $e) {}
            if (!$disease) {
                $_SESSION['flash_error'] = 'Disease not found.';
                header("Location: " . BASE_URL . "/admin/diseases");
                exit;
            }
        }

        $crops = [];
        try {
            $this->db->query("SELECT id, name, icon FROM crops WHERE is_active = 1 ORDER BY name");
            $crops = $this->db->resultSet() ?: [];
        } catch (\Throwable $e) {}

        parent::view('admin/diseases/form', [
            'title'   => $disease ? 'Edit — ' . $disease['name'] : 'Add Disease',
            'name'    => $_SESSION['name'] ?? 'Admin',
            'disease' => $disease,
            'crops'   => $crops,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       SAVE — POST (create or update)
       ═══════════════════════════════════════════════════════════ */
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/diseases");
            exit;
        }

        try { Security::verifyCsrf(); } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            header("Location: " . BASE_URL . "/admin/diseases");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        $data = [
            'crop_id'       => !empty($_POST['crop_id']) ? (int)$_POST['crop_id'] : null,
            'name'          => trim($_POST['name'] ?? ''),
            'pathogen_type' => trim($_POST['pathogen_type'] ?? ''),
            'description'   => trim($_POST['description'] ?? ''),
            'symptoms'      => trim($_POST['symptoms'] ?? ''),
            'treatment'     => trim($_POST['treatment'] ?? ''),
            'prevention'    => trim($_POST['prevention'] ?? ''),
            'severity'      => in_array($_POST['severity'] ?? '', ['low','medium','high','critical'], true)
                                 ? $_POST['severity'] : 'medium',
            'tags'          => trim($_POST['tags'] ?? ''),
            'is_active'     => !empty($_POST['is_active']) ? 1 : 0,
        ];

        if ($data['name'] === '' || mb_strlen($data['name']) < 3) {
            $_SESSION['flash_error'] = 'Disease name is required (min 3 chars).';
            header("Location: " . BASE_URL . "/admin/diseases/form" . ($id ? "/$id" : ""));
            exit;
        }

        /* ── Hero image upload ── */
        $heroPath = $_POST['existing_image'] ?? null;
        if (!empty($_FILES['image']['name'])) {
            $up = $this->handleImage($_FILES['image'], 'hero');
            if ($up['success']) {
                if ($heroPath) $this->deleteFile($heroPath);
                $heroPath = $up['url'];
            } else {
                $_SESSION['flash_error'] = 'Hero image: ' . $up['error'];
                header("Location: " . BASE_URL . "/admin/diseases/form" . ($id ? "/$id" : ""));
                exit;
            }
        }
        $data['image_url'] = $heroPath;

        /* ── Gallery uploads (multiple) ── */
        $gallery = [];
        if (!empty($_POST['existing_gallery'])) {
            $gallery = json_decode($_POST['existing_gallery'], true) ?: [];
        }
        if (!empty($_FILES['gallery']['name'][0])) {
            $files = $_FILES['gallery'];
            $count = min(count($files['name']), 6); // max 6 gallery images
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                $single = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
                $up = $this->handleImage($single, 'gallery');
                if ($up['success']) $gallery[] = $up['url'];
            }
        }

        /* Handle gallery removals */
        if (!empty($_POST['remove_gallery'])) {
            $remove = json_decode($_POST['remove_gallery'], true) ?: [];
            foreach ($remove as $url) {
                $this->deleteFile($url);
                $gallery = array_values(array_filter($gallery, fn($g) => $g !== $url));
            }
        }
        $data['gallery'] = !empty($gallery) ? json_encode($gallery) : null;

        /* ── Insert or Update ── */
        try {
            if ($id > 0) {
                $this->db->query("UPDATE diseases SET
                    crop_id = :cid, name = :name, pathogen_type = :pt,
                    description = :desc, symptoms = :sym, treatment = :tr,
                    prevention = :prev, severity = :sev, tags = :tags,
                    image_url = :img, gallery = :gal, is_active = :active
                    WHERE id = :id");
                $this->db->bind(':id', $id);
            } else {
                $this->db->query("INSERT INTO diseases
                    (crop_id, name, pathogen_type, description, symptoms, treatment,
                     prevention, severity, tags, image_url, gallery, is_active, created_by)
                    VALUES
                    (:cid, :name, :pt, :desc, :sym, :tr,
                     :prev, :sev, :tags, :img, :gal, :active, :uid)");
                $this->db->bind(':uid', (int)$_SESSION['user_id']);
            }

            $this->db->bind(':cid',    $data['crop_id']);
            $this->db->bind(':name',   $data['name']);
            $this->db->bind(':pt',     $data['pathogen_type'] ?: null);
            $this->db->bind(':desc',   $data['description'] ?: null);
            $this->db->bind(':sym',    $data['symptoms'] ?: null);
            $this->db->bind(':tr',     $data['treatment'] ?: null);
            $this->db->bind(':prev',   $data['prevention'] ?: null);
            $this->db->bind(':sev',    $data['severity']);
            $this->db->bind(':tags',   $data['tags'] ?: null);
            $this->db->bind(':img',    $data['image_url']);
            $this->db->bind(':gal',    $data['gallery']);
            $this->db->bind(':active', $data['is_active']);
            $this->db->execute();

            $_SESSION['flash_success'] = $id > 0 ? 'Disease updated.' : 'Disease added.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Save failed: ' . $e->getMessage();
        }

        header("Location: " . BASE_URL . "/admin/diseases");
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       DELETE
       ═══════════════════════════════════════════════════════════ */
    public function delete($id = null) {
        $id = (int)$id;
        if ($id <= 0) { header("Location: " . BASE_URL . "/admin/diseases"); exit; }

        try {
            /* Remove image files first */
            $this->db->query("SELECT image_url, gallery FROM diseases WHERE id = :id LIMIT 1");
            $this->db->bind(':id', $id);
            $row = $this->db->single();
            if ($row) {
                if ($row['image_url']) $this->deleteFile($row['image_url']);
                if ($row['gallery']) {
                    foreach ((json_decode($row['gallery'], true) ?: []) as $g) $this->deleteFile($g);
                }
            }

            /* Soft delete if used in detections */
            $this->db->query("SELECT COUNT(*) AS c FROM crop_detections WHERE disease_id = :id");
            $this->db->bind(':id', $id);
            $uses = (int)($this->db->single()['c'] ?? 0);

            if ($uses > 0) {
                $this->db->query("UPDATE diseases SET is_active = 0 WHERE id = :id");
                $this->db->bind(':id', $id);
                $this->db->execute();
                $_SESSION['flash_success'] = "Disease used in {$uses} detection(s) — deactivated instead of deleted.";
            } else {
                $this->db->query("DELETE FROM diseases WHERE id = :id");
                $this->db->bind(':id', $id);
                $this->db->execute();
                $_SESSION['flash_success'] = 'Disease deleted.';
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Delete failed: ' . $e->getMessage();
        }

        header("Location: " . BASE_URL . "/admin/diseases");
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       TOGGLE ACTIVE
       ═══════════════════════════════════════════════════════════ */
    public function toggle($id = null) {
        $id = (int)$id;
        try {
            $this->db->query("UPDATE diseases SET is_active = 1 - is_active WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();
            $_SESSION['flash_success'] = 'Status updated.';
        } catch (\Throwable $e) {}
        header("Location: " . BASE_URL . "/admin/diseases");
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       HELPERS
       ═══════════════════════════════════════════════════════════ */

    private function handleImage(array $file, string $kind = 'hero'): array {
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];
        $maxMB = $kind === 'hero' ? 4 : 2;

        if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) {
            return ['success'=>false, 'error'=>'Upload error code ' . $file['error']];
        }
        if ($file['size'] > $maxMB * 1024 * 1024) {
            return ['success'=>false, 'error'=>"Max {$maxMB} MB"];
        }

        $mime = function_exists('mime_content_type')
              ? mime_content_type($file['tmp_name'])
              : ($file['type'] ?? '');

        if (!isset($allowed[$mime])) {
            return ['success'=>false, 'error'=>'Only JPG / PNG / WebP / GIF'];
        }

        $dir = __DIR__ . '/../../public/uploads/diseases/' . date('Y/m/');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $fname = ($kind === 'hero' ? 'hero_' : 'ref_') . bin2hex(random_bytes(10)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . $fname)) {
            return ['success'=>false, 'error'=>'Move failed'];
        }
        return ['success'=>true, 'url'=>BASE_URL . '/uploads/diseases/' . date('Y/m/') . $fname];
    }

    private function deleteFile(string $url): void {
        $path = __DIR__ . '/../../public' . parse_url($url, PHP_URL_PATH);
        if (is_file($path)) @unlink($path);
    }
}