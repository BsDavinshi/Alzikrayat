<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $requestedFile = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($requestedFile)) {
        return false;
    }
}

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/core/bootstrap.php';

$router = new Router();
require BASE_PATH . '/routes/web.php';

$router->dispatch(new Request());
