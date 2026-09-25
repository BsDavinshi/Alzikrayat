<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $normalizedPath = $path === '' ? '' : '/' . ltrim($path, '/');
    return Request::basePath() . ($normalizedPath === '' ? '/' : $normalizedPath);
}

function asset(string $path): string
{
    return url('/' . ltrim($path, '/'));
}

function uploadUrl(string $fileName): string
{
    return asset(Config::get('upload.publicPath') . '/' . rawurlencode(basename($fileName)));
}

function csrfField(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Session::csrfToken()) . '">';
}

function old(string $field, string $defaultValue = ''): string
{
    return e(Session::old($field, $defaultValue));
}

function invalidClass(string $field): string
{
    return Session::error($field) !== null ? ' is-invalid' : '';
}

function fieldError(string $field): string
{
    $message = Session::error($field);
    return $message === null ? '' : '<div class="invalid-feedback d-block">' . e($message) . '</div>';
}

function formatDate(?string $dateTime): string
{
    $timestamp = $dateTime ? strtotime($dateTime) : false;
    return $timestamp ? date('M j, Y · g:i A', $timestamp) : '';
}

function timeAgo(?string $dateTime): string
{
    $timestamp = $dateTime ? strtotime($dateTime) : false;
    if (!$timestamp) {
        return '';
    }

    $secondsElapsed = max(0, time() - $timestamp);
    $units = ['year' => 31536000, 'month' => 2592000, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'minute' => 60];

    foreach ($units as $unitName => $unitSeconds) {
        if ($secondsElapsed >= $unitSeconds) {
            $amount = intdiv($secondsElapsed, $unitSeconds);
            return $amount . ' ' . $unitName . ($amount > 1 ? 's' : '') . ' ago';
        }
    }
    return 'just now';
}

function initials(string $firstName, string $lastName): string
{
    return mb_strtoupper(mb_substr($firstName, 0, 1) . mb_substr($lastName, 0, 1));
}

function avatarColor(int $userId): string
{
    $hue = ($userId * 137) % 360;
    return "hsl({$hue}, 55%, 45%)";
}

function activeClass(string $path, bool $isPrefix = false): string
{
    $currentPath = (new Request())->getPath();
    $isActive = $currentPath === $path || ($isPrefix && str_starts_with($currentPath, $path));
    return $isActive ? 'active' : '';
}

function filterLabel(string $filterName): string
{
    return ucwords(str_replace('-', ' ', $filterName));
}
