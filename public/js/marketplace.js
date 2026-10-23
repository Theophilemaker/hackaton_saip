let selectedMethod = null;
let pendingOrderId = null;
let pendingAmount = null;

/* ============ WISHLIST ============ */
async function toggleWishlist(productId, btn) {
    const res = await fetch(`${BASE_URL}/marketplace/toggleWishlist/${productId}`, { method: 'POST' });
    const data = await res.json();
    btn.textContent = data.result === 'added' ? '❤️' : '🤍';
}

/* ============ NEGOTIATION ============ */
function openNegotiation(productId) {
    document.getElementById('negoModal').style.display = 'flex';
}
async function submitNegotiation(productId) {
    const price = document.getElementById('negoPrice').value;
    const qty = document.getElementById('negoQty').value;
    const res = await fetch(`${BASE_URL}/marketplace/negotiate/${productId}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `offered_price=${price}&quantity=${qty}`
    });
    const data = await res.json();
    if (data.success) {
        window.location = `${BASE_URL}/chat/conversation/${data.conversation_id}`;
    }
}

/* ============ PAYMENT ============ */
function openPayModal(orderId, amount) {
    pendingOrderId = orderId;
    pendingAmount = amount;
    document.getElementById('payModal').style.display = 'flex';
}
function payWith(method) {
    selectedMethod = method;
    const phoneField = document.getElementById('phoneField');
    const confirmBtn = document.getElementById('payConfirm');
    if (method === 'bank_transfer') {
        phoneField.style.display = 'none';
    } else {
        phoneField.style.display = 'block';
    }
    confirmBtn.style.display = 'block';
    confirmBtn.textContent = `Pay ${pendingAmount.toLocaleString()} RWF via ${method.replace('_',' ')}`;
}
async function confirmPayment() {
    const phone = document.getElementById('payPhone').value;
    const btn = document.getElementById('payConfirm');
    btn.disabled = true;
    btn.textContent = '⏳ Processing...';

    const res = await fetch(`${BASE_URL}/marketplace/pay/${pendingOrderId}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `method=${selectedMethod}&phone=${phone}`
    });
    const data = await res.json();
    if (data.success) {
        btn.textContent = '✅ Payment Successful!';
        setTimeout(() => window.location.reload(), 1500);
    } else {
        btn.textContent = '❌ Failed — retry';
        btn.disabled = false;
    }
}

/* ============ ORDER TRACKING MAP ============ */
document.addEventListener('DOMContentLoaded', () => {
    const mapEl = document.getElementById('miniMap');
    if (!mapEl) return;
    const lat = parseFloat(mapEl.dataset.lat) || -1.9403;
    const lng = parseFloat(mapEl.dataset.lng) || 29.8739;

    const map = L.map('miniMap', { zoomControl: false }).setView([lat, lng], 11);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);
    L.marker([lat, lng]).addTo(map);
});  