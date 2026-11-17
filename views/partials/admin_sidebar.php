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
            'list' => '<svg ' . $common . '><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>',
            'plus' => '<svg ' . $common . '><path d="M5 12h14"/><path d="M12 5v14"/></svg>',
            'folder-kanban' => '<svg ' . $common . '><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/><path d="M9 10v5"/><path d="M17 10v5"/><path d="M13 10v5"/></svg>',
            'tag' => '<svg ' . $common . '><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg>',
            'megaphone' => '<svg ' . $common . '><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>',
            'video' => '<svg ' . $common . '><path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2"/><path d="M6 12H2"/></svg>',
            'mic' => '<svg ' . $common . '><rect width="6" height="12" x="9" y="2" rx="3"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>',
            'radio' => '<svg ' . $common . '><circle cx="12" cy="12" r="2"/><path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"/><path d="M4.9 19.1C1 15.2 1 8.8 4.9 4.9"/><path d="M19.1 4.9C23 8.8 23 15.1 19.1 19"/></svg>',
            'mic-vocal' => '<svg ' . $common . '><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><path d="M12 19v3"/></svg>',
            'settings-2' => '<svg ' . $common . '><path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/></svg>',
            'file-text' => '<svg ' . $common . '><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>',
            'ad-circle' => '<svg ' . $common . '><circle cx="12" cy="12" r="10"/><path d="M12 6a6 6 0 1 0 3.5 10.87"/><path d="M16.5 11a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"/></svg>',
            'bar-chart-3' => '<svg ' . $common . '><path d="M3 3v18h18"/><path d="M7 16V9"/><path d="M12 16V5"/><path d="M17 16v-7"/></svg>',
            'search' => '<svg ' . $common . '><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>',
            'settings' => '<svg ' . $common . '><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>',
            'users' => '<svg ' . $common . '><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            'user' => '<svg ' . $common . '><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
            'globe-2' => '<svg ' . $common . '><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/><path d="M2 12h20"/></svg>',
            'log-out' => '<svg ' . $common . '><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>',
            'menu-2' => '<svg ' . $common . '><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>',
            'chevron-down' => '<svg ' . $common . '><path d="m6 9 6 6 6-6"/></svg>',
            'home' => '<svg ' . $common . '><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
            'pencil' => '<svg ' . $common . '><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>',
            'eye' => '<svg ' . $common . '><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>',
            'trash-2' => '<svg ' . $common . '><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>',
            'toggle-right' => '<svg ' . $common . '><rect width="20" height="12" x="2" y="6" rx="6"/><circle cx="16" cy="12" r="2"/></svg>',
            'toggle-left' => '<svg ' . $common . '><rect width="20" height="12" x="2" y="6" rx="6"/><circle cx="8" cy="12" r="2"/></svg>',
            'check-circle-2' => '<svg ' . $common . '><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>',
            'file-edit' => '<svg ' . $common . '><path d="M4 13.5V4a2 2 0 0 1 2-2h8.5L20 7.5V20a2 2 0 0 1-2 2h-5.5"/><polyline points="14 2 14 8 20 8"/><path d="M10.42 12.61a2.1 2.1 0 1 1 2.97 2.97L7.95 21 4 22l.99-3.95 5.43-5.44Z"/></svg>',
            'image' => '<svg ' . $common . '><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>',
            'trend-up' => '<svg ' . $common . '><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>',
            'trend-down' => '<svg ' . $common . '><polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/></svg>',
            'lock' => '<svg ' . $common . '><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
            default => '<svg ' . $common . '><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>',
        };
    }
}

$u = auth_user();
$roleName = $u['role_name'] ?? ($u['role_id'] == 1 ? 'Admin' : 'Editor');
$siteName = app_setting('site_name', 'ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO');
$adminDarkLogo = app_setting('site_logo_dark_bg');
if ($adminDarkLogo === '') { $adminDarkLogo = app_setting('site_logo'); }
if (!empty($adminDarkLogo) && (str_starts_with($adminDarkLogo, '/') || preg_match('#^https?://#i', $adminDarkLogo))) {
    $adminDarkLogoSafe = $adminDarkLogo;
} elseif (!empty($adminDarkLogo)) {
    $abs = resolve_upload_abs_path($adminDarkLogo);
    $adminDarkLogoSafe = (is_file($abs)) ? upload_url($adminDarkLogo) : '';
} else {
    $adminDarkLogoSafe = '';
}

$activePrefix = function (string $p): string {
    return is_active_route('/admin/' . trim($p, '/'), 'is-active');
};
$activePrefixAria = function (string $p): string {
    return is_active_route('/admin/' . trim($p, '/'), 'aria-current="page"');
};

$activeSub = function (string $p): string {
    return is_active_route('/admin/' . trim($p, '/'), 'is-active');
};

$isAdmin = auth_is('admin');

$bcPages = [
    ['url' => url('/admin/dashboard'), 'label' => 'Inicio', 'icon' => 'home'],
];
$reqPath = $_SERVER['REQUEST_URI'] ?? '';
if (str_starts_with($reqPath, '/admin/dashboard')) {
    $bcPages[] = ['url' => null, 'label' => 'Dashboard'];
} elseif (str_starts_with($reqPath, '/admin/noticias')) {
    $bcPages[] = ['url' => url('/admin/noticias'), 'label' => 'Noticias'];
    if (str_contains($reqPath, '/crear')) $bcPages[] = ['url' => null, 'label' => 'Crear'];
    elseif (str_contains($reqPath, '/editar')) $bcPages[] = ['url' => null, 'label' => 'Editar'];
} elseif (str_starts_with($reqPath, '/admin/secciones')) $bcPages[] = ['url' => null, 'label' => 'Secciones'];
elseif (str_starts_with($reqPath, '/admin/etiquetas')) $bcPages[] = ['url' => null, 'label' => 'Etiquetas'];
elseif (str_starts_with($reqPath, '/admin/videos')) $bcPages[] = ['url' => null, 'label' => 'Videos'];
elseif (str_starts_with($reqPath, '/admin/radio')) $bcPages[] = ['url' => null, 'label' => 'Programas de impacto'];
elseif (str_starts_with($reqPath, '/admin/paginas')) $bcPages[] = ['url' => null, 'label' => 'Páginas'];
elseif (str_starts_with($reqPath, '/admin/publicidad')) $bcPages[] = ['url' => null, 'label' => 'Publicidad'];
elseif (str_starts_with($reqPath, '/admin/auditoria')) $bcPages[] = ['url' => null, 'label' => 'Auditoría'];
elseif (str_starts_with($reqPath, '/admin/ajustes')) $bcPages[] = ['url' => null, 'label' => 'Ajustes'];
elseif (str_starts_with($reqPath, '/admin/usuarios')) $bcPages[] = ['url' => null, 'label' => 'Usuarios'];
else $bcPages[] = ['url' => null, 'label' => 'Panel'];
?>
  <aside class="app-sidebar admin-sidebar" id="adminSidebar" role="complementary" aria-label="Menú de navegación administrativo">
    <div class="admin-brand">
      <a href="<?= url('/admin') ?>" class="admin-brand-link" aria-label="<?= e($siteName) ?> — Panel">
        <?php if ($adminDarkLogoSafe !== ''): ?>
          <img src="<?= e($adminDarkLogoSafe) ?>" alt="<?= e($siteName) ?>" class="admin-brand-logo" loading="eager">
        <?php else: ?>
          <span class="admin-brand-logo" aria-hidden="true" style="display:grid;place-items:center;border-radius:14px;background:#0b7b43;color:#fff;font-size:16px;font-weight:800;">AM</span>
        <?php endif; ?>
      </a>
      <div class="admin-brand-subtitle">Panel Administrativo</div>
      <span class="admin-badge-role badge badge-accent"><?= e($roleName) ?></span>
    </div>
    <nav class="admin-nav" aria-label="Menú administrativo">

      <a href="<?= url('/admin/dashboard') ?>" class="admin-nav-item <?= $activePrefix('dashboard') ?>" title="Dashboard" <?= $activePrefixAria('dashboard') ?>>
        <span class="an-icon"><?= admin_icon('layout-dashboard', 18, 1.8) ?></span>
        <span class="an-label">Dashboard</span>
      </a>

      <div class="admin-nav-group">
        <a href="<?= url('/admin/noticias') ?>" class="admin-nav-item <?= $activePrefix('noticias') ?>" title="Noticias" <?= $activePrefixAria('noticias') ?>>
          <span class="an-icon"><?= admin_icon('newspaper', 18, 1.8) ?></span>
          <span class="an-label">Noticias</span>
          <span class="an-caret"><?= admin_icon('chevron-down', 14, 2) ?></span>
        </a>
        <div class="admin-nav-submenu">
          <a href="<?= url('/admin/noticias') ?>" class="admin-nav-subitem <?= $activeSub('noticias') ?>">
            <span class="an-sub-icon"><?= admin_icon('list', 14, 1.8) ?></span>
            <span>Listar</span>
          </a>
          <a href="<?= url('/admin/noticias/crear') ?>" class="admin-nav-subitem <?= $activeSub('noticias/crear') ?>">
            <span class="an-sub-icon"><?= admin_icon('plus', 14, 2) ?></span>
            <span>Crear</span>
          </a>
        </div>
      </div>

      <a href="<?= url('/admin/secciones') ?>" class="admin-nav-item <?= $activePrefix('secciones') ?>" title="Secciones" <?= $activePrefixAria('secciones') ?>>
        <span class="an-icon"><?= admin_icon('folder-kanban', 18, 1.8) ?></span>
        <span class="an-label">Secciones</span>
      </a>

      <a href="<?= url('/admin/etiquetas') ?>" class="admin-nav-item <?= $activePrefix('etiquetas') ?>" title="Etiquetas" <?= $activePrefixAria('etiquetas') ?>>
        <span class="an-icon"><?= admin_icon('tag', 18, 1.8) ?></span>
        <span class="an-label">Etiquetas</span>
      </a>

      <a href="<?= url('/admin/videos') ?>" class="admin-nav-item <?= $activePrefix('videos') ?>" title="Videos" <?= $activePrefixAria('videos') ?>>
        <span class="an-icon"><?= admin_icon('video', 18, 1.8) ?></span>
        <span class="an-label">Videos</span>
      </a>

      <a href="<?= url('/admin/radio/programas') ?>" class="admin-nav-item <?= $activePrefix('radio') ?>" title="Programas de impacto" <?= $activePrefixAria('radio') ?>>
        <span class="an-icon"><?= admin_icon('globe-2', 18, 1.8) ?></span>
        <span class="an-label">Programas</span>
      </a>

      <a href="<?= url('/admin/paginas') ?>" class="admin-nav-item <?= $activePrefix('paginas') ?>" title="Páginas" <?= $activePrefixAria('paginas') ?>>
        <span class="an-icon"><?= admin_icon('file-text', 18, 1.8) ?></span>
        <span class="an-label">Páginas</span>
      </a>

      <div class="admin-nav-group">
        <a href="<?= url('/admin/publicidad') ?>" class="admin-nav-item <?= $activePrefix('publicidad') ?>" title="Publicidad" <?= $activePrefixAria('publicidad') ?>>
          <span class="an-icon"><?= admin_icon('ad-circle', 18, 1.8) ?></span>
          <span class="an-label">Publicidad</span>
          <span class="an-caret"><?= admin_icon('chevron-down', 14, 2) ?></span>
        </a>
        <div class="admin-nav-submenu">
          <a href="<?= url('/admin/publicidad') ?>" class="admin-nav-subitem <?= $activeSub('publicidad') ?>">
            <span class="an-sub-icon"><?= admin_icon('megaphone', 14, 1.8) ?></span>
            <span>Banners</span>
          </a>
          <a href="<?= url('/admin/publicidad/estadisticas') ?>" class="admin-nav-subitem <?= $activeSub('publicidad/estadisticas') ?>">
            <span class="an-sub-icon"><?= admin_icon('bar-chart-3', 14, 1.8) ?></span>
            <span>Estadísticas</span>
          </a>
        </div>
      </div>

      <a href="<?= url('/admin/auditoria') ?>" class="admin-nav-item <?= $activePrefix('auditoria') ?>" title="Auditoría" <?= $activePrefixAria('auditoria') ?>>
        <span class="an-icon"><?= admin_icon('search', 18, 1.8) ?></span>
        <span class="an-label">Auditoría</span>
      </a>

      <?php if ($isAdmin): ?>
        <a href="<?= url('/admin/ajustes') ?>" class="admin-nav-item <?= $activePrefix('ajustes') ?>" title="Ajustes" <?= $activePrefixAria('ajustes') ?>>
          <span class="an-icon"><?= admin_icon('settings', 18, 1.8) ?></span>
          <span class="an-label">Ajustes</span>
        </a>

        <a href="<?= url('/admin/usuarios') ?>" class="admin-nav-item <?= $activePrefix('usuarios') ?>" title="Usuarios" <?= $activePrefixAria('usuarios') ?>>
          <span class="an-icon"><?= admin_icon('users', 18, 1.8) ?></span>
          <span class="an-label">Usuarios</span>
        </a>
      <?php endif; ?>

    </nav>
    <div class="admin-sidebar-foot sidebar-footer">
      <a class="admin-nav-item" href="<?= url('/admin/usuarios') ?>">
        <span class="an-icon"><?= admin_icon('users', 18, 1.8) ?></span><span class="an-label"><?= e($u['name'] ?? 'Usuarios') ?></span>
      </a>
      <a class="admin-nav-item" href="<?= url('/') ?>" target="_blank" rel="noopener">
        <span class="an-icon"><?= admin_icon('globe-2', 18, 1.8) ?></span><span class="an-label">Ver sitio público</span>
      </a>
      <a class="admin-nav-item admin-nav-danger"
         href="<?= url('/admin/logout') ?>"
         onclick="return confirm('¿Cerrar sesión?')">
        <span class="an-icon"><?= admin_icon('log-out', 18, 1.8) ?></span><span class="an-label">Salir</span>
      </a>
    </div>
  </aside>

  <div class="app-wrapper">
    <header class="app-header admin-topbar">
      <button type="button" id="sidebarToggle" class="sidebar-toggle icon-btn" aria-label="Abrir menú lateral" aria-controls="adminSidebar" aria-expanded="false" title="Alternar menú">
        <?= admin_icon('menu-2', 20, 2) ?>
      </button>
      <nav class="app-breadcrumbs d-none d-md-flex" aria-label="Ruta de navegación">
        <ol>
          <?php foreach ($bcPages as $bci => $bc): ?>
            <li>
              <?php if (!empty($bc['url']) && $bci < count($bcPages)-1): ?>
                <a href="<?= e($bc['url']) ?>">
                  <?php if (!empty($bc['icon'])): ?><span class="bc-icon" aria-hidden="true"><?= admin_icon($bc['icon'], 14, 1.8) ?></span><?php endif; ?>
                  <span><?= e($bc['label']) ?></span>
                </a>
              <?php else: ?>
                <span class="rm-active" aria-current="location">
                  <?php if (!empty($bc['icon']) && $bci === 0): ?><span class="bc-icon" aria-hidden="true"><?= admin_icon($bc['icon'], 14, 1.8) ?></span><?php endif; ?>
                  <span><?= e($bc['label']) ?></span>
                </span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ol>
      </nav>
      <form class="app-search ms-auto" method="get" action="<?= url('/admin/noticias') ?>" role="search">
        <label for="appSearchQ" class="visually-hidden">Buscar noticias</label>
        <div class="searchbox">
          <input type="search" name="q" id="appSearchQ" class="searchbox-input" placeholder="Buscar noticias…" value="<?= e(isset($_GET['q']) ? (string)$_GET['q'] : '') ?>">
          <button type="submit" class="searchbox-btn rm-btn rm-btn-primary rm-btn-sm" aria-label="Buscar">
            <?= admin_icon('search', 16, 2) ?>
          </button>
        </div>
      </form>
      <div class="user-chip" data-user-menu>
        <button type="button" class="user-chip-toggle" aria-haspopup="true" aria-expanded="false" id="userMenuBtn" aria-label="Menú de usuario">
          <span class="user-avatar" aria-hidden="true">
            <img src="<?= e(avatar_svg($u['name'] ?? 'Usuario', 36)) ?>" alt="" class="auc-avatar">
          </span>
          <span class="user-meta d-none d-sm-flex">
            <span class="user-name"><?= e($u['name'] ?? 'Usuario') ?></span>
            <span class="user-role"><?= e($roleName) ?></span>
          </span>
          <span class="user-caret" aria-hidden="true"><?= admin_icon('chevron-down', 14, 2) ?></span>
        </button>
        <div class="user-menu user-dropdown-menu" role="menu" aria-labelledby="userMenuBtn" hidden>
          <a role="menuitem" href="<?= url('/admin/usuarios') ?>">
            <span aria-hidden="true"><?= admin_icon('users', 16, 1.8) ?></span>
            <span>Usuarios</span>
          </a>
          <a role="menuitem" href="<?= url('/') ?>" target="_blank" rel="noopener">
            <span aria-hidden="true"><?= admin_icon('globe-2', 16, 1.8) ?></span>
            <span>Ver sitio</span>
          </a>
          <div class="user-menu-sep dropdown-divider"></div>
          <a role="menuitem" class="user-menu-danger danger" href="<?= url('/admin/logout') ?>" onclick="return confirm('¿Cerrar sesión?')">
            <span aria-hidden="true"><?= admin_icon('log-out', 16, 1.8) ?></span>
            <span>Salir</span>
          </a>
        </div>
      </div>
    </header>

    <div class="app-backdrop admin-drawer-backdrop" id="appBackdrop" hidden></div>

    <a href="#rm-main" class="skip-link">Saltar al contenido principal</a>

    <main class="app-main admin-main" id="rm-main" tabindex="-1">
      <div class="app-container admin-container">
        <?php include __DIR__ . '/flash.php'; ?>
