<div class="dashboard-layout admin-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header">
                <div>
                    <h3>👥 User Management</h3>
                    <p class="card-subtitle"><?= count($data['users']) ?> user<?= count($data['users']) !== 1 ? 's' : '' ?> registered</p>
                </div>
                <div class="header-actions">
                    <button type="button" class="btn-primary-sm" onclick="openAddModal()">➕ Add User</button>
                    <a href="<?= BASE_URL ?>/admin" class="btn-secondary-sm">← Back</a>
                </div>
            </div>

            <div class="search-row">
                <input type="text" id="userSearch" placeholder="🔍 Search by name, email, or role..." onkeyup="filterTable()">
            </div>

            <?php if (empty($data['users'])): ?>
                <div class="empty-state"><div class="empty-icon">👤</div><h3>No Users Found</h3></div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="data-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>District</th>
                                <th>Status</th>
                                <th class="num-col">Logins</th>
                                <th>Registered</th>
                                <th class="actions-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['users'] as $u):
                                $statusClass = $u['status'] === 'active' ? 'green' : 'red';
                                $userJson = htmlspecialchars(json_encode([
                                    'id'          => (int)$u['id'],
                                    'first_name'  => $u['first_name'],
                                    'last_name'   => $u['last_name'],
                                    'email'       => $u['email'],
                                    'phone'       => $u['phone'] ?? '',
                                    'role_id'     => (int)$u['role_id'],
                                    'district_id' => (int)($u['district_id'] ?? 0),
                                    'status'      => $u['status']
                                ]), ENT_QUOTES);
                            ?>
                                <tr>
                                    <td class="name-cell"><strong><?= e($u['first_name'] . ' ' . ($u['last_name'] ?? '')) ?></strong></td>
                                    <td class="email-cell"><?= e($u['email']) ?></td>
                                    <td><span class="type-badge"><?= e($u['role_name'] ?? 'Unknown') ?></span></td>
                                    <td><?= e($u['district_name'] ?? '—') ?></td>
                                    <td><span class="badge <?= $statusClass ?>"><?= ucfirst($u['status']) ?></span></td>
                                    <td class="num-col"><?= number_format((int)$u['login_count']) ?></td>
                                    <td class="date-cell"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                    <td class="actions-cell">
                                        <button type="button" class="icon-action edit" title="Edit" onclick='openEditModal(<?= $userJson ?>)'>✏️</button>
                                        <a href="<?= BASE_URL ?>/admin/toggleUser/<?= $u['id'] ?>" class="icon-action toggle" title="Toggle status"><?= $u['status'] === 'active' ? '🚫' : '✅' ?></a>
                                        <button type="button" class="icon-action delete" title="Delete" onclick="deleteUser(<?= $u['id'] ?>, '<?= e($u['first_name']) ?>')">🗑️</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<!-- ══════════════════ USER MODAL ══════════════════ -->
<div class="modal-overlay" id="userModal" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">➕ Add User</h3>
            <button type="button" class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <form id="userForm" onsubmit="event.preventDefault(); saveUser();">
            <input type="hidden" id="userId" value="">
            <div class="form-row-2">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" id="firstName" required maxlength="100">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" id="lastName" maxlength="100">
                </div>
            </div>
            <div class="form-row-2">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" id="email" required maxlength="150">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" id="phone" maxlength="30">
                </div>
            </div>
            <div class="form-row-2">
                <div class="form-group">
                    <label>Role *</label>
                    <select id="roleId" required>
                        <option value="">— Select —</option>
                        <?php foreach ($data['roles'] as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>District</label>
                    <select id="districtId">
                        <option value="">— None —</option>
                        <?php foreach ($data['districts'] as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row-2">
                <div class="form-group">
                    <label id="pwdLabel">Password *</label>
                    <input type="password" id="password" minlength="6" placeholder="min 6 characters">
                    <small id="pwdHint" style="display:none; color:#94a3b8; font-size:11px;">Leave blank to keep current password.</small>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="status">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="saveBtn">💾 Save</button>
            </div>
        </form>
    </div>
</div>

<script>
let editing = false;

/* ═══════════════════════════════════════════════
   MODAL OPEN / CLOSE
   ═══════════════════════════════════════════════ */
function openAddModal() {
    editing = false;
    document.getElementById('modalTitle').textContent = '➕ Add User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('pwdLabel').innerHTML = 'Password *';
    document.getElementById('password').required = true;
    document.getElementById('pwdHint').style.display = 'none';
    document.getElementById('userModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function openEditModal(user) {
    editing = true;
    document.getElementById('modalTitle').textContent = '✏️ Edit User';
    document.getElementById('userId').value = user.id;
    document.getElementById('firstName').value = user.first_name;
    document.getElementById('lastName').value = user.last_name || '';
    document.getElementById('email').value = user.email;
    document.getElementById('phone').value = user.phone || '';
    document.getElementById('roleId').value = user.role_id;
    document.getElementById('districtId').value = user.district_id || '';
    document.getElementById('status').value = user.status;
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('pwdLabel').innerHTML = 'Password <span style="color:#64748b; font-weight:400;">(optional)</span>';
    document.getElementById('pwdHint').style.display = 'block';
    document.getElementById('userModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('userModal').style.display = 'none';
    document.body.style.overflow = '';
}

/* Close on overlay click */
document.getElementById('userModal').addEventListener('click', function (e) {
    if (e.target === this) closeModal();
});

/* Close on Escape */
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
});

/* ═══════════════════════════════════════════════
   SAVE USER
   ═══════════════════════════════════════════════ */
async function saveUser() {
    const btn = document.getElementById('saveBtn');
    const originalLabel = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ Saving...';

    /* Normalize phone → +250... */
    let phone = document.getElementById('phone').value.trim();
    if (phone && !phone.startsWith('+')) {
        phone = phone.replace(/^0+/, '');
        if (!phone.startsWith('250')) phone = '250' + phone;
        phone = '+' + phone;
    }

    const payload = new URLSearchParams({
        first_name:  document.getElementById('firstName').value.trim(),
        last_name:   document.getElementById('lastName').value.trim(),
        email:       document.getElementById('email').value.trim(),
        phone:       phone,
        role_id:     document.getElementById('roleId').value,
        district_id: document.getElementById('districtId').value,
        status:      document.getElementById('status').value,
        password:    document.getElementById('password').value
    });

    if (editing) payload.append('id', document.getElementById('userId').value);

    const url = editing
        ? '<?= BASE_URL ?>/admin/editUser'
        : '<?= BASE_URL ?>/admin/addUser';

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload.toString()
        });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); }
        catch (e) { throw new Error('Invalid server response: ' + text.slice(0, 120)); }

        if (data.success) {
            showToast(data.message || 'Saved!', 'success');
            closeModal();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.error || 'Save failed', 'error');
            btn.disabled = false;
            btn.innerHTML = originalLabel;
        }
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalLabel;
    }
}

/* ═══════════════════════════════════════════════
   DELETE USER
   ═══════════════════════════════════════════════ */
async function deleteUser(id, name) {
    if (!confirm(`Delete user "${name}"? This cannot be undone.`)) return;
    try {
        const res = await fetch('<?= BASE_URL ?>/admin/deleteUser/' + id);
        const data = await res.json();
        if (data.success) {
            showToast('User deleted.', 'success');
            setTimeout(() => location.reload(), 700);
        } else {
            showToast(data.error || 'Delete failed', 'error');
        }
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    }
}

/* ═══════════════════════════════════════════════
   SEARCH FILTER
   ═══════════════════════════════════════════════ */
function filterTable() {
    const q = document.getElementById('userSearch').value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

/* ═══════════════════════════════════════════════
   TOAST
   ═══════════════════════════════════════════════ */
function showToast(msg, type = 'success') {
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
</script>

<style>
/* ═══════════════════════════════════════════════════════════
   RESET / PAGE
   ═══════════════════════════════════════════════════════════ */
html, body { margin: 0 !important; padding: 0 !important; overflow-x: hidden !important; }

.admin-page.dashboard-layout {
    display: block !important;
    width: 100% !important;
    min-height: 100vh !important;
    background: #0b1120 !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
}

/* ── Main content fills the space to the right of the sidebar ── */
.admin-page .main-content {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    min-height: 100vh !important;
    padding: 26px 32px !important;
    box-sizing: border-box !important;
    overflow-x: hidden !important;
}

.admin-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 26px !important;
    box-sizing: border-box !important;
    width: 100% !important;
    max-width: 100% !important;
}

/* ═══════════════════════════════════════════════════════════
   CARD HEADER
   ═══════════════════════════════════════════════════════════ */
.card-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 18px !important;
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
.card-subtitle {
    font-size: 12.5px !important;
    color: #94a3b8 !important;
    margin: 4px 0 0 0 !important;
}
.header-actions { display: flex !important; gap: 10px !important; flex-wrap: wrap !important; }

.btn-primary-sm {
    padding: 10px 20px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3) !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
}
.btn-primary-sm:hover { transform: translateY(-2px) !important; }
.btn-secondary-sm {
    padding: 10px 18px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    transition: all 0.2s !important;
}
.btn-secondary-sm:hover { background: rgba(255, 255, 255, 0.1) !important; }

/* ═══════════════════════════════════════════════════════════
   SEARCH
   ═══════════════════════════════════════════════════════════ */
.search-row { margin-bottom: 16px !important; }
.search-row input {
    width: 100% !important;
    max-width: 420px !important;
    padding: 11px 16px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important;
    font-size: 13.5px !important;
    outline: none !important;
    box-sizing: border-box !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
}
.search-row input:focus { border-color: #38bdf8 !important; box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15) !important; }

/* ═══════════════════════════════════════════════════════════
   TABLE — compact, fits the screen, no horizontal scroll on desktop
   ═══════════════════════════════════════════════════════════ */
.table-wrapper {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: auto !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    background: rgba(0, 0, 0, 0.15) !important;
}

.data-table {
    width: 100% !important;
    min-width: 0 !important;        /* ← was 1000px, that's what broke it */
    table-layout: fixed !important; /* even, predictable column widths */
    border-collapse: collapse !important;
    font-size: 13px !important;
}

/* Column widths — sum to 100% */
.data-table thead th:nth-child(1) { width: 16%; }   /* Name        */
.data-table thead th:nth-child(2) { width: 22%; }   /* Email       */
.data-table thead th:nth-child(3) { width: 12%; }   /* Role        */
.data-table thead th:nth-child(4) { width: 10%; }   /* District    */
.data-table thead th:nth-child(5) { width: 10%; }   /* Status      */
.data-table thead th:nth-child(6) { width: 8%;  }   /* Logins      */
.data-table thead th:nth-child(7) { width: 12%; }   /* Registered  */
.data-table thead th:nth-child(8) { width: 10%; }   /* Actions     */

.data-table thead th {
    background: #1e293b !important;
    color: #94a3b8 !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    font-size: 11px !important;
    padding: 12px 12px !important;
    text-align: left !important;
    border-bottom: 2px solid rgba(56, 189, 248, 0.25) !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}
.data-table thead th.num-col     { text-align: right !important; }
.data-table thead th.actions-col { text-align: center !important; }

.data-table tbody td {
    padding: 12px 12px !important;
    color: #f1f5f9 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
    vertical-align: middle !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}
.data-table tbody tr:hover td { background: rgba(56, 189, 248, 0.04) !important; }

/* Cell styles */
.name-cell strong {
    color: #f1f5f9 !important;
    font-weight: 700 !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    display: block !important;
}
.email-cell {
    color: #94a3b8 !important;
    font-size: 12.5px !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    display: block !important;
}
.num-col {
    text-align: right !important;
    font-variant-numeric: tabular-nums !important;
    color: #cbd5e1 !important;
}
.date-cell {
    color: #94a3b8 !important;
    font-size: 12.5px !important;
    white-space: nowrap !important;
}
.actions-cell {
    text-align: center !important;
    white-space: nowrap !important;
}

/* Role & status badges — kept compact */
.type-badge {
    display: inline-flex !important;
    padding: 4px 10px !important;
    border-radius: 6px !important;
    font-size: 10.5px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    background: rgba(167, 139, 250, 0.15) !important;
    color: #a78bfa !important;
    border: 1px solid rgba(167, 139, 250, 0.3) !important;
    letter-spacing: 0.4px !important;
    max-width: 100% !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.badge {
    display: inline-flex !important;
    padding: 4px 12px !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
}
.badge.green { background: rgba(52, 211, 153, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(52, 211, 153, 0.3) !important; }
.badge.red   { background: rgba(248, 113, 113, 0.15) !important; color: #f87171 !important; border: 1px solid rgba(248, 113, 113, 0.3) !important; }

/* Icon action buttons — smaller so they fit 3-in-a-row */
.icon-action {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 30px !important;
    height: 30px !important;
    border-radius: 8px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    color: #cbd5e1 !important;
    font-size: 13px !important;
    cursor: pointer !important;
    text-decoration: none !important;
    transition: all 0.2s !important;
    margin: 0 1px !important;
    font-family: inherit !important;
    padding: 0 !important;
}
.icon-action:hover { transform: translateY(-2px) !important; }
.icon-action.edit:hover   { background: rgba(56, 189, 248, 0.2)  !important; border-color: rgba(56, 189, 248, 0.4)  !important; }
.icon-action.toggle:hover { background: rgba(251, 191, 36, 0.2)  !important; border-color: rgba(251, 191, 36, 0.4)  !important; }
.icon-action.delete:hover { background: rgba(239, 68, 68, 0.2)   !important; border-color: rgba(239, 68, 68, 0.4)   !important; }

/* ═══════════════════════════════════════════════════════════
   MODAL
   ═══════════════════════════════════════════════════════════ */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
    box-sizing: border-box;
}
.modal-overlay[style*="display: flex"],
.modal-overlay[style*="display:flex"] { display: flex !important; }

.modal {
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 28px;
    max-width: 620px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6);
    box-sizing: border-box;
    animation: modalIn 0.2s ease-out;
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(0.96); }
    to   { opacity: 1; transform: scale(1); }
}
.modal-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 22px !important;
    padding-bottom: 16px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
}
.modal-header h3 { font-size: 18px !important; color: #f1f5f9 !important; margin: 0 !important; font-weight: 700 !important; }
.modal-close {
    width: 32px !important;
    height: 32px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 8px !important;
    color: #cbd5e1 !important;
    font-size: 14px !important;
    cursor: pointer !important;
    font-family: inherit !important;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s !important;
}
.modal-close:hover { background: rgba(239, 68, 68, 0.2) !important; color: #f87171 !important; }

.form-row-2 {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 14px !important;
    margin-bottom: 14px !important;
}
.form-group { display: flex !important; flex-direction: column !important; gap: 6px !important; }
.form-group label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
}
.form-group input,
.form-group select {
    padding: 11px 14px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #f1f5f9 !important;
    font-size: 13.5px !important;
    outline: none !important;
    font-family: inherit !important;
    box-sizing: border-box !important;
    width: 100% !important;
    transition: all 0.2s !important;
}
.form-group input:focus,
.form-group select:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
}
.form-group select option { background: #1e293b !important; color: #f1f5f9 !important; }

.modal-actions {
    display: flex !important;
    justify-content: flex-end !important;
    gap: 10px !important;
    margin-top: 22px !important;
    padding-top: 18px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    flex-wrap: wrap !important;
}
.btn-primary {
    padding: 12px 28px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
    min-width: 120px !important;
}
.btn-primary:hover:not(:disabled) { transform: translateY(-2px) !important; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5) !important; }
.btn-primary:disabled { opacity: 0.5 !important; cursor: not-allowed !important; transform: none !important; }
.btn-secondary {
    padding: 12px 22px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
}
.btn-secondary:hover { background: rgba(255, 255, 255, 0.1) !important; color: #f1f5f9 !important; }

/* ═══════════════════════════════════════════════════════════
   EMPTY STATE
   ═══════════════════════════════════════════════════════════ */
.empty-state { text-align: center !important; padding: 60px 24px !important; }
.empty-icon { font-size: 56px !important; margin-bottom: 16px !important; opacity: 0.6 !important; }
.empty-state h3 { font-size: 18px !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; }

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
    z-index: 99999 !important;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5) !important;
    pointer-events: none !important;
    max-width: 400px !important;
}
.toast.show { transform: translateY(0) !important; opacity: 1 !important; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669) !important; }
.toast.error   { background: linear-gradient(135deg, #ef4444, #dc2626) !important; }

/* ═══════════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 1100px) {
    /* Hide less-critical columns on medium screens */
    .data-table thead th:nth-child(6),
    .data-table tbody td:nth-child(6) { display: none !important; } /* Logins */
}

@media (max-width: 900px) {
    .admin-page .main-content {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 16px !important;
        padding-top: 72px !important;
    }
    /* Hide District & Registered too */
    .data-table thead th:nth-child(4),
    .data-table tbody td:nth-child(4),
    .data-table thead th:nth-child(7),
    .data-table tbody td:nth-child(7) { display: none !important; }

    /* Rebalance remaining columns */
    .data-table thead th:nth-child(1) { width: 24% !important; }
    .data-table thead th:nth-child(2) { width: 32% !important; }
    .data-table thead th:nth-child(3) { width: 16% !important; }
    .data-table thead th:nth-child(5) { width: 12% !important; }
    .data-table thead th:nth-child(8) { width: 16% !important; }
}

@media (max-width: 768px) {
    .form-row-2 { grid-template-columns: 1fr !important; }
    .modal { padding: 20px !important; }
    .modal-actions { flex-direction: column-reverse !important; }
    .modal-actions .btn-primary,
    .modal-actions .btn-secondary { width: 100% !important; }
    .toast { left: 16px !important; right: 16px !important; bottom: 16px !important; }

    /* Hide Email on phones */
    .data-table thead th:nth-child(2),
    .data-table tbody td:nth-child(2) { display: none !important; }
    .data-table thead th:nth-child(1) { width: 35% !important; }
    .data-table thead th:nth-child(3) { width: 22% !important; }
    .data-table thead th:nth-child(5) { width: 16% !important; }
    .data-table thead th:nth-child(8) { width: 27% !important; }
}

@media (max-width: 480px) {
    /* Hide Role too — keep Name / Status / Actions only */
    .data-table thead th:nth-child(3),
    .data-table tbody td:nth-child(3) { display: none !important; }
    .data-table thead th:nth-child(1) { width: 45% !important; }
    .data-table thead th:nth-child(5) { width: 22% !important; }
    .data-table thead th:nth-child(8) { width: 33% !important; }
}
</style>