<?php
$incoming  = $incoming  ?? [];
$outgoing  = $outgoing  ?? [];
$friends   = $friends   ?? [];
$suggested = $suggested ?? [];
$csrf      = $csrf      ?? '';

$name = $_SESSION['name'] ?? 'User';
?>
<div class="dashboard-layout friends-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="main-content">

            <!-- HERO -->
            <section class="glass-card friends-hero">
                <div>
                    <h1>👥 Friends</h1>
                    <p>Manage your friendships and connect with other farmers.</p>
                </div>
                <div class="hero-stats">
                    <div class="hs"><strong><?= count($friends); ?></strong><small>Friends</small></div>
                    <div class="hs hs-warn"><strong><?= count($incoming); ?></strong><small>Requests</small></div>
                    <div class="hs hs-info"><strong><?= count($outgoing); ?></strong><small>Pending</small></div>
                </div>
            </section>

            <!-- PENDING REQUESTS -->
            <?php if (!empty($incoming)): ?>
            <section class="glass-card">
                <div class="sec-head">
                    <h2>📨 Friend Requests <span class="badge"><?= count($incoming); ?></span></h2>
                </div>
                <div class="user-list">
                    <?php foreach ($incoming as $u): 
                        $fullName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                        if ($fullName === '') $fullName = $u['email'] ?? 'User';
                        $initials = strtoupper(mb_substr($fullName, 0, 1));
                        $parts = explode(' ', $fullName);
                        if (count($parts) > 1) $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
                    ?>
                        <div class="user-row" data-request-id="<?= (int)$u['request_id']; ?>">
                            <div class="avatar">
                                <?php if (!empty($u['profile_photo'])): ?>
                                    <img src="<?= e($u['profile_photo']) ?>" alt="">
                                <?php else: ?>
                                    <span class="initials"><?= e($initials); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="uinfo">
                                <strong><?= e($fullName); ?></strong>
                                <?php if (!empty($u['message'])): ?>
                                    <em>"<?= e($u['message']); ?>"</em>
                                <?php else: ?>
                                    <small>Wants to be your friend</small>
                                <?php endif; ?>
                            </div>
                            <div class="actions">
                                <button class="btn btn-accept" data-action="accept" data-id="<?= (int)$u['request_id']; ?>">
                                    ✓ Accept
                                </button>
                                <button class="btn btn-reject" data-action="reject" data-id="<?= (int)$u['request_id']; ?>">
                                    ✕ Reject
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- FRIENDS LIST -->
            <section class="glass-card">
                <div class="sec-head">
                    <h2>👫 My Friends <span class="badge badge-green"><?= count($friends); ?></span></h2>
                </div>

                <?php if (empty($friends)): ?>
                    <div class="empty">
                        <div class="empty-icon">👥</div>
                        <p>You don't have any friends yet.</p>
                        <p class="dim">Find someone below and send a friend request!</p>
                    </div>
                <?php else: ?>
                    <div class="user-list">
                        <?php foreach ($friends as $u):
                            $fullName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                            if ($fullName === '') $fullName = $u['email'] ?? 'User';
                            $initials = strtoupper(mb_substr($fullName, 0, 1));
                            $parts = explode(' ', $fullName);
                            if (count($parts) > 1) $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
                            $online = false;
                            if (!empty($u['last_active_at'])) {
                                $online = (time() - strtotime($u['last_active_at'])) <= 120;
                            }
                        ?>
                            <div class="user-row" data-friend-id="<?= (int)$u['id']; ?>">
                                <div class="avatar">
                                    <?php if (!empty($u['profile_photo'])): ?>
                                        <img src="<?= e($u['profile_photo']) ?>" alt="">
                                    <?php else: ?>
                                        <span class="initials"><?= e($initials); ?></span>
                                    <?php endif; ?>
                                    <?php if ($online): ?><span class="dot-online"></span><?php endif; ?>
                                </div>
                                <div class="uinfo">
                                    <strong><?= e($fullName); ?></strong>
                                    <small><?= $online ? '🟢 Online' : '⚪ Offline'; ?></small>
                                </div>
                                <div class="actions">
                                    <a class="btn btn-chat" href="<?= BASE_URL ?>/Messages?with=<?= (int)$u['id']; ?>">
                                        💬 Chat
                                    </a>
                                    <button class="btn btn-ghost" data-action="unfriend" data-id="<?= (int)$u['id']; ?>">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- SENT REQUESTS -->
            <?php if (!empty($outgoing)): ?>
            <section class="glass-card">
                <div class="sec-head">
                    <h2>📤 Sent Requests <span class="badge"><?= count($outgoing); ?></span></h2>
                </div>
                <div class="user-list">
                    <?php foreach ($outgoing as $u):
                        $fullName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                        if ($fullName === '') $fullName = $u['email'] ?? 'User';
                        $initials = strtoupper(mb_substr($fullName, 0, 1));
                        $parts = explode(' ', $fullName);
                        if (count($parts) > 1) $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
                    ?>
                        <div class="user-row" data-request-id="<?= (int)$u['request_id']; ?>">
                            <div class="avatar">
                                <?php if (!empty($u['profile_photo'])): ?>
                                    <img src="<?= e($u['profile_photo']) ?>" alt="">
                                <?php else: ?>
                                    <span class="initials"><?= e($initials); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="uinfo">
                                <strong><?= e($fullName); ?></strong>
                                <small>⏳ Waiting for response</small>
                            </div>
                            <div class="actions">
                                <button class="btn btn-ghost" data-action="cancel" data-id="<?= (int)$u['request_id']; ?>">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- SUGGESTED PEOPLE -->
            <section class="glass-card">
                <div class="sec-head">
                    <h2>🔎 People You May Know</h2>
                </div>

                <?php if (empty($suggested)): ?>
                    <div class="empty">
                        <p class="dim">No suggestions right now — you're connected with everyone!</p>
                    </div>
                <?php else: ?>
                    <div class="user-list">
                        <?php foreach ($suggested as $u):
                            $fullName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                            if ($fullName === '') $fullName = $u['email'] ?? 'User';
                            $initials = strtoupper(mb_substr($fullName, 0, 1));
                            $parts = explode(' ', $fullName);
                            if (count($parts) > 1) $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
                        ?>
                            <div class="user-row" data-user-id="<?= (int)$u['id']; ?>">
                                <div class="avatar">
                                    <?php if (!empty($u['profile_photo'])): ?>
                                        <img src="<?= e($u['profile_photo']) ?>" alt="">
                                    <?php else: ?>
                                        <span class="initials"><?= e($initials); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="uinfo">
                                    <strong><?= e($fullName); ?></strong>
                                    <small><?= e($u['email'] ?? ''); ?></small>
                                </div>
                                <div class="actions">
                                    <button class="btn btn-primary" data-action="add" data-id="<?= (int)$u['id']; ?>">
                                        ➕ Add Friend
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        </main>
    </div>
</div>

<style>
/* FRIENDS PAGE STYLES */
.friends-page.dashboard-layout { display: block !important; width: 100% !important; background: #0b1120 !important; min-height: 100vh !important; }
.friends-page .main-wrap { margin-left: 260px !important; width: calc(100% - 260px) !important; min-height: 100vh !important; box-sizing: border-box !important; }
.friends-page .main-content { padding: 20px 28px 60px !important; display: flex !important; flex-direction: column !important; gap: 18px !important; box-sizing: border-box !important; }
.friends-page .glass-card { background: rgba(30,41,59,.7) !important; backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,.08) !important; border-radius: 20px !important; padding: 22px !important; box-sizing: border-box !important; width: 100% !important; }

.friends-hero {
    background: linear-gradient(135deg, rgba(16,185,129,.15), rgba(56,189,248,.08)) !important;
    display: flex !important; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;
}
.friends-hero h1 { margin: 0 0 6px; font-size: 24px; font-weight: 800; color: #f1f5f9; }
.friends-hero p { margin: 0; color: #94a3b8; font-size: 13.5px; }
.hero-stats { display: flex; gap: 10px; flex-wrap: wrap; }
.hs { background: rgba(15,23,42,.6); padding: 10px 18px; border-radius: 12px; text-align: center; min-width: 80px; border: 1px solid rgba(255,255,255,.06); }
.hs strong { display: block; font-size: 20px; font-weight: 800; color: #34d399; }
.hs small { font-size: 10.5px; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; font-weight: 700; }
.hs-warn strong { color: #fbbf24; }
.hs-info strong { color: #38bdf8; }

.sec-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,.06); }
.sec-head h2 { margin: 0; font-size: 15px; font-weight: 800; color: #f1f5f9; }
.badge { background: rgba(56,189,248,.15); color: #38bdf8; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 800; margin-left: 8px; }
.badge-green { background: rgba(16,185,129,.15); color: #34d399; }

.user-list { display: flex; flex-direction: column; gap: 8px; }
.user-row {
    display: flex; align-items: center; gap: 14px;
    padding: 12px 14px; background: rgba(15,23,42,.5);
    border: 1px solid rgba(255,255,255,.05);
    border-radius: 14px; transition: all .15s;
}
.user-row:hover { background: rgba(15,23,42,.8); border-color: rgba(56,189,248,.2); }

.avatar {
    position: relative; width: 52px; height: 52px;
    border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg,#10b981,#38bdf8);
    overflow: visible; border: 2px solid rgba(255,255,255,.1);
}
.avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
.avatar .initials { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #fff; font-size: 18px; border-radius: 50%; }
.avatar .dot-online {
    position: absolute; bottom: 2px; right: 2px;
    width: 14px; height: 14px; background: #10b981;
    border-radius: 50%; border: 2px solid #0b1120;
    box-shadow: 0 0 8px rgba(16,185,129,.7);
}

.uinfo { flex: 1; min-width: 0; }
.uinfo strong { display: block; color: #f1f5f9; font-size: 14px; font-weight: 700; margin-bottom: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.uinfo small { color: #94a3b8; font-size: 12px; display: block; }
.uinfo em { color: #cbd5e1; font-size: 12.5px; font-style: italic; display: block; margin-top: 2px; }

.actions { display: flex; gap: 8px; flex-wrap: wrap; }

.btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 16px; border-radius: 10px;
    font-size: 12.5px; font-weight: 700; cursor: pointer;
    text-decoration: none; border: none;
    font-family: inherit; transition: all .15s;
}
.btn:hover { transform: translateY(-1px); }
.btn-primary { background: linear-gradient(135deg,#10b981,#059669); color: #fff; box-shadow: 0 4px 12px rgba(16,185,129,.3); }
.btn-accept  { background: linear-gradient(135deg,#10b981,#059669); color: #fff; }
.btn-reject  { background: rgba(239,68,68,.15); color: #f87171; border: 1px solid rgba(239,68,68,.3); }
.btn-chat    { background: linear-gradient(135deg,#38bdf8,#0ea5e9); color: #fff; }
.btn-ghost   { background: rgba(255,255,255,.06); color: #cbd5e1; border: 1px solid rgba(255,255,255,.1); }
.btn-ghost:hover { background: rgba(239,68,68,.15); color: #f87171; border-color: rgba(239,68,68,.3); }

.empty { text-align: center; padding: 30px 20px; color: #94a3b8; }
.empty-icon { font-size: 40px; margin-bottom: 8px; }
.empty p { margin: 4px 0; }
.empty .dim { font-size: 12.5px; color: #64748b; }

@media (max-width: 900px) {
    .friends-page .main-wrap { margin-left: 0 !important; width: 100% !important; padding-top: 64px !important; }
    .friends-page .main-content { padding: 14px !important; }
    .user-row { flex-wrap: wrap; }
    .actions { width: 100%; justify-content: flex-end; }
}
</style>

<script>
(function(){
    var CSRF = '<?= htmlspecialchars($csrf, ENT_QUOTES) ?>';
    var BASE = '<?= BASE_URL ?>';

    function post(action, payload) {
        var body = new URLSearchParams(payload).toString();
        if (CSRF && !body.includes('csrf=')) body += '&csrf=' + encodeURIComponent(CSRF);
        return fetch(BASE + '/Friends/' + action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        }).then(function(r) { return r.json(); });
    }

    document.addEventListener('click', function(e){
        var btn = e.target.closest('[data-action]');
        if (!btn) return;
        e.preventDefault();

        var action = btn.dataset.action;
        var id     = btn.dataset.id;
        var row    = btn.closest('.user-row');
        if (!id || !row) return;

        btn.disabled = true;
        btn.textContent = '…';

        if (action === 'accept') {
            post('accept', {request_id: id}).then(function(d){
                if (d.success) {
                    row.style.opacity = '0.3';
                    setTimeout(function(){ location.reload(); }, 400);
                } else {
                    alert(d.error || 'Failed');
                    btn.disabled = false;
                }
            });
        }
        else if (action === 'reject') {
            post('reject', {request_id: id}).then(function(d){
                if (d.success) { row.remove(); }
                else { alert(d.error || 'Failed'); btn.disabled = false; }
            });
        }
        else if (action === 'cancel') {
            post('cancel', {request_id: id}).then(function(d){
                if (d.success) { row.remove(); }
                else { alert(d.error || 'Failed'); btn.disabled = false; }
            });
        }
        else if (action === 'unfriend') {
            if (!confirm('Remove this friend?')) { btn.disabled = false; btn.textContent = 'Remove'; return; }
            post('unfriend', {user_id: id}).then(function(d){
                if (d.success) { row.remove(); }
                else { alert(d.error || 'Failed'); btn.disabled = false; }
            });
        }
        else if (action === 'add') {
            post('send', {user_id: id}).then(function(d){
                if (d.success) {
                    if (d.result === 'became_friends') {
                        btn.textContent = '✓ Friends';
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-accept');
                    } else {
                        btn.textContent = '⏳ Requested';
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-ghost');
                    }
                    btn.disabled = true;
                } else {
                    alert(d.error || 'Failed');
                    btn.disabled = false;
                    btn.textContent = '➕ Add Friend';
                }
            });
        }
    });
})();
</script>