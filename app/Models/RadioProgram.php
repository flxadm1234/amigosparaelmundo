<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class RadioProgram extends Model
{
    protected static string $table = 'radio_programs';

    public static function allActive(): array
    {
        return self::queryRows(
            'SELECT * FROM radio_programs WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        $row = self::queryOne(
            'SELECT * FROM radio_programs WHERE slug = ? LIMIT 1',
            [$slug]
        );
        return $row ?: null;
    }

    public static function searchAdmin(array $filters, int $page, int $perPage): array
    {
        $where = [];
        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $where[] = ['is_active', '=', (int)$filters['is_active']];
        }
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $where[] = ['AND (name LIKE ? OR slug LIKE ? OR hosts LIKE ? OR schedule_text LIKE ? OR description LIKE ?)', null, [$q, $q, $q, $q, $q]];
        }

        $sqlBase = ' FROM radio_programs ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sqlBase .= ' WHERE ' . $whereSql;
        }

        $countSql = 'SELECT COUNT(*) AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $orderSort = !empty($filters['order']) && $filters['order'] === 'name' ? 'name ASC, sort_order ASC, id ASC' : 'sort_order ASC, id ASC';
        $sql = 'SELECT * ' . $sqlBase . " ORDER BY {$orderSort}";

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
}
