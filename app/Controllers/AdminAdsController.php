<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Core\Auth;
use App\Services\UploadService;
use App\Models\Ad;
use App\Models\AuditLog;

class AdminAdsController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q'         => trim((string)($_GET['q'] ?? '')),
            'position'  => trim((string)($_GET['position'] ?? '')),
            'is_active' => isset($_GET['is_active']) && $_GET['is_active'] !== '' ? (int)$_GET['is_active'] : null,
        ];
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $results = Ad::adminSearch($filters, $page, 20);

        $this->view('admin.ads.index', [
            'metaTitle' => 'Publicidad | Admin',
            'results'   => $results,
            'filters'   => $filters,
            'positions' => Ad::POSITIONS,
        ]);
    }

    public function create(): void
    {
        $this->view('admin.ads.form', [
            'metaTitle' => 'Nueva publicidad',
            'item'      => null,
            'positions' => Ad::POSITIONS,
            'kinds'     => Ad::KINDS,
        ]);
    }

    private function collectFormData(): array
    {
        $startAt = trim((string)($_POST['start_at'] ?? ''));
        $endAt   = trim((string)($_POST['end_at'] ?? ''));
        return [
            'title'        => trim((string)($_POST['title'] ?? '')),
            'position'     => trim((string)($_POST['position'] ?? 'top-banner')),
            'ad_kind'      => trim((string)($_POST['ad_kind'] ?? 'image')),
            'link_url'     => trim((string)($_POST['link_url'] ?? '')),
            'alt_text'     => trim((string)($_POST['alt_text'] ?? '')),
            'html_code'    => $_POST['html_code'] ?? '',
            'start_at'     => $startAt === '' ? null : $startAt,
            'end_at'       => $endAt   === '' ? null : $endAt,
            'sort_order'   => (int)($_POST['sort_order'] ?? 0),
            'max_views'    => !empty($_POST['max_views']) ? (int)$_POST['max_views'] : null,
            'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    public function store(): void
    {
        $data = $this->collectFormData();
        $user = Auth::user();
        $userId = (int)($user['id'] ?? 0);
        $rules = ['title' => 'required|minLength:2|maxLength:180', 'position' => 'required'];
        if ($data['ad_kind'] === 'html' && trim((string)$data['html_code']) === '') {
            $this->withError('Para tipo HTML se requiere el código.', '/admin/publicidad/crear');
        }
        $v = new Validator($data);
        if (!$v->validate($rules)) $this->withErrors($v->errors(), '/admin/publicidad/crear');

        if ($data['ad_kind'] === 'image') {
            $up = $this->handleImageUpload();
            if ($up['ok']) {
                $data['image_path'] = $up['stored_path'];
            } elseif (!empty($_POST['image_path_text'])) {
                $data['image_path'] = trim((string)$_POST['image_path_text']);
            } else {
                $this->withError($up['error'] ?: 'Se requiere subir una imagen.', '/admin/publicidad/crear');
            }
        } else {
            $data['image_path'] = !empty($_POST['image_path_text']) ? trim((string)$_POST['image_path_text']) : null;
        }
        $data['created_by'] = $userId;
        $id = Ad::create($data);
        AuditLog::record('create', 'ads', (string)$id);
        $this->withSuccess('Publicidad creada correctamente.', '/admin/publicidad');
    }

    public function edit(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $ad = Ad::find($id);
        if (!$ad) $this->withError('Publicidad no encontrada.', '/admin/publicidad');

        $this->view('admin.ads.form', [
            'metaTitle' => 'Editar publicidad',
            'item'      => $ad,
            'positions' => Ad::POSITIONS,
            'kinds'     => Ad::KINDS,
        ]);
    }

    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $ad = Ad::find($id);
        if (!$ad) $this->withError('Publicidad no encontrada.', '/admin/publicidad');

        $data = $this->collectFormData();
        $rules = ['title' => 'required|minLength:2|maxLength:180', 'position' => 'required'];
        $v = new Validator($data);
        if (!$v->validate($rules)) $this->withErrors($v->errors(), '/admin/publicidad/' . $id . '/editar');

        if ($data['ad_kind'] === 'image') {
            $up = $this->handleImageUpload();
            if ($up['ok']) {
                $data['image_path'] = $up['stored_path'];
            } elseif (!empty($_POST['image_path_text'])) {
                $data['image_path'] = trim((string)$_POST['image_path_text']);
            } elseif (empty($ad['image_path'])) {
                $this->withError('Se requiere subir una imagen.', '/admin/publicidad/' . $id . '/editar');
            }
        } else {
            $data['image_path'] = !empty($_POST['image_path_text']) ? trim((string)$_POST['image_path_text']) : null;
            if (trim((string)$data['html_code']) === '') {
                $this->withError('Para tipo HTML se requiere el código.', '/admin/publicidad/' . $id . '/editar');
            }
        }
        Ad::update($id, $data);
        AuditLog::record('update', 'ads', (string)$id);
        $this->withSuccess('Publicidad actualizada.', '/admin/publicidad');
    }

    public function toggle(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $ad = Ad::find($id);
        if (!$ad) $this->withError('Publicidad no encontrada.', '/admin/publicidad');
        Ad::update($id, ['is_active' => ((int)$ad['is_active'] === 1 ? 0 : 1)]);
        AuditLog::record('toggle', 'ads', (string)$id);
        $this->withSuccess('Estado actualizado.', '/admin/publicidad');
    }

    public function delete(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $redirectUrl = '/admin/publicidad';
        $entityName  = 'ads';
        $entityLabel = 'Publicidad';

        try {
            $ad = Ad::find($id);
            if (!$ad) {
                Session::flash('error', "{$entityLabel} no encontrada.");
                redirect($redirectUrl, 303);
            }
            $adBefore = json_encode($ad, JSON_UNESCAPED_UNICODE);
            $user = Auth::user();
            $userId = (int)($user['id'] ?? 0);

            // Eliminar imagen asociada si existe
            if (!empty($ad['image_path']) && $ad['ad_kind'] !== 'html') {
                try {
                    $absImg = resolve_upload_abs_path((string)$ad['image_path']);
                    if (is_file($absImg)) { @unlink($absImg); }
                    // Fallback doble ruta por si acaso
                    $alt = (str_starts_with($ad['image_path'], '/'))
                        ? dirname(__DIR__, 2) . '/public' . $ad['image_path']
                        : dirname(__DIR__, 2) . '/public/uploads/' . ltrim($ad['image_path'], '/');
                    if (is_file($alt) && $alt !== $absImg) { @unlink($alt); }
                } catch (Throwable $eImg) { /* ignorar */ }
            }

            Ad::delete($id);

            $auditData = [
                'before'        => json_decode($adBefore, true),
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

    public function stats(): void
    {
        $rows = Ad::queryRows('SELECT id, title, position, views_count, clicks_count, is_active
                               FROM ads ORDER BY views_count DESC, clicks_count DESC LIMIT 50');
        $this->view('admin.ads.stats', [
            'metaTitle' => 'Estadísticas Publicidad',
            'rows'      => $rows,
        ]);
    }

    private function handleImageUpload(): array
    {
        if (empty($_FILES['image_file']) || !is_uploaded_file($_FILES['image_file']['tmp_name'])) {
            return ['ok' => false, 'error' => 'No se seleccionó ningún archivo para subir.'];
        }
        $user = Auth::user();
        $uid = (int)($user['id'] ?? 0) ?: 1;
        $uploader = new UploadService();
        $res = $uploader->handleUpload($_FILES['image_file'], 'ads', 'image', null, $uid, 'Ad Banner');
        if (!empty($res['ok']) && !empty($res['data']['stored_path'])) {
            return ['ok' => true, 'stored_path' => $res['data']['stored_path']];
        }
        return ['ok' => false, 'error' => $res['error'] ?? 'Error al subir imagen.'];
    }
}
