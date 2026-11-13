<?php require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/db.php';
require_once __DIR__ . '/../../../php/ui.php';

$flash = $_GET['flash'] ?? '';
$editar = null;
if (isset($_GET['editar'])) {
    $st = db()->prepare('SELECT * FROM empleados WHERE id = ?');
    $st->execute([(int) $_GET['editar']]);
    $editar = $st->fetch() ?: null;
}
$empleados = db()->query('SELECT * FROM empleados ORDER BY id DESC')->fetchAll();

// Cuantos vehiculos tiene asignados cada empleado
$asignados = [];
foreach (db()->query('SELECT empleado_id, COUNT(*) c FROM vehiculos WHERE empleado_id IS NOT NULL GROUP BY empleado_id') as $r) {
    $asignados[(int) $r['empleado_id']] = (int) $r['c'];
}
?>
<div class="page-head d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <h3><span>Empleados</span> de la escuela</h3>
    <p>Instructores y staff. Sus fotos aparecen en el carousel de la pagina principal.</p>
  </div>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <span class="badge-soft b-info"><i class="fas fa-id-badge me-1"></i> <?= count($empleados) ?> empleado(s)</span>
    <span class="badge-soft b-active"><i class="fas fa-camera me-1"></i> <?= count(array_filter($empleados, fn($e) => !empty($e['imagen']))) ?> con foto</span>
  </div>
</div>

<?php if ($flash === 'ok'): ?>
  <div class="alert alert-ok"><i class="fas fa-circle-check me-2"></i>Operacion realizada correctamente.</div>
<?php elseif ($flash === 'error'): ?>
  <div class="alert"><i class="fas fa-circle-exclamation me-2"></i>No se pudo completar la operacion.</div>
<?php endif; ?>

<div class="glass panel">
  <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
    <div class="panel-title mb-0"><i class="fas fa-users-gear"></i>Equipo ESDRIVE</div>
    <div class="d-flex gap-2 flex-wrap">
      <div class="input-group" style="width:220px">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" placeholder="Buscar empleado..." id="buscarEmpleado">
      </div>
      <button type="button" class="btn btn-refresh" id="btnActualizarEmp" title="Actualizar tabla">
        <i class="fas fa-rotate"></i><span>Actualizar</span>
      </button>
      <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalEmpleado">
        <i class="fas fa-plus"></i><span>Agregar</span>
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-glass" id="tablaEmpleados">
      <thead>
        <tr>
          <th>Empleado</th>
          <th>Cargo</th>
          <th>Telefono</th>
          <th>Email</th>
          <th>Vehiculos</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$empleados): ?>
          <tr class="fila-vacia"><td colspan="6" class="text-center" style="padding:28px;color:var(--text-mut)">
            <i class="fas fa-inbox me-2"></i>No hay empleados registrados. Pulsa "Agregar" para crear el primero.
          </td></tr>
        <?php endif; ?>
        <?php foreach ($empleados as $e): ?>
          <tr>
            <td>
              <div class="cell-user">
                <?php if ($e['imagen']): ?>
                  <div class="cell-thumb cell-thumb-round"><img src="<?= htmlspecialchars($e['imagen']) ?>" alt="<?= htmlspecialchars($e['nombre']) ?>"></div>
                <?php else: ?>
                  <div class="cell-avatar"><?= strtoupper(substr($e['nombre'], 0, 1)) ?></div>
                <?php endif; ?>
                <div>
                  <div class="cu-name"><?= htmlspecialchars($e['nombre']) ?></div>
                  <div class="cu-mail">#<?= (int) $e['id'] ?></div>
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($e['cargo']) ?: '—' ?></td>
            <td><?= htmlspecialchars($e['telefono']) ?: '—' ?></td>
            <td><?= htmlspecialchars($e['email']) ?: '—' ?></td>
            <td>
              <?php if (!empty($asignados[(int) $e['id']])): ?>
                <span class="badge-soft b-pend"><i class="fas fa-car-side me-1"></i><?= $asignados[(int) $e['id']] ?></span>
              <?php else: ?>
                <span style="color:var(--text-mut)">—</span>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <a href="?v=empleados&editar=<?= (int) $e['id'] ?>" class="btn-act edit" title="Editar"><i class="fas fa-pen"></i></a>
              <button type="button" class="btn-act del" title="Eliminar"
                      data-eliminar="<?= (int) $e['id'] ?>" data-que="empleado" data-nombre="<?= htmlspecialchars($e['nombre']) ?>">
                <i class="fas fa-trash"></i>
              </button>
              <form method="post" id="formEliminar<?= (int) $e['id'] ?>" class="d-none">
                <input type="hidden" name="accion" value="eliminar_empleado">
                <input type="hidden" name="id" value="<?= (int) $e['id'] ?>">
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?= esdrive_paginacion('tablaEmpleados', 10) ?>
</div>

<!-- ============ MODAL AGREGAR / EDITAR EMPLEADO ============ -->
<div class="modal fade" id="modalEmpleado" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content modal-esdrive">
      <div class="modal-head-accent">
        <h5 class="modal-title"><i class="fas fa-id-badge me-2"></i><?= $editar ? 'Editar Empleado' : 'Agregar Empleado' ?></h5>
        <button type="button" class="btn-close-accent" data-bs-dismiss="modal" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
      </div>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="guardar_empleado">
        <input type="hidden" name="id" value="<?= $editar ? (int) $editar['id'] : '' ?>">

        <div class="modal-body-accent">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre completo</label>
              <input type="text" name="nombre" class="form-control" required
                     placeholder="Ej: Carlos Ramírez" value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Cargo</label>
              <input type="text" name="cargo" class="form-control" placeholder="Ej: Instructor certificado"
                     value="<?= htmlspecialchars($editar['cargo'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Telefono</label>
              <input type="text" name="telefono" class="form-control" placeholder="+503 2222-3333"
                     value="<?= htmlspecialchars($editar['telefono'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" placeholder="correo@esdrive.com"
                     value="<?= htmlspecialchars($editar['email'] ?? '') ?>">
            </div>

            <div class="col-12">
              <label class="form-label">Foto del empleado</label>
              <input type="file" name="imagen" class="form-control" accept="image/*" id="inputImagenEmp">
              <small class="hint">JPG o PNG. Se muestra en el carousel "Nuestro equipo" de la pagina principal automaticamente.</small>
              <div class="preview-wrap d-none" id="previewWrapEmp">
                <img id="previewImgEmp" alt="Vista previa">
              </div>
              <?php if (!empty($editar['imagen'])): ?>
                <div class="preview-wrap"><img src="<?= htmlspecialchars($editar['imagen']) ?>" alt="Foto actual"></div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="modal-foot-accent">
          <button type="button" class="btn btn-glass btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-add btn-sm"><i class="fas fa-floppy-disk me-2"></i><?= $editar ? 'Guardar cambios' : 'Agregar' ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  esdriveTabla({
    tabla: 'tablaEmpleados',
    buscar: 'buscarEmpleado'
  });

  var btnAct = document.getElementById('btnActualizarEmp');
  if (btnAct) btnAct.addEventListener('click', function () {
    btnAct.classList.add('is-spinning');
    setTimeout(function () { location.reload(); }, 220);
  });

  var input = document.getElementById('inputImagenEmp');
  if (input) input.addEventListener('change', function () {
    var wrap = document.getElementById('previewWrapEmp');
    var img  = document.getElementById('previewImgEmp');
    if (input.files && input.files[0]) {
      img.src = URL.createObjectURL(input.files[0]);
      wrap.classList.remove('d-none');
    }
  });

  <?php if ($editar): ?>
  var m = new bootstrap.Modal(document.getElementById('modalEmpleado'));
  m.show();
  <?php endif; ?>
});
</script>
