<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Core\Session;
use App\Core\Auth;
use App\Models\Video;
use App\Models\Section;
use App\Models\AuditLog;
use App\Services\UploadService;

class AdminVideosController extends Controller
{
    public function index(): void
    {
        Auth::requireCan('videos');

        $filters = [
            'q'            => trim((string)($_GET['q'] ?? '')),
            'is_published' => isset($_GET['is_published']) && $_GET['is_published'] !== '' ? (int)$_GET['is_published'] : null,
            'section_id'   => (int)($_GET['section_id'] ?? 0),
            'order'        => (string)($_GET['order'] ?? 'new'),
        ];
        $perPage = config_val('pagination.per_page_admin', 20);
        $page = $this->page();
        $results = Video::searchAdmin($filters, $page, $perPage);

        $this->view('admin.videos.index', [
            'metaTitle'  => 'Videos | Admin',
            'filters'    => $filters,
            'results'    => $results,
            'sections'   => Section::activeOrdered(),
        ]);
    }

    public function create(): void
    {
        Auth::requireCan('videos.create');

        $this->view('admin.videos.form', [
            'metaTitle' => 'Nuevo video',
            'item'      => null,
            'sections'  => Section::activeOrdered(),
        ]);
    }

    public function store(): void
    {
        Auth::requireCan('videos.create');

        $user = Auth::user();
        $data = [
            'title'         => trim((string)($_POST['title'] ?? '')),
            'slug'          => trim((string)($_POST['slug'] ?? '')),
            'description'   => trim((string)($_POST['description'] ?? '')),
            'section_id'    => (int)($_POST['section_id'] ?? 0) ?: null,
            'video_type'    => trim((string)($_POST['video_type'] ?? 'embed')),
            'embed_url'     => trim((string)($_POST['embed_url'] ?? '')),
            'video_path'    => trim((string)($_POST['video_path_existing'] ?? '')),
            'thumbnail_path'=> trim((string)($_POST['thumbnail_path_existing'] ?? '')),
            'duration'      => trim((string)($_POST['duration'] ?? '')),
            'publish_date'  => !empty($_POST['publish_date']) ? $_POST['publish_date'] : date('Y-m-d H:i:s'),
            'is_published'  => isset($_POST['is_published']) ? 1 : 0,
        ];

        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['title']);
        }
        $data['slug'] = $this->uniqueSlug($data['slug']);

        $v = new Validator($data);
        if (!$v->validate([
            'title' => 'required|minLength:2|maxLength:250',
            'slug'  => 'required|slug|maxLength:270',
        ])) {
            $this->withErrors($v->errors(), '/admin/videos/crear');
        }
        if (Video::existsBy('slug', $data['slug'])) {
            $this->withError('Ya existe un video con este slug.', '/admin/videos/crear');
        }

        $uploader = new UploadService();
        $subDir = 'videos/tmp';
        $userId = (int)$user['id'];

        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['thumbnail_file'], 'videos/thumbnails', 'cover', null, $userId, 'Thumbnail: ' . $data['title']);
            if ($res['ok']) {
                $data['thumbnail_path'] = $res['data']['stored_path'];
            } elseif (!empty($res['error'])) {
                $this->withError('Error al subir thumbnail: ' . $res['error'], '/admin/videos/crear');
            }
        }

        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['video_file'], 'videos', 'other', null, $userId, 'Video: ' . $data['title']);
            if ($res['ok']) {
                $data['video_path'] = $res['data']['stored_path'];
            } elseif (!empty($res['error'])) {
                $this->withError('Error al subir video: ' . $res['error'], '/admin/videos/crear');
            }
        }

        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;
        $id = Video::create($data);

        // Asociar archivos media ya subidos (thumbnail / video_path) al video creado mediante FK video_id
        try {
            $pdo = \App\Core\DB::connection();
            $bind = [];
            $sets = [];
            if (!empty($data['thumbnail_path'])) { $sets[] = "CASE WHEN stored_path = ? THEN ? ELSE video_id END"; $bind[] = $data['thumbnail_path']; $bind[] = $id; }
            if (!empty($data['video_path'])) { $sets[] = "CASE WHEN stored_path = ? THEN ? ELSE video_id END"; $bind[] = $data['video_path']; $bind[] = $id; }
            if (count($sets) > 0) {
                $wherePaths = array_filter([$data['thumbnail_path'] ?? null, $data['video_path'] ?? null]);
                $placeholders = implode(',', array_fill(0, count($wherePaths), '?'));
                $stmt = $pdo->prepare("UPDATE media_files SET video_id = COALESCE(LEAST(video_id, {$sets[0]}), {$sets[0]}) WHERE stored_path IN ({$placeholders}) AND (video_id IS NULL OR video_id = 0)");
                $params = array_merge($bind, $wherePaths);
                $stmt->execute($params);
            }
        } catch (\Throwable $eVidFk) { }

        AuditLog::record('create', 'videos', (string)$id, json_encode(['title' => $data['title']], JSON_UNESCAPED_UNICODE));

        $submitAction = (string)($_POST['action'] ?? '');
        if ($submitAction === 'save_and_continue' || isset($_POST['save_and_continue'])) {
            $this->withSuccess('Video creado correctamente. Puedes seguir editándolo.', '/admin/videos/' . $id . '/editar');
        } else {
            $this->withSuccess('Video creado correctamente.', '/admin/videos');
        }
    }

    public function edit(array $params): void
    {
        Auth::requireCan('videos.edit');

        $id = (int)($params['id'] ?? 0);
        $video = Video::find($id);
        if (!$video) {
            Session::flash('error', 'Video no encontrado.');
            redirect('/admin/videos');
        }
        $this->view('admin.videos.form', [
            'metaTitle' => 'Editar video #' . $id,
            'item'      => $video,
            'sections'  => Section::activeOrdered(),
        ]);
    }

    public function update(array $params): void
    {
        Auth::requireCan('videos.edit');

        $id = (int)($params['id'] ?? 0);
        $video = Video::find($id);
        if (!$video) {
            $this->withError('Video no encontrado.', '/admin/videos');
        }
        $before = json_encode($video, JSON_UNESCAPED_UNICODE);

        $user = Auth::user();
        $removeThumb = isset($_POST['remove_thumbnail']) && trim((string)($_POST['remove_thumbnail'] ?? '')) === '1';
        $data = [
            'title'         => trim((string)($_POST['title'] ?? '')),
            'slug'          => trim((string)($_POST['slug'] ?? '')),
            'description'   => trim((string)($_POST['description'] ?? '')),
            'section_id'    => (int)($_POST['section_id'] ?? 0) ?: null,
            'video_type'    => trim((string)($_POST['video_type'] ?? 'embed')),
            'embed_url'     => trim((string)($_POST['embed_url'] ?? '')),
            'video_path'    => $removeThumb ? (string)($video['video_path'] ?? '') : (string)(($video['video_path'] ?? ($_POST['video_path_existing'] ?? '')) ?? ''),
            'thumbnail_path'=> $removeThumb ? '' : (string)($video['thumbnail_path'] ?? ''),
            'duration'      => trim((string)($_POST['duration'] ?? '')),
            'publish_date'  => !empty($_POST['publish_date']) ? $_POST['publish_date'] : date('Y-m-d H:i:s'),
            'is_published'  => isset($_POST['is_published']) ? 1 : 0,
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['title']);
        }
        if ($data['slug'] !== $video['slug']) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $id);
            if (Video::existsBy('slug', $data['slug'], $id)) {
                $this->withError('Ya existe otro video con este slug.', '/admin/videos/' . $id . '/editar');
            }
        }
        $v = new Validator($data);
        if (!$v->validate([
            'title' => 'required|minLength:2|maxLength:250',
            'slug'  => 'required|slug|maxLength:270',
        ])) {
            $this->withErrors($v->errors(), '/admin/videos/' . $id . '/editar');
        }

        $uploader = new UploadService();
        $userId = (int)$user['id'];

        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['thumbnail_file'], 'videos/thumbnails', 'cover', null, $userId, 'Thumbnail: ' . $data['title'], 0, $id);
            if ($res['ok']) {
                $data['thumbnail_path'] = $res['data']['stored_path'];
            }
        }

        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['video_file'], 'videos', 'other', null, $userId, 'Video: ' . $data['title'], 0, $id);
            if ($res['ok']) {
                $data['video_path'] = $res['data']['stored_path'];
            }
        }

        $data['updated_by'] = $userId;
        Video::update($id, $data);
        AuditLog::record('update', 'videos', (string)$id, json_encode([
            'before' => json_decode($before, true),
            'after'  => $data,
        ], JSON_UNESCAPED_UNICODE));

        $submitAction = (string)($_POST['action'] ?? '');
        if ($submitAction === 'save_and_continue' || isset($_POST['save_and_continue'])) {
            $this->withSuccess('Cambios guardados correctamente.', '/admin/videos/' . $id . '/editar');
        } else {
            $this->withSuccess('Video actualizado correctamente.', '/admin/videos');
        }
    }

    public function togglePublish(array $params): void
    {
        Auth::requireCan('videos.publish');

        $id = (int)($params['id'] ?? 0);
        $video = Video::find($id);
        if (!$video) {
            Session::flash('error', 'Video no encontrado.');
            redirect('/admin/videos');
        }
        $newStatus = (int)$video['is_published'] === 1 ? 0 : 1;
        Video::update($id, ['is_published' => $newStatus]);
        AuditLog::record($newStatus ? 'publish' : 'unpublish', 'videos', (string)$id);
        Session::flash('success', $newStatus ? 'Video publicado.' : 'Video despublicado.');
        redirect('/admin/videos');
    }

    public function delete(array $params): void
    {
        Auth::requireCan('videos.delete');

        $id = (int)($params['id'] ?? 0);
        $redirectUrl = '/admin/videos';
        $entityName  = 'videos';
        $entityLabel = 'Video';

        try {
            $video = Video::find($id);
            if (!$video) {
                Session::flash('error', "{$entityLabel} no encontrado.");
                redirect($redirectUrl, 303);
            }
            $before = json_encode($video, JSON_UNESCAPED_UNICODE);
            $user = Auth::user();
            $userId = (int)($user['id'] ?? 0);

            Video::delete($id);

            $auditData = [
                'before'        => json_decode($before, true),
                'deleted_by'    => $userId,
                'deleted_by_ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ];
            AuditLog::record('delete', $entityName, (string)$id, json_encode($auditData, JSON_UNESCAPED_UNICODE));

            Session::flash('success', "✅ {$entityLabel} eliminado correctamente.");
            redirect($redirectUrl, 303);
        } catch (\Throwable $e) {
            $debug = (bool)(require dirname(__DIR__, 2) . '/config/config.php')['app']['debug'] ?? false;
            $msg = $debug
                ? "❌ Error al eliminar: " . get_class($e) . " — " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")"
                : "❌ No se pudo eliminar el {$entityLabel}. Intenta nuevamente.";
            Session::flash('error', $msg);
            redirect($redirectUrl, 303);
        }
    }

    private function uniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $i = 1;
        while (Video::existsBy('slug', $slug, $excludeId)) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }
}
