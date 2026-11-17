<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<?php
if (!function_exists('admin_icon')) {
    function admin_icon(string $key = 'newspaper', int $size = 18, float $sw = 1.75): string
    {
        $s = (string)$size;
        $w = number_format($sw, 2, '.', '');
        $common = 'width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $w . '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';
        return match ($key) {
            'newspaper' => '<svg ' . $common . '><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>',
            'plus' => '<svg ' . $common . '><path d="M5 12h14"/><path d="M12 5v14"/></svg>',
            'globe-2' => '<svg ' . $common . '><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>',
            'search' => '<svg ' . $common . '><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>',
            'pencil' => '<svg ' . $common . '><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>',
            'eye' => '<svg ' . $common . '><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>',
            'trash-2' => '<svg ' . $common . '><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>',
            'toggle-right' => '<svg ' . $common . '><rect width="20" height="12" x="2" y="6" rx="6"/><circle cx="16" cy="12" r="2"/></svg>',
            'toggle-left' => '<svg ' . $common . '><rect width="20" height="12" x="2" y="6" rx="6"/><circle cx="8" cy="12" r="2"/></svg>',
            'list' => '<svg ' . $common . '><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>',
            'filter-x' => '<svg ' . $common . '><path d="M13.013 3H2l8 9.46V19l4 2v-8.54L20.24 11.5"/><path d="m22 3-5 5"/><path d="m17 3 5 5"/></svg>',
            'image' => '<svg ' . $common . '><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>',
            'inbox' => '<svg ' . $common . '><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>',
            default => '<svg ' . $common . '><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>',
        };
    }
}

$secciones = class_exists('App\Models\Section') ? \App\Models\Section::allActive() : [];
$filters = $filters ?? ['q' => '', 'is_published' => '', 'section_id' => 0, 'date_from' => '', 'date_to' => '', 'order' => 'new'];
if (!isset($filters['order'])) { $filters['order'] = 'new'; }
$filters['q']          = (string)($filters['q'] ?? '');
$filters['is_published']   = (string)($filters['is_published'] ?? '');
$filters['section_id']     = (int)($filters['section_id'] ?? 0);
$filters['date_from']      = (string)($filters['date_from'] ?? '');
$filters['date_to']        = (string)($filters['date_to'] ?? '');
$paginator = $results ?? ($paginator ?? ['items' => [], 'total' => 0, 'from' => 0, 'to' => 0, 'total_pages' => 1, 'page' => 1, 'per_page' => 20]);
?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div class="d-flex align-items-center gap-3">
      <div style="width:44px;height:44px;border-radius:12px;background:rgba(59,130,246,0.12);color:var(--rm-admin-accent-2);display:inline-flex;align-items:center;justify-content:center;">
        <?= admin_icon('list', 22, 1.9) ?>
      </div>
      <div>
        <h1 class="h-title mb-1">Listado de Noticias</h1>
        <p class="muted mb-0">Gestiona todas las noticias del portal Amigos por el Mundo.</p>
      </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a class="rm-btn rm-btn-primary d-inline-flex align-items-center gap-2" href="<?= e(url('/admin/noticias/crear')) ?>">
        <?= admin_icon('plus', 15, 2.2) ?>
        <span>Crear noticia</span>
      </a>
      <a class="rm-btn rm-btn-secondary d-none d-sm-inline-flex align-items-center gap-2" href="<?= e(url('/noticias')) ?>" target="_blank" rel="noopener">
        <?= admin_icon('globe-2', 15, 1.9) ?>
        <span>Ver en sitio</span>
      </a>
    </div>
  </div>
</div>

<form method="get" action="<?= e(url('/admin/noticias')) ?>" class="card card-sm mb-3 filters-card">
  <div class="card-body p-3">
    <div class="d-flex flex-wrap align-items-end gap-3 admin-filters-row">

      <div class="flex-grow-1" style="min-width:220px;max-width:320px;">
        <label class="form-label mb-1 muted-sm" for="nf-q">Buscar título</label>
        <div class="input-group" style="position:relative;">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--rm-admin-text-4);z-index:2;pointer-events:none;"><?= admin_icon('search', 15, 2) ?></span>
          <input type="search" name="q" id="nf-q" class="form-control form-control-sm" placeholder="Buscar por título, slug o autor…" value="<?= e($filters['q']) ?>" style="padding-left:38px;border-radius:9999px;">
        </div>
      </div>

      <div style="min-width:150px;flex-shrink:0;">
        <label class="form-label mb-1 muted-sm" for="nf-status">Estado</label>
        <select name="is_published" id="nf-status" class="form-select form-select-sm">
          <option value="">Todos los estados</option>
          <option value="1" <?= $filters['is_published'] === '1' ? 'selected' : '' ?>>Publicadas</option>
          <option value="0" <?= $filters['is_published'] === '0' ? 'selected' : '' ?>>Borradores</option>
        </select>
      </div>

      <div style="min-width:160px;flex-shrink:0;">
        <label class="form-label mb-1 muted-sm" for="nf-section">Sección</label>
        <select name="section_id" id="nf-section" class="form-select form-select-sm">
          <option value="0">Todas las secciones</option>
          <?php foreach ($secciones as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= (int)$filters['section_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="min-width:150px;flex-shrink:0;">
        <label class="form-label mb-1 muted-sm" for="nf-from">Fecha desde</label>
        <input type="date" name="date_from" id="nf-from" class="form-control form-control-sm" value="<?= e($filters['date_from']) ?>">
      </div>

      <div style="min-width:150px;flex-shrink:0;">
        <label class="form-label mb-1 muted-sm" for="nf-to">Fecha hasta</label>
        <input type="date" name="date_to" id="nf-to" class="form-control form-control-sm" value="<?= e($filters['date_to']) ?>">
      </div>

      <div style="min-width:150px;flex-shrink:0;">
        <label class="form-label mb-1 muted-sm" for="nf-order">Orden</label>
        <select name="order" id="nf-order" class="form-select form-select-sm">
          <option value="new" <?= $filters['order'] === 'new' ? 'selected' : '' ?>>Más recientes</option>
          <option value="old" <?= $filters['order'] === 'old' ? 'selected' : '' ?>>Más antiguas</option>
        </select>
      </div>

      <div class="d-flex gap-2 align-items-end ms-auto" style="padding-bottom:1px;">
        <button type="submit" class="rm-btn rm-btn-primary rm-btn-sm d-inline-flex align-items-center gap-2">
          <?= admin_icon('search', 14, 2) ?>
          <span class="d-none d-sm-inline">Aplicar</span>
        </button>
        <a class="rm-btn rm-btn-secondary rm-btn-sm d-inline-flex align-items-center gap-2" href="<?= e(url('/admin/noticias')) ?>">
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
        noticias
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
        <h3 class="h5 mb-1" style="color:var(--rm-admin-text-1);">No hay noticias que coincidan</h3>
        <p class="muted mb-4">Prueba ajustando los filtros o crea una nueva noticia.</p>
        <div class="d-flex gap-2 justify-center flex-wrap">
          <a class="rm-btn rm-btn-primary rm-btn-sm d-inline-flex align-items-center gap-2" href="<?= e(url('/admin/noticias/crear')) ?>">
            <?= admin_icon('plus', 14, 2.2) ?>
            <span>Crear noticia</span>
          </a>
          <a class="rm-btn rm-btn-secondary rm-btn-sm" href="<?= e(url('/admin/noticias')) ?>">Quitar filtros</a>
        </div>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table table-hover mb-0 admin-table align-middle">
          <thead>
            <tr>
              <th style="width:64px;">ID</th>
              <th style="width:92px;">Portada</th>
              <th>Título</th>
              <th style="width:130px;">Sección</th>
              <th style="width:110px;">Estado</th>
              <th style="width:100px;">Dest.</th>
              <th style="width:100px;">U.H.</th>
              <th style="width:125px;">Publicación</th>
              <th style="width:80px;">Views</th>
              <th style="width:160px;" class="text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($paginator['items'] as $n):
                $id       = (int)($n['id'] ?? 0);
                $title    = (string)($n['title'] ?? '');
                $slug     = (string)($n['slug'] ?? '');
                $section  = (string)($n['section_name'] ?? '—');
                $isPub    = !empty($n['is_published']);
                $isFeat   = !empty($n['is_featured']);
                $isBreak  = !empty($n['is_breaking']);
                $pubDate  = !empty($n['publish_date']) ? format_date($n['publish_date']) : '—';
                $views    = (int)($n['views_count'] ?? 0);
            ?>
              <tr>
                <td><span style="font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;color:var(--rm-admin-text-3);font-size:0.8rem;">#<?= $id ?></span></td>
                <td>
                  <?php
                    $fallbackIcon = admin_icon('image', 22, 1.6);
                    $fallbackDiv  = '<div class="news-thumb-placeholder" style="width:72px;height:44px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--rm-admin-text-4);background:var(--rm-admin-surface-2);" aria-hidden="true">'.$fallbackIcon.'</div>';
                    $fallbackJS   = 'this.onerror=null;this.outerHTML=' . json_encode($fallbackDiv, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . ';';
                    $onerrorAttr  = htmlspecialchars($fallbackJS, ENT_QUOTES, 'UTF-8');
                  ?>
                  <img
                    src="<?= e(img_fallback($n['cover_image_path'] ?? '', '16x9', $title, 'news-list-'.$id)) ?>"
                    alt=""
                    style="width:72px;height:44px;object-fit:cover;border-radius:8px;border:1px solid var(--rm-admin-border);display:block;"
                    onerror="<?= $onerrorAttr ?>">
                </td>
                <td>
                  <div style="margin-bottom:2px;">
                    <a href="<?= e(url('/admin/noticias/'.$id.'/editar')) ?>" style="color:var(--rm-admin-text-1);font-weight:600;text-decoration:none;line-height:1.35;" class="hover-accent">
                      <?= e(mb_substr($title, 0, 72)) ?><?= mb_strlen($title) > 72 ? '…' : '' ?>
                    </a>
                  </div>
                  <?php if ($slug !== ''): ?>
                    <div style="font-size:0.72rem;color:var(--rm-admin-text-4);font-family:ui-monospace,Consolas,monospace;">/<?= e($slug) ?></div>
                  <?php endif; ?>
                </td>
                <td><span style="color:var(--rm-admin-text-3);"><?= e($section) ?></span></td>
                <td>
                  <?php if ($isPub): ?>
                    <span class="badge badge-success">Publicada</span>
                  <?php else: ?>
                    <span class="badge badge-warning">Borrador</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($isFeat): ?>
                    <span class="badge badge-accent badge-sm" style="background:#2A1452 !important;color:#C084FC !important;border-color:rgba(192,132,252,0.28) !important;">Destacada</span>
                  <?php else: ?>
                    <span class="badge badge-secondary badge-sm" style="opacity:0.6;">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($isBreak): ?>
                    <span class="badge badge-danger badge-sm">U. Hora</span>
                  <?php else: ?>
                    <span class="badge badge-secondary badge-sm" style="opacity:0.6;">—</span>
                  <?php endif; ?>
                </td>
                <td><span style="color:var(--rm-admin-text-3);font-size:0.82rem;"><?= e($pubDate) ?></span></td>
                <td><span style="font-variant-numeric:tabular-nums;color:var(--rm-admin-text-3);font-size:0.82rem;"><?= $views ?></span></td>
                <td class="text-right">
                  <div class="row-actions justify-content-end">
                    <a
                      class="btn-icon edit"
                      href="<?= e(url('/admin/noticias/'.$id.'/editar')) ?>"
                      title="Editar"
                      aria-label="Editar noticia #<?= $id ?>">
                      <?= admin_icon('pencil', 15, 1.9) ?>
                    </a>
                    <a
                      class="btn-icon toggle"
                      href="<?= e(url('/admin/noticias/'.$id.'/toggle')) ?>"
                      title="<?= $isPub ? 'Despublicar' : 'Publicar' ?>"
                      aria-label="<?= $isPub ? 'Despublicar noticia #'.$id : 'Publicar noticia #'.$id ?>"
                      onclick="return confirm('¿Cambiar el estado de publicación de esta noticia?')">
                      <?= admin_icon($isPub ? 'toggle-right' : 'toggle-left', 15, 1.9) ?>
                    </a>
                    <?php if ($slug !== ''): ?>
                      <a
                        class="btn-icon view"
                        href="<?= e(news_url($n)) ?>"
                        target="_blank"
                        rel="noopener"
                        title="Ver en sitio"
                        aria-label="Ver noticia #<?= $id ?> en el sitio público">
                        <?= admin_icon('eye', 15, 1.9) ?>
                      </a>
                    <?php endif; ?>
                    <form
                      method="post"
                      action="<?= e(url('/admin/noticias/'.$id.'/eliminar')) ?>"
                      onsubmit="return confirm('¿Eliminar permanentemente esta noticia? Esta acción no se puede deshacer.')"
                      style="display:inline;margin:0;padding:0;">
                      <?= csrf_field() ?>
                      <button
                        type="submit"
                        class="btn-icon delete"
                        title="Eliminar permanentemente"
                        aria-label="Eliminar noticia #<?= $id ?>">
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
  <?php if (!empty($paginator) && ($paginator['total_pages'] ?? 1) > 1): ?>
    <div class="card-footer d-flex justify-content-center pt-3 pb-2">
      <?= function_exists('pagination_links') ? pagination_links($paginator) : '' ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
