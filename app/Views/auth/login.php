<div class="auth-wrapper">
    <div class="glass-card auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <span class="logo-icon">🌱</span>
            <div>
                <h1><?= __('app_name') ?: 'SAIP' ?></h1>
                <small><?= __('app_tagline') ?: 'Smart Agriculture Intelligence Platform' ?></small>
            </div>
        </div>

        <h2>Welcome Back</h2>
        <p class="auth-subtitle">Sign in to access your dashboard</p>

        <!-- ============ ALERTS ============ -->
        <?php if (!empty($data['error'])): ?>
            <div class="alert error">
                <span>⚠️</span>
                <div><?= e($data['error']) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['login_error'])): ?>
            <div class="alert error">
                <span>⚠️</span>
                <div><?= e($_SESSION['login_error']) ?></div>
            </div>
            <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert success">
                <span>✅</span>
                <div>Registration successful! Please sign in.</div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['reset'])): ?>
            <div class="alert success">
                <span>✅</span>
                <div>Password reset successful! Please sign in.</div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['forgot'])): ?>
            <div class="alert success">
                <span>📧</span>
                <div>If that email exists, a reset link has been sent.</div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['timeout'])): ?>
            <div class="alert error">
                <span>⏱️</span>
                <div>Session expired. Please sign in again.</div>
            </div>
        <?php endif; ?>

        <!-- ============ LOGIN FORM ============ -->
        <!-- ✅ FIXED: Segment-based action URL (no index.php?url=) -->
        <form action="<?= BASE_URL ?>/auth/login" method="POST" class="auth-form" id="loginForm" autocomplete="on">
            <?= \App\Core\Security::csrfField() ?>

            <!-- Email -->
            <div class="form-group">
                <label for="email">
                    <span class="label-icon">📧</span>
                    Email Address
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="you@example.com"
                    value="<?= e($_POST['email'] ?? '') ?>"
                >
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">
                    <span class="label-icon">🔒</span>
                    Password
                </label>
                <div class="password-input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    >
                    <button type="button" class="toggle-password" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                        👁️
                    </button>
                </div>
            </div>

            <!-- Remember Me + Forgot Password -->
            <div class="form-row-between">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" value="1" <?= !empty($_POST['remember']) ? 'checked' : '' ?>>
                    <span>Remember me</span>
                </label>
                <a href="<?= BASE_URL ?>/auth/forgot" class="link-subtle">
                    Forgot password?
                </a>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-primary auth-submit" id="submitBtn">
                <span>→</span>
                <span class="btn-text">Sign In</span>
            </button>
        </form>

        <!-- Divider -->
        <div class="auth-divider">
            <span>or</span>
        </div>

        <!-- Language Switcher -->
        <div class="auth-lang-preview">
            <span class="lang-label">🌐 Language:</span>
            <div class="lang-chips">
                <?php 
                $langs = class_exists('App\Core\Language') ? \App\Core\Language::supported() : ['en' => ['name' => 'English', 'flag' => '🇬🇧']];
                $current = class_exists('App\Core\Language') ? \App\Core\Language::current() : 'en';
                foreach ($langs as $code => $info): 
                ?>
                    <a href="<?= BASE_URL ?>/language/switch/<?= $code ?>"
                       class="lang-chip <?= $current === $code ? 'active' : '' ?>"
                       title="<?= e($info['name'] ?? $code) ?>">
                        <?= $info['flag'] ?? '🌐' ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Register Link -->
        <div class="auth-link">
            Don't have an account?
            <a href="<?= BASE_URL ?>/auth/register">Create one →</a>
        </div>
    </div>
</div>

<script>
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
    }
}

// Prevent double submit + show loading state
document.getElementById('loginForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    if (btn && !btn.disabled) {
        btn.disabled = true;
        const txt = btn.querySelector('.btn-text');
        if (txt) txt.textContent = 'Signing in...';
        const arrow = btn.querySelector('span:first-child');
        if (arrow) arrow.textContent = '⏳';
    }
});

// Log form target on submit for debugging
document.getElementById('loginForm')?.addEventListener('submit', function(e) {
    console.log('Submitting form to:', this.action);
    console.log('Email:', document.getElementById('email').value);
});
</script>

<style>
/* =========================================
   AUTH PAGE — MODERN DARK THEME
   ========================================= */

:root {
    --bg-deep: #081C15;
    --bg-mid: #0b1120;
    --accent-green: #10b981;
    --accent-lime: #84cc16;
    --text-main: #f1f5f9;
    --text-sub: #94a3b8;
    --border: rgba(255, 255, 255, 0.08);
}

html, body {
    margin: 0;
    padding: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--bg-deep);
    color: var(--text-main);
}

.auth-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 20px;
    background: linear-gradient(135deg, #081C15 0%, #0F3D2E 50%, #0b1120 100%);
    position: relative;
    overflow: hidden;
}

.auth-wrapper::before {
    content: '';
    position: absolute;
    top: -30%;
    right: -15%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(34, 197, 94, 0.15), transparent 70%);
    border-radius: 50%;
    pointer-events: none;
    animation: float1 15s ease-in-out infinite;
}

.auth-wrapper::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(132, 204, 22, 0.1), transparent 70%);
    border-radius: 50%;
    pointer-events: none;
    animation: float2 18s ease-in-out infinite;
}

@keyframes float1 {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(-30px, 30px); }
}
@keyframes float2 {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(40px, -20px); }
}

/* CARD */
.auth-card {
    max-width: 440px;
    width: 100%;
    padding: 40px 36px;
    position: relative;
    z-index: 1;
    background: rgba(30, 41, 59, 0.75) !important;
    backdrop-filter: blur(20px) !important;
    -webkit-backdrop-filter: blur(20px) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 24px !important;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.5) !important;
    animation: cardIn 0.5s ease-out;
    box-sizing: border-box;
}

@keyframes cardIn {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* LOGO */
.auth-logo {
    display: flex;
    align-items: center;
    gap: 14px;
    justify-content: center;
    margin-bottom: 24px;
    padding-bottom: 22px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.auth-logo .logo-icon {
    font-size: 40px;
    line-height: 1;
    filter: drop-shadow(0 0 16px rgba(34, 197, 94, 0.6));
}

.auth-logo h1 {
    font-size: 24px;
    font-weight: 800;
    color: #10b981;
    margin: 0;
    letter-spacing: 1.5px;
    line-height: 1;
}

.auth-logo small {
    display: block;
    font-size: 10px;
    color: var(--text-sub);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-top: 4px;
}

/* HEADINGS */
.auth-card h2 {
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 8px;
    text-align: center;
    color: var(--text-main);
}

.auth-subtitle {
    text-align: center;
    color: var(--text-sub);
    font-size: 13.5px;
    margin: 0 0 26px;
}

/* ALERTS */
.alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 13px 16px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-size: 13px;
    line-height: 1.5;
    animation: alertIn 0.3s ease-out;
    box-sizing: border-box;
}

@keyframes alertIn {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}

.alert span:first-child { font-size: 16px; flex-shrink: 0; }
.alert > div { flex: 1; }

.alert.error {
    background: rgba(239, 68, 68, 0.12);
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, 0.35);
}

.alert.success {
    background: rgba(34, 197, 94, 0.12);
    color: #86efac;
    border: 1px solid rgba(34, 197, 94, 0.35);
}

/* FORM */
.auth-form { margin: 0; }

.form-group { margin-bottom: 16px; }

.form-group label {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
    color: var(--text-sub);
    font-size: 13px;
    font-weight: 500;
}

.label-icon { font-size: 12px; }

.form-group input {
    width: 100%;
    padding: 13px 16px;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #f1f5f9;
    font-family: inherit;
    font-size: 14px;
    outline: none;
    transition: all 250ms ease;
    box-sizing: border-box;
}

.form-group input::placeholder {
    color: rgba(148, 163, 184, 0.5);
}

.form-group input:focus {
    border-color: #10b981;
    background: rgba(0, 0, 0, 0.4);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}

/* PASSWORD TOGGLE */
.password-input-wrap {
    position: relative;
}

.password-input-wrap input {
    padding-right: 48px;
}

.toggle-password {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    font-size: 16px;
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    opacity: 0.6;
    transition: 200ms;
    color: inherit;
}

.toggle-password:hover {
    opacity: 1;
    background: rgba(255, 255, 255, 0.05);
}

/* REMEMBER + FORGOT ROW */
.form-row-between {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
    flex-wrap: wrap;
    gap: 10px;
}

.checkbox-label {
    display: flex !important;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 13px;
    color: var(--text-sub);
    margin: 0 !important;
}

.checkbox-label input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: #10b981;
    cursor: pointer;
    margin: 0;
}

.link-subtle {
    color: #10b981;
    font-size: 13px;
    text-decoration: none;
    transition: 200ms;
    font-weight: 500;
}

.link-subtle:hover {
    color: #34d399;
    text-decoration: underline;
}

/* SUBMIT BUTTON */
.btn-primary.auth-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px 24px;
    width: 100%;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 12px !important;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all 250ms ease;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
    font-family: inherit;
    position: relative;
    overflow: hidden;
}

.btn-primary.auth-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.55);
}

.btn-primary.auth-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.auth-submit span:first-child {
    display: inline-block;
    transition: transform 300ms;
}

.auth-submit:hover:not(:disabled) span:first-child {
    transform: translateX(4px);
}

/* DIVIDER */
.auth-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 24px 0 20px;
    color: var(--text-sub);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255, 255, 255, 0.08);
}

/* LANGUAGE SWITCHER */
.auth-lang-preview {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    margin-bottom: 20px;
    gap: 12px;
}

.lang-label {
    font-size: 12px;
    color: var(--text-sub);
    white-space: nowrap;
}

.lang-chips {
    display: flex;
    gap: 6px;
}

.lang-chip {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.08);
    font-size: 15px;
    text-decoration: none;
    transition: 200ms;
    cursor: pointer;
}

.lang-chip:hover {
    background: rgba(34, 197, 94, 0.15);
    border-color: #10b981;
    transform: translateY(-2px);
}

.lang-chip.active {
    background: rgba(34, 197, 94, 0.25);
    border-color: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
}

/* FOOTER LINK */
.auth-link {
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
    color: var(--text-sub);
}

.auth-link a {
    color: #10b981;
    text-decoration: none;
    font-weight: 600;
    margin-left: 4px;
    transition: 200ms;
}

.auth-link a:hover {
    color: #34d399;
}

/* MOBILE */
@media (max-width: 480px) {
    .auth-card {
        padding: 30px 22px;
    }
    .auth-logo h1 {
        font-size: 20px;
    }
    .auth-card h2 {
        font-size: 20px;
    }
    .form-row-between {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>