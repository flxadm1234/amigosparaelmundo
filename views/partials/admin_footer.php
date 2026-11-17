      </div><!-- /app-container -->
    </main><!-- /app-main -->
  </div><!-- /app-wrapper -->
</div><!-- /app-layout -->

<script src="<?= asset('js/utils.js') ?>"></script>
<script src="<?= asset('js/admin.js') ?>"></script>
<script src="<?= asset('js/wysiwyg.js') ?>"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var editors = document.querySelectorAll('[data-editor="wysiwyg"]');
    var W = window.Wysiwyg || window.WYSIWYG;
    for (var i = 0; i < editors.length; i++) {
      if (W) W.init(editors[i]);
    }

    var layout = document.getElementById('adminLayout');
    var sidebarT = document.getElementById('sidebarToggle');
    var backdrop = document.getElementById('appBackdrop');
    var sidebar = document.getElementById('adminSidebar');

    function closeSidebar() {
      if (layout) layout.classList.remove('sidebar-open');
      if (sidebarT) sidebarT.setAttribute('aria-expanded', 'false');
      if (backdrop) {
        backdrop.hidden = true;
        setTimeout(function(){ if (backdrop) backdrop.style.opacity = '0'; }, 10);
      }
    }
    function openSidebar() {
      if (layout) layout.classList.add('sidebar-open');
      if (sidebarT) sidebarT.setAttribute('aria-expanded', 'true');
      if (backdrop) {
        backdrop.hidden = false;
        requestAnimationFrame(function(){ if (backdrop) backdrop.style.opacity = '1'; });
      }
    }
    if (sidebarT) {
      sidebarT.addEventListener('click', function() {
        var isOpen = layout && layout.classList.contains('sidebar-open');
        if (isOpen) closeSidebar(); else openSidebar();
      });
    }
    if (backdrop) {
      backdrop.addEventListener('click', closeSidebar);
    }
    window.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeSidebar();
    });
    window.addEventListener('resize', function() {
      if (window.innerWidth >= 992) closeSidebar();
    });

    var userChip = document.querySelector('[data-user-menu]');
    if (userChip) {
      var tgl = userChip.querySelector('.user-chip-toggle');
      var menu = userChip.querySelector('.user-menu');
      function closeUserMenu() {
        if (!menu) return;
        menu.hidden = true;
        if (tgl) tgl.setAttribute('aria-expanded', 'false');
      }
      function openUserMenu() {
        if (!menu) return;
        menu.hidden = false;
        if (tgl) tgl.setAttribute('aria-expanded', 'true');
      }
      if (tgl) {
        tgl.addEventListener('click', function(e) {
          e.stopPropagation();
          var open = !(menu && menu.hidden === false);
          if (open) openUserMenu(); else closeUserMenu();
        });
      }
      document.addEventListener('click', function(e) {
        if (!userChip.contains(e.target)) closeUserMenu();
      });
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeUserMenu();
      });
    }

    (function () {
      var wrap = document.getElementById('page_cover_preview_wrap');
      var img = document.getElementById('page_cover_preview_img');
      var fileInput = document.getElementById('f_cover_image');
      var btnRemove = document.getElementById('page_cover_remove_btn');
      var removeHdn = document.getElementById('f_remove_cover_image');
      if (!fileInput) return;
      if (wrap && img && btnRemove && removeHdn) {
        btnRemove.addEventListener('click', function (e) {
          e.preventDefault();
          img.removeAttribute('src');
          img.setAttribute('src', '');
          img.setAttribute('alt', '');
          wrap.classList.add('d-none');
          try { fileInput.value = ''; } catch (_) {}
          removeHdn.value = '1';
        });
      }
      fileInput.addEventListener('change', function () {
        if (!wrap || !img || !removeHdn) return;
        var file = fileInput.files && fileInput.files[0];
        if (!file) return;
        if (!/^image\//i.test(file.type || '')) {
          alert('Selecciona una imagen válida (JPG, PNG, WebP o GIF).');
          try { fileInput.value = ''; } catch (_) {}
          return;
        }
        removeHdn.value = '0';
        var reader = new FileReader();
        reader.onload = function (ev) {
          img.setAttribute('src', ev.target.result);
          wrap.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
      });
    })();

    function toSlug(s) {
      s = (s || '').toString().toLowerCase().trim();
      var from = "áàäâãåéèëêíìïîóòöôõøúùüûñçýÿ";
      var to   = "aaaaaaeeeeiiiioooooouuuuncyy";
      for (var k = 0; k < from.length; k++) {
        s = s.split(from.charAt(k)).join(to.charAt(k));
      }
      s = s.replace(/[^a-z0-9\s-]/g, '')
           .replace(/[\s_-]+/g, '-')
           .replace(/^-+|-+$/g, '');
      return s;
    }
    function copySlugTo(fromEl, toEl) {
      if (!fromEl || !toEl) return;
      var v = toSlug(fromEl.value || fromEl.textContent || '');
      var start = toEl.selectionStart, end = toEl.selectionEnd;
      toEl.value = v;
      try { toEl.dispatchEvent(new Event('input', { bubbles: true })); } catch (_e) {}
      try { toEl.setSelectionRange(start, end); } catch (_e) {}
    }
    var btns = document.querySelectorAll('.slug-from-name');
    for (var bi = 0; bi < btns.length; bi++) {
      (function (btn) {
        btn.addEventListener('click', function (e) {
          e.preventDefault();
          var fid = btn.getAttribute('data-from'), tid = btn.getAttribute('data-to');
          if (!fid || !tid) return;
          copySlugTo(document.getElementById(fid), document.getElementById(tid));
        });
      })(btns[bi]);
    }
    var sources = document.querySelectorAll('[data-slug-source]');
    for (var si = 0; si < sources.length; si++) {
      (function (src) {
        var tgtSel = src.getAttribute('data-slug-target');
        var tgt = tgtSel ? (document.querySelector(tgtSel) || document.getElementById(tgtSel)) : document.getElementById('f_slug');
        if (!tgt) return;
        src.addEventListener('blur', function () { if (!tgt.value || tgt.dataset.filledAuto === '1') { tgt.dataset.filledAuto = '1'; copySlugTo(src, tgt); } });
        src.addEventListener('keyup', function () { if (!tgt.value || tgt.dataset.filledAuto === '1') { tgt.dataset.filledAuto = '1'; copySlugTo(src, tgt); } });
      })(sources[si]);
    }
    var btnAuto = document.getElementById('slugAuto');
    if (btnAuto) {
      btnAuto.addEventListener('click', function (e) {
        e.preventDefault();
        var fid = btnAuto.getAttribute('data-source'), tid = btnAuto.getAttribute('data-target') || 'f_slug';
        var fEl = fid ? document.getElementById(fid) : null, tEl = document.getElementById(tid);
        if (fEl && tEl) { tEl.dataset.filledAuto = '1'; copySlugTo(fEl, tEl); }
      });
    }
  });
</script>
</body>
</html>
