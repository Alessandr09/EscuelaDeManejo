/* ============================================================
   ESDRIVE - admin.js
   Sidebar colapsable + fechas con Tempo + Chart.js + CKEditor
   ============================================================ */

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

      // Fecha larga en espa├▒ol en la topbar
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
  }
});

/* ============================================================
   EXAMENES: la vista de examenes del panel muestra los resultados
   de los alumnos (tabla examen_alumnos). No hay modal de edicion.
   ============================================================ */

/* ============================================================
   TABLAS: busqueda + filtro + paginacion
   ------------------------------------------------------------
   Sustituye al filtrado manual que cada vista tenia escrito por
   su cuenta. La vista solo declara que tabla usa y que campos la
   filtran:

     esdriveTabla({
       tabla: 'tablaCuentas',
       buscar: 'buscarCuenta',
       filtros: [{ id: 'filtroEstadoCuenta', attr: 'data-estado' }]
     });

   El filtro se aplica sobre TODAS las filas y despues se pagina,
   por lo que la busqueda siempre encuentra resultados que estan
   en cualquier pagina.
   ============================================================ */
window.esdriveTabla = function (cfg) {
  cfg = cfg || {};
  var tabla = document.getElementById(cfg.tabla);
  if (!tabla) { return null; }
  var cuerpo = tabla.querySelector('tbody');
  if (!cuerpo) { return null; }

  var barra = document.querySelector('.paginacion[data-tabla="' + cfg.tabla + '"]');
  var info = barra ? barra.querySelector('.pag-info') : null;
  var controles = barra ? barra.querySelector('.pag-controles') : null;
  var porPagina = parseInt(cfg.porPagina || (barra && barra.getAttribute('data-por-pagina')) || 5, 10);
  if (!(porPagina > 0)) { porPagina = 5; }
  var pagina = 1;

  var buscarEl = cfg.buscar ? document.getElementById(cfg.buscar) : null;
  var filtros = [];
  (cfg.filtros || []).forEach(function (f) {
    var el = document.getElementById(f.id);
    if (el) { filtros.push({ el: el, attr: f.attr || 'data-estado' }); }
  });

  /* Fila de aviso cuando el filtro no deja nada (se crea una sola vez) */
  var aviso = null;

  function filasDatos() {
    return Array.prototype.slice.call(cuerpo.querySelectorAll('tr')).filter(function (tr) {
      return !tr.classList.contains('fila-vacia');
    });
  }

  function coincide(tr) {
    var txt = (buscarEl && buscarEl.value || '').toLowerCase().trim();
    if (txt && tr.textContent.toLowerCase().indexOf(txt) === -1) { return false; }
    for (var i = 0; i < filtros.length; i++) {
      var v = filtros[i].el.value || '';
      if (v && tr.getAttribute(filtros[i].attr) !== v) { return false; }
    }
    return true;
  }

  /* Numeros de pagina a mostrar, con ... cuando hay muchas */
  function numerosPagina(actual, total) {
    var out = [];
    var i;
    if (total <= 7) {
      for (i = 1; i <= total; i++) { out.push(i); }
      return out;
    }
    out.push(1);
    var ini = Math.max(2, actual - 1);
    var fin = Math.min(total - 1, actual + 1);
    if (ini > 2) { out.push('...'); }
    for (i = ini; i <= fin; i++) { out.push(i); }
    if (fin < total - 1) { out.push('...'); }
    out.push(total);
    return out;
  }

  function dibujarControles(actual, total) {
    if (!controles) { return; }
    var html = '<button type="button" class="pag-btn" data-pag="prev" title="Pagina anterior"'
      + (actual <= 1 ? ' disabled' : '') + '><i class="fas fa-chevron-left"></i></button>';
    numerosPagina(actual, total).forEach(function (n) {
      if (n === '...') {
        html += '<span class="pag-ellipsis">&hellip;</span>';
      } else {
        html += '<button type="button" class="pag-btn' + (n === actual ? ' is-active' : '')
          + '" data-pag="' + n + '">' + n + '</button>';
      }
    });
    html += '<button type="button" class="pag-btn" data-pag="next" title="Pagina siguiente"'
      + (actual >= total ? ' disabled' : '') + '><i class="fas fa-chevron-right"></i></button>';
    controles.innerHTML = html;
  }

  function pintar() {
    var todas = filasDatos();
    var visibles = todas.filter(coincide);

    /* Aviso de "sin coincidencias" */
    if (!visibles.length && todas.length) {
      if (!aviso) {
        aviso = document.createElement('tr');
        aviso.className = 'fila-vacia';
        var cols = todas[0].children.length || 1;
        aviso.innerHTML = '<td colspan="' + cols + '" class="text-center" style="padding:24px;color:var(--text-mut)">'
          + '<i class="fas fa-search me-2"></i>No se encontraron resultados.</td>';
        cuerpo.appendChild(aviso);
      }
      aviso.hidden = false;
    } else if (aviso) {
      aviso.hidden = true;
    }

    var total = visibles.length;
    var paginas = Math.max(1, Math.ceil(total / porPagina));
    if (pagina > paginas) { pagina = paginas; }
    if (pagina < 1) { pagina = 1; }

    var ini = (pagina - 1) * porPagina;
    var fin = Math.min(ini + porPagina, total);

    todas.forEach(function (tr) { tr.style.display = 'none'; });
    visibles.slice(ini, fin).forEach(function (tr) { tr.style.display = ''; });

    if (barra) {
      /* La barra siempre esta visible: con una sola pagina las flechas quedan deshabilitadas */
      barra.hidden = false;
      if (info) {
        info.textContent = total
          ? 'Mostrando ' + (ini + 1) + '-' + fin + ' de ' + total
          : 'Sin registros';
      }
      dibujarControles(pagina, paginas);
    }
    return { total: total, pagina: pagina, paginas: paginas };
  }

  if (buscarEl) {
    buscarEl.addEventListener('input', function () { pagina = 1; pintar(); });
  }
  filtros.forEach(function (f) {
    f.el.addEventListener('change', function () { pagina = 1; pintar(); });
  });

  if (controles) {
    controles.addEventListener('click', function (ev) {
      var btn = ev.target.closest('.pag-btn');
      if (!btn || btn.disabled) { return; }
      var accion = btn.getAttribute('data-pag');
      if (accion === 'prev') { pagina--; }
      else if (accion === 'next') { pagina++; }
      else { pagina = parseInt(accion, 10); }
      pintar();
    });
  }

  pintar();
  return { pintar: pintar, ir: function (p) { pagina = p; pintar(); } };
};

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

/* ============================================================
   TOASTS de avisos (SweetAlert2, arriba a la derecha)
   ------------------------------------------------------------
   PHP deja un nodo oculto con data-flash-toast (ver esdrive_flash
   en php/ui.php) y aqui se convierte en toast. Tambien se puede
   lanzar a mano desde cualquier vista:

     esdriveToast('Cambios guardados', 'Se actualizo el curso.');
   ============================================================ */
window.esdriveToast = function (titulo, texto, icono) {
  if (typeof Swal === 'undefined') { return; }
  var estilos = getComputedStyle(document.documentElement);
  var fondo = estilos.getPropertyValue('--surface').trim() || '#1a2338';
  var color = estilos.getPropertyValue('--text').trim() || '#eef2f8';
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon: icono || 'success',
    title: titulo || 'Listo',
    text: texto || '',
    showConfirmButton: false,
    timer: 3200,
    timerProgressBar: true,
    background: fondo,
    color: color,
    didOpen: function (el) {
      el.addEventListener('mouseenter', Swal.stopTimer);
      el.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });
};

(function () {
  function lanzarToasts() {
    document.querySelectorAll('[data-flash-toast]').forEach(function (nodo) {
      window.esdriveToast(
        nodo.getAttribute('data-titulo'),
        nodo.getAttribute('data-texto'),
        nodo.getAttribute('data-icono')
      );
      nodo.remove();
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', lanzarToasts);
  } else {
    lanzarToasts();
  }
})();

/* ============================================================
   DROPZONE para subir imagenes dentro de un formulario normal
   ------------------------------------------------------------
   La vista deja un contenedor y un <input type="file"> oculto con
   el name que espera PHP; aqui se copia el archivo elegido a ese
   input para que viaje con el submit del formulario.

     esdriveDropzone({
       dropzone: 'dzImagenEmp',
       input: 'inputImagenEmp',
       previewWrap: 'previewWrapEmp',
       previewImg: 'previewImgEmp'
     });
   ============================================================ */
window.esdriveDropzone = function (cfg) {
  cfg = cfg || {};
  var cont = document.getElementById(cfg.dropzone);
  var input = document.getElementById(cfg.input);
  if (!cont || !input || typeof Dropzone === 'undefined') { return null; }
  Dropzone.autoDiscover = false;

  var wrap = cfg.previewWrap ? document.getElementById(cfg.previewWrap) : null;
  var img = cfg.previewImg ? document.getElementById(cfg.previewImg) : null;

  function sincronizar() {
    try {
      var dt = new DataTransfer();
      dz.files.forEach(function (f) { dt.items.add(f); });
      input.files = dt.files;
    } catch (e) {}
  }

  var dz = new Dropzone(cont, {
    url: '#',
    autoProcessQueue: false,
    uploadMultiple: false,
    maxFiles: 1,
    maxFilesize: 5,
    acceptedFiles: 'image/*',
    addRemoveLinks: true,
    dictRemoveFile: 'Quitar imagen',
    dictCancelUpload: 'Cancelar',
    dictDefaultMessage: '<i class="fas fa-cloud-arrow-up"></i><span>Arrastra la imagen aqui o haz clic para elegirla</span>',
    dictFallbackMessage: 'Tu navegador no permite arrastrar archivos.',
    dictFileTooBig: 'La imagen supera los 5 MB.',
    dictInvalidFileType: 'Solo se permiten imagenes (JPG, PNG, WEBP o GIF).',
    init: function () {
      var self = this;

      self.on('maxfilesexceeded', function (file) {
        self.removeAllFiles();
        self.addFile(file);
      });

      self.on('addedfile', function (file) {
        setTimeout(sincronizar, 0);
        if (wrap && img) {
          img.src = URL.createObjectURL(file);
          wrap.classList.remove('d-none');
        }
      });

      self.on('removedfile', function () {
        setTimeout(sincronizar, 0);
        if (!self.files.length) {
          if (wrap) wrap.classList.add('d-none');
          if (img) img.removeAttribute('src');
        }
      });
    }
  });

  return dz;
};