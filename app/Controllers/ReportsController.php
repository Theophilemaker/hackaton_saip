<?php
namespace App\Controllers;

use App\Core\Controller;

class ReportsController extends Controller {

    public function __construct() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
    }

    /* ═══════════════ INDEX ═══════════════ */
    public function index() {
        $userId = (int)$_SESSION['user_id'];
        $roleId = (int)($_SESSION['role_id'] ?? 1);

        $recent = [];
        try {
            $db = new \App\Core\Database();
            if ($this->tableExists($db, 'reports')) {
                $db->query("SELECT id, name, type, format, created_at, size_bytes
                            FROM reports WHERE user_id = :u
                            ORDER BY created_at DESC LIMIT 20");
                $db->bind(':u', $userId);
                $recent = $db->resultSet() ?: [];
            }
        } catch (\Throwable $e) { /* silent */ }

        $this->view('reports/index', [
            'title'  => 'Reports',
            'name'   => $_SESSION['name'] ?? 'User',
            'recent' => $recent,
            'roleId' => $roleId,
        ]);
    }

    /* ═══════════════ GENERATE — POST ═══════════════ */
    public function generate() {
        try {
            $type   = $_POST['type']   ?? 'summary';
            $format = $_POST['format'] ?? 'csv';
            $from   = $_POST['from']   ?? date('Y-m-d', strtotime('-30 days'));
            $to     = $_POST['to']     ?? date('Y-m-d');
            $userId = (int)$_SESSION['user_id'];

            $result = $this->collectData($type, $userId, $from, $to);

            /* If requested format is not CSV and we have data, still deliver CSV
               (PDF/XLSX generation depends on your ReportGenerator helper). */
            $rows     = $result['rows'];
            $missing  = $result['missing'];

            $ext      = 'csv';
            $filename = sprintf('SAIP_%s_%s_%s.%s',
                $type, date('Ymd', strtotime($from)), date('Ymd', strtotime($to)), $ext);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('X-Report-Missing-Tables: ' . implode(',', $missing));

            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens it correctly

            /* Metadata block */
            fputcsv($out, ['SAIP Report', strtoupper($type)]);
            fputcsv($out, ['Generated', date('Y-m-d H:i:s')]);
            fputcsv($out, ['Period', $from . ' → ' . $to]);
            if (!empty($missing)) {
                fputcsv($out, ['Missing tables (skipped)', implode(', ', $missing)]);
            }
            fputcsv($out, []);

            /* Data */
            if (!empty($rows)) {
                fputcsv($out, array_keys($rows[0]));
                foreach ($rows as $r) fputcsv($out, $r);
            } else {
                fputcsv($out, ['No data available for this report.']);
            }
            fclose($out);
            exit;

        } catch (\Throwable $e) {
            error_log('[Reports] generate: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    /* ═══════════════ Data collector ═══════════════ */
    private function collectData(string $type, int $userId, string $from, string $to): array {
        $db      = new \App\Core\Database();
        $rows    = [];
        $missing = [];

        switch ($type) {

            case 'harvests':
                if ($this->tableExists($db, 'harvests')) {
                    $db->query("SELECT id, crop_id, quantity, unit, harvested_at
                                FROM harvests WHERE user_id = :u
                                AND DATE(harvested_at) BETWEEN :f AND :t
                                ORDER BY harvested_at DESC");
                    $db->bind(':u', $userId);
                    $db->bind(':f', $from);
                    $db->bind(':t', $to);
                    $rows = $db->resultSet() ?: [];
                } else {
                    $missing[] = 'harvests';
                }
                break;

            case 'orders':
                if ($this->tableExists($db, 'orders')) {
                    $db->query("SELECT id, buyer_id, status, total_amount, created_at
                                FROM orders WHERE seller_id = :u
                                AND DATE(created_at) BETWEEN :f AND :t
                                ORDER BY created_at DESC");
                    $db->bind(':u', $userId);
                    $db->bind(':f', $from);
                    $db->bind(':t', $to);
                    $rows = $db->resultSet() ?: [];
                } else {
                    $missing[] = 'orders';
                }
                break;

            case 'users':
                if ($this->tableExists($db, 'users')) {
                    $db->query("SELECT id, first_name, last_name, email, role_id,
                                       status, created_at
                                FROM users
                                WHERE DATE(created_at) BETWEEN :f AND :t
                                ORDER BY created_at DESC");
                    $db->bind(':f', $from);
                    $db->bind(':t', $to);
                    $rows = $db->resultSet() ?: [];
                } else {
                    $missing[] = 'users';
                }
                break;

            case 'crops':
                if ($this->tableExists($db, 'crops')) {
                    $db->query("SELECT id, name, category, growing_days, created_at
                                FROM crops ORDER BY name ASC");
                    $rows = $db->resultSet() ?: [];
                } else {
                    $missing[] = 'crops';
                }
                break;

            case 'summary':
            default:
                /* Query each table SEPARATELY so one missing table doesn't kill the rest */
                $summary = [];

                $checks = [
                    'users'     => "SELECT COUNT(*) AS c FROM users",
                    'farms'     => "SELECT COUNT(*) AS c FROM farms",
                    'harvests'  => "SELECT COUNT(*) AS c FROM harvests",
                    'crops'     => "SELECT COUNT(*) AS c FROM crops",
                    'orders'    => "SELECT COUNT(*) AS c FROM orders",
                    'products'  => "SELECT COUNT(*) AS c FROM products",
                ];

                foreach ($checks as $table => $sql) {
                    if (!$this->tableExists($db, $table)) {
                        $missing[] = $table;
                        continue;
                    }
                    try {
                        $db->query($sql);
                        $r = $db->single();
                        $summary[$table . '_total'] = (int)($r['c'] ?? 0);
                    } catch (\Throwable $e) {
                        $missing[] = $table;
                    }
                }

                /* Production total — only if harvests exists */
                if ($this->tableExists($db, 'harvests')) {
                    try {
                        $db->query("SELECT COALESCE(SUM(quantity),0) AS s FROM harvests");
                        $r = $db->single();
                        $summary['production_total'] = (float)($r['s'] ?? 0);
                    } catch (\Throwable $e) {}
                }

                if (!empty($summary)) {
                    $rows = [$summary];
                }
                break;
        }

        return ['rows' => $rows, 'missing' => $missing];
    }

    /* ═══════════════ Helper — does the table exist? ═══════════════ */
    private function tableExists(\App\Core\Database $db, string $table): bool {
        try {
            $db->query("SELECT 1 FROM information_schema.tables
                        WHERE table_schema = DATABASE() AND table_name = :t LIMIT 1");
            $db->bind(':t', $table);
            return (bool)$db->single();
        } catch (\Throwable $e) {
            return false;
        }
    }
}