<?php
$flashGroups = Session::consumeFlashes();
$alertStyles = [
    'success' => ['class' => 'success', 'icon' => 'bi-check-circle'],
    'error' => ['class' => 'danger',  'icon' => 'bi-exclamation-triangle'],
    'info' => ['class' => 'info',    'icon' => 'bi-info-circle'],
];
?>
<?php if ($flashGroups !== []): ?>
    <div class="container flash-stack mt-3" aria-live="polite">
        <?php foreach ($flashGroups as $flashType => $messages): ?>
            <?php $style = $alertStyles[$flashType] ?? $alertStyles['info']; ?>
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?= e($style['class']) ?> alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi <?= e($style['icon']) ?>"></i>
                    <div><?= e($message) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
