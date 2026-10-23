<div class="auth-wrapper">
    <div class="glass-card">
        <h2><?= __('auth.reset_title') ?? 'Set New Password' ?></h2>

        <?php if (isset($data['error'])): ?>
            <div class="alert error"><?= e($data['error']) ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/auth/reset" method="POST">
            <?= \App\Core\Security::csrfField() ?>
            <input type="hidden" name="email" value="<?= e($data['email'] ?? '') ?>">
            <input type="hidden" name="token" value="<?= e($data['token'] ?? '') ?>">

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" required minlength="8" placeholder="••••••••">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm" required minlength="8" placeholder="••••••••">
            </div>
            <button class="btn-primary">🔒 Reset Password</button>
        </form>
    </div>
</div>