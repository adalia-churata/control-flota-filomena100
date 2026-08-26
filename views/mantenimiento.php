<?php
// views/mantenimiento.php
$hoy = date('Y-m-d');
?>

<!-- ══ MANTENIMIENTO ════════════════════════════════════════ -->
<div class="flex items-center justify-between mb-12">
  <h1 style="font-size:18px;font-weight:700">Mantenimiento de Flota</h1>
  <div style="display:flex;gap:8px">
    <button class="btn btn-outline" onclick="abrirModalHistorial(null,'PREVENTIVO')">
      ✅ Registrar mantenimiento preventivo
    </button>
    <button class="btn btn-primary" onclick="abrirModalHistorial(null,'CORRECTIVO')">
      + Registrar trabajo / gasto
    </button>
  </div>
</div>

<!-- ══ TABS ════════════════════════════════════════════════ -->
<div class="tab-pills" style="margin-bottom:var(--gap)">
  <button class="tab-pill active" onclick="cambiarTabMant('alertas',this)">🔔 Plan preventivo</button>
  <button class="tab-pill" onclick="cambiarTabMant('historial',this)">📋 Historial</button>
  <button class="tab-pill" onclick="cambiarTabMant('documentos',this)">📄 Documentos</button>
  <button class="tab-pill" onclick="cambiarTabMant('llantas',this)">🔄 Durabilidad llantas</button>
</div>

<!-- ══ TAB: ALERTAS / PLAN PREVENTIVO ════════════════════ -->
<div id="tab-alertas">
  <div id="alertas-wrap">
    <div class="empty-state">Cargando plan preventivo…</div>
  </div>
</div>

<!-- ══ TAB: HISTORIAL ════════════════════════════════════ -->
<div id="tab-historial" style="display:none">
  <div class="filter-bar">
    <label>Unidad
      <select id="hist-unidad" onchange="cargarHistorial()">
        <option value="">Todas</option>
      </select>
    </label>
    <label>Categoría
      <select id="hist-cat" onchange="cargarHistorial()">
        <option value="">Todas</option>
        <option value="PREVENTIVO">✅ Preventivo</option>
        <option value="CORRECTIVO">🔧 Correctivo / Falla</option>
        <option value="OTRO">📦 Otro gasto</option>
      </select>
    </label>
    <label>Tipo
      <input type="text" id="hist-tipo-filter" placeholder="Ej: LLANTAS, ACEITE…" 
             oninput="filtrarHistorialLocal()" style="width:160px"/>
    </label>
    <button class="btn btn-outline btn-sm" onclick="exportarHistorial()">Excel</button>
  </div>

  <div class="card full">
    <div class="card-title">
      Historial de mantenimiento
      <span id="hist-count" class="text-muted text-sm"></span>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>Fecha</th><th>Unidad</th><th>Categoría</th><th>Tipo / Trabajo</th>
            <th>km</th><th>Horóm.</th><th>Marca</th>
            <th>Repuestos</th><th>Mano obra</th><th>Total</th><th>Acción</th>
          </tr>
        </thead>
        <tbody id="hist-tbody">
          <tr><td colspan="11" class="empty">Cargando…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══ TAB: DOCUMENTOS ═══════════════════════════════════ -->
<div id="tab-documentos" style="display:none">
  <div style="display:flex;justify-content:flex-end;margin-bottom:12px">
    <button class="btn btn-primary btn-sm" onclick="abrirModalDoc()">+ Nuevo documento</button>
  </div>
  <div id="docs-wrap">
    <div class="empty-state">Cargando documentos…</div>
  </div>
</div>

<!-- ══ TAB: DURABILIDAD LLANTAS ══════════════════════════ -->
<div id="tab-llantas" style="display:none">
  <div class="card full">
    <div class="card-title">🔄 Durabilidad de llantas por unidad</div>
    <div id="llantas-wrap">
      <div class="empty-state">Cargando análisis…</div>
    </div>
  </div>
</div>

<!-- ══ MODAL: Registrar mantenimiento ════════════════════ -->
<div class="overlay" id="modal-historial">
  <div class="modal modal-lg">
    <div class="modal-hdr">
      <span class="modal-title" id="modal-hist-title">Registrar mantenimiento</span>
      <button class="modal-close" onclick="cerrarModal('modal-historial')">×</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="hist-id"/>
      <input type="hidden" id="hist-categoria"/>

      <!-- Banner categoría -->
      <div id="hist-cat-banner" style="border-radius:8px;padding:10px 14px;font-size:13px;font-weight:600;margin-bottom:14px"></div>

      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Fecha *</label>
          <input type="date" id="hist-fecha" value="<?= $hoy ?>"/>
        </div>
        <div class="fgroup">
          <label>Unidad *</label>
          <select id="hist-unidad-modal">
            <option value="">Seleccionar…</option>
          </select>
        </div>
      </div>

      <div class="fgroup">
        <label>Tipo de mantenimiento / Trabajo *</label>
        <input type="text" id="hist-tipo" placeholder="Ej: CAMBIO DE ACEITE, LLANTAS, SOLDADURA…"
               list="hist-tipo-list" autocomplete="off"/>
        <datalist id="hist-tipo-list"></datalist>
        <span class="hint" id="hist-tipo-hint"></span>
      </div>

      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>km en el momento</label>
          <input type="number" id="hist-km" step="1" placeholder="km actual del vehículo"/>
        </div>
        <div class="fgroup">
          <label>Horómetro en el momento</label>
          <input type="number" id="hist-horom" step="0.1" placeholder="horas acumuladas"/>
        </div>
      </div>

      <div class="fgroup">
        <label>Descripción del trabajo *</label>
        <textarea id="hist-desc" rows="2" placeholder="Detalle del trabajo realizado…" style="resize:vertical"></textarea>
      </div>

      <div class="form-grid cols-3">
        <div class="fgroup">
          <label>Marca / Proveedor</label>
          <input type="text" id="hist-marca" placeholder="Marca del repuesto…"/>
        </div>
        <div class="fgroup">
          <label>Costo repuestos (S/)</label>
          <input type="number" id="hist-c-rep" step="0.01" value="0" oninput="calcCostoTotal()"/>
        </div>
        <div class="fgroup">
          <label>Mano de obra (S/)</label>
          <input type="number" id="hist-c-mo" step="0.01" value="0" oninput="calcCostoTotal()"/>
        </div>
      </div>

      <div style="background:var(--brand-lt);border-radius:8px;padding:10px 14px;font-size:14px;font-weight:700;color:var(--brand)">
        Total: S/ <span id="hist-total-display">0.00</span>
      </div>
    </div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-historial')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarHistorial()">Guardar</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Completar mantenimiento preventivo ═════════ -->
<div class="overlay" id="modal-completar">
  <div class="modal modal-sm">
    <div class="modal-hdr">
      <span class="modal-title">✅ Completar mantenimiento preventivo</span>
      <button class="modal-close" onclick="cerrarModal('modal-completar')">×</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="comp-id-plan"/>
      <div id="comp-info" style="background:var(--brand-lt);border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:14px"></div>
      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Fecha *</label>
          <input type="date" id="comp-fecha" value="<?= $hoy ?>"/>
        </div>
        <div class="fgroup">
          <label>km actual</label>
          <input type="number" id="comp-km" step="1"/>
        </div>
      </div>
      <div class="fgroup">
        <label>Horómetro actual</label>
        <input type="number" id="comp-horom" step="0.1"/>
      </div>
      <div class="fgroup">
        <label>Descripción</label>
        <textarea id="comp-desc" rows="2" style="resize:vertical"></textarea>
      </div>
      <div class="fgroup">
        <label>Marca / Proveedor</label>
        <input type="text" id="comp-marca"/>
      </div>
      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Costo repuestos (S/)</label>
          <input type="number" id="comp-c-rep" step="0.01" value="0" oninput="calcCompTotal()"/>
        </div>
        <div class="fgroup">
          <label>Mano de obra (S/)</label>
          <input type="number" id="comp-c-mo" step="0.01" value="0" oninput="calcCompTotal()"/>
        </div>
      </div>
      <div style="font-size:13px;font-weight:700;color:var(--brand)">
        Total: S/ <span id="comp-total">0.00</span>
      </div>
    </div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-completar')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarCompletado()">✅ Marcar como realizado</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Documento ══════════════════════════════════ -->
<div class="overlay" id="modal-doc">
  <div class="modal modal-sm">
    <div class="modal-hdr">
      <span class="modal-title">Documento de unidad</span>
      <button class="modal-close" onclick="cerrarModal('modal-doc')">×</button>
    </div>
    <div class="modal-body">
      <div class="fgroup">
        <label>Unidad *</label>
        <select id="doc-unidad"><option value="">Seleccionar…</option></select>
      </div>
      <div class="fgroup">
        <label>Tipo de documento *</label>
        <select id="doc-tipo">
          <option value="">Seleccionar…</option>
          <option>SOAT</option>
          <option>REVISIÓN TÉCNICA</option>
          <option>TARJETA DE PROPIEDAD</option>
          <option>PERMISO DE OPERACIÓN</option>
          <option>SEGURO VEHICULAR</option>
          <option>LICENCIA DE CONDUCIR</option>
        </select>
      </div>
      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Fecha emisión</label>
          <input type="date" id="doc-emision"/>
        </div>
        <div class="fgroup">
          <label>Fecha vencimiento *</label>
          <input type="date" id="doc-vence"/>
        </div>
      </div>
      <div class="fgroup">
        <label>Alertar con … días de anticipación</label>
        <input type="number" id="doc-alerta" value="30" min="1"/>
      </div>
    </div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-doc')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarDoc()">Guardar</button>
    </div>
  </div>
</div>

<script>
/* ══════════════════════════════════════════════════════════
   MANTENIMIENTO
══════════════════════════════════════════════════════════ */
var unidadesCache = [];
var alertasData   = [];
var historialData = [];
var historialFull = [];

document.addEventListener('DOMContentLoaded', function() {
  cargarUnidadesMant().then(function() {
    cargarAlertas();
  });
});

// ── Tabs ──────────────────────────────────────────────────────
function cambiarTabMant(nombre, el) {
  document.querySelectorAll('[id^="tab-"]').forEach(function(t){ t.style.display='none'; });
  document.querySelectorAll('.tab-pill').forEach(function(b){ b.classList.remove('active'); });
  document.getElementById('tab-' + nombre).style.display = '';
  if(el) el.classList.add('active');
  if(nombre === 'historial')  cargarHistorial();
  if(nombre === 'documentos') cargarDocumentos();
  if(nombre === 'llantas')    cargarLlantas();
}

// ── Catálogos ─────────────────────────────────────────────────
async function cargarUnidadesMant() {
  unidadesCache = await api('/api/unidades');
  var selIds = ['hist-unidad','hist-unidad-modal','doc-unidad'];
  selIds.forEach(function(selId) {
    var sel = document.getElementById(selId); if (!sel) return;
    unidadesCache.forEach(function(u) {
      sel.insertAdjacentHTML('beforeend',
        '<option value="' + u.id_unidad + '">' + u.placa + ' (' + u.tipo_unidad + ')</option>');
    });
  });
  // Datalist tipos de mantenimiento
  var dl = document.getElementById('hist-tipo-list');
  if(dl){
    var tipos = ['CAMBIO DE ACEITE','CAMBIO DE FILTROS','CAMBIO DE LLANTAS','REPARACIÓN DE LLANTAS',
                 'CAMBIO DE FRENOS','MANTENIMIENTO GENERAL','SOLDADURA','MUELLE','BATERÍA','OTRO'];
    tipos.forEach(function(t){ dl.insertAdjacentHTML('beforeend','<option value="'+t+'">'); });
  }
}

// ── Plan preventivo / Alertas ─────────────────────────────────
async function cargarAlertas() {
  var wrap = document.getElementById('alertas-wrap');
  try {
    alertasData = await api('/api/mantenimiento/alertas');
    renderAlertas();
  } catch(e) {
    wrap.innerHTML = '<div style="color:var(--danger)">Error: ' + e.message + '</div>';
  }
}

function renderAlertas() {
  var wrap = document.getElementById('alertas-wrap');
  if(!alertasData.length){
    wrap.innerHTML = '<div class="empty-state">Sin plan de mantenimiento configurado.</div>';
    return;
  }

  var html = '<div style="display:flex;flex-direction:column;gap:12px">';
  alertasData.forEach(function(m) {
    var color = m.estado==='VENCIDO'||m.estado==='CRITICO' ? 'var(--danger)'
              : m.estado==='PROXIMO' ? 'var(--warn)' : 'var(--ok)';
    var bgColor = m.estado==='VENCIDO'||m.estado==='CRITICO' ? 'var(--danger-lt)'
                : m.estado==='PROXIMO' ? 'var(--warn-lt)' : 'var(--bg)';
    var badge = m.estado==='VENCIDO'  ? '<span class="badge badge-danger">VENCIDO</span>'
              : m.estado==='CRITICO'  ? '<span class="badge badge-danger">CRÍTICO</span>'
              : m.estado==='PROXIMO'  ? '<span class="badge badge-warn">PRÓXIMO</span>'
              : '<span class="badge badge-ok">OK</span>';

    var proxInfo = '';
    if(m.km_proximo)   proxInfo += 'Próximo a km ' + fmt.num(m.km_proximo,0);
    if(m.h_proximo)    proxInfo += (proxInfo?' · ':'') + 'Próximo a ' + fmt.num(m.h_proximo,1) + ' h';
    if(m.falta_km)     proxInfo += ' <span style="color:var(--text3)">( faltan ' + fmt.num(m.falta_km,0) + ' km)</span>';
    if(m.falta_h)      proxInfo += ' <span style="color:var(--text3)">( faltan ' + fmt.num(m.falta_h,1) + ' h)</span>';

    // Línea de km: actual → último mantenimiento → próximo
    var kmLine = '';
    if (m.frecuencia_km) {
      kmLine = '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;font-size:12px;margin-top:8px">' +
        '<span style="color:var(--text2)">km último mant.:</span>' +
        '<strong>' + (m.km_ultimo ? fmt.num(m.km_ultimo,0) : '—') + '</strong>' +
        '<span style="color:var(--text3)">→</span>' +
        '<span style="color:var(--text2)">km actual:</span>' +
        '<strong style="color:var(--brand)">' + fmt.num(m.km_actual,0) + '</strong>' +
        '<span style="color:var(--text3)">→</span>' +
        '<span style="color:var(--text2)">próximo:</span>' +
        '<strong style="color:' + color + '">' + (m.km_proximo ? fmt.num(m.km_proximo,0) : '—') + '</strong>' +
        (m.falta_km !== null && m.falta_km > 0
          ? '<span style="background:' + color + ';color:#fff;border-radius:4px;padding:1px 6px;font-size:11px">faltan ' + fmt.num(m.falta_km,0) + ' km</span>'
          : m.falta_km === 0 || (m.km_actual >= m.km_proximo)
            ? '<span style="background:var(--danger);color:#fff;border-radius:4px;padding:1px 6px;font-size:11px">¡Ya superado!</span>'
            : '') +
      '</div>';
    }
    var hLine = '';
    if (m.frecuencia_horas) {
      var diasTag = '';
      if (m.es_ge && m.dias_restantes !== null && m.dias_restantes > 0) {
        diasTag = '<span style="background:var(--brand);color:#fff;border-radius:4px;padding:1px 8px;font-size:11px;font-weight:700">≈ ' + m.dias_restantes + ' días</span>';
      } else if (m.es_ge && (m.falta_h === 0 || m.falta_h < 0)) {
        diasTag = '<span style="background:var(--danger);color:#fff;border-radius:4px;padding:1px 8px;font-size:11px">¡Mantenimiento urgente!</span>';
      }
      hLine = '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;font-size:12px;margin-top:4px">' +
        (m.es_ge ? '<span style="font-size:11px;background:var(--brand-lt);color:var(--brand);border-radius:4px;padding:1px 6px;font-weight:700">⚡ 24/7</span>' : '') +
        '<span style="color:var(--text2)">Horóm. último:</span>' +
        '<strong>' + (m.h_ultimo ? fmt.num(m.h_ultimo,1) : '—') + ' h</strong>' +
        '<span style="color:var(--text3)">→</span>' +
        '<span style="color:var(--text2)">actual:</span>' +
        '<strong style="color:var(--brand)">' + fmt.num(m.h_actual,1) + ' h</strong>' +
        '<span style="color:var(--text3)">→</span>' +
        '<span style="color:var(--text2)">próximo:</span>' +
        '<strong style="color:' + color + '">' + (m.h_proximo ? fmt.num(m.h_proximo,1) : '—') + ' h</strong>' +
        (m.falta_h !== null && m.falta_h > 0
          ? '<span style="background:' + color + ';color:#fff;border-radius:4px;padding:1px 6px;font-size:11px">faltan ' + fmt.num(m.falta_h,1) + ' h</span>'
          : '') +
        diasTag +
      '</div>';
    }
    var ultInfo = m.fecha_ultimo
      ? '<span style="color:var(--text3)">Realizado: ' + fmtFecha(m.fecha_ultimo) + '</span>'
      : '<span style="color:var(--danger);font-size:11px">⚠ Sin registros previos — se calcula desde km 0</span>';

    html +=
      '<div style="background:' + bgColor + ';border:1.5px solid ' + color + ';border-radius:10px;padding:14px 16px">' +
        '<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px">' +
          '<div style="flex:1">' +
            '<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap">' +
              badge +
              (m.es_ge ? '<span style="background:#1a3a5c;color:#fff;border-radius:4px;padding:1px 7px;font-size:11px;font-weight:700">⚡ GE 24/7</span>' : '') +
              '<strong style="font-size:14px">' + m.tarea + '</strong>' +
              '<span class="text-muted text-sm">— ' + m.placa + '</span>' +
            '</div>' +
            '<div style="margin-bottom:8px">' +
              '<div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text3);margin-bottom:2px">' +
                '<span>' + fmt.num(m.pct,1) + '% del intervalo consumido</span>' +
                (m.frecuencia_km ? '<span>cada ' + fmt.num(m.frecuencia_km,0) + ' km</span>' : '') +
                (m.frecuencia_horas ? '<span>cada ' + fmt.num(m.frecuencia_horas,1) + ' h</span>' : '') +
              '</div>' +
              '<div style="height:10px;background:rgba(0,0,0,.1);border-radius:999px;overflow:hidden;width:100%">' +
                '<div style="height:100%;width:' + Math.min(m.pct,100) + '%;background:' + color + ';border-radius:999px"></div>' +
              '</div>' +
            '</div>' +
            kmLine + hLine +
            '<div style="margin-top:6px;font-size:11px">' + ultInfo + '</div>' +
          '</div>' +
          '<button class="btn btn-sm" style="background:' + color + ';color:#fff;white-space:nowrap;flex-shrink:0" ' +
            'onclick="abrirModalCompletar(' + JSON.stringify(m).replace(/"/g,'&quot;') + ')">' +
            (m.estado === 'OK' ? '📋 Registrar' : '✅ Marcar realizado') +
          '</button>' +
        '</div>' +
      '</div>';
  });
  html += '</div>';
  wrap.innerHTML = html;
}

// ── Completar mantenimiento preventivo ───────────────────────
function abrirModalCompletar(plan) {
  document.getElementById('comp-id-plan').value = plan.id_plan;
  document.getElementById('comp-km').value      = plan.km_actual || '';
  document.getElementById('comp-horom').value   = plan.h_actual || '';
  document.getElementById('comp-fecha').value   = '<?= $hoy ?>';
  document.getElementById('comp-desc').value    = plan.tarea;
  document.getElementById('comp-marca').value   = '';
  document.getElementById('comp-c-rep').value   = '0';
  document.getElementById('comp-c-mo').value    = '0';
  document.getElementById('comp-total').textContent = '0.00';
  document.getElementById('comp-info').innerHTML =
    '<strong>' + plan.tarea + '</strong> — ' + plan.placa + '<br>' +
    (plan.frecuencia_km ? 'Cada ' + fmt.num(plan.frecuencia_km,0) + ' km · ' : '') +
    (plan.frecuencia_horas ? 'Cada ' + fmt.num(plan.frecuencia_horas,1) + ' h · ' : '') +
    'Estado: <strong>' + plan.estado + '</strong>';
  abrirModal('modal-completar');
}

function calcCompTotal() {
  var r = parseFloat(document.getElementById('comp-c-rep').value)||0;
  var m = parseFloat(document.getElementById('comp-c-mo').value)||0;
  document.getElementById('comp-total').textContent = fmt.num(r+m,2);
}

async function guardarCompletado() {
  var plan_id = document.getElementById('comp-id-plan').value;
  // Find the plan to get id_unidad and tarea
  var plan = alertasData.find(function(p){ return p.id_plan == plan_id; });
  if(!plan){ toast('Error: plan no encontrado','error'); return; }

  var payload = {
    id_unidad:          plan.id_unidad,
    fecha_ejecucion:    document.getElementById('comp-fecha').value,
    tipo_mantenimiento: plan.tarea,
    tipo_mant_categoria:'PREVENTIVO',
    id_plan:            plan.id_plan,
    km_registro:        document.getElementById('comp-km').value || null,
    horometro_registro: document.getElementById('comp-horom').value || null,
    descripcion_trabajo:document.getElementById('comp-desc').value,
    marca:              document.getElementById('comp-marca').value || null,
    costo_repuestos:    document.getElementById('comp-c-rep').value || 0,
    costo_mano_obra:    document.getElementById('comp-c-mo').value || 0,
  };

  if(!payload.fecha_ejecucion){ toast('Ingresa la fecha','error'); return; }

  try {
    await api('/api/mantenimiento/historial', { method:'POST', body:JSON.stringify(payload) });
    toast('Mantenimiento registrado · El próximo cronograma se reinicia desde este km/horómetro','ok');
    cerrarModal('modal-completar');
    cargarAlertas();
  } catch(e) { toast('Error: '+e.message,'error'); }
}

// ── Historial ─────────────────────────────────────────────────
async function cargarHistorial() {
  var uid = document.getElementById('hist-unidad').value;
  var cat = document.getElementById('hist-cat').value;
  var url = '/api/mantenimiento/historial';
  var params = [];
  if(uid) params.push('id_unidad='+uid);
  if(cat) params.push('categoria='+encodeURIComponent(cat));
  if(params.length) url += '?' + params.join('&');

  historialFull = await api(url);
  historialData = historialFull;
  filtrarHistorialLocal();
  tx('hist-count', historialFull.length + ' registros');
}

function filtrarHistorialLocal() {
  var filtro = (document.getElementById('hist-tipo-filter').value||'').toLowerCase();
  historialData = filtro
    ? historialFull.filter(function(h){ return (h.tipo_mantenimiento||'').toLowerCase().includes(filtro); })
    : historialFull;
  renderHistorial();
}

function renderHistorial() {
  var tbody = document.getElementById('hist-tbody');
  if(!historialData.length){
    tbody.innerHTML = '<tr><td colspan="11" class="empty">Sin registros.</td></tr>'; return;
  }
  tbody.innerHTML = historialData.map(function(h) {
    var catCls = h.tipo_mant_categoria==='PREVENTIVO' ? 'badge-ok'
               : h.tipo_mant_categoria==='CORRECTIVO' ? 'badge-warn' : 'badge-info';
    var catLabel = h.tipo_mant_categoria==='PREVENTIVO' ? '✅ Preventivo'
                 : h.tipo_mant_categoria==='CORRECTIVO' ? '🔧 Correctivo' : '📦 Otro';
    return '<tr>' +
      '<td>' + fmtFecha(h.fecha_ejecucion) + '</td>' +
      '<td><strong>' + h.placa + '</strong></td>' +
      '<td><span class="badge ' + catCls + '">' + catLabel + '</span></td>' +
      '<td>' + (h.tipo_mantenimiento||'—') + '<div class="text-xs text-muted" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + (h.descripcion_trabajo||'') + '</div></td>' +
      '<td class="mono">' + (h.km_registro!=null?fmt.num(h.km_registro,0)+' km':'—') + '</td>' +
      '<td class="mono">' + (h.horometro_registro!=null?fmt.num(h.horometro_registro,1)+' h':'—') + '</td>' +
      '<td>' + (h.marca||'—') + '</td>' +
      '<td>' + fmt.sol(h.costo_repuestos||0) + '</td>' +
      '<td>' + fmt.sol(h.costo_mano_obra||0) + '</td>' +
      '<td><strong>' + fmt.sol(h.costo_total_soles||0) + '</strong></td>' +
      '<td>' +
        '<button class="btn btn-outline btn-xs" onclick="abrirModalHistorial(' + h.id_mantenimiento + ')">✏</button> ' +
        '<button class="btn btn-xs" style="background:var(--danger-lt);color:var(--danger);border:1px solid var(--danger)" onclick="eliminarHistorial(' + h.id_mantenimiento + ')">🗑</button>' +
      '</td>' +
    '</tr>';
  }).join('');
}

// ── Modal historial ───────────────────────────────────────────
function calcCostoTotal() {
  var r = parseFloat(document.getElementById('hist-c-rep').value)||0;
  var m = parseFloat(document.getElementById('hist-c-mo').value)||0;
  document.getElementById('hist-total-display').textContent = fmt.num(r+m,2);
}

async function abrirModalHistorial(id, categoria) {
  document.getElementById('hist-id').value      = id || '';
  document.getElementById('hist-fecha').value   = '<?= $hoy ?>';
  document.getElementById('hist-km').value      = '';
  document.getElementById('hist-horom').value   = '';
  document.getElementById('hist-desc').value    = '';
  document.getElementById('hist-marca').value   = '';
  document.getElementById('hist-c-rep').value   = '0';
  document.getElementById('hist-c-mo').value    = '0';
  document.getElementById('hist-total-display').textContent = '0.00';
  document.getElementById('hist-unidad-modal').value = '';
  document.getElementById('hist-tipo').value    = '';

  var cat = categoria || 'CORRECTIVO';
  document.getElementById('hist-categoria').value = cat;

  var banner = document.getElementById('hist-cat-banner');
  if(cat === 'PREVENTIVO') {
    document.getElementById('modal-hist-title').textContent = '✅ Registrar mantenimiento preventivo';
    banner.style.cssText = 'background:var(--ok-lt);border:1px solid var(--ok);border-radius:8px;padding:10px 14px;font-size:13px;font-weight:600;margin-bottom:14px;color:var(--ok)';
    banner.textContent = '✅ PREVENTIVO — Mantenimiento programado según cronograma';
    document.getElementById('hist-tipo-hint').textContent = 'Selecciona la tarea del plan de mantenimiento';
  } else {
    document.getElementById('modal-hist-title').textContent = '🔧 Registrar trabajo / gasto';
    banner.style.cssText = 'background:var(--warn-lt);border:1px solid var(--warn);border-radius:8px;padding:10px 14px;font-size:13px;font-weight:600;margin-bottom:14px;color:var(--warn)';
    banner.textContent = '🔧 CORRECTIVO — Falla, reparación o gasto no programado';
    document.getElementById('hist-tipo-hint').textContent = 'Ej: REPARACIÓN DE LLANTAS, SOLDADURA, PARCHE, etc.';
  }

  if(id) {
    var h = historialData.find(function(x){ return x.id_mantenimiento==id; });
    if(h){
      document.getElementById('modal-hist-title').textContent = 'Editar registro';
      document.getElementById('hist-unidad-modal').value = h.id_unidad;
      document.getElementById('hist-fecha').value  = h.fecha_ejecucion;
      document.getElementById('hist-tipo').value   = h.tipo_mantenimiento;
      document.getElementById('hist-km').value     = h.km_registro || '';
      document.getElementById('hist-horom').value  = h.horometro_registro || '';
      document.getElementById('hist-desc').value   = h.descripcion_trabajo || '';
      document.getElementById('hist-marca').value  = h.marca || '';
      document.getElementById('hist-c-rep').value  = h.costo_repuestos || 0;
      document.getElementById('hist-c-mo').value   = h.costo_mano_obra || 0;
      document.getElementById('hist-categoria').value = h.tipo_mant_categoria || 'CORRECTIVO';
      calcCostoTotal();
    }
  }

  abrirModal('modal-historial');
}

async function guardarHistorial() {
  var id = document.getElementById('hist-id').value;
  var payload = {
    id_unidad:           document.getElementById('hist-unidad-modal').value,
    fecha_ejecucion:     document.getElementById('hist-fecha').value,
    tipo_mantenimiento:  document.getElementById('hist-tipo').value,
    tipo_mant_categoria: document.getElementById('hist-categoria').value,
    km_registro:         document.getElementById('hist-km').value || null,
    horometro_registro:  document.getElementById('hist-horom').value || null,
    descripcion_trabajo: document.getElementById('hist-desc').value,
    marca:               document.getElementById('hist-marca').value || null,
    costo_repuestos:     document.getElementById('hist-c-rep').value || 0,
    costo_mano_obra:     document.getElementById('hist-c-mo').value || 0,
  };
  if(!payload.id_unidad||!payload.fecha_ejecucion||!payload.tipo_mantenimiento||!payload.descripcion_trabajo){
    toast('Completa los campos obligatorios','error'); return;
  }
  try {
    if(id) {
      await api('/api/mantenimiento/historial/'+id,{method:'PUT',body:JSON.stringify(payload)});
      toast('Registro actualizado','ok');
    } else {
      await api('/api/mantenimiento/historial',{method:'POST',body:JSON.stringify(payload)});
      toast('Registro guardado','ok');
    }
    cerrarModal('modal-historial');
    cargarHistorial();
    cargarAlertas();
  } catch(e){ toast('Error: '+e.message,'error'); }
}

async function eliminarHistorial(id) {
  if(!confirm('¿Eliminar este registro de mantenimiento?')) return;
  try {
    await api('/api/mantenimiento/historial/'+id,{method:'DELETE'});
    toast('Eliminado','ok');
    cargarHistorial();
    cargarAlertas();
  } catch(e){ toast('Error: '+e.message,'error'); }
}

async function exportarHistorial() {
  if(!historialData.length){ toast('Sin datos','warn'); return; }
  var ws = XLSX.utils.json_to_sheet(historialData.map(function(h){ return {
    'Fecha':h.fecha_ejecucion,'Unidad':h.placa,'Categoría':h.tipo_mant_categoria||'—',
    'Tipo':h.tipo_mantenimiento,'Descripción':h.descripcion_trabajo||'',
    'km':h.km_registro||'','Horómetro':h.horometro_registro||'',
    'Marca':h.marca||'','Repuestos':h.costo_repuestos||0,
    'Mano obra':h.costo_mano_obra||0,'Total':h.costo_total_soles||0,
  }; }));
  var wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,ws,'Historial');
  XLSX.writeFile(wb,'Historial_Mantenimiento.xlsx');
  toast('Excel generado','ok');
}

// ── Documentos ────────────────────────────────────────────────
async function cargarDocumentos() {
  var wrap = document.getElementById('docs-wrap');
  var data = await api('/api/mantenimiento/documentos');
  if(!data.length){
    wrap.innerHTML = '<div class="empty-state">Sin documentos registrados.</div>'; return;
  }
  var html = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px">';
  data.forEach(function(d) {
    var dias   = parseInt(d.dias_restantes);
    var estado = dias<0?'VENCIDO':dias<=7?'URGENTE':dias<=parseInt(d.alerta_dias_antes||30)?'ALERTA':'VIGENTE';
    var cls    = estado==='VENCIDO'||estado==='URGENTE'?'badge-danger':estado==='ALERTA'?'badge-warn':'badge-ok';
    var bord   = estado==='VENCIDO'||estado==='URGENTE'?'var(--danger)':estado==='ALERTA'?'var(--warn)':'var(--ok)';
    var bg     = estado==='VIGENTE'?'var(--ok-lt)':estado==='ALERTA'?'var(--warn-lt)':'var(--danger-lt)';
    html +=
      '<div style="background:'+bg+';border:1.5px solid '+bord+';border-radius:10px;padding:14px">' +
        '<div style="display:flex;justify-content:space-between;align-items:flex-start">' +
          '<div>' +
            '<div style="font-size:12px;color:var(--text2);margin-bottom:2px">' + d.placa + '</div>' +
            '<div style="font-size:14px;font-weight:700">' + d.tipo_documento + '</div>' +
          '</div>' +
          '<span class="badge '+cls+'">' + estado + '</span>' +
        '</div>' +
        '<div style="font-size:12px;color:var(--text2);margin-top:8px">' +
          'Vence: <strong>' + fmtFecha(d.fecha_vencimiento) + '</strong>' +
          (dias>=0?' · '+dias+' días':' · <span style="color:var(--danger);font-weight:700">VENCIDO hace '+Math.abs(dias)+' días</span>') +
        '</div>' +
        '<div style="display:flex;gap:6px;margin-top:10px">' +
          '<button class="btn btn-outline btn-xs" onclick="abrirModalDoc('+d.id_documento+')">Renovar</button>' +
          '<button class="btn btn-xs" style="background:var(--danger-lt);color:var(--danger);border:1px solid var(--danger)" onclick="eliminarDoc('+d.id_documento+')">Eliminar</button>' +
        '</div>' +
      '</div>';
  });
  html += '</div>';
  wrap.innerHTML = html;
}

function abrirModalDoc(id) {
  document.getElementById('doc-unidad').value  = '';
  document.getElementById('doc-tipo').value    = '';
  document.getElementById('doc-emision').value = '';
  document.getElementById('doc-vence').value   = '';
  document.getElementById('doc-alerta').value  = '30';
  abrirModal('modal-doc');
}

async function guardarDoc() {
  var payload = {
    id_unidad:        document.getElementById('doc-unidad').value,
    tipo_documento:   document.getElementById('doc-tipo').value,
    fecha_emision:    document.getElementById('doc-emision').value || null,
    fecha_vencimiento:document.getElementById('doc-vence').value,
    alerta_dias_antes:document.getElementById('doc-alerta').value || 30,
  };
  if(!payload.id_unidad||!payload.tipo_documento||!payload.fecha_vencimiento){
    toast('Completa los campos obligatorios','error'); return;
  }
  try {
    await api('/api/mantenimiento/documentos',{method:'POST',body:JSON.stringify(payload)});
    toast('Documento guardado','ok');
    cerrarModal('modal-doc');
    cargarDocumentos();
  } catch(e){ toast('Error: '+e.message,'error'); }
}

async function eliminarDoc(id) {
  if(!confirm('¿Eliminar este documento? Si tiene uno más reciente, quedará visible el anterior.')) return;
  try {
    await api('/api/mantenimiento/documentos/'+id,{method:'DELETE'});
    toast('Eliminado','ok');
    cargarDocumentos();
  } catch(e){ toast('Error: '+e.message,'error'); }
}

// ── Durabilidad llantas ───────────────────────────────────────
async function cargarLlantas() {
  var wrap = document.getElementById('llantas-wrap');
  wrap.innerHTML = '<div class="empty-state">Cargando…</div>';
  try {
    var data = await api('/api/mantenimiento/analisis-repuestos?tipo=CAMBIO%20DE%20LLANTAS');
    var resumen = data.resumen || [];
    var detalle = data.detalle || [];

    if(!resumen.length){
      wrap.innerHTML = '<div class="empty-state">Sin registros de cambio de llantas en el historial.</div>';
      return;
    }

    var html = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;margin-bottom:16px">';
    resumen.forEach(function(r) {
      html +=
        '<div class="kpi-card" style="border-left:3px solid var(--brand)">' +
          '<div class="kpi-lbl">' + r.placa + ' — Duración promedio</div>' +
          '<div class="kpi-val brand">' + (r.km_prom?fmt.num(r.km_prom,0)+' km':'—') + '</div>' +
          '<div class="kpi-sub">' +
            r.n_cambios + ' cambios · ' +
            (r.km_min&&r.km_max?'rango '+fmt.num(r.km_min,0)+'–'+fmt.num(r.km_max,0)+' km':'') +
          '</div>' +
        '</div>';
    });
    html += '</div>';

    if(detalle.length){
      html += '<div class="tbl-wrap"><table class="tbl"><thead><tr>' +
        '<th>Unidad</th><th>Fecha</th><th>km</th><th>km desde anterior</th><th>Días desde anterior</th><th>Marca</th><th>Costo</th>' +
        '</tr></thead><tbody>';
      detalle.forEach(function(d) {
        html += '<tr>' +
          '<td><strong>' + d.placa + '</strong></td>' +
          '<td>' + fmtFecha(d.fecha_ejecucion) + '</td>' +
          '<td class="mono">' + (d.km_registro?fmt.num(d.km_registro,0)+' km':'—') + '</td>' +
          '<td class="mono" style="color:var(--brand)">' + (d.km_duracion?fmt.num(d.km_duracion,0)+' km':'—') + '</td>' +
          '<td>' + (d.dias_duracion?d.dias_duracion+' días':'—') + '</td>' +
          '<td>' + (d.marca||'—') + '</td>' +
          '<td>' + fmt.sol(d.costo_total_soles||0) + '</td>' +
        '</tr>';
      });
      html += '</tbody></table></div>';
    }

    wrap.innerHTML = html;
  } catch(e) {
    wrap.innerHTML = '<div style="color:var(--danger)">Error: ' + e.message + '</div>';
  }
}
</script>