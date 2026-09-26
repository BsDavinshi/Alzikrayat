<?php
?>
<section class="auth-shell container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-7 col-xl-6">
            <div class="card auth-card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="display-font h3 mb-1">Create your account</h1>
                    <p class="text-body-secondary small mb-4">Already a member? <a href="<?= e(url('/login')) ?>">Login here</a>.</p>
                    <?= View::partial('auth/_register_form', ['idPrefix' => 'register']) ?>
                </div>
            </div>
        </div>
    </div>
</section>
