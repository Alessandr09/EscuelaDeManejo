<?php require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/ui.php';

$editar = null;
if (isset($_GET['editar'])) {
    $st = db()->prepare('SELECT * FROM cursos WHERE id = ?');
    $st->execute([(int) $_GET['editar']]);
    $editar = $st->fetch() ?: null;
}

try {
    $cursos = db()->query('SELECT * FROM cursos ORDER BY nombre')->fetchAll();
} catch (Exception $e) { $cursos = []; }

// Alumnos por curso
$porCurso = [];
try {
    foreach (db()->query('SELECT curso, COUNT(*) AS c FROM alumnos GROUP BY curso') as $r) {
        $porCurso[$r['curso']] = (int) $r['c'];
    }
} catch (Exception $e) {}

$activos = count(array_filter($cursos, fn($c) => $c['estado'] === 'Activo'));
$ingresos = array_sum(array_map(fn($c) => (float) $c['precio'], $cursos));
$puede = esdrive_puede('cursos', 'gestionar');

?>
<?= esdrive_page_head('Gestion de', 'cursos', 'Administra la oferta academica: precios, duracion y estados.',
    $puede ? '<button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalCurso">
        <i class="fas fa-plus"></i><span>Nuevo curso</span></button>' : '') ?>

<div class="glass panel">
  <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
    <div class="panel-title mb-0"><i class="fas fa-graduation-cap"></i>Cursos disponibles</div>
    <div class="d-flex gap-2 flex-wrap">
      <div class="input-group" style="width:230px">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" placeholder="Buscar curso..." id="buscarCurso">
      </div>
      <button type="button" class="btn btn-refresh" id="btnActualizar" title="Actualizar tabla">
        <i class="fas fa-rotate"></i><span>Actualizar</span>
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-glass" id="tablaCursos">
      <thead>
        <tr>
          <th>Curso</th>
          <th>Codigo</th>
          <th>Duracion</th>
          <th>Precio</th>
          <th>Alumnos</th>
          <th>Estado</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$cursos): ?>
          <tr class="fila-vacia"><td colspan="7" class="text-center" style="padding:28px;color:var(--text-mut)">
            <i class="fas fa-inbox me-2"></i>No hay cursos registrados.
          </td></tr>
        <?php endif; ?>
        <?php foreach ($cursos as $c): ?>
          <tr data-estado="<?= htmlspecialchars($c['estado']) ?>">
            <td>
              <div class="cell-user">
                <div class="cell-avatar"><i class="fas fa-graduation-cap"></i></div>
                <div>
                  <div class="cu-name"><?= htmlspecialchars($c['nombre']) ?></div>
                  <div class="cu-mail"><?= htmlspecialchars(mb_substr($c['descripcion'] ?? '', 0, 48)) ?><?= mb_strlen($c['descripcion'] ?? '') > 48 ? '...' : '' ?></div>
                </div>
              </div>
            </td>
            <td><span class="badge-soft b-info"><?= htmlspecialchars($c['codigo']) ?: '—' ?></span></td>
            <td><?= htmlspecialchars($c['duracion']) ?: '—' ?></td>
            <td style="font-weight:700;color:var(--gold)">$<?= number_format((float) $c['precio'], 2) ?></td>
            <td><?= (int) ($porCurso[$c['nombre']] ?? 0) ?></td>
            <td><?= esdrive_badge($c['estado']) ?></td>
            <td class="text-end">
              <?php if ($puede): ?>
                <a href="?v=cursos&editar=<?= (int) $c['id'] ?>" class="btn-act edit" title="Editar"><i class="fas fa-pen"></i></a>
                <button type="button" class="btn-act del" title="Eliminar"
                        data-eliminar="<?= (int) $c['id'] ?>" data-que="curso" data-nombre="<?= htmlspecialchars($c['nombre']) ?>">
                  <i class="fas fa-trash"></i>
                </button>
                <form method="post" id="formEliminar<?= (int) $c['id'] ?>" class="d-none">
                  <input type="hidden" name="accion" value="eliminar_curso">
                  <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
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

  <?= esdrive_paginacion('tablaCursos') ?>
</div>

<!-- ============ MODAL CURSO ============ -->
<div class="modal fade" id="modalCurso" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content modal-esdrive">
      <div class="modal-head-accent">
        <h5 class="modal-title"><i class="fas fa-graduation-cap me-2"></i><?= $editar ? 'Editar curso' : 'Nuevo curso' ?></h5>
        <button type="button" class="btn-close-accent" data-bs-dismiss="modal" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
      </div>
      <form method="post">
        <input type="hidden" name="accion" value="guardar_curso">
        <input type="hidden" name="id" value="<?= $editar ? (int) $editar['id'] : '' ?>">

        <div class="modal-body-accent">
          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label">Nombre del curso</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Clase B - Particular"
                       value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>">
              </div>
            </div>
            <div class="col-md-5">
              <label class="form-label">Codigo</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                <input type="text" name="codigo" class="form-control" placeholder="Ej: CB"
                       value="<?= htmlspecialchars($editar['codigo'] ?? '') ?>">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Precio (USD)</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                <input type="number" step="0.01" min="0" name="precio" class="form-control" value="<?= htmlspecialchars($editar['precio'] ?? '0') ?>">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Duracion</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-clock"></i></span>
                <input type="text" name="duracion" class="form-control" placeholder="Ej: 4 semanas"
                       value="<?= htmlspecialchars($editar['duracion'] ?? '') ?>">
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Estado</label>
              <div class="switch-estado">
                <input type="hidden" name="estado" value="Inactivo">
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="estado" value="Activo"
                         id="chkEstadoCurso" <?= (($editar['estado'] ?? 'Activo') === 'Activo') ? 'checked' : '' ?>>
                </div>
                <label class="form-check-label" for="chkEstadoCurso">Curso activo</label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Descripcion</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                <textarea name="descripcion" class="form-control" rows="3" placeholder="Que incluye el curso..."><?= htmlspecialchars($editar['descripcion'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-foot-accent">
          <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-gold"><i class="fas fa-floppy-disk me-2"></i>Guardar curso</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  esdriveTabla({
    tabla: 'tablaCursos',
    buscar: 'buscarCurso'
  });

  var btn = document.getElementById('btnActualizar');
  if (btn) btn.addEventListener('click', function () {
    btn.classList.add('is-spinning');
    setTimeout(function () { location.reload(); }, 220);
  });

  <?php if ($editar): ?>
  new bootstrap.Modal(document.getElementById('modalCurso')).show();
  <?php endif; ?>
});
</script>
