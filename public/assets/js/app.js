/* ==========================================================================
   AMIGOS POR EL MUNDO - Portal de Noticias · app.js
   Vanilla JS puro · Sin librerías · Navegadores modernos
   ========================================================================== */

(function () {
  'use strict';

  /* ============================================================
     1) FLYERS CARRUSEL · window.FlyerCarousel

     ⚠️ SECCIÓN CRÍTICA NO DEBE IR DENTRO DE DOMContentLoaded:
     El HTML helpers.php render_flyers_carousel() genera botones con
     data-flyer-carousel. Si el usuario hace click ANTES de
     DOMContentLoaded, el delegado global init LZ inicializa.
     RC1 FIX: lookup del carousel robusto por data-flyer-carousel match.
     RC2 FIX: keyboard Enter/Space + arrows + dots botones tabbable.
     RC4 FIX: WeakMap processed para evitar doble click (delegado + init).
     ============================================================ */
  window.FlyerCarousel = {
    instances: new WeakMap(),
    processed: new WeakSet(),

    resolveCarousel: function (ref) {
      if (!ref) return null;
      if (ref.nodeType !== 1) return null;
      if (ref.classList && ref.classList.contains('flyers-carousel')) return ref;
      if (typeof ref.closest === 'function') {
        var cDirect = ref.closest('.flyers-carousel');
        if (cDirect) return cDirect;
      }
      var uid = (typeof ref.getAttribute === 'function') ? ref.getAttribute('data-flyer-carousel') : null;
      if (!uid) return null;
      var byAttr = document.querySelector('.flyers-carousel[data-flyer-carousel="' + uid + '"]');
      if (byAttr) return byAttr;
      var byId = document.getElementById(uid);
      if (byId && byId.classList && byId.classList.contains('flyers-carousel')) return byId;
      return null;
    },

    init: function (carousel) {
      if (!carousel || this.instances.has(carousel)) return;

      var track = carousel.querySelector('.flyers-track');
      var slides = carousel.querySelectorAll('.flyer-slide');
      var prevBtn = carousel.querySelector('.flyer-prev');
      var nextBtn = carousel.querySelector('.flyer-next');
      var dotsWrap = carousel.querySelector('.flyer-dots');
      var dots = dotsWrap ? dotsWrap.querySelectorAll('.flyer-dot, button, span') : [];
      var index = 0;
      var timer = null;
      var transitioning = false;
      var transitionMs = 400;
      var interval = parseInt(carousel.getAttribute('data-interval') || '6000', 10);
      if (isNaN(interval) || interval < 1500) interval = 6000;
      var isHover = false;
      var isFocus = false;

      if (!track || slides.length === 0) return;
      track.style.transition = 'transform ' + transitionMs + 'ms cubic-bezier(.22,.61,.36,1)';

      if (dotsWrap) {
        if (dots.length === 0) {
          for (var di = 0; di < slides.length; di++) {
            (function (dIdx) {
              var dot = document.createElement('button');
              dot.type = 'button';
              dot.className = 'flyer-dot' + (dIdx === 0 ? ' active' : '');
              dot.setAttribute('role', 'tab');
              dot.setAttribute('aria-selected', dIdx === 0 ? 'true' : 'false');
              dot.setAttribute('tabindex', dIdx === 0 ? '0' : '-1');
              dot.setAttribute('data-flyer-idx', String(dIdx));
              dot.setAttribute('aria-label', 'Ir a imagen ' + (dIdx + 1) + ' de ' + slides.length);
              dotsWrap.appendChild(dot);
            })(di);
          }
          dots = dotsWrap.querySelectorAll('.flyer-dot, button');
        } else {
          Array.prototype.forEach.call(dots, function (d, i) {
            if (!d.hasAttribute('role')) d.setAttribute('role', 'tab');
            if (!d.hasAttribute('aria-selected')) d.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
            if (!d.hasAttribute('tabindex')) d.setAttribute('tabindex', i === 0 ? '0' : '-1');
            if (d.classList && !d.classList.contains('flyer-dot') && d.tagName === 'BUTTON') d.classList.add('flyer-dot');
          });
        }
      }

      function updateDots() {
        if (!dotsWrap || !dots.length) return;
        Array.prototype.forEach.call(dots, function (d, i) {
          var on = (i === index);
          if (d.classList) d.classList.toggle('active', on);
          d.setAttribute && d.setAttribute('aria-selected', on ? 'true' : 'false');
          d.setAttribute && d.setAttribute('tabindex', on ? '0' : '-1');
        });
      }

      function updateSlidesAria() {
        Array.prototype.forEach.call(slides, function (s, i) {
          s.setAttribute && s.setAttribute('aria-hidden', i === index ? 'false' : 'true');
        });
      }

      function render() {
        if (track) {
          track.style.transform = 'translateX(-' + (index * 100) + '%)';
        }
        updateDots();
        updateSlidesAria();
      }

      function autoStart() {
        if (timer) clearInterval(timer);
        timer = setInterval(function () {
          if (!isHover && !isFocus) FlyerCarousel.next(carousel, { user: false });
        }, interval);
      }

      function autoStop() {
        if (timer) { clearInterval(timer); timer = null; }
      }

      this.instances.set(carousel, {
        getIndex: function () { return index; },
        setIndexRaw: function (i) { index = i; },
        count: function () { return slides.length; },
        stop: autoStop,
        start: autoStart,
        getTransitionMs: function () { return transitionMs; },
        isTransitioning: function () { return transitioning; },
        setTransitioning: function (v) { transitioning = !!v; }
      });

      if (prevBtn && !this.processed.has(prevBtn)) {
        this.processed.add(prevBtn);
        prevBtn.addEventListener('click', function (e) {
          e.preventDefault(); e.stopPropagation();
          FlyerCarousel.prev(carousel, { user: true });
        }, true);
      }
      if (nextBtn && !this.processed.has(nextBtn)) {
        this.processed.add(nextBtn);
        nextBtn.addEventListener('click', function (e) {
          e.preventDefault(); e.stopPropagation();
          FlyerCarousel.next(carousel, { user: true });
        }, true);
      }
      if (dotsWrap && dots.length) {
        Array.prototype.forEach.call(dots, function (d) {
          if (FlyerCarousel.processed.has(d)) return;
          FlyerCarousel.processed.add(d);
          d.addEventListener('click', function (e) {
            e.preventDefault(); e.stopPropagation();
            var di = parseInt((d.getAttribute && d.getAttribute('data-flyer-idx')) || d.getAttribute && d.getAttribute('data-index') || '0', 10);
            if (!isNaN(di)) FlyerCarousel.goTo(carousel, di, { user: true });
          }, true);
        });
      }

      carousel.addEventListener('mouseenter', function () { isHover = true; });
      carousel.addEventListener('mouseleave', function () { isHover = false; });
      carousel.addEventListener('focusin', function () { isFocus = true; });
      carousel.addEventListener('focusout', function (e) {
        if (!carousel.contains(e.relatedTarget)) isFocus = false;
      });

      var tsX = null, tsY = null, teX = null, teY = null;
      carousel.addEventListener('touchstart', function (e) {
        autoStop();
        if (!e.touches || !e.touches[0]) return;
        tsX = e.touches[0].clientX; tsY = e.touches[0].clientY; teX = null; teY = null;
      }, { passive: true });
      carousel.addEventListener('touchmove', function (e) {
        if (!e.touches || !e.touches[0]) return;
        teX = e.touches[0].clientX; teY = e.touches[0].clientY;
      }, { passive: true });
      carousel.addEventListener('touchend', function () {
        if (tsX !== null && teX !== null) {
          var dx = teX - tsX; var dy = (teY !== null && tsY !== null) ? teY - tsY : 0;
          if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 40) {
            if (dx < 0) FlyerCarousel.next(carousel, { user: true });
            else FlyerCarousel.prev(carousel, { user: true });
          }
        }
        tsX = null; tsY = null; teX = null; teY = null;
        autoStart();
      }, { passive: true });

      document.addEventListener('visibilitychange', function () {
        if (document.hidden) autoStop(); else autoStart();
      });

      carousel.addEventListener('keydown', function (e) {
        var key = e.key || '';
        if (key === 'ArrowRight') {
          e.preventDefault(); FlyerCarousel.next(carousel, { user: true });
        } else if (key === 'ArrowLeft') {
          e.preventDefault(); FlyerCarousel.prev(carousel, { user: true });
        } else if (key === 'Home') {
          e.preventDefault(); FlyerCarousel.goTo(carousel, 0, { user: true });
        } else if (key === 'End') {
          e.preventDefault(); FlyerCarousel.goTo(carousel, slides.length - 1, { user: true });
        }
      }, true);

      render();
      if (slides.length > 1) autoStart();
    },

    _doTransition: function (carousel, newIndex) {
      if (!this.instances.has(carousel)) return;
      var inst = this.instances.get(carousel);
      var count = inst.count();
      var total = Math.max(1, count);
      newIndex = ((newIndex % total) + total) % total;
      var cur = inst.getIndex();
      if (newIndex === cur) return;
      if (inst.isTransitioning()) return;
      inst.setTransitioning(true);
      inst.setIndexRaw(newIndex);
      var track = carousel.querySelector('.flyers-track');
      if (track) {
        track.style.transform = 'translateX(-' + (newIndex * 100) + '%)';
      }
      var dotsWrap = carousel.querySelector('.flyer-dots');
      var dots = dotsWrap ? dotsWrap.querySelectorAll('.flyer-dot, button, span') : [];
      if (dots.length) {
        Array.prototype.forEach.call(dots, function (d, i) {
          var on = (i === newIndex);
          if (d.classList) d.classList.toggle('active', on);
          d.setAttribute && d.setAttribute('aria-selected', on ? 'true' : 'false');
          d.setAttribute && d.setAttribute('tabindex', on ? '0' : '-1');
        });
      }
      var slides = carousel.querySelectorAll('.flyer-slide');
      Array.prototype.forEach.call(slides, function (s, i) {
        s.setAttribute && s.setAttribute('aria-hidden', i === newIndex ? 'false' : 'true');
      });
      var self = this;
      setTimeout(function () {
        inst.setTransitioning(false);
        try { inst.start && inst.start(); } catch (_) {}
      }, inst.getTransitionMs() + 30);
      try { inst.stop && inst.stop(); } catch (_) {}
    },

    next: function (btnOrCarousel, opts) {
      var carousel = this.resolveCarousel(btnOrCarousel);
      if (!carousel) return;
      if (!this.instances.has(carousel)) this.init(carousel);
      if (!this.instances.has(carousel)) return;
      var inst = this.instances.get(carousel);
      this._doTransition(carousel, inst.getIndex() + 1);
    },

    prev: function (btnOrCarousel, opts) {
      var carousel = this.resolveCarousel(btnOrCarousel);
      if (!carousel) return;
      if (!this.instances.has(carousel)) this.init(carousel);
      if (!this.instances.has(carousel)) return;
      var inst = this.instances.get(carousel);
      this._doTransition(carousel, inst.getIndex() - 1);
    },

    goTo: function (dotOrCarousel, idx, opts) {
      var carousel;
      if (typeof idx === 'number') {
        carousel = this.resolveCarousel(dotOrCarousel);
      } else {
        carousel = this.resolveCarousel(dotOrCarousel);
        idx = parseInt(((dotOrCarousel && typeof dotOrCarousel.getAttribute === 'function')
          ? (dotOrCarousel.getAttribute('data-flyer-idx') || dotOrCarousel.getAttribute('data-index') || '0')
          : '0'), 10);
      }
      if (!carousel) return;
      if (typeof idx !== 'number' || isNaN(idx)) return;
      if (!this.instances.has(carousel)) this.init(carousel);
      if (!this.instances.has(carousel)) return;
      var inst = this.instances.get(carousel);
      idx = Math.max(0, Math.min(idx, Math.max(0, inst.count() - 1)));
      this._doTransition(carousel, idx);
    }
  };

  /* ==========================================================================
     1b) FLYER BTNS DELEGADO GLOBAL capture phase (FIX clicks ANTES de init)
     ========================================================================== */
  function _flyerResolveCarouselFromTarget(t) {
    var uid = (t && typeof t.getAttribute === 'function') ? t.getAttribute('data-flyer-carousel') : null;
    if (uid) {
      var cByAttr = document.querySelector('.flyers-carousel[data-flyer-carousel="' + uid + '"]');
      if (cByAttr) return cByAttr;
      var cById = document.getElementById(uid);
      if (cById && cById.classList && cById.classList.contains('flyers-carousel')) return cById;
    }
    if (t && typeof t.closest === 'function') return t.closest('.flyers-carousel');
    return null;
  }
  document.addEventListener('click', function (ev) {
    var t = ev && ev.target ? (ev.target.nodeType === 1 ? ev.target : (ev.target.parentElement || document.body)) : null;
    if (!t || typeof FlyerCarousel === 'undefined') return;
    var btn = (t.classList && (t.classList.contains('flyer-prev') || t.classList.contains('flyer-next'))) ? t : (t.closest ? t.closest('.flyer-prev, .flyer-next') : null);
    if (btn) {
      ev.preventDefault(); ev.stopPropagation ? ev.stopPropagation() : void 0;
      var carousel = _flyerResolveCarouselFromTarget(btn);
      if (!carousel) return;
      if (!FlyerCarousel.instances.has(carousel)) FlyerCarousel.init(carousel);
      if (btn.classList.contains('flyer-next')) FlyerCarousel.next(carousel, { user: true });
      else FlyerCarousel.prev(carousel, { user: true });
      return;
    }
    var dotSel = '.flyer-dots .flyer-dot, .flyer-dots button, .flyer-dots span';
    var dotCandidate = t.matches ? (t.matches(dotSel) ? t : (t.closest ? t.closest(dotSel) : null)) : null;
    if (dotCandidate) {
      ev.preventDefault(); ev.stopPropagation ? ev.stopPropagation() : void 0;
      var dCarousel = _flyerResolveCarouselFromTarget(dotCandidate);
      if (!dCarousel) return;
      if (!FlyerCarousel.instances.has(dCarousel)) FlyerCarousel.init(dCarousel);
      var dIdx = parseInt(((dotCandidate && typeof dotCandidate.getAttribute === 'function')
        ? (dotCandidate.getAttribute('data-flyer-idx') || dotCandidate.getAttribute('data-index') || '0')
        : '0'), 10);
      FlyerCarousel.goTo(dCarousel, dIdx, { user: true });
    }
  }, true);

  /* ============================================================
     1c) KEYDOWN GLOBAL (Enter/Space focusable nav/dots)
     ============================================================ */
  document.addEventListener('keydown', function (ev) {
    var k = ev.key;
    if (k !== 'Enter' && k !== ' ') return;
    var t = ev && ev.target;
    if (!t || t.nodeType !== 1) return;
    var isBtn = t.tagName === 'BUTTON';
    if (!isBtn) return;
    var nav = t.classList && (t.classList.contains('flyer-prev') || t.classList.contains('flyer-next'));
    var isDot = t.classList && t.classList.contains('flyer-dot');
    if (!nav && !isDot) return;
    ev.preventDefault();
    if (typeof FlyerCarousel === 'undefined') return;
    var c = FlyerCarousel.resolveCarousel(t);
    if (!c) return;
    if (!FlyerCarousel.instances.has(c)) FlyerCarousel.init(c);
    if (t.classList.contains('flyer-next')) FlyerCarousel.next(c, { user: true });
    else if (t.classList.contains('flyer-prev')) FlyerCarousel.prev(c, { user: true });
    else if (isDot) {
      var di = parseInt(((typeof t.getAttribute === 'function')
        ? (t.getAttribute('data-flyer-idx') || t.getAttribute('data-index') || '0') : '0'), 10);
      if (!isNaN(di)) FlyerCarousel.goTo(c, di, { user: true });
    }
  }, true);

  document.addEventListener('DOMContentLoaded', function () {

    /* Inicializar carruseles existentes en el DOM cuando esté listo */
    document.querySelectorAll('.flyers-carousel').forEach(function (c) {
      FlyerCarousel.init(c);
    });

    /* ============================================================
       2) MENÚ MÓVIL · Offcanvas
       ============================================================ */
    var menuToggle = document.getElementById('rm-menu-toggle') || document.querySelector('.rm-menu-toggle');
    var mobileNav = document.querySelector('.rm-mobile-nav');
    var navBackdrop = document.querySelector('.rm-nav-backdrop');
    var mobileNavClose = document.querySelector('.rm-mobile-nav-close');
    var body = document.body;

    function openMobileNav() {
      if (menuToggle) menuToggle.classList.add('is-open');
      if (mobileNav) mobileNav.classList.add('open');
      if (navBackdrop) navBackdrop.classList.add('show');
      if (body) body.classList.add('rm-mobile-open');
    }
    function closeMobileNav() {
      if (menuToggle) menuToggle.classList.remove('is-open');
      if (mobileNav) mobileNav.classList.remove('open');
      if (navBackdrop) navBackdrop.classList.remove('show');
      if (body) body.classList.remove('rm-mobile-open');
    }

    if (menuToggle) {
      menuToggle.addEventListener('click', function () {
        if (mobileNav && mobileNav.classList.contains('open')) closeMobileNav();
        else openMobileNav();
      });
    }
    if (mobileNavClose) mobileNavClose.addEventListener('click', closeMobileNav);
    if (navBackdrop) navBackdrop.addEventListener('click', closeMobileNav);

    document.querySelectorAll('.rm-mobile-nav-link').forEach(function (link) {
      link.addEventListener('click', function () { closeMobileNav(); });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && mobileNav && mobileNav.classList.contains('open')) {
        closeMobileNav();
      }
    });

    /* ============================================================
       3) GALERÍA LIGHTBOX
       ============================================================ */
    var lightbox = null;
    var lbImages = [];
    var lbIndex = 0;

    function ensureLightbox() {
      if (lightbox) return lightbox;
      lightbox = document.createElement('div');
      lightbox.className = 'rm-lightbox';
      lightbox.setAttribute('hidden', '');
      lightbox.innerHTML =
        '<div class="rm-lb-overlay"></div>' +
        '<div class="rm-lb-topbar">' +
          '<div class="rm-lb-info">' +
            '<div class="rm-lb-caption"></div>' +
            '<div class="rm-lb-counter"></div>' +
          '</div>' +
          '<div class="rm-lb-ctrls">' +
            '<button type="button" class="rm-lb-btn rm-lb-btn-download" title="Descargar">⬇</button>' +
            '<button type="button" class="rm-lb-btn rm-lb-btn-close" title="Cerrar">✕</button>' +
          '</div>' +
        '</div>' +
        '<button type="button" class="rm-lb-nav rm-lb-nav-prev" aria-label="Anterior">‹</button>' +
        '<button type="button" class="rm-lb-nav rm-lb-nav-next" aria-label="Siguiente">›</button>' +
        '<div class="rm-lb-stage">' +
          '<div class="rm-lb-img-wrap"><img id="rmLbImg" src="" alt=""></div>' +
        '</div>';
      document.body.appendChild(lightbox);

      lightbox.querySelector('.rm-lb-overlay').addEventListener('click', closeLightbox);
      lightbox.querySelector('.rm-lb-btn-close').addEventListener('click', closeLightbox);
      lightbox.querySelector('.rm-lb-nav-prev').addEventListener('click', function () { lbStep(-1); });
      lightbox.querySelector('.rm-lb-nav-next').addEventListener('click', function () { lbStep(1); });
      lightbox.querySelector('.rm-lb-btn-download').addEventListener('click', downloadLbImage);

      document.addEventListener('keydown', function (e) {
        if (lightbox.hasAttribute('hidden')) return;
        if (e.key === 'Escape') closeLightbox();
        else if (e.key === 'ArrowLeft') lbStep(-1);
        else if (e.key === 'ArrowRight') lbStep(1);
      });

      return lightbox;
    }

    function openLightbox(galleryItems, startIdx) {
      ensureLightbox();
      lbImages = Array.from(galleryItems).map(function (item) {
        var img = item.querySelector('img') || item;
        return {
          src: img.getAttribute('data-full') || img.src,
          alt: img.alt || '',
          caption: img.getAttribute('data-caption') || img.alt || ''
        };
      });
      lbIndex = Math.max(0, Math.min(startIdx || 0, lbImages.length - 1));
      renderLightbox();
      lightbox.removeAttribute('hidden');
      document.body.style.overflow = 'hidden';
    }

    function renderLightbox() {
      if (!lightbox || lbImages.length === 0) return;
      var img = lbImages[lbIndex];
      var lbImg = lightbox.querySelector('#rmLbImg');
      lbImg.src = img.src;
      lbImg.alt = img.alt;
      lightbox.querySelector('.rm-lb-caption').textContent = img.caption;
      lightbox.querySelector('.rm-lb-counter').textContent = (lbIndex + 1) + ' / ' + lbImages.length;
    }

    function lbStep(delta) {
      if (lbImages.length === 0) return;
      lbIndex = (lbIndex + delta + lbImages.length) % lbImages.length;
      renderLightbox();
    }

    function closeLightbox() {
      if (!lightbox) return;
      lightbox.setAttribute('hidden', '');
      document.body.style.overflow = '';
    }

    function downloadLbImage() {
      if (!lbImages[lbIndex]) return;
      var a = document.createElement('a');
      a.href = lbImages[lbIndex].src;
      a.download = '';
      a.target = '_blank';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    }

    document.querySelectorAll('.gallery-item, .pmg-img-box, [data-lightbox]').forEach(function (item) {
      item.addEventListener('click', function (e) {
        e.preventDefault();
        var selector = item.getAttribute('data-gallery') || '.gallery-item';
        var parent = item.closest('.rm-gallery, .gallery-grid, [data-gallery-wrap]') || document;
        var items = parent.querySelectorAll(selector);
        var itemsArr = Array.from(items);
        var startIdx = Math.max(0, itemsArr.indexOf(item));
        openLightbox(itemsArr.length ? itemsArr : [item], startIdx);
      });
    });

    /* ============================================================
       4) SHARE BUTTONS · Copy link + redes sociales
       ============================================================ */
    function showToast(msg, type) {
      var wrap = document.querySelector('.rm-toast-wrap');
      if (!wrap) {
        wrap = document.createElement('div');
        wrap.className = 'rm-toast-wrap';
        document.body.appendChild(wrap);
      }
      var toast = document.createElement('div');
      toast.className = 'rm-toast ' + (type || 'success');
      var icon = (type === 'error') ? '⚠' : (type === 'info' ? 'ℹ' : '✓');
      toast.innerHTML = '<span class="rm-toast-icon">' + icon + '</span><span>' + msg + '</span>';
      wrap.appendChild(toast);
      setTimeout(function () {
        toast.style.transition = 'opacity .3s ease, transform .3s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(function () {
          if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 300);
      }, 3000);
    }
    window.AmigosPorElMundoToast = showToast;

    function copyToClipboard(text) {
      if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
      }
      return new Promise(function (resolve, reject) {
        try {
          var ta = document.createElement('textarea');
          ta.value = text;
          ta.style.position = 'fixed';
          ta.style.top = '-9999px';
          ta.style.left = '-9999px';
          ta.setAttribute('readonly', '');
          document.body.appendChild(ta);
          ta.select();
          ta.setSelectionRange(0, text.length);
          document.execCommand('copy');
          document.body.removeChild(ta);
          resolve();
        } catch (e) { reject(e); }
      });
    }

    document.querySelectorAll('[data-share="copy"], .share-btn.copy-link, .copy-link-btn').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var url = btn.getAttribute('data-url') || window.location.href;
        copyToClipboard(url).then(function () {
          btn.classList.add('copied');
          var original = btn.textContent;
          if (btn.getAttribute('data-copied-text')) {
            btn.textContent = btn.getAttribute('data-copied-text');
          } else {
            var icon = btn.querySelector('.share-icon');
            if (btn.innerHTML.indexOf('Enlace copiado') === -1 && !icon) {
              btn.innerHTML = '✓ Enlace copiado';
            }
          }
          showToast('¡Enlace copiado!', 'success');
          setTimeout(function () {
            btn.classList.remove('copied');
            if (!btn.getAttribute('data-copied-text')) {
              if (original && original.indexOf('Enlace') === -1) btn.textContent = original;
            }
          }, 2500);
        }).catch(function () {
          showToast('No se pudo copiar el enlace', 'error');
        });
      });
    });

    document.querySelectorAll('[data-share]').forEach(function (btn) {
      var type = btn.getAttribute('data-share');
      if (type === 'copy' || !type) return;
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var url = encodeURIComponent(btn.getAttribute('data-url') || window.location.href);
        var title = encodeURIComponent(btn.getAttribute('data-title') || document.title);
        var shareUrl = '';
        switch (type) {
          case 'whatsapp':
            shareUrl = 'https://api.whatsapp.com/send?text=' + title + '%20' + url;
            break;
          case 'facebook':
            shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + url;
            break;
          case 'twitter':
          case 'x':
            shareUrl = 'https://twitter.com/intent/tweet?url=' + url + '&text=' + title;
            break;
          case 'telegram':
            shareUrl = 'https://t.me/share/url?url=' + url + '&text=' + title;
            break;
          case 'linkedin':
            shareUrl = 'https://www.linkedin.com/sharing/share-offsite/?url=' + url;
            break;
          case 'email':
            shareUrl = 'mailto:?subject=' + title + '&body=' + url;
            window.location.href = shareUrl;
            return;
        }
        if (shareUrl) {
          window.open(shareUrl, '_blank', 'width=600,height=500,noopener,noreferrer');
        }
      });
    });

    /* ============================================================
       5) RADIO MINI-PLAYER HEADER + Radio Player page
       ============================================================ */
    function setupRadioPlayer(playBtn, audioEl, stateClass) {
      if (!playBtn || !audioEl) return;
      stateClass = stateClass || 'playing';
      playBtn.addEventListener('click', function () {
        if (audioEl.paused) {
          audioEl.play().then(function () {
            playBtn.classList.add(stateClass);
            var icon = playBtn.querySelector('.play-icon') || playBtn;
            if (icon && icon.classList) icon.classList.add('playing');
            playBtn.setAttribute('aria-label', 'Pausar');
          }).catch(function (err) {
            console.warn('Radio play error:', err);
            showToast('No se pudo reproducir. Revisa tu conexión.', 'error');
          });
        } else {
          audioEl.pause();
          playBtn.classList.remove(stateClass);
          playBtn.setAttribute('aria-label', 'Reproducir');
        }
      });
      audioEl.addEventListener('play', function () { playBtn.classList.add(stateClass); });
      audioEl.addEventListener('pause', function () { playBtn.classList.remove(stateClass); });
      audioEl.addEventListener('ended', function () { playBtn.classList.remove(stateClass); });
    }

    var miniPlay = document.getElementById('rm-mini-play') || document.querySelector('.rm-mini-play');
    var miniAudio = document.getElementById('rm-radio-audio') || document.querySelector('.rm-header-right audio');
    if (miniPlay && miniAudio) setupRadioPlayer(miniPlay, miniAudio, 'playing');

    var bigPlay = document.querySelector('.rm-radio-play');
    var bigAudio = document.querySelector('.rm-radio-player audio, .rm-audio-player');
    if (bigPlay && bigAudio) setupRadioPlayer(bigPlay, bigAudio, 'playing');

    document.querySelectorAll('.rm-widget-radio .rm-mini-play').forEach(function (btn) {
      var parent = btn.closest('.rm-widget-radio');
      if (!parent) return;
      var audio = parent.querySelector('audio');
      if (!audio) {
        audio = document.querySelector('.rm-header-right audio');
      }
      if (audio) setupRadioPlayer(btn, audio, 'playing');
    });

    var volumeInput = document.querySelector('.rm-radio-volume input[type="range"]');
    if (volumeInput && bigAudio) {
      volumeInput.addEventListener('input', function () {
        bigAudio.volume = parseFloat(volumeInput.value) / 100;
      });
    }

    /* ============================================================
       6) VIDEO CARDS · Modal / Embed
       ============================================================ */
    var videoModal = null;

    function ensureVideoModal() {
      if (videoModal) return videoModal;
      videoModal = document.createElement('div');
      videoModal.className = 'rm-video-modal';
      videoModal.setAttribute('hidden', '');
      videoModal.innerHTML =
        '<div class="rm-vm-overlay"></div>' +
        '<button type="button" class="rm-vm-close" aria-label="Cerrar video">✕</button>' +
        '<div class="rm-vm-content"><div class="rm-vm-frame"></div></div>';
      document.body.appendChild(videoModal);

      videoModal.querySelector('.rm-vm-overlay').addEventListener('click', closeVideoModal);
      videoModal.querySelector('.rm-vm-close').addEventListener('click', closeVideoModal);
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !videoModal.hasAttribute('hidden')) closeVideoModal();
      });
      return videoModal;
    }

    function openVideoModal(src, embedType) {
      ensureVideoModal();
      var frame = videoModal.querySelector('.rm-vm-frame');
      frame.innerHTML = '';
      embedType = embedType || 'iframe';
      if (embedType === 'video') {
        var v = document.createElement('video');
        v.src = src;
        v.controls = true;
        v.autoplay = true;
        v.playsInline = true;
        frame.appendChild(v);
      } else {
        var ifr = document.createElement('iframe');
        ifr.src = src + (src.indexOf('?') > -1 ? '&' : '?') + 'autoplay=1&rel=0';
        ifr.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        ifr.allowFullscreen = true;
        ifr.setAttribute('loading', 'eager');
        frame.appendChild(ifr);
      }
      videoModal.removeAttribute('hidden');
      document.body.style.overflow = 'hidden';
    }

    function closeVideoModal() {
      if (!videoModal) return;
      var frame = videoModal.querySelector('.rm-vm-frame');
      frame.innerHTML = '';
      videoModal.setAttribute('hidden', '');
      document.body.style.overflow = '';
    }

    document.querySelectorAll('.video-card, [data-video-src]').forEach(function (card) {
      card.style.cursor = 'pointer';
      card.addEventListener('click', function (e) {
        e.preventDefault();
        var src = card.getAttribute('data-video-src') || card.getAttribute('data-src');
        var type = card.getAttribute('data-video-type') || 'iframe';
        if (!src) {
          var a = card.querySelector('a');
          if (a) src = a.href;
        }
        if (src) openVideoModal(src, type);
      });
    });

    document.querySelectorAll('[data-open-video]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var src = btn.getAttribute('data-open-video');
        var type = btn.getAttribute('data-video-type') || 'iframe';
        if (src) openVideoModal(src, type);
      });
    });

    /* ============================================================
       7) SMOOTH SCROLL a[href^="#"]
       ============================================================ */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
      anchor.addEventListener('click', function (e) {
        var href = anchor.getAttribute('href');
        if (!href || href === '#' || href.length < 2) return;
        var target = null;
        try { target = document.querySelector(href); } catch (err) { return; }
        if (!target) return;
        if (anchor.getAttribute('data-smooth') === '0') return;
        e.preventDefault();
        var offset = 80;
        var rect = target.getBoundingClientRect();
        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        var top = rect.top + scrollTop - offset;
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
        if (history && history.replaceState) {
          history.replaceState(null, '', href);
        }
      });
    });

    /* ============================================================
       8) PWA-like: Nav shadow toggle + Back to top
       ============================================================ */
    var mainNav = document.querySelector('.rm-nav, .site-header');
    var backTop = document.querySelector('.rm-back-top, [data-action="scroll-top"]');

    if (!backTop) {
      backTop = document.createElement('button');
      backTop.type = 'button';
      backTop.className = 'rm-back-top';
      backTop.setAttribute('aria-label', 'Volver arriba');
      backTop.innerHTML = '↑';
      document.body.appendChild(backTop);
    }

    var lastScroll = 0;
    function onScroll() {
      var y = window.pageYOffset || document.documentElement.scrollTop;
      if (mainNav) {
        if (y > 8) mainNav.classList.add('scrolled');
        else mainNav.classList.remove('scrolled');
      }
      if (backTop) {
        if (y > 300) backTop.classList.add('show');
        else backTop.classList.remove('show');
      }
      lastScroll = y;
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    backTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    /* ============================================================
       9) SEARCH FORM · Enter key submit correcto
       ============================================================ */
    document.querySelectorAll('.rm-nav-search form, [role="search"] form, form[data-search="1"]').forEach(function (form) {
      var input = form.querySelector('input[type="search"], input[name="q"], input[name="s"], input[name="query"]');
      if (!input) return;
      form.addEventListener('submit', function (e) {
        var val = input.value.trim();
        if (val.length < 2) {
          e.preventDefault();
          input.focus();
          input.style.borderColor = 'var(--rm-breaking)';
          setTimeout(function () { input.style.borderColor = ''; }, 1500);
          showToast('Escribe al menos 2 caracteres para buscar', 'info');
          return false;
        }
      });
      input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          form.requestSubmit ? form.requestSubmit() : form.submit();
        }
      });
    });

    /* ============================================================
       EXTRA: Podcasts audio controls visual sync
       ============================================================ */
    document.querySelectorAll('.podcast-audio audio').forEach(function (audio) {
      var card = audio.closest('.podcast-card');
      if (!card) return;
      var playBtn = card.querySelector('[data-podcast-play]');
      if (!playBtn) return;
      playBtn.addEventListener('click', function () {
        if (audio.paused) audio.play();
        else audio.pause();
      });
      audio.addEventListener('play', function () { playBtn.classList.add('playing'); });
      audio.addEventListener('pause', function () { playBtn.classList.remove('playing'); });
    });

    /* ============================================================
       EXTRA: Animación fade-in al cargar imágenes
       ============================================================ */
    document.querySelectorAll('img[data-src], img.lazy').forEach(function (img) {
      img.classList.add('rm-skeleton');
      img.addEventListener('load', function () {
        img.classList.remove('rm-skeleton');
        img.classList.add('rm-fade-in');
      });
      if (img.complete) {
        img.classList.remove('rm-skeleton');
      }
    });

    /* ============================================================
       EXTRA: Cards hover accesibilidad (teclado)
       ============================================================ */
    document.querySelectorAll('.news-card, .video-card, .flyer-card, .podcast-card').forEach(function (card) {
      card.setAttribute('tabindex', '0');
      card.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          var link = card.querySelector('a');
          if (link) {
            e.preventDefault();
            link.click();
          }
        }
      });
    });

    /* ============================================================
       10) HERO ROTATOR · Noticias destacadas rotativas
           Layout: 1 featured + 3 thumbs apiladas
           Swap cíclico user spec:
             featured → último thumb
             thumb[0] → nuevo featured
       ============================================================ */
    (function () {
      var rotEl = document.getElementById('rmHeroRotator');
      if (!rotEl) return;
      var dataEl = document.getElementById('heroRotatorData');
      var items = [];
      if (dataEl && dataEl.textContent) {
        try { items = JSON.parse(dataEl.textContent); } catch (e) { items = []; }
      }
      if (!Array.isArray(items)) items = [];
      items = items.filter(function (it) { return it && typeof it.title === 'string' && it.title.length > 0; });
      if (items.length === 0) return;

      var INTERVAL = parseInt(rotEl.getAttribute('data-interval') || '8000', 10);
      if (isNaN(INTERVAL) || INTERVAL < 2000) INTERVAL = 8000;

      var featuredSlot = rotEl.querySelector('[data-slot="featured"]');
      var thumbSlots = [
        rotEl.querySelector('[data-slot="thumb-1"]'),
        rotEl.querySelector('[data-slot="thumb-2"]'),
        rotEl.querySelector('[data-slot="thumb-3"]')
      ];
      var progressEl = rotEl.querySelector('.rm-hero-rotator-progress');
      var gridEl = rotEl.querySelector('.rm-hero-rotator-grid');
      if (!featuredSlot || !thumbSlots[0] || !gridEl) return;

      while (items.length < 4) items.push(null);
      var state = {
        visible: items.slice(),
        paused: false,
        transitioning: false,
        progressStart: 0,
        progress: 0,
        raf: null,
        lastManualTs: 0
      };

      function escHtml(str) {
        if (str == null) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
      }

      function renderFeatured(item) {
        if (!item) {
          return '' +
            '<div class="rm-hero-card rm-hero-card--featured rm-hero-card--empty">' +
            '<div class="rm-hero-card-media"><div class="rm-hero-card-empty-ph">—</div></div>' +
            '<div class="rm-hero-card-body">' +
            '<h2 class="rm-hero-card-title"><span>Esperando noticia</span></h2>' +
            '<p class="rm-hero-card-excerpt">Publica noticias en el panel para activar la rotación.</p>' +
            '</div></div>';
        }
        var badges = '';
        if (item.section) badges += '<span class="rm-hero-card-cat">' + escHtml(item.section) + '</span>';
        if (item.is_breaking) badges += '<span class="rm-hero-card-breaking">🔴 ÚLTIMA HORA</span>';
        var excerpt = item.excerpt ? ('<p class="rm-hero-card-excerpt">' + escHtml(item.excerpt) + '</p>') : '';
        var footItems = [];
        if (item.date) footItems.push('<span>📅 ' + escHtml(item.date) + '</span>');
        if (item.author) footItems.push('<span>✍️ ' + escHtml(item.author) + '</span>');
        footItems.push('<span class="rm-hero-card-cta">Leer noticia →</span>');
        var body = '' +
          '<div class="rm-hero-card-badges">' + badges + '</div>' +
          '<h2 class="rm-hero-card-title"><span>' + escHtml(item.title) + '</span></h2>' +
          excerpt +
          '<div class="rm-hero-card-foot">' + footItems.join('') + '</div>';
        return '' +
          '<article class="rm-hero-card rm-hero-card--featured">' +
          '<a href="' + escHtml(item.url || '#') + '" aria-label="' + escHtml(item.title) + '">' +
          '<div class="rm-hero-card-media">' +
          '<img src="' + escHtml(item.image || '') + '" alt="' + escHtml(item.title) + '" loading="eager" fetchpriority="high">' +
          '<div class="rm-hero-card-overlay" aria-hidden="true"></div>' +
          '</div>' +
          '<div class="rm-hero-card-body">' + body + '</div>' +
          '</a></article>';
      }

      function renderThumb(item) {
        if (!item) {
          return '' +
            '<div class="rm-hero-card rm-hero-card--thumb rm-hero-card--empty">' +
            '<div class="rm-hero-card-media"><div class="rm-hero-card-empty-ph">—</div></div>' +
            '<div class="rm-hero-card-body">' +
            '<span class="rm-hero-card-cat rm-hero-card-cat--thumb">Esperando noticia</span>' +
            '</div></div>';
        }
        var sec = item.section ? ('<span class="rm-hero-card-cat rm-hero-card-cat--thumb">' + escHtml(item.section) + '</span>') : '';
        return '' +
          '<article class="rm-hero-card rm-hero-card--thumb">' +
          '<a href="' + escHtml(item.url || '#') + '" aria-label="' + escHtml(item.title) + '">' +
          '<div class="rm-hero-card-media">' +
          '<img src="' + escHtml(item.image || '') + '" alt="' + escHtml(item.title) + '" loading="lazy">' +
          '<div class="rm-hero-card-overlay" aria-hidden="true"></div>' +
          '</div>' +
          '<div class="rm-hero-card-body">' + sec +
          '<h3 class="rm-hero-card-title rm-hero-card-title--thumb"><span>' + escHtml(item.title) + '</span></h3>' +
          '</div>' +
          '</a></article>';
      }

      function paint() {
        var vis = state.visible;
        var fHtml = renderFeatured(vis[0] || null);
        if (featuredSlot.innerHTML !== fHtml) featuredSlot.innerHTML = fHtml;
        for (var t = 0; t < 3; t++) {
          var slot = thumbSlots[t];
          if (!slot) continue;
          var h = renderThumb(vis[t + 1] || null);
          if (slot.innerHTML !== h) slot.innerHTML = h;
        }
      }

      function go(dir) {
        if (state.transitioning) return;
        var now = Date.now();
        state.lastManualTs = now;
        if (items.length < 2) return;
        state.transitioning = true;
        gridEl.classList.add('is-changing-out');
        setTimeout(function () {
          var v = state.visible;
          if (dir >= 0) {
            var first = v.shift();
            v.push(first);
          } else {
            var last = v.pop();
            v.unshift(last);
          }
          state.visible = v;
          paint();
          gridEl.classList.remove('is-changing-out');
          gridEl.classList.add('is-changing-in');
          setTimeout(function () {
            gridEl.classList.remove('is-changing-in');
            state.transitioning = false;
            resetProgress();
          }, 320);
        }, 260);
      }

      function resetProgress() {
        state.progressStart = performance.now();
        state.progress = 0;
        if (progressEl) progressEl.style.backgroundSize = '0% 100%';
      }

      function tick(now) {
        if (!state.paused) {
          var elapsed = now - state.progressStart;
          var pct = Math.min(100, (elapsed / INTERVAL) * 100);
          state.progress = pct;
          if (progressEl) progressEl.style.backgroundSize = pct + '% 100%';
          if (elapsed >= INTERVAL) {
            go(1);
            return;
          }
        } else {
          state.progressStart = now - (state.progress / 100) * INTERVAL;
        }
        state.raf = requestAnimationFrame(tick);
      }

      function start() {
        cancelAnimationFrame(state.raf);
        resetProgress();
        state.raf = requestAnimationFrame(tick);
      }

      function stop() {
        cancelAnimationFrame(state.raf);
        state.raf = null;
      }

      function togglePause(force) {
        var newPause = typeof force === 'boolean' ? force : !state.paused;
        state.paused = newPause;
        rotEl.setAttribute('aria-paused', state.paused ? 'true' : 'false');
      }

      paint();
      start();

      rotEl.addEventListener('mouseenter', function () { togglePause(true); });
      rotEl.addEventListener('mouseleave', function () { togglePause(false); });
      rotEl.addEventListener('focusin', function () { togglePause(true); });
      rotEl.addEventListener('focusout', function (e) { if (!rotEl.contains(e.relatedTarget)) togglePause(false); });

      rotEl.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-hero-action]');
        if (!btn) return;
        e.preventDefault();
        var act = btn.getAttribute('data-hero-action');
        if (act === 'next') go(1);
        else if (act === 'prev') go(-1);
      });

      rotEl.addEventListener('keydown', function (e) {
        switch (e.key) {
          case 'ArrowRight':
            e.preventDefault(); go(1); break;
          case 'ArrowLeft':
            e.preventDefault(); go(-1); break;
          case ' ':
          case 'Spacebar':
            e.preventDefault(); togglePause(); break;
          case 'Home':
            e.preventDefault();
            if (state.transitioning) break;
            gridEl.classList.add('is-changing-out');
            setTimeout(function () {
              state.visible = items.slice();
              paint();
              gridEl.classList.remove('is-changing-out');
              gridEl.classList.add('is-changing-in');
              setTimeout(function () { gridEl.classList.remove('is-changing-in'); state.transitioning = false; resetProgress(); }, 320);
            }, 260);
            state.transitioning = true;
            break;
          case 'End':
            e.preventDefault();
            if (state.transitioning || items.length === 0) break;
            gridEl.classList.add('is-changing-out');
            setTimeout(function () {
              var v = items.slice();
              var last = v.pop();
              v.unshift(last);
              state.visible = v;
              paint();
              gridEl.classList.remove('is-changing-out');
              gridEl.classList.add('is-changing-in');
              setTimeout(function () { gridEl.classList.remove('is-changing-in'); state.transitioning = false; resetProgress(); }, 320);
            }, 260);
            state.transitioning = true;
            break;
        }
      });

      if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        stop();
        state.progress = 100;
        if (progressEl) progressEl.style.backgroundSize = '100% 100%';
      }

      document.addEventListener('visibilitychange', function () {
        if (document.hidden) stop(); else start();
      });
    })();

  });
})();
