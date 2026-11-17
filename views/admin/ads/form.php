<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<?php
  $isEdit = !empty($item);
  $id = $isEdit ? (int)$item['id'] : 0;
  $formAction = $isEdit ? url('/admin/publicidad/'.$id.'/actualizar') : url('/admin/publicidad/guardar');
  $title = $isEdit ? 'Editar publicidad #'.$id : 'Nueva publicidad';
  $valFn = static function($k, $def = '') use ($item) {
      $v = old($k);
      if ($v !== '' && $v !== null) return $v;
      if ($item && array_key_exists($k, $item)) return $item[$k];
      return $def;
  };
  $imgPath = $valFn('image_path');
  $imgPreview = '';
  if (!empty($imgPath)) {
      $imgPreview = (str_starts_with($imgPath, '/') || preg_match('#^https?://#i', $imgPath)) ? $imgPath : upload_url($imgPath);
  }
?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1"><?= e($title) ?></h1>
    </div>
    <a class="btn btn-outline-secondary" href="<?= url('/admin/publicidad') ?>">← Volver</a>
  </div>
</div>

<div class="row justify-center">
  <div class="col-lg-8 col-12">
    <form method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="card" novalidate>
      <?= csrf_field() ?>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-lg-7 col-12">
            <label class="form-label">Título / Campaña *</label>
            <input type="text" name="title" required minlength="2" maxlength="180"
                   class="form-control form-control-lg"
                   value="<?= e($valFn('title')) ?>"
                   placeholder="Ej. Banner Superior - Elecciones 2026">
          </div>
          <div class="col-lg-5 col-12">
            <label class="form-label">Posición *</label>
            <select name="position" class="form-control form-control-lg">
              <?php foreach ($positions as $pk => $pv): ?>
                <option value="<?= e($pk) ?>" <?= $valFn('position', 'top-banner') === $pk ? 'selected' : '' ?>><?= e($pv) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6 col-12">
            <label class="form-label">Tipo de publicidad</label>
            <select name="ad_kind" id="ad_kind_sel" class="form-control">
              <?php foreach ($kinds as $kk => $kv): ?>
                <option value="<?= e($kk) ?>" <?= $valFn('ad_kind', 'image') === $kk ? 'selected' : '' ?>><?= e($kv) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6 col-12">
            <label class="form-check form-switch mt-4">
              <input type="checkbox" name="is_active" class="form-check-input"
                     <?= (int)$valFn('is_active', 1) === 1 ? 'checked' : '' ?>>
              <span>Activo (visible públicamente)</span>
            </label>
          </div>
        </div>

        <div id="ad_image_fields" class="mt-4">
          <div class="row g-3">
            <div class="col-md-7 col-12">
              <label class="form-label">🖼️ Imagen del banner (PNG/JPG/WebP/GIF)</label>
              <input type="file" name="image_file" accept="image/*" class="form-control">
              <small class="muted">Recomendado: 1200×140 (top) · 1000×180 (bottom) · 320×520/280 (sidebar)</small>
              <?php if ($isEdit): ?>
                <div class="mt-2"><small class="muted">Si no seleccionas nada, se mantiene la imagen actual.</small></div>
              <?php endif; ?>
            </div>
            <div class="col-md-5 col-12">
              <label class="form-label">Url de imagen (opcional, si no sube archivo)</label>
              <input type="text" name="image_path_text" class="form-control"
                     value="<?= e($imgPath) ?>"
                     placeholder="https://... o /ruta/local.png">
            </div>
            <?php if ($imgPreview !== ''): ?>
              <div class="col-12">
                <div style="border:1px dashed #cbd5e1;padding:.5rem;border-radius:8px;background:#f8fafc;display:inline-block;max-width:100%">
                  <div style="font-size:.7rem;margin-bottom:.3rem" class="muted">Vista previa actual:</div>
                  <img src="<?= e($imgPreview) ?>" alt="<?= e($valFn('alt_text', 'preview')) ?>" style="max-width:520px;max-height:180px;object-fit:contain;border-radius:6px;display:block">
                </div>
              </div>
            <?php endif; ?>
            <div class="col-md-6 col-12">
              <label class="form-label">URL destino (link al hacer click)</label>
              <input type="text" name="link_url" class="form-control" maxlength="500"
                     value="<?= e($valFn('link_url')) ?>"
                     placeholder="https://amigosporelmundo.org/contacto">
            </div>
            <div class="col-md-6 col-12">
              <label class="form-label">Texto alternativo (accesibilidad/SEO)</label>
              <input type="text" name="alt_text" class="form-control" maxlength="200"
                     value="<?= e($valFn('alt_text')) ?>"
                     placeholder="Ej: Solicita tu cotización Amigos por el Mundo 2026">
            </div>
          </div>
        </div>

        <div id="ad_html_fields" class="mt-4" style="display:none">
          <label class="form-label">Código HTML personalizado</label>
          <textarea name="html_code" rows="8" class="form-control mono" style="font-size:.82rem"
                    placeholder='<div style="background:#041b4e;color:#fff;padding:1rem">Mi banner...</div>'><?= e($valFn('html_code')) ?></textarea>
          <small class="muted">Se renderizará directamente sin escapado. Asegúrate de validar el HTML antes de publicar.</small>
        </div>

        <hr class="mt-4 mb-3">
        <div class="row g-3">
          <div class="col-md-4 col-12">
            <label class="form-label">📅 Fecha inicio (opcional)</label>
            <input type="datetime-local" name="start_at" class="form-control"
                   value="<?= $valFn('start_at') ? date('Y-m-d\TH:i', strtotime($valFn('start_at'))) : '' ?>">
          </div>
          <div class="col-md-4 col-12">
            <label class="form-label">🏁 Fecha fin (opcional)</label>
            <input type="datetime-local" name="end_at" class="form-control"
                   value="<?= $valFn('end_at') ? date('Y-m-d\TH:i', strtotime($valFn('end_at'))) : '' ?>">
          </div>
          <div class="col-md-2 col-6">
            <label class="form-label">🔢 Orden</label>
            <input type="number" name="sort_order" min="0" max="9999" step="1" class="form-control"
                   value="<?= e($valFn('sort_order', 0)) ?>">
          </div>
          <div class="col-md-2 col-6">
            <label class="form-label">👁️ Max. impresiones</label>
            <input type="number" name="max_views" min="0" step="1" class="form-control"
                   value="<?= $valFn('max_views') !== null && $valFn('max_views') !== '' ? (int)$valFn('max_views') : '' ?>"
                   placeholder="∞">
          </div>
        </div>
      </div>
      <div class="card-footer text-end">
        <a class="btn btn-outline-secondary" href="<?= url('/admin/publicidad') ?>">Cancelar</a>
        <button type="submit" class="btn btn-primary"><span>💾</span><span>Guardar</span></button>
      </div>
    </form>
  </div>
</div>

<script>
  (function(){
    var sel = document.getElementById('ad_kind_sel');
    var imgBox = document.getElementById('ad_image_fields');
    var htmlBox = document.getElementById('ad_html_fields');
    function toggle() {
      var kind = (sel.value || 'image');
      if (kind === 'html') {
        imgBox.style.display = 'none';
        htmlBox.style.display = 'block';
      } else {
        imgBox.style.display = 'block';
        htmlBox.style.display = 'none';
      }
    }
    sel.addEventListener('change', toggle);
    toggle();
  })();
</script>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
