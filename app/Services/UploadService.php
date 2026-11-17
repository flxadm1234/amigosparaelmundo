<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MediaFile;

class UploadService
{
    private array $config;

    public function __construct()
    {
        $this->config = require dirname(__DIR__, 2) . '/config/config.php';
    }

    /**
     * Maneja subida de un archivo $_FILES
     *
     * @param array $file          Entrada de $_FILES
     * @param string $subDir       Subdirectorio dentro de uploads (ej: news/12)
     * @param string $kind         attachment|gallery|cover|thumbnail|audio|video|other
     * @param int|null $newsId     FK opcional a tabla news
     * @param int|null $userId     Usuario creador
     * @param string|null $label   Etiqueta descriptiva
     * @param int $sortOrder       Orden visual
     * @param int|null $videoId    FK opcional a tabla videos
     * @param int|null $podcastId  FK opcional a tabla podcast_episodes (col: podcast_id)
     * @return array{ok:bool, error?:string, id?:int, data?:array}
     */
    public function handleUpload(
        array $file,
        string $subDir = '',
        string $kind = 'attachment',
        ?int $newsId = null,
        ?int $userId = null,
        ?string $label = null,
        int $sortOrder = 0,
        ?int $videoId = null,
        ?int $podcastId = null
    ): array {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['ok' => false, 'error' => 'Parámetros de archivo inválidos.'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => $this->uploadErrorText($file['error'])];
        }
        if ($file['size'] <= 0) {
            return ['ok' => false, 'error' => 'Archivo vacío.'];
        }
        $maxBytes = ((int)($this->config['upload']['max_mb'] ?? 8)) * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            return ['ok' => false, 'error' => "Archivo excede tamaño máximo ({$this->config['upload']['max_mb']} MB)."];
        }
        $origName = $file['name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowed = array_merge(
            (array)($this->config['upload']['allowed_images'] ?? []),
            (array)($this->config['upload']['allowed_docs'] ?? []),
            (array)($this->config['upload']['allowed_audio'] ?? []),
            (array)($this->config['upload']['allowed_video'] ?? [])
        );
        if (!in_array($ext, $allowed, true)) {
            return ['ok' => false, 'error' => 'Extensión no permitida: ' . $ext . '. Extensiones válidas: ' . implode(', ', $allowed)];
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? (string)finfo_file($finfo, $file['tmp_name']) : ($file['type'] ?? 'application/octet-stream');

        $baseDir = rtrim($this->config['upload']['base_dir'], '/');
        $subDirClean = trim(str_replace(['..', "\\"], '', $subDir), '/');
        $fullDir = $baseDir . ($subDirClean !== '' ? '/' . $subDirClean : '');
        if (!is_dir($fullDir)) {
            if (!mkdir($fullDir, 0775, true) && !is_dir($fullDir)) {
                return ['ok' => false, 'error' => 'No se pudo crear directorio de destino.'];
            }
        }

        $uniqueName = $this->uniqueName($origName);
        $destPath = $fullDir . '/' . $uniqueName;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['ok' => false, 'error' => 'No se pudo guardar el archivo en el servidor.'];
        }
        @chmod($destPath, 0664);

        $urlPath = rtrim($this->config['upload']['public_url'], '/')
            . ($subDirClean !== '' ? '/' . $subDirClean : '')
            . '/' . $uniqueName;

        $payload = [
            'file_kind'      => $kind,
            'file_label'     => $label ?? $origName,
            'original_name'  => $origName,
            'stored_name'    => $uniqueName,
            'stored_path'    => $urlPath,
            'mime_type'      => $mime,
            'size_bytes'     => (int)$file['size'],
            'file_ext'       => $ext,
            'sort_order'     => $sortOrder,
            'created_by'     => $userId,
        ];
        if ($newsId !== null)     { $payload['news_id']    = $newsId; }
        if ($videoId !== null)    { $payload['video_id']   = $videoId; }
        if ($podcastId !== null)  { $payload['podcast_id'] = $podcastId; }

        $id = MediaFile::create($payload);

        return [
            'ok'   => true,
            'id'   => $id,
            'data' => [
                'id'            => $id,
                'stored_path'   => $urlPath,
                'stored_name'   => $uniqueName,
                'original_name' => $origName,
                'size_bytes'    => (int)$file['size'],
                'mime_type'     => $mime,
                'file_ext'      => $ext,
            ],
        ];
    }

    public function deleteMedia(int $mediaId): bool
    {
        $m = MediaFile::find($mediaId);
        if (!$m) {
            return false;
        }
        // ✅ FIX: Resolver ruta FÍSICA REAL (no duplicar /uploads/uploads/)
        $path = resolve_upload_abs_path((string)($m['stored_path'] ?? ''));
        if (is_file($path)) {
            @unlink($path);
        }
        // Fallback: intentar también la ruta "antigua" con concatenación directa (por si acaso)
        $fallbackPath = rtrim((string)($this->config['upload']['base_dir'] ?? ''), '/')
            . '/' . ltrim((string)($m['stored_path'] ?? ''), '/');
        if (is_file($fallbackPath) && $fallbackPath !== $path) {
            @unlink($fallbackPath);
        }
        // Fallback 2: si stored_path es DIRECTAMENTE relativo a /public/
        $root = dirname(__DIR__, 2);
        $alt3 = $root . '/public/' . ltrim((string)($m['stored_path'] ?? ''), '/');
        if (is_file($alt3) && $alt3 !== $path && $alt3 !== $fallbackPath) {
            @unlink($alt3);
        }
        MediaFile::delete($mediaId);
        return true;
    }

    public static function uniqueName(string $original): string
    {
        $ext  = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $base = pathinfo($original, PATHINFO_FILENAME);
        $base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base));
        $base = trim($base, '-');
        if ($base === '') {
            $base = 'file';
        }
        return sprintf('%s_%s_%s.%s', date('YmdHis'), bin2hex(random_bytes(4)), substr($base, 0, 40), $ext);
    }

    private function uploadErrorText(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido.',
            UPLOAD_ERR_PARTIAL => 'La carga se interrumpió parcialmente. Intenta nuevamente.',
            UPLOAD_ERR_NO_FILE => 'Ningún archivo fue cargado.',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal en el servidor.',
            UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en disco.',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la carga.',
            default => 'Error desconocido al cargar el archivo.',
        };
    }
}
