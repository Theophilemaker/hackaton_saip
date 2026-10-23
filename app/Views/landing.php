<?php
/**
 * Landing Page — Public (v4)
 * Rendered via Controller::viewPublic('landing', $data)
 *
 * ✅ Nav lives in public_header.php (shared across all public pages)
 * ✅ Existing sections preserved exactly (hero, features, modules, impact, CTA, contact)
 * ✅ Multi-video demo modal with playlist + categories
 */

$s = $data['stats'] ?? $stats ?? [];
if (!is_array($s)) $s = [];
$s = array_merge([
    'farmers'    => 1240,
    'farms'      => 3400,
    'production' => 4800000,
    'districts'  => 30,
], $s);

$statFarmers    = number_format((int)$s['farmers']);
$statFarms      = number_format((int)$s['farms']);
$statProduction = number_format(((float)$s['production']) / 1e6, 1);
$statDistricts  = (int)$s['districts'];

/* ─── Video library ─── */
/* ─── Video library — DB first, fallback to _videos.php ─── */
$demoVideos = [];

try {
    $__db = new \App\Core\Database();
    $__db->query("
        SELECT id, title, category, description, type, src, thumbnail,
               duration, is_featured
        FROM demo_videos
        WHERE is_active = 1
        ORDER BY sort_order ASC, created_at DESC
    ");
    $rows = $__db->resultSet() ?: [];

    foreach ($rows as $r) {
        /* Make relative src/thumbnail absolute if needed */
        if (!empty($r['src']) && !preg_match('#^https?://#i', $r['src'])) {
            $r['src'] = BASE_URL . $r['src'];
        }
        if (!empty($r['thumbnail']) && !preg_match('#^https?://#i', $r['thumbnail'])) {
            $r['thumbnail'] = BASE_URL . $r['thumbnail'];
        }

        $demoVideos[] = [
            'id'          => 'v' . $r['id'],
            'category'    => $r['category'],
            'title'       => $r['title'],
            'description' => $r['description'] ?? '',
            'type'        => $r['type'],
            'src'         => $r['src'],
            'thumbnail'   => $r['thumbnail'],
            'duration'    => $r['duration'] ?: '—',
            'featured'    => !empty($r['is_featured']),
        ];
    }
} catch (\Throwable $e) { /* silent */ }

/* Fallback to static file if DB is empty */
if (empty($demoVideos)) {
    $videosFile = __DIR__ . '/landing/_videos.php';
    $demoVideos = file_exists($videosFile) ? require $videosFile : [];
}

$categories = !empty($demoVideos) ? array_unique(array_column($demoVideos, 'category')) : [];
?>

<div class="landing-page-v2">

<!-- ═══════════════════════════════════════════════════════════
     HERO
     ═══════════════════════════════════════════════════════════ -->
<section class="landing-hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="hero-badge">
            <span>🇷🇼</span> Aligned with NST2 · Vision 2050 · NISR 2026
        </div>
        <h1 class="hero-title">
            <span class="grad-text">Empowering Farmers</span><br>
            Strengthening Agriculture,<br>
            Building a Food Secure Rwanda
        </h1>
        <p class="hero-subtitle">
            SAIP combines AI, GIS, satellite weather, and a nationwide farmer network
            to turn NISR data into daily decisions for Rwandan farmers.
        </p>

        <div class="hero-ctas">
            <a href="<?= BASE_URL ?>/auth/register" class="btn-hero-primary">
                🌱 <?= __('auth.register') ?? 'Register' ?> — It's free
            </a>
            <button type="button" class="btn-hero-ghost" onclick="openDemoModal()">
                ▶ Watch Demo
            </button>
        </div>

        <div class="hero-stats">
            <div>
                <strong><?= $statFarmers ?>+</strong>
                <small>Farmers</small>
            </div>
            <div>
                <strong><?= $statFarms ?>+</strong>
                <small>Farms</small>
            </div>
            <div>
                <strong><?= $statProduction ?>M</strong>
                <small>Tons produced</small>
            </div>
            <div>
                <strong><?= $statDistricts ?></strong>
                <small>Districts</small>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     FEATURES
     ═══════════════════════════════════════════════════════════ -->
<section class="landing-section" id="features">
    <div class="section-inner">
        <div class="section-head">
            <h2>Everything a Rwandan farmer needs, in one platform</h2>
            <p>Powered by NISR datasets, AI models, and Rwanda's open data ecosystem.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon" style="background:linear-gradient(135deg,#16A34A,#22C55E)">🌾</div>
                <h3>Crop Intelligence</h3>
                <p>AI predicts your yield and detects diseases from a single leaf photo.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background:linear-gradient(135deg,#0EA5E9,#06B6D4)">🌤️</div>
                <h3>Weather Intelligence</h3>
                <p>Live forecasts and AI advice on planting, irrigation, and fertilizer timing.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background:linear-gradient(135deg,#F59E0B,#EF4444)">🛒</div>
                <h3>Smart Marketplace</h3>
                <p>Sell directly to buyers nationwide. Negotiate prices, track deliveries.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background:linear-gradient(135deg,#7C3AED,#C084FC)">💬</div>
                <h3>Farmer Connect</h3>
                <p>Chat with farmers across Rwanda in your language. Voice notes, auto-translation.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background:linear-gradient(135deg,#84CC16,#16A34A)">🗺️</div>
                <h3>Rwanda GIS Map</h3>
                <p>District-level heatmaps of production, rainfall, irrigation, and pest risk.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background:linear-gradient(135deg,#F43F5E,#EC4899)">📊</div>
                <h3>NISR Analyst Portal</h3>
                <p>Upload datasets, run pivot tables, build charts, and publish statistical reports.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     MODULES
     ═══════════════════════════════════════════════════════════ -->
<section class="landing-section alt-bg" id="modules">
    <div class="section-inner">
        <div class="section-head">
            <h2>15 Enterprise Modules</h2>
            <p>Fully integrated, multilingual, and built for scale.</p>
        </div>

        <div class="modules-grid">
            <?php
            $modules = [
                ['🌱', 'Authentication & RBAC', '7 roles, 2FA, audit logs'],
                ['🌐', 'Multilingual', 'Kinyarwanda · English · French · Kiswahili'],
                ['🎨', 'Personalized Dashboard', 'Drag-drop widgets, themes'],
                ['🌤️', 'Weather Intelligence', 'Forecast + AI advice'],
                ['🌾', 'Crop Intelligence', 'Track, predict, compare'],
                ['🤖', 'AI AgriBot', 'Multilingual farming assistant'],
                ['🔬', 'Disease & Pest Detection', 'Upload photo → diagnosis'],
                ['💬', 'Farmer Connect', 'Chat, voice, translation'],
                ['📍', 'District Forums', 'Officer-verified answers'],
                ['👥', 'Cooperative Suite', 'Members, harvest, contributions'],
                ['🛒', 'Smart Marketplace', 'Buy, sell, negotiate, deliver'],
                ['🚚', 'Logistics', 'Fleet, tracking, GPS'],
                ['🏭', 'Storage', 'Warehouse bins, IoT sensors'],
                ['🗺️', 'GIS Analytics', 'District heatmaps'],
                ['🏛️', 'Government Portal', 'KPIs, NST2, PDF reports'],
            ];
            foreach ($modules as $m): ?>
                <div class="module-pill">
                    <span class="mp-icon"><?= $m[0] ?></span>
                    <strong><?= htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8') ?></strong>
                    <small><?= htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8') ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     IMPACT
     ═══════════════════════════════════════════════════════════ -->
<section class="landing-section" id="impact">
    <div class="section-inner">
        <div class="section-head">
            <h2>Built on Rwanda's National Priorities</h2>
            <p>Direct alignment with NST2 pillars and Vision 2050.</p>
        </div>

        <div class="pillars-grid">
            <div class="pillar-card">
                <span class="pillar-icon">📈</span>
                <h3>Economic Transformation</h3>
                <p>Market access, productivity, exports, and post-harvest loss reduction.</p>
            </div>
            <div class="pillar-card">
                <span class="pillar-icon">👥</span>
                <h3>Social Transformation</h3>
                <p>Multilingual interface, digital inclusion, farmer knowledge sharing.</p>
            </div>
            <div class="pillar-card">
                <span class="pillar-icon">🏛️</span>
                <h3>Transformational Governance</h3>
                <p>Evidence-based policy through district-to-national analytics.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     CTA
     ═══════════════════════════════════════════════════════════ -->
<section class="landing-cta">
    <div class="cta-inner">
        <h2>Ready to farm smarter?</h2>
        <p>Join thousands of Rwandan farmers using AI-powered decisions every day.</p>
        <div class="hero-ctas">
            <a href="<?= BASE_URL ?>/auth/register" class="btn-hero-primary">🌱 Create Free Account</a>
            <a href="<?= BASE_URL ?>/auth/login" class="btn-hero-ghost"><?= __('auth.login') ?? 'Login' ?> →</a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     CONTACT
     ═══════════════════════════════════════════════════════════ -->
<section class="landing-section" id="contact">
    <div class="section-inner">
        <div class="section-head">
            <h2>Get in Touch</h2>
            <p>Questions, partnerships, or support — we're here to help.</p>
        </div>

        <div class="lp-contact-grid">
            <a href="mailto:theophilen298@gmail.com" class="lp-contact-card">
                <span class="lp-contact-icon">✉️</span>
                <strong>Email</strong>
                <span>theophilen298@gmail.com</span>
            </a>
            <a href="tel:+250738084659" class="lp-contact-card">
                <span class="lp-contact-icon">📞</span>
                <strong>Call</strong>
                <span>0738 084 659</span>
            </a>
            <a href="https://wa.me/250793473101" target="_blank" class="lp-contact-card">
                <span class="lp-contact-icon">💬</span>
                <strong>WhatsApp</strong>
                <span>0793 473 101</span>
            </a>
            <div class="lp-contact-card">
                <span class="lp-contact-icon">📍</span>
                <strong>Office</strong>
                <span>SAIP Headquarters</span>
                <span>KG 7 Ave, Kigali</span>
                <span>Huye, Tumba</span>
                <span>P.O. Box 4285</span>
                <span>Kigali, Rwanda</span>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     MULTI-VIDEO DEMO MODAL
     ═══════════════════════════════════════════════════════════ -->
<div class="demo-modal" id="demoModal" hidden data-videos='<?= htmlspecialchars(json_encode($demoVideos), ENT_QUOTES, "UTF-8") ?>'>
    <div class="demo-backdrop" onclick="closeDemoModal()"></div>

    <div class="demo-window">

        <!-- HEADER -->
        <header class="demo-head">
            <div>
                <h3>🎬 SAIP Video Library</h3>
                <p>Demos, feature walkthroughs, tutorials &amp; impact stories</p>
            </div>
            <button type="button" class="demo-close" onclick="closeDemoModal()" aria-label="Close">✕</button>
        </header>

        <!-- CATEGORY TABS -->
        <?php if (!empty($categories)): ?>
        <div class="demo-tabs" id="demoTabs">
            <button type="button" class="demo-tab active" data-category="all">
                🌐 All <span class="tab-count"><?= count($demoVideos) ?></span>
            </button>
            <?php foreach ($categories as $cat): ?>
                <?php $cnt = count(array_filter($demoVideos, fn($v) => $v['category'] === $cat)); ?>
                <button type="button" class="demo-tab" data-category="<?= htmlspecialchars($cat) ?>">
                    <?= htmlspecialchars($cat) ?> <span class="tab-count"><?= $cnt ?></span>
                </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- BODY: PLAYER + PLAYLIST -->
        <div class="demo-body">

            <!-- PLAYER -->
            <div class="demo-player-wrap">
                <div class="demo-video-container" id="demoVideoContainer">
                    <!-- JS populates this -->
                </div>
                <div class="demo-info">
                    <div class="demo-info-head">
                        <div>
                            <span class="demo-info-cat" id="demoInfoCat">Demo</span>
                            <h4 id="demoInfoTitle">Select a video</h4>
                            <p id="demoInfoDesc">Choose from the playlist on the right.</p>
                        </div>
                        <span class="demo-info-duration" id="demoInfoDuration">—</span>
                    </div>
                </div>
            </div>

            <!-- PLAYLIST -->
            <aside class="demo-playlist-wrap">
                <div class="demo-playlist-head">
                    <span>📚 Playlist</span>
                    <small id="demoPlaylistCount"><?= count($demoVideos) ?> videos</small>
                </div>
                <div class="demo-playlist" id="demoPlaylist">
                    <!-- JS populates this -->
                </div>
            </aside>
        </div>

        <!-- FOOTER CTA -->
        <footer class="demo-foot">
            <div class="demo-features">
                <span>🌾 Crop Intelligence</span>
                <span>🌤️ Weather AI</span>
                <span>💰 Market Prices</span>
                <span>🤖 AgriBot</span>
            </div>
            <a href="<?= BASE_URL ?>/auth/register" class="demo-cta">
                🌱 Try it free →
            </a>
        </footer>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MULTI-VIDEO DEMO STYLES
     ═══════════════════════════════════════════════════════════ -->
<style>
.demo-modal {
    position: fixed; inset: 0; z-index: 99999;
    display: flex; align-items: center; justify-content: center;
    padding: 20px; box-sizing: border-box;
    animation: demoFadeIn 0.25s ease-out;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.demo-modal[hidden] { display: none !important; }
@keyframes demoFadeIn { from { opacity: 0; } to { opacity: 1; } }

.demo-backdrop {
    position: absolute; inset: 0;
    background: rgba(0, 0, 0, 0.88);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.demo-window {
    position: relative; width: 100%; max-width: 1200px;
    max-height: calc(100vh - 40px);
    display: flex; flex-direction: column;
    background: #0f1d2e;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px; overflow: hidden;
    box-shadow: 0 32px 80px rgba(0, 0, 0, 0.8);
    animation: demoZoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes demoZoomIn { from { opacity: 0; transform: scale(0.94); } to { opacity: 1; transform: scale(1); } }

/* Head */
.demo-head {
    display: flex; justify-content: space-between; align-items: center;
    gap: 16px; padding: 18px 24px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(0, 0, 0, 0.25); flex-shrink: 0;
}
.demo-head h3 { font-size: 17px; font-weight: 800; color: #f1f5f9; margin: 0 0 3px; }
.demo-head p { font-size: 12.5px; color: #94a3b8; margin: 0; }
.demo-close {
    width: 38px; height: 38px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px; color: #cbd5e1; font-size: 15px;
    cursor: pointer; font-family: inherit; padding: 0;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s; flex-shrink: 0;
}
.demo-close:hover { background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: #f87171; }

/* Tabs */
.demo-tabs {
    display: flex; gap: 6px; padding: 12px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(0, 0, 0, 0.15);
    overflow-x: auto; scrollbar-width: none; flex-shrink: 0;
}
.demo-tabs::-webkit-scrollbar { display: none; }
.demo-tab {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px;
    background: rgba(255, 255, 255, 0.04);
    color: #94a3b8;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 999px;
    font-size: 12.5px; font-weight: 700;
    cursor: pointer; font-family: inherit;
    white-space: nowrap; transition: all 0.2s;
}
.demo-tab:hover { background: rgba(255, 255, 255, 0.08); color: #cbd5e1; }
.demo-tab.active {
    background: rgba(16, 185, 129, 0.15);
    border-color: rgba(16, 185, 129, 0.4);
    color: #34d399;
}
.tab-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 20px; height: 18px; padding: 0 6px;
    background: rgba(0, 0, 0, 0.3); border-radius: 999px;
    font-size: 10px; font-weight: 800;
}
.demo-tab.active .tab-count { background: rgba(16, 185, 129, 0.3); }

/* Body */
.demo-body {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    flex: 1; min-height: 0; overflow: hidden;
}

.demo-player-wrap {
    display: flex; flex-direction: column; min-width: 0;
    background: #000; overflow: hidden;
}
.demo-video-container {
    flex: 1; display: flex; align-items: center; justify-content: center;
    background: #000; min-height: 0; position: relative;
    aspect-ratio: 16 / 9; max-height: 100%;
}
.demo-video-container video,
.demo-video-container iframe {
    width: 100%; height: 100%; display: block;
    background: #000; border: none; object-fit: contain;
}
.demo-video-container:empty::after {
    content: '🎬 Select a video to play';
    color: #475569; font-size: 14px; font-weight: 600;
    display: flex; align-items: center; justify-content: center;
    height: 100%;
}

.demo-info {
    padding: 16px 20px;
    background: rgba(0, 0, 0, 0.3);
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    flex-shrink: 0;
}
.demo-info-head {
    display: flex; justify-content: space-between;
    align-items: flex-start; gap: 12px;
}
.demo-info-cat {
    display: inline-block; padding: 3px 10px;
    background: rgba(16, 185, 129, 0.15); color: #34d399;
    border-radius: 999px; font-size: 10px; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.6px;
    margin-bottom: 6px;
}
.demo-info-head h4 {
    font-size: 15px; font-weight: 800; color: #f1f5f9;
    margin: 0 0 4px; line-height: 1.3;
}
.demo-info-head p {
    font-size: 12.5px; color: #94a3b8;
    margin: 0; line-height: 1.5;
}
.demo-info-duration {
    display: inline-flex; padding: 4px 10px;
    background: rgba(0, 0, 0, 0.4); color: #cbd5e1;
    border-radius: 6px; font-size: 11px; font-weight: 700;
    flex-shrink: 0; font-family: 'Courier New', monospace;
}

/* Playlist */
.demo-playlist-wrap {
    display: flex; flex-direction: column;
    border-left: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(0, 0, 0, 0.2);
    overflow: hidden; min-width: 0;
}
.demo-playlist-head {
    display: flex; justify-content: space-between; align-items: center;
    padding: 14px 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    font-size: 12.5px; font-weight: 800; color: #f1f5f9;
    text-transform: uppercase; letter-spacing: 1px;
    flex-shrink: 0;
}
.demo-playlist-head small {
    font-size: 10.5px; color: #64748b;
    font-weight: 700; text-transform: none; letter-spacing: 0;
}
.demo-playlist {
    flex: 1; overflow-y: auto; padding: 8px;
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
}
.demo-playlist::-webkit-scrollbar { width: 6px; }
.demo-playlist::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 3px; }

.playlist-item {
    display: grid;
    grid-template-columns: 80px minmax(0, 1fr);
    gap: 10px; padding: 8px;
    border-radius: 10px; cursor: pointer;
    transition: all 0.15s; margin-bottom: 4px;
    border: 1px solid transparent;
}
.playlist-item:hover { background: rgba(255, 255, 255, 0.04); }
.playlist-item.active {
    background: rgba(16, 185, 129, 0.1);
    border-color: rgba(16, 185, 129, 0.3);
}
.playlist-thumb {
    width: 80px; height: 45px;
    border-radius: 6px; background: #1e293b;
    overflow: hidden; position: relative;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.playlist-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.playlist-thumb .play-overlay {
    position: absolute; inset: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; color: #fff;
    opacity: 0; transition: opacity 0.2s;
}
.playlist-item:hover .play-overlay { opacity: 1; }
.playlist-item.active .play-overlay { opacity: 1; background: rgba(16, 185, 129, 0.5); }

.playlist-info {
    min-width: 0; display: flex; flex-direction: column;
    justify-content: center; gap: 3px;
}
.playlist-info strong {
    font-size: 12.5px; color: #f1f5f9; font-weight: 700;
    line-height: 1.3;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
.playlist-info small {
    font-size: 10.5px; color: #64748b; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.4px;
}

/* Foot */
.demo-foot {
    display: flex; justify-content: space-between; align-items: center;
    gap: 16px; padding: 14px 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(0, 0, 0, 0.25);
    flex-wrap: wrap; flex-shrink: 0;
}
.demo-features { display: flex; gap: 6px; flex-wrap: wrap; }
.demo-features span {
    padding: 4px 10px;
    background: rgba(16, 185, 129, 0.1); color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.25);
    border-radius: 999px;
    font-size: 11px; font-weight: 700;
}
.demo-cta {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 22px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff; border-radius: 10px;
    text-decoration: none; font-size: 13px; font-weight: 800;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    transition: all 0.2s; white-space: nowrap;
}
.demo-cta:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.55); color: #fff; }

/* Responsive */
@media (max-width: 1000px) {
    .demo-body { grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
    .demo-playlist-wrap {
        border-left: none;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
        max-height: 260px;
    }
}
@media (max-width: 640px) {
    .demo-modal { padding: 8px; }
    .demo-head { padding: 14px 16px; }
    .demo-head h3 { font-size: 15px; }
    .demo-head p { font-size: 11.5px; }
    .demo-tabs { padding: 10px 14px; }
    .demo-tab { padding: 7px 12px; font-size: 11.5px; }
    .demo-info { padding: 12px 16px; }
    .demo-info-head h4 { font-size: 14px; }
    .demo-foot { padding: 12px 16px; flex-direction: column; align-items: stretch; }
    .demo-features { justify-content: center; }
    .demo-cta { justify-content: center; }
}
</style>

<!-- ═══════════════════════════════════════════════════════════
     MULTI-VIDEO DEMO SCRIPT
     ═══════════════════════════════════════════════════════════ -->
<script>
(function () {
    'use strict';

    var modal       = document.getElementById('demoModal');
    var container   = document.getElementById('demoVideoContainer');
    var playlistEl  = document.getElementById('demoPlaylist');
    var infoCat     = document.getElementById('demoInfoCat');
    var infoTitle   = document.getElementById('demoInfoTitle');
    var infoDesc    = document.getElementById('demoInfoDesc');
    var infoDur     = document.getElementById('demoInfoDuration');
    var tabs        = document.querySelectorAll('.demo-tab');

    if (!modal) return;

    var videos = [];
    var currentVideoId = null;
    var activeCategory = 'all';

    try { videos = JSON.parse(modal.dataset.videos || '[]'); } catch (e) { videos = []; }

    /* ═══ RENDER PLAYLIST ═══ */
    function renderPlaylist(filter) {
        var filtered = videos;
        if (filter && filter !== 'all') {
            filtered = videos.filter(function (v) { return v.category === filter; });
        }

        if (!filtered.length) {
            playlistEl.innerHTML = '<div style="padding:20px;text-align:center;color:#64748b;font-size:12.5px;">No videos in this category</div>';
            return;
        }

        playlistEl.innerHTML = filtered.map(function (v) {
            var isActive = v.id === currentVideoId ? 'active' : '';
            var thumb = v.thumbnail
                ? '<img src="' + esc(v.thumbnail) + '" alt="" loading="lazy" onerror="this.style.display=\'none\'">'
                : '';
            return '' +
                '<div class="playlist-item ' + isActive + '" data-id="' + esc(v.id) + '" tabindex="0" role="button">' +
                    '<div class="playlist-thumb">' + thumb + '<div class="play-overlay">▶</div></div>' +
                    '<div class="playlist-info">' +
                        '<strong>' + esc(v.title) + '</strong>' +
                        '<small>' + esc(v.category) + ' · ' + esc(v.duration || '—') + '</small>' +
                    '</div>' +
                '</div>';
        }).join('');

        playlistEl.querySelectorAll('.playlist-item').forEach(function (item) {
            item.addEventListener('click', function () { playVideo(this.dataset.id); });
            item.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    playVideo(this.dataset.id);
                }
            });
        });
    }

    /* ═══ PLAY VIDEO ═══ */
    function playVideo(id) {
        var v = videos.find(function (x) { return x.id === id; });
        if (!v) return;
        currentVideoId = id;

        var html = '';
        if (v.type === 'youtube') {
            var src = v.src + (v.src.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1&rel=0';
            html = '<iframe src="' + esc(src) + '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen title="' + esc(v.title) + '"></iframe>';
        } else if (v.type === 'vimeo') {
            var vsrc = v.src + (v.src.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1';
            html = '<iframe src="' + esc(vsrc) + '" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="' + esc(v.title) + '"></iframe>';
        } else {
            html = '<video controls autoplay playsinline ' +
                   (v.thumbnail ? 'poster="' + esc(v.thumbnail) + '" ' : '') +
                   '><source src="' + esc(v.src) + '" type="video/mp4">' +
                   'Your browser does not support the video tag.</video>';
        }
        container.innerHTML = html;

        infoCat.textContent = v.category;
        infoTitle.textContent = v.title;
        infoDesc.textContent = v.description || '';
        infoDur.textContent = v.duration || '—';

        playlistEl.querySelectorAll('.playlist-item').forEach(function (el) {
            el.classList.toggle('active', el.dataset.id === id);
        });

        var activeItem = playlistEl.querySelector('.playlist-item.active');
        if (activeItem) activeItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    /* ═══ OPEN / CLOSE ═══ */
    window.openDemoModal = function () {
        modal.hidden = false;
        document.body.style.overflow = 'hidden';

        if (!videos.length) {
            container.innerHTML = '<div style="padding:40px;text-align:center;color:#64748b;font-size:14px;">No demo videos configured yet.</div>';
            return;
        }

        if (!currentVideoId) {
            var featured = videos.find(function (v) { return v.featured; }) || videos[0];
            if (featured) playVideo(featured.id);
            renderPlaylist(activeCategory);
        }
    };

    window.closeDemoModal = function () {
        modal.hidden = true;
        document.body.style.overflow = '';
        container.innerHTML = '';
    };

    /* ═══ TABS ═══ */
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) { t.classList.remove('active'); });
            this.classList.add('active');
            activeCategory = this.dataset.category;
            renderPlaylist(activeCategory);
        });
    });

    /* ═══ ESC KEY ═══ */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) closeDemoModal();
    });

    /* ═══ ESCAPE HELPER ═══ */
    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
})();
</script>

</div><!-- /.landing-page-v2 -->

<!-- ═══════════════════════════════════════════════════════════
     CONTACT SECTION STYLES (only styles unique to landing content)
     ═══════════════════════════════════════════════════════════ -->
<style>
.lp-contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-top: 20px;
}
.lp-contact-card {
    display: flex; flex-direction: column; gap: 6px;
    padding: 24px 22px;
    background: rgba(30, 41, 59, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    text-decoration: none; color: #cbd5e1;
    transition: all 0.25s ease;
}
.lp-contact-card:hover {
    transform: translateY(-4px);
    border-color: rgba(16, 185, 129, 0.35);
    background: rgba(16, 185, 129, 0.05);
    box-shadow: 0 16px 32px rgba(0, 0, 0, 0.3);
}
.lp-contact-icon { font-size: 30px; margin-bottom: 6px; line-height: 1; }
.lp-contact-card strong {
    font-size: 15px; font-weight: 800;
    color: #f1f5f9; margin-bottom: 2px;
}
.lp-contact-card span:not(.lp-contact-icon) {
    font-size: 13px; color: #94a3b8;
    line-height: 1.5; word-break: break-word;
}
@media (max-width: 480px) {
    .lp-contact-grid { grid-template-columns: 1fr; }
}
</style>