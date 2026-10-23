<?php
$permMap = [];
foreach ($data['mapping'] ?? [] as $m) {
    $permMap[$m['role_id']][$m['permission_id']] = true;
}

$grouped = [];
foreach ($data['permissions'] ?? [] as $p) {
    $grouped[$p['module'] ?? 'general'][] = $p;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="dashboard-layout admin-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="main-content">

            <section class="glass-card">
                <div class="card-header">
                    <div>
                        <h3>🔐 Roles & Permissions</h3>
                        <p class="card-subtitle">Click any toggle to grant/revoke · Add/edit/delete roles below</p>
                    </div>
                    <div class="header-actions">
                        <button type="button" class="btn-primary-sm" id="btnAddRole">➕ Add Role</button>
                        <a href="<?= BASE_URL ?>/admin" class="btn-secondary-sm">← Back</a>
                    </div>
                </div>

                <!-- Role cards -->
                <div class="role-cards">
                    <?php foreach ($data['roles'] ?? [] as $r): ?>
                        <div class="role-card">
                            <div class="role-card-header">
                                <strong><?= e($r['name']) ?></strong>
                                <div class="role-card-actions">
                                    <button type="button" class="icon-sm btn-edit-role"
                                            data-id="<?= (int)$r['id'] ?>"
                                            data-name="<?= e($r['name']) ?>"
                                            data-desc="<?= e($r['description'] ?? '') ?>"
                                            title="Edit">✏️</button>
                                    <?php if ((int)$r['id'] !== 7): ?>
                                        <button type="button" class="icon-sm danger btn-delete-role"
                                                data-id="<?= (int)$r['id'] ?>"
                                                data-name="<?= e($r['name']) ?>"
                                                title="Delete">🗑️</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="role-card-meta">
                                <span>👥 <?= number_format((int)($r['user_count'] ?? 0)) ?> users</span>
                                <span>🔑 <?= number_format((int)($r['perm_count'] ?? 0)) ?> perms</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Permission matrix -->
                <h4 class="matrix-heading">📋 Permission Matrix</h4>
                <div class="table-wrapper">
                    <table class="permissions-table">
                        <thead>
                            <tr>
                                <th class="perm-header">Permission</th>
                                <?php foreach ($data['roles'] ?? [] as $r): ?>
                                    <th class="role-header"><?= e($r['name']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grouped as $module => $perms): ?>
                                <tr class="module-row">
                                    <td colspan="<?= count($data['roles'] ?? []) + 1 ?>">
                                        📦 <?= strtoupper(e($module)) ?>
                                    </td>
                                </tr>
                                <?php foreach ($perms as $p): ?>
                                    <tr>
                                        <td class="perm-name">
                                            <strong><?= e($p['permission_name']) ?></strong>
                                            <?php if (!empty($p['description'])): ?>
                                                <small><?= e($p['description']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <?php foreach ($data['roles'] ?? [] as $r): 
                                            $checked = !empty($permMap[$r['id']][$p['id']]);
                                        ?>
                                            <td class="check-cell">
                                                <label class="perm-checkbox">
                                                    <input type="checkbox"
                                                           <?= $checked ? 'checked' : '' ?>
                                                           data-role="<?= (int)$r['id'] ?>"
                                                           data-perm="<?= (int)$p['id'] ?>"
                                                           onchange="togglePerm(this)">
                                                    <span class="check-mark"></span>
                                                </label>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="roleModal" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <h3 id="roleModalTitle">➕ Add Role</h3>
            <button type="button" class="modal-close" id="btnCloseModal">✕</button>
        </div>
        <form id="roleForm" onsubmit="return false;">
            <input type="hidden" id="roleId" value="">
            <div class="form-group">
                <label>Role Name <span style="color:#f87171">*</span></label>
                <input type="text" id="roleName" required maxlength="100" autocomplete="off">
            </div>
            <div class="form-group" style="margin-top:14px;">
                <label>Description</label>
                <input type="text" id="roleDesc" maxlength="255" autocomplete="off">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="btnCancelModal">Cancel</button>
                <button type="button" class="btn-primary" id="btnSaveRole">💾 Save</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>

<script>
(function() {
    'use strict';

    const BASE = '<?= BASE_URL ?>';
    const modal       = document.getElementById('roleModal');
    const modalTitle  = document.getElementById('roleModalTitle');
    const inputId     = document.getElementById('roleId');
    const inputName   = document.getElementById('roleName');
    const inputDesc   = document.getElementById('roleDesc');
    const btnSave     = document.getElementById('btnSaveRole');
    const btnCancel   = document.getElementById('btnCancelModal');
    const btnClose    = document.getElementById('btnCloseModal');
    const btnAddRole  = document.getElementById('btnAddRole');

    let editing = false;
    let saving  = false;

    function openModal(mode, data) {
        if (mode === 'add') {
            editing = false;
            modalTitle.textContent = '➕ Add Role';
            inputId.value = '';
            inputName.value = '';
            inputDesc.value = '';
        } else {
            editing = true;
            modalTitle.textContent = '✏️ Edit Role';
            inputId.value = data.id || '';
            inputName.value = data.name || '';
            inputDesc.value = data.desc || '';
        }
        modal.style.display = 'flex';
        setTimeout(() => inputName.focus(), 100);
    }

    function closeModal() {
        modal.style.display = 'none';
        saving = false;
        btnSave.disabled = false;
        btnSave.textContent = '💾 Save';
    }

    async function saveRole() {
        if (saving) return;

        const name = inputName.value.trim();
        const desc = inputDesc.value.trim();
        const id   = inputId.value;

        if (!name) {
            inputName.focus();
            showToast('Role name is required.', 'error');
            return;
        }

        saving = true;
        btnSave.disabled = true;
        btnSave.textContent = '⏳ Saving...';

        const payload = new URLSearchParams();
        payload.append('name', name);
        payload.append('description', desc);
        if (editing && id) payload.append('id', id);

        const url = editing ? BASE + '/admin/editRole' : BASE + '/admin/addRole';

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload.toString(),
                credentials: 'same-origin'
            });

            const raw = await res.text();
            let data;
            try {
                data = JSON.parse(raw);
            } catch (e) {
                console.error('Non-JSON response:', raw);
                alert('Server error — check the browser console for details.');
                saving = false;
                btnSave.disabled = false;
                btnSave.textContent = '💾 Save';
                return;
            }

            if (data.success) {
                showToast(editing ? 'Role updated.' : 'Role created.', 'success');
                closeModal();
                setTimeout(() => location.reload(), 700);
            } else {
                showToast(data.error || 'Save failed.', 'error');
                saving = false;
                btnSave.disabled = false;
                btnSave.textContent = '💾 Save';
            }
        } catch (err) {
            alert('Save failed: ' + err.message);
            saving = false;
            btnSave.disabled = false;
            btnSave.textContent = '💾 Save';
        }
    }

    async function deleteRole(id, name) {
        if (!confirm('Delete role "' + name + '"? This cannot be undone.')) return;
        try {
            const res = await fetch(BASE + '/admin/deleteRole/' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            const raw = await res.text();
            let data;
            try { data = JSON.parse(raw); }
            catch (e) { alert('Delete failed: server error.'); return; }

            if (data.success) {
                showToast('Role deleted.', 'success');
                setTimeout(() => location.reload(), 600);
            } else {
                showToast(data.error || 'Delete failed.', 'error');
            }
        } catch (err) {
            showToast('Delete failed: ' + err.message, 'error');
        }
    }

    async function togglePerm(checkbox) {
        const roleId = checkbox.getAttribute('data-role');
        const permId = checkbox.getAttribute('data-perm');
        const wasChecked = checkbox.checked;

        checkbox.disabled = true;

        try {
            const payload = new URLSearchParams({
                role_id: roleId,
                permission_id: permId
            });

            const res = await fetch(BASE + '/admin/togglePermission', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload.toString(),
                credentials: 'same-origin'
            });

            const raw = await res.text();
            let data;
            try { data = JSON.parse(raw); }
            catch (e) {
                checkbox.checked = !wasChecked;
                showToast('Server error.', 'error');
                return;
            }

            if (data.action === 'added') showToast('✅ Granted', 'success');
            else if (data.action === 'removed') showToast('🚫 Revoked', 'warning');
            else if (data.error) {
                checkbox.checked = !wasChecked;
                showToast(data.error, 'error');
            }
        } catch (err) {
            checkbox.checked = !wasChecked;
            showToast('Failed: ' + err.message, 'error');
        } finally {
            checkbox.disabled = false;
        }
    }

    function showToast(msg, type) {
        const t = document.createElement('div');
        t.className = 'toast ' + type;
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => t.classList.add('show'), 10);
        setTimeout(() => {
            t.classList.remove('show');
            setTimeout(() => t.remove(), 300);
        }, 2400);
    }

    if (btnAddRole) btnAddRole.addEventListener('click', e => { e.preventDefault(); openModal('add'); });
    [btnCancel, btnClose].forEach(btn => {
        if (btn) btn.addEventListener('click', e => { e.preventDefault(); closeModal(); });
    });
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal && modal.style.display === 'flex') closeModal();
    });
    if (btnSave) btnSave.addEventListener('click', e => { e.preventDefault(); saveRole(); });
    [inputName, inputDesc].forEach(inp => {
        if (inp) inp.addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); saveRole(); }
        });
    });

    document.querySelectorAll('.btn-edit-role').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal('edit', {
                id: this.getAttribute('data-id'),
                name: this.getAttribute('data-name'),
                desc: this.getAttribute('data-desc')
            });
        });
    });

    document.querySelectorAll('.btn-delete-role').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            deleteRole(this.getAttribute('data-id'), this.getAttribute('data-name'));
        });
    });

    window.togglePerm = togglePerm;
})();
</script>

<style>
/* ═══════════════════════════════════════════════════════════
   RESET — kill every default margin / padding that could
   leave visible gaps
   ═══════════════════════════════════════════════════════════ */
html, body {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    overflow-x: hidden !important;
}

/* ═══════════════════════════════════════════════════════════
   OUTER LAYOUT — fills the screen next to the fixed sidebar
   ═══════════════════════════════════════════════════════════ */
.admin-page.dashboard-layout {
    display: block !important;
    width: 100% !important;
    min-height: 100vh !important;
    background: #0b1120 !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
}

/* Wrapper offset by sidebar width */
.admin-page .main-wrap {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    min-height: 100vh !important;
    box-sizing: border-box !important;
    display: flex !important;
    flex-direction: column !important;
}

/* Content grows to fill remaining height */
.admin-page .main-content {
    flex: 1 1 auto !important;
    padding: 28px 32px 40px !important;
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
    display: block !important;
}

/* ═══════════════════════════════════════════════════════════
   CARDS — stretch to full width
   ═══════════════════════════════════════════════════════════ */
.admin-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 28px !important;
    margin: 0 0 20px 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

.admin-page .glass-card:last-child { margin-bottom: 0 !important; }

/* ═══════════════════════════════════════════════════════════
   HEADER
   ═══════════════════════════════════════════════════════════ */
.card-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin: 0 0 20px 0 !important;
    padding: 0 0 16px 0 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
    width: 100% !important;
}
.card-header h3 { font-size: 18px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 !important; }
.card-subtitle { font-size: 12.5px !important; color: #94a3b8 !important; margin: 4px 0 0 0 !important; }
.header-actions { display: flex !important; gap: 10px !important; flex-wrap: wrap !important; }

.btn-primary-sm { padding: 10px 20px !important; background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; border: none !important; border-radius: 10px !important; font-size: 13px !important; font-weight: 700 !important; cursor: pointer !important; box-shadow: 0 4px 15px rgba(16,185,129,0.3) !important; font-family: inherit !important; }
.btn-primary-sm:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 20px rgba(16,185,129,0.5) !important; }

.btn-secondary-sm { padding: 10px 18px !important; background: rgba(255,255,255,0.05) !important; color: #cbd5e1 !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; font-size: 12.5px !important; font-weight: 700 !important; text-decoration: none !important; }
.btn-secondary-sm:hover { background: rgba(255,255,255,0.1) !important; }

/* ═══════════════════════════════════════════════════════════
   ROLE CARDS GRID — fills entire width
   ═══════════════════════════════════════════════════════════ */
.role-cards {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)) !important;
    gap: 14px !important;
    margin: 0 0 26px 0 !important;
    width: 100% !important;
}

.role-card {
    padding: 16px 18px !important;
    background: rgba(56, 189, 248, 0.06) !important;
    border: 1px solid rgba(56, 189, 248, 0.2) !important;
    border-radius: 12px !important;
    box-sizing: border-box !important;
    width: 100% !important;
}

.role-card-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 8px !important;
    gap: 8px !important;
}
.role-card-header strong { font-size: 14px !important; color: #38bdf8 !important; font-weight: 700 !important; }
.role-card-actions { display: flex !important; gap: 4px !important; }

.icon-sm {
    width: 28px !important;
    height: 28px !important;
    border-radius: 6px !important;
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    color: #cbd5e1 !important;
    font-size: 12px !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-family: inherit !important;
    padding: 0 !important;
}
.icon-sm:hover { background: rgba(56,189,248,0.2) !important; }
.icon-sm.danger:hover { background: rgba(239,68,68,0.2) !important; color: #f87171 !important; }

.role-card-meta {
    display: flex !important;
    gap: 12px !important;
    font-size: 11.5px !important;
    color: #94a3b8 !important;
}

/* ═══════════════════════════════════════════════════════════
   PERMISSION MATRIX
   ═══════════════════════════════════════════════════════════ */
.matrix-heading { font-size: 15px !important; color: #f1f5f9 !important; font-weight: 700 !important; margin: 0 0 14px 0 !important; }

.table-wrapper {
    overflow-x: auto !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255,255,255,0.08) !important;
    background: rgba(0,0,0,0.15) !important;
    width: 100% !important;
    max-width: 100% !important;
}

.permissions-table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 13px !important;
    min-width: 100% !important;
}

.permissions-table thead th {
    background: #1e293b !important;
    color: #94a3b8 !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    font-size: 11px !important;
    padding: 14px 16px !important;
    text-align: center !important;
    border-bottom: 2px solid rgba(56,189,248,0.25) !important;
    white-space: nowrap !important;
}
.permissions-table thead th.perm-header {
    text-align: left !important;
    width: 40% !important;
    min-width: 260px !important;
}
.permissions-table thead th.role-header { min-width: 110px !important; }

.permissions-table tbody td {
    padding: 12px 16px !important;
    color: #f1f5f9 !important;
    border-bottom: 1px solid rgba(255,255,255,0.04) !important;
    vertical-align: middle !important;
}
.permissions-table tbody tr:hover td { background: rgba(56,189,248,0.04) !important; }

.module-row td {
    background: rgba(167,139,250,0.08) !important;
    color: #a78bfa !important;
    font-weight: 800 !important;
    font-size: 11px !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    padding: 10px 16px !important;
}

.perm-name { text-align: left !important; }
.perm-name strong { display: block !important; font-size: 13px !important; }
.perm-name small { display: block !important; color: #94a3b8 !important; font-size: 11px !important; margin-top: 2px !important; }

.check-cell { text-align: center !important; width: 110px !important; }

.perm-checkbox {
    position: relative !important;
    display: inline-block !important;
    cursor: pointer !important;
    width: 34px !important;
    height: 20px !important;
    vertical-align: middle !important;
}
.perm-checkbox input { opacity: 0 !important; width: 0 !important; height: 0 !important; position: absolute !important; }
.check-mark {
    position: absolute !important;
    inset: 0 !important;
    background: rgba(255,255,255,0.08) !important;
    border-radius: 999px !important;
    transition: 0.2s !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
}
.check-mark::after {
    content: '' !important;
    position: absolute !important;
    top: 2px !important;
    left: 2px !important;
    width: 14px !important;
    height: 14px !important;
    background: #64748b !important;
    border-radius: 50% !important;
    transition: 0.2s !important;
}
.perm-checkbox input:checked + .check-mark { background: rgba(52,211,153,0.3) !important; border-color: rgba(52,211,153,0.5) !important; }
.perm-checkbox input:checked + .check-mark::after { transform: translateX(14px) !important; background: #34d399 !important; }

/* ═══════════════════════════════════════════════════════════
   MODAL — centers on screen, never overflows
   ═══════════════════════════════════════════════════════════ */
.modal-overlay {
    position: fixed !important;
    inset: 0 !important;
    background: rgba(0,0,0,0.75) !important;
    backdrop-filter: blur(4px) !important;
    -webkit-backdrop-filter: blur(4px) !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 99999 !important;
    padding: 20px !important;
    box-sizing: border-box !important;
}
.modal-overlay[style*="flex"] { display: flex !important; }

.modal {
    background: #1e293b !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 20px !important;
    padding: 28px !important;
    width: 100% !important;
    max-width: 520px !important;
    max-height: calc(100vh - 40px) !important;
    overflow-y: auto !important;
    box-shadow: 0 24px 64px rgba(0,0,0,0.6) !important;
    animation: modalIn 0.2s ease-out !important;
    box-sizing: border-box !important;
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

.modal-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 22px !important;
    padding-bottom: 16px !important;
    border-bottom: 1px solid rgba(255,255,255,0.08) !important;
}
.modal-header h3 { font-size: 18px !important; color: #f1f5f9 !important; margin: 0 !important; font-weight: 700 !important; }
.modal-close {
    width: 34px !important;
    height: 34px !important;
    background: rgba(255,255,255,0.05) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 8px !important;
    color: #cbd5e1 !important;
    font-size: 14px !important;
    cursor: pointer !important;
    transition: all 0.2s !important;
    font-family: inherit !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
}
.modal-close:hover { background: rgba(239,68,68,0.2) !important; color: #f87171 !important; border-color: rgba(239,68,68,0.4) !important; }

.form-group { display: flex !important; flex-direction: column !important; gap: 6px !important; }
.form-group label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
}
.form-group input {
    width: 100% !important;
    padding: 13px 16px !important;
    background: rgba(0,0,0,0.35) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important;
    font-size: 14px !important;
    outline: none !important;
    font-family: inherit !important;
    box-sizing: border-box !important;
    transition: all 0.2s !important;
}
.form-group input:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56,189,248,0.2) !important;
}

.modal-actions {
    display: flex !important;
    justify-content: flex-end !important;
    gap: 10px !important;
    margin-top: 22px !important;
    padding-top: 18px !important;
    border-top: 1px solid rgba(255,255,255,0.08) !important;
}
.btn-primary {
    padding: 13px 28px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    box-shadow: 0 4px 15px rgba(16,185,129,0.3) !important;
    transition: all 0.2s !important;
}
.btn-primary:hover:not(:disabled) { transform: translateY(-2px) !important; box-shadow: 0 6px 20px rgba(16,185,129,0.5) !important; }
.btn-primary:disabled { opacity: 0.5 !important; cursor: not-allowed !important; transform: none !important; }

.btn-secondary {
    padding: 13px 24px !important;
    background: rgba(255,255,255,0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    border-radius: 10px !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
}
.btn-secondary:hover { background: rgba(255,255,255,0.1) !important; color: #f1f5f9 !important; }

/* ═══════════════════════════════════════════════════════════
   TOAST
   ═══════════════════════════════════════════════════════════ */
.toast {
    position: fixed !important;
    bottom: 32px !important;
    right: 32px !important;
    padding: 14px 22px !important;
    border-radius: 12px !important;
    font-size: 13.5px !important;
    font-weight: 600 !important;
    color: #fff !important;
    transform: translateY(20px) !important;
    opacity: 0 !important;
    transition: all 0.3s !important;
    z-index: 999999 !important;
    max-width: 400px !important;
    box-shadow: 0 12px 32px rgba(0,0,0,0.5) !important;
}
.toast.show { transform: translateY(0) !important; opacity: 1 !important; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.toast.error   { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }
.toast.warning { background: linear-gradient(135deg, #f59e0b, #d97706) !important; }

/* ═══════════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 1200px) {
    .role-cards { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)) !important; }
}

@media (max-width: 768px) {
    .admin-page .main-wrap {
        margin-left: 0 !important;
        width: 100% !important;
        padding-top: 64px !important;
    }
    .admin-page .main-content { padding: 16px !important; }
    .admin-page .glass-card { padding: 20px !important; border-radius: 16px !important; }
    .role-cards { grid-template-columns: 1fr !important; }
    .card-header { flex-direction: column !important; align-items: flex-start !important; }
    .header-actions { width: 100% !important; }
    .btn-primary-sm, .btn-secondary-sm { flex: 1 !important; text-align: center !important; }
    .modal { padding: 22px !important; max-width: 100% !important; }
    .modal-actions { flex-direction: column !important; }
    .modal-actions .btn-primary,
    .modal-actions .btn-secondary { width: 100% !important; }
    .toast { left: 16px !important; right: 16px !important; bottom: 16px !important; max-width: none !important; }
    .permissions-table thead th { padding: 10px 8px !important; font-size: 10px !important; }
    .permissions-table tbody td { padding: 10px 8px !important; }
}

@media (max-width: 480px) {
    .admin-page .main-content { padding: 12px !important; }
    .admin-page .glass-card { padding: 16px !important; }
}
</style>