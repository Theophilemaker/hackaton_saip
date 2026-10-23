<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Security;

class DemoAdminController extends Controller {

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
       LIST — /demoAdmin
       ═══════════════════════════════════════════════════════════ */
    public function index() {
        $videos = [];
        try {
            $this->db->query("
                SELECT v.*, u.first_name, u.last_name
                FROM demo_videos v
                LEFT JOIN users u ON u.id = v.created_by
                ORDER BY v.sort_order ASC, v.created_at DESC
            ");
            $videos = $this->db->resultSet() ?: [];
        } catch (\Throwable $e) {}

        $stats = ['total' => count($videos), 'active' => 0, 'featured' => 0, 'categories' => []];
        foreach ($videos as $v) {
            if (!empty($v['is_active']))   $stats['active']++;
            if (!empty($v['is_featured'])) $stats['featured']++;
            $stats['categories'][$v['category']] = ($stats['categories'][$v['category']] ?? 0) + 1;
        }

        parent::view('admin/demos/index', [
            'title'  => 'Demo Videos',
            'name'   => $_SESSION['name'] ?? 'Admin',
            'videos' => $videos,
            'stats'  => $stats,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       FORM — /demoAdmin/form  or  /demoAdmin/form/{id}
       ═══════════════════════════════════════════════════════════ */
    public function form($id = null) {
        $id = (int)$id;
        $video = null;

        if ($id > 0) {
            try {
                $this->db->query("SELECT * FROM demo_videos WHERE id = :id LIMIT 1");
                $this->db->bind(':id', $id);
                $video = $this->db->single();
            } catch (\Throwable $e) {}

            if (!$video) {
                $_SESSION['flash_error'] = 'Video not found.';
                header("Location: " . BASE_URL . "/demoAdmin");
                exit;
            }
        }

        /* Existing categories (for datalist autocomplete) */
        $categories = [];
        try {
            $this->db->query("SELECT DISTINCT category FROM demo_videos ORDER BY category");
            foreach (($this->db->resultSet() ?: []) as $r) $categories[] = $r['category'];
        } catch (\Throwable $e) {}
        if (empty($categories)) $categories = ['Demo', 'Features', 'Tutorials', 'Impact'];

        parent::view('admin/demos/form', [
            'title'      => $video ? ('Edit — ' . $video['title']) : 'Add Demo Video',
            'name'       => $_SESSION['name'] ?? 'Admin',
            'video'      => $video,
            'categories' => $categories,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       SAVE — POST /demoAdmin/save
       ═══════════════════════════════════════════════════════════ */
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/demoAdmin");
            exit;
        }

        try { Security::verifyCsrf(); } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            header("Location: " . BASE_URL . "/demoAdmin");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        /* ── Sanitize ── */
        $title       = trim($_POST['title'] ?? '');
        $category    = trim($_POST['category'] ?? 'Demo');
        $description = trim($_POST['description'] ?? '');
        $type        = $_POST['type'] ?? 'mp4';
        $src         = trim($_POST['src'] ?? '');
        $thumbUrl    = trim($_POST['existing_thumbnail'] ?? '');
        $duration    = trim($_POST['duration'] ?? '');
        $isFeatured  = !empty($_POST['is_featured']) ? 1 : 0;
        $isActive    = !empty($_POST['is_active'])   ? 1 : 0;
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);

        if (!in_array($type, ['mp4','youtube','vimeo'], true)) $type = 'mp4';

        /* ── Validate ── */
        if ($title === '' || mb_strlen($title) < 3) {
            $_SESSION['flash_error'] = 'Title is required (min 3 chars).';
            header("Location: " . BASE_URL . "/demoAdmin/form" . ($id ? "/$id" : ""));
            exit;
        }

        if ($src === '' && empty($_FILES['video_file']['name'])) {
            $_SESSION['flash_error'] = 'Provide a video URL or upload a file.';
            header("Location: " . BASE_URL . "/demoAdmin/form" . ($id ? "/$id" : ""));
            exit;
        }

        /* ── Handle video file upload (overrides URL if provided) ── */
        if (!empty($_FILES['video_file']['name']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $up = $this->handleVideoUpload($_FILES['video_file']);
            if ($up['success']) {
                /* Delete old file if replacing */
                if ($id > 0) {
                    try {
                        $this->db->query("SELECT src FROM demo_videos WHERE id = :id LIMIT 1");
                        $this->db->bind(':id', $id);
                        $old = $this->db->single();
                        if ($old && $old['src'] && str_starts_with($old['src'], BASE_URL)) {
                            $oldPath = __DIR__ . '/../../public' . parse_url($old['src'], PHP_URL_PATH);
                            if (is_file($oldPath)) @unlink($oldPath);
                        }
                    } catch (\Throwable $e) {}
                }
                $src = $up['url'];
                $type = 'mp4';
            } else {
                $_SESSION['flash_error'] = 'Video upload failed: ' . $up['error'];
                header("Location: " . BASE_URL . "/demoAdmin/form" . ($id ? "/$id" : ""));
                exit;
            }
        }

        /* ── Handle thumbnail upload ── */
        if (!empty($_FILES['thumbnail']['name']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $up = $this->handleImageUpload($_FILES['thumbnail']);
            if ($up['success']) {
                if ($thumbUrl) {
                    $oldPath = __DIR__ . '/../../public' . parse_url($thumbUrl, PHP_URL_PATH);
                    if (is_file($oldPath)) @unlink($oldPath);
                }
                $thumbUrl = $up['url'];
            } else {
                $_SESSION['flash_error'] = 'Thumbnail: ' . $up['error'];
                header("Location: " . BASE_URL . "/demoAdmin/form" . ($id ? "/$id" : ""));
                exit;
            }
        }
        if ($thumbUrl === '') $thumbUrl = null;

        /* ── Insert or Update ── */
        try {
            if ($id > 0) {
                $this->db->query("
                    UPDATE demo_videos SET
                        title = :t, category = :c, description = :d,
                        type = :ty, src = :s, thumbnail = :th, duration = :du,
                        is_featured = :f, is_active = :a, sort_order = :o
                    WHERE id = :id
                ");
                $this->db->bind(':id', $id);
            } else {
                $this->db->query("
                    INSERT INTO demo_videos
                        (title, category, description, type, src, thumbnail, duration,
                         is_featured, is_active, sort_order, created_by)
                    VALUES
                        (:t, :c, :d, :ty, :s, :th, :du, :f, :a, :o, :uid)
                ");
                $this->db->bind(':uid', (int)$_SESSION['user_id']);
            }

            $this->db->bind(':t',  $title);
            $this->db->bind(':c',  $category);
            $this->db->bind(':d',  $description ?: null);
            $this->db->bind(':ty', $type);
            $this->db->bind(':s',  $src);
            $this->db->bind(':th', $thumbUrl);
            $this->db->bind(':du', $duration ?: null);
            $this->db->bind(':f',  $isFeatured);
            $this->db->bind(':a',  $isActive);
            $this->db->bind(':o',  $sortOrder);
            $this->db->execute();

            /* If featured, unfeature others */
            if ($isFeatured && $id > 0) {
                $this->db->query("UPDATE demo_videos SET is_featured = 0 WHERE id != :id");
                $this->db->bind(':id', $id);
                $this->db->execute();
            }

            $_SESSION['flash_success'] = $id > 0 ? 'Video updated.' : 'Video added.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Save failed: ' . $e->getMessage();
        }

        header("Location: " . BASE_URL . "/demoAdmin");
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       DELETE — /demoAdmin/delete/{id}
       ═══════════════════════════════════════════════════════════ */
    public function delete($id = null) {
        $id = (int)$id;
        if ($id <= 0) {
            header("Location: " . BASE_URL . "/demoAdmin");
            exit;
        }

        try {
            /* Remove files first */
            $this->db->query("SELECT src, thumbnail FROM demo_videos WHERE id = :id LIMIT 1");
            $this->db->bind(':id', $id);
            $row = $this->db->single();

            if ($row) {
                if (!empty($row['src']) && str_starts_with($row['src'], BASE_URL)) {
                    $p = __DIR__ . '/../../public' . parse_url($row['src'], PHP_URL_PATH);
                    if (is_file($p)) @unlink($p);
                }
                if (!empty($row['thumbnail']) && str_starts_with($row['thumbnail'], BASE_URL)) {
                    $p = __DIR__ . '/../../public' . parse_url($row['thumbnail'], PHP_URL_PATH);
                    if (is_file($p)) @unlink($p);
                }
            }

            $this->db->query("DELETE FROM demo_videos WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $_SESSION['flash_success'] = 'Video deleted.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Delete failed: ' . $e->getMessage();
        }

        header("Location: " . BASE_URL . "/demoAdmin");
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       TOGGLE — active / featured
       ═══════════════════════════════════════════════════════════ */
    public function toggle($id = null) {
        $id = (int)$id;
        try {
            $this->db->query("UPDATE demo_videos SET is_active = 1 - is_active WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();
            $_SESSION['flash_success'] = 'Status updated.';
        } catch (\Throwable $e) {}

        header("Location: " . BASE_URL . "/demoAdmin");
        exit;
    }

    public function feature($id = null) {
        $id = (int)$id;
        try {
            /* Unfeature all, then feature this one */
            $this->db->query("UPDATE demo_videos SET is_featured = 0");
            $this->db->execute();

            $this->db->query("UPDATE demo_videos SET is_featured = 1 WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $_SESSION['flash_success'] = 'Featured video updated.';
        } catch (\Throwable $e) {}

        header("Location: " . BASE_URL . "/demoAdmin");
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       UPLOAD HELPERS
       ═══════════════════════════════════════════════════════════ */
    private function handleVideoUpload(array $file): array {
        $allowed = [
            'video/mp4'       => 'mp4',
            'video/webm'      => 'webm',
            'video/quicktime' => 'mov',
        ];

        if ($file['size'] > 200 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Max 200 MB'];
        }

        $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : ($file['type'] ?? '');
        if (!isset($allowed[$mime])) {
            return ['success' => false, 'error' => 'Only MP4, WebM, MOV'];
        }

        $dir = __DIR__ . '/../../public/assets/videos/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $fname = 'demo_' . bin2hex(random_bytes(10)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . $fname)) {
            return ['success' => false, 'error' => 'Move failed'];
        }

        return ['success' => true, 'url' => BASE_URL . '/assets/videos/' . $fname];
    }

    private function handleImageUpload(array $file): array {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

        if ($file['size'] > 3 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Max 3 MB'];
        }

        $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : ($file['type'] ?? '');
        if (!isset($allowed[$mime])) {
            return ['success' => false, 'error' => 'Only JPG/PNG/WebP/GIF'];
        }

        $dir = __DIR__ . '/../../public/assets/videos/thumbs/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $fname = 'thumb_' . bin2hex(random_bytes(10)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . $fname)) {
            return ['success' => false, 'error' => 'Move failed'];
        }

        return ['success' => true, 'url' => BASE_URL . '/assets/videos/thumbs/' . $fname];
    }
}