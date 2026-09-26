<?php
$currentUser = Auth::user();
?>
<nav class="navbar navbar-expand-lg sticky-top alz-navbar" aria-label="Main navigation">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(url('/')) ?>">
            <img src="<?= e(asset('images/logo.svg')) ?>" width="36" height="36" alt="Alzikrayat logo">
            <span class="brand-word">Alzikrayat</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation"
                aria-controls="mainNavigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavigation">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?= activeClass('/') ?>" href="<?= e(url('/')) ?>"><i class="bi bi-house"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link <?= activeClass('/photo', true) ?>" href="<?= e(url('/photos')) ?>"><i class="bi bi-images"></i> Gallery</a></li>
                <li class="nav-item"><a class="nav-link <?= activeClass('/album', true) ?>" href="<?= e(url('/albums')) ?>"><i class="bi bi-journal-album"></i> Albums</a></li>
                <li class="nav-item"><a class="nav-link <?= activeClass('/about') ?>" href="<?= e(url('/about')) ?>"><i class="bi bi-info-circle"></i> About Us</a></li>
            </ul>

            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2 navbar-actions">
                <button type="button" class="btn btn-sm btn-ghost" id="themeToggle" aria-label="Toggle dark mode" title="Toggle dark mode">
                    <i class="bi bi-moon-stars"></i>
                </button>

                <?php if ($currentUser !== null): ?>
                    <a class="btn btn-sm btn-accent" href="<?= e(url('/photo/create')) ?>"><i class="bi bi-cloud-arrow-up"></i> Upload</a>
                    <a class="navbar-greeting d-flex align-items-center gap-2" href="<?= e(url('/user/' . $currentUser['id'])) ?>">
                        <span class="avatar avatar-sm" style="background: <?= e(avatarColor($currentUser['id'])) ?>"><?= e(initials($currentUser['first_name'], $currentUser['last_name'])) ?></span>
                        <span>Hi <?= e($currentUser['first_name']) ?></span>
                    </a>
                    <form method="post" action="<?= e(url('/logout')) ?>" class="m-0">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-sm btn-danger w-100"><i class="bi bi-box-arrow-right"></i> Logout</button>
                    </form>
                <?php else: ?>
                    <span class="navbar-text please-login"><i class="bi bi-person-lock"></i> Please Login</span>
                    <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('/login')) ?>">Login</a>
                    <a class="btn btn-sm btn-accent" href="<?= e(url('/register')) ?>">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
