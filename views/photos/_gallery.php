<?php
$gridColumns = [
    'grid3' => 'row-cols-1 row-cols-sm-2 row-cols-lg-3',
    'grid4' => 'row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4',
];
?>
<?php if (isset($gridColumns[$galleryStyle])): ?>
    <div class="row <?= e($gridColumns[$galleryStyle]) ?> g-4 gallery gallery-<?= e($galleryStyle) ?>">
        <?php foreach ($photos as $photo): ?>
            <div class="col"><?= View::partial('photos/_card', ['photo' => $photo]) ?></div>
        <?php endforeach; ?>
    </div>

<?php elseif ($galleryStyle === 'list'): ?>
    <div class="d-flex flex-column gap-3 gallery gallery-list">
        <?php foreach ($photos as $photo): ?>
            <?= View::partial('photos/_card', ['photo' => $photo, 'cardStyle' => 'list']) ?>
        <?php endforeach; ?>
    </div>

<?php elseif ($galleryStyle === 'masonry'): ?>
    <div class="gallery gallery-masonry">
        <?php foreach ($photos as $photo): ?>
            <div class="masonry-item"><?= View::partial('photos/_card', ['photo' => $photo, 'cardStyle' => 'natural']) ?></div>
        <?php endforeach; ?>
    </div>

<?php else: ?>
    <div id="gallerySlider" class="carousel slide carousel-fade gallery-slider" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
            <?php foreach ($photos as $slideIndex => $photo): ?>
                <div class="carousel-item <?= $slideIndex === 0 ? 'active' : '' ?>">
                    <a href="<?= e(url('/photo/' . $photo['id'])) ?>">
                        <img src="<?= e(uploadUrl($photo['file_name'])) ?>" class="d-block w-100" alt="<?= e($photo['title']) ?>" <?= $slideIndex > 0 ? 'loading="lazy"' : '' ?>>
                    </a>
                    <div class="carousel-caption">
                        <h3 class="display-font h4 mb-1"><?= e($photo['title']) ?></h3>
                        <p class="small mb-0">by <?= e($photo['first_name'] . ' ' . $photo['last_name']) ?> · <?= e(timeAgo($photo['date_time'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#gallerySlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#gallerySlider" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span>
        </button>
    </div>
    <div class="slider-thumbnails d-flex gap-2 mt-3 overflow-auto pb-2">
        <?php foreach ($photos as $slideIndex => $photo): ?>
            <button type="button" data-bs-target="#gallerySlider" data-bs-slide-to="<?= $slideIndex ?>"
                    class="slider-thumb <?= $slideIndex === 0 ? 'active' : '' ?>" aria-label="Show <?= e($photo['title']) ?>">
                <img src="<?= e(uploadUrl($photo['file_name'])) ?>" alt="" loading="lazy">
            </button>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
