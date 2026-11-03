<?php
/**
 * Estructura comun de las paginas: barra lateral, encabezado y pie.
 * Layout de panel (sidebar) con estilo propio y tema claro/oscuro.
 */

declare(strict_types=1);

/** Icono SVG en linea para un elemento del menu lateral. */
function iconoSvg(string $id): string
{
    $trazos = [
        // Casa
        'home'   => '<path d="M8 1.5 1 7.2V14a1 1 0 0 0 1 1h3.5a.5.5 0 0 0 .5-.5v-3.5h4V14.5a.5.5 0 0 0 .5.5H14a1 1 0 0 0 1-1V7.2L8 1.5Z"/>',
        // Cuadricula
        'grid'   => '<rect x="2.5" y="2.5" width="4.5" height="4.5" rx="1.2"/><rect x="9" y="2.5" width="4.5" height="4.5" rx="1.2"/>'
                  . '<rect x="2.5" y="9" width="4.5" height="4.5" rx="1.2"/><rect x="9" y="9" width="4.5" height="4.5" rx="1.2"/>',
        // Carpeta
        'folder' => '<path d="M1.5 5A1.5 1.5 0 0 1 3 3.5h3.17a1.5 1.5 0 0 1 1.06.44l.83.83h4.44A1.5 1.5 0 0 1 14 6.27V12.5A1.5 1.5 0 0 1 12.5 14h-11A1.5 1.5 0 0 1 1 12.5V5h.5Z"/>',
        // Documento
        'file'   => '<path d="M9 1.5 14 6.4V14a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14V2.5A1.5 1.5 0 0 1 3.5 1H9Z"/>',
        // Matraz de prueba
        'flask'  => '<path d="M5.5 1.5a.5.5 0 0 1 .5.5v4.2l3.9 6.3a1.5 1.5 0 0 1-1.3 2.3H4.4a1.5 1.5 0 0 1-1.3-2.3l3.9-6.3V2a.5.5 0 0 1 .5-.5Z"/>',
        // Grafica de barras / Reportes
        'report' => '<path d="M2 14a.5.5 0 0 1-.5-.5V5.5a.5.5 0 0 1 1 0V13.5a.5.5 0 0 1-.5.5Z"/>'
                  . '<path d="M6.5 14a.5.5 0 0 1-.5-.5V3.5a.5.5 0 0 1 1 0V13.5a.5.5 0 0 1-.5.5Z"/>'
                  . '<path d="M11 14a.5.5 0 0 1-.5-.5V7.5a.5.5 0 0 1 1 0V13.5a.5.5 0 0 1-.5.5Z"/>'
                  . '<path d="M14.5 14.5a.5.5 0 0 1-.5-.5V9.5a.5.5 0 0 1 1 0v4.5a.5.5 0 0 1-.5.5Z"/>',
        // Caja de inventario
        'box'    => '<path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5 8 5.961 14.154 3.5 8.186 1.113zM15 4.239l-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.761V6.838L1 4.239v7.922l6.5 2.6z"/>',
        // Balanza de comprobacion
        'scale'  => '<path d="M8 1a.5.5 0 0 1 .5.5V3h3.5a.5.5 0 0 1 .5.5v.5H15a.5.5 0 0 1 .354.854l-2 2a.5.5 0 0 1-.708 0l-2-2A.5.5 0 0 1 11 4.5V4H8.5v10.5h2a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1h2V4H5v.5a.5.5 0 0 1-.354.354l-2 2a.5.5 0 0 1-.708 0l-2-2A.5.5 0 0 1 .5 4H3v-.5A.5.5 0 0 1 3.5 3H7V1.5A.5.5 0 0 1 8 1z"/>',
    ];

    $d = $trazos[$id] ?? $trazos['grid'];

    return '<svg class="ico" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">' . $d . '</svg>';
}

/** Abre el HTML y muestra la barra lateral y el encabezado. */
function iniciarPagina(string $titulo, string $moduloActivo = '', string $accion = ''): void
{
    /** @var array<int, array{ruta:string, texto:string, icono:string, rutas_secundarias?:array<string>, hijos?:array<int, array{ruta:string, texto:string, accion:string}>}> */
    $menu = [
        ['ruta' => 'index.php', 'texto' => 'Inicio', 'icono' => 'home'],
        [
            'ruta'  => 'modulos/modulo1_catalogo.php',
            'texto' => 'Módulo 1 · Catálogo',
            'icono' => 'grid',
            'hijos' => [
                ['ruta' => 'modulos/modulo1_catalogo.php', 'texto' => 'Catálogo de cuentas', 'accion' => ''],
                ['ruta' => 'modulos/modulo1_catalogo.php?accion=registrar', 'texto' => 'Registrar cuenta', 'accion' => 'registrar'],
            ],
        ],
        [
            'ruta'  => 'modulos/modulo2_diario.php',
            'texto' => 'Diario y Mayor',
            'icono' => 'folder',
            'rutas_secundarias' => ['modulos/modulo3_mayor.php'],
            'hijos' => [
                ['ruta' => 'modulos/modulo2_diario.php?accion=nueva', 'texto' => 'Registrar partida', 'accion' => 'nueva'],
                ['ruta' => 'modulos/modulo2_diario.php?accion=ver', 'texto' => 'Libro Diario', 'accion' => 'ver'],
                ['ruta' => 'modulos/modulo3_mayor.php', 'texto' => 'Libro Mayor', 'accion' => 'mayor'],
            ],
        ],
        [
            'ruta'  => 'modulos/modulo_estados.php',
            'texto' => 'Módulo 2 · Balanza y EEFF',
            'icono' => 'report',
            'hijos' => [
                ['ruta' => 'modulos/modulo_estados.php?tab=balanza', 'texto' => '2.1 Balanza comprobación', 'accion' => 'balanza'],
                ['ruta' => 'modulos/modulo_estados.php?tab=resultados', 'texto' => '2.2 Estado de resultados', 'accion' => 'resultados'],
                ['ruta' => 'modulos/modulo_estados.php?tab=balance', 'texto' => '2.3 Balance general', 'accion' => 'balance'],
            ],
        ],
        [
            'ruta'  => 'modulos/modulo_kardex.php',
            'texto' => 'Módulo 3 · Kardex e Inv.',
            'icono' => 'box',
            'hijos' => [
                ['ruta' => 'modulos/modulo_kardex.php', 'texto' => 'Catálogo de productos', 'accion' => ''],
                ['ruta' => 'modulos/modulo_kardex.php?accion=compra', 'texto' => 'Registrar compra', 'accion' => 'compra'],
                ['ruta' => 'modulos/modulo_kardex.php?accion=venta', 'texto' => 'Registrar venta', 'accion' => 'venta'],
                ['ruta' => 'modulos/modulo_kardex.php?accion=tarjeta', 'texto' => 'Tarjeta Kardex', 'accion' => 'tarjeta'],
            ],
        ],
        ['ruta' => 'caso_prueba.php', 'texto' => 'Casos de prueba', 'icono' => 'flask'],
    ];

    $base = static function (string $ruta): string {
        return explode('?', $ruta, 2)[0];
    };

    ?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light dark">
<title><?= h($titulo) ?> - Tecnicas Contables</title>
<link rel="stylesheet" href="<?= RUTA_BASE ?>/assets/estilo.css">
<script>
/* Aplica el tema guardado antes de pintar para evitar el parpadeo. */
(function () {
    try {
        var t = localStorage.getItem('tecnicas_contables_theme');
        if (!t) {
            t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-theme', t);
    } catch (e) {}
})();
</script>
</head>
<body>

<div class="app">

    <!-- ============================ Barra lateral ============================ -->
    <aside class="sidebar" id="barraLateral">
        <div class="marca">
            <span class="marca-marca">TC</span>
            <span class="marca-texto">
                <strong>Automatizador</strong>
                <small>Sistema Contable Integral & Kardex</small>
            </span>
        </div>

        <nav class="menu">
            <?php foreach ($menu as $item): ?>
                <?php
                    $tieneHijos   = isset($item['hijos']);
                    $seccionActiva = ($base($item['ruta']) === $base($moduloActivo))
                        || (isset($item['rutas_secundarias']) && in_array($base($moduloActivo), $item['rutas_secundarias'], true));
                    $claseSeccion = $seccionActiva ? ' activo' : '';
                ?>
                <a class="menu-item<?= $claseSeccion ?>"
                   href="<?= RUTA_BASE ?>/<?= $item['ruta'] ?>">
                    <?= iconoSvg($item['icono']) ?>
                    <span><?= h($item['texto']) ?></span>
                </a>

                <?php if ($tieneHijos && $seccionActiva): ?>
                    <div class="menu-sub">
                        <?php foreach ($item['hijos'] as $hijo): ?>
                            <?php $activo = $hijo['accion'] === $accion; ?>
                            <a class="menu-sub-item<?= $activo ? ' activo' : '' ?>"
                               href="<?= RUTA_BASE ?>/<?= $hijo['ruta'] ?>">
                                <?= h($hijo['texto']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-pie">
            <button id="themeToggle" class="theme-toggle" type="button"
                    aria-label="Cambiar entre tema claro y oscuro" title="Cambiar tema">
                <svg class="icon-sol" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                    <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 0 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
                </svg>
                <svg class="icon-luna" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                    <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278z"/>
                </svg>
            </button>
            <span class="tema-etiqueta">Cambiar tema</span>
        </div>
    </aside>

    <!-- ============================== Contenido ============================== -->
    <div class="panel">

        <header class="topbar">
            <button class="abrir-menu" type="button" id="abrirMenu"
                    aria-label="Mostrar el menu de navegacion">
                <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                    <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5Zm0 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5Zm0 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5Z"/>
                </svg>
            </button>

            <h1 class="topbar-titulo"><?= h($titulo) ?></h1>

            <div class="topbar-acciones">
                <span class="avatar" aria-hidden="true">TC</span>
            </div>
        </header>

        <main class="contenido">
            <?php
}

/** Cierra el HTML. */
function terminarPagina(): void
{
    ?>
        </main>
    </div><!-- /.panel -->

    <div class="fondo-menu" id="fondoMenu" hidden></div>
</div><!-- /.app -->

<footer class="app-footer">
    <span>Proyecto entregable 2do Periodo (50%) &mdash; Tecnicas Contables</span>
    <span class="sep">&middot;</span>
    <span>Universidad Catolica de El Salvador</span>
</footer>

<script src="<?= RUTA_BASE ?>/assets/theme.js"></script>
</body>
</html>
    <?php
}