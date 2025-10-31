<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Services\DataIngestor;
use App\Support\Logger;

try {
    $ingestor = new DataIngestor();
    $ingestor->syncTeamsAndPlayers();
} catch (Throwable $e) {
    Logger::error('Teams & players cron failed', ['error' => $e->getMessage()]);
    throw $e;
}
