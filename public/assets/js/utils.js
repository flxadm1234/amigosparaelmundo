/* ==========================================================================
   AMIGOS POR EL MUNDO - utils.js (Sitio público)
   Vanilla JS: mobile navbar, confirmaciones, filtros rápidos.
   ========================================================================== */

(function () {
  'use strict';

  // ===== Mobile menu toggle =====
  document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.navbar-toggle');
    var menu   = document.querySelector('.mobile-menu');
    if (toggle && menu) {
      toggle.addEventListener('click', function () {
        menu.classList.toggle('open');
      });
    }

    // ===== Confirmar acciones =====
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        var msg = btn.getAttribute('data-confirm') || '¿Estás seguro?';
        if (!confirm(msg)) {
          e.preventDefault();
          return false;
        }
      });
    });

    // ===== Auto submit on filter change =====
    var autoSubmitters = document.querySelectorAll('[data-autosubmit="1"]');
    autoSubmitters.forEach(function (sel) {
      sel.addEventListener('change', function () {
        var form = sel.closest('form');
        if (form) form.submit();
      });
    });

    // ===== Clear filters =====
    var btnClear = document.querySelector('[data-action="clear-filters"]');
    if (btnClear) {
      btnClear.addEventListener('click', function () {
        var form = btnClear.closest('form');
        if (form) {
          form.querySelectorAll('input, select, textarea').forEach(function (f) {
            if (f.name !== '') {
              if (f.type === 'checkbox' || f.type === 'radio') f.checked = false;
              else f.value = '';
            }
          });
          form.submit();
        }
      });
    }

    // ===== Year / category chips clickable (filtro rápido) =====
    document.querySelectorAll('[data-filter]').forEach(function (el) {
      el.addEventListener('click', function (e) {
        e.preventDefault();
        var field = el.getAttribute('data-filter');
        var value = el.getAttribute('data-value');
        var base  = el.getAttribute('data-url') || window.location.pathname;
        var sp = new URLSearchParams(window.location.search);
        if (value === '' || value === null) sp.delete(field);
        else sp.set(field, value);
        window.location.href = base + (sp.toString() ? '?' + sp.toString() : '');
      });
    });

    // ===== Scroll top btn =====
    var scrollBtn = document.querySelector('[data-action="scroll-top"]');
    if (scrollBtn) {
      window.addEventListener('scroll', function () {
        scrollBtn.style.display = window.scrollY > 400 ? 'inline-flex' : 'none';
      });
      scrollBtn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }
  });
})();
