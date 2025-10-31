<?php

namespace App\Security;

class RateLimiter
{
    private string $name;
    private int $maxRequests;
    private int $ttl;

    public function __construct(string $name, int $maxRequests, int $minutes)
    {
        $this->name = $name;
        $this->maxRequests = $maxRequests;
        $this->ttl = $minutes * 60;
    }

    public function hit(string $key): bool
    {
        $path = storage_path('cache/rate_limits');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $file = $path . '/' . md5($this->name . $key) . '.json';
        $now = time();
        $data = ['count' => 0, 'expires' => $now + $this->ttl];

        if (file_exists($file)) {
            $content = json_decode(file_get_contents($file), true);
            if (is_array($content)) {
                $data = $content;
            }
        }

        if ($data['expires'] <= $now) {
            $data = ['count' => 0, 'expires' => $now + $this->ttl];
        }

        $data['count']++;

        file_put_contents($file, json_encode($data));

        return $data['count'] <= $this->maxRequests;
    }
}
