<?php
?>
<section class="container py-4 py-md-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <span class="eyebrow">Collections</span>
            <h1 class="display-font h1 mt-1 mb-1">Albums</h1>
            <p class="text-body-secondary mb-0">Memories grouped by trip, season, celebration or anything you like.</p>
        </div>
        <?php if (Auth::check()): ?>
            <a href="<?= e(url('/album/create')) ?>" class="btn btn-accent"><i class="bi bi-folder-plus"></i> New album</a>
        <?php endif; ?>
    </div>

    <?php if ($albums === []): ?>
        <div class="empty-state text-center py-5">
            <i class="bi bi-journal-album"></i>
            <p class="mt-3 text-body-secondary">No albums yet.</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
            <?php foreach ($albums as $album): ?>
                <div class="col"><?= View::partial('albums/_album_card', ['album' => $album]) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
