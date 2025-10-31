<?php

namespace App\Support;

class Config
{
    private static array $items = [];

    public static function set(array $config): void
    {
        self::$items = $config;
    }

    public static function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $value = self::$items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
