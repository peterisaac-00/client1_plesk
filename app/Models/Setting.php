<?php

namespace App\Models;

use App\Core\Database;
use App\Core\App;

class Setting extends Model
{
    protected static string $table = 'settings';

    /** الحقول القابلة للتعديل من صفحة الإعدادات */
    public const EDITABLE = [
        'system_name', 'org_name', 'org_email', 'org_phone', 'org_address',
        'base_url', 'ip_logging', 'logo_path',
    ];

    public static function set(string $key, string $value): void
    {
        Database::query(
            'INSERT INTO settings (setting_key, setting_value, updated_at)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()',
            [$key, $value]
        );
    }

    public static function saveMany(array $data): void
    {
        foreach ($data as $key => $value) {
            if (in_array($key, self::EDITABLE, true)) {
                self::set($key, trim((string)$value));
            }
        }
        App::loadSettings();
    }
}
