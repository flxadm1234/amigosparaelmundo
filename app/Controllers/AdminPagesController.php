<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Core\Auth;
use App\Services\UploadService;
use App\Models\Page;
use App\Models\AuditLog;

class AdminPagesController extends Controller
{
    public function index(): void
    {
        $perPage = config_val('pagination.per_page_admin', 20);
        $page = $this->page();
        $results = Page::paginate($page, $perPage, [], 'updated_at', 'DESC');

        $this->view('admin.pages.index', [
            'metaTitle' => 'Páginas | Admin',
            'results'   => $results,
        ]);
    }

    public function create(): void
    {
        $this->view('admin.pages.form', [
            'metaTitle' => 'Nueva página',
            'page'      => null,
        ]);
    }

    public function store(): void
    {
        $user = Auth::user();
        $data = [
            'title'            => trim((string)($_POST['title'] ?? '')),
            'slug'             => trim((string)($_POST['slug'] ?? '')),
            'content_html'     => $_POST['content_html'] ?? '',
            'meta_title'       => trim((string)($_POST['meta_title'] ?? '')),
            'meta_desc'        => trim((string)($_POST['meta_desc'] ?? '')),
            'layout'           => $_POST['layout'] ?? 'default',
            'is_published'     => isset($_POST['is_published']) ? 1 : 0,
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['title']);
        }
        if (Page::existsBy('slug', $data['slug'])) {
            $data['slug'] .= '-' . time();
        }
        $v = new Validator($data);
        if (!$v->validate(['title' => 'required|minLength:3|maxLength:200', 'slug' => 'required|slug|maxLength:160'])) {
            $this->withErrors($v->errors(), '/admin/paginas/crear');
        }
        $data['created_by'] = $data['updated_by'] = (int)$user['id'];
        $id = Page::create($data);
        AuditLog::record('create', 'pages', (string)$id);

        $submitAction = (string)($_POST['action'] ?? '');
        if (($submitAction === 'save_and_continue') || isset($_POST['save_and_continue'])) {
            $this->withSuccess('Página creada correctamente.', '/admin/paginas/' . $id . '/editar');
        } else {
            $this->withSuccess('Página creada correctamente.', '/admin/paginas');
        }
    }

    public function edit(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $p = Page::find($id);
        if (!$p) {
            $this->withError('Página no encontrada.', '/admin/paginas');
        }
        $this->view('admin.pages.form', [
            'metaTitle' => 'Editar página',
            'page'      => $p,
        ]);
    }

    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $p = Page::find($id);
        if (!$p) {
            $this->withError('Página no encontrada.', '/admin/paginas');
        }
        $user = Auth::user();
        $data = [
            'title'            => trim((string)($_POST['title'] ?? '')),
            'slug'             => trim((string)($_POST['slug'] ?? '')),
            'content_html'     => $_POST['content_html'] ?? '',
            'meta_title'       => trim((string)($_POST['meta_title'] ?? '')),
            'meta_desc'        => trim((string)($_POST['meta_desc'] ?? '')),
            'layout'           => $_POST['layout'] ?? 'default',
            'is_published'     => isset($_POST['is_published']) ? 1 : 0,
            'updated_by'       => (int)$user['id'],
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['title']);
        }
        if (Page::existsBy('slug', $data['slug'], $id)) {
            $this->withError('Slug ya usado.', '/admin/paginas/' . $id . '/editar');
        }
        Page::update($id, $data);
        AuditLog::record('update', 'pages', (string)$id);

        $submitAction = (string)($_POST['action'] ?? '');
        if (($submitAction === 'save_and_continue') || isset($_POST['save_and_continue'])) {
            $this->withSuccess('Página actualizada correctamente.', '/admin/paginas/' . $id . '/editar');
        } else {
            $this->withSuccess('Página actualizada correctamente.', '/admin/paginas');
        }
    }

    public function togglePublish(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $p = Page::find($id);
        if (!$p) {
            $this->withError('No encontrado.', '/admin/paginas');
        }
        $new = (int)$p['is_published'] === 1 ? 0 : 1;
        Page::update($id, ['is_published' => $new]);
        AuditLog::record($new ? 'publish' : 'unpublish', 'pages', (string)$id);
        $this->withSuccess($new ? 'Publicada.' : 'Despublicada.', '/admin/paginas');
    }

    public function delete(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $redirectUrl = '/admin/paginas';
        $entityName  = 'pages';
        $entityLabel = 'Página';

        try {
            $page = Page::find($id);
            if (!$page) {
                Session::flash('error', "{$entityLabel} no encontrada.");
                redirect($redirectUrl, 303);
            }
            $pageBefore = json_encode($page, JSON_UNESCAPED_UNICODE);
            $user = Auth::user();
            $userId = (int)($user['id'] ?? 0);

            Page::delete($id);

            $auditData = [
                'before'        => json_decode($pageBefore, true),
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

    private function handleImageUpload(string $fieldName = 'cover_image'): array
    {
        if (empty($_FILES[$fieldName]) || !is_uploaded_file($_FILES[$fieldName]['tmp_name'])) {
            return ['ok' => false, 'error' => 'No se seleccionó ningún archivo para subir.'];
        }
        $user = Auth::user();
        $uid = (int)($user['id'] ?? 0) ?: 1;
        $uploader = new UploadService();
        $res = $uploader->handleUpload($_FILES[$fieldName], 'pages', 'image', null, $uid, 'Page Cover');
        if (!empty($res['ok']) && !empty($res['data']['stored_path'])) {
            return ['ok' => true, 'stored_path' => $res['data']['stored_path']];
        }
        return ['ok' => false, 'error' => $res['error'] ?? 'Error al subir la imagen'];
    }
}
