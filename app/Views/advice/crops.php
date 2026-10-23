<?php
function cropEmoji($crop) {
    return match(strtolower($crop)) {
        'maize' => '🌽', 'potatoes' => '🥔', 'beans' => '🫘',
        'rice' => '🍚', 'tomatoes' => '🍅', 'coffee' => '☕',
        'tea' => '🍵', 'bananas' => '🍌', 'cassava' => '🥔',
        default => '🌱',
    };
}
?>
<div class="dashboard-layout advice-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card advice-hero">
            <div>
                <span class="hero-tag">🌱 Personalized</span>
                <h1>What Should I Plant?</h1>
                <p>Ranked by projected revenue for your farm in <?= e($data['profile']['district'] ?? 'your district') ?>.</p>
            </div>
            <a href="<?= BASE_URL ?>/advice" class="btn-hero-secondary">← Back</a>
        </section>

        <?php if (!$data['profile_complete']): ?>
            <section class="glass-card setup-prompt">
                <div class="setup-icon">🚜</div>
                <div class="setup-content">
                    <h3>Complete your profile first</h3>
                    <p>We need your location and land size to generate accurate recommendations.</p>
                    <a href="<?= BASE_URL ?>/advice/profile" class="btn-primary">Complete Profile →</a>
                </div>
            </section>
        <?php elseif (empty($data['recommendations'])): ?>
            <section class="glass-card">
                <div style="text-align:center;padding:60px 24px;">
                    <div style="font-size:60px;margin-bottom:12px;">📊</div>
                    <h3 style="color:#f1f5f9;margin:0 0 8px;">Not enough data yet</h3>
                    <p style="color:#94a3b8;">Upload an agricultural survey dataset so we can generate recommendations.</p>
                    <a href="<?= BASE_URL ?>/nisr/uploadForm" class="btn-primary" style="margin-top:16px;">📤 Upload Dataset</a>
                </div>
            </section>
        <?php else: ?>

            <!-- CONTEXT -->
            <div class="context-strip">
                <div class="ctx-box">
                    <span class="ctx-label">District</span>
                    <strong><?= e($data['profile']['district']) ?></strong>
                </div>
                <div class="ctx-box">
                    <span class="ctx-label">Land Size</span>
                    <strong><?= number_format((float)$data['profile']['land_size_ha'], 2) ?> ha</strong>
                </div>
                <div class="ctx-box">
                    <span class="ctx-label">Soil</span>
                    <strong><?= e(ucfirst($data['profile']['soil_type'] ?? 'loam')) ?></strong>
                </div>
                <div class="ctx-box">
                    <span class="ctx-label">Irrigation</span>
                    <strong><?= !empty($data['profile']['has_irrigation']) ? '✅ Yes' : '❌ No' ?></strong>
                </div>
            </div>

            <!-- RECOMMENDATIONS -->
            <div class="reco-list">
                <?php foreach ($data['recommendations'] as $i => $r): ?>
                    <article class="glass-card reco-card rank-<?= $i + 1 ?>">
                        <?php if ($i === 0): ?>
                            <div class="best-badge">🏆 BEST CHOICE</div>
                        <?php endif; ?>

                        <div class="reco-header">
                            <div class="reco-icon"><?= cropEmoji($r['crop']) ?></div>
                            <div class="reco-title">
                                <h2><?= e($r['crop']) ?></h2>
                                <div class="reco-tags">
                                    <span class="tag diff-<?= strtolower($r['difficulty']) ?>">
                                        <?= $r['difficulty'] === 'Easy' ? '✅' : ($r['difficulty'] === 'Medium' ? '⚡' : '🔥') ?>
                                        <?= e($r['difficulty']) ?>
                                    </span>
                                    <span class="tag risk-<?= strtolower($r['risk']) ?>">
                                        <?= $r['risk'] === 'Low' ? '🟢' : '🟡' ?>
                                        <?= e($r['risk']) ?> risk
                                    </span>
                                    <span class="tag cycle">⏱️ <?= (int)$r['growth_cycle_days'] ?> days</span>
                                </div>
                            </div>
                        </div>

                        <div class="reco-metrics">
                            <div class="metric-block">
                                <span class="metric-label">Yield per hectare</span>
                                <strong class="metric-value"><?= number_format($r['yield_per_ha']) ?> kg</strong>
                            </div>
                            <div class="metric-block">
                                <span class="metric-label">Market price</span>
                                <strong class="metric-value"><?= number_format($r['price_per_kg']) ?> RWF/kg</strong>
                            </div>
                            <div class="metric-block highlight">
                                <span class="metric-label">Projected revenue</span>
                                <strong class="metric-value big"><?= number_format($r['projected_revenue']) ?> RWF</strong>
                            </div>
                        </div>

                        <div class="reco-reasoning">
                            <strong>Why this crop?</strong>
                            <ul>
                                <li>
                                    Your district (<strong><?= e($data['profile']['district']) ?></strong>) averages 
                                    <strong><?= number_format($r['yield_per_ha']) ?> kg/ha</strong> for <?= e($r['crop']) ?>.
                                </li>
                                <li>
                                    Current market price is <strong><?= number_format($r['price_per_kg']) ?> RWF/kg</strong> — 
                                    trending from recent market data.
                                </li>
                                <li>
                                    On your <strong><?= number_format($r['land_size_ha'], 2) ?> ha</strong>, 
                                    that's about <strong><?= number_format($r['projected_revenue']) ?> RWF</strong> in gross revenue.
                                </li>
                            </ul>
                        </div>

                        <div class="reco-actions">
                            <a href="<?= BASE_URL ?>/advice/benchmark?crop=<?= urlencode($r['crop']) ?>" class="btn-secondary-sm">
                                📊 Compare vs district
                            </a>
                            <a href="<?= BASE_URL ?>/advice/league?crop=<?= urlencode($r['crop']) ?>" class="btn-secondary-sm">
                                🏆 District ranking
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- DISCLAIMER -->
            <section class="glass-card disclaimer">
                <p>
                    ℹ️ <strong>How we calculate this:</strong> Yield is computed from NISR agricultural surveys 
                    (district-specific averages). Prices come from recent market data. Projected revenue = 
                    <em>yield × price × land size</em>. This is a guide, not a guarantee — talk to your 
                    local agronomist before switching crops.
                </p>
            </section>

        <?php endif; ?>
    </main>
</div>

<style>
.advice-page.dashboard-layout { display: flex !important; width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; }
.advice-page .main-content { flex: 1 !important; margin-left: 260px !important; padding: 32px !important; box-sizing: border-box !important; min-width: 0 !important; }
.advice-page .glass-card { background: rgba(30,41,59,0.65) !important; backdrop-filter: blur(16px) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 20px !important; padding: 28px !important; margin-bottom: 24px !important; box-sizing: border-box !important; width: 100% !important; }

.advice-hero { background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(56,189,248,0.08)) !important; display: flex !important; justify-content: space-between !important; align-items: center !important; gap: 24px !important; flex-wrap: wrap !important; }
.hero-tag { display: inline-block !important; padding: 5px 14px !important; background: rgba(16,185,129,0.15) !important; color: #34d399 !important; border: 1px solid rgba(16,185,129,0.3) !important; border-radius: 999px !important; font-size: 11px !important; font-weight: 700 !important; letter-spacing: 1px !important; text-transform: uppercase !important; margin-bottom: 12px !important; }
.advice-hero h1 { font-size: 28px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.advice-hero p { color: #94a3b8 !important; font-size: 14px !important; margin: 0 !important; }
.btn-hero-secondary { display: inline-flex !important; align-items: center !important; gap: 8px !important; padding: 12px 22px !important; background: rgba(255,255,255,0.05) !important; color: #f1f5f9 !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; font-weight: 700 !important; text-decoration: none !important; }

.setup-prompt { display: flex !important; gap: 24px !important; align-items: center !important; flex-wrap: wrap !important; background: linear-gradient(135deg, rgba(251,191,36,0.08), rgba(245,158,11,0.04)) !important; border-color: rgba(251,191,36,0.3) !important; }
.setup-icon { font-size: 56px !important; }
.setup-content { flex: 1 !important; }
.setup-content h3 { font-size: 20px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.setup-content p { color: #94a3b8 !important; margin: 0 0 18px 0 !important; }
.btn-primary { display: inline-flex !important; align-items: center !important; gap: 8px !important; padding: 13px 26px !important; background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; border-radius: 10px !important; font-weight: 700 !important; text-decoration: none !important; }

.context-strip { display: grid !important; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important; gap: 14px !important; margin-bottom: 24px !important; }
.ctx-box { padding: 16px 20px !important; background: rgba(30,41,59,0.5) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 14px !important; }
.ctx-label { display: block !important; font-size: 11px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.8px !important; margin-bottom: 6px !important; }
.ctx-box strong { font-size: 16px !important; color: #38bdf8 !important; font-weight: 800 !important; }

.reco-list { display: flex !important; flex-direction: column !important; gap: 20px !important; }
.reco-card { position: relative !important; padding: 28px !important; }
.reco-card.rank-1 { border-color: rgba(16,185,129,0.5) !important; box-shadow: 0 8px 32px rgba(16,185,129,0.2) !important; }

.best-badge { position: absolute !important; top: -12px !important; left: 24px !important; padding: 6px 16px !important; background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; border-radius: 999px !important; font-size: 11px !important; font-weight: 800 !important; letter-spacing: 1px !important; box-shadow: 0 4px 15px rgba(16,185,129,0.4) !important; }

.reco-header { display: flex !important; gap: 18px !important; align-items: flex-start !important; margin-bottom: 22px !important; }
.reco-icon { font-size: 48px !important; flex-shrink: 0 !important; }
.reco-title h2 { font-size: 24px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.reco-tags { display: flex !important; gap: 8px !important; flex-wrap: wrap !important; }
.tag { padding: 4px 12px !important; border-radius: 999px !important; font-size: 11px !important; font-weight: 700 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; }
.tag.diff-easy { background: rgba(16,185,129,0.15) !important; color: #34d399 !important; border: 1px solid rgba(16,185,129,0.3) !important; }
.tag.diff-medium { background: rgba(251,191,36,0.15) !important; color: #fbbf24 !important; border: 1px solid rgba(251,191,36,0.3) !important; }
.tag.diff-hard { background: rgba(239,68,68,0.15) !important; color: #f87171 !important; border: 1px solid rgba(239,68,68,0.3) !important; }
.tag.risk-low { background: rgba(16,185,129,0.12) !important; color: #6ee7b7 !important; }
.tag.risk-medium { background: rgba(251,191,36,0.12) !important; color: #fbbf24 !important; }
.tag.cycle { background: rgba(56,189,248,0.12) !important; color: #38bdf8 !important; }

.reco-metrics { display: grid !important; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) !important; gap: 14px !important; margin-bottom: 22px !important; padding: 20px !important; background: rgba(0,0,0,0.25) !important; border-radius: 14px !important; }
.metric-block { text-align: center !important; }
.metric-block.highlight { position: relative !important; }
.metric-block.highlight::before { content: '' !important; position: absolute !important; top: -8px !important; left: 50% !important; transform: translateX(-50%) !important; width: 40px !important; height: 3px !important; background: linear-gradient(90deg, #10b981, #38bdf8) !important; border-radius: 999px !important; }
.metric-label { display: block !important; font-size: 11px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.8px !important; margin-bottom: 6px !important; }
.metric-value { display: block !important; font-size: 18px !important; font-weight: 800 !important; color: #cbd5e1 !important; }
.metric-value.big { font-size: 24px !important; color: #34d399 !important; }

.reco-reasoning { padding: 18px 22px !important; background: rgba(56,189,248,0.06) !important; border-left: 4px solid #38bdf8 !important; border-radius: 12px !important; margin-bottom: 18px !important; }
.reco-reasoning strong { display: block !important; color: #38bdf8 !important; font-size: 13px !important; margin-bottom: 10px !important; text-transform: uppercase !important; letter-spacing: 0.8px !important; }
.reco-reasoning ul { list-style: none !important; padding: 0 !important; margin: 0 !important; }
.reco-reasoning li { padding: 5px 0 !important; font-size: 13.5px !important; color: #cbd5e1 !important; line-height: 1.5 !important; }
.reco-reasoning li strong { display: inline !important; color: #f1f5f9 !important; text-transform: none !important; letter-spacing: normal !important; margin: 0 !important; font-size: 13.5px !important; }

.reco-actions { display: flex !important; gap: 12px !important; flex-wrap: wrap !important; }
.btn-secondary-sm { padding: 10px 20px !important; background: rgba(255,255,255,0.05) !important; color: #cbd5e1 !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; font-size: 13px !important; font-weight: 700 !important; text-decoration: none !important; }
.btn-secondary-sm:hover { background: rgba(56,189,248,0.15) !important; color: #38bdf8 !important; border-color: rgba(56,189,248,0.4) !important; }

.disclaimer { background: rgba(56,189,248,0.06) !important; border-left: 4px solid #38bdf8 !important; }
.disclaimer p { font-size: 13px !important; color: #cbd5e1 !important; margin: 0 !important; line-height: 1.6 !important; }
.disclaimer strong { color: #38bdf8 !important; }

@media (max-width: 768px) {
    .advice-page .main-content { margin-left: 0 !important; padding: 16px !important; padding-top: 72px !important; }
    .reco-header { flex-direction: column !important; }
}
</style>