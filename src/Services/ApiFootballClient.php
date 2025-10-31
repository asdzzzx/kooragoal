<?php

namespace App\Services;

use App\Support\Cache;
use App\Support\Config;

class ApiFootballClient
{
    private string $baseUrl;
    private string $apiKey;
    private string $host;

    public function __construct()
    {
        $apiConfig = Config::get('api');
        $this->baseUrl = rtrim($apiConfig['base_url'], '/');
        $this->apiKey = $apiConfig['key'];
        $this->host = $apiConfig['host'];
    }

    public function get(string $endpoint, array $query = [], int $ttl = 30)
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $cacheKey = $url;

        return Cache::remember($cacheKey, $ttl, function () use ($url) {
            $headers = [
                'Accept: application/json',
                'x-apisports-key: ' . $this->apiKey,
                'x-rapidapi-host: ' . $this->host,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new \RuntimeException('API request failed: ' . $error);
            }

            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $decoded = json_decode($response, true);

            if ($status >= 400) {
                throw new \RuntimeException('API error: ' . $status . ' ' . ($decoded['message'] ?? $response));
            }

            return $decoded;
        });
    }
}
