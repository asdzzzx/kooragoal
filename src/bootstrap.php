<?php
use Kooragoal\Services\Container;

$vendor = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendor)) {
    require_once $vendor;
}

spl_autoload_register(function ($class) {
    $prefix = 'Kooragoal\\';
    $baseDir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

$config = require __DIR__ . '/../config/config.php';

$container = new Container($config);
