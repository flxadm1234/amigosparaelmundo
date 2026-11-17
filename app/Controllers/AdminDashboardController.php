<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\DB;
use App\Core\View;
use App\Models\News;
use App\Models\Video;
use App\Models\RadioProgram;
use App\Models\User;
use App\Models\AuditLog;

class AdminDashboardController extends Controller
{
    public function index(): void
    {
        $totalNews       = (int)(class_exists(News::class) ? News::countWhere([]) : 0);
        $publishedNews   = (int)(class_exists(News::class) ? News::countWhere([['is_published', '=', 1]]) : 0);
        $draftNews       = max(0, $totalNews - $publishedNews);
        $publishedVideos = (int)(class_exists(Video::class) ? Video::countWhere([['is_published', '=', 1]]) : 0);
        $totalVideos     = (int)(class_exists(Video::class) ? Video::countWhere([]) : 0);
        $activePrograms  = (int)(class_exists(RadioProgram::class) ? RadioProgram::countWhere([['is_active', '=', 1]]) : 0);
        $activeUsers = 0;
        if (class_exists(User::class) && method_exists(User::class, 'countWhere')) {
            try {
                $activeUsers = (int)User::countWhere([['is_active', '=', 1]]);
            } catch (\Throwable $e) {
                try {
                    $activeUsers = (int)User::countWhere([['status', '=', 1]]);
                } catch (\Throwable $e2) {
                    $activeUsers = 0;
                }
            }
        }

        $recentAudit = class_exists(AuditLog::class) && method_exists(AuditLog::class, 'recent')
            ? AuditLog::recent(10)
            : [];

        if (class_exists(News::class) && method_exists(News::class, 'lastPublished')) {
            $recentNews = News::lastPublished(10);
        } else {
            try {
                $recentNews = DB::queryRows(
                    'SELECT n.*, s.name AS section_name
                     FROM news n LEFT JOIN sections s ON s.id = n.section_id
                     ORDER BY n.updated_at DESC, n.id DESC LIMIT 10'
                );
            } catch (\Throwable $e) {
                $recentNews = [];
            }
        }

        try {
            $chartData = DB::queryRows(
                "SELECT DATE_FORMAT(publish_date, '%Y-%m') AS ym, COUNT(*) AS cnt
                 FROM news
                 WHERE publish_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                   AND is_published = 1
                 GROUP BY DATE_FORMAT(publish_date, '%Y-%m') ORDER BY ym ASC"
            );
        } catch (\Throwable $e) {
            $chartData = [];
        }

        $kpiCards = [
            [
                'title'  => 'Noticias',
                'value'  => $totalNews,
                'icon'   => 'newspaper',
                'accent' => '#087B43',
                'hint'   => 'Ver todas',
                'href'   => '/admin/noticias',
            ],
            [
                'title'  => 'Publicadas',
                'value'  => $publishedNews,
                'icon'   => 'check-circle-2',
                'accent' => '#16A34A',
                'hint'   => 'Visibles en el sitio',
                'href'   => '/admin/noticias?estado=published',
            ],
            [
                'title'  => 'Borradores',
                'value'  => $draftNews,
                'icon'   => 'file-edit',
                'accent' => '#F59E0B',
                'hint'   => 'Pendientes de publicar',
                'href'   => '/admin/noticias?estado=draft',
            ],
            [
                'title'  => 'Programas de impacto',
                'value'  => $activePrograms,
                'icon'   => 'globe-2',
                'accent' => '#0D9488',
                'hint'   => 'Activos en la portada',
                'href'   => '/admin/radio/programas',
            ],
            [
                'title'  => 'Videos publicados',
                'value'  => $publishedVideos . ' / ' . $totalVideos,
                'icon'   => 'video',
                'accent' => '#0891B2',
                'hint'   => 'Publicados de totales',
                'href'   => '/admin/videos',
            ],
            [
                'title'  => 'Usuarios activos',
                'value'  => $activeUsers,
                'icon'   => 'users',
                'accent' => '#6D28D9',
                'hint'   => 'Con acceso al panel',
                'href'   => '/admin/usuarios',
            ],
        ];

        View::partial('admin/head', [
            'metaTitle' => 'Dashboard | Admin ' . app_setting('site_name', 'AMIGOS POR EL MUNDO'),
        ]);
        View::partial('admin/sidebar');

        $viewsDir = dirname(__DIR__, 2) . '/views';
        include $viewsDir . '/admin/dashboard/index.php';

        View::partial('admin/footer');
    }
}
