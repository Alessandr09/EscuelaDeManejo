<?php require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/ui.php';

/* ============ DATOS REALES ============ */
$q = function ($sql, $args = []) {
    try {
        $st = db()->prepare($sql);
        $st->execute($args);
        return $st->fetchAll();
    } catch (Exception $e) { return []; }
};
$uno = function ($sql, $args = []) use ($q) {
    $filas = $q($sql, $args);
    return $filas ? array_values($filas[0])[0] : 0;
};

$alumnosTotal   = (int) $uno('SELECT COUNT(*) FROM alumnos');
$alumnosActivos = (int) $uno("SELECT COUNT(*) FROM alumnos WHERE estado = 'Activo'");
$examenesTotal  = (int) $uno("SELECT COUNT(*) FROM examenes WHERE estado <> 'Borrador'");
$examenesBorr   = (int) $uno("SELECT COUNT(*) FROM examenes WHERE estado = 'Borrador'");
$horariosTotal  = (int) $uno('SELECT COUNT(*) FROM horarios');
$empleadoTotal  = (int) $uno('SELECT COUNT(*) FROM empleados');

$totalFlota  = 0;
$disponibles = 0;
try {
    $totalFlota  = (int) db()->query('SELECT COUNT(*) FROM vehiculos')->fetchColumn();
    $disponibles = (int) db()->query("SELECT COUNT(*) FROM vehiculos WHERE estado = 'disponible'")->fetchColumn();
} catch (Exception $e) {}

// Ingresos estimados: alumnos activos por curso x precio del curso
$ingresos = 0;
try {
    $ingresos = (float) db()->query(
        "SELECT COALESCE(SUM(c.precio),0) FROM alumnos a
         JOIN cursos c ON c.nombre LIKE CONCAT(a.curso, '%')
         WHERE a.estado = 'Activo'"
    )->fetchColumn();
} catch (Exception $e) {}

// Vehiculos para el carrusel
$vehiculos = $q('SELECT * FROM vehiculos ORDER BY estado DESC, id DESC');
$empNombre = [];
foreach ($q('SELECT id, nombre FROM empleados') as $e) { $empNombre[(int) $e['id']] = $e['nombre']; }

// Alumnos por mes (ultimos 6 meses) para la grafica
$etiquetas = [];
$valores   = [];
$mesesClave = [];
for ($i = 5; $i >= 0; $i--) {
    $ts = strtotime("first day of -{$i} months");
    $mesesClave[] = date('Y-m', $ts);
    $etiquetas[]  = mb_strtoupper(date('M', $ts), 'UTF-8');
}
$porMes = [];
foreach ($q("SELECT DATE_FORMAT(creado_en, '%Y-%m') AS m, COUNT(*) AS c FROM alumnos GROUP BY m") as $r) {
    $porMes[$r['m']] = (int) $r['c'];
}
foreach ($mesesClave as $mk) { $valores[] = $porMes[$mk] ?? 0; }

// Ultimos registros de alumnos
$ultimos = $q('SELECT * FROM alumnos ORDER BY creado_en DESC, id DESC LIMIT 6');

// Horarios de la semana
$horariosHoy = $q('SELECT h.*, c.nombre AS curso, e.nombre AS instructor
                   FROM horarios h
                   LEFT JOIN cursos c ON c.id = h.curso_id
                   LEFT JOIN empleados e ON e.id = h.empleado_id
                   ORDER BY FIELD(h.dia, "Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"), h.hora_inicio LIMIT 4');

$kpis = [
    ['fa-user-graduate', (string) $alumnosActivos, 'Alumnos activos', $alumnosTotal . ' inscritos', 'up'],
    ['fa-file-signature', (string) $examenesTotal,  'Examenes activos', $examenesBorr . ' en borrador', 'up'],
    ['fa-car-side',      (string) $totalFlota,      'Vehiculos en flota', $disponibles . ' libres', 'up'],
    ['fa-dollar-sign',   '$' . number_format($ingresos, 0), 'Ingresos estimados', $horariosTotal . ' horarios', 'up'],
];
?>
<div class="page-head d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <h3>Hola, <span><?= htmlspecialchars(explode(' ', $usuarioActual['nombre'] ?? 'Admin')[0]) ?></span></h3>
    <p>Aqui tienes el resumen de ESDRIVE de hoy.</p>
  </div>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <span class="badge-soft b-active"><i class="fas fa-circle-check me-1"></i> <?= $disponibles ?> vehiculos libres</span>
    <?php if (esdrive_puede('vehiculos')): ?>
      <a href="<?= BASE_URL ?>/admin.php?v=vehiculos" class="btn btn-glass btn-sm"><i class="fas fa-car-side me-2"></i>Ver flota</a>
    <?php endif; ?>
  </div>
</div>
<?= esdrive_flash() ?>

<!-- KPIs -->
<div class="row g-2 mb-3">
  <?php foreach ($kpis as $k): [$icon, $val, $lbl, $trend, $dir] = $k; ?>
  <div class="col-xl-3 col-md-6">
    <div class="glass kpi-card">
      <div class="kpi-icon"><i class="fas <?= $icon ?>"></i></div>
      <div class="kpi-info">
        <div class="kpi-val"><?= $val ?></div>
        <div class="kpi-lbl"><?= $lbl ?></div>
        <div class="kpi-trend <?= $dir ?>"><i class="fas fa-arrow-<?= $dir === 'up' ? 'up' : 'down' ?>"></i> <?= $trend ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ===== Fila principal: Carrusel + Pills ===== -->
<div class="row g-2 dash-body">

  <!-- Carrusel de la flota -->
  <div class="col-lg-4 order-1 order-lg-2">
    <div class="glass panel panel-flex">
      <div class="panel-title"><i class="fas fa-car-side"></i>Flota de vehiculos</div>
      <?php if ($vehiculos): ?>
        <div id="carruselFlota" class="carousel slide flota-carousel" data-bs-ride="carousel">
          <div class="carousel-indicators">
            <?php foreach ($vehiculos as $i => $v): ?>
              <button type="button" data-bs-target="#carruselFlota" data-bs-slide-to="<?= $i ?>"
                      class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Vehiculo <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
          </div>
          <div class="carousel-inner">
            <?php foreach ($vehiculos as $i => $v):
              $esDispo = $v['estado'] === 'disponible';
              $fase    = '';
              $fechaTs = 0;
              $texto   = '';
              if ($esDispo && !empty($v['proximo_uso'])) {
                  $fase = 'uso';  $fechaTs = strtotime($v['proximo_uso']);
                  $texto = 'Se usara a las ' . date('H:i', $fechaTs);
              } elseif (!$esDispo && !empty($v['disponible_desde'])) {
                  $fase = 'libre'; $fechaTs = strtotime($v['disponible_desde']);
                  $texto = 'Vuelve a estar libre a las ' . date('H:i', $fechaTs);
              } elseif ($esDispo) {
                  $texto = 'Libre ahora &middot; sin horario asignado';
              } else {
                  $texto = 'En uso ahora';
              }
              $empAsig = (!empty($v['empleado_id']) && isset($empNombre[(int) $v['empleado_id']])) ? $empNombre[(int) $v['empleado_id']] : null;
            ?>
              <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                <div class="flota-slide">
                  <!-- Imagen en su propia columna: nunca se recorta mal -->
                  <div class="flota-media">
                    <?php if ($v['imagen']): ?>
                      <img src="<?= htmlspecialchars($v['imagen']) ?>" alt="<?= htmlspecialchars($v['nombre']) ?>">
                    <?php else: ?>
                      <div class="flota-sin-img"><i class="fas fa-car-side"></i></div>
                    <?php endif; ?>
                  </div>
                  <!-- Informacion del vehiculo -->
                  <div class="flota-body">
                    <div class="flota-nombre"><?= htmlspecialchars($v['nombre']) ?></div>
                    <div class="flota-meta">
                      <?php if ($esDispo): ?>
                        <span class="badge-soft b-active"><i class="fas fa-circle-check me-1"></i>Disponible</span>
                      <?php else: ?>
                        <span class="badge-soft b-pend"><i class="fas fa-clock me-1"></i>En uso</span>
                      <?php endif; ?>
                      <span class="flota-placa"><i class="fas fa-id-card me-1"></i><?= htmlspecialchars($v['placa']) ?: '&mdash;' ?></span>
                    </div>
                    <?php if ($empAsig): ?>
                      <div class="flota-emp"><i class="fas fa-user-tie me-1"></i><?= htmlspecialchars($empAsig) ?></div>
                    <?php endif; ?>
                    <div class="flota-horario <?= $fase ? 'tiene-fecha' : 'sin-fecha' ?>"
                         <?= $fase ? 'data-fecha="' . date('Y-m-d\TH:i:s', $fechaTs) . '" data-fase="' . $fase . '"' : '' ?>>
                      <div class="fh-linea"><i class="far fa-clock me-1"></i><span class="fh-texto"><?= htmlspecialchars($texto) ?></span></div>
                      <?php if ($fase): ?><div class="fh-barra"><span class="fh-progreso"></span></div><?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carruselFlota" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carruselFlota" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
          </button>
        </div>
        <div class="flota-count"><i class="fas fa-circle-info me-1"></i><?= count($vehiculos) ?> en flota &middot; <?= $disponibles ?> libres ahora</div>
      <?php else: ?>
        <div class="flota-vacio">
          <i class="fas fa-car-side"></i>
          <p>No hay vehiculos registrados aun.</p>
          <?php if (esdrive_puede('vehiculos', 'gestionar')): ?>
            <a href="<?= BASE_URL ?>/admin.php?v=vehiculos" class="btn btn-add btn-sm"><i class="fas fa-plus me-2"></i>Agregar</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Pills (Graficas / Ultimos registros) -->
  <div class="col-lg-8 order-2 order-lg-1">
    <div class="glass panel panel-flex dash-main">
      <div class="dash-panel-head">
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
          <?php if (esdrive_puede('horarios')): ?>
          <li class="nav-item">
            <button type="button" class="nav-link" data-panel="panelHorarios">
              <i class="fas fa-calendar-days me-2"></i>Horarios
            </button>
          </li>
          <?php endif; ?>
        </ul>
        <?php if (esdrive_puede('reportes')): ?>
          <a href="<?= BASE_URL ?>/admin.php?v=reportes" class="btn btn-glass btn-sm"><i class="fas fa-chart-pie me-2"></i>Ver reportes</a>
        <?php endif; ?>
      </div>

      <!-- Panel: Graficas -->
      <div id="panelGraficas" class="dash-panel">
        <div class="glass panel panel-flex chart-single">
          <div class="panel-title"><i class="fas fa-chart-line"></i>Alumnos inscritos (ultimos 6 meses)</div>
          <div class="chart-wrap"><div id="graficaAlumnos"></div></div>
        </div>
      </div>

      <!-- Panel: Ultimos registros -->
      <div id="panelRegistros" class="dash-panel d-none">
        <div class="glass panel panel-flex">
          <div class="panel-title"><i class="fas fa-user-clock"></i>Ultimos registros</div>
          <div class="table-responsive">
            <table class="table table-glass">
              <thead>
                <tr><th>Alumno</th><th>Curso</th><th>Fecha</th><th>Estado</th></tr>
              </thead>
              <tbody>
                <?php if (!$ultimos): ?>
                  <tr><td colspan="4" class="text-center" style="padding:24px;color:var(--text-mut)">Aun no hay registros de alumnos.</td></tr>
                <?php endif; ?>
                <?php foreach ($ultimos as $a): ?>
                <tr>
                  <td>
                    <div class="cell-user">
                      <div class="cell-avatar" style="background:<?= esdrive_color_avatar($a['nombre']) ?>"><?= esdrive_ini($a['nombre']) ?></div>
                      <div><div class="cu-name"><?= htmlspecialchars($a['nombre']) ?></div><div class="cu-mail"><?= htmlspecialchars($a['email']) ?></div></div>
                    </div>
                  </td>
                  <td><?= htmlspecialchars($a['curso']) ?></td>
                  <td><?= date('d/m/Y', strtotime($a['creado_en'])) ?></td>
                  <td><?= esdrive_badge($a['estado']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Panel: Horarios -->
      <div id="panelHorarios" class="dash-panel d-none">
        <div class="glass panel panel-flex">
          <div class="panel-title"><i class="fas fa-calendar-days"></i>Proximos horarios</div>
          <div class="table-responsive">
            <table class="table table-glass">
              <thead>
                <tr><th>Dia</th><th>Hora</th><th>Curso</th><th>Instructor</th><th>Cupo</th></tr>
              </thead>
              <tbody>
                <?php if (!$horariosHoy): ?>
                  <tr><td colspan="5" class="text-center" style="padding:24px;color:var(--text-mut)">No hay horarios programados.</td></tr>
                <?php endif; ?>
                <?php foreach ($horariosHoy as $h): ?>
                <tr>
                  <td style="font-weight:600"><?= htmlspecialchars($h['dia']) ?></td>
                  <td><?= htmlspecialchars($h['hora_inicio']) ?> - <?= htmlspecialchars($h['hora_fin']) ?></td>
                  <td><?= htmlspecialchars($h['curso'] ?? 'Sin curso') ?></td>
                  <td><?= htmlspecialchars($h['instructor'] ?? 'Sin asignar') ?></td>
                  <td><?= (int) $h['cupo'] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
window.__graficaAlumnos = <?= json_encode(['etiquetas' => $etiquetas, 'valores' => $valores]) ?>;
</script>