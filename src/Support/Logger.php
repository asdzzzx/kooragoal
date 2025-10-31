<?php

namespace App\Support;

class Logger
{
    public static function info(string $message, array $context = []): void
    {
        self::writeLog('INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::writeLog('ERROR', $message, $context);
    }

    private static function writeLog(string $level, string $message, array $context): void
    {
        $dir = storage_path('logs');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $line = sprintf(
            "[%s] %s %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $context ? json_encode($context) : ''
        );

        file_put_contents($dir . '/app.log', $line, FILE_APPEND);
    }
}
