<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card publish-hero">
            <div>
                <a href="<?= BASE_URL ?>/index.php?url=government/alerts" class="back-link">← Alert Center</a>
                <h1>📢 Publish Government Alert</h1>
                <p>Broadcast urgent information to farmers, cooperatives, and district officers.</p>
            </div>
        </section>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="glass-card" style="border-left:3px solid #EF4444;">
                <div class="alert error" style="margin:0;">
                    <span>⚠️</span>
                    <?= htmlspecialchars($_SESSION['flash_error']) ?>
                </div>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/index.php?url=government/publishAlert" method="POST" class="alert-form-wrapper">
            <?= \App\Core\Security::csrfField() ?>

            <div class="alert-form-grid">

                <!-- Left Column: Main content -->
                <div class="glass-card form-main">
                    <h3 class="form-section-title">📝 Alert Content</h3>

                    <div class="form-group">
                        <label>Alert Title *</label>
                        <input type="text" name="title" required maxlength="200"
                               placeholder="e.g., Heavy rainfall warning — Nyagatare">
                    </div>

                    <div class="form-group">
                        <label>Detailed Description</label>
                        <textarea name="body" rows="4" maxlength="1000"
                                  placeholder="Describe the situation, affected areas, and expected impact..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Recommended Action for Farmers</label>
                        <textarea name="action" rows="3" maxlength="500"
                                  placeholder="e.g., Move livestock to higher ground. Delay planting by 48 hours."></textarea>
                    </div>

                    <h3 class="form-section-title">🕒 Expiry (Optional)</h3>
                    <div class="form-group">
                        <label>Auto-deactivate Alert at</label>
                        <input type="datetime-local" name="expires_at">
                        <small class="input-hint">Leave empty to keep active until manually dismissed.</small>
                    </div>
                </div>

                <!-- Right Column: Metadata -->
                <div class="glass-card form-side">
                    <h3 class="form-section-title">⚙️ Classification</h3>

                    <div class="form-group">
                        <label>Alert Type *</label>
                        <select name="alert_type" required>
                            <option value="flood">🌊 Flood</option>
                            <option value="drought">☀️ Drought</option>
                            <option value="pest">🐛 Pest Outbreak</option>
                            <option value="disease_outbreak">🦠 Disease Outbreak</option>
                            <option value="price_shock">💸 Price Shock</option>
                            <option value="food_security">🍽️ Food Security</option>
                            <option value="frost">❄️ Frost</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Severity Level *</label>
                        <div class="severity-options">
                            <label class="sev-option sev-low">
                                <input type="radio" name="severity" value="low">
                                <span class="sev-dot"></span>
                                <span>Low</span>
                            </label>
                            <label class="sev-option sev-medium">
                                <input type="radio" name="severity" value="medium" checked>
                                <span class="sev-dot"></span>
                                <span>Medium</span>
                            </label>
                            <label class="sev-option sev-high">
                                <input type="radio" name="severity" value="high">
                                <span class="sev-dot"></span>
                                <span>High</span>
                            </label>
                            <label class="sev-option sev-critical">
                                <input type="radio" name="severity" value="critical">
                                <span class="sev-dot"></span>
                                <span>Critical</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Affected District</label>
                        <select name="district_id">
                            <option value="">🌍 National (all districts)</option>
                            <?php
                            try {
                                $db = new \App\Core\Database();
                                $db->query("SELECT id, name FROM districts ORDER BY name");
                                foreach ($db->resultSet() as $d) {
                                    echo '<option value="' . (int)$d['id'] . '">'
                                       . htmlspecialchars($d['name']) . '</option>';
                                }
                            } catch (\Throwable $e) { /* silent */ }
                            ?>
                        </select>
                    </div>

                    <div class="severity-preview" id="severityPreview">
                        <span class="preview-label">Priority color:</span>
                        <span class="preview-pill medium" id="previewPill">MEDIUM</span>
                    </div>

                    <button type="submit" class="btn-primary publish-btn">
                        📢 Publish Alert
                    </button>

                    <a href="<?= BASE_URL ?>/index.php?url=government/alerts"
                       class="btn-secondary cancel-btn">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </main>
</div>

<style>
/* ============ HERO ============ */
.publish-hero {
    padding: 25px 30px !important;
    max-width: none;
    background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(239,68,68,0.05));
}
.publish-hero h1 { margin: 6px 0 6px; font-size: 24px; }
.publish-hero p { color: var(--text-sub); margin: 0; font-size: 13px; }
.back-link {
    color: var(--accent);
    text-decoration: none;
    font-size: 12px;
    display: inline-block;
}
.back-link:hover { text-decoration: underline; }

/* ============ FORM LAYOUT ============ */
.alert-form-wrapper { margin-top: 0; }
.alert-form-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    align-items: start;
}

.form-main, .form-side {
    padding: 24px !important;
    max-width: none;
}

.form-section-title {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: var(--accent);
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border);
    margin: 0 0 16px;
    font-weight: 700;
}
.form-section-title:not(:first-child) { margin-top: 26px; }

/* ============ FORM FIELDS ============ */
.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    font-size: 12px;
    color: var(--text-sub);
    margin-bottom: 6px;
    font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="datetime-local"],
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 14px;
    background: rgba(0,0,0,0.25);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: white;
    font-family: inherit;
    font-size: 14px;
    outline: none;
    transition: 200ms;
    box-sizing: border-box;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
    line-height: 1.5;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(132,204,22,0.15);
}

.form-group select option {
    background: #0F3D2E;
    color: white;
}

.input-hint {
    display: block;
    margin-top: 6px;
    font-size: 11px;
    color: var(--text-sub);
    opacity: 0.75;
}

/* ============ SEVERITY RADIO BUTTONS ============ */
.severity-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.sev-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 10px;
    cursor: pointer;
    font-size: 12px;
    transition: 200ms;
}
.sev-option:hover { border-color: var(--accent); }
.sev-option input[type="radio"] { display: none; }
.sev-option .sev-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.sev-low .sev-dot { background: #22C55E; }
.sev-medium .sev-dot { background: #F59E0B; }
.sev-high .sev-dot { background: #EF4444; }
.sev-critical .sev-dot { background: #DC2626; box-shadow: 0 0 8px #DC2626; }

.sev-option input:checked ~ span:last-child { color: white; font-weight: 600; }
.sev-option:has(input:checked) {
    background: rgba(34,197,94,0.12);
    border-color: var(--accent);
}

/* ============ SEVERITY PREVIEW ============ */
.severity-preview {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 14px;
    background: rgba(0,0,0,0.2);
    border-radius: 12px;
    margin: 20px 0;
}
.preview-label {
    font-size: 11px;
    text-transform: uppercase;
    color: var(--text-sub);
    letter-spacing: 0.5px;
}
.preview-pill {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
}
.preview-pill.low { background: rgba(34,197,94,0.2); color: #22C55E; }
.preview-pill.medium { background: rgba(245,158,11,0.2); color: #F59E0B; }
.preview-pill.high { background: rgba(239,68,68,0.2); color: #EF4444; }
.preview-pill.critical { background: #EF4444; color: white; }

/* ============ SUBMIT BUTTONS ============ */
.publish-btn {
    width: 100%;
    padding: 14px;
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 10px;
}
.cancel-btn {
    display: block;
    text-align: center;
    text-decoration: none;
    padding: 12px;
    width: 100%;
    box-sizing: border-box;
}

/* ============ SUCCESS/ERROR ALERTS ============ */
.alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 13px;
    line-height: 1.4;
}
.alert span:first-child { font-size: 16px; }
.alert.success {
    background: rgba(34,197,94,0.12);
    color: #86EFAC;
    border: 1px solid rgba(34,197,94,0.35);
}
.alert.error {
    background: rgba(239,68,68,0.12);
    color: #FCA5A5;
    border: 1px solid rgba(239,68,68,0.35);
}

/* ============ RESPONSIVE ============ */
@media (max-width: 900px) {
    .alert-form-grid { grid-template-columns: 1fr; }
    .severity-options { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 500px) {
    .severity-options { grid-template-columns: 1fr; }
    .publish-hero h1 { font-size: 20px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Severity preview updates as the user picks a level
    const radios = document.querySelectorAll('input[name="severity"]');
    const preview = document.getElementById('previewPill');
    const map = { low: 'LOW', medium: 'MEDIUM', high: 'HIGH', critical: 'CRITICAL' };

    function updatePreview(value) {
        if (!preview) return;
        preview.textContent = map[value] || value.toUpperCase();
        preview.className = 'preview-pill ' + value;
    }

    radios.forEach(r => {
        r.addEventListener('change', e => updatePreview(e.target.value));
    });
});
</script>