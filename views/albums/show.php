<?php
?>
<section class="container py-4 py-md-5">
    <div class="page-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <span class="eyebrow">Album</span>
            <h1 class="display-font h1 mt-1 mb-1"><?= e($album['title']) ?></h1>
            <p class="text-body-secondary mb-1">
                by <a href="<?= e(url('/user/' . $album['user_id'])) ?>"><?= e($album['first_name'] . ' ' . $album['last_name']) ?></a>
                · <?= e($pagination['total']) ?> photo<?= $pagination['total'] === 1 ? '' : 's' ?>
            </p>
            <?php if (!empty($album['description'])): ?>
                <p class="mb-0" style="max-width: 44rem;"><?= nl2br(e($album['description'])) ?></p>
            <?php endif; ?>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <?= View::partial('photos/_style_switcher', ['galleryStyle' => $galleryStyle]) ?>
            <?php if ($isOwner): ?>
                <a href="<?= e(url('/photo/create?album=' . $album['id'])) ?>" class="btn btn-sm btn-accent"><i class="bi bi-plus-lg"></i> Add photo</a>
                <form method="post" action="<?= e(url('/album/' . $album['id'] . '/delete')) ?>" class="m-0"
                      data-confirm="Delete the album “<?= e($album['title']) ?>”? The photos themselves will be kept.">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($pagination['items'] === []): ?>
        <?= View::partial('photos/_empty', ['emptyMessage' => 'This album is still empty.']) ?>
    <?php else: ?>
        <?= View::partial('photos/_gallery', ['photos' => $pagination['items'], 'galleryStyle' => $galleryStyle]) ?>
        <?= View::partial('photos/_pagination', ['pagination' => $pagination]) ?>
    <?php endif; ?>
</section>
