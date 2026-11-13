<?php
require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/db.php';

// El equipo se alimenta de la tabla `empleados` (se administra desde el panel)
try {
    $equipo = db()->query('SELECT * FROM empleados ORDER BY id DESC')->fetchAll();
} catch (Exception $e) {
    $equipo = [];
}
if (empty($equipo)) {
    $equipo = [
        ['nombre' => 'Carlos Ramírez', 'cargo' => 'Instructor certificado', 'telefono' => '+503 2222-3333', 'imagen' => BASE_URL . '/assets/img/equipo/miembro-1.jpg'],
        ['nombre' => 'Ana Martínez', 'cargo' => 'Instructora de circuito', 'telefono' => '+503 2222-3334', 'imagen' => BASE_URL . '/assets/img/equipo/miembro-2.jpg'],
        ['nombre' => 'José Hernández', 'cargo' => 'Instructor de noche', 'telefono' => '+503 2222-3335', 'imagen' => BASE_URL . '/assets/img/equipo/miembro-3.jpg'],
        ['nombre' => 'Maria Fernanda Lopez', 'cargo' => 'Instructora de noche', 'telefono' => '+503 2222-3336', 'imagen' => BASE_URL . '/assets/img/equipo/miembro-4.jpg'],
        ['nombre' => 'Roberto Sánchez', 'cargo' => 'Instructor de carga pesada', 'telefono' => '+503 2222-3337', 'imagen' => BASE_URL . '/assets/img/equipo/miembro-5.jpg'],
        ['nombre' => 'Valeria Ortiz', 'cargo' => 'Asesora vial y atención', 'telefono' => '+503 2222-3338', 'imagen' => BASE_URL . '/assets/img/equipo/miembro-6.jpg'],
    ];
}
?>

<!-- ============ HERO (adaptado al diseno Canva: ancho completo, imagen de fondo) ============ -->
<section class="hero-wrap">
  <div class="container">
    <div class="hero-copy">
      <span class="hero-kicker" data-anim="fadeInUp">Seguridad, experiencia y confianza al volante</span>
      <h1 class="hero-title" data-anim="fadeInUp" data-anim-delay="1">Aprende a manejar con confianza</h1>
      <p class="hero-lead" data-anim="fadeInUp" data-anim-delay="2">Instructores certificados, autos doble comando y cursos para todas las edades</p>
      <div class="hero-cta" data-anim="fadeInUp" data-anim-delay="3">
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
      <h2 class="section-title" data-anim="fadeInUp">Nuestros <span>Cursos</span></h2>
      <p class="section-sub" data-anim="fadeInUp" data-anim-delay="1">Elige la licencia que necesitas y comienza hoy mismo</p>
      <div class="title-line" data-anim="fadeInUp" data-anim-delay="2"></div>
    </div>

    <div class="row g-4">
      <?php
      $cursos = [
        ['fa-motorcycle','Clase A','Motocicleta','$120','2 semanas','motocicleta.jpg',['Incluye casco y guantes','Practica en circuito cerrado','Teoria + examen practico','Certificado inmediato']],
        ['fa-car-side','Clase B','Vehiculo particular','$180','4 semanas','ClaseB.jpg',['Vehiculo automatico o manual','Instructor certificado','Simulador de conduccion','Placa y permiso incluidos']],
        ['fa-truck','Clase C','Vehiculo de carga','$260','6 semanas','microbus.jpg',['Manejo de camiones y trailers','Certificacion oficial','Horarios nocturnos disponibles','Acompañamiento post-certificacion']],
      ];
      foreach ($cursos as $i => $c):
        [$icon,$titulo,$sub,$precio,$dura,$img,$feats] = $c;
      ?>
      <div class="col-lg-4 col-md-6">
        <article class="course-card" data-anim="fadeIn" data-anim-delay="<?= $i + 1 ?>">
          <img src="<?= BASE_URL ?>/assets/img/<?= $img ?>" alt="<?= $titulo ?> - <?= $sub ?>" loading="lazy">
          <span class="course-tag"><i class="fas fa-clock"></i><?= $dura ?></span>
          <span class="course-price"><span class="course-price-num"><?= $precio ?></span><span class="visually-hidden"> por curso</span></span>
          <div class="course-content">
            <h3 class="course-title"><?= $titulo ?></h3>
            <div class="course-infos">
              <i class="fas <?= $icon ?>"></i>
              <span><?= $sub ?></span>
              <span class="course-price-slot" aria-hidden="true"></span>
            </div>
            <ul class="course-synopsis">
              <?php foreach ($feats as $f): ?>
              <li><i class="fas fa-check-circle"></i><span><?= $f ?></span></li>
              <?php endforeach; ?>
            </ul>
            <div class="course-actions">
              <a href="#contacto" class="course-cta">Inscribirme <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </article>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ PORQUE NOSOTROS (slider tipo blog) ============ -->
<section class="section section-band why-slider-section" id="porque">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title" data-anim="fadeInUp">¿Por qué <span>ESDRIVE</span>?</h2>
      <p class="section-sub" data-anim="fadeInUp" data-anim-delay="1">Razones por las que somos tu mejor opción</p>
      <div class="title-line" data-anim="fadeInUp" data-anim-delay="2"></div>
    </div>

    <div class="why-slider-wrap" data-anim="fadeIn" data-anim-delay="2">
      <div class="blog-slider swiper" aria-label="Razones para elegir ESDRIVE">
        <div class="swiper-wrapper">
          <?php
          $razones = [
            ['teoria-moderna.jpg', 'Aprendizaje', 'Teoría moderna', 'Clases con material multimedia y simuladores interactivos.', 'Aula moderna con proyección y estudiantes'],
            ['vehiculos.jpg', 'Flota', 'Vehículos nuevos', 'Flota reciente y mantenida para tu práctica.', 'Vehículo reciente para prácticas de manejo'],
            ['instructores.jpg', 'Equipo', 'Instructores certificados', 'Profesionales con licencia y años de experiencia.', 'Instructora certificada de ESDRIVE'],
            ['horarios.jpg', 'Flexibilidad', 'Horarios flexibles', 'Clases de mañana, tarde y fin de semana.', 'Calendario de clases'],
            ['seguro.jpg', 'Seguridad', 'Totalmente asegurado', 'Cada alumno practica cubierto por seguro.', 'Firma de contrato de seguro'],
            ['certificacion.jpg', 'Certificación', 'Certificación oficial', 'Te preparamos para aprobar a la primera.', 'Examen de certificación'],
          ];

          foreach ($razones as $i => $razon):
            [$imagen, $categoria, $titulo, $texto, $alt] = $razon;
          ?>
          <article class="swiper-slide blog-slider__item">
            <div class="blog-slider__img">
              <img src="<?= BASE_URL ?>/assets/img/porque/<?= $imagen ?>" alt="<?= htmlspecialchars($alt) ?>" loading="lazy">
            </div>
            <div class="blog-slider__content">
              <span class="blog-slider__code"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?> · <?= $categoria ?></span>
              <h3 class="blog-slider__title"><?= $titulo ?></h3>
              <p class="blog-slider__text"><?= $texto ?></p>
              <a href="#contacto" class="blog-slider__button">
                <i class="fas fa-calendar-check me-2"></i>Agenda tu clase
              </a>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
        <div class="blog-slider__pagination"></div>
      </div>
    </div>
  </div>
</section>

<!-- ============ NUESTRO EQUIPO (carrusel 3D interactivo desde la BD) ============ -->
<?php if (!empty($equipo)): ?>
<section class="section team-section" id="equipo">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title" data-anim="fadeInUp">Nuestro <span>Equipo</span></h2>
      <p class="section-sub" data-anim="fadeInUp" data-anim-delay="1">Los instructores y staff que te acompañan hasta tu licencia</p>
      <div class="title-line" data-anim="fadeInUp" data-anim-delay="2"></div>
    </div>

    <div class="team-stage" data-anim="fadeIn" data-anim-delay="2">
      <span class="team-ghost-title" aria-hidden="true">OUR TEAM</span>

      <div class="team-carousel">
        <button class="team-arrow left" type="button" aria-label="Miembro anterior">
          <i class="fas fa-chevron-left" aria-hidden="true"></i>
        </button>

        <div class="team-track">
          <?php
          $totalEquipo = count($equipo);
          foreach ($equipo as $i => $e):
            $nombre = $e['nombre'] ?? '';
            $cargo  = !empty($e['cargo']) ? $e['cargo'] : 'Instructor certificado';
            $tel    = $e['telefono'] ?? '';
            $foto   = !empty($e['imagen']) ? $e['imagen'] : BASE_URL . '/assets/img/equipo/miembro-' . (($i % 6) + 1) . '.jpg';

            $offset = $i;
            if ($offset === 0) {
              $clase = 'center';
            } elseif ($offset === 1) {
              $clase = 'right-1';
            } elseif ($offset === $totalEquipo - 1) {
              $clase = 'left-1';
            } elseif ($offset === 2) {
              $clase = 'right-2';
            } elseif ($offset === $totalEquipo - 2) {
              $clase = 'left-2';
            } else {
              $clase = 'hidden';
            }
          ?>
          <div class="team-card <?= $clase ?>"
               data-index="<?= $i ?>"
               data-name="<?= htmlspecialchars($nombre) ?>"
               data-role="<?= htmlspecialchars($cargo) ?>"
               data-phone="<?= htmlspecialchars($tel) ?>">
            <img src="<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($nombre . ' - ' . $cargo) ?>" loading="lazy">
          </div>
          <?php endforeach; ?>
        </div>

        <button class="team-arrow right" type="button" aria-label="Siguiente miembro">
          <i class="fas fa-chevron-right" aria-hidden="true"></i>
        </button>
      </div>

      <div class="team-member-info" aria-live="polite">
        <h3 class="team-member-name"><?= htmlspecialchars($equipo[0]['nombre'] ?? '') ?></h3>
        <p class="team-member-role"><?= htmlspecialchars(!empty($equipo[0]['cargo']) ? $equipo[0]['cargo'] : 'Instructor certificado') ?></p>
        <div class="team-member-contact">
          <?php if (!empty($equipo[0]['telefono'])): ?>
            <a href="tel:<?= htmlspecialchars($equipo[0]['telefono']) ?>" class="team-member-phone">
              <i class="fas fa-phone me-1"></i><?= htmlspecialchars($equipo[0]['telefono']) ?>
            </a>
          <?php endif; ?>
        </div>
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
<?php endif; ?>

<!-- ============ TESTIMONIOS ============ -->
<section class="section" id="testimonios">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title" data-anim="fadeInUp">Lo que dicen <span>nuestros alumnos</span></h2>
      <p class="section-sub" data-anim="fadeInUp" data-anim-delay="1">Historias reales de quienes ya obtuvieron su licencia</p>
      <div class="title-line" data-anim="fadeInUp" data-anim-delay="2"></div>
    </div>
    <div class="row g-4">
      <?php
      $testis = [
        ['Maria Lopez','Aprobada Clase B','Obten mi licencia a la primera. Los instructores son muy pacientes y el material esta muy bien explicado.'],
        ['Carlos Ramirez','Aprobada Clase A','La practica en circuito cerrado me dio mucha seguridad. Totalmente recomendado.'],
        ['Ana Martinez','Aprobada Clase C','Flexibilidad de horarios y vehiculos en excelente estado. Aprobar el examen fue facil.'],
      ];
      foreach ($testis as $i => $t):
        [$nom,$rol,$txt] = $t;
        $ini = strtoupper(substr($nom,0,1));
      ?>
      <div class="col-lg-4 col-md-6">
        <div class="testi-card" data-anim="fadeIn" data-anim-delay="<?= $i + 1 ?>">
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
      <h2 class="section-title" data-anim="fadeInUp">Preguntas <span>frecuentes</span></h2>
      <p class="section-sub" data-anim="fadeInUp" data-anim-delay="1">Todo lo que necesitas saber antes de inscribirte</p>
      <div class="title-line" data-anim="fadeInUp" data-anim-delay="2"></div>
    </div>

    <div class="faq-wrap" data-anim="fadeIn" data-anim-delay="2">
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
    <div class="cta-inner" data-anim="fadeIn">
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
          <p class="contact-form-intro">Completa tus datos y te confirmamos por WhatsApp.</p>

          <div class="contact-form-grid">
            <div class="form-field">
              <label for="f-nombre">Nombre completo</label>
              <input type="text" class="form-control" id="f-nombre" name="nombre"
                     placeholder="Ej. María López" autocomplete="name" required>
            </div>

            <div class="form-field">
              <label for="f-telefono">Teléfono / WhatsApp</label>
              <input type="tel" class="form-control" id="f-telefono" name="telefono"
                     placeholder="Ej. 7000-0000" autocomplete="tel" inputmode="tel" required>
            </div>

            <div class="form-field form-field-full">
              <label for="f-curso">Curso de interés</label>
              <select class="form-select" id="f-curso" name="curso" required>
                <option value="Clase A - Motocicleta ($120)">Clase A - Motocicleta ($120)</option>
                <option value="Clase B - Vehiculo particular ($180)" selected>Clase B - Vehiculo particular ($180)</option>
                <option value="Clase C - Vehiculo de carga ($260)">Clase C - Vehiculo de carga ($260)</option>
              </select>
            </div>

            <div class="form-field form-field-full">
              <label for="f-mensaje">Mensaje <span class="fw-normal text-secondary">(opcional)</span></label>
              <textarea class="form-control" id="f-mensaje" name="mensaje" rows="4"
                        placeholder="Escribe tu mensaje aquí..."></textarea>
            </div>
          </div>

          <button type="submit" class="btn btn-gold">
            <i class="fab fa-whatsapp me-2"></i>Enviar por WhatsApp
          </button>
          <p class="form-note"><i class="fab fa-whatsapp"></i> Se abrirá WhatsApp con tus datos. Sin spam.</p>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- ============ REDES SOCIALES Y UBICACION ============ -->
<section class="section social-section" id="siguenos">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title" data-anim="fadeInUp">Síguenos y <span>visítanos</span></h2>
      <p class="section-sub" data-anim="fadeInUp" data-anim-delay="1">Conecta con nosotros en redes o llega a nuestra escuela</p>
      <div class="title-line" data-anim="fadeInUp" data-anim-delay="2"></div>
    </div>

    <?php
    // TODO: reemplazar los enlaces "#siguenos" por los perfiles oficiales cuando esten disponibles.
    $redesSociales = [
      ['fab fa-tiktok', 'TikTok', '@esdrive', '#siguenos', false],
      ['fab fa-instagram', 'Instagram', '@esdrive', '#siguenos', false],
      ['fab fa-facebook-f', 'Facebook', 'ESDRIVE Escuela de Manejo', '#siguenos', false],
      ['fas fa-envelope', 'Correo electrónico', 'contacto@esdrive.com', 'mailto:contacto@esdrive.com', false],
      ['fab fa-whatsapp', 'WhatsApp', '+503 7000-0000', 'https://wa.me/50370000000', true],
    ];

    // TODO: reemplazar por la direccion exacta de la escuela.
    $direccionCorta = 'Av. Principal #123, San Salvador';
    $direccionMapa = $direccionCorta . ', El Salvador';
    $mapaEmbed = 'https://www.google.com/maps?q=' . rawurlencode($direccionMapa) . '&output=embed';
    $mapaRuta = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($direccionMapa);
    ?>

    <div class="social-layout">
      <div class="social-channels" data-anim="fadeIn" data-anim-delay="2">
        <?php foreach ($redesSociales as [$icono, $nombre, $detalle, $url, $externo]): ?>
        <a class="social-channel" href="<?= htmlspecialchars($url) ?>"<?= $externo ? ' target="_blank" rel="noopener"' : '' ?>>
          <span class="social-icon"><i class="<?= $icono ?>"></i></span>
          <span class="social-copy">
            <span class="social-name"><?= $nombre ?></span>
            <span class="social-detail"><?= $detalle ?></span>
          </span>
          <i class="fas fa-arrow-right social-arrow" aria-hidden="true"></i>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="map-card" data-anim="fadeIn" data-anim-delay="2">
        <div class="map-frame">
          <iframe
            src="<?= $mapaEmbed ?>"
            title="Ubicación de ESDRIVE Escuela de Manejo"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen></iframe>
        </div>
        <div class="map-info">
          <div>
            <span class="map-kicker"><i class="fas fa-location-dot"></i> Nuestra ubicación</span>
            <h3><?= $direccionCorta ?></h3>
            <p><i class="fas fa-clock me-2"></i>Lun a Sab: 7:00 - 18:00</p>
          </div>
          <a class="btn btn-gold" href="<?= $mapaRuta ?>" target="_blank" rel="noopener">
            <i class="fas fa-diamond-turn-right me-2"></i>Cómo llegar
          </a>
        </div>
      </div>
    </div>
  </div>
</section>