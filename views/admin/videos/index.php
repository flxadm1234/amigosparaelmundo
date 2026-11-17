<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<?php
if (!function_exists('admin_icon')) {
    function admin_icon(string $k = 'video', int $size = 18, float $sw = 1.75): string {
        $stroke = htmlspecialchars('currentColor', ENT_QUOTES);
        $s = (int)$size;
        $w = number_format($sw, 2, '.', '');
        $base = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$s}\" height=\"{$s}\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"{$stroke}\" stroke-width=\"{$w}\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\">";
        $map = [
            'video'         => '<path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/>',
            'plus'          => '<path d="M5 12h14"/><path d="M12 5v14"/>',
            'globe-2'       => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
            'search'        => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'filter-x'      => '<path d="M13.013 3H2l8 9.46V19l4 2v-8.54l.9-1.055"/><path d="m22 3-5 5"/><path d="m17 3 5 5"/>',
            'list'          => '<line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/>',
            'pencil'        => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
            'toggle-right'  => '<rect x="1" y="5" width="18" height="14" rx="7" ry="7"/><circle cx="16" cy="12" r="3"/>',
            'toggle-left'   => '<rect x="1" y="5" width="18" height="14" rx="7" ry="7"/><circle cx="8" cy="12" r="3"/>',
            'eye'           => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
            'trash-2'       => '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/>',
            'image'         => '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
            'inbox'         => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z"/>',
            'youtube'       => '<path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>',
            'plug'          => '<path d="M9 2v6"/><path d="M15 2v6"/><path d="M6 8h12v4a6 6 0 1 1-12 0Z"/><path d="M12 18v4"/>',
            'upload'        => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>',
            'clock'         => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        ];
        return $base . ($map[$k] ?? ($map['video'] ?? '')) . '</svg>';
    }
}

$filters = $filters ?? ['q' => '', 'video_type' => '', 'section_id' => 0, 'is_published' => '', 'order' => 'new'];
$filters['q'] = $filters['q'] ?? '';
$filters['video_type'] = $filters['video_type'] ?? '';
$filters['section_id'] = (int)($filters['section_id'] ?? 0);
$filters['is_published'] = $filters['is_published'] ?? '';
$filters['order'] = $filters['order'] ?? 'new';

$paginator = $results ?? ($paginator ?? ['items' => [], 'total' => 0, 'from' => 0, 'to' => 0, 'total_pages' => 1, 'page' => 1, 'per_page' => 20]);
$secciones = $sections ?? (class_exists('App\Models\Section') ? \App\Models\Section::activeOrdered() : []);
unset($secVideos);
?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div class="d-flex align-items-start gap-3">
      <div style="width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:rgba(239,68,68,.12);color:#F87171;flex-shrink:0;">
        <?= admin_icon('video', 22, 2) ?>
      </div>
      <div>
        <h1 class="h-title mb-1">Videos</h1>
        <p class="muted mb-0">Galería de videos: embebidos, YouTube o subidos al servidor.</p>
      </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a class="rm-btn rm-btn-primary d-inline-flex align-items-center gap-2" href="<?= e(url('/admin/videos/crear')) ?>">
        <?= admin_icon('plus', 15, 2.2) ?>
        <span>Crear video</span>
      </a>
      <a class="rm-btn rm-btn-secondary d-none d-sm-inline-flex align-items-center gap-2" href="<?= e(url('/videos')) ?>" target="_blank" rel="noopener">
        <?= admin_icon('globe-2', 15, 1.9) ?>
        <span>Ver en sitio</span>
      </a>
    </div>
  </div>
</div>

<form method="get" action="<?= e(url('/admin/videos')) ?>" class="card card-sm mb-3 filters-card">
  <div class="card-body p-3">
    <div class="d-flex flex-wrap align-items-end gap-3 admin-filters-row">

      <div class="flex-grow-1" style="min-width:220px;max-width:320px;">
        <label class="form-label mb-1 muted-sm" for="vf-q">Buscar título</label>
        <div class="input-group" style="position:relative;">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--rm-admin-text-4);z-index:2;pointer-events:none;"><?= admin_icon('search', 15, 2) ?></span>
          <input type="search" name="q" id="vf-q" class="form-control form-control-sm" placeholder="Buscar por título, slug o descripción…" value="<?= e($filters['q']) ?>" style="padding-left:38px;border-radius:9999px;">
        </div>
      </div>

      <div style="min-width:140px;flex-shrink:0;">
        <label class="form-label mb-1 muted-sm" for="vf-type">Tipo</label>
        <select name="video_type" id="vf-type" class="form-select form-select-sm">
          <option value="">Todos los tipos</option>
          <option value="embed"   <?= $filters['video_type'] === 'embed'   ? 'selected' : '' ?>>Embebido (iframe)</option>
          <option value="youtube" <?= $filters['video_type'] === 'youtube' ? 'selected' : '' ?>>YouTube</option>
          <option value="upload"  <?= $filters['video_type'] === 'upload'  ? 'selected' : '' ?>>Subido (MP4)</option>
        </select>
      </div>

      <div style="min-width:150px;flex-shrink:0;">
        <label class="form-label mb-1 muted-sm" for="vf-section">Sección</label>
        <select name="section_id" id="vf-section" class="form-select form-select-sm">
          <option value="0">Todas las secciones</option>
          <?php foreach ($secciones as $s): ?>
            <?php $st = $s['section_type'] ?? ''; $ok = $st === '' || $st === 'videos' || $st === 'news'; if (!$ok) continue; ?>
            <option value="<?= (int)$s['id'] ?>" <?= (int)$filters['section_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="min-width:140px;flex-shrink:0;">
        <label class="form-label mb-1 muted-sm" for="vf-state">Estado</label>
        <select name="is_published" id="vf-state" class="form-select form-select-sm">
          <option value="">Todos (estado)</option>
          <option value="1" <?= $filters['is_published'] === '1' ? 'selected' : '' ?>>Publicados</option>
          <option value="0" <?= $filters['is_published'] === '0' ? 'selected' : '' ?>>Borradores</option>
        </select>
      </div>

      <div style="min-width:140px;flex-shrink:0;">
        <label class="form-label mb-1 muted-sm" for="vf-order">Orden</label>
        <select name="order" id="vf-order" class="form-select form-select-sm">
          <option value="new" <?= $filters['order'] === 'new' ? 'selected' : '' ?>>Más recientes</option>
          <option value="old" <?= $filters['order'] === 'old' ? 'selected' : '' ?>>Más antiguas</option>
        </select>
      </div>

      <div class="d-flex gap-2 align-items-end ms-auto" style="padding-bottom:1px;">
        <button type="submit" class="rm-btn rm-btn-primary rm-btn-sm d-inline-flex align-items-center gap-2">
          <?= admin_icon('search', 14, 2) ?>
          <span class="d-none d-sm-inline">Aplicar</span>
        </button>
        <a class="rm-btn rm-btn-secondary rm-btn-sm d-inline-flex align-items-center gap-2" href="<?= e(url('/admin/videos')) ?>">
          <?= admin_icon('filter-x', 14, 2) ?>
          <span class="d-none d-sm-inline">Limpiar</span>
        </a>
      </div>

    </div>

    <div class="d-flex gap-2 justify-between flex-wrap mt-3 pt-2 align-items-center" style="border-top:1px dashed var(--rm-admin-border);">
      <span class="muted-sm" style="font-size:0.8rem;">
        Mostrando
        <strong style="color:var(--rm-admin-text-1);"><?= (int)($paginator['from'] ?? 0) ?>–<?= (int)($paginator['to'] ?? 0) ?></strong>
        de
        <strong style="color:var(--rm-admin-text-1);"><?= (int)($paginator['total'] ?? 0) ?></strong>
        videos
      </span>
      <div class="d-flex align-items-center gap-2">
        <span class="muted-sm" style="font-size:0.75rem;">
          Página
          <strong style="color:var(--rm-admin-text-1);"><?= (int)($paginator['page'] ?? 1) ?></strong>
          / <?= (int)($paginator['total_pages'] ?? 1) ?>
        </span>
      </div>
    </div>
  </div>
</form>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($paginator['items'])): ?>
      <div class="list-empty p-6 text-center">
        <div style="width:72px;height:72px;margin:0 auto 14px;color:var(--rm-admin-text-4);opacity:0.55;"><?= admin_icon('inbox', 72, 1.3) ?></div>
        <h3 class="h5 mb-1" style="color:var(--rm-admin-text-1);">No hay videos que coincidan</h3>
        <p class="muted mb-4">Prueba ajustando los filtros o crea un nuevo video desde YouTube, embebido o subido.</p>
        <div class="d-flex gap-2 justify-center flex-wrap">
          <a class="rm-btn rm-btn-primary rm-btn-sm d-inline-flex align-items-center gap-2" href="<?= e(url('/admin/videos/crear')) ?>">
            <?= admin_icon('plus', 14, 2.2) ?>
            <span>Crear video</span>
          </a>
          <a class="rm-btn rm-btn-secondary rm-btn-sm" href="<?= e(url('/admin/videos')) ?>">Quitar filtros</a>
        </div>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table table-hover mb-0 admin-table align-middle">
          <thead>
            <tr>
              <th style="width:140px;">Thumbnail</th>
              <th>Título</th>
              <th style="width:120px;">Tipo</th>
              <th style="width:140px;">Sección</th>
              <th style="width:100px;">Duración</th>
              <th style="width:110px;">Estado</th>
              <th style="width:85px;">Views</th>
              <th style="width:130px;">Publicación</th>
              <th style="width:170px;" class="text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($paginator['items'] as $v):
                $id       = (int)($v['id'] ?? 0);
                $title    = (string)($v['title'] ?? '');
                $slug     = (string)($v['slug'] ?? '');
                $section  = (string)($v['section_name'] ?? '—');
                $vt       = (string)($v['video_type'] ?? 'embed');
                $duration = (string)($v['duration'] ?? '00:00');
                $isPub    = !empty($v['is_published']);
                $views    = (int)($v['views_count'] ?? 0);
                $pubDate  = !empty($v['publish_date']) ? format_date($v['publish_date']) : '—';
                $typeMap = [
                    'embed'   => ['label' => 'Embebido',  'badge' => 'badge-info',     'icon' => 'plug'],
                    'youtube' => ['label' => 'YouTube',   'badge' => 'badge-danger',   'icon' => 'youtube'],
                    'upload'  => ['label' => 'MP4 local', 'badge' => 'badge-success',  'icon' => 'upload'],
                ];
                $tl = $typeMap[$vt] ?? ['label' => $vt, 'badge' => 'badge-secondary', 'icon' => 'video'];
            ?>
              <tr>
                <td>
                  <?php $thumbSrcIndex = img_fallback($v['thumbnail_path'] ?? $v['cover_image_path'] ?? '', '16x9', $title, 'video-thumb-'.$id); ?>
                  <div class="dash-video-thumb" style="width:128px;height:74px;border-radius:8px;overflow:hidden;position:relative;display:inline-flex;align-items:center;justify-content:center;background:#087B43;border:1px solid var(--rm-admin-border);">
                    <img src="<?= e($thumbSrcIndex) ?>"
                         alt="Thumbnail: <?= e(mb_substr($title, 0, 60)) ?>"
                         loading="lazy"
                         decoding="async"
                         style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;background:#087B43;"
                         onload="try{var ph=this.parentNode && this.parentNode.querySelector('.video-thumb-ph');if(ph){ph.style.display='none';}}catch(e){}">
                    <span class="video-thumb-ph" style="width:100%;height:100%;align-items:center;justify-content:center;background:#087B43;color:#FFFFFF;display:inline-flex;">
                      <?= admin_icon('image', 26, 1.6) ?>
                    </span>
                  </div>
                </td>
                <td>
                  <a href="<?= e(url('/admin/videos/'.$id.'/editar')) ?>" class="admin-strong-link" style="display:block;font-weight:600;color:var(--rm-admin-text-1);font-size:0.93rem;line-height:1.3;">
                    <?= e(mb_substr($title, 0, 90)) ?><?= mb_strlen($title) > 90 ? '…' : '' ?>
                  </a>
                  <?php if (!empty($v['description'])): ?>
                    <?php $dsc = trim(preg_replace('/\s+/', ' ', (string)strip_tags($v['description']))); ?>
                    <div class="muted-sm" style="font-size:.78rem;line-height:1.25;margin-top:4px;"><?= e(mb_substr($dsc, 0, 110)) ?><?= mb_strlen($dsc) > 110 ? '…' : '' ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= $tl['badge'] ?> d-inline-flex align-items-center gap-1.5" style="gap:6px;">
                    <span style="display:inline-flex;"><?= admin_icon($tl['icon'], 13, 2) ?></span>
                    <span><?= e($tl['label']) ?></span>
                  </span>
                </td>
                <td><?= e($section) ?></td>
                <td><span class="badge badge-secondary d-inline-flex align-items-center gap-1.5" style="gap:6px;font-variant-numeric:tabular-nums;">
                  <?= admin_icon('clock', 12, 2) ?><span><?= e($duration) ?></span>
                </span></td>
                <td>
                  <?php if ($isPub): ?>
                    <span class="badge badge-success">Publicado</span>
                  <?php else: ?>
                    <span class="badge badge-warning">Borrador</span>
                  <?php endif; ?>
                </td>
                <td><span class="badge badge-secondary" style="font-variant-numeric:tabular-nums;"><?= $views ?></span></td>
                <td class="muted-sm"><?= e($pubDate) ?></td>
                <td>
                  <div class="row-actions d-flex align-items-center justify-content-end gap-2">
                    <a class="btn-icon" href="<?= e(url('/admin/videos/'.$id.'/editar')) ?>" title="Editar video #<?= $id ?>" aria-label="Editar">
                      <?= admin_icon('pencil', 15, 1.9) ?>
                    </a>
                    <a class="btn-icon"
                       href="<?= e(url('/admin/videos/'.$id.'/toggle')) ?>"
                       onclick="return confirm('¿Cambiar estado de publicación del video #<?= $id ?>?')"
                       title="<?= $isPub ? 'Despublicar' : 'Publicar' ?>"
                       aria-label="<?= $isPub ? 'Despublicar' : 'Publicar' ?>">
                      <?= $isPub ? admin_icon('toggle-right', 16, 2) : admin_icon('toggle-left', 16, 2) ?>
                    </a>
                    <?php if (!empty($slug)): ?>
                      <a class="btn-icon" href="<?= e(url('/video/'.$slug)) ?>" target="_blank" rel="noopener" title="Ver en sitio" aria-label="Ver en sitio">
                        <?= admin_icon('eye', 15, 1.9) ?>
                      </a>
                    <?php else: ?>
                      <span class="btn-icon" style="opacity:.35;cursor:not-allowed;pointer-events:none;"><?= admin_icon('eye', 15, 1.9) ?></span>
                    <?php endif; ?>
                    <form method="post" action="<?= e(url('/admin/videos/'.$id.'/eliminar')) ?>"
                          onsubmit="return confirm('¿Eliminar PERMANENTEMENTE este video?')"
                          style="display:inline-flex;margin:0;padding:0;border:0;background:transparent;">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn-icon" title="Eliminar video #<?= $id ?>" aria-label="Eliminar">
                        <?= admin_icon('trash-2', 15, 1.9) ?>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <?php if (!empty($paginator) && (int)($paginator['total_pages'] ?? 1) > 1): ?>
    <div class="card-footer d-flex justify-content-center">
      <?= function_exists('pagination_links') ? pagination_links($paginator) : '' ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
