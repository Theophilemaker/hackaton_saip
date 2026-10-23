<div class="dashboard-layout api-docs-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ============ HERO ============ -->
        <section class="glass-card api-hero">
            <div class="hero-content">
                <span class="hero-tag">Developer Resources</span>
                <h1>🔑 Public Data API</h1>
                <p>
                    Access NISR datasets programmatically via REST endpoints. 
                    Generate tokens, query datasets, and integrate statistical data into your own applications.
                </p>
            </div>
            <div class="hero-badge">
                <span class="badge-live">● LIVE</span>
                <span class="badge-count"><?= count($data['tokens'] ?? []) ?> Token<?= count($data['tokens'] ?? []) !== 1 ? 's' : '' ?></span>
            </div>
        </section>

        <!-- ============ YOUR API TOKENS ============ -->
        <section class="glass-card tokens-card">
            <div class="card-header">
                <div>
                    <h3>🎫 Your API Tokens</h3>
                    <p class="card-subtitle">
                        Generate tokens to authenticate your API requests. Keep them secure — they grant full read access to public datasets.
                    </p>
                </div>
            </div>

            <!-- Generate new token -->
            <div class="generate-token-row">
                <div class="input-wrap">
                    <span class="input-icon">🏷️</span>
                    <input type="text" id="newTokenLabel" 
                           placeholder="Token label (e.g., Research Access, My App)"
                           maxlength="100">
                </div>
                <button class="btn-generate" id="generateBtn" onclick="generateToken()">
                    ➕ Generate Token
                </button>
            </div>

            <!-- Tokens table -->
            <?php if (empty($data['tokens'])): ?>
                <div class="empty-tokens">
                    <div class="empty-icon">🔑</div>
                    <h3>No API Tokens Yet</h3>
                    <p>Generate your first token above to start accessing datasets programmatically.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="tokens-table">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Token</th>
                                <th>Calls Made</th>
                                <th>Last Used</th>
                                <th class="actions-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($data['tokens'] as $t): 
                            $neverUsed = empty($t['last_used_at']);
                            $tokenPreview = substr($t['token'], 0, 16) . '…';
                        ?>
                            <tr>
                                <td class="label-cell">
                                    <strong><?= e($t['label']) ?></strong>
                                </td>
                                <td>
                                    <code class="token-code"><?= e($tokenPreview) ?></code>
                                </td>
                                <td class="num-cell">
                                    <span class="calls-badge <?= $t['calls_made'] > 0 ? 'active' : 'inactive' ?>">
                                        <?= number_format((int)$t['calls_made']) ?>
                                    </span>
                                </td>
                                <td class="date-cell">
                                    <?php if ($neverUsed): ?>
                                        <span class="never-used">Never</span>
                                    <?php else: ?>
                                        <?= date('M d, Y', strtotime($t['last_used_at'])) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <button class="action-icon-btn" 
                                            onclick="copyToClipboard('<?= e($t['token']) ?>', this)" 
                                            title="Copy full token">
                                        📋
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- ============ API REFERENCE ============ -->
        <section class="glass-card api-ref-card">
            <div class="card-header">
                <div>
                    <h3>📚 API Reference</h3>
                    <p class="card-subtitle">
                        All endpoints require a valid <code>token</code> parameter. Rate limits apply per token.
                    </p>
                </div>
            </div>

            <!-- Endpoint: List All Datasets -->
            <div class="endpoint-block">
                <div class="endpoint-header">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/nisr/apiDatasets</span>
                    <button class="copy-btn" onclick="copyToClipboard('<?= BASE_URL ?>/nisr/apiDatasets?token=YOUR_TOKEN', this)">
                        📋 Copy
                    </button>
                </div>
                <p class="endpoint-desc">Retrieve a list of all published public datasets available on the platform.</p>
                <pre class="code-block">GET <?= BASE_URL ?>/nisr/apiDatasets?token=YOUR_TOKEN</pre>
            </div>

            <!-- Endpoint: Fetch a Dataset -->
            <div class="endpoint-block">
                <div class="endpoint-header">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/nisr/apiDataset/{slug}</span>
                    <button class="copy-btn" onclick="copyToClipboard('<?= BASE_URL ?>/nisr/apiDataset/{slug}?token=YOUR_TOKEN&limit=100&offset=0', this)">
                        📋 Copy
                    </button>
                </div>
                <p class="endpoint-desc">Fetch rows from a specific dataset with pagination support.</p>
                <div class="param-list">
                    <div class="param-row">
                        <span class="param-name">slug</span>
                        <span class="param-type">string</span>
                        <span class="param-desc">The unique dataset identifier</span>
                    </div>
                    <div class="param-row">
                        <span class="param-name">limit</span>
                        <span class="param-type">int</span>
                        <span class="param-desc">Rows per request (max 1000, default 100)</span>
                    </div>
                    <div class="param-row">
                        <span class="param-name">offset</span>
                        <span class="param-type">int</span>
                        <span class="param-desc">Pagination offset (default 0)</span>
                    </div>
                </div>
                <pre class="code-block">GET <?= BASE_URL ?>/nisr/apiDataset/{slug}?token=YOUR_TOKEN&limit=100&offset=0</pre>
            </div>

            <!-- Response Format -->
            <div class="endpoint-block">
                <div class="endpoint-header">
                    <span class="method-badge json">JSON</span>
                    <span class="endpoint-path">Response Format</span>
                    <button class="copy-btn" onclick="copyResponseSample(this)">
                        📋 Copy
                    </button>
                </div>
                <p class="endpoint-desc">All successful responses return JSON with this structure:</p>
                <pre class="code-block" id="responseSample">{
  "dataset": {
    "name": "Agricultural Household Survey 2024",
    "survey_type": "agricultural_survey",
    "year": 2024,
    "row_count": 15000,
    "schema": [
      { "name": "district", "type": "string" },
      { "name": "crop", "type": "string" },
      { "name": "yield_kg", "type": "number" }
    ]
  },
  "pagination": { "limit": 100, "offset": 0 },
  "data": [
    { "district": "Bugesera", "crop": "Maize", "yield_kg": 45000 },
    { "district": "Musanze", "crop": "Potatoes", "yield_kg": 120000 }
  ]
}</pre>
            </div>

            <!-- Error Responses -->
            <div class="endpoint-block">
                <div class="endpoint-header">
                    <span class="method-badge error">ERROR</span>
                    <span class="endpoint-path">Error Responses</span>
                </div>
                <div class="error-list">
                    <div class="error-row">
                        <span class="status-code err-401">401</span>
                        <span class="error-msg">Invalid or missing API token</span>
                    </div>
                    <div class="error-row">
                        <span class="status-code err-404">404</span>
                        <span class="error-msg">Dataset not found or not public</span>
                    </div>
                    <div class="error-row">
                        <span class="status-code err-500">500</span>
                        <span class="error-msg">Internal server error</span>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
async function generateToken() {
    const input = document.getElementById('newTokenLabel');
    const btn = document.getElementById('generateBtn');
    const label = input.value.trim();

    if (!label) {
        input.focus();
        showToast('Please enter a token label first.', 'error');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '⏳ Generating...';

    try {
        const res = await fetch('<?= BASE_URL ?>/nisr/createApiToken', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'label=' + encodeURIComponent(label)
        });
        const data = await res.json();
        
        if (data.success) {
            // Copy automatically to clipboard
            try {
                await navigator.clipboard.writeText(data.token);
                showToast('Token created & copied to clipboard!', 'success');
            } catch (e) {
                showToast('Token created successfully!', 'success');
            }
            
            // Show token in a modal-like alert as fallback
            setTimeout(() => {
                const msg = 'Your API Token:\n\n' + data.token + '\n\n' +
                           'Store this securely — it will only be shown in full once.\n\n' +
                           '(Already copied to your clipboard.)';
                alert(msg);
                location.reload();
            }, 800);
        } else {
            showToast(data.error || 'Failed to generate token.', 'error');
            btn.disabled = false;
            btn.innerHTML = '➕ Generate Token';
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '➕ Generate Token';
    }
}

function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        if (btn) {
            const original = btn.innerHTML;
            btn.innerHTML = '✅ Copied';
            btn.classList.add('copied');
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.remove('copied');
            }, 1500);
        }
    }).catch(() => {
        // Fallback for older browsers
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        if (btn) {
            btn.innerHTML = '✅ Copied';
            setTimeout(() => btn.innerHTML = '📋 Copy', 1500);
        }
    });
}

function copyResponseSample(btn) {
    const text = document.getElementById('responseSample').innerText;
    copyToClipboard(text, btn);
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 2800);
}
</script>

<style>
/* =========================================
   API DOCS — FULL WIDTH DARK THEME
   ========================================= */

/* 1. FORCE FULL WIDTH LAYOUT */
.api-docs-page.dashboard-layout {
    display: flex !important;
    width: 100vw !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
    overflow-x: hidden !important;
}

.api-docs-page .main-content {
    flex: 1 !important;
    width: calc(100% - 250px) !important;
    max-width: none !important;
    min-width: 0 !important;
    padding: 32px !important;
    box-sizing: border-box !important;
}

/* 2. CARD BASE */
.api-docs-page .glass-card {
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
.api-hero {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.12), rgba(167, 139, 250, 0.08)) !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 24px !important;
    flex-wrap: wrap !important;
    position: relative !important;
    overflow: hidden !important;
}

.api-hero::before {
    content: '' !important;
    position: absolute !important;
    top: -50% !important;
    right: -10% !important;
    width: 400px !important;
    height: 400px !important;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.15), transparent 70%) !important;
    pointer-events: none !important;
}

.hero-content {
    position: relative !important;
    z-index: 1 !important;
    flex: 1 !important;
    min-width: 280px !important;
}

.hero-tag {
    display: inline-block !important;
    padding: 5px 14px !important;
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    margin-bottom: 14px !important;
}

.api-hero h1 {
    font-size: 28px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 0 10px 0 !important;
    line-height: 1.2 !important;
}

.api-hero p {
    color: #94a3b8 !important;
    font-size: 14px !important;
    line-height: 1.6 !important;
    margin: 0 !important;
    max-width: 700px !important;
}

.hero-badge {
    display: flex !important;
    flex-direction: column !important;
    gap: 8px !important;
    align-items: flex-end !important;
    position: relative !important;
    z-index: 1 !important;
}

.badge-live {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 5px 14px !important;
    background: rgba(52, 211, 153, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(52, 211, 153, 0.3) !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    letter-spacing: 1px !important;
    animation: pulse 2s infinite !important;
}

.badge-count {
    font-size: 12px !important;
    color: #94a3b8 !important;
    font-weight: 600 !important;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

/* 4. CARD HEADER */
.card-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: flex-start !important;
    margin-bottom: 22px !important;
    padding-bottom: 16px !important;
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

.card-subtitle {
    font-size: 13px !important;
    color: #94a3b8 !important;
    margin: 6px 0 0 0 !important;
    line-height: 1.5 !important;
}

.card-subtitle code {
    background: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
    padding: 2px 8px !important;
    border-radius: 5px !important;
    font-family: 'Courier New', monospace !important;
    font-size: 12px !important;
    border: 1px solid rgba(56, 189, 248, 0.2) !important;
}

/* 5. GENERATE TOKEN ROW */
.generate-token-row {
    display: flex !important;
    gap: 12px !important;
    margin-bottom: 24px !important;
    flex-wrap: wrap !important;
}

.input-wrap {
    position: relative !important;
    flex: 1 !important;
    min-width: 240px !important;
}

.input-icon {
    position: absolute !important;
    left: 16px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    font-size: 16px !important;
    pointer-events: none !important;
    opacity: 0.6 !important;
}

.generate-token-row input {
    width: 100% !important;
    padding: 14px 16px 14px 46px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important;
    font-size: 14px !important;
    outline: none !important;
    transition: border-color 0.2s, box-shadow 0.2s !important;
    box-sizing: border-box !important;
}

.generate-token-row input::placeholder {
    color: #64748b !important;
}

.generate-token-row input:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
}

.btn-generate {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    padding: 14px 28px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
    white-space: nowrap !important;
}

.btn-generate:hover:not(:disabled) {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5) !important;
}

.btn-generate:disabled {
    opacity: 0.6 !important;
    cursor: not-allowed !important;
    transform: none !important;
}

/* 6. TOKENS TABLE */
.table-wrapper {
    overflow-x: auto !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: rgba(0, 0, 0, 0.15) !important;
}

.tokens-table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 13px !important;
    min-width: 700px !important;
}

.tokens-table thead th {
    background: #1e293b !important;
    color: #94a3b8 !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    font-size: 11px !important;
    padding: 14px 16px !important;
    text-align: left !important;
    border-bottom: 2px solid rgba(56, 189, 248, 0.2) !important;
    white-space: nowrap !important;
}

.tokens-table thead th.actions-col {
    text-align: center !important;
    width: 80px !important;
}

.tokens-table tbody td {
    padding: 14px 16px !important;
    color: #f1f5f9 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    vertical-align: middle !important;
}

.tokens-table tbody tr {
    transition: background 0.15s !important;
}

.tokens-table tbody tr:hover td {
    background: rgba(56, 189, 248, 0.04) !important;
}

.tokens-table tbody tr:last-child td {
    border-bottom: none !important;
}

.label-cell strong {
    color: #f1f5f9 !important;
    font-weight: 700 !important;
}

.token-code {
    display: inline-block !important;
    padding: 4px 10px !important;
    background: rgba(0, 0, 0, 0.4) !important;
    border: 1px solid rgba(56, 189, 248, 0.2) !important;
    border-radius: 6px !important;
    color: #38bdf8 !important;
    font-family: 'Courier New', monospace !important;
    font-size: 12px !important;
    letter-spacing: 0.5px !important;
}

.calls-badge {
    display: inline-flex !important;
    padding: 4px 10px !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    font-variant-numeric: tabular-nums !important;
}

.calls-badge.active {
    background: rgba(52, 211, 153, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(52, 211, 153, 0.3) !important;
}

.calls-badge.inactive {
    background: rgba(148, 163, 184, 0.1) !important;
    color: #94a3b8 !important;
    border: 1px solid rgba(148, 163, 184, 0.2) !important;
}

.date-cell {
    color: #cbd5e1 !important;
    font-size: 12px !important;
    white-space: nowrap !important;
}

.never-used {
    color: #64748b !important;
    font-style: italic !important;
}

.actions-cell {
    text-align: center !important;
}

.action-icon-btn {
    width: 32px !important;
    height: 32px !important;
    border-radius: 8px !important;
    background: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    cursor: pointer !important;
    font-size: 14px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s ease !important;
}

.action-icon-btn:hover {
    background: rgba(56, 189, 248, 0.25) !important;
    transform: scale(1.05) !important;
}

.action-icon-btn.copied {
    background: rgba(52, 211, 153, 0.2) !important;
    border-color: rgba(52, 211, 153, 0.4) !important;
    color: #34d399 !important;
}

/* 7. EMPTY TOKENS */
.empty-tokens {
    text-align: center !important;
    padding: 60px 24px !important;
}

.empty-tokens .empty-icon {
    font-size: 56px !important;
    margin-bottom: 12px !important;
    opacity: 0.6 !important;
}

.empty-tokens h3 {
    font-size: 18px !important;
    color: #f1f5f9 !important;
    margin: 0 0 8px 0 !important;
    font-weight: 700 !important;
}

.empty-tokens p {
    color: #94a3b8 !important;
    font-size: 14px !important;
    max-width: 480px !important;
    margin: 0 auto !important;
    line-height: 1.6 !important;
}

/* 8. ENDPOINT BLOCKS */
.endpoint-block {
    padding: 20px 0 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
}

.endpoint-block:last-child {
    border-bottom: none !important;
    padding-bottom: 0 !important;
}

.endpoint-block:first-of-type {
    padding-top: 0 !important;
}

.endpoint-header {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    margin-bottom: 10px !important;
    flex-wrap: wrap !important;
}

.method-badge {
    display: inline-flex !important;
    align-items: center !important;
    padding: 5px 12px !important;
    border-radius: 6px !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
}

.method-badge.get {
    background: rgba(52, 211, 153, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(52, 211, 153, 0.3) !important;
}

.method-badge.json {
    background: rgba(167, 139, 250, 0.15) !important;
    color: #a78bfa !important;
    border: 1px solid rgba(167, 139, 250, 0.3) !important;
}

.method-badge.error {
    background: rgba(248, 113, 113, 0.15) !important;
    color: #f87171 !important;
    border: 1px solid rgba(248, 113, 113, 0.3) !important;
}

.endpoint-path {
    font-family: 'Courier New', monospace !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    color: #38bdf8 !important;
    flex: 1 !important;
    min-width: 0 !important;
    word-break: break-all !important;
}

.copy-btn {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 6px 14px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 8px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
}

.copy-btn:hover {
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border-color: rgba(56, 189, 248, 0.4) !important;
}

.copy-btn.copied {
    background: rgba(52, 211, 153, 0.15) !important;
    color: #34d399 !important;
    border-color: rgba(52, 211, 153, 0.4) !important;
}

.endpoint-desc {
    font-size: 13px !important;
    color: #94a3b8 !important;
    margin: 0 0 14px 0 !important;
    line-height: 1.6 !important;
}

/* 9. CODE BLOCKS */
.code-block {
    background: rgba(0, 0, 0, 0.45) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 10px !important;
    padding: 16px 20px !important;
    font-family: 'Courier New', monospace !important;
    font-size: 12.5px !important;
    color: #6ee7b7 !important;
    overflow-x: auto !important;
    margin: 0 !important;
    white-space: pre !important;
    line-height: 1.7 !important;
}

/* 10. PARAMETERS LIST */
.param-list {
    display: flex !important;
    flex-direction: column !important;
    gap: 8px !important;
    margin-bottom: 14px !important;
    padding: 14px 18px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border-radius: 10px !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
}

.param-row {
    display: grid !important;
    grid-template-columns: 100px 70px 1fr !important;
    gap: 12px !important;
    align-items: center !important;
    font-size: 12.5px !important;
}

.param-name {
    font-family: 'Courier New', monospace !important;
    color: #38bdf8 !important;
    font-weight: 700 !important;
}

.param-type {
    display: inline-flex !important;
    padding: 2px 8px !important;
    background: rgba(167, 139, 250, 0.15) !important;
    color: #a78bfa !important;
    border-radius: 5px !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    justify-content: center !important;
}

.param-desc {
    color: #94a3b8 !important;
    font-size: 12.5px !important;
    line-height: 1.5 !important;
}

/* 11. ERROR LIST */
.error-list {
    display: flex !important;
    flex-direction: column !important;
    gap: 8px !important;
    margin-top: 4px !important;
}

.error-row {
    display: flex !important;
    align-items: center !important;
    gap: 14px !important;
    padding: 12px 18px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
    border-radius: 10px !important;
}

.status-code {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 4px 12px !important;
    border-radius: 6px !important;
    font-family: 'Courier New', monospace !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    min-width: 60px !important;
    letter-spacing: 0.5px !important;
}

.status-code.err-401 {
    background: rgba(251, 191, 36, 0.15) !important;
    color: #fbbf24 !important;
    border: 1px solid rgba(251, 191, 36, 0.3) !important;
}

.status-code.err-404 {
    background: rgba(251, 146, 60, 0.15) !important;
    color: #fb923c !important;
    border: 1px solid rgba(251, 146, 60, 0.3) !important;
}

.status-code.err-500 {
    background: rgba(248, 113, 113, 0.15) !important;
    color: #f87171 !important;
    border: 1px solid rgba(248, 113, 113, 0.3) !important;
}

.error-msg {
    color: #cbd5e1 !important;
    font-size: 13px !important;
}

/* 12. TOAST NOTIFICATIONS */
.toast {
    position: fixed !important;
    bottom: 32px !important;
    right: 32px !important;
    padding: 14px 22px !important;
    border-radius: 12px !important;
    font-size: 13.5px !important;
    font-weight: 600 !important;
    color: #f1f5f9 !important;
    background: #1e293b !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5) !important;
    transform: translateY(20px) !important;
    opacity: 0 !important;
    transition: all 0.3s ease !important;
    z-index: 99999 !important;
    max-width: 400px !important;
}

.toast.show {
    transform: translateY(0) !important;
    opacity: 1 !important;
}

.toast.success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.95), rgba(5, 150, 105, 0.95)) !important;
    border-color: rgba(52, 211, 153, 0.5) !important;
    color: #ffffff !important;
}

.toast.error {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(220, 38, 38, 0.95)) !important;
    border-color: rgba(248, 113, 113, 0.5) !important;
    color: #ffffff !important;
}

/* 13. RESPONSIVE */
@media (max-width: 768px) {
    .api-docs-page .main-content { padding: 16px !important; }
    .api-docs-page .glass-card { padding: 20px !important; }
    .api-hero { flex-direction: column !important; align-items: flex-start !important; }
    .api-hero h1 { font-size: 22px !important; }
    .hero-badge { align-items: flex-start !important; flex-direction: row !important; }
    .generate-token-row { flex-direction: column !important; }
    .generate-token-row .btn-generate { width: 100% !important; }
    .param-row { grid-template-columns: 1fr !important; gap: 4px !important; }
    .param-desc { padding-left: 8px !important; }
    .endpoint-path { font-size: 12px !important; }
    .copy-btn { width: 100% !important; justify-content: center !important; margin-top: 4px !important; }
    .toast { left: 16px !important; right: 16px !important; bottom: 16px !important; max-width: none !important; }
}
</style>