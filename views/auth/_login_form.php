<?php
$idPrefix = $idPrefix ?? 'login';
?>
<?php if (!empty($lastLogin)): ?>
    <div class="last-login-note d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-clock-history"></i>
        <span>Last login from this computer was <strong><?= e($lastLogin) ?></strong></span>
    </div>
<?php endif; ?>

<form method="post" action="<?= e(url('/login')) ?>" class="needs-validation" novalidate data-validate>
    <?= csrfField() ?>

    <div class="mb-3">
        <label for="<?= e($idPrefix) ?>-email" class="form-label">Email address</label>
        <input type="email" class="form-control<?= invalidClass('email') ?>" id="<?= e($idPrefix) ?>-email" name="email"
               value="<?= old('email') ?>" required maxlength="100" autocomplete="email"
               pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}" data-rule="email"
               placeholder="you@example.com">
        <div class="invalid-feedback">Please enter a valid email address.</div>
        <?= fieldError('email') ?>
    </div>

    <div class="mb-3">
        <label for="<?= e($idPrefix) ?>-password" class="form-label">Password</label>
        <div class="input-group has-validation">
            <input type="password" class="form-control<?= invalidClass('password') ?>" id="<?= e($idPrefix) ?>-password" name="password"
                   required maxlength="72" autocomplete="current-password" placeholder="Your password">
            <button type="button" class="btn btn-outline-secondary" data-toggle-password="#<?= e($idPrefix) ?>-password" aria-label="Show password">
                <i class="bi bi-eye"></i>
            </button>
            <div class="invalid-feedback">Please enter your password.</div>
        </div>
        <?= fieldError('password') ?>
    </div>

    <button type="submit" class="btn btn-accent w-100 py-2"><i class="bi bi-box-arrow-in-right"></i> Login</button>
</form>
