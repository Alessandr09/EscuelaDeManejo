<?php
/**
 * Pagina de inicio: resumen del estado actual + menu de todos los modulos contables.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';

$totalCuentas   = (int) $pdo->query('SELECT COUNT(*) FROM cuentas')->fetchColumn();
$totalAsientos  = (int) $pdo->query('SELECT COUNT(*) FROM asientos')->fetchColumn();
$totalRenglones = (int) $pdo->query('SELECT COUNT(*) FROM renglones')->fetchColumn();
$totalProductos = (int) $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn();
$valorInventario= (float) ($pdo->query('SELECT COALESCE(SUM(valor_total), 0) FROM productos')->fetchColumn() ?? 0);

// Comprobación de estado financiero
$eeff = calcularEstadoResultados($pdo);
$bg   = calcularBalanceGeneral($pdo, $eeff['utilidad_operativa']);
$bal  = calcularBalanzaComprobacion($pdo);

$opciones = [
    ['modulos/modulo1_catalogo.php', 'Catálogo de cuentas (Módulo 1)',
        'Consulta el listado completo de cuentas clasificadas por activo, pasivo, capital y rubro.'],
    ['modulos/modulo2_diario.php?accion=nueva', 'Registrar partida de diario',
        'Crea un nuevo asiento contable con validación obligatoria de partida doble.'],
    ['modulos/modulo2_diario.php?accion=ver', 'Ver Libro Diario',
        'Revisa el detalle de los asientos registrados en orden cronológico con sus glosas.'],
    ['modulos/modulo3_mayor.php', 'Libro Mayor general',
        'Centralización automática de cargos, abonos y saldos por cuenta contable.'],
    ['modulos/modulo_estados.php?tab=balanza', '2.1. Balanza de Comprobación (MÓDULO 2 NUEVO)',
        'Verificación de sumas iguales: ∑ Mov. Deudores = ∑ Mov. Acreedores y ∑ Saldos Deudores = ∑ Saldos Acreedores.'],
    ['modulos/modulo_estados.php?tab=resultados', '2.2. Estado de Resultados (MÓDULO 2 NUEVO)',
        'Cálculo jerárquico: Ventas − Costo de Ventas = Utilidad Bruta − Gastos Operativos = Utilidad Neta.'],
    ['modulos/modulo_estados.php?tab=balance', '2.3. Balance General (MÓDULO 2 NUEVO)',
        'Estado de Situación Financiera clasificado con validación de la Ecuación Contable (Activo = Pasivo + Patrimonio).'],
    ['modulos/modulo_kardex.php', 'Valuación y Control de Inventarios (MÓDULO 3 NUEVO)',
        'Control de mercancía con Método Promedio Ponderado, registro de compras y ventas con doble asiento contable.'],
    ['caso_prueba.php', 'Casos de prueba y simulación',
        'Carga con un clic el caso comercial completo con Kardex y estados financieros listos para evaluar.'],
];

iniciarPagina('Inicio · Panel de Control Contable', 'index.php');

?>
<section class="tarjetas-stats" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="stat">
        <span class="stat-valor"><?= $totalCuentas ?></span>
        <span class="stat-etiqueta">Cuentas en el catálogo</span>
    </div>
    <div class="stat">
        <span class="stat-valor"><?= $totalAsientos ?></span>
        <span class="stat-etiqueta">Asientos de diario</span>
    </div>
    <div class="stat">
        <span class="stat-valor"><?= $totalProductos ?></span>
        <span class="stat-etiqueta">Productos en inventario</span>
    </div>
    <div class="stat">
        <span class="stat-valor" style="color:var(--tc-accent);"><?= formatearDinero($valorInventario) ?></span>
        <span class="stat-etiqueta">Valor total inventario (Kardex)</span>
    </div>
    <div class="stat">
        <span class="stat-valor" style="color:<?= ($eeff['utilidad_operativa'] >= 0) ? 'var(--tc-ok-text)' : 'var(--tc-err-text)' ?>;">
            <?= formatearDinero($eeff['utilidad_operativa']) ?>
        </span>
        <span class="stat-etiqueta">Utilidad neta del ejercicio</span>
    </div>
</section>

<?php if ($totalAsientos > 0): ?>
    <section class="tarjeta" style="margin-bottom:1.25rem;">
        <div class="encabezado-seccion">
            <h3>Estado del ciclo contable</h3>
            <span class="badge-tipo badge-<?= $bg['esta_cuadrado'] ? 'compra' : 'venta' ?>" style="font-size:.85rem; padding:.25rem .75rem;">
                <?= $bg['esta_cuadrado'] ? 'Ciclo Balanceado [OK]' : 'Desbalance Detectado [!]' ?>
            </span>
        </div>
        <div class="fila-resumen" style="margin-top:0;">
            <div class="resumen-caja">
                <dl><dt>Total Activo</dt><dd><?= formatearDinero($bg['total_activo']) ?></dd></dl>
            </div>
            <div class="resumen-caja">
                <dl><dt>Total Pasivo + Patrimonio</dt><dd><?= formatearDinero($bg['total_pasivo_mas_patrimonio']) ?></dd></dl>
            </div>
            <div class="resumen-caja destacado <?= $bg['esta_cuadrado'] ? '' : 'resumen-alerta' ?>">
                <dl><dt>Diferencia de Ecuación</dt><dd><?= formatearDinero($bg['diferencia']) ?></dd></dl>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="tarjeta">
    <div class="encabezado-seccion">
        <h3>Módulos del Sistema Contable</h3>
    </div>

    <div class="lista-opciones">
        <?php foreach ($opciones as $n => $opcion): ?>
            <a class="opcion" href="<?= RUTA_BASE ?>/<?= $opcion[0] ?>">
                <span class="opcion-num"><?= $n + 1 ?></span>
                <span class="opcion-texto">
                    <strong><?= h($opcion[1]) ?></strong>
                    <small><?= h($opcion[2]) ?></small>
                </span>
                <svg class="opcion-flecha" viewBox="0 0 16 16" width="16" height="16" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8.5 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php
terminarPagina();
