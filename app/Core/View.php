<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], bool $return = false): ?string
    {
        $file = dirname(__DIR__, 2) . '/views/' . ltrim(str_replace('.', '/', $view), '/') . '.php';
        if (!file_exists($file)) {
            if ($return) {
                return null;
            }
            http_response_code(500);
            die('Vista no encontrada: ' . $view);
        }
        // Datos compartidos globales
        $GLOBALS['__view_data'] = $data;
        extract($data, EXTR_SKIP);
        if ($return) {
            ob_start();
            include $file;
            return ob_get_clean();
        }
        include $file;
        return null;
    }

    public static function partial(string $partial, array $data = []): void
    {
        $file = dirname(__DIR__, 2) . '/views/partials/' . ltrim(str_replace('.', '/', $partial), '/') . '.php';
        if (!file_exists($file)) {
            return;
        }
        extract($data, EXTR_SKIP);
        include $file;
    }
}
