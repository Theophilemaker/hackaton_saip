// Basic JS for future interactivity
document.addEventListener('DOMContentLoaded', () => {
    console.log('SAIP Application Loaded successfully.');
    
    // Example: Add a smooth fade-in effect to glass cards
    const cards = document.querySelectorAll('.glass-card, .widget');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
window.toast = window.toast || function (msg, type = 'info') {
    let t = document.createElement('div');
    t.className = 'toast ' + type;
    t.textContent = msg;
    Object.assign(t.style, {
        position: 'fixed', bottom: '24px', right: '24px',
        padding: '14px 20px', borderRadius: '12px',
        fontWeight: '600', color: '#fff', zIndex: 99999,
        background: type === 'success' ? 'linear-gradient(135deg,#10b981,#059669)'
                  : type === 'error'   ? 'linear-gradient(135deg,#ef4444,#dc2626)'
                  :                      'linear-gradient(135deg,#38bdf8,#0284c7)',
        transform: 'translateY(20px)', opacity: '0',
        transition: 'all .3s',
        boxShadow: '0 12px 32px rgba(0,0,0,0.4)',
    });
    document.body.appendChild(t);
    requestAnimationFrame(() => {
        t.style.transform = 'translateY(0)';
        t.style.opacity = '1';
    });
    setTimeout(() => {
        t.style.transform = 'translateY(20px)';
        t.style.opacity = '0';
        setTimeout(() => t.remove(), 300);
    }, 3000);
};