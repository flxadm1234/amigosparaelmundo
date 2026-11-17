<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Section extends Model
{
    protected static string $table = 'sections';

    public static function allActive(string $orderBy = 'sort_order', string $dir = 'ASC'): array
    {
        $allowedOrder = ['sort_order', 'name', 'id', 'created_at'];
        if (!in_array($orderBy, $allowedOrder, true)) {
            $orderBy = 'sort_order';
        }
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        return self::queryRows(
            "SELECT * FROM sections WHERE is_active = 1 ORDER BY {$orderBy} {$dir}"
        );
    }

    public static function allFeatured(): array
    {
        return self::queryRows(
            'SELECT * FROM sections WHERE is_active = 1 AND is_featured = 1 ORDER BY sort_order ASC, id ASC'
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        $row = self::queryOne(
            'SELECT * FROM sections WHERE slug = ? LIMIT 1',
            [$slug]
        );
        return $row ?: null;
    }

    public static function byType(string $type): array
    {
        return self::queryRows(
            'SELECT * FROM sections WHERE section_type = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC',
            [$type]
        );
    }

    public static function activeOrdered(): array
    {
        return self::allActive();
    }
}
