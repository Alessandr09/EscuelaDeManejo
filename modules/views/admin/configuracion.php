<?php require_once __DIR__ . '/../../../php/base.php';
require_once __DIR__ . '/../../../php/ui.php';

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

$puede = esdrive_puede('configuracion', 'gestionar');
$cfgActual = function_exists('esdrive_cfg') ? esdrive_cfg() : null;
?>
<?= esdrive_page_head('Configuracion', 'del sistema', 'Datos de la escuela que se muestran en la pagina y en los reportes.') ?>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="glass panel">
      <div class="panel-title mb-3"><i class="fas fa-sliders"></i>Datos generales</div>
      <form method="post">
        <input type="hidden" name="accion" value="guardar_config">
        <div class="row g-3">
          <?php foreach ($campos as $clave => [$lbl, $ph, $icono]): ?>
          <div class="col-md-6">
            <label class="form-label"><i class="fas <?= $icono ?> me-1" style="color:var(--gold)"></i><?= htmlspecialchars($lbl) ?></label>
            <input type="text" name="<?= $clave ?>" class="form-control" placeholder="<?= htmlspecialchars($ph) ?>"
                   value="<?= htmlspecialchars($cfg[$clave] ?? '') ?>" <?= $puede ? '' : 'readonly' ?>>
          </div>
          <?php endforeach; ?>
        </div>
        <?php if ($puede): ?>
        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="submit" class="btn btn-gold"><i class="fas fa-floppy-disk me-2"></i>Guardar cambios</button>
        </div>
        <?php else: ?>
        <div class="mt-4">
          <span class="badge-soft b-info"><i class="fas fa-lock me-1"></i>Tienes acceso de solo lectura a esta seccion.</span>
        </div>
        <?php endif; ?>
      </form>
    </div>
  </div>

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
