<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Core\Session;
use App\Core\Auth;
use App\Models\Publication;
use App\Models\Category;
use App\Models\Region;
use App\Models\Tag;
use App\Models\MediaFile;
use App\Models\AuditLog;
use App\Services\UploadService;

class AdminPublicationsController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q'           => trim((string)($_GET['q'] ?? '')),
            'is_published' => isset($_GET['is_published']) && $_GET['is_published'] !== '' ? (int)$_GET['is_published'] : null,
            'year'        => (int)($_GET['year'] ?? 0),
            'category_id' => (int)($_GET['category_id'] ?? 0),
            'region_id'   => (int)($_GET['region_id'] ?? 0),
            'order'       => (string)($_GET['order'] ?? 'new'),
        ];
        $perPage = config_val('pagination.per_page_admin', 20);
        $page = $this->page();
        $results = Publication::searchAdmin($filters, $page, $perPage);

        $this->view('admin.publications.index', [
            'metaTitle' => 'Publicaciones | Admin',
            'filters'   => $filters,
            'results'   => $results,
            'years'     => Publication::availableYears(),
            'categories' => Category::activeOrdered(),
            'regions'   => Region::activeOrdered(),
        ]);
    }

    public function create(): void
    {
        $this->view('admin.publications.form', [
            'metaTitle'  => 'Nueva publicación',
            'publication' => null,
            'selectedTags' => [],
            'categories'   => Category::activeOrdered(),
            'regions'      => Region::activeOrdered(),
            'tags'         => Tag::activeOrdered(),
            'attachments'  => [],
            'gallery'      => [],
        ]);
    }

    public function store(): void
    {
        $user = Auth::user();
        $data = [
            'title'              => trim((string)($_POST['title'] ?? '')),
            'slug'               => trim((string)($_POST['slug'] ?? '')),
            'excerpt'            => trim((string)($_POST['excerpt'] ?? '')),
            'content_html'       => $_POST['content_html'] ?? '',
            'category_id'        => (int)($_POST['category_id'] ?? 0) ?: null,
            'region_id'          => (int)($_POST['region_id'] ?? 0) ?: null,
            'year'               => (int)($_POST['year'] ?? 0) ?: null,
            'fieldwork_start_date' => $_POST['fieldwork_start_date'] === '' ? null : $_POST['fieldwork_start_date'],
            'fieldwork_end_date'   => $_POST['fieldwork_end_date'] === '' ? null : $_POST['fieldwork_end_date'],
            'publish_date'       => $_POST['publish_date'] ?? date('Y-m-d H:i:s'),
            'is_published'       => isset($_POST['is_published']) ? 1 : 0,
            'tag_ids'            => $_POST['tag_ids'] ?? [],
        ];

        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['title']);
        }
        // Garantizar slug único
        $data['slug'] = $this->uniqueSlug($data['slug']);

        $v = new Validator($data);
        if (!$v->validate([
            'title' => 'required|minLength:5|maxLength:250',
            'slug'  => 'required|slug|maxLength:270',
            'excerpt' => 'maxLength:500',
            'year'  => 'integer',
        ])) {
            $this->withErrors($v->errors(), '/admin/publicaciones/crear');
        }
        if (Publication::existsBy('slug', $data['slug'])) {
            $this->withError('Ya existe una publicación con este slug. Modifícalo manualmente.', '/admin/publicaciones/crear');
        }

        $create = [
            'title'                => $data['title'],
            'slug'                 => $data['slug'],
            'excerpt'              => $data['excerpt'],
            'content_html'         => $data['content_html'],
            'category_id'          => $data['category_id'],
            'region_id'            => $data['region_id'],
            'year'                 => $data['year'],
            'fieldwork_start_date' => $data['fieldwork_start_date'],
            'fieldwork_end_date'   => $data['fieldwork_end_date'],
            'publish_date'         => $data['publish_date'],
            'is_published'         => $data['is_published'],
            'created_by'           => (int)$user['id'],
            'updated_by'           => (int)$user['id'],
        ];
        $id = Publication::create($create);
        if (is_array($data['tag_ids'])) {
            Publication::syncTags($id, array_map('intval', $data['tag_ids']));
        }

        // Subida múltiple de archivos (adjuntos)
        $this->handleUploadedFiles($id, $user);

        AuditLog::record('create', 'publications', (string)$id, json_encode(['title' => $data['title']]));
        // FIX: Diferenciar botón Guardar (al listado) vs Guardar y seguir editando (misma página)
        if (isset($_POST['save_and_continue'])) {
            $this->withSuccess('Publicación creada correctamente. Puedes seguir editándola.', '/admin/publicaciones/' . $id . '/editar');
        } else {
            $this->withSuccess('Publicación creada correctamente.', '/admin/publicaciones');
        }
    }

    public function edit(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $pub = Publication::withDetails($id);
        if (!$pub) {
            Session::flash('error', 'Publicación no encontrada.');
            redirect('/admin/publicaciones');
        }
        $selectedTags = array_column(Publication::tagsFor($id), 'id');
        $attachments  = Publication::attachments($id, 'attachment');
        $gallery      = Publication::gallery($id);

        $this->view('admin.publications.form', [
            'metaTitle'    => 'Editar publicación #' . $id,
            'publication'  => $pub,
            'selectedTags' => $selectedTags,
            'categories'   => Category::activeOrdered(),
            'regions'      => Region::activeOrdered(),
            'tags'         => Tag::activeOrdered(),
            'attachments'  => $attachments,
            'gallery'      => $gallery,
        ]);
    }

    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $pub = Publication::find($id);
        if (!$pub) {
            $this->withError('Publicación no encontrada.', '/admin/publicaciones');
        }
        $user = Auth::user();
        $data = [
            'title'                => trim((string)($_POST['title'] ?? '')),
            'slug'                 => trim((string)($_POST['slug'] ?? '')),
            'excerpt'              => trim((string)($_POST['excerpt'] ?? '')),
            'content_html'         => $_POST['content_html'] ?? '',
            'category_id'          => (int)($_POST['category_id'] ?? 0) ?: null,
            'region_id'            => (int)($_POST['region_id'] ?? 0) ?: null,
            'year'                 => (int)($_POST['year'] ?? 0) ?: null,
            'fieldwork_start_date' => $_POST['fieldwork_start_date'] === '' ? null : $_POST['fieldwork_start_date'],
            'fieldwork_end_date'   => $_POST['fieldwork_end_date'] === '' ? null : $_POST['fieldwork_end_date'],
            'publish_date'         => $_POST['publish_date'] ?? date('Y-m-d H:i:s'),
            'is_published'         => isset($_POST['is_published']) ? 1 : 0,
            'tag_ids'              => $_POST['tag_ids'] ?? [],
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['title']);
        }
        if ($data['slug'] !== $pub['slug'] && Publication::existsBy('slug', $data['slug'], $id)) {
            $this->withError('Ya existe otra publicación con este slug.', '/admin/publicaciones/' . $id . '/editar');
        }
        $v = new Validator($data);
        if (!$v->validate([
            'title' => 'required|minLength:5|maxLength:250',
            'slug'  => 'required|slug|maxLength:270',
        ])) {
            $this->withErrors($v->errors(), '/admin/publicaciones/' . $id . '/editar');
        }

        Publication::update($id, [
            'title'                => $data['title'],
            'slug'                 => $data['slug'],
            'excerpt'              => $data['excerpt'],
            'content_html'         => $data['content_html'],
            'category_id'          => $data['category_id'],
            'region_id'            => $data['region_id'],
            'year'                 => $data['year'],
            'fieldwork_start_date' => $data['fieldwork_start_date'],
            'fieldwork_end_date'   => $data['fieldwork_end_date'],
            'publish_date'         => $data['publish_date'],
            'is_published'         => $data['is_published'],
            'updated_by'           => (int)$user['id'],
        ]);
        if (is_array($data['tag_ids'])) {
            Publication::syncTags($id, array_map('intval', $data['tag_ids']));
        }
        $this->handleUploadedFiles($id, $user);

        AuditLog::record('update', 'publications', (string)$id);
        // ✅ FIX: Botón "Guardar cambios" → REDIRIGE A LISTADO /admin/publicaciones
        // ✅ FIX: Botón "Guardar y seguir editando" → se mantiene en la misma página de edición
        if (isset($_POST['save_and_continue'])) {
            $this->withSuccess('Cambios guardados correctamente. Puedes seguir editando.', '/admin/publicaciones/' . $id . '/editar');
        } else {
            $this->withSuccess('Publicación actualizada correctamente.', '/admin/publicaciones');
        }
    }

    public function togglePublish(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $pub = Publication::find($id);
        if (!$pub) {
            Session::flash('error', 'Publicación no encontrada.');
            redirect('/admin/publicaciones');
        }
        $newStatus = (int)$pub['is_published'] === 1 ? 0 : 1;
        Publication::update($id, ['is_published' => $newStatus]);
        AuditLog::record($newStatus ? 'publish' : 'unpublish', 'publications', (string)$id);
        Session::flash('success', $newStatus ? 'Publicación publicada.' : 'Publicación despublicada.');
        redirect('/admin/publicaciones');
    }

    public function delete(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $redirectUrl = '/admin/publicaciones';
        $entityName  = 'publications';
        $entityLabel = 'Publicación';

        try {
            $pub = Publication::find($id);
            if (!$pub) {
                Session::flash('error', "{$entityLabel} no encontrada.");
                redirect($redirectUrl, 303);
            }
            $pubBefore = json_encode($pub, JSON_UNESCAPED_UNICODE);
            $user = Auth::user();
            $userId = (int)($user['id'] ?? 0);

            $uploader = new UploadService();
            $mediaArr = MediaFile::forPublication($id);
            $deletedMediaCount = 0;
            $mediaBefore = [];
            foreach ($mediaArr as $m) {
                $mediaBefore[] = $m['id'] . ':' . ($m['stored_path'] ?? $m['stored_name'] ?? '');
                try {
                    $uploader->deleteMedia((int)$m['id']);
                    $deletedMediaCount++;
                } catch (Throwable $eMedia) {
                    // No interrumpir delete por error de un archivo
                }
            }

            Publication::delete($id);

            $auditData = [
                'before'        => json_decode($pubBefore, true),
                'media_deleted' => $deletedMediaCount,
                'media_files'   => $mediaBefore,
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

    // --- Files ---
    public function deleteFile(array $params): void
    {
        $mid = (int)($params['media_id'] ?? 0);
        $media = MediaFile::find($mid);
        if (!$media) {
            Session::flash('error', 'Archivo no encontrado.');
            redirect_back('/admin/publicaciones');
        }
        try {
            $uploader = new UploadService();
            $uploader->deleteMedia($mid);
            AuditLog::record('delete', 'media_files', (string)$mid, json_encode([
                'original_name' => $media['original_name'] ?? '',
                'stored_path'   => $media['stored_path'] ?? '',
                'deleted_by_ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ]));
            Session::flash('success', '✅ Archivo eliminado correctamente.');
        } catch (Throwable $e) {
            $debug = (bool)(require dirname(__DIR__, 2) . '/config/config.php')['app']['debug'] ?? false;
            $msg = $debug
                ? "❌ Error al eliminar archivo: " . get_class($e) . " — " . $e->getMessage()
                : "❌ No se pudo eliminar el archivo.";
            Session::flash('error', $msg);
        }
        // Volver SIEMPRE a la URL que invocó la acción (editar publicación) y NO al listado
        $relId = (int)($media['news_id'] ?? $media['publication_id'] ?? 0);
        $fallback = $relId > 0
            ? '/admin/publicaciones/' . $relId . '/editar'
            : '/admin/publicaciones';
        redirect_back($fallback);
    }

    // --- Helpers ---
    private function handleUploadedFiles(int $pubId, array $user): void
    {
        $uploader = new UploadService();
        $subDir = 'publications/' . $pubId;
        $userId = (int)$user['id'];

        // Archivos adjuntos
        if (isset($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
            $count = count($_FILES['attachments']['name']);
            $labels = $_POST['attachment_label'] ?? [];
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $file = [
                    'name'     => $_FILES['attachments']['name'][$i],
                    'type'     => $_FILES['attachments']['type'][$i],
                    'tmp_name' => $_FILES['attachments']['tmp_name'][$i],
                    'error'    => $_FILES['attachments']['error'][$i],
                    'size'     => $_FILES['attachments']['size'][$i],
                ];
                $label = is_string($labels[$i] ?? null) && $labels[$i] !== '' ? $labels[$i] : null;
                $res = $uploader->handleUpload($file, $subDir, 'attachment', $pubId, $userId, $label, $i);
                if ($res['ok']) {
                    AuditLog::record('upload', 'media_files', (string)$res['id'], json_encode(['kind' => 'attachment', 'name' => $file['name']]));
                }
            }
        }
        // Galería
        if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
            $count = count($_FILES['gallery']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['gallery']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $file = [
                    'name'     => $_FILES['gallery']['name'][$i],
                    'type'     => $_FILES['gallery']['type'][$i],
                    'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
                    'error'    => $_FILES['gallery']['error'][$i],
                    'size'     => $_FILES['gallery']['size'][$i],
                ];
                $res = $uploader->handleUpload($file, $subDir, 'gallery', $pubId, $userId, null, $i);
                if ($res['ok']) {
                    AuditLog::record('upload', 'media_files', (string)$res['id'], json_encode(['kind' => 'gallery', 'name' => $file['name']]));
                }
            }
        }
    }

    private function uniqueSlug(string $slug): string
    {
        $original = $slug;
        $i = 1;
        while (Publication::existsBy('slug', $slug)) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }
}
