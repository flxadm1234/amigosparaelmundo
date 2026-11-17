<?php include __DIR__ . '/../../partials/public_head.php'; ?>
<?php include __DIR__ . '/../../partials/public_navbar.php'; ?>

<main id="rm-main" class="rm-main" tabindex="-1">
  <?php
    $seccion = $section ?? $seccion ?? null;
    $sectionIconMap = [
        'ultimas-noticias' => 'flash',
        'politica'         => 'building',
        'lima'             => 'lima',
        'peru'             => 'pais',
        'futbol'           => 'futbol',
        'deportes'         => 'trophy',
        'mundo'            => 'globe',
        'economia'         => 'money',
        'entretenimiento'  => 'clapper',
        'cultura'          => 'theater',
        'tecnologia'       => 'laptop',
        'radio'            => 'radio',
        'videos'           => 'tv',
        'podcast'          => 'mic',
    ];
    $secSlug = $seccion['slug'] ?? '';
    $secIconKey = $sectionIconMap[$secSlug] ?? 'alert';
    $secName = $seccion['name'] ?? 'Sección';
    $secDesc = $seccion['description'] ?? '';
    $secColor = !empty($seccion['color']) ? $seccion['color'] : 'var(--rm-primary,#0052A5)';
    $paginator = $noticias ?? ($results ?? ($paginator ?? ['items' => [], 'total' => 0, 'from' => 0, 'to' => 0, 'total_pages' => 1]));
    $totalItems = isset($paginator['total']) ? (int)$paginator['total'] : (is_array($paginator['items'] ?? null) ? count($paginator['items']) : 0);
  ?>

  <section class="rm-section-head" style="--grad-c1:<?php echo e($secColor) ?>;--grad-c2:var(--rm-secondary,#00A651);" aria-labelledby="rm-section-title">
    <div class="rm-section-head-overlayA" aria-hidden="true"></div>
    <div class="rm-section-head-overlayB" aria-hidden="true"></div>
    <div class="rm-section-head-overlayGrid" aria-hidden="true"></div>
    <div class="rm-container rm-section-head-wrap">
      <div class="rm-section-head-toprow">
        <div class="rm-section-head-kicker">
          <span class="rm-section-pill">SECCIÓN</span>
          <nav aria-label="breadcrumb" class="rm-breadcrumb rm-breadcrumb-light rm-breadcrumb-pro">
            <ol>
              <li><a href="<?php echo url('/') ?>" class="rm-bc-home"><?php echo nav_icon('home', 14, 2, 'none') ?><span>Inicio</span></a></li>
              <li class="rm-active" aria-current="page"><?php echo e($secName) ?></li>
            </ol>
          </nav>
        </div>
        <div class="rm-section-head-count" title="<?php echo $totalItems ?> publicaciones">
          <?php echo nav_icon('calendar', 14, 2, 'none') ?>
          <span><?php echo $totalItems ?> <?php echo $totalItems === 1 ? 'publicación' : 'publicaciones' ?></span>
        </div>
      </div>

      <div class="rm-section-head-inner">
        <div class="rm-section-icon" aria-hidden="true">
          <?php echo nav_icon($secIconKey, 28, 1.8, 'none') ?>
        </div>
        <div class="rm-section-head-copy">
          <h1 id="rm-section-title" class="rm-section-head-title"><?php echo e($secName) ?></h1>
          <?php if (!empty($secDesc)): ?>
            <p class="rm-section-head-desc"><?php echo e($secDesc) ?></p>
          <?php else: ?>
            <p class="rm-section-head-desc">Mantente informado con las últimas noticias, reportajes y análisis de <?php echo e($secName) ?> en ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="rm-section">
    <div class="rm-container">
      <?php include __DIR__ . '/../../partials/flash.php'; ?>

      <div class="rm-news-list-grid">
        <?php
          $items = isset($paginator['items']) ? $paginator['items'] : [];
          if (empty($items)):
        ?>
          <div class="rm-empty-state">
            <div class="rm-empty-icon"><?php echo nav_icon('alert', 32, 2, 'none') ?></div>
            <h3>Aún no hay noticias en esta sección</h3>
            <p>Te invitamos a explorar otras secciones del portal.</p>
            <a class="rm-btn rm-btn-primary" href="<?php echo url('/noticias') ?>">Ver todas las noticias</a>
          </div>
        <?php else: ?>
          <?php foreach ($items as $n): ?>
            <div class="rm-news-list-item">
              <?php
                if (function_exists('news_card')) {
                  echo news_card($n, 'md');
                }
              ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php
        if (!empty($paginator) && function_exists('pagination_links')) {
          echo pagination_links($paginator);
        }
      ?>
    </div>
  </section>
</main>

<?php include __DIR__ . '/../../partials/public_footer.php'; ?>
