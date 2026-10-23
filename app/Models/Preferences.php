<?php
namespace App\Models;

use App\Core\Database;

class Preferences {
    private $db;

    public function __construct() { $this->db = new Database(); }

    public function get($userId) {
        $this->db->query("SELECT * FROM user_preferences WHERE user_id = :u");
        $this->db->bind(':u', $userId);
        $pref = $this->db->single();

        if (!$pref) {
            // Auto-create with defaults
            $this->db->query("INSERT INTO user_preferences (user_id) VALUES (:u)");
            $this->db->bind(':u', $userId);
            $this->db->execute();

            $this->db->query("SELECT * FROM user_preferences WHERE user_id = :u");
            $this->db->bind(':u', $userId);
            $pref = $this->db->single();
        }
        return $pref;
    }

    public function update($userId, $data) {
        $fields = [];
        $binds  = [':u' => $userId];

        foreach ($data as $key => $value) {
            if (in_array($key, ['language_code','theme_name','primary_color','secondary_color',
                'accent_color','background_color','card_style','icon_style','border_radius',
                'animation_level','font_size','dark_mode','high_contrast','dashboard_layout'])) {
                $fields[] = "$key = :$key";
                $binds[":$key"] = is_array($value) ? json_encode($value) : $value;
            }
        }

        if (empty($fields)) return false;
        $this->db->query("UPDATE user_preferences SET " . implode(', ', $fields) . " WHERE user_id = :u");
        foreach ($binds as $k => $v) $this->db->bind($k, $v);
        return $this->db->execute();
    }

    public function getThemePreset($themeName) {
        $themes = [
            'green-harvest' => ['primary'=>'#16A34A','secondary'=>'#22C55E','accent'=>'#84CC16','background'=>'#081C15','mode'=>'dark'],
            'midnight'      => ['primary'=>'#0F172A','secondary'=>'#1E293B','accent'=>'#38BDF8','background'=>'#020617','mode'=>'dark'],
            'royal-purple'  => ['primary'=>'#7C3AED','secondary'=>'#A855F7','accent'=>'#C084FC','background'=>'#1A0B2E','mode'=>'dark'],
            'sunset'        => ['primary'=>'#EA580C','secondary'=>'#F97316','accent'=>'#FACC15','background'=>'#1C0A00','mode'=>'dark'],
            'sky-blue'      => ['primary'=>'#2563EB','secondary'=>'#3B82F6','accent'=>'#0EA5E9','background'=>'#0C1D3D','mode'=>'dark'],
        ];
        return $themes[$themeName] ?? $themes['green-harvest'];
    }
}
?>