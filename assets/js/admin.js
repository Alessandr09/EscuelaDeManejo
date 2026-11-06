/* ============================================================
   ESDRIVE - admin.js
   Sidebar colapsable + fechas con Tempo + Chart.js + CKEditor
   ============================================================ */

/* Redimensiona las graficas del dashboard sin importar la libreria:
   Chart.js tiene .resize(); ApexCharts se redimensiona al disparar
   el evento resize de la ventana. */
window.esdriveResizeCharts = function () {
  ['chartAlumnos', 'chartLicencias'].forEach(function (key) {
    var c = window[key];
    if (c && typeof c.resize === 'function') c.resize();
  });
  try { window.dispatchEvent(new Event('resize')); } catch (e) {}
};

document.addEventListener('DOMContentLoaded', function () {

  /* ---------- SIDEBAR COLAPSABLE ---------- */
  var layout = document.getElementById('adminLayout');
  var toggle = document.getElementById('toggleSide');
  var scrim  = document.getElementById('scrim');

  if (toggle && layout) {
    toggle.addEventListener('click', function () {
      if (window.innerWidth <= 992) {
        layout.classList.toggle('mobile-open');   // en movil: drawer
      } else {
        layout.classList.toggle('sidebar-collapsed'); // en escritorio: recoger
      }
    });
  }
  if (scrim && layout) {
    scrim.addEventListener('click', function () { layout.classList.remove('mobile-open'); });
  }

  /* ---------- PILL SWITCHER (dashboard) ---------- */
  var pills = document.querySelectorAll('#dashPills [data-panel]');
  pills.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var targetId = btn.getAttribute('data-panel');
      pills.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      document.querySelectorAll('.dash-panel').forEach(function (p) {
        if (p.id !== targetId) {
          p.classList.add('d-none');
          p.classList.remove('animate__animated', 'animate__fadeIn');
        }
      });
      var target = document.getElementById(targetId);
      if (target) {
        target.classList.remove('d-none');
        target.classList.add('animate__animated', 'animate__fadeIn');
        // Recalcular graficas al volver a mostrarse (canvas que estado oculto)
        setTimeout(function () { window.esdriveResizeCharts(); }, 80);
      }
    });
  });

  /* ---------- FECHAS CON TEMPO ---------- */
  // Import dinamico del modulo ES de @formkit/tempo
  import('https://cdn.jsdelivr.net/npm/@formkit/tempo@1.1.0/dist/bundle.mjs')
    .then(function (Tempo) {
      var fmt = Tempo.format;

      // Fecha larga en español en la topbar
      var elFecha = document.getElementById('fechaActual');
      if (elFecha) {
        try {
          elFecha.textContent = fmt(new Date(), { date: 'full' }, 'es');
        } catch (e) {
          elFecha.textContent = new Date().toLocaleDateString('es');
        }
      }

      // Fecha del modal de examen
      var elFechEx = document.getElementById('fechaExamen');
      if (elFechEx) {
        try {
          elFechEx.textContent = fmt(new Date(), 'DD [de] MMMM [de] YYYY', 'es');
        } catch (e) {
          elFechEx.textContent = new Date().toLocaleDateString('es');
        }
      }

      // Fechas en tablas (atributo data-fecha)
      document.querySelectorAll('td[data-fecha]').forEach(function (td) {
        var iso = td.getAttribute('data-fecha');
        if (!iso) return;
        try {
          td.textContent = fmt(iso, 'DD MMM YYYY', 'es');
        } catch (e) {
          td.textContent = iso;
        }
      });
    })
    .catch(function () {
      // Fallback si el modulo no carga
      var elFecha = document.getElementById('fechaActual');
      if (elFecha) elFecha.textContent = new Date().toLocaleDateString('es');
      document.querySelectorAll('td[data-fecha]').forEach(function (td) {
        if (td.getAttribute('data-fecha')) td.textContent = td.getAttribute('data-fecha');
      });
    });

  /* ---------- GRAFICAS ApexCharts (flat, sin bordes, claro/oscuro) ---------- */
  if (typeof ApexCharts !== 'undefined') {
    var isLight = document.documentElement.classList.contains('light-mode');

    var gold    = isLight ? '#d97706' : '#e9a537';
    var goldSoft= isLight ? '#eab308' : '#ffd76a';
    var indigo  = isLight ? '#3730a3' : '#4353a0';
    var green   = isLight ? '#15803d' : '#5fd689';
    var purple  = isLight ? '#7e22ce' : '#b98cf0';
    var textMut = isLight ? '#475569' : '#aab4c8';
    var text    = isLight ? '#0f1729' : '#eef2f8';
    var surface = isLight ? '#ffffff' : '#1a2338';
    var border  = isLight ? '#cbd5e1' : '#334155';

    var apexTheme = isLight ? 'light' : 'dark';

    // --- Linea: alumnos inscritos (ultimos 6 meses) ---
    var g1 = document.getElementById('graficaAlumnos');
    if (g1) {
      window.chartAlumnos = new ApexCharts(g1, {
        chart: {
          type: 'line',
          height: 320,
          fontFamily: "'Inter', sans-serif",
          foreColor: textMut,
          toolbar: { show: false },
          zoom: { enabled: false },
          animations: { enabled: true, animateGradually: { enabled: true, delay: 150 } }
        },
        series: [{
          name: 'Alumnos inscritos',
          data: [42, 58, 51, 73, 88, 128]
        }],
        labels: ['May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct'],
        colors: [gold],
        stroke: {
          curve: 'smooth',
          width: 3,
          lineCap: 'round'
        },
        fill: {
          type: 'solid',
          opacity: 0.12
        },
        markers: {
          size: 5,
          colors: [gold],
          strokeColor: surface,
          strokeWidth: 2,
          hover: { size: 7 }
        },
        xaxis: {
          labels: { style: { colors: textMut, fontSize: '12px' } },
          axisBorder: { show: false },
          axisTicks: { show: false },
          crosshairs: { show: false }
        },
        yaxis: {
          labels: { style: { colors: textMut, fontSize: '12px' } },
          min: 0,
          tickAmount: 5
        },
        grid: {
          show: false,
          padding: { top: 10, right: 10, bottom: 20, left: 10 }
        },
        tooltip: {
          theme: apexTheme,
          x: { format: 'dd MMM' },
          y: { formatter: function (val) { return val + ' alumnos'; } }
        },
        responsive: [{
          breakpoint: 480,
          options: { chart: { height: 260 } }
        }]
      });
      window.chartAlumnos.render();
    }

    // --- Donut: distribucion de licencias por clase ---
    var g2 = document.getElementById('graficaLicencias');
    if (g2) {
      window.chartLicencias = new ApexCharts(g2, {
        chart: {
          type: 'donut',
          height: 320,
          fontFamily: "'Inter', sans-serif",
          foreColor: textMut
        },
        series: [28, 46, 18, 8],
        labels: ['Clase A', 'Clase B', 'Clase C', 'Renovaci\u00f3n'],
        colors: [gold, indigo, green, purple],
        stroke: {
          width: 2,
          colors: [surface]
        },
        dataLabels: {
          enabled: true,
          formatter: function (val, opt) {
            var total = opt.w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0);
            var pct = ((val / total) * 100).toFixed(1);
            return val + ' (' + pct + '%)';
          },
          style: { fontSize: '13px', fontWeight: 700, colors: [text] }
        },
        legend: {
          position: 'bottom',
          fontSize: '13px',
          labels: { colors: textMut },
          itemMargin: { horizontal: 14, vertical: 10 }
        },
        plotOptions: {
          pie: {
            donut: {
              size: '60%',
              labels: {
                show: true,
                total: {
                  show: true,
                  label: 'Total',
                  formatter: function () { return 100; },
                  style: { fontSize: '14px', fontWeight: 700, color: text }
                }
              }
            }
          }
        },
        tooltip: {
          theme: apexTheme,
          y: { formatter: function (val) { return val + ' alumnos'; } }
        },
        responsive: [{
          breakpoint: 480,
          options: { chart: { height: 260 } }
        }]
      });
      window.chartLicencias.render();
    }
  }

  /* ---------- CKEEDITOR (modal examenes) ---------- */
  window.ckExamen = null;
});

/* Inicializa CKEditor al abrir el modal (espera a que cargue el CDN) */
function abrirModalExamen() {
  var modalEl = document.getElementById('modalExamen');
  if (!modalEl) return;
  var modal = new bootstrap.Modal(modalEl);
  modal.show();

  setTimeout(function () {
    var target = document.getElementById('editorExamen');
    if (!target) return;
    if (window.ckExamen) return; // ya inicializado
    if (typeof ClassicEditor === 'undefined') {
      target.placeholder = 'CKEditor aun cargando...';
      return;
    }
    ClassicEditor.create(target).then(function (editor) {
      window.ckExamen = editor;
    }).catch(function (err) {
      console.error('CKEditor:', err);
    });
  }, 350);
}

function guardarExamen() {
  Swal.fire({
    icon: 'success',
    title: 'Examen guardado',
    text: 'El registro se guardo correctamente (demo).',
    confirmButtonColor: '#e9a537'
  });
}