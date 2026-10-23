<div class="auth-wrapper">
    <div class="glass-card auth-card">
        <div class="auth-logo">
            <span class="logo-icon">🌱</span>
            <div>
                <h1><?= __('app_name') ?: 'SAIP' ?></h1>
                <small><?= __('app_tagline') ?: 'Smart Agriculture Intelligence Platform' ?></small>
            </div>
        </div>

        <h2>Forgot Password</h2>
        <p class="auth-subtitle">Enter your email and we'll send a reset link.</p>

        <?php if (!empty($data['error'])): ?>
            <div class="alert error">
                <span>⚠️</span>
                <div><?= e($data['error']) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/auth/forgot" class="auth-form">
            <?= \App\Core\Security::csrfField() ?>

            <div class="form-group">
                <label for="email"><span class="label-icon">📧</span> Email Address</label>
                <input type="email" id="email" name="email" required autofocus
                       placeholder="you@example.com"
                       value="<?= e($_POST['email'] ?? '') ?>">
            </div>

            <button type="submit" class="btn-primary auth-submit">
                <span>📧</span>
                <span class="btn-text">Send Reset Link</span>
            </button>
        </form>

        <div class="auth-link">
            Remembered it?
            <a href="<?= BASE_URL ?>/auth/login">← Login</a>
        </div>
    </div>
</div>