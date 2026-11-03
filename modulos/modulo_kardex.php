<?php
/**
 * MÓDULO 3 - VALUACIÓN Y CONTROL DE INVENTARIOS · KARDEX (NUEVO)
 *
 * Método Promedio Ponderado
 *
 * 1. Estructura de datos del producto: Código, Descripción, Existencia Actual, Costo Unitario Promedio, Valor Total ($).
 * 2. Registro de Compras (Entrada) con cálculo de nuevo costo promedio y generación automática de partida:
 *    Carga a Inventario/Mercancía y Crédito Fiscal IVA, con abono a Bancos o Proveedores.
 * 3. Registro de Ventas (Salida) aplicando el costo promedio vigente con doble asiento:
 *    - Partida de Ingreso: Cobro al cliente con abono a Ingresos por Ventas y Débito Fiscal IVA.
 *    - Partida de Costo: Cargo a Costo de Ventas y abono a Inventario.
 * 4. Tarjeta de Control de Inventarios (Kardex).
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';

$accion = (string) ($_GET['accion'] ?? 'listar');
$productoIdFiltro = isset($_GET['producto_id']) ? (int) $_GET['producto_id'] : null;

$errores = [];
$exito   = '';

/* ---------------------------------------------------------------------
 *  ALTA DE NUEVO PRODUCTO
 * ------------------------------------------------------------------- */
if ($accion === 'nuevo_producto' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo      = trim((string) ($_POST['codigo'] ?? ''));
    $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
    $existencia  = (float) str_replace(',', '.', (string) ($_POST['existencia'] ?? '0'));
    $costo       = (float) str_replace(',', '.', (string) ($_POST['costo_promedio'] ?? '0'));

    if ($codigo === '') {
        $errores[] = 'El código del producto es obligatorio.';
    } else {
        $existe = (int) $pdo->query('SELECT COUNT(*) FROM productos WHERE codigo = ' . $pdo->quote($codigo))->fetchColumn() > 0;
        if ($existe) {
            $errores[] = 'Ya existe un producto con el código "' . $codigo . '".';
        }
    }

    if ($descripcion === '') {
        $errores[] = 'La descripción del producto es obligatoria.';
    }

    if ($existencia < 0) {
        $errores[] = 'La existencia inicial no puede ser negativa.';
    }
    if ($costo < 0) {
        $errores[] = 'El costo inicial no puede ser negativo.';
    }

    if ($errores === []) {
        try {
            $nuevoId = crearProducto($pdo, $codigo, $descripcion, $existencia, $costo);
            $exito   = 'Producto ' . $codigo . ' ("' . $descripcion . '") registrado exitosamente.';
            $accion  = 'listar';
        } catch (Throwable $e) {
            $errores[] = 'No se pudo registrar el producto: ' . $e->getMessage();
        }
    }
}

/* ---------------------------------------------------------------------
 *  REGISTRO DE COMPRA (ENTRADA AL KARDEX)
 * ------------------------------------------------------------------- */
if ($accion === 'compra' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $prodId   = (int) ($_POST['producto_id'] ?? 0);
    $fecha    = trim((string) ($_POST['fecha'] ?? ''));
    $cantidad = (float) str_replace(',', '.', (string) ($_POST['cantidad'] ?? '0'));
    $precio   = (float) str_replace(',', '.', (string) ($_POST['precio_unitario'] ?? '0'));
    $cuenta   = trim((string) ($_POST['cuenta_pago'] ?? '1.1.2'));
    $detalle  = trim((string) ($_POST['detalle'] ?? ''));

    if ($prodId <= 0) {
        $errores[] = 'Debe seleccionar un producto.';
    }
    if ($cantidad <= 0) {
        $errores[] = 'La cantidad comprada debe ser mayor a cero.';
    }
    if ($precio <= 0) {
        $errores[] = 'El precio unitario de compra debe ser mayor a cero.';
    }

    if ($errores === []) {
        $res = registrarCompraKardex($pdo, $prodId, $fecha, $cantidad, $precio, $cuenta, $detalle);
        if ($res['ok']) {
            $exito = 'Compra registrada en Kardex. Se calculó el nuevo costo promedio ('
                   . formatearDinero($res['nuevo_costo_prom']) . ') y se generó automáticamente el Asiento #'
                   . $res['asiento_id'] . ' en el Libro Diario (Inventario + IVA Crédito Fiscal).';
            $productoIdFiltro = $prodId;
            $accion = 'tarjeta';
        } else {
            $errores[] = $res['error'] ?? 'Error desconocido al registrar la compra.';
        }
    }
}

/* ---------------------------------------------------------------------
 *  REGISTRO DE VENTA (SALIDA DEL KARDEX)
 * ------------------------------------------------------------------- */
if ($accion === 'venta' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $prodId   = (int) ($_POST['producto_id'] ?? 0);
    $fecha    = trim((string) ($_POST['fecha'] ?? ''));
    $cantidad = (float) str_replace(',', '.', (string) ($_POST['cantidad'] ?? '0'));
    $precio   = (float) str_replace(',', '.', (string) ($_POST['precio_unitario'] ?? '0'));
    $cuenta   = trim((string) ($_POST['cuenta_cobro'] ?? '1.1.2'));
    $detalle  = trim((string) ($_POST['detalle'] ?? ''));

    if ($prodId <= 0) {
        $errores[] = 'Debe seleccionar un producto.';
    }
    if ($cantidad <= 0) {
        $errores[] = 'La cantidad a vender debe ser mayor a cero.';
    }
    if ($precio <= 0) {
        $errores[] = 'El precio unitario de venta debe ser mayor a cero.';
    }

    if ($errores === []) {
        $res = registrarVentaKardex($pdo, $prodId, $fecha, $cantidad, $precio, $cuenta, $detalle);
        if ($res['ok']) {
            $exito = 'Venta registrada en Kardex. Se aplicó el Costo Promedio ('
                   . formatearDinero($res['costo_promedio']) . ') y se generó automáticamente el doble asiento contable: '
                   . 'Asiento #' . $res['asiento_ingreso_id'] . ' (Ingreso + IVA Débito Fiscal) y '
                   . 'Asiento #' . $res['asiento_costo_id'] . ' (Costo de Ventas vs Inventario).';
            $productoIdFiltro = $prodId;
            $accion = 'tarjeta';
        } else {
            $errores[] = $res['error'] ?? 'Error desconocido al registrar la venta.';
        }
    }
}

$productos = obtenerProductos($pdo);

$tituloModulo = match ($accion) {
    'compra'         => 'Módulo 3 · Registrar Compra (Entrada Kardex)',
    'venta'          => 'Módulo 3 · Registrar Venta (Salida Kardex)',
    'tarjeta'        => 'Módulo 3 · Tarjeta de Control de Inventarios (Kardex)',
    'nuevo_producto' => 'Módulo 3 · Registrar Nuevo Producto',
    default          => 'Módulo 3 · Valuación y Control de Inventarios',
};

iniciarPagina($tituloModulo, 'modulos/modulo_kardex.php', $accion);

?>
<?php if ($exito !== ''): ?>
    <?= mensajeOk($exito) ?>
<?php endif; ?>

<?= mensajeErrores($errores) ?>

<!-- Barra de navegacion secundaria del Modulo Kardex -->
<div class="tabs-modulo" style="margin-bottom:1.25rem;">
    <a href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php"
       class="tab-link<?= ($accion === 'listar') ? ' activo' : '' ?>">
        <?= iconoSvg('grid') ?>
        <span>Catálogo y Existencias</span>
    </a>
    <a href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=compra"
       class="tab-link<?= ($accion === 'compra') ? ' activo' : '' ?>">
        <span style="font-weight:700; color:var(--tc-ok-text); font-size:1.1rem; line-height:1;">+</span>
        <span>Registrar Compra (Entrada)</span>
    </a>
    <a href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=venta"
       class="tab-link<?= ($accion === 'venta') ? ' activo' : '' ?>">
        <span style="font-weight:700; color:var(--tc-err-text); font-size:1.1rem; line-height:1;">&minus;</span>
        <span>Registrar Venta (Salida)</span>
    </a>
    <a href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=tarjeta<?= $productoIdFiltro ? '&producto_id=' . $productoIdFiltro : '' ?>"
       class="tab-link<?= ($accion === 'tarjeta') ? ' activo' : '' ?>">
        <?= iconoSvg('file') ?>
        <span>Tarjeta Kardex Detallada</span>
    </a>
</div>

<?php if ($accion === 'nuevo_producto'): ?>
    <!-- ===================================================================
         FORMULARIO: NUEVO PRODUCTO
         =================================================================== -->
    <section class="tarjeta">
        <div class="encabezado-seccion">
            <h3>Registrar nuevo producto en catálogo</h3>
            <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php">Volver al catálogo</a>
        </div>

        <form method="post" action="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=nuevo_producto">
            <p>
                <label for="codigo">Código del producto (ej. PROD-01 o LAPTOP-01)</label>
                <input type="text" id="codigo" name="codigo" placeholder="PROD-01"
                       value="<?= h($_POST['codigo'] ?? '') ?>" required size="20">
            </p>

            <p>
                <label for="descripcion">Descripción del producto</label>
                <input type="text" id="descripcion" name="descripcion" placeholder="Laptop Dell Latitude 16GB RAM"
                       value="<?= h($_POST['descripcion'] ?? '') ?>" required style="width:100%; max-width:500px;">
            </p>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
                <p>
                    <label for="existencia">Existencia inicial (unidades)</label>
                    <input type="number" id="existencia" name="existencia" step="0.01" min="0"
                           value="<?= h($_POST['existencia'] ?? '0') ?>">
                </p>

                <p>
                    <label for="costo_promedio">Costo unitario inicial ($)</label>
                    <input type="number" id="costo_promedio" name="costo_promedio" step="0.0001" min="0"
                           value="<?= h($_POST['costo_promedio'] ?? '0.00') ?>">
                </p>
            </div>

            <div class="acciones-form">
                <button type="submit">Guardar producto</button>
                <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php">Cancelar</a>
            </div>
        </form>
    </section>

<?php elseif ($accion === 'compra'): ?>
    <!-- ===================================================================
         FORMULARIO: REGISTRAR COMPRA (ENTRADA AL KARDEX)
         =================================================================== -->
    <section class="tarjeta">
        <div class="encabezado-seccion">
            <div>
                <h3>2. Registro de Compras (Entrada de Mercancía)</h3>
                <p class="ayuda" style="margin-bottom:0">
                    Aplica el <strong>Método Promedio Ponderado</strong>: calcula el nuevo costo promedio y genera de forma automática la partida en el Diario cargando a Inventario y Crédito Fiscal IVA.
                </p>
            </div>
            <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php">Ver catálogo</a>
        </div>

        <?php if ($productos === []): ?>
            <div class="estado-vacio">
                <strong>No hay productos registrados</strong>
                Debe registrar al menos un producto antes de poder ingresar compras.
                <div style="margin-top:1rem;">
                    <a class="btn-a" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=nuevo_producto">Registrar producto</a>
                </div>
            </div>
        <?php else: ?>
            <form method="post" id="formCompra" action="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=compra">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:1rem;">
                    <p>
                        <label for="producto_id">Producto a comprar</label>
                        <select id="producto_id" name="producto_id" required>
                            <option value="">(Seleccione un producto)</option>
                            <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                        data-existencia="<?= $p['existencia'] ?>"
                                        data-costo="<?= $p['costo_promedio'] ?>"
                                        data-valor="<?= $p['valor_total'] ?>"
                                    <?= ($productoIdFiltro === (int) $p['id']) ? 'selected' : '' ?>>
                                    <?= h($p['codigo'] . ' - ' . $p['descripcion'] . ' (Stock: ' . $p['existencia'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>

                    <p>
                        <label for="fecha">Fecha (dd/mm/aaaa)</label>
                        <input type="text" id="fecha" name="fecha" value="<?= h(fechaHoy()) ?>" size="12" required>
                    </p>
                </div>

                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
                    <p>
                        <label for="cantidad">Cantidad comprada (unidades)</label>
                        <input type="number" id="cantidad" name="cantidad" step="0.01" min="0.01" required
                               placeholder="Ej. 10">
                    </p>

                    <p>
                        <label for="precio_unitario">Precio unitario de compra ($ sin IVA)</label>
                        <input type="number" id="precio_unitario" name="precio_unitario" step="0.01" min="0.01" required
                               placeholder="Ej. 500.00">
                    </p>

                    <p>
                        <label for="cuenta_pago">Método de pago / Cuenta de abono</label>
                        <select id="cuenta_pago" name="cuenta_pago" required>
                            <option value="1.1.2">1.1.2 Bancos (Transferencia / Cheque)</option>
                            <option value="1.1.1">1.1.1 Caja (Efectivo)</option>
                            <option value="2.1.1">2.1.1 Proveedores (Compra a crédito)</option>
                        </select>
                    </p>
                </div>

                <p>
                    <label for="detalle">Detalle / Referencia (ej. Factura #402)</label>
                    <input type="text" id="detalle" name="detalle" placeholder="Factura de proveedor #402" style="max-width:500px;">
                </p>

                <!-- Panel de simulación en vivo del Promedio Ponderado -->
                <div class="tarjeta-simulacion" id="panelSimulacionCompra" style="margin: 1.25rem 0; padding:1.2rem; border-radius:var(--tc-r-md); background-color:var(--tc-surface-alt); border:1px solid var(--tc-border);">
                    <h4 style="margin-bottom:.75rem; font-size:.95rem; color:var(--tc-accent);">
                        Simulación de Valuación (Promedio Ponderado) y Asiento Automático
                    </h4>
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:.8rem;">
                        <div>
                            <small style="color:var(--tc-text-muted); display:block;">Stock anterior:</small>
                            <strong id="simStockAnt">0 un.</strong>
                        </div>
                        <div>
                            <small style="color:var(--tc-text-muted); display:block;">Costo promedio anterior:</small>
                            <strong id="simCostoAnt">$0.00</strong>
                        </div>
                        <div>
                            <small style="color:var(--tc-text-muted); display:block;">Subtotal Compra:</small>
                            <strong id="simSubtotalCompra" style="color:var(--tc-accent);">$0.00</strong>
                        </div>
                        <div>
                            <small style="color:var(--tc-text-muted); display:block;">Crédito Fiscal IVA (13%):</small>
                            <strong id="simIvaCredito">$0.00</strong>
                        </div>
                        <div>
                            <small style="color:var(--tc-text-muted); display:block;">Total a pagar:</small>
                            <strong id="simTotalPagar">$0.00</strong>
                        </div>
                        <div style="background-color:var(--tc-ok-bg); padding:.5rem .8rem; border-radius:8px; border:1px solid var(--tc-ok-border);">
                            <small style="color:var(--tc-ok-text); display:block; font-weight:600;">NUEVO COSTO PROMEDIO:</small>
                            <strong id="simNuevoCostoProm" style="font-size:1.15rem; color:var(--tc-ok-text);">$0.00</strong>
                        </div>
                    </div>

                    <div style="margin-top:1rem; padding-top:.75rem; border-top:1px dashed var(--tc-border);">
                        <small style="font-weight:700; color:var(--tc-text-muted);">Asiento contable automático generado:</small>
                        <div style="font-size:.85rem; font-family:monospace; margin-top:.4rem; line-height:1.6;">
                            <div>&bull; CARGO: 1.1.4 Inventario de productos &rarr; <span id="asientoInv">$0.00</span></div>
                            <div>&bull; CARGO: 1.1.5 IVA Crédito Fiscal (13%) &rarr; <span id="asientoIva">$0.00</span></div>
                            <div>&bull; ABONO: Cuenta de pago seleccionada &rarr; <span id="asientoPago">$0.00</span></div>
                        </div>
                    </div>
                </div>

                <div class="acciones-form">
                    <button type="submit">Registrar Compra y Generar Asiento</button>
                    <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php">Cancelar</a>
                </div>
            </form>

            <script>
            function recalcularCompra() {
                const sel = document.getElementById('producto_id');
                const opt = sel.options[sel.selectedIndex];
                const stockAnt = parseFloat(opt ? opt.getAttribute('data-existencia') : 0) || 0;
                const costoAnt  = parseFloat(opt ? opt.getAttribute('data-costo') : 0) || 0;
                const valorAnt  = parseFloat(opt ? opt.getAttribute('data-valor') : 0) || (stockAnt * costoAnt);

                const cant = parseFloat(document.getElementById('cantidad').value) || 0;
                const prec = parseFloat(document.getElementById('precio_unitario').value) || 0;

                const costoTotalCompra = Math.round(cant * prec * 100) / 100;
                const ivaCredito       = Math.round(costoTotalCompra * 0.13 * 100) / 100;
                const totalPagar       = Math.round((costoTotalCompra + ivaCredito) * 100) / 100;

                const nuevoStock = stockAnt + cant;
                const nuevoValor = valorAnt + costoTotalCompra;
                const nuevoCostoProm = nuevoStock > 0 ? (nuevoValor / nuevoStock) : 0;

                document.getElementById('simStockAnt').textContent = stockAnt.toFixed(2) + ' un.';
                document.getElementById('simCostoAnt').textContent = '$' + costoAnt.toFixed(4);
                document.getElementById('simSubtotalCompra').textContent = '$' + costoTotalCompra.toFixed(2);
                document.getElementById('simIvaCredito').textContent = '$' + ivaCredito.toFixed(2);
                document.getElementById('simTotalPagar').textContent = '$' + totalPagar.toFixed(2);
                document.getElementById('simNuevoCostoProm').textContent = '$' + nuevoCostoProm.toFixed(4);

                document.getElementById('asientoInv').textContent = '$' + costoTotalCompra.toFixed(2);
                document.getElementById('asientoIva').textContent = '$' + ivaCredito.toFixed(2);
                document.getElementById('asientoPago').textContent = '$' + totalPagar.toFixed(2);
            }

            document.getElementById('formCompra').addEventListener('input', recalcularCompra);
            document.getElementById('producto_id').addEventListener('change', recalcularCompra);
            recalcularCompra();
            </script>
        <?php endif; ?>
    </section>

<?php elseif ($accion === 'venta'): ?>
    <!-- ===================================================================
         FORMULARIO: REGISTRAR VENTA (SALIDA DEL KARDEX)
         =================================================================== -->
    <section class="tarjeta">
        <div class="encabezado-seccion">
            <div>
                <h3>3. Registro de Ventas (Salida de Mercancía)</h3>
                <p class="ayuda" style="margin-bottom:0">
                    Aplica automáticamente el <strong>Costo Promedio Ponderado vigente</strong> para el Costo de Ventas y genera el <strong>doble asiento contable</strong> (Partida de Ingreso + Partida de Costo).
                </p>
            </div>
            <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php">Ver catálogo</a>
        </div>

        <?php if ($productos === []): ?>
            <div class="estado-vacio">
                <strong>No hay productos con existencia</strong>
                Registre o compre productos antes de realizar ventas.
            </div>
        <?php else: ?>
            <form method="post" id="formVenta" action="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=venta">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:1rem;">
                    <p>
                        <label for="producto_id_v">Producto a vender</label>
                        <select id="producto_id_v" name="producto_id" required>
                            <option value="">(Seleccione un producto)</option>
                            <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                        data-existencia="<?= $p['existencia'] ?>"
                                        data-costo="<?= $p['costo_promedio'] ?>"
                                        data-valor="<?= $p['valor_total'] ?>"
                                    <?= ($productoIdFiltro === (int) $p['id']) ? 'selected' : '' ?>>
                                    <?= h($p['codigo'] . ' - ' . $p['descripcion'] . ' (Disponible: ' . $p['existencia'] . ' un. | Costo: $' . number_format((float)$p['costo_promedio'], 4) . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>

                    <p>
                        <label for="fecha_v">Fecha (dd/mm/aaaa)</label>
                        <input type="text" id="fecha_v" name="fecha" value="<?= h(fechaHoy()) ?>" size="12" required>
                    </p>
                </div>

                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
                    <p>
                        <label for="cantidad_v">Cantidad a vender (unidades)</label>
                        <input type="number" id="cantidad_v" name="cantidad" step="0.01" min="0.01" required
                               placeholder="Ej. 4">
                        <small id="avisoStock" style="display:block; margin-top:.25rem; font-size:.8rem;"></small>
                    </p>

                    <p>
                        <label for="precio_unitario_v">Precio de venta unitario ($ sin IVA)</label>
                        <input type="number" id="precio_unitario_v" name="precio_unitario" step="0.01" min="0.01" required
                               placeholder="Ej. 800.00">
                    </p>

                    <p>
                        <label for="cuenta_cobro">Forma de cobro / Cuenta de cargo</label>
                        <select id="cuenta_cobro" name="cuenta_cobro" required>
                            <option value="1.1.2">1.1.2 Bancos (Transferencia / Cheque)</option>
                            <option value="1.1.1">1.1.1 Caja (Efectivo recibido)</option>
                            <option value="1.1.3">1.1.3 Clientes (Venta a crédito)</option>
                        </select>
                    </p>
                </div>

                <p>
                    <label for="detalle_v">Detalle / Referencia (ej. Factura #501 a Cliente)</label>
                    <input type="text" id="detalle_v" name="detalle" placeholder="Factura de venta #501" style="max-width:500px;">
                </p>

                <!-- Panel de simulación en vivo de la Venta y Doble Asiento -->
                <div class="tarjeta-simulacion" id="panelSimulacionVenta" style="margin: 1.25rem 0; padding:1.2rem; border-radius:var(--tc-r-md); background-color:var(--tc-surface-alt); border:1px solid var(--tc-border);">
                    <h4 style="margin-bottom:.75rem; font-size:.95rem; color:var(--tc-accent);">
                        Simulación de Salida (Costo Promedio Vigente) y Doble Asiento Contable Automático
                    </h4>
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:.8rem;">
                        <div>
                            <small style="color:var(--tc-text-muted); display:block;">Stock disponible:</small>
                            <strong id="vStockDisp">0 un.</strong>
                        </div>
                        <div>
                            <small style="color:var(--tc-text-muted); display:block;">Costo promedio aplicado:</small>
                            <strong id="vCostoProm">$0.00</strong>
                        </div>
                        <div>
                            <small style="color:var(--tc-text-muted); display:block;">Subtotal Venta:</small>
                            <strong id="vSubtotalVenta" style="color:var(--tc-accent);">$0.00</strong>
                        </div>
                        <div>
                            <small style="color:var(--tc-text-muted); display:block;">Débito Fiscal IVA (13%):</small>
                            <strong id="vIvaDebito">$0.00</strong>
                        </div>
                        <div>
                            <small style="color:var(--tc-text-muted); display:block;">Total a Cobrar:</small>
                            <strong id="vTotalCobrar">$0.00</strong>
                        </div>
                        <div style="background-color:var(--tc-warn-bg); padding:.5rem .8rem; border-radius:8px; border:1px solid var(--tc-warn-border);">
                            <small style="color:var(--tc-warn-text); display:block; font-weight:600;">COSTO DE VENTAS TOTAL:</small>
                            <strong id="vCostoVentaTotal" style="font-size:1.15rem; color:var(--tc-warn-text);">$0.00</strong>
                        </div>
                    </div>

                    <div style="margin-top:1rem; padding-top:.75rem; border-top:1px dashed var(--tc-border); display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                        <div>
                            <small style="font-weight:700; color:var(--tc-ok-text);">1. Partida de Ingreso (Venta + IVA):</small>
                            <div style="font-size:.85rem; font-family:monospace; margin-top:.4rem; line-height:1.6;">
                                <div>&bull; CARGO: Cuenta de cobro &rarr; <span id="asientoCobro">$0.00</span></div>
                                <div>&bull; ABONO: 4.1.3 Ingresos por ventas &rarr; <span id="asientoVenta">$0.00</span></div>
                                <div>&bull; ABONO: 2.1.4 IVA Débito Fiscal (13%) &rarr; <span id="asientoIvaDeb">$0.00</span></div>
                            </div>
                        </div>
                        <div>
                            <small style="font-weight:700; color:var(--tc-warn-text);">2. Partida de Costo (Salida de Inventario):</small>
                            <div style="font-size:.85rem; font-family:monospace; margin-top:.4rem; line-height:1.6;">
                                <div>&bull; CARGO: 5.1.0 Costo de ventas &rarr; <span id="asientoCosto">$0.00</span></div>
                                <div>&bull; ABONO: 1.1.4 Inventario de productos &rarr; <span id="asientoInvSalida">$0.00</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="acciones-form">
                    <button type="submit" id="btnSubmitVenta">Registrar Venta y Generar Doble Asiento</button>
                    <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php">Cancelar</a>
                </div>
            </form>

            <script>
            function recalcularVenta() {
                const sel = document.getElementById('producto_id_v');
                const opt = sel.options[sel.selectedIndex];
                const stockDisp = parseFloat(opt ? opt.getAttribute('data-existencia') : 0) || 0;
                const costoProm = parseFloat(opt ? opt.getAttribute('data-costo') : 0) || 0;

                const cant = parseFloat(document.getElementById('cantidad_v').value) || 0;
                const prec = parseFloat(document.getElementById('precio_unitario_v').value) || 0;

                const aviso = document.getElementById('avisoStock');
                const btn   = document.getElementById('btnSubmitVenta');

                if (cant > stockDisp && cant > 0) {
                    aviso.textContent = '[!] Excede stock disponible (' + stockDisp.toFixed(2) + ' un.)';
                    aviso.style.color = 'var(--tc-err-text)';
                    btn.disabled = true;
                } else {
                    aviso.textContent = 'Disponible: ' + stockDisp.toFixed(2) + ' unidades.';
                    aviso.style.color = 'var(--tc-text-muted)';
                    btn.disabled = false;
                }

                const subtotalVenta = Math.round(cant * prec * 100) / 100;
                const ivaDebito     = Math.round(subtotalVenta * 0.13 * 100) / 100;
                const totalCobrar   = Math.round((subtotalVenta + ivaDebito) * 100) / 100;

                const costoVentaTotal = Math.round(cant * costoProm * 100) / 100;

                document.getElementById('vStockDisp').textContent = stockDisp.toFixed(2) + ' un.';
                document.getElementById('vCostoProm').textContent = '$' + costoProm.toFixed(4);
                document.getElementById('vSubtotalVenta').textContent = '$' + subtotalVenta.toFixed(2);
                document.getElementById('vIvaDebito').textContent = '$' + ivaDebito.toFixed(2);
                document.getElementById('vTotalCobrar').textContent = '$' + totalCobrar.toFixed(2);
                document.getElementById('vCostoVentaTotal').textContent = '$' + costoVentaTotal.toFixed(2);

                document.getElementById('asientoCobro').textContent = '$' + totalCobrar.toFixed(2);
                document.getElementById('asientoVenta').textContent = '$' + subtotalVenta.toFixed(2);
                document.getElementById('asientoIvaDeb').textContent = '$' + ivaDebito.toFixed(2);

                document.getElementById('asientoCosto').textContent = '$' + costoVentaTotal.toFixed(2);
                document.getElementById('asientoInvSalida').textContent = '$' + costoVentaTotal.toFixed(2);
            }

            document.getElementById('formVenta').addEventListener('input', recalcularVenta);
            document.getElementById('producto_id_v').addEventListener('change', recalcularVenta);
            recalcularVenta();
            </script>
        <?php endif; ?>
    </section>

<?php elseif ($accion === 'tarjeta'): ?>
    <!-- ===================================================================
         VISTA: TARJETA DE CONTROL DE INVENTARIOS (KARDEX)
         =================================================================== -->
    <?php
    $prodActual = $productoIdFiltro ? obtenerProductoPorId($pdo, $productoIdFiltro) : ($productos[0] ?? null);
    $movimientos = $prodActual ? obtenerMovimientosKardex($pdo, (int) $prodActual['id']) : [];
    ?>

    <section class="tarjeta">
        <div class="encabezado-seccion">
            <div>
                <h3>Tarjeta de Control de Inventario &middot; Kardex</h3>
                <p class="ayuda" style="margin-bottom:0">
                    Método de Valuación: <strong>Promedio Ponderado</strong> &middot; Moneda: USD ($)
                </p>
            </div>
            <div class="acciones-header">
                <a class="btn-a" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=compra&producto_id=<?= $prodActual ? $prodActual['id'] : '' ?>">+ Registrar compra</a>
                <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=venta&producto_id=<?= $prodActual ? $prodActual['id'] : '' ?>">&minus; Registrar venta</a>
            </div>
        </div>

        <?php if ($productos === []): ?>
            <div class="estado-vacio">
                <strong>No hay productos en el inventario</strong>
                Comience registrando un producto.
            </div>
        <?php else: ?>
            <!-- Selector de producto -->
            <form method="get" action="<?= RUTA_BASE ?>/modulos/modulo_kardex.php" style="margin-bottom:1.25rem;">
                <input type="hidden" name="accion" value="tarjeta">
                <label for="producto_id_sel" style="font-weight:600;">Seleccionar producto:</label>
                <div style="display:flex; gap:.6rem; align-items:center; flex-wrap:wrap; margin-top:.3rem;">
                    <select id="producto_id_sel" name="producto_id" onchange="this.form.submit()" style="max-width:400px;">
                        <?php foreach ($productos as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($prodActual && $prodActual['id'] == $p['id']) ? 'selected' : '' ?>>
                                <?= h($p['codigo'] . ' - ' . $p['descripcion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button type="submit">Ver tarjeta</button></noscript>
                </div>
            </form>

            <?php if ($prodActual): ?>
                <!-- Ficha del Producto -->
                <div class="tarjetas-stats" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:1.25rem;">
                    <div class="stat">
                        <span class="stat-etiqueta">Código & Descripción</span>
                        <strong style="font-size:1.1rem; display:block; margin-top:.4rem;"><?= h($prodActual['codigo']) ?></strong>
                        <small style="color:var(--tc-text-muted);"><?= h($prodActual['descripcion']) ?></small>
                    </div>
                    <div class="stat">
                        <span class="stat-etiqueta">Existencia Actual</span>
                        <span class="stat-valor" style="font-size:2rem;"><?= number_format((float)$prodActual['existencia'], 2) ?></span>
                        <small style="color:var(--tc-text-muted);">Unidades físicas en stock</small>
                    </div>
                    <div class="stat">
                        <span class="stat-etiqueta">Costo Unitario Promedio</span>
                        <span class="stat-valor" style="font-size:2rem; color:var(--tc-accent);"><?= formatearDinero($prodActual['costo_promedio']) ?></span>
                        <small style="color:var(--tc-text-muted);">Por unidad ponderada</small>
                    </div>
                    <div class="stat">
                        <span class="stat-etiqueta">Valor Total del Inventario</span>
                        <span class="stat-valor" style="font-size:2rem; color:var(--tc-ok-text);"><?= formatearDinero($prodActual['valor_total']) ?></span>
                        <small style="color:var(--tc-text-muted);">Existencia &times; Costo Promedio</small>
                    </div>
                </div>

                <!-- Tabla de Kardex con 3 bloques -->
                <div class="tabla-envoltura">
                    <table class="tabla-kardex">
                        <thead>
                            <tr>
                                <th rowspan="2">Fecha</th>
                                <th rowspan="2">Detalle / Movimiento</th>
                                <th rowspan="2">Asiento</th>
                                <th colspan="3" class="th-grupo" style="background-color:rgba(37,99,235,.08);">ENTRADAS (COMPRAS)</th>
                                <th colspan="3" class="th-grupo" style="background-color:rgba(239,68,68,.08);">SALIDAS (VENTAS)</th>
                                <th colspan="3" class="th-grupo" style="background-color:rgba(16,185,129,.08);">SALDOS EXISTENCIA</th>
                            </tr>
                            <tr>
                                <th class="num">Cant.</th>
                                <th class="num">Costo U.</th>
                                <th class="num">Total ($)</th>
                                <th class="num">Cant.</th>
                                <th class="num">Costo U.</th>
                                <th class="num">Total ($)</th>
                                <th class="num">Cant.</th>
                                <th class="num">Costo Prom.</th>
                                <th class="num">Total ($)</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($movimientos === []): ?>
                            <tr>
                                <td colspan="12" style="text-align:center; padding:1.5rem; color:var(--tc-text-muted);">
                                    Este producto aún no registra movimientos en el Kardex.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($movimientos as $m): ?>
                                <tr>
                                    <td><?= h(fechaAMostrar($m['fecha'])) ?></td>
                                    <td>
                                        <span class="badge-tipo badge-<?= strtolower($m['tipo']) ?>"><?= h($m['tipo']) ?></span>
                                        <?= h($m['detalle']) ?>
                                    </td>
                                    <td>
                                        <?php if ($m['asiento_id']): ?>
                                            <a href="<?= RUTA_BASE ?>/modulos/modulo2_diario.php?accion=ver" title="Ver partida en Diario">
                                                #<?= $m['asiento_id'] ?>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($m['asiento_costo_id']): ?>
                                            / <a href="<?= RUTA_BASE ?>/modulos/modulo2_diario.php?accion=ver" title="Ver partida de costo">
                                                #<?= $m['asiento_costo_id'] ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <!-- Entradas -->
                                    <td class="num"><?= ($m['cant_entrada'] > 0) ? number_format((float)$m['cant_entrada'], 2) : '-' ?></td>
                                    <td class="num"><?= ($m['costo_entrada'] > 0) ? formatearDinero($m['costo_entrada']) : '-' ?></td>
                                    <td class="num"><?= ($m['total_entrada'] > 0) ? formatearDinero($m['total_entrada']) : '-' ?></td>
                                    <!-- Salidas -->
                                    <td class="num"><?= ($m['cant_salida'] > 0) ? number_format((float)$m['cant_salida'], 2) : '-' ?></td>
                                    <td class="num"><?= ($m['costo_salida'] > 0) ? formatearDinero($m['costo_salida']) : '-' ?></td>
                                    <td class="num"><?= ($m['total_salida'] > 0) ? formatearDinero($m['total_salida']) : '-' ?></td>
                                    <!-- Saldos -->
                                    <td class="num" style="font-weight:700;"><?= number_format((float)$m['cant_saldo'], 2) ?></td>
                                    <td class="num" style="font-weight:700; color:var(--tc-accent);"><?= formatearDinero($m['costo_promedio']) ?></td>
                                    <td class="num" style="font-weight:700; color:var(--tc-ok-text);"><?= formatearDinero($m['total_saldo']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

<?php else: ?>
    <!-- ===================================================================
         VISTA POR DEFECTO: LISTADO DE PRODUCTOS Y VALUACIÓN
         =================================================================== -->
    <?php
    $totalStock = 0.0;
    $totalInversion = 0.0;
    foreach ($productos as $p) {
        $totalStock += (float) $p['existencia'];
        $totalInversion += (float) $p['valor_total'];
    }
    ?>

    <section class="tarjetas-stats" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="stat">
            <span class="stat-valor"><?= count($productos) ?></span>
            <span class="stat-etiqueta">Productos registrados</span>
        </div>
        <div class="stat">
            <span class="stat-valor" style="color:var(--tc-accent);"><?= number_format($totalStock, 2) ?></span>
            <span class="stat-etiqueta">Unidades totales en stock</span>
        </div>
        <div class="stat">
            <span class="stat-valor" style="color:var(--tc-ok-text);"><?= formatearDinero($totalInversion) ?></span>
            <span class="stat-etiqueta">Valor total del inventario</span>
        </div>
    </section>

    <section class="tarjeta">
        <div class="encabezado-seccion">
            <div>
                <h3>Control de Mercancías · Método Promedio Ponderado</h3>
                <p class="ayuda" style="margin-bottom:0">
                    Estructura de datos del producto: Código, Descripción, Existencia Actual, Costo Unitario Promedio y Valor Total ($).
                </p>
            </div>
            <div class="acciones-header">
                <a class="btn-a" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=nuevo_producto">+ Nuevo producto</a>
                <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=compra">+ Registrar compra</a>
                <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=venta">&minus; Registrar venta</a>
            </div>
        </div>

        <?php if ($productos === []): ?>
            <div class="estado-vacio">
                <strong>No hay productos registrados en el inventario</strong>
                Comienza agregando un producto o carga el caso de prueba para ver el kardex en acción.
                <div style="margin-top:1rem; display:flex; gap:.6rem; justify-content:center;">
                    <a class="btn-a" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=nuevo_producto">Registrar producto</a>
                    <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/caso_prueba.php">Cargar caso de prueba</a>
                </div>
            </div>
        <?php else: ?>
            <div class="tabla-envoltura">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th class="num">Existencia Actual (Un.)</th>
                            <th class="num">Costo Unit. Promedio ($)</th>
                            <th class="num">Valor Total Inventario ($)</th>
                            <th style="text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($productos as $p): ?>
                        <tr>
                            <td><code><strong><?= h($p['codigo']) ?></strong></code></td>
                            <td><?= h($p['descripcion']) ?></td>
                            <td class="num" style="font-weight:600;"><?= number_format((float)$p['existencia'], 2) ?></td>
                            <td class="num" style="color:var(--tc-accent); font-weight:600;"><?= formatearDinero($p['costo_promedio']) ?></td>
                            <td class="num monto-destacado"><?= formatearDinero($p['valor_total']) ?></td>
                            <td style="text-align:center; white-space:nowrap;">
                                <a class="btn-a btn-sm" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=tarjeta&producto_id=<?= $p['id'] ?>" title="Ver historial Kardex">
                                    Ver Kardex
                                </a>
                                <a class="btn-a btn-sm btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=compra&producto_id=<?= $p['id'] ?>" title="Comprar producto">
                                    + Compra
                                </a>
                                <a class="btn-a btn-sm btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo_kardex.php?accion=venta&producto_id=<?= $p['id'] ?>" title="Vender producto">
                                    &minus; Venta
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">TOTAL GENERAL DE MERCANCÍAS</th>
                            <th class="num"><?= number_format($totalStock, 2) ?> un.</th>
                            <th></th>
                            <th class="num"><?= formatearDinero($totalInversion) ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <p class="pie-tabla">Total de productos en catálogo: <strong><?= count($productos) ?></strong></p>
        <?php endif; ?>
    </section>

<?php endif; ?>

<?php
terminarPagina();
