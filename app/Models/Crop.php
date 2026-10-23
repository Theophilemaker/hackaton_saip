<?php
namespace App\Models;

use App\Core\Database;

class Crop
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function all()
    {
        try {
            $this->db->query('SELECT * FROM crops WHERE is_active = 1 ORDER BY name');
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function find($id)
    {
        try {
            $this->db->query('SELECT * FROM crops WHERE id = :id LIMIT 1');
            $this->db->bind(':id', (int)$id);
            return $this->db->single();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getFarms($userId)
    {
        try {
            $this->db->query('SELECT f.*, d.name AS district_name FROM farms f LEFT JOIN districts d ON d.id = f.district_id WHERE f.user_id = :u AND f.is_active = 1 ORDER BY f.created_at DESC');
            $this->db->bind(':u', (int)$userId);
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getFarm($id, $userId)
    {
        try {
            $this->db->query('SELECT f.*, d.name AS district_name FROM farms f LEFT JOIN districts d ON d.id = f.district_id WHERE f.id = :id AND f.user_id = :u LIMIT 1');
            $this->db->bind(':id', (int)$id);
            $this->db->bind(':u', (int)$userId);
            return $this->db->single();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function createFarm($data)
    {
        try {
            $this->db->query('INSERT INTO farms (uuid, user_id, name, district_id, size_ha, soil_type, irrigation_type) VALUES (:u, :uid, :n, :d, :s, :st, :ir)');
            $this->db->bind(':u', uniqid('farm_', true));
            $this->db->bind(':uid', (int)$data['user_id']);
            $this->db->bind(':n', $data['name'] ?? 'Farm');
            $this->db->bind(':d', !empty($data['district_id']) ? (int)$data['district_id'] : null);
            $this->db->bind(':s', $data['size_ha'] ?? null);
            $this->db->bind(':st', $data['soil_type'] ?? 'loam');
            $this->db->bind(':ir', $data['irrigation_type'] ?? 'none');
            $this->db->execute();
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function plantCrop($data)
    {
        try {
            $this->db->query('INSERT INTO farm_crops (farm_id, crop_id, season, year, planted_date, area_ha) VALUES (:f, :c, :s, :y, :pd, :a)');
            $this->db->bind(':f', (int)$data['farm_id']);
            $this->db->bind(':c', (int)$data['crop_id']);
            $this->db->bind(':s', $data['season'] ?? 'A');
            $this->db->bind(':y', (int)($data['year'] ?? date('Y')));
            $this->db->bind(':pd', $data['planted_date'] ?? date('Y-m-d'));
            $this->db->bind(':a', $data['area_ha'] ?? null);
            $this->db->execute();
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getActiveCrops($userId)
    {
        try {
            $this->db->query('SELECT fc.*, c.name AS crop_name, c.icon AS crop_icon, f.name AS farm_name FROM farm_crops fc JOIN crops c ON c.id = fc.crop_id JOIN farms f ON f.id = fc.farm_id WHERE f.user_id = :u AND fc.is_active = 1 ORDER BY fc.planted_date DESC');
            $this->db->bind(':u', (int)$userId);
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getFarmCrop($id, $userId)
    {
        try {
            $this->db->query('SELECT fc.*, c.name AS crop_name, c.icon AS crop_icon, c.scientific_name, c.growing_days, f.name AS farm_name FROM farm_crops fc JOIN crops c ON c.id = fc.crop_id JOIN farms f ON f.id = fc.farm_id WHERE fc.id = :id AND f.user_id = :u LIMIT 1');
            $this->db->bind(':id', (int)$id);
            $this->db->bind(':u', (int)$userId);
            return $this->db->single();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function updateStage($id, $stage, $userId)
    {
        try {
            $allowed = ['planting', 'germination', 'vegetative', 'flowering', 'fruiting', 'maturity', 'harvested'];
            if (!in_array($stage, $allowed, true)) {
                return false;
            }
            $this->db->query('UPDATE farm_crops SET growth_stage = :s WHERE id = :id');
            $this->db->bind(':s', $stage);
            $this->db->bind(':id', (int)$id);
            $this->db->execute();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getGrowthLogs($farmCropId)
    {
        try {
            $this->db->query('SELECT * FROM growth_logs WHERE farm_crop_id = :f ORDER BY recorded_at DESC LIMIT 50');
            $this->db->bind(':f', (int)$farmCropId);
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getYieldStats($userId)
    {
        try {
            $this->db->query('SELECT c.id, c.name, c.icon, COALESCE(SUM(fc.actual_yield_kg), 0) AS total_yield FROM crops c LEFT JOIN farm_crops fc ON fc.crop_id = c.id LEFT JOIN farms f ON f.id = fc.farm_id WHERE f.user_id = :u GROUP BY c.id, c.name, c.icon ORDER BY total_yield DESC LIMIT 8');
            $this->db->bind(':u', (int)$userId);
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getProductionHistory($userId, $cropId = null)
    {
        try {
            $sql = 'SELECT fc.*, c.name AS crop_name, c.icon AS crop_icon, f.name AS farm_name FROM farm_crops fc JOIN crops c ON c.id = fc.crop_id JOIN farms f ON f.id = fc.farm_id WHERE f.user_id = :u';
            if ($cropId) {
                $sql .= ' AND fc.crop_id = :cid';
            }
            $sql .= ' ORDER BY fc.planted_date DESC LIMIT 50';
            $this->db->query($sql);
            $this->db->bind(':u', (int)$userId);
            if ($cropId) {
                $this->db->bind(':cid', (int)$cropId);
            }
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function saveYieldPrediction($farmCropId, $yieldKg, $confidence, $features = [])
    {
        return false;
    }

    public function getDiseases($cropId = null)
    {
        try {
            $sql = 'SELECT * FROM diseases WHERE is_active = 1';
            if ($cropId) {
                $sql .= ' AND (crop_id = :cid OR crop_id IS NULL)';
            }
            $sql .= ' ORDER BY name';
            $this->db->query($sql);
            if ($cropId) {
                $this->db->bind(':cid', (int)$cropId);
            }
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getDetections($userId, $limit = 20)
    {
        try {
            $this->db->query('SELECT id, user_id, farm_crop_id, image_url, disease_id, disease_name, confidence, severity, treatment, recommendation, created_at FROM crop_detections WHERE user_id = :u ORDER BY created_at DESC LIMIT 20');
            $this->db->bind(':u', (int)$userId);
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function recordDetection($data)
    {
        try {
            $this->db->query('INSERT INTO crop_detections (user_id, farm_crop_id, image_url, disease_id, disease_name, confidence, severity, treatment, recommendation, created_at) VALUES (:uid, :fcid, :img, :did, :dn, :conf, :sev, :tr, :rec, NOW())');
            $this->db->bind(':uid', (int)($data['user_id'] ?? 0));
            $this->db->bind(':fcid', !empty($data['farm_crop_id']) ? (int)$data['farm_crop_id'] : null);
            $this->db->bind(':img', $data['image_url'] ?? null);
            $this->db->bind(':did', !empty($data['disease_id']) ? (int)$data['disease_id'] : null);
            $this->db->bind(':dn', $data['disease_name'] ?? 'Unknown');
            $this->db->bind(':conf', (float)($data['confidence'] ?? 0));
            $this->db->bind(':sev', $data['severity'] ?? 'medium');
            $this->db->bind(':tr', $data['treatment'] ?? null);
            $this->db->bind(':rec', $data['recommendation'] ?? null);
            $this->db->execute();
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getDetection($id, $userId)
    {
        try {
            $this->db->query('SELECT * FROM crop_detections WHERE id = :id AND user_id = :u LIMIT 1');
            $this->db->bind(':id', (int)$id);
            $this->db->bind(':u', (int)$userId);
            return $this->db->single();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getPriceForecast($cropId, $days = 14)
    {
        return [];
    }
}