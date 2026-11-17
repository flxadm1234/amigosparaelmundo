<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<?php
  $isEdit = !empty($user);
  $formAction = $isEdit ? url('/admin/usuarios/'.((int)$user['id']).'/actualizar') : url('/admin/usuarios/guardar');
  $title = $isEdit ? 'Editar usuario #'.(int)$user['id'] : 'Nuevo usuario';
  $valFn = function($k, $def = '') use ($user) {
      $v = old($k);
      if ($v !== '' && $v !== null) return $v;
      if ($user && array_key_exists($k, $user)) return $user[$k];
      return $def;
  };
?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div><h1 class="h-title mb-1"><?= e($title) ?></h1></div>
    <a class="btn btn-outline-secondary" href="<?= url('/admin/usuarios') ?>">← Volver</a>
  </div>
</div>

<div class="row justify-center">
  <div class="col-lg-7 col-12">
    <form method="post" action="<?= e($formAction) ?>" class="card" novalidate>
      <?= csrf_field() ?>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-8 col-sm-12">
            <label class="form-label">Nombre completo *</label>
            <input type="text" name="name" required minlength="3" maxlength="120"
                   class="form-control form-control-lg" value="<?= e($valFn('name')) ?>"
                   placeholder="Ej: María Gómez">
          </div>
          <div class="col-md-4 col-sm-12">
            <label class="form-label">Estado</label>
            <select name="status" class="form-select form-select-lg">
              <option value="1" <?= (int)($valFn('status', 1)) === 1 ? 'selected' : '' ?>>✅ Activo</option>
              <option value="0" <?= (int)($valFn('status', 1)) === 0 ? 'selected' : '' ?>>🚫 Bloqueado</option>
            </select>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Email *</label>
          <input type="email" name="email" required maxlength="180"
                 class="form-control" value="<?= e($valFn('email')) ?>"
                 placeholder="usuario@amigosporelmundo.org" autocomplete="email">
        </div>
        <div class="mb-3">
          <label class="form-label">Rol *</label>
          <select name="role_id" required class="form-select">
            <?php foreach ($roles as $r): $sel = (int)($valFn('role_id', 0)) === (int)$r['id'] ? 'selected' : ''; ?>
              <option value="<?= (int)$r['id'] ?>" <?= $sel ?>><?= e($r['name']) ?><?= !empty($r['description']) ? ' ('.e($r['description']).')' : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row g-3">
          <div class="col-md-6 col-sm-12">
            <label class="form-label">Contraseña <?php if (!$isEdit): ?>*<?php else: ?>(dejar en blanco para mantener)<?php endif; ?></label>
            <input type="password" name="password" minlength="4" maxlength="120"
                   class="form-control" placeholder="••••••••" autocomplete="new-password">
            <div class="form-help">Mín 4 caracteres. Se almacena con hash seguro (password_hash).</div>
          </div>
          <div class="col-md-6 col-sm-12">
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" minlength="4" maxlength="120"
                   class="form-control" placeholder="••••••••" autocomplete="new-password">
          </div>
        </div>
      </div>
      <div class="card-footer text-end">
        <a class="btn btn-outline-secondary" href="<?= url('/admin/usuarios') ?>">Cancelar</a>
        <button type="submit" class="btn btn-primary">💾 Guardar usuario</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
