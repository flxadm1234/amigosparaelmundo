<?php
$bodyClass = 'amw-body';
include __DIR__ . '/../../partials/public_head.php';

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
$headerStyle = "--amw-kicker-size:{$kickerSize};--amw-name-size:{$nameSize};--amw-slogan-size:{$sloganSize};";

$paginator = $videos ?? ($results ?? ($paginator ?? ['items' => [], 'total' => 0, 'from' => 0, 'to' => 0, 'total_pages' => 1]));
$items = isset($paginator['items']) ? $paginator['items'] : [];
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
        if (is_string($navUrl) && str_starts_with($navUrl, '#')) {
            $navUrl = rtrim(url('/'), '/') . $navUrl;
        }
      ?>
      <a href="<?= e($navUrl) ?>"<?= $key === 'home_nav_videos_text' ? ' aria-current="page"' : '' ?>><?= e($setting($key, $default)) ?></a>
    <?php endforeach; ?>
  </nav>
</header>

<main id="contenido">
  <section class="amw-videos amw-section" id="videos">
    <div class="amw-section-heading" data-reveal>
      <span class="amw-section-kicker">Contenido audiovisual</span>
      <h1>Videos</h1>
      <p>Mira nuestras actividades, testimonios y momentos de la asociación.</p>
    </div>

    <?php if (empty($items)): ?>
      <div class="amw-videos-empty">
        <strong>No hay videos publicados aún</strong>
        <span>Regresa pronto para disfrutar de nuestro contenido audiovisual.</span>
      </div>
    <?php else: ?>
      <div class="amw-videos-grid">
        <?php foreach ($items as $v):
            $vTitle = (string)($v['title'] ?? '');
            $vImg   = !empty($v['thumbnail_path'])
                ? img_fallback((string)$v['thumbnail_path'], '16x9', $vTitle, 'video-list-'.((int)($v['id'] ?? 0)))
                : img_placeholder('16x9', $vTitle, 'video-list-'.((int)($v['id'] ?? 0)));
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
              <div class="amw-video-meta">
                <?php if (!empty($v['section_name'])): ?>
                  <span class="amw-video-category"><?= e($v['section_name']) ?></span>
                <?php endif; ?>
                <?php if (!empty($v['publish_date'])): ?>
                  <span class="amw-video-date"><?= e(format_date($v['publish_date'])) ?></span>
                <?php endif; ?>
                <?php if (isset($v['views_count'])): ?>
                  <span class="amw-video-views">👁 <?= (int)$v['views_count'] ?></span>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <?php
        if (!empty($paginator) && function_exists('pagination_links')) {
            echo pagination_links($paginator);
        }
      ?>
    <?php endif; ?>
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
</body>
</html>
