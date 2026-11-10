<?php require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/db.php';

$flash = $_GET['flash'] ?? '';
$editar = null;
if (isset($_GET['editar'])) {
    $st = db()->prepare('SELECT * FROM vehiculos WHERE id = ?');
    $st->execute([(int) $_GET['editar']]);
    $editar = $st->fetch() ?: null;
}
$vehiculos = db()->query('SELECT * FROM vehiculos ORDER BY id DESC')->fetchAll();
$disponibles = count(array_filter($vehiculos, fn($v) => $v['estado'] === 'disponible'));

// Empleados para el select "Empleado asignado"
try { $empleados = db()->query('SELECT id, nombre, cargo FROM empleados ORDER BY nombre')->fetchAll(); }
catch (Exception $e) { $empleados = []; }
$empById = [];
foreach ($empleados as $e) { $empById[(int) $e['id']] = $e; }
?>
<div class="page-head d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <h3><span>Vehiculos</span> disponibles</h3>
    <p>Administra la flota: cuando estan libres, en uso y sus tiempos.</p>
  </div>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <span class="badge-soft b-active"><i class="fas fa-circle-check me-1"></i> <?= $disponibles ?> libres</span>
    <span class="badge-soft b-info"><i class="fas fa-car-side me-1"></i> <?= count($vehiculos) ?> en flota</span>
  </div>
</div>

<?php if ($flash === 'ok'): ?>
  <div class="alert alert-ok"><i class="fas fa-circle-check me-2"></i>Operacion realizada correctamente.</div>
<?php elseif ($flash === 'error'): ?>
  <div class="alert"><i class="fas fa-circle-exclamation me-2"></i>No se pudo completar la operacion.</div>
<?php endif; ?>

<div class="glass panel">
  <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
    <div class="panel-title mb-0"><i class="fas fa-car-side"></i>Flota de vehiculos</div>
    <div class="d-flex gap-2 flex-wrap">
      <div class="input-group" style="width:220px">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" placeholder="Buscar vehiculo..." id="buscarVehiculo">
      </div>
      <select class="form-select" style="width:170px" id="filtroEstado">
        <option value="">Todos los estados</option>
        <option value="disponible">Disponible</option>
        <option value="en_uso">En uso</option>
      </select>
      <!-- Actualizar: recarga la tabla por si hay cambios -->
      <button type="button" class="btn btn-refresh" id="btnActualizar" title="Actualizar tabla">
        <i class="fas fa-rotate"></i><span>Actualizar</span>
      </button>
      <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalVehiculo">
        <i class="fas fa-plus"></i><span>Agregar</span>
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-glass" id="tablaVehiculos">
      <thead>
        <tr>
          <th>Vehiculo</th>
          <th>Tipo</th>
          <th>Estado</th>
          <th>Empleado</th>
          <th>Horario</th>
          <th>Tiempo en uso</th>
          <th>Tiempo libre</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$vehiculos): ?>
          <tr><td colspan="8" class="text-center" style="padding:28px;color:var(--text-mut)">
            <i class="fas fa-inbox me-2"></i>No hay vehiculos registrados. Pulsa "Agregar" para crear el primero.
          </td></tr>
        <?php endif; ?>
        <?php foreach ($vehiculos as $v): ?>
          <tr data-estado="<?= htmlspecialchars($v['estado']) ?>">
            <td>
              <div class="cell-user">
                <?php if ($v['imagen']): ?>
                  <div class="cell-thumb"><img src="<?= htmlspecialchars($v['imagen']) ?>" alt="<?= htmlspecialchars($v['nombre']) ?>"></div>
                <?php else: ?>
                  <div class="cell-avatar"><i class="fas fa-car"></i></div>
                <?php endif; ?>
                <div>
                  <div class="cu-name"><?= htmlspecialchars($v['nombre']) ?></div>
                  <div class="cu-mail"><?= htmlspecialchars($v['placa']) ?></div>
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($v['tipo']) ?></td>
            <td>
              <?php if ($v['estado'] === 'disponible'): ?>
                <span class="badge-soft b-active"><i class="fas fa-circle-check me-1"></i>Disponible</span>
              <?php else: ?>
                <span class="badge-soft b-pend"><i class="fas fa-clock me-1"></i>En uso</span>
              <?php endif; ?>
            </td>
            <td>
              <?php $emp = $v['empleado_id'] ? ($empById[(int) $v['empleado_id']] ?? null) : null; ?>
              <?php if ($emp): ?>
                <span class="badge-soft b-info"><i class="fas fa-user-tie me-1"></i><?= htmlspecialchars($emp['nombre']) ?></span>
              <?php else: ?>
                <span style="color:var(--text-mut)">Sin asignar</span>
              <?php endif; ?>
            </td>
            <td class="cell-horario">
              <?php if (!empty($v['disponible_desde'])): ?>
                <div><i class="fas fa-unlock me-1"></i><?= date('d/m H:i', strtotime($v['disponible_desde'])) ?> <small>(libre)</small></div>
              <?php endif; ?>
              <?php if (!empty($v['proximo_uso'])): ?>
                <div><i class="fas fa-calendar-check me-1"></i><?= date('d/m H:i', strtotime($v['proximo_uso'])) ?> <small>(uso)</small></div>
              <?php endif; ?>
              <?php if (empty($v['disponible_desde']) && empty($v['proximo_uso'])): ?><span style="color:var(--text-mut)">—</span><?php endif; ?>
            </td>
            <td><?= htmlspecialchars($v['tiempo_uso']) ?: '—' ?></td>
            <td><?= htmlspecialchars($v['tiempo_libre']) ?: '—' ?></td>
            <td class="text-end">
              <a href="?v=vehiculos&editar=<?= (int) $v['id'] ?>" class="btn-act edit" title="Editar"><i class="fas fa-pen"></i></a>
              <button type="button" class="btn-act del" title="Eliminar"
                      data-eliminar="<?= (int) $v['id'] ?>" data-nombre="<?= htmlspecialchars($v['nombre']) ?>">
                <i class="fas fa-trash"></i>
              </button>
              <form method="post" id="formEliminar<?= (int) $v['id'] ?>" class="d-none">
                <input type="hidden" name="accion" value="eliminar_vehiculo">
                <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ============ MODAL AGREGAR / EDITAR ============ -->
<div class="modal fade" id="modalVehiculo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content modal-esdrive">
      <div class="modal-head-accent">
        <h5 class="modal-title"><i class="fas fa-car-side me-2"></i><?= $editar ? 'Editar Vehiculo' : 'Agregar Vehiculo' ?></h5>
        <button type="button" class="btn-close-accent" data-bs-dismiss="modal" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
      </div>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="guardar_vehiculo">
        <input type="hidden" name="id" value="<?= $editar ? (int) $editar['id'] : '' ?>">

        <div class="modal-body-accent">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" required
                     placeholder="Ej: Sedan manual" value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Placa</label>
              <input type="text" name="placa" class="form-control" placeholder="ESD-000"
                     value="<?= htmlspecialchars($editar['placa'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Tipo</label>
              <input type="text" name="tipo" class="form-control" placeholder="Sedan"
                     value="<?= htmlspecialchars($editar['tipo'] ?? '') ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Estado</label>
              <select name="estado" class="form-select">
                <option value="disponible" <?= ($editar['estado'] ?? 'disponible') === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                <option value="en_uso" <?= ($editar['estado'] ?? '') === 'en_uso' ? 'selected' : '' ?>>En uso</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Disponible desde</label>
              <input type="datetime-local" name="disponible_desde" class="form-control"
                     value="<?= !empty($editar['disponible_desde']) ? date('Y-m-d\TH:i', strtotime($editar['disponible_desde'])) : '' ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Proximo uso</label>
              <input type="datetime-local" name="proximo_uso" class="form-control"
                     value="<?= !empty($editar['proximo_uso']) ? date('Y-m-d\TH:i', strtotime($editar['proximo_uso'])) : '' ?>">
              <small class="hint">Cuando se usara de nuevo. Al faltar 1 hora corre una cuenta regresiva en el carrusel.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label">Empleado asignado</label>
              <select name="empleado_id" class="form-select">
                <option value="">Sin asignar</option>
                <?php foreach ($empleados as $emp): ?>
                  <option value="<?= (int) $emp['id'] ?>" <?= ((int)($editar['empleado_id'] ?? 0)) === (int)$emp['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($emp['nombre']) ?><?= $emp['cargo'] ? ' — ' . htmlspecialchars($emp['cargo']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small class="hint">Aparece en la ficha del vehiculo cuando esta en uso.</small>
            </div>

            <div class="col-md-6">
              <label class="form-label">Tiempo en uso</label>
              <input type="text" name="tiempo_uso" class="form-control" placeholder="Ej: 4 horas"
                     value="<?= htmlspecialchars($editar['tiempo_uso'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Tiempo libre</label>
              <input type="text" name="tiempo_libre" class="form-control" placeholder="Ej: 2 horas"
                     value="<?= htmlspecialchars($editar['tiempo_libre'] ?? '') ?>">
            </div>

            <div class="col-12">
              <label class="form-label">Imagen del vehiculo</label>
              <input type="file" name="imagen" class="form-control" accept="image/*" id="inputImagen">
              <small class="hint">JPG o PNG. Se muestra en la tabla y en el carrusel del dashboard.</small>
              <div class="preview-wrap d-none" id="previewWrap">
                <img id="previewImg" alt="Vista previa">
              </div>
              <?php if (!empty($editar['imagen'])): ?>
                <div class="preview-wrap"><img src="<?= htmlspecialchars($editar['imagen']) ?>" alt="Imagen actual"></div>
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
  var buscar = document.getElementById('buscarVehiculo');
  var filtro = document.getElementById('filtroEstado');
  var filas  = document.querySelectorAll('#tablaVehiculos tbody tr');

  function aplicar() {
    var q = (buscar.value || '').toLowerCase();
    var est = filtro.value;
    filas.forEach(function (f) {
      var okTexto = f.textContent.toLowerCase().includes(q);
      var okEstado = !est || f.getAttribute('data-estado') === est;
      f.style.display = (okTexto && okEstado) ? '' : 'none';
    });
  }
  if (buscar) buscar.addEventListener('input', aplicar);
  if (filtro) filtro.addEventListener('change', aplicar);

  // Actualizar: recarga la pagina (por si hay cambios en la BD)
  var btnAct = document.getElementById('btnActualizar');
  if (btnAct) btnAct.addEventListener('click', function () {
    btnAct.classList.add('is-spinning');
    setTimeout(function () { location.reload(); }, 220);
  });

  // Vista previa de la imagen
  var input = document.getElementById('inputImagen');
  if (input) input.addEventListener('change', function () {
    var wrap = document.getElementById('previewWrap');
    var img  = document.getElementById('previewImg');
    if (input.files && input.files[0]) {
      img.src = URL.createObjectURL(input.files[0]);
      wrap.classList.remove('d-none');
    }
  });

  // Si llegamos con ?editar=... abrir el modal
  <?php if ($editar): ?>
  var m = new bootstrap.Modal(document.getElementById('modalVehiculo'));
  m.show();
  <?php endif; ?>
});
</script>
