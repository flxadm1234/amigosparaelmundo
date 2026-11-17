<?php
/**
 * Configuración general de la aplicación
 * Asociación Civil Amigos por el Mundo
 */

declare(strict_types=1);

return [
    'app' => [
        'name'       => 'ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO',
        'url'        => env_val('APP_URL', 'http://127.0.0.1:8000'),
        'env'        => env_val('APP_ENV', 'production'),
        'debug'      => filter_var(env_val('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
        'key'        => env_val('APP_KEY', 'amigos-mundo-default-change-me-please-000000000000'),
        'timezone'   => 'America/Lima',
        'locale'     => 'es_ES',
    ],
    'db' => [
        'host'    => env_val('DB_HOST', '127.0.0.1'),
        'port'    => (int) env_val('DB_PORT', '3306'),
        'name'    => env_val('DB_NAME', 'amigos_mundo'),
        'user'    => env_val('DB_USER', 'root'),
        'pass'    => env_val('DB_PASS', ''),
        'charset' => env_val('DB_CHARSET', 'utf8mb4'),
    ],
    'upload' => [
        'max_mb'   => (int) env_val('MAX_UPLOAD_MB', '256'),
        'base_dir' => dirname(__DIR__) . '/public/uploads',
        'public_url' => '/uploads',
        'allowed_images' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif'],
        'allowed_docs'   => ['pdf', 'xlsx', 'xls', 'docx', 'doc', 'csv', 'txt', 'zip', 'rar'],
        'allowed_audio'  => ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac'],
        'allowed_video'  => ['mp4', 'webm', 'ogv', 'mov', 'm4v'],
        'allowed_mimes'  => [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/bmp', 'image/avif',
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
            'text/csv', 'text/plain',
            'application/zip', 'application/x-rar-compressed', 'application/x-rar',
            'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/mp4', 'audio/x-m4a', 'audio/aac', 'audio/flac',
            'video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-m4v',
            'application/octet-stream',
        ],
    ],
    'security' => [
        'login_max_attempts' => 5,
        'login_lock_seconds' => 300,
        'session_lifetime'   => 7200,
        'csrf_token_name'    => '_csrf_token',
    ],
    'pagination' => [
        'per_page_public' => 9,
        'per_page_admin'  => 20,
    ],
];
