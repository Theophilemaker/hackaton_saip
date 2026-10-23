<div class="auth-wrapper">
    <div class="glass-card auth-card auth-card-wide">
        <div class="auth-logo">
            <span class="logo-icon">🌱</span>
            <div>
                <h1><?= __('app_name') ?></h1>
                <small><?= __('app_tagline') ?></small>
            </div>
        </div>

        <h2><?= __('auth.create') ?></h2>
        <p class="auth-subtitle"><?= __('auth.register_sub') ?? 'Join thousands of Rwandan farmers' ?></p>

        <?php if (!empty($data['error'])): ?>
            <div class="alert error">
                <span>⚠️</span>
                <?= e($data['error']) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($data['success'])): ?>
            <div class="alert success">
                <span>✅</span>
                <?= e($data['success']) ?>
            </div>
        <?php endif; ?>

      <form action="<?= BASE_URL ?>/index.php?url=auth/register" method="POST" class="auth-form" id="registerForm">
            <?= \App\Core\Security::csrfField() ?>

            <div class="form-row-2">
                <div class="form-group">
                    <label for="first_name">👤 <?= __('auth.first_name') ?></label>
                    <input type="text" id="first_name" name="first_name" required maxlength="100"
                           placeholder="Jean" value="<?= e($_POST['first_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="last_name">👤 <?= __('auth.last_name') ?></label>
                    <input type="text" id="last_name" name="last_name" required maxlength="100"
                           placeholder="Mugisha" value="<?= e($_POST['last_name'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="email">📧 <?= __('auth.email') ?></label>
                <input type="email" id="email" name="email" required maxlength="150"
                       placeholder="you@example.rw" value="<?= e($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="phone">📱 <?= __('auth.phone') ?></label>
                <input type="tel" id="phone" name="phone" required maxlength="20"
                       placeholder="0788123456" value="<?= e($_POST['phone'] ?? '') ?>">
                <small class="input-hint">Examples: 0788123456 · +250788123456 · 0788 123 456</small>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label for="role_id">🎯 <?= __('auth.role') ?></label>
                    <select id="role_id" name="role_id" required>
                        <option value="1">🌾 Farmer</option>
                        <option value="2">👥 Cooperative Leader</option>
                        <option value="3">🏛️ Agricultural Officer</option>
                        <option value="5">🚢 Exporter</option>
                        <option value="6">📊 Researcher</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="language">🌐 <?= __('auth.language') ?></label>
                    <select id="language" name="language" required>
                        <?php $cl = $_POST['language'] ?? \App\Core\Language::current(); ?>
                        <option value="rw" <?= $cl === 'rw' ? 'selected' : '' ?>>🇷🇼 Kinyarwanda</option>
                        <option value="en" <?= $cl === 'en' ? 'selected' : '' ?>>🇬🇧 English</option>
                        <option value="fr" <?= $cl === 'fr' ? 'selected' : '' ?>>🇫🇷 Français</option>
                        <option value="sw" <?= $cl === 'sw' ? 'selected' : '' ?>>🇹🇿 Kiswahili</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="password">🔒 <?= __('auth.password') ?></label>
                <div class="password-input-wrap">
                    <input type="password" id="password" name="password" required minlength="8"
                           placeholder="••••••••" autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="togglePassword('password', this)">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">🔒 <?= __('auth.confirm_password') ?></label>
                <div class="password-input-wrap">
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                           placeholder="••••••••" autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required>
                    <span>I agree to the <a href="#" class="link-subtle">Terms</a> and <a href="#" class="link-subtle">Privacy Policy</a></span>
                </label>
            </div>

            <button type="submit" class="btn-primary auth-submit" id="registerBtn">
                <span>→</span>
                <?= __('auth.register') ?>
            </button>
        </form>

        <div class="auth-link">
            <?= __('auth.have_account') ?>
            <a href="<?= BASE_URL ?>/auth/login"><?= __('auth.login') ?> →</a>
        </div>
    </div>
</div>

<style>
.auth-card-wide { max-width: 560px; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 520px) { .form-row-2 { grid-template-columns: 1fr; gap: 0; } }

.form-group select {
    width: 100%; padding: 13px 16px;
    background: rgba(0,0,0,0.25);
    border: 1px solid var(--border); border-radius: 12px;
    color: white; font-family: inherit; font-size: 14px;
    outline: none; cursor: pointer; appearance: none;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23C8D5D1' stroke-width='2'><polyline points='6 9 12 15 18 9'/></svg>");
    background-repeat: no-repeat; background-position: right 14px center;
    padding-right: 40px;
}
.form-group select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(132,204,22,0.15); }
.form-group select option { background: #0F3D2E; color: white; }

.input-hint { display: block; margin-top: 6px; font-size: 11px; color: var(--text-sub); opacity: 0.75; }

.checkbox-label {
    display: flex !important; align-items: flex-start !important;
    gap: 10px; cursor: pointer; font-size: 12px; color: var(--text-sub);
    margin: 0 !important; line-height: 1.5;
}
.checkbox-label input[type="checkbox"] {
    width: 16px; height: 16px; accent-color: var(--primary); cursor: pointer; margin-top: 2px;
}
.checkbox-label a { color: var(--accent); text-decoration: none; font-weight: 500; }
.checkbox-label a:hover { text-decoration: underline; }
</style>

<script>
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') { input.type = 'text'; btn.textContent = '🙈'; }
    else { input.type = 'password'; btn.textContent = '👁️'; }
}
</script>