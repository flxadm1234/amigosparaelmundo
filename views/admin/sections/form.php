<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<?php
$item = $item ?? null;
$isEdit = !empty($item);
$formAction = $isEdit
    ? url('/admin/secciones/'.((int)$item['id']).'/actualizar')
    : url('/admin/secciones/guardar');
$pageTitle = $isEdit ? '✏️ Editar sección #'.(int)$item['id'] : '➕ Crear nueva sección';

$oldFn = function($k, $def = '') use ($item) {
    $v = old($k);
    if ($v !== '' && $v !== null) return $v;
    if ($item && array_key_exists($k, $item)) return $item[$k];
    return $def;
};
?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1"><?= e($pageTitle) ?></h1>
      <p class="muted mb-0">Las secciones clasifican noticias, videos, podcast y programas de radio.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-outline-secondary" href="<?= url('/admin/secciones') ?>">← Volver al listado</a>
    </div>
  </div>
</div>

<?php
$errors = method_exists('Session', 'flash') ? \Session::flash('errors') : null;
if (!empty($errors) && is_array($errors)): ?>
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
  <div class="col-lg-8 col-12">
    <form method="post" action="<?= e($formAction) ?>" class="card">
      <?= csrf_field() ?>
      <div class="card-header"><h3 class="card-title-sm mb-0">📋 Datos de la sección</h3></div>
      <div class="card-body">

        <div class="row g-3">
          <div class="col-md-8 col-12">
            <div class="form-group">
              <label class="form-label">Nombre de la sección *</label>
              <input type="text" name="name" id="f_name" required minlength="2" maxlength="100"
                     class="form-control form-control-lg" value="<?= e($oldFn('name')) ?>"
                     data-slug-source data-slug-target="#f_slug"
                     placeholder="Ej: Política, Deportes, Locales…">
            </div>
          </div>

          <div class="col-md-4 col-12">
            <div class="form-group">
              <label class="form-label">
                Slug *
                <button type="button" class="btn btn-xs btn-outline-primary float-end slug-from-name"
                        data-from="f_name" data-to="f_slug">🔄 Generar</button>
              </label>
              <input type="text" name="slug" id="f_slug" required maxlength="120"
                     class="form-control mono" value="<?= e($oldFn('slug')) ?>"
                     placeholder="politica, deportes, locales">
            </div>
          </div>

          <div class="col-12">
            <div class="form-group">
              <label class="form-label">Descripción (opcional)</label>
              <textarea name="description" rows="3" maxlength="500" class="form-control"
                        placeholder="Breve descripción de la sección (aparece en cabecera y SEO)"><?= e($oldFn('description')) ?></textarea>
            </div>
          </div>

          <div class="col-md-4 col-12">
            <div class="form-group">
              <label class="form-label">Tipo de sección *</label>
              <select name="section_type" id="f_section_type" required class="form-select">
                <option value="news"     <?= $oldFn('section_type', 'news') === 'news'     ? 'selected' : '' ?>>📰 Noticias</option>
                <option value="radio"    <?= $oldFn('section_type', 'news') === 'radio'    ? 'selected' : '' ?>>📻 Radio</option>
                <option value="videos"   <?= $oldFn('section_type', 'news') === 'videos'   ? 'selected' : '' ?>>🎥 Videos</option>
                <option value="podcast"  <?= $oldFn('section_type', 'news') === 'podcast'  ? 'selected' : '' ?>>🎙️ Podcast</option>
              </select>
              <div class="form-help">Define el tipo de contenido que usa esta sección.</div>
            </div>
          </div>

          <div class="col-md-4 col-12">
            <div class="form-group">
              <label class="form-label">Icono (emoji o texto)</label>
              <input type="text" name="icon" class="form-control text-center" style="font-size:1.4rem;"
                     value="<?= e($oldFn('icon', '📁')) ?>"
                     placeholder="📰, 🎥, 🎙️, ⚽…">
              <div class="form-help">Pega un emoji o escribe un carácter.</div>
            </div>
          </div>

          <div class="col-md-4 col-12">
            <div class="form-group">
              <label class="form-label">Color distintivo</label>
              <div class="input-group">
                <input type="color" name="color" id="f_color" class="form-control form-control-color"
                       value="<?= e($oldFn('color', '#1e40af')) ?>" style="height:38px;width:56px;cursor:pointer;padding:2px;">
                <input type="text" name="color_text" class="form-control mono"
                       value="<?= e($oldFn('color', '#1e40af')) ?>"
                       oninput="document.getElementById('f_color').value=this.value"
                       onchange="document.getElementById('f_color').value=this.value">
              </div>
              <div class="form-help">Color para badges, tarjetas y enlaces de la sección.</div>
            </div>
          </div>

          <div class="col-md-4 col-12">
            <label class="form-check form-switch">
              <input type="checkbox" name="is_featured" class="form-check-input"
                     <?= (int)($oldFn('is_featured', 0)) === 1 ? 'checked' : '' ?>>
              <span><strong>⭐ Mostrar en menú principal</strong></span>
            </label>
            <div class="form-help ms-4">Si está activo, aparecerá en la navegación superior del home.</div>
          </div>

          <div class="col-md-4 col-12">
            <div class="form-group">
              <label class="form-label">Orden (número)</label>
              <input type="number" name="sort_order" min="0" max="9999" class="form-control"
                     value="<?= e($oldFn('sort_order', 0)) ?>">
              <div class="form-help">Orden ascendente: 0 = primero, 9999 = último.</div>
            </div>
          </div>

          <div class="col-md-4 col-12">
            <label class="form-check form-switch">
              <input type="checkbox" name="is_active" class="form-check-input"
                     <?= (int)($oldFn('is_active', 1)) === 1 ? 'checked' : '' ?>>
              <span><strong>✅ Sección activa</strong></span>
            </label>
            <div class="form-help ms-4">Desactiva para ocultar la sección del sitio público.</div>
          </div>
        </div>

      </div>
      <div class="card-footer d-flex gap-2 flex-wrap justify-end">
        <a class="btn btn-secondary" href="<?= url('/admin/secciones') ?>">↩️ Cancelar</a>
        <button type="submit" name="action" value="save_and_continue" class="btn btn-success">💾 Guardar y seguir editando</button>
        <button type="submit" name="action" value="save" class="btn btn-primary">✅ Guardar cambios</button>
        <?php if ($isEdit): ?>
          <form method="post" action="<?= url('/admin/secciones/'.((int)$item['id']).'/eliminar') ?>"
                onsubmit="return confirm('⚠️ ¿Eliminar permanentemente esta sección? Verifica que no tenga contenido asociado.')"
                style="display:inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger">🗑️ Eliminar</button>
          </form>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <div class="col-lg-4 col-12">
    <div class="card sticky-top" style="top:1rem">
      <div class="card-header"><h3 class="card-title-sm mb-0">👁️ Vista previa</h3></div>
      <div class="card-body text-center">
        <div id="preview_badge" style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:999px;background:<?= e($oldFn('color','#1e40af')) ?>;color:#fff;font-weight:600;font-size:1rem;box-shadow:0 4px 14px -4px rgba(0,0,0,.2);">
          <span id="preview_icon" style="font-size:1.2rem;"><?= e($oldFn('icon', '📁')) ?></span>
          <span id="preview_name"><?= e($oldFn('name', 'Nombre de sección')) ?></span>
        </div>
        <div class="muted-sm mt-3">Así se verá la sección en el sitio público.</div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var fName  = document.getElementById('f_name');
    var fIcon  = document.querySelector('input[name="icon"]');
    var fColor = document.getElementById('f_color');
    var pName  = document.getElementById('preview_name');
    var pIcon  = document.getElementById('preview_icon');
    var pBadge = document.getElementById('preview_badge');
    if (fName && pName) fName.addEventListener('input', function() { pName.textContent = fName.value || 'Nombre de sección'; });
    if (fIcon && pIcon) fIcon.addEventListener('input', function() { pIcon.textContent = fIcon.value || '📁'; });
    if (fColor && pBadge) fColor.addEventListener('input', function() { pBadge.style.background = fColor.value; });
  });
</script>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
