<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Support\Config;
use App\Support\Database;
use App\Support\Logger;

try {
    $pdo = Database::getConnection();
    $tables = ['fixtures','lineups','events','statistics','standings','scorers'];
    $data = [];

    foreach ($tables as $table) {
        $stmt = $pdo->query('SELECT * FROM ' . $table);
        $data[$table] = $stmt->fetchAll();
    }

    $directory = Config::get('security.backup.path');
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $filename = $directory . '/backup-' . date('Ymd-His') . '.json.gz';
    $encoded = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($filename, gzencode($encoded, 9));

    $pdo->prepare('INSERT INTO backups (file_path) VALUES (:file)')->execute([':file' => $filename]);
    Logger::info('Backup created', ['file' => $filename]);
} catch (Throwable $e) {
    Logger::error('Backup cron failed', ['error' => $e->getMessage()]);
    throw $e;
}
