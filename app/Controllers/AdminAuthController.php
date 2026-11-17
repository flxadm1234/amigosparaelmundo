<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;

class AdminAuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect('/admin');
        }
        View::render('admin.auth.login', [
            'metaTitle' => 'Iniciar sesión | Admin ' . app_setting('site_name'),
        ]);
    }

    public function loginSubmit(): void
    {
        if (Auth::check()) {
            redirect('/admin');
        }
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $v = new Validator(['email' => $email, 'password' => $password]);
        if (!$v->validate([
            'email'    => 'required|email',
            'password' => 'required|minLength:4',
        ])) {
            $this->flashInputOld();
            Session::flash('error', $v->firstError() ?? 'Datos inválidos.');
            redirect('/admin/login');
        }
        $error = null;
        if (Auth::attempt($email, $password, $error)) {
            Session::flash('success', 'Bienvenido al panel de administración.');
            $flashOld = Session::flash('_old_input');
            $redirect = is_array($flashOld) && isset($flashOld['redirect_after']) && is_string($flashOld['redirect_after']) && $flashOld['redirect_after'] !== ''
                ? $flashOld['redirect_after']
                : '/admin';
            redirect($redirect);
        }
        $this->flashInputOld();
        Session::flash('error', $error ?? 'Credenciales inválidas.');
        redirect('/admin/login');
    }

    public function logout(): void
    {
        Auth::logout();
        Session::flash('success', 'Cierre de sesión exitoso.');
        redirect('/admin/login');
    }
}
