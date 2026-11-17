<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Core\Session;
use App\Core\Auth;
use App\Models\Section;
use App\Models\AuditLog;

class AdminSectionsController extends Controller
{
    public function index(): void
    {
        Auth::requireCan('sections');

        $filters = [
            'q'            => trim((string)($_GET['q'] ?? '')),
            'section_type' => trim((string)($_GET['section_type'] ?? '')),
            'is_featured'  => !isset($_GET['is_featured']) || $_GET['is_featured'] === '' ? null : (string)$_GET['is_featured'],
            'is_active'    => !isset($_GET['is_active'])    || $_GET['is_active']    === '' ? null : (string)$_GET['is_active'],
        ];

        $all = Section::all('sort_order', 'ASC');
        $filtered = [];
        foreach ($all as $s) {
            if ($filters['q'] !== '' && mb_stripos($s['name'] . ' ' . ($s['description'] ?? '') . ' ' . ($s['slug'] ?? ''), $filters['q']) === false) {
                continue;
            }
            if ($filters['section_type'] !== '' && ($s['section_type'] ?? 'news') !== $filters['section_type']) {
                continue;
            }
            if ($filters['is_featured'] !== null && (string)(int)($s['is_featured'] ?? 0) !== $filters['is_featured']) {
                continue;
            }
            if ($filters['is_active'] !== null && (string)(int)($s['is_active'] ?? 1) !== $filters['is_active']) {
                continue;
            }
            $filtered[] = $s;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = (int)config_val('pagination.per_page_admin', 50);
        $total = count($filtered);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($filtered, $offset, $perPage);

        $paginator = [
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int)ceil($total / max(1, $perPage)),
            'from'        => $total === 0 ? 0 : $offset + 1,
            'to'          => min($offset + $perPage, $total),
        ];

        $this->view('admin.sections.index', [
            'metaTitle' => 'Secciones | Admin',
            'filters'   => $filters,
            'paginator' => $paginator,
        ]);
    }

    public function create(): void
    {
        Auth::requireCan('sections.create');

        $this->view('admin.sections.form', [
            'metaTitle' => 'Nueva sección',
            'item'      => null,
        ]);
    }

    public function store(): void
    {
        Auth::requireCan('sections.create');

        $data = [
            'name'         => trim((string)($_POST['name'] ?? '')),
            'slug'         => trim((string)($_POST['slug'] ?? '')),
            'description'  => trim((string)($_POST['description'] ?? '')),
            'section_type' => trim((string)($_POST['section_type'] ?? 'news')),
            'icon'         => trim((string)($_POST['icon'] ?? '')),
            'color'        => trim((string)($_POST['color'] ?? '')),
            'is_featured'  => isset($_POST['is_featured']) ? 1 : 0,
            'sort_order'   => (int)($_POST['sort_order'] ?? 0),
            'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['name']);
        }
        $v = new Validator($data);
        if (!$v->validate([
            'name' => 'required|minLength:2|maxLength:100',
            'slug' => 'required|slug|maxLength:120',
        ])) {
            $this->withErrors($v->errors(), '/admin/secciones/crear');
        }
        if (Section::existsBy('slug', $data['slug'])) {
            $this->withError('Slug ya existe.', '/admin/secciones/crear');
        }
        $id = Section::create($data);
        AuditLog::record('create', 'sections', (string)$id, json_encode($data, JSON_UNESCAPED_UNICODE));
        $this->withSuccess('Sección creada.', '/admin/secciones');
    }

    public function edit(array $params): void
    {
        Auth::requireCan('sections.edit');

        $id = (int)($params['id'] ?? 0);
        $section = Section::find($id);
        if (!$section) {
            $this->withError('Sección no encontrada.', '/admin/secciones');
        }
        $this->view('admin.sections.form', [
            'metaTitle' => 'Editar sección',
            'item'      => $section,
        ]);
    }

    public function update(array $params): void
    {
        Auth::requireCan('sections.edit');

        $id = (int)($params['id'] ?? 0);
        $section = Section::find($id);
        if (!$section) {
            $this->withError('Sección no encontrada.', '/admin/secciones');
        }
        $before = json_encode($section, JSON_UNESCAPED_UNICODE);

        $data = [
            'name'         => trim((string)($_POST['name'] ?? '')),
            'slug'         => trim((string)($_POST['slug'] ?? '')),
            'description'  => trim((string)($_POST['description'] ?? '')),
            'section_type' => trim((string)($_POST['section_type'] ?? 'news')),
            'icon'         => trim((string)($_POST['icon'] ?? '')),
            'color'        => trim((string)($_POST['color'] ?? '')),
            'is_featured'  => isset($_POST['is_featured']) ? 1 : 0,
            'sort_order'   => (int)($_POST['sort_order'] ?? 0),
            'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['name']);
        }
        if (Section::existsBy('slug', $data['slug'], $id)) {
            $this->withError('Slug ya usado por otra sección.', '/admin/secciones/' . $id . '/editar');
        }
        Section::update($id, $data);
        AuditLog::record('update', 'sections', (string)$id, json_encode([
            'before' => json_decode($before, true),
            'after'  => $data,
        ], JSON_UNESCAPED_UNICODE));
        $this->withSuccess('Sección actualizada.', '/admin/secciones');
    }

    public function toggle(array $params): void
    {
        Auth::requireCan('sections.edit');

        $id = (int)($params['id'] ?? 0);
        $redirectUrl = '/admin/secciones';
        $entityName  = 'sections';
        $entityLabel = 'Sección';

        try {
            $section = Section::find($id);
            if (!$section) {
                Session::flash('error', "{$entityLabel} no encontrada.");
                redirect($redirectUrl, 303);
            }
            $before = json_encode($section, JSON_UNESCAPED_UNICODE);
            $user = Auth::user();
            $userId = (int)($user['id'] ?? 0);

            $newVal = ((int)($section['is_active'] ?? 0)) === 1 ? 0 : 1;
            Section::update($id, ['is_active' => $newVal]);

            $auditData = [
                'before'       => json_decode($before, true),
                'after'        => ['is_active' => $newVal],
                'updated_by'   => $userId,
                'updated_ip'   => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ];
            AuditLog::record('update', $entityName, (string)$id, json_encode($auditData, JSON_UNESCAPED_UNICODE));

            Session::flash(
                'success',
                $newVal === 1
                    ? "✅ {$entityLabel} «" . ($section['name'] ?? '#'.$id) . "» activada correctamente."
                    : "⏸️ {$entityLabel} «" . ($section['name'] ?? '#'.$id) . "» desactivada correctamente."
            );
            redirect($redirectUrl, 303);
        } catch (\Throwable $e) {
            $debug = (bool)(require dirname(__DIR__, 2) . '/config/config.php')['app']['debug'] ?? false;
            $msg = $debug
                ? "❌ Error al cambiar estado: " . get_class($e) . " — " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")"
                : "❌ No se pudo cambiar el estado de la {$entityLabel}. Intenta nuevamente.";
            Session::flash('error', $msg);
            redirect($redirectUrl, 303);
        }
    }

    public function delete(array $params): void
    {
        Auth::requireCan('sections.delete');

        $id = (int)($params['id'] ?? 0);
        $redirectUrl = '/admin/secciones';
        $entityName  = 'sections';
        $entityLabel = 'Sección';

        try {
            $section = Section::find($id);
            if (!$section) {
                Session::flash('error', "{$entityLabel} no encontrada.");
                redirect($redirectUrl, 303);
            }
            $before = json_encode($section, JSON_UNESCAPED_UNICODE);
            $user = Auth::user();
            $userId = (int)($user['id'] ?? 0);

            Section::delete($id);

            $auditData = [
                'before'        => json_decode($before, true),
                'deleted_by'    => $userId,
                'deleted_by_ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ];
            AuditLog::record('delete', $entityName, (string)$id, json_encode($auditData, JSON_UNESCAPED_UNICODE));

            Session::flash('success', "✅ {$entityLabel} eliminada correctamente.");
            redirect($redirectUrl, 303);
        } catch (\Throwable $e) {
            $debug = (bool)(require dirname(__DIR__, 2) . '/config/config.php')['app']['debug'] ?? false;
            $msg = $debug
                ? "❌ Error al eliminar: " . get_class($e) . " — " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")"
                : "❌ No se pudo eliminar la {$entityLabel}. Intenta nuevamente.";
            Session::flash('error', $msg);
            redirect($redirectUrl, 303);
        }
    }
}
