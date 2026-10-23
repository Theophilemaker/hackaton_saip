<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>USSD Simulator - SAIP</title>
    <style>
        body { background: #0f172a; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .phone { width: 320px; background: #1e293b; border-radius: 40px; padding: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); border: 8px solid #334155; position: relative; }
        .screen { background: #a3d9a5; height: 400px; border-radius: 10px; padding: 20px; font-family: monospace; color: #000; display: flex; flex-direction: column; border: 4px solid #475569; }
        .ussd-header { font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 10px; display: flex; justify-content: space-between; }
        .ussd-content { flex: 1; white-space: pre-wrap; font-size: 14px; line-height: 1.5; }
        .ussd-input { margin-top: 10px; display: flex; gap: 10px; }
        .ussd-input input { flex: 1; padding: 10px; border: 1px solid #000; border-radius: 5px; font-size: 16px; font-family: monospace; outline: none; }
        .ussd-input button { padding: 10px 20px; background: #000; color: #a3d9a5; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .keypad { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 20px; }
        .key { background: #475569; color: #fff; padding: 15px; text-align: center; border-radius: 10px; font-weight: bold; cursor: pointer; user-select: none; }
        .key:active { background: #64748b; }
    </style>
</head>
<body>

<div class="phone">
    <div class="screen">
        <div class="ussd-header">
            <span>SAIP</span>
            <span id="signal">📶</span>
        </div>
        <div class="ussd-content" id="ussdText">Dial *123# to start...</div>
        
        <div class="ussd-input" id="inputArea" style="display:none;">
            <input type="text" id="reply" placeholder="Reply..." autocomplete="off">
            <button onclick="sendReply()">Send</button>
        </div>
    </div>
    
    <!-- Visual Keypad for realism -->
    <div class="keypad">
        <div class="key" onclick="typeKey('1')">1</div>
        <div class="key" onclick="typeKey('2')">2</div>
        <div class="key" onclick="typeKey('3')">3</div>
        <div class="key" onclick="typeKey('4')">4</div>
        <div class="key" onclick="typeKey('5')">5</div>
        <div class="key" onclick="typeKey('6')">6</div>
        <div class="key" onclick="typeKey('7')">7</div>
        <div class="key" onclick="typeKey('8')">8</div>
        <div class="key" onclick="typeKey('9')">9</div>
        <div class="key" onclick="dial()" style="background:#10b981;">Dial</div>
        <div class="key" onclick="typeKey('0')">0</div>
        <div class="key" onclick="reset()" style="background:#ef4444;">End</div>
    </div>
</div>

<script>
let currentStep = 0;
let isDialed = false;

function typeKey(num) {
    if (!isDialed) return;
    const input = document.getElementById('reply');
    input.value += num;
}

function dial() {
    isDialed = true;
    document.getElementById('inputArea').style.display = 'flex';
    sendToServer('');
}

function reset() {
    isDialed = false;
    currentStep = 0;
    document.getElementById('ussdText').innerText = "Dial *123# to start...";
    document.getElementById('inputArea').style.display = 'none';
    document.getElementById('reply').value = '';
}

function sendReply() {
    const input = document.getElementById('reply');
    const val = input.value;
    input.value = '';
    sendToServer(val);
}

function sendToServer(userInput) {
    fetch('<?= BASE_URL ?>/ussd/handle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'input=' + encodeURIComponent(userInput) + '&step=' + currentStep
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('ussdText').innerText = data.response;
        currentStep = data.next_step;
        
        if (data.is_end) {
            setTimeout(reset, 4000); // Auto-reset after 4 seconds
        }
    });
}
</script>
</body>
</html>