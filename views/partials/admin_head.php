<?php
/**
 * Partial: Head HTML admin
 * MOD: Cache Busting AUTOMÁTICO + NO-CACHE HEADERS (fix versiones antiguas)
 */
$metaTitle = $metaTitle ?? 'Panel Administrativo | ' . app_setting('site_name');
$favicon   = app_setting('site_favicon', '');
// Convertir ruta relativa de upload a URL completa (si no empieza por / o http)
$faviconUrl = '';
if (trim((string)$favicon) !== '') {
    $faviconUrl = (str_starts_with($favicon, '/') || preg_match('#^https?://#i', $favicon))
        ? $favicon
        : upload_url($favicon);
}
$faviconPngDefault = asset('img/favicon-icon.png') . _imm_cacheBust('/assets/img/favicon-icon.png');
$faviconSvgDefault = asset('img/favicon.svg')       . _imm_cacheBust('/assets/img/favicon.svg');

$titleAnimEnabled = (string)app_setting('site_title_animation_enabled', '1') === '1';
$titleAnimText    = trim((string)app_setting('site_title_animated', 'AMIGOS POR EL MUNDO · Solidaridad, educación y medio ambiente'));
if ($titleAnimText === '') { $titleAnimText = 'AMIGOS POR EL MUNDO · Solidaridad, educación y medio ambiente'; }

$siteName  = app_setting('site_name', 'ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO');
$logo      = app_setting('site_logo', '');
$primary   = app_setting('site_primary_color',   '#087B43');
$secondary = app_setting('site_secondary_color', '#85C440');
$accent    = app_setting('site_accent_color',    '#F3BF24');
$blueDark  = app_setting('site_blue_dark',       '#123B2B');
$greenDark = app_setting('site_green_dark',      '#075B38');
$headerBg  = app_setting('site_header_bg',       '#087B43');
$footerBg  = app_setting('site_footer_bg',       '#092C21');
$csrfName  = config_val('security.csrf_token_name', '_csrf_token');

// ✅ FIX CACHE 1/2: HEADERS PHP NO-CACHE para panel admin (siempre versión actual DB)
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

// ✅ FIX CACHE 2/2: URLs CSS con cache busting AUTOMÁTICO (filemtime hash)
$cssApp    = asset('css/app.css')    . _imm_cacheBust('/assets/css/app.css');
$cssAdmin  = asset('css/admin.css')  . _imm_cacheBust('/assets/css/admin.css');
$jsApp     = asset('js/app.js')       . _imm_cacheBust('/assets/js/app.js');
$jsAdmin   = asset('js/admin.js')     . _imm_cacheBust('/assets/js/admin.js');
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($metaTitle) ?></title>
<?php if ($faviconUrl !== ''): ?>
<link rel="icon" type="image/png" sizes="any" href="<?= e($faviconUrl) ?>">
<?php else: ?>
<link rel="icon" type="image/png" sizes="any" href="<?= $faviconPngDefault ?>">
<?php endif; ?>
<link rel="alternate icon" type="image/svg+xml" href="<?= $faviconSvgDefault ?>">
<script>
(function(){
  var _origTitle = document.title;
  var _enabled   = <?php echo $titleAnimEnabled ? 'true' : 'false' ?>;
  var _text      = <?php echo json_encode($titleAnimText, JSON_UNESCAPED_UNICODE) ?>;
  var _iv        = null;
  function _start(){
    if (!_enabled) return;
    if (typeof window.matchMedia === 'function' && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var _buf = _text + '   ' + _text + '   ';
    var i = 0;
    if (_iv) clearInterval(_iv);
    _iv = setInterval(function(){
      var slice = _buf.substring(i, i + 60);
      document.title = (slice.length < 60 ? slice + ' · ' : slice) + ' | Admin Amigos por el Mundo';
      i = (i + 1) % (_text.length + 3);
    }, 350);
  }
  function _stop(){
    if (_iv) { clearInterval(_iv); _iv = null; }
    document.title = _origTitle;
  }
  window.addEventListener('focus', _start);
  window.addEventListener('blur',  _stop);
  document.addEventListener('visibilitychange', function(){
    if (document.hidden) _stop(); else _start();
  });
  if (!document.hidden) _start();
})();
</script>
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="<?= e($primary) ?>">

<!-- ✅ FIX CACHE: META TAGS NO-CACHE (backup navegadores antiguos) -->
<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Thu, 01 Jan 1970 00:00:00 GMT">
<meta http-equiv="Permissions-Policy" content="geolocation=(), microphone=(), camera=()">

<!-- CSS CON CACHE BUSTING AUTOMÁTICO (actualiza SIN Ctrl+Shift+R) -->
<link rel="preload" as="style" href="<?= $cssApp ?>">
<link rel="preload" as="style" href="<?= $cssAdmin ?>">
<link rel="stylesheet" href="<?= $cssApp ?>">
<link rel="stylesheet" href="<?= $cssAdmin ?>">

<style>
  :root {
    --color-primary:   <?= e($primary) ?>;
    --color-secondary: <?= e($secondary) ?>;
    --color-accent:    <?= e($accent) ?>;
    --color-blue-dark: <?= e($blueDark) ?>;
    --color-green-dark:<?= e($greenDark) ?>;
    --rm-header-bg:    <?= e($headerBg) ?>;
    --rm-footer-bg:    <?= e($footerBg) ?>;
  }
  /* Override gradiente sidebar admin con vars del tema (V55F Colores del tema) */
  .app-sidebar.admin-sidebar,
  .admin-sidebar {
    background: linear-gradient(180deg,
      var(--color-primary, <?= e($primary) ?>) 0%,
      var(--color-blue-dark, <?= e($blueDark) ?>) 55%,
      var(--color-green-dark, <?= e($greenDark) ?>) 100%) !important;
    background-image: linear-gradient(180deg,
      var(--color-primary, <?= e($primary) ?>) 0%,
      var(--color-blue-dark, <?= e($blueDark) ?>) 55%,
      var(--color-green-dark, <?= e($greenDark) ?>) 100%) !important;
  }
  /* Badge accent color usa --color-accent */
  .badge-accent,
  .admin-badge-role {
    background: color-mix(in srgb, var(--color-accent, #F97316) 20%, transparent) !important;
    color: var(--color-accent, #F97316) !important;
    filter: brightness(1.1);
  }
  .btn-primary,
  .btn.btn-primary {
    background: var(--color-primary, <?= e($primary) ?>) !important;
    border-color: var(--color-primary, <?= e($primary) ?>) !important;
  }
  .btn-secondary,
  .btn.btn-secondary {
    background: var(--color-secondary, <?= e($secondary) ?>) !important;
    border-color: var(--color-secondary, <?= e($secondary) ?>) !important;
  }
  .btn-accent,
  .btn.btn-accent {
    background: var(--color-accent, <?= e($accent) ?>) !important;
    border-color: var(--color-accent, <?= e($accent) ?>) !important;
    color: #fff;
  }

  /* ================================================================
     FIX GLOBAL: ADMIN FORMS — TEXTO 100% VISIBLE en TODOS los forms
     PANEL ADMIN CLARO (tema verde Amigos por el Mundo).
     ================================================================ */
  body.admin-body .app-main.admin-main form.card label,
  body.admin-body .app-main.admin-main form.card .form-label,
  body.admin-body .app-main.admin-main form label,
  body.admin-body .app-main.admin-main .card form label,
  body.admin-body .app-main.admin-main .card .form-label {
    color: #1E293B !important;
    font-weight: 600 !important;
  }
  body.admin-body .app-main.admin-main form.card form label abbr[title="required"],
  body.admin-body .app-main.admin-main form.card label sup,
  body.admin-body .app-main.admin-main form.card label .required,
  body.admin-body .app-main.admin-main form.card label > span[style*="color: red"],
  body.admin-body .app-main.admin-main form.card label > .text-danger,
  body.admin-body .app-main.admin-main form.card label .required-mark {
    color: #f59e0b !important;
  }
  body.admin-body .app-main.admin-main form.card .card-header.nav-tabs-card-header .nav-item .nav-link,
  body.admin-body .app-main.admin-main form.card ul.nav-tabs.card-header-tabs .nav-link,
  body.admin-body .app-main.admin-main form.card .nav-tabs .nav-link,
  body.admin-body .app-main.admin-main .card .nav-tabs .nav-link {
    color: #475569 !important;
  }
  body.admin-body .app-main.admin-main form.card .card-header.nav-tabs-card-header .nav-item .nav-link.active,
  body.admin-body .app-main.admin-main form.card ul.nav-tabs.card-header-tabs .nav-link.active,
  body.admin-body .app-main.admin-main form.card .nav-tabs .nav-link.active,
  body.admin-body .app-main.admin-main .card .nav-tabs .nav-link.active {
    color: #0f172a !important;
    background-color: #ffffff !important;
    border-color: #e2e8f0 #e2e8f0 #ffffff !important;
  }
  body.admin-body .app-main.admin-main form.card .card-header.nav-tabs-card-header .nav-item .nav-link:hover:not(.active),
  body.admin-body .app-main.admin-main form.card ul.nav-tabs.card-header-tabs .nav-link:hover:not(.active),
  body.admin-body .app-main.admin-main form.card .nav-tabs .nav-link:hover:not(.active),
  body.admin-body .app-main.admin-main .card .nav-tabs .nav-link:hover:not(.active) {
    color: #087B43 !important;
    background-color: #F1F8F3 !important;
  }
  body.admin-body .app-main.admin-main form.card .tab-pane,
  body.admin-body .app-main.admin-main form.card .tab-content,
  body.admin-body .app-main.admin-main form.card fieldset legend,
  body.admin-body .app-main.admin-main form.card .h3,
  body.admin-body .app-main.admin-main form.card .h4,
  body.admin-body .app-main.admin-main form.card h3,
  body.admin-body .app-main.admin-main form.card h4,
  body.admin-body .app-main.admin-main form.card .form-card-title,
  body.admin-body .app-main.admin-main form.card .card-title,
  body.admin-body .app-main.admin-main .card .card-title,
  body.admin-body .app-main.admin-main .card .card-title-sm,
  body.admin-body .app-main.admin-main .card fieldset legend,
  body.admin-body .app-main.admin-main .card h3,
  body.admin-body .app-main.admin-main .card h4 {
    color: #0F172A !important;
  }
  body.admin-body .app-main.admin-main form.card .form-help,
  body.admin-body .app-main.admin-main form.card .text-muted,
  body.admin-body .app-main.admin-main form.card small.form-text,
  body.admin-body .app-main.admin-main form.card small:not(.badge),
  body.admin-body .app-main.admin-main form.card p.form-hint,
  body.admin-body .app-main.admin-main form.card .form-text,
  body.admin-body .app-main.admin-main form.card .input-group-text,
  body.admin-body .app-main.admin-main .card .text-muted,
  body.admin-body .app-main.admin-main .card .form-help,
  body.admin-body .app-main.admin-main .card small.form-text,
  body.admin-body .app-main.admin-main .card small:not(.badge) {
    color: #64748B !important;
  }
  body.admin-body .app-main.admin-main .form-control,
  body.admin-body .app-main.admin-main .form-control-sm,
  body.admin-body .app-main.admin-main .form-control-lg,
  body.admin-body .app-main.admin-main .form-select,
  body.admin-body .app-main.admin-main .form-select-sm,
  body.admin-body .app-main.admin-main .form-select-lg,
  body.admin-body .app-main.admin-main textarea,
  body.admin-body .app-main.admin-main input[type="text"],
  body.admin-body .app-main.admin-main input[type="number"],
  body.admin-body .app-main.admin-main input[type="search"],
  body.admin-body .app-main.admin-main input[type="email"],
  body.admin-body .app-main.admin-main input[type="url"],
  body.admin-body .app-main.admin-main input[type="password"],
  body.admin-body .app-main.admin-main input[type="date"],
  body.admin-body .app-main.admin-main input[type="datetime-local"],
  body.admin-body .app-main.admin-main input[type="time"],
  body.admin-body .app-main.admin-main input[type="tel"],
  body.admin-body .app-main.admin-main input[type="month"],
  body.admin-body .app-main.admin-main input[type="week"],
  body.admin-body .app-main.admin-main select,
  body.admin-body .app-main.admin-main select.form-control,
  body.admin-body .app-main.admin-main select.form-select {
    color: #000000 !important;
    background-color: #ffffff !important;
    background: #ffffff !important;
    border-color: #cbd5e1 !important;
  }
  body.admin-body .app-main.admin-main .form-control:focus,
  body.admin-body .app-main.admin-main textarea:focus,
  body.admin-body .app-main.admin-main input:focus,
  body.admin-body .app-main.admin-main select:focus,
  body.admin-body .app-main.admin-main .form-select:focus {
    color: #000000 !important;
    background-color: #ffffff !important;
    background: #ffffff !important;
    border-color: #087B43 !important;
    outline: none !important;
    box-shadow: 0 0 0 2px rgba(8, 123, 67, .18) !important;
  }
  body.admin-body .app-main.admin-main .form-control::placeholder,
  body.admin-body .app-main.admin-main textarea::placeholder,
  body.admin-body .app-main.admin-main input::placeholder,
  body.admin-body .app-main.admin-main .form-select::placeholder {
    color: #475569 !important;
    opacity: 1 !important;
  }
  body.admin-body .app-main.admin-main form.card .form-check-label,
  body.admin-body .app-main.admin-main form.card .form-switch .form-check-label,
  body.admin-body .app-main.admin-main form.card .form-check input[type="checkbox"] + label,
  body.admin-body .app-main.admin-main form.card .form-check input[type="radio"] + label,
  body.admin-body .app-main.admin-main .card .form-check-label {
    color: #1E293B !important;
  }
  body.admin-body .app-main.admin-main form.card .wysiwyg-editor,
  body.admin-body .app-main.admin-main form.card .wysiwyg-wrap,
  body.admin-body .app-main.admin-main form.card [contenteditable="true"],
  body.admin-body .app-main.admin-main form.card .wysiwyg-content,
  body.admin-body .app-main.admin-main .card .wysiwyg-editor,
  body.admin-body .app-main.admin-main .card .wysiwyg-wrap,
  body.admin-body .app-main.admin-main .card [contenteditable="true"],
  body.admin-body .app-main.admin-main .card .wysiwyg-content {
    color: #000000 !important;
    background-color: #ffffff !important;
  }
  body.admin-body .app-main.admin-main form.card .wysiwyg-editor *,
  body.admin-body .app-main.admin-main form.card .wysiwyg-wrap *,
  body.admin-body .app-main.admin-main form.card [contenteditable="true"] *,
  body.admin-body .app-main.admin-main form.card .wysiwyg-content *,
  body.admin-body .app-main.admin-main .card .wysiwyg-editor *,
  body.admin-body .app-main.admin-main .card .wysiwyg-wrap *,
  body.admin-body .app-main.admin-main .card [contenteditable="true"] *,
  body.admin-body .app-main.admin-main .card .wysiwyg-content * {
    color: #000000 !important;
    background-color: transparent !important;
  }
  body.admin-body .app-main.admin-main form.card code,
  body.admin-body .app-main.admin-main .card code {
    color: #be123c !important;
    background: #fef2f2 !important;
    padding: .1rem .35rem;
    border-radius: 6px;
  }
  body.admin-body .app-main.admin-main form.card .badge,
  body.admin-body .app-main.admin-main form.card .form-badge,
  body.admin-body .app-main.admin-main form.card .alert.alert-info code,
  body.admin-body .app-main.admin-main .card .badge {
    color: #1E293B !important;
  }

  @media (min-width: 992px) {
    body.admin-body .app-main.admin-main .row.g-4.align-items-stretch#dashboardMainRow {
      display: grid !important;
      grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) !important;
      gap: 1rem !important;
      align-items: stretch !important;
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    body.admin-body .app-main.admin-main .row.g-4.align-items-stretch#dashboardMainRow > #dashColNews {
      grid-column: 1 / 2 !important;
      width: auto !important;
      max-width: none !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 .75rem !important;
      box-sizing: border-box !important;
    }
    body.admin-body .app-main.admin-main .row.g-4.align-items-stretch#dashboardMainRow > #dashColAudit {
      grid-column: 2 / 3 !important;
      width: auto !important;
      max-width: none !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 .75rem !important;
      box-sizing: border-box !important;
    }
    body.admin-body .app-main.admin-main .row.g-4.align-items-stretch#dashboardMainRow > [id^="dashCol"] .card.h-100 {
      height: 100% !important;
      min-height: 100% !important;
    }
  }
  @media (max-width: 991.98px) {
    body.admin-body .app-main.admin-main .row.g-4.align-items-stretch#dashboardMainRow {
      display: grid !important;
      grid-template-columns: minmax(0, 1fr) !important;
      gap: 1rem !important;
      align-items: stretch !important;
      width: 100% !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    body.admin-body .app-main.admin-main .row.g-4.align-items-stretch#dashboardMainRow > #dashColNews,
    body.admin-body .app-main.admin-main .row.g-4.align-items-stretch#dashboardMainRow > #dashColAudit {
      grid-column: 1 / 2 !important;
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 !important;
    }
  }

  /* Fix GLOBAL FORMULARIO VIDEOS (Bug user: Thumbnail/Portada + Buenas prácticas ABAJO → AL COSTADO sidebar derecho).
     Mismo robustez CSS GRID que dashboard, evita flex-wrap de row g-4 col-lg-8/4. */
  @media (min-width: 992px) {
    body.admin-body .app-main.admin-main #videosFormRow {
      display: grid !important;
      grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) !important;
      gap: 1.5rem !important;
      align-items: start !important;
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    body.admin-body .app-main.admin-main #videosFormRow > #videosFormColMain {
      grid-column: 1 / 2 !important;
      width: auto !important;
      max-width: none !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 !important;
      box-sizing: border-box !important;
    }
    body.admin-body .app-main.admin-main #videosFormRow > #videosFormColSide {
      grid-column: 2 / 3 !important;
      width: auto !important;
      max-width: none !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 !important;
      box-sizing: border-box !important;
    }
  }
  @media (max-width: 991.98px) {
    body.admin-body .app-main.admin-main #videosFormRow {
      display: grid !important;
      grid-template-columns: minmax(0, 1fr) !important;
      gap: 1.5rem !important;
      align-items: stretch !important;
      width: 100% !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    body.admin-body .app-main.admin-main #videosFormRow > #videosFormColMain,
    body.admin-body .app-main.admin-main #videosFormRow > #videosFormColSide {
      grid-column: 1 / 2 !important;
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 !important;
    }
  }

  /* Fix GLOBAL FORMULARIO PODCAST (Bug user: Portada del episodio ABAJO → AL COSTADO sidebar derecho).
     Mismo robustez CSS GRID que dashboard/videos, evita flex-wrap de row g-4 col-lg-8/4. */
  @media (min-width: 992px) {
    body.admin-body .app-main.admin-main #podcastFormRow {
      display: grid !important;
      grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) !important;
      gap: 1.5rem !important;
      align-items: start !important;
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    body.admin-body .app-main.admin-main #podcastFormRow > #podcastFormColMain {
      grid-column: 1 / 2 !important;
      width: auto !important;
      max-width: none !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 !important;
      box-sizing: border-box !important;
    }
    body.admin-body .app-main.admin-main #podcastFormRow > #podcastFormColSide {
      grid-column: 2 / 3 !important;
      width: auto !important;
      max-width: none !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 !important;
      box-sizing: border-box !important;
    }
  }
  @media (max-width: 991.98px) {
    body.admin-body .app-main.admin-main #podcastFormRow {
      display: grid !important;
      grid-template-columns: minmax(0, 1fr) !important;
      gap: 1.5rem !important;
      align-items: stretch !important;
      width: 100% !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    body.admin-body .app-main.admin-main #podcastFormRow > #podcastFormColMain,
    body.admin-body .app-main.admin-main #podcastFormRow > #podcastFormColSide {
      grid-column: 1 / 2 !important;
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 !important;
    }
  }

  /* Fix GLOBAL FORMULARIO NOTICIAS (Bug: Layout wrap row g-3 col-md-* flex-wrap).
     Misma robustez CSS GRID que podcast/videos/dashboard. */
  @media (min-width: 992px) {
    body.admin-body .app-main.admin-main #newsFormRow {
      display: grid !important;
      grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) !important;
      gap: 1.5rem !important;
      align-items: start !important;
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    body.admin-body .app-main.admin-main #newsFormRow > #newsFormColMain {
      grid-column: 1 / 2 !important;
      width: auto !important;
      max-width: none !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 !important;
      box-sizing: border-box !important;
    }
    body.admin-body .app-main.admin-main #newsFormRow > #newsFormColSide {
      grid-column: 2 / 3 !important;
      width: auto !important;
      max-width: none !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 !important;
      box-sizing: border-box !important;
    }
  }
  @media (max-width: 991.98px) {
    body.admin-body .app-main.admin-main #newsFormRow {
      display: grid !important;
      grid-template-columns: minmax(0, 1fr) !important;
      gap: 1.5rem !important;
      align-items: stretch !important;
      width: 100% !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    body.admin-body .app-main.admin-main #newsFormRow > #newsFormColMain,
    body.admin-body .app-main.admin-main #newsFormRow > #newsFormColSide {
      grid-column: 1 / 2 !important;
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 !important;
    }
  }

  /* Fix GLOBAL FORMULARIO PUBLICACIONES (Bug: row g-4 col-lg-8/4 flex-wrap → sidebar cae abajo).
     Misma robustez CSS GRID. */
  @media (min-width: 992px) {
    body.admin-body .app-main.admin-main #publicationsFormRow {
      display: grid !important;
      grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) !important;
      gap: 1.5rem !important;
      align-items: start !important;
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    body.admin-body .app-main.admin-main #publicationsFormRow > #publicationsFormColMain {
      grid-column: 1 / 2 !important;
      width: auto !important;
      max-width: none !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 !important;
      box-sizing: border-box !important;
    }
    body.admin-body .app-main.admin-main #publicationsFormRow > #publicationsFormColSide {
      grid-column: 2 / 3 !important;
      width: auto !important;
      max-width: none !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 !important;
      box-sizing: border-box !important;
    }
  }
  @media (max-width: 991.98px) {
    body.admin-body .app-main.admin-main #publicationsFormRow {
      display: grid !important;
      grid-template-columns: minmax(0, 1fr) !important;
      gap: 1.5rem !important;
      align-items: stretch !important;
      width: 100% !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    body.admin-body .app-main.admin-main #publicationsFormRow > #publicationsFormColMain,
    body.admin-body .app-main.admin-main #publicationsFormColSide {
      grid-column: 1 / 2 !important;
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
      flex: none !important;
      padding: 0 !important;
    }
  }

  .chars-counter {
    display: block;
    text-align: right;
    font-size: .75rem;
    color: #64748b;
    margin-top: 4px;
    font-variant-numeric: tabular-nums;
  }
  .chars-counter.is-over {
    color: #be123c !important;
    font-weight: 600;
  }
</style>
<meta name="<?= e($csrfName) ?>" content="<?= e(csrf_token()) ?>">
<script defer src="<?= $jsAdmin ?>"></script>
</head>
<body class="admin-body">
<script>
(function(){
  document.addEventListener('DOMContentLoaded', function() {

    /* =========================================
       T9: JS Global Submit Loading State
       Formularios con id finalizado en FormMain + needs-validation
       ========================================= */
    try {
      var forms = document.querySelectorAll('form[id$="FormMain"].needs-validation');
      for (var fi = 0; fi < forms.length; fi++) {
        (function(form) {
          form.addEventListener('submit', function(ev) {
            if (form.checkValidity && form.checkValidity() === false) {
              ev.preventDefault();
              ev.stopPropagation();
              form.classList.add('was-validated');
              return;
            }

            var formId = form.getAttribute('id') || '';
            var btns = [];

            var insideBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            for (var ib = 0; ib < insideBtns.length; ib++) btns.push(insideBtns[ib]);

            if (formId) {
              var outside = document.querySelectorAll('[form="' + formId + '"]');
              for (var ob = 0; ob < outside.length; ob++) {
                var el = outside[ob];
                var tn = (el.tagName || '').toLowerCase();
                if ((tn === 'button' && (el.getAttribute('type') || 'submit') === 'submit') ||
                    (tn === 'input' && (el.getAttribute('type') || '').toLowerCase() === 'submit')) {
                  btns.push(el);
                }
              }
            }

            for (var b = 0; b < btns.length; b++) {
              (function(btn) {
                btn.setAttribute('aria-busy', 'true');
                btn.setAttribute('disabled', 'true');
                btn.style.opacity = '0.85';
                btn.style.pointerEvents = 'none';
                var origHTML = btn.innerHTML;
                btn.setAttribute('data-orig-html', origHTML);
                if (!/⏳/.test(origHTML)) {
                  btn.innerHTML = '⏳ ' + origHTML;
                }
                setTimeout(function() {
                  try {
                    btn.removeAttribute('aria-busy');
                    btn.removeAttribute('disabled');
                    btn.style.opacity = '';
                    btn.style.pointerEvents = '';
                    var saved = btn.getAttribute('data-orig-html');
                    if (saved) btn.innerHTML = saved;
                    btn.removeAttribute('data-orig-html');
                  } catch(_) {}
                }, 25000);
              })(btns[b]);
            }
          });
        })(forms[fi]);
      }
    } catch(e) { console.warn('Submit Loading State init failed:', e); }

    /* =========================================
       T10: Contadores Caracteres Globales
       Detecta [data-chars-max] y renderiza contador live
       ========================================= */
    try {
      var fields = document.querySelectorAll('[data-chars-max]');
      for (var i = 0; i < fields.length; i++) {
        (function(fld) {
          var max = parseInt(fld.getAttribute('data-chars-max') || '0', 10);
          if (!max || max <= 0) return;

          var cnt = document.createElement('small');
          cnt.className = 'chars-counter';

          function update() {
            var len = (fld.value || '').length;
            cnt.textContent = len + ' / ' + max;
            if (len > max) {
              cnt.classList.add('is-over');
            } else {
              cnt.classList.remove('is-over');
            }
          }

          var events = ['input', 'change', 'keyup', 'paste', 'blur'];
          for (var ei = 0; ei < events.length; ei++) {
            fld.addEventListener(events[ei], update, {passive:true});
          }

          try {
            var pn = fld.parentNode;
            if (pn) {
              var ns = fld.nextSibling;
              if (ns && ns.parentNode === pn) {
                pn.insertBefore(cnt, ns);
              } else {
                pn.appendChild(cnt);
              }
            }
          } catch(e) {
            try { fld.parentNode.appendChild(cnt); } catch(_) {}
          }
          update();
        })(fields[i]);
      }
    } catch(e) { console.warn('Chars counters init failed:', e); }

  });
})();
</script>
<div class="app-layout admin-layout" id="adminLayout">
