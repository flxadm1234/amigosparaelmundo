<?php
$siteName = app_setting('site_name', 'ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO');
$siteSlogan = app_setting('site_slogan', 'Juntos podemos transformar vidas y proteger nuestro planeta');
$siteKicker = app_setting('site_brand_kicker', 'ASOCIACIÓN CIVIL');
$siteLogo = app_setting('site_logo', '');
$siteLogoUrl = (!empty($siteLogo) && (str_starts_with($siteLogo, '/') || preg_match('#^https?://#i', $siteLogo)))
    ? $siteLogo
    : (!empty($siteLogo) ? upload_url($siteLogo) : '');

$radioStreamUrl = app_setting('radio_stream_url', '');

$socialFacebook = app_setting('social_facebook', '');
$socialInstagram = app_setting('social_instagram', '');
$socialTwitter = app_setting('social_twitter', '');
$socialYoutube = app_setting('social_youtube', '');
$socialTiktok = app_setting('social_tiktok', '');
$socialWhatsapp = app_setting('social_whatsapp', '');

$seccionesMenu = method_exists(\App\Models\Section::class, 'allFeatured')
    ? \App\Models\Section::allFeatured()
    : \App\Models\Section::all();

$seccionesMenu = array_values(array_filter((array)$seccionesMenu, function ($s) {
    return ((int)($s['is_active'] ?? 0)) === 1;
}));

$sectionsBySlug = [];
foreach ($seccionesMenu as $s) {
    $slug = (string)($s['slug'] ?? '');
    if ($slug !== '') { $sectionsBySlug[$slug] = $s; }
}

$topbarBgColor = trim((string)app_setting('header_topbar_bg', '#FFFFFF'));
$bannerLiveBgColor  = trim((string)app_setting('banner_live_bg_color', '#0F172A'));

$breakingEnabled = (string)app_setting('breaking_enabled', '1') === '1';
$breakingCustomRaw = trim((string)app_setting('breaking_custom_items', ''));
$breakingCustomItems = [];
if ($breakingCustomRaw !== '') {
    foreach (explode("\n", str_replace(["\r\n", "\r"], "\n", $breakingCustomRaw)) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $parts = explode('|', $line, 2);
        $title = trim($parts[0] ?? '');
        $url   = trim($parts[1] ?? '#');
        if ($title !== '') {
            $breakingCustomItems[] = ['title' => $title, 'slug' => '', 'url' => $url];
        }
    }
}

$breakingStubFallback = [
    ['title' => '⚡ ÚLTIMO MINUTO · Amigos por el Mundo presenta su portal de noticias con cobertura nacional', 'slug' => 'ultimo-minuto-amigos-por-el-mundo', 'url' => '/'],
    ['title' => '🔴 EN DIRECTO · Congreso debate reforma educativa — cobertura de AMIGOS POR EL MUNDO', 'slug' => 'reforma-educativa-congreso', 'url' => '/noticias'],
];

$breakingNews = method_exists(\App\Models\News::class, 'breakingNews')
    ? (array)\App\Models\News::breakingNews(5)
    : [];

if (!empty($breakingCustomItems)) {
    $breakingNews = $breakingCustomItems;
} else {
    if (count($breakingNews) < 3) {
        $fallbackNews = [];
        if (method_exists(\App\Models\News::class, 'latestPublished')) {
            $fallbackNews = (array)\App\Models\News::latestPublished(3);
        } elseif (class_exists(\App\Models\News::class)) {
            $fallbackNews = (array)\App\Models\News::where([['is_published','=',1]], 'publish_date', 'DESC', 3);
        }
        $fallbackNews = array_values(array_filter((array)$fallbackNews, function($n) use ($breakingNews){
            if (empty($n['slug'])) return false;
            foreach ($breakingNews as $b) { if (($b['slug'] ?? null) === $n['slug']) return false; }
            return true;
        }));
        while (count($breakingNews) < 3 && !empty($fallbackNews)) {
            $breakingNews[] = array_shift($fallbackNews);
        }
    }
    if (count($breakingNews) === 0) {
        $breakingNews = $breakingStubFallback;
    }
}
$breakingNews = array_slice(array_values((array)$breakingNews), 0, 3);

$weekdays = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$months = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$today = getdate();
$fechaHoy = $weekdays[$today['wday']] . ', ' . $today['mday'] . ' de ' . $months[$today['mon']] . ' de ' . $today['year'];

/**
 * Helper: retorna la sección del menú por slug, O ARRAY VACÍO si no está en BD / no activa.
 * Ya NO genera stubs fantasma para secciones eliminadas o desactivadas.
 */
function rm_resolve_section(array $bySlug, string $slug, string $fallbackName): array {
    if (isset($bySlug[$slug]) && ((int)($bySlug[$slug]['is_active'] ?? 0)) === 1) {
        return $bySlug[$slug];
    }
    return [];
}

$navGroups = [
    [
        'key'  => 'nacional',
        'name' => 'Nacional',
        'icon' => 'building',
        'items' => [
            ['slug' => 'lima',              'name' => 'Lima',             'icon' => 'lima'],
            ['slug' => 'peru',              'name' => 'Perú',             'icon' => 'pais'],
            ['slug' => 'politica',          'name' => 'Política',         'icon' => 'building'],
            ['slug' => 'mundo',             'name' => 'Mundo',            'icon' => 'globe'],
        ],
    ],
    [
        'key'  => 'deportes',
        'name' => 'Deportes',
        'icon' => 'trophy',
        'items' => [
            ['slug' => 'futbol',            'name' => 'Fútbol',           'icon' => 'futbol'],
            ['slug' => 'deportes',          'name' => 'Polideportivo',    'icon' => 'trophy'],
        ],
    ],
    [
        'key'  => 'secciones',
        'name' => 'Secciones',
        'icon' => 'theater',
        'items' => [
            ['slug' => 'economia',          'name' => 'Economía',         'icon' => 'money'],
            ['slug' => 'tecnologia',        'name' => 'Tecnología',       'icon' => 'laptop'],
            ['slug' => 'cultura',           'name' => 'Cultura',          'icon' => 'theater'],
            ['slug' => 'entretenimiento',   'name' => 'Entretenimiento',  'icon' => 'clapper'],
        ],
    ],
    [
        'key'  => 'medios',
        'name' => 'En Vivo / Medios',
        'icon' => 'radio',
        'items' => [
            ['slug' => 'radio',             'name' => 'Radio',            'icon' => 'radio'],
            ['slug' => 'videos',            'name' => 'Televisión',       'icon' => 'tv'],
            ['slug' => 'podcast',           'name' => 'Podcast',          'icon' => 'mic'],
        ],
    ],
];

$_activeNavGroups = [];
foreach ($navGroups as $_g) {
    $_gItems = array_values(array_filter($_g['items'], function ($_gi) use ($sectionsBySlug) {
        $_slug = (string)($_gi['slug'] ?? '');
        if ($_slug === '') return false;
        if (in_array($_slug, ['radio', 'videos', 'podcast'], true)) return true;
        return isset($sectionsBySlug[$_slug]) && ((int)($sectionsBySlug[$_slug]['is_active'] ?? 0)) === 1;
    }));
    if (empty($_gItems)) continue;
    $_g['items'] = $_gItems;
    $_activeNavGroups[] = $_g;
}
$navGroups = $_activeNavGroups;
unset($_g, $_gItems, $_activeNavGroups);

/**
 * Helper inline de íconos SVG monocromáticos (stroke).
 * NO renderiza emojis. Todos los tamaños, stroke-width, stroke-color configurables.
 */
function nav_icon(string $key, int $size = 18, float $sw = 1.8, string $fill_mode = 'none'): string
{
    $s = $size;
    $stroke = 'currentColor';
    $common = sprintf(
        'width="%d" height="%d" viewBox="0 0 24 24" fill="%s" stroke="%s" stroke-width="%s" stroke-linecap="round" stroke-linejoin="round"',
        $s, $s, $fill_mode, $stroke, $sw
    );
    $paths = [
        'home'         => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'flash'        => 'M13 10V3L4 14h7v7l9-11h-7',
        'lima'         => 'M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6',
        'pais'         => 'M12 22s8-4.5 8-11V5l-8-3-8 3v6c0 6.5 8 11 8 11zM9 9h6M9 13h6',
        'building'     => 'M4 21V8a2 2 0 012-2h12a2 2 0 012 2v13M4 21h16M8 11h1M8 15h1M8 19h1M15 11h1M15 15h1M15 19h1M12 21v-7a2 2 0 012-2h2a2 2 0 012 2v7',
        'globe'        => 'M12 2a10 10 0 100 20 10 10 0 000-20zm0 0v20M2 12h20M12 2c3.7 2.6 6 6.2 6 10s-2.3 7.4-6 10M12 2C8.3 4.6 6 8.2 6 12s2.3 7.4 6 10',
        'futbol'       => 'M12 2a10 10 0 100 20 10 10 0 000-20zM5 12l3 2 2 4 4-1 3 4 2-5 3-3-3-4-4-2-4 2z',
        'trophy'       => 'M8 21h8M12 17v4M7 4h10v6a5 5 0 01-10 0V4zM5 7H2v1a3 3 0 003 3M19 7h3v1a3 3 0 01-3 3M5 7v3M19 7v3',
        'money'        => 'M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6',
        'laptop'       => 'M4 5a2 2 0 012-2h12a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zM2 19h20M9 20h6',
        'theater'      => 'M2 18h20v2H2zM4 18V6l8-4 8 4v12M4 18l8-4 8 4M8 10h2m4 0h2m-8 4h2m4 0h2',
        'clapper'      => 'M2 8l20-3v13a2 2 0 01-2 2H4a2 2 0 01-2-2V8zM2 8l5-3M15 2l5 3M6 3l12 3M7 8l5 2 5-2',
        'radio'        => 'M4 11a9 9 0 019 9v1M4 14a6 6 0 016 6v1M4 17a3 3 0 013 3v1M3 20h18a2 2 0 002-2v-6a2 2 0 00-2-2H3a2 2 0 00-2 2v6a2 2 0 002 2zM7 15v2M11 15v2M15 15v2M7 19h.01M13 19h.01',
        'tv'           => 'M3 7a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7zM8 21h8M12 17v4',
        'mic'          => 'M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3zM19 10v1a7 7 0 01-14 0v-1M12 18v4M8 22h8',
        'search'       => 'M21 21l-4.35-4.35M10 18a8 8 0 110-16 8 8 0 010 16z',
        'menu'         => 'M3 6h18M3 12h18M3 18h18',
        'calendar'     => 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z',
        'lock'         => 'M7 10V7a5 5 0 0110 0v3M5 10h14v11H5V10zM12 14v3',
        'close'        => 'M18 6L6 18M6 6l12 12',
        'play-circle'  => 'M12 22a10 10 0 100-20 10 10 0 000 20zM10 8l8 4-8 4V8z',
        'pause-circle' => 'M12 22a10 10 0 100-20 10 10 0 000 20zM10 9v6M14 9v6',
        'live-dot'     => 'M12 9a3 3 0 100 6 3 3 0 000-6z',
        'caret-down'   => 'M6 9l6 6 6-6',
        'facebook'     => 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3V2z',
        'instagram'    => 'M7 3h10a4 4 0 014 4v10a4 4 0 01-4 4H7a4 4 0 01-4-4V7a4 4 0 014-4zM12 17a5 5 0 110-10 5 5 0 010 10zM17.5 6.5h.01',
        'twitter'      => 'M18 5L6 19M6 5l12 14',
        'youtube'      => 'M2.5 8.5A2.5 2.5 0 015 6h14a2.5 2.5 0 012.5 2.5v7A2.5 2.5 0 0119 18H5a2.5 2.5 0 01-2.5-2.5v-7zM10 9l6 3-6 3V9z',
        'tiktok'       => 'M15 3h3v3a6 6 0 01-6 6v-3a3 3 0 003-3zM9 12a3 3 0 100 6 3 3 0 000-6z',
        'whatsapp'     => 'M12 2a10 10 0 1010 10M3 21l1.5-4A10 10 0 0112 2zM8 11c.5-1 2-1.5 3-1M11 13c.5 1 1.5 1.5 2.5 2M15 16c1 .5 2 1.5 2 3',
        'alert'        => 'M12 3L1 21h22L12 3zM12 10v5M12 19h.01',
    ];
    $p = $paths[$key] ?? $paths['menu'];
    return sprintf('<svg %s aria-hidden="true"><path d="%s" /></svg>', $common, $p);
}
?>
<a class="skip-link rm-skip-link visually-hidden" href="#rm-main">Saltar al contenido</a>

<div id="rm-fixed-topbar-spacer" aria-hidden="true"></div>

<header class="rm-header-wrap rm-header-rpp" role="banner">
  <!-- ROW 1: Topbar grid 3 columnas RPP (Izq Menú / Centro Logo / Der Búsqueda+Señal) -->
  <div class="rm-topbar-new rm-grid-3" data-rpp-topbar style="background: <?php echo e($topbarBgColor !== '' ? $topbarBgColor : '#FFFFFF'); ?>;">
    <div class="rm-tb-col rm-tb-col-left">
      <button
        type="button"
        class="rm-btn-menu"
        id="rmDrawerOpen"
        aria-controls="rmDrawer"
        aria-expanded="false"
        aria-haspopup="dialog"
        aria-label="Abrir / cerrar menú de secciones">
        <?php echo nav_icon('menu', 20, 2.2, 'none') ?>
        <span class="rm-btn-menu-label">Menú</span>
      </button>
    </div>

    <div class="rm-tb-col rm-tb-col-center">
      <a href="<?php echo url('/') ?>" class="rm-brand-center" aria-label="<?php echo e($siteName) ?> · Inicio"
         style="--amw-kicker-size:<?php echo e(trim((string)app_setting('header_logo_kicker_size', '12')) ?: '12') ?>px;--amw-name-size:<?php echo e(trim((string)app_setting('header_logo_name_size', '24')) ?: '24') ?>px;--amw-slogan-size:<?php echo e(trim((string)app_setting('header_logo_slogan_size', '14')) ?: '14') ?>px;">
        <?php if ($siteLogoUrl !== ''): ?>
          <img src="<?php echo e($siteLogoUrl) ?>" alt="Logotipo de <?php echo e($siteName) ?>" class="rm-logo" loading="eager" fetchpriority="high">
        <?php endif; ?>
        <span class="rm-brand-text" style="display:grid;max-width:310px;padding:5px 8px;color:#14532d;font-size:calc(var(--amw-name-size,20px) * .72);font-weight:800;line-height:1.12;text-align:left;">
          <span style="font-size:calc(var(--amw-kicker-size,12px) * .78);letter-spacing:.12em;color:#087B43;"><?php echo e($siteKicker) ?></span>
          <span><?php echo e($siteName) ?></span>
          <span style="margin-top:2px;color:#435047;font-family:Georgia,serif;font-size:calc(var(--amw-slogan-size,14px) * .78);font-style:italic;font-weight:400;"><?php echo e($siteSlogan) ?></span>
        </span>
      </a>
    </div>

    <div class="rm-tb-col rm-tb-col-right">
      <!-- Búsqueda (sheet existente #rmSearchSheet -->
      <div class="rm-header-search-wrap" id="rmHeaderSearchWrap">
        <button
          type="button"
          class="rm-search-toggle"
          id="rmSearchToggle"
          aria-expanded="false"
          aria-controls="rmSearchSheet"
          aria-label="Abrir buscador">
          <?php echo nav_icon('search', 18, 2, 'none') ?>
          <span class="rm-search-toggle-label">Buscar</span>
        </button>
        <div
          class="rm-search-sheet"
          id="rmSearchSheet"
          role="dialog"
          aria-modal="false"
          aria-label="Buscador de noticias"
          aria-hidden="true"
          hidden>
          <div class="rm-search-sheet-head">
            <span class="rm-search-sheet-title">Buscar</span>
            <button
              type="button"
              class="rm-search-sheet-close"
              data-rm-search-close
              aria-label="Cerrar buscador">
              <?php echo nav_icon('close', 16, 2, 'none') ?>
            </button>
          </div>
          <form role="search" action="<?php echo url('/noticias') ?>" method="get" id="rmSearchForm">
            <label for="rmSearchInput" class="visually-hidden">Buscar noticias, secciones o autores</label>
            <input
              id="rmSearchInput"
              name="q"
              type="search"
              autocomplete="off"
              placeholder="Buscar noticias, secciones, autores…"
              value="<?php echo e($_GET['q'] ?? '') ?>">
            <button type="submit" class="rm-search-sheet-submit" aria-label="Ejecutar búsqueda">
              <?php echo nav_icon('search', 18, 2.2, 'none') ?>
            </button>
          </form>
        </div>
      </div>

      <!-- Señal en vivo: AMIGOS TV | RADIO + Play circular -->
      <div class="rm-signal" aria-label="Señales en vivo">
        <span class="rm-live-dot" aria-hidden="true"></span>
        <span class="rm-signal-label">Señal en vivo:</span>
        <a class="rm-signal-link rm-signal-tv" href="/en-vivo" aria-label="Abrir AMIGOS TV en vivo">AMIGOS TV</a>
        <span class="rm-signal-sep" aria-hidden="true">|</span>
        <a class="rm-signal-link rm-signal-radio" href="/radio" aria-label="Abrir Radio en vivo">RADIO</a>
        <button
          type="button"
          class="rm-play-circle"
          id="rmMiniRadioBtn"
          aria-label="Reproducir radio en vivo"
          aria-pressed="false"
          title="Escuchar radio en vivo"
          data-src="<?php echo e($radioStreamUrl) ?>">
          <span class="rm-play-circle-icon" aria-hidden="true">▶</span>
        </button>
        <?php if (!empty($radioStreamUrl)): ?>
          <audio id="rmMiniAudio" preload="none" crossorigin="anonymous">
            <source src="<?php echo e($radioStreamUrl) ?>" type="audio/mpeg">
          </audio>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<?php
  $bannerLiveEnabled    = (string)app_setting('banner_live_enabled', '1') === '1';
  $bannerLiveUrl       = trim((string)app_setting('banner_live_url', '/en-vivo'));
  $bannerLiveText      = trim((string)app_setting('banner_live_text', 'Mira aquí la transmisión en vivo de AMIGOS TV'));
?>
<?php if ($bannerLiveEnabled && $bannerLiveText !== ''): ?>
  <a class="rm-live-banner rm-live-banner--black rm-live-banner--tight"
     style="background: <?php echo e($bannerLiveBgColor !== '' ? $bannerLiveBgColor : '#0F172A'); ?>;"
     href="<?php echo e($bannerLiveUrl !== '' ? $bannerLiveUrl : '/en-vivo'); ?>"
     <?php if (str_starts_with($bannerLiveUrl, 'http')): ?>target="_blank" rel="noopener"<?php endif; ?>
     aria-label="<?php echo e($bannerLiveText) ?>">
    <span class="rm-live-banner-yticon" aria-hidden="true">
      <svg width="26" height="18" viewBox="0 0 90 60" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="90" height="60" rx="10" fill="#FF0000"/><path d="M35 20L60 30L35 40V20Z" fill="white"/></svg>
    </span>
    <span class="rm-live-banner-prefix">EN VIVO</span>
    <span class="rm-live-banner-sep" aria-hidden="true">|</span>
    <span class="rm-live-banner-text"><?php echo e($bannerLiveText) ?></span>
  </a>
<?php endif; ?>

<?php if ($breakingEnabled && !empty($breakingNews)): ?>
  <div class="rm-breaking rm-breaking--tight" role="region" aria-label="Noticias de última hora">
    <div class="rm-breaking-inner">
      <span class="rm-breaking-label">ÚLTIMA HORA</span>
      <div class="rm-breaking-track-wrap" aria-live="polite">
        <div class="rm-breaking-track">
          <?php foreach ($breakingNews as $bn):
            $bnHref = !empty($bn['url']) ? $bn['url'] : news_url($bn['slug'] ?? '');
            $isExt = !empty($bn['url']) && preg_match('#^https?://#i', $bn['url']);
          ?>
            <a href="<?php echo e($bnHref) ?>" class="rm-breaking-item" title="<?php echo e($bn['title'] ?? '') ?>" <?php if ($isExt): ?>target="_blank" rel="noopener"<?php endif; ?>>
              <span class="rm-breaking-caret">▸</span>
              <span><?php echo e($bn['title'] ?? '') ?></span>
            </a>
          <?php endforeach; ?>
          <?php foreach ($breakingNews as $bn):
            $bnHref2 = !empty($bn['url']) ? $bn['url'] : news_url($bn['slug'] ?? '');
          ?>
            <a href="<?php echo e($bnHref2) ?>" class="rm-breaking-item" aria-hidden="true" tabindex="-1">
              <span class="rm-breaking-caret">▸</span>
              <span><?php echo e($bn['title'] ?? '') ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<nav class="rm-nav" aria-label="Navegación principal">
    <div class="container">
      <ul class="rm-nav-list" role="menubar" aria-label="Menú principal">

        <li class="rm-nav-item" role="none">
          <a
            href="<?php echo url('/') ?>"
            role="menuitem"
            class="rm-nav-link <?php echo is_active_route('/') ? 'active' : '' ?>"
            <?php if (is_active_route('/')): ?>aria-current="page"<?php endif; ?>>
            <?php echo nav_icon('home', 18, 2, 'none') ?>
            <span>Inicio</span>
          </a>
        </li>

        <li class="rm-nav-item" role="none">
          <?php
            $ultSec = rm_resolve_section($sectionsBySlug, 'ultimas-noticias', 'Últimas noticias');
            $ultActive = is_active_route('/seccion/ultimas-noticias');
            $ultHref = !empty($ultSec) ? section_url($ultSec) : url('/noticias');
          ?>
          <a
            href="<?php echo e($ultHref) ?>"
            role="menuitem"
            class="rm-nav-link <?php echo $ultActive ? 'active' : '' ?>"
            <?php if ($ultActive): ?>aria-current="page"<?php endif; ?>>
            <?php echo nav_icon('flash', 18, 2, 'none') ?>
            <span>Últimas noticias</span>
          </a>
        </li>

        <?php foreach ($navGroups as $g): ?>
          <li class="rm-nav-item rm-has-submenu" role="none">
            <?php
              $firstValid = null;
              foreach ($g['items'] as $_xgi) {
                  $_xs = (string)($_xgi['slug'] ?? '');
                  if (in_array($_xs, ['radio', 'videos', 'podcast'], true)) { $firstValid = $_xgi; break; }
                  $_xr = rm_resolve_section($sectionsBySlug, $_xs, $_xgi['name'] ?? '');
                  if (!empty($_xr)) { $firstValid = $_xgi; break; }
              }
              $firstSlug = (string)($firstValid['slug'] ?? ($g['items'][0]['slug'] ?? ''));
              $gActive = false;
              foreach ($g['items'] as $gi) { $_slug = (string)($gi['slug'] ?? ''); if ($_slug !== '' && is_active_route('/seccion/' . $_slug)) { $gActive = true; break; } }
              if (in_array($g['key'], ['medios'], true)) {
                  if (is_active_route('/en-vivo') || is_active_route('/tv') || is_active_route('/radio') || is_active_route('/podcast')) $gActive = true;
              }
              $gSec = ($firstSlug !== '' && !in_array($firstSlug, ['radio', 'videos', 'podcast'], true))
                  ? rm_resolve_section($sectionsBySlug, $firstSlug, $g['name'])
                  : [];
              if ($g['key'] === 'medios') {
                  $gHref = url('/en-vivo');
              } elseif (!empty($gSec)) {
                  $gHref = section_url($gSec);
              } else {
                  $gHref = !empty($firstValid)
                      ? (in_array($firstSlug, ['radio','videos','podcast'], true)
                          ? url('/' . ($firstSlug === 'videos' ? 'en-vivo' : $firstSlug))
                          : '#')
                      : '#';
              }
            ?>
            <a
              href="<?php echo e($gHref) ?>"
              role="menuitem"
              tabindex="0"
              aria-haspopup="true"
              aria-expanded="false"
              class="rm-nav-link rm-nav-link--group <?php echo $gActive ? 'active' : '' ?>"
              <?php if ($gActive): ?>aria-current="page"<?php endif; ?>>
              <?php echo nav_icon($g['icon'], 18, 2, 'none') ?>
              <span><?php echo e($g['name']) ?></span>
              <span class="rm-nav-link__caret" aria-hidden="true"><?php echo nav_icon('caret-down', 12, 2.4, 'none') ?></span>
            </a>
            <ul class="rm-nav-submenu" role="menu" aria-label="<?php echo e($g['name']) ?>">
              <?php foreach ($g['items'] as $gi):
                $slug = (string)($gi['slug'] ?? '');
                if ($slug === '') continue;
                $isMedio = in_array($slug, ['radio', 'videos', 'podcast'], true);
                if (!$isMedio) {
                    $sec = rm_resolve_section($sectionsBySlug, $slug, $gi['name']);
                    if (empty($sec)) continue;
                } else {
                    $sec = [];
                }
                $iActive = $slug !== '' ? is_active_route('/seccion/' . $slug) : false;
                $isLiveTelevision = $slug === 'videos';
                if ($isLiveTelevision) {
                  $gi_href = url('/en-vivo');
                  $iActive = $iActive || is_active_route('/en-vivo') || is_active_route('/tv');
                  $gi_extraCss = ' rm-nav-sublink--live';
                } elseif ($slug === 'radio') {
                  $gi_href = url('/radio');
                  $iActive = $iActive || is_active_route('/radio');
                  $gi_extraCss = '';
                } elseif ($slug === 'podcast') {
                  $gi_href = url('/podcast');
                  $iActive = $iActive || is_active_route('/podcast');
                  $gi_extraCss = '';
                } else {
                  $gi_href = section_url($sec);
                  $gi_extraCss = '';
                }
              ?>
                <li role="none">
                  <a
                    href="<?php echo e($gi_href) ?>"
                    role="menuitem"
                    class="rm-nav-sublink<?php echo $gi_extraCss ?><?php echo $iActive ? ' active' : '' ?>"
                    <?php if ($iActive): ?>aria-current="page"<?php endif; ?>>
                    <?php echo nav_icon($gi['icon'], 16, 1.8, 'none') ?>
                    <span><?php echo e($gi['name']) ?></span>
                    <?php if ($isLiveTelevision): ?>
                      <span style="margin-left:auto;display:inline-flex;align-items:center;gap:.35rem;padding:.12rem .5rem;border-radius:9999px;background:linear-gradient(90deg,#BE123C,#E11D48);color:#fff;font-size:.72rem;font-weight:900;letter-spacing:.12em;line-height:1;">
                        <span style="width:6px;height:6px;border-radius:9999px;background:#fff;box-shadow:0 0 0 2px rgba(255,139,139,.38);"></span>
                        LIVE
                      </span>
                    <?php endif; ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </li>
        <?php endforeach; ?>

      </ul>
    </div>
  </nav>

<div class="rm-search-backdrop" id="rmSearchBackdrop" data-rm-search-close aria-hidden="true" hidden></div>
<div class="rm-nav-backdrop" id="rmNavBackdrop" data-rm-nav-close aria-hidden="true"></div>

<aside
  class="rm-mobile-nav"
  id="rmMobileNav"
  role="dialog"
  aria-modal="true"
  aria-label="Menú de navegación móvil"
  aria-hidden="true">
  <div class="rm-mobile-nav-head">
    <strong class="rm-mobile-nav-title"><?php echo nav_icon('menu', 16, 2, 'none') ?><span>Menú Amigos por el Mundo</span></strong>
    <button
      type="button"
      class="rm-mobile-nav-close"
      data-rm-nav-close
      aria-label="Cerrar menú móvil">
      <?php echo nav_icon('close', 18, 2.2, 'none') ?>
    </button>
  </div>

  <div class="rm-mobile-nav-search-wrap">
    <form role="search" action="<?php echo url('/noticias') ?>" method="get" class="rm-mobile-nav-search">
      <label for="rmNavSearchMobile" class="visually-hidden">Buscar noticias</label>
      <div class="rm-mobile-search-row">
        <span class="rm-mobile-search-icon" aria-hidden="true"><?php echo nav_icon('search', 16, 2, 'none') ?></span>
        <input
          id="rmNavSearchMobile"
          name="q"
          type="search"
          placeholder="Buscar noticias, secciones, autores…"
          value="<?php echo e($_GET['q'] ?? '') ?>">
        <button type="submit" class="rm-mobile-search-submit" aria-label="Buscar">
          <?php echo nav_icon('search', 16, 2, 'none') ?>
        </button>
      </div>
    </form>
  </div>

  <ul class="rm-mobile-nav-list" role="list">

    <li>
      <a
        href="<?php echo url('/') ?>"
        class="rm-mobile-nav-link <?php echo is_active_route('/') ? 'active' : '' ?>"
        <?php if (is_active_route('/')): ?>aria-current="page"<?php endif; ?>>
        <?php echo nav_icon('home', 18, 2, 'none') ?><span>Inicio</span>
      </a>
    </li>
    <li>
      <?php
        $uSec = rm_resolve_section($sectionsBySlug, 'ultimas-noticias', 'Últimas noticias');
        $uAct = is_active_route('/seccion/ultimas-noticias');
        $uHref = !empty($uSec) ? section_url($uSec) : url('/noticias');
      ?>
      <a
        href="<?php echo e($uHref) ?>"
        class="rm-mobile-nav-link <?php echo $uAct ? 'active' : '' ?>"
        <?php if ($uAct): ?>aria-current="page"<?php endif; ?>>
        <?php echo nav_icon('flash', 18, 2, 'none') ?><span>Últimas noticias</span>
      </a>
    </li>

    <?php foreach ($navGroups as $g):
      $gAct = false;
      foreach ($g['items'] as $gi) {
          $_mslug = (string)($gi['slug'] ?? '');
          if ($_mslug === '') continue;
          if (in_array($_mslug, ['radio','videos','podcast'], true)) {
              if (($_mslug === 'videos' && (is_active_route('/en-vivo') || is_active_route('/tv')))
                  || ($_mslug === 'radio' && is_active_route('/radio'))
                  || ($_mslug === 'podcast' && is_active_route('/podcast'))) {
                  $gAct = true; break;
              }
          } elseif (is_active_route('/seccion/' . $_mslug)) { $gAct = true; break; }
      }
    ?>
      <li class="rm-mobile-group rm-has-submenu" data-rm-mobile-group>
        <button
          type="button"
          class="rm-mobile-nav-link rm-mobile-nav-link--group"
          aria-haspopup="true"
          aria-expanded="<?php echo $gAct ? 'true' : 'false' ?>"
          data-rm-mobile-group-trigger>
          <?php echo nav_icon($g['icon'], 18, 2, 'none') ?>
          <span><?php echo e($g['name']) ?></span>
          <?php echo nav_icon('caret-down', 14, 2.4, 'none') ?>
        </button>
        <ul class="rm-mobile-submenu" role="list" <?php echo $gAct ? '' : 'hidden' ?>>
          <?php foreach ($g['items'] as $gi):
            $slug = (string)($gi['slug'] ?? '');
            if ($slug === '') continue;
            $isMedio = in_array($slug, ['radio', 'videos', 'podcast'], true);
            if (!$isMedio) {
                $sec  = rm_resolve_section($sectionsBySlug, $slug, $gi['name']);
                if (empty($sec)) continue;
            } else {
                $sec = [];
            }
            $iAct = !$isMedio ? is_active_route('/seccion/' . $slug) : false;
            if ($slug === 'videos') {
                $mhref = url('/en-vivo');
                $iAct = $iAct || is_active_route('/en-vivo') || is_active_route('/tv');
            } elseif ($slug === 'radio') {
                $mhref = url('/radio');
                $iAct = $iAct || is_active_route('/radio');
            } elseif ($slug === 'podcast') {
                $mhref = url('/podcast');
                $iAct = $iAct || is_active_route('/podcast');
            } else {
                $mhref = section_url($sec);
            }
          ?>
            <li>
              <a
                href="<?php echo e($mhref) ?>"
                class="rm-mobile-nav-link rm-mobile-nav-link--sub <?php echo $iAct ? 'active' : '' ?>"
                <?php if ($iAct): ?>aria-current="page"<?php endif; ?>>
                <?php echo nav_icon($gi['icon'], 16, 1.8, 'none') ?>
                <span><?php echo e($gi['name']) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </li>
    <?php endforeach; ?>
  </ul>

  <?php if (!empty($radioStreamUrl)): ?>
    <div class="rm-mobile-nav-foot">
      <div class="rm-mini-radio" aria-label="Radio en vivo (menú móvil)">
        <audio id="rmMiniAudioMob" preload="none">
          <source src="<?php echo e($radioStreamUrl) ?>" type="audio/mpeg">
        </audio>
        <button
          type="button"
          class="rm-mini-play"
          id="rmMiniPlayBtnMob"
          aria-label="Escuchar radio en vivo"
          aria-pressed="false">
          <span class="rm-mini-play-svg"><?php echo nav_icon('play-circle', 16, 2, 'none') ?></span>
        </button>
        <div class="rm-mini-radio-label">
          <div class="rm-live-indicator">
            <?php echo nav_icon('live-dot', 8, 2, 'currentColor') ?>
            <span>EN VIVO</span>
          </div>
          <span>Radio Amigos por el Mundo</span>
        </div>
      </div>
    </div>
  <?php endif; ?>
</aside>

<style>
/* ----- Drawer móvil + accordion estilos (UIUX redesign) ----- */
.rm-mobile-nav {
  position: fixed;
  inset: 0 auto 0 0;
  width: min(88vw, 340px);
  background: #fff;
  border-right: 1px solid var(--rm-border);
  z-index: 200;
  transform: translateX(-102%);
  transition: transform 260ms cubic-bezier(.2,.8,.2,1);
  box-shadow: 0 20px 60px -24px rgba(15, 23, 42, 0.35);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  contain: layout paint;
}
.rm-mobile-nav.open { transform: translateX(0); }
.rm-nav-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  opacity: 0;
  pointer-events: none;
  transition: opacity 220ms ease;
  z-index: 190;
}
.rm-nav-backdrop.show { opacity: 1; pointer-events: auto; }
body.rm-mobile-open { overflow: hidden; touch-action: none; }

.rm-mobile-nav-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  background: var(--rm-navy);
  color: #fff;
  gap: 12px;
  flex: 0 0 auto;
}
.rm-mobile-nav-title {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.92rem;
  font-weight: 700;
  letter-spacing: 0.02em;
}
.rm-mobile-nav-close {
  width: 34px; height: 34px;
  border-radius: 9999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  background: rgba(255,255,255,0.12);
  transition: background-color 160ms ease, transform 160ms ease;
}
.rm-mobile-nav-close:hover { background: rgba(255,255,255,0.22); transform: scale(1.05); }
.rm-mobile-nav-close > svg { width: 18px; height: 18px; display: block; }

.rm-mobile-nav-search-wrap {
  padding: 14px 16px 6px;
  border-bottom: 1px solid var(--rm-border);
  flex: 0 0 auto;
}
.rm-mobile-search-row {
  display: flex;
  align-items: center;
  gap: 8px;
  height: 44px;
  padding: 0 10px 0 12px;
  background: var(--rm-bg-soft);
  border: 1px solid var(--rm-border);
  border-radius: 10px;
  transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
}
.rm-mobile-search-row:focus-within {
  background: #fff;
  border-color: var(--rm-navy);
  box-shadow: 0 0 0 3px rgba(10, 37, 64, 0.18);
}
.rm-mobile-search-icon { display: inline-flex; color: var(--rm-text-400); }
.rm-mobile-search-icon > svg { width: 16px; height: 16px; display: block; }
.rm-mobile-search-row input[type="search"] {
  flex: 1 1 auto;
  border: 0;
  outline: 0;
  background: transparent;
  height: 42px;
  font-size: 0.92rem;
  color: var(--rm-text);
  min-width: 0;
  padding: 0;
}
.rm-mobile-search-row input[type="search"]::placeholder { color: var(--rm-text-400); }
.rm-mobile-search-submit {
  width: 34px; height: 34px;
  border-radius: 8px;
  background: var(--rm-navy);
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: background-color 160ms ease, transform 160ms ease;
  flex: 0 0 auto;
}
.rm-mobile-search-submit:hover { background: var(--rm-navy-dark); transform: translateY(-1px); }
.rm-mobile-search-submit > svg { width: 16px; height: 16px; display: block; }

.rm-mobile-nav-list {
  list-style: none;
  margin: 0;
  padding: 8px 6px;
  overflow-y: auto;
  flex: 1 1 auto;
  -webkit-overflow-scrolling: touch;
}
.rm-mobile-nav-list > li { margin: 0; }

.rm-mobile-nav-link,
.rm-mobile-nav-link--sub {
  display: flex;
  width: 100%;
  align-items: center;
  gap: 10px;
  padding: 11px 12px;
  font-size: 0.92rem;
  font-weight: 600;
  color: var(--rm-text);
  border-radius: 8px;
  text-decoration: none !important;
  transition: background-color 150ms ease, color 150ms ease, transform 150ms ease;
  background: transparent;
  border: 0;
  text-align: left;
  line-height: 1.25;
  min-height: 44px;
  box-sizing: border-box;
}
.rm-mobile-nav-link > svg:first-child {
  width: 18px; height: 18px; display: block; color: var(--rm-navy); flex: 0 0 auto;
}
.rm-mobile-nav-link > span:nth-of-type(1) { flex: 1 1 auto; min-width: 0; }
.rm-mobile-nav-link:hover,
.rm-mobile-nav-link:focus-visible {
  background: var(--rm-navy-50);
  color: var(--rm-navy);
  transform: translateX(2px);
}
.rm-mobile-nav-link:focus-visible {
  outline: 3px solid rgba(10, 37, 64, 0.3);
  outline-offset: -2px;
}
.rm-mobile-nav-link.active,
.rm-mobile-nav-link[aria-current="page"] {
  background: var(--rm-navy-50);
  color: var(--rm-navy);
  font-weight: 700;
}

.rm-mobile-nav-link--group > svg:last-of-type {
  margin-left: auto;
  width: 14px; height: 14px; display: block; color: var(--rm-text-400);
  transition: transform 160ms ease, color 160ms ease;
}
.rm-mobile-group.rm-is-open > .rm-mobile-nav-link--group > svg:last-of-type {
  transform: rotate(180deg);
  color: var(--rm-navy);
}
.rm-mobile-group.rm-is-open > .rm-mobile-nav-link--group {
  background: var(--rm-navy-50);
  color: var(--rm-navy);
}

.rm-mobile-submenu {
  list-style: none;
  padding: 2px 0 8px 12px;
  margin: 0;
}
.rm-mobile-nav-link--sub {
  font-weight: 500;
  min-height: 40px;
  padding: 9px 12px 9px 10px;
  font-size: 0.88rem;
  color: var(--rm-text-600);
}
.rm-mobile-nav-link--sub > svg:first-child {
  width: 16px; height: 16px; display: block; color: var(--rm-navy); flex: 0 0 auto; opacity: 0.85;
}
.rm-mobile-nav-link--sub.active,
.rm-mobile-nav-link--sub[aria-current="page"] {
  background: var(--rm-navy-50);
  color: var(--rm-navy);
  font-weight: 700;
}

.rm-mobile-nav-foot {
  padding: 12px 16px 18px;
  border-top: 1px solid var(--rm-border);
  background: var(--rm-bg-soft);
  flex: 0 0 auto;
}
.rm-mobile-nav-foot .rm-mini-radio {
  display: inline-flex !important;
  width: 100%;
  justify-content: flex-start;
  border: 1px solid var(--rm-border);
  background: #fff;
  border-radius: 12px;
  padding: 10px 12px 10px 8px;
}
.rm-mobile-nav-foot .rm-mini-radio-label > span:not(.rm-live-indicator) {
  color: var(--rm-text-600) !important;
}
.rm-mobile-nav-foot .rm-live-indicator { color: var(--rm-text) !important; }

/* Focus visible outline global consistente para nav/sheet */
.rm-menu-toggle:focus-visible,
.rm-search-toggle:focus-visible,
.rm-mini-play:focus-visible,
.rm-mobile-nav-link:focus-visible {
  outline: 3px solid rgba(10, 37, 64, 0.42);
  outline-offset: 2px;
}
@media (prefers-reduced-motion: reduce) {
  .rm-mobile-nav,
  .rm-nav-backdrop,
  .rm-mobile-nav-link,
  .rm-mobile-nav-link--group > svg:last-of-type { transition: none !important; }
}
</style>

<script>
(function(){
  'use strict';

  /* ========== 1. Mini radio (desktop) ========== */
  function bindMiniRadio(audioId, btnId) {
    var audio = document.getElementById(audioId);
    var btn   = document.getElementById(btnId);
    if (!audio || !btn) return;
    var playing = false;
    var wrap = btn.querySelector('.rm-mini-play-svg');
    var svgPlay  = <?php echo json_encode(nav_icon('play-circle', 16, 2, 'none')) ?>;
    var svgPause = <?php echo json_encode(nav_icon('pause-circle', 16, 2, 'none')) ?>;
    var svgAlert = <?php echo json_encode(nav_icon('alert', 16, 2, 'none')) ?>;
    function render(state) {
      if (!wrap) return;
      wrap.innerHTML = (state === 'pause') ? svgPause : (state === 'alert' ? svgAlert : svgPlay);
    }
    render('play');
    btn.addEventListener('click', function(){
      if (!playing) {
        audio.play().then(function(){
          playing = true;
          btn.classList.add('playing');
          btn.setAttribute('aria-pressed','true');
          render('pause');
        }).catch(function(){
          render('alert');
          setTimeout(function(){ render('play'); }, 2500);
        });
      } else {
        audio.pause();
        playing = false;
        btn.classList.remove('playing');
        btn.setAttribute('aria-pressed','false');
        render('play');
      }
    });
  }
  bindMiniRadio('rmMiniAudio', 'rmMiniPlayBtn');
  bindMiniRadio('rmMiniAudioMob', 'rmMiniPlayBtnMob');

  /* ========== 2. Drawer móvil (toggle / close / escape) ========== */
  var toggleBtn = document.getElementById('rmMenuToggle');
  var mobileNav = document.getElementById('rmMobileNav');
  var backdrop  = document.getElementById('rmNavBackdrop');
  function closeDrawer(){
    if (!mobileNav || !backdrop || !toggleBtn) return;
    mobileNav.classList.remove('open');
    backdrop.classList.remove('show');
    document.body.classList.remove('rm-mobile-open');
    mobileNav.setAttribute('aria-hidden','true');
    backdrop.setAttribute('aria-hidden','true');
    toggleBtn.setAttribute('aria-expanded','false');
    toggleBtn.classList.remove('is-open');
  }
  function openDrawer(){
    if (!mobileNav || !backdrop || !toggleBtn) return;
    mobileNav.classList.add('open');
    backdrop.classList.add('show');
    document.body.classList.add('rm-mobile-open');
    mobileNav.setAttribute('aria-hidden','false');
    backdrop.setAttribute('aria-hidden','false');
    toggleBtn.setAttribute('aria-expanded','true');
    toggleBtn.classList.add('is-open');
    var first = mobileNav.querySelector('.rm-mobile-nav-link, input, button, [tabindex]:not([tabindex="-1"])');
    if (first) setTimeout(function(){ try { first.focus({preventScroll:true}); } catch(e){} }, 60);
  }
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function(){
      if (mobileNav && mobileNav.classList.contains('open')) closeDrawer(); else openDrawer();
    });
  }
  if (mobileNav) {
    mobileNav.querySelectorAll('[data-rm-nav-close]').forEach(function(el){
      el.addEventListener('click', closeDrawer);
    });
  }
  if (backdrop) backdrop.addEventListener('click', closeDrawer);

  /* ========== 3. Accordion drawer móvil (grupos) ========== */
  document.querySelectorAll('[data-rm-mobile-group-trigger]').forEach(function(trigger){
    trigger.addEventListener('click', function(e){
      e.preventDefault();
      var group = trigger.closest('[data-rm-mobile-group]');
      if (!group) return;
      var sub = group.querySelector('.rm-mobile-submenu');
      var open = group.classList.toggle('rm-is-open');
      trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (sub) {
        if (open) {
          sub.removeAttribute('hidden');
          try { sub.style.maxHeight = sub.scrollHeight + 'px'; } catch(e){}
        } else {
          sub.setAttribute('hidden', '');
          try { sub.style.maxHeight = ''; } catch(e){}
        }
      }
    });
  });

  /* ========== 4. Search sheet desktop (toggle / close / escape / backdrop) ========== */
  var searchWrap    = document.getElementById('rmHeaderSearchWrap');
  var searchToggle  = document.getElementById('rmSearchToggle');
  var searchSheet   = document.getElementById('rmSearchSheet');
  var searchBackdrop= document.getElementById('rmSearchBackdrop');
  var searchInput   = document.getElementById('rmSearchInput');

  function openSearch(){
    if (!searchWrap || !searchToggle || !searchSheet) return;
    searchWrap.classList.add('rm-is-open');
    searchToggle.setAttribute('aria-expanded','true');
    searchSheet.setAttribute('aria-hidden','false');
    try { searchSheet.removeAttribute('hidden'); } catch(e){}
    if (searchBackdrop) { searchBackdrop.classList.add('rm-is-open'); searchBackdrop.setAttribute('aria-hidden','false'); try { searchBackdrop.removeAttribute('hidden'); } catch(e1){} }
    if (searchInput) setTimeout(function(){ searchInput.focus(); searchInput.select(); }, 70);
  }
  function closeSearch(){
    if (!searchWrap || !searchToggle || !searchSheet) return;
    searchWrap.classList.remove('rm-is-open');
    searchToggle.setAttribute('aria-expanded','false');
    searchSheet.setAttribute('aria-hidden','true');
    try { searchSheet.setAttribute('hidden',''); } catch(e){}
    if (searchBackdrop) { searchBackdrop.classList.remove('rm-is-open'); searchBackdrop.setAttribute('aria-hidden','true'); try { searchBackdrop.setAttribute('hidden',''); } catch(e1){} }
  }
  if (searchToggle) {
    searchToggle.addEventListener('click', function(e){
      e.stopPropagation();
      if (searchWrap && searchWrap.classList.contains('rm-is-open')) closeSearch(); else openSearch();
    });
  }
  if (searchSheet) {
    searchSheet.querySelectorAll('[data-rm-search-close]').forEach(function(el){
      el.addEventListener('click', function(e){ e.stopPropagation(); closeSearch(); });
    });
    searchSheet.addEventListener('click', function(e){ e.stopPropagation(); });
  }
  if (searchBackdrop) {
    searchBackdrop.addEventListener('click', function(e){ e.stopPropagation(); closeSearch(); });
  }
  /* === DEFAULT CLOSED SEGURO (fallback anti bug inamovible) === */
  try { window.closeSearch = closeSearch; } catch(e){}
  if (searchWrap && searchSheet) {
    try {
      if (!location.search || !/[?&]q=/.test(location.search)) {
        searchWrap.classList.remove('rm-is-open');
        searchSheet.setAttribute('hidden','');
        searchSheet.setAttribute('aria-hidden','true');
        if (searchToggle) searchToggle.setAttribute('aria-expanded','false');
        if (searchBackdrop) { searchBackdrop.setAttribute('hidden',''); searchBackdrop.classList.remove('rm-is-open'); searchBackdrop.setAttribute('aria-hidden','true'); }
      }
    } catch(e){}
  }

  /* ========== 5. Dropdowns desktop (hover / focus / click outside / escape) ========== */
  var isDesktop = function(){ return window.matchMedia && window.matchMedia('(min-width: 992px)').matches; };
  var navItems = document.querySelectorAll('.rm-nav-item.rm-has-submenu');
  function closeAllDropdowns(){
    navItems.forEach(function(li){
      li.classList.remove('rm-is-open');
      var t = li.querySelector('[aria-haspopup="true"]');
      if (t) t.setAttribute('aria-expanded','false');
    });
  }
  navItems.forEach(function(li){
    var trigger = li.querySelector('[aria-haspopup="true"]');
    function open(){
      closeAllDropdowns();
      li.classList.add('rm-is-open');
      if (trigger) trigger.setAttribute('aria-expanded','true');
    }
    function close(){
      li.classList.remove('rm-is-open');
      if (trigger) trigger.setAttribute('aria-expanded','false');
    }
    li.addEventListener('mouseenter', open);
    li.addEventListener('mouseleave', close);
    if (trigger) {
      trigger.addEventListener('focus', open);
      trigger.addEventListener('click', function(e){
        e.preventDefault();
        if (li.classList.contains('rm-is-open')) close(); else open();
      });
      trigger.addEventListener('keydown', function(e){
        if (e.key === 'ArrowDown') { e.preventDefault(); open(); var firstLink = li.querySelector('.rm-nav-sublink'); if (firstLink) firstLink.focus(); }
        else if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); var firstLink = li.querySelector('.rm-nav-sublink'); if (firstLink) firstLink.focus(); }
      });
    }
    var sublinks = li.querySelectorAll('.rm-nav-sublink');
    sublinks.forEach(function(a, i){
      a.addEventListener('keydown', function(e){
        if (e.key === 'ArrowDown') { e.preventDefault(); var n = sublinks[i+1]; if (n) n.focus(); else if (trigger) trigger.focus(); }
        else if (e.key === 'ArrowUp')   { e.preventDefault(); var p = sublinks[i-1]; if (p) p.focus(); else if (trigger) trigger.focus(); }
        else if (e.key === 'Escape')    { e.preventDefault(); close(); if (trigger) trigger.focus(); }
      });
    });
  });
  if (document.querySelector('.rm-nav-list')) {
    document.querySelector('.rm-nav-list').addEventListener('keydown', function(e){
      var triggers = document.querySelectorAll('.rm-nav-list > .rm-nav-item > .rm-nav-link');
      var idx = -1;
      triggers.forEach(function(t, i){ if (document.activeElement === t) { idx = i; } });
      if (idx < 0) return;
      if (e.key === 'ArrowRight') { e.preventDefault(); triggers[(idx+1) % triggers.length].focus(); closeAllDropdowns(); }
      else if (e.key === 'ArrowLeft') { e.preventDefault(); triggers[(idx-1+triggers.length) % triggers.length].focus(); closeAllDropdowns(); }
    });
  }
  document.addEventListener('click', function(e){
    if (!e.target.closest('.rm-nav-list')) closeAllDropdowns();
  });

  /* ========== 6. Escape cierra drawer + search + dropdowns ========== */
  document.addEventListener('keydown', function(e){
    if (e.code !== 'Escape' && e.key !== 'Escape' && e.keyCode !== 27) return;
    closeAllDropdowns();
    if (typeof closeSearch === 'function') closeSearch();
    if (mobileNav && mobileNav.classList.contains('open')) closeDrawer();
  }, true);

  /* ========== 7. Sticky nav sombra al scrollear ========== */
  var rmNav = document.querySelector('.rm-nav');
  if (rmNav) {
    var onScroll = function(){
      if (window.scrollY > 8) rmNav.classList.add('scrolled'); else rmNav.classList.remove('scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }
})();
</script>

<!-- ============== DRAWER MOVIL RPP NUEVO (img3) ============== -->
<aside class="rm-drawer" id="rmDrawer" role="dialog" aria-modal="true" aria-label="Menú de secciones" hidden>
  <header class="rm-drawer-head">
    <button type="button" class="rm-drawer-close" id="rmDrawerClose" aria-label="Cerrar menú">
      <?php echo nav_icon('close', 22, 2.2, 'none') ?>
    </button>
    <a href="<?php echo url('/') ?>" class="rm-drawer-logo" aria-label="<?php echo e($siteName) ?> · Inicio">
      <?php if ($siteLogoUrl !== ''): ?>
        <img src="<?php echo e($siteLogoUrl) ?>" alt="Logotipo <?php echo e($siteName) ?>" class="rm-logo" loading="eager" style="max-width:70px;">
      <?php endif; ?>
      <span class="rm-brand-text" style="display:grid;max-width:155px;padding:4px 6px;color:#14532d;font-size:11px;font-weight:800;line-height:1.15;text-align:left;">
        <span style="color:#087B43;font-size:9px;letter-spacing:.08em;"><?php echo e($siteKicker) ?></span>
        <span><?php echo e($siteName) ?></span>
      </span>
    </a>
    <a class="rm-drawer-search" href="/noticias" role="button" aria-label="Buscar noticias">
      <span>BUSCAR</span>
      <?php echo nav_icon('search', 18, 2.2, 'none') ?>
    </a>
  </header>

  <div class="rm-drawer-social">
    <p class="rm-drawer-social-title">Síguenos en redes sociales</p>
    <div class="rm-drawer-social-list">
      <?php if (!empty($socialTiktok)): ?>
        <a class="rm-drawer-social-item" href="<?php echo e($socialTiktok) ?>" target="_blank" rel="noopener" title="TikTok" aria-label="TikTok">
          <?php echo nav_icon('tiktok', 18, 2, 'currentColor') ?>
        </a>
      <?php endif; ?>
      <?php if (!empty($socialInstagram)): ?>
        <a class="rm-drawer-social-item" href="<?php echo e($socialInstagram) ?>" target="_blank" rel="noopener" title="Instagram" aria-label="Instagram">
          <?php echo nav_icon('instagram', 18, 2, 'currentColor') ?>
        </a>
      <?php endif; ?>
      <?php if (!empty($socialFacebook)): ?>
        <a class="rm-drawer-social-item" href="<?php echo e($socialFacebook) ?>" target="_blank" rel="noopener" title="Facebook" aria-label="Facebook">
          <?php echo nav_icon('facebook', 18, 2, 'currentColor') ?>
        </a>
      <?php endif; ?>
      <?php if (!empty($socialTwitter)): ?>
        <a class="rm-drawer-social-item" href="<?php echo e($socialTwitter) ?>" target="_blank" rel="noopener" title="X / Twitter" aria-label="X Twitter">
          <?php echo nav_icon('twitter', 18, 2.2, 'currentColor') ?>
        </a>
      <?php endif; ?>
      <?php if (!empty($socialYoutube)): ?>
        <a class="rm-drawer-social-item" href="<?php echo e($socialYoutube) ?>" target="_blank" rel="noopener" title="YouTube" aria-label="YouTube">
          <?php echo nav_icon('youtube', 18, 2, 'currentColor') ?>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <?php
    $waChannelUrl = trim((string)app_setting('social_whatsapp_channel_url', ''));
    if ($waChannelUrl === '' && !empty($socialWhatsapp)) {
      $waDigits = preg_replace('/[^0-9]/','', $socialWhatsapp);
      if ($waDigits !== '') $waChannelUrl = 'https://wa.me/' . $waDigits;
    }
  ?>
  <?php if ($waChannelUrl !== ''): ?>
    <a class="rm-drawer-whatsapp" href="<?php echo e($waChannelUrl) ?>" target="_blank" rel="noopener noreferrer">
      <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="#fff"><path d="M12 2a10 10 0 108.006 16.624l2.757.805a1 1 0 001.262-1.247l-.856-2.89A10 10 0 0012 2zm-1.191 5.009a1 1 0 00-1.227.266 5.13 5.13 0 00-.91 3.094 5.85 5.85 0 00.317 1.766 11.5 11.5 0 003.04 4.26 11.7 11.7 0 004.165 2.637 1 1 0 001.149-.3 1 1 0 00.197-1.083 10.2 10.2 0 00-.618-1.57 1 1 0 01-1.057-.504c-.543.118-1.118.111-1.701-.064-.406-.123-1.156-.435-1.996-1.15-.56-.476-.946-.95-1.202-1.416-.18-.332-.28-.59-.297-.714a.796.796 0 01-.03-.256.999.999 0 01.323-.69 1 1 0 00.232-.96c-.11-.536-.335-1.237-.935-1.88a.993.993 0 00-.996-.227z"/></svg>
      <span>Únete a nuestro canal en WhatsApp</span>
    </a>
  <?php endif; ?>

  <?php
    $promoTitle = trim((string)app_setting('promo_block1_title', ''));
    $promoUrl   = trim((string)app_setting('promo_block1_url', ''));
  ?>
  <?php if ($promoTitle !== '' && $promoUrl !== ''): ?>
    <a class="rm-drawer-promo" href="<?php echo e($promoUrl) ?>" target="_blank" rel="noopener">
      <?php echo e($promoTitle) ?>
    </a>
  <?php endif; ?>

  <?php $mobileSections = (string)app_setting('menu_mobile_sections_enabled', '1') === '1'; ?>
  <div class="rm-drawer-sections-wrap">
    <h4 class="rm-drawer-sec-title">Secciones</h4>
    <ul class="rm-drawer-sections" role="list">
      <li>
        <?php
          $ultSecD = rm_resolve_section($sectionsBySlug, 'ultimas-noticias', 'Últimas noticias');
          $ultHrefD = !empty($ultSecD) ? section_url($ultSecD) : url('/noticias');
        ?>
        <a class="rm-drawer-link" href="<?php echo e($ultHrefD) ?>">
          <span>Últimas noticias</span><span aria-hidden="true">›</span>
        </a>
      </li>
      <?php foreach ($seccionesMenu as $sec): ?>
        <?php
          $slug = (string)($sec['slug'] ?? '');
          if ($slug === '' || $slug === 'ultimas-noticias') continue;
          if (((int)($sec['is_active'] ?? 0)) !== 1) continue;
          $name = (string)($sec['name'] ?? $slug);
          $desc = trim((string)($sec['description'] ?? ''));
          $sid  = 'dsec-' . preg_replace('/[^a-z0-9_-]/i', '-', $slug);
        ?>
        <?php if ($mobileSections): ?>
          <li class="rm-drawer-section">
            <button type="button" class="rm-drawer-section-btn" aria-expanded="false" aria-controls="<?php echo $sid ?>">
              <span><?php echo e($name) ?></span><span class="caret" aria-hidden="true">▾</span>
            </button>
            <div class="rm-drawer-submenu" id="<?php echo $sid ?>">
              <a class="rm-drawer-link rm-drawer-link--sub" href="<?php echo section_url($sec) ?>">
                <span>Ver todas las noticias de <?php echo e($name) ?></span><span aria-hidden="true">›</span>
              </a>
              <?php if ($desc !== ''): ?>
                <p class="rm-drawer-desc muted"><?php echo e($desc) ?></p>
              <?php endif; ?>
            </div>
          </li>
        <?php else: ?>
          <li>
            <a class="rm-drawer-link" href="<?php echo section_url($sec) ?>">
              <span><?php echo e($name) ?></span><span aria-hidden="true">›</span>
            </a>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>

      <li>
        <a class="rm-drawer-link rm-drawer-link--live" href="/en-vivo">
          <span>📺 AMIGOS TV EN VIVO</span><span aria-hidden="true">›</span>
        </a>
      </li>
      <li>
        <a class="rm-drawer-link" href="/radio"><span>📻 Radio</span><span aria-hidden="true">›</span></a>
      </li>
      <li>
        <a class="rm-drawer-link" href="/videos"><span>🎬 Videos / Televisión</span><span aria-hidden="true">›</span></a>
      </li>
      <li>
        <a class="rm-drawer-link" href="/podcast"><span>🎙 Podcast</span><span aria-hidden="true">›</span></a>
      </li>
      <li>
        <a class="rm-drawer-link" href="<?php echo url('/admin') ?>" rel="noopener">
          <span>🔒 Panel Administrativo</span><span aria-hidden="true">›</span>
        </a>
      </li>
    </ul>
  </div>
</aside>
<div class="rm-drawer-overlay" id="rmDrawerOverlay" hidden></div>

<script>
(function () {
  "use strict";
  var miniBtn = document.getElementById("rmMiniRadioBtn");
  var miniAudio = document.getElementById("rmMiniAudio");
  if (miniBtn && miniAudio) {
    var radioSrc = miniBtn.getAttribute("data-src") || "";
    if (radioSrc) try { miniAudio.src = radioSrc; } catch (_) {}
    var isPlaying = false;
    var setPlaying = function (v) {
      isPlaying = !!v;
      miniBtn.setAttribute("aria-pressed", isPlaying ? "true" : "false");
      miniBtn.setAttribute("aria-label", isPlaying ? "Pausar radio" : "Reproducir radio en vivo");
      var ic = miniBtn.querySelector(".rm-play-circle-icon");
      if (ic) ic.innerHTML = isPlaying ? "\u275A\u275A" : "\u25B6";
    };
    miniAudio.addEventListener("play", function () { setPlaying(true); });
    miniAudio.addEventListener("pause", function () { setPlaying(false); });
    miniAudio.addEventListener("error", function () {
      setPlaying(false);
      try {
        var msg = "No se pudo reproducir la señal de radio. Se abrir\u00e1 en una nueva ventana.";
        if (window.RM && typeof window.RM.toast === "function") window.RM.toast(msg, "warn", 4200);
        else console.warn(msg);
        if (radioSrc) setTimeout(function () { window.open(radioSrc, "_blank", "noopener"); }, 700);
      } catch (_) {}
    });
    miniBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (!radioSrc) {
        try {
          var m = "A\u00fan no se ha configurado la URL de la radio en el panel administrativo.";
          if (window.RM && typeof window.RM.toast === "function") window.RM.toast(m, "warn", 4500);
          else alert(m);
        } catch (_) {}
        return;
      }
      try {
        if (isPlaying) {
          miniAudio.pause();
          try { miniAudio.removeAttribute("src"); } catch (_) {}
          try { miniAudio.load(); } catch (_) {}
          setPlaying(false);
          return;
        }
        miniAudio.setAttribute("src", radioSrc);
        miniAudio.crossOrigin = "anonymous";
        miniAudio.preload = "auto";
        miniAudio.volume = 1.0;
        var p = miniAudio.play();
        if (p && typeof p.then === "function") {
          p.then(function () { setPlaying(true); }).catch(function () {
            setPlaying(false);
            try {
              var m2 = "No se pudo reproducir. Se abrir\u00e1 en una nueva ventana.";
              if (window.RM && typeof window.RM.toast === "function") window.RM.toast(m2, "warn", 4000);
            } catch (_) {}
            try { window.open(radioSrc, "_blank", "noopener"); } catch (_) {}
          });
        } else setPlaying(true);
      } catch (err) {
        setPlaying(false);
        try {
          if (window.RM && typeof window.RM.toast === "function") window.RM.toast("Error al reproducir radio.", "error", 3500);
          window.open(radioSrc, "_blank", "noopener");
        } catch (_) {}
      }
    }, true);
  }

  var drawer = document.getElementById("rmDrawer");
  var overlay = document.getElementById("rmDrawerOverlay");
  var openBtn = document.getElementById("rmDrawerOpen");
  var closeBtn = document.getElementById("rmDrawerClose");
  var bodyLockCls = "rm-drawer-open";
  var openCls = "is-open";
  var showCls = "show";

  var closeDrawer = function () {
    try {
      if (drawer) {
        drawer.classList.remove(openCls);
        drawer.setAttribute("hidden", "");
        drawer.setAttribute("aria-hidden", "true");
      }
      if (overlay) {
        overlay.classList.remove(showCls);
        overlay.setAttribute("hidden", "");
      }
      document.body.classList.remove(bodyLockCls);
      try { document.documentElement.classList.remove(bodyLockCls); } catch (_) {}
      if (openBtn) try { openBtn.setAttribute("aria-expanded","false"); openBtn.classList.remove("is-open"); } catch(_){}
    } catch (_) {}
  };
  var openDrawer = function () {
    try {
      if (drawer) {
        drawer.removeAttribute("hidden");
        drawer.setAttribute("aria-hidden", "false");
        void drawer.offsetWidth;
        drawer.classList.add(openCls);
      }
      if (overlay) {
        overlay.removeAttribute("hidden");
        void overlay.offsetWidth;
        overlay.classList.add(showCls);
      }
      document.body.classList.add(bodyLockCls);
      try { document.documentElement.classList.add(bodyLockCls); } catch (_) {}
      if (openBtn) try { openBtn.setAttribute("aria-expanded","true"); openBtn.classList.add("is-open"); } catch(_){}
      if (closeBtn) try { closeBtn.focus({preventScroll:true}); } catch (_) {}
    } catch (_) {}
  };
  var toggleDrawer = function () {
    try {
      var isOpen = drawer && drawer.classList.contains(openCls);
      if (isOpen) closeDrawer(); else openDrawer();
    } catch (_) {}
  };

  if (openBtn) openBtn.addEventListener("click", function (e) { e.preventDefault(); e.stopImmediatePropagation(); toggleDrawer(); }, true);
  if (closeBtn) closeBtn.addEventListener("click", function (e) { e.preventDefault(); e.stopImmediatePropagation(); closeDrawer(); }, true);
  if (overlay) overlay.addEventListener("click", function (e) { e.preventDefault(); closeDrawer(); }, true);

  document.addEventListener("keydown", function (e) {
    if (e.code === "Escape" || e.key === "Escape" || e.keyCode === 27) {
      if (drawer && drawer.classList.contains(openCls)) { closeDrawer(); e.preventDefault(); e.stopImmediatePropagation(); }
    }
  }, true);

  if (drawer) {
    drawer.addEventListener("click", function (e) {
      var t = e.target;
      if (t && t.closest && t.closest(".rm-drawer-link")) {
        setTimeout(function () { try { closeAllPanels(); } catch (_) {} }, 120);
      }
    }, true);
  }

  var secBtns = document.querySelectorAll(".rm-drawer-section-btn");
  if (secBtns && secBtns.length) {
    for (var i = 0; i < secBtns.length; i++) {
      (function (btn) {
        btn.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();
          var parent = btn.closest(".rm-drawer-section");
          if (!parent) return;
          var expanded = btn.getAttribute("aria-expanded") === "true";
          btn.setAttribute("aria-expanded", expanded ? "false" : "true");
          parent.setAttribute("aria-expanded", expanded ? "false" : "true");
          if (expanded) parent.classList.remove("is-open"); else parent.classList.add("is-open");
        }, true);
      })(secBtns[i]);
    }
  }
})();
</script>

<script>
(function(){
  var wrap = document.querySelector('.rm-header-wrap.rm-header-rpp');
  if (!wrap) return;
  var SC = 'rm-is-sticky-scrolled';
  function upd(){ wrap.classList.toggle(SC, window.scrollY > 4); }
  upd();
  window.addEventListener('scroll', upd, { passive: true });
  window.addEventListener('touchmove', upd, { passive: true });
  window.addEventListener('load', upd);
})();
</script>
