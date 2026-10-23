<?php
/* ═══════════════════════════════════════════════════════════════════
   AI Model Registry — admin/models.php
   Fully defensive: no shared scope, no variable shadowing, no crashes.
   ═══════════════════════════════════════════════════════════════════ */

/* ── Resolve $models ── */
$models = [];
if (isset($models) && is_array($models)) {
    $models = $models;
} elseif (isset($data['models']) && is_array($data['models'])) {
    $models = $data['models'];
}

$csrf = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : bin2hex(random_bytes(16));

/* ═══════════════════════ SAFE HELPERS ═══════════════════════ */
if (!function_exists('ml_esc')) {
    function ml_esc($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('ml_num')) {
    function ml_num($v, $decimals = 0) {
        $n = is_numeric($v) ? (float)$v : 0.0;
        return number_format($n, $decimals);
    }
}
if (!function_exists('ml_acc_class')) {
    function ml_acc_class($acc) {
        $a = is_numeric($acc) ? (float)$acc : 0.0;
        if ($a >= 90) return 'excellent';
        if ($a >= 75) return 'good';
        if ($a >= 60) return 'fair';
        return 'poor';
    }
}
if (!function_exists('ml_icon')) {
    function ml_icon($type) {
        $t = strtolower((string)$type);
        $map = [
            'yield'      => '🌾',
            'disease'    => '🔬',
            'price'      => '💰',
            'weather'    => '🌤️',
            'pest'       => '🐛',
            'soil'       => '🪨',
            'irrigation' => '💧',
        ];
        return isset($map[$t]) ? $map[$t] : '🤖';
    }
}
if (!function_exists('ml_date')) {
    function ml_date($val, $format = 'M d, Y') {
        if (empty($val)) return '—';
        $ts = strtotime((string)$val);
        return $ts ? date($format, $ts) : '—';
    }
}

/* ═══════════════════════ ISOLATED STATS FUNCTION ═══════════════════════
   Computed inside its own scope. No variable from extract() or the
   outer template can shadow $total, $active, $types, $best, etc.
   Returned as an array — cannot be a Closure. */
if (!function_exists('ml_stats')) {
    function ml_stats($models) {
        $out = [
            'total'    => 0,
            'active'   => 0,
            'inactive' => 0,
            'types'    => [],
            'avg'      => 0.0,
            'best'     => null,
        ];

        if (!is_array($models)) return $out;

        $accSum  = 0.0;
        $bestAcc = -1.0;

        foreach ($models as $m) {
            if (!is_array($m)) continue;

            $out['total']++;

            $isActive = !empty($m['is_active']);
            if ($isActive) {
                $out['active']++;
            } else {
                $out['inactive']++;
            }

            $accRaw = isset($m['accuracy']) ? $m['accuracy'] : 0;
            $acc    = is_numeric($accRaw) ? (float)$accRaw : 0.0;
            $accSum += $acc;

            if ($acc > $bestAcc) {
                $bestAcc     = $acc;
                $out['best'] = $m;
            }

            $t = isset($m['model_type']) ? (string)$m['model_type'] : 'other';
            if (!isset($out['types'][$t])) $out['types'][$t] = 0;
            $out['types'][$t]++;
        }

        $out['avg'] = $out['total'] > 0 ? round($accSum / $out['total'], 1) : 0.0;
        return $out;
    }
}

/* Compute once — everything reads from $ST, never from a raw variable */
$ST = ml_stats($models);
?>
<div class="dashboard-layout admin-ml-page">
    <?php
    $sidebarPath = __DIR__ . '/../layouts/sidebar.php';
    if (is_file($sidebarPath)) require_once $sidebarPath;
    ?>
    <main class="main-content">
        <?php
        $topbarPath = __DIR__ . '/../layouts/topbar.php';
        if (is_file($topbarPath)) require_once $topbarPath;
        ?>

        <!-- ═══════════════════════ HERO ═══════════════════════ -->
        <section class="glass-card hero-card">
            <div class="hero-left">
                <span class="hero-tag">🧠 MACHINE LEARNING</span>
                <h1>AI Model Registry</h1>
                <p>Manage every model that powers SAIP — crop yield prediction, disease detection, price forecasting, and weather intelligence.</p>
            </div>
            <div class="hero-stats">
                <div class="hstat">
                    <span class="hstat-value"><?= ml_num($ST['total']) ?></span>
                    <span class="hstat-label">Total</span>
                </div>
                <div class="hstat">
                    <span class="hstat-value ok"><?= ml_num($ST['active']) ?></span>
                    <span class="hstat-label">Active</span>
                </div>
                <div class="hstat">
                    <span class="hstat-value"><?= ml_num($ST['avg'], 1) ?>%</span>
                    <span class="hstat-label">Avg Accuracy</span>
                </div>
                <div class="hstat">
                    <span class="hstat-value"><?= ml_num(count($ST['types'])) ?></span>
                    <span class="hstat-label">Categories</span>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════ TOOLBAR ═══════════════════════ -->
        <section class="glass-card toolbar-card">
            <div class="toolbar-row">
                <div class="search-wrap">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" id="mSearch" placeholder="Search models by name, type, or version…" autocomplete="off">
                    <button type="button" class="search-clear" id="mSearchClear" onclick="clearSearch()" hidden>✕</button>
                </div>

                <div class="view-toggle">
                    <button type="button" class="view-btn active" data-view="grid" onclick="setView('grid')" title="Grid view">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                            <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                            <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                            <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                        </svg>
                    </button>
                    <button type="button" class="view-btn" data-view="table" onclick="setView('table')" title="Table view">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                </div>

                <div class="toolbar-actions">
                    <button type="button" class="btn-primary" onclick="openAddModal()">➕ Register Model</button>
                    <button type="button" class="btn-secondary" onclick="exportModels()" title="Export as CSV">⬇️ Export</button>
                </div>
            </div>

            <div class="chip-row" id="chipRow">
                <button type="button" class="chip active" data-filter="__all__">
                    All <span class="chip-count"><?= ml_num($ST['total']) ?></span>
                </button>
                <button type="button" class="chip" data-filter="__active__">
                    ✅ Active <span class="chip-count"><?= ml_num($ST['active']) ?></span>
                </button>
                <button type="button" class="chip" data-filter="__inactive__">
                    📦 Archived <span class="chip-count"><?= ml_num($ST['inactive']) ?></span>
                </button>
                <?php foreach ($ST['types'] as $typeName => $count): ?>
                    <button type="button" class="chip" data-filter="type:<?= ml_esc($typeName) ?>">
                        <?= ml_esc(ucfirst($typeName)) ?> <span class="chip-count"><?= ml_num($count) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ═══════════════════════ GRID / TABLE ═══════════════════════ -->
        <?php if ($ST['total'] === 0): ?>
            <section class="glass-card">
                <div class="empty-state">
                    <div class="empty-emoji">🤖</div>
                    <h3>No Models Registered</h3>
                    <p>Register your first AI model to get started.</p>
                    <button type="button" class="btn-primary" onclick="openAddModal()">➕ Register Model</button>
                </div>
            </section>
        <?php else: ?>

            <!-- GRID VIEW -->
            <section class="model-grid" id="gridView">
                <?php foreach ($models as $m):
                    if (!is_array($m)) continue;

                    $accRaw   = isset($m['accuracy']) ? $m['accuracy'] : 0;
                    $acc      = is_numeric($accRaw) ? (float)$accRaw : 0.0;
                    $accClass = ml_acc_class($acc);
                    $type     = isset($m['model_type']) ? (string)$m['model_type'] : 'other';
                    $isActive = !empty($m['is_active']);
                    $icon     = ml_icon($type);

                    $modelJson = ml_esc(json_encode([
                        'id'          => isset($m['id']) ? (int)$m['id'] : 0,
                        'model_name'  => isset($m['model_name']) ? (string)$m['model_name'] : '',
                        'model_type'  => $type,
                        'version'     => isset($m['version']) ? (string)$m['version'] : '1.0',
                        'accuracy'    => $acc,
                        'description' => isset($m['description']) ? (string)$m['description'] : '',
                        'is_active'   => $isActive ? 1 : 0,
                    ]));
                ?>
                    <article class="glass-card model-card"
                             data-name="<?= ml_esc(strtolower(isset($m['model_name']) ? $m['model_name'] : '')) ?>"
                             data-type="<?= ml_esc(strtolower($type)) ?>"
                             data-status="<?= $isActive ? 'active' : 'inactive' ?>"
                             data-accuracy="<?= ml_esc($acc) ?>">

                        <div class="mc-header">
                            <div class="mc-icon <?= ml_esc($accClass) ?>"><?= $icon ?></div>
                            <div class="mc-header-info">
                                <h3><?= ml_esc(isset($m['model_name']) ? $m['model_name'] : 'Untitled') ?></h3>
                                <span class="mc-type"><?= ml_esc(ucfirst($type)) ?></span>
                            </div>
                            <div class="mc-status <?= $isActive ? 'active' : 'archived' ?>">
                                <span class="mc-status-dot"></span>
                                <?= $isActive ? 'Active' : 'Archived' ?>
                            </div>
                        </div>

                        <div class="mc-body">
                            <div class="mc-accuracy">
                                <div class="mc-acc-head">
                                    <span class="mc-acc-label">Accuracy</span>
                                    <span class="mc-acc-value <?= ml_esc($accClass) ?>"><?= ml_num($acc, 1) ?>%</span>
                                </div>
                                <div class="mc-progress">
                                    <div class="mc-progress-fill <?= ml_esc($accClass) ?>"
                                         style="width: <?= ml_esc(min(100, max(0, $acc))) ?>%"></div>
                                </div>
                            </div>

                            <?php if (!empty($m['description'])): ?>
                                <p class="mc-desc"><?= ml_esc(mb_strimwidth((string)$m['description'], 0, 120, '…')) ?></p>
                            <?php endif; ?>

                            <div class="mc-meta">
                                <div class="mc-meta-item">
                                    <span class="mc-meta-label">Version</span>
                                    <code class="mc-meta-value">v<?= ml_esc(isset($m['version']) ? $m['version'] : '1.0') ?></code>
                                </div>
                                <div class="mc-meta-item">
                                    <span class="mc-meta-label">Trained</span>
                                    <span class="mc-meta-value"><?= ml_esc(ml_date(isset($m['trained_at']) ? $m['trained_at'] : null)) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="mc-actions">
                            <button type="button" class="mc-btn edit"
                                    onclick='openEditModal(<?= $modelJson ?>)'>✏️ Edit</button>
                            <button type="button" class="mc-btn toggle"
                                    onclick="toggleModel(<?= (int)(isset($m['id']) ? $m['id'] : 0) ?>, this)">
                                <?= $isActive ? '⏸️ Archive' : '▶️ Activate' ?>
                            </button>
                            <button type="button" class="mc-btn delete"
                                    onclick="deleteModel(<?= (int)(isset($m['id']) ? $m['id'] : 0) ?>, '<?= ml_esc(addslashes(isset($m['model_name']) ? $m['model_name'] : 'this model')) ?>')">🗑️</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>

            <!-- TABLE VIEW -->
            <section class="glass-card table-card" id="tableView" style="display: none;">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 26%;">Model</th>
                                <th style="width: 14%;">Type</th>
                                <th style="width: 10%;">Version</th>
                                <th style="width: 20%;">Accuracy</th>
                                <th style="width: 14%;">Trained</th>
                                <th style="width: 8%;">Status</th>
                                <th style="width: 8%; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($models as $m):
                                if (!is_array($m)) continue;

                                $accRaw   = isset($m['accuracy']) ? $m['accuracy'] : 0;
                                $acc      = is_numeric($accRaw) ? (float)$accRaw : 0.0;
                                $accClass = ml_acc_class($acc);
                                $isActive = !empty($m['is_active']);
                                $type     = isset($m['model_type']) ? (string)$m['model_type'] : 'other';

                                $modelJson = ml_esc(json_encode([
                                    'id'          => isset($m['id']) ? (int)$m['id'] : 0,
                                    'model_name'  => isset($m['model_name']) ? (string)$m['model_name'] : '',
                                    'model_type'  => $type,
                                    'version'     => isset($m['version']) ? (string)$m['version'] : '1.0',
                                    'accuracy'    => $acc,
                                    'description' => isset($m['description']) ? (string)$m['description'] : '',
                                    'is_active'   => $isActive ? 1 : 0,
                                ]));
                            ?>
                                <tr class="model-row"
                                    data-name="<?= ml_esc(strtolower(isset($m['model_name']) ? $m['model_name'] : '')) ?>"
                                    data-type="<?= ml_esc(strtolower($type)) ?>"
                                    data-status="<?= $isActive ? 'active' : 'inactive' ?>">
                                    <td><strong><?= ml_esc(isset($m['model_name']) ? $m['model_name'] : 'Untitled') ?></strong></td>
                                    <td><span class="cat-badge"><?= ml_esc($type) ?></span></td>
                                    <td><code class="key-code">v<?= ml_esc(isset($m['version']) ? $m['version'] : '1.0') ?></code></td>
                                    <td>
                                        <div class="mini-progress">
                                            <div class="mini-progress-fill <?= ml_esc($accClass) ?>"
                                                 style="width: <?= ml_esc(min(100, max(0, $acc))) ?>%"></div>
                                        </div>
                                        <span class="mini-acc <?= ml_esc($accClass) ?>"><?= ml_num($acc, 1) ?>%</span>
                                    </td>
                                    <td class="muted"><?= ml_esc(ml_date(isset($m['trained_at']) ? $m['trained_at'] : null)) ?></td>
                                    <td>
                                        <span class="pill <?= $isActive ? 'active' : 'archived' ?>">
                                            <?= $isActive ? 'Active' : 'Archived' ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" class="icon-btn" title="Edit"
                                                onclick='openEditModal(<?= $modelJson ?>)'>✏️</button>
                                        <button type="button" class="icon-btn danger" title="Delete"
                                                onclick="deleteModel(<?= (int)(isset($m['id']) ? $m['id'] : 0) ?>, '<?= ml_esc(addslashes(isset($m['model_name']) ? $m['model_name'] : 'this model')) ?>')">🗑️</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <span id="visibleCount">Showing <?= ml_num($ST['total']) ?> of <?= ml_num($ST['total']) ?> models</span>
                    <span class="footer-hint">💡 Hover a row for actions</span>
                </div>
            </section>
        <?php endif; ?>

        <!-- ═══════════════════════ BEST MODEL ═══════════════════════ -->
        <?php if (!empty($ST['best']) && is_array($ST['best'])):
            $bm = $ST['best'];
        ?>
            <section class="glass-card info-card">
                <div class="info-head">
                    <h3>🏆 Best Performing Model</h3>
                    <p>Highest accuracy in the current registry</p>
                </div>
                <div class="best-model">
                    <div class="bm-icon">🏅</div>
                    <div class="bm-info">
                        <strong><?= ml_esc(isset($bm['model_name']) ? $bm['model_name'] : 'Untitled') ?></strong>
                        <small>
                            <?= ml_esc(ucfirst(isset($bm['model_type']) ? $bm['model_type'] : 'other')) ?>
                            · v<?= ml_esc(isset($bm['version']) ? $bm['version'] : '1.0') ?>
                        </small>
                    </div>
                    <div class="bm-accuracy">
                        <span class="bm-value"><?= ml_num(isset($bm['accuracy']) ? $bm['accuracy'] : 0, 1) ?>%</span>
                        <small>accuracy</small>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>
</div>

<!-- ═══════════════════════ ADD / EDIT MODAL ═══════════════════════ -->
<div class="modal-overlay" id="modelModal" hidden>
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">➕ Register New Model</h3>
            <button type="button" class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <form id="modelForm" onsubmit="event.preventDefault(); saveModel();">
            <input type="hidden" id="modelId" value="">

            <div class="form-row-2">
                <div class="form-group">
                    <label>Model Name *</label>
                    <input type="text" id="modelName" required maxlength="150" placeholder="e.g. Maize Yield RF">
                </div>
                <div class="form-group">
                    <label>Type *</label>
                    <select id="modelType" required>
                        <option value="">— Select —</option>
                        <option value="yield">🌾 Yield Prediction</option>
                        <option value="disease">🔬 Disease Detection</option>
                        <option value="pest">🐛 Pest Detection</option>
                        <option value="price">💰 Price Forecast</option>
                        <option value="weather">🌤️ Weather</option>
                        <option value="soil">🪨 Soil Analysis</option>
                        <option value="irrigation">💧 Irrigation</option>
                        <option value="other">🤖 Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label>Version</label>
                    <input type="text" id="modelVersion" value="1.0" maxlength="20" placeholder="1.0">
                </div>
                <div class="form-group">
                    <label>Accuracy (%)</label>
                    <input type="number" id="modelAccuracy" min="0" max="100" step="0.1" value="0" placeholder="0">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea id="modelDesc" rows="3" maxlength="500"
                          placeholder="Short description of what this model does…"></textarea>
            </div>

            <div class="form-group">
                <label class="switch-label">
                    <span>
                        <strong>Active</strong>
                        <small>Model is available for inference</small>
                    </span>
                    <input type="checkbox" id="modelActive" checked>
                    <span class="switch"></span>
                </label>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="saveBtn">💾 Save</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    'use strict';

    var BASE = '<?= BASE_URL ?>';
    var CSRF = <?= json_encode($csrf) ?>;
    var editing = false;

    /* ═══════════════════════ VIEW TOGGLE ═══════════════════════ */
    window.setView = function (view) {
        document.querySelectorAll('.view-btn').forEach(function (b) { b.classList.remove('active'); });
        var btn = document.querySelector('.view-btn[data-view="' + view + '"]');
        if (btn) btn.classList.add('active');

        var gridEl  = document.getElementById('gridView');
        var tableEl = document.getElementById('tableView');

        if (view === 'table') {
            if (gridEl)  gridEl.style.display  = 'none';
            if (tableEl) tableEl.style.display = '';
        } else {
            if (gridEl)  gridEl.style.display  = '';
            if (tableEl) tableEl.style.display = 'none';
        }
        try { localStorage.setItem('saip_models_view', view); } catch (e) {}
    };

    try {
        var saved = localStorage.getItem('saip_models_view');
        if (saved) setView(saved);
    } catch (e) {}

    /* ═══════════════════════ SEARCH + FILTER ═══════════════════════ */
    var searchEl    = document.getElementById('mSearch');
    var searchClear = document.getElementById('mSearchClear');
    var currentFilter = '__all__';

    if (searchEl) {
        searchEl.addEventListener('input', function () {
            searchClear.hidden = !this.value;
            applyFilters();
        });
    }

    window.clearSearch = function () {
        if (!searchEl) return;
        searchEl.value = '';
        if (searchClear) searchClear.hidden = true;
        applyFilters();
        searchEl.focus();
    };

    document.querySelectorAll('#chipRow .chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('#chipRow .chip').forEach(function (c) { c.classList.remove('active'); });
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            applyFilters();
        });
    });

    function matchesFilter(status, type, filter) {
        if (filter === '__all__') return true;
        if (filter === '__active__')   return status === 'active';
        if (filter === '__inactive__') return status === 'inactive';
        if (filter.indexOf('type:') === 0) return type === filter.substring(5);
        return true;
    }

    function applyFilters() {
        var q = (searchEl && searchEl.value ? searchEl.value : '').toLowerCase().trim();
        var visible = 0;

        document.querySelectorAll('.model-card').forEach(function (card) {
            var name   = (card.dataset.name || '');
            var type   = (card.dataset.type || '');
            var status = (card.dataset.status || '');
            var matchSearch = !q || name.indexOf(q) !== -1 || type.indexOf(q) !== -1;
            var matchFilter = matchesFilter(status, type, currentFilter);
            var show = matchSearch && matchFilter;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.querySelectorAll('.model-row').forEach(function (row) {
            var name   = (row.dataset.name || '');
            var type   = (row.dataset.type || '');
            var status = (row.dataset.status || '');
            var matchSearch = !q || name.indexOf(q) !== -1 || type.indexOf(q) !== -1;
            var matchFilter = matchesFilter(status, type, currentFilter);
            row.style.display = (matchSearch && matchFilter) ? '' : 'none';
        });

        var counter = document.getElementById('visibleCount');
        var total = document.querySelectorAll('.model-card').length;
        if (counter) counter.textContent = 'Showing ' + visible + ' of ' + total + ' models';
    }

    /* ═══════════════════════ MODAL ═══════════════════════ */
    window.openAddModal = function () {
        editing = false;
        document.getElementById('modalTitle').textContent = '➕ Register New Model';
        document.getElementById('modelForm').reset();
        document.getElementById('modelId').value = '';
        document.getElementById('modelVersion').value = '1.0';
        document.getElementById('modelAccuracy').value = '0';
        document.getElementById('modelActive').checked = true;
        document.getElementById('modelModal').hidden = false;
        document.body.style.overflow = 'hidden';
        setTimeout(function () {
            var el = document.getElementById('modelName');
            if (el) el.focus();
        }, 50);
    };

    window.openEditModal = function (model) {
        editing = true;
        document.getElementById('modalTitle').textContent = '✏️ Edit Model';
        document.getElementById('modelId').value       = model.id || '';
        document.getElementById('modelName').value     = model.model_name || '';
        document.getElementById('modelType').value     = model.model_type || '';
        document.getElementById('modelVersion').value  = model.version || '1.0';
        document.getElementById('modelAccuracy').value = (model.accuracy != null ? model.accuracy : 0);
        document.getElementById('modelDesc').value     = model.description || '';
        document.getElementById('modelActive').checked = (model.is_active == 1);
        document.getElementById('modelModal').hidden = false;
        document.body.style.overflow = 'hidden';
    };

    window.closeModal = function () {
        document.getElementById('modelModal').hidden = true;
        document.body.style.overflow = '';
    };

    /* ═══════════════════════ SAVE ═══════════════════════ */
    window.saveModel = async function () {
        var btn = document.getElementById('saveBtn');
        var orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ Saving…';

        var payload = new URLSearchParams();
        payload.append('model_name',  document.getElementById('modelName').value.trim());
        payload.append('model_type',  document.getElementById('modelType').value);
        payload.append('version',     document.getElementById('modelVersion').value.trim() || '1.0');
        payload.append('accuracy',    document.getElementById('modelAccuracy').value || '0');
        payload.append('description', document.getElementById('modelDesc').value.trim());
        payload.append('status',      document.getElementById('modelActive').checked ? 'active' : 'archived');
        payload.append('_csrf',       CSRF);

        var url = BASE + '/admin/' + (editing ? 'editModel' : 'addModel');
        if (editing) payload.append('id', document.getElementById('modelId').value);

        try {
            var res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload.toString(),
                credentials: 'same-origin'
            });
            var data = await res.json().catch(function () { return { success: false, error: 'Invalid server response' }; });

            if (data.success) {
                showToast(editing ? '✅ Model updated' : '✅ Model registered', 'success');
                closeModal();
                setTimeout(function () { location.reload(); }, 700);
            } else {
                showToast(data.error || 'Save failed', 'error');
                btn.disabled = false;
                btn.innerHTML = orig;
            }
        } catch (e) {
            showToast('Error: ' + e.message, 'error');
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    };

    /* ═══════════════════════ TOGGLE / DELETE ═══════════════════════ */
    window.toggleModel = async function (id, btn) {
        if (!id) return;
        if (!confirm('Change this model\'s active status?')) return;
        var orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳…';
        try {
            var body = new URLSearchParams();
            body.append('_csrf', CSRF);

            var res = await fetch(BASE + '/admin/toggleModel/' + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body.toString(),
                credentials: 'same-origin'
            });
            var data = await res.json().catch(function () { return { success: true }; });
            if (data && data.success) {
                showToast('✅ Status updated', 'success');
                setTimeout(function () { location.reload(); }, 600);
            } else {
                showToast((data && data.error) || 'Toggle failed', 'error');
                btn.disabled = false;
                btn.innerHTML = orig;
            }
        } catch (e) {
            showToast('Error: ' + e.message, 'error');
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    };

    window.deleteModel = async function (id, name) {
        if (!id) return;
        if (!confirm('Delete model "' + name + '"? This cannot be undone.')) return;
        try {
            var body = new URLSearchParams();
            body.append('_csrf', CSRF);

            var res = await fetch(BASE + '/admin/deleteModel/' + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body.toString(),
                credentials: 'same-origin'
            });
            var data = await res.json().catch(function () { return { success: true }; });
            if (data && data.success) {
                showToast('🗑️ Model deleted', 'success');
                setTimeout(function () { location.reload(); }, 600);
            } else {
                showToast((data && data.error) || 'Delete failed', 'error');
            }
        } catch (e) {
            showToast('Error: ' + e.message, 'error');
        }
    };

    /* ═══════════════════════ EXPORT ═══════════════════════ */
    window.exportModels = function () {
        var rows = [['name', 'type', 'version', 'accuracy', 'status']];
        document.querySelectorAll('.model-card').forEach(function (card) {
            if (card.style.display === 'none') return;
            var nameEl = card.querySelector('h3');
            var typeEl = card.querySelector('.mc-type');
            var verEl  = card.querySelector('.mc-meta-value');
            var accEl  = card.querySelector('.mc-acc-value');
            rows.push([
                nameEl ? nameEl.textContent.trim() : '',
                typeEl ? typeEl.textContent.trim() : '',
                verEl  ? verEl.textContent.trim()  : '',
                accEl  ? accEl.textContent.trim()  : '',
                card.dataset.status || ''
            ]);
        });

        var csv = rows.map(function (r) {
            return r.map(function (cell) {
                var s = String(cell == null ? '' : cell).replace(/"/g, '""');
                return /[",\n]/.test(s) ? '"' + s + '"' : s;
            }).join(',');
        }).join('\n');

        var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'saip_models_' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
        showToast('📥 Exported ' + (rows.length - 1) + ' models', 'success');
    };

    /* ═══════════════════════ HELPERS ═══════════════════════ */
    window.showToast = function (msg, type) {
        if (window.SAIP && window.SAIP.toast) { window.SAIP.toast(msg, type); return; }
        var t = document.createElement('div');
        t.className = 'toast ' + (type || 'info');
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { t.classList.add('show'); }, 10);
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { t.remove(); }, 300);
        }, 2600);
    };

    /* ═══════════════════════ MODAL DISMISS ═══════════════════════ */
    var modal = document.getElementById('modelModal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
    });
})();
</script>

<style>
/* ═══════════════════════════════════════════════════════════
   LAYOUT
   ═══════════════════════════════════════════════════════════ */
html, body { margin: 0 !important; padding: 0 !important; overflow-x: hidden !important; }

.admin-ml-page.dashboard-layout {
    display: flex !important;
    width: 100% !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
}
.admin-ml-page .main-content {
    flex: 1 !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    padding: 24px 32px 60px !important;
    box-sizing: border-box !important;
}
.admin-ml-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 18px !important;
    padding: 22px !important;
    margin-bottom: 18px !important;
    box-sizing: border-box !important;
}

/* Hero */
.hero-card {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 26px !important;
    flex-wrap: wrap !important;
    background: linear-gradient(135deg, rgba(167, 139, 250, 0.12), rgba(56, 189, 248, 0.06)) !important;
    padding: 26px 28px !important;
}
.hero-left { flex: 1 !important; min-width: 260px !important; }
.hero-tag {
    display: inline-flex;
    padding: 4px 12px;
    background: rgba(167, 139, 250, 0.18);
    color: #a78bfa;
    border: 1px solid rgba(167, 139, 250, 0.35);
    border-radius: 999px;
    font-size: 10.5px; font-weight: 800;
    letter-spacing: 1.2px; text-transform: uppercase;
    margin-bottom: 10px;
}
.hero-card h1 { font-size: 26px; font-weight: 800; color: #f1f5f9; margin: 0 0 6px; }
.hero-card p { color: #94a3b8; font-size: 13.5px; margin: 0; max-width: 620px; line-height: 1.55; }

.hero-stats { display: flex !important; gap: 12px !important; flex-wrap: wrap !important; }
.hstat {
    display: flex; flex-direction: column;
    align-items: center; gap: 4px;
    padding: 12px 18px;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 12px;
    min-width: 78px;
}
.hstat-value { font-size: 22px; font-weight: 800; color: #f1f5f9; line-height: 1; }
.hstat-value.ok { color: #34d399; }
.hstat-label {
    font-size: 10px; font-weight: 700;
    color: #94a3b8; text-transform: uppercase;
    letter-spacing: 0.8px;
}

/* Toolbar */
.toolbar-card { padding: 18px 20px !important; }
.toolbar-row {
    display: flex; gap: 12px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 14px;
}
.search-wrap { flex: 1; min-width: 240px; position: relative; }
.search-icon {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    color: #64748b; pointer-events: none;
}
.search-wrap input {
    width: 100%;
    padding: 11px 40px 11px 40px;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    color: #f1f5f9;
    font-size: 13.5px;
    font-family: inherit;
    outline: none;
    box-sizing: border-box;
    transition: all 0.2s;
}
.search-wrap input:focus { border-color: #a78bfa; box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.15); }
.search-clear {
    position: absolute; right: 8px; top: 50%;
    transform: translateY(-50%);
    width: 26px; height: 26px;
    background: rgba(255, 255, 255, 0.06);
    color: #94a3b8;
    border: none; border-radius: 50%;
    cursor: pointer; padding: 0; font-size: 12px;
    display: flex; align-items: center; justify-content: center;
}

.view-toggle {
    display: flex; gap: 2px;
    padding: 4px;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
}
.view-btn {
    width: 34px; height: 34px;
    display: flex; align-items: center; justify-content: center;
    background: transparent;
    border: none; border-radius: 7px;
    color: #94a3b8;
    cursor: pointer;
    padding: 0;
    transition: all 0.2s;
}
.view-btn:hover { background: rgba(255, 255, 255, 0.05); color: #cbd5e1; }
.view-btn.active { background: rgba(167, 139, 250, 0.2); color: #a78bfa; }

.toolbar-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.btn-primary {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 11px 20px;
    background: linear-gradient(135deg, #a78bfa, #7c3aed);
    color: #fff; border: none; border-radius: 10px;
    font-size: 13px; font-weight: 700;
    cursor: pointer; font-family: inherit;
    box-shadow: 0 4px 14px rgba(167, 139, 250, 0.3);
    transition: all 0.2s;
}
.btn-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(167, 139, 250, 0.5); }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-secondary {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 11px 18px;
    background: rgba(255, 255, 255, 0.05);
    color: #cbd5e1;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    font-size: 13px; font-weight: 700;
    cursor: pointer; font-family: inherit;
    transition: all 0.2s;
}
.btn-secondary:hover { background: rgba(255, 255, 255, 0.1); color: #f1f5f9; }

/* Chips */
.chip-row {
    display: flex; gap: 6px;
    flex-wrap: wrap;
    padding-top: 14px;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    overflow-x: auto;
    scrollbar-width: none;
}
.chip-row::-webkit-scrollbar { display: none; }
.chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 999px;
    color: #94a3b8;
    font-size: 11.5px; font-weight: 700;
    cursor: pointer; font-family: inherit;
    white-space: nowrap;
    transition: all 0.15s;
}
.chip:hover { background: rgba(255, 255, 255, 0.06); color: #cbd5e1; }
.chip.active {
    background: rgba(167, 139, 250, 0.15);
    border-color: rgba(167, 139, 250, 0.4);
    color: #a78bfa;
}
.chip-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 20px; height: 18px; padding: 0 6px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 999px;
    font-size: 10px; font-weight: 800;
}
.chip.active .chip-count { background: rgba(167, 139, 250, 0.25); }

/* ═══════════════════════════════════════════════════════════
   GRID CARDS
   ═══════════════════════════════════════════════════════════ */
.model-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)) !important;
    gap: 18px !important;
    margin-bottom: 18px !important;
}

.model-card {
    padding: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
    transition: all 0.25s !important;
    position: relative !important;
}
.model-card::before {
    content: '' !important;
    position: absolute !important;
    top: 0; left: 0; right: 0;
    height: 3px !important;
    background: linear-gradient(90deg, #a78bfa, #7c3aed) !important;
}
.model-card:hover {
    transform: translateY(-4px) !important;
    border-color: rgba(167, 139, 250, 0.4) !important;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4) !important;
}

.mc-header {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 20px 22px 16px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
}
.mc-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    background: rgba(167, 139, 250, 0.15);
    border: 1px solid rgba(167, 139, 250, 0.3);
    flex-shrink: 0;
}
.mc-icon.excellent { background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.35); }
.mc-icon.good      { background: rgba(56, 189, 248, 0.15); border-color: rgba(56, 189, 248, 0.35); }
.mc-icon.fair      { background: rgba(251, 191, 36, 0.15); border-color: rgba(251, 191, 36, 0.35); }
.mc-icon.poor      { background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.35); }

.mc-header-info { flex: 1; min-width: 0; }
.mc-header-info h3 {
    font-size: 15px; font-weight: 800;
    color: #f1f5f9; margin: 0 0 3px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.mc-type {
    font-size: 11.5px; color: #94a3b8;
    text-transform: uppercase; letter-spacing: 0.6px;
    font-weight: 700;
}

.mc-status {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 5px 10px !important;
    border-radius: 999px !important;
    font-size: 10.5px !important;
    font-weight: 800 !important;
    letter-spacing: 0.5px !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important;
}
.mc-status.active   { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
.mc-status.archived { background: rgba(148, 163, 184, 0.12); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.25); }
.mc-status-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: currentColor;
    box-shadow: 0 0 6px currentColor;
}

.mc-body {
    padding: 18px 22px !important;
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 12px !important;
}
.mc-accuracy { display: flex !important; flex-direction: column !important; gap: 8px !important; }
.mc-acc-head { display: flex !important; justify-content: space-between !important; align-items: baseline !important; }
.mc-acc-label {
    font-size: 10.5px !important; font-weight: 800 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important; letter-spacing: 0.8px !important;
}
.mc-acc-value {
    font-size: 20px !important; font-weight: 800 !important;
    font-variant-numeric: tabular-nums !important;
}
.mc-acc-value.excellent { color: #34d399; }
.mc-acc-value.good      { color: #38bdf8; }
.mc-acc-value.fair      { color: #fbbf24; }
.mc-acc-value.poor      { color: #f87171; }

.mc-progress {
    height: 8px !important; width: 100% !important;
    background: rgba(255, 255, 255, 0.06) !important;
    border-radius: 999px !important;
    overflow: hidden !important;
}
.mc-progress-fill { height: 100% !important; border-radius: 999px !important; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1) !important; }
.mc-progress-fill.excellent { background: linear-gradient(90deg, #10b981, #34d399); box-shadow: 0 0 10px rgba(16, 185, 129, 0.4); }
.mc-progress-fill.good      { background: linear-gradient(90deg, #0ea5e9, #38bdf8); box-shadow: 0 0 10px rgba(56, 189, 248, 0.4); }
.mc-progress-fill.fair      { background: linear-gradient(90deg, #f59e0b, #fbbf24); box-shadow: 0 0 10px rgba(251, 191, 36, 0.35); }
.mc-progress-fill.poor      { background: linear-gradient(90deg, #dc2626, #ef4444); box-shadow: 0 0 10px rgba(239, 68, 68, 0.4); }

.mc-desc { font-size: 12.5px !important; color: #cbd5e1 !important; line-height: 1.55 !important; margin: 0 !important; }

.mc-meta {
    display: flex !important;
    gap: 20px !important;
    padding-top: 12px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
    margin-top: auto !important;
}
.mc-meta-item { display: flex !important; flex-direction: column !important; gap: 3px !important; }
.mc-meta-label {
    font-size: 10px !important; font-weight: 800 !important;
    color: #64748b !important;
    text-transform: uppercase !important; letter-spacing: 0.6px !important;
}
.mc-meta-value { font-size: 12.5px !important; font-weight: 700 !important; color: #cbd5e1 !important; }
.mc-meta-value code {
    background: rgba(167, 139, 250, 0.12) !important;
    color: #a78bfa !important;
    padding: 2px 8px !important;
    border-radius: 5px !important;
    font-size: 11.5px !important;
    font-family: 'Courier New', monospace !important;
}

.mc-actions {
    display: flex !important;
    gap: 6px !important;
    padding: 14px 22px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
    background: rgba(0, 0, 0, 0.15) !important;
}
.mc-btn {
    flex: 1 !important;
    padding: 9px 12px !important;
    border-radius: 9px !important;
    font-size: 12px !important; font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    text-align: center !important;
    transition: all 0.2s !important;
    border: 1px solid transparent !important;
}
.mc-btn.edit   { background: rgba(167, 139, 250, 0.12) !important; color: #a78bfa !important; border-color: rgba(167, 139, 250, 0.3) !important; }
.mc-btn.edit:hover { background: rgba(167, 139, 250, 0.25) !important; }
.mc-btn.toggle { background: rgba(56, 189, 248, 0.12) !important; color: #38bdf8 !important; border-color: rgba(56, 189, 248, 0.3) !important; }
.mc-btn.toggle:hover { background: rgba(56, 189, 248, 0.25) !important; }
.mc-btn.delete { flex: 0 0 42px !important; background: rgba(239, 68, 68, 0.12) !important; color: #f87171 !important; border-color: rgba(239, 68, 68, 0.3) !important; }
.mc-btn.delete:hover { background: rgba(239, 68, 68, 0.25) !important; }

/* ═══════════════════════════════════════════════════════════
   TABLE
   ═══════════════════════════════════════════════════════════ */
.table-card { padding: 0 !important; overflow: hidden !important; }
.table-wrapper { overflow-x: auto !important; }
.data-table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 13.5px !important;
    table-layout: fixed !important;
}
.data-table thead th {
    background: #1e293b !important;
    color: #94a3b8 !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.7px !important;
    font-size: 10.5px !important;
    padding: 14px 16px !important;
    text-align: left !important;
    border-bottom: 2px solid rgba(167, 139, 250, 0.2) !important;
    white-space: nowrap !important;
}
.data-table tbody td {
    padding: 14px 16px !important;
    color: #f1f5f9 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    vertical-align: middle !important;
}
.data-table tbody tr:hover td { background: rgba(167, 139, 250, 0.04) !important; }
.data-table tbody tr:last-child td { border-bottom: none !important; }

.cat-badge {
    display: inline-block;
    padding: 4px 10px;
    background: rgba(167, 139, 250, 0.12);
    color: #a78bfa;
    border: 1px solid rgba(167, 139, 250, 0.3);
    border-radius: 6px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.key-code {
    display: inline-block;
    padding: 3px 8px;
    background: rgba(56, 189, 248, 0.10);
    color: #38bdf8;
    border-radius: 5px;
    font-family: 'Courier New', monospace;
    font-size: 11.5px;
    font-weight: 700;
}

.mini-progress {
    height: 6px;
    background: rgba(255, 255, 255, 0.06);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 4px;
}
.mini-progress-fill { height: 100%; border-radius: 999px; transition: width 0.5s; }
.mini-progress-fill.excellent { background: #10b981; }
.mini-progress-fill.good      { background: #38bdf8; }
.mini-progress-fill.fair      { background: #fbbf24; }
.mini-progress-fill.poor      { background: #ef4444; }
.mini-acc { font-size: 11.5px; font-weight: 700; }
.mini-acc.excellent { color: #34d399; }
.mini-acc.good      { color: #38bdf8; }
.mini-acc.fair      { color: #fbbf24; }
.mini-acc.poor      { color: #f87171; }
.muted { color: #94a3b8 !important; font-size: 12.5px !important; }

.pill {
    display: inline-flex;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.4px;
    text-transform: uppercase;
}
.pill.active   { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
.pill.archived { background: rgba(148, 163, 184, 0.12); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.25); }

.icon-btn {
    width: 32px; height: 32px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: #cbd5e1;
    font-size: 13px; cursor: pointer;
    font-family: inherit; padding: 0;
    margin: 0 2px;
    transition: all 0.2s;
}
.icon-btn:hover { background: rgba(167, 139, 250, 0.2); border-color: rgba(167, 139, 250, 0.4); color: #a78bfa; transform: translateY(-1px); }
.icon-btn.danger:hover { background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: #f87171; }

.table-footer {
    display: flex; justify-content: space-between;
    align-items: center;
    padding: 14px 22px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(0, 0, 0, 0.15);
    font-size: 12.5px;
    color: #94a3b8;
    flex-wrap: wrap;
    gap: 10px;
}
.footer-hint { color: #64748b; font-style: italic; font-size: 12px; }

/* Info card */
.info-card { padding: 22px 26px !important; }
.info-head { margin-bottom: 16px; }
.info-head h3 { font-size: 16px; font-weight: 800; color: #f1f5f9; margin: 0 0 4px; }
.info-head p { font-size: 12.5px; color: #94a3b8; margin: 0; }
.best-model {
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
    padding: 18px 20px !important;
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.08), rgba(245, 158, 11, 0.04)) !important;
    border: 1px solid rgba(251, 191, 36, 0.25) !important;
    border-radius: 14px !important;
}
.bm-icon {
    width: 52px; height: 52px;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px;
    background: rgba(251, 191, 36, 0.15);
    border: 1px solid rgba(251, 191, 36, 0.3);
    border-radius: 14px;
    flex-shrink: 0;
}
.bm-info { flex: 1 !important; }
.bm-info strong { display: block; font-size: 15px; color: #f1f5f9; font-weight: 800; margin-bottom: 2px; }
.bm-info small { font-size: 12.5px; color: #94a3b8; }
.bm-accuracy { text-align: right; }
.bm-value {
    display: block; font-size: 26px; font-weight: 900;
    color: #fbbf24; line-height: 1;
    font-variant-numeric: tabular-nums;
}
.bm-accuracy small {
    display: block; font-size: 10.5px; color: #94a3b8;
    text-transform: uppercase; letter-spacing: 0.6px;
    font-weight: 700; margin-top: 4px;
}

/* Empty */
.empty-state { text-align: center; padding: 60px 30px; }
.empty-emoji { font-size: 56px; margin-bottom: 14px; opacity: 0.6; }
.empty-state h3 { font-size: 18px; color: #f1f5f9; margin: 0 0 6px; font-weight: 800; }
.empty-state p { color: #94a3b8; font-size: 13.5px; margin: 0 0 20px; }

/* ═══════════════════════════════════════════════════════════
   MODAL
   ═══════════════════════════════════════════════════════════ */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
    z-index: 9999; padding: 20px;
    box-sizing: border-box;
}
.modal-overlay:not([hidden]) { display: flex; }
.modal-overlay[hidden] { display: none !important; }

.modal {
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 26px;
    max-width: 560px; width: 100%;
    max-height: 90vh; overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6);
    box-sizing: border-box;
    animation: modalIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

.modal-header {
    display: flex; justify-content: space-between;
    align-items: center;
    margin-bottom: 20px; padding-bottom: 14px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.modal-header h3 { font-size: 17px; color: #f1f5f9; margin: 0; font-weight: 800; }
.modal-close {
    width: 32px; height: 32px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: #cbd5e1;
    font-size: 14px; cursor: pointer;
    font-family: inherit; padding: 0;
    display: inline-flex; align-items: center; justify-content: center;
    transition: all 0.2s;
}
.modal-close:hover { background: rgba(239, 68, 68, 0.2); color: #f87171; }

.form-row-2 {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 14px; margin-bottom: 14px;
}
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.form-group label {
    font-size: 11px; font-weight: 800;
    color: #94a3b8; text-transform: uppercase;
    letter-spacing: 0.6px;
}
.form-group input,
.form-group select,
.form-group textarea {
    padding: 11px 14px;
    background: rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    color: #f1f5f9;
    font-size: 13.5px;
    font-family: inherit;
    outline: none;
    box-sizing: border-box;
    width: 100%;
    resize: vertical;
    transition: all 0.2s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #a78bfa;
    box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.15);
}
.form-group select option { background: #1e293b; color: #f1f5f9; }

.switch-label {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 14px 16px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 12px !important;
    cursor: pointer !important;
    user-select: none !important;
    margin: 0 !important;
    text-transform: none !important;
    letter-spacing: 0 !important;
}
.switch-label > span:first-child { display: flex; flex-direction: column; gap: 2px; }
.switch-label strong { font-size: 13.5px; color: #f1f5f9; font-weight: 700; }
.switch-label small { font-size: 11.5px; color: #94a3b8; }
.switch-label input[type="checkbox"] { display: none; }
.switch {
    width: 46px; height: 26px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 999px;
    position: relative;
    transition: 0.25s;
    flex-shrink: 0;
}
.switch::after {
    content: '';
    position: absolute;
    top: 3px; left: 3px;
    width: 20px; height: 20px;
    background: #fff;
    border-radius: 50%;
    transition: 0.25s;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
}
.switch-label input:checked ~ .switch {
    background: linear-gradient(135deg, #a78bfa, #7c3aed);
    box-shadow: 0 0 16px rgba(167, 139, 250, 0.4);
}
.switch-label input:checked ~ .switch::after { left: 23px; }

.modal-actions {
    display: flex; justify-content: flex-end;
    gap: 10px; margin-top: 8px;
    padding-top: 18px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

/* Toast */
.toast {
    position: fixed;
    bottom: 28px; right: 28px;
    padding: 14px 22px;
    border-radius: 12px;
    font-size: 13.5px; font-weight: 600;
    color: #fff;
    transform: translateY(20px);
    opacity: 0;
    transition: all 0.3s;
    z-index: 99999;
    pointer-events: none;
    max-width: 400px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5);
}
.toast.show { transform: translateY(0); opacity: 1; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669); }
.toast.error   { background: linear-gradient(135deg, #ef4444, #dc2626); }
.toast.info    { background: linear-gradient(135deg, #38bdf8, #0284c7); }

/* ═══════════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 900px) {
    .admin-ml-page .main-content {
        width: 100% !important;
        max-width: 100% !important;
        padding: 20px !important;
        padding-top: 72px !important;
    }
    .hero-card { flex-direction: column !important; align-items: flex-start !important; }
    .hero-stats { width: 100% !important; justify-content: space-between !important; }
    .model-grid { grid-template-columns: 1fr !important; }
}
@media (max-width: 720px) {
    .admin-ml-page .main-content { padding: 14px !important; padding-top: 68px !important; }
    .toolbar-row { flex-direction: column; align-items: stretch; }
    .search-wrap { min-width: 0; }
    .toolbar-actions { width: 100%; }
    .toolbar-actions .btn-primary,
    .toolbar-actions .btn-secondary { flex: 1; justify-content: center; }
    .form-row-2 { grid-template-columns: 1fr; }
    .toast { left: 16px; right: 16px; bottom: 16px; }
    .data-table { font-size: 12.5px; }
}
</style>