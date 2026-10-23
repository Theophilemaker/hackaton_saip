<div class="dashboard-layout advice-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card advice-hero">
            <div>
                <span class="hero-tag">🏆 District League</span>
                <h1><?= e($data['crop']) ?> Rankings</h1>
                <p>See how districts across Rwanda perform on <?= e($data['crop']) ?> yield.</p>
            </div>
            <a href="<?= BASE_URL ?>/advice" class="btn-hero-secondary">← Back</a>
        </section>

        <section class="glass-card">
            <form method="GET" action="<?= BASE_URL ?>/advice/league" class="filter-row">
                <select name="crop" class="filter-select" onchange="this.form.submit()">
                    <?php foreach ($data['crops'] as $c): ?>
                        <option value="<?= e($c) ?>" <?= $data['crop'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </section>

        <?php if (empty($data['table'])): ?>
            <section class="glass-card">
                <div style="text-align:center;padding:60px 24px;">
                    <div style="font-size:60px;margin-bottom:12px;">📊</div>
                    <h3 style="color:#f1f5f9;">No data for this crop</h3>
                    <p style="color:#94a3b8;">Try a different crop or upload more data.</p>
                </div>
            </section>
        <?php else: ?>
            <section class="glass-card">
                <div class="card-header">
                    <div>
                        <h3>Rankings by Avg Yield</h3>
                        <p class="card-subtitle"><?= count($data['table']) ?> districts ranked</p>
                    </div>
                </div>

                <div class="leaderboard">
                    <?php foreach ($data['table'] as $i => $row): 
                        $isYourDistrict = ($data['profile']['district'] ?? '') === $row['district'];
                        $medal = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '#' . ($i + 1)));
                        $maxYield = $data['table'][0]['yield'] ?? 1;
                        $barPct = $maxYield > 0 ? ($row['yield'] / $maxYield) * 100 : 0;
                    ?>
                        <div class="lb-row <?= $isYourDistrict ? 'your-district' : '' ?>">
                            <div class="lb-rank"><?= $medal ?></div>
                            <div class="lb-info">
                                <div class="lb-header">
                                    <strong><?= e($row['district']) ?></strong>
                                    <?php if ($isYourDistrict): ?>
                                        <span class="lb-you">📍 Your district</span>
                                    <?php endif; ?>
                                </div>
                                <div class="lb-bar-wrap">
                                    <div class="lb-bar" style="width:<?= $barPct ?>%"></div>
                                </div>
                            </div>
                            <div class="lb-value">
                                <strong><?= number_format($row['yield'], 1) ?></strong>
                                <small>kg/ha</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
</div>

<style>
.advice-page.dashboard-layout { display: flex !important; width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; }
.advice-page .main-content { flex: 1 !important; margin-left: 260px !important; padding: 32px !important; box-sizing: border-box !important; }
.advice-page .glass-card { background: rgba(30,41,59,0.65) !important; backdrop-filter: blur(16px) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 20px !important; padding: 28px !important; margin-bottom: 24px !important; }

.advice-hero { background: linear-gradient(135deg, rgba(251,191,36,0.15), rgba(245,158,11,0.08)) !important; display: flex !important; justify-content: space-between !important; align-items: center !important; gap: 24px !important; flex-wrap: wrap !important; }
.hero-tag { display: inline-block !important; padding: 5px 14px !important; background: rgba(251,191,36,0.15) !important; color: #fbbf24 !important; border: 1px solid rgba(251,191,36,0.3) !important; border-radius: 999px !important; font-size: 11px !important; font-weight: 700 !important; text-transform: uppercase !important; margin-bottom: 12px !important; }
.advice-hero h1 { font-size: 28px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.advice-hero p { color: #94a3b8 !important; margin: 0 !important; }
.btn-hero-secondary { padding: 12px 22px !important; background: rgba(255,255,255,0.05) !important; color: #f1f5f9 !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; font-weight: 700 !important; text-decoration: none !important; }

.filter-row { display: flex !important; gap: 12px !important; }
.filter-select { padding: 13px 16px !important; background: rgba(0,0,0,0.35) !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; color: #f1f5f9 !important; font-size: 14px !important; cursor: pointer !important; min-width: 240px !important; }
.filter-select option { background: #1e293b !important; }

.card-header h3 { font-size: 20px !important; color: #f1f5f9 !important; margin: 0 0 4px 0 !important; }
.card-subtitle { color: #94a3b8 !important; font-size: 13px !important; margin: 0 0 20px 0 !important; }

.leaderboard { display: flex !important; flex-direction: column !important; gap: 8px !important; }
.lb-row { display: flex !important; align-items: center !important; gap: 16px !important; padding: 14px 20px !important; background: rgba(0,0,0,0.2) !important; border: 1px solid rgba(255,255,255,0.06) !important; border-radius: 12px !important; transition: all 0.2s !important; }
.lb-row:hover { background: rgba(56,189,248,0.04) !important; border-color: rgba(56,189,248,0.2) !important; }
.lb-row.your-district { background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(56,189,248,0.06)) !important; border-color: rgba(16,185,129,0.4) !important; }
.lb-rank { font-size: 22px !important; font-weight: 800 !important; color: #fbbf24 !important; width: 44px !important; text-align: center !important; flex-shrink: 0 !important; }
.lb-info { flex: 1 !important; min-width: 0 !important; }
.lb-header { display: flex !important; align-items: center !important; gap: 10px !important; margin-bottom: 8px !important; flex-wrap: wrap !important; }
.lb-header strong { font-size: 15px !important; color: #f1f5f9 !important; font-weight: 700 !important; }
.lb-you { font-size: 10.5px !important; padding: 3px 10px !important; background: rgba(16,185,129,0.15) !important; color: #34d399 !important; border: 1px solid rgba(16,185,129,0.3) !important; border-radius: 999px !important; font-weight: 700 !important; }
.lb-bar-wrap { height: 8px !important; background: rgba(255,255,255,0.06) !important; border-radius: 999px !important; overflow: hidden !important; }
.lb-bar { height: 100% !important; background: linear-gradient(90deg, #10b981, #38bdf8) !important; border-radius: 999px !important; transition: width 0.4s ease !important; }
.lb-value { text-align: right !important; flex-shrink: 0 !important; }
.lb-value strong { display: block !important; font-size: 18px !important; color: #38bdf8 !important; font-weight: 800 !important; }
.lb-value small { font-size: 11px !important; color: #64748b !important; }

@media (max-width: 768px) {
    .advice-page .main-content { margin-left: 0 !important; padding: 16px !important; padding-top: 72px !important; }
}
</style>