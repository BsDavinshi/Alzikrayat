<?php
$currentUserId = Auth::id();
$canDelete = $currentUserId !== null && ($currentUserId === (int) $comment['user_id'] || $currentUserId === $photoOwnerId);
?>
<li class="comment" id="comment-<?= e($comment['id']) ?>">
    <a href="<?= e(url('/user/' . $comment['user_id'])) ?>" class="avatar avatar-sm flex-shrink-0" style="background: <?= e(avatarColor((int) $comment['user_id'])) ?>">
        <?= e(initials($comment['first_name'], $comment['last_name'])) ?>
    </a>
    <div class="comment-body flex-grow-1">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
                <a href="<?= e(url('/user/' . $comment['user_id'])) ?>" class="fw-semibold"><?= e($comment['first_name'] . ' ' . $comment['last_name']) ?></a>
                <time class="small text-body-secondary ms-1" datetime="<?= e(date(DATE_ATOM, strtotime((string) $comment['date_time']))) ?>" title="<?= e(formatDate($comment['date_time'])) ?>">
                    <?= e(formatDate($comment['date_time'])) ?>
                </time>
            </div>
            <?php if ($canDelete): ?>
                <form method="post" action="<?= e(url('/comment/' . $comment['id'] . '/delete')) ?>" class="m-0 comment-delete-form" data-confirm="Delete this comment?">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-link btn-sm text-danger p-0" aria-label="Delete comment"><i class="bi bi-trash3"></i></button>
                </form>
            <?php endif; ?>
        </div>
        <p class="mb-0 comment-text"><?= nl2br(e($comment['comment'])) ?></p>
    </div>
</li>
