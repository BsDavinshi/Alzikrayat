<?php
?>
<section class="container py-5 text-center error-page">
    <div class="error-code"><?= e($errorCode) ?></div>
    <h1 class="display-font h2 mb-3"><?= e($pageTitle ?? 'Error') ?></h1>
    <p class="lead text-body-secondary mx-auto" style="max-width: 36rem;"><?= e($errorMessage) ?></p>
    <div class="d-flex gap-2 justify-content-center mt-4">
        <a href="<?= e(url('/')) ?>" class="btn btn-accent"><i class="bi bi-house"></i> Back home</a>
        <a href="<?= e(url('/photos')) ?>" class="btn btn-outline-secondary"><i class="bi bi-images"></i> Browse gallery</a>
    </div>
    <?php if (!empty($debugDetails)): ?>
        <pre class="text-start small mt-5 p-3 bg-body-tertiary rounded overflow-auto"><?= e($debugDetails) ?></pre>
    <?php endif; ?>
</section>
