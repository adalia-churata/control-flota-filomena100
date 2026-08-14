<?php // views/reportes.php ?>
<!-- ══ REPORTES ═════════════════════════════════════════════ -->
<div class="flex items-center justify-between mb-12">
  <h1 style="font-size:18px;font-weight:700">Reportes</h1>
  <div style="display:flex;gap:8px;align-items:center">
    <label style="font-size:12px">Desde <input type="date" id="rp-desde" value="<?= date('Y-m-01') ?>" style="margin-left:4px;padding:5px 8px;border:1px solid var(--border2);border-radius:6px;font-size:12px"/></label>
    <label style="font-size:12px">Hasta <input type="date" id="rp-hasta" value="<?= date('Y-m-d') ?>"  style="margin-left:4px;padding:5px 8px;border:1px solid var(--border2);border-radius:6px;font-size:12px"/></label>
  </div>
</div>

<!-- Tarjetas de reportes disponibles -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;margin-bottom:var(--gap)">

  <!-- Reporte 1: Consumo completo combustible -->
  <div class="card" style="display:flex;flex-direction:column;gap:10px">
    <div style="font-size:15px;font-weight:700">🛢 Combustible — Reporte completo</div>
    <div style="font-size:13px;color:var(--text2)">
      Todas las compras con subtotal, IGV, total, grifo, unidad, km, forma de pago.
      Incluye movimientos de crédito y saldo actual.
    </div>
    <button class="btn btn-primary btn-sm" onclick="exportCombustible()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Exportar Excel
    </button>
  </div>

  <!-- Reporte 2: Control de flota completo -->
  <div class="card" style="display:flex;flex-direction:column;gap:10px">
    <div style="font-size:15px;font-weight:700">🚛 Flota — Control de viajes</div>
    <div style="font-size:13px;color:var(--text2)">
      Todos los viajes con conductor, ruta, km salida/retorno, km recorrido, desviación,
      estado margen y galones asignados.
    </div>
    <button class="btn btn-primary btn-sm" onclick="exportFlota()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Exportar Excel
    </button>
  </div>

  <!-- Reporte 3: Rendimiento por unidad -->
  <div class="card" style="display:flex;flex-direction:column;gap:10px">
    <div style="font-size:15px;font-weight:700">📊 Rendimiento por unidad</div>
    <div style="font-size:13px;color:var(--text2)">
      km/galón por vehículo, gal/hora por maquinaria y GE, costos totales de combustible
      y mantenimiento por unidad.
    </div>
    <button class="btn btn-primary btn-sm" onclick="exportRendimiento()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Exportar Excel
    </button>
  </div>

  <!-- Reporte 4: Maquinaria pesada -->
  <div class="card" style="display:flex;flex-direction:column;gap:10px">
    <div style="font-size:15px;font-weight:700">⚙️ Maquinaria — Control diario</div>
    <div style="font-size:13px;color:var(--text2)">
      Control por día de cada máquina: operador, horómetros, horas productivas vs ralentí,
      actividades realizadas y combustible asignado.
    </div>
    <button class="btn btn-primary btn-sm" onclick="exportMaquinaria()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Exportar Excel
    </button>
  </div>

  <!-- Reporte 5: Grupos electrógenos -->
  <div class="card" style="display:flex;flex-direction:column;gap:10px">
    <div style="font-size:15px;font-weight:700">⚡ Grupos Electrógenos</div>
    <div style="font-size:13px;color:var(--text2)">
      Registros de Perkins y Cattini: CI/CF porcentaje, galones consumidos, gal/hora,
      factor de carga, kWh estimados y kardex de bidón.
    </div>
    <button class="btn btn-primary btn-sm" onclick="exportGE()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Exportar Excel
    </button>
  </div>

  <!-- Reporte 6: Mantenimiento -->
  <div class="card" style="display:flex;flex-direction:column;gap:10px">
    <div style="font-size:15px;font-weight:700">🔧 Mantenimiento y documentos</div>
    <div style="font-size:13px;color:var(--text2)">
      Historial de mantenimientos con costos totales por unidad, estado de documentos
      (SOAT, RV, seguros) con días restantes.
    </div>
    <button class="btn btn-primary btn-sm" onclick="exportMantenimiento()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Exportar Excel
    </button>
  </div>

  <!-- Reporte 7: Ejecutivo completo para gerencia -->
  <div class="card" style="display:flex;flex-direction:column;gap:10px;border:2px solid var(--brand)">
    <div style="font-size:15px;font-weight:700;color:var(--brand)">📋 Resumen Ejecutivo (Gerencia)</div>
    <div style="font-size:13px;color:var(--text2)">
      Excel multi-hoja: KPIs generales, consumo por unidad, rendimiento flota y maquinaria,
      costos totales, alertas. Listo para presentar a la dirección.
    </div>
    <button class="btn btn-primary btn-sm" onclick="exportEjecutivo()" style="background:var(--brand)">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Exportar Resumen Ejecutivo
    </button>
  </div>

</div>

<!-- Vista previa de datos cargados -->
<div class="card full">
  <div class="card-title">Vista previa — KPIs del período</div>
  <div class="kpi-grid" id="rp-kpis">
    <div class="empty-state" style="padding:20px">Haz clic en cualquier reporte para cargarlo.</div>
  </div>
</div>

<div id="rp-estado" style="display:none;background:var(--ok-lt);border:1px solid var(--ok);border-radius:8px;padding:12px 16px;font-size:13px;margin-top:var(--gap);color:var(--ok)">
  ✅ <span id="rp-estado-msg"></span>
</div>

<script>
const fd = () => document.getElementById('rp-desde').value;
const fh = () => document.getElementById('rp-hasta').value;

function rpEstado(msg) {
  const el=document.getElementById('rp-estado');
  tx('rp-estado-msg',msg); el.style.display='block';
  setTimeout(()=>el.style.display='none',4000);
}

async function cargarKPIsPreview() {
  const kpis = await api(`/api/dashboard/kpis?fecha_desde=${fd()}&fecha_hasta=${fh()}`);
  const wrap = document.getElementById('rp-kpis');
  wrap.innerHTML = `
    <div class="kpi-card"><div class="kpi-lbl">Viajes</div><div class="kpi-val brand">${fmt.num(kpis.viajes,0)}</div></div>
    <div class="kpi-card"><div class="kpi-lbl">km Total</div><div class="kpi-val brand">${fmt.num(kpis.km_total,0)} km</div></div>
    <div class="kpi-card"><div class="kpi-lbl">Galones comprados</div><div class="kpi-val brand">${fmt.num(kpis.total_galones,1)} gll</div></div>
    <div class="kpi-card"><div class="kpi-lbl">Gasto combustible</div><div class="kpi-val">${fmt.sol(kpis.total_soles)}</div></div>
    <div class="kpi-card"><div class="kpi-lbl">Saldo grifo</div><div class="kpi-val ${kpis.saldo_grifo<0?'danger':'ok'}">${fmt.sol(kpis.saldo_grifo)}</div></div>
    <div class="kpi-card"><div class="kpi-lbl">Alertas docs</div><div class="kpi-val danger">${kpis.alertas_doc}</div></div>
  `;
}

document.addEventListener('DOMContentLoaded', () => cargarKPIsPreview());
document.getElementById('rp-desde').onchange = cargarKPIsPreview;
document.getElementById('rp-hasta').onchange = cargarKPIsPreview;

// ── Exportadores ──────────────────────────────────────────────
async function exportCombustible() {
  toast('Generando…','warn');
  const [compras,movs]=await Promise.all([api(`/api/compras?fecha_desde=${fd()}&fecha_hasta=${fh()}`),api('/api/compras/movimientos')]);
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(compras.map(c=>({'Fecha':c.fecha,'Grifo':c.grifo_nombre||'—','Tipo compr.':c.tipo_comprobante||'—','Nro':c.nro_comprobante||'—','Unidad':c.placa||'Bidón','Tipo combustible':c.tipo_combustible,'Galones':parseFloat(c.cantidad_gll),'Precio unit.':parseFloat(c.precio_unitario),'Subtotal':parseFloat(c.subtotal),'IGV':parseFloat(c.igv),'Total':parseFloat(c.total),'km/Horóm.':c.km_vehiculo??'','Forma pago':c.forma_pago,'Tanqueo':c.tanqueo==1?'Sí':'No'}))),'Compras');
  XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(movs.map(m=>({'Fecha':m.fecha,'Tipo':m.tipo,'Descripción':m.descripcion,'Monto S/':parseFloat(m.monto),'Saldo S/':parseFloat(m.saldo)}))),'Movimientos Grifo');
  XLSX.writeFile(wb,`Combustible_${fd()}_${fh()}.xlsx`);
  rpEstado(`Reporte combustible generado: ${compras.length} compras exportadas`);
}

async function exportFlota() {
  toast('Generando…','warn');
  const data=await api(`/api/garita/viajes?fecha_desde=${fd()}&fecha_hasta=${fh()}`);
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(data.map(v=>({'Fecha':v.fecha,'Unidad':v.placa,'Conductor':v.conductor_nombre||'—','Ruta':v.destino?`${v.origen||''} → ${v.destino}`:'—','Actividad':v.tipo_actividad||'—','H. Salida':v.hora_salida||'—','H. Regreso':v.hora_regreso||'—','km Salida':v.km_salida??'','km Retorno':v.km_retorno??'','km Recorrido':v.km_recorrido??'','Desviación':v.desviacion??'','Estado':v.estado_ruta||'—','Observación':v.observacion||'—'}))),'Control Flota');
  XLSX.writeFile(wb,`Flota_${fd()}_${fh()}.xlsx`);
  rpEstado(`Reporte flota generado: ${data.length} viajes exportados`);
}

async function exportRendimiento() {
  toast('Generando…','warn');
  const rend=await api('/api/compras/rendimiento');
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(rend.flota.map(r=>({'Unidad':r.placa,'Tipo':'Flota','Viajes':r.n_viajes,'km Total':parseFloat(r.km_totales||0),'Galones':parseFloat(r.gll_totales||0),'km/galón':parseFloat(r.km_por_galon||0),'Costo comb. S/':parseFloat(r.costo_total_soles||0)}))),'Flota km-gal');
  XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(rend.maquinaria.map(r=>({'Unidad':r.placa,'Tipo':'Maquinaria','Horas':parseFloat(r.horas_totales||0),'H.Ralentí':parseFloat(r.horas_ralenti||0),'Galones':parseFloat(r.gll_totales||0),'gal/hora':parseFloat(r.gal_por_hora||0),'Costo S/':parseFloat(r.costo_soles||0)}))),'Maquinaria gal-hora');
  XLSX.writeFile(wb,`Rendimiento_${fd()}_${fh()}.xlsx`);
  rpEstado('Reporte rendimiento generado');
}

async function exportMaquinaria() {
  toast('Generando…','warn');
  const [dias,acts]=await Promise.all([api(`/api/maquinaria/dias?fecha_desde=${fd()}&fecha_hasta=${fh()}`),api('/api/maquinaria/actividades?id_control_dia=0').catch(()=>[])]);
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(dias.map(d=>({'Fecha':d.fecha,'Máquina':d.placa,'Operador':d.conductor_nombre||'—','Horóm.Ini':d.horometro_inicio??'','Horóm.Fin':d.horometro_final??'','Horas':d.horas_horometro??'','H.Ralentí':d.horas_ralenti??0,'Combustible asig.':d.id_combustible?'Sí':'No'}))),'Control Diario');
  XLSX.writeFile(wb,`Maquinaria_${fd()}_${fh()}.xlsx`);
  rpEstado(`Maquinaria exportada: ${dias.length} días`);
}

async function exportGE() {
  toast('Generando…','warn');
  const [regs,kardex]=await Promise.all([api(`/api/ge/registros?fecha_desde=${fd()}&fecha_hasta=${fh()}`),api('/api/ge/kardex?id_unidad=0').catch(()=>[])]);
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(regs.map(r=>({'Fecha':r.fecha,'Hora':r.hora,'GE':r.placa,'CI%':r.ci_porcentaje,'CF%':r.cf_porcentaje,'Gal.echados':r.galones_echados,'Horómetro':r.horometro,'Horas':r.horas_trabajadas,'Gal.cons.':r.galones_consumidos,'Gal/hora':r.consumo_gal_hora,'Factor carga':r.factor_carga,'kWh est.':r.kwh_estimados}))),'Registros GE');
  XLSX.writeFile(wb,`GruposElectrogenos_${fd()}_${fh()}.xlsx`);
  rpEstado(`GE exportado: ${regs.length} registros`);
}

async function exportMantenimiento() {
  toast('Generando…','warn');
  const [mant,docs]=await Promise.all([api('/api/mantenimiento/historial'),api('/api/mantenimiento/documentos')]);
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(mant.map(m=>({'Fecha':m.fecha_ejecucion,'Unidad':m.placa,'Tipo':m.tipo_mantenimiento,'km':m.km_registro??'','Horóm.':m.horometro_registro??'','Descripción':m.descripcion_trabajo,'Marca':m.marca??'','Repuestos S/':parseFloat(m.costo_repuestos||0),'Mano obra S/':parseFloat(m.costo_mano_obra||0),'Total S/':parseFloat(m.costo_total_soles||0)}))),'Historial Mant.');
  XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(docs.map(d=>({'Unidad':d.placa,'Documento':d.tipo_documento,'Emisión':d.fecha_emision,'Vencimiento':d.fecha_vencimiento,'Días restantes':d.dias_restantes,'Estado':d.dias_restantes<0?'VENCIDO':d.dias_restantes<=d.alerta_dias_antes?'ALERTA':'VIGENTE'}))),'Documentos');
  XLSX.writeFile(wb,`Mantenimiento_${fd()}_${fh()}.xlsx`);
  rpEstado(`Mantenimiento exportado: ${mant.length} registros`);
}

async function exportEjecutivo() {
  toast('Generando reporte ejecutivo…','warn');
  const [kpis,rend,consumo,mant]=await Promise.all([
    api(`/api/dashboard/kpis?fecha_desde=${fd()}&fecha_hasta=${fh()}`),
    api('/api/compras/rendimiento'),
    api(`/api/dashboard/consumo-por-unidad?fecha_desde=${fd()}&fecha_hasta=${fh()}`),
    api('/api/mantenimiento/historial'),
  ]);
  const wb=XLSX.utils.book_new();
  const wsR=XLSX.utils.aoa_to_sheet([
    ['FLEETCONTROL PRO — RESUMEN EJECUTIVO'],
    [`Período: ${fd()} al ${fh()}`],[],
    ['INDICADOR','VALOR'],
    ['Total viajes flota',kpis.viajes],
    ['km Total recorrido',kpis.km_total],
    ['Galones comprados',kpis.total_galones],
    ['Gasto combustible S/',kpis.total_soles],
    ['Saldo en grifo S/',kpis.saldo_grifo],
    ['Alertas de documentos',kpis.alertas_doc],
  ]);
  XLSX.utils.book_append_sheet(wb,wsR,'Resumen');
  XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(consumo.map(r=>({'Unidad':r.placa,'Tipo':r.tipo_unidad,'Galones':parseFloat(r.galones||0),'Soles S/':parseFloat(r.soles||0),'% del total':parseFloat(r.porcentaje||0)}))),'Consumo por Unidad');
  XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(rend.flota.map(r=>({'Unidad':r.placa,'Viajes':r.n_viajes,'km':parseFloat(r.km_totales||0),'Galones':parseFloat(r.gll_totales||0),'km/galón':parseFloat(r.km_por_galon||0),'Costo S/':parseFloat(r.costo_total_soles||0)}))),'Rendimiento Flota');
  XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(rend.maquinaria.map(r=>({'Unidad':r.placa,'Horas':parseFloat(r.horas_totales||0),'Galones':parseFloat(r.gll_totales||0),'gal/hora':parseFloat(r.gal_por_hora||0),'Costo S/':parseFloat(r.costo_soles||0)}))),'Rendimiento Maquinaria');
  // Costo mantenimiento por unidad
  const costMap={};
  mant.forEach(m=>{if(!costMap[m.placa])costMap[m.placa]=0;costMap[m.placa]+=(parseFloat(m.costo_total_soles||0));});
  XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(Object.entries(costMap).map(([p,c])=>({'Unidad':p,'Costo mantenimiento S/':c}))),'Costos Mantenimiento');
  XLSX.writeFile(wb,`ResumenEjecutivo_${fd()}_${fh()}.xlsx`);
  rpEstado('Resumen ejecutivo generado — 5 hojas listas para presentar');
}
</script>