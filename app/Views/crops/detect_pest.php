<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card detect-panel">
            <div class="card-header">
                <h3>🐛 <?= __('crop.detect_pest') ?> — AI</h3>
                <a href="<?= BASE_URL ?>/crops/detect" class="btn-secondary">🔬 Disease Detection</a>
            </div>
            <p class="detect-hint">
                Upload a clear photo of the affected crop area. AI will identify the pest and suggest control methods.
            </p>

            <form id="pestForm" enctype="multipart/form-data">
                <input type="hidden" name="crop_id" id="pestCrop" value="">

                <div class="upload-zone" id="pestUploadZone">
                    <input type="file" name="image" id="pestImage" accept="image/*" hidden>
                    <div id="pestUploadPrompt">
                        <span class="upload-icon">📷</span>
                        <p>Click or drop a photo of the affected plant</p>
                        <small>JPG, PNG, WEBP up to 5MB</small>
                    </div>
                    <img id="pestPreviewImg" style="display:none; max-width:100%; border-radius:12px;">
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label><?= __('crop.catalog') ?> (optional)</label>
                    <select id="pestCropSelect" onchange="document.getElementById('pestCrop').value=this.value">
                        <option value="">Select crop</option>
                        <?php foreach ($data['crops'] as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['icon'] ?> <?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="margin-top:16px;">🔍 Analyze Pest</button>
            </form>

            <!-- Result Panel -->
            <div id="pestResult" class="detect-result" style="display:none;">
                <div class="result-header">
                    <span class="result-icon">🐛</span>
                    <div>
                        <h3 id="pestName">—</h3>
                        <span id="pestSeverity" class="severity-pill"></span>
                        <span id="pestType" class="badge green" style="margin-left:8px;"></span>
                    </div>
                    <div class="confidence-circle" id="pestConfidence">—</div>
                </div>

                <div class="result-block">
                    <h4>🔎 Symptoms</h4>
                    <p id="pestSymptoms"></p>
                </div>

                <div class="result-block">
                    <h4>💊 Control Methods</h4>
                    <p id="pestControl"></p>
                </div>

                <div class="result-block">
                    <h4>📞 Need help?</h4>
                    <p>
                        <a href="<?= BASE_URL ?>/forum" class="link-subtle">Post on the district forum</a>
                        or contact your nearest agricultural officer.
                    </p>
                </div>
            </div>
        </section>
    </main>
</div>

<script src="<?= BASE_URL ?>/js/crops.js" defer></script>