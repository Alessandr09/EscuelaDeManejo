<?php require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/ui.php';

$editar = null;
if (isset($_GET['editar'])) {
    $st = db()->prepare('SELECT * FROM horarios WHERE id = ?');
    $st->execute([(int) $_GET['editar']]);
    $editar = $st->fetch() ?: null;
}

try {
    $horarios = db()->query(
        'SELECT h.*, c.nombre AS curso, e.nombre AS instructor
         FROM horarios h
         LEFT JOIN cursos c ON c.id = h.curso_id
         LEFT JOIN empleados e ON e.id = h.empleado_id
         ORDER BY FIELD(h.dia, "Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"), h.hora_inicio'
    )->fetchAll();
} catch (Exception $e) { $horarios = []; }

$cursos      = esdrive_opciones_cursos();
try { $empleados = db()->query('SELECT id, nombre, cargo FROM empleados ORDER BY nombre')->fetchAll(); }
catch (Exception $e) { $empleados = []; }

$cupoTotal = array_sum(array_map(fn($h) => (int) $h['cupo'], $horarios));
$puede     = esdrive_puede('horarios', 'gestionar');

$kpis = [
    ['fa-calendar-days', count($horarios), 'Horarios',        'b-info'],
    ['fa-users',         $cupoTotal,       'Cupo total',      'b-active'],
    ['fa-graduation-cap', count($cursos),  'Cursos con horario', 'b-pend'],
    ['fa-chalkboard-teacher', count($empleados), 'Instructores', 'b-info'],
];
?>
<?= esdrive_page_head('Gestion de', 'horarios', 'Programa las clases por dia, hora, curso e instructor.',
    $puede ? '<button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalHorario">
        <i class="fas fa-plus"></i><span>Nuevo horario</span></button>' : '') ?>

<div class="row g-2 mb-3">
  <?php foreach ($kpis as $k): [$icon, $val, $lbl, $cls] = $k; ?>
  <div class="col-xl-3 col-md-6">
    <div class="glass kpi-card">
      <div class="kpi-icon"><i class="fas <?= $icon ?>"></i></div>
      <div class="kpi-info"><div class="kpi-val"><?= $val ?></div><div class="kpi-lbl"><?= $lbl ?></div></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="glass panel">
  <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
    <div class="panel-title mb-0"><i class="fas fa-calendar-check"></i>Horario semanal</div>
    <div class="d-flex gap-2 flex-wrap">
      <div class="input-group" style="width:210px">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" placeholder="Buscar..." id="buscarHorario">
      </div>
      <select class="form-select" style="width:160px" id="filtroDia">
        <option value="">Todos los dias</option>
        <?php foreach (['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo'] as $d): ?>
          <option value="<?= $d ?>"><?= $d ?></option>
        <?php endforeach; ?>
      </select>
      <button type="button" class="btn btn-refresh" id="btnActualizar" title="Actualizar tabla">
        <i class="fas fa-rotate"></i><span>Actualizar</span>
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-glass" id="tablaHorarios">
      <thead>
        <tr>
          <th>Dia</th>
          <th>Horario</th>
          <th>Curso</th>
          <th>Instructor</th>
          <th>Cupo</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$horarios): ?>
          <tr><td colspan="6" class="text-center" style="padding:28px;color:var(--text-mut)">
            <i class="fas fa-inbox me-2"></i>No hay horarios programados.
          </td></tr>
        <?php endif; ?>
        <?php foreach ($horarios as $h): ?>
          <tr data-dia="<?= htmlspecialchars($h['dia']) ?>">
            <td><span class="badge-soft b-pend"><i class="fas fa-calendar-day me-1"></i><?= htmlspecialchars($h['dia']) ?></span></td>
            <td style="font-weight:600"><?= htmlspecialchars($h['hora_inicio']) ?> - <?= htmlspecialchars($h['hora_fin']) ?></td>
            <td><?= htmlspecialchars($h['curso'] ?? 'Sin curso') ?></td>
            <td>
              <?php if ($h['instructor']): ?>
                <div class="cell-user">
                  <div class="cell-avatar" style="background:<?= esdrive_color_avatar($h['instructor']) ?>"><?= esdrive_ini($h['instructor']) ?></div>
                  <div class="cu-name"><?= htmlspecialchars($h['instructor']) ?></div>
                </div>
              <?php else: ?>
                <span style="color:var(--text-mut)">Sin asignar</span>
              <?php endif; ?>
            </td>
            <td><span class="badge-soft b-info"><?= (int) $h['cupo'] ?> cupos</span></td>
            <td class="text-end">
              <?php if ($puede): ?>
                <a href="?v=horarios&editar=<?= (int) $h['id'] ?>" class="btn-act edit" title="Editar"><i class="fas fa-pen"></i></a>
                <button type="button" class="btn-act del" title="Eliminar"
                        data-eliminar="<?= (int) $h['id'] ?>" data-que="horario"
                        data-nombre="<?= htmlspecialchars($h['dia'] . ' ' . $h['hora_inicio'] . '-' . $h['hora_fin']) ?>">
                  <i class="fas fa-trash"></i>
                </button>
                <form method="post" id="formEliminar<?= (int) $h['id'] ?>" class="d-none">
                  <input type="hidden" name="accion" value="eliminar_horario">
                  <input type="hidden" name="id" value="<?= (int) $h['id'] ?>">
                </form>
              <?php else: ?>
                <span class="badge-soft b-info"><i class="fas fa-eye me-1"></i>Solo lectura</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ============ MODAL HORARIO ============ -->
<div class="modal fade" id="modalHorario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content modal-esdrive">
      <div class="modal-head-accent">
        <h5 class="modal-title"><i class="fas fa-calendar-check me-2"></i><?= $editar ? 'Editar horario' : 'Nuevo horario' ?></h5>
        <button type="button" class="btn-close-accent" data-bs-dismiss="modal" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
      </div>
      <form method="post">
        <input type="hidden" name="accion" value="guardar_horario">
        <input type="hidden" name="id" value="<?= $editar ? (int) $editar['id'] : '' ?>">

        <div class="modal-body-accent">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Dia</label>
              <select name="dia" class="form-select" required>
                <?php foreach (['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo'] as $d): ?>
                  <option <?= (($editar['dia'] ?? '') === $d) ? 'selected' : '' ?>><?= $d ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Inicio</label>
              <input type="time" name="hora_inicio" class="form-control" required
                     value="<?= htmlspecialchars($editar['hora_inicio'] ?? '08:00') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Fin</label>
              <input type="time" name="hora_fin" class="form-control"
                     value="<?= htmlspecialchars($editar['hora_fin'] ?? '10:00') ?>">
            </div>
            <div class="col-md-5">
              <label class="form-label">Curso</label>
              <select name="curso_id" class="form-select">
                <option value="">Sin curso</option>
                <?php foreach ($cursos as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= ((int) ($editar['curso_id'] ?? 0) === $c['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Instructor</label>
              <select name="empleado_id" class="form-select">
                <option value="">Sin asignar</option>
                <?php foreach ($empleados as $e): ?>
                  <option value="<?= (int) $e['id'] ?>" <?= ((int) ($editar['empleado_id'] ?? 0) === (int) $e['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Cupo</label>
              <input type="number" name="cupo" min="0" class="form-control" value="<?= (int) ($editar['cupo'] ?? 4) ?>">
            </div>
          </div>
        </div>

        <div class="modal-foot-accent">
          <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-gold"><i class="fas fa-floppy-disk me-2"></i>Guardar horario</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var q = document.getElementById('buscarHorario');
  var f = document.getElementById('filtroDia');
  var filas = Array.prototype.slice.call(document.querySelectorAll('#tablaHorarios tbody tr'));
  function filtrar() {
    var txt = (q && q.value || '').toLowerCase();
    var dia = f && f.value || '';
    filas.forEach(function (tr) {
      var okTxt = tr.textContent.toLowerCase().indexOf(txt) !== -1;
      var okDia = !dia || tr.getAttribute('data-dia') === dia;
      tr.style.display = (okTxt && okDia) ? '' : 'none';
    });
  }
  if (q) q.addEventListener('input', filtrar);
  if (f) f.addEventListener('change', filtrar);

  var btn = document.getElementById('btnActualizar');
  if (btn) btn.addEventListener('click', function () {
    btn.classList.add('is-spinning');
    setTimeout(function () { location.reload(); }, 220);
  });

  <?php if ($editar): ?>
  new bootstrap.Modal(document.getElementById('modalHorario')).show();
  <?php endif; ?>
});
</script>
