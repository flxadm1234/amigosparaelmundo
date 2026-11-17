<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\DB;

class Ad extends Model
{
    protected static string $table = 'ads';

    public const POSITIONS = [
        'top-banner'     => 'Banner Superior (Debajo del Nav)',
        'bottom-sticky'  => 'Banner Inferior Fijo (Sticky)',
        'sidebar-right'  => 'Sidebar Derecho',
        'interstitial'   => 'Intersticial (pantalla completa)',
        'news-inline'    => 'Dentro de Noticia (Inline)',
        'section-top'    => 'Top de Sección',
        'sidebar-left'   => 'Sidebar Izquierdo',
    ];

    public const KINDS = [
        'image'   => 'Imagen con enlace',
        'html'    => 'Código HTML personalizado',
    ];

    public static function findActiveById(int $id): ?array
    {
        $row = self::queryOne('SELECT * FROM ads WHERE id = ?', [$id]);
        return $row ?: null;
    }

    public static function adminSearch(array $filters, int $page, int $perPage): array
    {
        $where = [];
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $where[] = ['AND (a.title LIKE ? OR a.link_url LIKE ?)', null, [$q, $q]];
        }
        if (!empty($filters['position'])) {
            $where[] = ['a.position', '=', $filters['position']];
        }
        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $where[] = ['a.is_active', '=', (int)$filters['is_active']];
        }

        $sqlBase = ' FROM ads a ';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') $sqlBase .= ' WHERE ' . $whereSql;

        $countSql = 'SELECT COUNT(*) AS c' . $sqlBase;
        $total = (int)self::queryScalar($countSql, $params);

        $sql = 'SELECT a.* ' . $sqlBase . ' ORDER BY a.sort_order ASC, a.id DESC';
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $sql .= ' LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;
        $items = self::queryRows($sql, $params);

        return [
            'items' => $items,
            'total' => $total,
            'page'  => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($total / max(1, $perPage)),
            'from'  => $total === 0 ? 0 : $offset + 1,
            'to'    => min($offset + $perPage, $total),
        ];
    }

    public static function getActiveByPosition(string $position): array
    {
        $now = date('Y-m-d H:i:s');
        $sql = 'SELECT * FROM ads
                WHERE position = ?
                  AND is_active = 1
                  AND (start_at IS NULL OR start_at <= ?)
                  AND (end_at IS NULL OR end_at >= ?)
                ORDER BY sort_order ASC, id ASC';
        $rows = self::queryRows($sql, [$position, $now, $now]);
        $out = [];
        foreach ($rows as $r) {
            $max = (int)($r['max_views'] ?? 0);
            if ($max > 0 && (int)$r['views_count'] >= $max) continue;
            $out[] = $r;
        }
        return $out;
    }

    public static function incrementViews(int $id): void
    {
        self::execute('UPDATE ads SET views_count = views_count + 1 WHERE id = ?', [$id]);
    }

    public static function incrementClicks(int $id): void
    {
        self::execute('UPDATE ads SET clicks_count = clicks_count + 1 WHERE id = ?', [$id]);
    }
}
