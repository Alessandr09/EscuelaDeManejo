<?php require_once __DIR__ . '/../../../php/base.php'; ?>

<!-- ============ HERO (ECOSAN: VMT, Santa Ana, imagen de fondo) ============ -->
<section class="hero-wrap">
  <div class="container">
    <div class="hero-copy">
      <span class="hero-kicker"><i class="fas fa-shield-halved me-2" aria-hidden="true"></i>Escuela y examinadora autorizada por el VMT</span>
      <h1 class="hero-title">Aprende a conducir con seguridad y obtén tu licencia</h1>
      <p class="hero-lead">Cursos teórico-prácticos en Santa Ana con instructores certificados, vehículos modernos asegurados y horarios flexibles. Inscríbete en línea en minutos.</p>
      <div class="hero-cta">
        <a href="#contacto" class="btn btn-gold">Inscribirme ahora</a>
        <a href="tel:+50324404057" class="btn btn-hero-outline"><i class="fas fa-phone me-2"></i>2440-4057</a>
      </div>
      <div class="hero-stats" aria-label="ECOSAN en cifras">
        <div class="hero-stat"><b>+5,000</b><small>Alumnos formados</small></div>
        <div class="hero-stat"><b>97%</b><small>Tasa de aprobación</small></div>
        <div class="hero-stat"><b>15+</b><small>Años de experiencia</small></div>
      </div>
    </div>
  </div>
</section>

<!-- ============ CURSOS Y PRECIOS ============ -->
<section class="section" id="cursos">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title reveal">Cursos y <span>precios</span></h2>
      <p class="section-sub reveal">Elige tu categoría de licencia y nivel. Contamos con vehículo particular, liviano, motocicleta y clases de refuerzo</p>
      <div class="title-line reveal"></div>
    </div>

    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <article class="course-card reveal">
          <div class="course-media">
            <!-- TODO(foto): reemplazar por foto real de la flota particular -->
            <img src="<?= BASE_URL ?>/assets/img/sedan.jpg" alt="Vehículo particular sedan para el curso de ECOSAN" loading="lazy">
            <span class="course-tag"><i class="fas fa-fire"></i>Más solicitado</span>
          </div>
          <div class="course-body">
            <div class="course-head">
              <div class="course-icon"><i class="fas fa-car-side"></i></div>
              <div>
                <h4>Vehículo Particular</h4>
                <p>Para conducir automóvil propio</p>
              </div>
            </div>
            <div class="course-price">Desde $140 <small>/ curso</small></div>
            <div class="table-responsive">
              <table class="price-table">
                <thead><tr><th>Nivel</th><th>Automático</th><th>Estándar</th></tr></thead>
                <tbody>
                  <tr><td>Principiante</td><td>$200</td><td>$180</td></tr>
                  <tr><td>Intermedio</td><td>$170</td><td>$160</td></tr>
                  <tr><td>Avanzado</td><td>$140</td><td>$140</td></tr>
                </tbody>
              </table>
            </div>
            <ul class="course-feats">
              <li><i class="fas fa-check-circle"></i>Transmisión automática o estándar</li>
              <li><i class="fas fa-check-circle"></i>De 1 a 6 semanas, a tu ritmo</li>
            </ul>
            <a href="#contacto" class="btn btn-gold w-100" data-curso="particular-estandar">Inscribirme</a>
          </div>
        </article>
      </div>

      <div class="col-lg-4 col-md-6">
        <article class="course-card reveal">
          <div class="course-media">
            <!-- TODO(foto): reemplazar por foto real de vehículos livianos -->
            <img src="<?= BASE_URL ?>/assets/img/microbus.jpg" alt="Vehículo liviano para el curso de ECOSAN" loading="lazy">
            <span class="course-tag"><i class="fas fa-truck-pickup"></i>Categoría Liviana</span>
          </div>
          <div class="course-body">
            <div class="course-head">
              <div class="course-icon"><i class="fas fa-truck"></i></div>
              <div>
                <h4>Vehículo Liviano</h4>
                <p>Licencia liviana y mayor porte</p>
              </div>
            </div>
            <div class="course-price">Desde $175 <small>/ curso</small></div>
            <div class="table-responsive">
              <table class="price-table">
                <thead><tr><th>Nivel</th><th>Precio</th></tr></thead>
                <tbody>
                  <tr><td>Principiante</td><td>$250</td></tr>
                  <tr><td>Avanzado</td><td>$175</td></tr>
                </tbody>
              </table>
            </div>
            <ul class="course-feats">
              <li><i class="fas fa-check-circle"></i>Formación práctica en vehículos de mayor porte</li>
              <li><i class="fas fa-check-circle"></i>Preparación para examen VMT</li>
            </ul>
            <a href="#contacto" class="btn btn-gold w-100" data-curso="liviano">Inscribirme</a>
          </div>
        </article>
      </div>

      <div class="col-lg-4 col-md-6">
        <article class="course-card reveal">
          <div class="course-media">
            <!-- TODO(foto): reemplazar por foto real de motocicletas de la escuela -->
            <img src="<?= BASE_URL ?>/assets/img/motocicleta.jpg" alt="Motocicleta para el curso de ECOSAN" loading="lazy">
            <span class="course-tag"><i class="fas fa-motorcycle"></i>Desde $120</span>
          </div>
          <div class="course-body">
            <div class="course-head">
              <div class="course-icon"><i class="fas fa-motorcycle"></i></div>
              <div>
                <h4>Motocicleta</h4>
                <p>Aprende a conducir moto con seguridad</p>
              </div>
            </div>
            <div class="course-price">Desde $120 <small>/ curso</small></div>
            <div class="table-responsive">
              <table class="price-table">
                <thead><tr><th>Nivel</th><th>Automático</th><th>Estándar</th></tr></thead>
                <tbody>
                  <tr><td>Principiante</td><td>$160</td><td>$160</td></tr>
                  <tr><td>Intermedio</td><td>$140</td><td>$140</td></tr>
                  <tr><td>Avanzado</td><td>$120</td><td>$120</td></tr>
                </tbody>
              </table>
            </div>
            <ul class="course-feats">
              <li><i class="fas fa-check-circle"></i>Transmisión automática o estándar</li>
              <li><i class="fas fa-check-circle"></i>Conducción 100% enfocada en seguridad</li>
            </ul>
            <a href="#contacto" class="btn btn-gold w-100" data-curso="moto-estandar">Inscribirme</a>
          </div>
        </article>
      </div>

      <div class="col-lg-4 col-md-6">
        <article class="course-card reveal">
          <div class="course-media">
            <!-- TODO(foto): reemplazar por foto real de clases de refuerzo -->
            <img src="<?= BASE_URL ?>/assets/img/porque/vehiculos.jpg" alt="Clase práctica de manejo en vehículo de ECOSAN" loading="lazy">
            <span class="course-tag"><i class="fas fa-user-check"></i>Clase individual</span>
          </div>
          <div class="course-body">
            <div class="course-head">
              <div class="course-icon"><i class="fas fa-car-burst"></i></div>
              <div>
                <h4>Clases de Refuerzo</h4>
                <p>Para quienes ya manejan y quieren ganar confianza</p>
              </div>
            </div>
            <div class="course-price">$15 – $30 <small>/ clase</small></div>
            <div class="table-responsive">
              <table class="price-table">
                <thead><tr><th>Tipo</th><th>Automático</th><th>Estándar</th></tr></thead>
                <tbody>
                  <tr><td>Particular</td><td>$20</td><td>$15</td></tr>
                  <tr><td>Liviana</td><td>$30</td><td>$30</td></tr>
                </tbody>
              </table>
            </div>
            <ul class="course-feats">
              <li><i class="fas fa-check-circle"></i>Se cobran por clase individual</li>
              <li><i class="fas fa-check-circle"></i>Agenda el número de clases que necesites</li>
            </ul>
            <a href="#contacto" class="btn btn-gold w-100" data-curso="refuerzo-particular">Inscribirme</a>
          </div>
        </article>
      </div>

      <div class="col-lg-4 col-md-6">
        <article class="course-card reveal">
          <div class="course-media">
            <!-- TODO(foto): reemplazar por foto real de capacitaciones empresariales -->
            <img src="<?= BASE_URL ?>/assets/img/porque/instructores.jpg" alt="Instructora de ECOSAN en capacitación empresarial" loading="lazy">
            <span class="course-tag"><i class="fas fa-briefcase"></i>A la medida</span>
          </div>
          <div class="course-body">
            <div class="course-head">
              <div class="course-icon"><i class="fas fa-building"></i></div>
              <div>
                <h4>Planes para Empresas</h4>
                <p>Capacitación de personal para tu negocio</p>
              </div>
            </div>
            <div class="course-price">Cotización <small>personalizada</small></div>
            <ul class="course-feats">
              <li><i class="fas fa-check-circle"></i>Descuentos por volumen</li>
              <li><i class="fas fa-check-circle"></i>Factura para empresa</li>
              <li><i class="fas fa-check-circle"></i>Reportes de avance por colaborador</li>
            </ul>
            <a href="#contacto" class="btn btn-gold w-100" data-curso="empresas">Cotizar</a>
          </div>
        </article>
      </div>

      <div class="col-lg-4 col-md-6">
        <article class="course-card course-card--accent reveal">
          <div class="course-body d-flex flex-column h-100">
            <div class="advisory-icon"><i class="fas fa-circle-question"></i></div>
            <h4 class="advisory-title">¿No sabes cuál elegir?</h4>
            <p class="advisory-text">Cuéntanos qué necesitas —licencia, categoría o número de colaboradores— y te asesoramos sin compromiso.</p>
            <a class="btn btn-gold w-100 mt-auto" href="https://wa.me/50375288584?text=Hola%20ECOSAN%2C%20quiero%20asesor%C3%ADa%20para%20elegir%20un%20curso" target="_blank" rel="noopener">
              <i class="fab fa-whatsapp me-2"></i>Asesoría por WhatsApp
            </a>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>

<!-- ============ BENEFICIOS (slider tipo blog) ============ -->
<section class="section section-band why-slider-section" id="beneficios">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title reveal">¿Por qué <span>ECOSAN</span>?</h2>
      <p class="section-sub reveal">Una escuela autorizada, con experiencia comprobada y resultados reales</p>
      <div class="title-line reveal"></div>
    </div>

    <div class="why-slider-wrap reveal">
      <div class="blog-slider swiper" aria-label="Razones para elegir ECOSAN">
        <div class="swiper-wrapper">
          <?php
          $beneficios = [
            ['instructores.jpg', 'Equipo VMT', 'Instructores certificados', 'Personal capacitado y autorizado por el VMT.', 'Instructora certificada de ECOSAN'],
            ['vehiculos.jpg', 'Flota', 'Vehículos modernos', 'Flota en óptimas condiciones y totalmente asegurada.', 'Vehículo moderno de la flota ECOSAN'],
            ['horarios.jpg', 'Flexibilidad', 'Horarios flexibles', 'Clases en la mañana, tarde y sábados.', 'Horarios de atención de ECOSAN'],
            ['teoria-moderna.jpg', 'Atención', 'Atención personalizada', 'Acompañamiento individual en cada clase.', 'Instructor acompañando a un alumno'],
            ['certificacion.jpg', 'Resultados', '97% de aprobación', 'Nuestros alumnos aprueban el examen del VMT.', 'Alumno aprobando el examen del VMT'],
          ];

          foreach ($beneficios as $i => $razon):
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
                <i class="fas fa-calendar-check me-2"></i>Inscribirme
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

<!-- ============ PROCESO DE INSCRIPCIÓN (4 pasos) ============ -->
<section class="section" id="proceso">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title reveal">Inscríbete en <span>4 pasos</span></h2>
      <p class="section-sub reveal">Un proceso rápido y seguro de inicio a fin</p>
      <div class="title-line reveal"></div>
    </div>

    <ol class="steps-grid">
      <li class="step-card reveal">
        <span class="step-num">1</span>
        <h3>Selecciona tu curso</h3>
        <p>Elige la categoría y nivel que mejor se adapte a tus objetivos.</p>
      </li>
      <li class="step-card reveal">
        <span class="step-num">2</span>
        <h3>Completa el formulario</h3>
        <p>Ingresa tus datos, horario deseado y curso de interés en minutos.</p>
      </li>
      <li class="step-card reveal">
        <span class="step-num">3</span>
        <h3>Realiza tu pago</h3>
        <p>Con tarjeta, Wompi, Pagadito o transferencia bancaria. Encriptación segura.</p>
      </li>
      <li class="step-card reveal">
        <span class="step-num">4</span>
        <h3>Confirmación automática</h3>
        <p>Recibes tu comprobante y agenda por correo al instante.</p>
      </li>
    </ol>

    <ul class="trust-strip reveal" aria-label="Garantías del proceso">
      <li><i class="fas fa-shield-halved"></i>Datos protegidos con encriptación SSL</li>
      <li><i class="fas fa-lock"></i>Pago seguro en línea</li>
      <li><i class="fas fa-envelope-circle-check"></i>Confirmación inmediata por correo</li>
    </ul>
  </div>
</section>

<!-- ============ GALERÍA ============ -->
<section class="section section-band" id="galeria">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title reveal">Conoce nuestras <span>instalaciones</span></h2>
      <p class="section-sub reveal">Flota, instructores y espacios pensados para aprender con confianza</p>
      <div class="title-line reveal"></div>
    </div>

    <!-- TODO(foto): reemplazar todas las fotografías por imágenes reales de la escuela -->
    <div class="gallery-grid">
      <figure class="gallery-item reveal"><img src="<?= BASE_URL ?>/assets/img/porque/vehiculos.jpg" alt="Instalaciones: flota de vehículos de ECOSAN" loading="lazy"><figcaption>Nuestra flota</figcaption></figure>
      <figure class="gallery-item reveal"><img src="<?= BASE_URL ?>/assets/img/porque/instructores.jpg" alt="Instructores certificados de ECOSAN" loading="lazy"><figcaption>Instructores</figcaption></figure>
      <figure class="gallery-item reveal"><img src="<?= BASE_URL ?>/assets/img/porque/teoria-moderna.jpg" alt="Instalaciones del aula de ECOSAN" loading="lazy"><figcaption>Instalaciones</figcaption></figure>
      <figure class="gallery-item reveal"><img src="<?= BASE_URL ?>/assets/img/sedan.jpg" alt="Prácticas de manejo en la escuela" loading="lazy"><figcaption>Prácticas</figcaption></figure>
      <figure class="gallery-item reveal"><img src="<?= BASE_URL ?>/assets/img/microbus.jpg" alt="Circuito de manejo de ECOSAN" loading="lazy"><figcaption>Circuito de manejo</figcaption></figure>
      <figure class="gallery-item reveal"><img src="<?= BASE_URL ?>/assets/img/porque/certificacion.jpg" alt="Simulador y examen de certificación" loading="lazy"><figcaption>Simulador</figcaption></figure>
    </div>
    <p class="gallery-note">Fotografías de referencia — reemplazar por imágenes reales de ECOSAN antes de publicar.</p>
  </div>
</section>

<!-- ============ TESTIMONIOS ============ -->
<!-- Testimonios tomados del sitio público de ECOSAN; confirmar vigencia y permiso del cliente antes de publicar -->
<section class="section" id="testimonios">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title reveal">Lo que dicen <span>nuestros alumnos</span></h2>
      <p class="section-sub reveal">Miles de salvadoreños ya obtuvieron su licencia con nosotros</p>
      <div class="title-line reveal"></div>
    </div>
    <div class="row g-4">
      <?php
      $testis = [
        ['María Rodríguez','Curso Automático · Santa Ana','Aprobé a la primera. Los instructores son súper pacientes y el curso automático me sirvió muchísimo. ¡Recomendados!'],
        ['José Castro','Reforzamiento · Santa Ana','Tenía mucho miedo de manejar en ciudad. Con el curso de reforzamiento agarré confianza en pocas clases.'],
        ['Ana Lima','Plan Empresas · Logística','Capacitamos a nuestro equipo de repartidores. Excelente atención, factura y reportes puntuales. Muy profesionales.'],
      ];
      foreach ($testis as $t):
        [$nom,$rol,$txt] = $t;
        $partes = preg_split('/\s+/', trim($nom));
        $ini = strtoupper(substr($partes[0], 0, 1) . (count($partes) > 1 ? substr(end($partes), 0, 1) : ''));
      ?>
      <div class="col-lg-4 col-md-6">
        <div class="testi-card reveal">
          <i class="fas fa-quote-right testi-quote"></i>
          <div class="testi-stars">
            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
          </div>
          <p><?= htmlspecialchars($txt) ?></p>
          <div class="testi-user">
            <div class="testi-avatar"><?= $ini ?></div>
            <div>
              <div class="testi-name"><?= htmlspecialchars($nom) ?></div>
              <div class="testi-role"><?= htmlspecialchars($rol) ?></div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ RECURSOS PARA ALUMNOS ============ -->
<!-- Enlaces a material público de ECOSAN (sitio oficial y YouTube). Verificar antes de publicar. -->
<section class="section section-band" id="recursos">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title reveal">Recursos para <span>alumnos</span></h2>
      <p class="section-sub reveal">Manuales, guías y videos de apoyo para tu preparación</p>
      <div class="title-line reveal"></div>
    </div>

    <div class="resource-grid">
      <a class="resource-card reveal" href="https://autoescuelaecosan.com/recursos/guia-estudio-examen-teorico.pdf" target="_blank" rel="noopener">
        <span class="resource-type"><i class="fas fa-file-pdf"></i>PDF</span>
        <h3>Guía de Estudio Examen Teórico</h3>
        <p>Material de preparación para el examen teórico del VMT con todos los temas oficiales.</p>
        <span class="resource-link">Descargar <i class="fas fa-arrow-right"></i></span>
      </a>
      <a class="resource-card reveal" href="https://autoescuelaecosan.com/recursos/manual-escuela-ecosan-2026.pdf" target="_blank" rel="noopener">
        <span class="resource-type"><i class="fas fa-file-pdf"></i>PDF</span>
        <h3>Manual Escuela ECOSAN 2026</h3>
        <p>Manual oficial de la escuela con contenido teórico y práctico actualizado.</p>
        <span class="resource-link">Descargar <i class="fas fa-arrow-right"></i></span>
      </a>
      <a class="resource-card reveal" href="https://examen-teorico-ecosan-vmt.netlify.app/" target="_blank" rel="noopener">
        <span class="resource-type"><i class="fas fa-mobile-screen-button"></i>App Web</span>
        <h3>Simulador de Examen VMT</h3>
        <p>Más de 386 preguntas oficiales en 5 módulos. Practica antes de tu examen.</p>
        <span class="resource-link">Abrir simulador <i class="fas fa-arrow-right"></i></span>
      </a>
      <a class="resource-card reveal" href="https://youtu.be/omS7nWiPf34" target="_blank" rel="noopener">
        <span class="resource-type"><i class="fas fa-circle-play"></i>Video</span>
        <h3>Técnicas de Conducción Defensiva</h3>
        <p>Las técnicas esenciales para anticipar riesgos y conducir de forma segura.</p>
        <span class="resource-link">Ver video <i class="fas fa-arrow-right"></i></span>
      </a>
      <a class="resource-card reveal" href="https://www.youtube.com/watch?v=m5jNEwQndCs" target="_blank" rel="noopener">
        <span class="resource-type"><i class="fas fa-circle-play"></i>Video</span>
        <h3>Conducción Defensiva</h3>
        <p>Principios fundamentales de la conducción defensiva, explicados con ejemplos.</p>
        <span class="resource-link">Ver video <i class="fas fa-arrow-right"></i></span>
      </a>
      <a class="resource-card reveal" href="https://youtu.be/WfFnI_NaWps" target="_blank" rel="noopener">
        <span class="resource-type"><i class="fas fa-circle-play"></i>Video</span>
        <h3>Manejo Defensivo</h3>
        <p>Guía visual para prevenir accidentes con técnicas de manejo defensivo.</p>
        <span class="resource-link">Ver video <i class="fas fa-arrow-right"></i></span>
      </a>
      <a class="resource-card reveal" href="https://youtu.be/BfKc960X7dk" target="_blank" rel="noopener">
        <span class="resource-type"><i class="fas fa-circle-play"></i>Video</span>
        <h3>Normas y Señales de Tránsito</h3>
        <p>Aprende a circular con seguridad conociendo todas las señales y normas viales.</p>
        <span class="resource-link">Ver video <i class="fas fa-arrow-right"></i></span>
      </a>
      <a class="resource-card reveal" href="https://youtu.be/2fXvZgwJvSE" target="_blank" rel="noopener">
        <span class="resource-type"><i class="fas fa-circle-play"></i>Video</span>
        <h3>Prevención de Consumo de Drogas al Volante</h3>
        <p>Concientización sobre los riesgos del consumo de sustancias al conducir.</p>
        <span class="resource-link">Ver video <i class="fas fa-arrow-right"></i></span>
      </a>
    </div>
  </div>
</section>

<!-- ============ PREGUNTAS FRECUENTES ============ -->
<section class="section" id="preguntas">
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
          ['faq1', true,  '¿Qué requisitos necesito para inscribirme?', 'Para tomar el curso no necesitas más que el deseo de superar tus metas — no hay requisitos previos. Para tramitar tu licencia ante el VMT: si tienes 18 años o más, DUI original y copia al 150%; si tienes entre 15 y 17 años, carné de menoridad y partida de nacimiento. Desde los 15 años puedes obtener la Licencia Juvenil Vehículo o la Licencia Juvenil Motocicleta.'],
          ['faq2', false, '¿Cuánto dura cada curso?', 'La duración depende del nivel y de las horas semanales que elijas según tu horario: Principiante, 31 clases; Intermedio, 25 clases; Avanzado, 17 clases. Un curso puede completarse desde 1 semana (horario intensivo diario) hasta 6 semanas (por ejemplo, si eliges solo los sábados). Tú decides el ritmo según tu disponibilidad.'],
          ['faq3', false, '¿Los vehículos son automáticos o estándar?', 'Contamos con vehículos de transmisión estándar y automática, para que practiques según el curso que elijas.'],
          ['faq4', false, '¿Puedo pagar en línea?', 'Sí. Aceptamos tarjetas de crédito y débito mediante una pasarela de pago segura, además de plataformas locales como Wompi y Pagadito, cuotas y puntos Banco Agrícola, y transferencia bancaria.'],
          ['faq5', false, '¿ECOSAN está autorizada por el VMT?', 'Sí. Somos una escuela de manejo y examinadora autorizada por el Viceministerio de Transporte de El Salvador, con registro vigente.'],
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

<!-- ============ CTA CONTACTO / INSCRIPCIÓN ============ -->
<section class="section cta-parallax" id="contacto">
  <div class="cta-parallax-bg" aria-hidden="true"></div>
  <div class="container">
    <div class="cta-inner reveal">
      <div class="cta-grid">
        <div>
          <span class="cta-kicker"><i class="fas fa-route"></i> Tu camino al volante empieza aquí</span>
          <h2 class="section-title">¿Listo para <span>conducir</span>?</h2>
          <p class="cta-lead">Completa el formulario y un asesor confirmará tu cupo y horario. Tus datos están protegidos y no recibirás spam.</p>
          <ul class="cta-info">
            <li><i class="fas fa-phone"></i>2440-4057</li>
            <li><i class="fas fa-clock"></i>Lun–Vie 8:00–18:00 · Sáb 8:00–12:00</li>
            <li><i class="fas fa-location-dot"></i>6a Av. Sur y 21 Calle Pte., Barrio Nuevo, Santa Ana</li>
            <li><i class="fas fa-envelope"></i>autoescuelaecosan@gmail.com</li>
          </ul>
          <div class="cta-btns cta-btns-left">
            <a href="https://wa.me/50375288584?text=Hola%20ECOSAN%2C%20quiero%20inscribirme%20en%20un%20curso" target="_blank" rel="noopener" class="btn btn-gold"><i class="fab fa-whatsapp me-2"></i>WhatsApp 7528-8584</a>
            <a href="mailto:autoescuelaecosan@gmail.com" class="btn btn-white"><i class="fas fa-envelope me-2"></i>Correo</a>
          </div>
        </div>

        <form class="contact-form-card" id="formContacto" novalidate>
          <h3>Inscripción en línea</h3>
          <p class="contact-form-intro">Completa tus datos y un asesor confirmará tu cupo y horario.</p>

          <div class="contact-form-grid">
            <div class="form-field">
              <label for="f-nombre">Nombre completo *</label>
              <input type="text" class="form-control" id="f-nombre" name="nombre"
                     placeholder="Ej. María Rodríguez" autocomplete="name" required>
            </div>

            <div class="form-field">
              <label for="f-telefono">Teléfono / WhatsApp *</label>
              <input type="tel" class="form-control" id="f-telefono" name="telefono"
                     placeholder="Ej. 7528-8584" autocomplete="tel" inputmode="tel" required>
            </div>

            <div class="form-field">
              <label for="f-curso">Curso de interés *</label>
              <select class="form-select" id="f-curso" name="curso" required>
                <option value="particular-estandar">Particular Estándar</option>
                <option value="particular-automatico" selected>Particular Automático</option>
                <option value="liviano">Liviano</option>
                <option value="moto-estandar">Motocicleta Estándar</option>
                <option value="moto-automatico">Motocicleta Automático</option>
                <option value="refuerzo-particular">Refuerzo — Particular</option>
                <option value="refuerzo-liviana">Refuerzo — Liviana</option>
                <option value="empresas">Planes para Empresas</option>
              </select>
            </div>

            <div class="form-field">
              <label for="f-nivel">Nivel</label>
              <select class="form-select" id="f-nivel" name="nivel">
                <option value="Principiante" selected>Principiante</option>
                <option value="Intermedio">Intermedio</option>
                <option value="Avanzado">Avanzado</option>
              </select>
            </div>

            <div class="form-field form-field-full">
              <label for="f-horario">Horario deseado *</label>
              <select class="form-select" id="f-horario" name="horario" required>
                <option value="Lun–Vie 8:00 – 9:30">Lun–Vie 8:00am – 9:30am</option>
                <option value="Lun–Vie 9:30 – 11:00">Lun–Vie 9:30am – 11:00am</option>
                <option value="Lun–Vie 11:00 – 11:45">Lun–Vie 11:00am – 11:45am</option>
                <option value="Lun–Vie 13:00 – 14:30">Lun–Vie 1:00pm – 2:30pm</option>
                <option value="Lun–Vie 14:30 – 16:00">Lun–Vie 2:30pm – 4:00pm</option>
                <option value="Lun–Vie 16:00 – 16:45">Lun–Vie 4:00pm – 4:45pm</option>
                <option value="Sábado 8:00 – 12:00">Sábado 8:00am – 12:00md</option>
                <option value="Horario rotativo">Horario rotativo (coordinar con asesor)</option>
              </select>
            </div>

            <div class="form-field form-field-full">
              <label for="f-mensaje">Mensaje <span class="fw-normal text-secondary">(opcional)</span></label>
              <textarea class="form-control" id="f-mensaje" name="mensaje" rows="2"
                        placeholder="Escribe tu mensaje aquí..."></textarea>
            </div>
          </div>

          <p class="price-estimate" aria-live="polite">Precio estimado: <strong id="precioEstimado">—</strong></p>

          <button type="submit" class="btn btn-gold">
            <i class="fab fa-whatsapp me-2"></i>Enviar solicitud por WhatsApp
          </button>
          <p class="form-note"><i class="fab fa-whatsapp"></i> Se abrirá WhatsApp con tu solicitud. Sin spam.</p>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- ============ REDES SOCIALES Y UBICACIÓN ============ -->
<section class="section social-section" id="siguenos">
  <div class="container">
    <div class="title-center mb-5">
      <h2 class="section-title reveal">Síguenos y <span>visítanos</span></h2>
      <p class="section-sub reveal">Conecta con nosotros o llega a nuestro centro en Santa Ana</p>
      <div class="title-line reveal"></div>
    </div>

    <?php
    $redesSociales = [
      ['fab fa-whatsapp', 'WhatsApp', '7528-8584', 'https://wa.me/50375288584', true],
      ['fas fa-phone', 'Teléfono', '2440-4057', 'tel:+50324404057', false],
      ['fas fa-envelope', 'Correo electrónico', 'autoescuelaecosan@gmail.com', 'mailto:autoescuelaecosan@gmail.com', false],
      ['fab fa-facebook-f', 'Facebook', 'Autoescuela ECOSAN', 'https://www.facebook.com/autoescuela.ecosan/', true],
      ['fas fa-mobile-screen-button', 'Simulador VMT', 'examen-teorico-ecosan-vmt.netlify.app', 'https://examen-teorico-ecosan-vmt.netlify.app/', true],
    ];

    $direccionCorta = '6a Av. Sur y 21 Calle Pte., Barrio Nuevo, Santa Ana';
    $direccionMapa = $direccionCorta . ', El Salvador';
    $mapaEmbed = 'https://www.google.com/maps?q=' . rawurlencode($direccionMapa) . '&output=embed';
    $mapaRuta = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($direccionMapa);
    ?>

    <div class="social-layout">
      <div class="social-channels reveal">
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

      <div class="map-card reveal">
        <div class="map-frame">
          <iframe
            src="<?= $mapaEmbed ?>"
            title="Ubicación de ECOSAN Escuela de Manejo"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen></iframe>
        </div>
        <div class="map-info">
          <div>
            <span class="map-kicker"><i class="fas fa-location-dot"></i> Nuestra ubicación</span>
            <h3><?= $direccionCorta ?></h3>
            <p><i class="fas fa-clock me-2"></i>Lun–Vie 8:00–18:00 · Sáb 8:00–12:00</p>
          </div>
          <a class="btn btn-gold" href="<?= $mapaRuta ?>" target="_blank" rel="noopener">
            <i class="fas fa-diamond-turn-right me-2"></i>Cómo llegar
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
