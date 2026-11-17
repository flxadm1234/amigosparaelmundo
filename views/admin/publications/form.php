<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<?php
  $pub = $publication ?? null;
  $isEdit = !empty($pub);
  $formAction = $isEdit
      ? url('/admin/publicaciones/'.((int)$pub['id']).'/actualizar')
      : url('/admin/publicaciones/guardar');
  $title  = $isEdit ? '✏️ Editar publicación #'.(int)$pub['id'] : '✨ Nueva publicación';
  $oldFn = function($k, $def = '') use ($pub) {
      $v = old($k);
      if ($v !== '' && $v !== null) return $v;
      if ($pub && array_key_exists($k, $pub)) return $pub[$k];
      return $def;
  };
?>

<style>
  form[data-publications-form="1"] .form-control,
  form[data-publications-form="1"] .form-control-sm,
  form[data-publications-form="1"] .form-control-lg,
  form[data-publications-form="1"] .form-select,
  form[data-publications-form="1"] .form-select-sm,
  form[data-publications-form="1"] textarea,
  form[data-publications-form="1"] .wysiwyg-editor,
  form[data-publications-form="1"] input[type="text"],
  form[data-publications-form="1"] input[type="number"],
  form[data-publications-form="1"] input[type="search"],
  form[data-publications-form="1"] input[type="email"],
  form[data-publications-form="1"] input[type="url"],
  form[data-publications-form="1"] input[type="password"],
  form[data-publications-form="1"] input[type="date"],
  form[data-publications-form="1"] input[type="datetime-local"] {
    color: #000000 !important;
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
  }
  form[data-publications-form="1"] input::placeholder,
  form[data-publications-form="1"] textarea::placeholder,
  form[data-publications-form="1"] .form-control::placeholder,
  form[data-publications-form="1"] .form-select::placeholder {
    color: #475569 !important;
    opacity: 1 !important;
  }
  form[data-publications-form="1"] label.form-label {
    color: var(--bs-body-color, #0f172a) !important;
    font-weight: 600;
  }
  form[data-publications-form="1"] .wysiwyg-editor,
  form[data-publications-form="1"] .wysiwyg-wrap,
  form[data-publications-form="1"] [contenteditable="true"],
  form[data-publications-form="1"] .wysiwyg-content {
    color: #000000 !important;
    background: #ffffff !important;
  }
  form[data-publications-form="1"] .wysiwyg-editor *,
  form[data-publications-form="1"] .wysiwyg-wrap *,
  form[data-publications-form="1"] [contenteditable="true"] *,
  form[data-publications-form="1"] .wysiwyg-content * {
    color: #000000 !important;
    background-color: transparent;
  }
  form[data-publications-form="1"] code {
    color: #be123c !important;
    background: #fef2f2 !important;
  }

  #publicationsFormColSide .form-control,
  #publicationsFormColSide .form-select,
  #publicationsFormColSide textarea,
  #publicationsFormColSide input[type="text"],
  #publicationsFormColSide input[type="number"],
  #publicationsFormColSide input[type="search"],
  #publicationsFormColSide input[type="email"],
  #publicationsFormColSide input[type="url"],
  #publicationsFormColSide input[type="password"],
  #publicationsFormColSide input[type="date"],
  #publicationsFormColSide input[type="datetime-local"] {
    color: #000000 !important;
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
  }
  #publicationsFormColSide label.form-label {
    color: #0f172a !important;
    font-weight: 600;
  }
  #publicationsFormColSide input::placeholder,
  #publicationsFormColSide textarea::placeholder {
    color: #475569 !important;
    opacity: 1 !important;
  }

  .chars-counter {
    display: block;
    text-align: right;
    font-size: .75rem;
    color: #64748b;
    margin-top: 4px;
    font-variant-numeric: tabular-nums;
  }
  .chars-counter.is-over {
    color: #be123c !important;
    font-weight: 600;
  }
</style>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1"><?= e($title) ?></h1>
      <p class="muted mb-0">Completa los campos y guarda. Los archivos se suben al final.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <?php if ($isEdit && !empty($pub['slug'])): ?>
        <a class="btn btn-outline-secondary" href="<?= url('/publicaciones/'.$pub['slug']) ?>" target="_blank">🌐 Ver en sitio</a>
      <?php endif; ?>
      <a class="btn btn-outline-secondary" href="<?= url('/admin/publicaciones') ?>">← Volver al listado</a>
    </div>
  </div>
</div>

<div id="publicationsFormRow">

  <div id="publicationsFormColMain">

    <form method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="needs-validation" novalidate
          data-publications-form="1" id="publicationsFormMain">
      <?= csrf_field() ?>

      <div class="card mb-3">
        <div class="card-header"><h3 class="card-title-sm mb-0">📝 Contenido</h3></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Título *</label>
            <input type="text" name="title" id="f_title" required minlength="5" maxlength="250"
                   class="form-control form-control-lg" value="<?= e($oldFn('title')) ?>"
                   data-chars-max="250"
                   placeholder="Ej: Sondeo municipal Lima 2026">
          </div>
          <div class="mb-3">
            <label class="form-label">Slug (URL amigable) * <button type="button" id="slugAuto" class="btn btn-xs btn-outline-primary float-end" data-source="f_title">🔄 Generar del título</button></label>
            <input type="text" name="slug" id="f_slug" required maxlength="270"
                   class="form-control mono" value="<?= e($oldFn('slug')) ?>"
                   placeholder="sondeo-municipal-lima-2026">
            <div class="form-help">Solo letras minúsculas, números y guiones. Ej: sondeo-nacional-2026</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Resumen / Excerpt</label>
            <textarea name="excerpt" rows="3" maxlength="500" class="form-control"
                      data-chars-max="500"
                      placeholder="Resumen breve que se muestra en tarjetas y resultados de búsqueda..."><?= e($oldFn('excerpt')) ?></textarea>
          </div>
          <div class="mb-0">
            <label class="form-label">Contenido completo</label>
            <div class="wysiwyg-wrap">
              <textarea name="content_html" id="f_content_html" class="d-none" data-editor-target="wysiwyg-content"><?= $oldFn('content_html') ?></textarea>
              <div id="wysiwyg-content" class="wysiwyg-editor" data-editor="wysiwyg" data-sync-to="f_content_html"
                   style="min-height:320px"><?= $oldFn('content_html') ?></div>
            </div>
            <div class="form-help">Formato visual. Pega contenido de Word o escribe directamente.</div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><h3 class="card-title-sm mb-0">📎 Archivos adjuntos (PDF, XLSX, SAV, imágenes)</h3></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Subir nuevos archivos (selección múltiple)</label>
            <input type="file" name="attachments[]" multiple class="form-control form-control-sm"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.sav,.csv,.jpg,.jpeg,.png,.svg,.zip">
            <div class="form-help">
              Tipos permitidos: PDF, Word, Excel, SPSS (.sav), CSV, imágenes (JPG/PNG/SVG), ZIP. Tamaño máximo por archivo depende del hosting (usualmente 32–64MB).
            </div>
          </div>

          <?php if ($isEdit && !empty($attachments)): ?>
            <h4 class="card-title-sm mt-4 mb-2">Archivos adjuntos existentes</h4>
            <div class="media-list">
              <?php foreach ($attachments as $a):
                  $fm = file_type_meta($a['stored_name'] ?? '.pdf');
                  $viewUrl = url('/media/'.$a['id'].'/view');
                  $dUrl = url('/media/'.$a['id'].'/download');
              ?>
                <div class="media-item">
                  <div class="mi-icon"><?= $fm['icon'] ?></div>
                  <div class="mi-info">
                    <div class="mi-title"><?= e($a['original_name'] ?? '') ?></div>
                    <div class="mi-meta">
                      <span class="<?= $fm['class'] ?>"><?= $fm['label'] ?></span>
                      <?php if (!empty($a['size_bytes'])): ?><span class="muted-sm"><?= e(format_bytes((int)$a['size_bytes'])) ?></span><?php endif; ?>
                      <span class="muted-sm">· Subido: <?= e(format_date($a['created_at'] ?? '')) ?></span>
                    </div>
                  </div>
                  <div class="mi-actions">
                    <a class="btn btn-xs btn-outline-secondary" href="<?= $viewUrl ?>" target="_blank" rel="noopener">👁️</a>
                    <a class="btn btn-xs btn-outline-accent" href="<?= $dUrl ?>">⬇️</a>
                    <form method="post" action="<?= url('/admin/media/'.$a['id'].'/eliminar') ?>"
                          onsubmit="return confirm('¿Eliminar este archivo?')" style="display:inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="redirect_back" value="1">
                      <button class="btn btn-xs btn-outline-danger" type="submit">🗑️</button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header"><h3 class="card-title-sm mb-0">🖼️ Galería de imágenes</h3></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Subir imágenes (JPG, PNG, SVG)</label>
            <input type="file" name="gallery[]" multiple class="form-control form-control-sm"
                   accept=".jpg,.jpeg,.png,.svg,.webp">
          </div>
          <?php if ($isEdit && !empty($gallery)): ?>
            <h4 class="card-title-sm mt-4 mb-2">🖼️ Imágenes agregadas (para eliminar usa el botón rojo 🗑️ de la esquina superior derecha)</h4>
            <div class="gallery-grid admin-gallery mt-3" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;">
              <?php foreach ($gallery as $g):
                  $viewUrl = url('/media/'.$g['id'].'/view');
                  $isImg = file_is_image($g);
                  $thumbUrl = $isImg && !empty($g['stored_path']) ? upload_url($g['stored_path']) : $viewUrl;
              ?>
                <div class="gallery-item admin-gallery-item" style="position:relative;background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 4px 12px -6px rgba(15,23,42,.15);">
                  <a href="<?= $viewUrl ?>" target="_blank" rel="noopener" style="display:block;width:100%;aspect-ratio:1/1;overflow:hidden;background:#f1f5f9;">
                    <?php if ($isImg): ?>
                      <img src="<?= e($thumbUrl) ?>" alt="<?= e($g['original_name'] ?? '') ?>" loading="lazy"
                           style="width:100%;height:100%;object-fit:cover;"
                           onerror="this.onerror=null;this.src='<?= e($viewUrl) ?>';">
                    <?php else: ?>
                      <div class="agi-placeholder" style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f1f5f9;color:#64748b;">
                        <div class="agi-placeholder-icon" style="font-size:3rem;margin-bottom:10px;">📄</div>
                        <div class="agi-placeholder-name" style="font-size:.8rem;text-align:center;padding:0 12px;word-break:break-all;"><?= e($g['original_name'] ?? '') ?></div>
                      </div>
                    <?php endif; ?>
                  </a>
                  <?php
                  $fileName = trim($g['original_name'] ?? '');
                  if ($fileName === '') { $fileName = $g['stored_name'] ?? 'imagen'; }
                  $fileSize = !empty($g['size_bytes']) ? format_bytes((int)$g['size_bytes']) : '';
                  ?>
                  <div class="agi-label" style="padding:8px 12px 10px;border-top:1px solid #f1f5f9;background:#f8fafc;">
                    <div class="agi-name" style="font-size:.78rem;color:#0f172a;font-weight:600;line-height:1.35;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;"><?= e($fileName) ?></div>
                    <?php if ($fileSize !== ''): ?><div class="agi-size" style="font-size:.68rem;color:#64748b;margin-top:2px;"><?= e($fileSize) ?></div><?php endif; ?>
                  </div>
                  <form method="post" action="<?= url('/admin/media/'.$g['id'].'/eliminar') ?>"
                        class="agi-delete" onsubmit="return confirm('⚠️ ¿Estás SEGURO de eliminar esta imagen? Esta acción NO se puede deshacer.')"
                        style="position:absolute;top:8px;right:8px;z-index:30;display:block;margin:0;padding:0;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="redirect_back" value="1">
                    <button class="btn btn-xs btn-danger agi-delete-btn" type="submit"
                            title="Eliminar imagen del servidor"
                            style="background:#dc2626;color:#fff;border:2px solid #fff;border-radius:999px;padding:6px 8px;min-width:36px;min-height:36px;font-size:.9rem;line-height:1;box-shadow:0 6px 18px -4px rgba(220,38,38,.55);cursor:pointer;transition:transform .12s ease,background .12s ease;"
                            onmouseover="this.style.transform='scale(1.1)';this.style.background='#991b1b';"
                            onmouseout="this.style.transform='scale(1)';this.style.background='#dc2626';">🗑️</button>
                  </form>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card mb-3 d-lg-none">
        <div class="card-body">
          <div class="d-grid gap-2">
            <button type="submit" name="action" value="save" class="btn btn-primary btn-lg"><span>💾</span><span><?= $isEdit ? 'Guardar cambios' : 'Crear publicación' ?></span></button>
            <button type="submit" name="save_and_continue" value="1" class="btn btn-outline-primary"><span>💾</span><span>Guardar y seguir editando</span></button>
            <a class="btn btn-outline-secondary" href="<?= url('/admin/publicaciones') ?>">Cancelar</a>
          </div>
        </div>
      </div>

    </form>

    <?php if ($isEdit): ?>
      <form method="post" action="<?= url('/admin/publicaciones/'.((int)$pub['id']).'/eliminar') ?>"
            onsubmit="return confirm('⚠️ ¿Eliminar permanentemente esta publicación? Esta acción NO se puede deshacer.')"
            class="mb-3 d-lg-none" style="display:flex;justify-content:flex-end;">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger">🗑️ Eliminar publicación</button>
      </form>
    <?php endif; ?>

  </div>

  <div id="publicationsFormColSide" class="sticky-top" style="top:1rem;">

    <div class="card mb-3">
      <div class="card-header"><h3 class="card-title-sm mb-0">🏷️ Clasificación</h3></div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Categoría</label>
          <select name="category_id" class="form-select" form="publicationsFormMain">
            <option value="0">— Sin categoría —</option>
            <?php foreach ($categories as $c): $sel = (int)($oldFn('category_id', 0)) === (int)$c['id'] ? 'selected' : ''; ?>
              <option value="<?= (int)$c['id'] ?>" <?= $sel ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Región / Ámbito</label>
          <select name="region_id" class="form-select" form="publicationsFormMain">
            <option value="0">— Nacional / Sin definir —</option>
            <?php foreach ($regions as $r): $sel = (int)($oldFn('region_id', 0)) === (int)$r['id'] ? 'selected' : ''; ?>
              <option value="<?= (int)$r['id'] ?>" <?= $sel ?>><?= e($r['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Año</label>
          <input type="number" name="year" min="2000" max="2100" class="form-control" form="publicationsFormMain"
                 value="<?= e($oldFn('year', date('Y'))) ?>">
        </div>
        <div class="mb-0">
          <label class="form-label">Etiquetas</label>
          <div class="chips-wrap">
            <?php foreach ($tags as $t):
                $checked = is_array($selectedTags) && in_array((int)$t['id'], $selectedTags) ? 'checked' : '';
            ?>
              <label class="chip-check">
                <input type="checkbox" name="tag_ids[]" value="<?= (int)$t['id'] ?>" <?= $checked ?> form="publicationsFormMain">
                <span>#<?= e($t['name']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header"><h3 class="card-title-sm mb-0">📅 Fechas</h3></div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Fecha de publicación</label>
          <input type="datetime-local" name="publish_date" class="form-control" form="publicationsFormMain"
                 value="<?= e(date('Y-m-d\TH:i', strtotime($oldFn('publish_date', date('Y-m-d H:i:s'))))) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Inicio trabajo de campo</label>
          <input type="date" name="fieldwork_start_date" class="form-control" form="publicationsFormMain"
                 value="<?= e($oldFn('fieldwork_start_date')) ?>">
        </div>
        <div class="mb-0">
          <label class="form-label">Fin trabajo de campo</label>
          <input type="date" name="fieldwork_end_date" class="form-control" form="publicationsFormMain"
                 value="<?= e($oldFn('fieldwork_end_date')) ?>">
        </div>
      </div>
    </div>

    <div class="card card-accent mb-3">
      <div class="card-body">
        <label class="form-check form-switch mb-2">
          <input type="checkbox" name="is_published" class="form-check-input" form="publicationsFormMain"
                 <?= (int)($oldFn('is_published', 1)) === 1 ? 'checked' : '' ?>>
          <span><strong>Publicado</strong></span>
        </label>
        <div class="muted-sm mb-3">Desmarca para dejar en borrador sin que sea visible en el sitio público.</div>
        <div class="d-grid gap-2 d-none d-lg-grid">
          <button type="submit" name="action" value="save" form="publicationsFormMain" class="btn btn-primary btn-lg"><span>💾</span><span><?= $isEdit ? 'Guardar cambios' : 'Crear publicación' ?></span></button>
          <button type="submit" name="save_and_continue" value="1" form="publicationsFormMain" class="btn btn-outline-primary"><span>💾</span><span>Guardar y seguir editando</span></button>
          <a class="btn btn-outline-secondary" href="<?= url('/admin/publicaciones') ?>">Cancelar</a>
        </div>
        <?php if ($isEdit): ?>
          <form method="post" action="<?= url('/admin/publicaciones/'.((int)$pub['id']).'/eliminar') ?>"
                onsubmit="return confirm('⚠️ ¿Eliminar permanentemente esta publicación? Esta acción NO se puede deshacer.')"
                class="mt-2 d-none d-lg-block" style="display:flex;justify-content:center;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger w-100">🗑️ Eliminar publicación</button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <div class="card card-accent mb-3">
      <div class="card-header"><h4 class="card-title-sm mb-0">💡 Buenas prácticas</h4></div>
      <div class="card-body">
        <ul class="mb-0 ps-3" style="color:#334155;font-size:.85rem;line-height:1.6;">
          <li style="margin-bottom:8px;">Título claro y descriptivo (máx. 250 caracteres).</li>
          <li style="margin-bottom:8px;">Resumen ejecutivo que sintetice hallazgos clave.</li>
          <li style="margin-bottom:8px;">Sube el archivo principal en PDF para máxima compatibilidad.</li>
          <li style="margin-bottom:8px;">Clasifica correctamente por categoría y región.</li>
          <li>Usa etiquetas para facilitar la búsqueda por temas.</li>
        </ul>
      </div>
    </div>

  </div>

</div>

<script src="<?= asset('js/wysiwyg.js') ?>"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var W = window.Wysiwyg || window.WYSIWYG;
    if (W) {
      var editors = document.querySelectorAll('[data-editor="wysiwyg"]');
      for (var i = 0; i < editors.length; i++) W.init(editors[i]);
    }
  });
</script>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
