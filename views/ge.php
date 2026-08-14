<?php
// views/ge.php — Grupos Electrógenos
$hoy    = date('Y-m-d');
$mesIni = date('Y-m-01');
$horaAhora = date('H:i');
?>

<div class="flex items-center justify-between mb-12">
  <h1 style="font-size:18px;font-weight:700">Grupos Electrógenos</h1>
  <div style="display:flex;gap:8px">
    <button class="btn btn-outline btn-sm" onclick="abrirModalKardex()">+ Entrada bidón</button>
    <button class="btn btn-primary" onclick="abrirModalGE(null)">+ Registrar consumo</button>
  </div>
</div>

<!-- KPIs dinámicos por GE -->
<div id="kpi-ge-wrap" class="kpi-grid" style="margin-bottom:var(--gap)"></div>

<!-- Alerta saldo bidón -->
<div id="alertas-bidon" style="margin-bottom:var(--gap)"></div>

<!-- Filtros tabla -->
<div class="filter-bar">
  <label>Desde <input type="date" id="ge-desde" value="<?= $mesIni ?>" onchange="cargarGE()"/></label>
  <label>Hasta  <input type="date" id="ge-hasta" value="<?= $hoy ?>"    onchange="cargarGE()"/></label>
  <label>GE
    <select id="ge-filtro-unidad" onchange="cargarGE()" style="font-size:12px;padding:5px 8px;border:1px solid var(--border2);border-radius:6px">
      <option value="">Todos</option>
    </select>
  </label>
  <button class="btn btn-outline btn-sm" onclick="exportarGE()">Excel</button>
</div>

<!-- Tabla registros -->
<div class="card full" style="margin-bottom:var(--gap)">
  <div class="card-title">Registros de consumo</div>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>Fecha</th><th>Hora</th><th>GE</th>
          <th>CI%</th><th>CF%</th><th>Gll Echados</th>
          <th>Horómetro</th><th>Horas</th>
          <th>Gll Cons.</th><th>Gal/Hora</th><th>F.Carga</th><th>kWh est.</th>
          <th>Combustible</th><th>Acción</th>
        </tr>
      </thead>
      <tbody id="ge-tbody">
        <tr><td colspan="14" class="empty">Cargando…</td></tr>
      </tbody>
    </table>
  </div>
  <div class="pager" id="pager-ge"></div>
</div>

<!-- Fila gráficos -->
<div class="row" style="margin-bottom:var(--gap)">
  <!-- Gráfico gal/hora -->
  <div class="card w2">
    <div class="card-title">
      Consumo gal/hora por turno
      <div class="tab-pills" style="margin-bottom:0">
        <button class="tab-pill active" data-ge="all" onclick="filtrarGEChart('all',this)">Todos</button>
        <button class="tab-pill" data-ge="PERKINS" onclick="filtrarGEChart('PERKINS',this)">Perkins</button>
        <button class="tab-pill" data-ge="CATTINI" onclick="filtrarGEChart('CATTINI',this)">Cattini</button>
      </div>
    </div>
    <div class="chart-box" style="height:220px"><canvas id="chart-ge-gph"></canvas></div>
    <p class="chart-hint">Puntos rojos ≥4 gal/h · Click para causa raíz</p>
  </div>

  <!-- Kardex bidón -->
  <div class="card w1">
    <div class="card-title">📋 Kardex del Bidón
      <div style="display:flex;gap:6px;flex-wrap:wrap">
        <button class="btn btn-outline btn-sm" onclick="abrirModalKardex()">+ Entrada manual</button>
        <button class="btn btn-outline btn-sm" style="border-color:var(--warn);color:var(--warn)"
                onclick="reconstruirKardex()" title="Recalcula desde compras y consumos">
          🔄 Reconstruir
        </button>
        <button class="btn btn-outline btn-sm" style="border-color:var(--danger);color:var(--danger)"
                onclick="limpiarPreMayo()" title="Elimina registros anteriores a mayo 2025 y recalcula saldos">
          🗑 Limpiar pre-mayo
        </button>
      </div>
    </div>
    <div id="ge-kardex-resumen">
      <div class="empty-state" style="padding:12px">Cargando…</div>
    </div>
  </div>
</div>

<!-- Gráfico semanal -->
<div class="card full" style="margin-bottom:var(--gap)">
  <div class="card-title">Galones consumidos por semana</div>
  <div class="chart-box" style="height:180px"><canvas id="chart-ge-semanal"></canvas></div>
</div>

<!-- Causa raíz -->
<div id="causa-raiz-panel" style="display:none" class="card full">
  <div class="card-title" id="causa-raiz-titulo">Causa raíz</div>
  <div id="causa-raiz-body" style="font-size:13px"></div>
</div>

<!-- ══ MODAL: Registrar/Editar consumo GE ═══════════════════ -->
<div class="overlay" id="modal-ge">
  <div class="modal modal-lg">
    <div class="modal-hdr">
      <span class="modal-title" id="modal-ge-title">Registrar Consumo GE</span>
      <button class="modal-close" onclick="cerrarModal('modal-ge')">×</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="ge-id-registro"/>
      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Fecha *</label>
          <input type="date" id="ge-fecha" value="<?= $hoy ?>"/>
        </div>
        <div class="fgroup">
          <label>Hora</label>
          <input type="time" id="ge-hora" value="<?= $horaAhora ?>"/>
        </div>
      </div>
      <div class="fgroup">
        <label>Grupo Electrógeno *</label>
        <select id="ge-unidad" onchange="cargarDatosGEAnterior()">
          <option value="">Seleccionar GE…</option>
        </select>
      </div>
      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>CI% (nivel inicial tanque GE)</label>
          <input type="number" id="ge-ci" min="0" max="100" step="1" placeholder="0-100"/>
          <span class="hint" id="ge-ci-hint">Se carga del registro anterior</span>
        </div>
        <div class="fgroup">
          <label>CF% (nivel final tanque GE)</label>
          <input type="number" id="ge-cf" min="0" max="100" step="1" placeholder="0-100"/>
        </div>
      </div>
      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Galones echados al GE <span style="color:var(--text3);font-size:11px">(salida del bidón)</span></label>
          <input type="number" id="ge-gll-echados" min="0" step="0.1" placeholder="0.0"/>
        </div>
        <div class="fgroup">
          <label>Horómetro actual (h)</label>
          <input type="number" id="ge-horometro" min="0" step="0.1" placeholder="horas acumuladas"/>
          <span class="hint" id="ge-horom-hint">Se carga del registro anterior</span>
        </div>
      </div>
      <!-- Valores calculados (solo lectura) -->
      <div id="ge-calc-wrap" style="display:none;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:12px;margin-top:8px">
        <div style="font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;margin-bottom:8px">
          Valores que se calcularán automáticamente
        </div>
        <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr)">
          <div class="kpi-card"><div class="kpi-lbl">Horas trabajadas</div><div class="kpi-val brand" id="ge-prev-horas">—</div></div>
          <div class="kpi-card"><div class="kpi-lbl">Gll consumidos</div><div class="kpi-val brand" id="ge-prev-gll">—</div></div>
          <div class="kpi-card"><div class="kpi-lbl">Gal/hora</div><div class="kpi-val" id="ge-prev-gph">—</div></div>
        </div>
        <div class="hint" style="margin-top:6px">Estos se recalcularán al guardar según CI%/CF% y horómetro.</div>
      </div>
    </div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-ge')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarRegistroGE()">Guardar</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Entrada bidón kardex ══════════════════════════ -->
<div class="overlay" id="modal-kardex">
  <div class="modal modal-sm">
    <div class="modal-hdr">
      <span class="modal-title">Entrada al bidón</span>
      <button class="modal-close" onclick="cerrarModal('modal-kardex')">×</button>
    </div>
    <div class="modal-body">
      <div class="fgroup">
        <label>GE que recibe (bidón compartido)</label>
        <select id="kd-unidad">
          <option value="">Seleccionar GE…</option>
        </select>
      </div>
      <div class="fgroup">
        <label>Galones</label>
        <input type="number" id="kd-galones" min="0.1" step="0.1" placeholder="0.0"/>
      </div>
      <div class="fgroup">
        <label>Compra relacionada</label>
        <select id="kd-combustible">
          <option value="">Sin compra relacionada</option>
        </select>
      </div>
      <div class="fgroup">
        <label>Observación</label>
        <input type="text" id="kd-obs" placeholder="Descripción de la entrada…"/>
      </div>
    </div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-kardex')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarKardexEntrada()">Registrar entrada</button>
    </div>
  </div>
</div>

<script>
/* ══ GE ══════════════════════════════════════════════════════ */
var geData    = [];
var geRawAll  = [];
var geUnidades= [];
var saldosBidon = {};
var gePage    = 0;
var GE_PP     = 20;

document.addEventListener('DOMContentLoaded', function() {
  cargarUnidadesGE().then(function() {
    cargarGE();
    cargarSaldosBidon();
    cargarKardexResumen();
    cargarComprasGE();
  });
});

// ── Catálogos GE ─────────────────────────────────────────────
async function cargarUnidadesGE() {
  var data = await api('/api/unidades');
  // Excluir MEBA
  geUnidades = data.filter(function(u) {
    return u.tipo_unidad === 'GRUPO ELECTROGENO' && u.placa !== 'MEBA';
  });
  renderKPIsGE();
  // Poblar selects
  ['ge-unidad','kd-unidad','ge-filtro-unidad'].forEach(function(selId) {
    var sel = document.getElementById(selId); if (!sel) return;
    geUnidades.forEach(function(u) {
      sel.insertAdjacentHTML('beforeend',
        '<option value="' + u.id_unidad + '">' + u.placa + '</option>');
    });
  });
}

async function cargarComprasGE() {
  try {
    var comps = await api('/api/compras?tipo_combustible=PETROLEO');
    var sel   = document.getElementById('kd-combustible');
    comps.filter(function(c) { return c.cantidad_gll > 0; }).forEach(function(cc) {
      sel.insertAdjacentHTML('beforeend',
        '<option value="' + cc.id_combustible + '">' +
        fmtFecha(cc.fecha) + ' · ' + fmt.num(cc.cantidad_gll,1) + ' gll · ' + fmt.sol(cc.total) +
        '</option>');
    });
  } catch(e) {}
}

function renderKPIsGE() {
  var wrap = document.getElementById('kpi-ge-wrap');
  var html = geUnidades.map(function(u) {
    return '<div class="kpi-card">' +
      '<div class="kpi-lbl">' + u.placa + ' — gal/hora prom.</div>' +
      '<div class="kpi-val brand" id="kpi-gph-' + u.id_unidad + '">— gal/h</div>' +
      '<div class="kpi-sub" id="kpi-gll-' + u.id_unidad + '">— gll consumidos</div>' +
    '</div>';
  }).join('');
  html += '<div class="kpi-card" id="kpi-bidon-compartido" style="border-left:3px solid var(--brand)">' +
    '<div class="kpi-lbl">🛢 Saldo Bidón (compartido)</div>' +
    '<div class="kpi-val brand">Cargando…</div>' +
  '</div>';
  wrap.innerHTML = html;
}

// ── Saldo del bidón compartido ────────────────────────────────
async function cargarSaldosBidon() {
  var alertasWrap = document.getElementById('alertas-bidon');
  alertasWrap.innerHTML = '';
  try {
    var d     = await api('/api/ge/saldo-bidon');
    var saldo = parseFloat(d.saldo_gll || 0);
    geUnidades.forEach(function(u) { saldosBidon[u.id_unidad] = saldo; });

    var kpiEl = document.getElementById('kpi-bidon-compartido');
    if (kpiEl) {
      var cls   = saldo <= 10 ? 'danger' : saldo <= 20 ? 'warn' : 'ok';
      var subTxt = d.dias_restantes
        ? '≈ ' + d.dias_restantes + ' días al ritmo actual'
        : 'galones en reserva';
      kpiEl.innerHTML =
        '<div class="kpi-lbl">🛢 Saldo Bidón (compartido)</div>' +
        '<div class="kpi-val ' + cls + '" style="font-size:22px">' + fmt.num(saldo,1) + ' gll</div>' +
        '<div class="kpi-sub">' + subTxt + '</div>';
    }

    if (d.alerta) {
      alertasWrap.innerHTML =
        '<div style="background:var(--danger-lt);border:1px solid var(--danger);border-radius:8px;padding:12px 16px;font-size:14px;color:var(--danger);font-weight:700;display:flex;align-items:center;gap:10px">' +
        '🚨 SALDO CRÍTICO: ' + fmt.num(saldo,1) + ' gll en bidón — ¡Comprar combustible urgente!</div>';
    } else if (d.alerta_leve) {
      alertasWrap.innerHTML =
        '<div style="background:var(--warn-lt);border:1px solid var(--warn);border-radius:8px;padding:10px 14px;font-size:13px;color:var(--warn)">' +
        '⚠ Saldo bajo: ' + fmt.num(saldo,1) + ' gll en bidón.' +
        (d.dias_restantes ? ' Alcanza para ≈' + d.dias_restantes + ' días.' : '') + ' Planificar compra.</div>';
    }
  } catch(e) { console.error('Error saldo bidon:', e); }
}

// ── Cargar y renderizar registros ────────────────────────────
async function cargarGE() {
  var fd  = document.getElementById('ge-desde').value;
  var fh  = document.getElementById('ge-hasta').value;
  var uid = document.getElementById('ge-filtro-unidad').value;
  var url = '/api/ge/registros?fecha_desde=' + fd + '&fecha_hasta=' + fh;
  if (uid) url += '&id_unidad=' + uid;
  geData   = await api(url);
  geRawAll = geData;
  gePage   = 0;
  renderGETabla();

  // KPIs por unidad
  geUnidades.forEach(function(u) {
    var rows    = geData.filter(function(r) { return r.id_unidad == u.id_unidad; });
    var gph_sum = rows.reduce(function(s,r) { return s + parseFloat(r.consumo_gal_hora||0); }, 0);
    var gph_prom= rows.length ? gph_sum / rows.length : 0;
    var gll_tot = rows.reduce(function(s,r) { return s + parseFloat(r.galones_consumidos||0); }, 0);
    var gphEl   = document.getElementById('kpi-gph-' + u.id_unidad);
    if (gphEl) {
      gphEl.textContent = fmt.num(gph_prom,2) + ' gal/h';
      gphEl.className   = 'kpi-val ' + (gph_prom >= 4.5 ? 'danger' : gph_prom >= 4.0 ? 'warn' : 'ok');
    }
    tx('kpi-gll-' + u.id_unidad, fmt.num(gll_tot,1) + ' gll consumidos');
  });

  dibujarChartGPH('all');
  dibujarChartSemanal();
}

function renderGETabla() {
  var tbody = document.getElementById('ge-tbody');
  var total = Math.max(1, Math.ceil(geData.length / GE_PP));
  var slice = geData.slice(gePage * GE_PP, (gePage+1) * GE_PP);

  if (!slice.length) {
    tbody.innerHTML = '<tr><td colspan="14" class="empty">Sin registros para estos filtros.</td></tr>';
    renderPager('pager-ge', 0, 1, function(){});
    return;
  }

  tbody.innerHTML = slice.map(function(r) {
    var g    = parseFloat(r.consumo_gal_hora || 0);
    var cls  = g >= 4.5 ? 'badge-danger' : g >= 4.0 ? 'badge-warn' : 'badge-ok';
    var comb = r.id_combustible
      ? '<span class="badge badge-ok">✓ #' + r.id_combustible + '</span>'
      : '<span class="badge badge-warn">Sin asignar</span>';
    return '<tr>' +
      '<td>' + fmtFecha(r.fecha) + '</td>' +
      '<td class="mono">' + (r.hora||'—').slice(0,5) + '</td>' +
      '<td><strong>' + r.placa + '</strong></td>' +
      '<td>' + (r.ci_porcentaje!=null?r.ci_porcentaje+'%':'—') + '</td>' +
      '<td>' + (r.cf_porcentaje!=null?r.cf_porcentaje+'%':'—') + '</td>' +
      '<td>' + fmt.num(r.galones_echados||0,1) + ' gll</td>' +
      '<td class="mono">' + (r.horometro!=null?fmt.num(r.horometro,1)+' h':'—') + '</td>' +
      '<td>' + (r.horas_trabajadas ? fmt.num(r.horas_trabajadas,2)+' h' : '—') + '</td>' +
      '<td>' + (r.galones_consumidos ? fmt.num(r.galones_consumidos,2)+' gll' : '—') + '</td>' +
      '<td><span class="badge ' + cls + '">' + fmt.num(g,2) + ' gal/h</span></td>' +
      '<td>' + (r.factor_carga ? fmt.num(r.factor_carga,2) : '—') + '</td>' +
      '<td>' + (r.kwh_estimados ? fmt.num(r.kwh_estimados,1)+' kWh' : '—') + '</td>' +
      '<td>' + comb + '</td>' +
      '<td>' +
        '<button class="btn btn-outline btn-xs" onclick="editarRegistroGE(' + r.id_registro + ')">✏</button> ' +
        '<button class="btn btn-xs" style="background:var(--danger-lt);color:var(--danger);border:1px solid var(--danger)" onclick="eliminarRegistroGE(' + r.id_registro + ')">🗑</button>' +
      '</td>' +
    '</tr>';
  }).join('');

  renderPager('pager-ge', gePage, total, function(p){ gePage=p; renderGETabla(); });
}

// ── Gráfico gal/hora ─────────────────────────────────────────
function filtrarGEChart(tipo, el) {
  document.querySelectorAll('[data-ge]').forEach(function(b){ b.classList.remove('active'); });
  el.classList.add('active');
  dibujarChartGPH(tipo);
}

function dibujarChartGPH(tipo) {
  var data = tipo === 'all' ? geRawAll
    : geRawAll.filter(function(r){ return r.placa === tipo; });

  // Agrupar por placa y fecha
  var byP = {};
  data.forEach(function(r) {
    if (!byP[r.placa]) byP[r.placa] = {};
    byP[r.placa][r.fecha] = { gph: parseFloat(r.consumo_gal_hora||0), id: r.id_registro };
  });

  var placas = Object.keys(byP);
  var fechas  = [...new Set(data.map(function(r){ return r.fecha; }))].sort();

  dch('ge-gph');
  var ctx = document.getElementById('chart-ge-gph'); if (!ctx) return;

  CHARTS['ge-gph'] = new Chart(ctx, {
    type: 'line',
    data: {
      labels: fechas.map(function(f){ return f.slice(5); }),
      datasets: placas.map(function(p, i) {
        return {
          label: p,
          data:  fechas.map(function(f){ return byP[p][f] ? byP[p][f].gph : null; }),
          borderColor:       PAL[i % PAL.length],
          backgroundColor:   'transparent',
          borderWidth:       2,
          spanGaps:          true,
          pointRadius:       fechas.map(function(f){ return byP[p][f] && byP[p][f].gph >= 4 ? 7 : 3; }),
          pointBackgroundColor: fechas.map(function(f){ return byP[p][f] && byP[p][f].gph >= 4 ? '#A32D2D' : PAL[i%PAL.length]; }),
        };
      }),
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins:{
        legend:{ labels:{ font:{size:11}, color:'#5c5b55' }},
        tooltip:{ callbacks:{ label: function(ctx){ return ' ' + fmt.num(ctx.raw,2) + ' gal/hora'; }}},
      },
      scales:{
        x:{ grid:{color:'rgba(0,0,0,.04)'}, ticks:{font:{size:11},color:'#9c9a92'} },
        y:{ grid:{color:'rgba(0,0,0,.04)'}, ticks:{font:{size:11},color:'#9c9a92'} },
      },
      onClick: function(evt, els) {
        if (!els.length) return;
        var f = fechas[els[0].index];
        var p = placas[els[0].datasetIndex];
        var id = byP[p] && byP[p][f] ? byP[p][f].id : null;
        if (id) cargarCausaRaiz(id);
      },
    },
  });
}

function dibujarChartSemanal() {
  var map = {};
  geRawAll.forEach(function(r) {
    var sem = r.fecha.slice(0,7); // YYYY-MM
    if (!map[sem]) map[sem] = 0;
    map[sem] += parseFloat(r.galones_consumidos||0);
  });
  var semanas = Object.keys(map).sort();
  dch('ge-semanal');
  var ctx = document.getElementById('chart-ge-semanal'); if (!ctx) return;
  CHARTS['ge-semanal'] = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: semanas,
      datasets:[{ label:'Gll consumidos', data: semanas.map(function(s){ return +map[s].toFixed(2); }),
        backgroundColor:'rgba(24,95,165,.7)', borderRadius:4 }],
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{display:false} },
      scales:{ x:{grid:{display:false},ticks:{font:{size:11},color:'#9c9a92'}},
               y:{grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:11},color:'#9c9a92'}} },
    },
  });
}

// ── Kardex resumen ────────────────────────────────────────────
async function cargarKardexResumen() {
  var wrap = document.getElementById('ge-kardex-resumen');
  try {
    var todos = await Promise.all(
      geUnidades.map(function(u){ return api('/api/ge/kardex?id_unidad=' + u.id_unidad); })
    );
    var movs = todos.reduce(function(a,b){ return a.concat(b); }, [])
      .sort(function(a,b){ return String(b.fecha).localeCompare(String(a.fecha)); });

    var saldoGlobal = parseFloat(Object.values(saldosBidon)[0] || 0);
    var capTotal    = geUnidades.reduce(function(s,u){ return s + parseFloat(u.capacidad_tanque||0); }, 0);
    var pct         = capTotal > 0 ? Math.min(Math.round(saldoGlobal/capTotal*100),100) : 0;
    var color       = saldoGlobal<=10?'var(--danger)':saldoGlobal<=20?'var(--warn)':'var(--ok)';

    var html =
      '<div style="margin-bottom:10px">' +
        '<div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:13px">' +
          '<strong>Bidón compartido (Perkins + Cattini)</strong>' +
          '<span style="font-weight:700;color:' + color + '">' + fmt.num(saldoGlobal,1) + ' gll</span>' +
        '</div>' +
        '<div style="height:12px;background:var(--border);border-radius:999px;overflow:hidden">' +
          '<div style="height:100%;width:' + pct + '%;background:' + color + ';border-radius:999px"></div>' +
        '</div>' +
        '<div style="font-size:11px;color:var(--text3);margin-top:3px">' + pct + '% de nivel estimado</div>' +
      '</div>' +
      '<div style="font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;margin-bottom:6px">Últimos movimientos</div>';

    if (!movs.length) {
      html += '<div class="empty-state" style="padding:8px">Sin movimientos.</div>';
    } else {
      html += movs.slice(0,10).map(function(m) {
        var esE    = m.tipo_movimiento === 'ENTRADA';
        var uNom   = (geUnidades.find(function(u){ return u.id_unidad==m.id_unidad; })||{}).placa||'GE';
        return '<div style="display:flex;justify-content:space-between;font-size:12px;padding:5px 0;border-bottom:1px solid var(--border)">' +
          '<div>' +
            '<span style="color:' + (esE?'var(--ok)':'var(--danger)') + ';font-weight:700">' + (esE?'+':'-') + fmt.num(m.galones,1) + ' gll</span>' +
            ' <span class="text-muted">' + uNom + '</span>' +
          '</div>' +
          '<div style="text-align:right">' +
            '<div style="font-size:11px;color:var(--text3)">' + fmtFecha(String(m.fecha).slice(0,10)) + '</div>' +
            '<div style="font-size:11px;color:var(--text2)">Saldo: ' + fmt.num(m.saldo_gll,1) + ' gll</div>' +
          '</div>' +
        '</div>';
      }).join('');
    }

    wrap.innerHTML = html;
  } catch(e) {
    wrap.innerHTML = '<div class="empty-state">Error cargando kardex</div>';
  }
}

// ── Reconstruir kardex ────────────────────────────────────────
async function reconstruirKardex() {
  if (!confirm('Reconstruirá el kardex desde cero leyendo compras GE y consumos registrados.\n¿Continuar?')) return;
  try {
    toast('Reconstruyendo kardex…', 'warn');
    var r = await api('/api/ge/reconstruir-kardex', { method: 'POST', body: '{}' });
    toast('Kardex reconstruido: ' + r.entradas + ' entradas · ' + r.salidas + ' salidas · Saldo: ' + fmt.num(r.saldo_final,1) + ' gll', 'ok');
    await Promise.all([cargarSaldosBidon(), cargarKardexResumen()]);
  } catch(e) { toast('Error: ' + e.message, 'error'); }
}

// ── Causa raíz ───────────────────────────────────────────────
async function cargarCausaRaiz(id) {
  var panel = document.getElementById('causa-raiz-panel');
  var body  = document.getElementById('causa-raiz-body');
  panel.style.display = 'block';
  panel.scrollIntoView({ behavior:'smooth' });
  body.innerHTML = '<p class="text-sm">Cargando…</p>';
  try {
    var d = await api('/api/ge/causa-raiz/' + id);
    body.innerHTML =
      '<div style="font-size:13px;font-weight:700">' + d.placa + '</div>' +
      '<div class="text-sm text-muted">' + fmtFecha(d.fecha) + ' · ' + fmt.num(d.consumo_gal_hora,2) + ' gal/hora</div>' +
      '<div class="divider"></div>' +
      (!d.equipos_activos || !d.equipos_activos.length
        ? '<div class="empty-state" style="padding:8px">Sin procesos registrados.</div>'
        : d.equipos_activos.map(function(e) {
            return '<div style="display:flex;justify-content:space-between;font-size:12px;padding:5px 0;border-bottom:1px solid var(--border)">' +
              '<div><div style="font-weight:600">' + e.nombre_equipo + '</div><div class="text-muted">' + (e.proceso||'—') + '</div></div>' +
              '<div style="text-align:right"><div>' + fmt.num(e.horas_trabajo,1) + ' h</div><div class="text-muted">' + fmt.num(e.kwh_consumidos,1) + ' kWh</div></div>' +
            '</div>';
          }).join(''));
  } catch(e) { body.innerHTML = '<p style="color:var(--danger)">Error: ' + e.message + '</p>'; }
}

// ══════════════════════════════════════════════════════════════
// MODAL GE — Nuevo y Editar
// ══════════════════════════════════════════════════════════════
function limpiarModalGE() {
  document.getElementById('ge-id-registro').value = '';
  document.getElementById('modal-ge-title').textContent = 'Registrar Consumo GE';
  document.getElementById('ge-fecha').value     = '<?= $hoy ?>';
  document.getElementById('ge-hora').value      = '<?= $horaAhora ?>';
  document.getElementById('ge-unidad').value    = '';
  document.getElementById('ge-ci').value        = '';
  document.getElementById('ge-cf').value        = '';
  document.getElementById('ge-gll-echados').value = '';
  document.getElementById('ge-horometro').value   = '';
  document.getElementById('ge-calc-wrap').style.display = 'none';
  document.getElementById('ge-ci-hint').textContent    = 'Se carga del registro anterior';
  document.getElementById('ge-horom-hint').textContent = 'Se carga del registro anterior';
}

function abrirModalGE(id) {
  limpiarModalGE();
  abrirModal('modal-ge');
}

async function editarRegistroGE(id_registro) {
  var r = geData.find(function(x){ return x.id_registro == id_registro; });
  if (!r) { toast('Registro no encontrado','error'); return; }

  limpiarModalGE();
  document.getElementById('ge-id-registro').value = r.id_registro;
  document.getElementById('modal-ge-title').textContent = 'Editar GE — ' + fmtFecha(r.fecha);
  document.getElementById('ge-fecha').value        = r.fecha;
  document.getElementById('ge-hora').value         = (r.hora||'').slice(0,5);
  document.getElementById('ge-unidad').value       = r.id_unidad;
  document.getElementById('ge-ci').value           = r.ci_porcentaje != null ? r.ci_porcentaje : '';
  document.getElementById('ge-cf').value           = r.cf_porcentaje != null ? r.cf_porcentaje : '';
  document.getElementById('ge-gll-echados').value  = r.galones_echados || '';
  document.getElementById('ge-horometro').value    = r.horometro || '';

  // Mostrar valores actuales
  var wrap = document.getElementById('ge-calc-wrap');
  wrap.style.display = 'block';
  tx('ge-prev-horas', r.horas_trabajadas  ? fmt.num(r.horas_trabajadas,2)+' h'   : '—');
  tx('ge-prev-gll',   r.galones_consumidos? fmt.num(r.galones_consumidos,2)+' gll': '—');
  tx('ge-prev-gph',   r.consumo_gal_hora  ? fmt.num(r.consumo_gal_hora,2)+' gal/h': '—');

  document.getElementById('ge-ci-hint').textContent    = 'CI% registrado: ' + (r.ci_porcentaje!=null?r.ci_porcentaje:'—') + '%';
  document.getElementById('ge-horom-hint').textContent = 'Horómetro: ' + (r.horometro!=null?fmt.num(r.horometro,1):' —') + ' h';

  abrirModal('modal-ge');
}

async function eliminarRegistroGE(id_registro) {
  if (!confirm('¿Eliminar este registro de consumo GE?')) return;
  try {
    await api('/api/ge/registros/' + id_registro, { method: 'DELETE' });
    toast('Registro eliminado','ok');
    await Promise.all([cargarGE(), cargarSaldosBidon(), cargarKardexResumen()]);
  } catch(e) { toast('Error: '+e.message,'error'); }
}

async function cargarDatosGEAnterior() {
  var id = document.getElementById('ge-unidad').value;
  if (!id) return;
  var idReg = document.getElementById('ge-id-registro').value;
  if (idReg) return; // Si estamos editando, no sobrescribir
  var data = await api('/api/ge/registros?id_unidad=' + id);
  if (data.length > 0) {
    var ult = data[0];
    document.getElementById('ge-ci').value      = ult.cf_porcentaje != null ? ult.cf_porcentaje : '';
    document.getElementById('ge-horometro').value = ult.horometro    != null ? ult.horometro    : '';
    document.getElementById('ge-ci-hint').textContent    = 'CF anterior: ' + (ult.cf_porcentaje!=null?ult.cf_porcentaje:'—') + '%';
    document.getElementById('ge-horom-hint').textContent = 'Horómetro anterior: ' + (ult.horometro!=null?fmt.num(ult.horometro,1):'—') + ' h';
  }
}

async function guardarRegistroGE() {
  var idReg = document.getElementById('ge-id-registro').value;
  var hora  = document.getElementById('ge-hora').value || '';
  if (hora.length === 5) hora = hora + ':00';

  var payload = {
    fecha:          document.getElementById('ge-fecha').value,
    hora:           hora,
    id_unidad:      document.getElementById('ge-unidad').value,
    ci_porcentaje:  document.getElementById('ge-ci').value         || null,
    cf_porcentaje:  document.getElementById('ge-cf').value         || null,
    galones_echados:document.getElementById('ge-gll-echados').value || null,
    horometro:      document.getElementById('ge-horometro').value   || null,
  };

  if (!payload.id_unidad || !payload.ci_porcentaje) {
    toast('Selecciona GE e ingresa CI%', 'error'); return;
  }

  try {
    var method   = idReg ? 'PUT' : 'POST';
    var endpoint = idReg ? '/api/ge/registros/' + idReg : '/api/ge/registros';
    var r = await api(endpoint, { method: method, body: JSON.stringify(payload) });

    // Actualizar fila en geData directamente para respuesta inmediata
    if (idReg) {
      var idx = geData.findIndex(function(x){ return x.id_registro == idReg; });
      if (idx >= 0) {
        geData[idx].ci_porcentaje     = payload.ci_porcentaje;
        geData[idx].cf_porcentaje     = payload.cf_porcentaje;
        geData[idx].galones_echados   = payload.galones_echados;
        geData[idx].horometro         = payload.horometro;
        geData[idx].fecha             = payload.fecha;
        geData[idx].hora              = payload.hora;
        geData[idx].horas_trabajadas  = r.horas_trabajadas;
        geData[idx].galones_consumidos= r.galones_consumidos;
        geData[idx].consumo_gal_hora  = r.consumo_gal_hora;
        geData[idx].factor_carga      = r.factor_carga;
        geData[idx].kwh_estimados     = r.kwh_estimados;
      }
      renderGETabla();
    }

    var partes = [idReg ? 'Actualizado' : 'Guardado'];
    if (r.horas_trabajadas)    partes.push(fmt.num(r.horas_trabajadas,2)    + ' h');
    if (r.galones_consumidos)  partes.push(fmt.num(r.galones_consumidos,2)  + ' gll consumidos');
    if (r.consumo_gal_hora)    partes.push(fmt.num(r.consumo_gal_hora,2)    + ' gal/h');
    if (r.factor_carga)        partes.push('FC ' + fmt.num(r.factor_carga,2));
    if (r.kwh_estimados)       partes.push(fmt.num(r.kwh_estimados,1)       + ' kWh');
    if (r.saldo_bidon != null) partes.push('Bidón: ' + fmt.num(r.saldo_bidon,1) + ' gll');
    toast(partes.join(' · '), 'ok');
    cerrarModal('modal-ge');
    await Promise.all([cargarGE(), cargarSaldosBidon(), cargarKardexResumen()]);
  } catch(e) { toast('Error: '+e.message,'error'); }
}

// ── Modal kardex entrada ─────────────────────────────────────
async function abrirModalKardex() {
  document.getElementById('kd-galones').value  = '';
  document.getElementById('kd-obs').value      = '';
  document.getElementById('kd-unidad').value   = '';
  document.getElementById('kd-combustible').value = '';
  abrirModal('modal-kardex');
}

async function guardarKardexEntrada() {
  var id_u   = document.getElementById('kd-unidad').value;
  var gll    = parseFloat(document.getElementById('kd-galones').value) || 0;
  var obs    = document.getElementById('kd-obs').value || 'Entrada manual bidón';
  var id_comb= document.getElementById('kd-combustible').value || null;
  if (!id_u || gll <= 0) { toast('Selecciona GE e ingresa galones','error'); return; }
  try {
    await api('/api/ge/kardex-entrada', {
      method: 'POST',
      body: JSON.stringify({ id_unidad: id_u, galones: gll, observacion: obs, id_combustible: id_comb }),
    });
    toast('Entrada registrada: ' + fmt.num(gll,1) + ' gll al bidón','ok');
    cerrarModal('modal-kardex');
    await Promise.all([cargarSaldosBidon(), cargarKardexResumen()]);
  } catch(e) { toast('Error: '+e.message,'error'); }
}

// ── Limpiar kardex — empezar desde id_kardex 193 ─────────────
async function limpiarPreMayo() {
  if (!confirm(
    'Esto eliminará todos los movimientos del kardex con id_kardex < 193\n' +
    'y recalculará los saldos desde cero a partir del registro 193.\n\n' +
    '¿Continuar?'
  )) return;
  try {
    toast('Limpiando…', 'warn');
    var r = await api('/api/ge/limpiar-desde-id', { method: 'POST', body: JSON.stringify({ id_kardex: 193 }) });
    toast(
      'Listo: ' + r.eliminados + ' registros eliminados · ' +
      r.movimientos + ' movimientos recalculados · ' +
      'Saldo final: ' + fmt.num(r.saldo_final, 1) + ' gll',
      'ok'
    );
    await Promise.all([cargarSaldosBidon(), cargarKardexResumen()]);
  } catch(e) { toast('Error: ' + e.message, 'error'); }
}

// ── Excel ─────────────────────────────────────────────────────
async function exportarGE() {
  if (!geData.length) { toast('Sin datos','warn'); return; }
  var ws = XLSX.utils.json_to_sheet(geData.map(function(r){ return {
    'Fecha':r.fecha,'Hora':(r.hora||'').slice(0,5),'GE':r.placa,
    'CI%':r.ci_porcentaje,'CF%':r.cf_porcentaje,
    'Gll Echados':parseFloat(r.galones_echados||0),
    'Horómetro':parseFloat(r.horometro||0),
    'Horas':r.horas_trabajadas?parseFloat(r.horas_trabajadas):null,
    'Gll Consumidos':r.galones_consumidos?parseFloat(r.galones_consumidos):null,
    'Gal/Hora':r.consumo_gal_hora?parseFloat(r.consumo_gal_hora):null,
    'Factor Carga':r.factor_carga?parseFloat(r.factor_carga):null,
    'kWh est.':r.kwh_estimados?parseFloat(r.kwh_estimados):null,
  }; }));
  var wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'GE');
  XLSX.writeFile(wb, 'GE_' + document.getElementById('ge-desde').value + '_' + document.getElementById('ge-hasta').value + '.xlsx');
  toast('Excel generado','ok');
}
</script>