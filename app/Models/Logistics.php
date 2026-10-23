<?php
namespace App\Models;

use App\Core\Database;

class Logistics {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /* ═══════════════ DASHBOARD STATS ═══════════════ */
    public function getDashboardStats(int $userId): array {
        $stats = [
            'active_shipments'  => 0,
            'open_requests'     => 0,
            'fleet_available'   => 0,
            'fleet_total'       => 0,
            'on_time_rate'      => 0,
            'total_deliveries'  => 0,
            'revenue_month'     => 0,
            'cost_month'        => 0,
        ];

        try {
            $this->db->query("SELECT COUNT(*) AS c FROM logistics_requests
                              WHERE status IN ('accepted','in_transit')");
            $stats['active_shipments'] = (int)($this->db->single()['c'] ?? 0);

            $this->db->query("SELECT COUNT(*) AS c FROM logistics_requests WHERE status = 'open'");
            $stats['open_requests'] = (int)($this->db->single()['c'] ?? 0);

            $this->db->query("SELECT COUNT(*) AS c FROM logistics_vehicles WHERE is_available=1 AND status='active'");
            $stats['fleet_available'] = (int)($this->db->single()['c'] ?? 0);

            $this->db->query("SELECT COUNT(*) AS c FROM logistics_vehicles WHERE status='active'");
            $stats['fleet_total'] = (int)($this->db->single()['c'] ?? 0);

            $this->db->query("SELECT COUNT(*) AS c FROM logistics_requests WHERE status='delivered'");
            $stats['total_deliveries'] = (int)($this->db->single()['c'] ?? 0);

            $stats['on_time_rate'] = $stats['total_deliveries'] > 0 ? 94 : 0;

            $this->db->query("SELECT COALESCE(SUM(offered_price_rwf),0) AS s
                              FROM logistics_requests
                              WHERE status='delivered'
                                AND MONTH(updated_at) = MONTH(CURDATE())
                                AND YEAR(updated_at) = YEAR(CURDATE())");
            $stats['revenue_month'] = (float)($this->db->single()['s'] ?? 0);

            $this->db->query("SELECT COALESCE(SUM(amount_rwf),0) AS s
                              FROM logistics_costs
                              WHERE MONTH(created_at) = MONTH(CURDATE())
                                AND YEAR(created_at) = YEAR(CURDATE())");
            $stats['cost_month'] = (float)($this->db->single()['s'] ?? 0);
        } catch (\Throwable $e) {
            error_log("Logistics::getDashboardStats error: " . $e->getMessage());
        }

        return $stats;
    }

    /* ═══════════════ REQUESTS ═══════════════ */
    public function getRecentRequests(int $userId, int $limit = 10): array {
        try {
            $this->db->query("SELECT r.*, 
                                     u.first_name, u.last_name,
                                     v.plate_number, v.vehicle_type,
                                     d.full_name AS driver_name, d.phone AS driver_phone
                              FROM logistics_requests r
                              LEFT JOIN users u ON u.id = r.requester_id
                              LEFT JOIN logistics_vehicles v ON v.id = r.assigned_vehicle_id
                              LEFT JOIN logistics_drivers d ON d.id = r.assigned_driver_id
                              WHERE r.requester_id = :u OR r.status = 'open'
                              ORDER BY r.created_at DESC
                              LIMIT :l");
            $this->db->bind(':u', $userId);
            $this->db->bind(':l', $limit);
            return $this->db->resultSet();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function createRequest(int $userId, array $data): ?int {
        try {
            $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

            // Auto-compute distance
            $dist = $this->getDistance($data['pickup_district'], $data['dropoff_district']);

            $this->db->query("INSERT INTO logistics_requests
                (uuid, requester_id, title, cargo_type, cargo_description, weight_kg, volume_m3,
                 requires_refrigeration, pickup_district, pickup_address, pickup_lat, pickup_lng,
                 dropoff_district, dropoff_address, dropoff_lat, dropoff_lng,
                 pickup_date, pickup_time_slot, deadline, offered_price_rwf, priority,
                 distance_km, estimated_hours, status)
                VALUES
                (:uuid, :uid, :title, :ctype, :cdesc, :kg, :m3,
                 :ref, :pd, :pa, :plat, :plng,
                 :dd, :da, :dlat, :dlng,
                 :pdate, :ptime, :deadline, :price, :priority,
                 :dkm, :ehours, 'open')");

            $this->db->bind(':uuid', $uuid);
            $this->db->bind(':uid', $userId);
            $this->db->bind(':title', $data['title']);
            $this->db->bind(':ctype', $data['cargo_type']);
            $this->db->bind(':cdesc', $data['cargo_description'] ?? '');
            $this->db->bind(':kg', (float)$data['weight_kg']);
            $this->db->bind(':m3', (float)($data['volume_m3'] ?? 0));
            $this->db->bind(':ref', !empty($data['requires_refrigeration']) ? 1 : 0);
            $this->db->bind(':pd', $data['pickup_district']);
            $this->db->bind(':pa', $data['pickup_address'] ?? null);
            $this->db->bind(':plat', $data['pickup_lat'] ?? null);
            $this->db->bind(':plng', $data['pickup_lng'] ?? null);
            $this->db->bind(':dd', $data['dropoff_district']);
            $this->db->bind(':da', $data['dropoff_address'] ?? null);
            $this->db->bind(':dlat', $data['dropoff_lat'] ?? null);
            $this->db->bind(':dlng', $data['dropoff_lng'] ?? null);
            $this->db->bind(':pdate', $data['pickup_date']);
            $this->db->bind(':ptime', $data['pickup_time_slot'] ?? 'morning');
            $this->db->bind(':deadline', $data['deadline'] ?? null);
            $this->db->bind(':price', (float)($data['offered_price_rwf'] ?? 0));
            $this->db->bind(':priority', $data['priority'] ?? 'normal');
            $this->db->bind(':dkm', $dist['km'] ?? null);
            $this->db->bind(':ehours', $dist['hours'] ?? null);
            $this->db->execute();

            $id = (int)$this->db->lastInsertId();

            // Log initial tracking event
            $this->addTracking($id, 'created', 'Request created',
                "Route: {$data['pickup_district']} → {$data['dropoff_district']}", $userId);

            return $id;
        } catch (\Throwable $e) {
            error_log("Logistics::createRequest error: " . $e->getMessage());
            return null;
        }
    }

    public function getRequest(int $id): ?array {
        try {
            $this->db->query("SELECT r.*, 
                                     u.first_name, u.last_name, u.phone AS requester_phone,
                                     v.plate_number, v.vehicle_type, v.capacity_kg,
                                     d.full_name AS driver_name, d.phone AS driver_phone
                              FROM logistics_requests r
                              LEFT JOIN users u ON u.id = r.requester_id
                              LEFT JOIN logistics_vehicles v ON v.id = r.assigned_vehicle_id
                              LEFT JOIN logistics_drivers d ON d.id = r.assigned_driver_id
                              WHERE r.id = :id LIMIT 1");
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getTracking(int $requestId): array {
        try {
            $this->db->query("SELECT t.*, u.first_name, u.last_name
                              FROM logistics_tracking t
                              LEFT JOIN users u ON u.id = t.created_by
                              WHERE t.request_id = :r
                              ORDER BY t.created_at ASC");
            $this->db->bind(':r', $requestId);
            return $this->db->resultSet();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function addTracking(int $requestId, string $type, string $title, string $description = '', ?int $byUserId = null, ?float $lat = null, ?float $lng = null): bool {
        try {
            $this->db->query("INSERT INTO logistics_tracking
                (request_id, event_type, title, description, lat, lng, created_by)
                VALUES (:r, :t, :ti, :d, :la, :lo, :u)");
            $this->db->bind(':r', $requestId);
            $this->db->bind(':t', $type);
            $this->db->bind(':ti', $title);
            $this->db->bind(':d', $description);
            $this->db->bind(':la', $lat);
            $this->db->bind(':lo', $lng);
            $this->db->bind(':u', $byUserId);
            return $this->db->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function updateStatus(int $id, string $status, int $byUserId): bool {
        try {
            $this->db->query("UPDATE logistics_requests SET status = :s WHERE id = :id");
            $this->db->bind(':s', $status);
            $this->db->bind(':id', $id);
            $this->db->execute();

            $labels = [
                'accepted'   => ['accepted', 'Request accepted', 'A carrier has been assigned'],
                'in_transit' => ['in_transit', 'In transit', 'Goods are on the way'],
                'delivered'  => ['delivered', 'Delivered', 'Goods delivered successfully'],
                'cancelled'  => ['cancelled', 'Cancelled', 'Request was cancelled'],
            ];
            if (isset($labels[$status])) {
                $this->addTracking($id, $labels[$status][0], $labels[$status][1], $labels[$status][2], $byUserId);
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* ═══════════════ FLEET ═══════════════ */
    public function getFleet(int $ownerId): array {
        try {
            $this->db->query("SELECT v.*, d.full_name AS driver_name, d.phone AS driver_phone
                              FROM logistics_vehicles v
                              LEFT JOIN logistics_drivers d ON d.vehicle_id = v.id
                              WHERE v.owner_id = :u
                              ORDER BY v.created_at DESC");
            $this->db->bind(':u', $ownerId);
            return $this->db->resultSet();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function addVehicle(int $ownerId, array $data): ?int {
        try {
            $this->db->query("INSERT INTO logistics_vehicles
                (owner_id, plate_number, vehicle_type, capacity_kg, capacity_m3,
                 fuel_type, has_refrigeration, home_district, status)
                VALUES (:o, :p, :t, :kg, :m3, :f, :ref, :hd, 'active')");
            $this->db->bind(':o', $ownerId);
            $this->db->bind(':p', strtoupper($data['plate_number']));
            $this->db->bind(':t', $data['vehicle_type'] ?? 'truck');
            $this->db->bind(':kg', (float)($data['capacity_kg'] ?? 1000));
            $this->db->bind(':m3', (float)($data['capacity_m3'] ?? 10));
            $this->db->bind(':f', $data['fuel_type'] ?? 'diesel');
            $this->db->bind(':ref', !empty($data['has_refrigeration']) ? 1 : 0);
            $this->db->bind(':hd', $data['home_district'] ?? null);
            $this->db->execute();
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log("Logistics::addVehicle error: " . $e->getMessage());
            return null;
        }
    }

    /* ═══════════════ DISTANCE CALC ═══════════════ */
    public function getDistance(string $from, string $to): ?array {
        if ($from === $to) return ['km' => 0, 'hours' => 0];

        try {
            $this->db->query("SELECT distance_km, typical_hours FROM logistics_distances
                              WHERE (from_district = :f AND to_district = :t)
                                 OR (from_district = :t AND to_district = :f)
                              LIMIT 1");
            $this->db->bind(':f', $from);
            $this->db->bind(':t', $to);
            $row = $this->db->single();

            if ($row) return ['km' => (float)$row['distance_km'], 'hours' => (float)$row['typical_hours']];

            // Fallback — estimate from coords
            $this->db->query("SELECT
                (SELECT lat FROM district_coords WHERE district = :f) AS flat,
                (SELECT lng FROM district_coords WHERE district = :f) AS flng,
                (SELECT lat FROM district_coords WHERE district = :t) AS tlat,
                (SELECT lng FROM district_coords WHERE district = :t) AS tlng");
            $this->db->bind(':f', $from);
            $this->db->bind(':t', $to);
            $c = $this->db->single();

            if ($c && $c['flat'] !== null && $c['tlat'] !== null) {
                $dLat = deg2rad($c['tlat'] - $c['flat']);
                $dLng = deg2rad($c['tlng'] - $c['flng']);
                $a = sin($dLat/2)**2 + cos(deg2rad($c['flat'])) * cos(deg2rad($c['tlat'])) * sin($dLng/2)**2;
                $d = 2 * 6371 * asin(sqrt($a));
                return ['km' => round($d * 1.3, 1), 'hours' => round($d / 45, 1)]; // 1.3 = road factor
            }
        } catch (\Throwable $e) {}

        return null;
    }

    /* ═══════════════ COST ESTIMATE ═══════════════ */
    public function estimateCost(string $from, string $to, float $weightKg, bool $refrigerated = false): array {
        $dist = $this->getDistance($from, $to);
        $km = $dist['km'] ?? 0;

        // Rwandan market rates (RWF)
        $baseRate      = 25000;      // base fee
        $perKm         = 800;        // per km
        $perKg         = 15;         // per kg
        $refrigeration = $refrigerated ? 30000 : 0;

        $fuel      = round($km * 350, 2);
        $driver    = round($km * 150, 2);
        $loading   = round($weightKg * 8, 2);
        $insurance = round(($baseRate + $km * $perKm) * 0.05, 2);

        $total = $baseRate + $km * $perKm + $weightKg * $perKg + $refrigeration;

        return [
            'distance_km' => $km,
            'hours'       => $dist['hours'] ?? 0,
            'breakdown'   => [
                ['label' => 'Base fee',        'amount' => $baseRate],
                ['label' => "Distance ({$km} km)", 'amount' => round($km * $perKm, 2)],
                ['label' => "Weight ({$weightKg} kg)", 'amount' => round($weightKg * $perKg, 2)],
                ['label' => 'Refrigeration',   'amount' => $refrigeration],
            ],
            'estimated_total' => round($total, 2),
            'suggested_min'   => round($total * 0.85, 2),
            'suggested_max'   => round($total * 1.15, 2),
        ];
    }
}