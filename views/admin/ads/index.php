<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<?php
  $valFn = static function($k, $def = '') use ($filters) {
      if (array_key_exists($k, $filters)) {
          $v = $filters[$k];
          if ($v !== '' && $v !== null) return $v;
      }
      return $def;
  };
?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1">Publicidad / Anuncios</h1>
      <p class="muted mb-0">Administra banners y campañas. Crea anuncios con fecha inicio/fin, posiciones, estadísticas de views y clicks.</p>
    </div>
    <div class="btn-group-wrap">
      <a class="btn btn-outline-secondary" href="<?= url('/admin/publicidad/estadisticas') ?>">📊 Estadísticas</a>
      <a class="btn btn-primary" href="<?= url('/admin/publicidad/crear') ?>">➕ Nueva publicidad</a>
    </div>
  </div>
</div>

<div class="card mb-3">
  <form method="get" action="<?= url('/admin/publicidad') ?>" class="card-body" style="padding:1rem .75rem">
    <div class="row g-2 align-end">
      <div class="col-md-4 col-12">
        <label class="form-label xs-label">Buscar</label>
        <input class="form-control" type="text" name="q" placeholder="Título, link..." value="<?= e($valFn('q')) ?>">
      </div>
      <div class="col-md-3 col-6">
        <label class="form-label xs-label">Posición</label>
        <select name="position" class="form-control">
          <option value="">Todas</option>
          <?php foreach ($positions as $pk => $pv): ?>
            <option value="<?= e($pk) ?>" <?= $valFn('position') === $pk ? 'selected' : '' ?>><?= e($pv) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 col-6">
        <label class="form-label xs-label">Estado</label>
        <select name="is_active" class="form-control">
          <option value="">Todos</option>
          <option value="1" <?= $valFn('is_active') === 1 ? 'selected' : '' ?>>Activos</option>
          <option value="0" <?= $valFn('is_active') === 0 && $valFn('is_active') !== '' ? 'selected' : '' ?>>Inactivos</option>
        </select>
      </div>
      <div class="col-md-2 col-12">
        <button class="btn btn-primary w-100" type="submit">🔎 Buscar</button>
      </div>
    </div>
  </form>
</div>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($results['items'])): ?>
      <div class="list-empty p-5 text-center">
        <div style="font-size:3rem;margin-bottom:.5rem">📢</div>
        <h3 class="h-title-sm">Aún no hay publicidad</h3>
        <p class="muted">Crea tu primer anuncio y se visualizará en el sitio web.</p>
        <a class="btn btn-primary mt-3" href="<?= url('/admin/publicidad/crear') ?>">➕ Crear publicidad</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table table-striped table-compact mb-0 admin-table">
          <thead>
            <tr>
              <th style="width:56px">ID</th>
              <th>Vista previa / Título</th>
              <th>Posición</th>
              <th>Tipo</th>
              <th>Vigencia</th>
              <th>Impresiones</th>
              <th>Clicks</th>
              <th>Orden</th>
              <th>Estado</th>
              <th style="width:180px">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results['items'] as $a): $id = (int)$a['id']; ?>
              <tr>
                <td class="mono muted-sm"><?= $id ?></td>
                <td>
                  <div class="d-flex gap-2 align-center">
                    <?php if (!empty($a['image_path'])):
                      $sp = $a['image_path'];
                      $url = (str_starts_with($sp, '/') || preg_match('#^https?://#i', $sp)) ? $sp : upload_url($sp);
                    ?>
                      <div style="width:140px;min-width:140px;aspect-ratio:2/1;border-radius:8px;overflow:hidden;background:#f1f5f9;border:1px solid #e2e8f0">
                        <img src="<?= e($url) ?>" alt="<?= e($a['alt_text'] ?? $a['title']) ?>" style="width:100%;height:100%;object-fit:cover;display:block">
                      </div>
                    <?php elseif (($a['ad_kind'] ?? '') === 'html'): ?>
                      <div style="width:140px;min-width:140px;aspect-ratio:2/1;border-radius:8px;background:linear-gradient(135deg,#041b4e,#0d9488);color:#fff;display:grid;place-items:center;font-size:.7rem;padding:.4rem;text-align:center">
                        HTML Personalizado
                      </div>
                    <?php else: ?>
                      <div style="width:140px;min-width:140px;aspect-ratio:2/1;border-radius:8px;background:#e2e8f0;display:grid;place-items:center;color:#64748b">Sin imagen</div>
                    <?php endif; ?>
                    <div class="flex-grow-1 min-w-0">
                      <a href="<?= url('/admin/publicidad/'.$id.'/editar') ?>" class="admin-strong-link"><?= e($a['title']) ?></a>
                      <?php if (!empty($a['link_url'])): ?>
                        <div class="muted-sm mono" style="font-size:.72rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:420px"><?= e($a['link_url']) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td>
                  <?php
                    $pk = $a['position'];
                    $pLabel = \App\Models\Ad::POSITIONS[$pk] ?? $pk;
                  ?>
                  <span class="badge badge-info"><?= e($pLabel) ?></span>
                </td>
                <td class="muted-sm"><?= $a['ad_kind'] === 'html' ? 'HTML' : 'Imagen + Link' ?></td>
                <td class="muted-sm" style="font-size:.75rem;white-space:nowrap">
                  <?php if (!empty($a['start_at']) || !empty($a['end_at'])): ?>
                    <?= !empty($a['start_at']) ? date('d/m/Y', strtotime($a['start_at'])) : '—' ?>
                    &nbsp;→&nbsp;
                    <?= !empty($a['end_at']) ? date('d/m/Y', strtotime($a['end_at'])) : '∞' ?>
                  <?php else: ?>
                    <span class="muted-sm">Siempre</span>
                  <?php endif; ?>
                </td>
                <td class="mono"><strong><?= number_format((int)$a['views_count']) ?></strong></td>
                <td class="mono"><strong><?= number_format((int)$a['clicks_count']) ?></strong></td>
                <td class="muted-sm"><?= (int)($a['sort_order'] ?? 0) ?></td>
                <td>
                  <a href="<?= url('/admin/publicidad/'.$id.'/toggle') ?>" class="btn btn-xs <?= !empty($a['is_active']) ? 'btn-success' : 'btn-secondary' ?>" style="padding:.2rem .5rem">
                    <?= !empty($a['is_active']) ? '✅ Activo' : '⏸️ Inactivo' ?>
                  </a>
                </td>
                <td>
                  <div class="btn-group">
                    <a class="btn btn-xs btn-outline-primary" href="<?= url('/admin/publicidad/'.$id.'/editar') ?>">✏️</a>
                    <form method="post" action="<?= url('/admin/publicidad/'.$id.'/eliminar') ?>"
                          onsubmit="return confirm('¿Eliminar esta publicidad?')" style="display:inline">
                      <?= csrf_field() ?>
                      <button class="btn btn-xs btn-outline-danger" type="submit">🗑️</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($results['total_pages'] > 1): ?>
        <div class="card-footer pagination-wrap">
          <?php for ($p = 1; $p <= $results['total_pages']; $p++): ?>
            <?php
              $qs = $_GET; $qs['page'] = $p;
              $u = url('/admin/publicidad') . '?' . http_build_query($qs);
            ?>
            <?php if ($p === (int)$results['page']): ?>
              <span class="btn btn-primary btn-xs disabled"><?= $p ?></span>
            <?php else: ?>
              <a class="btn btn-xs btn-outline-secondary" href="<?= e($u) ?>"><?= $p ?></a>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
