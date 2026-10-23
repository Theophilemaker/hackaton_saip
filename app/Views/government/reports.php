<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <?php
        /* ─────────────────────────────────────────────────────────
           FIX: The controller extracts $data into local variables,
           so inside this view the reports array is $reports — NOT
           $data['reports']. The old version threw "Undefined array
           key 'reports'" on every render. Null-safe fallback added.
           ───────────────────────────────────────────────────────── */
        $reports = $reports ?? ($data['reports'] ?? []);
        ?>

        <section class="glass-card">
            <div class="card-header"><h3>📄 <?= __('gov.reports_center') ?></h3></div>

            <div class="report-types">
                <a href="<?= BASE_URL ?>/government/generateReport?type=district" target="_blank" class="report-type-card">
                    <span class="rt-icon">📍</span>
                    <strong>District Report</strong>
                    <small>Per-district production and alerts</small>
                </a>
                <a href="<?= BASE_URL ?>/government/generateReport?type=provincial" target="_blank" class="report-type-card">
                    <span class="rt-icon">🗺️</span>
                    <strong>Provincial Report</strong>
                    <small>Province-level KPIs</small>
                </a>
                <a href="<?= BASE_URL ?>/government/generateReport?type=national" target="_blank" class="report-type-card">
                    <span class="rt-icon">🇷🇼</span>
                    <strong>National Report</strong>
                    <small>Executive summary</small>
                </a>
                <a href="<?= BASE_URL ?>/government/generateReport?type=export" target="_blank" class="report-type-card">
                    <span class="rt-icon">🚢</span>
                    <strong>Export Report</strong>
                    <small>NAEB-style export analytics</small>
                </a>
                <a href="<?= BASE_URL ?>/government/generateReport?type=ai_insight" target="_blank" class="report-type-card">
                    <span class="rt-icon">🤖</span>
                    <strong>AI Insight Report</strong>
                    <small>AI-generated insights</small>
                </a>
            </div>

            <div class="export-row">
                <strong>Quick Exports:</strong>
                <button onclick="exportData('production','csv')"   class="btn-secondary">📊 Production CSV</button>
                <button onclick="exportData('production','excel')" class="btn-secondary">📗 Production Excel</button>
                <button onclick="exportData('nst2','csv')"         class="btn-secondary">📊 NST2 CSV</button>
                <button onclick="exportData('market','csv')"       class="btn-secondary">📊 Market CSV</button>
                <button onclick="exportData('food','csv')"         class="btn-secondary">📊 Food Security CSV</button>
            </div>
        </section>

        <section class="glass-card">
            <div class="card-header"><h3>🗂️ Generated Reports</h3></div>

            <?php if (empty($reports)): ?>
                <p class="empty-state">No reports generated yet.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Format</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reports as $r): ?>
                        <?php
                        // Null-safe field resolution — prevents PHP 8 warnings
                        $title  = $r['title']       ?? ($r['name']        ?? 'Untitled');
                        $type   = $r['report_type'] ?? ($r['survey_type'] ?? 'general');
                        $format = $r['file_format'] ?? 'csv';
                        $when   = $r['generated_at'] ?? ($r['created_at'] ?? null);
                        ?>
                        <tr>
                            <td><?= e($title) ?></td>
                            <td><?= ucfirst(str_replace('_', ' ', $type)) ?></td>
                            <td><span class="badge green"><?= strtoupper($format) ?></span></td>
                            <td><?= $when ? date('M d, Y H:i', strtotime($when)) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</div>