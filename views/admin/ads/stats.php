<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1">📊 Estadísticas de Publicidad</h1>
      <p class="muted mb-0">Impresiones vs Clicks (top 50)</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= url('/admin/publicidad') ?>">← Volver a publicidad</a>
  </div>
</div>

<?php
  $totViews = 0; $totClicks = 0;
  foreach ($rows as $r) { $totViews += (int)$r['views_count']; $totClicks += (int)$r['clicks_count']; }
  $ctrAll = $totViews > 0 ? round(100 * $totClicks / $totViews, 3) : 0;
?>
<div class="row g-3 mb-3">
  <div class="col-md-4 col-6"><div class="card"><div class="card-body"><div class="muted-sm small">Total Impresiones</div><div style="font-size:1.9rem;font-weight:800;color:#041b4e"><?= number_format($totViews) ?></div></div></div></div>
  <div class="col-md-4 col-6"><div class="card"><div class="card-body"><div class="muted-sm small">Total Clicks</div><div style="font-size:1.9rem;font-weight:800;color:#0d9488"><?= number_format($totClicks) ?></div></div></div></div>
  <div class="col-md-4 col-12"><div class="card"><div class="card-body"><div class="muted-sm small">CTR promedio</div><div style="font-size:1.9rem;font-weight:800;color:#7c3aed"><?= $ctrAll ?>%</div></div></div></div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-wrap">
      <table class="table table-striped table-compact mb-0 admin-table">
        <thead>
          <tr>
            <th style="width:56px">ID</th>
            <th>Anuncio</th>
            <th>Posición</th>
            <th>Impresiones</th>
            <th>Clicks</th>
            <th>CTR</th>
            <th>Estado</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr><td colspan="8" class="text-center p-4 muted">Sin datos.</td></tr>
          <?php endif; ?>
          <?php foreach ($rows as $r): $id=(int)$r['id']; $views=(int)$r['views_count']; $cl=(int)$r['clicks_count']; $ctr=$views>0?round(100*$cl/$views,3):0; ?>
            <tr>
              <td class="mono muted-sm"><?= $id ?></td>
              <td>
                <a href="<?= url('/admin/publicidad/'.$id.'/editar') ?>" class="admin-strong-link"><?= e($r['title']) ?></a>
              </td>
              <td><span class="badge badge-info"><?= e(\App\Models\Ad::POSITIONS[$r['position']] ?? $r['position']) ?></span></td>
              <td class="mono"><?= number_format($views) ?></td>
              <td class="mono"><?= number_format($cl) ?></td>
              <td class="mono"><strong style="color:#7c3aed"><?= $ctr ?>%</strong></td>
              <td><?= !empty($r['is_active']) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>' ?></td>
              <td>
                <a class="btn btn-xs btn-outline-primary" href="<?= url('/admin/publicidad/'.$id.'/editar') ?>">✏️</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
