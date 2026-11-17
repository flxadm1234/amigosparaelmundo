<?php include __DIR__ . '/../../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../../partials/admin_sidebar.php'; ?>

<?php
$item = $item ?? null;
$isEdit = !empty($item);
$formAction = $isEdit
    ? url('/admin/radio/programas/'.((int)$item['id']).'/actualizar')
    : url('/admin/radio/programas/guardar');
$pageTitle = $isEdit ? '✏️ Editar programa de impacto #'.(int)$item['id'] : '✨ Crear programa de impacto';

$oldFn = function($k, $def = '') use ($item) {
    $v = old($k);
    if ($v !== '' && $v !== null) return $v;
    if ($item && array_key_exists($k, $item)) return $item[$k];
    return $def;
};

$coverUrl = '';
if ($isEdit && !empty($item['cover_image_path'])) {
    $coverUrl = upload_url($item['cover_image_path']);
}
?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1"><?= e($pageTitle) ?></h1>
      <p class="muted mb-0">Contenido de las tarjetas que se muestran en “Nuestros Programas”.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <?php if ($isEdit && !empty($item['slug'])): ?>
                <a class="btn btn-outline-secondary" href="<?= url('/#programas') ?>" target="_blank">🌐 Ver en sitio</a>
      <?php endif; ?>
      <a class="btn btn-outline-secondary" href="<?= url('/admin/radio/programas') ?>">← Volver al listado</a>
    </div>
  </div>
</div>

<?php
$errors = method_exists('Session', 'flash') ? \Session::flash('errors') : null;
$flashSuccess = method_exists('Session', 'flash') ? \Session::flash('success') : null;
$flashError   = method_exists('Session', 'flash') ? \Session::flash('error') : null;

if (!empty($flashSuccess)): ?>
  <div class="alert alert-success mb-3" role="alert">
    ✅ <?= e(is_array($flashSuccess) ? implode(', ', $flashSuccess) : $flashSuccess) ?>
  </div>
<?php endif; ?>
<?php if (!empty($flashError)): ?>
  <div class="alert alert-danger mb-3" role="alert">
    ❌ <?= e(is_array($flashError) ? implode(', ', $flashError) : $flashError) ?>
  </div>
<?php endif; ?>
<?php if (!empty($errors) && is_array($errors)): ?>
  <div class="alert alert-danger mb-3">
    <strong>⚠️ Errores de validación:</strong>
    <ul class="mb-0 mt-2">
      <?php foreach ($errors as $e): ?>
        <li><?= e(is_array($e) ? implode(', ', $e) : $e) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-12">
    <form method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="card mb-0" id="radioProgramForm">
      <?= csrf_field() ?>
      <div class="card-header"><h3 class="card-title-sm mb-0">🌱 Datos del programa</h3></div>
      <div class="card-body">
        <div class="row g-3">

          <div class="col-lg-8 col-12">
            <div class="row g-3">

              <div class="col-md-8 col-12">
                <div class="form-group">
                  <label class="form-label">Nombre del programa *</label>
                  <input type="text" name="name" id="f_name" required minlength="2" maxlength="150"
                         class="form-control form-control-lg" value="<?= e($oldFn('name')) ?>"
                         data-slug-source data-slug-target="#f_slug"
                         placeholder="Ej: Sembrando Vida, Manos que Ayudan…">
                </div>
              </div>

              <div class="col-md-4 col-12">
                <div class="form-group">
                  <label class="form-label">
                    Slug *
                    <button type="button" class="btn btn-xs btn-outline-primary float-end slug-from-name"
                            data-from="f_name" data-to="f_slug">🔄 Generar</button>
                  </label>
                  <input type="text" name="slug" id="f_slug" required maxlength="150"
                         class="form-control mono" value="<?= e($oldFn('slug')) ?>"
                         placeholder="sembrando-vida">
                </div>
              </div>

              <div class="col-12">
                <div class="form-group">
                  <label class="form-label">Descripción del impacto</label>
                  <textarea name="description" rows="4" maxlength="2000" class="form-control"
                            placeholder="Sinopsis, invitados frecuentes, secciónes, características del programa…"><?= e($oldFn('description')) ?></textarea>
                </div>
              </div>

              <div class="col-md-6 col-12">
                <div class="form-group">
                  <label class="form-label">Frase breve o público beneficiario (opcional)</label>
                  <input type="text" name="hosts" class="form-control"
                         value="<?= e($oldFn('hosts')) ?>"
                         placeholder="Ej: Educación y oportunidades para niñas y niños">
                </div>
              </div>

              <div class="col-md-6 col-12">
                <div class="form-group">
                  <label class="form-label">Categoría o frecuencia (opcional)</label>
                  <input type="text" name="schedule_text" class="form-control"
                         value="<?= e($oldFn('schedule_text')) ?>"
                         placeholder="Ej: Educación · Permanente">
                  <div class="form-help">Texto opcional que aparece como contexto del programa.</div>
                </div>
              </div>

              <div class="col-md-6 col-12">
                <div class="form-group">
                  <label class="form-label" for="f_icon_key">Icono del programa</label>
                  <select name="icon_key" id="f_icon_key" class="form-select">
                    <?php foreach ([
                        'leaf' => 'Hoja · Medio ambiente',
                        'book' => 'Libro · Educación',
                        'hands' => 'Manos · Apoyo comunitario',
                        'sun' => 'Sol · Bienestar y oportunidades',
                        'heart' => 'Corazón · Solidaridad',
                        'paw' => 'Huella · Protección animal',
                    ] as $iconKey => $iconLabel): ?>
                      <option value="<?= e($iconKey) ?>" <?= $oldFn('icon_key', 'leaf') === $iconKey ? 'selected' : '' ?>>
                        <?= e($iconLabel) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="form-help">Se muestra como distintivo en la tarjeta y también sirve como imagen alternativa.</div>
                </div>
              </div>

              <div class="col-md-4 col-6">
                <div class="form-group">
                  <label class="form-label">Orden</label>
                  <input type="number" name="sort_order" min="0" max="9999" class="form-control"
                         value="<?= e($oldFn('sort_order', 0)) ?>">
                </div>
              </div>

              <div class="col-md-4 col-6 d-flex align-items-end">
                <label class="form-check form-switch">
                  <input type="checkbox" name="is_active" class="form-check-input"
                         <?= (int)($oldFn('is_active', 1)) === 1 ? 'checked' : '' ?>>
                  <span><strong>✅ Programa activo</strong></span>
                </label>
              </div>

            </div>
          </div>

          <div class="col-lg-4 col-12">
            <div class="card card-inner h-100">
              <div class="card-header border-bottom py-2"><h3 class="card-title-sm mb-0">🎨 Imagen del programa</h3></div>
              <div class="card-body text-center">
                <?php if ($isEdit && !empty($item['cover_image_path'])): ?>
                  <input type="hidden" name="cover_image_path_existing" value="<?= e($item['cover_image_path']) ?>">
                <?php endif; ?>

                <div id="cover_wrap" style="<?= empty($coverUrl) ? 'display:none;' : '' ?>margin-bottom:14px;">
                  <div style="position:relative;display:inline-block;">
                    <img id="cover_img" src="<?= e($coverUrl) ?>" alt="Cover programa"
                         style="max-width:100%;max-height:220px;border-radius:14px;border:4px solid #fecaca;box-shadow:0 8px 24px -8px rgba(239,68,68,.25);">
                    <button type="button" id="cover_remove" class="btn btn-sm btn-danger"
                            style="position:absolute;top:8px;right:8px;">🗑️</button>
                  </div>
                </div>
                <input type="hidden" name="remove_cover_image" id="f_remove_cover" value="0">

                <div class="form-group text-start">
                  <label class="form-label">Subir logo / portada</label>
                  <input type="file" name="cover_image_file" id="f_cover" accept=".jpg,.jpeg,.png,.webp"
                         class="form-control form-control-sm">
                  <div class="form-help">Tamaño recomendado: 800×800 px (cuadrada) o 1200×675 (16:9). JPG o PNG.</div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
      <div class="card-footer d-flex gap-2 flex-wrap justify-content-between align-items-center">
        <div style="min-height:36px;">
          <?php if ($isEdit): ?>&nbsp;<?php endif; ?>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
          <a class="btn btn-secondary" href="<?= url('/admin/radio/programas') ?>">↩️ Cancelar</a>
          <button type="submit" name="action" value="save_and_continue" class="btn btn-success">💾 Guardar y seguir editando</button>
          <button type="submit" name="action" value="save" class="btn btn-primary">✅ Guardar cambios</button>
        </div>
      </div>
    </form>
    <?php if ($isEdit): ?>
      <form method="post" action="<?= url('/admin/radio/programas/'.((int)$item['id']).'/eliminar') ?>"
            onsubmit="return confirm('⚠️ ¿Eliminar permanentemente este programa?');"
            class="mt-2 d-flex justify-content-start" style="margin-bottom:1.5rem;">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger d-inline-flex align-items-center gap-2">🗑️ <span>Eliminar programa</span></button>
      </form>
    <?php endif; ?>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var wrap = document.getElementById('cover_wrap');
    var img  = document.getElementById('cover_img');
    var fin  = document.getElementById('f_cover');
    var btnR = document.getElementById('cover_remove');
    var rmH  = document.getElementById('f_remove_cover');
    if (btnR && wrap && rmH && img) {
      btnR.addEventListener('click', function(e) {
        e.preventDefault();
        img.removeAttribute('src'); wrap.style.display = 'none';
        try { fin.value = ''; } catch(_) {}
        rmH.value = '1';
      });
    }
    if (fin && img && wrap && rmH) {
      fin.addEventListener('change', function() {
        var f = fin.files && fin.files[0];
        if (!f) return;
        if (!/^image\//i.test(f.type || '')) {
          alert('Selecciona una imagen válida.');
          try { fin.value = ''; } catch(_) {} return;
        }
        rmH.value = '0';
        var r = new FileReader();
        r.onload = function(ev) { img.setAttribute('src', ev.target.result); wrap.style.display = 'block'; };
        r.readAsDataURL(f);
      });
    }
  });
</script>

<?php include __DIR__ . '/../../../partials/admin_footer.php'; ?>
