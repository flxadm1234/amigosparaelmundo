<?php
  $metaTitle = $podcast['meta_title'] ?? $podcast['title'] ?? app_setting('site_name');
  $metaDesc  = $podcast['meta_desc'] ?? $podcast['description'] ?? '';
  include __DIR__ . '/../../partials/public_head.php';
  include __DIR__ . '/../../partials/public_navbar.php';

  $coverImg = !empty($podcast['cover_image_path'])
      ? upload_url($podcast['cover_image_path'])
      : img_placeholder('1x1', $podcast['program_name'] ?? 'Podcast', 'podcast-cover-'.($podcast['id'] ?? 'show'));

  $relacionados = $relacionados ?? [];
  $audioSrc = !empty($podcast['audio_path']) ? upload_url($podcast['audio_path']) : '';
?>

<main id="rm-main" class="rm-main" tabindex="-1">
  <article class="rm-podcast-detail-wrap">
    <div class="rm-container">
      <?php include __DIR__ . '/../../partials/flash.php'; ?>

      <nav aria-label="breadcrumb" class="rm-breadcrumb">
        <ol>
          <li><a href="<?php echo url('/') ?>">🏠 Inicio</a></li>
          <li><a href="<?php echo url('/podcast') ?>">🎙️ Podcast</a></li>
          <li class="rm-active"><?php echo e(mb_substr($podcast['title'] ?? '', 0, 50)) ?><?php echo mb_strlen($podcast['title'] ?? '') > 50 ? '…' : '' ?></li>
        </ol>
      </nav>

      <div class="rm-two-col">
        <div class="rm-col-main">
          <section class="rm-podcast-player-section">
            <div class="rm-podcast-detail-hero">
              <div class="rm-podcast-detail-cover has-bg-img">
                <img src="<?php echo e($coverImg) ?>" alt="" class="bg-img-fill" aria-hidden="true">
              </div>
              <div class="rm-podcast-detail-info">
                <?php if (!empty($podcast['program_name'])): ?>
                  <div class="rm-podcast-detail-program"><?php echo e($podcast['program_name']) ?></div>
                <?php endif; ?>
                <h1 class="rm-podcast-detail-title"><?php echo e($podcast['title'] ?? '') ?></h1>
                <div class="rm-podcast-detail-meta">
                  <?php if (isset($podcast['episode_number']) && (int)$podcast['episode_number'] > 0): ?>
                    <span class="rm-meta-episodio-badge">Episodio <?php echo (int)$podcast['episode_number'] ?></span>
                  <?php endif; ?>
                  <?php if (!empty($podcast['duration'])): ?>
                    <span class="rm-meta-duration">⏱ <?php echo e($podcast['duration']) ?></span>
                  <?php endif; ?>
                  <?php if (!empty($podcast['hosts'])): ?>
                    <span class="rm-meta-host">🎤 <?php echo e($podcast['hosts']) ?></span>
                  <?php endif; ?>
                  <?php if (!empty($podcast['publish_date'])): ?>
                    <span class="rm-meta-date">📅 <?php echo e(format_date_spanish($podcast['publish_date'])) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <?php if (!empty($audioSrc)): ?>
              <div class="rm-podcast-player-box">
                <audio controls class="rm-audio-player">
                  <source src="<?php echo e($audioSrc) ?>" type="audio/mpeg">
                    Tu navegador no soporta el elemento audio.
                </audio>
              </div>
            <?php endif; ?>

            <?php if (!empty($podcast['description'])): ?>
              <div class="rm-podcast-detail-desc">
                <?php echo $podcast['description'] ?>
              </div>
            <?php endif; ?>
          </section>

          <?php if (!empty($relacionados) && is_array($relacionados) && count($relacionados) > 0): ?>
            <section class="rm-related-section">
              <h3 class="rm-article-subtitle">🎙️ Episodios relacionados</h3>
              <div class="rm-podcasts-grid rm-podcasts-grid-sm">
                <?php foreach (array_slice($relacionados, 0, 4) as $rp):
                    $rpImg = !empty($rp['cover_image_path'])
                        ? upload_url($rp['cover_image_path'])
                        : img_placeholder('1x1', $rp['program_name'] ?? 'Podcast', 'podcast-rel-'.($rp['id'] ?? ('rp-0')));
                ?>
                  <article class="rm-podcast-card rm-podcast-card-sm">
                    <a href="<?php echo podcast_url($rp) ?>" class="rm-podcast-link">
                      <div class="rm-podcast-cover has-bg-img">
                        <img src="<?php echo e($rpImg) ?>" alt="" class="bg-img-fill" aria-hidden="true">
                        <span class="rm-play-overlay-large">▶</span>
                      </div>
                      <div class="rm-podcast-body">
                        <h3 class="rm-podcast-title-sm"><?php echo e($rp['title'] ?? '') ?></h3>
                      </div>
                    </a>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>
        </div>

        <aside class="rm-col-sidebar" role="complementary" aria-label="Programas y podcasts relacionados">
          <div class="rm-sidebar-card">
            <h3 class="rm-sidebar-title">🎙️ Más podcasts</h3>
            <div class="rm-sidebar-list">
              <?php
                $mpSidebar = method_exists(\App\Models\Podcast::class, 'lastPublished')
                    ? \App\Models\Podcast::lastPublished(6)
                    : [];
                foreach ((array)$mpSidebar as $mp):
                    $mpImg = !empty($mp['cover_image_path'])
                        ? upload_url($mp['cover_image_path'])
                        : img_placeholder('1x1', $mp['program_name'] ?? 'Podcast', 'podcast-sidebar-'.($mp['id'] ?? ('mp-0')));
              ?>
                <a href="<?php echo podcast_url($mp) ?>" class="rm-sidebar-podcast rm-sidebar-podcast-sm">
                  <div class="rm-sidebar-podcast-cover-sm has-bg-img">
                    <img src="<?php echo e($mpImg) ?>" alt="" class="bg-img-fill" aria-hidden="true">
                  </div>
                  <div class="rm-sidebar-podcast-body">
                    <h4 class="rm-sidebar-podcast-title-sm"><?php echo e($mp['title'] ?? '') ?></h4>
                    <?php if (!empty($mp['program_name'])): ?>
                      <span class="rm-sidebar-podcast-program-sm"><?php echo e($mp['program_name']) ?></span>
                    <?php endif; ?>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </article>
</main>

<?php include __DIR__ . '/../../partials/public_footer.php'; ?>
