document.addEventListener('DOMContentLoaded', () => {
    // Global Chart styling
    Chart.defaults.color = '#C8D5D1';
    Chart.defaults.borderColor = 'rgba(255,255,255,0.08)';
    Chart.defaults.font.family = 'Inter';

    const greenGradient = (ctx) => {
        const g = ctx.createLinearGradient(0, 0, 0, 300);
        g.addColorStop(0, 'rgba(34, 197, 94, 0.6)');
        g.addColorStop(1, 'rgba(34, 197, 94, 0.02)');
        return g;
    };

    // Yield Prediction Chart
    const yieldEl = document.getElementById('yieldChart');
    if (yieldEl) {
        new Chart(yieldEl.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
                datasets: [
                    {
                        label: 'Predicted (tons)',
                        data: [2.1, 2.8, 3.2, 4.5, 5.1, 6.8, 7.2, 8.0],
                        borderColor: '#22C55E',
                        backgroundColor: greenGradient(yieldEl.getContext('2d')),
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointBackgroundColor: '#22C55E',
                        pointRadius: 4
                    },
                    {
                        label: 'Historical (tons)',
                        data: [1.8, 2.3, 2.9, 3.8, 4.2, 5.5, 6.1, 6.5],
                        borderColor: '#84CC16',
                        backgroundColor: 'transparent',
                        tension: 0.4,
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Price Trends Chart
    const priceEl = document.getElementById('priceChart');
    if (priceEl) {
        new Chart(priceEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Wk 1', 'Wk 2', 'Wk 3', 'Wk 4', 'Wk 5', 'Wk 6'],
                datasets: [
                    {
                        label: 'Maize (RWF/kg)',
                        data: [320, 335, 340, 350, 355, 350],
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderRadius: 6
                    },
                    {
                        label: 'Beans (RWF/kg)',
                        data: [600, 615, 640, 620, 625, 620],
                        backgroundColor: 'rgba(132, 204, 22, 0.7)',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});

// Language menu toggle
function toggleLangMenu() {
    const menu = document.getElementById('langMenu');
    if (menu) menu.classList.toggle('open');
}

// Close lang menu on outside click
document.addEventListener('click', (e) => {
    const switcher = document.getElementById('langSwitcher');
    const menu = document.getElementById('langMenu');
    if (menu && switcher && !switcher.contains(e.target)) {
        menu.classList.remove('open');
    }
});