<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    protected static function table(): string
    {
        return static::$table;
    }

    public static function find(int|string $id): ?array
    {
        $pdo = DB::connection();
        $sth = $pdo->prepare('SELECT * FROM `' . static::table() . '` WHERE `' . static::$primaryKey . '` = ? LIMIT 1');
        $sth->execute([$id]);
        $row = $sth->fetch();
        return $row ?: null;
    }

    public static function findBy(string $column, mixed $value, string $op = '='): ?array
    {
        $pdo = DB::connection();
        $sth = $pdo->prepare('SELECT * FROM `' . static::table() . "` WHERE `{$column}` {$op} ? LIMIT 1");
        $sth->execute([$value]);
        $row = $sth->fetch();
        return $row ?: null;
    }

    public static function findAllBy(string $column, mixed $value, string $op = '='): array
    {
        $pdo = DB::connection();
        $sth = $pdo->prepare('SELECT * FROM `' . static::table() . "` WHERE `{$column}` {$op} ? ORDER BY `id` DESC");
        $sth->execute([$value]);
        return $sth->fetchAll();
    }

    public static function all(string $orderBy = '', string $dir = 'DESC'): array
    {
        $pdo = DB::connection();
        $sql = 'SELECT * FROM `' . static::table() . '`';
        if ($orderBy !== '') {
            $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
            $sql .= " ORDER BY `{$orderBy}` {$dir}";
        }
        return $pdo->query($sql)->fetchAll();
    }

    public static function existsBy(string $column, mixed $value, ?int $excludeId = null): bool
    {
        $pdo = DB::connection();
        $sql = 'SELECT COUNT(*) AS c FROM `' . static::table() . "` WHERE `{$column}` = ?";
        $params = [$value];
        if ($excludeId !== null) {
            $sql .= ' AND `' . static::$primaryKey . '` <> ?';
            $params[] = $excludeId;
        }
        $sth = $pdo->prepare($sql);
        $sth->execute($params);
        return ((int)$sth->fetchColumn()) > 0;
    }

    public static function create(array $data): int
    {
        $pdo = DB::connection();
        $fields = array_keys($data);
        $columns = '`' . implode('`, `', $fields) . '`';
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $sql = 'INSERT INTO `' . static::table() . "` ({$columns}) VALUES ({$placeholders})";
        $sth = $pdo->prepare($sql);
        $sth->execute(array_values($data));
        return (int)$pdo->lastInsertId();
    }

    public static function update(int|string $id, array $data): int
    {
        if (count($data) === 0) {
            return 0;
        }
        $pdo = DB::connection();
        $fields = array_keys($data);
        $set = [];
        foreach ($fields as $f) {
            $set[] = "`{$f}` = ?";
        }
        $sql = 'UPDATE `' . static::table() . '` SET ' . implode(', ', $set) . ' WHERE `' . static::$primaryKey . '` = ?';
        $sth = $pdo->prepare($sql);
        $values = array_values($data);
        $values[] = $id;
        $sth->execute($values);
        return $sth->rowCount();
    }

    public static function delete(int|string $id): int
    {
        $pdo = DB::connection();
        $sth = $pdo->prepare('DELETE FROM `' . static::table() . '` WHERE `' . static::$primaryKey . '` = ?');
        $sth->execute([$id]);
        return $sth->rowCount();
    }

    /**
     * @param array $where [ ['col', 'op', 'val'], ... ]
     */
    public static function where(array $where = [], string $orderBy = '', string $dir = 'DESC', ?int $limit = null): array
    {
        $pdo = DB::connection();
        $sql = 'SELECT * FROM `' . static::table() . '`';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }
        if ($orderBy !== '') {
            $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
            $sql .= " ORDER BY `{$orderBy}` {$dir}";
        }
        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int)$limit;
        }
        $sth = $pdo->prepare($sql);
        $sth->execute($params);
        return $sth->fetchAll();
    }

    public static function countWhere(array $where = []): int
    {
        $pdo = DB::connection();
        $sql = 'SELECT COUNT(*) AS c FROM `' . static::table() . '`';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }
        $sth = $pdo->prepare($sql);
        $sth->execute($params);
        return (int)$sth->fetchColumn();
    }

    public static function paginate(int $page, int $perPage, array $where = [], string $orderBy = '', string $dir = 'DESC'): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $total = self::countWhere($where);
        $pdo = DB::connection();
        $sql = 'SELECT * FROM `' . static::table() . '`';
        [$whereSql, $params] = self::buildWhere($where);
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }
        if ($orderBy !== '') {
            $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
            $sql .= " ORDER BY `{$orderBy}` {$dir}";
        }
        $sql .= ' LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;
        $sth = $pdo->prepare($sql);
        $sth->execute($params);
        $items = $sth->fetchAll();
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
     * Soporte simple para WHERE custom.
     *   $where = [
     *     ['col1','=','a'],
     *     ['col2','>','2020-01-01'],
     *     ['AND (col3 IS NULL OR col4=?)', null, [1]]   => raw con parámetros
     *     ['col','IN', [1,2,3]],
     *     ['col','LIKE', '%foo%'],
     *   ]
     */
    protected static function buildWhere(array $where): array
    {
        $sql = '';
        $params = [];
        $parts = [];
        $q = static function (string $col): string {
            $bits = explode('.', $col);
            $out = [];
            foreach ($bits as $b) {
                $b = trim($b);
                if ($b === '' || $b === '*') { $out[] = $b; continue; }
                if (preg_match('/[`"\[]/', $b)) { $out[] = $b; continue; }
                $out[] = "`{$b}`";
            }
            return implode('.', $out);
        };
        foreach ($where as $w) {
            if (!is_array($w) || count($w) < 3) {
                continue;
            }
            [$col, $op, $val] = $w;
            if ($op === null && str_starts_with($col, 'AND')) {
                $parts[] = ' ' . substr($col, 3) . ' ';
                if (is_array($val)) {
                    $params = array_merge($params, $val);
                }
                continue;
            }
            $op = strtoupper($op);
            if ($op === 'IN' && is_array($val)) {
                $ph = implode(',', array_fill(0, count($val), '?'));
                $parts[] = $q($col) . " IN ({$ph})";
                $params = array_merge($params, array_values($val));
            } elseif ($op === 'LIKE') {
                $parts[] = $q($col) . " LIKE ?";
                $params[] = $val;
            } else {
                $parts[] = $q($col) . " {$op} ?";
                $params[] = $val;
            }
        }
        if (count($parts)) {
            $sql = implode(' AND ', $parts);
        }
        return [$sql, $params];
    }

    /**
     * Ejecuta SQL raw con prepared params (solo lectura)
     */
    public static function queryRows(string $sql, array $params = []): array
    {
        $pdo = DB::connection();
        $sth = $pdo->prepare($sql);
        $sth->execute($params);
        return $sth->fetchAll();
    }

    public static function queryOne(string $sql, array $params = []): ?array
    {
        $trimmed = rtrim($sql, "; \t\n\r\0\x0B");
        $hasLimit = preg_match('/\sLIMIT\s+\d+/i', $trimmed) === 1;
        $finalSql = $hasLimit ? $sql : $sql . ' LIMIT 1';
        $rows = self::queryRows($finalSql, $params);
        return $rows[0] ?? null;
    }

    public static function queryScalar(string $sql, array $params = []): mixed
    {
        $pdo = DB::connection();
        $sth = $pdo->prepare($sql);
        $sth->execute($params);
        return $sth->fetchColumn();
    }

    public static function execute(string $sql, array $params = []): int
    {
        $pdo = DB::connection();
        $sth = $pdo->prepare($sql);
        $sth->execute($params);
        return $sth->rowCount();
    }
}
