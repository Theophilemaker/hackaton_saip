<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class SecurityController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 7) {
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }
    }

    public function auditLogs() {
        $db = new Database();
        $db->query("SELECT al.*, u.first_name, u.last_name
                    FROM audit_logs al
                    LEFT JOIN users u ON u.id = al.user_id
                    ORDER BY al.created_at DESC LIMIT 200");
        $data = [
            'title' => 'Audit Logs',
            'name'  => $_SESSION['name'],
            'logs'  => $db->resultSet(),
        ];
        $this->view('security/audit_logs', $data);
    }

    public function loginHistory() {
        $db = new Database();
        $db->query("SELECT lh.*, u.first_name, u.last_name
                    FROM login_history lh
                    LEFT JOIN users u ON u.id = lh.user_id
                    ORDER BY lh.login_at DESC LIMIT 200");
        $data = [
            'title' => 'Login History',
            'name'  => $_SESSION['name'],
            'logs'  => $db->resultSet(),
        ];
        $this->view('security/login_history', $data);
    }

    public function sessions() {
        $db = new Database();
        $db->query("SELECT us.*, u.first_name, u.last_name
                    FROM user_sessions us
                    JOIN users u ON u.id = us.user_id
                    WHERE us.is_active = 1
                    ORDER BY us.last_activity DESC LIMIT 100");
        $data = [
            'title' => 'Active Sessions',
            'name'  => $_SESSION['name'],
            'sessions' => $db->resultSet(),
        ];
        $this->view('security/sessions', $data);
    }

    public function killSession($id) {
        $db = new Database();
        $db->query("UPDATE user_sessions SET is_active = 0 WHERE id = :id");
        $db->bind(':id', $id);
        $db->execute();
        header("Location: " . BASE_URL . "/security/sessions");
        exit;
    }
}
?>