<?php
$currentUserId = Auth::id();
$authorName = $photo['first_name'] . ' ' . $photo['last_name'];
$imageUrl = uploadUrl($photo['file_name']);
?>
<section class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="<?= e(url('/photos')) ?>">Gallery</a></li>
            <?php if (!empty($photo['album_id'])): ?>
                <li class="breadcrumb-item"><a href="<?= e(url('/album/' . $photo['album_id'])) ?>"><?= e($photo['album_title']) ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?= e($photo['title']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <figure class="photo-stage mb-0">
                <button type="button" class="photo-stage-button" data-bs-toggle="modal" data-bs-target="#lightbox" aria-label="Open full size">
                    <img src="<?= e($imageUrl) ?>" alt="<?= e($photo['title']) ?>" id="mainPhoto">
                </button>
                <?php if ($neighbours['previous'] !== null): ?>
                    <a class="stage-nav stage-nav-prev" href="<?= e(url('/photo/' . $neighbours['previous'])) ?>" id="previousPhotoLink" aria-label="Newer photo"><i class="bi bi-chevron-left"></i></a>
                <?php endif; ?>
                <?php if ($neighbours['next'] !== null): ?>
                    <a class="stage-nav stage-nav-next" href="<?= e(url('/photo/' . $neighbours['next'])) ?>" id="nextPhotoLink" aria-label="Older photo"><i class="bi bi-chevron-right"></i></a>
                <?php endif; ?>
                <figcaption class="photo-stage-hint small"><i class="bi bi-arrows-fullscreen"></i> Click to view full size · use ← → to browse</figcaption>
            </figure>
        </div>

        <div class="col-lg-4">
            <aside class="photo-panel">
                <?php if ($photo['filter_name'] !== 'none'): ?>
                    <span class="filter-badge position-static mb-2"><i class="bi bi-magic"></i> <?= e(filterLabel($photo['filter_name'])) ?> filter</span>
                <?php endif; ?>
                <h1 class="display-font h2 mb-3"><?= e($photo['title']) ?></h1>

                <a href="<?= e(url('/user/' . $photo['user_id'])) ?>" class="member-chip mb-3">
                    <span class="avatar" style="background: <?= e(avatarColor((int) $photo['user_id'])) ?>"><?= e(initials($photo['first_name'], $photo['last_name'])) ?></span>
                    <span>
                        <strong><?= e($authorName) ?></strong>
                        <small class="d-block text-body-secondary"><?= e($photo['occupation'] ?: 'Member') ?></small>
                    </span>
                </a>

                <dl class="photo-meta small">
                    <dt><i class="bi bi-calendar3"></i> Uploaded</dt>
                    <dd><time datetime="<?= e(date(DATE_ATOM, strtotime((string) $photo['date_time']))) ?>"><?= e(formatDate($photo['date_time'])) ?></time></dd>
                    <?php if (!empty($photo['album_id'])): ?>
                        <dt><i class="bi bi-journal-album"></i> Album</dt>
                        <dd><a href="<?= e(url('/album/' . $photo['album_id'])) ?>"><?= e($photo['album_title']) ?></a></dd>
                    <?php endif; ?>
                </dl>

                <?php if (!empty($photo['description'])): ?>
                    <p class="photo-description"><?= nl2br(e($photo['description'])) ?></p>
                <?php endif; ?>

                <?php if ($taggedUsers !== []): ?>
                    <div class="mb-3">
                        <div class="small fw-semibold mb-2"><i class="bi bi-person-bounding-box"></i> In this memory</div>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($taggedUsers as $taggedUser): ?>
                                <span class="tag-chip">
                                    <a href="<?= e(url('/user/' . $taggedUser['id'])) ?>"><?= e($taggedUser['first_name'] . ' ' . $taggedUser['last_name']) ?></a>
                                    <?php if ($currentUserId !== null && ($currentUserId === (int) $taggedUser['id'] || $isOwner)): ?>
                                        <form method="post" action="<?= e(url('/photo/' . $photo['id'] . '/untag/' . $taggedUser['id'])) ?>" class="d-inline m-0">
                                            <?= csrfField() ?>
                                            <button type="submit" class="tag-chip-remove" aria-label="Remove tag"><i class="bi bi-x"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2 photo-actions">
                    <?php if ($currentUserId !== null): ?>
                        <button type="button" class="btn btn-like <?= $photo['liked_by_viewer'] ? 'is-liked' : '' ?>"
                                data-like-url="<?= e(url('/photo/' . $photo['id'] . '/like')) ?>" aria-pressed="<?= $photo['liked_by_viewer'] ? 'true' : 'false' ?>">
                            <i class="bi <?= $photo['liked_by_viewer'] ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                            <span data-like-count><?= e($photo['like_count']) ?></span>
                        </button>
                    <?php else: ?>
                        <a class="btn btn-like" href="<?= e(url('/login')) ?>" title="Login to like"><i class="bi bi-heart"></i> <?= e($photo['like_count']) ?></a>
                    <?php endif; ?>

                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-share"></i> Share
                        </button>
                        <ul class="dropdown-menu share-menu" data-share-title="<?= e($photo['title']) ?>">
                            <li><button class="dropdown-item" type="button" data-share="native"><i class="bi bi-phone"></i> Share…</button></li>
                            <li><a class="dropdown-item" href="#" data-share="twitter" target="_blank" rel="noopener"><i class="bi bi-twitter-x"></i> Post on X</a></li>
                            <li><a class="dropdown-item" href="#" data-share="facebook" target="_blank" rel="noopener"><i class="bi bi-facebook"></i> Facebook</a></li>
                            <li><a class="dropdown-item" href="#" data-share="whatsapp" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> WhatsApp</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><button class="dropdown-item" type="button" data-share="copy"><i class="bi bi-link-45deg"></i> Copy link</button></li>
                        </ul>
                    </div>

                    <a class="btn btn-outline-secondary" href="<?= e($imageUrl) ?>" download title="Download original"><i class="bi bi-download"></i></a>

                    <?php if ($isOwner): ?>
                        <form method="post" action="<?= e(url('/photo/' . $photo['id'] . '/delete')) ?>" class="m-0"
                              data-confirm="Delete “<?= e($photo['title']) ?>” and all of its comments? This cannot be undone.">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash3"></i> Delete</button>
                        </form>
                    <?php endif; ?>
                </div>

                <hr class="my-4">

                <section id="comments" aria-labelledby="commentsHeading">
                    <h2 class="h5 mb-3" id="commentsHeading"><i class="bi bi-chat-quote"></i> Comments <span class="badge rounded-pill text-bg-secondary" data-comment-count><?= count($comments) ?></span></h2>

                    <ul class="comment-list list-unstyled" id="commentList">
                        <?php foreach ($comments as $comment): ?>
                            <?= View::partial('photos/_comment', ['comment' => $comment, 'photoOwnerId' => (int) $photo['user_id']]) ?>
                        <?php endforeach; ?>
                    </ul>
                    <p class="text-body-secondary small <?= $comments !== [] ? 'd-none' : '' ?>" id="noCommentsMessage">No comments yet. Start the conversation!</p>

                    <?php if ($currentUserId !== null): ?>
                        <form method="post" action="<?= e(url('/photo/' . $photo['id'] . '/comments')) ?>" id="commentForm" class="comment-form needs-validation" novalidate data-validate>
                            <?= csrfField() ?>
                            <label for="commentText" class="visually-hidden">Your comment</label>
                            <textarea class="form-control<?= invalidClass('comment') ?>" id="commentText" name="comment" rows="2" required maxlength="<?= Comment::MAX_LENGTH ?>"
                                      placeholder="Write a comment…" data-counter="#commentCounter"><?= old('comment') ?></textarea>
                            <div class="invalid-feedback">Please write something (max <?= Comment::MAX_LENGTH ?> characters).</div>
                            <?= fieldError('comment') ?>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-body-secondary"><span id="commentCounter">0</span>/<?= Comment::MAX_LENGTH ?> · Ctrl+Enter to send</small>
                                <button type="submit" class="btn btn-accent btn-sm"><i class="bi bi-send"></i> Post</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-light border small mb-0">
                            <a href="<?= e(url('/login')) ?>">Login</a> or <a href="<?= e(url('/register')) ?>">register</a> to join the conversation.
                        </div>
                    <?php endif; ?>
                </section>
            </aside>
        </div>
    </div>
</section>

<div class="modal fade lightbox" id="lightbox" tabindex="-1" aria-label="Full size photo" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <button type="button" class="btn-close btn-close-white lightbox-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-body d-flex align-items-center justify-content-center p-0">
                <img src="<?= e($imageUrl) ?>" alt="<?= e($photo['title']) ?>" class="lightbox-image" id="lightboxImage">
            </div>
            <div class="lightbox-caption"><?= e($photo['title']) ?> — <?= e($authorName) ?> · <span class="text-white-50">scroll or double-click to zoom</span></div>
        </div>
    </div>
</div>
