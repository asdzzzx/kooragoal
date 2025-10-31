<?php

use App\Support\Config;

if (!function_exists('config')) {
    function config(string $key, $default = null)
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__);
        return $path ? $base . '/' . ltrim($path, '/') : $base;
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        $base = base_path('storage');
        if (!is_dir($base)) {
            mkdir($base, 0755, true);
        }

        return $path ? $base . '/' . ltrim($path, '/') : $base;
    }
}

if (!function_exists('response_json')) {
    function response_json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

if (!function_exists('request_method')) {
    function request_method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
}

if (!function_exists('get_bearer_token')) {
    function get_bearer_token(): ?string
    {
        $headers = getallheaders();
        $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if ($authorization && preg_match('/Bearer\s(.*)/', $authorization, $matches)) {
            return trim($matches[1]);
        }

        if (!empty($_GET['token'])) {
            return $_GET['token'];
        }

        return null;
    }
}

if (!function_exists('sanitize_domain')) {
    function sanitize_domain(string $host): string
    {
        return strtolower(preg_replace('/^www\./', '', $host));
    }
}
