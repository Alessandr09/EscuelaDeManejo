<?php
/**
 * MODULO 2 - REGISTRO DE PARTIDAS DE DIARIO Y VALIDACION DE PARTIDA DOBLE
 *
 * Acciones:
 *   ?accion=nueva   formulario de carga de un asiento
 *   ?accion=ver     (por defecto) consulta del Libro Diario
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';

$accion = $_GET['accion'] ?? 'ver';

$errores = [];
$exito   = '';

// Valores enviados, para repoblar el formulario si algo falla.
$enviado = ['fecha' => '', 'renglones' => []];

/* =====================================================================
 *  REGISTRO DE UNA PARTIDA
 * ===================================================================== */
if ($accion === 'nueva' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $fechaCruda = trim((string) ($_POST['fecha'] ?? ''));
    $fecha      = fechaAMysql($fechaCruda);
    $concepto   = trim((string) ($_POST['concepto'] ?? ''));

    if ($fecha === null) {
        $errores[] = 'La fecha "' . $fechaCruda . '" no es valida. Use el formato dd/mm/aaaa.';
    }

    $cuentas  = (array) ($_POST['cuenta'] ?? []);
    $debes    = (array) ($_POST['debe'] ?? []);
    $haberes  = (array) ($_POST['haber'] ?? []);
    $cuentaMaestro = obtenerCatalogo($pdo);
    $codigosValidos = array_column($cuentaMaestro, null, 'codigo');

    $lineas = [];

    for ($i = 0; $i < count($cuentas); $i++) {
        $cuenta = trim((string) $cuentas[$i]);
        $debe   = str_replace(',', '.', trim((string) ($debes[$i] ?? '')));
        $haber  = str_replace(',', '.', trim((string) ($haberes[$i] ?? '')));

        $debe  = ($debe === '')  ? 0.0 : (float) $debe;
        $haber = ($haber === '') ? 0.0 : (float) $haber;

        $enviado['renglones'][] = ['cuenta' => $cuenta, 'debe' => $debe, 'haber' => $haber];

        // Renglones completamente vacios: se ignoran (el usuario dejo la fila de mas).
        if ($cuenta === '' && $debe === 0.0 && $haber === 0.0) {
            continue;
        }

        if ($cuenta === '') {
            $errores[] = 'Renglon ' . ($i + 1) . ': debe seleccionar una cuenta.';
            continue;
        }

        if (!isset($codigosValidos[$cuenta])) {
            $errores[] = 'Renglon ' . ($i + 1) . ': la cuenta "' . $cuenta . '" no existe en el catalogo.';
            continue;
        }

        if ($debe < 0 || $haber < 0) {
            $errores[] = 'Renglon ' . ($i + 1) . ': los montos no pueden ser negativos.';
            continue;
        }

        if ($debe > 0 && $haber > 0) {
            $errores[] = 'Renglon ' . ($i + 1) . ': un renglon no puede llevar monto en Debe y Haber a la vez.';
            continue;
        }

        if ($debe == 0.0 && $haber == 0.0) {
            $errores[] = 'Renglon ' . ($i + 1) . ': el renglon debe tener monto en Debe o en Haber.';
            continue;
        }

        $lineas[] = [
            'cuenta' => $cuenta,
            'debe'   => round($debe, 2),
            'haber'  => round($haber, 2),
        ];
    }

    if ($lineas === [] && $errores === []) {
        $errores[] = 'Debe registrar al menos un renglon con monto.';
    }

    /* Comprobacion de la partida doble. */
    $totalDebe  = 0.0;
    $totalHaber = 0.0;

    foreach ($lineas as $linea) {
        $totalDebe  = round($totalDebe + $linea['debe'], 2);
        $totalHaber = round($totalHaber + $linea['haber'], 2);
    }

    if ($errores === [] && round($totalDebe, 2) !== round($totalHaber, 2)) {
        $errores[] = 'LA PARTIDA NO CUADRA: el Debe (' . formatearDinero($totalDebe)
            . ') es distinto al Haber (' . formatearDinero($totalHaber) . ').';
        $errores[] = 'El asiento fue RECHAZADO. Registre la partida corregida.';
    }

    $enviado['fecha']    = ($fecha === null) ? $fechaCruda : fechaAMostrar($fecha);
    $enviado['concepto'] = $concepto;

    if ($errores === []) {
        $pdo->beginTransaction();

        try {
            $pdo->prepare('INSERT INTO asientos (fecha, concepto) VALUES (?, ?)')->execute([$fecha, $concepto]);

            $nuevoId = (int) $pdo->lastInsertId();
            $sql     = 'INSERT INTO renglones (asiento_id, linea, cuenta, debe, haber) VALUES (?, ?, ?, ?, ?)';
            $ins     = $pdo->prepare($sql);

            foreach ($lineas as $n => $linea) {
                $ins->execute([$nuevoId, $n + 1, $linea['cuenta'], $linea['debe'], $linea['haber']]);
            }

            $pdo->commit();
            $exito = 'Asiento #' . $nuevoId . ' registrado y balanceado correctamente.';
            $accion = 'ver';
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errores[] = 'No se pudo guardar el asiento: ' . $e->getMessage();
        }
    }
}

/* =====================================================================
 *  CONSULTA DEL LIBRO DIARIO
 * ===================================================================== */
$asientos = $pdo->query(
    'SELECT a.id, a.fecha, a.concepto, r.cuenta, c.nombre, r.debe, r.haber
       FROM asientos a
       JOIN renglones r ON r.asiento_id = a.id
       LEFT JOIN cuentas c ON c.codigo = r.cuenta
   ORDER BY a.id, r.linea'
)->fetchAll();

// Agrupa los renglones por asiento.
$diario = [];

foreach ($asientos as $fila) {
    $diario[(int) $fila['id']]['fecha']    = $fila['fecha'];
    $diario[(int) $fila['id']]['concepto'] = $fila['concepto'] ?? '';
    $diario[(int) $fila['id']]['lineas'][]  = [
        'cuenta' => $fila['cuenta'],
        'nombre' => $fila['nombre'] ?? '(cuenta eliminada)',
        'debe'   => (float) $fila['debe'],
        'haber'  => (float) $fila['haber'],
    ];
}

$tituloModulo = ($accion === 'nueva')
    ? 'Módulo 2 · Registrar partida de diario'
    : 'Módulo 2 · Ver Libro Diario';

iniciarPagina($tituloModulo, 'modulos/modulo2_diario.php', $accion);

if ($accion === 'nueva') {
    $cuentaMaestro = obtenerCatalogo($pdo);
    $renglones     = $enviado['renglones'];

    if ($renglones === []) {
        $renglones = [['cuenta' => '', 'debe' => '', 'haber' => '']];
    }

    ?>
    <?= mensajeErrores($errores) ?>

    <section class="tarjeta">
        <div class="encabezado-seccion">
            <h3>Nueva partida de diario</h3>
            <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo2_diario.php?accion=ver">Ver Libro Diario</a>
        </div>

        <form method="post" id="formPartida"
              action="<?= RUTA_BASE ?>/modulos/modulo2_diario.php?accion=nueva">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1rem;">
                <p>
                    <label for="fecha">Fecha (dd/mm/aaaa, vacío = hoy)</label>
                    <input type="text" id="fecha" name="fecha" value="<?= h($enviado['fecha']) ?>"
                           placeholder="<?= h(fechaHoy()) ?>" size="14">
                </p>
                <p>
                    <label for="concepto">Concepto / Glosa (opcional)</label>
                    <input type="text" id="concepto" name="concepto" value="<?= h($enviado['concepto'] ?? '') ?>"
                           placeholder="Ej. Pago de proveedores o Venta contado">
                </p>
            </div>

            <p class="ayuda">
                Escriba el monto en <strong>DEBE</strong> o en <strong>HABER</strong>
                de cada renglón; el campo contrario queda en cero.
            </p>

            <div class="tabla-envoltura">
                <table id="tablaRenglones">
                    <thead>
                        <tr>
                            <th class="num">#</th>
                            <th>Cuenta</th>
                            <th class="num">Debe</th>
                            <th class="num">Haber</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($renglones as $n => $linea): ?>
                        <tr>
                            <td class="num"><?= $n + 1 ?></td>
                            <td>
                                <select name="cuenta[]">
                                    <option value="">(sin seleccionar)</option>
                                    <?php foreach ($cuentaMaestro as $c): ?>
                                        <option value="<?= h($c['codigo']) ?>"
                                            <?= ($linea['cuenta'] === $c['codigo']) ? 'selected' : '' ?>>
                                            <?= h($c['codigo'] . ' ' . $c['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="debe[]"  value="<?= h($linea['debe']) ?>" size="10"></td>
                            <td><input type="text" name="haber[]" value="<?= h($linea['haber']) ?>" size="10"></td>
                            <td><button type="button" class="btn-quitar" onclick="quitarRenglon(this)" title="Quitar renglón">&times;</button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="acciones-form">
                <button type="button" class="btn-sec" onclick="agregarRenglon()">Agregar renglón</button>
            </div>

            <div class="fila-resumen">
                <div class="resumen-caja">
                    <dl><dt>Total debe</dt><dd id="totalDebe">0.00</dd></dl>
                </div>
                <div class="resumen-caja">
                    <dl><dt>Total haber</dt><dd id="totalHaber">0.00</dd></dl>
                </div>
                <div class="resumen-caja destacado">
                    <dl><dt>Diferencia</dt><dd id="diferencia">0.00</dd></dl>
                </div>
            </div>

            <div class="acciones-form">
                <button type="submit">Registrar partida</button>
                <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo2_diario.php">Cancelar</a>
            </div>
        </form>
    </section>

    <script>
    const CUENTAS = <?= json_encode($cuentaMaestro, JSON_UNESCAPED_UNICODE) ?>;

    function opcionesCuentas() {
        let html = '<option value="">(sin seleccionar)</option>';
        for (const c of CUENTAS) {
            html += '<option value="' + c.codigo + '">' + c.codigo + ' - ' + c.nombre + '</option>';
        }
        return html;
    }

    function cuerpoTabla() {
        return document.getElementById('tablaRenglones').tBodies[0];
    }

    function agregarRenglon() {
        const cuerpo = cuerpoTabla();
        const fila   = cuerpo.rows[cuerpo.rows.length - 1].cloneNode(true);

        fila.querySelectorAll('input').forEach(i => { i.value = ''; });
        fila.querySelector('select').innerHTML = opcionesCuentas();
        cuerpo.appendChild(fila);
        renumerar();
        recalcular();
    }

    function quitarRenglon(boton) {
        const cuerpo = cuerpoTabla();
        if (cuerpo.rows.length <= 1) { return; }
        boton.closest('tr').remove();
        renumerar();
        recalcular();
    }

    function renumerar() {
        const filas = cuerpoTabla().rows;
        for (let i = 0; i < filas.length; i++) {
            filas[i].cells[0].textContent = i + 1;
        }
    }

    function recalcular() {
        let debe = 0, haber = 0;
        const filas = cuerpoTabla().rows;

        for (let i = 0; i < filas.length; i++) {
            const d = parseFloat((filas[i].querySelector('input[name="debe[]"]').value || '').replace(',', '.')) || 0;
            const h = parseFloat((filas[i].querySelector('input[name="haber[]"]').value || '').replace(',', '.')) || 0;
            debe  = Math.round((debe  + d) * 100) / 100;
            haber = Math.round((haber + h) * 100) / 100;
        }

        document.getElementById('totalDebe').textContent  = debe.toFixed(2);
        document.getElementById('totalHaber').textContent = haber.toFixed(2);

        const dif = Math.round((debe - haber) * 100) / 100;
        const caja = document.getElementById('diferencia').closest('.resumen-caja');
        document.getElementById('diferencia').textContent = dif.toFixed(2);
        caja.classList.toggle('destacado', dif !== 0);
        caja.classList.toggle('resumen-alerta', dif !== 0);
    }

    document.addEventListener('DOMContentLoaded', () => {
        renumerar();
        recalcular();
    });

    document.getElementById('formPartida').addEventListener('input', recalcular);
    </script>

    <?php
} else {
    ?>
    <section class="tarjeta">
        <div class="encabezado-seccion">
            <h3>Libro Diario</h3>
            <a class="btn-a" href="<?= RUTA_BASE ?>/modulos/modulo2_diario.php?accion=nueva">Registrar nueva partida</a>
        </div>

        <?php if ($exito !== ''): ?>
            <?= mensajeOk($exito) ?>
        <?php endif; ?>

        <?php if ($diario === []): ?>
            <div class="estado-vacio">
                <strong>Aún no hay asientos registrados</strong>
                Registra tu primera partida para comenzar.
            </div>
        <?php else: ?>
            <?php foreach ($diario as $numero => $asiento): ?>
                <div class="asiento" id="asiento-<?= $numero ?>">
                    <div class="asiento-cabecera">
                        <div>
                            <span class="asiento-num">Asiento #<?= $numero ?></span>
                            <?php if (!empty($asiento['concepto'])): ?>
                                <span class="asiento-concepto" style="margin-left:.6rem; color:var(--tc-text-muted); font-size:.85rem;">
                                    &mdash; <?= h($asiento['concepto']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <span class="asiento-fecha"><?= h(fechaAMostrar($asiento['fecha'])) ?></span>
                    </div>

                    <div class="tabla-envoltura">
                        <table>
                            <thead>
                                <tr>
                                    <th>Cuenta</th>
                                    <th>Nombre</th>
                                    <th class="num">Debe</th>
                                    <th class="num">Haber</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $totalDebe  = 0.0;
                            $totalHaber = 0.0;
                            foreach ($asiento['lineas'] as $linea):
                                $totalDebe  = round($totalDebe + $linea['debe'], 2);
                                $totalHaber = round($totalHaber + $linea['haber'], 2);
                            ?>
                                <tr>
                                    <td><?= h($linea['cuenta']) ?></td>
                                    <td><?= h(recortar($linea['nombre'], 32)) ?></td>
                                    <td class="num"><?= h(formatearDinero($linea['debe'])) ?></td>
                                    <td class="num"><?= h(formatearDinero($linea['haber'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                                <tr>
                                    <th>Totales</th>
                                    <th></th>
                                    <th class="num"><?= h(formatearDinero($totalDebe)) ?></th>
                                    <th class="num"><?= h(formatearDinero($totalHaber)) ?></th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
            <p class="pie-tabla">Total de asientos: <strong><?= count($diario) ?></strong></p>
        <?php endif; ?>
    </section>
    <?php
}

terminarPagina();
