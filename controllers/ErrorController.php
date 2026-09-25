<?php
declare(strict_types=1);

class ErrorController extends Controller
{
    private const TITLES = [
        401 => 'Login Required',
        403 => 'Access Denied',
        404 => 'Page Not Found',
        405 => 'Method Not Allowed',
        419 => 'Session Expired',
        422 => 'Invalid Data',
        500 => 'Server Error',
    ];

    public function show(int $statusCode, string $message): void
    {
        http_response_code($statusCode);
        $this->render('errors/error', [
            'pageTitle' => self::TITLES[$statusCode] ?? 'Error',
            'errorCode' => $statusCode,
            'errorMessage' => $message,
            'debugDetails' => null,
        ]);
    }
}
