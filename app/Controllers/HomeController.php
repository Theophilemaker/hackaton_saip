<?php
namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {

    /* ═══════════════════════════════════════════════════════════
       HOMEPAGE — Always renders the public landing page
       URL: /home
       Works whether the user is logged in or not.
       ═══════════════════════════════════════════════════════════ */
    public function index() {
        /* Public landing — no auth redirect, no dashboard fallback */
        $this->viewPublic('landing', [
            'title' => 'SAIP · Smart Agriculture Intelligence Platform',
            'name'  => $_SESSION['name'] ?? 'Guest',
            'stats' => [],
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       Optional alias — /homepage also works
       ═══════════════════════════════════════════════════════════ */
    public function homepage() {
        return $this->index();
    }
}