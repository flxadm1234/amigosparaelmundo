<?php
/**
 * AMIGOS POR EL MUNDO - Front Controller
 * Punto de entrada único del sitio.
 */

declare(strict_types=1);

// ---- Configuración de errores ---------------------------------------
if (file_exists(__DIR__ . '/../config/.env')) {
    require_once __DIR__ . '/../config/env.php';
}
$config = require __DIR__ . '/../config/config.php';
if ($config['app']['debug']) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}
date_default_timezone_set($config['app']['timezone'] ?? 'America/Lima');

// ---- Static files (solo para PHP built-in server / dev) ------------------
// PHP CLI server: si el request es a un archivo real existente en public/,
// devolverlo directamente con Content-Type correcto (sin pasar por MVC).
// Esto sirve también en LAMP (Apache/Nginx ya lo resuelve ellos, pero es NOOP).
if (PHP_SAPI === 'cli-server' || isset($_SERVER['REQUEST_METHOD'])) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    // Remover query string (?var=val)
    $requestPath = parse_url($requestUri, PHP_URL_PATH);
    if ($requestPath !== null && $requestPath !== '' && $requestPath !== '/') {
        $requestPath = '/' . ltrim($requestPath, '/');
        // Security: no permitir path traversal ../.. fuera de public/
        $cleanPath = str_replace(["\\", "\0"], '', $requestPath);
        while (str_contains($cleanPath, '/..') || str_starts_with($cleanPath, '..')) {
            $cleanPath = preg_replace('#(^|/)\.\.(/|$)#', '$1', $cleanPath);
        }
        $absFile = __DIR__ . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $cleanPath), DIRECTORY_SEPARATOR);
        if ($absFile !== '' && is_file($absFile) && is_readable($absFile)) {
            // Extensiones estáticas conocidas -> devolver archivo con MIME correcto
            $ext = strtolower(pathinfo($absFile, PATHINFO_EXTENSION));
            $staticExts = [
                'css'  => 'text/css; charset=utf-8',
                'js'   => 'application/javascript; charset=utf-8',
                'json' => 'application/json; charset=utf-8',
                'svg'  => 'image/svg+xml',
                'ico'  => 'image/x-icon',
                'png'  => 'image/png',
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                'avif' => 'image/avif',
                'bmp'  => 'image/bmp',
                'woff' => 'font/woff',
                'woff2'=> 'font/woff2',
                'ttf'  => 'font/ttf',
                'otf'  => 'font/otf',
                'eot'  => 'application/vnd.ms-fontobject',
                'pdf'  => 'application/pdf',
                'txt'  => 'text/plain; charset=utf-8',
                'xml'  => 'text/xml; charset=utf-8',
                'xsl'  => 'text/xsl',
                'csv'  => 'text/csv; charset=utf-8',
                'map'  => 'application/json; charset=utf-8',
            ];
            if (array_key_exists($ext, $staticExts)) {
                $mtime = @filemtime($absFile) ?: 0;
                // Cache 1h para assets con fingerprint/versionados: usar Last-Modified
                $etag = '"' . dechex(filesize($absFile)) . '-' . dechex($mtime) . '"';
                $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
                $ifModSince  = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
                $notModified = false;
                if ($ifNoneMatch !== '' && str_contains($ifNoneMatch, $etag)) {
                    $notModified = true;
                } elseif ($ifModSince !== '') {
                    $ifModTs = @strtotime($ifModSince) ?: 0;
                    if ($ifModTs > 0 && $mtime > 0 && $ifModTs >= $mtime) {
                        $notModified = true;
                    }
                }
                if ($notModified) {
                    http_response_code(304);
                    header('ETag: ' . $etag);
                    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
                    header('Cache-Control: public, max-age=3600, must-revalidate');
                    exit;
                }
                header('Content-Type: ' . $staticExts[$ext]);
                header('Content-Length: ' . filesize($absFile));
                header('ETag: ' . $etag);
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
                header('Cache-Control: public, max-age=3600, must-revalidate');
                header('X-Content-Type-Options: nosniff');
                // Gzip en dev si la extensión lo soporta y el cliente lo pide
                $acceptEnc = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
                if (in_array($ext, ['css','js','json','svg','xml','txt','csv','map'], true)
                    && extension_loaded('zlib')
                    && str_contains($acceptEnc, 'gzip')) {
                    header('Content-Encoding: gzip');
                    echo gzencode((string)file_get_contents($absFile), 6);
                } else {
                    readfile($absFile);
                }
                exit;
            }
        }
    }
}

// ---- Autoload PSR-4 simple ------------------------------------------
spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\Controllers\\' => __DIR__ . '/../app/Controllers/',
        'App\\Core\\'        => __DIR__ . '/../app/Core/',
        'App\\Models\\'      => __DIR__ . '/../app/Models/',
        'App\\Services\\'    => __DIR__ . '/../app/Services/',
        'App\\'              => __DIR__ . '/../app/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }
        $relative = substr($class, strlen($prefix));
        $file = rtrim($baseDir, '/\\') . '/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ---- Bootstrap ------------------------------------------------------
use App\Core\Session;
use App\Core\Router;
use App\Core\DB;

Session::start();
// Forzar conexión (para detectar error temprano)
try {
    DB::connection();
} catch (Throwable $e) {
    if ($config['app']['debug']) {
        echo '<h1>Error de configuración</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        exit;
    }
    http_response_code(503);
    echo '<h1>Sitio en mantenimiento</h1><p>Intenta nuevamente en unos minutos.</p>';
    exit;
}

// Helpers globales
require_once __DIR__ . '/../app/Core/helpers.php';

// ---- Rutas ----------------------------------------------------------
$router = new Router();

// ====== RUTAS PÚBLICAS ===============================================
$router->get('/',  'HomeController@index');
$router->get('/publicaciones',           'PublicationsController@index');
$router->get('/publicaciones/{slug}',    'PublicationsController@show');
$router->get('/publicacion/{slug}',      'PublicationsController@show'); // alias
$router->post('/contacto/enviar',        'ContactController@submit');
$router->get('/media/{id}',              'FileController@serve');
$router->get('/media/{id}/view',         function (array $p) {
    $_GET['mode'] = 'view';
    (new App\Controllers\FileController())->serve(['id' => $p['id'], 'mode' => 'view']);
});
$router->get('/media/{id}/download',     function (array $p) {
    (new App\Controllers\FileController())->serve(['id' => $p['id'], 'mode' => 'download']);
});

// ===== Publicidad (tracker / dismiss) =====
$router->get('/ads/{id}/view',            'AdsController@incrementView');
$router->get('/ads/{id}/click',           'AdsController@incrementClick');
$router->post('/ads/{id}/dismiss',        'AdsController@dismiss');
$router->post('/ads/dismiss/{id}',        'AdsController@dismiss');

// ====== RUTAS ADMIN ==================================================
$router->group(['prefix' => 'admin'], function (Router $r) {

    // Auth
    $r->get('/login',                     'AdminAuthController@loginForm');
    $r->post('/login',                    'AdminAuthController@loginSubmit', ['middleware' => ['csrf']]);
    $r->get('/logout',                    'AdminAuthController@logout');

    // Rutas protegidas
    $r->group(['middleware' => ['auth']], function (Router $r) {

        $r->get('/',                      'AdminDashboardController@index');
        $r->get('/dashboard',             'AdminDashboardController@index');

        // Publicaciones
        $r->group(['middleware' => ['editor_or_admin']], function (Router $r) {
            // Programas de impacto de la asociación
            $r->get('/radio/programas',                       'AdminRadioController@index');
            $r->get('/radio/programas/crear',                 'AdminRadioController@create');
            $r->post('/radio/programas/guardar',              'AdminRadioController@store', ['csrf']);
            $r->get('/radio/programas/{id}/editar',           'AdminRadioController@edit');
            $r->post('/radio/programas/{id}/actualizar',      'AdminRadioController@update', ['csrf']);
            $r->get('/radio/programas/{id}/toggle',           'AdminRadioController@toggle');
            $r->post('/radio/programas/{id}/eliminar',        'AdminRadioController@delete', ['csrf']);

            $r->get('/publicaciones',                         'AdminPublicationsController@index');
            $r->get('/publicaciones/crear',                   'AdminPublicationsController@create');
            $r->post('/publicaciones/guardar',                'AdminPublicationsController@store', ['csrf']);
            $r->get('/publicaciones/{id}/editar',             'AdminPublicationsController@edit');
            $r->post('/publicaciones/{id}/actualizar',        'AdminPublicationsController@update', ['csrf']);
            $r->get('/publicaciones/{id}/toggle',             'AdminPublicationsController@togglePublish');
            $r->post('/publicaciones/{id}/eliminar',          'AdminPublicationsController@delete', ['csrf']);
            $r->post('/media/{media_id}/eliminar',            'AdminPublicationsController@deleteFile', ['csrf']);

            // Categorías
            $r->get('/categorias',                            'AdminCategoriesController@index');
            $r->get('/categorias/crear',                      'AdminCategoriesController@create');
            $r->post('/categorias/guardar',                   'AdminCategoriesController@store', ['csrf']);
            $r->get('/categorias/{id}/editar',                'AdminCategoriesController@edit');
            $r->post('/categorias/{id}/actualizar',           'AdminCategoriesController@update', ['csrf']);
            $r->post('/categorias/{id}/eliminar',             'AdminCategoriesController@delete', ['csrf']);

            // Etiquetas
            $r->get('/etiquetas',                             'AdminTagsController@index');
            $r->get('/etiquetas/crear',                       'AdminTagsController@create');
            $r->post('/etiquetas/guardar',                    'AdminTagsController@store', ['csrf']);
            $r->get('/etiquetas/{id}/editar',                 'AdminTagsController@edit');
            $r->post('/etiquetas/{id}/actualizar',            'AdminTagsController@update', ['csrf']);
            $r->post('/etiquetas/{id}/eliminar',              'AdminTagsController@delete', ['csrf']);

            // Regiones
            $r->get('/regiones',                              'AdminRegionsController@index');
            $r->get('/regiones/crear',                        'AdminRegionsController@create');
            $r->post('/regiones/guardar',                     'AdminRegionsController@store', ['csrf']);
            $r->get('/regiones/{id}/editar',                  'AdminRegionsController@edit');
            $r->post('/regiones/{id}/actualizar',             'AdminRegionsController@update', ['csrf']);
            $r->post('/regiones/{id}/eliminar',               'AdminRegionsController@delete', ['csrf']);

            // Páginas
            $r->get('/paginas',                               'AdminPagesController@index');
            $r->get('/paginas/crear',                         'AdminPagesController@create');
            $r->post('/paginas/guardar',                      'AdminPagesController@store', ['csrf']);
            $r->get('/paginas/{id}/editar',                   'AdminPagesController@edit');
            $r->post('/paginas/{id}/actualizar',              'AdminPagesController@update', ['csrf']);
            $r->get('/paginas/{id}/toggle',                   'AdminPagesController@togglePublish');
            $r->post('/paginas/{id}/eliminar',                'AdminPagesController@delete', ['csrf']);

            // Publicidad / Anuncios
            $r->get('/publicidad',                            'AdminAdsController@index');
            $r->get('/publicidad/crear',                      'AdminAdsController@create');
            $r->post('/publicidad/guardar',                   'AdminAdsController@store', ['csrf']);
            $r->get('/publicidad/{id}/editar',                'AdminAdsController@edit');
            $r->post('/publicidad/{id}/actualizar',           'AdminAdsController@update', ['csrf']);
            $r->get('/publicidad/{id}/toggle',                'AdminAdsController@toggle');
            $r->post('/publicidad/{id}/eliminar',             'AdminAdsController@delete', ['csrf']);
            $r->get('/publicidad/estadisticas',               'AdminAdsController@stats');

            // Media / Upload inline para WYSIWYG
            $r->post('/media/upload-inline',                  'AdminMediaController@uploadInline', ['csrf']);

            // Auditoría visible a editor
            $r->get('/auditoria',                             'AdminAuditController@index');

            // Ajustes (solo admin)
            $r->group(['middleware' => ['admin']], function (Router $r) {
                $r->get('/ajustes',                            'AdminSettingsController@index');
                $r->post('/ajustes/guardar',                   'AdminSettingsController@update', ['csrf']);

                // Usuarios
                $r->get('/usuarios',                           'AdminUsersController@index');
                $r->get('/usuarios/crear',                     'AdminUsersController@create');
                $r->post('/usuarios/guardar',                  'AdminUsersController@store', ['csrf']);
                $r->get('/usuarios/{id}/editar',               'AdminUsersController@edit');
                $r->post('/usuarios/{id}/actualizar',          'AdminUsersController@update', ['csrf']);
                $r->post('/usuarios/{id}/eliminar',            'AdminUsersController@delete', ['csrf']);
            });
        });
    });
});

// ====== RUTAS PÚBLICAS: Páginas amigables (catch-all al final para no interceptar /admin) ======
$router->get('/pagina/{slug}',           'PagesController@show');
$router->get('/{slug}',                  'PagesController@show'); // /nosotros, /servicios, /equipo, /clientes, /contacto, /privacidad, etc.

// ---- Despacho -------------------------------------------------------
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI'] ?? '/';
$router->dispatch($method, $uri);
