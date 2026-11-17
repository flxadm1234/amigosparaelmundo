<?php include __DIR__ . '/../../partials/public_head.php'; ?>
<?php include __DIR__ . '/../../partials/public_navbar.php'; ?>

<main>
  <section class="page-head page-head-light">
    <div class="container">
      <div class="page-head-inner">
        <div>
          <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="<?= url('/') ?>">Inicio</a></li>
              <li class="breadcrumb-item active" aria-current="page">Publicaciones</li>
            </ol>
          </nav>
          <h1 class="page-title">Publicaciones & Encuestas</h1>
          <p class="page-subtitle mb-0">Explora nuestros estudios electorales, sociales, educativos y de mercado.</p>
        </div>
        <div class="d-none d-md-block">
          <a class="btn btn-outline-primary btn-lg" href="#filtros">🔎 Filtrar resultados</a>
        </div>
      </div>
    </div>
  </section>

  <section class="section section-white pt-4 pt-md-5">
    <div class="container">

      <?php include __DIR__ . '/../../partials/flash.php'; ?>

      <!-- FILTROS COMPACTOS 1 LÍNEA — INMEDIATAMENTE DEBAJO DEL TEXTO EXPLORA... -->
      <form method="get" action="<?= url('/publicaciones') ?>" id="filtros"
            class="filters-compact mb-3 py-2" data-auto-submit="1">
        <div class="filters-row">
          <div class="fi fi-search">
            <label class="fi-label">Buscar</label>
            <div class="input-group input-group-sm">
              <input type="search" name="q" class="form-control" placeholder="Título, resumen…"
                     value="<?= e($filters['q']) ?>" aria-label="Buscar">
              <button class="btn btn-primary btn-sm" type="submit" title="Buscar">🔎</button>
            </div>
          </div>

          <div class="fi">
            <label class="fi-label">Año</label>
            <select name="year" class="form-select form-select-sm" data-submit-on-change>
              <option value="">Todos</option>
              <?php foreach ($years as $y): $y = (int)$y; ?>
                <option value="<?= $y ?>" <?= $filters['year'] === $y ? 'selected' : '' ?>>
                  <?= $y ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="fi">
            <label class="fi-label">Categoría</label>
            <select name="category_id" class="form-select form-select-sm" data-submit-on-change>
              <option value="0">Todas</option>
              <?php foreach ($categories as $c): $cid = (int)$c['id']; ?>
                <option value="<?= $cid ?>" <?= $filters['category_id'] === $cid ? 'selected' : '' ?>>
                  <?= e($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="fi">
            <label class="fi-label">Región</label>
            <select name="region_id" class="form-select form-select-sm" data-submit-on-change>
              <option value="0">Todas</option>
              <?php foreach ($regions as $r): $rid = (int)$r['id']; ?>
                <option value="<?= $rid ?>" <?= $filters['region_id'] === $rid ? 'selected' : '' ?>>
                  <?= e($r['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="fi">
            <label class="fi-label">Etiqueta</label>
            <select name="tag_id" class="form-select form-select-sm" data-submit-on-change>
              <option value="0">Todas</option>
              <?php foreach ($tags as $t): $tid = (int)$t['id']; ?>
                <option value="<?= $tid ?>" <?= $filters['tag_id'] === $tid ? 'selected' : '' ?>>
                  #<?= e($t['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="fi">
            <label class="fi-label">Orden</label>
            <select name="order" class="form-select form-select-sm" data-submit-on-change>
              <option value="new" <?= $filters['order'] === 'new' ? 'selected' : '' ?>>Más recientes</option>
              <option value="old" <?= $filters['order'] === 'old' ? 'selected' : '' ?>>Más antiguas</option>
            </select>
          </div>

          <div class="fi fi-actions">
            <label class="fi-label invisible">Acciones</label>
            <a class="btn btn-outline-secondary btn-sm" href="<?= url('/publicaciones') ?>" title="Limpiar filtros">
              ✕ Limpiar
            </a>
          </div>
        </div>

        <div class="filters-meta mt-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <span class="muted-sm">
            Mostrando
            <strong><?= (int)($results['from'] ?? 0) ?>–<?= (int)($results['to'] ?? 0) ?></strong>
            de <strong><?= (int)($results['total'] ?? 0) ?></strong> resultados.
          </span>
        </div>
      </form>

      <!-- BANNERS PUBLICIDAD TOP-BANNER (debajo del filtro, arriba de cards) -->
      <?php if (function_exists('render_ads_position')): ?>
        <?php render_ads_position('top-banner'); ?>
      <?php endif; ?>

      <!-- RESULTADOS -->
      <?php if (empty($results['items'])): ?>
        <div class="empty-state card">
          <div class="empty-icon">📭</div>
          <h3 class="empty-title">No se encontraron publicaciones</h3>
          <p class="empty-desc">Intenta con otros filtros o limpia la búsqueda.</p>
          <a class="btn btn-primary" href="<?= url('/publicaciones') ?>">Ver todas las publicaciones</a>
        </div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($results['items'] as $p):
              $fm = file_type_meta($p['stored_name'] ?? 'pdf.pdf');
              $title = e($p['title']);
              $href = url('/publicaciones/' . ($p['slug'] ?? ''));
              $coverText = ($p['category_name'] ?? 'Encuesta') . ' · ' . ($p['year'] ?? '');
              $firstImg = \App\Models\Publication::firstGalleryImage((int)$p['id']);
              $firstImgUrl = '';
              if (!empty($firstImg['stored_path'])) {
                  $sp = $firstImg['stored_path'];
                  $firstImgUrl = (str_starts_with($sp, '/') || preg_match('#^https?://#i', $sp)) ? $sp : upload_url($sp);
              }
              $svgBgOpacity = !empty($firstImgUrl) ? 0.74 : null;
          ?>
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
              <article class="card pub-card h-100" data-pub-id="<?= (int)$p['id'] ?>">
                <div class="pub-card-media">
                  <?php if (!empty($firstImgUrl)): ?>
                    <img src="<?= e($firstImgUrl) ?>" alt="" class="pcm-bg-image" loading="lazy" aria-hidden="true">
                  <?php endif; ?>
                  <img src="<?= e(placeholder_svg(640, 340, $coverText, null, '#ffffff', $svgBgOpacity)) ?>"
                       alt="" class="pub-cover" loading="lazy">
                  <div class="pub-card-badges">
                    <?php if (!empty($p['category_name'])): ?>
                      <span class="badge badge-primary"><?= e($p['category_name']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['year'])): ?>
                      <span class="badge badge-secondary"><?= (int)$p['year'] ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="card-body">
                  <div class="pub-meta">
                    <?php if (!empty($p['publish_date'])): ?>
                      <span>📅 <?= e(format_date($p['publish_date'])) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['region_name'])): ?>
                      <span>📍 <?= e($p['region_name']) ?></span>
                    <?php endif; ?>
                  </div>
                  <h3 class="pub-title"><a href="<?= $href ?>"><?= $title ?></a></h3>
                  <p class="pub-excerpt"><?= e(mb_substr(strip_tags($p['excerpt'] ?? ''), 0, 150)) ?><?= mb_strlen(strip_tags($p['excerpt'] ?? '')) > 150 ? '…' : '' ?></p>
                  <div class="pub-tags">
                    <?php
                      $ptags = \App\Models\Publication::tagsFor((int)$p['id']);
                      foreach (array_slice($ptags, 0, 3) as $t) {
                          echo '<span class="tag"># ' . e($t['name']) . '</span>';
                      }
                      if (count($ptags) > 3) {
                          echo '<span class="tag muted">+' . (count($ptags) - 3) . '</span>';
                      }
                    ?>
                  </div>
                  <div class="pub-actions">
                    <a class="btn btn-primary btn-sm" href="<?= $href ?>">
                      <span>📄</span><span>Ver detalles</span>
                    </a>
                    <?php
                      $atts = \App\Models\Publication::attachments((int)$p['id'], 'attachment');
                      if (!empty($atts)) {
                          $first = $atts[0];
                          echo '<a class="btn btn-outline-accent btn-sm" href="'.url('/media/'.$first['id'].'/download').'">
                                  <span>⬇️</span><span>Descargar</span>
                                </a>';
                      }
                    ?>
                  </div>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="mt-5">
          <?= pagination_links($results) ?>
        </div>
      <?php endif; ?>

    </div>
  </section>
</main>

<?php include __DIR__ . '/../../partials/public_footer.php'; ?>
