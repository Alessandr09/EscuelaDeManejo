<?php
/**
 * examen.php - Simulador de examen teorico de manejo (modo instructor)
 *
 * Uso: el instructor abre esta pagina desde el panel admin (admin.php?v=examenes),
 * escribe el nombre del alumno, aplica el examen en la computadora de la escuela
 * y revisa el resultado al instante.
 *
 * URL publica: BASE_URL/examen.php
 * (no puede vivir bajo /admin/ porque el servidor reserva ese prefijo para admin.php)
 */
require_once __DIR__ . '/php/base.php';

$preguntas = require __DIR__ . '/modules/data/preguntas.php';
$total     = count($preguntas);
$umbral    = 80; // % minimo de aciertos para aprobar (ajustar segun reglamento)

$alumno   = trim($_POST['alumno'] ?? '');
$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aciertos = 0;
    $errores  = [];

    foreach ($preguntas as $i => $p) {
        $respuestaUsuario = isset($_POST["p$i"]) ? (int) $_POST["p$i"] : -1;
        if ($respuestaUsuario === $p['respuesta']) {
            $aciertos++;
        } else {
            $errores[] = $i + 1;
        }
    }

    $porcentaje = $total > 0 ? round(($aciertos / $total) * 100) : 0;
    $resultado = [
        'aciertos'   => $aciertos,
        'total'      => $total,
        'porcentaje' => $porcentaje,
        'aprobado'   => $porcentaje >= $umbral,
        'falladas'   => $errores,
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Examen Teorico de Manejo | <?= APP_NOMBRE ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/examen.css?v=1">
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/logo.svg">
</head>
<body class="examen-body">

<header class="examen-topbar no-print">
    <div class="examen-topbar-inner">
        <a class="examen-brand" href="<?= BASE_URL ?>/index.php">
            <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="Logo <?= APP_NOMBRE ?>" width="38" height="38">
            <span class="examen-brand-text">
                <b><?= APP_NOMBRE ?></b>
                <small>Examen Teorico de Manejo</small>
            </span>
        </a>
        <div class="examen-topbar-meta">
            <span class="examen-pill"><i class="fas fa-list-ol me-2"></i><?= $total ?> preguntas</span>
            <span class="examen-pill"><i class="fas fa-gauge me-2"></i>Umbral <?= $umbral ?>%</span>
            <a class="examen-pill examen-pill--ghost" href="<?= BASE_URL ?>/admin.php?v=examenes">
                <i class="fas fa-arrow-left me-2"></i>Volver al panel
            </a>
        </div>
    </div>
</header>

<main class="examen-main">

    <?php if ($resultado): ?>
        <!-- ================= RESULTADO ================= -->
        <section class="examen-resultado <?= $resultado['aprobado'] ? 'es-aprobado' : 'es-reprobado' ?>">
            <div class="examen-resultado-head">
                <span class="examen-sello">
                    <i class="fas <?= $resultado['aprobado'] ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
                    <?= $resultado['aprobado'] ? 'Aprobado' : 'No aprobado' ?>
                </span>
                <div class="examen-resultado-datos">
                    <h1><?= htmlspecialchars($alumno !== '' ? $alumno : 'Alumno sin nombre') ?></h1>
                    <p>
                        <strong><?= $resultado['aciertos'] ?></strong> de <strong><?= $resultado['total'] ?></strong> respuestas correctas
                        (<strong><?= $resultado['porcentaje'] ?>%</strong>) · Umbral <?= $umbral ?>%
                    </p>
                </div>
                <div class="examen-acciones no-print">
                    <button type="button" class="examen-btn" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Imprimir resultado
                    </button>
                    <a class="examen-btn examen-btn--gold" href="<?= BASE_URL ?>/examen.php">
                        <i class="fas fa-user-plus me-2"></i>Aplicar a otro alumno
                    </a>
                </div>
            </div>

            <?php if ($resultado['falladas']): ?>
                <p class="examen-alerta">
                    <i class="fas fa-circle-exclamation me-2"></i>
                    Repasar las preguntas <strong><?= count($resultado['falladas']) ?></strong> marcadas en rojo.
                </p>
            <?php endif; ?>
        </section>

        <form method="post" action="examen.php" class="examen-lista">
            <?php foreach ($preguntas as $i => $p): ?>
                <?php
                $correcta = $p['respuesta'];
                $elegida = (int)($_POST["p$i"] ?? -1);
                $ok = $elegida === $correcta;
                ?>
                <article class="examen-card <?= $ok ? 'es-ok' : 'es-error' ?>">
                    <div class="examen-card-head">
                        <span class="examen-num"><?= $i + 1 ?></span>
                        <div>
                            <span class="examen-categoria"><?= htmlspecialchars($p['categoria']) ?></span>
                            <h2 class="examen-pregunta"><?= htmlspecialchars($p['pregunta']) ?></h2>
                        </div>
                        <span class="examen-estado"><i class="fas <?= $ok ? 'fa-check' : 'fa-xmark' ?>"></i></span>
                    </div>
                    <div class="examen-opciones">
                        <?php foreach ($p['opciones'] as $j => $opcion): ?>
                            <?php $esCorrecta = $j === $correcta; ?>
                            <label class="examen-opcion <?= $esCorrecta ? 'es-correcta' : '' ?>">
                                <input type="radio" name="p<?= $i ?>" value="<?= $j ?>" disabled <?= $elegida === $j ? 'checked' : '' ?>>
                                <span class="examen-letra"><?= chr(65 + $j) ?></span>
                                <span class="examen-opcion-texto"><?= htmlspecialchars($opcion) ?></span>
                                <?php if ($esCorrecta): ?><i class="fas fa-check-circle opcion-check"></i><?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="examen-solucion">
                        Respuesta correcta: <strong><?= chr(65 + $correcta) ?>)</strong>
                        <?= htmlspecialchars($p['opciones'][$correcta]) ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </form>

    <?php else: ?>
        <!-- ================= APLICAR EXAMEN ================= -->
        <section class="examen-intro">
            <h1>Examen Teorico de Manejo</h1>
            <p>Escribe el nombre del alumno y aplica el examen. Al enviarlo veras el resultado al instante.</p>
        </section>

        <form method="post" action="examen.php" class="examen-lista" id="formExamen">

            <div class="examen-intro">
                <div class="examen-alumno">
                    <label for="f-alumno"><i class="fas fa-user me-2"></i>Alumno</label>
                    <input type="text" id="f-alumno" name="alumno" class="form-control"
                           placeholder="Ej. Maria Rodriguez" autocomplete="off" value="<?= htmlspecialchars($alumno) ?>">
                </div>

                <div class="examen-progreso">
                    <div class="examen-progreso-texto">
                        <span><i class="fas fa-list-check me-2"></i>Progreso</span>
                        <span id="progresoTexto">0 de <?= $total ?> respondidas</span>
                    </div>
                    <div class="examen-progreso-barra"><span id="progresoBarra" style="width:0%"></span></div>
                </div>
            </div>

            <?php foreach ($preguntas as $i => $p): ?>
                <article class="examen-card" data-pregunta="<?= $i ?>">
                    <div class="examen-card-head">
                        <span class="examen-num"><?= $i + 1 ?></span>
                        <div>
                            <span class="examen-categoria"><?= htmlspecialchars($p['categoria']) ?></span>
                            <h2 class="examen-pregunta"><?= htmlspecialchars($p['pregunta']) ?></h2>
                        </div>
                    </div>
                    <div class="examen-opciones">
                        <?php foreach ($p['opciones'] as $j => $opcion): ?>
                            <label class="examen-opcion">
                                <input type="radio" name="p<?= $i ?>" value="<?= $j ?>" required>
                                <span class="examen-letra"><?= chr(65 + $j) ?></span>
                                <span class="examen-opcion-texto"><?= htmlspecialchars($opcion) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>

            <div class="examen-enviar no-print">
                <p class="examen-nota"><i class="fas fa-circle-info me-2"></i>Todas las preguntas son obligatorias.</p>
                <button type="submit" class="examen-btn examen-btn--gold">
                    <i class="fas fa-paper-plane me-2"></i>Enviar y ver resultado
                </button>
            </div>
        </form>
    <?php endif; ?>

</main>

<script>
/* Contador de preguntas respondidas */
(function () {
  var form = document.getElementById('formExamen');
  if (!form) return;

  var total = <?= $total ?>;
  var barra = document.getElementById('progresoBarra');
  var texto = document.getElementById('progresoTexto');

  function actualizar() {
    var respondidas = form.querySelectorAll('input[type=radio]:checked').length;
    if (barra) barra.style.width = (respondidas / total * 100).toFixed(1) + '%';
    if (texto) texto.textContent = respondidas + ' de ' + total + ' respondidas';

    Array.prototype.forEach.call(form.querySelectorAll('.examen-card'), function (card) {
      var marcada = card.querySelector('input[type=radio]:checked');
      card.classList.toggle('es-respondida', !!marcada);
    });
  }

  form.addEventListener('change', function (ev) {
    if (ev.target.type === 'radio') actualizar();
  });
  actualizar();
})();
</script>
</body>
</html>
