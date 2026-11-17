<?php include __DIR__ . '/../../partials/public_head.php'; ?>
<?php include __DIR__ . '/../../partials/public_navbar.php'; ?>

<?php
  $pubId = (int)($pub['id'] ?? 0);
  $title = $pub['title'] ?? '';
  $categoryName = $pub['category_name'] ?? \App\Models\Category::nameFor((int)($pub['category_id'] ?? 0));
  $regionName   = $pub['region_name']   ?? \App\Models\Region::nameFor((int)($pub['region_id'] ?? 0));

  // ========= CONSTRUIR MEDIA IMÁGENES PARA HERO GRID =========
  $mediaImages = [];
  // 1) Portada dedicada (cover_image_path) — prioridad alta
  if (!empty($pub['cover_image_path'])) {
      $coverSrc = upload_url($pub['cover_image_path']);
      if (!empty($coverSrc)) {
          $mediaImages[] = [
              'src'      => $coverSrc,
              'alt'      => 'Portada: ' . $title,
              'caption'  => 'Portada del estudio',
              'fallback' => null,
          ];
      }
  }
  // 2) Galería de imágenes (file_kind = gallery)
  if (!empty($gallery)) {
      foreach ($gallery as $g) {
          if (!file_is_image($g)) continue;
          $idMedia = (int)($g['id'] ?? 0);
          $storedPath = $g['stored_path'] ?? '';
          $src  = !empty($storedPath) ? upload_url($storedPath) : '';
          $fallback = $idMedia > 0 ? url('/media/' . $idMedia . '/view') : '';
          $orig = $g['original_name'] ?? 'Imagen del estudio';
          $mediaImages[] = [
              'src'      => $src,
              'alt'      => $orig,
              'caption'  => $orig,
              'fallback' => $fallback,
          ];
      }
  }
  $hasMediaImages = !empty($mediaImages);
  $mainImage = $hasMediaImages ? $mediaImages[0] : null;
  $placeholderMain = placeholder_svg(1600, 900, 'Amigos por el Mundo · Noticia Destacada', null, '#ffffff');
?>

<main>
  <section class="page-head page-head-gradient">
    <div class="container">
      <div class="row">
        <div class="col-lg-9">
          <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="<?= url('/') ?>">Inicio</a></li>
              <li class="breadcrumb-item"><a href="<?= url('/publicaciones') ?>">Publicaciones</a></li>
              <?php if (!empty($categoryName)): ?>
                <li class="breadcrumb-item"><a href="<?= e(current_url_with_query(['page'=>1,'q'=>'','category_id'=>(int)($pub['category_id']??0), 'region_id'=>0, 'tag_id'=>0,'year'=>0], ['category_id'])) ?>"><?= e($categoryName) ?></a></li>
              <?php endif; ?>
              <li class="breadcrumb-item active" aria-current="page"><?= e(mb_substr($title, 0, 40)) ?><?= mb_strlen($title) > 40 ? '…' : '' ?></li>
            </ol>
          </nav>
          <div class="mb-3 d-flex flex-wrap gap-2">
            <?php if (!empty($categoryName)): ?>
              <span class="badge badge-primary"><?= e($categoryName) ?></span>
            <?php endif; ?>
            <?php if (!empty($pub['year'])): ?>
              <span class="badge badge-secondary">Año <?= (int)$pub['year'] ?></span>
            <?php endif; ?>
            <?php if (!empty($regionName)): ?>
              <span class="badge badge-accent">📍 <?= e($regionName) ?></span>
            <?php endif; ?>
            <?php if (!empty($pub['is_published'])): ?>
              <span class="badge badge-success">Publicado</span>
            <?php else: ?>
              <span class="badge badge-warning">Borrador</span>
            <?php endif; ?>
          </div>
          <h1 class="page-title mb-3"><?= e($title) ?></h1>
          <div class="pub-detail-meta d-flex flex-wrap gap-3 mb-0">
            <?php if (!empty($pub['publish_date'])): ?>
              <span>📅 Publicado: <strong><?= e(format_date_spanish($pub['publish_date'])) ?></strong></span>
            <?php endif; ?>
            <?php if (!empty($pub['fieldwork_start_date']) || !empty($pub['fieldwork_end_date'])): ?>
              <span>🗓️ Trabajo de campo:
                <strong>
                  <?= e(format_date($pub['fieldwork_start_date'])) ?>
                  <?php if (!empty($pub['fieldwork_end_date']) && $pub['fieldwork_end_date'] !== $pub['fieldwork_start_date']): ?>
                    al <?= e(format_date($pub['fieldwork_end_date'])) ?>
                  <?php endif; ?>
                </strong>
              </span>
            <?php endif; ?>
            <?php if (auth_check() && (auth_is('admin') || auth_is('editor'))): ?>
              <span><a href="<?= url('/admin/publicaciones/'.$pubId.'/editar') ?>" class="btn btn-sm btn-outline-primary">✏️ Editar en admin</a></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section section-white pub-detail">
    <div class="container">
      <?php include __DIR__ . '/../../partials/flash.php'; ?>

      <div class="row g-4">
        <!-- CONTENIDO -->
        <div class="col-lg-8 col-12">

          <!-- ========== HERO MEDIA GRID: Imagen grande + miniaturas vertical (PROFESIONAL) ========== -->
          <div class="pub-media-wrapper mb-4">
            <div class="pub-media-grid">
              <!-- IMAGEN PRINCIPAL GRANDE -->
              <div class="pmg-main">
                <div class="pmg-img-box" id="pmgImgBox"
                     data-main-src="<?= e($hasMediaImages ? $mainImage['src'] : $placeholderMain) ?>"
                     data-main-alt="<?= e($hasMediaImages ? $mainImage['alt'] : $title) ?>"
                     data-main-fb="<?= e($hasMediaImages ? ($mainImage['fallback'] ?? '') : '') ?>"
                     data-main-caption="<?= e($hasMediaImages ? $mainImage['caption'] : 'Portada') ?>">
                  <img id="pmgMainImg"
                       src="<?= e($hasMediaImages ? $mainImage['src'] : $placeholderMain) ?>"
                       alt="<?= e($hasMediaImages ? $mainImage['alt'] : $title) ?>"
                       loading="eager"
                       onerror="this.onerror=null;var fb=this.closest('.pmg-img-box').dataset.mainFb;if(fb){this.src=fb;}">
                  <div class="pmg-zoom-hint">
                    <span class="pmg-zl">🔍</span>
                    <span class="pmg-zt">Clic para ampliar · Zoom</span>
                  </div>
                </div>
                <?php if ($hasMediaImages): ?>
                  <div class="pmg-caption">
                    <span class="muted-sm" id="pmgCaptionText"><?= e($mainImage['caption'] ?? '') ?></span>
                    <span class="muted-sm pmg-count">Imagen <strong id="pmgIdx">1</strong> de <strong id="pmgTotal"><?= count($mediaImages) ?></strong></span>
                  </div>
                <?php endif; ?>
              </div>
              <!-- MINIATURAS VERTICAL (lado derecho) -->
              <?php if ($hasMediaImages && count($mediaImages) > 1): ?>
                <div class="pmg-thumbs">
                  <?php foreach ($mediaImages as $idx => $mi): ?>
                    <button type="button"
                            class="pmg-thumb <?= $idx === 0 ? 'active' : '' ?>"
                            data-idx="<?= $idx ?>"
                            data-src="<?= e($mi['src']) ?>"
                            data-alt="<?= e($mi['alt']) ?>"
                            data-fb="<?= e($mi['fallback'] ?? '') ?>"
                            data-caption="<?= e($mi['caption'] ?? '') ?>"
                            aria-label="Imagen <?= $idx + 1 ?>">
                      <img src="<?= e($mi['src']) ?>"
                           alt="<?= e($mi['alt']) ?>"
                           loading="lazy"
                           onerror="this.onerror=null;var fb=this.closest('.pmg-thumb').dataset.fb;if(fb){this.src=fb;}">
                    </button>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Resumen ejecutivo -->
          <?php if (!empty($pub['excerpt'])): ?>
            <div class="pub-excerpt-box mb-4">
              <strong class="muted d-block mb-1">Resumen ejecutivo</strong>
              <p class="lead mb-0"><?= e($pub['excerpt']) ?></p>
            </div>
          <?php endif; ?>

          <!-- Contenido WYSIWYG -->
          <?php if (!empty($pub['content_html'])): ?>
            <div class="pub-content rich-text">
              <!-- El HTML fue saneado al guardarse; confiamos en el admin. -->
              <?= $pub['content_html'] ?>
            </div>
          <?php endif; ?>

          <!-- Etiquetas -->
          <?php if (!empty($tags)): ?>
            <div class="mt-5">
              <h2 class="section-title-sm">🏷️ Etiquetas</h2>
              <div class="pub-tags">
                <?php foreach ($tags as $t): ?>
                  <a class="tag tag-link" href="<?= e(current_url_with_query(['tag_id' => (int)$t['id'], 'page' => 1], ['page', 'tag_id'])) ?>">#<?= e($t['name']) ?></a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

        </div>

        <!-- LATERAL DERECHO -->
        <aside class="col-lg-4 col-12">
          <div class="sticky-top" style="top:1rem">
            <!-- ===== ARCHIVOS PARA DESCARGAR (DISEÑO PROFESIONAL) ===== -->
            <div class="card card-hover card-shadow mb-4">
              <div class="card-body">
                <h3 class="card-title-sm mb-3">📥 Archivos para descargar</h3>
                <?php if (empty($attachments)): ?>
                  <div class="att-empty">
                    <div class="att-empty-ic">📭</div>
                    <p class="muted mb-0">Aún no hay archivos adjuntos publicados para este estudio.</p>
                  </div>
                <?php else: ?>
                  <div class="att-list">
                    <?php
                      $attCount = count($attachments);
                      foreach ($attachments as $attIdx => $att):
                        $fm = file_type_meta($att['stored_name'] ?? '.pdf', $att['mime_type'] ?? '');
                        $size = (int)($att['size_bytes'] ?? 0);
                        $ext = strtolower(pathinfo($att['stored_name'] ?? '.pdf', PATHINFO_EXTENSION));
                        $viewUrl = url('/media/' . (int)($att['id'] ?? 0) . '/view');
                        $dlUrl   = url('/media/' . (int)($att['id'] ?? 0) . '/download');
                        $orig = $att['original_name'] ?? 'Archivo';
                        $isLast = ($attIdx === $attCount - 1);
                        // Icon classes type (for background tint)
                        $iconTintClass = in_array($ext, ['pdf'], true) ? 'att-icon-pdf'
                            : (in_array($ext, ['xlsx','xls','csv'], true) ? 'att-icon-excel'
                            : (in_array($ext, ['sav'], true) ? 'att-icon-spss'
                            : (in_array($ext, ['doc','docx'], true) ? 'att-icon-word'
                            : (in_array($ext, ['zip','rar'], true) ? 'att-icon-zip'
                            : 'att-icon-default'))));
                        // Short badge (3-4 letters) inside icon
                        $iconBadge = in_array($ext, ['pdf','xlsx','xls','sav','csv','zip','docx','doc'], true)
                            ? strtoupper(str_replace(['xlsx','docx'], ['XLS','DOC'], $ext))
                            : $fm['label'];
                    ?>
                      <div class="att-row <?= $isLast ? '' : 'att-row-sep' ?>">
                        <div class="att-icon <?= $iconTintClass ?>">
                          <span class="att-icon-badge"><?= e($iconBadge) ?></span>
                        </div>
                        <div class="att-info" title="<?= e($orig) ?>">
                          <div class="att-name"><?= e($orig) ?></div>
                          <div class="att-meta">
                            <span class="chip chip-type chip-type-<?= $ext ?>"><?= e($fm['label']) ?></span>
                            <?php if ($size > 0): ?><span class="muted-sm">· <?= e(format_bytes($size)) ?></span><?php endif; ?>
                          </div>
                        </div>
                        <div class="att-actions">
                          <a class="btn btn-xs btn-outline-primary att-btn"
                             href="<?= e($viewUrl) ?>" target="_blank" rel="noopener">
                            <span class="ab-ic">👁️</span>
                            <span class="ab-tx">Ver</span>
                          </a>
                          <a class="btn btn-xs btn-primary att-btn"
                             href="<?= e($dlUrl) ?>">
                            <span class="ab-ic">⬇️</span>
                            <span class="ab-tx">Descargar</span>
                          </a>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="card card-accent mb-4">
              <div class="card-body">
                <h3 class="card-title-sm">💬 ¿Dudas sobre este estudio?</h3>
                <p class="mb-3">Nuestro equipo comercial responde tus consultas y cotizaciones.</p>
                <a class="btn btn-light btn-block" href="<?= url('/contacto') ?>">📞 Contáctanos</a>
              </div>
            </div>

            <div class="card mb-4">
              <div class="card-body">
                <h3 class="card-title-sm">🔗 Compartir</h3>
                <div class="d-flex gap-2 flex-wrap">
                  <button type="button" class="btn btn-outline-primary btn-sm share-btn" data-share="copy">🔗 Copiar enlace</button>
                  <a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"
                     href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('/publicaciones/'.($pub['slug']??''))) ?>">f Facebook</a>
                  <a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"
                     href="https://twitter.com/intent/tweet?url=<?= urlencode(url('/publicaciones/'.($pub['slug']??''))) ?>&text=<?= urlencode($title) ?>">𝕏 Twitter</a>
                  <a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"
                     href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(url('/publicaciones/'.($pub['slug']??''))) ?>">in LinkedIn</a>
                </div>
              </div>
            </div>
          </div>
        </aside>
      </div>

      <!-- RELACIONADOS -->
      <?php if (!empty($related)): ?>
        <div class="mt-6">
          <h2 class="section-title">📚 Publicaciones relacionadas</h2>
          <div class="row g-4">
            <?php foreach (array_slice($related, 0, 4) as $r): ?>
              <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <article class="card pub-card h-100">
                  <div class="pub-card-media">
                    <img src="<?= e(placeholder_svg(520, 280, ($r['category_name'] ?? 'Estudio').' · '.((int)($r['year']??0)))) ?>"
                         alt="" class="pub-cover" loading="lazy">
                    <div class="pub-card-badges">
                      <?php if (!empty($r['category_name'])): ?><span class="badge badge-primary"><?= e($r['category_name']) ?></span><?php endif; ?>
                      <?php if (!empty($r['year'])): ?><span class="badge badge-secondary"><?= (int)$r['year'] ?></span><?php endif; ?>
                    </div>
                  </div>
                  <div class="card-body">
                    <h4 class="pub-title-sm"><a href="<?= url('/publicaciones/'.($r['slug']??'')) ?>"><?= e($r['title']) ?></a></h4>
                    <div class="muted-sm">📅 <?= e(format_date($r['publish_date'] ?? '')) ?></div>
                  </div>
                </article>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </section>
</main>

<!-- ============== LIGHTBOX MODAL ZOOM PROFESIONAL ============== -->
<div class="ps-lightbox" id="psLightbox" role="dialog" aria-modal="true" aria-label="Visor de imágenes" hidden>
  <!-- Overlay fondo oscuro -->
  <div class="ps-lb-overlay" data-action="close"></div>
  <!-- Controles superiores -->
  <div class="ps-lb-topbar">
    <div class="ps-lb-info">
      <div class="ps-lb-filename" id="psLbFilename">—</div>
      <div class="ps-lb-counter muted-sm"><span id="psLbIdx">1</span> / <span id="psLbTotal">0</span></div>
    </div>
    <div class="ps-lb-ctrls">
      <button type="button" class="ps-lb-btn" data-action="zoom-out" title="Reducir (–)">🔍 −</button>
      <button type="button" class="ps-lb-btn" data-action="zoom-reset" title="Tamaño real (0)">⟲ 100%</button>
      <button type="button" class="ps-lb-btn" data-action="zoom-in" title="Ampliar (+)">🔍 +</button>
      <button type="button" class="ps-lb-btn ps-lb-btn-close" data-action="close" title="Cerrar (Esc)">✕</button>
    </div>
  </div>
  <!-- Contenedor imagen (pan/zoom) -->
  <button type="button" class="ps-lb-nav ps-lb-nav-prev" data-action="prev" aria-label="Anterior (←)">‹</button>
  <button type="button" class="ps-lb-nav ps-lb-nav-next" data-action="next" aria-label="Siguiente (→)">›</button>
  <div class="ps-lb-stage" id="psLbStage">
    <div class="ps-lb-img-wrap" id="psLbImgWrap">
      <img id="psLbImg" alt="" draggable="false">
    </div>
  </div>
</div>

<script>
(function(){
  'use strict';
  // ===================== 1) MEDIA: Thumbnails <-> Imagen principal =====================
  var $mainImg = document.getElementById('pmgMainImg');
  var $imgBox  = document.getElementById('pmgImgBox');
  var $capText = document.getElementById('pmgCaptionText');
  var $idxEl   = document.getElementById('pmgIdx');
  var $totalEl = document.getElementById('pmgTotal');
  var $thumbs  = document.querySelectorAll('.pmg-thumb');
  var mediaList = [];
  $thumbs.forEach(function(btn){
    mediaList.push({
      src:     btn.dataset.src,
      alt:     btn.dataset.alt,
      fb:      btn.dataset.fb || '',
      caption: btn.dataset.caption || '',
      idx:     parseInt(btn.dataset.idx, 10) || 0
    });
    btn.addEventListener('click', function(){
      selectThumb(parseInt(btn.dataset.idx, 10) || 0);
    });
  });
  // Fallback: si no hubo thumbs pero sí media (solo 1 imagen), generar mediaList de dataset box
  if (mediaList.length === 0 && $imgBox && $imgBox.dataset.mainSrc) {
    mediaList.push({
      src:     $imgBox.dataset.mainSrc,
      alt:     $imgBox.dataset.mainAlt,
      fb:      $imgBox.dataset.mainFb,
      caption: $imgBox.dataset.mainCaption,
      idx:     0
    });
  }
  function selectThumb(i) {
    var it = mediaList[i];
    if (!it || !$mainImg) return;
    $mainImg.src = it.src;
    $mainImg.alt = it.alt || '';
    if ($imgBox && it.fb) {
      $imgBox.dataset.mainFb = it.fb;
    }
    if ($capText) $capText.textContent = it.caption || '';
    if ($idxEl)   $idxEl.textContent = String(i + 1);
    if ($totalEl) $totalEl.textContent = String(mediaList.length);
    $thumbs.forEach(function(b, j){
      b.classList.toggle('active', j === i);
    });
    currentImageIdx = i;
  }

  // ===================== 2) LIGHTBOX + ZOOM =====================
  var currentImageIdx = 0;
  var scale = 1;
  var SCALE_MIN = 1;
  var SCALE_MAX = 4;
  var SCALE_STEP = 0.25;
  var panX = 0, panY = 0;
  var dragging = false;
  var dragStartX = 0, dragStartY = 0;
  var dragStartPanX = 0, dragStartPanY = 0;

  var $lb       = document.getElementById('psLightbox');
  var $lbImg    = document.getElementById('psLbImg');
  var $lbWrap   = document.getElementById('psLbImgWrap');
  var $lbStage  = document.getElementById('psLbStage');
  var $lbIdx    = document.getElementById('psLbIdx');
  var $lbTotal  = document.getElementById('psLbTotal');
  var $lbFile   = document.getElementById('psLbFilename');

  function openLb(i) {
    if (mediaList.length === 0) return;
    if (typeof i !== 'number') i = currentImageIdx || 0;
    currentImageIdx = Math.max(0, Math.min(mediaList.length - 1, i));
    var it = mediaList[currentImageIdx];
    if (!$lb || !$lbImg) return;
    $lbImg.src = it.src;
    $lbImg.alt = it.alt || '';
    $lbImg.onerror = function(){ if (it.fb) { $lbImg.src = it.fb; } };
    if ($lbIdx)   $lbIdx.textContent   = String(currentImageIdx + 1);
    if ($lbTotal) $lbTotal.textContent = String(mediaList.length);
    if ($lbFile)  $lbFile.textContent  = (it.caption || it.alt || 'Imagen');
    // Reset zoom/pan
    scale = 1; panX = 0; panY = 0;
    applyTransform();
    $lb.hidden = false;
    document.body.style.overflow = 'hidden';
  }
  function closeLb() {
    if (!$lb) return;
    $lb.hidden = true;
    document.body.style.overflow = '';
    scale = 1; panX = 0; panY = 0;
    applyTransform();
  }
  function nextImg() { if (mediaList.length>1) openLb((currentImageIdx + 1) % mediaList.length); }
  function prevImg() { if (mediaList.length>1) openLb((currentImageIdx - 1 + mediaList.length) % mediaList.length); }
  function zoomIn()  { scale = Math.min(SCALE_MAX, +(scale + SCALE_STEP).toFixed(2)); applyTransform(); }
  function zoomOut() { scale = Math.max(SCALE_MIN, +(scale - SCALE_STEP).toFixed(2)); applyTransform(); }
  function zoomReset(){ scale = 1; panX = 0; panY = 0; applyTransform(); }
  function applyTransform() {
    if (!$lbWrap) return;
    $lbWrap.style.transform = 'translate3d('+panX+'px,'+panY+'px,0) scale('+scale+')';
  }

  // Click en imagen principal → abrir lightbox
  if ($imgBox) {
    $imgBox.addEventListener('click', function(){ openLb(currentImageIdx); });
  }

  // Delegación controles lightbox
  if ($lb) {
    $lb.addEventListener('click', function(e){
      var btn = e.target.closest('[data-action]');
      if (!btn) return;
      e.preventDefault();
      var a = btn.dataset.action;
      if (a === 'close')     closeLb();
      if (a === 'zoom-in')   zoomIn();
      if (a === 'zoom-out')  zoomOut();
      if (a === 'zoom-reset')zoomReset();
      if (a === 'prev')      prevImg();
      if (a === 'next')      nextImg();
    });
    // Drag para panear (solo cuando zoom > 1)
    $lbStage.addEventListener('mousedown', function(e){
      if (scale <= 1) return;
      var tgt = e.target.closest('.ps-lb-btn, .ps-lb-nav'); if (tgt) return;
      dragging = true;
      dragStartX = e.clientX; dragStartY = e.clientY;
      dragStartPanX = panX; dragStartPanY = panY;
      $lbStage.style.cursor = 'grabbing';
      e.preventDefault();
    });
    window.addEventListener('mousemove', function(e){
      if (!dragging) return;
      panX = dragStartPanX + (e.clientX - dragStartX);
      panY = dragStartPanY + (e.clientY - dragStartY);
      applyTransform();
    });
    window.addEventListener('mouseup', function(){
      if (dragging) { dragging = false; if ($lbStage) $lbStage.style.cursor = ''; }
    });
    // Zoom con rueda mouse
    $lbStage.addEventListener('wheel', function(e){
      if ($lb.hidden) return;
      e.preventDefault();
      if (e.deltaY < 0) zoomIn(); else zoomOut();
    }, { passive: false });
    // Zoom doble click
    $lbStage.addEventListener('dblclick', function(){
      if ($lb.hidden) return;
      if (scale > 1) zoomReset(); else { scale = 2; applyTransform(); }
    });
  }

  // Teclado global
  document.addEventListener('keydown', function(e){
    if ($lb && !$lb.hidden) {
      if (e.key === 'Escape')  closeLb();
      else if (e.key === 'ArrowLeft')  prevImg();
      else if (e.key === 'ArrowRight') nextImg();
      else if (e.key === '+' || e.key === '=') zoomIn();
      else if (e.key === '-' || e.key === '_') zoomOut();
      else if (e.key === '0') zoomReset();
      return;
    }
    // Compartir enlace
    var b = e.target.closest && e.target.closest('.share-btn[data-share="copy"]');
    if (!b) return;
  });

  // ===================== 3) Compartir: Copiar enlace =====================
  document.addEventListener('click', function(e) {
    var b = e.target.closest('.share-btn[data-share="copy"]');
    if (!b) return;
    e.preventDefault();
    var url = location.href;
    if (navigator.clipboard) {
      navigator.clipboard.writeText(url).then(function() {
        var t = b.textContent; b.textContent = '✓ Enlace copiado!';
        setTimeout(function(){ b.textContent = t; }, 1500);
      });
    }
  });
})();
</script>

<?php include __DIR__ . '/../../partials/public_footer.php'; ?>
