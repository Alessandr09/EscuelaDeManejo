<?php require_once __DIR__ . '/../../../php/base.php'; ?>
<div class="page-head d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <h3>Hola, <span>Admin</span> 👋</h3>
    <p>Aqui tienes el resumen de ESDRIVE de hoy.</p>
  </div>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <!-- Pill switcher: alterna entre graficas y ultimos registros -->
    <ul class="nav dash-pills" id="dashPills">
      <li class="nav-item">
        <button type="button" class="nav-link active" data-panel="panelGraficas">
          <i class="fas fa-chart-line me-2"></i>Graficas
        </button>
      </li>
      <li class="nav-item">
        <button type="button" class="nav-link" data-panel="panelRegistros">
          <i class="fas fa-user-clock me-2"></i>Ultimos registros
        </button>
      </li>
    </ul>
    <button class="btn btn-glass btn-sm"><i class="fas fa-download me-2"></i>Exportar</button>
    <button class="btn btn-gold btn-sm"><i class="fas fa-plus me-2"></i>Nueva cuenta</button>
  </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-3">
  <?php
  $kpis = [
    ['fa-users','128','Cuentas activas','+12% este mes','up'],
    ['fa-file-signature','342','Examenes aplicados','+8%','up'],
    ['fa-circle-check','91%','Aprobacion','-2%','down'],
    ['fa-dollar-sign','$4,820','Ingresos del mes','+15%','up'],
  ];
  foreach ($kpis as $k):
    [$icon,$val,$lbl,$trend,$dirT] = $k;
  ?>
  <div class="col-xl-3 col-md-6">
    <div class="glass kpi-card">
      <div class="kpi-icon"><i class="fas <?= $icon ?>"></i></div>
      <div class="kpi-info">
        <div class="kpi-val"><?= $val ?></div>
        <div class="kpi-lbl"><?= $lbl ?></div>
        <div class="kpi-trend <?= $dirT ?>"><i class="fas fa-arrow-<?= $dirT === 'up' ? 'up' : 'down' ?>"></i> <?= $trend ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ===== Panel: Graficas ===== -->
<div id="panelGraficas" class="dash-panel">
  <div class="row g-3">
    <!-- Grafica de linea -->
    <div class="col-lg-8">
      <div class="glass panel panel-flex">
        <div class="panel-title"><i class="fas fa-chart-line"></i>Alumnos inscritos (ultimos 6 meses)</div>
        <div class="chart-wrap"><canvas id="graficaAlumnos"></canvas></div>
      </div>
    </div>

    <!-- Distribucion de licencias -->
    <div class="col-lg-4">
      <div class="glass panel panel-flex">
        <div class="panel-title"><i class="fas fa-chart-pie"></i>Licencias por clase</div>
        <div class="chart-wrap chart-wrap-donut"><canvas id="graficaLicencias"></canvas></div>
      </div>
    </div>
  </div>
</div>

<!-- ===== Panel: Ultimos registros ===== -->
<div id="panelRegistros" class="dash-panel d-none">
  <div class="glass panel panel-flex">
    <div class="panel-title"><i class="fas fa-user-clock"></i>Ultimos registros</div>
    <div class="table-responsive">
      <table class="table table-glass">
        <thead>
          <tr><th>Alumno</th><th>Curso</th><th>Fecha</th><th>Estado</th><th class="text-end">Acciones</th></tr>
        </thead>
        <tbody>
          <?php
          $regs = [
            ['Maria Lopez','maria@email.com','Clase B','2026-10-24','Activo','a-active','ML','#ffd76a'],
            ['Carlos Ramirez','carlos@email.com','Clase A','2026-10-23','Pendiente','a-pend','CR','#a3b6f5'],
            ['Ana Martinez','ana@email.com','Clase C','2026-10-22','Activo','a-active','AM','#5fd689'],
            ['Jose Hernandez','jose@email.com','Clase B','2026-10-21','Inactivo','a-off','JH','#f07276'],
            ['Laura Diaz','laura@email.com','Renovacion','2026-10-20','Activo','a-active','LD','#ffd76a'],
          ];
          foreach ($regs as $r):
            [$nom,$mail,$curso,$f,$est,$cls,$ini,$col] = $r;
          ?>
          <tr>
            <td>
              <div class="cell-user">
                <div class="cell-avatar" style="background:<?= $col ?>"><?= $ini ?></div>
                <div><div class="cu-name"><?= $nom ?></div><div class="cu-mail"><?= $mail ?></div></div>
              </div>
            </td>
            <td><?= $curso ?></td>
            <td data-fecha="<?= $f ?>"></td>
            <td><span class="badge-soft <?= str_replace('a-','b-',$cls) ?>"><?= $est ?></span></td>
            <td class="text-end">
              <a href="#" class="btn-act"><i class="fas fa-eye"></i></a>
              <a href="#" class="btn-act edit"><i class="fas fa-pen"></i></a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
