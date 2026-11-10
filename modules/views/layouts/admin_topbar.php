<?php require_once __DIR__ . '/../../../php/base.php'; ?>
<div class="scrim" id="scrim"></div>

<header class="admin-topbar">
  <div class="btn-toggle-side" id="toggleSide" title="Mostrar / ocultar menu">
    <i class="fas fa-bars"></i>
  </div>

  <div class="topbar-date">
    <i class="fas fa-calendar-day"></i>
    <span id="fechaActual">Cargando fecha...</span>
  </div>

  <div class="topbar-right">
    <button id="btnToggleTheme" style="background:none;border:none;color:var(--text-mut);cursor:pointer;font-size:1.15rem;padding:4px 8px;margin-right:6px;" title="Cambiar tema" aria-label="Cambiar tema">
      <i id="iconTheme" class="fas fa-sun"></i>
    </button>
    <div class="topbar-icon" title="Notificaciones">
      <i class="fas fa-bell"></i><span class="dot"></span>
    </div>
    <div class="topbar-user">
      <div class="topbar-avatar"><?= htmlspecialchars(esdrive_iniciales($usuarioActual['nombre'] ?? 'Admin')) ?></div>
      <div class="tu-meta">
        <div class="tu-name"><?= htmlspecialchars($usuarioActual['nombre'] ?? 'Admin') ?></div>
        <div class="tu-role"><?= htmlspecialchars(esdrive_rol_label($usuarioActual['rol'] ?? 'administrador')) ?></div>
      </div>
      <a href="<?= BASE_URL ?>/admin.php?v=logout" class="btn-act del" title="Cerrar sesion"><i class="fas fa-right-from-bracket"></i></a>
    </div>
  </div>
</header>