<?php require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/ui.php';

$editar = null;
if (isset($_GET['editar'])) {
    $st = db()->prepare('SELECT * FROM usuarios WHERE id = ?');
    $st->execute([(int) $_GET['editar']]);
    $editar = $st->fetch() ?: null;
}

try {
    $usuarios = db()->query('SELECT * FROM usuarios ORDER BY rol, nombre')->fetchAll();
} catch (Exception $e) { $usuarios = []; }

$modulos  = esdrive_modulos();
$roles    = esdrive_roles();
$puede    = esdrive_puede('usuarios', 'gestionar');
$miId     = (int) ($_SESSION['esdrive_user_id'] ?? 0);
$nActivo  = count(array_filter($usuarios, fn($u) => (int) $u['activo'] === 1));

// Permisos del usuario en edicion
$permsEditar = [];
if ($editar) {
    $permsEditar = json_decode($editar['permisos'] ?? '[]', true) ?: [];
    if (($editar['rol'] ?? '') === 'administrador') {
        $permsEditar = array_fill_keys(array_keys($modulos), 'gestionar');
    }
}

$kpis = [
    ['fa-user-shield', count($usuarios), 'Usuarios',   'b-info'],
    ['fa-user-check',  $nActivo,         'Activos',    'b-active'],
    ['fa-users-gear',  count($roles),    'Roles',      'b-pend'],
    ['fa-layer-group', count($modulos),  'Modulos',    'b-info'],
];
?>
<?= esdrive_page_head('Usuarios y', 'permisos', 'Crea cuentas de acceso al panel y define que puede hacer cada una.',
    $puede ? '<button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalUsuario">
        <i class="fas fa-plus"></i><span>Nuevo usuario</span></button>' : '') ?>

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
    <div class="panel-title mb-0"><i class="fas fa-user-shield"></i>Cuentas de acceso</div>
    <div class="d-flex gap-2 flex-wrap">
      <div class="input-group" style="width:230px">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" placeholder="Buscar usuario..." id="buscarUsuario">
      </div>
      <select class="form-select" style="width:170px" id="filtroRol">
        <option value="">Todos los roles</option>
        <?php foreach (array_keys($roles) as $r): ?>
          <option value="<?= $r ?>"><?= esdrive_rol_label($r) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="button" class="btn btn-refresh" id="btnActualizar" title="Actualizar tabla">
        <i class="fas fa-rotate"></i><span>Actualizar</span>
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-glass" id="tablaUsuarios">
      <thead>
        <tr>
          <th>Usuario</th>
          <th>Rol</th>
          <th>Permisos</th>
          <th>Estado</th>
          <th>Creado</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$usuarios): ?>
          <tr class="fila-vacia"><td colspan="6" class="text-center" style="padding:28px;color:var(--text-mut)">
            <i class="fas fa-inbox me-2"></i>No hay usuarios registrados.
          </td></tr>
        <?php endif; ?>
        <?php foreach ($usuarios as $u):
          $esMiCuenta = ((int) $u['id'] === $miId);
          $p = json_decode($u['permisos'] ?? '[]', true) ?: [];
          if (($u['rol'] ?? '') === 'administrador') { $p = array_fill_keys(array_keys($modulos), 'gestionar'); }
          $nGes = count(array_filter($p, fn($x) => $x === 'gestionar'));
          $nVer = count(array_filter($p, fn($x) => $x === 'ver'));
          $rolCls = $u['rol'] === 'administrador' ? 'b-pend' : ($u['rol'] === 'editor' ? 'b-info' : 'b-active');
        ?>
          <tr data-rol="<?= htmlspecialchars($u['rol']) ?>">
            <td>
              <div class="cell-user">
                <div class="cell-avatar" style="background:<?= esdrive_color_avatar($u['nombre']) ?>"><?= esdrive_ini($u['nombre']) ?></div>
                <div>
                  <div class="cu-name"><?= htmlspecialchars($u['nombre']) ?>
                    <?php if ($esMiCuenta): ?><span class="badge-soft b-info" style="font-size:.62rem;padding:2px 6px">Tu cuenta</span><?php endif; ?>
                  </div>
                  <div class="cu-mail">@<?= htmlspecialchars($u['usuario']) ?></div>
                </div>
              </div>
            </td>
            <td><span class="badge-soft <?= $rolCls ?>"><i class="fas fa-user-tag me-1"></i><?= esdrive_rol_label($u['rol']) ?></span></td>
            <td>
              <span class="badge-soft b-active" title="Modulos con gestion"><?= $nGes ?> gestionar</span>
              <span class="badge-soft b-info" title="Modulos solo ver"><?= $nVer ?> ver</span>
            </td>
            <td><?= ((int) $u['activo'] === 1) ? esdrive_badge('Activo') : esdrive_badge('Inactivo') ?></td>
            <td><?= date('d/m/Y', strtotime($u['creado_en'])) ?></td>
            <td class="text-end">
              <?php if ($puede): ?>
                <a href="?v=usuarios&editar=<?= (int) $u['id'] ?>" class="btn-act edit" title="Editar permisos"><i class="fas fa-pen"></i></a>
                <?php if (!$esMiCuenta): ?>
                  <button type="button" class="btn-act del" title="Eliminar"
                          data-eliminar="<?= (int) $u['id'] ?>" data-que="usuario" data-nombre="<?= htmlspecialchars($u['nombre']) ?>">
                    <i class="fas fa-trash"></i>
                  </button>
                  <form method="post" id="formEliminar<?= (int) $u['id'] ?>" class="d-none">
                    <input type="hidden" name="accion" value="eliminar_usuario">
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                  </form>
                <?php endif; ?>
              <?php else: ?>
                <span class="badge-soft b-info"><i class="fas fa-eye me-1"></i>Solo lectura</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?= esdrive_paginacion('tablaUsuarios', 10) ?>
</div>

<!-- ============ MODAL USUARIO + MATRIZ DE PERMISOS ============ -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content modal-esdrive">
      <div class="modal-head-accent">
        <h5 class="modal-title"><i class="fas fa-user-shield me-2"></i><?= $editar ? 'Editar usuario y permisos' : 'Nuevo usuario' ?></h5>
        <button type="button" class="btn-close-accent" data-bs-dismiss="modal" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
      </div>
      <form method="post">
        <input type="hidden" name="accion" value="guardar_usuario">
        <input type="hidden" name="id" value="<?= $editar ? (int) $editar['id'] : '' ?>">

        <div class="modal-body-accent">
          <div class="row g-3 mb-4">
            <div class="col-md-5">
              <label class="form-label">Nombre completo</label>
              <input type="text" name="nombre" class="form-control" required placeholder="Ej: Ana Martinez"
                     value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Usuario (login)</label>
              <input type="text" name="usuario" class="form-control" required placeholder="ana"
                     value="<?= htmlspecialchars($editar['usuario'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Contrasena <?= $editar ? '(dejar vacio para no cambiar)' : '' ?></label>
              <input type="password" name="clave" class="form-control" <?= $editar ? '' : 'required' ?>
                     minlength="4" placeholder="Minimo 4 caracteres">
            </div>
            <div class="col-md-6">
              <label class="form-label">Rol</label>
              <select name="rol" class="form-select" id="selectRol" onchange="aplicarRol()">
                <?php foreach ($roles as $r => $desc): ?>
                  <option value="<?= $r ?>" <?= (($editar['rol'] ?? 'operador') === $r) ? 'selected' : '' ?>><?= esdrive_rol_label($r) ?> — <?= htmlspecialchars($desc) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" name="activo" id="chkActivo"
                       <?= (!$editar || (int) $editar['activo'] === 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="chkActivo">Cuenta activa (puede ingresar al admin)</label>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="panel-title mb-0" style="font-size:.9rem"><i class="fas fa-key me-1"></i>Permisos por modulo</div>
            <small class="text-muted">Sin acceso / Ver / Gestionar</small>
          </div>

          <div class="table-responsive">
            <table class="table table-glass" id="tablaPermisos">
              <thead>
                <tr>
                  <th style="width:34%">Modulo</th>
                  <th class="text-center">Sin acceso</th>
                  <th class="text-center">Ver</th>
                  <th class="text-center">Gestionar</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($modulos as $mod => $lbl):
                  $nivel = $permsEditar[$mod] ?? '';
                  if ($editar === null) {
                      // Por defecto al crear: dashboard y cuentas en "ver"
                      $nivel = in_array($mod, ['dashboard', 'cuentas'], true) ? 'ver' : '';
                  }
                ?>
                  <tr data-modulo="<?= $mod ?>">
                    <td style="font-weight:600"><i class="fas fa-lock-open me-1" style="color:var(--gold)"></i><?= htmlspecialchars($lbl) ?></td>
                    <td class="text-center">
                      <input class="form-check-input perm-radio" type="radio" name="permiso[<?= $mod ?>]"
                             value="" <?= $nivel === '' ? 'checked' : '' ?>>
                    </td>
                    <td class="text-center">
                      <input class="form-check-input perm-radio" type="radio" name="permiso[<?= $mod ?>]"
                             value="ver" <?= $nivel === 'ver' ? 'checked' : '' ?>>
                    </td>
                    <td class="text-center">
                      <input class="form-check-input perm-radio" type="radio" name="permiso[<?= $mod ?>]"
                             value="gestionar" <?= $nivel === 'gestionar' ? 'checked' : '' ?>>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="d-flex gap-2 flex-wrap mt-3">
            <button type="button" class="btn btn-glass btn-sm" onclick="marcarTodos('ver')"><i class="fas fa-eye me-1"></i>Todos "Ver"</button>
            <button type="button" class="btn btn-glass btn-sm" onclick="marcarTodos('gestionar')"><i class="fas fa-pen me-1"></i>Todos "Gestionar"</button>
            <button type="button" class="btn btn-glass btn-sm" onclick="marcarTodos('')"><i class="fas fa-ban me-1"></i>Ninguno</button>
          </div>
        </div>

        <div class="modal-foot-accent">
          <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-gold"><i class="fas fa-floppy-disk me-2"></i>Guardar usuario</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
/* Aplica los permisos predefinidos del rol seleccionado */
var PERMISOS_ROL = <?= json_encode([
    'administrador' => array_fill_keys(array_keys($modulos), 'gestionar'),
    'editor'        => esdrive_permisos_default('editor'),
    'operador'      => esdrive_permisos_default('operador'),
    'auditor'       => esdrive_permisos_default('auditor'),
], JSON_UNESCAPED_UNICODE) ?>;

function marcarTodos(nivel) {
  document.querySelectorAll('#tablaPermisos .perm-radio').forEach(function (r) {
    if (r.value === nivel) r.checked = true;
  });
}

function aplicarRol() {
  var sel = document.getElementById('selectRol');
  if (!sel) return;
  var defs = PERMISOS_ROL[sel.value];
  if (!defs) return;
  Object.keys(defs).forEach(function (mod) {
    var val = defs[mod];
    var radio = document.querySelector('#tablaPermisos input[name="permiso[' + mod + ']"][value="' + val + '"]');
    if (radio) radio.checked = true;
  });
}

document.addEventListener('DOMContentLoaded', function () {
  // Busqueda y filtro de la tabla
  esdriveTabla({
    tabla: 'tablaUsuarios',
    buscar: 'buscarUsuario',
    filtros: [{ id: 'filtroRol', attr: 'data-rol' }]
  });

  var btn = document.getElementById('btnActualizar');
  if (btn) btn.addEventListener('click', function () {
    btn.classList.add('is-spinning');
    setTimeout(function () { location.reload(); }, 220);
  });

  <?php if ($editar): ?>
  new bootstrap.Modal(document.getElementById('modalUsuario')).show();
  <?php endif; ?>
});
</script>
