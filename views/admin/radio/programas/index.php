<?php include __DIR__ . '/../../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../../partials/admin_sidebar.php'; ?>

<?php
$filtersDefaults = ['q' => '', 'is_active' => '', 'order' => 'sort'];
$filters = is_array($filters ?? null) ? array_replace($filtersDefaults, $filters) : $filtersDefaults;
$filters['q']         = (string)($filters['q'] ?? '');
$filters['is_active'] = isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null ? (string)$filters['is_active'] : '';
$filters['order']     = (string)($filters['order'] ?? 'sort');
$paginator = $results ?? ($paginator ?? ['items' => [], 'total' => 0, 'from' => 0, 'to' => 0, 'total_pages' => 1, 'page' => 1, 'per_page' => 20]);
?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1">🌱 Programas de Impacto</h1>
      <p class="muted mb-0">Administra las tarjetas y el contenido de “Nuestros Programas”.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-primary" href="<?= url('/admin/radio/programas/crear') ?>">✨ Crear programa</a>
      <a class="btn btn-outline-secondary" href="<?= url('/#programas') ?>" target="_blank">🌐 Ver portada</a>
    </div>
  </div>
</div>

<form method="get" action="<?= url('/admin/radio/programas') ?>" class="card card-sm mb-3 filters-card">
  <div class="card-body p-3">
    <div class="row g-2 align-end">
      <div class="col-lg-6 col-md-8 col-12">
        <div class="input-group">
          <input type="search" name="q" class="form-control form-control-sm" placeholder="Buscar programa…" value="<?= e($filters['q']) ?>">
          <button class="btn btn-primary btn-sm" type="submit">🔎</button>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6 col-6">
        <select name="is_active" class="form-select form-select-sm" data-submit-on-change>
          <option value="">Todos (estado)</option>
          <option value="1" <?= $filters['is_active'] === '1' ? 'selected' : '' ?>>✅ Activos</option>
          <option value="0" <?= $filters['is_active'] === '0' ? 'selected' : '' ?>>⏸️ Inactivos</option>
        </select>
      </div>
      <div class="col-lg-4 col-md-12 col-12 d-flex gap-2">
        <select name="order" class="form-select form-select-sm d-inline-block" style="width:auto" data-submit-on-change>
          <option value="sort" <?= ($filters['order'] ?? 'sort') === 'sort' ? 'selected' : '' ?>>Orden manual</option>
          <option value="name" <?= ($filters['order'] ?? 'sort') === 'name' ? 'selected' : '' ?>>Por nombre A-Z</option>
        </select>
        <a class="btn btn-outline-secondary btn-sm" href="<?= url('/admin/radio/programas') ?>">🧹 Limpiar</a>
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
        <div style="font-size:3rem;margin-bottom:.5rem">🌱</div>
        <h3 class="h-title-sm">Todavía no hay programas</h3>
        <p class="muted">Crea una iniciativa para mostrarla en la portada de la asociación.</p>
        <a class="btn btn-primary" href="<?= url('/admin/radio/programas/crear') ?>">✨ Crear programa</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table table-striped table-hover mb-0 admin-table">
          <thead>
            <tr>
              <th style="width:80px">Imagen / icono</th>
              <th>Programa</th>
              <th>Locutores</th>
              <th>Horario</th>
              <th>Orden</th>
              <th>Estado</th>
              <th style="width:180px">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($paginator['items'] as $rp):
                $id = (int)$rp['id'];
                $cover = !empty($rp['cover_image_path']) ? upload_url($rp['cover_image_path']) : '';
                $iconPreview = [
                    'leaf' => '🌿', 'book' => '📚', 'hands' => '🤝',
                    'sun' => '☀️', 'heart' => '💚', 'paw' => '🐾',
                ][$rp['icon_key'] ?? 'leaf'] ?? '🌿';
            ?>
              <tr>
                <td>
                  <?php if ($cover !== ''): ?>
                    <img src="<?= e($cover) ?>" alt="<?= e($rp['name']) ?>"
                         style="width:60px;height:60px;object-fit:cover;border-radius:10px;border:1px solid #c9d8ce;">
                  <?php else: ?>
                    <span class="program-icon-preview" title="Icono seleccionado; aún no hay portada"><?= e($iconPreview) ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="<?= url('/admin/radio/programas/'.$id.'/editar') ?>" class="admin-strong-link">
                    <?= e($rp['name']) ?>
                  </a>
                  <?php if (!empty($rp['slug'])): ?>
                    <div class="muted-sm mono" style="font-size:.78rem;">/<?= e($rp['slug']) ?></div>
                  <?php endif; ?>
                  <?php if (!empty($rp['description'])): ?>
                    <div class="muted-sm" style="font-size:.78rem;line-height:1.2;max-width:360px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                      <?= e(mb_substr($rp['description'], 0, 80)) ?><?= mb_strlen($rp['description']) > 80 ? '…' : '' ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td><?= !empty($rp['hosts']) ? e($rp['hosts']) : '<span class="muted-sm">—</span>' ?></td>
                <td>
                  <?php if (!empty($rp['schedule_text'])): ?>
                    <span class="badge badge-secondary">⏰ <?= e(mb_substr($rp['schedule_text'], 0, 60)) ?></span>
                  <?php else: ?>
                    <span class="muted-sm">Sin horario</span>
                  <?php endif; ?>
                </td>
                <td><span class="badge badge-secondary">#<?= (int)($rp['sort_order'] ?? 0) ?></span></td>
                <td>
                  <?php if (!empty($rp['is_active'])): ?>
                    <span class="badge badge-success">✅ Al aire</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">⏸️ Inactivo</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="btn-group" role="group">
                    <a class="btn btn-sm btn-outline-primary" href="<?= url('/admin/radio/programas/'.$id.'/editar') ?>" title="Editar">✏️</a>
                    <a class="btn btn-sm btn-outline-<?= $rp['is_active'] ? 'warning' : 'success' ?>"
                       href="<?= url('/admin/radio/programas/'.$id.'/toggle') ?>"
                       onclick="return confirm('¿Cambiar estado del programa?')"
                       title="<?= $rp['is_active'] ? 'Desactivar' : 'Activar' ?>">
                      <?= $rp['is_active'] ? '⏸️' : '▶️' ?>
                    </a>
                    <?php if (!empty($rp['slug'])): ?>
                    <a class="btn btn-sm btn-outline-secondary" href="<?= url('/#programas') ?>" target="_blank" title="Ver portada">👁️</a>
                    <?php endif; ?>
                    <form method="post" action="<?= url('/admin/radio/programas/'.$id.'/eliminar') ?>"
                          onsubmit="return confirm('⚠️ ¿Eliminar permanentemente este programa?')"
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

<?php include __DIR__ . '/../../../partials/admin_footer.php'; ?>
