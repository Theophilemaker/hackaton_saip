<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- HERO -->
        <section class="glass-card gov-hero">
            <div>
                <h1>🏛️ <?= __('gov.title') ?? 'Government Analytics' ?></h1>
                <p><?= __('gov.subtitle') ?? 'National Agricultural Intelligence & NST2 Alignment' ?></p>
                <small>Last updated: <?= htmlspecialchars($data['kpis']['computed_at'] ?? date('Y-m-d H:i:s')) ?></small>
            </div>
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>/government/nst2Theory" class="btn-primary theory-cta">
                    📖 View NST2 Theory
                </a>
                <a href="<?= BASE_URL ?>/government/generateReport" target="_blank" class="btn-secondary">📄 Report</a>
                <a href="<?= BASE_URL ?>/government/export" class="btn-secondary">📊 CSV</a>
            </div>
        </section>

        <!-- KPI CARDS -->
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background:linear-gradient(135deg,#16A34A,#22C55E)">👨‍🌾</div>
                <div><p class="kpi-label">Farmers</p><h3 class="kpi-value"><?= number_format((int)($data['kpis']['registered_farmers'] ?? 0)) ?></h3></div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background:linear-gradient(135deg,#84CC16,#16A34A)">🌾</div>
                <div><p class="kpi-label">Production</p><h3 class="kpi-value"><?= number_format(((float)($data['kpis']['total_production_kg'] ?? 0))/1000,1) ?> <small>t</small></h3></div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background:linear-gradient(135deg,#F59E0B,#EF4444)">💰</div>
                <div><p class="kpi-label">Market Value</p><h3 class="kpi-value"><?= number_format(((float)($data['kpis']['market_value_rwf'] ?? 0))/1e9,2) ?> <small>B RWF</small></h3></div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background:linear-gradient(135deg,#0EA5E9,#6366F1)">🏡</div>
                <div><p class="kpi-label">Farms</p><h3 class="kpi-value"><?= number_format((int)($data['kpis']['active_farms'] ?? 0)) ?></h3></div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background:linear-gradient(135deg,#EF4444,#DC2626)">🚨</div>
                <div><p class="kpi-label">Alerts</p><h3 class="kpi-value"><?= number_format((int)($data['kpis']['active_alerts'] ?? 0)) ?></h3></div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background:linear-gradient(135deg,#6366F1,#8B5CF6)">📍</div>
                <div><p class="kpi-label">Districts</p><h3 class="kpi-value"><?= number_format((int)($data['kpis']['districts_covered'] ?? 0)) ?>/30</h3></div>
            </div>
        </section>

        <!-- =========================================================
             NEW: NST2 & TOTAL IMPACT TRACKER
             ========================================================= -->
        <section class="glass-card" style="background: linear-gradient(135deg, rgba(16,185,129,.15), rgba(2,132,199,.08)); margin-bottom: 24px; padding: 24px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
                <div style="flex: 1; min-width: 300px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <h2 style="color: #34d399; margin: 0; font-size: 20px; font-weight: 800;">
                            <i class="fas fa-chart-line"></i> NST2 & Total Impact Tracker
                        </h2>
                        <span class="ds-pill" style="background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                            📊 Powered by NISR SAS 2025
                        </span>
                    </div>
                    <p style="color: #94a3b8; font-size: 14px; margin-bottom: 16px;">
                        SAIP is directly aligned with Rwanda's National Strategy for Transformation (NST2) and Vision 2050 targets.
                    </p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                        <div style="background: rgba(15,23,42,.6); padding: 16px; border-radius: 12px; border-left: 4px solid #10b981;">
                            <div style="font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700;">Productivity Goal</div>
                            <div style="font-size: 24px; font-weight: 800; color: #f1f5f9; margin: 4px 0;">+50%</div>
                            <div style="font-size: 12px; color: #cbd5e1;">NST2 target for priority crops by 2035</div>
                        </div>
                        <div style="background: rgba(15,23,42,.6); padding: 16px; border-radius: 12px; border-left: 4px solid #38bdf8;">
                            <div style="font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700;">Post-Harvest Loss Goal</div>
                            <div style="font-size: 24px; font-weight: 800; color: #f1f5f9; margin: 4px 0;">&lt; 5%</div>
                            <div style="font-size: 12px; color: #cbd5e1;">Target reduction (currently 13.8% for maize)</div>
                        </div>
                        <div style="background: rgba(15,23,42,.6); padding: 16px; border-radius: 12px; border-left: 4px solid #fbbf24;">
                            <div style="font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700;">Projected Economic Unlock</div>
                            <div style="font-size: 24px; font-weight: 800; color: #fbbf24; margin: 4px 0;">+RWF 2.4B</div>
                            <div style="font-size: 12px; color: #cbd5e1;">If lime adoption reaches 10,000 farmers</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
             NST2 STRATEGY PORTAL — FULL DRILL-DOWN
             ========================================================= -->
        <?php
        $strategyMap = [
            'economic' => [
                'label' => 'Economic Transformation', 'icon'  => '📈', 'color' => '#22C55E',
                'desc'  => 'Modern agriculture, jobs, exports, digital economy (NST2 2024–2029)',
                'strategies' => [
                    'agri'       => ['label' => 'Modern Agriculture & Productivity',  'icon' => '🌾', 'codes' => ['AGRI.YIELD.MAIZE','AGRI.YIELD.RICE','AGRI.YIELD.POTATO','AGRI.YIELD.COFFEE','AGRI.POSTHARVEST','AGRI.LIVESTOCK']],
                    'irrigation' => ['label' => 'Irrigation & Water Management',     'icon' => '💧', 'codes' => ['AGRI.IRRIG.COVER']],
                    'jobs'       => ['label' => 'Decent & Productive Jobs',           'icon' => '👷', 'codes' => ['AGRI.JOBS']],
                    'value'      => ['label' => 'Value-Added & Agro-Processing',      'icon' => '🏭', 'codes' => ['AGRI.VALUE.ADD']],
                    'exports'    => ['label' => 'Export-Led Growth',                  'icon' => '🚢', 'codes' => ['AGRI.EXPORT.VAL']],
                ],
            ],
            'social' => [
                'label' => 'Social Transformation', 'icon'  => '👥', 'color' => '#0EA5E9',
                'desc'  => 'Digital inclusion, nutrition, gender, youth (NST2 2024–2029)',
                'strategies' => [
                    'digital'    => ['label' => 'Digital Inclusion & Literacy',       'icon' => '📱', 'codes' => ['SOC.FARMER.DIG','SOC.DIGITAL.LIT']],
                    'nutrition'  => ['label' => 'Nutrition & Food Security',          'icon' => '🍽️', 'codes' => ['SOC.NUTRITION']],
                    'women'      => ['label' => 'Gender Equality & Women Empowerment','icon' => '♀️', 'codes' => ['SOC.INCLUSION.WOMEN']],
                    'youth'      => ['label' => 'Youth Empowerment',                  'icon' => '🎓', 'codes' => ['SOC.INCLUSION.YOUTH']],
                    'protection' => ['label' => 'Social Protection',                  'icon' => '🤝', 'codes' => ['SOC.SOCIAL.PROT']],
                ],
            ],
            'governance' => [
                'label' => 'Transformational Governance', 'icon'  => '🏛️', 'color' => '#F59E0B',
                'desc'  => 'Evidence-based policy, e-services, transparency (NST2 2024–2029)',
                'strategies' => [
                    'evidence'       => ['label' => 'Evidence-Based Planning',        'icon' => '📊', 'codes' => ['GOV.EVIDENCE.PLAN']],
                    'eservices'      => ['label' => 'E-Government Services',          'icon' => '💻', 'codes' => ['GOV.E.SERVICES']],
                    'transparency'   => ['label' => 'Budget Transparency',            'icon' => '🔍', 'codes' => ['GOV.TRANSPARENCY']],
                    'decentral'      => ['label' => 'Decentralization',              'icon' => '🏘️', 'codes' => ['GOV.DECENTRALIZ']],
                    'anticorruption' => ['label' => 'Anti-Corruption',              'icon' => '🛡️', 'codes' => ['GOV.ANTICORRUP']],
                ],
            ],
            'vision2050' => [
                'label' => 'Vision 2050 (Long-Term)', 'icon'  => '🎯', 'color' => '#7C3AED',
                'desc'  => 'High-income status, prosperity, longevity, resilience',
                'strategies' => [
                    'income'  => ['label' => 'High-Income Economy',           'icon' => '💵', 'codes' => ['V2050.INCOME.STATUS']],
                    'gdp'     => ['label' => 'Economic Structural Change',    'icon' => '📉', 'codes' => ['V2050.AGRI.GDP']],
                    'trade'   => ['label' => 'Global Trade & Exports',        'icon' => '🌍', 'codes' => ['V2050.EXPORTS']],
                    'urban'   => ['label' => 'Sustainable Urbanization',      'icon' => '🏙️', 'codes' => ['V2050.URBANIZATION']],
                    'health'  => ['label' => 'Health & Longevity',            'icon' => '❤️', 'codes' => ['V2050.LIFE.EXPECT']],
                    'connect' => ['label' => 'Universal Connectivity',        'icon' => '📡', 'codes' => ['V2050.INTERNET']],
                    'climate' => ['label' => 'Climate Resilience',            'icon' => '🌱', 'codes' => ['V2050.CLIMATE']],
                    'food'    => ['label' => 'Food Self-Sufficiency',         'icon' => '🍚', 'codes' => ['V2050.FOOD.SEC']],
                ],
            ],
        ];

        $indicatorIndex = [];
        foreach (($data['nst2'] ?? []) as $row) {
            $current  = (float)($row['current_value'] ?? 0);
            $target   = (float)($row['target_2029']   ?? 1);
            $baseline = (float)($row['baseline']      ?? 0);

            $row['progress_pct']    = $target > 0 ? min(100, round(($current / $target) * 100, 1)) : 0;
            $row['improvement_pct'] = ($baseline > 0 && $current > $baseline) ? round((($current - $baseline) / $baseline) * 100, 1) : 0;
            $row['on_track']        = $row['progress_pct'] >= 60;
            $row['status_class']    = $row['progress_pct'] >= 75 ? 'excellent' : ($row['progress_pct'] >= 50 ? 'good' : ($row['progress_pct'] >= 25 ? 'warning' : 'critical'));

            $indicatorIndex[$row['code']] = $row;
        }

        $totalInd = count($indicatorIndex);
        $onTrack  = 0;
        foreach ($indicatorIndex as $i) if (!empty($i['on_track'])) $onTrack++;
        $overallPct = $totalInd > 0 ? round(($onTrack / $totalInd) * 100) : 0;
        ?>

        <?php if ($totalInd > 0): ?>
        <section class="nst2-portal glass-card">
            <div class="portal-header">
                <div class="ph-left">
                    <span class="portal-icon">🎯</span>
                    <div>
                        <h2>NST2 &amp; Vision 2050 Strategy Portal</h2>
                        <p>Rwanda's National Strategy for Transformation II (2024–2029) · Vision 2050</p>
                    </div>
                </div>
                <div class="ph-right">
                    <div class="overall-ring">
                        <svg viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="8"/>
                            <circle cx="50" cy="50" r="40" fill="none" stroke="url(#nst2Grad)" stroke-width="8" stroke-dasharray="<?= round($overallPct * 2.51, 2) ?> 251" transform="rotate(-90 50 50)" stroke-linecap="round"/>
                            <defs>
                                <linearGradient id="nst2Grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#22C55E"/>
                                    <stop offset="100%" stop-color="#7C3AED"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="ring-label">
                            <strong><?= $overallPct ?>%</strong>
                            <small>on track</small>
                        </div>
                    </div>
                    <div class="ph-stats">
                        <div><strong><?= $onTrack ?></strong><small>On track</small></div>
                        <div><strong><?= $totalInd ?></strong><small>Indicators</small></div>
                    </div>
                </div>
            </div>

            <div class="portal-tabs">
                <?php $first = true; foreach ($strategyMap as $key => $pillar): ?>
                    <button class="portal-tab <?= $first ? 'active' : '' ?>" style="--pillar-color: <?= $pillar['color'] ?>" data-pillar="<?= $key ?>" onclick="showPillar('<?= $key ?>')">
                        <span><?= $pillar['icon'] ?></span>
                        <?= htmlspecialchars($pillar['label']) ?>
                        <span class="tab-count"><?= count($pillar['strategies']) ?></span>
                    </button>
                <?php $first = false; endforeach; ?>
            </div>

            <?php $firstPillar = true; foreach ($strategyMap as $pKey => $pillar): ?>
                <div class="pillar-panel <?= $firstPillar ? 'active' : '' ?>" id="pillar-<?= $pKey ?>" style="--pillar-color: <?= $pillar['color'] ?>">
                    <div class="pillar-intro">
                        <span class="pillar-badge" style="background:<?= $pillar['color'] ?>22; color:<?= $pillar['color'] ?>">
                            <?= $pillar['icon'] ?> <?= htmlspecialchars($pillar['label']) ?>
                        </span>
                        <p><?= htmlspecialchars($pillar['desc']) ?></p>
                    </div>

                    <div class="strategy-buttons">
                        <?php foreach ($pillar['strategies'] as $sKey => $strat): ?>
                            <button class="strategy-btn" style="--pillar-color: <?= $pillar['color'] ?>" data-target="strat-<?= $pKey ?>-<?= $sKey ?>" onclick="showStrategy('<?= $pKey ?>','<?= $sKey ?>')">
                                <span class="strat-icon"><?= $strat['icon'] ?></span>
                                <span class="strat-label"><?= htmlspecialchars($strat['label']) ?></span>
                                <span class="strat-count"><?= count($strat['codes']) ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="strategy-panels">
                        <?php $firstStrat = true; foreach ($pillar['strategies'] as $sKey => $strat): ?>
                            <div class="strategy-panel <?= $firstStrat ? 'active' : '' ?>" id="strat-<?= $pKey ?>-<?= $sKey ?>">
                                <h3 class="strategy-title">
                                    <span style="color:<?= $pillar['color'] ?>"><?= $strat['icon'] ?></span>
                                    <?= htmlspecialchars($strat['label']) ?>
                                </h3>

                                <div class="indicator-grid">
                                    <?php
                                    $rendered = 0;
                                    foreach ($strat['codes'] as $code):
                                        if (!isset($indicatorIndex[$code])) continue;
                                        $item = $indicatorIndex[$code];
                                        $rendered++;
                                    ?>
                                        <div class="indicator-card status-<?= $item['status_class'] ?>">
                                            <div class="indicator-header">
                                                <div class="indicator-icon" style="background:<?= $pillar['color'] ?>22; color:<?= $pillar['color'] ?>">
                                                    <?= $strat['icon'] ?>
                                                </div>
                                                <div class="indicator-title">
                                                    <strong><?= htmlspecialchars($item['name'] ?? '') ?></strong>
                                                    <small><?= htmlspecialchars($item['code'] ?? '') ?></small>
                                                </div>
                                                <div class="indicator-badge <?= $item['status_class'] ?>">
                                                    <?= !empty($item['on_track']) ? '✅' : '⚠️' ?>
                                                </div>
                                            </div>

                                            <div class="indicator-values">
                                                <div class="iv-item">
                                                    <small>Baseline</small>
                                                    <strong><?= number_format((float)($item['baseline'] ?? 0), 1) ?></strong>
                                                </div>
                                                <div class="iv-item highlight">
                                                    <small>Current</small>
                                                    <strong><?= number_format((float)($item['current_value'] ?? 0), 1) ?></strong>
                                                </div>
                                                <div class="iv-item">
                                                    <small>Target 2029</small>
                                                    <strong><?= number_format((float)($item['target_2029'] ?? 0), 1) ?></strong>
                                                </div>
                                            </div>

                                            <div class="indicator-unit"><?= htmlspecialchars($item['unit'] ?? '') ?></div>

                                            <div class="indicator-progress">
                                                <div class="ip-track">
                                                    <div class="ip-fill status-<?= $item['status_class'] ?>" style="width:<?= $item['progress_pct'] ?>%"></div>
                                                </div>
                                                <div class="ip-meta">
                                                    <span><?= $item['progress_pct'] ?>% achieved</span>
                                                    <span class="trend-badge <?= $item['trend'] ?? 'flat' ?>">
                                                        <?php if (($item['trend'] ?? '') === 'up'): ?>▲ <?= $item['improvement_pct'] ?>%
                                                        <?php elseif (($item['trend'] ?? '') === 'down'): ?>▼ <?= $item['improvement_pct'] ?>%
                                                        <?php else: ?>— Flat
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if ($rendered === 0): ?>
                                        <div class="empty-state">No indicators linked to this strategy yet.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php $firstStrat = false; endforeach; ?>
                    </div>
                </div>
            <?php $firstPillar = false; endforeach; ?>
        </section>
        <?php else: ?>
            <section class="glass-card">
                <p class="empty-state">No NST2 indicators loaded. Run the seed SQL.</p>
            </section>
        <?php endif; ?>

        <!-- AI INSIGHTS -->
        <section class="glass-card gov-insights-card" style="max-width:none;">
            <div class="card-header"><h3>🤖 <?= __('gov.ai_insights') ?? 'AI Insights' ?></h3></div>
            <div class="insights-list">
                <?php if (empty($data['insights'])): ?>
                    <p class="empty-state">No insights yet.</p>
                <?php else: ?>
                    <?php foreach ($data['insights'] as $ins): ?>
                        <div class="insight-item">
                            <span class="insight-icon"><?= htmlspecialchars($ins['icon'] ?? '💡') ?></span>
                            <div>
                                <strong><?= htmlspecialchars($ins['title'] ?? '') ?></strong>
                                <p><?= htmlspecialchars($ins['body'] ?? '') ?></p>
                                <small>Confidence: <?= round(((float)($ins['confidence'] ?? 0)) * 100) ?>%</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- =========================================================
             NEW: PREDICTIVE MARKET ALERTS
             ========================================================= -->
        <section class="glass-card" style="border-left: 4px solid #f59e0b;">
            <div class="card-header">
                <h3>📉 Predictive Market Alerts (AI)</h3>
            </div>
            <div class="insights-list">
                <div class="insight-item">
                    <span class="insight-icon">⚠️</span>
                    <div>
                        <strong style="color: #fbbf24;">Bumper Harvest Alert: Maize in Musanze</strong>
                        <p>Predicted yield is 22% above district average. Expect market prices to drop due to oversupply. Advise farmers to consider value addition (e.g., processing into flour) or storage.</p>
                    </div>
                </div>
                <div class="insight-item">
                    <span class="insight-icon">📈</span>
                    <div>
                        <strong style="color: #34d399;">Supply Shortage Alert: Beans in Kirehe</strong>
                        <p>Predicted yield is below average due to erratic rainfall. Market prices are likely to spike by 15% in 3 weeks. Advise farmers to hold stock if possible.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ACTIVE ALERTS -->
        <section class="glass-card">
            <div class="card-header">
                <h3>🚨 <?= __('gov.active_alerts') ?? 'Active Alerts' ?></h3>
                <a href="<?= BASE_URL ?>/government/alerts" class="btn-sm">Manage</a>
            </div>
            <?php if (empty($data['alerts'])): ?>
                <p class="empty-state">No active alerts. All clear ✅</p>
            <?php else: ?>
                <?php foreach (array_slice($data['alerts'], 0, 5) as $a): ?>
                    <div class="alert-row">
                        <span class="severity-pill <?= htmlspecialchars($a['severity'] ?? 'medium') ?>">
                            <?= strtoupper(htmlspecialchars($a['severity'] ?? 'medium')) ?>
                        </span>
                        <div>
                            <strong><?= htmlspecialchars($a['title'] ?? '') ?></strong>
                            <small><?= htmlspecialchars($a['district_name'] ?? 'National') ?> · <?= date('M d, H:i', strtotime($a['created_at'] ?? 'now')) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</div>

<style>
/* Keep all your existing styles below this line */
.gov-hero {
    padding: 30px !important; max-width: none;
    background: linear-gradient(135deg, rgba(22,163,74,0.2), rgba(132,204,22,0.08));
    display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;
}
.gov-hero h1 { margin: 0 0 6px; font-size: 26px; }
.gov-hero p { color: var(--text-sub); margin: 0 0 4px; font-size: 13px; }
.gov-hero small { color: var(--text-sub); font-size: 11px; }

/* ========== NST2 PORTAL ========== */
.nst2-portal {
    padding: 0 !important; max-width: none; overflow: hidden;
    background: linear-gradient(135deg, rgba(22,163,74,0.06), rgba(124,58,237,0.04));
}
.portal-header {
    display: flex; justify-content: space-between; align-items: center;
    gap: 24px; padding: 24px 28px; border-bottom: 1px solid var(--border); flex-wrap: wrap;
}
.ph-left { display: flex; gap: 16px; align-items: center; flex: 1; min-width: 260px; }
.portal-icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: linear-gradient(135deg, #16A34A, #7C3AED);
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; flex-shrink: 0;
    box-shadow: 0 8px 24px rgba(34,197,94,0.25);
}
.ph-left h2 {
    margin: 0 0 4px; font-size: 20px; font-weight: 700;
    background: linear-gradient(135deg, #22C55E, #84CC16, #7C3AED);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.ph-left p { margin: 0; font-size: 12px; color: var(--text-sub); }
.ph-right { display: flex; align-items: center; gap: 24px; }
.overall-ring { position: relative; width: 90px; height: 90px; }
.overall-ring svg { width: 100%; height: 100%; }
.ring-label { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.ring-label strong { font-size: 20px; font-weight: 800; color: #84CC16; line-height: 1; }
.ring-label small { font-size: 9px; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.8px; margin-top: 2px; }
.ph-stats { display: flex; gap: 20px; }
.ph-stats div { text-align: center; }
.ph-stats strong { display: block; font-size: 20px; font-weight: 800; color: var(--accent); }
.ph-stats small { font-size: 10px; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.5px; }

/* Pillar tabs */
.portal-tabs {
    display: flex; gap: 4px; padding: 12px 20px 0;
    border-bottom: 1px solid var(--border); overflow-x: auto; scrollbar-width: none;
}
.portal-tabs::-webkit-scrollbar { display: none; }
.portal-tab {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 18px; background: transparent; border: none;
    border-bottom: 3px solid transparent; color: var(--text-sub);
    font-family: inherit; font-size: 13px; font-weight: 500;
    cursor: pointer; white-space: nowrap; transition: 250ms;
}
.portal-tab:hover { color: var(--pillar-color); background: rgba(255,255,255,0.02); }
.portal-tab.active { color: var(--pillar-color); font-weight: 700; border-bottom-color: var(--pillar-color); }
.portal-tab span:first-child { font-size: 16px; }
.tab-count { font-size: 10px; padding: 2px 7px; background: rgba(255,255,255,0.08); border-radius: 20px; font-weight: 700; }
.portal-tab.active .tab-count { background: var(--pillar-color); color: white; }

/* Pillar panel */
.pillar-panel { display: none; padding: 24px; }
.pillar-panel.active { display: block; animation: fadeIn 0.35s ease-out; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}
.pillar-intro { margin-bottom: 20px; }
.pillar-badge {
    display: inline-block; padding: 6px 16px; border-radius: 20px;
    font-size: 12px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 8px;
}
.pillar-intro p { margin: 0; font-size: 13px; color: var(--text-sub); }

/* Strategy buttons row */
.strategy-buttons {
    display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px;
    padding-bottom: 20px; border-bottom: 1px solid var(--border);
}
.strategy-btn {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 18px; background: rgba(255,255,255,0.03);
    border: 1px solid var(--border); border-radius: 12px;
    color: var(--text-sub); font-family: inherit; font-size: 13px;
    font-weight: 500; cursor: pointer; transition: 250ms;
}
.strategy-btn:hover {
    background: rgba(255,255,255,0.06);
    border-color: var(--pillar-color);
    color: white;
    transform: translateY(-2px);
}
.strategy-btn.active {
    background: linear-gradient(135deg, var(--pillar-color)33, var(--pillar-color)11);
    border-color: var(--pillar-color);
    color: white;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.strat-icon { font-size: 18px; }
.strat-label { flex-grow: 1; text-align: left; }
.strat-count {
    font-size: 10px; padding: 2px 7px;
    background: rgba(255,255,255,0.08); border-radius: 20px; font-weight: 700;
}
.strategy-btn.active .strat-count { background: var(--pillar-color); color: white; }

/* Strategy panels */
.strategy-panel { display: none; }
.strategy-panel.active { display: block; animation: fadeIn 0.3s ease-out; }
.strategy-title {
    display: flex; align-items: center; gap: 10px;
    margin: 0 0 16px; font-size: 16px; font-weight: 700;
}
.indicator-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;
}

/* Indicator card */
.indicator-card {
    padding: 18px; background: rgba(255,255,255,0.03);
    border: 1px solid var(--border); border-radius: 14px;
    transition: 250ms; position: relative; overflow: hidden;
}
.indicator-card::before {
    content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
}
.indicator-card.status-excellent::before { background: #22C55E; box-shadow: 0 0 12px #22C55E; }
.indicator-card.status-good::before      { background: #84CC16; }
.indicator-card.status-warning::before   { background: #F59E0B; }
.indicator-card.status-critical::before  { background: #EF4444; }
.indicator-card:hover {
    transform: translateY(-3px);
    border-color: rgba(132,204,22,0.4);
    background: rgba(255,255,255,0.05);
}

.indicator-header { display: flex; gap: 12px; align-items: center; margin-bottom: 14px; }
.indicator-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.indicator-title { flex-grow: 1; min-width: 0; }
.indicator-title strong { display: block; font-size: 13px; line-height: 1.3; margin-bottom: 2px; }
.indicator-title small { font-size: 10px; color: var(--text-sub); font-family: 'Courier New', monospace; letter-spacing: 0.5px; }
.indicator-badge {
    width: 26px; height: 26px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;
}
.indicator-badge.excellent { background: rgba(34,197,94,0.2); }
.indicator-badge.good      { background: rgba(132,204,22,0.2); }
.indicator-badge.warning   { background: rgba(245,158,11,0.2); }
.indicator-badge.critical  { background: rgba(239,68,68,0.2); }

.indicator-values { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 8px; }
.iv-item { padding: 8px; background: rgba(0,0,0,0.2); border-radius: 10px; text-align: center; }
.iv-item.highlight { background: rgba(132,204,22,0.12); border: 1px solid rgba(132,204,22,0.3); }
.iv-item small { display: block; font-size: 9px; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.iv-item strong { font-size: 14px; font-weight: 700; color: white; }
.iv-item.highlight strong { color: var(--accent); }

.indicator-unit { font-size: 10px; color: var(--text-sub); text-align: right; margin-bottom: 12px; font-style: italic; opacity: 0.7; }
.indicator-progress { margin-top: 4px; }
.ip-track { height: 8px; background: rgba(255,255,255,0.08); border-radius: 4px; overflow: hidden; margin-bottom: 8px; }
.ip-fill { height: 100%; border-radius: 4px; transition: width 800ms cubic-bezier(0.4, 0, 0.2, 1); }
.ip-fill.status-excellent { background: linear-gradient(90deg, #16A34A, #22C55E); box-shadow: 0 0 12px rgba(34,197,94,0.5); }
.ip-fill.status-good      { background: linear-gradient(90deg, #84CC16, #A3E635); }
.ip-fill.status-warning   { background: linear-gradient(90deg, #F59E0B, #FBBF24); }
.ip-fill.status-critical  { background: linear-gradient(90deg, #EF4444, #F87171); box-shadow: 0 0 10px rgba(239,68,68,0.4); }
.ip-meta { display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--text-sub); }
.trend-badge { font-weight: 700; padding: 2px 8px; border-radius: 20px; font-size: 10px; }
.trend-badge.up   { background: rgba(34,197,94,0.15); color: #22C55E; }
.trend-badge.down { background: rgba(239,68,68,0.15); color: #EF4444; }
.trend-badge.flat { background: rgba(255,255,255,0.06); color: var(--text-sub); }

/* AI + Alerts */
.gov-insights-card { padding: 22px !important; }
.insight-item { display: flex; gap: 12px; padding: 14px 0; border-bottom: 1px solid var(--border); }
.insight-item:last-child { border-bottom: none; }
.insight-icon { font-size: 24px; flex-shrink: 0; }
.insight-item strong { display: block; font-size: 13px; margin-bottom: 4px; }
.insight-item p { margin: 0; color: var(--text-sub); font-size: 12px; line-height: 1.5; }
.insight-item small { color: var(--accent); font-size: 11px; font-weight: 600; }

.alert-row { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid var(--border); }
.alert-row:last-child { border-bottom: none; }
.alert-row strong { display: block; font-size: 13px; }
.alert-row small { color: var(--text-sub); font-size: 11px; }
.severity-pill { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; }
.severity-pill.low      { background: rgba(34,197,94,0.2);  color: #22C55E; }
.severity-pill.medium   { background: rgba(245,158,11,0.2); color: #F59E0B; }
.severity-pill.high     { background: rgba(239,68,68,0.2);  color: #EF4444; }
.severity-pill.critical { background: #EF4444; color: white; }

.empty-state { text-align: center; padding: 20px; color: var(--text-sub); font-size: 13px; }

@media (max-width: 900px) {
    .portal-header { flex-direction: column; align-items: flex-start; }
    .ph-right { width: 100%; justify-content: space-between; }
    .indicator-grid { grid-template-columns: 1fr; }
    .strategy-btn { flex: 1 1 100%; }
    .gov-hero { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 500px) {
    .portal-tab { padding: 10px 12px; font-size: 12px; }
    .portal-tab span:first-child { display: none; }
    .indicator-values { grid-template-columns: 1fr; }
}
.theory-cta {
    background: linear-gradient(135deg, #16A34A, #7C3AED) !important;
    color: white !important;
    font-weight: 700;
    padding: 12px 24px !important;
    border-radius: 12px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: 300ms;
}
.theory-cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(124,58,237,0.3);
}
</style>

<script>
function showPillar(pillarKey) {
    document.querySelectorAll('.portal-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.portal-tab[data-pillar="${pillarKey}"]`)?.classList.add('active');

    document.querySelectorAll('.pillar-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('pillar-' + pillarKey)?.classList.add('active');

    const firstBtn = document.querySelector(`#pillar-${pillarKey} .strategy-btn`);
    if (firstBtn) firstBtn.click();
}

function showStrategy(pillarKey, strategyKey) {
    const pillarEl = document.getElementById('pillar-' + pillarKey);
    if (!pillarEl) return;

    pillarEl.querySelectorAll('.strategy-btn').forEach(b => b.classList.remove('active'));
    pillarEl.querySelectorAll('.strategy-panel').forEach(p => p.classList.remove('active'));

    pillarEl.querySelector(`.strategy-btn[data-target="strat-${pillarKey}-${strategyKey}"]`)?.classList.add('active');
    pillarEl.querySelector(`#strat-${pillarKey}-${strategyKey}`)?.classList.add('active');
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.pillar-panel').forEach(p => {
        const firstBtn = p.querySelector('.strategy-btn');
        const firstId = firstBtn?.getAttribute('data-target');
        if (firstId) firstBtn.classList.add('active');

        p.querySelectorAll('.strategy-panel').forEach(sp => sp.classList.remove('active'));
        const firstPanel = p.querySelector('.strategy-panel');
        if (firstPanel) firstPanel.classList.add('active');
    });
});
</script>