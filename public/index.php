<?php
/**
 * AMIGOS POR EL MUNDO - Front Controller (RAÍZ public_html/)
 * Portal de Noticias - Front Controller único
 * Rutas relativas AJUSTADAS sin /../
 */

declare(strict_types=1);

// ==== CORRECCIÓN DESPLIEGUE (PORTABLE: RAÍZ local o public/ VPS) ====
// Detecta automáticamente la raíz del proyecto (donde están config/, app/, views/, storage/).
// - Desarrollo local: index.php está en RAÍZ    → config/ está al lado.
// - Producción VPS:  index.php está en public/  → config/ está en el directorio padre.
$_rm_isPublic = is_file(dirname(__DIR__) . '/config/config.php');
if ($_rm_isPublic) {
    $AMIGOS_BASE   = dirname(__DIR__);
    $AMIGOS_PUBLIC = __DIR__;
} else {
    $AMIGOS_BASE   = __DIR__;
    $AMIGOS_PUBLIC = __DIR__ . '/public';
}
define('AMIGOS_ROOT',       $AMIGOS_BASE);
define('AMIGOS_PUBLIC_PATH',$AMIGOS_PUBLIC);
unset($_rm_isPublic);
// Fin detección portable.

if (ob_get_level() > 0) { @ob_end_clean(); }
@ob_start();

if (PHP_VERSION_ID >= 80400) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
} else {
    error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
}
@ini_set('display_errors',         '0');
@ini_set('display_startup_errors', '0');
@ini_set('log_errors',             '1');

if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    if ($isHttps) { header('Strict-Transport-Security: max-age=31536000; includeSubDomains'); }
}

if (file_exists($AMIGOS_BASE . '/config/.env')) {
    require_once $AMIGOS_BASE . '/config/env.php';
}
$config = require $AMIGOS_BASE . '/config/config.php';
if ($config['app']['debug']) {
    if (ob_get_level() > 0) { @ob_end_clean(); }
    ini_set('display_errors',         '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors',         '0');
    error_reporting(0);
}
date_default_timezone_set($config['app']['timezone'] ?? 'America/Lima');

if (PHP_SAPI === 'cli-server') {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $requestPath = parse_url($requestUri, PHP_URL_PATH);
    if ($requestPath !== null && $requestPath !== '' && $requestPath !== '/') {
        $requestPath = '/' . ltrim($requestPath, '/');
        $cleanPath = str_replace(["\\", "\0"], '', $requestPath);
        while (str_contains($cleanPath, '/..') || str_starts_with($cleanPath, '..')) {
            $cleanPath = preg_replace('#(^|/)\.\.(/|$)#', '$1', $cleanPath);
        }
        $absFile = $AMIGOS_PUBLIC . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $cleanPath), DIRECTORY_SEPARATOR);
        if ($absFile !== '' && is_file($absFile) && is_readable($absFile)) {
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
                'mp3'  => 'audio/mpeg',
                'wav'  => 'audio/wav',
                'ogg'  => 'audio/ogg',
                'm4a'  => 'audio/mp4',
                'flac' => 'audio/flac',
                'mp4'  => 'video/mp4',
                'webm' => 'video/webm',
                'mov'  => 'video/quicktime',
            ];
            if (array_key_exists($ext, $staticExts)) {
                $mtime = @filemtime($absFile) ?: 0;
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

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\Controllers\\' => $GLOBALS['AMIGOS_BASE'] . '/app/Controllers/',
        'App\\Core\\'        => $GLOBALS['AMIGOS_BASE'] . '/app/Core/',
        'App\\Models\\'      => $GLOBALS['AMIGOS_BASE'] . '/app/Models/',
        'App\\Services\\'    => $GLOBALS['AMIGOS_BASE'] . '/app/Services/',
        'App\\'              => $GLOBALS['AMIGOS_BASE'] . '/app/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) { continue; }
        $relative = substr($class, strlen($prefix));
        $file = rtrim($baseDir, '/\\') . '/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) { require_once $file; return; }
    }
});

use App\Core\Session;
use App\Core\Router;
use App\Core\DB;

@mkdir($AMIGOS_BASE.'/storage/sessions', 0775, true);
@chmod($AMIGOS_BASE.'/storage/sessions', 0775);

if (is_dir($AMIGOS_BASE.'/storage/sessions') && is_writable($AMIGOS_BASE.'/storage/sessions')) {
    @ini_set('session.save_path', $AMIGOS_BASE.'/storage/sessions');
}
@ini_set('session.save_handler',   'files');
@ini_set('session.use_only_cookies', '1');
@ini_set('session.cookie_httponly',  '1');
@ini_set('session.cookie_samesite',  'Lax');
if ($isHttps) { @ini_set('session.cookie_secure', '1'); }

Session::start();

if (empty($_SESSION['__rm_sess_regen']) && !empty($_SESSION['user']['id'])) {
    @session_regenerate_id(true);
    $_SESSION['__rm_sess_regen'] = true;
}

try {
    DB::connection();
} catch (Throwable $e) {
    if (!empty($config['app']['debug'])) {
        if (ob_get_level() > 0) { @ob_end_clean(); }
        echo '<h1>Error de configuración DB</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        exit;
    }
    http_response_code(503);
    @ob_end_clean();
    echo '<h1>Sitio en mantenimiento</h1><p>Intenta nuevamente en unos minutos.</p>';
    exit;
}

require_once $AMIGOS_BASE . '/app/Core/helpers.php';

$router = new Router();

/* ================================================
   RUTAS PÚBLICAS - PORTAL AMIGOS POR EL MUNDO
   ================================================ */

// HOME
$router->get('/',  'HomeController@index');

// NOTICIAS (News) - listado y detalle
$router->get('/noticias',                  'NewsController@index');
$router->get('/noticias/pagina/{page}',    'NewsController@index');
$router->get('/archivo',                   'NewsController@archivo');
$router->get('/archivo/pagina/{page}',     'NewsController@archivo');
$router->get('/noticia/{slug}',            'NewsController@show');
$router->get('/noticias/{slug}',           'NewsController@show');

// SECCIONES (Sections)
$router->get('/seccion/{slug}',             'SectionController@show');
$router->get('/seccion/{slug}/pagina/{page}','SectionController@show');
$router->get('/categoria/{slug}',           'SectionController@show');

// MULTIMEDIA ESPECIALES
$router->get('/radio',                      'RadioController@index');
$router->get('/radio/programa/{slug}',      'RadioController@program');
$router->get('/en-vivo',                    'LiveStreamController@index');
$router->get('/tv',                         'LiveStreamController@index');
$router->get('/videos',                     'VideosController@index');
$router->get('/videos/pagina/{page}',       'VideosController@index');
$router->get('/video/{slug}',               'VideosController@show');
$router->get('/podcast',                    'PodcastController@index');
$router->get('/podcast/pagina/{page}',      'PodcastController@index');
$router->get('/podcast/{slug}',             'PodcastController@show');

// PÁGINAS ESTÁTICAS
$router->get('/pagina/{slug}',              'PagesController@show');

// BUSCADOR (reutiliza NewsController index que ya acepta ?q=
$router->get('/buscar',                      'NewsController@index');

// CONTACTO (si se necesita)
$router->post('/contacto/enviar',           'ContactController@submit');

// MEDIA FILES (serve/download)
$router->get('/media/{id}',                 'FileController@serve');
$router->get('/media/{id}/download',        function (array $p) {
    (new App\Controllers\FileController())->serve(['id' => $p['id'], 'mode' => 'download']);
});

// ADS
$router->get('/ads/{id}/view',              'AdsController@incrementView');
$router->get('/ads/{id}/click',             'AdsController@incrementClick');
$router->post('/ads/{id}/dismiss',          'AdsController@dismiss');
$router->post('/ads/dismiss/{id}',          'AdsController@dismiss');

// FLYERS (track clicks)
$router->get('/flyer/{id}/click',           function (array $p) {
    $f = App\Models\Flyer::find((int)$p['id']);
    if ($f) { App\Models\Flyer::incrementClicks((int)$f['id']); }
    $url = !empty($f['link_url']) ? $f['link_url'] : '/';
    redirect($url, 302);
});

/* ================================================
   RUTAS ADMIN - PANEL AMIGOS POR EL MUNDO
   ================================================ */
$router->group(['prefix' => 'admin'], function (Router $r) {

    // AUTH (sin middleware)
    $r->get('/login',                     'AdminAuthController@loginForm');
    $r->post('/login',                    'AdminAuthController@loginSubmit', ['middleware' => ['csrf']]);
    $r->get('/logout',                    'AdminAuthController@logout');

    $r->group(['middleware' => ['auth']], function (Router $r) {

        // DASHBOARD (cualquier usuario logueado)
        $r->get('/',                      'AdminDashboardController@index');
        $r->get('/dashboard',             'AdminDashboardController@index');

        // EDITOR O ADMIN: gestión de contenido
        $r->group(['middleware' => ['editor_or_admin']], function (Router $r) {

            // === NOTICIAS ===
            $r->get('/noticias',                              'AdminNewsController@index');
            $r->get('/noticias/crear',                        'AdminNewsController@create');
            $r->post('/noticias/guardar',                     'AdminNewsController@store', ['csrf']);
            $r->get('/noticias/{id}/editar',                  'AdminNewsController@edit');
            $r->post('/noticias/{id}/actualizar',             'AdminNewsController@update', ['csrf']);
            $r->get('/noticias/{id}/toggle',                  'AdminNewsController@togglePublish');
            $r->post('/noticias/{id}/eliminar',               'AdminNewsController@delete', ['csrf']);
            $r->post('/noticias/media/{media_id}/eliminar',   'AdminNewsController@deleteFile', ['csrf']);

            // === SECCIONES ===
            $r->get('/secciones',                             'AdminSectionsController@index');
            $r->get('/secciones/crear',                       'AdminSectionsController@create');
            $r->post('/secciones/guardar',                    'AdminSectionsController@store', ['csrf']);
            $r->get('/secciones/{id}/editar',                 'AdminSectionsController@edit');
            $r->post('/secciones/{id}/actualizar',            'AdminSectionsController@update', ['csrf']);
            $r->get('/secciones/{id}/toggle',                 'AdminSectionsController@toggle');
            $r->post('/secciones/{id}/eliminar',              'AdminSectionsController@delete', ['csrf']);

            // === TAGS ===
            $r->get('/etiquetas',                             'AdminTagsController@index');
            $r->get('/etiquetas/crear',                       'AdminTagsController@create');
            $r->post('/etiquetas/guardar',                    'AdminTagsController@store', ['csrf']);
            $r->get('/etiquetas/{id}/editar',                 'AdminTagsController@edit');
            $r->post('/etiquetas/{id}/actualizar',            'AdminTagsController@update', ['csrf']);
            $r->post('/etiquetas/{id}/eliminar',              'AdminTagsController@delete', ['csrf']);

            // === VIDEOS ===
            $r->get('/videos',                                'AdminVideosController@index');
            $r->get('/videos/crear',                          'AdminVideosController@create');
            $r->post('/videos/guardar',                       'AdminVideosController@store', ['csrf']);
            $r->get('/videos/{id}/editar',                    'AdminVideosController@edit');
            $r->post('/videos/{id}/actualizar',               'AdminVideosController@update', ['csrf']);
            $r->get('/videos/{id}/toggle',                    'AdminVideosController@togglePublish');
            $r->post('/videos/{id}/eliminar',                 'AdminVideosController@delete', ['csrf']);

            // === PROGRAMAS DE IMPACTO ===
            $r->get('/radio/programas',                       'AdminRadioController@index');
            $r->get('/radio/programas/crear',                 'AdminRadioController@create');
            $r->post('/radio/programas/guardar',              'AdminRadioController@store', ['csrf']);
            $r->get('/radio/programas/{id}/editar',           'AdminRadioController@edit');
            $r->post('/radio/programas/{id}/actualizar',      'AdminRadioController@update', ['csrf']);
            $r->get('/radio/programas/{id}/toggle',           'AdminRadioController@toggle');
            $r->post('/radio/programas/{id}/eliminar',        'AdminRadioController@delete', ['csrf']);

            // === PÁGINAS ESTÁTICAS ===
            $r->get('/paginas',                               'AdminPagesController@index');
            $r->get('/paginas/crear',                         'AdminPagesController@create');
            $r->post('/paginas/guardar',                      'AdminPagesController@store', ['csrf']);
            $r->get('/paginas/{id}/editar',                   'AdminPagesController@edit');
            $r->post('/paginas/{id}/actualizar',              'AdminPagesController@update', ['csrf']);
            $r->get('/paginas/{id}/toggle',                   'AdminPagesController@togglePublish');
            $r->post('/paginas/{id}/eliminar',                'AdminPagesController@delete', ['csrf']);

            // === PUBLICIDAD ===
            $r->get('/publicidad',                            'AdminAdsController@index');
            $r->get('/publicidad/crear',                      'AdminAdsController@create');
            $r->post('/publicidad/guardar',                   'AdminAdsController@store', ['csrf']);
            $r->get('/publicidad/{id}/editar',                'AdminAdsController@edit');
            $r->post('/publicidad/{id}/actualizar',           'AdminAdsController@update', ['csrf']);
            $r->get('/publicidad/{id}/toggle',                'AdminAdsController@toggle');
            $r->post('/publicidad/{id}/eliminar',             'AdminAdsController@delete', ['csrf']);
            $r->get('/publicidad/estadisticas',               'AdminAdsController@stats');

            // === MEDIA INLINE WYSIWYG ===
            $r->post('/media/upload-inline',                  'AdminMediaController@uploadInline', ['csrf']);

            // === AUDITORÍA ===
            $r->get('/auditoria',                             'AdminAuditController@index');

            // === ADMIN ONLY: Ajustes y Usuarios ===
            $r->group(['middleware' => ['admin']], function (Router $r) {
                $r->get('/ajustes',                            'AdminSettingsController@index');
                $r->post('/ajustes/guardar',                   'AdminSettingsController@update', ['csrf']);

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

// Páginas estáticas al final (catch-all suave)
$router->get('/{slug}',                      'PagesController@show');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI'] ?? '/';

try {
    $router->dispatch($method, $uri);
} catch (Throwable $e) {
    if (!empty($config['app']['debug'])) {
        if (ob_get_level() > 0) { @ob_end_clean(); }
        echo '<pre style="background:#fff1f2;color:#991b1b;padding:24px;border:1px solid #fecaca;border-radius:10px;font-family:monospace;white-space:pre-wrap;word-wrap:break-word;">';
        echo '<b style="color:#7f1d1d;font-size:1.1rem;">❌ ERROR FATAL (APP_DEBUG=true — temporal)</b><hr style="border-color:#fecaca;">';
        echo '<b>Tipo:</b>     '.htmlspecialchars(get_class($e)).'<br>';
        echo '<b>Mensaje:</b> '.htmlspecialchars($e->getMessage()).'<br>';
        echo '<b>Archivo:</b> '.htmlspecialchars($e->getFile()).':'.$e->getLine().'<br><br>';
        echo '<b>Stack trace:</b><br>'.htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
        exit;
    } else {
        http_response_code(500);
        @ob_end_clean();
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Error</title>';
        echo '<style>body{font-family:Inter,Arial,sans-serif;background:#f8fafc;color:#0f172a;display:flex;align-items:center;justify-content:center;min-height:100vh;}';
        echo '.card{max-width:520px;background:#fff;border-radius:20px;padding:40px;box-shadow:0 20px 60px -20px rgba(15,23,42,.25);}';
        echo 'h1{margin:0 0 10px;color:#0f172a;font-size:1.6rem;}p{color:#475569;margin:0;}</style></head>';
        echo '<body><div class="card"><h1>😵 Ocurrió un error inesperado</h1><p>Intenta nuevamente en unos minutos. Si el problema persiste, contacta al administrador.</p></div></body></html>';
        exit;
    }
}

if (PHP_SESSION_ACTIVE === session_status() && !empty($_SESSION['user']) && !headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}
if (ob_get_level() > 0) { @ob_end_flush(); }
