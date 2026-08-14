<?php
// views/layouts/header.php
// Cabecera HTML completa: <head>, estilos CSS, topbar y navbar
// $viewName y $alertas_count vienen del index.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= APP_NAME ?></title>
<!-- Chart.js desde CDN — sin npm -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" defer></script>
<!-- SheetJS para exportar Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer></script>
<style>
/* ════════════════════════════════════════════════════════
   RESET & VARIABLES
════════════════════════════════════════════════════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --brand:#185FA5; --brand-lt:#E6F1FB; --brand-dk:#0f3f75;
  --ok:#2d6a4f;    --ok-lt:#d8f3dc;
  --warn:#854F0B;  --warn-lt:#FAEEDA;
  --danger:#A32D2D;--danger-lt:#FCEBEB;
  --info:#0077b6;  --info-lt:#e0f4ff;
  --bg:#f8f7f4;    --bg-card:#ffffff;
  --border:#e4e2db;--border2:#ccc9bf;
  --text:#1a1a1a;  --text2:#5c5b55;  --text3:#9c9a92;
  --radius:10px;   --radius-sm:6px;  --gap:14px;
  --shadow:0 1px 4px rgba(0,0,0,.08);
  --font:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
  --p0:#185FA5;--p1:#1D9E75;--p2:#D85A30;--p3:#534AB7;--p4:#854F0B;--p5:#993556;--p6:#2d6a4f;
}
body{font-family:var(--font);font-size:14px;color:var(--text);background:var(--bg);min-height:100vh}
a{color:inherit;text-decoration:none}
button{font-family:inherit;cursor:pointer}
input,select,textarea{font-family:inherit;font-size:14px}

/* ════════════════════════════════════════════════════════
   TOPBAR
════════════════════════════════════════════════════════ */
.topbar{
  display:flex;align-items:center;justify-content:space-between;
  padding:0 20px;height:52px;
  background:#fff;border-bottom:1px solid var(--border);
  position:sticky;top:0;z-index:300;
  box-shadow:var(--shadow);
}
.topbar-brand{
  display:flex;align-items:center;gap:9px;
  font-size:15px;font-weight:700;color:var(--brand);letter-spacing:-.3px;
}
.topbar-brand svg{flex-shrink:0}
.topbar-right{display:flex;align-items:center;gap:10px}
.topbar-date{font-size:12px;color:var(--text3);display:none}
@media(min-width:480px){.topbar-date{display:block}}

/* ════════════════════════════════════════════════════════
   NAVBAR
════════════════════════════════════════════════════════ */
.navbar{
  display:flex;gap:2px;padding:6px 20px;
  background:#fff;border-bottom:1px solid var(--border);
  overflow-x:auto;-webkit-overflow-scrolling:touch;
  position:sticky;top:52px;z-index:299;
}
.navbar::-webkit-scrollbar{display:none}
.nav-btn{
  display:flex;align-items:center;gap:6px;
  padding:7px 13px;border-radius:8px;
  border:1px solid transparent;background:transparent;
  color:var(--text2);font-size:13px;white-space:nowrap;
  transition:background .12s,color .12s;
}
.nav-btn svg{flex-shrink:0;opacity:.7}
.nav-btn:hover{background:var(--bg);color:var(--text)}
.nav-btn.active{
  background:var(--brand-lt);color:var(--brand);
  border-color:var(--brand);font-weight:600;
}
.nav-btn.active svg{opacity:1}
/* En móvil: solo ícono */
.nav-label{display:none}
@media(min-width:600px){.nav-label{display:inline}}

/* ════════════════════════════════════════════════════════
   LAYOUT MAIN
════════════════════════════════════════════════════════ */
.main{padding:18px 20px;max-width:1400px;margin:0 auto}
@media(max-width:600px){.main{padding:12px}}

/* ════════════════════════════════════════════════════════
   CARDS & ROWS
════════════════════════════════════════════════════════ */
.row{display:flex;gap:var(--gap);flex-wrap:wrap;align-items:stretch}
.card{
  background:var(--bg-card);border:1px solid var(--border);
  border-radius:var(--radius);padding:16px;
  box-shadow:var(--shadow);flex:1;min-width:0;
}
.card.full{flex:0 0 100%}
.card.w2{flex:2;min-width:280px}
.card.w1{flex:1;min-width:220px}
.card-title{
  font-size:11px;font-weight:700;color:var(--text2);
  letter-spacing:.06em;text-transform:uppercase;margin-bottom:14px;
  display:flex;align-items:center;justify-content:space-between;
}
.card-title span{font-size:13px;font-weight:600;color:var(--text);text-transform:none;letter-spacing:0}

/* ════════════════════════════════════════════════════════
   KPI CARDS
════════════════════════════════════════════════════════ */
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(148px,1fr));gap:var(--gap)}
.kpi-card{
  background:var(--bg-card);border:1px solid var(--border);
  border-radius:var(--radius);padding:14px 16px;
  box-shadow:var(--shadow);
}
.kpi-lbl{font-size:11px;color:var(--text3);margin-bottom:4px;font-weight:500}
.kpi-val{font-size:24px;font-weight:700;line-height:1.1;color:var(--text)}
.kpi-sub{font-size:11px;color:var(--text3);margin-top:3px}
.kpi-val.ok{color:var(--ok)}.kpi-val.warn{color:var(--warn)}
.kpi-val.danger{color:var(--danger)}.kpi-val.brand{color:var(--brand)}

/* ════════════════════════════════════════════════════════
   BADGES & SEMÁFORO
════════════════════════════════════════════════════════ */
.badge{
  display:inline-flex;align-items:center;gap:4px;
  font-size:11px;font-weight:600;padding:2px 9px;
  border-radius:999px;white-space:nowrap;
}
.badge-brand  {background:var(--brand-lt); color:var(--brand)}
.badge-ok     {background:var(--ok-lt);    color:var(--ok)}
.badge-warn   {background:var(--warn-lt);  color:var(--warn)}
.badge-danger {background:var(--danger-lt);color:var(--danger)}
.badge-info   {background:var(--info-lt);  color:var(--info)}
.dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;display:inline-block}

/* ════════════════════════════════════════════════════════
   TABLES
════════════════════════════════════════════════════════ */
.tbl-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.tbl{width:100%;border-collapse:collapse;font-size:13px}
.tbl th{
  font-size:11px;font-weight:600;color:var(--text2);
  text-align:left;padding:8px 10px;
  border-bottom:2px solid var(--border);
  white-space:nowrap;background:var(--bg);
}
.tbl td{
  padding:9px 10px;border-bottom:1px solid var(--border);
  vertical-align:middle;
}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr:hover td{background:#fafaf8}
.tbl .mono{font-size:12px;font-family:Menlo,Consolas,monospace}
.tbl .empty{text-align:center;color:var(--text3);padding:24px;font-size:13px}
.tbl .action-btns{display:flex;gap:6px}

/* ════════════════════════════════════════════════════════
   FORMS
════════════════════════════════════════════════════════ */
.form-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
  gap:12px;
}
.form-grid.cols-2{grid-template-columns:repeat(2,1fr)}
.form-grid.cols-3{grid-template-columns:repeat(3,1fr)}
@media(max-width:600px){
  .form-grid,.form-grid.cols-2,.form-grid.cols-3{grid-template-columns:1fr}
}
.fgroup{display:flex;flex-direction:column;gap:5px}
.fgroup.full{grid-column:1/-1}
.fgroup label{font-size:12px;font-weight:600;color:var(--text2)}
.fgroup input,.fgroup select,.fgroup textarea{
  padding:9px 11px;border-radius:var(--radius-sm);
  border:1px solid var(--border2);background:#fff;
  color:var(--text);transition:border-color .15s;
  width:100%;
}
.fgroup input:focus,.fgroup select:focus,.fgroup textarea:focus{
  outline:none;border-color:var(--brand);
  box-shadow:0 0 0 3px rgba(24,95,165,.12);
}
.fgroup input[readonly]{background:var(--bg);color:var(--text3)}
.fgroup textarea{resize:vertical;min-height:70px}
.fgroup .hint{font-size:11px;color:var(--text3)}

/* ════════════════════════════════════════════════════════
   BUTTONS
════════════════════════════════════════════════════════ */
.btn{
  display:inline-flex;align-items:center;gap:6px;
  font-size:13px;font-weight:600;padding:9px 16px;
  border-radius:var(--radius-sm);border:none;
  transition:opacity .15s,transform .1s;cursor:pointer;
}
.btn:active{transform:scale(.97)}
.btn:disabled{opacity:.45;cursor:not-allowed}
.btn-primary {background:var(--brand);color:#fff}
.btn-primary:hover{opacity:.88}
.btn-success {background:var(--ok);color:#fff}
.btn-success:hover{opacity:.88}
.btn-danger  {background:var(--danger);color:#fff}
.btn-danger:hover{opacity:.88}
.btn-warn    {background:var(--warn);color:#fff}
.btn-outline {background:transparent;border:1.5px solid var(--border2);color:var(--text)}
.btn-outline:hover{background:var(--bg)}
.btn-ghost   {background:transparent;color:var(--brand);border:none;padding:6px 10px}
.btn-sm      {font-size:11px;padding:5px 11px;border-radius:var(--radius-sm)}
.btn-xs      {font-size:11px;padding:3px 8px;border-radius:4px}
.btn-icon    {padding:7px;border-radius:var(--radius-sm);background:transparent;border:1px solid var(--border2);color:var(--text2)}
.btn-icon:hover{background:var(--bg)}

/* ════════════════════════════════════════════════════════
   MODALES
════════════════════════════════════════════════════════ */
.overlay{
  position:fixed;inset:0;background:rgba(0,0,0,.45);
  display:none;align-items:center;justify-content:center;
  z-index:1000;padding:16px;
}
.overlay.open{display:flex}
.modal{
  background:#fff;border-radius:12px;
  width:min(580px,100%);max-height:90vh;
  overflow-y:auto;display:flex;flex-direction:column;
  box-shadow:0 24px 64px rgba(0,0,0,.22);
}
.modal.modal-sm{width:min(420px,100%)}
.modal.modal-lg{width:min(780px,100%)}
.modal-hdr{
  display:flex;align-items:center;justify-content:space-between;
  padding:16px 20px;border-bottom:1px solid var(--border);
  position:sticky;top:0;background:#fff;z-index:2;
}
.modal-title{font-size:15px;font-weight:700}
.modal-close{
  background:none;border:none;font-size:22px;line-height:1;
  color:var(--text3);cursor:pointer;padding:2px 6px;
  border-radius:4px;
}
.modal-close:hover{background:var(--bg);color:var(--text)}
.modal-body{padding:20px;display:flex;flex-direction:column;gap:14px}
.modal-ftr{
  display:flex;justify-content:flex-end;gap:8px;
  padding:14px 20px;border-top:1px solid var(--border);
  position:sticky;bottom:0;background:#fff;
}

/* ════════════════════════════════════════════════════════
   PAGER
════════════════════════════════════════════════════════ */
.pager{
  display:flex;align-items:center;justify-content:center;
  gap:10px;padding:12px 0;font-size:12px;color:var(--text2);
}
.pager button{
  font-size:12px;padding:5px 12px;border-radius:var(--radius-sm);
  border:1px solid var(--border2);background:#fff;color:var(--text);
}
.pager button:hover:not(:disabled){background:var(--bg)}
.pager button:disabled{opacity:.35;cursor:not-allowed}

/* ════════════════════════════════════════════════════════
   ALERT / TOAST
════════════════════════════════════════════════════════ */
.toast-wrap{
  position:fixed;bottom:20px;right:20px;
  display:flex;flex-direction:column;gap:8px;z-index:2000;
}
.toast{
  padding:11px 16px;border-radius:8px;font-size:13px;
  font-weight:500;color:#fff;box-shadow:0 4px 16px rgba(0,0,0,.2);
  animation:slideIn .2s ease;max-width:320px;
}
.toast.ok    {background:var(--ok)}
.toast.error {background:var(--danger)}
.toast.warn  {background:var(--warn)}
@keyframes slideIn{from{transform:translateX(40px);opacity:0}to{transform:translateX(0);opacity:1}}

/* ════════════════════════════════════════════════════════
   TABS (pills)
════════════════════════════════════════════════════════ */
.tab-pills{display:flex;gap:4px;flex-wrap:wrap;margin-bottom:14px}
.tab-pill{
  font-size:12px;padding:4px 12px;border-radius:999px;cursor:pointer;
  border:1px solid var(--border2);background:transparent;color:var(--text2);
}
.tab-pill.active{
  background:var(--brand-lt);color:var(--brand);
  border-color:var(--brand);font-weight:600;
}

/* ════════════════════════════════════════════════════════
   FILTROS
════════════════════════════════════════════════════════ */
.filter-bar{
  display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;
  margin-bottom:16px;
}
.filter-bar input,.filter-bar select{
  font-size:13px;padding:7px 10px;border-radius:var(--radius-sm);
  border:1px solid var(--border2);background:#fff;color:var(--text);
}
.filter-bar label{font-size:11px;font-weight:600;color:var(--text2);display:flex;flex-direction:column;gap:4px}

/* ════════════════════════════════════════════════════════
   CHART WRAPPER
════════════════════════════════════════════════════════ */
.chart-box{position:relative;width:100%}
.chart-hint{font-size:11px;color:var(--text3);text-align:center;margin-top:6px}

/* ════════════════════════════════════════════════════════
   SEMÁFORO LIST (mantenimiento / alertas)
════════════════════════════════════════════════════════ */
.sem-list{display:flex;flex-direction:column;gap:8px}
.sem-row{
  display:flex;align-items:center;gap:12px;
  padding:10px 14px;border-radius:8px;
  background:var(--bg);border:1px solid var(--border);
}
.sem-info{flex:1;min-width:0}
.sem-title{font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sem-sub{font-size:11px;color:var(--text3);margin-top:1px}
.progress-bar{height:6px;background:var(--border);border-radius:999px;overflow:hidden;margin-top:6px}
.progress-fill{height:100%;border-radius:999px;transition:width .4s}

/* ════════════════════════════════════════════════════════
   MISC
════════════════════════════════════════════════════════ */
.divider{height:1px;background:var(--border);margin:12px 0}
.empty-state{
  text-align:center;padding:40px 20px;color:var(--text3);font-size:13px;
}
.empty-state svg{margin-bottom:10px;opacity:.4}
.spin{animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.gap-8{gap:8px}.gap-12{gap:12px}.mt-12{margin-top:12px}.mb-12{margin-bottom:12px}
.flex{display:flex}.items-center{align-items:center}.justify-between{justify-content:space-between}
.font-bold{font-weight:700}.text-sm{font-size:12px}.text-xs{font-size:11px}.text-muted{color:var(--text3)}
.truncate{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
</style>
</head>
<body>

<!-- ══ TOPBAR ══════════════════════════════════════════════ -->
<header class="topbar">
  <div class="topbar-brand">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
      <rect x="1" y="3" width="15" height="13" rx="1"/>
      <path d="M16 8h4l3 5v4h-7V8z"/>
      <circle cx="5.5" cy="18.5" r="2.5"/>
      <circle cx="18.5" cy="18.5" r="2.5"/>
    </svg>
    <?= APP_NAME ?>
  </div>

  <div class="topbar-right">
    <?php if ($alertas_count > 0): ?>
    <a href="<?= rtrim(APP_BASE,'/') ?>/mantenimiento" class="badge badge-danger">
      <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
      <?= $alertas_count ?> alertas
    </a>
    <?php endif ?>
    <span class="topbar-date"><?= date('d M Y') ?></span>
  </div>
</header>

<!-- ══ NAVBAR ══════════════════════════════════════════════ -->
<nav class="navbar">

<?php
$navItems = [
  ['dashboard',    'Dashboard',   '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
  ['garita',       'Garita',      '<circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M4.93 4.93a10 10 0 0 0 0 14.14"/>'],
  ['compras',      'Combustible', '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>'],
  ['maquinaria',   'Maquinaria',  '<path d="M4 17l6-6 4 4 6-8"/><path d="M22 17H2"/>'],
  ['ge',           'Grupos E.',   '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>'],
  ['mantenimiento','Mantenim.',   '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>'],
  ['reportes',     'Reportes',    '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'],
];

// Prefijo de subcarpeta (ej: '/flota-maquinaria-filomena-100')
$base = rtrim(APP_BASE, '/');

foreach ($navItems as [$id, $label, $svgPath]):
  $active = ($viewName === $id) ? ' active' : '';
  $href   = $id === 'dashboard' ? ($base . '/') : ($base . '/' . $id);
?>
  <a href="<?= htmlspecialchars($href) ?>" class="nav-btn<?= $active ?>">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
      <?= $svgPath ?>
    </svg>
    <span class="nav-label"><?= $label ?></span>
  </a>
<?php endforeach ?>

</nav>

<!-- ══ TOAST CONTAINER ═════════════════════════════════════ -->
<div class="toast-wrap" id="toast-wrap"></div>

<!-- ══ HELPERS GLOBALES JS (deben ir ANTES del contenido) ══ -->
<!-- api(), toast(), fmt, CHARTS, PAL, etc. se definen aquí   -->
<!-- para que estén disponibles cuando DOMContentLoaded corra  -->
<script>
/* ════════════════════════════════════════════════════════════
   GLOBALS — disponibles en todas las vistas
   Usamos window.X en lugar de const para evitar errores de
   "already declared" si algún archivo antiguo los redeclara.
════════════════════════════════════════════════════════════ */

// ── Base de la subcarpeta (inyectada por PHP) ───────────────
window.APP_BASE = <?= json_encode(rtrim(APP_BASE, '/')) ?>;

// ── Paleta de colores para Chart.js ────────────────────────
window.PAL = ['#185FA5','#1D9E75','#D85A30','#534AB7','#854F0B','#993556','#2d6a4f','#0077b6'];

// ── Registro global de gráficos ─────────────────────────────
window.CHARTS = {};

// ── Formato de valores ──────────────────────────────────────
window.fmt = {
  sol : v => 'S/ ' + Number(v ?? 0).toLocaleString('es-PE', { minimumFractionDigits:2, maximumFractionDigits:2 }),
  num : (v, d=1) => Number(v ?? 0).toLocaleString('es-PE', { minimumFractionDigits:d, maximumFractionDigits:d }),
  pct : v => Math.round(v ?? 0) + '%',
  gll : v => Number(v ?? 0).toFixed(1) + ' gll',
};

window.fmtFecha = function(s) {
  if (!s) return '—';
  const [y, m, d] = String(s).slice(0, 10).split('-');
  const M = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
  return `${d} ${M[+m - 1]} ${y.slice(2)}`;
};

// ── API fetch helper ────────────────────────────────────────
window.api = async function(path, opts = {}) {
  const url = window.APP_BASE + path;
  const r   = await fetch(url, {
    headers: { 'Content-Type': 'application/json' },
    ...opts,
  });
  if (!r.ok) {
    const e = await r.json().catch(() => ({}));
    throw new Error(e.error || `HTTP ${r.status}`);
  }
  return r.json();
};

// ── DOM helpers ─────────────────────────────────────────────
window.tx = function(id, val) {
  const el = document.getElementById(id);
  if (el) el.textContent = val;
};

// Destruir un gráfico antes de redibujar
window.dch = function(id) {
  if (window.CHARTS[id]) { window.CHARTS[id].destroy(); delete window.CHARTS[id]; }
};

// ── Modales ─────────────────────────────────────────────────
window.abrirModal = function(id) {
  const el = document.getElementById(id);
  if (el) { el.classList.add('open'); document.body.style.overflow = 'hidden'; }
};
window.cerrarModal = function(id) {
  const el = document.getElementById(id);
  if (el) { el.classList.remove('open'); document.body.style.overflow = ''; }
};
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.overlay.open').forEach(el => {
      el.classList.remove('open');
      document.body.style.overflow = '';
    });
  }
});

// ── Toast ────────────────────────────────────────────────────
window.toast = function(msg, tipo, ms) {
  tipo = tipo || 'ok'; ms = ms || 3500;
  const wrap = document.getElementById('toast-wrap');
  if (!wrap) return;
  const div = document.createElement('div');
  div.className = 'toast ' + tipo;
  div.textContent = msg;
  wrap.appendChild(div);
  setTimeout(function(){ div.remove(); }, ms);
};

// ── Paginador genérico ──────────────────────────────────────
window.renderPager = function(containerId, page, total, cb) {
  const el = document.getElementById(containerId);
  if (!el) return;
  if (total <= 1) { el.innerHTML = ''; return; }
  el.innerHTML =
    '<button ' + (page===0?'disabled':'') + ' onclick="('+cb.toString()+')('+( page-1 )+')">← Anterior</button>' +
    '<span>Página '+(page+1)+' de '+total+'</span>' +
    '<button '+(page>=total-1?'disabled':'')+' onclick="('+cb.toString()+')('+(page+1)+')">Siguiente →</button>';
};
</script>

<!-- ══ CONTENIDO DE LA VISTA ═══════════════════════════════ -->
<main class="main">