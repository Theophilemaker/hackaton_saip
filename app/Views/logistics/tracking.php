<?php
/* ═══════════════════════════════════════════════════════════════
   LIVE TRACKING VIEW
   Layout fixed: single 260px offset, columns stretch,
   cards fill height. No conflict with sidebar.php's global rules.
   ═══════════════════════════════════════════════════════════════ */

$stats    = $stats    ?? ($data['stats']    ?? []);
$requests = $requests ?? ($data['requests'] ?? []);
$fleet    = $fleet    ?? ($data['fleet']    ?? []);
$district = $district ?? ($data['district'] ?? 'Kigali');

$districts = ['Bugesera','Burera','Gakenke','Gasabo','Gatsibo','Gicumbi','Gisagara','Huye','Kamonyi','Karongi','Kayonza','Kicukiro','Kirehe','Muhanga','Musanze','Ngoma','Ngororero','Nyabihu','Nyagatare','Nyamagabe','Nyamasheke','Nyanza','Nyarugenge','Nyaruguru','Rubavu','Ruhango','Rulindo','Rusizi','Rutsiro','Rwamagana'];

if (!function_exists('fmtRWF')) {
    function fmtRWF($n) { return number_format((float)$n) . ' RWF'; }
}
?>
<div class="dashboard-layout logistics-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="main-content">

            <!-- ═══ HERO ═══ -->
            <section class="glass-card log-hero">
                <div class="hero-left">
                    <span class="hero-tag"><span class="live-dot"></span> LIVE TRACKING</span>
                    <h1>📡 Shipment Tracking Center</h1>
                    <p>Monitor active shipments, manage transport requests, and track deliveries across all 30 districts of Rwanda in real time.</p>
                </div>
                <div class="hero-actions">
                    <button type="button" class="btn-hero-primary" onclick="openModal('requestModal')">➕ New Request</button>
                    <button type="button" class="btn-hero-secondary" onclick="openModal('fleetModal')">🚙 Add Vehicle</button>
                </div>
            </section>

            <!-- ═══ KPI STRIP ═══ -->
            <section class="kpi-strip">
                <div class="kpi-card shipments"><div class="kpi-icon">🚚</div><div><span class="kpi-label">Active Shipments</span><strong class="kpi-value"><?= number_format($stats['active_shipments'] ?? 0) ?></strong></div></div>
                <div class="kpi-card requests"><div class="kpi-icon">📋</div><div><span class="kpi-label">Open Requests</span><strong class="kpi-value"><?= number_format($stats['open_requests'] ?? 0) ?></strong></div></div>
                <div class="kpi-card fleet"><div class="kpi-icon">🚙</div><div><span class="kpi-label">Fleet Available</span><strong class="kpi-value"><?= number_format($stats['fleet_available'] ?? 0) ?><span class="kpi-sub">/<?= number_format($stats['fleet_total'] ?? 0) ?></span></strong></div></div>
                <div class="kpi-card ontime"><div class="kpi-icon">✅</div><div><span class="kpi-label">On-Time Rate</span><strong class="kpi-value"><?= number_format($stats['on_time_rate'] ?? 0) ?>%</strong></div></div>
                <div class="kpi-card revenue"><div class="kpi-icon">💰</div><div><span class="kpi-label">Revenue (Month)</span><strong class="kpi-value"><?= fmtRWF($stats['revenue_month'] ?? 0) ?></strong></div></div>
                <div class="kpi-card costs"><div class="kpi-icon">💸</div><div><span class="kpi-label">Costs (Month)</span><strong class="kpi-value"><?= fmtRWF($stats['cost_month'] ?? 0) ?></strong></div></div>
            </section>

            <!-- ═══ MAIN GRID ═══ -->
            <div class="log-grid">

                <!-- LEFT: Requests -->
                <div class="log-main">
                    <section class="glass-card">
                        <div class="card-head">
                            <div><h3>📋 Transport Requests</h3><p>Active and recent shipment requests</p></div>
                            <div class="head-actions">
                                <select class="filter-select" id="statusFilter">
                                    <option value="">All statuses</option>
                                    <option value="open">Open</option>
                                    <option value="accepted">Accepted</option>
                                    <option value="in_transit">In Transit</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <?php if (empty($requests)): ?>
                            <div class="empty-requests">
                                <div class="empty-icon">🚛</div>
                                <h3>No transport requests yet</h3>
                                <p>Create your first shipment request to get started.</p>
                                <button type="button" class="btn-primary" onclick="openModal('requestModal')">➕ Create First Request</button>
                            </div>
                        <?php else: ?>
                            <div class="requests-list" id="requestsList">
                                <?php foreach ($requests as $r):
                                    $statusClass = match($r['status'] ?? '') {
                                        'open'       => 'st-open',
                                        'accepted'   => 'st-accepted',
                                        'in_transit' => 'st-transit',
                                        'delivered'  => 'st-delivered',
                                        'cancelled'  => 'st-cancelled',
                                        default      => 'st-open',
                                    };
                                    $priorityClass = 'pr-' . ($r['priority'] ?? 'normal');
                                    $requestId = (int)($r['id'] ?? 0);
                                ?>
                                    <div class="request-card <?= $priorityClass ?>" data-status="<?= e($r['status'] ?? '') ?>" onclick="openRequestDetail(<?= $requestId ?>)">
                                        <div class="req-head">
                                            <div class="req-title-row">
                                                <span class="priority-dot <?= $priorityClass ?>"></span>
                                                <strong><?= e($r['title'] ?? 'Untitled') ?></strong>
                                                <span class="status-pill <?= $statusClass ?>"><?= strtoupper(str_replace('_', ' ', $r['status'] ?? '')) ?></span>
                                            </div>
                                            <span class="req-id">#<?= $requestId ?></span>
                                        </div>
                                        <div class="req-route">
                                            <span class="route-point"><span class="rp-icon">📍</span> <?= e($r['pickup_district'] ?? '—') ?></span>
                                            <span class="route-arrow">→</span>
                                            <span class="route-point"><span class="rp-icon">🎯</span> <?= e($r['dropoff_district'] ?? '—') ?></span>
                                        </div>
                                        <div class="req-meta">
                                            <span class="meta-chip">📦 <?= number_format((float)($r['weight_kg'] ?? 0)) ?> kg</span>
                                            <?php if (!empty($r['distance_km'])): ?><span class="meta-chip">🛣️ <?= number_format((float)$r['distance_km'], 1) ?> km</span><?php endif; ?>
                                            <?php if (!empty($r['estimated_hours'])): ?><span class="meta-chip">⏱️ ~<?= number_format((float)$r['estimated_hours'], 1) ?>h</span><?php endif; ?>
                                            <?php if (!empty($r['requires_refrigeration'])): ?><span class="meta-chip ref">❄️ Refrigerated</span><?php endif; ?>
                                            <?php if (!empty($r['offered_price_rwf'])): ?><span class="meta-chip price">💰 <?= fmtRWF($r['offered_price_rwf']) ?></span><?php endif; ?>
                                        </div>
                                        <div class="req-footer">
                                            <span class="req-date">📅 <?= !empty($r['pickup_date']) ? date('M d, Y', strtotime($r['pickup_date'])) : '—' ?> · <?= ucfirst($r['pickup_time_slot'] ?? 'morning') ?></span>
                                            <?php if (!empty($r['driver_name'])): ?><span class="req-driver">👨‍✈️ <?= e($r['driver_name']) ?><?= !empty($r['plate_number']) ? ' · ' . e($r['plate_number']) : '' ?></span><?php endif; ?>
                                        </div>
                                        <div class="req-actions">
                                            <?php if (($r['status'] ?? '') === 'open'): ?>
                                                <button type="button" class="act-btn primary" onclick="event.stopPropagation(); updateStatus(<?= $requestId ?>, 'accepted')">✅ Accept</button>
                                                <button type="button" class="act-btn danger"  onclick="event.stopPropagation(); updateStatus(<?= $requestId ?>, 'cancelled')">✕ Cancel</button>
                                            <?php elseif (($r['status'] ?? '') === 'accepted'): ?>
                                                <button type="button" class="act-btn primary" onclick="event.stopPropagation(); updateStatus(<?= $requestId ?>, 'in_transit')">🚚 Start Transit</button>
                                            <?php elseif (($r['status'] ?? '') === 'in_transit'): ?>
                                                <button type="button" class="act-btn success" onclick="event.stopPropagation(); updateStatus(<?= $requestId ?>, 'delivered')">✅ Mark Delivered</button>
                                            <?php endif; ?>
                                            <button type="button" class="act-btn ghost" onclick="event.stopPropagation(); openRequestDetail(<?= $requestId ?>)">🔍 Details</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>

                <!-- RIGHT: Calculator + Fleet + Quick Actions -->
                <div class="log-side">

                    <section class="glass-card calculator">
                        <div class="card-head"><div><h3>💰 Cost Calculator</h3><p>Instant shipping estimate</p></div></div>
                        <div class="calc-form">
                            <div class="calc-row"><label>From</label>
                                <select id="calcFrom" class="calc-select"><?php foreach ($districts as $d): ?><option value="<?= $d ?>" <?= $d === $district ? 'selected' : '' ?>><?= $d ?></option><?php endforeach; ?></select>
                            </div>
                            <div class="calc-row"><label>To</label>
                                <select id="calcTo" class="calc-select"><?php foreach ($districts as $d): ?><option value="<?= $d ?>" <?= $d === 'Kigali' ? 'selected' : '' ?>><?= $d ?></option><?php endforeach; ?></select>
                            </div>
                            <div class="calc-row"><label>Weight (kg)</label><input type="number" id="calcKg" value="500" min="1"></div>
                            <div class="calc-row checkbox-row"><label class="checkbox-label"><input type="checkbox" id="calcRefrig"><span>❄️ Requires refrigeration</span></label></div>
                            <button type="button" class="calc-btn" onclick="calculateEstimate()">📊 Calculate</button>
                            <div class="calc-result" id="calcResult" style="display:none;">
                                <div class="calc-total"><span>Estimated cost</span><strong id="calcTotal">—</strong></div>
                                <div class="calc-range" id="calcRange">—</div>
                                <div class="calc-breakdown" id="calcBreakdown"></div>
                            </div>
                        </div>
                    </section>

                    <section class="glass-card">
                        <div class="card-head">
                            <div><h3>🚙 My Fleet</h3><p><?= count($fleet) ?> vehicle<?= count($fleet) !== 1 ? 's' : '' ?></p></div>
                            <button type="button" class="head-btn" onclick="openModal('fleetModal')">➕ Add</button>
                        </div>
                        <?php if (empty($fleet)): ?>
                            <div class="fleet-empty"><div class="empty-icon">🚙</div><p>No vehicles yet. Add your first vehicle to start offering transport services.</p></div>
                        <?php else: ?>
                            <div class="fleet-list">
                                <?php foreach ($fleet as $v): ?>
                                    <div class="fleet-item">
                                        <div class="fleet-icon"><?= ($v['vehicle_type'] ?? '') === 'refrigerated' ? '❄️' : (($v['vehicle_type'] ?? '') === 'motorcycle' ? '🏍️' : '🚛') ?></div>
                                        <div class="fleet-info">
                                            <strong><?= e($v['plate_number'] ?? '—') ?></strong>
                                            <small><?= ucfirst($v['vehicle_type'] ?? 'truck') ?> · <?= number_format((float)($v['capacity_kg'] ?? 0)) ?> kg<?= !empty($v['driver_name']) ? ' · 👨‍✈️ ' . e($v['driver_name']) : '' ?></small>
                                        </div>
                                        <span class="fleet-status <?= !empty($v['is_available']) ? 'available' : 'busy' ?>"><?= !empty($v['is_available']) ? '● Available' : '● Busy' ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section class="glass-card">
                        <div class="card-head"><div><h3>⚡ Quick Actions</h3></div></div>
                        <div class="quick-actions">
                            <button type="button" class="quick-act" onclick="openModal('requestModal')"><span>📦</span><strong>New Shipment</strong><small>Post a transport request</small></button>
                            <button type="button" class="quick-act" onclick="openModal('fleetModal')"><span>🚙</span><strong>Register Vehicle</strong><small>Add to your fleet</small></button>
                            <a href="<?= BASE_URL ?>/logistics" class="quick-act"><span>📊</span><strong>Dashboard</strong><small>Overview & stats</small></a>
                            <a href="<?= BASE_URL ?>/logistics/analytics" class="quick-act"><span>📈</span><strong>Analytics</strong><small>Cost & routes</small></a>
                        </div>
                    </section>

                </div>
            </div>
        </main>
    </div>
</div>

<!-- ═══ MODAL: NEW REQUEST ═══ -->
<div class="modal-overlay" id="requestModal">
    <div class="modal large">
        <div class="modal-header">
            <div><h3>📦 New Transport Request</h3><p>Fill in the shipment details — cost is auto-calculated</p></div>
            <button type="button" class="modal-close" onclick="closeModal('requestModal')">✕</button>
        </div>
        <form id="requestForm" onsubmit="return false;">
            <div class="form-grid-2">
                <div class="fg"><label>Shipment Title *</label><input type="text" name="title" required placeholder="e.g., 2 tonnes of maize to Kigali" maxlength="200"></div>
                <div class="fg"><label>Cargo Type *</label>
                    <select name="cargo_type" required>
                        <option value="">— Select —</option>
                        <option value="agricultural_produce">🌾 Agricultural Produce</option>
                        <option value="livestock">🐄 Livestock</option>
                        <option value="fertilizer">🌱 Fertilizer / Seeds</option>
                        <option value="equipment">🔧 Equipment</option>
                        <option value="construction">🧱 Construction Materials</option>
                        <option value="general">📦 General Cargo</option>
                        <option value="perishable">❄️ Perishable Goods</option>
                    </select>
                </div>
            </div>
            <div class="fg"><label>Description</label><textarea name="cargo_description" rows="2" maxlength="500" placeholder="Additional details…"></textarea></div>
            <div class="form-grid-2">
                <div class="fg"><label>Weight (kg) *</label><input type="number" name="weight_kg" min="1" required placeholder="500"></div>
                <div class="fg"><label>Volume (m³)</label><input type="number" name="volume_m3" min="0" step="0.1" placeholder="0"></div>
            </div>
            <div class="fg checkbox-row"><label class="checkbox-label"><input type="checkbox" name="requires_refrigeration" value="1"><span>❄️ Requires refrigeration</span></label></div>

            <h4 class="form-section-title">📍 Pickup</h4>
            <div class="form-grid-2">
                <div class="fg"><label>District *</label>
                    <select name="pickup_district" required id="pickupDistrict">
                        <?php foreach ($districts as $d): ?><option value="<?= $d ?>" <?= $d === $district ? 'selected' : '' ?>><?= $d ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="fg"><label>Address</label><input type="text" name="pickup_address" placeholder="e.g., Musanze Market" maxlength="255"></div>
            </div>

            <h4 class="form-section-title">🎯 Dropoff</h4>
            <div class="form-grid-2">
                <div class="fg"><label>District *</label>
                    <select name="dropoff_district" required id="dropoffDistrict">
                        <?php foreach ($districts as $d): ?><option value="<?= $d ?>" <?= $d === 'Kigali' ? 'selected' : '' ?>><?= $d ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="fg"><label>Address</label><input type="text" name="dropoff_address" placeholder="e.g., Nyabugogo Depot" maxlength="255"></div>
            </div>

            <h4 class="form-section-title">📅 Schedule</h4>
            <div class="form-grid-3">
                <div class="fg"><label>Pickup Date *</label><input type="date" name="pickup_date" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>"></div>
                <div class="fg"><label>Time Slot</label>
                    <select name="pickup_time_slot">
                        <option value="morning">🌅 Morning</option>
                        <option value="afternoon">☀️ Afternoon</option>
                        <option value="evening">🌆 Evening</option>
                    </select>
                </div>
                <div class="fg"><label>Priority</label>
                    <select name="priority">
                        <option value="low">🟢 Low</option>
                        <option value="normal" selected>🟡 Normal</option>
                        <option value="high">🟠 High</option>
                        <option value="urgent">🔴 Urgent</option>
                    </select>
                </div>
            </div>
            <div class="fg"><label>Offered Price (RWF) — leave blank to auto-calculate</label><input type="number" name="offered_price_rwf" min="0" placeholder="Auto-calculated"></div>
            <div class="live-estimate" id="liveEstimate" style="display:none;">
                <div class="le-title">💡 Estimated cost</div>
                <div class="le-amount" id="leAmount">—</div>
                <div class="le-detail" id="leDetail"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('requestModal')">Cancel</button>
                <button type="button" class="btn-primary" id="btnSubmitRequest">📤 Submit Request</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ MODAL: ADD VEHICLE ═══ -->
<div class="modal-overlay" id="fleetModal">
    <div class="modal">
        <div class="modal-header">
            <div><h3>🚙 Register Vehicle</h3><p>Add a new vehicle to your fleet</p></div>
            <button type="button" class="modal-close" onclick="closeModal('fleetModal')">✕</button>
        </div>
        <form id="fleetForm" onsubmit="return false;">
            <div class="form-grid-2">
                <div class="fg"><label>Plate Number *</label><input type="text" name="plate_number" required placeholder="RAD 123 A" maxlength="50" style="text-transform:uppercase"></div>
                <div class="fg"><label>Vehicle Type *</label>
                    <select name="vehicle_type" required>
                        <option value="truck">🚛 Truck</option>
                        <option value="pickup">🛻 Pickup</option>
                        <option value="van">🚐 Van</option>
                        <option value="motorcycle">🏍️ Motorcycle</option>
                        <option value="refrigerated">❄️ Refrigerated Truck</option>
                        <option value="tanker">🛢️ Tanker</option>
                    </select>
                </div>
            </div>
            <div class="form-grid-2">
                <div class="fg"><label>Capacity (kg)</label><input type="number" name="capacity_kg" min="1" placeholder="1000"></div>
                <div class="fg"><label>Capacity (m³)</label><input type="number" name="capacity_m3" min="0" step="0.1" placeholder="10"></div>
            </div>
            <div class="form-grid-2">
                <div class="fg"><label>Fuel Type</label>
                    <select name="fuel_type">
                        <option value="diesel">Diesel</option>
                        <option value="petrol">Petrol</option>
                        <option value="electric">Electric</option>
                        <option value="hybrid">Hybrid</option>
                    </select>
                </div>
                <div class="fg"><label>Home District</label>
                    <select name="home_district">
                        <?php foreach ($districts as $d): ?><option value="<?= $d ?>" <?= $d === $district ? 'selected' : '' ?>><?= $d ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="fg checkbox-row"><label class="checkbox-label"><input type="checkbox" name="has_refrigeration" value="1"><span>❄️ Has refrigeration system</span></label></div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('fleetModal')">Cancel</button>
                <button type="button" class="btn-primary" id="btnSubmitFleet">💾 Register Vehicle</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    'use strict';
    var BASE = '<?= BASE_URL ?>';

    window.openModal = function (id) { document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; };
    window.closeModal = function (id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; };

    document.querySelectorAll('.modal-overlay').forEach(function (o) {
        o.addEventListener('click', function (e) { if (e.target === o) { o.classList.remove('open'); document.body.style.overflow = ''; } });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { document.querySelectorAll('.modal-overlay.open').forEach(function (m) { m.classList.remove('open'); }); document.body.style.overflow = ''; }
    });

    /* Submit request */
    var btnReq = document.getElementById('btnSubmitRequest');
    if (btnReq) btnReq.addEventListener('click', async function () {
        var fd = new FormData(document.getElementById('requestForm'));
        btnReq.disabled = true; btnReq.textContent = '⏳ Submitting…';
        try {
            var res = await fetch(BASE + '/logistics/create', { method: 'POST', body: fd, credentials: 'same-origin' });
            var data = await res.json();
            if (data.success) { toast('✅ Request created', 'success'); closeModal('requestModal'); setTimeout(function(){ location.reload(); }, 800); }
            else toast('⚠️ ' + (data.error || 'Failed'), 'error');
        } catch (err) { toast('⚠️ ' + err.message, 'error'); }
        finally { btnReq.disabled = false; btnReq.textContent = '📤 Submit Request'; }
    });

    /* Submit fleet */
    var btnFleet = document.getElementById('btnSubmitFleet');
    if (btnFleet) btnFleet.addEventListener('click', async function () {
        var fd = new FormData(document.getElementById('fleetForm'));
        btnFleet.disabled = true; btnFleet.textContent = '⏳ Adding…';
        try {
            var res = await fetch(BASE + '/logistics/addVehicle', { method: 'POST', body: fd, credentials: 'same-origin' });
            var data = await res.json();
            if (data.success) { toast('✅ Vehicle added', 'success'); closeModal('fleetModal'); setTimeout(function(){ location.reload(); }, 800); }
            else toast('⚠️ ' + (data.error || 'Failed'), 'error');
        } catch (err) { toast('⚠️ ' + err.message, 'error'); }
        finally { btnFleet.disabled = false; btnFleet.textContent = '💾 Register Vehicle'; }
    });

    /* Live estimate */
    async function updateLiveEstimate() {
        var from = document.getElementById('pickupDistrict').value;
        var to   = document.getElementById('dropoffDistrict').value;
        var kg   = parseFloat(document.querySelector('#requestForm [name="weight_kg"]').value) || 0;
        var refr = document.querySelector('#requestForm [name="requires_refrigeration"]').checked;
        if (!from || !to || kg <= 0) return;
        try {
            var url = BASE + '/logistics/estimate?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to) + '&kg=' + kg + (refr ? '&refrigeration=1' : '');
            var res = await fetch(url, { credentials: 'same-origin' });
            var data = await res.json();
            if (data.success) {
                document.getElementById('liveEstimate').style.display = 'block';
                document.getElementById('leAmount').textContent = numberFormat(data.estimate.estimated_total) + ' RWF';
                document.getElementById('leDetail').innerHTML = numberFormat(data.estimate.suggested_min) + ' – ' + numberFormat(data.estimate.suggested_max) + ' RWF range<br>Distance: ' + data.estimate.distance_km + ' km · ~' + data.estimate.hours + ' hours';
            }
        } catch (e) {}
    }
    ['pickupDistrict', 'dropoffDistrict'].forEach(function (id) { var el = document.getElementById(id); if (el) el.addEventListener('change', updateLiveEstimate); });
    var kgInput = document.querySelector('#requestForm [name="weight_kg"]');
    if (kgInput) kgInput.addEventListener('input', debounce(updateLiveEstimate, 600));
    var refrInput = document.querySelector('#requestForm [name="requires_refrigeration"]');
    if (refrInput) refrInput.addEventListener('change', updateLiveEstimate);

    /* Calculator */
    window.calculateEstimate = async function () {
        var from = document.getElementById('calcFrom').value;
        var to   = document.getElementById('calcTo').value;
        var kg   = parseFloat(document.getElementById('calcKg').value) || 0;
        var refr = document.getElementById('calcRefrig').checked;
        try {
            var url = BASE + '/logistics/estimate?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to) + '&kg=' + kg + (refr ? '&refrigeration=1' : '');
            var res = await fetch(url, { credentials: 'same-origin' });
            var data = await res.json();
            if (data.success) {
                var e = data.estimate;
                document.getElementById('calcTotal').textContent = numberFormat(e.estimated_total) + ' RWF';
                document.getElementById('calcRange').textContent = 'Range: ' + numberFormat(e.suggested_min) + ' – ' + numberFormat(e.suggested_max) + ' RWF';
                document.getElementById('calcBreakdown').innerHTML = e.breakdown.map(function (b) {
                    return '<div class="bd-row"><span>' + b.label + '</span><strong>' + numberFormat(b.amount) + ' RWF</strong></div>';
                }).join('') + '<div class="bd-row"><span>📍 Distance</span><strong>' + e.distance_km + ' km · ~' + e.hours + 'h</strong></div>';
                document.getElementById('calcResult').style.display = 'block';
            }
        } catch (e) { toast('⚠️ Calculator failed', 'error'); }
    };

    /* Update status */
    window.updateStatus = async function (id, status) {
        if (!confirm('Change status to ' + status.replace('_', ' ') + '?')) return;
        var fd = new URLSearchParams(); fd.append('status', status);
        try {
            var res = await fetch(BASE + '/logistics/updateStatus/' + id, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: fd.toString(), credentials: 'same-origin' });
            var data = await res.json();
            if (data.success) { toast('✅ Status updated', 'success'); setTimeout(function(){ location.reload(); }, 700); }
            else toast('⚠️ ' + (data.error || 'Failed'), 'error');
        } catch (err) { toast('⚠️ ' + err.message, 'error'); }
    };

    /* Request detail */
    window.openRequestDetail = function (id) { window.location.href = BASE + '/logistics/viewRequest/' + id; };

    /* Status filter */
    var filter = document.getElementById('statusFilter');
    if (filter) filter.addEventListener('change', function () {
        var val = this.value;
        document.querySelectorAll('#requestsList .request-card').forEach(function (card) {
            card.style.display = (!val || card.dataset.status === val) ? '' : 'none';
        });
    });

    /* Helpers */
    function numberFormat(n) { return Number(n || 0).toLocaleString('en-US', { maximumFractionDigits: 0 }); }
    function debounce(fn, ms) { var t; return function () { var a = arguments, c = this; clearTimeout(t); t = setTimeout(function () { fn.apply(c, a); }, ms); }; }
    function toast(msg, type) {
        var t = document.createElement('div');
        t.className = 'toast ' + (type || 'info');
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { t.classList.add('show'); }, 10);
        setTimeout(function () { t.classList.remove('show'); setTimeout(function () { t.remove(); }, 300); }, 2600);
    }

    setTimeout(function () { if (typeof calculateEstimate === 'function') calculateEstimate(); }, 400);
})();
</script>

<style>
/* ═══════════════════════════════════════════════════════════════
   LAYOUT — THE KEY FIX
   sidebar.php adds margin-left:260px to every .main-content.
   This page's .main-wrap already has margin-left:260px.
   Without overriding the inner .main-content, we'd get 520px total.
   ═══════════════════════════════════════════════════════════════ */

html, body { margin: 0 !important; padding: 0 !important; width: 100% !important; overflow-x: hidden !important; }

.logistics-page.dashboard-layout {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    min-height: 100vh !important;
    background: #0b1120 !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
}

.logistics-page .main-wrap {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    min-height: 100vh !important;
    box-sizing: border-box !important;
}

/* ★ THE FIX: reset sidebar.php's global margin inside our wrapped content */
.logistics-page .main-wrap > .main-content {
    margin-left: 0 !important;
    margin-right: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    padding: 20px 28px 60px !important;
    box-sizing: border-box !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 18px !important;
}

.logistics-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 22px !important;
    margin: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

/* HERO */
.log-hero {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 24px !important;
    flex-wrap: wrap !important;
    padding: 26px 30px !important;
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(16, 185, 129, 0.08)) !important;
    position: relative !important;
    overflow: hidden !important;
}
.log-hero::before {
    content: '' !important;
    position: absolute !important;
    top: -40% !important; right: -10% !important;
    width: 380px !important; height: 380px !important;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.12), transparent 70%) !important;
    pointer-events: none !important;
}
.hero-left { flex: 1 !important; min-width: 260px !important; position: relative !important; z-index: 1 !important; }
.hero-tag {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    padding: 4px 12px !important;
    background: rgba(56, 189, 248, 0.15) !important; color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.35) !important;
    border-radius: 999px !important;
    font-size: 10.5px !important; font-weight: 800 !important;
    letter-spacing: 1.2px !important; text-transform: uppercase !important;
    margin-bottom: 10px !important;
}
.live-dot {
    width: 6px !important; height: 6px !important;
    background: #38bdf8 !important; border-radius: 50% !important;
    animation: pulse 1.6s infinite !important;
    box-shadow: 0 0 8px #38bdf8 !important;
}
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }

.log-hero h1 { font-size: 26px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.log-hero p  { color: #94a3b8 !important; font-size: 14px !important; margin: 0 !important; line-height: 1.55 !important; max-width: 640px !important; }

.hero-actions { display: flex !important; gap: 10px !important; flex-wrap: wrap !important; position: relative !important; z-index: 1 !important; }

.btn-hero-primary, .btn-hero-secondary {
    display: inline-flex !important; align-items: center !important; gap: 8px !important;
    padding: 13px 24px !important; border: none !important; border-radius: 12px !important;
    font-size: 13.5px !important; font-weight: 700 !important; cursor: pointer !important;
    font-family: inherit !important; white-space: nowrap !important; transition: all 0.2s !important;
}
.btn-hero-primary { background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35) !important; }
.btn-hero-primary:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 22px rgba(16, 185, 129, 0.55) !important; }
.btn-hero-secondary { background: rgba(255, 255, 255, 0.06) !important; color: #f1f5f9 !important; border: 1px solid rgba(255, 255, 255, 0.14) !important; }
.btn-hero-secondary:hover { background: rgba(255, 255, 255, 0.12) !important; transform: translateY(-2px) !important; }

/* KPI STRIP */
.kpi-strip {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
    gap: 14px !important;
}
.kpi-card {
    display: flex !important; align-items: center !important; gap: 14px !important;
    padding: 18px 20px !important;
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 16px !important;
    position: relative !important; overflow: hidden !important;
    transition: all 0.25s !important; box-sizing: border-box !important;
}
.kpi-card::before { content: '' !important; position: absolute !important; top: 0 !important; left: 0 !important; width: 4px !important; height: 100% !important; }
.kpi-card.shipments::before { background: linear-gradient(180deg, #38bdf8, #0284c7) !important; }
.kpi-card.requests::before  { background: linear-gradient(180deg, #fbbf24, #d97706) !important; }
.kpi-card.fleet::before     { background: linear-gradient(180deg, #a78bfa, #7c3aed) !important; }
.kpi-card.ontime::before    { background: linear-gradient(180deg, #10b981, #059669) !important; }
.kpi-card.revenue::before   { background: linear-gradient(180deg, #22d3ee, #0891b2) !important; }
.kpi-card.costs::before     { background: linear-gradient(180deg, #f87171, #dc2626) !important; }
.kpi-card:hover { transform: translateY(-3px) !important; border-color: rgba(56, 189, 248, 0.3) !important; }
.kpi-icon { font-size: 28px !important; flex-shrink: 0 !important; }
.kpi-label { display: block !important; font-size: 11px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.6px !important; margin-bottom: 4px !important; }
.kpi-value { display: block !important; font-size: 22px !important; font-weight: 800 !important; color: #f1f5f9 !important; line-height: 1.1 !important; font-variant-numeric: tabular-nums !important; }
.kpi-sub { font-size: 13px !important; color: #64748b !important; font-weight: 600 !important; margin-left: 2px !important; }

/* GRID — THE SECOND KEY FIX */
.logistics-page .log-grid {
    display: grid !important;
    grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr) !important;
    gap: 18px !important;
    align-items: stretch !important;
    width: 100% !important;
    box-sizing: border-box !important;
}
.logistics-page .log-main,
.logistics-page .log-side {
    display: flex !important; flex-direction: column !important;
    gap: 18px !important; min-width: 0 !important; height: 100% !important;
}
.logistics-page .log-main > .glass-card {
    flex: 1 1 auto !important;
    display: flex !important; flex-direction: column !important;
}
.logistics-page .log-main > .glass-card > .empty-requests {
    flex: 1 !important;
    display: flex !important; flex-direction: column !important;
    align-items: center !important; justify-content: center !important;
    min-height: 260px !important;
}

/* CARD HEAD */
.card-head {
    display: flex !important; justify-content: space-between !important;
    align-items: flex-start !important; gap: 12px !important;
    margin-bottom: 16px !important; padding-bottom: 12px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    flex-wrap: wrap !important;
}
.card-head h3 { font-size: 16px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 0 4px 0 !important; }
.card-head p { font-size: 12.5px !important; color: #94a3b8 !important; margin: 0 !important; }
.head-actions { display: flex !important; gap: 8px !important; }

.filter-select {
    padding: 8px 32px 8px 14px !important;
    background: rgba(0, 0, 0, 0.3) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 10px !important; color: #cbd5e1 !important;
    font-size: 12.5px !important; font-weight: 600 !important;
    font-family: inherit !important; cursor: pointer !important;
    outline: none !important;
    -webkit-appearance: none !important; appearance: none !important;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 10px center !important;
    background-size: 14px !important;
}
.filter-select option { background: #1e293b !important; }

.head-btn {
    padding: 8px 16px !important;
    background: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    border-radius: 8px !important;
    font-size: 12px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
}
.head-btn:hover { background: rgba(56, 189, 248, 0.25) !important; }

/* REQUESTS */
.requests-list { display: flex !important; flex-direction: column !important; gap: 12px !important; }
.request-card {
    padding: 16px 18px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 14px !important; cursor: pointer !important;
    transition: all 0.2s !important; position: relative !important; overflow: hidden !important;
}
.request-card::before { content: '' !important; position: absolute !important; top: 0 !important; left: 0 !important; width: 3px !important; height: 100% !important; }
.request-card.pr-low::before    { background: #64748b !important; }
.request-card.pr-normal::before { background: #38bdf8 !important; }
.request-card.pr-high::before   { background: #fbbf24 !important; }
.request-card.pr-urgent::before { background: #ef4444 !important; }
.request-card:hover { background: rgba(56, 189, 248, 0.06) !important; border-color: rgba(56, 189, 248, 0.25) !important; transform: translateX(3px) !important; }

.req-head { display: flex !important; justify-content: space-between !important; align-items: center !important; gap: 12px !important; margin-bottom: 10px !important; flex-wrap: wrap !important; }
.req-title-row { display: flex !important; align-items: center !important; gap: 10px !important; flex-wrap: wrap !important; min-width: 0 !important; }
.priority-dot { width: 8px !important; height: 8px !important; border-radius: 50% !important; flex-shrink: 0 !important; }
.priority-dot.pr-low { background: #64748b !important; }
.priority-dot.pr-normal { background: #38bdf8 !important; }
.priority-dot.pr-high { background: #fbbf24 !important; }
.priority-dot.pr-urgent { background: #ef4444 !important; box-shadow: 0 0 8px rgba(239, 68, 68, 0.6) !important; animation: pulse 1.8s infinite !important; }
.req-title-row strong { font-size: 14.5px !important; color: #f1f5f9 !important; font-weight: 700 !important; }

.status-pill { display: inline-flex !important; padding: 3px 10px !important; border-radius: 999px !important; font-size: 10px !important; font-weight: 800 !important; letter-spacing: 0.5px !important; white-space: nowrap !important; }
.status-pill.st-open      { background: rgba(251, 191, 36, 0.15) !important; color: #fbbf24 !important; border: 1px solid rgba(251, 191, 36, 0.35) !important; }
.status-pill.st-accepted  { background: rgba(56, 189, 248, 0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(56, 189, 248, 0.35) !important; }
.status-pill.st-transit   { background: rgba(167, 139, 250, 0.15) !important; color: #a78bfa !important; border: 1px solid rgba(167, 139, 250, 0.35) !important; }
.status-pill.st-delivered { background: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(16, 185, 129, 0.35) !important; }
.status-pill.st-cancelled { background: rgba(148, 163, 184, 0.15) !important; color: #94a3b8 !important; border: 1px solid rgba(148, 163, 184, 0.35) !important; }

.req-id { font-family: 'Courier New', monospace !important; font-size: 11px !important; color: #64748b !important; font-weight: 700 !important; }

.req-route { display: flex !important; align-items: center !important; gap: 10px !important; margin-bottom: 10px !important; font-size: 13.5px !important; color: #cbd5e1 !important; flex-wrap: wrap !important; }
.route-point { display: inline-flex !important; align-items: center !important; gap: 6px !important; font-weight: 600 !important; }
.rp-icon { font-size: 13px !important; }
.route-arrow { color: #38bdf8 !important; font-size: 16px !important; font-weight: 800 !important; }

.req-meta { display: flex !important; gap: 8px !important; flex-wrap: wrap !important; margin-bottom: 10px !important; }
.meta-chip { display: inline-flex !important; align-items: center !important; gap: 4px !important; padding: 4px 10px !important; background: rgba(255, 255, 255, 0.04) !important; border: 1px solid rgba(255, 255, 255, 0.06) !important; border-radius: 8px !important; font-size: 11.5px !important; color: #cbd5e1 !important; font-weight: 600 !important; }
.meta-chip.ref   { background: rgba(56, 189, 248, 0.12) !important; color: #38bdf8 !important; border-color: rgba(56, 189, 248, 0.3) !important; }
.meta-chip.price { background: rgba(16, 185, 129, 0.12) !important; color: #34d399 !important; border-color: rgba(16, 185, 129, 0.3) !important; }

.req-footer { display: flex !important; justify-content: space-between !important; gap: 10px !important; font-size: 11.5px !important; color: #94a3b8 !important; flex-wrap: wrap !important; padding-top: 10px !important; border-top: 1px solid rgba(255, 255, 255, 0.05) !important; }
.req-actions { display: flex !important; gap: 6px !important; flex-wrap: wrap !important; margin-top: 12px !important; }

.act-btn { display: inline-flex !important; align-items: center !important; gap: 5px !important; padding: 7px 12px !important; border-radius: 8px !important; font-size: 11.5px !important; font-weight: 700 !important; cursor: pointer !important; font-family: inherit !important; border: none !important; transition: all 0.2s !important; }
.act-btn.primary { background: rgba(56, 189, 248, 0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(56, 189, 248, 0.3) !important; }
.act-btn.primary:hover { background: rgba(56, 189, 248, 0.28) !important; }
.act-btn.success { background: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(16, 185, 129, 0.3) !important; }
.act-btn.success:hover { background: rgba(16, 185, 129, 0.28) !important; }
.act-btn.danger  { background: rgba(239, 68, 68, 0.12) !important; color: #f87171 !important; border: 1px solid rgba(239, 68, 68, 0.3) !important; }
.act-btn.danger:hover { background: rgba(239, 68, 68, 0.25) !important; }
.act-btn.ghost   { background: rgba(255, 255, 255, 0.04) !important; color: #cbd5e1 !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; }
.act-btn.ghost:hover { background: rgba(255, 255, 255, 0.1) !important; }

/* EMPTY */
.empty-requests, .fleet-empty { text-align: center !important; padding: 40px 20px !important; }
.empty-icon { font-size: 52px !important; margin-bottom: 12px !important; opacity: 0.6 !important; }
.empty-requests h3 { font-size: 16px !important; color: #f1f5f9 !important; margin: 0 0 6px 0 !important; font-weight: 700 !important; }
.empty-requests p, .fleet-empty p { color: #94a3b8 !important; font-size: 13px !important; margin: 0 0 18px 0 !important; }

.btn-primary { display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; padding: 12px 26px !important; background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; border: none !important; border-radius: 10px !important; font-size: 13px !important; font-weight: 700 !important; cursor: pointer !important; font-family: inherit !important; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important; transition: all 0.2s !important; }
.btn-primary:hover:not(:disabled) { transform: translateY(-2px) !important; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5) !important; }
.btn-primary:disabled { opacity: 0.6 !important; cursor: not-allowed !important; transform: none !important; }

.btn-secondary { display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; padding: 12px 22px !important; background: rgba(255, 255, 255, 0.05) !important; color: #cbd5e1 !important; border: 1px solid rgba(255, 255, 255, 0.12) !important; border-radius: 10px !important; font-size: 13px !important; font-weight: 700 !important; cursor: pointer !important; font-family: inherit !important; }
.btn-secondary:hover { background: rgba(255, 255, 255, 0.1) !important; }

/* CALCULATOR */
.calc-form { display: flex !important; flex-direction: column !important; gap: 12px !important; }
.calc-row { display: flex !important; flex-direction: column !important; gap: 6px !important; }
.calc-row label { font-size: 11px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; }
.calc-select, .calc-form input[type="number"] {
    padding: 11px 14px !important;
    background: rgba(0, 0, 0, 0.3) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 10px !important; color: #f1f5f9 !important;
    font-size: 13.5px !important; font-family: inherit !important;
    outline: none !important; box-sizing: border-box !important;
    width: 100% !important;
}
.calc-select {
    cursor: pointer !important;
    -webkit-appearance: none !important; appearance: none !important;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 10px center !important;
    background-size: 14px !important;
    padding-right: 36px !important;
}
.calc-select option { background: #1e293b !important; }
.calc-row.checkbox-row { padding: 4px 0 !important; }
.checkbox-label { display: flex !important; align-items: center !important; gap: 8px !important; cursor: pointer !important; font-size: 12.5px !important; color: #cbd5e1 !important; font-weight: 600 !important; }
.checkbox-label input[type="checkbox"] { accent-color: #10b981 !important; width: 16px !important; height: 16px !important; cursor: pointer !important; }

.calc-btn { display: flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; padding: 12px 20px !important; background: linear-gradient(135deg, #38bdf8, #0284c7) !important; color: #fff !important; border: none !important; border-radius: 10px !important; font-size: 13px !important; font-weight: 700 !important; cursor: pointer !important; font-family: inherit !important; box-shadow: 0 4px 15px rgba(56, 189, 248, 0.3) !important; transition: all 0.2s !important; }
.calc-btn:hover { transform: translateY(-2px) !important; }

.calc-result { margin-top: 6px !important; padding: 16px !important; background: rgba(16, 185, 129, 0.08) !important; border: 1px solid rgba(16, 185, 129, 0.25) !important; border-radius: 12px !important; }
.calc-total { display: flex !important; justify-content: space-between !important; align-items: center !important; margin-bottom: 6px !important; }
.calc-total span { font-size: 12px !important; color: #94a3b8 !important; text-transform: uppercase !important; font-weight: 700 !important; letter-spacing: 0.5px !important; }
.calc-total strong { font-size: 20px !important; color: #34d399 !important; font-weight: 800 !important; }
.calc-range { font-size: 11.5px !important; color: #cbd5e1 !important; margin-bottom: 12px !important; }
.calc-breakdown { display: flex !important; flex-direction: column !important; gap: 6px !important; padding-top: 12px !important; border-top: 1px solid rgba(255, 255, 255, 0.08) !important; }
.bd-row { display: flex !important; justify-content: space-between !important; font-size: 12px !important; color: #cbd5e1 !important; }
.bd-row strong { color: #f1f5f9 !important; }

/* FLEET */
.fleet-list { display: flex !important; flex-direction: column !important; gap: 8px !important; }
.fleet-item { display: flex !important; align-items: center !important; gap: 12px !important; padding: 12px 14px !important; background: rgba(0, 0, 0, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.05) !important; border-radius: 12px !important; transition: all 0.2s !important; }
.fleet-item:hover { background: rgba(56, 189, 248, 0.06) !important; border-color: rgba(56, 189, 248, 0.2) !important; }
.fleet-icon { font-size: 26px !important; flex-shrink: 0 !important; }
.fleet-info { flex: 1 !important; min-width: 0 !important; }
.fleet-info strong { display: block !important; font-size: 13.5px !important; color: #f1f5f9 !important; font-weight: 700 !important; margin-bottom: 2px !important; font-family: 'Courier New', monospace !important; }
.fleet-info small { display: block !important; font-size: 11px !important; color: #94a3b8 !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; }
.fleet-status { font-size: 10.5px !important; font-weight: 800 !important; letter-spacing: 0.4px !important; white-space: nowrap !important; }
.fleet-status.available { color: #34d399 !important; }
.fleet-status.busy { color: #f87171 !important; }

/* QUICK ACTIONS */
.quick-actions { display: grid !important; grid-template-columns: repeat(2, 1fr) !important; gap: 10px !important; }
.quick-act { display: flex !important; flex-direction: column !important; align-items: flex-start !important; gap: 4px !important; padding: 14px !important; background: rgba(0, 0, 0, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.06) !important; border-radius: 12px !important; color: #cbd5e1 !important; text-decoration: none !important; cursor: pointer !important; font-family: inherit !important; text-align: left !important; transition: all 0.2s !important; }
.quick-act:hover { background: rgba(56, 189, 248, 0.1) !important; border-color: rgba(56, 189, 248, 0.3) !important; transform: translateY(-2px) !important; }
.quick-act span { font-size: 22px !important; line-height: 1 !important; }
.quick-act strong { font-size: 12.5px !important; color: #f1f5f9 !important; font-weight: 700 !important; }
.quick-act small { font-size: 10.5px !important; color: #94a3b8 !important; }

/* MODALS */
.modal-overlay { position: fixed !important; inset: 0 !important; background: rgba(0, 0, 0, 0.75) !important; backdrop-filter: blur(4px) !important; display: none !important; align-items: center !important; justify-content: center !important; z-index: 99999 !important; padding: 20px !important; box-sizing: border-box !important; }
.modal-overlay.open { display: flex !important; }
.modal { background: #1e293b !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; border-radius: 20px !important; padding: 26px !important; width: 100% !important; max-width: 600px !important; max-height: calc(100vh - 40px) !important; overflow-y: auto !important; box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6) !important; animation: modalIn 0.2s ease-out !important; box-sizing: border-box !important; }
.modal.large { max-width: 720px !important; }
@keyframes modalIn { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }
.modal-header { display: flex !important; justify-content: space-between !important; align-items: flex-start !important; gap: 12px !important; margin-bottom: 20px !important; padding-bottom: 14px !important; border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important; }
.modal-header h3 { font-size: 17px !important; color: #f1f5f9 !important; margin: 0 0 4px 0 !important; font-weight: 700 !important; }
.modal-header p { font-size: 12.5px !important; color: #94a3b8 !important; margin: 0 !important; }
.modal-close { width: 34px !important; height: 34px !important; background: rgba(255, 255, 255, 0.05) !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; border-radius: 8px !important; color: #cbd5e1 !important; font-size: 14px !important; cursor: pointer !important; font-family: inherit !important; flex-shrink: 0 !important; padding: 0 !important; display: flex !important; align-items: center !important; justify-content: center !important; }
.modal-close:hover { background: rgba(239, 68, 68, 0.2) !important; color: #f87171 !important; }

.form-grid-2 { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 14px !important; margin-bottom: 14px !important; }
.form-grid-3 { display: grid !important; grid-template-columns: repeat(3, 1fr) !important; gap: 12px !important; margin-bottom: 14px !important; }
.fg { display: flex !important; flex-direction: column !important; gap: 6px !important; }
.fg label { font-size: 11px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; }
.fg input, .fg select, .fg textarea {
    padding: 11px 14px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important; color: #f1f5f9 !important;
    font-size: 13.5px !important; font-family: inherit !important;
    outline: none !important; box-sizing: border-box !important;
    transition: border-color 0.2s, box-shadow 0.2s !important;
    width: 100% !important;
}
.fg textarea { resize: vertical !important; min-height: 60px !important; }
.fg input:focus, .fg select:focus, .fg textarea:focus { border-color: #38bdf8 !important; box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15) !important; }
.fg select { cursor: pointer !important; -webkit-appearance: none !important; appearance: none !important; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important; background-repeat: no-repeat !important; background-position: right 10px center !important; background-size: 14px !important; padding-right: 36px !important; }
.fg select option { background: #1e293b !important; }

.form-section-title { font-size: 12px !important; font-weight: 800 !important; color: #38bdf8 !important; text-transform: uppercase !important; letter-spacing: 1px !important; margin: 20px 0 12px 0 !important; padding-bottom: 8px !important; border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important; }
.checkbox-row { margin-bottom: 14px !important; }

.live-estimate { padding: 14px 16px !important; background: rgba(251, 191, 36, 0.08) !important; border-left: 4px solid #fbbf24 !important; border-radius: 10px !important; margin-bottom: 14px !important; animation: fadeIn 0.3s !important; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.le-title { font-size: 11px !important; color: #fbbf24 !important; font-weight: 700 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; margin-bottom: 4px !important; }
.le-amount { font-size: 20px !important; color: #f1f5f9 !important; font-weight: 800 !important; margin-bottom: 4px !important; }
.le-detail { font-size: 11.5px !important; color: #cbd5e1 !important; line-height: 1.5 !important; }

.modal-actions { display: flex !important; justify-content: flex-end !important; gap: 10px !important; padding-top: 16px !important; border-top: 1px solid rgba(255, 255, 255, 0.08) !important; margin-top: 8px !important; flex-wrap: wrap !important; }

/* TOAST */
.toast { position: fixed !important; bottom: 28px !important; right: 28px !important; padding: 14px 22px !important; border-radius: 12px !important; font-size: 13.5px !important; font-weight: 600 !important; color: #fff !important; transform: translateY(20px) !important; opacity: 0 !important; transition: all 0.3s ease !important; z-index: 999999 !important; box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5) !important; max-width: 400px !important; }
.toast.show { transform: translateY(0) !important; opacity: 1 !important; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.toast.error   { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }
.toast.info    { background: linear-gradient(135deg, #38bdf8, #0284c7) !important; }
.loading { text-align: center !important; padding: 40px !important; color: #94a3b8 !important; }

/* RESPONSIVE */
@media (max-width: 1100px) {
    .logistics-page .log-grid { grid-template-columns: 1fr !important; }
}
@media (max-width: 768px) {
    .logistics-page .main-wrap {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding-top: 64px !important;
    }
    .logistics-page .main-wrap > .main-content { padding: 14px !important; }
    .logistics-page .glass-card { padding: 18px !important; border-radius: 16px !important; }
    .log-hero { flex-direction: column !important; align-items: flex-start !important; padding: 20px !important; }
    .log-hero h1 { font-size: 20px !important; }
    .hero-actions { width: 100% !important; }
    .btn-hero-primary, .btn-hero-secondary { flex: 1 !important; justify-content: center !important; }
    .kpi-strip { grid-template-columns: repeat(2, 1fr) !important; }
    .kpi-value { font-size: 18px !important; }
    .form-grid-2, .form-grid-3 { grid-template-columns: 1fr !important; }
    .quick-actions { grid-template-columns: 1fr !important; }
    .modal { padding: 20px !important; }
    .modal-actions { flex-direction: column !important; }
    .modal-actions .btn-primary, .modal-actions .btn-secondary { width: 100% !important; }
    .toast { left: 16px !important; right: 16px !important; bottom: 16px !important; }
}
</style>