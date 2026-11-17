<?php include __DIR__ . '/../partials/public_head.php'; ?>
<?php include __DIR__ . '/../partials/public_navbar.php'; ?>

<?php
$heroNews = $heroNews ?? (
    method_exists(\App\Models\News::class, 'latestPublished')
        ? \App\Models\News::latestPublished(6)
        : (\App\Models\News::where([['is_published','=',1]], 'publish_date', 'DESC', 6) ?: [])
);

$masNoticias = $masNoticias ?? (
    method_exists(\App\Models\News::class, 'latestPublished')
        ? \App\Models\News::latestPublished(9)
        : []
);

$radioStreamUrlHome = app_setting('radio_stream_url', '');
?>

<main id="rm-main" class="rm-main" tabindex="-1">
  <?php include __DIR__ . '/../partials/flash.php'; ?>

  <?php
    $trendingHome = [];
    try {
      if (method_exists(\App\Models\News::class, 'getMostViewed')) {
        $trendingHome = \App\Models\News::getMostViewed(5);
      }
      if (empty($trendingHome)) {
        $trendingHome = method_exists(\App\Models\News::class, 'latestPublished')
          ? \App\Models\News::latestPublished(5)
          : array_values((array)$masNoticias);
      }
    } catch (\Throwable $e) { $trendingHome = array_values((array)$masNoticias); }

    $sideNewsPool = array_values(array_filter(array_merge(
      array_slice((array)$heroNews, 1, 5),
      (array)$masNoticias
    ), function($n){ return !empty($n) && is_array($n) && !empty($n['id']); }));
    $sideNewsPool = array_values(array_intersect_key(
      $sideNewsPool,
      array_unique(array_column($sideNewsPool, 'id'))
    ));
    $sideNewsFill = [];
    for ($sfi = 0; $sfi < 5 && !empty($sideNewsPool); $sfi++) {
      $sideNewsFill[] = array_shift($sideNewsPool);
    }
    while (count($sideNewsFill) < 5) { $sideNewsFill[] = null; }

    $homeLiveTvOn   = (string)app_setting('live_tv_enabled','0') === '1';
    $homeLiveTvUrl  = trim((string)app_setting('live_tv_url',''));
    $homeLiveTvTit  = trim((string)app_setting('live_tv_title','AMIGOS TV en vivo'));
    $homeLiveEmbed  = '';
    if ($homeLiveTvUrl !== '' && preg_match('#(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([A-Za-z0-9_-]{6,})#i', $homeLiveTvUrl, $yh)) {
      $homeLiveEmbed = 'https://www.youtube.com/embed/' . $yh[1] . '?rel=0&modestbranding=1&playsinline=1';
    } elseif (stripos($homeLiveTvUrl, 'http') === 0) {
      $homeLiveEmbed = $homeLiveTvUrl;
    }
  ?>

  <?php
    $_hrPoolRaw = array_values(array_filter(array_merge(
      (array)$heroNews,
      (array)$masNoticias
    ), function($n){ return !empty($n) && is_array($n) && !empty($n['id']); }));
    $_hrSeen = [];
    $_hrPool = [];
    foreach ($_hrPoolRaw as $_hrn) {
      $_id = (string)($_hrn['id'] ?? '');
      if ($_id === '' || isset($_hrSeen[$_id])) continue;
      $_hrSeen[$_id] = true;
      $_hrPool[] = $_hrn;
    }
    $_hrPool = array_values($_hrPool);
    while (count($_hrPool) < 4) {
      $_hrPool[] = null;
    }
    $_hrFeatured = $_hrPool[0] ?? null;
    $_hrThumbs   = [$_hrPool[1] ?? null, $_hrPool[2] ?? null, $_hrPool[3] ?? null];

    $_hrToItem = function($n, $idx) {
      if (empty($n)) return null;
      $img = !empty($n['cover_image_path']) ? cover_image_url($n)
           : img_placeholder('16x9', $n['title'] ?? 'Noticia', 'hr-item-'.$idx.'-'.($n['id'] ?? 'x'));
      $section = (string)($n['section_name'] ?? '');
      if ($section === '' && !empty($n['section_id'])) {
        try {
          $s = \App\Models\Section::findById($n['section_id']);
          if ($s) $section = (string)($s['name'] ?? '');
        } catch (\Throwable $e) { $section = ''; }
      }
      $date = function_exists('format_date_spanish') ? format_date_spanish($n['publish_date'] ?? '') : ($n['publish_date'] ?? '');
      $excerpt = trim(strip_tags((string)($n['excerpt'] ?? '')));
      if (mb_strlen($excerpt) > 200) $excerpt = mb_substr($excerpt, 0, 200) . '…';
      return [
        'id'            => (string)($n['id'] ?? ''),
        'title'         => (string)($n['title'] ?? ''),
        'excerpt'       => $excerpt,
        'url'           => news_url($n['slug'] ?? ''),
        'image'         => $img,
        'section'       => $section,
        'is_breaking'   => !empty($n['is_breaking']),
        'date'          => $date,
        'author'        => (string)($n['author_name'] ?? ''),
      ];
    };

    $_hrDatasetArr = [];
    foreach (array_values(array_filter($_hrPool, function($v){return !empty($v);})) as $_hri => $_hrn) {
      $it = $_hrToItem($_hrn, $_hri);
      if ($it) $_hrDatasetArr[] = $it;
    }
    $_hrDatasetJson = json_encode($_hrDatasetArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($_hrDatasetJson === false) $_hrDatasetJson = '[]';
  ?>
  <section class="container mt-4">
    <div class="rm-home-top-wrap">

      <div class="rm-home-top-left">
        <div
          class="rm-hero-rotator"
          id="rmHeroRotator"
          role="region"
          aria-roledescription="carousel"
          aria-label="Noticias destacadas rotativas"
          data-interval="8000"
          tabindex="0">

          <script type="application/json" id="heroRotatorData"><?php echo $_hrDatasetJson ?></script>

          <div class="rm-hero-rotator-grid" aria-live="polite">
            <div class="rm-hero-featured rm-hero-slot" data-slot="featured">
              <?php
                $fn = $_hrFeatured;
                if (!empty($fn)):
                  $fi = $_hrToItem($fn, 0);
              ?>
                <article class="rm-hero-card rm-hero-card--featured">
                  <a href="<?php echo e($fi['url']) ?>" aria-label="<?php echo e($fi['title']) ?>">
                    <div class="rm-hero-card-media">
                      <img src="<?php echo e($fi['image']) ?>" alt="<?php echo e($fi['title']) ?>" loading="eager" fetchpriority="high">
                      <div class="rm-hero-card-overlay" aria-hidden="true"></div>
                    </div>
                    <div class="rm-hero-card-body">
                      <div class="rm-hero-card-badges">
                        <?php if (!empty($fi['section'])): ?>
                          <span class="rm-hero-card-cat"><?php echo e($fi['section']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($fi['is_breaking'])): ?>
                          <span class="rm-hero-card-breaking">🔴 ÚLTIMA HORA</span>
                        <?php endif; ?>
                      </div>
                      <h2 class="rm-hero-card-title"><span><?php echo e($fi['title']) ?></span></h2>
                      <?php if (!empty($fi['excerpt'])): ?>
                        <p class="rm-hero-card-excerpt"><?php echo e($fi['excerpt']) ?></p>
                      <?php endif; ?>
                      <div class="rm-hero-card-foot">
                        <span>📅 <?php echo e($fi['date']) ?></span>
                        <?php if (!empty($fi['author'])): ?><span>✍️ <?php echo e($fi['author']) ?></span><?php endif; ?>
                        <span class="rm-hero-card-cta">Leer noticia →</span>
                      </div>
                    </div>
                  </a>
                </article>
              <?php else: ?>
                <div class="rm-hero-card rm-hero-card--featured rm-hero-card--empty">
                  <div class="rm-hero-card-media"><div class="rm-hero-card-empty-ph">📰</div></div>
                  <div class="rm-hero-card-body">
                    <h2 class="rm-hero-card-title"><span>Publica tu primera noticia</span></h2>
                    <p class="rm-hero-card-excerpt">Desde el panel administrativo crea una noticia publicada y aparecerá automáticamente aquí.</p>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <div class="rm-hero-thumbs" aria-label="Siguientes noticias en rotación">
              <?php foreach ($_hrThumbs as $tIdx => $tn): $ti = !empty($tn) ? $_hrToItem($tn, $tIdx+1) : null; ?>
                <div class="rm-hero-thumb rm-hero-slot" data-slot="thumb-<?php echo ($tIdx+1) ?>">
                  <?php if (!empty($ti)): ?>
                    <article class="rm-hero-card rm-hero-card--thumb">
                      <a href="<?php echo e($ti['url']) ?>" aria-label="<?php echo e($ti['title']) ?>">
                        <div class="rm-hero-card-media">
                          <img src="<?php echo e($ti['image']) ?>" alt="<?php echo e($ti['title']) ?>" loading="lazy">
                          <div class="rm-hero-card-overlay" aria-hidden="true"></div>
                        </div>
                        <div class="rm-hero-card-body">
                          <?php if (!empty($ti['section'])): ?>
                            <span class="rm-hero-card-cat rm-hero-card-cat--thumb"><?php echo e($ti['section']) ?></span>
                          <?php endif; ?>
                          <h3 class="rm-hero-card-title rm-hero-card-title--thumb"><span><?php echo e($ti['title']) ?></span></h3>
                        </div>
                      </a>
                    </article>
                  <?php else: ?>
                    <div class="rm-hero-card rm-hero-card--thumb rm-hero-card--empty">
                      <div class="rm-hero-card-media"><div class="rm-hero-card-empty-ph">—</div></div>
                      <div class="rm-hero-card-body"><span class="rm-hero-card-cat rm-hero-card-cat--thumb">Esperando noticia</span></div>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="rm-hero-rotator-bar" aria-hidden="true">
            <button type="button" class="rm-hero-rotator-btn" data-hero-action="prev" aria-label="Noticia anterior">‹</button>
            <div class="rm-hero-rotator-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
            <button type="button" class="rm-hero-rotator-btn" data-hero-action="next" aria-label="Siguiente noticia">›</button>
          </div>
        </div>
      </div>

      <aside class="rm-home-top-right" aria-label="Contenido destacado multimedia y trending">

        <div class="rm-top-widget rm-top-widget-tv">
          <div class="rm-top-widget-head">
            <div class="rm-top-widget-title">
              <span class="rm-live-dot" aria-hidden="true"></span>
              AMIGOS TV EN VIVO
            </div>
            <a href="/en-vivo" class="rm-top-widget-more">VER ↗</a>
          </div>
          <?php if ($homeLiveTvOn && $homeLiveEmbed !== ''): ?>
            <div class="rm-top-widget-tv-frame">
              <iframe src="<?php echo e($homeLiveEmbed) ?>" title="<?php echo e($homeLiveTvTit) ?>"
                      allow="autoplay; encrypted-media; picture-in-picture; gyroscope" allowfullscreen></iframe>
            </div>
          <?php else: ?>
            <div class="rm-top-widget-tv-frame rm-tv-off">
              <div class="rm-tv-off-emoji">📺</div>
              <div class="rm-tv-off-title">Señal lista</div>
              <a href="/en-vivo" class="rm-top-widget-btn-primary">Ver transmisión →</a>
            </div>
          <?php endif; ?>
          <div class="rm-top-widget-links">
            <a href="/tv" class="rm-top-link rm-top-link-tv">📺 TV</a>
            <a href="/radio" class="rm-top-link rm-top-link-radio">📻 Radio</a>
            <a href="/videos" class="rm-top-link rm-top-link-video">🎥 Videos</a>
          </div>
        </div>

        <?php if (!empty($trendingHome)): ?>
          <div class="rm-top-widget">
            <div class="rm-top-widget-head">
              <div class="rm-top-widget-title">📊 LO MÁS LEÍDO</div>
              <a href="<?php echo url('/noticias') ?>" class="rm-top-widget-more">Todas →</a>
            </div>
            <ol class="rm-top-trending">
              <?php foreach (array_slice((array)$trendingHome, 0, 4) as $thIdx => $tn): ?>
                <li>
                  <span class="rm-trending-index" aria-hidden="true"><?php echo ($thIdx+1) ?></span>
                  <a href="<?php echo news_url($tn['slug'] ?? '') ?>">
                    <span class="rm-trending-tit"><?php echo e(mb_substr($tn['title'] ?? '', 0, 82)) ?><?php echo mb_strlen($tn['title'] ?? '') > 82 ? '…' : '' ?></span>
                    <?php if (!empty($tn['views_count'])): ?>
                      <span class="rm-trending-meta">👁 <?php echo e($tn['views_count']) ?></span>
                    <?php endif; ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ol>
          </div>
        <?php endif; ?>

      </aside>

    </div>
  </section>

  <?php
    $flyersHome = [];
    try {
      if (method_exists(\App\Models\Flyer::class, 'getActiveByPosition')) {
        $flyersHome = \App\Models\Flyer::getActiveByPosition('home-carousel', 5);
      } elseif (class_exists(\App\Models\Flyer::class)) {
        $flyersHome = \App\Models\Flyer::where([['is_active','=',1]], 'sort_order', 'ASC', 5) ?: [];
      }
    } catch (\Throwable $e) { $flyersHome = []; }
  ?>

  <?php if (!empty($flyersHome) || function_exists('render_flyers_carousel')): ?>
  <section class="container">
    <div class="rm-section-title">
      <h2>📢 Destacados AMIGOS POR EL MUNDO</h2>
    </div>
    <?php
      if (function_exists('render_flyers_carousel')) {
        render_flyers_carousel('home-carousel', 5);
      }
      if (!empty($flyersHome) && !function_exists('render_flyers_carousel')):
    ?>
      <div class="flyers-carousel" id="rmFlyersCarousel" aria-roledescription="carousel">
        <div class="flyers-track-wrap">
          <div class="flyers-track" id="rmFlyersTrack">
            <?php foreach ($flyersHome as $idx => $flyer):
                $fImg = !empty($flyer['image_path']) ? upload_url($flyer['image_path']) : img_placeholder('21x9', $flyer['title'] ?? 'Banner AMIGOS POR EL MUNDO', 'flyer-home-'.$idx);
            ?>
              <div class="flyer-slide" role="group" aria-roledescription="slide" aria-label="Diapositiva <?php echo ($idx+1) ?> de <?php echo count($flyersHome) ?>">
                <?php if (!empty($flyer['link_url'])): ?>
                  <a href="<?php echo e($flyer['link_url']) ?>" target="<?php echo !empty($flyer['link_external']) ? '_blank' : '_self' ?>" rel="noopener">
                <?php endif; ?>
                  <img src="<?php echo e($fImg) ?>" alt="<?php echo e($flyer['title'] ?? 'Publicidad') ?>" class="flyer-img" loading="lazy">
                  <div class="flyer-caption" aria-hidden="true">
                    <div class="flyer-caption-inner">
                      <?php if (!empty($flyer['title'])): ?>
                        <h3 class="flyer-title"><?php echo e($flyer['title']) ?></h3>
                      <?php endif; ?>
                      <?php if (!empty($flyer['subtitle'])): ?>
                        <p class="flyer-subtitle"><?php echo e($flyer['subtitle']) ?></p>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php if (!empty($flyer['link_url'])): ?>
                  </a>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php if (count($flyersHome) > 1): ?>
          <button type="button" class="flyer-nav flyer-prev" data-flyer-dir="prev" aria-label="Diapositiva anterior">‹</button>
          <button type="button" class="flyer-nav flyer-next" data-flyer-dir="next" aria-label="Diapositiva siguiente">›</button>
          <div class="flyer-dots" role="tablist" aria-label="Selector de diapositivas">
            <?php foreach ($flyersHome as $idx => $flyer): ?>
              <span data-flyer-dot="<?php echo $idx ?>" role="tab" aria-label="Ir a diapositiva <?php echo ($idx+1) ?>" class="<?php echo $idx===0 ? 'active' : '' ?>"></span>
            <?php endforeach; ?>
          </div>
          <script>
          (function(){
            var track = document.getElementById('rmFlyersTrack');
            var dots = document.querySelectorAll('[data-flyer-dot]');
            if (!track || !dots.length) return;
            var idx = 0, total = dots.length, timer = null;
            function go(n){
              idx = (n + total) % total;
              track.style.transform = 'translateX(-'+(idx*100)+'%)';
              dots.forEach(function(d,i){ d.classList.toggle('active', i===idx); });
            }
            document.querySelectorAll('[data-flyer-dir]').forEach(function(b){
              b.addEventListener('click', function(){
                go(idx + (b.getAttribute('data-flyer-dir') === 'next' ? 1 : -1));
                resetTimer();
              });
            });
            dots.forEach(function(d,i){
              d.addEventListener('click', function(){ go(i); resetTimer(); });
            });
            function resetTimer(){
              if (timer) clearInterval(timer);
              timer = setInterval(function(){ go(idx+1); }, 6000);
            }
            resetTimer();
          })();
          </script>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  
  <?php
    $featuredSecsHome = method_exists(\App\Models\Section::class, 'allFeatured') ? \App\Models\Section::allFeatured() : [];
    $homeSecs = array_values(array_slice(array_values(array_filter((array)$featuredSecsHome, function($s){
      $sl = (string)($s['slug'] ?? ''); return $sl !== '' && $sl !== 'ultimas-noticias';
    })), 0, 3));

    $liveTvEnabledH = (string)app_setting('live_tv_enabled','0') === '1';
    $liveTvUrlH     = trim((string)app_setting('live_tv_url',''));
    $liveTvTitleH   = trim((string)app_setting('live_tv_title','AMIGOS TV en vivo'));
    $liveEmbedH = '';
    if ($liveTvUrlH !== '' && preg_match('#(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([A-Za-z0-9_-]{6,})#i', $liveTvUrlH, $ym)) {
      $liveEmbedH = 'https://www.youtube.com/embed/' . $ym[1] . '?rel=0&modestbranding=1&playsinline=1';
    } elseif (stripos($liveTvUrlH, '.m3u8') !== false) {
      $liveEmbedH = '';
    } else {
      $liveEmbedH = $liveTvUrlH;
    }
  ?>
  <section class="container mt-4 mb-5">
    <div style="display:grid;grid-template-columns:minmax(0,2.1fr) minmax(0,1fr);gap:1.5rem;align-items:start;">
      <div class="rm-home-density">
        <h3 class="h4 mb-3 fw-800" style="letter-spacing:-.01em; color:#0F172A;">Secciones destacadas</h3>
        <?php if (empty($homeSecs)): ?>
          <div class="row" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem;">
            <?php for($ph=0;$ph<3;$ph++): ?>
              <div class="rm-widget card p-3" style="border-radius:16px;background:#fff;border:1px solid rgba(15,23,42,.08);box-shadow:0 2px 8px rgba(15,23,42,.04);">
                <div style="height:140px;border-radius:12px;background:linear-gradient(135deg,#0052A5 0%,#0A2540 100%);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;">
                  Bloque de sección
                </div>
                <p class="mt-2 text-sm" style="color:#64748B;">Configura secciones en Panel &gt; Secciones marcando "Mostrar en menú principal" para que aparezcan aquí.</p>
              </div>
            <?php endfor; ?>
          </div>
        <?php else: ?>
          <div class="rm-home-secs-grid">
          <?php foreach ($homeSecs as $hs): ?>
            <?php
              $hsSlug = (string)($hs['slug'] ?? '');
              $hsName = (string)($hs['name'] ?? $hsSlug);
              $hsNews = method_exists(\App\Models\News::class,'latestPublishedBySectionSlug')
                ? \App\Models\News::latestPublishedBySectionSlug($hsSlug, 2)
                : \App\Models\News::where(
                    [
                      ['is_published','=', 1],
                      ['section_id','=', (int)($hs['id'] ?? 0)]
                    ],
                    'publish_date',
                    'DESC',
                    2
                  );
              $hsNews = array_values((array)$hsNews);
              if (count($hsNews) < 2) {
                $fallback = method_exists(\App\Models\News::class, 'latestPublished')
                  ? array_values((array)\App\Models\News::latestPublished(3))
                  : array_values((array)\App\Models\News::where([['is_published','=',1]], 'publish_date', 'DESC', 3));
                while (count($hsNews) < 2 && !empty($fallback)) { $hsNews[] = array_shift($fallback); }
              }
            ?>
            <div class="rm-home-sec-card" style="background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:16px;overflow:hidden;box-shadow:0 2px 10px rgba(15,23,42,.04);display:flex;flex-direction:column;">
              <div style="padding:.85rem 1rem;background:linear-gradient(90deg,#0052A5 0%,#0A2540 100%);color:#fff;display:flex;justify-content:space-between;align-items:center;">
                <a href="<?php echo section_url($hs) ?>" style="color:#fff;text-decoration:none;font-weight:800;letter-spacing:-.01em;"><?php echo e($hsName) ?></a>
                <a href="<?php echo section_url($hs) ?>" style="color:#fff;text-decoration:none;opacity:.9;font-weight:600;font-size:.82rem;">Ver más →</a>
              </div>
              <ul style="list-style:none;margin:0;padding:.85rem 1rem 1rem;display:flex;flex-direction:column;gap:.85rem;">
                <?php foreach ($hsNews as $hi => $hn): ?>
                  <?php
                    $hnImg = !empty($hn['cover_image_path']) ? cover_image_url($hn) : img_placeholder('16x9', $hn['title'] ?? 'Noticia', 'home-sec-'.$hsSlug.'-'.$hi);
                  ?>
                  <li style="display:grid;grid-template-columns:92px 1fr;gap:.75rem;align-items:start;">
                    <a href="<?php echo news_url($hn['slug'] ?? '') ?>" aria-label="<?php echo e($hn['title'] ?? '') ?>">
                      <img src="<?php echo e($hnImg) ?>" alt="<?php echo e($hn['title'] ?? 'Noticia') ?>" loading="lazy" style="width:92px;height:64px;object-fit:cover;border-radius:10px;display:block;">
                    </a>
                    <div style="min-width:0;">
                      <a href="<?php echo news_url($hn['slug'] ?? '') ?>" style="color:#0F172A;text-decoration:none;font-weight:700;font-size:.88rem;line-height:1.35;">
                        <?php echo e(mb_substr($hn['title'] ?? '', 0, 90)) ?><?php echo mb_strlen($hn['title'] ?? '') > 90 ? '…' : '' ?>
                      </a>
                      <div style="color:#94A3B8;font-size:.72rem;margin-top:.25rem;">
                        <?php echo e(function_exists('format_date_spanish') ? format_date_spanish($hn['publish_date'] ?? '') : ($hn['publish_date'] ?? '')) ?>
                      </div>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <aside class="rm-home-sidebar-tv" aria-label="AMIGOS TV y Multimedia">
        <div style="background:linear-gradient(180deg,#0B1220 0%,#0A2540 100%);color:#fff;border-radius:18px;overflow:hidden;box-shadow:0 24px 60px -20px rgba(15,23,42,.55);border:1px solid rgba(148,163,184,.12);">
          <div style="padding:.8rem 1rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(148,163,184,.12);">
            <div style="display:inline-flex;align-items:center;gap:.5rem;font-weight:800;letter-spacing:-.01em;">
              <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:9999px;background:#E11D48;color:#fff;font-size:.7rem;box-shadow:0 0 0 4px rgba(225,29,72,.15);">●</span>
              AMIGOS TV EN VIVO
            </div>
            <a href="/en-vivo" style="background:#fff;color:#0F172A;padding:.35rem .7rem;border-radius:9999px;text-decoration:none;font-weight:700;font-size:.8rem;display:inline-flex;align-items:center;gap:.3rem;">
              VER <span aria-hidden="true">↗</span>
            </a>
          </div>
          <?php if ($liveTvEnabledH && $liveEmbedH !== ''): ?>
            <?php if (stripos($liveEmbedH, 'youtube.com/embed/') === 0 || stripos($liveEmbedH, 'https://www.youtube.com/embed/') === 0 || stripos($liveEmbedH, 'http') === 0): ?>
              <div style="aspect-ratio:16/9;background:#000;">
                <iframe src="<?php echo e($liveEmbedH) ?>" title="<?php echo e($liveTvTitleH) ?>" style="width:100%;height:100%;border:0;display:block;background:#000;" loading="lazy" allow="autoplay; encrypted-media; picture-in-picture; gyroscope" allowfullscreen></iframe>
              </div>
            <?php else: ?>
              <div style="aspect-ratio:16/9;background:#000;display:flex;align-items:center;justify-content:center;color:#94A3B8;">
                Transmisión lista
              </div>
            <?php endif; ?>
          <?php else: ?>
            <div style="aspect-ratio:16/9;background:#0B1220;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#94A3B8;gap:.5rem;padding:1.5rem;text-align:center;">
              <div style="font-size:2.4rem;">📺</div>
              <div style="font-weight:700;color:#CBD5E1;">TV en vivo no habilitada</div>
              <div style="font-size:.82rem;color:#64748B;">Activa y configura la URL en <span style="color:#fff;">Ajustes &gt; AMIGOS TV en vivo</span>.</div>
            </div>
          <?php endif; ?>
          <div style="padding:.85rem 1rem 1rem;display:flex;flex-wrap:wrap;gap:.5rem;">
            <a href="/en-vivo" style="flex:1 1 auto;min-width:0;background:#2563EB;color:#fff;padding:.55rem .8rem;border-radius:9999px;text-decoration:none;font-weight:700;font-size:.85rem;text-align:center;">📺 Ver AMIGOS TV →</a>
            <a href="/radio" style="flex:1 1 auto;min-width:0;background:rgba(148,163,184,.12);color:#fff;padding:.55rem .8rem;border-radius:9999px;text-decoration:none;font-weight:700;font-size:.85rem;text-align:center;border:1px solid rgba(148,163,184,.18);">📻 Radio en vivo →</a>
          </div>
        </div>
      </aside>
    </div>
  </section>
<section class="container mb-6">
    <div class="row row-main-sidebar">
      <div>
        <div class="rm-section-title d-flex justify-content-between align-items-center flex-wrap gap-3">
          <h2>📰 Más Noticias</h2>
          <a href="<?php echo url('/noticias') ?>" class="rm-btn rm-btn-ghost rm-btn-sm">Ver todas →</a>
        </div>
        <div class="rm-news-grid">
          <?php
            if (!empty($masNoticias) && function_exists('news_card')) {
              foreach ((array)$masNoticias as $n) {
                echo news_card($n, 'md');
              }
            } else {
              foreach (array_values((array)$masNoticias) as $ncIdx => $n) {
                $ncImg = !empty($n['cover_image_path'])
                    ? cover_image_url($n)
                    : img_placeholder('16x9', $n['title'] ?? 'Noticia', 'news-grid-'.($n['id'] ?? ('idx-'.$ncIdx)));
                $ncSection = $n['section_name'] ?? '';
                if (empty($ncSection) && !empty($n['section_id'])) {
                  $sec = \App\Models\Section::findById($n['section_id']);
                  if ($sec) $ncSection = $sec['name'] ?? '';
                }
                $ncSectionSlug = $n['section_slug'] ?? '';
                if (empty($ncSectionSlug) && !empty($n['section_id'])) {
                  $secSlug = \App\Models\Section::findById($n['section_id']);
                  if ($secSlug) $ncSectionSlug = $secSlug['slug'] ?? '';
                }
                $ncTitle = e($n['title'] ?? '');
                $ncLink  = news_url($n['slug'] ?? '');
          ?>
            <article class="news-card news-card--md">
              <div class="news-card-media">
                <a href="<?php echo $ncLink ?>" class="news-card-media-link" aria-label="<?php echo $ncTitle ?>">
                  <img src="<?php echo e($ncImg) ?>" alt="<?php echo $ncTitle ?>" class="news-card-img" loading="lazy" decoding="async">
                </a>
                <div class="news-card-badges" aria-label="Etiquetas de la noticia">
                  <?php if (!empty($n['is_breaking'])): ?>
                    <span class="news-card-badge news-card-badge-breaking">ÚLTIMA HORA</span>
                  <?php endif; ?>
                </div>
                <div class="news-card-media-footer" aria-hidden="true">
                  <?php if (!empty($ncSection)):
                    $ncCatHref = $ncSectionSlug ? e(section_url(null, $ncSectionSlug)) : 'javascript:void(0)';
                  ?>
                    <a href="<?php echo $ncCatHref ?>" class="news-card-badge news-card-badge-category" aria-label="Sección <?php echo e($ncSection) ?>"><?php echo e($ncSection) ?></a>
                  <?php endif; ?>
                </div>
              </div>
              <div class="news-card-body">
                <h3 class="news-card-title"><a href="<?php echo $ncLink ?>"><?php echo $ncTitle ?></a></h3>
                <?php if (!empty($n['excerpt'])): ?>
                  <p class="news-card-excerpt">
                    <?php echo e(mb_substr(strip_tags($n['excerpt']), 0, 180)) ?><?php echo mb_strlen(strip_tags($n['excerpt'] ?? '')) > 180 ? '…' : '' ?>
                  </p>
                <?php endif; ?>
                <div class="news-card-meta">
                  <span class="news-card-date">📅 <?php echo e(function_exists('format_date_spanish') ? format_date_spanish($n['publish_date'] ?? '') : ($n['publish_date'] ?? '')) ?></span>
                  <?php if (!empty($n['views_count'])): ?>
                    <span class="news-card-views">👁 <?php echo e($n['views_count']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php
              }
            }
          ?>
        </div>
        <?php if (!empty($paginator) && is_object($paginator) && method_exists($paginator, 'render')): ?>
          <?php echo $paginator->render(); ?>
        <?php elseif (is_array($paginator ?? null) && !empty($paginator)): ?>
          <nav class="pagination" aria-label="Paginación">
            <ul>
              <?php foreach ($paginator as $page): ?>
                <li class="page-item <?php echo !empty($page['active']) ? 'active' : '' ?> <?php echo !empty($page['disabled']) ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?php echo e($page['url'] ?? '#') ?>" <?php if (!empty($page['disabled'])): ?>aria-disabled="true"<?php endif; ?>>
                    <?php echo e($page['label'] ?? '') ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </nav>
        <?php endif; ?>
      </div>

      <aside class="rm-sidebar" role="complementary" aria-label="Barra lateral">
                <div class="rm-widget rm-widget-tv" style="background:linear-gradient(180deg,#0B1220 0%,#0A2540 100%);color:#fff;border-radius:16px;padding:0;overflow:hidden;margin-bottom:1.1rem;box-shadow:0 18px 40px -20px rgba(15,23,42,.55);">
          <?php
            $liveTvEnabledSb = (string)app_setting('live_tv_enabled','0') === '1';
            $liveTvTitleSb   = trim((string)app_setting('live_tv_title','AMIGOS TV en vivo'));
            $liveTvUrlSb     = trim((string)app_setting('live_tv_url',''));
            $liveEmbedSb = '';
            if ($liveTvUrlSb !== '' && preg_match('#(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([A-Za-z0-9_-]{6,})#i', $liveTvUrlSb, $ymS)) {
              $liveEmbedSb = 'https://www.youtube.com/embed/' . $ymS[1] . '?rel=0&modestbranding=1&playsinline=1';
            } elseif (stripos($liveTvUrlSb, 'http') === 0) {
              $liveEmbedSb = $liveTvUrlSb;
            }
          ?>
          <div style="padding:.8rem 1rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(148,163,184,.12);">
            <div style="display:inline-flex;align-items:center;gap:.45rem;font-weight:800;letter-spacing:-.01em;">
              <span style="width:10px;height:10px;border-radius:9999px;background:#E11D48;box-shadow:0 0 0 3px rgba(225,29,72,.18);"></span>
              AMIGOS TV
            </div>
            <a href="/en-vivo" style="background:#fff;color:#0F172A;padding:.25rem .6rem;border-radius:9999px;text-decoration:none;font-weight:700;font-size:.78rem;">VER ↗</a>
          </div>
          <?php if ($liveTvEnabledSb && $liveEmbedSb !== ''): ?>
            <div style="aspect-ratio:16/9;background:#000;">
              <iframe src="<?php echo e($liveEmbedSb) ?>" title="<?php echo e($liveTvTitleSb) ?>" style="width:100%;height:100%;border:0;display:block;background:#000;" loading="lazy" allow="autoplay; encrypted-media; picture-in-picture; gyroscope" allowfullscreen></iframe>
            </div>
          <?php else: ?>
            <div style="padding:1.5rem 1rem;text-align:center;color:#94A3B8;">
              <div style="font-size:2.2rem;">📺</div>
              <div style="font-weight:700;color:#CBD5E1;margin-top:.25rem;">TV en vivo</div>
              <div style="font-size:.82rem;color:#64748B;margin-top:.25rem;">Configírala en Ajustes &gt; AMIGOS TV.</div>
            </div>
          <?php endif; ?>
        </div>
        <div class="rm-widget-radio d-flex flex-column align-items-center text-center gap-3" role="region" aria-label="Escucha nuestra radio">
          <h4 class="h5 text-white fw-700 mb-0">📻 Radio AMIGOS POR EL MUNDO</h4>
          <p class="text-white opacity-90 mb-0">
            <?php echo !empty($radioStreamUrlHome) ? 'Sintoniza la señal en vivo las 24 horas' : 'Pronto transmitiendo en vivo las 24 horas' ?>
          </p>
          <a href="<?php echo url('/radio') ?>" class="rm-mini-play" aria-label="Ir a página de radio">
            <span aria-hidden="true"><?php echo !empty($radioStreamUrlHome) ? '▶' : '📻' ?></span>
          </a>
          <a href="<?php echo url('/radio') ?>" class="rm-btn rm-btn-outline-primary">
            Escuchar radio →
          </a>
        </div>

        <?php
          $trendingNews = [];
          try {
            if (method_exists(\App\Models\News::class, 'getMostViewed')) {
              $trendingNews = \App\Models\News::getMostViewed(5);
            } elseif (empty($trendingNews)) {
              $trendingNews = method_exists(\App\Models\News::class, 'latestPublished')
                ? \App\Models\News::latestPublished(5)
                : [];
            }
          } catch (\Throwable $e) { $trendingNews = []; }
        ?>
        <?php if (!empty($trendingNews)): ?>
          <div class="rm-widget">
            <h3 class="rm-widget-title">📊 Lo más leído</h3>
            <div class="rm-trending-list">
              <?php foreach (array_slice((array)$trendingNews, 0, 5) as $idx => $tn): ?>
                <a href="<?php echo news_url($tn['slug'] ?? '') ?>" class="rm-trending-item" aria-label="<?php echo e($tn['title'] ?? '') ?>">
                  <span class="rm-trending-num" aria-hidden="true"><?php echo ($idx+1) ?></span>
                  <div class="rm-trending-info">
                    <h4><?php echo e($tn['title'] ?? '') ?></h4>
                    <span>
                      <?php if (!empty($tn['views_count'])): ?>
                        👁 <?php echo e($tn['views_count']) ?>
                      <?php else: ?>
                        📰 Noticia
                      <?php endif; ?>
                    </span>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </aside>
    </div>
  </section>
</main>

<?php include __DIR__ . '/../partials/public_footer.php'; ?>
