<?php
declare(strict_types=1);

final class Config
{
    private static array $settings = [];

    public static function load(array $settings): void
    {
        self::$settings = $settings;
    }

    public static function get(string $key, mixed $defaultValue = null): mixed
    {
        $currentNode = self::$settings;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($currentNode) || !array_key_exists($segment, $currentNode)) {
                return $defaultValue;
            }
            $currentNode = $currentNode[$segment];
        }

        return $currentNode;
    }
}
