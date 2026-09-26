<?php
$cardStyle = $cardStyle ?? 'grid';
$photoUrl = url('/photo/' . $photo['id']);
$authorName = $photo['first_name'] . ' ' . $photo['last_name'];
?>
<article class="photo-card photo-card-<?= e($cardStyle) ?> h-100">
    <a href="<?= e($photoUrl) ?>" class="photo-card-media<?= $cardStyle === 'grid' ? ' ratio ratio-4x3' : '' ?>">
        <img src="<?= e(uploadUrl($photo['file_name'])) ?>" alt="<?= e($photo['title']) ?>" loading="lazy">
        <?php if (($photo['filter_name'] ?? 'none') !== 'none'): ?>
            <span class="filter-badge"><i class="bi bi-magic"></i> <?= e(filterLabel($photo['filter_name'])) ?></span>
        <?php endif; ?>
    </a>
    <div class="photo-card-body">
        <h3 class="photo-card-title"><a href="<?= e($photoUrl) ?>"><?= e($photo['title']) ?></a></h3>
        <?php if ($cardStyle === 'list' && !empty($photo['description'])): ?>
            <p class="photo-card-description"><?= e(mb_strimwidth($photo['description'], 0, 180, '…')) ?></p>
        <?php endif; ?>
        <div class="photo-card-meta">
            <a href="<?= e(url('/user/' . $photo['user_id'])) ?>" class="d-inline-flex align-items-center gap-1">
                <span class="avatar avatar-xs" style="background: <?= e(avatarColor((int) $photo['user_id'])) ?>"><?= e(initials($photo['first_name'], $photo['last_name'])) ?></span>
                <?= e($authorName) ?>
            </a>
            <span class="text-body-secondary" title="<?= e(formatDate($photo['date_time'])) ?>"><?= e(timeAgo($photo['date_time'])) ?></span>
        </div>
        <div class="photo-card-counts">
            <span title="Likes"><i class="bi bi-heart"></i> <?= e($photo['like_count']) ?></span>
            <span title="Comments"><i class="bi bi-chat"></i> <?= e($photo['comment_count']) ?></span>
        </div>
    </div>
</article>
