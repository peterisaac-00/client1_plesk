<?php

namespace App\Models;

use App\Core\Database;

/**
 * الوحدة الأساسية للنماذج
 */
abstract class Model
{
    protected static string $table = '';

    public static function table(): string
    {
        return static::$table;
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM `' . static::$table . '` WHERE id = ? LIMIT 1', [$id]);
    }

    public static function all(): array
    {
        return Database::fetchAll('SELECT * FROM `' . static::$table . '` ORDER BY id DESC');
    }

    public static function count(string $where = '1=1', array $params = []): int
    {
        $row = Database::fetch('SELECT COUNT(*) AS c FROM `' . static::$table . '` WHERE ' . $where, $params);
        return (int)($row['c'] ?? 0);
    }

    public static function deleteById(int $id): int
    {
        return Database::delete('DELETE FROM `' . static::$table . '` WHERE id = ?', [$id]);
    }
}
