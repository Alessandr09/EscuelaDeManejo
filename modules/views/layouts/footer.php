<?php require_once __DIR__ . '/../../../php/base.php'; require_once __DIR__ . '/../../../php/cfg.php';
$redesFoot = esdrive_redes_links();
$esExterno = function (string $u): bool { return $u !== '#siguenos' && $u !== ''; };
?>
<footer class="footer-glass">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <div class="d-flex align-items-center gap-2 mb-3">
          <img src="<?= htmlspecialchars(esdrive_logo()) ?>" alt="Logo" width="38" height="38">
          <span class="nav-brand-name">ES<span>DRIVE</span></span>
        </div>
        <p>Formamos conductores responsables y seguros. Licencias de las clases A, B y C con instructores certificados y vehiculos modernos.</p>
        <div class="footer-social mt-3">
          <a href="<?= htmlspecialchars($redesFoot['facebook']) ?>" <?= $esExterno($redesFoot['facebook']) ? 'target="_blank" rel="noopener"' : '' ?> aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="<?= htmlspecialchars($redesFoot['instagram']) ?>" <?= $esExterno($redesFoot['instagram']) ? 'target="_blank" rel="noopener"' : '' ?> aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="<?= htmlspecialchars($redesFoot['tiktok']) ?>" <?= $esExterno($redesFoot['tiktok']) ? 'target="_blank" rel="noopener"' : '' ?> aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
          <a href="<?= htmlspecialchars($redesFoot['whatsapp']) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
        </div>
      </div>

      <div class="col-lg-2 col-md-6 col-6">
        <h6>Navegacion</h6>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>/index.php">Inicio</a></li>
          <li><a href="#cursos">Cursos</a></li>
          <li><a href="#porque">Nosotros</a></li>
          <li><a href="#equipo">Equipo</a></li>
          <li><a href="#testimonios">Testimonios</a></li>
          <li><a href="#siguenos">Síguenos</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6 col-6">
        <h6>Servicios</h6>
        <ul class="footer-links">
          <li><a href="#cursos">Clase A - Motocicleta</a></li>
          <li><a href="#cursos">Clase B - Particular</a></li>
          <li><a href="#cursos">Clase C - Carga</a></li>
          <li><a href="#cursos">Renovacion</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6">
        <h6>Contacto</h6>
        <ul class="footer-links">
          <li><i class="fas fa-location-dot me-2" style="color:var(--gold)"></i>Av. Principal #123, San Salvador</li>
          <li><i class="fas fa-phone me-2" style="color:var(--gold)"></i>+503 2222-3333</li>
          <li><i class="fas fa-envelope me-2" style="color:var(--gold)"></i>contacto@esdrive.com</li>
          <li><i class="fas fa-clock me-2" style="color:var(--gold)"></i>Lun a Sab: 7:00 - 18:00</li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom d-flex flex-wrap justify-content-between align-items-center">
      <span>&copy; <?= date('Y') ?> <?= APP_NOMBRE ?> — <?= APP_TAGLINE ?>. Todos los derechos reservados.</span>
      <span>Fue hecho por <strong style="color:var(--gold)">Diego Romero</strong> y <strong style="color:var(--gold)">Juan Henriquez</strong></span>
    </div>
  </div>
</footer>