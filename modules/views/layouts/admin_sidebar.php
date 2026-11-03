<?php require_once __DIR__ . '/../../../php/base.php';
$vista = $vista ?? 'dashboard';
$link = function($v){ return BASE_URL . '/admin.php?v=' . $v; };
$activo = function($v) use ($vista){ return $vista === $v ? 'active' : ''; };
?>
<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="Logo">
    <div class="sb-text">
      <div class="sb-name">ES<span>DRIVE</span></div>
      <div class="sb-sub">Panel admin</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section">General</div>
    <a class="side-link <?= $activo('dashboard') ?>" href="<?= $link('dashboard') ?>">
      <i class="fas fa-gauge-high"></i><span>Dashboard</span>
    </a>
    <a class="side-link <?= $activo('cuentas') ?>" href="<?= $link('cuentas') ?>">
      <i class="fas fa-users"></i><span>Cuentas</span><span class="badge">128</span>
    </a>
    <a class="side-link <?= $activo('examenes') ?>" href="<?= $link('examenes') ?>">
      <i class="fas fa-file-signature"></i><span>Examenes</span><span class="badge">6</span>
    </a>

    <div class="nav-section">Academico</div>
    <a class="side-link" href="#"><i class="fas fa-car-side"></i><span>Cursos</span></a>
    <a class="side-link" href="#"><i class="fas fa-chalkboard-teacher"></i><span>Instructores</span></a>
    <a class="side-link" href="#"><i class="fas fa-calendar-check"></i><span>Horarios</span></a>

    <div class="nav-section">Sistema</div>
    <a class="side-link" href="#"><i class="fas fa-chart-pie"></i><span>Reportes</span></a>
    <a class="side-link" href="#"><i class="fas fa-gear"></i><span>Configuracion</span></a>
  </nav>

  <div class="sidebar-foot">
    <small><i class="fas fa-circle" style="color:#46c26e;font-size:.6rem"></i> Servidor en linea<br>ESDRIVE v1.0</small>
  </div>
</aside>