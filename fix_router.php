<?php
// fix_router.php — Parcha router.php en el servidor eliminando carga_equivalente
// EJECUTAR UNA SOLA VEZ y eliminar

$path = __DIR__ . '/api/router.php';
$c    = file_get_contents($path);

if (!$c) { die('No se pudo leer router.php'); }

$fixes = 0;

// Fix 1: INSERT retro_control_actividad con carga_equivalente
$old1 = "INSERT INTO retro_control_actividad(id_control_dia,id_actividad,observacion,hora_inicio,hora_fin,total_hora,carga_equivalente)VALUES(?,?,?,?,?,?,?)";
$new1 = "INSERT INTO retro_control_actividad(id_control_dia,id_actividad,observacion,hora_inicio,hora_fin,total_hora)VALUES(?,?,?,?,?,?)";
if (strpos($c, $old1) !== false) { $c = str_replace($old1, $new1, $c); $fixes++; }

// Fix 2: El parámetro extra $ceq en el execute
$old2 = '[$d[\'id_control_dia\'],$d[\'id_actividad\'],$d[\'observacion\']??null,$hi,$hf,$th,$ceq]';
$new2 = '[$d[\'id_control_dia\'],$d[\'id_actividad\'],$d[\'observacion\']??null,$hi,$hf,$th]';
if (strpos($c, $old2) !== false) { $c = str_replace($old2, $new2, $c); $fixes++; }

// Fix 3: respuesta con carga_equivalente
$old3 = "jout(['id_control_activ'=>(int)\$id,'total_hora'=>\$th,'carga_equivalente'=>\$ceq],201);";
$new3 = "jout(['id_control_activ'=>(int)\$id,'total_hora'=>\$th],201);";
if (strpos($c, $old3) !== false) { $c = str_replace($old3, $new3, $c); $fixes++; }

file_put_contents($path, $c);

// Verify
$check = file_get_contents($path);
$still_has = strpos($check, 'carga_equivalente') !== false;

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Fix aplicado</title>
<style>body{font-family:sans-serif;padding:40px;max-width:600px}
.ok{background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px;padding:16px;margin:12px 0}
.err{background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:16px;margin:12px 0}
.warn{background:#fef3c7;padding:12px;border-radius:8px;margin-top:16px;font-size:13px}
</style></head><body>
<h2>Fix router.php</h2>';

if (!$still_has) {
    echo '<div class="ok">✅ <strong>' . $fixes . ' reemplazos aplicados.</strong><br>
    carga_equivalente eliminado de router.php correctamente.</div>';
} else {
    echo '<div class="err">⚠️ Aún quedan referencias a carga_equivalente. Sube el router.php manualmente.</div>';
    // Show remaining lines
    foreach (explode("\n", $check) as $i => $line) {
        if (strpos($line, 'carga_equivalente') !== false) {
            echo '<p>Línea ' . ($i+1) . ': <code>' . htmlspecialchars(trim($line)) . '</code></p>';
        }
    }
}

echo '<div class="warn">⚠️ Elimina este archivo del servidor (fix_router.php) ahora.</div>
</body></html>';