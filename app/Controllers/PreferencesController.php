<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Preferences;

class PreferencesController extends Controller {
    private $prefs;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        $this->prefs = new Preferences();
    }

    public function index() {
        $data = [
            'title' => 'Appearance & Settings',
            'name'  => $_SESSION['name'],
            'prefs' => $this->prefs->get($_SESSION['user_id']),
        ];
        $this->view('settings/appearance', $data);
    }

    public function save() {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        $ok = $this->prefs->update($_SESSION['user_id'], $input);
        echo json_encode(['success' => $ok]);
        exit;
    }

    public function applyTheme() {
        header('Content-Type: application/json');
        $theme = $_POST['theme'] ?? 'green-harvest';
        $preset = $this->prefs->getThemePreset($theme);

        $this->prefs->update($_SESSION['user_id'], [
            'theme_name' => $theme,
            'primary_color' => $preset['primary'],
            'secondary_color' => $preset['secondary'],
            'accent_color' => $preset['accent'],
            'background_color' => $preset['background'],
            'dark_mode' => $preset['mode'] === 'dark' ? 1 : 0,
        ]);

        echo json_encode(['success' => true, 'theme' => $preset]);
        exit;
    }
}
?>