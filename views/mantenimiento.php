<?php // views/mantenimiento.php
$base = rtrim(APP_BASE, '/');
?>
<!-- ══ MANTENIMIENTO ════════════════════════════════════════ -->
<div class="flex items-center justify-between mb-12">
  <h1 style="font-size:18px;font-weight:700">Mantenimiento</h1>
  <div style="display:flex;gap:8px">
    <button class="btn btn-outline" onclick="abrirModalDoc(null)">+ Documento</button>
    <button class="btn btn-primary" onclick="abrirModalMant(null)">+ Mantenimiento</button>
  </div>
</div>

<!-- KPIs de costo mantenimiento -->
<div class="kpi-grid" style="margin-bottom:var(--gap)">
  <div class="kpi-card"><div class="kpi-lbl">Mantenimientos este año</div><div class="kpi-val brand" id="mt-n">—</div></div>
  <div class="kpi-card"><div class="kpi-lbl">Costo repuestos</div><div class="kpi-val" id="mt-rep">—</div></div>
  <div class="kpi-card"><div class="kpi-lbl">Costo mano de obra</div><div class="kpi-val" id="mt-mo">—</div></div>
  <div class="kpi-card"><div class="kpi-lbl">Costo total</div><div class="kpi-val brand" id="mt-tot">—</div></div>
</div>

<!-- Alertas plan mantenimiento -->
<div class="card full" style="margin-bottom:var(--gap)">
  <div class="card-title">🔧 Plan preventivo — Semáforo</div>
  <div id="mant-plan-lista" class="sem-list">
    <div class="empty-state" style="padding:20px">Cargando…</div>
  </div>
</div>

<!-- Documentos semáforo -->
<div class="card full" style="margin-bottom:var(--gap)">
  <div class="card-title">
    📄 Documentos de unidades — Semáforo
    <button class="btn btn-outline btn-sm" onclick="abrirModalDoc(null)">+ Nuevo</button>
  </div>
  <div id="docs-lista" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:10px">
    <div class="empty-state" style="padding:20px">Cargando…</div>
  </div>
</div>

<!-- Historial mantenimiento -->
<div class="card full">
  <!-- Filtro tipo para análisis -->
  <div style="display:flex;gap:8px;margin-bottom:var(--gap);flex-wrap:wrap">
    <select id="mth-tipo-mant" onchange="cargarHistorial();cargarAnalisisRepuestos()"
            style="font-size:13px;padding:7px 10px;border:1px solid var(--border2);border-radius:8px">
      <option value="">Todos los tipos</option>
      <option value="PREVENTIVO">Preventivo</option>
      <option value="CORRECTIVO">Correctivo</option>
      <option value="CAMBIO LLANTAS">Cambio de llantas</option>
      <option value="REVISION">Revisión</option>
    </select>
    <button class="btn btn-outline btn-sm" onclick="cargarAnalisisRepuestos()">Calcular durabilidad</button>
  </div>

  <!-- Panel análisis durabilidad -->
  <div class="card full" style="margin-bottom:var(--gap)">
    <div class="card-title">🔬 Análisis de durabilidad de repuestos</div>
    <div id="analisis-resumen" class="kpi-grid" style="margin-bottom:12px">
      <div class="empty-state" style="padding:16px">Selecciona un tipo de mantenimiento y haz clic en "Calcular durabilidad".</div>
    </div>
    <div style="font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;margin-bottom:8px">Detalle por cambio</div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr><th>Fecha</th><th>Unidad</th><th>Tipo</th><th>km registro</th><th>Días duró</th><th>km duró</th><th>Costo</th></tr></thead>
        <tbody id="analisis-tbody"><tr><td colspan="7" class="empty">Sin datos.</td></tr></tbody>
      </table>
    </div>
  </div>

  <div class="card full">
  <div class="card-title">
    Historial de mantenimientos
    <div style="display:flex;gap:8px">
      <select id="mth-unidad" onchange="cargarHistorial()"
              style="font-size:12px;padding:4px 8px;border:1px solid var(--border2);border-radius:6px">
        <option value="">Todas las unidades</option>
      </select>
      <button class="btn btn-outline btn-sm" onclick="exportarMant()">Excel</button>
    </div>
  </div>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>Fecha</th><th>Unidad</th><th>Tipo</th><th>km / Horóm.</th>
          <th>Descripción</th><th>Marca</th>
          <th>Repuestos</th><th>Mano obra</th><th>Total</th><th>Acc.</th>
        </tr>
      </thead>
      <tbody id="mant-tbody"><tr><td colspan="10" class="empty">Cargando…</td></tr></tbody>
    </table>
  </div>
  <div class="pager" id="pager-mant"></div>
</div>

<!-- ══ MODAL: Mantenimiento ════════════════════════════════ -->
<div class="overlay" id="modal-mant">
  <div class="modal modal-lg">
    <div class="modal-hdr">
      <span class="modal-title" id="modal-mant-title">Registrar Mantenimiento</span>
      <button class="modal-close" onclick="cerrarModal('modal-mant')">×</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="mnt-id"/>
      <div class="form-grid cols-2">
        <div class="fgroup"><label>Unidad *</label>
          <select id="mnt-unidad"><option value="">Seleccionar…</option></select></div>
        <div class="fgroup"><label>Fecha ejecución *</label>
          <input type="date" id="mnt-fecha" value="<?= date('Y-m-d') ?>"/></div>
      </div>
      <div class="form-grid cols-2">
        <div class="fgroup"><label>Tipo mantenimiento *</label>
          <select id="mnt-tipo">
            <option value="PREVENTIVO">Preventivo</option>
            <option value="CORRECTIVO">Correctivo</option>
            <option value="CAMBIO LLANTAS">Cambio de llantas</option>
            <option value="REVISION">Revisión</option>
          </select></div>
        <div class="fgroup"><label>Marca / Proveedor</label>
          <input type="text" id="mnt-marca" placeholder="Nombre del taller o marca…"/></div>
      </div>
      <div class="form-grid cols-2">
        <div class="fgroup"><label>km al momento (flota)</label>
          <input type="number" id="mnt-km" step="1" placeholder="km del vehículo"/></div>
        <div class="fgroup"><label>Horómetro (maquinaria/GE)</label>
          <input type="number" id="mnt-horom" step="0.1" placeholder="horas"/></div>
      </div>
      <div class="fgroup full"><label>Descripción del trabajo *</label>
        <textarea id="mnt-desc" rows="3" placeholder="Detalle del mantenimiento realizado…"></textarea></div>
      <div class="form-grid cols-2">
        <div class="fgroup"><label>Costo repuestos (S/)</label>
          <input type="number" id="mnt-rep" step="0.01" placeholder="0.00" value="0" oninput="calcCostMant()"/></div>
        <div class="fgroup"><label>Costo mano de obra (S/)</label>
          <input type="number" id="mnt-mo" step="0.01" placeholder="0.00" value="0" oninput="calcCostMant()"/></div>
      </div>
      <div style="background:var(--bg);border-radius:8px;padding:10px 14px;font-size:13px">
        Total calculado: <strong id="mnt-total-disp">S/ 0.00</strong>
      </div>
    </div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-mant')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarMant()">Guardar</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Documento ════════════════════════════════════ -->
<div class="overlay" id="modal-doc">
  <div class="modal modal-sm">
    <div class="modal-hdr">
      <span class="modal-title">Registrar Documento</span>
      <button class="modal-close" onclick="cerrarModal('modal-doc')">×</button>
    </div>
    <div class="modal-body">
      <div class="fgroup"><label>Unidad *</label>
        <select id="doc-unidad"><option value="">Seleccionar…</option></select></div>
      <div class="fgroup"><label>Tipo de documento *</label>
        <select id="doc-tipo">
          <option value="SOAT">SOAT</option>
          <option value="REVISION VEHICULAR">Revisión Vehicular</option>
          <option value="TARJETA DE PROPIEDAD">Tarjeta de Propiedad</option>
          <option value="SEGURO VEHICULAR">Seguro Vehicular</option>
          <option value="LICENCIA OPERACION">Licencia de Operación</option>
          <option value="OTRO">Otro</option>
        </select></div>
      <div class="form-grid cols-2">
        <div class="fgroup"><label>Fecha emisión</label>
          <input type="date" id="doc-emision"/></div>
        <div class="fgroup"><label>Fecha vencimiento *</label>
          <input type="date" id="doc-vence"/></div>
      </div>
      <div class="fgroup"><label>Días de alerta anticipada</label>
        <input type="number" id="doc-dias" value="30" min="1"/></div>
    </div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-doc')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarDoc()">Guardar</button>
    </div>
  </div>
</div>

<!-- Modal confirmar eliminar -->
<div class="overlay" id="modal-del-mant">
  <div class="modal modal-sm">
    <div class="modal-hdr"><span class="modal-title">Confirmar eliminación</span>
      <button class="modal-close" onclick="cerrarModal('modal-del-mant')">×</button></div>
    <div class="modal-body"><p>¿Eliminar este registro?</p></div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-del-mant')">Cancelar</button>
      <button class="btn btn-danger" id="btn-del-mant">Eliminar</button>
    </div>
  </div>
</div>

<script>
let mantData  = [], mantPage = 0;
const MANT_PP = 15;

document.addEventListener('DOMContentLoaded', () => {
  Promise.all([cargarUnidadesSelects()]).then(() => {
    cargarAlertas();
    cargarDocumentos();
    cargarHistorial();
    // No cargar analisis automáticamente — esperar que el usuario seleccione tipo
  });
});

async function cargarUnidadesSelects() {
  const data = await api('/api/unidades');
  ['mth-unidad','mnt-unidad','doc-unidad'].forEach(selId => {
    const sel = document.getElementById(selId); if (!sel) return;
    data.forEach(u => sel.insertAdjacentHTML('beforeend',
      `<option value="${u.id_unidad}">${u.placa} — ${u.tipo_unidad}</option>`));
  });
}

async function cargarAlertas() {
  const data = await api('/api/mantenimiento/alertas');
  const el   = document.getElementById('mant-plan-lista');
  if (!data.length) { el.innerHTML = '<div class="empty-state" style="padding:16px">Sin planes de mantenimiento.</div>'; return; }
  el.innerHTML = data.map(m => {
    const pct   = Math.min(m.pct||0, 100);
    const color = m.estado==='VENCIDO'||m.estado==='CRITICO'?'#A32D2D':m.estado==='PROXIMO'?'#854F0B':'#2d6a4f';
    const badge = m.estado==='VENCIDO'?'<span class="badge badge-danger">VENCIDO</span>'
                 :m.estado==='CRITICO'?'<span class="badge badge-danger">CRÍTICO</span>'
                 :m.estado==='PROXIMO'?'<span class="badge badge-warn">PRÓXIMO</span>'
                 :'<span class="badge badge-ok">OK</span>';
    const falta = m.falta_km?`Faltan ${fmt.num(m.falta_km,0)} km`:m.falta_h?`Faltan ${fmt.num(m.falta_h,1)} h`:'';
    return `<div class="sem-row">
      <div class="sem-info">
        <div class="sem-title">${m.placa} — ${m.tarea||m.tipo_mantenimiento}</div>
        <div class="sem-sub">${falta}${falta&&' · '}${fmt.num(pct,1)}% completado</div>
        <div class="progress-bar"><div class="progress-fill" style="width:${pct}%;background:${color}"></div></div>
      </div>${badge}
    </div>`;
  }).join('');
}

async function cargarDocumentos() {
  const data = await api('/api/mantenimiento/documentos');
  const el   = document.getElementById('docs-lista');
  if (!data.length) { el.innerHTML = '<div class="empty-state" style="padding:16px">Sin documentos.</div>'; return; }
  el.innerHTML = data.map(d => {
    const dias  = parseInt(d.dias_restantes);
    const estado= dias<0?'VENCIDO':dias<=7?'URGENTE':dias<=parseInt(d.alerta_dias_antes)?'ALERTA':'VIGENTE';
    const cls   = estado==='VENCIDO'||estado==='URGENTE'?'badge-danger':estado==='ALERTA'?'badge-warn':'badge-ok';
    const bord  = estado==='VENCIDO'||estado==='URGENTE'?'var(--danger)':estado==='ALERTA'?'var(--warn)':'var(--ok)';
    const label = dias<0?`Vencido hace ${Math.abs(dias)} días`:dias===0?'Vence HOY':`Vence en ${dias} días`;
    return `<div style="background:${estado==='VIGENTE'?'var(--ok-lt)':estado==='ALERTA'?'var(--warn-lt)':'var(--danger-lt)'};border:1.5px solid ${bord};border-radius:10px;padding:14px">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
        <div>
          <div style="font-weight:700;font-size:13px">${d.placa}</div>
          <div style="font-size:12px;color:var(--text2)">${d.tipo_documento}</div>
        </div>
        <span class="badge ${cls}">${estado}</span>
      </div>
      <div style="font-size:12px;color:var(--text3)">
        Emitido: ${fmtFecha(d.fecha_emision)} · Vence: ${fmtFecha(d.fecha_vencimiento)}
      </div>
      <div style="font-size:12px;font-weight:600;color:${bord};margin-top:4px">${label}</div>
      <div style="display:flex;justify-content:flex-end;margin-top:8px">
        <button class="btn btn-xs" style="background:var(--danger-lt);color:var(--danger);border:1px solid var(--danger)"
                onclick="eliminarDoc(${d.id_documento})">Eliminar</button>
      </div>
    </div>`;
  }).join('');
}

async function cargarHistorial() {
  const uid  = document.getElementById('mth-unidad').value;
  const tipo = document.getElementById('mth-tipo-mant')?.value;
  const params = [];
  if (uid)  params.push('id_unidad='+uid);
  if (tipo) params.push('tipo_mant='+encodeURIComponent(tipo));
  let url = '/api/mantenimiento/historial' + (params.length?'?'+params.join('&'):'');
  mantData = await api(url);
  mantPage = 0;
  renderHistorial();
  // KPIs
  const anio = new Date().getFullYear();
  const delAnio = mantData.filter(m => String(m.fecha_ejecucion||'').startsWith(anio));
  tx('mt-n',   delAnio.length);
  tx('mt-rep', fmt.sol(delAnio.reduce((s,m)=>s+parseFloat(m.costo_repuestos||0),0)));
  tx('mt-mo',  fmt.sol(delAnio.reduce((s,m)=>s+parseFloat(m.costo_mano_obra||0),0)));
  tx('mt-tot', fmt.sol(delAnio.reduce((s,m)=>s+parseFloat(m.costo_total_soles||0),0)));
}

function renderHistorial() {
  const tbody = document.getElementById('mant-tbody');
  const total = Math.max(1,Math.ceil(mantData.length/MANT_PP));
  const slice = mantData.slice(mantPage*MANT_PP,(mantPage+1)*MANT_PP);
  if (!slice.length) { tbody.innerHTML='<tr><td colspan="10" class="empty">Sin registros.</td></tr>'; renderPager('pager-mant',0,1,()=>{}); return; }
  tbody.innerHTML = slice.map(m=>`<tr>
    <td>${fmtFecha(m.fecha_ejecucion)}</td>
    <td><strong>${m.placa}</strong></td>
    <td><span class="badge badge-brand">${m.tipo_mantenimiento}</span></td>
    <td class="mono">${m.km_registro?fmt.num(m.km_registro,0)+' km':m.horometro_registro?fmt.num(m.horometro_registro,1)+' h':'—'}</td>
    <td class="text-sm" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${m.descripcion_trabajo||''}">${m.descripcion_trabajo||'—'}</td>
    <td class="text-sm">${m.marca||'—'}</td>
    <td>${fmt.sol(m.costo_repuestos)}</td>
    <td>${fmt.sol(m.costo_mano_obra)}</td>
    <td><strong>${fmt.sol(m.costo_total_soles)}</strong></td>
    <td><div class="action-btns">
      <button class="btn btn-outline btn-xs" onclick="abrirModalMant(${m.id_mantenimiento})">✏</button>
      <button class="btn btn-xs" style="background:var(--danger-lt);color:var(--danger);border:1px solid var(--danger)"
              onclick="confirmarDelMant(${m.id_mantenimiento})">🗑</button>
    </div></td>
  </tr>`).join('');
  renderPager('pager-mant', mantPage, total, p=>{mantPage=p;renderHistorial();});
}

// ── Modales mantenimiento ─────────────────────────────────────
async function abrirModalMant(id) {
  document.getElementById('mnt-id').value='';
  ['mnt-unidad','mnt-km','mnt-horom','mnt-desc','mnt-marca'].forEach(k=>{const e=document.getElementById(k);if(e)e.value='';});
  document.getElementById('mnt-fecha').value='<?= date('Y-m-d') ?>';
  document.getElementById('mnt-rep').value='0';
  document.getElementById('mnt-mo').value='0';
  tx('mnt-total-disp','S/ 0.00');

  if (id) {
    document.getElementById('modal-mant-title').textContent='Editar Mantenimiento';
    const m=mantData.find(x=>x.id_mantenimiento===id);
    if (m) {
      document.getElementById('mnt-id').value=m.id_mantenimiento;
      document.getElementById('mnt-unidad').value=m.id_unidad||'';
      document.getElementById('mnt-fecha').value=m.fecha_ejecucion||'';
      document.getElementById('mnt-tipo').value=m.tipo_mantenimiento||'PREVENTIVO';
      document.getElementById('mnt-km').value=m.km_registro??'';
      document.getElementById('mnt-horom').value=m.horometro_registro??'';
      document.getElementById('mnt-desc').value=m.descripcion_trabajo||'';
      document.getElementById('mnt-marca').value=m.marca||'';
      document.getElementById('mnt-rep').value=m.costo_repuestos??0;
      document.getElementById('mnt-mo').value=m.costo_mano_obra??0;
      calcCostMant();
    }
  } else {
    document.getElementById('modal-mant-title').textContent='Registrar Mantenimiento';
  }
  abrirModal('modal-mant');
}

function calcCostMant() {
  const rep=parseFloat(document.getElementById('mnt-rep').value)||0;
  const mo=parseFloat(document.getElementById('mnt-mo').value)||0;
  tx('mnt-total-disp', fmt.sol(rep+mo));
}

async function guardarMant() {
  const id=document.getElementById('mnt-id').value;
  const payload={
    id_unidad:          document.getElementById('mnt-unidad').value,
    fecha_ejecucion:    document.getElementById('mnt-fecha').value,
    tipo_mantenimiento: document.getElementById('mnt-tipo').value,
    km_registro:        document.getElementById('mnt-km').value||null,
    horometro_registro: document.getElementById('mnt-horom').value||null,
    descripcion_trabajo:document.getElementById('mnt-desc').value,
    marca:              document.getElementById('mnt-marca').value||null,
    costo_repuestos:    document.getElementById('mnt-rep').value||0,
    costo_mano_obra:    document.getElementById('mnt-mo').value||0,
  };
  if (!payload.id_unidad||!payload.descripcion_trabajo) { toast('Unidad y descripción son obligatorios','error'); return; }
  try {
    if (id) {
      await api(`/api/mantenimiento/historial/${id}`,{method:'PUT',body:JSON.stringify(payload)});
      toast('Mantenimiento actualizado','ok');
    } else {
      await api('/api/mantenimiento/historial',{method:'POST',body:JSON.stringify(payload)});
      toast('Mantenimiento registrado','ok');
    }
    cerrarModal('modal-mant'); cargarHistorial(); cargarAlertas();
  } catch(e) { toast('Error: '+e.message,'error'); }
}

function confirmarDelMant(id) {
  document.getElementById('btn-del-mant').onclick=async()=>{
    try {
      await api(`/api/mantenimiento/historial/${id}`,{method:'DELETE'});
      toast('Eliminado','ok'); cerrarModal('modal-del-mant'); cargarHistorial();
    } catch(e) { toast('Error: '+e.message,'error'); }
  };
  abrirModal('modal-del-mant');
}

// ── Modales documentos ────────────────────────────────────────
function abrirModalDoc() {
  document.getElementById('doc-unidad').value='';
  document.getElementById('doc-tipo').value='SOAT';
  document.getElementById('doc-emision').value='';
  document.getElementById('doc-vence').value='';
  document.getElementById('doc-dias').value='30';
  abrirModal('modal-doc');
}
async function guardarDoc() {
  const payload={
    id_unidad:       document.getElementById('doc-unidad').value,
    tipo_documento:  document.getElementById('doc-tipo').value,
    fecha_emision:   document.getElementById('doc-emision').value||null,
    fecha_vencimiento:document.getElementById('doc-vence').value,
    alerta_dias_antes:document.getElementById('doc-dias').value||30,
  };
  if (!payload.id_unidad||!payload.fecha_vencimiento) { toast('Completa los campos obligatorios','error'); return; }
  try {
    await api('/api/mantenimiento/documentos',{method:'POST',body:JSON.stringify(payload)});
    toast('Documento registrado','ok'); cerrarModal('modal-doc'); cargarDocumentos();
  } catch(e) { toast('Error: '+e.message,'error'); }
}
async function eliminarDoc(id) {
  if (!confirm('¿Eliminar este documento?')) return;
  try {
    await api(`/api/mantenimiento/documentos/${id}`,{method:'DELETE'});
    toast('Eliminado','ok'); cargarDocumentos();
  } catch(e) { toast('Error: '+e.message,'error'); }
}

// ════════════════════════════════════════════════════════
// ANÁLISIS DE DURABILIDAD DE REPUESTOS
// ════════════════════════════════════════════════════════

let analisisData = [];

async function cargarAnalisisRepuestos() {
  const id_u = document.getElementById('mth-unidad').value;
  const tipo  = document.getElementById('mth-tipo-mant').value;
  let url = '/api/mantenimiento/analisis-repuestos';
  const params = [];
  if (id_u) params.push('id_unidad='+id_u);
  if (tipo)  params.push('tipo_mant='+encodeURIComponent(tipo));
  if (params.length) url += '?' + params.join('&');

  const data = await api(url);
  analisisData = data;

  // Renderizar resumen
  const wrap = document.getElementById('analisis-resumen');
  if (!data.resumen.length) {
    wrap.innerHTML = '<div class="empty-state" style="padding:16px">Sin suficientes registros para calcular durabilidad.</div>';
    return;
  }

  wrap.innerHTML = data.resumen.map(r => `
    <div class="kpi-card">
      <div class="kpi-lbl">${r.placa} — ${r.tipo}</div>
      <div style="font-size:13px;font-weight:700;color:var(--brand)">${r.n_cambios} cambios</div>
      <div style="font-size:12px;margin-top:6px">
        ${r.dias_prom ? `<div>Duración prom: <strong>${r.dias_prom} días</strong></div>` : ''}
        ${r.km_prom   ? `<div>km prom: <strong>${fmt.num(r.km_prom,0)} km</strong></div>` : ''}
        ${r.dias_min||r.dias_max ? `<div class="text-muted text-xs">Rango: ${r.dias_min||'—'} → ${r.dias_max||'—'} días</div>` : ''}
        ${r.km_min||r.km_max ? `<div class="text-muted text-xs">km: ${r.km_min||'—'} → ${r.km_max||'—'}</div>` : ''}
        <div style="margin-top:4px">Costo prom: <strong>${fmt.sol(r.costo_prom||0)}</strong> · Total: ${fmt.sol(r.costo_total)}</div>
      </div>
    </div>`).join('');

  // Tabla detalle con duración calculada
  const tbody = document.getElementById('analisis-tbody');
  const detalle = data.detalle.filter(d => d.dias_duracion !== null);
  if (!detalle.length) { tbody.innerHTML = '<tr><td colspan="7" class="empty">Sin historial comparativo.</td></tr>'; return; }
  tbody.innerHTML = detalle.map(d => `<tr>
    <td>${fmtFecha(d.fecha_ejecucion)}</td>
    <td><strong>${d.placa}</strong></td>
    <td>${d.tipo_mantenimiento}</td>
    <td>${d.km_registro ? fmt.num(d.km_registro,0)+' km' : '—'}</td>
    <td>${d.dias_duracion !== null ? `<strong style="color:${d.dias_duracion<60?'var(--danger)':d.dias_duracion<120?'var(--warn)':'var(--ok)'}">${d.dias_duracion} días</strong>` : '—'}</td>
    <td>${d.km_duracion !== null ? fmt.num(d.km_duracion,0)+' km' : '—'}</td>
    <td>${fmt.sol(d.costo_total_soles)}</td>
  </tr>`).join('');
}

async function exportarMant() {
  if (!mantData.length) { toast('Sin datos','warn'); return; }
  const ws=XLSX.utils.json_to_sheet(mantData.map(m=>({
    'Fecha':m.fecha_ejecucion,'Unidad':m.placa,'Tipo':m.tipo_mantenimiento,
    'km':m.km_registro??'','Horóm.':m.horometro_registro??'',
    'Descripción':m.descripcion_trabajo,'Marca':m.marca??'',
    'Repuestos S/':m.costo_repuestos,'Mano obra S/':m.costo_mano_obra,'Total S/':m.costo_total_soles,
  })));
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,ws,'Mantenimiento');
  XLSX.writeFile(wb,`Mantenimiento_${new Date().toISOString().slice(0,10)}.xlsx`);
  toast('Excel generado','ok');
}
</script>