<?php

namespace Kooragoal\Services;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = new PDO(
            $config['dsn'],
            $config['user'],
            $config['password'],
            $config['options'] ?? []
        );

        try {
            $this->pdo->exec('SET time_zone = "+00:00"');
        } catch (PDOException $exception) {
            $message = $exception->getMessage();
            if (
                stripos($message, 'super privilege') !== false ||
                stripos($message, 'access denied') !== false ||
                stripos($message, 'unknown or incorrect time zone') !== false
            ) {
                // Shared hosting environments (such as cPanel) often block SET time_zone without SUPER privileges.
                // Swallow the exception so the application can continue using the host default time zone.
                return;
            }

            throw $exception;
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function transaction(callable $callback)
    {
        try {
            $this->pdo->beginTransaction();
            $callback($this);
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
