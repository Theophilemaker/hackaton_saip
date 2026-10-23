<div class="dashboard-layout admin-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card hero-card">
            <div class="hero-content">
                <span class="hero-tag">Communication</span>
                <h1>📱 SMS Settings</h1>
                <p>Configure SMS provider and message templates for the platform.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin" class="btn-hero-secondary">← Back</a>
        </section>

        <form id="smsForm" onsubmit="event.preventDefault(); saveSmsSettings();">
            <section class="glass-card">
                <div class="card-header">
                    <div><h3>⚙️ Provider Configuration</h3><p class="card-subtitle">Choose and configure your SMS gateway</p></div>
                    <label class="enable-toggle">
                        <input type="checkbox" name="sms_enabled" value="1"
                               <?= ($data['settings']['sms_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                        <span>Enable SMS</span>
                    </label>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>SMS Provider</label>
                        <select name="sms_provider">
                            <?php foreach (['twilio' => 'Twilio', 'africastalking' => "Africa's Talking", 'vonage' => 'Vonage (Nexmo)', 'custom' => 'Custom HTTP API'] as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($data['settings']['sms_provider'] ?? 'twilio') === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sender ID / From</label>
                        <input type="text" name="sms_sender_id" placeholder="SAIP" maxlength="20"
                               value="<?= e($data['settings']['sms_sender_id'] ?? '') ?>">
                        <small style="color:#64748b;font-size:11px;">Up to 11 alphanumeric characters</small>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>API Key / Account SID</label>
                        <input type="text" name="sms_api_key" placeholder="Your API key"
                               value="<?= e($data['settings']['sms_api_key'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>API Secret / Auth Token</label>
                        <input type="password" name="sms_api_secret" placeholder="••••••••"
                               value="<?= e($data['settings']['sms_api_secret'] ?? '') ?>">
                    </div>
                </div>

                <div class="action-row">
                    <button type="submit" class="btn-primary" id="saveSmsBtn">💾 Save Settings</button>
                </div>
            </section>
        </form>

        <section class="glass-card">
            <div class="card-header">
                <div><h3>🧪 Test SMS</h3><p class="card-subtitle">Send a test SMS to verify your configuration</p></div>
            </div>
            <div class="test-row">
                <input type="tel" id="testPhone" placeholder="+250 7XX XXX XXX">
                <button class="btn-primary" onclick="sendTestSms()" id="testSmsBtn">📨 Send Test SMS</button>
            </div>
            <p class="info-note">
                ℹ️ <strong>Note:</strong> SMS sending requires your provider's API to be integrated. 
                Configure API credentials above and use this to test connectivity.
            </p>
        </section>

        <section class="glass-card">
            <div class="card-header">
                <div><h3>📝 SMS Templates</h3><p class="card-subtitle">Customize the SMS messages sent by the system</p></div>
                <span class="badge blue"><?= count($data['templates']) ?> templates</span>
            </div>

            <?php if (empty($data['templates'])): ?>
                <div class="empty-state"><div class="empty-icon">📱</div><h3>No Templates</h3><p>No SMS templates found.</p></div>
            <?php else: ?>
                <div class="templates-list">
                    <?php foreach ($data['templates'] as $t): ?>
                        <div class="template-card">
                            <div class="template-header">
                                <div>
                                    <strong><?= e($t['template_key']) ?></strong>
                                    <span class="badge <?= $t['is_active'] ? 'green' : 'red' ?>"><?= $t['is_active'] ? 'Active' : 'Disabled' ?></span>
                                </div>
                                <button class="btn-secondary-sm" onclick='toggleSmsTemplate(<?= json_encode($t) ?>)'>✏️ Edit</button>
                            </div>
                            <div class="template-body">
                                <div class="sms-preview"><?= e($t['message']) ?></div>
                                <small style="color:#64748b;font-size:11px;"><?= strlen($t['message']) ?> characters · <?= ceil(strlen($t['message']) / 160) ?> SMS segment(s)</small>
                            </div>
                            <div class="template-vars">
                                <small>Variables: <code>{{name}}</code> <code>{{code}}</code> <code>{{order_id}}</code> <code>{{total}}</code></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<!-- SMS TEMPLATE MODAL -->
<div class="modal-overlay" id="smsModal" style="display:none;">
    <div class="modal" style="max-width: 620px;">
        <div class="modal-header">
            <h3>✏️ Edit SMS Template</h3>
            <button class="modal-close" onclick="document.getElementById('smsModal').style.display='none'">✕</button>
        </div>
        <form onsubmit="event.preventDefault(); saveSmsTemplate();">
            <input type="hidden" id="smsTemplateId">
            <div class="form-group">
                <label>Message Text</label>
                <textarea id="smsMessage" rows="5" required maxlength="480" style="font-family: 'Courier New', monospace;"></textarea>
                <small class="char-count"><span id="smsCharCount">0</span>/480 characters (<span id="smsSegments">0</span> SMS)</small>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="document.getElementById('smsModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary">💾 Save Template</button>
            </div>
        </form>
    </div>
</div>

<script>
async function saveSmsSettings() {
    const btn = document.getElementById('saveSmsBtn');
    btn.disabled = true; btn.innerHTML = '⏳ Saving...';
    const fd = new FormData(document.getElementById('smsForm'));
    if (!document.querySelector('[name="sms_enabled"]').checked) fd.set('sms_enabled', '0');

    try {
        const res = await fetch('<?= BASE_URL ?>/admin/saveSms', { method: 'POST', body: fd });
        const data = await res.json();
        showToast(data.message || data.error, data.success ? 'success' : 'error');
    } catch (e) { showToast('Error: ' + e.message, 'error'); }
    finally { btn.disabled = false; btn.innerHTML = '💾 Save Settings'; }
}

async function sendTestSms() {
    const phone = document.getElementById('testPhone').value.trim();
    if (!phone) { showToast('Enter a phone number', 'error'); return; }
    const btn = document.getElementById('testSmsBtn');
    btn.disabled = true; btn.innerHTML = '⏳ Sending...';

    const fd = new FormData(); fd.append('phone', phone);
    try {
        const res = await fetch('<?= BASE_URL ?>/admin/sendTestSms', { method: 'POST', body: fd });
        const data = await res.json();
        showToast(data.message || data.error, data.success ? 'success' : 'error');
    } catch (e) { showToast('Error: ' + e.message, 'error'); }
    finally { btn.disabled = false; btn.innerHTML = '📨 Send Test SMS'; }
}

function toggleSmsTemplate(t) {
    document.getElementById('smsTemplateId').value = t.id;
    document.getElementById('smsMessage').value = t.message;
    updateSmsCharCount();
    document.getElementById('smsModal').style.display = 'flex';
}

document.getElementById('smsMessage')?.addEventListener('input', updateSmsCharCount);

function updateSmsCharCount() {
    const msg = document.getElementById('smsMessage').value;
    document.getElementById('smsCharCount').textContent = msg.length;
    document.getElementById('smsSegments').textContent = Math.ceil(msg.length / 160) || 0;
}

async function saveSmsTemplate() {
    const fd = new FormData();
    fd.append('id', document.getElementById('smsTemplateId').value);
    fd.append('message', document.getElementById('smsMessage').value);

    try {
        const res = await fetch('<?= BASE_URL ?>/admin/updateSmsTemplate', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            showToast('Template saved!', 'success');
            setTimeout(() => location.reload(), 800);
        } else showToast(data.error || 'Failed', 'error');
    } catch (e) { showToast('Error: ' + e.message, 'error'); }
}

function showToast(msg, type) {
    const t = document.createElement('div');
    t.className = 'toast ' + type; t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 2600);
}
</script>

<style>
/* Same base styles as email.php — pasting the differences below */
.admin-page.dashboard-layout { display: flex !important; width: 100vw !important; min-height: 100vh !important; background-color: #0b1120 !important; }
.admin-page .main-content { flex: 1 !important; width: calc(100% - 250px) !important; padding: 32px !important; box-sizing: border-box !important; }
.admin-page .glass-card { background: rgba(30,41,59,0.65) !important; backdrop-filter: blur(16px) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 20px !important; padding: 28px !important; margin-bottom: 24px !important; }

.hero-card { background: linear-gradient(135deg, rgba(34,197,94,0.12), rgba(16,185,129,0.08)) !important; display: flex !important; justify-content: space-between !important; align-items: center !important; gap: 24px !important; flex-wrap: wrap !important; }
.hero-content { flex: 1 !important; min-width: 280px !important; }
.hero-tag { display: inline-block !important; padding: 5px 14px !important; background: rgba(34,197,94,0.15) !important; color: #22c55e !important; border: 1px solid rgba(34,197,94,0.3) !important; border-radius: 999px !important; font-size: 11px !important; font-weight: 700 !important; letter-spacing: 1px !important; text-transform: uppercase !important; margin-bottom: 12px !important; }
.hero-card h1 { font-size: 28px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }
.hero-card p { color: #94a3b8 !important; font-size: 14px !important; margin: 0 !important; max-width: 620px !important; }
.btn-hero-secondary { display: inline-flex !important; align-items: center !important; gap: 8px !important; padding: 13px 24px !important; background: rgba(255,255,255,0.05) !important; color: #f1f5f9 !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; font-weight: 700 !important; text-decoration: none !important; font-size: 13.5px !important; }

.card-header { display: flex !important; justify-content: space-between !important; align-items: center !important; margin-bottom: 20px !important; padding-bottom: 14px !important; border-bottom: 1px solid rgba(255,255,255,0.08) !important; flex-wrap: wrap !important; gap: 12px !important; }
.card-header h3 { font-size: 18px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 !important; }
.card-subtitle { font-size: 12.5px !important; color: #94a3b8 !important; margin: 4px 0 0 0 !important; }

.enable-toggle { display: inline-flex !important; align-items: center !important; gap: 10px !important; padding: 8px 14px !important; background: rgba(0,0,0,0.25) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 10px !important; font-size: 12.5px !important; color: #cbd5e1 !important; font-weight: 700 !important; cursor: pointer !important; }
.enable-toggle input { position: absolute !important; opacity: 0 !important; }
.enable-toggle .toggle-slider { position: relative !important; width: 36px !important; height: 20px !important; background: rgba(255,255,255,0.1) !important; border-radius: 999px !important; transition: 0.2s !important; }
.enable-toggle .toggle-slider::after { content: '' !important; position: absolute !important; top: 2px !important; left: 2px !important; width: 16px !important; height: 16px !important; background: #94a3b8 !important; border-radius: 50% !important; transition: 0.2s !important; }
.enable-toggle input:checked + .toggle-slider { background: rgba(56,189,248,0.4) !important; }
.enable-toggle input:checked + .toggle-slider::after { left: 18px !important; background: #38bdf8 !important; }

.form-row-2 { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 16px !important; margin-bottom: 16px !important; }
.form-group { display: flex !important; flex-direction: column !important; gap: 6px !important; }
.form-group label { font-size: 11px !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.6px !important; }
.form-group input, .form-group select, .form-group textarea { padding: 12px 14px !important; background: rgba(0,0,0,0.35) !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; color: #f1f5f9 !important; font-size: 14px !important; outline: none !important; font-family: inherit !important; box-sizing: border-box !important; width: 100% !important; resize: vertical !important; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #38bdf8 !important; box-shadow: 0 0 0 3px rgba(56,189,248,0.2) !important; }
.form-group select option { background: #1e293b !important; }

.action-row { display: flex !important; justify-content: flex-end !important; margin-top: 12px !important; }
.test-row { display: flex !important; gap: 12px !important; flex-wrap: wrap !important; }
.test-row input { flex: 1 !important; min-width: 240px !important; padding: 12px 14px !important; background: rgba(0,0,0,0.35) !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; color: #f1f5f9 !important; font-size: 14px !important; outline: none !important; box-sizing: border-box !important; }
.test-row input:focus { border-color: #38bdf8 !important; }

.info-note { margin-top: 14px !important; padding: 12px 16px !important; background: rgba(56,189,248,0.08) !important; border-left: 3px solid #38bdf8 !important; border-radius: 8px !important; font-size: 12.5px !important; color: #cbd5e1 !important; line-height: 1.5 !important; }

.btn-primary { display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; padding: 13px 28px !important; background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; border: none !important; border-radius: 10px !important; font-size: 13.5px !important; font-weight: 700 !important; cursor: pointer !important; box-shadow: 0 4px 15px rgba(16,185,129,0.3) !important; }
.btn-primary:disabled { opacity: 0.5 !important; }
.btn-secondary { padding: 12px 22px !important; background: rgba(255,255,255,0.05) !important; color: #cbd5e1 !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; font-weight: 700 !important; cursor: pointer !important; font-size: 13.5px !important; }
.btn-secondary-sm { padding: 8px 14px !important; background: rgba(56,189,248,0.12) !important; color: #38bdf8 !important; border: 1px solid rgba(56,189,248,0.3) !important; border-radius: 8px !important; font-size: 12px !important; font-weight: 700 !important; cursor: pointer !important; }

.badge { display: inline-flex !important; padding: 4px 10px !important; border-radius: 999px !important; font-size: 10.5px !important; font-weight: 700 !important; text-transform: uppercase !important; }
.badge.blue  { background: rgba(56,189,248,0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(56,189,248,0.3) !important; }
.badge.green { background: rgba(52,211,153,0.15) !important; color: #34d399 !important; border: 1px solid rgba(52,211,153,0.3) !important; }
.badge.red   { background: rgba(248,113,113,0.15) !important; color: #f87171 !important; border: 1px solid rgba(248,113,113,0.3) !important; }

.templates-list { display: flex !important; flex-direction: column !important; gap: 16px !important; }
.template-card { padding: 18px 20px !important; background: rgba(0,0,0,0.2) !important; border: 1px solid rgba(255,255,255,0.06) !important; border-radius: 12px !important; }
.template-header { display: flex !important; justify-content: space-between !important; align-items: center !important; margin-bottom: 12px !important; padding-bottom: 12px !important; border-bottom: 1px solid rgba(255,255,255,0.06) !important; flex-wrap: wrap !important; gap: 8px !important; }
.template-header strong { font-size: 14px !important; color: #f1f5f9 !important; margin-right: 10px !important; }
.sms-preview { padding: 12px 16px !important; background: rgba(16,185,129,0.06) !important; border-left: 3px solid #10b981 !important; border-radius: 8px !important; font-size: 13.5px !important; color: #f1f5f9 !important; line-height: 1.5 !important; font-family: 'Courier New', monospace !important; }
.template-vars { margin-top: 10px !important; padding-top: 10px !important; border-top: 1px solid rgba(255,255,255,0.06) !important; }
.template-vars code { background: rgba(56,189,248,0.15) !important; color: #38bdf8 !important; padding: 2px 8px !important; border-radius: 5px !important; font-size: 11px !important; margin-right: 4px !important; }

.char-count { display: block !important; margin-top: 6px !important; font-size: 11px !important; color: #64748b !important; }

.empty-state { text-align: center !important; padding: 48px 24px !important; }
.empty-icon { font-size: 52px !important; margin-bottom: 12px !important; opacity: 0.6 !important; }
.empty-state h3 { font-size: 17px !important; color: #f1f5f9 !important; margin: 0 0 6px 0 !important; }

.modal-overlay { position: fixed !important; inset: 0 !important; background: rgba(0,0,0,0.75) !important; backdrop-filter: blur(4px) !important; display: flex !important; align-items: center !important; justify-content: center !important; z-index: 9999 !important; padding: 20px !important; }
.modal { background: #1e293b !important; border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 20px !important; padding: 28px !important; width: 100% !important; }
.modal-header { display: flex !important; justify-content: space-between !important; align-items: center !important; margin-bottom: 20px !important; padding-bottom: 14px !important; border-bottom: 1px solid rgba(255,255,255,0.08) !important; }
.modal-header h3 { font-size: 18px !important; color: #f1f5f9 !important; margin: 0 !important; }
.modal-close { width: 32px !important; height: 32px !important; background: rgba(255,255,255,0.05) !important; border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 8px !important; color: #cbd5e1 !important; cursor: pointer !important; }
.modal-actions { display: flex !important; justify-content: flex-end !important; gap: 10px !important; margin-top: 20px !important; padding-top: 18px !important; border-top: 1px solid rgba(255,255,255,0.08) !important; }

.toast { position: fixed !important; bottom: 32px !important; right: 32px !important; padding: 14px 22px !important; border-radius: 12px !important; font-size: 13.5px !important; font-weight: 600 !important; color: #fff !important; transform: translateY(20px) !important; opacity: 0 !important; transition: all 0.3s !important; z-index: 99999 !important; }
.toast.show { transform: translateY(0) !important; opacity: 1 !important; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.toast.error { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }

@media (max-width: 768px) {
    .admin-page .main-content { padding: 16px !important; }
    .form-row-2 { grid-template-columns: 1fr !important; }
    .toast { left: 16px !important; right: 16px !important; bottom: 16px !important; }
}
</style>