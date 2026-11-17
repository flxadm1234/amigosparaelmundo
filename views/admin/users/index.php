<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1">Usuarios</h1>
      <p class="muted mb-0">Gestión de accesos al panel: Admin y Editor.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('/admin/usuarios/crear') ?>">➕ Nuevo usuario</a>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($users)): ?>
      <div class="list-empty p-5 text-center">
        <div style="font-size:3rem;margin-bottom:.5rem">👥</div>
        <h3 class="h-title-sm">No hay usuarios</h3>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table table-striped table-compact mb-0 admin-table">
          <thead>
            <tr>
              <th style="width:50px"></th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Creado</th>
              <th style="width:180px">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): $id = (int)$u['id']; ?>
              <tr>
                <td>
                  <img src="<?= e(avatar_svg($u['name'] ?? '?', 36)) ?>" alt="" class="avatar-sm">
                </td>
                <td>
                  <a href="<?= url('/admin/usuarios/'.$id.'/editar') ?>" class="admin-strong-link"><?= e($u['name']) ?></a>
                </td>
                <td class="mono"><?= e($u['email']) ?></td>
                <td>
                  <?php
                    $rname = $u['role_name'] ?? (\App\Models\Role::find((int)($u['role_id'] ?? 0))['name'] ?? '—');
                    $isAdmin = mb_strtolower($rname) === 'admin' || !empty($u['role_id']) && (int)$u['role_id'] === 1;
                    if ($isAdmin):
                  ?>
                    <span class="badge badge-primary">Admin</span>
                  <?php else: ?>
                    <span class="badge badge-secondary"><?= e($rname) ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($u['status'])): ?>
                    <span class="badge badge-success">Activo</span>
                  <?php else: ?>
                    <span class="badge badge-danger">Bloqueado</span>
                  <?php endif; ?>
                </td>
                <td class="muted-sm"><?= e(format_date($u['created_at'] ?? '')) ?></td>
                <td>
                  <div class="btn-group">
                    <a class="btn btn-xs btn-outline-primary" href="<?= url('/admin/usuarios/'.$id.'/editar') ?>">✏️</a>
                    <?php if ((int)(auth_user()['id'] ?? 0) !== $id): ?>
                      <form method="post" action="<?= url('/admin/usuarios/'.$id.'/eliminar') ?>"
                            onsubmit="return confirm('¿Eliminar usuario permanentemente?')" style="display:inline">
                        <?= csrf_field() ?>
                        <button class="btn btn-xs btn-outline-danger" type="submit">🗑️</button>
                      </form>
                    <?php else: ?>
                      <button class="btn btn-xs btn-outline-secondary" disabled title="Tu propio usuario" aria-disabled="true">🔒</button>
                    <?php endif; ?>
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
