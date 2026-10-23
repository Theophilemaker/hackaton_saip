<?php
namespace App\Models;

use App\Core\Database;

class Storage {
    private $db;

    public function __construct() { $this->db = new Database(); }

    public function warehousesByUser($userId) {
        $this->db->query("SELECT w.*, d.name AS district_name,
                                 (SELECT COUNT(*) FROM storage_bins WHERE warehouse_id=w.id) AS bin_count,
                                 (SELECT COALESCE(SUM(quantity_kg),0) FROM storage_bins WHERE warehouse_id=w.id) AS stored_kg
                          FROM warehouses w
                          LEFT JOIN districts d ON d.id = w.district_id
                          WHERE w.owner_id=:u OR w.cooperative_id IN (
                             SELECT cooperative_id FROM cooperative_members WHERE user_id=:u2
                          )
                          ORDER BY w.name");
        $this->db->bind(':u', $userId);
        $this->db->bind(':u2', $userId);
        return $this->db->resultSet();
    }

    public function createWarehouse($data) {
        $this->db->query("INSERT INTO warehouses
            (uuid, name, owner_id, cooperative_id, district_id, address, lat, lng,
             capacity_kg, capacity_m3, warehouse_type, has_ventilation, has_cooling)
            VALUES (:u,:n,:o,:c,:d,:a,:la,:ln,:ck,:cm,:t,:hv,:hc)");
        $this->db->bind(':u', uniqid('', true));
        $this->db->bind(':n', $data['name']);
        $this->db->bind(':o', $data['owner_id']);
        $this->db->bind(':c', $data['cooperative_id'] ?? null);
        $this->db->bind(':d', $data['district_id'] ?? null);
        $this->db->bind(':a', $data['address'] ?? '');
        $this->db->bind(':la', $data['lat'] ?? null);
        $this->db->bind(':ln', $data['lng'] ?? null);
        $this->db->bind(':ck', $data['capacity_kg']);
        $this->db->bind(':cm', $data['capacity_m3'] ?? null);
        $this->db->bind(':t', $data['warehouse_type'] ?? 'dry');
        $this->db->bind(':hv', $data['has_ventilation'] ?? 1);
        $this->db->bind(':hc', $data['has_cooling'] ?? 0);
        return $this->db->lastInsertId();
    }

    public function getWarehouse($id, $userId) {
        $this->db->query("SELECT w.*, d.name AS district_name FROM warehouses w
                          LEFT JOIN districts d ON d.id=w.district_id
                          WHERE w.id=:id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getBins($warehouseId) {
        $this->db->query("SELECT b.*, c.name AS crop_name, c.icon AS crop_icon
                          FROM storage_bins b
                          LEFT JOIN crops c ON c.id = b.crop_id
                          WHERE b.warehouse_id=:w
                          ORDER BY b.bin_code");
        $this->db->bind(':w', $warehouseId);
        return $this->db->resultSet();
    }

    public function addBin($data) {
        $this->db->query("INSERT INTO storage_bins
            (warehouse_id, bin_code, crop_id, quantity_kg, moisture_pct, temperature_c, entry_date, expiry_date)
            VALUES (:w,:bc,:c,:q,:m,:t,:e,:ex)");
        $this->db->bind(':w', $data['warehouse_id']);
        $this->db->bind(':bc', $data['bin_code']);
        $this->db->bind(':c', $data['crop_id']);
        $this->db->bind(':q', $data['quantity_kg']);
        $this->db->bind(':m', $data['moisture_pct'] ?? null);
        $this->db->bind(':t', $data['temperature_c'] ?? null);
        $this->db->bind(':e', $data['entry_date'] ?? date('Y-m-d'));
        $this->db->bind(':ex', $data['expiry_date'] ?? null);
        return $this->db->lastInsertId();
    }

    public function updateBin($binId, $data) {
        $this->db->query("UPDATE storage_bins SET
            quantity_kg=:q, moisture_pct=:m, temperature_c=:t, pest_risk=:pr, status=:s
            WHERE id=:id");
        $this->db->bind(':q', $data['quantity_kg']);
        $this->db->bind(':m', $data['moisture_pct'] ?? null);
        $this->db->bind(':t', $data['temperature_c'] ?? null);
        $this->db->bind(':pr', $data['pest_risk'] ?? 'low');
        $this->db->bind(':s', $data['status'] ?? 'active');
        $this->db->bind(':id', $binId);
        return $this->db->execute();
    }

    public function logEvent($binId, $type, $qty, $note, $userId) {
        $this->db->query("INSERT INTO storage_events (bin_id, event_type, quantity_kg, note, recorded_by)
                          VALUES (:b,:t,:q,:n,:u)");
        $this->db->bind(':b', $binId);
        $this->db->bind(':t', $type);
        $this->db->bind(':q', $qty);
        $this->db->bind(':n', $note);
        $this->db->bind(':u', $userId);
        return $this->db->execute();
    }

    public function getEvents($binId) {
        $this->db->query("SELECT se.*, u.first_name FROM storage_events se
                          LEFT JOIN users u ON u.id = se.recorded_by
                          WHERE se.bin_id=:b ORDER BY se.created_at DESC LIMIT 30");
        $this->db->bind(':b', $binId);
        return $this->db->resultSet();
    }

    /* ============ EXPIRY ALERTS ============ */
    public function getExpiringBins($userId, $days = 30) {
        $this->db->query("SELECT b.*, w.name AS warehouse_name, c.name AS crop_name, c.icon
                          FROM storage_bins b
                          JOIN warehouses w ON w.id = b.warehouse_id
                          LEFT JOIN crops c ON c.id = b.crop_id
                          WHERE (w.owner_id=:u OR w.cooperative_id IN (
                             SELECT cooperative_id FROM cooperative_members WHERE user_id=:u2
                          ))
                          AND b.expiry_date IS NOT NULL
                          AND b.expiry_date <= DATE_ADD(CURDATE(), INTERVAL :d DAY)
                          AND b.expiry_date >= CURDATE()
                          ORDER BY b.expiry_date ASC");
        $this->db->bind(':u', $userId);
        $this->db->bind(':u2', $userId);
        $this->db->bind(':d', $days);
        return $this->db->resultSet();
    }
}
?>