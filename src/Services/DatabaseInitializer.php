<?php

namespace Kooragoal\Services;

use RuntimeException;

class DatabaseInitializer
{
    private Database $db;
    private string $schemaFile;
    private ?Logger $logger;
    private bool $initialised = false;

    private array $requiredTables = [
        'leagues',
        'teams',
        'players',
        'fixtures',
        'events',
        'statistics',
        'standings',
        'scorers',
        'lineups',
        'system_updates',
        'admins',
        'logs',
        'admin_login_attempts',
    ];

    public function __construct(Database $db, string $schemaFile, ?Logger $logger = null)
    {
        $this->db = $db;
        $this->schemaFile = $schemaFile;
        $this->logger = $logger;
    }

    public function ensureSchema(): void
    {
        if ($this->initialised) {
            return;
        }

        $missing = $this->findMissingTables();
        if ($missing) {
            $this->runSchemaMigrations();
            $this->log('Database schema initialised', ['missing_tables' => array_values($missing)]);
        }

        $this->seedDefaultAdmin();

        $this->initialised = true;
    }

    private function findMissingTables(): array
    {
        $placeholders = implode(',', array_fill(0, count($this->requiredTables), '?'));
        $rows = $this->db->fetchAll(
            sprintf(
                'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (%s)',
                $placeholders
            ),
            $this->requiredTables
        );

        $existing = array_map(
            static function (array $row): string {
                $name = $row['TABLE_NAME'] ?? $row['table_name'] ?? '';
                return strtolower($name);
            },
            $rows
        );
        $missing = [];
        foreach ($this->requiredTables as $table) {
            if (!in_array(strtolower($table), $existing, true)) {
                $missing[] = $table;
            }
        }

        return $missing;
    }

    private function runSchemaMigrations(): void
    {
        if (!is_readable($this->schemaFile)) {
            throw new RuntimeException('Schema file not found: ' . $this->schemaFile);
        }

        $sql = file_get_contents($this->schemaFile);
        if ($sql === false) {
            throw new RuntimeException('Unable to read schema file: ' . $this->schemaFile);
        }

        $statements = array_filter(array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', $sql)));
        $pdo = $this->db->getConnection();
        foreach ($statements as $statement) {
            if ($statement !== '') {
                $pdo->exec($statement);
            }
        }
    }

    private function seedDefaultAdmin(): void
    {
        $existing = $this->db->fetch('SELECT id FROM admins LIMIT 1');
        if ($existing) {
            return;
        }

        $this->db->execute(
            'INSERT INTO admins (username, password) VALUES (:username, :password)',
            [
                'username' => 'admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
            ]
        );

        $this->log('Default admin account created', ['username' => 'admin']);
    }

    private function log(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
    }
}
