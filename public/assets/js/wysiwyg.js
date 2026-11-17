/* ==========================================================================
   AMIGOS POR EL MUNDO - wysiwyg.js (Editor WYSIWYG simple sin dependencias)
   Uso:
     <textarea id="contenido" class="wysiwyg" name="content_html"></textarea>
     <script>Wysiwyg.init('#contenido');</script>
   ========================================================================== */

(function (global) {
  'use strict';

  var Wysiwyg = {
    instances: {},
    defaults: {
      buttons: ['bold','italic','underline','strike','h1','h2','h3','p','quote','ul','ol','link','image','unlink','left','center','right','table','undo','redo','source']
    }
  };

  Wysiwyg.init = function (selectorOrEl, options) {
    var opts = Object.assign({}, Wysiwyg.defaults, options || {});
    var els = (typeof selectorOrEl === 'string')
      ? document.querySelectorAll(selectorOrEl)
      : [selectorOrEl];
    els.forEach(function (el) {
      if (el.dataset.wysiwygReady === '1') return;

      if (el.tagName === 'DIV' && el.dataset.syncTo) {
        var target = document.getElementById(el.dataset.syncTo);
        if (target && target.tagName === 'TEXTAREA') {
          if (!target.value && el.innerHTML) target.value = el.innerHTML;
          el.dataset.wysiwygReady = '1';
          Wysiwyg.init(target, opts);
          try { el.parentNode.removeChild(el); } catch (e) {}
        }
        return;
      }

      if (el.tagName !== 'TEXTAREA') return;
      el.dataset.wysiwygReady = '1';
      buildEditor(el, opts);
    });
  };

  function buildEditor(textarea, opts) {
    var wrap = document.createElement('div');
    wrap.className = 'wysiwyg-editor';
    textarea.parentNode.insertBefore(wrap, textarea);

    // Toolbar
    var toolbar = document.createElement('div');
    toolbar.className = 'wysiwyg-toolbar';

    var buttonDefs = {
      bold:      { label: '<b>B</b>',     title: 'Negrita',          cmd: 'bold' },
      italic:    { label: '<i>I</i>',     title: 'Cursiva',         cmd: 'italic' },
      underline: { label: '<u>U</u>',     title: 'Subrayado',       cmd: 'underline' },
      strike:    { label: '<s>S</s>',     title: 'Tachado',         cmd: 'strikeThrough' },
      h1:        { label: 'H1',           title: 'Título 1',        cmd: 'formatBlock', val: 'H1' },
      h2:        { label: 'H2',           title: 'Título 2',        cmd: 'formatBlock', val: 'H2' },
      h3:        { label: 'H3',           title: 'Título 3',        cmd: 'formatBlock', val: 'H3' },
      p:         { label: '¶',            title: 'Párrafo',         cmd: 'formatBlock', val: 'P' },
      quote:     { label: '❝',            title: 'Cita',            cmd: 'formatBlock', val: 'BLOCKQUOTE' },
      ul:        { label: '• Lista',      title: 'Lista viñetas',   cmd: 'insertUnorderedList' },
      ol:        { label: '1. Lista',     title: 'Lista ordenada',  cmd: 'insertOrderedList' },
      link:      { label: '🔗',           title: 'Enlace',          custom: 'link' },
      image:     { label: '🖼️',           title: 'Insertar imagen desde tu equipo', custom: 'image' },
      unlink:    { label: '🔓',           title: 'Quitar enlace',   cmd: 'unlink' },
      left:      { label: '⟸',            title: 'Alinear izquierda', cmd: 'justifyLeft' },
      center:    { label: '≡',            title: 'Centrar',         cmd: 'justifyCenter' },
      right:     { label: '⟹',            title: 'Alinear derecha', cmd: 'justifyRight' },
      table:     { label: '▦',            title: 'Insertar tabla',  custom: 'table' },
      undo:      { label: '↶',            title: 'Deshacer',        cmd: 'undo' },
      redo:      { label: '↷',            title: 'Rehacer',         cmd: 'redo' },
      source:    { label: '</>',          title: 'Vista HTML',      custom: 'source' },
    };

    var lastSep = false;
    opts.buttons.forEach(function (b, i) {
      var def = buttonDefs[b];
      if (!def) return;
      if (i > 0 && lastSep && (['h1','quote','ul','link','left','undo','source'].includes(b))) {
        var sep = document.createElement('div'); sep.className = 'toolbar-sep'; toolbar.appendChild(sep);
        lastSep = false;
      }
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.innerHTML = def.label;
      btn.title = def.title;
      btn.dataset.cmd = def.cmd || '';
      btn.dataset.val = def.val || '';
      btn.dataset.custom = def.custom || '';
      btn.addEventListener('mousedown', function (e) { e.preventDefault(); });
      btn.addEventListener('click', function () { onButtonClick(btn, editor, textarea); });
      toolbar.appendChild(btn);
      lastSep = true;
    });
    wrap.appendChild(toolbar);

    // Contenido editable
    var editor = document.createElement('div');
    editor.className = 'wysiwyg-content';
    editor.setAttribute('contenteditable', 'true');
    editor.spellcheck = false;
    editor.innerHTML = textarea.value || '';
    wrap.appendChild(editor);

    // Ocultar textarea
    textarea.style.display = 'none';

    // Sincronizar
    function sync() { textarea.value = editor.innerHTML; }
    editor.addEventListener('input', sync);
    editor.addEventListener('blur', sync);
    editor.addEventListener('paste', function (e) {
      // Pegar como texto plano
      e.preventDefault();
      var text = (e.clipboardData || window.clipboardData).getData('text/plain');
      document.execCommand('insertText', false, text);
    });

    // Marcar estado activo en botones de formato al mover selección
    document.addEventListener('selectionchange', function () {
      toolbar.querySelectorAll('button[data-cmd]').forEach(function (b) {
        try {
          var cmd = b.dataset.cmd;
          if (['bold','italic','underline','strikeThrough'].includes(cmd)) {
            if (document.queryCommandState(cmd)) b.classList.add('is-active');
            else b.classList.remove('is-active');
          }
        } catch (e) {}
      });
    });

    Wysiwyg.instances[textarea.id || ('wysiwyg_' + Math.random().toString(36).slice(2, 8))] = {
      textarea: textarea, editor: editor, sync: sync
    };
  }

  function onButtonClick(btn, editor, textarea) {
    editor.focus();
    var cmd = btn.dataset.cmd;
    var val = btn.dataset.val || null;
    var custom = btn.dataset.custom;

    if (custom === 'source') {
      // Alternar vista fuente
      if (editor.getAttribute('contenteditable') === 'true') {
        var html = editor.innerHTML;
        editor.setAttribute('contenteditable','false');
        editor.textContent = html;
        editor.style.fontFamily = 'monospace';
        editor.style.whiteSpace = 'pre-wrap';
        btn.classList.add('is-active');
      } else {
        var src = editor.textContent;
        editor.innerHTML = src;
        editor.setAttribute('contenteditable','true');
        editor.style.fontFamily = '';
        editor.style.whiteSpace = '';
        btn.classList.remove('is-active');
      }
      // Mantener sincronizado
      textarea.value = editor.getAttribute('contenteditable') === 'true' ? editor.innerHTML : editor.textContent;
      return;
    }

    if (custom === 'link') {
      var url = prompt('Ingresa la URL del enlace:', 'https://');
      if (url) document.execCommand('createLink', false, url);
      textarea.value = editor.innerHTML;
      return;
    }

    if (custom === 'image') {
      insertImageFromComputer(editor, textarea, btn);
      return;
    }

    if (custom === 'table') {
      var rows = parseInt(prompt('Filas de la tabla:', '3'), 10);
      var cols = parseInt(prompt('Columnas de la tabla:', '3'), 10);
      if (!isNaN(rows) && !isNaN(cols) && rows > 0 && cols > 0) {
        var html = '<table>';
        for (var r = 0; r < rows; r++) {
          html += '<tr>';
          for (var c = 0; c < cols; c++) {
            html += (r === 0 ? '<th>' : '<td>') + '&nbsp;' + (r === 0 ? '</th>' : '</td>');
          }
          html += '</tr>';
        }
        html += '</table><p>&nbsp;</p>';
        document.execCommand('insertHTML', false, html);
        textarea.value = editor.innerHTML;
      }
      return;
    }

    if (cmd) {
      document.execCommand(cmd, false, val);
      textarea.value = editor.innerHTML;
    }
  }

  function insertImageFromComputer(editor, textarea, toolbarBtn) {
    editor.focus();
    // Guardar selección actual para insertar en la posición correcta
    var savedRange = null;
    try {
      var sel = window.getSelection();
      if (sel && sel.rangeCount > 0) savedRange = sel.getRangeAt(0).cloneRange();
    } catch (e) {}

    var input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/png,image/jpeg,image/webp,image/gif';
    input.style.display = 'none';
    document.body.appendChild(input);

    var btnWasActive = false;
    if (toolbarBtn) { btnWasActive = toolbarBtn.classList.contains('is-active'); toolbarBtn.classList.add('is-active'); }

    input.onchange = function () {
      var file = input.files && input.files[0];
      if (!file) { cleanup(); return; }
      if (file.size > 64 * 1024 * 1024) {
        alert('La imagen seleccionada es demasiado grande. Peso máximo permitido 64 MB.');
        cleanup();
        return;
      }
      if (!/^image\//i.test(file.type || '')) {
        alert('Archivo no permitido. Usa una imagen en formato JPG, PNG, WebP o GIF.');
        cleanup();
        return;
      }

      // Restaurar selección caret
      try {
        var sel2 = window.getSelection();
        if (savedRange && sel2) {
          sel2.removeAllRanges();
          sel2.addRange(savedRange);
        }
      } catch (e2) {}

      // Mostrar placeholder temporal "Subiendo..."
      var placeholderId = 'wysiwyg-upload-' + Math.random().toString(36).slice(2, 9);
      var tmpHtml = '<img id="' + placeholderId + '" src="" alt="Subiendo imagen…" '
        + ' style="max-width:100%; height:auto; opacity:.45; filter: blur(1px);" '
        + ' title="Subiendo imagen ' + encodeURIComponent(file.name || '').replace(/%20/g,' ') + ' ...">';
      document.execCommand('insertHTML', false, tmpHtml);
      textarea.value = editor.innerHTML;

      // Preparar request multipart
      var fd = new FormData();
      fd.append('inline_image', file);
      var csrfMeta = document.querySelector('meta[name="csrf-token"]');
      var csrfValue = csrfMeta ? csrfMeta.getAttribute('content') : '';
      if (!csrfValue) {
        var csrfInput = document.querySelector('input[name="_csrf_token"]');
        if (csrfInput) csrfValue = csrfInput.value || '';
      }
      if (csrfValue) fd.append('_csrf_token', csrfValue);

      var xhr = new XMLHttpRequest();
      xhr.open('POST', (window.__APP_BASE_URL__ || '') + '/admin/media/upload-inline', true);
      xhr.withCredentials = true;
      xhr.onload = function () {
        var resp;
        try { resp = JSON.parse(xhr.responseText || '{}'); }
        catch (err) { resp = { ok: false, error: 'Respuesta inválida del servidor al subir la imagen.' }; }
        var placeholder = document.getElementById(placeholderId);
        if (!resp || resp.ok !== true || !resp.url) {
          if (placeholder) placeholder.remove();
          alert('Error al subir la imagen: ' + ((resp && resp.error) ? resp.error : 'error desconocido.'));
          textarea.value = editor.innerHTML;
          cleanup();
          if (toolbarBtn) toolbarBtn.classList.toggle('is-active', btnWasActive);
          return;
        }
        // Reemplazar placeholder por <img> final
        var finalImg = document.createElement('img');
        finalImg.src = resp.url;
        finalImg.alt = (resp.original_name || file.name || 'Imagen').replace(/\.[^.]+$/, '');
        finalImg.className = 'img-fluid';
        finalImg.style.maxWidth = '100%';
        finalImg.style.height = 'auto';
        finalImg.style.borderRadius = '10px';
        finalImg.setAttribute('data-inline-upload', '1');
        if (placeholder) placeholder.replaceWith(finalImg);
        else document.execCommand('insertHTML', false, finalImg.outerHTML + '&nbsp;');
        textarea.value = editor.innerHTML;
        if (toolbarBtn) toolbarBtn.classList.toggle('is-active', btnWasActive);
        cleanup();
      };
      xhr.onerror = function () {
        var placeholder = document.getElementById(placeholderId);
        if (placeholder) placeholder.remove();
        textarea.value = editor.innerHTML;
        alert('No se pudo conectar con el servidor para subir la imagen. Revisa tu conexión a internet.');
        if (toolbarBtn) toolbarBtn.classList.toggle('is-active', btnWasActive);
        cleanup();
      };
      xhr.send(fd);
    };
    input.click();

    function cleanup() {
      try { if (input && input.parentNode) input.parentNode.removeChild(input); }
      catch (e) {}
    }
  }

  global.Wysiwyg = Wysiwyg;
  global.WYSIWYG = Wysiwyg;
})(window);
