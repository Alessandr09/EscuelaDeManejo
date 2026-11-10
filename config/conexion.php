<?php
/**
 * config/conexion.php - Conexion PDO a MySQL (WAMP: root / sin contrasena).
 *
 * Crea la base de datos y el esquema del simulador de examenes la primera
 * vez que se usa, igual que en el proyecto PupuseriaRinas.
 */
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'esdrive');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Conexion PDO a MySQL.
 * @param bool $fatale Si true y MySQL no responde, detiene la pagina.
 *                     Si false, devuelve null (uso publico: la pagina sigue).
 */
function db(bool $fatale = true): ?PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $charset = 'charset=' . DB_CHARSET;

    $ultimoError = null;
    foreach ([DB_HOST, 'localhost'] as $host) {
        try {
            $dsn = "mysql:host=$host;$charset";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo->exec('USE `' . DB_NAME . '`');
            $pdo->exec("CREATE TABLE IF NOT EXISTS examen_alumnos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(120) NOT NULL,
                dui VARCHAR(20) NULL,
                codigo VARCHAR(32) NOT NULL UNIQUE,
                habilitado_desde DATETIME NOT NULL,
                vence DATETIME NOT NULL,
                inicio_real DATETIME NULL,
                estado ENUM('pendiente','en_curso','aprobado','reprobado','expirado') NOT NULL DEFAULT 'pendiente',
                aciertos INT NOT NULL DEFAULT 0,
                total INT NOT NULL DEFAULT 0,
                porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,
                nota TINYINT UNSIGNED NOT NULL DEFAULT 0,
                respuestas LONGTEXT NULL,
                pregunta_actual INT NOT NULL DEFAULT 0,
                terminado_en DATETIME NULL,
                creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (estado),
                INDEX (codigo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            return $pdo;
        } catch (PDOException $e) {
            $ultimoError = $e;
            $pdo = null;
        }
    }

    if (!$fatale) {
        return null;
    }
    http_response_code(500);
    exit('No se pudo conectar a MySQL. ¿Esta WAMP encendido (servicio MySQL)?<br>'
        . '<small>' . htmlspecialchars($ultimoError->getMessage() ?? '') . '</small>');
}
