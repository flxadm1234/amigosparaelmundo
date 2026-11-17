<?php
$siteName = app_setting('site_name', 'ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO');
$footerAbout = app_setting('footer_about', 'Una asociación comprometida con las personas, la educación y el cuidado de nuestro planeta.');
$siteLogoFooter = app_setting('site_logo_footer', '');
$siteLogoUrl = (!empty($siteLogoFooter) && (str_starts_with($siteLogoFooter, '/') || preg_match('#^https?://#i', $siteLogoFooter)))
    ? $siteLogoFooter
    : (!empty($siteLogoFooter) ? upload_url($siteLogoFooter) : '');
if (empty($siteLogoUrl)) {
    $siteLogo = app_setting('site_logo', '');
    $siteLogoUrl = (!empty($siteLogo) && (str_starts_with($siteLogo, '/') || preg_match('#^https?://#i', $siteLogo)))
        ? $siteLogo
        : (!empty($siteLogo) ? upload_url($siteLogo) : '');
}

$footerSections = [];
try {
    if (method_exists(\App\Models\Section::class, 'allFeatured')) {
        $footerSections = \App\Models\Section::allFeatured() ?: [];
    } elseif (class_exists(\App\Models\Section::class)) {
        $footerSections = \App\Models\Section::where([['is_featured', '=', 1]], 'sort_order', 'ASC', 6) ?: [];
    }
} catch (\Throwable) { $footerSections = []; }
$footerSections = array_values(array_slice(array_filter((array)$footerSections, static fn($s) => !empty($s['slug'] ?? '')), 0, 6));

$socials = [
    ['icon' => 'facebook',  'label' => 'Facebook',  'url' => trim((string)app_setting('social_facebook',  '')) ?: '#'],
    ['icon' => 'instagram', 'label' => 'Instagram', 'url' => trim((string)app_setting('social_instagram', '')) ?: '#'],
    ['icon' => 'x',         'label' => 'X',         'url' => trim((string)app_setting('social_twitter',   '')) ?: '#'],
    ['icon' => 'youtube',   'label' => 'YouTube',   'url' => trim((string)app_setting('social_youtube',   '')) ?: '#'],
    ['icon' => 'tiktok',    'label' => 'TikTok',    'url' => trim((string)app_setting('social_tiktok',    '')) ?: '#'],
    ['icon' => 'whatsapp',  'label' => 'WhatsApp',  'url' => trim((string)app_setting('social_whatsapp',  '')) ?: '#'],
];

$socialIcons = [
    'facebook'  => '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.25-1.5 1.55-1.5h1.4V4.36C14.62 4.2 13.85 4.1 12.97 4.1c-2.47 0-4.17 1.5-4.17 4.28v2.12H6.4v3h2.4V21h4.7z"/></svg>',
    'instagram' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>',
    'x'         => '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M18.244 2H21.5l-7.5 8.57L22.5 22h-6.93l-5.42-7.09L3.84 22H.58l8.03-9.18L1.5 2h7.1l4.9 6.48L18.24 2zm-1.2 18h1.92L7.03 4H4.98L17.04 20z"/></svg>',
    'youtube'   => '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M23.5 6.2a3 3 0 00-2.11-2.12C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.39.48A3 3 0 00.5 6.2 31.3 31.3 0 000 12a31.3 31.3 0 00.5 5.8 3 3 0 002.11 2.12C4.5 20.4 12 20.4 12 20.4s7.5 0 9.39-.48a3 3 0 002.11-2.12A31.3 31.3 0 0024 12a31.3 31.3 0 00-.5-5.8zM9.75 15.5v-7L15.82 12l-6.07 3.5z"/></svg>',
    'tiktok'    => '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M19.6 5.8a6.1 6.1 0 01-3.6-1.2 6.1 6.1 0 01-2.2-3.1h-3.7v14.5a3.5 3.5 0 11-3.5-3.5c.5 0 1 .1 1.4.4V9a7 7 0 00-1.4-.1 7 7 0 107 7v-7.2c1.1.7 2.3 1.1 3.6 1.1V6.5h-.1z"/></svg>',
    'whatsapp'  => '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M20.5 3.5A11 11 0 003.2 17L2 22l5.1-1.3a11 11 0 005.2 1.3h.1A11 11 0 0020.5 3.5zM12.3 19.5a9.3 9.3 0 01-4.8-1.4l-.3-.2-3 .8.8-2.9-.2-.3A9.3 9.3 0 1121 12.1a9.2 9.2 0 01-8.7 7.4zm5-6.7c-.3-.1-1.7-.8-2-1s-.5-.3-.7.2-.7 1-.9 1.2c-.2.2-.4.2-.7.1a9 9 0 01-4.5-3.9c-.4-.7.4-.6 1.1-1.9a.8.8 0 00-.1-.7c-.1-.3-.7-1.7-1-2.3s-.6-.5-.7-.5h-.6a1.2 1.2 0 00-.9.4 3.4 3.4 0 00-1.1 2.6 6 6 0 001.2 3.2 14.1 14.1 0 005.6 5 8.4 8.4 0 004.8 1.2c1.5 0 2.8-.6 3.2-1.2.4-.7.4-1.2.3-1.4-.1-.3-1.2-.6-2.5-1.2z"/></svg>',
];
?>
<footer class="rm-footer" role="contentinfo">
  <div class="rm-footer-wrap">
    <div class="container">
      <div class="rm-footer-grid">

        <?php /* =========================================================
                 COLUMNA 1 — LOGO DEL SITIO (ADMINISTRABLE) A UN COSTADO
           ========================================================= */ ?>
        <div class="rm-footer-col rm-footer-brand">
          <a href="<?php echo e(url('/')) ?>" class="rm-footer-logo" aria-label="<?php echo e($siteName) ?>">
            <?php if ($siteLogoUrl !== ''): ?>
              <img
                src="<?php echo e($siteLogoUrl) ?>"
                alt="Logotipo de <?php echo e($siteName) ?>"
                loading="lazy"
                decoding="async">
            <?php else: ?>
              <span class="rm-footer-brand-text"><?php echo e($siteName) ?></span>
            <?php endif; ?>
          </a>
          <p class="rm-footer-about"><?php echo e($footerAbout) ?></p>
          <?php if (!empty($socials)): ?>
            <div class="rm-socials" aria-label="Redes sociales de <?php echo e($siteName) ?>">
              <?php foreach ($socials as $s):
                  $socialUrl = (string)$s['url'];
                  $isReal = $socialUrl !== '' && $socialUrl !== '#';
                  $href = $isReal ? $socialUrl : '#';
                  $relAttr = $isReal ? 'noopener nofollow sponsored' : '';
                  $targetAttr = $isReal ? '_blank' : '';
              ?>
                <a href="<?php echo e($href) ?>"
                   <?php if ($targetAttr !== ''): ?>target="<?php echo e($targetAttr) ?>"<?php endif; ?>
                   <?php if ($relAttr !== ''): ?>rel="<?php echo e($relAttr) ?>"<?php endif; ?>
                   class="rm-social-btn"
                   aria-label="<?php echo e($s['label']) ?>"
                   title="<?php echo e($s['label']) ?>">
                  <?php echo $socialIcons[$s['icon']] ?? '' ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <?php /* =========================================================
                 COLUMNA 2 — Secciones (de base de datos o defaults)
           ========================================================= */ ?>
        <div class="rm-footer-col rm-footer-links">
          <h4 class="rm-footer-title">Secciones</h4>
          <ul>
            <?php if (!empty($footerSections)): foreach ($footerSections as $s): ?>
              <?php $sSlug = (string)($s['slug'] ?? ''); if ($sSlug === '') continue; ?>
              <li><a href="<?php echo e(section_url(null, $sSlug)) ?>"><?php echo e($s['name'] ?? $sSlug) ?></a></li>
            <?php endforeach; else: ?>
              <li><a href="<?php echo e(section_url(null, 'ultimas-noticias')) ?>">Últimas noticias</a></li>
              <li><a href="<?php echo e(section_url(null, 'deportes')) ?>">Deportes</a></li>
              <li><a href="<?php echo e(section_url(null, 'politica')) ?>">Política</a></li>
              <li><a href="<?php echo e(section_url(null, 'economia')) ?>">Economía</a></li>
              <li><a href="<?php echo e(section_url(null, 'policiales')) ?>">Policiales</a></li>
              <li><a href="<?php echo e(url('/noticias')) ?>">Todas las noticias</a></li>
            <?php endif; ?>
          </ul>
        </div>

        <?php /* =========================================================
                 COLUMNA 3 — Multimedia (Videos, Podcast, Radio, TV)
           ========================================================= */ ?>
        <div class="rm-footer-col rm-footer-links">
          <h4 class="rm-footer-title">Multimedia</h4>
          <ul>
            <li><a href="<?php echo e(url('/videos')) ?>">Videos</a></li>
            <li><a href="<?php echo e(url('/podcast')) ?>">Podcast</a></li>
            <li><a href="<?php echo e(url('/radio')) ?>">Programas de radio</a></li>
            <li><a href="<?php echo e(url('/tv')) ?>">Televisión en vivo</a></li>
            <li><a href="<?php echo e(url('/en-vivo')) ?>">Señal en vivo</a></li>
          </ul>
        </div>

        <?php /* =========================================================
                 COLUMNA 4 — Contacto
           ========================================================= */ ?>
        <div class="rm-footer-col rm-footer-links">
          <h4 class="rm-footer-title">Contacto</h4>
          <ul>
            <li>📞 <?php echo e(app_setting('contact_phone',   '(01) 000-0000')) ?></li>
            <li>✉️ <?php echo e(app_setting('contact_email',   'contacto@amigosporelmundo.org')) ?></li>
            <li>📍 <?php echo e(app_setting('contact_address', 'Piura, Perú')) ?></li>
            <li>🕘 <?php echo e(app_setting('contact_hours',   'Lun - Sáb · 08:00 - 20:00')) ?></li>
            <li><a href="<?php echo e(url('/pagina/contacto')) ?>">Escríbenos</a></li>
          </ul>
        </div>

      </div>
    </div>

    <?php /* =========================================================
             BARRA INFERIOR — Copyright + Hecho en Perú 🇵🇪
       ========================================================= */ ?>
    <div class="rm-footer-bottom">
      <div class="container">
        <div class="rm-footer-bottom-inner">
          <span>© <?php echo (int)date('Y') ?> <?php echo e($siteName) ?>. Todos los derechos reservados.</span>
          <span>Hecho en Perú 🇵🇪</span>
        </div>
      </div>
    </div>
  </div>
</footer>

<style>
/* ============================================================
   AMIGOS POR EL MUNDO · Footer · LOGO A UN COSTADO (IZQUIERDA)
   Paleta navy #0A2540 + verde #00A651 (institucional)
   ============================================================ */
.rm-footer {
  background: #0A2540;
  color: #E2E8F0;
  border-top: 4px solid #00A651;
}
.rm-footer-wrap { padding: 48px 0 0; }

.rm-footer-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr);
  gap: 2.2rem 1.8rem;
  align-items: flex-start;
}

.rm-footer-col h4.rm-footer-title {
  font-size: .92rem;
  font-weight: 800;
  color: #FFFFFF;
  letter-spacing: .04em;
  text-transform: uppercase;
  margin: 0 0 1rem 0;
  padding-bottom: .5rem;
  border-bottom: 2px solid #00A651;
  display: inline-block;
}
.rm-footer-col ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: .55rem;
}
.rm-footer-col ul li {
  font-size: .9rem;
  line-height: 1.45;
  color: #CBD5E1;
}
.rm-footer-col ul li a {
  color: #CBD5E1;
  text-decoration: none;
  transition: color .15s ease, transform .15s ease;
  display: inline-block;
}
.rm-footer-col ul li a:hover {
  color: #00A651;
  transform: translateX(2px);
}

/* LOGO (columna IZQUIERDA — costado izq, NO centrado) */
.rm-footer-brand .rm-footer-logo {
  display: inline-block;
  margin: 0 0 1rem 0;
  text-align: left;
}
.rm-footer-brand-text {
  display: block;
  max-width: 26ch;
  color: #FFFFFF;
  font-size: 1.05rem;
  font-weight: 800;
  letter-spacing: .02em;
  line-height: 1.25;
}
.rm-footer-brand .rm-footer-about {
  color: #94A3B8;
  font-size: .9rem;
  line-height: 1.6;
  margin: 0 0 1.2rem 0;
  max-width: 42ch;
  text-align: left;
}

/* Redes sociales */
.rm-socials {
  display: flex;
  flex-wrap: wrap;
  gap: .55rem;
}
.rm-social-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 9999px;
  background: rgba(255,255,255,.06);
  color: #F1F5F9;
  border: 1px solid rgba(255,255,255,.08);
  text-decoration: none;
  transition: background .15s ease, color .15s ease, transform .15s ease, border-color .15s ease;
}
.rm-social-btn:hover {
  background: #00A651;
  color: #fff;
  border-color: rgba(0,166,81,.55);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,166,81,.25);
}

/* Barra inferior */
.rm-footer-bottom {
  margin-top: 44px;
  padding: 18px 0 22px;
  background: #071A30;
  border-top: 1px solid rgba(255,255,255,.08);
}
.rm-footer-bottom-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
  color: #94A3B8;
  font-size: .85rem;
}

/* Responsive */
@media (max-width: 992px) {
  .rm-footer-grid {
    grid-template-columns: minmax(0,1fr) minmax(0,1fr);
  }
}
@media (max-width: 576px) {
  .rm-footer-wrap { padding-top: 32px; }
  .rm-footer-grid { grid-template-columns: minmax(0,1fr); gap: 1.8rem; }
  .rm-footer-bottom-inner { justify-content: center; text-align: center; }
  .rm-footer-brand .rm-footer-about { text-align: left; }
}
</style>

<script>
/* RENOVACION UI 2026-09-25 · Task6 · IntersectionObserver fade-in on scroll
   Threshold 0.08 · unobserve después de primera intersección (no-repeat)
   Compatible navegadores modernos + fallback inmediato si no soportado */
(function () {
  'use strict';
  var cls = 'rm-observe-fade';
  var visibleCls = 'rm-is-visible';
  var nodes = document.querySelectorAll('.' + cls);
  if (!nodes.length) return;
  if (!('IntersectionObserver' in window)) {
    for (var i = 0; i < nodes.length; i++) nodes[i].classList.add(visibleCls);
    return;
  }
  var io = new IntersectionObserver(function (entries, obs) {
    for (var e = 0; e < entries.length; e++) {
      var entry = entries[e];
      if (entry.isIntersecting) {
        entry.target.classList.add(visibleCls);
        obs.unobserve(entry.target);
      }
    }
  }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
  for (var j = 0; j < nodes.length; j++) io.observe(nodes[j]);
})();
</script>

</body>
</html>
