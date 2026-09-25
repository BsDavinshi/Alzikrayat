<?php
declare(strict_types=1);

final class Request
{
    public function getMethod(): string
    {
        $requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        return $requestMethod === 'HEAD' ? 'GET' : $requestMethod;
    }

    public static function basePath(): string
    {
        static $cachedBasePath = null;

        if ($cachedBasePath === null) {
            $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
            $cachedBasePath = rtrim($scriptDirectory, '/');
        }

        return $cachedBasePath;
    }

    public function getPath(): string
    {
        $uriPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        $basePath = self::basePath();

        $candidatePrefixes = [$basePath];
        if (str_ends_with($basePath, '/public')) {
            $candidatePrefixes[] = substr($basePath, 0, -strlen('/public'));
        }

        foreach ($candidatePrefixes as $prefix) {
            if ($prefix !== '' && ($uriPath === $prefix || str_starts_with($uriPath, $prefix . '/'))) {
                $uriPath = substr($uriPath, strlen($prefix));
                break;
            }
        }

        if (str_starts_with($uriPath, '/index.php')) {
            $uriPath = substr($uriPath, strlen('/index.php'));
        }

        return '/' . trim($uriPath, '/');
    }

    public function input(string $key, mixed $defaultValue = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $defaultValue;
        return is_string($value) ? trim($value) : $value;
    }

    public function query(string $key, mixed $defaultValue = null): mixed
    {
        $value = $_GET[$key] ?? $defaultValue;
        return is_string($value) ? trim($value) : $value;
    }

    public function raw(string $key): string
    {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? $value : '';
    }

    public function file(string $key): ?array
    {
        $uploadedFile = $_FILES[$key] ?? null;
        return is_array($uploadedFile) ? $uploadedFile : null;
    }

    public function header(string $name): ?string
    {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return isset($_SERVER[$serverKey]) ? (string) $_SERVER[$serverKey] : null;
    }

    public function isAjax(): bool
    {
        $requestedWith = strtolower($this->header('X-Requested-With') ?? '');
        $acceptHeader = $this->header('Accept') ?? '';
        return $requestedWith === 'xmlhttprequest' || str_contains($acceptHeader, 'application/json');
    }

    public function getPathWithQuery(): string
    {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        return $this->getPath() . ($queryString !== '' ? '?' . $queryString : '');
    }
}
