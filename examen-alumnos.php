<?php
/**
 * examen-alumnos.php - Consola del instructor para el examen aprobatorio.
 *
 * El instructor inscribe al alumno, define la hora en la que se habilita su
 * examen y le entrega el codigo de acceso. La nota se guarda en la tabla
 * `examen_alumnos` (misma que leera la vista de examenes del panel admin).
 *
 * Reutiliza la sesion y los permisos del panel admin (php/auth.php):
 * solo entran los usuarios con acceso al modulo "examenes".
 */
require_once __DIR__ . '/php/base.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/config/examen_funciones.php';

session_start();

// Conexion tolerante a fallos: si MySQL no responde, se avisa en pantalla
try {
    $pdo = db();
} catch (Throwable $e) {
    $pdo = null;
}

$accion   = $_POST['accion'] ?? '';
$error    = $_SESSION['examen_admin_error'] ?? '';
$exito    = $_SESSION['examen_admin_ok'] ?? '';
unset($_SESSION['examen_admin_error'], $_SESSION['examen_admin_ok']);

if ($accion === 'logout') {
    session_destroy();
    header('Location: ' . BASE_URL . '/admin.php?v=login');
    exit;
}

/* ============ Permisos (mismo control que el panel) ============ */
$autorizado = false;
if ($pdo) {
    $usuario = esdrive_usuario();
    $autorizado = $usuario && esdrive_puede('examenes', 'ver');
    if (!$autorizado && !isset($_GET['alumno']) && $accion === '') {
        // Sin sesion o sin permiso: al login del panel
        header('Location: ' . BASE_URL . '/admin.php?v=login');
        exit;
    }
}

/* ============ Acciones del instructor ============ */
if ($autorizado && $pdo) {
    if ($accion === 'inscribir') {
        $nombre = trim($_POST['nombre'] ?? '');
        $dui    = trim($_POST['dui'] ?? '');
        $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
        $desde  = trim($_POST['habilitado_desde'] ?? '');

        if ($nombre === '') {
            $_SESSION['examen_admin_error'] = 'Escribe el nombre del alumno.';
        } elseif ($desde === '') {
            $_SESSION['examen_admin_error'] = 'Indica la hora en la que se habilita el examen.';
        } else {
            $codigo = $codigo !== '' ? $codigo : examen_generar_codigo($pdo);
            $ventana = examen_ventana(str_replace('T', ' ', $desde));

            try {
                $stmt = $pdo->prepare('INSERT INTO examen_alumnos
                    (nombre, dui, codigo, habilitado_desde, vence, total, estado)
                    VALUES (?, ?, ?, ?, ?, ?, "pendiente")');
                $stmt->execute([$nombre, $dui !== '' ? $dui : null, $codigo, $ventana['habilitado_desde'], $ventana['vence'], count(require __DIR__ . '/modules/data/preguntas.php')]);
                $_SESSION['examen_admin_ok'] = 'Alumno inscrito. Codigo de acceso: ' . $codigo;
            } catch (PDOException $e) {
                $_SESSION['examen_admin_error'] = 'No se pudo inscribir: ' . $e->getMessage();
            }
        }
        header('Location: ' . BASE_URL . '/examen-alumnos.php');
        exit;
    }

    if ($accion === 'rehabilitar') {
        $id    = (int) ($_POST['id'] ?? 0);
        $desde = trim($_POST['habilitado_desde'] ?? '');
        if ($id > 0 && $desde !== '') {
            $ventana = examen_ventana(str_replace('T', ' ', $desde));
            $stmt = $pdo->prepare('UPDATE examen_alumnos
                SET habilitado_desde = ?, vence = ?, estado = "pendiente", respuestas = NULL,
                    pregunta_actual = 0, inicio_real = NULL, aciertos = 0, porcentaje = 0,
                    nota = 0, terminado_en = NULL
                WHERE id = ?');
            $stmt->execute([$ventana['habilitado_desde'], $ventana['vence'], $id]);
            $_SESSION['examen_admin_ok'] = 'Examen re-habilitado.';
        }
        header('Location: ' . BASE_URL . '/examen-alumnos.php');
        exit;
    }

    if ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM examen_alumnos WHERE id = ?');
            $stmt->execute([$id]);
            $_SESSION['examen_admin_ok'] = 'Registro eliminado.';
        }
        header('Location: ' . BASE_URL . '/examen-alumnos.php');
        exit;
    }
}

/* ============ Detalle de un alumno ============ */
$detalle = null;
if ($autorizado && $pdo && isset($_GET['alumno'])) {
    $detalle = examen_alumno_por_id($pdo, (int) $_GET['alumno']);
}

/* ============ Listado ============ */
$alumnos = [];
if ($autorizado && $pdo) {
    $alumnos = $pdo->query('SELECT * FROM examen_alumnos ORDER BY habilitado_desde DESC, id DESC')->fetchAll();
}

$preguntasBanco = require __DIR__ . '/modules/data/preguntas.php';
$codigoSugerido = $pdo ? examen_generar_codigo($pdo) : '';
$ahoraInput = date('Y-m-d\TH:i');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Consola de examenes | <?= APP_NOMBRE ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/examen.css?v=3">
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/logo.svg">
</head>
<body class="examen-body">

<header class="examen-topbar no-print">
    <div class="examen-topbar-inner">
        <a class="examen-brand" href="<?= BASE_URL ?>/examen-alumnos.php">
            <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="Logo <?= APP_NOMBRE ?>" width="36" height="36">
            <span class="examen-brand-text">
                <b>Consola de examenes</b>
                <small><?= APP_NOMBRE ?> · <?= APP_TAGLINE ?></small>
            </span>
        </a>
        <div class="examen-topbar-meta">
            <a class="examen-pill examen-pill--ghost" href="<?= BASE_URL ?>/examen.php" target="_blank" rel="noopener">
                <i class="fas fa-display me-2"></i>Vista del alumno
            </a>
            <?php if ($autorizado): ?>
                <a class="examen-pill examen-pill--ghost" href="<?= BASE_URL ?>/admin.php?v=examenes">
                    <i class="fas fa-arrow-left me-2"></i>Panel admin
                </a>
                <a class="examen-pill examen-pill--ghost" href="<?= BASE_URL ?>/examen-alumnos.php?accion=logout">
                    <i class="fas fa-right-from-bracket me-2"></i>Salir
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="examen-main examen-main--ancho">

<?php if (!$pdo): ?>
    <section class="examen-panel examen-panel--error">
        <h1><i class="fas fa-triangle-exclamation me-2"></i>Base de datos no disponible</h1>
        <p>Enciende el servicio MySQL de WAMP y recarga la pagina.</p>
    </section>

<?php elseif (!$autorizado): ?>
    <section class="examen-panel examen-panel--login">
        <span class="examen-kicker"><i class="fas fa-lock me-2"></i>Acceso restringido</span>
        <h1>Consola de examenes</h1>
        <p class="examen-panel-lead">
            Necesitas iniciar sesion en el panel con un usuario que tenga acceso al modulo
            <strong>Examenes</strong> para inscribir alumnos y habilitar sus examenes.
        </p>

        <?php if ($error): ?>
            <p class="examen-alerta"><i class="fas fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <a class="examen-btn examen-btn--gold" href="<?= BASE_URL ?>/admin.php?v=login">
            <i class="fas fa-right-to-bracket me-2"></i>Ir al inicio de sesion del panel
        </a>
    </section>

<?php else: ?>

    <?php if ($error): ?>
        <p class="examen-alerta"><i class="fas fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($exito): ?>
        <p class="examen-alerta examen-alerta--ok"><i class="fas fa-circle-check me-2"></i><?= htmlspecialchars($exito) ?></p>
    <?php endif; ?>

    <?php if ($detalle): ?>
        <!-- ============ DETALLE DEL ALUMNO ============ -->
        <section class="examen-panel">
            <div class="examen-panel-head">
                <div>
                    <span class="examen-kicker"><i class="fas fa-user me-2"></i>Detalle del alumno</span>
                    <h1><?= htmlspecialchars($detalle['nombre']) ?></h1>
                    <p class="examen-panel-lead">
                        Codigo <strong><?= htmlspecialchars($detalle['codigo']) ?></strong>
                        <?php if ($detalle['dui']): ?> · DUI <?= htmlspecialchars($detalle['dui']) ?><?php endif; ?>
                        · Habilitado <?= examen_fecha($detalle['habilitado_desde']) ?>
                        · Vence <?= examen_fecha($detalle['vence']) ?>
                    </p>
                </div>
                <div class="examen-nota-caja <?= $detalle['estado'] === 'aprobado' ? 'es-aprobado' : ($detalle['estado'] === 'reprobado' ? 'es-reprobado' : '') ?>">
                    <span class="examen-nota-numero"><?= (int) $detalle['nota'] ?></span>
                    <span class="examen-nota-label"><?= htmlspecialchars(examen_etiqueta_estado($detalle['estado'])) ?></span>
                </div>
            </div>

            <?php
            $respuestas = examen_decodificar_respuestas($detalle['respuestas'] ?? null);
            if ($respuestas) {
                echo '<div class="examen-revision">';
                foreach ($preguntasBanco as $i => $p) {
                    $elegida = isset($respuestas[(string) $i]) ? (int) $respuestas[(string) $i] : -1;
                    $ok = $elegida === (int) $p['respuesta'];
                    echo '<div class="examen-revision-item ' . ($ok ? 'es-ok' : 'es-error') . '">'
                        . '<span class="examen-revision-num">' . ($i + 1) . '</span>'
                        . '<span class="examen-revision-texto">' . htmlspecialchars($p['pregunta']) . '</span>'
                        . '<span class="examen-revision-marca"><i class="fas ' . ($ok ? 'fa-check' : 'fa-xmark') . '"></i></span>'
                        . '</div>';
                }
                echo '</div>';
            } else {
                echo '<p class="examen-nota"><i class="fas fa-circle-info me-2"></i>Este alumno todavia no responde el examen.</p>';
            }
            ?>

            <div class="examen-acciones">
                <a class="examen-btn" href="<?= BASE_URL ?>/examen-alumnos.php"><i class="fas fa-arrow-left me-2"></i>Volver</a>
            </div>
        </section>

    <?php else: ?>
        <!-- ============ INSCRIBIR ALUMNO ============ -->
        <section class="examen-panel">
            <span class="examen-kicker"><i class="fas fa-user-plus me-2"></i>Inscribir alumno</span>
            <h1>Registra al alumno y habilita su examen</h1>
            <p class="examen-panel-lead">
                El alumno entra a <strong><?= BASE_URL ?>/examen.php</strong> con su codigo.
                Tendra <?= EXAMEN_VENTANA_MINUTOS ?> minutos desde la hora de habilitacion.
            </p>

            <form method="post" class="examen-form-acceso examen-form-acceso--fila">
                <input type="hidden" name="accion" value="inscribir">
                <div class="examen-campo">
                    <label for="i-nombre"><i class="fas fa-user me-2"></i>Nombre completo</label>
                    <input type="text" id="i-nombre" name="nombre" class="form-control" required placeholder="Ej. Maria Rodriguez">
                </div>
                <div class="examen-campo">
                    <label for="i-dui"><i class="fas fa-id-card me-2"></i>DUI <span>(opcional)</span></label>
                    <input type="text" id="i-dui" name="dui" class="form-control" placeholder="Ej. 04551234-5">
                </div>
                <div class="examen-campo">
                    <label for="i-codigo"><i class="fas fa-key me-2"></i>Codigo</label>
                    <input type="text" id="i-codigo" name="codigo" class="form-control"
                           value="<?= htmlspecialchars($codigoSugerido) ?>"
                           style="text-transform:uppercase; letter-spacing:2px; font-weight:700">
                </div>
                <div class="examen-campo">
                    <label for="i-fecha"><i class="fas fa-clock me-2"></i>Se habilita el</label>
                    <input type="datetime-local" id="i-fecha" name="habilitado_desde" class="form-control"
                           value="<?= $ahoraInput ?>" required>
                </div>
                <button type="submit" class="examen-btn examen-btn--gold"><i class="fas fa-check me-2"></i>Inscribir</button>
            </form>
        </section>

        <!-- ============ LISTADO ============ -->
        <section class="examen-panel">
            <div class="examen-panel-head">
                <div>
                    <span class="examen-kicker"><i class="fas fa-list me-2"></i>Alumnos inscritos</span>
                    <h1><?= count($alumnos) ?> alumno<?= count($alumnos) === 1 ? '' : 's' ?></h1>
                </div>
            </div>

            <?php if (!$alumnos): ?>
                <p class="examen-nota"><i class="fas fa-circle-info me-2"></i>Todavia no hay alumnos inscritos.</p>
            <?php else: ?>
                <div class="examen-tabla-wrap">
                    <table class="examen-tabla" id="tablaAlumnosExamen">
                        <thead>
                            <tr>
                                <th>Alumno</th>
                                <th>Codigo</th>
                                <th>Habilitado</th>
                                <th>Vence</th>
                                <th>Estado</th>
                                <th class="text-center">Nota</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($alumnos as $a): ?>
                            <?php $situacion = examen_estado_ventana($a); ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($a['nombre']) ?></strong>
                                    <?php if ($a['dui']): ?><br><small class="examen-mut">DUI <?= htmlspecialchars($a['dui']) ?></small><?php endif; ?>
                                </td>
                                <td><code><?= htmlspecialchars($a['codigo']) ?></code></td>
                                <td><?= examen_fecha($a['habilitado_desde']) ?></td>
                                <td><?= examen_fecha($a['vence']) ?></td>
                                <td>
                                    <span class="examen-estado estado-<?= htmlspecialchars($a['estado']) ?>">
                                        <?= htmlspecialchars(examen_etiqueta_estado($a['estado'])) ?>
                                    </span>
                                    <?php if ($a['estado'] === 'pendiente' && $situacion['situacion'] === 'espera'): ?>
                                        <br><small class="examen-mut">faltan <?= gmdate('H:i', $situacion['restante']) ?></small>
                                    <?php endif; ?>
                                    <?php if ($a['estado'] === 'en_curso'): ?>
                                        <br><small class="examen-mut">pregunta <?= (int) $a['pregunta_actual'] + 1 ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (in_array($a['estado'], ['aprobado', 'reprobado'], true)): ?>
                                        <strong class="nota-<?= $a['estado'] === 'aprobado' ? 'ok' : 'error' ?>"><?= (int) $a['nota'] ?></strong>
                                    <?php else: ?>
                                        <span class="examen-mut">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a class="examen-btn-mini" href="<?= BASE_URL ?>/examen-alumnos.php?alumno=<?= (int) $a['id'] ?>" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="post" class="examen-form-inline" data-rehabilitar="<?= (int) $a['id'] ?>">
                                        <input type="hidden" name="accion" value="rehabilitar">
                                        <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                        <input type="datetime-local" name="habilitado_desde" class="examen-input-mini" value="<?= $ahoraInput ?>" required>
                                        <button type="submit" class="examen-btn-mini" title="Re-habilitar"><i class="fas fa-rotate-right"></i></button>
                                    </form>
                                    <form method="post" class="examen-form-inline" data-confirmar="¿Eliminar el registro de <?= htmlspecialchars($a['nombre']) ?>?">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                        <button type="submit" class="examen-btn-mini examen-btn-mini--peligro" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="examen-paginacion" data-tabla="tablaAlumnosExamen" data-por-pagina="5">
                    <span class="examen-pag-info"></span>
                    <div class="examen-pag-controles"></div>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

<?php endif; ?>

</main>

<script>
/* Confirmacion al eliminar */
document.querySelectorAll('form[data-confirmar]').forEach(function (form) {
    form.addEventListener('submit', function (ev) {
        if (!confirm(form.getAttribute('data-confirmar'))) ev.preventDefault();
    });
});
</script>
<script>
/* Paginacion del listado de alumnos (5 por pagina) */
document.addEventListener('DOMContentLoaded', function () {
    var tabla = document.getElementById('tablaAlumnosExamen');
    if (!tabla) { return; }
    var cuerpo = tabla.querySelector('tbody');
    var barra = document.querySelector('.examen-paginacion[data-tabla="tablaAlumnosExamen"]');
    if (!cuerpo || !barra) { return; }

    var info = barra.querySelector('.examen-pag-info');
    var controles = barra.querySelector('.examen-pag-controles');
    var porPagina = parseInt(barra.getAttribute('data-por-pagina'), 10) || 5;
    var filas = Array.prototype.slice.call(cuerpo.querySelectorAll('tr'));
    var pagina = 1;
    var paginas = Math.max(1, Math.ceil(filas.length / porPagina));

    function pintar() {
        var ini = (pagina - 1) * porPagina;
        var fin = Math.min(ini + porPagina, filas.length);
        filas.forEach(function (tr, i) { tr.style.display = (i >= ini && i < fin) ? '' : 'none'; });
        info.textContent = filas.length
            ? 'Mostrando ' + (ini + 1) + '-' + fin + ' de ' + filas.length
            : 'Sin registros';

        var html = '<button type="button" class="examen-pag-btn" data-pag="prev"'
            + (pagina <= 1 ? ' disabled' : '') + '><i class="fas fa-chevron-left"></i></button>';
        for (var n = 1; n <= paginas; n++) {
            html += '<button type="button" class="examen-pag-btn' + (n === pagina ? ' is-active' : '')
                + '" data-pag="' + n + '">' + n + '</button>';
        }
        html += '<button type="button" class="examen-pag-btn" data-pag="next"'
            + (pagina >= paginas ? ' disabled' : '') + '><i class="fas fa-chevron-right"></i></button>';
        controles.innerHTML = html;
    }

    controles.addEventListener('click', function (ev) {
        var btn = ev.target.closest('.examen-pag-btn');
        if (!btn || btn.disabled) { return; }
        var destino = btn.getAttribute('data-pag');
        if (destino === 'prev') { pagina--; }
        else if (destino === 'next') { pagina++; }
        else { pagina = parseInt(destino, 10); }
        pintar();
    });

    pintar();
});
</script>
</body>
</html>
