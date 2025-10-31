<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Services\DataIngestor;
use App\Support\Logger;

try {
    $ingestor = new DataIngestor();
    $ingestor->syncDailyFixtures();
} catch (Throwable $e) {
    Logger::error('Daily fixtures cron failed', ['error' => $e->getMessage()]);
    throw $e;
}
