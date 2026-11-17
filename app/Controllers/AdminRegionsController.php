<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Models\Region;
use App\Models\AuditLog;

class AdminRegionsController extends Controller
{
    public function index(): void
    {
        $this->view('admin.regions.index', [
            'metaTitle' => 'Regiones / Ámbitos | Admin',
            'items'     => Region::all('sort_order', 'ASC'),
        ]);
    }

    public function create(): void
    {
        $this->view('admin.regions.form', ['metaTitle' => 'Nueva región', 'item' => null]);
    }

    public function store(): void
    {
        $data = [
            'name'       => trim((string)($_POST['name'] ?? '')),
            'slug'       => trim((string)($_POST['slug'] ?? '')),
            'type'       => trim((string)($_POST['type'] ?? 'region')),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['name']);
        }
        $v = new Validator($data);
        if (!$v->validate(['name' => 'required|maxLength:120', 'slug' => 'required|slug|maxLength:140'])) {
            $this->withErrors($v->errors(), '/admin/regiones/crear');
        }
        if (Region::existsBy('slug', $data['slug'])) {
            $this->withError('Slug ya existe.', '/admin/regiones/crear');
        }
        $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        $id = Region::create($data);
        AuditLog::record('create', 'regions', (string)$id);
        $this->withSuccess('Región creada.', '/admin/regiones');
    }

    public function edit(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $item = Region::find($id);
        if (!$item) {
            $this->withError('No encontrado.', '/admin/regiones');
        }
        $this->view('admin.regions.form', ['metaTitle' => 'Editar región', 'item' => $item]);
    }

    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        if (!Region::find($id)) {
            $this->withError('No encontrado.', '/admin/regiones');
        }
        $data = [
            'name'       => trim((string)($_POST['name'] ?? '')),
            'slug'       => trim((string)($_POST['slug'] ?? '')),
            'type'       => trim((string)($_POST['type'] ?? 'region')),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['name']);
        }
        if (Region::existsBy('slug', $data['slug'], $id)) {
            $this->withError('Slug ya usado.', '/admin/regiones/' . $id . '/editar');
        }
        $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        Region::update($id, $data);
        AuditLog::record('update', 'regions', (string)$id);
        $this->withSuccess('Región actualizada.', '/admin/regiones');
    }

    public function delete(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $redirectUrl = '/admin/regiones';
        $entityName  = 'regions';
        $entityLabel = 'Región';

        try {
            $region = Region::find($id);
            if (!$region) {
                Session::flash('error', "{$entityLabel} no encontrada.");
                redirect($redirectUrl, 303);
            }
            $regionBefore = json_encode($region, JSON_UNESCAPED_UNICODE);
            $user = Auth::user();
            $userId = (int)($user['id'] ?? 0);

            Region::delete($id);

            $auditData = [
                'before'        => json_decode($regionBefore, true),
                'deleted_by'    => $userId,
                'deleted_by_ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ];
            AuditLog::record('delete', $entityName, (string)$id, json_encode($auditData, JSON_UNESCAPED_UNICODE));

            Session::flash('success', "✅ {$entityLabel} eliminada correctamente.");
            redirect($redirectUrl, 303);
        } catch (Throwable $e) {
            $debug = (bool)(require dirname(__DIR__, 2) . '/config/config.php')['app']['debug'] ?? false;
            $msg = $debug
                ? "❌ Error al eliminar: " . get_class($e) . " — " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")"
                : "❌ No se pudo eliminar la {$entityLabel}. Intenta nuevamente.";
            Session::flash('error', $msg);
            redirect($redirectUrl, 303);
        }
    }
}
