<?php

namespace Kooragoal\Services;

class Logger
{
    private string $logFile;

    public function __construct(string $logFile)
    {
        $this->logFile = $logFile;
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    private function write(string $level, string $message, array $context = []): void
    {
        $contextJson = $context ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        $line = sprintf("[%s] %s: %s %s\n", date('c'), $level, $message, $contextJson);
        file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
