<?php
// ⚠️ DELETE AFTER CHECKING
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() == PHP_SESSION_NONE) @session_start();

echo "<!DOCTYPE html><html><head><title>Chat Diagnostic</title>
<style>
body{font-family:system-ui;background:#0f172a;color:#e2e8f0;padding:24px;max-width:1000px;margin:0 auto}
h1{color:#38bdf8}
h2{color:#10b981;border-bottom:1px solid #334155;padding-bottom:8px;margin-top:24px}
pre{background:#1e293b;padding:14px;border-radius:8px;overflow-x:auto;font-size:13px;line-height:1.6;white-space:pre-wrap;word-break:break-word}
.ok{color:#4ade80}.fail{color:#f87171}.warn{color:#fbbf24}
.box{background:#1e293b;padding:14px;border-radius:8px;margin:10px 0;border-left:4px solid #38bdf8}
</style></head><body>
<h1>🔍 Chat Diagnostic</h1>";

/* ========== 1. Session ========== */
echo "<h2>1. Session</h2><div class='box'><pre>";
echo "session_name: " . session_name() . "\n";
echo "user_id:  " . (isset($_SESSION['user_id']) ? "<span class='ok'>" . $_SESSION['user_id'] . "</span>" : "<span class='fail'>NOT SET</span>") . "\n";
echo "name:     " . ($_SESSION['name'] ?? '-') . "\n";
echo "role_id:  " . ($_SESSION['role_id'] ?? '-') . "\n";
echo "</pre></div>";

/* ========== 2. Try to load DB via app config ========== */
echo "<h2>2. Database</h2><div class='box'><pre>";
$conn = null;
foreach ([
    __DIR__ . '/config/config.php',
    __DIR__ . '/app/Core/Database.php',
] as $f) {
    if (file_exists($f)) {
        echo "Found: " . str_replace(__DIR__, '.', $f) . "\n";
    }
}

/* Try to instantiate the Database class */
if (file_exists(__DIR__ . '/app/Core/Database.php')) {
    // Try autoloading the whole app to get Database
    spl_autoload_register(function ($class) {
        if (strpos($class, 'App\\') !== 0) return;
        $rel  = str_replace('\\', '/', substr($class, 4));
        $file = __DIR__ . '/app/' . $rel . '.php';
        if (is_file($file)) require_once $file;
    });
    try {
        if (class_exists('App\\Core\\Database')) {
            $db = new \App\Core\Database();
            echo "<span class='ok'>✅ Database instantiated</span>\n";
            // Try simple query
            $db->query("SELECT DATABASE() AS db");
            $row = $db->single();
            echo "Connected DB: <span class='ok'>" . ($row['db'] ?? '?') . "</span>\n";
            $conn = $db;
        } else {
            echo "<span class='fail'>❌ Database class not found</span>\n";
        }
    } catch (Throwable $e) {
        echo "<span class='fail'>❌ DB error: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    }
}
echo "</pre></div>";

/* ========== 3. Chat-related tables ========== */
echo "<h2>3. Chat tables</h2><div class='box'><pre>";
if ($conn) {
    foreach (['conversations','messages','chat_messages','chats','chat_conversations','chat_participants','chat'] as $t) {
        try {
            $conn->query("SHOW TABLES LIKE '$t'");
            $r = $conn->resultSet();
            if (!empty($r)) {
                echo "<span class='ok'>✅ $t</span> exists\n";
                // show columns
                $conn->query("SHOW COLUMNS FROM `$t`");
                $cols = $conn->resultSet();
                foreach ($cols as $c) {
                    echo "     • " . $c['Field'] . " (" . $c['Type'] . ")\n";
                }
            }
        } catch (Throwable $e) {
            echo "<span class='fail'>❌ $t: " . $e->getMessage() . "</span>\n";
        }
    }
} else {
    echo "(skipped - DB not connected)\n";
}
echo "</pre></div>";

/* ========== 4. Chat routes / controllers ========== */
echo "<h2>4. Chat code in project</h2><div class='box'><pre>";
$hits = [];
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS)
);
foreach ($it as $file) {
    if (!$file->isFile()) continue;
    $n = strtolower($file->getFilename());
    if (strpos($n, 'chat') !== false || strpos($n, 'message') !== false) {
        $hits[] = str_replace(__DIR__, '.', $file->getPathname());
    }
    if (count($hits) > 30) break;
}
if (empty($hits)) {
    echo "<span class='warn'>No files with 'chat' or 'message' in their name.</span>\n";
    echo "This means the chat feature might not be implemented yet!\n";
} else {
    foreach ($hits as $h) echo "  📄 $h\n";
}
echo "</pre></div>";

/* ========== 5. Routes ========== */
echo "<h2>5. Chat routes</h2><div class='box'><pre>";
$routeFiles = [
    'routes/web.php',
    'config/routes.php',
    'app/Config/routes.php',
    'app/routes.php',
];
foreach ($routeFiles as $rf) {
    $p = __DIR__ . '/' . $rf;
    if (file_exists($p)) {
        echo "Found routes file: $rf\n\n";
        $content = file_get_contents($p);
        preg_match_all('/.*chat.*/i', $content, $matches);
        if (!empty($matches[0])) {
            foreach (array_slice($matches[0], 0, 20) as $line) {
                echo "  " . trim($line) . "\n";
            }
        } else {
            echo "<span class='warn'>No 'chat' route defined.</span>\n";
        }
        break;
    }
}
echo "</pre></div>";

/* ========== 6. Test message insert ========== */
echo "<h2>6. Try a test message</h2><div class='box'><pre>";
if ($conn && isset($_SESSION['user_id'])) {
    try {
        // Find messages table
        $tables = ['messages','chat_messages'];
        $found = null;
        foreach ($tables as $t) {
            $conn->query("SHOW TABLES LIKE '$t'");
            if (!empty($conn->resultSet())) { $found = $t; break; }
        }
        if (!$found) {
            echo "<span class='fail'>❌ No messages table found.</span>\n";
        } else {
            echo "Using table: $found\n";
            // List columns so we know what to insert
            $conn->query("SHOW COLUMNS FROM `$found`");
            $cols = $conn->resultSet();
            echo "Columns: " . implode(', ', array_column($cols, 'Field')) . "\n\n";
            echo "<span class='warn'>⚠️ Not testing insert (would modify data).</span>\n";
            echo "If you want, we can test after checking permissions.\n";
        }
    } catch (Throwable $e) {
        echo "<span class='fail'>Error: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    }
} else {
    echo "(skipped - no session or DB)\n";
}
echo "</pre></div>";

echo "<h2>7. Full page output</h2>";
echo "<p>If you still see errors, they may be in the log. Check <code>storage/logs/php_error.log</code>:</p>";
$logFile = __DIR__ . '/storage/logs/php_error.log';
if (file_exists($logFile)) {
    $lines = array_slice(file($logFile), -30);
    echo "<pre><span class='warn'>Last 30 log lines:</span>\n";
    foreach ($lines as $line) echo htmlspecialchars($line);
    echo "</pre>";
} else {
    echo "<span class='warn'>No log file at storage/logs/php_error.log</span>";
}

echo "<p style='color:#ef4444;font-weight:bold;margin-top:32px'>⚠️ DELETE chat-check.php when done</p>";
echo "</body></html>";