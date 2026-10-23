<div class="dashboard-layout upload-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ============ HERO ============ -->
        <section class="glass-card upload-hero">
            <div class="hero-content">
                <a href="<?= BASE_URL ?>/nisr" class="back-link">← <?= __('nav.nisr') ?? 'Back to NISR' ?></a>
                <h1>📤 <?= __('nisr.upload') ?? 'Upload Dataset' ?></h1>
                <p>
                    Upload a CSV, Excel, or PDF dataset — we'll extract the data, auto-detect columns, 
                    compute quality metrics, and make it queryable across the portal.
                </p>
            </div>
            <a href="<?= BASE_URL ?>/nisr/datasets" class="btn-hero-secondary">
                📁 <?= __('nisr.datasets') ?? 'Browse Datasets' ?>
            </a>
        </section>

        <!-- ============ ERROR ALERTS ============ -->
        <?php if (!empty($data['error'])): ?>
            <div class="glass-card error-card">
                <span class="error-icon">⚠️</span>
                <div class="error-text"><?= e($data['error']) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="glass-card error-card">
                <span class="error-icon">⚠️</span>
                <div class="error-text"><?= $_SESSION['flash_error'] ?></div>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- ============ GUIDELINES ============ -->
        <section class="glass-card info-banner">
            <span class="info-icon">ℹ️</span>
            <div>
                <strong>Accepted File Formats</strong>
                <ul class="guideline-list">
                    <li>📊 <strong>CSV</strong> — First row must contain column headers; comma-separated; UTF-8 recommended (for Kinyarwanda / French accents)</li>
                    <li>📗 <strong>Excel</strong> (.xlsx / .xls) — First sheet is used; first row must be headers</li>
                    <li>📕 <strong>PDF</strong> — Tables extracted automatically; best results with text-based (non-scanned) PDFs</li>
                    <li>✅ Numeric columns auto-detected for statistics</li>
                    <li>✅ Large files (100K+ rows) supported — parsed in batches</li>
                    <li>✅ Max file size: <strong>50 MB</strong> per upload</li>
                </ul>
            </div>
        </section>

        <!-- ============ UPLOAD FORM ============ -->
        <section class="glass-card upload-form-card">
            <div class="card-header">
                <h3>📋 Dataset Information</h3>
                <span class="badge green">Step 1 of 1</span>
            </div>

            <form action="<?= BASE_URL ?>/nisr/upload" method="POST" enctype="multipart/form-data" id="uploadForm">
                <?= \App\Core\Security::csrfField() ?>

                <!-- ============ BASIC INFO ============ -->
                <h4 class="section-title">📝 Basic Information</h4>

                <div class="form-group">
                    <label>Dataset Name <span class="required">*</span></label>
                    <input type="text" name="name" required maxlength="200"
                           placeholder="e.g., Agricultural Household Survey 2025"
                           value="<?= e($_POST['name'] ?? '') ?>">
                    <small class="input-hint">A clear, descriptive name for this dataset</small>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" maxlength="1000"
                              placeholder="What does this dataset cover? Which population? Which year?"
                              ><?= e($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Survey Type <span class="required">*</span></label>
                        <select name="survey_type" required>
                            <option value="agricultural_survey" <?= (($_POST['survey_type'] ?? 'agricultural_survey') === 'agricultural_survey') ? 'selected' : '' ?>>🌾 Agricultural Survey</option>
                            <option value="EICV" <?= (($_POST['survey_type'] ?? '') === 'EICV') ? 'selected' : '' ?>>👨‍👩‍👧 EICV (Household Consumption)</option>
                            <option value="RPHC" <?= (($_POST['survey_type'] ?? '') === 'RPHC') ? 'selected' : '' ?>>🏘️ RPHC (Population Census)</option>
                            <option value="household_survey" <?= (($_POST['survey_type'] ?? '') === 'household_survey') ? 'selected' : '' ?>>🏠 Household Survey</option>
                            <option value="census" <?= (($_POST['survey_type'] ?? '') === 'census') ? 'selected' : '' ?>>📊 Census</option>
                            <option value="external" <?= (($_POST['survey_type'] ?? '') === 'external') ? 'selected' : '' ?>>🌍 External / Partner Data</option>
                            <option value="custom" <?= (($_POST['survey_type'] ?? '') === 'custom') ? 'selected' : '' ?>>📁 Custom / Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Year <span class="required">*</span></label>
                        <input type="number" name="year" required min="1990" max="2050"
                               value="<?= e($_POST['year'] ?? date('Y')) ?>">
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Source <span class="required">*</span></label>
                        <input type="text" name="source" required maxlength="200"
                               placeholder="NISR"
                               value="<?= e($_POST['source'] ?? 'NISR') ?>">
                    </div>
                    <div class="form-group">
                        <label>Source URL <span class="optional">(optional)</span></label>
                        <input type="url" name="source_url" maxlength="500"
                               placeholder="https://www.statistics.gov.rw/..."
                               value="<?= e($_POST['source_url'] ?? '') ?>">
                    </div>
                </div>

                <!-- ============ FILE UPLOAD ============ -->
                <h4 class="section-title">📁 Dataset File</h4>

                <div class="file-upload-zone" id="uploadZone">
                    <input type="file" name="file" id="datasetFile"
                           accept=".csv,.xlsx,.xls,.pdf,text/csv,application/csv,application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                           hidden required>

                    <div id="filePrompt">
                        <span class="upload-icon">📊</span>
                        <p><strong>Click to upload</strong> or drop a file here</p>
                        <small>CSV · XLSX · XLS · PDF &nbsp;·&nbsp; Max 50 MB</small>
                        <div class="format-chips">
                            <span class="format-chip">.csv</span>
                            <span class="format-chip">.xlsx</span>
                            <span class="format-chip">.xls</span>
                            <span class="format-chip">.pdf</span>
                        </div>
                    </div>

                    <div id="filePreview" style="display:none;">
                        <span class="file-icon" id="fileIcon">📄</span>
                        <div class="file-details">
                            <strong id="fileName">—</strong>
                            <small id="fileSize">—</small>
                            <small id="fileType" class="file-type-tag">—</small>
                        </div>
                        <button type="button" class="file-remove" onclick="clearFile()" title="Remove file">✕</button>
                    </div>
                </div>

                <!-- ============ OPTIONS ============ -->
                <h4 class="section-title">⚙️ Options</h4>

                <div class="checkbox-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_public" value="1"
                               <?= !empty($_POST['is_public']) ? 'checked' : '' ?>>
                        <div>
                            <strong>🌐 Make this dataset public</strong>
                            <small>Expose via public REST API for researchers and partners</small>
                        </div>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="compute_quality" value="1" checked>
                        <div>
                            <strong>📊 Auto-compute data quality metrics</strong>
                            <small>Missing values, outliers, min/max/mean per column</small>
                        </div>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="notify_officers" value="1">
                        <div>
                            <strong>🔔 Notify agricultural officers</strong>
                            <small>Send notifications when dataset is processed</small>
                        </div>
                    </label>
                </div>

                <!-- ============ SUBMIT ============ -->
                <div class="action-buttons">
                    <button type="submit" class="btn-primary" id="submitBtn">
                        📤 Upload & Process Dataset
                    </button>
                    <a href="<?= BASE_URL ?>/nisr" class="btn-secondary">Cancel</a>
                </div>

                <p class="upload-note">
                    ⏳ Processing time varies by format and size:
                    CSV/Excel ≈ 10–60 seconds · PDF table extraction may take 1–3 minutes.
                    You'll be redirected to the dataset page once processing completes.
                </p>
            </form>
        </section>

        <!-- ============ SAMPLE CSV HELP ============ -->
        <section class="glass-card template-card">
            <div class="card-header">
                <div>
                    <h3>📖 Need a template?</h3>
                    <p class="template-subtitle">
                        Download a sample CSV to see the expected format
                    </p>
                </div>
                <button onclick="downloadSampleCSV()" class="btn-sample">
                    ⬇️ Download Sample CSV
                </button>
            </div>

            <p class="template-help">
                Your CSV (or the first sheet of your Excel file) should look something like this:
            </p>
            <pre class="code-block">district,crop,season,area_ha,yield_kg,year
Bugesera,Maize,A,120,45000,2024
Musanze,Potatoes,A,85,120000,2024
Nyagatare,Maize,A,200,85000,2024
Huye,Beans,B,60,18000,2024
Kigali,Tomatoes,A,15,22500,2024</pre>
            <p class="input-hint">
                💡 Column names become queryable dimensions in the Statistical Explorer, 
                Chart Builder, and Comparison tool. For PDFs, make sure the file contains a real 
                table (not a scanned image) for the best extraction accuracy.
            </p>
        </section>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const zone      = document.getElementById('uploadZone');
    const input     = document.getElementById('datasetFile');
    const prompt    = document.getElementById('filePrompt');
    const preview   = document.getElementById('filePreview');
    const nameEl    = document.getElementById('fileName');
    const sizeEl    = document.getElementById('fileSize');
    const typeEl    = document.getElementById('fileType');
    const iconEl    = document.getElementById('fileIcon');
    const form      = document.getElementById('uploadForm');
    const submitBtn = document.getElementById('submitBtn');

    const ALLOWED_EXT = ['csv', 'xlsx', 'xls', 'pdf'];
    const FORMAT_META = {
        csv:  { icon: '📊', label: 'CSV — Comma Separated Values' },
        xlsx: { icon: '📗', label: 'Excel Workbook (.xlsx)' },
        xls:  { icon: '📗', label: 'Excel Workbook (.xls)' },
        pdf:  { icon: '📕', label: 'PDF Document' }
    };
    const MAX_BYTES = 50 * 1024 * 1024;

    zone.addEventListener('click', (e) => {
        if (e.target.closest('.file-remove')) return;
        input.click();
    });

    zone.addEventListener('dragover', e => {
        e.preventDefault();
        zone.classList.add('dragover');
    });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            try {
                input.files = e.dataTransfer.files;
            } catch (err) {
                alert('Your browser blocked the drop. Please click to browse for the file instead.');
                return;
            }
            showFile(input.files[0]);
        }
    });

    input.addEventListener('change', () => {
        if (input.files.length) showFile(input.files[0]);
    });

    function getExtension(filename) {
        const parts = filename.toLowerCase().split('.');
        return parts.length > 1 ? parts.pop() : '';
    }

    function showFile(file) {
        const ext = getExtension(file.name);

        if (!ALLOWED_EXT.includes(ext)) {
            alert('Unsupported file type ".' + (ext || '?') + '".\n\nPlease upload a CSV, Excel (.xlsx/.xls), or PDF file.');
            clearFile();
            return;
        }

        if (file.size > MAX_BYTES) {
            alert('File too large (' + formatSize(file.size) + '). Maximum allowed is 50 MB.');
            clearFile();
            return;
        }

        if (file.size === 0) {
            alert('This file appears to be empty.');
            clearFile();
            return;
        }

        const meta = FORMAT_META[ext] || { icon: '📄', label: ext.toUpperCase() };

        iconEl.textContent = meta.icon;
        nameEl.textContent = file.name;
        sizeEl.textContent = formatSize(file.size);
        typeEl.textContent = meta.label;

        prompt.style.display  = 'none';
        preview.style.display = 'flex';
    }

    form.addEventListener('submit', (e) => {
        if (!input.files.length) {
            e.preventDefault();
            alert('Please select a CSV, Excel, or PDF file to upload.');
            return;
        }

        const ext = getExtension(input.files[0].name);
        if (!ALLOWED_EXT.includes(ext)) {
            e.preventDefault();
            alert('Unsupported file type. Please upload a CSV, Excel, or PDF file.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Processing… this may take a minute';
    });

    window.clearFile = function() {
        input.value = '';
        prompt.style.display = 'block';
        preview.style.display = 'none';
        iconEl.textContent = '📄';
        nameEl.textContent = '—';
        sizeEl.textContent = '—';
        typeEl.textContent = '—';
    };
});

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

function downloadSampleCSV() {
    const csv = [
        'district,crop,season,area_ha,yield_kg,year',
        'Bugesera,Maize,A,120,45000,2024',
        'Musanze,Potatoes,A,85,120000,2024',
        'Nyagatare,Maize,A,200,85000,2024',
        'Huye,Beans,B,60,18000,2024',
        'Kigali,Tomatoes,A,15,22500,2024',
        'Rubavu,Coffee,A,45,3200,2024',
        'Nyamasheke,Tea,B,80,18000,2024',
        'Rulindo,Maize,B,95,36000,2024',
        'Rwamagana,Beans,A,55,16500,2024',
    ].join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'saip_sample_dataset.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>

<style>
/* =========================================
   UPLOAD PAGE — FULL WIDTH DARK THEME
   ========================================= */

/* 1. FORCE FULL WIDTH LAYOUT */
.upload-page.dashboard-layout {
    display: flex !important;
    width: 100vw !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
    overflow-x: hidden !important;
}

.upload-page .main-content {
    flex: 1 !important;
    width: calc(100% - 250px) !important;
    max-width: none !important;
    min-width: 0 !important;
    padding: 32px !important;
    box-sizing: border-box !important;
}

/* 2. CARD BASE */
.upload-page .glass-card {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 28px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
}

/* 3. HERO */
.upload-hero {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.12), rgba(34, 197, 94, 0.08)) !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 24px !important;
    flex-wrap: wrap !important;
}

.hero-content { flex: 1 !important; min-width: 280px !important; }

.upload-hero h1 {
    font-size: 26px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 8px 0 8px 0 !important;
    line-height: 1.2 !important;
}

.upload-hero p {
    color: #94a3b8 !important;
    margin: 0 !important;
    font-size: 14px !important;
    max-width: 700px !important;
    line-height: 1.6 !important;
}

.back-link {
    color: #38bdf8 !important;
    text-decoration: none !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    display: inline-block !important;
}
.back-link:hover { text-decoration: underline !important; }

.btn-hero-secondary {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 12px 22px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #f1f5f9 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
}
.btn-hero-secondary:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    transform: translateY(-2px) !important;
}

/* 4. ERROR CARD */
.error-card {
    display: flex !important;
    align-items: center !important;
    gap: 14px !important;
    border-left: 4px solid #ef4444 !important;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(239, 68, 68, 0.05)) !important;
}
.error-icon { font-size: 22px !important; }
.error-text { color: #fca5a5 !important; font-size: 14px !important; font-weight: 500 !important; }

/* 5. INFO BANNER */
.info-banner {
    display: flex !important;
    gap: 16px !important;
    align-items: flex-start !important;
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(14, 165, 233, 0.03)) !important;
    border-left: 4px solid #0ea5e9 !important;
}
.info-icon { font-size: 22px !important; flex-shrink: 0 !important; line-height: 1 !important; }
.info-banner strong { display: block !important; font-size: 14px !important; color: #f1f5f9 !important; margin-bottom: 10px !important; }
.guideline-list {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 6px !important;
}
.guideline-list li {
    font-size: 13px !important;
    color: #cbd5e1 !important;
    line-height: 1.5 !important;
}
.guideline-list strong { display: inline !important; margin: 0 !important; color: #38bdf8 !important; }

/* 6. CARD HEADER */
.card-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 20px !important;
    padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
}
.card-header h3 {
    font-size: 18px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    margin: 0 !important;
}

.badge {
    display: inline-flex !important;
    align-items: center !important;
    padding: 5px 12px !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 0.5px !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
}
.badge.green { background: rgba(52, 211, 153, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(52, 211, 153, 0.3) !important; }

/* 7. FORM SECTIONS */
.section-title {
    margin: 28px 0 16px !important;
    font-size: 12px !important;
    text-transform: uppercase !important;
    letter-spacing: 1.2px !important;
    color: #38bdf8 !important;
    padding-bottom: 8px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    font-weight: 700 !important;
}
.section-title:first-of-type { margin-top: 8px !important; }

.required { color: #ef4444 !important; }
.optional { color: #64748b !important; font-weight: 400 !important; font-size: 11px !important; }

.form-group {
    display: flex !important;
    flex-direction: column !important;
    gap: 8px !important;
    margin-bottom: 18px !important;
}

.form-group label {
    font-size: 12px !important;
    font-weight: 700 !important;
    color: #cbd5e1 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="url"],
.form-group textarea,
.form-group select {
    width: 100% !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    padding: 12px 14px !important;
    color: #f1f5f9 !important;
    font-size: 14px !important;
    font-family: inherit !important;
    outline: none !important;
    transition: border-color 0.2s, box-shadow 0.2s !important;
    box-sizing: border-box !important;
}

.form-group select {
    -webkit-appearance: none !important;
    appearance: none !important;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 16px !important;
    padding-right: 40px !important;
    cursor: pointer !important;
}

.form-group select option {
    background: #1e293b !important;
    color: #f1f5f9 !important;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
}

.form-group textarea {
    resize: vertical !important;
    min-height: 80px !important;
}

.form-row-2 {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 16px !important;
}
.form-row-2 .form-group { margin-bottom: 0 !important; }

.input-hint {
    display: block !important;
    font-size: 11px !important;
    color: #64748b !important;
    line-height: 1.4 !important;
    margin-top: 2px !important;
}

/* 8. FILE UPLOAD ZONE */
.file-upload-zone {
    border: 2px dashed rgba(56, 189, 248, 0.4) !important;
    border-radius: 16px !important;
    padding: 40px 30px !important;
    text-align: center !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    margin-bottom: 20px !important;
    min-height: 200px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: rgba(56, 189, 248, 0.03) !important;
}

.file-upload-zone:hover,
.file-upload-zone.dragover {
    background: rgba(56, 189, 248, 0.08) !important;
    border-color: #38bdf8 !important;
    transform: scale(1.005) !important;
}

.upload-icon {
    font-size: 52px !important;
    display: block !important;
    margin-bottom: 14px !important;
}

.file-upload-zone p {
    color: #f1f5f9 !important;
    font-size: 15px !important;
    margin: 0 0 8px 0 !important;
}

.file-upload-zone small {
    color: #94a3b8 !important;
    font-size: 12px !important;
}

.format-chips {
    display: flex !important;
    justify-content: center !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
    margin-top: 16px !important;
}

.format-chip {
    font-size: 10px !important;
    font-weight: 700 !important;
    letter-spacing: 0.6px !important;
    text-transform: uppercase !important;
    padding: 5px 12px !important;
    border-radius: 999px !important;
    background: rgba(56, 189, 248, 0.12) !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    color: #38bdf8 !important;
}

/* File preview */
#filePreview {
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
    padding: 20px 24px !important;
    background: rgba(34, 197, 94, 0.08) !important;
    border: 1px solid rgba(34, 197, 94, 0.3) !important;
    border-radius: 12px !important;
    width: 100% !important;
    max-width: 560px !important;
    text-align: left !important;
    animation: alertIn 0.3s ease-out !important;
    box-sizing: border-box !important;
}

.file-icon { font-size: 38px !important; flex-shrink: 0 !important; }

.file-details { flex: 1 !important; min-width: 0 !important; }

.file-details strong {
    display: block !important;
    font-size: 14px !important;
    color: #f1f5f9 !important;
    margin-bottom: 4px !important;
    word-break: break-all !important;
}

.file-details small {
    display: block !important;
    color: #34d399 !important;
    font-size: 12px !important;
    font-weight: 600 !important;
}

.file-type-tag {
    color: #94a3b8 !important;
    font-weight: 400 !important;
    font-size: 11px !important;
    margin-top: 2px !important;
}

.file-remove {
    width: 34px !important;
    height: 34px !important;
    border-radius: 8px !important;
    background: rgba(239, 68, 68, 0.15) !important;
    border: 1px solid rgba(239, 68, 68, 0.35) !important;
    color: #fca5a5 !important;
    cursor: pointer !important;
    font-size: 14px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s !important;
    flex-shrink: 0 !important;
}
.file-remove:hover {
    background: #ef4444 !important;
    color: white !important;
    transform: scale(1.1) !important;
}

/* 9. CHECKBOXES */
.checkbox-row {
    display: flex !important;
    flex-direction: column !important;
    gap: 12px !important;
}

.checkbox-label {
    display: flex !important;
    align-items: flex-start !important;
    gap: 14px !important;
    padding: 16px 20px !important;
    background: rgba(255, 255, 255, 0.03) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 12px !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
}

.checkbox-label:hover {
    background: rgba(34, 197, 94, 0.06) !important;
    border-color: rgba(34, 197, 94, 0.3) !important;
}

.checkbox-label input[type="checkbox"] {
    width: 18px !important;
    height: 18px !important;
    accent-color: #10b981 !important;
    cursor: pointer !important;
    flex-shrink: 0 !important;
    margin-top: 2px !important;
}

.checkbox-label strong {
    display: block !important;
    font-size: 13px !important;
    color: #f1f5f9 !important;
    margin-bottom: 4px !important;
}

.checkbox-label small {
    display: block !important;
    font-size: 11px !important;
    color: #94a3b8 !important;
    line-height: 1.4 !important;
}

/* 10. SUBMIT BUTTONS */
.action-buttons {
    display: flex !important;
    gap: 12px !important;
    margin-top: 28px !important;
    flex-wrap: wrap !important;
}

.btn-primary {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    padding: 14px 32px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
    text-decoration: none !important;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5) !important;
}

.btn-primary:disabled {
    opacity: 0.6 !important;
    cursor: not-allowed !important;
    transform: none !important;
    box-shadow: none !important;
}

.btn-secondary {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 14px 28px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
}
.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    color: #f1f5f9 !important;
}

.upload-note {
    margin: 20px 0 0 0 !important;
    padding: 14px 18px !important;
    background: rgba(245, 158, 11, 0.08) !important;
    border-left: 3px solid #f59e0b !important;
    border-radius: 8px !important;
    font-size: 12px !important;
    color: #cbd5e1 !important;
    line-height: 1.6 !important;
}

/* 11. TEMPLATE CARD */
.template-subtitle {
    font-size: 13px !important;
    color: #94a3b8 !important;
    margin: 4px 0 0 0 !important;
}

.template-help {
    color: #cbd5e1 !important;
    font-size: 13px !important;
    margin: 0 0 12px 0 !important;
}

.btn-sample {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 10px 20px !important;
    background: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
}
.btn-sample:hover {
    background: rgba(56, 189, 248, 0.25) !important;
    transform: translateY(-2px) !important;
}

.code-block {
    background: rgba(0, 0, 0, 0.4) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 12px !important;
    padding: 18px !important;
    font-family: 'Courier New', monospace !important;
    font-size: 12px !important;
    color: #6ee7b7 !important;
    overflow-x: auto !important;
    margin: 12px 0 !important;
    white-space: pre !important;
    line-height: 1.7 !important;
}

/* 12. RESPONSIVE */
@media (max-width: 768px) {
    .upload-page .main-content { padding: 16px !important; }
    .upload-page .glass-card { padding: 20px !important; }
    .form-row-2 { grid-template-columns: 1fr !important; gap: 16px !important; }
    .form-row-2 .form-group { margin-bottom: 0 !important; }
    .upload-hero { flex-direction: column !important; align-items: flex-start !important; }
    .upload-hero h1 { font-size: 22px !important; }
    #filePreview { flex-direction: column !important; text-align: center !important; }
    .file-details { text-align: center !important; }
    .action-buttons { flex-direction: column !important; }
    .btn-primary, .btn-secondary { width: 100% !important; }
}

@keyframes alertIn {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>