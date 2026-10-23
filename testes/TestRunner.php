<?php
/**
 * Minimal test runner — no Composer required.
 * Usage: php tests/TestRunner.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Security.php';
require_once __DIR__ . '/../app/Core/Database.php';

class TestRunner {
    private $passed = 0;
    private $failed = 0;

    public function assert($condition, $message) {
        if ($condition) {
            echo "✅ PASS — {$message}\n";
            $this->passed++;
        } else {
            echo "❌ FAIL — {$message}\n";
            $this->failed++;
        }
    }

    public function assertEquals($expected, $actual, $message) {
        if ($expected == $actual) {
            echo "✅ PASS — {$message}\n";
            $this->passed++;
        } else {
            echo "❌ FAIL — {$message} (expected: " . var_export($expected, true) . ", got: " . var_export($actual, true) . ")\n";
            $this->failed++;
        }
    }

    public function summary() {
        echo "\n========================================\n";
        echo "Total: " . ($this->passed + $this->failed) . " | ";
        echo "Passed: {$this->passed} | Failed: {$this->failed}\n";
        echo "========================================\n";
        exit($this->failed > 0 ? 1 : 0);
    }
}

$t = new TestRunner();

// ============ SECURITY TESTS ============
echo "\n--- Security ---\n";
$t->assert(strlen(\App\Core\Security::deviceFingerprint()) === 64, "Device fingerprint is SHA-256");
$t->assert(\App\Core\Security::passwordStrength('Passw0rd!') >= 80, "Strong password scores high");
$t->assert(\App\Core\Security::passwordStrength('abc') < 40, "Weak password scores low");
$t->assert(\App\Core\Security::e('<script>') === '&lt;script&gt;', "HTML escaping works");
$t->assert(\App\Core\Security::cleanInt('123abc') === 123, "cleanInt strips non-numeric");
$t->assert(\App\Core\Security::cleanPhone('+250 78-123-4567') === '+250781234567', "cleanPhone strips separators");
$t->assert(\App\Core\Security::validatePassword('Passw0rd')['ok'] === true, "Valid password passes");
$t->assert(\App\Core\Security::validatePassword('short')['ok'] === false, "Short password fails");
$t->assert(\App\Core\Security::validatePassword('alllowercase')['ok'] === false, "Password with no digits fails");

// ============ DATABASE TESTS ============
echo "\n--- Database ---\n";
try {
    $db = new \App\Core\Database();
    $t->assert(true, "Database connects");

    $db->query("SELECT 1 AS one");
    $row = $db->single();
    $t->assertEquals(1, $row['one'], "Basic SELECT works");

    // Check critical tables exist
    $required = ['users','roles','crops','farms','products','orders','conversations','messages',
                 'notifications','government_alerts','cooperatives','warehouses','login_history',
                 'user_sessions','rate_limits','audit_logs'];
    foreach ($required as $table) {
        $db->query("SHOW TABLES LIKE :t");
        $db->bind(':t', $table);
        $t->assert((bool)$db->single(), "Table '{$table}' exists");
    }
} catch (\Throwable $e) {
    $t->assert(false, "DB test failed: " . $e->getMessage());
}

// ============ I18N TESTS ============
echo "\n--- i18n ---\n";
foreach (['en','rw','fr','sw'] as $lang) {
    $file = __DIR__ . "/../app/Languages/{$lang}.json";
    $t->assert(file_exists($file), "Language file {$lang}.json exists");
    $json = json_decode(file_get_contents($file), true);
    $t->assert(is_array($json), "Language {$lang}.json is valid JSON");
    $t->assert(isset($json['app_name']), "Language {$lang} has app_name key");
    $t->assert(isset($json['nav.dashboard']), "Language {$lang} has nav.dashboard key");
}

$t->summary();
?>