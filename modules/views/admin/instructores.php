<?php require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/ui.php';

$editar = null;
if (isset($_GET['editar'])) {
    $st = db()->prepare('SELECT * FROM empleados WHERE id = ?');
    $st->execute([(int) $_GET['editar']]);
    $editar = $st->fetch() ?: null;
}

try {
    $instructores = db()->query("SELECT * FROM empleados WHERE cargo LIKE '%nstructor%' OR cargo LIKE '%Maestro%' OR cargo LIKE '%Profesor%' ORDER BY nombre")->fetchAll();
} catch (Exception $e) { $instructores = []; }

// Si no hay coincidencias por cargo, mostramos todos (para no dejar la vista vacia)
if (!$instructores) {
    try { $instructores = db()->query('SELECT * FROM empleados ORDER BY nombre')->fetchAll(); }
    catch (Exception $e) { $instructores = []; }
}

// Horarios de cada instructor
$horariosPorEmp = [];
try {
    foreach (db()->query('SELECT empleado_id, COUNT(*) AS c FROM horarios GROUP BY empleado_id') as $r) {
        $horariosPorEmp[(int) $r['empleado_id']] = (int) $r['c'];
    }
} catch (Exception $e) {}

// Vehiculos asignados
$vehiculosPorEmp = [];
try {
    foreach (db()->query('SELECT empleado_id, COUNT(*) AS c FROM vehiculos WHERE empleado_id IS NOT NULL GROUP BY empleado_id') as $r) {
        $vehiculosPorEmp[(int) $r['empleado_id']] = (int) $r['c'];
    }
} catch (Exception $e) {}

$puede = esdrive_puede('empleados', 'gestionar');

?>
<?= esdrive_page_head('Nuestros', 'instructores', 'Equipo docente: certificaciones, contacto y carga horaria.',
    $puede ? '<button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalInstructor">
        <i class="fas fa-plus"></i><span>Nuevo instructor</span></button>' : '') ?>

<div class="glass panel">
  <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
    <div class="panel-title mb-0"><i class="fas fa-chalkboard-teacher"></i>Equipo de instructores</div>
    <div class="d-flex gap-2 flex-wrap">
      <div class="input-group" style="width:230px">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" placeholder="Buscar instructor..." id="buscarInstructor">
      </div>
      <button type="button" class="btn btn-refresh" id="btnActualizar" title="Actualizar tabla">
        <i class="fas fa-rotate"></i><span>Actualizar</span>
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-glass" id="tablaInstructores">
      <thead>
        <tr>
          <th>Instructor</th>
          <th>Cargo</th>
          <th>Contacto</th>
          <th>Horarios</th>
          <th>Vehiculos</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$instructores): ?>
          <tr class="fila-vacia"><td colspan="6" class="text-center" style="padding:28px;color:var(--text-mut)">
            <i class="fas fa-inbox me-2"></i>No hay instructores registrados.
          </td></tr>
        <?php endif; ?>
        <?php foreach ($instructores as $e): ?>
          <tr>
            <td>
              <div class="cell-user">
                <?php if ($e['imagen']): ?>
                  <div class="cell-thumb"><img src="<?= htmlspecialchars($e['imagen']) ?>" alt="<?= htmlspecialchars($e['nombre']) ?>"></div>
                <?php else: ?>
                  <div class="cell-avatar" style="background:<?= esdrive_color_avatar($e['nombre']) ?>"><?= esdrive_ini($e['nombre']) ?></div>
                <?php endif; ?>
                <div>
                  <div class="cu-name"><?= htmlspecialchars($e['nombre']) ?></div>
                  <div class="cu-mail"><?= htmlspecialchars($e['email']) ?></div>
                </div>
              </div>
            </td>
            <td><span class="badge-soft b-info"><?= htmlspecialchars($e['cargo']) ?: '—' ?></span></td>
            <td><?= htmlspecialchars($e['telefono']) ?: '—' ?></td>
            <td><span class="badge-soft b-active"><i class="fas fa-calendar me-1"></i><?= (int) ($horariosPorEmp[(int) $e['id']] ?? 0) ?></span></td>
            <td><span class="badge-soft b-pend"><i class="fas fa-car me-1"></i><?= (int) ($vehiculosPorEmp[(int) $e['id']] ?? 0) ?></span></td>
            <td class="text-end">
              <?php if ($puede): ?>
                <a href="?v=instructores&editar=<?= (int) $e['id'] ?>" class="btn-act edit" title="Editar"><i class="fas fa-pen"></i></a>
                <button type="button" class="btn-act del" title="Eliminar"
                        data-eliminar="<?= (int) $e['id'] ?>" data-que="instructor" data-nombre="<?= htmlspecialchars($e['nombre']) ?>">
                  <i class="fas fa-trash"></i>
                </button>
                <form method="post" id="formEliminar<?= (int) $e['id'] ?>" class="d-none">
                  <input type="hidden" name="accion" value="eliminar_empleado">
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

  <?= esdrive_paginacion('tablaInstructores') ?>
</div>

<!-- ============ MODAL INSTRUCTOR ============ -->
<div class="modal fade" id="modalInstructor" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content modal-esdrive">
      <div class="modal-head-accent">
        <h5 class="modal-title"><i class="fas fa-chalkboard-teacher me-2"></i><?= $editar ? 'Editar instructor' : 'Nuevo instructor' ?></h5>
        <button type="button" class="btn-close-accent" data-bs-dismiss="modal" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
      </div>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="guardar_empleado">
        <input type="hidden" name="id" value="<?= $editar ? (int) $editar['id'] : '' ?>">

        <div class="modal-body-accent">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre completo</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Carlos Ramirez"
                       value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Cargo</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                <input type="text" name="cargo" class="form-control" placeholder="Ej: Instructor certificado"
                       value="<?= htmlspecialchars($editar['cargo'] ?? 'Instructor certificado') ?>">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Telefono</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                <input type="text" name="telefono" class="form-control" placeholder="+503 2222-3333"
                       value="<?= htmlspecialchars($editar['telefono'] ?? '') ?>">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Correo</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" name="email" class="form-control" placeholder="instructor@esdrive.com"
                       value="<?= htmlspecialchars($editar['email'] ?? '') ?>">
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Foto (opcional)</label>
              <div class="dropzone dropzone-esdrive" id="dzImagenInstructor"></div>
              <input type="file" name="imagen" accept="image/*" id="inputImagenInstructor" class="d-none">
            </div>
          </div>
        </div>

        <div class="modal-foot-accent">
          <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-gold"><i class="fas fa-floppy-disk me-2"></i>Guardar instructor</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  esdriveTabla({
    tabla: 'tablaInstructores',
    buscar: 'buscarInstructor'
  });

  var btn = document.getElementById('btnActualizar');
  if (btn) btn.addEventListener('click', function () {
    btn.classList.add('is-spinning');
    setTimeout(function () { location.reload(); }, 220);
  });

  if (typeof esdriveDropzone === 'function') {
    esdriveDropzone({ dropzone: 'dzImagenInstructor', input: 'inputImagenInstructor' });
  }

  <?php if ($editar): ?>
  new bootstrap.Modal(document.getElementById('modalInstructor')).show();
  <?php endif; ?>
});
</script>
