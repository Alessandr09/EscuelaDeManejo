<?php
/**
 * config/examen_funciones.php - Logica compartida del examen aprobatorio.
 *
 * Usada por examen.php (alumno) y examen-alumnos.php (instructor).
 * La tabla donde se guardan las notas es `examen_alumnos` (ver config/examen.sql).
 */

/** Duracion maxima del examen, en minutos, desde que se habilita. */
define('EXAMEN_VENTANA_MINUTOS', 30);

/** Porcentaje minimo de aciertos para aprobar. */
define('EXAMEN_UMBRAL', 80);

/** Estado en el que se encuentra el examen de un alumno. */
function examen_estado_ventana(array $alumno): array
{
    $ahora = time();
    $desde = strtotime($alumno['habilitado_desde']);
    $vence = strtotime($alumno['vence']);

    if (in_array($alumno['estado'], ['aprobado', 'reprobado'], true)) {
        return ['situacion' => 'finalizado', 'restante' => 0];
    }
    if ($ahora > $vence) {
        return ['situacion' => 'expirado', 'restante' => 0];
    }
    if ($ahora < $desde) {
        return ['situacion' => 'espera', 'restante' => $desde - $ahora];
    }
    return ['situacion' => 'disponible', 'restante' => $vence - $ahora];
}

/** Busca un alumno por su codigo de acceso. */
function examen_alumno_por_codigo(PDO $pdo, string $codigo): ?array
{
    $codigo = trim($codigo);
    if ($codigo === '') {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM examen_alumnos WHERE codigo = ? LIMIT 1');
    $stmt->execute([$codigo]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

/** Busca un alumno por su id. */
function examen_alumno_por_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM examen_alumnos WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

/** Genera un codigo de acceso legible para el alumno. */
function examen_generar_codigo(PDO $pdo): string
{
    $letras = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    do {
        $codigo = '';
        for ($i = 0; $i < 8; $i++) {
            $codigo .= $letras[random_int(0, strlen($letras) - 1)];
        }
        $stmt = $pdo->prepare('SELECT id FROM examen_alumnos WHERE codigo = ?');
        $stmt->execute([$codigo]);
        $existe = (bool) $stmt->fetch();
    } while ($existe);

    return $codigo;
}

/** Calcula la ventana de tiempo de un alumno (habilitado -> vence). */
function examen_ventana(string $habilitadoDesde): array
{
    $desde = strtotime($habilitadoDesde);
    if (!$desde) {
        $desde = time();
    }
    return [
        'habilitado_desde' => date('Y-m-d H:i:s', $desde),
        'vence'            => date('Y-m-d H:i:s', $desde + EXAMEN_VENTANA_MINUTOS * 60),
    ];
}

/**
 * Registra la respuesta de la pregunta actual.
 *
 * Reglas del examen aprobatorio:
 *  - solo se acepta la pregunta que el servidor tiene abierta (no hay retroceso);
 *  - si el tiempo ya vencio, el examen se cierra automaticamente.
 *
 * @return array ['ok' => bool, 'error' => string, 'restante' => int, 'indice' => int]
 */
function examen_guardar_respuesta(PDO $pdo, array $alumno, int $indice, int $opcion, array $preguntas): array
{
    $ventana = examen_estado_ventana($alumno);
    if ($ventana['situacion'] === 'expirado') {
        examen_finalizar($pdo, $alumno, $preguntas);
        return ['ok' => false, 'error' => 'expirado', 'restante' => 0, 'indice' => (int) $alumno['pregunta_actual']];
    }
    if ($ventana['situacion'] !== 'disponible') {
        return ['ok' => false, 'error' => 'no_disponible', 'restante' => $ventana['restante'], 'indice' => (int) $alumno['pregunta_actual']];
    }

    $actual = (int) $alumno['pregunta_actual'];
    if ($indice !== $actual) {
        // No se permite volver a una pregunta anterior ni saltarse el orden.
        return ['ok' => false, 'error' => 'pregunta_bloqueada', 'restante' => $ventana['restante'], 'indice' => $actual];
    }
    if (!isset($preguntas[$indice]['opciones'][$opcion])) {
        return ['ok' => false, 'error' => 'opcion_invalida', 'restante' => $ventana['restante'], 'indice' => $actual];
    }

    $respuestas = examen_decodificar_respuestas($alumno['respuestas'] ?? null);
    $respuestas[(string) $indice] = $opcion;

    $stmt = $pdo->prepare('UPDATE examen_alumnos
        SET respuestas = ?, pregunta_actual = ?, estado = ?, inicio_real = COALESCE(inicio_real, ?)
        WHERE id = ?');
    $stmt->execute([
        json_encode($respuestas),
        $indice + 1,
        'en_curso',
        date('Y-m-d H:i:s'),
        (int) $alumno['id'],
    ]);

    return [
        'ok'       => true,
        'error'    => '',
        'restante' => $ventana['restante'],
        'indice'   => $indice + 1,
    ];
}

/** Decodifica el JSON de respuestas guardadas. */
function examen_decodificar_respuestas(?string $json): array
{
    if (!$json) {
        return [];
    }
    $datos = json_decode($json, true);
    return is_array($datos) ? $datos : [];
}

/**
 * Cierra el examen: calcula la nota, la guarda en la base de datos y marca
 * al alumno como aprobado o reprobado.
 */
function examen_finalizar(PDO $pdo, array $alumno, array $preguntas): array
{
    $respuestas = examen_decodificar_respuestas($alumno['respuestas'] ?? null);
    $aciertos = 0;
    foreach ($preguntas as $i => $p) {
        if (isset($respuestas[(string) $i]) && (int) $respuestas[(string) $i] === (int) $p['respuesta']) {
            $aciertos++;
        }
    }

    $total     = count($preguntas);
    $porcentaje = $total > 0 ? round(($aciertos / $total) * 100, 2) : 0;
    $nota      = (int) round($porcentaje);
    $estado    = $nota >= EXAMEN_UMBRAL ? 'aprobado' : 'reprobado';

    $stmt = $pdo->prepare('UPDATE examen_alumnos
        SET estado = ?, aciertos = ?, total = ?, porcentaje = ?, nota = ?, terminado_en = ?
        WHERE id = ?');
    $stmt->execute([
        $estado,
        $aciertos,
        $total,
        $porcentaje,
        $nota,
        date('Y-m-d H:i:s'),
        (int) $alumno['id'],
    ]);

    return [
        'aciertos'   => $aciertos,
        'total'      => $total,
        'porcentaje' => $porcentaje,
        'nota'       => $nota,
        'estado'     => $estado,
    ];
}

/** Marca el examen como vencido si se paso el tiempo. */
function examen_marcar_expirado(PDO $pdo, array $alumno): void
{
    $stmt = $pdo->prepare("UPDATE examen_alumnos SET estado = 'expirado' WHERE id = ? AND estado IN ('pendiente','en_curso')");
    $stmt->execute([(int) $alumno['id']]);
}

/** Formatea una fecha de MySQL para mostrar. */
function examen_fecha(?string $fecha): string
{
    if (!$fecha) {
        return '—';
    }
    $ts = strtotime($fecha);
    if (!$ts) {
        return '—';
    }
    return date('d/m/Y h:i A', $ts);
}

/** Etiqueta legible del estado del examen. */
function examen_etiqueta_estado(string $estado): string
{
    $mapa = [
        'pendiente' => 'Pendiente',
        'en_curso'  => 'En curso',
        'aprobado'  => 'Aprobado',
        'reprobado' => 'Reprobado',
        'expirado'  => 'Vencido',
    ];
    return $mapa[$estado] ?? $estado;
}
