/* ============ DISEASE DETECTION ============ */
document.addEventListener('DOMContentLoaded', () => {
    const zone = document.getElementById('uploadZone');
    if (zone) {
        const input = document.getElementById('detectImage');
        const prompt = document.getElementById('uploadPrompt');
        const preview = document.getElementById('previewImg');

        zone.addEventListener('click', () => input.click());
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('hover'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('hover'));
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('hover');
            input.files = e.dataTransfer.files;
            handleFile(input.files[0]);
        });
        input.addEventListener('change', () => handleFile(input.files[0]));

        function handleFile(file) {
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
                prompt.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        document.getElementById('detectForm').addEventListener('submit', async e => {
            e.preventDefault();
            if (!input.files[0]) { alert('Please select an image'); return; }

            const fd = new FormData(e.target);
            const btn = e.target.querySelector('button[type=submit]');
            btn.disabled = true;
            btn.innerHTML = '⏳ Analyzing...';

            const res = await fetch(`${BASE_URL}/crops/detect`, { method: 'POST', body: fd });
            const data = await res.json();

            btn.disabled = false;
            btn.innerHTML = '🔍 Analyze';

            if (!data.success) { alert(data.error || 'Detection failed'); return; }

            const r = data.result;
            document.getElementById('resultName').textContent = r.disease_name;
            document.getElementById('resultSeverity').textContent = r.severity.toUpperCase();
            document.getElementById('resultSeverity').className = 'severity-pill ' + r.severity;
            document.getElementById('resultConfidence').textContent = Math.round(r.confidence * 100) + '%';
            document.getElementById('resultTreatment').textContent = r.treatment;
            document.getElementById('resultPrevention').textContent = r.recommendation;
            document.getElementById('detectResult').style.display = 'block';
            document.getElementById('detectResult').scrollIntoView({ behavior: 'smooth' });
        });
    }
});

/* ============ AGRIPOT CHAT ============ */
function quickAsk(text) {
    document.getElementById('agriInput').value = text;
    sendAgriMessage(new Event('submit'));
}

async function sendAgriMessage(ev) {
    ev.preventDefault();
    const input = document.getElementById('agriInput');
    const msg = input.value.trim();
    if (!msg) return;

    appendMsg('user', msg);
    input.value = '';

    const thinking = appendMsg('assistant', '⏳ Thinking...');

    const res = await fetch(`${BASE_URL}/crops/agribot`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(msg)
    });
    const data = await res.json();

    thinking.querySelector('.msg-content').innerHTML = escapeHtml(data.reply || 'Sorry, something went wrong.').replace(/\n/g, '<br>');
}

function appendMsg(role, text) {
    const box = document.getElementById('agriMessages');
    const div = document.createElement('div');
    div.className = 'msg ' + (role === 'user' ? 'out' : 'in');
    div.innerHTML = `
        ${role !== 'user' ? '<div class="msg-avatar">🤖</div>' : ''}
        <div class="msg-body">
            <div class="msg-content">${escapeHtml(text).replace(/\n/g, '<br>')}</div>
        </div>
    `;
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
    return div;
}

function escapeHtml(s) {
    return (s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}