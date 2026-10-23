<?php
namespace App\Controllers;

use App\Core\Controller;

class DeveloperController extends Controller {

    public function __construct() {
        // Require login for all developer routes
        if (empty($_SESSION['user_id'])) {
            $redirect = urlencode($_SERVER['REQUEST_URI']);
            header("Location: " . BASE_URL . "/auth/login?redirect=" . $redirect);
            exit;
        }
    }

    public function docs()      { $this->render('API Documentation', '📘', 'Complete REST API reference for SAIP services.'); }
    public function keys()      { $this->render('API Keys', '🔑', 'Manage your API keys and access tokens.'); }
    public function webhooks()  { $this->render('Webhooks', '🪝', 'Subscribe to real-time events from SAIP.'); }
    public function sdk()       { $this->render('SDKs & Libraries', '📦', 'Official SDKs for PHP, JavaScript, and Python.'); }
    public function status()    { $this->render('System Status', '🟢', 'Real-time status of all SAIP services.'); }

    private function render($title, $icon, $desc) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . ' · SAIP Dev</title>';
        echo '<style>body{background:#0b1120;color:#cbd5e1;font-family:Inter,sans-serif;padding:60px 40px;max-width:900px;margin:0 auto;line-height:1.7}';
        echo 'h1{color:#10b981;font-size:28px;margin:10px 0 16px;display:flex;align-items:center;gap:12px}';
        echo '.icon{font-size:44px}p{color:#94a3b8;font-size:14px}';
        echo '.box{margin-top:30px;padding:20px;background:rgba(16,185,129,.06);border-left:3px solid #10b981;border-radius:10px;color:#cbd5e1}';
        echo '.back{display:inline-block;margin-top:30px;padding:12px 22px;background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.35);border-radius:10px;color:#34d399;text-decoration:none;font-weight:700}</style>';
        echo '</head><body>';
        echo '<div class="icon">' . $icon . '</div>';
        echo '<h1>' . htmlspecialchars($title) . '</h1>';
        echo '<p>' . htmlspecialchars($desc) . '</p>';
        echo '<div class="box">🔐 You are logged in as <strong>' . htmlspecialchars($_SESSION['name'] ?? 'User') . '</strong>. Full access granted to this resource.</div>';
        echo '<a href="' . BASE_URL . '/dashboard" class="back">← Back to Dashboard</a>';
        echo '</body></html>';
        exit;
    }
}