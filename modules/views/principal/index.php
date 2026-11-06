<?php require_once __DIR__ . '/../../../php/base.php'; ?>

<!-- ============ HERO (adaptado al diseno Canva: ancho completo, imagen de fondo) ============ -->
<section class="hero-wrap">
  <div class="container">
    <div class="hero-copy">
      <span class="hero-kicker">Seguridad, experiencia y confianza al volante</span>
      <h1 class="hero-title">Aprende a manejar con confianza</h1>
      <p class="hero-lead">Instructores certificados, autos doble comando y cursos para todas las edades</p>
      <div class="hero-cta">
        <a href="#contacto" class="btn btn-gold">Agenda tu clase muestra gratis</a>
        <a href="tel:+50322223333" class="btn btn-hero-outline"><i class="fas fa-phone me-2"></i>+503 2222-3333</a>
      </div>
    </div>
  </div>
</section>

<!-- ============ CURSOS ============ -->
<section class="section" id="cursos">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title reveal">Nuestros <span>Cursos</span></h2>
      <p class="section-sub reveal">Elige la licencia que necesitas y comienza hoy mismo</p>
      <div class="title-line reveal"></div>
    </div>

    <div class="row g-4">
      <?php
      $cursos = [
        ['fa-motorcycle','Clase A','Motocicleta','$120','2 semanas','motocicleta.jpg',['Incluye casco y guantes','Practica en circuito cerrado','Teoria + examen practico','Certificado inmediato']],
        ['fa-car-side','Clase B','Vehiculo particular','$180','4 semanas','sedan.jpg',['Vehiculo automatico o manual','Instructor certificado','Simulador de conduccion','Placa y permiso incluidos']],
        ['fa-truck','Clase C','Vehiculo de carga','$260','6 semanas','microbus.jpg',['Manejo de camiones y trailers','Certificacion oficial','Horarios nocturnos disponibles','Acompañamiento post-certificacion']],
      ];
      foreach ($cursos as $c):
        [$icon,$titulo,$sub,$precio,$dura,$img,$feats] = $c;
      ?>
      <div class="col-lg-4 col-md-6">
        <div class="course-card reveal">
          <div class="course-media">
            <img src="<?= BASE_URL ?>/assets/img/<?= $img ?>" alt="<?= $titulo ?> - <?= $sub ?>" loading="lazy">
            <span class="course-tag"><i class="fas fa-clock"></i><?= $dura ?></span>
          </div>
          <div class="course-body">
            <div class="course-head">
              <div class="course-icon"><i class="fas <?= $icon ?>"></i></div>
              <div>
                <h4><?= $titulo ?></h4>
                <p><?= $sub ?></p>
              </div>
            </div>
            <div class="course-price"><?= $precio ?> <small>/ total</small></div>
            <ul class="course-feats">
              <?php foreach ($feats as $f): ?>
              <li><i class="fas fa-check-circle"></i><?= $f ?></li>
              <?php endforeach; ?>
            </ul>
            <a href="#contacto" class="btn btn-gold w-100">Inscribirme</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ PORQUE NOSOTROS ============ -->
<section class="section section-band" id="porque">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title reveal">¿Por que <span>ESDRIVE</span>?</h2>
      <p class="section-sub reveal">Razones por las que somos la mejor opcion</p>
      <div class="title-line reveal"></div>
    </div>
    <div class="feature-grid reveal">
      <div class="row g-0">
      <?php
      $feats = [
        ['fa-chalkboard-teacher','Teoria moderna','Clases con material multimedia y simuladores interactivos.'],
        ['fa-car','Vehiculos nuevos','Flota reciente y mantenida para tu practica.'],
        ['fa-user-tie','Instructores certificados','Profesionales con licencia y anos de experiencia.'],
        ['fa-clock','Horarios flexibles','Clases de manana, tarde y fin de semana.'],
        ['fa-shield-halved','Totalmente asegurado','Cada alumno practica cubierto por seguro.'],
        ['fa-graduation-cap','Certificacion oficial','Te preparamos para aprobar a la primera.'],
      ];
      foreach ($feats as $i => $f):
        [$icon,$t,$d] = $f;
      ?>
      <div class="col-lg-4 col-md-6">
        <div class="feature-item">
          <span class="fi-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
          <div class="fi-icon"><i class="fas <?= $icon ?>"></i></div>
          <h5><?= $t ?></h5>
          <p><?= $d ?></p>
        </div>
      </div>
      <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ============ EQUIPO (carrusel 3D) ============ -->
<section class="section team-section" id="equipo">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title reveal">Nuestro <span>Equipo</span></h2>
      <p class="section-sub reveal">Instructores y asesores que te acompañan en cada etapa</p>
      <div class="title-line reveal"></div>
    </div>

    <div class="team-stage reveal">
      <span class="team-ghost-title" aria-hidden="true">OUR TEAM</span>

      <div class="team-carousel">
        <button class="team-arrow left" type="button" aria-label="Miembro anterior">
          <i class="fas fa-chevron-left" aria-hidden="true"></i>
        </button>

        <div class="team-track">
          <?php
          $equipo = [
            ['miembro-1.jpg', 'Emily Kim', 'Founder'],
            ['miembro-2.jpg', 'Michael Steward', 'Creative Director'],
            ['miembro-3.jpg', 'Emma Rodriguez', 'Lead Developer'],
            ['miembro-4.jpg', 'Julia Gimmel', 'UX Designer'],
            ['miembro-5.jpg', 'Lisa Anderson', 'Marketing Manager'],
            ['miembro-6.jpg', 'James Wilson', 'Product Manager'],
          ];
          $totalEquipo = count($equipo);

          foreach ($equipo as $i => $miembro):
            [$foto, $nombre, $rol] = $miembro;
            $offset = $i; // El primer miembro (indice 0) es el centro
            if ($offset === 0) {
              $clase = 'center';
            } elseif ($offset === 1) {
              $clase = 'right-1';
            } elseif ($offset === 2) {
              $clase = 'right-2';
            } elseif ($offset === $totalEquipo - 1) {
              $clase = 'left-1';
            } elseif ($offset === $totalEquipo - 2) {
              $clase = 'left-2';
            } else {
              $clase = 'hidden';
            }
          ?>
          <div class="team-card <?= $clase ?>" data-index="<?= $i ?>" data-name="<?= htmlspecialchars($nombre) ?>" data-role="<?= htmlspecialchars($rol) ?>">
            <img src="<?= BASE_URL ?>/assets/img/equipo/<?= $foto ?>" alt="<?= htmlspecialchars($nombre . ' - ' . $rol) ?>" loading="lazy">
          </div>
          <?php endforeach; ?>
        </div>

        <button class="team-arrow right" type="button" aria-label="Siguiente miembro">
          <i class="fas fa-chevron-right" aria-hidden="true"></i>
        </button>
      </div>

      <div class="team-member-info" aria-live="polite">
        <h3 class="team-member-name"><?= htmlspecialchars($equipo[0][1]) ?></h3>
        <p class="team-member-role"><?= htmlspecialchars($equipo[0][2]) ?></p>
      </div>

      <div class="team-dots" aria-label="Seleccionar miembro del equipo">
        <?php for ($i = 0; $i < $totalEquipo; $i++): ?>
        <button class="team-dot<?= $i === 0 ? ' active' : '' ?>" type="button" data-index="<?= $i ?>"
                aria-label="Ver miembro <?= $i + 1 ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"></button>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</section>

<!-- ============ TESTIMONIOS ============ -->
<section class="section" id="testimonios">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title reveal">Lo que dicen <span>nuestros alumnos</span></h2>
      <p class="section-sub reveal">Historias reales de quienes ya obtuvieron su licencia</p>
      <div class="title-line reveal"></div>
    </div>
    <div class="row g-4">
      <?php
      $testis = [
        ['Maria Lopez','Aprobada Clase B','Obten mi licencia a la primera. Los instructores son muy pacientes y el material esta muy bien explicado.'],
        ['Carlos Ramirez','Aprobada Clase A','La practica en circuito cerrado me dio mucha seguridad. Totalmente recomendado.'],
        ['Ana Martinez','Aprobada Clase C','Flexibilidad de horarios y vehiculos en excelente estado. Aprobar el examen fue facil.'],
      ];
      foreach ($testis as $t):
        [$nom,$rol,$txt] = $t;
        $ini = strtoupper(substr($nom,0,1));
      ?>
      <div class="col-lg-4 col-md-6">
        <div class="testi-card reveal">
          <i class="fas fa-quote-right testi-quote"></i>
          <div class="testi-stars">
            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
          </div>
          <p><?= $txt ?></p>
          <div class="testi-user">
            <div class="testi-avatar"><?= $ini ?></div>
            <div>
              <div class="testi-name"><?= $nom ?></div>
              <div class="testi-role"><?= $rol ?></div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ PREGUNTAS FRECUENTES ============ -->
<section class="section section-band" id="preguntas">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title reveal">Preguntas <span>frecuentes</span></h2>
      <p class="section-sub reveal">Todo lo que necesitas saber antes de inscribirte</p>
      <div class="title-line reveal"></div>
    </div>

    <div class="faq-wrap reveal">
      <div class="accordion" id="faqAccordion">
        <?php
        $faqs = [
          ['faq1', true,  '¿Cuánto tiempo toma obtener mi licencia?', 'Depende de la clase que elijas: la Clase A (motocicleta) toma 2 semanas, la Clase B (particular) 4 semanas y la Clase C (carga) 6 semanas. Tenemos horarios de mañana, tarde y fines de semana para que avances a tu ritmo.'],
          ['faq2', false, '¿El curso incluye vehículo y seguro?', 'Sí. Practicas con vehículos doble comando, de flotilla reciente y totalmente asegurados. En la Clase B puedes elegir entre vehículo automático o manual, y en la Clase A incluimos casco y guantes.'],
          ['faq3', false, '¿Qué edad mínima se necesita para inscribirse?', 'Puedes inscribirte desde los 16 años con permiso provisional firmado por tu padre, madre o tutor legal. Los menores reciben formación en circuito cerrado antes de salir a la vía pública.'],
          ['faq4', false, '¿Qué métodos de pago aceptan?', 'Aceptamos efectivo, tarjetas de débito/crédito y transferencias bancarias. Puedes apartar tu lugar con el 50% de anticipo y pagar el resto antes de tu primera práctica.'],
          ['faq5', false, '¿Qué pasa si no apruebo el examen a la primera?', 'Sin problema: tu paquete incluye clases de refuerzo sin costo hasta que apruebes. Nuestro 98% de aprobados en el primer intento sale de prepararte con simulacros y práctica constante.'],
        ];
        foreach ($faqs as [$id, $open, $pregunta, $respuesta]):
        ?>
        <div class="accordion-item">
          <h3 class="accordion-header" id="heading-<?= $id ?>">
            <button class="accordion-button<?= $open ? '' : ' collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $id ?>" aria-expanded="<?= $open ? 'true' : 'false' ?>" aria-controls="<?= $id ?>">
              <?= $pregunta ?>
            </button>
          </h3>
          <div id="<?= $id ?>" class="accordion-collapse collapse<?= $open ? ' show' : '' ?>" aria-labelledby="heading-<?= $id ?>" data-bs-parent="#faqAccordion">
            <div class="accordion-body"><?= $respuesta ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ============ CTA CONTACTO (parallax) ============ -->
<section class="section cta-parallax" id="contacto">
  <div class="cta-parallax-bg" aria-hidden="true"></div>
  <div class="container">
    <div class="cta-inner reveal">
      <div class="cta-grid">
        <div>
          <span class="cta-kicker"><i class="fas fa-route"></i> Tu camino a la licencia empieza aqui</span>
          <h2 class="section-title">¿Listo para <span>conducir</span>?</h2>
          <p class="cta-lead">Agenda tu clase muestra gratis hoy y comienza el camino hacia tu licencia. Un asesor te contacta en minutos.</p>
          <ul class="cta-info">
            <li><i class="fas fa-phone"></i>+503 2222-3333</li>
            <li><i class="fas fa-clock"></i>Lun a Sab: 7:00 - 18:00</li>
            <li><i class="fas fa-location-dot"></i>Av. Principal #123, San Salvador</li>
            <li><i class="fas fa-envelope"></i>contacto@esdrive.com</li>
          </ul>
          <div class="cta-btns cta-btns-left">
            <a href="https://wa.me/50370000000" target="_blank" rel="noopener" class="btn btn-gold"><i class="fab fa-whatsapp me-2"></i>WhatsApp</a>
            <a href="mailto:contacto@esdrive.com" class="btn btn-white"><i class="fas fa-envelope me-2"></i>Correo</a>
          </div>
        </div>

        <form class="contact-form-card" id="formContacto" novalidate>
          <h3>Agenda tu clase gratis</h3>
          <p>Completa el formulario y te confirmamos por WhatsApp.</p>
          <label for="f-nombre">Nombre completo</label>
          <input type="text" class="form-control" id="f-nombre" name="nombre" placeholder="Ej. Maria Lopez" required>
          <label for="f-telefono">Telefono / WhatsApp</label>
          <input type="tel" class="form-control" id="f-telefono" name="telefono" placeholder="Ej. 7000-0000" required>
          <label for="f-curso">Curso de interes</label>
          <select class="form-select" id="f-curso" name="curso" required>
            <option value="Clase A - Motocicleta ($120)">Clase A - Motocicleta ($120)</option>
            <option value="Clase B - Vehiculo particular ($180)" selected>Clase B - Vehiculo particular ($180)</option>
            <option value="Clase C - Vehiculo de carga ($260)">Clase C - Vehiculo de carga ($260)</option>
          </select>
          <label for="f-mensaje">Mensaje <span class="fw-normal text-secondary">(opcional)</span></label>
          <textarea class="form-control" id="f-mensaje" name="mensaje" rows="4" placeholder="Escribe tu mensaje aqui..."></textarea>
          <button type="submit" class="btn btn-gold"><i class="fab fa-whatsapp me-2"></i>Enviar por WhatsApp</button>
          <p class="form-note"><i class="fab fa-whatsapp"></i> Tu mensaje se abrira en WhatsApp con tus datos. Sin spam.</p>
        </form>
      </div>
    </div>
  </div>
</section>