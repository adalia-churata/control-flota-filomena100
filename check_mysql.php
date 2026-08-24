<?php
require_once __DIR__ . '/config/config.php';
$pdo = pdo();

// Check if retro_control_actividad is actually a VIEW
$r = $pdo->query("SELECT TABLE_TYPE FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA='filomena_100' AND TABLE_NAME='retro_control_actividad'")->fetch();
echo "Table type: " . ($r['TABLE_TYPE'] ?? 'NOT FOUND') . "\n";

// Check for any stored procedures
$procs = $pdo->query("SHOW PROCEDURE STATUS WHERE Db='filomena_100'")->fetchAll();
echo "Stored procedures: " . count($procs) . "\n";
foreach($procs as $p) echo "  - " . $p['Name'] . "\n";

// Try the exact INSERT that router.php does
try {
    $pdo->exec("INSERT INTO retro_control_actividad(id_control_dia,id_actividad,observacion,hora_inicio,hora_fin,total_hora) VALUES(999,1,NULL,'08:00:00','09:00:00',1.0)");
    echo "INSERT succeeded (then rolling back)\n";
    $pdo->exec("DELETE FROM retro_control_actividad WHERE id_control_dia=999");
} catch(Exception $e) {
    echo "INSERT ERROR: " . $e->getMessage() . "\n";
}