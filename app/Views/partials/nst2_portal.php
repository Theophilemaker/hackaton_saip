<?php
/**
 * NST2 & Vision 2050 Portal Partial
 * Expects: $data['nst2_grouped']
 */
$groups = $data['nst2_grouped'] ?? [];

// Counters across all pillars
$totalIndicators = 0;
$onTrackCount = 0;
foreach ($groups as $g) {
    $totalIndicators += count($g['items'] ?? []);
    foreach ($g['items'] as $i) {
        if (!empty($i['on_track'])) $onTrackCount++;
    }
}
$overallPct = $totalIndicators > 0 ? round(($onTrackCount / $totalIndicators) * 100) : 0;
?>

<section class="nst2-portal glass-card">
    <!-- Header -->
    <div class="portal-header">
        <div class="ph-left">
            <span class="portal-icon">🎯</span>
            <div>
                <h2>NST2 & Vision 2050 Performance Portal</h2>
                <p>Rwanda's National Strategy for Transformation II (2024–2029) and long-term Vision 2050</p>
            </div>
        </div>
        <div class="ph-right">
            <div class="overall-ring">
                <svg viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="40" fill="none"
                            stroke="rgba(255,255,255,0.08)" stroke-width="8"/>
                    <circle cx="50" cy="50" r="40" fill="none"
                            stroke="url(#nst2Grad)" stroke-width="8"
                            stroke-dasharray="<?= round(($overallPct * 2.51), 2) ?> 251"
                            stroke-dashoffset="0"
                            transform="rotate(-90 50 50)"
                            stroke-linecap="round"/>
                    <defs>
                        <linearGradient id="nst2Grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#22C55E"/>
                            <stop offset="100%" stop-color="#84CC16"/>
                        </linearGradient>
                    </defs>
                </svg>
                <div class="ring-label">
                    <strong><?= $overallPct ?>%</strong>
                    <small>on track</small>
                </div>
            </div>
            <div class="ph-stats">
                <div><strong><?= $onTrackCount ?></strong><small>On track</small></div>
                <div><strong><?= $totalIndicators ?></strong><small>Indicators</small></div>
            </div>
        </div>
    </div>

    <!-- Pillar Tabs -->
    <div class="portal-tabs">
        <?php $first = true; foreach ($groups as $key => $g):
            if (empty($g['items'])) continue; ?>
            <button class="portal-tab <?= $first ? 'active' : '' ?>"
                    data-target="nst2-<?= htmlspecialchars($key) ?>"
                    style="--pillar-color: <?= $g['color'] ?>"
                    onclick="switchNST2Tab('<?= htmlspecialchars($key) ?>')">
                <span><?= $g['icon'] ?></span>
                <?= htmlspecialchars($g['label']) ?>
                <span class="tab-count"><?= count($g['items']) ?></span>
            </button>
        <?php $first = false; endforeach; ?>
    </div>

    <!-- Pillar Content -->
    <?php $first = true; foreach ($groups as $key => $g):
        if (empty($g['items'])) continue; ?>
        <div class="nst2-pillar-content <?= $first ? 'active' : '' ?>"
             id="nst2-<?= htmlspecialchars($key) ?>">

            <div class="pillar-grid">
                <?php foreach ($g['items'] as $item):
                    $progress = (float)($item['progress_pct'] ?? 0);
                    $trend    = $item['trend'] ?? 'flat';
                    $improve  = (float)($item['improvement_pct'] ?? 0);
                    $onTrack  = !empty($item['on_track']);
                    $statusClass = $progress >= 75 ? 'excellent'
                                 : ($progress >= 50 ? 'good'
                                 : ($progress >= 25 ? 'warning' : 'critical'));
                ?>
                    <div class="indicator-card status-<?= $statusClass ?>">
                        <div class="indicator-header">
                            <div class="indicator-icon" style="background:<?= $g['color'] ?>20; color:<?= $g['color'] ?>">
                                <?= $g['icon'] ?>
                            </div>
                            <div class="indicator-title">
                                <strong><?= htmlspecialchars($item['name'] ?? '') ?></strong>
                                <small><?= htmlspecialchars($item['code'] ?? '') ?></small>
                            </div>
                            <div class="indicator-badge <?= $statusClass ?>">
                                <?= $onTrack ? '✅' : '⚠️' ?>
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
                                <div class="ip-fill status-<?= $statusClass ?>"
                                     style="width:<?= $progress ?>%"></div>
                            </div>
                            <div class="ip-meta">
                                <span><?= $progress ?>% achieved</span>
                                <span class="trend-badge <?= $trend ?>">
                                    <?php if ($trend === 'up'): ?>▲ <?= $improve ?>%
                                    <?php elseif ($trend === 'down'): ?>▼ <?= $improve ?>%
                                    <?php else: ?>— Flat
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php $first = false; endforeach; ?>
</section>

<style>
/* ========== NST2 PORTAL ========== */
.nst2-portal {
    padding: 0 !important;
    max-width: none;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(22,163,74,0.06), rgba(124,58,237,0.04));
}

.portal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    padding: 24px 28px;
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
}
.ph-left { display: flex; gap: 16px; align-items: center; flex: 1; min-width: 260px; }
.portal-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, #16A34A, #7C3AED);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    flex-shrink: 0;
    box-shadow: 0 8px 24px rgba(34,197,94,0.25);
}
.ph-left h2 {
    margin: 0 0 4px;
    font-size: 20px;
    font-weight: 700;
    background: linear-gradient(135deg, #22C55E, #84CC16, #7C3AED);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.ph-left p { margin: 0; font-size: 12px; color: var(--text-sub); }

.ph-right { display: flex; align-items: center; gap: 24px; }

.overall-ring {
    position: relative;
    width: 90px;
    height: 90px;
}
.overall-ring svg { width: 100%; height: 100%; }
.ring-label {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.ring-label strong {
    font-size: 20px;
    font-weight: 800;
    color: #84CC16;
    line-height: 1;
}
.ring-label small {
    font-size: 9px;
    color: var(--text-sub);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-top: 2px;
}

.ph-stats { display: flex; gap: 20px; }
.ph-stats div { text-align: center; }
.ph-stats strong {
    display: block;
    font-size: 20px;
    font-weight: 800;
    color: var(--accent);
}
.ph-stats small {
    font-size: 10px;
    color: var(--text-sub);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Tabs */
.portal-tabs {
    display: flex;
    gap: 4px;
    padding: 12px 20px 0;
    border-bottom: 1px solid var(--border);
    overflow-x: auto;
    scrollbar-width: none;
}
.portal-tabs::-webkit-scrollbar { display: none; }

.portal-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 18px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    color: var(--text-sub);
    font-family: inherit;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    white-space: nowrap;
    transition: 250ms;
    position: relative;
}
.portal-tab:hover {
    color: var(--pillar-color);
    background: rgba(255,255,255,0.02);
}
.portal-tab.active {
    color: var(--pillar-color);
    font-weight: 700;
    border-bottom-color: var(--pillar-color);
}
.portal-tab span:first-child { font-size: 16px; }
.tab-count {
    font-size: 10px;
    padding: 2px 7px;
    background: rgba(255,255,255,0.08);
    border-radius: 20px;
    font-weight: 700;
}
.portal-tab.active .tab-count {
    background: var(--pillar-color);
    color: white;
}

/* Pillar content */
.nst2-pillar-content {
    display: none;
    padding: 24px;
}
.nst2-pillar-content.active { display: block; animation: fadeIn 0.35s ease-out; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.pillar-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
}

/* Indicator card */
.indicator-card {
    padding: 18px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 14px;
    transition: 250ms;
    position: relative;
    overflow: hidden;
}
.indicator-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
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

.indicator-header {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 14px;
}
.indicator-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.indicator-title { flex-grow: 1; min-width: 0; }
.indicator-title strong {
    display: block;
    font-size: 13px;
    line-height: 1.3;
    margin-bottom: 2px;
}
.indicator-title small {
    font-size: 10px;
    color: var(--text-sub);
    font-family: 'Courier New', monospace;
    letter-spacing: 0.5px;
}
.indicator-badge {
    width: 26px; height: 26px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}
.indicator-badge.excellent { background: rgba(34,197,94,0.2); }
.indicator-badge.good      { background: rgba(132,204,22,0.2); }
.indicator-badge.warning   { background: rgba(245,158,11,0.2); }
.indicator-badge.critical  { background: rgba(239,68,68,0.2); }

.indicator-values {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 8px;
    margin-bottom: 8px;
}
.iv-item {
    padding: 8px;
    background: rgba(0,0,0,0.2);
    border-radius: 10px;
    text-align: center;
}
.iv-item.highlight {
    background: rgba(132,204,22,0.12);
    border: 1px solid rgba(132,204,22,0.3);
}
.iv-item small {
    display: block;
    font-size: 9px;
    color: var(--text-sub);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.iv-item strong {
    font-size: 14px;
    font-weight: 700;
    color: white;
}
.iv-item.highlight strong { color: var(--accent); }

.indicator-unit {
    font-size: 10px;
    color: var(--text-sub);
    text-align: right;
    margin-bottom: 12px;
    font-style: italic;
    opacity: 0.7;
}

.indicator-progress { margin-top: 4px; }
.ip-track {
    height: 8px;
    background: rgba(255,255,255,0.08);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}
.ip-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 800ms cubic-bezier(0.4, 0, 0.2, 1);
}
.ip-fill.status-excellent {
    background: linear-gradient(90deg, #16A34A, #22C55E);
    box-shadow: 0 0 12px rgba(34,197,94,0.5);
}
.ip-fill.status-good {
    background: linear-gradient(90deg, #84CC16, #A3E635);
}
.ip-fill.status-warning {
    background: linear-gradient(90deg, #F59E0B, #FBBF24);
}
.ip-fill.status-critical {
    background: linear-gradient(90deg, #EF4444, #F87171);
    box-shadow: 0 0 10px rgba(239,68,68,0.4);
}

.ip-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
    color: var(--text-sub);
}
.trend-badge {
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 10px;
}
.trend-badge.up {
    background: rgba(34,197,94,0.15);
    color: #22C55E;
}
.trend-badge.down {
    background: rgba(239,68,68,0.15);
    color: #EF4444;
}
.trend-badge.flat {
    background: rgba(255,255,255,0.06);
    color: var(--text-sub);
}

@media (max-width: 900px) {
    .portal-header { flex-direction: column; align-items: flex-start; }
    .ph-right { width: 100%; justify-content: space-between; }
    .pillar-grid { grid-template-columns: 1fr; }
    .indicator-values { grid-template-columns: 1fr 1fr 1fr; }
}
@media (max-width: 500px) {
    .portal-tab { padding: 10px 12px; font-size: 12px; }
    .portal-tab span:first-child { display: none; }
    .indicator-values { grid-template-columns: 1fr; }
}
</style>

<script>
function switchNST2Tab(key) {
    document.querySelectorAll('.nst2-pillar-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.portal-tab').forEach(el => el.classList.remove('active'));

    const content = document.getElementById('nst2-' + key);
    const tab = document.querySelector(`.portal-tab[data-target="nst2-${key}"]`);

    if (content) content.classList.add('active');
    if (tab) tab.classList.add('active');
}
</script>