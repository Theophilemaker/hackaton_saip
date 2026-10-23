<?php
/* ═══════════════════════════════════════════════════════════════
   AgriBot — bulletproof view.
   Failsafe paths, failsafe language read, failsafe Gemini check.
   ═══════════════════════════════════════════════════════════════ */

/* Safe paths */
$sidebarFile = __DIR__ . '/../layouts/sidebar.php';
$topbarFile  = __DIR__ . '/../layouts/topbar.php';

/* Safe data */
$history = [];
if (isset($history) && is_array($history))          $history = $history;
elseif (isset($data['history']) && is_array($data['history'])) $history = $data['history'];

$userInitial = 'U';
if (!empty($_SESSION['name'])) {
    $userInitial = strtoupper(substr((string)$_SESSION['name'], 0, 1));
}

/* Language — never touch App\Core\Language here */
$agriLang = isset($_SESSION['lang']) ? (string)$_SESSION['lang'] : 'en';
if (!in_array($agriLang, ['en', 'rw', 'fr', 'sw'], true)) $agriLang = 'en';

/* Gemini check — must not throw */
$geminiReady = false;
try {
    if (class_exists('App\\Helpers\\GeminiHelper')) {
        $cls = 'App\\Helpers\\GeminiHelper';
        $g = new $cls();
        if (method_exists($g, 'isConfigured')) {
            $geminiReady = (bool)$g->isConfigured();
        }
    }
} catch (\Throwable $e) {
    $geminiReady = false;
}

/* Base URL safe fallback */
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="dashboard-layout">
    <?php if (is_file($sidebarFile)) require_once $sidebarFile; ?>
    <main class="main-content">
        <?php if (is_file($topbarFile)) require_once $topbarFile; ?>

        <section class="agribot-wrapper">

            <!-- ═══════ HEADER ═══════ -->
            <div class="agribot-header glass-card">
                <div class="bot-info">
                    <div class="bot-avatar">
                        <span class="bot-icon">🤖</span>
                        <span class="bot-status"></span>
                    </div>
                    <div>
                        <h2>AgriBot Assistant</h2>
                        <p>Your AI farming companion · Powered by NISR data, weather &amp; market trends</p>
                        <div class="bot-tags">
                            <?php if ($geminiReady): ?>
                                <span class="tag tag-live">✨ Gemini AI</span>
                            <?php else: ?>
                                <span class="tag tag-local">📚 Local knowledge</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="bot-actions">
                    <button type="button" onclick="exportChat()" class="btn-icon" title="Export chat">💾</button>
                    <button type="button" onclick="clearChat()" class="btn-icon" title="Clear chat">🗑️</button>
                    <button type="button" onclick="toggleLang()" class="btn-icon" title="Language">🌐</button>
                </div>
            </div>

            <!-- ═══════ MAIN ═══════ -->
            <div class="agribot-main glass-card">
                <div class="chat-messages" id="agriMessages">
                    <?php if (empty($history)): ?>
                        <div class="msg bot">
                            <div class="msg-avatar bot-av">🤖</div>
                            <div class="msg-body">
                                <div class="msg-content">
                                    <p>Hello! I'm <strong>AgriBot</strong> 🌱 — your AI farming assistant.</p>
                                    <p>I can help you with:</p>
                                    <ul class="bot-list">
                                        <li>🌾 Best planting times</li>
                                        <li>🧪 Fertilizer recommendations</li>
                                        <li>🐛 Disease &amp; pest detection</li>
                                        <li>🌦️ Weather-based advice</li>
                                        <li>💰 Market prices</li>
                                        <li>💧 Irrigation planning</li>
                                        <li>🏭 Post-harvest storage</li>
                                    </ul>
                                    <p>Pick a quick prompt below or type your own question 👇</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($history as $m):
                            if (!is_array($m)) continue;
                            $isUser = (($m['role'] ?? '') === 'user');
                        ?>
                            <div class="msg <?= $isUser ? 'user' : 'bot' ?>">
                                <div class="msg-avatar <?= $isUser ? 'user-av' : 'bot-av' ?>">
                                    <?= $isUser ? htmlspecialchars($userInitial) : '🤖' ?>
                                </div>
                                <div class="msg-body">
                                    <div class="msg-content"><?= nl2br(htmlspecialchars((string)($m['content'] ?? ''))) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <button type="button" class="scroll-btn" id="scrollBtn" onclick="scrollToBottom()" title="Scroll to latest">↓</button>

                <div class="quick-prompts" id="quickPrompts">
                    <button type="button" onclick="quickAsk('When should I plant maize?')">🌾 When to plant maize?</button>
                    <button type="button" onclick="quickAsk('What fertilizer for potatoes?')">🧪 Fertilizer for potatoes</button>
                    <button type="button" onclick="quickAsk('How to treat coffee rust?')">🐛 Treat coffee rust</button>
                    <button type="button" onclick="quickAsk('What is the price of beans?')">💰 Price of beans</button>
                    <button type="button" onclick="quickAsk('When should I irrigate tomatoes?')">💧 Irrigate tomatoes</button>
                    <button type="button" onclick="quickAsk('How do I store maize?')">🏭 Store maize</button>
                    <button type="button" onclick="quickAsk('What yield can I expect for coffee?')">📊 Coffee yield</button>
                    <button type="button" onclick="quickAsk('Tell me about government support programs')">🏛️ Government support</button>
                </div>

                <form class="chat-input-wrap" id="agriForm" onsubmit="return sendMessage(event)">
                    <button type="button" class="attach-btn" title="Voice input" onclick="startVoice()">🎤</button>
                    <div class="input-wrap">
                        <input type="text" id="agriInput"
                               placeholder="Ask AgriBot anything about your farm..."
                               autocomplete="off" maxlength="500" required>
                        <span class="char-count" id="charCount">0/500</span>
                    </div>
                    <button type="submit" class="send-btn" id="sendBtn">
                        <span class="send-icon">➤</span>
                    </button>
                </form>
            </div>
        </section>
    </main>
</div>

<style>
/* ═══════════ LAYOUT ═══════════ */
.agribot-wrapper {
    display: flex;
    flex-direction: column;
    gap: 16px;
    height: calc(100vh - 120px);
    min-height: 560px;
}
.agribot-wrapper .glass-card {
    background: rgba(30, 41, 59, 0.65);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    box-sizing: border-box;
    min-height: 0;
}

/* ═══════════ HEADER ═══════════ */
.agribot-header {
    padding: 20px 24px !important;
    max-width: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    background: linear-gradient(135deg, rgba(22, 163, 74, 0.15), rgba(124, 58, 237, 0.08));
    flex-wrap: wrap;
}
.bot-info { display: flex; gap: 16px; align-items: center; }
.bot-avatar {
    position: relative;
    width: 56px; height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #16A34A, #7C3AED);
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    box-shadow: 0 8px 24px rgba(34, 197, 94, 0.35);
    animation: botPulse 3s ease-in-out infinite;
    flex-shrink: 0;
}
@keyframes botPulse {
    0%, 100% { box-shadow: 0 8px 24px rgba(34, 197, 94, 0.35); }
    50%      { box-shadow: 0 8px 30px rgba(124, 58, 237, 0.5); }
}
.bot-status {
    position: absolute; bottom: 2px; right: 2px;
    width: 14px; height: 14px;
    background: #22C55E;
    border: 3px solid #081C15;
    border-radius: 50%;
    animation: statusPulse 2s ease-in-out infinite;
}
@keyframes statusPulse { 0%,100%{opacity:1;} 50%{opacity:0.5;} }

.bot-info h2 {
    margin: 0 0 4px; font-size: 20px;
    background: linear-gradient(135deg, #22C55E, #7C3AED);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text; font-weight: 800;
}
.bot-info p { margin: 0; font-size: 12px; color: var(--text-sub); }

.bot-tags { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px; }
.tag {
    display: inline-flex; padding: 3px 10px;
    border-radius: 999px; font-size: 10.5px;
    font-weight: 700; letter-spacing: 0.3px;
}
.tag-live  { background: rgba(124, 58, 237, 0.18); color: #a78bfa; border: 1px solid rgba(124, 58, 237, 0.35); }
.tag-local { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }

.bot-actions { display: flex; gap: 8px; }
.btn-icon {
    width: 40px; height: 40px; border-radius: 12px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border, rgba(255,255,255,0.1));
    color: var(--text-sub, #94a3b8);
    font-size: 16px; cursor: pointer; transition: 200ms;
    font-family: inherit; padding: 0;
}
.btn-icon:hover {
    background: rgba(34, 197, 94, 0.15);
    border-color: var(--accent, #84cc16);
    color: var(--accent, #84cc16);
    transform: translateY(-2px);
}

/* ═══════════ CHAT AREA ═══════════ */
.agribot-main {
    flex-grow: 1;
    padding: 0 !important;
    max-width: none;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
    min-height: 0;
}

.chat-messages {
    flex-grow: 1;
    overflow-y: auto;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    scroll-behavior: smooth;
    min-height: 0;
}
.chat-messages::-webkit-scrollbar { width: 6px; }
.chat-messages::-webkit-scrollbar-thumb { background: rgba(132, 204, 22, 0.3); border-radius: 3px; }

.msg { display: flex; gap: 12px; max-width: 80%; animation: msgIn 0.3s ease-out; }
@keyframes msgIn { from { opacity:0; transform: translateY(10px);} to { opacity:1; transform: translateY(0);} }
.msg.user { align-self: flex-end; flex-direction: row-reverse; }
.msg.bot  { align-self: flex-start; }

.msg-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 14px; flex-shrink: 0;
}
.user-av { background: linear-gradient(135deg, #0EA5E9, #6366F1); color: white; }
.bot-av  { background: linear-gradient(135deg, #16A34A, #7C3AED); font-size: 20px; }

.msg-body {
    padding: 14px 18px;
    border-radius: 16px;
    font-size: 13.5px;
    line-height: 1.65;
    position: relative;
    min-width: 0;
    word-wrap: break-word;
    overflow-wrap: anywhere;
}
.msg.bot .msg-body {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid var(--border, rgba(255,255,255,0.08));
    border-top-left-radius: 6px;
    color: var(--text-main, #f1f5f9);
}
.msg.user .msg-body {
    background: linear-gradient(135deg, #16A34A, #22C55E);
    color: white;
    border-top-right-radius: 6px;
    box-shadow: 0 4px 14px rgba(34, 197, 94, 0.3);
}
.msg-content p { margin: 0 0 8px; }
.msg-content p:last-child { margin-bottom: 0; }
.msg-content strong { color: var(--accent, #84cc16); font-weight: 700; }
.msg-content em { color: #a78bfa; font-style: italic; }
.msg.user .msg-content strong { color: #fff; }
.msg.user .msg-content em { color: #fff; }

.msg-meta {
    display: flex; align-items: center; gap: 8px;
    margin-top: 6px; font-size: 10.5px; color: #64748b;
    opacity: 0; transition: opacity 0.2s;
}
.msg:hover .msg-meta { opacity: 1; }
.msg.user .msg-meta { justify-content: flex-end; color: rgba(255,255,255,0.65); }
.copy-msg {
    background: transparent; border: none; color: inherit;
    cursor: pointer; font-size: 11px; padding: 2px 6px;
    border-radius: 4px; font-family: inherit; transition: 200ms;
}
.copy-msg:hover { background: rgba(255, 255, 255, 0.1); }

.bot-list { margin: 8px 0; padding-left: 0; list-style: none; }
.bot-list li { padding: 4px 0; font-size: 13px; }

/* Quick prompts — now WRAP inside the box */
.quick-prompts {
    padding: 14px 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    border-top: 1px solid var(--border, rgba(255,255,255,0.08));
    background: rgba(0, 0, 0, 0.15);
    max-height: 140px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(132, 204, 22, 0.3) transparent;
    flex-shrink: 0;
}
.quick-prompts::-webkit-scrollbar { width: 5px; }
.quick-prompts::-webkit-scrollbar-thumb { background: rgba(132, 204, 22, 0.3); border-radius: 3px; }
.quick-prompts button {
    padding: 8px 14px;
    background: rgba(132, 204, 22, 0.1);
    border: 1px solid rgba(132, 204, 22, 0.3);
    color: var(--accent, #84cc16);
    border-radius: 20px;
    font-size: 11.5px;
    cursor: pointer;
    white-space: nowrap;
    font-family: inherit;
    transition: 250ms;
    font-weight: 500;
    flex-shrink: 0;
}
.quick-prompts button:hover {
    background: rgba(132, 204, 22, 0.22);
    border-color: var(--accent, #84cc16);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(132, 204, 22, 0.2);
}

.scroll-btn {
    position: absolute;
    right: 24px;
    bottom: 160px;
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #16A34A, #7C3AED);
    border: none; color: #fff; font-size: 18px;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
    opacity: 0; pointer-events: none;
    transition: 250ms; z-index: 5;
    font-family: inherit; padding: 0;
}
.scroll-btn.visible { opacity: 1; pointer-events: auto; }
.scroll-btn:hover { transform: scale(1.1); }

/* ═══════════ INPUT ═══════════ */
.chat-input-wrap {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 20px;
    border-top: 1px solid var(--border, rgba(255,255,255,0.08));
    background: rgba(0, 0, 0, 0.2);
    flex-shrink: 0;
}
.attach-btn {
    background: transparent; border: none;
    font-size: 20px; cursor: pointer;
    padding: 8px; border-radius: 10px;
    transition: 200ms; opacity: 0.7;
    flex-shrink: 0;
}
.attach-btn:hover { opacity: 1; background: rgba(255, 255, 255, 0.05); }
.attach-btn.recording { background: rgba(239, 68, 68, 0.2); animation: statusPulse 1s infinite; }

.input-wrap { flex-grow: 1; position: relative; min-width: 0; }
#agriInput {
    width: 100%;
    padding: 12px 60px 12px 18px;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid var(--border, rgba(255,255,255,0.08));
    border-radius: 24px;
    color: white;
    font-family: inherit;
    font-size: 14px;
    outline: none;
    transition: 250ms;
    box-sizing: border-box;
}
#agriInput::placeholder { color: rgba(200, 213, 209, 0.5); }
#agriInput:focus {
    border-color: var(--accent, #84cc16);
    box-shadow: 0 0 0 3px rgba(132, 204, 22, 0.15);
    background: rgba(0, 0, 0, 0.4);
}

.char-count {
    position: absolute; right: 16px; top: 50%;
    transform: translateY(-50%);
    font-size: 10.5px; color: #64748b;
    pointer-events: none;
    font-variant-numeric: tabular-nums; font-weight: 600;
}
.char-count.warn   { color: #fbbf24; }
.char-count.danger { color: #f87171; }

.send-btn {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #16A34A, #7C3AED);
    border: none; color: white; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: 250ms; font-size: 16px;
    flex-shrink: 0; padding: 0; font-family: inherit;
}
.send-btn:hover:not(:disabled) { transform: scale(1.08); box-shadow: 0 6px 20px rgba(124, 58, 237, 0.45); }
.send-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

.typing-indicator { display: flex; gap: 4px; padding: 8px 4px; align-items: center; }
.typing-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--accent, #84cc16);
    animation: typingBounce 1.4s infinite;
}
.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes typingBounce {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30% { transform: translateY(-6px); opacity: 1; }
}

@media (max-width: 700px) {
    .agribot-wrapper { height: calc(100vh - 90px); min-height: 480px; gap: 10px; }
    .msg { max-width: 95%; }
    .agribot-header { flex-direction: column; align-items: flex-start; padding: 14px 16px !important; }
    .bot-info h2 { font-size: 17px; }
    .chat-messages { padding: 16px; gap: 12px; }
    .bot-actions { align-self: stretch; justify-content: flex-end; }
    .quick-prompts { padding: 10px 14px; max-height: 110px; }
    .quick-prompts button { font-size: 11px; padding: 7px 12px; }
    .chat-input-wrap { padding: 12px 14px; }
    .scroll-btn { bottom: 140px; right: 18px; width: 36px; height: 36px; font-size: 16px; }
    #agriInput { padding: 11px 54px 11px 14px; font-size: 13.5px; }
}
</style>

<script>
(function () {
    'use strict';

    var BASE              = <?= json_encode($baseUrl) ?>;
    var AGRI_LANG         = <?= json_encode($agriLang) ?>;
    var AGRI_USER_INITIAL = <?= json_encode($userInitial) ?>;
    var AGRI_ENDPOINT     = BASE + '/crops/agribot';

    /* ═══ Character counter + scroll button ═══ */
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('agriInput');
        var counter = document.getElementById('charCount');
        if (input && counter) {
            input.addEventListener('input', function () {
                var len = this.value.length;
                counter.textContent = len + '/500';
                counter.classList.toggle('warn',   len > 400);
                counter.classList.toggle('danger', len > 480);
            });
            input.focus();
        }
        var chatBox = document.getElementById('agriMessages');
        var scrollBtn = document.getElementById('scrollBtn');
        if (chatBox && scrollBtn) {
            chatBox.addEventListener('scroll', function () {
                var nearBottom = (chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight) < 100;
                scrollBtn.classList.toggle('visible', !nearBottom);
            });
        }
    });

    window.quickAsk = function (text) {
        var input = document.getElementById('agriInput');
        if (!input) return;
        input.value = text;
        input.dispatchEvent(new Event('input'));
        var form = document.getElementById('agriForm');
        if (form) form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
    };

    window.sendMessage = async function (e) {
        e.preventDefault();
        var input = document.getElementById('agriInput');
        var btn   = document.getElementById('sendBtn');
        if (!input || !btn) return false;
        var msg = input.value.trim();
        if (!msg) return false;

        appendMessage('user', msg);
        input.value = '';
        var counter = document.getElementById('charCount');
        if (counter) { counter.textContent = '0/500'; counter.classList.remove('warn', 'danger'); }
        btn.disabled = true;

        var typing = appendTyping();

        try {
            var res = await fetch(AGRI_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'message=' + encodeURIComponent(msg),
                credentials: 'same-origin'
            });
            var text = await res.text();
            var data;
            try { data = JSON.parse(text); }
            catch (pe) {
                typing.remove();
                appendMessage('bot', '⚠️ Server returned non-JSON. Check /crops/agribot endpoint.');
                btn.disabled = false;
                return false;
            }
            typing.remove();

            if (data.success && data.reply) {
                appendMessage('bot', data.reply);
            } else if (data.error) {
                appendMessage('bot', '⚠️ ' + data.error);
            } else {
                appendMessage('bot', "Sorry, I couldn't process that. Please try again. 🌱");
            }
        } catch (err) {
            typing.remove();
            appendMessage('bot', 'Connection error. Please check your network. 🌱');
        }
        btn.disabled = false;
        input.focus();
        return false;
    };

    function appendMessage(role, text) {
        var box = document.getElementById('agriMessages');
        if (!box) return;
        var div = document.createElement('div');
        div.className = 'msg ' + (role === 'user' ? 'user' : 'bot');
        var time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        div.innerHTML =
            '<div class="msg-avatar ' + (role === 'user' ? 'user-av' : 'bot-av') + '">' +
                (role === 'user' ? AGRI_USER_INITIAL : '🤖') +
            '</div>' +
            '<div class="msg-body">' +
                '<div class="msg-content">' + formatReply(text) + '</div>' +
                '<div class="msg-meta">' +
                    '<span>' + time + '</span>' +
                    '<button type="button" class="copy-msg" onclick="copyMsg(this)">📋 Copy</button>' +
                '</div>' +
            '</div>';
        box.appendChild(div);
        box.scrollTop = box.scrollHeight;
    }

    window.copyMsg = function (btn) {
        var content = btn.closest('.msg-body').querySelector('.msg-content').innerText;
        navigator.clipboard.writeText(content).then(function () {
            btn.textContent = '✅ Copied';
            setTimeout(function () { btn.textContent = '📋 Copy'; }, 1500);
        }).catch(function () {
            var ta = document.createElement('textarea');
            ta.value = content;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            ta.remove();
            btn.textContent = '✅ Copied';
            setTimeout(function () { btn.textContent = '📋 Copy'; }, 1500);
        });
    };

    function appendTyping() {
        var box = document.getElementById('agriMessages');
        var div = document.createElement('div');
        div.className = 'msg bot';
        div.innerHTML =
            '<div class="msg-avatar bot-av">🤖</div>' +
            '<div class="msg-body"><div class="typing-indicator">' +
                '<span class="typing-dot"></span>' +
                '<span class="typing-dot"></span>' +
                '<span class="typing-dot"></span>' +
            '</div></div>';
        box.appendChild(div);
        box.scrollTop = box.scrollHeight;
        return div;
    }

    function formatReply(text) {
        if (!text) return '';
        var s = String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
        s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        s = s.replace(/(?<!\*)\*([^*\n]+)\*(?!\*)/g, '<em>$1</em>');
        s = s.replace(/\n/g, '<br>');
        return s;
    }

    window.scrollToBottom = function () {
        var box = document.getElementById('agriMessages');
        if (box) box.scrollTop = box.scrollHeight;
    };

    window.clearChat = function () {
        if (!confirm('Clear this conversation?')) return;
        var box = document.getElementById('agriMessages');
        if (box) box.innerHTML = '';
        appendMessage('bot', "Chat cleared. How can I help you with your farm today? 🌱");
    };

    window.exportChat = function () {
        var msgs = document.querySelectorAll('#agriMessages .msg');
        if (!msgs.length) return alert('Nothing to export yet.');
        var out = 'AgriBot Conversation — ' + new Date().toLocaleString() + '\n' + '═'.repeat(60) + '\n\n';
        msgs.forEach(function (m) {
            var isUser = m.classList.contains('user');
            var content = m.querySelector('.msg-content').innerText.trim();
            out += (isUser ? 'YOU: ' : 'AGRIBOT: ') + content + '\n\n';
        });
        var blob = new Blob([out], { type: 'text/plain;charset=utf-8' });
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href = url;
        a.download = 'agribot-chat-' + Date.now() + '.txt';
        a.click();
        URL.revokeObjectURL(url);
    };

    var recognition = null;
    window.startVoice = function () {
        var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SR) return alert('Voice input not supported in this browser.');
        var btn = document.querySelector('.attach-btn');
        if (recognition) { recognition.stop(); recognition = null; btn.classList.remove('recording'); return; }
        recognition = new SR();
        recognition.lang = ({ rw: 'rw-RW', fr: 'fr-FR', sw: 'sw-KE' })[AGRI_LANG] || 'en-US';
        recognition.interimResults = false;
        btn.classList.add('recording');
        recognition.onresult = function (ev) {
            var input = document.getElementById('agriInput');
            input.value = ev.results[0][0].transcript;
            input.dispatchEvent(new Event('input'));
            btn.classList.remove('recording');
            recognition = null;
        };
        recognition.onerror = function () { btn.classList.remove('recording'); recognition = null; };
        recognition.onend   = function () { btn.classList.remove('recording'); recognition = null; };
        recognition.start();
    };

    window.toggleLang = function () {
        var order = ['en', 'rw', 'fr', 'sw'];
        var names = { en: 'English', rw: 'Kinyarwanda', fr: 'Français', sw: 'Kiswahili' };
        var next  = order[(order.indexOf(AGRI_LANG) + 1) % order.length];
        if (confirm('Switch AgriBot language to ' + names[next] + '?')) {
            window.location.href = BASE + '/language/switch?lang=' + next + '&redirect=' + encodeURIComponent(window.location.pathname);
        }
    };
})();
</script>