<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Helpers\ReportGenerator;
use App\Models\NisrDataset;
use App\Models\NisrAnalytics;

class NisrController extends Controller {
    private $dataset;
    private $analytics;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        if (!in_array($_SESSION['role_id'], [3, 4, 6, 7])) {
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }
        $this->dataset = new NisrDataset();
        $this->analytics = new NisrAnalytics();
    }

    /* ============ PORTAL HOME ============ */
    public function index() {
        $data = [
            'title'    => 'NISR Data Analysis Portal',
            'name'     => $_SESSION['name'],
            'stats'    => $this->dataset->getStats(),
            'datasets' => $this->dataset->all(['status' => 'published']),
            'recent'   => $this->analytics->getSavedAnalyses($_SESSION['user_id']),
        ];
        $this->view('nisr/index', $data);
    }

    /* ============ DATASET BROWSER ============ */
    public function datasets() {
        $filters = [
            'survey_type' => $_GET['type'] ?? null,
            'year'        => $_GET['year'] ?? null,
            'search'      => $_GET['q'] ?? null,
        ];
        $data = [
            'title'    => 'Dataset Registry',
            'name'     => $_SESSION['name'],
            'datasets' => $this->dataset->all($filters),
            'filters'  => $filters,
        ];
        $this->view('nisr/datasets', $data);
    }

    /* ============ DATASET DETAIL ============ */
    public function dataset($id) {
        $ds = $this->dataset->find($id);
        if (!$ds) { header("Location: " . BASE_URL . "/nisr/datasets"); exit; }

        $data = [
            'title'      => $ds['name'],
            'name'       => $_SESSION['name'],
            'dataset'    => $ds,
            'quality'    => $this->dataset->getQuality($id),
            'preview'    => $this->dataset->getRows($id, 20, 0),
            'schema'     => json_decode($ds['columns_schema'], true) ?? [],
        ];
        $this->view('nisr/dataset_detail', $data);
    }

    /* ============ UPLOAD DATASET ============ */
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrf();

            if (empty($_FILES['file']['name'])) {
                $_SESSION['flash_error'] = 'Please select a file.';
                header("Location: " . BASE_URL . "/nisr/uploadForm");
                exit;
            }

            $file = $_FILES['file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, ['csv', 'xlsx', 'xls', 'pdf'])) {
                $_SESSION['flash_error'] = 'Only CSV, Excel, and PDF files are allowed.';
                header("Location: " . BASE_URL . "/nisr/uploadForm");
                exit;
            }

            $dir = __DIR__ . '/../../public/uploads/nisr/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = uniqid('nisr_', true) . '.' . $ext;
            $path = $dir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $path)) {
                $_SESSION['flash_error'] = 'File upload failed.';
                header("Location: " . BASE_URL . "/nisr/uploadForm");
                exit;
            }

            /* ═══════════ PARSE THE FILE ═══════════ */
            $rows = [];
            $schema = [];

            if ($ext === 'csv') {
                $result = $this->parseCsv($path);
                $rows   = $result['rows'];
                $schema = $result['schema'];
            } else {
                // Excel / PDF parsers — placeholder
                // Install PhpSpreadsheet / Smalot\PdfParser via Composer
                $_SESSION['flash_error'] = 'Excel and PDF parsing requires additional libraries. Please upload a CSV file.';
                @unlink($path);
                header("Location: " . BASE_URL . "/nisr/uploadForm");
                exit;
            }

            /* ═══════════ VALIDATE ═══════════ */
            if (empty($schema) || empty($rows)) {
                $_SESSION['flash_error'] = 'Could not parse any data from the file. Please verify the format.';
                @unlink($path);
                header("Location: " . BASE_URL . "/nisr/uploadForm");
                exit;
            }

            /* ═══════════ DETECT NUMERIC COLUMNS ═══════════ */
            foreach ($schema as $i => $col) {
                $values = array_filter(
                    array_column($rows, $col['name']),
                    fn($v) => $v !== '' && $v !== null
                );
                $sample = array_slice(array_values($values), 0, 50);
                $numeric = array_filter($sample, 'is_numeric');

                if (count($sample) > 0 && count($numeric) >= count($sample) * 0.7) {
                    $schema[$i]['type'] = 'number';
                }
            }

            /* ═══════════ SAVE DATASET ═══════════ */
            $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $_POST['name']));
            $datasetId = $this->dataset->create([
                'name'           => Security::clean($_POST['name'], 200),
                'slug'           => $slug . '-' . time(),
                'description'    => Security::clean($_POST['description'] ?? '', 1000),
                'survey_type'    => $_POST['survey_type'] ?? 'custom',
                'source'         => Security::clean($_POST['source'] ?? 'NISR', 100),
                'year'           => (int)($_POST['year'] ?? date('Y')),
                'columns_schema' => $schema,
                'file_path'      => 'uploads/nisr/' . $filename,
                'uploaded_by'    => $_SESSION['user_id'],
                'is_public'      => isset($_POST['is_public']) ? 1 : 0,
            ]);

            $count = $this->dataset->insertRows($datasetId, $rows);
            $this->dataset->updateStatus($datasetId, 'published', $count);

            if (!empty($_POST['compute_quality'])) {
                $this->dataset->computeQuality($datasetId);
            }

            $_SESSION['flash_success'] = "Dataset '<strong>" . htmlspecialchars($_POST['name']) . "</strong>' uploaded successfully with " . number_format($count) . " rows and " . count($schema) . " columns.";

            header("Location: " . BASE_URL . "/nisr/dataset/" . $datasetId);
            exit;
        }

        $this->view('nisr/upload', ['title' => 'Upload Dataset', 'name' => $_SESSION['name']]);
    }

    public function uploadForm() {
        $this->view('nisr/upload', ['title' => 'Upload Dataset', 'name' => $_SESSION['name']]);
    }

    /* ═══════════════════════════════════════════════════════════
       ⭐ SMART CSV PARSER
       - Skips empty leading rows
       - Auto-detects header row
       - Cleans empty / duplicate column names
       - Skips empty data rows
       ═══════════════════════════════════════════════════════════ */
    private function parseCsv($path) {
        $schema = [];
        $rows = [];

        $content = file_get_contents($path);
        if ($content === false) return ['schema' => [], 'rows' => []];

        // Strip UTF-8 BOM
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }

        // Convert UTF-16 → UTF-8 if detected
        if (substr($content, 0, 2) === "\xFF\xFE") {
            $content = mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16LE');
        } elseif (substr($content, 0, 2) === "\xFE\xFF") {
            $content = mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16BE');
        }

        // Auto-detect delimiter
        $firstLine = strtok($content, "\n");
        $counts = [',' => 0, ';' => 0, "\t" => 0, '|' => 0];
        foreach ($counts as $d => $n) {
            $counts[$d] = substr_count($firstLine, $d);
        }
        arsort($counts);
        $delimiter = array_key_first($counts);

        // Write sanitized content to temp file and parse
        $tmp = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($tmp, $content);

        $allLines = [];
        if (($handle = fopen($tmp, 'r')) !== false) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $allLines[] = $row;
            }
            fclose($handle);
        }
        @unlink($tmp);

        // Find header row — first row with at least 2 non-empty cells
        $headers = null;
        $headerIndex = 0;
        foreach ($allLines as $i => $line) {
            $nonEmpty = array_filter($line, function ($c) {
                return trim((string)$c) !== '';
            });
            if (count($nonEmpty) >= 2) {
                $headers = $line;
                $headerIndex = $i;
                break;
            }
        }

        if ($headers === null) {
            return ['schema' => [], 'rows' => []];
        }

        // Clean header names
        $seen = [];
        foreach ($headers as $i => $h) {
            $name = trim((string)$h);
            $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name);
            if ($name === '') {
                $name = 'column_' . ($i + 1);
            }
            // Handle duplicates
            $base = $name;
            $suffix = 1;
            while (in_array($name, $seen)) {
                $suffix++;
                $name = $base . '_' . $suffix;
            }
            $seen[] = $name;
            $schema[] = ['name' => $name, 'type' => 'string'];
        }

        // Read data rows (skip rows before and at header)
        foreach (array_slice($allLines, $headerIndex + 1) as $row) {
            $nonEmpty = array_filter($row, function ($c) {
                return trim((string)$c) !== '';
            });
            if (empty($nonEmpty)) continue;

            // Pad short rows, trim long rows
            while (count($row) < count($schema)) $row[] = '';
            $row = array_slice($row, 0, count($schema));

            $rows[] = array_combine(array_column($schema, 'name'), $row);
        }

        return ['schema' => $schema, 'rows' => $rows];
    }

    /* ============ STATISTICAL EXPLORER (PIVOT) ============ */
    public function explorer($id = null) {
        $datasets = $this->dataset->all(['status' => 'published']);
        $selectedId = $id ?? ($datasets[0]['id'] ?? null);
        $dataset = $selectedId ? $this->dataset->find($selectedId) : null;
        $schema = $dataset ? (json_decode($dataset['columns_schema'], true) ?? []) : [];

        $pivot = null;
        $rowField = $_GET['row'] ?? null;
        $colField = $_GET['col'] ?? null;
        $valField = $_GET['val'] ?? null;
        $agg = $_GET['agg'] ?? 'sum';

        if ($dataset && $rowField && $colField && $valField) {
            $start = microtime(true);
            $pivot = $this->analytics->pivot($selectedId, $rowField, $colField, $valField, $agg);
            $this->analytics->log(
                $_SESSION['user_id'],
                'pivot',
                $selectedId,
                compact('rowField', 'colField', 'valField', 'agg'),
                (int)((microtime(true) - $start) * 1000),
                count($pivot['rows'])
            );
        }

        $data = [
            'title'      => 'Statistical Explorer',
            'name'       => $_SESSION['name'],
            'datasets'   => $datasets,
            'dataset'    => $dataset,
            'schema'     => $schema,
            'pivot'      => $pivot,
            'row_field'  => $rowField,
            'col_field'  => $colField,
            'val_field'  => $valField,
            'agg'        => $agg,
        ];
        $this->view('nisr/explorer', $data);
    }

    /* ============ CHART BUILDER ============ */
    public function chartBuilder($id = null) {
        $datasets = $this->dataset->all(['status' => 'published']);
        $selectedId = $id ?? ($datasets[0]['id'] ?? null);
        $dataset = $selectedId ? $this->dataset->find($selectedId) : null;

        $schema = [];
        if ($dataset && !empty($dataset['columns_schema'])) {
            $decoded = json_decode($dataset['columns_schema'], true);
            if (is_array($decoded)) {
                $schema = $decoded;
            }
        }

        $data = [
            'title'    => 'Chart Builder',
            'name'     => $_SESSION['name'],
            'datasets' => $datasets,
            'dataset'  => $dataset,
            'schema'   => $schema,
        ];

        $this->view('nisr/chart_builder', $data);
    }

    /* ============ COMPARE ============ */
    public function compare($id = null) {
        $datasets = $this->dataset->all(['status' => 'published']);
        $selectedId = $id ?? ($datasets[0]['id'] ?? null);
        $dataset = $selectedId ? $this->dataset->find($selectedId) : null;
        $schema = $dataset ? (json_decode($dataset['columns_schema'], true) ?? []) : [];

        $comparison = null;
        $dimension = $_GET['dim'] ?? null;
        $metric = $_GET['metric'] ?? null;
        $agg = $_GET['agg'] ?? 'sum';

        if ($dataset && $dimension && $metric) {
            $comparison = $this->analytics->compare($selectedId, $dimension, $metric, [], $agg);
        }

        $data = [
            'title'      => 'Compare',
            'name'       => $_SESSION['name'],
            'datasets'   => $datasets,
            'dataset'    => $dataset,
            'schema'     => $schema,
            'comparison' => $comparison,
            'dimension'  => $dimension,
            'metric'     => $metric,
            'agg'        => $agg,
        ];
        $this->view('nisr/compare', $data);
    }

    /* ============ TIME SERIES ============ */
    public function timeseries($id = null) {
        $datasets = $this->dataset->all(['status' => 'published']);
        $selectedId = $id ?? ($datasets[0]['id'] ?? null);
        $dataset = $selectedId ? $this->dataset->find($selectedId) : null;
        $schema = $dataset ? (json_decode($dataset['columns_schema'], true) ?? []) : [];

        $series = null;
        $dateField = $_GET['date'] ?? null;
        $metric = $_GET['metric'] ?? null;
        $agg = $_GET['agg'] ?? 'sum';
        $smooth = (int)($_GET['smooth'] ?? 0);

        if ($dataset && $dateField && $metric) {
            $series = $this->analytics->timeseries($selectedId, $dateField, $metric, $agg, $smooth);
        }

        $data = [
            'title'      => 'Time Series',
            'name'       => $_SESSION['name'],
            'datasets'   => $datasets,
            'dataset'    => $dataset,
            'schema'     => $schema,
            'series'     => $series,
            'date_field' => $dateField,
            'metric'     => $metric,
            'agg'        => $agg,
            'smooth'     => $smooth,
        ];
        $this->view('nisr/timeseries', $data);
    }

    /* ============ DATA QUALITY ============ */
    public function quality($id) {
        $ds = $this->dataset->find($id);
        if (!$ds) { header("Location: " . BASE_URL . "/nisr/datasets"); exit; }

        $data = [
            'title'   => 'Data Quality — ' . $ds['name'],
            'name'    => $_SESSION['name'],
            'dataset' => $ds,
            'quality' => $this->dataset->getQuality($id),
        ];
        $this->view('nisr/quality', $data);
    }

    public function recomputeQuality($id) {
        $this->dataset->computeQuality($id);
        $_SESSION['flash_success'] = 'Data quality metrics have been recomputed.';
        header("Location: " . BASE_URL . "/nisr/quality/" . $id);
        exit;
    }

    /* ============ PUBLICATIONS ============ */
    public function publications() {
        $db = new \App\Core\Database();
        $db->query("SELECT p.*, u.first_name, u.last_name FROM nisr_publications p
                    LEFT JOIN users u ON u.id = p.created_by
                    ORDER BY p.created_at DESC");
        $data = [
            'title' => 'NISR Publications',
            'name'  => $_SESSION['name'],
            'publications' => $db->resultSet(),
        ];
        $this->view('nisr/publications', $data);
    }

    /* ============ API DOCS ============ */
    public function apiDocs() {
        $db = new \App\Core\Database();
        $db->query("SELECT * FROM nisr_api_tokens WHERE user_id = :u ORDER BY created_at DESC");
        $db->bind(':u', $_SESSION['user_id']);
        $data = [
            'title' => 'Public Data API',
            'name'  => $_SESSION['name'],
            'tokens' => $db->resultSet(),
        ];
        $this->view('nisr/api_docs', $data);
    }

    public function createApiToken() {
        header('Content-Type: application/json');
        $token = $this->analytics->createApiToken($_SESSION['user_id'], $_POST['label'] ?? 'Default');
        echo json_encode(['success' => true, 'token' => $token]);
        exit;
    }

    /* ============ PUBLIC API ============ */
    public function apiDatasets() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        $token = $_GET['token'] ?? $_SERVER['HTTP_X_API_TOKEN'] ?? null;
        if (!$token || !$this->analytics->validateApiToken($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or missing API token']);
            exit;
        }
        $all = $this->dataset->all(['status' => 'published']);
        echo json_encode(['datasets' => $all, 'count' => count($all)]);
        exit;
    }

    public function apiDataset($slug) {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        $token = $_GET['token'] ?? $_SERVER['HTTP_X_API_TOKEN'] ?? null;
        if (!$token || !$this->analytics->validateApiToken($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or missing API token']);
            exit;
        }
        $ds = $this->dataset->findBySlug($slug);
        if (!$ds || !$ds['is_public']) {
            http_response_code(404);
            echo json_encode(['error' => 'Dataset not found or not public']);
            exit;
        }
        $limit = min((int)($_GET['limit'] ?? 100), 1000);
        $offset = (int)($_GET['offset'] ?? 0);
        $rows = $this->dataset->getRows($ds['id'], $limit, $offset);
        echo json_encode([
            'dataset' => [
                'name' => $ds['name'],
                'survey_type' => $ds['survey_type'],
                'year' => $ds['year'],
                'row_count' => $ds['row_count'],
                'schema' => json_decode($ds['columns_schema'], true),
            ],
            'pagination' => ['limit' => $limit, 'offset' => $offset],
            'data' => $rows,
        ]);
        exit;
    }

    /* ============ PIVOT API (for Chart Builder) ============ */
    public function apiPivot() {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        if (ob_get_level() > 0) @ob_clean();

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated.']);
            exit;
        }

        $datasetId = (int)($_GET['datasetId'] ?? 0);
        $row = trim($_GET['row'] ?? '');
        $col = trim($_GET['col'] ?? '');
        $val = trim($_GET['val'] ?? '');
        $agg = $_GET['agg'] ?? 'sum';

        if (!$datasetId || $row === '' || $col === '' || $val === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
            exit;
        }

        if (!in_array($agg, ['sum', 'avg', 'count', 'min', 'max'], true)) {
            $agg = 'sum';
        }

        try {
            $pivot = $this->analytics->pivot($datasetId, $row, $col, $val, $agg);
            echo json_encode($pivot ?: ['rows' => [], 'columns' => [], 'data' => []]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    /* ============ EXPORT ============ */
    public function exportPivot() {
        $id = (int)($_GET['dataset'] ?? 0);
        $ds = $this->dataset->find($id);
        if (!$ds) { http_response_code(404); exit; }

        $pivot = $this->analytics->pivot(
            $id,
            $_GET['row'] ?? '',
            $_GET['col'] ?? '',
            $_GET['val'] ?? '',
            $_GET['agg'] ?? 'sum'
        );

        $headers = array_merge([$_GET['row']], $pivot['columns']);
        $rows = [];
        foreach ($pivot['rows'] as $rk) {
            $row = [$rk];
            foreach ($pivot['columns'] as $ck) {
                $row[] = $pivot['data'][$rk][$ck] ?? 0;
            }
            $rows[] = $row;
        }

        $format = $_GET['format'] ?? 'csv';
        $filename = 'nisr_pivot_' . date('Ymd');
        if ($format === 'excel') {
            ReportGenerator::excel($filename, $ds['name'] . ' — Pivot', $headers, $rows);
        } else {
            ReportGenerator::csv($filename, $headers, $rows);
        }
    }

    public function exportReport() {
        $id = (int)($_GET['dataset'] ?? 0);
        $ds = $this->dataset->find($id);
        if (!$ds) { http_response_code(404); exit; }

        $quality = $this->dataset->getQuality($id);
        $schema = json_decode($ds['columns_schema'], true) ?? [];

        $kpis = [
            'Dataset'     => $ds['name'],
            'Survey Type' => strtoupper($ds['survey_type']),
            'Year'        => $ds['year'],
            'Rows'        => number_format($ds['row_count']),
            'Columns'     => count($schema),
            'Source'      => $ds['source'],
        ];

        $sections = [
            [
                'title' => 'Data Quality Report',
                'table' => [
                    'headers' => ['Column', 'Type', 'Missing %', 'Unique', 'Min', 'Max', 'Mean'],
                    'rows' => array_map(fn($q) => [
                        $q['column_name'],
                        $q['data_type'],
                        $q['missing_pct'] . '%',
                        $q['unique_count'],
                        $q['min_value'] ?? '—',
                        $q['max_value'] ?? '—',
                        $q['mean_value'] ? round($q['mean_value'], 2) : '—',
                    ], $quality),
                ],
            ],
        ];

        $html = ReportGenerator::html('NISR Dataset Report — ' . $ds['name'], $kpis, $sections);
        echo $html;
        exit;
    }
}