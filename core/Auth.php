<?php
declare(strict_types=1);

final class Auth
{
    private const SESSION_KEY = 'auth_user';

    public static function login(array $user): void
    {
        Session::regenerate();
        self::refresh($user);
    }

    public static function refresh(array $user): void
    {
        Session::set(self::SESSION_KEY, [
            'id' => (int) $user['id'],
            'first_name' => (string) $user['first_name'],
            'last_name' => (string) $user['last_name'],
            'email' => (string) $user['email'],
        ]);
    }

    public static function logout(): void
    {
        Session::reset();
    }

    public static function check(): bool
    {
        return is_array(Session::get(self::SESSION_KEY));
    }

    public static function user(): ?array
    {
        $user = Session::get(self::SESSION_KEY);
        return is_array($user) ? $user : null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user !== null ? (int) $user['id'] : null;
    }
}
