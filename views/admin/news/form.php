<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<?php
$item = $item ?? null;
$isEdit = !empty($item);
$formAction = $isEdit
    ? url('/admin/noticias/'.((int)$item['id']).'/actualizar')
    : url('/admin/noticias/guardar');
$pageTitle  = $isEdit ? '✏️ Editar noticia #'.(int)$item['id'] : '✨ Crear nueva noticia';

$secciones = $secciones ?? (class_exists('App\Models\Section') ? \App\Models\Section::allActive() : []);
$tags      = $tags ?? (class_exists('App\Models\Tag') ? \App\Models\Tag::allActive() : []);
$selectedTags = $selectedTags ?? [];
$gallery   = $gallery ?? [];
$attachments = $attachments ?? [];

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
$publishDateVal = $oldFn('publish_date', date('Y-m-d H:i:s'));
$publishDateHtml = date('Y-m-d\TH:i', strtotime($publishDateVal));

$sectionNews = [];
foreach ($secciones as $s) {
    $type = $s['section_type'] ?? 'news';
    if ($type === 'news' || $type === 'noticias' || empty($type)) {
        $sectionNews[] = $s;
    }
}
if (empty($sectionNews)) { $sectionNews = $secciones; }
?>

<style>
  form[data-news-form="1"] .form-control,
  form[data-news-form="1"] .form-control-sm,
  form[data-news-form="1"] .form-control-lg,
  form[data-news-form="1"] .form-select,
  form[data-news-form="1"] .form-select-sm,
  form[data-news-form="1"] textarea,
  form[data-news-form="1"] .wysiwyg-editor,
  form[data-news-form="1"] input[type="text"],
  form[data-news-form="1"] input[type="number"],
  form[data-news-form="1"] input[type="search"],
  form[data-news-form="1"] input[type="email"],
  form[data-news-form="1"] input[type="url"],
  form[data-news-form="1"] input[type="password"],
  form[data-news-form="1"] input[type="date"],
  form[data-news-form="1"] input[type="datetime-local"] {
    color: #000000 !important;
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
  }
  form[data-news-form="1"] input::placeholder,
  form[data-news-form="1"] textarea::placeholder,
  form[data-news-form="1"] .form-control::placeholder,
  form[data-news-form="1"] .form-select::placeholder {
    color: #475569 !important;
    opacity: 1 !important;
  }
  form[data-news-form="1"] label.form-label {
    color: var(--bs-body-color, #0f172a) !important;
    font-weight: 600;
  }
  form[data-news-form="1"] .wysiwyg-editor,
  form[data-news-form="1"] .wysiwyg-wrap,
  form[data-news-form="1"] [contenteditable="true"],
  form[data-news-form="1"] .wysiwyg-content {
    color: #000000 !important;
    background: #ffffff !important;
  }
  form[data-news-form="1"] .wysiwyg-editor *,
  form[data-news-form="1"] .wysiwyg-wrap *,
  form[data-news-form="1"] [contenteditable="true"] *,
  form[data-news-form="1"] .wysiwyg-content * {
    color: #000000 !important;
    background-color: transparent;
  }
  form[data-news-form="1"] code {
    color: #be123c !important;
    background: #fef2f2 !important;
  }
  form[data-news-form="1"] .nav-tabs .nav-link { color: #0f172a !important; }
  form[data-news-form="1"] .tab-pane { color: #0f172a !important; }

  #newsFormColSide .form-control,
  #newsFormColSide .form-select,
  #newsFormColSide textarea,
  #newsFormColSide input[type="text"],
  #newsFormColSide input[type="number"],
  #newsFormColSide input[type="search"],
  #newsFormColSide input[type="email"],
  #newsFormColSide input[type="url"],
  #newsFormColSide input[type="password"],
  #newsFormColSide input[type="date"],
  #newsFormColSide input[type="datetime-local"] {
    color: #000000 !important;
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
  }
  #newsFormColSide label.form-label {
    color: #0f172a !important;
    font-weight: 600;
  }
  #newsFormColSide input::placeholder,
  #newsFormColSide textarea::placeholder {
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
      <h1 class="h-title mb-1"><?= e($pageTitle) ?></h1>
      <p class="muted mb-0">Completa los campos y guarda los cambios.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <?php if ($isEdit && !empty($item['slug'])): ?>
        <a class="btn btn-outline-secondary" href="<?= url('/noticias/'.$item['slug']) ?>" target="_blank">🌐 Ver en sitio</a>
      <?php endif; ?>
      <a class="btn btn-outline-secondary" href="<?= url('/admin/noticias') ?>">← Volver al listado</a>
    </div>
  </div>
</div>

<?php
$errors = method_exists('Session', 'flash') ? \Session::flash('errors') : null;
if (!empty($errors) && is_array($errors)): ?>
  <div class="alert alert-danger mb-3">
    <strong>⚠️ Por favor corrige los siguientes errores:</strong>
    <ul class="mb-0 mt-2">
      <?php foreach ($errors as $e): ?>
        <li><?= e(is_array($e) ? implode(', ', $e) : $e) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div id="newsFormRow">

  <div id="newsFormColMain">

    <form method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="needs-validation" novalidate
          data-news-form="1" id="newsFormMain">
      <?= csrf_field() ?>

      <div class="card mb-3">
        <div class="card-header">
          <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-general" role="tab">📋 General</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-contenido" role="tab">📝 Contenido</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-multimedia" role="tab">🖼️ Multimedia</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-seo" role="tab">🔍 SEO</a></li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content">

            <div class="tab-pane fade show active" id="tab-general" role="tabpanel">

              <div class="row g-3">
                <div class="col-12">
                  <div class="form-group">
                    <label class="form-label">Título *</label>
                    <input type="text" name="title" id="f_title" required minlength="5" maxlength="250"
                           class="form-control form-control-lg" value="<?= e($oldFn('title')) ?>"
                           data-slug-source data-slug-target="#f_slug"
                           data-chars-max="250"
                           placeholder="Ej: Jornada de reforestación comunitaria: voluntarios recuperan áreas verdes">
                  </div>
                </div>

                <div class="col-md-8 col-12">
                  <div class="form-group">
                    <label class="form-label">
                      Slug (URL amigable)
                      <button type="button" id="slugAuto" class="btn btn-xs btn-outline-primary float-end"
                              data-source="f_title" data-target="f_slug">🔄 Generar del título</button>
                    </label>
                    <input type="text" name="slug" id="f_slug" maxlength="270"
                           class="form-control mono" value="<?= e($oldFn('slug')) ?>"
                           placeholder="autogenerado si vacío">
                    <div class="form-help">Solo minúsculas, números y guiones. Si se deja vacío se genera automáticamente.</div>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-group">
                    <label class="form-label">Fuente (nombre)</label>
                    <input type="text" name="source_name" class="form-control"
                           value="<?= e($oldFn('source_name')) ?>"
                           placeholder="Ej: Agencia EFE">
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-group">
                    <label class="form-label">Fuente (URL)</label>
                    <input type="url" name="source_url" class="form-control"
                           value="<?= e($oldFn('source_url')) ?>"
                           placeholder="https://…">
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-group">
                    <label class="form-label">🏷️ Etiquetas</label>
                    <div class="chips-wrap">
                      <?php foreach ($tags as $t):
                          $checked = is_array($selectedTags) && in_array((int)$t['id'], $selectedTags) ? 'checked' : '';
                      ?>
                        <label class="chip-check">
                          <input type="checkbox" name="tag_ids[]" value="<?= (int)$t['id'] ?>" <?= $checked ?>>
                          <span>#<?= e($t['name']) ?></span>
                        </label>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-contenido" role="tabpanel">

              <div class="form-group mb-3">
                <label class="form-label">Entradilla / Resumen corto</label>
                <textarea name="excerpt" rows="3" maxlength="500" class="form-control"
                          data-chars-max="500"
                          placeholder="Resumen breve que se muestra en tarjetas, home y resultados de búsqueda…"><?= e($oldFn('excerpt')) ?></textarea>
              </div>

              <div class="form-group mb-0">
                <label class="form-label">Cuerpo de la noticia (HTML)</label>
                <div class="wysiwyg-wrap">
                  <textarea name="content_html" id="f_content_html" class="d-none" data-editor-target="wysiwyg-content"><?= $oldFn('content_html') ?></textarea>
                  <div id="wysiwyg-content" class="wysiwyg-editor" data-editor="wysiwyg" data-sync-to="f_content_html"
                       style="min-height:420px"><?= $oldFn('content_html') ?></div>
                </div>
                <div class="form-help">Editor visual con formato. Pega desde Word o escribe directamente.</div>
              </div>

            </div>

            <div class="tab-pane fade" id="tab-multimedia" role="tabpanel">

              <div class="row g-4">
                <div class="col-12">
                  <div class="card card-sm">
                    <div class="card-header"><h4 class="card-title-sm mb-0">📎 Archivos adjuntos</h4></div>
                    <div class="card-body">
                      <div class="form-group">
                        <label class="form-label">Subir nuevos archivos (múltiple)</label>
                        <input type="file" name="attachments[]" multiple class="form-control form-control-sm"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png,.zip,.rar">
                        <div class="form-help">PDF, Word, Excel, CSV, imágenes, ZIP. Tamaño máx. por archivo según hosting.</div>
                      </div>

                      <?php if ($isEdit && !empty($attachments)): ?>
                        <h5 class="card-title-sm mt-3 mb-2">Archivos existentes</h5>
                        <div class="media-list">
                          <?php foreach ($attachments as $a):
                              $fm = function_exists('file_type_meta') ? file_type_meta($a['stored_name'] ?? '.pdf') : ['icon'=>'📄','label'=>'Archivo','class'=>''];
                              $viewUrl = url('/media/'.$a['id'].'/view');
                              $dUrl = url('/media/'.$a['id'].'/download');
                          ?>
                            <div class="media-item">
                              <div class="mi-icon"><?= $fm['icon'] ?></div>
                              <div class="mi-info">
                                <div class="mi-title"><?= e($a['original_name'] ?? '') ?></div>
                                <div class="mi-meta">
                                  <span class="<?= $fm['class'] ?>"><?= $fm['label'] ?></span>
                                  <?php if (!empty($a['size_bytes'])): ?>
                                    <span class="muted-sm">· <?= e(function_exists('format_bytes') ? format_bytes((int)$a['size_bytes']) : '') ?></span>
                                  <?php endif; ?>
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
                </div>

                <div class="col-12">
                  <div class="card card-sm">
                    <div class="card-header"><h4 class="card-title-sm mb-0">🖼️ Galería de imágenes</h4></div>
                    <div class="card-body">
                      <div class="form-group mb-3">
                        <label class="form-label">Subir imágenes a la galería (múltiple)</label>
                        <input type="file" name="gallery[]" multiple class="form-control form-control-sm"
                               accept=".jpg,.jpeg,.png,.webp,.svg">
                      </div>

                      <?php if ($isEdit && !empty($gallery)): ?>
                        <h5 class="card-title-sm mb-2">Imágenes de la galería (usa 🗑️ para eliminar)</h5>
                        <div class="gallery-grid admin-gallery mt-3" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;">
                          <?php foreach ($gallery as $g):
                              $viewUrl = url('/media/'.$g['id'].'/view');
                              $isImg = function_exists('file_is_image') ? file_is_image($g) : true;
                              $thumbUrl = $isImg && !empty($g['stored_path']) ? upload_url($g['stored_path']) : $viewUrl;
                          ?>
                            <div class="gallery-item admin-gallery-item" style="position:relative;background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
                              <a href="<?= $viewUrl ?>" target="_blank" rel="noopener" style="display:block;width:100%;aspect-ratio:1/1;overflow:hidden;background:#f1f5f9;">
                                <?php if ($isImg): ?>
                                  <img src="<?= e($thumbUrl) ?>" alt="<?= e($g['original_name'] ?? '') ?>" loading="lazy"
                                       style="width:100%;height:100%;object-fit:cover;"
                                       onerror="this.onerror=null;this.src='<?= e($viewUrl) ?>';">
                                <?php else: ?>
                                  <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">📄</div>
                                <?php endif; ?>
                              </a>
                              <div style="padding:6px 10px 10px;border-top:1px solid #f1f5f9;background:#f8fafc;">
                                <div style="font-size:.78rem;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                                  <?= e($g['original_name'] ?? '') ?>
                                </div>
                              </div>
                              <form method="post" action="<?= url('/admin/media/'.$g['id'].'/eliminar') ?>"
                                    onsubmit="return confirm('⚠️ ¿Eliminar esta imagen de la galería?')"
                                    style="position:absolute;top:8px;right:8px;z-index:30;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="redirect_back" value="1">
                                <button class="btn btn-xs btn-danger" type="submit" title="Eliminar imagen"
                                        style="border:2px solid #fff;border-radius:999px;padding:4px 7px;box-shadow:0 4px 12px -4px rgba(220,38,38,.6);">🗑️</button>
                              </form>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <div class="tab-pane fade" id="tab-seo" role="tabpanel">

              <div class="form-group mb-3">
                <label class="form-label">Meta Title (Título SEO)</label>
                <input type="text" name="meta_title" class="form-control" maxlength="120"
                       value="<?= e($oldFn('meta_title')) ?>"
                       data-chars-max="120"
                       placeholder="Título que se muestra en Google (50-60 caracteres recomendado)">
              </div>

              <div class="form-group mb-3">
                <label class="form-label">Meta Description (Descripción SEO)</label>
                <textarea name="meta_desc" rows="3" maxlength="320" class="form-control"
                          data-chars-max="320"
                          placeholder="Descripción que aparece en resultados de búsqueda (150-160 caracteres recomendado)"><?= e($oldFn('meta_desc')) ?></textarea>
              </div>

              <div class="form-group mb-0">
                <label class="form-label">Meta Keywords (palabras clave, separadas por coma)</label>
                <textarea name="meta_keywords" rows="2" class="form-control mono"
                          placeholder="noticias, Amigos por el Mundo, solidaridad, educación, medio ambiente"><?= e($oldFn('meta_keywords')) ?></textarea>
              </div>

            </div>

          </div>
        </div>
        <div class="card-footer d-flex gap-2 flex-wrap justify-end">
          <a class="btn btn-secondary" href="<?= url('/admin/noticias') ?>">↩️ Cancelar</a>
          <?php if ($isEdit): ?>
            <button type="submit" name="action" value="save_and_continue" class="btn btn-success">💾 Guardar y seguir editando</button>
            <button type="submit" name="action" value="save" class="btn btn-primary">✅ Guardar cambios</button>
          <?php else: ?>
            <button type="submit" name="action" value="save_and_continue" class="btn btn-success">💾 Crear y seguir editando</button>
            <button type="submit" name="action" value="save" class="btn btn-primary">✅ Crear noticia</button>
          <?php endif; ?>
        </div>
      </div>
    </form>

    <?php if ($isEdit): ?>
      <form method="post" action="<?= url('/admin/noticias/'.((int)$item['id']).'/eliminar') ?>"
            onsubmit="return confirm('⚠️ ¿Eliminar permanentemente esta noticia? Esta acción NO se puede deshacer.')"
            class="mb-3" style="display:flex;justify-content:flex-end;">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger">🗑️ Eliminar noticia</button>
      </form>
    <?php endif; ?>

  </div>

  <div id="newsFormColSide" class="sticky-top" style="top:1rem;">

    <div class="card mb-3">
      <div class="card-header"><h4 class="card-title-sm mb-0">🖼️ Imagen de portada</h4></div>
      <div class="card-body">
        <?php if ($isEdit && !empty($item['cover_image_path'])): ?>
          <input type="hidden" name="cover_image_path_existing" form="newsFormMain" value="<?= e($item['cover_image_path']) ?>">
        <?php endif; ?>

        <div id="cover_preview_wrap" style="<?= empty($coverUrl) ? 'display:none;' : '' ?>margin-bottom:12px;">
          <div style="position:relative;display:inline-block;max-width:100%;width:100%;aspect-ratio:16/9;background:#f1f5f9;border-radius:10px;border:2px solid #e2e8f0;overflow:hidden;">
            <img id="cover_preview_img" src="<?= e($coverUrl) ?>" alt="Preview portada"
                 style="width:100%;height:100%;object-fit:cover;">
            <button type="button" id="cover_remove_btn" class="btn btn-sm btn-danger"
                    style="position:absolute;top:8px;right:8px;">🗑️ Quitar</button>
          </div>
        </div>
        <input type="hidden" name="remove_cover_image" id="f_remove_cover_image" form="newsFormMain" value="0">

        <div class="form-group">
          <label class="form-label">Subir nueva imagen de portada</label>
          <input type="file" name="cover_image" id="f_cover_image" form="newsFormMain" class="form-control form-control-sm"
                 accept=".jpg,.jpeg,.png,.webp,.svg">
          <div class="form-help">Tamaño recomendado: 1200×675 px (ratio 16:9). JPG, PNG o WebP.</div>
        </div>

        <div class="form-group mb-0">
          <label class="form-label">Pie de foto / créditos portada</label>
          <input type="text" name="cover_caption" form="newsFormMain" class="form-control"
                 value="<?= e($oldFn('cover_caption')) ?>"
                 placeholder="Ej: Foto gentileza / Crédito: Redacción Amigos por el Mundo">
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header"><h4 class="card-title-sm mb-0">🚀 Estado y clasificación</h4></div>
      <div class="card-body">

        <div class="form-group mb-3">
          <label class="form-label">Sección *</label>
          <select name="section_id" id="f_section_id" required form="newsFormMain" class="form-select">
            <option value="">— Seleccionar sección —</option>
            <?php foreach ($sectionNews as $s):
                $sel = (int)($oldFn('section_id', 0)) === (int)$s['id'] ? 'selected' : '';
            ?>
              <option value="<?= (int)$s['id'] ?>" <?= $sel ?>><?= e($s['icon'] ?? '') ?> <?= e($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:16px;">
          <label class="form-check form-switch">
            <input type="checkbox" name="is_featured" form="newsFormMain" class="form-check-input"
                   <?= (int)($oldFn('is_featured', 0)) === 1 ? 'checked' : '' ?>>
            <span><strong>⭐ Noticia Destacada (Hero Home)</strong></span>
          </label>
          <div class="form-help ms-4" style="margin-top:-6px;">Aparece en el carrusel principal del home.</div>

          <label class="form-check form-switch">
            <input type="checkbox" name="is_breaking" form="newsFormMain" class="form-check-input"
                   <?= (int)($oldFn('is_breaking', 0)) === 1 ? 'checked' : '' ?>>
            <span><strong>🚨 ÚLTIMA HORA (ticker rojo)</strong></span>
          </label>
          <div class="form-help ms-4" style="margin-top:-6px;">Muestra en el ticker rojo superior del sitio.</div>

          <label class="form-check form-switch">
            <input type="checkbox" name="is_published" form="newsFormMain" class="form-check-input"
                   <?= (int)($oldFn('is_published', 0)) === 1 ? 'checked' : '' ?>>
            <span><strong>✅ Publicada</strong></span>
          </label>
          <div class="form-help ms-4" style="margin-top:-6px;">Desmarcado = borrador (no visible en sitio público).</div>
        </div>

        <div class="form-group mb-3">
          <label class="form-label">Fecha y hora de publicación</label>
          <input type="datetime-local" name="publish_date" form="newsFormMain" class="form-control"
                 value="<?= e($publishDateHtml) ?>">
        </div>

        <div class="form-group mb-0">
          <label class="form-label">Autor/a</label>
          <input type="text" name="author_name" form="newsFormMain" class="form-control"
                 value="<?= e($oldFn('author_name', auth_user()['name'] ?? '')) ?>"
                 placeholder="Nombre del periodista">
        </div>

      </div>
    </div>

    <div class="card card-accent mb-3">
      <div class="card-header"><h4 class="card-title-sm mb-0">💡 Buenas prácticas</h4></div>
      <div class="card-body">
        <ul class="mb-0 ps-3" style="color:#334155;font-size:.85rem;line-height:1.6;">
          <li style="margin-bottom:8px;">Usa un título claro y llamativo (máx. 250 caracteres).</li>
          <li style="margin-bottom:8px;">La entradilla resume la noticia en 1-2 frases (máx. 500 caracteres).</li>
          <li style="margin-bottom:8px;">Imagen de portada en formato 16:9 (1200×675 px recomendado).</li>
          <li style="margin-bottom:8px;">Completa Meta Title y Meta Description para SEO.</li>
          <li>Asigna al menos 2-3 etiquetas relacionadas.</li>
        </ul>
      </div>
    </div>

  </div>

</div>

<script src="<?= asset('js/wysiwyg.js') ?>"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    (function(){
      var groups = document.querySelectorAll('ul.nav-tabs.card-header-tabs');
      for (var g = 0; g < groups.length; g++) {
        var group = groups[g];
        var card = group.closest('.card');
        if (!card) continue;
        var panesWrap = card.querySelector(':scope > .card-body > .tab-content');
        if (!panesWrap) panesWrap = card.querySelector('.tab-content');
        if (!panesWrap) continue;
        var links = group.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
        function activate(target, skipFocus){
          var pane = panesWrap.querySelector(target);
          if (!pane) return;
          links.forEach(function(l){ l.classList.remove('active'); l.setAttribute('aria-selected','false'); });
          panesWrap.querySelectorAll('.tab-pane').forEach(function(p){
            p.classList.remove('show','active');
          });
          var chosen = Array.prototype.filter.call(links, function(l){ return l.getAttribute('href') === target; })[0];
          if (chosen) { chosen.classList.add('active'); chosen.setAttribute('aria-selected','true'); }
          pane.classList.add('show','active');
          if (!skipFocus && chosen) try { chosen.focus({preventScroll:true}); } catch(e){}
        }
        links.forEach(function(l){
          l.addEventListener('click', function(ev){
            ev.preventDefault(); ev.stopPropagation();
            var href = l.getAttribute('href') || '';
            if (!href) return;
            activate(href, false);
          });
        });
      }
    })();

    var W = window.Wysiwyg || window.WYSIWYG;
    if (W) {
      var editors = document.querySelectorAll('[data-editor="wysiwyg"]');
      for (var i = 0; i < editors.length; i++) W.init(editors[i]);
    }

    var wrap   = document.getElementById('cover_preview_wrap');
    var img    = document.getElementById('cover_preview_img');
    var fileIn = document.getElementById('f_cover_image');
    var btnRm  = document.getElementById('cover_remove_btn');
    var rmHdn  = document.getElementById('f_remove_cover_image');

    if (btnRm && wrap && rmHdn && img) {
      btnRm.addEventListener('click', function(e) {
        e.preventDefault();
        img.removeAttribute('src');
        wrap.style.display = 'none';
        try { fileIn.value = ''; } catch(_) {}
        rmHdn.value = '1';
      });
    }
    if (fileIn && img && wrap && rmHdn) {
      fileIn.addEventListener('change', function() {
        var f = fileIn.files && fileIn.files[0];
        if (!f) return;
        if (!/^image\//i.test(f.type || '')) {
          alert('Selecciona una imagen válida (JPG, PNG, WebP o SVG).');
          try { fileIn.value = ''; } catch(_) {}
          return;
        }
        rmHdn.value = '0';
        var r = new FileReader();
        r.onload = function(ev) {
          img.setAttribute('src', ev.target.result);
          wrap.style.display = 'block';
        };
        r.readAsDataURL(f);
      });
    }
  });
</script>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
