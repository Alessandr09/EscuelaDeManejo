<?php require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/ui.php';

$editar = null;
if (isset($_GET['editar'])) {
    $st = db()->prepare('SELECT * FROM examenes WHERE id = ?');
    $st->execute([(int) $_GET['editar']]);
    $editar = $st->fetch() ?: null;
}

try {
    $examenes = db()->query('SELECT * FROM examenes ORDER BY fecha IS NULL, fecha, id DESC')->fetchAll();
} catch (Exception $e) { $examenes = []; }

$total   = count($examenes);
$activos = count(array_filter($examenes, fn($e) => $e['estado'] === 'Activo'));
$borr    = count(array_filter($examenes, fn($e) => $e['estado'] === 'Borrador'));
$preguntas = array_sum(array_map(fn($e) => (int) $e['preguntas'], $examenes));
$cursos  = esdrive_opciones_cursos();
$puede   = esdrive_puede('examenes', 'gestionar');

// JSON para precargar el modal al editar
$jsonExamenes = [];
foreach ($examenes as $e) {
    $jsonExamenes[(int) $e['id']] = [
        'titulo'      => $e['titulo'],
        'curso'       => $e['curso'],
        'preguntas'   => (int) $e['preguntas'],
        'duracion'    => $e['duracion'],
        'fecha'       => $e['fecha'] ? date('Y-m-d', strtotime($e['fecha'])) : '',
        'estado'      => $e['estado'],
        'descripcion' => $e['descripcion'] ?? '',
    ];
}

$kpis = [
    ['fa-file-signature', $activos,  'Examenes activos',  'b-active'],
    ['fa-pen-nib',        $total,    'Total examenes',    'b-info'],
    ['fa-list-ol',        $preguntas,'Preguntas',         'b-info'],
    ['fa-file-pen',       $borr,     'Borradores',        'b-pend'],
];
?>
<?= esdrive_page_head('Gestion de', 'examenes', 'Crea y administra las evaluaciones teoricas y practicas.',
    $puede ? '<button type="button" class="btn btn-add" onclick="abrirModalExamen(0)">
        <i class="fas fa-plus"></i><span>Nuevo examen</span></button>' : '') ?>

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
    <div class="panel-title mb-0"><i class="fas fa-file-signature"></i>Examenes</div>
    <div class="d-flex gap-2 flex-wrap">
      <div class="input-group" style="width:230px">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" placeholder="Buscar examen..." id="buscarExamen">
      </div>
      <select class="form-select" style="width:160px" id="filtroEstadoExamen">
        <option value="">Todos los estados</option>
        <option value="Activo">Activo</option>
        <option value="Pendiente">Pendiente</option>
        <option value="Borrador">Borrador</option>
      </select>
      <button type="button" class="btn btn-refresh" id="btnActualizar" title="Actualizar tabla">
        <i class="fas fa-rotate"></i><span>Actualizar</span>
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-glass" id="tablaExamenes">
      <thead>
        <tr>
          <th>Examen</th>
          <th>Curso</th>
          <th>Preguntas</th>
          <th>Duracion</th>
          <th>Fecha</th>
          <th>Estado</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$examenes): ?>
          <tr><td colspan="7" class="text-center" style="padding:28px;color:var(--text-mut)">
            <i class="fas fa-inbox me-2"></i>No hay examenes creados.
          </td></tr>
        <?php endif; ?>
        <?php foreach ($examenes as $e): ?>
          <tr data-estado="<?= htmlspecialchars($e['estado']) ?>">
            <td>
              <div class="cell-user">
                <div class="cell-avatar"><i class="fas fa-file-lines"></i></div>
                <div>
                  <div class="cu-name"><?= htmlspecialchars($e['titulo']) ?></div>
                  <div class="cu-mail">#<?= (int) $e['id'] ?></div>
                </div>
              </div>
            </td>
            <td><span class="badge-soft b-info"><?= htmlspecialchars($e['curso']) ?: 'ÔÇö' ?></span></td>
            <td><?= (int) $e['preguntas'] ?></td>
            <td><?= htmlspecialchars($e['duracion']) ?: 'ÔÇö' ?></td>
            <td><?= $e['fecha'] ? date('d/m/Y', strtotime($e['fecha'])) : 'ÔÇö' ?></td>
            <td><?= esdrive_badge($e['estado']) ?></td>
            <td class="text-end">
              <?php if ($puede): ?>
                <button type="button" class="btn-act edit" title="Editar" onclick="abrirModalExamen(<?= (int) $e['id'] ?>)">
                  <i class="fas fa-pen"></i>
                </button>
                <button type="button" class="btn-act del" title="Eliminar"
                        data-eliminar="<?= (int) $e['id'] ?>" data-que="examen" data-nombre="<?= htmlspecialchars($e['titulo']) ?>">
                  <i class="fas fa-trash"></i>
                </button>
                <form method="post" id="formEliminar<?= (int) $e['id'] ?>" class="d-none">
                  <input type="hidden" name="accion" value="eliminar_examen">
                  <input type="hidden" name="id" value="<?= (int) $e['id'] ?>">
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

<!-- ============ MODAL EXAMEN (CKEditor) ============ -->
<div class="modal fade" id="modalExamen" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content modal-esdrive">
      <div class="modal-head-accent">
        <h5 class="modal-title"><i class="fas fa-pen-nib me-2"></i>Nuevo examen</h5>
        <button type="button" class="btn-close-accent" data-bs-dismiss="modal" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
      </div>
      <form method="post" id="formExamen" onsubmit="return prepararExamen()">
        <input type="hidden" name="accion" value="guardar_examen">
        <input type="hidden" name="id" value="">
        <input type="hidden" name="descripcion" value="">

        <div class="modal-body-accent">
          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label">Titulo del examen</label>
              <input type="text" name="titulo" class="form-control" required placeholder="Ej: Senalamiento vial">
            </div>
            <div class="col-md-5">
              <label class="form-label">Curso</label>
              <select name="curso" class="form-select">
                <option value="">General / todos</option>
                <?php foreach ($cursos as $c): ?>
                  <option value="<?= htmlspecialchars($c['nombre']) ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Preguntas</label>
              <input type="number" name="preguntas" class="form-control" min="0" value="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Duracion</label>
              <input type="text" name="duracion" class="form-control" placeholder="20 min">
            </div>
            <div class="col-md-3">
              <label class="form-label">Fecha</label>
              <input type="date" name="fecha" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label">Estado</label>
              <select name="estado" class="form-select">
                <option>Borrador</option>
                <option>Pendiente</option>
                <option>Activo</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Contenido / instrucciones</label>
              <textarea name="editorExamen" id="editorExamen"></textarea>
            </div>
          </div>
        </div>

        <div class="modal-foot-accent">
          <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-gold"><i class="fas fa-floppy-disk me-2"></i>Guardar examen</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.__examenes = <?= json_encode($jsonExamenes, JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', function () {
  var q = document.getElementById('buscarExamen');
  var f = document.getElementById('filtroEstadoExamen');
  var filas = Array.prototype.slice.call(document.querySelectorAll('#tablaExamenes tbody tr'));
  function filtrar() {
    var txt = (q && q.value || '').toLowerCase();
    var est = f && f.value || '';
    filas.forEach(function (tr) {
      var okTxt = tr.textContent.toLowerCase().indexOf(txt) !== -1;
      var okEst = !est || tr.getAttribute('data-estado') === est;
      tr.style.display = (okTxt && okEst) ? '' : 'none';
    });
  }
  if (q) q.addEventListener('input', filtrar);
  if (f) f.addEventListener('change', filtrar);

  var btn = document.getElementById('btnActualizar');
  if (btn) btn.addEventListener('click', function () {
    btn.classList.add('is-spinning');
    setTimeout(function () { location.reload(); }, 220);
  });
});
</script>