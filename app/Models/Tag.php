<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Tag extends Model
{
    protected static string $table = 'tags';

    public static function activeOrdered(): array
    {
        return self::queryRows(
            'SELECT * FROM tags WHERE is_active = 1 ORDER BY name ASC'
        );
    }

    public static function findBySlugs(array $slugs): array
    {
        if (count($slugs) === 0) {
            return [];
        }
        $ph = implode(',', array_fill(0, count($slugs), '?'));
        return self::queryRows("SELECT * FROM tags WHERE slug IN ({$ph})", array_values($slugs));
    }

    public static function nameFor(int $id): string
    {
        if ($id <= 0) return '';
        $row = self::queryOne('SELECT name FROM tags WHERE id = ? LIMIT 1', [$id]);
        return $row['name'] ?? '';
    }
}
