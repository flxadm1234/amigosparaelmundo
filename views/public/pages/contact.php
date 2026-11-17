<?php include __DIR__ . '/../../partials/public_head.php'; ?>
<?php include __DIR__ . '/../../partials/public_navbar.php'; ?>

<main>
  <section class="page-head page-head-gradient">
    <div class="container">
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= url('/') ?>">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Contacto</li>
        </ol>
      </nav>
      <h1 class="page-title">Contáctanos</h1>
      <p class="page-subtitle mb-0">Estamos para responder tus consultas sobre estudios, encuestas y cotizaciones.</p>
    </div>
  </section>

  <section class="section section-white">
    <div class="container">
      <?php include __DIR__ . '/../../partials/flash.php'; ?>

      <div class="row g-5">
        <!-- INFO -->
        <aside class="col-lg-5 col-12 order-2 order-lg-1">
          <div class="card contact-info-card mb-4">
            <div class="card-body">
              <h3 class="card-title-sm mb-4">📍 Datos de contacto</h3>
              <?php
                $addr = app_setting('contact_address', '');
                $phones = app_setting('contact_phones', '');
                $emails = app_setting('contact_emails', '');
                $wh = app_setting('contact_hours', '');
                $phoneArr = array_values(array_filter(array_map('trim', explode('|', $phones))));
                $emailArr = array_values(array_filter(array_map('trim', explode('|', $emails))));
              ?>

              <?php if ($addr): ?>
                <div class="ci-row">
                  <div class="ci-icon ci-icon-navy">📍</div>
                  <div class="ci-body">
                    <div class="ci-label">Dirección</div>
                    <div class="ci-value muted"><?= e($addr) ?></div>
                  </div>
                </div>
              <?php endif; ?>

              <?php foreach ($phoneArr as $p): ?>
                <div class="ci-row">
                  <div class="ci-icon ci-icon-teal">📞</div>
                  <div class="ci-body">
                    <div class="ci-label">Teléfono / WhatsApp</div>
                    <a class="ci-value ci-link" href="tel:<?= e(preg_replace('/[^0-9+]/','',$p)) ?>"><?= e($p) ?></a>
                  </div>
                </div>
              <?php endforeach; ?>

              <?php foreach ($emailArr as $em): ?>
                <div class="ci-row">
                  <div class="ci-icon ci-icon-accent">✉️</div>
                  <div class="ci-body">
                    <div class="ci-label">Correo electrónico</div>
                    <a class="ci-value ci-link" href="mailto:<?= e($em) ?>"><?= e($em) ?></a>
                  </div>
                </div>
              <?php endforeach; ?>

              <?php if ($wh): ?>
                <div class="ci-row">
                  <div class="ci-icon ci-icon-navy">🕒</div>
                  <div class="ci-body">
                    <div class="ci-label">Horario de atención</div>
                    <div class="ci-value muted"><?= e($wh) ?></div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="card contact-cta-card">
            <div class="card-body">
              <h3 class="card-title-sm mb-2">💼 Cotiza tu estudio</h3>
              <p class="mb-3">Cuéntanos sobre tu necesidad: sondeo, censo, evaluación de políticas, focus group, mercado.</p>
              <a class="btn btn-light btn-block btn-icon-left" href="mailto:<?= e($emailArr[0] ?? 'contacto@amigosporelmundo.org') ?>">
                <span>✉️</span><span>Escríbenos ahora</span>
              </a>
            </div>
          </div>
        </aside>

        <!-- FORMULARIO -->
        <div class="col-lg-7 col-12 order-1 order-lg-2">
          <div class="card contact-form-card">
            <div class="card-body">
              <h3 class="card-title-sm mb-4">📝 Envíanos un mensaje</h3>
              <form method="post" action="<?= url('/contacto/enviar') ?>" novalidate class="needs-validation">
                <?= csrf_field() ?>
                <div class="row g-3">
                  <div class="col-md-6 col-sm-12">
                    <label class="form-label">Nombre completo *</label>
                    <input type="text" name="name" required minlength="3" maxlength="120" class="form-control"
                           value="<?= e(old('name')) ?>" placeholder="Tu nombre y apellidos">
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <label class="form-label">Correo electrónico *</label>
                    <input type="email" name="email" required maxlength="180" class="form-control"
                           value="<?= e(old('email')) ?>" placeholder="tu@empresa.com">
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <label class="form-label">Teléfono / Celular</label>
                    <input type="tel" name="phone" maxlength="40" class="form-control"
                           value="<?= e(old('phone')) ?>" placeholder="+51 9XX XXX XXX">
                  </div>
                  <div class="col-md-6 col-sm-12">
                    <label class="form-label">Asunto *</label>
                    <input type="text" name="subject" required minlength="5" maxlength="200" class="form-control"
                           value="<?= e(old('subject')) ?>" placeholder="Ej: Cotización sondeo municipal">
                  </div>
                  <div class="col-12">
                    <label class="form-label">Mensaje *</label>
                    <textarea name="message" rows="6" required minlength="10" maxlength="4000" class="form-control"
                              placeholder="Cuéntanos sobre tu proyecto, ámbito geográfico, muestra aproximada, fechas…"><?= e(old('message')) ?></textarea>
                    <div class="form-help">Mínimo 10 caracteres. No envíes datos sensibles (DNI, tarjetas) por este medio.</div>
                  </div>
                  <div class="col-12">
                    <label class="form-check">
                      <input type="checkbox" name="privacy_accepted" required class="form-check-input">
                      <span>Acepto la <a href="<?= url('/politica-privacidad') ?>" target="_blank" rel="noopener">política de privacidad</a> y el tratamiento de mis datos para responder a esta consulta.</span>
                    </label>
                  </div>
                  <div class="col-12">
                    <div class="d-flex gap-2 flex-wrap">
                      <button type="submit" class="btn btn-primary btn-lg btn-icon-left">
                        <span>✉️</span><span>Enviar mensaje</span>
                      </button>
                      <button type="reset" class="btn btn-outline-secondary btn-lg btn-icon-left">
                        <span>♻️</span><span>Limpiar</span>
                      </button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <div class="mt-4">
            <article class="card card-paper rich-text contact-about-card">
              <div class="card-body">
                <?= $page['content_html'] ?? '<h2>Nuestra oficina</h2><p>Atendemos de lunes a viernes. Respuesta a consultas en menos de 24 horas hábiles.</p>' ?>
              </div>
            </article>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/../../partials/public_footer.php'; ?>
