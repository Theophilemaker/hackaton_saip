/* =====================================================================
 * SAIP — NISR Data Analysis Portal
 * Client-side controller
 * Used by: /nisr/* pages
 * ===================================================================== */

(function () {
    'use strict';

    /* ============================================================
     *  CONFIG
     * ============================================================ */
    const BASE_URL = window.BASE_URL || (window.SAIP && window.SAIP.baseUrl) || '/';
    const API = BASE_URL.replace(/\/$/, '');

    const state = {
        currentDataset: null,
        schema: [],
        chartInstance: null,
        compareChart: null,
        tsChart: null,
        editingTranslation: null,
    };

    /* ============================================================
     *  INIT
     * ============================================================ */
    function init() {
        const page = detectPage();
        if (!page) return;

        switch (page) {
            case 'explorer':      initExplorer(); break;
            case 'chartBuilder':  initChartBuilder(); break;
            case 'compare':       initCompare(); break;
            case 'timeseries':    initTimeseries(); break;
            case 'upload':        initUpload(); break;
            case 'apiDocs':       initApiDocs(); break;
            case 'quality':       initQuality(); break;
            default: break;
        }
    }

    function detectPage() {
        const path = window.location.pathname;
        if (path.includes('/nisr/explorer'))      return 'explorer';
        if (path.includes('/nisr/chartBuilder'))  return 'chartBuilder';
        if (path.includes('/nisr/compare'))       return 'compare';
        if (path.includes('/nisr/timeseries'))    return 'timeseries';
        if (path.includes('/nisr/upload'))        return 'upload';
        if (path.includes('/nisr/apiDocs'))       return 'apiDocs';
        if (path.includes('/nisr/quality'))       return 'quality';
        return null;
    }

    /* ============================================================
     *  STATISTICAL EXPLORER
     * ============================================================ */
    function initExplorer() {
        const datasetSelect = document.querySelector('select[name="dataset"]');
        if (datasetSelect) {
            // Value already in URL, but on change, navigate
            datasetSelect.addEventListener('change', (e) => {
                window.location = API + '/nisr/explorer/' + e.target.value;
            });
        }

        // Pivot form run button
        const runBtn = document.querySelector('button[type="submit"]');
        if (runBtn) {
            runBtn.addEventListener('click', (e) => {
                const row = document.querySelector('select[name="row"]')?.value;
                const col = document.querySelector('select[name="col"]')?.value;
                const val = document.querySelector('select[name="val"]')?.value;

                if (!row || !col || !val) {
                    e.preventDefault();
                    showToast('⚠️ Please select Row, Column, and Value fields');
                }
            });
        }

        // Highlight non-empty pivot cells
        highlightPivotCells();
    }

    function highlightPivotCells() {
        const cells = document.querySelectorAll('.pivot-table td');
        if (!cells.length) return;

        let max = 0;
        cells.forEach(td => {
            const v = parseFloat(td.textContent.replace(/,/g, ''));
            if (!isNaN(v) && v > max) max = v;
        });

        if (max <= 0) return;

        cells.forEach(td => {
            const v = parseFloat(td.textContent.replace(/,/g, ''));
            if (isNaN(v)) return;
            const ratio = v / max;
            if (ratio > 0.75) td.style.background = 'rgba(239,68,68,0.15)';
            else if (ratio > 0.5) td.style.background = 'rgba(245,158,11,0.12)';
            else if (ratio > 0.25) td.style.background = 'rgba(132,204,22,0.10)';
            else if (v > 0) td.style.background = 'rgba(34,197,94,0.08)';
        });
    }

    /* ============================================================
     *  CHART BUILDER
     * ============================================================ */
    function initChartBuilder() {
        const datasetSel = document.getElementById('chartDataset');
        const typeSel = document.getElementById('chartType');
        const xSel = document.getElementById('chartX');
        const ySel = document.getElementById('chartY');

        if (!datasetSel) return;

        // Reload schema when dataset changes
        datasetSel.addEventListener('change', () => {
            window.location = API + '/nisr/chartBuilder/' + datasetSel.value;
        });

        // Generate button
        const genBtn = document.querySelector('button[onclick*="renderChart"]');
        if (genBtn) genBtn.removeAttribute('onclick');

        const generate = () => renderChart();
        document.querySelectorAll('#chartForm button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                generate();
            });
        });

        // Auto-generate if URL has params
        const params = new URLSearchParams(window.location.search);
        if (params.has('x') && params.has('y')) {
            setTimeout(generate, 200);
        }
    }

    async function renderChart() {
        const datasetId = document.getElementById('chartDataset')?.value;
        const xField = document.getElementById('chartX')?.value;
        const yField = document.getElementById('chartY')?.value;
        const chartType = document.getElementById('chartType')?.value || 'bar';

        if (!datasetId || !xField || !yField) {
            showToast('⚠️ Please select dataset, X field, and Y field');
            return;
        }

        showToast('⏳ Generating chart...');

        const canvas = document.getElementById('builderChart');
        if (!canvas) return;

        try {
            const url = `${API}/nisr/api/pivot?datasetId=${datasetId}&row=${xField}&col=${xField}&val=${yField}&agg=sum`;
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();

            const labels = data.rows || [];
            const values = labels.map(l => (data.data[l] && data.data[l][l]) || 0);

            if (state.chartInstance) state.chartInstance.destroy();

            const ctx = canvas.getContext('2d');
            const greenGradient = ctx.createLinearGradient(0, 0, 0, 300);
            greenGradient.addColorStop(0, 'rgba(34,197,94,0.85)');
            greenGradient.addColorStop(1, 'rgba(34,197,94,0.05)');

            state.chartInstance = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: yField,
                        data: values,
                        backgroundColor: chartType === 'line'
                            ? greenGradient
                            : [
                                '#16A34A', '#22C55E', '#84CC16', '#0EA5E9',
                                '#F59E0B', '#EF4444', '#7C3AED', '#EC4899',
                            ],
                        borderColor: '#22C55E',
                        borderWidth: 2,
                        fill: chartType === 'line' || chartType === 'area',
                        tension: 0.4,
                        pointBackgroundColor: '#22C55E',
                        pointRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            labels: { color: '#C8D5D1', boxWidth: 12, padding: 15 },
                        },
                    },
                    scales: (chartType === 'pie' || chartType === 'doughnut')
                        ? {}
                        : {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(255,255,255,0.05)' },
                                ticks: { color: '#C8D5D1' },
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#C8D5D1', maxRotation: 45 },
                            },
                        },
                },
            });

            showToast('✅ Chart generated');
        } catch (e) {
            console.error(e);
            showToast('⚠️ Could not generate chart');
        }
    }

    /* ============================================================
     *  COMPARE
     * ============================================================ */
    function initCompare() {
        const datasetSel = document.querySelector('select[name="dataset"]');
        if (datasetSel) {
            datasetSel.addEventListener('change', (e) => {
                window.location = API + '/nisr/compare/' + e.target.value;
            });
        }

        // Animate bars on load
        setTimeout(() => {
            document.querySelectorAll('.compare-card .progress-fill').forEach(el => {
                const w = el.style.width || '0%';
                el.style.transition = 'width 800ms ease-out';
                el.style.width = '0%';
                requestAnimationFrame(() => setTimeout(() => { el.style.width = w; }, 50));
            });
        }, 200);
    }

    /* ============================================================
     *  TIME SERIES
     * ============================================================ */
    function initTimeseries() {
        const datasetSel = document.querySelector('select[name="dataset"]');
        if (datasetSel) {
            datasetSel.addEventListener('change', (e) => {
                window.location = API + '/nisr/timeseries/' + e.target.value;
            });
        }

        const form = document.querySelector('form[method="GET"]');
        if (form) {
            form.addEventListener('submit', () => {
                showToast('📈 Analyzing trend...');
            });
        }

        // Animate the trend chart if it exists
        animateTrend();
    }

    function animateTrend() {
        const canvas = document.getElementById('tsChart');
        if (!canvas) return;

        // Chart is drawn by the view, this just adds a fade-in
        canvas.style.opacity = '0';
        canvas.style.transition = 'opacity 600ms ease-out';
        setTimeout(() => { canvas.style.opacity = '1'; }, 100);
    }

    /* ============================================================
     *  UPLOAD
     * ============================================================ */
    function initUpload() {
        const zone = document.getElementById('uploadZone');
        const input = document.getElementById('csvFile');
        const form = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');

        if (!zone || !input) return;

        // Click zone → open file picker
        zone.addEventListener('click', () => input.click());

        // Drag & drop
        ['dragenter', 'dragover'].forEach(evt =>
            zone.addEventListener(evt, (e) => {
                e.preventDefault();
                zone.classList.add('dragover');
            })
        );

        ['dragleave', 'drop'].forEach(evt =>
            zone.addEventListener(evt, (e) => {
                e.preventDefault();
                zone.classList.remove('dragover');
            })
        );

        zone.addEventListener('drop', (e) => {
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                showFile(input.files[0]);
            }
        });

        input.addEventListener('change', () => {
            if (input.files.length) showFile(input.files[0]);
        });

        // Submit
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!input.files.length) {
                    e.preventDefault();
                    showToast('⚠️ Please select a CSV file');
                    return;
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '⏳ Uploading & processing...';
                }
            });
        }

        // Auto-remove error alert after 8s
        const errAlert = document.querySelector('.alert.error');
        if (errAlert) {
            setTimeout(() => {
                errAlert.style.transition = 'opacity 500ms';
                errAlert.style.opacity = '0';
                setTimeout(() => errAlert.remove(), 500);
            }, 8000);
        }
    }

    function showFile(file) {
        if (!file.name.toLowerCase().endsWith('.csv')) {
            showToast('⚠️ Only .csv files are allowed');
            clearFile();
            return;
        }
        if (file.size > 50 * 1024 * 1024) {
            showToast('⚠️ File too large (max 50 MB)');
            clearFile();
            return;
        }

        const nameEl = document.getElementById('fileName');
        const sizeEl = document.getElementById('fileSize');
        const prompt = document.getElementById('filePrompt');
        const preview = document.getElementById('filePreview');

        if (nameEl) nameEl.textContent = file.name;
        if (sizeEl) sizeEl.textContent = formatBytes(file.size);
        if (prompt) prompt.style.display = 'none';
        if (preview) preview.style.display = 'flex';
    }

    function clearFile() {
        const input = document.getElementById('csvFile');
        const prompt = document.getElementById('filePrompt');
        const preview = document.getElementById('filePreview');

        if (input) input.value = '';
        if (prompt) prompt.style.display = 'block';
        if (preview) preview.style.display = 'none';
    }

    function downloadSampleCSV() {
        const csv = [
            'district,crop,season,area_ha,yield_kg,year',
            'Bugesera,Maize,A,120,45000,2024',
            'Musanze,Potatoes,A,85,120000,2024',
            'Nyagatare,Maize,A,200,85000,2024',
            'Huye,Beans,B,60,18000,2024',
            'Kigali,Tomatoes,A,15,22500,2024',
            'Rubavu,Coffee,A,45,3200,2024',
            'Nyamasheke,Tea,B,80,18000,2024',
            'Rulindo,Maize,B,95,36000,2024',
            'Rwamagana,Beans,A,55,16500,2024',
        ].join('\n');

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'saip_sample_dataset.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        showToast('✅ Sample CSV downloaded');
    }

    /* ============================================================
     *  API DOCS
     * ============================================================ */
    function initApiDocs() {
        // Copy code blocks on click
        document.querySelectorAll('.code-block').forEach(block => {
            block.style.cursor = 'pointer';
            block.title = 'Click to copy';

            block.addEventListener('click', () => {
                const text = block.textContent;
                navigator.clipboard.writeText(text)
                    .then(() => {
                        const original = block.dataset.original || block.textContent;
                        block.dataset.original = original;
                        showToast('📋 Copied to clipboard');

                        block.style.background = 'rgba(34,197,94,0.15)';
                        setTimeout(() => { block.style.background = ''; }, 400);
                    })
                    .catch(() => showToast('⚠️ Copy failed'));
            });
        });
    }

    async function generateApiToken() {
        const labelInput = document.getElementById('newTokenLabel');
        const label = labelInput ? labelInput.value.trim() : '';

        if (!label) {
            showToast('⚠️ Enter a token label');
            return;
        }

        try {
            const res = await fetch(API + '/nisr/createApiToken', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: 'label=' + encodeURIComponent(label),
            });

            const data = await res.json();

            if (data.success) {
                // Show the token in a modal so the user can copy it
                showTokenModal(data.token);
                if (labelInput) labelInput.value = '';
            } else {
                showToast('⚠️ ' + (data.error || 'Could not generate token'));
            }
        } catch (e) {
            showToast('⚠️ Network error');
        }
    }

    function showTokenModal(token) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.style.display = 'flex';
        modal.innerHTML = `
            <div class="glass-card modal">
                <h3>🔑 API Token Generated</h3>
                <p style="color:var(--text-sub); font-size:13px;">
                    Copy this token and store it securely. You won't see it again.
                </p>
                <pre class="code-block" style="white-space:pre-wrap; word-break:break-all;">${escapeHtml(token)}</pre>
                <div class="action-buttons">
                    <button class="btn-primary" id="copyTokenBtn">📋 Copy Token</button>
                    <button class="btn-secondary" id="closeTokenBtn">Close</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        document.getElementById('copyTokenBtn').addEventListener('click', () => {
            navigator.clipboard.writeText(token)
                .then(() => showToast('✅ Token copied'))
                .catch(() => showToast('⚠️ Copy failed'));
        });

        document.getElementById('closeTokenBtn').addEventListener('click', () => {
            modal.remove();
            setTimeout(() => window.location.reload(), 300);
        });
    }

    /* ============================================================
     *  QUALITY PAGE
     * ============================================================ */
    function initQuality() {
        // Animate the missing-percentage bars
        document.querySelectorAll('.quality-fill').forEach(el => {
            const w = el.style.width || '0%';
            el.style.transition = 'width 700ms ease-out';
            el.style.width = '0%';
            requestAnimationFrame(() => setTimeout(() => { el.style.width = w; }, 50));
        });

        // Color-code the outlier count
        document.querySelectorAll('.data-table tbody tr').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length < 8) return;

            const outlierCell = cells[7];
            const outliers = parseInt(outlierCell.textContent.replace(/,/g, ''), 10);

            if (outliers > 10) outlierCell.style.color = '#EF4444';
            else if (outliers > 0) outlierCell.style.color = '#F59E0B';
            else outlierCell.style.color = '#22C55E';
        });
    }

    /* ============================================================
     *  HELPERS
     * ============================================================ */
    function formatNumber(num) {
        return new Intl.NumberFormat('en-US').format(Math.round(num || 0));
    }

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        }[c]));
    }

    function showToast(message) {
        let toast = document.querySelector('.nisr-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'nisr-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        requestAnimationFrame(() => toast.classList.add('show'));

        clearTimeout(toast._timeout);
        toast._timeout = setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    /* ============================================================
     *  STYLES (injected once)
     * ============================================================ */
    function injectStyles() {
        if (document.getElementById('nisrJsStyles')) return;

        const style = document.createElement('style');
        style.id = 'nisrJsStyles';
        style.textContent = `
            .nisr-toast {
                position: fixed;
                bottom: 30px;
                left: 50%;
                transform: translateX(-50%) translateY(100px);
                background: rgba(15, 61, 46, 0.98);
                color: #fff;
                padding: 14px 24px;
                border-radius: 12px;
                border: 1px solid var(--accent, #84CC16);
                box-shadow: 0 10px 40px rgba(0,0,0,0.5);
                font-size: 13px;
                z-index: 9999;
                transition: transform 300ms ease-out;
                font-family: 'Inter', sans-serif;
                max-width: 90vw;
                text-align: center;
            }
            .nisr-toast.show { transform: translateX(-50%) translateY(0); }

            .code-block {
                cursor: pointer;
                transition: background 200ms;
            }
            .code-block:hover { background: rgba(0,0,0,0.5); }
        `;
        document.head.appendChild(style);
    }

    /* ============================================================
     *  PUBLIC API
     * ============================================================ */
    window.SAIP_NISR = {
        init,
        renderChart,
        downloadSampleCSV,
        clearFile,
        generateApiToken,
        showToast,
    };

    // Backwards-compat globals for inline onclick handlers
    window.renderChart         = renderChart;
    window.downloadSampleCSV   = downloadSampleCSV;
    window.clearFile           = clearFile;
    window.generateToken       = generateApiToken;
    window.generateApiToken    = generateApiToken;
    window.showTokenModal      = showTokenModal;

    /* ============================================================
     *  AUTO-INIT
     * ============================================================ */
    document.addEventListener('DOMContentLoaded', () => {
        injectStyles();
        init();
    });
})();