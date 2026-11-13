<?php
/**
 * ui.php - Bloques de UI reutilizables para las vistas del panel
 */
require_once __DIR__ . '/auth.php';

/** Filas por pagina que muestran las tablas del panel (el resto se pagina) */
if (!defined('ESDRIVE_POR_PAGINA')) { define('ESDRIVE_POR_PAGINA', 5); }

/**
 * Mensajes flash del panel.
 * Devuelve un nodo oculto que assets/js/admin.js convierte en toast de
 * SweetAlert2 (arriba a la derecha) al cargar la pagina.
 */
function esdrive_flash(): string
{
    $f  = $_GET['flash'] ?? '';
    $op = $_GET['op'] ?? '';
    $toast = null;

    if ($f === 'ok') {
        if ($op === 'eliminado') {
            $toast = ['success', 'Registro eliminado', 'El registro se borro correctamente.'];
        } elseif ($op === 'creado') {
            $toast = ['success', 'Registro creado', 'El nuevo registro se guardo correctamente.'];
        } elseif ($op === 'actualizado') {
            $toast = ['success', 'Cambios guardados', 'El registro se actualizo correctamente.'];
        } else {
            $toast = ['success', 'Listo', 'Operacion realizada correctamente.'];
        }
    } elseif ($f === 'exportado') {
        $toast = ['success', 'Reporte exportado', 'El archivo CSV se descargo correctamente.'];
    } elseif ($f === 'error') {
        $toast = ['error', 'No se pudo completar', 'Revisa los datos e intenta de nuevo.'];
    } elseif ($f === 'error_clave') {
        $toast = ['error', 'Contrasena muy corta', 'Debe tener al menos 4 caracteres.'];
    } elseif ($f === 'error_propio') {
        $toast = ['warning', 'Accion no permitida', 'No puedes eliminar tu propia cuenta.'];
    } elseif ($f === 'denegado') {
        $toast = ['warning', 'Acceso denegado', 'No tienes permiso para acceder a ese modulo.'];
    }

    if (!$toast) { return ''; }
    [$icono, $titulo, $texto] = $toast;

    return '<div class="flash-toast d-none" data-flash-toast="1"'
        . ' data-icono="' . $icono . '"'
        . ' data-titulo="' . htmlspecialchars($titulo, ENT_QUOTES) . '"'
        . ' data-texto="' . htmlspecialchars($texto, ENT_QUOTES) . '"></div>';
}

/** Cabecera de vista: titulo + subtitulo + zona de badges */
function esdrive_page_head(string $titulo, string $resaltado, string $sub, string $derecha = ''): string
{
    return '
<div class="page-head d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <h3>' . htmlspecialchars($titulo) . ' <span>' . htmlspecialchars($resaltado) . '</span></h3>
    <p>' . htmlspecialchars($sub) . '</p>
  </div>
  <div class="d-flex align-items-center gap-2 flex-wrap">' . $derecha . '</div>
</div>' . esdrive_flash();
}

/** Badge de estado generico */
function esdrive_badge(string $estado): string
{
    $e = strtolower(trim($estado));
    if (in_array($e, ['activo', 'activa', 'aprobado', 'aprobada', 'disponible', 'cerrado'], true)) {
        return '<span class="badge-soft b-active"><i class="fas fa-circle-check me-1"></i>' . htmlspecialchars($estado) . '</span>';
    }
    if (in_array($e, ['pendiente', 'en curso', 'en_uso', 'programado', 'borrador'], true)) {
        return '<span class="badge-soft b-pend"><i class="fas fa-clock me-1"></i>' . htmlspecialchars($estado) . '</span>';
    }
    if (in_array($e, ['inactivo', 'inactiva', 'cancelado', 'rechazado', 'reprobado', 'reprobada', 'vencido', 'expirado'], true)) {
        return '<span class="badge-soft b-off"><i class="fas fa-circle-xmark me-1"></i>' . htmlspecialchars($estado) . '</span>';
    }
    return '<span class="badge-soft b-info"><i class="fas fa-circle-info me-1"></i>' . htmlspecialchars($estado) . '</span>';
}

/** Iniciales de un nombre */
function esdrive_ini(string $nombre): string
{
    $p = preg_split('/\s+/', trim($nombre));
    $i = strtoupper(substr($p[0] ?? '', 0, 1));
    if (count($p) > 1) { $i .= strtoupper(substr($p[count($p) - 1], 0, 1)); }
    return $i;
}

/** Colores de avatar para tablas */
function esdrive_color_avatar(string $nombre): string
{
    $colores = ['#ffd76a', '#a3b6f5', '#5fd689', '#f07276', '#b98cf0', '#7cd4e8'];
    $suma = 0;
    for ($i = 0; $i < strlen($nombre); $i++) { $suma += ord($nombre[$i]); }
    return $colores[$suma % count($colores)];
}

/** Opciones de cursos (para selects) */
function esdrive_opciones_cursos(?string $seleccionado = null): array
{
    try {
        $filas = db()->query("SELECT id, nombre FROM cursos WHERE estado <> 'Inactivo' ORDER BY nombre")->fetchAll();
    } catch (Exception $e) {
        $filas = [];
    }
    $out = [];
    foreach ($filas as $f) { $out[] = ['id' => (int) $f['id'], 'nombre' => $f['nombre']]; }
    return $out;
}

/**
 * Barra de paginacion para una tabla del panel.
 *
 * Los controles los dibuja assets/js/admin.js (esdriveTabla), que ademas
 * se encarga de aplicar el filtro de busqueda sobre todas las filas.
 *
 * @param string $tablaId  id del <table> a paginar
 * @param int    $porPagina  filas por pagina
 */
function esdrive_paginacion(string $tablaId, int $porPagina = ESDRIVE_POR_PAGINA): string
{
    return '<div class="paginacion" data-tabla="' . htmlspecialchars($tablaId) . '" data-por-pagina="' . (int) $porPagina . '">'
        . '<span class="pag-info"></span>'
        . '<div class="pag-controles"></div>'
        . '</div>';
}

