<?php require_once __DIR__ . '/../../../php/base.php'; $error = $error ?? ''; ?>
<div class="app-bg"></div>

<!-- Video de fondo + velo glass -->
<div class="login-video-wrap" aria-hidden="true">
  <video class="login-video" autoplay muted loop playsinline preload="metadata"
         poster="<?= BASE_URL ?>/assets/img/logo.svg">
    <source src="<?= BASE_URL ?>/assets/video/login-bg.mp4" type="video/mp4">
  </video>
  <div class="login-video-veil"></div>
</div>

<div class="login-wrap">
  <div class="glass login-card animate__animated animate__zoomIn">
    <div class="login-logo">
      <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="Logo">
      <h4>ES<span>DRIVE</span></h4>
      <p>Panel de administracion</p>
    </div>

    <?php if ($error): ?>
      <div class="alert" style="background:rgba(229,72,77,.16);border:1px solid rgba(229,72,77,.3);color:#f07276;border-radius:12px">
        <i class="fas fa-circle-exclamation me-2"></i><?= $error ?>
      </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <input type="hidden" name="accion" value="login">
      <div class="mb-3">
        <label class="form-label">Usuario</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-user"></i></span>
          <input type="text" name="usuario" class="form-control" placeholder="admin" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Contrasena</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" name="clave" class="form-control" placeholder="ÔÇóÔÇóÔÇóÔÇóÔÇóÔÇóÔÇóÔÇó" required>
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="recordar" style="background:rgba(255,255,255,.06);border-color:var(--glass-brd)">
          <label class="form-check-label" for="recordar" style="color:var(--text-mut);font-size:.85rem">Recordarme</label>
        </div>
        <a href="#" style="color:var(--gold);font-size:.85rem;text-decoration:none">┬┐Olvidaste tu clave?</a>
      </div>
      <button type="submit" class="btn btn-gold w-100"><i class="fas fa-right-to-bracket me-2"></i>Ingresar</button>
    </form>

    <div class="text-center mt-4">
      <a href="<?= BASE_URL ?>/index.php" style="color:var(--text-mut);font-size:.85rem;text-decoration:none">
        <i class="fas fa-arrow-left me-1"></i> Volver al sitio
      </a>
    </div>
  </div>
</div>