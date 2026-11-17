<?php include __DIR__ . '/../../partials/public_head.php'; ?>
<?php include __DIR__ . '/../../partials/public_navbar.php'; ?>

<main id="rm-main" class="rm-main" tabindex="-1">
  <?php
    $paginator = $episodios ?? ($podcasts ?? ($results ?? ($paginator ?? ['items' => [], 'total' => 0, 'from' => 0, 'to' => 0, 'total_pages' => 1])));
    $items = isset($paginator['items']) ? $paginator['items'] : [];
  ?>

  <section class="rm-page-head">
    <div class="rm-container">
      <nav aria-label="breadcrumb" class="rm-breadcrumb">
        <ol>
          <li><a href="<?php echo url('/') ?>">🏠 Inicio</a></li>
          <li class="rm-active">🎙️ Podcast</li>
        </ol>
      </nav>
      <h1 class="rm-page-title">🎙️ Podcast</h1>
      <p class="rm-page-subtitle">Nuestros programas y contenidos en formato de audio.</p>
    </div>
  </section>

  <section class="rm-section">
    <div class="rm-container">
      <?php include __DIR__ . '/../../partials/flash.php'; ?>

      <?php if (empty($items)): ?>
        <div class="rm-empty-state">
          <div class="rm-empty-icon">🎙️</div>
          <h3>No hay episodios publicados aún</h3>
          <p>Regresa pronto para disfrutar de nuestros podcasts.</p>
          <a class="rm-btn rm-btn-primary" href="<?php echo url('/') ?>">Volver al inicio</a>
        </div>
      <?php else: ?>
        <div class="rm-podcasts-grid">
          <?php foreach ($items as $p):
              $pImg = !empty($p['cover_image_path'])
                  ? upload_url($p['cover_image_path'])
                  : img_placeholder('1x1', $p['program_name'] ?? 'Podcast', 'podcast-list-'.($p['id'] ?? 'p0'));
          ?>
            <article class="rm-podcast-card">
              <a href="<?php echo podcast_url($p) ?>" class="rm-podcast-link">
                <div class="rm-podcast-cover has-bg-img">
                  <img src="<?php echo e($pImg) ?>" alt="" class="bg-img-fill" aria-hidden="true">
                  <span class="rm-play-overlay-large">▶</span>
                </div>
                <div class="rm-podcast-body">
                  <?php if (!empty($p['program_name'])): ?>
                    <div class="rm-podcast-program"><?php echo e($p['program_name']) ?></div>
                  <?php endif; ?>
                  <h3 class="rm-podcast-title"><?php echo e($p['title'] ?? '') ?></h3>
                  <div class="rm-podcast-meta">
                    <?php if (isset($p['episode_number']) && (int)$p['episode_number'] > 0): ?>
                      <span class="rm-meta-episodio">Ep. <?php echo (int)$p['episode_number'] ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['duration'])): ?>
                      <span class="rm-meta-duration">⏱ <?php echo e($p['duration']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['publish_date'])): ?>
                      <span class="rm-meta-date"><?php echo e(format_date($p['publish_date'])) ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
        </div>

        <?php
          if (!empty($paginator) && function_exists('pagination_links')) {
            echo pagination_links($paginator);
          }
        ?>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php include __DIR__ . '/../../partials/public_footer.php'; ?>
