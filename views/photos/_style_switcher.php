<?php
$keepQuery = $keepQuery ?? [];
$currentPath = (new Request())->getPath();
?>
<div class="btn-group style-switcher" role="group" aria-label="Display style">
    <?php foreach (Config::get('gallery.styles') as $styleKey => $styleInfo): ?>
        <?php $styleLink = url($currentPath) . '?' . http_build_query($keepQuery + ['style' => $styleKey]); ?>
        <a href="<?= e($styleLink) ?>" class="btn btn-sm <?= $galleryStyle === $styleKey ? 'btn-accent' : 'btn-outline-secondary' ?>"
           title="<?= e($styleInfo['label']) ?>" aria-label="<?= e($styleInfo['label']) ?>" <?= $galleryStyle === $styleKey ? 'aria-current="true"' : '' ?>>
            <i class="bi <?= e($styleInfo['icon']) ?>"></i><span class="d-none d-xl-inline ms-1"><?= e($styleInfo['label']) ?></span>
        </a>
    <?php endforeach; ?>
</div>
