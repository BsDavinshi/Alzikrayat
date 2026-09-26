<?php
?>
<div class="empty-state text-center py-5">
    <i class="bi bi-camera"></i>
    <p class="mt-3 mb-3 text-body-secondary"><?= e($emptyMessage) ?></p>
    <?php if (Auth::check()): ?>
        <a href="<?= e(url('/photo/create')) ?>" class="btn btn-accent"><i class="bi bi-cloud-arrow-up"></i> Upload a photo</a>
    <?php else: ?>
        <a href="<?= e(url('/register')) ?>" class="btn btn-accent">Join Alzikrayat</a>
    <?php endif; ?>
</div>
