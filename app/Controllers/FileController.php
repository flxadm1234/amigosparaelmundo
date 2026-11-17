<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\MediaFile;

class FileController extends Controller
{
    /**
     * Descarga / visualiza un archivo de publicación.
     * URL: /media/{id}/{download|view}
     *
     * ✅ FIX: Ahora usa resolve_upload_abs_path() para NO duplicar /uploads/uploads/
     *        y encontrar el archivo en la ruta FÍSICA REAL del disco.
     */
    public function serve(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $mode = (string)($params['mode'] ?? 'download');
        $media = MediaFile::find($id);
        if (!$media) {
            Response::notFound('Archivo no encontrado.');
        }

        // ✅ FIX CRÍTICO: Resolver ruta FÍSICA REAL (no duplicar /uploads/uploads/)
        $filePath = resolve_upload_abs_path((string)($media['stored_path'] ?? ''));

        if (!is_file($filePath) || !is_readable($filePath)) {
            // Debug temporal: reportar QUÉ rutas se probaron (solo si APP_DEBUG=true)
            $cfg = require dirname(__DIR__, 2) . '/config/config.php';
            if (!empty($cfg['app']['debug'])) {
                $stored = (string)($media['stored_path'] ?? '');
                $baseDir = rtrim((string)($cfg['upload']['base_dir'] ?? ''), '/');
                $publicUrl = rtrim((string)($cfg['upload']['public_url'] ?? '/uploads'), '/');
                $msgDebug  = "DEBUG (APP_DEBUG=true):\r\n";
                $msgDebug .= "  stored_path DB  : {$stored}\r\n";
                $msgDebug .= "  upload.base_dir : {$baseDir}\r\n";
                $msgDebug .= "  upload.public_url: {$publicUrl}\r\n";
                $msgDebug .= "  Ruta probada (resolve_upload_abs_path): {$filePath}\r\n";
                $msgDebug .= "  file_exists: " . (is_file($filePath) ? 'SI' : 'NO') . "\r\n";
                $msgDebug .= "  is_readable: " . (is_readable($filePath) ? 'SI' : 'NO') . "\r\n";
                $msgDebug .= "  Permisos: " . (is_file($filePath) ? decoct(fileperms($filePath) & 0777) : 'N/A') . "\r\n";
            } else {
                $msgDebug = '';
            }

            $ext = strtolower($media['file_ext'] ?: 'bin');
            $downloadName = $media['original_name'] ?: 'archivo.' . $ext;
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');
            echo "❌ Archivo no encontrado en el servidor.\r\n";
            echo "Noticia ID : " . ($media['news_id']    ?? '-') . "\r\n";
            echo "Video ID   : " . ($media['video_id']   ?? '-') . "\r\n";
            echo "Podcast ID : " . ($media['podcast_id'] ?? '-') . "\r\n";
            echo "Etiqueta   : " . ($media['file_label'] ?? '-') . "\r\n";
            echo "Nombre original: " . $media['original_name'] . "\r\n";
            echo "Extensión: {$ext}\r\nTamaño: " . format_bytes((int)($media['size_bytes'] ?? 0)) . "\r\n";
            if ($msgDebug !== '') {
                echo "\r\n===========================\r\n" . $msgDebug;
            }
            exit;
        }

        if ($mode === 'view' && str_starts_with((string)($media['mime_type'] ?? ''), 'image/')) {
            Response::fileInline($filePath, $media['original_name'], $media['mime_type']);
        }
        Response::file($filePath, $media['original_name'], $media['mime_type']);
    }
}
