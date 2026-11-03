<?php
/**
 * Arranque comun: defines de rutas, conexion y esquema.
 * Toda pagina del proyecto incluye este archivo primero.
 */

declare(strict_types=1);

require_once __DIR__ . '/funciones.php';

/**
 * Ruta web de la raiz del proyecto, para construir enlaces correctos
 * sin importar desde que subcarpeta se llame a la pagina.
 * Ejemplo: /TecnicasContablesSoftware
 */
if (!defined('RUTA_BASE')) {
    $dirApp     = str_replace('\\', '/', dirname(__DIR__));
    $docRoot    = rtrim(str_replace('\\', '/', (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    $rutaBase   = ($docRoot !== '' && str_starts_with($dirApp, $docRoot))
        ? substr($dirApp, strlen($docRoot))
        : $dirApp;

    define('RUTA_BASE', rtrim($rutaBase, '/'));
}

/** Conexion compartida a la base de datos. */
$pdo = bd();

// Crea tablas y catalogo inicial la primera vez (idempotente).
instalarEsquema($pdo);
