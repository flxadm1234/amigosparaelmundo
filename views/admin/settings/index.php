﻿<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<?php
$get = function(string $k, string $def = '') use ($settings): string {
    if (is_array($settings)) {
        if (isset($settings[$k]) && is_string($settings[$k])) {
            return $settings[$k];
        }
        foreach ($settings as $s) {
            if (is_array($s)) {
                $name = $s['key_name'] ?? $s['key'] ?? $s['setting_key'] ?? null;
                if ($name === $k) {
                    return (string)($s['value_text'] ?? $s['value'] ?? '');
                }
            }
        }
    }
    return $def;
};

$logo    = $get('site_logo', '');
$favicon = $get('site_favicon', asset('img/favicon.svg'));
$heroLogo = $get('site_logo_hero_home', '');
$adminLogo = $get('site_logo_dark_bg', '');
$logoUrl    = (str_starts_with($logo, '/') || preg_match('#^https?://#i', $logo))    ? $logo    : upload_url($logo);
$faviconUrl = (str_starts_with($favicon, '/') || preg_match('#^https?://#i', $favicon)) ? $favicon : upload_url($favicon);
$heroLogoUrl = $heroLogo === '' ? '' : ((str_starts_with($heroLogo, '/') || preg_match('#^https?://#i', $heroLogo)) ? $heroLogo : upload_url($heroLogo));
$adminLogoUrl = $adminLogo === '' ? '' : ((str_starts_with($adminLogo, '/') || preg_match('#^https?://#i', $adminLogo)) ? $adminLogo : upload_url($adminLogo));
?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1">⚙️ Identidad y contenido del sitio</h1>
      <p class="muted mb-0">Administra la marca, portada, programas, navegación, contacto y apariencia de la asociación.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-outline-secondary" href="<?= url('/') ?>" target="_blank">🌐 Previsualizar sitio</a>
    </div>
  </div>
</div>

<?php
$errors = method_exists('Session', 'flash') ? \Session::flash('errors') : null;
if (!empty($errors) && is_array($errors)): ?>
  <div class="alert alert-danger mb-3">
    <strong>⚠️ Errores de validación:</strong>
    <ul class="mb-0 mt-2">
      <?php foreach ($errors as $e): ?>
        <li><?= e(is_array($e) ? implode(', ', $e) : $e) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" action="<?= url('/admin/ajustes/guardar') ?>" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>

  <div class="card mb-3 settings-accordion">
    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#sec-identidad" style="cursor:pointer;">
      <h3 class="card-title-sm mb-0 d-flex align-items-center gap-2">
        🏷️ Identidad de la asociación
        <span class="muted-sm ms-auto">site_name, slogan, logo, favicon</span>
      </h3>
    </div>
    <div id="sec-identidad" class="collapse show">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="form-label">Nombre del sitio *</label>
              <input type="text" name="site_name" required maxlength="120" class="form-control form-control-lg"
                     value="<?= e($get('site_name', 'ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO')) ?>">
            </div>
          </div>
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="form-label">Eslogan / Slogan</label>
              <input type="text" name="site_slogan" maxlength="200" class="form-control"
                     value="<?= e($get('site_slogan', 'Juntos podemos transformar vidas y proteger nuestro planeta')) ?>">
            </div>
          </div>
          <div class="col-12">
            <div class="form-group">
              <label class="form-label">Descripción corta del sitio</label>
              <textarea name="site_description" rows="2" maxlength="500" class="form-control"
                        placeholder="Presentación institucional de la Asociación Civil Amigos por el Mundo"><?= e($get('site_description', 'Promovemos solidaridad, educación y cuidado del medio ambiente para construir un mundo mejor.')) ?></textarea>
            </div>
          </div>
          <div class="col-md-6 col-12">
            <label class="form-label">Logo del sitio (URL o subir archivo)</label>
            <?php if (!empty($logoUrl)): ?>
              <div class="mb-2 p-3 rounded border d-inline-block" style="background:#fff;min-width:180px;">
                <img src="<?= e(img_fallback($logo, '1x1', 'Logo de la asociación', 'site-logo')) ?>" alt="Vista previa del logo" style="max-height:56px;display:block;">
              </div>
            <?php endif; ?>
            <input type="text" name="site_logo" class="form-control mb-2" value="<?= e($logo) ?>" placeholder="/img/logo.svg o URL…">
            <input type="file" name="site_logo_file" accept=".png,.jpg,.jpeg,.webp" class="form-control form-control-sm">
          </div>
          <div class="col-md-6 col-12">
            <label class="form-label">Favicon (URL o subir)</label>
            <?php if (!empty($faviconUrl)): ?>
              <div class="mb-2 p-3 rounded border d-inline-block" style="background:#fff;">
                <img src="<?= e(img_fallback($favicon, '1x1', 'Icono de la asociación', 'site-favicon')) ?>" alt="Vista previa del favicon" style="height:40px;">
              </div>
            <?php endif; ?>
            <input type="text" name="site_favicon" class="form-control mb-2" value="<?= e($favicon) ?>" placeholder="/img/favicon.svg">
            <input type="file" name="site_favicon_file" accept=".svg,.png,.ico" class="form-control form-control-sm">
          </div>
          <div class="col-md-6 col-12">
            <label class="form-label">RUC de la organización</label>
            <input type="text" name="site_ruc" maxlength="11" class="form-control"
              value="<?= e($get('site_ruc', '20605514635')) ?>" placeholder="Ej. 20605514635">
            <div class="form-help">RUC de la asociación civil.</div>
          </div>
          <div class="col-md-6 col-12">
            <label class="form-label">Registro SUNARP</label>
            <input type="text" name="site_sunarp" class="form-control"
              value="<?= e($get('site_sunarp', '11120073')) ?>" placeholder="Ej. 11120073">
            <div class="form-help">Número de registro en SUNARP.</div>
          </div>
          <div class="col-md-6 col-12">
            <label class="form-label">Emblema del banner principal</label>
            <?php if ($heroLogoUrl !== ''): ?>
              <div class="mb-2 p-2 rounded border d-inline-block" style="background:#fff;">
                <img src="<?= e($heroLogoUrl) ?>" alt="Emblema actual del banner" style="width:72px;height:72px;object-fit:contain;">
              </div>
            <?php endif; ?>
            <input type="file" name="hero_logo_file" accept=".png,.jpg,.jpeg,.webp" class="form-control form-control-sm mb-2">
            <div class="input-group input-group-sm">
              <span class="input-group-text">Tamaño</span>
              <input type="number" name="hero_logo_size" min="80" max="600" step="10" class="form-control"
                     value="<?= e($get('hero_logo_size', '320')) ?>" aria-label="Tamaño del emblema del banner">
              <span class="input-group-text">px</span>
            </div>
            <div class="form-help">Opcional. Si se sube, reemplaza la ilustración del banner por este logo. Ajusta el tamaño para que se vea bien.</div>
          </div>
          <div class="col-md-6 col-12">
            <label class="form-label">Logo para el panel administrativo</label>
            <?php if ($adminLogoUrl !== ''): ?>
              <div class="mb-2 p-2 rounded border d-inline-block" style="background:#17352a;">
                <img src="<?= e($adminLogoUrl) ?>" alt="Logo actual del panel" style="width:120px;height:56px;object-fit:contain;">
              </div>
            <?php endif; ?>
            <input type="file" name="logo_dark_bg_file" accept=".png,.jpg,.jpeg,.webp" class="form-control form-control-sm">
          </div>
          <div class="col-12 mt-3 pt-3" style="border-top:1px dashed var(--border);">
            <div class="row g-3 align-items-center">
              <div class="col-md-4 col-sm-6 col-12">
                <label class="form-label d-flex align-items-center gap-2">
                  <input type="hidden" name="site_title_animation_enabled" value="0">
                  <input type="checkbox" name="site_title_animation_enabled" value="1"
                    <?php if ($get('site_title_animation_enabled', '0') === '1'): ?>checked<?php endif; ?>
                    class="form-check-input" style="width:1.2rem;height:1.2rem;">
                  Animar título de la pestaña
                </label>
                <div class="muted-sm mt-1" style="font-size:.8rem;line-height:1.3;">
                  Activa el efecto marquesina (texto en movimiento) en la pestaña del navegador.
                </div>
              </div>
              <div class="col-md-8 col-sm-6 col-12">
                <label class="form-label">Texto animado del title 🔴</label>
                <input type="text" name="site_title_animated" maxlength="160" class="form-control"
                  value="<?= e($get('site_title_animated', 'ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO')) ?>"
                  placeholder="Texto animado en la pestaña del navegador">
                <div class="muted-sm mt-1" style="font-size:.8rem;">
                  Este texto se muestra con efecto de desplazamiento en la pestaña. Se reinicia cuando el usuario abre la ventana.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3 settings-accordion">
    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#sec-portada-asociacion" style="cursor:pointer;">
      <h3 class="card-title-sm mb-0 d-flex align-items-center gap-2">
        🌿 Portada de Amigos por el Mundo
        <span class="muted-sm ms-auto">Hero, navegación, programas, misión, visión y voluntariado</span>
      </h3>
    </div>
    <div id="sec-portada-asociacion" class="collapse show">
      <div class="card-body">
        <h5 class="subtitle-sm">Cabecera y logotipo</h5>
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label">Texto superior de marca</label>
            <input type="text" name="site_brand_kicker" class="form-control" maxlength="90"
                   value="<?= e($get('site_brand_kicker', 'ASOCIACIÓN CIVIL')) ?>"
                   placeholder="Ej: ASOCIACIÓN CIVIL">
          </div>
          <div class="col-md-8">
            <div class="callout muted-sm h-100 d-flex align-items-center">
              El logotipo principal se cambia en <strong class="mx-1">Identidad de la asociación</strong>, arriba. También aparece en la portada y el pie de página.
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <label class="form-label">Tamaño del texto superior</label>
            <div class="input-group">
              <input type="number" name="header_logo_kicker_size" min="8" max="40" step="1" class="form-control"
                     value="<?= e($get('header_logo_kicker_size', '12')) ?>">
              <span class="input-group-text">px</span>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <label class="form-label">Tamaño del nombre</label>
            <div class="input-group">
              <input type="number" name="header_logo_name_size" min="12" max="64" step="1" class="form-control"
                     value="<?= e($get('header_logo_name_size', '24')) ?>">
              <span class="input-group-text">px</span>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <label class="form-label">Tamaño del eslogan</label>
            <div class="input-group">
              <input type="number" name="header_logo_slogan_size" min="8" max="40" step="1" class="form-control"
                     value="<?= e($get('header_logo_slogan_size', '14')) ?>">
              <span class="input-group-text">px</span>
            </div>
          </div>
          <div class="col-md-3 col-sm-12">
            <div class="callout muted-sm h-100 d-flex align-items-center">
              Ajusta el tamaño de cada línea del logo de la portada. En móvil se reducen proporcionalmente.
            </div>
          </div>
        </div>

        <h5 class="subtitle-sm">Banner principal</h5>
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label">Texto introductorio</label>
            <input type="text" name="home_hero_eyebrow" class="form-control" maxlength="100"
                   value="<?= e($get('home_hero_eyebrow', 'Una comunidad que transforma')) ?>">
          </div>
          <div class="col-md-8">
            <label class="form-label">Título principal</label>
            <input type="text" name="home_hero_title" class="form-control" maxlength="150"
                   value="<?= e($get('home_hero_title', 'Construyendo un')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Palabras destacadas</label>
            <input type="text" name="home_hero_highlight" class="form-control" maxlength="90"
                   value="<?= e($get('home_hero_highlight', 'mundo mejor')) ?>">
          </div>
          <div class="col-md-8">
            <label class="form-label">Texto descriptivo</label>
            <textarea name="home_hero_subtitle" rows="2" class="form-control" maxlength="400"><?= e($get('home_hero_subtitle', 'Con solidaridad, educación y cuidado del medio ambiente. Juntos podemos transformar vidas y proteger nuestro planeta.')) ?></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label">Texto del botón</label>
            <input type="text" name="home_hero_button_text" class="form-control" maxlength="90"
                   value="<?= e($get('home_hero_button_text', 'Conoce nuestros programas')) ?>">
          </div>
          <div class="col-md-8">
            <label class="form-label">Enlace del botón principal</label>
            <input type="text" name="home_hero_button_url" class="form-control"
                   value="<?= e($get('home_hero_button_url', '#programas')) ?>" placeholder="#programas o https://...">
          </div>
          <div class="col-12">
            <label class="form-label">Imagen de fondo del banner</label>
            <input type="file" name="hero_bg_file" accept=".jpg,.jpeg,.png,.webp" class="form-control">
            <?php if ($get('home_hero_bg_image') !== ''): ?>
              <div class="form-help">Imagen actual: <a href="<?= e(upload_url($get('home_hero_bg_image'))) ?>" target="_blank" rel="noopener">ver fondo del banner</a>. Al subir otra imagen se reemplaza.</div>
            <?php else: ?>
              <div class="form-help">Imagen horizontal recomendada: 1800 × 900 px. El sistema usa una fotografía inicial de la Amazonía.</div>
            <?php endif; ?>
          </div>
        </div>

        <h5 class="subtitle-sm">Navegación del sitio</h5>
        <div class="row g-3 mb-4">
          <?php foreach ([
              'home_nav_about_text' => ['Quiénes Somos', 'Quiénes Somos', 'home_nav_about_url', '#nosotros'],
              'home_nav_programs_text' => ['Nuestros Programas', 'Nuestros Programas', 'home_nav_programs_url', '#programas'],
              'home_nav_projects_text' => ['Proyectos', 'Proyectos', 'home_nav_projects_url', '#proyectos'],
              'home_nav_news_text' => ['Noticias', 'Noticias', 'home_nav_news_url', '/noticias'],
              'home_nav_partners_text' => ['Empresas Aliadas', 'Empresas Aliadas', 'home_nav_partners_url', '#aliados'],
              'home_nav_volunteer_text' => ['Voluntariado', 'Voluntariado', 'home_nav_volunteer_url', '#voluntariado'],
              'home_nav_contact_text' => ['Contáctenos', 'Contáctenos', 'home_nav_contact_url', '#contacto'],
          ] as $key => [$label, $default, $urlKey, $defaultUrl]): ?>
            <div class="col-md-6">
              <label class="form-label"><?= e($label) ?> · texto</label>
              <input type="text" name="<?= e($key) ?>" class="form-control" maxlength="80"
                     value="<?= e($get($key, $default)) ?>">
              <label class="form-label mt-2"><?= e($label) ?> · destino</label>
              <?php if ($key === 'home_nav_news_text'): ?>
                <input type="text" class="form-control" value="<?= e(url('/noticias')) ?>" readonly>
                <div class="form-help">Abre el archivo de noticias de este sitio.</div>
              <?php else: ?>
                <input type="text" name="<?= e($urlKey) ?>" class="form-control"
                       value="<?= e($get($urlKey, $defaultUrl)) ?>" placeholder="#seccion o /pagina">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <h5 class="subtitle-sm">Programas de impacto</h5>
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label">Texto superior de sección</label>
            <input type="text" name="home_programs_eyebrow" class="form-control" maxlength="100"
                   value="<?= e($get('home_programs_eyebrow', 'Lo que hacemos')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Título de sección</label>
            <input type="text" name="home_programs_title" class="form-control" maxlength="100"
                   value="<?= e($get('home_programs_title', 'Nuestros Programas')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Subtítulo de sección</label>
            <input type="text" name="home_programs_subtitle" class="form-control" maxlength="180"
                   value="<?= e($get('home_programs_subtitle', 'Acciones que generan un impacto positivo')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Texto de enlace de cada tarjeta</label>
            <input type="text" name="home_program_card_link" class="form-control" maxlength="80"
                   value="<?= e($get('home_program_card_link', 'Conoce el programa')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Destino del enlace de cada tarjeta</label>
            <input type="text" name="home_program_card_url" class="form-control"
                   value="<?= e($get('home_program_card_url', '#voluntariado')) ?>" placeholder="#voluntariado o /pagina">
          </div>
          <div class="col-md-6">
            <label class="form-label">Mensaje cuando aún no hay programas activos</label>
            <input type="text" name="home_programs_empty" class="form-control" maxlength="220"
                   value="<?= e($get('home_programs_empty', 'Pronto compartiremos nuevas iniciativas para transformar nuestra comunidad.')) ?>">
          </div>
          <div class="col-12">
            <div class="callout muted-sm">Registra y ordena las tarjetas desde <strong>Contenido → Programas</strong>. Cada tarjeta admite título, descripción, fotografía y estado de publicación.</div>
          </div>
        </div>

        <h5 class="subtitle-sm">Misión, visión y participación</h5>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Etiqueta de misión</label>
            <input type="text" name="home_mission_label" class="form-control" value="<?= e($get('home_mission_label', 'Nuestro propósito')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Título de misión</label>
            <input type="text" name="home_mission_title" class="form-control" value="<?= e($get('home_mission_title', 'Nuestra Misión')) ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Descripción de misión</label>
            <textarea name="home_mission_text" rows="2" class="form-control"><?= e($get('home_mission_text', 'Trabajar por el bienestar social, la educación y la protección del medio ambiente.')) ?></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Etiqueta de visión</label>
            <input type="text" name="home_vision_label" class="form-control" value="<?= e($get('home_vision_label', 'Hacia dónde vamos')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Título de visión</label>
            <input type="text" name="home_vision_title" class="form-control" value="<?= e($get('home_vision_title', 'Nuestra Visión')) ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Descripción de visión</label>
            <textarea name="home_vision_text" rows="2" class="form-control"><?= e($get('home_vision_text', 'Ser una organización reconocida por impulsar comunidades solidarias, saludables y sostenibles.')) ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Frase central</label>
            <input type="text" name="home_quote" class="form-control" maxlength="180"
                   value="<?= e($get('home_quote', 'Juntos podemos hacer la diferencia')) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Texto introductorio de voluntariado</label>
            <input type="text" name="home_join_eyebrow" class="form-control" value="<?= e($get('home_join_eyebrow', 'Tu ayuda hace la diferencia')) ?>">
          </div>
          <div class="col-md-8">
            <label class="form-label">Título de voluntariado</label>
            <input type="text" name="home_join_title" class="form-control" value="<?= e($get('home_join_title', 'Súmate al cambio')) ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Descripción de voluntariado</label>
            <textarea name="home_join_text" rows="2" class="form-control"><?= e($get('home_join_text', 'Cada gesto cuenta. Participa como voluntario, apoya nuestros proyectos o comparte nuestra causa.')) ?></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label">Texto del botón de apoyo</label>
            <input type="text" name="home_join_button_text" class="form-control" value="<?= e($get('home_join_button_text', 'Quiero colaborar')) ?>">
          </div>
          <div class="col-md-8">
            <label class="form-label">Enlace del botón de apoyo</label>
            <input type="text" name="home_join_button_url" class="form-control" value="<?= e($get('home_join_button_url', '#contacto')) ?>">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3 settings-accordion">
    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#sec-colores" style="cursor:pointer;">
      <h3 class="card-title-sm mb-0 d-flex align-items-center gap-2">
        🎨 Colores del tema
        <span class="muted-sm ms-auto">CSS variables del sitio público</span>
      </h3>
    </div>
    <div id="sec-colores" class="collapse show">
      <div class="card-body">
        <div class="row g-3">

          <div class="col-md-4 col-sm-6 col-12">
            <label class="form-label">Color Primario (site_primary_color)</label>
            <div class="input-group">
              <input type="color" name="site_primary_color" class="form-control form-control-color"
                     value="<?= e($get('site_primary_color', '#087B43')) ?>" style="height:38px;">
              <input type="text" name="site_primary_color_text" class="form-control mono"
                     value="<?= e($get('site_primary_color', '#087B43')) ?>">
            </div>
          </div>

          <div class="col-md-4 col-sm-6 col-12">
            <label class="form-label">Color Secundario (site_secondary_color)</label>
            <div class="input-group">
              <input type="color" name="site_secondary_color" class="form-control form-control-color"
                     value="<?= e($get('site_secondary_color', '#85C440')) ?>" style="height:38px;">
              <input type="text" name="site_secondary_color_text" class="form-control mono"
                     value="<?= e($get('site_secondary_color', '#85C440')) ?>">
            </div>
          </div>

          <div class="col-md-4 col-sm-6 col-12">
            <label class="form-label">Color Acento / CTA (site_accent_color)</label>
            <div class="input-group">
              <input type="color" name="site_accent_color" class="form-control form-control-color"
                     value="<?= e($get('site_accent_color', '#F3BF24')) ?>" style="height:38px;">
              <input type="text" name="site_accent_color_text" class="form-control mono"
                     value="<?= e($get('site_accent_color', '#F3BF24')) ?>">
            </div>
          </div>

          <div class="col-md-4 col-sm-6 col-12">
            <label class="form-label">Azul Oscuro (site_blue_dark)</label>
            <div class="input-group">
              <input type="color" name="site_blue_dark" class="form-control form-control-color"
                     value="<?= e($get('site_blue_dark', '#123B2B')) ?>" style="height:38px;">
              <input type="text" name="site_blue_dark_text" class="form-control mono"
                     value="<?= e($get('site_blue_dark', '#123B2B')) ?>">
            </div>
          </div>

          <div class="col-md-4 col-sm-6 col-12">
            <label class="form-label">Verde Oscuro (site_green_dark)</label>
            <div class="input-group">
              <input type="color" name="site_green_dark" class="form-control form-control-color"
                     value="<?= e($get('site_green_dark', '#075B38')) ?>" style="height:38px;">
              <input type="text" name="site_green_dark_text" class="form-control mono"
                     value="<?= e($get('site_green_dark', '#075B38')) ?>">
            </div>
          </div>

          <div class="col-md-4 col-sm-6 col-12">
            <label class="form-label">Fondo Header (site_header_bg)</label>
            <div class="input-group">
              <input type="color" name="site_header_bg" class="form-control form-control-color"
                     value="<?= e($get('site_header_bg', '#087B43')) ?>" style="height:38px;">
              <input type="text" name="site_header_bg_text" class="form-control mono"
                     value="<?= e($get('site_header_bg', '#087B43')) ?>">
            </div>
          </div>

          <div class="col-md-4 col-sm-6 col-12">
            <label class="form-label">Texto Header (site_header_text)</label>
            <div class="input-group">
              <input type="color" name="site_header_text" class="form-control form-control-color"
                     value="<?= e($get('site_header_text', '#FFFFFF')) ?>" style="height:38px;">
              <input type="text" name="site_header_text_text" class="form-control mono"
                     value="<?= e($get('site_header_text', '#FFFFFF')) ?>">
            </div>
          </div>

          <div class="col-md-4 col-sm-6 col-12">
            <label class="form-label">Fondo Última Hora (site_breaking_bg)</label>
            <div class="input-group">
              <input type="color" name="site_breaking_bg" class="form-control form-control-color"
                     value="<?= e($get('site_breaking_bg', '#E11D48')) ?>" style="height:38px;">
              <input type="text" name="site_breaking_bg_text" class="form-control mono"
                     value="<?= e($get('site_breaking_bg', '#E11D48')) ?>">
            </div>
          </div>

          <div class="col-md-4 col-sm-6 col-12">
            <label class="form-label">Texto Última Hora (site_breaking_text)</label>
            <div class="input-group">
              <input type="color" name="site_breaking_text" class="form-control form-control-color"
                     value="<?= e($get('site_breaking_text', '#FFFFFF')) ?>" style="height:38px;">
              <input type="text" name="site_breaking_text_text" class="form-control mono"
                     value="<?= e($get('site_breaking_text', '#FFFFFF')) ?>">
            </div>
          </div>

          <div class="col-md-4 col-sm-6 col-12">
            <label class="form-label">Fondo Footer (site_footer_bg)</label>
            <div class="input-group">
              <input type="color" name="site_footer_bg" class="form-control form-control-color"
                     value="<?= e($get('site_footer_bg', '#092C21')) ?>" style="height:38px;">
              <input type="text" name="site_footer_bg_text" class="form-control mono"
                     value="<?= e($get('site_footer_bg', '#092C21')) ?>">
            </div>
          </div>

          <div class="col-md-4 col-sm-6 col-12">
            <label class="form-label">Texto Footer (site_footer_text)</label>
            <div class="input-group">
              <input type="color" name="site_footer_text" class="form-control form-control-color"
                     value="<?= e($get('site_footer_text', '#FFFFFF')) ?>" style="height:38px;">
              <input type="text" name="site_footer_text_text" class="form-control mono"
                     value="<?= e($get('site_footer_text', '#FFFFFF')) ?>">
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3 settings-accordion">
    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#sec-redes" style="cursor:pointer;">
      <h3 class="card-title-sm mb-0 d-flex align-items-center gap-2">
        🔗 Redes Sociales
        <span class="muted-sm ms-auto">Enlaces completos con https://</span>
      </h3>
    </div>
    <div id="sec-redes" class="collapse show">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="form-label">📘 Facebook</label>
              <input type="url" name="social_facebook" class="form-control"
                     value="<?= e($get('social_facebook', '')) ?>"
                     placeholder="https://facebook.com/amigosporelmundo">
            </div>
          </div>
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="form-label">🐦 Twitter / X</label>
              <input type="url" name="social_twitter" class="form-control"
                     value="<?= e($get('social_twitter', '')) ?>"
                     placeholder="https://twitter.com/amigosporelmundo">
            </div>
          </div>
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="form-label">📸 Instagram</label>
              <input type="url" name="social_instagram" class="form-control"
                     value="<?= e($get('social_instagram', '')) ?>"
                     placeholder="https://instagram.com/amigosporelmundo">
            </div>
          </div>
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="form-label">▶️ YouTube</label>
              <input type="url" name="social_youtube" class="form-control"
                     value="<?= e($get('social_youtube', '')) ?>"
                     placeholder="https://youtube.com/@amigosporelmundo">
            </div>
          </div>
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="form-label">🎵 TikTok</label>
              <input type="url" name="social_tiktok" class="form-control"
                     value="<?= e($get('social_tiktok', '')) ?>"
                     placeholder="https://tiktok.com/@amigosporelmundo">
            </div>
          </div>
          <div class="col-md-6 col-12">
            <div class="form-group mb-0">
              <label class="form-label">💬 WhatsApp (con código país, sin + ni espacios)</label>
              <input type="text" name="social_whatsapp" class="form-control mono"
                     value="<?= e($get('social_whatsapp', '')) ?>"
                     placeholder="51987654321 (se convierte a wa.me link)">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3 settings-accordion">
    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#sec-seo" style="cursor:pointer;">
      <h3 class="card-title-sm mb-0 d-flex align-items-center gap-2">
        🔍 SEO (Meta tags por defecto)
        <span class="muted-sm ms-auto">Home title, description, keywords</span>
      </h3>
    </div>
    <div id="sec-seo" class="collapse show">
      <div class="card-body">
        <div class="form-group mb-3">
          <label class="form-label">Título SEO del Home (seo_home_title)</label>
          <input type="text" name="seo_home_title" class="form-control" maxlength="120"
                 value="<?= e($get('seo_home_title', 'Amigos por el Mundo | Solidaridad, Educación y Medio Ambiente')) ?>"
                 placeholder="50-60 caracteres recomendado">
        </div>
        <div class="form-group mb-3">
          <label class="form-label">Descripción SEO por defecto (seo_home_desc)</label>
          <textarea name="seo_home_desc" rows="3" maxlength="320" class="form-control"
                    placeholder="150-160 caracteres recomendado. Aparece en Google y redes sociales."><?= e($get('seo_home_desc', '')) ?></textarea>
        </div>
        <div class="form-group mb-0">
          <label class="form-label">Palabras clave SEO (seo_default_keywords, separadas por coma)</label>
          <textarea name="seo_default_keywords" rows="2" class="form-control mono"
                    placeholder="noticias, amigos por el mundo, radio, actualidad, deportes, politica"><?= e($get('seo_default_keywords', '')) ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3 settings-accordion">
    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#sec-footer" style="cursor:pointer;">
      <h3 class="card-title-sm mb-0 d-flex align-items-center gap-2">
        📞 Footer del sitio
        <span class="muted-sm ms-auto">Sobre nosotros, dirección, teléfono, email, copyright</span>
      </h3>
    </div>
    <div id="sec-footer" class="collapse show">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label">📝 Texto "Sobre Amigos por el Mundo" (footer_about)</label>
              <textarea name="footer_about" rows="3" maxlength="600" class="form-control"
                        placeholder="Breve descripción de la empresa/marca que aparece en el footer."><?= e($get('footer_about', '')) ?></textarea>
            </div>
          </div>
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="form-label">📍 Dirección (footer_address)</label>
              <input type="text" name="footer_address" class="form-control" maxlength="250"
                     value="<?= e($get('footer_address', 'Calle Arequipa 1340 IQUITOS')) ?>"
                     placeholder="Av. Principal 123, Ciudad, País">
            </div>
          </div>
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="form-label">🏢 Oficina enlace (site_office_address)</label>
              <input type="text" name="site_office_address" class="form-control" maxlength="250"
                     value="<?= e($get('site_office_address', 'Arica 646 IQUITOS')) ?>"
                     placeholder="Arica 646, Iquitos">
            </div>
          </div>
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="form-label">📞 Contáctenos / Teléfono (footer_phone)</label>
              <input type="text" name="footer_phone" class="form-control" maxlength="120"
                     value="<?= e($get('footer_phone', '959669866 / 968192039')) ?>"
                     placeholder="959669866 / 968192039">
            </div>
          </div>
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="form-label">✉️ Email (footer_email)</label>
              <input type="email" name="footer_email" class="form-control"
                     value="<?= e($get('footer_email', '')) ?>"
                     placeholder="contacto@amigosporelmundo.org">
            </div>
          </div>
          <div class="col-md-6 col-12">
            <div class="form-group mb-0">
              <label class="form-label">© Copyright (copyright_text)</label>
              <input type="text" name="copyright_text" class="form-control" maxlength="250"
                     value="<?= e($get('copyright_text', '© '.date('Y').' Amigos por el Mundo. Todos los derechos reservados.')) ?>">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3 settings-accordion">
    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#sec-cantidades" style="cursor:pointer;">
      <h3 class="card-title-sm mb-0 d-flex align-items-center gap-2">
        📊 Cantidades y paginación
        <span class="muted-sm ms-auto">Items por vista en el sitio público</span>
      </h3>
    </div>
    <div id="sec-cantidades" class="collapse show">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6 col-lg-3 col-12">
            <div class="form-group">
              <label class="form-label">Hero Home - Nº noticias destacadas (home_hero_count)</label>
              <input type="number" name="home_hero_count" min="1" max="20" class="form-control"
                     value="<?= e($get('home_hero_count', '5')) ?>">
            </div>
          </div>
          <div class="col-md-6 col-lg-3 col-12">
            <div class="form-group">
              <label class="form-label">Home - "Más noticias" (home_mas_noticias_count)</label>
              <input type="number" name="home_mas_noticias_count" min="1" max="60" class="form-control"
                     value="<?= e($get('home_mas_noticias_count', '12')) ?>">
            </div>
          </div>
          <div class="col-md-6 col-lg-3 col-12">
            <div class="form-group mb-0">
              <label class="form-label">Listado noticias - Paginación (news_per_page)</label>
              <input type="number" name="news_per_page" min="5" max="100" class="form-control"
                     value="<?= e($get('news_per_page', '12')) ?>">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card sticky-bottom-card sticky-bottom">
    <div class="card-body d-flex gap-2 flex-wrap justify-end">
      <a class="btn btn-secondary" href="<?= url('/admin/dashboard') ?>">↩️ Cancelar</a>
      <button type="reset" class="btn btn-outline-secondary">♻️ Restaurar</button>
      <button type="submit" name="action" value="save" class="btn btn-primary btn-lg">✅ Guardar cambios</button>
    </div>
  </div>
</form>

<script>
/* ============================================================================
   Amigos por el Mundo — admin/settings: sync bidireccional color picker ↔ hex text input
   + validación hex + submit coherente (V55F Colores del tema robusto)
   ============================================================================ */
(function () {
  'use strict';

  var colorKeys = [
    'site_primary_color','site_secondary_color','site_accent_color',
    'site_blue_dark','site_green_dark',
    'site_header_bg','site_header_text',
    'site_breaking_bg','site_breaking_text',
    'site_footer_bg','site_footer_text'
  ];

  var form = document.querySelector('form[action$="/admin/ajustes/guardar"]') || document.querySelector('form.enctype[multipart]') || document.forms[0];

  function normHex(s) {
    s = (s == null ? '' : String(s)).trim().toUpperCase();
    if (!s) return '';
    if (s.charAt(0) !== '#') s = '#' + s;
    // Expansión corta #ABC → #AABBCC
    if (/^#[0-9A-F]{3}$/.test(s)) {
      s = '#' + s.charAt(1) + s.charAt(1) + s.charAt(2) + s.charAt(2) + s.charAt(3) + s.charAt(3);
    }
    if (!/^#[0-9A-F]{6}$/.test(s)) return '';
    return s;
  }

  colorKeys.forEach(function (key) {
    var colorIn = document.querySelector('input[type="color"][name="' + key + '"]');
    var textIn  = document.querySelector('input[type="text"][name="' + key + '_text"]');
    if (!colorIn || !textIn) return;

    // Normalizar valores iniciales
    var init = normHex(colorIn.value) || normHex(textIn.value) || '#087B43';
    colorIn.value = init;
    textIn.value = init;
    textIn.setAttribute('pattern', '#[0-9A-Fa-f]{6}|#[0-9A-Fa-f]{3}');
    textIn.setAttribute('placeholder', '#RRGGBB');
    textIn.setAttribute('maxlength', '7');

    colorIn.addEventListener('input', function () {
      var v = normHex(colorIn.value);
      if (v) {
        colorIn.value = v;
        textIn.value = v;
      }
    });
    colorIn.addEventListener('change', function () {
      var v = normHex(colorIn.value);
      if (v) {
        colorIn.value = v;
        textIn.value = v;
      }
    });

    textIn.addEventListener('input', function () {
      var raw = textIn.value;
      var v = normHex(raw);
      if (v) {
        colorIn.value = v;
      }
    });
    textIn.addEventListener('change', function () {
      var v = normHex(textIn.value);
      if (v) {
        colorIn.value = v;
        textIn.value = v;
      } else {
        // Volver al último valor válido que tenga el color picker
        var cv = normHex(colorIn.value);
        if (cv) textIn.value = cv;
      }
    });
  });

  // Al enviar el formulario: asegurarse que el input color (el que se envía realmente por tener el name correcto sin _text)
  // tenga el valor normalizado proveniente del text input si es que el usuario lo editó.
  if (form) {
    form.addEventListener('submit', function () {
      colorKeys.forEach(function (key) {
        var colorIn = document.querySelector('input[type="color"][name="' + key + '"]');
        var textIn  = document.querySelector('input[type="text"][name="' + key + '_text"]');
        if (!colorIn || !textIn) return;
        var v = normHex(textIn.value) || normHex(colorIn.value);
        if (v) {
          colorIn.value = v;
          textIn.value = v;
        }
      });
    }, true);
  }

  // Preview en vivo al cambiar (sin guardar) — actualiza :root vars para visualizar colores sin recargar
  function applyLivePreview() {
    var map = [
      ['site_primary_color','--rm-primary'],
      ['site_secondary_color','--rm-secondary'],
      ['site_accent_color','--rm-accent'],
      ['site_blue_dark','--rm-blue-dark'],
      ['site_green_dark','--rm-green-dark'],
      ['site_header_bg','--rm-header-bg'],
      ['site_header_text','--rm-header-text'],
      ['site_breaking_bg','--rm-breaking-bg'],
      ['site_breaking_text','--rm-breaking-text'],
      ['site_footer_bg','--rm-footer-bg'],
      ['site_footer_text','--rm-footer-text'],
    ];
    var root = document.documentElement;
    map.forEach(function (pair) {
      var colorIn = document.querySelector('input[type="color"][name="' + pair[0] + '"]');
      if (!colorIn) return;
      var v = normHex(colorIn.value);
      if (v) root.style.setProperty(pair[1], v);
    });
  }
  colorKeys.forEach(function (key) {
    var colorIn = document.querySelector('input[type="color"][name="' + key + '"]');
    var textIn  = document.querySelector('input[type="text"][name="' + key + '_text"]');
    if (colorIn) {
      colorIn.addEventListener('input', applyLivePreview);
      colorIn.addEventListener('change', applyLivePreview);
    }
    if (textIn) {
      textIn.addEventListener('input', applyLivePreview);
      textIn.addEventListener('change', applyLivePreview);
    }
  });
  // Aplicar una vez inicial
  try { applyLivePreview(); } catch (_) {}
})();
</script>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
