<?php
/* Resolve data — works whether $data[] is set or extract() ran */
$translations = $translations ?? ($data['translations'] ?? []);
$languages    = $languages    ?? ($data['languages']    ?? []);
$categories   = $categories   ?? ($data['categories']   ?? []);
$filterLang   = $filterLang   ?? ($data['filter_lang']  ?? 'en');
$csrf         = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(16));

/* Build stats */
$totalKeys      = is_array($translations) ? count($translations) : 0;
$translated     = 0;
$empty          = 0;
$categoriesUsed = [];

if (is_array($translations)) {
    foreach ($translations as $t) {
        if (!is_array($t)) continue;
        if (trim((string)($t['translated_text'] ?? '')) !== '') $translated++;
        else $empty++;
        $cat = $t['category_name'] ?? 'Uncategorized';
        $categoriesUsed[$cat] = ($categoriesUsed[$cat] ?? 0) + 1;
    }
}

$pct = $totalKeys > 0 ? round(($translated / $totalKeys) * 100) : 0;

$currentLang = null;
if (is_array($languages)) {
    foreach ($languages as $l) {
        if (!is_array($l)) continue;
        if (($l['code'] ?? '') === $filterLang) { $currentLang = $l; break; }
    }
}
?>
<div class="dashboard-layout admin-tr-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ═══════════════════════ HERO ═══════════════════════ -->
        <section class="glass-card hero-card">
            <div class="hero-left">
                <span class="hero-tag">🌐 i18n ENGINE</span>
                <h1>Translation Management</h1>
                <p>Manage keys and strings across English, Kinyarwanda, French, and Kiswahili. Edits save automatically.</p>
            </div>

            <div class="hero-stats">
                <div class="hstat">
                    <span class="hstat-value"><?= number_format($totalKeys) ?></span>
                    <span class="hstat-label">Keys</span>
                </div>
                <div class="hstat">
                    <span class="hstat-value ok"><?= number_format($translated) ?></span>
                    <span class="hstat-label">Translated</span>
                </div>
                <div class="hstat">
                    <span class="hstat-value warn"><?= number_format($empty) ?></span>
                    <span class="hstat-label">Missing</span>
                </div>
                <div class="hstat">
                    <span class="hstat-value"><?= $pct ?>%</span>
                    <span class="hstat-label">Complete</span>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════ PROGRESS BAR ═══════════════════════ -->
        <div class="progress-wrap">
            <div class="progress-label">
                <span><strong><?= $currentLang['flag_icon'] ?? '🌐' ?> <?= e($currentLang['name'] ?? $filterLang) ?></strong> translation progress</span>
                <span><?= $translated ?> / <?= $totalKeys ?> keys complete</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $pct ?>%"></div>
            </div>
        </div>

        <!-- ═══════════════════════ TOOLBAR ═══════════════════════ -->
        <section class="glass-card toolbar-card">
            <div class="toolbar-row">
                <!-- Language switcher -->
                <div class="lang-switch">
                    <?php foreach ($languages as $l): ?>
                        <a href="?lang=<?= e($l['code']) ?>"
                           class="lang-chip <?= $filterLang === $l['code'] ? 'active' : '' ?>"
                           title="<?= e($l['name']) ?>">
                            <span class="lang-flag"><?= $l['flag_icon'] ?? '🌐' ?></span>
                            <span class="lang-code"><?= strtoupper($l['code']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Search -->
                <div class="search-wrap">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" id="trSearch" placeholder="Search keys, translations, categories…" autocomplete="off">
                    <button type="button" class="search-clear" id="trSearchClear" onclick="clearSearch()" hidden>✕</button>
                </div>

                <!-- Actions -->
                <div class="toolbar-actions">
                    <button type="button" class="btn-primary" onclick="openAddModal()">➕ Add Key</button>
                    <button type="button" class="btn-secondary" onclick="exportCsv()" title="Export current language as CSV">⬇️ Export</button>
                </div>
            </div>

            <!-- Category filter chips -->
            <div class="chip-row" id="chipRow">
                <button type="button" class="chip active" data-cat="__all__">
                    All <span class="chip-count"><?= $totalKeys ?></span>
                </button>
                <?php foreach ($categoriesUsed as $catName => $count): ?>
                    <button type="button" class="chip" data-cat="<?= e($catName) ?>">
                        <?= e($catName) ?> <span class="chip-count"><?= $count ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ═══════════════════════ TRANSLATION TABLE ═══════════════════════ -->
        <section class="glass-card table-card">
            <?php if (empty($translations)): ?>
                <div class="empty-state">
                    <div class="empty-emoji">🌐</div>
                    <h3>No Translations Found</h3>
                    <p>Add your first translation key to get started.</p>
                    <button type="button" class="btn-primary" onclick="openAddModal()">➕ Add Key</button>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="data-table" id="trTable">
                        <thead>
                            <tr>
                                <th style="width: 26%;">Key</th>
                                <th style="width: 14%;">Category</th>
                                <th style="width: 46%;">Translation</th>
                                <th style="width: 14%; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($translations as $i => $t):
                                $key      = $t['translation_key'] ?? '';
                                $catName  = $t['category_name']   ?? 'Uncategorized';
                                $text     = $t['translated_text'] ?? '';
                                $lang     = $t['lang_code']       ?? $filterLang;
                                $isEmpty  = trim($text) === '';
                                $charCount = mb_strlen($text);
                            ?>
                                <tr class="tr-row"
                                    data-key="<?= e($key) ?>"
                                    data-cat="<?= e($catName) ?>"
                                    data-lang="<?= e($lang) ?>"
                                    data-search="<?= e(strtolower($key . ' ' . $text . ' ' . $catName)) ?>">

                                    <td>
                                        <div class="key-cell">
                                            <code><?= e($key) ?></code>
                                            <button type="button" class="copy-btn"
                                                    onclick="copyKey('<?= e($key) ?>')"
                                                    title="Copy key">📋</button>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="cat-badge"><?= e($catName) ?></span>
                                    </td>

                                    <td>
                                        <div class="input-wrap">
                                            <input type="text"
                                                   class="trans-input <?= $isEmpty ? 'is-empty' : '' ?>"
                                                   value="<?= e($text) ?>"
                                                   data-key="<?= e($key) ?>"
                                                   data-lang="<?= e($lang) ?>"
                                                   data-original="<?= e($text) ?>"
                                                   placeholder="Enter translation…"
                                                   autocomplete="off">
                                            <span class="char-count"><?= $charCount ?></span>
                                        </div>
                                    </td>

                                    <td style="text-align: center;">
                                        <span class="save-indicator" data-key="<?= e($key) ?>">
                                            <?= $isEmpty ? '<span class="dot warn"></span>' : '<span class="dot ok"></span>' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer stats -->
                <div class="table-footer">
                    <span id="visibleCount">Showing <?= $totalKeys ?> of <?= $totalKeys ?> keys</span>
                    <span class="footer-hint">💡 Changes save automatically as you type</span>
                </div>
            <?php endif; ?>
        </section>

        <!-- ═══════════════════════ LEGEND ═══════════════════════ -->
        <section class="glass-card legend-card">
            <div class="legend-item">
                <span class="dot ok"></span>
                <span>Translated</span>
            </div>
            <div class="legend-item">
                <span class="dot warn"></span>
                <span>Missing translation</span>
            </div>
            <div class="legend-item">
                <span class="dot saving"></span>
                <span>Saving…</span>
            </div>
            <div class="legend-item">
                <span class="dot error"></span>
                <span>Save failed</span>
            </div>
        </section>
    </main>
</div>

<!-- ═══════════════════════ ADD KEY MODAL ═══════════════════════ -->
<div class="modal-overlay" id="addModal" hidden>
    <div class="modal">
        <div class="modal-header">
            <h3>➕ Add Translation Key</h3>
            <button type="button" class="modal-close" onclick="closeAddModal()">✕</button>
        </div>
        <form id="addForm" onsubmit="event.preventDefault(); submitAdd();">
            <input type="hidden" name="lang" value="<?= e($filterLang) ?>">
            <div class="form-group">
                <label>Key *</label>
                <input type="text" id="newKey" required placeholder="e.g. dashboard.welcome" maxlength="150" autocomplete="off">
                <small class="form-hint">Use dot notation (section.key)</small>
            </div>
            <div class="form-group">
                <label>Translation *</label>
                <textarea id="newText" required rows="3" placeholder="The translated string…" maxlength="2000"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="addSubmitBtn">💾 Save Key</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    'use strict';

    var BASE  = '<?= BASE_URL ?>';
    var CSRF  = <?= json_encode($csrf) ?>;
    var LANG  = <?= json_encode($filterLang) ?>;

    /* ═══════════════════════ AUTO-SAVE ═══════════════════════ */
    var saveTimers = {};

    document.querySelectorAll('.trans-input').forEach(function (input) {
        input.addEventListener('input', function () {
            var key = this.dataset.key;
            updateCharCount(this);
            setIndicator(key, 'saving');
            clearTimeout(saveTimers[key]);
            saveTimers[key] = setTimeout(function () {
                saveTranslation(input);
            }, 800);
        });

        /* Immediate save on blur if changed */
        input.addEventListener('blur', function () {
            if (this.value !== this.dataset.original) {
                clearTimeout(saveTimers[this.dataset.key]);
                saveTranslation(this);
            }
        });

        /* Escape reverts */
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                this.value = this.dataset.original;
                updateCharCount(this);
                this.blur();
            }
        });

        updateCharCount(input);
    });

    async function saveTranslation(input) {
        var key  = input.dataset.key;
        var lang = input.dataset.lang;
        var text = input.value;

        if (text === input.dataset.original) {
            setIndicator(key, '');
            return;
        }

        setIndicator(key, 'saving');

        try {
            var body = new URLSearchParams();
            body.append('key',  key);
            body.append('lang', lang);
            body.append('text', text);
            body.append('_csrf', CSRF);

            var res = await fetch(BASE + '/admin/saveTranslation', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString(),
                credentials: 'same-origin'
            });

            var data = await res.json().catch(function () { return { success: false }; });
            if (data.success) {
                input.dataset.original = text;
                input.classList.toggle('is-empty', text.trim() === '');
                setIndicator(key, text.trim() === '' ? '' : 'saved');
            } else {
                setIndicator(key, 'error');
                console.error('Save failed:', data.error || 'unknown');
            }
        } catch (e) {
            setIndicator(key, 'error');
            console.error(e);
        }
    }

    function updateCharCount(input) {
        var el = input.parentElement.querySelector('.char-count');
        if (el) el.textContent = input.value.length;
    }

    function setIndicator(key, state) {
        var ind = document.querySelector('.save-indicator[data-key="' + CSS.escape(key) + '"]');
        if (!ind) return;

        if (state === 'saving') {
            ind.innerHTML = '<span class="dot saving"></span>';
        } else if (state === 'saved') {
            ind.innerHTML = '<span class="dot ok"></span>';
            /* brief pulse */
            ind.classList.add('pulse');
            setTimeout(function () { ind.classList.remove('pulse'); }, 700);
        } else if (state === 'error') {
            ind.innerHTML = '<span class="dot error"></span>';
        } else {
            /* neutral: base on current input */
            var input = document.querySelector('.trans-input[data-key="' + CSS.escape(key) + '"]');
            var empty = input ? input.value.trim() === '' : true;
            ind.innerHTML = empty ? '<span class="dot warn"></span>' : '<span class="dot ok"></span>';
        }
    }

    /* ═══════════════════════ SEARCH + FILTER ═══════════════════════ */
    var searchEl  = document.getElementById('trSearch');
    var searchClear = document.getElementById('trSearchClear');
    var currentCat = '__all__';

    searchEl.addEventListener('input', function () {
        searchClear.hidden = !this.value;
        applyFilters();
    });

    window.clearSearch = function () {
        searchEl.value = '';
        searchClear.hidden = true;
        applyFilters();
        searchEl.focus();
    };

    document.querySelectorAll('#chipRow .chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('#chipRow .chip').forEach(function (c) { c.classList.remove('active'); });
            this.classList.add('active');
            currentCat = this.dataset.cat;
            applyFilters();
        });
    });

    function applyFilters() {
        var q = (searchEl.value || '').toLowerCase().trim();
        var visible = 0;

        document.querySelectorAll('.tr-row').forEach(function (row) {
            var matchSearch = !q || (row.dataset.search || '').indexOf(q) !== -1;
            var matchCat = currentCat === '__all__' || row.dataset.cat === currentCat;
            var show = matchSearch && matchCat;

            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        var total = document.querySelectorAll('.tr-row').length;
        document.getElementById('visibleCount').textContent = 'Showing ' + visible + ' of ' + total + ' keys';

        /* Toggle "no results" empty state */
        var noRes = document.getElementById('noResults');
        if (visible === 0 && !noRes) {
            var wrapper = document.querySelector('.table-wrapper');
            if (wrapper) {
                noRes = document.createElement('div');
                noRes.id = 'noResults';
                noRes.className = 'no-results';
                noRes.innerHTML = '<div class="empty-emoji">🔍</div><h3>No matching keys</h3><p>Try a different search term or filter.</p>';
                wrapper.appendChild(noRes);
            }
        } else if (visible > 0 && noRes) {
            noRes.remove();
        }
    }

    /* ═══════════════════════ COPY KEY ═══════════════════════ */
    window.copyKey = function (key) {
        var notify = function () { toast('📋 Key copied', 'success'); };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(key).then(notify).catch(function () {
                fallbackCopy(key); notify();
            });
        } else {
            fallbackCopy(key); notify();
        }
    };

    /* ═══════════════════════ ADD MODAL ═══════════════════════ */
    window.openAddModal = function () {
        document.getElementById('addModal').hidden = false;
        document.body.style.overflow = 'hidden';
        setTimeout(function () { document.getElementById('newKey').focus(); }, 50);
    };

    window.closeAddModal = function () {
        document.getElementById('addModal').hidden = true;
        document.body.style.overflow = '';
        document.getElementById('addForm').reset();
    };

    window.submitAdd = async function () {
        var key  = document.getElementById('newKey').value.trim();
        var text = document.getElementById('newText').value.trim();
        if (!key || !text) { toast('Both fields are required', 'error'); return; }

        var btn = document.getElementById('addSubmitBtn');
        var orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ Saving…';

        try {
            var body = new URLSearchParams();
            body.append('key', key);
            body.append('lang', LANG);
            body.append('text', text);
            body.append('_csrf', CSRF);

            var res = await fetch(BASE + '/admin/saveTranslation', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString(),
                credentials: 'same-origin'
            });
            var data = await res.json().catch(function () { return { success: false }; });

            if (data.success) {
                toast('✅ Translation saved', 'success');
                setTimeout(function () { location.reload(); }, 700);
            } else {
                toast(data.error || 'Save failed', 'error');
                btn.disabled = false;
                btn.innerHTML = orig;
            }
        } catch (e) {
            toast('Error: ' + e.message, 'error');
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    };

    /* ═══════════════════════ EXPORT CSV ═══════════════════════ */
    window.exportCsv = function () {
        var rows = [['key', 'category', 'translation']];
        document.querySelectorAll('.tr-row').forEach(function (row) {
            if (row.style.display === 'none') return;
            var key  = row.dataset.key;
            var cat  = row.dataset.cat;
            var txt  = row.querySelector('.trans-input').value;
            rows.push([key, cat, txt]);
        });

        var csv = rows.map(function (r) {
            return r.map(function (cell) {
                var s = String(cell == null ? '' : cell).replace(/"/g, '""');
                return /[",\n]/.test(s) ? '"' + s + '"' : s;
            }).join(',');
        }).join('\n');

        var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'saip_translations_' + LANG + '_' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
        toast('📥 Exported ' + (rows.length - 1) + ' keys', 'success');
    };

    /* ═══════════════════════ HELPERS ═══════════════════════ */
    function fallbackCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        ta.remove();
    }

    window.toast = function (msg, type) {
        var t = document.createElement('div');
        t.className = 'toast ' + (type || 'info');
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { t.classList.add('show'); }, 10);
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { t.remove(); }, 300);
        }, 2600);
    };

    /* ═══════════════════════ MODAL DISMISS ═══════════════════════ */
    document.getElementById('addModal').addEventListener('click', function (e) {
        if (e.target === this) closeAddModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAddModal();
        /* Ctrl+F focuses search */
        if ((e.ctrlKey || e.metaKey) && e.key === 'f' && !e.target.matches('input,textarea')) {
            e.preventDefault();
            searchEl.focus();
        }
    });
})();
</script>

<style>
/* ═══════════════════════════════════════════════════════════
   LAYOUT
   ═══════════════════════════════════════════════════════════ */
html, body { margin: 0 !important; padding: 0 !important; overflow-x: hidden !important; }

.admin-tr-page.dashboard-layout {
    display: flex !important;
    width: 100% !important;
    min-height: 100vh !important;
    background-color: #0b1120 !important;
}
.admin-tr-page .main-content {
    flex: 1 !important;
    width: calc(100% - 260px) !important;
    max-width: calc(100% - 260px) !important;
    padding: 24px 32px 60px !important;
    box-sizing: border-box !important;
}
.admin-tr-page .glass-card {
    background: rgba(30, 41, 59, 0.65) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 18px !important;
    padding: 22px !important;
    margin-bottom: 18px !important;
    box-sizing: border-box !important;
}

/* ═══════════════════════════════════════════════════════════
   HERO
   ═══════════════════════════════════════════════════════════ */
.hero-card {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 26px !important;
    flex-wrap: wrap !important;
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.10), rgba(167, 139, 250, 0.06)) !important;
    padding: 26px 28px !important;
}
.hero-left { flex: 1 !important; min-width: 260px !important; }
.hero-tag {
    display: inline-flex;
    padding: 4px 12px;
    background: rgba(56, 189, 248, 0.15);
    color: #38bdf8;
    border: 1px solid rgba(56, 189, 248, 0.3);
    border-radius: 999px;
    font-size: 10.5px; font-weight: 800;
    letter-spacing: 1.2px; text-transform: uppercase;
    margin-bottom: 10px;
}
.hero-card h1 { font-size: 26px; font-weight: 800; color: #f1f5f9; margin: 0 0 6px; }
.hero-card p { color: #94a3b8; font-size: 13.5px; margin: 0; max-width: 620px; line-height: 1.55; }

.hero-stats { display: flex !important; gap: 14px !important; flex-wrap: wrap !important; }
.hstat {
    display: flex; flex-direction: column;
    align-items: center; gap: 4px;
    padding: 12px 18px;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 12px;
    min-width: 82px;
}
.hstat-value { font-size: 22px; font-weight: 800; color: #f1f5f9; line-height: 1; }
.hstat-value.ok   { color: #34d399; }
.hstat-value.warn { color: #fbbf24; }
.hstat-label {
    font-size: 10px; font-weight: 700;
    color: #94a3b8; text-transform: uppercase;
    letter-spacing: 0.8px;
}

/* ═══════════════════════════════════════════════════════════
   PROGRESS BAR
   ═══════════════════════════════════════════════════════════ */
.progress-wrap {
    padding: 0 4px;
    margin-bottom: 18px;
}
.progress-label {
    display: flex; justify-content: space-between;
    font-size: 12px; color: #94a3b8;
    margin-bottom: 8px; font-weight: 600;
}
.progress-label strong { color: #f1f5f9; font-weight: 800; }
.progress-bar {
    height: 8px; width: 100%;
    background: rgba(255, 255, 255, 0.06);
    border-radius: 999px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #38bdf8);
    border-radius: 999px;
    transition: width 0.5s ease-out;
    box-shadow: 0 0 12px rgba(16, 185, 129, 0.4);
}

/* ═══════════════════════════════════════════════════════════
   TOOLBAR
   ═══════════════════════════════════════════════════════════ */
.toolbar-card { padding: 18px 20px !important; }
.toolbar-row {
    display: flex; gap: 14px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 14px;
}
.lang-switch {
    display: flex; gap: 6px;
    padding: 4px;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
}
.lang-chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px;
    border-radius: 9px;
    color: #94a3b8;
    font-size: 12px; font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
}
.lang-chip:hover { background: rgba(255, 255, 255, 0.05); color: #cbd5e1; }
.lang-chip.active {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
}
.lang-flag { font-size: 15px; }
.lang-code { letter-spacing: 0.5px; }

.search-wrap {
    flex: 1; min-width: 240px;
    position: relative;
}
.search-icon {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    color: #64748b; pointer-events: none;
}
.search-wrap input {
    width: 100%;
    padding: 11px 40px 11px 40px;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    color: #f1f5f9;
    font-size: 13.5px;
    font-family: inherit;
    outline: none;
    box-sizing: border-box;
    transition: all 0.2s;
}
.search-wrap input:focus { border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15); }
.search-clear {
    position: absolute; right: 8px; top: 50%;
    transform: translateY(-50%);
    width: 26px; height: 26px;
    background: rgba(255, 255, 255, 0.06);
    color: #94a3b8;
    border: none; border-radius: 50%;
    cursor: pointer; padding: 0; font-size: 12px;
    display: flex; align-items: center; justify-content: center;
}

.toolbar-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.btn-primary {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 11px 20px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff; border: none; border-radius: 10px;
    font-size: 13px; font-weight: 700;
    cursor: pointer; font-family: inherit;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
    transition: all 0.2s;
}
.btn-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.5); }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-secondary {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 11px 18px;
    background: rgba(255, 255, 255, 0.05);
    color: #cbd5e1;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    font-size: 13px; font-weight: 700;
    cursor: pointer; font-family: inherit;
    transition: all 0.2s;
}
.btn-secondary:hover { background: rgba(255, 255, 255, 0.1); color: #f1f5f9; }

/* Category chips */
.chip-row {
    display: flex; gap: 6px;
    flex-wrap: wrap;
    padding-top: 14px;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    overflow-x: auto;
    scrollbar-width: none;
}
.chip-row::-webkit-scrollbar { display: none; }
.chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 999px;
    color: #94a3b8;
    font-size: 11.5px; font-weight: 700;
    cursor: pointer; font-family: inherit;
    white-space: nowrap;
    transition: all 0.15s;
}
.chip:hover { background: rgba(255, 255, 255, 0.06); color: #cbd5e1; }
.chip.active {
    background: rgba(56, 189, 248, 0.15);
    border-color: rgba(56, 189, 248, 0.4);
    color: #38bdf8;
}
.chip-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 20px; height: 18px; padding: 0 6px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 999px;
    font-size: 10px; font-weight: 800;
}
.chip.active .chip-count { background: rgba(56, 189, 248, 0.25); }

/* ═══════════════════════════════════════════════════════════
   TABLE
   ═══════════════════════════════════════════════════════════ */
.table-card { padding: 0 !important; overflow: hidden !important; }
.table-wrapper { overflow-x: auto; max-height: 70vh; }
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
    table-layout: fixed;
}
.data-table thead {
    position: sticky; top: 0; z-index: 5;
}
.data-table thead th {
    background: #1e293b;
    color: #94a3b8;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    font-size: 10.5px;
    padding: 14px 18px;
    text-align: left;
    border-bottom: 2px solid rgba(56, 189, 248, 0.2);
    white-space: nowrap;
}
.data-table tbody td {
    padding: 12px 18px;
    color: #f1f5f9;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    vertical-align: middle;
}
.data-table tbody tr:hover td { background: rgba(56, 189, 248, 0.04); }
.data-table tbody tr:last-child td { border-bottom: none; }

.key-cell { display: flex; align-items: center; gap: 8px; }
.key-cell code {
    flex: 1; min-width: 0;
    background: rgba(56, 189, 248, 0.10);
    color: #38bdf8;
    padding: 4px 10px;
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 11.5px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.copy-btn {
    width: 26px; height: 26px;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 6px;
    color: #64748b;
    font-size: 12px;
    cursor: pointer; padding: 0;
    opacity: 0;
    transition: all 0.2s;
    flex-shrink: 0;
}
.tr-row:hover .copy-btn { opacity: 1; }
.copy-btn:hover { background: rgba(56, 189, 248, 0.15); color: #38bdf8; }

.cat-badge {
    display: inline-block;
    padding: 4px 10px;
    background: rgba(167, 139, 250, 0.12);
    color: #a78bfa;
    border: 1px solid rgba(167, 139, 250, 0.3);
    border-radius: 6px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.input-wrap { position: relative; }
.trans-input {
    width: 100%;
    padding: 10px 50px 10px 14px;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 9px;
    color: #f1f5f9;
    font-size: 13.5px;
    font-family: inherit;
    outline: none;
    box-sizing: border-box;
    transition: all 0.2s;
}
.trans-input::placeholder { color: #64748b; font-style: italic; }
.trans-input:focus {
    border-color: #38bdf8;
    background: rgba(0, 0, 0, 0.4);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
}
.trans-input.is-empty {
    border-color: rgba(251, 191, 36, 0.4);
    background: rgba(251, 191, 36, 0.04);
}
.trans-input.is-empty:focus {
    border-color: #fbbf24;
    box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.15);
}
.char-count {
    position: absolute;
    right: 12px; top: 50%;
    transform: translateY(-50%);
    font-size: 10.5px;
    color: #64748b;
    font-family: 'Courier New', monospace;
    pointer-events: none;
    font-weight: 700;
}

.save-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px; height: 30px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.2);
    transition: all 0.2s;
}
.save-indicator.pulse { animation: savePulse 0.7s ease-out; }
@keyframes savePulse {
    0%   { transform: scale(1);   box-shadow: 0 0 0 0   rgba(16, 185, 129, 0.6); }
    100% { transform: scale(1.1); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
}

.dot {
    display: inline-block;
    width: 9px; height: 9px;
    border-radius: 50%;
}
.dot.ok      { background: #10b981; box-shadow: 0 0 8px rgba(16, 185, 129, 0.6); }
.dot.warn    { background: #fbbf24; box-shadow: 0 0 8px rgba(251, 191, 36, 0.5); }
.dot.error   { background: #ef4444; box-shadow: 0 0 8px rgba(239, 68, 68, 0.6); }
.dot.saving  {
    background: #38bdf8;
    animation: spin 1s linear infinite;
    box-shadow: 0 0 8px rgba(56, 189, 248, 0.6);
}
@keyframes spin { to { transform: rotate(360deg); border-radius: 4px; } }

/* Footer */
.table-footer {
    display: flex; justify-content: space-between;
    align-items: center;
    padding: 14px 22px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(0, 0, 0, 0.15);
    font-size: 12.5px;
    color: #94a3b8;
    flex-wrap: wrap;
    gap: 10px;
}
.footer-hint { color: #64748b; font-style: italic; font-size: 12px; }

/* No results state */
.no-results {
    padding: 60px 24px !important;
    text-align: center !important;
}
.no-results .empty-emoji { font-size: 52px !important; opacity: 0.5 !important; margin-bottom: 12px !important; }
.no-results h3 { color: #f1f5f9 !important; font-size: 16px !important; margin: 0 0 6px !important; }
.no-results p { color: #94a3b8 !important; font-size: 13px !important; margin: 0 !important; }

/* ═══════════════════════════════════════════════════════════
   EMPTY STATE
   ═══════════════════════════════════════════════════════════ */
.empty-state { text-align: center; padding: 60px 30px; }
.empty-emoji { font-size: 56px; margin-bottom: 14px; opacity: 0.6; }
.empty-state h3 { font-size: 18px; color: #f1f5f9; margin: 0 0 6px; font-weight: 800; }
.empty-state p { color: #94a3b8; font-size: 13.5px; margin: 0 0 20px; }

/* ═══════════════════════════════════════════════════════════
   LEGEND
   ═══════════════════════════════════════════════════════════ */
.legend-card {
    display: flex !important;
    gap: 22px !important;
    align-items: center !important;
    padding: 14px 22px !important;
    flex-wrap: wrap !important;
}
.legend-item {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 12.5px; color: #94a3b8; font-weight: 600;
}

/* ═══════════════════════════════════════════════════════════
   MODAL
   ═══════════════════════════════════════════════════════════ */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
    z-index: 9999; padding: 20px;
    box-sizing: border-box;
}
.modal-overlay:not([hidden]) { display: flex; }
.modal-overlay[hidden] { display: none !important; }

.modal {
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 26px;
    max-width: 520px; width: 100%;
    max-height: 90vh; overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6);
    box-sizing: border-box;
    animation: modalIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

.modal-header {
    display: flex; justify-content: space-between;
    align-items: center;
    margin-bottom: 20px; padding-bottom: 14px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.modal-header h3 { font-size: 17px; color: #f1f5f9; margin: 0; font-weight: 800; }
.modal-close {
    width: 32px; height: 32px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: #cbd5e1;
    font-size: 14px; cursor: pointer;
    font-family: inherit; padding: 0;
    display: inline-flex; align-items: center; justify-content: center;
    transition: all 0.2s;
}
.modal-close:hover { background: rgba(239, 68, 68, 0.2); color: #f87171; }

.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.form-group label {
    font-size: 11px; font-weight: 800;
    color: #94a3b8; text-transform: uppercase;
    letter-spacing: 0.6px;
}
.form-group input,
.form-group textarea {
    padding: 12px 15px;
    background: rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    color: #f1f5f9;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    box-sizing: border-box;
    width: 100%;
    resize: vertical;
    transition: all 0.2s;
}
.form-group input:focus,
.form-group textarea:focus {
    border-color: #38bdf8;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
}
.form-hint { font-size: 11px; color: #64748b; font-style: italic; }

.modal-actions {
    display: flex; justify-content: flex-end;
    gap: 10px; margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

/* ═══════════════════════════════════════════════════════════
   TOAST
   ═══════════════════════════════════════════════════════════ */
.toast {
    position: fixed;
    bottom: 28px; right: 28px;
    padding: 14px 22px;
    border-radius: 12px;
    font-size: 13.5px; font-weight: 600;
    color: #fff;
    transform: translateY(20px);
    opacity: 0;
    transition: all 0.3s;
    z-index: 99999;
    pointer-events: none;
    max-width: 400px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5);
}
.toast.show { transform: translateY(0); opacity: 1; }
.toast.success { background: linear-gradient(135deg, #10b981, #059669); }
.toast.error   { background: linear-gradient(135deg, #ef4444, #dc2626); }
.toast.info    { background: linear-gradient(135deg, #38bdf8, #0284c7); }

/* ═══════════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════════ */
@media (max-width: 1000px) {
    /* hide category column */
    .data-table thead th:nth-child(2),
    .data-table tbody td:nth-child(2) { display: none; }
}
@media (max-width: 900px) {
    .admin-tr-page .main-content {
        width: 100% !important;
        max-width: 100% !important;
        padding: 20px !important;
        padding-top: 72px !important;
    }
    .hero-card { flex-direction: column !important; align-items: flex-start !important; }
    .hero-stats { width: 100% !important; justify-content: space-between !important; }
}
@media (max-width: 720px) {
    .admin-tr-page .main-content { padding: 14px !important; padding-top: 68px !important; }
    .toolbar-row { flex-direction: column; align-items: stretch; }
    .search-wrap { min-width: 0; }
    .toolbar-actions { width: 100%; }
    .toolbar-actions .btn-primary,
    .toolbar-actions .btn-secondary { flex: 1; justify-content: center; }
    .toast { left: 16px; right: 16px; bottom: 16px; }
    .data-table { font-size: 12.5px; }
    .data-table thead th, .data-table tbody td { padding: 10px 12px; }
}
</style>