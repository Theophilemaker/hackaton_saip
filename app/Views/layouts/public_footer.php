<?php
/**
 * SAIP Global Footer — v4
 * Contact Us column removed.
 */
$isLoggedIn  = !empty($_SESSION['user_id']);
$currentYear = date('Y');
?>
<footer class="saip-footer">
    <div class="footer-wrap">

        <!-- ═══ COL 1 — BRAND + FOLLOW + SUBSCRIBE ═══ -->
        <div class="footer-col footer-brand-col">
            <div class="footer-brand">
                <span class="footer-logo">🌱</span>
                <div>
                    <h3 class="footer-brand-name">SAIP</h3>
                    <small class="footer-brand-tag">Smart Agriculture Intelligence Platform</small>
                </div>
            </div>

            <p class="footer-desc">
                Empowering Rwanda's farmers with AI-driven agricultural intelligence,
                market transparency, and data-backed decisions.
            </p>
            <small>
             <i class="fas fa-envelope"></i> theophilen298@gmail.com<br>
    
                    <i class="fas fa-map-marker-alt"></i> P.O. Box 4285 Kigali, Rwanda
                    </small>
                    
            <h4 class="footer-mini-heading">Follow Us</h4>
            <div class="footer-socials">
                <a href="https://youtube.com/@saip" target="_blank" rel="noopener" class="social-btn social-youtube" title="YouTube">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 0 0 .5 6.2C0 8.1 0 12 0 12s0 3.9.5 5.8a3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1c.5-1.9.5-5.8.5-5.8s0-3.9-.5-5.8zM9.6 15.6V8.4l6.2 3.6-6.2 3.6z"/></svg>
                </a>
                <a href="https://x.com/saip" target="_blank" rel="noopener" class="social-btn social-x" title="X">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M18.9 2H22l-6.8 7.8L23.3 22h-6.3l-4.9-6.4L6.5 22H3.4l7.3-8.3L1 2h6.4l4.4 5.8L18.9 2zm-1.1 18h1.7L7.3 3.9H5.5L17.8 20z"/></svg>
                </a>
                <a href="https://facebook.com/saip" target="_blank" rel="noopener" class="social-btn social-facebook" title="Facebook">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z"/></svg>
                </a>
                <a href="https://linkedin.com/company/saip" target="_blank" rel="noopener" class="social-btn social-linkedin" title="LinkedIn">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.03-1.85-3.03-1.85 0-2.13 1.44-2.13 2.94v5.66H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.38-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13zM7.12 20.45H3.56V9h3.56v11.45z"/></svg>
                </a>
                <a href="https://instagram.com/saip" target="_blank" rel="noopener" class="social-btn social-instagram" title="Instagram">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.72 3.72 0 0 1-1.38-.9 3.72 3.72 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16zm0-2.16C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63c-.79.31-1.46.72-2.12 1.38A5.86 5.86 0 0 0 .63 4.14C.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.31.79.72 1.46 1.38 2.12a5.86 5.86 0 0 0 2.13 1.39c.76.3 1.64.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56a5.86 5.86 0 0 0 2.12-1.39 5.86 5.86 0 0 0 1.39-2.12c.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91a5.86 5.86 0 0 0-1.39-2.12A5.86 5.86 0 0 0 19.86.63c-.76-.3-1.64-.5-2.91-.56C15.67.01 15.26 0 12 0z"/></svg>
                </a>
                <a href="https://github.com/saip" target="_blank" rel="noopener" class="social-btn social-github" title="GitHub">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 .3a12 12 0 0 0-3.79 23.39c.6.11.82-.26.82-.58v-2.02c-3.34.73-4.04-1.61-4.04-1.61-.55-1.39-1.34-1.76-1.34-1.76-1.09-.75.08-.73.08-.73 1.21.08 1.84 1.24 1.84 1.24 1.07 1.83 2.81 1.3 3.5.99.11-.78.42-1.3.76-1.6-2.66-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.13-.3-.54-1.52.12-3.18 0 0 1.01-.32 3.3 1.23a11.5 11.5 0 0 1 6.01 0c2.29-1.55 3.3-1.23 3.3-1.23.66 1.66.24 2.88.12 3.18.77.84 1.24 1.91 1.24 3.22 0 4.61-2.81 5.63-5.48 5.92.43.37.81 1.1.81 2.22v3.29c0 .32.22.7.83.58A12 12 0 0 0 12 .3z"/></svg>
                </a>
                <a href="https://wa.me/250793473101" target="_blank" rel="noopener" class="social-btn social-whatsapp" title="WhatsApp">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M20.5 3.5A11.8 11.8 0 0 0 12 0C5.5 0 .2 5.3.2 11.8c0 2.1.5 4.1 1.6 5.9L0 24l6.5-1.7a11.8 11.8 0 0 0 5.6 1.4c6.5 0 11.8-5.3 11.8-11.8 0-3.2-1.2-6.2-3.4-8.4zM12 21.7c-1.7 0-3.4-.5-4.9-1.3l-.35-.2-3.65.96.97-3.55-.23-.37a9.8 9.8 0 0 1-1.5-5.2c0-5.4 4.4-9.8 9.8-9.8 2.6 0 5.1 1 6.9 2.9a9.7 9.7 0 0 1 2.9 6.9c0 5.4-4.4 9.8-9.8 9.8z"/></svg>
                </a>
            </div>

            <h4 class="footer-mini-heading" style="margin-top:22px;">Subscribe</h4>
            <p class="subscribe-desc">Get farming tips and market alerts in your inbox.</p>
            <form class="subscribe-form" onsubmit="event.preventDefault(); handleSubscribe(this);">
                <input type="email" name="email" placeholder="your@email.com" required>
                <button type="submit" aria-label="Subscribe">→</button>
            </form>
            <small class="subscribe-msg" id="subscribeMsg"></small>
        </div>

        <!-- ═══ COL 2 — QUICK LINKS ═══ -->
        <div class="footer-col">
            <h4 class="footer-heading">Quick Links</h4>
            <ul class="footer-links">
                <li><a href="<?= BASE_URL ?>/crops/agribot">🤖 AgriBot Assistant</a></li>
                <li><a href="<?= BASE_URL ?>/crops/detect">🔬 Disease Detection</a></li>
                <li><a href="<?= BASE_URL ?>/marketplace">🛒 Marketplace</a></li>
                <li><a href="<?= BASE_URL ?>/weather">🌤️ Weather</a></li>
                <li><a href="<?= BASE_URL ?>/feed">📰 Community Feed</a></li>
                <li><a href="<?= BASE_URL ?>/chat">💬 Farmer Connect</a></li>
            </ul>
        </div>

        <!-- ═══ COL 3 — RWANDA PARTNERS ═══ -->
        <div class="footer-col">
            <h4 class="footer-heading">Rwanda Partners</h4>
            <ul class="footer-links">
                <li><a href="https://www.statistics.gov.rw" target="_blank" rel="noopener">NISR</a></li>
                <li><a href="https://www.minecofin.gov.rw" target="_blank" rel="noopener">MINECOFIN</a></li>
                <li><a href="https://www.minagri.gov.rw" target="_blank" rel="noopener">MINAGRI</a></li>
                <li><a href="https://www.rab.gov.rw" target="_blank" rel="noopener">RAB</a></li>
                <li><a href="https://www.naeb.gov.rw" target="_blank" rel="noopener">NAEB</a></li>
                <li><a href="https://www.rcs.gov.rw" target="_blank" rel="noopener">RCS</a></li>
                <li><a href="https://www.rdb.rw" target="_blank" rel="noopener">RDB</a></li>
                <li><a href="https://www.meteorwanda.gov.rw" target="_blank" rel="noopener">Meteo Rwanda</a></li>
            </ul>
        </div>

        <!-- ═══ COL 4 — DEVELOPERS ═══ -->
        <div class="footer-col">
            <h4 class="footer-heading">Developers</h4>
            <ul class="footer-links">
                <li><a href="<?= BASE_URL ?>/developer/docs" class="dev-link">API Documentation</a></li>
                <li><a href="<?= BASE_URL ?>/developer/keys" class="dev-link">API Keys</a></li>
                <li><a href="<?= BASE_URL ?>/developer/webhooks" class="dev-link">Webhooks</a></li>
                <li><a href="<?= BASE_URL ?>/developer/sdk">SDKs &amp; Libraries</a></li>
                <li><a href="<?= BASE_URL ?>/developer/status">System Status</a></li>
                <li><a href="mailto:theophilen298@gmail.com">Contact Dev Team</a></li>
            </ul>
        </div>

    </div>

    <!-- ═══ BOTTOM BAR ═══ -->
    <div class="footer-bottom">
        <div class="footer-bottom-wrap">
            <div class="footer-copyright">
                © <?= $currentYear ?> <strong>SAIP</strong> · AI-powered agriculture intelligence for Rwanda's farmers, market and data-backed decisions.
                <br>
                <small>Built for Rwanda's farmers &amp; other agri participants.</small>
            </div>

            <div class="footer-legal-links">
                <a href="<?= BASE_URL ?>/terms" title="Terms & Conditions">
                    <span class="flink-icon">📜</span>
                    <span>Terms</span>
                </a>
                <span class="divider">·</span>
                <a href="<?= BASE_URL ?>/privacy" title="Security & Privacy">
                    <span class="flink-icon">🔒</span>
                    <span>Privacy</span>
                </a>
                <span class="divider">·</span>
                <a href="<?= BASE_URL ?>/cookies" title="Cookie Policy">
                    <span class="flink-icon">🍪</span>
                    <span>Cookies</span>
                </a>
                <span class="divider">·</span>
                <a href="<?= BASE_URL ?>/accessibility" title="Accessibility">
                    <span class="flink-icon">♿</span>
                    <span>Accessibility</span>
                </a>
            </div>
        </div>
    </div>
</footer>

<!-- ═══ LOGIN MODAL (silent gate) ═══ -->
<div class="saip-login-modal" id="saipLoginModal" style="display:none">
    <div class="saip-login-card">
        <div class="saip-login-icon">🔐</div>
        <h3>Login Required</h3>
        <p>This developer resource requires authentication. Please log in to continue.</p>
        <div class="saip-login-actions">
            <button type="button" onclick="closeSaipLoginModal()" class="btn-cancel">Cancel</button>
            <a href="<?= BASE_URL ?>/auth/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-login">→ Login</a>
        </div>
    </div>
</div>

<style>
/* ═══════════════════════════════════════════════════════════
   FOOTER POSITIONING — Respects sidebar offset
   ═══════════════════════════════════════════════════════════ */
@media (min-width: 769px) {
    body.has-sidebar .saip-footer {
        margin-left: 260px !important;
        width: calc(100% - 260px) !important;
        max-width: calc(100% - 260px) !important;
        box-sizing: border-box !important;
    }
    body.has-sidebar .saip-footer .footer-wrap,
    body.has-sidebar .saip-footer .footer-bottom-wrap {
        max-width: 100% !important;
        padding-left: 32px !important;
        padding-right: 32px !important;
    }
}
@media (max-width: 768px) {
    body.has-sidebar .saip-footer {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
}

/* ═══ BASE ═══ */
.saip-footer {
    background: linear-gradient(180deg, #0a1628 0%, #061018 100%);
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    color: #cbd5e1;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    margin-top: 60px;
    position: relative;
    overflow: hidden;
}
.saip-footer::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.4), transparent);
    pointer-events: none;
}

/* ═══ GRID — 4 columns now (was 5) ═══ */
.footer-wrap {
    max-width: 1500px;
    margin: 0 auto;
    padding: 56px 40px 40px;
    display: grid;
    grid-template-columns: 1.6fr 1fr 1fr 1fr;
    gap: 40px;
    align-items: start;
}

.footer-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
.footer-logo { font-size: 40px; line-height: 1; filter: drop-shadow(0 0 12px rgba(16, 185, 129, 0.4)); }
.footer-brand-name { font-size: 22px; font-weight: 800; color: #10b981; letter-spacing: 3px; margin: 0 0 3px; line-height: 1; }
.footer-brand-tag { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 700; white-space: nowrap; }
.footer-desc { font-size: 13px; line-height: 1.65; color: #94a3b8; margin: 0 0 22px; max-width: 400px; }
.footer-mini-heading { font-size: 11px; font-weight: 800; color: #10b981; text-transform: uppercase; letter-spacing: 1.4px; margin: 0 0 12px; }

.footer-socials { display: flex; gap: 8px; flex-wrap: wrap; }
.social-btn {
    width: 38px; height: 38px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px; color: #94a3b8;
    text-decoration: none; transition: all 0.2s ease;
}
.social-btn svg { display: block; }
.social-btn:hover { transform: translateY(-3px); }
.social-youtube:hover   { background: rgba(255, 0, 0, 0.15);    border-color: rgba(255, 0, 0, 0.5);    color: #ff4444; }
.social-x:hover         { background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.4); color: #ffffff; }
.social-facebook:hover  { background: rgba(24, 119, 242, 0.15); border-color: rgba(24, 119, 242, 0.5); color: #1877f2; }
.social-linkedin:hover  { background: rgba(0, 119, 181, 0.15);  border-color: rgba(0, 119, 181, 0.5);  color: #0077b5; }
.social-instagram:hover { background: rgba(228, 64, 95, 0.15);  border-color: rgba(228, 64, 95, 0.5);  color: #e4405f; }
.social-github:hover    { background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.3); color: #ffffff; }
.social-whatsapp:hover  { background: rgba(37, 211, 102, 0.15); border-color: rgba(37, 211, 102, 0.5);  color: #25d366; }

.subscribe-desc { font-size: 12px; color: #94a3b8; line-height: 1.55; margin: 0 0 10px; }
.subscribe-form { display: flex; gap: 6px; max-width: 340px; }
.subscribe-form input {
    flex: 1; min-width: 0; padding: 10px 14px;
    background: rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 9px; color: #f1f5f9;
    font-size: 12.5px; font-family: inherit; outline: none;
    transition: all 0.2s;
}
.subscribe-form input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15); }
.subscribe-form input::placeholder { color: #64748b; }
.subscribe-form button {
    width: 40px; height: 40px; flex-shrink: 0;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff; border: none; border-radius: 9px;
    font-size: 16px; font-weight: 800; cursor: pointer;
    font-family: inherit; transition: all 0.2s;
    display: flex; align-items: center; justify-content: center; padding: 0;
}
.subscribe-form button:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4); }
.subscribe-msg { display: block; margin-top: 8px; font-size: 11.5px; font-weight: 600; color: #34d399; min-height: 16px; }

.footer-heading {
    font-size: 11px; font-weight: 800; color: #10b981;
    text-transform: uppercase; letter-spacing: 1.4px;
    margin: 0 0 20px; padding-bottom: 10px;
    border-bottom: 1px solid rgba(16, 185, 129, 0.15);
    position: relative;
}
.footer-heading::after {
    content: ''; position: absolute;
    left: 0; bottom: -1px;
    width: 32px; height: 2px;
    background: linear-gradient(90deg, #10b981, #38bdf8);
    border-radius: 2px;
}
.footer-links { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 11px; }
.footer-links li a {
    display: inline-flex; align-items: center; gap: 6px;
    color: #94a3b8; text-decoration: none;
    font-size: 13px; font-weight: 500;
    transition: all 0.2s ease; padding: 2px 0;
}
.footer-links li a:hover { color: #34d399; transform: translateX(4px); }

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    background: rgba(0, 0, 0, 0.25);
    padding: 20px 0;
}
.footer-bottom-wrap {
    max-width: 1500px; margin: 0 auto;
    padding: 0 40px;
    display: flex; justify-content: space-between;
    align-items: center; gap: 20px; flex-wrap: wrap;
}
.footer-copyright { font-size: 12.5px; color: #94a3b8; line-height: 1.6; max-width: 720px; }
.footer-copyright strong { color: #10b981; font-weight: 800; }
.footer-copyright small { display: inline-block; margin-top: 4px; font-size: 11px; color: #64748b; }

.footer-legal-links { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.footer-legal-links a {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; color: #94a3b8;
    text-decoration: none; font-weight: 600;
    padding: 6px 10px; border-radius: 8px;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}
.footer-legal-links a:hover {
    color: #34d399;
    background: rgba(16, 185, 129, 0.08);
    border-color: rgba(16, 185, 129, 0.25);
    transform: translateY(-1px);
}
.footer-legal-links .flink-icon { font-size: 13px; line-height: 1; }
.footer-legal-links .divider { color: #334155; font-weight: 700; font-size: 11px; margin: 0 2px; }

/* Login modal */
.saip-login-modal {
    position: fixed; inset: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
    z-index: 99999; padding: 20px; box-sizing: border-box;
}
.saip-login-modal[style*="display:flex"],
.saip-login-modal[style*="display: flex"] { display: flex !important; }
.saip-login-card {
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 32px 28px;
    max-width: 420px; width: 100%;
    text-align: center;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.7);
    box-sizing: border-box;
}
.saip-login-icon { font-size: 52px; margin-bottom: 14px; }
.saip-login-card h3 { font-size: 20px; font-weight: 800; color: #f1f5f9; margin: 0 0 10px; }
.saip-login-card p { color: #94a3b8; font-size: 13.5px; line-height: 1.55; margin: 0 0 22px; }
.saip-login-actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.btn-cancel, .btn-login {
    display: inline-flex; align-items: center; justify-content: center;
    gap: 8px; padding: 12px 24px;
    border-radius: 10px; font-size: 13.5px; font-weight: 700;
    text-decoration: none; cursor: pointer;
    border: none; font-family: inherit;
    transition: all 0.2s; min-width: 120px;
}
.btn-cancel { background: rgba(255, 255, 255, 0.05); color: #cbd5e1; border: 1px solid rgba(255, 255, 255, 0.12); }
.btn-cancel:hover { background: rgba(255, 255, 255, 0.1); }
.btn-login { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4); }
.btn-login:hover { transform: translateY(-2px); box-shadow: 0 6px 22px rgba(16, 185, 129, 0.6); }

/* ═══ RESPONSIVE ═══ */
@media (max-width: 1100px) {
    .footer-wrap { grid-template-columns: 1.5fr 1fr 1fr; gap: 28px; }
    .footer-brand-col { grid-column: 1 / -1; }
}
@media (max-width: 900px) {
    .footer-wrap { grid-template-columns: 1fr 1fr; padding: 40px 24px 30px; gap: 28px; }
    .footer-brand-col { grid-column: 1 / -1; }
    .footer-bottom-wrap { padding: 0 24px; flex-direction: column; text-align: center; gap: 12px; }
    .footer-legal-links { justify-content: center; }
}
@media (max-width: 620px) {
    .footer-wrap { grid-template-columns: 1fr; padding: 32px 20px 24px; }
    .footer-legal-links .divider { display: none; }
    .footer-legal-links { gap: 4px; }
    .footer-legal-links a { padding: 5px 8px; font-size: 11.5px; }
}
</style>

<script>
(function () {
    'use strict';

    var IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;

    /* Silent gate for developer links */
    document.querySelectorAll('.dev-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (!IS_LOGGED_IN) {
                e.preventDefault();
                var m = document.getElementById('saipLoginModal');
                if (m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
            }
        });
    });

    window.closeSaipLoginModal = function () {
        var m = document.getElementById('saipLoginModal');
        if (m) { m.style.display = 'none'; document.body.style.overflow = ''; }
    };

    document.getElementById('saipLoginModal')?.addEventListener('click', function (e) {
        if (e.target === this) closeSaipLoginModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSaipLoginModal();
    });

    /* Subscribe handler */
    window.handleSubscribe = function (form) {
        var email = form.querySelector('input[name="email"]').value.trim();
        var msg = document.getElementById('subscribeMsg');
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            msg.textContent = '⚠️ Please enter a valid email.';
            msg.style.color = '#f87171';
            return;
        }
        msg.textContent = '⏳ Subscribing…';
        msg.style.color = '#94a3b8';
        fetch('<?= BASE_URL ?>/newsletter/subscribe', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'email=' + encodeURIComponent(email)
        })
        .then(function (r) { return r.json().catch(function () { return { success: true }; }); })
        .then(function (d) {
            msg.textContent = (d.success !== false) ? '✅ Subscribed!' : '⚠️ ' + (d.error || 'Could not subscribe.');
            msg.style.color = (d.success !== false) ? '#34d399' : '#f87171';
            if (d.success !== false) form.reset();
        })
        .catch(function () {
            msg.textContent = '✅ Subscribed!';
            msg.style.color = '#34d399';
            form.reset();
        });
    };
})();
</script>