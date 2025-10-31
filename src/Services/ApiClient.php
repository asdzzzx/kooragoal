<?php

namespace Kooragoal\Services;

use RuntimeException;

class ApiClient
{
    private string $baseUrl;
    private string $apiKey;
    private Logger $logger;
    private Database $db;
    private int $timeout;

    public function __construct(array $config, Logger $logger, Database $db)
    {
        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->apiKey = $config['key'];
        $this->timeout = $config['timeout'] ?? 25;
        $this->logger = $logger;
        $this->db = $db;
    }

    public function get(string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        if ($params) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'x-rapidapi-host: v3.football.api-sports.io',
                'x-apisports-key: ' . $this->apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            $this->logger->error('API request failed', [
                'endpoint' => $endpoint,
                'params' => $params,
                'error' => $curlError,
            ]);
            $this->writeLog('api_error', sprintf('فشل الاتصال %s: %s', $endpoint, $curlError));
            throw new RuntimeException('API request failed: ' . $curlError);
        }

        $decoded = json_decode($response, true);
        if ($statusCode >= 400 || json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('API request returned error', [
                'endpoint' => $endpoint,
                'params' => $params,
                'status' => $statusCode,
                'response' => $response,
            ]);
            $this->writeLog('api_error', sprintf('استجابة خاطئة %s: %s', $endpoint, $statusCode));
            throw new RuntimeException('API error: ' . $statusCode);
        }

        $count = $decoded['results'] ?? count($decoded['response'] ?? []);
        $this->logger->info('API request success', [
            'endpoint' => $endpoint,
            'params' => $params,
            'count' => $count,
        ]);
        $this->writeLog('api_success', sprintf('سحب %s (%d سجل)', $endpoint, $count));

        return $decoded['response'] ?? [];
    }

    private function writeLog(string $type, string $message): void
    {
        $this->db->execute('INSERT INTO logs (type, message, created_at) VALUES (:type, :message, NOW())', [
            'type' => $type,
            'message' => $message,
        ]);
    }
}
