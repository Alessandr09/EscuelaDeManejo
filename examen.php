<?php
/**
 * examen.php - Examen aprobatorio de manejo (modo alumno).
 *
 * El alumno se registra previamente con un maestro (ver examen-alumnos.php),
 * recibe un codigo de acceso y la hora en la que se habilita su examen.
 * Desde esa hora tiene EXAMEN_VENTANA_MINUTOS minutos para responder.
 *
 * Reglas:
 *  - las preguntas se muestran de una en una;
 *  - no se puede volver a una pregunta anterior (bloqueado en el servidor);
 *  - al agotarse el tiempo el examen se cierra solo;
 *  - al terminar se guarda la nota en la tabla `examen_alumnos`.
 */
require_once __DIR__ . '/php/base.php';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/config/examen_funciones.php';

session_start();

$preguntas = require __DIR__ . '/modules/data/preguntas.php';
$total     = count($preguntas);

// Conexion tolerante a fallos: si MySQL no responde, el alumno ve un aviso
try {
    $pdo = db();
} catch (Throwable $e) {
    $pdo = null;
}

$accion  = $_POST['accion'] ?? $_GET['accion'] ?? '';
$error   = $_SESSION['examen_error'] ?? '';
unset($_SESSION['examen_error']);

/* ============ API: guardar respuesta de la pregunta actual ============ */
if ($accion === 'respuesta') {
    header('Content-Type: application/json; charset=utf-8');
    $alumno = $_SESSION['examen_alumno_id'] ? examen_alumno_por_id($pdo, (int) $_SESSION['examen_alumno_id']) : null;
    if (!$alumno) {
        echo json_encode(['ok' => false, 'error' => 'sin_sesion']);
        exit;
    }
    $datos = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
    $resultado = examen_guardar_respuesta(
        $pdo,
        $alumno,
        (int) ($datos['indice'] ?? -1),
        (int) ($datos['opcion'] ?? -1),
        $preguntas
    );
    echo json_encode($resultado);
    exit;
}

/* ============ API: finalizar el examen ============ */
if ($accion === 'finalizar') {
    header('Content-Type: application/json; charset=utf-8');
    $alumno = $_SESSION['examen_alumno_id'] ? examen_alumno_por_id($pdo, (int) $_SESSION['examen_alumno_id']) : null;
    if (!$alumno) {
        echo json_encode(['ok' => false, 'error' => 'sin_sesion']);
        exit;
    }
    $nota = examen_finalizar($pdo, $alumno, $preguntas);
    echo json_encode(['ok' => true] + $nota);
    exit;
}

/* ============ Acceso del alumno ============ */
if ($accion === 'acceder') {
    $codigo = trim($_POST['codigo'] ?? '');
    $dui    = trim($_POST['dui'] ?? '');

    if (!$pdo) {
        $_SESSION['examen_error'] = 'La base de datos no esta disponible. Avisale a tu instructor.';
    } elseif ($codigo === '') {
        $_SESSION['examen_error'] = 'Escribe el codigo que te dio tu instructor.';
    } else {
        $alumno = examen_alumno_por_codigo($pdo, $codigo);
        if (!$alumno) {
            $_SESSION['examen_error'] = 'No encontramos un alumno con ese codigo. Revisa con tu instructor.';
        } elseif ($dui !== '' && $alumno['dui'] && strcasecmp($dui, $alumno['dui']) !== 0) {
            $_SESSION['examen_error'] = 'El DUI no coincide con el registro de ese codigo.';
        } else {
            $_SESSION['examen_alumno_id'] = (int) $alumno['id'];
            header('Location: ' . BASE_URL . '/examen.php');
            exit;
        }
    }
    header('Location: ' . BASE_URL . '/examen.php');
    exit;
}

/* Salir del examen (el instructor puede re-habilitarlo) */
if (isset($_GET['salir'])) {
    unset($_SESSION['examen_alumno_id']);
    header('Location: ' . BASE_URL . '/examen.php');
    exit;
}

/* ============ Estado del alumno en sesion ============ */
$alumno = ($_SESSION['examen_alumno_id'] ?? null) && $pdo
    ? examen_alumno_por_id($pdo, (int) $_SESSION['examen_alumno_id'])
    : null;

$ventana   = $alumno ? examen_estado_ventana($alumno) : null;
$situacion = $ventana['situacion'] ?? 'acceso';
$restante  = (int) ($ventana['restante'] ?? 0);

// Si el alumno ya respondio todas las preguntas, el examen se cierra solo.
if ($alumno && $situacion === 'disponible' && (int) $alumno['pregunta_actual'] >= $total) {
    examen_finalizar($pdo, $alumno, $preguntas);
    $alumno    = examen_alumno_por_id($pdo, (int) $alumno['id']);
    $situacion = 'finalizado';
}

// Si se paso el tiempo, el examen queda vencido.
if ($alumno && $situacion === 'expirado' && in_array($alumno['estado'], ['pendiente', 'en_curso'], true)) {
    examen_marcar_expirado($pdo, $alumno);
    $alumno = examen_alumno_por_id($pdo, (int) $alumno['id']);
}

// Datos para el cliente: preguntas SIN la respuesta correcta.
$preguntasCliente = [];
foreach ($preguntas as $p) {
    $preguntasCliente[] = [
        'categoria' => $p['categoria'],
        'pregunta'  => $p['pregunta'],
        'opciones'  => array_values($p['opciones']),
    ];
}
$jsonPreguntas = json_encode(
    $preguntasCliente,
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);

$indiceActual = $alumno ? (int) $alumno['pregunta_actual'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Examen de Manejo | <?= APP_NOMBRE ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/examen.css?v=2">
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/logo.svg">
</head>
<body class="examen-body">

<header class="examen-topbar no-print">
    <div class="examen-topbar-inner">
        <a class="examen-brand" href="<?= BASE_URL ?>/index.php">
            <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="Logo <?= APP_NOMBRE ?>" width="36" height="36">
            <span class="examen-brand-text">
                <b><?= APP_NOMBRE ?></b>
                <small>Examen Teorico de Manejo</small>
            </span>
        </a>
        <div class="examen-topbar-meta">
            <?php if ($alumno && $situacion === 'disponible'): ?>
                <span class="examen-alumno-chip"><i class="fas fa-user me-2"></i><?= htmlspecialchars($alumno['nombre']) ?></span>
                <span class="examen-timer" id="timer" role="timer" aria-live="off">
                    <i class="fas fa-stopwatch me-2"></i><span id="timerTexto">--:--</span>
                </span>
            <?php elseif ($alumno && $situacion === 'finalizado'): ?>
                <span class="examen-alumno-chip"><i class="fas fa-user me-2"></i><?= htmlspecialchars($alumno['nombre']) ?></span>
            <?php endif; ?>
            <?php if ($alumno): ?>
                <a class="examen-pill examen-pill--ghost" href="<?= BASE_URL ?>/examen.php?salir=1">
                    <i class="fas fa-right-from-bracket me-2"></i>Salir
                </a>
            <?php else: ?>
                <a class="examen-pill examen-pill--ghost" href="<?= BASE_URL ?>/examen-alumnos.php">
                    <i class="fas fa-chalkboard-user me-2"></i>Acceso instructor
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="examen-main">

<?php if (!$pdo): ?>
    <!-- ============ BD no disponible ============ -->
    <section class="examen-panel examen-panel--error">
        <h1><i class="fas fa-triangle-exclamation me-2"></i>Base de datos no disponible</h1>
        <p>No pudimos conectarnos con el servidor de datos. Avisale a tu instructor para que encienda el servicio MySQL de WAMP.</p>
    </section>

<?php elseif ($situacion === 'acceso'): ?>
    <!-- ============ ACCESO DEL ALUMNO ============ -->
    <section class="examen-panel">
        <span class="examen-kicker"><i class="fas fa-graduation-cap me-2"></i>Examen aprobatorio</span>
        <h1>Presentate tu examen teorico</h1>
        <p class="examen-panel-lead">
            Escribe el codigo que te dio tu instructor. Tu examen se habilita a la hora acordada
            y tienes <?= EXAMEN_VENTANA_MINUTOS ?> minutos para completarlo.
        </p>

        <?php if ($error): ?>
            <p class="examen-alerta"><i class="fas fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" action="examen.php" class="examen-form-acceso">
            <input type="hidden" name="accion" value="acceder">
            <div class="examen-campo">
                <label for="f-codigo"><i class="fas fa-key me-2"></i>Codigo de alumno</label>
                <input type="text" id="f-codigo" name="codigo" class="form-control" required
                       placeholder="Ej. K7M2QP4B" autocomplete="off"
                       style="text-transform:uppercase; letter-spacing:2px; font-weight:700">
            </div>
            <div class="examen-campo">
                <label for="f-dui"><i class="fas fa-id-card me-2"></i>DUI <span>(opcional)</span></label>
                <input type="text" id="f-dui" name="dui" class="form-control" placeholder="Ej. 04551234-5">
            </div>
            <button type="submit" class="examen-btn examen-btn--gold">
                <i class="fas fa-arrow-right me-2"></i>Continuar
            </button>
        </form>

        <ul class="examen-info">
            <li><i class="fas fa-clock"></i><?= EXAMEN_VENTANA_MINUTOS ?> minutos, una pregunta a la vez</li>
            <li><i class="fas fa-ban"></i>No se puede volver a una pregunta anterior</li>
            <li><i class="fas fa-certificate"></i>Apruebas con <?= EXAMEN_UMBRAL ?>% o mas</li>
        </ul>
    </section>

<?php elseif ($situacion === 'espera'): ?>
    <!-- ============ ESPERANDO LA HORA HABILITADA ============ -->
    <section class="examen-panel examen-panel--espera">
        <span class="examen-kicker"><i class="fas fa-clock me-2"></i>Examen habilitado a las <?= date('h:i A', strtotime($alumno['habilitado_desde'])) ?></span>
        <h1>Hola, <?= htmlspecialchars(explode(' ', $alumno['nombre'])[0]) ?></h1>
        <p class="examen-panel-lead">
            Tu examen empieza el <strong><?= examen_fecha($alumno['habilitado_desde']) ?></strong>.
            Tendras <?= EXAMEN_VENTANA_MINUTOS ?> minutos desde ese momento.
        </p>
        <div class="examen-cuenta-espera" id="cuentaEspera" data-fecha="<?= htmlspecialchars($alumno['habilitado_desde']) ?>">
            <span id="esperaTexto">--:--:--</span>
            <small>hasta que se habilite tu examen</small>
        </div>
        <p class="examen-nota"><i class="fas fa-circle-info me-2"></i>Esta pagina se actualizara sola cuando empiece tu turno.</p>
    </section>

<?php elseif ($situacion === 'expirado'): ?>
    <!-- ============ TIEMPO VENCIDO ============ -->
    <section class="examen-panel examen-panel--error">
        <span class="examen-kicker"><i class="fas fa-triangle-exclamation me-2"></i>Tiempo agotado</span>
        <h1>Tu examen ya no esta disponible</h1>
        <p class="examen-panel-lead">
            La ventana de <?= EXAMEN_VENTANA_MINUTOS ?> minutos (hasta <?= examen_fecha($alumno['vence']) ?>) se cumplio.
            Pide a tu instructor que te habilite de nuevo.
        </p>
        <?php if ($alumno['estado'] === 'expirado'): ?>
            <p class="examen-alerta"><i class="fas fa-circle-info me-2"></i>No se guardo nota porque el examen no se completo a tiempo.</p>
        <?php endif; ?>
        <a class="examen-btn" href="<?= BASE_URL ?>/examen.php?salir=1"><i class="fas fa-arrow-left me-2"></i>Salir</a>
    </section>

<?php elseif ($situacion === 'finalizado'): ?>
    <!-- ============ RESULTADO ============ -->
    <?php
    $nota       = (int) $alumno['nota'];
    $aprobado   = $alumno['estado'] === 'aprobado';
    $respuestas = examen_decodificar_respuestas($alumno['respuestas'] ?? null);
    $falladas   = [];
    foreach ($preguntas as $i => $p) {
        $elegida = isset($respuestas[(string) $i]) ? (int) $respuestas[(string) $i] : -1;
        if ($elegida !== (int) $p['respuesta']) {
            $falladas[] = ['pregunta' => $p['pregunta'], 'categoria' => $p['categoria'], 'correcta' => $p['opciones'][$p['respuesta']]];
        }
    }
    ?>
    <section class="examen-panel examen-panel--resultado <?= $aprobado ? 'es-aprobado' : 'es-reprobado' ?>">
        <div class="examen-nota-caja">
            <span class="examen-nota-numero"><?= $nota ?></span>
            <span class="examen-nota-label">Nota</span>
        </div>
        <div class="examen-nota-datos">
            <span class="examen-kicker"><i class="fas <?= $aprobado ? 'fa-circle-check' : 'fa-circle-xmark' ?> me-2"></i><?= $aprobado ? 'Aprobado' : 'No aprobado' ?></span>
            <h1><?= htmlspecialchars($alumno['nombre']) ?></h1>
            <p class="examen-panel-lead">
                Respondiste correctamente <strong><?= (int) $alumno['aciertos'] ?></strong> de <strong><?= (int) $alumno['total'] ?></strong> preguntas
                (<?= (float) $alumno['porcentaje'] ?>%). Se necesita <?= EXAMEN_UMBRAL ?>% para aprobar.
            </p>
            <p class="examen-nota"><i class="fas fa-clock me-2"></i>Examen finalizado el <?= examen_fecha($alumno['terminado_en']) ?></p>
        </div>
        <div class="examen-acciones no-print">
            <button type="button" class="examen-btn" onclick="window.print()"><i class="fas fa-print me-2"></i>Imprimir</button>
            <a class="examen-btn examen-btn--gold" href="<?= BASE_URL ?>/examen.php?salir=1"><i class="fas fa-right-from-bracket me-2"></i>Salir</a>
        </div>
    </section>

    <?php if ($falladas): ?>
        <section class="examen-panel">
            <h2 class="examen-panel-h2"><i class="fas fa-circle-exclamation me-2"></i>Repasa estas <?= count($falladas) ?> preguntas</h2>
            <?php foreach ($falladas as $f): ?>
                <div class="examen-fallada">
                    <span class="examen-categoria"><?= htmlspecialchars($f['categoria']) ?></span>
                    <p><?= htmlspecialchars($f['pregunta']) ?></p>
                    <small><i class="fas fa-check me-1"></i>Respuesta correcta: <?= htmlspecialchars($f['correcta']) ?></small>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

<?php else: ?>
    <!-- ============ EXAMEN EN CURSO ============ -->
    <section class="examen-barra-progreso">
        <div class="examen-barra-texto">
            <span><i class="fas fa-list-check me-2"></i>Pregunta <strong id="preguntaNum"><?= $indiceActual + 1 ?></strong> de <?= $total ?></span>
            <span id="preguntaResto"><?= $total - $indiceActual ?> restante<?= ($total - $indiceActual) === 1 ? '' : 's' ?></span>
        </div>
        <div class="examen-barra"><span id="barraProgreso" style="width:<?= round($indiceActual / $total * 100, 1) ?>%"></span></div>
    </section>

    <section class="examen-panel examen-panel--pregunta">
        <span class="examen-categoria" id="preguntaCategoria"></span>
        <h1 class="examen-pregunta" id="preguntaTexto">Cargando…</h1>

        <div class="examen-opciones" id="preguntaOpciones"></div>

        <div class="examen-panel-pie">
            <p class="examen-nota"><i class="fas fa-lock me-2"></i>No puedes volver a una pregunta anterior.</p>
            <button type="button" class="examen-btn examen-btn--gold" id="btnSiguiente" disabled>
                <i class="fas fa-arrow-right me-2"></i><span id="btnSiguienteTexto">Siguiente</span>
            </button>
        </div>
    </section>
<?php endif; ?>

</main>

<?php if ($situacion === 'disponible'): ?>
<script>
/* ================= Motor del examen ================= */
(function () {
    var TOTAL = <?= $total ?>;
    var PREGUNTAS = <?= $jsonPreguntas ?>;
    var indice = <?= $indiceActual ?>;
    var restante = <?= $restante ?>;
    var restanteServidor = restante;
    var ultimoEnvio = 0;

    var elPregunta = document.getElementById('preguntaTexto');
    var elCategoria = document.getElementById('preguntaCategoria');
    var elOpciones = document.getElementById('preguntaOpciones');
    var elBoton = document.getElementById('btnSiguiente');
    var elBotonTexto = document.getElementById('btnSiguienteTexto');
    var elNum = document.getElementById('preguntaNum');
    var elResto = document.getElementById('preguntaResto');
    var elBarra = document.getElementById('barraProgreso');
    var elTimer = document.getElementById('timer');
    var elTimerTexto = document.getElementById('timerTexto');
    var elegida = -1;

    function dosDigitos(n) { return (n < 10 ? '0' : '') + n; }

    function formatear(segundos) {
        if (segundos < 0) segundos = 0;
        var m = Math.floor(segundos / 60);
        var s = segundos % 60;
        return dosDigitos(m) + ':' + dosDigitos(s);
    }

    function pintar() {
        var p = PREGUNTAS[indice];
        if (!p) { finalizar(); return; }
        elegida = -1;
        elCategoria.textContent = p.categoria;
        elPregunta.textContent = p.pregunta;
        elOpciones.innerHTML = '';
        p.opciones.forEach(function (texto, j) {
            var label = document.createElement('label');
            label.className = 'examen-opcion';
            label.innerHTML = '<input type="radio" name="opcion" value="' + j + '">' +
                '<span class="examen-letra">' + String.fromCharCode(65 + j) + '</span>' +
                '<span class="examen-opcion-texto"></span>';
            label.querySelector('.examen-opcion-texto').textContent = texto;
            label.querySelector('input').addEventListener('change', function () {
                elegida = j;
                Array.prototype.forEach.call(elOpciones.querySelectorAll('.examen-opcion'), function (o) {
                    o.classList.remove('es-elegida');
                });
                label.classList.add('es-elegida');
                elBoton.disabled = false;
            });
            elOpciones.appendChild(label);
        });
        elNum.textContent = indice + 1;
        var restan = TOTAL - indice - 1;
        elResto.textContent = restan + ' restante' + (restan === 1 ? '' : 's');
        elBarra.style.width = (indice / TOTAL * 100).toFixed(1) + '%';
        elBotonTexto.textContent = (indice === TOTAL - 1) ? 'Finalizar examen' : 'Siguiente';
        elBoton.classList.toggle('es-final', indice === TOTAL - 1);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function guardarYAvanzar() {
        elBoton.disabled = true;
        var cuerpo = JSON.stringify({ indice: indice, opcion: elegida });
        fetch('examen.php?accion=respuesta', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: cuerpo
        }).then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.error === 'expirado') { finalizar(); return; }
            if (data.error === 'sin_sesion') { window.location.href = 'examen.php'; return; }
            // 'pregunta_bloqueada' significa que el servidor ya avanzo: solo avanzamos en pantalla
            indice = Math.max(indice + 1, data.indice || 0);
            if (indice >= TOTAL) { finalizar(); return; }
            pintar();
          })
          .catch(function () {
            // sin conexion: se reintenta la misma pregunta
            elBoton.disabled = false;
          });
    }

    function finalizar() {
        elBoton.disabled = true;
        fetch('examen.php?accion=finalizar', { method: 'POST' })
            .then(function () { window.location.href = 'examen.php'; })
            .catch(function () { window.location.href = 'examen.php'; });
    }

    elBoton.addEventListener('click', function () {
        if (elegida < 0) return;
        if (indice === TOTAL - 1) { guardarYAvanzar(); } else { guardarYAvanzar(); }
    });

    /* Cuenta regresiva: el plazo lo calcula el servidor (no el reloj del equipo) */
    function tic() {
        restante--;
        if (restante <= 0) {
            restante = 0;
            if (elTimer) elTimer.classList.add('es-vencido');
            finalizar();
            return;
        }
        if (elTimerTexto) elTimerTexto.textContent = formatear(restante);
        if (elTimer && restante <= 300) elTimer.classList.add('es-urgente');
    }
    if (elTimerTexto) elTimerTexto.textContent = formatear(restante);
    setInterval(tic, 1000);

    /* No se permite volver atras con el boton del navegador */
    history.pushState({ examen: 1 }, '');
    window.addEventListener('popstate', function () { history.pushState({ examen: 1 }, ''); });

    pintar();
})();
</script>
<?php endif; ?>

<?php if ($situacion === 'espera'): ?>
<script>
/* Espera: la pagina se recarga sola cuando se habilita el examen */
(function () {
    var destino = <?= strtotime($alumno['habilitado_desde']) ?>;
    var el = document.getElementById('esperaTexto');
    function tic() {
        var falta = destino - Math.floor(Date.now() / 1000);
        if (falta <= 0) { window.location.reload(); return; }
        var h = Math.floor(falta / 3600);
        var m = Math.floor((falta % 3600) / 60);
        var s = falta % 60;
        var d = function (n) { return (n < 10 ? '0' : '') + n; };
        if (el) el.textContent = d(h) + ':' + d(m) + ':' + d(s);
    }
    tic();
    setInterval(tic, 1000);
})();
</script>
<?php endif; ?>

</body>
</html>
