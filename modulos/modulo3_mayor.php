<?php
/**
 * MODULO 3 - GENERACION AUTOMATICA DEL LIBRO MAYOR
 *
 * Centraliza los renglones del diario por cuenta y calcula el saldo
 * final segun la naturaleza de cada cuenta.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';

/**
 * Procesa el diario y clasifica los montos por cuenta.
 * Equivalente al GROUP BY de la base de datos.
 *
 * @return array<int, array{cuenta:string, nombre:string, saldo:string, cargos:float, abonos:float}>
 */
function acumularMovimientos(PDO $pdo): array
{
    $sql = 'SELECT r.cuenta,
                   c.nombre,
                   c.saldo,
                   SUM(r.debe)  AS cargos,
                   SUM(r.haber) AS abonos
              FROM renglones r
         LEFT JOIN cuentas c ON c.codigo = r.cuenta
          GROUP BY r.cuenta, c.nombre, c.saldo
          ORDER BY r.cuenta';

    $movimientos = [];

    foreach ($pdo->query($sql)->fetchAll() as $fila) {
        if ($fila['nombre'] === null) {
            // La cuenta fue borrada del catalogo: no se muestra en el mayor.
            continue;
        }

        $movimientos[] = [
            'cuenta' => $fila['cuenta'],
            'nombre' => $fila['nombre'],
            'saldo'  => $fila['saldo'],
            'cargos' => round((float) $fila['cargos'], 2),
            'abonos' => round((float) $fila['abonos'], 2),
        ];
    }

    return $movimientos;
}

$movimientos = acumularMovimientos($pdo);

$sumaCargos = 0.0;
$sumaAbonos = 0.0;
$filasMayor = [];

foreach ($movimientos as $mov) {
    /* El saldo final depende de la naturaleza de la cuenta:
       - Saldo DEUDOR   (activos y gastos):  saldo = cargos - abonos
       - Saldo ACREEDOR (pasivos, capital, ingresos): saldo = abonos - cargos */
    $saldo = ($mov['saldo'] === 'DEUDOR')
        ? round($mov['cargos'] - $mov['abonos'], 2)
        : round($mov['abonos'] - $mov['cargos'], 2);

    $mov['saldoFinal'] = $saldo;
    $filasMayor[]      = $mov;

    $sumaCargos = round($sumaCargos + $mov['cargos'], 2);
    $sumaAbonos = round($sumaAbonos + $mov['abonos'], 2);
}

$diferenciaMayor = round($sumaCargos - $sumaAbonos, 2);
$tipoSaldo       = $diferenciaMayor >= 0 ? 'deudor' : 'acreedor';
$acumuladoTexto  = formatearDinero(abs($diferenciaMayor)) . ' ' . $tipoSaldo;

iniciarPagina('Libro Mayor · Centralización de Cuentas', 'modulos/modulo3_mayor.php', 'mayor');

?>
<section class="tarjeta">
    <div class="encabezado-seccion">
        <h3>Libro Mayor &middot; Centralización automática</h3>
        <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo2_diario.php?accion=ver">Ver Libro Diario</a>
    </div>

    <?php if ($filasMayor === []): ?>
        <div class="estado-vacio">
            <strong>No hay movimientos</strong>
            Registra partidas de diario primero para generar el mayor.
        </div>
    <?php else: ?>

        <div class="tabla-envoltura">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cuenta</th>
                        <th class="num">Cargos</th>
                        <th class="num">Abonos</th>
                        <th class="num">Saldo final</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filasMayor as $mov): ?>
                    <tr>
                        <td><strong><?= h($mov['cuenta']) ?></strong></td>
                        <td><?= h(recortar($mov['nombre'], 30)) ?></td>
                        <td class="num"><?= h(formatearDinero($mov['cargos'])) ?></td>
                        <td class="num"><?= h(formatearDinero($mov['abonos'])) ?></td>
                        <td class="num monto-destacado"><?= h(formatearDinero($mov['saldoFinal'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="fila-resumen">
            <div class="resumen-caja">
                <dl><dt>Total debe</dt><dd><?= h(formatearDinero($sumaCargos)) ?></dd></dl>
            </div>
            <div class="resumen-caja">
                <dl><dt>Total haber</dt><dd><?= h(formatearDinero($sumaAbonos)) ?></dd></dl>
            </div>
            <div class="resumen-caja destacado">
                <dl><dt>Saldo acumulado</dt><dd><?= h($acumuladoTexto) ?></dd></dl>
            </div>
        </div>

        <?php if ($sumaCargos === $sumaAbonos): ?>
            <?= mensajeOk('Comprobación: suma de cargos = suma de abonos. Partida doble correcta.') ?>
        <?php else: ?>
            <?= mensajeAviso('Comprobación: las sumas no coinciden. Revise el diario.') ?>
        <?php endif; ?>

    <?php endif; ?>
</section>

<?php
terminarPagina();
