<div class="auth-wrapper">
    <div class="glass-card">
        <h2>🔐 Two-Factor Authentication</h2>
        <p style="text-align:center; color:var(--text-sub); margin-bottom:20px;">
            Enter the 6-digit code from your authenticator app.
        </p>

        <?php if (isset($data['error'])): ?>
            <div class="alert error"><?= e($data['error']) ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/auth/verify2fa" method="POST">
            <?= \App\Core\Security::csrfField() ?>
            <div class="form-group">
                <label>6-Digit Code</label>
                <input type="text" name="code" required pattern="[0-9]{6}" maxlength="6"
                       inputmode="numeric" autocomplete="one-time-code"
                       style="font-size:22px; letter-spacing:8px; text-align:center;">
            </div>
            <button class="btn-primary">Verify</button>
        </form>
    </div>
</div>