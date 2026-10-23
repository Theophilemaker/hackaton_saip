<?php
namespace App\Helpers;

/**
 * PHP ↔ C++ NISR Engine bridge.
 * Returns null if the binary is missing or fails.
 */
class NisrEngine {

    private string $binary;
    private bool $available;
    private int $timeout = 30;

    public function __construct() {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $winPath = $base . '/public/bin/nisr_engine.exe';
        $nixPath = $base . '/public/bin/nisr_engine';

        $this->binary = file_exists($winPath) ? $winPath : $nixPath;
        $this->available = is_file($this->binary) && is_readable($this->binary);
    }

    public function isAvailable(): bool { return $this->available; }

    /* ──────── PUBLIC API ──────── */
    public function pivot(string $csv, string $row, string $col, string $val, string $agg = 'sum'): ?array {
        return $this->exec('pivot', [$csv, $row, $col, $val, $agg]);
    }

    public function quality(string $csv): ?array {
        return $this->exec('quality', [$csv]);
    }

    public function timeseries(string $csv, string $date, string $metric, string $agg = 'sum', int $smooth = 0): ?array {
        return $this->exec('timeseries', [$csv, $date, $metric, $agg, (string)$smooth]);
    }

    public function compare(string $csv, string $dim, string $metric, string $agg = 'sum'): ?array {
        return $this->exec('compare', [$csv, $dim, $metric, $agg]);
    }

    public function schema(string $csv): ?array {
        return $this->exec('schema', [$csv]);
    }

    /* ──────── EXEC ──────── */
    private function exec(string $mode, array $args): ?array {
        if (!$this->available) return null;

        $cmd = escapeshellarg($this->binary) . ' ' . escapeshellarg($mode);
        foreach ($args as $a) $cmd .= ' ' . escapeshellarg((string)$a);

        $start = microtime(true);

        // Use proc_open for timeout control
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = @proc_open($cmd, $descriptors, $pipes);
        if (!is_resource($proc)) return null;

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stdout = '';
        $stderr = '';
        $deadline = microtime(true) + $this->timeout;

        while (true) {
            $status = proc_get_status($proc);
            $stdout .= stream_get_contents($pipes[1]);
            $stderr .= stream_get_contents($pipes[2]);

            if (!$status['running']) break;
            if (microtime(true) > $deadline) {
                proc_terminate($proc, 9);
                error_log("NisrEngine timeout: {$mode}");
                fclose($pipes[1]); fclose($pipes[2]);
                proc_close($proc);
                return null;
            }
            usleep(5000);
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);

        $elapsed = (int)((microtime(true) - $start) * 1000);

        $json = trim($stdout);
        if ($json === '') {
            error_log("NisrEngine empty output: {$mode} / stderr: {$stderr}");
            return null;
        }

        $decoded = json_decode($json, true);
        if ($decoded === null) {
            error_log("NisrEngine bad JSON: {$mode} / " . substr($json, 0, 200));
            return null;
        }

        if (isset($decoded['error'])) {
            error_log("NisrEngine error: " . $decoded['error']);
            return null;
        }

        // Optional: attach timing for debugging
        $decoded['_engine_ms'] = $elapsed;
        return $decoded;
    }
}