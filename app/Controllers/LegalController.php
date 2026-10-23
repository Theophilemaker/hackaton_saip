<?php
namespace App\Controllers;

use App\Core\Controller;

class LegalController extends Controller {

    /* ═══════════════════════════════════════════════════════════
       CONSTRUCTOR — Require login for all legal pages
       ═══════════════════════════════════════════════════════════ */
    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/dashboard');
            header("Location: " . BASE_URL . "/auth/login?redirect=" . $redirect);
            exit;
        }
    }

    public function terms()         { $this->render('Terms & Conditions',      'terms'); }
    public function privacy()       { $this->render('Security & Privacy',      'privacy'); }
    public function cookies()       { $this->render('Cookie Policy',           'cookies'); }
    public function accessibility() { $this->render('Accessibility Statement', 'accessibility'); }

    private function render($title, $view) {
        $file = __DIR__ . '/../Views/legal/' . $view . '.php';

        if (!file_exists($file)) {
            http_response_code(404);
            die('Legal page not found: ' . htmlspecialchars($view));
        }

        parent::view('legal/' . $view, [
            'title' => $title,
            'name'  => $_SESSION['name'] ?? 'User',
        ]);
    }
}