<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1">Publicaciones</h1>
      <p class="muted mb-0">Gestiona encuestas, estudios e informes publicados.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-primary" href="<?= url('/admin/publicaciones/crear') ?>">➕ Nueva publicación</a>
      <a class="btn btn-outline-secondary" href="<?= url('/publicaciones') ?>" target="_blank">🌐 Ver en público</a>
    </div>
  </div>
</div>

<!-- Filtros -->
<form method="get" action="<?= url('/admin/publicaciones') ?>" class="card card-sm mb-3 filters-card" data-auto-submit="1">
  <div class="card-body p-3">
    <div class="row g-2 align-end">
      <div class="col-lg-4 col-md-6 col-12">
        <div class="input-group">
          <input type="search" name="q" class="form-control form-control-sm" placeholder="Buscar por título, slug…" value="<?= e($filters['q']) ?>">
          <button class="btn btn-primary btn-sm" type="submit">🔎</button>
        </div>
      </div>
      <div class="col-lg-2 col-md-6 col-sm-6 col-6">
        <select name="is_published" class="form-select form-select-sm" data-submit-on-change>
          <option value="">Todos (estado)</option>
          <option value="1" <?= $filters['is_published'] === 1 ? 'selected' : '' ?>>✅ Publicados</option>
          <option value="0"  <?= $filters['is_published'] === 0 ? 'selected' : '' ?>>📝 Borradores</option>
        </select>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-4 col-6">
        <select name="year" class="form-select form-select-sm" data-submit-on-change>
          <option value="0">Todos (año)</option>
          <?php foreach ($years as $y): ?>
            <option value="<?= (int)$y ?>" <?= $filters['year'] === (int)$y ? 'selected' : '' ?>><?= (int)$y ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-4 col-6">
        <select name="category_id" class="form-select form-select-sm" data-submit-on-change>
          <option value="0">Todas (categoría)</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= $filters['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-4 col-6">
        <select name="region_id" class="form-select form-select-sm" data-submit-on-change>
          <option value="0">Todas (región)</option>
          <?php foreach ($regions as $r): ?>
            <option value="<?= (int)$r['id'] ?>" <?= $filters['region_id'] === (int)$r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="d-flex gap-2 justify-between flex-wrap mt-2 align-center">
      <span class="muted-sm">Mostrando <strong><?= (int)($results['from'] ?? 0) ?>–<?= (int)($results['to'] ?? 0) ?></strong> de <strong><?= (int)($results['total'] ?? 0) ?></strong></span>
      <div class="d-flex gap-2">
        <select name="order" class="form-select form-select-sm d-inline-block" style="width:auto" data-submit-on-change>
          <option value="new" <?= $filters['order'] === 'new' ? 'selected' : '' ?>>Más recientes</option>
          <option value="old" <?= $filters['order'] === 'old' ? 'selected' : '' ?>>Más antiguos</option>
        </select>
        <a class="btn btn-outline-secondary btn-sm" href="<?= url('/admin/publicaciones') ?>">Limpiar</a>
      </div>
    </div>
  </div>
</form>

<!-- Tabla -->
<div class="card">
  <div class="card-body p-0">
    <?php if (empty($results['items'])): ?>
      <div class="list-empty p-5 text-center">
        <div style="font-size:3rem;margin-bottom:.5rem">📭</div>
        <h3 class="h-title-sm">No hay publicaciones</h3>
        <p class="muted">Prueba creando la primera publicación.</p>
        <a class="btn btn-primary" href="<?= url('/admin/publicaciones/crear') ?>">➕ Crear publicación</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table table-striped table-compact mb-0 admin-table">
          <thead>
            <tr>
              <th style="width:40px"><input type="checkbox" class="check-all" data-target=".row-check"></th>
              <th>ID</th>
              <th>Título</th>
              <th>Categoría</th>
              <th>Año</th>
              <th>Región</th>
              <th>Fecha</th>
              <th>Estado</th>
              <th>Adj</th>
              <th style="width:180px">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results['items'] as $p):
                $id = (int)$p['id'];
                $attsCount = (int)(\App\Models\Publication::attachmentsCount($id) ?? 0);
            ?>
              <tr>
                <td><input type="checkbox" class="row-check" value="<?= $id ?>"></td>
                <td class="mono muted-sm"><?= $id ?></td>
                <td>
                  <a href="<?= url('/admin/publicaciones/'.$id.'/editar') ?>" class="admin-strong-link">
                    <?= e(mb_substr($p['title'], 0, 70)) ?><?= mb_strlen($p['title']) > 70 ? '…' : '' ?>
                  </a>
                  <div class="muted-sm">/<?= e($p['slug']) ?></div>
                </td>
                <td><?= e($p['category_name'] ?? '—') ?></td>
                <td><?= $p['year'] ? (int)$p['year'] : '—' ?></td>
                <td><?= e($p['region_name'] ?? '—') ?></td>
                <td class="muted-sm"><?= e(format_date($p['publish_date'] ?? '')) ?></td>
                <td>
                  <?php if (!empty($p['is_published'])): ?>
                    <span class="badge badge-success">Publicado</span>
                  <?php else: ?>
                    <span class="badge badge-warning">Borrador</span>
                  <?php endif; ?>
                </td>
                <td><span class="badge badge-secondary"><?= $attsCount ?> 📎</span></td>
                <td>
                  <div class="btn-group" role="group" aria-label="Acciones">
                    <a class="btn btn-xs btn-outline-primary" href="<?= url('/admin/publicaciones/'.$id.'/editar') ?>" title="Editar">✏️</a>
                    <a class="btn btn-xs btn-outline-secondary" href="<?= url('/publicaciones/'.$p['slug']) ?>" target="_blank" title="Ver">👁️</a>
                    <a class="btn btn-xs btn-outline-<?= $p['is_published'] ? 'warning' : 'success' ?>"
                       href="<?= url('/admin/publicaciones/'.$id.'/toggle') ?>"
                       title="<?= $p['is_published'] ? 'Despublicar' : 'Publicar' ?>"
                       onclick="return confirm('¿Cambiar el estado de esta publicación?')">
                      <?= $p['is_published'] ? '🔽' : '🔼' ?>
                    </a>
                    <form method="post" action="<?= url('/admin/publicaciones/'.$id.'/eliminar') ?>"
                          onsubmit="return confirm('¿Eliminar permanentemente esta publicación? No se puede deshacer.')"
                          style="display:inline">
                      <?= csrf_field() ?>
                      <button class="btn btn-xs btn-outline-danger" type="submit" title="Eliminar">🗑️</button>
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
  <?php if (!empty($results) && $results['total_pages'] > 1): ?>
    <div class="card-footer">
      <?= pagination_links($results) ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
