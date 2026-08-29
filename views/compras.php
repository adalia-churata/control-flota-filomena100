<?php // views/compras.php ?>

<!-- ══ COMPRAS DE COMBUSTIBLE ══════════════════════════════ -->
<div class="flex items-center justify-between mb-12">
  <h1 style="font-size:18px;font-weight:700">Combustible — Compras</h1>
  <div style="display:flex;gap:8px">
    <button class="btn btn-outline btn-sm" onclick="abrirModalDeposito()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
      </svg>
      Depósito a grifo
    </button>
    <button class="btn btn-primary" onclick="abrirModalCompra(null)">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
      </svg>
      Nueva compra
    </button>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     KPIs DEL PERÍODO
════════════════════════════════════════════════════════════ -->
<div class="kpi-grid" style="margin-bottom:var(--gap)" id="kpi-compras-wrap">
  <div class="kpi-card">
    <div class="kpi-lbl">Total galones</div>
    <div class="kpi-val brand" id="ck-galones">—</div>
    <div class="kpi-sub">comprados</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-lbl">Gasto total</div>
    <div class="kpi-val" id="ck-soles">—</div>
    <div class="kpi-sub" id="ck-pu">Precio prom: —</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-lbl">N° compras</div>
    <div class="kpi-val brand" id="ck-n">—</div>
    <div class="kpi-sub">transacciones</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-lbl">Saldo en grifo</div>
    <div class="kpi-val" id="ck-saldo">—</div>
    <div class="kpi-sub">crédito disponible</div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     FILTROS
════════════════════════════════════════════════════════════ -->
<div class="filter-bar">
  <label>Desde
    <input type="date" id="f-desde" value="<?= date('Y-m-01') ?>" onchange="cargarCompras()"/>
  </label>
  <label>Hasta
    <input type="date" id="f-hasta" value="<?= date('Y-m-d') ?>" onchange="cargarCompras()"/>
  </label>
  <label>Unidad
    <select id="f-unidad" onchange="cargarCompras()">
      <option value="">Todas</option>
    </select>
  </label>
  <label>Tipo combustible
    <select id="f-tipo-comb" onchange="cargarCompras()">
      <option value="">Todos</option>
      <option value="PETROLEO">Petróleo</option>
      <option value="GASOLINA">Gasolina</option>
    </select>
  </label>
  <label>Forma de pago
    <select id="f-pago" onchange="cargarCompras()">
      <option value="">Todas</option>
      <option value="CREDITO">Crédito</option>
      <option value="CONTADO">Contado</option>
    </select>
  </label>
  <button class="btn btn-outline btn-sm" onclick="exportarCompras()">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
      <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
    </svg>
    Excel
  </button>
</div>

<!-- ═══════════════════════════════════════════════════════════
     TABLA DE COMPRAS
════════════════════════════════════════════════════════════ -->
<div class="card full">
  <div class="card-title">
    Historial de compras
    <span id="compras-count" class="text-muted text-sm">— registros</span>
  </div>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Comprobante</th>
          <th>Grifo</th>
          <th>Unidad</th>
          <th>Combustible</th>
          <th>Galones</th>
          <th>P. Unit.</th>
          <th>Subtotal</th>
          <th>IGV</th>
          <th>Total</th>
          <th>km / Horóm.</th>
          <th>Pago</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="compras-tbody">
        <tr><td colspan="13" class="empty">Cargando…</td></tr>
      </tbody>
    </table>
  </div>
  <div class="pager" id="pager-compras"></div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     PANEL MOVIMIENTOS DE CRÉDITO
════════════════════════════════════════════════════════════ -->
<div class="row" style="margin-top:var(--gap)">
  <div class="card w1">
    <div class="card-title">
      Movimientos en grifo
      <div style="font-size:18px;font-weight:700;color:var(--brand)" id="saldo-grifo-label">S/ —</div>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr><th>Fecha</th><th>Tipo</th><th>Descripción</th><th>Monto</th><th>Saldo</th></tr>
        </thead>
        <tbody id="movimientos-tbody">
          <tr><td colspan="5" class="empty">Cargando…</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card w1">
    <div class="card-title">Consumo por tipo de unidad (período)</div>
    <div class="chart-box" style="height:220px">
      <canvas id="chart-tipo-consumo"></canvas>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MODAL: Nueva / Editar compra
════════════════════════════════════════════════════════════ -->
<div class="overlay" id="modal-compra">
  <div class="modal modal-lg">
    <div class="modal-hdr">
      <span class="modal-title" id="modal-compra-title">Nueva Compra de Combustible</span>
      <button class="modal-close" onclick="cerrarModal('modal-compra')">×</button>
    </div>
    <div class="modal-body">

      <input type="hidden" id="c-id"/>

      <!-- Fila 1: Fecha + Grifo -->
      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Fecha *</label>
          <input type="date" id="c-fecha" value="<?= date('Y-m-d') ?>"/>
        </div>
        <div class="fgroup">
          <label>Grifo</label>
          <select id="c-grifo">
            <option value="">Seleccionar grifo…</option>
          </select>
        </div>
      </div>

      <!-- Fila 2: Tipo comprobante + Nro -->
      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Tipo comprobante</label>
          <select id="c-tipo-comp">
            <option value="">Sin comprobante</option>
            <option value="FACTURA">Factura</option>
            <option value="BOLETA">Boleta</option>
            <option value="TICKET">Ticket</option>
            <option value="NOTA DE VENTA">Nota de venta</option>
          </select>
        </div>
        <div class="fgroup">
          <label>N° Comprobante</label>
          <input type="text" id="c-nro-comp" placeholder="F001-00123"/>
        </div>
      </div>

      <!-- Fila 3: Unidad + Tipo combustible -->
      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Unidad</label>
          <select id="c-unidad" onchange="toggleKmField()">
            <option value="">Sin unidad (bidón)</option>
          </select>
        </div>
        <div class="fgroup">
          <label>Tipo de combustible *</label>
          <select id="c-tipo-comb" onchange="toggleKmField()">
            <option value="">Seleccionar…</option>
            <option value="PETROLEO">Petróleo / Diesel</option>
            <option value="GASOLINA">Gasolina</option>
          </select>
        </div>
      </div>

      <!-- km / horómetro (solo si hay unidad y es petróleo) -->
      <div id="km-field-wrap" style="display:none">
        <div class="fgroup">
          <label id="km-field-label">km del vehículo al momento del tanqueo</label>
          <input type="number" id="c-km" step="0.1" placeholder="Ej: 52430"/>
          <span class="hint" id="km-hint">Se usará para asignar automáticamente el combustible a los viajes anteriores</span>
        </div>
      </div>

      <!-- Nota gasolina -->
      <div id="gasolina-nota" style="display:none;background:#fff8e1;border:1px solid #f0c040;border-radius:8px;padding:10px 14px;font-size:13px;color:#7a5f00">
        ⚠ La gasolina <strong>no se asignará</strong> automáticamente a viajes. Es para uso de mantenimiento o limpieza de piezas.
      </div>

      <!-- Fila 4: Cantidad + Precio unitario -->
      <div class="form-grid cols-2">
        <div class="fgroup">
          <label>Cantidad (galones) *</label>
          <input type="number" id="c-galones" step="0.01" placeholder="0.00" oninput="calcularMontos()"/>
        </div>
        <div class="fgroup">
          <label>Precio unitario (S/) *</label>
          <input type="number" id="c-precio" step="0.01" placeholder="0.00" oninput="calcularMontos()"/>
        </div>
      </div>

      <!-- Montos calculados automáticamente -->
      <div style="background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:14px 16px">
        <div style="font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">
          Montos calculados automáticamente
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
          <div class="fgroup">
            <label>Subtotal (S/)</label>
            <input type="number" id="c-subtotal" step="0.01" readonly/>
          </div>
          <div class="fgroup">
            <label>IGV 18% (S/)</label>
            <input type="number" id="c-igv" step="0.01" readonly/>
          </div>
          <div class="fgroup">
            <label>Total (S/) *</label>
            <input type="number" id="c-total" step="0.01"
                   placeholder="Editable si la factura redondea"
                   title="Puedes editar si la factura tiene un total diferente al calculado"/>
          </div>
        </div>
        <div class="hint" style="margin-top:6px">💡 <strong>galones × precio = total (con IGV incluido)</strong>. Subtotal e IGV se calculan del total. Puedes editar el total directamente si la factura tiene un monto diferente.</div>
      </div>

      <!-- Tanqueo completo -->
      <div class="fgroup">
        <label>¿Fue tanqueo completo?</label>
        <select id="c-tanqueo">
          <option value="1">Sí — tanqueo completo (se llenó el tanque)</option>
          <option value="0">No — solo se echó parcialmente</option>
        </select>
        <span class="hint">El tanqueo completo permite calcular km/galón con mayor precisión</span>
      </div>

      <!-- Forma de pago -->
      <div class="fgroup">
        <label>Forma de pago *</label>
        <select id="c-pago">
          <option value="CONTADO">Contado</option>
          <option value="CREDITO">Crédito (descuenta del saldo en grifo)</option>
        </select>
      </div>

      <!-- Saldo actual del grifo (informativo) -->
      <div id="saldo-info-wrap" style="background:var(--brand-lt);border-radius:8px;padding:10px 14px;font-size:13px;display:none">
        Saldo actual en grifo: <strong id="saldo-info-val">—</strong>
        · Si esta compra es a crédito, el nuevo saldo será: <strong id="saldo-info-nuevo">—</strong>
      </div>

    </div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-compra')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarCompra()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        Guardar
      </button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Depósito al grifo ════════════════════════════ -->
<div class="overlay" id="modal-deposito">
  <div class="modal modal-sm">
    <div class="modal-hdr">
      <span class="modal-title">Registrar Depósito al Grifo</span>
      <button class="modal-close" onclick="cerrarModal('modal-deposito')">×</button>
    </div>
    <div class="modal-body">
      <div class="fgroup">
        <label>Fecha</label>
        <input type="date" id="dep-fecha" value="<?= date('Y-m-d') ?>"/>
      </div>
      <div class="fgroup">
        <label>Monto depositado (S/) *</label>
        <input type="number" id="dep-monto" step="0.01" placeholder="0.00" oninput="calcularNuevoSaldo()"/>
      </div>
      <div class="fgroup">
        <label>Descripción</label>
        <input type="text" id="dep-desc" placeholder="Depósito a cuenta grifo…"/>
      </div>
      <div style="background:var(--ok-lt);border-radius:8px;padding:10px 14px;font-size:13px">
        Saldo actual: <strong id="dep-saldo-actual">—</strong><br/>
        Nuevo saldo: <strong id="dep-saldo-nuevo" style="color:var(--ok)">—</strong>
      </div>
    </div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-deposito')">Cancelar</button>
      <button class="btn btn-success" onclick="guardarDeposito()">Registrar depósito</button>
    </div>
  </div>
</div>

<!-- ══ MODAL: Confirmar eliminar ═══════════════════════════ -->
<div class="overlay" id="modal-confirmar-c">
  <div class="modal modal-sm">
    <div class="modal-hdr">
      <span class="modal-title">Confirmar eliminación</span>
      <button class="modal-close" onclick="cerrarModal('modal-confirmar-c')">×</button>
    </div>
    <div class="modal-body">
      <p>¿Eliminar esta compra de combustible? Si tiene movimiento de crédito asociado, deberás eliminarlo manualmente.</p>
    </div>
    <div class="modal-ftr">
      <button class="btn btn-outline" onclick="cerrarModal('modal-confirmar-c')">Cancelar</button>
      <button class="btn btn-danger" id="btn-del-compra">Eliminar</button>
    </div>
  </div>
</div>


<!-- ══ Diagnóstico de asignaciones ═══════════════════════════ -->
<div class="card full" style="margin-top:var(--gap)" id="section-diagnostico">
  <div class="card-title">
    🔍 Diagnóstico de asignaciones
    <span class="text-muted text-sm">Verifica y corrige relaciones viaje ↔ combustible</span>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:14px">
    <label>Desde <input type="date" id="diag-desde" value="<?= date('Y-m-01') ?>"/></label>
    <label>Hasta <input type="date" id="diag-hasta" value="<?= date('Y-m-d') ?>"/></label>
    <label>Unidad
      <select id="diag-unidad">
        <option value="">Todas FLOTA</option>
      </select>
    </label>
    <button class="btn btn-primary btn-sm" onclick="cargarDiagnostico()">🔍 Analizar</button>
    <button class="btn btn-sm" style="background:var(--brand);color:#fff" onclick="recalcularAsignaciones()"
            title="Corrige automáticamente las asignaciones incorrectas usando la lógica de km">
      ⚡ Corregir erróneos
    </button>
    <button class="btn btn-outline btn-sm" onclick="limpiarNulos()" title="Elimina filas vacías (id_combustible NULL)">🗑 Quitar vacíos</button>
  </div>
  <div class="kpi-grid" style="margin-bottom:var(--gap)" id="diag-kpis"></div>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>Viaje</th><th>Fecha</th><th>Unidad</th>
          <th>km salida</th><th>km retorno</th>
          <th>Compra / Comprobante</th><th>km tanqueo</th><th>Gll</th>
          <th>Relación</th><th>Detalle</th><th>Acción</th>
        </tr>
      </thead>
      <tbody id="diag-tbody">
        <tr><td colspan="11" class="empty">Haz clic en "Analizar" para ver las asignaciones.</td></tr>
      </tbody>
    </table>
  </div>
</div>

<script>
/* ══════════════════════════════════════════════════════════
   COMPRAS — JavaScript
══════════════════════════════════════════════════════════ */

let comprasData  = [];
let comprasPage  = 0;
const COMPRAS_PP = 15;
let saldoActual  = 0;

// ── Boot ────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  Promise.all([
    cargarUnidadesSelect(),
    cargarGrifosSelect(),
    cargarSaldoGrifo(),
  ]).then(() => {
    cargarCompras();
    cargarMovimientos();
  });
});

// ── Catálogos ───────────────────────────────────────────────
async function cargarUnidadesSelect() {
  const data = await api('/api/unidades');
  const sels = ['f-unidad', 'c-unidad'];
  sels.forEach(selId => {
    const sel = document.getElementById(selId);
    if (!sel) return;
    data.forEach(u => {
      sel.insertAdjacentHTML('beforeend',
        `<option value="${u.id_unidad}">${u.placa} — ${u.tipo_unidad}</option>`);
    });
  });
}

async function cargarGrifosSelect() {
  try {
    const data = await api('/api/grifos');
    const sel  = document.getElementById('c-grifo');
    data.forEach(g => {
      sel.insertAdjacentHTML('beforeend', `<option value="${g.id_grifo}">${g.razon_social||g.nombre||"Grifo "+g.id_grifo}</option>`);
    });
  } catch(_) {}
}

async function cargarSaldoGrifo() {
  const d = await api('/api/compras/saldo-grifo');
  saldoActual = parseFloat(d.saldo || 0);
  const cls   = saldoActual < 0 ? 'danger' : 'ok';
  document.getElementById('ck-saldo').textContent      = fmt.sol(saldoActual);
  document.getElementById('ck-saldo').className        = 'kpi-val ' + cls;
  document.getElementById('saldo-grifo-label').textContent = fmt.sol(saldoActual);
  document.getElementById('dep-saldo-actual').textContent  = fmt.sol(saldoActual);
}

// ── Cargar compras con filtros ──────────────────────────────
async function cargarCompras() {
  const fd   = document.getElementById('f-desde').value;
  const fh   = document.getElementById('f-hasta').value;
  const uid  = document.getElementById('f-unidad').value;
  const tc   = document.getElementById('f-tipo-comb').value;
  const pago = document.getElementById('f-pago').value;

  let url = `/api/compras?fecha_desde=${fd}&fecha_hasta=${fh}`;
  if (uid)  url += `&id_unidad=${uid}`;
  if (tc)   url += `&tipo_combustible=${tc}`;
  if (pago) url += `&forma_pago=${pago}`;

  comprasData = await api(url);
  comprasPage = 0;
  renderCompras();
  cargarKPIsCompras(fd, fh, uid, tc);
  cargarGraficoTipo(fd, fh);
}

async function cargarKPIsCompras(fd, fh, uid, tc) {
  let url = `/api/compras/kpis?fecha_desde=${fd}&fecha_hasta=${fh}`;
  if (uid) url += `&id_unidad=${uid}`;
  if (tc)  url += `&tipo_combustible=${tc}`;
  const k = await api(url);
  tx('ck-galones', fmt.num(k.total_galones, 1) + ' gll');
  tx('ck-soles',   fmt.sol(k.total_soles));
  tx('ck-n',       k.n_compras);
  document.getElementById('ck-pu').textContent = 'Precio prom: ' + fmt.sol(k.precio_promedio) + '/gll';
}

function renderCompras() {
  const tbody = document.getElementById('compras-tbody');
  const total = Math.max(1, Math.ceil(comprasData.length / COMPRAS_PP));
  const slice = comprasData.slice(comprasPage * COMPRAS_PP, (comprasPage + 1) * COMPRAS_PP);

  document.getElementById('compras-count').textContent = comprasData.length + ' registros';

  if (!slice.length) {
    tbody.innerHTML = '<tr><td colspan="13" class="empty">Sin compras para estos filtros.</td></tr>';
    renderPager('pager-compras', comprasPage, total, p => { comprasPage = p; renderCompras(); });
    return;
  }

  tbody.innerHTML = slice.map(c => {
    const pagoClass = c.forma_pago === 'CREDITO' ? 'badge-brand' : 'badge-ok';
    const tcLabel   = c.tipo_combustible === 'PETROLEO' ? '🛢 Petróleo' : '⛽ Gasolina';
    return `<tr>
      <td>${fmtFecha(c.fecha)}</td>
      <td class="mono">${[c.tipo_comprobante, c.nro_comprobante].filter(Boolean).join(' ') || '—'}</td>
      <td class="text-sm">${c.grifo_nombre || '—'}</td>
      <td><strong>${c.placa || '<em style="color:var(--text3)">Bidón</em>'}</strong></td>
      <td><span class="badge ${c.tipo_combustible === 'PETROLEO' ? 'badge-info' : 'badge-warn'}">${tcLabel}</span></td>
      <td>${fmt.num(c.cantidad_gll, 2)} gll</td>
      <td>${fmt.sol(c.precio_unitario)}</td>
      <td>${fmt.sol(c.subtotal)}</td>
      <td>${fmt.sol(c.igv)}</td>
      <td><strong>${fmt.sol(c.total)}</strong></td>
      <td class="mono">${c.km_vehiculo != null ? fmt.num(c.km_vehiculo, 1) : '—'}</td>
      <td><span class="badge ${pagoClass}">${c.forma_pago}</span></td>
      <td>
        <div class="action-btns">
          <button class="btn btn-outline btn-xs" onclick="abrirModalCompra(${c.id_combustible})">✏</button>
          <button class="btn btn-xs" style="background:var(--danger-lt);color:var(--danger);border:1px solid var(--danger)"
                  onclick="confirmarEliminarCompra(${c.id_combustible})">🗑</button>
        </div>
      </td>
    </tr>`;
  }).join('');

  renderPager('pager-compras', comprasPage, total, p => { comprasPage = p; renderCompras(); });
}

// ── Movimientos de crédito ──────────────────────────────────
async function cargarMovimientos() {
  const data  = await api('/api/compras/movimientos');
  const tbody = document.getElementById('movimientos-tbody');

  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="empty">Sin movimientos.</td></tr>';
    return;
  }

  tbody.innerHTML = data.slice(0, 20).map(m => {
    const esDep  = m.tipo === 'DEPOSITO';
    const saldoC = parseFloat(m.saldo) >= 0 ? 'ok' : 'danger';
    return `<tr>
      <td class="text-sm">${fmtFecha(m.fecha)}</td>
      <td><span class="badge ${esDep ? 'badge-ok' : 'badge-brand'}">${esDep ? '↑ Depósito' : '↓ Compra'}</span></td>
      <td class="text-sm">${m.descripcion || '—'}</td>
      <td style="color:${esDep ? 'var(--ok)' : 'var(--danger)'}">
        ${esDep ? '+' : '-'}${fmt.sol(Math.abs(m.monto))}
      </td>
      <td class="${saldoC}" style="font-weight:700">${fmt.sol(m.saldo)}</td>
    </tr>`;
  }).join('');
}

// ── Gráfico tipo consumo ─────────────────────────────────────
async function cargarGraficoTipo(fd, fh) {
  const data = await api(`/api/dashboard/consumo-por-unidad?fecha_desde=${fd}&fecha_hasta=${fh}`);
  const ctx  = document.getElementById('chart-tipo-consumo');
  if (!ctx) return;

  // Agrupar por tipo_unidad
  const byTipo = {};
  data.forEach(r => {
    if (!byTipo[r.tipo_unidad]) byTipo[r.tipo_unidad] = 0;
    byTipo[r.tipo_unidad] += parseFloat(r.galones || 0);
  });
  const labels = Object.keys(byTipo);
  const vals   = labels.map(k => +byTipo[k].toFixed(1));

  if (CHARTS.tipoConsumo) { CHARTS.tipoConsumo.destroy(); }
  CHARTS.tipoConsumo = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data:            vals,
        backgroundColor: PAL.slice(0, labels.length),
        borderWidth:     2, borderColor: '#fff',
      }],
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '50%',
      plugins: {
        legend: { position: 'bottom', labels: { font: { size: 11 }, color: '#5c5b55' } },
        tooltip: { callbacks: { label: ctx => ` ${fmt.num(ctx.raw, 1)} gll` } },
      },
    },
  });
}

// ── Modal compra ────────────────────────────────────────────
async function abrirModalCompra(id) {
  limpiarFormCompra();
  await cargarSaldoGrifo();

  if (id) {
    document.getElementById('modal-compra-title').textContent = 'Editar Compra';
    const c = comprasData.find(x => x.id_combustible === id);
    if (c) rellenarFormCompra(c);
  } else {
    document.getElementById('modal-compra-title').textContent = 'Nueva Compra de Combustible';
  }

  actualizarInfoSaldo();
  abrirModal('modal-compra');
}

function limpiarFormCompra() {
  ['c-id','c-nro-comp','c-km','c-galones','c-precio','c-subtotal','c-igv','c-total'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  document.getElementById('c-fecha').value     = '<?= date('Y-m-d') ?>';
  document.getElementById('c-unidad').value    = '';
  document.getElementById('c-grifo').value     = '';
  document.getElementById('c-tipo-comp').value = '';
  document.getElementById('c-tipo-comb').value = '';
  document.getElementById('c-pago').value      = 'CONTADO';
  toggleKmField();
  document.getElementById('saldo-info-wrap').style.display = 'none';
}

function rellenarFormCompra(c) {
  document.getElementById('c-id').value        = c.id_combustible;
  document.getElementById('c-fecha').value     = c.fecha;
  document.getElementById('c-grifo').value     = c.id_grifo    || '';
  document.getElementById('c-unidad').value    = c.id_unidad   || '';
  document.getElementById('c-tipo-comp').value = c.tipo_comprobante || '';
  document.getElementById('c-nro-comp').value  = c.nro_comprobante  || '';
  document.getElementById('c-tipo-comb').value = c.tipo_combustible || '';
  document.getElementById('c-km').value        = c.km_vehiculo  ?? '';
  document.getElementById('c-galones').value   = c.cantidad_gll ?? '';
  document.getElementById('c-precio').value    = c.precio_unitario ?? '';
  document.getElementById('c-subtotal').value  = c.subtotal     ?? '';
  document.getElementById('c-igv').value       = c.igv          ?? '';
  document.getElementById('c-total').value     = c.total        ?? '';
  document.getElementById('c-pago').value      = c.forma_pago   || 'CONTADO';
  const tq = document.getElementById('c-tanqueo'); if(tq) tq.value = c.tanqueo ?? 1;
  toggleKmField();
  actualizarInfoSaldo();
}

// ── Mostrar / ocultar campo km ──────────────────────────────
function toggleKmField() {
  const unidad   = document.getElementById('c-unidad').value;
  const tipoComb = document.getElementById('c-tipo-comb').value;
  const kmWrap   = document.getElementById('km-field-wrap');
  const gasNota  = document.getElementById('gasolina-nota');
  const kmLabel  = document.getElementById('km-field-label');

  if (!unidad) {
    kmWrap.style.display  = 'none';
    gasNota.style.display = 'none';
    return;
  }

  if (tipoComb === 'GASOLINA') {
    kmWrap.style.display  = 'none';
    gasNota.style.display = 'block';
    return;
  }

  gasNota.style.display = 'none';

  if (tipoComb === 'PETROLEO') {
    // Detectar si es maquinaria para cambiar la etiqueta
    const sel = document.getElementById('c-unidad');
    const opt = sel.options[sel.selectedIndex];
    const tipo = opt?.text?.includes('MAQUINARIA') ? 'maquinaria' : 'flota';
    kmLabel.textContent = tipo === 'maquinaria'
      ? 'Horómetro al momento del tanqueo'
      : 'km del vehículo al momento del tanqueo';
    kmWrap.style.display = 'block';
  } else {
    kmWrap.style.display = 'none';
  }
}

// ── Calcular montos ──────────────────────────────────────────
// LÓGICA: precio_unitario ya incluye IGV
//   gll × precio_unitario = TOTAL (con IGV)
//   subtotal = total / 1.18
//   igv      = total - subtotal

function descomponerTotal(total) {
  // Dado el total con IGV, calcular subtotal e IGV
  const sub = Math.round(total / 1.18 * 100) / 100;
  const igv = Math.round((total - sub) * 100) / 100;
  document.getElementById('c-subtotal').value = sub.toFixed(2);
  document.getElementById('c-igv').value      = igv.toFixed(2);
}

function calcularMontos() {
  // Disparado por cambio en gll o precio_unitario
  const gll   = parseFloat(document.getElementById('c-galones').value) || 0;
  const pu    = parseFloat(document.getElementById('c-precio').value)  || 0;
  if (gll > 0 && pu > 0) {
    const total = Math.round(gll * pu * 100) / 100;
    document.getElementById('c-total').value = total.toFixed(2);
    descomponerTotal(total);
  } else if (gll > 0 || pu > 0) {
    // Solo uno de los dos — calcular igv/subtotal del total actual
    const total = parseFloat(document.getElementById('c-total').value) || 0;
    if (total > 0) descomponerTotal(total);
  }
  actualizarInfoSaldo();
}

// Disparado por cambio manual en el total
document.getElementById('c-total')?.addEventListener('input', function() {
  const total = parseFloat(this.value) || 0;
  if (total > 0) descomponerTotal(total);
  actualizarInfoSaldo();
});

function actualizarInfoSaldo() {
  const pago  = document.getElementById('c-pago').value;
  const wrap  = document.getElementById('saldo-info-wrap');
  const total = parseFloat(document.getElementById('c-total').value) || 0;

  if (pago === 'CREDITO' && total > 0) {
    wrap.style.display = 'block';
    document.getElementById('saldo-info-val').textContent   = fmt.sol(saldoActual);
    document.getElementById('saldo-info-nuevo').textContent = fmt.sol(saldoActual - total);
  } else {
    wrap.style.display = 'none';
  }
}

// Escuchar cambio en forma de pago
document.getElementById('c-pago')?.addEventListener('change', actualizarInfoSaldo);

// ── Guardar compra ───────────────────────────────────────────
async function guardarCompra() {
  const id = document.getElementById('c-id').value;

  const payload = {
    fecha:             document.getElementById('c-fecha').value,
    tipo_comprobante:  document.getElementById('c-tipo-comp').value || null,
    nro_comprobante:   document.getElementById('c-nro-comp').value  || null,
    id_grifo:          document.getElementById('c-grifo').value     || null,
    id_unidad:         document.getElementById('c-unidad').value    || null,
    tipo_combustible:  document.getElementById('c-tipo-comb').value,
    cantidad_gll:      document.getElementById('c-galones').value,
    km_vehiculo:       document.getElementById('c-km').value        || null,
    precio_unitario:   document.getElementById('c-precio').value,
    total:             document.getElementById('c-total').value      || null,
    forma_pago:        document.getElementById('c-pago').value,
    tanqueo:           document.getElementById('c-tanqueo')?.value ?? 1,
  };

  if (!payload.fecha || !payload.tipo_combustible || !payload.cantidad_gll || !payload.precio_unitario) {
    toast('Completa los campos obligatorios (*)', 'error'); return;
  }

  try {
    if (id) {
      const r = await api(`/api/compras/${id}`, { method: 'PUT', body: JSON.stringify(payload) });
      toast(`Compra actualizada · Subtotal: ${fmt.sol(r.subtotal)} · IGV: ${fmt.sol(r.igv)} · Total: ${fmt.sol(r.total)}`, 'ok');
    } else {
      const r = await api('/api/compras', { method: 'POST', body: JSON.stringify(payload) });
      let msg = `Compra registrada · Total: ${fmt.sol(r.total)}`;
      if (r.asignacion?.rows_updated > 0) msg += ` · ${r.asignacion.rows_updated} viaje(s) asignados automáticamente`;
      toast(msg, 'ok');
    }
    cerrarModal('modal-compra');
    // Limpiar la marca de total editado
    const totalEl = document.getElementById('c-total');
    if (totalEl) delete totalEl.dataset.editado;
    await Promise.all([cargarCompras(), cargarMovimientos(), cargarSaldoGrifo()]);
  } catch(e) {
    toast('Error: ' + e.message, 'error');
  }
}

// ── Eliminar compra ──────────────────────────────────────────
function confirmarEliminarCompra(id) {
  document.getElementById('btn-del-compra').onclick = async () => {
    try {
      await api(`/api/compras/${id}`, { method: 'DELETE' });
      toast('Compra eliminada', 'ok');
      cerrarModal('modal-confirmar-c');
      cargarCompras();
      cargarMovimientos();
      cargarSaldoGrifo();
    } catch(e) {
      toast('Error: ' + e.message, 'error');
    }
  };
  abrirModal('modal-confirmar-c');
}

// ── Modal depósito ───────────────────────────────────────────
async function abrirModalDeposito() {
  await cargarSaldoGrifo();
  document.getElementById('dep-fecha').value = '<?= date('Y-m-d') ?>';
  document.getElementById('dep-monto').value = '';
  document.getElementById('dep-desc').value  = '';
  document.getElementById('dep-saldo-nuevo').textContent = fmt.sol(saldoActual);
  abrirModal('modal-deposito');
}

function calcularNuevoSaldo() {
  const monto = parseFloat(document.getElementById('dep-monto').value) || 0;
  document.getElementById('dep-saldo-nuevo').textContent = fmt.sol(saldoActual + monto);
}

async function guardarDeposito() {
  const payload = {
    fecha:       document.getElementById('dep-fecha').value,
    monto:       document.getElementById('dep-monto').value,
    descripcion: document.getElementById('dep-desc').value || 'Depósito a cuenta grifo',
  };
  if (!payload.monto || parseFloat(payload.monto) <= 0) {
    toast('Ingresa un monto válido', 'error'); return;
  }
  try {
    const r = await api('/api/compras/deposito', { method: 'POST', body: JSON.stringify(payload) });
    toast(`Depósito registrado · Nuevo saldo: ${fmt.sol(r.saldo_nuevo)}`, 'ok');
    cerrarModal('modal-deposito');
    cargarMovimientos();
    cargarSaldoGrifo();
  } catch(e) {
    toast('Error: ' + e.message, 'error');
  }
}

// ── Exportar Excel ───────────────────────────────────────────
async function exportarCompras() {
  try {
    toast('Generando Excel…', 'warn');
    const rows = comprasData.map(c => ({
      'Fecha':             c.fecha,
      'Comprobante':       [c.tipo_comprobante, c.nro_comprobante].filter(Boolean).join(' ') || '—',
      'Grifo':             c.grifo_nombre || '—',
      'Unidad':            c.placa || 'Bidón',
      'Tipo':              c.tipo_unidad || '—',
      'Combustible':       c.tipo_combustible,
      'Galones':           parseFloat(c.cantidad_gll),
      'Precio Unit. S/':   parseFloat(c.precio_unitario),
      'Subtotal S/':       parseFloat(c.subtotal),
      'IGV S/':            parseFloat(c.igv),
      'Total S/':          parseFloat(c.total),
      'km / Horómetro':    c.km_vehiculo ?? '',
      'Forma de pago':     c.forma_pago,
    }));
    const ws = XLSX.utils.json_to_sheet(rows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Compras');

    // Hoja movimientos
    const movs = await api('/api/compras/movimientos');
    const wsMov = XLSX.utils.json_to_sheet(movs.map(m => ({
      'Fecha':       m.fecha,
      'Tipo':        m.tipo,
      'Descripción': m.descripcion,
      'Monto S/':    parseFloat(m.monto),
      'Saldo S/':    parseFloat(m.saldo),
    })));
    XLSX.utils.book_append_sheet(wb, wsMov, 'Movimientos Grifo');

    const fd = document.getElementById('f-desde').value;
    const fh = document.getElementById('f-hasta').value;
    XLSX.writeFile(wb, `Combustible_${fd}_${fh}.xlsx`);
    toast('Excel generado', 'ok');
  } catch(e) {
    toast('Error al exportar: ' + e.message, 'error');
  }
}

// ── Asignación manual bidireccional ──────────────────────────
// Modo A: desde COMPRA → asignar viajes
// Modo B: desde VIAJE  → buscar compra correcta
let asignComprasCache = [];
let asignViajesCache  = [];

async function cargarViajesPendientes() {
  // Cargar viajes sin asignar (últimos 90 días)
  const desde = new Date(Date.now()-90*86400000).toISOString().slice(0,10);
  const viajes  = await api('/api/garita/viajes?solo_sin_asignar=1&fecha_desde='+desde);
  asignViajesCache = viajes;

  // Cargar todas las compras de petróleo (para el selector de compras)
  const compras = await api('/api/compras?tipo_combustible=PETROLEO');
  asignComprasCache = compras;

  // Poblar selector de viajes
  const selV = document.getElementById('asign-viaje-sel');
  selV.innerHTML = '<option value="">— Seleccionar viaje sin asignar —</option>';
  viajes.forEach(v => {
    selV.insertAdjacentHTML('beforeend',
      '<option value="'+v.id_control+'">'+fmtFecha(v.fecha)+' · '+v.placa+' · km '+
      (v.km_salida!=null?fmt.num(v.km_salida,1):'?')+' → '+(v.km_retorno!=null?fmt.num(v.km_retorno,1):'?')+
      ' · '+(v.tipo_actividad||'—')+'</option>');
  });

  // Poblar selector de compras
  const selC = document.getElementById('asign-comb-sel');
  selC.innerHTML = '<option value="">— Seleccionar compra de combustible —</option>';
  compras.forEach(cc => {
    selC.insertAdjacentHTML('beforeend',
      '<option value="'+cc.id_combustible+'">'+fmtFecha(cc.fecha)+' · '+(cc.placa||'Bidón')+
      ' · km '+(cc.km_vehiculo!=null?fmt.num(cc.km_vehiculo,1):'—')+
      ' · '+fmt.num(cc.cantidad_gll,1)+' gll · '+fmt.sol(cc.total)+'</option>');
  });

  renderViajesPend(viajes);
}

function renderViajesPend(data) {
  const tbody = document.getElementById('asign-viajes-tbody');
  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="empty">Sin viajes pendientes.</td></tr>'; return;
  }
  tbody.innerHTML = data.map(function(v) {
    return '<tr>' +
      '<td>'+fmtFecha(v.fecha)+'</td>' +
      '<td><strong>'+v.placa+'</strong></td>' +
      '<td class="text-sm">'+(v.destino?v.origen+'→'+v.destino:v.tipo_actividad||'—')+'</td>' +
      '<td class="mono">'+(v.km_salida!=null?fmt.num(v.km_salida,1):'—')+'</td>' +
      '<td class="mono">'+(v.km_retorno!=null?fmt.num(v.km_retorno,1):'—')+'</td>' +
      '<td>'+(parseFloat(v.km_recorrido||0)>0?fmt.num(v.km_recorrido,1)+' km':'—')+'</td>' +
      '<td><span class="badge badge-warn">Sin asignar</span></td>' +
      '<td><button class="btn btn-outline btn-xs" onclick="seleccionarViaje('+v.id_control+')">Seleccionar</button></td>' +
    '</tr>';
  }).join('');
}

async function seleccionarViaje(id) {
  document.getElementById('asign-viaje-sel').value = id;
  mostrarDetalleViaje();
  document.getElementById('panel-asign-selects').scrollIntoView({behavior:'smooth'});

  var v = asignViajesCache.find(function(x){ return x.id_control==id; });
  if (!v) return;
  var kmS = parseFloat(v.km_salida||0);
  var kmR = parseFloat(v.km_retorno||0);

  // Cargar compras candidatas con flags cond1/cond2/ya_asignado del servidor
  var url = '/api/garita/compras-para-asignar?id_unidad=' + v.id_unidad +
            '&km_salida=' + kmS + '&km_retorno=' + kmR + '&id_control=' + id;
  try {
    var compras = await api(url);
    var sugeridas = compras.filter(function(cc){ return cc.sugerido==1; });

    var selC = document.getElementById('asign-comb-sel');
    selC.innerHTML = '<option value="">— Seleccionar compra —</option>';
    compras.forEach(function(cc) {
      var prefix = cc.ya_asignado          ? '✓ YA ASIG · '
                 : cc.relacion === 'DURANTE'    ? '⭐ SUGERIDA · '
                 : cc.relacion === 'ANTERIOR'   ? '⬆ ANTERIOR · '
                 : cc.relacion === 'EMERGENCIA' ? '🚨 EMERGENCIA · '
                 : cc.relacion === 'EN_RANGO'   ? '📍 EN RANGO · '
                 : cc.relacion === 'POSTERIOR'  ? '⏭ POSTERIOR · '
                 : '◽ ';
      selC.insertAdjacentHTML('beforeend',
        '<option value="' + cc.id_combustible + '">' +
        prefix + fmtFecha(cc.fecha) +
        ' · km ' + (cc.km_vehiculo!=null?fmt.num(cc.km_vehiculo,1):'—') +
        ' · ' + fmt.num(cc.cantidad_gll,1) + ' gll' +
        ' · ' + fmt.sol(cc.total) +
        (cc.ya_asignado ? ' ✓' : '') +
        '</option>');
    });

    var msg        = document.getElementById('asign-sugerido-msg');
    var sugeridas   = compras.filter(function(cc){ return cc.relacion==='DURANTE' || cc.relacion==='ANTERIOR'; });
    var emergencias = compras.filter(function(cc){ return cc.relacion==='EMERGENCIA'; });
    var enRango     = compras.filter(function(cc){ return cc.relacion==='EN_RANGO'; });
    var posteriores= compras.filter(function(cc){ return cc.relacion==='POSTERIOR'; });

    if (sugeridas.length > 0) {
      selC.value = sugeridas[0].id_combustible;
      msg.style.color = 'var(--ok)';
      msg.innerHTML = sugeridas.length === 1
        ? '⭐ <strong>Sugerida (' + sugeridas[0].relacion + '):</strong> km ' +
          fmt.num(sugeridas[0].km_vehiculo,1) + ' · ' + fmt.num(sugeridas[0].cantidad_gll,1) + ' gll · ' + fmtFecha(sugeridas[0].fecha)
        : '⭐ <strong>' + sugeridas.length + ' compras sugeridas</strong> — selecciona y asigna una a una';
    } else if (emergencias.length > 0) {
      selC.value = emergencias[0].id_combustible;
      msg.style.color = 'var(--warn)';
      msg.innerHTML = '🚨 <strong>' + emergencias.length + ' compra(s) de emergencia</strong> (tanqueo parcial dentro del viaje) — asigna manualmente';
    } else if (enRango.length > 0) {
      selC.value = enRango[0].id_combustible;
      msg.style.color = 'var(--warn)';
      msg.innerHTML = '📍 <strong>' + enRango.length + ' compra(s) dentro del trayecto</strong> — verifica y asigna';
    } else if (posteriores.length > 0) {
      msg.style.color = 'var(--text3)';
      msg.innerHTML = '⚠ Sin tanqueo exacto · Se muestran las ' + compras.length + ' compras más recientes de este vehículo · Selecciona la más cercana';
    } else {
      msg.style.color = 'var(--danger)';
      msg.innerHTML = '❌ Sin compras de combustible registradas para este vehículo';
    }
    msg.style.display = 'block';
  } catch(e) { console.error(e); }
}


function mostrarDetalleViaje() {
  const id = document.getElementById('asign-viaje-sel').value;
  const el = document.getElementById('asign-viaje-detalle');
  if (!id) { el.textContent=''; return; }
  const v = asignViajesCache.find(function(x){ return x.id_control==id; });
  if (!v) return;
  el.innerHTML = '<strong>Viaje:</strong> '+v.placa+' · '+fmtFecha(v.fecha)+
    ' · km '+(v.km_salida!=null?fmt.num(v.km_salida,1):'—')+' → '+(v.km_retorno!=null?fmt.num(v.km_retorno,1):'—')+
    (parseFloat(v.km_recorrido||0)>0?' ('+fmt.num(v.km_recorrido,1)+' km)':'')+
    ' · '+(v.conductor_nombre||'—')+' · '+(v.tipo_actividad||'Sin actividad')+
    (v.observacion?' · '+v.observacion:'');
}

// ── Diagnóstico de asignaciones ──────────────────────────────
async function cargarDiagnostico() {
  var desde  = document.getElementById('diag-desde').value;
  var hasta  = document.getElementById('diag-hasta').value;
  var uid    = document.getElementById('diag-unidad').value;
  var tbody  = document.getElementById('diag-tbody');
  tbody.innerHTML = '<tr><td colspan="11" class="empty">Cargando…</td></tr>';

  // Cargar detalle_consumo con info de viaje y compra
  var url = '/api/compras/diagnostico?fecha_desde=' + desde + '&fecha_hasta=' + hasta;
  if (uid) url += '&id_unidad=' + uid;

  var rows = await api(url);
  tx('diag-count', rows.length + ' relaciones');

  // KPIs
  var sinAsig   = rows.filter(function(r){ return !r.id_combustible; }).length;
  var malAsig   = rows.filter(function(r){ return r.id_combustible && r.estado_rel==='MAL'; }).length;
  var okAsig    = rows.filter(function(r){ return r.id_combustible && r.estado_rel!=='MAL'; }).length;
  document.getElementById('diag-kpis').innerHTML =
    '<div class="kpi-card"><div class="kpi-lbl">Sin asignar</div><div class="kpi-val warn">' + sinAsig + '</div></div>' +
    '<div class="kpi-card"><div class="kpi-lbl">Posible error</div><div class="kpi-val" style="color:var(--danger)">' + malAsig + '</div></div>' +
    '<div class="kpi-card"><div class="kpi-lbl">Correctos</div><div class="kpi-val ok">' + okAsig + '</div></div>' +
    '<div class="kpi-card"><div class="kpi-lbl">Total relaciones</div><div class="kpi-val brand">' + rows.length + '</div></div>';

  if (!rows.length) {
    tbody.innerHTML = '<tr><td colspan="11" class="empty">Sin registros.</td></tr>';
    return;
  }

  tbody.innerHTML = rows.map(function(r) {
    var sinComb = !r.id_combustible;
    var esMal   = r.id_combustible && r.estado_rel === 'MAL';
    var rowBg   = sinComb ? 'background:var(--warn-lt)' : esMal ? 'background:var(--danger-lt)' : '';
    var badge   = sinComb
      ? '<span class="badge badge-warn">Sin asignar</span>'
      : esMal
        ? '<span class="badge badge-danger" title="' + (r.motivo_mal||'') + '">⚠ ' + (r.relacion_km||'?') + '</span>'
        : '<span class="badge badge-ok">' + (r.relacion_km||'OK') + '</span>';

    return '<tr style="' + rowBg + '">' +
      '<td class="mono">#' + r.id_control + '</td>' +
      '<td>' + fmtFecha(r.fecha_viaje) + '</td>' +
      '<td><strong>' + (r.placa||'—') + '</strong></td>' +
      '<td class="mono">' + (r.km_salida!=null?fmt.num(r.km_salida,0):'—') + '</td>' +
      '<td class="mono">' + (r.km_retorno!=null?fmt.num(r.km_retorno,0):'—') + '</td>' +
      '<td class="mono text-sm">' + (r.nro_comprobante||'<span class="text-muted">—</span>') + '</td>' +
      '<td class="mono">' + (r.km_tanqueo!=null?fmt.num(r.km_tanqueo,0):'—') + '</td>' +
      '<td class="mono">' + (r.cantidad_gll!=null?fmt.num(r.cantidad_gll,1):'—') + '</td>' +
      '<td>' + badge + '</td>' +
      '<td class="text-sm" style="color:var(--text3)">' + (r.motivo_mal||'') + '</td>' +
      '<td>' +
        '<button class="btn btn-xs btn-outline" onclick="reasignarFila(' + r.id_control + ',' + r.id_detalle + ')"' +
          ' title="Quitar esta asignación y dejar el viaje sin asignar para corregirlo">🔄</button>' +
      '</td>' +
    '</tr>';
  }).join('');
}

async function reasignarFila(id_ctrl, id_detalle) {
  if (!confirm('¿Quitar esta asignación? El viaje quedará sin combustible para que puedas reasignarlo correctamente.')) return;
  try {
    // Eliminar el detalle específico y dejar fila NULL
    await api('/api/compras/asignar-viaje', {method:'POST', body:JSON.stringify({
      id_control: id_ctrl, id_combustible: null, accion: 'reemplazar'
    })});
    toast('Asignación quitada — reasigna desde Garita o el panel de asignación','ok');
    cargarDiagnostico();
  } catch(e){ toast('Error: '+e.message,'error'); }
}

async function recalcularAsignaciones() {
  if (!confirm(
    'Esto revisará TODAS las asignaciones existentes y corregirá las que no\n' +
    'cumplen la lógica de km (km_salida <= km_tanqueo <= km_retorno + 4).\n\n' +
    'Las correctas no se tocan. Las incorrectas se actualizan a la compra correcta.\n\n' +
    '¿Continuar?'
  )) return;
  try {
    toast('Analizando y corrigiendo asignaciones...', 'warn');
    var r = await api('/api/compras/recalcular-asignaciones', {method:'POST', body:JSON.stringify({solo_erroneos:true})});
    toast(
      '✓ ' + r.corregidos + ' corregidas · ' +
      (r.emergencias_propagadas||0) + ' emergencias propagadas · ' +
      r.correctos + ' correctas · ' +
      r.sin_match + ' sin compra válida · ' +
      r.total + ' revisadas',
      'ok'
    );
    cargarDiagnostico();
  } catch(e) { toast('Error: ' + e.message, 'error'); }
}

async function limpiarNulos() {
  if (!confirm('¿Eliminar todas las filas de detalle_consumo donde id_combustible es NULL? (viajes sin asignar seguirán apareciendo en la lista)')) return;
  try {
    var r = await api('/api/compras/limpiar-nulos', {method:'POST'});
    toast('Eliminadas ' + r.eliminadas + ' filas vacías','ok');
    cargarDiagnostico();
  } catch(e){ toast('Error: '+e.message,'error'); }
}

async function reasignarVacios() {
  if (!confirm(
    'Buscará viajes SIN combustible asignado y les asignará la compra correcta\n' +
    'según la lógica de km (km_tanqueo > km_salida y diferencia ≤ 4 km).\n\n' +
    'Los viajes que ya tienen asignación NO se tocan.\n\n¿Continuar?'
  )) return;
  try {
    toast('Procesando asignaciones automáticas…', 'warn');
    var r = await api('/api/compras/reasignar-vacios', {
      method: 'POST',
      body: JSON.stringify({ solo_vacios: true })
    });
    toast(
      'Listo: ' + r.asignados + ' viajes asignados · ' +
      r.saltados + ' sin coincidencia o ya asignados · ' +
      r.total + ' viajes revisados',
      'ok'
    );
    cargarCompras();
    cargarViajesPendientes();
  } catch(e) { toast('Error: ' + e.message, 'error'); }
}

async function asignarViajeManual() {
  var id_ctrl = document.getElementById('asign-viaje-sel').value;
  var id_comb = document.getElementById('asign-comb-sel').value;
  if (!id_ctrl || !id_comb) { toast('Selecciona viaje y compra','error'); return; }
  try {
    // Usar 'agregar' para soportar múltiples tanqueos por viaje
    await api('/api/compras/asignar-viaje',{method:'POST',body:JSON.stringify({
      id_control:    +id_ctrl,
      id_combustible:+id_comb,
      accion:        'agregar'
    })});
    toast('Combustible asignado al viaje','ok');
    // Recargar compras sugeridas para ver si quedan más por asignar
    await seleccionarViaje(id_ctrl);
    cargarCompras();
  } catch(e) { toast('Error: '+e.message,'error'); }
}

async function desasignarViaje() {
  var id_ctrl = document.getElementById('asign-viaje-sel').value;
  var id_comb = document.getElementById('asign-comb-sel').value;
  if (!id_ctrl) { toast('Selecciona un viaje','warn'); return; }

  var msg = id_comb
    ? '¿Quitar solo esta compra (' + id_comb + ') del viaje?'
    : '¿Quitar TODA la asignación de combustible de este viaje?';
  if (!confirm(msg)) return;

  try {
    if (id_comb) {
      // Quitar solo el combustible seleccionado
      await api('/api/compras/asignar-viaje', {method:'POST', body:JSON.stringify({
        id_control:    +id_ctrl,
        id_combustible:+id_comb,
        accion:        'quitar'
      })});
      toast('Compra quitada del viaje','ok');
    } else {
      // Quitar toda la asignación → dejar fila NULL
      await api('/api/compras/asignar-viaje', {method:'POST', body:JSON.stringify({
        id_control:    +id_ctrl,
        id_combustible:null,
        accion:        'reemplazar'
      })});
      toast('Asignación quitada','ok');
    }
    await seleccionarViaje(id_ctrl);
    cargarViajesPendientes();
  } catch(e) { toast('Error: '+e.message,'error'); }
}


// ── Diagnóstico de asignaciones ──────────────────────────────
async function cargarDiagnostico() {
  var desde = document.getElementById('diag-desde').value;
  var hasta  = document.getElementById('diag-hasta').value;
  var uid    = document.getElementById('diag-unidad').value;
  var tbody  = document.getElementById('diag-tbody');
  tbody.innerHTML = '<tr><td colspan="11" class="empty">Analizando…</td></tr>';

  var url = '/api/compras/diagnostico?fecha_desde=' + desde + '&fecha_hasta=' + hasta;
  if (uid) url += '&id_unidad=' + uid;

  var rows = await api(url);
  tx('diag-count', rows.length + ' relaciones');

  var sinAsig = rows.filter(function(r){ return r.estado_rel==='VACIO'; }).length;
  var malAsig = rows.filter(function(r){ return r.estado_rel==='MAL'; }).length;
  var okAsig  = rows.filter(function(r){ return r.estado_rel==='OK'; }).length;
  document.getElementById('diag-kpis').innerHTML =
    '<div class="kpi-card"><div class="kpi-lbl">Sin asignar</div><div class="kpi-val warn">' + sinAsig + '</div></div>' +
    '<div class="kpi-card"><div class="kpi-lbl">Posible error</div><div class="kpi-val" style="color:var(--danger)">' + malAsig + '</div></div>' +
    '<div class="kpi-card"><div class="kpi-lbl">Correctos</div><div class="kpi-val ok">' + okAsig + '</div></div>' +
    '<div class="kpi-card"><div class="kpi-lbl">Total</div><div class="kpi-val brand">' + rows.length + '</div></div>';

  if (!rows.length) {
    tbody.innerHTML = '<tr><td colspan="11" class="empty">Sin registros en este período.</td></tr>';
    return;
  }

  tbody.innerHTML = rows.map(function(r) {
    var sinC = r.estado_rel === 'VACIO';
    var mal  = r.estado_rel === 'MAL';
    var bg   = sinC ? 'background:var(--warn-lt)' : mal ? 'background:var(--danger-lt)' : '';
    var enCurso = r.en_curso == 1;
    var badge = sinC
      ? '<span class="badge badge-warn">Sin asignar</span>'
      : mal
        ? '<span class="badge badge-danger">⚠ ' + r.relacion_km + '</span>'
        : '<span class="badge badge-ok">' + r.relacion_km + '</span>';
    var cursoTag = enCurso ? ' <span class="badge" style="background:var(--brand);color:#fff;font-size:9px">EN CURSO</span>' : '';
    return '<tr style="' + bg + '">' +
      '<td class="mono text-sm">#' + r.id_control + cursoTag + '</td>' +
      '<td>' + fmtFecha(r.fecha_viaje) + '</td>' +
      '<td><strong>' + (r.placa||'—') + '</strong></td>' +
      '<td class="mono">' + (r.km_salida!=null?fmt.num(r.km_salida,0):'—') + '</td>' +
      '<td class="mono">' + (r.km_retorno!=null?fmt.num(r.km_retorno,0):'—') + '</td>' +
      '<td class="text-sm">' + (r.nro_comprobante||'<span class="text-muted">—</span>') + '</td>' +
      '<td class="mono">' + (r.km_tanqueo!=null?fmt.num(r.km_tanqueo,0):'—') + '</td>' +
      '<td class="mono">' + (r.cantidad_gll!=null?fmt.num(r.cantidad_gll,1):'—') + '</td>' +
      '<td>' + badge + '</td>' +
      '<td class="text-xs text-muted" style="max-width:180px">' + (r.motivo_mal||'') + '</td>' +
      '<td>' +
        '<button class="btn btn-xs btn-outline" title="Quitar asignación" ' +
          'onclick="quitarAsignFila(' + r.id_control + ',' + (r.id_combustible||'null') + ')">🔄</button>' +
      '</td>' +
    '</tr>';
  }).join('');
}

async function quitarAsignFila(id_ctrl, id_comb) {
  var msg = id_comb && id_comb !== 'null'
    ? 'Quitar la compra #' + id_comb + ' de este viaje?'
    : 'Limpiar la fila vacía del viaje #' + id_ctrl + '?';
  if (!confirm(msg)) return;
  try {
    if (id_comb && id_comb !== 'null') {
      await api('/api/compras/asignar-viaje', {method:'POST', body:JSON.stringify({
        id_control: id_ctrl, id_combustible: id_comb, accion: 'quitar'
      })});
    } else {
      await api('/api/compras/asignar-viaje', {method:'POST', body:JSON.stringify({
        id_control: id_ctrl, id_combustible: null, accion: 'reemplazar'
      })});
    }
    toast('Asignacion quitada', 'ok');
    cargarDiagnostico();
  } catch(e) { toast('Error: ' + e.message, 'error'); }
}

async function recalcularAsignaciones() {
  if (!confirm(
    'Esto revisará TODAS las asignaciones existentes y corregirá las que no\n' +
    'cumplen la lógica de km (km_salida <= km_tanqueo <= km_retorno + 4).\n\n' +
    'Las correctas no se tocan. Las incorrectas se actualizan a la compra correcta.\n\n' +
    '¿Continuar?'
  )) return;
  try {
    toast('Analizando y corrigiendo asignaciones...', 'warn');
    var r = await api('/api/compras/recalcular-asignaciones', {method:'POST', body:JSON.stringify({solo_erroneos:true})});
    toast(
      '✓ ' + r.corregidos + ' corregidas · ' +
      (r.emergencias_propagadas||0) + ' emergencias propagadas · ' +
      r.correctos + ' correctas · ' +
      r.sin_match + ' sin compra válida · ' +
      r.total + ' revisadas',
      'ok'
    );
    cargarDiagnostico();
  } catch(e) { toast('Error: ' + e.message, 'error'); }
}

async function limpiarNulos() {
  if (!confirm('Eliminar todas las filas de detalle_consumo sin combustible asignado?')) return;
  try {
    var r = await api('/api/compras/limpiar-nulos', {method:'POST'});
    toast('Eliminadas ' + r.eliminadas + ' filas vacias', 'ok');
    cargarDiagnostico();
  } catch(e) { toast('Error: ' + e.message, 'error'); }
}

// Poblar selector de unidades del diagnostico
api('/api/unidades').then(function(us){
  var sel = document.getElementById('diag-unidad');
  if(!sel) return;
  us.filter(function(u){ return u.tipo_unidad==='FLOTA'; }).forEach(function(u){
    sel.insertAdjacentHTML('beforeend','<option value="'+u.id_unidad+'">'+u.placa+'</option>');
  });
}).catch(function(){});

</script>

<!-- ═══════════════════════════════════════════════════════════
     PANEL: Asignación manual de viajes ↔ compras
════════════════════════════════════════════════════════════ -->
<div class="card full" style="margin-top:var(--gap)">
  <div class="card-title">
    🔗 Asignación manual — Viajes sin combustible
    <button class="btn btn-outline btn-sm" onclick="cargarViajesPendientes()">🔄 Cargar pendientes</button>
  </div>
  <div style="background:var(--brand-lt);border:1px solid var(--brand);border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:14px">
    Selecciona un viaje de la tabla → se sugiere automáticamente la compra correcta según km.
    Puedes cambiarla manualmente y confirmar con <strong>Asignar</strong>.
    Un viaje puede pertenecer a más de una compra (combustible adicional) y una compra puede cubrir varios viajes.
  </div>

  <div id="panel-asign-selects" style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px 16px;margin-bottom:14px">
    <!-- Fila superior: viaje + compra + botones — todo visible sin scroll -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin-bottom:8px">
      <div class="fgroup" style="flex:2;min-width:200px;margin:0">
        <label>Viaje seleccionado</label>
        <select id="asign-viaje-sel" onchange="mostrarDetalleViaje()">
          <option value="">— Selecciona en la tabla de abajo —</option>
        </select>
      </div>
      <div class="fgroup" style="flex:2;min-width:200px;margin:0">
        <label>Compra a asignar</label>
        <select id="asign-comb-sel">
          <option value="">— Cargando… —</option>
        </select>
      </div>
      <!-- Botones pegados al selector -->
      <div style="display:flex;gap:6px;align-items:center;padding-bottom:1px">
        <button class="btn btn-primary" onclick="asignarViajeManual()"
                style="height:38px;font-size:14px;padding:0 18px;white-space:nowrap">
          ✓ Asignar
        </button>
        <button class="btn btn-outline btn-sm" onclick="desasignarViaje()"
                style="height:38px;white-space:nowrap" title="Quitar la compra seleccionada del viaje">
          ✕ Quitar
        </button>
        <button class="btn btn-outline btn-sm" onclick="reasignarVacios()"
                style="height:38px;border-color:var(--brand);color:var(--brand);white-space:nowrap"
                title="Asigna automáticamente a todos los viajes sin combustible">
          🔄 Auto
        </button>
      </div>
    </div>
    <!-- Mensaje sugerido y detalle del viaje -->
    <div id="asign-sugerido-msg" style="display:none;font-size:12px;border-radius:6px;padding:6px 10px;margin-bottom:4px"></div>
    <div id="asign-viaje-detalle" style="font-size:12px;color:var(--text2)"></div>
  </div>

  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr><th>Fecha</th><th>Unidad</th><th>Ruta/Actividad</th><th>km Sal.</th><th>km Ret.</th><th>km Rec.</th><th>Estado</th><th>Seleccionar</th></tr>
      </thead>
      <tbody id="asign-viajes-tbody">
        <tr><td colspan="8" class="empty">Haz clic en "Cargar pendientes" para ver viajes sin combustible asignado.</td></tr>
      </tbody>
    </table>
  </div>
</div>