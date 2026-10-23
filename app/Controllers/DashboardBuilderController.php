<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DashboardBuilderController extends Controller {
    private $db;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        $this->db = new Database();
    }

    public function edit() {
        $roleId = (int)$_SESSION['role_id'];
        $dashType = $this->roleToDashType($roleId);

        $this->db->query("SELECT * FROM widget_catalog WHERE is_active = 1 ORDER BY category, name");
        $widgets = $this->db->resultSet();

        $this->db->query("SELECT * FROM dashboard_layouts WHERE user_id = :u AND dashboard_type = :t");
        $this->db->bind(':u', $_SESSION['user_id']);
        $this->db->bind(':t', $dashType);
        $layout = $this->db->single();

        $layoutData = $layout ? json_decode($layout['layout_data'], true) : null;

        $data = [
            'title'  => 'Customize Dashboard',
            'name'   => $_SESSION['name'],
            'widgets'=> $widgets,
            'layout' => $layoutData,
            'dash_type' => $dashType,
        ];
        $this->view('dashboard/builder', $data);
    }

    public function save() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $layout = $input['layout'] ?? [];
        $roleId = (int)$_SESSION['role_id'];
        $dashType = $this->roleToDashType($roleId);

        $this->db->query("INSERT INTO dashboard_layouts (user_id, dashboard_type, layout_data)
                          VALUES (:u, :t, :l)
                          ON DUPLICATE KEY UPDATE layout_data = :l2, updated_at = NOW()");
        $this->db->bind(':u', $_SESSION['user_id']);
        $this->db->bind(':t', $dashType);
        $this->db->bind(':l', json_encode($layout));
        $this->db->bind(':l2', json_encode($layout));
        $this->db->execute();

        echo json_encode(['success' => true]);
        exit;
    }

    public function reset() {
        $this->db->query("DELETE FROM dashboard_layouts WHERE user_id = :u");
        $this->db->bind(':u', $_SESSION['user_id']);
        $this->db->execute();
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }

    private function roleToDashType($roleId) {
        return match($roleId) {
            1 => 'farmer',
            2 => 'cooperative',
            3 => 'officer',
            4 => 'government',
            5 => 'exporter',
            6 => 'researcher',
            7 => 'admin',
            default => 'farmer',
        };
    }
}
?>