<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class GeoController extends Controller {

    /* ═══════════════════════════════════════════════════════════════
       JSON HELPER — clean output every time
       ═══════════════════════════════════════════════════════════════ */
    private function json($data, $code = 200) {
        if (ob_get_level() > 0) @ob_clean();
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function db() {
        return new Database();
    }

    private function requireParam($key, $type = 'int') {
        $val = $_GET[$key] ?? $_POST[$key] ?? null;
        if ($val === null || $val === '') {
            $this->json([
                'success' => false,
                'error'   => "Missing required parameter: {$key}",
            ], 400);
        }
        return $type === 'int' ? (int)$val : trim((string)$val);
    }

    /* ═══════════════════════════════════════════════════════════════
       PROVINCES — list all
       ═══════════════════════════════════════════════════════════════ */
    public function provinces() {
        try {
            $db = $this->db();
            $db->query("SELECT id, code, name 
                        FROM provinces 
                        ORDER BY name");
            $rows = $db->resultSet();

            $this->json([
                'success'   => true,
                'provinces' => $rows,
                'count'     => count($rows),
            ]);
        } catch (\Throwable $e) {
            error_log("GeoController::provinces error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       DISTRICTS — filter by province_id (optional)
       ═══════════════════════════════════════════════════════════════ */
    public function districts() {
        try {
            $provinceId = (int)($_GET['province_id'] ?? 0);
            $db = $this->db();

            if ($provinceId) {
                $db->query("SELECT id, name, lat, lng, province_id 
                            FROM districts 
                            WHERE province_id = :p 
                            ORDER BY name");
                $db->bind(':p', $provinceId);
            } else {
                $db->query("SELECT id, name, lat, lng, province_id 
                            FROM districts 
                            ORDER BY name");
            }

            $rows = $db->resultSet();

            $this->json([
                'success'   => true,
                'districts' => $rows,
                'count'     => count($rows),
            ]);
        } catch (\Throwable $e) {
            error_log("GeoController::districts error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       SECTORS — filter by district_id
       ═══════════════════════════════════════════════════════════════ */
    public function sectors() {
        try {
            $districtId = $this->requireParam('district_id', 'int');
            $db = $this->db();

            $db->query("SELECT id, name, district_id 
                        FROM sectors 
                        WHERE district_id = :d 
                        ORDER BY name");
            $db->bind(':d', $districtId);
            $rows = $db->resultSet();

            $this->json([
                'success' => true,
                'sectors' => $rows,
                'count'   => count($rows),
            ]);
        } catch (\Throwable $e) {
            error_log("GeoController::sectors error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       CELLS — filter by sector_id
       ═══════════════════════════════════════════════════════════════ */
    public function cells() {
        try {
            $sectorId = $this->requireParam('sector_id', 'int');
            $db = $this->db();

            $db->query("SELECT id, name, sector_id 
                        FROM cells 
                        WHERE sector_id = :s 
                        ORDER BY name");
            $db->bind(':s', $sectorId);
            $rows = $db->resultSet();

            $this->json([
                'success' => true,
                'cells'   => $rows,
                'count'   => count($rows),
            ]);
        } catch (\Throwable $e) {
            error_log("GeoController::cells error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       VILLAGES — filter by cell_id
       ═══════════════════════════════════════════════════════════════ */
    public function villages() {
        try {
            $cellId = $this->requireParam('cell_id', 'int');
            $db = $this->db();

            $db->query("SELECT id, name, cell_id 
                        FROM villages 
                        WHERE cell_id = :c 
                        ORDER BY name");
            $db->bind(':c', $cellId);
            $rows = $db->resultSet();

            $this->json([
                'success'  => true,
                'villages' => $rows,
                'count'    => count($rows),
            ]);
        } catch (\Throwable $e) {
            error_log("GeoController::villages error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       HIERARCHY — full tree in one request
       Returns: provinces → districts → sectors → cells → villages
       ═══════════════════════════════════════════════════════════════ */
    public function hierarchy() {
        try {
            $db = $this->db();

            // Provinces
            $db->query("SELECT id, code, name FROM provinces ORDER BY name");
            $provinces = $db->resultSet();

            foreach ($provinces as &$p) {
                // Districts for this province
                $db->query("SELECT id, name, lat, lng 
                            FROM districts 
                            WHERE province_id = :pid 
                            ORDER BY name");
                $db->bind(':pid', $p['id']);
                $p['districts'] = $db->resultSet();

                // Sectors for each district (limited to keep payload small)
                foreach ($p['districts'] as &$d) {
                    $db->query("SELECT id, name 
                                FROM sectors 
                                WHERE district_id = :did 
                                ORDER BY name");
                    $db->bind(':did', $d['id']);
                    $d['sectors'] = $db->resultSet();
                }
                unset($d);
            }
            unset($p);

            $this->json([
                'success'   => true,
                'provinces' => $provinces,
                'timestamp' => date('c'),
            ]);

        } catch (\Throwable $e) {
            error_log("GeoController::hierarchy error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       AUTOCOMPLETE — search districts by name
       ═══════════════════════════════════════════════════════════════ */
    public function search() {
        try {
            $q = trim($_GET['q'] ?? '');
            if (mb_strlen($q) < 2) {
                $this->json(['success' => true, 'results' => []]);
            }

            $db = $this->db();
            $db->query("SELECT id, name, lat, lng 
                        FROM districts 
                        WHERE name LIKE :q 
                        ORDER BY name 
                        LIMIT 10");
            $db->bind(':q', '%' . $q . '%');
            $rows = $db->resultSet();

            $this->json([
                'success' => true,
                'results' => $rows,
                'query'   => $q,
            ]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════════
       NEAREST — find nearest district to lat/lng
       ═══════════════════════════════════════════════════════════════ */
    public function nearest() {
        try {
            $lat = (float)($_GET['lat'] ?? 0);
            $lng = (float)($_GET['lng'] ?? 0);

            if (!$lat || !$lng) {
                $this->json(['success' => false, 'error' => 'Missing lat/lng'], 400);
            }

            $db = $this->db();
            $db->query("SELECT id, name, lat, lng,
                        (POW(lat - :lat, 2) + POW(lng - :lng, 2)) AS dist
                        FROM districts
                        WHERE lat IS NOT NULL AND lng IS NOT NULL
                        ORDER BY dist ASC LIMIT 1");
            $db->bind(':lat', $lat);
            $db->bind(':lng', $lng);
            $row = $db->single();

            if ($row) {
                $this->json([
                    'success'  => true,
                    'district' => $row,
                    'distance' => sqrt((float)$row['dist']) * 111, // rough km
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'No district found'], 404);
            }
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}