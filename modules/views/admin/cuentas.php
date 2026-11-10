<<<<<<< HEAD
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

$kpis = [
    ['fa-users',        $total,    'Alumnos totales',  'b-info'],
    ['fa-user-check',   $activos,  'Activos',          'b-active'],
    ['fa-user-clock',   $pend,     'Pendientes',       'b-pend'],
    ['fa-graduation-cap', count($cursos), 'Cursos',    'b-info'],
];
?>
<?= esdrive_page_head('Cuentas', 'de alumnos', 'Administra los registros y accesos de los estudiantes.',
    $puede ? '<button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalAlumno">
        <i class="fas fa-plus"></i><span>Nueva cuenta</span></button>' : '') ?>

<div class="row g-2 mb-3">
  <?php foreach ($kpis as $k): [$icon, $val, $lbl, $cls] = $k; ?>
  <div class="col-xl-3 col-md-6">
    <div class="glass kpi-card">
      <div class="kpi-icon"><i class="fas <?= $icon ?>"></i></div>
      <div class="kpi-info"><div class="kpi-val"><?= $val ?></div><div class="kpi-lbl"><?= $lbl ?></div></div>
    </div>
  </div>
  <?php endforeach; ?>
=======
<?php require_once __DIR__ . '/../../../php/base.php'; ?>
<div class="page-head d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <h3><span>Cuentas</span> de alumnos</h3>
    <p>Administra los registros y accesos de los estudiantes.</p>
  </div>
  <button class="btn btn-gold btn-sm" onclick="Swal.fire({icon:'success',title:'Demo',text:'Aqui se abrira el formulario para crear cuentas.',confirmButtonColor:'#e9a537'})">
    <i class="fas fa-user-plus me-2"></i>Nueva cuenta
  </button>
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
</div>

<div class="glass panel">
  <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
    <div class="panel-title mb-0"><i class="fas fa-users"></i>Listado general</div>
    <div class="d-flex gap-2 flex-wrap">
<<<<<<< HEAD
      <div class="input-group" style="width:230px">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" placeholder="Buscar alumno..." id="buscarCuenta">
      </div>
      <select class="form-select" style="width:160px" id="filtroEstadoCuenta">
        <option value="">Todos los estados</option>
        <option value="Activo">Activo</option>
        <option value="Pendiente">Pendiente</option>
        <option value="Inactivo">Inactivo</option>
      </select>
      <button type="button" class="btn btn-refresh" id="btnActualizar" title="Actualizar tabla">
        <i class="fas fa-rotate"></i><span>Actualizar</span>
      </button>
=======
      <div class="input-group" style="width:240px">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" placeholder="Buscar alumno..." id="buscarCuenta">
      </div>
      <select class="form-select" style="width:160px">
        <option>Todos los cursos</option>
        <option>Clase A</option>
        <option>Clase B</option>
        <option>Clase C</option>
      </select>
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-glass" id="tablaCuentas">
      <thead>
<<<<<<< HEAD
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
          <tr><td colspan="6" class="text-center" style="padding:28px;color:var(--text-mut)">
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
            <td><?= htmlspecialchars($a['telefono']) ?: '—' ?></td>
            <td><span class="badge-soft b-info"><?= htmlspecialchars($a['curso']) ?: '—' ?></span></td>
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
=======
        <tr><th>Alumno</th><th>Telefono</th><th>Curso</th><th>Registro</th><th>Estado</th><th class="text-end">Acciones</th></tr>
      </thead>
      <tbody>
        <?php
        $cuentas = [
          ['Maria Lopez','maria@email.com','+503 7001-1111','Clase B','2026-10-24','Activo','b-active','ML','#ffd76a'],
          ['Carlos Ramirez','carlos@email.com','+503 7002-2222','Clase A','2026-10-23','Pendiente','b-pend','CR','#a3b6f5'],
          ['Ana Martinez','ana@email.com','+503 7003-3333','Clase C','2026-10-22','Activo','b-active','AM','#5fd689'],
          ['Jose Hernandez','jose@email.com','+503 7004-4444','Clase B','2026-10-21','Inactivo','b-off','JH','#f07276'],
          ['Laura Diaz','laura@email.com','+503 7005-5555','Renovacion','2026-10-20','Activo','b-active','LD','#ffd76a'],
          ['Roberto Sanchez','roberto@email.com','+503 7006-6666','Clase C','2026-10-19','Activo','b-active','RS','#a3b6f5'],
          ['Gabriela Reyes','gabriela@email.com','+503 7007-7777','Clase A','2026-10-18','Pendiente','b-pend','GR','#5fd689'],
        ];
        foreach ($cuentas as $c):
          [$nom,$mail,$tel,$curso,$f,$est,$cls,$ini,$col] = $c;
        ?>
        <tr>
          <td>
            <div class="cell-user">
              <div class="cell-avatar" style="background:<?= $col ?>"><?= $ini ?></div>
              <div><div class="cu-name"><?= $nom ?></div><div class="cu-mail"><?= $mail ?></div></div>
            </div>
          </td>
          <td><?= $tel ?></td>
          <td><?= $curso ?></td>
          <td data-fecha="<?= $f ?>"></td>
          <td><span class="badge-soft <?= $cls ?>"><?= $est ?></span></td>
          <td class="text-end">
            <a href="#" class="btn-act" title="Ver"><i class="fas fa-eye"></i></a>
            <a href="#" class="btn-act edit" title="Editar"><i class="fas fa-pen"></i></a>
            <a href="#" class="btn-act del" title="Eliminar"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<<<<<<< HEAD
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
              <input type="text" name="nombre" class="form-control" required placeholder="Ej: Maria Lopez"
                     value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Correo electronico</label>
              <input type="email" name="email" class="form-control" placeholder="alumno@email.com"
                     value="<?= htmlspecialchars($editar['email'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Telefono</label>
              <input type="text" name="telefono" class="form-control" placeholder="+503 7000-0000"
                     value="<?= htmlspecialchars($editar['telefono'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Curso</label>
              <select name="curso" class="form-select">
                <option value="">Sin curso asignado</option>
                <?php foreach ($cursos as $c): ?>
                  <option value="<?= htmlspecialchars($c['nombre']) ?>" <?= (($editar['curso'] ?? '') === $c['nombre']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Estado</label>
              <select name="estado" class="form-select">
                <?php foreach (['Activo', 'Pendiente', 'Inactivo'] as $est): ?>
                  <option <?= (($editar['estado'] ?? 'Activo') === $est) ? 'selected' : '' ?>><?= $est ?></option>
                <?php endforeach; ?>
              </select>
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
  var q = document.getElementById('buscarCuenta');
  var f = document.getElementById('filtroEstadoCuenta');
  var filas = Array.prototype.slice.call(document.querySelectorAll('#tablaCuentas tbody tr'));
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

  <?php if ($editar): ?>
  new bootstrap.Modal(document.getElementById('modalAlumno')).show();
  <?php endif; ?>
});
</script>
=======
<script>
document.getElementById('buscarCuenta').addEventListener('input', function () {
  var q = this.value.toLowerCase();
  document.querySelectorAll('#tablaCuentas tbody tr').forEach(function (fila) {
    fila.style.display = fila.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
