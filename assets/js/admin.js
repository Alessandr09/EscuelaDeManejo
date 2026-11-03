/* ============================================================
   ESDRIVE - admin.js
   Sidebar colapsable + fechas con Tempo + Chart.js + CKEditor
   ============================================================ */

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
        setTimeout(function () {
          if (window.chartAlumnos) window.chartAlumnos.resize();
          if (window.chartLicencias) window.chartLicencias.resize();
        }, 80);
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

  /* ---------- GRAFICAS CHART.js ---------- */
  if (typeof Chart !== 'undefined') {
    Chart.defaults.color = '#aab4c8';
    Chart.defaults.font.family = "'Inter', sans-serif";

    var g1 = document.getElementById('graficaAlumnos');
    if (g1) {
      window.chartAlumnos = new Chart(g1, {
        type: 'line',
        data: {
          labels: ['May','Jun','Jul','Ago','Sep','Oct'],
          datasets: [{
            label: 'Alumnos inscritos',
            data: [42, 58, 51, 73, 88, 128],
            borderColor: '#e9a537',
            backgroundColor: 'rgba(233,165,55,0.15)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#ffd76a',
            pointBorderColor: '#ffd76a',
            pointRadius: 5,
            borderWidth: 3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.06)' } },
            x: { grid: { display: false } }
          }
        }
      });
    }

    var g2 = document.getElementById('graficaLicencias');
    if (g2) {
      window.chartLicencias = new Chart(g2, {
        type: 'doughnut',
        data: {
          labels: ['Clase A','Clase B','Clase C','Renovacion'],
          datasets: [{
            data: [28, 46, 18, 8],
            backgroundColor: ['#e9a537','#7c9cf0','#5fd689','#b98cf0'],
            borderColor: 'rgba(20,28,46,0.9)',
            borderWidth: 3,
            hoverOffset: 8
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '62%',
          plugins: {
            legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } }
          }
        }
      });
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