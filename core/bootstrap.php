<?php
declare(strict_types=1);

spl_autoload_register(static function (string $className): void {
    foreach (['core', 'controllers', 'models'] as $directory) {
        $classFile = BASE_PATH . '/' . $directory . '/' . $className . '.php';
        if (is_file($classFile)) {
            require $classFile;
            return;
        }
    }
});

Config::load(require BASE_PATH . '/config/config.php');
require BASE_PATH . '/core/helpers.php';
require BASE_PATH . '/config/database.php';

date_default_timezone_set((string) Config::get('app.timezone', 'UTC'));
mb_internal_encoding('UTF-8');

$isDebugMode = (bool) Config::get('app.debug', false);
error_reporting(E_ALL);
ini_set('display_errors', $isDebugMode ? '1' : '0');

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (Throwable $exception) use ($isDebugMode): void {
    error_log('[Alzikrayat] ' . $exception);

    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $exception . PHP_EOL);
        exit(1);
    }

    $friendlyMessage = 'Something went wrong on our side. Please try again in a moment.';
    if ($exception instanceof PDOException) {
        $friendlyMessage = 'The database is unavailable. Make sure MySQL is running and database/schema.sql has been imported.';
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(500);

    try {
        echo View::render('errors/error', [
            'pageTitle' => 'Server Error',
            'errorCode' => 500,
            'errorMessage' => $friendlyMessage,
            'debugDetails' => $isDebugMode ? (string) $exception : null,
        ]);
    } catch (Throwable $renderFailure) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $friendlyMessage;
    }
});

if (PHP_SAPI !== 'cli') {
    Response::sendSecurityHeaders();
    Session::start();
}
