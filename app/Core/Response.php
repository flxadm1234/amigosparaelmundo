<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function text(string $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: text/plain; charset=utf-8');
        echo $data;
        exit;
    }

    public static function notFound(string $message = 'Página no encontrada'): never
    {
        http_response_code(404);
        View::render('public.404', ['message' => $message]);
        exit;
    }

    public static function forbidden(string $message = 'Acceso denegado'): never
    {
        http_response_code(403);
        echo '<h1>403</h1><p>' . e($message) . '</p>';
        exit;
    }

    public static function file(string $filePath, string $downloadName, ?string $mime = null): never
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'Archivo no encontrado.';
            exit;
        }
        $size = filesize($filePath);
        $mime = $mime ?? mime_content_type($filePath) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . $size);
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Accept-Ranges: bytes');
        readfile($filePath);
        exit;
    }

    public static function fileInline(string $filePath, string $name, ?string $mime = null): never
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'Archivo no encontrado.';
            exit;
        }
        $size = filesize($filePath);
        $mime = $mime ?? mime_content_type($filePath) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . $size);
        header('Content-Disposition: inline; filename="' . $name . '"');
        readfile($filePath);
        exit;
    }
}
