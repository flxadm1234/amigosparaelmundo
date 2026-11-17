<?php
$setting = static fn(string $key, string $default = ''): string => app_setting($key, $default);
$siteLink = static function (string $value, string $default = '#'): string {
    $value = trim($value);
    if ($value === '' || str_starts_with($value, '//')) { return $default; }
    return preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $value) ? $value : $default;
};
$siteName = app_setting('site_name', 'ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO');
$siteLogo = trim((string)app_setting('site_logo', ''));
$logoUrl = $siteLogo === '' ? '' : (
    str_starts_with($siteLogo, '/') || preg_match('#^https?://#i', $siteLogo) ? $siteLogo : upload_url($siteLogo)
);
$kicker = app_setting('site_brand_kicker', 'ASOCIACIÓN CIVIL');
$slogan = app_setting('site_slogan', 'Juntos podemos transformar vidas y proteger nuestro planeta');
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
$navItems = [
    ['home_nav_about_text', 'home_nav_about_url', 'Quiénes Somos', '#nosotros'],
    ['home_nav_programs_text', 'home_nav_programs_url', 'Nuestros Programas', '#programas'],
    ['home_nav_projects_text', 'home_nav_projects_url', 'Proyectos', '#proyectos'],
    ['home_nav_news_text', 'home_nav_news_url', 'Noticias', '/noticias'],
    ['home_nav_partners_text', 'home_nav_partners_url', 'Empresas Aliadas', '#aliados'],
    ['home_nav_volunteer_text', 'home_nav_volunteer_url', 'Voluntariado', '#voluntariado'],
    ['home_nav_contact_text', 'home_nav_contact_url', 'Contáctenos', '#contacto'],
];
$paginator = $noticias ?? ['items' => [], 'total' => 0, 'page' => 1, 'total_pages' => 1];
$items = (array)($paginator['items'] ?? []);
$qVal = (string)($filters['q'] ?? '');
$sectionId = (int)($filters['section_id'] ?? 0);
$sections = (array)($seccionesMenu ?? []);
$currentPage = max(1, (int)($paginator['page'] ?? 1));
$totalPages = max(1, (int)($paginator['total_pages'] ?? 1));
$pageQuery = $_GET;
?>
<?php include __DIR__ . '/../../partials/public_head.php'; ?>

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
        <span class="amw-brand-kicker"><?= e($kicker) ?></span>
        <strong><?= e($siteName) ?></strong>
        <span><?= e($slogan) ?></span>
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
        $navUrl = $key === 'home_nav_news_text' ? url('/noticias') : $siteLink($setting($urlKey, $defaultUrl), $defaultUrl);
        // Los anchors (#seccion) solo existen en la página de inicio: prefijar la URL base.
        if (str_starts_with($navUrl, '#')) {
            $navUrl = rtrim(url('/'), '/') . $navUrl;
        }
      ?>
      <a href="<?= e($navUrl) ?>" <?= $key === 'home_nav_news_text' ? 'aria-current="page"' : '' ?>><?= e($setting($key, $default)) ?></a>
    <?php endforeach; ?>
  </nav>
</header>

<main class="amw-news-archive" id="contenido">
  <section class="amw-news-hero">
    <div class="amw-news-hero-inner">
      <span class="amw-section-kicker">Amigos por el Mundo</span>
      <h1>Noticias de nuestra comunidad</h1>
      <p>Actividades, iniciativas y avances que construimos juntos.</p>
    </div>
  </section>

  <section class="amw-news-archive-content amw-section">
    <form class="amw-news-filter" method="get" action="<?= e(url('/noticias')) ?>">
      <label>
        <span>Buscar noticias</span>
        <input type="search" name="q" value="<?= e($qVal) ?>" placeholder="Escribe una palabra o tema">
      </label>
      <label>
        <span>Sección</span>
        <select name="section_id">
          <option value="0">Todas las secciones</option>
          <?php foreach ($sections as $section): ?>
            <option value="<?= (int)($section['id'] ?? 0) ?>" <?= $sectionId === (int)($section['id'] ?? 0) ? 'selected' : '' ?>>
              <?= e($section['name'] ?? '') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="amw-button amw-button-green" type="submit">Buscar</button>
    </form>

    <?php if ($items !== []): ?>
      <div class="amw-news-grid">
        <?php foreach ($items as $newsItem): ?>
          <?php
            $excerpt = trim(strip_tags((string)($newsItem['excerpt'] ?? '')));
            if ($excerpt === '') $excerpt = trim(strip_tags((string)($newsItem['content_html'] ?? '')));
            if (mb_strlen($excerpt) > 170) $excerpt = mb_substr($excerpt, 0, 170) . '…';
            $image = !empty($newsItem['cover_image_path'])
                ? cover_image_url($newsItem)
                : img_placeholder('16x9', $newsItem['title'] ?? 'Noticia', 'archive-news-' . ($newsItem['id'] ?? 'x'));
          ?>
          <article class="amw-news-card">
            <a class="amw-news-image" href="<?= e(news_url($newsItem['slug'] ?? '')) ?>">
              <img src="<?= e($image) ?>" alt="" loading="lazy">
            </a>
            <div class="amw-news-copy">
              <span class="amw-news-category"><?= e($newsItem['section_name'] ?? 'Amigos por el Mundo') ?></span>
              <h2><a href="<?= e(news_url($newsItem['slug'] ?? '')) ?>"><?= e($newsItem['title'] ?? '') ?></a></h2>
              <?php if ($excerpt !== ''): ?><p><?= e($excerpt) ?></p><?php endif; ?>
              <a class="amw-news-link" href="<?= e(news_url($newsItem['slug'] ?? '')) ?>">Leer noticia <span aria-hidden="true">→</span></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if ($totalPages > 1): ?>
        <nav class="amw-news-pagination" aria-label="Paginación de noticias">
          <?php for ($pageNo = 1; $pageNo <= $totalPages; $pageNo++): ?>
            <?php $pageHref = url('/noticias' . ($pageNo > 1 ? '/pagina/' . $pageNo : '')) . ($pageQuery !== [] ? '?' . http_build_query($pageQuery) : ''); ?>
            <a href="<?= e($pageHref) ?>"
               <?= $pageNo === $currentPage ? 'aria-current="page"' : '' ?>><?= $pageNo ?></a>
          <?php endfor; ?>
        </nav>
      <?php endif; ?>
    <?php else: ?>
      <div class="amw-news-empty">
        <strong>No hay noticias publicadas todavía.</strong>
        <span>Las noticias publicadas desde el panel administrativo aparecerán aquí.</span>
      </div>
    <?php endif; ?>
  </section>
</main>

<footer class="amw-news-footer">
  <a href="<?= e(url('/')) ?>"><?= e($siteName) ?></a>
  <span><?= e(app_setting('footer_about', 'Una asociación comprometida con las personas y el cuidado de nuestro planeta.')) ?></span>
  <?php
    $newsAddress = trim((string)app_setting('footer_address', ''));
    $newsOffice = trim((string)app_setting('site_office_address', ''));
    $newsPhone = trim((string)app_setting('footer_phone', ''));
    $newsRuc = trim((string)app_setting('site_ruc', ''));
    $newsSunarp = trim((string)app_setting('site_sunarp', ''));
  ?>
  <?php if ($newsAddress !== ''): ?><span class="amw-footer-legal"><strong>Dirección:</strong> <?= e($newsAddress) ?></span><?php endif; ?>
  <?php if ($newsOffice !== ''): ?><span class="amw-footer-legal"><strong>Oficina enlace:</strong> <?= e($newsOffice) ?></span><?php endif; ?>
  <?php if ($newsPhone !== ''): ?><span class="amw-footer-legal"><strong>Contáctenos:</strong> <?= e($newsPhone) ?></span><?php endif; ?>
  <?php if ($newsRuc !== '' || $newsSunarp !== ''): ?>
    <span class="amw-footer-legal">
      <?php if ($newsRuc !== ''): ?><strong>RUC:</strong> <?= e($newsRuc) ?> · <?php endif; ?>
      <?php if ($newsSunarp !== ''): ?><strong>REG. SUNARP:</strong> <?= e($newsSunarp) ?><?php endif; ?>
    </span>
  <?php endif; ?>
</footer>

<script>
(() => {
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
