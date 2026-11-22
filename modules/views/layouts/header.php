<?php require_once __DIR__ . '/../../../php/base.php'; require_once __DIR__ . '/../../../php/cfg.php'; ?>
<div class="app-bg"></div>

<nav class="navbar navbar-expand-lg nav-esdrive">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
      <img src="<?= htmlspecialchars(esdrive_logo()) ?>" alt="Logo <?= APP_NOMBRE ?>">
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
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="#cursos">Cursos</a></li>
        <li class="nav-item"><a class="nav-link" href="#porque">Nosotros</a></li>
        <li class="nav-item"><a class="nav-link" href="#testimonios">Testimonios</a></li>
        <li class="nav-item"><a class="nav-link" href="#preguntas">Preguntas</a></li>
        <li class="nav-item"><a class="nav-link" href="#guias">Guías</a></li>
        <li class="nav-item"><a class="nav-link" href="#siguenos">Contacto</a></li>
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
/* Navbar: sombra al hacer scroll + enlace activo segun la seccion visible */
(function () {
  var nav = document.querySelector('.nav-esdrive');
  if (!nav) return;

  var links = Array.prototype.slice.call(nav.querySelectorAll('.nav-link[href]'));

  function onScroll() {
    nav.classList.toggle('nav-scrolled', window.scrollY > 30);

    // Margen por debajo del navbar fijo para considerar "alcanzada" una seccion.
    var linea = (nav.offsetHeight || 74) + 24;
    var actual = links[0] || null; // "Inicio" mientras no se alcance ninguna seccion

    links.forEach(function (link) {
      var href = link.getAttribute('href') || '';
      if (href.charAt(0) !== '#') return; // Inicio no apunta a una seccion
      var seccion = document.querySelector(href);
      if (seccion && seccion.getBoundingClientRect().top <= linea) actual = link;
    });

    // Al llegar al fondo, marcar el ultimo enlace (Contacto).
    // scrollY > 0 evita marcarlo al cargar, antes de que exista todo el contenido.
    if (window.scrollY > 0 && window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
      actual = links[links.length - 1] || actual;
    }

    links.forEach(function (link) {
      var esActual = link === actual;
      link.classList.toggle('active', esActual);
      if (esActual) link.setAttribute('aria-current', 'page');
      else link.removeAttribute('aria-current');
    });
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
  // El header se incluye antes del contenido: volver a calcular cuando el DOM este listo.
  document.addEventListener('DOMContentLoaded', onScroll);
  window.addEventListener('load', onScroll);
  onScroll();
})();
</script>
