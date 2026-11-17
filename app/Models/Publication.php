<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\DB;

class Publication extends Model
{
    protected static string $table = 'publications';

    public static function findPublishedBySlug(string $slug): ?array
    {
        $row = self::queryOne(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                    r.name AS region_name, r.slug AS region_slug
             FROM publications p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN regions r    ON r.id = p.region_id
             WHERE p.slug = ? AND p.is_published = 1',
            [$slug]
        );
        return $row ?: null;
    }

    public static function withDetails(int $id): ?array
    {
        return self::queryOne(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                    r.name AS region_name, r.slug AS region_slug
             FROM publications p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN regions r    ON r.id = p.region_id
             WHERE p.id = ?',
            [$id]
        );
    }

    public static function tagsFor(int $publicationId): array
    {
        return self::queryRows(
            'SELECT t.id, t.name, t.slug FROM publication_tags pt
             INNER JOIN tags t ON t.id = pt.tag_id
             WHERE pt.publication_id = ? ORDER BY t.name ASC',
            [$publicationId]
        );
    }

    public static function syncTags(int $publicationId, array $tagIds): void
    {
        $tagIds = array_unique(array_map('intval', $tagIds));
        $pdo = DB::connection();
        $pdo->prepare('DELETE FROM publication_tags WHERE publication_id = ?')->execute([$publicationId]);
        if (count($tagIds) === 0) {
            return;
        }
        $sql = 'INSERT INTO publication_tags (publication_id, tag_id) VALUES ';
        $values = [];
        $params = [];
        foreach ($tagIds as $tid) {
            $values[] = '(?, ?)';
            $params[] = $publicationId;
            $params[] = $tid;
        }
        $sql .= implode(',', $values);
        $pdo->prepare($sql)->execute($params);
    }

    public static function attachments(int $publicationId, string $kind = 'attachment'): array
    {
        return self::queryRows(
            'SELECT * FROM media_files WHERE news_id = ? AND file_kind = ? ORDER BY sort_order ASC, id ASC',
            [$publicationId, $kind]
        );
    }

    public static function gallery(int $publicationId): array
    {
        return self::attachments($publicationId, 'gallery');
    }

    /**
     * Devuelve la PRIMERA imagen de galería (stored_path) de una publicación,
     * o null si no tiene galería. Útil para portadas de cards en listados.
     */
    public static function firstGalleryImage(int $publicationId): ?array
    {
        $row = self::queryOne(
            'SELECT id, original_name, stored_path, mime_type FROM media_files
             WHERE news_id = ? AND file_kind = ?
             ORDER BY sort_order ASC, id ASC',
            [$publicationId, 'gallery']
        );
        return $row ?: null;
    }

    public static function attachmentsCount(int $publicationId): int
    {
        return (int)self::queryScalar(
            'SELECT COUNT(*) FROM media_files WHERE news_id = ?',
            [$publicationId]
        );
    }

    public static function availableYears(): array
    {
        $rows = self::queryRows(
            'SELECT DISTINCT year FROM publications 
             WHERE year IS NOT NULL AND year >= 1990 AND year <= YEAR(CURDATE()) + 1 
             ORDER BY year DESC'
        );
        $out = [];
        foreach ($rows as $r) {
            $y = (int)($r['year'] ?? 0);
            if ($y >= 1990) $out[] = $y;
        }
        return $out;
    }

    public static function lastPublished(int $limit = 6): array
    {
        return self::queryRows(
            'SELECT p.*, c.name AS category_name, r.name AS region_name
             FROM publications p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN regions r    ON r.id = p.region_id
             WHERE p.is_published = 1
             ORDER BY p.publish_date DESC, p.id DESC
             LIMIT ' . (int)$limit
        );
    }

    /**
     * Búsqueda + filtros público (solo publicados)
     */
    public static function searchPublic(array $filters, int $page, int $perPage): array
    {
        $where = [['p.is_published', '=', 1]];
        $joins = '';
        $distinct = false;

        if (!empty($filters['year'])) {
            $where[] = ['p.year', '=', (int)$filters['year']];
        }
        if (!empty($filters['category_id'])) {
            $where[] = ['p.category_id', '=', (int)$filters['category_id']];
        }
        if (!empty($filters['region_id'])) {
            $where[] = ['p.region_id', '=', (int)$filters['region_id']];
        }
        if (!empty($filters['tag_id'])) {
            $joins .= ' INNER JOIN publication_tags pt ON pt.publication_id = p.id';
            $where[] = ['pt.tag_id', '=', (int)$filters['tag_id']];
            $distinct = true;
        }
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $where[] = ['AND (p.title LIKE ? OR p.excerpt LIKE ? OR p.content_html LIKE ?)', null, [$q, $q, $q]];
        }

        $sqlBase = ' FROM publications p
                     LEFT JOIN categories c ON c.id = p.category_id
                     LEFT JOIN regions r    ON r.id = p.region_id
                     ' . $joins . ' ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sqlBase .= ' WHERE ' . $whereSql;
        }
        $order = !empty($filters['order']) && $filters['order'] === 'old' ? 'ASC' : 'DESC';

        // Total
        $countSql = 'SELECT COUNT(' . ($distinct ? 'DISTINCT p.id' : '*') . ') AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        // Rows
        $sel = 'SELECT ' . ($distinct ? 'DISTINCT ' : '') .
               'p.*, c.name AS category_name, c.slug AS category_slug, r.name AS region_name, r.slug AS region_slug';
        $sql = $sel . $sqlBase . " ORDER BY p.publish_date {$order}, p.id {$order}";
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

    /**
     * Búsqueda admin (todos, con filtros)
     */
    public static function searchAdmin(array $filters, int $page, int $perPage): array
    {
        $where = [];
        if (array_key_exists('is_published', $filters) && $filters['is_published'] !== '' && $filters['is_published'] !== null) {
            $where[] = ['p.is_published', '=', (int)$filters['is_published']];
        }
        if (!empty($filters['year'])) {
            $where[] = ['p.year', '=', (int)$filters['year']];
        }
        if (!empty($filters['category_id'])) {
            $where[] = ['p.category_id', '=', (int)$filters['category_id']];
        }
        if (!empty($filters['region_id'])) {
            $where[] = ['p.region_id', '=', (int)$filters['region_id']];
        }
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $where[] = ['AND (p.title LIKE ? OR p.slug LIKE ?)', null, [$q, $q]];
        }

        $sqlBase = ' FROM publications p
                     LEFT JOIN categories c ON c.id = p.category_id
                     LEFT JOIN regions r    ON r.id = p.region_id ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sqlBase .= ' WHERE ' . $whereSql;
        }
        $order = !empty($filters['order']) && $filters['order'] === 'old' ? 'ASC' : 'DESC';
        $countSql = 'SELECT COUNT(*) AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $sql = 'SELECT p.*, c.name AS category_name, r.name AS region_name ' . $sqlBase .
               " ORDER BY p.publish_date {$order}, p.id {$order}";
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
        self::execute('UPDATE publications SET views_count = views_count + 1 WHERE id = ?', [$id]);
    }
}
