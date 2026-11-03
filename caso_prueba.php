<?php
/**
 * CASOS DE PRUEBA Y DEMOSTRACIÓN
 *
 * Permite precargar datos de ejemplo:
 *   1. Caso Comercial Integral con Kardex (Módulos 2 y 3 NUEVOS):
 *      Capital, activo fijo, compras con IVA y Kardex (Promedio Ponderado),
 *      ventas con IVA y doble asiento (Ingreso + Costo), gastos operativos,
 *      Balanza de Comprobación y Estados Financieros perfectamente cuadrados.
 *   2. Caso de Servicios de Software (Caso base del 1er período).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';

$exito   = '';
$errores = [];

$tipoCaso = (string) ($_POST['tipo_caso'] ?? 'comercial');

/** Transacciones del caso clásico de servicios. */
function casoServicios(): array
{
    return [
        [
            'fecha'    => '01/10/2026',
            'concepto' => 'Aporte de capital social inicial',
            'lineas'   => [
                ['1.1.2', 15000.00, 0.0],
                ['3.1.1', 0.0, 15000.00],
            ],
        ],
        [
            'fecha'    => '02/10/2026',
            'concepto' => 'Compra de servidores a credito',
            'lineas'   => [
                ['1.2.1', 4000.00, 0.0],
                ['2.1.1', 0.0, 4000.00],
            ],
        ],
        [
            'fecha'    => '03/10/2026',
            'concepto' => 'Cobro de licenciamiento anual',
            'lineas'   => [
                ['1.1.2', 2500.00, 0.0],
                ['4.1.1', 0.0, 2500.00],
            ],
        ],
        [
            'fecha'    => '04/10/2026',
            'concepto' => 'Pago de nomina de programadores',
            'lineas'   => [
                ['5.1.1', 1800.00, 0.0],
                ['1.1.2', 0.0, 1800.00],
            ],
        ],
        [
            'fecha'    => '05/10/2026',
            'concepto' => 'Servicio facturado: contado y credito',
            'lineas'   => [
                ['1.1.3', 1200.00, 0.0],
                ['1.1.2', 800.00, 0.0],
                ['4.1.2', 0.0, 2000.00],
            ],
        ],
    ];
}

/** Carga el caso comercial completo con inventario y kardex. */
function cargarCasoComercial(PDO $pdo): void
{
    // 1. Limpieza total
    $pdo->exec('DELETE FROM kardex');
    $pdo->exec('DELETE FROM productos');
    $pdo->exec('DELETE FROM renglones');
    $pdo->exec('DELETE FROM asientos');

    // 2. Crear producto para el Kardex
    $prodId = crearProducto(
        $pdo,
        'PROD-01',
        'Laptop Dell Latitude 5420 Core i7 16GB RAM',
        0.0,
        0.0
    );

    // 3. Asiento 1: Aporte de capital ($25,000)
    $fecha1 = fechaAMysql('01/10/2026');
    $pdo->prepare('INSERT INTO asientos (fecha, concepto) VALUES (?, ?)')->execute([
        $fecha1,
        'Aporte de capital social inicial en cuenta bancaria',
    ]);
    $asiento1 = (int) $pdo->lastInsertId();
    $insR = $pdo->prepare('INSERT INTO renglones (asiento_id, linea, cuenta, debe, haber) VALUES (?, ?, ?, ?, ?)');
    $insR->execute([$asiento1, 1, '1.1.2', 25000.00, 0.00]);
    $insR->execute([$asiento1, 2, '3.1.1', 0.00, 25000.00]);

    // 4. Asiento 2: Compra de servidores / equipo de cómputo ($3,000)
    $fecha2 = fechaAMysql('02/10/2026');
    $pdo->prepare('INSERT INTO asientos (fecha, concepto) VALUES (?, ?)')->execute([
        $fecha2,
        'Compra de equipo de cómputo para oficina al contado',
    ]);
    $asiento2 = (int) $pdo->lastInsertId();
    $insR->execute([$asiento2, 1, '1.2.1', 3000.00, 0.00]);
    $insR->execute([$asiento2, 2, '1.1.2', 0.00, 3000.00]);

    // 5. Compra 1 Kardex: 20 un. a $500.00 c/u pagado con Bancos
    registrarCompraKardex(
        $pdo,
        $prodId,
        '03/10/2026',
        20.0,
        500.00,
        '1.1.2',
        'Factura Proveedor #101 al contado'
    );

    // 6. Compra 2 Kardex: 10 un. a $560.00 c/u al crédito con Proveedores
    // Nuevo costo promedio = (10,000 + 5,600) / 30 = $520.00 c/u
    registrarCompraKardex(
        $pdo,
        $prodId,
        '05/10/2026',
        10.0,
        560.00,
        '2.1.1',
        'Factura Proveedor #102 a crédito'
    );

    // 7. Venta Kardex: 18 un. a $850.00 c/u cobrado con Bancos
    // Aplica costo promedio vigente de $520.00 -> Costo de ventas: $9,360.00
    // Stock restante: 12 un. a $520.00 = $6,240.00
    registrarVentaKardex(
        $pdo,
        $prodId,
        '10/10/2026',
        18.0,
        850.00,
        '1.1.2',
        'Factura Cliente #201 al contado'
    );

    // 8. Gastos de Administración ($1,500)
    $fechaAdm = fechaAMysql('15/10/2026');
    $pdo->prepare('INSERT INTO asientos (fecha, concepto) VALUES (?, ?)')->execute([
        $fechaAdm,
        'Pago de nómina administrativa y servicios básicos',
    ]);
    $asientoAdm = (int) $pdo->lastInsertId();
    $insR->execute([$asientoAdm, 1, '5.2.1', 1500.00, 0.00]);
    $insR->execute([$asientoAdm, 2, '1.1.2', 0.00, 1500.00]);

    // 9. Gastos de Venta ($800)
    $fechaVta = fechaAMysql('20/10/2026');
    $pdo->prepare('INSERT INTO asientos (fecha, concepto) VALUES (?, ?)')->execute([
        $fechaVta,
        'Pago de publicidad comercial y comisiones de venta',
    ]);
    $asientoVta = (int) $pdo->lastInsertId();
    $insR->execute([$asientoVta, 1, '5.2.2', 800.00, 0.00]);
    $insR->execute([$asientoVta, 2, '1.1.2', 0.00, 800.00]);
}

/** Carga el caso de servicios básico. */
function cargarCasoServicios(PDO $pdo): void
{
    $pdo->exec('DELETE FROM kardex');
    $pdo->exec('DELETE FROM productos');
    $pdo->exec('DELETE FROM renglones');
    $pdo->exec('DELETE FROM asientos');

    $insAsiento = $pdo->prepare('INSERT INTO asientos (fecha, concepto) VALUES (?, ?)');
    $insRenglon = $pdo->prepare(
        'INSERT INTO renglones (asiento_id, linea, cuenta, debe, haber) VALUES (?, ?, ?, ?, ?)'
    );

    foreach (casoServicios() as $caso) {
        $fecha = fechaAMysql($caso['fecha']);
        $insAsiento->execute([$fecha, $caso['concepto']]);
        $id = (int) $pdo->lastInsertId();

        foreach ($caso['lineas'] as $n => $linea) {
            $insRenglon->execute([$id, $n + 1, $linea[0], $linea[1], $linea[2]]);
        }
    }
}

$yaHay = (int) $pdo->query('SELECT COUNT(*) FROM asientos')->fetchColumn() > 0;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $tipo = (string) ($_POST['tipo_caso'] ?? 'comercial');
    try {
        if ($tipo === 'comercial') {
            cargarCasoComercial($pdo);
            $exito = '¡Caso Comercial Integral cargado con éxito! Se registraron productos, compras con IVA y Kardex (Promedio Ponderado), ventas con doble asiento contable, gastos operativos y estados financieros equilibrados.';
        } else {
            cargarCasoServicios($pdo);
            $exito = 'Se cargaron las 5 transacciones del caso de servicios de software.';
        }
        $yaHay = true;
    } catch (Throwable $e) {
        $errores[] = 'Error al cargar el caso de prueba: ' . $e->getMessage();
    }
}

iniciarPagina('Casos de prueba y demostración', 'caso_prueba.php');

?>
<?php if ($exito !== ''): ?>
    <?= mensajeOk($exito) ?>
<?php endif; ?>

<?= mensajeErrores($errores) ?>

<section class="tarjeta">
    <div class="encabezado-seccion">
        <div>
            <h3>Casos de prueba &middot; Demostración guiada</h3>
            <p class="ayuda" style="margin-bottom:0">
                Seleccione el caso práctico que desea cargar en el sistema para probar automáticamente todos los módulos.
            </p>
        </div>
        <div class="acciones-header">
            <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_estados.php?tab=balanza">Balanza de Comprobación</a>
            <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php">Control de Kardex</a>
        </div>
    </div>

    <?php if ($yaHay): ?>
        <?= mensajeAviso('Actualmente ya existen registros en el sistema. Al cargar un caso se reemplazarán los datos anteriores.') ?>
    <?php endif; ?>

    <form method="post" action="<?= RUTA_BASE ?>/caso_prueba.php">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:1.25rem; margin:1.25rem 0;">
            <!-- Opción 1: Caso Comercial Integral -->
            <label class="tarjeta-opcion-caso" style="display:block; padding:1.2rem; border-radius:var(--tc-r-md); border:2px solid var(--tc-accent); background-color:var(--tc-surface-alt); cursor:pointer;">
                <div style="display:flex; align-items:center; gap:.6rem; margin-bottom:.5rem;">
                    <input type="radio" name="tipo_caso" value="comercial" checked style="transform:scale(1.2);">
                    <strong style="font-size:1.02rem; color:var(--tc-accent);">CASO COMERCIAL CON KARDEX (RECOMENDADO)</strong>
                </div>
                <p style="font-size:.86rem; color:var(--tc-text-muted); line-height:1.5; margin:0 0 .75rem 1.6rem;">
                    Demuestra los <strong>Módulos 2 y 3 NUEVOS</strong> de forma integral:
                </p>
                <ul style="font-size:.82rem; margin:0 0 0 2.2rem; padding:0; color:var(--tc-text); line-height:1.6;">
                    <li>Aporte de capital inicial ($25,000) y compra de equipo ($3,000)</li>
                    <li>Compras de mercadería con <strong>Kardex Promedio Ponderado</strong> e IVA Crédito Fiscal (13%)</li>
                    <li>Venta con cálculo de costo promedio y <strong>doble asiento contable</strong> (Ingreso + Costo de Ventas)</li>
                    <li>Gastos operativos (Administración $1,500 y Ventas $800)</li>
                    <li><strong>2.1 Balanza de Comprobación</strong> cuadrada con fila de totales verificada</li>
                    <li><strong>2.2 Estado de Resultados</strong>: Ingresos &minus; Costo de Ventas = Utilidad Bruta ($5,940) &minus; Gastos = Utilidad Operativa ($3,640)</li>
                    <li><strong>2.3 Balance General</strong> donde Activo ($36,957) = Pasivo ($8,317) + Patrimonio ($28,640)</li>
                </ul>
            </label>

            <!-- Opción 2: Caso de Servicios -->
            <label class="tarjeta-opcion-caso" style="display:block; padding:1.2rem; border-radius:var(--tc-r-md); border:1px solid var(--tc-border); background-color:var(--tc-surface-alt); cursor:pointer;">
                <div style="display:flex; align-items:center; gap:.6rem; margin-bottom:.5rem;">
                    <input type="radio" name="tipo_caso" value="servicios" style="transform:scale(1.2);">
                    <strong style="font-size:1.02rem;">Caso Base: Empresa de Software</strong>
                </div>
                <p style="font-size:.86rem; color:var(--tc-text-muted); line-height:1.5; margin:0 0 .75rem 1.6rem;">
                    Las 5 transacciones de servicios del 1er período:
                </p>
                <ul style="font-size:.82rem; margin:0 0 0 2.2rem; padding:0; color:var(--tc-text); line-height:1.6;">
                    <li>Aporte de capital social ($15,000)</li>
                    <li>Compra de servidores a crédito ($4,000)</li>
                    <li>Cobro de licenciamiento anual ($2,500)</li>
                    <li>Pago de nómina de programadores ($1,800)</li>
                    <li>Servicio facturado contado y crédito ($2,000)</li>
                </ul>
            </label>
        </div>

        <div class="acciones-form">
            <button type="submit" style="font-size:1rem; padding:.7rem 1.5rem;">
                <?= $yaHay ? 'Reemplazar datos y cargar caso' : 'Cargar caso de prueba seleccionado' ?>
            </button>
            <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/index.php">Volver al inicio</a>
        </div>
    </form>
</section>

<?php
terminarPagina();
