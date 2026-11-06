<?php require_once __DIR__ . '/../../../php/base.php'; ?>
<footer class="footer-glass">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <div class="d-flex align-items-center gap-2 mb-3">
          <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="Logo <?= APP_NOMBRE ?>" width="38" height="38">
          <span class="nav-brand-name">ECO<span>SAN</span></span>
        </div>
        <p>Escuela de Manejo y Examinadora autorizada por el VMT. Formamos conductores responsables en Santa Ana, El Salvador.</p>
        <div class="footer-social mt-3">
          <a href="https://www.facebook.com/autoescuela.ecosan/" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="https://wa.me/50375288584" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
          <a href="tel:+50324404057" aria-label="Teléfono"><i class="fas fa-phone"></i></a>
          <a href="mailto:autoescuelaecosan@gmail.com" aria-label="Correo"><i class="fas fa-envelope"></i></a>
        </div>
      </div>

      <div class="col-lg-2 col-md-6 col-6">
        <h6>Navegación</h6>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>/index.php">Inicio</a></li>
          <li><a href="#cursos">Cursos</a></li>
          <li><a href="#beneficios">Beneficios</a></li>
          <li><a href="#proceso">Proceso</a></li>
          <li><a href="#preguntas">Preguntas</a></li>
          <li><a href="#contacto">Contacto</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6 col-6">
        <h6>Cursos</h6>
        <ul class="footer-links">
          <li><a href="#cursos">Motocicleta</a></li>
          <li><a href="#cursos">Particular Estándar</a></li>
          <li><a href="#cursos">Particular Automático</a></li>
          <li><a href="#cursos">Liviano</a></li>
          <li><a href="#cursos">Clases de refuerzo</a></li>
          <li><a href="#cursos">Planes para empresas</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6">
        <h6>Contacto</h6>
        <ul class="footer-links">
          <li><i class="fas fa-location-dot me-2" style="color:var(--gold)"></i>6a Av. Sur y 21 Calle Pte., Barrio Nuevo, Santa Ana</li>
          <li><i class="fas fa-phone me-2" style="color:var(--gold)"></i>2440-4057</li>
          <li><i class="fas fa-whatsapp me-2" style="color:var(--gold)"></i>7528-8584</li>
          <li><i class="fas fa-envelope me-2" style="color:var(--gold)"></i>autoescuelaecosan@gmail.com</li>
          <li><i class="fas fa-clock me-2" style="color:var(--gold)"></i>Lun–Vie 8:00–18:00 · Sáb 8:00–12:00</li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
      <span>&copy; <?= date('Y') ?> <?= APP_NOMBRE ?> — <?= APP_TAGLINE ?>. Autorizada por el VMT.</span>
      <span>
        <a href="https://autoescuelaecosan.com/politicas-de-privacidad" target="_blank" rel="noopener">Políticas de privacidad</a>
        ·
        <a href="https://autoescuelaecosan.com/terminos-y-condiciones" target="_blank" rel="noopener">Términos y condiciones</a>
      </span>
    </div>
  </div>
</footer>
