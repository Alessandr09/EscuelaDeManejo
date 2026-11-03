<?php
/**
 * base.php - Configuracion global de ESDRIVE
 * Define la URL base para que el proyecto funcione en cualquier carpeta
 * (C:\wamp64\www\ESDRIVE, la VM o el escritorio) sin romper los enlaces.
 */

if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    define('BASE_URL', rtrim($scriptDir, '/'));
}

define('APP_NOMBRE', 'ESDRIVE');
define('APP_TAGLINE', 'Escuela de Manejo');