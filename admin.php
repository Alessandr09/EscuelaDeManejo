<?php
require_once __DIR__ . '/php/base.php';
session_start();

// Autenticacion basica de demo
$auth = $_SESSION['esdrive_auth'] ?? false;
$vista = $_GET['v'] ?? 'dashboard';

if ($vista === 'login') { $auth = false; }

// Logout
if ($vista === 'logout') {
    session_destroy();
    header('Location: ' . BASE_URL . '/admin.php?v=login');
    exit;
}

// Login (demo: admin / 1234)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'login') {
    if ($_POST['usuario'] === 'admin' && $_POST['clave'] === '1234') {
        $_SESSION['esdrive_auth'] = true;
        header('Location: ' . BASE_URL . '/admin.php?v=dashboard');
        exit;
    } else {
        $error = 'Credenciales incorrectas';
    }
}

// Proteccion de vistas
if (!$auth) {
    $vista = 'login';
}
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
            <?php
            $mapa = ['dashboard','cuentas','examenes'];
            $vista = in_array($vista, $mapa) ? $vista : 'dashboard';
            include __DIR__ . '/modules/views/admin/' . $vista . '.php';
            ?>
          </div>
        </main>
      </div>
      <script src="<?= BASE_URL ?>/assets/js/admin.js?v=3"></script>
    <?php endif; ?>
  </body>
</html>