<?php
/**
 * db.php - Conexion y esquema de la BD ESDRIVE (WAMP / MySQL)
 *
 * Crea automaticamente la base de datos `esdrive` con todas sus tablas
 * y siembra datos de ejemplo la primera vez.
 *
 * Credenciales por defecto de WAMP: root / sin password en localhost.
 */

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = 'localhost';
    $user = 'root';
    $pass = '';

    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    $pdo->exec('CREATE DATABASE IF NOT EXISTS `esdrive`
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE `esdrive`');

    /* ================= ESQUEMA ================= */

    $pdo->exec('CREATE TABLE IF NOT EXISTS `vehiculos` (
        `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `nombre`          VARCHAR(120) NOT NULL,
        `placa`           VARCHAR(20)  NOT NULL DEFAULT "",
        `tipo`            VARCHAR(60)  NOT NULL DEFAULT "",
        `imagen`          VARCHAR(255) NOT NULL DEFAULT "",
        `estado`          ENUM("disponible","en_uso") NOT NULL DEFAULT "disponible",
        `disponible_desde` DATETIME    NULL,
        `proximo_uso`     DATETIME    NULL,
        `empleado_id`     INT UNSIGNED NULL,
        `tiempo_uso`      VARCHAR(60)  NOT NULL DEFAULT "",
        `tiempo_libre`    VARCHAR(60)  NOT NULL DEFAULT "",
        `creado_en`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS `empleados` (
        `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `nombre`    VARCHAR(120) NOT NULL,
        `cargo`     VARCHAR(80)  NOT NULL DEFAULT "",
        `telefono`  VARCHAR(40)  NOT NULL DEFAULT "",
        `email`     VARCHAR(160) NOT NULL DEFAULT "",
        `imagen`    VARCHAR(255) NOT NULL DEFAULT "",
        `creado_en` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS `usuarios` (
        `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `nombre`    VARCHAR(120) NOT NULL,
        `usuario`   VARCHAR(60)  NOT NULL,
        `clave`     VARCHAR(255) NOT NULL,
        `rol`       VARCHAR(30)  NOT NULL DEFAULT "operador",
        `permisos`  TEXT         NULL,
        `activo`    TINYINT(1)   NOT NULL DEFAULT 1,
        `creado_en` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_usuario` (`usuario`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS `alumnos` (
        `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `nombre`    VARCHAR(120) NOT NULL,
        `email`     VARCHAR(160) NOT NULL DEFAULT "",
        `telefono`  VARCHAR(40)  NOT NULL DEFAULT "",
        `curso`     VARCHAR(60)  NOT NULL DEFAULT "",
        `estado`    VARCHAR(20)  NOT NULL DEFAULT "Activo",
        `creado_en` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS `examenes` (
        `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `titulo`      VARCHAR(160) NOT NULL,
        `curso`       VARCHAR(60)  NOT NULL DEFAULT "",
        `preguntas`   INT          NOT NULL DEFAULT 0,
        `duracion`    VARCHAR(30)  NOT NULL DEFAULT "",
        `fecha`       DATE         NULL,
        `estado`      VARCHAR(20)  NOT NULL DEFAULT "Borrador",
        `descripcion` TEXT         NULL,
        `creado_en`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS `cursos` (
        `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `nombre`      VARCHAR(120) NOT NULL,
        `codigo`      VARCHAR(20)  NOT NULL DEFAULT "",
        `precio`      DECIMAL(10,2) NOT NULL DEFAULT 0,
        `duracion`    VARCHAR(40)  NOT NULL DEFAULT "",
        `estado`      VARCHAR(20)  NOT NULL DEFAULT "Activo",
        `descripcion` TEXT         NULL,
        `creado_en`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS `horarios` (
        `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `curso_id`     INT UNSIGNED NULL,
        `empleado_id`  INT UNSIGNED NULL,
        `dia`          VARCHAR(20)  NOT NULL DEFAULT "",
        `hora_inicio`  VARCHAR(10)  NOT NULL DEFAULT "",
        `hora_fin`     VARCHAR(10)  NOT NULL DEFAULT "",
        `cupo`         INT          NOT NULL DEFAULT 0,
        `creado_en`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS `config` (
        `clave`  VARCHAR(60) NOT NULL,
        `valor`  TEXT NULL,
        PRIMARY KEY (`clave`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    /* ================= EXAMEN APROBATORIO ================= */
    /* Tabla propia del simulador de examen (ver EXAMEN.md).
       Guarda la nota de cada alumno: estado, aciertos, porcentaje y nota. */
    $pdo->exec('CREATE TABLE IF NOT EXISTS `examen_alumnos` (
        `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `nombre`           VARCHAR(120) NOT NULL,
        `dui`              VARCHAR(20)  NULL,
        `codigo`           VARCHAR(32)  NOT NULL,
        `habilitado_desde` DATETIME     NOT NULL,
        `vence`            DATETIME     NOT NULL,
        `inicio_real`      DATETIME     NULL,
        `estado`           VARCHAR(20)  NOT NULL DEFAULT "pendiente",
        `aciertos`         INT          NOT NULL DEFAULT 0,
        `total`            INT          NOT NULL DEFAULT 50,
        `porcentaje`       DECIMAL(5,2) NOT NULL DEFAULT 0,
        `nota`             TINYINT UNSIGNED NOT NULL DEFAULT 0,
        `respuestas`       LONGTEXT     NULL,
        `pregunta_actual`  INT          NOT NULL DEFAULT 0,
        `terminado_en`     DATETIME     NULL,
        `creado_en`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_examen_codigo` (`codigo`),
        KEY `idx_examen_estado` (`estado`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    /* ================= MIGRACIONES SUAVES ================= */
    $agregarColumna = function (string $tabla, string $col, string $ddl) use ($pdo) {
        $cols = [];
        foreach ($pdo->query("SHOW COLUMNS FROM `$tabla`") as $c) { $cols[] = $c['Field']; }
        if (!in_array($col, $cols, true)) { $pdo->exec("ALTER TABLE `$tabla` ADD COLUMN $ddl"); }
    };
    $agregarColumna('vehiculos', 'proximo_uso',     '`proximo_uso` DATETIME NULL AFTER `disponible_desde`');
    $agregarColumna('vehiculos', 'empleado_id',     '`empleado_id` INT UNSIGNED NULL AFTER `proximo_uso`');

    /* ================= DATOS DE EJEMPLO ================= */
    $base = BASE_URL . '/assets/img/';
    $ahora = date('Y-m-d H:i:s');

    if ((int) $pdo->query('SELECT COUNT(*) FROM `vehiculos`')->fetchColumn() === 0) {
        $st = $pdo->prepare('INSERT INTO `vehiculos`
            (`nombre`,`placa`,`tipo`,`imagen`,`estado`,`disponible_desde`,`proximo_uso`,`tiempo_uso`,`tiempo_libre`)
            VALUES (?,?,?,?,?,?,?,?,?)');
        $st->execute(['Microbus escolar', 'ESD-101', 'Microbus', $base . 'microbus.jpg', 'disponible', $ahora, null, '4 horas', '2 horas']);
        $st->execute(['Sedan manual',     'ESD-202', 'Sedan',    $base . 'sedan.jpg',     'disponible', $ahora, date('Y-m-d H:i:s', strtotime('+45 minutes')), '2 horas', '6 horas']);
        $st->execute(['Motocicleta',      'ESD-303', 'Motocicleta', $base . 'motocicleta.jpg', 'en_uso', date('Y-m-d H:i:s', strtotime('+2 hours')), null, '30 minutos', '1 hora']);
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM `empleados`')->fetchColumn() === 0) {
        $st = $pdo->prepare('INSERT INTO `empleados` (`nombre`,`cargo`,`telefono`,`email`) VALUES (?,?,?,?)');
        $st->execute(['Carlos Ramírez',   'Instructor certificado',   '+503 2222-3333', 'carlos@esdrive.com']);
        $st->execute(['Ana Martínez',     'Instructora de circuito',  '+503 2222-3334', 'ana@esdrive.com']);
        $st->execute(['José Hernández',   'Instructor de noche',      '+503 2222-3335', 'jose@esdrive.com']);
        $st->execute(['Maria Fernanda Lopez', 'Instructora de noche', '+503 2222-3336', 'maria@esdrive.com']);
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM `usuarios`')->fetchColumn() === 0) {
        if (!function_exists('esdrive_permisos_default')) {
            require_once __DIR__ . '/auth.php';
        }
        $st = $pdo->prepare('INSERT INTO `usuarios` (`nombre`,`usuario`,`clave`,`rol`,`permisos`,`activo`) VALUES (?,?,?,?,?,1)');
        foreach ([
            ['Administrador del sistema', 'admin',  'administrador'],
            ['Ana Martinez (Editor)',    'ana',    'editor'],
            ['Carlos Ramirez (Operador)','carlos', 'operador'],
        ] as $u) {
            $perms = function_exists('esdrive_permisos_default') ? esdrive_permisos_default($u[2]) : [];
            $st->execute([$u[0], $u[1], password_hash('1234', PASSWORD_DEFAULT), $u[2], json_encode($perms)]);
        }
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM `cursos`')->fetchColumn() === 0) {
        $st = $pdo->prepare('INSERT INTO `cursos` (`nombre`,`codigo`,`precio`,`duracion`,`estado`,`descripcion`) VALUES (?,?,?,?,?,?)');
        $st->execute(['Clase A - Motocicleta', 'CA', 120.00, '2 semanas',   'Activo', 'Manejo seguro de motocicleta, senalamiento vial y practicas en circuito.']);
        $st->execute(['Clase B - Particular',  'CB', 180.00, '4 semanas',   'Activo', 'Vehiculo particular manual y automatico con instructores certificados.']);
        $st->execute(['Clase C - Carga',       'CC', 260.00, '6 semanas',   'Activo', 'Vehiculos de carga, estabilidad y carga seguro de mercancia.']);
        $st->execute(['Renovacion',            'RN',  60.00, '1 semana',    'Activo', 'Actualizacion de licencia vigente con evaluacion practica.']);
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM `alumnos`')->fetchColumn() === 0) {
        $st = $pdo->prepare('INSERT INTO `alumnos` (`nombre`,`email`,`telefono`,`curso`,`estado`,`creado_en`) VALUES (?,?,?,?,?,?)');
        $datos = [
            ['Maria Lopez',      'maria@email.com',      '+503 7001-1111', 'Clase B', 'Activo',    '-5 months'],
            ['Carlos Ramirez',   'carlos@email.com',     '+503 7002-2222', 'Clase A', 'Pendiente', '-5 months'],
            ['Ana Martinez',     'ana@email.com',        '+503 7003-3333', 'Clase C', 'Activo',    '-4 months'],
            ['Jose Hernandez',   'jose@email.com',       '+503 7004-4444', 'Clase B', 'Inactivo',  '-3 months'],
            ['Laura Diaz',       'laura@email.com',      '+503 7005-5555', 'Renovacion', 'Activo', '-3 months'],
            ['Roberto Sanchez',  'roberto@email.com',    '+503 7006-6666', 'Clase C', 'Activo',    '-2 months'],
            ['Gabriela Reyes',   'gabriela@email.com',   '+503 7007-7777', 'Clase A', 'Pendiente', '-2 months'],
            ['Diego Morales',    'diego@email.com',      '+503 7008-8888', 'Clase B', 'Activo',    '-1 month'],
            ['Sofia Castillo',   'sofia@email.com',      '+503 7009-9999', 'Clase C', 'Activo',    '-1 month'],
            ['Andres Vega',      'andres@email.com',     '+503 7010-1010', 'Clase B', 'Activo',    '-10 days'],
            ['Valeria Ortiz',    'valeria@email.com',    '+503 7011-1111', 'Renovacion', 'Pendiente', '-5 days'],
            ['Mateo Rivas',      'mateo@email.com',      '+503 7012-1212', 'Clase A', 'Activo',    '-2 days'],
        ];
        foreach ($datos as $d) { $st->execute([$d[0], $d[1], $d[2], $d[3], $d[4], date('Y-m-d H:i:s', strtotime($d[5]))]); }
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM `examenes`')->fetchColumn() === 0) {
        $st = $pdo->prepare('INSERT INTO `examenes` (`titulo`,`curso`,`preguntas`,`duracion`,`fecha`,`estado`,`descripcion`) VALUES (?,?,?,?,?,?,?)');
        $exams = [
            ['Seccion A: Senalamiento vial', 'Clase B', 30, '20 min', '+3 days',  'Activo',    '<p>Evaluacion teorica sobre senalamiento vial horizontal y vertical.</p>'],
            ['Manejo defensivo teorico',     'Clase B', 25, '25 min', '+6 days',  'Activo',    '<p>Principios de manejo defensivo y anticipacion de riesgos.</p>'],
            ['Normas de transito urbano',    'Clase A', 20, '15 min', '+9 days',  'Pendiente', '<p>Normas urbanas, prioridades y limites de velocidad.</p>'],
            ['Carga y estabilidad',          'Clase C', 35, '40 min', '+13 days', 'Borrador',  '<p>Calculo de carga, estabilidad y frenado en vehiculos pesados.</p>'],
            ['Renovacion - Actualizacion',   'Renovacion', 15, '10 min', '+16 days', 'Activo', '<p>Actualizacion de normativa vigente para renovacion de licencia.</p>'],
            ['Practico - Circuito cerrado',  'Clase A', 10, '30 min', '+19 days', 'Pendiente', '<p>Evaluacion practica en circuito cerrado.</p>'],
        ];
        foreach ($exams as $e) { $st->execute([$e[0], $e[1], $e[2], $e[3], date('Y-m-d', strtotime($e[4])), $e[5], $e[6]]); }
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM `horarios`')->fetchColumn() === 0) {
        $st = $pdo->prepare('INSERT INTO `horarios` (`curso_id`,`empleado_id`,`dia`,`hora_inicio`,`hora_fin`,`cupo`) VALUES (?,?,?,?,?,?)');
        $horas = [
            [1, 1, 'Lunes',     '08:00', '10:00', 4],
            [2, 2, 'Martes',    '10:00', '12:00', 6],
            [2, 1, 'Miercoles', '14:00', '16:00', 6],
            [3, 3, 'Jueves',    '08:00', '12:00', 3],
            [4, 2, 'Viernes',   '16:00', '18:00', 8],
            [1, 4, 'Sabado',    '09:00', '11:00', 4],
        ];
        foreach ($horas as $h) { $st->execute($h); }
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM `config`')->fetchColumn() === 0) {
        $st = $pdo->prepare('INSERT INTO `config` (`clave`,`valor`) VALUES (?,?)');
        foreach ([
            'nombre'     => 'ESDRIVE',
            'telefono'   => '+503 2222-3333',
            'email'      => 'contacto@esdrive.com',
            'direccion'  => 'Av. Principal #123, San Salvador',
            'horario'    => 'Lun a Sab: 7:00 - 18:00',
            'whatsapp'   => '50370000000',
        ] as $k => $v) { $st->execute([$k, $v]); }
    }

    return $pdo;
}
