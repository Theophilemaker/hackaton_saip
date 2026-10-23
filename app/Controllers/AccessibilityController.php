<?php
namespace App\Controllers;

use App\Core\Controller;

class AccessibilityController extends Controller {
    public function __construct() {
    if (empty($_SESSION['user_id'])) {
        $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/dashboard');
        header("Location: " . BASE_URL . "/auth/login?redirect=" . $redirect);
        exit;
    }
}

    public function index() {
        $viewFile = __DIR__ . '/../Views/legal/accessibility.php';

        if (file_exists($viewFile)) {
            $this->viewPublic('legal/accessibility', [
                'title' => 'Accessibility · SAIP',
                'name'  => $_SESSION['name'] ?? 'Guest',
            ]);
            return;
        }

        $this->renderFallback(
            'Accessibility Statement',
            '♿',
            'Our commitment to making SAIP usable for every Rwandan farmer, regardless of ability.',
            'accessibility'
        );
    }

    private function renderFallback($title, $icon, $desc, $slug) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . ' · SAIP</title>';
        echo '<style>body{background:#0b1120;color:#cbd5e1;font-family:Inter,sans-serif;padding:60px 40px;max-width:800px;margin:0 auto;line-height:1.7}h1{color:#10b981;font-size:28px;margin:0 0 16px;display:flex;align-items:center;gap:14px}.icon{font-size:44px}.box{margin-top:30px;padding:24px;background:rgba(30,41,59,.7);border:1px solid rgba(255,255,255,.08);border-radius:16px;color:#cbd5e1}a{color:#34d399;text-decoration:none}.back{display:inline-block;margin-top:30px;padding:12px 22px;background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.35);border-radius:10px;font-weight:700}</style>';
        echo '</head><body>';
        echo '<div class="icon">' . $icon . '</div>';
        echo '<h1>' . htmlspecialchars($title) . '</h1>';
        echo '<p style="color:#94a3b8;font-size:15px">' . htmlspecialchars($desc) . '</p>';
        echo '<div class="box">Full content is being prepared. For questions, contact <a href="mailto:theophilen298@gmail.com">theophilen298@gmail.com</a>.</div>';
        echo '<a href="' . BASE_URL . '/" class="back">← Back to Home</a>';
        echo '</body></html>';
        exit;
    }
}