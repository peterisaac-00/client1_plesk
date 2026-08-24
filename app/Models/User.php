<?php

namespace App\Models;

use App\Core\App;
use App\Core\Database;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return Database::fetch('SELECT * FROM users WHERE email = ? LIMIT 1', [strtolower(trim($email))]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        if ($hash === '') {
            return false;
        }
        return password_verify($password, $hash);
    }

    public static function updatePassword(int $id, string $password): void
    {
        Database::update(
            'UPDATE users SET password = ?, must_change_password = 0, updated_at = NOW() WHERE id = ?',
            [password_hash($password, PASSWORD_BCRYPT), $id]
        );
    }

    public static function updateName(int $id, string $name): void
    {
        Database::update('UPDATE users SET name = ?, updated_at = NOW() WHERE id = ?', [trim($name), $id]);
    }

    public static function updateEmail(int $id, string $email): void
    {
        Database::update('UPDATE users SET email = ?, updated_at = NOW() WHERE id = ?', [strtolower(trim($email)), $id]);
    }

    // ---------- حماية محاولات الدخول ----------

    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public static function isLocked(string $email, string $ip): bool
    {
        $row = Database::fetch(
            'SELECT attempts, last_attempt FROM login_attempts
             WHERE email = ? AND ip_address = ? AND last_attempt > DATE_SUB(NOW(), INTERVAL 15 MINUTE)',
            [strtolower(trim($email)), $ip]
        );
        return $row !== null && (int)$row['attempts'] >= self::MAX_ATTEMPTS;
    }

    public static function registerFailedAttempt(string $email, string $ip): void
    {
        Database::query(
            'INSERT INTO login_attempts (email, ip_address, attempts, last_attempt)
             VALUES (?, ?, 1, NOW())
             ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()',
            [strtolower(trim($email)), $ip]
        );
    }

    public static function clearFailedAttempts(string $email, string $ip): void
    {
        Database::delete('DELETE FROM login_attempts WHERE email = ? AND ip_address = ?', [strtolower(trim($email)), $ip]);
    }

    public static function remainingAttempts(string $email, string $ip): int
    {
        $row = Database::fetch(
            'SELECT attempts FROM login_attempts
             WHERE email = ? AND ip_address = ? AND last_attempt > DATE_SUB(NOW(), INTERVAL 15 MINUTE)',
            [strtolower(trim($email)), $ip]
        );
        return max(0, self::MAX_ATTEMPTS - (int)($row['attempts'] ?? 0));
    }
}
