<?php
$isGuest = !Auth::check();
$statisticItems = [
    ['value' => $statistics['members'],  'label' => 'Members',   'icon' => 'bi-people'],
    ['value' => $statistics['photos'],   'label' => 'Memories',  'icon' => 'bi-camera'],
    ['value' => $statistics['comments'], 'label' => 'Comments',  'icon' => 'bi-chat-quote'],
    ['value' => $statistics['albums'],   'label' => 'Albums',    'icon' => 'bi-journal-album'],
];
?>
<section class="hero">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow">الذكريات · Alzikrayat</span>
                <h1 class="display-font hero-title mt-3">Keep the moments<br>that made you.</h1>
                <p class="lead hero-lead mt-3">
                    Alzikrayat is a warm little corner of the web for sharing photographs and the stories behind them.
                    Upload your memories, give them a vintage glow with our built-in filters, tag the friends who were
                    there, and gather around each photo with comments.
                </p>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a href="<?= e(url('/photos')) ?>" class="btn btn-accent btn-lg"><i class="bi bi-images"></i> Explore the gallery</a>
                    <?php if ($isGuest): ?>
                        <a href="#login-register" class="btn btn-outline-secondary btn-lg d-lg-none">Join now</a>
                    <?php else: ?>
                        <a href="<?= e(url('/photo/create')) ?>" class="btn btn-outline-secondary btn-lg"><i class="bi bi-cloud-arrow-up"></i> Share a memory</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-6">
                <?php if ($isGuest): ?>
                    <?= View::partial('auth/_login_register', ['lastLogin' => $lastLogin]) ?>
                <?php else: ?>
                    <?= View::partial('home/_polaroids', ['heroPhotos' => $heroPhotos]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="container">
    <div class="stats-band row row-cols-2 row-cols-md-4 g-0">
        <?php foreach ($statisticItems as $statistic): ?>
            <div class="col stat-item">
                <i class="bi <?= e($statistic['icon']) ?>"></i>
                <div class="stat-value" data-count-to="<?= e($statistic['value']) ?>"><?= e(number_format($statistic['value'])) ?></div>
                <div class="stat-label"><?= e($statistic['label']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php if ($isGuest && $heroPhotos !== []): ?>
    <section class="container mt-5">
        <?= View::partial('home/_polaroids', ['heroPhotos' => $heroPhotos, 'isStrip' => true]) ?>
    </section>
<?php endif; ?>

<section class="container mt-5 pt-3">
    <div class="d-flex justify-content-between align-items-end mb-3 flex-wrap gap-2">
        <div>
            <span class="eyebrow">Fresh from the community</span>
            <h2 class="display-font h2 mb-0 mt-1">Latest memories</h2>
        </div>
        <a href="<?= e(url('/photos')) ?>" class="btn btn-sm btn-outline-secondary">See all <i class="bi bi-arrow-right"></i></a>
    </div>

    <?php if ($latestPhotos === []): ?>
        <?= View::partial('photos/_empty', ['emptyMessage' => 'No memories have been shared yet. Be the first!']) ?>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
            <?php foreach ($latestPhotos as $photo): ?>
                <div class="col"><?= View::partial('photos/_card', ['photo' => $photo]) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="container mt-5 pt-3">
    <div class="row g-4 features">
        <div class="col-md-6 col-lg-3">
            <div class="feature-card h-100">
                <i class="bi bi-magic"></i>
                <h3 class="h6 mt-3">Creative filters</h3>
                <p class="small text-body-secondary mb-0">Sepia, vintage, noir, emboss and more, computed pixel-by-pixel in your browser before upload.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="feature-card h-100">
                <i class="bi bi-person-bounding-box"></i>
                <h3 class="h6 mt-3">Tag your people</h3>
                <p class="small text-body-secondary mb-0">Tag registered friends in a photo. Their profile shows every memory they appear in.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="feature-card h-100">
                <i class="bi bi-grid-1x2"></i>
                <h3 class="h6 mt-3">Five display styles</h3>
                <p class="small text-body-secondary mb-0">Browse albums as 3 or 4 columns, list cards, masonry or a full-width slider.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="feature-card h-100">
                <i class="bi bi-chat-heart"></i>
                <h3 class="h6 mt-3">Live conversations</h3>
                <p class="small text-body-secondary mb-0">Comments and likes appear instantly without reloading the page.</p>
            </div>
        </div>
    </div>
</section>

<?php if ($topContributors !== []): ?>
    <section class="container mt-5 pt-3">
        <span class="eyebrow">Storytellers</span>
        <h2 class="display-font h2 mt-1 mb-3">Top contributors</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
            <?php foreach ($topContributors as $member): ?>
                <div class="col">
                    <a href="<?= e(url('/user/' . $member['id'])) ?>" class="member-chip">
                        <span class="avatar" style="background: <?= e(avatarColor((int) $member['id'])) ?>"><?= e(initials($member['first_name'], $member['last_name'])) ?></span>
                        <span>
                            <strong><?= e($member['first_name'] . ' ' . $member['last_name']) ?></strong>
                            <small class="d-block text-body-secondary"><?= e($member['occupation'] ?: 'Member') ?> · <?= e($member['photo_count']) ?> photos</small>
                        </span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
