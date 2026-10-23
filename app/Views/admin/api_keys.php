<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card api-keys-card">
            <div class="card-header">
                <div>
                    <h3>🔑 API Keys</h3>
                    <p class="card-sub">Manage keys that grant programmatic access to SAIP.</p>
                </div>

                <form id="genKeyForm" method="POST" action="<?= BASE_URL ?>/admin/generateApiKey" class="gen-form">
                    <?= \App\Core\Security::csrfField() ?>
                    <input type="text" name="key_name" id="keyName" placeholder="e.g. Mobile app v2" required maxlength="80">
                    <button type="submit" class="btn-primary" id="genKeyBtn">
                        ✨ Generate
                    </button>
                </form>
            </div>

            <!-- ═══════════ NEW KEY PANEL (revealed after success) ═══════════ -->
            <div id="newKeyPanel" class="new-key-panel" hidden>
                <div class="nkw-header">
                    <span class="nkw-check">✅</span>
                    <div>
                        <h4>API Key Generated</h4>
                        <p>Copy this key now — you <strong>won't</strong> see it again after leaving this page.</p>
                    </div>
                </div>

                <div class="nkw-input-row">
                    <input type="text" id="newKeyValue" readonly
                           onclick="this.select();"
                           class="nkw-input">
                    <button type="button" class="btn-copy" onclick="copyNewKey()" id="copyBtn">
                        📋 Copy
                    </button>
                </div>

                <p id="copyStatus" class="nkw-status"></p>
            </div>

            <!-- ═══════════ STATUS MESSAGE (errors / info) ═══════════ -->
            <div id="formStatus" class="form-status" hidden></div>

            <!-- ═══════════ TABLE ═══════════ -->
            <div class="table-wrap">
                <table class="data-table" id="apiKeysTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Owner</th>
                            <th>Key</th>
                            <th>Last Used</th>
                            <th>Active</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($data['keys'])): ?>
                        <tr>
                            <td colspan="5" class="empty-row">
                                <div class="empty-state">
                                    <div class="empty-emoji">🔑</div>
                                    <p>No API keys yet</p>
                                    <small>Generate your first key above to get started.</small>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['keys'] as $k): ?>
                            <tr>
                                <td>
                                    <strong><?= e($k['key_name']) ?></strong>
                                </td>
                                <td class="muted">
                                    <?= e(trim(($k['first_name'] ?? '') . ' ' . ($k['last_name'] ?? ''))) ?: '—' ?>
                                </td>
                                <td>
                                    <code class="key-code"><?= e(substr($k['api_key'], 0, 16)) ?>…</code>
                                </td>
                                <td class="muted">
                                    <?= $k['last_used_at']
                                        ? e(date('M d, Y', strtotime($k['last_used_at'])))
                                        : '<span class="never">Never</span>' ?>
                                </td>
                                <td>
                                    <span class="status-pill <?= $k['is_active'] ? 'paid' : 'cancelled' ?>">
                                        <?= $k['is_active'] ? 'Active' : 'Revoked' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<style>
/* ═══════════════════════════════════════════════════════════
   API KEYS — Card polish
   ═══════════════════════════════════════════════════════════ */
.api-keys-card {
    padding: 24px;
    border-radius: 16px;
    background: rgba(30, 41, 59, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.api-keys-card .card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}
.api-keys-card h3 {
    font-size: 18px;
    font-weight: 800;
    color: #f1f5f9;
    margin: 0 0 4px;
    letter-spacing: -0.3px;
}
.api-keys-card .card-sub {
    font-size: 13px;
    color: #94a3b8;
    margin: 0;
}

/* ─── Generate form ─── */
.gen-form {
    display: inline-flex;
    gap: 8px;
    align-items: center;
}
.gen-form input {
    padding: 11px 16px;
    min-width: 220px;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    color: #f1f5f9;
    font-family: inherit;
    font-size: 13.5px;
    outline: none;
    transition: all 0.2s;
}
.gen-form input:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}
.gen-form input::placeholder {
    color: #64748b;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 22px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    transition: all 0.2s;
    white-space: nowrap;
}
.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.55);
}
.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* ─── New key panel ─── */
.new-key-panel {
    margin-bottom: 22px;
    padding: 20px 22px;
    background: rgba(16, 185, 129, 0.06);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 14px;
    animation: slideDown 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.nkw-header {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 16px;
}
.nkw-check {
    font-size: 24px;
    line-height: 1;
}
.nkw-header h4 {
    font-size: 15px;
    font-weight: 800;
    color: #34d399;
    margin: 0 0 4px;
}
.nkw-header p {
    font-size: 13px;
    color: #94a3b8;
    margin: 0;
    line-height: 1.5;
}
.nkw-header strong {
    color: #f1f5f9;
}
.nkw-input-row {
    display: flex;
    gap: 8px;
    align-items: center;
}
.nkw-input {
    flex: 1;
    padding: 13px 16px;
    background: rgba(0, 0, 0, 0.45);
    border: 1px solid rgba(16, 185, 129, 0.35);
    border-radius: 10px;
    color: #a7f3d0;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    letter-spacing: 0.5px;
    outline: none;
    cursor: text;
}
.btn-copy {
    padding: 13px 22px;
    background: #10b981;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.btn-copy:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
}
.btn-copy.copied {
    background: #059669;
}
.nkw-status {
    margin: 10px 0 0;
    font-size: 12.5px;
    font-weight: 600;
    color: #34d399;
    min-height: 16px;
}

/* ─── Error / status message ─── */
.form-status {
    margin-bottom: 20px;
    padding: 14px 18px;
    border-radius: 12px;
    font-size: 13.5px;
    line-height: 1.5;
    animation: slideDown 0.3s ease-out;
}
.form-status.error {
    background: rgba(239, 68, 68, 0.1);
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, 0.35);
}
.form-status.success {
    background: rgba(16, 185, 129, 0.1);
    color: #86efac;
    border: 1px solid rgba(16, 185, 129, 0.35);
}

/* ─── Table ─── */
.table-wrap {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
}
.data-table thead tr {
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.data-table th {
    text-align: left;
    padding: 14px 16px;
    color: #94a3b8;
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    background: rgba(0, 0, 0, 0.15);
}
.data-table td {
    padding: 16px;
    color: #e2e8f0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    vertical-align: middle;
}
.data-table tbody tr {
    transition: background 0.15s;
}
.data-table tbody tr:hover {
    background: rgba(255, 255, 255, 0.02);
}
.data-table tbody tr:last-child td {
    border-bottom: none;
}
.data-table td.muted {
    color: #94a3b8;
    font-size: 13px;
}
.data-table td strong {
    color: #f1f5f9;
    font-weight: 700;
}

.key-code {
    display: inline-block;
    padding: 4px 10px;
    background: rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: #a7f3d0;
    letter-spacing: 0.5px;
}
.never {
    color: #64748b;
    font-style: italic;
    font-size: 12px;
}

/* ─── Status pill ─── */
.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.status-pill.paid {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.status-pill.cancelled {
    background: rgba(239, 68, 68, 0.12);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

/* ─── Empty state ─── */
.empty-row td {
    padding: 0 !important;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
}
.empty-emoji {
    font-size: 52px;
    opacity: 0.4;
    margin-bottom: 14px;
}
.empty-state p {
    font-size: 15px;
    color: #cbd5e1;
    margin: 0 0 6px;
    font-weight: 600;
}
.empty-state small {
    font-size: 12.5px;
    color: #94a3b8;
}

/* ─── Responsive ─── */
@media (max-width: 720px) {
    .api-keys-card { padding: 18px; }
    .api-keys-card .card-header {
        flex-direction: column;
        align-items: stretch;
    }
    .gen-form {
        flex-direction: column;
        width: 100%;
    }
    .gen-form input,
    .gen-form button {
        width: 100%;
    }
    .nkw-input-row {
        flex-direction: column;
    }
    .btn-copy {
        width: 100%;
    }
    .data-table th,
    .data-table td {
        padding: 12px 10px;
        font-size: 12.5px;
    }
}

/* Hidden by default — safety net */
[hidden] { display: none !important; }
</style>

<script>
(function () {
    'use strict';

    var form       = document.getElementById('genKeyForm');
    var btn        = document.getElementById('genKeyBtn');
    var panel      = document.getElementById('newKeyPanel');
    var keyInput   = document.getElementById('newKeyValue');
    var copyBtn    = document.getElementById('copyBtn');
    var copyStatus = document.getElementById('copyStatus');
    var formStatus = document.getElementById('formStatus');

    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        var name = (document.getElementById('keyName').value || '').trim();
        if (!name) { showStatus('Please enter a name for the key.', 'error'); return; }

        /* Reset UI */
        var releaseBtn = SAIP.loading.button(btn, 'Generating…');
        SAIP.loading.start('Generating API key…');
        hideStatus();
        panel.hidden = true;

        try {
            var res  = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            var text = await res.text();
            var data;
            try { data = JSON.parse(text); }
            catch (parseErr) {
                throw new Error('Server returned non-JSON response (HTTP ' + res.status + '). Preview: ' + text.slice(0, 160));
            }

            if (!data.success) throw new Error(data.error || data.message || 'Generation failed');

            /* Show the key */
            keyInput.value = data.api_key || '';
            panel.hidden = false;
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            prependRow({ name: data.name || name, key: data.api_key || '' });

            document.getElementById('keyName').value = '';
            copyStatus.textContent = '';

            keyInput.focus();
            keyInput.select();

        } catch (err) {
            showStatus(err.message, 'error');
        } finally {
            releaseBtn();
            SAIP.loading.stop();
        }
    });

    window.copyNewKey = function () {
        var v = keyInput.value;
        if (!v) return;

        function success() {
            copyBtn.classList.add('copied');
            copyBtn.textContent = '✅ Copied';
            copyStatus.style.color = '#34d399';
            copyStatus.textContent = 'Key copied to clipboard.';
            setTimeout(function () {
                copyBtn.classList.remove('copied');
                copyBtn.innerHTML = '📋 Copy';
            }, 2200);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(v).then(success).catch(function () {
                keyInput.select(); document.execCommand('copy'); success();
            });
        } else {
            keyInput.select(); document.execCommand('copy'); success();
        }
    };

    function showStatus(msg, type) {
        formStatus.hidden = false;
        formStatus.className = 'form-status ' + (type || 'error');
        formStatus.textContent = (type === 'error' ? '⚠️ ' : '✅ ') + msg;
    }
    function hideStatus() {
        formStatus.hidden = true;
        formStatus.textContent = '';
    }
    function prependRow(data) {
        var tbody = document.querySelector('#apiKeysTable tbody');
        if (!tbody) return;
        var emptyRow = tbody.querySelector('.empty-row');
        if (emptyRow) emptyRow.remove();

        var tr = document.createElement('tr');
        tr.style.background = 'rgba(16, 185, 129, 0.06)';
        tr.innerHTML =
            '<td><strong>' + esc(data.name) + '</strong></td>' +
            '<td class="muted">You</td>' +
            '<td><code class="key-code">' + esc((data.key || '').slice(0, 16)) + '…</code></td>' +
            '<td class="muted"><span class="never">Never</span></td>' +
            '<td><span class="status-pill paid">Active</span></td>';
        tbody.insertBefore(tr, tbody.firstChild);
        tr.style.opacity = '0';
        requestAnimationFrame(function () { tr.style.transition = 'opacity 0.4s'; tr.style.opacity = '1'; });
    }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
})();
</script>