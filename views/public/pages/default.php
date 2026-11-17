<?php include __DIR__ . '/../../partials/public_head.php'; ?>
<?php include __DIR__ . '/../../partials/public_navbar.php'; ?>

<?php
  $coverPath = !empty($page['cover_image_path']) ? (string)$page['cover_image_path'] : '';
  $coverUrl = '';
  if ($coverPath !== '') {
    $coverUrl = (str_starts_with($coverPath, '/') || preg_match('#^https?://#i', $coverPath))
      ? $coverPath
      : upload_url($coverPath);
  }
  $coverAlt = trim((string)($page['cover_alt_text'] ?? '')) ?: trim((string)($page['title'] ?? ''));
?>

<main>
  <?php if ($coverUrl !== ''): ?>
    <!-- Hero con imagen destacada + overlay degradado transparente (estilo IPSOS) -->
    <section class="page-hero-cover" style="--bg:url('<?= e($coverUrl) ?>');">
      <div class="phc-bg" aria-hidden="true" style="background-image:url('<?= e($coverUrl) ?>');"></div>
      <div class="phc-overlay" aria-hidden="true"></div>
      <div class="container phc-content">
        <nav aria-label="breadcrumb" class="mb-3">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($page['title'] ?? 'Página') ?></li>
          </ol>
        </nav>
        <h1 class="page-title text-white"><?= e($page['title'] ?? '') ?></h1>
        <?php if (!empty($page['meta_desc'])): ?>
          <p class="phc-subtitle"><?= e($page['meta_desc']) ?></p>
        <?php endif; ?>
      </div>
    </section>
  <?php else: ?>
    <section class="page-head page-head-light">
      <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($page['title'] ?? 'Página') ?></li>
          </ol>
        </nav>
        <h1 class="page-title"><?= e($page['title'] ?? '') ?></h1>
      </div>
    </section>
  <?php endif; ?>

  <section class="section section-white">
    <div class="container">
      <?php include __DIR__ . '/../../partials/flash.php'; ?>

      <div class="row justify-center">
        <div class="col-lg-10 col-12">
          <article class="card card-paper rich-text">
            <div class="card-body">
              <?php if (empty($page['content_html'])): ?>
                <p class="muted mb-0">Contenido pendiente de edición.</p>
              <?php else: ?>
                <?= $page['content_html'] ?>
              <?php endif; ?>
            </div>
          </article>

          <?php if (auth_check() && (auth_is('admin') || auth_is('editor'))): ?>
            <div class="mt-3 text-end">
              <a class="btn btn-outline-primary btn-sm" href="<?= url('/admin/paginas/'.((int)($page['id']??0)).'/editar') ?>">✏️ Editar esta página</a>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </section>
</main>

<?php include __DIR__ . '/../../partials/public_footer.php'; ?>
