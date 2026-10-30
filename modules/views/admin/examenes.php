<?php require_once __DIR__ . '/../../../php/base.php'; ?>
<div class="page-head d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <h3>Gestion de <span>examenes</span></h3>
    <p>Crea y administra las evaluaciones teoricas y practicas.</p>
  </div>
  <button class="btn btn-gold btn-sm" onclick="abrirModalExamen()"><i class="fas fa-plus me-2"></i>Nuevo examen</button>
</div>

<!-- KPIs rapidos -->
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="glass kpi-card"><div class="kpi-icon"><i class="fas fa-file-lines"></i></div><div class="kpi-info"><div class="kpi-val">6</div><div class="kpi-lbl">Examenes activos</div></div></div></div>
  <div class="col-md-3"><div class="glass kpi-card"><div class="kpi-icon"><i class="fas fa-pen-nib"></i></div><div class="kpi-info"><div class="kpi-val">342</div><div class="kpi-lbl">Aplicaciones</div></div></div></div>
  <div class="col-md-3"><div class="glass kpi-card"><div class="kpi-icon"><i class="fas fa-circle-check"></i></div><div class="kpi-info"><div class="kpi-val">91%</div><div class="kpi-lbl">Aprobados</div></div></div></div>
  <div class="col-md-3"><div class="glass kpi-card"><div class="kpi-icon"><i class="fas fa-hourglass-half"></i></div><div class="kpi-info"><div class="kpi-val">18</div><div class="kpi-lbl">Pendientes</div></div></div></div>
</div>

<div class="glass panel">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div class="panel-title mb-0"><i class="fas fa-list-check"></i>Examenes registrados</div>
    <div class="input-group" style="width:240px">
      <span class="input-group-text"><i class="fas fa-search"></i></span>
      <input type="text" class="form-control" placeholder="Buscar examen...">
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-glass">
      <thead>
        <tr><th>Titulo</th><th>Curso</th><th>Preguntas</th><th>Duracion</th><th>Fecha</th><th>Estado</th><th class="text-end">Acciones</th></tr>
      </thead>
      <tbody>
        <?php
        $exams = [
          ['Seccion A: Senalamiento vial','Clase B',30,'20 min','2026-11-02','Activo','b-active'],
          ['Manejo defensivo teorico','Clase B',25,'25 min','2026-11-05','Activo','b-active'],
          ['Normas de transito urbano','Clase A',20,'15 min','2026-11-08','Pendiente','b-pend'],
          ['Carga y estabilidad','Clase C',35,'40 min','2026-11-12','Borrador','b-info'],
          ['Renovacion - Actualizacion','Renovacion',15,'10 min','2026-11-15','Activo','b-active'],
          ['Practico - Circuito cerrado','Clase A',10,'30 min','2026-11-18','Pendiente','b-pend'],
        ];
        foreach ($exams as $e):
          [$tit,$curso,$preg,$dur,$f,$est,$cls] = $e;
        ?>
        <tr>
          <td style="font-weight:600"><?= $tit ?></td>
          <td><?= $curso ?></td>
          <td><?= $preg ?></td>
          <td><?= $dur ?></td>
          <td data-fecha="<?= $f ?>"></td>
          <td><span class="badge-soft <?= $cls ?>"><?= $est ?></span></td>
          <td class="text-end">
            <a href="#" class="btn-act"><i class="fas fa-eye"></i></a>
            <a href="#" class="btn-act edit" onclick="abrirModalExamen();return false" title="Editar"><i class="fas fa-pen"></i></a>
            <a href="#" class="btn-act del"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal nuevo examen con CKEditor -->
<div class="modal fade" id="modalExamen" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="glass modal-content" style="background:rgba(20,28,46,.9)">
      <div class="modal-header" style="border-color:var(--glass-brd)">
        <h5 class="modal-title" style="font-family:'Poppins',sans-serif;font-weight:700"><i class="fas fa-file-signature me-2" style="color:var(--gold)"></i>Nuevo examen</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="exam-meta mb-3">
          <div class="em-pill"><b><i class="fas fa-calendar me-1"></i> Fecha:</b> <span id="fechaExamen"></span></div>
          <div class="em-pill"><b><i class="fas fa-language me-1"></i> Idioma:</b> es</div>
        </div>
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Titulo del examen</label>
            <input type="text" class="form-control" placeholder="Ej. Senalamiento vial - Seccion A">
          </div>
          <div class="col-md-4">
            <label class="form-label">Curso</label>
            <select class="form-select"><option>Clase A</option><option>Clase B</option><option>Clase C</option><option>Renovacion</option></select>
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Descripcion / Instrucciones</label>
          <textarea id="editorExamen" rows="6"></textarea>
        </div>
      </div>
      <div class="modal-footer" style="border-color:var(--glass-brd)">
        <button type="button" class="btn btn-glass btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-gold btn-sm" onclick="guardarExamen()"><i class="fas fa-save me-2"></i>Guardar</button>
      </div>
    </div>
  </div>
</div>