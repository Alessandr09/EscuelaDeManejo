/* =====================================================================
   Tecnicas Contables - control del tema claro/oscuro
   El tema se aplica en <html data-theme="light|dark"> y se guarda en
   localStorage para recordarlo entre visitas.
   ===================================================================== */
(function () {
    'use strict';

    var RAIZ   = document.documentElement;
    var CLAVE  = 'tecnicas_contables_theme';
    var BOTON  = document.getElementById('themeToggle');

    function aplicar(tema) {
        RAIZ.setAttribute('data-theme', tema);
        try {
            localStorage.setItem(CLAVE, tema);
        } catch (e) {
            /* Si localStorage esta bloqueado la preferencia no se guardara. */
        }
        if (BOTON) {
            BOTON.setAttribute(
                'title',
                tema === 'dark' ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro'
            );
        }
    }

    if (BOTON) {
        BOTON.addEventListener('click', function () {
            var actual = RAIZ.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            aplicar(actual === 'dark' ? 'light' : 'dark');
        });
    }

    /* Sigue al sistema mientras el visitante no elija manualmente. */
    var consulta = window.matchMedia('(prefers-color-scheme: dark)');

    var alCambiarSistema = function (evento) {
        var guardado = null;
        try {
            guardado = localStorage.getItem(CLAVE);
        } catch (e) {
            return;
        }
        if (!guardado) {
            aplicar(evento.matches ? 'dark' : 'light');
        }
    };

    if (consulta.addEventListener) {
        consulta.addEventListener('change', alCambiarSistema);
    } else if (consulta.addListener) {
        consulta.addListener(alCambiarSistema);
    }

    /* -------------------------------------------------------------------
       Menu lateral en pantallas pequeñas
       ------------------------------------------------------------------- */
    var barra    = document.getElementById('barraLateral');
    var botonAbr = document.getElementById('abrirMenu');
    var fondo    = document.getElementById('fondoMenu');

    function cerrarMenu() {
        if (barra) {
            barra.classList.remove('abierto');
        }
        if (fondo) {
            fondo.hidden = true;
        }
    }

    function alternarMenu() {
        if (!barra) {
            return;
        }
        var abierto = barra.classList.toggle('abierto');
        if (fondo) {
            fondo.hidden = !abierto;
        }
    }

    if (botonAbr) {
        botonAbr.addEventListener('click', alternarMenu);
    }

    if (fondo) {
        fondo.addEventListener('click', cerrarMenu);
    }

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrarMenu();
        }
    });

    /* Al cambiar a escritorio no debe quedarse el fondo abierto. */
    var ancho = window.matchMedia('(min-width: 992px)');
    var alCambiarAncho = function (evento) {
        if (evento.matches) {
            cerrarMenu();
        }
    };

    if (ancho.addEventListener) {
        ancho.addEventListener('change', alCambiarAncho);
    } else if (ancho.addListener) {
        ancho.addListener(alCambiarAncho);
    }
})();