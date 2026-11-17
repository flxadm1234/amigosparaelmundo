<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return self::findBy('email', $email);
    }

    public static function withRole(int $id): ?array
    {
        $row = self::queryOne(
            'SELECT u.*, r.name AS role_name, r.slug AS role_slug FROM users u
             LEFT JOIN roles r ON r.id = u.role_id WHERE u.id = ?',
            [$id]
        );
        return $row ?: null;
    }

    public static function listWithRole(): array
    {
        return self::queryRows(
            'SELECT u.*, r.name AS role_name, r.slug AS role_slug FROM users u
             LEFT JOIN roles r ON r.id = u.role_id ORDER BY u.id DESC'
        );
    }
}
