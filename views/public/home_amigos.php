<?php include __DIR__ . '/../partials/public_head.php'; ?>
<?php
$setting = static fn(string $key, string $default = ''): string => app_setting($key, $default);
$siteLink = static function (string $value, string $default = '#'): string {
    $value = trim($value);
    if ($value === '' || str_starts_with($value, '//')) { return $default; }
    return preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $value) ? $value : $default;
};
$siteName = $setting('site_name', 'ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO');
$siteLogo = trim($setting('site_logo', ''));
$logoUrl = $siteLogo === '' ? '' : (
    str_starts_with($siteLogo, '/') || preg_match('#^https?://#i', $siteLogo)
        ? $siteLogo
        : upload_url($siteLogo)
);
$heroLogo = trim($setting('site_logo_hero_home', ''));
$heroLogoUrl = $heroLogo === '' ? '' : (
    str_starts_with($heroLogo, '/') || preg_match('#^https?://#i', $heroLogo)
        ? $heroLogo
        : upload_url($heroLogo)
);
$heroBg = trim($setting(
    'home_hero_bg_image',
    '/uploads/site/20260908222819_c33134b7_Gemini_Generated_Image_3j2tor3j2tor3j2t.png'
));
$heroBgUrl = $heroBg === '' ? '' : (
    str_starts_with($heroBg, '/') || preg_match('#^https?://#i', $heroBg)
        ? $heroBg
        : upload_url($heroBg)
);
$navItems = [
    ['home_nav_about_text', 'home_nav_about_url', 'Quiénes Somos', '#nosotros'],
    ['home_nav_programs_text', 'home_nav_programs_url', 'Nuestros Programas', '#programas'],
    ['home_nav_projects_text', 'home_nav_projects_url', 'Proyectos', '#proyectos'],
    ['home_nav_news_text', 'home_nav_news_url', 'Noticias', '/noticias'],
    ['home_nav_videos_text', 'home_nav_videos_url', 'Videos', '/videos'],
    ['home_nav_partners_text', 'home_nav_partners_url', 'Empresas Aliadas', '#aliados'],
    ['home_nav_volunteer_text', 'home_nav_volunteer_url', 'Voluntariado', '#voluntariado'],
    ['home_nav_contact_text', 'home_nav_contact_url', 'Contáctenos', '#contacto'],
];
$programIcons = [
    'leaf' => '<svg viewBox="0 0 48 48"><path d="M39 8C22 8 10 12 8 25c-1 7 4 12 11 11C32 34 37 21 39 8Z"/><path d="M9 40c7-11 15-17 25-23"/></svg>',
    'book' => '<svg viewBox="0 0 48 48"><path d="M8 10h22a7 7 0 0 1 7 7v23H15a7 7 0 0 1-7-7V10Zm0 23a7 7 0 0 1 7-7h22M17 17h12m-12 6h9"/></svg>',
    'hands' => '<svg viewBox="0 0 48 48"><path d="m5 27 8-8 10 5 8-6 12 4-13 15-12-4-6 4-7-10Zm9-10 5-6 9 4 5 5m-22 9 7 8"/></svg>',
    'sun' => '<svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="9"/><path d="M24 4v5m0 30v5M4 24h5m30 0h5M10 10l4 4m20 20 4 4m0-28-4 4M14 34l-4 4"/></svg>',
    'heart' => '<svg viewBox="0 0 48 48"><path d="M24 39S7 29 7 18a9 9 0 0 1 17-4 9 9 0 0 1 17 4c0 11-17 21-17 21Z"/><path d="M15 24h6l3-6 4 12 3-6h3"/></svg>',
    'paw' => '<svg viewBox="0 0 48 48"><path d="M15 26c-4-2-7-7-5-11s6-2 8 2l3 7m12 2c4-2 7-7 5-11s-6-2-8 2l-3 7M19 17c-3-1-5-6-3-9s6-1 7 3l1 5m8 1c3-1 5-6 3-9s-6-1-7 3l-1 5m-15 16c0-6 7-12 12-12s12 6 12 12c0 5-4 6-8 4-3-1-5-1-8 0-4 2-8 1-8-4Z"/></svg>',
];

// Tamaños personalizables del texto del logo (cabecera de la portada)
$amwSize = static function (string $key, string $default): string {
    $v = trim((string)app_setting($key, $default));
    if ($v === '') return $default;
    if (preg_match('/^\d+(\.\d+)?$/', $v)) return $v . 'px';
    if (preg_match('/^\d+(\.\d+)?(px|rem|em|vw|%)$/', $v)) return $v;
    return $default;
};
$kickerSize  = $amwSize('header_logo_kicker_size', '12px');
$nameSize    = $amwSize('header_logo_name_size', '24px');
$sloganSize  = $amwSize('header_logo_slogan_size', '14px');
$heroLogoSize = $amwSize('hero_logo_size', '320px');
$headerStyle = "--amw-kicker-size:{$kickerSize};--amw-name-size:{$nameSize};--amw-slogan-size:{$sloganSize};--amw-hero-logo-size:{$heroLogoSize};";
?>
<a class="amw-skip" href="#contenido">Saltar al contenido</a>
<header class="amw-header" style="<?= e($headerStyle) ?>">
  <div class="amw-brandbar">
    <a class="amw-brand" href="<?= e(url('/')) ?>" aria-label="<?= e($siteName) ?>, inicio">
      <?php if ($logoUrl !== ''): ?>
        <img class="amw-brand-image" src="<?= e($logoUrl) ?>" alt="<?= e($siteName) ?>">
      <?php else: ?>
        <span class="amw-mark" aria-hidden="true">
          <svg viewBox="0 0 64 64" fill="none">
            <circle cx="32" cy="25" r="18" fill="#39A9E8" stroke="#087B43" stroke-width="3"/>
            <path d="M17 14c5 1 7 4 7 8l-5 4 2 6-5 3a18 18 0 0 1 1-21Zm28 2 5 4-2 6-5 2-4-4 2-7 4-1ZM29 39l6 1 3 7-5 6-5-5 1-9Z" fill="#8CC63F"/>
            <path d="M7 53c10-11 17-14 25-14s15 3 25 14c-12-4-20-4-25-1-6-3-14-3-25 1Z" fill="#138A47"/>
            <path d="M32 39v15" stroke="white" stroke-width="2"/>
          </svg>
        </span>
      <?php endif; ?>
      <span class="amw-brand-copy">
        <span class="amw-brand-kicker"><?= e($setting('site_brand_kicker', 'ASOCIACIÓN CIVIL')) ?></span>
        <strong><?= e($siteName) ?></strong>
        <span><?= e($setting('site_slogan', 'Juntos podemos transformar vidas y proteger nuestro planeta')) ?></span>
      </span>
    </a>
    <button class="amw-menu-toggle" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="amw-navigation">
      <span></span><span></span><span></span>
    </button>
  </div>
  <nav class="amw-nav" id="amw-navigation" aria-label="Navegación principal">
    <a class="amw-nav-home" href="<?= e(url('/')) ?>" aria-label="Inicio">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 11 9-8 9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>
      <span>Inicio</span>
    </a>
    <?php foreach ($navItems as [$key, $urlKey, $default, $defaultUrl]): ?>
      <?php
        $navUrl = $defaultUrl;
        if ($key === 'home_nav_news_text') {
            $navUrl = url('/noticias');
        } elseif ($key === 'home_nav_videos_text') {
            $navUrl = url('/videos');
        } else {
            $navUrl = $siteLink($setting($urlKey, $defaultUrl), $defaultUrl);
        }
      ?>
      <a href="<?= e($navUrl) ?>"><?= e($setting($key, $default)) ?></a>
    <?php endforeach; ?>
  </nav>
</header>

<main id="contenido">
  <section class="amw-hero" id="inicio"<?= $heroBgUrl !== '' ? ' style="--amw-hero-image:url(&quot;' . e($heroBgUrl) . '&quot;)"' : '' ?>>
    <div class="amw-hero-inner">
      <div class="amw-hero-copy" data-reveal>
        <span class="amw-eyebrow"><?= e($setting('home_hero_eyebrow', 'Una comunidad que transforma')) ?></span>
        <h1><?= nl2br(e($setting('home_hero_title', 'Construyendo un'))) ?><br><span><?= e($setting('home_hero_highlight', 'mundo mejor')) ?></span></h1>
        <p><?= nl2br(e($setting('home_hero_subtitle', 'Con solidaridad, educación y cuidado del medio ambiente. Juntos podemos transformar vidas y proteger nuestro planeta.'))) ?></p>
        <a class="amw-button amw-button-gold" href="<?= e($siteLink($setting('home_hero_button_url', '#programas'))) ?>">
          <?= e($setting('home_hero_button_text', 'Conoce nuestros programas')) ?>
          <span aria-hidden="true">→</span>
        </a>
      </div>
      <div class="amw-hero-art" aria-hidden="true" data-reveal>
        <?php if ($heroLogoUrl !== ''): ?>
          <img class="amw-hero-logo" src="<?= e($heroLogoUrl) ?>" alt="<?= e($siteName) ?>">
        <?php else: ?>
          <div class="amw-globe-orbit"></div>
          <svg class="amw-hero-globe" viewBox="0 0 440 390" fill="none">
            <path d="M25 335c63-17 104-36 144-43 59-10 117 12 246 32-61 42-119 57-199 54-72-3-132-18-191-43Z" fill="#128448"/>
            <path d="M78 311c-7-68-25-105-61-146 48 13 91 37 130 75l13 84-82-13Z" fill="#82C341" stroke="white" stroke-width="5"/>
            <path d="M360 308c14-58 34-101 72-139-57 13-102 37-141 72l-10 75 79-8Z" fill="#82C341" stroke="white" stroke-width="5"/>
            <circle cx="220" cy="150" r="114" fill="#F8FFF8" stroke="#138448" stroke-width="9"/>
            <circle cx="220" cy="150" r="95" fill="#168CCB"/>
            <path d="m160 68 28 8 11 20-18 10-4 20-22 12-12-16-23-6a94 94 0 0 1 40-48Zm104 3 24 13 9 19-14 15-25-3-7-15 13-29Zm-20 89 25 5 21 21-8 21-23 8-13-18-22-3-7-22 27-12Zm-83 39 22 4 10 20-9 25-22-3-14-17 13-29Z" fill="#87C940"/>
            <path d="M126 255c-34 39-57 84-65 123 41-30 78-45 117-47m136-74c34 39 57 84 65 123-41-30-78-45-117-47" stroke="white" stroke-width="7" stroke-linecap="round"/>
            <path d="M184 286v79m72-79v79" stroke="white" stroke-width="13" stroke-linecap="round"/>
            <circle cx="184" cy="276" r="14" fill="#087B43" stroke="white" stroke-width="4"/>
            <circle cx="256" cy="276" r="14" fill="#087B43" stroke="white" stroke-width="4"/>
            <path d="M210 294h20v73h-20z" fill="#F2A51A" stroke="white" stroke-width="4"/>
            <path d="M161 363h118" stroke="white" stroke-width="8" stroke-linecap="round"/>
          </svg>
        <?php endif; ?>
      </div>
    </div>
    <div class="amw-hero-wave" aria-hidden="true"></div>
  </section>

  <section class="amw-programs amw-section" id="programas">
    <div class="amw-section-heading" data-reveal>
      <span class="amw-section-kicker"><?= e($setting('home_programs_eyebrow', 'Lo que hacemos')) ?></span>
      <h2><?= e($setting('home_programs_title', 'Nuestros Programas')) ?></h2>
      <p><?= e($setting('home_programs_subtitle', 'Acciones que generan un impacto positivo')) ?></p>
    </div>
    <div class="amw-program-grid">
      <?php foreach (array_values((array)($programas ?? [])) as $index => $program): ?>
        <?php
          $cover = trim((string)($program['cover_image_path'] ?? ''));
          $coverUrl = $cover === '' ? '' : (
              str_starts_with($cover, '/') || preg_match('#^https?://#i', $cover)
                  ? $cover
                  : upload_url($cover)
          );
          $iconKey = (string)($program['icon_key'] ?? 'leaf');
          if (!isset($programIcons[$iconKey])) $iconKey = 'leaf';
        ?>
        <article class="amw-program-card" data-reveal>
          <div class="amw-program-visual">
            <?php if ($coverUrl !== ''): ?>
              <img src="<?= e($coverUrl) ?>" alt="<?= e($program['name'] ?? '') ?>" loading="lazy">
            <?php else: ?>
              <span class="amw-program-icon" aria-hidden="true">
                <?= $programIcons[$iconKey] ?>
              </span>
            <?php endif; ?>
            <span class="amw-program-number"><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
          </div>
          <div class="amw-program-body">
            <h3><?= e($program['name'] ?? '') ?></h3>
            <?php if (!empty($program['description'])): ?>
              <p><?= e($program['description']) ?></p>
            <?php endif; ?>
            <a href="<?= e($siteLink($setting('home_program_card_url', '#voluntariado'))) ?>" aria-label="Conoce cómo apoyar <?= e($program['name'] ?? 'este programa') ?>">
              <?= e($setting('home_program_card_link', 'Conoce el programa')) ?><span aria-hidden="true"> ↗</span>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
      <?php if (empty($programas)): ?>
        <div class="amw-program-empty">
          <span aria-hidden="true">✳</span>
          <p><?= e($setting('home_programs_empty', 'Pronto compartiremos nuevas iniciativas para transformar nuestra comunidad.')) ?></p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="amw-news amw-section" id="noticias">
    <div class="amw-section-heading" data-reveal>
      <span class="amw-section-kicker">Actualidad de la asociación</span>
      <h2>Noticias</h2>
      <p>Conoce las actividades, iniciativas y avances de nuestra comunidad.</p>
    </div>
    <?php $homeNews = array_values((array)($noticias ?? [])); ?>
    <?php if ($homeNews !== []): ?>
      <div class="amw-news-grid">
        <?php foreach ($homeNews as $newsItem): ?>
          <?php
            $newsExcerpt = trim(strip_tags((string)($newsItem['excerpt'] ?? '')));
            if ($newsExcerpt === '') $newsExcerpt = trim(strip_tags((string)($newsItem['content_html'] ?? '')));
            if (mb_strlen($newsExcerpt) > 150) $newsExcerpt = mb_substr($newsExcerpt, 0, 150) . '…';
            $newsImage = !empty($newsItem['cover_image_path'])
                ? cover_image_url($newsItem)
                : img_placeholder('16x9', $newsItem['title'] ?? 'Noticia', 'home-news-' . ($newsItem['id'] ?? 'x'));
          ?>
          <article class="amw-news-card" data-reveal>
            <a class="amw-news-image" href="<?= e(news_url($newsItem['slug'] ?? '')) ?>">
              <img src="<?= e($newsImage) ?>" alt="" loading="lazy">
            </a>
            <div class="amw-news-copy">
              <span class="amw-news-category"><?= e($newsItem['section_name'] ?? 'Amigos por el Mundo') ?></span>
              <h3><a href="<?= e(news_url($newsItem['slug'] ?? '')) ?>"><?= e($newsItem['title'] ?? '') ?></a></h3>
              <?php if ($newsExcerpt !== ''): ?><p><?= e($newsExcerpt) ?></p><?php endif; ?>
              <a class="amw-news-link" href="<?= e(news_url($newsItem['slug'] ?? '')) ?>">Leer noticia <span aria-hidden="true">→</span></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="amw-news-empty">
        <strong>Pronto compartiremos nuestras noticias.</strong>
        <span>Las publicaciones activas del panel aparecerán aquí automáticamente.</span>
      </div>
    <?php endif; ?>
    <div class="amw-news-all">
      <a class="amw-button amw-button-green" href="<?= e(url('/noticias')) ?>">Ver todas las noticias <span aria-hidden="true">→</span></a>
    </div>
  </section>

  <section class="amw-videos amw-section" id="videos">
    <div class="amw-section-heading" data-reveal>
      <span class="amw-section-kicker">Contenido audiovisual</span>
      <h2>Videos</h2>
      <p>Mira nuestras actividades, testimonios y momentos de la asociación.</p>
    </div>
    <?php $homeVideos = array_values((array)($videos ?? [])); ?>
    <?php if ($homeVideos !== []): ?>
      <div class="amw-videos-grid">
        <?php foreach ($homeVideos as $v):
            $vTitle = (string)($v['title'] ?? '');
            $vSlug  = (string)($v['slug'] ?? '');
            $vImg   = !empty($v['thumbnail_path'])
                ? img_fallback((string)$v['thumbnail_path'], '16x9', $vTitle, 'home-video-' . ((int)($v['id'] ?? 0)))
                : img_placeholder('16x9', $vTitle, 'home-video-' . ((int)($v['id'] ?? 0)));
        ?>
          <article class="amw-video-card" data-reveal>
            <a class="amw-video-image" href="<?= e(video_url($v)) ?>" aria-label="<?= e($vTitle) ?>">
              <img src="<?= e($vImg) ?>" alt="<?= e(mb_substr($vTitle, 0, 80)) ?>" loading="lazy">
              <span class="amw-video-play" aria-hidden="true">▶</span>
              <?php if (!empty($v['duration'])): ?>
                <span class="amw-video-duration"><?= e($v['duration']) ?></span>
              <?php endif; ?>
            </a>
            <div class="amw-video-copy">
              <h3><a href="<?= e(video_url($v)) ?>"><?= e($vTitle) ?></a></h3>
              <?php if (!empty($v['section_name'])): ?>
                <span class="amw-video-category"><?= e($v['section_name']) ?></span>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="amw-videos-empty">
        <strong>Pronto compartiremos nuestros videos.</strong>
        <span>Los videos publicados en el panel aparecerán aquí automáticamente.</span>
      </div>
    <?php endif; ?>
    <div class="amw-videos-all">
      <a class="amw-button amw-button-green" href="<?= e(url('/videos')) ?>">Ver todos los videos <span aria-hidden="true">→</span></a>
    </div>
  </section>

  <section class="amw-impact" id="proyectos">
    <div class="amw-impact-leaves amw-impact-leaves-left" aria-hidden="true">✦</div>
    <div class="amw-impact-inner">
      <article class="amw-impact-card" id="nosotros" data-reveal>
        <span class="amw-impact-symbol" aria-hidden="true">◎</span>
        <div>
          <span class="amw-impact-label"><?= e($setting('home_mission_label', 'Nuestro propósito')) ?></span>
          <h2><?= e($setting('home_mission_title', 'Nuestra Misión')) ?></h2>
          <p><?= e($setting('home_mission_text', 'Trabajar por el bienestar social, la educación y la protección del medio ambiente.')) ?></p>
        </div>
      </article>
      <p class="amw-impact-quote" data-reveal><?= e($setting('home_quote', 'Juntos podemos hacer la diferencia')) ?><span></span></p>
      <article class="amw-impact-card amw-impact-card-right" id="aliados" data-reveal>
        <span class="amw-impact-symbol" aria-hidden="true">◉</span>
        <div>
          <span class="amw-impact-label"><?= e($setting('home_vision_label', 'Hacia dónde vamos')) ?></span>
          <h2><?= e($setting('home_vision_title', 'Nuestra Visión')) ?></h2>
          <p><?= e($setting('home_vision_text', 'Ser una organización reconocida por impulsar comunidades solidarias, saludables y sostenibles.')) ?></p>
        </div>
      </article>
    </div>
    <div class="amw-impact-leaves amw-impact-leaves-right" aria-hidden="true">✦</div>
  </section>

  <section class="amw-join" id="voluntariado" data-reveal>
    <div>
      <span class="amw-section-kicker"><?= e($setting('home_join_eyebrow', 'Tu ayuda hace la diferencia')) ?></span>
      <h2><?= e($setting('home_join_title', 'Súmate al cambio')) ?></h2>
      <p><?= e($setting('home_join_text', 'Cada gesto cuenta. Participa como voluntario, apoya nuestros proyectos o comparte nuestra causa.')) ?></p>
    </div>
    <a class="amw-button amw-button-gold" href="<?= e($siteLink($setting('home_join_button_url', '#contacto'))) ?>">
      <?= e($setting('home_join_button_text', 'Quiero colaborar')) ?><span aria-hidden="true">→</span>
    </a>
  </section>
</main>

<footer class="amw-footer" id="contacto">
  <div class="amw-footer-main">
    <div class="amw-footer-about">
      <a class="amw-footer-brand" href="<?= e(url('/')) ?>">
        <?php if ($logoUrl !== ''): ?><img src="<?= e($logoUrl) ?>" alt="<?= e($siteName) ?>">
        <?php else: ?><span class="amw-footer-mark" aria-hidden="true">✳</span><?php endif; ?>
        <strong><?= e($siteName) ?></strong>
      </a>
      <p><?= e($setting('footer_about', 'Una asociación comprometida con las personas, la educación y el cuidado de nuestro planeta.')) ?></p>
      <?php $siteRuc = trim((string)$setting('site_ruc', '')); ?>
      <?php $siteSunarp = trim((string)$setting('site_sunarp', '')); ?>
      <?php if ($siteRuc !== '' || $siteSunarp !== ''): ?>
        <div class="amw-footer-legal">
          <?php if ($siteRuc !== ''): ?><span><strong>RUC:</strong> <?= e($siteRuc) ?></span><?php endif; ?>
          <?php if ($siteSunarp !== ''): ?><span><strong>REG. SUNARP:</strong> <?= e($siteSunarp) ?></span><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="amw-footer-contact">
      <h2><?= e($setting('home_contact_title', 'Hablemos')) ?></h2>
      <?php $address = $setting('footer_address', $setting('contact_address', '')); ?>
      <?php $officeAddress = $setting('site_office_address', ''); ?>
      <?php $email = $setting('footer_email', $setting('contact_emails', '')); ?>
      <?php $phone = $setting('footer_phone', $setting('contact_phones', '')); ?>
      <?php if ($address !== ''): ?><p><span>Dirección</span><?= e($address) ?></p><?php endif; ?>
      <?php if ($officeAddress !== ''): ?><p><span>Oficina enlace</span><?= e($officeAddress) ?></p><?php endif; ?>
      <?php if ($email !== ''): ?><p><span>Correo</span><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p><?php endif; ?>
      <?php if ($phone !== ''): ?><p><span>Contáctenos</span><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>"><?= e($phone) ?></a></p><?php endif; ?>
    </div>
    <div class="amw-footer-social">
      <h2><?= e($setting('home_social_title', 'Conectemos')) ?></h2>
      <?php foreach ([
          'Facebook' => $setting('social_facebook'),
          'Instagram' => $setting('social_instagram'),
          'YouTube' => $setting('social_youtube'),
      ] as $label => $socialUrl): ?>
        <?php $socialHref = $siteLink($socialUrl, ''); ?>
        <?php if ($socialHref !== ''): ?><a href="<?= e($socialHref) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?> <span aria-hidden="true">↗</span></a><?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="amw-footer-bottom">
    <span><?= e($setting('copyright_text', '© ' . date('Y') . ' ' . $siteName . '. Todos los derechos reservados.')) ?></span>
    <a href="<?= e(url('/admin/login')) ?>">Acceso administrativo</a>
  </div>
</footer>

<script>
(() => {
  document.documentElement.classList.add('amw-motion-ready');
  const toggle = document.querySelector('.amw-menu-toggle');
  const nav = document.getElementById('amw-navigation');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = toggle.getAttribute('aria-expanded') !== 'true';
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
      nav.classList.toggle('is-open', open);
    });
    nav.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
      toggle.setAttribute('aria-expanded', 'false');
      nav.classList.remove('is-open');
    }));
  }
  const items = document.querySelectorAll('[data-reveal]');
  if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    }), { threshold: 0.12 });
    items.forEach(item => observer.observe(item));
  } else {
    items.forEach(item => item.classList.add('is-visible'));
  }
})();
</script>

<?php
/* ============================================================
   MODAL DE PUBLICIDAD — Banner Superior (top-banner)
   Se muestra al cargar el homepage.
   ============================================================ */
$adModalAd = null;
$adModalDismissed = false;
try {
    $adModalAds = \App\Models\Ad::getActiveByPosition('top-banner');
    if (!empty($adModalAds)) {
        // Verificar si está descartado por cookie
        $adModalNow = time();
        $adModalCookie = $_COOKIE['dismissed_ads'] ?? '';
        $adModalCookArr = [];
        if ($adModalCookie !== '') {
            $tmp = json_decode($adModalCookie, true);
            if (is_array($tmp)) $adModalCookArr = $tmp;
        }
        foreach ($adModalAds as $adModalCandidate) {
            $adModalId = (int)$adModalCandidate['id'];
            $byId  = !empty($adModalCookArr['id_' . $adModalId]) && (int)$adModalCookArr['id_' . $adModalId] > $adModalNow;
            $byPos = !empty($adModalCookArr['pos_top-banner']) && (int)$adModalCookArr['pos_top-banner'] > $adModalNow;
            if (!$byId && !$byPos) {
                $adModalAd = $adModalCandidate;
                break;
            }
        }
        if ($adModalAd === null) {
            $adModalDismissed = true;
        }
    }
} catch (Throwable $e) {
    $adModalAd = null;
}
?>
<?php if ($adModalAd !== null): ?>
<?php
    $adModalId = (int)$adModalAd['id'];
    $adModalKind = $adModalAd['ad_kind'] ?? 'image';
    $adModalHtmlCode = trim((string)($adModalAd['html_code'] ?? ''));
    $adModalSp = (string)($adModalAd['image_path'] ?? '');
    $adModalImgUrl = '';
    if ($adModalSp !== '' && $adModalKind !== 'html') {
        $adModalImgUrl = (str_starts_with($adModalSp, '/') || preg_match('#^https?://#i', $adModalSp)) ? $adModalSp : upload_url($adModalSp);
    }
    $adModalLinkUrl = !empty($adModalAd['link_url']) ? url('/ads/' . $adModalId . '/click') : '';
    $adModalAlt = !empty($adModalAd['alt_text']) ? $adModalAd['alt_text'] : ($adModalAd['title'] ?? 'Publicidad');
    $adModalTitle = e($adModalAd['title'] ?? 'Publicidad');
    \App\Models\Ad::incrementViews($adModalId);
    $adModalCanShow = ($adModalKind === 'html' && $adModalHtmlCode !== '') || ($adModalImgUrl !== '');
?>
<?php if ($adModalCanShow): ?>
<div class="amw-ad-modal" id="amwAdModal" role="dialog" aria-modal="true" aria-labelledby="amwAdModalTitle" data-ad-id="<?= $adModalId ?>" data-ad-position="top-banner">
  <div class="amw-ad-modal__dialog" role="document">
    <button type="button" class="amw-ad-modal__close" aria-label="Cerrar publicidad" id="amwAdModalClose">
      <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
    <span class="amw-ad-modal__badge">Publicidad</span>
    <div class="amw-ad-modal__body" id="amwAdModalTitle">
      <?php if ($adModalKind === 'html' && $adModalHtmlCode !== ''): ?>
        <?= $adModalHtmlCode ?>
      <?php elseif ($adModalImgUrl !== ''): ?>
        <?php if ($adModalLinkUrl !== ''): ?>
          <a href="<?= e($adModalLinkUrl) ?>" target="_blank" rel="noopener sponsored nofollow" aria-label="<?= e($adModalAlt) ?>" title="<?= e($adModalAlt) ?>">
            <img src="<?= e($adModalImgUrl) ?>" alt="<?= e($adModalAlt) ?>" decoding="async">
          </a>
        <?php else: ?>
          <img src="<?= e($adModalImgUrl) ?>" alt="<?= e($adModalAlt) ?>" decoding="async">
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <div class="amw-ad-modal__footer">
      <span class="amw-ad-modal__hint">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        Esta publicidad se mostrará solo una vez por sesión · Clic en la imagen para más información
      </span>
    </div>
  </div>
</div>
<script>
(function(){
  var modal = document.getElementById('amwAdModal');
  if (!modal) return;
  var adId = parseInt(modal.getAttribute('data-ad-id'), 10) || 0;
  var adPos = modal.getAttribute('data-ad-position') || '';
  var TTL = 604800; // 7 días

  function dismiss() {
    try {
      var o = {};
      try {
        var s = sessionStorage.getItem('dismissed_ads');
        if (s) o = Object.assign(o, JSON.parse(s));
      } catch (e) {}
      try {
        var c = document.cookie.match(/(?:^|; )dismissed_ads=([^;]+)/);
        if (c) o = Object.assign(o, JSON.parse(decodeURIComponent(c[1])));
      } catch (e) {}
      var now = Math.floor(Date.now() / 1000);
      var exp = now + TTL;
      if (adId) o['id_' + adId] = exp;
      if (adPos) o['pos_' + adPos] = exp;
      try { sessionStorage.setItem('dismissed_ads', JSON.stringify(o)); } catch (e) {}
      // enviar al backend para guardar cookie
      try {
        var csrfEl = document.querySelector('meta[name="csrf-token"]');
        var tok = csrfEl ? csrfEl.content : '';
        var fd = new FormData();
        if (tok) fd.append('csrf_token', tok);
        var u = '/ads/dismiss/' + adId + '?ttl=' + TTL;
        if (adPos) u += '&position=' + encodeURIComponent(adPos);
        fetch(u, { method: 'POST', body: fd, credentials: 'same-origin' }).catch(function(){});
      } catch (e) {}
    } catch (e) {}
    modal.classList.remove('is-open');
    document.body.style.overflow = '';
    setTimeout(function(){ if (modal.parentNode) modal.parentNode.removeChild(modal); }, 350);
  }

  var closeBtn = document.getElementById('amwAdModalClose');
  if (closeBtn) closeBtn.addEventListener('click', dismiss);
  // cerrar al hacer clic fuera del diálogo
  modal.addEventListener('click', function(ev){
    if (ev.target === modal) dismiss();
  });
  // cerrar con ESC
  document.addEventListener('keydown', function(ev){
    if (ev.key === 'Escape' || ev.keyCode === 27) dismiss();
  });

  // mostrar con pequeño retardo para mejor UX
  var delay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 600;
  setTimeout(function(){
    modal.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    if (closeBtn) closeBtn.focus();
  }, delay);
})();
</script>
<?php endif; ?>
<?php endif; ?>
</body>
</html>
