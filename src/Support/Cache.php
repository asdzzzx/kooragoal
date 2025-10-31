<?php

namespace App\Support;

class Cache
{
    public static function remember(string $key, int $seconds, callable $callback)
    {
        $path = storage_path('cache');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $file = $path . '/' . md5($key) . '.cache.php';

        if (file_exists($file)) {
            $data = include $file;
            if (is_array($data) && isset($data['expires']) && $data['expires'] >= time()) {
                return $data['value'];
            }
        }

        $value = $callback();

        file_put_contents($file, '<?php return ' . var_export([
            'expires' => time() + $seconds,
            'value' => $value,
        ], true) . ';');

        return $value;
    }

    public static function forget(string $key): void
    {
        $file = storage_path('cache/' . md5($key) . '.cache.php');
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
