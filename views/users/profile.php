<?php
$fullName = $profile['first_name'] . ' ' . $profile['last_name'];
?>
<section class="profile-hero">
    <div class="container py-5">
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
            <span class="avatar avatar-xl" style="background: <?= e(avatarColor((int) $profile['id'])) ?>"><?= e(initials($profile['first_name'], $profile['last_name'])) ?></span>
            <div class="flex-grow-1">
                <h1 class="display-font h2 mb-1"><?= e($fullName) ?></h1>
                <div class="d-flex flex-wrap gap-3 text-body-secondary small">
                    <?php if (!empty($profile['occupation'])): ?><span><i class="bi bi-briefcase"></i> <?= e($profile['occupation']) ?></span><?php endif; ?>
                    <?php if (!empty($profile['location'])): ?><span><i class="bi bi-geo-alt"></i> <?= e($profile['location']) ?></span><?php endif; ?>
                    <span><i class="bi bi-calendar-heart"></i> Joined <?= e(date('F Y', strtotime((string) $profile['created_at']))) ?></span>
                </div>
                <?php if (!empty($profile['description'])): ?>
                    <p class="mt-2 mb-0" style="max-width: 44rem;"><?= nl2br(e($profile['description'])) ?></p>
                <?php endif; ?>
            </div>
            <?php if ($isOwnProfile): ?>
                <div class="d-flex gap-2">
                    <a href="<?= e(url('/profile/edit')) ?>" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Edit profile</a>
                    <a href="<?= e(url('/photo/create')) ?>" class="btn btn-accent"><i class="bi bi-cloud-arrow-up"></i> Upload</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-stats d-flex flex-wrap gap-4 mt-4">
            <div><strong><?= e($profile['photo_count']) ?></strong> photos</div>
            <div><strong><?= e($profile['album_count']) ?></strong> albums</div>
            <div><strong><?= e($profile['tagged_count']) ?></strong> tagged in</div>
            <div><strong><?= e($profile['likes_received']) ?></strong> likes received</div>
        </div>
    </div>
</section>

<section class="container py-4">
    <ul class="nav nav-underline mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profilePhotos" type="button" role="tab">Photos (<?= count($photos) ?>)</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profileTagged" type="button" role="tab">Tagged in (<?= count($taggedPhotos) ?>)</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profileAlbums" type="button" role="tab">Albums (<?= count($albums) ?>)</button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="profilePhotos" role="tabpanel">
            <?php if ($photos === []): ?>
                <?= View::partial('photos/_empty', ['emptyMessage' => $isOwnProfile ? 'You have not uploaded any photos yet.' : 'No photos yet.']) ?>
            <?php else: ?>
                <?= View::partial('photos/_gallery', ['photos' => $photos, 'galleryStyle' => 'grid4']) ?>
            <?php endif; ?>
        </div>
        <div class="tab-pane fade" id="profileTagged" role="tabpanel">
            <?php if ($taggedPhotos === []): ?>
                <p class="text-body-secondary py-4 text-center">Nobody has tagged <?= e($profile['first_name']) ?> in a photo yet.</p>
            <?php else: ?>
                <?= View::partial('photos/_gallery', ['photos' => $taggedPhotos, 'galleryStyle' => 'grid4']) ?>
            <?php endif; ?>
        </div>
        <div class="tab-pane fade" id="profileAlbums" role="tabpanel">
            <?php if ($albums === []): ?>
                <p class="text-body-secondary py-4 text-center">No albums yet.</p>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
                    <?php foreach ($albums as $album): ?>
                        <div class="col"><?= View::partial('albums/_album_card', ['album' => $album]) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
