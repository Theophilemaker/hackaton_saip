<?php
namespace App\Models;

use App\Core\Database;

class NisrDataset {
    private $db;

    public function __construct() { $this->db = new Database(); }

    /* ============ DATASETS ============ */

    public function all($filters = []) {
        $sql = "SELECT d.*, u.first_name, u.last_name FROM nisr_datasets d
                LEFT JOIN users u ON u.id = d.uploaded_by WHERE 1=1";
        $binds = [];

        if (!empty($filters['survey_type'])) {
            $sql .= " AND d.survey_type = :st";
            $binds[':st'] = $filters['survey_type'];
        }
        if (!empty($filters['year'])) {
            $sql .= " AND d.year = :y";
            $binds[':y'] = $filters['year'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND d.status = :stt";
            $binds[':stt'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (d.name LIKE :q OR d.description LIKE :q)";
            $binds[':q'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY d.year DESC, d.created_at DESC";
        $this->db->query($sql);
        foreach ($binds as $k => $v) $this->db->bind($k, $v);
        return $this->db->resultSet();
    }

    public function find($id) {
        $this->db->query("SELECT d.*, u.first_name, u.last_name FROM nisr_datasets d
                          LEFT JOIN users u ON u.id = d.uploaded_by
                          WHERE d.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function findBySlug($slug) {
        $this->db->query("SELECT * FROM nisr_datasets WHERE slug = :s");
        $this->db->bind(':s', $slug);
        return $this->db->single();
    }

    public function create($data) {
        $this->db->query("INSERT INTO nisr_datasets 
            (uuid, name, slug, description, survey_type, source, year, columns_schema,
             file_path, uploaded_by, status, is_public)
            VALUES (:u,:n,:s,:d,:t,:src,:y,:cs,:fp,:ub,'processing',:ip)");
        $this->db->bind(':u', uniqid('', true));
        $this->db->bind(':n', $data['name']);
        $this->db->bind(':s', $data['slug']);
        $this->db->bind(':d', $data['description'] ?? '');
        $this->db->bind(':t', $data['survey_type'] ?? 'custom');
        $this->db->bind(':src', $data['source'] ?? 'NISR');
        $this->db->bind(':y', $data['year'] ?? date('Y'));
        $this->db->bind(':cs', json_encode($data['columns_schema'] ?? []));
        $this->db->bind(':fp', $data['file_path'] ?? null);
        $this->db->bind(':ub', $data['uploaded_by']);
        $this->db->bind(':ip', $data['is_public'] ?? 0);
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    public function updateStatus($id, $status, $rowCount = null) {
        $this->db->query("UPDATE nisr_datasets SET status = :s" . 
            ($rowCount !== null ? ", row_count = :rc" : "") . " WHERE id = :id");
        $this->db->bind(':s', $status);
        $this->db->bind(':id', $id);
        if ($rowCount !== null) $this->db->bind(':rc', $rowCount);
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM nisr_datasets WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /* ============ ROWS ============ */

    public function insertRows($datasetId, $rows) {
        // Bulk insert in batches
        $this->db->query("DELETE FROM nisr_dataset_rows WHERE dataset_id = :d");
        $this->db->bind(':d', $datasetId);
        $this->db->execute();

        $batchSize = 500;
        $count = 0;
        foreach (array_chunk($rows, $batchSize) as $batch) {
            $placeholders = [];
            $binds = [];
            foreach ($batch as $i => $row) {
                $placeholders[] = "(:d{$i}, :ri{$i}, :dt{$i}, :di{$i}, :yr{$i}, :sn{$i})";
                $binds[":d{$i}"]   = $datasetId;
                $binds[":ri{$i}"]  = $count++;
                $binds[":dt{$i}"]  = json_encode($row);
                $binds[":di{$i}"]  = $row['district_id'] ?? null;
                $binds[":yr{$i}"]  = $row['year'] ?? null;
                $binds[":sn{$i}"]  = $row['season'] ?? null;
            }
            $sql = "INSERT INTO nisr_dataset_rows (dataset_id, row_index, data, district_id, year, season) VALUES " . implode(',', $placeholders);
            $this->db->query($sql);
            foreach ($binds as $k => $v) $this->db->bind($k, $v);
            $this->db->execute();
        }
        return $count;
    }

    public function getRows($datasetId, $limit = 100, $offset = 0) {
        $this->db->query("SELECT data FROM nisr_dataset_rows 
                          WHERE dataset_id = :d 
                          ORDER BY row_index 
                          LIMIT :l OFFSET :o");
        $this->db->bind(':d', $datasetId);
        $this->db->bind(':l', $limit);
        $this->db->bind(':o', $offset);
        $rows = $this->db->resultSet();
        return array_map(fn($r) => json_decode($r['data'], true), $rows);
    }

    public function getAllRows($datasetId) {
        $this->db->query("SELECT data FROM nisr_dataset_rows WHERE dataset_id = :d ORDER BY row_index");
        $this->db->bind(':d', $datasetId);
        $rows = $this->db->resultSet();
        return array_map(fn($r) => json_decode($r['data'], true), $rows);
    }

    /* ============ DATA QUALITY ============ */

    public function computeQuality($datasetId) {
        $dataset = $this->find($datasetId);
        if (!$dataset) return false;

        $rows = $this->getAllRows($datasetId);
        if (empty($rows)) return false;

        $schema = json_decode($dataset['columns_schema'], true) ?: [];
        $totalRows = count($rows);

        // Clear old
        $this->db->query("DELETE FROM nisr_data_quality WHERE dataset_id = :d");
        $this->db->bind(':d', $datasetId);
        $this->db->execute();

        $seenNames = []; // Track processed column names to prevent duplicate key errors

        // Analyze each column
        foreach ($schema as $index => $col) {
            $rawName = trim($col['name'] ?? '');
            
            // 1. Sanitize name for database insertion
            $dbColumnName = $rawName;
            if ($dbColumnName === '') {
                $dbColumnName = 'column_' . ($index + 1);
            }
            
            // 2. Handle duplicate column names (e.g., two columns named 'year')
            if (in_array($dbColumnName, $seenNames)) {
                $suffix = 1;
                $newName = $dbColumnName . '_' . $suffix;
                while (in_array($newName, $seenNames)) {
                    $suffix++;
                    $newName = $dbColumnName . '_' . $suffix;
                }
                $dbColumnName = $newName;
            }
            $seenNames[] = $dbColumnName;

            $type = $col['type'] ?? 'string';

            // 3. Extract values robustly (try raw name, numeric index, or sanitized name)
            $values = array_column($rows, $rawName);
            if (empty($values) && $rawName === '') {
                 $values = array_column($rows, $index);
            }
            if (empty($values)) {
                 $values = array_column($rows, $dbColumnName);
            }

            $nonNull = array_filter($values, fn($v) => $v !== null && $v !== '');
            $missing = $totalRows - count($nonNull);

            $stats = [
                'dataset_id'    => $datasetId,
                'column_name'   => $dbColumnName,
                'data_type'     => $type,
                'total_values'  => $totalRows,
                'missing_count' => $missing,
                'missing_pct'   => $totalRows > 0 ? round(($missing / $totalRows) * 100, 2) : 0,
                'unique_count'  => count(array_unique($nonNull)),
            ];

            if ($type === 'number' && !empty($nonNull)) {
                $numeric = array_filter($nonNull, 'is_numeric');
                if (!empty($numeric)) {
                    sort($numeric);
                    $stats['min_value']    = min($numeric);
                    $stats['max_value']    = max($numeric);
                    $stats['mean_value']   = array_sum($numeric) / count($numeric);
                    $stats['median_value'] = $numeric[(int)(count($numeric) / 2)];
                    $stats['std_dev']      = $this->stdDev($numeric);

                    // Outliers (> 2 std devs)
                    $mean = $stats['mean_value'];
                    $sd = $stats['std_dev'];
                    $stats['outlier_count'] = $sd > 0 ? count(array_filter($numeric, fn($v) => abs($v - $mean) > 2 * $sd)) : 0;
                }
            }

            $this->db->query("INSERT INTO nisr_data_quality 
                (dataset_id, column_name, data_type, total_values, missing_count, missing_pct,
                 unique_count, min_value, max_value, mean_value, median_value, std_dev, outlier_count)
                VALUES (:d,:c,:t,:tt,:mc,:mp,:uc,:mn,:mx,:me,:md,:sd,:oc)");
            $this->db->bind(':d', $stats['dataset_id']);
            $this->db->bind(':c', $stats['column_name']);
            $this->db->bind(':t', $stats['data_type']);
            $this->db->bind(':tt', $stats['total_values']);
            $this->db->bind(':mc', $stats['missing_count']);
            $this->db->bind(':mp', $stats['missing_pct']);
            $this->db->bind(':uc', $stats['unique_count']);
            $this->db->bind(':mn', $stats['min_value'] ?? null);
            $this->db->bind(':mx', $stats['max_value'] ?? null);
            $this->db->bind(':me', $stats['mean_value'] ?? null);
            $this->db->bind(':md', $stats['median_value'] ?? null);
            $this->db->bind(':sd', $stats['std_dev'] ?? null);
            $this->db->bind(':oc', $stats['outlier_count'] ?? 0);
            $this->db->execute();
        }
        return true;
    }

    public function getQuality($datasetId) {
        $this->db->query("SELECT * FROM nisr_data_quality WHERE dataset_id = :d ORDER BY column_name");
        $this->db->bind(':d', $datasetId);
        return $this->db->resultSet();
    }

    private function stdDev($arr) {
        $n = count($arr);
        if ($n < 2) return 0;
        $mean = array_sum($arr) / $n;
        $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $arr)) / ($n - 1);
        return sqrt($variance);
    }

    /* ============ STATS ============ */

    public function getStats() {
        $this->db->query("SELECT 
            COUNT(*) AS total,
            SUM(status='published') AS published,
            SUM(is_public=1) AS public_count,
            COALESCE(SUM(row_count),0) AS total_rows
            FROM nisr_datasets");
        return $this->db->single();
    }
}
?>