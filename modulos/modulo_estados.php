<?php
/**
 * MÓDULO 2 - BALANZA DE COMPROBACIÓN Y ESTADOS FINANCIEROS (NUEVO)
 *
 * 2.1. Balanza de Comprobación
 * 2.2. Estado de Resultados (Pérdidas y Ganancias)
 * 2.3. Balance General (Estado de Situación Financiera)
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';

$tab = (string) ($_GET['tab'] ?? 'balanza');
if (!in_array($tab, ['balanza', 'resultados', 'balance'], true)) {
    $tab = 'balanza';
}

$tituloPagina = match ($tab) {
    'balanza'    => 'Módulo 2 · 2.1 Balanza de Comprobación',
    'resultados' => 'Módulo 2 · 2.2 Estado de Resultados',
    'balance'    => 'Módulo 2 · 2.3 Balance General',
};

// Cálculos
$balanza = calcularBalanzaComprobacion($pdo);
$eeff    = calcularEstadoResultados($pdo);
$bg      = calcularBalanceGeneral($pdo, $eeff['utilidad_operativa']);

iniciarPagina($tituloPagina, 'modulos/modulo_estados.php', $tab);

?>
<!-- Navegacion por pestañas del Modulo 2 -->
<div class="tabs-modulo">
    <a href="<?= RUTA_BASE ?>/modulos/modulo_estados.php?tab=balanza"
       class="tab-link<?= ($tab === 'balanza') ? ' activo' : '' ?>">
        <?= iconoSvg('scale') ?>
        <span>2.1. Balanza de Comprobación</span>
    </a>
    <a href="<?= RUTA_BASE ?>/modulos/modulo_estados.php?tab=resultados"
       class="tab-link<?= ($tab === 'resultados') ? ' activo' : '' ?>">
        <?= iconoSvg('report') ?>
        <span>2.2. Estado de Resultados</span>
    </a>
    <a href="<?= RUTA_BASE ?>/modulos/modulo_estados.php?tab=balance"
       class="tab-link<?= ($tab === 'balance') ? ' activo' : '' ?>">
        <?= iconoSvg('file') ?>
        <span>2.3. Balance General</span>
    </a>
</div>

<?php if ($tab === 'balanza'): ?>
    <!-- ===================================================================
         2.1. BALANZA DE COMPROBACIÓN
         =================================================================== -->
    <section class="tarjeta">
        <div class="encabezado-seccion">
            <div>
                <h3>2.1. Balanza de Comprobación</h3>
                <p class="ayuda" style="margin-bottom:0">
                    Recorre todas las cuentas activas del Libro Mayor, totalizando movimientos (Debe y Haber) y saldos (Deudor o Acreedor).
                </p>
            </div>
            <div class="acciones-header">
                <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo3_mayor.php">Ver Libro Mayor</a>
                <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo2_diario.php?accion=ver">Ver Libro Diario</a>
            </div>
        </div>

        <?php if ($balanza['cuentas'] === []): ?>
            <div class="estado-vacio">
                <strong>No hay cuentas activas en el Libro Mayor</strong>
                Registra partidas de diario o carga un caso de prueba para generar la balanza de comprobación.
                <div style="margin-top: 1rem;">
                    <a class="btn-a" href="<?= RUTA_BASE ?>/caso_prueba.php">Cargar caso de prueba</a>
                </div>
            </div>
        <?php else: ?>
            <div class="tabla-envoltura">
                <table class="tabla-balanza">
                    <thead>
                        <tr>
                            <th rowspan="2">Código</th>
                            <th rowspan="2">Cuenta</th>
                            <th colspan="2" class="th-grupo">Movimientos</th>
                            <th colspan="2" class="th-grupo">Saldos</th>
                        </tr>
                        <tr>
                            <th class="num">Debe (&sum;)</th>
                            <th class="num">Haber (&sum;)</th>
                            <th class="num">Deudor</th>
                            <th class="num">Acreedor</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($balanza['cuentas'] as $c): ?>
                        <tr>
                            <td><code><?= h($c['codigo']) ?></code></td>
                            <td>
                                <strong><?= h($c['nombre']) ?></strong>
                                <small style="display:block; color:var(--tc-text-muted); font-size:.78rem;"><?= h($c['rubro_texto']) ?></small>
                            </td>
                            <td class="num"><?= h(formatearDinero($c['cargos'])) ?></td>
                            <td class="num"><?= h(formatearDinero($c['abonos'])) ?></td>
                            <td class="num"><?= ($c['saldo_deudor'] > 0) ? h(formatearDinero($c['saldo_deudor'])) : '-' ?></td>
                            <td class="num"><?= ($c['saldo_acreedor'] > 0) ? h(formatearDinero($c['saldo_acreedor'])) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">TOTALES GENERALES</th>
                            <th class="num"><?= h(formatearDinero($balanza['total_debe'])) ?></th>
                            <th class="num"><?= h(formatearDinero($balanza['total_haber'])) ?></th>
                            <th class="num"><?= h(formatearDinero($balanza['total_saldo_deudor'])) ?></th>
                            <th class="num"><?= h(formatearDinero($balanza['total_saldo_acreedor'])) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Resumen de Validación -->
            <div class="fila-resumen" style="margin-top:1.25rem;">
                <div class="resumen-caja <?= $balanza['movimientos_cuadrados'] ? '' : 'resumen-alerta' ?>">
                    <dl>
                        <dt>&sum; Movimientos Deudores vs Acreedores</dt>
                        <dd style="font-size:1.15rem;"><?= h(formatearDinero($balanza['total_debe'])) ?> = <?= h(formatearDinero($balanza['total_haber'])) ?></dd>
                    </dl>
                </div>
                <div class="resumen-caja <?= $balanza['saldos_cuadrados'] ? '' : 'resumen-alerta' ?>">
                    <dl>
                        <dt>&sum; Saldos Deudores vs Acreedores</dt>
                        <dd style="font-size:1.15rem;"><?= h(formatearDinero($balanza['total_saldo_deudor'])) ?> = <?= h(formatearDinero($balanza['total_saldo_acreedor'])) ?></dd>
                    </dl>
                </div>
                <div class="resumen-caja destacado <?= $balanza['esta_cuadrada'] ? '' : 'resumen-alerta' ?>">
                    <dl>
                        <dt>Estado de la Balanza</dt>
                        <dd style="font-size:1.1rem;">
                            <?= $balanza['esta_cuadrada'] ? 'CUADRADA [OK]' : 'DESCUADRADA [!]' ?>
                        </dd>
                    </dl>
                </div>
            </div>

            <?php if ($balanza['esta_cuadrada']): ?>
                <div class="msg ok" style="margin-top:1rem;">
                    <strong>[OK] Regla de Validación Cumplida:</strong>
                    &sum; Movimientos Deudores (<?= h(formatearDinero($balanza['total_debe'])) ?>) = &sum; Movimientos Acreedores (<?= h(formatearDinero($balanza['total_haber'])) ?>) y
                    &sum; Saldos Deudores (<?= h(formatearDinero($balanza['total_saldo_deudor'])) ?>) = &sum; Saldos Acreedores (<?= h(formatearDinero($balanza['total_saldo_acreedor'])) ?>).
                </div>
            <?php else: ?>
                <div class="msg error" style="margin-top:1rem;">
                    <strong>[ERROR] Desbalance Detectado en la Balanza:</strong>
                    Revise las partidas de diario registradas para localizar la discrepancia.
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </section>

<?php elseif ($tab === 'resultados'): ?>
    <!-- ===================================================================
         2.2. ESTADO DE RESULTADOS (PÉRDIDAS Y GANANCIAS)
         =================================================================== -->
    <section class="tarjeta">
        <div class="encabezado-seccion">
            <div>
                <h3>2.2. Estado de Resultados (Pérdidas y Ganancias)</h3>
                <p class="ayuda" style="margin-bottom:0">
                    Cálculo jerárquico del período: Ingresos por Ventas &minus; Costo de Ventas = Utilidad Bruta &minus; Gastos Operativos = Utilidad Neta del Ejercicio.
                </p>
            </div>
            <div>
                <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_estados.php?tab=balance">Ir al Balance General &rarr;</a>
            </div>
        </div>

        <div class="documento-financiero">
            <header class="doc-header">
                <h2>EMPRESA CONTABLE S.A. DE C.V.</h2>
                <h4>ESTADO DE RESULTADOS</h4>
                <p>Del período actual &middot; Cifras expresadas en Dólares de los Estados Unidos ($ USD)</p>
            </header>

            <div class="tabla-envoltura" style="margin-top:1rem;">
                <table class="tabla-eeff">
                    <tbody>
                        <!-- 1. INGRESOS POR VENTAS -->
                        <tr class="fila-rubro">
                            <td colspan="2"><strong>INGRESOS OPERACIONALES (VENTAS)</strong></td>
                            <td class="num"></td>
                        </tr>
                        <?php if ($eeff['ingresos'] === []): ?>
                            <tr>
                                <td style="padding-left:2rem; color:var(--tc-text-muted);">Sin ingresos por ventas registrados</td>
                                <td></td>
                                <td class="num">$0.00</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($eeff['ingresos'] as $ing): ?>
                                <tr>
                                    <td style="padding-left:2rem;"><?= h($ing['codigo'] . ' - ' . $ing['nombre']) ?></td>
                                    <td class="num"><?= h(formatearDinero($ing['monto'])) ?></td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr class="fila-subtotal">
                            <th colspan="2" style="padding-left:1.5rem;">TOTAL INGRESOS POR VENTAS</th>
                            <th class="num"><?= h(formatearDinero($eeff['total_ingresos'])) ?></th>
                        </tr>

                        <!-- 2. COSTO DE VENTAS -->
                        <tr class="fila-rubro">
                            <td colspan="2"><strong>(-) COSTO DE VENTAS</strong></td>
                            <td class="num"></td>
                        </tr>
                        <?php if ($eeff['costos'] === []): ?>
                            <tr>
                                <td style="padding-left:2rem; color:var(--tc-text-muted);">Sin costo de ventas registrado</td>
                                <td></td>
                                <td class="num">$0.00</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($eeff['costos'] as $cos): ?>
                                <tr>
                                    <td style="padding-left:2rem;"><?= h($cos['codigo'] . ' - ' . $cos['nombre']) ?></td>
                                    <td class="num"><?= h(formatearDinero($cos['monto'])) ?></td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr class="fila-subtotal">
                            <th colspan="2" style="padding-left:1.5rem;">TOTAL COSTO DE VENTAS</th>
                            <th class="num" style="color:var(--tc-err-text);"><?= h(formatearDinero($eeff['total_costos'])) ?></th>
                        </tr>

                        <!-- UTILIDAD BRUTA -->
                        <tr class="fila-resultado-intermedio">
                            <th colspan="2">(=) UTILIDAD BRUTA EN VENTAS</th>
                            <th class="num monto-destacado"><?= h(formatearDinero($eeff['utilidad_bruta'])) ?></th>
                        </tr>

                        <!-- 3. GASTOS OPERATIVOS -->
                        <tr class="fila-rubro">
                            <td colspan="2"><strong>(-) GASTOS OPERATIVOS</strong></td>
                            <td class="num"></td>
                        </tr>
                        <!-- Gastos de Administracion -->
                        <tr>
                            <td colspan="2" style="padding-left:1.5rem; font-weight:600; color:var(--tc-text-muted);">Gastos de Administración:</td>
                            <td></td>
                        </tr>
                        <?php if ($eeff['gastos_admin'] === []): ?>
                            <tr>
                                <td style="padding-left:2.5rem; color:var(--tc-text-muted); font-size:.85rem;">Ninguno</td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($eeff['gastos_admin'] as $ga): ?>
                                <tr>
                                    <td style="padding-left:2.5rem; font-size:.88rem;"><?= h($ga['codigo'] . ' - ' . $ga['nombre']) ?></td>
                                    <td class="num" style="font-size:.88rem;"><?= h(formatearDinero($ga['monto'])) ?></td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr>
                            <td style="padding-left:1.5rem;"><em>Subtotal Gastos de Administración</em></td>
                            <td></td>
                            <td class="num"><?= h(formatearDinero($eeff['total_gastos_admin'])) ?></td>
                        </tr>

                        <!-- Gastos de Venta -->
                        <tr>
                            <td colspan="2" style="padding-left:1.5rem; font-weight:600; color:var(--tc-text-muted);">Gastos de Venta:</td>
                            <td></td>
                        </tr>
                        <?php if ($eeff['gastos_venta'] === []): ?>
                            <tr>
                                <td style="padding-left:2.5rem; color:var(--tc-text-muted); font-size:.85rem;">Ninguno</td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($eeff['gastos_venta'] as $gv): ?>
                                <tr>
                                    <td style="padding-left:2.5rem; font-size:.88rem;"><?= h($gv['codigo'] . ' - ' . $gv['nombre']) ?></td>
                                    <td class="num" style="font-size:.88rem;"><?= h(formatearDinero($gv['monto'])) ?></td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr>
                            <td style="padding-left:1.5rem;"><em>Subtotal Gastos de Venta</em></td>
                            <td></td>
                            <td class="num"><?= h(formatearDinero($eeff['total_gastos_venta'])) ?></td>
                        </tr>

                        <tr class="fila-subtotal">
                            <th colspan="2" style="padding-left:1.5rem;">TOTAL GASTOS OPERATIVOS</th>
                            <th class="num" style="color:var(--tc-err-text);"><?= h(formatearDinero($eeff['total_gastos_operativos'])) ?></th>
                        </tr>

                        <!-- UTILIDAD O PÉRDIDA NETA OPERATIVA -->
                        <tr class="fila-total-final">
                            <th colspan="2">
                                <?= $eeff['es_utilidad'] ? '(=) UTILIDAD OPERATIVA (NETA DEL EJERCICIO)' : '(=) PÉRDIDA OPERATIVA DEL EJERCICIO' ?>
                            </th>
                            <th class="num" style="font-size:1.15rem; color: <?= $eeff['es_utilidad'] ? 'var(--tc-ok-text)' : 'var(--tc-err-text)' ?>;">
                                <?= h(formatearDinero($eeff['utilidad_operativa'])) ?>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="msg info" style="margin-top:1.25rem;">
                <strong>[Conexión Contable Automática]:</strong>
                El saldo resultante de <strong><?= h(formatearDinero($eeff['utilidad_operativa'])) ?></strong>
                se transfiere de forma automática para alimentar el bloque de <strong>Patrimonio</strong> en el
                <a href="<?= RUTA_BASE ?>/modulos/modulo_estados.php?tab=balance">Balance General</a>.
            </div>
        </div>
    </section>

<?php elseif ($tab === 'balance'): ?>
    <!-- ===================================================================
         2.3. BALANCE GENERAL (ESTADO DE SITUACIÓN FINANCIERA)
         =================================================================== -->
    <section class="tarjeta">
        <div class="encabezado-seccion">
            <div>
                <h3>2.3. Balance General (Estado de Situación Financiera)</h3>
                <p class="ayuda" style="margin-bottom:0">
                    Agrupado en Activo (Corriente y No Corriente), Pasivo (Corriente y No Corriente) y Patrimonio (Capital Social + Utilidad/Pérdida del Ejercicio).
                </p>
            </div>
            <div>
                <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_estados.php?tab=resultados">&larr; Ver Estado de Resultados</a>
            </div>
        </div>

        <div class="documento-financiero">
            <header class="doc-header">
                <h2>EMPRESA CONTABLE S.A. DE C.V.</h2>
                <h4>ESTADO DE SITUACIÓN FINANCIERA (BALANCE GENERAL)</h4>
                <p>Al período contable actual &middot; Expresado en Dólares ($ USD)</p>
            </header>

            <div class="cuadricula-balance">
                <!-- BLOQUE IZQUIERDO: ACTIVO -->
                <div class="bloque-balance">
                    <div class="tabla-envoltura">
                        <table class="tabla-eeff">
                            <thead>
                                <tr class="fila-titulo-bloque">
                                    <th colspan="2">1. ACTIVO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- ACTIVO CORRIENTE -->
                                <tr class="fila-rubro">
                                    <td colspan="2"><strong>ACTIVO CORRIENTE</strong></td>
                                </tr>
                                <?php if ($bg['activo_corriente'] === []): ?>
                                    <tr>
                                        <td style="padding-left:1.5rem; color:var(--tc-text-muted);">Sin saldos en activo corriente</td>
                                        <td class="num">$0.00</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($bg['activo_corriente'] as $ac): ?>
                                        <tr>
                                            <td style="padding-left:1.5rem;"><?= h($ac['codigo'] . ' ' . $ac['nombre']) ?></td>
                                            <td class="num"><?= h(formatearDinero($ac['saldo'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr class="fila-subtotal">
                                    <th style="padding-left:1.2rem;">Total Activo Corriente</th>
                                    <th class="num"><?= h(formatearDinero($bg['total_activo_corriente'])) ?></th>
                                </tr>

                                <!-- ACTIVO NO CORRIENTE -->
                                <tr class="fila-rubro">
                                    <td colspan="2"><strong>ACTIVO NO CORRIENTE</strong></td>
                                </tr>
                                <?php if ($bg['activo_no_corriente'] === []): ?>
                                    <tr>
                                        <td style="padding-left:1.5rem; color:var(--tc-text-muted);">Sin saldos en activo no corriente</td>
                                        <td class="num">$0.00</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($bg['activo_no_corriente'] as $anc): ?>
                                        <tr>
                                            <td style="padding-left:1.5rem;"><?= h($anc['codigo'] . ' ' . $anc['nombre']) ?></td>
                                            <td class="num"><?= h(formatearDinero($anc['saldo'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr class="fila-subtotal">
                                    <th style="padding-left:1.2rem;">Total Activo No Corriente</th>
                                    <th class="num"><?= h(formatearDinero($bg['total_activo_no_corriente'])) ?></th>
                                </tr>

                                <!-- TOTAL ACTIVO -->
                                <tr class="fila-total-final">
                                    <th>TOTAL ACTIVO</th>
                                    <th class="num monto-destacado" style="font-size:1.15rem;"><?= h(formatearDinero($bg['total_activo'])) ?></th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- BLOQUE DERECHO: PASIVO Y PATRIMONIO -->
                <div class="bloque-balance">
                    <div class="tabla-envoltura">
                        <table class="tabla-eeff">
                            <thead>
                                <tr class="fila-titulo-bloque">
                                    <th colspan="2">2. PASIVO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- PASIVO CORRIENTE -->
                                <tr class="fila-rubro">
                                    <td colspan="2"><strong>PASIVO CORRIENTE</strong></td>
                                </tr>
                                <?php if ($bg['pasivo_corriente'] === []): ?>
                                    <tr>
                                        <td style="padding-left:1.5rem; color:var(--tc-text-muted);">Sin saldos en pasivo corriente</td>
                                        <td class="num">$0.00</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($bg['pasivo_corriente'] as $pc): ?>
                                        <tr>
                                            <td style="padding-left:1.5rem;"><?= h($pc['codigo'] . ' ' . $pc['nombre']) ?></td>
                                            <td class="num"><?= h(formatearDinero($pc['saldo'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr class="fila-subtotal">
                                    <th style="padding-left:1.2rem;">Total Pasivo Corriente</th>
                                    <th class="num"><?= h(formatearDinero($bg['total_pasivo_corriente'])) ?></th>
                                </tr>

                                <!-- PASIVO NO CORRIENTE -->
                                <tr class="fila-rubro">
                                    <td colspan="2"><strong>PASIVO NO CORRIENTE</strong></td>
                                </tr>
                                <?php if ($bg['pasivo_no_corriente'] === []): ?>
                                    <tr>
                                        <td style="padding-left:1.5rem; color:var(--tc-text-muted);">Sin saldos en pasivo no corriente</td>
                                        <td class="num">$0.00</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($bg['pasivo_no_corriente'] as $pnc): ?>
                                        <tr>
                                            <td style="padding-left:1.5rem;"><?= h($pnc['codigo'] . ' ' . $pnc['nombre']) ?></td>
                                            <td class="num"><?= h(formatearDinero($pnc['saldo'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr class="fila-subtotal">
                                    <th style="padding-left:1.2rem;">Total Pasivo No Corriente</th>
                                    <th class="num"><?= h(formatearDinero($bg['total_pasivo_no_corriente'])) ?></th>
                                </tr>

                                <tr class="fila-subtotal" style="background-color:var(--tc-surface-alt);">
                                    <th>TOTAL PASIVO</th>
                                    <th class="num"><?= h(formatearDinero($bg['total_pasivo'])) ?></th>
                                </tr>

                                <!-- 3. PATRIMONIO -->
                                <tr class="fila-titulo-bloque">
                                    <th colspan="2">3. PATRIMONIO</th>
                                </tr>
                                <?php foreach ($bg['patrimonio_cuentas'] as $pat): ?>
                                    <tr>
                                        <td style="padding-left:1.5rem;"><?= h($pat['codigo'] . ' ' . $pat['nombre']) ?></td>
                                        <td class="num"><?= h(formatearDinero($pat['saldo'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="background-color:var(--tc-accent-soft);">
                                    <td style="padding-left:1.5rem;">
                                        <strong>Utilidad o Pérdida del Ejercicio</strong>
                                        <small style="display:block; color:var(--tc-text-muted);">Proveniente del Estado de Resultados</small>
                                    </td>
                                    <td class="num" style="font-weight:700; color:<?= ($bg['utilidad_ejercicio'] >= 0) ? 'var(--tc-ok-text)' : 'var(--tc-err-text)' ?>;">
                                        <?= h(formatearDinero($bg['utilidad_ejercicio'])) ?>
                                    </td>
                                </tr>
                                <tr class="fila-subtotal">
                                    <th style="padding-left:1.2rem;">Total Patrimonio</th>
                                    <th class="num"><?= h(formatearDinero($bg['total_patrimonio'])) ?></th>
                                </tr>

                                <!-- TOTAL PASIVO + PATRIMONIO -->
                                <tr class="fila-total-final">
                                    <th>TOTAL PASIVO + PATRIMONIO</th>
                                    <th class="num monto-destacado" style="font-size:1.15rem;"><?= h(formatearDinero($bg['total_pasivo_mas_patrimonio'])) ?></th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- VALIDACIÓN DE LA ECUACIÓN CONTABLE -->
            <div style="margin-top:1.5rem;">
                <?php if ($bg['esta_cuadrado']): ?>
                    <div class="msg ok">
                        <strong>[OK] Ecuación Contable Cumplida:</strong>
                        Total Activo (<?= h(formatearDinero($bg['total_activo'])) ?>) = Total Pasivo (<?= h(formatearDinero($bg['total_pasivo'])) ?>) + Total Patrimonio (<?= h(formatearDinero($bg['total_patrimonio'])) ?>). Diferencia: $0.00.
                        Integridad del ciclo contable verificada al 100%.
                    </div>
                    <script>
                    console.log("Ciclo contable íntegro: Total Activo (<?= $bg['total_activo'] ?>) = Total Pasivo + Total Patrimonio (<?= $bg['total_pasivo_mas_patrimonio'] ?>)");
                    </script>
                <?php else: ?>
                    <div class="msg error">
                        <strong>[ERROR] ERROR EN LA INTEGRIDAD DEL CICLO CONTABLE:</strong>
                        La ecuación contable no cuadra. Total Activo (<?= h(formatearDinero($bg['total_activo'])) ?>) &ne; Total Pasivo + Total Patrimonio (<?= h(formatearDinero($bg['total_pasivo_mas_patrimonio'])) ?>).
                        Diferencia detectada: <strong><?= h(formatearDinero($bg['diferencia'])) ?></strong>.
                    </div>
                    <script>
                    console.error("ERROR EN LA INTEGRIDAD DEL CICLO: Total Activo (<?= $bg['total_activo'] ?>) != Total Pasivo + Total Patrimonio (<?= $bg['total_pasivo_mas_patrimonio'] ?>). Diferencia: <?= $bg['diferencia'] ?>");
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php endif; ?>

<?php
terminarPagina();
