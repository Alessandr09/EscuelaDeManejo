<?php require_once __DIR__ . '/../../../php/base.php'; ?>
<div class="page-head d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <h3><span>Cuentas</span> de alumnos</h3>
    <p>Administra los registros y accesos de los estudiantes.</p>
  </div>
  <button class="btn btn-gold btn-sm" onclick="Swal.fire({icon:'success',title:'Demo',text:'Aqui se abrira el formulario para crear cuentas.',confirmButtonColor:'#e9a537'})">
    <i class="fas fa-user-plus me-2"></i>Nueva cuenta
  </button>
</div>

<div class="glass panel">
  <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
    <div class="panel-title mb-0"><i class="fas fa-users"></i>Listado general</div>
    <div class="d-flex gap-2 flex-wrap">
      <div class="input-group" style="width:240px">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" placeholder="Buscar alumno..." id="buscarCuenta">
      </div>
      <select class="form-select" style="width:160px">
        <option>Todos los cursos</option>
        <option>Clase A</option>
        <option>Clase B</option>
        <option>Clase C</option>
      </select>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-glass" id="tablaCuentas">
      <thead>
        <tr><th>Alumno</th><th>Telefono</th><th>Curso</th><th>Registro</th><th>Estado</th><th class="text-end">Acciones</th></tr>
      </thead>
      <tbody>
        <?php
        $cuentas = [
          ['Maria Lopez','maria@email.com','+503 7001-1111','Clase B','2026-10-24','Activo','b-active','ML','#ffd76a'],
          ['Carlos Ramirez','carlos@email.com','+503 7002-2222','Clase A','2026-10-23','Pendiente','b-pend','CR','#a3b6f5'],
          ['Ana Martinez','ana@email.com','+503 7003-3333','Clase C','2026-10-22','Activo','b-active','AM','#5fd689'],
          ['Jose Hernandez','jose@email.com','+503 7004-4444','Clase B','2026-10-21','Inactivo','b-off','JH','#f07276'],
          ['Laura Diaz','laura@email.com','+503 7005-5555','Renovacion','2026-10-20','Activo','b-active','LD','#ffd76a'],
          ['Roberto Sanchez','roberto@email.com','+503 7006-6666','Clase C','2026-10-19','Activo','b-active','RS','#a3b6f5'],
          ['Gabriela Reyes','gabriela@email.com','+503 7007-7777','Clase A','2026-10-18','Pendiente','b-pend','GR','#5fd689'],
        ];
        foreach ($cuentas as $c):
          [$nom,$mail,$tel,$curso,$f,$est,$cls,$ini,$col] = $c;
        ?>
        <tr>
          <td>
            <div class="cell-user">
              <div class="cell-avatar" style="background:<?= $col ?>"><?= $ini ?></div>
              <div><div class="cu-name"><?= $nom ?></div><div class="cu-mail"><?= $mail ?></div></div>
            </div>
          </td>
          <td><?= $tel ?></td>
          <td><?= $curso ?></td>
          <td data-fecha="<?= $f ?>"></td>
          <td><span class="badge-soft <?= $cls ?>"><?= $est ?></span></td>
          <td class="text-end">
            <a href="#" class="btn-act" title="Ver"><i class="fas fa-eye"></i></a>
            <a href="#" class="btn-act edit" title="Editar"><i class="fas fa-pen"></i></a>
            <a href="#" class="btn-act del" title="Eliminar"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.getElementById('buscarCuenta').addEventListener('input', function () {
  var q = this.value.toLowerCase();
  document.querySelectorAll('#tablaCuentas tbody tr').forEach(function (fila) {
    fila.style.display = fila.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>