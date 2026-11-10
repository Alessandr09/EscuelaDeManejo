<?php
/**
 * auth.php - Sesiones, roles y permisos del panel ESDRIVE
 *
 * Niveles de permiso por modulo:
 *   ''            -> sin acceso
 *   'ver'         -> puede abrir la vista (solo lectura)
 *   'gestionar'   -> puede crear / editar / eliminar
 */
require_once __DIR__ . '/db.php';

/** Modulos del sistema: id => etiqueta */
function esdrive_modulos(): array
{
    return [
        'dashboard'     => 'Dashboard',
        'cuentas'       => 'Cuentas',
        'examenes'      => 'Examenes',
        'vehiculos'     => 'Vehiculos',
        'empleados'     => 'Empleados',
        'cursos'        => 'Cursos',
        'instructores'  => 'Instructores',
        'horarios'      => 'Horarios',
        'reportes'      => 'Reportes',
        'configuracion' => 'Configuracion',
        'usuarios'      => 'Usuarios',
    ];
}

/** Roles disponibles: id => descripcion */
function esdrive_roles(): array
{
    return [
        'administrador' => 'Acceso total a todo el sistema',
        'editor'        => 'Gestiona contenido academico y flota, sin usuarios ni configuracion',
        'operador'      => 'Gestiona vehiculos, empleados y horarios; el resto solo consulta',
        'auditor'       => 'Solo lectura: consulta todas las vistas y reportes',
    ];
}

/** Permisos por defecto de cada rol */
function esdrive_permisos_default(string $rol): array
{
    $mods = array_keys(esdrive_modulos());
    $todos = array_fill_keys($mods, 'gestionar');

    switch ($rol) {
        case 'administrador':
            return $todos;

        case 'editor':
            $todos['reportes']      = 'ver';
            $todos['configuracion'] = '';
            $todos['usuarios']      = '';
            return $todos;

        case 'operador':
            return [
                'dashboard' => 'ver', 'cuentas' => 'ver', 'examenes' => 'ver',
                'vehiculos' => 'gestionar', 'empleados' => 'gestionar',
                'cursos' => 'ver', 'instructores' => 'ver',
                'horarios' => 'gestionar', 'reportes' => '',
                'configuracion' => '', 'usuarios' => '',
            ];

        case 'auditor':
        default:
            $todos = array_fill_keys($mods, 'ver');
            $todos['configuracion'] = '';
            $todos['usuarios']      = '';
            return $todos;
    }
}

/** Usuario autenticado (cargado desde la BD, cacheado por request) */
function esdrive_usuario(): ?array
{
    static $cache = false;
    if ($cache !== false) { return $cache; }

    $id = (int) ($_SESSION['esdrive_user_id'] ?? 0);
    if (!($id > 0)) { return $cache = null; }

    try {
        $st = db()->prepare('SELECT * FROM usuarios WHERE id = ? AND activo = 1');
        $st->execute([$id]);
        $u = $st->fetch() ?: null;
    } catch (Exception $e) {
        $u = null;
    }
    return $cache = $u;
}

/** Permisos del usuario como array modulo => nivel */
function esdrive_permisos(?array $u = null): array
{
    $u = $u ?? esdrive_usuario();
    if (!$u) { return []; }
    if (($u['rol'] ?? '') === 'administrador') {
        return array_fill_keys(array_keys(esdrive_modulos()), 'gestionar');
    }
    $p = json_decode($u['permisos'] ?? '[]', true);
    return is_array($p) ? $p : [];
}

/**
 * ¿Tiene acceso a un modulo?
 * @param string $mod    id del modulo
 * @param string $nivel  'ver' | 'gestionar'
 */
function esdrive_puede(string $mod, string $nivel = 'ver'): bool
{
    $u = esdrive_usuario();
    if (!$u) { return false; }
    if (($u['rol'] ?? '') === 'administrador') { return true; }

    $p = esdrive_permisos($u);
    $val = $p[$mod] ?? '';
    if ($nivel === 'gestionar') { return $val === 'gestionar'; }
    return $val === 'ver' || $val === 'gestionar';
}

/** Nombre visible del rol */
function esdrive_rol_label(string $rol): string
{
    $map = [
        'administrador' => 'Administrador',
        'editor'        => 'Editor',
        'operador'      => 'Operador',
        'auditor'       => 'Auditor',
    ];
    return $map[$rol] ?? ucfirst($rol);
}

/** Iniciales para el avatar del topbar */
function esdrive_iniciales(string $nombre): string
{
    $partes = preg_split('/\s+/', trim($nombre));
    $ini = substr($partes[0] ?? '', 0, 1);
    if (count($partes) > 1) { $ini .= substr($partes[count($partes) - 1], 0, 1); }
    return strtoupper($ini);
}
