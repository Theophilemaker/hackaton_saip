<?php
namespace App\Models;

use App\Core\Database;
use App\Helpers\NisrEngine;

class NisrAnalytics {

    private $db;
    private $dataset;
    private $engine;

    public function __construct() {
        $this->db      = new Database();
        $this->dataset = new NisrDataset();
        $this->engine  = new NisrEngine();
    }

    /* ═══════════════════════════════════════════════════════════════
       PIVOT / CROSSTAB
       Returns: ['rows', 'columns', 'data', 'row_field', 'col_field',
                 'value_field', 'agg']
       Uses:    C++ engine (fast) → PHP fallback (slow)
       ═══════════════════════════════════════════════════════════════ */
    public function pivot($datasetId, $rowField, $colField, $valueField, $agg = 'sum') {

        // ⚡ FAST PATH: C++ engine
        if ($this->engine->isAvailable()) {
            $csv = $this->getCsvPath($datasetId);
            if ($csv) {
                $result = $this->engine->pivot($csv, $rowField, $colField, $valueField, $agg);
                if ($result !== null && isset($result['rows'], $result['columns'], $result['data'])) {
                    // Strip debug metadata
                    unset($result['_engine_ms']);

                    // Add the extra keys the PHP version returns
                    $result['row_field']   = $rowField;
                    $result['col_field']   = $colField;
                    $result['value_field'] = $valueField;
                    $result['agg']         = $agg;

                    return $result;
                }
            }
        }

        // 🐢 FALLBACK: existing PHP implementation
        return $this->pivotPhp($datasetId, $rowField, $colField, $valueField, $agg);
    }

    private function pivotPhp($datasetId, $rowField, $colField, $valueField, $agg = 'sum') {
        $rows = $this->dataset->getAllRows($datasetId);

        $pivot = [];
        $rowKeys = [];
        $colKeys = [];

        foreach ($rows as $r) {
            $rk  = $r[$rowField] ?? 'Unknown';
            $ck  = $r[$colField] ?? 'Unknown';
            $val = isset($r[$valueField]) && is_numeric($r[$valueField])
                 ? (float)$r[$valueField] : 0;

            if (!isset($pivot[$rk]))         $pivot[$rk] = [];
            if (!isset($pivot[$rk][$ck]))    $pivot[$rk][$ck] = [];
            $pivot[$rk][$ck][] = $val;

            $rowKeys[$rk] = true;
            $colKeys[$ck] = true;
        }

        $result = [];
        foreach (array_keys($rowKeys) as $rk) {
            $result[$rk] = [];
            foreach (array_keys($colKeys) as $ck) {
                $vals = $pivot[$rk][$ck] ?? [];
                $result[$rk][$ck] = $this->aggregate($vals, $agg);
            }
        }

        return [
            'rows'        => array_keys($rowKeys),
            'columns'     => array_keys($colKeys),
            'data'        => $result,
            'row_field'   => $rowField,
            'col_field'   => $colField,
            'value_field' => $valueField,
            'agg'         => $agg,
        ];
    }

    private function aggregate($values, $agg) {
        if (empty($values)) return 0;
        return match($agg) {
            'sum'   => array_sum($values),
            'avg'   => array_sum($values) / count($values),
            'count' => count($values),
            'min'   => min($values),
            'max'   => max($values),
            default => array_sum($values),
        };
    }

    /* ═══════════════════════════════════════════════════════════════
       COMPARISON (district vs district, year vs year)
       Returns: ['data' => [...], 'delta' => ['first','second','absolute','percent']]
       ═══════════════════════════════════════════════════════════════ */
    public function compare($datasetId, $dimension, $metric, $values = [], $agg = 'sum') {

        // ⚡ FAST PATH: C++ engine (only when no value filter is applied)
        if (empty($values) && $this->engine->isAvailable()) {
            $csv = $this->getCsvPath($datasetId);
            if ($csv) {
                $result = $this->engine->compare($csv, $dimension, $metric, $agg);
                if ($result !== null && isset($result['data'])) {
                    unset($result['_engine_ms']);

                    // Add 'absolute' to delta (C++ only returns first/second/percent)
                    if (!empty($result['delta'])) {
                        $d = $result['delta'];
                        $firstVal  = $result['data'][$d['first']]  ?? 0;
                        $secondVal = $result['data'][$d['second']] ?? 0;
                        $result['delta']['absolute'] = $firstVal - $secondVal;
                    }

                    return $result;
                }
            }
        }

        // 🐢 FALLBACK
        return $this->comparePhp($datasetId, $dimension, $metric, $values, $agg);
    }

    private function comparePhp($datasetId, $dimension, $metric, $values = [], $agg = 'sum') {
        $rows = $this->dataset->getAllRows($datasetId);

        $grouped = [];
        foreach ($rows as $r) {
            $key = $r[$dimension] ?? 'Unknown';
            if (!empty($values) && !in_array($key, $values)) continue;

            $val = isset($r[$metric]) && is_numeric($r[$metric])
                 ? (float)$r[$metric] : 0;
            if (!isset($grouped[$key])) $grouped[$key] = [];
            $grouped[$key][] = $val;
        }

        $result = [];
        foreach ($grouped as $k => $vals) {
            $result[$k] = $this->aggregate($vals, $agg);
        }
        arsort($result);

        $delta = null;
        if (count($result) === 2) {
            $keys = array_keys($result);
            $a = $result[$keys[0]];
            $b = $result[$keys[1]];
            $delta = [
                'first'    => $keys[0],
                'second'   => $keys[1],
                'absolute' => $a - $b,
                'percent'  => $b != 0 ? round((($a - $b) / $b) * 100, 2) : 0,
            ];
        }

        return ['data' => $result, 'delta' => $delta];
    }

    /* ═══════════════════════════════════════════════════════════════
       TIME SERIES
       Returns: ['points' => [['x','y'], ...], 'smoothed' => [...]]
       ═══════════════════════════════════════════════════════════════ */
    public function timeseries($datasetId, $dateField, $metric, $agg = 'sum', $smooth = 0) {

        // ⚡ FAST PATH
        if ($this->engine->isAvailable()) {
            $csv = $this->getCsvPath($datasetId);
            if ($csv) {
                $result = $this->engine->timeseries($csv, $dateField, $metric, $agg, (int)$smooth);
                if ($result !== null && isset($result['points'])) {
                    unset($result['_engine_ms']);
                    // Ensure 'smoothed' key always exists (C++ omits it when smooth=0)
                    if (!isset($result['smoothed'])) $result['smoothed'] = [];
                    return $result;
                }
            }
        }

        // 🐢 FALLBACK
        return $this->timeseriesPhp($datasetId, $dateField, $metric, $agg, $smooth);
    }

    private function timeseriesPhp($datasetId, $dateField, $metric, $agg = 'sum', $smooth = 0) {
        $rows = $this->dataset->getAllRows($datasetId);

        $series = [];
        foreach ($rows as $r) {
            $t = $r[$dateField] ?? null;
            if ($t === null) continue;
            $val = isset($r[$metric]) && is_numeric($r[$metric])
                 ? (float)$r[$metric] : 0;
            if (!isset($series[$t])) $series[$t] = [];
            $series[$t][] = $val;
        }

        ksort($series);

        $points = [];
        foreach ($series as $t => $vals) {
            $points[] = ['x' => $t, 'y' => $this->aggregate($vals, $agg)];
        }

        if ($smooth > 0 && count($points) > $smooth) {
            $smoothed = [];
            for ($i = 0; $i < count($points); $i++) {
                if ($i < $smooth - 1) { $smoothed[] = null; continue; }
                $window = array_slice($points, $i - $smooth + 1, $smooth);
                $smoothed[] = array_sum(array_column($window, 'y')) / $smooth;
            }
            return ['points' => $points, 'smoothed' => $smoothed];
        }

        return ['points' => $points, 'smoothed' => []];
    }

    /* ═══════════════════════════════════════════════════════════════
       DISTRIBUTION (histogram)
       Note: no C++ equivalent — stays PHP-only
       ═══════════════════════════════════════════════════════════════ */
    public function distribution($datasetId, $field, $bins = 10) {
        $rows = $this->dataset->getAllRows($datasetId);

        $values = [];
        foreach ($rows as $r) {
            if (isset($r[$field]) && is_numeric($r[$field])) {
                $values[] = (float)$r[$field];
            }
        }

        if (empty($values)) return ['bins' => [], 'counts' => []];

        $min     = min($values);
        $max     = max($values);
        $range   = $max - $min;
        $binSize = $range / $bins ?: 1;

        $counts = array_fill(0, $bins, 0);
        foreach ($values as $v) {
            $idx = min((int)(($v - $min) / $binSize), $bins - 1);
            $counts[$idx]++;
        }

        $labels = [];
        for ($i = 0; $i < $bins; $i++) {
            $labels[] = round($min + $i * $binSize, 2)
                      . '–'
                      . round($min + ($i + 1) * $binSize, 2);
        }

        return [
            'bins'   => $labels,
            'counts' => $counts,
            'min'    => $min,
            'max'    => $max,
            'mean'   => array_sum($values) / count($values),
        ];
    }

    /* ═══════════════════════════════════════════════════════════════
       LOG ANALYSIS  (unchanged)
       ═══════════════════════════════════════════════════════════════ */
    public function log($userId, $type, $datasetId, $params, $duration, $rowCount) {
        $this->db->query("INSERT INTO nisr_analysis_logs
            (user_id, analysis_type, dataset_id, query_params, execution_time_ms, row_count)
            VALUES (:u, :t, :d, :p, :dur, :rc)");
        $this->db->bind(':u',   $userId);
        $this->db->bind(':t',   $type);
        $this->db->bind(':d',   $datasetId);
        $this->db->bind(':p',   json_encode($params));
        $this->db->bind(':dur', $duration);
        $this->db->bind(':rc',  $rowCount);
        return $this->db->execute();
    }

    /* ═══════════════════════════════════════════════════════════════
       SAVED ANALYSES (unchanged)
       ═══════════════════════════════════════════════════════════════ */
    public function saveAnalysis($data) {
        $this->db->query("INSERT INTO nisr_saved_analyses
            (uuid, title, description, analysis_type, dataset_id, config, filters, is_public, created_by)
            VALUES (:u,:t,:d,:at,:di,:c,:f,:ip,:cb)");
        $this->db->bind(':u',  uniqid('', true));
        $this->db->bind(':t',  $data['title']);
        $this->db->bind(':d',  $data['description'] ?? '');
        $this->db->bind(':at', $data['analysis_type']);
        $this->db->bind(':di', $data['dataset_id'] ?? null);
        $this->db->bind(':c',  json_encode($data['config']  ?? []));
        $this->db->bind(':f',  json_encode($data['filters'] ?? []));
        $this->db->bind(':ip', $data['is_public'] ?? 0);
        $this->db->bind(':cb', $data['created_by']);
        return $this->db->execute();
    }

    public function getSavedAnalyses($userId = null) {
        $sql = "SELECT sa.*, d.name AS dataset_name, u.first_name, u.last_name
                FROM nisr_saved_analyses sa
                LEFT JOIN nisr_datasets d ON d.id = sa.dataset_id
                LEFT JOIN users u ON u.id = sa.created_by
                WHERE sa.is_public = 1";
        if ($userId) $sql .= " OR sa.created_by = " . (int)$userId;
        $sql .= " ORDER BY sa.created_at DESC LIMIT 50";
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    /* ═══════════════════════════════════════════════════════════════
       CHART CONFIGS (unchanged)
       ═══════════════════════════════════════════════════════════════ */
    public function saveChart($data) {
        $this->db->query("INSERT INTO nisr_chart_configs
            (uuid, title, chart_type, dataset_id, x_field, y_fields, group_by, filters, palette, created_by)
            VALUES (:u,:t,:ct,:d,:xf,:yf,:gb,:fl,:p,:cb)");
        $this->db->bind(':u',  uniqid('', true));
        $this->db->bind(':t',  $data['title']);
        $this->db->bind(':ct', $data['chart_type']);
        $this->db->bind(':d',  $data['dataset_id']);
        $this->db->bind(':xf', $data['x_field']);
        $this->db->bind(':yf', json_encode($data['y_fields'] ?? []));
        $this->db->bind(':gb', $data['group_by'] ?? null);
        $this->db->bind(':fl', json_encode($data['filters']   ?? []));
        $this->db->bind(':p',  $data['palette'] ?? 'green');
        $this->db->bind(':cb', $data['created_by']);
        return $this->db->execute();
    }

    /* ═══════════════════════════════════════════════════════════════
       PUBLIC API TOKENS (unchanged)
       ═══════════════════════════════════════════════════════════════ */
    public function createApiToken($userId, $label) {
        $token = bin2hex(random_bytes(32));
        $this->db->query("INSERT INTO nisr_api_tokens (user_id, token, label)
                          VALUES (:u, :t, :l)");
        $this->db->bind(':u', $userId);
        $this->db->bind(':t', $token);
        $this->db->bind(':l', $label);
        $this->db->execute();
        return $token;
    }

    public function validateApiToken($token) {
        $this->db->query("SELECT * FROM nisr_api_tokens
                          WHERE token = :t AND is_active = 1
                          AND (expires_at IS NULL OR expires_at > NOW())");
        $this->db->bind(':t', $token);
        $row = $this->db->single();

        if ($row) {
            $this->db->query("UPDATE nisr_api_tokens
                              SET calls_made = calls_made + 1, last_used_at = NOW()
                              WHERE id = :id");
            $this->db->bind(':id', $row['id']);
            $this->db->execute();
        }
        return $row;
    }

    /* ═══════════════════════════════════════════════════════════════
       PRIVATE HELPER — resolve the CSV path for a dataset
       (with security: must be inside public/uploads/nisr/)
       ═══════════════════════════════════════════════════════════════ */
    private function getCsvPath($datasetId) {
        $this->db->query("SELECT file_path FROM nisr_datasets WHERE id = :id");
        $this->db->bind(':id', (int)$datasetId);
        $row = $this->db->single();

        if (!$row || empty($row['file_path'])) return null;

        // Base path — works regardless of where BASE_PATH is defined
        $base = defined('BASE_PATH')
              ? BASE_PATH
              : dirname(__DIR__, 2);

        $path = $base . '/public/' . ltrim($row['file_path'], '/');

        // Security: must be inside public/uploads/nisr
        $real    = realpath($path);
        $allowed = realpath($base . '/public/uploads/nisr');

        if (!$real || !$allowed || strpos($real, $allowed) !== 0) {
            error_log("NisrAnalytics: unsafe CSV path rejected: {$path}");
            return null;
        }

        if (!is_file($real)) {
            error_log("NisrAnalytics: CSV file missing: {$real}");
            return null;
        }

        return $real;
    }
}