<?php require_once __DIR__ . '/../../../php/base.php'; ?>
<div class="app-bg"></div>

<nav class="navbar navbar-expand-lg navbar-dark nav-esdrive">
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

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Servicios
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#cursos"><i class="fas fa-car-side me-2"></i>Clase B (Particular)</a></li>
            <li><a class="dropdown-item" href="#cursos"><i class="fas fa-motorcycle me-2"></i>Clase A (Motocicleta)</a></li>
            <li><a class="dropdown-item" href="#cursos"><i class="fas fa-truck me-2"></i>Clase C (Carga)</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#cursos"><i class="fas fa-id-card me-2"></i>Renovacion de licencia</a></li>
          </ul>
        </li>
      </ul>
    </div>

    <button id="btnToggleTheme" style="background:none;border:none;color:var(--text-mut);cursor:pointer;font-size:1.15rem;padding:6px 10px;margin-left:10px;" title="Cambiar tema" aria-label="Cambiar tema">
      <i id="iconTheme" class="fas fa-sun"></i>
    </button>
  </div>
</nav>

<script>
/* Navbar: transparente sobre el hero, solido al hacer scroll */
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