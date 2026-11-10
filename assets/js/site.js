/* ============================================================
   ESDRIVE - site.js (sitio publico)
   Efectos parallax + formulario de contacto (WhatsApp)
   ============================================================ */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- CKEditor en el campo mensaje ---------- */
  var editorMensaje = null;
  var textareaMensaje = document.getElementById('f-mensaje');
  if (textareaMensaje && window.ClassicEditor) {
    window.ClassicEditor
      .create(textareaMensaje, {
        toolbar: ['bold', 'italic', 'underline', 'link', 'bulletedList', 'numberedList']
      })
      .then(function (editor) { editorMensaje = editor; })
      .catch(function () { /* sin CKEditor: queda el textarea normal */ });
  }

  function htmlATexto(html) {
    return String(html)
      .replace(/<br\s*\/?>/gi, '\n')
      .replace(/<\/(p|li|h[1-6]|div|blockquote)>/gi, '\n')
      .replace(/<li[^>]*>/gi, '- ')
      .replace(/<[^>]*>/g, '')
      .replace(/&nbsp;/gi, ' ')
      .replace(/&amp;/gi, '&')
      .replace(/&lt;/gi, '<')
      .replace(/&gt;/gi, '>')
      .replace(/&quot;/gi, '"')
      .replace(/&#39;/gi, "'")
      .replace(/\n{3,}/g, '\n\n')
      .trim();
  }

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
      var mensajeTexto = editorMensaje
        ? htmlATexto(editorMensaje.getData())
        : (textareaMensaje ? textareaMensaje.value.trim() : '');
      var mensaje = 'Hola ESDRIVE, soy ' + nombre +
        ' (tel: ' + telefono + '). Quiero agendar una clase muestra gratis para: ' + curso + '.';
      if (mensajeTexto) {
        // WhatsApp no soporta HTML: se envia el texto plano (max. 500 caracteres)
        var extra = mensajeTexto.length > 500 ? mensajeTexto.substring(0, 500) + '…' : mensajeTexto;
        mensaje += '\n\nMensaje:\n' + extra;
      }
      window.open('https://wa.me/50370000000?text=' + encodeURIComponent(mensaje), '_blank', 'noopener');
    });
  }

  /* ---------- CARRUSEL DE EQUIPO ---------- */
  (function initTeamCarousel() {
    var track = document.querySelector('.team-track');
    if (!track) return;

    var cards = Array.prototype.slice.call(track.querySelectorAll('.team-card'));
    var dots = Array.prototype.slice.call(document.querySelectorAll('.team-dot'));
    var memberName = document.querySelector('.team-member-name');
    var memberRole = document.querySelector('.team-member-role');
    var leftArrow = document.querySelector('.team-arrow.left');
    var rightArrow = document.querySelector('.team-arrow.right');
    if (!cards.length || !memberName || !memberRole || !leftArrow || !rightArrow) return;

    var total = cards.length;
    var currentIndex = 0;
    var isAnimating = false;

    function updateMemberInfo() {
      var activeCard = cards[currentIndex];
      memberName.textContent = activeCard.getAttribute('data-name') || '';
      memberRole.textContent = activeCard.getAttribute('data-role') || '';
    }

    function updateCarousel(newIndex, instant) {
      if (isAnimating) return;
      currentIndex = (newIndex + total) % total;

      cards.forEach(function (card, i) {
        var offset = (i - currentIndex + total) % total;
        card.classList.remove('center', 'left-1', 'left-2', 'right-1', 'right-2', 'hidden');

        if (offset === 0) {
          card.classList.add('center');
        } else if (offset === 1) {
          card.classList.add('right-1');
        } else if (offset === 2) {
          card.classList.add('right-2');
        } else if (offset === total - 1) {
          card.classList.add('left-1');
        } else if (offset === total - 2) {
          card.classList.add('left-2');
        } else {
          card.classList.add('hidden');
        }
      });

      dots.forEach(function (dot, i) {
        var isActive = i === currentIndex;
        dot.classList.toggle('active', isActive);
        dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });

      if (instant) {
        updateMemberInfo();
        return;
      }

      isAnimating = true;
      memberName.style.opacity = '0';
      memberRole.style.opacity = '0';

      window.setTimeout(function () {
        updateMemberInfo();
        memberName.style.opacity = '1';
        memberRole.style.opacity = '1';
      }, 250);

      window.setTimeout(function () {
        isAnimating = false;
      }, 800);
    }

    leftArrow.addEventListener('click', function () {
      updateCarousel(currentIndex - 1);
    });

    rightArrow.addEventListener('click', function () {
      updateCarousel(currentIndex + 1);
    });

    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () {
        updateCarousel(i);
      });
    });

    cards.forEach(function (card, i) {
      card.addEventListener('click', function () {
        if (i !== currentIndex) updateCarousel(i);
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;

      var target = e.target;
      var tag = target && target.tagName ? target.tagName.toLowerCase() : '';
      if (tag === 'input' || tag === 'textarea' || tag === 'select' ||
          (target && target.isContentEditable)) return;

      var section = document.querySelector('.team-section');
      if (section) {
        var rect = section.getBoundingClientRect();
        if (rect.bottom < 0 || rect.top > window.innerHeight) return;
      }

      updateCarousel(currentIndex + (e.key === 'ArrowRight' ? 1 : -1));
    });

    var touchStartX = 0;
    track.addEventListener('touchstart', function (e) {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    track.addEventListener('touchend', function (e) {
      var diff = touchStartX - e.changedTouches[0].screenX;
      if (Math.abs(diff) > 50) {
        updateCarousel(currentIndex + (diff > 0 ? 1 : -1));
      }
    }, { passive: true });

    updateCarousel(0, true);
  })();

  /* ---------- SLIDER DE RAZONES (SWIPER) ---------- */
  (function initWhySlider() {
    var slider = document.querySelector('.blog-slider');
    if (!slider || !window.Swiper) return;

    new window.Swiper(slider, {
      effect: 'fade',
      fadeEffect: { crossFade: true },
      loop: true,
      speed: reduceMotion ? 0 : 600,
      grabCursor: true,
      pagination: {
        el: slider.querySelector('.blog-slider__pagination'),
        clickable: true
      },
      a11y: {
        enabled: true,
        paginationBulletMessage: 'Ir a la diapositiva {{index}}'
      }
    });
  })();

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
        ctaBg.style.transform = 'translate3d(0,' + ((prog - 0.5) * 170).toFixed(1) + 'px,0)';
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
