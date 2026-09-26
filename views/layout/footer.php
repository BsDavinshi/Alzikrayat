<?php
?>
<footer class="alz-footer mt-5">
    <div class="container py-4">
        <div class="row gy-3 align-items-center">
            <div class="col-md-5 d-flex align-items-center gap-2">
                <img src="<?= e(asset('images/logo.svg')) ?>" width="28" height="28" alt="">
                <div>
                    <div class="brand-word fs-6">Alzikrayat · الذكريات</div>
                    <small class="text-body-secondary"><?= e(Config::get('app.tagline')) ?></small>
                </div>
            </div>
            <nav class="col-md-4" aria-label="Footer">
                <ul class="list-inline mb-0 small">
                    <li class="list-inline-item"><a href="<?= e(url('/photos')) ?>">Gallery</a></li>
                    <li class="list-inline-item"><a href="<?= e(url('/albums')) ?>">Albums</a></li>
                    <li class="list-inline-item"><a href="<?= e(url('/about')) ?>">About Us</a></li>
                </ul>
            </nav>
            <div class="col-md-3 text-md-end small text-body-secondary">
                &copy; <?= date('Y') ?> Alzikrayat · SUST CS&amp;IT
            </div>
        </div>
    </div>
</footer>
