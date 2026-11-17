<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Core\Session;
use App\Models\Tag;
use App\Models\AuditLog;

class AdminTagsController extends Controller
{
    public function index(): void
    {
        $this->view('admin.tags.index', [
            'metaTitle' => 'Etiquetas | Admin',
            'items'     => Tag::all('name', 'ASC'),
        ]);
    }

    public function create(): void
    {
        $this->view('admin.tags.form', ['metaTitle' => 'Nueva etiqueta', 'item' => null]);
    }

    public function store(): void
    {
        $data = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'slug' => trim((string)($_POST['slug'] ?? '')),
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['name']);
        }
        $v = new Validator($data);
        if (!$v->validate(['name' => 'required|maxLength:80', 'slug' => 'required|slug|maxLength:100'])) {
            $this->withErrors($v->errors(), '/admin/etiquetas/crear');
        }
        if (Tag::existsBy('slug', $data['slug'])) {
            $this->withError('Slug ya existe.', '/admin/etiquetas/crear');
        }
        $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        $id = Tag::create($data);
        AuditLog::record('create', 'tags', (string)$id);
        $this->withSuccess('Etiqueta creada.', '/admin/etiquetas');
    }

    public function edit(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $item = Tag::find($id);
        if (!$item) {
            $this->withError('No encontrado.', '/admin/etiquetas');
        }
        $this->view('admin.tags.form', ['metaTitle' => 'Editar etiqueta', 'item' => $item]);
    }

    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        if (!Tag::find($id)) {
            $this->withError('No encontrado.', '/admin/etiquetas');
        }
        $data = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'slug' => trim((string)($_POST['slug'] ?? '')),
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['name']);
        }
        if (Tag::existsBy('slug', $data['slug'], $id)) {
            $this->withError('Slug ya usado.', '/admin/etiquetas/' . $id . '/editar');
        }
        $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        Tag::update($id, $data);
        AuditLog::record('update', 'tags', (string)$id);
        $this->withSuccess('Etiqueta actualizada.', '/admin/etiquetas');
    }

    public function delete(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $redirectUrl = '/admin/etiquetas';
        $entityName  = 'tags';
        $entityLabel = 'Etiqueta';

        try {
            $tag = Tag::find($id);
            if (!$tag) {
                Session::flash('error', "{$entityLabel} no encontrada.");
                redirect($redirectUrl, 303);
            }
            $tagBefore = json_encode($tag, JSON_UNESCAPED_UNICODE);
            $user = Auth::user();
            $userId = (int)($user['id'] ?? 0);

            Tag::delete($id);

            $auditData = [
                'before'        => json_decode($tagBefore, true),
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
