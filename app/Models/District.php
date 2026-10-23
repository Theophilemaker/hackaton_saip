<?php
namespace App\Models;

use App\Core\Database;

class District {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function all() {
        try {
            $this->db->query("SELECT * FROM districts ORDER BY name");
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getProductionHeatmap($year = null) {
        try {
            $this->db->query("SELECT id, name, 0 AS total_yield FROM districts ORDER BY name");
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getCoordinates($districtId = null) {
        try {
            if ($districtId) {
                $this->db->query("SELECT * FROM districts WHERE id = :id");
                $this->db->bind(':id', (int)$districtId);
                return $this->db->single();
            }
            $this->db->query("SELECT * FROM districts");
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return $districtId ? null : [];
        }
    }
}
?>