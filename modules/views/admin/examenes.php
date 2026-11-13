<?php
/**
 * Examenes (admin): resultados de los examenes realizados por los alumnos.
 *
 * Los datos salen de la tabla `examen_alumnos` (nota, aciertos, porcentaje y
 * estado de cada alumno). Para inscribir alumnos y habilitar sus examenes
 * se usa la Consola de examenes (examen-alumnos.php).
 */
require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/ui.php';
require_once __DIR__ . '/../../../config/examen_funciones.php';

/* ---------------- Resultados ---------------- */
try {
    $resultados = db()->query(
        'SELECT * FROM examen_alumnos ORDER BY terminado_en IS NULL, terminado_en DESC, id DESC'
    )->fetchAll();
} catch (Exception $e) { $resultados = []; }

/* Filtro por estado (mismos valores que guarda la BD) */
$estadosFiltro = ['pendiente', 'en_curso', 'aprobado', 'reprobado', 'expirado'];
?>

<?= esdrive_page_head('Resultados de', 'examenes',
    'Notas obtenidas por los alumnos en los examenes realizados.',
    '<a class="btn btn-add" href="' . BASE_URL . '/examen-alumnos.php">
        <i class="fas fa-user-plus"></i><span>Inscribir alumno</span></a>') ?>

<div class="glass panel">
  <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
    <div class="panel-title mb-0"><i class="fas fa-graduation-cap"></i>Resultados de los alumnos</div>
    <div class="d-flex gap-2 flex-wrap">
      <div class="input-group" style="width:240px">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" placeholder="Buscar alumno, DUI o codigo..." id="buscarResultado">
      </div>
      <select class="form-select" style="width:180px" id="filtroEstadoResultado">
        <option value="">Todos los estados</option>
        <?php foreach ($estadosFiltro as $est): ?>
          <option value="<?= htmlspecialchars($est) ?>"><?= htmlspecialchars(examen_etiqueta_estado($est)) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="button" class="btn btn-refresh" id="btnActualizar" title="Actualizar tabla">
        <i class="fas fa-rotate"></i><span>Actualizar</span>
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-glass" id="tablaResultados">
      <thead>
        <tr>
          <th>Alumno</th>
          <th>Codigo</th>
          <th>Estado</th>
          <th class="text-center">Aciertos</th>
          <th class="text-center">Nota</th>
          <th style="min-width:150px">Porcentaje</th>
          <th>Termino</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$resultados): ?>
          <tr class="fila-vacia"><td colspan="8" class="text-center" style="padding:28px;color:var(--text-mut)">
            <i class="fas fa-inbox me-2"></i>Todavia no hay resultados de examenes.
          </td></tr>
        <?php endif; ?>
        <?php foreach ($resultados as $r): ?>
          <?php
            $estado = (string) $r['estado'];
            $nota   = (int) $r['nota'];
            $pct    = (float) $r['porcentaje'];
            $rendido = in_array($estado, ['aprobado', 'reprobado'], true);
            $claseNota = $estado === 'aprobado' ? 'nt-ok' : ($estado === 'reprobado' ? 'nt-error' : 'nt-muted');
            $claseBarra = $estado === 'aprobado' ? 'ok' : ($estado === 'reprobado' ? 'error' : 'mid');
          ?>
          <tr data-estado="<?= htmlspecialchars($estado) ?>">
            <td>
              <div class="cell-user">
                <div class="cell-avatar" style="background:<?= esdrive_color_avatar($r['nombre']) ?>"><?= esdrive_ini($r['nombre']) ?></div>
                <div>
                  <div class="cu-name"><?= htmlspecialchars($r['nombre']) ?></div>
                  <div class="cu-mail"><?= $r['dui'] ? 'DUI ' . htmlspecialchars($r['dui']) : 'Sin DUI' ?></div>
                </div>
              </div>
            </td>
            <td><code><?= htmlspecialchars($r['codigo']) ?></code></td>
            <td><?= esdrive_badge(examen_etiqueta_estado($estado)) ?></td>
            <td class="text-center">
              <?php if ($rendido): ?>
                <?= (int) $r['aciertos'] ?> / <?= (int) $r['total'] ?>
              <?php else: ?>
                <span style="color:var(--text-mut)">&mdash;</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <?php if ($rendido): ?>
                <span class="nota-caja <?= $claseNota ?>"><?= $nota ?><small>/100</small></span>
              <?php else: ?>
                <span style="color:var(--text-mut)">&mdash;</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="barra-nota" title="<?= $pct ?>%">
                <i class="<?= $claseBarra ?>" style="width:<?= max(0, min(100, $pct)) ?>%"></i>
              </div>
              <small style="color:var(--text-mut)"><?= number_format($pct, 2) ?>%</small>
            </td>
            <td>
              <?php if ($r['terminado_en']): ?>
                <?= date('d/m/Y', strtotime($r['terminado_en'])) ?>
                <small style="color:var(--text-mut)"><?= date('h:i A', strtotime($r['terminado_en'])) ?></small>
              <?php else: ?>
                <span style="color:var(--text-mut)">&mdash;</span>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <a class="btn-act" title="Ver detalle"
                 href="<?= BASE_URL ?>/examen-alumnos.php?alumno=<?= (int) $r['id'] ?>">
                <i class="fas fa-eye"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?= esdrive_paginacion('tablaResultados', 10) ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  esdriveTabla({
    tabla: 'tablaResultados',
    buscar: 'buscarResultado',
    filtros: [{ id: 'filtroEstadoResultado', attr: 'data-estado' }]
  });

  var btn = document.getElementById('btnActualizar');
  if (btn) btn.addEventListener('click', function () {
    btn.classList.add('is-spinning');
    setTimeout(function () { location.reload(); }, 220);
  });
});
</script>
