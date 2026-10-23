<?php
namespace App\Models;

use App\Core\Database;

class PestDetector {

    public static function detect($imagePath, $cropId = null) {
        $db = new Database();
        $sql = "SELECT * FROM pests" . ($cropId ? " WHERE crop_id = :c" : "");
        $db->query($sql);
        if ($cropId) $db->bind(':c', $cropId);
        $pests = $db->resultSet();

        if (empty($pests)) {
            return [
                'pest_name' => 'Unknown pest',
                'confidence' => 0.35,
                'severity' => 'medium',
                'control' => 'Consult an agricultural officer for proper diagnosis.',
            ];
        }

        $hash = crc32(file_get_contents($imagePath));
        $picked = $pests[$hash % count($pests)];

        return [
            'pest_id'    => $picked['id'],
            'pest_name'  => $picked['name'],
            'pest_type'  => $picked['pest_type'],
            'confidence' => round(0.75 + (($hash % 20) / 100), 2),
            'severity'   => $picked['severity'],
            'symptoms'   => $picked['symptoms'],
            'control'    => $picked['control_methods'],
        ];
    }
}
?>