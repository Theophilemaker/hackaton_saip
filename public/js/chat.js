/* ============ STATE ============ */
let currentConvId = null;
let lastMessageId = 0;
let typingTimer = null;
let isTyping = false;
let mediaRecorder = null;
let audioChunks = [];
let reactingMessageId = null;

/* ============ INIT ============ */
document.addEventListener('DOMContentLoaded', () => {
    const room = document.querySelector('.chat-room');
    if (!room) return;

    currentConvId = room.dataset.convId;
    const msgs = document.querySelectorAll('.msg');
    if (msgs.length) lastMessageId = parseInt(msgs[msgs.length-1].dataset.id);

    scrollToBottom();
    startPolling();

    // Form submit
    document.getElementById('chatForm').addEventListener('submit', sendMessage);
    document.getElementById('msgInput').addEventListener('input', onTyping);
    document.getElementById('mediaInput').addEventListener('change', onMediaPicked);

    // Search
    const searchInput = document.getElementById('userSearch');
    if (searchInput) searchInput.addEventListener('input', debounce(searchUsers, 300));
});

/* ============ POLLING ============ */
function startPolling() {
    setInterval(pollMessages, 3000);
}

async function pollMessages() {
    try {
        const res = await fetch(`${BASE_URL}/chat/poll?conversation_id=${currentConvId}&after_id=${lastMessageId}`);
        const data = await res.json();
        if (data.messages && data.messages.length) {
            data.messages.forEach(appendMessage);
            lastMessageId = Math.max(...data.messages.map(m => parseInt(m.id)));
            scrollToBottom();
        }
        // Typing indicator
        const ti = document.getElementById('typingIndicator');
        if (ti) ti.textContent = data.typing.length ? data.typing.join(', ') + ' is typing...' : '';
    } catch(e) { /* silent */ }
}

/* ============ APPEND ============ */
function appendMessage(m) {
    if (document.querySelector(`.msg[data-id="${m.id}"]`)) return;
    const myId = document.querySelector('.chat-room').dataset.myId;
    const isMe = String(m.sender_id) === String(myId);
    const html = `
        <div class="msg ${isMe?'out':'in'}" data-id="${m.id}">
            ${!isMe ? `<div class="msg-avatar">${(m.first_name||'?').charAt(0).toUpperCase()}</div>` : ''}
            <div class="msg-body">
                ${!isMe ? `<span class="msg-sender">${m.first_name} ${m.badge_type?'<span class="verified-badge">✓</span>':''}</span>` : ''}
                <div class="msg-content">${renderContent(m)}</div>
                <div class="msg-footer">
                    <span class="msg-time">${new Date(m.sent_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}</span>
                    <button class="msg-react-btn" onclick="showEmojiPicker(${m.id}, event)">😊</button>
                    <button class="msg-translate-btn" onclick="translateMessage(${m.id}, this)">🌐</button>
                </div>
                <div class="msg-reactions" id="reactions-${m.id}"></div>
            </div>
        </div>`;
    document.getElementById('chatMessages').insertAdjacentHTML('beforeend', html);
}

function renderContent(m) {
    switch(m.message_type) {
        case 'image': return `<img src="${m.media_url}" class="msg-media" onclick="window.open(this.src)">`;
        case 'video': return `<video src="${m.media_url}" controls class="msg-media"></video>`;
        case 'audio': return `<audio src="${m.media_url}" controls></audio>`;
        case 'file': return `<a href="${m.media_url}" target="_blank">📎 Download</a>`;
        case 'location': return `<a href="https://maps.google.com/?q=${m.location_lat},${m.location_lng}" target="_blank">📍 Location</a>`;
        default: return escapeHtml(m.content).replace(/\n/g,'<br>');
    }
}

/* ============ SEND ============ */
async function sendMessage(e) {
    e.preventDefault();
    const input = document.getElementById('msgInput');
    const type = document.getElementById('msgType').value;
    const content = input.value.trim();
    const mediaInput = document.getElementById('mediaInput');

    if (!content && !mediaInput.files.length && type === 'text') return;

    const fd = new FormData();
    fd.append('conversation_id', currentConvId);
    fd.append('content', content);
    fd.append('type', type);
    if (mediaInput.files.length) fd.append('media', mediaInput.files[0]);

    input.value = '';
    document.getElementById('msgType').value = 'text';
    mediaInput.value = '';

    await fetch(`${BASE_URL}/chat/send`, { method:'POST', body: fd });
    pollMessages();
}

/* ============ TYPING ============ */
function onTyping() {
    if (isTyping) return;
    isTyping = true;
    fetch(`${BASE_URL}/chat/typing`, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: `conversation_id=${currentConvId}`
    });
    setTimeout(() => isTyping = false, 2000);
}

/* ============ MEDIA PREVIEW ============ */
function onMediaPicked(e) {
    const f = e.target.files[0];
    if (!f) return;
    if (f.type.startsWith('image/')) document.getElementById('msgType').value = 'image';
    else if (f.type.startsWith('video/')) document.getElementById('msgType').value = 'video';
    else document.getElementById('msgType').value = 'file';
    document.getElementById('msgInput').value = '📎 ' + f.name;
}

/* ============ EMOJI REACTIONS ============ */
function showEmojiPicker(msgId, ev) {
    ev.stopPropagation();
    reactingMessageId = msgId;
    const picker = document.getElementById('emojiPicker');
    picker.style.display = 'flex';
    picker.style.top = (ev.clientY - 60) + 'px';
    picker.style.left = Math.min(ev.clientX, window.innerWidth - 300) + 'px';
}
function sendReaction(emoji) {
    if (!reactingMessageId) return;
    fetch(`${BASE_URL}/chat/react`, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: `message_id=${reactingMessageId}&emoji=${encodeURIComponent(emoji)}&action=add`
    });
    const box = document.getElementById('reactions-' + reactingMessageId);
    if (box && !box.querySelector(`[data-e="${emoji}"]`)) {
        const chip = document.createElement('span');
        chip.className = 'reaction-chip';
        chip.dataset.e = emoji;
        chip.textContent = emoji;
        box.appendChild(chip);
    }
    document.getElementById('emojiPicker').style.display = 'none';
}
document.addEventListener('click', () => {
    const p = document.getElementById('emojiPicker');
    if (p) p.style.display = 'none';
});

/* ============ TRANSLATE ============ */
async function translateMessage(msgId, btn) {
    const target = window.__SAIP_LANG__ || 'en';
    btn.disabled = true;
    btn.textContent = '⏳';
    const res = await fetch(`${BASE_URL}/chat/translate`, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: `message_id=${msgId}&lang=${target}`
    });
    const data = await res.json();
    btn.disabled = false;
    btn.textContent = '🌐';
    if (data.text) {
        const body = btn.closest('.msg-body').querySelector('.msg-content');
        body.innerHTML = `<em style="color:#84CC16">${escapeHtml(data.text)}</em>`;
    }
}

/* ============ VOICE RECORDING ============ */
async function recordVoice(btn) {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
        btn.textContent = '🎤';
        return;
    }
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio:true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];
        mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
        mediaRecorder.onstop = async () => {
            const blob = new Blob(audioChunks, { type:'audio/webm' });
            const fd = new FormData();
            fd.append('conversation_id', currentConvId);
            fd.append('type', 'audio');
            fd.append('content', '🎤 Voice note');
            fd.append('media', blob, 'voice.webm');
            await fetch(`${BASE_URL}/chat/send`, { method:'POST', body: fd });
            pollMessages();
        };
        mediaRecorder.start();
        btn.textContent = '⏹';
    } catch(e) { alert('Microphone access denied'); }
}

/* ============ LOCATION ============ */
function shareLocation() {
    if (!navigator.geolocation) return alert('Geolocation not supported');
    navigator.geolocation.getCurrentPosition(pos => {
        const fd = new FormData();
        fd.append('conversation_id', currentConvId);
        fd.append('type', 'location');
        fd.append('content', '📍 Shared location');
        fd.append('lat', pos.coords.latitude);
        fd.append('lng', pos.coords.longitude);
        fetch(`${BASE_URL}/chat/send`, { method:'POST', body: fd }).then(pollMessages);
    });
}

/* ============ FEED LIKE ============ */
function likePost(postId, btn) {
    fetch(`${BASE_URL}/feed/like/${postId}`, { method:'POST' })
        .then(r => r.json())
        .then(d => {
            const span = btn.querySelector('span');
            let n = parseInt(span.textContent);
            span.textContent = d.result === 'liked' ? n + 1 : Math.max(n - 1, 0);
        });
}

/* ============ USER SEARCH ============ */
async function searchUsers(e) {
    const q = e.target.value.trim();
    const box = document.getElementById('searchResults');
    if (q.length < 2) { box.innerHTML = ''; return; }
    const res = await fetch(`${BASE_URL}/chat/search?q=${encodeURIComponent(q)}`);
    const users = await res.json();
    box.innerHTML = users.map(u => `
        <div class="search-result" onclick="window.location='${BASE_URL}/chat/startDirect/${u.id}'">
            <strong>${escapeHtml(u.first_name + ' ' + u.last_name)}</strong>
            <small>${u.presence} ${u.badge_type?'• ✓ '+u.badge_type:''}</small>
        </div>`).join('');
}

/* ============ UTIL ============ */
function scrollToBottom() {
    const el = document.getElementById('chatMessages');
    if (el) el.scrollTop = el.scrollHeight;
}
function escapeHtml(s) {
    return (s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
function debounce(fn, ms) {
    let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
}