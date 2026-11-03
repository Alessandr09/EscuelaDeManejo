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
    // Recalcular graficas si existen (ApexCharts)
    if (window.chartAlumnos) window.chartAlumnos.resize();
    if (window.chartLicencias) window.chartLicencias.resize();
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
