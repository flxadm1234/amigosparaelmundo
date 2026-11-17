<?php
$bodyClass = 'amw-body';
$metaTitle = ($noticia['meta_title'] ?? '') !== '' ? $noticia['meta_title'] : (($noticia['title'] ?? 'Noticia') . ' | ' . app_setting('site_name'));
$metaDesc  = $noticia['meta_desc'] ?? ($noticia['excerpt'] ?? '');
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

$coverImg = !empty($noticia['cover_image_path'])
    ? cover_image_url($noticia)
    : img_placeholder('16x9', $noticia['title'] ?? 'Noticia', 'news-cover-'.($noticia['id'] ?? 'show'));

$galeria = $galeria ?? [];
$adjuntos = $adjuntos ?? [];
$tags = $tags ?? [];
$relacionadas = $related ?? [];
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
      <a href="<?= e($navUrl) ?>"><?= e($setting($key, $default)) ?></a>
    <?php endforeach; ?>
  </nav>
</header>

<main id="contenido">
  <article class="amw-news-article amw-section">
    <div class="amw-container">
      <nav class="amw-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= e(url('/')) ?>">Inicio</a>
        <span aria-hidden="true">/</span>
        <a href="<?= e(url('/noticias')) ?>">Noticias</a>
        <?php if (!empty($noticia['section_name'])): ?>
          <span aria-hidden="true">/</span>
          <span><?= e($noticia['section_name']) ?></span>
        <?php endif; ?>
      </nav>

      <header class="amw-news-article-header">
        <?php if (!empty($noticia['section_name'])): ?>
          <span class="amw-news-article-section"><?= e($noticia['section_name']) ?></span>
        <?php endif; ?>
        <h1><?= e($noticia['title'] ?? '') ?></h1>
        <?php if (!empty($noticia['excerpt'])): ?>
          <p class="amw-news-article-lead"><?= e($noticia['excerpt']) ?></p>
        <?php endif; ?>
        <div class="amw-news-article-meta">
          <?php if (!empty($noticia['author_name'])): ?>
            <span>✍️ <?= e($noticia['author_name']) ?></span>
          <?php endif; ?>
          <?php if (!empty($noticia['publish_date'])): ?>
            <span>📅 <?= e(format_date($noticia['publish_date'])) ?></span>
          <?php endif; ?>
          <?php if (isset($noticia['views_count'])): ?>
            <span>👁 <?= (int)$noticia['views_count'] ?> lecturas</span>
          <?php endif; ?>
        </div>
      </header>

      <figure class="amw-news-article-cover">
        <img src="<?= e($coverImg) ?>" alt="<?= e($noticia['title'] ?? '') ?>" loading="eager">
        <?php if (!empty($noticia['cover_caption'])): ?>
          <figcaption><?= e($noticia['cover_caption']) ?></figcaption>
        <?php endif; ?>
      </figure>

      <div class="amw-news-article-content">
        <?php
          if (!empty($noticia['content_html'])) {
            echo $noticia['content_html'];
          }
        ?>
      </div>

      <?php if (!empty($galeria) && is_array($galeria) && count($galeria) > 0): ?>
        <section class="amw-news-gallery">
          <h2>📷 Galería de fotos</h2>
          <div class="amw-news-gallery-grid">
            <?php foreach ($galeria as $gi):
                $gSrc = !empty($gi['stored_path']) ? upload_url($gi['stored_path']) : '';
                $gAlt = $gi['original_name'] ?? 'Imagen';
                if (empty($gSrc)) continue;
            ?>
              <a href="<?= e($gSrc) ?>" class="amw-news-gallery-item" data-lightbox="galeria" data-caption="<?= e($gAlt) ?>">
                <img src="<?= e($gSrc) ?>" alt="<?= e($gAlt) ?>" loading="lazy">
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if (!empty($adjuntos) && is_array($adjuntos) && count($adjuntos) > 0): ?>
        <section class="amw-news-attachments">
          <h2>📎 Archivos adjuntos</h2>
          <ul class="amw-news-attachments-list">
            <?php foreach ($adjuntos as $att):
                $attId = (int)($att['id'] ?? 0);
                $attName = $att['original_name'] ?? 'Archivo';
                $attExt = strtolower(pathinfo($att['stored_name'] ?? '.pdf', PATHINFO_EXTENSION));
                $attSize = (int)($att['size_bytes'] ?? 0);
                $attDl = url('/media/' . $attId . '/download');
                $attIcon = in_array($attExt, ['pdf']) ? '📕'
                    : (in_array($attExt, ['doc','docx']) ? '📘'
                    : (in_array($attExt, ['xls','xlsx','csv']) ? '📗'
                    : (in_array($attExt, ['zip','rar']) ? '🗜️'
                    : (in_array($attExt, ['jpg','jpeg','png','gif','webp']) ? '🖼️'
                    : '📄'))));
            ?>
              <li class="amw-news-attachment-item">
                <span class="amw-news-attachment-icon"><?= $attIcon ?></span>
                <span class="amw-news-attachment-name"><?= e($attName) ?></span>
                <?php if ($attSize > 0): ?>
                  <span class="amw-news-attachment-size"><?= e(format_bytes($attSize)) ?></span>
                <?php endif; ?>
                <a class="amw-button amw-button-outline" href="<?= e($attDl) ?>">⬇️ Descargar</a>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>

      <?php if (!empty($tags) && is_array($tags) && count($tags) > 0): ?>
        <section class="amw-news-tags">
          <h2>🏷️ Etiquetas</h2>
          <div class="amw-news-tags-wrap">
            <?php foreach ($tags as $t): ?>
              <a class="amw-news-tag" href="<?= e(url('/noticias')) ?>?tag=<?= urlencode($t['name'] ?? '') ?>">
                #<?= e($t['name']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if (!empty($relacionadas) && is_array($relacionadas) && count($relacionadas) > 0): ?>
        <section class="amw-news-related">
          <h2>🔗 También te puede interesar</h2>
          <div class="amw-news-related-grid">
            <?php foreach (array_slice($relacionadas, 0, 4) as $r):
                $rTitle = (string)($r['title'] ?? '');
                $rImg   = !empty($r['cover_image_path'])
                    ? img_fallback((string)$r['cover_image_path'], '16x9', $rTitle, 'news-rel-'.((int)($r['id'] ?? 0)))
                    : img_placeholder('16x9', $rTitle, 'news-rel-'.((int)($r['id'] ?? 0)));
            ?>
              <a class="amw-news-related-item" href="<?= e(news_url($r)) ?>">
                <div class="amw-news-related-image">
                  <img src="<?= e($rImg) ?>" alt="<?= e(mb_substr($rTitle, 0, 80)) ?>" loading="lazy">
                </div>
                <h3><?= e($rTitle) ?></h3>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    </div>
  </article>
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
})();
</script>
</body>
</html>
