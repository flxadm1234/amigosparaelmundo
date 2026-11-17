<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    protected static string $table = 'categories';

    public static function activeOrdered(): array
    {
        return self::queryRows(
            'SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC'
        );
    }

    public static function nameFor(int $id): string
    {
        if ($id <= 0) return '';
        $row = self::queryOne('SELECT name FROM categories WHERE id = ? LIMIT 1', [$id]);
        return $row['name'] ?? '';
    }
}
