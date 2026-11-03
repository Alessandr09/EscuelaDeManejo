<?php
/**
 * Capa de base de datos: conexion PDO, creacion del esquema y
 * catalogo de cuentas precargado (Modulo 1).
 *
 * Soporta dos motores (ver config/conexion.php, constante DB_MOTOR):
 *   - 'sqlite': un solo archivo, sin servidor. Modo por defecto.
 *   - 'mysql':  MySQL de WAMP en 127.0.0.1:3306.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

/** Abre la conexion PDO segun el motor configurado. */
function bd(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        // MySQL devuelve DECIMAL como string: se respeta la precision exacta.
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ];

    $pdo = (DB_MOTOR === 'sqlite')
        ? conectarSqlite($opciones)
        : conectarMysql($opciones);

    return $pdo;
}

/**
 * SQLite: la base es un solo archivo (data/tecnicas_contables.db).
 * No requiere ningun servidor instalado.
 */
function conectarSqlite(array $opciones): PDO
{
    $carpeta = dirname(DB_SQLITE_RUTA);

    if (!is_dir($carpeta) && !mkdir($carpeta, 0777, true)) {
        throw new RuntimeException('No se pudo crear la carpeta de datos: ' . $carpeta);
    }

    try {
        $pdo = new PDO('sqlite:' . DB_SQLITE_RUTA, null, null, $opciones);
        // Sin esto, SQLite ignora las llaves foraneas (y el ON DELETE CASCADE).
        $pdo->exec('PRAGMA foreign_keys = ON');
    } catch (PDOException $e) {
        throw new RuntimeException(
            'No se pudo abrir la base SQLite en ' . DB_SQLITE_RUTA . '. Detalle: ' . $e->getMessage(),
            0,
            $e
        );
    }

    return $pdo;
}

/** MySQL (WAMP): crea la base si todavia no existe. */
function conectarMysql(array $opciones): PDO
{
    // 1) Conexion a nivel de servidor para poder crear la base.
    try {
        $servidor = new PDO(
            'mysql:host=' . DB_HOST . ';port=' . DB_PUERTO . ';charset=' . DB_CHARSET,
            DB_USUARIO,
            DB_CLAVE,
            $opciones
        );
        $servidor->exec(
            'CREATE DATABASE IF NOT EXISTS `' . DB_NOMBRE . '` '
            . 'CHARACTER SET ' . DB_CHARSET . ' COLLATE ' . DB_CHARSET . '_unicode_ci'
        );
    } catch (PDOException $e) {
        throw new RuntimeException(
            'No se pudo conectar a MySQL en ' . DB_HOST . ':' . DB_PUERTO . '. '
            . 'Verifique que el servicio MySQL de WAMP este iniciado. Detalle: ' . $e->getMessage(),
            0,
            $e
        );
    }

    // 2) Conexion a la base del proyecto.
    return new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PUERTO . ';dbname=' . DB_NOMBRE . ';charset=' . DB_CHARSET,
        DB_USUARIO,
        DB_CLAVE,
        $opciones
    );
}

/**
 * Crea las tablas si no existen y carga las 14 cuentas del catalogo
 * la primera vez. Es idempotente: se puede ejecutar en cada carga.
 * El DDL cambia ligeramente segun el motor (ENUM/AUTO_INCREMENT son
 * propios de MySQL; en SQLite se usan TEXT/INTEGER PRIMARY KEY).
 */
function instalarEsquema(PDO $pdo): void
{
    if (DB_MOTOR === 'sqlite') {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS cuentas (
                codigo      TEXT PRIMARY KEY,
                nombre      TEXT NOT NULL,
                naturaleza  TEXT NOT NULL,
                saldo       TEXT NOT NULL,
                rubro       TEXT NULL
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS asientos (
                id       INTEGER PRIMARY KEY AUTOINCREMENT,
                fecha    TEXT NOT NULL,
                concepto TEXT NULL DEFAULT ""
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS renglones (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                asiento_id INTEGER NOT NULL,
                linea      INTEGER NOT NULL,
                cuenta     TEXT    NOT NULL,
                debe       NUMERIC NOT NULL DEFAULT 0,
                haber      NUMERIC NOT NULL DEFAULT 0,
                FOREIGN KEY (asiento_id)
                    REFERENCES asientos (id) ON DELETE CASCADE,
                FOREIGN KEY (cuenta)
                    REFERENCES cuentas (codigo)
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS productos (
                id             INTEGER PRIMARY KEY AUTOINCREMENT,
                codigo         TEXT UNIQUE NOT NULL,
                descripcion    TEXT NOT NULL,
                existencia     NUMERIC NOT NULL DEFAULT 0,
                costo_promedio NUMERIC NOT NULL DEFAULT 0,
                valor_total    NUMERIC NOT NULL DEFAULT 0,
                created_at     TEXT DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS kardex (
                id               INTEGER PRIMARY KEY AUTOINCREMENT,
                producto_id      INTEGER NOT NULL,
                fecha            TEXT NOT NULL,
                tipo             TEXT NOT NULL,
                detalle          TEXT NOT NULL,
                asiento_id       INTEGER NULL,
                asiento_costo_id INTEGER NULL,
                cant_entrada     NUMERIC NOT NULL DEFAULT 0,
                costo_entrada    NUMERIC NOT NULL DEFAULT 0,
                total_entrada    NUMERIC NOT NULL DEFAULT 0,
                cant_salida      NUMERIC NOT NULL DEFAULT 0,
                costo_salida     NUMERIC NOT NULL DEFAULT 0,
                total_salida     NUMERIC NOT NULL DEFAULT 0,
                cant_saldo       NUMERIC NOT NULL DEFAULT 0,
                costo_promedio   NUMERIC NOT NULL DEFAULT 0,
                total_saldo      NUMERIC NOT NULL DEFAULT 0,
                created_at       TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (producto_id) REFERENCES productos (id) ON DELETE CASCADE,
                FOREIGN KEY (asiento_id) REFERENCES asientos (id) ON DELETE SET NULL,
                FOREIGN KEY (asiento_costo_id) REFERENCES asientos (id) ON DELETE SET NULL
            )'
        );
    } else {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS cuentas (
                codigo      VARCHAR(20)  NOT NULL,
                nombre      VARCHAR(120) NOT NULL,
                naturaleza  ENUM("ACTIVO","PASIVO","CAPITAL") NOT NULL,
                saldo       ENUM("DEUDOR","ACREEDOR")       NOT NULL,
                rubro       VARCHAR(40)  NULL,
                PRIMARY KEY (codigo)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . DB_MOTOR . ' COLLATE=' . DB_CHARSET . '_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS asientos (
                id       INT AUTO_INCREMENT PRIMARY KEY,
                fecha    DATE NOT NULL,
                concepto VARCHAR(255) NULL DEFAULT ""
            ) ENGINE=InnoDB DEFAULT CHARSET=' . DB_CHARSET . ' COLLATE=' . DB_CHARSET . '_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS renglones (
                id         INT AUTO_INCREMENT NOT NULL,
                asiento_id INT NOT NULL,
                linea      SMALLINT      NOT NULL,
                cuenta     VARCHAR(20)   NOT NULL,
                debe       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
                haber      DECIMAL(14,2) NOT NULL DEFAULT 0.00,
                PRIMARY KEY (id),
                KEY fk_asiento (asiento_id),
                CONSTRAINT fk_asiento FOREIGN KEY (asiento_id)
                    REFERENCES asientos (id) ON DELETE CASCADE,
                CONSTRAINT fk_cuenta FOREIGN KEY (cuenta)
                    REFERENCES cuentas (codigo)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . DB_CHARSET . ' COLLATE=' . DB_CHARSET . '_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS productos (
                id             INT AUTO_INCREMENT PRIMARY KEY,
                codigo         VARCHAR(30)  NOT NULL UNIQUE,
                descripcion    VARCHAR(150) NOT NULL,
                existencia     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                costo_promedio DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                valor_total    DECIMAL(14,2) NOT NULL DEFAULT 0.00,
                created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=' . DB_CHARSET . ' COLLATE=' . DB_CHARSET . '_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS kardex (
                id               INT AUTO_INCREMENT PRIMARY KEY,
                producto_id      INT NOT NULL,
                fecha            DATE NOT NULL,
                tipo             VARCHAR(20) NOT NULL,
                detalle          VARCHAR(255) NOT NULL,
                asiento_id       INT NULL,
                asiento_costo_id INT NULL,
                cant_entrada     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                costo_entrada    DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                total_entrada    DECIMAL(14,2) NOT NULL DEFAULT 0.00,
                cant_salida      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                costo_salida     DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                total_salida     DECIMAL(14,2) NOT NULL DEFAULT 0.00,
                cant_saldo       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                costo_promedio   DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                total_saldo      DECIMAL(14,2) NOT NULL DEFAULT 0.00,
                created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY fk_kardex_producto (producto_id),
                CONSTRAINT fk_kardex_prod FOREIGN KEY (producto_id)
                    REFERENCES productos (id) ON DELETE CASCADE,
                CONSTRAINT fk_kardex_asiento FOREIGN KEY (asiento_id)
                    REFERENCES asientos (id) ON DELETE SET NULL,
                CONSTRAINT fk_kardex_asiento_costo FOREIGN KEY (asiento_costo_id)
                    REFERENCES asientos (id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=' . DB_CHARSET . ' COLLATE=' . DB_CHARSET . '_unicode_ci'
        );
    }

    migrarColumnasSiFaltan($pdo);

    if ((int) $pdo->query('SELECT COUNT(*) FROM cuentas')->fetchColumn() === 0) {
        cargarCatalogoInicial($pdo);
    } else {
        asegurarCuentasNuevas($pdo);
    }
}

/** Agrega columnas faltantes a tablas preexistentes de forma segura. */
function migrarColumnasSiFaltan(PDO $pdo): void
{
    // 1. Columna rubro en cuentas
    $tieneRubro = false;
    if (DB_MOTOR === 'sqlite') {
        $cols = $pdo->query('PRAGMA table_info(cuentas)')->fetchAll();
        foreach ($cols as $col) {
            if ($col['name'] === 'rubro') {
                $tieneRubro = true;
                break;
            }
        }
        if (!$tieneRubro) {
            $pdo->exec('ALTER TABLE cuentas ADD COLUMN rubro TEXT NULL');
        }
    } else {
        $cols = $pdo->query("SHOW COLUMNS FROM cuentas LIKE 'rubro'")->fetchAll();
        if ($cols === []) {
            $pdo->exec('ALTER TABLE cuentas ADD COLUMN rubro VARCHAR(40) NULL');
        }
    }

    // 2. Columna concepto en asientos
    $tieneConcepto = false;
    if (DB_MOTOR === 'sqlite') {
        $cols = $pdo->query('PRAGMA table_info(asientos)')->fetchAll();
        foreach ($cols as $col) {
            if ($col['name'] === 'concepto') {
                $tieneConcepto = true;
                break;
            }
        }
        if (!$tieneConcepto) {
            $pdo->exec('ALTER TABLE asientos ADD COLUMN concepto TEXT NULL DEFAULT ""');
        }
    } else {
        $cols = $pdo->query("SHOW COLUMNS FROM asientos LIKE 'concepto'")->fetchAll();
        if ($cols === []) {
            $pdo->exec('ALTER TABLE asientos ADD COLUMN concepto VARCHAR(255) NULL DEFAULT ""');
        }
    }
}

/**
 * Catalogo de cuentas precargado completo con rubros contables.
 *
 * @return array<int, array{0:string,1:string,2:string,3:string,4:string}>
 */
function catalogoInicial(): array
{
    return [
        ['1.1.1', 'Caja',                           'ACTIVO',  'DEUDOR',   'ACTIVO_CORRIENTE'],
        ['1.1.2', 'Bancos',                         'ACTIVO',  'DEUDOR',   'ACTIVO_CORRIENTE'],
        ['1.1.3', 'Clientes',                       'ACTIVO',  'DEUDOR',   'ACTIVO_CORRIENTE'],
        ['1.1.4', 'Inventario de productos',        'ACTIVO',  'DEUDOR',   'ACTIVO_CORRIENTE'],
        ['1.1.5', 'IVA Crédito Fiscal',             'ACTIVO',  'DEUDOR',   'ACTIVO_CORRIENTE'],
        ['1.2.1', 'Equipo de computo (servidores)', 'ACTIVO',  'DEUDOR',   'ACTIVO_NO_CORRIENTE'],
        ['1.2.2', 'Mobiliario y equipo',            'ACTIVO',  'DEUDOR',   'ACTIVO_NO_CORRIENTE'],
        ['2.1.1', 'Proveedores',                    'PASIVO',  'ACREEDOR', 'PASIVO_CORRIENTE'],
        ['2.1.2', 'Nomina por pagar',               'PASIVO',  'ACREEDOR', 'PASIVO_CORRIENTE'],
        ['2.1.3', 'Impuestos por pagar',            'PASIVO',  'ACREEDOR', 'PASIVO_CORRIENTE'],
        ['2.1.4', 'IVA Débito Fiscal',              'PASIVO',  'ACREEDOR', 'PASIVO_CORRIENTE'],
        ['3.1.1', 'Capital social',                 'CAPITAL', 'ACREEDOR', 'PATRIMONIO'],
        ['3.1.2', 'Utilidades acumuladas',          'CAPITAL', 'ACREEDOR', 'PATRIMONIO'],
        ['4.1.1', 'Ingresos por licenciamiento',    'CAPITAL', 'ACREEDOR', 'INGRESOS'],
        ['4.1.2', 'Ingresos por servicios',         'CAPITAL', 'ACREEDOR', 'INGRESOS'],
        ['4.1.3', 'Ingresos por ventas',            'CAPITAL', 'ACREEDOR', 'INGRESOS'],
        ['5.1.0', 'Costo de ventas',                'CAPITAL', 'DEUDOR',   'COSTO_VENTAS'],
        ['5.1.1', 'Gastos de nomina',               'CAPITAL', 'DEUDOR',   'GASTOS_ADMIN'],
        ['5.1.2', 'Gastos de servicios basicos',    'CAPITAL', 'DEUDOR',   'GASTOS_ADMIN'],
        ['5.2.1', 'Gastos de administracion',       'CAPITAL', 'DEUDOR',   'GASTOS_ADMIN'],
        ['5.2.2', 'Gastos de venta',                'CAPITAL', 'DEUDOR',   'GASTOS_VENTA'],
    ];
}

function cargarCatalogoInicial(PDO $pdo): void
{
    $sql = 'INSERT INTO cuentas (codigo, nombre, naturaleza, saldo, rubro) VALUES (?, ?, ?, ?, ?)';
    $ins = $pdo->prepare($sql);

    foreach (catalogoInicial() as $fila) {
        $ins->execute($fila);
    }
}

/** Asegura que cuentas esenciales agregadas en actualizaciones existan y tengan su rubro. */
function asegurarCuentasNuevas(PDO $pdo): void
{
    $ins = $pdo->prepare('INSERT INTO cuentas (codigo, nombre, naturaleza, saldo, rubro) VALUES (?, ?, ?, ?, ?)');
    $upd = $pdo->prepare('UPDATE cuentas SET rubro = ? WHERE codigo = ? AND (rubro IS NULL OR rubro = "")');

    foreach (catalogoInicial() as $fila) {
        $codigo = $fila[0];
        $rubro  = $fila[4];

        $existe = (int) $pdo->query('SELECT COUNT(*) FROM cuentas WHERE codigo = ' . $pdo->quote($codigo))->fetchColumn() > 0;
        if (!$existe) {
            $ins->execute($fila);
        } else {
            $upd->execute([$rubro, $codigo]);
        }
    }
}
