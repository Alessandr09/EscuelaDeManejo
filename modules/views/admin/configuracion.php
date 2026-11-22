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

$puede  = esdrive_puede('configuracion', 'gestionar');
$guias  = esdrive_guias();
$icono  = esdrive_favicon();
$logo   = esdrive_logo();
$defIcono = BASE_URL . '/assets/img/logo.svg';

/** Etiqueta de seccion + boton guardar compartido */
$btnGuardar = function () use ($puede) {
    if ($puede) {
        return '<div class="d-flex justify-content-end gap-2 mt-4">'
             . '<button type="submit" class="btn btn-gold"><i class="fas fa-floppy-disk me-2"></i>Guardar cambios</button>'
             . '</div>';
    }
    return '<div class="mt-4"><span class="badge-soft b-info"><i class="fas fa-lock me-1"></i>Tienes acceso de solo lectura a esta seccion.</span></div>';
};
?>
<?= esdrive_page_head('Configuracion', 'del sistema', 'Datos de la escuela, apariencia y contenido de la pagina principal.') ?>

<div class="row g-3">

  <!-- ===================== Columna principal ===================== -->
  <div class="col-lg-8">

    <!-- ---------- Datos generales ---------- -->
    <div class="glass panel">
      <div class="panel-title mb-3"><i class="fas fa-sliders"></i>Datos generales</div>
      <form method="post">
        <input type="hidden" name="accion" value="guardar_config">
        <div class="row g-3">
          <?php foreach ($campos as $clave => [$lbl, $ph, $iconoFa]): ?>
          <div class="col-md-6">
            <label class="form-label"><?= htmlspecialchars($lbl) ?></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas <?= $iconoFa ?>"></i></span>
              <input type="text" name="<?= $clave ?>" class="form-control" placeholder="<?= htmlspecialchars($ph) ?>"
                     value="<?= htmlspecialchars($cfg[$clave] ?? '') ?>" <?= $puede ? '' : 'readonly' ?>>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?= $btnGuardar() ?>
      </form>
    </div>

    <!-- ---------- Icono y logo ---------- -->
    <div class="glass panel mt-3">
      <div class="panel-title mb-1"><i class="fas fa-image"></i>Icono y logo del sitio</div>
      <p class="text-muted mb-3" style="font-size:.83rem">
        El <strong>icono</strong> se muestra en la pestana del navegador (favicon).
        El <strong>logo</strong> aparece en la barra de navegacion, el footer y el sidebar del panel.
        Formatos: JPG, PNG, WEBP o GIF, maximo 5 MB.
      </p>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="guardar_config">
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label">Icono (favicon)</label>
            <div class="d-flex align-items-center gap-3">
              <img src="<?= htmlspecialchars($icono) ?>" alt="Icono actual"
                   style="width:48px;height:48px;object-fit:contain;background:rgba(255,255,255,.06);border-radius:10px;padding:4px">
              <input type="file" name="icono" class="form-control" accept="image/*" <?= $puede ? '' : 'disabled' ?>>
            </div>
            <small class="text-muted">Actual: <?= htmlspecialchars(basename(parse_url($icono, PHP_URL_PATH) ?: 'logo.svg')) ?></small>
          </div>
          <div class="col-md-6">
            <label class="form-label">Logo</label>
            <div class="d-flex align-items-center gap-3">
              <img src="<?= htmlspecialchars($logo) ?>" alt="Logo actual"
                   style="width:48px;height:48px;object-fit:contain;background:rgba(255,255,255,.06);border-radius:10px;padding:4px">
              <input type="file" name="logo" class="form-control" accept="image/*" <?= $puede ? '' : 'disabled' ?>>
            </div>
            <small class="text-muted">Actual: <?= htmlspecialchars(basename(parse_url($logo, PHP_URL_PATH) ?: 'logo.svg')) ?></small>
          </div>
        </div>
        <?php if ($icono === $defIcono && $logo === $defIcono): ?>
          <div class="mt-3"><span class="badge-soft b-info"><i class="fas fa-circle-info me-1"></i>Se esta usando el icono/logo por defecto.</span></div>
        <?php endif; ?>
        <?= $btnGuardar() ?>
      </form>
    </div>

    <!-- ---------- Videos de guias ---------- -->
    <div class="glass panel mt-3">
      <div class="panel-title mb-1"><i class="fas fa-circle-play"></i>Videos de la seccion Guias</div>
      <p class="text-muted mb-3" style="font-size:.83rem">
        Contenido de las tarjetas "Guias para el volante" de la pagina principal.
        Deja un campo vacio para conservar el valor por defecto. El miniatura es la imagen que se ve en la tarjeta (16:9).
      </p>
      <form method="post">
        <input type="hidden" name="accion" value="guardar_config">
        <?php foreach ($guias as $i => $g): ?>
        <fieldset class="mb-4" style="border:1px solid var(--border);border-radius:14px;padding:14px 16px 6px">
          <legend style="font-size:.8rem;color:var(--gold);font-weight:700;padding:0 6px">Guia <?= $i + 1 ?> &middot; <?= htmlspecialchars($g['categoria']) ?></legend>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Categoria</label>
              <input type="text" name="guia[<?= $i ?>][categoria]" class="form-control" placeholder="<?= htmlspecialchars($g['categoria']) ?>"
                     value="<?= htmlspecialchars($g['categoria']) ?>" <?= $puede ? '' : 'readonly' ?>>
            </div>
            <div class="col-md-5">
              <label class="form-label">Titulo</label>
              <input type="text" name="guia[<?= $i ?>][titulo]" class="form-control" placeholder="<?= htmlspecialchars($g['titulo']) ?>"
                     value="<?= htmlspecialchars($g['titulo']) ?>" <?= $puede ? '' : 'readonly' ?>>
            </div>
            <div class="col-md-3">
              <label class="form-label">Duracion</label>
              <input type="text" name="guia[<?= $i ?>][duracion]" class="form-control" placeholder="0:15"
                     value="<?= htmlspecialchars($g['duracion']) ?>" <?= $puede ? '' : 'readonly' ?>>
            </div>
            <div class="col-md-4">
              <label class="form-label">Icono (FontAwesome)</label>
              <input type="text" name="guia[<?= $i ?>][icono]" class="form-control" placeholder="fa-route"
                     value="<?= htmlspecialchars($g['icono']) ?>" <?= $puede ? '' : 'readonly' ?>>
            </div>
            <div class="col-md-8">
              <label class="form-label">URL del video (MP4)</label>
              <input type="url" name="guia[<?= $i ?>][video]" class="form-control" placeholder="https://...mp4"
                     value="<?= htmlspecialchars($g['video']) ?>" <?= $puede ? '' : 'readonly' ?>>
            </div>
            <div class="col-12">
              <label class="form-label">URL de la miniatura (imagen 16:9)</label>
              <input type="url" name="guia[<?= $i ?>][poster]" class="form-control" placeholder="https://...jpg"
                     value="<?= htmlspecialchars($g['poster']) ?>" <?= $puede ? '' : 'readonly' ?>>
            </div>
            <div class="col-12">
              <label class="form-label">Consejo (texto corto de la tarjeta)</label>
              <input type="text" name="guia[<?= $i ?>][tip]" class="form-control" placeholder="Consejo breve..."
                     value="<?= htmlspecialchars($g['tip']) ?>" <?= $puede ? '' : 'readonly' ?>>
            </div>
          </div>
        </fieldset>
        <?php endforeach; ?>
        <?= $btnGuardar() ?>
      </form>
    </div>

    <!-- ---------- Redes sociales ---------- -->
    <div class="glass panel mt-3">
      <div class="panel-title mb-1"><i class="fas fa-share-nodes"></i>Redes sociales</div>
      <p class="text-muted mb-3" style="font-size:.83rem">
        Enlaces que se usan en el footer y en la seccion "Siguenos" de la pagina principal.
        WhatsApp y correo se toman de los datos generales. Deja vacio el enlace para que muestre el valor por defecto.
      </p>
      <form method="post">
        <input type="hidden" name="accion" value="guardar_config">
        <div class="row g-3">
          <?php foreach ($redes as $clave => [$lbl, $ph, $iconoFa]): ?>
          <div class="col-md-4">
            <label class="form-label"><i class="<?= $iconoFa ?> me-1"></i><?= htmlspecialchars($lbl) ?></label>
            <input type="url" name="<?= $clave ?>" class="form-control" placeholder="<?= htmlspecialchars($ph) ?>"
                   value="<?= htmlspecialchars($cfg[$clave] ?? '') ?>" <?= $puede ? '' : 'readonly' ?>>
          </div>
          <?php endforeach; ?>
        </div>
        <?= $btnGuardar() ?>
      </form>
    </div>

  </div>

  <!-- ===================== Columna lateral ===================== -->
  <div class="col-lg-4">
    <div class="glass panel mb-3">
      <div class="panel-title mb-3"><i class="fas fa-circle-info"></i>Estado del sistema</div>
      <ul class="list-unstyled mb-0" style="font-size:.86rem">
        <li class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--border)">
          <span style="color:var(--text-mut)">Version</span><strong>ESDRIVE v1.0</strong>
        </li>
        <li class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--border)">
          <span style="color:var(--text-mut)">Base de datos</span><strong style="color:#46c26e">Conectada</strong>
        </li>
        <li class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--border)">
          <span style="color:var(--text-mut)">Tema</span>
          <span><button class="btn btn-glass btn-sm" onclick="document.getElementById('btnToggleTheme').click()">
            <i class="fas fa-moon me-1"></i>Cambiar</button></span>
        </li>
        <li class="d-flex justify-content-between py-2">
          <span style="color:var(--text-mut)">Sesion</span>
          <a href="<?= BASE_URL ?>/admin.php?v=logout" style="color:var(--gold);font-weight:600">Cerrar sesion</a>
        </li>
      </ul>
    </div>

    <div class="glass panel">
      <div class="panel-title mb-3"><i class="fas fa-database"></i>Registros en BD</div>
      <ul class="list-unstyled mb-0" style="font-size:.86rem">
        <?php
        $tablas = ['vehiculos' => 'Vehiculos', 'empleados' => 'Empleados', 'alumnos' => 'Alumnos',
                   'usuarios' => 'Usuarios', 'cursos' => 'Cursos', 'examenes' => 'Examenes', 'horarios' => 'Horarios'];
        foreach ($tablas as $t => $lbl):
            try { $n = (int) db()->query("SELECT COUNT(*) FROM `$t`")->fetchColumn(); }
            catch (Exception $e) { $n = 0; }
        ?>
        <li class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--border)">
          <span style="color:var(--text-mut)"><?= $lbl ?></span><strong><?= $n ?></strong>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
