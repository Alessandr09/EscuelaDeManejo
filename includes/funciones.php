<?php
/**
 * Utilidades comunes: escape de salida, formato de dinero, fechas,
 * mensajes de estado y consultas de solo lectura al catalogo.
 */

declare(strict_types=1);

require_once __DIR__ . '/bd.php';

/** Escapa texto para imprimirlo en HTML. */
function h(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Formatea un monto: $1,234.56 */
function formatearDinero($monto): string
{
    return '$' . number_format((float) $monto, 2);
}

/** Recorta un texto sin depender de mbstring. */
function recortar(?string $texto, int $largo): string
{
    $texto = (string) $texto;

    if (function_exists('mb_substr')) {
        return mb_substr($texto, 0, $largo, 'UTF-8');
    }

    return substr($texto, 0, $largo);
}

/* ---------------------------------------------------------------------
 *  Fechas: la base guarda DATE (aaaa-mm-dd); la pantalla usa dd/mm/aaaa.
 * ------------------------------------------------------------------- */

function fechaHoy(): string
{
    return date('d/m/Y');
}

/** Convierte 'dd/mm/aaaa' a 'aaaa-mm-dd'. Devuelve null si no es valida. */
function fechaAMysql(string $fecha): ?string
{
    $fecha = trim($fecha);

    if ($fecha === '') {
        return date('Y-m-d');
    }

    $d = DateTime::createFromFormat('!d/m/Y', $fecha);

    if ($d === false || $d->format('d/m/Y') !== $fecha) {
        return null;
    }

    return $d->format('Y-m-d');
}

/** Convierte 'aaaa-mm-dd' a 'dd/mm/aaaa'. */
function fechaAMostrar(?string $fecha): string
{
    if ($fecha === null || $fecha === '') {
        return '';
    }

    $d = DateTime::createFromFormat('!Y-m-d', substr($fecha, 0, 10));

    return $d === false ? (string) $fecha : $d->format('d/m/Y');
}

/* ---------------------------------------------------------------------
 *  Mensajes de estado (mismos marcadores que la version de consola)
 * ------------------------------------------------------------------- */

function mensajeOk(string $texto): string
{
    return '<p class="msg ok">[OK] ' . h($texto) . '</p>';
}

function mensajeInfo(string $texto): string
{
    return '<p class="msg info">[i] ' . h($texto) . '</p>';
}

function mensajeAviso(string $texto): string
{
    return '<p class="msg warn">[!] ' . h($texto) . '</p>';
}

function mensajeError(string $texto): string
{
    return '<p class="msg error">[ERROR] ' . h($texto) . '</p>';
}

/** Une varios mensajes de error en un bloque. */
function mensajeErrores(array $errores): string
{
    if ($errores === []) {
        return '';
    }

    $html = '<div class="msg error"><strong>[ERROR] La operacion fue rechazada:</strong><ul>';

    foreach ($errores as $error) {
        $html .= '<li>' . h((string) $error) . '</li>';
    }

    return $html . '</ul></div>';
}

/* ---------------------------------------------------------------------
 *  Consultas de solo lectura
 * ------------------------------------------------------------------- */

/**
 * Catalogo completo, ordenado por codigo.
 *
 * @return array<int, array{codigo:string, nombre:string, naturaleza:string, saldo:string, rubro:?string}>
 */
function obtenerCatalogo(PDO $pdo): array
{
    return $pdo->query(
        'SELECT codigo, nombre, naturaleza, saldo, rubro FROM cuentas ORDER BY codigo'
    )->fetchAll();
}

/** Existe una cuenta con ese codigo? */
function existeCuenta(PDO $pdo, string $codigo): bool
{
    $sql = 'SELECT COUNT(*) FROM cuentas WHERE codigo = ?';
    $ins = $pdo->prepare($sql);
    $ins->execute([$codigo]);

    return (int) $ins->fetchColumn() > 0;
}

/* ---------------------------------------------------------------------
 *  Clasificacion contable de cuentas
 * ------------------------------------------------------------------- */

function rubrosDisponibles(): array
{
    return [
        'ACTIVO_CORRIENTE'    => 'Activo Corriente',
        'ACTIVO_NO_CORRIENTE' => 'Activo No Corriente',
        'PASIVO_CORRIENTE'    => 'Pasivo Corriente',
        'PASIVO_NO_CORRIENTE' => 'Pasivo No Corriente',
        'PATRIMONIO'          => 'Patrimonio / Capital',
        'INGRESOS'            => 'Ingresos Operativos (Ventas)',
        'COSTO_VENTAS'        => 'Costos de Ventas',
        'GASTOS_ADMIN'        => 'Gastos de Administración',
        'GASTOS_VENTA'        => 'Gastos de Venta',
    ];
}

function nombreRubro(?string $rubro): string
{
    $rubros = rubrosDisponibles();
    return $rubros[$rubro ?? ''] ?? ($rubro ?: 'Sin clasificación');
}

/** Deduce o retorna el rubro normalizado de una cuenta. */
function clasificarCuenta(string $codigo, string $nombre = '', ?string $rubroDb = null): string
{
    if ($rubroDb !== null && $rubroDb !== '' && isset(rubrosDisponibles()[$rubroDb])) {
        return $rubroDb;
    }

    $nombreNorm = strtolower($nombre);

    if (str_starts_with($codigo, '1.1')) {
        return 'ACTIVO_CORRIENTE';
    }
    if (str_starts_with($codigo, '1.2') || str_starts_with($codigo, '1.3')) {
        return 'ACTIVO_NO_CORRIENTE';
    }
    if (str_starts_with($codigo, '2.1')) {
        return 'PASIVO_CORRIENTE';
    }
    if (str_starts_with($codigo, '2.2') || str_starts_with($codigo, '2.3')) {
        return 'PASIVO_NO_CORRIENTE';
    }
    if (str_starts_with($codigo, '3.')) {
        return 'PATRIMONIO';
    }
    if (str_starts_with($codigo, '4.')) {
        return 'INGRESOS';
    }
    if (str_starts_with($codigo, '5.1.0') || str_contains($nombreNorm, 'costo de venta') || str_contains($nombreNorm, 'costo')) {
        return 'COSTO_VENTAS';
    }
    if (str_starts_with($codigo, '5.3') || str_contains($nombreNorm, 'venta') || str_contains($nombreNorm, 'comisi')) {
        return 'GASTOS_VENTA';
    }
    if (str_starts_with($codigo, '5.')) {
        return 'GASTOS_ADMIN';
    }

    return 'ACTIVO_CORRIENTE';
}

/* ---------------------------------------------------------------------
 *  MODULO 2: Balanza de Comprobación y Estados Financieros
 * ------------------------------------------------------------------- */

/**
 * 2.1. Balanza de Comprobación
 * Recorre todas las cuentas con movimientos en el Libro Mayor.
 * Para cada cuenta calcula Total Debe, Total Haber y Saldo Deudor o Acreedor.
 */
function calcularBalanzaComprobacion(PDO $pdo): array
{
    $sql = 'SELECT c.codigo,
                   c.nombre,
                   c.naturaleza,
                   c.saldo AS saldo_habitual,
                   c.rubro,
                   COALESCE(SUM(r.debe), 0)  AS cargos,
                   COALESCE(SUM(r.haber), 0) AS abonos
              FROM renglones r
              JOIN cuentas c ON c.codigo = r.cuenta
          GROUP BY c.codigo, c.nombre, c.naturaleza, c.saldo, c.rubro
            HAVING cargos > 0 OR abonos > 0
          ORDER BY c.codigo';

    $filas = $pdo->query($sql)->fetchAll();

    $cuentas = [];
    $totalDebe = 0.0;
    $totalHaber = 0.0;
    $totalSaldoDeudor = 0.0;
    $totalSaldoAcreedor = 0.0;

    foreach ($filas as $fila) {
        $cargos = round((float) $fila['cargos'], 2);
        $abonos = round((float) $fila['abonos'], 2);
        $rubro  = clasificarCuenta($fila['codigo'], $fila['nombre'], $fila['rubro']);

        // Saldo neto segun quien sea mayor
        $saldoDeudor = 0.0;
        $saldoAcreedor = 0.0;

        if ($cargos >= $abonos) {
            $saldoDeudor = round($cargos - $abonos, 2);
        } else {
            $saldoAcreedor = round($abonos - $cargos, 2);
        }

        $cuentas[] = [
            'codigo'         => $fila['codigo'],
            'nombre'         => $fila['nombre'],
            'naturaleza'     => $fila['naturaleza'],
            'rubro'          => $rubro,
            'rubro_texto'    => nombreRubro($rubro),
            'cargos'         => $cargos,
            'abonos'         => $abonos,
            'saldo_deudor'   => $saldoDeudor,
            'saldo_acreedor' => $saldoAcreedor,
        ];

        $totalDebe          = round($totalDebe + $cargos, 2);
        $totalHaber         = round($totalHaber + $abonos, 2);
        $totalSaldoDeudor   = round($totalSaldoDeudor + $saldoDeudor, 2);
        $totalSaldoAcreedor = round($totalSaldoAcreedor + $saldoAcreedor, 2);
    }

    $movimientosCuadrados = (abs($totalDebe - $totalHaber) < 0.005);
    $saldosCuadrados      = (abs($totalSaldoDeudor - $totalSaldoAcreedor) < 0.005);
    $cuadrada             = $movimientosCuadrados && $saldosCuadrados;

    return [
        'cuentas'               => $cuentas,
        'total_debe'            => $totalDebe,
        'total_haber'           => $totalHaber,
        'total_saldo_deudor'    => $totalSaldoDeudor,
        'total_saldo_acreedor'  => $totalSaldoAcreedor,
        'movimientos_cuadrados' => $movimientosCuadrados,
        'saldos_cuadrados'      => $saldosCuadrados,
        'esta_cuadrada'         => $cuadrada,
    ];
}

/**
 * 2.2. Estado de Resultados (Perdidas y Ganancias)
 * Filtra cuentas de Ingresos, Costos de Ventas y Gastos Operativos.
 * Utilidad Bruta = Ingresos por Ventas - Costo de Ventas
 * Utilidad Operativa = Utilidad Bruta - Gastos Operativos
 */
function calcularEstadoResultados(PDO $pdo): array
{
    $sql = 'SELECT c.codigo,
                   c.nombre,
                   c.naturaleza,
                   c.saldo AS saldo_habitual,
                   c.rubro,
                   COALESCE(SUM(r.debe), 0)  AS cargos,
                   COALESCE(SUM(r.haber), 0) AS abonos
              FROM renglones r
              JOIN cuentas c ON c.codigo = r.cuenta
          GROUP BY c.codigo, c.nombre, c.naturaleza, c.saldo, c.rubro
          ORDER BY c.codigo';

    $filas = $pdo->query($sql)->fetchAll();

    $ingresos = [];
    $costos   = [];
    $gastosAdmin = [];
    $gastosVenta = [];

    $totalIngresos = 0.0;
    $totalCostos   = 0.0;
    $totalGastosAdmin = 0.0;
    $totalGastosVenta = 0.0;

    foreach ($filas as $fila) {
        $cargos = round((float) $fila['cargos'], 2);
        $abonos = round((float) $fila['abonos'], 2);
        $rubro  = clasificarCuenta($fila['codigo'], $fila['nombre'], $fila['rubro']);

        // Ingresos: saldo acreedor (abonos - cargos)
        if ($rubro === 'INGRESOS') {
            $monto = round($abonos - $cargos, 2);
            if ($monto !== 0.0 || $cargos > 0 || $abonos > 0) {
                $ingresos[] = [
                    'codigo' => $fila['codigo'],
                    'nombre' => $fila['nombre'],
                    'monto'  => $monto,
                ];
                $totalIngresos = round($totalIngresos + $monto, 2);
            }
        }

        // Costo de Ventas: saldo deudor (cargos - abonos)
        if ($rubro === 'COSTO_VENTAS') {
            $monto = round($cargos - $abonos, 2);
            if ($monto !== 0.0 || $cargos > 0 || $abonos > 0) {
                $costos[] = [
                    'codigo' => $fila['codigo'],
                    'nombre' => $fila['nombre'],
                    'monto'  => $monto,
                ];
                $totalCostos = round($totalCostos + $monto, 2);
            }
        }

        // Gastos de Administracion: saldo deudor (cargos - abonos)
        if ($rubro === 'GASTOS_ADMIN') {
            $monto = round($cargos - $abonos, 2);
            if ($monto !== 0.0 || $cargos > 0 || $abonos > 0) {
                $gastosAdmin[] = [
                    'codigo' => $fila['codigo'],
                    'nombre' => $fila['nombre'],
                    'monto'  => $monto,
                ];
                $totalGastosAdmin = round($totalGastosAdmin + $monto, 2);
            }
        }

        // Gastos de Venta: saldo deudor (cargos - abonos)
        if ($rubro === 'GASTOS_VENTA') {
            $monto = round($cargos - $abonos, 2);
            if ($monto !== 0.0 || $cargos > 0 || $abonos > 0) {
                $gastosVenta[] = [
                    'codigo' => $fila['codigo'],
                    'nombre' => $fila['nombre'],
                    'monto'  => $monto,
                ];
                $totalGastosVenta = round($totalGastosVenta + $monto, 2);
            }
        }
    }

    $utilidadBruta          = round($totalIngresos - $totalCostos, 2);
    $totalGastosOperativos  = round($totalGastosAdmin + $totalGastosVenta, 2);
    $utilidadOperativa      = round($utilidadBruta - $totalGastosOperativos, 2);

    return [
        'ingresos'                => $ingresos,
        'total_ingresos'          => $totalIngresos,
        'costos'                  => $costos,
        'total_costos'            => $totalCostos,
        'utilidad_bruta'          => $utilidadBruta,
        'gastos_admin'            => $gastosAdmin,
        'total_gastos_admin'      => $totalGastosAdmin,
        'gastos_venta'            => $gastosVenta,
        'total_gastos_venta'      => $totalGastosVenta,
        'total_gastos_operativos' => $totalGastosOperativos,
        'utilidad_operativa'      => $utilidadOperativa,
        'es_utilidad'             => ($utilidadOperativa >= 0),
    ];
}

/**
 * 2.3. Balance General (Estado de Situacion Financiera)
 * Bloques: ACTIVO (Corriente y No Corriente), PASIVO (Corriente y No Corriente),
 * PATRIMONIO (Capital Social + Utilidad o Perdida del Ejercicio).
 * Valida: Total Activo = Total Pasivo + Total Patrimonio.
 */
function calcularBalanceGeneral(PDO $pdo, ?float $utilidadPeriodo = null): array
{
    if ($utilidadPeriodo === null) {
        $eeff = calcularEstadoResultados($pdo);
        $utilidadPeriodo = $eeff['utilidad_operativa'];
    }

    $sql = 'SELECT c.codigo,
                   c.nombre,
                   c.naturaleza,
                   c.saldo AS saldo_habitual,
                   c.rubro,
                   COALESCE(SUM(r.debe), 0)  AS cargos,
                   COALESCE(SUM(r.haber), 0) AS abonos
              FROM cuentas c
         LEFT JOIN renglones r ON r.cuenta = c.codigo
          GROUP BY c.codigo, c.nombre, c.naturaleza, c.saldo, c.rubro
          ORDER BY c.codigo';

    $filas = $pdo->query($sql)->fetchAll();

    $activoCorriente   = [];
    $activoNoCorriente = [];
    $pasivoCorriente   = [];
    $pasivoNoCorriente = [];
    $patrimonioCuentas = [];

    $totalActivoCorriente   = 0.0;
    $totalActivoNoCorriente = 0.0;
    $totalPasivoCorriente   = 0.0;
    $totalPasivoNoCorriente = 0.0;
    $totalPatrimonioBase    = 0.0;

    foreach ($filas as $fila) {
        $cargos = round((float) $fila['cargos'], 2);
        $abonos = round((float) $fila['abonos'], 2);
        $rubro  = clasificarCuenta($fila['codigo'], $fila['nombre'], $fila['rubro']);

        // Solo procesamos cuentas de Balance (Activo, Pasivo, Patrimonio)
        if ($rubro === 'ACTIVO_CORRIENTE') {
            $saldo = round($cargos - $abonos, 2);
            if ($saldo !== 0.0 || $cargos > 0 || $abonos > 0) {
                $activoCorriente[] = ['codigo' => $fila['codigo'], 'nombre' => $fila['nombre'], 'saldo' => $saldo];
                $totalActivoCorriente = round($totalActivoCorriente + $saldo, 2);
            }
        } elseif ($rubro === 'ACTIVO_NO_CORRIENTE') {
            $saldo = round($cargos - $abonos, 2);
            if ($saldo !== 0.0 || $cargos > 0 || $abonos > 0) {
                $activoNoCorriente[] = ['codigo' => $fila['codigo'], 'nombre' => $fila['nombre'], 'saldo' => $saldo];
                $totalActivoNoCorriente = round($totalActivoNoCorriente + $saldo, 2);
            }
        } elseif ($rubro === 'PASIVO_CORRIENTE') {
            $saldo = round($abonos - $cargos, 2);
            if ($saldo !== 0.0 || $cargos > 0 || $abonos > 0) {
                $pasivoCorriente[] = ['codigo' => $fila['codigo'], 'nombre' => $fila['nombre'], 'saldo' => $saldo];
                $totalPasivoCorriente = round($totalPasivoCorriente + $saldo, 2);
            }
        } elseif ($rubro === 'PASIVO_NO_CORRIENTE') {
            $saldo = round($abonos - $cargos, 2);
            if ($saldo !== 0.0 || $cargos > 0 || $abonos > 0) {
                $pasivoNoCorriente[] = ['codigo' => $fila['codigo'], 'nombre' => $fila['nombre'], 'saldo' => $saldo];
                $totalPasivoNoCorriente = round($totalPasivoNoCorriente + $saldo, 2);
            }
        } elseif ($rubro === 'PATRIMONIO') {
            $saldo = round($abonos - $cargos, 2);
            if ($saldo !== 0.0 || $cargos > 0 || $abonos > 0) {
                $patrimonioCuentas[] = ['codigo' => $fila['codigo'], 'nombre' => $fila['nombre'], 'saldo' => $saldo];
                $totalPatrimonioBase = round($totalPatrimonioBase + $saldo, 2);
            }
        }
    }

    $totalActivo          = round($totalActivoCorriente + $totalActivoNoCorriente, 2);
    $totalPasivo          = round($totalPasivoCorriente + $totalPasivoNoCorriente, 2);
    $totalPatrimonio      = round($totalPatrimonioBase + $utilidadPeriodo, 2);
    $totalPasivoPatrimonio= round($totalPasivo + $totalPatrimonio, 2);
    $diferencia           = round($totalActivo - $totalPasivoPatrimonio, 2);
    $estaCuadrado         = (abs($diferencia) < 0.005);

    return [
        'activo_corriente'           => $activoCorriente,
        'total_activo_corriente'     => $totalActivoCorriente,
        'activo_no_corriente'        => $activoNoCorriente,
        'total_activo_no_corriente'  => $totalActivoNoCorriente,
        'total_activo'               => $totalActivo,

        'pasivo_corriente'           => $pasivoCorriente,
        'total_pasivo_corriente'     => $totalPasivoCorriente,
        'pasivo_no_corriente'        => $pasivoNoCorriente,
        'total_pasivo_no_corriente'  => $totalPasivoNoCorriente,
        'total_pasivo'               => $totalPasivo,

        'patrimonio_cuentas'         => $patrimonioCuentas,
        'total_patrimonio_base'      => $totalPatrimonioBase,
        'utilidad_ejercicio'         => $utilidadPeriodo,
        'total_patrimonio'           => $totalPatrimonio,

        'total_pasivo_mas_patrimonio'=> $totalPasivoPatrimonio,
        'diferencia'                 => $diferencia,
        'esta_cuadrado'              => $estaCuadrado,
    ];
}

/* ---------------------------------------------------------------------
 *  MODULO 3: Valuacion y Control de Inventarios - Kardex (Promedio Ponderado)
 * ------------------------------------------------------------------- */

const TASA_IVA = 0.13; // IVA en El Salvador (13%)

/** Listado de todos los productos registrados. */
function obtenerProductos(PDO $pdo): array
{
    return $pdo->query(
        'SELECT id, codigo, descripcion, existencia, costo_promedio, valor_total, created_at
           FROM productos
       ORDER BY codigo'
    )->fetchAll();
}

/** Obtiene un producto por ID. */
function obtenerProductoPorId(PDO $pdo, int $id): ?array
{
    $ins = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
    $ins->execute([$id]);
    $prod = $ins->fetch();
    return $prod ?: null;
}

/** Registra un nuevo producto en el catálogo. */
function crearProducto(
    PDO $pdo,
    string $codigo,
    string $descripcion,
    float $existenciaInicial = 0.0,
    float $costoInicial = 0.0
): int {
    $codigo      = trim($codigo);
    $descripcion = trim($descripcion);
    $existencia  = max(0.0, round($existenciaInicial, 2));
    $costo       = max(0.0, round($costoInicial, 4));
    $valorTotal  = round($existencia * $costo, 2);

    $sql = 'INSERT INTO productos (codigo, descripcion, existencia, costo_promedio, valor_total)
            VALUES (?, ?, ?, ?, ?)';
    $ins = $pdo->prepare($sql);
    $ins->execute([$codigo, $descripcion, $existencia, $costo, $valorTotal]);

    $prodId = (int) $pdo->lastInsertId();

    if ($existencia > 0) {
        $sqlK = 'INSERT INTO kardex (producto_id, fecha, tipo, detalle, cant_entrada, costo_entrada, total_entrada,
                                     cant_saldo, costo_promedio, total_saldo)
                 VALUES (?, ?, "INICIAL", "Inventario inicial", ?, ?, ?, ?, ?, ?)';
        $pdo->prepare($sqlK)->execute([
            $prodId,
            date('Y-m-d'),
            $existencia,
            $costo,
            $valorTotal,
            $existencia,
            $costo,
            $valorTotal,
        ]);
    }

    return $prodId;
}

/**
 * 3.2. Registro de Compras (Entrada):
 * Nuevo Costo Promedio = (Valor Total Anterior + Costo Total Nueva Compra) / (Existencia Anterior + Unidades Compradas)
 * Genera asiento:
 *   DEBE:  1.1.4 Inventario de productos (Costo de Compra)
 *   DEBE:  1.1.5 IVA Crédito Fiscal     (13% sobre Costo)
 *   HABER: Efectivo/Bancos o Proveedores (Total)
 */
function registrarCompraKardex(
    PDO $pdo,
    int $productoId,
    string $fecha,
    float $cantidad,
    float $precioUnitario,
    string $cuentaPago = '1.1.2',
    string $detalle = ''
): array {
    $producto = obtenerProductoPorId($pdo, $productoId);
    if (!$producto) {
        return ['ok' => false, 'error' => 'El producto no existe.'];
    }

    if ($cantidad <= 0) {
        return ['ok' => false, 'error' => 'La cantidad debe ser mayor a cero.'];
    }
    if ($precioUnitario <= 0) {
        return ['ok' => false, 'error' => 'El precio unitario de compra debe ser mayor a cero.'];
    }

    $fechaSql = fechaAMysql($fecha);
    if ($fechaSql === null) {
        return ['ok' => false, 'error' => 'Fecha no válida.'];
    }

    $existenciaAnt = (float) $producto['existencia'];
    $valorAnt      = (float) $producto['valor_total'];

    $costoTotalCompra = round($cantidad * $precioUnitario, 2);
    $nuevaExistencia  = round($existenciaAnt + $cantidad, 2);
    $nuevoValorTotal  = round($valorAnt + $costoTotalCompra, 2);
    $nuevoCostoProm   = ($nuevaExistencia > 0) ? round($nuevoValorTotal / $nuevaExistencia, 4) : 0.0;

    $ivaCredito = round($costoTotalCompra * TASA_IVA, 2);
    $totalPagar = round($costoTotalCompra + $ivaCredito, 2);

    $concepto = 'Compra ' . $cantidad . ' un. ' . $producto['codigo'] . ' - ' . ($detalle ?: 'Factura de compra');

    $pdo->beginTransaction();
    try {
        // 1. Asiento en Diario
        $insAsiento = $pdo->prepare('INSERT INTO asientos (fecha, concepto) VALUES (?, ?)');
        $insAsiento->execute([$fechaSql, $concepto]);
        $asientoId = (int) $pdo->lastInsertId();

        $insRenglon = $pdo->prepare(
            'INSERT INTO renglones (asiento_id, linea, cuenta, debe, haber) VALUES (?, ?, ?, ?, ?)'
        );
        // R1: Inventario
        $insRenglon->execute([$asientoId, 1, '1.1.4', $costoTotalCompra, 0.00]);
        // R2: IVA Credito Fiscal
        $insRenglon->execute([$asientoId, 2, '1.1.5', $ivaCredito, 0.00]);
        // R3: Pago (Bancos o Proveedores)
        $insRenglon->execute([$asientoId, 3, $cuentaPago, 0.00, $totalPagar]);

        // 2. Movimiento en Kardex
        $sqlK = 'INSERT INTO kardex (producto_id, fecha, tipo, detalle, asiento_id,
                                     cant_entrada, costo_entrada, total_entrada,
                                     cant_salida, costo_salida, total_salida,
                                     cant_saldo, costo_promedio, total_saldo)
                 VALUES (?, ?, "COMPRA", ?, ?, ?, ?, ?, 0, 0, 0, ?, ?, ?)';
        $pdo->prepare($sqlK)->execute([
            $productoId,
            $fechaSql,
            $concepto,
            $asientoId,
            $cantidad,
            $precioUnitario,
            $costoTotalCompra,
            $nuevaExistencia,
            $nuevoCostoProm,
            $nuevoValorTotal,
        ]);

        // 3. Actualizar Producto
        $updP = $pdo->prepare(
            'UPDATE productos SET existencia = ?, costo_promedio = ?, valor_total = ? WHERE id = ?'
        );
        $updP->execute([$nuevaExistencia, $nuevoCostoProm, $nuevoValorTotal, $productoId]);

        $pdo->commit();

        return [
            'ok'                  => true,
            'asiento_id'          => $asientoId,
            'nueva_existencia'    => $nuevaExistencia,
            'nuevo_costo_prom'    => $nuevoCostoProm,
            'nuevo_valor_total'   => $nuevoValorTotal,
            'costo_compra'        => $costoTotalCompra,
            'iva_credito'         => $ivaCredito,
            'total_pagar'         => $totalPagar,
        ];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'Error al registrar compra: ' . $e->getMessage()];
    }
}

/**
 * 3.3. Registro de Ventas (Salida):
 * Utiliza el costo promedio ponderado vigente para el Costo de Ventas.
 * Genera doble asiento:
 *   Partida 1 (Ingreso):
 *     DEBE:  Bancos o Clientes (Total Venta + IVA)
 *     HABER: 4.1.3 Ingresos por ventas (Subtotal Venta)
 *     HABER: 2.1.4 IVA Débito Fiscal   (13% sobre Subtotal)
 *   Partida 2 (Costo):
 *     DEBE:  5.1.0 Costo de ventas (Cantidad * Costo Promedio Vigente)
 *     HABER: 1.1.4 Inventario de productos (Cantidad * Costo Promedio Vigente)
 */
function registrarVentaKardex(
    PDO $pdo,
    int $productoId,
    string $fecha,
    float $cantidad,
    float $precioVentaUnitario,
    string $cuentaCobro = '1.1.2',
    string $detalle = ''
): array {
    $producto = obtenerProductoPorId($pdo, $productoId);
    if (!$producto) {
        return ['ok' => false, 'error' => 'El producto no existe.'];
    }

    if ($cantidad <= 0) {
        return ['ok' => false, 'error' => 'La cantidad a vender debe ser mayor a cero.'];
    }
    if ($precioVentaUnitario <= 0) {
        return ['ok' => false, 'error' => 'El precio unitario de venta debe ser mayor a cero.'];
    }

    $existenciaAnt = (float) $producto['existencia'];
    if ($cantidad > $existenciaAnt) {
        return [
            'ok'    => false,
            'error' => 'Existencia insuficiente. Stock actual: ' . $existenciaAnt . ' unidades.',
        ];
    }

    $fechaSql = fechaAMysql($fecha);
    if ($fechaSql === null) {
        return ['ok' => false, 'error' => 'Fecha no válida.'];
    }

    $costoPromVigente = (float) $producto['costo_promedio'];
    $costoVentaTotal  = round($cantidad * $costoPromVigente, 2);

    $subtotalVenta = round($cantidad * $precioVentaUnitario, 2);
    $ivaDebito     = round($subtotalVenta * TASA_IVA, 2);
    $totalCobrar   = round($subtotalVenta + $ivaDebito, 2);

    $nuevaExistencia = round($existenciaAnt - $cantidad, 2);
    $nuevoValorTotal = round($nuevaExistencia * $costoPromVigente, 2);

    $conceptoIngreso = 'Venta ' . $cantidad . ' un. ' . $producto['codigo'] . ' - ' . ($detalle ?: 'Factura de venta');
    $conceptoCosto   = 'Costo de venta ' . $cantidad . ' un. ' . $producto['codigo'] . ' a costo prom. ' . formatearDinero($costoPromVigente);

    $pdo->beginTransaction();
    try {
        $insAsiento = $pdo->prepare('INSERT INTO asientos (fecha, concepto) VALUES (?, ?)');
        $insRenglon = $pdo->prepare(
            'INSERT INTO renglones (asiento_id, linea, cuenta, debe, haber) VALUES (?, ?, ?, ?, ?)'
        );

        // 1. Partida de Ingreso
        $insAsiento->execute([$fechaSql, $conceptoIngreso]);
        $asientoIngresoId = (int) $pdo->lastInsertId();

        $insRenglon->execute([$asientoIngresoId, 1, $cuentaCobro, $totalCobrar, 0.00]);
        $insRenglon->execute([$asientoIngresoId, 2, '4.1.3', 0.00, $subtotalVenta]);
        $insRenglon->execute([$asientoIngresoId, 3, '2.1.4', 0.00, $ivaDebito]);

        // 2. Partida de Costo
        $insAsiento->execute([$fechaSql, $conceptoCosto]);
        $asientoCostoId = (int) $pdo->lastInsertId();

        $insRenglon->execute([$asientoCostoId, 1, '5.1.0', $costoVentaTotal, 0.00]);
        $insRenglon->execute([$asientoCostoId, 2, '1.1.4', 0.00, $costoVentaTotal]);

        // 3. Movimiento en Kardex
        $sqlK = 'INSERT INTO kardex (producto_id, fecha, tipo, detalle, asiento_id, asiento_costo_id,
                                     cant_entrada, costo_entrada, total_entrada,
                                     cant_salida, costo_salida, total_salida,
                                     cant_saldo, costo_promedio, total_saldo)
                 VALUES (?, ?, "VENTA", ?, ?, ?, 0, 0, 0, ?, ?, ?, ?, ?, ?)';
        $pdo->prepare($sqlK)->execute([
            $productoId,
            $fechaSql,
            $conceptoIngreso,
            $asientoIngresoId,
            $asientoCostoId,
            $cantidad,
            $costoPromVigente,
            $costoVentaTotal,
            $nuevaExistencia,
            $costoPromVigente,
            $nuevoValorTotal,
        ]);

        // 4. Actualizar Producto
        $updP = $pdo->prepare(
            'UPDATE productos SET existencia = ?, valor_total = ? WHERE id = ?'
        );
        $updP->execute([$nuevaExistencia, $nuevoValorTotal, $productoId]);

        $pdo->commit();

        return [
            'ok'                  => true,
            'asiento_ingreso_id'  => $asientoIngresoId,
            'asiento_costo_id'    => $asientoCostoId,
            'nueva_existencia'    => $nuevaExistencia,
            'costo_promedio'      => $costoPromVigente,
            'nuevo_valor_total'   => $nuevoValorTotal,
            'subtotal_venta'      => $subtotalVenta,
            'iva_debito'          => $ivaDebito,
            'total_cobrar'        => $totalCobrar,
            'costo_venta'         => $costoVentaTotal,
        ];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'Error al registrar venta: ' . $e->getMessage()];
    }
}

/** Historial de movimientos Kardex para un producto o general. */
function obtenerMovimientosKardex(PDO $pdo, ?int $productoId = null): array
{
    $where = ($productoId !== null) ? ' WHERE k.producto_id = ' . (int) $productoId : '';
    $sql = 'SELECT k.*, p.codigo AS prod_codigo, p.descripcion AS prod_descripcion
              FROM kardex k
              JOIN productos p ON p.id = k.producto_id
              ' . $where . '
          ORDER BY k.id ASC';

    return $pdo->query($sql)->fetchAll();
}
