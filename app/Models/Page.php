<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Page extends Model
{
    protected static string $table = 'pages';

    public static function findPublishedBySlug(string $slug): ?array
    {
        return self::queryOne(
            'SELECT * FROM pages WHERE slug = ? AND is_published = 1',
            [$slug]
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        return self::findBy('slug', $slug);
    }
}
