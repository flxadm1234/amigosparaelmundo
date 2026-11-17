<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Auth;

class AuditLog extends Model
{
    protected static string $table = 'audit_logs';

    public static function record(string $action, ?string $entity, ?string $entity_id, ?string $metadata = null, ?int $userId = null): void
    {
        try {
            if ($userId === null) {
                $user = Auth::user();
                $userId = $user ? (int)$user['id'] : null;
            }
            $ip = method_exists(Auth::class, 'ip') ? Auth::ip() : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
            self::create([
                'user_id'       => $userId,
                'action'        => $action,
                'entity'        => $entity,
                'entity_id'     => $entity_id,
                'ip_address'    => $ip,
                'metadata_text' => $metadata,
            ]);
        } catch (\Throwable $e) {
            // Auditoría no debe romper el flujo principal
            error_log('AUDIT ERROR: ' . $e->getMessage());
        }
    }

    public static function recordWithBeforeAfter(string $action, ?string $entity, ?string $entityId, ?array $oldData, ?array $newData, ?string $metadata = null, ?int $userId = null): void
    {
        try {
            if ($userId === null) {
                $user = Auth::user();
                $userId = $user ? (int)$user['id'] : null;
            }
            $ip = method_exists(Auth::class, 'ip') ? Auth::ip() : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

            $oldJson = $oldData !== null ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null;
            $newJson = $newData !== null ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null;

            self::create([
                'user_id'       => $userId,
                'action'        => $action,
                'entity'        => $entity,
                'entity_id'     => $entityId,
                'ip_address'    => $ip,
                'metadata_text' => $metadata,
                'old_data'      => $oldJson,
                'new_data'      => $newJson,
            ]);
        } catch (\Throwable $e) {
            // Auditoría no debe romper el flujo principal
            error_log('AUDIT ERROR: ' . $e->getMessage());
        }
    }

    public static function recent(int $limit = 50): array
    {
        return self::queryRows(
            'SELECT a.*, u.name AS user_name, u.email AS user_email
             FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC, a.id DESC LIMIT ' . (int)$limit
        );
    }
}
