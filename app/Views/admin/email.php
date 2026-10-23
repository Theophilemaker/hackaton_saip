<div class="dashboard-layout admin-page email-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="main-content">

            <!-- HERO -->
            <section class="glass-card hero-card">
                <div class="hero-content">
                    <span class="hero-tag">📧 Communication</span>
                    <h1>Email Settings</h1>
                    <p>Configure SMTP and manage email templates for your platform.</p>
                </div>
                <a href="<?= BASE_URL ?>/admin" class="btn-hero-secondary">← Back to Admin</a>
            </section>

            <!-- SMTP SETTINGS -->
            <form id="emailForm" onsubmit="return false;">
                <section class="glass-card">
                    <div class="card-head">
                        <div>
                            <h3>⚙️ SMTP Configuration</h3>
                            <p>Outgoing mail server details</p>
                        </div>
                        <label class="enable-toggle">
                            <input type="checkbox" name="email_enabled"
                                   <?= ($data['settings']['email_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">Enable Email</span>
                        </label>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>SMTP Host</label>
                            <input type="text" name="smtp_host" placeholder="smtp.gmail.com"
                                   value="<?= e($data['settings']['smtp_host'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>SMTP Port</label>
                            <input type="number" name="smtp_port" placeholder="587"
                                   value="<?= e($data['settings']['smtp_port'] ?? '587') ?>">
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>SMTP Username</label>
                            <input type="text" name="smtp_user" placeholder="you@example.com"
                                   value="<?= e($data['settings']['smtp_user'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>SMTP Password</label>
                            <input type="password" name="smtp_pass" placeholder="••••••••"
                                   value="<?= e($data['settings']['smtp_pass'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label>Encryption</label>
                            <select name="smtp_secure">
                                <?php $cur = $data['settings']['smtp_secure'] ?? 'tls'; ?>
                                <option value="tls" <?= $cur === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= $cur === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                <option value="none" <?= $cur === 'none' ? 'selected' : '' ?>>None</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>From Name</label>
                            <input type="text" name="smtp_from_name" placeholder="SAIP Platform"
                                   value="<?= e($data['settings']['smtp_from_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>From Email</label>
                            <input type="email" name="smtp_from_email" placeholder="noreply@saip.rw"
                                   value="<?= e($data['settings']['smtp_from_email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="action-row">
                        <button type="button" class="btn-primary" id="btnSaveEmail">💾 Save Settings</button>
                    </div>
                </section>
            </form>

            <!-- TEST EMAIL -->
            <section class="glass-card">
                <div class="card-head">
                    <div>
                        <h3>🧪 Test Email</h3>
                        <p>Send a test email to verify your configuration</p>
                    </div>
                </div>

                <div class="test-row">
                    <input type="email" id="testEmail" placeholder="test@example.com">
                    <button type="button" class="btn-primary" id="btnSendTest">📨 Send Test</button>
                </div>
            </section>

            <!-- EMAIL TEMPLATES -->
            <section class="glass-card">
                <div class="card-head">
                    <div>
                        <h3>📝 Email Templates</h3>
                        <p>Customize the emails sent by the system</p>
                    </div>
                    <span class="badge blue"><?= count($data['templates'] ?? []) ?> templates</span>
                </div>

                <?php if (empty($data['templates'])): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📧</div>
                        <h3>No Templates Found</h3>
                        <p>Email templates will appear here once they're created.</p>
                    </div>
                <?php else: ?>
                    <div class="templates-grid">
                        <?php foreach ($data['templates'] as $t): ?>
                            <div class="template-card">
                                <div class="template-header">
                                    <div class="template-title">
                                        <strong><?= e($t['template_key']) ?></strong>
                                        <span class="badge <?= $t['is_active'] ? 'green' : 'red' ?>">
                                            <?= $t['is_active'] ? 'Active' : 'Disabled' ?>
                                        </span>
                                    </div>
                                    <button type="button" class="btn-edit-template"
                                            data-id="<?= (int)$t['id'] ?>"
                                            data-key="<?= e($t['template_key']) ?>"
                                            data-subject="<?= e($t['subject']) ?>"
                                            data-body="<?= e($t['body_html']) ?>">
                                        ✏️ Edit
                                    </button>
                                </div>

                                <div class="template-body">
                                    <div class="template-field">
                                        <label>Subject</label>
                                        <div class="subject-preview"><?= e($t['subject']) ?></div>
                                    </div>
                                    <div class="template-field">
                                        <label>Body Preview</label>
                                        <div class="body-preview"><?= e(mb_substr(strip_tags($t['body_html']), 0, 140)) ?>…</div>
                                    </div>
                                </div>

                                <div class="template-vars">
                                    <small>Available variables:
                                        <code>{{name}}</code>
                                        <code>{{email}}</code>
                                        <code>{{link}}</code>
                                        <code>{{order_id}}</code>
                                        <code>{{total}}</code>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        </main>
    </div>
</div>

<!-- ═══════════ TEMPLATE EDIT MODAL ═══════════ -->
<div class="modal-overlay" id="templateModal" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <div>
                <h3 id="modalTitle">✏️ Edit Email Template</h3>
                <p id="modalSubtitle">Update subject and body</p>
            </div>
            <button type="button" class="modal-close" id="btnCloseModal" aria-label="Close">✕</button>
        </div>

        <form id="templateForm" onsubmit="return false;">
            <input type="hidden" id="templateId" value="">

            <div class="form-group">
                <label>Subject <span class="req">*</span></label>
                <input type="text" id="templateSubject" required maxlength="255" autocomplete="off">
            </div>

            <div class="form-group">
                <label>Body (HTML) <span class="req">*</span></label>
                <textarea id="templateBody" rows="12" required
                          placeholder="<h2>Hello {{name}}</h2><p>Your message here...</p>"></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="btnCancelModal">Cancel</button>
                <button type="button" class="btn-primary" id="btnSaveTemplate">💾 Save Template</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    'use strict';

    var BASE = '<?= BASE_URL ?>';

    /* ═══════════ DOM REFS ═══════════ */
    var modal        = document.getElementById('templateModal');
    var inputId      = document.getElementById('templateId');
    var inputSubject = document.getElementById('templateSubject');
    var inputBody    = document.getElementById('templateBody');
    var btnSave      = document.getElementById('btnSaveTemplate');
    var btnCancel    = document.getElementById('btnCancelModal');
    var btnClose     = document.getElementById('btnCloseModal');
    var btnSaveEmail = document.getElementById('btnSaveEmail');
    var btnSendTest  = document.getElementById('btnSendTest');

    var saving = false;

    /* ═══════════ MODAL OPEN/CLOSE ═══════════ */
    function openModal(data) {
        inputId.value      = data.id || '';
        inputSubject.value = data.subject || '';
        inputBody.value    = data.body || '';
        document.getElementById('modalSubtitle').textContent = 'Template: ' + (data.key || '');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setTimeout(function () { inputSubject.focus(); }, 100);
    }

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        saving = false;
        btnSave.disabled = false;
        btnSave.innerHTML = '💾 Save Template';
    }

    /* ═══════════ SAVE TEMPLATE ═══════════ */
    function saveTemplate() {
        if (saving) return;

        var id      = inputId.value;
        var subject = inputSubject.value.trim();
        var body    = inputBody.value.trim();

        if (!id) {
            showToast('Missing template ID.', 'error');
            return;
        }
        if (!subject) {
            inputSubject.focus();
            showToast('Subject is required.', 'error');
            return;
        }
        if (!body) {
            inputBody.focus();
            showToast('Body cannot be empty.', 'error');
            return;
        }

        saving = true;
        btnSave.disabled = true;
        btnSave.innerHTML = '⏳ Saving…';

        var payload = new URLSearchParams();
        payload.append('id', id);
        payload.append('subject', subject);
        payload.append('body_html', body);

        fetch(BASE + '/admin/updateEmailTemplate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: payload.toString(),
            credentials: 'same-origin'
        })
        .then(function (res) { return res.text(); })
        .then(function (raw) {
            var data;
            try {
                data = JSON.parse(raw);
            } catch (e) {
                console.error('Non-JSON response:', raw);
                showToast('Server error — check console for details.', 'error');
                saving = false;
                btnSave.disabled = false;
                btnSave.innerHTML = '💾 Save Template';
                return;
            }

            if (data.success) {
                showToast('✅ Template saved', 'success');
                closeModal();
                setTimeout(function () { location.reload(); }, 700);
            } else {
                showToast('⚠️ ' + (data.error || 'Save failed'), 'error');
                saving = false;
                btnSave.disabled = false;
                btnSave.innerHTML = '💾 Save Template';
            }
        })
        .catch(function (err) {
            console.error(err);
            showToast('Network error: ' + err.message, 'error');
            saving = false;
            btnSave.disabled = false;
            btnSave.innerHTML = '💾 Save Template';
        });
    }

    /* ═══════════ SAVE SMTP SETTINGS ═══════════ */
    function saveEmailSettings() {
        var btn = btnSaveEmail;
        btn.disabled = true;
        btn.innerHTML = '⏳ Saving…';

        var form = document.getElementById('emailForm');
        var fd = new FormData(form);
        var enabled = form.querySelector('[name="email_enabled"]');
        fd.set('email_enabled', enabled.checked ? '1' : '0');

        fetch(BASE + '/admin/saveEmail', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.text(); })
        .then(function (raw) {
            var data;
            try { data = JSON.parse(raw); }
            catch (e) {
                showToast('Server error.', 'error');
                return;
            }
            showToast(data.success ? '✅ Settings saved' : '⚠️ ' + (data.error || 'Failed'),
                     data.success ? 'success' : 'error');
        })
        .catch(function (err) {
            showToast('Network error: ' + err.message, 'error');
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = '💾 Save Settings';
        });
    }

    /* ═══════════ SEND TEST EMAIL ═══════════ */
    function sendTestEmail() {
        var input = document.getElementById('testEmail');
        var to = input.value.trim();

        if (!to) { input.focus(); showToast('Enter an email address.', 'error'); return; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(to)) {
            input.focus(); showToast('Invalid email format.', 'error'); return;
        }

        var btn = btnSendTest;
        btn.disabled = true;
        btn.innerHTML = '⏳ Sending…';

        var fd = new FormData();
        fd.append('to', to);

        fetch(BASE + '/admin/sendTestEmail', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.text(); })
        .then(function (raw) {
            var data;
            try { data = JSON.parse(raw); }
            catch (e) {
                showToast('Server error.', 'error');
                return;
            }
            showToast(data.message || data.error || 'Done',
                     data.success ? 'success' : 'error');
        })
        .catch(function (err) {
            showToast('Network error: ' + err.message, 'error');
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = '📨 Send Test';
        });
    }

    /* ═══════════ EVENT BINDINGS (delegated) ═══════════ */

    // Save Settings
    if (btnSaveEmail) {
        btnSaveEmail.addEventListener('click', function (e) {
            e.preventDefault();
            saveEmailSettings();
        });
    }

    // Send Test
    if (btnSendTest) {
        btnSendTest.addEventListener('click', function (e) {
            e.preventDefault();
            sendTestEmail();
        });
    }

    // Save Template
    if (btnSave) {
        btnSave.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            saveTemplate();
        });
    }

    // Cancel & Close
    [btnCancel, btnClose].forEach(function (btn) {
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                closeModal();
            });
        }
    });

    // Click outside modal → close
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
    }

    // ESC key → close
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
            closeModal();
        }
    });

    // Enter key in subject → save
    if (inputSubject) {
        inputSubject.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveTemplate();
            }
        });
    }

    // Edit template buttons
    document.querySelectorAll('.btn-edit-template').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            openModal({
                id:      this.getAttribute('data-id'),
                key:     this.getAttribute('data-key'),
                subject: this.getAttribute('data-subject'),
                body:    this.getAttribute('data-body')
            });
        });
    });

    // Test email Enter key
    var testEmailInput = document.getElementById('testEmail');
    if (testEmailInput) {
        testEmailInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendTestEmail();
            }
        });
    }

    /* ═══════════ TOAST ═══════════ */
    function showToast(msg, type) {
        var t = document.createElement('div');
        t.className = 'toast ' + (type || 'info');
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { t.classList.add('show'); }, 10);
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { t.remove(); }, 300);
        }, 2400);
    }
})();
</script>

<style>
/* ═══════════════════════════════════════════════════════════════
   BASE LAYOUT — full-screen, sidebar-aware
   ═══════════════════════════════════════════════════════════════ */

html, body {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    overflow-x: hidden !important;
}

.email-page.dashboard-layout {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    min-height: 100vh !important;
    background: #0b1120 !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
}

.email-page .main-wrap {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    min-height: 100vh !important;
    box-sizing: border-box !important;
}

.email-page .main-content {
    padding: 28px 32px 60px !important;
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
}

.email-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 28px !important;
    margin-bottom: 20px !important;
    box-sizing: border-box !important;
    width: 100% !important;
}

/* HERO */
.email-page .hero-card {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.12), rgba(245, 158, 11, 0.06)) !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 24px !important;
    flex-wrap: wrap !important;
}

.email-page .hero-content { flex: 1 !important; min-width: 260px !important; }

.email-page .hero-tag {
    display: inline-block !important;
    padding: 5px 14px !important;
    background: rgba(251, 191, 36, 0.15) !important;
    color: #fbbf24 !important;
    border: 1px solid rgba(251, 191, 36, 0.3) !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    margin-bottom: 12px !important;
}

.email-page .hero-card h1 {
    font-size: 26px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 0 6px 0 !important;
    line-height: 1.2 !important;
}

.email-page .hero-card p {
    color: #94a3b8 !important;
    font-size: 14px !important;
    margin: 0 !important;
    max-width: 620px !important;
    line-height: 1.55 !important;
}

.email-page .btn-hero-secondary {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 12px 22px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #f1f5f9 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    transition: all 0.2s !important;
}
.email-page .btn-hero-secondary:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    transform: translateY(-2px) !important;
}

/* CARD HEAD */
.email-page .card-head {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 20px !important;
    padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
}

.email-page .card-head h3 {
    font-size: 18px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
    margin: 0 0 4px 0 !important;
}

.email-page .card-head p {
    font-size: 12.5px !important;
    color: #94a3b8 !important;
    margin: 0 !important;
}

.email-page .badge {
    display: inline-flex !important;
    align-items: center !important;
    padding: 5px 12px !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}
.email-page .badge.blue  { background: rgba(56, 189, 248, 0.15) !important; color: #38bdf8 !important; border: 1px solid rgba(56, 189, 248, 0.3) !important; }
.email-page .badge.green { background: rgba(52, 211, 153, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(52, 211, 153, 0.3) !important; }
.email-page .badge.red   { background: rgba(248, 113, 113, 0.15) !important; color: #f87171 !important; border: 1px solid rgba(248, 113, 113, 0.3) !important; }

/* ENABLE TOGGLE */
.email-page .enable-toggle {
    display: inline-flex !important;
    align-items: center !important;
    gap: 10px !important;
    padding: 8px 14px !important;
    background: rgba(0, 0, 0, 0.25) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 10px !important;
    font-size: 12.5px !important;
    color: #cbd5e1 !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    user-select: none !important;
}

.email-page .enable-toggle input {
    position: absolute !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

.email-page .toggle-slider {
    position: relative !important;
    width: 38px !important;
    height: 22px !important;
    background: rgba(255, 255, 255, 0.1) !important;
    border-radius: 999px !important;
    transition: 0.2s !important;
    flex-shrink: 0 !important;
}

.email-page .toggle-slider::after {
    content: '' !important;
    position: absolute !important;
    top: 2px !important;
    left: 2px !important;
    width: 18px !important;
    height: 18px !important;
    background: #94a3b8 !important;
    border-radius: 50% !important;
    transition: 0.2s !important;
}

.email-page .enable-toggle input:checked + .toggle-slider {
    background: rgba(56, 189, 248, 0.4) !important;
}

.email-page .enable-toggle input:checked + .toggle-slider::after {
    left: 18px !important;
    background: #38bdf8 !important;
}

/* FORM GRID */
.email-page .form-row-2 {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 16px !important;
    margin-bottom: 16px !important;
}

.email-page .form-row-3 {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 16px !important;
    margin-bottom: 16px !important;
}

.email-page .form-group {
    display: flex !important;
    flex-direction: column !important;
    gap: 6px !important;
    min-width: 0 !important;
}

.email-page .form-group label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
}

.email-page .form-group input,
.email-page .form-group select,
.email-page .form-group textarea {
    width: 100% !important;
    padding: 12px 14px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important;
    font-size: 14px !important;
    font-family: inherit !important;
    outline: none !important;
    box-sizing: border-box !important;
    transition: border-color 0.2s, box-shadow 0.2s !important;
}

.email-page .form-group textarea {
    resize: vertical !important;
    min-height: 200px !important;
    font-family: 'Courier New', Consolas, monospace !important;
    font-size: 13px !important;
    line-height: 1.6 !important;
}

.email-page .form-group input:focus,
.email-page .form-group select:focus,
.email-page .form-group textarea:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
}

.email-page .form-group select {
    cursor: pointer !important;
    -webkit-appearance: none !important;
    appearance: none !important;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 16px !important;
    padding-right: 40px !important;
}

.email-page .form-group select option {
    background: #1e293b !important;
    color: #f1f5f9 !important;
}

/* ACTION ROW */
.email-page .action-row {
    display: flex !important;
    justify-content: flex-end !important;
    margin-top: 12px !important;
}

/* TEST ROW */
.email-page .test-row {
    display: flex !important;
    gap: 12px !important;
    flex-wrap: wrap !important;
}

.email-page .test-row input {
    flex: 1 !important;
    min-width: 240px !important;
    padding: 13px 16px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important;
    font-size: 14px !important;
    font-family: inherit !important;
    outline: none !important;
    box-sizing: border-box !important;
}

.email-page .test-row input:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
}

/* BUTTONS */
.email-page .btn-primary {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    padding: 13px 26px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
    transition: all 0.2s !important;
    white-space: nowrap !important;
}

.email-page .btn-primary:hover:not(:disabled) {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5) !important;
}

.email-page .btn-primary:disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
    transform: none !important;
}

.email-page .btn-secondary {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    padding: 13px 24px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
    white-space: nowrap !important;
}

.email-page .btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    color: #f1f5f9 !important;
}

/* TEMPLATES GRID */
.email-page .templates-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)) !important;
    gap: 16px !important;
}

.email-page .template-card {
    padding: 20px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 14px !important;
    transition: all 0.25s !important;
    box-sizing: border-box !important;
}

.email-page .template-card:hover {
    background: rgba(0, 0, 0, 0.3) !important;
    border-color: rgba(56, 189, 248, 0.3) !important;
    transform: translateY(-2px) !important;
}

.email-page .template-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: flex-start !important;
    gap: 12px !important;
    margin-bottom: 14px !important;
    padding-bottom: 14px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    flex-wrap: wrap !important;
}

.email-page .template-title {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    flex-wrap: wrap !important;
    min-width: 0 !important;
    flex: 1 !important;
}

.email-page .template-title strong {
    font-size: 14.5px !important;
    color: #f1f5f9 !important;
    font-weight: 700 !important;
    word-break: break-word !important;
}

.email-page .btn-edit-template {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 8px 14px !important;
    background: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    border-radius: 8px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important;
}

.email-page .btn-edit-template:hover {
    background: rgba(56, 189, 248, 0.25) !important;
    transform: translateY(-1px) !important;
}

.email-page .template-body {
    display: flex !important;
    flex-direction: column !important;
    gap: 12px !important;
    margin-bottom: 12px !important;
}

.email-page .template-field label {
    display: block !important;
    font-size: 10.5px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
    margin-bottom: 4px !important;
}

.email-page .subject-preview {
    font-size: 13.5px !important;
    color: #f1f5f9 !important;
    font-weight: 600 !important;
    line-height: 1.4 !important;
}

.email-page .body-preview {
    font-size: 12.5px !important;
    color: #cbd5e1 !important;
    line-height: 1.5 !important;
    font-family: 'Courier New', Consolas, monospace !important;
    background: rgba(0, 0, 0, 0.3) !important;
    padding: 10px 12px !important;
    border-radius: 8px !important;
    word-break: break-word !important;
}

.email-page .template-vars {
    padding-top: 12px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
}

.email-page .template-vars small {
    font-size: 11px !important;
    color: #64748b !important;
    line-height: 1.6 !important;
    display: block !important;
}

.email-page .template-vars code {
    display: inline-block !important;
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    padding: 2px 8px !important;
    border-radius: 5px !important;
    font-size: 10.5px !important;
    margin: 2px 2px 2px 0 !important;
    font-family: 'Courier New', monospace !important;
}

/* EMPTY STATE */
.email-page .empty-state {
    text-align: center !important;
    padding: 48px 24px !important;
}

.email-page .empty-icon {
    font-size: 52px !important;
    margin-bottom: 12px !important;
    opacity: 0.6 !important;
}

.email-page .empty-state h3 {
    font-size: 17px !important;
    color: #f1f5f9 !important;
    margin: 0 0 6px 0 !important;
    font-weight: 700 !important;
}

.email-page .empty-state p {
    color: #94a3b8 !important;
    font-size: 13px !important;
    margin: 0 !important;
}

/* ═══════════════════════════════════════════════════════════════
   MODAL — CRITICAL FIX
   ═══════════════════════════════════════════════════════════════ */

.modal-overlay {
    position: fixed !important;
    inset: 0 !important;
    background: rgba(0, 0, 0, 0.75) !important;
    backdrop-filter: blur(4px) !important;
    -webkit-backdrop-filter: blur(4px) !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 99999 !important;
    padding: 20px !important;
    box-sizing: border-box !important;
}

.modal-overlay[style*="flex"] {
    display: flex !important;
}

.modal {
    background: #1e293b !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 20px !important;
    padding: 28px !important;
    width: 100% !important;
    max-width: 720px !important;
    max-height: calc(100vh - 40px) !important;
    overflow-y: auto !important;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6) !important;
    animation: modalIn 0.2s ease-out !important;
    box-sizing: border-box !important;
    position: relative !important;
}

@keyframes modalIn {
    from { opacity: 0; transform: scale(0.95); }
    to   { opacity: 1; transform: scale(1); }
}

.modal-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: flex-start !important;
    gap: 12px !important;
    margin-bottom: 22px !important;
    padding-bottom: 16px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.modal-header h3 {
    font-size: 18px !important;
    color: #f1f5f9 !important;
    margin: 0 0 4px 0 !important;
    font-weight: 700 !important;
}

.modal-header p {
    font-size: 12.5px !important;
    color: #94a3b8 !important;
    margin: 0 !important;
}

.modal-close {
    width: 36px !important;
    height: 36px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 10px !important;
    color: #cbd5e1 !important;
    font-size: 14px !important;
    cursor: pointer !important;
    font-family: inherit !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    flex-shrink: 0 !important;
    transition: all 0.2s !important;
}

.modal-close:hover {
    background: rgba(239, 68, 68, 0.2) !important;
    color: #f87171 !important;
    border-color: rgba(239, 68, 68, 0.4) !important;
}

.modal .form-group {
    display: flex !important;
    flex-direction: column !important;
    gap: 6px !important;
    margin-bottom: 18px !important;
}

.modal .form-group label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
}

.modal .req { color: #ef4444 !important; }

.modal .form-group input,
.modal .form-group textarea {
    width: 100% !important;
    padding: 13px 16px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important;
    font-size: 14px !important;
    font-family: inherit !important;
    outline: none !important;
    box-sizing: border-box !important;
    transition: border-color 0.2s, box-shadow 0.2s !important;
}

.modal .form-group textarea {
    font-family: 'Courier New', Consolas, monospace !important;
    font-size: 13px !important;
    line-height: 1.6 !important;
    resize: vertical !important;
    min-height: 200px !important;
    max-height: 400px !important;
}

.modal .form-group input:focus,
.modal .form-group textarea:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
}

.modal-actions {
    display: flex !important;
    justify-content: flex-end !important;
    gap: 10px !important;
    padding-top: 18px !important;
    margin-top: 4px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    flex-wrap: wrap !important;
    position: sticky !important;
    bottom: 0 !important;
    background: #1e293b !important;
}

.modal-actions .btn-primary,
.modal-actions .btn-secondary {
    padding: 13px 26px !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    border-radius: 10px !important;
    cursor: pointer !important;
    font-family: inherit !important;
    white-space: nowrap !important;
    transition: all 0.2s !important;
    position: relative !important;
    z-index: 10 !important;
}

.modal-actions .btn-primary {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important;
    border: none !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
}

.modal-actions .btn-primary:hover:not(:disabled) {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5) !important;
}

.modal-actions .btn-primary:disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
    transform: none !important;
}

.modal-actions .btn-secondary {
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
}

.modal-actions .btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    color: #f1f5f9 !important;
}

/* ═══════════════════════════════════════════════════════════════
   TOAST
   ═══════════════════════════════════════════════════════════════ */

.toast {
    position: fixed !important;
    bottom: 28px !important;
    right: 28px !important;
    padding: 14px 22px !important;
    border-radius: 12px !important;
    font-size: 13.5px !important;
    font-weight: 600 !important;
    color: #ffffff !important;
    transform: translateY(20px) !important;
    opacity: 0 !important;
    transition: all 0.3s ease !important;
    z-index: 999999 !important;
    max-width: 400px !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5) !important;
    pointer-events: none !important;
}

.toast.show { transform: translateY(0) !important; opacity: 1 !important; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.toast.error   { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }
.toast.info    { background: linear-gradient(135deg, #38bdf8, #0284c7) !important; }

/* ═══════════════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════════════ */

@media (max-width: 1024px) {
    .email-page .form-row-3 { grid-template-columns: 1fr 1fr !important; }
}

@media (max-width: 768px) {
    .email-page .main-wrap {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding-top: 64px !important;
    }
    .email-page .main-content { padding: 16px !important; }
    .email-page .glass-card { padding: 20px !important; border-radius: 16px !important; }
    .email-page .hero-card { flex-direction: column !important; align-items: flex-start !important; }
    .email-page .hero-card h1 { font-size: 20px !important; }
    .email-page .btn-hero-secondary { width: 100% !important; justify-content: center !important; }
    .email-page .form-row-2,
    .email-page .form-row-3 { grid-template-columns: 1fr !important; }
    .email-page .templates-grid { grid-template-columns: 1fr !important; }
    .email-page .action-row { justify-content: stretch !important; }
    .email-page .btn-primary { width: 100% !important; }
    .email-page .test-row { flex-direction: column !important; }
    .email-page .test-row input { min-width: 0 !important; width: 100% !important; }
    .modal { padding: 20px !important; max-width: 100% !important; }
    .modal-actions { flex-direction: column !important; }
    .modal-actions .btn-primary,
    .modal-actions .btn-secondary { width: 100% !important; }
    .toast { left: 16px !important; right: 16px !important; bottom: 16px !important; max-width: none !important; }
}
</style>