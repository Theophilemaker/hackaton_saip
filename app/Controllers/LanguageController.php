<?php
namespace App\Controllers;

use App\Core\Controller;

class LanguageController extends Controller {

    /* ═══════════════════════════════════════════════════════════
       INDEX — /language
       ═══════════════════════════════════════════════════════════ */
    public function index() {
        if (!empty($_GET['lang'])) {
            return $this->switch($_GET['lang']);
        }

        $current   = $_SESSION['lang'] ?? 'en';
        $supported = [
            'en' => ['name' => 'English',     'flag' => '🇬🇧'],
            'rw' => ['name' => 'Kinyarwanda', 'flag' => '🇷🇼'],
            'fr' => ['name' => 'Français',    'flag' => '🇫🇷'],
            'sw' => ['name' => 'Kiswahili',   'flag' => '🇹🇿'],
        ];

        $viewFile = __DIR__ . '/../Views/language/index.php';

        if (file_exists($viewFile)) {
            parent::view('language/index', [
                'title'     => 'Language Settings',
                'name'      => $_SESSION['name'] ?? 'User',
                'current'   => $current,
                'supported' => $supported,
            ]);
            return;
        }

        /* ─── INLINE FALLBACK — renders even without a view file ─── */
        $this->renderInline($current, $supported);
    }

    /* ═══════════════════════════════════════════════════════════
       SWITCH — /language/switch?lang=rw
       ═══════════════════════════════════════════════════════════ */
    public function switch($lang = null) {
        if ($lang === null) $lang = $_GET['lang'] ?? null;

        if (in_array($lang, ['en', 'rw', 'fr', 'sw'], true)) {
            $_SESSION['lang'] = $lang;

            if (!empty($_SESSION['user_id'])) {
                try {
                    $db = new \App\Core\Database();
                    $db->query("UPDATE users SET language = :l WHERE id = :id");
                    $db->bind(':l',  $lang);
                    $db->bind(':id', (int)$_SESSION['user_id']);
                    $db->execute();
                } catch (\Throwable $e) { /* column may not exist */ }
            }
        }

        /* AJAX */
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            if (ob_get_level() > 0) @ob_clean();
            echo json_encode(['success' => true, 'status' => 'ok', 'lang' => $lang]);
            exit;
        }

        $redirect = $_GET['redirect'] ?? null;
        if ($redirect) {
            header("Location: " . $redirect);
            exit;
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/dashboard');
        header("Location: " . $referer);
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       SET — POST /language/set (AJAX from topbar)
       ═══════════════════════════════════════════════════════════ */
    public function set() {
        $lang = $_POST['lang'] ?? $_GET['lang'] ?? null;
        return $this->switch($lang);
    }

    /* ═══════════════════════════════════════════════════════════
       INLINE FALLBACK VIEW — no separate file needed
       ═══════════════════════════════════════════════════════════ */
    private function renderInline(string $current, array $supported): void {
        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Language Settings · SAIP</title>
<style>
* { box-sizing: border-box; }
body {
    margin: 0; background: #0b1120; color: #f1f5f9;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.page { display: flex; min-height: 100vh; }
.main {
    margin-left: 260px; width: calc(100% - 260px);
    padding: 32px; box-sizing: border-box;
}
.glass {
    background: rgba(30,41,59,0.7);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px; padding: 28px; margin-bottom: 20px;
}
.hero {
    background: linear-gradient(135deg, rgba(56,189,248,0.15), rgba(167,139,250,0.08));
    display: flex; justify-content: space-between; align-items: center;
    gap: 20px; flex-wrap: wrap;
}
.hero h1 { font-size: 26px; font-weight: 800; margin: 0 0 8px; color: #f1f5f9; }
.hero p  { color: #94a3b8; font-size: 13.5px; margin: 0; max-width: 600px; }
.tag {
    display: inline-flex; padding: 4px 12px;
    background: rgba(56,189,248,0.15); color: #38bdf8;
    border: 1px solid rgba(56,189,248,0.35); border-radius: 999px;
    font-size: 10.5px; font-weight: 800; letter-spacing: 1.2px;
    text-transform: uppercase; margin-bottom: 10px;
}
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}
.card {
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    padding: 26px 20px; text-decoration: none;
    background: rgba(0,0,0,0.2);
    border: 2px solid rgba(255,255,255,0.06);
    border-radius: 16px; transition: all 0.2s;
}
.card:hover {
    border-color: rgba(56,189,248,0.4);
    transform: translateY(-3px);
    background: rgba(56,189,248,0.05);
}
.card.active {
    border-color: #10b981;
    background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(5,150,105,0.06));
}
.card .flag { font-size: 48px; line-height: 1; }
.card strong { color: #f1f5f9; font-size: 15px; font-weight: 800; }
.card .check {
    color: #34d399; font-size: 11px; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.card .action { color: #94a3b8; font-size: 12px; font-weight: 700; }
.card:hover .action { color: #38bdf8; }
.btn-back {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 22px; background: rgba(255,255,255,0.06);
    color: #f1f5f9; border: 1px solid rgba(255,255,255,0.14);
    border-radius: 12px; font-size: 13px; font-weight: 700;
    text-decoration: none; transition: all 0.2s;
}
.btn-back:hover { background: rgba(255,255,255,0.12); transform: translateY(-2px); }
.note {
    margin-top: 20px; padding: 14px 18px;
    background: rgba(251,191,36,0.06);
    border-left: 3px solid #fbbf24; border-radius: 10px;
    font-size: 12.5px; color: #fbbf24; line-height: 1.55;
}
@media (max-width: 900px) {
    .main { margin-left: 0; width: 100%; padding: 16px; padding-top: 72px; }
}
</style>
</head>
<body>
<div class="page">
    <?php require_once __DIR__ . '/../Views/layouts/sidebar.php'; ?>
    <main class="main">

        <section class="glass hero">
            <div>
                <span class="tag">🌍 LANGUAGE</span>
                <h1>Language Settings</h1>
                <p>Choose the language you want to use across the SAIP platform. Your choice is saved to your account.</p>
            </div>
            <a href="<?= BASE_URL ?>/dashboard" class="btn-back">← Back to Dashboard</a>
        </section>

        <section class="glass">
            <h3 style="font-size:16px;font-weight:800;color:#f1f5f9;margin:0 0 6px;">Available Languages</h3>
            <p style="color:#94a3b8;font-size:12.5px;margin:0 0 22px;">Select one to apply it immediately</p>

            <div class="grid">
                <?php foreach ($supported as $code => $info): ?>
                    <a href="<?= BASE_URL ?>/language/switch?lang=<?= $code ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                       class="card <?= $code === $current ? 'active' : '' ?>">
                        <span class="flag"><?= $info['flag'] ?></span>
                        <strong><?= htmlspecialchars($info['name']) ?></strong>
                        <?php if ($code === $current): ?>
                            <span class="check">✓ Currently Active</span>
                        <?php else: ?>
                            <span class="action">Switch →</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="note">
                💡 Your language choice affects the dashboard, chat, AI responses, and forms throughout the platform.
            </div>
        </section>

    </main>
</div>
</body>
</html>
        <?php
        exit;
    }
}