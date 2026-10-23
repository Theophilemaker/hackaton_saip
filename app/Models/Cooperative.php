<?php
namespace App\Models;

use App\Core\Database;

class Cooperative {
    private $db;

    public function __construct() { $this->db = new Database(); }

    public function all() {
        $this->db->query("SELECT c.*, d.name AS district_name,
                                 (SELECT COUNT(*) FROM cooperative_members WHERE cooperative_id=c.id) AS members
                          FROM cooperatives c
                          LEFT JOIN districts d ON d.id = c.district_id
                          WHERE c.status='active' ORDER BY c.name");
        return $this->db->resultSet();
    }

    public function create($data) {
        $this->db->query("INSERT INTO cooperatives
            (uuid, name, registration_no, district_id, sector, cell, village,
             chairperson_id, founded_year, description)
            VALUES (:u,:n,:r,:d,:s,:cl,:v,:ch,:fy,:de)");
        $this->db->bind(':u', uniqid('', true));
        $this->db->bind(':n', $data['name']);
        $this->db->bind(':r', $data['registration_no'] ?? null);
        $this->db->bind(':d', $data['district_id'] ?? null);
        $this->db->bind(':s', $data['sector'] ?? null);
        $this->db->bind(':cl', $data['cell'] ?? null);
        $this->db->bind(':v', $data['village'] ?? null);
        $this->db->bind(':ch', $data['chairperson_id'] ?? null);
        $this->db->bind(':fy', $data['founded_year'] ?? date('Y'));
        $this->db->bind(':de', $data['description'] ?? '');
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    public function getById($id) {
        $this->db->query("SELECT c.*, d.name AS district_name,
                                 u.first_name AS chair_name
                          FROM cooperatives c
                          LEFT JOIN districts d ON d.id = c.district_id
                          LEFT JOIN users u ON u.id = c.chairperson_id
                          WHERE c.id=:id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getByUser($userId) {
        $this->db->query("SELECT c.*, cm.role AS my_role
                          FROM cooperatives c
                          JOIN cooperative_members cm ON cm.cooperative_id=c.id
                          WHERE cm.user_id=:u AND cm.status='active'");
        $this->db->bind(':u', $userId);
        return $this->db->resultSet();
    }

    public function addMember($coopId, $userId, $role = 'member', $memberNo = null) {
        $this->db->query("INSERT IGNORE INTO cooperative_members
            (cooperative_id, user_id, member_no, role, joined_at)
            VALUES (:c,:u,:m,:r,CURDATE())");
        $this->db->bind(':c', $coopId);
        $this->db->bind(':u', $userId);
        $this->db->bind(':m', $memberNo);
        $this->db->bind(':r', $role);
        $this->db->execute();

        // Update count
        $this->db->query("UPDATE cooperatives SET member_count=(SELECT COUNT(*) FROM cooperative_members WHERE cooperative_id=:c AND status='active') WHERE id=:c2");
        $this->db->bind(':c', $coopId);
        $this->db->bind(':c2', $coopId);
        return $this->db->execute();
    }

    public function getMembers($coopId) {
        $this->db->query("SELECT cm.*, u.first_name, u.last_name, u.phone, u.email, u.uuid
                          FROM cooperative_members cm
                          JOIN users u ON u.id = cm.user_id
                          WHERE cm.cooperative_id=:c ORDER BY cm.role, u.first_name");
        $this->db->bind(':c', $coopId);
        return $this->db->resultSet();
    }

    /* ============ HARVESTS ============ */
    public function getHarvests($coopId) {
        $this->db->query("SELECT h.*, c.name AS crop_name, c.icon
                          FROM coop_harvests h
                          JOIN crops c ON c.id = h.crop_id
                          WHERE h.cooperative_id=:c
                          ORDER BY h.harvest_date DESC");
        $this->db->bind(':c', $coopId);
        return $this->db->resultSet();
    }

    public function addHarvest($data) {
        $this->db->query("INSERT INTO coop_harvests
            (cooperative_id, crop_id, season, year, total_yield_kg, total_area_ha,
             quality_grade, harvest_date, notes, created_by)
            VALUES (:c,:cr,:s,:y,:ty,:ta,:qg,:hd,:n,:cb)");
        $this->db->bind(':c', $data['cooperative_id']);
        $this->db->bind(':cr', $data['crop_id']);
        $this->db->bind(':s', $data['season'] ?? 'A');
        $this->db->bind(':y', $data['year'] ?? date('Y'));
        $this->db->bind(':ty', $data['total_yield_kg']);
        $this->db->bind(':ta', $data['total_area_ha'] ?? null);
        $this->db->bind(':qg', $data['quality_grade'] ?? 'Standard');
        $this->db->bind(':hd', $data['harvest_date'] ?? date('Y-m-d'));
        $this->db->bind(':n', $data['notes'] ?? '');
        $this->db->bind(':cb', $data['created_by'] ?? null);
        return $this->db->execute();
    }

    /* ============ CONTRIBUTIONS ============ */
    public function getContributions($coopId, $userId = null) {
        $sql = "SELECT cc.*, u.first_name, u.last_name
                FROM coop_contributions cc
                JOIN users u ON u.id = cc.user_id
                WHERE cc.cooperative_id = :c";
        if ($userId) $sql .= " AND cc.user_id = :u";
        $sql .= " ORDER BY cc.contribution_date DESC LIMIT 100";
        $this->db->query($sql);
        $this->db->bind(':c', $coopId);
        if ($userId) $this->db->bind(':u', $userId);
        return $this->db->resultSet();
    }

    public function addContribution($data) {
        $this->db->query("INSERT INTO coop_contributions
            (cooperative_id, user_id, amount_rwf, purpose, contribution_date,
             payment_method, status, receipt_no, recorded_by)
            VALUES (:c,:u,:a,:p,:d,:m,:s,:r,:rb)");
        $this->db->bind(':c', $data['cooperative_id']);
        $this->db->bind(':u', $data['user_id']);
        $this->db->bind(':a', $data['amount_rwf']);
        $this->db->bind(':p', $data['purpose'] ?? 'Monthly contribution');
        $this->db->bind(':d', $data['contribution_date'] ?? date('Y-m-d'));
        $this->db->bind(':m', $data['payment_method'] ?? 'cash');
        $this->db->bind(':s', $data['status'] ?? 'paid');
        $this->db->bind(':r', 'RCP-' . strtoupper(substr(uniqid(), -8)));
        $this->db->bind(':rb', $data['recorded_by'] ?? null);
        return $this->db->execute();
    }

    /* ============ EQUIPMENT ============ */
    public function getEquipment($coopId) {
        $this->db->query("SELECT e.*, u.first_name AS assigned_name
                          FROM coop_equipment e
                          LEFT JOIN users u ON u.id = e.assigned_to
                          WHERE e.cooperative_id=:c ORDER BY e.category, e.name");
        $this->db->bind(':c', $coopId);
        return $this->db->resultSet();
    }

    public function addEquipment($data) {
        $this->db->query("INSERT INTO coop_equipment
            (cooperative_id, name, category, quantity, condition_status, purchase_date, purchase_value_rwf, location, notes)
            VALUES (:c,:n,:cat,:q,:cs,:pd,:pv,:lo,:nt)");
        $this->db->bind(':c', $data['cooperative_id']);
        $this->db->bind(':n', $data['name']);
        $this->db->bind(':cat', $data['category'] ?? 'other');
        $this->db->bind(':q', $data['quantity'] ?? 1);
        $this->db->bind(':cs', $data['condition_status'] ?? 'good');
        $this->db->bind(':pd', $data['purchase_date'] ?? null);
        $this->db->bind(':pv', $data['purchase_value_rwf'] ?? null);
        $this->db->bind(':lo', $data['location'] ?? null);
        $this->db->bind(':nt', $data['notes'] ?? '');
        return $this->db->execute();
    }

    /* ============ MEETINGS ============ */
    public function getMeetings($coopId) {
        $this->db->query("SELECT m.*,
                                 (SELECT COUNT(*) FROM coop_meeting_attendance WHERE meeting_id=m.id AND attended=1) AS attended_count
                          FROM coop_meetings m
                          WHERE m.cooperative_id=:c ORDER BY m.meeting_date DESC");
        $this->db->bind(':c', $coopId);
        return $this->db->resultSet();
    }

    public function createMeeting($data) {
        $this->db->query("INSERT INTO coop_meetings
            (cooperative_id, title, agenda, meeting_date, location, status, created_by)
            VALUES (:c,:t,:a,:d,:l,'scheduled',:cb)");
        $this->db->bind(':c', $data['cooperative_id']);
        $this->db->bind(':t', $data['title']);
        $this->db->bind(':a', $data['agenda'] ?? '');
        $this->db->bind(':d', $data['meeting_date']);
        $this->db->bind(':l', $data['location'] ?? '');
        $this->db->bind(':cb', $data['created_by']);
        return $this->db->lastInsertId();
    }

    /* ============ REPORTS ============ */
    public function getStats($coopId) {
        $this->db->query("SELECT
            (SELECT COUNT(*) FROM cooperative_members WHERE cooperative_id=:c AND status='active') AS members,
            (SELECT COALESCE(SUM(total_yield_kg),0) FROM coop_harvests WHERE cooperative_id=:c) AS total_harvest,
            (SELECT COALESCE(SUM(amount_rwf),0) FROM coop_contributions WHERE cooperative_id=:c AND status='paid') AS total_contributions,
            (SELECT COUNT(*) FROM coop_equipment WHERE cooperative_id=:c) AS equipment_count,
            (SELECT COUNT(*) FROM coop_meetings WHERE cooperative_id=:c) AS meetings_count
        ");
        $this->db->bind(':c', $coopId);
        return $this->db->single();
    }
}
?>