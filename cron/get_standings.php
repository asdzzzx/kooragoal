<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Services\DataIngestor;
use App\Support\Logger;

try {
    $ingestor = new DataIngestor();
    $ingestor->syncStandingsAndScorers();
} catch (Throwable $e) {
    Logger::error('Standings cron failed', ['error' => $e->getMessage()]);
    throw $e;
}
