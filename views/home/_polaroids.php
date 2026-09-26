<?php
$isStrip = $isStrip ?? false;
?>
<?php if ($heroPhotos === []): ?>
    <div class="polaroid-stack">
        <?php foreach (['bi-sunrise', 'bi-camera2', 'bi-heart'] as $position => $iconName): ?>
            <div class="polaroid polaroid-<?= $position + 1 ?> polaroid-placeholder">
                <div class="polaroid-image d-flex align-items-center justify-content-center"><i class="bi <?= e($iconName) ?>"></i></div>
                <div class="polaroid-caption">Your memory here</div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="<?= $isStrip ? 'polaroid-strip' : 'polaroid-stack' ?>">
        <?php foreach (array_slice($heroPhotos, 0, $isStrip ? 5 : 3) as $position => $photo): ?>
            <a class="polaroid polaroid-<?= $position + 1 ?>" href="<?= e(url('/photo/' . $photo['id'])) ?>">
                <img class="polaroid-image" src="<?= e(uploadUrl($photo['file_name'])) ?>" alt="<?= e($photo['title']) ?>" loading="lazy">
                <span class="polaroid-caption"><?= e($photo['title']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
