<?php include __DIR__ . '/../../partials/admin_head.php'; ?>
<?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

<div class="page-head mb-3">
  <div class="page-head-inner">
    <div>
      <h1 class="h-title mb-1">Auditoría / Historial</h1>
      <p class="muted mb-0">Registro de todas las acciones: crear, editar, eliminar, login, logout.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <form method="get" action="<?= url('/admin/auditoria') ?>" class="d-flex gap-2 flex-wrap">
        <input type="search" name="q" class="form-control form-control-sm" placeholder="Buscar por usuario, entidad, acción…"
               value="<?= e(trim((string)($_GET['q'] ?? ''))) ?>" style="min-width:220px">
        <select name="action" class="form-select form-select-sm" style="width:auto">
          <option value="">Todas (acción)</option>
          <option value="create">➕ create</option>
          <option value="update">✏️ update</option>
          <option value="delete">🗑️ delete</option>
          <option value="login">🔑 login</option>
          <option value="logout">🚪 logout</option>
        </select>
        <button class="btn btn-primary btn-sm" type="submit">🔎</button>
        <a class="btn btn-outline-secondary btn-sm" href="<?= url('/admin/auditoria') ?>">Limpiar</a>
      </form>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($pager['items'])): ?>
      <div class="list-empty p-5 text-center">
        <div style="font-size:3rem;margin-bottom:.5rem">📜</div>
        <h3 class="h-title-sm">Sin registros de auditoría</h3>
        <p class="muted">Las acciones se registrarán aquí.</p>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table table-striped table-compact mb-0 admin-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Usuario</th>
              <th>Acción</th>
              <th>Entidad</th>
              <th>ID</th>
              <th>IP</th>
              <th>Detalles</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pager['items'] as $log):
                $iconMap = ['create'=>'➕','update'=>'✏️','delete'=>'🗑️','login'=>'🔑','logout'=>'🚪','toggle'=>'🔘'];
                $badgeMap = ['create'=>'badge-success','update'=>'badge-primary','delete'=>'badge-danger','login'=>'badge-accent','logout'=>'badge-secondary','toggle'=>'badge-warning'];
                $act = $log['action'] ?? '';
            ?>
              <tr>
                <td class="mono muted-sm" style="white-space:nowrap"><?= e(format_date($log['created_at'] ?? '', 'd/m/Y H:i')) ?></td>
                <td>
                  <?php if (!empty($log['user_name'])): ?>
                    <img src="<?= e(avatar_svg($log['user_name'], 28)) ?>" alt="" class="avatar-xs me-1">
                    <strong><?= e($log['user_name']) ?></strong>
                    <div class="muted-sm mono"><?= e($log['user_email'] ?? '') ?></div>
                  <?php else: ?>
                    <em class="muted-sm">Sistema</em>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= $badgeMap[$act] ?? 'badge-secondary' ?>">
                    <?= $iconMap[$act] ?? '•' ?> <?= e($act) ?>
                  </span>
                </td>
                <td class="mono"><?= e($log['entity'] ?? '') ?></td>
                <td class="mono muted-sm">#<?= e($log['entity_id'] ?? '') ?></td>
                <td class="mono muted-sm"><?= e($log['ip_address'] ?? '') ?></td>
                <td class="muted-sm">
                  <?php
                    $meta = json_decode($log['metadata_text'] ?? '{}', true);
                    if (is_array($meta) && !empty($meta)) {
                        $parts = [];
                        foreach (array_slice($meta, 0, 3) as $k => $v) {
                            if (is_array($v) || is_object($v)) {
                                $vStr = json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            } else {
                                $vStr = (string)$v;
                            }
                            if (mb_strlen($vStr) > 60) $vStr = mb_substr($vStr, 0, 60) . '…';
                            $parts[] = '<strong>' . e($k) . '</strong>: ' . e($vStr);
                        }
                        echo implode(' · ', $parts);
                    } else {
                        echo e(mb_substr((string)($log['metadata_text'] ?? ''), 0, 100));
                    }
                  ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <?php if (!empty($pager) && $pager['total_pages'] > 1): ?>
    <div class="card-footer">
      <?= pagination_links($pager) ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../partials/admin_footer.php'; ?>
