<?php
/**
 * Datos de conexion a la base de datos.
 *
 * Por defecto la aplicacion usa SQLite: la base es UN SOLO ARCHIVO
 * dentro de la carpeta data/, sin ningun servidor que instalar.
 * Asi el sistema funciona con doble clic en ejecutar.bat.
 *
 * Si se prefiere MySQL (WAMP), cambiar DB_MOTOR a 'mysql'.
 */

declare(strict_types=1);

/* Motor de base de datos: 'sqlite' (sin servidor) o 'mysql' (WAMP). */
const DB_MOTOR = 'sqlite';

/* SQLite: ruta del archivo de la base de datos. */
const DB_SQLITE_RUTA = __DIR__ . '/../data/tecnicas_contables.db';

/* MySQL (WAMP): MySQL 8.4.7 corre en el puerto 3306. El MariaDB (3307) no se usa. */
const DB_HOST    = '127.0.0.1';
const DB_PUERTO  = 3306;
const DB_NOMBRE  = 'tecnicas_contables';
const DB_USUARIO = 'root';
const DB_CLAVE   = '';        // WAMP viene sin clave para root
const DB_CHARSET = 'utf8mb4';
