<?php
error_reporting(E_ALL);
ini_set('display_errors','1');
require_once __DIR__ . '/config/config.php';
$pdo = pdo();

$pdo->exec('DELETE FROM kardex_combustible');
$pdo->exec('ALTER TABLE kardex_combustible AUTO_INCREMENT = 193');

$sqls = [
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(193,'2026-05-02 00:00:00',8,'ENTRADA',49.04,182,'Compra de combustible',49.04)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(194,'2026-05-02 21:10:00',8,'SALIDA',20.00,182,'Consumo grupo electrógeno',29.04)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(195,'2026-05-03 08:33:00',8,'SALIDA',15.00,182,'Consumo grupo electrógeno',14.04)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(196,'2026-05-03 16:17:00',8,'SALIDA',15.00,182,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(199,'2026-05-03 00:00:00',8,'ENTRADA',60.01,185,'Compra de combustible',60.01)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(200,'2026-05-03 22:00:00',8,'SALIDA',10.00,185,'Consumo grupo electrógeno',50.01)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(201,'2026-05-04 11:49:00',8,'SALIDA',20.00,185,'Consumo grupo electrógeno',30.01)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(202,'2026-05-04 18:42:00',8,'SALIDA',15.00,185,'Consumo grupo electrógeno',15.01)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(203,'2026-05-05 07:14:00',8,'SALIDA',15.00,185,'Consumo grupo electrógeno',0.01)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(204,'2026-05-05 00:00:00',8,'ENTRADA',60.00,194,'Compra de combustible',60.01)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(205,'2026-05-05 13:48:00',8,'SALIDA',15.00,194,'Consumo grupo electrógeno',45.01)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(206,'2026-05-06 09:00:00',8,'SALIDA',20.00,194,'Consumo grupo electrógeno',25.01)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(207,'2026-05-06 07:32:00',8,'SALIDA',20.00,194,'Consumo grupo electrógeno',5.01)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(208,'2026-05-07 22:30:00',8,'SALIDA',20.00,194,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(209,'2026-05-08 00:00:00',8,'ENTRADA',60.00,204,'Compra de combustible',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(210,'2026-05-08 11:40:00',8,'SALIDA',12.50,204,'Consumo grupo electrógeno',47.50)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(211,'2026-05-08 15:46:00',8,'SALIDA',15.00,204,'Consumo grupo electrógeno',32.50)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(212,'2026-05-08 20:02:00',8,'SALIDA',10.00,204,'Consumo grupo electrógeno',22.50)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(213,'2026-05-08 22:15:00',8,'SALIDA',15.00,204,'Consumo grupo electrógeno',7.50)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(214,'2026-05-09 00:00:00',8,'ENTRADA',60.01,211,'Compra de combustible',67.51)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(215,'2026-05-09 15:19:00',8,'SALIDA',5.00,211,'Consumo grupo electrógeno',62.51)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(216,'2026-05-09 18:12:00',8,'SALIDA',15.00,211,'Consumo grupo electrógeno',47.51)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(217,'2026-05-10 11:25:00',8,'SALIDA',20.00,211,'Consumo grupo electrógeno',27.51)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(218,'2026-05-11 08:24:00',8,'SALIDA',20.00,211,'Consumo grupo electrógeno',7.51)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(219,'2026-05-11 00:00:00',8,'ENTRADA',60.01,217,'Compra de combustible',67.52)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(220,'2026-05-11 18:33:00',8,'SALIDA',10.00,217,'Consumo grupo electrógeno',57.52)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(221,'2026-05-12 14:00:00',8,'SALIDA',20.00,217,'Consumo grupo electrógeno',37.52)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(222,'2026-05-13 14:00:00',8,'SALIDA',20.00,217,'Consumo grupo electrógeno',17.52)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(223,'2026-05-14 08:56:00',8,'SALIDA',20.00,217,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(224,'2026-05-15 00:00:00',8,'ENTRADA',42.91,225,'Compra de combustible',42.91)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(225,'2026-05-15 12:20:00',8,'SALIDA',25.00,225,'Consumo grupo electrógeno',17.91)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(226,'2026-05-15 17:30:00',4,'SALIDA',10.00,225,'Consumo BLY-790',7.91)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(227,'2026-05-16 00:00:00',8,'ENTRADA',52.07,229,'Compra de combustible',69.98)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(228,'2026-05-16 17:10:00',8,'SALIDA',20.00,229,'Consumo grupo electrógeno',49.98)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(229,'2026-05-17 13:46:00',8,'SALIDA',20.00,229,'Consumo grupo electrógeno',29.98)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(230,'2026-05-18 07:50:00',8,'SALIDA',15.00,229,'Consumo grupo electrógeno',14.98)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(231,'2026-05-18 00:00:00',8,'ENTRADA',60.00,233,'Compra de combustible',74.98)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(232,'2026-05-18 18:37:00',8,'SALIDA',15.00,233,'Consumo grupo electrógeno',59.98)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(233,'2026-05-19 14:56:00',8,'SALIDA',15.00,233,'Consumo grupo electrógeno',44.98)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(234,'2026-05-20 09:52:00',8,'SALIDA',20.00,233,'Consumo grupo electrógeno',24.98)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(235,'2026-05-20 00:00:00',8,'ENTRADA',16.78,239,'Compra de combustible',41.76)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(236,'2026-05-21 08:43:00',8,'SALIDA',25.00,239,'Consumo grupo electrógeno',16.76)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(237,'2026-05-21 00:00:00',8,'ENTRADA',60.00,241,'Compra de combustible',76.76)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(238,'2026-05-21 20:28:00',8,'SALIDA',15.00,241,'Consumo grupo electrógeno',61.76)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(239,'2026-05-22 01:30:00',8,'SALIDA',15.00,241,'Consumo grupo electrógeno',46.76)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(240,'2026-05-22 11:17:00',8,'SALIDA',20.00,241,'Consumo grupo electrógeno',26.76)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(241,'2026-05-17 19:02:00',8,'SALIDA',10.00,241,'Consumo grupo electrógeno',16.76)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(242,'2026-05-22 18:57:00',8,'SALIDA',10.00,241,'Consumo grupo electrógeno',6.76)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(243,'2026-05-23 13:20:00',8,'SALIDA',5.00,241,'Consumo grupo electrógeno',1.76)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(244,'2026-05-23 00:00:00',8,'ENTRADA',60.00,247,'Compra de combustible',61.76)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(245,'2026-05-23 19:13:00',8,'SALIDA',25.00,247,'Consumo grupo electrógeno',36.76)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(246,'2026-05-24 13:31:00',8,'SALIDA',17.50,247,'Consumo grupo electrógeno',19.26)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(247,'2026-05-24 18:57:00',8,'SALIDA',10.00,247,'Consumo grupo electrógeno',9.26)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(248,'2026-05-24 00:00:00',8,'ENTRADA',60.00,249,'Compra de combustible',69.26)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(249,'2026-05-25 07:03:00',8,'SALIDA',20.00,249,'Consumo grupo electrógeno',49.26)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(250,'2026-05-25 15:28:00',8,'SALIDA',20.00,249,'Consumo grupo electrógeno',29.26)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(252,'2026-05-25 18:38:00',8,'SALIDA',5.00,249,'Consumo grupo electrogeno',24.26)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(253,'2026-05-26 11:37:00',8,'SALIDA',20.00,249,'Consumo grupo electrógeno',4.26)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(254,'2026-05-26 18:49:00',8,'SALIDA',7.50,249,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(255,'2026-05-27 00:00:00',8,'ENTRADA',60.00,258,'Compra de combustible',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(256,'2026-05-27 19:34:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',40.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(257,'2026-05-28 07:43:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',20.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(258,'2026-05-28 18:26:00',8,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',10.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(259,'2026-05-30 00:00:00',8,'ENTRADA',60.02,263,'Compra de combustible',70.02)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(260,'2026-05-29 16:40:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',55.02)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(261,'2026-05-30 09:50:00',8,'SALIDA',27.50,NULL,'Consumo grupo electrógeno',27.52)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(262,'2026-05-31 19:20:00',8,'SALIDA',25.00,NULL,'Consumo grupo electrógeno',2.52)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(263,'2026-06-01 00:00:00',8,'ENTRADA',59.74,268,'Compra de combustible',62.26)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(264,'2026-06-01 11:30:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',42.26)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(265,'2026-06-01 19:32:00',8,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',32.26)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(266,'2026-06-02 06:57:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',12.26)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(267,'2026-06-03 19:15:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(268,'2026-06-04 00:00:00',8,'ENTRADA',60.00,273,'Compra de combustible',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(269,'2026-06-04 17:20:00',8,'SALIDA',35.00,NULL,'Consumo grupo electrógeno',25.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(270,'2026-06-05 08:15:00',8,'SALIDA',25.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(271,'2026-06-06 00:00:00',8,'ENTRADA',60.00,279,'Compra de combustible',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(272,'2026-06-06 18:50:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',40.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(273,'2026-06-07 10:33:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',20.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(274,'2026-06-08 11:47:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(275,'2026-06-08 00:00:00',8,'ENTRADA',60.00,282,'Compra de combustible',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(276,'2026-06-08 19:05:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',45.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(277,'2026-06-09 07:28:00',8,'SALIDA',25.00,NULL,'Consumo grupo electrógeno',20.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(278,'2026-06-09 00:00:00',8,'ENTRADA',60.00,290,'Compra de combustible',80.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(279,'2026-06-07 22:20:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(280,'2026-06-09 18:36:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',45.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(281,'2026-06-10 07:32:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',25.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(282,'2026-06-10 00:16:55',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',5.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(283,'2026-06-10 00:00:00',8,'ENTRADA',60.00,294,'Compra de combustible',65.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(284,'2026-06-11 07:40:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',45.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(285,'2026-06-11 19:44:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',25.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(286,'2026-06-12 13:25:00',7,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',15.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(287,'2026-06-12 18:00:00',7,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',5.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(288,'2026-06-13 01:00:00',7,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(289,'2026-06-13 07:12:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(290,'2026-06-13 00:00:00',7,'ENTRADA',9.29,299,'Compra de combustible',9.29)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(291,'2026-06-13 00:00:00',7,'ENTRADA',50.71,300,'Compra de combustible',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(292,'2026-06-13 18:57:05',7,'SALIDA',35.00,NULL,'REGULARIZAR DEL ANTERIOR',25.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(293,'2026-06-13 19:14:00',7,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',5.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(294,'2026-06-14 00:00:00',7,'ENTRADA',60.00,305,'Compra de combustible',65.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(295,'2026-06-14 13:32:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',50.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(296,'2026-06-15 07:48:00',7,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',30.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(297,'2026-06-15 16:28:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',15.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(298,'2026-06-15 18:21:00',7,'SALIDA',5.00,NULL,'Consumo grupo electrógeno',10.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(299,'2026-06-15 00:00:00',7,'ENTRADA',60.00,308,'Compra de combustible',70.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(300,'2026-06-16 08:09:00',7,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',50.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(301,'2026-06-16 18:42:00',7,'SALIDA',5.00,NULL,'Consumo grupo electrógeno',45.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(302,'2026-06-17 08:12:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',30.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(303,'2026-06-17 18:12:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',15.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(304,'2026-06-18 01:00:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(305,'2026-06-18 00:00:00',7,'ENTRADA',60.00,318,'Compra de combustible',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(306,'2026-06-18 13:00:00',7,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',40.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(307,'2026-06-18 20:10:00',7,'SALIDA',12.50,NULL,'Consumo grupo electrógeno',27.50)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(308,'2026-06-19 03:30:00',7,'SALIDA',12.50,NULL,'Consumo grupo electrógeno',15.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(309,'2026-06-19 11:30:00',7,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',5.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(310,'2026-06-19 00:00:00',7,'ENTRADA',60.00,323,'Compra de combustible',65.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(311,'2026-06-21 00:00:00',7,'ENTRADA',6.98,327,'Compra de combustible',71.98)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(312,'2026-06-21 00:00:00',7,'ENTRADA',53.34,328,'Compra de combustible',125.32)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(313,'2026-06-22 00:00:00',7,'ENTRADA',60.00,330,'Compra de combustible',185.32)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(314,'2026-06-19 20:02:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',170.32)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(315,'2026-06-20 08:03:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',155.32)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(316,'2026-06-20 17:45:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',140.32)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(317,'2026-06-21 01:30:00',7,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',130.32)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(318,'2026-06-21 09:15:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',115.32)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(319,'2026-06-21 15:18:00',7,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',105.32)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(320,'2026-06-21 20:10:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',90.32)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(321,'2026-06-22 08:30:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',75.32)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(322,'2026-06-22 13:40:00',7,'SALIDA',12.50,NULL,'Consumo grupo electrógeno',62.82)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(323,'2026-06-22 17:04:00',7,'SALIDA',2.82,NULL,'Consumo grupo electrógeno',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(324,'2026-06-22 20:35:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',45.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(325,'2026-06-23 04:30:00',7,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',35.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(326,'2026-06-23 08:35:00',7,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',25.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(327,'2026-06-23 17:50:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',10.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(328,'2026-06-24 07:25:00',7,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(329,'2026-06-24 00:00:00',7,'ENTRADA',60.00,335,'Compra de combustible',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(330,'2026-06-24 11:48:00',7,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',50.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(331,'2026-06-24 18:25:00',7,'SALIDA',5.00,NULL,'Consumo grupo electrógeno',45.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(332,'2026-06-25 07:39:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',30.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(333,'2026-06-25 11:21:00',7,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',20.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(334,'2026-06-25 18:38:00',7,'SALIDA',5.00,NULL,'Consumo grupo electrógeno',15.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(335,'2026-06-26 00:00:00',7,'ENTRADA',60.00,346,'Compra de combustible',75.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(336,'2026-06-26 08:10:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(337,'2026-06-26 16:52:00',7,'SALIDA',5.00,NULL,'Consumo grupo electrógeno',55.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(338,'2026-06-26 21:18:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',40.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(339,'2026-06-27 08:48:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',25.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(340,'2026-06-27 18:06:00',7,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',15.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(341,'2026-06-28 11:24:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',10.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(342,'2026-06-28 00:00:00',8,'ENTRADA',60.00,353,'Compra de combustible',70.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(343,'2026-06-28 18:11:00',8,'SALIDA',5.00,NULL,'Consumo grupo electrógeno',65.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(344,'2026-06-29 11:58:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',45.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(345,'2026-06-29 18:44:00',8,'SALIDA',5.00,NULL,'Consumo grupo electrógeno',40.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(346,'2026-06-30 13:40:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',25.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(347,'2026-06-30 18:33:00',8,'SALIDA',5.00,NULL,'Consumo grupo electrógeno',20.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(348,'2026-07-01 14:18:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(349,'2026-07-02 00:00:00',8,'ENTRADA',60.00,366,'Compra de combustible',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(350,'2026-07-02 09:54:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',45.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(351,'2026-07-02 17:59:00',8,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',35.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(352,'2026-07-03 07:14:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',20.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(353,'2026-07-03 18:15:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',5.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(354,'2026-07-04 00:00:00',8,'ENTRADA',60.00,372,'Compra de combustible',65.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(355,'2026-07-04 08:38:00',8,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',55.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(356,'2026-07-04 14:05:00',8,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',45.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(357,'2026-07-04 18:47:00',8,'SALIDA',5.00,NULL,'Consumo grupo electrógeno',40.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(358,'2026-07-05 06:43:00',8,'SALIDA',25.00,NULL,'Consumo grupo electrógeno',15.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(359,'2026-07-05 13:45:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(360,'2026-07-06 00:00:00',8,'ENTRADA',60.00,376,'Compra de combustible',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(361,'2026-07-06 06:54:00',8,'SALIDA',25.00,NULL,'Consumo grupo electrógeno',35.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(362,'2026-07-06 14:16:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',20.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(363,'2026-07-06 15:06:00',7,'SALIDA',5.00,NULL,'Consumo grupo electrógeno',10.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(364,'2026-07-06 18:54:00',8,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',10.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(365,'2026-07-07 07:00:00',8,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(366,'2026-07-07 00:00:00',8,'ENTRADA',61.90,382,'Compra de combustible',61.90)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(367,'2026-07-07 08:38:00',8,'SALIDA',20.00,NULL,'Consumo grupo electrógeno',41.90)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(368,'2026-07-07 11:02:00',7,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(369,'2026-07-07 17:05:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',26.90)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(370,'2026-07-07 18:40:00',8,'SALIDA',5.00,NULL,'Consumo grupo electrógeno',21.90)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(371,'2026-07-08 07:10:00',8,'SALIDA',12.90,NULL,'Consumo grupo electrógeno',9.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(372,'2026-07-08 00:00:00',8,'ENTRADA',60.00,386,'Compra de combustible',69.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(373,'2026-07-08 15:13:00',8,'SALIDA',25.00,NULL,'Consumo grupo electrógeno',44.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(374,'2026-07-08 15:59:00',7,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',29.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(375,'2026-07-09 07:40:00',8,'SALIDA',29.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(376,'2026-07-09 00:00:00',8,'ENTRADA',60.00,389,'Compra de combustible',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(377,'2026-07-09 18:54:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',45.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(378,'2026-07-10 13:30:00',8,'SALIDA',15.00,NULL,'Consumo grupo electrógeno',30.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(379,'2026-07-11 07:35:00',8,'SALIDA',30.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(380,'2026-07-13 00:00:00',8,'ENTRADA',0.47,397,'Compra de combustible',0.47)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(382,'2026-07-13 00:00:00',8,'ENTRADA',60.00,404,'Compra de combustible',60.47)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(383,'2026-07-13 08:01:00',8,'SALIDA',37.90,NULL,'Consumo grupo electrógeno',22.57)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(384,'2026-07-14 16:30:00',8,'SALIDA',17.57,NULL,'Consumo grupo electrógeno',5.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(386,'2026-07-15 00:00:00',8,'ENTRADA',32.57,410,'Compra de combustible',37.57)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(387,'2026-07-15 00:00:00',9,'ENTRADA',5.18,414,'Compra de combustible',5.18)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(388,'2026-07-15 00:00:00',8,'ENTRADA',60.00,415,'Compra de combustible',97.57)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(389,'2026-07-15 14:03:00',8,'SALIDA',30.00,NULL,'Consumo grupo electrógeno',67.57)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(390,'2026-07-15 19:46:00',8,'SALIDA',7.57,NULL,'Consumo grupo electrógeno',60.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(391,'2026-07-16 07:40:00',8,'SALIDA',25.00,NULL,'Consumo grupo electrógeno',35.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(392,'2026-07-16 07:45:00',7,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',25.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(393,'2026-07-16 19:02:00',8,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',15.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(394,'2026-07-17 07:24:00',8,'SALIDA',10.00,NULL,'Consumo grupo electrógeno',5.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(395,'2026-07-17 07:25:00',7,'SALIDA',5.00,NULL,'Consumo grupo electrógeno',0.00)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(396,'2026-07-17 00:00:00',8,'ENTRADA',44.47,424,'Compra de combustible',49.47)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(397,'2026-07-18 00:00:00',8,'ENTRADA',44.46,427,'Compra de combustible',93.93)",
  "INSERT INTO kardex_combustible(id_kardex,fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES(398,'2026-07-20 00:00:00',8,'ENTRADA',60.00,432,'Compra de combustible',153.93)",
];

foreach($sqls as $sql){ $pdo->exec($sql); }

// Leer compras #432 y #448 de la BD
$cps = $pdo->query("SELECT cc.id_combustible,cc.fecha,cc.id_unidad,cc.cantidad_gll,cc.tipo_comprobante,cc.nro_comprobante,u.placa FROM compra_combustible cc JOIN unidad u ON cc.id_unidad=u.id_unidad WHERE cc.id_combustible IN(432,448) ORDER BY cc.fecha ASC")->fetchAll(PDO::FETCH_ASSOC);
foreach($cps as $cp){
  $gll=(float)$cp['cantidad_gll'];
  $tipo=$gll>=0?'ENTRADA':'SALIDA';
  $obs='Compra '.($cp['tipo_comprobante']??'').' '.($cp['nro_comprobante']??'').' . '.$cp['placa'];
  $obs=$pdo->quote($obs);
  $pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('{$cp['fecha']} 00:00:00',{$cp['id_unidad']},'$tipo',".abs($gll).",{$cp['id_combustible']},$obs,0)");
}

// Consumos 17-jul al 25-jul
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-17 20:15:00',8,'SALIDA',20.0,432,'Consumo PERKINS turno 2026-07-17',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-18 07:25:00',8,'SALIDA',12.5,432,'Consumo PERKINS turno 2026-07-18',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-18 07:30:00',7,'SALIDA',5.0,432,'Consumo CATTINI turno 2026-07-18',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-18 19:08:00',8,'SALIDA',12.5,432,'Consumo PERKINS turno 2026-07-18',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-19 08:02:00',8,'SALIDA',15.9,432,'Consumo PERKINS turno 2026-07-19',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-19 08:08:00',7,'SALIDA',5.0,432,'Consumo CATTINI turno 2026-07-19',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-19 20:10:00',8,'SALIDA',12.5,432,'Consumo PERKINS turno 2026-07-19',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-20 07:39:00',8,'SALIDA',17.5,432,'Consumo PERKINS turno 2026-07-20',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-20 20:05:00',8,'SALIDA',15.0,432,'Consumo PERKINS turno 2026-07-20',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-21 18:54:00',8,'SALIDA',10.0,432,'Consumo PERKINS turno 2026-07-21',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-23 10:22:00',8,'SALIDA',28.0,432,'Consumo PERKINS turno 2026-07-23',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-24 21:03:00',8,'SALIDA',15.0,448,'Consumo PERKINS turno 2026-07-24',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-25 07:29:00',8,'SALIDA',20.0,448,'Consumo PERKINS turno 2026-07-25',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-25 18:15:00',7,'SALIDA',10.0,448,'Consumo CATTINI turno 2026-07-25',0)");
$pdo->exec("INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll) VALUES('2026-07-25 19:28:00',8,'SALIDA',15.0,448,'Consumo PERKINS turno 2026-07-25',0)");

// Recalcular saldos
$movs=$pdo->query('SELECT id_kardex,tipo_movimiento,galones FROM kardex_combustible ORDER BY fecha ASC,id_kardex ASC')->fetchAll(PDO::FETCH_ASSOC);
$sal=0.0;
$upd=$pdo->prepare('UPDATE kardex_combustible SET saldo_gll=? WHERE id_kardex=?');
foreach($movs as $mv){
  $sal+=$mv['tipo_movimiento']==='ENTRADA'?(float)$mv['galones']:-(float)$mv['galones'];
  $upd->execute([round($sal,2),$mv['id_kardex']]);
}

$total=$pdo->query('SELECT COUNT(*) FROM kardex_combustible')->fetchColumn();
$prim=$pdo->query('SELECT * FROM kardex_combustible ORDER BY id_kardex ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$ult=$pdo->query('SELECT * FROM kardex_combustible ORDER BY id_kardex DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$prev=$pdo->query('SELECT * FROM kardex_combustible ORDER BY id_kardex DESC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);
$prev=array_reverse($prev);
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>OK</title>
<style>body{font-family:sans-serif;padding:30px;max-width:1000px}
.ok{background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px;padding:14px;margin-bottom:14px}
.warn{background:#fef3c7;padding:12px;border-radius:8px;margin-top:14px;font-size:13px}
table{border-collapse:collapse;width:100%;font-size:12px}
td,th{border:1px solid #ddd;padding:5px 9px}th{background:#f3f4f6}
</style></head><body>
<h2>Kardex importado</h2>
<div class="ok">
<p>Total: '.$total.' | Primer id: '.$prim['id_kardex'].' saldo='.$prim['saldo_gll'].' | Ultimo id: '.$ult['id_kardex'].' saldo='.$ult['saldo_gll'].'</p>
<p>Saldo final: '.round($sal,2).' gll</p>
</div>
<table><tr><th>id</th><th>fecha</th><th>tipo</th><th>gll</th><th>id_comb</th><th>obs</th><th>saldo</th></tr>';
foreach($prev as $r){
  echo '<tr><td>'.$r['id_kardex'].'</td><td>'.$r['fecha'].'</td><td>'.$r['tipo_movimiento'].'</td><td>'.$r['galones'].'</td><td>'.($r['id_combustible']??'-').'</td><td>'.htmlspecialchars(substr($r['observacion'],0,40)).'</td><td><b>'.$r['saldo_gll'].'</b></td></tr>';
}
echo '</table><div class="warn">Elimina este archivo del servidor.</div></body></html>';