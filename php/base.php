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

// Hora local de El Salvador.
// Se fija siempre (no solo si el php.ini lo deja vacio) porque el servidor web
// puede venir con UTC: los horarios de los examenes deben compararse en hora local.
date_default_timezone_set('America/El_Salvador');

define('APP_NOMBRE', 'ESDRIVE');
define('APP_TAGLINE', 'Escuela de Manejo');