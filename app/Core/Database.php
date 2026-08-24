<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * اتصال قاعدة البيانات (MySQL عبر PDO)
 * جميع الاستعلامات تستخدم Prepared Statements للحماية من SQL Injection
 */
class Database
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = App::config('db', []);
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            App::config('db_host', '127.0.0.1'),
            (int)App::config('db_port', 3306),
            App::config('db_name', 'document_verify')
        );

        try {
            self::$pdo = new PDO($dsn, App::config('db_user', 'root'), App::config('db_pass', ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return self::$pdo;
        } catch (PDOException $e) {
            error_log('[DB] ' . $e->getMessage());
            App::renderSetupError(
                'تعذر الاتصال بقاعدة البيانات',
                'تأكد من صحة بيانات الاتصال في ملف <code>config/config.php</code>، ومن أن قاعدة البيانات <code>' . e(App::config('db_name', '')) . '</code> تم إنشاؤها واستيراد ملف <code>database/database.sql</code> إليها.'
            );
        }
    }

    /** تنفيذ استعلام مع Prepared Statement */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** جلب صف واحد */
    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** جلب جميع الصفوف */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /** إدراج وإرجاع آخر ID */
    public static function insert(string $sql, array $params = []): int
    {
        self::query($sql, $params);
        return (int)self::connect()->lastInsertId();
    }

    public static function update(string $sql, array $params = []): int
    {
        return self::query($sql, $params)->rowCount();
    }

    public static function delete(string $sql, array $params = []): int
    {
        return self::query($sql, $params)->rowCount();
    }

    public static function begin(): void
    {
        self::connect()->beginTransaction();
    }

    public static function commit(): void
    {
        self::connect()->commit();
    }

    public static function rollback(): void
    {
        self::connect()->rollBack();
    }
}
