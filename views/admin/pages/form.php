<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<?php
  $isEdit = !empty($page);
  $formAction = $isEdit ? url('/admin/paginas/'.((int)$page['id']).'/actualizar') : url('/admin/paginas/guardar');
  $deleteFormAction = $isEdit ? url('/admin/paginas/'.((int)$page['id']).'/eliminar') : null;
  $title = $isEdit ? 'Editar página #'.(int)$page['id'] : 'Nueva página';
  $valFn = function($k, $def = '') use ($page) {
      $v = old($k);
      if ($v !== '' && $v !== null) return $v;
      if ($page && array_key_exists($k, $page)) return $page[$k];
      return $def;
  };

  $errors = method_exists('Session', 'flash') ? \Session::flash('errors') : null;
  $flashSuccess = method_exists('Session', 'flash') ? \Session::flash('success') : null;
  $flashError   = method_exists('Session', 'flash') ? \Session::flash('error') : null;
?>

<style>
  form[data-page-form="1"] .form-control,
  form[data-page-form="1"] .form-control-sm,
  form[data-page-form="1"] .form-control-lg,
  form[data-page-form="1"] .form-select,
  form[data-page-form="1"] .form-select-sm,
  form[data-page-form="1"] textarea,
  form[data-page-form="1"] .wysiwyg-editor,
  form[data-page-form="1"] input[type="text"],
  form[data-page-form="1"] input[type="number"],
  form[data-page-form="1"] input[type="search"],
  form[data-page-form="1"] input[type="email"],
  form[data-page-form="1"] input[type="url"],
  form[data-page-form="1"] input[type="password"],
  form[data-page-form="1"] input[type="date"],
  form[data-page-form="1"] input[type="datetime-local"] {
    color: #000000 !important;
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
  }
  form[data-page-form="1"] input::placeholder,
  form[data-page-form="1"] textarea::placeholder,
  form[data-page-form="1"] .form-control::placeholder,
  form[data-page-form="1"] .form-select::placeholder {
    color: #475569 !important;
    opacity: 1 !important;
  }
  form[data-page-form="1"] label.form-label {
    color: var(--bs-body-color, #0f172a) !important;
    font-weight: 600;
  }
  form[data-page-form="1"] .wysiwyg-editor,
  form[data-page-form="1"] .wysiwyg-wrap {
    color: #000000 !important;
    background: #ffffff !important;
  }
  form[data-page-form="1"] .wysiwyg-editor *,
  form[data-page-form="1"] .wysiwyg-wrap * {
    color: #000000 !important;
  }
  form[data-page-form="1"] code {
    color: #be123c !important;
    background: #fef2f2 !important;
  }
</style>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div><h1 class="h-title mb-1"><?= e($title) ?></h1></div>
    <div class="d-flex gap-2 flex-wrap">
      <?php if ($isEdit && !empty($page['slug'])): ?>
        <a class="btn btn-outline-secondary" href="<?= url('/'.$page['slug']) ?>" target="_blank">🌐 Ver en sitio</a>
      <?php endif; ?>
      <a class="btn btn-outline-secondary" href="<?= url('/admin/paginas') ?>">← Volver</a>
    </div>
  </div>
</div>

<?php if (!empty($flashSuccess)): ?>
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

<form method="post" action="<?= e($formAction) ?>" class="needs-validation" novalidate
      enctype="multipart/form-data" data-page-form="1" id="pagesFormMain">
  <?= csrf_field() ?>
  <div class="row g-4">
    <div class="col-lg-8 col-12">
      <div class="card mb-0">
        <div class="card-header"><h3 class="card-title-sm mb-0">📄 Contenido de la página</h3></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Título *</label>
            <input type="text" name="title" id="f_title" required minlength="3" maxlength="200"
                   class="form-control form-control-lg" value="<?= e($valFn('title')) ?>"
                   placeholder="Ej: Sobre nosotros, Servicios, Política de privacidad"
                   data-slug-source>
          </div>
          <div class="mb-3">
            <label class="form-label">Slug (URL amigable) * <button type="button" class="btn btn-xs btn-outline-primary float-end slug-from-name" data-from="f_title" data-to="f_slug">Generar</button></label>
            <input type="text" name="slug" id="f_slug" required maxlength="160"
                   class="form-control mono" value="<?= e($valFn('slug')) ?>"
                   placeholder="nosotros, servicios, politica-privacidad">
            <div class="form-help">Luego será accesible en: <code><?= e(url('/')) ?><strong>{slug}</strong></code></div>
          </div>
          <div class="mb-3">
            <label class="form-label">Contenido HTML</label>
            <div class="wysiwyg-wrap">
              <textarea name="content_html" id="f_content_html" class="d-none"><?= $valFn('content_html') ?></textarea>
              <div id="wysiwyg-page-content" class="wysiwyg-editor" data-editor="wysiwyg" data-sync-to="f_content_html"
                   style="min-height:400px"><?= $valFn('content_html') ?></div>
            </div>
          </div>
        </div>
        <div class="card-footer d-flex gap-2 flex-wrap justify-content-between align-items-center">
          <div style="min-height:36px;"><?php if ($isEdit): ?>&nbsp;<?php endif; ?></div>
          <div class="d-flex gap-2 flex-wrap justify-content-end">
            <a class="btn btn-secondary" href="<?= url('/admin/paginas') ?>">↩️ Cancelar</a>
            <button type="submit" name="action" value="save_and_continue" class="btn btn-success">💾 Guardar y seguir editando</button>
            <button type="submit" name="action" value="save" class="btn btn-primary">✅ Guardar cambios</button>
          </div>
        </div>
      </div>
      <?php if ($isEdit && !empty($deleteFormAction)): ?>
        <form method="post" action="<?= e($deleteFormAction) ?>"
              onsubmit="return confirm('⚠️ ¿Eliminar permanentemente esta página?');"
              class="mt-2 d-flex justify-content-start" style="margin-bottom:1.5rem;">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-danger d-inline-flex align-items-center gap-2">🗑️ <span>Eliminar página</span></button>
        </form>
      <?php endif; ?>
    </div>
    <div class="col-lg-4 col-12">
      <div class="sticky-top" style="top:1rem">
        <div class="card mb-3">
          <div class="card-header"><h3 class="card-title-sm mb-0">⚙️ Metadatos y opciones</h3></div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Meta title (SEO)</label>
              <input type="text" name="meta_title" maxlength="180" class="form-control form-control-sm"
                     value="<?= e($valFn('meta_title')) ?>" placeholder="Título para Google / buscadores">
            </div>
            <div class="mb-3">
              <label class="form-label">Meta description (SEO)</label>
              <textarea name="meta_desc" rows="3" maxlength="300" class="form-control form-control-sm"
                        placeholder="Descripción breve para resultados de búsqueda..."><?= e($valFn('meta_desc')) ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Layout</label>
              <select name="layout" class="form-select form-select-sm">
                <option value="default"  <?= ($valFn('layout', 'default') === 'default') ? 'selected' : '' ?>>Default (contenido simple)</option>
                <option value="contact"  <?= ($valFn('layout') === 'contact') ? 'selected' : '' ?>>Contacto (incluye formulario + info)</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-check form-switch">
                <input type="checkbox" name="is_published" class="form-check-input"
                       <?= (int)($valFn('is_published', 1)) === 1 ? 'checked' : '' ?>>
                <span>Publicada</span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
