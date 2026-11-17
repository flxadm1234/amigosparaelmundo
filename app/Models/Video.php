<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Video extends Model
{
    protected static string $table = 'videos';

    public static function findPublishedBySlug(string $slug): ?array
    {
        $row = self::queryOne(
            'SELECT v.*, s.name AS section_name, s.slug AS section_slug
             FROM videos v
             LEFT JOIN sections s ON s.id = v.section_id
             WHERE v.slug = ? AND v.is_published = 1',
            [$slug]
        );
        return $row ?: null;
    }

    public static function allPublished(int $page, int $perPage): array
    {
        $where = [['v.is_published', '=', 1]];

        $sqlBase = ' FROM videos v
                     LEFT JOIN sections s ON s.id = v.section_id ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sqlBase .= ' WHERE ' . $whereSql;
        }

        $countSql = 'SELECT COUNT(*) AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $sql = 'SELECT v.*, s.name AS section_name, s.slug AS section_slug ' . $sqlBase .
               ' ORDER BY v.publish_date DESC, v.id DESC';
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
            'SELECT v.*, s.name AS section_name, s.slug AS section_slug
             FROM videos v
             LEFT JOIN sections s ON s.id = v.section_id
             WHERE v.is_published = 1
             ORDER BY v.publish_date DESC, v.id DESC
             LIMIT ' . (int)$limit
        );
    }

    public static function bySectionId(int $sectionId, int $page, int $perPage): array
    {
        $where = [['v.is_published', '=', 1], ['v.section_id', '=', $sectionId]];

        $sqlBase = ' FROM videos v
                     LEFT JOIN sections s ON s.id = v.section_id ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sqlBase .= ' WHERE ' . $whereSql;
        }

        $countSql = 'SELECT COUNT(*) AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $sql = 'SELECT v.*, s.name AS section_name, s.slug AS section_slug ' . $sqlBase .
               ' ORDER BY v.publish_date DESC, v.id DESC';
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

    public static function searchPublic(array $filters, int $page, int $perPage): array
    {
        $where = [['v.is_published', '=', 1]];
        if (!empty($filters['section_id'])) {
            $where[] = ['v.section_id', '=', (int)$filters['section_id']];
        }
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $where[] = ['AND (v.title LIKE ? OR v.slug LIKE ? OR v.description LIKE ?)', null, [$q, $q, $q]];
        }

        $sqlBase = ' FROM videos v
                     LEFT JOIN sections s ON s.id = v.section_id ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sqlBase .= ' WHERE ' . $whereSql;
        }
        $order = !empty($filters['order']) && $filters['order'] === 'old' ? 'ASC' : 'DESC';

        $countSql = 'SELECT COUNT(*) AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $sql = 'SELECT v.*, s.name AS section_name, s.slug AS section_slug ' . $sqlBase .
               " ORDER BY v.publish_date {$order}, v.id {$order}";
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
            $where[] = ['v.is_published', '=', (int)$filters['is_published']];
        }
        if (!empty($filters['section_id'])) {
            $where[] = ['v.section_id', '=', (int)$filters['section_id']];
        }
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $where[] = ['AND (v.title LIKE ? OR v.slug LIKE ? OR v.embed_url LIKE ?)', null, [$q, $q, $q]];
        }

        $sqlBase = ' FROM videos v
                     LEFT JOIN sections s ON s.id = v.section_id ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sqlBase .= ' WHERE ' . $whereSql;
        }
        $order = !empty($filters['order']) && $filters['order'] === 'old' ? 'ASC' : 'DESC';
        $countSql = 'SELECT COUNT(*) AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $sql = 'SELECT v.*, s.name AS section_name ' . $sqlBase .
               " ORDER BY v.publish_date {$order}, v.id {$order}";
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
        self::execute('UPDATE videos SET views_count = views_count + 1 WHERE id = ?', [$id]);
    }

    public static function getEmbedHtml(array $video): string
    {
        $url = $video['embed_url'] ?? '';
        if ($url === '') {
            return '';
        }

        $html = '';

        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $matches)) {
            $id = $matches[1];
            $html = '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . htmlspecialchars($id) . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
        } elseif (preg_match('%vimeo\.com/(?:video/)?(\d+)%i', $url, $matches)) {
            $id = $matches[1];
            $html = '<iframe width="560" height="315" src="https://player.vimeo.com/video/' . htmlspecialchars($id) . '" title="Vimeo video player" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
        }

        return $html;
    }
}
