<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <a href="<?= BASE_URL ?>/marketplace/sell" class="btn-secondary" style="display:inline-block; margin-bottom:12px;">
                ← <?= __('mkt.seller_dashboard') ?>
            </a>
            <div class="card-header">
                <h3>📦 <?= __('mkt.add_product') ?></h3>
                <span class="badge green">Seller</span>
            </div>

            <?php if (!empty($data['error'])): ?>
                <div class="alert error"><?= e($data['error']) ?></div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/marketplace/newProduct" method="POST" enctype="multipart/form-data" class="alert-form">
                <?php if (class_exists('\App\Core\Security')) echo \App\Core\Security::csrfField(); ?>

                <!-- Product Image -->
                <h4 class="section-title">📷 Product Image</h4>
                <div class="image-upload-zone" id="imageZone">
                    <input type="file" name="main_image" id="mainImage" accept="image/*" hidden>
                    <div id="uploadPrompt">
                        <span class="upload-icon">📷</span>
                        <p>Click or drop a product image here</p>
                        <small>JPG, PNG, WEBP up to 5MB</small>
                    </div>
                    <img id="imagePreview" style="display:none;">
                </div>

                <!-- Basic Info -->
                <h4 class="section-title">📝 Product Details</h4>

                <div class="form-group">
                    <label>Product Title *</label>
                    <input type="text" name="title" required maxlength="200" placeholder="e.g., Premium Maize — Grade A" value="<?= e($_POST['title'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" maxlength="1000" placeholder="Describe quality, harvest date, storage conditions, delivery options..."><?= e($_POST['description'] ?? '') ?></textarea>
                </div>

                <!-- Category + Crop -->
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($data['categories'] ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ((int)($_POST['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                                    <?= $c['icon'] ?? '' ?> <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Crop (optional)</label>
                        <select name="crop_id">
                            <option value="">— None —</option>
                            <?php foreach (($data['crops'] ?? []) as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ((int)($_POST['crop_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                                    <?= $c['icon'] ?? '' ?> <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Pricing & Quantity -->
                <h4 class="section-title">💰 Price & Quantity</h4>

                <div class="form-row-3">
                    <div class="form-group">
                        <label>Price per Unit *</label>
                        <input type="number" name="price_per_unit" step="0.01" min="0.01" required placeholder="350" value="<?= e($_POST['price_per_unit'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Unit *</label>
                        <select name="unit" required>
                            <option value="kg">Kilograms</option>
                            <option value="ton">Tons</option>
                            <option value="bag">Bags</option>
                            <option value="piece">Pieces</option>
                            <option value="liter">Liters</option>
                            <option value="bunch">Bunches</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Currency</label>
                        <input type="text" value="RWF" disabled style="opacity:0.6;">
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Quantity Available *</label>
                        <input type="number" name="quantity_available" step="0.01" min="0.01" required placeholder="500" value="<?= e($_POST['quantity_available'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Minimum Order</label>
                        <input type="number" name="min_order_qty" step="0.01" min="0.01" placeholder="10" value="<?= e($_POST['min_order_qty'] ?? '') ?>">
                    </div>
                </div>

                <!-- Quality & Location -->
                <h4 class="section-title">🎯 Quality & Location</h4>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Quality Grade</label>
                        <select name="quality_grade">
                            <option value="Standard" <?= (($_POST['quality_grade'] ?? 'Standard') === 'Standard') ? 'selected' : '' ?>>Standard</option>
                            <option value="A" <?= (($_POST['quality_grade'] ?? '') === 'A') ? 'selected' : '' ?>>Grade A</option>
                            <option value="B" <?= (($_POST['quality_grade'] ?? '') === 'B') ? 'selected' : '' ?>>Grade B</option>
                            <option value="C" <?= (($_POST['quality_grade'] ?? '') === 'C') ? 'selected' : '' ?>>Grade C</option>
                            <option value="Organic" <?= (($_POST['quality_grade'] ?? '') === 'Organic') ? 'selected' : '' ?>>Organic</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>District</label>
                        <select name="district_id">
                            <option value="">Select district</option>
                            <?php 
                            $districts = $data['districts'] ?? [
                                ['id'=>1, 'name'=>'Gasabo'], ['id'=>2, 'name'=>'Kicukiro'], ['id'=>3, 'name'=>'Nyarugenge'],
                                ['id'=>7, 'name'=>'Musanze'], ['id'=>17, 'name'=>'Bugesera'], ['id'=>22, 'name'=>'Nyagatare'],
                            ];
                            foreach ($districts as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ((int)($_POST['district_id'] ?? 0) === (int)$d['id']) ? 'selected' : '' ?>>
                                    <?= e($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Attributes -->
                <h4 class="section-title">🌱 Attributes</h4>

                <div class="checkbox-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_organic" value="1" <?= !empty($_POST['is_organic']) ? 'checked' : '' ?>>
                        <span>🌱 This product is Organic</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_export_ready" value="1" <?= !empty($_POST['is_export_ready']) ? 'checked' : '' ?>>
                        <span>🚢 Export-ready (meets international standards)</span>
                    </label>
                </div>

                <!-- Submit -->
                <div class="action-buttons" style="margin-top:24px;">
                    <button type="submit" class="btn-primary">📤 Publish Product</button>
                    <a href="<?= BASE_URL ?>/marketplace/sell" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </section>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const zone = document.getElementById('imageZone');
    const input = document.getElementById('mainImage');
    const preview = document.getElementById('imagePreview');
    const prompt = document.getElementById('uploadPrompt');
    if (!zone) return;
    zone.addEventListener('click', () => input.click());
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.classList.remove('dragover');
        input.files = e.dataTransfer.files; handleFile(input.files[0]);
    });
    input.addEventListener('change', () => handleFile(input.files[0]));
    function handleFile(file) {
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) { alert('File too large — max 5MB'); return; }
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; prompt.style.display = 'none'; };
        reader.readAsDataURL(file);
    }
});
</script>

<style>
/* [Full CSS block from your new_product text file goes here] */
.image-upload-zone { border: 2px dashed rgba(132,204,22,0.4); border-radius: 16px; padding: 30px; text-align: center; cursor: pointer; transition: 300ms; margin-bottom: 20px; min-height: 220px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.02); }
.image-upload-zone:hover, .image-upload-zone.dragover { background: rgba(132,204,22,0.08); border-color: var(--accent); }
.upload-icon { font-size: 48px; display: block; margin-bottom: 10px; }
.image-upload-zone p { color: var(--text-sub); font-size: 14px; margin: 0 0 6px; }
.image-upload-zone small { color: var(--text-sub); font-size: 11px; opacity: 0.7; }
#imagePreview { max-width: 100%; max-height: 400px; border-radius: 12px; }
.section-title { margin: 28px 0 16px; font-size: 12px; text-transform: uppercase; letter-spacing: 1.2px; color: var(--accent); padding-bottom: 8px; border-bottom: 1px solid var(--border); }
.section-title:first-of-type { margin-top: 8px; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-row-3 { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 14px; }
.checkbox-row { display: flex; flex-direction: column; gap: 12px; }
.checkbox-label { display: flex !important; align-items: center !important; gap: 10px; padding: 12px 16px; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 12px; cursor: pointer; font-size: 13px; transition: 200ms; }
.checkbox-label:hover { background: rgba(34,197,94,0.08); border-color: var(--accent); }
.checkbox-label input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; flex-shrink: 0; }
@media (max-width: 700px) { .form-row-2, .form-row-3 { grid-template-columns: 1fr; gap: 0; } }
</style>