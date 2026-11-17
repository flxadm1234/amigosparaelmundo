<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class SiteSetting extends Model
{
    protected static string $table = 'site_settings';

    private static ?array $cache = null;

    private static function loadAll(): void
    {
        if (self::$cache !== null) {
            return;
        }
        self::$cache = [];
        try {
            $rows = self::queryRows('SELECT `setting_key`, `value_text` FROM `site_settings`');
            foreach ($rows as $r) {
                self::$cache[$r['setting_key']] = (string)$r['value_text'];
            }
        } catch (\Throwable) {
            self::$cache = [];
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        self::loadAll();
        return self::$cache[$key] ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        self::execute('INSERT INTO site_settings (setting_key, value_text) VALUES (?,?)
                       ON DUPLICATE KEY UPDATE value_text = VALUES(value_text), updated_at = CURRENT_TIMESTAMP', [$key, $value]);
        self::$cache = null;
    }

    public static function allAsMap(): array
    {
        self::loadAll();
        return self::$cache ?? [];
    }
}
