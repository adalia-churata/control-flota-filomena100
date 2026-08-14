<?php $base = rtrim(APP_BASE, '/'); ?>

<div class="flex items-center justify-between mb-12">
  <h1 style="font-size:18px;font-weight:700">Dashboard</h1>
  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <label style="font-size:12px">Desde
      <input type="date" id="d-desde" value="<?= date('Y-m-01') ?>"
             style="margin-left:4px;padding:5px 8px;border:1px solid var(--border2);border-radius:6px;font-size:12px"
             onchange="recargarTodo()"/>
    </label>
    <label style="font-size:12px">Hasta
      <input type="date" id="d-hasta" value="<?= date('Y-m-d') ?>"
             style="margin-left:4px;padding:5px 8px;border:1px solid var(--border2);border-radius:6px;font-size:12px"
             onchange="recargarTodo()"/>
    </label>
    <!-- Filtro grifo -->
    <select id="f-grifo" onchange="recargarTodo()"
            style="font-size:12px;padding:5px 8px;border:1px solid var(--border2);border-radius:6px">
      <option value="">Todos los grifos</option>
    </select>
    <!-- Filtro tipo actividad -->
    <select id="f-actividad" onchange="recargarTodo()"
            style="font-size:12px;padding:5px 8px;border:1px solid var(--border2);border-radius:6px">
      <option value="">Todas las actividades</option>
      <option value="ACOPIO">Acopio</option>
      <option value="LOGISTICA">Logística</option>
      <option value="AGUA">Agua</option>
      <option value="MANTENIMIENTO">Mantenimiento</option>
      <option value="VENTA DE MINERAL">Venta de Mineral</option>
      <option value="PAD">PAD</option>
    </select>
    <button class="btn btn-outline btn-sm" onclick="exportarResumen()">📥 Excel</button>
  </div>
</div>

<!-- ═══ KPIs ═════════════════════════════════════════════════ -->
<div class="kpi-grid" style="margin-bottom:var(--gap)">
  <div class="kpi-card">
    <div class="kpi-lbl">Viajes registrados</div>
    <div class="kpi-val brand" id="kpi-viajes">—</div>
    <div class="kpi-sub" id="kpi-viajes-sub">en el periodo</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-lbl">Gasto pagado al grifo</div>
    <div class="kpi-val" id="kpi-soles">—</div>
    <div class="kpi-sub" id="kpi-soles-sub">— gll comprados</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-lbl">Saldo disponible en grifo</div>
    <div class="kpi-val" id="kpi-saldo">—</div>
    <div class="kpi-sub">crédito restante</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-lbl">Precio promedio</div>
    <div class="kpi-val brand" id="kpi-pu">—</div>
    <div class="kpi-sub">soles por galón</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-lbl">Alertas documentos</div>
    <div class="kpi-val danger" id="kpi-alertas">—</div>
    <div class="kpi-sub">por vencer / vencidos</div>
  </div>
</div>

<!-- Gasto por grifo (cuando hay más de uno) -->
<div id="gasto-grifo-wrap" style="display:none;margin-bottom:var(--gap)">
  <div style="font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">
    Desglose por grifo
  </div>
  <div id="gasto-grifo-cards" style="display:flex;gap:var(--gap);flex-wrap:wrap"></div>
</div>

<!-- ═══ FILA 1: Semanal + Dona ═══════════════════════════════ -->
<div class="row" style="margin-bottom:var(--gap)">
  <div class="card w2">
    <div class="card-title">
      Gasto semanal en combustible (S/)
      <div class="tab-pills" style="margin-bottom:0">
        <button class="tab-pill active" data-tipo="all"               onclick="filtrarSemanal('all',this)">Todos</button>
        <button class="tab-pill"        data-tipo="FLOTA"             onclick="filtrarSemanal('FLOTA',this)">Flota</button>
        <button class="tab-pill"        data-tipo="MAQ. PESADA" onclick="filtrarSemanal('MAQ. PESADA',this)">Maquinaria</button>
        <button class="tab-pill"        data-tipo="GRUPO ELECTROGENO" onclick="filtrarSemanal('GRUPO ELECTROGENO',this)">GE</button>
      </div>
    </div>
    <div class="chart-box" style="height:230px"><canvas id="chart-semanal"></canvas></div>
  </div>
  <div class="card w1">
    <div class="card-title">% Gasto por unidad</div>
    <div class="tab-pills">
      <button class="tab-pill active" onclick="cambiarDona('FLOTA',this)">Flota</button>
      <button class="tab-pill"        onclick="cambiarDona('MAQ. PESADA',this)">Maquinaria</button>
      <button class="tab-pill"        onclick="cambiarDona('GRUPO ELECTROGENO',this)">GE</button>
    </div>
    <div class="chart-box" style="height:200px"><canvas id="chart-dona"></canvas></div>
    <div id="dona-total" class="chart-hint"></div>
  </div>
</div>

<!-- ═══ FILA 2: Línea temporal de tanqueos por unidad ════════ -->
<div class="card full" style="margin-bottom:var(--gap)">
  <div class="card-title">
    📈 Frecuencia de tanqueos por unidad — línea temporal
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <select id="linea-unidad" onchange="cargarLineaTanqueos()"
              style="font-size:12px;padding:4px 8px;border:1px solid var(--border2);border-radius:6px">
        <option value="">Todas las unidades</option>
      </select>
      <span class="text-muted text-sm">Click en un punto para ver el detalle de ese tanqueo</span>
    </div>
  </div>
  <div class="chart-box" style="height:240px"><canvas id="chart-linea-tanqueos"></canvas></div>

  <!-- Panel detalle tanqueo seleccionado -->
  <div id="panel-tanqueo-detalle" style="display:none;margin-top:12px">
    <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px 16px">
      <!-- Cabecera del tanqueo -->
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;margin-bottom:12px">
        <div>
          <div style="font-size:15px;font-weight:700" id="det-titulo">—</div>
          <div style="font-size:12px;color:var(--text2)" id="det-sub">—</div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <div class="kpi-card" style="padding:10px 14px;min-width:120px">
            <div class="kpi-lbl">Galones</div>
            <div class="kpi-val brand" id="det-gll" style="font-size:18px">—</div>
          </div>
          <div class="kpi-card" style="padding:10px 14px;min-width:120px">
            <div class="kpi-lbl">Monto pagado</div>
            <div class="kpi-val" id="det-soles" style="font-size:18px">—</div>
          </div>
          <div class="kpi-card" style="padding:10px 14px;min-width:120px">
            <div class="kpi-lbl">km/galón (bloque)</div>
            <div class="kpi-val ok" id="det-kmgal" style="font-size:18px">—</div>
          </div>
          <div class="kpi-card" style="padding:10px 14px;min-width:120px">
            <div class="kpi-lbl">Viajes cubiertos</div>
            <div class="kpi-val brand" id="det-nviajes" style="font-size:18px">—</div>
          </div>
        </div>
      </div>

      <!-- Viajes de ese tanqueo -->
      <div style="font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;margin-bottom:8px">
        Viajes que consumieron este combustible
      </div>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th>Fecha</th><th>Conductor</th><th>Ruta / Destino</th>
              <th>Actividad</th><th>km Rec.</th><th>Gal. est.</th><th>Observación</th>
            </tr>
          </thead>
          <tbody id="det-viajes-tbody">
            <tr><td colspan="7" class="empty">Selecciona un tanqueo del gráfico.</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Gráfico de actividades de ese bloque -->
      <div style="display:flex;gap:var(--gap);margin-top:14px;flex-wrap:wrap">
        <div style="flex:1;min-width:200px">
          <div style="font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;margin-bottom:8px">
            Actividades en este tanqueo
          </div>
          <div id="det-actividades" style="display:flex;flex-direction:column;gap:6px"></div>
        </div>
        <div style="flex:1;min-width:200px">
          <div class="chart-box" style="height:150px"><canvas id="chart-det-actividades"></canvas></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══ FILA 3: Rendimiento por tanqueo + Actividades ════════ -->
<div class="row" style="margin-bottom:var(--gap)">
  <div class="card w2">
    <div class="card-title">
      km/galón por bloque de tanqueo
      <select id="rend-unidad" onchange="cargarRendimientoDiario()"
              style="font-size:12px;padding:4px 8px;border:1px solid var(--border2);border-radius:6px">
        <option value="">Todas las unidades</option>
      </select>
    </div>
    <div class="kpi-grid" style="margin-bottom:10px">
      <div class="kpi-card"><div class="kpi-lbl">km/galón promedio</div><div class="kpi-val ok" id="rv-kmpgal">—</div></div>
      <div class="kpi-card"><div class="kpi-lbl">Tanqueos</div><div class="kpi-val brand" id="rv-n">—</div></div>
      <div class="kpi-card"><div class="kpi-lbl">Gasto total</div><div class="kpi-val" id="rv-costo">—</div></div>
    </div>
    <div class="chart-box" style="height:200px"><canvas id="chart-rend-diario"></canvas></div>
    <p class="chart-hint">Verde ≥10 km/gal · Naranja ≥7 · Rojo &lt;7 · Click para ver viajes</p>
  </div>

  <div class="card w1">
    <div class="card-title">Gasto por tipo de actividad</div>
    <div class="chart-box" style="height:200px"><canvas id="chart-actividad"></canvas></div>
    <div id="act-tabla" style="margin-top:10px;font-size:12px;display:flex;flex-direction:column;gap:4px"></div>
  </div>
</div>

<!-- ═══ FILA 3.5: Viajes por actividad ══════════════════════ -->
<div class="card full" style="margin-bottom:var(--gap)">
  <div class="card-title">
    🚛 Viajes por tipo de actividad
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <select id="act-filtro-tipo" onchange="cargarViajesActividad()"
              style="font-size:12px;padding:4px 8px;border:1px solid var(--border2);border-radius:6px">
        <option value="">Todas las actividades</option>
        <option value="ACOPIO">Acopio</option>
        <option value="LOGISTICA">Logística</option>
        <option value="AGUA">Agua</option>
        <option value="MANTENIMIENTO">Mantenimiento</option>
        <option value="VENTA DE MINERAL">Venta de Mineral</option>
        <option value="PAD">PAD</option>
      </select>
      <select id="act-filtro-unidad" onchange="cargarViajesActividad()"
              style="font-size:12px;padding:4px 8px;border:1px solid var(--border2);border-radius:6px">
        <option value="">Todas las unidades</option>
      </select>
      <span class="text-muted text-sm" id="act-count"></span>
    </div>
  </div>
  <div class="row" style="margin-bottom:10px">
    <div style="flex:1;min-width:220px">
      <div class="chart-box" style="height:220px"><canvas id="chart-viajes-act"></canvas></div>
    </div>
    <div style="flex:2;min-width:280px">
      <div id="act-resumen-tabla" style="display:flex;flex-direction:column;gap:6px"></div>
    </div>
  </div>
  <!-- Tabla de viajes filtrada -->
  <div id="act-viajes-panel" style="display:none;margin-top:10px">
    <div style="font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;margin-bottom:8px"
         id="act-viajes-titulo">Viajes</div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr><th>Fecha</th><th>Unidad</th><th>Conductor</th><th>Actividad</th>
              <th>Ruta</th><th>km Rec.</th><th>Hora salida</th><th>Observación</th></tr>
        </thead>
        <tbody id="act-viajes-tbody"><tr><td colspan="8" class="empty">Selecciona una actividad.</td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ═══ FILA 4: Alertas + Documentos + Viajes hoy ════════════ -->
<div class="row" style="margin-bottom:var(--gap)">
  <div class="card w1">
    <div class="card-title">🔧 Mantenimiento
      <a href="<?= $base ?>/mantenimiento" class="btn btn-ghost btn-sm">Ver →</a>
    </div>
    <div id="mant-alertas" class="sem-list"><div class="empty-state" style="padding:12px">Cargando…</div></div>
  </div>
  <div class="card w1">
    <div class="card-title">📄 Documentos críticos
      <a href="<?= $base ?>/mantenimiento" class="btn btn-ghost btn-sm">Ver →</a>
    </div>
    <div id="docs-semaforo" style="display:flex;flex-direction:column;gap:8px"><div class="empty-state" style="padding:12px">Cargando…</div></div>
  </div>
  <div class="card w1">
    <div class="card-title">🚛 Viajes de hoy
      <a href="<?= $base ?>/garita" class="btn btn-ghost btn-sm">Ver →</a>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr><th>Unidad</th><th>Actividad</th><th>km Rec.</th><th>Estado</th></tr></thead>
        <tbody id="viajes-hoy"><tr><td colspan="4" class="empty">Cargando…</td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ═══ FILA 5: GE ═══════════════════════════════════════════ -->
<div class="row">
  <div class="card w2">
    <div class="card-title">⚡ Grupos Electrógenos — gal/hora (últimas 2 semanas)</div>
    <div class="chart-box" style="height:200px"><canvas id="chart-ge"></canvas></div>
    <p class="chart-hint">Puntos rojos ≥4 gal/h · Click para causa raíz</p>
  </div>
  <div class="card w1">
    <div class="card-title">Causa raíz GE</div>
    <div id="causa-raiz-body" class="empty-state" style="padding:12px;font-size:12px">
      Click en un punto rojo del gráfico.
    </div>
  </div>
</div>

<script>
/* ══ DASHBOARD — usa PAL, fmt, api, CHARTS, tx, dch, fmtFecha del header ══ */

let semanalRaw   = [];
let consumoURaw  = [];
let rendDiario   = [];
let lineaData    = [];  // tanqueos para línea temporal
let grifosCache  = [];

// ── helpers de filtro ────────────────────────────────────────
const fd  = () => document.getElementById('d-desde').value;
const fh  = () => document.getElementById('d-hasta').value;
const fgr = () => document.getElementById('f-grifo').value;
const fac = () => document.getElementById('f-actividad').value;

document.addEventListener('DOMContentLoaded', () => {
  cargarCatalogos().then(() => recargarTodo());
});

async function cargarCatalogos() {
  // Grifos
  try {
    grifosCache = await api('/api/grifos');
    const sel = document.getElementById('f-grifo');
    grifosCache.forEach(g => {
      sel.insertAdjacentHTML('beforeend',
        `<option value="${g.id_grifo}">${g.razon_social}</option>`);
    });
  } catch(_) {}

  // Unidades para selectores
  const us = await api('/api/unidades');
  const flota = us.filter(u => u.tipo_unidad === 'FLOTA');
  ['linea-unidad','rend-unidad','act-filtro-unidad'].forEach(selId => {
    const sel = document.getElementById(selId);
    if (!sel) return;
    us.forEach(u => sel.insertAdjacentHTML('beforeend',
      `<option value="${u.id_unidad}">${u.placa} (${u.tipo_unidad==='FLOTA'?'Flota':u.tipo_unidad==='MAQ. PESADA'?'Maquinaria':'GE'})</option>`));
  });
}

async function recargarTodo() {
  await Promise.all([
    cargarKPIs(),
    cargarSemanal(),
    cargarConsumoPorUnidad(),
    cargarLineaTanqueos(),
    cargarRendimientoDiario(),
    cargarAlertasMant(),
    cargarDocumentos(),
    cargarViajesHoy(),
    cargarGEChart(),
    cargarViajesActividad(),
  ]);
}

// ══════════════════════════════════════════════════════════════
// KPIs — gasto en soles, saldo grifo, precio promedio
// ══════════════════════════════════════════════════════════════
async function cargarKPIs() {
  let url = `/api/dashboard/kpis?fecha_desde=${fd()}&fecha_hasta=${fh()}`;
  if (fgr()) url += `&id_grifo=${fgr()}`;
  if (fac()) url += `&tipo_actividad=${fac()}`;

  const d = await api(url);

  tx('kpi-viajes', fmt.num(d.viajes, 0));
  tx('kpi-viajes-sub', fac() ? `actividad: ${fac()}` : 'en el periodo');

  const solesEl = document.getElementById('kpi-soles');
  solesEl.textContent = fmt.sol(d.total_soles);
  solesEl.className   = 'kpi-val';

  tx('kpi-soles-sub', fmt.num(d.total_galones, 1) + ' gll comprados');

  const saldoEl = document.getElementById('kpi-saldo');
  saldoEl.textContent = fmt.sol(d.saldo_grifo);
  saldoEl.className   = 'kpi-val ' + (d.saldo_grifo < 0 ? 'danger' : 'ok');

  // Precio promedio
  const pu = d.total_galones > 0 ? d.total_soles / d.total_galones : 0;
  tx('kpi-pu', fmt.sol(pu) + '/gll');

  tx('kpi-alertas', d.alertas_doc);

  // Desglose por grifo
  const wrap  = document.getElementById('gasto-grifo-wrap');
  const cards = document.getElementById('gasto-grifo-cards');
  if (d.por_grifo && d.por_grifo.length > 1) {
    wrap.style.display = 'block';
    cards.innerHTML = d.por_grifo.map((g, i) => `
      <div class="kpi-card" style="border-left:3px solid ${PAL[i % PAL.length]}">
        <div class="kpi-lbl">${g.grifo}</div>
        <div class="kpi-val" style="font-size:18px;color:${PAL[i % PAL.length]}">${fmt.sol(g.total_soles)}</div>
        <div class="kpi-sub">${fmt.num(g.total_gll, 1)} gll</div>
      </div>`).join('');
  } else if (d.por_grifo && d.por_grifo.length === 1) {
    wrap.style.display = 'block';
    cards.innerHTML = `
      <div style="font-size:13px;color:var(--text2)">
        Grifo principal: <strong>${d.por_grifo[0].grifo}</strong>
        · ${fmt.sol(d.por_grifo[0].total_soles)}
        · ${fmt.num(d.por_grifo[0].total_gll, 1)} gll
      </div>`;
  } else {
    wrap.style.display = 'none';
  }
}

// ══════════════════════════════════════════════════════════════
// CONSUMO SEMANAL — barras soles + línea galones
// ══════════════════════════════════════════════════════════════
async function cargarSemanal() {
  semanalRaw = await api('/api/dashboard/consumo-semanal');
  filtrarSemanal('all');
}

function filtrarSemanal(tipo, el) {
  if (el) {
    document.querySelectorAll('[data-tipo]').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
  }
  const filtered = tipo === 'all' ? semanalRaw : semanalRaw.filter(r => r.tipo_unidad === tipo);
  const map = {};
  filtered.forEach(r => {
    if (!map[r.semana]) map[r.semana] = { galones:0, soles:0, inicio: r.inicio_semana||'', fin: r.fin_semana_real||'' };
    map[r.semana].galones += parseFloat(r.galones || 0);
    map[r.semana].soles   += parseFloat(r.soles   || 0);
    if (r.inicio_semana) map[r.semana].inicio = r.inicio_semana;
    if (r.fin_semana_real) map[r.semana].fin   = r.fin_semana_real;
  });

  const semanas = Object.keys(map).sort();
  // Label: rango de fechas "01/07 → 07/07"
  const labels  = semanas.map(s => {
    const ini = map[s].inicio ? map[s].inicio.slice(5).replace('-','/') : '';
    const fin = map[s].fin    ? map[s].fin.slice(5).replace('-','/')    : '';
    return ini && fin ? `${ini}→${fin}` : 'S'+String(s).slice(-2);
  });
  const soles   = semanas.map(s => +map[s].soles.toFixed(2));
  const galones = semanas.map(s => +map[s].galones.toFixed(1));

  dch('semanal');
  const ctx = document.getElementById('chart-semanal'); if (!ctx) return;
  CHARTS.semanal = new Chart(ctx, {
    data: { labels, datasets: [
      { type:'bar',  label:'Gasto (S/)',  data:soles,   backgroundColor:'rgba(24,95,165,.75)', borderRadius:4, yAxisID:'y',  order:2 },
      { type:'line', label:'Galones',     data:galones, borderColor:'#D85A30', backgroundColor:'transparent', borderWidth:2, pointRadius:4, tension:.3, yAxisID:'y2', order:1 },
    ]},
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{ labels:{ font:{size:11}, color:'#5c5b55' }},
        tooltip:{ callbacks:{
          title: items => {
            const s = semanas[items[0].dataIndex];
            return `Semana ${s} · ${map[s].inicio} → ${map[s].fin}`;
          },
        }},
      },
      scales:{
        x:{  grid:{display:false}, ticks:{font:{size:10},color:'#9c9a92',maxRotation:35} },
        y:{  grid:{color:'rgba(0,0,0,.04)'}, ticks:{font:{size:11},color:'#185FA5'},  title:{display:true,text:'S/',font:{size:10}} },
        y2:{ position:'right', grid:{display:false}, ticks:{font:{size:11},color:'#D85A30'}, title:{display:true,text:'Gll',font:{size:10}} },
      },
    },
  });
}

// ══════════════════════════════════════════════════════════════
// DONA — % gasto en soles por unidad
// ══════════════════════════════════════════════════════════════
async function cargarConsumoPorUnidad() {
  consumoURaw = await api(`/api/dashboard/consumo-por-unidad?fecha_desde=${fd()}&fecha_hasta=${fh()}`);
  cambiarDona('FLOTA');
}

function cambiarDona(tipo, el) {
  if (el) {
    el.closest('.tab-pills')?.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
  }
  const data  = tipo === 'all'
    ? consumoURaw.filter(r => r.soles > 0)
    : consumoURaw.filter(r => r.tipo_unidad === tipo && r.soles > 0);
  const total = data.reduce((s, r) => s + parseFloat(r.soles), 0);
  if (!data.length) {
    const ctx=document.getElementById('chart-dona'); if(ctx){dch('dona');const c=ctx.getContext('2d');c.clearRect(0,0,ctx.width,ctx.height);}
    tx('dona-total', 'Sin datos para este tipo en el período');
    return;
  }
  tx('dona-total', 'Total gastado: ' + fmt.sol(total));
  dch('dona');
  const ctx = document.getElementById('chart-dona'); if (!ctx) return;
  CHARTS.dona = new Chart(ctx, {
    type: 'doughnut',
    data: { labels: data.map(r => r.placa),
            datasets: [{ data: data.map(r => +parseFloat(r.soles||0).toFixed(2)),
              backgroundColor: PAL.slice(0, data.length), borderWidth:2, borderColor:'#fff', hoverOffset:6 }] },
    options: { responsive:true, maintainAspectRatio:false, cutout:'55%',
      plugins:{ legend:{ position:'right', labels:{ font:{size:10}, color:'#5c5b55',
          generateLabels: ch => { const ds=ch.data.datasets[0], tot=ds.data.reduce((a,b)=>a+b,0);
            return ch.data.labels.map((l,i)=>({ text:`${l}  ${Math.round(ds.data[i]/tot*100)}%`, fillStyle:ds.backgroundColor[i], index:i })); }}},
        tooltip:{ callbacks:{ label: ctx => { const tot=ctx.dataset.data.reduce((a,b)=>a+b,0);
          return ` ${fmt.sol(ctx.raw)} (${Math.round(ctx.raw/tot*100)}%)`; }}},
      },
    },
  });
}

// ══════════════════════════════════════════════════════════════
// LÍNEA TEMPORAL DE TANQUEOS POR UNIDAD
// ══════════════════════════════════════════════════════════════
async function cargarLineaTanqueos() {
  let url = `/api/dashboard/compras-por-unidad-fecha?fecha_desde=${fd()}&fecha_hasta=${fh()}`;
  if (fgr()) url += `&id_grifo=${fgr()}`;
  const uid = document.getElementById('linea-unidad')?.value;
  if (uid) url += `&id_unidad=${uid}`;

  lineaData = await api(url);

  dch('linea-tanqueos');
  const ctx = document.getElementById('chart-linea-tanqueos'); if (!ctx) return;

  // Una serie por unidad
  const placas = [...new Set(lineaData.map(r => r.placa))];
  // Fechas únicas ordenadas
  const fechas  = [...new Set(lineaData.map(r => r.fecha))].sort();

  const datasets = placas.map((placa, i) => {
    const filas = lineaData.filter(r => r.placa === placa);
    // Puntos: (fecha, total_soles), null si no tanqueó ese día
    const puntos = fechas.map(f => {
      const fila = filas.find(r => r.fecha === f);
      return fila ? { x: f, y: parseFloat(fila.total), id: fila.id_combustible,
                      gll: fila.cantidad_gll, km: fila.km_vehiculo,
                      grifo: fila.grifo_nombre, nviajes: fila.n_viajes_asignados } : null;
    }).filter(p => p !== null);

    return {
      label: placa,
      data:  puntos.map(p => ({ x: p.x, y: p.y })),
      _extra: puntos,  // guardamos datos extra para el click
      borderColor:       PAL[i % PAL.length],
      backgroundColor:   PAL[i % PAL.length],
      pointRadius:       puntos.map(p => p.nviajes > 0 ? 8 : 5),
      pointStyle:        puntos.map(p => p.nviajes > 0 ? 'circle' : 'rect'),
      borderWidth:       2,
      tension:           0.2,
      spanGaps:          false,
    };
  });

  CHARTS['linea-tanqueos'] = new Chart(ctx, {
    type: 'line',
    data: { datasets },
    options: {
      responsive: true, maintainAspectRatio: false,
      parsing: false,  // usamos {x,y} directamente
      plugins: {
        legend: { labels: { font:{size:11}, color:'#5c5b55' } },
        tooltip: {
          callbacks: {
            title: items => fmtFecha(items[0].raw.x),
            label: ctx => {
              const ex = ctx.dataset._extra[ctx.dataIndex];
              return [
                ` ${ctx.dataset.label}: ${fmt.sol(ctx.raw.y)}`,
                ` ${fmt.num(ex.gll,1)} gll · km ${ex.km||'—'}`,
                ` Grifo: ${ex.grifo||'—'}`,
                ` ${ex.nviajes} viaje${ex.nviajes!==1?'s':''} asignado${ex.nviajes!==1?'s':''}`,
              ];
            },
          },
        },
      },
      scales: {
        x: { type:'category', grid:{color:'rgba(0,0,0,.04)'}, ticks:{font:{size:10},color:'#9c9a92',maxRotation:40} },
        y: { grid:{color:'rgba(0,0,0,.04)'}, ticks:{font:{size:11},color:'#9c9a92'},
             title:{display:true,text:'S/ pagado',font:{size:10}} },
      },
      onClick: (evt, els) => {
        if (!els.length) return;
        const ds  = CHARTS['linea-tanqueos'].data.datasets[els[0].datasetIndex];
        const ex  = ds._extra[els[0].index];
        if (ex) abrirDetalleTanqueo(ex);
      },
    },
  });
}

async function abrirDetalleTanqueo(pt) {
  const panel = document.getElementById('panel-tanqueo-detalle');
  panel.style.display = 'block';
  panel.scrollIntoView({ behavior:'smooth', block:'nearest' });

  tx('det-titulo', `${pt.x ? fmtFecha(pt.x) : '—'} — Tanqueo ${pt.x || '—'}`);
  tx('det-sub',    `Grifo: ${pt.grifo||'—'} · km odómetro: ${pt.km||'—'}`);
  tx('det-gll',    fmt.num(pt.gll, 1) + ' gll');
  tx('det-soles',  fmt.sol(pt.y));
  tx('det-nviajes','Cargando…');
  tx('det-kmgal',  '—');

  const tbody = document.getElementById('det-viajes-tbody');
  tbody.innerHTML = '<tr><td colspan="7" class="empty">Cargando viajes…</td></tr>';

  try {
    const viajes = await api(`/api/dashboard/viajes-bloque?id_combustible=${pt.id}`);
    tx('det-nviajes', viajes.length + ' viaje' + (viajes.length !== 1 ? 's' : ''));

    // Calcular km/galón del bloque
    const kmTotal = viajes.reduce((s, v) => s + parseFloat(v.km_recorrido > 0 ? v.km_recorrido : 0), 0);
    const kmpgal  = pt.gll > 0 ? kmTotal / pt.gll : 0;
    const kpgEl   = document.getElementById('det-kmgal');
    if (kpgEl) {
      kpgEl.textContent = kmpgal > 0 ? fmt.num(kmpgal, 2) + ' km/gal' : '—';
      kpgEl.style.color = kmpgal >= 10 ? 'var(--ok)' : kmpgal >= 7 ? 'var(--warn)' : kmpgal > 0 ? 'var(--danger)' : 'var(--text3)';
    }

    if (!viajes.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="empty">Sin viajes asignados a este tanqueo.</td></tr>';
    } else {
      // Galones estimados por viaje (proporcional a km)
      tbody.innerHTML = viajes.map(v => {
        const kmRec  = parseFloat(v.km_recorrido > 0 ? v.km_recorrido : 0);
        const gllEst = kmTotal > 0 ? (kmRec / kmTotal * pt.gll).toFixed(2) : '—';
        return `<tr>
          <td>${fmtFecha(v.fecha)}</td>
          <td class="text-sm">${v.conductor_nombre || '—'}</td>
          <td class="text-sm">${v.destino ? (v.origen||'') + ' → ' + v.destino : '—'}</td>
          <td><span class="badge ${v.tipo_actividad==='AGUA'?'badge-info':v.tipo_actividad==='ACOPIO'?'badge-brand':v.tipo_actividad==='LOGISTICA'?'badge-ok':'badge-warn'}">${v.tipo_actividad||'—'}</span></td>
          <td>${kmRec > 0 ? fmt.num(kmRec, 1) + ' km' : '—'}</td>
          <td class="font-bold">${gllEst !== '—' ? gllEst + ' gll' : '—'}</td>
          <td class="text-sm" style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${v.observacion||''}">${v.observacion||'—'}</td>
        </tr>`;
      }).join('');
    }

    // Gráfico de actividades del bloque
    renderActividadesBloque(viajes);

  } catch(e) {
    tbody.innerHTML = `<tr><td colspan="7" class="empty" style="color:var(--danger)">Error: ${e.message}</td></tr>`;
  }
}

function renderActividadesBloque(viajes) {
  // Agrupar por tipo_actividad
  const map = {};
  viajes.forEach(v => {
    const act = v.tipo_actividad || 'Sin actividad';
    if (!map[act]) map[act] = { n:0, km:0 };
    map[act].n++;
    map[act].km += parseFloat(v.km_recorrido > 0 ? v.km_recorrido : 0);
  });
  const acts   = Object.keys(map);
  const colores = ['#185FA5','#1D9E75','#D85A30','#534AB7','#854F0B'];

  // Lista
  const lista = document.getElementById('det-actividades');
  const kmTotal = Object.values(map).reduce((s,a) => s + a.km, 0);
  lista.innerHTML = acts.map((a, i) => {
    const pct = kmTotal > 0 ? Math.round(map[a].km / kmTotal * 100) : 0;
    return `<div style="display:flex;align-items:center;gap:8px;font-size:12px">
      <div style="width:10px;height:10px;border-radius:50%;background:${colores[i%colores.length]};flex-shrink:0"></div>
      <span style="flex:1">${a}</span>
      <span class="font-bold">${map[a].n} viaje${map[a].n!==1?'s':''}</span>
      <span class="text-muted">${pct}%</span>
    </div>`;
  }).join('');

  // Mini dona
  dch('det-actividades');
  const ctx = document.getElementById('chart-det-actividades'); if (!ctx) return;
  CHARTS['det-actividades'] = new Chart(ctx, {
    type: 'doughnut',
    data: { labels: acts,
            datasets:[{ data: acts.map(a => map[a].n), backgroundColor: colores.slice(0,acts.length), borderWidth:2, borderColor:'#fff' }] },
    options: { responsive:true, maintainAspectRatio:false, cutout:'50%',
      plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label: ctx => ` ${ctx.label}: ${ctx.raw} viajes` }}},
    },
  });
}

// ══════════════════════════════════════════════════════════════
// RENDIMIENTO POR TANQUEO (barras km/galón)
// ══════════════════════════════════════════════════════════════
async function cargarRendimientoDiario() {
  let url = `/api/dashboard/rendimiento-diario?fecha_desde=${fd()}&fecha_hasta=${fh()}`;
  if (fgr()) url += `&id_grifo=${fgr()}`;
  if (fac()) url += `&tipo_actividad=${fac()}`;
  const uid = document.getElementById('rend-unidad')?.value;
  if (uid) url += `&id_unidad=${uid}`;

  const data = await api(url);
  rendDiario = data.bloques || [];

  const n       = rendDiario.length;
  const kostoT  = rendDiario.reduce((s,b) => s + parseFloat(b.costo_tanqueo||0), 0);
  const sumKmpg = rendDiario.filter(b=>b.km_por_galon>0).map(b=>parseFloat(b.km_por_galon));
  const prom    = sumKmpg.length ? sumKmpg.reduce((a,b)=>a+b)/sumKmpg.length : 0;

  tx('rv-n',     n + ' tanqueos');
  tx('rv-costo', fmt.sol(kostoT));
  const kpEl = document.getElementById('rv-kmpgal');
  if (kpEl) {
    kpEl.textContent = prom > 0 ? fmt.num(prom,2)+' km/gal' : '—';
    kpEl.className   = 'kpi-val ' + (prom>=10?'ok':prom>=7?'warn':'danger');
  }

  dch('rend-diario');
  const ctx = document.getElementById('chart-rend-diario'); if (!ctx) return;
  CHARTS['rend-diario'] = new Chart(ctx, {
    type: 'bar',
    data: {
      labels:   rendDiario.map(b => `${b.placa} ${String(b.fecha_tanqueo||'').slice(5)}`),
      datasets: [{ label:'km/galón', data: rendDiario.map(b=>parseFloat(b.km_por_galon||0)),
        backgroundColor: rendDiario.map(b=>{const v=parseFloat(b.km_por_galon||0);
          return v>=10?'rgba(45,106,79,.8)':v>=7?'rgba(133,79,11,.8)':'rgba(163,45,45,.8)';}),
        borderRadius:4 }],
    },
    options: { responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{display:false},
        tooltip:{ callbacks:{ label: ctx => {
          const b=rendDiario[ctx.dataIndex];
          return[` ${fmt.num(ctx.raw,2)} km/gal`,` ${b.n_viajes||0} viajes · ${fmt.num(b.km_viajes_bloque||0,0)} km`,` ${fmt.num(b.cantidad_gll||0,1)} gll · ${fmt.sol(b.costo_tanqueo||0)}`];
        }}},
      },
      scales:{
        x:{grid:{display:false},ticks:{font:{size:10},color:'#9c9a92',maxRotation:40}},
        y:{grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:11},color:'#9c9a92'},
           title:{display:true,text:'km/gal',font:{size:10}},min:0},
      },
      onClick:(evt,els)=>{
        if(!els.length)return;
        const b=rendDiario[els[0].index];
        // Reusar abrirDetalleTanqueo con los datos del bloque
        abrirDetalleTanqueo({id:b.id_combustible,x:b.fecha_tanqueo,y:b.costo_tanqueo,
          gll:b.cantidad_gll,km:b.km_tanqueo,grifo:'—',nviajes:b.n_viajes});
        document.getElementById('panel-tanqueo-detalle').scrollIntoView({behavior:'smooth'});
      },
    },
  });

  // Gráfico actividades
  dibujarActividadChart(data.por_actividad || []);
}

function dibujarActividadChart(por_act) {
  dch('actividad');
  const ctx = document.getElementById('chart-actividad'); if (!ctx || !por_act.length) return;

  // Tabla resumen
  const actTabla = document.getElementById('act-tabla');
  const total    = por_act.reduce((s,a) => s+parseFloat(a.gll_total||0), 0);
  actTabla.innerHTML = por_act.map((a,i) => {
    const pct = total > 0 ? Math.round(parseFloat(a.gll_total)/total*100) : 0;
    return `<div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;padding:3px 0;border-bottom:1px solid var(--border)">
      <div style="display:flex;align-items:center;gap:6px">
        <div style="width:8px;height:8px;border-radius:50%;background:${PAL[i%PAL.length]}"></div>
        <span>${a.tipo_actividad||'Sin actividad'}</span>
      </div>
      <div style="text-align:right">
        <span class="font-bold">${fmt.num(a.gll_total||0,1)} gll</span>
        <span class="text-muted" style="margin-left:6px">${pct}%</span>
      </div>
    </div>`;
  }).join('');

  CHARTS.actividad = new Chart(ctx, {
    type: 'doughnut',
    data: { labels: por_act.map(a=>a.tipo_actividad||'Sin actividad'),
            datasets:[{ data: por_act.map(a=>parseFloat(a.gll_total||0)),
              backgroundColor: PAL.slice(0,por_act.length), borderWidth:2, borderColor:'#fff', hoverOffset:6 }] },
    options: { responsive:true, maintainAspectRatio:false, cutout:'50%',
      plugins:{ legend:{display:false},
        tooltip:{ callbacks:{ label: ctx=>{
          const a=por_act[ctx.dataIndex];
          return[` ${fmt.num(ctx.raw,1)} gll`,` ${a.n_viajes} viajes`,` ${fmt.num(a.km_total||0,0)} km`,` ${fmt.sol(a.costo_total||0)}`];
        }}},
      },
    },
  });
}

// ══════════════════════════════════════════════════════════════
// ALERTAS + DOCUMENTOS + VIAJES HOY + GE
// ══════════════════════════════════════════════════════════════
async function cargarAlertasMant() {
  const data = await api('/api/mantenimiento/alertas');
  const el   = document.getElementById('mant-alertas');
  if (!data.length){el.innerHTML='<div class="empty-state" style="padding:10px">Sin planes.</div>';return;}
  el.innerHTML = data.slice(0,5).map(m=>{
    const pct  = Math.min(m.pct||0,100);
    const color= m.estado==='VENCIDO'||m.estado==='CRITICO'?'#A32D2D':m.estado==='PROXIMO'?'#854F0B':'#2d6a4f';
    const badge= m.estado==='VENCIDO'?'<span class="badge badge-danger">VENCIDO</span>':m.estado==='CRITICO'?'<span class="badge badge-danger">CRÍTICO</span>':m.estado==='PROXIMO'?'<span class="badge badge-warn">PRÓXIMO</span>':'<span class="badge badge-ok">OK</span>';
    const falta= m.falta_km?`Faltan ${fmt.num(m.falta_km,0)} km`:m.falta_h?`Faltan ${fmt.num(m.falta_h,1)} h`:'';
    return`<div class="sem-row"><div class="sem-info"><div class="sem-title">${m.placa} — ${m.tarea||m.tipo_mantenimiento}</div><div class="sem-sub">${falta}</div><div class="progress-bar"><div class="progress-fill" style="width:${pct}%;background:${color}"></div></div></div>${badge}</div>`;
  }).join('');
}

async function cargarDocumentos() {
  const data     = await api('/api/mantenimiento/documentos');
  const el       = document.getElementById('docs-semaforo');
  const criticos = data.filter(d=>d.dias_restantes<=d.alerta_dias_antes);
  if (!criticos.length){el.innerHTML='<div class="empty-state" style="padding:10px">✅ Todos vigentes.</div>';return;}
  el.innerHTML = criticos.slice(0,5).map(d=>{
    const dias  = parseInt(d.dias_restantes);
    const cls   = dias<0?'badge-danger':dias<=7?'badge-danger':'badge-warn';
    const label = dias<0?`Vencido hace ${Math.abs(dias)}d`:`Vence en ${dias}d`;
    return`<div class="sem-row"><div class="sem-info"><div class="sem-title">${d.placa} — ${d.tipo_documento}</div><div class="sem-sub">${fmtFecha(d.fecha_vencimiento)}</div></div><span class="badge ${cls}">${label}</span></div>`;
  }).join('');
}

async function cargarViajesHoy() {
  const hoy  = new Date().toISOString().slice(0,10);
  const data = await api(`/api/garita/viajes?fecha=${hoy}`);
  const tbody= document.getElementById('viajes-hoy');
  if (!data.length){tbody.innerHTML='<tr><td colspan="4" class="empty">Sin viajes hoy.</td></tr>';return;}
  tbody.innerHTML = data.slice(0,8).map(v=>`<tr>
    <td><strong>${v.placa||'—'}</strong></td>
    <td><span class="badge ${v.tipo_actividad==='AGUA'?'badge-info':v.tipo_actividad==='ACOPIO'?'badge-brand':'badge-ok'} ">${v.tipo_actividad||'—'}</span></td>
    <td>${v.km_recorrido&&v.km_recorrido>0?fmt.num(v.km_recorrido,1)+' km':'En curso'}</td>
    <td>${v.estado_ruta==='DENTRO_MARGEN'?'<span class="badge badge-ok">✓</span>':v.estado_ruta==='FUERA_MARGEN'?'<span class="badge badge-danger">⚠</span>':'<span class="badge badge-info">—</span>'}</td>
  </tr>`).join('');
}

async function cargarGEChart() {
  const desde14 = new Date(Date.now()-14*86400000).toISOString().slice(0,10);
  const data    = await api(`/api/ge/registros?fecha_desde=${desde14}`);
  const placas  = [...new Set(data.map(r=>r.placa))];
  const fechas  = [...new Set(data.map(r=>r.fecha))].sort();
  const byP     = {};
  data.forEach(r=>{if(!byP[r.placa])byP[r.placa]={};byP[r.placa][r.fecha]={gph:parseFloat(r.consumo_gal_hora||0),id:r.id_registro};});
  dch('ge');
  const ctx = document.getElementById('chart-ge'); if (!ctx) return;
  CHARTS.ge = new Chart(ctx,{type:'line',
    data:{labels:fechas.map(f=>f.slice(5)),
      datasets:placas.map((p,i)=>({label:p,data:fechas.map(f=>byP[p]?.[f]?.gph??null),
        borderColor:PAL[i%PAL.length],backgroundColor:'transparent',borderWidth:2,spanGaps:true,
        pointRadius:fechas.map(f=>(byP[p]?.[f]?.gph||0)>=4?7:3),
        pointBackgroundColor:fechas.map(f=>(byP[p]?.[f]?.gph||0)>=4?'#A32D2D':PAL[i%PAL.length])}))},
    options:{responsive:true,maintainAspectRatio:false,
      plugins:{legend:{labels:{font:{size:11},color:'#5c5b55'}},tooltip:{callbacks:{label:ctx=>` ${fmt.num(ctx.raw,2)} gal/hora`}}},
      scales:{x:{grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:11},color:'#9c9a92'}},y:{grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:11},color:'#9c9a92'}}},
      onClick:(evt,els)=>{if(!els.length)return;const f=fechas[els[0].index],p=placas[els[0].datasetIndex];const id=byP[p]?.[f]?.id;if(id)cargarCausaRaiz(id);}},
  });
}

async function cargarCausaRaiz(id) {
  const body=document.getElementById('causa-raiz-body');
  body.innerHTML='<p class="text-sm">Cargando…</p>';
  try{
    const d=await api(`/api/ge/causa-raiz/${id}`);
    body.innerHTML=`<div style="font-size:13px;font-weight:700">${d.placa}</div>
      <div class="text-sm text-muted">${fmtFecha(d.fecha)} · ${fmt.num(d.consumo_gal_hora,2)} gal/hora</div>
      <div class="divider"></div>
      ${!d.equipos_activos.length?'<div class="empty-state" style="padding:8px">Sin procesos.</div>':
        d.equipos_activos.map(e=>`<div style="display:flex;justify-content:space-between;font-size:12px;padding:5px 0;border-bottom:1px solid var(--border)">
          <div><div style="font-weight:600">${e.nombre_equipo}</div><div class="text-muted">${e.proceso||'—'}</div></div>
          <div style="text-align:right"><div>${fmt.num(e.horas_trabajo,1)} h</div><div class="text-muted">${fmt.num(e.kwh_consumidos,1)} kWh</div></div>
        </div>`).join('')}`;
  }catch(e){body.innerHTML=`<p style="color:var(--danger);font-size:12px">Error: ${e.message}</p>`;}
}

// ══════════════════════════════════════════════════════════════
// VIAJES POR ACTIVIDAD — dona + tabla filtrable
// ══════════════════════════════════════════════════════════════
let viajesActData = { resumen:[], detalle:[] };

async function cargarViajesActividad() {
  const tipo = document.getElementById('act-filtro-tipo').value;
  const uid  = document.getElementById('act-filtro-unidad').value;
  let url = `/api/dashboard/viajes-por-actividad?fecha_desde=${fd()}&fecha_hasta=${fh()}`;
  if (uid)  url += `&id_unidad=${uid}`;
  if (tipo) url += `&tipo_actividad=${tipo}`;

  viajesActData = await api(url);
  const res = viajesActData.resumen || [];
  const det = viajesActData.detalle || [];

  // Total viajes
  const totalViajes = res.reduce((s,r) => s+parseInt(r.n_viajes||0), 0);
  tx('act-count', totalViajes + ' viajes totales · ' + det.length + ' en lista');

  // ── Dona de viajes por actividad ────────────────────────────
  dch('viajes-act');
  const ctx = document.getElementById('chart-viajes-act');
  const coloresAct = {
    'ACOPIO':'#185FA5','LOGISTICA':'#1D9E75','AGUA':'#0077b6',
    'MANTENIMIENTO':'#854F0B','VENTA DE MINERAL':'#534AB7','PAD':'#993556','':'#9c9a92'
  };
  if (ctx && res.length) {
    CHARTS['viajes-act'] = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: res.map(r => r.tipo_actividad || 'Sin actividad'),
        datasets:[{
          data: res.map(r => parseInt(r.n_viajes||0)),
          backgroundColor: res.map(r => coloresAct[r.tipo_actividad]||PAL[res.indexOf(r)%PAL.length]),
          borderWidth:2, borderColor:'#fff', hoverOffset:6,
        }],
      },
      options:{
        responsive:true, maintainAspectRatio:false, cutout:'52%',
        plugins:{
          legend:{ position:'bottom', labels:{ font:{size:11}, color:'#5c5b55', padding:10 }},
          tooltip:{ callbacks:{ label: ctx => {
            const r = res[ctx.dataIndex];
            return [
              ` ${ctx.raw} viajes (${r.porcentaje||0}%)`,
              ` ${fmt.num(r.km_total||0,0)} km recorridos`,
              r.gll_total > 0 ? ` ${fmt.num(r.gll_total,1)} gll consumidos` : '',
            ].filter(Boolean);
          }}},
        },
        onClick:(evt,els)=>{
          if (!els.length) return;
          const r = res[els[0].index];
          document.getElementById('act-filtro-tipo').value = r.tipo_actividad || '';
          cargarViajesActividad();
        },
      },
    });
  }

  // ── Tabla resumen por actividad ──────────────────────────────
  const tablaEl = document.getElementById('act-resumen-tabla');
  tablaEl.innerHTML = res.map((r,i) => {
    const color = coloresAct[r.tipo_actividad] || PAL[i%PAL.length];
    const pct   = parseFloat(r.porcentaje||0);
    return `<div style="padding:8px 0;border-bottom:1px solid var(--border);cursor:pointer"
                 onclick="filtrarPorActividad('${r.tipo_actividad||''}')">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
        <div style="display:flex;align-items:center;gap:8px">
          <div style="width:10px;height:10px;border-radius:50%;background:${color};flex-shrink:0"></div>
          <span style="font-weight:600;font-size:13px">${r.tipo_actividad||'Sin actividad'}</span>
        </div>
        <div style="display:flex;gap:14px;font-size:12px;color:var(--text2)">
          <span><strong style="color:var(--brand)">${r.n_viajes}</strong> viajes (${pct}%)</span>
          <span>${fmt.num(r.km_total||0,0)} km</span>
          ${r.gll_total > 0 ? `<span>${fmt.num(r.gll_total,1)} gll</span>` : ''}
          ${r.costo_total > 0 ? `<span>${fmt.sol(r.costo_total)}</span>` : ''}
        </div>
      </div>
      <div style="height:5px;background:var(--border);border-radius:999px;overflow:hidden">
        <div style="height:100%;width:${pct}%;background:${color};border-radius:999px;transition:width .4s"></div>
      </div>
    </div>`;
  }).join('');

  // ── Tabla de viajes ──────────────────────────────────────────
  renderTablaViajesAct(det, tipo);
}

function filtrarPorActividad(tipo) {
  document.getElementById('act-filtro-tipo').value = tipo;
  cargarViajesActividad();
}

function renderTablaViajesAct(det, tipoFiltro) {
  const panel = document.getElementById('act-viajes-panel');
  const tbody = document.getElementById('act-viajes-tbody');
  const titulo= document.getElementById('act-viajes-titulo');

  if (!det.length) {
    panel.style.display = 'none'; return;
  }

  panel.style.display = 'block';
  titulo.textContent  = (tipoFiltro ? tipoFiltro + ' — ' : 'Todos — ') + det.length + ' viajes';

  const coloresAct = {
    'ACOPIO':'badge-brand','LOGISTICA':'badge-ok','AGUA':'badge-info',
    'MANTENIMIENTO':'badge-warn','VENTA DE MINERAL':'badge-brand','PAD':'badge-danger'
  };

  tbody.innerHTML = det.map(v => {
    const kmR = parseFloat(v.km_recorrido||0);
    const cls = coloresAct[v.tipo_actividad] || 'badge-info';
    return `<tr>
      <td>${fmtFecha(v.fecha)}</td>
      <td><strong>${v.placa}</strong></td>
      <td class="text-sm">${v.conductor_nombre||'—'}</td>
      <td><span class="badge ${cls}">${v.tipo_actividad||'—'}</span></td>
      <td class="text-sm">${v.destino ? (v.origen||'')+'→'+v.destino : '—'}</td>
      <td>${kmR>0 ? fmt.num(kmR,1)+' km' : '—'}</td>
      <td class="mono">${v.hora_salida||'—'}</td>
      <td class="text-sm" style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
          title="${v.observacion||''}">${v.observacion||'—'}</td>
    </tr>`;
  }).join('');
}

async function exportarResumen() {
  toast('Generando…','warn');
  try {
    const [kpis,rend]=await Promise.all([
      api(`/api/dashboard/kpis?fecha_desde=${fd()}&fecha_hasta=${fh()}${fgr()?'&id_grifo='+fgr():''}`),
      api('/api/compras/rendimiento'),
    ]);
    const wb=XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb,XLSX.utils.aoa_to_sheet([
      ['FleetControl Pro — Resumen'],['Período: '+fd()+' al '+fh()],[],
      ['KPI','Valor'],['Viajes',kpis.viajes],['Galones',kpis.total_galones],
      ['Gasto S/',kpis.total_soles],['Saldo grifo S/',kpis.saldo_grifo],['Alertas docs',kpis.alertas_doc],
    ]),'Resumen');
    XLSX.utils.book_append_sheet(wb,XLSX.utils.json_to_sheet(rend.flota.map(r=>({
      'Unidad':r.placa,'Tanqueos':r.n_tanqueos,'km':parseFloat(r.km_totales||0),
      'Galones':parseFloat(r.gll_totales||0),'km/galón':parseFloat(r.km_por_galon||0),
      'Costo S/':parseFloat(r.costo_combustible||0),
    }))),'Rendimiento Flota');
    XLSX.writeFile(wb,`Dashboard_${fd()}_${fh()}.xlsx`);
    toast('Excel generado','ok');
  } catch(e){toast('Error: '+e.message,'error');}
}
</script>