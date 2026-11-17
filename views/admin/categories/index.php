<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1">Categorías</h1>
      <p class="muted mb-0">Clasificación principal de publicaciones: Electoral, Social, Educativa, Mercado.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('/admin/categorias/crear') ?>">➕ Nueva categoría</a>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($categories)): ?>
      <div class="list-empty p-5 text-center">
        <div style="font-size:3rem;margin-bottom:.5rem">📁</div>
        <h3 class="h-title-sm">Aún no hay categorías</h3>
        <a class="btn btn-primary mt-3" href="<?= url('/admin/categorias/crear') ?>">➕ Crear primera categoría</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table table-striped table-compact mb-0 admin-table">
          <thead>
            <tr>
              <th style="width:60px">ID</th>
              <th>Nombre</th>
              <th>Slug</th>
              <th>Orden</th>
              <th>Estado</th>
              <th style="width:160px">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $c): $id = (int)$c['id']; ?>
              <tr>
                <td class="mono muted-sm"><?= $id ?></td>
                <td><a href="<?= url('/admin/categorias/'.$id.'/editar') ?>" class="admin-strong-link"><?= e($c['name']) ?></a></td>
                <td class="mono muted-sm">/<?= e($c['slug']) ?></td>
                <td class="muted-sm"><?= (int)($c['sort_order'] ?? 0) ?></td>
                <td>
                  <?php if (!empty($c['is_active'])): ?>
                    <span class="badge badge-success">Activa</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">Inactiva</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="btn-group">
                    <a class="btn btn-xs btn-outline-primary" href="<?= url('/admin/categorias/'.$id.'/editar') ?>">✏️</a>
                    <a class="btn btn-xs btn-outline-secondary" href="<?= url('/publicaciones?category_id='.$id) ?>" target="_blank">👁️</a>
                    <form method="post" action="<?= url('/admin/categorias/'.$id.'/eliminar') ?>"
                          onsubmit="return confirm('¿Eliminar esta categoría? Las publicaciones pasarán a sin categoría.')" style="display:inline">
                      <?= csrf_field() ?>
                      <button class="btn btn-xs btn-outline-danger" type="submit">🗑️</button>
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
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
