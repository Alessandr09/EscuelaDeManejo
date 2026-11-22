<?php
/**
 * cfg.php - Configuracion editable del sitio (tabla `config`)
 *
 * Expone la data que se edita desde Configuracion del panel:
 *   - esdrive_cfg()          -> valor de una clave (con defecto)
 *   - esdrive_guias()        -> videos de la seccion Guias (pagina principal)
 *   - esdrive_redes()        -> tarjetas de redes sociales (pagina principal)
 *   - esdrive_redes_links()  -> links planos de redes (footer / reuse)
 *   - esdrive_favicon()      -> icono (favicon) del sitio
 *
 * Los defaults viven aqui para que la vista publica y el panel compartan
 * la misma fuente de verdad.
 */
require_once __DIR__ . '/db.php';

/** Lee una clave de la tabla `config`; devuelve $defecto si esta vacia o falta. */
function esdrive_cfg(string $clave, string $defecto = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (db()->query('SELECT clave, valor FROM config') as $r) {
                $cache[$r['clave']] = (string) $r['valor'];
            }
        } catch (Exception $e) {
            $cache = [];
        }
    }
    $v = $cache[$clave] ?? '';
    return $v !== '' ? $v : $defecto;
}

/** Campos de cada guia, en el orden en que se pintan en la tarjeta. */
function esdrive_guias_campos(): array
{
    return ['icono', 'categoria', 'titulo', 'duracion', 'video', 'poster', 'tip'];
}

/** Guias por defecto de la pagina principal (seccion "Guias para el volante"). */
function esdrive_guias_defecto(): array
{
    $g = function (string $icono, string $cat, string $tit, string $dur, string $video, string $poster, string $tip): array {
        return [
            'icono'     => $icono,
            'categoria' => $cat,
            'titulo'    => $tit,
            'duracion'  => $dur,
            'video'     => $video,
            'poster'    => $poster,
            'tip'       => $tip,
        ];
    };
    return [
        $g('fa-steering-wheel', 'Primeros pasos', 'Tu puesto de mando', '0:10',
           'https://videos.pexels.com/video-files/10579034/10579034-uhd_2560_1440_25fps.mp4',
           'https://images.pexels.com/videos/10579034/pexels-photo-10579034.jpeg',
           'Ajusta el asiento y los espejos, abrochate el cinturon y revisa el tablero antes de arrancar.'),
        $g('fa-square-parking', 'Estacionamiento', 'Como estacionarse con seguridad', '0:15',
           'https://videos.pexels.com/video-files/9100884/9100884-uhd_2560_1440_30fps.mp4',
           'https://images.pexels.com/videos/9100884/car-car-park-parking-parking-area-9100884.jpeg',
           'Enciende la direccional, busca un espacio completo y calcula el angulo con ayuda de los espejos.'),
        $g('fa-route', 'Circulacion', 'Cambios de carril sin sorpresas', '0:22',
           'https://videos.pexels.com/video-files/20589891/20589891-hd_1920_1080_60fps.mp4',
           'https://images.pexels.com/videos/20589891/pexels-photo-20589891.jpeg',
           'Espejo, direccional y angulo muerto: cambia de carril solo cuando tengas espacio suficiente.'),
        $g('fa-moon', 'Conduccion nocturna', 'Manejar de noche', '0:09',
           'https://videos.pexels.com/video-files/28928755/12519833_2560_1440_30fps.mp4',
           'https://images.pexels.com/videos/28928755/drive-car-highway-night-timelapse-28928755.jpeg',
           'Usa las luces altas solo sin trafico de frente y mantén una distancia mayor con el vehiculo que va adelante.'),
        $g('fa-cloud-rain', 'Mal tiempo', 'Conduccion bajo lluvia', '0:17',
           'https://videos.pexels.com/video-files/13395797/13395797-hd_1280_720_25fps.mp4',
           'https://images.pexels.com/videos/13395797/ambiance-inside-the-car-moving-rain-13395797.jpeg',
           'Reduce la velocidad, enciende las luces y aumenta la distancia de frenado: el pavimento resbala mas.'),
        $g('fa-graduation-cap', 'Examen practico', 'El dia de tu evaluacion', '0:08',
           'https://videos.pexels.com/video-files/8630309/8630309-uhd_2560_1440_25fps.mp4',
           'https://images.pexels.com/videos/8630309/pexels-photo-8630309.jpeg',
           'Respira, escucha bien las indicaciones y prioriza siempre la seguridad por encima de la rapidez.'),
    ];
}

/**
 * Guias efectivas: defaults + overrides guardados en config (clave `guias`, JSON).
 * Un campo vacio en el JSON conserva el valor por defecto.
 */
function esdrive_guias(): array
{
    $def = esdrive_guias_defecto();
    $json = esdrive_cfg('guias', '');
    if ($json === '') { return $def; }
    $datos = json_decode($json, true);
    if (!is_array($datos)) { return $def; }

    foreach ($def as $i => $g) {
        if (!isset($datos[$i]) || !is_array($datos[$i])) { continue; }
        foreach ($datos[$i] as $k => $v) {
            if (array_key_exists($k, $g) && is_string($v) && trim($v) !== '') {
                $def[$i][$k] = trim($v);
            }
        }
    }
    return $def;
}

/** Links planos de redes (para footer y reuse). '#siguenos' = sin configurar. */
function esdrive_redes_links(): array
{
    $wa   = preg_replace('/\D/', '', esdrive_cfg('whatsapp', '50370000000'));
    $mail = esdrive_cfg('email', 'contacto@esdrive.com');
    return [
        'facebook'  => esdrive_cfg('redes_facebook', '#siguenos'),
        'instagram' => esdrive_cfg('redes_instagram', '#siguenos'),
        'tiktok'    => esdrive_cfg('redes_tiktok', '#siguenos'),
        'whatsapp'  => 'https://wa.me/' . $wa,
        'email'     => 'mailto:' . $mail,
    ];
}

/** Tarjetas de redes para la seccion "Siguenos" de la pagina principal. */
function esdrive_redes(): array
{
    $links = esdrive_redes_links();
    $wa    = preg_replace('/\D/', '', esdrive_cfg('whatsapp', '50370000000'));
    $mail  = esdrive_cfg('email', 'contacto@esdrive.com');

    $detalle = function (string $url, string $porDefecto): string {
        if ($url === '#siguenos' || $url === '') { return $porDefecto; }
        return preg_replace('#^https?://(www\.)?#', '', rtrim($url, '/'));
    };

    return [
        ['fab fa-tiktok',    'TikTok',            $detalle($links['tiktok'], '@esdrive'), $links['tiktok'],    $links['tiktok'] !== '#siguenos'],
        ['fab fa-instagram', 'Instagram',         $detalle($links['instagram'], '@esdrive'), $links['instagram'], $links['instagram'] !== '#siguenos'],
        ['fab fa-facebook-f', 'Facebook',         $detalle($links['facebook'], 'ESDRIVE Escuela de Manejo'), $links['facebook'], $links['facebook'] !== '#siguenos'],
        ['fas fa-envelope',  'Correo electronico', $mail, 'mailto:' . $mail, false],
        ['fab fa-whatsapp',  'WhatsApp',          '+' . $wa, 'https://wa.me/' . $wa, true],
    ];
}

/** Icono (favicon) del sitio: el configurado o el logo por defecto. */
function esdrive_favicon(): string
{
    return esdrive_cfg('icono', BASE_URL . '/assets/img/logo.svg');
}

/** Logo del sitio (header / footer / sidebar): el configurado o el default. */
function esdrive_logo(): string
{
    return esdrive_cfg('logo', BASE_URL . '/assets/img/logo.svg');
}
