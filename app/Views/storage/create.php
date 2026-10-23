<?php
/**
 * Storage — Create Warehouse
 * Premium form with type-picker cards, capacity slider, live preview
 */
?>
<div class="dashboard-layout storage-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="main-wrap">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="main-content">

            <!-- ═══════════ HERO ═══════════ -->
            <section class="glass-card create-hero">
                <a href="<?= BASE_URL ?>/storage" class="back-link">
                    <span>←</span> All warehouses
                </a>
                <h1>🏭 Create Warehouse</h1>
                <p>Set up a new storage facility. Choose the type, define capacity, and configure environmental controls.</p>
            </section>

            <!-- ═══════════ FORM ═══════════ -->
            <form method="POST" action="<?= BASE_URL ?>/storage/createWarehouse" class="create-form" id="createForm">
                <div class="create-layout">

                    <!-- LEFT: FORM FIELDS -->
                    <div class="create-main">

                        <!-- SECTION 1: BASIC INFO -->
                        <section class="glass-card">
                            <div class="section-head">
                                <span class="section-num">1</span>
                                <div>
                                    <h3>Basic Information</h3>
                                    <p>Name and location of the facility</p>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="fg full">
                                    <label>Warehouse Name *</label>
                                    <input type="text" name="name" id="whName" required
                                           placeholder="e.g., Huye Central Storage" maxlength="120"
                                           oninput="updatePreview()">
                                    <small class="fg-hint">Choose a clear, memorable name</small>
                                </div>
                            </div>

                            <div class="form-row two">
                                <div class="fg">
                                    <label>District ID</label>
                                    <input type="number" name="district_id" id="whDistrict"
                                           placeholder="1–30" min="1" max="30"
                                           oninput="updatePreview()">
                                    <small class="fg-hint">1=Kigali, 22=Huye, etc.</small>
                                </div>
                                <div class="fg">
                                    <label>Address</label>
                                    <input type="text" name="address" id="whAddress"
                                           placeholder="e.g., Ngoma Sector"
                                           maxlength="200" oninput="updatePreview()">
                                </div>
                            </div>
                        </section>

                        <!-- SECTION 2: TYPE -->
                        <section class="glass-card">
                            <div class="section-head">
                                <span class="section-num">2</span>
                                <div>
                                    <h3>Storage Type</h3>
                                    <p>Determines the environmental controls available</p>
                                </div>
                            </div>

                            <input type="hidden" name="warehouse_type" id="whTypeInput" value="dry">

                            <div class="type-grid">
                                <button type="button" class="type-card active" data-type="dry" onclick="pickType('dry')">
                                    <span class="type-icon">🏭</span>
                                    <strong>Dry</strong>
                                    <small>Standard dry storage for grains &amp; cereals</small>
                                    <span class="type-check">✓</span>
                                </button>
                                <button type="button" class="type-card" data-type="cold" onclick="pickType('cold')">
                                    <span class="type-icon">❄️</span>
                                    <strong>Cold</strong>
                                    <small>Refrigerated for perishables &amp; dairy</small>
                                    <span class="type-check">✓</span>
                                </button>
                                <button type="button" class="type-card" data-type="controlled" onclick="pickType('controlled')">
                                    <span class="type-icon">🎛️</span>
                                    <strong>Controlled</strong>
                                    <small>Climate-controlled for sensitive crops</small>
                                    <span class="type-check">✓</span>
                                </button>
                                <button type="button" class="type-card" data-type="hermetic" onclick="pickType('hermetic')">
                                    <span class="type-icon">🫙</span>
                                    <strong>Hermetic</strong>
                                    <small>Sealed for chemical-free pest control</small>
                                    <span class="type-check">✓</span>
                                </button>
                            </div>
                        </section>

                        <!-- SECTION 3: CAPACITY -->
                        <section class="glass-card">
                            <div class="section-head">
                                <span class="section-num">3</span>
                                <div>
                                    <h3>Capacity</h3>
                                    <p>Maximum storage volume</p>
                                </div>
                            </div>

                            <div class="fg">
                                <label>Capacity (kg) *</label>
                                <div class="capacity-wrap">
                                    <input type="range" id="whCapRange" min="1000" max="500000" step="1000" value="100000"
                                           oninput="syncCapacity(this.value)">
                                    <input type="number" name="capacity_kg" id="whCapacity" required
                                           min="1000" step="100" value="100000"
                                           oninput="syncCapacity(this.value, true)">
                                </div>
                                <div class="capacity-presets">
                                    <button type="button" onclick="syncCapacity(10000, true)">10 t</button>
                                    <button type="button" onclick="syncCapacity(50000, true)">50 t</button>
                                    <button type="button" onclick="syncCapacity(100000, true)">100 t</button>
                                    <button type="button" onclick="syncCapacity(250000, true)">250 t</button>
                                    <button type="button" onclick="syncCapacity(500000, true)">500 t</button>
                                </div>
                                <small class="fg-hint">Total kilograms the warehouse can hold</small>
                            </div>
                        </section>

                        <!-- SECTION 4: FEATURES -->
                        <section class="glass-card">
                            <div class="section-head">
                                <span class="section-num">4</span>
                                <div>
                                    <h3>Features &amp; Controls</h3>
                                    <p>Enable environmental systems</p>
                                </div>
                            </div>

                            <div class="toggle-list">
                                <label class="toggle-row">
                                    <div class="toggle-info">
                                        <span class="toggle-icon">💨</span>
                                        <div>
                                            <strong>Ventilation System</strong>
                                            <small>Air circulation for moisture control</small>
                                        </div>
                                    </div>
                                    <input type="checkbox" name="has_ventilation" value="1" checked class="toggle-input">
                                    <span class="toggle-switch"></span>
                                </label>

                                <label class="toggle-row">
                                    <div class="toggle-info">
                                        <span class="toggle-icon">❄️</span>
                                        <div>
                                            <strong>Cooling System</strong>
                                            <small>Active refrigeration for temperature control</small>
                                        </div>
                                    </div>
                                    <input type="checkbox" name="has_cooling" value="1" class="toggle-input">
                                    <span class="toggle-switch"></span>
                                </label>
                            </div>
                        </section>

                        <!-- SUBMIT -->
                        <div class="form-submit-bar">
                            <a href="<?= BASE_URL ?>/storage" class="btn-secondary">Cancel</a>
                            <button type="submit" class="btn-primary">
                                <span>✓</span> Create Warehouse
                            </button>
                        </div>
                    </div>

                    <!-- RIGHT: LIVE PREVIEW -->
                    <aside class="create-side">
                        <div class="glass-card preview-card">
                            <div class="preview-label">
                                <span class="pulse-dot"></span> LIVE PREVIEW
                            </div>

                            <div class="preview-icon" id="previewIcon">🏭</div>
                            <h3 class="preview-name" id="previewName">Untitled Warehouse</h3>
                            <p class="preview-meta" id="previewMeta">📍 District — · Dry storage</p>

                            <div class="preview-gauge">
                                <div class="preview-gauge-labels">
                                    <span>Capacity</span>
                                    <strong id="previewCap">100,000 kg</strong>
                                </div>
                                <div class="preview-gauge-track">
                                    <div class="preview-gauge-fill" style="width:15%;"></div>
                                </div>
                                <small class="preview-gauge-hint" id="previewCapHint">≈ 100 tonnes</small>
                            </div>

                            <ul class="preview-features" id="previewFeatures">
                                <li><span>💨</span> Ventilation</li>
                            </ul>

                            <div class="preview-note">
                                💡 You can add storage bins after creating the warehouse.
                            </div>
                        </div>
                    </aside>

                </div>
            </form>
        </main>
    </div>
</div>

<style>
/* ═══════════════════════════════════════════════════════════
   BASE
   ═══════════════════════════════════════════════════════════ */
html, body { margin: 0 !important; padding: 0 !important; overflow-x: hidden !important; }

.storage-page.dashboard-layout {
    display: block !important; width: 100% !important; max-width: 100% !important;
    min-height: 100vh !important; background: #0b1120 !important;
    margin: 0 !important; padding: 0 !important; box-sizing: border-box !important;
}
.storage-page .main-wrap {
    margin-left: 260px !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    min-height: 100vh !important; box-sizing: border-box !important;
}
.storage-page .main-wrap > .main-content {
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    padding: 20px 28px 60px !important;
    box-sizing: border-box !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 18px !important;
}
.storage-page .glass-card {
    background: rgba(30, 41, 59, 0.7) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    padding: 22px !important;
    margin: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

/* ═══════════════════════════════════════════════════════════
   HERO
   ═══════════════════════════════════════════════════════════ */
.create-hero {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08)) !important;
    padding: 24px 28px !important;
    position: relative !important;
    overflow: hidden !important;
}
.create-hero::before {
    content: '' !important;
    position: absolute !important;
    top: -40% !important; right: -10% !important;
    width: 380px !important; height: 380px !important;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.15), transparent 70%) !important;
    pointer-events: none !important;
}
.back-link {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    font-size: 12.5px !important; color: #94a3b8 !important;
    text-decoration: none !important;
    font-weight: 600 !important;
    margin-bottom: 14px !important;
    transition: color 0.2s !important;
    position: relative !important; z-index: 1 !important;
}
.back-link:hover { color: #34d399 !important; }
.create-hero h1 {
    font-size: 24px !important; font-weight: 800 !important;
    color: #f1f5f9 !important; margin: 0 0 6px 0 !important;
    position: relative !important; z-index: 1 !important;
}
.create-hero p {
    color: #94a3b8 !important; font-size: 13.5px !important;
    margin: 0 !important; max-width: 640px !important;
    position: relative !important; z-index: 1 !important;
    line-height: 1.55 !important;
}

/* ═══════════════════════════════════════════════════════════
   FORM LAYOUT
   ═══════════════════════════════════════════════════════════ */
.create-form { width: 100% !important; }
.create-layout {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) 340px !important;
    gap: 18px !important;
    align-items: start !important;
}
.create-main { display: flex !important; flex-direction: column !important; gap: 18px !important; min-width: 0 !important; }

.create-side { position: sticky !important; top: 20px !important; }

/* SECTION HEAD */
.section-head {
    display: flex !important; align-items: center !important;
    gap: 14px !important;
    margin-bottom: 18px !important;
    padding-bottom: 12px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
}
.section-num {
    width: 34px !important; height: 34px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important;
    border-radius: 10px !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    font-weight: 800 !important; font-size: 15px !important;
    flex-shrink: 0 !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35) !important;
}
.section-head h3 {
    font-size: 15px !important; font-weight: 800 !important;
    color: #f1f5f9 !important; margin: 0 0 2px 0 !important;
}
.section-head p { font-size: 12px !important; color: #94a3b8 !important; margin: 0 !important; }

/* FIELDS */
.form-row { display: flex !important; flex-direction: column !important; gap: 14px !important; margin-bottom: 14px !important; }
.form-row.two { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 14px !important; }
.form-row:last-child { margin-bottom: 0 !important; }

.fg { display: flex !important; flex-direction: column !important; gap: 6px !important; }
.fg.full { grid-column: 1 / -1 !important; }
.fg label {
    font-size: 11px !important; font-weight: 700 !important;
    color: #94a3b8 !important; text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}
.fg input[type="text"],
.fg input[type="number"] {
    padding: 12px 15px !important;
    background: rgba(0, 0, 0, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 11px !important;
    color: #f1f5f9 !important; font-size: 14px !important;
    font-family: inherit !important; outline: none !important;
    box-sizing: border-box !important; width: 100% !important;
    transition: all 0.2s !important;
}
.fg input:focus {
    border-color: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
    background: rgba(0, 0, 0, 0.45) !important;
}
.fg-hint { font-size: 11px !important; color: #64748b !important; font-style: italic !important; }

/* ═══════════════════════════════════════════════════════════
   TYPE PICKER
   ═══════════════════════════════════════════════════════════ */
.type-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
    gap: 12px !important;
}
.type-card {
    position: relative !important;
    display: flex !important; flex-direction: column !important;
    align-items: flex-start !important; gap: 6px !important;
    padding: 18px !important;
    background: rgba(0, 0, 0, 0.25) !important;
    border: 2px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 14px !important;
    cursor: pointer !important; font-family: inherit !important;
    text-align: left !important; transition: all 0.2s !important;
    color: #cbd5e1 !important;
}
.type-card:hover {
    border-color: rgba(16, 185, 129, 0.4) !important;
    background: rgba(16, 185, 129, 0.05) !important;
    transform: translateY(-2px) !important;
}
.type-card.active {
    border-color: #10b981 !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08)) !important;
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.2) !important;
}
.type-icon { font-size: 26px !important; margin-bottom: 4px !important; }
.type-card strong {
    font-size: 14px !important; color: #f1f5f9 !important;
    font-weight: 800 !important;
}
.type-card small {
    font-size: 11.5px !important; color: #94a3b8 !important;
    line-height: 1.4 !important;
}
.type-check {
    position: absolute !important;
    top: 12px !important; right: 12px !important;
    width: 24px !important; height: 24px !important;
    background: #10b981 !important; color: #fff !important;
    border-radius: 50% !important;
    display: none !important; align-items: center !important; justify-content: center !important;
    font-size: 13px !important; font-weight: 900 !important;
}
.type-card.active .type-check { display: flex !important; }

/* ═══════════════════════════════════════════════════════════
   CAPACITY
   ═══════════════════════════════════════════════════════════ */
.capacity-wrap {
    display: flex !important; flex-direction: column !important;
    gap: 12px !important;
}
#whCapRange {
    -webkit-appearance: none !important; appearance: none !important;
    width: 100% !important; height: 8px !important;
    background: rgba(0, 0, 0, 0.4) !important;
    border-radius: 999px !important; outline: none !important;
    cursor: pointer !important;
}
#whCapRange::-webkit-slider-thumb {
    -webkit-appearance: none !important; appearance: none !important;
    width: 22px !important; height: 22px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    border-radius: 50% !important;
    border: 3px solid #0b1120 !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.5) !important;
    cursor: pointer !important;
}
#whCapRange::-moz-range-thumb {
    width: 22px !important; height: 22px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    border-radius: 50% !important; border: 3px solid #0b1120 !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.5) !important;
    cursor: pointer !important;
}

.capacity-presets {
    display: flex !important; gap: 6px !important; flex-wrap: wrap !important;
}
.capacity-presets button {
    padding: 6px 14px !important;
    background: rgba(255, 255, 255, 0.04) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 999px !important;
    font-size: 11.5px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    transition: all 0.15s !important;
}
.capacity-presets button:hover {
    background: rgba(16, 185, 129, 0.15) !important;
    color: #34d399 !important;
    border-color: rgba(16, 185, 129, 0.35) !important;
}

/* ═══════════════════════════════════════════════════════════
   TOGGLES
   ═══════════════════════════════════════════════════════════ */
.toggle-list { display: flex !important; flex-direction: column !important; gap: 10px !important; }
.toggle-row {
    display: flex !important; align-items: center !important; gap: 14px !important;
    padding: 14px 16px !important;
    background: rgba(0, 0, 0, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    border-radius: 12px !important;
    cursor: pointer !important;
    transition: all 0.15s !important;
}
.toggle-row:hover { background: rgba(255, 255, 255, 0.03) !important; }
.toggle-info { flex: 1 !important; display: flex !important; align-items: center !important; gap: 12px !important; min-width: 0 !important; }
.toggle-icon {
    width: 40px !important; height: 40px !important;
    background: rgba(16, 185, 129, 0.12) !important;
    border-radius: 10px !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    font-size: 20px !important;
    flex-shrink: 0 !important;
}
.toggle-info strong {
    display: block !important; font-size: 13.5px !important;
    color: #f1f5f9 !important; font-weight: 700 !important;
    margin-bottom: 2px !important;
}
.toggle-info small { display: block !important; font-size: 11.5px !important; color: #94a3b8 !important; }
.toggle-input { display: none !important; }
.toggle-switch {
    width: 46px !important; height: 26px !important;
    background: rgba(255, 255, 255, 0.1) !important;
    border-radius: 999px !important;
    position: relative !important;
    transition: all 0.25s !important;
    flex-shrink: 0 !important;
}
.toggle-switch::after {
    content: '' !important;
    position: absolute !important;
    top: 3px !important; left: 3px !important;
    width: 20px !important; height: 20px !important;
    background: #fff !important;
    border-radius: 50% !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3) !important;
}
.toggle-input:checked ~ .toggle-switch {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    box-shadow: 0 0 16px rgba(16, 185, 129, 0.4) !important;
}
.toggle-input:checked ~ .toggle-switch::after { left: 23px !important; }

/* ═══════════════════════════════════════════════════════════
   SUBMIT BAR
   ═══════════════════════════════════════════════════════════ */
.form-submit-bar {
    display: flex !important; justify-content: flex-end !important;
    gap: 10px !important;
    padding: 18px 22px !important;
    background: rgba(30, 41, 59, 0.7) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 20px !important;
    flex-wrap: wrap !important;
}
.btn-primary, .btn-secondary {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    gap: 8px !important; padding: 12px 26px !important;
    border-radius: 10px !important;
    font-size: 13.5px !important; font-weight: 700 !important;
    cursor: pointer !important; font-family: inherit !important;
    transition: all 0.2s !important; text-decoration: none !important;
    border: none !important;
}
.btn-primary {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #fff !important;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35) !important;
}
.btn-primary:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 22px rgba(16, 185, 129, 0.55) !important; }
.btn-secondary {
    background: rgba(255, 255, 255, 0.05) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
}
.btn-secondary:hover { background: rgba(255, 255, 255, 0.1) !important; }

/* ═══════════════════════════════════════════════════════════
   PREVIEW CARD
   ═══════════════════════════════════════════════════════════ */
.preview-card {
    position: relative !important;
    overflow: hidden !important;
    background: linear-gradient(160deg, rgba(16, 185, 129, 0.08), rgba(30, 41, 59, 0.7)) !important;
}
.preview-card::before {
    content: '' !important;
    position: absolute !important;
    top: -30% !important; right: -30% !important;
    width: 240px !important; height: 240px !important;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.12), transparent 70%) !important;
    pointer-events: none !important;
}
.preview-label {
    display: inline-flex !important; align-items: center !important; gap: 6px !important;
    padding: 4px 12px !important;
    background: rgba(16, 185, 129, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(16, 185, 129, 0.3) !important;
    border-radius: 999px !important;
    font-size: 10px !important; font-weight: 800 !important;
    letter-spacing: 1.2px !important;
    text-transform: uppercase !important;
    margin-bottom: 20px !important;
    position: relative !important; z-index: 1 !important;
}
.pulse-dot {
    width: 6px !important; height: 6px !important;
    background: #34d399 !important; border-radius: 50% !important;
    animation: pulse 1.6s infinite !important;
    box-shadow: 0 0 8px #34d399 !important;
}
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }

.preview-icon {
    width: 64px !important; height: 64px !important;
    border-radius: 18px !important;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(56, 189, 248, 0.15)) !important;
    display: flex !important; align-items: center !important; justify-content: center !important;
    font-size: 32px !important;
    margin-bottom: 14px !important;
    border: 1px solid rgba(16, 185, 129, 0.25) !important;
    position: relative !important; z-index: 1 !important;
}
.preview-name {
    font-size: 18px !important; font-weight: 800 !important;
    color: #f1f5f9 !important; margin: 0 0 6px 0 !important;
    line-height: 1.25 !important; word-break: break-word !important;
    position: relative !important; z-index: 1 !important;
}
.preview-meta {
    font-size: 12.5px !important; color: #94a3b8 !important;
    margin: 0 0 20px 0 !important;
    position: relative !important; z-index: 1 !important;
}
.preview-gauge {
    padding: 14px !important;
    background: rgba(0, 0, 0, 0.25) !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
    border-radius: 12px !important;
    margin-bottom: 16px !important;
    position: relative !important; z-index: 1 !important;
}
.preview-gauge-labels {
    display: flex !important; justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 8px !important;
    font-size: 11.5px !important;
}
.preview-gauge-labels span { color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; font-weight: 700 !important; }
.preview-gauge-labels strong { color: #f1f5f9 !important; font-weight: 800 !important; font-variant-numeric: tabular-nums !important; }
.preview-gauge-track {
    height: 8px !important;
    background: rgba(0, 0, 0, 0.4) !important;
    border-radius: 999px !important;
    overflow: hidden !important;
}
.preview-gauge-fill {
    height: 100% !important;
    background: linear-gradient(90deg, #10b981, #38bdf8) !important;
    border-radius: 999px !important;
    transition: width 0.4s ease-out !important;
}
.preview-gauge-hint {
    display: block !important; margin-top: 6px !important;
    font-size: 11px !important; color: #64748b !important;
    font-style: italic !important;
}

.preview-features {
    list-style: none !important; padding: 0 !important; margin: 0 0 16px 0 !important;
    display: flex !important; flex-direction: column !important; gap: 6px !important;
    position: relative !important; z-index: 1 !important;
}
.preview-features li {
    display: flex !important; align-items: center !important; gap: 8px !important;
    padding: 8px 12px !important;
    background: rgba(16, 185, 129, 0.08) !important;
    border-radius: 9px !important;
    font-size: 12.5px !important; color: #cbd5e1 !important;
    font-weight: 600 !important;
    border-left: 3px solid #10b981 !important;
}
.preview-features li span { font-size: 14px !important; }

.preview-note {
    padding: 12px 14px !important;
    background: rgba(251, 191, 36, 0.08) !important;
    border-left: 3px solid #fbbf24 !important;
    border-radius: 10px !important;
    font-size: 12px !important; color: #fbbf24 !important;
    line-height: 1.5 !important;
    position: relative !important; z-index: 1 !important;
}

/* RESPONSIVE */
@media (max-width: 1100px) {
    .create-layout { grid-template-columns: 1fr !important; }
    .create-side { position: static !important; }
}
@media (max-width: 768px) {
    .storage-page .main-wrap {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding-top: 64px !important;
    }
    .storage-page .main-wrap > .main-content { padding: 14px !important; }
    .storage-page .glass-card { padding: 18px !important; border-radius: 16px !important; }
    .form-row.two { grid-template-columns: 1fr !important; }
    .type-grid { grid-template-columns: 1fr !important; }
    .form-submit-bar { flex-direction: column-reverse !important; }
    .form-submit-bar .btn-primary,
    .form-submit-bar .btn-secondary { width: 100% !important; }
    .create-hero h1 { font-size: 20px !important; }
}
</style>

<script>
(function () {
    'use strict';

    /* Type picker */
    window.pickType = function (type) {
        document.querySelectorAll('.type-card').forEach(function (c) {
            c.classList.toggle('active', c.getAttribute('data-type') === type);
        });
        document.getElementById('whTypeInput').value = type;
        updatePreview();
    };

    /* Capacity sync */
    window.syncCapacity = function (value, fromNumber) {
        var num = parseInt(value, 10) || 1000;
        var range = document.getElementById('whCapRange');
        var input = document.getElementById('whCapacity');
        if (fromNumber) {
            range.value = num;
        } else {
            input.value = num;
        }
        updatePreview();
    };

    /* Live preview */
    window.updatePreview = function () {
        var name = (document.getElementById('whName').value || '').trim() || 'Untitled Warehouse';
        var district = document.getElementById('whDistrict').value || '';
        var type = document.getElementById('whTypeInput').value || 'dry';
        var cap = parseInt(document.getElementById('whCapacity').value, 10) || 0;
        var hasVent = document.querySelector('input[name="has_ventilation"]').checked;
        var hasCool = document.querySelector('input[name="has_cooling"]').checked;

        var icons  = { dry: '🏭', cold: '❄️', controlled: '🎛️', hermetic: '🫙' };
        var labels = { dry: 'Dry storage', cold: 'Cold storage', controlled: 'Controlled atmosphere', hermetic: 'Hermetic sealed' };

        document.getElementById('previewIcon').textContent = icons[type] || '🏭';
        document.getElementById('previewName').textContent = name;
        document.getElementById('previewMeta').textContent =
            '📍 ' + (district ? 'District ' + district : 'District —') + ' · ' + (labels[type] || 'Dry storage');

        document.getElementById('previewCap').textContent = cap.toLocaleString() + ' kg';
        document.getElementById('previewCapHint').textContent = '≈ ' + (cap / 1000).toFixed(1) + ' tonnes';

        /* gauge bar — relative to 500t max */
        var pct = Math.min(100, Math.max(4, (cap / 500000) * 100));
        document.querySelector('.preview-gauge-fill').style.width = pct + '%';

        /* features */
        var features = [];
        if (hasVent) features.push('<li><span>💨</span> Ventilation</li>');
        if (hasCool) features.push('<li><span>❄️</span> Cooling system</li>');
        if (!features.length) features.push('<li><span>📦</span> Basic storage only</li>');
        document.getElementById('previewFeatures').innerHTML = features.join('');
    };

    /* Watch toggles */
    document.querySelectorAll('.toggle-input').forEach(function (cb) {
        cb.addEventListener('change', updatePreview);
    });

    /* Initial render */
    updatePreview();
})();
</script>