<div class="dashboard-layout api-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="api-wrapper">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="api-content">

            <!-- HERO -->
            <section class="glass-card api-hero">
                <div class="hero-left">
                    <span class="hero-tag">🔌 Developer Resources</span>
                    <h1>SAIP Public API</h1>
                    <p>REST endpoints for developers, researchers, and partners. Build apps on top of Rwanda's agricultural data.</p>
                </div>
                <div class="hero-right">
                    <span class="badge-live">● LIVE</span>
                    <a href="<?= BASE_URL ?>/admin/apiKeys" class="btn-ghost">🔑 My Tokens</a>
                </div>
            </section>

            <!-- QUICK START -->
            <section class="glass-card">
                <div class="card-head">
                    <h3>🚀 Quick Start</h3>
                    <p>Get your first API call working in 60 seconds</p>
                </div>

                <div class="steps">
                    <div class="step">
                        <div class="step-num">1</div>
                        <div class="step-body">
                            <strong>Get an API token</strong>
                            <p>Generate a token from the admin panel. Store it securely.</p>
                            <a href="<?= BASE_URL ?>/admin/apiKeys" class="inline-link">→ Generate Token</a>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-num">2</div>
                        <div class="step-body">
                            <strong>Make your first call</strong>
                            <p>Test with cURL:</p>
                            <div class="code-wrap">
                                <pre class="code-block">curl "<?= BASE_URL ?>/api/districts?token=YOUR_TOKEN"</pre>
                                <button class="copy-btn" onclick="copyCode(this)">📋</button>
                            </div>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-num">3</div>
                        <div class="step-body">
                            <strong>Parse the JSON response</strong>
                            <p>Every endpoint returns the same envelope:</p>
                            <div class="code-wrap">
                                <pre class="code-block">{
  "success": true,
  "data": [ ... ],
  "timestamp": "2026-10-02T15:30:00+02:00"
}</pre>
                                <button class="copy-btn" onclick="copyCode(this)">📋</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- AUTHENTICATION -->
            <section class="glass-card">
                <div class="card-head">
                    <h3>🔐 Authentication</h3>
                    <p>All requests require a valid API token</p>
                </div>

                <h4 class="sub-head">Method 1 — Query Parameter</h4>
                <div class="code-wrap">
                    <pre class="code-block">GET <?= BASE_URL ?>/api/districts?token=YOUR_TOKEN</pre>
                    <button class="copy-btn" onclick="copyCode(this)">📋</button>
                </div>

                <h4 class="sub-head">Method 2 — HTTP Header <span class="rec">recommended</span></h4>
                <div class="code-wrap">
                    <pre class="code-block">GET <?= BASE_URL ?>/api/districts
X-API-Token: YOUR_TOKEN</pre>
                    <button class="copy-btn" onclick="copyCode(this)">📋</button>
                </div>

                <div class="warn-box">
                    ⚠️ <strong>Keep your token secret.</strong> Never expose it in client-side code or public repos.
                </div>
            </section>

            <!-- ENDPOINTS -->
            <section class="glass-card">
                <div class="card-head">
                    <h3>📡 Endpoints</h3>
                    <p>All 5 public endpoints</p>
                </div>

                <div class="endpoints">

                    <div class="endpoint">
                        <div class="ep-head">
                            <span class="method get">GET</span>
                            <code class="ep-path">/api/weather</code>
                        </div>
                        <p class="ep-desc">Recent weather data for a district (temperature, rainfall, humidity).</p>
                        <div class="params">
                            <div class="param-row">
                                <code class="p-name">district_id</code>
                                <span class="p-type">integer</span>
                                <span class="p-req">required</span>
                                <span class="p-desc">District ID (1–30)</span>
                            </div>
                        </div>
                        <div class="code-wrap">
                            <pre class="code-block">GET <?= BASE_URL ?>/api/weather?district_id=1&token=YOUR_TOKEN</pre>
                            <button class="copy-btn" onclick="copyCode(this)">📋</button>
                        </div>
                    </div>

                    <div class="endpoint">
                        <div class="ep-head">
                            <span class="method get">GET</span>
                            <code class="ep-path">/api/crops</code>
                        </div>
                        <p class="ep-desc">Full catalog of crops with scientific names, categories, and growing durations.</p>
                        <div class="code-wrap">
                            <pre class="code-block">GET <?= BASE_URL ?>/api/crops?token=YOUR_TOKEN</pre>
                            <button class="copy-btn" onclick="copyCode(this)">📋</button>
                        </div>
                    </div>

                    <div class="endpoint">
                        <div class="ep-head">
                            <span class="method get">GET</span>
                            <code class="ep-path">/api/markets</code>
                        </div>
                        <p class="ep-desc">Active marketplace listings — products, prices, units, districts, and crop types.</p>
                        <div class="code-wrap">
                            <pre class="code-block">GET <?= BASE_URL ?>/api/markets?token=YOUR_TOKEN</pre>
                            <button class="copy-btn" onclick="copyCode(this)">📋</button>
                        </div>
                    </div>

                    <div class="endpoint">
                        <div class="ep-head">
                            <span class="method get">GET</span>
                            <code class="ep-path">/api/production</code>
                        </div>
                        <p class="ep-desc">Aggregated crop production by district and year — perfect for analytics dashboards.</p>
                        <div class="code-wrap">
                            <pre class="code-block">GET <?= BASE_URL ?>/api/production?token=YOUR_TOKEN</pre>
                            <button class="copy-btn" onclick="copyCode(this)">📋</button>
                        </div>
                    </div>

                    <div class="endpoint">
                        <div class="ep-head">
                            <span class="method get">GET</span>
                            <code class="ep-path">/api/districts</code>
                        </div>
                        <p class="ep-desc">All 30 Rwandan districts with coordinates — for building GIS maps.</p>
                        <div class="code-wrap">
                            <pre class="code-block">GET <?= BASE_URL ?>/api/districts?token=YOUR_TOKEN</pre>
                            <button class="copy-btn" onclick="copyCode(this)">📋</button>
                        </div>
                    </div>

                </div>
            </section>

            <!-- ERROR RESPONSES -->
            <section class="glass-card">
                <div class="card-head">
                    <h3>⚠️ Error Responses</h3>
                    <p>How to interpret errors</p>
                </div>

                <div class="errors">
                    <div class="err-row">
                        <span class="code err-401">401</span>
                        <div>
                            <strong>Unauthorized</strong>
                            <p>Missing, invalid, or expired token.</p>
                        </div>
                    </div>
                    <div class="err-row">
                        <span class="code err-404">404</span>
                        <div>
                            <strong>Not Found</strong>
                            <p>Endpoint doesn't exist, or resource was not found.</p>
                        </div>
                    </div>
                    <div class="err-row">
                        <span class="code err-429">429</span>
                        <div>
                            <strong>Rate Limited</strong>
                            <p>Too many requests — slow down or upgrade your plan.</p>
                        </div>
                    </div>
                    <div class="err-row">
                        <span class="code err-500">500</span>
                        <div>
                            <strong>Server Error</strong>
                            <p>Something went wrong on our end. Try again later.</p>
                        </div>
                    </div>
                </div>

                <div class="code-wrap" style="margin-top:16px;">
                    <pre class="code-block">{
  "success": false,
  "error": "Invalid or expired token"
}</pre>
                    <button class="copy-btn" onclick="copyCode(this)">📋</button>
                </div>
            </section>

            <!-- CODE EXAMPLES -->
            <section class="glass-card">
                <div class="card-head">
                    <h3>💻 Code Examples</h3>
                    <p>Copy-paste into your project</p>
                </div>

                <div class="tabs">
                    <button class="tab active" onclick="switchTab(event, 'curl')">cURL</button>
                    <button class="tab" onclick="switchTab(event, 'js')">JavaScript</button>
                    <button class="tab" onclick="switchTab(event, 'php')">PHP</button>
                    <button class="tab" onclick="switchTab(event, 'py')">Python</button>
                </div>

                <div class="tab-pane active" id="pane-curl">
                    <div class="code-wrap">
                        <pre class="code-block">curl -H "X-API-Token: YOUR_TOKEN" \
  "<?= BASE_URL ?>/api/districts"</pre>
                        <button class="copy-btn" onclick="copyCode(this)">📋</button>
                    </div>
                </div>

                <div class="tab-pane" id="pane-js">
                    <div class="code-wrap">
                        <pre class="code-block">fetch('<?= BASE_URL ?>/api/districts', {
  headers: { 'X-API-Token': 'YOUR_TOKEN' }
})
.then(res => res.json())
.then(json => console.log(json.data));</pre>
                        <button class="copy-btn" onclick="copyCode(this)">📋</button>
                    </div>
                </div>

                <div class="tab-pane" id="pane-php">
                    <div class="code-wrap">
                        <pre class="code-block">$ch = curl_init('<?= BASE_URL ?>/api/districts');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-Token: YOUR_TOKEN']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = json_decode(curl_exec($ch), true);
print_r($data['data']);</pre>
                        <button class="copy-btn" onclick="copyCode(this)">📋</button>
                    </div>
                </div>

                <div class="tab-pane" id="pane-py">
                    <div class="code-wrap">
                        <pre class="code-block">import requests

r = requests.get(
    '<?= BASE_URL ?>/api/districts',
    headers={'X-API-Token': 'YOUR_TOKEN'}
)
print(r.json()['data'])</pre>
                        <button class="copy-btn" onclick="copyCode(this)">📋</button>
                    </div>
                </div>
            </section>

            <!-- TEST CONSOLE -->
            <section class="glass-card">
                <div class="card-head">
                    <h3>🧪 API Test Console</h3>
                    <p>Try endpoints live (requires your token)</p>
                </div>

                <div class="test-form">
                    <input type="text" id="testToken" placeholder="Paste your API token..." autocomplete="off">
                    <select id="testEndpoint">
                        <option value="/api/districts">GET /api/districts</option>
                        <option value="/api/crops">GET /api/crops</option>
                        <option value="/api/markets">GET /api/markets</option>
                        <option value="/api/production">GET /api/production</option>
                        <option value="/api/weather?district_id=1">GET /api/weather?district_id=1</option>
                    </select>
                    <button class="btn-primary" onclick="runTest()">▶ Send</button>
                </div>

                <div class="test-output" id="testOutput" style="display:none;">
                    <div class="test-meta" id="testMeta"></div>
                    <pre class="code-block" id="testResult"></pre>
                </div>
            </section>

        </main>
    </div>
</div>

<script>
function copyCode(btn) {
    const pre = btn.parentElement.querySelector('pre');
    if (!pre) return;
    const text = pre.textContent;
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.textContent;
        btn.textContent = '✅';
        setTimeout(() => { btn.textContent = orig; }, 1400);
    });
}

function switchTab(e, name) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    e.currentTarget.classList.add('active');
    document.getElementById('pane-' + name).classList.add('active');
}

async function runTest() {
    const token = document.getElementById('testToken').value.trim();
    const path  = document.getElementById('testEndpoint').value;
    const out   = document.getElementById('testOutput');
    const meta  = document.getElementById('testMeta');
    const pre   = document.getElementById('testResult');

    if (!token) { alert('Please enter your API token first.'); return; }

    const url = '<?= BASE_URL ?>' + path + (path.includes('?') ? '&' : '?') + 'token=' + encodeURIComponent(token);

    out.style.display = 'block';
    meta.innerHTML = '<span class="status-dot"></span> Calling ' + path + '...';
    pre.textContent = '';

    const start = performance.now();
    try {
        const res = await fetch(url);
        const ms  = Math.round(performance.now() - start);
        const json = await res.json();
        const cls = res.ok ? 'ok' : 'err';
        meta.innerHTML = `<span class="status-dot ${cls}"></span> HTTP ${res.status} · ${ms}ms · ${path}`;
        pre.textContent = JSON.stringify(json, null, 2);
    } catch (err) {
        meta.innerHTML = '<span class="status-dot err"></span> ❌ ' + err.message;
        pre.textContent = String(err);
    }
}
</script>

<style>
/* ═══════════════════════════════════════════════════
   API DOCS — FITS PERFECTLY IN DASHBOARD
   ═══════════════════════════════════════════════════ */

.api-page.dashboard-layout {
    display: block !important;
    width: 100% !important;
    min-height: 100vh !important;
    background: #0b1120 !important;
    overflow-x: hidden !important;
}

/* Wrapper sits to the right of the fixed 260px sidebar */
.api-page .api-wrapper {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    min-height: 100vh !important;
    display: flex !important;
    flex-direction: column !important;
    box-sizing: border-box !important;
}

/* Main content — no fixed height, natural scroll */
.api-page .api-content {
    flex: 1 !important;
    padding: 28px 32px 60px !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    min-width: 0 !important;
}

/* ── CARDS ── */
.api-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 18px !important;
    padding: 26px !important;
    margin-bottom: 20px !important;
    box-sizing: border-box !important;
    width: 100% !important;
    max-width: 100% !important;
    overflow: hidden !important;
}

/* ── HERO ── */
.api-page .api-hero {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(167, 139, 250, 0.08)) !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 24px !important;
    flex-wrap: wrap !important;
}

.api-page .hero-left { flex: 1 1 400px !important; min-width: 0 !important; }
.api-page .hero-right { display: flex !important; gap: 10px !important; align-items: center !important; flex-wrap: wrap !important; }

.api-page .hero-tag {
    display: inline-block !important;
    padding: 5px 14px !important;
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 0.8px !important;
    text-transform: uppercase !important;
    margin-bottom: 10px !important;
}

.api-page .api-hero h1 {
    font-size: 26px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 0 8px 0 !important;
    line-height: 1.2 !important;
}

.api-page .api-hero p {
    color: #94a3b8 !important;
    font-size: 13.5px !important;
    margin: 0 !important;
    line-height: 1.55 !important;
    max-width: 620px !important;
}

.api-page .badge-live {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 6px 14px !important;
    background: rgba(52, 211, 153, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(52, 211, 153, 0.3) !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    letter-spacing: 0.8px !important;
    animation: apiPulse 2s infinite !important;
}

@keyframes apiPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }

.api-page .btn-ghost {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 10px 18px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #f1f5f9 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    transition: all 0.2s !important;
}
.api-page .btn-ghost:hover { background: rgba(255, 255, 255, 0.12) !important; transform: translateY(-2px) !important; }

/* ── CARD HEADERS ── */
.api-page .card-head {
    margin-bottom: 18px !important;
    padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.api-page .card-head h3 {
    font-size: 17px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    margin: 0 0 4px 0 !important;
}
.api-page .card-head p {
    font-size: 12.5px !important;
    color: #94a3b8 !important;
    margin: 0 !important;
}

.api-page .sub-head {
    font-size: 13px !important;
    color: #cbd5e1 !important;
    margin: 16px 0 8px 0 !important;
    font-weight: 700 !important;
}

.api-page .rec {
    display: inline-block !important;
    margin-left: 8px !important;
    padding: 2px 8px !important;
    background: rgba(16, 185, 129, 0.15) !important;
    color: #34d399 !important;
    font-size: 10px !important;
    border-radius: 999px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    font-weight: 700 !important;
}

/* ── STEPS ── */
.api-page .steps {
    display: flex !important;
    flex-direction: column !important;
    gap: 18px !important;
}
.api-page .step { display: flex !important; gap: 16px !important; align-items: flex-start !important; }
.api-page .step-num {
    width: 36px !important;
    height: 36px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #10b981, #38bdf8) !important;
    color: #fff !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-weight: 800 !important;
    font-size: 14px !important;
    flex-shrink: 0 !important;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3) !important;
}
.api-page .step-body { flex: 1 !important; min-width: 0 !important; }
.api-page .step-body strong {
    display: block !important;
    font-size: 14px !important;
    color: #f1f5f9 !important;
    margin-bottom: 4px !important;
}
.api-page .step-body p {
    color: #94a3b8 !important;
    font-size: 12.5px !important;
    margin: 0 0 10px 0 !important;
    line-height: 1.5 !important;
}
.api-page .inline-link {
    color: #38bdf8 !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
}
.api-page .inline-link:hover { text-decoration: underline !important; }

/* ── CODE BLOCKS ── */
.api-page .code-wrap {
    position: relative !important;
    margin-bottom: 10px !important;
    width: 100% !important;
}
.api-page .code-wrap:last-child { margin-bottom: 0 !important; }

.api-page .code-block {
    background: rgba(0, 0, 0, 0.45) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 12px !important;
    padding: 14px 18px !important;
    font-family: 'Courier New', Consolas, monospace !important;
    font-size: 12.5px !important;
    color: #6ee7b7 !important;
    overflow-x: auto !important;
    margin: 0 !important;
    white-space: pre !important;
    line-height: 1.65 !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

.api-page .copy-btn {
    position: absolute !important;
    top: 8px !important;
    right: 8px !important;
    padding: 5px 10px !important;
    background: rgba(56, 189, 248, 0.18) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.35) !important;
    border-radius: 8px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    transition: all 0.15s !important;
    z-index: 2 !important;
}
.api-page .copy-btn:hover { background: rgba(56, 189, 248, 0.28) !important; }

/* ── WARNING ── */
.api-page .warn-box {
    padding: 12px 16px !important;
    background: rgba(251, 191, 36, 0.08) !important;
    border-left: 4px solid #fbbf24 !important;
    border-radius: 10px !important;
    font-size: 12.5px !important;
    color: #fcd34d !important;
    line-height: 1.55 !important;
    margin-top: 14px !important;
}
.api-page .warn-box strong { color: #fef3c7 !important; }

/* ── ENDPOINTS ── */
.api-page .endpoints {
    display: flex !important;
    flex-direction: column !important;
    gap: 14px !important;
}
.api-page .endpoint {
    padding: 18px 20px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 14px !important;
    transition: all 0.2s !important;
    box-sizing: border-box !important;
}
.api-page .endpoint:hover {
    border-color: rgba(56, 189, 248, 0.3) !important;
    background: rgba(0, 0, 0, 0.3) !important;
}

.api-page .ep-head {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    margin-bottom: 8px !important;
    flex-wrap: wrap !important;
}

.api-page .method {
    display: inline-flex !important;
    padding: 4px 10px !important;
    border-radius: 6px !important;
    font-size: 10.5px !important;
    font-weight: 800 !important;
    letter-spacing: 0.8px !important;
}
.api-page .method.get {
    background: rgba(52, 211, 153, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(52, 211, 153, 0.3) !important;
}

.api-page .ep-path {
    font-family: 'Courier New', Consolas, monospace !important;
    font-size: 13.5px !important;
    color: #38bdf8 !important;
    font-weight: 700 !important;
    word-break: break-all !important;
}

.api-page .ep-desc {
    font-size: 13px !important;
    color: #94a3b8 !important;
    line-height: 1.5 !important;
    margin: 0 0 12px 0 !important;
}

.api-page .params {
    padding: 12px 14px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border-radius: 10px !important;
    margin-bottom: 12px !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
}
.api-page .param-row {
    display: grid !important;
    grid-template-columns: 110px 70px 70px 1fr !important;
    gap: 10px !important;
    align-items: center !important;
    font-size: 12px !important;
}
.api-page .p-name { font-family: 'Courier New', monospace !important; color: #38bdf8 !important; font-weight: 700 !important; }
.api-page .p-type { color: #a78bfa !important; font-weight: 700 !important; font-size: 10.5px !important; text-transform: uppercase !important; }
.api-page .p-req { color: #f87171 !important; font-weight: 800 !important; font-size: 10px !important; text-transform: uppercase !important; letter-spacing: 0.4px !important; }
.api-page .p-desc { color: #cbd5e1 !important; }

/* ── ERROR TABLE ── */
.api-page .errors {
    display: flex !important;
    flex-direction: column !important;
    gap: 8px !important;
}
.api-page .err-row {
    display: flex !important;
    gap: 14px !important;
    align-items: center !important;
    padding: 12px 16px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 10px !important;
}
.api-page .code {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 52px !important;
    height: 30px !important;
    border-radius: 8px !important;
    font-family: 'Courier New', monospace !important;
    font-size: 12.5px !important;
    font-weight: 800 !important;
    flex-shrink: 0 !important;
}
.api-page .code.err-401 { background: rgba(251, 191, 36, 0.15) !important; color: #fbbf24 !important; border: 1px solid rgba(251, 191, 36, 0.3) !important; }
.api-page .code.err-404 { background: rgba(251, 146, 60, 0.15) !important; color: #fb923c !important; border: 1px solid rgba(251, 146, 60, 0.3) !important; }
.api-page .code.err-429 { background: rgba(167, 139, 250, 0.15) !important; color: #a78bfa !important; border: 1px solid rgba(167, 139, 250, 0.3) !important; }
.api-page .code.err-500 { background: rgba(248, 113, 113, 0.15) !important; color: #f87171 !important; border: 1px solid rgba(248, 113, 113, 0.3) !important; }
.api-page .err-row strong { display: block !important; font-size: 13px !important; color: #f1f5f9 !important; margin-bottom: 2px !important; }
.api-page .err-row p { font-size: 12px !important; color: #94a3b8 !important; margin: 0 !important; }

/* ── TABS ── */
.api-page .tabs {
    display: flex !important;
    gap: 4px !important;
    padding: 4px !important;
    background: rgba(0, 0, 0, 0.25) !important;
    border-radius: 12px !important;
    margin-bottom: 14px !important;
    overflow-x: auto !important;
}
.api-page .tab {
    flex: 1 !important;
    min-width: 80px !important;
    padding: 9px 14px !important;
    background: transparent !important;
    color: #94a3b8 !important;
    border: none !important;
    border-radius: 9px !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
    white-space: nowrap !important;
}
.api-page .tab:hover { color: #cbd5e1 !important; background: rgba(255, 255, 255, 0.04) !important; }
.api-page .tab.active {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(167, 139, 250, 0.15)) !important;
    color: #38bdf8 !important;
}
.api-page .tab-pane { display: none !important; }
.api-page .tab-pane.active { display: block !important; }

/* ── TEST CONSOLE ── */
.api-page .test-form {
    display: grid !important;
    grid-template-columns: 2fr 1.5fr auto !important;
    gap: 10px !important;
    align-items: center !important;
}
.api-page .test-form input,
.api-page .test-form select {
    padding: 12px 16px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important;
    font-size: 13px !important;
    outline: none !important;
    font-family: inherit !important;
    box-sizing: border-box !important;
    width: 100% !important;
}
.api-page .test-form input:focus,
.api-page .test-form select:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
}
.api-page .test-form select option { background: #1e293b !important; }

.api-page .btn-primary {
    padding: 12px 24px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    white-space: nowrap !important;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3) !important;
    transition: all 0.2s !important;
}
.api-page .btn-primary:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5) !important; }

.api-page .test-output { margin-top: 14px !important; }
.api-page .test-meta {
    font-size: 12px !important;
    color: #cbd5e1 !important;
    margin-bottom: 10px !important;
    padding: 10px 14px !important;
    background: rgba(0, 0, 0, 0.25) !important;
    border-radius: 8px !important;
    font-family: 'Courier New', monospace !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}
.api-page .status-dot {
    display: inline-block !important;
    width: 8px !important;
    height: 8px !important;
    border-radius: 50% !important;
    background: #64748b !important;
    flex-shrink: 0 !important;
}
.api-page .status-dot.ok { background: #34d399 !important; box-shadow: 0 0 8px rgba(52, 211, 153, 0.6) !important; }
.api-page .status-dot.err { background: #f87171 !important; box-shadow: 0 0 8px rgba(248, 113, 113, 0.6) !important; }

/* ═══════════ RESPONSIVE ═══════════ */
@media (max-width: 1024px) {
    .api-page .test-form { grid-template-columns: 1fr 1fr !important; }
    .api-page .test-form .btn-primary { grid-column: 1 / -1 !important; }
}

@media (max-width: 768px) {
    .api-page .api-wrapper {
        margin-left: 0 !important;
        width: 100% !important;
        padding-top: 64px !important;
    }
    .api-page .api-content { padding: 16px !important; }
    .api-page .glass-card { padding: 20px !important; border-radius: 16px !important; }
    .api-page .api-hero { flex-direction: column !important; align-items: flex-start !important; }
    .api-page .api-hero h1 { font-size: 20px !important; }
    .api-page .hero-right { width: 100% !important; justify-content: flex-start !important; }
    .api-page .param-row { grid-template-columns: 1fr !important; gap: 4px !important; }
    .api-page .p-desc { padding-left: 8px !important; }
    .api-page .tabs { overflow-x: auto !important; }
    .api-page .tab { font-size: 12px !important; padding: 8px 12px !important; }
    .api-page .test-form { grid-template-columns: 1fr !important; }
    .api-page .code-block { font-size: 11.5px !important; padding: 12px 14px !important; }
}
</style>