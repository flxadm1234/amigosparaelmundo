<?php
/**
 * Helpers globales - se carga en bootstrap
 */

declare(strict_types=1);

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('h')) {
    function h(mixed $value): string
    {
        return e($value);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        static $base = null;
        if ($base === null) {
            $cfg = require dirname(__DIR__, 2) . '/config/config.php';
            $base = rtrim($cfg['app']['url'], '/');
        }
        if ($path === '') {
            return $base . '/';
        }
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('upload_url')) {
    function upload_url(string $path): string
    {
        if ($path === '') { return ''; }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }
        $normalized = ltrim($path, '/');
        if (str_starts_with(strtolower($normalized), 'assets/')) {
            return url($normalized);
        }
        if (str_starts_with($normalized, 'uploads/')) {
            return url($normalized);
        }
        return url('uploads/' . $normalized);
    }
}

if (!function_exists('news_url')) {
    function news_url(mixed $noticiaOrSlug = null, ?string $slug = null): string
    {
        if (is_string($noticiaOrSlug) && $slug === null) {
            $slug = $noticiaOrSlug;
            $noticiaOrSlug = null;
        }
        $noticia = is_array($noticiaOrSlug) ? $noticiaOrSlug : null;
        $s = $slug ?? ($noticia['slug'] ?? '');
        if ($s === '') { return url('/noticias'); }
        return url('/noticia/' . ltrim($s, '/'));
    }
}

if (!function_exists('section_url')) {
    function section_url(mixed $seccionOrSlug = null, ?string $slug = null): string
    {
        if (is_string($seccionOrSlug) && $slug === null) {
            $slug = $seccionOrSlug;
            $seccionOrSlug = null;
        }
        $seccion = is_array($seccionOrSlug) ? $seccionOrSlug : null;
        $s = $slug ?? ($seccion['slug'] ?? '');
        if ($s === '') { return url('/'); }
        if ($s === 'radio')    return url('/radio');
        if ($s === 'videos')   return url('/videos');
        if ($s === 'podcast')  return url('/podcast');
        return url('/seccion/' . ltrim($s, '/'));
    }
}

if (!function_exists('video_url')) {
    function video_url(mixed $videoOrSlug = null, ?string $slug = null): string
    {
        if (is_string($videoOrSlug) && $slug === null) {
            $slug = $videoOrSlug;
            $videoOrSlug = null;
        }
        $video = is_array($videoOrSlug) ? $videoOrSlug : null;
        $s = $slug ?? ($video['slug'] ?? '');
        if ($s === '') { return url('/videos'); }
        return url('/video/' . ltrim($s, '/'));
    }
}

if (!function_exists('podcast_url')) {
    function podcast_url(mixed $epOrSlug = null, ?string $slug = null): string
    {
        if (is_string($epOrSlug) && $slug === null) {
            $slug = $epOrSlug;
            $epOrSlug = null;
        }
        $ep = is_array($epOrSlug) ? $epOrSlug : null;
        $s = $slug ?? ($ep['slug'] ?? '');
        if ($s === '') { return url('/podcast'); }
        return url('/podcast/' . ltrim($s, '/'));
    }
}

if (!function_exists('flyer_click_url')) {
    function flyer_click_url(?array $flyer): string
    {
        if (empty($flyer)) { return '#'; }
        $id = (int)($flyer['id'] ?? 0);
        return url('/flyer/' . $id . '/click');
    }
}

if (!function_exists('cover_image_url')) {
    function cover_image_url(?array $item, string $defaultText = '', int $w = 1200, int $h = 675): string
    {
        $path = '';
        if (!empty($item['cover_image_path'])) $path = (string)$item['cover_image_path'];
        elseif (!empty($item['thumbnail_path'])) $path = (string)$item['thumbnail_path'];
        elseif (!empty($item['image_path'])) $path = (string)$item['image_path'];
        if ($path !== '') {
            $pub = upload_url($path);
            if ($pub !== '') return $pub;
        }
        return img_or_placeholder('', ['w'=>$w,'h'=>$h,'text'=>$defaultText]);
    }
}

if (!function_exists('render_flyers_carousel')) {
    function render_flyers_carousel(string $position = 'home-carousel', int $max = 8): void
    {
        static $uidCounter = 0;
        $uidCounter = ($uidCounter ?? 0) + 1;
        $uid = 'rmFlyersCarousel_' . (string)$uidCounter;

        try {
            $flyers = \App\Models\Flyer::getActiveByPosition($position, $max);
        } catch (\Throwable) { $flyers = []; }
        if (empty($flyers)) return;
        $count = count($flyers);
        echo '<div class="flyers-carousel" id="' . $uid . '" data-flyer-carousel="' . $uid . '" data-position="' . e($position) . '" data-interval="6000" aria-roledescription="carousel" aria-label="Banners promocionales ' . e($position) . '">';
        echo '<div class="flyers-track-wrap" aria-live="polite"><div class="flyers-track">';
        foreach ($flyers as $i => $f) {
            $id = (int)$f['id'];
            \App\Models\Flyer::incrementViews($id);
            $title = $f['title'] ?? '';
            $sub   = $f['subtitle'] ?? '';
            $img   = upload_url((string)$f['image_path']);
            $link  = !empty($f['link_url']) ? flyer_click_url($f) : 'javascript:void(0)';
            $tgt   = ($f['link_target'] ?? '_self') === '_blank' ? ' target="_blank" rel="noopener sponsored nofollow"' : '';
            $active = $i === 0 ? ' active' : '';
            $slideLabelledBy = $uid . '__title_' . $i;
            echo '<div class="flyer-slide' . $active . '" data-flyer="' . $id . '" data-flyer-idx="' . $i . '" role="group" aria-roledescription="slide" aria-label="' . ($i + 1) . ' de ' . $count . '"' . ($title !== '' ? ' aria-labelledby="' . $slideLabelledBy . '"' : '') . '>';
            echo '<a href="' . e($link) . '"' . $tgt . ' class="flyer-link" aria-label="' . e($title ?: 'Banner promocional') . '">';
            echo '<img src="' . e($img) . '" alt="' . e($title ?: 'Banner') . '" class="flyer-img" loading="lazy" decoding="async">';
            if ($title !== '' || $sub !== '') {
                echo '<div class="flyer-caption"><div class="flyer-caption-inner">';
                if ($title !== '') echo '<h3 class="flyer-title" id="' . $slideLabelledBy . '">' . e($title) . '</h3>';
                if ($sub !== '')   echo '<p class="flyer-subtitle">' . e($sub) . '</p>';
                echo '</div></div>';
            }
            echo '</a></div>';
        }
        echo '</div></div>';
        if ($count > 1) {
            echo '<div class="flyer-controls-wrap" aria-hidden="false">';
            echo '<button type="button" class="flyer-nav flyer-prev" aria-controls="' . $uid . '" aria-label="Anterior imagen del carrusel" data-flyer-carousel="' . $uid . '" data-flyer-dir="prev" tabindex="0" title="Anterior (←)">';
            echo '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>';
            echo '</button>';
            echo '<button type="button" class="flyer-nav flyer-next" aria-controls="' . $uid . '" aria-label="Siguiente imagen del carrusel" data-flyer-carousel="' . $uid . '" data-flyer-dir="next" tabindex="0" title="Siguiente (→)">';
            echo '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>';
            echo '</button>';
            echo '</div>';
            echo '<div class="flyer-dots" role="tablist" aria-label="Seleccionar imagen del carrusel" data-flyer-carousel="' . $uid . '">';
            foreach ($flyers as $i => $f) {
                $act = $i === 0 ? ' active' : '';
                $t = trim((string)($f['title'] ?? ''));
                $lab = 'Ir a imagen ' . ($i + 1) . ' de ' . $count . ($t !== '' ? ' — ' . $t : '');
                echo '<button type="button" class="flyer-dot' . $act . '" role="tab" aria-selected="' . ($i === 0 ? 'true' : 'false') . '" aria-label="' . e($lab) . '" data-flyer-carousel="' . $uid . '" data-flyer-idx="' . $i . '" tabindex="' . ($i === 0 ? '0' : '-1') . '"></button>';
            }
            echo '</div>';
        }
        echo '</div>';
    }
}

if (!function_exists('render_flyers_grid')) {
    function render_flyers_grid(string $position = 'home-grid', int $max = 6): void
    {
        try {
            $flyers = \App\Models\Flyer::getActiveByPosition($position, $max);
        } catch (\Throwable) { $flyers = []; }
        if (empty($flyers)) return;
        echo '<div class="flyers-grid row g-3" data-position="' . e($position) . '">';
        $cols = match (true) {
            count($flyers) >= 6 => 'col-lg-4 col-md-6 col-sm-6',
            count($flyers) >= 4 => 'col-lg-3 col-md-6 col-sm-6',
            count($flyers) >= 2 => 'col-md-6 col-sm-6',
            default => 'col-12',
        };
        foreach ($flyers as $f) {
            $id = (int)$f['id'];
            \App\Models\Flyer::incrementViews($id);
            $title = $f['title'] ?? '';
            $sub   = $f['subtitle'] ?? '';
            $img   = upload_url((string)$f['image_path']);
            $link  = !empty($f['link_url']) ? flyer_click_url($f) : 'javascript:void(0)';
            $tgt   = ($f['link_target'] ?? '_self') === '_blank' ? 'target="_blank" rel="noopener sponsored nofollow"' : '';
            echo '<div class="' . $cols . '">';
            echo '<a href="' . e($link) . '" ' . $tgt . ' class="flyer-card" data-flyer="' . $id . '" aria-label="' . e($title ?: 'Banner') . '">';
            echo '<div class="flyer-card-img-wrap">';
            echo '<img src="' . e($img) . '" alt="' . e($title ?: 'Banner') . '" class="flyer-card-img" loading="lazy" decoding="async">';
            echo '</div>';
            if ($title !== '' || $sub !== '') {
                echo '<div class="flyer-card-body">';
                if ($title !== '') echo '<h4 class="flyer-card-title">' . e($title) . '</h4>';
                if ($sub !== '')   echo '<p class="flyer-card-text">' . e($sub) . '</p>';
                echo '</div>';
            }
            echo '</a></div>';
        }
        echo '</div>';
    }
}

if (!function_exists('news_card')) {
    function news_card(array $n, string $size = 'md'): string
    {
        $title = (string)($n['title'] ?? '');
        $excerpt = trim((string)($n['excerpt'] ?? ''));
        if ($excerpt === '') {
            $excerpt = mb_substr(strip_tags((string)($n['content_html'] ?? '')), 0, 120);
            if (mb_strlen($excerpt) >= 120) $excerpt .= '…';
        }
        $sectionName = $n['section_name'] ?? '';
        $sectionSlug = $n['section_slug'] ?? '';
        $cover = cover_image_url($n, $title);
        $date  = format_date_spanish($n['publish_date'] ?? null);
        $views = (int)($n['views_count'] ?? 0);
        $breaking = !empty($n['is_breaking']) ? '<span class="news-card-badge news-card-badge-breaking">ÚLTIMA HORA</span>' : '';
        $featured = !empty($n['is_featured']) ? ' news-card--featured' : '';
        $link = news_url($n);
        $categoryBadge = '';
        if ($sectionName !== '') {
            $catHref = $sectionSlug ? e(section_url(null, $sectionSlug)) : 'javascript:void(0)';
            $categoryBadge = '<a href="' . $catHref . '" class="news-card-badge news-card-badge-category" aria-label="Sección ' . e($sectionName) . '">' . e($sectionName) . '</a>';
        }
        $titleSafe = e($title);
        return <<<HTML
        <article class="news-card news-card--{$size}{$featured}">
          <div class="news-card-media">
            <a href="{$link}" class="news-card-media-link" aria-label="{$titleSafe}">
              <img src="{$cover}" alt="{$titleSafe}" class="news-card-img" loading="lazy" decoding="async">
            </a>
            <div class="news-card-badges" aria-label="Etiquetas de la noticia">
              {$breaking}
            </div>
            <div class="news-card-media-footer" aria-hidden="true">
              {$categoryBadge}
            </div>
          </div>
          <div class="news-card-body">
            <h3 class="news-card-title"><a href="{$link}">{$titleSafe}</a></h3>
            <p class="news-card-excerpt">{$excerpt}</p>
            <div class="news-card-meta">
              <span class="news-card-date">📅 {$date}</span>
              <span class="news-card-views">👁 {$views}</span>
            </div>
          </div>
        </article>
        HTML;
    }
}

if (!function_exists('redirect')) {
    /**
     * ✅ REDIRECT LIMPIO (Fix: Headers already sent / Botón Eliminar no redirige)
     * - Limpia TODOS los buffers de salida (BOM UTF-8, whitespaces, warnings)
     * - Envía headers NO-CACHE para redirects POST/PRG Pattern
     * - Usa 303 See Other por defecto (evita re-submit formulario)
     * - Fallback meta refresh 0s + link manual si el navegador no sigue Location
     *
     * @param string $to    URL destino (relativa o absoluta)
     * @param int    $status Código HTTP (default 303 See Other PRG Pattern)
     * @return never         No regresa (termina ejecución)
     */
    function redirect(string $to, int $status = 303): never
    {
        // 1) VACIAR BUFFERS — ELIMINA cualquier salida previa al header Location
        while (ob_get_level() > 0) { @ob_end_clean(); }

        // 2) HEADERS NO-CACHE para redirects (navegador no guarde cache)
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

        // 3) Resolver URL (relativa -> absoluta, permite rutas externas válidas)
        $absTo = (str_starts_with($to, 'http://') || str_starts_with($to, 'https://') || str_starts_with($to, '/'))
            ? $to
            : url($to);

        // 4) Enviar header Location con código HTTP seleccionado
        header('Location: ' . $absTo, true, $status);

        // 5) FALLBACK si el navegador no sigue el header (meta refresh + enlace manual)
        $safeTo = htmlspecialchars($absTo, ENT_QUOTES, 'UTF-8');
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
        echo '<meta http-equiv="refresh" content="0;url=' . $safeTo . '">';
        echo '<meta name="robots" content="noindex,nofollow">';
        echo '<title>Redirigiendo...</title>';
        echo '<style>body{font-family:Inter,Arial,sans-serif;background:#f8fafc;color:#0f172a;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;}';
        echo '.card{max-width:480px;background:#fff;border-radius:20px;padding:40px;text-align:center;box-shadow:0 20px 60px -20px rgba(15,23,42,.25);}';
        echo '.dot{display:inline-block;width:14px;height:14px;border-radius:50%;background:#0ea5e9;margin-right:10px;animation:pulse 1s infinite;}';
        echo 'h1{margin:0 0 14px;font-size:1.45rem;color:#0f172a;}p{color:#475569;margin:0;line-height:1.6;}a{color:#2563eb;}';
        echo '@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.15);}}</style></head>';
        echo '<body><div class="card"><h1><span class="dot" aria-hidden="true"></span>Redirigiendo...</h1>';
        echo '<p>Si no eres redirigido automáticamente, haz <a href="' . $safeTo . '">clic aquí</a>.</p></div></body></html>';
        exit(0);
    }
}

if (!function_exists('redirect_back')) {
    /**
     * ✅ REDIRECT_BACK limpio (mismo patrón de buffers + no-cache)
     */
    function redirect_back(string $fallback = '/', array $with = []): never
    {
        foreach ($with as $k => $v) {
            \App\Core\Session::flash($k, $v);
        }
        $to = $_SERVER['HTTP_REFERER'] ?? url($fallback);
        redirect($to, 303);
    }
}

if (!function_exists('_imm_cacheBust')) {
    /**
     * ✅ CACHE BUSTING AUTOMÁTICO para assets CSS/JS
     * Lee la FECHA DE ÚLTIMA MODIFICACIÓN real del archivo (filemtime)
     * y genera un hash hex único para agregar como query param ?v=HASH
     *
     * USO EN VIEWS HEAD:
     *   <link rel="stylesheet" href="<?= asset('css/app.css') . _imm_cacheBust('/assets/css/app.css') ?>">
     *
     * @param string $publicRelPath Ruta RELATIVA desde /public/ (ej: '/assets/css/app.css')
     * @return string               Query param listo para concatenar (?v=HASH o ?v=YYYYMMDD fallback)
     */
    function _imm_cacheBust(string $publicRelPath): string
    {
        $abs = dirname(__DIR__, 2) . '/public' . $publicRelPath;
        if (is_file($abs) && false !== ($mt = @filemtime($abs))) {
            return '?v=' . dechex($mt);
        }
        return '?v=' . date('Ymd');
    }
}

if (!function_exists('resolve_upload_abs_path')) {
    /**
     * ✅ RESUELVE RUTA FÍSICA ABSOLUTA REAL para archivos uploads.
     *
     * ⚠️ FIX DEL PROBLEMA CRÍTICO:
     * stored_path en DB = /uploads/publications/14/archivo.pdf  (URL pública con Alias)
     * base_dir config  = /public/uploads                      (carpeta FÍSICA REAL /public/uploads)
     * Si concatenábamos DIRECTAMENTE: /public/uploads + /uploads/publications/xx = ❌ /public/uploads/uploads/... (DUPLICADO)
     *
     * Esta función:
     *  1) Elimina el prefijo /uploads/ del stored_path (quita parte URL pública)
     *  2) Luego concatena al base_dir (RUTA FÍSICA REAL del disco)
     *  3) Prueba la ruta FINAL, y si NO existe, prueba ambos paths físicos (compatibilidad doble)
     *
     * @param string $storedPath Valor de media_files.stored_path (ej: "/uploads/publications/14/a.pdf")
     * @return string            Ruta absoluta FÍSICA del archivo en el disco (no URL)
     */
    function resolve_upload_abs_path(string $storedPath): string
    {
        static $cfg = null;
        if ($cfg === null) {
            $cfg = require dirname(__DIR__, 2) . '/config/config.php';
        }
        $baseDir     = rtrim((string)($cfg['upload']['base_dir'] ?? ''), '/\\');
        $publicPref  = rtrim((string)($cfg['upload']['public_url'] ?? '/uploads'), '/\\');

        $clean = ltrim((string)$storedPath, '/\\');

        // Quitar /uploads/ del stored_path (evitar duplicado /uploads/uploads/)
        $prefCheck = trim($publicPref, '/\\') . '/';
        if (strcasecmp(substr($clean, 0, strlen($prefCheck)), $prefCheck) === 0) {
            $clean = substr($clean, strlen($prefCheck));
        }
        $clean = ltrim($clean, '/\\');

        // RUTA FÍSICA OFICIAL (correcta): /home/.../public_html/public/uploads/publications/14/...
        $rutaCorrecta = $baseDir . ($clean !== '' ? '/' . $clean : '');
        if (is_file($rutaCorrecta)) {
            return $rutaCorrecta;
        }

        // FALLBACK 1: Sin base_dir /public/ (archivos antiguos en /public_html/uploads/)
        $rootDir = dirname(__DIR__, 2);
        $rutaAlt1 = $rootDir . '/' . ltrim((string)$storedPath, '/');
        if (is_file($rutaAlt1)) {
            return $rutaAlt1;
        }

        // FALLBACK 2: stored_path empieza directamente por /public/uploads
        $rutaAlt2 = $rootDir . '/public/' . ltrim($clean, '/');
        if (is_file($rutaAlt2)) {
            return $rutaAlt2;
        }

        // Si no existe ninguno, devolver la ruta OFICIAL para que el llamador maneje el error
        return $rutaCorrecta;
    }
}

if (!function_exists('resolve_upload_public_url')) {
    /**
     * ✅ RESUELVE URL PÚBLICA (navegador) para stored_path de media_files.
     * Compatibilidad doble Alias .htaccess.
     *
     * @param string $storedPath Valor de media_files.stored_path
     * @return string            URL pública (empieza por / o https://)
     */
    function resolve_upload_public_url(string $storedPath): string
    {
        if ($storedPath === '') { return ''; }
        if (str_starts_with($storedPath, 'http://') || str_starts_with($storedPath, 'https://')) {
            return $storedPath;
        }
        // Asegurar que empiece por /uploads/ (Alias .htaccess /uploads/* → /public/uploads/*)
        $p = '/' . ltrim($storedPath, '/');
        if (!str_starts_with($p, '/uploads/') && !str_starts_with($p, '/assets/') && !str_starts_with($p, '/fonts/')) {
            $p = '/uploads' . $p;
        }
        return $p;
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        $old = \App\Core\Session::flash('_old_input') ?? [];
        return $old[$key] ?? $default;
    }
}

if (!function_exists('config_val')) {
    function config_val(string $dot, mixed $default = null): mixed
    {
        static $cfg = null;
        if ($cfg === null) {
            $cfg = require dirname(__DIR__, 2) . '/config/config.php';
        }
        $parts = explode('.', $dot);
        $v = $cfg;
        foreach ($parts as $p) {
            if (is_array($v) && array_key_exists($p, $v)) {
                $v = $v[$p];
            } else {
                return $default;
            }
        }
        return $v;
    }
}

if (!function_exists('str_slug')) {
    function str_slug(string $text, string $divider = '-'): string
    {
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, $divider);
        $text = preg_replace('~-+~', $divider, $text);
        $text = strtolower($text);
        if ($text === '') {
            return 'n-a';
        }
        return $text;
    }
}

if (!function_exists('format_bytes')) {
    function format_bytes(int $bytes): string
    {
        $units = ['B','KB','MB','GB','TB'];
        $b = max($bytes, 0);
        $pow = floor(($b ? log($b) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $b /= (1 << (10 * $pow));
        return round($b, 2) . ' ' . $units[$pow];
    }
}

if (!function_exists('format_date')) {
    function format_date(string|DateTimeInterface|null $date, string $format = 'd/m/Y'): string
    {
        if ($date === null || $date === '') {
            return '';
        }
        if ($date instanceof DateTimeInterface) {
            return $date->format($format);
        }
        $dt = date_create($date);
        return $dt ? $dt->format($format) : '';
    }
}

if (!function_exists('format_date_spanish')) {
    function format_date_spanish(string|DateTimeInterface|null $date): string
    {
        if (!$date) {
            return '';
        }
        $dt = $date instanceof DateTimeInterface ? $date : date_create($date);
        if (!$dt) {
            return '';
        }
        $months = ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        return $dt->format('d') . ' de ' . $months[(int)$dt->format('n')] . ' de ' . $dt->format('Y');
    }
}

if (!function_exists('is_active_route')) {
    function is_active_route(string $prefix, string $class = 'active'): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = $uri === null ? '/' : $uri;
        if ($prefix === '/' && $uri === '/') {
            return $class;
        }
        if ($prefix !== '/' && str_starts_with($uri, $prefix)) {
            return $class;
        }
        return '';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = \App\Core\CSRF::token();
        $name  = config_val('security.csrf_token_name', '_csrf_token');
        return '<input type="hidden" name="' . e($name) . '" value="' . e($token) . '">';
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Core\CSRF::token();
    }
}

if (!function_exists('csrf_check')) {
    function csrf_check(?string $token): bool
    {
        return \App\Core\CSRF::check($token);
    }
}

if (!function_exists('csrf_token_header_meta')) {
    function csrf_token_header_meta(): string
    {
        $name  = config_val('security.csrf_token_name', '_csrf_token');
        $token = csrf_token();
        return '<meta name="' . e($name) . '" content="' . e($token) . '">';
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return \App\Core\Auth::user();
    }
}

if (!function_exists('auth_check')) {
    function auth_check(): bool
    {
        return \App\Core\Auth::check();
    }
}

if (!function_exists('auth_is')) {
    function auth_is(string $role_slug): bool
    {
        return \App\Core\Auth::is($role_slug);
    }
}

if (!function_exists('can')) {
    function can(string $action): bool
    {
        return \App\Core\Auth::can($action);
    }
}

if (!function_exists('app_setting')) {
    function app_setting(string $key, string $default = ''): string
    {
        return \App\Models\SiteSetting::get($key, $default);
    }
}

if (!function_exists('view_path')) {
    function view_path(string $view): string
    {
        return dirname(__DIR__, 2) . '/views/' . ltrim(str_replace('.', '/', $view), '/') . '.php';
    }
}

if (!function_exists('public_image_path')) {
    /**
     * Convierte URL relativa a ruta absoluta en FS
     */
    function public_image_path(string $url): string
    {
        $base = dirname(__DIR__, 2) . '/public';
        return $base . '/' . ltrim($url, '/');
    }
}

if (!function_exists('img_or_placeholder')) {
    /**
     * Devuelve una URL segura: si el archivo no existe, devuelve un SVG placeholder inline.
     * $opts: [ 'w'=>1200, 'h'=>675, 'text'=>'', 'bg'=>null, 'fg'=>null, 'icon'=>null ]
     */
    function img_or_placeholder(?string $url, array $opts = []): string
    {
        $w = (int)($opts['w'] ?? 1200);
        $h = (int)($opts['h'] ?? 675);
        $text = $opts['text'] ?? '';
        $bg = $opts['bg'] ?? null;
        $fg = $opts['fg'] ?? '#ffffff';
        if ($url === null || $url === '') {
            return placeholder_svg($w, $h, $text, $bg, $fg);
        }
        $fs = public_image_path($url);
        if (file_exists($fs)) {
            return $url;
        }
        // Fallback a SVG en /assets/img/ si hay extensión jpg/jpeg/png
        $info = pathinfo($url);
        $dir  = $info['dirname'] ?? '';
        $base = $info['filename'] ?? 'img';
        $svgPath = rtrim($dir, '/') . '/' . $base . '.svg';
        $svgFs   = public_image_path($svgPath);
        if (file_exists($svgFs)) {
            return $svgPath;
        }
        return placeholder_svg($w, $h, $text, $bg, $fg);
    }
}

if (!function_exists('placeholder_svg')) {
    function placeholder_svg(int $w = 1200, int $h = 675, string $text = '', ?string $bg = null, string $fg = '#ffffff', ?float $bgOpacity = null): string
    {
        $palettes = [
            ['#1e3a8a','#2563eb','#0ea5e9'],
            ['#7c3aed','#2563eb','#0ea5e9'],
            ['#047857','#10b981','#06b6d4'],
            ['#b91c1c','#ef4444','#f97316'],
            ['#b45309','#f59e0b','#f97316'],
            ['#0369a1','#0ea5e9','#06b6d4'],
        ];
        if ($bg === null) {
            $seed = abs(crc32($text . $w . $h)) % count($palettes);
            $pal = $palettes[$seed];
        } else {
            $pal = [$bg, $bg, $bg];
        }
        $stopAlpha = '';
        $circleOpacity = '.08';
        if ($bgOpacity !== null) {
            $a = max(0.0, min(1.0, (float)$bgOpacity));
            $stopAlpha = ' stop-opacity="' . $a . '"';
            $circleOpacity = (string)max(0.06, 0.18 * $a);
        }
        $svg = 'data:image/svg+xml;utf8,' . rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 '.$w.' '.$h.'">
              <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="'.$pal[0].'"'.$stopAlpha.'/>
                <stop offset="55%" stop-color="'.$pal[1].'"'.$stopAlpha.'/>
                <stop offset="100%" stop-color="'.$pal[2].'"'.$stopAlpha.'/>
              </linearGradient></defs>
              <rect width="100%" height="100%" fill="url(#g)"/>
              <g opacity="'.$circleOpacity.'" fill="#ffffff">
                <circle cx="'.($w*0.2).'" cy="'.($h*0.3).'" r="'.($h*0.35).'"/>
                <circle cx="'.($w*0.85).'" cy="'.($h*0.8).'" r="'.($h*0.3).'"/>
              </g>
              <text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="'.$fg.'" font-family="Arial, sans-serif" font-weight="700" font-size="'.(min($w,$h)/9).'" opacity="0.98">'.htmlspecialchars($text,ENT_XML1,'UTF-8').'</text>
            </svg>'
        );
        return $svg;
    }
}

if (!function_exists('avatar_svg')) {
    function avatar_svg(string $name, int $size = 120, ?string $bg = null): string
    {
        $initials = '';
        $parts = preg_split('/\s+/', trim($name));
        if (is_array($parts) && count($parts)) {
            foreach (array_slice($parts, 0, 2) as $p) {
                $initials .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
            }
        }
        if ($initials === '') {
            $initials = '??';
        }
        $colors = ['#1e3a8a','#7c3aed','#b91c1c','#047857','#b45309','#0369a1','#be185d','#4338ca'];
        $bg = $bg ?: $colors[abs(crc32($name)) % count($colors)];
        return 'data:image/svg+xml;utf8,' . rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 '.$size.' '.$size.'">
               <rect width="100%" height="100%" fill="'.$bg.'"/>
               <circle cx="'.($size/2).'" cy="'.($size*0.42).'" r="'.($size*0.18).'" fill="#ffffff" opacity="0.92"/>
               <path d="M'.($size*0.12).' '.$size.' C'.($size*0.15).' '.($size*0.68).' '.($size*0.85).' '.($size*0.68).' '.($size*0.88).' '.$size.' Z" fill="#ffffff" opacity="0.92"/>
               <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" fill="'.$bg.'" font-family="Arial, sans-serif" font-weight="800" font-size="'.($size*0.32).'" opacity="0">'.$initials.'</text>
             </svg>'
        );
    }
}

if (!function_exists('current_url_with_query')) {
    function current_url_with_query(array $merge = [], array $remove = ['page']): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $query = [];
        parse_str($_SERVER['QUERY_STRING'] ?? '', $query);
        foreach ($remove as $k) {
            unset($query[$k]);
        }
        $query = array_merge($query, $merge);
        if (count($query) === 0) {
            return $uri;
        }
        return $uri . '?' . http_build_query($query);
    }
}

if (!function_exists('pagination_links')) {
    function pagination_links(array $paginator, string $baseClass = 'pagination'): string
    {
        $totalPages = (int)($paginator['total_pages'] ?? 0);
        $page       = (int)($paginator['page'] ?? 1);
        if ($totalPages <= 1) {
            return '';
        }
        $range = 2;
        $start = max(1, $page - $range);
        $end   = min($totalPages, $page + $range);
        if ($start > 1) {
            $start = max(1, $end - ($range * 2));
        }
        if ($end < $totalPages) {
            $end = min($totalPages, $start + ($range * 2));
        }

        $html = '<nav class="' . $baseClass . '" aria-label="Paginación"><ul>';
        // Prev
        $disabled = $page <= 1 ? ' disabled' : '';
        $prevHref = $disabled ? '#' : e(current_url_with_query(['page' => $page - 1]));
        $html .= '<li class="page-item' . $disabled . '"><a class="page-link" href="' . $prevHref . '" aria-label="Anterior">« Anterior</a></li>';

        if ($start > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . e(current_url_with_query(['page' => 1])) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
        }
        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $page ? ' active' : '';
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . e(current_url_with_query(['page' => $i])) . '">' . $i . '</a></li>';
        }
        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . e(current_url_with_query(['page' => $totalPages])) . '">' . $totalPages . '</a></li>';
        }
        // Next
        $disabledN = $page >= $totalPages ? ' disabled' : '';
        $nextHref = $disabledN ? '#' : e(current_url_with_query(['page' => $page + 1]));
        $html .= '<li class="page-item' . $disabledN . '"><a class="page-link" href="' . $nextHref . '" aria-label="Siguiente">Siguiente »</a></li>';
        $html .= '</ul></nav>';
        return $html;
    }
}

if (!function_exists('file_is_image')) {
    function file_is_image(array $media): bool
    {
        if (!empty($media['mime_type']) && str_starts_with((string)$media['mime_type'], 'image/')) {
            return true;
        }
        $ext = strtolower(pathinfo((string)($media['stored_name'] ?? $media['file_ext'] ?? ''), PATHINFO_EXTENSION));
        return in_array($ext, ['jpg','jpeg','png','svg','webp','gif','bmp'], true);
    }
}

if (!function_exists('file_type_meta')) {
    function file_type_meta(string $storedName, string $mime = ''): array
    {
        $ext = strtolower(pathinfo($storedName, PATHINFO_EXTENSION));
        $map = [
            'pdf'  => ['icon' => '📕', 'label' => 'PDF',    'class' => 'badge badge-red'],
            'xlsx' => ['icon' => '📊', 'label' => 'Excel',  'class' => 'badge badge-green'],
            'xls'  => ['icon' => '📊', 'label' => 'Excel',  'class' => 'badge badge-green'],
            'sav'  => ['icon' => '🗄️', 'label' => 'SPSS',   'class' => 'badge badge-blue'],
            'doc'  => ['icon' => '📝', 'label' => 'Word',   'class' => 'badge badge-blue'],
            'docx' => ['icon' => '📝', 'label' => 'Word',   'class' => 'badge badge-blue'],
            'jpg'  => ['icon' => '🖼️', 'label' => 'Imagen', 'class' => 'badge badge-purple'],
            'jpeg' => ['icon' => '🖼️', 'label' => 'Imagen', 'class' => 'badge badge-purple'],
            'png'  => ['icon' => '🖼️', 'label' => 'Imagen', 'class' => 'badge badge-purple'],
            'svg'  => ['icon' => '🎨', 'label' => 'SVG',    'class' => 'badge badge-purple'],
            'webp' => ['icon' => '🖼️', 'label' => 'Imagen', 'class' => 'badge badge-purple'],
            'zip'  => ['icon' => '🗜️', 'label' => 'Zip',    'class' => 'badge badge-gray'],
            'rar'  => ['icon' => '🗜️', 'label' => 'RAR',    'class' => 'badge badge-gray'],
            'csv'  => ['icon' => '📋', 'label' => 'CSV',    'class' => 'badge badge-green'],
        ];
        $info = $map[$ext] ?? ['icon' => '📄', 'label' => strtoupper($ext ?: 'Archivo'), 'class' => 'badge badge-gray'];
        return $info;
    }
}

if (!function_exists('render_ads_position')) {
    function render_ads_position(string $position): void
    {
        static $loadedOnce = false;
        if (!$loadedOnce) {
            $loadedOnce = true;
            echo '<script data-ads-bootstrap="1">',
                 '(function(){try{var k="dismissed_ads",s=sessionStorage.getItem(k),c=document.cookie.match(/(?:^|; )dismissed_ads=([^;]+)/);var obj={};try{if(s)obj=Object.assign(obj,JSON.parse(s));}catch(e){}try{if(c)obj=Object.assign(obj,JSON.parse(decodeURIComponent(c[1])));}catch(e){}window.__adsDismissed=obj||{};}catch(e){window.__adsDismissed={};}})();',
                 "window.__adsDismiss = function(id,pos,ttl,ev){",
                 "  ttl=ttl||3600;",
                 "  try{if(ev){if(ev.preventDefault)ev.preventDefault();if(ev.stopPropagation)ev.stopPropagation();if(ev.returnValue!==undefined)ev.returnValue=false;}}catch(e){}",
                 "  try{",
                 "    var o=window.__adsDismissed||{};var now=Math.floor(Date.now()/1000);var exp=now+parseInt(ttl,10);if(id)o['id_'+parseInt(id,10)]=exp;if(pos)o['pos_'+pos]=exp;window.__adsDismissed=o;",
                 "    try{sessionStorage.setItem('dismissed_ads',JSON.stringify(o));}catch(_e){}",
                 "    try{",
                 "      var csrfEl=document.querySelector('meta[name=\"csrf-token\"]');var tok=csrfEl?csrfEl.content:'';",
                 "      var fd=new FormData();if(tok)fd.append('csrf_token',tok);",
                 "      var u='/ads/dismiss/'+parseInt(id,10)+'?ttl='+parseInt(ttl,10);if(pos)u+='&position='+encodeURIComponent(pos);",
                 "      fetch(u,{method:'POST',body:fd,credentials:'same-origin'}).catch(function(){});",
                 "    }catch(e){}",
                 "    var sel='[data-ad-id=\"'+parseInt(id,10)+'\"]';",
                 "    var els=document.querySelectorAll(sel);",
                 "    for(var i=0;i<els.length;i++){",
                 "      var el=els[i];",
                 "      el.style.setProperty('display','none','important');",
                 "      el.style.setProperty('height','0','important');",
                 "      el.style.setProperty('margin','0','important');",
                 "      el.style.setProperty('opacity','0','important');",
                 "      el.style.setProperty('pointer-events','none','important');",
                 "      el.classList.add('ad-dismissed-by-user');",
                 "    }",
                 "  }catch(_e){}",
                 "  return false;",
                 "};",
                 'window.__adsIsDismissed = function(id,pos){var o=window.__adsDismissed||{};var now=Math.floor(Date.now()/1000);var byId=id&&o["id_"+parseInt(id,10)]&&parseInt(o["id_"+parseInt(id,10)],10)>now;var byPos=pos&&o["pos_"+pos]&&parseInt(o["pos_"+pos],10)>now;return !!(byId||byPos);};',
                 '</script>';
        }
        try {
            $ads = \App\Models\Ad::getActiveByPosition($position);
        } catch (Throwable $e) {
            $ads = [];
        }
        if (empty($ads)) return;

        $now = time();
        $cookie = $_COOKIE['dismissed_ads'] ?? '';
        $cookArr = [];
        if ($cookie !== '') {
            $tmp = json_decode($cookie, true);
            if (is_array($tmp)) $cookArr = $tmp;
        }

        foreach ($ads as $ad) {
            $id = (int)$ad['id'];
            $byId  = !empty($cookArr['id_' . $id])  && (int)$cookArr['id_' . $id]  > $now;
            $byPos = !empty($cookArr['pos_' . $position]) && (int)$cookArr['pos_' . $position] > $now;
            if ($byId || $byPos) continue;
            \App\Models\Ad::incrementViews($id);

            $sp = $ad['image_path'] ?? '';
            $imgUrl = '';
            if ($sp !== '' && $ad['ad_kind'] !== 'html') {
                $imgUrl = (str_starts_with($sp, '/') || preg_match('#^https?://#i', $sp)) ? $sp : upload_url($sp);
            }
            $linkUrl = !empty($ad['link_url']) ? url('/ads/' . $id . '/click') : '';
            $linkAttr = $linkUrl !== '' ? ('href="' . e($linkUrl) . '" target="_blank" rel="noopener sponsored nofollow"') : '';
            $alt = !empty($ad['alt_text']) ? $ad['alt_text'] : ($ad['title'] ?? 'Publicidad');
            $posJsSafe = "'" . str_replace("'", "\\'", (string)$position) . "'";
            $dismissFn = "return window.__adsDismiss({$id}, {$posJsSafe}, 604800, event);";
            $dataAttrs = 'data-ad-id="' . $id . '" data-ad-position="' . e($position) . '"';
            $kind = $ad['ad_kind'] ?? 'image';
            $htmlCode = $ad['html_code'] ?? '';

            $isBottomSticky = ($position === 'bottom-sticky');
            $cssClass = match($position) {
                'top-banner'    => 'ad-pos ad-pos-top ad-pos-clean',
                'bottom-sticky' => 'ad-pos ad-pos-bottom ad-pos-clean ad-pos-bottom-inline',
                'sidebar-right' => 'ad-pos ad-pos-side ad-pos-clean',
                'interstitial'  => 'ad-pos ad-pos-inter ad-pos-clean',
                default         => 'ad-pos ad-pos-clean',
            };
            ?>
            <aside class="<?= $cssClass ?>" <?= $dataAttrs ?> role="complementary" aria-label="Publicidad">
              <!-- Botón cerrar flotante (ÚNICO visible sobre la imagen, SIN encabezado) -->
              <a href="#" class="ad-close ad-close-float" title="Cerrar publicidad" onclick="<?= $dismissFn ?>" aria-label="Cerrar publicidad" oncontextmenu="return false;">
                <span aria-hidden="true">×</span>
              </a>
              <?php if ($kind === 'html' && trim((string)$htmlCode) !== ''): ?>
                <div class="ad-body ad-body-nohead"><?= $htmlCode ?></div>
              <?php elseif ($imgUrl !== ''): ?>
                <div class="ad-body ad-body-nohead">
                  <?php if ($linkAttr !== ''): ?><a <?= $linkAttr ?> class="ad-link" aria-label="<?= e($alt) ?>"><?php endif; ?>
                    <img src="<?= e($imgUrl) ?>" alt="<?= e($alt) ?>" class="ad-img" loading="lazy" decoding="async">
                  <?php if ($linkAttr !== ''): ?></a><?php endif; ?>
                </div>
              <?php endif; ?>
              <script data-ad-hideinit>
                (function(){try{
                  var cur = document.currentScript;
                  if (cur && cur.parentNode && window.__adsIsDismissed && window.__adsIsDismissed(<?= $id ?>, <?= json_encode($position) ?>)) {
                    var nd = cur.parentNode;
                    nd.style.setProperty('display','none','important');
                    nd.classList.add('ad-dismissed-by-user');
                  }
                }catch(e){}})();
              </script>
            </aside>
            <?php
        }
    }
}

if (!function_exists('img_placeholder')) {
    /**
     * Fallback chain para imágenes: [upload real → picsum seed → placehold.co → SVG local]
     *
     * @param string $ratio Formato 'Ancho:Alto' ej: '16x9', '4x3', '21x9', '1x1', '3x4', '9x16'
     * @param string $text  Texto descriptivo (título de noticia, etc.) para placehold.co
     * @param string|null $seed  Seed determinista para picsum.photos (ej: 'noticia-142', 'rio-mar-portada')
     * @return string URL (https:// o data: o /assets/)
     */
    function img_placeholder(string $ratio = '16x9', string $text = '', ?string $seed = null): string
    {
        $allowed = ['16x9','4x3','21x9','1x1','3x4','9x16'];
        if (!in_array($ratio, $allowed, true)) {
            $ratio = '16x9';
        }
        [$rw, $rh] = array_map('intval', explode('x', $ratio));
        $baseW = 1200;
        $w = $baseW;
        $h = (int)round($baseW * $rh / $rw);
        if ($seed !== null && $seed !== '') {
            $safeSeed = is_numeric($seed) ? 'seed-' . $seed : str_slug($seed);
            return 'https://picsum.photos/seed/' . rawurlencode($safeSeed) . '/' . $w . '/' . $h;
        }
        if ($text !== '') {
            $short = mb_substr(trim($text), 0, 45, 'UTF-8');
            if ($short !== '') {
                return 'https://placehold.co/' . $w . 'x' . $h . '/0052A5/FFFFFF/png?text=' . rawurlencode($short);
            }
        }
        return asset('img/placeholder/' . $ratio . '.svg');
    }
}

if (!function_exists('img_fallback')) {
    /**
     * Dada una URL de upload real, si existe físicamente la devuelve;
     * si no, aplica el fallback chain img_placeholder(). Útil para reemplazar onerror inline.
     */
    function img_fallback(?string $uploadPath, string $ratio = '16x9', string $text = '', ?string $seed = null): string
    {
        if ($uploadPath !== null && $uploadPath !== '') {
            $pub = upload_url($uploadPath);
            if ($pub !== '') {
                $abs = resolve_upload_abs_path($uploadPath);
                if (is_file($abs)) {
                    return $pub;
                }
                if (str_starts_with($pub, 'http://') || str_starts_with($pub, 'https://')) {
                    return $pub;
                }
            }
        }
        return img_placeholder($ratio, $text, $seed);
    }
}

if (!function_exists('video_extract_youtube_id')) {
    /**
     * Extrae el ID de un video de YouTube desde cualquier formato:
     *   - https://www.youtube.com/watch?v=ID&list=…
     *   - https://youtu.be/ID?list=…
     *   - https://www.youtube.com/embed/ID
     *   - https://youtube.com/shorts/ID
     *   - https://www.youtube.com/live/ID
     * Devuelve string vacío si no es YouTube.
     */
    function video_extract_youtube_id(string $urlOrHtml): string
    {
        $src = trim($urlOrHtml);
        if ($src === '') {
            return '';
        }
        if (stripos($src, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/i', $src, $m)) {
            $src = trim($m[1]);
        }
        $patterns = [
            '#(?:youtube\.com/(?:watch\?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{6,})#i',
        ];
        foreach ($patterns as $re) {
            if (preg_match($re, $src, $mm)) {
                return (string)$mm[1];
            }
        }
        return '';
    }
}

if (!function_exists('video_extract_vimeo_id')) {
    function video_extract_vimeo_id(string $urlOrHtml): string
    {
        $src = trim($urlOrHtml);
        if ($src === '') {
            return '';
        }
        if (stripos($src, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/i', $src, $m)) {
            $src = trim($m[1]);
        }
        if (preg_match('#(?:player\.)?vimeo\.com/(?:video/)?(\d{6,})#i', $src, $mm)) {
            return (string)$mm[1];
        }
        return '';
    }
}

if (!function_exists('video_extract_iframe_src')) {
    function video_extract_iframe_src(string $embedOrUrl): string
    {
        $s = trim($embedOrUrl);
        if ($s === '') {
            return '';
        }
        if (stripos($s, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/i', $s, $m)) {
            return trim($m[1]);
        }
        if (preg_match('#^https?://#i', $s)) {
            return $s;
        }
        return '';
    }
}

if (!function_exists('video_render_embed_html')) {
    /**
     * Devuelve HTML <iframe> (o <video>) normalizado para reproducir en /video/{slug}.
     * Detecta YouTube ID, Vimeo ID, iframe HTML crudo o URL raw.
     */
    function video_render_embed_html(array $video, ?string $posterUrl = null): string
    {
        $vt = (string)($video['video_type'] ?? 'embed');
        $embed = (string)($video['embed_url'] ?? '');
        $title = trim((string)($video['title'] ?? 'Video'));
        $titleEsc = e($title !== '' ? $title : 'Video');

        $ytId = video_extract_youtube_id($embed);
        if ($ytId !== '') {
            $src = 'https://www.youtube.com/embed/' . rawurlencode($ytId) . '?rel=0&modestbranding=1&playsinline=1';
            return '<iframe loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen="true" '
                . 'src="' . e($src) . '" '
                . 'title="' . $titleEsc . '" '
                . 'frameborder="0" '
                . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>';
        }

        $vimId = video_extract_vimeo_id($embed);
        if ($vimId !== '') {
            $src = 'https://player.vimeo.com/video/' . rawurlencode($vimId) . '?title=0&byline=0&portrait=0';
            return '<iframe loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen="true" '
                . 'src="' . e($src) . '" '
                . 'title="' . $titleEsc . '" '
                . 'frameborder="0" '
                . 'allow="autoplay; fullscreen; picture-in-picture"></iframe>';
        }

        $srcRaw = video_extract_iframe_src($embed);
        if ($srcRaw !== '') {
            return '<iframe loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen="true" '
                . 'src="' . e($srcRaw) . '" '
                . 'title="' . $titleEsc . '" '
                . 'frameborder="0" '
                . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>';
        }

        $videoPath = (string)($video['video_path'] ?? '');
        if ($videoPath !== '') {
            $src = upload_url($videoPath);
            if ($src !== '') {
                $posterAttr = $posterUrl !== null && $posterUrl !== ''
                    ? ' poster="' . e($posterUrl) . '"'
                    : '';
                return '<video controls playsinline preload="metadata" class="rm-video-tag rm-video-fill"' . $posterAttr . '>'
                    . '<source src="' . e($src) . '" type="video/mp4">'
                    . 'Tu navegador no soporta el elemento video.'
                    . '</video>';
            }
        }

        return '';
    }
}

