<?php
declare(strict_types=1);

final class Session
{
    private static array $oldInput = [];

    private static array $validationErrors = [];

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name('ALZSESSID');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');
        session_start();

        self::$oldInput = $_SESSION['_old_input'] ?? [];
        self::$validationErrors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_old_input'], $_SESSION['_errors']);
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    }

    public static function get(string $key, mixed $defaultValue = null): mixed
    {
        return $_SESSION[$key] ?? $defaultValue;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function pull(string $key): mixed
    {
        $value = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);
        return $value;
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = $message;
    }

    public static function consumeFlashes(): array
    {
        $flashMessages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashMessages;
    }

    public static function flashInput(array $input): void
    {
        $_SESSION['_old_input'] = $input;
    }

    public static function flashErrors(array $errors): void
    {
        $_SESSION['_errors'] = $errors;
    }

    public static function old(string $key, string $defaultValue = ''): string
    {
        $value = self::$oldInput[$key] ?? $defaultValue;
        return is_scalar($value) ? (string) $value : $defaultValue;
    }

    public static function error(string $field): ?string
    {
        return self::$validationErrors[$field] ?? null;
    }

    public static function errors(): array
    {
        return self::$validationErrors;
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function verifyCsrf(?string $submittedToken): bool
    {
        return is_string($submittedToken)
            && !empty($_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $submittedToken);
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function reset(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }
}
