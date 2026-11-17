<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Flyer extends Model
{
    protected static string $table = 'flyers';

    public const POSICIONES = [
        'home-carousel' => 'Carrusel Home',
        'home-grid'     => 'Grilla Home',
        'sidebar'       => 'Sidebar',
        'section-top'   => 'Top Sección',
    ];

    public const TARGETS = [
        '_self'  => 'Misma ventana',
        '_blank' => 'Nueva ventana',
    ];

    public static function getActiveByPosition(string $position, int $limit = 20): array
    {
        $now = date('Y-m-d H:i:s');
        $sql = 'SELECT * FROM flyers
                WHERE position = ?
                  AND is_active = 1
                  AND (start_at IS NULL OR start_at <= ?)
                  AND (end_at IS NULL OR end_at >= ?)
                ORDER BY sort_order ASC, id ASC
                LIMIT ' . (int)$limit;
        $rows = self::queryRows($sql, [$position, $now, $now]);
        $out = [];
        foreach ($rows as $r) {
            $out[] = $r;
        }
        return $out;
    }

    public static function adminSearch(array $filters, int $page, int $perPage): array
    {
        $where = [];
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $where[] = ['AND (f.title LIKE ? OR f.link_url LIKE ?)', null, [$q, $q]];
        }
        if (!empty($filters['position'])) {
            $where[] = ['f.position', '=', $filters['position']];
        }
        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $where[] = ['f.is_active', '=', (int)$filters['is_active']];
        }

        $sqlBase = ' FROM flyers f ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') $sqlBase .= ' WHERE ' . $whereSql;

        $countSql = 'SELECT COUNT(*) AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $sql = 'SELECT f.* ' . $sqlBase . ' ORDER BY f.sort_order ASC, f.id DESC';
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $sql .= ' LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;
        $items = self::queryRows($sql, $params);

        return [
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int)ceil($total / max(1, $perPage)),
            'from'        => $total === 0 ? 0 : $offset + 1,
            'to'          => min($offset + $perPage, $total),
        ];
    }

    public static function incrementViews(int $id): void
    {
        self::execute('UPDATE flyers SET views_count = views_count + 1 WHERE id = ?', [$id]);
    }

    public static function incrementClicks(int $id): void
    {
        self::execute('UPDATE flyers SET clicks_count = clicks_count + 1 WHERE id = ?', [$id]);
    }
}
