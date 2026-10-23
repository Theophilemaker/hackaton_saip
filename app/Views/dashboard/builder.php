<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header">
                <h3>🎨 Customize Your Dashboard</h3>
                <div>
                    <button onclick="saveLayout()" class="btn-primary" style="width:auto; padding:10px 20px;">💾 Save Layout</button>
                    <a href="<?= BASE_URL ?>/dashboardBuilder/reset" class="btn-secondary" onclick="return confirm('Reset to default layout?')">↺ Reset</a>
                </div>
            </div>
            <p class="empty-state">Drag widgets from the library into your dashboard. Resize by clicking a size button.</p>
        </section>

        <section class="builder-layout">
            <aside class="builder-sidebar glass-card">
                <h4 class="section-title">Widget Library</h4>
                <div id="widgetLibrary">
                    <?php 
                    $byCategory = [];
                    foreach ($data['widgets'] as $w) $byCategory[$w['category']][] = $w;
                    foreach ($byCategory as $cat => $items): ?>
                        <div class="widget-cat">
                            <span class="wc-label"><?= ucfirst($cat) ?></span>
                            <?php foreach ($items as $w): ?>
                                <div class="widget-chip" draggable="true" 
                                     data-key="<?= e($w['widget_key']) ?>"
                                     data-name="<?= e($w['name']) ?>"
                                     data-icon="<?= e($w['icon']) ?>">
                                    <span><?= $w['icon'] ?></span>
                                    <span><?= e($w['name']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </aside>

            <section class="builder-canvas glass-card" id="builderCanvas">
                <h4 class="section-title">Your Dashboard</h4>
                <div id="canvasDropzone" class="canvas-dropzone">
                    <p class="empty-state">Drag widgets here →</p>
                </div>
            </section>
        </section>
    </main>
</div>

<script>
let layout = <?= json_encode($data['layout'] ?? []) ?> || [];

function renderCanvas() {
    const zone = document.getElementById('canvasDropzone');
    if (!layout.length) {
        zone.innerHTML = '<p class="empty-state">Drag widgets here →</p>';
        return;
    }
    zone.innerHTML = '';
    layout.forEach((w, idx) => {
        const el = document.createElement('div');
        el.className = 'widget-instance widget-size-' + (w.size || 'medium');
        el.innerHTML = `
            <div class="wi-header">
                <span class="wi-icon">${w.icon || '📊'}</span>
                <strong>${w.name}</strong>
                <div class="wi-actions">
                    <select onchange="resize(${idx}, this.value)">
                        <option value="small" ${w.size==='small'?'selected':''}>S</option>
                        <option value="medium" ${w.size==='medium'?'selected':''}>M</option>
                        <option value="large" ${w.size==='large'?'selected':''}>L</option>
                        <option value="full" ${w.size==='full'?'selected':''}>Full</option>
                    </select>
                    <button onclick="removeWidget(${idx})">✕</button>
                </div>
            </div>
            <div class="wi-body">Widget preview</div>
        `;
        zone.appendChild(el);
    });
}

function removeWidget(i) {
    layout.splice(i, 1);
    renderCanvas();
}

function resize(i, size) {
    layout[i].size = size;
    renderCanvas();
}

document.querySelectorAll('.widget-chip').forEach(chip => {
    chip.addEventListener('dragstart', e => {
        e.dataTransfer.setData('text/plain', JSON.stringify({
            key: chip.dataset.key,
            name: chip.dataset.name,
            icon: chip.dataset.icon,
        }));
    });
});

const zone = document.getElementById('canvasDropzone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    const w = JSON.parse(e.dataTransfer.getData('text/plain'));
    layout.push({ ...w, size: 'medium' });
    renderCanvas();
});

async function saveLayout() {
    const res = await fetch('<?= BASE_URL ?>/dashboardBuilder/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ layout })
    });
    const data = await res.json();
    if (data.success) {
        alert('✅ Dashboard layout saved!');
        window.location = '<?= BASE_URL ?>/dashboard';
    }
}

renderCanvas();
</script>