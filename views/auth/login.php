<?php
?>
<section class="auth-shell container py-5">
    <div class="row justify-content-center align-items-center g-5">
        <div class="col-lg-5 d-none d-lg-block">
            <h1 class="display-font display-5 mb-3">Welcome back.</h1>
            <p class="lead text-body-secondary">Your memories are waiting. Log in to upload new photos, leave comments and tag the people you love.</p>
            <ul class="list-unstyled auth-perks mt-4">
                <li><i class="bi bi-cloud-arrow-up"></i> Upload photos with creative filters</li>
                <li><i class="bi bi-people"></i> Tag friends who appear in your memories</li>
                <li><i class="bi bi-chat-heart"></i> Comment and like instantly</li>
            </ul>
        </div>
        <div class="col-md-8 col-lg-5">
            <div class="card auth-card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h4 mb-1">Login</h2>
                    <p class="text-body-secondary small mb-4">Don't have an account? <a href="<?= e(url('/register')) ?>">Register here</a>.</p>
                    <?= View::partial('auth/_login_form', ['idPrefix' => 'login', 'lastLogin' => $lastLogin]) ?>
                </div>
            </div>
        </div>
    </div>
</section>
