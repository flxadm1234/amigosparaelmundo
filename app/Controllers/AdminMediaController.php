<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Services\UploadService;

class AdminMediaController extends Controller
{
    public function __construct()
    {
        Auth::requireAuth();
        if (!Auth::is('admin') && !Auth::can('edit')) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'No tienes permisos para subir archivos.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function uploadInline(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $field = 'inline_image';
        if (empty($_FILES[$field]) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'No se recibió ninguna imagen válida.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $user = Auth::user();
        $uid = (int)($user['id'] ?? 0) ?: 1;
        $folder = 'pages-inline';
        $uploader = new UploadService();
        $res = $uploader->handleUpload($_FILES[$field], $folder, 'image', null, $uid, 'Inline WYSIWYG');
        if (empty($res['ok']) || empty($res['data']['stored_path'])) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => $res['error'] ?? 'Error al subir la imagen al servidor. Intenta con un formato diferente (JPG, PNG, WebP).'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        $sp = (string)$res['data']['stored_path'];
        $url = (str_starts_with($sp, '/') || preg_match('#^https?://#i', $sp)) ? $sp : upload_url($sp);
        echo json_encode([
            'ok' => true,
            'url' => $url,
            'stored_path' => $sp,
            'original_name' => $res['data']['original_name'] ?? null,
            'size_bytes'    => $res['data']['file_size'] ?? null,
            'mime_type'     => $res['data']['mime_type'] ?? null,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
