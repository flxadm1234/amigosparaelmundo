<?php
if (!function_exists('admin_icon')) {
    function admin_icon(string $key = 'layout-dashboard', int $size = 18, float $sw = 1.75): string
    {
        $s = (string)$size;
        $w = number_format($sw, 2, '.', '');
        $common = 'width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $w . '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';
        return match ($key) {
            'layout-dashboard' => '<svg ' . $common . '><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>',
            'newspaper' => '<svg ' . $common . '><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>',
            'check-circle-2' => '<svg ' . $common . '><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>',
            'file-edit' => '<svg ' . $common . '><path d="M4 13.5V4a2 2 0 0 1 2-2h8.5L20 7.5V20a2 2 0 0 1-2 2h-5.5"/><polyline points="14 2 14 8 20 8"/><path d="M10.42 12.61a2.1 2.1 0 1 1 2.97 2.97L7.95 21 4 22l.99-3.95 5.43-5.44Z"/></svg>',
            'megaphone' => '<svg ' . $common . '><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>',
            'video' => '<svg ' . $common . '><path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2"/><path d="M6 12H2"/></svg>',
            'mic' => '<svg ' . $common . '><rect width="6" height="12" x="9" y="2" rx="3"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>',
            'radio' => '<svg ' . $common . '><circle cx="12" cy="12" r="2"/><path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"/><path d="M4.9 19.1C1 15.2 1 8.8 4.9 4.9"/><path d="M19.1 4.9C23 8.8 23 15.1 19.1 19"/></svg>',
            'users' => '<svg ' . $common . '><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            'pencil' => '<svg ' . $common . '><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>',
            'eye' => '<svg ' . $common . '><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>',
            'trash-2' => '<svg ' . $common . '><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>',
            'plus' => '<svg ' . $common . '><path d="M5 12h14"/><path d="M12 5v14"/></svg>',
            'globe-2' => '<svg ' . $common . '><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>',
            'search' => '<svg ' . $common . '><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>',
            'image' => '<svg ' . $common . '><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>',
            'trend-up' => '<svg ' . $common . '><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>',
            'trend-down' => '<svg ' . $common . '><polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/></svg>',
            'lock' => '<svg ' . $common . '><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
            'log-out' => '<svg ' . $common . '><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>',
            default => '<svg ' . $common . '><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>',
        };
    }
}

$todayLabel = function_exists('format_date_spanish') ? format_date_spanish('now', true) : date('d/m/Y H:i');
?>

<div class="page-head page-head-inner d-flex gap-3 flex-wrap justify-content-between align-items-center mb-4">
  <div class="d-flex align-items-center gap-3">
    <div style="width:44px;height:44px;border-radius:12px;background:rgba(8,123,67,0.12);color:#087B43;display:inline-flex;align-items:center;justify-content:center;">
      <?= admin_icon('layout-dashboard', 22, 1.9) ?>
    </div>
    <div>
      <h1 class="h-title mb-1">Panel de control</h1>
      <p class="muted mb-0">Administra aquí el contenido de la web de Amigos por el Mundo.</p>
    </div>
  </div>
  <div class="d-flex gap-3 flex-wrap align-items-center">
    <span class="text-muted text-sm">
      <span class="d-none d-sm-inline">Actualizado:</span>
      <time datetime="<?= e(date('c')) ?>"><?= e($todayLabel) ?></time>
    </span>
    <a class="rm-btn rm-btn-primary d-inline-flex align-items-center gap-2" href="<?= e(url('/admin/noticias/crear')) ?>">
      <?= admin_icon('plus', 15, 2.2) ?>
      <span>Crear noticia</span>
    </a>
    <a class="rm-btn rm-btn-secondary d-none d-sm-inline-flex align-items-center gap-2" href="<?= e(url('/')) ?>" target="_blank" rel="noopener">
      <?= admin_icon('globe-2', 15, 1.9) ?>
      <span>Ver sitio</span>
    </a>
  </div>
</div>

<div class="admin-kpi-grid mb-4">
  <?php foreach (($kpiCards ?? []) as $idx => $k):
    $accent  = $k['accent']  ?? '#087B43';
    $iconKey = $k['icon']    ?? 'newspaper';
    $href    = $k['href']    ?? null;
    $hint    = (string)($k['hint'] ?? '');
    $tag     = $href ? 'a' : 'div';
  ?>
    <<?= $tag ?><?php if ($href): ?> href="<?= e(url($href)) ?>"<?php endif; ?>
      class="admin-kpi-card"
      style="--_kpi-accent: <?= e($accent) ?>;"
      <?php if ($tag === 'a'): ?> aria-label="<?= e($k['title'] . ': ' . (string)$k['value']) ?>"<?php endif; ?>>
      <div class="kpi-icon-bg" aria-hidden="true" style="color:<?= e($accent) ?>;"><?= admin_icon($iconKey, 110, 1.2) ?></div>
      <div class="kpi-head">
        <span class="kpi-chip" style="background:<?= e($accent) ?>1f;color:<?= e($accent) ?>;"><?= admin_icon($iconKey, 18, 1.85) ?></span>
        <span class="kpi-title"><?= e($k['title']) ?></span>
      </div>
      <div class="kpi-value"><?= e((string)($k['value'] ?? 0)) ?></div>
      <div class="kpi-foot">
        <span><?= e($hint) ?></span>
        <?php if ($href): ?><span class="kpi-go" aria-hidden="true">→</span><?php endif; ?>
      </div>
    </<?= $tag ?>>
  <?php endforeach; ?>
</div>

<div class="quick-actions mb-5">
  <span class="qa-label">Accesos rápidos:</span>
  <a class="qa-chip" href="<?= e(url('/admin/noticias/crear')) ?>"><span class="qa-icon"><?= admin_icon('plus', 15, 2.2) ?></span>Nueva noticia</a>
  <a class="qa-chip" href="<?= e(url('/admin/radio/programas/crear')) ?>"><span class="qa-icon"><?= admin_icon('globe-2', 15, 1.9) ?></span>Nuevo programa</a>
  <a class="qa-chip" href="<?= e(url('/admin/videos/crear')) ?>"><span class="qa-icon"><?= admin_icon('video', 15, 1.9) ?></span>Subir video</a>
  <a class="qa-chip" href="<?= e(url('/admin/paginas/crear')) ?>"><span class="qa-icon"><?= admin_icon('file-edit', 15, 1.9) ?></span>Nueva página</a>
  <a class="qa-chip" href="<?= e(url('/admin/ajustes')) ?>"><span class="qa-icon"><?= admin_icon('lock', 15, 1.9) ?></span>Ajustes del sitio</a>
</div>

<div class="row g-4 align-items-stretch"
     id="dashboardMainRow"
     style="display:block;box-sizing:border-box;margin:0;padding:0;">

  <div class="col-12 col-lg-8 col-xl-8"
       id="dashColNews"
       style="box-sizing:border-box;min-width:0;padding:0 .75rem;display:block;">
    <div class="card h-100" style="height:100%;min-height:100%;">
      <div class="card-header d-flex justify-content-between align-items-center gap-3">
        <h3 class="card-title-sm mb-0 d-inline-flex align-items-center gap-2">
          <span style="color:var(--rm-admin-accent-2);"><?= admin_icon('newspaper', 17, 1.9) ?></span>
          Últimas 10 noticias
        </h3>
        <a class="rm-btn rm-btn-secondary rm-btn-sm d-inline-flex align-items-center gap-2" href="<?= e(url('/admin/noticias')) ?>">
          <span>Ver todas</span>
          <span aria-hidden="true">→</span>
        </a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($recentNews)): ?>
          <div class="list-empty px-3 py-5 text-center">
            <div style="width:64px;height:64px;margin:0 auto 12px;color:var(--rm-admin-text-4);opacity:0.6;"><?= admin_icon('newspaper', 64, 1.5) ?></div>
            <h3 class="h5 mb-1">Aún no hay noticias</h3>
            <p class="muted mb-3">Empieza creando la primera noticia del portal.</p>
            <a class="rm-btn rm-btn-primary rm-btn-sm" href="<?= e(url('/admin/noticias/crear')) ?>">Crear primera noticia</a>
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table class="admin-table table mb-0 align-middle">
              <thead>
                <tr>
                  <th style="width:88px;">Portada</th>
                  <th>Título</th>
                  <th style="width:130px;">Sección</th>
                  <th style="width:110px;">Estado</th>
                  <th style="width:120px;">Fecha</th>
                  <th style="width:128px;" class="text-right">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentNews as $n):
                    $nid     = (int)($n['id'] ?? 0);
                    $slug    = (string)($n['slug'] ?? '');
                    $title   = (string)($n['title'] ?? '');
                    $section = (string)($n['section_name'] ?? '—');
                    $isPub   = !empty($n['is_published']);
                    $isFeat  = !empty($n['is_featured']);
                    $isBreak = !empty($n['is_breaking']);
                    $pubDate = !empty($n['publish_date']) ? format_date($n['publish_date']) : '—';
                ?>
                  <tr>
                    <td>
                      <?php
                        $thumbW = 72; $thumbH = 44;
                        $thumbSrc = img_fallback($n['cover_image_path'] ?? '', '16x9', $title, 'dash-news-'.$nid);
                        $thumbIcon = admin_icon('image', 20, 1.7);
                      ?>
                      <div class="dash-news-thumb"
                           style="width:<?= $thumbW ?>px;height:<?= $thumbH ?>px;border-radius:8px;overflow:hidden;position:relative;display:inline-flex;align-items:center;justify-content:center;background:#087B43;border:1px solid var(--rm-admin-border);"
                           aria-hidden="false">
                        <img
                          src="<?= e($thumbSrc) ?>"
                          alt="Portada: <?= e(mb_substr($title, 0, 60)) ?>"
                          loading="lazy"
                          decoding="async"
                          style="width:100%;height:100%;object-fit:cover;display:block;position:absolute;inset:0;"
                          onerror="try{this.style.display='none';var p=this.parentNode;if(p){var ph=p.querySelector('.news-thumb-ph');if(ph){ph.style.display='inline-flex';}}this.onerror=null;}catch(e){this.onerror=null;this.style.display='none';}">
                        <span class="news-thumb-ph"
                              style="display:none;width:100%;height:100%;align-items:center;justify-content:center;background:#087B43;color:#FFFFFF;font-size:11px;font-weight:700;letter-spacing:.04em;position:absolute;inset:0;gap:4px;">
                          <?= $thumbIcon ?>
                        </span>
                      </div>
                    </td>
                    <td>
                      <div style="font-weight:600;color:var(--rm-admin-text-1);line-height:1.35;margin-bottom:4px;">
                        <a href="<?= e(url('/admin/noticias/'.$nid.'/editar')) ?>" style="color:inherit;text-decoration:none;" class="hover-accent">
                          <?= e(mb_substr($title, 0, 58)) ?><?= mb_strlen($title) > 58 ? '…' : '' ?>
                        </a>
                      </div>
                      <div class="d-flex flex-wrap gap-1">
                        <?php if ($isFeat): ?><span class="badge badge-accent badge-sm" style="background:#EDE9FE !important;color:#6D28D9 !important;border-color:rgba(109,40,217,0.25) !important;">Destacada</span><?php endif; ?>
                        <?php if ($isBreak): ?><span class="badge badge-danger badge-sm">Última hora</span><?php endif; ?>
                      </div>
                    </td>
                    <td>
                      <span style="color:var(--rm-admin-text-3);"><?= e($section) ?></span>
                    </td>
                    <td>
                      <?php if ($isPub): ?>
                        <span class="badge badge-success">Publicado</span>
                      <?php else: ?>
                        <span class="badge badge-warning">Borrador</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="text-sm" style="color:var(--rm-admin-text-3);"><?= e($pubDate) ?></span>
                    </td>
                    <td class="text-right">
                      <div class="row-actions justify-content-end">
                        <a class="btn-icon edit"   href="<?= e(url('/admin/noticias/'.$nid.'/editar')) ?>" title="Editar" aria-label="Editar noticia #<?= $nid ?>"><?= admin_icon('pencil', 15, 1.9) ?></a>
                        <?php if ($slug !== ''): ?>
                          <a class="btn-icon view" href="<?= e(news_url($n)) ?>" target="_blank" rel="noopener" title="Ver" aria-label="Ver noticia en el sitio"><?= admin_icon('eye', 15, 1.9) ?></a>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4 col-xl-4"
       id="dashColAudit"
       style="box-sizing:border-box;min-width:0;padding:0 .75rem;display:block;">
    <div class="card h-100" style="height:100%;min-height:100%;">
      <div class="card-header d-flex justify-content-between align-items-center gap-3">
        <h3 class="card-title-sm mb-0 d-inline-flex align-items-center gap-2">
          <span style="color:var(--rm-admin-accent-2);"><?= admin_icon('search', 17, 1.9) ?></span>
          Última actividad (Auditoría)
        </h3>
        <a class="rm-btn rm-btn-secondary rm-btn-sm d-inline-flex align-items-center gap-2" href="<?= e(url('/admin/auditoria')) ?>">
          <span>Ver todo</span>
          <span aria-hidden="true">→</span>
        </a>
      </div>
      <div class="card-body">
        <?php if (empty($recentAudit)): ?>
          <div class="list-empty text-center py-4">
            <div style="width:64px;height:64px;margin:0 auto 12px;color:var(--rm-admin-text-4);opacity:0.6;"><?= admin_icon('search', 64, 1.5) ?></div>
            <h3 class="h5 mb-1">Sin actividad registrada</h3>
            <p class="muted mb-0">Las acciones del equipo aparecerán aquí.</p>
          </div>
        <?php else: ?>
          <div class="audit-timeline">
            <?php
            $actionIconMap = [
              'create'    => 'plus',
              'update'    => 'pencil',
              'delete'    => 'trash-2',
              'login'     => 'lock',
              'logout'    => 'log-out',
              'toggle'    => 'check-circle-2',
              'publish'   => 'check-circle-2',
              'unpublish' => 'file-edit',
            ];
            $actionBadgeMap = [
              'create'    => 'success',
              'update'    => 'info',
              'delete'    => 'danger',
              'login'     => 'warning',
              'logout'    => 'secondary',
              'publish'   => 'success',
              'unpublish' => 'warning',
              'toggle'    => 'accent',
            ];
            foreach ($recentAudit as $a):
                $action    = (string)($a['action'] ?? 'info');
                $iconKey   = $actionIconMap[$action] ?? 'search';
                $badgeKind = $actionBadgeMap[$action] ?? 'secondary';
                $entity    = trim((string)($a['entity_type'] ?? $a['entity'] ?? ''));
                $eid       = (string)($a['entity_id'] ?? '');
                $userName  = (string)($a['user_name'] ?? 'Sistema');
                $ip        = (string)($a['ip_address'] ?? '');
                $created   = !empty($a['created_at']) ? format_date($a['created_at'], 'd/m/Y H:i') : '';
                $metaText  = '';
                if (!empty($a['metadata_text'])) {
                    $meta = json_decode($a['metadata_text'], true);
                    if (is_array($meta) && !empty($meta)) {
                        $parts = [];
                        foreach (array_slice($meta, 0, 2) as $mk => $mv) {
                            if (is_array($mv) || is_object($mv)) {
                                $mvStr = json_encode($mv, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            } else {
                                $mvStr = (string)$mv;
                            }
                            if (mb_strlen($mvStr) > 28) $mvStr = mb_substr($mvStr, 0, 28) . '…';
                            $parts[] = e($mk) . ': ' . e($mvStr);
                        }
                        $metaText = implode(' · ', $parts);
                    }
                }
            ?>
              <div class="audit-item <?= e($action) ?>">
                <div class="audit-meta">
                  <?php if ($created !== ''): ?><time datetime="<?= e($a['created_at'] ?? '') ?>"><?= e($created) ?></time><?php endif; ?>
                  <?php if ($ip !== ''): ?>· IP <?= e($ip) ?><?php endif; ?>
                </div>
                <p class="audit-text">
                  <strong><?= e($userName) ?></strong>
                  <span class="badge badge-<?= e($badgeKind) ?> badge-sm" style="margin:0 6px;"><?= e($action) ?></span>
                  <?php if ($entity !== ''): ?>
                    <span style="color:var(--rm-admin-accent-2);font-weight:600;text-transform:uppercase;letter-spacing:0.02em;font-size:0.75rem;"><?= e($entity) ?></span>
                    <?php if ($eid !== ''): ?>
                      <span style="color:var(--rm-admin-text-4);">#<?= e($eid) ?></span>
                    <?php endif; ?>
                  <?php endif; ?>
                </p>
                <?php if ($metaText !== ''): ?>
                  <div class="mt-1" style="font-size:0.75rem;color:var(--rm-admin-text-4);"><?= $metaText ?></div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<?php if (!empty($chartData) && is_array($chartData)):
  $maxCnt = 0;
  foreach ($chartData as $cdx) { $maxCnt = max($maxCnt, (int)($cdx['cnt'] ?? 0)); }
  $maxCnt = max(1, $maxCnt);
?>
  <div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center gap-3">
      <h3 class="card-title-sm mb-0 d-inline-flex align-items-center gap-2">
        <span style="color:var(--rm-admin-accent-2);"><?= admin_icon('trend-up', 17, 1.9) ?></span>
        Publicaciones de noticias (últimos 6 meses)
      </h3>
      <span class="text-muted text-sm">Total: <strong style="color:var(--rm-admin-text-1);"><?= (int)array_sum(array_map(fn($x) => (int)($x['cnt'] ?? 0), $chartData)) ?></strong> noticias</span>
    </div>
    <div class="card-body">
      <div style="display:flex;align-items:flex-end;gap:1.2rem;height:220px;padding:0 0.5rem 0.25rem;border-bottom:1px solid var(--rm-admin-border);">
        <?php foreach ($chartData as $cd):
            $cnt = (int)($cd['cnt'] ?? 0);
            $h   = 14 + (int)(($cnt / $maxCnt) * 176);
            $ym  = (string)($cd['ym'] ?? '');
            $ymLabel = $ym;
            if (preg_match('#^(\d{4})-(\d{2})$#', $ym, $m)) {
                $meses = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                $ymLabel = $meses[(int)$m[2]] . ' ' . $m[1];
            }
        ?>
          <div style="flex:1;text-align:center;display:flex;flex-direction:column;align-items:center;gap:0.5rem;min-width:0;">
            <div class="text-xs font-bold" style="color:var(--rm-admin-text-2);font-variant-numeric:tabular-nums;"><?= $cnt ?></div>
            <div class="chart-bar" style="width:100%;max-width:56px;height:<?= $h ?>px;"></div>
            <div class="text-xs" style="color:var(--rm-admin-text-4);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;"><?= e($ymLabel) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
<?php endif; ?>
