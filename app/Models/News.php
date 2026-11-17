<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\DB;

class News extends Model
{
    protected static string $table = 'news';

    public static function findPublishedBySlug(string $slug): ?array
    {
        $row = self::queryOne(
            'SELECT n.*, s.name AS section_name, s.slug AS section_slug
             FROM news n
             LEFT JOIN sections s ON s.id = n.section_id
             WHERE n.slug = ? AND n.is_published = 1',
            [$slug]
        );
        return $row ?: null;
    }

    public static function withDetails(int $id): ?array
    {
        return self::queryOne(
            'SELECT n.*, s.name AS section_name, s.slug AS section_slug
             FROM news n
             LEFT JOIN sections s ON s.id = n.section_id
             WHERE n.id = ?',
            [$id]
        );
    }

    public static function tagsFor(int $newsId): array
    {
        return self::queryRows(
            'SELECT t.id, t.name, t.slug FROM news_tags nt
             INNER JOIN tags t ON t.id = nt.tag_id
             WHERE nt.news_id = ? ORDER BY t.name ASC',
            [$newsId]
        );
    }

    public static function syncTags(int $newsId, array $tagIds): void
    {
        $tagIds = array_unique(array_map('intval', $tagIds));
        $pdo = DB::connection();
        $pdo->prepare('DELETE FROM news_tags WHERE news_id = ?')->execute([$newsId]);
        if (count($tagIds) === 0) {
            return;
        }
        $sql = 'INSERT INTO news_tags (news_id, tag_id) VALUES ';
        $values = [];
        $params = [];
        foreach ($tagIds as $tid) {
            $values[] = '(?, ?)';
            $params[] = $newsId;
            $params[] = $tid;
        }
        $sql .= implode(',', $values);
        $pdo->prepare($sql)->execute($params);
    }

    public static function gallery(int $newsId): array
    {
        return self::queryRows(
            'SELECT * FROM media_files WHERE news_id = ? AND file_kind = ? ORDER BY sort_order ASC, id ASC',
            [$newsId, 'gallery']
        );
    }

    public static function attachments(int $newsId, string $kind = 'attachment'): array
    {
        return self::queryRows(
            'SELECT * FROM media_files WHERE news_id = ? AND file_kind = ? ORDER BY sort_order ASC, id ASC',
            [$newsId, $kind]
        );
    }

    public static function lastPublished(int $limit = 10): array
    {
        return self::queryRows(
            'SELECT n.*, s.name AS section_name, s.slug AS section_slug
             FROM news n
             LEFT JOIN sections s ON s.id = n.section_id
             WHERE n.is_published = 1
             ORDER BY n.publish_date DESC, n.id DESC
             LIMIT ' . (int)$limit
        );
    }

    public static function featuredNews(int $limit = 6): array
    {
        return self::queryRows(
            'SELECT n.*, s.name AS section_name, s.slug AS section_slug
             FROM news n
             LEFT JOIN sections s ON s.id = n.section_id
             WHERE n.is_featured = 1 AND n.is_published = 1
             ORDER BY n.publish_date DESC, n.id DESC
             LIMIT ' . (int)$limit
        );
    }

    public static function breakingNews(int $limit = 5): array
    {
        return self::queryRows(
            'SELECT n.*, s.name AS section_name, s.slug AS section_slug
             FROM news n
             LEFT JOIN sections s ON s.id = n.section_id
             WHERE n.is_breaking = 1 AND n.is_published = 1
             ORDER BY n.publish_date DESC, n.id DESC
             LIMIT ' . (int)$limit
        );
    }

    public static function bySectionId(int $sectionId, int $page, int $perPage): array
    {
        $where = [['n.is_published', '=', 1], ['n.section_id', '=', $sectionId]];

        $sqlBase = ' FROM news n
                     LEFT JOIN sections s ON s.id = n.section_id ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sqlBase .= ' WHERE ' . $whereSql;
        }

        $countSql = 'SELECT COUNT(*) AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $sql = 'SELECT n.*, s.name AS section_name, s.slug AS section_slug ' . $sqlBase .
               ' ORDER BY n.publish_date DESC, n.id DESC';
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
        $where = [['n.is_published', '=', 1]];
        $joins = '';
        $distinct = false;

        if (!empty($filters['section_id'])) {
            $where[] = ['n.section_id', '=', (int)$filters['section_id']];
        }
        if (!empty($filters['tag_id'])) {
            $joins .= ' INNER JOIN news_tags nt ON nt.news_id = n.id';
            $where[] = ['nt.tag_id', '=', (int)$filters['tag_id']];
            $distinct = true;
        }
        if (!empty($filters['date_from'])) {
            $where[] = ['n.publish_date', '>=', $filters['date_from']];
        }
        if (!empty($filters['date_to'])) {
            $where[] = ['n.publish_date', '<=', $filters['date_to']];
        }
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $where[] = ['AND (n.title LIKE ? OR n.excerpt LIKE ? OR n.content_html LIKE ?)', null, [$q, $q, $q]];
        }

        $sqlBase = ' FROM news n
                     LEFT JOIN sections s ON s.id = n.section_id
                     ' . $joins . ' ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sqlBase .= ' WHERE ' . $whereSql;
        }
        $order = !empty($filters['order']) && $filters['order'] === 'old' ? 'ASC' : 'DESC';

        $countSql = 'SELECT COUNT(' . ($distinct ? 'DISTINCT n.id' : '*') . ') AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $sel = 'SELECT ' . ($distinct ? 'DISTINCT ' : '') .
               'n.*, s.name AS section_name, s.slug AS section_slug';
        $sql = $sel . $sqlBase . " ORDER BY n.publish_date {$order}, n.id {$order}";
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
            $where[] = ['n.is_published', '=', (int)$filters['is_published']];
        }
        if (!empty($filters['section_id'])) {
            $where[] = ['n.section_id', '=', (int)$filters['section_id']];
        }
        if (array_key_exists('is_featured', $filters) && $filters['is_featured'] !== '' && $filters['is_featured'] !== null) {
            $where[] = ['n.is_featured', '=', (int)$filters['is_featured']];
        }
        if (array_key_exists('is_breaking', $filters) && $filters['is_breaking'] !== '' && $filters['is_breaking'] !== null) {
            $where[] = ['n.is_breaking', '=', (int)$filters['is_breaking']];
        }
        if (!empty($filters['created_by'])) {
            $where[] = ['n.created_by', '=', (int)$filters['created_by']];
        }
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $where[] = ['AND (n.title LIKE ? OR n.slug LIKE ? OR n.excerpt LIKE ?)', null, [$q, $q, $q]];
        }

        $sqlBase = ' FROM news n
                     LEFT JOIN sections s ON s.id = n.section_id ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sqlBase .= ' WHERE ' . $whereSql;
        }
        $order = !empty($filters['order']) && $filters['order'] === 'old' ? 'ASC' : 'DESC';
        $countSql = 'SELECT COUNT(*) AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $sql = 'SELECT n.*, s.name AS section_name ' . $sqlBase .
               " ORDER BY n.publish_date {$order}, n.id {$order}";
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
        self::execute('UPDATE news SET views_count = views_count + 1 WHERE id = ?', [$id]);
    }

    public static function related(int $newsId, int $sectionId, int $limit = 4): array
    {
        return self::queryRows(
            'SELECT n.*, s.name AS section_name, s.slug AS section_slug
             FROM news n
             LEFT JOIN sections s ON s.id = n.section_id
             WHERE n.is_published = 1 AND n.section_id = ? AND n.id != ?
             ORDER BY n.publish_date DESC, n.id DESC
             LIMIT ' . (int)$limit,
            [$sectionId, $newsId]
        );
    }

    public static function existsBy(string $field, mixed $value, ?int $excludeId = null): bool
    {
        $allowed = ['id', 'slug', 'title'];
        if (!in_array($field, $allowed, true)) {
            return false;
        }
        $sql = 'SELECT COUNT(*) AS c FROM news WHERE ' . $field . ' = ?';
        $params = [$value];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        return (int)self::queryScalar($sql, $params) > 0;
    }

    public static function availableYears(): array
    {
        return self::queryRows(
            'SELECT DISTINCT CAST(YEAR(publish_date) AS UNSIGNED) AS yr
             FROM news
             WHERE publish_date IS NOT NULL AND is_published = 1
             ORDER BY yr DESC'
        );
    }

    public static function cover(int $id): ?array
    {
        $row = self::queryOne('SELECT cover_image_path, cover_caption FROM news WHERE id = ? LIMIT 1', [$id]);
        if (empty($row) || empty($row['cover_image_path'])) {
            return null;
        }
        $storedPath = (string)($row['cover_image_path'] ?? '');
        $ext = strtolower((string)(pathinfo($storedPath, PATHINFO_EXTENSION) ?: ''));
        return [
            'id'            => 0,
            'file_kind'     => 'cover',
            'file_label'    => (string)($row['cover_caption'] ?? ''),
            'original_name' => basename($storedPath),
            'stored_name'   => basename($storedPath),
            'stored_path'   => $storedPath,
            'mime_type'     => null,
            'size_bytes'    => null,
            'file_ext'      => $ext !== '' ? $ext : null,
            'news_id'       => $id,
        ];
    }
}
