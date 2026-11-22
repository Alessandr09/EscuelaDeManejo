<?php require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/ui.php';
require_once __DIR__ . '/../../../php/cfg.php';

$cfg = [];
try {
    foreach (db()->query('SELECT clave, valor FROM config') as $r) { $cfg[$r['clave']] = $r['valor']; }
} catch (Exception $e) {}

$campos = [
    'nombre'     => ['Nombre del negocio', 'ESDRIVE', 'fa-school'],
    'telefono'   => ['Telefono de contacto', '+503 2222-3333', 'fa-phone'],
    'email'      => ['Correo electronico', 'contacto@esdrive.com', 'fa-envelope'],
    'direccion'  => ['Direccion', 'Av. Principal #123, San Salvador', 'fa-location-dot'],
    'horario'    => ['Horario de atencion', 'Lun a Sab: 7:00 - 18:00', 'fa-clock'],
    'whatsapp'   => ['Numero de WhatsApp (50370000000)', '50370000000', 'fa-brands fa-whatsapp'],
];

$redes = [
    'redes_facebook'  => ['Facebook', 'https://facebook.com/tu-pagina', 'fab fa-facebook-f'],
    'redes_instagram' => ['Instagram', 'https://instagram.com/tu-perfil', 'fab fa-instagram'],
    'redes_tiktok'    => ['TikTok', 'https://tiktok.com/@tu-perfil', 'fab fa-tiktok'],
];

$puede    = esdrive_puede('configuracion', 'gestionar');
$guias    = esdrive_guias();
$icono    = esdrive_favicon();
$logo     = esdrive_logo();
$defLogo  = BASE_URL . '/assets/img/logo.svg';

/** Titulo de bloque interno con linea divisoria */
$bloque = function (string $iconoFa, string $txt): string {
    return '<div class="cfg-block-title"><i class="fas ' . $iconoFa . '"></i>' . htmlspecialchars($txt) . '</div>';
};
?>
<?= esdrive_page_head('Configuracion', 'del sistema', 'Apariencia y contenido de la pagina principal, en un solo lugar.') ?>

<div class="row g-3">
  <div class="col-12">

    <!-- ================= UN SOLO PANEL: todo el formulario ================= -->
    <form method="post" enctype="multipart/form-data" class="glass panel">
      <input type="hidden" name="accion" value="guardar_config">

      <div class="panel-title d-flex justify-content-between align-items-center">
        <span><i class="fas fa-sliders"></i>Configuracion del sitio</span>
        <?php if ($puede): ?>
          <button type="submit" class="btn btn-gold btn-sm">
            <i class="fas fa-floppy-disk me-1"></i>Guardar cambios
          </button>
        <?php endif; ?>
      </div>
      <p class="text-muted mb-4" style="font-size:.82rem">
        Los cambios se aplican a la pagina principal y al icono del navegador.
        Deja un campo vacio para conservar el valor por defecto.
      </p>

      <!-- ---------- Fila 1: generales | icono/logo | redes ---------- -->
      <div class="row g-4">
        <!-- Datos generales -->
        <div class="col-lg-5">
          <?= $bloque('fa-sliders', 'Datos generales') ?>
          <div class="row g-3">
            <?php foreach ($campos as $clave => [$lbl, $ph, $iconoFa]): ?>
            <div class="col-sm-6">
              <label class="form-label"><?= htmlspecialchars($lbl) ?></label>
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas <?= $iconoFa ?>"></i></span>
                <input type="text" name="<?= $clave ?>" class="form-control" placeholder="<?= htmlspecialchars($ph) ?>"
                       value="<?= htmlspecialchars($cfg[$clave] ?? '') ?>" <?= $puede ? '' : 'readonly' ?>>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Icono y logo -->
        <div class="col-lg-4">
          <?= $bloque('fa-image', 'Icono y logo') ?>
          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label">Icono (pestana)</label>
              <div class="d-flex align-items-center gap-2">
                <img src="<?= htmlspecialchars($icono) ?>" alt="Icono actual" class="cfg-preview">
                <input type="file" name="icono" class="form-control form-control-sm" accept="image/*" <?= $puede ? '' : 'disabled' ?>>
              </div>
            </div>
            <div class="col-sm-6">
              <label class="form-label">Logo (navegacion)</label>
              <div class="d-flex align-items-center gap-2">
                <img src="<?= htmlspecialchars($logo) ?>" alt="Logo actual" class="cfg-preview">
                <input type="file" name="logo" class="form-control form-control-sm" accept="image/*" <?= $puede ? '' : 'disabled' ?>>
              </div>
            </div>
            <div class="col-12">
              <small class="text-muted" style="font-size:.74rem">
                JPG, PNG, WEBP o GIF, max 5 MB.
                <?php if ($icono === $defLogo && $logo === $defLogo): ?>Se usa el icono/logo por defecto.<?php endif; ?>
              </small>
            </div>
          </div>
        </div>

        <!-- Redes sociales -->
        <div class="col-lg-3">
          <?= $bloque('fa-share-nodes', 'Redes sociales') ?>
          <div class="d-grid gap-3">
            <?php foreach ($redes as $clave => [$lbl, $ph, $iconoFa]): ?>
            <div>
              <label class="form-label"><i class="<?= $iconoFa ?> me-1"></i><?= htmlspecialchars($lbl) ?></label>
              <input type="url" name="<?= $clave ?>" class="form-control form-control-sm" placeholder="<?= htmlspecialchars($ph) ?>"
                     value="<?= htmlspecialchars($cfg[$clave] ?? '') ?>" <?= $puede ? '' : 'readonly' ?>>
            </div>
            <?php endforeach; ?>
            <small class="text-muted" style="font-size:.74rem">
              WhatsApp y correo salen de Datos generales.
            </small>
          </div>
        </div>
      </div>

      <hr class="cfg-div">

      <!-- ---------- Videos de guias ---------- -->
      <?= $bloque('fa-circle-play', 'Videos de la seccion Guias (pagina principal)') ?>
      <div class="row g-3">
        <?php foreach ($guias as $i => $g): ?>
        <div class="col-md-6 col-xl-4">
          <div class="cfg-card">
            <div class="cfg-card-head">
              <span>Guia <?= $i + 1 ?></span>
              <span class="badge-soft b-info" style="font-size:.68rem"><?= htmlspecialchars(mb_strtoupper(mb_substr($g['categoria'], 0, 18))) ?></span>
            </div>
            <div class="row g-2">
              <div class="col-8">
                <label class="form-label cfg-lbl">Titulo</label>
                <input type="text" name="guia[<?= $i ?>][titulo]" class="form-control form-control-sm" placeholder="<?= htmlspecialchars($g['titulo']) ?>"
                       value="<?= htmlspecialchars($g['titulo']) ?>" <?= $puede ? '' : 'readonly' ?>>
              </div>
              <div class="col-4">
                <label class="form-label cfg-lbl">Duracion</label>
                <input type="text" name="guia[<?= $i ?>][duracion]" class="form-control form-control-sm" placeholder="0:15"
                       value="<?= htmlspecialchars($g['duracion']) ?>" <?= $puede ? '' : 'readonly' ?>>
              </div>
              <div class="col-7">
                <label class="form-label cfg-lbl">Categoria</label>
                <input type="text" name="guia[<?= $i ?>][categoria]" class="form-control form-control-sm" placeholder="<?= htmlspecialchars($g['categoria']) ?>"
                       value="<?= htmlspecialchars($g['categoria']) ?>" <?= $puede ? '' : 'readonly' ?>>
              </div>
              <div class="col-5">
                <label class="form-label cfg-lbl">Icono</label>
                <input type="text" name="guia[<?= $i ?>][icono]" class="form-control form-control-sm" placeholder="fa-route"
                       value="<?= htmlspecialchars($g['icono']) ?>" <?= $puede ? '' : 'readonly' ?>>
              </div>
              <div class="col-12">
                <label class="form-label cfg-lbl">URL del video (MP4)</label>
                <input type="url" name="guia[<?= $i ?>][video]" class="form-control form-control-sm" placeholder="https://...mp4"
                       value="<?= htmlspecialchars($g['video']) ?>" <?= $puede ? '' : 'readonly' ?>>
              </div>
              <div class="col-12">
                <label class="form-label cfg-lbl">Miniatura (imagen 16:9)</label>
                <input type="url" name="guia[<?= $i ?>][poster]" class="form-control form-control-sm" placeholder="https://...jpg"
                       value="<?= htmlspecialchars($g['poster']) ?>" <?= $puede ? '' : 'readonly' ?>>
              </div>
              <div class="col-12">
                <label class="form-label cfg-lbl">Consejo de la tarjeta</label>
                <input type="text" name="guia[<?= $i ?>][tip]" class="form-control form-control-sm" placeholder="Consejo breve..."
                       value="<?= htmlspecialchars($g['tip']) ?>" <?= $puede ? '' : 'readonly' ?>>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <hr class="cfg-div">

      <!-- ---------- Pie del panel ---------- -->
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted" style="font-size:.76rem">
          <i class="fas fa-circle-info me-1"></i>Se guarda todo el formulario de una vez.
        </small>
        <?php if ($puede): ?>
          <button type="submit" class="btn btn-gold">
            <i class="fas fa-floppy-disk me-2"></i>Guardar cambios
          </button>
        <?php else: ?>
          <span class="badge-soft b-info"><i class="fas fa-lock me-1"></i>Solo lectura en esta seccion.</span>
        <?php endif; ?>
      </div>
    </form>

  </div>
</div>

<!-- ================= Fila inferior: estado + registros ================= -->
<div class="row g-3 mt-1">
  <div class="col-md-6">
    <div class="glass panel">
      <div class="panel-title mb-2"><i class="fas fa-circle-info"></i>Estado del sistema</div>
      <div class="d-flex flex-wrap gap-2">
        <span class="badge-soft b-info">Version <strong class="ms-1">ESDRIVE v1.0</strong></span>
        <span class="badge-soft b-active">Base de datos <strong class="ms-1" style="color:#46c26e">Conectada</strong></span>
        <button class="btn btn-glass btn-sm" onclick="document.getElementById('btnToggleTheme').click()">
          <i class="fas fa-moon me-1"></i>Cambiar tema</button>
        <a href="<?= BASE_URL ?>/admin.php?v=logout" class="btn btn-glass btn-sm">
          <i class="fas fa-right-from-bracket me-1"></i>Cerrar sesion</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="glass panel">
      <div class="panel-title mb-2"><i class="fas fa-database"></i>Registros en BD</div>
      <div class="d-flex flex-wrap gap-2">
        <?php
        $tablas = ['vehiculos' => 'Vehiculos', 'empleados' => 'Empleados', 'alumnos' => 'Alumnos',
                   'usuarios' => 'Usuarios', 'cursos' => 'Cursos', 'examenes' => 'Examenes', 'horarios' => 'Horarios'];
        foreach ($tablas as $t => $lbl):
            try { $n = (int) db()->query("SELECT COUNT(*) FROM `$t`")->fetchColumn(); }
            catch (Exception $e) { $n = 0; }
        ?>
          <span class="badge-soft b-info"><?= $lbl ?> <strong class="ms-1"><?= $n ?></strong></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
