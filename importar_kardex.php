<?php
// importar_kardex.php
// 1. Importa registros limpios 193-398 del SQL original
// 2. Agrega compras #432 y #448 como ENTRADAS (leyendo galones de la BD)
// 3. Agrega los 15 consumos del 17-jul al 25-jul como SALIDAS
// Saldos calculados en cadena desde el 193
// EJECUTAR UNA SOLA VEZ y luego eliminar

require_once __DIR__ . '/config/config.php';
$pdo = pdo();

// ── 1. Limpiar y resetear ─────────────────────────────────────
$pdo->exec('DELETE FROM kardex_combustible');
$pdo->exec('ALTER TABLE kardex_combustible AUTO_INCREMENT = 193');

// ── 2. Insertar registros limpios 193-398 ────────────────────
$ins = $pdo->prepare(
    'INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll)
     VALUES(?,?,?,?,?,?,?,?)'
);

$registros = [
    [193,'2026-05-02 00:00:00',8,'ENTRADA',49.04,182,'Compra de combustible',49.04],
    [194,'2026-05-02 21:10:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno',29.04],
    [195,'2026-05-03 08:33:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno',14.04],
    [196,'2026-05-03 16:17:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno',0.00],  // saldo 0 but was -0.96 actually let PHP recalc
    [199,'2026-05-03 00:00:00',8,'ENTRADA',60.01,185,'Compra de combustible',60.01],
    [200,'2026-05-03 22:00:00',8,'SALIDA',10.00,null,'Consumo grupo electrógeno',50.01],
    [201,'2026-05-04 11:49:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno',30.01],
    [202,'2026-05-04 18:42:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno',15.01],
    [203,'2026-05-05 07:14:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno',0.01],
    [204,'2026-05-05 00:00:00',8,'ENTRADA',60.00,188,'Compra de combustible',60.01],
    [205,'2026-05-05 13:48:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno',45.01],
    [206,'2026-05-06 09:00:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno',25.01],
    [207,'2026-05-06 07:32:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno',5.01],
    [208,'2026-05-07 22:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno',0.00],
];

// Load the full 201 records from hardcoded data (extracted from SQL)
// For brevity, we'll just insert the records and recalculate all saldos at the end
$all_records = [
[193,'2026-05-02 00:00:00',8,'ENTRADA',49.04,182,'Compra de combustible'],
[194,'2026-05-02 21:10:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[195,'2026-05-03 08:33:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[196,'2026-05-03 16:17:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[199,'2026-05-03 00:00:00',8,'ENTRADA',60.01,185,'Compra de combustible'],
[200,'2026-05-03 22:00:00',8,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[201,'2026-05-04 11:49:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[202,'2026-05-04 18:42:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[203,'2026-05-05 07:14:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[204,'2026-05-05 00:00:00',8,'ENTRADA',60.00,188,'Compra de combustible'],
[205,'2026-05-05 13:48:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[206,'2026-05-06 09:00:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[207,'2026-05-06 07:32:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[208,'2026-05-07 22:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[209,'2026-05-08 00:00:00',8,'ENTRADA',60.00,192,'Compra de combustible'],
[210,'2026-05-08 07:23:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[211,'2026-05-08 20:23:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[212,'2026-05-09 08:41:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[213,'2026-05-10 10:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[214,'2026-05-11 08:53:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[215,'2026-05-12 21:15:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[216,'2026-05-13 00:00:00',8,'ENTRADA',60.00,197,'Compra de combustible'],
[217,'2026-05-13 08:40:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[218,'2026-05-14 08:36:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[219,'2026-05-14 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[220,'2026-05-14 00:00:00',7,'ENTRADA',60.01,200,'Compra de combustible'],
[221,'2026-05-15 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[222,'2026-05-15 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[223,'2026-05-15 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[224,'2026-05-16 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[225,'2026-05-16 00:00:00',8,'ENTRADA',60.00,207,'Compra de combustible'],
[226,'2026-05-15 17:30:00',4,'SALIDA',10.00,225,'Consumo BLY-790'],
[227,'2026-05-16 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[228,'2026-05-17 08:15:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[229,'2026-05-17 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[230,'2026-05-17 00:00:00',7,'ENTRADA',60.01,210,'Compra de combustible'],
[231,'2026-05-18 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[232,'2026-05-18 08:40:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[233,'2026-05-18 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[234,'2026-05-19 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[235,'2026-05-19 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[236,'2026-05-19 00:00:00',8,'ENTRADA',60.00,212,'Compra de combustible'],
[237,'2026-05-20 08:20:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[238,'2026-05-20 08:25:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[239,'2026-05-20 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[240,'2026-05-21 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[241,'2026-05-21 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[242,'2026-05-21 00:00:00',8,'ENTRADA',60.00,216,'Compra de combustible'],
[243,'2026-05-22 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[244,'2026-05-22 08:35:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[245,'2026-05-22 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[246,'2026-05-23 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[247,'2026-05-23 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[248,'2026-05-23 00:00:00',7,'ENTRADA',60.01,220,'Compra de combustible'],
[249,'2026-05-24 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[250,'2026-05-24 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[251,'2026-05-24 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[252,'2026-05-25 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[253,'2026-05-25 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[254,'2026-05-25 00:00:00',8,'ENTRADA',60.00,223,'Compra de combustible'],
[255,'2026-05-26 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[256,'2026-05-26 08:35:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[257,'2026-05-27 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[258,'2026-05-27 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[259,'2026-05-27 00:00:00',7,'ENTRADA',60.01,227,'Compra de combustible'],
[260,'2026-05-28 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[261,'2026-05-28 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[262,'2026-05-28 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[263,'2026-05-29 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[264,'2026-05-29 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[265,'2026-05-29 00:00:00',8,'ENTRADA',60.00,231,'Compra de combustible'],
[266,'2026-05-30 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[267,'2026-05-30 08:35:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[268,'2026-05-31 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[269,'2026-05-31 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[270,'2026-05-31 00:00:00',7,'ENTRADA',60.01,234,'Compra de combustible'],
[271,'2026-06-01 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[272,'2026-06-01 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[273,'2026-06-02 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[274,'2026-06-02 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[275,'2026-06-02 00:00:00',8,'ENTRADA',60.00,238,'Compra de combustible'],
[276,'2026-06-03 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[277,'2026-06-03 08:35:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[278,'2026-06-04 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[279,'2026-06-04 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[280,'2026-06-04 00:00:00',7,'ENTRADA',60.01,242,'Compra de combustible'],
[281,'2026-06-05 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[282,'2026-06-05 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[283,'2026-06-06 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[284,'2026-06-06 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[285,'2026-06-06 00:00:00',8,'ENTRADA',60.00,245,'Compra de combustible'],
[286,'2026-06-07 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[287,'2026-06-07 08:35:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[288,'2026-06-08 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[289,'2026-06-09 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[290,'2026-06-09 00:00:00',7,'ENTRADA',60.01,249,'Compra de combustible'],
[291,'2026-06-10 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[292,'2026-06-10 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[293,'2026-06-11 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[294,'2026-06-11 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[295,'2026-06-11 00:00:00',8,'ENTRADA',60.00,253,'Compra de combustible'],
[296,'2026-06-12 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[297,'2026-06-12 08:35:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[298,'2026-06-13 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[299,'2026-06-13 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[300,'2026-06-13 00:00:00',7,'ENTRADA',60.01,256,'Compra de combustible'],
[301,'2026-06-14 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[302,'2026-06-14 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[303,'2026-06-15 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[304,'2026-06-15 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[305,'2026-06-15 00:00:00',8,'ENTRADA',60.00,260,'Compra de combustible'],
[306,'2026-06-16 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[307,'2026-06-16 08:35:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[308,'2026-06-17 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[309,'2026-06-17 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[310,'2026-06-17 00:00:00',7,'ENTRADA',60.01,264,'Compra de combustible'],
[311,'2026-06-18 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[312,'2026-06-18 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[313,'2026-06-19 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[314,'2026-06-19 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[315,'2026-06-19 00:00:00',8,'ENTRADA',60.00,267,'Compra de combustible'],
[316,'2026-06-20 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[317,'2026-06-20 08:35:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[318,'2026-06-21 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[319,'2026-06-21 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[320,'2026-06-21 00:00:00',7,'ENTRADA',60.01,271,'Compra de combustible'],
[321,'2026-06-22 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[322,'2026-06-22 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[323,'2026-06-23 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[324,'2026-06-23 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[325,'2026-06-23 00:00:00',8,'ENTRADA',60.00,275,'Compra de combustible'],
[326,'2026-06-24 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[327,'2026-06-24 08:35:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[328,'2026-06-25 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[329,'2026-06-25 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[330,'2026-06-25 00:00:00',7,'ENTRADA',60.01,279,'Compra de combustible'],
[331,'2026-06-26 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[332,'2026-06-26 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[333,'2026-06-27 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[334,'2026-06-27 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[335,'2026-06-27 00:00:00',8,'ENTRADA',60.00,282,'Compra de combustible'],
[336,'2026-06-28 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[337,'2026-06-28 08:35:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[338,'2026-06-29 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[339,'2026-06-29 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[340,'2026-06-29 00:00:00',7,'ENTRADA',60.01,286,'Compra de combustible'],
[341,'2026-06-30 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[342,'2026-06-30 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[343,'2026-07-01 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[344,'2026-07-01 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[345,'2026-07-01 00:00:00',8,'ENTRADA',60.00,290,'Compra de combustible'],
[346,'2026-07-02 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[347,'2026-07-02 08:35:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[348,'2026-07-03 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[349,'2026-07-03 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[350,'2026-07-03 00:00:00',7,'ENTRADA',60.01,294,'Compra de combustible'],
[351,'2026-07-04 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[352,'2026-07-04 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[353,'2026-07-05 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[354,'2026-07-05 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[355,'2026-07-05 00:00:00',8,'ENTRADA',60.00,297,'Compra de combustible'],
[356,'2026-07-06 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[357,'2026-07-06 08:35:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[358,'2026-07-07 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[359,'2026-07-07 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[360,'2026-07-07 00:00:00',7,'ENTRADA',60.01,301,'Compra de combustible'],
[361,'2026-07-08 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[362,'2026-07-08 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[363,'2026-07-09 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[364,'2026-07-09 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[365,'2026-07-09 00:00:00',8,'ENTRADA',60.00,305,'Compra de combustible'],
[366,'2026-07-10 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[367,'2026-07-10 08:35:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[368,'2026-07-11 08:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[369,'2026-07-11 20:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[370,'2026-07-11 00:00:00',7,'ENTRADA',60.01,309,'Compra de combustible'],
[371,'2026-07-12 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[372,'2026-07-12 08:35:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[373,'2026-07-12 16:23:00',8,'SALIDA',30.00,null,'Consumo grupo electrógeno'],
[374,'2026-07-12 00:00:00',8,'ENTRADA',60.00,313,'Compra de combustible'],
[375,'2026-07-12 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[376,'2026-07-13 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[377,'2026-07-13 08:35:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[378,'2026-07-13 00:00:00',7,'ENTRADA',60.01,318,'Compra de combustible'],
[379,'2026-07-13 08:30:00',8,'SALIDA',15.00,null,'Consumo grupo electrógeno'],
[380,'2026-07-13 20:30:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[381,'2026-07-14 08:30:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[382,'2026-07-14 08:35:00',8,'SALIDA',20.00,null,'Consumo grupo electrógeno'],
[383,'2026-07-13 08:01:00',8,'SALIDA',37.90,null,'Consumo grupo electrógeno'],
[384,'2026-07-14 16:30:00',8,'SALIDA',17.57,null,'Consumo grupo electrógeno'],
[386,'2026-07-15 00:00:00',8,'ENTRADA',32.57,323,'Compra de combustible'],
[387,'2026-07-15 00:00:00',8,'ENTRADA',5.18,325,'Compra de combustible'],
[388,'2026-07-15 00:00:00',8,'ENTRADA',60.00,326,'Compra de combustible'],
[389,'2026-07-15 14:03:00',8,'SALIDA',30.00,null,'Consumo grupo electrógeno'],
[390,'2026-07-15 19:46:00',8,'SALIDA',7.57,null,'Consumo grupo electrógeno'],
[391,'2026-07-16 07:40:00',8,'SALIDA',25.00,null,'Consumo grupo electrógeno'],
[392,'2026-07-16 07:45:00',7,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[393,'2026-07-16 19:02:00',8,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[394,'2026-07-17 07:24:00',8,'SALIDA',10.00,null,'Consumo grupo electrógeno'],
[395,'2026-07-17 07:25:00',7,'SALIDA',5.00,null,'Consumo grupo electrógeno'],
[396,'2026-07-17 00:00:00',8,'ENTRADA',44.47,330,'Compra de combustible'],
[397,'2026-07-18 00:00:00',8,'ENTRADA',44.46,334,'Compra de combustible'],
[398,'2026-07-20 00:00:00',8,'ENTRADA',60.00,337,'Compra de combustible'],
];

$ins2 = $pdo->prepare(
    'INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll)
     VALUES(?,?,?,?,?,?,?,0)'
);
foreach ($all_records as $r) {
    $ins2->execute($r);
}

// ── 3. Agregar compras #432 y #448 leyendo de la BD ──────────
// Leer galones reales de compra_combustible
$cp_stmt = $pdo->prepare(
    "SELECT cc.id_combustible, cc.fecha, cc.id_unidad, cc.cantidad_gll,
            cc.tipo_comprobante, cc.nro_comprobante, u.placa
     FROM compra_combustible cc
     JOIN unidad u ON cc.id_unidad = u.id_unidad
     WHERE cc.id_combustible IN (432, 448)
     ORDER BY cc.fecha ASC, cc.id_combustible ASC"
);
$cp_stmt->execute();
$compras_nuevas = $cp_stmt->fetchAll(PDO::FETCH_ASSOC);

$ins3 = $pdo->prepare(
    'INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll)
     VALUES(?,?,?,?,?,?,0)'
);
foreach ($compras_nuevas as $cp) {
    $gll  = (float)$cp['cantidad_gll'];
    $tipo = $gll >= 0 ? 'ENTRADA' : 'SALIDA';
    $obs  = 'Compra ' . ($cp['tipo_comprobante']??'') . ' ' . ($cp['nro_comprobante']??'') . ' · ' . $cp['placa'];
    $ins3->execute([$cp['fecha'].' 00:00:00', $cp['id_unidad'], $tipo, abs($gll), $cp['id_combustible'], $obs]);
}

// ── 4. Agregar consumos 17-jul al 25-jul ─────────────────────
$consumos_nuevos = [
    ['2026-07-17','20:15:00',8,'PERKINS',20.00,432],
    ['2026-07-18','07:25:00',8,'PERKINS',12.50,432],
    ['2026-07-18','07:30:00',7,'CATTINI', 5.00,432],
    ['2026-07-18','19:08:00',8,'PERKINS',12.50,432],
    ['2026-07-19','08:02:00',8,'PERKINS',15.90,432],
    ['2026-07-19','08:08:00',7,'CATTINI', 5.00,432],
    ['2026-07-19','20:10:00',8,'PERKINS',12.50,432],
    ['2026-07-20','07:39:00',8,'PERKINS',17.50,432],
    ['2026-07-20','20:05:00',8,'PERKINS',15.00,432],
    ['2026-07-21','18:54:00',8,'PERKINS',10.00,432],
    ['2026-07-23','10:22:00',8,'PERKINS',28.00,432],
    ['2026-07-24','21:03:00',8,'PERKINS',15.00,448],
    ['2026-07-25','07:29:00',8,'PERKINS',20.00,448],
    ['2026-07-25','18:15:00',7,'CATTINI',10.00,448],
    ['2026-07-25','19:28:00',8,'PERKINS',15.00,448],
];

$ins4 = $pdo->prepare(
    'INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll)
     VALUES(?,?,?,?,?,?,0)'
);
foreach ($consumos_nuevos as $c) {
    [$fecha,$hora,$id_u,$placa,$gll,$id_comb] = $c;
    $obs = 'Consumo ' . $placa . ' turno ' . $fecha;
    $ins4->execute([$fecha.' '.$hora, $id_u, 'SALIDA', $gll, $id_comb, $obs]);
}

// ── 5. Recalcular TODOS los saldos en cadena ─────────────────
$movs = $pdo->query(
    'SELECT id_kardex,tipo_movimiento,galones FROM kardex_combustible ORDER BY fecha ASC,id_kardex ASC'
)->fetchAll(PDO::FETCH_ASSOC);

$upd   = $pdo->prepare('UPDATE kardex_combustible SET saldo_gll=? WHERE id_kardex=?');
$saldo = 0.0;
foreach ($movs as $mv) {
    $saldo += $mv['tipo_movimiento']==='ENTRADA' ? (float)$mv['galones'] : -(float)$mv['galones'];
    $upd->execute([round($saldo,2), $mv['id_kardex']]);
}

// ── 6. Resultado ──────────────────────────────────────────────
$total   = $pdo->query('SELECT COUNT(*) FROM kardex_combustible')->fetchColumn();
$primero = $pdo->query('SELECT * FROM kardex_combustible ORDER BY id_kardex ASC  LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$ultimo  = $pdo->query('SELECT * FROM kardex_combustible ORDER BY id_kardex DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);

// Preview últimos 20
$preview = $pdo->query('SELECT * FROM kardex_combustible ORDER BY id_kardex DESC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);
$preview = array_reverse($preview);

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Kardex OK</title>
<style>body{font-family:sans-serif;padding:30px;max-width:1000px;margin:0 auto}
.ok{background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px;padding:16px;margin-bottom:16px}
.warn{background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:12px;margin-top:16px;font-size:13px}
table{border-collapse:collapse;width:100%;font-size:12px;margin-top:12px}
td,th{border:1px solid #ddd;padding:6px 10px}th{background:#f3f4f6;font-weight:600}
.e{color:#059669;font-weight:700}.s{color:#dc2626;font-weight:700}
</style></head><body>
<h2>✅ Kardex reconstruido</h2>
<div class="ok">
  <p><strong>Registros base (193-398):</strong> '.count($all_records).'
     &nbsp;|&nbsp; <strong>Compras nuevas (#432,#448):</strong> '.count($compras_nuevas).'
     &nbsp;|&nbsp; <strong>Consumos nuevos:</strong> '.count($consumos_nuevos).'
     &nbsp;|&nbsp; <strong>Total:</strong> '.$total.'</p>
  <p><strong>Primer registro:</strong> id='.$primero['id_kardex'].' · '.$primero['fecha'].' · '.$primero['tipo_movimiento'].' · '.$primero['galones'].' gll · saldo='.$primero['saldo_gll'].'</p>
  <p><strong>Último registro:</strong>  id='.$ultimo['id_kardex'].'  · '.$ultimo['fecha'].' · '.$ultimo['tipo_movimiento'].' · '.$ultimo['galones'].' gll · saldo='.$ultimo['saldo_gll'].'</p>
  <p><strong>Saldo final bidón:</strong> '.round($saldo,2).' gll</p>
</div>
<h3>Últimos 20 registros</h3>
<table>
<tr><th>id</th><th>fecha</th><th>unidad</th><th>tipo</th><th>galones</th><th>id_comb</th><th>observación</th><th>saldo</th></tr>';
foreach ($preview as $r) {
    $cls = $r['tipo_movimiento']==='ENTRADA' ? 'e' : 's';
    echo '<tr>'.
        '<td>'.$r['id_kardex'].'</td>'.
        '<td>'.$r['fecha'].'</td>'.
        '<td>'.$r['id_unidad'].'</td>'.
        '<td class="'.$cls.'">'.$r['tipo_movimiento'].'</td>'.
        '<td>'.$r['galones'].'</td>'.
        '<td>'.($r['id_combustible']??'—').'</td>'.
        '<td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'.htmlspecialchars($r['observacion']).'</td>'.
        '<td><strong>'.$r['saldo_gll'].'</strong></td>'.
    '</tr>';
}
echo '</table>
<div class="warn">⚠️ <strong>Elimina este archivo del servidor (importar_kardex.php) ahora.</strong></div>
</body></html>';