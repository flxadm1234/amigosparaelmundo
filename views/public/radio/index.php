<?php include __DIR__ . '/../../partials/public_head.php'; ?>
<?php include __DIR__ . '/../../partials/public_navbar.php'; ?>

<main id="rm-main" class="rm-main" tabindex="-1">
  <?php
    $stream_url = $stream_url ?? app_setting('radio_stream_url', '');
    $nowPlaying = app_setting('radio_now_playing', '');
    $programas = $programas ?? [];
  ?>

  <section class="rm-radio-hero" style="--grad-c1:var(--rm-secondary,#00A651);--grad-c2:#007A3D;">
    <div class="rm-container">
      <nav aria-label="breadcrumb" class="rm-breadcrumb rm-breadcrumb-light">
        <ol>
          <li><a href="<?php echo url('/') ?>">🏠 Inicio</a></li>
          <li class="rm-active">📻 Radio</li>
        </ol>
      </nav>
      <div class="rm-radio-hero-inner">
        <div class="rm-radio-hero-icon">📻</div>
        <div>
          <h1 class="rm-radio-hero-title">📻 En Vivo - AMIGOS POR EL MUNDO Radio</h1>
          <p class="rm-radio-hero-subtitle">La señal informativa del Perú, 24 horas al día.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="rm-section">
    <div class="rm-container">
      <?php include __DIR__ . '/../../partials/flash.php'; ?>

      <div class="rm-radio-player-wrap">
        <div class="rm-radio-player-card">
          <?php if (!empty($stream_url)): ?>
            <div class="rm-radio-player-header">
              <div class="rm-radio-onair">
                <span class="rm-dot-live"></span>
                <strong>EN VIVO AHORA</strong>
              </div>
              <?php if (!empty($nowPlaying)): ?>
                <div class="rm-radio-now">
                  🎵 <?php echo e($nowPlaying) ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="rm-radio-player">
              <audio controls class="rm-audio-player">
                <source src="<?php echo e($stream_url) ?>" type="audio/mpeg">
                Tu navegador no soporta el elemento audio.
              </audio>
            </div>
          <?php else: ?>
            <div class="rm-empty-state rm-empty-state--transparent">
              <div class="rm-empty-icon">📻</div>
              <h3>Próximamente configuraremos la señal en vivo.</h3>
              <p>Pronto podrás escuchar nuestra programación en línea 24 horas.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <section class="rm-programas-section">
        <h2 class="rm-section-title">📺 Nuestros Programas</h2>
        <?php if (empty($programas)): ?>
          <div class="rm-empty-state">
            <div class="rm-empty-icon">📋</div>
            <h3>Próximamente publicaremos nuestra parrilla de programas</h3>
          </div>
        <?php else: ?>
          <div class="rm-programas-grid">
            <?php foreach ($programas as $prog):
                $progImg = !empty($prog['cover_image_path'])
                    ? upload_url($prog['cover_image_path'])
                    : img_placeholder('3x2', $prog['name'] ?? 'Programa', 'radio-prog-'.($prog['id'] ?? ('prog-0')));
            ?>
              <article class="rm-programa-card">
                <div class="rm-programa-cover has-bg-img">
                  <img src="<?php echo e($progImg) ?>" alt="" class="bg-img-fill" aria-hidden="true">
                </div>
                <div class="rm-programa-body">
                  <h3 class="rm-programa-name"><?php echo e($prog['name'] ?? '') ?></h3>
                  <?php if (!empty($prog['schedule_text'])): ?>
                    <div class="rm-programa-schedule">⏰ <?php echo e($prog['schedule_text']) ?></div>
                  <?php endif; ?>
                  <?php if (!empty($prog['hosts'])): ?>
                    <div class="rm-programa-hosts">🎤 <?php echo e($prog['hosts']) ?></div>
                  <?php endif; ?>
                  <?php if (!empty($prog['description_short']) || !empty($prog['description'])): ?>
                    <p class="rm-programa-desc">
                      <?php
                        $desc = $prog['description_short'] ?? $prog['description'] ?? '';
                        echo e(mb_substr(strip_tags($desc), 0, 150));
                        echo mb_strlen(strip_tags($desc)) > 150 ? '…' : '';
                      ?>
                    </p>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </section>
</main>

<?php include __DIR__ . '/../../partials/public_footer.php'; ?>
