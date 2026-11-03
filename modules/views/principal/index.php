<?php require_once __DIR__ . '/../../../php/base.php'; ?>

<!-- ============ HERO ============ -->
<section class="hero-wrap">
  <div class="container">
    <div class="hero-grid">
      <div class="hero-copy">
        <span class="hero-badge animate__animated animate__fadeInDown"><i class="fas fa-star"></i> +2,500 conductores formados</span>
        <h1 class="hero-title animate__animated animate__fadeInUp">Aprende a conducir con <em>confianza y seguridad</em></h1>
        <p class="hero-lead animate__animated animate__fadeInUp">Obten tu licencia de las clases A, B y C con instructores certificados, vehiculos modernos y un enfoque practico que si funciona.</p>
        <div class="hero-cta animate__animated animate__fadeInUp" style="animation-delay:.15s">
          <a href="#cursos" class="btn btn-gold"><i class="fas fa-steering-wheel me-2"></i>Ver cursos</a>
          <a href="#contacto" class="btn btn-glass"><i class="fas fa-paper-plane me-2"></i>Agenda tu clase</a>
        </div>
        <div class="hero-stats animate__animated animate__fadeInUp" style="animation-delay:.3s">
          <div class="hero-stat"><b>2,500+</b><small>Alumnos formados</small></div>
          <div class="hero-stat"><b>98%</b><small>Tasa de aprobacion</small></div>
          <div class="hero-stat"><b>12</b><small>Instructores</small></div>
        </div>
      </div>

      <div class="glass hero-card animate__animated animate__zoomIn">
        <div class="hero-card-row" style="margin-top:0;margin-bottom:18px">
          <div class="pill"><i class="fas fa-shield-halved" style="color:var(--gold)"></i><b>100%</b>Seguro</div>
          <div class="pill"><i class="fas fa-clock" style="color:var(--gold)"></i><b>Flexible</b>Horarios</div>
        </div>
        <i class="fas fa-car-side car"></i>
        <div class="hero-card-row">
          <div class="pill"><b>MOTOCICLETA</b>Consigue tu licencia para manejar motocicleta.</div>
          <div class="pill"><b>PARTICULAR</b>Maneja vehiculos sedanes, pickups y camionetas.</div>
          <div class="pill"><b>LIVIANA</b>Maneja vehiculos de hasta 2.5 toneladas y 15 pasajeros</div>
        </div>
      </div>
    </div>
  </div>
  <a class="hero-scroll" href="#cursos" aria-label="Ver cursos"><i class="fas fa-chevron-down"></i></a>
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

<!-- ============ CTA CONTACTO (parallax) ============ -->
<section class="section cta-parallax" id="contacto">
  <div class="cta-parallax-bg" aria-hidden="true"></div>
  <div class="container">
    <div class="cta-inner reveal">
      <span class="cta-kicker"><i class="fas fa-route"></i> Tu camino a la licencia empieza aqui</span>
      <h2 class="section-title">¿Listo para <span>conducir</span>?</h2>
      <p class="cta-lead">Agenda tu primera clase hoy y comienza el camino hacia tu licencia.</p>
      <div class="cta-btns">
        <a href="https://wa.me/50370000000" target="_blank" class="btn btn-gold"><i class="fab fa-whatsapp me-2"></i>WhatsApp</a>
        <a href="mailto:contacto@esdrive.com" class="btn btn-white"><i class="fas fa-envelope me-2"></i>Correo</a>
      </div>
      <ul class="cta-info">
        <li><i class="fas fa-phone"></i>+503 2222-3333</li>
        <li><i class="fas fa-clock"></i>Lun a Sab: 7:00 - 18:00</li>
        <li><i class="fas fa-location-dot"></i>Av. Principal #123, San Salvador</li>
      </ul>
    </div>
  </div>
</section>