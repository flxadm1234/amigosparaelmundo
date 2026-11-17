<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1">Páginas</h1>
      <p class="muted mb-0">Páginas estáticas: nosotros, servicios, equipo, clientes, privacidad, etc.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('/admin/paginas/crear') ?>">➕ Nueva página</a>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($results['items'])): ?>
      <div class="list-empty p-5 text-center">
        <div style="font-size:3rem;margin-bottom:.5rem">📄</div>
        <h3 class="h-title-sm">No hay páginas</h3>
        <a class="btn btn-primary mt-3" href="<?= url('/admin/paginas/crear') ?>">➕ Crear primera página</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table table-striped table-compact mb-0 admin-table">
          <thead>
            <tr>
              <th style="width:60px">ID</th>
              <th>Título</th>
              <th>Slug / URL</th>
              <th>Layout</th>
              <th>Estado</th>
              <th>Actualizado</th>
              <th style="width:200px">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results['items'] as $p): $id = (int)$p['id']; ?>
              <tr>
                <td class="mono muted-sm"><?= $id ?></td>
                <td><a href="<?= url('/admin/paginas/'.$id.'/editar') ?>" class="admin-strong-link"><?= e(mb_substr($p['title'], 0, 60)) ?><?= mb_strlen($p['title']) > 60 ? '…' : '' ?></a></td>
                <td class="mono muted-sm">/<?= e($p['slug']) ?></td>
                <td>
                  <?php if (($p['layout'] ?? 'default') === 'contact'): ?>
                    <span class="badge badge-accent">Contacto</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">Default</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($p['is_published'])): ?>
                    <span class="badge badge-success">Publicada</span>
                  <?php else: ?>
                    <span class="badge badge-warning">Borrador</span>
                  <?php endif; ?>
                </td>
                <td class="muted-sm"><?= e(format_date($p['updated_at'] ?? '')) ?></td>
                <td>
                  <div class="btn-group">
                    <a class="btn btn-xs btn-outline-primary" href="<?= url('/admin/paginas/'.$id.'/editar') ?>">✏️</a>
                    <a class="btn btn-xs btn-outline-secondary" href="<?= url('/'.$p['slug']) ?>" target="_blank">👁️</a>
                    <a class="btn btn-xs btn-outline-<?= $p['is_published'] ? 'warning' : 'success' ?>"
                       href="<?= url('/admin/paginas/'.$id.'/toggle') ?>"
                       onclick="return confirm('¿Cambiar estado de publicación?')">
                      <?= $p['is_published'] ? '🔽' : '🔼' ?>
                    </a>
                    <form method="post" action="<?= url('/admin/paginas/'.$id.'/eliminar') ?>"
                          onsubmit="return confirm('¿Eliminar página permanentemente?')" style="display:inline">
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
  <?php if (!empty($results) && $results['total_pages'] > 1): ?>
    <div class="card-footer">
      <?= pagination_links($results) ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
