<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Session;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        return $default;
    }

    protected function allInput(): array
    {
        return array_merge($_GET, $_POST);
    }

    protected function flashInputOld(): void
    {
        Session::flash('_old_input', $_POST + $_GET);
    }

    protected function withSuccess(string $msg, string $redirect): never
    {
        Session::flash('success', $msg);
        redirect($redirect);
    }

    protected function withError(string $msg, string $redirect): never
    {
        Session::flash('error', $msg);
        redirect($redirect);
    }

    protected function withErrors(array $errors, string $redirect): never
    {
        Session::flash('errors', $errors);
        $this->flashInputOld();
        redirect($redirect);
    }

    protected function page(): int
    {
        return max(1, (int)($_GET['page'] ?? 1));
    }

    protected function currentUrl(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        return $uri;
    }
}
