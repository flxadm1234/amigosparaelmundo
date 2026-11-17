<?php require dirname(__DIR__, 2) . '/partials/public_head.php'; ?>
<?php require dirname(__DIR__, 2) . '/partials/public_navbar.php'; ?>
<main id="main" class="rm-live-page rm-site-main" role="main">
  <section class="rm-live-hero">
    <div class="container">
      <div class="rm-live-hero-head">
        <span class="rm-live-dot-badge">
          <span class="dot"></span> EN VIVO
        </span>
        <h1 class="rm-live-title"><?= e($title) ?></h1>
        <?php if (!$enabled): ?>
          <p class="rm-live-muted">La transmisión en vivo no se encuentra habilitada en este momento.</p>
        <?php endif; ?>
      </div>

      <div class="rm-live-stage<?= !$enabled ? ' is-offline' : '' ?>">
        <?php if ($enabled && $embedType === 'youtube'): ?>
          <div class="rm-embed rm-embed-16-9">
            <iframe
              src="<?= e($embedSrc) ?>"
              title="<?= e($title) ?>"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowfullscreen
              referrerpolicy="no-referrer-when-downgrade"
              loading="eager"></iframe>
          </div>
        <?php elseif ($enabled && $embedType === 'hls'): ?>
          <div class="rm-embed rm-embed-video">
            <video
              id="rm-live-video"
              class="rm-live-video"
              src="<?= e($embedSrc) ?>"
              autoplay muted playsinline controls
              preload="metadata"></video>
          </div>
        <?php elseif ($enabled && $embedType === 'iframe'): ?>
          <div class="rm-embed rm-embed-16-9">
            <iframe
              src="<?= e($embedSrc) ?>"
              title="<?= e($title) ?>"
              frameborder="0"
              allow="autoplay; fullscreen; encrypted-media; picture-in-picture"
              allowfullscreen
              referrerpolicy="no-referrer-when-downgrade"
              sandbox="allow-same-origin allow-scripts allow-forms allow-popups allow-presentation"></iframe>
          </div>
        <?php else: ?>
          <div class="rm-live-offline-card">
            <div class="icon">📺</div>
            <h2>Transmisión no disponible</h2>
            <p>Activa la señal desde el <strong>Panel Administrativo → Ajustes → AMIGOS TV en vivo</strong>.</p>
            <div class="rm-live-offline-tip">
              Soporte: YouTube Live (https://www.youtube.com/embed/VIDEO_ID) · HLS (.m3u8) · Cualquier iframe público.
            </div>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($enabled && !empty($rawUrl)): ?>
      <div class="rm-live-actions">
        <a class="btn btn-outline" href="<?= e($rawUrl) ?>" target="_blank" rel="noopener noreferrer">
          🔗 Abrir reproductor externo
        </a>
        <?php if (!empty($radioStreamUrl)): ?>
        <a class="btn btn-outline" href="/radio">
          📻 Sintonizar Radio <?= e($siteName) ?>
        </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php require dirname(__DIR__, 2) . '/partials/public_footer.php'; ?>
