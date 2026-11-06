/* ============================================================
   ECOSAN - Toggle de tema claro / oscuro
   Oscuro via clase html.dark-mode (el tema claro es el default,
   adaptado al diseno Canva "Ruta Segura").
   Guarda la preferencia en localStorage
   ============================================================ */

(function () {
  var html = document.documentElement;
  var btn  = document.getElementById('btnToggleTheme');
  var icon = document.getElementById('iconTheme');

  function applyTheme(isDark) {
    if (isDark) {
      html.classList.add('dark-mode');
      if (icon) icon.className = 'fas fa-sun';  // icono para pasar a claro
      localStorage.setItem('ecosan-theme', 'dark');
    } else {
      html.classList.remove('dark-mode');
      if (icon) icon.className = 'fas fa-moon'; // icono para pasar a oscuro
      localStorage.setItem('ecosan-theme', 'light');
    }
    // Recalcular graficas si existen (ApexCharts)
    if (window.chartAlumnos) window.chartAlumnos.resize();
    if (window.chartLicencias) window.chartLicencias.resize();
  }

  // Inicializar tema: claro por defecto (diseno Canva), oscuro si se guardo o el SO lo prefiere
  var saved = localStorage.getItem('ecosan-theme');
  var prefersDark = window.matchMedia &&
    window.matchMedia('(prefers-color-scheme: dark)').matches;
  var initialDark = saved ? saved === 'dark' : prefersDark;
  applyTheme(initialDark);

  // Evento del boton
  if (btn) {
    btn.addEventListener('click', function () {
      var isDark = !html.classList.contains('dark-mode');
      applyTheme(isDark);
    });
  }
})();
