/* ============================================================
   ESDRIVE - admin.js
   Sidebar colapsable + fechas con Tempo + Chart.js + CKEditor
   ============================================================ */

<<<<<<< HEAD
/* ============================================================
   GRAFICAS (ApexCharts) con soporte de tema claro/oscuro
   - esdriveChartDefs[id] = funcion(el, colores) => instancia
   - esdrivePaintCharts()  repinta todas con los colores actuales
   ============================================================ */
window.esdriveChartDefs = {};

window.esdriveColores = function () {
  var isLight = document.documentElement.classList.contains('light-mode');
  return {
    gold:     isLight ? '#d97706' : '#e9a537',
    goldSoft: isLight ? '#eab308' : '#ffd76a',
    indigo:   isLight ? '#3730a3' : '#4353a0',
    green:    isLight ? '#15803d' : '#5fd689',
    purple:   isLight ? '#7e22ce' : '#b98cf0',
    red:      isLight ? '#b91c1c' : '#e5484d',
    textMut:  isLight ? '#475569' : '#aab4c8',
    text:     isLight ? '#0f1729' : '#eef2f8',
    surface:  isLight ? '#ffffff' : '#1a2338',
    border:   isLight ? '#cbd5e1' : '#334155',
    theme:    isLight ? 'light' : 'dark'
  };
};

window.esdrivePaintCharts = function () {
  if (typeof ApexCharts === 'undefined') { return; }
  Object.keys(window.esdriveChartDefs).forEach(function (id) {
    var el = document.getElementById(id);
    if (!el) { return; }
    try { if (el.__apex && typeof el.__apex.destroy === 'function') el.__apex.destroy(); } catch (e) {}
    el.innerHTML = '';
    try {
      var c = window.esdriveChartDefs[id](el, window.esdriveColores());
      if (c && typeof c.render === 'function') { c.render(); el.__apex = c; }
    } catch (e) {}
  });
};

/* Redimensiona / repinta las graficas sin importar la libreria */
window.esdriveResizeCharts = function () {
  window.esdrivePaintCharts();
  try { window.dispatchEvent(new Event('resize')); } catch (e) {}
};

/* ---------- Constructores reutilizables ---------- */
window.esdriveDefLinea = function (etiquetas, valores, nombre) {
  return function (el, c) {
    return new ApexCharts(el, {
      chart: { type: 'line', height: 250, fontFamily: "'Inter', sans-serif", foreColor: c.textMut,
               toolbar: { show: false }, zoom: { enabled: false },
               animations: { enabled: true, animateGradually: { enabled: true, delay: 150 } } },
      series: [{ name: nombre, data: valores }],
      colors: [c.gold],
      stroke: { curve: 'smooth', width: 3, lineCap: 'round' },
      fill: { type: 'solid', opacity: 0.12 },
      markers: { size: 5, colors: [c.gold], strokeColor: c.surface, strokeWidth: 2, hover: { size: 7 } },
      xaxis: { categories: etiquetas, labels: { style: { colors: c.textMut, fontSize: '12px' } },
               axisBorder: { show: false }, axisTicks: { show: false }, crosshairs: { show: false } },
      yaxis: { labels: { style: { colors: c.textMut, fontSize: '12px' } }, min: 0, tickAmount: 5 },
      grid: { show: false, padding: { top: 10, right: 10, bottom: 20, left: 10 } },
      tooltip: { theme: c.theme, y: { formatter: function (v) { return v + ' ' + nombre; } } },
      responsive: [{ breakpoint: 480, options: { chart: { height: 240 } } }]
    });
  };
};

window.esdriveDefBarras = function (etiquetas, valores, nombre) {
  return function (el, c) {
    return new ApexCharts(el, {
      chart: { type: 'bar', height: 260, fontFamily: "'Inter', sans-serif", foreColor: c.textMut,
               toolbar: { show: false } },
      series: [{ name: nombre, data: valores }],
      colors: [c.gold],
      plotOptions: { bar: { borderRadius: 3, columnWidth: '55%', colors: { ranges: [] } } },
      dataLabels: { enabled: false },
      xaxis: { categories: etiquetas, labels: { style: { colors: c.textMut, fontSize: '11px' } },
               axisBorder: { show: false }, axisTicks: { show: false } },
      yaxis: { labels: { style: { colors: c.textMut, fontSize: '12px' } }, min: 0 },
      grid: { show: false, padding: { top: 10, right: 10, bottom: 10, left: 10 } },
      tooltip: { theme: c.theme, y: { formatter: function (v) { return v + ' ' + nombre; } } }
    });
  };
};

window.esdriveDefDonut = function (labels, series, nombre) {
  return function (el, c) {
    return new ApexCharts(el, {
      chart: { type: 'donut', height: 250, fontFamily: "'Inter', sans-serif", foreColor: c.textMut },
      series: series,
      labels: labels,
      colors: [c.gold, c.indigo, c.green, c.purple, c.red, c.goldSoft],
      stroke: { width: 2, colors: [c.surface] },
      dataLabels: {
        enabled: true,
        formatter: function (val, opt) {
          var total = opt.w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0);
          return total ? ((val / total) * 100).toFixed(0) + '%' : '0%';
        },
        style: { fontSize: '12px', fontWeight: 700, colors: [c.text] }
      },
      legend: { position: 'bottom', fontSize: '12px', labels: { colors: c.textMut },
                itemMargin: { horizontal: 10, vertical: 6 } },
      tooltip: { theme: c.theme },
      responsive: [{ breakpoint: 480, options: { chart: { height: 230 } } }]
    });
  };
};

/* ============================================================
   CUENTA REGRESIVA del carrusel de vehiculos (dashboard)
   - Mas de 1 hora: texto fijo (lo pinta PHP)
   - Menos de 1 hora: corre en vivo + barra de aviso
   - Ya paso: "En uso ahora" / "Libre ahora"
   ============================================================ */
window.esdriveCountdowns = function () {
  var nodos = document.querySelectorAll('.flota-horario[data-fecha]');
  if (!nodos.length) return;

  var ALARMA = 60 * 60 * 1000; // 1 hora

  function pintar() {
    var ahora = Date.now();
    nodos.forEach(function (n) {
      var texto = n.querySelector('.fh-texto');
      var barra = n.querySelector('.fh-progreso');
      var linea = n.querySelector('.fh-linea');
      if (!texto) return;

      var objetivo = new Date(n.getAttribute('data-fecha')).getTime();
      var fase = n.getAttribute('data-fase'); // 'uso' | 'libre'
      var falta = objetivo - ahora;

      n.classList.remove('aviso', 'critico', 'cumplido');

      if (fase === 'uso') {
        var base = 'Se usara';
        var fin  = 'En uso ahora';
        if (falta > ALARMA) {
          texto.textContent = base + ' a las ' + new Date(objetivo).toLocaleTimeString('es', { hour: '2-digit', minute: '2-digit' });
          if (barra) barra.style.width = '0%';
        } else if (falta > 0) {
          texto.textContent = base + ' en ' + reloj(falta);
          n.classList.add(falta <= 10 * 60 * 1000 ? 'critico' : 'aviso');
          if (barra) barra.style.width = Math.max(0, (falta / ALARMA) * 100) + '%';
        } else {
          texto.textContent = fin;
          n.classList.add('cumplido');
          if (barra) barra.style.width = '100%';
        }
      } else { // 'libre'
        var baseL = 'Vuelve a estar libre';
        if (falta > ALARMA) {
          texto.textContent = baseL + ' a las ' + new Date(objetivo).toLocaleTimeString('es', { hour: '2-digit', minute: '2-digit' });
          if (barra) barra.style.width = '0%';
        } else if (falta > 0) {
          texto.textContent = baseL + ' en ' + reloj(falta);
          n.classList.add(falta <= 10 * 60 * 1000 ? 'critico' : 'aviso');
          if (barra) barra.style.width = Math.max(0, (falta / ALARMA) * 100) + '%';
        } else {
          texto.textContent = 'Libre ahora';
          n.classList.add('cumplido');
          if (barra) barra.style.width = '100%';
        }
      }

      // icono segun estado
      var icono = linea ? linea.querySelector('i') : null;
      if (icono) icono.className = 'far fa-clock me-1';
    });
  }

  function reloj(ms) {
    var s = Math.max(0, Math.floor(ms / 1000));
    var h = Math.floor(s / 3600);
    var m = Math.floor((s % 3600) / 60);
    var seg = s % 60;
    var mm = (m < 10 ? '0' : '') + m;
    var ss = (seg < 10 ? '0' : '') + seg;
    return h > 0 ? h + ':' + mm + ':' + ss : mm + ':' + ss;
  }

  pintar();
  if (window._esdriveCd) clearInterval(window._esdriveCd);
  window._esdriveCd = setInterval(pintar, 1000);
};

document.addEventListener('DOMContentLoaded', function () {

  /* ---------- CUENTA REGRESIVA DE VEHICULOS ---------- */
  if (typeof window.esdriveCountdowns === 'function') window.esdriveCountdowns();

=======
document.addEventListener('DOMContentLoaded', function () {

>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
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
<<<<<<< HEAD
        setTimeout(function () { window.esdriveResizeCharts(); }, 80);
=======
        setTimeout(function () {
          if (window.chartAlumnos) window.chartAlumnos.resize();
          if (window.chartLicencias) window.chartLicencias.resize();
        }, 80);
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
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
<<<<<<< HEAD
    // Dashboard: alumnos inscritos por mes
    var g1 = document.getElementById('graficaAlumnos');
    var dAlumnos = window.__graficaAlumnos;
    if (g1 && dAlumnos) {
      window.esdriveChartDefs['graficaAlumnos'] =
        window.esdriveDefLinea(dAlumnos.etiquetas, dAlumnos.valores, 'alumnos');
    }

    // Reportes: el JSON lo genera reportes.php en window.__reportes
    var rep = window.__reportes;
    if (rep) {
      if (rep.alumnosMes && document.getElementById('graficaAlumnosMes')) {
        window.esdriveChartDefs['graficaAlumnosMes'] =
          window.esdriveDefLinea(rep.alumnosMes.etiquetas, rep.alumnosMes.valores, 'alumnos');
      }
      if (rep.alumnosCurso && document.getElementById('graficaAlumnosCurso')) {
        window.esdriveChartDefs['graficaAlumnosCurso'] =
          window.esdriveDefDonut(rep.alumnosCurso.labels, rep.alumnosCurso.series, 'alumnos');
      }
      if (rep.examenes && document.getElementById('graficaExamenes')) {
        window.esdriveChartDefs['graficaExamenes'] =
          window.esdriveDefDonut(rep.examenes.labels, rep.examenes.series, 'examenes');
      }
      if (rep.vehiculos && document.getElementById('graficaVehiculos')) {
        window.esdriveChartDefs['graficaVehiculos'] =
          window.esdriveDefDonut(rep.vehiculos.labels, rep.vehiculos.series, 'vehiculos');
      }
      if (rep.ingresos && document.getElementById('graficaIngresos')) {
        window.esdriveChartDefs['graficaIngresos'] =
          window.esdriveDefBarras(rep.ingresos.etiquetas, rep.ingresos.valores, 'usd');
      }
    }

    window.esdrivePaintCharts();
=======
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
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
  }

  /* ---------- CKEEDITOR (modal examenes) ---------- */
  window.ckExamen = null;
});

<<<<<<< HEAD
/* ============================================================
   EXAMENES: modal real con CKEditor
   ============================================================ */
function abrirModalExamen(id) {
  id = id || 0;
  var modalEl = document.getElementById('modalExamen');
  if (!modalEl) return;

  // Resetear el form
  var form = document.getElementById('formExamen');
  if (form) {
    form.reset();
    var hId = form.querySelector('input[name="id"]');
    if (hId) hId.value = id;
    var hDesc = form.querySelector('input[name="descripcion"]');
    if (hDesc) hDesc.value = '';
  }

  // Titulo del modal
  var titulo = modalEl.querySelector('.modal-title');
  if (titulo) titulo.innerHTML = id ? '<i class="fas fa-pen-nib me-2"></i>Editar examen' : '<i class="fas fa-plus me-2"></i>Nuevo examen';

  // Cargar datos si es edicion (los filtra el PHP en window.__examenes)
  if (id && window.__examenes && window.__examenes[id]) {
    var d = window.__examenes[id];
    var set = function (n, v) { var e = form.querySelector('[name="' + n + '"]'); if (e) e.value = v; };
    set('titulo', d.titulo); set('curso', d.curso); set('preguntas', d.preguntas);
    set('duracion', d.duracion); set('fecha', d.fecha || ''); set('estado', d.estado);
    if (hDesc) hDesc.value = d.descripcion || '';
  }

  var modal = new bootstrap.Modal(modalEl);
  modal.show();

  // CKEditor
  setTimeout(function () {
    var target = document.getElementById('editorExamen');
    if (!target) return;
    if (window.ckExamen) {
      window.ckExamen.setData(hDesc ? hDesc.value : '');
      return;
    }
    if (typeof ClassicEditor === 'undefined') { return; }
    ClassicEditor.create(target).then(function (editor) {
      window.ckExamen = editor;
      if (hDesc) editor.setData(hDesc.value);
    }).catch(function (err) { console.error('CKEditor:', err); });
  }, 350);
}

/* Sincroniza el editor antes de enviar el form */
function prepararExamen() {
  var hDesc = document.querySelector('#formExamen input[name="descripcion"]');
  if (hDesc && window.ckExamen) hDesc.value = window.ckExamen.getData();
  return true;
}

/* Confirmacion SweetAlert2 para eliminar (usada por todas las vistas):
   <button data-eliminar="ID" data-nombre="Texto"> ... <form id="formEliminarID"> */
document.addEventListener('click', function (e) {
  var btn = e.target.closest('[data-eliminar]');
  if (!btn) return;
  e.preventDefault();
  var id = btn.getAttribute('data-eliminar');
  var nombre = btn.getAttribute('data-nombre') || 'este registro';
  var que = btn.getAttribute('data-que') || 'registro';
  Swal.fire({
    icon: 'warning',
    title: '¿Eliminar ' + que + '?',
    text: 'Se borrara "' + nombre + '".',
    showCancelButton: true,
    confirmButtonText: 'Si, eliminar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#e5484d',
    cancelButtonColor: '#64748b'
  }).then(function (res) {
    if (res.isConfirmed) {
      var f = document.getElementById('formEliminar' + id);
      if (f) f.submit();
    }
  });
});
=======
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
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
