<?php
$keepQuery = $searchTerm !== '' ? ['q' => $searchTerm] : [];
?>
<section class="container py-4 py-md-5">
    <div class="page-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <span class="eyebrow">Community gallery</span>
            <h1 class="display-font h1 mb-1 mt-1"><?= $searchTerm !== '' ? 'Results for “' . e($searchTerm) . '”' : 'Every memory, in one place' ?></h1>
            <p class="text-body-secondary mb-0"><?= e(number_format($pagination['total'])) ?> photo<?= $pagination['total'] === 1 ? '' : 's' ?></p>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2 align-items-sm-center">
            <form class="search-form" method="get" action="<?= e(url('/photos')) ?>" role="search">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" class="form-control" placeholder="Search titles, stories, people" value="<?= e($searchTerm) ?>" maxlength="100" aria-label="Search photos">
                </div>
            </form>
            <?= View::partial('photos/_style_switcher', ['galleryStyle' => $galleryStyle, 'keepQuery' => $keepQuery]) ?>
        </div>
    </div>

    <?php if ($pagination['items'] === []): ?>
        <?= View::partial('photos/_empty', ['emptyMessage' => $searchTerm !== '' ? 'No memories match your search.' : 'The gallery is empty. Upload the very first memory!']) ?>
    <?php else: ?>
        <?= View::partial('photos/_gallery', ['photos' => $pagination['items'], 'galleryStyle' => $galleryStyle]) ?>
        <?= View::partial('photos/_pagination', ['pagination' => $pagination, 'keepQuery' => $keepQuery]) ?>
    <?php endif; ?>
</section>
