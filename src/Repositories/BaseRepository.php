<?php

namespace App\Repositories;

use App\Support\Database;
use PDO;

abstract class BaseRepository
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    protected function insertOrUpdate(string $table, array $data, array $uniqueKeys): void
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $updateParts = [];
        foreach ($columns as $column) {
            if (!in_array($column, $uniqueKeys, true)) {
                $updateParts[] = sprintf('`%s` = VALUES(`%s`)', $column, $column);
            }
        }

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $table,
            '`' . implode('`,`', $columns) . '`',
            implode(',', $placeholders),
            implode(',', $updateParts)
        );

        $stmt = $this->db->prepare($sql);
        foreach ($data as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }
        $stmt->execute();
    }
}
