<?php

use App\Support\Config;

require_once __DIR__ . '/helpers.php';

$config = [
    'app' => [
        'name' => 'Kooragoal Local Sports Hub',
        'url' => 'https://Yacine--tv.live',
        'timezone' => 'Africa/Cairo',
        'domain_whitelist' => [
            'yacine--tv.live',
            'www.yacine--tv.live',
        ],
    ],
    'database' => [
        'driver' => 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_DATABASE') ?: 'yacinetv_football_db',
        'username' => getenv('DB_USERNAME') ?: 'yacinetv_football_db',
        'password' => getenv('DB_PASSWORD') ?: '01010250185!!',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    'api' => [
        'base_url' => 'https://v3.football.api-sports.io',
        'key' => getenv('FOOTBALL_API_KEY') ?: 'eee60a0a491976182f656c6e4e7d1a25',
        'host' => 'v3.football.api-sports.io',
        'rate_limit' => 50,
    ],
    'auth' => [
        'admin' => [
            'username' => getenv('ADMIN_USERNAME') ?: 'admin',
            'password_hash' => getenv('ADMIN_PASSWORD_HASH') ?: '$2y$12$ULQAc.ul5W718wm7VwLr3uEypwhg9DkX/G.6SNNwvyEG3s3DE2Uqa',
        ],
        'tokens' => include __DIR__ . '/tokens.php',
    ],
    'security' => [
        'api_rate_limit' => [
            'max_requests' => 120,
            'per_minutes' => 1,
        ],
        'admin_rate_limit' => [
            'max_requests' => 60,
            'per_minutes' => 1,
        ],
        'backup' => [
            'path' => __DIR__ . '/../storage/backups',
        ],
    ],
    'leagues' => include __DIR__ . '/leagues.php',
];

date_default_timezone_set($config['app']['timezone']);

if (!is_dir($config['security']['backup']['path'])) {
    mkdir($config['security']['backup']['path'], 0755, true);
}

Config::set($config);

return $config;
