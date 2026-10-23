<?php
/**
 * Storage Management — Warehouse directory
 * Premium rebuild with KPIs, filters, gauge cards, expiring feed
 */
$warehouses = $data['warehouses'] ?? [];
$expiring   = $data['expiring']   ?? [];

/* ── Compute KPIs ── */
$totalCap   = 0;
$totalStored= 0;
$typeCount  = ['dry' => 0, 'cold' => 0, 'controlled' => 0, 'hermetic' => 0];
$criticalCount = 0;

foreach ($warehouses as $w) {
    $cap    = (float)($w['capacity_kg'] ?? 0);
    $stored = (float)($w['stored_kg']   ?? 0);
    $totalCap    += $cap;
    $totalStored += $stored;
    $t = $w['warehouse_type'] ?? 'dry';
    if (isset($typeCount[$t])) $typeCount[$t]++;
    if ($cap > 0 && ($stored / $cap) >= 0.9) $criticalCount++;
}
$utilization = $totalCap > 0 ? round(($totalStored / $totalCap) * 100) : 0;

function wtypeIcon(string $t): string {
    return match($t) {
        'cold'       => '❄️',
        'controlled' => '🎛️',
        'hermetic'   => '🫙',
        default      => '🏭',
    };
}
function utilizationClass(int $pct): string {
    if ($pct >= 90) return 'danger';
    if ($pct >= 70) return 'warn';
    if ($pct >= 30) return 'ok';
    return 'low';
}
?>
<div class="dashboard-layout storage-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="main-content">

            <!-- ═══════════ HERO ═══════════ -->
            <section class="glass-card stor-hero">
                <div class="hero-left">
                    <span class="hero-tag">
                        <span class="live-dot"></span> STORAGE NETWORK
                    </span>
                    <h1>🏭 Storage Management</h1>
                    <p>Monitor warehouses, track stock levels, prevent spoilage, and manage post-harvest storage across your network.</p>
                </div>
                <div class="hero-actions">
                    <a href="<?= BASE_URL ?>/storage/createWarehouse" class="btn-hero-primary">
                        <span>➕</span> New Warehouse
                    </a>
                    <button type="button" class="btn-hero-secondary" onclick="exportStorage()">
                        <span>📊</span> Export
                    </button>
                </div>
            </section>

            <!-- ═══════════ KPI STRIP ═══════════ -->
            <section class="kpi-strip">
                <div class="kpi-card kpi-capacity">
                    <div class="kpi-icon">🏗️</div>
                    <div>
                        <span class="kpi-label">Total Capacity</span>
                        <strong class="kpi-value"><?= number_format($totalCap / 1000, 1) ?><span class="kpi-unit">t</span></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-stored">
                    <div class="kpi-icon">📦</div>
                    <div>
                        <span class="kpi-label">Currently Stored</span>
                        <strong class="kpi-value"><?= number_format($totalStored / 1000, 1) ?><span class="kpi-unit">t</span></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-utilization">
                    <div class="kpi-icon">📊</div>
                    <div>
                        <span class="kpi-label">Utilization</span>
                        <strong class="kpi-value"><?= $utilization ?><span class="kpi-unit">%</span></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-warehouses">
                    <div class="kpi-icon">🏭</div>
                    <div>
                        <span class="kpi-label">Warehouses</span>
                        <strong class="kpi-value"><?= count($warehouses) ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-alerts">
                    <div class="kpi-icon">⚠️</div>
                    <div>
                        <span class="kpi-label">Critical Load</span>
                        <strong class="kpi-value"><?= $criticalCount ?></strong>
                    </div>
                </div>
            </section>

            <!-- ═══════════ FILTER BAR ═══════════ -->
            <section class="glass-card stor-filters">
                <div class="filter-search">
                    <span class="search-ico">🔍</span>
                    <input type="text" id="storSearch" placeholder="Search warehouses by name, district…" oninput="filterWarehouses()">
                </div>
                <div class="filter-chips" id="filterChips">
                    <button type="button" class="chip active" data-type="all">All</button>
                    <button type="button" class="chip" data-type="dry">🏭 Dry</button>
                    <button type="button" class="chip" data-type="cold">❄️ Cold</button>
                    <button type="button" class="chip" data-type="controlled">🎛️ Controlled</button>
                    <button type="button" class="chip" data-type="hermetic">🫙 Hermetic</button>
                </div>
                <select class="filter-sort" id="storSort" onchange="sortWarehouses()">
                    <option value="name">Sort: Name</option>
                    <option value="util-desc">Utilization (High → Low)</option>
                    <option value="util-asc">Utilization (Low → High)</option>
                    <option value="capacity">Capacity</option>
                </select>
            </section>

            <!-- ═══════════ WAREHOUSE GRID ═══════════ -->
            <?php if (empty($warehouses)): ?>
                <section class="glass-card empty-hero">
                    <div class="empty-emoji">🏭</div>
                    <h2>No warehouses yet</h2>
                    <p>Start by creating your first storage facility to track inventory and prevent spoilage.</p>
                    <a href="<?= BASE_URL ?>/storage/createWarehouse" class="btn-primary">➕ Create First Warehouse</a>
                </section>
            <?php else: ?>
                <section class="stor-grid" id="storGrid">
                    <?php foreach ($warehouses as $w):
                        $cap    = (float)($w['capacity_kg'] ?? 0);
                        $stored = (float)($w['stored_kg']   ?? 0);
                        $pct    = $cap > 0 ? min(100, round(($stored / $cap) * 100)) : 0;
                        $cls    = utilizationClass($pct);
                        $type   = $w['warehouse_type'] ?? 'dry';
                        $icon   = wtypeIcon($type);
                        $wId    = (int)$w['id'];
                        $name   = $w['name'] ?? 'Warehouse';
                        $dist   = $w['district_name'] ?? '—';
                        $alert  = $pct >= 90 ? 'Critical load' : ($pct >= 70 ? 'High load' : 'Healthy');
                    ?>
                        <a href="<?= BASE_URL ?>/storage/show/<?= $wId ?>"
                           class="glass-card stor-card"
                           data-type="<?= e($type) ?>"
                           data-name="<?= e(strtolower($name . ' ' . $dist)) ?>"
                           data-util="<?= $pct ?>"
                           data-cap="<?= $cap ?>">

                            <!-- Header -->
                            <div class="sc-head">
                                <div class="sc-icon"><?= $icon ?></div>
                                <div class="sc-title">
                                    <strong><?= e($name) ?></strong>
                                    <small>📍 <?= e($dist) ?> · <?= ucfirst($type) ?></small>
                                </div>
                                <span class="sc-status sc-<?= $cls ?>"><?= $pct ?>%</span>
                            </div>

                            <!-- Fill gauge -->
                            <div class="sc-gauge">
                                <div class="sc-gauge-track">
                                    <div class="sc-gauge-fill sc-fill-<?= $cls ?>" style="width:<?= $pct ?>%"></div>
                                </div>
                                <div class="sc-gauge-labels">
                                    <span><?= number_format($stored) ?> kg</span>
                                    <span><?= number_format($cap) ?> kg</span>
                                </div>
                            </div>

                            <!-- Metrics row -->
                            <div class="sc-metrics">
                                <div class="sc-metric">
                                    <small>Available</small>
                                    <strong><?= number_format(max(0, $cap - $stored)) ?> kg</strong>
                                </div>
                                <div class="sc-metric">
                                    <small>Status</small>
                                    <strong class="sc-status-text sc-text-<?= $cls ?>"><?= $alert ?></strong>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="sc-footer">
                                <span class="sc-view-link">View details →</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <!-- ═══════════ EXPIRING STOCK ═══════════ -->
            <?php if (!empty($expiring)): ?>
                <section class="glass-card expiring-card">
                    <div class="card-header">
                        <div>
                            <h3>⚠️ Expiring Soon</h3>
                            <p>Items expiring in the next 30 days — take action to avoid losses</p>
                        </div>
                        <span class="expiring-count"><?= count($expiring) ?> item<?= count($expiring) !== 1 ? 's' : '' ?></span>
                    </div>

                    <div class="expiring-list">
                        <?php foreach ($expiring as $b):
                            $expiryTs = strtotime($b['expiry_date'] ?? '');
                            $daysLeft = $expiryTs ? (int)ceil(($expiryTs - time()) / 86400) : null;
                            $urgency  = $daysLeft === null ? 'info' : ($daysLeft <= 3 ? 'critical' : ($daysLeft <= 7 ? 'high' : ($daysLeft <= 14 ? 'medium' : 'low')));
                            $urgencyLabel = match($urgency) {
                                'critical' => 'Critical',
                                'high'     => 'Urgent',
                                'medium'   => 'Soon',
                                default    => 'Watch',
                            };
                        ?>
                            <div class="exp-row exp-<?= $urgency ?>">
                                <div class="exp-left">
                                    <span class="exp-icon"><?= $b['icon'] ?? '📦' ?></span>
                                    <div>
                                        <strong><?= e($b['crop_name'] ?? '—') ?></strong>
                                        <small>Bin <?= e($b['bin_code'] ?? '—') ?> · <?= e($b['warehouse_name'] ?? '—') ?></small>
                                    </div>
                                </div>
                                <div class="exp-right">
                                    <span class="exp-badge exp-badge-<?= $urgency ?>"><?= $urgencyLabel ?></span>
                                    <?php if ($daysLeft !== null): ?>
                                        <span class="exp-days">
                                            <?php if ($daysLeft < 0): ?>
                                                Expired
                                            <?php elseif ($daysLeft === 0): ?>
                                                Today
                                            <?php elseif ($daysLeft === 1): ?>
                                                Tomorrow
                                            <?php else: ?>
                                                <?= $daysLeft ?> days
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        </main>
    </div>
</div>

<style>
/* ═══════════════════════════════════════════════════════════
   BASE
   ═══════════════════════════════════════════════════════════ */
html, body { margin: 0 !important; padding: 0 !important; overflow-x: hidden !important; }

.storage-page.dashboard-layout {
    display: block !important; width: 100% !important; max-width: 100% !important;
    min-height: 100vh !important; background: #0b1120 !important;
    margin: 0 !important; padding: 0 !important; box-sizing: border-box !important;
}
.storage-page .main-wrap {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    min-height: 100vh !important; box-sizing: border-box !important;
}
.storage-page .main-wrap > .main-content {
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    padding: 20px 28px 60px !important;
    box-sizing: border-box !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 18px !important;
}
.storage-page .glass-card {
    background: rgba(30, 41, 59, 0.7) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 22px !important;
    margin: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

/* ═══════════════════════════════════════════════════════════
   HERO
   ═══════════════════════════════════════════════════════════ */
.stor-hero {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 24px !important;
    flex-wrap: wrap !important;
    padding: 26px 30px !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08)) !important;
    position: relative !important;
    overflow: hidden !important;
}
.stor-hero::before {
    content: '' !important;
    position: absolute !important;
    top: -40% !important; right: -10% !important;
    width: 380px !important; height: 380px !important;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.15), transparent 70%) !important;
    pointer-events: none !important;
}
.hero-left { flex: 1 !important; min-width: 260px !important; position: relative !important; z-index: 1 !important; }
.hero-tag {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    padding: 4px 12px !important;
    background: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important;
    border: 1px solid rgba(16, 185, 129, 0.35) !important;
    border-radius: 999px !important;
    font-size: 10.5px !important; font-weight: 800 !important;
    letter-spacing: 1.2px !important; text-transform: uppercase !important;
    margin-bottom: 10px !important;
}
.live-dot {
    width: 6px !important; height: 6px !important;
    background: #34d399 !important; border-radius: 50% !important;
    animation: pulse 1.6s infinite !important;
    box-shadow: 0 0 8px #34d399 !important;
}
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }

.stor-hero h1 { font-size: 26px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.stor-hero p  { color: #94a3b8 !important; font-size: 14px !important; margin: 0 !important; line-height: 1.55 !important; max-width: 620px !important; }

.hero-actions { display: flex !important; gap: 10px !important; flex-wrap: wrap !important; position: relative !important; z-index: 1 !important; }
.btn-hero-primary, .btn-hero-secondary {
    display: inline-flex !important; align-items: center !important; gap: 8px !important;
    padding: 13px 24px !important; border: none !important; border-radius: 12px !important;
    font-size: 13.5px !important; font-weight: 700 !important; cursor: pointer !important;
    font-family: inherit !important; white-space: nowrap !important; transition: all 0.2s !important;
    text-decoration: none !important;
}
.btn-hero-primary {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35) !important;
}
.btn-hero-primary:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 22px rgba(16, 185, 129, 0.55) !important; }
.btn-hero-secondary {
    background: rgba(255, 255, 255, 0.06) !important;
    color: #f1f5f9 !important;
    border: 1px solid rgba(255, 255, 255, 0.14) !important;
}
.btn-hero-secondary:hover { background: rgba(255, 255, 255, 0.12) !important; transform: translateY(-2px) !important; }

/* ═══════════════════════════════════════════════════════════
   KPI STRIP
   ═══════════════════════════════════════════════════════════ */
.kpi-strip {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)) !important;
    gap: 14px !important;
}
.kpi-card {
    display: flex !important; align-items: center !important; gap: 14px !important;
    padding: 18px 20px !important;
    background: rgba(30, 41, 59, 0.7) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 16px !important;
    position: relative !important; overflow: hidden !important;
    transition: all 0.25s !important; box-sizing: border-box !important;
}
.kpi-card::before {
    content: '' !important; position: absolute !important;
    top: 0 !important; left: 0 !important;
    width: 4px !important; height: 100% !important;
}
.kpi-capacity::before    { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.kpi-stored::before      { background: linear-gradient(180deg, #10b981, #059669) !important; }
.kpi-utilization::before { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.kpi-warehouses::before  { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.kpi-alerts::before      { background: linear-gradient(180deg, #f87171, #dc2626) !important; }
.kpi-card:hover { transform: translateY(-3px) !important; border-color: rgba(16, 185, 129, 0.3) !important; }
.kpi-icon { font-size: 28px !important; flex-shrink: 0 !important; }
.kpi-label {
    display: block !important; font-size: 11px !important; font-weight: 700 !important;
    color: #94a3b8 !important; text-transform: uppercase !important;
    letter-spacing: 0.6px !important; margin-bottom: 4px !important;
}
.kpi-value {
    display: block !important; font-size: 22px !important; font-weight: 800 !important;
    color: #f1f5f9 !important; line-height: 1.1 !important;
    font-variant-numeric: tabular-nums !important;
}
.kpi-unit { font-size: 14px !important; color: #64748b !important; margin-left: 2px !important; font-weight: 700 !important; }

/* ═══════════════════════════════════════════════════════════
   FILTERS
   ═══════════════════════════════════════════════════════════ */
.stor-filters {
    display: flex !important; gap: 12px !important;
    align-items: center !important; flex-wrap: wrap !important;
    padding: 14px 18px !important;
}
.filter-search {
    flex: 1 !important; min-width: 220px !important;
    position: relative !important;
}
.search-ico {
    position: absolute !important; left: 14px !important; top: 50% !important;
    transform: translateY(-50%) !important; font-size: 14px !important;
    color: #64748b !important; pointer-events: none !important;
}
.filter-search input {
    width: 100% !important;
    padding: 11px 16px 11px 40px !important;
    background: rgba(0, 0, 0, 0.3) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 12px !important;
    color: #f1f5f9 !important; font-size: 13.5px !important;
    font-family: inherit !important; outline: none !important;
    box-sizing: border-box !important; transition: all 0.2s !important;
}
.filter-search input:focus {
    border-color: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
}

.filter-chips { display: flex !important; gap: 6px !important; flex-wrap: wrap !important; }
.chip {
    padding: 8px 14px !important;
    background: rgba(255, 255, 255, 0.03) !important;
    color: #94a3b8 !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 999px !important;
    font-size: 12px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    transition: all 0.2s !important;
}
.chip:hover { background: rgba(255, 255, 255, 0.06) !important; color: #cbd5e1 !important; }
.chip.active {
    background: rgba(16, 185, 129, 0.15) !important;
    color: #34d399 !important;
    border-color: rgba(16, 185, 129, 0.35) !important;
}

.filter-sort {
    padding: 10px 32px 10px 14px !important;
    background: rgba(0, 0, 0, 0.3) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 12px !important;
    color: #cbd5e1 !important;
    font-size: 12.5px !important; font-weight: 600 !important;
    font-family: inherit !important; cursor: pointer !important;
    outline: none !important;
    -webkit-appearance: none !important; appearance: none !important;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 10px center !important;
    background-size: 14px !important;
}
.filter-sort option { background: #1e293b !important; }

/* ═══════════════════════════════════════════════════════════
   WAREHOUSE GRID
   ═══════════════════════════════════════════════════════════ */
.stor-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr)) !important;
    gap: 16px !important;
}

.stor-card {
    display: flex !important; flex-direction: column !important;
    gap: 16px !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: all 0.25s !important;
    padding: 22px !important;
}
.stor-card:hover {
    transform: translateY(-4px) !important;
    border-color: rgba(16, 185, 129, 0.4) !important;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35) !important;
}

.sc-head {
    display: flex !important; align-items: center !important;
    gap: 12px !important;
}
.sc-icon {
    width: 52px !important; height: 52px !important;
    border-radius: 14px !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(56, 189, 248, 0.15)) !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    font-size: 26px !important;
    flex-shrink: 0 !important;
    border: 1px solid rgba(16, 185, 129, 0.25) !important;
}
.sc-title { flex: 1 !important; min-width: 0 !important; }
.sc-title strong {
    display: block !important; font-size: 15px !important;
    color: #f1f5f9 !important; font-weight: 800 !important;
    margin-bottom: 3px !important;
    white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important;
}
.sc-title small { font-size: 11.5px !important; color: #94a3b8 !important; }
.sc-status {
    padding: 5px 12px !important;
    border-radius: 999px !important;
    font-size: 11px !important; font-weight: 800 !important;
    white-space: nowrap !important;
}
.sc-ok     { background: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }
.sc-warn   { background: rgba(251, 191, 36, 0.15) !important; color: #fbbf24 !important; }
.sc-danger { background: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important; }
.sc-low    { background: rgba(56, 189, 248, 0.15) !important; color: #38bdf8 !important; }

.sc-gauge { display: flex !important; flex-direction: column !important; gap: 8px !important; }
.sc-gauge-track {
    height: 10px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border-radius: 999px !important;
    overflow: hidden !important;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3) !important;
}
.sc-gauge-fill {
    height: 100% !important;
    border-radius: 999px !important;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
}
.sc-gauge-fill::after {
    content: '' !important;
    position: absolute !important; inset: 0 !important;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent) !important;
    animation: shimmer 2s infinite !important;
}
@keyframes shimmer {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.sc-fill-ok     { background: linear-gradient(90deg, #10b981, #34d399) !important; }
.sc-fill-warn   { background: linear-gradient(90deg, #f59e0b, #fbbf24) !important; }
.sc-fill-danger { background: linear-gradient(90deg, #dc2626, #f87171) !important; }
.sc-fill-low    { background: linear-gradient(90deg, #0284c7, #38bdf8) !important; }

.sc-gauge-labels {
    display: flex !important; justify-content: space-between !important;
    font-size: 11.5px !important; color: #94a3b8 !important;
    font-weight: 700 !important;
    font-variant-numeric: tabular-nums !important;
}

.sc-metrics {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 10px !important;
    padding: 12px 0 !important;
    border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
}
.sc-metric small {
    display: block !important; font-size: 10.5px !important;
    color: #64748b !important; text-transform: uppercase !important;
    letter-spacing: 0.5px !important; font-weight: 700 !important;
    margin-bottom: 3px !important;
}
.sc-metric strong {
    font-size: 13px !important; color: #f1f5f9 !important;
    font-weight: 800 !important;
    font-variant-numeric: tabular-nums !important;
}
.sc-text-ok     { color: #34d399 !important; }
.sc-text-warn   { color: #fbbf24 !important; }
.sc-text-danger { color: #f87171 !important; }
.sc-text-low    { color: #38bdf8 !important; }

.sc-footer { display: flex !important; justify-content: flex-end !important; }
.sc-view-link {
    font-size: 12px !important;
    color: #34d399 !important;
    font-weight: 700 !important;
    transition: transform 0.2s !important;
}
.stor-card:hover .sc-view-link { transform: translateX(4px) !important; }

/* ═══════════════════════════════════════════════════════════
   EMPTY STATE
   ═══════════════════════════════════════════════════════════ */
.empty-hero {
    text-align: center !important;
    padding: 60px 30px !important;
}
.empty-emoji { font-size: 72px !important; margin-bottom: 16px !important; opacity: 0.5 !important; }
.empty-hero h2 {
    font-size: 20px !important; color: #f1f5f9 !important;
    margin: 0 0 8px 0 !important; font-weight: 800 !important;
}
.empty-hero p {
    color: #94a3b8 !important; font-size: 13.5px !important;
    margin: 0 0 22px 0 !important; max-width: 420px !important;
    margin-left: auto !important; margin-right: auto !important;
}
.btn-primary {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    gap: 8px !important;
    padding: 12px 26px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important; border: none !important;
    border-radius: 10px !important;
    font-size: 13px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
    transition: all 0.2s !important;
    text-decoration: none !important;
}
.btn-primary:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5) !important; }

/* ═══════════════════════════════════════════════════════════
   CARD HEADER (generic)
   ═══════════════════════════════════════════════════════════ */
.card-header {
    display: flex !important; justify-content: space-between !important;
    align-items: flex-start !important; gap: 12px !important;
    margin-bottom: 16px !important; padding-bottom: 12px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    flex-wrap: wrap !important;
}
.card-header h3 {
    font-size: 16px !important; font-weight: 800 !important;
    color: #f1f5f9 !important; margin: 0 0 4px 0 !important;
}
.card-header p { font-size: 12.5px !important; color: #94a3b8 !important; margin: 0 !important; }

/* ═══════════════════════════════════════════════════════════
   EXPIRING CARD
   ═══════════════════════════════════════════════════════════ */
.expiring-count {
    padding: 5px 12px !important;
    background: rgba(251, 191, 36, 0.15) !important;
    color: #fbbf24 !important;
    border-radius: 999px !important;
    font-size: 11px !important; font-weight: 800 !important;
}

.expiring-list { display: flex !important; flex-direction: column !important; gap: 8px !important; }

.exp-row {
    display: flex !important; align-items: center !important; justify-content: space-between !important;
    gap: 12px !important;
    padding: 12px 14px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
    border-radius: 12px !important;
    position: relative !important; overflow: hidden !important;
    transition: all 0.2s !important;
}
.exp-row::before {
    content: '' !important; position: absolute !important;
    top: 0 !important; left: 0 !important;
    width: 3px !important; height: 100% !important;
}
.exp-critical::before { background: #ef4444 !important; }
.exp-high::before     { background: #f59e0b !important; }
.exp-medium::before   { background: #fbbf24 !important; }
.exp-low::before      { background: #38bdf8 !important; }
.exp-row:hover { background: rgba(255, 255, 255, 0.04) !important; }

.exp-left { display: flex !important; align-items: center !important; gap: 12px !important; flex: 1 !important; min-width: 0 !important; }
.exp-icon { font-size: 26px !important; flex-shrink: 0 !important; }
.exp-left strong {
    display: block !important; font-size: 13.5px !important;
    color: #f1f5f9 !important; font-weight: 700 !important;
    margin-bottom: 2px !important;
    white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important;
}
.exp-left small { font-size: 11.5px !important; color: #94a3b8 !important; }

.exp-right { display: flex !important; align-items: center !important; gap: 10px !important; flex-shrink: 0 !important; }
.exp-badge {
    padding: 4px 10px !important;
    border-radius: 999px !important;
    font-size: 10px !important; font-weight: 800 !important;
    text-transform: uppercase !important; letter-spacing: 0.5px !important;
}
.exp-badge-critical { background: rgba(239, 68, 68, 0.15) !important; color: #f87171 !important; }
.exp-badge-high     { background: rgba(245, 158, 11, 0.15) !important; color: #fbbf24 !important; }
.exp-badge-medium   { background: rgba(251, 191, 36, 0.15) !important; color: #fbbf24 !important; }
.exp-badge-low      { background: rgba(56, 189, 248, 0.15) !important; color: #38bdf8 !important; }
.exp-days {
    font-size: 12px !important; color: #cbd5e1 !important;
    font-weight: 700 !important; white-space: nowrap !important;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .storage-page .main-wrap {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding-top: 64px !important;
    }
    .storage-page .main-wrap > .main-content { padding: 14px !important; }
    .storage-page .glass-card { padding: 18px !important; border-radius: 16px !important; }
    .stor-hero { flex-direction: column !important; align-items: flex-start !important; padding: 20px !important; }
    .stor-hero h1 { font-size: 20px !important; }
    .hero-actions { width: 100% !important; }
    .btn-hero-primary, .btn-hero-secondary { flex: 1 !important; justify-content: center !important; }
    .kpi-strip { grid-template-columns: repeat(2, 1fr) !important; }
    .kpi-value { font-size: 18px !important; }
    .stor-grid { grid-template-columns: 1fr !important; }
    .exp-row { flex-direction: column !important; align-items: flex-start !important; }
    .exp-right { align-self: flex-end !important; }
}
</style>

<script>
(function () {
    'use strict';

    /* Filter warehouses */
    var activeType = 'all';
    var activeSort = 'name';

    window.filterWarehouses = function () {
        applyFilters();
    };

    document.querySelectorAll('#filterChips .chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('#filterChips .chip').forEach(function (c) { c.classList.remove('active'); });
            this.classList.add('active');
            activeType = this.getAttribute('data-type');
            applyFilters();
        });
    });

    window.sortWarehouses = function () {
        activeSort = document.getElementById('storSort').value;
        applyFilters();
    };

    function applyFilters() {
        var q = (document.getElementById('storSearch').value || '').toLowerCase().trim();
        var grid = document.getElementById('storGrid');
        if (!grid) return;
        var cards = Array.from(grid.querySelectorAll('.stor-card'));

        cards.forEach(function (c) {
            var name = c.getAttribute('data-name') || '';
            var type = c.getAttribute('data-type') || '';
            var matchQ = !q || name.indexOf(q) !== -1;
            var matchT = activeType === 'all' || type === activeType;
            c.style.display = (matchQ && matchT) ? '' : 'none';
        });

        /* Sorting */
        cards.sort(function (a, b) {
            if (activeSort === 'name') {
                return (a.getAttribute('data-name') || '').localeCompare(b.getAttribute('data-name') || '');
            }
            var au = parseFloat(a.getAttribute('data-util')) || 0;
            var bu = parseFloat(b.getAttribute('data-util')) || 0;
            var ac = parseFloat(a.getAttribute('data-cap'))  || 0;
            var bc = parseFloat(b.getAttribute('data-cap'))  || 0;
            if (activeSort === 'util-desc') return bu - au;
            if (activeSort === 'util-asc')  return au - bu;
            if (activeSort === 'capacity')  return bc - ac;
            return 0;
        });
        cards.forEach(function (c) { grid.appendChild(c); });
    }

    window.exportStorage = function () {
        var rows = [['Name', 'District', 'Type', 'Stored (kg)', 'Capacity (kg)', 'Utilization (%)']];
        document.querySelectorAll('.stor-card').forEach(function (c) {
            var name = c.querySelector('.sc-title strong')?.textContent || '';
            var sub  = c.querySelector('.sc-title small')?.textContent || '';
            var cap  = c.getAttribute('data-cap');
            var util = c.getAttribute('data-util');
            var stored = c.querySelector('.sc-gauge-labels span')?.textContent || '';
            rows.push([name, sub.replace('📍 ', ''), c.getAttribute('data-type'), stored, cap, util]);
        });
        var csv = rows.map(function (r) {
            return r.map(function (v) { return '"' + String(v).replace(/"/g, '""') + '"'; }).join(',');
        }).join('\n');

        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href = url; a.download = 'storage-' + Date.now() + '.csv';
        a.click();
        URL.revokeObjectURL(url);
    };
})();
</script>