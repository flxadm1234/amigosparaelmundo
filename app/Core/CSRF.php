<?php

declare(strict_types=1);

namespace App\Core;

class CSRF
{
    public const SESSION_KEY = '_csrf_tokens';

    private static function getKey(): string
    {
        $cfg = require dirname(__DIR__, 2) . '/config/config.php';
        return (string) ($cfg['security']['csrf_token_name'] ?? '_csrf_token');
    }

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
        $key = self::getKey();
        if (empty($_SESSION[self::SESSION_KEY][$key]) || !is_string($_SESSION[self::SESSION_KEY][$key])) {
            $_SESSION[self::SESSION_KEY][$key] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY][$key];
    }

    public static function check(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }
        $key = self::getKey();
        $stored = $_SESSION[self::SESSION_KEY][$key] ?? null;
        if (!is_string($stored)) {
            return false;
        }
        return hash_equals($stored, $token);
    }

    public static function regenerate(): void
    {
        $key = self::getKey();
        $_SESSION[self::SESSION_KEY][$key] = bin2hex(random_bytes(32));
    }
}
