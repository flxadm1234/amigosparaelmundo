<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Validator;
use App\Core\Auth;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;

class AdminUsersController extends Controller
{
    public function index(): void
    {
        $users = User::listWithRole();
        $this->view('admin.users.index', [
            'metaTitle' => 'Usuarios | Admin',
            'users'     => $users,
        ]);
    }

    public function create(): void
    {
        $this->view('admin.users.form', [
            'metaTitle' => 'Nuevo usuario',
            'user'      => null,
            'roles'     => Role::all('name', 'ASC'),
        ]);
    }

    public function store(): void
    {
        $data = [
            'name'                  => trim((string)($_POST['name'] ?? '')),
            'email'                 => trim((string)($_POST['email'] ?? '')),
            'password'              => (string)($_POST['password'] ?? ''),
            'password_confirmation' => (string)($_POST['password_confirmation'] ?? ''),
            'role_id'               => (int)($_POST['role_id'] ?? 0),
            'status'                => isset($_POST['status']) ? 1 : 0,
        ];
        $v = new Validator($data);
        if (!$v->validate([
            'name'  => 'required|minLength:3|maxLength:120',
            'email' => 'required|email|maxLength:180',
            'password' => 'required|minLength:6|maxLength:100|matches:password_confirmation',
            'role_id'  => 'integer',
        ])) {
            $this->withErrors($v->errors(), '/admin/usuarios/crear');
        }
        if (User::existsBy('email', $data['email'])) {
            $this->withError('Ya existe un usuario con este correo.', '/admin/usuarios/crear');
        }
        $id = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role_id'       => $data['role_id'],
            'status'        => $data['status'],
        ]);
        AuditLog::record('create', 'users', (string)$id);
        $this->withSuccess('Usuario creado correctamente.', '/admin/usuarios');
    }

    public function edit(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $u = User::withRole($id);
        if (!$u) {
            $this->withError('Usuario no encontrado.', '/admin/usuarios');
        }
        $this->view('admin.users.form', [
            'metaTitle' => 'Editar usuario',
            'user'      => $u,
            'roles'     => Role::all('name', 'ASC'),
        ]);
    }

    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $u = User::find($id);
        if (!$u) {
            $this->withError('Usuario no encontrado.', '/admin/usuarios');
        }
        $data = [
            'name'                  => trim((string)($_POST['name'] ?? '')),
            'email'                 => trim((string)($_POST['email'] ?? '')),
            'password'              => (string)($_POST['password'] ?? ''),
            'password_confirmation' => (string)($_POST['password_confirmation'] ?? ''),
            'role_id'               => (int)($_POST['role_id'] ?? 0),
            'status'                => isset($_POST['status']) ? 1 : 0,
        ];
        $rules = [
            'name'    => 'required|minLength:3|maxLength:120',
            'email'   => 'required|email|maxLength:180',
            'role_id' => 'integer',
        ];
        if ($data['password'] !== '') {
            $rules['password'] = 'minLength:6|maxLength:100|matches:password_confirmation';
        }
        $v = new Validator($data);
        if (!$v->validate($rules)) {
            $this->withErrors($v->errors(), '/admin/usuarios/' . $id . '/editar');
        }
        if (User::existsBy('email', $data['email'], $id)) {
            $this->withError('Correo ya usado por otro usuario.', '/admin/usuarios/' . $id . '/editar');
        }
        $up = [
            'name'    => $data['name'],
            'email'   => $data['email'],
            'role_id' => $data['role_id'],
            'status'  => $data['status'],
        ];
        if ($data['password'] !== '') {
            $up['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        User::update($id, $up);
        AuditLog::record('update', 'users', (string)$id);
        $this->withSuccess('Usuario actualizado.', '/admin/usuarios');
    }

    public function delete(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $redirectUrl = '/admin/usuarios';
        $entityName  = 'users';
        $entityLabel = 'Usuario';

        try {
            $current = Auth::user();
            if ($current && (int)$current['id'] === $id) {
                Session::flash('error', "⚠️ No puedes eliminar tu propio usuario.");
                redirect($redirectUrl, 303);
            }
            $userRow = User::find($id);
            if (!$userRow) {
                Session::flash('error', "{$entityLabel} no encontrado.");
                redirect($redirectUrl, 303);
            }
            $userBefore = json_encode($userRow, JSON_UNESCAPED_UNICODE);
            $userId = (int)($current['id'] ?? 0);

            User::delete($id);

            $auditData = [
                'before'        => json_decode($userBefore, true),
                'deleted_by'    => $userId,
                'deleted_by_ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ];
            // No guardar password_hash en auditoría (seguridad)
            if (isset($auditData['before']['password_hash'])) {
                $auditData['before']['password_hash'] = '[REDACTED]';
            }
            AuditLog::record('delete', $entityName, (string)$id, json_encode($auditData, JSON_UNESCAPED_UNICODE));

            Session::flash('success', "✅ {$entityLabel} eliminado correctamente.");
            redirect($redirectUrl, 303);
        } catch (Throwable $e) {
            $debug = (bool)(require dirname(__DIR__, 2) . '/config/config.php')['app']['debug'] ?? false;
            $msg = $debug
                ? "❌ Error al eliminar: " . get_class($e) . " — " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")"
                : "❌ No se pudo eliminar el {$entityLabel}. Intenta nuevamente.";
            Session::flash('error', $msg);
            redirect($redirectUrl, 303);
        }
    }
}
