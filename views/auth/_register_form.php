<?php
$idPrefix = $idPrefix ?? 'register';
$namePattern = '[A-Za-z؀-ۿ]+( [A-Za-z؀-ۿ]+)*';
?>
<form method="post" action="<?= e(url('/register')) ?>" class="needs-validation" novalidate data-validate>
    <?= csrfField() ?>

    <div class="row g-3">
        <div class="col-sm-6">
            <label for="<?= e($idPrefix) ?>-first-name" class="form-label">First name</label>
            <input type="text" class="form-control<?= invalidClass('first_name') ?>" id="<?= e($idPrefix) ?>-first-name" name="first_name"
                   value="<?= old('first_name') ?>" required maxlength="50" pattern="<?= e($namePattern) ?>" data-rule="name"
                   autocomplete="given-name">
            <div class="invalid-feedback">Letters only, up to 50 characters.</div>
            <?= fieldError('first_name') ?>
        </div>
        <div class="col-sm-6">
            <label for="<?= e($idPrefix) ?>-last-name" class="form-label">Last name</label>
            <input type="text" class="form-control<?= invalidClass('last_name') ?>" id="<?= e($idPrefix) ?>-last-name" name="last_name"
                   value="<?= old('last_name') ?>" required maxlength="50" pattern="<?= e($namePattern) ?>" data-rule="name"
                   autocomplete="family-name">
            <div class="invalid-feedback">Letters only, up to 50 characters.</div>
            <?= fieldError('last_name') ?>
        </div>

        <div class="col-12">
            <label for="<?= e($idPrefix) ?>-email" class="form-label">Email address</label>
            <input type="email" class="form-control<?= invalidClass('email') ?>" id="<?= e($idPrefix) ?>-email" name="email"
                   value="<?= old('email') ?>" required maxlength="100" autocomplete="email"
                   pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}" data-rule="email">
            <div class="invalid-feedback">Please enter a valid email address.</div>
            <?= fieldError('email') ?>
        </div>

        <div class="col-sm-6">
            <label for="<?= e($idPrefix) ?>-password" class="form-label">Password</label>
            <input type="password" class="form-control<?= invalidClass('password') ?>" id="<?= e($idPrefix) ?>-password" name="password"
                   required minlength="8" maxlength="72" pattern="(?=.*[A-Za-z])(?=.*\d).{8,72}" data-rule="password"
                   data-strength-meter="#<?= e($idPrefix) ?>-strength" autocomplete="new-password">
            <div class="progress password-strength mt-2" role="progressbar" aria-label="Password strength">
                <div class="progress-bar" id="<?= e($idPrefix) ?>-strength" style="width: 0%"></div>
            </div>
            <div class="form-text">8+ characters with letters and numbers.</div>
            <div class="invalid-feedback">At least 8 characters, including a letter and a number.</div>
            <?= fieldError('password') ?>
        </div>
        <div class="col-sm-6">
            <label for="<?= e($idPrefix) ?>-password-confirm" class="form-label">Confirm password</label>
            <input type="password" class="form-control<?= invalidClass('password_confirm') ?>" id="<?= e($idPrefix) ?>-password-confirm"
                   name="password_confirm" required maxlength="72" data-match="#<?= e($idPrefix) ?>-password" autocomplete="new-password">
            <div class="invalid-feedback">Passwords must match.</div>
            <?= fieldError('password_confirm') ?>
        </div>
    </div>

    <button type="submit" class="btn btn-accent w-100 py-2 mt-4"><i class="bi bi-person-plus"></i> Create account</button>
</form>
