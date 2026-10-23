<?php
$crops      = $crops      ?? ($data['crops']      ?? []);
$detections = $detections ?? ($data['detections'] ?? []);
?>
<div class="dashboard-layout crop-detect-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
        <main class="main-content">

            <!-- HERO -->
            <section class="glass-card detect-hero">
                <div class="hero-left">
                    <span class="hero-tag">
                        <span class="live-dot"></span> AI DISEASE SCAN
                    </span>
                    <h1>🔬 Crop Disease Detection</h1>
                    <p>Upload a clear photo of the affected plant. Our AI compares it against <?= count($crops) ?>+ crop varieties and 100+ known diseases, then gives you treatment advice instantly.</p>
                </div>
                <div class="hero-actions">
                    <a href="<?= BASE_URL ?>/crops/detectPest" class="btn-hero-secondary">🐛 Pest Detection</a>
                </div>
            </section>

            <!-- MAIN LAYOUT: upload + history -->
            <div class="detect-layout">

                <!-- LEFT: UPLOAD + RESULT -->
                <div class="detect-main">

                    <!-- UPLOAD CARD -->
                    <section class="glass-card upload-card">
                        <div class="card-header">
                            <div>
                                <h3>📷 Upload a Photo</h3>
                                <p>Take a clear photo of the affected area in daylight</p>
                            </div>
                        </div>

                        <form id="detectForm" enctype="multipart/form-data">
                            <input type="hidden" name="crop_id" id="detectCropId" value="">
                            <input type="hidden" name="_csrf" value="<?= e(\App\Core\Security::csrfToken()) ?>">

                            <!-- Upload Zone -->
                            <div class="upload-zone" id="uploadZone">
                                <input type="file" name="image" id="imageInput" accept="image/*" hidden>
                                <div id="uploadPrompt" class="upload-prompt">
                                    <div class="up-icon">📸</div>
                                    <p>Click to choose a photo, or drag and drop</p>
                                    <small>JPG · PNG · WebP — max 5 MB</small>
                                </div>
                                <img id="previewImg" style="display:none" alt="">
                                <button type="button" class="change-btn" id="changeBtn" style="display:none">
                                    🔄 Change photo
                                </button>
                            </div>

                            <!-- Crop selection (optional) -->
                            <div class="crop-picker">
                                <label>Which crop is this? <span class="opt">(optional but improves accuracy)</span></label>
                                <div class="crop-chips" id="cropChips">
                                    <button type="button" class="crop-chip active" data-crop="">❓ Not sure</button>
                                    <?php foreach (array_slice($crops, 0, 10) as $c): ?>
                                        <button type="button" class="crop-chip" data-crop="<?= (int)$c['id'] ?>">
                                            <?= e($c['icon'] ?? '🌱') ?> <?= e($c['name']) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Tip row -->
                            <div class="tips-row">
                                <div class="tip-item">💡 <strong>Tip:</strong> Include leaves, stems, and fruit in the frame</div>
                                <div class="tip-item">📏 <strong>Distance:</strong> 20–30 cm from the affected area</div>
                            </div>

                            <!-- Analyze -->
                            <button type="submit" class="analyze-btn" id="analyzeBtn" disabled>
                                <span class="btn-icon">🔍</span>
                                <span class="btn-label">Analyze Photo</span>
                            </button>
                        </form>
                    </section>

                    <!-- RESULT CARD -->
                    <section class="glass-card result-card" id="resultCard" style="display:none;">
                        <div class="card-header">
                            <div>
                                <h3>🔬 Detection Result</h3>
                                <p>AI analysis complete</p>
                            </div>
                            <button type="button" class="rescan-btn" onclick="resetDetect()">🔄 New Scan</button>
                        </div>

                        <div class="result-hero">
                            <div class="result-img-wrap">
                                <img id="resultImage" src="" alt="">
                                <div class="confidence-ring">
                                    <svg viewBox="0 0 80 80">
                                        <circle cx="40" cy="40" r="34" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="6"/>
                                        <circle id="confRing" cx="40" cy="40" r="34" fill="none"
                                                stroke="url(#confGradient)" stroke-width="6" stroke-linecap="round"
                                                stroke-dasharray="213.6" stroke-dashoffset="213.6"
                                                transform="rotate(-90 40 40)"/>
                                        <defs>
                                            <linearGradient id="confGradient" x1="0" y1="0" x2="1" y2="1">
                                                <stop offset="0%" stop-color="#10b981"/>
                                                <stop offset="100%" stop-color="#38bdf8"/>
                                            </linearGradient>
                                        </defs>
                                        <text id="confText" x="40" y="46" text-anchor="middle" fill="#f1f5f9" font-size="14" font-weight="800">—</text>
                                    </svg>
                                </div>
                            </div>

                            <div class="result-header-info">
                                <span class="sev-pill" id="resultSev">—</span>
                                <h2 id="resultName">—</h2>
                                <p class="result-pathogen" id="resultPathogen">—</p>
                                <p class="result-desc" id="resultDesc">—</p>
                            </div>
                        </div>

                        <!-- Result Tabs -->
                        <div class="result-tabs">
                            <button type="button" class="rtab active" data-tab="symptoms">🔍 Symptoms</button>
                            <button type="button" class="rtab" data-tab="treatment">💊 Treatment</button>
                            <button type="button" class="rtab" data-tab="prevention">🛡️ Prevention</button>
                            <button type="button" class="rtab" data-tab="gallery">📸 Gallery</button>
                        </div>

                        <div class="result-panels">
                            <div class="rpanel active" data-panel="symptoms">
                                <div class="panel-content" id="panelSymptoms">—</div>
                            </div>
                            <div class="rpanel" data-panel="treatment">
                                <div class="panel-content" id="panelTreatment">—</div>
                            </div>
                            <div class="rpanel" data-panel="prevention">
                                <div class="panel-content" id="panelPrevention">—</div>
                            </div>
                            <div class="rpanel" data-panel="gallery">
                                <div class="gallery-strip" id="panelGallery">—</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="result-actions">
                            <button type="button" class="res-act primary" onclick="saveToFarm()">💾 Save to Farm Records</button>
                            <a href="<?= BASE_URL ?>/crops/agribot" class="res-act">🤖 Ask AgriBot</a>
                            <button type="button" class="res-act" onclick="shareResult()">📤 Share</button>
                        </div>
                    </section>

                </div>

                <!-- RIGHT: SIDEBAR -->
                <aside class="detect-side">

                    <!-- HOW IT WORKS -->
                    <section class="glass-card side-card">
                        <div class="side-head">
                            <h3>🎯 How It Works</h3>
                        </div>
                        <ol class="steps-list">
                            <li>
                                <span class="step-num">1</span>
                                <div><strong>Take a photo</strong><small>Clear, well-lit image of the problem</small></div>
                            </li>
                            <li>
                                <span class="step-num">2</span>
                                <div><strong>AI analyzes</strong><small>Compared to trained disease library</small></div>
                            </li>
                            <li>
                                <span class="step-num">3</span>
                                <div><strong>Get treatment</strong><small>Step-by-step cure and prevention</small></div>
                            </li>
                        </ol>
                    </section>

                    <!-- QUICK TIPS -->
                    <section class="glass-card side-card tips-card">
                        <div class="side-head">
                            <h3>✅ Photo Tips</h3>
                        </div>
                        <ul class="check-list">
                            <li>Good natural lighting</li>
                            <li>Focus on affected leaves</li>
                            <li>Include the whole plant</li>
                            <li>No blurry shots</li>
                            <li>Take 2–3 photos if unsure</li>
                        </ul>
                    </section>

                    <!-- HISTORY -->
                    <?php if (!empty($detections)): ?>
                        <section class="glass-card side-card">
                            <div class="side-head">
                                <h3>📜 Recent Scans</h3>
                                <span class="side-sub"><?= count($detections) ?></span>
                            </div>
                            <div class="history-list">
                                <?php foreach (array_slice($detections, 0, 5) as $d): ?>
                                    <div class="history-item" onclick="reopenDetection(<?= (int)$d['id'] ?>)">
                                        <div class="hist-thumb">
                                            <?php if (!empty($d['image_url'])): ?>
                                                <img src="<?= e($d['image_url']) ?>" alt="">
                                            <?php else: ?>
                                                <span>🌿</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="hist-info">
                                            <strong><?= e($d['disease_name'] ?? 'Unknown') ?></strong>
                                            <small><?= date('M d, H:i', strtotime($d['created_at'] ?? 'now')) ?></small>
                                        </div>
                                        <span class="hist-conf"><?= round(((float)($d['confidence'] ?? 0)) * 100) ?>%</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                </aside>
            </div>

        </main>
    </div>
</div>

<style>
/* ═══════════════════════════════════════════════════════════
   LAYOUT
   ═══════════════════════════════════════════════════════════ */
html,body{margin:0!important;padding:0!important;overflow-x:hidden!important}
.crop-detect-page.dashboard-layout{display:block!important;width:100%!important;background:#0b1120!important;min-height:100vh!important}
.crop-detect-page .main-wrap{margin-left:260px!important;width:calc(100% - 260px)!important;min-height:100vh!important;box-sizing:border-box!important}
.crop-detect-page .main-content{padding:20px 28px 60px!important;display:flex!important;flex-direction:column!important;gap:18px!important;box-sizing:border-box!important}
.crop-detect-page .glass-card{background:rgba(30,41,59,.7)!important;backdrop-filter:blur(16px)!important;border:1px solid rgba(255,255,255,.08)!important;border-radius:20px!important;padding:22px!important;box-sizing:border-box!important;width:100%!important}

/* HERO */
.detect-hero{display:flex!important;justify-content:space-between!important;align-items:center!important;gap:20px!important;flex-wrap:wrap!important;padding:24px 28px!important;background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(56,189,248,.08))!important;position:relative!important;overflow:hidden!important}
.detect-hero::before{content:'';position:absolute;top:-40%;right:-10%;width:380px;height:380px;background:radial-gradient(circle,rgba(16,185,129,.15),transparent 70%);pointer-events:none}
.hero-left{flex:1;min-width:260px;position:relative;z-index:1}
.hero-tag{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:rgba(16,185,129,.15);color:#34d399;border:1px solid rgba(16,185,129,.35);border-radius:999px;font-size:10.5px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;margin-bottom:10px}
.live-dot{width:6px;height:6px;background:#34d399;border-radius:50%;animation:pulse 1.6s infinite;box-shadow:0 0 8px #34d399}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.detect-hero h1{font-size:26px;font-weight:800;color:#f1f5f9;margin:0 0 8px}
.detect-hero p{color:#94a3b8;font-size:13.5px;margin:0;line-height:1.55;max-width:640px}
.hero-actions{display:flex;gap:10px;position:relative;z-index:1}
.btn-hero-secondary{display:inline-flex;align-items:center;gap:8px;padding:12px 22px;background:rgba(255,255,255,.06);color:#f1f5f9;border:1px solid rgba(255,255,255,.14);border-radius:12px;font-size:13.5px;font-weight:700;text-decoration:none;transition:.2s}
.btn-hero-secondary:hover{background:rgba(255,255,255,.12);transform:translateY(-2px)}

/* GRID */
.detect-layout{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:18px;align-items:start}
.detect-main{display:flex;flex-direction:column;gap:18px;min-width:0}
.detect-side{display:flex;flex-direction:column;gap:14px;position:sticky;top:20px}

.card-header{display:flex!important;justify-content:space-between!important;align-items:flex-start!important;gap:12px!important;margin-bottom:18px!important;padding-bottom:12px!important;border-bottom:1px solid rgba(255,255,255,.06)!important;flex-wrap:wrap!important}
.card-header h3{font-size:16px!important;font-weight:800!important;color:#f1f5f9!important;margin:0 0 4px!important}
.card-header p{font-size:12.5px!important;color:#94a3b8!important;margin:0!important}

/* UPLOAD */
.upload-zone{position:relative;height:340px;background:rgba(0,0,0,.25);border:2px dashed rgba(16,185,129,.35);border-radius:16px;display:flex;align-items:center;justify-content:center;overflow:hidden;cursor:pointer;transition:.2s;margin-bottom:18px}
.upload-zone:hover,.upload-zone.dragging{border-color:#10b981;background:rgba(16,185,129,.06)}
.upload-zone img{width:100%;height:100%;object-fit:contain;background:#0b1120}
.upload-prompt{text-align:center;color:#94a3b8;padding:30px}
.up-icon{font-size:56px;display:block;margin-bottom:12px;filter:drop-shadow(0 4px 12px rgba(16,185,129,.4))}
.upload-prompt p{margin:0 0 6px;font-size:15px;color:#cbd5e1;font-weight:600}
.upload-prompt small{font-size:12px;color:#64748b}
.change-btn{position:absolute;bottom:16px;right:16px;padding:9px 16px;background:rgba(0,0,0,.75);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;backdrop-filter:blur(8px)}
.change-btn:hover{background:rgba(16,185,129,.9);border-color:#10b981}

/* CROP CHIPS */
.crop-picker{margin-bottom:18px}
.crop-picker label{display:block;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px}
.opt{color:#64748b;font-weight:500;text-transform:none;letter-spacing:0;font-style:italic}
.crop-chips{display:flex;gap:6px;flex-wrap:wrap}
.crop-chip{padding:8px 14px;background:rgba(255,255,255,.03);color:#94a3b8;border:1px solid rgba(255,255,255,.08);border-radius:999px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s}
.crop-chip:hover{background:rgba(16,185,129,.1);color:#34d399;border-color:rgba(16,185,129,.3)}
.crop-chip.active{background:rgba(16,185,129,.18);color:#34d399;border-color:#10b981;box-shadow:0 0 0 2px rgba(16,185,129,.15)}

/* TIPS */
.tips-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px}
.tip-item{padding:10px 14px;background:rgba(251,191,36,.06);border-left:3px solid #fbbf24;border-radius:10px;font-size:12px;color:#cbd5e1;line-height:1.45}
.tip-item strong{color:#fbbf24}

/* ANALYZE BTN */
.analyze-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:16px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:14px;font-size:15px;font-weight:800;cursor:pointer;font-family:inherit;box-shadow:0 6px 24px rgba(16,185,129,.35);transition:.2s;letter-spacing:.3px}
.analyze-btn:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 8px 28px rgba(16,185,129,.55)}
.analyze-btn:disabled{opacity:.4;cursor:not-allowed;transform:none;box-shadow:none}
.analyze-btn .btn-icon{font-size:18px}

/* RESULT CARD */
.result-card{animation:slideIn .4s ease-out}
@keyframes slideIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.rescan-btn{padding:8px 16px;background:rgba(255,255,255,.05);color:#cbd5e1;border:1px solid rgba(255,255,255,.1);border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s}
.rescan-btn:hover{background:rgba(255,255,255,.1);color:#f1f5f9}

.result-hero{display:grid;grid-template-columns:200px 1fr;gap:20px;margin-bottom:22px;align-items:start}
.result-img-wrap{position:relative;border-radius:16px;overflow:hidden;background:#0b1120;border:1px solid rgba(255,255,255,.08);aspect-ratio:1}
.result-img-wrap img{width:100%;height:100%;object-fit:cover}
.confidence-ring{position:absolute;bottom:10px;right:10px;width:80px;height:80px;background:rgba(0,0,0,.7);backdrop-filter:blur(8px);border-radius:50%}
.confidence-ring svg{width:100%;height:100%}
#confRing{transition:stroke-dashoffset 1s ease-out}

.result-header-info{padding-top:6px}
.sev-pill{display:inline-flex;padding:4px 12px;border-radius:999px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px}
.sev-pill.critical{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.35)}
.sev-pill.high{background:rgba(245,158,11,.15);color:#fbbf24;border:1px solid rgba(245,158,11,.35)}
.sev-pill.medium{background:rgba(251,191,36,.15);color:#fbbf24;border:1px solid rgba(251,191,36,.35)}
.sev-pill.low{background:rgba(56,189,248,.15);color:#38bdf8;border:1px solid rgba(56,189,248,.35)}
.result-header-info h2{font-size:22px;color:#f1f5f9;font-weight:800;margin:0 0 8px;line-height:1.2}
.result-pathogen{display:inline-flex;padding:3px 10px;background:rgba(167,139,250,.15);color:#a78bfa;border-radius:999px;font-size:11.5px;font-weight:700;margin:0 0 12px}
.result-desc{font-size:13.5px;color:#cbd5e1;line-height:1.6;margin:0}

/* TABS */
.result-tabs{display:flex;gap:6px;padding:6px;background:rgba(0,0,0,.25);border-radius:12px;margin-bottom:16px;overflow-x:auto}
.rtab{padding:10px 18px;background:transparent;color:#94a3b8;border:none;border-radius:9px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s;white-space:nowrap}
.rtab:hover{color:#cbd5e1;background:rgba(255,255,255,.04)}
.rtab.active{background:linear-gradient(135deg,rgba(16,185,129,.2),rgba(56,189,248,.12));color:#34d399}

.result-panels{min-height:180px}
.rpanel{display:none;animation:fadeIn .25s}
.rpanel.active{display:block}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.panel-content{padding:18px;background:rgba(0,0,0,.2);border-radius:14px;font-size:13.5px;color:#cbd5e1;line-height:1.75;white-space:pre-wrap}
.panel-content strong{color:#34d399;font-weight:700}
.gallery-strip{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px}
.gallery-strip img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:12px;cursor:pointer;transition:.2s;border:1px solid rgba(255,255,255,.08)}
.gallery-strip img:hover{transform:scale(1.03);border-color:#10b981}

.result-actions{display:flex;gap:8px;padding-top:18px;border-top:1px solid rgba(255,255,255,.06);margin-top:18px;flex-wrap:wrap}
.res-act{padding:11px 18px;background:rgba(255,255,255,.04);color:#cbd5e1;border:1px solid rgba(255,255,255,.1);border-radius:10px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:.15s}
.res-act:hover{background:rgba(255,255,255,.1);color:#f1f5f9}
.res-act.primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;box-shadow:0 4px 14px rgba(16,185,129,.3)}
.res-act.primary:hover{transform:translateY(-2px)}

/* SIDEBAR CARDS */
.side-card{padding:18px!important}
.side-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06)}
.side-head h3{font-size:13.5px;font-weight:800;color:#f1f5f9;margin:0}
.side-sub{font-size:10.5px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.5px}

.steps-list{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:14px}
.steps-list li{display:flex;gap:12px;align-items:flex-start}
.step-num{width:28px;height:28px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;box-shadow:0 3px 10px rgba(16,185,129,.35)}
.steps-list strong{display:block;font-size:13px;color:#f1f5f9;font-weight:700;margin-bottom:2px}
.steps-list small{display:block;font-size:11px;color:#94a3b8;line-height:1.4}

.check-list{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px}
.check-list li{padding:8px 12px;background:rgba(16,185,129,.06);border-left:3px solid #10b981;border-radius:8px;font-size:12px;color:#cbd5e1;font-weight:500}
.check-list li::before{content:'✓ ';color:#34d399;font-weight:800;margin-right:4px}

.history-list{display:flex;flex-direction:column;gap:8px}
.history-item{display:flex;align-items:center;gap:10px;padding:8px;background:rgba(0,0,0,.2);border-radius:10px;cursor:pointer;transition:.15s}
.history-item:hover{background:rgba(16,185,129,.08);transform:translateX(2px)}
.hist-thumb{width:40px;height:40px;border-radius:9px;overflow:hidden;background:rgba(16,185,129,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:20px}
.hist-thumb img{width:100%;height:100%;object-fit:cover}
.hist-info{flex:1;min-width:0}
.hist-info strong{display:block;font-size:12.5px;color:#f1f5f9;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hist-info small{display:block;font-size:10.5px;color:#64748b;margin-top:2px}
.hist-conf{font-size:11px;font-weight:800;color:#34d399;flex-shrink:0}

/* TOAST */
.toast{position:fixed!important;bottom:28px!important;right:28px!important;padding:14px 22px!important;border-radius:12px!important;font-size:13.5px!important;font-weight:600!important;color:#fff!important;transform:translateY(20px)!important;opacity:0!important;transition:all .3s!important;z-index:999999!important;box-shadow:0 12px 32px rgba(0,0,0,.5)!important;pointer-events:none!important}
.toast.show{transform:translateY(0)!important;opacity:1!important}
.toast.success{background:linear-gradient(135deg,#10b981,#059669)!important}
.toast.error{background:linear-gradient(135deg,#ef4444,#dc2626)!important}
.toast.info{background:linear-gradient(135deg,#38bdf8,#0284c7)!important}

/* RESPONSIVE */
@media(max-width:1100px){.detect-layout{grid-template-columns:1fr}.detect-side{position:static}}
@media(max-width:768px){
    .crop-detect-page .main-wrap{margin-left:0!important;width:100%!important;padding-top:64px!important}
    .crop-detect-page .main-content{padding:14px!important}
    .crop-detect-page .glass-card{padding:18px!important}
    .detect-hero h1{font-size:20px!important}
    .tips-row{grid-template-columns:1fr}
    .result-hero{grid-template-columns:1fr}
    .result-img-wrap{max-width:280px;margin:0 auto}
    .result-actions{flex-direction:column}
    .res-act{width:100%;justify-content:center}
}
</style>

<script>
(function () {
    'use strict';

    var BASE = '<?= BASE_URL ?>';
    var input    = document.getElementById('imageInput');
    var zone     = document.getElementById('uploadZone');
    var prompt   = document.getElementById('uploadPrompt');
    var preview  = document.getElementById('previewImg');
    var changeBtn= document.getElementById('changeBtn');
    var analyzeBtn = document.getElementById('analyzeBtn');
    var form     = document.getElementById('detectForm');
    var resultCard = document.getElementById('resultCard');
    var detectCropId = document.getElementById('detectCropId');

    /* ═══════ CROP CHIPS ═══════ */
    document.querySelectorAll('.crop-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.crop-chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            detectCropId.value = this.dataset.crop || '';
        });
    });

    /* ═══════ UPLOAD ZONE ═══════ */
    zone.addEventListener('click', function (e) {
        if (e.target === changeBtn) return;
        if (preview.style.display === 'none') input.click();
    });

    ['dragenter', 'dragover'].forEach(function (ev) {
        zone.addEventListener(ev, function (e) {
            e.preventDefault();
            zone.classList.add('dragging');
        });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
        zone.addEventListener(ev, function (e) {
            e.preventDefault();
            zone.classList.remove('dragging');
        });
    });
    zone.addEventListener('drop', function (e) {
        var f = e.dataTransfer.files;
        if (f.length) { input.files = f; showPreview(f[0]); }
    });

    input.addEventListener('change', function () {
        if (this.files.length) showPreview(this.files[0]);
    });

    changeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        input.click();
    });

    function showPreview(file) {
        if (!file.type.startsWith('image/')) {
            toast('Please select an image file', 'error');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            toast('File too large (max 5 MB)', 'error');
            return;
        }
        var r = new FileReader();
        r.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            prompt.style.display = 'none';
            changeBtn.style.display = 'block';
            analyzeBtn.disabled = false;
        };
        r.readAsDataURL(file);
    }

    /* ═══════ SUBMIT ═══════ */
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!input.files.length) return;

        var originalLabel = analyzeBtn.innerHTML;
        analyzeBtn.disabled = true;
        analyzeBtn.innerHTML = '<span class="btn-icon">⏳</span><span class="btn-label">Analyzing photo…</span>';

        try {
            var fd = new FormData(form);
            var res = await fetch(BASE + '/crops/detect', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin'
            });
            var data = await res.json();

            if (data.success && data.result) {
                renderResult(data.result, preview.src);
                toast('✅ Analysis complete', 'success');
            } else {
                toast('⚠️ ' + (data.error || 'Detection failed'), 'error');
            }
        } catch (err) {
            toast('⚠️ ' + err.message, 'error');
        } finally {
            analyzeBtn.disabled = false;
            analyzeBtn.innerHTML = originalLabel;
        }
    });

    /* ═══════ RENDER RESULT ═══════ */
    function renderResult(r, imgSrc) {
        /* Image */
        document.getElementById('resultImage').src = r.image_url || imgSrc;

        /* Severity */
        var sev = (r.severity || 'medium').toLowerCase();
        var sevEl = document.getElementById('resultSev');
        sevEl.className = 'sev-pill ' + sev;
        sevEl.textContent = sev.toUpperCase() + ' SEVERITY';

        /* Name + pathogen + desc */
        document.getElementById('resultName').textContent = r.disease_name || 'Unknown';
        document.getElementById('resultPathogen').textContent = 'Pathogen: ' + (r.pathogen || 'Unknown');
        document.getElementById('resultDesc').textContent = r.description || 'No description available.';

        /* Confidence ring */
        var pct = Math.round((r.confidence || 0) * 100);
        document.getElementById('confText').textContent = pct + '%';
        var circumference = 213.6;
        document.getElementById('confRing').style.strokeDashoffset =
            circumference * (1 - pct / 100);

        /* Panels */
        document.getElementById('panelSymptoms').innerHTML =
            escapeHtml(r.symptoms || 'No symptoms data recorded.').replace(/\n/g, '<br>');
        document.getElementById('panelTreatment').innerHTML =
            escapeHtml(r.treatment || 'Consult an agricultural officer.').replace(/\n/g, '<br>');
        document.getElementById('panelPrevention').innerHTML =
            escapeHtml(r.recommendation || 'Monitor the crop regularly.').replace(/\n/g, '<br>');

        /* Gallery */
        var galEl = document.getElementById('panelGallery');
        var gallery = Array.isArray(r.gallery) ? r.gallery : [];
        if (gallery.length) {
            galEl.innerHTML = gallery.map(u =>
                '<img src="' + escapeHtml(u) + '" loading="lazy" onclick="window.open(this.src)">'
            ).join('');
        } else {
            galEl.innerHTML = '<p style="color:#64748b;grid-column:1/-1;text-align:center;padding:20px;">No additional photos</p>';
        }

        /* Show + scroll */
        resultCard.style.display = 'block';
        resultCard.scrollIntoView({ behavior: 'smooth', block: 'start' });

        /* Wire tabs */
        document.querySelectorAll('.rtab').forEach(function (tab) {
            tab.onclick = function () {
                document.querySelectorAll('.rtab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.rpanel').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                document.querySelector('.rpanel[data-panel="' + this.dataset.tab + '"]').classList.add('active');
            };
        });
    }

    /* ═══════ RESET ═══════ */
    window.resetDetect = function () {
        input.value = '';
        preview.src = '';
        preview.style.display = 'none';
        prompt.style.display = 'block';
        changeBtn.style.display = 'none';
        analyzeBtn.disabled = true;
        resultCard.style.display = 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    /* ═══════ REOPEN OLD DETECTION ═══════ */
    window.reopenDetection = function (id) {
        toast('Loading detection…', 'info');
        window.location.href = BASE + '/crops/detection/' + id;
    };

    /* ═══════ SAVE TO FARM ═══════ */
    window.saveToFarm = function () {
        var name = document.getElementById('resultName').textContent;
        var csrf = document.querySelector('#detectForm input[name="_csrf"]').value;
        var fd = new FormData();
        fd.append('disease_name', name);
        fd.append('_csrf', csrf);
        fetch(BASE + '/crops/saveDetection', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        }).then(r => r.json()).then(function (d) {
            if (d.success) toast('💾 Saved to your farm records', 'success');
            else toast('⚠️ ' + (d.error || 'Save failed'), 'error');
        }).catch(() => toast('⚠️ Network error', 'error'));
    };

    /* ═══════ SHARE ═══════ */
    window.shareResult = function () {
        var name = document.getElementById('resultName').textContent;
        var text = 'Disease detected: ' + name + ' — via SAIP AgriBot';
        if (navigator.share) {
            navigator.share({ title: 'Crop Disease Result', text: text }).catch(() => {});
        } else {
            navigator.clipboard.writeText(text).then(() => toast('📋 Copied to clipboard', 'success'));
        }
    };

    /* ═══════ HELPERS ═══════ */
    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
    function toast(msg, type) {
        var t = document.createElement('div');
        t.className = 'toast ' + (type || 'info');
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => t.classList.add('show'), 10);
        setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 2600);
    }
})();
</script>