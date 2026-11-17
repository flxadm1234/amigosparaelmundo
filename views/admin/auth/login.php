<?php
/**
 * Página de Login Admin — Rediseño Profesional IPSOS-style
 * MOD: Cache Busting AUTOMÁTICO + NO-CACHE HEADERS (fix versiones antiguas)
 * Split layout: hero branding + card glass login
 */
$siteName  = app_setting('site_name', 'ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO');
$siteDesc  = app_setting('site_slogan', 'Juntos podemos transformar vidas y proteger nuestro planeta');
$about     = app_setting('footer_about', '');
$heroLogo  = app_setting('site_logo_hero_home', '');
if (!empty($heroLogo) && (str_starts_with($heroLogo, '/') || preg_match('#^https?://#i', $heroLogo))) {
    $heroLogoSafe = $heroLogo;
} elseif (!empty($heroLogo)) {
    $abs = resolve_upload_abs_path($heroLogo);
    $heroLogoSafe = (is_file($abs)) ? upload_url($heroLogo) : '';
} else {
    $heroLogoSafe = trim((string)app_setting('site_logo', ''));
    if ($heroLogoSafe !== '' && !str_starts_with($heroLogoSafe, '/') && !preg_match('#^https?://#i', $heroLogoSafe)) {
        $heroLogoSafe = upload_url($heroLogoSafe);
    }
}

$navy    = app_setting('site_color_navy',       '#075B38');
$teal    = app_setting('site_color_cta_teal',   '#85C440');
$primary = app_setting('site_primary_color',    '#087B43');

// ✅ FIX CACHE 1/2: HEADERS PHP NO-CACHE para login (fuerza actualización)
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}
// ✅ FIX CACHE 2/2: CSS con cache busting AUTOMÁTICO (filemtime hash)
$cssApp   = asset('css/app.css')   . _imm_cacheBust('/assets/css/app.css');
$cssAdmin = asset('css/admin.css') . _imm_cacheBust('/assets/css/admin.css');
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title>Iniciar sesión · Panel | <?= e($siteName) ?></title>
<meta name="theme-color" content="<?= e($navy) ?>">

<!-- ✅ FIX CACHE: META TAGS NO-CACHE (backup) -->
<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Thu, 01 Jan 1970 00:00:00 GMT">

<link rel="icon" type="image/svg+xml" href="<?= e(app_setting('site_favicon', asset('img/favicon.svg'))) ?>">
<link rel="preload" as="font" type="font/woff2" href="<?= asset('fonts/Inter/Inter-Bold.woff2') ?>" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="<?= asset('fonts/Inter/Inter-Regular.woff2') ?>" crossorigin>
<link rel="stylesheet" href="<?= $cssApp ?>">
<link rel="stylesheet" href="<?= $cssAdmin ?>">
<?= csrf_token_header_meta() ?>
</head>
<body class="login-body">

<div class="l-wrap" role="main">

  <!-- ========================= HERO BRANDING (LADO IZQUIERDO) ========================= -->
  <section class="l-hero" aria-label="Panel Administrativo de <?= e($siteName) ?>">
    <div class="l-hero-bg"></div>
    <div class="l-hero-bg-pattern" aria-hidden="true"></div>
    <div class="l-hero-inner">
      <a href="<?= url('/') ?>" class="l-hero-logo-link" aria-label="<?= e($siteName) ?> · Volver al inicio">
        <?php if ($heroLogoSafe !== ''): ?>
          <img src="<?= e($heroLogoSafe) ?>" alt="<?= e($siteName) ?> logo" class="l-hero-logo" loading="eager">
        <?php else: ?>
          <span class="l-hero-logo" style="display:grid;place-items:center;border-radius:18px;background:#0b7b43;color:#fff;font-size:32px;font-weight:800;" aria-hidden="true">AM</span>
        <?php endif; ?>
      </a>

      <div class="l-hero-copy">
        <div class="l-hero-badge">
          <span class="dot-pulse"></span>
          Panel Administrativo
        </div>

        <h1 class="l-hero-h1" >
          Bienvenido al <strong>Centro de Operaciones</strong>
        </h1>
        <p class="l-hero-p" >
          <?= e($siteDesc) ?> · Administra programas, proyectos, contenidos, identidad visual y la información de contacto.
        </p>

        <ul class="l-hero-bullets">
          <li>
            <span class="bh-ic">📊</span>
            <div>
              <strong >Panel en tiempo real</strong>
              <span >Estadísticas, auditorías y publicaciones en un solo lugar.</span>
            </div>
          </li>
          <li>
            <span class="bh-ic">🔒</span>
            <div>
              <strong >Roles &amp; permisos</strong>
              <span >Administradores y editores con acceso controlado.</span>
            </div>
          </li>
          <li>
            <span class="bh-ic">📢</span>
            <div>
              <strong >Publicidad administrable</strong>
              <span >Crea banners programados y mide su rendimiento con CTR.</span>
            </div>
          </li>
        </ul>
      </div>

      <div class="l-hero-foot">
        <span>© <?= date('Y') ?> <?= e($siteName) ?></span>
        <span>Hecho con ❤️ en Perú</span>
      </div>
    </div>
  </section>

  <!-- ========================= CARD LOGIN LADO DERECHO ========================= -->
  <section class="l-card-wrap" aria-label="Formulario de acceso">
    <div class="l-card">

      <div class="l-card-head">
        <h2 class="l-card-title">Iniciar sesión</h2>
        <p class="l-card-sub">Ingresa tus credenciales para continuar.</p>
      </div>

      <?php include __DIR__ . '/../../partials/flash.php'; ?>

      <form method="post" action="<?= url('/admin/login') ?>" class="l-form" novalidate id="loginForm">
        <?= csrf_field() ?>

        <div class="lf-group">
          <label for="f_email" class="lf-label">
            <span class="lf-label-ic">✉️</span> Correo electrónico
          </label>
          <div class="lf-input">
            <input type="email" id="f_email" name="email" required
                   autocomplete="username" inputmode="email"
                   value="<?= e(old('email')) ?>"
                   placeholder="correo@amigos.org">
          </div>
        </div>

        <div class="lf-group">
          <div class="lf-label-row">
            <label for="f_password" class="lf-label">
              <span class="lf-label-ic">🔒</span> Contraseña
            </label>
            <a href="#" class="lf-forgot"
               onclick="alert('Por favor contacta al administrador para restablecer tu contraseña.');return false;"
               title="Restablecer contraseña">¿Olvidaste tu contraseña?</a>
          </div>
          <div class="lf-input lf-input-pass">
            <input type="password" id="f_password" name="password" required
                   autocomplete="current-password" minlength="4"
                   placeholder="••••••••">
            <button type="button" class="lf-eye" aria-label="Mostrar / ocultar contraseña" id="btnTogglePass">
              <span class="eye-open">👁️</span>
              <span class="eye-closed" hidden>🙈</span>
            </button>
          </div>
        </div>

        <div class="lf-row-between">
          <label class="lf-remember">
            <input type="checkbox" name="remember" id="f_remember" value="1">
            <span>Mantener sesión iniciada</span>
          </label>
        </div>

        <button type="submit" class="lf-submit" id="btnLoginSubmit">
          <span class="s-ic">🔑</span>
          <span class="s-label">Ingresar al panel</span>
          <span class="s-spinner" aria-hidden="true"></span>
        </button>

        <div class="lf-bottom-links">
          <a href="<?= url('/') ?>" class="lf-back" title="Volver al sitio público">
            <span>←</span> Volver al sitio web
          </a>
          <span class="sep">·</span>
          <a href="<?= url('/contacto') ?>" class="lf-support" title="Contacto de soporte">💬 Soporte</a>
        </div>
      </form>
    </div>

    <div class="l-card-foot muted-sm">
      © <?= date('Y') ?> <?= e($siteName) ?> · Todos los derechos reservados.
    </div>
  </section>

</div>

<script>
/* ======= Eye toggle contraseña ======= */
(function(){
  var btn = document.getElementById('btnTogglePass');
  var inp = document.getElementById('f_password');
  var eyeO = btn && btn.querySelector('.eye-open');
  var eyeC = btn && btn.querySelector('.eye-closed');
  if (btn && inp) {
    btn.addEventListener('click', function(){
      if (inp.type === 'password') { inp.type = 'text'; if (eyeO) eyeO.hidden = true; if (eyeC) eyeC.hidden = false; }
      else { inp.type = 'password'; if (eyeO) eyeO.hidden = false; if (eyeC) eyeC.hidden = true; }
    });
  }
})();

/* ======= Animación de submit (spinner + texto loading) ======= */
(function(){
  var form = document.getElementById('loginForm');
  var btn  = document.getElementById('btnLoginSubmit');
  var label= btn && btn.querySelector('.s-label');
  var sp   = btn && btn.querySelector('.s-spinner');
  if (form && btn) {
    form.addEventListener('submit', function(){
      if (!form.checkValidity()) return;
      btn.classList.add('is-loading');
      btn.disabled = true;
      if (label) label.textContent = 'Accediendo…';
      if (sp) sp.removeAttribute('hidden');
    });
  }
})();

/* ======= Prefill demo (solo si campos están vacíos) ======= */
(function(){
  try {
    var e = document.getElementById('f_email');
    var p = document.getElementById('f_password');
    if (e && p && e.value === '' && p.value === '' && location.search.indexOf('demo=1') !== -1) {
      e.value = 'admin@amigosporelmundo.org';
      p.value = 'admin123';
    }
  } catch(e) {}
})();
</script>

</body>
</html>
