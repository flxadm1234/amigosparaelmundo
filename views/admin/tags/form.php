<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<?php
  $isEdit = !empty($item);
  $formAction = $isEdit ? url('/admin/etiquetas/'.((int)$item['id']).'/actualizar') : url('/admin/etiquetas/guardar');
  $title = $isEdit ? 'Editar etiqueta #'.(int)$item['id'] : 'Nueva etiqueta';
  $valFn = function($k, $def = '') use ($item) {
      $v = old($k);
      if ($v !== '' && $v !== null) return $v;
      if ($item && array_key_exists($k, $item)) return $item[$k];
      return $def;
  };
?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div><h1 class="h-title mb-1"><?= e($title) ?></h1></div>
    <a class="btn btn-outline-secondary" href="<?= url('/admin/etiquetas') ?>">← Volver</a>
  </div>
</div>

<div class="row justify-center">
  <div class="col-lg-7 col-12">
    <form method="post" action="<?= e($formAction) ?>" class="card" novalidate>
      <?= csrf_field() ?>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Nombre *</label>
          <input type="text" name="name" id="f_name" required minlength="2" maxlength="80"
                 class="form-control form-control-lg" value="<?= e($valFn('name')) ?>"
                 placeholder="Ej: Lima, Municipal, Presidencial"
                 data-slug-source>
        </div>
        <div class="mb-3">
          <label class="form-label">Slug * <button type="button" class="btn btn-xs btn-outline-primary float-end slug-from-name" data-from="f_name" data-to="f_slug">Generar</button></label>
          <input type="text" name="slug" id="f_slug" required maxlength="100"
                 class="form-control mono" value="<?= e($valFn('slug')) ?>"
                 placeholder="ej: lima, municipal, presidencial">
        </div>
        <div class="row g-3">
          <div class="col-md-6 col-12">
            <label class="form-label">Orden (menor = primero)</label>
            <input type="number" name="sort_order" min="0" max="9999" step="1" class="form-control"
                   value="<?= e($valFn('sort_order', 0)) ?>">
          </div>
          <div class="col-md-6 col-12">
            <label class="form-check form-switch mt-4">
              <input type="checkbox" name="is_active" class="form-check-input"
                     <?= (int)($valFn('is_active', 1)) === 1 ? 'checked' : '' ?>>
              <span>Activa (visible en filtros)</span>
            </label>
          </div>
        </div>
      </div>
      <div class="card-footer text-end">
        <a class="btn btn-outline-secondary" href="<?= url('/admin/etiquetas') ?>">Cancelar</a>
        <button type="submit" class="btn btn-primary">💾 Guardar etiqueta</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
