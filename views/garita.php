<?php
// views/garita.php
$hoy    = date('Y-m-d');
$mesIni = date('Y-m-01');
$horaAhora = date('H:i');
?>
<style>
.ac-wrap{position:relative}
.ac-input{width:100%;padding:8px 10px;border:1px solid var(--border2);border-radius:8px;font-size:13px;background:#fff;box-sizing:border-box}
.ac-input:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 3px var(--brand-lt)}
.ac-drop{position:absolute;z-index:999;width:100%;max-height:240px;overflow-y:auto;background:#fff;border:1px solid var(--border2);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.12);margin-top:2px;display:none}
.ac-drop.open{display:block}
.ac-item{padding:9px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid var(--border)}
.ac-item:last-child{border-bottom:none}
.ac-item:hover,.ac-item.ac-sel{background:var(--brand-lt)}
.ac-item em{color:var(--brand);font-style:normal;font-weight:700}
.ac-sub{font-size:11px;color:var(--text3);margin-top:1px}
.vc-encurso{border-color:var(--warn)!important;background:rgba(133,79,11,.03)}
</style>

<div class="flex items-center justify-between mb-12">
  <h1 style="font-size:18px;font-weight:700">Garita — Control de Salidas</h1>
  <button class="btn btn-primary" onclick="abrirModalViaje(null)">+ Registrar Salida</button>
</div>

<div class="tab-pills" style="margin-bottom:var(--gap)">
  <button class="tab-pill active" onclick="cambiarTab('viajes',this)">Viajes del día</button>
  <button class="tab-pill"        onclick="cambiarTab('porfecha',this)">Por fechas</button>
  <button class="tab-pill"        onclick="cambiarTab('asign',this)">Asignación combustible</button>
</div>

<!-- TAB 1 -->
<div id="tab-viajes">
  <div class="filter-bar">
    <label>Fecha <input type="date" id="f-fecha" value="<?= $hoy ?>" onchange="cargarViajes()"/></label>
    <label>Unidad
      <select id="f-unidad" onchange="cargarViajes()"><option value="">Todas</option></select>
    </label>
    <label>Actividad
      <select id="f-act" onchange="cargarViajes()">
        <option value="">Todas</option>
        <option>ACOPIO</option><option>LOGISTICA</option><option>AGUA</option><option>RELAVERA</option><option>PAD</option><option>VENTA DE MINERAL</option><option>MANTENIMIENTO</option>
      </select>
    </label>
    <button class="btn btn-outline btn-sm" onclick="exportarViajes()">Excel</button>
  </div>
  <div class="kpi-grid" style="margin-bottom:var(--gap)">
    <div class="kpi-card"><div class="kpi-lbl">Viajes</div><div class="kpi-val brand" id="g-n">—</div></div>
    <div class="kpi-card"><div class="kpi-lbl">En curso</div><div class="kpi-val warn" id="g-curso">—</div><div class="kpi-sub">sin retorno</div></div>
    <div class="kpi-card"><div class="kpi-lbl">km Total</div><div class="kpi-val brand" id="g-km">—</div></div>
    <div class="kpi-card"><div class="kpi-lbl">Sin combustible</div><div class="kpi-val warn" id="g-sin">—</div></div>
    <div class="kpi-card"><div class="kpi-lbl">Completados</div><div class="kpi-val ok" id="g-comp">—</div></div>
  </div>
  <div id="viajes-lista" style="display:flex;flex-direction:column;gap:10px">
    <div class="empty-state"><p>Cargando…</p></div>
  </div>
</div>

<!-- TAB 2 -->
<div id="tab-porfecha" style="display:none">
  <div class="filter-bar">
    <label>Desde <input type="date" id="pf-desde" value="<?= $mesIni ?>" onchange="cargarPorFecha()"/></label>
    <label>Hasta  <input type="date" id="pf-hasta" value="<?= $hoy ?>"    onchange="cargarPorFecha()"/></label>
    <label>Unidad
      <select id="pf-unidad" onchange="cargarPorFecha()"><option value="">Todas</option></select>
    </label>
    <button class="btn btn-outline btn-sm" onclick="exportarPorFecha()">Excel</button>
  </div>
  <div class="card full" style="margin-bottom:var(--gap)">
    <div class="card-title">Resumen por día y unidad <span id="pf-total" class="text-muted text-sm"></span></div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr><th>Fecha</th><th>Unidad</th><th>Viajes</th><th>km</th><th>Actividades</th><th>Con comb.</th><th>Sin comb.</th><th>Ver</th></tr></thead>
        <tbody id="pf-tbody"><tr><td colspan="8" class="empty">Selecciona un rango.</td></tr></tbody>
      </table>
    </div>
    <div class="pager" id="pf-pager"></div>
  </div>
  <div id="pf-det-panel" style="display:none">
    <div class="card full">
      <div class="card-title" id="pf-det-titulo">Detalle</div>
      <div id="pf-det-lista" style="display:flex;flex-direction:column;gap:8px"></div>
    </div>
  </div>
</div>

<!-- TAB 3 -->
<div id="tab-asign" style="display:none">
  <div style="background:var(--brand-lt);border:1px solid var(--brand);border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:var(--gap)">
    <strong>Lógica:</strong> viaje → primer tanqueo con km_tanqueo &gt; km_salida y (km_retorno − km_tanqueo) ≤ 4 km.
  </div>
  <div class="filter-bar" style="margin-bottom:12px">
    <label>Unidad
      <select id="asign-uid" onchange="onCambioUnidadAsign()"><option value="">Seleccionar…</option></select>
    </label>
    <label style="display:flex;align-items:center;gap:6px">
      <input type="checkbox" id="asign-solo" checked onchange="cargarViajesAsign()"/>
      Solo pendientes
    </label>
    <button class="btn btn-outline btn-sm" onclick="cargarViajesAsign()">🔄 Actualizar</button>
  </div>
  <div class="row" style="align-items:flex-start">
    <div class="card w2" style="padding:0">
      <div class="card-title" style="padding:14px 16px 10px">Viajes <span id="asign-count" class="text-muted text-sm"></span></div>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead><tr><th>Fecha</th><th>Unidad</th><th>km Sal.</th><th>km Ret.</th><th>km Rec.</th><th>Actividad</th><th>Estado</th><th>Acción</th></tr></thead>
          <tbody id="asign-tbody"><tr><td colspan="8" class="empty">Selecciona una unidad.</td></tr></tbody>
        </table>
      </div>
    </div>
    <div class="card w1" style="padding:14px 16px">
      <div style="font-size:13px;font-weight:700;margin-bottom:12px" id="asign-titulo">Selecciona un viaje →</div>
      <div id="asign-vinfo" style="display:none;background:var(--bg);border-radius:8px;padding:10px;font-size:12px;margin-bottom:10px">
        <div style="font-weight:700" id="asign-v1">—</div>
        <div class="text-muted" id="asign-v2">—</div>
      </div>
      <!-- Botones ARRIBA de la lista de compras -->
      <div id="asign-btns" style="display:none;margin-bottom:10px">
        <div style="display:flex;gap:6px">
          <button class="btn btn-primary" style="flex:1" onclick="ejecutarAsignacion()">✓ Asignar</button>
          <button class="btn btn-outline" style="flex:1" onclick="desasignarViaje()">✕ Quitar</button>
        </div>
      </div>
      <div style="font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;margin-bottom:8px">Compras disponibles</div>
      <div id="asign-compras" style="display:flex;flex-direction:column;gap:8px;max-height:420px;overflow-y:auto">
        <div class="empty-state" style="padding:10px;font-size:12px">Selecciona un viaje.</div>
      </div>
    </div>
  </div>
</div>

<!-- MODAL VIAJE -->
<div class="overlay" id="modal-viaje">
  <div class="modal modal-lg">
    <div class="modal-hdr">
      <span class="modal-title" id="mv-title">Registrar Salida</span>
      <button class="modal-close" onclick="cerrarModal('modal-viaje')">×</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="mv-id"/>
      <input type="hidden" id="mv-cond-id"/>
      <input type="hidden" id="mv-ruta-id"/>

      <div id="mv-retorno-banner" style="display:none;background:var(--warn-lt);border:1px solid var(--warn);border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:12px">
        <strong>📥 Registrando RETORNO</strong> — Completa km retorno, hora y observación.
      </div>

      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Fecha *</label>
          <input type="date" id="mv-fecha" value="<?= $hoy ?>" onchange="onCambioFechaUnidad()"/>
        </div>
        <div class="fgroup">
          <label>Unidad *</label>
          <select id="mv-unidad" onchange="onCambioFechaUnidad()">
            <option value="">Seleccionar…</option>
          </select>
        </div>
      </div>

      <div class="fgroup">
        <label>Conductor</label>
        <div class="ac-wrap">
          <input type="text" class="ac-input" id="mv-cond-txt" placeholder="Escribe nombre o apellido…"
                 autocomplete="off"
                 oninput="acFiltrar('cond')" onfocus="acFiltrar('cond')" onblur="acCerrar('cond')"/>
          <div class="ac-drop" id="ac-cond"></div>
        </div>
      </div>

      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Tipo de actividad</label>
          <select id="mv-act">
            <option value="">Seleccionar…</option>
            <option>ACOPIO</option><option>LOGISTICA</option><option>AGUA</option><option>RELAVERA</option><option>PAD</option><option>VENTA DE MINERAL</option><option>MANTENIMIENTO</option>
            <option>MANTENIMIENTO</option><option>VENTA DE MINERAL</option><option>PAD</option>
          </select>
        </div>
        <div class="fgroup">
          <label>Hora salida *</label>
          <input type="time" id="mv-hsal"/>
        </div>
      </div>

      <div class="fgroup">
        <label>Ruta</label>
        <div class="ac-wrap">
          <input type="text" class="ac-input" id="mv-ruta-txt" placeholder="Escribe destino u origen…"
                 autocomplete="off"
                 oninput="acFiltrar('ruta')" onfocus="acFiltrar('ruta')" onblur="acCerrar('ruta')"/>
          <div class="ac-drop" id="ac-ruta"></div>
        </div>
      </div>

      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>km Salida</label>
          <input type="number" id="mv-kmsal" step="1" oninput="calcKmRec()"/>
          <span class="hint" id="mv-km-hint">—</span>
        </div>
        <div class="fgroup">
          <label>km Retorno <span style="color:var(--text3);font-size:11px">(al regresar)</span></label>
          <input type="number" id="mv-kmret" step="1" oninput="calcKmRec()"/>
        </div>
      </div>

      <div id="mv-kmrec-wrap" style="display:none;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:10px 14px;margin-bottom:10px">
        km Recorrido: <strong id="mv-kmrec" style="font-size:18px;color:var(--brand)">—</strong>
        <span id="mv-desv" style="font-size:12px;margin-left:10px"></span>
      </div>

      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Hora retorno <span style="color:var(--text3);font-size:11px">(al regresar)</span></label>
          <input type="time" id="mv-hret"/>
        </div>
        <div class="fgroup">
          <label>Observación</label>
          <input type="text" id="mv-obs" placeholder="DETALLE, DESTINO, QUÉ HIZO…" oninput="this.value=this.value.toUpperCase()"/>
        </div>
      </div>
    </div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-viaje')">Cancelar</button>
      <button class="btn btn-primary" id="mv-btn-guardar" onclick="guardarViaje()">Guardar Salida</button>
    </div>
  </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="overlay" id="modal-del-v">
  <div class="modal modal-sm">
    <div class="modal-hdr"><span class="modal-title">Confirmar eliminación</span>
      <button class="modal-close" onclick="cerrarModal('modal-del-v')">×</button></div>
    <div class="modal-body"><p>¿Eliminar este viaje y su asignación de combustible?</p></div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-del-v')">Cancelar</button>
      <button class="btn btn-danger" id="btn-del-v">Eliminar</button>
    </div>
  </div>
</div>

<script>
/* ══ GARITA ══════════════════════════════════════════════════ */
var viajesData       = [];
var pfData           = [], pfPage = 0;
var PF_PP            = 20;
var rutasCache       = [];
var conductoresCache = [];
var viajesAsignData  = [];
var viajeSelec       = null;
var compraSelec      = null;

document.addEventListener('DOMContentLoaded', function() {
  Promise.all([cargarUnidades(), cargarCondCache(), cargarRutasCache()])
    .then(function() { cargarViajes(); });
});

// ── Tabs ──────────────────────────────────────────────────────
function cambiarTab(nombre, el) {
  ['tab-viajes','tab-porfecha','tab-asign'].forEach(function(id) {
    document.getElementById(id).style.display = 'none';
  });
  document.querySelectorAll('.tab-pill').forEach(function(b){ b.classList.remove('active'); });
  document.getElementById('tab-' + nombre).style.display = 'block';
  el.classList.add('active');
  if (nombre === 'porfecha') { cargarPorFecha(); cargarRendConductor(); }
}

// ── Catálogos ─────────────────────────────────────────────────
async function cargarUnidades() {
  var data = await api('/api/unidades');
  var flota = data.filter(function(u){ return u.tipo_unidad === 'FLOTA'; });
  ['f-unidad','pf-unidad','mv-unidad','asign-uid','cond-f-unidad'].forEach(function(selId) {
    var sel = document.getElementById(selId);
    if (!sel) return;
    flota.forEach(function(u) {
      sel.insertAdjacentHTML('beforeend',
        '<option value="' + u.id_unidad + '">' + u.placa + '</option>');
    });
  });
}

async function cargarCondCache() {
  conductoresCache = await api('/api/conductores');
  // Poblar filtros de conductor
  ['f-conductor','cond-f-cond'].forEach(function(selId) {
    var selF = document.getElementById(selId);
    if (!selF) return;
    conductoresCache.forEach(function(ct) {
      selF.insertAdjacentHTML('beforeend',
        '<option value="'+ct.id_conductor+'">'+ct.nombre_conductor+'</option>');
    });
  });
}

async function cargarRutasCache() {
  rutasCache = await api('/api/rutas');
}

// ══════════════════════════════════════════════════════════════
// AUTOCOMPLETE genérico
// ══════════════════════════════════════════════════════════════
var acItems  = { cond:{}, ruta:{} };
var acTimers = {};

function acFiltrar(tipo) {
  clearTimeout(acTimers[tipo]);
  acTimers[tipo] = setTimeout(function() {
    var inputId = tipo === 'cond' ? 'mv-cond-txt' : 'mv-ruta-txt';
    var dropId  = tipo === 'cond' ? 'ac-cond'     : 'ac-ruta';
    var q = (document.getElementById(inputId).value || '').toLowerCase().trim();
    var items;
    if (tipo === 'cond') {
      items = q ? conductoresCache.filter(function(c){
        return c.nombre_conductor.toLowerCase().includes(q);
      }) : conductoresCache;
    } else {
      items = q ? rutasCache.filter(function(r){
        return r.destino.toLowerCase().includes(q) || r.origen.toLowerCase().includes(q);
      }) : rutasCache;
    }
    acItems[tipo] = items;
    acRender(tipo, items, q, document.getElementById(dropId));
  }, 120);
}

function acRender(tipo, items, q, dropEl) {
  if (!items.length) { dropEl.classList.remove('open'); return; }
  var html = items.slice(0,40).map(function(item, idx) {
    if (tipo === 'cond') {
      var nom = item.nombre_conductor;
      var hl  = q ? nom.replace(new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')','gi'),'<em>$1</em>') : nom;
      var sub = item.nro_licencia ? '<div class="ac-sub">Lic: ' + item.nro_licencia + '</div>' : '';
      return '<div class="ac-item" onmousedown="acElegir(\'cond\',' + idx + ')">' + hl + sub + '</div>';
    } else {
      var dest = item.destino || '';
      var orig = item.origen  || '';
      var hd   = q ? dest.replace(new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')','gi'),'<em>$1</em>') : dest;
      var ho   = q ? orig.replace(new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')','gi'),'<em>$1</em>') : orig;
      return '<div class="ac-item" onmousedown="acElegir(\'ruta\',' + idx + ')">' + hd +
             '<div class="ac-sub">desde: ' + ho + ' · ' + item.km_esperado + ' km</div></div>';
    }
  }).join('');
  dropEl.innerHTML = html;
  dropEl.classList.add('open');
}

function acElegir(tipo, idx) {
  var item = acItems[tipo][idx];
  if (!item) return;
  if (tipo === 'cond') {
    document.getElementById('mv-cond-txt').value = item.nombre_conductor;
    document.getElementById('mv-cond-id').value  = item.id_conductor;
  } else {
    document.getElementById('mv-ruta-txt').value = item.destino + ' (desde ' + item.origen + ')';
    document.getElementById('mv-ruta-id').value  = item.id_ruta;
    calcKmRec();
  }
  acCerrar(tipo);
}

function acCerrar(tipo) {
  setTimeout(function() {
    var id = tipo === 'cond' ? 'ac-cond' : 'ac-ruta';
    document.getElementById(id).classList.remove('open');
  }, 160);
}

// ══════════════════════════════════════════════════════════════
// TAB 1: VIAJES DEL DÍA
// ══════════════════════════════════════════════════════════════
async function cargarViajes() {
  var fecha = document.getElementById('f-fecha').value;
  var uid   = document.getElementById('f-unidad').value;
  var act   = document.getElementById('f-act').value;

  // Cargar viajes del día seleccionado
  var url = '/api/garita/viajes?fecha=' + fecha;
  if (uid) url += '&id_unidad=' + uid;
  var viajesDia = await api(url);

  // Cargar también viajes sin retorno de los últimos 30 días (viajes largos multi-día)
  var desde30 = new Date(Date.now()-30*86400000).toISOString().slice(0,10);
  var urlPend = '/api/garita/viajes?fecha_desde=' + desde30 + '&fecha_hasta=' + fecha + '&solo_sin_retorno=1';
  if (uid) urlPend += '&id_unidad=' + uid;
  var viajesPend = await api(urlPend);

  // Combinar: pendientes de retorno (de días anteriores) + viajes del día
  // Evitar duplicados por id_control
  var idsDelDia = {};
  viajesDia.forEach(function(v){ idsDelDia[v.id_control] = true; });
  var pendientesOtrosDias = viajesPend.filter(function(v){ return !idsDelDia[v.id_control]; });

  viajesData = pendientesOtrosDias.concat(viajesDia);

  var condEl = document.getElementById('f-conductor');
  var cond   = condEl ? condEl.value : '';
  var data   = viajesData.slice();
  if (act)  data = data.filter(function(v){ return v.tipo_actividad === act; });
  if (cond) data = data.filter(function(v){ return v.id_conductor == cond; });

  // En curso primero
  data.sort(function(a,b){
    var ac = !a.km_retorno ? 1 : 0;
    var bc = !b.km_retorno ? 1 : 0;
    if (bc !== ac) return bc - ac;
    return (b.hora_salida||'').localeCompare(a.hora_salida||'');
  });

  var enCurso = data.filter(function(v){ return !v.km_retorno; }).length;
  var kmTot   = data.reduce(function(s,v){ return s + Math.max(0, parseFloat(v.km_recorrido||0)); }, 0);
  var sinCom  = data.filter(function(v){ return !v.comb_asignado; }).length;
  var compl = data.filter(function(v){ return v.km_retorno && v.km_retorno > 0; }).length;
  tx('g-n',     data.length);
  tx('g-curso', enCurso);
  tx('g-km',    fmt.num(kmTot,1) + ' km');
  tx('g-sin',   sinCom);
  tx('g-comp',  compl);

  renderViajes(data);
}

function renderViajes(data) {
  var lista = document.getElementById('viajes-lista');
  if (!data.length) {
    lista.innerHTML = '<div class="empty-state"><p>Sin viajes registrados.</p>' +
      '<button class="btn btn-outline btn-sm" onclick="abrirModalViaje(null)">+ Registrar salida</button></div>';
    return;
  }

  var enCursoList = data.filter(function(v){ return !v.km_retorno; });
  var compList    = data.filter(function(v){ return  !!v.km_retorno; });
  var html = '';

  if (enCursoList.length) {
    html += '<div style="background:rgba(133,79,11,.07);border:2px solid var(--warn);border-radius:10px;padding:12px 14px;margin-bottom:10px">' +
      '<div style="font-size:12px;font-weight:700;color:var(--warn);margin-bottom:10px;letter-spacing:.03em">' +
      '🚛 ' + enCursoList.length + ' VEHÍCULO' + (enCursoList.length>1?'S':'') +
      ' EN CURSO — PENDIENTE' + (enCursoList.length>1?'S':'') + ' DE REGISTRAR RETORNO' +
      '</div>' +
      '<div style="display:flex;flex-direction:column;gap:8px">' +
      enCursoList.map(function(v){ return buildCard(v); }).join('') +
      '</div></div>';
  }

  if (compList.length) {
    if (enCursoList.length) html += '<div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.05em;margin:8px 0 6px">Viajes completados</div>';
    html += '<div style="display:flex;flex-direction:column;gap:8px">' +
            compList.map(function(v){ return buildCard(v); }).join('') +
            '</div>';
  }

  lista.innerHTML = html;
}

function buildCard(v) {
  var enC   = !v.km_retorno;
  var kmR   = parseFloat(v.km_recorrido||0);
  var cCls  = v.comb_asignado ? 'badge-ok' : 'badge-warn';
  var cLbl  = v.comb_asignado ? '🔗 Comb.' : '⚠ Sin comb.';
  var aCls  = v.tipo_actividad==='AGUA' ? 'badge-info' : v.tipo_actividad==='ACOPIO' ? 'badge-brand' : 'badge-ok';

  var kmRetStr  = v.km_retorno != null ? fmt.num(v.km_retorno,1) : (enC ? '<em style="color:var(--warn)">Pendiente</em>' : '—');
  var kmRecStr  = kmR > 0 ? fmt.num(kmR,1) + ' km' : '—';
  var horarios  = 'Salida: <strong>' + (v.hora_salida||'—') + '</strong>' +
          (!enC ? ' · Regreso: <strong>' + (v.hora_regreso||'—') + '</strong>' : '');
  var rutaHtml  = v.destino
    ? '<div style="font-size:12px;color:var(--text2);margin-bottom:4px">📍 ' +
    (v.origen||'') + ' → ' + v.destino + '</div>'
    : '';
  var obsHtml   = v.observacion
    ? '<div style="font-size:12px;color:var(--text3);margin-bottom:6px">' + v.observacion + '</div>'
    : '';
  var retBtn  = enC
    ? '<button class="btn btn-success btn-sm" onclick="abrirModalRetorno(' + v.id_control + ')">📥 Registrar retorno</button>'
    : '';

  return '<div class="viaje-card ' + (enC ? 'vc-encurso' : '') + '" style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:12px 14px">' +
    '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">' +
    '<div>' +
      '<div style="font-weight:700;font-size:14px">' + (v.placa||'—') +
      (enC ? ' <span style="background:var(--warn-lt);color:var(--warn);font-weight:700;border-radius:6px;padding:2px 7px;font-size:11px">EN CURSO</span>' : '') +
      '</div>' +
      '<div style="font-size:12px;color:var(--text2)">' + (v.conductor_nombre||'—') + ' · ' + horarios + '</div>' +
    '</div>' +
    '<div style="display:flex;flex-direction:column;gap:4px;align-items:flex-end">' +
      (enC ? '<span class="badge badge-warn">🚛 En curso</span>' : '') +
      '<span class="badge ' + cCls + '">' + cLbl + '</span>' +
      (v.tipo_actividad ? '<span class="badge ' + aCls + '">' + v.tipo_actividad + '</span>' : '') +
    '</div>' +
    '</div>' +
    '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;font-size:12px;background:var(--bg);padding:8px;border-radius:6px;margin-bottom:8px">' +
    '<div><div class="kpi-lbl">km Salida</div><div class="font-bold">' + (v.km_salida!=null?fmt.num(v.km_salida,1):'—') + '</div></div>' +
    '<div><div class="kpi-lbl">km Retorno</div><div class="font-bold">' + kmRetStr + '</div></div>' +
    '<div><div class="kpi-lbl">km Recorrido</div><div class="font-bold" style="color:' + (kmR>0?'var(--brand)':'var(--text3)') + '">' + kmRecStr + '</div></div>' +
    '</div>' +
    rutaHtml + obsHtml +
    '<div class="action-btns">' +
    retBtn +
    '<button class="btn btn-outline btn-xs" onclick="abrirModalViaje(' + v.id_control + ')">✏ Editar</button>' +
    '<button class="btn btn-xs" style="background:var(--danger-lt);color:var(--danger);border:1px solid var(--danger)" onclick="confirmarEliminar(' + v.id_control + ')">🗑</button>' +
    '</div>' +
  '</div>';

}

// ══════════════════════════════════════════════════════════════
// TAB 2: POR FECHAS
// ══════════════════════════════════════════════════════════════
async function cargarPorFecha() {
  var fd  = document.getElementById('pf-desde').value;
  var fh  = document.getElementById('pf-hasta').value;
  var uid = document.getElementById('pf-unidad').value;
  var url = '/api/garita/viajes-por-dia?fecha_desde=' + fd + '&fecha_hasta=' + fh;
  if (uid) url += '&id_unidad=' + uid;
  pfData = await api(url);
  pfPage = 0;
  renderPorFecha();
  tx('pf-total', pfData.length + ' filas · ' + (new Set(pfData.map(function(r){return r.fecha;}))).size + ' días');
}

function renderPorFecha() {
  var tbody = document.getElementById('pf-tbody');
  var total = Math.max(1, Math.ceil(pfData.length / PF_PP));
  var slice = pfData.slice(pfPage * PF_PP, (pfPage+1) * PF_PP);
  if (!slice.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="empty">Sin datos.</td></tr>';
    renderPager('pf-pager',0,1,function(){});
    return;
  }
  tbody.innerHTML = slice.map(function(r) {
    var sinCls = r.viajes_sin_comb > 0 ? 'color:var(--warn);font-weight:700' : 'color:var(--text3)';
    return '<tr>' +
      '<td>' + fmtFecha(r.fecha) + '</td>' +
      '<td><strong>' + r.placa + '</strong></td>' +
      '<td><strong>' + r.n_viajes + '</strong></td>' +
      '<td>' + (r.km_dia>0 ? fmt.num(r.km_dia,1)+' km' : '—') + '</td>' +
      '<td class="text-sm" style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + (r.actividades||'—') + '</td>' +
      '<td style="color:var(--ok)">' + r.viajes_con_comb + '</td>' +
      '<td style="' + sinCls + '">' + r.viajes_sin_comb + '</td>' +
      '<td><button class="btn btn-outline btn-xs" onclick="verDetalleDia(\'' + r.fecha + '\',\'' + r.id_unidad + '\',\'' + r.placa + '\')">Ver →</button></td>' +
    '</tr>';
  }).join('');
  renderPager('pf-pager', pfPage, total, function(p){ pfPage=p; renderPorFecha(); });
}

async function verDetalleDia(fecha, id_unidad, placa) {
  var panel = document.getElementById('pf-det-panel');
  var lista = document.getElementById('pf-det-lista');
  panel.style.display = 'block';
  panel.scrollIntoView({ behavior:'smooth' });
  tx('pf-det-titulo', placa + ' — ' + fmtFecha(fecha));
  lista.innerHTML = '<div class="text-sm text-muted" style="padding:10px">Cargando…</div>';
  var viajes = await api('/api/garita/viajes?fecha=' + fecha + '&id_unidad=' + id_unidad);
  if (!viajes.length) { lista.innerHTML = '<div class="empty-state" style="padding:12px">Sin viajes.</div>'; return; }

  // Agregar estos viajes a viajesData para que abrirModalViaje pueda encontrarlos
  viajes.forEach(function(v) {
    var existe = viajesData.find(function(x){ return x.id_control == v.id_control; });
    if (!existe) viajesData.push(v);
  });

  var kmTot = viajes.reduce(function(s,v){ return s+Math.max(0,parseFloat(v.km_recorrido||0)); },0);
  lista.innerHTML =
    '<div style="background:var(--bg);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:10px">' +
    '<strong>' + viajes.length + '</strong> viajes · <strong>' + fmt.num(kmTot,1) + ' km</strong></div>' +
    viajes.map(function(v) {
      var en  = !v.km_retorno;
      var aCls = v.tipo_actividad==='AGUA'?'badge-info':v.tipo_actividad==='ACOPIO'?'badge-brand':'badge-ok';
      return '<div style="background:#fff;border:1px solid ' + (en?'var(--warn)':'var(--border)') + ';border-radius:8px;padding:10px 12px;margin-bottom:6px">' +
        '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">' +
          '<div>' +
            '<div style="font-size:13px;font-weight:600">' + (v.hora_salida||'—') + (en?'':' → '+(v.hora_regreso||'—')) +
              (en ? ' <span class="badge badge-warn" style="font-size:10px">En curso</span>' : '') + '</div>' +
            '<div style="font-size:12px;color:var(--text2)">' + (v.conductor_nombre||'—') + ' · ' + (v.tipo_actividad||'—') + '</div>' +
            (v.destino ? '<div style="font-size:11px;color:var(--text3)">' + (v.origen||'') + ' → ' + v.destino + '</div>' : '') +
          '</div>' +
          '<div style="display:flex;flex-direction:column;gap:4px;align-items:flex-end">' +
            (v.comb_asignado ? '<span class="badge badge-ok">Comb ✓</span>' : '<span class="badge badge-warn">Sin comb.</span>') +
            (v.tipo_actividad ? '<span class="badge ' + aCls + '">' + v.tipo_actividad + '</span>' : '') +
          '</div>' +
        '</div>' +
        '<div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;color:var(--text3);margin-bottom:6px">' +
          '<span>km ' + (v.km_salida!=null?fmt.num(v.km_salida,1):'—') + ' → ' + (v.km_retorno!=null?fmt.num(v.km_retorno,1):'?') + '</span>' +
          '<span style="font-weight:700;color:var(--brand)">' + (parseFloat(v.km_recorrido||0)>0?fmt.num(v.km_recorrido,1)+' km':'—') + '</span>' +
        '</div>' +
        (v.observacion ? '<div style="font-size:12px;background:var(--bg);border-radius:6px;padding:5px 8px;margin-bottom:6px;color:var(--text2)">📝 ' + v.observacion + '</div>' : '') +
        '<div class="action-btns">' +
          (en ? '<button class="btn btn-success btn-sm" onclick="abrirModalRetorno(' + v.id_control + ')">📥 Registrar retorno</button>' : '') +
          '<button class="btn btn-outline btn-xs" onclick="abrirModalViaje(' + v.id_control + ')">✏ Editar</button>' +
          '<button class="btn btn-xs" style="background:var(--danger-lt);color:var(--danger);border:1px solid var(--danger)" onclick="confirmarEliminarPF(' + v.id_control + ')">🗑</button>' +
        '</div>' +
      '</div>';
    }).join('');
}

function confirmarEliminarPF(id) {
  document.getElementById('btn-del-v').onclick = async function() {
    try {
      await api('/api/garita/viajes/'+id,{method:'DELETE'});
      toast('Viaje eliminado','ok'); cerrarModal('modal-del-v');
      // Refrescar el panel — cerrar y limpiar
      document.getElementById('pf-det-panel').style.display='none';
      cargarPorFecha();
    } catch(e){ toast('Error: '+e.message,'error'); }
  };
  abrirModal('modal-del-v');
}

// ── Rendimiento por conductor ─────────────────────────────────
var rendCondData = { resumen:[], detalle:[] };

async function cargarRendConductor() {
  var fdEl  = document.getElementById('pf-desde');
  var fhEl  = document.getElementById('pf-hasta');
  var uidEl = document.getElementById('cond-f-unidad');
  var cidEl = document.getElementById('cond-f-cond');
  if (!fdEl || !fhEl || !uidEl || !cidEl) return;
  var fd  = fdEl.value;
  var fh  = fhEl.value;
  var uid = uidEl.value;
  var cid = cidEl.value;
  var url = '/api/dashboard/rendimiento-conductor?fecha_desde='+fd+'&fecha_hasta='+fh;
  if (uid) url += '&id_unidad='+uid;
  if (cid) url += '&id_conductor='+cid;

  try {
    rendCondData = await api(url);
    renderResumenConductor(rendCondData.resumen || []);
  } catch(e) { toast('Error: '+e.message,'error'); }
}

function formatMin(min) {
  if (!min || min < 0) return '—';
  var h = Math.floor(min/60);
  var m = Math.round(min%60);
  return h > 0 ? h+'h '+m+'m' : m+'m';
}

function renderResumenConductor(data) {
  var tbody = document.getElementById('cond-resumen-tbody');
  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="empty">Sin datos para el período.</td></tr>'; return;
  }
  tbody.innerHTML = data.map(function(r) {
    return '<tr style="cursor:pointer" onclick="verDetalleConductor('+r.id_conductor+')">'+
      '<td><strong>'+r.nombre_conductor+'</strong></td>'+
      '<td class="font-bold" style="color:var(--brand)">'+r.n_viajes+'</td>'+
      '<td>'+(parseFloat(r.km_total||0)>0?fmt.num(r.km_total,1)+' km':'—')+'</td>'+
      '<td>'+formatMin(r.min_prom_viaje)+'</td>'+
      '<td>'+formatMin(r.min_min_viaje)+'</td>'+
      '<td>'+formatMin(r.min_max_viaje)+'</td>'+
      '<td class="text-sm" style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'+
        (r.actividades||'—')+'</td>'+
      '<td>'+r.n_unidades+'</td>'+
    '</tr>';
  }).join('');
}

function verDetalleConductor(id_conductor) {
  var resumen = (rendCondData.resumen||[]).find(function(r){ return r.id_conductor==id_conductor; });
  var nombre  = resumen ? resumen.nombre_conductor : 'Conductor';
  var detalle = (rendCondData.detalle||[]).filter(function(v){ return v.id_conductor==id_conductor; });
  var panel   = document.getElementById('cond-det-panel');
  var tbody   = document.getElementById('cond-det-tbody');
  tx('cond-det-titulo', nombre + ' — '+detalle.length+' viajes');
  panel.style.display = 'block';
  panel.scrollIntoView({behavior:'smooth'});

  if (!detalle.length) {
    tbody.innerHTML = '<tr><td colspan="9" class="empty">Sin viajes con horario registrado.</td></tr>'; return;
  }
  tbody.innerHTML = detalle.map(function(v) {
    var dur = formatMin(v.duracion_min);
    var aCls = v.tipo_actividad==='AGUA'?'badge-info':v.tipo_actividad==='ACOPIO'?'badge-brand':'badge-ok';
    return '<tr>'+
      '<td>'+fmtFecha(v.fecha)+'</td>'+
      '<td><strong>'+v.placa+'</strong></td>'+
      '<td>'+(v.tipo_actividad?'<span class="badge '+aCls+'">'+v.tipo_actividad+'</span>':'—')+'</td>'+
      '<td class="text-sm">'+(v.destino?v.origen+'→'+v.destino:'—')+'</td>'+
      '<td class="mono">'+(v.hora_salida||'—')+'</td>'+
      '<td class="mono">'+(v.hora_regreso||'—')+'</td>'+
      '<td style="font-weight:700;color:var(--brand)">'+dur+'</td>'+
      '<td>'+(parseFloat(v.km_recorrido||0)>0?fmt.num(v.km_recorrido,1)+' km':'—')+'</td>'+
      '<td class="text-sm" style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="'+(v.observacion||'')+'">'+
        (v.observacion||'—')+'</td>'+
    '</tr>';
  }).join('');
}

async function exportarConductores() {
  if (!rendCondData.detalle || !rendCondData.detalle.length) { toast('Sin datos','warn'); return; }
  var ws = XLSX.utils.json_to_sheet(rendCondData.detalle.map(function(v){ return {
    'Fecha':v.fecha,'Conductor':v.nombre_conductor,'Unidad':v.placa,
    'Actividad':v.tipo_actividad||'—','Ruta':v.destino?v.origen+'→'+v.destino:'—',
    'Hora salida':v.hora_salida||'—','Hora regreso':v.hora_regreso||'—',
    'Duración (min)':v.duracion_min||'','km':parseFloat(v.km_recorrido||0),
    'Observación':v.observacion||'—'
  }; }));
  var wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,ws,'Por Conductor');
  XLSX.writeFile(wb,'Conductores_'+document.getElementById('pf-desde').value+'_'+document.getElementById('pf-hasta').value+'.xlsx');
  toast('Excel generado','ok');
}

async function exportarPorFecha() {
  if (!pfData.length) { toast('Sin datos','warn'); return; }
  var ws = XLSX.utils.json_to_sheet(pfData.map(function(r){ return {
    'Fecha':r.fecha,'Unidad':r.placa,'Viajes':r.n_viajes,
    'km':parseFloat(r.km_dia||0),'Actividades':r.actividades||'—',
    'Con comb.':r.viajes_con_comb,'Sin comb.':r.viajes_sin_comb
  }; }));
  var wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Viajes por fecha');
  XLSX.writeFile(wb, 'Viajes_' + document.getElementById('pf-desde').value + '_' + document.getElementById('pf-hasta').value + '.xlsx');
  toast('Excel generado','ok');
}

// ══════════════════════════════════════════════════════════════
// TAB 3: ASIGNACIÓN
// ══════════════════════════════════════════════════════════════
async function onCambioUnidadAsign() {
  viajeSelec = null; compraSelec = null;
  document.getElementById('asign-vinfo').style.display = 'none';
  document.getElementById('asign-btns').style.display  = 'none';
  document.getElementById('asign-compras').innerHTML   = '<div class="empty-state" style="padding:10px;font-size:12px">Selecciona un viaje.</div>';
  await cargarViajesAsign();
}

async function cargarViajesAsign() {
  var id_u = document.getElementById('asign-uid').value;
  var solo = document.getElementById('asign-solo').checked;
  if (!id_u) {
    document.getElementById('asign-tbody').innerHTML = '<tr><td colspan="8" class="empty">Selecciona una unidad.</td></tr>';
    return;
  }
  var desde = new Date(Date.now()-60*86400000).toISOString().slice(0,10);
  var url = '/api/garita/viajes?id_unidad=' + id_u + '&fecha_desde=' + desde;
  if (solo) url += '&solo_sin_asignar=1';
  viajesAsignData = await api(url);
  tx('asign-count', viajesAsignData.length + ' viajes');
  renderViajesAsign();
}

function renderViajesAsign() {
  var tbody = document.getElementById('asign-tbody');
  if (!viajesAsignData.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="empty">Sin viajes.</td></tr>'; return;
  }
  tbody.innerHTML = viajesAsignData.map(function(v) {
    var kmR  = parseFloat(v.km_recorrido||0);
    var comb = v.comb_asignado
      ? '<span class="badge badge-ok" style="font-size:10px">✓ #'+v.comb_asignado+'</span>'
      : '<span class="badge badge-warn" style="font-size:10px">Sin asig.</span>';
    var asel = viajeSelec && viajeSelec.id_control===v.id_control;
    return '<tr style="' + (asel?'background:var(--brand-lt)':'') + '">' +
      '<td>' + fmtFecha(v.fecha) + '</td>' +
      '<td><strong>' + v.placa + '</strong></td>' +
      '<td class="mono">' + (v.km_salida!=null?fmt.num(v.km_salida,1):'—') + '</td>' +
      '<td class="mono">' + (v.km_retorno!=null?fmt.num(v.km_retorno,1):'<em style="color:var(--warn)">En curso</em>') + '</td>' +
      '<td>' + (kmR>0?fmt.num(kmR,1)+' km':'—') + '</td>' +
      '<td>' + (v.tipo_actividad||'—') + '</td>' +
      '<td>' + comb + '</td>' +
      '<td><button class="btn btn-outline btn-xs" onclick="seleccionarViaje(' + v.id_control + ')">Asignar →</button></td>' +
    '</tr>';
  }).join('');
}

async function seleccionarViaje(id_control) {
  viajeSelec  = viajesAsignData.find(function(v){ return v.id_control === id_control; });
  compraSelec = null;
  if (!viajeSelec) return;
  renderViajesAsign();
  document.getElementById('asign-vinfo').style.display = 'block';
  tx('asign-v1', viajeSelec.placa + ' — ' + fmtFecha(viajeSelec.fecha));
  tx('asign-v2', 'km ' + (viajeSelec.km_salida!=null?fmt.num(viajeSelec.km_salida,1):'—') +
     ' → ' + (viajeSelec.km_retorno!=null?fmt.num(viajeSelec.km_retorno,1):'En curso') +
     ' · ' + (viajeSelec.tipo_actividad||'—'));
  tx('asign-titulo','Compras para este viaje:');
  await cargarComprasAsign(viajeSelec);
}

async function cargarComprasAsign(viaje) {
  var lista = document.getElementById('asign-compras');
  lista.innerHTML = '<div class="text-sm text-muted" style="padding:8px">Cargando…</div>';
  document.getElementById('asign-btns').style.display = 'none';
  var url = '/api/garita/compras-para-asignar?id_unidad=' + viaje.id_unidad +
            '&km_salida=' + (viaje.km_salida||0) + '&km_retorno=' + (viaje.km_retorno||0) +
            '&id_control=' + viaje.id_control;
  var compras = await api(url);
  if (!compras.length) {
    lista.innerHTML = '<div class="empty-state" style="padding:10px;font-size:12px">Sin compras para esta unidad.</div>'; return;
  }
  var MARGEN = 4;

  // Usar la clasificación del servidor si existe, si no recalcular localmente
  var kmS   = parseFloat(viaje.km_salida||0);
  var kmRet = parseFloat(viaje.km_retorno||0);

  // Calcular relacion localmente para consistencia con el servidor
  var kmAntMax = 0;
  compras.filter(function(cc){ return parseInt(cc.tanqueo||1)===1 && parseFloat(cc.km_vehiculo||0) < kmS; })
         .forEach(function(cc){ var k=parseFloat(cc.km_vehiculo||0); if(k>kmAntMax) kmAntMax=k; });

  compras.forEach(function(cc){
    var kmT   = parseFloat(cc.km_vehiculo||0);
    var difR  = kmRet > 0 ? kmRet - kmT : -1;
    var esTanq= parseInt(cc.tanqueo||1) === 1;
    var dur   = esTanq && kmT >= kmS && difR <= MARGEN;
    var ant   = esTanq && kmT < kmS && kmT === kmAntMax;
    var emg   = !esTanq && kmT >= kmS && (kmRet===0 || kmT <= kmRet);
    if (dur)       cc.relacion = 'DURANTE';
    else if (ant)  cc.relacion = 'ANTERIOR';
    else if (emg)  cc.relacion = 'EMERGENCIA';
    else if (esTanq && kmT >= kmS && (kmRet===0||kmT<=kmRet)) cc.relacion = 'EN_RANGO';
    else if (kmRet>0 && kmT > kmRet) cc.relacion = 'POSTERIOR';
    else           cc.relacion = cc.relacion || 'OTRO';
    cc.sugerido = (dur || ant) ? 1 : 0;
  });

  // Ordenar: DURANTE > ANTERIOR > EMERGENCIA > EN_RANGO > POSTERIOR > OTRO
  var orden = {DURANTE:0,ANTERIOR:1,EMERGENCIA:2,EN_RANGO:3,POSTERIOR:4,OTRO:5};
  compras.sort(function(a,b){
    var oa = orden[a.relacion]||5, ob = orden[b.relacion]||5;
    return oa!==ob ? oa-ob : parseFloat(b.km_vehiculo||0)-parseFloat(a.km_vehiculo||0);
  });

  var primera = compras[0];

  lista.innerHTML = compras.map(function(cc) {
    var rel    = cc.relacion || 'OTRO';
    var esSug  = cc.sugerido==1;
    var esAct  = cc.ya_asignado==1;
    var esEmg  = rel==='EMERGENCIA';
    var bord   = esSug ? '2px solid var(--brand)' : esAct ? '2px solid var(--ok)' : esEmg ? '2px solid var(--warn)' : '1px solid var(--border)';
    var bg     = esSug ? 'var(--brand-lt)' : esAct ? 'var(--ok-lt)' : esEmg ? 'var(--warn-lt)' : '#fff';
    var kmT    = parseFloat(cc.km_vehiculo||0);
    var difR   = kmRet > 0 ? kmRet - kmT : -1;
    var c1ok   = kmT >= kmS;
    var c2ok   = kmRet===0 || difR<=MARGEN;
    var c2txt  = kmRet===0 ? '(viaje en curso)' : difR<=0 ? '(retornó antes del tanqueo)' : difR<=MARGEN ? '(≤'+MARGEN+' km ✓)' : '(>'+MARGEN+' km)';
    var icon   = rel==='DURANTE' ? '⭐' : rel==='ANTERIOR' ? '⬆' : rel==='EMERGENCIA' ? '🚨' : rel==='EN_RANGO' ? '📍' : rel==='POSTERIOR' ? '⏭' : '◽';
    var badge  = rel==='DURANTE'   ? '<span class="badge badge-brand"  style="font-size:10px">SUGERIDA</span>'
               : rel==='ANTERIOR'  ? '<span class="badge badge-info"   style="font-size:10px">ANTERIOR</span>'
               : rel==='EMERGENCIA'? '<span class="badge badge-warn"   style="font-size:10px">EMERGENCIA</span>'
               : rel==='EN_RANGO'  ? '<span class="badge badge-info"   style="font-size:10px">EN RANGO</span>'
               : rel==='POSTERIOR' ? '<span class="badge"              style="font-size:10px">POSTERIOR</span>'
               : '';
    return '<div onclick="acElegirCompra(' + cc.id_combustible + ',this)" data-id="' + cc.id_combustible + '"' +
           ' style="cursor:pointer;padding:10px 12px;border:' + bord + ';background:' + bg + ';border-radius:8px;transition:.15s">' +
      '<div style="display:flex;justify-content:space-between;align-items:flex-start">' +
        '<div style="font-size:13px;font-weight:700">' + icon + ' ' + fmtFecha(cc.fecha) + ' ' + badge +
          (esAct ? ' <span class="badge badge-ok" style="font-size:10px">YA ASIG.</span>' : '') + '</div>' +
        '<span style="font-size:13px;font-weight:700;color:var(--brand)">' + fmt.sol(cc.total) + '</span>' +
      '</div>' +
      '<div style="font-size:12px;color:var(--text2);margin-top:4px">km: <strong>' + fmt.num(kmT,1) +
        '</strong> · ' + fmt.num(cc.cantidad_gll,1) + ' gll · ' + fmt.sol(cc.precio_unitario) + '/gll</div>' +
      '<div style="font-size:11px;color:var(--text3);margin-top:2px">' + (cc.nro_comprobante||'Sin comprobante') +
        ' · ' + cc.viajes_asignados + ' viaje' + (cc.viajes_asignados!=1?'s':'') + ' asignado' + (cc.viajes_asignados!=1?'s':'') + '</div>' +
      // Solo mostrar condiciones si es relevante (no para POSTERIOR ni OTRO)
      (rel!=='POSTERIOR'&&rel!=='OTRO' ?
        '<div style="margin-top:5px;font-size:11px;display:flex;flex-direction:column;gap:1px">' +
          '<div style="color:' + (c1ok?'var(--ok)':'var(--danger)') + '">' + (c1ok?'✓':'✗') + ' km salida (' + fmt.num(kmS,1) + ') ≤ km tanqueo (' + fmt.num(kmT,1) + ')</div>' +
          (kmRet>0 ? '<div style="color:' + (c2ok?'var(--ok)':'var(--danger)') + '">' + (c2ok?'✓':'✗') + ' dif = ' + (difR>0?'+':'') + fmt.num(difR,1) + ' km ' + c2txt + '</div>' : '') +
        '</div>' : '') +
    '</div>';
  }).join('');

  // Guardar en cache para que ejecutarAsignacion pueda verificar la relacion
  window._comprasCache = compras;

  // Auto-seleccionar la primera sugerida o la primera de la lista
  if (primera) {
    compraSelec = primera.id_combustible;
    document.getElementById('asign-btns').style.display = 'block';
  }
}

function acElegirCompra(id, el) {
  compraSelec = id;
  document.querySelectorAll('#asign-compras > div').forEach(function(d){
    d.style.outline = d.dataset.id == id ? '2px solid var(--brand)' : '';
  });
  document.getElementById('asign-btns').style.display = 'block';
}

async function ejecutarAsignacion() {
  if (!viajeSelec || !compraSelec) { toast('Selecciona viaje y compra','error'); return; }

  // Verificar que la compra seleccionada esté en rango válido del viaje
  // Solo permitir doble/triple asignación si es DURANTE, ANTERIOR, EMERGENCIA o EN_RANGO
  var compra = (window._comprasCache||[]).find(function(cc){ return cc.id_combustible==compraSelec; });
  var rel = compra ? (compra.relacion||'OTRO') : 'OTRO';
  var permitido = ['DURANTE','ANTERIOR','EMERGENCIA','EN_RANGO'].indexOf(rel) >= 0;

  if (!permitido) {
    if (!confirm(
      'Esta compra es de tipo "' + rel + '" y está fuera del rango normal del viaje.\n' +
      '¿Deseas asignarla de todas formas?'
    )) return;
  }

  try {
    await api('/api/compras/asignar-viaje', {method:'POST', body:JSON.stringify({
      id_control:     viajeSelec.id_control,
      id_combustible: compraSelec,
      accion:         'agregar'  // agrega sin borrar otras compras ya asignadas
    })});

    toast('✓ Compra asignada al viaje', 'ok');

    // Recargar compras del mismo viaje para ver cuáles quedan por asignar
    // (no limpiar selección — permite asignar otra compra enseguida)
    await cargarComprasAsign(viajeSelec);
    await cargarViajesAsign();

  } catch(e){ toast('Error: '+e.message,'error'); }
}

async function desasignarViaje() {
  if (!viajeSelec) { toast('Selecciona un viaje','warn'); return; }

  // Si hay una compra seleccionada, quitar solo esa; si no, quitar toda la asignación
  var compra = (window._comprasCache||[]).find(function(cc){ return cc.id_combustible==compraSelec; });
  var msg = compraSelec
    ? '¿Quitar solo esta compra del viaje? Las demás asignaciones se mantienen.'
    : '¿Quitar TODA la asignacion de combustible de este viaje?';
  if (!confirm(msg)) return;

  try {
    if (compraSelec) {
      await api('/api/compras/asignar-viaje', {method:'POST', body:JSON.stringify({
        id_control:     viajeSelec.id_control,
        id_combustible: compraSelec,
        accion:         'quitar'
      })});
      toast('Compra quitada del viaje','ok');
    } else {
      await api('/api/compras/asignar-viaje', {method:'POST', body:JSON.stringify({
        id_control:     viajeSelec.id_control,
        id_combustible: null,
        accion:         'reemplazar'
      })});
      toast('Asignación quitada','ok');
    }
    await cargarComprasAsign(viajeSelec);
    await cargarViajesAsign();
  } catch(e){ toast('Error: '+e.message,'error'); }
}

// ══════════════════════════════════════════════════════════════
// MODAL VIAJE
// ══════════════════════════════════════════════════════════════
function limpiarMV() {
  ['mv-id','mv-cond-id','mv-ruta-id','mv-kmsal','mv-kmret','mv-obs'].forEach(function(id){
    var el=document.getElementById(id); if(el) el.value='';
  });
  document.getElementById('mv-cond-txt').value = '';
  document.getElementById('mv-ruta-txt').value = '';
  document.getElementById('mv-fecha').value    = '<?= $hoy ?>';
  document.getElementById('mv-unidad').value   = '';
  document.getElementById('mv-act').value      = '';
  document.getElementById('mv-hsal').value     = '';
  document.getElementById('mv-hret').value     = '';
  document.getElementById('mv-kmrec-wrap').style.display    = 'none';
  document.getElementById('mv-retorno-banner').style.display= 'none';
  tx('mv-km-hint','—');
  tx('mv-btn-guardar','Guardar Salida');
}

async function abrirModalViaje(id_control) {
  limpiarMV();
  document.getElementById('mv-title').textContent = id_control ? 'Editar Viaje' : 'Registrar Salida';
  if (id_control) {
    var v = viajesData.find(function(x){ return x.id_control==id_control; });
    if (v) {
      document.getElementById('mv-id').value       = v.id_control;
      document.getElementById('mv-fecha').value    = v.fecha;
      document.getElementById('mv-unidad').value   = v.id_unidad    || '';
      document.getElementById('mv-cond-id').value  = v.id_conductor || '';
      document.getElementById('mv-ruta-id').value  = v.id_ruta      || '';
      document.getElementById('mv-act').value      = v.tipo_actividad || '';
      document.getElementById('mv-kmsal').value    = v.km_salida    != null ? v.km_salida : '';
      document.getElementById('mv-kmret').value    = v.km_retorno   != null ? v.km_retorno : '';
      document.getElementById('mv-hsal').value     = v.hora_salida  || '';
      document.getElementById('mv-hret').value     = v.hora_regreso || '';
      document.getElementById('mv-obs').value      = v.observacion  || '';
      if (v.conductor_nombre) document.getElementById('mv-cond-txt').value = v.conductor_nombre;
      if (v.destino) document.getElementById('mv-ruta-txt').value = v.destino + ' (desde ' + (v.origen||'') + ')';
      calcKmRec();
    }
  } else {
    document.getElementById('mv-hsal').value = '<?= $horaAhora ?>';
    await onCambioFechaUnidad();
  }
  abrirModal('modal-viaje');
}

async function abrirModalRetorno(id_control) {
  limpiarMV();
  var v = viajesData.find(function(x){ return x.id_control==id_control; });
  if (!v) return;
  document.getElementById('mv-title').textContent = '📥 Registrar Retorno';
  document.getElementById('mv-retorno-banner').style.display = 'block';
  tx('mv-btn-guardar','Guardar Retorno');
  document.getElementById('mv-id').value      = v.id_control;
  document.getElementById('mv-fecha').value   = v.fecha;
  document.getElementById('mv-unidad').value  = v.id_unidad    || '';
  document.getElementById('mv-cond-id').value = v.id_conductor || '';
  document.getElementById('mv-ruta-id').value = v.id_ruta      || '';
  document.getElementById('mv-act').value     = v.tipo_actividad || '';
  document.getElementById('mv-kmsal').value   = v.km_salida    != null ? v.km_salida : '';
  document.getElementById('mv-hsal').value    = v.hora_salida  || '';
  document.getElementById('mv-obs').value     = v.observacion  || '';
  if (v.conductor_nombre) document.getElementById('mv-cond-txt').value = v.conductor_nombre;
  if (v.destino) document.getElementById('mv-ruta-txt').value = v.destino + ' (desde ' + (v.origen||'') + ')';
  document.getElementById('mv-hret').value = '<?= $horaAhora ?>';
  var ruta = rutasCache.find(function(r){ return r.id_ruta == v.id_ruta; });
  if (ruta && v.km_salida) {
    tx('mv-km-hint','Sugerido: ' + fmt.num(Math.round(parseFloat(v.km_salida)+parseFloat(ruta.km_esperado)),1) + ' km');
  }
  abrirModal('modal-viaje');
  setTimeout(function(){ document.getElementById('mv-kmret').focus(); },300);
}

async function onCambioFechaUnidad() {
  var id_u = document.getElementById('mv-unidad').value;
  if (!id_u) { tx('mv-km-hint','—'); return; }
  var d = await api('/api/garita/ultimo-km?id_unidad=' + id_u);
  if (d.ultimo_km > 0) {
    document.getElementById('mv-kmsal').value = d.ultimo_km;
    tx('mv-km-hint','Último km: ' + fmt.num(d.ultimo_km,1) + ' (' + fmtFecha(d.fecha) + ')');
    calcKmRec();
  } else {
    tx('mv-km-hint','Sin registros previos');
  }
}

function calcKmRec() {
  var ks   = parseFloat(document.getElementById('mv-kmsal').value) || 0;
  var kr   = parseFloat(document.getElementById('mv-kmret').value) || 0;
  var wrap = document.getElementById('mv-kmrec-wrap');
  if (ks<=0||kr<=0||kr<=ks) { wrap.style.display='none'; return; }
  var rec  = kr - ks;
  document.getElementById('mv-kmrec').textContent = fmt.num(rec,1) + ' km';
  wrap.style.display = 'block';
  var ruta_id = document.getElementById('mv-ruta-id').value;
  var ruta    = rutasCache.find(function(r){ return r.id_ruta == ruta_id; });
  var desvEl  = document.getElementById('mv-desv');
  if (ruta) {
    var desv = rec - ruta.km_esperado;
    var ok   = Math.abs(desv) <= ruta.margen_km;
    desvEl.textContent = (desv>0?'+':'') + fmt.num(desv,1) + ' km vs ruta';
    desvEl.style.color = ok ? 'var(--ok)' : 'var(--danger)';
  } else { desvEl.textContent=''; }
}

async function guardarViaje() {
  var id = document.getElementById('mv-id').value;
  var payload = {
    fecha:          document.getElementById('mv-fecha').value,
    id_unidad:      document.getElementById('mv-unidad').value,
    id_conductor:   document.getElementById('mv-cond-id').value   || null,
    id_ruta:        document.getElementById('mv-ruta-id').value   || null,
    tipo_actividad: document.getElementById('mv-act').value       || null,
    km_salida:      document.getElementById('mv-kmsal').value     || null,
    km_retorno:     document.getElementById('mv-kmret').value     || null,
    hora_salida:    document.getElementById('mv-hsal').value      || null,
    hora_regreso:   document.getElementById('mv-hret').value      || null,
    observacion:    document.getElementById('mv-obs').value       || null,
  };
  if (!payload.fecha || !payload.id_unidad) { toast('Fecha y unidad son obligatorios','error'); return; }
  try {
    if (id) {
      await api('/api/garita/viajes/'+id,{method:'PUT',body:JSON.stringify(payload)});
      toast('Viaje actualizado','ok');
    } else {
      await api('/api/garita/viajes',{method:'POST',body:JSON.stringify(payload)});
      toast('Salida registrada','ok');
    }
    cerrarModal('modal-viaje');
    cargarViajes();
  } catch(e){ toast('Error: '+e.message,'error'); }
}

function confirmarEliminar(id) {
  document.getElementById('btn-del-v').onclick = async function() {
    try {
      await api('/api/garita/viajes/'+id,{method:'DELETE'});
      toast('Viaje eliminado','ok'); cerrarModal('modal-del-v'); cargarViajes();
    } catch(e){ toast('Error: '+e.message,'error'); }
  };
  abrirModal('modal-del-v');
}

async function exportarViajes() {
  if (!viajesData.length) { toast('Sin datos','warn'); return; }
  var ws = XLSX.utils.json_to_sheet(viajesData.map(function(v){ return {
    'Fecha':v.fecha,'Unidad':v.placa,'Conductor':v.conductor_nombre||'—',
    'Actividad':v.tipo_actividad||'—','Ruta':v.destino?v.origen+'→'+v.destino:'—',
    'H.Salida':v.hora_salida||'—','H.Regreso':v.hora_regreso||'—',
    'km Salida':v.km_salida||'','km Retorno':v.km_retorno||'En curso',
    'km Recorrido':v.km_recorrido||'','Observación':v.observacion||'—',
    'Combustible':v.comb_asignado||'Sin asignar'
  }; }));
  var wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,ws,'Viajes');
  XLSX.writeFile(wb,'Viajes_' + document.getElementById('f-fecha').value + '.xlsx');
  toast('Excel generado','ok');
}
</script>