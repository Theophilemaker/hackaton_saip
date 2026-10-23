<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ApiController extends Controller {

    public function docs() {
        $data = [
            'title' => 'Developer API',
            'name'  => $_SESSION['name'] ?? 'Guest',
        ];
        $this->view('api/docs', $data);
    }

    /* ============ API ENDPOINTS ============ */

    public function weather() {
        $this->verify();
        $districtId = (int)($_GET['district_id'] ?? 0);
        $db = new Database();
        $db->query("SELECT * FROM weather_data WHERE district_id = :d ORDER BY recorded_at DESC LIMIT 10");
        $db->bind(':d', $districtId);
        $this->apiJson($db->resultSet());
    }

    public function crops() {
        $this->verify();
        $db = new Database();
        $db->query("SELECT id, name, scientific_name, category, icon, growing_days FROM crops WHERE is_active=1");
        $this->apiJson($db->resultSet());
    }

    public function markets() {
        $this->verify();
        $db = new Database();
        $db->query("SELECT p.id, p.title, p.price_per_unit, p.unit, p.district_id, c.name AS crop
                    FROM products p JOIN crops c ON c.id = p.crop_id
                    WHERE p.status='active' LIMIT 200");
        $this->apiJson($db->resultSet());
    }

    public function production() {
        $this->verify();
        $db = new Database();
        $db->query("SELECT d.name AS district, c.name AS crop, SUM(p.yield_kg) AS total_yield, p.year
                    FROM production p
                    JOIN users u ON u.id = p.user_id
                    JOIN farms f ON f.user_id = u.id
                    JOIN districts d ON d.id = f.district_id
                    JOIN crops c ON c.id = p.crop_id
                    GROUP BY d.id, c.id, p.year LIMIT 500");
        $this->apiJson($db->resultSet());
    }

    public function districts() {
        $this->verify();
        $db = new Database();
        $db->query("SELECT id, name, lat, lng FROM districts ORDER BY name");
        $this->apiJson($db->resultSet());
    }

    /* ============ HELPERS ============ */

    private function verify() {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');

        $token = $_GET['token'] ?? $_SERVER['HTTP_X_API_TOKEN'] ?? '';
        if (empty($token)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Missing API token']);
            exit;
        }

        $db = new Database();
        $db->query("SELECT * FROM api_keys 
                    WHERE api_key = :k AND is_active = 1 
                    AND (expires_at IS NULL OR expires_at > NOW())");
        $db->bind(':k', $token);
        $key = $db->single();

        if (!$key) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired token']);
            exit;
        }

        // Log the request
        try {
            $db->query("INSERT INTO api_request_log (token_id, endpoint, method, ip_address, status_code)
                        VALUES (:t, :e, :m, :ip, 200)");
            $db->bind(':t', $key['id']);
            $db->bind(':e', $_SERVER['REQUEST_URI'] ?? '');
            $db->bind(':m', $_SERVER['REQUEST_METHOD'] ?? 'GET');
            $db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? null);
            $db->execute();
        } catch (\Throwable $e) {
            // silent — logging shouldn't break the API
        }

        // Update last_used_at
        try {
            $db->query("UPDATE api_keys SET last_used_at = NOW() WHERE id = :id");
            $db->bind(':id', $key['id']);
            $db->execute();
        } catch (\Throwable $e) {
            // silent
        }
    }

    /**
     * ⭐ Renamed from json() → apiJson() to avoid clashing with
     *    parent Controller::json(). Wraps the payload in a
     *    standard success envelope.
     */
    private function apiJson($data) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'   => true,
            'data'      => $data,
            'timestamp' => date('c'),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>