<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class PodcastEpisode extends Model
{
    protected static string $table = 'podcast_episodes';

    public static function findPublishedBySlug(string $slug): ?array
    {
        $row = self::queryOne(
            'SELECT * FROM podcast_episodes
             WHERE slug = ? AND is_published = 1',
            [$slug]
        );
        return $row ?: null;
    }

    public static function allPublished(int $page, int $perPage): array
    {
        $where = [['is_published', '=', 1]];

        $sqlBase = ' FROM podcast_episodes ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sqlBase .= ' WHERE ' . $whereSql;
        }

        $countSql = 'SELECT COUNT(*) AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $sql = 'SELECT * ' . $sqlBase . ' ORDER BY publish_date DESC, id DESC';
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

    public static function latestPublished(int $limit = 12): array
    {
        return self::queryRows(
            'SELECT * FROM podcast_episodes
             WHERE is_published = 1
             ORDER BY publish_date DESC, id DESC
             LIMIT ' . (int)$limit
        );
    }

    public static function searchPublic(array $filters, int $page, int $perPage): array
    {
        $where = [['is_published', '=', 1]];
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $where[] = ['AND (title LIKE ? OR slug LIKE ? OR description LIKE ?)', null, [$q, $q, $q]];
        }

        $sqlBase = ' FROM podcast_episodes ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sqlBase .= ' WHERE ' . $whereSql;
        }
        $order = !empty($filters['order']) && $filters['order'] === 'old' ? 'ASC' : 'DESC';

        $countSql = 'SELECT COUNT(*) AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $sql = 'SELECT * ' . $sqlBase . " ORDER BY publish_date {$order}, id {$order}";
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

    public static function searchAdmin(array $filters, int $page, int $perPage): array
    {
        $where = [];
        if (array_key_exists('is_published', $filters) && $filters['is_published'] !== '' && $filters['is_published'] !== null) {
            $where[] = ['is_published', '=', (int)$filters['is_published']];
        }
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $where[] = ['AND (title LIKE ? OR slug LIKE ? OR description LIKE ?)', null, [$q, $q, $q]];
        }

        $sqlBase = ' FROM podcast_episodes ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sqlBase .= ' WHERE ' . $whereSql;
        }
        $order = !empty($filters['order']) && $filters['order'] === 'old' ? 'ASC' : 'DESC';
        $countSql = 'SELECT COUNT(*) AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $sql = 'SELECT * ' . $sqlBase . " ORDER BY publish_date {$order}, id {$order}";
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

    public static function incrementPlays(int $id): void
    {
        self::execute('UPDATE podcast_episodes SET plays_count = plays_count + 1 WHERE id = ?', [$id]);
    }
}
