/* ============================================================
   ESDRIVE - Toggle de tema claro / oscuro
   Guarda la preferencia en localStorage
   ============================================================ */

(function () {
  var html = document.documentElement;
  var btn  = document.getElementById('btnToggleTheme');
  var icon = document.getElementById('iconTheme');

  function applyTheme(isLight) {
    if (isLight) {
      html.classList.add('light-mode');
      if (icon) icon.className = 'fas fa-moon'; // icono para pasar a oscuro
      localStorage.setItem('esdrive-theme', 'light');
    } else {
      html.classList.remove('light-mode');
      if (icon) icon.className = 'fas fa-sun';  // icono para pasar a claro
      localStorage.setItem('esdrive-theme', 'dark');
    }
<<<<<<< HEAD
    // Recalcular graficas si existen (Chart.js o ApexCharts)
    if (typeof window.esdriveResizeCharts === 'function') {
      window.esdriveResizeCharts();
    }
=======
    // Recalcular graficas si existen (ApexCharts)
    if (window.chartAlumnos) window.chartAlumnos.resize();
    if (window.chartLicencias) window.chartLicencias.resize();
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
  }

  // Inicializar tema
  var saved = localStorage.getItem('esdrive-theme');
  var prefersDark = window.matchMedia &&
    window.matchMedia('(prefers-color-scheme: dark)').matches;
  var initialLight = saved ? saved === 'light' : !prefersDark;
  applyTheme(initialLight);

  // Evento del boton
  if (btn) {
    btn.addEventListener('click', function () {
      var isLight = !html.classList.contains('light-mode');
      applyTheme(isLight);
    });
  }
})();
