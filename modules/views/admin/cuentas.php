<?php require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/ui.php';

$editar = null;
if (isset($_GET['editar'])) {
    $st = db()->prepare('SELECT * FROM alumnos WHERE id = ?');
    $st->execute([(int) $_GET['editar']]);
    $editar = $st->fetch() ?: null;
}

try {
    $alumnos = db()->query('SELECT * FROM alumnos ORDER BY creado_en DESC, id DESC')->fetchAll();
} catch (Exception $e) { $alumnos = []; }

$total  = count($alumnos);
$activos = count(array_filter($alumnos, fn($a) => $a['estado'] === 'Activo'));
$pend    = count(array_filter($alumnos, fn($a) => $a['estado'] === 'Pendiente'));
$cursos  = esdrive_opciones_cursos();
$puede   = esdrive_puede('cuentas', 'gestionar');

?>
<?= esdrive_page_head('Cuentas', 'de alumnos', 'Administra los registros y accesos de los estudiantes.',
    $puede ? '<button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalAlumno">
        <i class="fas fa-plus"></i><span>Nueva cuenta</span></button>' : '') ?>

<div class="glass panel">
  <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
    <div class="panel-title mb-0"><i class="fas fa-users"></i>Listado general</div>
    <div class="d-flex gap-2 flex-wrap">
      <div class="input-group" style="width:230px">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" placeholder="Buscar alumno..." id="buscarCuenta">
      </div>
      <div class="input-group" style="width:190px">
        <span class="input-group-text"><i class="fas fa-filter"></i></span>
        <select class="form-select" id="filtroEstadoCuenta">
          <option value="">Todos los estados</option>
          <option value="Activo">Activo</option>
          <option value="Pendiente">Pendiente</option>
          <option value="Inactivo">Inactivo</option>
        </select>
      </div>
      <button type="button" class="btn btn-refresh" id="btnActualizar" title="Actualizar tabla">
        <i class="fas fa-rotate"></i><span>Actualizar</span>
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-glass" id="tablaCuentas">
      <thead>
        <tr>
          <th>Alumno</th>
          <th>Telefono</th>
          <th>Curso</th>
          <th>Ingreso</th>
          <th>Estado</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$alumnos): ?>
          <tr class="fila-vacia"><td colspan="6" class="text-center" style="padding:28px;color:var(--text-mut)">
            <i class="fas fa-inbox me-2"></i>No hay cuentas de alumnos registradas.
          </td></tr>
        <?php endif; ?>
        <?php foreach ($alumnos as $a): ?>
          <tr data-estado="<?= htmlspecialchars($a['estado']) ?>">
            <td>
              <div class="cell-user">
                <div class="cell-avatar" style="background:<?= esdrive_color_avatar($a['nombre']) ?>"><?= esdrive_ini($a['nombre']) ?></div>
                <div>
                  <div class="cu-name"><?= htmlspecialchars($a['nombre']) ?></div>
                  <div class="cu-mail"><?= htmlspecialchars($a['email']) ?></div>
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($a['telefono']) ?: '&mdash;' ?></td>
            <td><span class="badge-soft b-info"><?= htmlspecialchars($a['curso']) ?: '&mdash;' ?></span></td>
            <td><?= date('d/m/Y', strtotime($a['creado_en'])) ?></td>
            <td><?= esdrive_badge($a['estado']) ?></td>
            <td class="text-end">
              <?php if ($puede): ?>
                <a href="?v=cuentas&editar=<?= (int) $a['id'] ?>" class="btn-act edit" title="Editar"><i class="fas fa-pen"></i></a>
                <button type="button" class="btn-act del" title="Eliminar"
                        data-eliminar="<?= (int) $a['id'] ?>" data-que="cuenta" data-nombre="<?= htmlspecialchars($a['nombre']) ?>">
                  <i class="fas fa-trash"></i>
                </button>
                <form method="post" id="formEliminar<?= (int) $a['id'] ?>" class="d-none">
                  <input type="hidden" name="accion" value="eliminar_alumno">
                  <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
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

  <?= esdrive_paginacion('tablaCuentas') ?>
</div>

<!-- ============ MODAL CUENTA ============ -->
<div class="modal fade" id="modalAlumno" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content modal-esdrive">
      <div class="modal-head-accent">
        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i><?= $editar ? 'Editar cuenta' : 'Nueva cuenta' ?></h5>
        <button type="button" class="btn-close-accent" data-bs-dismiss="modal" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
      </div>
      <form method="post">
        <input type="hidden" name="accion" value="guardar_alumno">
        <input type="hidden" name="id" value="<?= $editar ? (int) $editar['id'] : '' ?>">

        <div class="modal-body-accent">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre completo</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Maria Lopez"
                       value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Correo electronico</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" name="email" class="form-control" placeholder="alumno@email.com"
                       value="<?= htmlspecialchars($editar['email'] ?? '') ?>">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Telefono</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                <input type="text" name="telefono" class="form-control" placeholder="+503 7000-0000"
                       value="<?= htmlspecialchars($editar['telefono'] ?? '') ?>">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Curso</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                <select name="curso" class="form-select">
                  <option value="">Sin curso asignado</option>
                  <?php foreach ($cursos as $c): ?>
                    <option value="<?= htmlspecialchars($c['nombre']) ?>" <?= (($editar['curso'] ?? '') === $c['nombre']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($c['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Estado</label>
              <div class="switch-estado">
                <input type="hidden" name="estado" value="Inactivo">
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="estado" value="Activo"
                         id="chkEstadoCuenta" <?= (($editar['estado'] ?? 'Activo') === 'Activo') ? 'checked' : '' ?>>
                </div>
                <label class="form-check-label" for="chkEstadoCuenta">Cuenta activa (puede inscribirse y presentar examenes)</label>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-foot-accent">
          <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-gold"><i class="fas fa-floppy-disk me-2"></i>Guardar cuenta</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  esdriveTabla({
    tabla: 'tablaCuentas',
    buscar: 'buscarCuenta',
    filtros: [{ id: 'filtroEstadoCuenta', attr: 'data-estado' }]
  });

  var btn = document.getElementById('btnActualizar');
  if (btn) btn.addEventListener('click', function () {
    btn.classList.add('is-spinning');
    setTimeout(function () { location.reload(); }, 220);
  });

  <?php if ($editar): ?>
  new bootstrap.Modal(document.getElementById('modalAlumno')).show();
  <?php endif; ?>
});
</script>