<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Core\Session;
use App\Core\Auth;
use App\Models\Category;
use App\Models\AuditLog;

class AdminCategoriesController extends Controller
{
    public function index(): void
    {
        $this->view('admin.categories.index', [
            'metaTitle'  => 'Categorías | Admin',
            'categories' => Category::all('sort_order', 'ASC'),
        ]);
    }

    public function create(): void
    {
        $this->view('admin.categories.form', [
            'metaTitle' => 'Nueva categoría',
            'item'      => null,
        ]);
    }

    public function store(): void
    {
        $data = [
            'name'        => trim((string)($_POST['name'] ?? '')),
            'slug'        => trim((string)($_POST['slug'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'sort_order'  => (int)($_POST['sort_order'] ?? 0),
            'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['name']);
        }
        $v = new Validator($data);
        if (!$v->validate(['name' => 'required|minLength:2|maxLength:100', 'slug' => 'required|slug|maxLength:120'])) {
            $this->withErrors($v->errors(), '/admin/categorias/crear');
        }
        if (Category::existsBy('slug', $data['slug'])) {
            $this->withError('Slug ya existe.', '/admin/categorias/crear');
        }
        $id = Category::create($data);
        AuditLog::record('create', 'categories', (string)$id);
        $this->withSuccess('Categoría creada.', '/admin/categorias');
    }

    public function edit(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $cat = Category::find($id);
        if (!$cat) {
            $this->withError('Categoría no encontrada.', '/admin/categorias');
        }
        $this->view('admin.categories.form', [
            'metaTitle' => 'Editar categoría',
            'item'      => $cat,
        ]);
    }

    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $cat = Category::find($id);
        if (!$cat) {
            $this->withError('Categoría no encontrada.', '/admin/categorias');
        }
        $data = [
            'name'        => trim((string)($_POST['name'] ?? '')),
            'slug'        => trim((string)($_POST['slug'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'sort_order'  => (int)($_POST['sort_order'] ?? 0),
            'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['name']);
        }
        if (Category::existsBy('slug', $data['slug'], $id)) {
            $this->withError('Slug ya usado por otra categoría.', '/admin/categorias/' . $id . '/editar');
        }
        Category::update($id, $data);
        AuditLog::record('update', 'categories', (string)$id);
        $this->withSuccess('Categoría actualizada.', '/admin/categorias');
    }

    public function delete(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $redirectUrl = '/admin/categorias';
        $entityName  = 'categories';
        $entityLabel = 'Categoría';

        try {
            $cat = Category::find($id);
            if (!$cat) {
                Session::flash('error', "{$entityLabel} no encontrada.");
                redirect($redirectUrl, 303);
            }
            $catBefore = json_encode($cat, JSON_UNESCAPED_UNICODE);
            $user = Auth::user();
            $userId = (int)($user['id'] ?? 0);

            Category::delete($id);

            $auditData = [
                'before'        => json_decode($catBefore, true),
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
