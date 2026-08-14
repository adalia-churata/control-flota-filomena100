<?php
// importar_kardex.php — Reconstruir kardex desde la primera compra GE de mayo 2026
// IDs desde 1, saldos desde 0
// EJECUTAR UNA SOLA VEZ y luego eliminar

require_once __DIR__ . '/config/config.php';
$pdo = pdo();

// ── 1. Limpiar completamente ──────────────────────────────────
$pdo->exec('DELETE FROM kardex_combustible');
$pdo->exec('ALTER TABLE kardex_combustible AUTO_INCREMENT = 1');

// ── 2. Compras GE (Perkins/Cattini) desde mayo 2026 ──────────
// Notas de crédito (cantidad_gll < 0) → SALIDA
$compras = $pdo->query(
    "SELECT cc.fecha, cc.id_unidad, cc.cantidad_gll,
            cc.id_combustible, cc.tipo_comprobante, cc.nro_comprobante, u.placa
     FROM compra_combustible cc
     JOIN unidad u ON cc.id_unidad = u.id_unidad
     WHERE u.tipo_unidad       = 'GRUPO ELECTROGENO'
       AND u.placa            != 'MEBA'
       AND cc.tipo_combustible = 'PETROLEO'
       AND cc.fecha           >= '2026-05-01'
     ORDER BY cc.fecha ASC, cc.id_combustible ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// ── 3. Consumos GE desde mayo 2026 ───────────────────────────
$consumos = $pdo->query(
    "SELECT cge.fecha, cge.hora, cge.id_unidad,
            cge.galones_echados, cge.galones_consumidos,
            cge.horas_trabajadas, cge.id_combustible, u.placa
     FROM consumo_grupo_electrogeno cge
     JOIN unidad u ON cge.id_unidad = u.id_unidad
     WHERE u.placa   != 'MEBA'
       AND cge.fecha >= '2026-05-01'
       AND (cge.galones_echados > 0 OR cge.galones_consumidos > 0)
     ORDER BY cge.fecha ASC, cge.hora ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// ── 4. Combinar y ordenar cronológicamente ────────────────────
$movs = [];

foreach ($compras as $cp) {
    $gll  = (float)$cp['cantidad_gll'];
    $tipo = $gll >= 0 ? 'ENTRADA' : 'SALIDA';
    $movs[] = [
        'fecha'   => $cp['fecha'] . ' 00:00:00',
        'id_u'    => (int)$cp['id_unidad'],
        'tipo'    => $tipo,
        'galones' => abs($gll),
        'id_comb' => $cp['id_combustible'],
        'obs'     => 'Compra ' . ($cp['tipo_comprobante']??'') . ' ' . ($cp['nro_comprobante']??'') . ' · ' . $cp['placa'],
        'sort'    => $cp['fecha'] . '_a_' . str_pad($cp['id_combustible'], 8, '0', STR_PAD_LEFT),
    ];
}

foreach ($consumos as $cg) {
    $gll = (float)($cg['galones_echados'] ?? 0) > 0
         ? (float)$cg['galones_echados']
         : (float)($cg['galones_consumidos'] ?? 0);
    if ($gll <= 0) continue;
    $hora = substr($cg['hora'] ?? '00:00:00', 0, 8);
    $movs[] = [
        'fecha'   => $cg['fecha'] . ' ' . $hora,
        'id_u'    => (int)$cg['id_unidad'],
        'tipo'    => 'SALIDA',
        'galones' => $gll,
        'id_comb' => $cg['id_combustible'] ?? null,
        'obs'     => 'Consumo ' . $cg['placa'] . ' turno ' . $cg['fecha'] . ($cg['horas_trabajadas'] ? ' · ' . $cg['horas_trabajadas'] . 'h' : ''),
        'sort'    => $cg['fecha'] . ' ' . $hora . '_b',
    ];
}

usort($movs, function($a, $b) { return strcmp($a['sort'], $b['sort']); });

// ── 5. Insertar con saldos desde 0 ───────────────────────────
$ins = $pdo->prepare(
    'INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll)
     VALUES(?,?,?,?,?,?,?)'
);

$saldo = 0.0;
foreach ($movs as $mv) {
    $saldo += $mv['tipo'] === 'ENTRADA' ? $mv['galones'] : -$mv['galones'];
    $ins->execute([
        $mv['fecha'], $mv['id_u'], $mv['tipo'],
        round($mv['galones'], 2), $mv['id_comb'],
        $mv['obs'], round($saldo, 2),
    ]);
}

// ── 6. Resultado ──────────────────────────────────────────────
$total   = $pdo->query('SELECT COUNT(*) FROM kardex_combustible')->fetchColumn();
$primero = $pdo->query('SELECT * FROM kardex_combustible ORDER BY id_kardex ASC  LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$ultimo  = $pdo->query('SELECT * FROM kardex_combustible ORDER BY id_kardex DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);

// Mostrar primeros 20 para verificar
$preview = $pdo->query('SELECT * FROM kardex_combustible ORDER BY id_kardex ASC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Kardex OK</title>
<style>body{font-family:sans-serif;padding:30px;max-width:900px;margin:0 auto}
.ok{background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px;padding:16px;margin-bottom:16px}
.warn{background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:12px;margin-top:16px;font-size:13px}
table{border-collapse:collapse;width:100%;font-size:12px;margin-top:12px}
td,th{border:1px solid #ddd;padding:6px 10px}th{background:#f3f4f6;font-weight:600}
</style></head><body>
<h2>✅ Kardex reconstruido desde mayo 2026</h2>
<div class="ok">
  <p><strong>Compras insertadas:</strong> ' . count($compras) . ' &nbsp;|&nbsp;
     <strong>Consumos insertados:</strong> ' . count($consumos) . ' &nbsp;|&nbsp;
     <strong>Total:</strong> ' . $total . '</p>
  <p><strong>Primer registro:</strong> id=' . ($primero['id_kardex']??'—') . ' · ' . ($primero['fecha']??'—') . ' · ' . ($primero['tipo_movimiento']??'—') . ' · ' . ($primero['galones']??'—') . ' gll · saldo=' . ($primero['saldo_gll']??'—') . '</p>
  <p><strong>Último registro:</strong>  id=' . ($ultimo['id_kardex']??'—')  . ' · ' . ($ultimo['fecha']??'—')  . ' · ' . ($ultimo['tipo_movimiento']??'—')  . ' · ' . ($ultimo['galones']??'—')  . ' gll · saldo=' . ($ultimo['saldo_gll']??'—')  . '</p>
  <p><strong>Saldo final bidón:</strong> ' . round($saldo, 2) . ' gll</p>
</div>
<h3>Primeros 20 registros</h3>
<table>
<tr><th>id</th><th>fecha</th><th>unidad</th><th>tipo</th><th>galones</th><th>id_comb</th><th>observación</th><th>saldo</th></tr>';

foreach ($preview as $r) {
    echo '<tr>' .
        '<td>' . $r['id_kardex'] . '</td>' .
        '<td>' . $r['fecha'] . '</td>' .
        '<td>' . $r['id_unidad'] . '</td>' .
        '<td><strong>' . $r['tipo_movimiento'] . '</strong></td>' .
        '<td>' . $r['galones'] . '</td>' .
        '<td>' . ($r['id_combustible']??'—') . '</td>' .
        '<td style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' . htmlspecialchars($r['observacion']) . '</td>' .
        '<td><strong>' . $r['saldo_gll'] . '</strong></td>' .
    '</tr>';
}

echo '</table>
<div class="warn">⚠️ <strong>Elimina este archivo del servidor (importar_kardex.php) ahora.</strong></div>
</body></html>';