<?php // views/maquinaria.php ?>

<style>
/* ── Autocomplete maquinaria ── */
.ac-wrap { position:relative; }
.ac-input { width:100%;padding:8px 10px;border:1px solid var(--border2);border-radius:8px;font-size:13px;background:#fff;box-sizing:border-box; }
.ac-input:focus { outline:none;border-color:var(--brand);box-shadow:0 0 0 3px var(--brand-lt); }
.ac-drop { position:absolute;z-index:999;width:100%;max-height:240px;overflow-y:auto;background:#fff;border:1px solid var(--border2);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.12);margin-top:2px;display:none; }
.ac-drop.open { display:block; }
.ac-item { padding:9px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid var(--border); }
.ac-item:last-child { border-bottom:none; }
.ac-item:hover { background:var(--brand-lt); }
.ac-item .ac-sub { font-size:11px;color:var(--text3);margin-top:1px; }
</style>

<!-- ══ MAQ. PESADA ════════════════════════════════════ -->
<div class="flex items-center justify-between mb-12">
  <h1 style="font-size:18px;font-weight:700">Maquinaria Pesada</h1>
  <button class="btn btn-primary" onclick="abrirModalDia(null)">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    Registrar día
  </button>
</div>

<!-- KPIs fijos -->
<div class="kpi-grid" style="margin-bottom:var(--gap)">
  <div class="kpi-card"><div class="kpi-lbl">Horas horómetro (período)</div><div class="kpi-val brand" id="mq-horas">—</div><div class="kpi-sub">total acumulado</div></div>
  <div class="kpi-card"><div class="kpi-lbl">Horas ralentí (período)</div><div class="kpi-val warn" id="mq-ralenti">—</div><div class="kpi-sub">sin carga productiva</div></div>
  <div class="kpi-card"><div class="kpi-lbl">Días registrados</div><div class="kpi-val brand" id="mq-dias">—</div><div class="kpi-sub">en el período</div></div>
  <div class="kpi-card"><div class="kpi-lbl">Rendimiento promedio</div><div class="kpi-val ok" id="mq-rend">—</div><div class="kpi-sub">gal / hora</div></div>
</div>
<!-- KPIs dinámicos por máquina: horómetro actual -->
<div id="mq-horometros-wrap" class="kpi-grid" style="margin-bottom:var(--gap)"></div>

<!-- Filtros -->
<div class="filter-bar">
  <label>Desde <input type="date" id="mq-desde" value="<?= date('Y-m-01') ?>" onchange="cargarDias()"/></label>
  <label>Hasta <input type="date" id="mq-hasta" value="<?= date('Y-m-d') ?>"  onchange="cargarDias()"/></label>
  <label>Máquina
    <select id="mq-filtro-unidad" onchange="cargarDias()">
      <option value="">Todas</option>
    </select>
  </label>
  <button class="btn btn-outline btn-sm" onclick="exportarMaquinaria()">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
      <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
    </svg>
    Excel
  </button>
</div>

<!-- Tabla días -->
<div class="card full">
  <div class="card-title">
    Control diario — Maquinaria pesada
    <span id="mq-count" class="text-muted text-sm">— registros</span>
  </div>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>Fecha</th><th>Máquina</th><th>Operador</th>
          <th>Horóm. Ini.</th><th>Horóm. Fin.</th>
          <th>Horas</th><th>H. Ralentí</th>
          <th>Combustible</th><th>Actividades</th><th>Acciones</th>
        </tr>
      </thead>
      <tbody id="mq-tbody">
        <tr><td colspan="10" class="empty">Cargando…</td></tr>
      </tbody>
    </table>
  </div>
  <div class="pager" id="pager-mq"></div>
</div>

<!-- Gráficos -->
<div class="row" style="margin-top:var(--gap)">
  <div class="card w2">
    <div class="card-title">Horas productivas vs Ralentí por semana</div>
    <div class="chart-box" style="height:220px"><canvas id="chart-mq-horas"></canvas></div>
  </div>
  <div class="card w1">
    <div class="card-title">Consumo gal/hora por máquina</div>
    <div class="chart-box" style="height:220px"><canvas id="chart-mq-rend"></canvas></div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MODAL: Registrar / Editar día + Actividades integradas
════════════════════════════════════════════════════════════ -->
<div class="overlay" id="modal-dia">
  <div class="modal modal-lg">
    <div class="modal-hdr">
      <span class="modal-title" id="modal-dia-title">Registrar Día de Trabajo</span>
      <button class="modal-close" onclick="cerrarModal('modal-dia')">×</button>
    </div>
    <div class="modal-body">

      <input type="hidden" id="md-id"/>

      <!-- Sección 1: Datos del día -->
      <div style="font-size:11px;font-weight:700;color:var(--brand);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">
        📋 Datos del día
      </div>

      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Fecha *</label>
          <input type="date" id="md-fecha" value="<?= date('Y-m-d') ?>" onchange="onFechaChange()"/>
        </div>
        <div class="fgroup">
          <label>Máquina *</label>
          <select id="md-unidad" onchange="onUnidadChange()">
            <option value="">Seleccionar máquina…</option>
          </select>
        </div>
      </div>

      <div class="fgroup">
        <label>Operador</label>
        <div class="ac-wrap">
          <input type="hidden" id="md-conductor-id"/>
          <input type="text" class="ac-input" id="md-conductor-input"
                 placeholder="Escribe nombre o apellido del operador…"
                 autocomplete="off"
                 oninput="filtrarACOp()" onfocus="filtrarACOp()" onblur="cerrarACOp()"/>
          <div class="ac-drop" id="ac-operador"></div>
        </div>
        <span class="hint">Busca por nombre en la tabla de conductores</span>
      </div>

      <!-- Horómetros -->
      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Horómetro inicial *</label>
          <input type="number" id="md-h-ini" step="0.1"
                 placeholder="Se carga automáticamente…"
                 oninput="calcularHoras()"/>
          <span class="hint" id="md-h-ini-hint" style="color:var(--brand)">
            Selecciona la máquina para cargar el último horómetro
          </span>
        </div>
        <div class="fgroup">
          <label>Horómetro final *</label>
          <input type="number" id="md-h-fin" step="0.1"
                 placeholder="Al finalizar el día…"
                 oninput="calcularHoras()"/>
        </div>
      </div>

      <!-- Resultado calculated -->
      <div id="md-horas-wrap" style="display:none;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:12px 16px">
        <div style="display:flex;gap:24px;flex-wrap:wrap;align-items:flex-end">
          <div>
            <div class="text-xs text-muted">Horas horómetro</div>
            <div style="font-size:22px;font-weight:700;color:var(--brand)" id="md-horas-display">—</div>
          </div>
          <div class="fgroup" style="margin:0;min-width:140px">
            <label>Horas ralentí</label>
            <input type="number" id="md-ralenti" step="0.1" placeholder="0.0" min="0" value="0"/>
          </div>
        </div>
      </div>

      <div class="divider"></div>

      <!-- Sección 2: Actividades del día -->
      <div style="font-size:11px;font-weight:700;color:var(--brand);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">
        ⚙️ Actividades realizadas
      </div>

      <!-- Lista de actividades ya agregadas -->
      <div id="act-lista-modal" style="display:flex;flex-direction:column;gap:6px;margin-bottom:12px">
        <div class="empty-state" style="padding:10px 0;font-size:12px">
          Guarda el día primero para agregar actividades, o agrégalas directamente abajo.
        </div>
      </div>

      <!-- Formulario actividad rápida -->
      <div style="background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:14px">
        <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:10px">Agregar actividad</div>
        <div class="form-grid cols-2">
          <div class="fgroup">
            <label>Actividad *</label>
            <select id="act-tipo" onchange="onActividadChange()">
              <option value="">Seleccionar…</option>
            </select>
          </div>
          <div class="fgroup">
            <label>Observación / Lote / Lugar</label>
            <input type="text" id="act-obs" placeholder="Lote, lugar, detalle…"/>
          </div>
        </div>
        <div class="form-grid cols-2">
          <div class="fgroup">
            <label>Hora inicio *</label>
            <input type="time" id="act-hi" oninput="calcularHorasAct()"/>
          </div>
          <div class="fgroup">
            <label>Hora fin *</label>
            <input type="time" id="act-hf" oninput="calcularHorasAct()"/>
          </div>
        </div>
        <!-- Info de la actividad -->
        <div id="act-info-wrap" style="display:none;background:var(--brand-lt);border-radius:6px;padding:8px 12px;font-size:12px;margin-top:4px">
          <div style="display:flex;gap:20px;flex-wrap:wrap">
            <div><span class="text-muted">Tipo consumo:</span> <strong id="act-tipo-cons">—</strong></div>
            <div><span class="text-muted">Factor carga:</span> <strong id="act-factor">—</strong></div>
            <div><span class="text-muted">Total horas:</span>  <strong id="act-total-h">—</strong></div>
          </div>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:10px">
          <button class="btn btn-success btn-sm" onclick="agregarActividadModal()">
            + Agregar actividad
          </button>
        </div>
      </div>

    </div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-dia')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarDia()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        Guardar día
      </button>
    </div>
  </div>
</div>

<!-- Modal confirmar eliminar -->
<div class="overlay" id="modal-confirmar-mq">
  <div class="modal modal-sm">
    <div class="modal-hdr">
      <span class="modal-title">Confirmar eliminación</span>
      <button class="modal-close" onclick="cerrarModal('modal-confirmar-mq')">×</button>
    </div>
    <div class="modal-body">
      <p>¿Eliminar este registro de día? Se eliminarán también todas las actividades asociadas.</p>
    </div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-confirmar-mq')">Cancelar</button>
      <button class="btn btn-danger" id="btn-del-mq">Eliminar</button>
    </div>
  </div>
</div>

<script>
/* ══════════════════════════════════════════════════════════
   MAQUINARIA — JavaScript
══════════════════════════════════════════════════════════ */

let diasData    = [];
let diasPage    = 0;
const DIAS_PP   = 15;
let activCache  = [];   // catálogo actividades_retro
let maqCache    = [];   // unidades maquinaria
let idDiaGuardado = null; // id_control_dia del día actualmente en el modal

// ── Boot ─────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  Promise.all([
    cargarMaquinas(),
    cargarOperadores(),
    cargarActividadesCatalogo(),
  ]).then(() => cargarDias());
});

// ── Catálogos ────────────────────────────────────────────────
async function cargarMaquinas() {
  const data = await api('/api/unidades');
  maqCache   = data.filter(u => u.tipo_unidad === 'MAQ. PESADA');

  // Filtro tabla
  const selF = document.getElementById('mq-filtro-unidad');
  maqCache.forEach(u => {
    selF.insertAdjacentHTML('beforeend',
      `<option value="${u.id_unidad}">${u.placa}</option>`);
  });

  // Select del modal
  const selM = document.getElementById('md-unidad');
  maqCache.forEach(u => {
    selM.insertAdjacentHTML('beforeend',
      `<option value="${u.id_unidad}">${u.placa} — ${u.descripcion || ''}</option>`);
  });
}

// Caché de conductores para autocomplete en maquinaria
let maqConductoresCache = [];

async function cargarOperadores() {
  maqConductoresCache = await api('/api/conductor');
}

// ── Autocomplete operador (maquinaria) ────────────────────────
let acOpTimer = null;

function filtrarACOp() {
  clearTimeout(acOpTimer);
  acOpTimer = setTimeout(() => {
    const q    = (document.getElementById('md-conductor-input').value || '').toLowerCase();
    const drop = document.getElementById('ac-operador');
    const items = q.length === 0
      ? maqConductoresCache
      : maqConductoresCache.filter(ct => ct.nombre_conductor.toLowerCase().includes(q));

    if (!items.length) { drop.classList.remove('open'); return; }

    drop.innerHTML = items.slice(0,40).map((ct, idx) => {
      const hl = q
        ? ct.nombre_conductor.replace(new RegExp(`(${q})`,'gi'),'<em style="color:var(--brand);font-weight:700">$1</em>')
        : ct.nombre_conductor;
      const sub = ct.nro_licencia ? `Lic: ${ct.nro_licencia}` : '';
      return `<div class="ac-item" data-id="${ct.id_conductor}"
                   onmousedown="elegirOperador(${ct.id_conductor},'${ct.nombre_conductor.replace(/'/g,"\\'")}')">
               ${hl}
               ${sub ? `<div class="ac-sub">${sub}</div>` : ''}
             </div>`;
    }).join('');
    drop.classList.add('open');
  }, 100);
}

function elegirOperador(id, nombre) {
  document.getElementById('md-conductor-id').value    = id;
  document.getElementById('md-conductor-input').value = nombre;
  setTimeout(() => document.getElementById('ac-operador').classList.remove('open'), 150);
}

function cerrarACOp() {
  setTimeout(() => document.getElementById('ac-operador').classList.remove('open'), 150);
}

function calcActTotalH() {
  var hi = document.getElementById('act-hi').value;
  var hf = document.getElementById('act-hf').value;
  if (hi && hf) {
    var diff = (new Date('2000-01-01 '+hf) - new Date('2000-01-01 '+hi)) / 3600000;
    document.getElementById('act-total-h').textContent = diff > 0 ? fmt.num(diff,2) + ' h' : '—';
  }
}

async function cargarActividadesCatalogo() {
  activCache = await api('/api/actividades-retro');
  const sel  = document.getElementById('act-tipo');
  activCache.forEach(a => {
    sel.insertAdjacentHTML('beforeend',
      `<option value="${a.id_actividad}"
               data-factor="${a.factor_carga}"
               data-tipo="${a.tipo_consumo}">
        ${a.actividad} — ${a.tipo_consumo}
       </option>`);
  });
}

// ── Eventos del modal ────────────────────────────────────────
async function onUnidadChange() {
  const id = document.getElementById('md-unidad').value;
  if (!id) {
    document.getElementById('md-h-ini-hint').textContent = 'Selecciona la máquina para cargar el último horómetro';
    return;
  }
  await cargarUltimoHorometro(id);
}

async function onFechaChange() {
  const id = document.getElementById('md-unidad').value;
  if (id) await cargarUltimoHorometro(id);
}

async function cargarUltimoHorometro(idUnidad) {
  const hint = document.getElementById('md-h-ini-hint');
  hint.textContent = 'Cargando último horómetro…';
  hint.style.color = 'var(--text3)';

  const d = await api(`/api/maquinaria/ultimo-horometro?id_unidad=${idUnidad}`);
  if (d.horometro_final > 0) {
    document.getElementById('md-h-ini').value = d.horometro_final;
    hint.textContent = `Último: ${fmt.num(d.horometro_final, 1)} h (${fmtFecha(d.fecha)})`;
    hint.style.color = 'var(--ok)';
    calcularHoras();
  } else {
    document.getElementById('md-h-ini').value = '';
    hint.textContent = 'Sin registros previos — ingresa el horómetro manualmente';
    hint.style.color = 'var(--warn)';
  }
}

function calcularHoras() {
  const hi   = parseFloat(document.getElementById('md-h-ini').value) || 0;
  const hf   = parseFloat(document.getElementById('md-h-fin').value) || 0;
  const wrap = document.getElementById('md-horas-wrap');
  if (hi <= 0 || hf <= 0 || hf <= hi) { wrap.style.display = 'none'; return; }
  document.getElementById('md-horas-display').textContent = fmt.num(hf - hi, 2) + ' h';
  wrap.style.display = 'block';
}

// ── Actividad info ────────────────────────────────────────────
function onActividadChange() {
  const sel  = document.getElementById('act-tipo');
  const opt  = sel.options[sel.selectedIndex];
  const wrap = document.getElementById('act-info-wrap');
  if (!sel.value) { wrap.style.display = 'none'; return; }
  document.getElementById('act-factor').textContent    = opt.dataset.factor || '—';
  document.getElementById('act-tipo-cons').textContent = opt.dataset.tipo   || '—';
  wrap.style.display = 'block';
  calcularHorasAct();
}

function calcularHorasAct() {
  const hi  = document.getElementById('act-hi').value;
  const hf  = document.getElementById('act-hf').value;
  if (!hi || !hf) return;
  const totalH = (new Date('1970-01-01T'+hf) - new Date('1970-01-01T'+hi)) / 3600000;
  tx('act-total-h', fmt.num(totalH, 2) + ' h');
}

// ── Cargar días ──────────────────────────────────────────────
async function cargarDias() {
  const fd  = document.getElementById('mq-desde').value;
  const fh  = document.getElementById('mq-hasta').value;
  const uid = document.getElementById('mq-filtro-unidad').value;

  let url = `/api/maquinaria/dias?fecha_desde=${fd}&fecha_hasta=${fh}`;
  if (uid) url += `&id_unidad=${uid}`;

  diasData = await api(url);
  diasPage = 0;
  renderDias();
  calcularKPIsMaq();
  dibujarGraficoHoras();
  dibujarGraficoRendimiento();
}

function renderDias() {
  const tbody = document.getElementById('mq-tbody');
  const total = Math.max(1, Math.ceil(diasData.length / DIAS_PP));
  const slice = diasData.slice(diasPage * DIAS_PP, (diasPage + 1) * DIAS_PP);
  tx('mq-count', diasData.length + ' registros');

  if (!slice.length) {
    tbody.innerHTML = '<tr><td colspan="10" class="empty">Sin registros para estos filtros.</td></tr>';
    renderPager('pager-mq', 0, 1, () => {});
    return;
  }

  tbody.innerHTML = slice.map(d => {
    const horas   = d.horas_horometro ? fmt.num(d.horas_horometro, 2) + ' h' : '—';
    const ralenti = d.horas_ralenti   ? fmt.num(d.horas_ralenti,   2) + ' h' : '0 h';
    const comb    = d.id_combustible
      ? `<span class="badge badge-ok">✓ Asignado</span>`
      : `<span class="badge badge-warn">Pendiente</span>`;

    return `<tr>
      <td>${fmtFecha(d.fecha)}</td>
      <td><strong>${d.placa || '—'}</strong></td>
      <td class="text-sm">${d.conductor_nombre || '—'}</td>
      <td class="mono">${d.horometro_inicio != null ? fmt.num(d.horometro_inicio, 1) : '—'}</td>
      <td class="mono">${d.horometro_final  != null ? fmt.num(d.horometro_final,  1) : '—'}</td>
      <td><strong>${horas}</strong></td>
      <td style="color:var(--warn)">${ralenti}</td>
      <td>${comb}</td>
      <td>
        <button class="btn btn-outline btn-xs"
                onclick="abrirModalActividades(${d.id_control_dia})">
          Ver / Agregar
        </button>
      </td>
      <td>
        <div class="action-btns">
          <button class="btn btn-outline btn-xs" onclick="abrirModalDia(${d.id_control_dia})">✏</button>
          <button class="btn btn-xs" style="background:var(--danger-lt);color:var(--danger);border:1px solid var(--danger)"
                  onclick="confirmarEliminarDia(${d.id_control_dia})">🗑</button>
        </div>
      </td>
    </tr>`;
  }).join('');

  renderPager('pager-mq', diasPage, total, p => { diasPage = p; renderDias(); });
}

// ── KPIs ─────────────────────────────────────────────────────
function calcularKPIsMaq() {
  const totalH = diasData.reduce((s,d) => s + parseFloat(d.horas_horometro || 0), 0);
  const totalR = diasData.reduce((s,d) => s + parseFloat(d.horas_ralenti   || 0), 0);
  tx('mq-horas',   fmt.num(totalH, 1) + ' h');
  tx('mq-ralenti', fmt.num(totalR, 1) + ' h');
  tx('mq-dias',    diasData.length);
  tx('mq-rend',    '—');
  cargarHorometrosActuales();
}

// ── Horómetro actual por máquina ─────────────────────────────
async function cargarHorometrosActuales() {
  const wrap = document.getElementById('mq-horometros-wrap');
  if (!wrap || !maqCache.length) return;

  const items = await Promise.all(maqCache.map(async u => {
    try {
      const d = await api(`/api/maquinaria/ultimo-horometro?id_unidad=${u.id_unidad}`);
      return { u, h: d.horometro_final || 0, fecha: d.fecha };
    } catch(_) { return { u, h: 0, fecha: null }; }
  }));

  wrap.innerHTML = items.map(({ u, h, fecha }) => `
    <div class="kpi-card" style="border-left:3px solid var(--brand)">
      <div class="kpi-lbl">${u.placa} — Horómetro actual</div>
      <div class="kpi-val brand">${fmt.num(h, 1)} h</div>
      <div class="kpi-sub">${fecha ? 'Último registro: ' + fmtFecha(fecha) : 'Sin registros'}</div>
    </div>`).join('');
}

// ── Gráfico horas apiladas ────────────────────────────────────
function dibujarGraficoHoras() {
  dch('mq-horas');
  const semanasMap = {};
  diasData.forEach(d => {
    const w = 'S' + getISOWeek(d.fecha);
    if (!semanasMap[w]) semanasMap[w] = { prod: 0, ral: 0 };
    const h = parseFloat(d.horas_horometro || 0);
    const r = parseFloat(d.horas_ralenti   || 0);
    semanasMap[w].ral  += r;
    semanasMap[w].prod += Math.max(0, h - r);
  });
  const semanas = Object.keys(semanasMap).sort();
  const ctx = document.getElementById('chart-mq-horas');
  if (!ctx || !semanas.length) return;

  CHARTS['mq-horas'] = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: semanas,
      datasets: [
        { label:'Productivas', data:semanas.map(s=>+semanasMap[s].prod.toFixed(2)), backgroundColor:'rgba(45,106,79,.75)', borderRadius:3, stack:'h' },
        { label:'Ralentí',     data:semanas.map(s=>+semanasMap[s].ral.toFixed(2)),  backgroundColor:'rgba(133,79,11,.65)', borderRadius:3, stack:'h' },
      ],
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{ labels:{ font:{size:11}, color:'#5c5b55' }}},
      scales:{
        x:{ stacked:true, grid:{display:false}, ticks:{font:{size:11},color:'#9c9a92'} },
        y:{ stacked:true, grid:{color:'rgba(0,0,0,.04)'}, ticks:{font:{size:11},color:'#9c9a92'},
            title:{display:true,text:'Horas',font:{size:10}} },
      },
    },
  });
}

// ── Gráfico rendimiento gal/hora ──────────────────────────────
async function dibujarGraficoRendimiento() {
  dch('mq-rend-chart');
  try {
    const rend = await api('/api/compras/rendimiento');
    let data = rend.maquinaria || [];

    // Si no hay datos con combustible asignado, calcular desde días sin asignar
    // usando horas_horometro acumuladas por unidad
    if (!data.length && maqCache.length) {
      const fd = document.getElementById('mq-desde').value;
      const fh = document.getElementById('mq-hasta').value;
      const dias = await api(`/api/maquinaria/dias?fecha_desde=${fd}&fecha_hasta=${fh}`);
      const byU = {};
      dias.forEach(d => {
        if (!byU[d.id_unidad]) byU[d.id_unidad] = { placa: d.placa, horas: 0, n: 0 };
        byU[d.id_unidad].horas += parseFloat(d.horas_horometro || 0);
        byU[d.id_unidad].n++;
      });
      // No hay gal asignados, mostrar solo horas como referencia
      data = Object.values(byU).map(u => ({
        placa: u.placa, horas_totales: u.horas, gll_totales: 0,
        gal_por_hora: null, costo_soles: 0
      }));
    }

    if (!data.length) { tx('mq-rend','Sin datos'); return; }

    // Actualizar KPI (solo con datos reales de combustible)
    const conDatos = data.filter(r => r.gal_por_hora > 0);
    if (conDatos.length) {
      const promedio = conDatos.reduce((s,r) => s + parseFloat(r.gal_por_hora||0), 0) / conDatos.length;
      tx('mq-rend', fmt.num(promedio, 3) + ' gal/h');
    } else {
      tx('mq-rend', 'Pendiente asig.');
    }

    const ctx = document.getElementById('chart-mq-rend');
    if (!ctx) return;
    CHARTS['mq-rend-chart'] = new Chart(ctx, {
      type: 'bar',
      data: {
        labels:   data.map(r => r.placa),
        datasets:[{
          label:'gal/hora',
          data: data.map(r => parseFloat(r.gal_por_hora || 0)),
          backgroundColor: PAL.slice(0, data.length),
          borderRadius:4,
        }],
      },
      options:{
        responsive:true, maintainAspectRatio:false, indexAxis:'y',
        plugins:{
          legend:{display:false},
          tooltip:{ callbacks:{ label: ctx => {
            const r = data[ctx.dataIndex];
            return [
              ` ${fmt.num(ctx.raw,3)} gal/hora`,
              ` ${fmt.num(r.horas_totales||0,1)} h · ${fmt.num(r.gll_totales||0,1)} gll`,
              ` Costo: ${fmt.sol(r.costo_soles||0)}`,
            ];
          }}},
        },
        scales:{
          x:{grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:11},color:'#9c9a92'}},
          y:{grid:{display:false},ticks:{font:{size:11},color:'#5c5b55'}},
        },
      },
    });
  } catch(_) {}
}

// ── Modal día ────────────────────────────────────────────────
async function abrirModalDia(idControl) {
  limpiarFormDia();
  idDiaGuardado = idControl; // si ya existe, podemos agregar actividades

  if (idControl) {
    document.getElementById('modal-dia-title').textContent = 'Editar Día de Trabajo';
    const d = diasData.find(x => x.id_control_dia === idControl);
    if (d) rellenarFormDia(d);
    await cargarActividadesEnModal(idControl);
  } else {
    document.getElementById('modal-dia-title').textContent = 'Registrar Día de Trabajo';
    document.getElementById('md-fecha').value = '<?= date('Y-m-d') ?>';
    document.getElementById('act-lista-modal').innerHTML =
      '<div class="empty-state" style="padding:10px 0;font-size:12px">Guarda el día primero para agregar actividades.</div>';
  }

  abrirModal('modal-dia');
}

function limpiarFormDia() {
  ['md-id','md-h-ini','md-h-fin','md-ralenti'].forEach(id => {
    const el = document.getElementById(id); if (el) el.value = '';
  });
  document.getElementById('md-fecha').value    = '<?= date('Y-m-d') ?>';
  document.getElementById('md-unidad').value   = '';
  document.getElementById('md-conductor-id').value    = '';
  document.getElementById('md-conductor-input').value = '';
  document.getElementById('md-horas-wrap').style.display = 'none';
  document.getElementById('md-h-ini-hint').textContent   = 'Selecciona la máquina para cargar el último horómetro';
  document.getElementById('md-h-ini-hint').style.color   = 'var(--brand)';
  idDiaGuardado = null;
}

function rellenarFormDia(d) {
  document.getElementById('md-id').value        = d.id_control_dia;
  document.getElementById('md-fecha').value     = d.fecha;
  document.getElementById('md-unidad').value    = d.id_unidad    || '';
  document.getElementById('md-conductor-id').value    = d.id_conductor || '';
  // Buscar el nombre del conductor para mostrar en el input
  const ct = maqConductoresCache.find(x => x.id_conductor == d.id_conductor);
  document.getElementById('md-conductor-input').value = ct ? ct.nombre_conductor : '';
  document.getElementById('md-h-ini').value     = d.horometro_inicio ?? '';
  document.getElementById('md-h-fin').value     = d.horometro_final  ?? '';
  document.getElementById('md-ralenti').value   = d.horas_ralenti    ?? 0;
  calcularHoras();
}

async function guardarDia() {
  const id = document.getElementById('md-id').value;
  const payload = {
    fecha:            document.getElementById('md-fecha').value,
    id_unidad:        document.getElementById('md-unidad').value,
    id_conductor:     document.getElementById('md-conductor-id').value || null,
    horometro_inicio: document.getElementById('md-h-ini').value    || null,
    horometro_final:  document.getElementById('md-h-fin').value    || null,
    horas_ralenti:    document.getElementById('md-ralenti').value  || 0,
  };

  if (!payload.fecha || !payload.id_unidad || !payload.horometro_inicio) {
    toast('Fecha, máquina y horómetro inicial son obligatorios', 'error'); return;
  }

  try {
    if (id) {
      await api(`/api/maquinaria/dias/${id}`, { method:'PUT', body:JSON.stringify(payload) });
      idDiaGuardado = parseInt(id);
      toast('Día actualizado', 'ok');
    } else {
      const r = await api('/api/maquinaria/dias', { method:'POST', body:JSON.stringify(payload) });
      idDiaGuardado = r.id_control_dia;
      document.getElementById('md-id').value = idDiaGuardado;
      document.getElementById('modal-dia-title').textContent = 'Editar Día de Trabajo';
      let msg = `Día guardado · ${fmt.num(r.horas_horometro||0, 2)} horas`;
      if (r.asignacion?.asignado) msg += ' · Combustible asignado automáticamente';
      toast(msg, 'ok');
    }
    // Habilitar sección de actividades
    await cargarActividadesEnModal(idDiaGuardado);
    cargarDias();
  } catch(e) {
    toast('Error: ' + e.message, 'error');
  }
}

// ── Actividades en modal ──────────────────────────────────────
async function cargarActividadesEnModal(idDia) {
  if (!idDia) return;
  const lista = document.getElementById('act-lista-modal');
  lista.innerHTML = '<div class="text-sm text-muted" style="padding:6px 0">Cargando actividades…</div>';

  const data = await api(`/api/maquinaria/actividades?id_control_dia=${idDia}`);

  if (!data.length) {
    lista.innerHTML = '<div class="text-sm text-muted" style="padding:6px 0">Sin actividades aún. Agrégalas abajo.</div>';
    return;
  }

  lista.innerHTML = data.map(a => {
    const tipoCls = a.tipo_consumo === 'CONSUMO ALTO' ? 'badge-danger'
                  : a.tipo_consumo === 'CONSUMO MEDIO' ? 'badge-warn' : 'badge-ok';
    return `
    <div style="background:#fff;border:1px solid var(--border);border-radius:8px;padding:9px 12px;display:flex;gap:10px;align-items:flex-start">
      <div style="flex:1">
        <div style="font-size:13px;font-weight:600">${a.actividad}</div>
        <div style="font-size:11px;color:var(--text3)">
          ${a.hora_inicio||'—'} – ${a.hora_fin||'—'}
          · ${fmt.num(a.total_hora||0,2)} h
          ${a.observacion ? `· ${a.observacion}` : ''}
        </div>
      </div>
      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px">
        <span class="badge ${tipoCls}">${a.tipo_consumo}</span>
        <button class="btn btn-xs" style="background:var(--danger-lt);color:var(--danger);border:1px solid var(--danger)"
                onclick="eliminarActividad(${a.id_control_activ})">🗑</button>
      </div>
    </div>`;
  }).join('');
}

async function agregarActividadModal() {
  if (!idDiaGuardado) {
    toast('Guarda el día primero antes de agregar actividades', 'warn'); return;
  }
  const payload = {
    id_control_dia: idDiaGuardado,
    id_actividad:   document.getElementById('act-tipo').value,
    observacion:    document.getElementById('act-obs').value  || null,
    hora_inicio:    document.getElementById('act-hi').value   || null,
    hora_fin:       document.getElementById('act-hf').value   || null
  };
  if (!payload.id_actividad) {
    toast('Selecciona el tipo de actividad', 'error'); return;
  }

  try {
    await api('/api/maquinaria/actividades', { method: 'POST', body: JSON.stringify(payload) });
    toast('Actividad agregada', 'ok');
    document.getElementById('act-tipo').value = '';
    document.getElementById('act-obs').value  = '';
    document.getElementById('act-hi').value   = '';
    document.getElementById('act-hf').value   = '';
    document.getElementById('act-info-wrap').style.display = 'none';
    cargarActividadesEnModal(idDiaGuardado);
  } catch (e) {
    toast('Error: ' + e.message, 'error');
  }
}
</script>