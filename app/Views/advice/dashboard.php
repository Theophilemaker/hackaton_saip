<div class="dashboard-layout advice-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- FLASH -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="flash success">✅ <?= $_SESSION['flash_success'] ?></div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <!-- HERO -->
        <section class="glass-card advice-hero">
            <div class="hero-content">
                <span class="hero-tag">🌱 Personalized for you</span>
                <h1>My Farm Advice</h1>
                <p>Data-driven recommendations based on NISR surveys, market prices, and your location.</p>
            </div>
            <a href="<?= BASE_URL ?>/advice/profile" class="btn-hero-secondary">⚙️ Edit Profile</a>
        </section>

        <!-- PROFILE INCOMPLETE -->
        <?php if (!$data['profile_complete']): ?>
            <section class="glass-card setup-prompt">
                <div class="setup-icon">🚜</div>
                <div class="setup-content">
                    <h3>Let's get you set up</h3>
                    <p>Tell us where you farm and what you grow — we'll generate personalized recommendations in seconds.</p>
                    <a href="<?= BASE_URL ?>/advice/profile" class="btn-primary">Complete My Profile →</a>
                </div>
            </section>
        <?php else: ?>

            <!-- CONTEXT BAR -->
            <div class="context-bar">
                <span class="ctx-item">📍 <strong><?= e($data['profile']['district']) ?></strong></span>
                <?php if ($data['profile']['sector']): ?>
                    <span class="ctx-item">🏘️ <?= e($data['profile']['sector']) ?></span>
                <?php endif; ?>
                <span class="ctx-item">📐 <?= number_format((float)$data['profile']['land_size_ha'], 2) ?> ha</span>
                <?php 
                $crops = json_decode($data['profile']['primary_crops'] ?? '[]', true) ?: [];
                if ($crops): ?>
                    <span class="ctx-item">🌾 <?= e(implode(', ', $crops)) ?></span>
                <?php endif; ?>
            </div>

            <!-- ALERTS -->
            <?php if (!empty($data['alerts'])): ?>
                <section class="glass-card alerts-section">
                    <h3>⚠️ Your Alerts</h3>
                    <?php foreach ($data['alerts'] as $a): ?>
                        <div class="alert-item sev-<?= $a['severity'] ?>">
                            <strong><?= e($a['title']) ?></strong>
                            <p><?= e($a['message']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <!-- QUICK ACTIONS -->
            <div class="action-grid">
                <a href="<?= BASE_URL ?>/advice/crops" class="action-card plant">
                    <span class="action-icon">🌾</span>
                    <div>
                        <strong>What Should I Plant?</strong>
                        <small>Top crops for your location</small>
                    </div>
                </a>
                <a href="<?= BASE_URL ?>/advice/benchmark" class="action-card bench">
                    <span class="action-icon">📊</span>
                    <div>
                        <strong>Benchmark</strong>
                        <small>Compare vs district</small>
                    </div>
                </a>
                <a href="<?= BASE_URL ?>/advice/league" class="action-card league">
                    <span class="action-icon">🏆</span>
                    <div>
                        <strong>District Rankings</strong>
                        <small>See who leads</small>
                    </div>
                </a>
            </div>

            <!-- TOP RECOMMENDATIONS PREVIEW -->
            <?php if (!empty($data['recommendations'])): ?>
                <section class="glass-card">
                    <div class="card-header">
                        <div>
                            <h3>🎯 Top Recommendations</h3>
                            <p class="card-subtitle">Based on your district and land size</p>
                        </div>
                        <a href="<?= BASE_URL ?>/advice/crops" class="btn-secondary-sm">See All →</a>
                    </div>

                    <div class="rec-preview-grid">
                        <?php foreach ($data['recommendations'] as $i => $r): ?>
                            <div class="rec-preview-card">
                                <div class="rank-badge">#<?= $i + 1 ?></div>
                                <div class="crop-icon"><?= cropEmoji($r['crop']) ?></div>
                                <h4><?= e($r['crop']) ?></h4>
                                <div class="rec-metric">
                                    <span class="metric-label">Projected revenue</span>
                                    <strong class="metric-val"><?= number_format($r['projected_revenue']) ?> RWF</strong>
                                </div>
                                <div class="rec-meta">
                                    <span>🌱 <?= number_format($r['yield_per_ha']) ?> kg/ha</span>
                                    <span>💰 <?= number_format($r['price_per_kg']) ?> RWF/kg</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        <?php endif; ?>
    </main>
</div>

<?php
// Emoji helper (put at top of file or in a helper functions file)
function cropEmoji($crop) {
    return match(strtolower($crop)) {
        'maize' => '🌽', 'potatoes' => '🥔', 'beans' => '🫘',
        'rice' => '🍚', 'tomatoes' => '🍅', 'coffee' => '☕',
        'tea' => '🍵', 'bananas' => '🍌', 'cassava' => '🥔',
        default => '🌱',
    };
}
?>

<style>
.advice-page.dashboard-layout { display: flex !important; width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; }
.advice-page .main-content { flex: 1 !important; margin-left: 260px !important; padding: 32px !important; box-sizing: border-box !important; min-width: 0 !important; }
.advice-page .glass-card { background: rgba(30,41,59,0.65) !important; backdrop-filter: blur(16px) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 20px !important; padding: 28px !important; margin-bottom: 24px !important; width: 100% !important; box-sizing: border-box !important; }

.flash.success { background: rgba(16,185,129,0.15) !important; border: 1px solid rgba(16,185,129,0.4) !important; color: #6ee7b7 !important; padding: 14px 22px !important; border-radius: 12px !important; margin-bottom: 20px !important; font-weight: 600 !important; }

.advice-hero { background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(56,189,248,0.08)) !important; display: flex !important; justify-content: space-between !important; align-items: center !important; gap: 24px !important; flex-wrap: wrap !important; }
.hero-tag { display: inline-block !important; padding: 5px 14px !important; background: rgba(16,185,129,0.15) !important; color: #34d399 !important; border: 1px solid rgba(16,185,129,0.3) !important; border-radius: 999px !important; font-size: 11px !important; font-weight: 700 !important; letter-spacing: 1px !important; text-transform: uppercase !important; margin-bottom: 12px !important; }
.advice-hero h1 { font-size: 28px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.advice-hero p { color: #94a3b8 !important; font-size: 14px !important; margin: 0 !important; }
.btn-hero-secondary { display: inline-flex !important; align-items: center !important; gap: 8px !important; padding: 12px 22px !important; background: rgba(255,255,255,0.05) !important; color: #f1f5f9 !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; font-weight: 700 !important; text-decoration: none !important; }

.setup-prompt { display: flex !important; gap: 24px !important; align-items: center !important; flex-wrap: wrap !important; background: linear-gradient(135deg, rgba(251,191,36,0.08), rgba(245,158,11,0.04)) !important; border-color: rgba(251,191,36,0.3) !important; }
.setup-icon { font-size: 56px !important; }
.setup-content { flex: 1 !important; min-width: 260px !important; }
.setup-content h3 { font-size: 20px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.setup-content p { color: #94a3b8 !important; margin: 0 0 18px 0 !important; font-size: 14px !important; }
.btn-primary { display: inline-flex !important; align-items: center !important; gap: 8px !important; padding: 13px 26px !important; background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; border-radius: 10px !important; font-weight: 700 !important; text-decoration: none !important; box-shadow: 0 4px 15px rgba(16,185,129,0.3) !important; }

.context-bar { display: flex !important; gap: 12px !important; flex-wrap: wrap !important; margin-bottom: 20px !important; }
.ctx-item { display: inline-flex !important; align-items: center !important; gap: 6px !important; padding: 8px 16px !important; background: rgba(255,255,255,0.04) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 999px !important; font-size: 13px !important; color: #cbd5e1 !important; }
.ctx-item strong { color: #38bdf8 !important; }

.action-grid { display: grid !important; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)) !important; gap: 16px !important; margin-bottom: 24px !important; }
.action-card { display: flex !important; gap: 14px !important; align-items: center !important; padding: 20px 22px !important; background: rgba(30,41,59,0.55) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 16px !important; text-decoration: none !important; transition: all 0.25s ease !important; position: relative !important; overflow: hidden !important; }
.action-card::before { content: '' !important; position: absolute !important; top: 0 !important; left: 0 !important; width: 4px !important; height: 100% !important; }
.action-card.plant::before { background: #10b981 !important; }
.action-card.bench::before { background: #38bdf8 !important; }
.action-card.league::before { background: #fbbf24 !important; }
.action-card:hover { transform: translateY(-4px) !important; border-color: rgba(56,189,248,0.4) !important; box-shadow: 0 12px 32px rgba(0,0,0,0.4) !important; }
.action-icon { font-size: 28px !important; flex-shrink: 0 !important; }
.action-card strong { display: block !important; font-size: 15px !important; color: #f1f5f9 !important; margin-bottom: 4px !important; }
.action-card small { display: block !important; font-size: 12.5px !important; color: #94a3b8 !important; }

.card-header { display: flex !important; justify-content: space-between !important; align-items: center !important; margin-bottom: 20px !important; padding-bottom: 14px !important; border-bottom: 1px solid rgba(255,255,255,0.08) !important; flex-wrap: wrap !important; gap: 12px !important; }
.card-header h3 { font-size: 18px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 !important; }
.card-subtitle { font-size: 12.5px !important; color: #94a3b8 !important; margin: 4px 0 0 0 !important; }
.btn-secondary-sm { padding: 9px 16px !important; background: rgba(255,255,255,0.05) !important; color: #cbd5e1 !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; font-size: 12.5px !important; font-weight: 700 !important; text-decoration: none !important; }

.rec-preview-grid { display: grid !important; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important; gap: 16px !important; }
.rec-preview-card { position: relative !important; padding: 22px !important; background: rgba(0,0,0,0.25) !important; border: 1px solid rgba(255,255,255,0.06) !important; border-radius: 14px !important; text-align: center !important; }
.rank-badge { position: absolute !important; top: 12px !important; right: 12px !important; padding: 3px 10px !important; background: linear-gradient(135deg, #10b981, #38bdf8) !important; color: #fff !important; border-radius: 999px !important; font-size: 11px !important; font-weight: 800 !important; }
.crop-icon { font-size: 42px !important; margin-bottom: 8px !important; }
.rec-preview-card h4 { font-size: 16px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 0 14px 0 !important; }
.rec-metric { margin-bottom: 10px !important; }
.metric-label { display: block !important; font-size: 10.5px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.8px !important; margin-bottom: 4px !important; }
.metric-val { display: block !important; font-size: 18px !important; font-weight: 800 !important; color: #34d399 !important; }
.rec-meta { display: flex !important; justify-content: center !important; gap: 14px !important; font-size: 11.5px !important; color: #cbd5e1 !important; }

.alerts-section h3 { font-size: 16px !important; color: #f1f5f9 !important; margin: 0 0 16px 0 !important; }
.alert-item { padding: 14px 18px !important; border-radius: 12px !important; margin-bottom: 10px !important; border-left: 4px solid #38bdf8 !important; background: rgba(0,0,0,0.2) !important; }
.alert-item.sev-warning  { border-left-color: #fbbf24 !important; }
.alert-item.sev-critical { border-left-color: #ef4444 !important; }
.alert-item strong { display: block !important; font-size: 13.5px !important; color: #f1f5f9 !important; margin-bottom: 4px !important; }
.alert-item p { margin: 0 !important; font-size: 12.5px !important; color: #cbd5e1 !important; }

@media (max-width: 768px) {
    .advice-page .main-content { margin-left: 0 !important; padding: 16px !important; padding-top: 72px !important; }
    .action-grid { grid-template-columns: 1fr !important; }
}
</style>