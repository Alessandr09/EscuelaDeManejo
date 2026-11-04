/* ============================================================
   ESDRIVE - site.js (sitio publico)
   Efectos parallax + formulario de contacto (WhatsApp)
   ============================================================ */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- FORMULARIO DE CONTACTO -> WhatsApp ---------- */
  var formContacto = document.getElementById('formContacto');
  if (formContacto) {
    formContacto.addEventListener('submit', function (ev) {
      ev.preventDefault();
      var nombre = formContacto.elements['nombre'].value.trim();
      var telefono = formContacto.elements['telefono'].value.trim();
      var curso = formContacto.elements['curso'].value;
      if (!nombre || !telefono) {
        formContacto.classList.add('was-validated');
        return;
      }
      var mensaje = 'Hola ESDRIVE, soy ' + nombre +
        ' (tel: ' + telefono + '). Quiero agendar una clase muestra gratis para: ' + curso + '.';
      window.open('https://wa.me/50370000000?text=' + encodeURIComponent(mensaje), '_blank', 'noopener');
    });
  }

  if (reduceMotion) return;

  /* ---------- PARALLAX ---------- */
  var hero = document.querySelector('.hero-wrap');
  var heroGrid = hero ? hero.querySelector('.hero-grid') : null;
  var cta = document.querySelector('.cta-parallax');
  var ctaBg = cta ? cta.querySelector('.cta-parallax-bg') : null;
  var ticking = false;

  function update() {
    ticking = false;
    var y = window.scrollY || window.pageYOffset || 0;
    var vh = window.innerHeight;

    // Hero: la imagen avanza mas lento que el contenido
    if (hero && y < vh * 1.5) {
      hero.style.setProperty('--hero-py', (y * 0.25) + 'px');
      if (heroGrid) {
        var f = Math.min(1, y / (vh * 0.75));
        heroGrid.style.transform = 'translate3d(0,' + (-y * 0.12) + 'px,0)';
        heroGrid.style.opacity = String(Math.max(0, 1 - f));
      }
    }

    // CTA: la capa de imagen se desplaza dentro de su seccion
    if (cta && ctaBg) {
      var r = cta.getBoundingClientRect();
      if (r.bottom > 0 && r.top < vh) {
        var prog = (vh - r.top) / (vh + r.height); // 0..1
        ctaBg.style.transform = 'translate3d(0,' + ((prog - 0.5) * 90).toFixed(1) + 'px,0)';
      }
    }
  }

  function onScroll() {
    if (!ticking) {
      ticking = true;
      window.requestAnimationFrame(update);
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll, { passive: true });
  update();
})();
