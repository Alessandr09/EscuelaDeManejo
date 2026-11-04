<?php require_once __DIR__ . '/../../../php/base.php'; ?>
<div class="app-bg"></div>

<nav class="navbar navbar-expand-lg nav-esdrive">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
      <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="Logo <?= APP_NOMBRE ?>">
      <span class="navbar-brand-text">
        <span class="nav-brand-name">ES<span>DRIVE</span></span><br>
        <span class="nav-brand-sub">Escuela de Manejo</span>
      </span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPrincipal"
            aria-controls="navPrincipal" aria-expanded="false" aria-label="Alternar navegacion">
      <i class="fas fa-bars"></i>
    </button>

    <div class="collapse navbar-collapse" id="navPrincipal">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
        <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>/index.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="#cursos">Cursos</a></li>
        <li class="nav-item"><a class="nav-link" href="#porque">Nosotros</a></li>
        <li class="nav-item"><a class="nav-link" href="#testimonios">Testimonios</a></li>
        <li class="nav-item"><a class="nav-link" href="#preguntas">Preguntas</a></li>
        <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
      </ul>

      <div class="d-flex align-items-center gap-2 ms-lg-3 mt-3 mt-lg-0">
        <button id="btnToggleTheme" title="Cambiar tema" aria-label="Cambiar tema">
          <i id="iconTheme" class="fas fa-moon"></i>
        </button>
        <a href="#contacto" class="btn btn-gold nav-cta"><i class="fas fa-calendar-check me-2"></i>Agenda tu clase</a>
      </div>
    </div>
  </div>
</nav>

<script>
/* Navbar: agrega sombra al hacer scroll */
(function () {
  var nav = document.querySelector('.nav-esdrive');
  if (!nav) return;
  function onScroll() {
    nav.classList.toggle('nav-scrolled', window.scrollY > 30);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();
</script>
