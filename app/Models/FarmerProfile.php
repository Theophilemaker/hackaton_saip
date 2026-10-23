<?php
namespace App\Models;

use App\Core\Database;

class FarmerProfile {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Fetch a farmer's profile. Creates a default one if missing.
     */
    public function get($userId) {
        $this->db->query("SELECT * FROM farmer_profiles WHERE user_id = :u");
        $this->db->bind(':u', (int)$userId);
        $profile = $this->db->single();

        if ($profile) return $profile;

        // Auto-create empty profile
        try {
            $this->db->query("INSERT INTO farmer_profiles (user_id) VALUES (:u)");
            $this->db->bind(':u', (int)$userId);
            $this->db->execute();
        } catch (\Throwable $e) {
            error_log("FarmerProfile::get auto-create failed: " . $e->getMessage());
        }

        $this->db->query("SELECT * FROM farmer_profiles WHERE user_id = :u");
        $this->db->bind(':u', (int)$userId);
        return $this->db->single();
    }

    /**
     * Save or update a farmer profile.
     * Only writes columns that actually exist in the DB.
     */
    public function save($userId, array $data) {
        $this->get($userId); // ensure row exists

        $existingCols = $this->existingColumns();

        $fields = [];
        $binds  = [];

        $candidates = [
            'district'                 => 'district',
            'sector'                   => 'sector',
            'cell'                     => 'cell',
            'province'                 => 'province',
            'land_size_ha'             => 'land_size_ha',
            'primary_crops'            => 'primary_crops',
            'soil_type'                => 'soil_type',
            'has_irrigation'           => 'has_irrigation',
            'farming_experience_years' => 'farming_experience_years',
        ];

        foreach ($candidates as $field => $colName) {
            if (!in_array($colName, $existingCols, true)) {
                error_log("FarmerProfile: column '{$colName}' missing — skipped");
                continue;
            }

            switch ($field) {
                case 'land_size_ha':
                    $value = (float)($data[$field] ?? 1);
                    break;
                case 'primary_crops':
                    $value = json_encode($data[$field] ?? []);
                    break;
                case 'has_irrigation':
                    $value = !empty($data[$field]) ? 1 : 0;
                    break;
                case 'farming_experience_years':
                    $value = (int)($data[$field] ?? 0);
                    break;
                default:
                    $value = $data[$field] ?? null;
            }

            $fields[] = "`{$colName}` = :{$field}";
            $binds[":{$field}"] = $value;
        }

        if (empty($fields)) {
            error_log("FarmerProfile: no valid columns to update");
            return false;
        }

        $sql = "UPDATE farmer_profiles SET " . implode(', ', $fields) . " WHERE user_id = :u";
        $this->db->query($sql);
        foreach ($binds as $k => $v) {
            $this->db->bind($k, $v);
        }
        $this->db->bind(':u', (int)$userId);

        return $this->db->execute();
    }

    /**
     * Return list of column names that exist in farmer_profiles.
     */
    private function existingColumns(): array {
        try {
            $this->db->query("SELECT COLUMN_NAME 
                              FROM INFORMATION_SCHEMA.COLUMNS
                              WHERE TABLE_SCHEMA = DATABASE()
                                AND TABLE_NAME = 'farmer_profiles'");
            $rows = $this->db->resultSet();
            return array_map(fn($r) => $r['COLUMN_NAME'], $rows);
        } catch (\Throwable $e) {
            error_log("FarmerProfile::existingColumns error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Is the profile sufficiently filled?
     */
    public function isComplete($profile) {
        if (!$profile) return false;
        return !empty($profile['district'])
            && !empty($profile['land_size_ha']);
    }
}