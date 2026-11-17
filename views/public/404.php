<?php include __DIR__ . '/../partials/public_head.php'; ?>
<?php include __DIR__ . '/../partials/public_navbar.php'; ?>

<main id="rm-main" class="rm-main" tabindex="-1">
  <section class="rm-section" style="min-height:60vh;">
    <div class="rm-container">
      <?php include __DIR__ . '/../partials/flash.php'; ?>

      <div class="rm-404-wrap">
        <div class="rm-404-code" aria-hidden="true">404</div>
        <h1 class="rm-404-title">😕 Noticia no encontrada</h1>
        <p class="rm-404-desc mb-4">
          <?php echo e($message ?? 'La página que buscas no existe, fue eliminada o se movió de lugar.') ?>
        </p>

        <form method="get" action="<?php echo url('/noticias') ?>" class="rm-404-search">
          <div class="rm-input-group rm-input-group-lg">
            <input type="search" name="q" class="rm-form-input" placeholder="🔎 Buscar noticias…" aria-label="Buscar noticias">
            <button type="submit" class="rm-btn rm-btn-primary rm-btn-lg">Buscar</button>
          </div>
        </form>

        <div class="rm-404-actions">
          <a class="rm-btn rm-btn-primary rm-btn-lg" href="<?php echo url('/') ?>">🏠 Volver al inicio</a>
          <a class="rm-btn rm-btn-outline rm-btn-lg" href="<?php echo url('/noticias') ?>">📰 Ver todas las noticias</a>
        </div>
      </div>

      <section class="rm-404-recomendadas">
        <h2 class="rm-section-title">📰 Quizás te interese</h2>
        <?php
          $recomendadas = method_exists(\App\Models\News::class, 'lastPublished')
              ? \App\Models\News::lastPublished(6)
              : [];
        ?>
        <?php if (empty($recomendadas)): ?>
          <div class="rm-empty-state">
            <p>No hay recomendaciones disponibles.</p>
          </div>
        <?php else: ?>
          <div class="rm-news-grid">
            <?php foreach ($recomendadas as $rn): ?>
              <div class="rm-news-grid-item">
                <?php
                  if (function_exists('news_card')) {
                    echo news_card($rn, 'md');
                  }
                ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </section>
</main>

<?php include __DIR__ . '/../partials/public_footer.php'; ?>
