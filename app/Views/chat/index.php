<?php
$conversations      = $conversations      ?? ($data['conversations']      ?? []);
$activeConversation = $activeConversation ?? ($data['activeConversation'] ?? null);
$messages           = $messages           ?? ($data['messages']           ?? []);
$csrf               = $csrf               ?? ($data['csrf']               ?? '');
$myId               = (int)($_SESSION['user_id'] ?? 0);
$myInitial          = strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1));

function chat_avatar(array $u, string $size = 'md'): string {
    $name = $u['name'] ?? 'User';
    $inits = strtoupper(mb_substr($name, 0, 1, 'UTF-8'));
    $parts = explode(' ', $name);
    if (count($parts) > 1) $inits = strtoupper(mb_substr($parts[0], 0, 1, 'UTF-8') . mb_substr(end($parts), 0, 1, 'UTF-8'));
    return '<span class="chat-av chat-av-' . $size . '">' . htmlspecialchars($inits, ENT_QUOTES, 'UTF-8') . '</span>';
}

function chat_time_short(?string $ts): string {
    if (!$ts) return '';
    $t = strtotime($ts);
    if (!$t) return '';
    $diff = time() - $t;
    if ($diff < 60) return 'now';
    if ($diff < 3600) return floor($diff / 60) . 'm';
    if ($diff < 86400) return date('H:i', $t);
    return date('M d', $t);
}
?>
<div class="dashboard-layout chat-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="main-content chat-shell" data-my-id="<?= $myId ?>">

            <!-- ═══════════ INBOX ═══════════ -->
            <aside class="chat-inbox glass-card">
                <header class="inbox-head">
                    <div class="inbox-title">
                        <h2>Messages</h2>
                        <span class="inbox-count"><?= count($conversations) ?></span>
                    </div>
                    <button type="button" class="inbox-new" onclick="focusSearch()" title="New conversation">＋</button>
                </header>

                <div class="search-box">
                    <input type="text" id="userSearch" placeholder="Search farmers, officers, cooperatives…" autocomplete="off">
                    <button type="button" class="search-clear" id="searchClear" onclick="clearSearch()" hidden>✕</button>
                    <div class="search-results" id="searchResults" hidden></div>
                </div>

                <div class="inbox-list">
                    <?php if (empty($conversations)): ?>
                        <div class="inbox-empty">
                            <div class="empty-emoji">💬</div>
                            <p>No conversations yet</p>
                            <button type="button" class="btn-ghost-sm" onclick="focusSearch()">Find someone to chat with</button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $c):
                            $title     = $c['title'] ?? 'Direct chat';
                            $preview   = $c['last_msg'] ?? 'No messages yet';
                            $unread    = (int)($c['unread_count'] ?? 0);
                            $convId    = (int)$c['id'];
                            $activeCls = ($activeConversation && (int)$activeConversation['id'] === $convId) ? 'active' : '';
                        ?>
                            <a href="<?= BASE_URL ?>/Messages?c=<?= $convId ?>" class="conv-row <?= $activeCls ?>">
                                <div class="conv-av-wrap">
                                    <?= chat_avatar(['name' => $title], 'md') ?>
                                </div>
                                <div class="conv-main">
                                    <div class="conv-row-1">
                                        <strong class="conv-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span class="conv-time"><?= htmlspecialchars(chat_time_short($c['last_msg_at'] ?? null)) ?></span>
                                    </div>
                                    <div class="conv-row-2">
                                        <span class="conv-preview"><?= htmlspecialchars(mb_substr($preview, 0, 40, 'UTF-8')) ?></span>
                                        <?php if ($unread > 0): ?>
                                            <span class="unread-badge"><?= $unread > 99 ? '99+' : $unread ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- ═══════════ CHAT PANE ═══════════ -->
            <section class="chat-pane glass-card">
                <?php if (!$activeConversation): ?>
                    <div class="pane-empty">
                        <div class="pane-empty-emoji">💬</div>
                        <h3>Your messages</h3>
                        <p>Search for someone to start chatting, or pick a conversation from the left.</p>
                        <button type="button" class="btn-primary-lg" onclick="focusSearch()">🔍 Find someone</button>
                    </div>
                <?php else: ?>
                    <header class="pane-head">
                        <div class="pane-head-av"><?= chat_avatar(['name' => $activeConversation['title'] ?? 'Chat'], 'sm') ?></div>
                        <div class="pane-head-info">
                            <strong><?= htmlspecialchars($activeConversation['title'] ?? 'Chat', ENT_QUOTES, 'UTF-8') ?></strong>
                            <small>Tap a message to reply · Emojis supported 😊</small>
                        </div>
                    </header>

                    <div class="pane-messages" id="chatMessages">
                        <?php if (empty($messages)): ?>
                            <div class="chat-empty-hint">
                                <span class="hint-emoji">👋</span>
                                <p>Say hello to <strong><?= htmlspecialchars($activeConversation['title'] ?? 'them', ENT_QUOTES, 'UTF-8') ?></strong></p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $m):
                                $isMe = ((int)($m['sender_id'] ?? 0) === $myId);
                                $mType = $m['message_type'] ?? 'text';
                            ?>
                                <div class="msg-row <?= $isMe ? 'out' : 'in' ?>" data-id="<?= (int)$m['id'] ?>">
                                    <div class="msg-bubble">
                                        <?php if (!$isMe && !empty($m['first_name'])): ?>
                                            <div class="msg-sender"><?= htmlspecialchars($m['first_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>

                                        <?php if ($mType === 'emoji'): ?>
                                            <div class="msg-text msg-emoji-big"><?= htmlspecialchars($m['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php elseif ($mType === 'image' && !empty($m['media_url'])): ?>
                                            <img src="<?= htmlspecialchars($m['media_url'], ENT_QUOTES, 'UTF-8') ?>" alt="" style="max-width:100%;border-radius:10px;display:block;">
                                        <?php elseif ($mType === 'audio' && !empty($m['media_url'])): ?>
                                            <audio controls src="<?= htmlspecialchars($m['media_url'], ENT_QUOTES, 'UTF-8') ?>" style="max-width:100%;"></audio>
                                        <?php elseif ($mType === 'video' && !empty($m['media_url'])): ?>
                                            <video controls src="<?= htmlspecialchars($m['media_url'], ENT_QUOTES, 'UTF-8') ?>" style="max-width:100%;border-radius:10px;"></video>
                                        <?php else: ?>
                                            <div class="msg-text"><?= nl2br(htmlspecialchars($m['content'] ?? '', ENT_QUOTES, 'UTF-8')) ?></div>
                                        <?php endif; ?>

                                        <div class="msg-meta">
                                            <span class="msg-time"><?= date('H:i', strtotime($m['created_at'] ?? 'now')) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form class="pane-input" id="chatForm" onsubmit="return sendMessage(event)">
                        <input type="hidden" name="conversation_id" value="<?= (int)$activeConversation['id'] ?>">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

                        <!-- Emoji picker button -->
                        <button type="button" class="input-emoji" id="emojiBtn" title="Insert emoji">😊</button>

                        <textarea name="content" id="msgInput" placeholder="Type a message… (emojis welcome 😊)" rows="1"></textarea>
                        <button type="submit" class="input-send" id="sendBtn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21 23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
                    </form>

                    <!-- Simple emoji palette -->
                    <div class="emoji-palette" id="emojiPalette" hidden>
                        <?php
                        $emojiSet = ['😊','😂','❤️','👍','🙏','🔥','🎉','😍','😎','🤔','😅','👏','💪','🌱','🌾','🌻','🐝','🚜','☀️','🌧️','🌱','✅','❌','⭐'];
                        foreach ($emojiSet as $e) echo '<button type="button" class="emoji-btn">' . $e . '</button>';
                        ?>
                    </div>
                <?php endif; ?>
            </section>

        </main>
    </div>
</div>

<style>
html, body { margin: 0 !important; padding: 0 !important; overflow-x: hidden !important; }

.chat-page.dashboard-layout { display: block !important; width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; }
.chat-page .main-wrap {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    min-height: 100vh !important;
    box-sizing: border-box !important;
}
.chat-page .main-content.chat-shell {
    margin: 0 !important;
    width: 100% !important;
    padding: 20px 24px !important;
    box-sizing: border-box !important;
    display: grid !important;
    grid-template-columns: 360px minmax(0, 1fr) !important;
    gap: 20px !important;
    height: calc(100vh - 60px) !important;
    overflow: hidden !important;
}
.chat-page .glass-card {
    background: rgba(30, 41, 59, 0.7) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 18px !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
    min-height: 0 !important;
}

/* INBOX */
.chat-inbox { display: flex !important; flex-direction: column !important; height: 100% !important; padding: 0 !important; }
.inbox-head { display: flex; justify-content: space-between; align-items: center; padding: 18px 20px; border-bottom: 1px solid rgba(255,255,255,0.06); }
.inbox-title { display: flex; align-items: center; gap: 10px; }
.inbox-title h2 { font-size: 18px; font-weight: 800; color: #f1f5f9; margin: 0; }
.inbox-count { display: inline-flex; align-items: center; justify-content: center; min-width: 24px; height: 22px; padding: 0 8px; background: rgba(16,185,129,0.15); color: #34d399; border-radius: 999px; font-size: 11.5px; font-weight: 800; }
.inbox-new { width: 38px; height: 38px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; border-radius: 12px; cursor: pointer; font-size: 20px; font-family: inherit; font-weight: 700; padding: 0; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(16,185,129,0.35); }

.search-box { position: relative; margin: 14px 16px 10px; }
.search-box input { width: 100%; padding: 11px 40px 11px 16px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; color: #f1f5f9; font-size: 13.5px; font-family: inherit; outline: none; box-sizing: border-box; }
.search-box input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }
.search-clear { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 26px; height: 26px; background: rgba(255,255,255,0.06); color: #94a3b8; border: none; border-radius: 50%; cursor: pointer; font-size: 12px; padding: 0; }
.search-results { position: absolute; top: calc(100% + 6px); left: 0; right: 0; max-height: 420px; overflow-y: auto; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; box-shadow: 0 16px 40px rgba(0,0,0,0.6); z-index: 200; padding: 6px; }
.search-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 10px; cursor: pointer; transition: all 0.15s; }
.search-item:hover { background: rgba(16,185,129,0.1); }
.search-item-info { flex: 1; min-width: 0; }
.search-item-info strong { display: block; font-size: 13.5px; color: #f1f5f9; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.search-item-info small { display: block; font-size: 11.5px; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.search-item .relation-tag {
    display: inline-block; padding: 2px 8px; border-radius: 10px;
    font-size: 10px; font-weight: 800; text-transform: uppercase;
    margin-top: 3px; letter-spacing: 0.3px;
}
.relation-tag.friends    { background: rgba(16,185,129,0.15); color: #34d399; }
.relation-tag.request_sent { background: rgba(251,191,36,0.15); color: #fbbf24; }
.relation-tag.request_received { background: rgba(56,189,248,0.15); color: #38bdf8; }
.relation-tag.blocked { background: rgba(239,68,68,0.15); color: #f87171; }
.relation-tag.none { background: rgba(148,163,184,0.15); color: #94a3b8; }

.btn-chat { padding: 7px 14px; background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.35); border-radius: 8px; font-size: 11.5px; font-weight: 700; cursor: pointer; font-family: inherit; }
.btn-chat:hover { background: rgba(16,185,129,0.25); }
.btn-add-friend { padding: 7px 14px; background: rgba(56,189,248,0.15); color: #38bdf8; border: 1px solid rgba(56,189,248,0.35); border-radius: 8px; font-size: 11.5px; font-weight: 700; cursor: pointer; font-family: inherit; }
.btn-add-friend:hover { background: rgba(56,189,248,0.25); }
.btn-accept-friend { padding: 7px 14px; background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.35); border-radius: 8px; font-size: 11.5px; font-weight: 700; cursor: pointer; font-family: inherit; }
.btn-pending { padding: 7px 14px; background: rgba(148,163,184,0.15); color: #94a3b8; border: 1px solid rgba(148,163,184,0.2); border-radius: 8px; font-size: 11.5px; font-weight: 700; font-family: inherit; cursor: default; }

.search-loading, .search-empty, .search-error { padding: 20px; text-align: center; font-size: 12.5px; color: #94a3b8; }

.inbox-list { flex: 1; overflow-y: auto; padding: 8px; }
.inbox-list::-webkit-scrollbar { width: 6px; }
.inbox-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
.inbox-empty { text-align: center; padding: 40px 20px; }
.empty-emoji { font-size: 44px; margin-bottom: 10px; opacity: 0.5; }
.inbox-empty p { color: #94a3b8; font-size: 13px; margin: 0 0 14px 0; }
.btn-ghost-sm { padding: 9px 16px; background: rgba(255,255,255,0.04); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.1); border-radius: 9px; font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: inherit; }

.conv-row { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 12px; cursor: pointer; transition: all 0.15s; margin-bottom: 2px; text-decoration: none; }
.conv-row:hover { background: rgba(255,255,255,0.04); }
.conv-row.active { background: rgba(16,185,129,0.12); box-shadow: inset 3px 0 0 #10b981; }
.conv-av-wrap { position: relative; flex-shrink: 0; }
.chat-av { display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 800; color: #fff; flex-shrink: 0; background: linear-gradient(135deg, #10b981, #38bdf8); }
.chat-av-sm { width: 36px; height: 36px; font-size: 12px; }
.chat-av-md { width: 44px; height: 44px; font-size: 14px; }
.conv-main { flex: 1; min-width: 0; }
.conv-row-1 { display: flex; justify-content: space-between; align-items: baseline; gap: 8px; margin-bottom: 3px; }
.conv-title { font-size: 13.5px; font-weight: 700; color: #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conv-time { font-size: 10.5px; color: #64748b; font-weight: 700; flex-shrink: 0; }
.conv-row-2 { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
.conv-preview { font-size: 12px; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; }
.unread-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 6px; background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; border-radius: 999px; font-size: 10.5px; font-weight: 800; }

/* CHAT PANE */
.chat-pane { display: flex !important; flex-direction: column !important; height: 100% !important; padding: 0 !important; position: relative; }
.pane-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 40px; }
.pane-empty-emoji { font-size: 64px; margin-bottom: 16px; opacity: 0.4; }
.pane-empty h3 { font-size: 20px; color: #f1f5f9; margin: 0 0 8px 0; font-weight: 700; }
.pane-empty p { color: #94a3b8; font-size: 13.5px; margin: 0 0 20px 0; max-width: 320px; }
.btn-primary-lg { display: inline-flex; align-items: center; gap: 8px; padding: 13px 26px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; border-radius: 12px; font-size: 13.5px; font-weight: 700; cursor: pointer; font-family: inherit; box-shadow: 0 4px 15px rgba(16,185,129,0.35); }

.pane-head { display: flex; align-items: center; gap: 12px; padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.06); background: rgba(0,0,0,0.15); flex-shrink: 0; }
.pane-head-av { flex-shrink: 0; }
.pane-head-info { flex: 1; min-width: 0; }
.pane-head-info strong { display: block; font-size: 14.5px; color: #f1f5f9; font-weight: 800; margin-bottom: 2px; }
.pane-head-info small { display: block; font-size: 11.5px; color: #94a3b8; }

.pane-messages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 10px; min-height: 0; }
.pane-messages::-webkit-scrollbar { width: 6px; }
.pane-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }

.chat-empty-hint { display: flex; flex-direction: column; align-items: center; padding: 60px 20px; text-align: center; color: #94a3b8; }
.chat-empty-hint .hint-emoji { font-size: 48px; margin-bottom: 12px; }

.msg-row { display: flex; max-width: 75%; animation: msgIn 0.25s ease-out; }
@keyframes msgIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
.msg-row.in { align-self: flex-start; }
.msg-row.out { align-self: flex-end; flex-direction: row-reverse; }
.msg-bubble { padding: 10px 14px; border-radius: 16px; font-size: 13.5px; line-height: 1.55; word-wrap: break-word; max-width: 100%; }
.msg-row.in .msg-bubble { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08); color: #e2e8f0; border-bottom-left-radius: 4px; }
.msg-row.out .msg-bubble { background: linear-gradient(135deg, #10b981, #059669); color: #fff; border-bottom-right-radius: 4px; box-shadow: 0 4px 14px rgba(16,185,129,0.25); }
.msg-sender { display: block; font-size: 11px; font-weight: 800; color: #34d399; margin-bottom: 4px; }
.msg-text { word-break: break-word; }
.msg-emoji-big { font-size: 36px; line-height: 1.2; }
.msg-meta { display: flex; justify-content: flex-end; margin-top: 4px; }
.msg-time { font-size: 10.5px; opacity: 0.7; }

.pane-input { display: flex; align-items: flex-end; gap: 8px; padding: 12px 16px; border-top: 1px solid rgba(255,255,255,0.06); background: rgba(0,0,0,0.2); flex-shrink: 0; }
.input-emoji { width: 42px; height: 42px; background: rgba(255,255,255,0.06); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.08); border-radius: 50%; cursor: pointer; font-size: 20px; padding: 0; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.input-emoji:hover { background: rgba(255,255,255,0.12); }
#msgInput { flex: 1; padding: 11px 16px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 22px; color: #f1f5f9; font-family: inherit; font-size: 14px; outline: none; resize: none; min-height: 42px; max-height: 120px; box-sizing: border-box; }
#msgInput:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }
.input-send { width: 44px; height: 44px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; padding: 0; box-shadow: 0 4px 14px rgba(16,185,129,0.35); }
.input-send:hover { transform: scale(1.05); }
.input-send:disabled { opacity: 0.5; cursor: not-allowed; }

/* Emoji palette */
.emoji-palette {
    position: absolute; bottom: 70px; left: 16px;
    background: #1e293b; border: 1px solid rgba(255,255,255,0.1);
    border-radius: 14px; padding: 10px;
    display: grid; grid-template-columns: repeat(8, 1fr); gap: 4px;
    box-shadow: 0 16px 40px rgba(0,0,0,0.6);
    z-index: 100;
    max-width: 340px;
}
.emoji-btn {
    width: 34px; height: 34px;
    background: transparent; border: none;
    font-size: 22px; cursor: pointer;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    padding: 0; font-family: inherit;
}
.emoji-btn:hover { background: rgba(255,255,255,0.08); transform: scale(1.15); }

.toast { position: fixed; bottom: 28px; right: 28px; padding: 14px 22px; border-radius: 12px; font-size: 13.5px; font-weight: 600; color: #fff; transform: translateY(20px); opacity: 0; transition: all 0.3s; z-index: 999999; max-width: 400px; pointer-events: none; box-shadow: 0 12px 32px rgba(0,0,0,0.5); font-family: 'Inter', sans-serif; }
.toast.show { transform: translateY(0); opacity: 1; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669); }
.toast.error   { background: linear-gradient(135deg, #ef4444, #dc2626); }
.toast.info    { background: linear-gradient(135deg, #38bdf8, #0284c7); }

@media (max-width: 900px) {
    .chat-page .main-wrap { margin-left: 0 !important; width: 100% !important; padding-top: 64px !important; }
    .chat-page .main-content.chat-shell { grid-template-columns: 1fr !important; height: auto !important; padding: 12px !important; }
    .chat-page .chat-inbox, .chat-page .chat-pane { height: 72vh !important; }
}
</style>

<script>
(function () {
    'use strict';

    var BASE = '<?= BASE_URL ?>';
    var CSRF = '<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>';
    var searchEl      = document.getElementById('userSearch');
    var searchResults = document.getElementById('searchResults');
    var searchClear   = document.getElementById('searchClear');
    var searchTimer;

    window.focusSearch = function () { if (searchEl) searchEl.focus(); };
    window.clearSearch = function () {
        if (searchEl) searchEl.value = '';
        if (searchResults) { searchResults.hidden = true; searchResults.innerHTML = ''; }
        if (searchClear) searchClear.hidden = true;
    };

    /* ═══ SEARCH ═══ */
    if (searchEl) {
        searchEl.addEventListener('input', function () {
            var q = this.value.trim();
            clearTimeout(searchTimer);
            searchClear.hidden = !q;

            if (q.length < 1) {
                searchResults.hidden = true;
                searchResults.innerHTML = '';
                return;
            }

            searchResults.hidden = false;
            searchResults.innerHTML = '<div class="search-loading">Searching…</div>';

            searchTimer = setTimeout(function () {
                fetch(BASE + '/Messages/search?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.success || !data.users || !data.users.length) {
                            searchResults.innerHTML = '<div class="search-empty">No users found</div>';
                            return;
                        }
                        searchResults.innerHTML = data.users.map(function (u) {
                            var rel = u.relationship || 'none';
                            var actionHtml = '';
                            var relLabel = '';

                            if (rel === 'friends') {
                                relLabel = '<span class="relation-tag friends">Friend</span>';
                                actionHtml = '<button type="button" class="btn-chat" data-action="chat" data-uid="' + u.id + '">💬 Chat</button>';
                            } else if (rel === 'request_sent') {
                                relLabel = '<span class="relation-tag request_sent">Requested</span>';
                                actionHtml = '<button type="button" class="btn-pending" disabled>⏳ Pending</button>';
                            } else if (rel === 'request_received') {
                                relLabel = '<span class="relation-tag request_received">Wants to be friends</span>';
                                actionHtml = '<button type="button" class="btn-accept-friend" data-action="accept" data-uid="' + u.id + '">✓ Accept</button>';
                            } else if (rel === 'blocked') {
                                relLabel = '<span class="relation-tag blocked">Blocked</span>';
                                actionHtml = '';
                            } else {
                                relLabel = '<span class="relation-tag none">Not connected</span>';
                                actionHtml = '<button type="button" class="btn-add-friend" data-action="add" data-uid="' + u.id + '">➕ Add Friend</button>';
                            }

                            return '<div class="search-item" data-uid="' + u.id + '" data-rel="' + rel + '">' +
                                '<span class="chat-av chat-av-sm">' + esc(u.initials || '?') + '</span>' +
                                '<div class="search-item-info">' +
                                    '<strong>' + esc(u.name) + '</strong>' +
                                    '<small>' + esc(u.email || '') + '</small>' +
                                    relLabel +
                                '</div>' +
                                actionHtml +
                            '</div>';
                        }).join('');
                    })
                    .catch(function () {
                        searchResults.innerHTML = '<div class="search-error">Search failed</div>';
                    });
            }, 250);
        });
    }

    /* ═══ CLICK HANDLER (delegated) ═══ */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-action]');
        if (btn) {
            e.stopPropagation();
            var action = btn.dataset.action;
            var uid    = parseInt(btn.dataset.uid, 10);
            if (!uid) return;

            if (action === 'chat')   return startDM(uid, btn);
            if (action === 'add')    return addFriend(uid, btn);
            if (action === 'accept') return acceptFriend(uid, btn);
            return;
        }

        /* Also allow clicking the whole search item row */
        var item = e.target.closest('.search-item');
        if (item && !e.target.closest('button')) {
            var rel = item.dataset.rel;
            var uid = parseInt(item.dataset.uid, 10);
            if (rel === 'friends') startDM(uid, item);
        }
    });

    /* ═══ START DIRECT MESSAGE ═══ */
    function startDM(userId, btn) {
        if (!userId) return;
        if (searchResults) searchResults.innerHTML = '<div class="search-loading">Starting chat…</div>';

        var body = new URLSearchParams();
        body.append('user_id', userId);
        if (CSRF) body.append('csrf', CSRF);

        fetch(BASE + '/Messages/start', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body.toString()
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success && data.conversation_id) {
                window.location.href = BASE + '/Messages?c=' + data.conversation_id;
            } else if (data.auto_friend_request) {
                toast('Friend request sent! Chat unlocks once they accept.', 'info');
                if (searchResults) searchResults.innerHTML = '<div class="search-empty">Friend request sent ✓</div>';
            } else {
                toast(data.error || 'Could not start chat', 'error');
                if (searchResults) searchResults.innerHTML = '<div class="search-error">' + esc(data.error || 'Failed') + '</div>';
            }
        })
        .catch(function (e) { toast('Error: ' + e.message, 'error'); });
    }

    /* ═══ ADD FRIEND ═══ */
    function addFriend(userId, btn) {
        if (!userId) return;
        btn.disabled = true;
        btn.textContent = '…';

        var body = new URLSearchParams();
        body.append('user_id', userId);
        if (CSRF) body.append('csrf', CSRF);

        fetch(BASE + '/Messages/friendRequest', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body.toString()
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                if (data.result === 'became_friends') {
                    toast('You are now friends! 💚', 'success');
                    btn.outerHTML = '<button type="button" class="btn-chat" data-action="chat" data-uid="' + userId + '">💬 Chat</button>';
                } else {
                    toast('Friend request sent!', 'success');
                    btn.outerHTML = '<button type="button" class="btn-pending" disabled>⏳ Pending</button>';
                }
            } else {
                toast(data.error || 'Failed', 'error');
                btn.disabled = false;
                btn.textContent = '➕ Add Friend';
            }
        })
        .catch(function (e) { toast('Error: ' + e.message, 'error'); });
    }

    /* ═══ ACCEPT FRIEND REQUEST (from search) ═══ */
    function acceptFriend(userId, btn) {
        if (!userId) return;
        btn.disabled = true;
        btn.textContent = '…';

        /* Accepting requires a request_id — but from search we only have user_id.
           The FriendsController has a dedicated accept-by-request-id endpoint.
           For simplicity, send a friend request back — which auto-accepts. */
        var body = new URLSearchParams();
        body.append('user_id', userId);
        if (CSRF) body.append('csrf', CSRF);

        fetch(BASE + '/Messages/friendRequest', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body.toString()
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success && data.result === 'became_friends') {
                toast('You are now friends! 💚', 'success');
                btn.outerHTML = '<button type="button" class="btn-chat" data-action="chat" data-uid="' + userId + '">💬 Chat</button>';
            } else {
                toast(data.error || 'Failed', 'error');
                btn.disabled = false;
                btn.textContent = '✓ Accept';
            }
        })
        .catch(function () { toast('Network error', 'error'); });
    }

    /* ═══ SEND MESSAGE ═══ */
    var chatForm = document.getElementById('chatForm');
    var msgInput = document.getElementById('msgInput');
    var sendBtn  = document.getElementById('sendBtn');

    if (chatForm) {
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var content = (msgInput.value || '').trim();
            if (!content) return false;

            var convId = chatForm.querySelector('input[name="conversation_id"]').value;

            sendBtn.disabled = true;
            var body = new URLSearchParams();
            body.append('conversation_id', convId);
            body.append('content', content);
            body.append('type', 'text');
            if (CSRF) body.append('csrf', CSRF);

            fetch(BASE + '/Messages/send', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body.toString()
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success && data.message) {
                    appendMessage(data.message);
                    msgInput.value = '';
                    msgInput.style.height = 'auto';
                } else {
                    toast(data.error || 'Send failed', 'error');
                }
            })
            .catch(function (e) { toast('Error: ' + e.message, 'error'); })
            .finally(function () {
                sendBtn.disabled = false;
                msgInput.focus();
            });
        });

        msgInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        msgInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            }
        });
    }

    function appendMessage(m) {
        var box = document.getElementById('chatMessages');
        if (!box) return;
        var div = document.createElement('div');
        div.className = 'msg-row out';
        div.setAttribute('data-id', m.id);
        div.innerHTML = '<div class="msg-bubble">' +
            '<div class="msg-text">' + esc(m.content).replace(/\n/g, '<br>') + '</div>' +
            '<div class="msg-meta"><span class="msg-time">' + esc(m.time || 'now') + '</span></div>' +
        '</div>';
        box.appendChild(div);
        box.scrollTop = box.scrollHeight;
    }

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function toast(msg, type) {
        var t = document.createElement('div');
        t.className = 'toast ' + (type || 'info');
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { t.classList.add('show'); }, 10);
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { t.remove(); }, 300);
        }, 2600);
    }

    /* ═══ EMOJI PICKER ═══ */
    var emojiBtn = document.getElementById('emojiBtn');
    var emojiPalette = document.getElementById('emojiPalette');
    if (emojiBtn && emojiPalette && msgInput) {
        emojiBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            emojiPalette.hidden = !emojiPalette.hidden;
        });
        emojiPalette.querySelectorAll('.emoji-btn').forEach(function (b) {
            b.addEventListener('click', function () {
                msgInput.value += this.textContent;
                msgInput.focus();
            });
        });
        document.addEventListener('click', function (e) {
            if (!emojiPalette.contains(e.target) && e.target !== emojiBtn) {
                emojiPalette.hidden = true;
            }
        });
    }

    /* Scroll chat to bottom on load */
    var box = document.getElementById('chatMessages');
    if (box) box.scrollTop = box.scrollHeight;
})();
</script>