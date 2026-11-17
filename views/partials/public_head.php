<?php
$metaTitle = $metaTitle ?? app_setting('seo_home_title', app_setting('site_name', 'ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO'));
$metaDesc  = $metaDesc  ?? app_setting('seo_home_desc', '');
$siteHeaderBg   = app_setting('site_header_bg',   '#0052A5');
$siteHeaderText = app_setting('site_header_text', '#FFFFFF');
$sitePrimary    = app_setting('site_primary_color', '#0052A5');
$siteSecondary  = app_setting('site_secondary_color', '#00A651');
$siteAccent     = app_setting('site_accent_color', '#22C55E');
$siteBlueDark   = app_setting('site_blue_dark',   '#003366');
$siteGreenDark  = app_setting('site_green_dark',  '#007A3D');
$siteBreakingBg   = app_setting('site_breaking_bg',   '#DC2626');
$siteBreakingText = app_setting('site_breaking_text', '#FFFFFF');
$siteFooterBg   = app_setting('site_footer_bg',   '#0A1628');
$siteFooterText = app_setting('site_footer_text', '#E2E8F0');

$cssApp = asset('css/app.css') . _imm_cacheBust('/assets/css/app.css');
$jsApp  = asset('js/app.js')  . _imm_cacheBust('/assets/js/app.js');

$fontRegular  = asset('fonts/Inter/Inter-Regular.woff2')  . _imm_cacheBust('/assets/fonts/Inter/Inter-Regular.woff2');
$fontBold     = asset('fonts/Inter/Inter-Bold.woff2')     . _imm_cacheBust('/assets/fonts/Inter/Inter-Bold.woff2');
$fontExtraBold= asset('fonts/Inter/Inter-ExtraBold.woff2'). _imm_cacheBust('/assets/fonts/Inter/Inter-ExtraBold.woff2');
$faviconCustom = trim((string)app_setting('site_favicon', ''));
// Convertir ruta relativa de upload a URL completa (si no empieza por / o http)
$faviconCustomUrl = '';
if ($faviconCustom !== '') {
    $faviconCustomUrl = (str_starts_with($faviconCustom, '/') || preg_match('#^https?://#i', $faviconCustom))
        ? $faviconCustom
        : upload_url($faviconCustom);
}
$faviconPng    = asset('img/favicon-icon.png') . _imm_cacheBust('/assets/img/favicon-icon.png');
$faviconSvg    = $faviconCustomUrl !== '' ? e($faviconCustomUrl) : (asset('img/favicon.svg') . _imm_cacheBust('/assets/img/favicon.svg'));

$titleAnimEnabled = ($bodyClass ?? '') !== 'amw-body'
  && (string)app_setting('site_title_animation_enabled', '1') === '1';
$titleAnimText    = trim((string)app_setting('site_title_animated', 'AMIGOS POR EL MUNDO · Solidaridad, educación y medio ambiente'));
if ($titleAnimText === '') { $titleAnimText = 'AMIGOS POR EL MUNDO · Solidaridad, educación y medio ambiente'; }

$uploadsBase  = rtrim(url('/uploads'), '/');
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="<?php echo e($siteHeaderBg) ?>">
<meta name="color-scheme" content="light">
<meta name="Sec-CH-Prefers-Color-Scheme" content="light">
<title><?php echo e($metaTitle) ?></title>
<meta name="description" content="<?php echo e($metaDesc) ?>">
<?php if ($faviconCustomUrl !== ''): ?>
<link rel="icon" type="image/png" sizes="any" href="<?php echo e($faviconCustomUrl) ?>">
<?php else: ?>
<link rel="icon" type="image/png" sizes="any" href="<?php echo $faviconPng ?>">
<?php endif; ?>
<link rel="alternate icon" type="image/svg+xml" href="<?php echo $faviconSvg ?>">
<script>
(function(){
  var _origTitle = document.title;
  var _enabled   = <?php echo $titleAnimEnabled ? 'true' : 'false' ?>;
  var _text      = <?php echo json_encode($titleAnimText, JSON_UNESCAPED_UNICODE) ?>;
  var _iv        = null;
  var _buf       = '';
  function _start(){
    if (!_enabled) return;
    if (typeof window.matchMedia === 'function' && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    _buf = _text + '   ' + _text + '   ';
    var i = 0;
    if (_iv) clearInterval(_iv);
    _iv = setInterval(function(){
      var slice = _buf.substring(i, i + 70);
      document.title = (slice.length < 70 ? slice + ' · ' : slice) + ' | AMIGOS POR EL MUNDO';
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
<link rel="preconnect" href="<?php echo $uploadsBase ?>" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="<?php echo $fontRegular ?>" crossorigin fetchpriority="high">
<link rel="preload" as="font" type="font/woff2" href="<?php echo $fontBold ?>" crossorigin fetchpriority="high">
<link rel="preload" as="font" type="font/woff2" href="<?php echo $fontExtraBold ?>" crossorigin>
<link rel="stylesheet" href="<?php echo $cssApp ?>">
<?php if (($bodyClass ?? '') === 'amw-body'): ?>
<link rel="stylesheet" href="<?php echo e(asset('css/amigos.css') . _imm_cacheBust('/assets/css/amigos.css')) ?>">
<?php endif; ?>
<style>
  :root {
    --rm-primary:      <?php echo e($sitePrimary) ?>;
    --rm-secondary:    <?php echo e($siteSecondary) ?>;
    --rm-accent:       <?php echo e($siteAccent) ?>;
    --rm-blue-dark:    <?php echo e($siteBlueDark) ?>;
    --rm-green-dark:   <?php echo e($siteGreenDark) ?>;
    --rm-header-bg:    <?php echo e($siteHeaderBg) ?>;
    --rm-header-text:  <?php echo e($siteHeaderText) ?>;
    --rm-breaking-bg:  <?php echo e($siteBreakingBg) ?>;
    --rm-breaking-text:<?php echo e($siteBreakingText) ?>;
    --rm-footer-bg:    <?php echo e($siteFooterBg) ?>;
    --rm-footer-text:  <?php echo e($siteFooterText) ?>;
  }
  :focus-visible {
    outline: 3px solid #2563EB;
    outline-offset: 2px;
    border-radius: 4px;
  }
  :focus {
    outline: 3px solid #2563EB;
    outline-offset: 2px;
    border-radius: 4px;
  }
  @supports not selector(:focus-visible) {
    :focus {
      outline: 3px solid #2563EB !important;
      outline-offset: 2px;
    }
  }
  .visually-hidden {
    position: absolute;
    width: 1px; height: 1px;
    padding: 0; margin: -1px;
    overflow: hidden;
    clip: rect(0,0,0,0);
    white-space: nowrap;
    border: 0;
  }
  .skip-link:focus {
    position: fixed;
    top: 8px; left: 8px;
    z-index: 99999;
    width: auto; height: auto;
    clip: auto;
    background: var(--rm-primary, #0052A5);
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 700;
    box-shadow: 0 8px 24px rgba(0,0,0,.3);
  }
  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
      animation-duration: 0.001ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: 0.001ms !important;
      scroll-behavior: auto !important;
    }
  }
</style>
<?php echo csrf_token_header_meta() ?>
<script defer src="<?php echo $jsApp ?>"></script>
</head>
<body class="<?php echo e($bodyClass ?? 'rm-body') ?>">
