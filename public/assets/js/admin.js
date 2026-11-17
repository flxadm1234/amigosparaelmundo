/* ==========================================================================
   AMIGOS POR EL MUNDO - admin.js (Panel administrativo)
   Vanilla JS: sidebar, filtros, confirmaciones custom, ordenes, tabs.
   ========================================================================== */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {

    // ===== Sidebar — robusto V55F =====
    // Escucha TANTO id="sidebarToggle" COMO [data-action="sidebar-toggle"] (backward compat)
    var sidebarBtns = Array.prototype.slice.call(
      document.querySelectorAll('#sidebarToggle, [data-action="sidebar-toggle"], .sidebar-toggle')
    );
    var sidebar  = document.getElementById('adminSidebar') || document.querySelector('.admin-sidebar');
    var backdrop = document.getElementById('appBackdrop')  || document.querySelector('.sidebar-backdrop, .admin-drawer-backdrop');
    var body     = document.body;
    var STORAGE_KEY_COLLAPSED = 'amigos_mundo_admin_sidebar_collapsed';
    var STORAGE_KEY_M_OPEN    = 'amigos_mundo_admin_sidebar_m_open';
    var mqDesktop = window.matchMedia ? window.matchMedia('(min-width: 992px)') : null;

    function isDesktop() {
      return !!(mqDesktop && mqDesktop.matches);
    }

    // Restaurar estado al cargar
    try {
      if (isDesktop() && localStorage.getItem(STORAGE_KEY_COLLAPSED) === '1') {
        if (body) body.classList.add('sidebar-collapsed');
        if (sidebar) sidebar.classList.add('is-collapsed');
      }
      if (!isDesktop() && localStorage.getItem(STORAGE_KEY_M_OPEN) === '1') {
        if (body) body.classList.add('sidebar-open');
        if (sidebar) sidebar.classList.add('open');
        if (backdrop) {
          backdrop.classList.add('show');
          if (backdrop.hasAttribute('hidden')) backdrop.removeAttribute('hidden');
        }
      }
    } catch (e) { /* ignore storage errors */ }

    function setBtnAria(expanded) {
      sidebarBtns.forEach(function (b) {
        try {
          b.setAttribute('aria-expanded', expanded ? 'true' : 'false');
          b.setAttribute('aria-label', expanded ? 'Cerrar menú lateral' : 'Abrir menú lateral');
        } catch (_) {}
      });
    }

    function closeMobileDrawer() {
      if (sidebar) sidebar.classList.remove('open');
      if (body) body.classList.remove('sidebar-open');
      if (backdrop) {
        backdrop.classList.remove('show');
        try { setTimeout(function () { if (backdrop && !backdrop.classList.contains('show')) backdrop.setAttribute('hidden', ''); }, 350); } catch (_) {}
      }
      setBtnAria(false);
      try { localStorage.setItem(STORAGE_KEY_M_OPEN, '0'); } catch (_) {}
    }

    function openMobileDrawer() {
      if (sidebar) sidebar.classList.add('open');
      if (body) body.classList.add('sidebar-open');
      if (backdrop) {
        if (backdrop.hasAttribute('hidden')) backdrop.removeAttribute('hidden');
        // Necesitamos reflow para que transition funcione
        try { void backdrop.offsetWidth; } catch (_) {}
        backdrop.classList.add('show');
      }
      setBtnAria(true);
      try { localStorage.setItem(STORAGE_KEY_M_OPEN, '1'); } catch (_) {}
    }

    function toggleDesktopCollapsed() {
      var collapsed = !(body && body.classList.contains('sidebar-collapsed'));
      if (body) {
        body.classList.toggle('sidebar-collapsed', collapsed);
      }
      if (sidebar) {
        sidebar.classList.toggle('is-collapsed', collapsed);
      }
      try { localStorage.setItem(STORAGE_KEY_COLLAPSED, collapsed ? '1' : '0'); } catch (_) {}
      setBtnAria(collapsed);
    }

    function onToggle(e) {
      if (e && typeof e.preventDefault === 'function') e.preventDefault();
      if (isDesktop()) {
        toggleDesktopCollapsed();
      } else {
        var isOpen = !!(sidebar && sidebar.classList.contains('open'));
        if (isOpen) closeMobileDrawer();
        else openMobileDrawer();
      }
    }

    sidebarBtns.forEach(function (btn) {
      btn.addEventListener('click', onToggle);
    });

    // Backdrop click cierra drawer MOBILE (desktop no usa backdrop)
    if (backdrop) {
      backdrop.addEventListener('click', function () {
        if (!isDesktop()) closeMobileDrawer();
      });
    }

    // Escape cierra drawer mobile
    document.addEventListener('keydown', function (e) {
      if (e && e.key === 'Escape' && !isDesktop()) {
        if (sidebar && sidebar.classList.contains('open')) closeMobileDrawer();
      }
    });

    // Al cambiar resolución (resize), resetear estados inconsistentes
    var lastViewport = isDesktop() ? 'd' : 'm';
    function onResizeViewport() {
      var now = isDesktop() ? 'd' : 'm';
      if (now !== lastViewport) {
        lastViewport = now;
        // Cambió a desktop: cerrar drawer móvil
        if (now === 'd') closeMobileDrawer();
        // Cambió a móvil: quitar collapsed desktop
        else {
          if (body) body.classList.remove('sidebar-collapsed');
          if (sidebar) sidebar.classList.remove('is-collapsed');
          if (sidebar && !sidebar.classList.contains('open')) closeMobileDrawer();
        }
      }
    }
    if (mqDesktop && typeof mqDesktop.addEventListener === 'function') {
      mqDesktop.addEventListener('change', onResizeViewport);
    } else if (mqDesktop && typeof mqDesktop.addListener === 'function') {
      mqDesktop.addListener(onResizeViewport);
    } else {
      window.addEventListener('resize', onResizeViewport);
    }
    // Botón inicial aria correcto: si desktop collapsed, true (expandible); si mobile open, true
    try {
      var initExpanded = false;
      if (isDesktop()) initExpanded = !!(body && body.classList.contains('sidebar-collapsed'));
      else initExpanded = !!(sidebar && sidebar.classList.contains('open'));
      setBtnAria(initExpanded);
    } catch (_) {}

    // ===== User Dropdown (avatar top-right) robusto =====
    (function () {
      var chips = Array.prototype.slice.call(
        document.querySelectorAll('[data-user-menu="trigger"], .user-chip, [aria-controls="userMenuPanel"]')
      );
      var menus = Array.prototype.slice.call(
        document.querySelectorAll('[data-user-menu="panel"], .user-menu, #userMenuPanel')
      );
      if (chips.length === 0 || menus.length === 0) return;

      function openMenu(btn, panel) {
        try { panel.removeAttribute('hidden'); } catch (_) {}
        panel.classList.add('is-open');
        try { btn.setAttribute('aria-expanded', 'true'); } catch (_) {}
        try { btn.setAttribute('aria-haspopup', 'true'); } catch (_) {}
      }
      function closeMenu(btn, panel) {
        panel.classList.remove('is-open');
        try { panel.setAttribute('hidden', ''); } catch (_) {}
        try { btn.setAttribute('aria-expanded', 'false'); } catch (_) {}
      }
      function closeAllMenus() {
        chips.forEach(function (c, i) { closeMenu(c, menus[i] || menus[0]); });
      }

      chips.forEach(function (btn, idx) {
        var panel = menus[idx] || menus[0];
        btn.addEventListener('click', function (e) {
          // Si el clic se originó dentro del panel del menú, dejar que el
          // enlace navegue normalmente (no hacer preventDefault ni togglear).
          if (e && e.target && panel && panel.contains(e.target)) {
            return;
          }
          if (e && typeof e.preventDefault === 'function') e.preventDefault();
          e && e.stopPropagation && e.stopPropagation();
          var isOpen = panel.classList.contains('is-open');
          closeAllMenus();
          if (!isOpen) openMenu(btn, panel);
        });
        btn.addEventListener('keydown', function (e) {
          if (!e) return;
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            var isOpen = panel.classList.contains('is-open');
            closeAllMenus();
            if (!isOpen) openMenu(btn, panel);
          } else if (e.key === 'Escape') {
            e.preventDefault();
            closeMenu(btn, panel);
            try { btn.focus(); } catch (_) {}
          }
        });
      });

      menus.forEach(function (panel) {
        panel.addEventListener('click', function (e) {
          if (!e) return;
          var link = e.target.closest && e.target.closest('a, button, [role="menuitem"]');
          if (link) {
            setTimeout(closeAllMenus, 60);
          }
        });
        panel.addEventListener('keydown', function (e) {
          if (e && e.key === 'Escape') {
            e.preventDefault();
            closeAllMenus();
            try { if (chips[0]) chips[0].focus(); } catch (_) {}
          }
        });
      });

      document.addEventListener('click', function (e) {
        if (!e || !e.target) return;
        var insideChip = e.target.closest && e.target.closest('[data-user-menu="trigger"], .user-chip, [aria-controls="userMenuPanel"]');
        var insideMenu = e.target.closest && e.target.closest('[data-user-menu="panel"], .user-menu, #userMenuPanel');
        if (!insideChip && !insideMenu) closeAllMenus();
      });
      document.addEventListener('keydown', function (e) {
        if (e && e.key === 'Escape') closeAllMenus();
      });
    })();

    // ===== Sidebar Admin Nav Groups (BORRADO, reubicar al FINAL del DOMContentLoaded para capture phase) =====
    // (placeholder, código completo se ejecuta AL FINAL del closure después de slug generator)

    // ===== Confirmación simple con modal =====
    document.querySelectorAll('[data-swal-confirm]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var title = btn.getAttribute('data-swal-title') || '¿Confirmar?';
        var msg   = btn.getAttribute('data-swal-text')  || 'Esta acción no se puede deshacer.';
        var yes   = btn.getAttribute('data-swal-yes')   || 'Confirmar';
        var no    = btn.getAttribute('data-swal-no')    || 'Cancelar';
        showConfirm(title, msg, yes, no, function () {
          // Enviar el formulario padre o seguir el link
          var form = btn.closest('form');
          if (form) {
            form.submit();
          } else if (btn.tagName === 'A' && btn.getAttribute('href')) {
            window.location.href = btn.getAttribute('href');
          }
        });
      });
    });

    // ===== Check-all (tablas) =====
    document.querySelectorAll('[data-check-all]').forEach(function (master) {
      master.addEventListener('change', function () {
        var scope = master.getAttribute('data-check-all');
        var boxes = document.querySelectorAll('input[type="checkbox"][data-check-group="' + scope + '"]');
        boxes.forEach(function (b) { b.checked = master.checked; });
      });
    });

    // ===== Auto-submit para filtros admin =====
    document.querySelectorAll('.auto-submit').forEach(function (el) {
      el.addEventListener('change', function () {
        var form = el.closest('form');
        if (form) form.submit();
      });
    });

    // ===== Admin page filters clear =====
    document.querySelectorAll('[data-action="clear-admin-filters"]').forEach(function (b) {
      b.addEventListener('click', function () {
        var form = b.closest('form');
        if (!form) return;
        form.querySelectorAll('input, select, textarea').forEach(function (f) {
          if (!f.name) return;
          if (f.type === 'checkbox' || f.type === 'radio') f.checked = false;
          else f.value = '';
        });
        form.submit();
      });
    });

    // ===== Tooltips / notificaciones flash auto-dismiss =====
    setTimeout(function () {
      document.querySelectorAll('.alert[data-autodismiss="1"]').forEach(function (a) {
        a.style.transition = 'opacity .4s ease';
        a.style.opacity = '0';
        setTimeout(function () { if (a.parentNode) a.parentNode.removeChild(a); }, 400);
      });
    }, 5000);

    // ===== Slug generator =====
    var titleIn = document.querySelector('[data-slug-source="true"]');
    var slugIn  = document.querySelector('[data-slug-target="true"]');
    if (titleIn && slugIn && !slugIn.dataset.manual) {
      var isManual = false;
      slugIn.addEventListener('input', function () { isManual = true; });
      titleIn.addEventListener('input', function () {
        if (!isManual) slugIn.value = toSlug(titleIn.value);
      });
      if (!slugIn.value && titleIn.value) slugIn.value = toSlug(titleIn.value);
    }

    // =========================================================================
    // ===== Sidebar Admin Nav Groups — toggle colapsable (FINAL + CAPTURE PHASE) =====
    // Usa Capture Phase para asegurar ejecución PRIMERA; no usa closures;
    // init con setTimeout para asegurar DOM completo.
    // =========================================================================
    (function initSidebarGroupsFinal() {
      var groups = document.querySelectorAll('.admin-nav-group');
      if (groups.length === 0) return;
      var i;
      for (i = 0; i < groups.length; i++) (function (group) {
        var trigger = group.querySelector('.admin-nav-item');
        var submenu = group.querySelector('.admin-nav-submenu');
        if (!trigger || !submenu) return;
        trigger.style.cursor = 'pointer';
        try { trigger.setAttribute('role', 'button'); } catch (_) {}
        var hasActive = !!submenu.querySelector('.is-active, [aria-current="page"], .active');
        if (hasActive) { group.classList.add('is-open'); }
        else { group.classList.remove('is-open'); }
        try { trigger.setAttribute('aria-expanded', hasActive ? 'true' : 'false'); } catch (_) {}
      })(groups[i]);

      function getCtx(target) {
        if (!target || typeof target.closest !== 'function') return null;
        var tri = target.closest('.admin-nav-group > .admin-nav-item');
        if (!tri) return null;
        var sub = target.closest('.admin-nav-submenu');
        if (sub) return null;
        var grp = tri.closest('.admin-nav-group');
        if (!grp) return null;
        return { t: tri, g: grp };
      }
      function applyToggle(ctx, force) {
        if (!ctx) return;
        var open = typeof force === 'boolean'
          ? force
          : !ctx.g.classList.contains('is-open');
        ctx.g.classList.toggle('is-open', open);
        try { ctx.t.setAttribute('aria-expanded', open ? 'true' : 'false'); } catch (_) {}
        return open;
      }

      // CAPTURE PHASE (último parámetro true) → se ejecuta ANTES que cualquier otro listener bubbling (a tag)
      document.addEventListener('click', function (e) {
        if (!e) return;
        var ctx = getCtx(e.target);
        if (!ctx) return;
        if (typeof e.preventDefault === 'function') e.preventDefault();
        if (typeof e.stopPropagation === 'function') e.stopPropagation();
        if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
        applyToggle(ctx);
      }, true);

      document.addEventListener('keydown', function (e) {
        if (!e) return;
        var ctx = getCtx(e.target);
        if (!ctx) return;
        var k = e.key, force = null;
        if (k === 'Enter' || k === ' ')          force = null;
        else if (k === 'ArrowRight')            force = true;
        else if (k === 'ArrowLeft')             force = false;
        else return;
        e.preventDefault();
        applyToggle(ctx, force);
      }, true);
    })();
  });

  function toSlug(str) {
    str = str.toLowerCase().trim();
    // Remover tildes
    str = str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    str = str.replace(/[^a-z0-9\s-]/g, '')
             .replace(/\s+/g, '-')
             .replace(/-+/g, '-')
             .replace(/^-|-$/g, '');
    return str;
  }

  // ===== Modal custom simple =====
  function showConfirm(title, text, btnYes, btnNo, onOk) {
    var html = '<div class="swal-simple show" id="_swalTmp">' +
      '<div class="swal-card">' +
        '<h4>' + escapeHtml(title) + '</h4>' +
        '<p>' + escapeHtml(text) + '</p>' +
        '<div class="swal-actions">' +
          '<button type="button" class="btn btn-light" data-swal-no>' + escapeHtml(btnNo) + '</button>' +
          '<button type="button" class="btn btn-danger" data-swal-yes>' + escapeHtml(btnYes) + '</button>' +
        '</div>' +
      '</div></div>';
    document.body.insertAdjacentHTML('beforeend', html);
    var m = document.getElementById('_swalTmp');
    m.querySelector('[data-swal-no]').addEventListener('click', function () { m.remove(); });
    m.querySelector('[data-swal-yes]').addEventListener('click', function () { m.remove(); if (onOk) onOk(); });
    m.addEventListener('click', function (e) { if (e.target === m) m.remove(); });
  }

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str == null ? '' : String(str);
    return div.innerHTML;
  }

})();
