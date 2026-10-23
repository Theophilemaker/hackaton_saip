<?php
/**
 * Storage — Warehouse detail with bin grid
 * Premium rebuild with capacity ring, environmental gauges, expiry countdown
 */
$w    = $data['warehouse'] ?? [];
$bins = $data['bins']      ?? [];

/* ── Warehouse KPIs ── */
$totalCap  = (float)($w['capacity_kg'] ?? 0);
$totalStored = 0;
$avgTemp = $avgMoist = null;
$tempSum = $moistSum = 0;
$tempCount = $moistCount = 0;
$highRisk = 0;

foreach ($bins as $b) {
    $totalStored += (float)($b['quantity_kg'] ?? 0);
    if ($b['temperature_c'] !== null) { $tempSum += (float)$b['temperature_c']; $tempCount++; }
    if ($b['moisture_pct']  !== null) { $moistSum += (float)$b['moisture_pct'];  $moistCount++; }
    if (($b['pest_risk'] ?? '') === 'high') $highRisk++;
}
if ($tempCount)  $avgTemp  = round($tempSum / $tempCount, 1);
if ($moistCount) $avgMoist = round($moistSum / $moistCount, 1);
$utilPct = $totalCap > 0 ? min(100, round(($totalStored / $totalCap) * 100)) : 0;

function pestLabel(string $p): string {
    return match($p) { 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low', default => 'None' };
}
function pestClass(string $p): string {
    return match($p) { 'high' => 'danger', 'medium' => 'warn', 'low' => 'ok', default => 'ok' };
}
function tempClass(?float $t): string {
    if ($t === null) return 'neutral';
    if ($t > 35 || $t < 0) return 'danger';
    if ($t > 30 || $t < 5)  return 'warn';
    return 'ok';
}
function moistClass(?float $m): string {
    if ($m === null) return 'neutral';
    if ($m > 14 || $m < 8) return 'danger';
    if ($m > 13 || $m < 10) return 'warn';
    return 'ok';
}
?>
<div class="dashboard-layout storage-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="main-content">

            <!-- ═══════════ HERO ═══════════ -->
            <section class="glass-card wh-hero">
                <div class="wh-hero-left">
                    <a href="<?= BASE_URL ?>/storage" class="back-link">
                        <span>←</span> All warehouses
                    </a>
                    <div class="wh-title-row">
                        <div class="wh-icon">🏭</div>
                        <div>
                            <h1><?= e($w['name'] ?? 'Warehouse') ?></h1>
                            <p>
                                📍 <?= e($w['district_name'] ?? '—') ?>
                                <span class="dot-sep">·</span>
                                <span class="type-pill"><?= ucfirst($w['warehouse_type'] ?? 'dry') ?></span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Capacity ring -->
                <div class="wh-ring-wrap">
                    <svg class="wh-ring" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="10"/>
                        <circle cx="60" cy="60" r="52" fill="none"
                                stroke="url(#ringGradient)" stroke-width="10"
                                stroke-linecap="round"
                                stroke-dasharray="<?= 2 * 3.14159 * 52 ?>"
                                stroke-dashoffset="<?= 2 * 3.14159 * 52 * (1 - $utilPct/100) ?>"
                                transform="rotate(-90 60 60)"
                                style="transition: stroke-dashoffset 1s ease-out;"/>
                        <defs>
                            <linearGradient id="ringGradient" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#10b981"/>
                                <stop offset="100%" stop-color="#38bdf8"/>
                            </linearGradient>
                        </defs>
                        <text x="60" y="62" text-anchor="middle" fill="#f1f5f9" font-size="22" font-weight="800"><?= $utilPct ?>%</text>
                        <text x="60" y="78" text-anchor="middle" fill="#94a3b8" font-size="9" font-weight="700" letter-spacing="1">UTILIZED</text>
                    </svg>
                </div>

                <div class="wh-hero-actions">
                    <button type="button" class="btn-hero-primary" onclick="openAddBin()">
                        <span>➕</span> Add Bin
                    </button>
                </div>
            </section>

            <!-- ═══════════ KPI STRIP ═══════════ -->
            <section class="kpi-strip">
                <div class="kpi-card kpi-capacity">
                    <div class="kpi-icon">🏗️</div>
                    <div>
                        <span class="kpi-label">Capacity</span>
                        <strong class="kpi-value"><?= number_format($totalCap / 1000, 1) ?><span class="kpi-unit">t</span></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-stored">
                    <div class="kpi-icon">📦</div>
                    <div>
                        <span class="kpi-label">Stored</span>
                        <strong class="kpi-value"><?= number_format($totalStored / 1000, 1) ?><span class="kpi-unit">t</span></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-bins">
                    <div class="kpi-icon">🗄️</div>
                    <div>
                        <span class="kpi-label">Bins</span>
                        <strong class="kpi-value"><?= count($bins) ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-temp">
                    <div class="kpi-icon">🌡️</div>
                    <div>
                        <span class="kpi-label">Avg Temp</span>
                        <strong class="kpi-value"><?= $avgTemp !== null ? $avgTemp . '°C' : '—' ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-moist">
                    <div class="kpi-icon">💧</div>
                    <div>
                        <span class="kpi-label">Avg Moisture</span>
                        <strong class="kpi-value"><?= $avgMoist !== null ? $avgMoist . '%' : '—' ?></strong>
                    </div>
                </div>
                <div class="kpi-card kpi-risk">
                    <div class="kpi-icon">🐛</div>
                    <div>
                        <span class="kpi-label">Pest Risk</span>
                        <strong class="kpi-value"><?= $highRisk ?></strong>
                    </div>
                </div>
            </section>

            <!-- ═══════════ BINS ═══════════ -->
            <section class="glass-card bins-section">
                <div class="card-header">
                    <div>
                        <h3>🗄️ Storage Bins</h3>
                        <p>Individual storage units with live environmental monitoring</p>
                    </div>
                    <button type="button" class="head-btn" onclick="openAddBin()">
                        ➕ Add Bin
                    </button>
                </div>

                <?php if (empty($bins)): ?>
                    <div class="empty-state">
                        <div class="empty-emoji">🗄️</div>
                        <h3>No bins yet</h3>
                        <p>Create your first storage bin to start tracking inventory.</p>
                        <button type="button" class="btn-primary" onclick="openAddBin()">➕ Add First Bin</button>
                    </div>
                <?php else: ?>
                    <div class="bins-grid">
                        <?php foreach ($bins as $b):
                            $qty     = (float)($b['quantity_kg'] ?? 0);
                            $perBin  = count($bins) > 0 ? $totalCap / count($bins) : 1;
                            $fillPct = (float)($b['fill_percentage'] ?? 0);
                            if ($fillPct <= 0 && $perBin > 0) {
                                $fillPct = min(100, round(($qty / $perBin) * 100));
                            }
                            $fillPct = min(100, max(0, round($fillPct)));

                            $t  = $b['temperature_c'] !== null ? (float)$b['temperature_c'] : null;
                            $m  = $b['moisture_pct']  !== null ? (float)$b['moisture_pct']  : null;
                            $tc = tempClass($t);
                            $mc = moistClass($m);

                            $pest     = $b['pest_risk'] ?? 'low';
                            $pestCls  = pestClass($pest);

                            $status   = $b['status'] ?? 'active';
                            $statusCls = $status === 'active' ? 'ok' : 'warn';

                            /* Expiry countdown */
                            $expTs   = !empty($b['expiry_date']) ? strtotime($b['expiry_date']) : null;
                            $daysLeft= $expTs ? (int)ceil(($expTs - time()) / 86400) : null;
                            $expCls  = 'ok';
                            $expTxt  = '—';
                            if ($daysLeft !== null) {
                                if ($daysLeft < 0)     { $expCls = 'danger'; $expTxt = 'Expired'; }
                                elseif ($daysLeft <= 7){ $expCls = 'danger'; $expTxt = $daysLeft . 'd left'; }
                                elseif ($daysLeft <= 30){$expCls = 'warn';   $expTxt = $daysLeft . 'd left'; }
                                else                   { $expCls = 'ok';     $expTxt = $daysLeft . 'd left'; }
                            }
                        ?>
                            <div class="bin-card glass-inner">
                                <!-- HEAD -->
                                <div class="bin-head">
                                    <div class="bin-head-left">
                                        <span class="bin-icon"><?= $b['crop_icon'] ?? '📦' ?></span>
                                        <div>
                                            <strong>Bin <?= e($b['bin_code'] ?? '—') ?></strong>
                                            <small><?= e($b['crop_name'] ?? 'Empty') ?></small>
                                        </div>
                                    </div>
                                    <span class="status-pill st-<?= $statusCls ?>"><?= ucfirst($status) ?></span>
                                </div>

                                <!-- FILL -->
                                <div class="bin-fill-wrap">
                                    <div class="bin-fill-labels">
                                        <span>Fill level</span>
                                        <strong class="bf-<?= $fillPct >= 90 ? 'danger' : ($fillPct >= 70 ? 'warn' : 'ok') ?>"><?= $fillPct ?>%</strong>
                                    </div>
                                    <div class="bin-fill-track">
                                        <div class="bin-fill-bar bf-<?= $fillPct >= 90 ? 'danger' : ($fillPct >= 70 ? 'warn' : 'ok') ?>"
                                             style="width:<?= $fillPct ?>%"></div>
                                    </div>
                                    <div class="bin-qty"><?= number_format($qty) ?> kg</div>
                                </div>

                                <!-- ENV GAUGES -->
                                <div class="bin-env">
                                    <div class="env-metric env-<?= $tc ?>">
                                        <span class="env-icon">🌡️</span>
                                        <div>
                                            <small>Temp</small>
                                            <strong><?= $t !== null ? $t . '°C' : '—' ?></strong>
                                        </div>
                                    </div>
                                    <div class="env-metric env-<?= $mc ?>">
                                        <span class="env-icon">💧</span>
                                        <div>
                                            <small>Moisture</small>
                                            <strong><?= $m !== null ? $m . '%' : '—' ?></strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- FOOTER PILLS -->
                                <div class="bin-footer">
                                    <span class="footer-pill fp-<?= $pestCls ?>" title="Pest risk">
                                        🐛 <?= pestLabel($pest) ?>
                                    </span>
                                    <span class="footer-pill fp-<?= $expCls ?>" title="Expiry">
                                        ⏳ <?= $expTxt ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        </main>
    </div>
</div>

<!-- ═══════════ ADD BIN MODAL ═══════════ -->
<div id="addBinModal" class="stor-modal-overlay">
    <div class="stor-modal">
        <div class="stor-modal-head">
            <div>
                <h3>➕ Add Storage Bin</h3>
                <p>Register a new bin in <?= e($w['name'] ?? 'this warehouse') ?></p>
            </div>
            <button type="button" class="modal-close" onclick="closeAddBin()">✕</button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/storage/addBin/<?= (int)($w['id'] ?? 0) ?>">
            <div class="form-grid-2">
                <div class="fg">
                    <label>Bin Code *</label>
                    <input type="text" name="bin_code" required placeholder="e.g., A-01" maxlength="20">
                </div>
                <div class="fg">
                    <label>Crop *</label>
                    <select name="crop_id" required>
                        <option value="">— Select crop —</option>
                        <option value="1">🌽 Maize</option>
                        <option value="2">🫘 Beans</option>
                        <option value="3">🥔 Potatoes</option>
                        <option value="4">🌾 Rice</option>
                        <option value="5">☕ Coffee</option>
                        <option value="6">🍌 Bananas</option>
                        <option value="7">🌾 Wheat</option>
                        <option value="8">🥜 Groundnuts</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="fg">
                    <label>Quantity (kg) *</label>
                    <input type="number" step="0.01" name="quantity_kg" required placeholder="0">
                </div>
                <div class="fg">
                    <label>Expiry Date</label>
                    <input type="date" name="expiry_date" min="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <h4 class="form-section">🌡️ Environmental Conditions</h4>

            <div class="form-grid-2">
                <div class="fg">
                    <label>Moisture %</label>
                    <input type="number" step="0.1" name="moisture_pct" placeholder="12.0" min="0" max="100">
                    <small class="fg-hint">Target: 10–13% for dry grains</small>
                </div>
                <div class="fg">
                    <label>Temperature °C</label>
                    <input type="number" step="0.1" name="temperature_c" placeholder="22.0">
                    <small class="fg-hint">Target: 15–25°C ideal</small>
                </div>
            </div>

            <div class="stor-modal-actions">
                <button type="button" class="btn-secondary" onclick="closeAddBin()">Cancel</button>
                <button type="submit" class="btn-primary">💾 Save Bin</button>
            </div>
        </form>
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
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 22px !important;
    margin: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
}
.glass-inner {
    background: rgba(15, 23, 42, 0.55) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 16px !important;
}

/* ═══════════════════════════════════════════════════════════
   WAREHOUSE HERO
   ═══════════════════════════════════════════════════════════ */
.wh-hero {
    display: grid !important;
    grid-template-columns: 1fr auto auto !important;
    gap: 24px !important;
    align-items: center !important;
    padding: 24px 28px !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08)) !important;
    position: relative !important;
    overflow: hidden !important;
}
.wh-hero::before {
    content: '' !important;
    position: absolute !important;
    top: -40% !important; right: -10% !important;
    width: 380px !important; height: 380px !important;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.15), transparent 70%) !important;
    pointer-events: none !important;
}
.wh-hero-left { min-width: 0 !important; position: relative !important; z-index: 1 !important; }

.back-link {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    font-size: 12.5px !important; color: #94a3b8 !important;
    text-decoration: none !important;
    font-weight: 600 !important;
    margin-bottom: 14px !important;
    transition: color 0.2s !important;
}
.back-link:hover { color: #34d399 !important; }

.wh-title-row { display: flex !important; align-items: center !important; gap: 16px !important; }
.wh-icon {
    width: 60px !important; height: 60px !important;
    border-radius: 16px !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(56, 189, 248, 0.15)) !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    font-size: 32px !important;
    flex-shrink: 0 !important;
    border: 1px solid rgba(16, 185, 129, 0.3) !important;
}
.wh-title-row h1 {
    font-size: 24px !important; font-weight: 800 !important;
    color: #f1f5f9 !important; margin: 0 0 6px 0 !important;
}
.wh-title-row p {
    color: #94a3b8 !important; font-size: 13px !important;
    margin: 0 !important; display: flex !important;
    align-items: center !important; gap: 8px !important;
    flex-wrap: wrap !important;
}
.dot-sep { color: #475569 !important; }
.type-pill {
    padding: 3px 10px !important;
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border-radius: 999px !important;
    font-size: 11px !important; font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}

.wh-ring-wrap { position: relative !important; z-index: 1 !important; }
.wh-ring { width: 120px !important; height: 120px !important; }

.wh-hero-actions { display: flex !important; gap: 10px !important; position: relative !important; z-index: 1 !important; }
.btn-hero-primary {
    display: inline-flex !important; align-items: center !important; gap: 8px !important;
    padding: 13px 24px !important; border: none !important; border-radius: 12px !important;
    font-size: 13.5px !important; font-weight: 700 !important; cursor: pointer !important;
    font-family: inherit !important; white-space: nowrap !important; transition: all 0.2s !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35) !important;
    text-decoration: none !important;
}
.btn-hero-primary:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 22px rgba(16, 185, 129, 0.55) !important; }

/* ═══════════════════════════════════════════════════════════
   KPI STRIP
   ═══════════════════════════════════════════════════════════ */
.kpi-strip {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)) !important;
    gap: 14px !important;
}
.kpi-card {
    display: flex !important; align-items: center !important; gap: 12px !important;
    padding: 16px 18px !important;
    background: rgba(30, 41, 59, 0.7) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 14px !important;
    position: relative !important; overflow: hidden !important;
    transition: all 0.25s !important; box-sizing: border-box !important;
}
.kpi-card::before {
    content: '' !important; position: absolute !important;
    top: 0 !important; left: 0 !important;
    width: 4px !important; height: 100% !important;
}
.kpi-capacity::before { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.kpi-stored::before   { background: linear-gradient(180deg, #10b981, #059669) !important; }
.kpi-bins::before     { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.kpi-temp::before     { background: linear-gradient(180deg, #f87171, #dc2626) !important; }
.kpi-moist::before    { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.kpi-risk::before     { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.kpi-card:hover { transform: translateY(-3px) !important; }
.kpi-icon { font-size: 24px !important; flex-shrink: 0 !important; }
.kpi-label {
    display: block !important; font-size: 10.5px !important; font-weight: 700 !important;
    color: #94a3b8 !important; text-transform: uppercase !important;
    letter-spacing: 0.6px !important; margin-bottom: 3px !important;
}
.kpi-value {
    display: block !important; font-size: 19px !important; font-weight: 800 !important;
    color: #f1f5f9 !important; line-height: 1.1 !important;
    font-variant-numeric: tabular-nums !important;
}
.kpi-unit { font-size: 12px !important; color: #64748b !important; margin-left: 2px !important; font-weight: 700 !important; }

/* ═══════════════════════════════════════════════════════════
   BINS SECTION
   ═══════════════════════════════════════════════════════════ */
.card-header {
    display: flex !important; justify-content: space-between !important;
    align-items: flex-start !important; gap: 12px !important;
    margin-bottom: 18px !important; padding-bottom: 12px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    flex-wrap: wrap !important;
}
.card-header h3 {
    font-size: 16px !important; font-weight: 800 !important;
    color: #f1f5f9 !important; margin: 0 0 4px 0 !important;
}
.card-header p { font-size: 12.5px !important; color: #94a3b8 !important; margin: 0 !important; }

.head-btn {
    padding: 10px 18px !important;
    background: rgba(16, 185, 129, 0.12) !important;
    color: #34d399 !important;
    border: 1px solid rgba(16, 185, 129, 0.35) !important;
    border-radius: 10px !important;
    font-size: 12.5px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    transition: all 0.2s !important;
}
.head-btn:hover { background: rgba(16, 185, 129, 0.25) !important; transform: translateY(-1px) !important; }

.bins-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
    gap: 14px !important;
}

/* BIN CARD */
.bin-card {
    padding: 18px !important;
    display: flex !important; flex-direction: column !important;
    gap: 14px !important;
    transition: all 0.25s !important;
    position: relative !important;
}
.bin-card:hover {
    transform: translateY(-3px) !important;
    border-color: rgba(16, 185, 129, 0.35) !important;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3) !important;
}
.bin-head { display: flex !important; align-items: center !important; justify-content: space-between !important; gap: 10px !important; }
.bin-head-left { display: flex !important; align-items: center !important; gap: 10px !important; min-width: 0 !important; }
.bin-icon {
    width: 40px !important; height: 40px !important;
    border-radius: 10px !important;
    background: rgba(16, 185, 129, 0.12) !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    font-size: 20px !important;
    flex-shrink: 0 !important;
}
.bin-head strong {
    display: block !important; font-size: 14px !important;
    color: #f1f5f9 !important; font-weight: 800 !important;
    margin-bottom: 2px !important;
}
.bin-head small { display: block !important; font-size: 11.5px !important; color: #94a3b8 !important; }

.status-pill {
    padding: 4px 10px !important;
    border-radius: 999px !important;
    font-size: 10px !important; font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    white-space: nowrap !important;
}
.st-ok   { background: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; }
.st-warn { background: rgba(251, 191, 36, 0.15) !important; color: #fbbf24 !important; }

/* FILL BAR */
.bin-fill-wrap { display: flex !important; flex-direction: column !important; gap: 8px !important; }
.bin-fill-labels {
    display: flex !important; justify-content: space-between !important;
    font-size: 11.5px !important; color: #94a3b8 !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}
.bin-fill-labels strong { font-size: 13px !important; }
.bf-ok     { color: #34d399 !important; }
.bf-warn   { color: #fbbf24 !important; }
.bf-danger { color: #f87171 !important; }

.bin-fill-track {
    height: 8px !important;
    background: rgba(0, 0, 0, 0.4) !important;
    border-radius: 999px !important;
    overflow: hidden !important;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3) !important;
}
.bin-fill-bar {
    height: 100% !important;
    border-radius: 999px !important;
    transition: width 0.6s ease-out !important;
}
.bin-fill-bar.bf-ok     { background: linear-gradient(90deg, #10b981, #34d399) !important; }
.bin-fill-bar.bf-warn   { background: linear-gradient(90deg, #f59e0b, #fbbf24) !important; }
.bin-fill-bar.bf-danger { background: linear-gradient(90deg, #dc2626, #f87171) !important; }

.bin-qty {
    font-size: 12px !important; color: #cbd5e1 !important;
    font-weight: 700 !important;
    font-variant-numeric: tabular-nums !important;
}

/* ENV GAUGES */
.bin-env {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 10px !important;
    padding: 12px 0 !important;
    border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
}
.env-metric {
    display: flex !important; align-items: center !important; gap: 8px !important;
    padding: 8px 10px !important;
    border-radius: 10px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border-left: 3px solid transparent !important;
}
.env-ok     { border-left-color: #10b981 !important; }
.env-warn   { border-left-color: #fbbf24 !important; }
.env-danger { border-left-color: #f87171 !important; background: rgba(239, 68, 68, 0.06) !important; }
.env-neutral { border-left-color: #64748b !important; }
.env-icon { font-size: 16px !important; flex-shrink: 0 !important; }
.env-metric small {
    display: block !important; font-size: 10px !important;
    color: #94a3b8 !important; text-transform: uppercase !important;
    letter-spacing: 0.5px !important; font-weight: 700 !important;
    margin-bottom: 2px !important;
}
.env-metric strong { font-size: 13px !important; color: #f1f5f9 !important; font-weight: 800 !important; }

/* FOOTER PILLS */
.bin-footer { display: flex !important; gap: 6px !important; flex-wrap: wrap !important; }
.footer-pill {
    display: inline-flex !important; align-items: center !important; gap: 4px !important;
    padding: 5px 10px !important;
    border-radius: 999px !important;
    font-size: 11px !important; font-weight: 700 !important;
    white-space: nowrap !important;
}
.fp-ok     { background: rgba(16, 185, 129, 0.12) !important; color: #34d399 !important; }
.fp-warn   { background: rgba(251, 191, 36, 0.12) !important; color: #fbbf24 !important; }
.fp-danger { background: rgba(239, 68, 68, 0.12) !important; color: #f87171 !important; }

/* EMPTY STATE */
.empty-state {
    text-align: center !important;
    padding: 60px 30px !important;
}
.empty-state .empty-emoji { font-size: 60px !important; margin-bottom: 14px !important; opacity: 0.5 !important; }
.empty-state h3 { font-size: 17px !important; color: #f1f5f9 !important; margin: 0 0 6px 0 !important; font-weight: 700 !important; }
.empty-state p { color: #94a3b8 !important; font-size: 13px !important; margin: 0 0 18px 0 !important; }

/* ═══════════════════════════════════════════════════════════
   MODAL
   ═══════════════════════════════════════════════════════════ */
.stor-modal-overlay {
    position: fixed !important; inset: 0 !important;
    background: rgba(0, 0, 0, 0.75) !important;
    backdrop-filter: blur(6px) !important;
    display: none !important;
    align-items: center !important; justify-content: center !important;
    z-index: 99999 !important;
    padding: 20px !important;
    box-sizing: border-box !important;
}
.stor-modal-overlay.open { display: flex !important; }
.stor-modal {
    background: #1e293b !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 20px !important;
    padding: 26px !important;
    width: 100% !important; max-width: 620px !important;
    max-height: calc(100vh - 40px) !important;
    overflow-y: auto !important;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6) !important;
    box-sizing: border-box !important;
    animation: modalIn 0.2s ease-out !important;
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(0.96) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.stor-modal-head {
    display: flex !important; justify-content: space-between !important;
    align-items: flex-start !important; gap: 12px !important;
    margin-bottom: 20px !important; padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.stor-modal-head h3 { font-size: 17px !important; color: #f1f5f9 !important; margin: 0 0 4px 0 !important; font-weight: 800 !important; }
.stor-modal-head p { font-size: 12.5px !important; color: #94a3b8 !important; margin: 0 !important; }
.modal-close {
    width: 34px !important; height: 34px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 10px !important;
    color: #cbd5e1 !important; font-size: 14px !important;
    cursor: pointer !important; font-family: inherit !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    padding: 0 !important;
}
.modal-close:hover { background: rgba(239, 68, 68, 0.2) !important; color: #f87171 !important; }

.form-grid-2 {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 14px !important; margin-bottom: 14px !important;
}
.fg { display: flex !important; flex-direction: column !important; gap: 6px !important; }
.fg label {
    font-size: 11px !important; font-weight: 700 !important;
    color: #94a3b8 !important; text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}
.fg input, .fg select {
    padding: 11px 14px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important; font-size: 13.5px !important;
    font-family: inherit !important; outline: none !important;
    box-sizing: border-box !important; width: 100% !important;
    transition: all 0.2s !important;
}
.fg input:focus, .fg select:focus {
    border-color: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
}
.fg select option { background: #1e293b !important; }
.fg-hint { font-size: 10.5px !important; color: #64748b !important; font-style: italic !important; }

.form-section {
    font-size: 12px !important; font-weight: 800 !important;
    color: #38bdf8 !important; text-transform: uppercase !important;
    letter-spacing: 1px !important;
    margin: 20px 0 12px 0 !important;
    padding-bottom: 8px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
}
.stor-modal-actions {
    display: flex !important; justify-content: flex-end !important;
    gap: 10px !important; padding-top: 16px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    margin-top: 8px !important;
    flex-wrap: wrap !important;
}
.btn-primary {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    gap: 8px !important; padding: 12px 26px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important; border: none !important; border-radius: 10px !important;
    font-size: 13px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
    transition: all 0.2s !important;
    text-decoration: none !important;
}
.btn-primary:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5) !important; }

.btn-secondary {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    gap: 8px !important; padding: 12px 22px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 13px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    transition: all 0.2s !important;
}
.btn-secondary:hover { background: rgba(255, 255, 255, 0.1) !important; }

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
    .wh-hero { grid-template-columns: 1fr !important; }
    .wh-ring-wrap { justify-self: center !important; }
    .wh-hero-actions { justify-self: stretch !important; }
    .btn-hero-primary { width: 100% !important; justify-content: center !important; }
    .kpi-strip { grid-template-columns: repeat(2, 1fr) !important; }
    .bins-grid { grid-template-columns: 1fr !important; }
    .form-grid-2 { grid-template-columns: 1fr !important; }
    .stor-modal-actions .btn-primary,
    .stor-modal-actions .btn-secondary { width: 100% !important; }
}
</style>

<script>
function openAddBin() {
    document.getElementById('addBinModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeAddBin() {
    document.getElementById('addBinModal').classList.remove('open');
    document.body.style.overflow = '';
}
document.getElementById('addBinModal').addEventListener('click', function (e) {
    if (e.target === this) closeAddBin();
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAddBin();
});
</script>