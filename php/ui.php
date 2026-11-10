<?php
/**
 * ui.php - Bloques de UI reutilizables para las vistas del panel
 */
require_once __DIR__ . '/auth.php';

/** Mensajes flash (ok / error / denegado / avisos) */
function esdrive_flash(): string
{
    $f = $_GET['flash'] ?? '';
    if ($f === '') { return ''; }
    $map = [
        'ok'          => ['alert-ok', 'fa-circle-check',        'Operacion realizada correctamente.'],
        'error'       => ['',         'fa-circle-exclamation',  'No se pudo completar la operacion.'],
        'error_clave' => ['',         'fa-circle-exclamation',  'La contrasena debe tener al menos 4 caracteres.'],
        'error_propio'=> ['',         'fa-circle-exclamation',  'No puedes eliminar tu propia cuenta.'],
        'denegado'    => ['',         'fa-lock',                'No tienes permiso para acceder a ese modulo.'],
        'exportado'   => ['alert-ok', 'fa-file-csv',            'Reporte exportado correctamente.'],
    ];
    if (!isset($map[$f])) { return ''; }
    [$cls, $icono, $texto] = $map[$f];
    return '<div class="alert ' . $cls . '"><i class="fas ' . $icono . ' me-2"></i>' . $texto . '</div>';
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
    if (in_array($e, ['inactivo', 'inactiva', 'cancelado', 'rechazado'], true)) {
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
