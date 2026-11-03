<?php
/**
 * MODULO 1 - CONFIGURACION DEL CATALOGO DE CUENTAS
 *
 * Acciones:
 *   (por defecto)  listar el catalogo
 *   ?accion=registrar  alta de una cuenta nueva
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';

$accion = $_GET['accion'] ?? 'listar';

$errores = [];
$exito   = '';

/* ---------------------------------------------------------------------
 *  Alta de cuenta
 * ------------------------------------------------------------------- */
if ($accion === 'registrar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo      = trim((string) ($_POST['codigo'] ?? ''));
    $nombre      = trim((string) ($_POST['nombre'] ?? ''));
    $naturaleza  = (string) ($_POST['naturaleza'] ?? '');
    $opcionSaldo = (string) ($_POST['saldo'] ?? '');

    // El saldo habitual se sugiere segun la naturaleza, pero puede ajustarse.
    $sugerido = ($naturaleza === 'ACTIVO') ? 'DEUDOR' : 'ACREEDOR';

    if ($codigo === '') {
        $errores[] = 'El codigo de la cuenta es obligatorio.';
    } elseif (existeCuenta($pdo, $codigo)) {
        $errores[] = 'El codigo "' . $codigo . '" ya existe en el catalogo.';
    }

    if ($nombre === '') {
        $errores[] = 'El nombre de la cuenta es obligatorio.';
    }

    if (!in_array($naturaleza, ['ACTIVO', 'PASIVO', 'CAPITAL'], true)) {
        $errores[] = 'Opcion de naturaleza invalida.';
    }

    // Vacio = usar el valor sugerido; cualquier otro valor debe ser valido.
    $saldo = match ($opcionSaldo) {
        '1'     => 'DEUDOR',
        '2'     => 'ACREEDOR',
        '',     => $sugerido,
        default => null,
    };

    if ($saldo === null) {
        $errores[] = 'Opcion de saldo habitual invalida.';
    }

    $rubro = trim((string) ($_POST['rubro'] ?? ''));
    if ($rubro === '' || !isset(rubrosDisponibles()[$rubro])) {
        $rubro = clasificarCuenta($codigo, $nombre);
    }

    if ($errores === []) {
        $sql = 'INSERT INTO cuentas (codigo, nombre, naturaleza, saldo, rubro) VALUES (?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([$codigo, $nombre, $naturaleza, $saldo, $rubro]);

        $exito   = 'Cuenta ' . $codigo . ' - ' . $nombre . ' registrada ('
            . $naturaleza . ', saldo ' . $saldo . ', rubro: ' . nombreRubro($rubro) . ').';
        $accion  = 'listar';
    }
}

/* ---------------------------------------------------------------------
 *  Listado
 * ------------------------------------------------------------------- */
$catalogo = obtenerCatalogo($pdo);

$tituloModulo = ($accion === 'registrar')
    ? 'Módulo 1 · Registrar cuenta nueva'
    : 'Módulo 1 · Catálogo de cuentas';

iniciarPagina($tituloModulo, 'modulos/modulo1_catalogo.php', $accion);

?>
<?php if ($exito !== ''): ?>
    <?= mensajeOk($exito) ?>
<?php endif; ?>

<?= mensajeErrores($errores) ?>

<?php if ($accion === 'registrar'): ?>

    <section class="tarjeta">
        <div class="encabezado-seccion">
            <h3>Registrar nueva cuenta</h3>
            <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo1_catalogo.php">Ver catálogo</a>
        </div>

        <form method="post" action="<?= RUTA_BASE ?>/modulos/modulo1_catalogo.php?accion=registrar">
            <p>
                <label for="codigo">Código de la cuenta (ej. 1.1.5)</label>
                <input type="text" id="codigo" name="codigo" placeholder="1.1.5"
                       value="<?= h($_POST['codigo'] ?? '') ?>" required>
            </p>

            <p>
                <label for="nombre">Nombre de la cuenta</label>
                <input type="text" id="nombre" name="nombre" placeholder="Alquiler de local"
                       value="<?= h($_POST['nombre'] ?? '') ?>" required>
            </p>

            <p>
                <label>Naturaleza</label>
                <label><input type="radio" name="naturaleza" value="ACTIVO"
                    <?= (($_POST['naturaleza'] ?? '') === 'ACTIVO') ? 'checked' : '' ?>> Activo</label>
                <label><input type="radio" name="naturaleza" value="PASIVO"
                    <?= (($_POST['naturaleza'] ?? '') === 'PASIVO') ? 'checked' : '' ?>> Pasivo</label>
                <label><input type="radio" name="naturaleza" value="CAPITAL"
                    <?= (($_POST['naturaleza'] ?? 'CAPITAL') === 'CAPITAL') ? 'checked' : '' ?>> Capital</label>
            </p>

            <p>
                <label for="saldo">Saldo habitual</label>
                <select id="saldo" name="saldo">
                    <option value="">Sugerido según naturaleza</option>
                    <option value="1" <?= (($_POST['saldo'] ?? '') === '1') ? 'selected' : '' ?>>Deudor</option>
                    <option value="2" <?= (($_POST['saldo'] ?? '') === '2') ? 'selected' : '' ?>>Acreedor</option>
                </select>
            </p>

            <p>
                <label for="rubro">Rubro / Clasificación contable</label>
                <select id="rubro" name="rubro">
                    <option value="">(Detectar automáticamente según código y nombre)</option>
                    <?php foreach (rubrosDisponibles() as $k => $v): ?>
                        <option value="<?= $k ?>" <?= (($_POST['rubro'] ?? '') === $k) ? 'selected' : '' ?>>
                            <?= h($v) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <div class="acciones-form">
                <button type="submit">Registrar cuenta</button>
                <a class="btn-a btn-sec" href="<?= RUTA_BASE ?>/modulos/modulo1_catalogo.php">Cancelar</a>
            </div>
        </form>
    </section>

<?php else: ?>

    <section class="tarjeta">
        <div class="encabezado-seccion">
            <h3>Catálogo de cuentas</h3>
            <a class="btn-a" href="<?= RUTA_BASE ?>/modulos/modulo1_catalogo.php?accion=registrar">Registrar nueva cuenta</a>
        </div>

        <?php if ($catalogo === []): ?>
            <div class="estado-vacio">
                <strong>El catálogo está vacío</strong>
                Registra cuentas primero para poder registrar asientos.
            </div>
        <?php else: ?>
            <div class="tabla-envoltura">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Naturaleza</th>
                            <th>Saldo Habitual</th>
                            <th>Rubro / Clasificación</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($catalogo as $cuenta): ?>
                        <tr>
                            <td><code><?= h($cuenta['codigo']) ?></code></td>
                            <td><strong><?= h($cuenta['nombre']) ?></strong></td>
                            <td><?= h($cuenta['naturaleza']) ?></td>
                            <td><?= h($cuenta['saldo']) ?></td>
                            <td><?= h(nombreRubro($cuenta['rubro'] ?? clasificarCuenta($cuenta['codigo'], $cuenta['nombre']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="pie-tabla">Total de cuentas: <strong><?= count($catalogo) ?></strong></p>
        <?php endif; ?>
    </section>

<?php endif; ?>

<?php
terminarPagina();
