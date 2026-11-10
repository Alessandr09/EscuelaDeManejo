<?php require_once __DIR__ . '/../../../php/base.php';
<<<<<<< HEAD
require_once __DIR__ . '/../../../php/auth.php';
$vista = $vista ?? 'dashboard';
$link  = function ($v) { return BASE_URL . '/admin.php?v=' . $v; };
$activo = function ($v) use ($vista) { return $vista === $v ? 'active' : ''; };

// Cuenta real para las insignias
$badge = function ($tabla, $filtro = '') {
    try {
        $sql = "SELECT COUNT(*) FROM `$tabla`" . ($filtro ? " WHERE $filtro" : '');
        return (int) db()->query($sql)->fetchColumn();
    } catch (Exception $e) { return 0; }
};
$nCuentas  = $badge('alumnos');
$nExamenes = $badge('examenes', "estado <> 'Borrador'");
$nVehi     = $badge('vehiculos', "estado = 'disponible'");
$nEmp      = $badge('empleados');
$nCursos   = $badge('cursos', "estado = 'Activo'");
$nHorarios = $badge('horarios');
$nUsers    = $badge('usuarios', 'activo = 1');

/** Enlace con control de permiso */
$nav = function ($id, $icono, $texto, $badgeVal = '') use ($link, $activo) {
    if (!esdrive_puede($id, 'ver')) { return; }
    ?>
    <a class="side-link <?= $activo($id) ?>" href="<?= $link($id) ?>">
      <i class="fas <?= $icono ?>"></i><span><?= $texto ?></span>
      <?php if ($badgeVal !== '' && $badgeVal !== 0): ?><span class="badge"><?= (int) $badgeVal ?></span><?php endif; ?>
    </a>
    <?php
};
=======
$vista = $vista ?? 'dashboard';
$link = function($v){ return BASE_URL . '/admin.php?v=' . $v; };
$activo = function($v) use ($vista){ return $vista === $v ? 'active' : ''; };
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
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
<<<<<<< HEAD
    <?php
    $nav('dashboard',    'fa-gauge-high',      'Dashboard',    '');
    $nav('cuentas',      'fa-users',           'Cuentas',      $nCuentas);
    $nav('examenes',     'fa-file-signature',  'Examenes',     $nExamenes);
    $nav('vehiculos',    'fa-car-side',        'Vehiculos',    $nVehi);
    $nav('empleados',    'fa-id-badge',        'Empleados',    $nEmp);
    ?>

    <div class="nav-section">Academico</div>
    <?php
    $nav('cursos',       'fa-graduation-cap',  'Cursos',       $nCursos);
    $nav('instructores', 'fa-chalkboard-teacher', 'Instructores', $nEmp);
    $nav('horarios',     'fa-calendar-check',  'Horarios',     $nHorarios);
    ?>

    <div class="nav-section">Sistema</div>
    <?php
    $nav('reportes',     'fa-chart-pie',       'Reportes',     '');
    $nav('usuarios',     'fa-user-shield',     'Usuarios',     $nUsers);
    $nav('configuracion','fa-gear',            'Configuracion', '');
    ?>
=======
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
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
  </nav>

  <div class="sidebar-foot">
    <small><i class="fas fa-circle" style="color:#46c26e;font-size:.6rem"></i> Servidor en linea<br>ESDRIVE v1.0</small>
  </div>
<<<<<<< HEAD
</aside>
=======
</aside>
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
