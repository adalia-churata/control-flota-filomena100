<?php
// importar_kardex.php — Reconstruir kardex desde cero partiendo de las tablas reales
// Lee compra_combustible y consumo_grupo_electrogeno desde mayo 2025
// IDs empiezan desde 1, saldos calculados en cadena
// EJECUTAR UNA SOLA VEZ y luego eliminar

require_once __DIR__ . '/config/config.php';
$pdo = pdo();

// ── 1. Limpiar completamente el kardex ───────────────────────
$pdo->exec('DELETE FROM kardex_combustible');
$pdo->exec('ALTER TABLE kardex_combustible AUTO_INCREMENT = 1');

// ── 2. Obtener ENTRADAS: compras para Perkins/Cattini desde mayo ──
// Notas de crédito tienen cantidad_gll negativa → se convierten en SALIDA
$compras = $pdo->query(
    "SELECT cc.fecha, cc.id_unidad, cc.cantidad_gll,
            cc.id_combustible, cc.tipo_comprobante, cc.nro_comprobante, u.placa
     FROM compra_combustible cc
     JOIN unidad u ON cc.id_unidad = u.id_unidad
     WHERE u.tipo_unidad      = 'GRUPO ELECTROGENO'
       AND u.placa           != 'MEBA'
       AND cc.tipo_combustible = 'PETROLEO'
       AND cc.fecha           >= '2025-05-01'
     ORDER BY cc.fecha ASC, cc.id_combustible ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// ── 3. Obtener SALIDAS: consumos GE desde mayo ────────────────
$consumos = $pdo->query(
    "SELECT cge.fecha, cge.hora, cge.id_unidad,
            cge.galones_echados, cge.galones_consumidos,
            cge.horas_trabajadas, cge.id_combustible, u.placa
     FROM consumo_grupo_electrogeno cge
     JOIN unidad u ON cge.id_unidad = u.id_unidad
     WHERE u.placa  != 'MEBA'
       AND cge.fecha >= '2025-05-01'
       AND (cge.galones_echados > 0 OR cge.galones_consumidos > 0)
     ORDER BY cge.fecha ASC, cge.hora ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// ── 4. Combinar y ordenar cronológicamente ────────────────────
$movimientos = [];

foreach ($compras as $cp) {
    $gll  = (float)$cp['cantidad_gll'];
    $tipo = $gll >= 0 ? 'ENTRADA' : 'SALIDA';
    $movimientos[] = [
        'fecha'          => $cp['fecha'] . ' 00:00:00',
        'id_unidad'      => (int)$cp['id_unidad'],
        'tipo_movimiento'=> $tipo,
        'galones'        => abs($gll),
        'id_combustible' => $cp['id_combustible'],
        'observacion'    => 'Compra ' . ($cp['tipo_comprobante']??'') . ' ' . ($cp['nro_comprobante']??'') . ' · ' . $cp['placa'],
        'sort_key'       => $cp['fecha'] . '_c_' . str_pad($cp['id_combustible'], 6, '0', STR_PAD_LEFT),
    ];
}

foreach ($consumos as $cg) {
    $gll = (float)($cg['galones_echados'] ?? 0) > 0
         ? (float)$cg['galones_echados']
         : (float)($cg['galones_consumidos'] ?? 0);
    if ($gll <= 0) continue;
    $hora = substr($cg['hora'] ?? '00:00:00', 0, 8);
    $movimientos[] = [
        'fecha'          => $cg['fecha'] . ' ' . $hora,
        'id_unidad'      => (int)$cg['id_unidad'],
        'tipo_movimiento'=> 'SALIDA',
        'galones'        => $gll,
        'id_combustible' => $cg['id_combustible'] ?? null,
        'observacion'    => 'Consumo ' . $cg['placa'] . ' turno ' . $cg['fecha'] . ($cg['horas_trabajadas'] ? ' · ' . $cg['horas_trabajadas'] . 'h' : ''),
        'sort_key'       => $cg['fecha'] . ' ' . $hora . '_s',
    ];
}

// Ordenar cronológicamente (compras del mismo día antes que consumos)
usort($movimientos, function($a, $b) {
    return strcmp($a['sort_key'], $b['sort_key']);
});

// ── 5. Insertar con saldos calculados en cadena desde 0 ───────
$ins = $pdo->prepare(
    'INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll)
     VALUES(?,?,?,?,?,?,?)'
);

$saldo = 0.0;
$n_ins = 0;
foreach ($movimientos as $mv) {
    $saldo += $mv['tipo_movimiento'] === 'ENTRADA' ? $mv['galones'] : -$mv['galones'];
    $ins->execute([
        $mv['fecha'],
        $mv['id_unidad'],
        $mv['tipo_movimiento'],
        round($mv['galones'], 2),
        $mv['id_combustible'],
        $mv['observacion'],
        round($saldo, 2),
    ]);
    $n_ins++;
}

// ── 6. Resultados ─────────────────────────────────────────────
$total    = (int)$pdo->query('SELECT COUNT(*) FROM kardex_combustible')->fetchColumn();
$primero  = $pdo->query('SELECT id_kardex,fecha,tipo_movimiento,galones,saldo_gll FROM kardex_combustible ORDER BY id_kardex ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$ultimo   = $pdo->query('SELECT id_kardex,fecha,tipo_movimiento,galones,saldo_gll FROM kardex_combustible ORDER BY id_kardex DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Kardex reconstruido</title>
<style>
body{font-family:sans-serif;padding:40px;max-width:700px;margin:0 auto}
.ok{background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px;padding:20px;margin-bottom:16px}
table{border-collapse:collapse;width:100%;font-size:13px;margin-top:12px}
td,th{border:1px solid #ddd;padding:8px 12px;text-align:left}
th{background:#f3f4f6;font-weight:600}
.warn{background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:12px;margin-top:16px;font-size:13px}
</style></head><body>
<h2>✅ Kardex reconstruido desde cero</h2>
<div class="ok">
  <p><strong>Compras insertadas (entradas):</strong> ' . count($compras) . '</p>
  <p><strong>Consumos insertados (salidas):</strong> ' . count($consumos) . '</p>
  <p><strong>Total movimientos:</strong> ' . $total . '</p>
  <p><strong>Saldo final:</strong> ' . round($saldo, 2) . ' gll</p>
</div>
<table>
  <tr><th></th><th>id_kardex</th><th>fecha</th><th>tipo</th><th>galones</th><th>saldo_gll</th></tr>
  <tr>
    <td><strong>Primero</strong></td>
    <td>' . ($primero['id_kardex']??'—') . '</td>
    <td>' . ($primero['fecha']??'—') . '</td>
    <td>' . ($primero['tipo_movimiento']??'—') . '</td>
    <td>' . ($primero['galones']??'—') . '</td>
    <td>' . ($primero['saldo_gll']??'—') . '</td>
  </tr>
  <tr>
    <td><strong>Último</strong></td>
    <td>' . ($ultimo['id_kardex']??'—') . '</td>
    <td>' . ($ultimo['fecha']??'—') . '</td>
    <td>' . ($ultimo['tipo_movimiento']??'—') . '</td>
    <td>' . ($ultimo['galones']??'—') . '</td>
    <td>' . ($ultimo['saldo_gll']??'—') . '</td>
  </tr>
</table>
<div class="warn">
  ⚠️ <strong>Elimina este archivo del servidor (importar_kardex.php) ahora que terminó.</strong>
</div>
</body></html>';