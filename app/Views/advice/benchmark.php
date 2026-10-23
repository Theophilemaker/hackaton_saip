<div class="dashboard-layout advice-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card advice-hero">
            <div>
                <span class="hero-tag">📊 Benchmarking</span>
                <h1>Compare Your Farm</h1>
                <p>See how your yields compare to your district and national averages.</p>
            </div>
            <a href="<?= BASE_URL ?>/advice" class="btn-hero-secondary">← Back</a>
        </section>

        <section class="glass-card">
            <form method="GET" action="<?= BASE_URL ?>/advice/benchmark" class="filter-row">
                <select name="crop" class="filter-select" onchange="this.form.submit()">
                    <option value="">— Select a crop —</option>
                    <?php foreach ($data['crops'] as $c): ?>
                        <option value="<?= e($c) ?>" <?= $data['crop'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </section>

        <?php if (!empty($data['data'])): 
            $d = $data['data'];
            $vsClass = $d['vs_district'] >= 0 ? 'positive' : 'negative';
        ?>
            <section class="glass-card">
                <div class="card-header">
                    <div>
                        <h3><?= e($d['crop']) ?> — <?= e($d['district']) ?></h3>
                        <p class="card-subtitle">Your performance vs district & national averages</p>
                    </div>
                </div>

                <div class="bench-grid">
                    <div class="bench-card you">
                        <span class="bench-label">Your Farm</span>
                        <strong class="bench-val"><?= number_format($d['farmer_yield'], 1) ?></strong>
                        <span class="bench-unit">kg/ha</span>
                    </div>
                    <div class="bench-card district">
                        <span class="bench-label"><?= e($d['district']) ?> Avg</span>
                        <strong class="bench-val"><?= number_format($d['district_avg'], 1) ?></strong>
                        <span class="bench-unit">kg/ha</span>
                    </div>
                    <div class="bench-card national">
                        <span class="bench-label">National Avg</span>
                        <strong class="bench-val"><?= number_format($d['national_avg'], 1) ?></strong>
                        <span class="bench-unit">kg/ha</span>
                    </div>
                </div>

                <?php if ($d['farmer_yield'] > 0): ?>
                    <div class="delta-banner <?= $vsClass ?>">
                        <span class="delta-icon"><?= $vsClass === 'positive' ? '⬆️' : '⬇️' ?></span>
                        <div>
                            <strong>You are <?= abs($d['vs_district']) ?>% <?= $vsClass === 'positive' ? 'above' : 'below' ?> district average</strong>
                            <p><?= $vsClass === 'positive' ? 'Great job! Keep doing what you\'re doing.' : 'Consider reaching out to your cooperative for advice.' ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="info-banner">
                        ℹ️ Enter your last harvest yield in your profile to compare against benchmarks.
                    </div>
                <?php endif; ?>
            </section>
        <?php elseif ($data['crop']): ?>
            <section class="glass-card">
                <div style="text-align:center;padding:60px 24px;">
                    <p style="color:#94a3b8;">No data available for this crop.</p>
                </div>
            </section>
        <?php endif; ?>
    </main>
</div>

<style>
.advice-page.dashboard-layout { display: flex !important; width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; }
.advice-page .main-content { flex: 1 !important; margin-left: 260px !important; padding: 32px !important; box-sizing: border-box !important; }
.advice-page .glass-card { background: rgba(30,41,59,0.65) !important; backdrop-filter: blur(16px) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 20px !important; padding: 28px !important; margin-bottom: 24px !important; }

.advice-hero { background: linear-gradient(135deg, rgba(56,189,248,0.15), rgba(167,139,250,0.08)) !important; display: flex !important; justify-content: space-between !important; align-items: center !important; gap: 24px !important; flex-wrap: wrap !important; }
.hero-tag { display: inline-block !important; padding: 5px 14px !important; background: rgba(56,189,248,0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(56,189,248,0.3) !important; border-radius: 999px !important; font-size: 11px !important; font-weight: 700 !important; text-transform: uppercase !important; margin-bottom: 12px !important; }
.advice-hero h1 { font-size: 28px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.advice-hero p { color: #94a3b8 !important; margin: 0 !important; }
.btn-hero-secondary { padding: 12px 22px !important; background: rgba(255,255,255,0.05) !important; color: #f1f5f9 !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; font-weight: 700 !important; text-decoration: none !important; }

.filter-row { display: flex !important; gap: 12px !important; }
.filter-select { padding: 13px 16px !important; background: rgba(0,0,0,0.35) !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; color: #f1f5f9 !important; font-size: 14px !important; cursor: pointer !important; min-width: 240px !important; }
.filter-select option { background: #1e293b !important; }

.card-header h3 { font-size: 20px !important; color: #f1f5f9 !important; margin: 0 0 4px 0 !important; font-weight: 700 !important; }
.card-subtitle { color: #94a3b8 !important; margin: 0 0 22px 0 !important; font-size: 13px !important; }

.bench-grid { display: grid !important; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important; gap: 16px !important; margin-bottom: 22px !important; }
.bench-card { padding: 22px !important; background: rgba(0,0,0,0.25) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 14px !important; text-align: center !important; position: relative !important; overflow: hidden !important; }
.bench-card::before { content: '' !important; position: absolute !important; top: 0 !important; left: 0 !important; width: 4px !important; height: 100% !important; }
.bench-card.you::before { background: #10b981 !important; }
.bench-card.district::before { background: #38bdf8 !important; }
.bench-card.national::before { background: #a78bfa !important; }
.bench-label { display: block !important; font-size: 11px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.8px !important; margin-bottom: 8px !important; }
.bench-val { display: block !important; font-size: 32px !important; font-weight: 800 !important; color: #f1f5f9 !important; line-height: 1.1 !important; }
.bench-unit { display: block !important; font-size: 12px !important; color: #64748b !important; margin-top: 4px !important; }

.delta-banner { display: flex !important; gap: 16px !important; align-items: flex-start !important; padding: 18px 22px !important; border-radius: 14px !important; }
.delta-banner.positive { background: rgba(16,185,129,0.12) !important; border: 1px solid rgba(16,185,129,0.35) !important; }
.delta-banner.negative { background: rgba(239,68,68,0.12) !important; border: 1px solid rgba(239,68,68,0.35) !important; }
.delta-icon { font-size: 28px !important; }
.delta-banner strong { display: block !important; font-size: 15px !important; color: #f1f5f9 !important; margin-bottom: 4px !important; }
.delta-banner p { margin: 0 !important; font-size: 13px !important; color: #cbd5e1 !important; }

.info-banner { padding: 16px 22px !important; background: rgba(56,189,248,0.08) !important; border-left: 4px solid #38bdf8 !important; border-radius: 10px !important; font-size: 13px !important; color: #cbd5e1 !important; }

@media (max-width: 768px) {
    .advice-page .main-content { margin-left: 0 !important; padding: 16px !important; padding-top: 72px !important; }
}
</style>