<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLog;

class AdminAuditController extends Controller
{
    public function index(): void
    {
        $page = $this->page();
        $perPage = config_val('pagination.per_page_admin', 50);
        $pager = AuditLog::paginate($page, $perPage, [], 'created_at', 'DESC');

        // Unir con nombre de usuario
        foreach ($pager['items'] as &$row) {
            if (!empty($row['user_id'])) {
                $row += ['user_name' => \App\Models\User::queryScalar('SELECT name FROM users WHERE id = ?', [(int)$row['user_id']]) ?: '-'];
            } else {
                $row['user_name'] = '-';
            }
        }
        unset($row);

        $this->view('admin.audit.index', [
            'metaTitle' => 'Historial de auditoría | Admin',
            'pager'     => $pager,
        ]);
    }
}
