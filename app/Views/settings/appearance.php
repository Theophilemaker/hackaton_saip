<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header"><h3>🎨 Appearance & Theme</h3></div>

            <h4 class="section-title">Choose a Theme</h4>
            <div class="theme-gallery" id="themeGallery">
                <?php 
                $themes = [
                    'green-harvest' => ['name'=>'Green Harvest','colors'=>['#16A34A','#22C55E','#84CC16']],
                    'midnight'      => ['name'=>'Midnight',      'colors'=>['#0F172A','#1E293B','#38BDF8']],
                    'royal-purple'  => ['name'=>'Royal Purple',  'colors'=>['#7C3AED','#A855F7','#C084FC']],
                    'sunset'        => ['name'=>'Sunset',        'colors'=>['#EA580C','#F97316','#FACC15']],
                    'sky-blue'      => ['name'=>'Sky Blue',      'colors'=>['#2563EB','#3B82F6','#0EA5E9']],
                ];
                foreach ($themes as $slug => $t): ?>
                    <div class="theme-card <?= $data['prefs']['theme_name'] === $slug ? 'active' : '' ?>"
                         data-theme="<?= $slug ?>"
                         onclick="applyTheme('<?= $slug ?>')">
                        <div class="theme-preview">
                            <?php foreach ($t['colors'] as $c): ?>
                                <span style="background:<?= $c ?>"></span>
                            <?php endforeach; ?>
                        </div>
                        <strong><?= $t['name'] ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <h4 class="section-title">Accent Color</h4>
            <div class="color-picker-row">
                <?php foreach (['#16A34A','#22C55E','#84CC16','#0EA5E9','#F59E0B','#EF4444','#7C3AED','#EC4899'] as $c): ?>
                    <button class="color-dot" style="background:<?= $c ?>" onclick="setColor('accent_color','<?= $c ?>')"></button>
                <?php endforeach; ?>
                <input type="color" id="customColor" onchange="setColor('accent_color', this.value)">
            </div>

            <h4 class="section-title">Card Style</h4>
            <div class="option-row">
                <label><input type="radio" name="card_style" value="glass" <?= $data['prefs']['card_style'] === 'glass' ? 'checked' : '' ?> onchange="setPref('card_style', 'glass')"> Glassmorphism</label>
                <label><input type="radio" name="card_style" value="material" <?= $data['prefs']['card_style'] === 'material' ? 'checked' : '' ?> onchange="setPref('card_style', 'material')"> Material</label>
                <label><input type="radio" name="card_style" value="flat" <?= $data['prefs']['card_style'] === 'flat' ? 'checked' : '' ?> onchange="setPref('card_style', 'flat')"> Flat</label>
            </div>

            <h4 class="section-title">Font Size</h4>
            <input type="range" min="1" max="4" value="<?= ['small'=>1,'medium'=>2,'large'=>3,'xlarge'=>4][$data['prefs']['font_size']] ?? 2 ?>" 
                   oninput="setFontSize(this.value)" style="width:100%">

            <h4 class="section-title">Border Radius</h4>
            <input type="range" min="0" max="24" value="<?= $data['prefs']['border_radius'] ?>" 
                   oninput="setPref('border_radius', this.value)" style="width:100%">

            <h4 class="section-title">Animations</h4>
            <select onchange="setPref('animation_level', this.value)">
                <option value="none" <?= $data['prefs']['animation_level']==='none'?'selected':'' ?>>None</option>
                <option value="low" <?= $data['prefs']['animation_level']==='low'?'selected':'' ?>>Low</option>
                <option value="medium" <?= $data['prefs']['animation_level']==='medium'?'selected':'' ?>>Medium</option>
                <option value="high" <?= $data['prefs']['animation_level']==='high'?'selected':'' ?>>High</option>
            </select>

            <h4 class="section-title">Accessibility</h4>
            <label><input type="checkbox" onchange="setPref('high_contrast', this.checked?1:0)" <?= $data['prefs']['high_contrast'] ? 'checked' : '' ?>> High Contrast Mode</label>
            <label><input type="checkbox" onchange="setPref('dark_mode', this.checked?1:0)" <?= $data['prefs']['dark_mode'] ? 'checked' : '' ?>> Dark Mode</label>

            <button class="btn-primary" style="margin-top:24px;" onclick="resetToDefault()">Reset to Default</button>
        </section>
    </main>
</div>

<script>
async function setPref(key, value) {
    await fetch('<?= BASE_URL ?>/preferences/save', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({[key]: value})
    });
    applyLocalPref(key, value);
}

async function applyTheme(name) {
    const res = await fetch('<?= BASE_URL ?>/preferences/applyTheme', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'theme=' + name
    });
    const data = await res.json();
    if (data.success) {
        document.documentElement.style.setProperty('--primary', data.theme.primary);
        document.documentElement.style.setProperty('--secondary', data.theme.secondary);
        document.documentElement.style.setProperty('--accent', data.theme.accent);
        document.documentElement.style.setProperty('--bg', data.theme.background);
        document.querySelectorAll('.theme-card').forEach(el => el.classList.remove('active'));
        document.querySelector(`.theme-card[data-theme="${name}"]`).classList.add('active');
    }
}

function setColor(key, value) {
    document.documentElement.style.setProperty('--' + key.replace('_', '-'), value);
    setPref(key, value);
}

function setFontSize(level) {
    const sizes = {1:'14px', 2:'16px', 3:'18px', 4:'20px'};
    document.body.style.fontSize = sizes[level];
    const names = {1:'small', 2:'medium', 3:'large', 4:'xlarge'};
    setPref('font_size', names[level]);
}

function applyLocalPref(key, value) {
    if (key === 'border_radius') {
        document.documentElement.style.setProperty('--card-radius', value + 'px');
    }
    if (key === 'accent_color') {
        document.documentElement.style.setProperty('--accent', value);
    }
    if (key === 'high_contrast') {
        document.body.classList.toggle('high-contrast', value == 1);
    }
}

async function resetToDefault() {
    if (!confirm('Reset all appearance settings to default?')) return;
    location.reload();
}
</script>