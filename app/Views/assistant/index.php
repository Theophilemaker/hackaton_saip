<?php
function renderMd(string $text): string {
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/^[•\-]\s+(.+)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
    return nl2br($text);
}

// Ensure greeting shows if history is empty
$history = $data['history'] ?? [];
$userName = $_SESSION['name'] ?? 'Farmer';
$userDistrict = $data['profile']['district'] ?? null;
?>

<div class="dashboard-layout assistant-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="main-content">

            <!-- HERO -->
            <section class="glass-card chat-hero">
                <div class="hero-content">
                    <span class="hero-tag">🤖 AI ASSISTANT</span>
                    <h1>Farm Assistant</h1>
                    <p>Ask me anything about your farm — English or Kinyarwanda.</p>
                </div>
                <button type="button" class="btn-hero-secondary" id="btnReset">🔄 New Chat</button>
            </section>

            <!-- CHAT WRAPPER -->
            <section class="glass-card chat-wrap">

                <!-- MESSAGES -->
                <div class="chat-messages" id="chatMessages">
                    <?php if (!empty($history)): ?>
                        <?php foreach ($history as $m): ?>
                            <?php $isUser = ($m['role'] ?? '') === 'user'; ?>
                            <div class="msg-row <?= $isUser ? 'user' : 'assistant' ?>">
                                <div class="msg-avatar"><?= $isUser ? '👤' : '🌱' ?></div>
                                <div class="msg-bubble">
                                    <?= $isUser ? e($m['content']) : renderMd($m['content']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback greeting if DB returned nothing -->
                        <div class="msg-row assistant">
                            <div class="msg-avatar">🌱</div>
                            <div class="msg-bubble">
                                <strong>Muraho, <?= e($userName) ?>! 👋</strong><br><br>
                                I'm your AI farm assistant. Ask me anything about your crops, market prices, or farming practices — in <strong>English</strong> or <strong>Kinyarwanda</strong>.
                                <?php if ($userDistrict): ?>
                                    <br><br>I see you farm in <strong><?= e($userDistrict) ?></strong>.
                                <?php endif; ?>
                                <div class="msg-actions">
                                    <button type="button" class="msg-action-btn" data-send="What should I plant this season?">🌾 What to plant</button>
                                    <button type="button" class="msg-action-btn" data-send="Show me current market prices">💰 Prices</button>
                                    <button type="button" class="msg-action-btn" data-send="How does my yield compare?">📊 My yield</button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TYPING INDICATOR -->
                <div class="typing-row" id="typingIndicator">
                    <div class="msg-avatar">🌱</div>
                    <div class="typing-dots"><span></span><span></span><span></span></div>
                </div>

                <!-- QUICK REPLIES -->
                <div class="quick-replies" id="quickReplies">
                    <span class="qr-label">Quick questions:</span>
                    <button type="button" class="qr-btn" data-send="What should I plant this season?">🌾 What to plant</button>
                    <button type="button" class="qr-btn" data-send="Show me current market prices">💰 Market prices</button>
                    <button type="button" class="qr-btn" data-send="How does my yield compare to my district?">📊 My yield</button>
                    <button type="button" class="qr-btn" data-send="Where can I sell my produce?">🏪 Where to sell</button>
                    <button type="button" class="qr-btn" data-send="I have a pest problem in my crops">🐛 Pest help</button>
                    <button type="button" class="qr-btn" data-send="What loans can I apply for?">💳 Loans</button>
                    <button type="button" class="qr-btn" data-send="help">❓ Help</button>
                </div>

                <!-- INPUT -->
                <form class="chat-input-row" id="chatForm" autocomplete="off">
                    <input type="text" id="chatInput"
                           placeholder="Ask me anything… e.g., 'What should I plant?'"
                           autocomplete="off" required maxlength="500">
                    <button type="submit" class="btn-send" id="sendBtn" title="Send">➤</button>
                </form>
            </section>

        </main>
    </div>
</div>

<script>
(function() {
    'use strict';

    var chatMessages = document.getElementById('chatMessages');
    var chatForm     = document.getElementById('chatForm');
    var chatInput    = document.getElementById('chatInput');
    var sendBtn      = document.getElementById('sendBtn');
    var typing       = document.getElementById('typingIndicator');
    var quickReplies = document.getElementById('quickReplies');
    var btnReset     = document.getElementById('btnReset');
    var BASE         = '<?= BASE_URL ?>';

    var sending = false;

    function scrollBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    setTimeout(scrollBottom, 80);

    // ═══════ MARKDOWN ═══════
    function renderMd(text) {
        var h = String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
        h = h.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        h = h.replace(/^[•\-]\s+(.+)$/gm, '<li>$1</li>');
        h = h.replace(/(<li>[\s\S]*?<\/li>)/g, '<ul>$1</ul>');
        h = h.replace(/\n/g, '<br>');
        return h;
    }

    function escapeHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // ═══════ APPEND MESSAGE ═══════
    function appendMessage(role, content, actions) {
        var row = document.createElement('div');
        row.className = 'msg-row ' + role;

        var av = document.createElement('div');
        av.className = 'msg-avatar';
        av.textContent = role === 'user' ? '👤' : '🌱';

        var bubble = document.createElement('div');
        bubble.className = 'msg-bubble';
        bubble.innerHTML = role === 'user' ? escapeHtml(content) : renderMd(content);

        if (actions && actions.length) {
            var wrap = document.createElement('div');
            wrap.className = 'msg-actions';
            actions.forEach(function (a) {
                if (a.url) {
                    var l = document.createElement('a');
                    l.href = a.url;
                    l.className = 'msg-action-btn';
                    l.textContent = a.label;
                    wrap.appendChild(l);
                } else {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'msg-action-btn';
                    b.textContent = a.label;
                    b.setAttribute('data-send', a.message);
                    b.addEventListener('click', function () { sendMessage(a.message); });
                    wrap.appendChild(b);
                }
            });
            bubble.appendChild(wrap);
        }

        row.appendChild(av);
        row.appendChild(bubble);
        chatMessages.appendChild(row);
        scrollBottom();
    }

    // ═══════ SEND ═══════
    async function sendMessage(text) {
        text = String(text || '').trim();
        if (!text || sending) return;

        sending = true;
        appendMessage('user', text);
        chatInput.value = '';
        if (quickReplies) quickReplies.style.display = 'none';

        typing.classList.add('visible');
        scrollBottom();

        sendBtn.disabled = true;
        chatInput.disabled = true;

        try {
            var fd = new FormData();
            fd.append('message', text);

            var res = await fetch(BASE + '/assistant/send', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            var raw = await res.text();
            typing.classList.remove('visible');

            var data;
            try {
                data = JSON.parse(raw);
            } catch (e) {
                appendMessage('assistant', '⚠️ Server error (not JSON). Check console.');
                console.error('Non-JSON response:', raw.substring(0, 500));
                return;
            }

            if (data.success) {
                appendMessage('assistant', data.reply, data.actions);
            } else {
                appendMessage('assistant', '⚠️ ' + (data.error || 'Unknown error.'));
            }
        } catch (err) {
            typing.classList.remove('visible');
            appendMessage('assistant', '⚠️ Network error: ' + err.message);
            console.error(err);
        } finally {
            sending = false;
            sendBtn.disabled = false;
            chatInput.disabled = false;
            chatInput.focus();
            scrollBottom();
        }
    }

    // ═══════ EVENTS ═══════
    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        sendMessage(chatInput.value);
    });

    // Delegate all "data-send" clicks (quick replies + message action buttons)
    document.addEventListener('click', function (e) {
        var target = e.target.closest('[data-send]');
        if (!target) return;
        e.preventDefault();
        sendMessage(target.getAttribute('data-send'));
    });

    if (btnReset) {
        btnReset.addEventListener('click', async function () {
            if (!confirm('Start a new conversation?')) return;
            try {
                await fetch(BASE + '/assistant/reset', { method: 'POST', credentials: 'same-origin' });
                location.reload();
            } catch (err) {
                alert('Reset failed: ' + err.message);
            }
        });
    }

    setTimeout(function () { chatInput.focus(); }, 300);
})();
</script>

<style>

/* ═══════════════════════════════════════════════════════════
   AGGRESSIVE RESET — beat every global CSS rule
   Uses body + element + class for max specificity
   ═══════════════════════════════════════════════════════════ */

body,
body html {
    margin: 0 !important;
    padding: 0 !important;
    height: 100% !important;
    width: 100% !important;
    max-width: 100% !important;
    overflow: hidden !important;
}

/* Force every ancestor of the chat to full width */
body .dashboard-layout,
body .assistant-page {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 100% !important;
    height: 100vh !important;
    min-height: 100vh !important;
    max-height: 100vh !important;
    background: #0b1120 !important;
    overflow: hidden !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
}

body .assistant-page .main-wrap {
    display: flex !important;
    flex-direction: column !important;
    margin-left: 260px !important;
    margin-right: 0 !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    min-width: 0 !important;
    height: 100vh !important;
    max-height: 100vh !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
    padding: 0 !important;
}

body .assistant-page .main-content {
    flex: 1 1 auto !important;
    display: flex !important;
    flex-direction: column !important;
    padding: 16px 24px !important;
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    min-height: 0 !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
    gap: 14px !important;
}

/* Cards — full width */
body .assistant-page .glass-card,
body .assistant-page .chat-hero,
body .assistant-page .chat-wrap {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
    background: rgba(30, 41, 59, 0.75) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 18px !important;
}

/* HERO */
body .assistant-page .chat-hero {
    flex: 0 0 auto !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 16px !important;
    flex-wrap: wrap !important;
    padding: 14px 22px !important;
    background: linear-gradient(135deg, rgba(167, 139, 250, 0.18), rgba(56, 189, 248, 0.1)) !important;
}

body .assistant-page .hero-content { flex: 1 !important; min-width: 200px !important; }

body .assistant-page .hero-tag {
    display: inline-block !important;
    padding: 3px 10px !important;
    background: rgba(167, 139, 250, 0.2) !important;
    color: #a78bfa !important;
    border: 1px solid rgba(167, 139, 250, 0.4) !important;
    border-radius: 999px !important;
    font-size: 10px !important;
    font-weight: 800 !important;
    letter-spacing: 1px !important;
    text-transform: uppercase !important;
    margin-bottom: 6px !important;
}

body .assistant-page .chat-hero h1 {
    font-size: 20px !important;
    font-weight: 800 !important;
    color: #f1f5f9 !important;
    margin: 0 0 2px 0 !important;
}

body .assistant-page .chat-hero p {
    color: #94a3b8 !important;
    font-size: 12.5px !important;
    margin: 0 !important;
}

body .assistant-page .btn-hero-secondary {
    padding: 9px 16px !important;
    background: rgba(255, 255, 255, 0.06) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.14) !important;
    border-radius: 10px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    white-space: nowrap !important;
    transition: all 0.2s !important;
}

/* CHAT WRAPPER */
body .assistant-page .chat-wrap {
    flex: 1 1 auto !important;
    display: flex !important;
    flex-direction: column !important;
    padding: 0 !important;
    margin: 0 !important;
    min-height: 0 !important;
    overflow: hidden !important;
}

/* MESSAGES — the critical area */
body .assistant-page .chat-messages {
    flex: 1 1 auto !important;
    min-height: 250px !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    padding: 20px !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 14px !important;
    background: rgba(0, 0, 0, 0.15) !important;
    scrollbar-width: thin !important;
    scrollbar-color: rgba(255, 255, 255, 0.2) transparent !important;
}

body .assistant-page .chat-messages::-webkit-scrollbar { width: 8px !important; }
body .assistant-page .chat-messages::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2) !important;
    border-radius: 999px !important;
}

/* MESSAGE ROWS */
body .assistant-page .msg-row {
    display: flex !important;
    gap: 10px !important;
    align-items: flex-start !important;
    width: 100% !important;
    flex-shrink: 0 !important;
}

body .assistant-page .msg-row.user { flex-direction: row-reverse !important; }

body .assistant-page .msg-avatar {
    width: 34px !important;
    height: 34px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #10b981, #38bdf8) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 16px !important;
    flex-shrink: 0 !important;
    box-shadow: 0 3px 10px rgba(16, 185, 129, 0.3) !important;
}

body .assistant-page .msg-row.user .msg-avatar {
    background: linear-gradient(135deg, #38bdf8, #a78bfa) !important;
    box-shadow: 0 3px 10px rgba(56, 189, 248, 0.3) !important;
}

body .assistant-page .msg-bubble {
    max-width: calc(100% - 60px) !important;
    padding: 12px 16px !important;
    border-radius: 14px !important;
    font-size: 13.5px !important;
    line-height: 1.55 !important;
    color: #f1f5f9 !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
    word-break: break-word !important;
    white-space: normal !important;
    height: auto !important;
    overflow: visible !important;
}

body .assistant-page .msg-row.assistant .msg-bubble {
    background: rgba(16, 185, 129, 0.1) !important;
    border-color: rgba(16, 185, 129, 0.25) !important;
    border-top-left-radius: 4px !important;
}

body .assistant-page .msg-row.user .msg-bubble {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.22), rgba(167, 139, 250, 0.16)) !important;
    border-color: rgba(56, 189, 248, 0.35) !important;
    border-top-right-radius: 4px !important;
}

body .assistant-page .msg-bubble ul {
    margin: 6px 0 !important;
    padding-left: 18px !important;
    list-style: disc !important;
}
body .assistant-page .msg-bubble li {
    margin: 3px 0 !important;
    color: #cbd5e1 !important;
}
body .assistant-page .msg-bubble strong { color: #38bdf8 !important; }
body .assistant-page .msg-row.user .msg-bubble strong { color: #ffffff !important; }

/* MESSAGE ACTIONS */
body .assistant-page .msg-actions {
    display: flex !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
    margin-top: 10px !important;
}

body .assistant-page .msg-action-btn {
    display: inline-flex !important;
    align-items: center !important;
    padding: 7px 13px !important;
    background: rgba(56, 189, 248, 0.14) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.35) !important;
    border-radius: 999px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    text-decoration: none !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
    white-space: nowrap !important;
}

body .assistant-page .msg-action-btn:hover {
    background: rgba(56, 189, 248, 0.28) !important;
    transform: translateY(-1px) !important;
}

/* TYPING INDICATOR */
body .assistant-page .typing-row {
    display: none !important;
    padding: 0 20px 10px !important;
    gap: 10px !important;
    align-items: center !important;
    flex-shrink: 0 !important;
}
body .assistant-page .typing-row.visible { display: flex !important; }

body .assistant-page .typing-dots {
    display: flex !important;
    gap: 5px !important;
    padding: 12px 16px !important;
    background: rgba(16, 185, 129, 0.1) !important;
    border-radius: 14px !important;
    border: 1px solid rgba(16, 185, 129, 0.25) !important;
}
body .assistant-page .typing-dots span {
    width: 7px !important;
    height: 7px !important;
    background: #34d399 !important;
    border-radius: 50% !important;
    animation: typingBounce 1.4s infinite both !important;
}
body .assistant-page .typing-dots span:nth-child(2) { animation-delay: 0.2s !important; }
body .assistant-page .typing-dots span:nth-child(3) { animation-delay: 0.4s !important; }

@keyframes typingBounce {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
    40%           { transform: scale(1); opacity: 1; }
}

/* QUICK REPLIES — full width horizontal flow */
body .assistant-page .quick-replies {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 12px 20px !important;
    border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
    background: rgba(0, 0, 0, 0.2) !important;
    flex-shrink: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

body .assistant-page .qr-label {
    color: #64748b !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    margin-right: 4px !important;
    white-space: nowrap !important;
}

body .assistant-page .qr-btn {
    display: inline-flex !important;
    align-items: center !important;
    padding: 7px 13px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 999px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    white-space: nowrap !important;
    transition: all 0.2s !important;
    line-height: 1.2 !important;
}

body .assistant-page .qr-btn:hover {
    background: rgba(56, 189, 248, 0.18) !important;
    color: #38bdf8 !important;
    border-color: rgba(56, 189, 248, 0.5) !important;
    transform: translateY(-1px) !important;
}

/* INPUT ROW */
body .assistant-page .chat-input-row {
    display: flex !important;
    gap: 10px !important;
    padding: 12px 20px !important;
    background: rgba(0, 0, 0, 0.3) !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    align-items: center !important;
    flex-shrink: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

body .assistant-page .chat-input-row input {
    flex: 1 !important;
    padding: 12px 18px !important;
    background: rgba(0, 0, 0, 0.4) !important;
    border: 1px solid rgba(255, 255, 255, 0.14) !important;
    border-radius: 999px !important;
    color: #f1f5f9 !important;
    font-size: 13.5px !important;
    font-family: inherit !important;
    outline: none !important;
    transition: all 0.2s !important;
    box-sizing: border-box !important;
    width: 100% !important;
    min-width: 0 !important;
}

body .assistant-page .chat-input-row input::placeholder { color: #64748b !important; }
body .assistant-page .chat-input-row input:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
    background: rgba(0, 0, 0, 0.5) !important;
}

body .assistant-page .btn-send {
    width: 44px !important;
    height: 44px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important;
    border: none !important;
    cursor: pointer !important;
    font-size: 17px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4) !important;
    flex-shrink: 0 !important;
    padding: 0 !important;
}

body .assistant-page .btn-send:hover:not(:disabled) {
    transform: scale(1.08) !important;
    box-shadow: 0 6px 22px rgba(16, 185, 129, 0.6) !important;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    body .assistant-page .main-wrap {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding-top: 64px !important;
    }
    body .assistant-page .main-content { padding: 10px !important; gap: 10px !important; }
    body .assistant-page .chat-hero { padding: 12px 14px !important; }
    body .assistant-page .chat-hero h1 { font-size: 17px !important; }
    body .assistant-page .chat-messages { padding: 14px !important; }
    body .assistant-page .msg-bubble { max-width: calc(100% - 50px) !important; font-size: 13px !important; }
    body .assistant-page .quick-replies { padding: 10px 14px !important; }
    body .assistant-page .qr-label { display: none !important; }
    body .assistant-page .chat-input-row { padding: 10px 14px !important; }
}
</style>
<!-- ═══════════════════════════════════════════════════════════
     NUCLEAR FIX — inline styles override ANY global CSS
     Applied by JS so nothing can beat it in specificity
     ═══════════════════════════════════════════════════════════ -->
<script>
(function forceFullWidth() {
    'use strict';

    function applyFix() {
        // Every ancestor between body and the chat gets full width
        var selectors = [
            'body',
            '.dashboard-layout',
            '.assistant-page',
            '.main-wrap',
            '.main-content',
            '.chat-hero',
            '.chat-wrap',
            '.chat-messages',
            '.quick-replies',
            '.chat-input-row'
        ];

        selectors.forEach(function (sel) {
            document.querySelectorAll(sel).forEach(function (el) {
                el.style.setProperty('max-width', 'none', 'important');
                el.style.setProperty('width', '100%', 'important');
                el.style.setProperty('min-width', '0', 'important');
                el.style.setProperty('box-sizing', 'border-box', 'important');
            });
        });

        // Remove any auto-centering margins on main
        document.querySelectorAll('.main-content, .main-wrap').forEach(function (el) {
            el.style.setProperty('margin-left', '0', 'important');
            el.style.setProperty('margin-right', '0', 'important');
        });

        // Offset only the outer wrapper by the sidebar width
        var wrapper = document.querySelector('.assistant-page .main-wrap');
        if (wrapper) {
            var isMobile = window.innerWidth <= 768;
            wrapper.style.setProperty('margin-left', isMobile ? '0' : '260px', 'important');
            wrapper.style.setProperty('width', isMobile ? '100%' : 'calc(100% - 260px)', 'important');
            wrapper.style.setProperty('max-width', isMobile ? '100%' : 'calc(100% - 260px)', 'important');
        }
    }

    // Apply on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyFix);
    } else {
        applyFix();
    }

    // Apply on resize (handles responsive)
    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(applyFix, 100);
    });

    // Apply again after a short delay (in case fonts/layout shift)
    setTimeout(applyFix, 300);
    setTimeout(applyFix, 800);
})();
</script>
