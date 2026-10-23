<?php
function renderMarkdown(string $text): string {
    // Very lightweight markdown → HTML
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    // Bold
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    // Bullet lists
    $text = preg_replace('/^[•\-]\s+(.+)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
    // Line breaks
    $text = nl2br($text);
    return $text;
}
?>
<div class="dashboard-layout advice-page chat-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card chat-hero">
            <div class="hero-content">
                <span class="hero-tag">🤖 AI Assistant</span>
                <h1>Farm Assistant</h1>
                <p>Ask anything about your farm — in English or Kinyarwanda.</p>
            </div>
            <button type="button" class="btn-hero-secondary" onclick="resetChat()">🔄 New Conversation</button>
        </section>

        <section class="glass-card chat-wrap">
            <!-- Message area -->
            <div class="chat-messages" id="chatMessages">
                <?php foreach ($data['history'] as $m): ?>
                    <?php $isUser = $m['role'] === 'user'; ?>
                    <div class="msg-row <?= $isUser ? 'user' : 'assistant' ?>">
                        <div class="msg-avatar">
                            <?= $isUser ? '👤' : '🌱' ?>
                        </div>
                        <div class="msg-bubble">
                            <?= $isUser ? e($m['content']) : renderMarkdown($m['content']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Typing indicator -->
            <div class="typing" id="typingIndicator" style="display:none;">
                <div class="msg-avatar">🌱</div>
                <div class="typing-dots"><span></span><span></span><span></span></div>
            </div>

            <!-- Quick replies -->
            <div class="quick-replies" id="quickReplies">
                <?php foreach ($data['quick_replies'] as $qr): ?>
                    <button type="button" class="qr-btn" onclick="sendQuick('<?= e(addslashes($qr['message'])) ?>')">
                        <?= e($qr['label']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Input -->
            <form class="chat-input-row" id="chatForm" onsubmit="sendMessage(event)">
                <input type="text"
                       id="chatInput"
                       placeholder="Ask me anything... e.g., 'What should I plant?'"
                       autocomplete="off"
                       required
                       maxlength="500">
                <button type="submit" class="btn-send" id="sendBtn">
                    <span>➤</span>
                </button>
            </form>
        </section>
    </main>
</div>

<script>
const chatMessages = document.getElementById('chatMessages');
const chatForm     = document.getElementById('chatForm');
const chatInput    = document.getElementById('chatInput');
const sendBtn      = document.getElementById('sendBtn');
const typing       = document.getElementById('typingIndicator');

// Auto-scroll
function scrollBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}
scrollBottom();

// Send message
async function sendMessage(e) {
    if (e) e.preventDefault();
    const text = chatInput.value.trim();
    if (!text) return;

    // Append user message instantly
    appendMessage('user', text);
    chatInput.value = '';
    hideQuickReplies();
    typing.style.display = 'flex';
    scrollBottom();

    // Disable while waiting
    sendBtn.disabled = true;
    chatInput.disabled = true;

    try {
        const fd = new FormData();
        fd.append('message', text);

        const res = await fetch('<?= BASE_URL ?>/chat/send', { method: 'POST', body: fd });
        const data = await res.json();

        typing.style.display = 'none';

        if (data.success) {
            appendMessage('assistant', data.reply, data.actions);
        } else {
            appendMessage('assistant', '⚠️ ' + (data.error || 'Something went wrong.'));
        }
    } catch (err) {
        typing.style.display = 'none';
        appendMessage('assistant', '⚠️ Network error. Please try again.');
    } finally {
        sendBtn.disabled = false;
        chatInput.disabled = false;
        chatInput.focus();
        scrollBottom();
    }
}

// Render markdown (mirror of PHP renderMarkdown)
function renderMarkdown(text) {
    let html = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/^[•\-]\s+(.+)$/gm, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
    html = html.replace(/\n/g, '<br>');
    return html;
}

// Append a message to the UI
function appendMessage(role, content, actions) {
    const row = document.createElement('div');
    row.className = 'msg-row ' + role;

    const avatar = document.createElement('div');
    avatar.className = 'msg-avatar';
    avatar.textContent = role === 'user' ? '👤' : '🌱';

    const bubble = document.createElement('div');
    bubble.className = 'msg-bubble';
    bubble.innerHTML = role === 'user'
        ? escapeHtml(content)
        : renderMarkdown(content);

    // Add actions
    if (actions && actions.length) {
        const actionsWrap = document.createElement('div');
        actionsWrap.className = 'msg-actions';
        actions.forEach(a => {
            if (a.url) {
                const link = document.createElement('a');
                link.href = a.url;
                link.className = 'msg-action-btn';
                link.textContent = a.label;
                actionsWrap.appendChild(link);
            } else {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'msg-action-btn';
                btn.textContent = a.label;
                btn.onclick = () => sendQuick(a.message);
                actionsWrap.appendChild(btn);
            }
        });
        bubble.appendChild(actionsWrap);
    }

    row.appendChild(avatar);
    row.appendChild(bubble);
    chatMessages.appendChild(row);
}

function escapeHtml(s) {
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function sendQuick(msg) {
    chatInput.value = msg;
    sendMessage();
}

function hideQuickReplies() {
    const qr = document.getElementById('quickReplies');
    if (qr) qr.style.display = 'none';
}

async function resetChat() {
    if (!confirm('Start a new conversation? Your current chat history will be archived.')) return;
    await fetch('<?= BASE_URL ?>/chat/reset', { method: 'POST' });
    location.reload();
}
</script>

<style>
/* =========================================
   CHAT PAGE
   ========================================= */

.chat-page.dashboard-layout { display: flex !important; width: 100% !important; min-height: 100vh !important; background: #0b1120 !important; }
.chat-page .main-content { flex: 1 !important; margin-left: 260px !important; padding: 32px !important; box-sizing: border-box !important; min-width: 0 !important; display: flex !important; flex-direction: column !important; height: 100vh !important; overflow: hidden !important; }
.chat-page .glass-card { background: rgba(30,41,59,0.65) !important; backdrop-filter: blur(16px) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 20px !important; padding: 24px !important; margin-bottom: 16px !important; }

/* HERO */
.chat-hero { background: linear-gradient(135deg, rgba(167,139,250,0.15), rgba(56,189,248,0.08)) !important; display: flex !important; justify-content: space-between !important; align-items: center !important; gap: 20px !important; flex-wrap: wrap !important; flex-shrink: 0 !important; padding: 20px 24px !important; }
.hero-tag { display: inline-block !important; padding: 5px 14px !important; background: rgba(167,139,250,0.15) !important; color: #a78bfa !important; border: 1px solid rgba(167,139,250,0.3) !important; border-radius: 999px !important; font-size: 11px !important; font-weight: 700 !important; text-transform: uppercase !important; letter-spacing: 1px !important; margin-bottom: 10px !important; }
.chat-hero h1 { font-size: 24px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 6px 0 !important; }
.chat-hero p { color: #94a3b8 !important; font-size: 13.5px !important; margin: 0 !important; }
.btn-hero-secondary { padding: 10px 20px !important; background: rgba(255,255,255,0.05) !important; color: #cbd5e1 !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px !important; font-size: 12.5px !important; font-weight: 700 !important; cursor: pointer !important; font-family: inherit !important; }
.btn-hero-secondary:hover { background: rgba(255,255,255,0.1) !important; color: #f1f5f9 !important; }

/* CHAT WRAP */
.chat-wrap {
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    padding: 0 !important;
    overflow: hidden !important;
    margin-bottom: 0 !important;
}

/* MESSAGES */
.chat-messages {
    flex: 1 !important;
    overflow-y: auto !important;
    padding: 24px !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 18px !important;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.1) transparent;
}
.chat-messages::-webkit-scrollbar { width: 6px; }
.chat-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 999px; }

.msg-row { display: flex !important; gap: 12px !important; align-items: flex-start !important; animation: msgIn 0.3s ease-out; }
.msg-row.user { flex-direction: row-reverse !important; }
@keyframes msgIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

.msg-avatar {
    width: 38px !important;
    height: 38px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #10b981, #38bdf8) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 18px !important;
    flex-shrink: 0 !important;
    box-shadow: 0 4px 12px rgba(16,185,129,0.25) !important;
}
.msg-row.user .msg-avatar { background: linear-gradient(135deg, #38bdf8, #a78bfa) !important; }

.msg-bubble {
    max-width: 78% !important;
    padding: 14px 18px !important;
    border-radius: 16px !important;
    font-size: 13.5px !important;
    line-height: 1.6 !important;
    color: #f1f5f9 !important;
    background: rgba(255,255,255,0.04) !important;
    border: 1px solid rgba(255,255,255,0.06) !important;
    word-break: break-word !important;
    overflow-wrap: break-word !important;
}
.msg-row.assistant .msg-bubble {
    background: rgba(16,185,129,0.08) !important;
    border-color: rgba(16,185,129,0.2) !important;
    border-top-left-radius: 4px !important;
}
.msg-row.user .msg-bubble {
    background: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(167,139,250,0.15)) !important;
    border-color: rgba(56,189,248,0.3) !important;
    border-top-right-radius: 4px !important;
}
.msg-bubble ul { margin: 6px 0 !important; padding-left: 20px !important; }
.msg-bubble li { margin: 3px 0 !important; }
.msg-bubble strong { color: #38bdf8 !important; font-weight: 700 !important; }
.msg-row.user .msg-bubble strong { color: #fff !important; }

/* ACTION BUTTONS INSIDE MESSAGES */
.msg-actions { display: flex !important; gap: 8px !important; flex-wrap: wrap !important; margin-top: 12px !important; }
.msg-action-btn {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 8px 14px !important;
    background: rgba(56,189,248,0.12) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56,189,248,0.3) !important;
    border-radius: 999px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    text-decoration: none !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
}
.msg-action-btn:hover { background: rgba(56,189,248,0.25) !important; transform: translateY(-1px) !important; }

/* TYPING INDICATOR */
.typing {
    padding: 0 24px 12px !important;
    display: flex !important;
    gap: 12px !important;
    align-items: center !important;
}
.typing-dots { display: flex !important; gap: 5px !important; padding: 14px 18px !important; background: rgba(16,185,129,0.08) !important; border-radius: 16px !important; border: 1px solid rgba(16,185,129,0.2) !important; }
.typing-dots span {
    width: 8px !important;
    height: 8px !important;
    background: #34d399 !important;
    border-radius: 50% !important;
    animation: typingBounce 1.4s infinite both !important;
}
.typing-dots span:nth-child(2) { animation-delay: 0.2s !important; }
.typing-dots span:nth-child(3) { animation-delay: 0.4s !important; }
@keyframes typingBounce {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
    40% { transform: scale(1); opacity: 1; }
}

/* QUICK REPLIES */
.quick-replies {
    display: flex !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
    padding: 12px 24px !important;
    border-top: 1px solid rgba(255,255,255,0.06) !important;
    background: rgba(0,0,0,0.15) !important;
}
.qr-btn {
    padding: 8px 14px !important;
    background: rgba(255,255,255,0.04) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 999px !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    font-family: inherit !important;
    transition: all 0.2s !important;
    white-space: nowrap !important;
}
.qr-btn:hover {
    background: rgba(56,189,248,0.15) !important;
    color: #38bdf8 !important;
    border-color: rgba(56,189,248,0.4) !important;
    transform: translateY(-1px) !important;
}

/* INPUT ROW */
.chat-input-row {
    display: flex !important;
    gap: 10px !important;
    padding: 16px 24px !important;
    background: rgba(0,0,0,0.25) !important;
    border-top: 1px solid rgba(255,255,255,0.06) !important;
    align-items: center !important;
}
.chat-input-row input {
    flex: 1 !important;
    padding: 14px 20px !important;
    background: rgba(0,0,0,0.35) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    border-radius: 999px !important;
    color: #f1f5f9 !important;
    font-size: 14px !important;
    font-family: inherit !important;
    outline: none !important;
    transition: all 0.2s !important;
}
.chat-input-row input::placeholder { color: #64748b !important; }
.chat-input-row input:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56,189,248,0.2) !important;
}
.chat-input-row input:disabled { opacity: 0.5 !important; }

.btn-send {
    width: 48px !important;
    height: 48px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important;
    border: none !important;
    cursor: pointer !important;
    font-size: 18px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s !important;
    box-shadow: 0 4px 15px rgba(16,185,129,0.35) !important;
    flex-shrink: 0 !important;
    font-family: inherit !important;
}
.btn-send:hover:not(:disabled) {
    transform: scale(1.08) !important;
    box-shadow: 0 6px 22px rgba(16,185,129,0.55) !important;
}
.btn-send:disabled { opacity: 0.5 !important; cursor: not-allowed !important; }

/* RESPONSIVE */
@media (max-width: 768px) {
    .chat-page .main-content {
        margin-left: 0 !important;
        padding: 16px !important;
        padding-top: 72px !important;
        height: 100vh !important;
    }
    .msg-bubble { max-width: 88% !important; }
    .chat-messages { padding: 16px !important; }
    .quick-replies, .chat-input-row { padding: 10px 16px !important; }
    .qr-btn { font-size: 11.5px !important; padding: 7px 12px !important; }
}
</style>