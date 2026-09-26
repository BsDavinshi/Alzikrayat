<?php
$applicationName = Config::get('app.name');
$documentTitle = isset($pageTitle) ? $pageTitle . ' · ' . $applicationName : $applicationName;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Alzikrayat — a photo sharing space for the memories that matter.">
    <meta name="csrf-token" content="<?= e(Session::csrfToken()) ?>">
    <meta name="base-url" content="<?= e(rtrim(url(''), '/')) ?>">
    <title><?= e($documentTitle) ?></title>
    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('alz-theme');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.setAttribute('data-bs-theme', savedTheme || (prefersDark ? 'dark' : 'light'));
            } catch (storageError) { }
        })();
    </script>
    <link rel="icon" href="<?= e(asset('images/logo.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,650&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(asset('css/app.css')) ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <a class="visually-hidden-focusable skip-link" href="#main-content">Skip to content</a>

    <?= View::partial('layout/navbar') ?>

    <main id="main-content" class="flex-grow-1">
        <?= View::partial('layout/flash') ?>
        <?= $content ?>
    </main>

    <?= View::partial('layout/footer') ?>

    <?= View::partial('layout/confirm_modal') ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= e(asset('js/app.js')) ?>"></script>
    <script src="<?= e(asset('js/validation.js')) ?>"></script>
    <?php foreach ($pageScripts ?? [] as $scriptPath): ?>
        <script src="<?= e(asset($scriptPath)) ?>"></script>
    <?php endforeach; ?>
</body>
</html>
