<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Core\Session;
use App\Core\Auth;
use App\Models\News;
use App\Models\Section;
use App\Models\Tag;
use App\Models\MediaFile;
use App\Models\AuditLog;
use App\Services\UploadService;

class AdminNewsController extends Controller
{
    public function index(): void
    {
        Auth::requireCan('news');

        $filters = [
            'q'            => trim((string)($_GET['q'] ?? '')),
            'is_published' => isset($_GET['is_published']) && $_GET['is_published'] !== '' ? (int)$_GET['is_published'] : null,
            'year'         => (int)($_GET['year'] ?? 0),
            'section_id'   => (int)($_GET['section_id'] ?? 0),
            'order'        => (string)($_GET['order'] ?? 'new'),
        ];
        $perPage = config_val('pagination.per_page_admin', 20);
        $page = $this->page();
        $results = News::searchAdmin($filters, $page, $perPage);

        $this->view('admin.news.index', [
            'metaTitle'  => 'Noticias | Admin',
            'filters'    => $filters,
            'results'    => $results,
            'years'      => News::availableYears(),
            'sections'   => Section::activeOrdered(),
        ]);
    }

    public function create(): void
    {
        Auth::requireCan('news.create');

        $this->view('admin.news.form', [
            'metaTitle'    => 'Nueva noticia',
            'item'         => null,
            'selectedTags' => [],
            'sections'     => Section::activeOrdered(),
            'tags'         => Tag::activeOrdered(),
            'adjuntos'     => [],
            'galeria'      => [],
            'cover'        => null,
        ]);
    }

    public function store(): void
    {
        Auth::requireCan('news.create');

        $user = Auth::user();
        $data = [
            'title'              => trim((string)($_POST['title'] ?? '')),
            'slug'               => trim((string)($_POST['slug'] ?? '')),
            'excerpt'            => trim((string)($_POST['excerpt'] ?? '')),
            'content_html'       => $_POST['content_html'] ?? '',
            'section_id'         => (int)($_POST['section_id'] ?? 0) ?: null,
            'publish_date'       => !empty($_POST['publish_date']) ? $_POST['publish_date'] : date('Y-m-d H:i:s'),
            'is_published'       => isset($_POST['is_published']) ? 1 : 0,
            'is_featured'        => isset($_POST['is_featured']) ? 1 : 0,
            'is_breaking'        => isset($_POST['is_breaking']) ? 1 : 0,
            'tag_ids'            => $_POST['tag_ids'] ?? [],
            'source_name'        => trim((string)($_POST['source_name'] ?? '')),
            'source_url'         => trim((string)($_POST['source_url'] ?? '')),
            'cover_caption'      => trim((string)($_POST['cover_caption'] ?? '')),
            'meta_title'         => trim((string)($_POST['meta_title'] ?? '')),
            'meta_desc'          => trim((string)($_POST['meta_desc'] ?? '')),
            'meta_keywords'      => trim((string)($_POST['meta_keywords'] ?? '')),
            'author_name'        => trim((string)($_POST['author_name'] ?? '')),
            'remove_cover_image' => isset($_POST['remove_cover_image']) && $_POST['remove_cover_image'] === '1',
        ];

        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['title']);
        }
        $data['slug'] = $this->uniqueSlug($data['slug']);

        $v = new Validator($data);
        if (!$v->validate([
            'title' => 'required|minLength:5|maxLength:250',
            'slug'  => 'required|slug|maxLength:270',
            'excerpt' => 'maxLength:500',
        ])) {
            $this->withErrors($v->errors(), '/admin/noticias/crear');
        }
        if (News::existsBy('slug', $data['slug'])) {
            $this->withError('Ya existe una noticia con este slug. Modifícalo manualmente.', '/admin/noticias/crear');
        }

        $create = [
            'title'                => $data['title'],
            'slug'                 => $data['slug'],
            'excerpt'              => $data['excerpt'],
            'content_html'         => $data['content_html'],
            'section_id'           => $data['section_id'],
            'publish_date'         => $data['publish_date'],
            'is_published'         => $data['is_published'],
            'is_featured'          => $data['is_featured'],
            'is_breaking'          => $data['is_breaking'],
            'created_by'           => (int)$user['id'],
            'updated_by'           => (int)$user['id'],
            'source_name'          => $data['source_name'] !== '' ? $data['source_name'] : null,
            'source_url'           => $data['source_url'] !== '' ? $data['source_url'] : null,
            'cover_caption'        => $data['cover_caption'] !== '' ? $data['cover_caption'] : null,
            'meta_title'           => $data['meta_title'] !== '' ? $data['meta_title'] : null,
            'meta_desc'            => $data['meta_desc'] !== '' ? $data['meta_desc'] : null,
            'meta_keywords'        => $data['meta_keywords'] !== '' ? $data['meta_keywords'] : null,
            'author_name'          => $data['author_name'] !== '' ? $data['author_name'] : null,
        ];
        $id = News::create($create);

        if (is_array($data['tag_ids'])) {
            News::syncTags($id, array_map('intval', $data['tag_ids']));
        }

        $coverUploaded = $this->handleUploadedFiles($id, $user);
        $hasNewCoverStore = !empty($coverUploaded['cover_path']);
        $hasRemoveCoverStore = !empty($data['remove_cover_image']);
        if ($hasNewCoverStore || $hasRemoveCoverStore) {
            News::update($id, ['cover_image_path' => $hasNewCoverStore ? $coverUploaded['cover_path'] : null]);
        }

        AuditLog::record('create', 'news', (string)$id, json_encode([
            'title'           => $data['title'],
            'cover_image_set' => $hasNewCoverStore,
            'cover_removed'   => $hasRemoveCoverStore,
        ], JSON_UNESCAPED_UNICODE));

        if (isset($_POST['action']) && $_POST['action'] === 'save_and_continue') {
            $this->withSuccess('Noticia creada correctamente. Puedes seguir editándola.', '/admin/noticias/' . $id . '/editar');
        } else {
            $this->withSuccess('Noticia creada correctamente.', '/admin/noticias');
        }
    }

    public function edit(array $params): void
    {
        Auth::requireCan('news.edit');

        $id = (int)($params['id'] ?? 0);
        $noticia = News::withDetails($id);
        if (!$noticia) {
            Session::flash('error', 'Noticia no encontrada.');
            redirect('/admin/noticias');
        }
        $selectedTags = array_column(News::tagsFor($id), 'id');
        $adjuntos     = News::attachments($id, 'attachment');
        $galeria      = News::gallery($id);
        $cover        = News::cover($id);

        $this->view('admin.news.form', [
            'metaTitle'    => 'Editar noticia #' . $id,
            'item'         => $noticia,
            'selectedTags' => $selectedTags,
            'sections'     => Section::activeOrdered(),
            'tags'         => Tag::activeOrdered(),
            'adjuntos'     => $adjuntos,
            'galeria'      => $galeria,
            'cover'        => $cover,
        ]);
    }

    public function update(array $params): void
    {
        Auth::requireCan('news.edit');

        $id = (int)($params['id'] ?? 0);
        $noticia = News::find($id);
        if (!$noticia) {
            $this->withError('Noticia no encontrada.', '/admin/noticias');
        }
        $before = json_encode($noticia, JSON_UNESCAPED_UNICODE);

        $user = Auth::user();
        $data = [
            'title'              => trim((string)($_POST['title'] ?? '')),
            'slug'               => trim((string)($_POST['slug'] ?? '')),
            'excerpt'            => trim((string)($_POST['excerpt'] ?? '')),
            'content_html'       => $_POST['content_html'] ?? '',
            'section_id'         => (int)($_POST['section_id'] ?? 0) ?: null,
            'publish_date'       => !empty($_POST['publish_date']) ? $_POST['publish_date'] : date('Y-m-d H:i:s'),
            'is_published'       => isset($_POST['is_published']) ? 1 : 0,
            'is_featured'        => isset($_POST['is_featured']) ? 1 : 0,
            'is_breaking'        => isset($_POST['is_breaking']) ? 1 : 0,
            'tag_ids'            => $_POST['tag_ids'] ?? [],
            'source_name'        => trim((string)($_POST['source_name'] ?? '')),
            'source_url'         => trim((string)($_POST['source_url'] ?? '')),
            'cover_caption'      => trim((string)($_POST['cover_caption'] ?? '')),
            'meta_title'         => trim((string)($_POST['meta_title'] ?? '')),
            'meta_desc'          => trim((string)($_POST['meta_desc'] ?? '')),
            'meta_keywords'      => trim((string)($_POST['meta_keywords'] ?? '')),
            'author_name'        => trim((string)($_POST['author_name'] ?? '')),
            'remove_cover_image' => isset($_POST['remove_cover_image']) && $_POST['remove_cover_image'] === '1',
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['title']);
        }
        if ($data['slug'] !== $noticia['slug']) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $id);
            if (News::existsBy('slug', $data['slug'], $id)) {
                $this->withError('Ya existe otra noticia con este slug.', '/admin/noticias/' . $id . '/editar');
            }
        }
        $v = new Validator($data);
        if (!$v->validate([
            'title' => 'required|minLength:5|maxLength:250',
            'slug'  => 'required|slug|maxLength:270',
        ])) {
            $this->withErrors($v->errors(), '/admin/noticias/' . $id . '/editar');
        }

        $update = [
            'title'                => $data['title'],
            'slug'                 => $data['slug'],
            'excerpt'              => $data['excerpt'],
            'content_html'         => $data['content_html'],
            'section_id'           => $data['section_id'],
            'publish_date'         => $data['publish_date'],
            'is_published'         => $data['is_published'],
            'is_featured'          => $data['is_featured'],
            'is_breaking'          => $data['is_breaking'],
            'updated_by'           => (int)$user['id'],
            'source_name'          => $data['source_name'] !== '' ? $data['source_name'] : null,
            'source_url'           => $data['source_url'] !== '' ? $data['source_url'] : null,
            'cover_caption'        => $data['cover_caption'] !== '' ? $data['cover_caption'] : null,
            'meta_title'           => $data['meta_title'] !== '' ? $data['meta_title'] : null,
            'meta_desc'            => $data['meta_desc'] !== '' ? $data['meta_desc'] : null,
            'meta_keywords'        => $data['meta_keywords'] !== '' ? $data['meta_keywords'] : null,
            'author_name'          => $data['author_name'] !== '' ? $data['author_name'] : null,
        ];
        News::update($id, $update);

        if (is_array($data['tag_ids'])) {
            News::syncTags($id, array_map('intval', $data['tag_ids']));
        }
        $coverUploaded = $this->handleUploadedFiles($id, $user);
        $hasNewCoverUpdate = !empty($coverUploaded['cover_path']);
        $hasRemoveCoverUpdate = !empty($data['remove_cover_image']);
        if ($hasNewCoverUpdate || $hasRemoveCoverUpdate) {
            $update['cover_image_path'] = $hasNewCoverUpdate ? $coverUploaded['cover_path'] : null;
            News::update($id, ['cover_image_path' => $update['cover_image_path']]);
        }

        AuditLog::record('update', 'news', (string)$id, json_encode([
            'before' => json_decode($before, true),
            'after'  => $update,
            'cover_changed' => $hasNewCoverUpdate || $hasRemoveCoverUpdate,
            'cover_removed' => $hasRemoveCoverUpdate,
        ], JSON_UNESCAPED_UNICODE));

        if (isset($_POST['action']) && $_POST['action'] === 'save_and_continue') {
            $this->withSuccess('Cambios guardados correctamente. Puedes seguir editando.', '/admin/noticias/' . $id . '/editar');
        } else {
            $this->withSuccess('Noticia actualizada correctamente.', '/admin/noticias');
        }
    }

    public function togglePublish(array $params): void
    {
        Auth::requireCan('news.publish');

        $id = (int)($params['id'] ?? 0);
        $noticia = News::find($id);
        if (!$noticia) {
            Session::flash('error', 'Noticia no encontrada.');
            redirect('/admin/noticias');
        }
        $newStatus = (int)$noticia['is_published'] === 1 ? 0 : 1;
        News::update($id, ['is_published' => $newStatus]);
        AuditLog::record($newStatus ? 'publish' : 'unpublish', 'news', (string)$id);
        Session::flash('success', $newStatus ? 'Noticia publicada.' : 'Noticia despublicada.');
        redirect('/admin/noticias');
    }

    public function delete(array $params): void
    {
        Auth::requireCan('news.delete');

        $id = (int)($params['id'] ?? 0);
        $redirectUrl = '/admin/noticias';
        $entityName  = 'news';
        $entityLabel = 'Noticia';

        try {
            $noticia = News::find($id);
            if (!$noticia) {
                Session::flash('error', "{$entityLabel} no encontrada.");
                redirect($redirectUrl, 303);
            }
            $before = json_encode($noticia, JSON_UNESCAPED_UNICODE);
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
                } catch (\Throwable $eMedia) {
                }
            }

            News::delete($id);

            $auditData = [
                'before'        => json_decode($before, true),
                'media_deleted' => $deletedMediaCount,
                'media_files'   => $mediaBefore,
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

    public function deleteFile(array $params): void
    {
        Auth::requireCan('news.edit');

        $mid = (int)($params['media_id'] ?? 0);
        $media = MediaFile::find($mid);
        if (!$media) {
            Session::flash('error', 'Archivo no encontrado.');
            redirect_back('/admin/noticias');
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
        } catch (\Throwable $e) {
            $debug = (bool)(require dirname(__DIR__, 2) . '/config/config.php')['app']['debug'] ?? false;
            $msg = $debug
                ? "❌ Error al eliminar archivo: " . get_class($e) . " — " . $e->getMessage()
                : "❌ No se pudo eliminar el archivo.";
            Session::flash('error', $msg);
        }
        $fallback = (!empty($media['news_id']))
            ? '/admin/noticias/' . (int)$media['news_id'] . '/editar'
            : '/admin/noticias';
        redirect_back($fallback);
    }

    /**
     * @return array{cover_path:?string}
     */
    private function handleUploadedFiles(int $newsId, array $user): array
    {
        $uploader = new UploadService();
        $subDir = 'news/' . $newsId;
        $userId = (int)$user['id'];
        $out = ['cover_path' => null];

        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['cover_image'], $subDir, 'cover', $newsId, $userId, 'Cover');
            if ($res['ok']) {
                $out['cover_path'] = (string)($res['data']['stored_path'] ?? '');
                AuditLog::record('upload', 'media_files', (string)$res['id'], json_encode(['kind' => 'cover', 'news_id' => $newsId]));
            }
        }

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
                $res = $uploader->handleUpload($file, $subDir, 'attachment', $newsId, $userId, $label, $i);
                if ($res['ok']) {
                    AuditLog::record('upload', 'media_files', (string)$res['id'], json_encode(['kind' => 'attachment', 'name' => $file['name']]));
                }
            }
        }

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
                $res = $uploader->handleUpload($file, $subDir, 'gallery', $newsId, $userId, null, $i);
                if ($res['ok']) {
                    AuditLog::record('upload', 'media_files', (string)$res['id'], json_encode(['kind' => 'gallery', 'name' => $file['name']]));
                }
            }
        }

        return $out;
    }

    private function uniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $i = 1;
        while (News::existsBy('slug', $slug, $excludeId)) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }
}
