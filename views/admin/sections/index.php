<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<?php
$filters = $filters ?? ['q' => '', 'section_type' => '', 'is_featured' => '', 'is_active' => ''];
$paginator = $results ?? ($paginator ?? ['items' => [], 'total' => 0, 'from' => 0, 'to' => 0, 'total_pages' => 1]);
?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1">🗂️ Secciones</h1>
      <p class="muted mb-0">Categorías y secciones del portal: noticias, radio, videos, podcast.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-primary" href="<?= url('/admin/secciones/crear') ?>">➕ Crear sección</a>
      <a class="btn btn-outline-secondary" href="<?= url('/') ?>" target="_blank">🌐 Ver sitio</a>
    </div>
  </div>
</div>

<form method="get" action="<?= url('/admin/secciones') ?>" class="card card-sm mb-3 filters-card">
  <div class="card-body p-3">
    <div class="row g-2 align-end">
      <div class="col-lg-4 col-md-6 col-12">
        <div class="input-group">
          <input type="search" name="q" class="form-control form-control-sm" placeholder="Buscar por nombre…" value="<?= e($filters['q']) ?>">
          <button class="btn btn-primary btn-sm" type="submit">🔎</button>
        </div>
      </div>
      <div class="col-lg-2 col-md-6 col-sm-6 col-6">
        <select name="section_type" class="form-select form-select-sm" data-submit-on-change>
          <option value="">Todos los tipos</option>
          <option value="news" <?= $filters['section_type'] === 'news' ? 'selected' : '' ?>>📰 Noticias</option>
          <option value="radio" <?= $filters['section_type'] === 'radio' ? 'selected' : '' ?>>📻 Radio</option>
          <option value="videos" <?= $filters['section_type'] === 'videos' ? 'selected' : '' ?>>🎥 Videos</option>
          <option value="podcast" <?= $filters['section_type'] === 'podcast' ? 'selected' : '' ?>>🎙️ Podcast</option>
        </select>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-4 col-6">
        <select name="is_featured" class="form-select form-select-sm" data-submit-on-change>
          <option value="">Menú principal: Todas</option>
          <option value="1" <?= $filters['is_featured'] === '1' ? 'selected' : '' ?>⭐ Sí, en menú</option>
          <option value="0" <?= $filters['is_featured'] === '0' ? 'selected' : '' ?>>No en menú</option>
        </select>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-4 col-6">
        <select name="is_active" class="form-select form-select-sm" data-submit-on-change>
          <option value="">Estado: Todas</option>
          <option value="1" <?= $filters['is_active'] === '1' ? 'selected' : '' ?>>✅ Activas</option>
          <option value="0" <?= $filters['is_active'] === '0' ? 'selected' : '' ?>>🚫 Inactivas</option>
        </select>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-4 col-6">
        <a class="btn btn-outline-secondary btn-sm w-100" href="<?= url('/admin/secciones') ?>">🧹 Limpiar</a>
      </div>
    </div>
    <div class="d-flex gap-2 justify-between flex-wrap mt-2 align-center">
      <span class="muted-sm">Mostrando <strong><?= (int)($paginator['from'] ?? 0) ?>–<?= (int)($paginator['to'] ?? 0) ?></strong> de <strong><?= (int)($paginator['total'] ?? 0) ?></strong></span>
    </div>
  </div>
</form>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($paginator['items'])): ?>
      <div class="list-empty p-5 text-center">
        <div style="font-size:3rem;margin-bottom:.5rem">🗂️</div>
        <h3 class="h-title-sm">No hay secciones</h3>
        <p class="muted">Crea la primera sección para clasificar el contenido.</p>
        <a class="btn btn-primary" href="<?= url('/admin/secciones/crear') ?>">➕ Crear sección</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table table-striped table-hover mb-0 admin-table">
          <thead>
            <tr>
              <th style="width:60px">Icono</th>
              <th>Nombre</th>
              <th>Slug</th>
              <th>Tipo</th>
              <th>Menú ppal.</th>
              <th>Orden</th>
              <th>Estado</th>
              <th style="width:180px">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($paginator['items'] as $s):
                $id = (int)$s['id'];
                $type = $s['section_type'] ?? 'news';
                $typeLabels = [
                    'news'    => ['📰', 'Noticias',  'secondary'],
                    'noticias'=> ['📰', 'Noticias',  'secondary'],
                    'radio'   => ['📻', 'Radio',     'success'],
                    'videos'  => ['🎥', 'Videos',    'info'],
                    'podcast' => ['🎙️', 'Podcast',   'accent'],
                ];
                $t = $typeLabels[$type] ?? ['📁', ucfirst($type), 'secondary'];
            ?>
              <tr>
                <td>
                  <div style="width:40px;height:40px;border-radius:10px;background:<?= e($s['color'] ?? '#f1f5f9') ?>;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                    <?= e($s['icon'] ?? '📁') ?>
                  </div>
                </td>
                <td>
                  <a href="<?= url('/admin/secciones/'.$id.'/editar') ?>" class="admin-strong-link">
                    <?= e($s['name']) ?>
                  </a>
                  <?php if (!empty($s['description'])): ?>
                    <div class="muted-sm" style="font-size:.8rem;line-height:1.2;max-width:360px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                      <?= e(mb_substr($s['description'], 0, 80)) ?><?= mb_strlen($s['description']) > 80 ? '…' : '' ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="mono muted-sm">/<?= e($s['slug']) ?></td>
                <td><span class="badge badge-<?= $t[2] ?>"><?= $t[0] ?> <?= $t[1] ?></span></td>
                <td>
                  <?php if (!empty($s['is_featured'])): ?>
                    <span class="badge badge-accent">⭐ Sí</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">No</span>
                  <?php endif; ?>
                </td>
                <td><span class="badge badge-secondary"><?= (int)($s['sort_order'] ?? 0) ?></span></td>
                <td>
                  <?php if (!empty($s['is_active'])): ?>
                    <span class="badge badge-success">✅ Activa</span>
                  <?php else: ?>
                    <span class="badge badge-danger">🚫 Inactiva</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="btn-group" role="group">
                    <a class="btn btn-sm btn-outline-primary" href="<?= url('/admin/secciones/'.$id.'/editar') ?>" title="Editar">✏️</a>
                    <a class="btn btn-sm btn-outline-<?= $s['is_active'] ? 'warning' : 'success' ?>"
                       href="<?= url('/admin/secciones/'.$id.'/toggle') ?>"
                       onclick="return confirm('¿Cambiar estado de esta sección?')"
                       title="<?= $s['is_active'] ? 'Desactivar' : 'Activar' ?>">
                      <?= $s['is_active'] ? '🔽' : '🔼' ?>
                    </a>
                    <form method="post" action="<?= url('/admin/secciones/'.$id.'/eliminar') ?>"
                          onsubmit="return confirm('⚠️ ¿Eliminar sección? Si contiene noticias NO se podrá eliminar.')"
                          style="display:inline">
                      <?= csrf_field() ?>
                      <button class="btn btn-sm btn-outline-danger" type="submit" title="Eliminar">🗑️</button>
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
    <div class="card-footer">
      <?= function_exists('pagination_links') ? pagination_links($paginator) : '' ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
