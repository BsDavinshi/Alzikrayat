<?php
?>
<a href="<?= e(url('/album/' . $album['id'])) ?>" class="album-card h-100">
    <div class="album-cover">
        <?php if (!empty($album['cover_file'])): ?>
            <img src="<?= e(uploadUrl($album['cover_file'])) ?>" alt="" loading="lazy">
        <?php else: ?>
            <div class="album-cover-empty"><i class="bi bi-journal-album"></i></div>
        <?php endif; ?>
        <span class="album-count"><i class="bi bi-images"></i> <?= e($album['photo_count']) ?></span>
    </div>
    <div class="album-card-body">
        <h3 class="h6 mb-1"><?= e($album['title']) ?></h3>
        <small class="text-body-secondary">by <?= e($album['first_name'] . ' ' . $album['last_name']) ?></small>
    </div>
</a>
