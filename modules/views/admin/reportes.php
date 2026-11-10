<?php require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/ui.php';

$q = function ($sql, $args = []) {
    try { $st = db()->prepare($sql); $st->execute($args); return $st->fetchAll(); }
    catch (Exception $e) { return []; }
};

// --- Alumnos por mes (6 meses) ---
$mesesClave = []; $etiquetasMes = [];
for ($i = 5; $i >= 0; $i--) {
    $ts = strtotime("first day of -{$i} months");
    $mesesClave[] = date('Y-m', $ts);
    $etiquetasMes[] = mb_strtoupper(date('M', $ts), 'UTF-8');
}
$porMes = [];
foreach ($q("SELECT DATE_FORMAT(creado_en, '%Y-%m') AS m, COUNT(*) AS c FROM alumnos GROUP BY m") as $r) {
    $porMes[$r['m']] = (int) $r['c'];
}
$valoresMes = array_map(fn($mk) => $porMes[$mk] ?? 0, $mesesClave);

// --- Alumnos por curso (donut) ---
$etiquetasCurso = []; $seriesCurso = [];
foreach ($q("SELECT curso, COUNT(*) AS c FROM alumnos GROUP BY curso ORDER BY c DESC") as $r) {
    $etiquetasCurso[] = $r['curso'] ?: 'Sin curso';
    $seriesCurso[] = (int) $r['c'];
}

// --- Examenes por estado (donut) ---
$etiquetasExamen = []; $seriesExamen = [];
foreach ($q("SELECT estado, COUNT(*) AS c FROM examenes GROUP BY estado ORDER BY c DESC") as $r) {
    $etiquetasExamen[] = $r['estado'];
    $seriesExamen[] = (int) $r['c'];
}

// --- Vehiculos por estado (donut) ---
$etiquetasVeh = []; $seriesVeh = [];
foreach ($q("SELECT estado, COUNT(*) AS c FROM vehiculos GROUP BY estado") as $r) {
    $etiquetasVeh[] = $r['estado'] === 'disponible' ? 'Disponible' : 'En uso';
    $seriesVeh[] = (int) $r['c'];
}

// --- Ingresos estimados por curso (barras) ---
$etiquetasIng = []; $seriesIng = [];
foreach ($q("SELECT c.nombre, COUNT(a.id) AS n, COALESCE(c.precio,0) AS precio
             FROM cursos c LEFT JOIN alumnos a ON a.curso LIKE CONCAT(c.nombre, '%') AND a.estado = 'Activo'
             GROUP BY c.id ORDER BY c.nombre") as $r) {
    $etiquetasIng[] = mb_strimwidth($r['nombre'], 0, 16, '…');
    $seriesIng[] = (int) $r['n'] * (float) $r['precio'];
}

// KPIs
$alumnosTotal  = (int) db()->query('SELECT COUNT(*) FROM alumnos')->fetchColumn();
$alumnosAct    = (int) db()->query("SELECT COUNT(*) FROM alumnos WHERE estado='Activo'")->fetchColumn();
$examenesTotal = (int) db()->query('SELECT COUNT(*) FROM examenes')->fetchColumn();
$ingresosTotal = array_sum($seriesIng);

// Tabla de resumen por curso
$resumen = $q("SELECT c.nombre, c.precio, c.estado,
                      COUNT(a.id) AS alumnos,
                      COALESCE(COUNT(a.id) * c.precio, 0) AS ingreso
               FROM cursos c
               LEFT JOIN alumnos a ON a.curso LIKE CONCAT(c.nombre, '%') AND a.estado = 'Activo'
               GROUP BY c.id ORDER BY c.nombre");
?>
<?= esdrive_page_head('Reportes y', 'estadisticas', 'Analisis real de alumnos, examenes, flota e ingresos.',
    '<button type="button" class="btn btn-refresh" onclick="location.reload()"><i class="fas fa-rotate"></i><span>Actualizar</span></button>') ?>

<div class="row g-2 mb-3">
  <div class="col-xl-3 col-md-6"><div class="glass kpi-card">
    <div class="kpi-icon"><i class="fas fa-user-graduate"></i></div>
    <div class="kpi-info"><div class="kpi-val"><?= $alumnosTotal ?></div><div class="kpi-lbl">Alumnos totales</div></div>
  </div></div>
  <div class="col-xl-3 col-md-6"><div class="glass kpi-card">
    <div class="kpi-icon"><i class="fas fa-user-check"></i></div>
    <div class="kpi-info"><div class="kpi-val"><?= $alumnosAct ?></div><div class="kpi-lbl">Alumnos activos</div></div>
  </div></div>
  <div class="col-xl-3 col-md-6"><div class="glass kpi-card">
    <div class="kpi-icon"><i class="fas fa-file-signature"></i></div>
    <div class="kpi-info"><div class="kpi-val"><?= $examenesTotal ?></div><div class="kpi-lbl">Examenes</div></div>
  </div></div>
  <div class="col-xl-3 col-md-6"><div class="glass kpi-card">
    <div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
    <div class="kpi-info"><div class="kpi-val">$<?= number_format($ingresosTotal, 0) ?></div><div class="kpi-lbl">Ingresos estimados</div></div>
  </div></div>
</div>

<div class="row g-2 mb-2">
  <div class="col-lg-7">
    <div class="glass panel panel-flex">
      <div class="panel-title"><i class="fas fa-chart-line"></i>Alumnos inscritos (ultimos 6 meses)</div>
      <div class="chart-wrap"><div id="graficaAlumnosMes"></div></div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="glass panel panel-flex">
      <div class="panel-title"><i class="fas fa-chart-pie"></i>Alumnos por curso</div>
      <div class="chart-wrap"><div id="graficaAlumnosCurso"></div></div>
    </div>
  </div>
</div>

<div class="row g-2 mb-2">
  <div class="col-lg-4">
    <div class="glass panel panel-flex">
      <div class="panel-title"><i class="fas fa-file-lines"></i>Examenes por estado</div>
      <div class="chart-wrap"><div id="graficaExamenes"></div></div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="glass panel panel-flex">
      <div class="panel-title"><i class="fas fa-car-side"></i>Flota por estado</div>
      <div class="chart-wrap"><div id="graficaVehiculos"></div></div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="glass panel panel-flex">
      <div class="panel-title"><i class="fas fa-coins"></i>Ingresos por curso</div>
      <div class="chart-wrap"><div id="graficaIngresos"></div></div>
    </div>
  </div>
</div>

<div class="glass panel">
  <div class="panel-title mb-3"><i class="fas fa-table"></i>Resumen por curso</div>
  <div class="table-responsive">
    <table class="table table-glass">
      <thead>
        <tr><th>Curso</th><th>Precio</th><th>Alumnos activos</th><th>Ingreso estimado</th><th>Estado</th></tr>
      </thead>
      <tbody>
        <?php if (!$resumen): ?>
          <tr><td colspan="5" class="text-center" style="padding:24px;color:var(--text-mut)">No hay cursos registrados.</td></tr>
        <?php endif; ?>
        <?php foreach ($resumen as $r): ?>
        <tr>
          <td style="font-weight:600"><?= htmlspecialchars($r['nombre']) ?></td>
          <td>$<?= number_format((float) $r['precio'], 2) ?></td>
          <td><?= (int) $r['alumnos'] ?></td>
          <td style="color:var(--gold);font-weight:700">$<?= number_format((float) $r['ingreso'], 2) ?></td>
          <td><?= esdrive_badge($r['estado']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
window.__reportes = <?= json_encode([
    'alumnosMes'    => ['etiquetas' => $etiquetasMes, 'valores' => $valoresMes],
    'alumnosCurso'  => ['labels' => $etiquetasCurso, 'series' => $seriesCurso],
    'examenes'      => ['labels' => $etiquetasExamen, 'series' => $seriesExamen],
    'vehiculos'     => ['labels' => $etiquetasVeh,   'series' => $seriesVeh],
    'ingresos'      => ['etiquetas' => $etiquetasIng, 'valores' => $seriesIng],
], JSON_UNESCAPED_UNICODE) ?>;
</script>
