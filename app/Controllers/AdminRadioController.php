<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Core\Session;
use App\Core\Auth;
use App\Models\RadioProgram;
use App\Models\SiteSetting;
use App\Models\AuditLog;
use App\Services\UploadService;

class AdminRadioController extends Controller
{
    public function index(): void
    {
        Auth::requireCan('radio');

        $filters = [
            'q'         => trim((string)($_GET['q'] ?? '')),
            'is_active' => isset($_GET['is_active']) && $_GET['is_active'] !== '' ? (string)$_GET['is_active'] : '',
            'order'     => (string)($_GET['order'] ?? 'sort'),
        ];
        $perPage = config_val('pagination.per_page_admin', 20);
        $page = $this->page();
        $results = RadioProgram::searchAdmin($filters, $page, $perPage);

        $this->view('admin.radio.programas.index', [
            'metaTitle' => 'Radio | Admin',
            'filters'   => $filters,
            'results'   => $results,
        ]);
    }

    public function create(): void
    {
        Auth::requireCan('radio.create');

        $this->view('admin.radio.programas.form', [
            'metaTitle' => 'Nuevo programa de radio',
            'item'      => null,
        ]);
    }

    public function store(): void
    {
        Auth::requireCan('radio.create');

        $user = Auth::user();
        $data = [
            'name'             => trim((string)($_POST['name'] ?? '')),
            'slug'             => trim((string)($_POST['slug'] ?? '')),
            'description'      => trim((string)($_POST['description'] ?? '')),
            'hosts'            => trim((string)($_POST['hosts'] ?? '')),
            'schedule_text'    => trim((string)($_POST['schedule_text'] ?? '')),
            'cover_image_path' => trim((string)($_POST['cover_image_path_existing'] ?? '')),
            'icon_key'         => in_array((string)($_POST['icon_key'] ?? ''), ['leaf', 'book', 'hands', 'sun', 'heart', 'paw'], true)
                ? (string)$_POST['icon_key'] : 'leaf',
            'sort_order'       => (int)($_POST['sort_order'] ?? 0),
            'is_active'        => isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['name']);
        }
        $v = new Validator($data);
        if (!$v->validate([
            'name' => 'required|minLength:2|maxLength:200',
            'slug' => 'required|slug|maxLength:220',
        ])) {
            $this->withErrors($v->errors(), '/admin/radio/crear');
        }
        if (RadioProgram::existsBy('slug', $data['slug'])) {
            $this->withError('Slug ya existe.', '/admin/radio/crear');
        }

        $uploader = new UploadService();
        $userId = (int)$user['id'];
        if (isset($_FILES['cover_image_file']) && $_FILES['cover_image_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['cover_image_file'], 'radio', 'cover', null, $userId, 'Cover: ' . $data['name']);
            if ($res['ok']) {
                $data['cover_image_path'] = $res['data']['stored_path'];
            }
        }

        $id = RadioProgram::create($data);
        AuditLog::record('create', 'radio_programs', (string)$id, json_encode($data, JSON_UNESCAPED_UNICODE));

        $submitAction = (string)($_POST['action'] ?? '');
        if (($submitAction === 'save_and_continue') || isset($_POST['save_and_continue'])) {
            $this->withSuccess('Programa de radio creado correctamente.', '/admin/radio/programas/' . $id . '/editar');
        } else {
            $this->withSuccess('Programa de radio creado correctamente.', '/admin/radio/programas');
        }
    }

    public function edit(array $params): void
    {
        Auth::requireCan('radio.edit');

        $id = (int)($params['id'] ?? 0);
        $prog = RadioProgram::find($id);
        if (!$prog) {
            $this->withError('Programa no encontrado.', '/admin/radio');
        }
        $this->view('admin.radio.programas.form', [
            'metaTitle' => 'Editar programa #' . $id,
            'item'      => $prog,
        ]);
    }

    public function update(array $params): void
    {
        Auth::requireCan('radio.edit');

        $id = (int)($params['id'] ?? 0);
        $prog = RadioProgram::find($id);
        if (!$prog) {
            $this->withError('Programa no encontrado.', '/admin/radio');
        }
        $before = json_encode($prog, JSON_UNESCAPED_UNICODE);

        $user = Auth::user();
        $removeCover = isset($_POST['remove_cover_image']) && trim((string)($_POST['remove_cover_image'] ?? '')) === '1';
        $data = [
            'name'             => trim((string)($_POST['name'] ?? '')),
            'slug'             => trim((string)($_POST['slug'] ?? '')),
            'description'      => trim((string)($_POST['description'] ?? '')),
            'hosts'            => trim((string)($_POST['hosts'] ?? '')),
            'schedule_text'    => trim((string)($_POST['schedule_text'] ?? '')),
            'cover_image_path' => $removeCover ? '' : (string)(($prog['cover_image_path'] ?? '') ?: ($_POST['cover_image_path_existing'] ?? '')),
            'icon_key'         => in_array((string)($_POST['icon_key'] ?? ''), ['leaf', 'book', 'hands', 'sun', 'heart', 'paw'], true)
                ? (string)$_POST['icon_key'] : (string)($prog['icon_key'] ?? 'leaf'),
            'sort_order'       => (int)($_POST['sort_order'] ?? 0),
            'is_active'        => isset($_POST['is_active']) ? 1 : 0,
        ];
        if ($data['slug'] === '') {
            $data['slug'] = str_slug($data['name']);
        }
        if (RadioProgram::existsBy('slug', $data['slug'], $id)) {
            $this->withError('Slug ya usado por otro programa.', '/admin/radio/programas/' . $id . '/editar');
        }

        $uploader = new UploadService();
        $userId = (int)$user['id'];
        if (isset($_FILES['cover_image_file']) && $_FILES['cover_image_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['cover_image_file'], 'radio', 'cover', null, $userId, 'Cover: ' . $data['name']);
            if ($res['ok']) {
                $data['cover_image_path'] = $res['data']['stored_path'];
            }
        }

        RadioProgram::update($id, $data);
        AuditLog::record('update', 'radio_programs', (string)$id, json_encode([
            'before' => json_decode($before, true),
            'after'  => $data,
        ], JSON_UNESCAPED_UNICODE));

        $submitAction = (string)($_POST['action'] ?? '');
        if (($submitAction === 'save_and_continue') || isset($_POST['save_and_continue'])) {
            $this->withSuccess('Programa actualizado correctamente.', '/admin/radio/programas/' . $id . '/editar');
        } else {
            $this->withSuccess('Programa actualizado correctamente.', '/admin/radio/programas');
        }
    }

    public function delete(array $params): void
    {
        Auth::requireCan('radio.delete');

        $id = (int)($params['id'] ?? 0);
        $redirectUrl = '/admin/radio/programas';
        $entityName  = 'radio_programs';
        $entityLabel = 'Programa de radio';

        try {
            $prog = RadioProgram::find($id);
            if (!$prog) {
                Session::flash('error', "{$entityLabel} no encontrado.");
                redirect($redirectUrl, 303);
            }
            $before = json_encode($prog, JSON_UNESCAPED_UNICODE);
            $user = Auth::user();
            $userId = (int)($user['id'] ?? 0);

            RadioProgram::delete($id);

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

    public function toggle(array $params): void
    {
        Auth::requireCan('radio.edit');

        $id = (int)($params['id'] ?? 0);
        $redirectUrl = '/admin/radio/programas';
        $entityLabel = 'Programa de radio';

        try {
            $prog = RadioProgram::find($id);
            if (!$prog) {
                Session::flash('error', "{$entityLabel} no encontrado.");
                redirect($redirectUrl, 303);
            }
            $before = json_encode($prog, JSON_UNESCAPED_UNICODE);
            $user = Auth::user();
            $userId = (int)($user['id'] ?? 0);

            $newVal = ((int)($prog['is_active'] ?? 0)) === 1 ? 0 : 1;
            RadioProgram::update($id, ['is_active' => $newVal]);

            $auditData = [
                'before' => json_decode($before, true),
                'after'  => ['is_active' => $newVal],
            ];
            AuditLog::record('update', 'radio_programs', (string)$id, json_encode($auditData, JSON_UNESCAPED_UNICODE));

            Session::flash('success', $newVal === 1 ? "✅ {$entityLabel} activado correctamente." : "⏸️ {$entityLabel} desactivado correctamente.");
            redirect($redirectUrl, 303);
        } catch (\Throwable $e) {
            $debug = (bool)(require dirname(__DIR__, 2) . '/config/config.php')['app']['debug'] ?? false;
            $msg = $debug
                ? "❌ Error al cambiar estado: " . get_class($e) . " — " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")"
                : "❌ No se pudo cambiar el estado del {$entityLabel}. Intenta nuevamente.";
            Session::flash('error', $msg);
            redirect($redirectUrl, 303);
        }
    }

    public function settings(): void
    {
        Auth::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $keys = ['radio_stream_url', 'radio_station_name', 'radio_now_playing'];
            $before = [];
            $after  = [];
            foreach ($keys as $k) {
                $before[$k] = app_setting($k);
                $val = trim((string)($_POST[$k] ?? ''));
                SiteSetting::set($k, $val);
                $after[$k] = $val;
            }
            AuditLog::record('update', 'site_settings', 'radio', json_encode([
                'before' => $before,
                'after'  => $after,
            ], JSON_UNESCAPED_UNICODE));
            $this->withSuccess('Configuración de radio guardada correctamente.', '/admin/radio/ajustes');
        }

        $this->view('admin.radio.settings', [
            'metaTitle' => 'Ajustes de Radio | Admin',
        ]);
    }
}
