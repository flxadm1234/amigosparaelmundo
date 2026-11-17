<?php
/**
 * DB - Conexión PDO singleton
 */

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class DB
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $cfg = require dirname(__DIR__, 2) . '/config/config.php';
            $db  = $cfg['db'];
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $db['host'],
                $db['port'],
                $db['name'],
                $db['charset']
            );
            try {
                self::$instance = new PDO($dsn, $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_FOUND_ROWS   => true,
                ]);
                self::$instance->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            } catch (PDOException $e) {
                http_response_code(500);
                die('Error de conexión a base de datos. Revise configuración.');
            }
        }
        return self::$instance;
    }

    public static function lastInsertId(?string $name = null): string
    {
        return self::connection()->lastInsertId($name);
    }
}
