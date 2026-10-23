<style>
/* ═══════════════════════════════════════════════════════════
   LEGAL PAGES — Shared Styles
   ═══════════════════════════════════════════════════════════ */
html, body { margin: 0 !important; padding: 0 !important; overflow-x: hidden !important; }

.dashboard-layout { display: block !important; width: 100% !important; background: #0b1120 !important; min-height: 100vh !important; }
.main-wrap { margin-left: 260px !important; width: calc(100% - 260px) !important; min-height: 100vh !important; box-sizing: border-box !important; }
.main-wrap > .main-content {
    margin: 0 !important; width: 100% !important; max-width: 100% !important;
    padding: 24px 32px 60px !important; box-sizing: border-box !important;
    display: flex !important; flex-direction: column !important; gap: 20px !important;
}
.glass-card {
    background: rgba(30,41,59,.7) !important;
    backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(255,255,255,.08) !important;
    border-radius: 20px !important;
    padding: 26px !important;
    box-sizing: border-box !important;
    width: 100% !important;
}

/* ═══ HERO ═══ */
.legal-hero {
    background: linear-gradient(135deg, rgba(16,185,129,.15), rgba(56,189,248,.08)) !important;
    position: relative; overflow: hidden;
}
.legal-hero::before {
    content: '';
    position: absolute; top: -40%; right: -10%;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(16,185,129,.18), transparent 70%);
    pointer-events: none;
}

.legal-brand {
    display: flex; align-items: center; gap: 14px;
    margin-bottom: 24px; position: relative; z-index: 1;
    padding-bottom: 18px; border-bottom: 1px solid rgba(255,255,255,.08);
}
.legal-logo {
    font-size: 46px; line-height: 1;
    filter: drop-shadow(0 0 16px rgba(16,185,129,.5));
}
.legal-brand h1 {
    font-size: 26px; font-weight: 800; color: #10b981;
    letter-spacing: 3px; margin: 0 0 4px; line-height: 1;
}
.legal-brand small {
    font-size: 10px; color: #64748b; text-transform: uppercase;
    letter-spacing: 1.4px; font-weight: 700;
}

.legal-title-block { position: relative; z-index: 1; }
.legal-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 14px; margin-bottom: 12px;
    background: rgba(16,185,129,.15); color: #34d399;
    border: 1px solid rgba(16,185,129,.35); border-radius: 999px;
    font-size: 10.5px; font-weight: 800; letter-spacing: 1.2px; text-transform: uppercase;
}
.legal-title-block h2 {
    font-size: 30px; font-weight: 800; color: #f1f5f9;
    margin: 0 0 10px; line-height: 1.15;
}
.legal-title-block p {
    color: #94a3b8; font-size: 14px; line-height: 1.6;
    margin: 0 0 14px; max-width: 720px;
}
.legal-updated {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px;
    background: rgba(0,0,0,.3); border-radius: 8px;
    font-size: 12px; color: #94a3b8;
}
.legal-updated strong { color: #34d399; }

/* ═══ LAYOUT: TOC + Content ═══ */
.legal-layout {
    display: grid;
    grid-template-columns: 280px minmax(0, 1fr);
    gap: 20px;
    align-items: start;
}

/* ═══ TOC ═══ */
.legal-toc {
    position: sticky; top: 20px;
    padding: 22px !important;
}
.legal-toc h3 {
    font-size: 13px; font-weight: 800; color: #f1f5f9;
    margin: 0 0 14px; padding-bottom: 12px;
    border-bottom: 1px solid rgba(255,255,255,.06);
}
.legal-toc nav { display: flex; flex-direction: column; gap: 2px; }
.toc-item {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px; border-radius: 9px;
    text-decoration: none; color: #94a3b8;
    font-size: 12.5px; font-weight: 600;
    transition: all .15s;
}
.toc-item:hover {
    background: rgba(16,185,129,.08);
    color: #34d399;
    transform: translateX(3px);
}
.toc-num {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 22px; height: 20px; padding: 0 6px;
    background: rgba(16,185,129,.12); color: #34d399;
    border-radius: 5px; font-size: 10px; font-weight: 800;
    letter-spacing: .3px; flex-shrink: 0;
}
.toc-text { line-height: 1.35; }

/* ═══ CONTENT ═══ */
.legal-content { padding: 32px 36px !important; }
.legal-section {
    padding-bottom: 28px; margin-bottom: 28px;
    border-bottom: 1px solid rgba(255,255,255,.06);
    scroll-margin-top: 20px;
}
.legal-section:last-of-type { border-bottom: none; }

.section-head {
    display: flex; align-items: flex-start; gap: 14px;
    margin-bottom: 18px;
}
.sec-icon {
    font-size: 26px; line-height: 1;
    width: 52px; height: 52px;
    background: linear-gradient(135deg, rgba(16,185,129,.12), rgba(56,189,248,.06));
    border: 1px solid rgba(16,185,129,.25);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sec-num {
    display: inline-block;
    font-size: 10.5px; font-weight: 800;
    color: #10b981; letter-spacing: 1.4px;
    text-transform: uppercase; margin-bottom: 3px;
}
.section-head h3 {
    font-size: 19px; font-weight: 800;
    color: #f1f5f9; margin: 0; line-height: 1.25;
}

.legal-section p {
    font-size: 13.5px; color: #cbd5e1;
    line-height: 1.75; margin: 0 0 14px;
}
.legal-section p:last-child { margin-bottom: 0; }
.legal-section strong { color: #34d399; font-weight: 700; }

.legal-list {
    list-style: none; padding: 0; margin: 0 0 14px;
    display: flex; flex-direction: column; gap: 9px;
}
.legal-list li {
    padding: 10px 16px 10px 42px; position: relative;
    background: rgba(0,0,0,.2);
    border-left: 3px solid #10b981;
    border-radius: 10px;
    font-size: 13px; color: #cbd5e1; line-height: 1.55;
}
.legal-list li::before {
    content: '✓';
    position: absolute; left: 16px; top: 10px;
    color: #34d399; font-weight: 800; font-size: 13px;
}
.legal-list.danger li { border-left-color: #ef4444; }
.legal-list.danger li::before { content: '✕'; color: #f87171; }

/* ═══ GRIDS ═══ */
.service-grid, .collect-grid, .security-grid,
.rights-grid, .feature-grid, .browser-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px; margin-bottom: 16px;
}
.service-item, .collect-card, .security-item,
.right-card, .feature-card, .browser-item {
    display: flex; flex-direction: column; gap: 6px;
    padding: 16px;
    background: rgba(0,0,0,.22);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 12px;
    transition: all .2s;
}
.service-item:hover, .collect-card:hover, .security-item:hover,
.right-card:hover, .feature-card:hover, .browser-item:hover {
    border-color: rgba(16,185,129,.35);
    background: rgba(16,185,129,.05);
    transform: translateY(-2px);
}
.service-item span, .feature-card span, .browser-item span { font-size: 24px; line-height: 1; }
.collect-card .cc-icon { font-size: 26px; line-height: 1; margin-bottom: 4px; }
.service-item strong, .collect-card strong, .right-card strong,
.feature-card strong, .browser-item strong {
    font-size: 13px; color: #f1f5f9; font-weight: 800;
}
.service-item small, .collect-card ul, .right-card small,
.feature-card small, .browser-item small {
    font-size: 11.5px; color: #94a3b8; line-height: 1.45;
}
.collect-card ul { padding-left: 16px; margin: 4px 0 0; display: flex; flex-direction: column; gap: 3px; }

/* security-item — horizontal layout */
.security-item, .right-card { flex-direction: row; align-items: center; gap: 12px; }
.security-item > div, .right-card > span + * { flex: 1; }
.security-item span, .right-card span { font-size: 22px; }

/* ═══ CALL OUTS ═══ */
.callout {
    display: flex; gap: 14px; align-items: flex-start;
    padding: 16px 18px;
    border-radius: 12px;
    margin: 16px 0;
    border-left: 4px solid;
}
.callout .co-icon { font-size: 22px; line-height: 1; flex-shrink: 0; }
.callout strong { display: block; font-size: 13px; margin-bottom: 4px; }
.callout p { margin: 0 !important; font-size: 12.5px !important; color: #cbd5e1; line-height: 1.55; }
.callout.info    { background: rgba(56,189,248,.08);  border-left-color: #38bdf8; }
.callout.info strong    { color: #38bdf8; }
.callout.warn    { background: rgba(251,191,36,.08);  border-left-color: #fbbf24; }
.callout.warn strong    { color: #fbbf24; }
.callout.success { background: rgba(16,185,129,.08);  border-left-color: #10b981; }
.callout.success strong { color: #34d399; }

/* ═══ COOKIE TABLE ═══ */
.cookie-table {
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 16px;
}
.ct-head, .ct-row {
    display: grid;
    grid-template-columns: 200px 1fr 130px;
    gap: 16px;
    padding: 12px 18px;
    align-items: center;
}
.ct-head {
    background: rgba(0,0,0,.3);
    font-size: 11px; font-weight: 800;
    color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.ct-row {
    font-size: 12.5px; color: #cbd5e1;
    border-bottom: 1px solid rgba(255,255,255,.04);
}
.ct-row:last-child { border-bottom: none; }
.ct-name { font-family: 'Courier New', monospace; color: #34d399; font-weight: 700; font-size: 12px; }
.ct-badge {
    display: inline-flex; padding: 4px 10px;
    border-radius: 999px; font-size: 10px;
    font-weight: 800; text-transform: uppercase; letter-spacing: .5px;
    justify-self: start;
}
.ct-badge.essential { background: rgba(239,68,68,.15); color: #f87171; border: 1px solid rgba(239,68,68,.3); }
.ct-badge.pref      { background: rgba(56,189,248,.15); color: #38bdf8; border: 1px solid rgba(56,189,248,.3); }
.ct-badge.analytics { background: rgba(167,139,250,.15); color: #a78bfa; border: 1px solid rgba(167,139,250,.3); }

/* ═══ KEYBOARD ═══ */
.kbd-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px; margin-bottom: 16px; }
.kbd-item {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 14px;
    background: rgba(0,0,0,.22);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 10px;
    font-size: 12.5px; color: #cbd5e1; flex-wrap: wrap;
}
kbd {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 4px 10px; min-width: 28px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.15);
    border-bottom-width: 2px;
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 11.5px; font-weight: 800;
    color: #f1f5f9;
    box-shadow: 0 1px 2px rgba(0,0,0,.3);
}

/* ═══ CONTACT GRID ═══ */
.contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 12px;
    margin-top: 14px;
}
.contact-item {
    display: flex; gap: 12px; align-items: flex-start;
    padding: 14px 16px;
    background: rgba(0,0,0,.22);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 12px;
}
.ci-icon {
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    background: rgba(16,185,129,.1);
    border: 1px solid rgba(16,185,129,.25);
    border-radius: 10px;
    font-size: 16px; flex-shrink: 0;
}
.contact-item > div { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.contact-item small {
    font-size: 10px; color: #64748b;
    text-transform: uppercase; letter-spacing: .8px; font-weight: 700;
}
.contact-item a, .ci-plain {
    font-size: 12.5px; color: #cbd5e1;
    text-decoration: none; font-weight: 600;
    word-break: break-word; line-height: 1.4;
}
.contact-item a:hover { color: #34d399; }
.ci-plain { color: #94a3b8; font-weight: 500; }

/* ═══ FOOTER ═══ */
.legal-footer {
    margin-top: 32px; padding-top: 24px;
    border-top: 1px solid rgba(255,255,255,.08);
    display: flex; justify-content: space-between;
    align-items: center; gap: 20px; flex-wrap: wrap;
}
.legal-footer p {
    font-size: 11.5px; color: #64748b; margin: 0;
}
.legal-links { display: flex; gap: 10px; flex-wrap: wrap; }
.legal-links a {
    font-size: 11.5px; color: #94a3b8;
    text-decoration: none; font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    transition: all .15s;
}
.legal-links a:hover { color: #34d399; background: rgba(16,185,129,.08); }
.legal-links a.active { color: #34d399; background: rgba(16,185,129,.12); }

/* ═══ RESPONSIVE ═══ */
@media (max-width: 1000px) {
    .legal-layout { grid-template-columns: 1fr; }
    .legal-toc { position: static; }
}
@media (max-width: 768px) {
    .main-wrap { margin-left: 0 !important; width: 100% !important; padding-top: 64px !important; }
    .main-wrap > .main-content { padding: 14px !important; }
    .glass-card { padding: 20px !important; border-radius: 16px !important; }
    .legal-content { padding: 22px !important; }
    .legal-title-block h2 { font-size: 22px; }
    .legal-brand h1 { font-size: 22px; }
    .legal-logo { font-size: 38px; }
    .section-head { gap: 12px; }
    .sec-icon { width: 44px; height: 44px; font-size: 22px; }
    .section-head h3 { font-size: 16px; }
    .ct-head, .ct-row { grid-template-columns: 1fr; gap: 6px; }
    .legal-footer { flex-direction: column; text-align: center; }
}
</style>