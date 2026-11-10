<?php
require_once __DIR__ . '/php/base.php';
<<<<<<< HEAD
require_once __DIR__ . '/php/auth.php';
session_start();

$auth  = $_SESSION['esdrive_auth'] ?? false;
$vista = $_GET['v'] ?? 'dashboard';
$error = '';
=======
session_start();

// Autenticacion basica de demo
$auth = $_SESSION['esdrive_auth'] ?? false;
$vista = $_GET['v'] ?? 'dashboard';
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc

if ($vista === 'login') { $auth = false; }

// Logout
if ($vista === 'logout') {
    session_destroy();
    header('Location: ' . BASE_URL . '/admin.php?v=login');
    exit;
}

<<<<<<< HEAD
/* ============ LOGIN (usuarios de la BD) ============ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'login') {
    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = (string) ($_POST['clave'] ?? '');
    try {
        $st = db()->prepare('SELECT * FROM usuarios WHERE usuario = ? AND activo = 1');
        $st->execute([$usuario]);
        $u = $st->fetch();
        if ($u && password_verify($clave, $u['clave'])) {
            session_regenerate_id(true);
            $_SESSION['esdrive_auth']     = true;
            $_SESSION['esdrive_user_id']  = (int) $u['id'];
            header('Location: ' . BASE_URL . '/admin.php?v=dashboard');
            exit;
        }
    } catch (Exception $e) {
        $error = 'Error al conectar con la base de datos';
    }
    if ($error === '') { $error = 'Usuario o contrasena incorrectos'; }
=======
// Login (demo: admin / 1234)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'login') {
    if ($_POST['usuario'] === 'admin' && $_POST['clave'] === '1234') {
        $_SESSION['esdrive_auth'] = true;
        header('Location: ' . BASE_URL . '/admin.php?v=dashboard');
        exit;
    } else {
        $error = 'Credenciales incorrectas';
    }
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
}

// Proteccion de vistas
if (!$auth) {
    $vista = 'login';
}
<<<<<<< HEAD

$usuarioActual = null;
if ($auth) {
    $usuarioActual = esdrive_usuario();
    if (!$usuarioActual) {
        session_destroy();
        header('Location: ' . BASE_URL . '/admin.php?v=login');
        exit;
    }
    // Si no tiene acceso al modulo solicitado, lo mandamos al dashboard
    if (!esdrive_puede($vista, 'ver')) {
        header('Location: ' . BASE_URL . '/admin.php?v=dashboard&flash=denegado');
        exit;
    }
}

/* ============ ACCIONES POST (con control de permisos) ============ */
if ($auth && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    // accion => [modulo requerido, nivel]
    $mapaAcciones = [
        'guardar_vehiculo'   => ['vehiculos',     'gestionar'],
        'eliminar_vehiculo'  => ['vehiculos',     'gestionar'],
        'guardar_empleado'   => ['empleados',     'gestionar'],
        'eliminar_empleado'  => ['empleados',     'gestionar'],
        'guardar_alumno'     => ['cuentas',       'gestionar'],
        'eliminar_alumno'    => ['cuentas',       'gestionar'],
        'guardar_examen'     => ['examenes',      'gestionar'],
        'eliminar_examen'    => ['examenes',      'gestionar'],
        'guardar_curso'      => ['cursos',        'gestionar'],
        'eliminar_curso'     => ['cursos',        'gestionar'],
        'guardar_horario'    => ['horarios',      'gestionar'],
        'eliminar_horario'   => ['horarios',      'gestionar'],
        'guardar_usuario'    => ['usuarios',      'gestionar'],
        'eliminar_usuario'   => ['usuarios',      'gestionar'],
        'guardar_config'     => ['configuracion', 'gestionar'],
    ];

    $vistaRetorno = 'dashboard';
    if (isset($mapaAcciones[$accion])) {
        [$modReq, $nivReq] = $mapaAcciones[$accion];
        $vistaRetorno = $modReq === 'configuracion' || $modReq === 'usuarios' || $modReq === 'reportes'
            ? $modReq : $modReq;
        if (!esdrive_puede($modReq, $nivReq)) {
            header('Location: ' . BASE_URL . '/admin.php?v=dashboard&flash=denegado');
            exit;
        }
    }

    $go = function ($msg, $v = null) use ($vistaRetorno) {
        header('Location: ' . BASE_URL . '/admin.php?v=' . ($v ?: $vistaRetorno) . '&flash=' . $msg);
        exit;
    };

    // Subida de imagen generica (vehiculos y empleados)
    $subirImagen = function ($campo, $prefijo) {
        if (empty($_FILES[$campo]['name']) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) { return ''; }
        $permitidos = ['jpg' => 'jpg', 'jpeg' => 'jpg', 'png' => 'png', 'webp' => 'webp', 'gif' => 'gif'];
        $ext  = strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION));
        $tam  = $_FILES[$campo]['size'];
        $mime = function_exists('mime_content_type') ? mime_content_type($_FILES[$campo]['tmp_name']) : '';
        $esImagen = in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true);
        if (!isset($permitidos[$ext]) || !$esImagen || $tam > 5 * 1024 * 1024) { return ''; }
        $dir = __DIR__ . '/assets/img/uploads';
        if (!is_dir($dir)) { mkdir($dir, 0775, true); }
        $nuevo = $prefijo . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $permitidos[$ext];
        if (move_uploaded_file($_FILES[$campo]['tmp_name'], $dir . '/' . $nuevo)) {
            return BASE_URL . '/assets/img/uploads/' . $nuevo;
        }
        return '';
    };

    /* ---------- VEHICULOS ---------- */
    if ($accion === 'guardar_vehiculo') {
        $id       = (int) ($_POST['id'] ?? 0);
        $nombre   = trim($_POST['nombre'] ?? '');
        $placa    = trim($_POST['placa'] ?? '');
        $tipo     = trim($_POST['tipo'] ?? '');
        $estado   = ($_POST['estado'] ?? 'disponible') === 'en_uso' ? 'en_uso' : 'disponible';
        $desdeRaw = trim($_POST['disponible_desde'] ?? '');
        $desde    = $desdeRaw ? str_replace('T', ' ', $desdeRaw) . ':00' : null;
        $usoRaw   = trim($_POST['proximo_uso'] ?? '');
        $proximo  = $usoRaw ? str_replace('T', ' ', $usoRaw) . ':00' : null;
        $empId    = (int) ($_POST['empleado_id'] ?? 0) ?: null;
        $tUso     = trim($_POST['tiempo_uso'] ?? '');
        $tLibre   = trim($_POST['tiempo_libre'] ?? '');

        if ($nombre === '') { $go('error'); }

        $imagenUrl = $subirImagen('imagen', 'veh');
        $pdo = db();
        if ($id > 0) {
            if ($imagenUrl !== '') {
                $st = $pdo->prepare('UPDATE vehiculos SET nombre=?, placa=?, tipo=?, estado=?, disponible_desde=?, proximo_uso=?, empleado_id=?, tiempo_uso=?, tiempo_libre=?, imagen=? WHERE id=?');
                $st->execute([$nombre, $placa, $tipo, $estado, $desde, $proximo, $empId, $tUso, $tLibre, $imagenUrl, $id]);
            } else {
                $st = $pdo->prepare('UPDATE vehiculos SET nombre=?, placa=?, tipo=?, estado=?, disponible_desde=?, proximo_uso=?, empleado_id=?, tiempo_uso=?, tiempo_libre=? WHERE id=?');
                $st->execute([$nombre, $placa, $tipo, $estado, $desde, $proximo, $empId, $tUso, $tLibre, $id]);
            }
        } else {
            $st = $pdo->prepare('INSERT INTO vehiculos (nombre, placa, tipo, estado, disponible_desde, proximo_uso, empleado_id, tiempo_uso, tiempo_libre, imagen) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $st->execute([$nombre, $placa, $tipo, $estado, $desde, $proximo, $empId, $tUso, $tLibre, $imagenUrl]);
        }
        $go('ok', 'vehiculos');
    }

    if ($accion === 'eliminar_vehiculo') {
        db()->prepare('DELETE FROM vehiculos WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        $go('ok', 'vehiculos');
    }

    /* ---------- EMPLEADOS / INSTRUCTORES ---------- */
    if ($accion === 'guardar_empleado') {
        $id     = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $cargo  = trim($_POST['cargo'] ?? '');
        $tel    = trim($_POST['telefono'] ?? '');
        $mail   = trim($_POST['email'] ?? '');

        if ($nombre === '') { $go('error', 'empleados'); }

        $imagenUrl = $subirImagen('imagen', 'emp');
        $pdo = db();
        if ($id > 0) {
            if ($imagenUrl !== '') {
                $st = $pdo->prepare('UPDATE empleados SET nombre=?, cargo=?, telefono=?, email=?, imagen=? WHERE id=?');
                $st->execute([$nombre, $cargo, $tel, $mail, $imagenUrl, $id]);
            } else {
                $st = $pdo->prepare('UPDATE empleados SET nombre=?, cargo=?, telefono=?, email=? WHERE id=?');
                $st->execute([$nombre, $cargo, $tel, $mail, $id]);
            }
        } else {
            $st = $pdo->prepare('INSERT INTO empleados (nombre, cargo, telefono, email, imagen) VALUES (?,?,?,?,?)');
            $st->execute([$nombre, $cargo, $tel, $mail, $imagenUrl]);
        }
        $go('ok', 'empleados');
    }

    if ($accion === 'eliminar_empleado') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo = db();
        $pdo->prepare('UPDATE vehiculos SET empleado_id = NULL WHERE empleado_id = ?')->execute([$id]);
        $pdo->prepare('UPDATE horarios SET empleado_id = NULL WHERE empleado_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM empleados WHERE id = ?')->execute([$id]);
        $go('ok', 'empleados');
    }

    /* ---------- CUENTAS (ALUMNOS) ---------- */
    if ($accion === 'guardar_alumno') {
        $id     = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $mail   = trim($_POST['email'] ?? '');
        $tel    = trim($_POST['telefono'] ?? '');
        $curso  = trim($_POST['curso'] ?? '');
        $estado = trim($_POST['estado'] ?? 'Activo');
        if ($nombre === '') { $go('error', 'cuentas'); }

        $pdo = db();
        if ($id > 0) {
            $st = $pdo->prepare('UPDATE alumnos SET nombre=?, email=?, telefono=?, curso=?, estado=? WHERE id=?');
            $st->execute([$nombre, $mail, $tel, $curso, $estado, $id]);
        } else {
            $st = $pdo->prepare('INSERT INTO alumnos (nombre, email, telefono, curso, estado) VALUES (?,?,?,?,?)');
            $st->execute([$nombre, $mail, $tel, $curso, $estado]);
        }
        $go('ok', 'cuentas');
    }

    if ($accion === 'eliminar_alumno') {
        db()->prepare('DELETE FROM alumnos WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        $go('ok', 'cuentas');
    }

    /* ---------- EXAMENES ---------- */
    if ($accion === 'guardar_examen') {
        $id     = (int) ($_POST['id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $curso  = trim($_POST['curso'] ?? '');
        $preg   = (int) ($_POST['preguntas'] ?? 0);
        $dur    = trim($_POST['duracion'] ?? '');
        $fecha  = trim($_POST['fecha'] ?? '');
        $fecha  = $fecha ? date('Y-m-d', strtotime(str_replace('T', ' ', $fecha))) : null;
        $estado = trim($_POST['estado'] ?? 'Borrador');
        $desc   = $_POST['descripcion'] ?? '';
        if ($titulo === '') { $go('error', 'examenes'); }

        $pdo = db();
        if ($id > 0) {
            $st = $pdo->prepare('UPDATE examenes SET titulo=?, curso=?, preguntas=?, duracion=?, fecha=?, estado=?, descripcion=? WHERE id=?');
            $st->execute([$titulo, $curso, $preg, $dur, $fecha, $estado, $desc, $id]);
        } else {
            $st = $pdo->prepare('INSERT INTO examenes (titulo, curso, preguntas, duracion, fecha, estado, descripcion) VALUES (?,?,?,?,?,?,?)');
            $st->execute([$titulo, $curso, $preg, $dur, $fecha, $estado, $desc]);
        }
        $go('ok', 'examenes');
    }

    if ($accion === 'eliminar_examen') {
        db()->prepare('DELETE FROM examenes WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        $go('ok', 'examenes');
    }

    /* ---------- CURSOS ---------- */
    if ($accion === 'guardar_curso') {
        $id     = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $codigo = trim($_POST['codigo'] ?? '');
        $precio = (float) ($_POST['precio'] ?? 0);
        $dur    = trim($_POST['duracion'] ?? '');
        $estado = trim($_POST['estado'] ?? 'Activo');
        $desc   = trim($_POST['descripcion'] ?? '');
        if ($nombre === '') { $go('error', 'cursos'); }

        $pdo = db();
        if ($id > 0) {
            $st = $pdo->prepare('UPDATE cursos SET nombre=?, codigo=?, precio=?, duracion=?, estado=?, descripcion=? WHERE id=?');
            $st->execute([$nombre, $codigo, $precio, $dur, $estado, $desc, $id]);
        } else {
            $st = $pdo->prepare('INSERT INTO cursos (nombre, codigo, precio, duracion, estado, descripcion) VALUES (?,?,?,?,?,?)');
            $st->execute([$nombre, $codigo, $precio, $dur, $estado, $desc]);
        }
        $go('ok', 'cursos');
    }

    if ($accion === 'eliminar_curso') {
        $pdo = db();
        $pdo->prepare('UPDATE horarios SET curso_id = NULL WHERE curso_id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        $pdo->prepare('DELETE FROM cursos WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        $go('ok', 'cursos');
    }

    /* ---------- HORARIOS ---------- */
    if ($accion === 'guardar_horario') {
        $id     = (int) ($_POST['id'] ?? 0);
        $curso  = (int) ($_POST['curso_id'] ?? 0) ?: null;
        $emp    = (int) ($_POST['empleado_id'] ?? 0) ?: null;
        $dia    = trim($_POST['dia'] ?? '');
        $inicio = trim($_POST['hora_inicio'] ?? '');
        $fin    = trim($_POST['hora_fin'] ?? '');
        $cupo   = (int) ($_POST['cupo'] ?? 0);
        if ($dia === '' || $inicio === '') { $go('error', 'horarios'); }

        $pdo = db();
        if ($id > 0) {
            $st = $pdo->prepare('UPDATE horarios SET curso_id=?, empleado_id=?, dia=?, hora_inicio=?, hora_fin=?, cupo=? WHERE id=?');
            $st->execute([$curso, $emp, $dia, $inicio, $fin, $cupo, $id]);
        } else {
            $st = $pdo->prepare('INSERT INTO horarios (curso_id, empleado_id, dia, hora_inicio, hora_fin, cupo) VALUES (?,?,?,?,?,?)');
            $st->execute([$curso, $emp, $dia, $inicio, $fin, $cupo]);
        }
        $go('ok', 'horarios');
    }

    if ($accion === 'eliminar_horario') {
        db()->prepare('DELETE FROM horarios WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        $go('ok', 'horarios');
    }

    /* ---------- USUARIOS DEL SISTEMA ---------- */
    if ($accion === 'guardar_usuario') {
        $id     = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $usu    = strtolower(trim($_POST['usuario'] ?? ''));
        $clave  = (string) ($_POST['clave'] ?? '');
        $rol    = trim($_POST['rol'] ?? 'operador');
        $activo = isset($_POST['activo']) ? 1 : 0;
        $niveles = $_POST['permiso'] ?? [];
        if (!isset(esdrive_roles()[$rol])) { $rol = 'operador'; }

        if ($nombre === '' || $usu === '') { $go('error', 'usuarios'); }
        if ($id === 0 && strlen($clave) < 4) { $go('error_clave', 'usuarios'); }

        // Permisos por modulo (si el rol es administrador se guarda igual, pero da todo)
        $permisos = [];
        foreach (esdrive_modulos() as $mod => $lbl) {
            $n = $niveles[$mod] ?? '';
            $permisos[$mod] = in_array($n, ['ver', 'gestionar'], true) ? $n : '';
        }
        if ($rol === 'administrador') { $permisos = esdrive_permisos_default('administrador'); }

        $pdo = db();
        // Que el usuario no se quite el acceso a si mismo por accidente
        if ($id === (int) ($_SESSION['esdrive_user_id'] ?? 0)) { $activo = 1; }

        if ($id > 0) {
            if ($clave !== '') {
                $st = $pdo->prepare('UPDATE usuarios SET nombre=?, usuario=?, clave=?, rol=?, permisos=?, activo=? WHERE id=?');
                $st->execute([$nombre, $usu, password_hash($clave, PASSWORD_DEFAULT), $rol, json_encode($permisos), $activo, $id]);
            } else {
                $st = $pdo->prepare('UPDATE usuarios SET nombre=?, usuario=?, rol=?, permisos=?, activo=? WHERE id=?');
                $st->execute([$nombre, $usu, $rol, json_encode($permisos), $activo, $id]);
            }
        } else {
            $st = $pdo->prepare('INSERT INTO usuarios (nombre, usuario, clave, rol, permisos, activo) VALUES (?,?,?,?,?,?)');
            $st->execute([$nombre, $usu, password_hash($clave, PASSWORD_DEFAULT), $rol, json_encode($permisos), $activo]);
        }
        $go('ok', 'usuarios');
    }

    if ($accion === 'eliminar_usuario') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === (int) ($_SESSION['esdrive_user_id'] ?? 0)) {
            $go('error_propio', 'usuarios');
        }
        db()->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]);
        $go('ok', 'usuarios');
    }

    /* ---------- CONFIGURACION ---------- */
    if ($accion === 'guardar_config') {
        $campos = ['nombre', 'telefono', 'email', 'direccion', 'horario', 'whatsapp'];
        $pdo = db();
        $st = $pdo->prepare('INSERT INTO `config` (clave, valor) VALUES (?,?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)');
        foreach ($campos as $c) {
            $st->execute([$c, trim($_POST[$c] ?? '')]);
        }
        $go('ok', 'configuracion');
    }
}

/* ============ MAPA DE VISTAS ============ */
$mapaVistas = ['dashboard','cuentas','examenes','vehiculos','empleados','cursos','instructores','horarios','reportes','configuracion','usuarios'];
// Ojo: no pisar 'login' (si no, un usuario sin sesion veria el dashboard)
if ($vista !== 'login' && !in_array($vista, $mapaVistas, true)) { $vista = 'dashboard'; }
=======
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
?>
<!doctype html>
<html lang="es">
  <head><?php include __DIR__ . '/php/enlaces_admin.php'; ?></head>
  <body class="admin-body">
    <?php if ($vista === 'login'): ?>
        <?php include __DIR__ . '/modules/views/admin/login.php'; ?>
    <?php else: ?>
      <div class="app-bg"></div>
      <div class="admin-layout" id="adminLayout">
        <?php include __DIR__ . '/modules/views/layouts/admin_sidebar.php'; ?>
        <main class="admin-main">
          <?php include __DIR__ . '/modules/views/layouts/admin_topbar.php'; ?>
          <div class="admin-content">
<<<<<<< HEAD
            <?php include __DIR__ . '/modules/views/admin/' . $vista . '.php'; ?>
          </div>
        </main>
      </div>
      <script src="<?= BASE_URL ?>/assets/js/admin.js?v=7"></script>
    <?php endif; ?>
  </body>
</html>
=======
            <?php
            $mapa = ['dashboard','cuentas','examenes'];
            $vista = in_array($vista, $mapa) ? $vista : 'dashboard';
            include __DIR__ . '/modules/views/admin/' . $vista . '.php';
            ?>
          </div>
        </main>
      </div>
      <script src="<?= BASE_URL ?>/assets/js/admin.js?v=2"></script>
    <?php endif; ?>
  </body>
</html>
>>>>>>> 2f49e7418e273fb5b505fe2b836bc5d281f7ddcc
