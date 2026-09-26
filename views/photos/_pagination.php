<?php
$keepQuery = $keepQuery ?? [];
$currentPath = (new Request())->getPath();
if ($pagination['pages'] <= 1) {
    return;
}

$pageLink = static fn(int $pageNumber): string => url($currentPath) . '?' . http_build_query($keepQuery + ['page' => $pageNumber]);
?>
<nav class="mt-5" aria-label="Gallery pages">
    <ul class="pagination justify-content-center flex-wrap">
        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e($pageLink(max(1, $pagination['page'] - 1))) ?>" aria-label="Previous">&laquo;</a>
        </li>
        <?php for ($pageNumber = 1; $pageNumber <= $pagination['pages']; $pageNumber++): ?>
            <li class="page-item <?= $pageNumber === $pagination['page'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= e($pageLink($pageNumber)) ?>"><?= $pageNumber ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $pagination['page'] >= $pagination['pages'] ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e($pageLink(min($pagination['pages'], $pagination['page'] + 1))) ?>" aria-label="Next">&raquo;</a>
        </li>
    </ul>
</nav>
