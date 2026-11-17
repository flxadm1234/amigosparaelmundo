<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<?php
if (!function_exists('admin_icon')) {
    function admin_icon(string $k = 'video', int $size = 18, float $sw = 1.75): string {
        $stroke = htmlspecialchars('currentColor', ENT_QUOTES);
        $s = (int)$size;
        $w = number_format($sw, 2, '.', '');
        $base = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$s}\" height=\"{$s}\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"{$stroke}\" stroke-width=\"{$w}\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\">";
        $map = [
            'video'         => '<path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/>',
            'plus'          => '<path d="M5 12h14"/><path d="M12 5v14"/>',
            'pencil'        => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
            'globe-2'       => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
            'arrow-left'    => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
            'clipboard-list' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
            'film'          => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 3v18"/><path d="M3 7.5h4"/><path d="M3 12h18"/><path d="M3 16.5h4"/><path d="M17 3v18"/><path d="M17 7.5h4"/><path d="M17 16.5h4"/>',
            'search'        => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'file-text'     => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M8 13h8"/><path d="M8 17h8"/><path d="M8 9h2"/>',
            'youtube'       => '<path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>',
            'plug'          => '<path d="M9 2v6"/><path d="M15 2v6"/><path d="M6 8h12v4a6 6 0 1 1-12 0Z"/><path d="M12 18v4"/>',
            'upload'        => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>',
            'play'          => '<polygon points="6 3 20 12 6 21 6 3"/>',
            'image'         => '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
            'trash-2'       => '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/>',
            'save'          => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
            'check'         => '<polyline points="20 6 9 17 4 12"/>',
            'undo-2'        => '<path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"/>',
            'refresh-cw'    => '<path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/>',
            'toggle-right'  => '<rect x="1" y="5" width="18" height="14" rx="7" ry="7"/><circle cx="16" cy="12" r="3"/>',
            'calendar'      => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>',
            'clock'         => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            'rotate-cw'     => '<path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/>',
            'info'          => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
            'external-link' => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>',
            'alert-triangle' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        ];
        return $base . ($map[$k] ?? ($map['video'] ?? '')) . '</svg>';
    }
}

$item = $item ?? null;
$isEdit = !empty($item);
$formAction = $isEdit
    ? url('/admin/videos/' . ((int)$item['id']) . '/actualizar')
    : url('/admin/videos/guardar');
$deleteFormAction = $isEdit ? url('/admin/videos/' . ((int)$item['id']) . '/eliminar') : '';
$pageTitle = $isEdit ? 'Editar video #' . (int)$item['id'] : 'Crear nuevo video';
$pageIcon  = $isEdit ? 'pencil' : 'plus';
$pageBadgeBg = $isEdit ? 'rgba(59,130,246,.12)' : 'rgba(34,197,94,.12)';
$pageBadgeFg = $isEdit ? '#60A5FA' : '#4ADE80';
$previewUrl = $isEdit && !empty($item['slug']) ? url('/video/' . $item['slug']) : '';

$secciones = $sections ?? (class_exists('App\Models\Section') ? \App\Models\Section::activeOrdered() : []);
$sectionVideos = [];
foreach ($secciones as $s) {
    $st = (string)($s['section_type'] ?? '');
    if ($st === 'videos' || $st === 'news' || $st === '') {
        $sectionVideos[] = $s;
    }
}
if (empty($sectionVideos)) $sectionVideos = $secciones;

$errors = method_exists('App\Core\Session', 'flash') ? \App\Core\Session::flash('errors') : null;
$errsFlat = [];
if (is_array($errors)) {
    foreach ($errors as $k => $msgs) {
        if (is_int($k)) continue;
        $errsFlat[$k] = is_array($msgs) ? implode(', ', $msgs) : (string)$msgs;
    }
}
$hasErr = function(string $key) use ($errsFlat): string {
    return empty($errsFlat[$key]) ? '' : ' is-invalid';
};
$errMsg = function(string $key) use ($errsFlat): string {
    return empty($errsFlat[$key]) ? '' : $errsFlat[$key];
};

$oldFn = function(string $k, $def = '') use ($item, $errsFlat) {
    $v = old($k);
    if ($v !== '' && $v !== null) return $v;
    if ($item && is_array($item) && array_key_exists($k, $item)) return $item[$k];
    return $def;
};

$thumbUrl = '';
if ($isEdit && !empty($item['thumbnail_path'])) {
    $thumbUrl = upload_url((string)$item['thumbnail_path']);
}
$videoFileUrl = '';
if ($isEdit && !empty($item['video_path'])) {
    $videoFileUrl = upload_url((string)$item['video_path']);
}

$publishHtml = !empty($oldFn('publish_date'))
    ? date('Y-m-d\TH:i', is_numeric(strtotime((string)$oldFn('publish_date'))) ? (int)strtotime((string)$oldFn('publish_date')) : time())
    : date('Y-m-d\TH:i');
$videoType = (string)$oldFn('video_type', 'youtube');
?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div class="d-flex align-items-start gap-3">
      <div style="width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:<?= $pageBadgeBg ?>;color:<?= $pageBadgeFg ?>;flex-shrink:0;">
        <?= admin_icon($pageIcon, 22, 2) ?>
      </div>
      <div>
        <h1 class="h-title mb-1"><?= e($pageTitle) ?></h1>
        <p class="muted mb-0">Videos de YouTube, embebidos iframe o subidos directamente en MP4.</p>
      </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <?php if ($isEdit && !empty($previewUrl)): ?>
        <a class="rm-btn rm-btn-secondary d-none d-sm-inline-flex align-items-center gap-2" href="<?= e($previewUrl) ?>" target="_blank" rel="noopener">
          <?= admin_icon('external-link', 15, 1.9) ?>
          <span>Ver en sitio</span>
        </a>
      <?php endif; ?>
      <a class="rm-btn rm-btn-secondary d-inline-flex align-items-center gap-2" href="<?= e(url('/admin/videos')) ?>">
        <?= admin_icon('arrow-left', 15, 2) ?>
        <span>Volver al listado</span>
      </a>
    </div>
  </div>
</div>

<?php if (!empty($errsFlat)): ?>
  <div class="alert alert-danger mb-3" role="alert">
    <div class="d-flex gap-2 align-items-start">
      <div style="flex-shrink:0;margin-top:1px;color:#FCA5A5;"><?= admin_icon('alert-triangle', 18, 2) ?></div>
      <div>
        <strong class="d-block mb-1" style="color:#FEE2E2;">Errores de validación</strong>
        <ul class="mb-0 mt-1 pl-4">
          <?php foreach ($errsFlat as $k => $m): ?>
            <li style="color:#FECACA;"><strong><?= e(ucfirst($k)) ?>:</strong> <?= e($m) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if (method_exists('App\Core\Session', 'flash')):
    $flashSuccess = \App\Core\Session::flash('success');
    $flashError   = \App\Core\Session::flash('error');
  ?>
  <?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success mb-3" role="alert">
      <div class="d-flex gap-2 align-items-start">
        <div style="flex-shrink:0;margin-top:2px;color:#4ADE80;"><?= admin_icon('check', 18, 2) ?></div>
        <div><?= e(is_array($flashSuccess) ? implode(', ', $flashSuccess) : $flashSuccess) ?></div>
      </div>
    </div>
  <?php endif; ?>
  <?php if (!empty($flashError)): ?>
    <div class="alert alert-danger mb-3" role="alert">
      <div class="d-flex gap-2 align-items-start">
        <div style="flex-shrink:0;margin-top:2px;color:#FCA5A5;"><?= admin_icon('alert-triangle', 18, 2) ?></div>
        <div><?= e(is_array($flashError) ? implode(', ', $flashError) : $flashError) ?></div>
      </div>
    </div>
  <?php endif; ?>
<?php endif; ?>

<div class="row g-4" id="videosFormRow">
  <div class="col-lg-8 col-12" id="videosFormColMain">
    <form method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" id="videoFormMain" autocomplete="off" class="card">
      <?= csrf_field() ?>
      <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-inline-flex align-items-center gap-2">
          <span style="color:var(--rm-admin-accent);"><?= admin_icon('film', 18, 2) ?></span>
          <h3 class="card-title-sm mb-0">Formulario de video</h3>
        </div>
        <?php if ($isEdit): ?>
          <span class="badge badge-sm" style="background:rgba(59,130,246,.14);color:#60A5FA;border-color:rgba(59,130,246,.28);">ID #<?= (int)$item['id'] ?></span>
        <?php endif; ?>
      </div>
      <div class="card-body">

        <section class="mb-4">
          <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:1px solid var(--rm-admin-border);">
            <div class="section-kicker d-inline-flex align-items-center gap-2" style="font-size:.72rem;letter-spacing:.04em;text-transform:uppercase;color:var(--rm-admin-accent);font-weight:700;">
              <?= admin_icon('clipboard-list', 14, 2) ?>
              <span>Datos generales</span>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-12">
              <div class="form-group">
                <label class="form-label" for="f_title">Título del video <span class="text-danger">*</span></label>
                <input type="text" name="title" id="f_title" required minlength="5" maxlength="250"
                       class="form-control form-control-lg<?= $hasErr('title') ?>"
                       value="<?= e($oldFn('title')) ?>"
                       data-slug-source data-slug-target="#f_slug"
                       placeholder="Ej: Entrevista exclusiva con el candidato">
                <?php if (!empty($errsFlat['title'])): ?>
                  <div class="invalid-feedback"><?= e($errsFlat['title']) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <div class="col-md-8 col-12">
              <div class="form-group">
                <label class="form-label d-flex align-items-center justify-content-between gap-2" for="f_slug">
                  <span>Slug (URL amigable)</span>
                  <button type="button" class="rm-btn rm-btn-secondary rm-btn-sm d-inline-flex align-items-center gap-2 slug-from-name"
                          data-from="f_title" data-to="f_slug"
                          style="padding:.22rem .6rem;font-size:.74rem;">
                    <?= admin_icon('refresh-cw', 12, 2) ?>
                    <span>Generar</span>
                  </button>
                </label>
                <input type="text" name="slug" id="f_slug" maxlength="270"
                       class="form-control mono<?= $hasErr('slug') ?>"
                       value="<?= e($oldFn('slug')) ?>"
                       placeholder="autogenerado si se deja vacío">
                <?php if (!empty($errsFlat['slug'])): ?>
                  <div class="invalid-feedback"><?= e($errsFlat['slug']) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <div class="col-md-4 col-12">
              <div class="form-group">
                <label class="form-label" for="f_section">Sección</label>
                <select name="section_id" id="f_section" class="form-select">
                  <option value="0">Sin sección</option>
                  <?php foreach ($sectionVideos as $s):
                      $sel = (int)($oldFn('section_id', 0)) === (int)$s['id'] ? 'selected' : '';
                  ?>
                    <option value="<?= (int)$s['id'] ?>" <?= $sel ?>><?= e($s['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="col-12">
              <div class="form-group">
                <label class="form-label" for="f_description">Descripción</label>
                <textarea name="description" id="f_description" rows="4"
                          class="form-control<?= $hasErr('description') ?>"
                          placeholder="Descripción o resumen del video…"><?= e($oldFn('description')) ?></textarea>
              </div>
            </div>

            <div class="col-md-4 col-12">
              <div class="form-group">
                <label class="form-label" for="f_video_type">Tipo de video <span class="text-danger">*</span></label>
                <select name="video_type" id="f_video_type" required class="form-select"
                        onchange="toggleVideoFields(this.value)">
                  <option value="youtube" <?= $videoType === 'youtube' ? 'selected' : '' ?>>YouTube (URL)</option>
                  <option value="embed"   <?= $videoType === 'embed'   ? 'selected' : '' ?>>Embebido (iframe)</option>
                  <option value="upload"  <?= $videoType === 'upload'  ? 'selected' : '' ?>>Subir archivo MP4</option>
                </select>
              </div>
            </div>

            <div class="col-md-4 col-12 d-flex align-items-end">
              <label class="form-check form-switch" style="padding-top:.25rem;">
                <input type="checkbox" name="is_published" class="form-check-input"
                       <?= (int)($oldFn('is_published', 0)) === 1 ? 'checked' : '' ?>>
                <span><strong style="color:var(--rm-admin-text-1);">Publicado</strong></span>
              </label>
            </div>

            <div class="col-md-4 col-12">
              <div class="form-group mb-0">
                <label class="form-label d-inline-flex align-items-center gap-2" for="f_publish_date">
                  <?= admin_icon('calendar', 14, 2) ?><span>Fecha publicación</span>
                </label>
                <input type="datetime-local" name="publish_date" id="f_publish_date" class="form-control" value="<?= e($publishHtml) ?>">
              </div>
            </div>
          </div>
        </section>

        <section class="mb-4">
          <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:1px solid var(--rm-admin-border);">
            <div class="section-kicker d-inline-flex align-items-center gap-2" style="font-size:.72rem;letter-spacing:.04em;text-transform:uppercase;color:var(--rm-admin-accent);font-weight:700;">
              <?= admin_icon('film', 14, 2) ?>
              <span>Multimedia</span>
            </div>
          </div>
          <div class="row g-4">
            <div class="col-12">

              <div id="div-youtube" class="subcard mb-3" style="display:<?= $videoType === 'youtube' ? 'block' : 'none' ?>;">
                <div class="form-group">
                  <label class="form-label d-inline-flex align-items-center gap-2" for="f_yt">
                    <?= admin_icon('youtube', 15, 2) ?><span>URL de YouTube <span class="text-danger">*</span></span>
                  </label>
                  <input type="url" name="embed_url" id="f_yt" class="form-control mono<?= $hasErr('embed_url') ?>"
                         value="<?= e($oldFn('embed_url')) ?>"
                         placeholder="https://www.youtube.com/watch?v=XXXXXXXXXXX o https://youtu.be/XXXXXXXXXXX">
                  <div class="form-help">Pega la URL completa del video de YouTube.</div>
                  <?php if (!empty($errsFlat['embed_url'])): ?>
                    <div class="invalid-feedback d-block"><?= e($errsFlat['embed_url']) ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <div id="div-embed" class="subcard mb-3" style="display:<?= $videoType === 'embed' ? 'block' : 'none' ?>;">
                <div class="form-group">
                  <label class="form-label d-inline-flex align-items-center gap-2" for="f_embed">
                    <?= admin_icon('plug', 15, 2) ?><span>Código iframe embebido <span class="text-danger">*</span></span>
                  </label>
                  <textarea name="embed_url" id="f_embed" rows="5"
                            class="form-control mono font-sm<?= $hasErr('embed_url') ?>"
                            placeholder="<iframe src=... ></iframe>"><?= e($oldFn('embed_url')) ?></textarea>
                  <div class="form-help">Pega el código iframe completo de Vimeo, Dailymotion, etc.</div>
                  <?php if (!empty($errsFlat['embed_url'])): ?>
                    <div class="invalid-feedback d-block"><?= e($errsFlat['embed_url']) ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <div id="div-video-preview" class="subcard mb-3" style="display:<?= $videoType === 'upload' ? 'none' : 'block' ?>;">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                  <label class="form-label d-inline-flex align-items-center gap-2 mb-0">
                    <?= admin_icon('play-circle', 14, 2) ?><strong style="color:var(--rm-admin-text-1);">Vista previa</strong>
                  </label>
                  <span id="f_vp_status" class="muted-sm" style="font-size:.76rem;">—</span>
                </div>
                <div id="f_video_preview_wrap"
                     style="position:relative;aspect-ratio:16/9;width:100%;border-radius:12px;overflow:hidden;background:#000;border:1px solid var(--rm-admin-border-2);">
                  <div id="f_video_preview_empty"
                       style="position:absolute;inset:0;display:inline-flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;color:var(--rm-gray-300);background:linear-gradient(135deg,#0B1220 0%,#0A2540 100%);font-size:.9rem;letter-spacing:.01em;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:58px;height:58px;border-radius:50%;background:rgba(255,255,255,.06);color:#60A5FA;border:1px solid rgba(96,165,250,.22);">
                      <?= admin_icon('play-circle', 30, 2) ?>
                    </span>
                    <div style="text-align:center;line-height:1.55;">
                      Ingresa la URL de YouTube <em style="opacity:.82;">o</em> pega el código iframe<br/>
                      para ver aquí la vista previa del video.
                    </div>
                  </div>
                  <iframe id="f_video_preview_iframe"
                          title="Vista previa de video"
                          loading="lazy"
                          allowfullscreen
                          referrerpolicy="no-referrer-when-downgrade"
                          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                          style="position:absolute;inset:0;width:100%;height:100%;border:0;display:none;background:#000;"></iframe>
                </div>
                <div class="form-help mt-2 mb-0" style="font-size:.78rem;">
                  Tip YouTube: cualquier enlace funciona (watch?v=, youtu.be, /shorts/, /live/, /embed/).
                </div>
              </div>

              <div id="div-upload" class="subcard mb-3" style="display:<?= $videoType === 'upload' ? 'block' : 'none' ?>;">
                <?php if ($isEdit && !empty($item['video_path'])): ?>
                  <input type="hidden" name="video_path_existing" value="<?= e($item['video_path']) ?>">
                  <div class="mb-3 p-3 rounded" style="background:var(--rm-admin-elevated);border:1px solid var(--rm-admin-border);">
                    <div class="muted-sm mb-2 d-inline-flex align-items-center gap-2">
                      <?= admin_icon('info', 14, 2) ?>
                      <span>Archivo actual:</span>
                    </div>
                    <div class="mono text-sm" style="color:var(--rm-admin-text-2);word-break:break-all;"><?= e($item['video_path']) ?></div>
                    <video src="<?= e($videoFileUrl) ?>" controls preload="metadata" playsinline
                           style="width:100%;max-height:260px;margin-top:12px;border-radius:8px;border:1px solid var(--rm-admin-border);background:#000;"></video>
                  </div>
                <?php endif; ?>
                <div class="form-group">
                  <label class="form-label d-inline-flex align-items-center gap-2" for="f_video_file">
                    <?= admin_icon('upload', 15, 2) ?><span>Subir video (MP4) <?= $isEdit ? '' : '<span class="text-danger">*</span>' ?></span>
                  </label>
                  <input type="file" name="video_file" id="f_video_file" accept="video/mp4,video/webm,video/ogg"
                         class="form-control form-control-sm" <?= $isEdit ? '' : 'required' ?>>
                  <div class="form-help">Formatos recomendados: MP4 H.264 / WebM. Tamaño máximo según configuración PHP (upload_max_filesize).</div>
                </div>
              </div>

              <div class="form-group mt-2 mb-0">
                <label class="form-label d-inline-flex align-items-center gap-2" for="f_duration">
                  <?= admin_icon('clock', 15, 2) ?><span>Duración (mm:ss)</span>
                </label>
                <input type="text" name="duration" id="f_duration"
                       class="form-control mono text-center<?= $hasErr('duration') ?>"
                       style="max-width:220px;"
                       value="<?= e($oldFn('duration', '00:00')) ?>"
                       pattern="[0-9]+:[0-5][0-9]"
                       placeholder="Ej: 05:32 o 01:23:45">
                <?php if (!empty($errsFlat['duration'])): ?>
                  <div class="invalid-feedback d-block"><?= e($errsFlat['duration']) ?></div>
                <?php endif; ?>
              </div>

            </div>
          </div>
        </section>

        <section class="mb-0">
          <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:1px solid var(--rm-admin-border);">
            <div class="section-kicker d-inline-flex align-items-center gap-2" style="font-size:.72rem;letter-spacing:.04em;text-transform:uppercase;color:var(--rm-admin-accent);font-weight:700;">
              <?= admin_icon('search', 14, 2) ?>
              <span>SEO</span>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-12">
              <div class="form-group">
                <label class="form-label d-flex align-items-center justify-content-between gap-2" for="f_mt">
                  <span>Meta Title</span>
                  <span id="f_mt_count" class="muted-sm" style="font-variant-numeric:tabular-nums;"></span>
                </label>
                <input type="text" name="meta_title" id="f_mt" class="form-control" maxlength="120"
                       value="<?= e($oldFn('meta_title')) ?>"
                       placeholder="Título SEO (50-60 caracteres recomendado)">
                <div class="form-help">Título que se muestra en resultados de buscadores y pestaña.</div>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group mb-0">
                <label class="form-label d-flex align-items-center justify-content-between gap-2" for="f_md">
                  <span>Meta Description</span>
                  <span id="f_md_count" class="muted-sm" style="font-variant-numeric:tabular-nums;"></span>
                </label>
                <textarea name="meta_desc" id="f_md" rows="3" maxlength="320" class="form-control"
                          placeholder="Descripción SEO (150-160 caracteres recomendado)"><?= e($oldFn('meta_desc')) ?></textarea>
                <div class="form-help">Resumen que aparece bajo el título en buscadores. Sé claro y directo.</div>
              </div>
            </div>
          </div>
        </section>

      </div>
      <div class="card-footer d-flex gap-2 flex-wrap justify-content-between align-items-center">
        <div class="d-flex gap-2 flex-wrap align-items-center">
          <?php if ($isEdit && !empty($deleteFormAction)): ?>
            <form method="post" action="<?= e($deleteFormAction) ?>"
                  onsubmit="return confirm('¿Eliminar PERMANENTEMENTE este video?');"
                  id="videoFormDelete" style="display:inline-flex;margin:0;padding:0;border:0;background:transparent;">
              <?= csrf_field() ?>
              <button type="submit" class="rm-btn rm-btn-danger d-inline-flex align-items-center gap-2">
                <?= admin_icon('trash-2', 15, 1.9) ?>
                <span>Eliminar</span>
              </button>
            </form>
          <?php endif; ?>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center justify-content-end ml-auto">
          <a class="rm-btn rm-btn-secondary d-inline-flex align-items-center gap-2" href="<?= e(url('/admin/videos')) ?>">
            <?= admin_icon('undo-2', 15, 2) ?>
            <span>Cancelar</span>
          </a>
          <button type="submit" name="action" value="save_and_continue" class="rm-btn rm-btn-secondary d-inline-flex align-items-center gap-2">
            <?= admin_icon('save', 15, 2) ?>
            <span>Guardar y seguir editando</span>
          </button>
          <button type="submit" name="action" value="save" class="rm-btn rm-btn-primary d-inline-flex align-items-center gap-2">
            <?= admin_icon('check', 15, 2.1) ?>
            <span>Guardar cambios</span>
          </button>
        </div>
      </div>
    </form>
  </div>

  <div class="col-lg-4 col-12" id="videosFormColSide">
    <div class="card sticky-top mb-3" style="top:1rem;">
      <div class="card-header d-flex align-items-center gap-2">
        <?= admin_icon('image', 16, 2) ?>
        <h4 class="card-title-sm mb-0">Thumbnail / Portada</h4>
      </div>
      <div class="card-body">
        <?php if ($isEdit && !empty($item['thumbnail_path'])): ?>
          <input type="hidden" name="thumbnail_path_existing" value="<?= e($item['thumbnail_path']) ?>" form="videoFormMain">
        <?php endif; ?>
        <input type="hidden" name="remove_thumbnail" id="f_remove_thumbnail" value="0" form="videoFormMain">

        <div id="thumb_wrap" style="<?= empty($thumbUrl) ? 'display:none;' : '' ?>margin-bottom:14px;">
          <div style="position:relative;display:block;max-width:100%;">
            <img id="thumb_img" src="<?= e($thumbUrl) ?>" alt="Preview thumbnail"
                 style="width:100%;height:auto;max-height:260px;object-fit:cover;border-radius:10px;border:1px solid var(--rm-admin-border-2);display:block;background:var(--rm-admin-elevated);">
            <button type="button" id="thumb_remove" class="rm-btn rm-btn-danger rm-btn-sm d-inline-flex align-items-center gap-2"
                    aria-label="Quitar thumbnail"
                    style="position:absolute;top:8px;right:8px;padding:.28rem .6rem;font-size:.74rem;box-shadow:0 4px 14px rgba(190,18,60,.35);">
              <?= admin_icon('trash-2', 13, 2) ?>
              <span>Quitar</span>
            </button>
          </div>
        </div>

        <div class="form-group mb-0">
          <label class="form-label d-inline-flex align-items-center gap-2" for="f_thumb_file">
            <?= admin_icon('upload', 14, 2) ?><span>Subir thumbnail</span>
          </label>
          <input type="file" name="thumbnail_file" id="f_thumb_file" accept=".jpg,.jpeg,.png,.webp"
                 class="form-control form-control-sm" form="videoFormMain">
          <div class="form-help mb-0">Tamaño recomendado: <strong>1280×720 px (16:9)</strong>. Formatos JPG / PNG / WebP.</div>
        </div>
      </div>
    </div>

    <div class="card sticky-top" style="top:calc(1rem + 360px);">
      <div class="card-header d-flex align-items-center gap-2">
        <?= admin_icon('info', 16, 2) ?>
        <h4 class="card-title-sm mb-0">Buenas prácticas</h4>
      </div>
      <div class="card-body">
        <ul class="mb-0 pl-4" style="font-size:.88rem;line-height:1.75;color:var(--rm-admin-text-2);">
          <li>Títulos: 6-12 palabras, clave al principio.</li>
          <li>Descripción: explica de qué trata el video en 2 líneas.</li>
          <li>Thumbnail nítido, texto grande, contraste alto (click-through rate).</li>
          <li>Duración en <strong>mm:ss</strong> (ej: <code>08:15</code>).</li>
          <li>Usa <strong>MP4 H.264</strong> si subes archivo (mejor compatibilidad).</li>
          <li>SEO: completa Meta Title + Description para buscadores.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
  function toggleVideoFields(val) {
    var y = document.getElementById('div-youtube');
    var e = document.getElementById('div-embed');
    var u = document.getElementById('div-upload');
    var p = document.getElementById('div-video-preview');
    var yt = document.getElementById('f_yt');
    var em = document.getElementById('f_embed');
    var up = document.getElementById('f_video_file');
    if (y)  y.style.display  = val === 'youtube' ? 'block' : 'none';
    if (e)  e.style.display  = val === 'embed'   ? 'block' : 'none';
    if (u)  u.style.display  = val === 'upload'  ? 'block' : 'none';
    if (p)  p.style.display  = val === 'upload'  ? 'none'  : 'block';
    if (yt) yt.disabled      = val !== 'youtube';
    if (em) em.disabled      = val !== 'embed';
    if (up) up.disabled      = val !== 'upload';
    try { renderVideoPreview(val); } catch(_) {}
  }

  function rmvExtractYoutubeId(val) {
    if (!val) return '';
    var s = String(val).trim();
    if (/<iframe[\s\S]*?>/i.test(s)) {
      var m = s.match(/src\s*=\s*["']([^"']+)["']/i);
      if (m) s = m[1];
    }
    var patterns = [
      /(?:youtube\.com|youtube-nocookie\.com)\/embed\/([A-Za-z0-9_-]{6,})/,
      /(?:youtube\.com|youtube-nocookie\.com)\/shorts\/([A-Za-z0-9_-]{6,})/,
      /(?:youtube\.com|youtube-nocookie\.com)\/live\/([A-Za-z0-9_-]{6,})/,
      /(?:youtube\.com|youtube-nocookie\.com)\/watch\?(?:[^&]*&)*v=([A-Za-z0-9_-]{6,})/,
      /youtu\.be\/([A-Za-z0-9_-]{6,})/,
    ];
    for (var i = 0; i < patterns.length; i++) {
      var res = patterns[i].exec(s);
      if (res && res[1]) return res[1];
    }
    return '';
  }

  function rmvExtractVimeoId(val) {
    if (!val) return '';
    var s = String(val).trim();
    if (/<iframe[\s\S]*?>/i.test(s)) {
      var m = s.match(/src\s*=\s*["']([^"']+)["']/i);
      if (m) s = m[1];
    }
    var m2 = s.match(/(?:player\.)?vimeo\.com\/(?:video\/)?(\d{5,})/i);
    return (m2 && m2[1]) ? m2[1] : '';
  }

  function rmvExtractIframeSrc(val) {
    if (!val) return '';
    var s = String(val).trim();
    var m = s.match(/<iframe[\s\S]*?src\s*=\s*["']([^"']+)["'][\s\S]*?>/i);
    if (m && m[1]) return m[1].trim();
    if (/^https?:\/\//i.test(s)) return s;
    return '';
  }

  function renderVideoPreview(forcedType) {
    var typeSel = document.getElementById('f_video_type');
    var type = forcedType || (typeSel ? typeSel.value : 'youtube');
    var yt = document.getElementById('f_yt');
    var em = document.getElementById('f_embed');
    var wrap = document.getElementById('f_video_preview_wrap');
    var empty = document.getElementById('f_video_preview_empty');
    var iframe = document.getElementById('f_video_preview_iframe');
    var status = document.getElementById('f_vp_status');
    var block = document.getElementById('div-video-preview');
    if (!wrap || !iframe || !empty) return;
    if (block && block.style.display === 'none') { empty.style.display = 'inline-flex'; iframe.style.display = 'none'; iframe.removeAttribute('src'); return; }
    var rawValue = '';
    if (type === 'youtube' && yt) rawValue = (yt.value || '').trim();
    else if (type === 'embed' && em) rawValue = (em.value || '').trim();
    if (!rawValue) {
      empty.style.display = 'inline-flex';
      iframe.style.display = 'none';
      iframe.removeAttribute('src');
      if (status) status.textContent = 'Esperando URL…';
      return;
    }
    var ytId = rmvExtractYoutubeId(rawValue);
    var viId = rmvExtractVimeoId(rawValue);
    var finalSrc = '';
    var label = '';
    if (ytId) {
      finalSrc = 'https://www.youtube.com/embed/' + encodeURIComponent(ytId) + '?rel=0&modestbranding=1&playsinline=1';
      label = 'YouTube · ID ' + ytId;
    } else if (viId) {
      finalSrc = 'https://player.vimeo.com/video/' + encodeURIComponent(viId) + '?title=0&byline=0&portrait=0';
      label = 'Vimeo · ID ' + viId;
    } else {
      var src = rmvExtractIframeSrc(rawValue);
      if (src) { finalSrc = src; try { label = 'Embed · ' + (new URL(src)).hostname; } catch(e){ label = 'Embed · externo'; } }
    }
    if (!finalSrc) {
      empty.style.display = 'inline-flex';
      iframe.style.display = 'none';
      iframe.removeAttribute('src');
      if (status) status.textContent = 'No se reconoció URL. Revisa el enlace.';
      return;
    }
    if (status) status.textContent = label;
    empty.style.display = 'none';
    iframe.style.display = 'block';
    iframe.setAttribute('src', finalSrc);
  }

  function bindCounter(inputId, counterId, max, min, opt) {
    var inp = document.getElementById(inputId);
    var out = document.getElementById(counterId);
    if (!inp || !out) return;
    function upd() {
      var n = (inp.value || '').length;
      var ok = n === 0 || (n >= min && n <= max);
      out.textContent = n + ' / ' + max + (opt ? ' (' + opt + ')' : '');
      out.style.color = ok ? 'var(--rm-admin-text-4)' : (n > max ? '#F87171' : '#F59E0B');
      out.style.fontWeight = '600';
    }
    inp.addEventListener('input', upd);
    upd();
  }

  document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('f_video_type');
    if (sel) toggleVideoFields(sel.value);

    var wrap = document.getElementById('thumb_wrap');
    var img  = document.getElementById('thumb_img');
    var fin  = document.getElementById('f_thumb_file');
    var btnR = document.getElementById('thumb_remove');
    var rmH  = document.getElementById('f_remove_thumbnail');

    if (btnR && wrap && rmH && img) {
      btnR.addEventListener('click', function(e) {
        e.preventDefault();
        img.removeAttribute('src');
        wrap.style.display = 'none';
        try { fin.value = ''; } catch(_) {}
        rmH.value = '1';
      });
    }
    if (fin && img && wrap && rmH) {
      fin.addEventListener('change', function() {
        var f = fin.files && fin.files[0];
        if (!f) return;
        if (!/^image\//i.test(f.type || '')) {
          alert('Selecciona una imagen válida (JPG, PNG, WebP).');
          try { fin.value = ''; } catch(_) {} return;
        }
        rmH.value = '0';
        var r = new FileReader();
        r.onload = function(ev) { img.setAttribute('src', ev.target.result); wrap.style.display = 'block'; };
        r.readAsDataURL(f);
      });
    }

    bindCounter('f_mt', 'f_mt_count', 60, 45, 'recomendado 50-60');
    bindCounter('f_md', 'f_md_count', 170, 130, 'recomendado 150-160');

    var ytInp = document.getElementById('f_yt');
    var emInp = document.getElementById('f_embed');
    if (ytInp) ytInp.addEventListener('input', function () { try { renderVideoPreview('youtube'); } catch(_) {} });
    if (emInp) emInp.addEventListener('input', function () { try { renderVideoPreview('embed'); } catch(_) {} });
    try { renderVideoPreview(null); } catch(_) {}

    var btnSlug = document.querySelector('.slug-from-name');
    if (btnSlug) {
      btnSlug.addEventListener('click', function(e) {
        e.preventDefault();
        var from = document.getElementById(btnSlug.getAttribute('data-from'));
        var to   = document.getElementById(btnSlug.getAttribute('data-to'));
        if (!from || !to) return;
        var val = (from.value || '').toLowerCase()
          .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
          .replace(/[^a-z0-9\s-]/g, '')
          .trim().replace(/\s+/g, '-').replace(/-+/g, '-');
        to.value = val;
      });
    }
  });
</script>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
