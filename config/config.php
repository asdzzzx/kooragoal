<?php
return [
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=yacinetv_football_db;charset=utf8mb4',
        'user' => 'yacinetv_football_db',
        'password' => '01010250185!!',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    ],
    'api' => [
        'base_url' => 'https://v3.football.api-sports.io',
        'key' => 'eee60a0a491976182f656c6e4e7d1a25',
        'timeout' => 25,
    ],
    'security' => [
        'session_name' => 'kooragoal_admin',
        'csrf_token_key' => 'kooragoal_csrf',
        'lockout_threshold' => 5,
        'lockout_minutes' => 15,
        'session_lifetime' => 3600,
    ],
];
