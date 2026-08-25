<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors','0');
ini_set('log_errors','1');

register_shutdown_function(function(){
    $err=error_get_last();
    if($err&&in_array($err['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR])){
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['error'=>$err['message'],'file'=>basename($err['file']),'line'=>$err['line']]);
    }
});

require_once __DIR__.'/../config/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if($_SERVER['REQUEST_METHOD']==='OPTIONS')exit;

$uri=$_SERVER['REQUEST_URI']??'/';
$uri=parse_url($uri,PHP_URL_PATH);
$base=rtrim(APP_BASE,'/');
if($base!==''&&(strpos($uri, $base) === 0))$uri=substr($uri,strlen($base));
$path=trim(preg_replace('#^/api#','',$uri),'/');
$seg=array_values(array_filter(explode('/',$path)));
$method=$_SERVER['REQUEST_METHOD'];

try{ob_clean();dispatch($method,$seg);}
catch(Throwable $e){ob_clean();jout(['error'=>$e->getMessage(),'file'=>basename($e->getFile()),'line'=>$e->getLine()],500);}

function dispatch(string $m,array $s):void{
$r0=$s[0]??'';$r1=$s[1]??'';$r2=$s[2]??'';

// ── catálogos ──────────────────────────────────────────────
if($r0==='unidades')jout(qall('SELECT id_unidad,placa,tipo_unidad,descripcion,capacidad_tanque,potencia_kw,consumo_nominal_gal_hora FROM unidad ORDER BY tipo_unidad,placa'));
if($r0==='rutas')jout(qall('SELECT * FROM ruta ORDER BY destino'));
if($r0==='grifos')jout(qall('SELECT * FROM grifo ORDER BY razon_social'));
if($r0==='actividades-retro')jout(qall('SELECT * FROM actividades_retro ORDER BY actividad'));
if($r0==='equipos-proceso')jout(qall('SELECT * FROM equipos_proceso ORDER BY proceso,nombre_equipo'));
if($r0==='conductores')jout(qall('SELECT id_conductor,nombre_conductor,nro_licencia FROM conductor ORDER BY nombre_conductor'));
if($r0==='conductor')jout(qall('SELECT id_conductor,nombre_conductor,dni,nro_licencia FROM conductor ORDER BY nombre_conductor'));
if($r0==='trabajadores'){
    $sql='SELECT id_trabajador,nombre_completo,cargo,id_area FROM trabajadores WHERE activo=1';
    $p=[];
    if($v=gget('id_area')){$sql.=' AND id_area=?';$p[]=$v;}
    if($v=gget('cargo')){$sql.=' AND cargo LIKE ?';$p[]="%$v%";}
    jout(qall($sql.' ORDER BY nombre_completo',$p));
}

// ════════════════════════════════════════════════════════════
// GARITA
// ════════════════════════════════════════════════════════════
if($r0==='garita'){
    if($r1==='ultimo-km'){
        $id=gget('id_unidad');if(!$id)jout(['error'=>'id_unidad requerido'],400);
        jout(qone('SELECT km_retorno AS ultimo_km,fecha FROM control_flota WHERE id_unidad=? AND km_retorno IS NOT NULL ORDER BY fecha DESC,id_control DESC LIMIT 1',[$id])??['ultimo_km'=>0,'fecha'=>null]);
    }
    if($r1==='viajes'&&$m==='GET'){
        // Usar subconsulta para comb_asignado — evita duplicados por LEFT JOIN detalle_consumo
        $sql="SELECT cf.*,u.placa,u.tipo_unidad,
                     COALESCE(t.nombre_conductor,'') AS conductor_nombre,
                     r.destino,r.origen,r.km_esperado,r.margen_km,
                     (SELECT dc.id_combustible FROM detalle_consumo dc
                      WHERE dc.id_control=cf.id_control LIMIT 1) AS comb_asignado
              FROM control_flota cf
              LEFT JOIN unidad u ON cf.id_unidad=u.id_unidad
              LEFT JOIN conductor t ON cf.id_conductor=t.id_conductor
              LEFT JOIN ruta r ON cf.id_ruta=r.id_ruta
              WHERE 1=1";
        $p=[];
        if($v=gget('id_unidad')){$sql.=' AND cf.id_unidad=?';$p[]=$v;}
        if($v=gget('fecha')){$sql.=' AND cf.fecha=?';$p[]=$v;}
        if($v=gget('fecha_desde')){$sql.=' AND cf.fecha>=?';$p[]=$v;}
        if($v=gget('fecha_hasta')){$sql.=' AND cf.fecha<=?';$p[]=$v;}
        if(gget('solo_sin_asignar')){
            $sql.=' AND (SELECT dc2.id_combustible FROM detalle_consumo dc2 WHERE dc2.id_control=cf.id_control LIMIT 1) IS NULL';
        }
        // solo_sin_retorno: viajes sin km_retorno (para viajes largos multi-día)
        if(gget('solo_sin_retorno')){
            $sql.=' AND (cf.km_retorno IS NULL OR cf.km_retorno = 0)';
        }
        if($v=gget('id_conductor')){$sql.=' AND cf.id_conductor=?';$p[]=$v;}
        $sql.=' ORDER BY cf.fecha DESC,cf.hora_salida DESC LIMIT 300';
        jout(qall($sql,$p));
    }
    if($r1==='viajes'&&$m==='POST'){
        $d=jbody();
        $km_rec=null;
        if(!empty($d['km_retorno'])&&isset($d['km_salida']))$km_rec=round((float)$d['km_retorno']-(float)$d['km_salida'],2);
        $desv=null;$estado=null;
        if(!empty($d['id_ruta'])&&$km_rec!==null){
            $rt=qone('SELECT km_esperado,margen_km FROM ruta WHERE id_ruta=?',[$d['id_ruta']]);
            if($rt){$desv=round($km_rec-(float)$rt['km_esperado'],2);$estado=abs($desv)<=(float)$rt['margen_km']?'DENTRO_MARGEN':'FUERA_MARGEN';}
        }
        $id=qexec('INSERT INTO control_flota(fecha,id_unidad,id_conductor,hora_salida,hora_regreso,id_ruta,km_salida,km_retorno,tipo_actividad,observacion,km_recorrido,desviacion,estado_ruta)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [$d['fecha']??date('Y-m-d'),$d['id_unidad'],$d['id_conductor']??null,$d['hora_salida']??null,$d['hora_regreso']??null,$d['id_ruta']??null,$d['km_salida']??null,$d['km_retorno']??null,$d['tipo_actividad']??null,$d['observacion']??null,$km_rec,$desv,$estado]);
        qexec('INSERT INTO detalle_consumo(id_combustible,id_control,km_recorridos)VALUES(NULL,?,?)',[$id,$km_rec]);
        jout(['id_control'=>(int)$id,'km_recorrido'=>$km_rec,'estado_ruta'=>$estado],201);
    }
    if($r1==='viajes'&&$r2!==''&&$m==='PUT'){
        $id=(int)$r2;$d=jbody();
        $km_rec=null;
        if(!empty($d['km_retorno'])&&isset($d['km_salida']))$km_rec=round((float)$d['km_retorno']-(float)$d['km_salida'],2);
        $desv=null;$estado=null;
        if(!empty($d['id_ruta'])&&$km_rec!==null){
            $rt=qone('SELECT km_esperado,margen_km FROM ruta WHERE id_ruta=?',[$d['id_ruta']]);
            if($rt){$desv=round($km_rec-(float)$rt['km_esperado'],2);$estado=abs($desv)<=(float)$rt['margen_km']?'DENTRO_MARGEN':'FUERA_MARGEN';}
        }
        qrows('UPDATE control_flota SET fecha=?,id_unidad=?,id_conductor=?,hora_salida=?,hora_regreso=?,id_ruta=?,km_salida=?,km_retorno=?,tipo_actividad=?,observacion=?,km_recorrido=?,desviacion=?,estado_ruta=? WHERE id_control=?',
            [$d['fecha'],$d['id_unidad'],$d['id_conductor']??null,$d['hora_salida']??null,$d['hora_regreso']??null,$d['id_ruta']??null,$d['km_salida']??null,$d['km_retorno']??null,$d['tipo_actividad']??null,$d['observacion']??null,$km_rec,$desv,$estado,$id]);
        qrows('UPDATE detalle_consumo SET km_recorridos=? WHERE id_control=?',[$km_rec,$id]);
        jout(['ok'=>true,'km_recorrido'=>$km_rec,'estado_ruta'=>$estado]);
    }
    if($r1==='viajes'&&$r2!==''&&$m==='DELETE'){
        $id=(int)$r2;
        qrows('DELETE FROM detalle_consumo WHERE id_control=?',[$id]);
        qrows('DELETE FROM control_flota WHERE id_control=?',[$id]);
        jout(['ok'=>true]);
    }
    // GET /api/garita/viajes-por-dia?fecha_desde&fecha_hasta&id_unidad
    // Resumen de viajes por día y unidad (para vista de heatmap/tabla)
    if($r1==='viajes-por-dia'){
        $fd2=gget('fecha_desde',date('Y-m-01'));
        $fh2=gget('fecha_hasta',date('Y-m-d'));
        $uid=gget('id_unidad');
        $sql="SELECT cf.fecha,cf.id_unidad,u.placa,u.tipo_unidad,
                     COUNT(*) AS n_viajes,
                     SUM(CASE WHEN cf.km_recorrido>0 THEN cf.km_recorrido ELSE 0 END) AS km_dia,
                     COUNT(CASE WHEN dc.id_combustible IS NOT NULL THEN 1 END) AS viajes_con_comb,
                     COUNT(CASE WHEN dc.id_combustible IS NULL     THEN 1 END) AS viajes_sin_comb,
                     GROUP_CONCAT(DISTINCT cf.tipo_actividad ORDER BY cf.tipo_actividad SEPARATOR ', ') AS actividades
              FROM control_flota cf
              JOIN unidad u ON cf.id_unidad=u.id_unidad
              LEFT JOIN detalle_consumo dc ON dc.id_control=cf.id_control
              WHERE cf.fecha BETWEEN ? AND ?";
        $p=[$fd2,$fh2];
        if($uid){$sql.=' AND cf.id_unidad=?';$p[]=$uid;}
        $sql.=' GROUP BY cf.fecha,cf.id_unidad,u.placa,u.tipo_unidad ORDER BY cf.fecha DESC,u.placa';
        jout(qall($sql,$p));
    }

    // GET /api/garita/compras-para-asignar?id_unidad=X&km_salida=Y&km_retorno=Z
    //
    // LÓGICA (confirmada con el usuario):
    // Un viaje pertenece al tanqueo T si:
    //   1. km_T > km_salida  (el tanqueo fue posterior al inicio del viaje)
    //   2. (km_retorno - km_T) <= MARGEN  (el retorno no superó al tanqueo por más del margen)
    //      Si km_retorno < km_T → diferencia negativa → siempre cumple
    //      Si km_retorno > km_T → fue al grifo en el camino → solo si dif ≤ margen
    //
    // Ejemplo: T1=km50, T2=km100
    //   V5 (96→110): T2=100 no cumple (110-100=10 > 4). T3 siguiente → V5 al T3
    //   V6 (96→104): T2=100 sí cumple (104-100=4 ≤ 4) → V6 al T2
    if($r1==='compras-para-asignar'){
        $id_u  = gget('id_unidad');
        $km_s  = (float)(gget('km_salida',  0));
        $km_r  = (float)(gget('km_retorno', 0));
        $margen= (float)(gget('margen', 4));   // default 4 km
        if(!$id_u) jout(['error'=>'id_unidad requerido'], 400);

        $compras = qall(
            "SELECT cc.id_combustible, cc.fecha, cc.nro_comprobante, cc.cantidad_gll,
                    cc.km_vehiculo, cc.precio_unitario, cc.total,
                    (SELECT COUNT(*) FROM detalle_consumo dc2
                     WHERE dc2.id_combustible=cc.id_combustible) AS viajes_asignados,

                    CASE WHEN cc.km_vehiculo = (
                        SELECT MIN(cx.km_vehiculo)
                        FROM compra_combustible cx
                        WHERE cx.id_unidad        = cc.id_unidad
                          AND cx.tipo_combustible = 'PETROLEO'
                          AND cx.km_vehiculo      > ?
                          AND (? - cx.km_vehiculo) <= ?
                    ) THEN 1 ELSE 0 END AS sugerido,

                    (? - cc.km_vehiculo) AS dif_retorno
             FROM compra_combustible cc
             WHERE cc.id_unidad        = ?
               AND cc.tipo_combustible = 'PETROLEO'
               AND cc.km_vehiculo      IS NOT NULL
             ORDER BY cc.km_vehiculo DESC
             LIMIT 20",
            [$km_s, $km_r, $margen,   // para la subconsulta sugerido
             $km_r,                    // para dif_retorno
             $id_u]
        );
        jout($compras);
    }
}

// ════════════════════════════════════════════════════════════
// COMPRAS
// ════════════════════════════════════════════════════════════
if($r0==='compras'){
    if($r1==='rendimiento'){
        $fl=qall("SELECT u.id_unidad,u.placa,u.tipo_unidad,
                    COUNT(DISTINCT dc.id_control) AS n_viajes,
                    COALESCE(SUM(cf.km_recorrido),0) AS km_totales,
                    COALESCE(SUM(cc.cantidad_gll),0) AS gll_totales,
                    ROUND(SUM(cf.km_recorrido)/NULLIF(SUM(cc.cantidad_gll),0),2) AS km_por_galon,
                    ROUND(SUM(cc.total),2) AS costo_total_soles
                 FROM compra_combustible cc
                 JOIN unidad u ON cc.id_unidad=u.id_unidad AND u.tipo_unidad='FLOTA'
                 JOIN detalle_consumo dc ON dc.id_combustible=cc.id_combustible
                 JOIN control_flota cf ON dc.id_control=cf.id_control
                 GROUP BY u.id_unidad,u.placa,u.tipo_unidad ORDER BY km_por_galon DESC");
        // Maquinaria: gal/hora = galones_tanqueo / horas_entre_tanqueos (mismo patrón que km/gal en flota)
        $mq=qall("SELECT u.id_unidad,u.placa,u.tipo_unidad,
                    COUNT(c2.id_combustible) AS n_tanqueos,
                    SUM(c2.km_vehiculo - c1.km_vehiculo) AS horas_totales,
                    SUM(c2.cantidad_gll) AS gll_totales,
                    ROUND(SUM(c2.cantidad_gll)/NULLIF(SUM(c2.km_vehiculo - c1.km_vehiculo),0),3) AS gal_por_hora,
                    ROUND(SUM(c2.total),2) AS costo_soles
                 FROM compra_combustible c2
                 JOIN unidad u ON c2.id_unidad=u.id_unidad AND u.tipo_unidad='MAQ. PESADA'
                 JOIN compra_combustible c1
                   ON c1.id_unidad=c2.id_unidad
                  AND c1.km_vehiculo=(
                      SELECT MAX(cx.km_vehiculo) FROM compra_combustible cx
                      WHERE cx.id_unidad=c2.id_unidad AND cx.km_vehiculo<c2.km_vehiculo
                        AND cx.tipo_combustible='PETROLEO')
                 WHERE c2.tipo_combustible='PETROLEO'
                   AND c2.id_unidad IS NOT NULL AND c2.km_vehiculo IS NOT NULL
                   AND (c2.km_vehiculo - c1.km_vehiculo) BETWEEN 0.5 AND 5000
                 GROUP BY u.id_unidad,u.placa,u.tipo_unidad ORDER BY gal_por_hora DESC");
        jout(['flota'=>$fl,'maquinaria'=>$mq,'grupos_electrogenos'=>[]]);
    }
    if($r1==='saldo-grifo')jout(qone('SELECT saldo,fecha FROM movimientos_combustible ORDER BY id_movimiento DESC LIMIT 1')??['saldo'=>0,'fecha'=>null]);
    if($r1==='kpis'){
        $sql='SELECT COALESCE(SUM(cc.cantidad_gll),0) AS total_galones,COALESCE(SUM(cc.total),0) AS total_soles,COUNT(*) AS n_compras,COALESCE(AVG(cc.precio_unitario),0) AS precio_promedio FROM compra_combustible cc WHERE 1=1';
        $p=[];
        if($v=gget('fecha_desde')){$sql.=' AND cc.fecha>=?';$p[]=$v;}
        if($v=gget('fecha_hasta')){$sql.=' AND cc.fecha<=?';$p[]=$v;}
        $row=qone($sql,$p);
        $saldo=qone('SELECT saldo FROM movimientos_combustible ORDER BY id_movimiento DESC LIMIT 1');
        $row['saldo_grifo']=(float)($saldo['saldo']??0);
        jout($row);
    }
    if($r1==='movimientos')jout(qall('SELECT * FROM movimientos_combustible ORDER BY id_movimiento DESC LIMIT 100'));
    if($r1==='deposito'&&$m==='POST'){
        $d=jbody();
        $ul=qone('SELECT saldo FROM movimientos_combustible ORDER BY id_movimiento DESC LIMIT 1');
        $nuevo=(float)($ul['saldo']??0)+(float)$d['monto'];
        qexec('INSERT INTO movimientos_combustible(fecha,tipo,descripcion,monto,id_compra,saldo)VALUES(?,"DEPOSITO",?,?,NULL,?)',
            [$d['fecha']??date('Y-m-d'),$d['descripcion']??'Depósito grifo',(float)$d['monto'],$nuevo]);
        jout(['ok'=>true,'saldo_nuevo'=>$nuevo]);
    }
    if($r1===''&&$m==='GET'){
        $sql="SELECT cc.*,u.placa,u.tipo_unidad,g.razon_social AS grifo_nombre
              FROM compra_combustible cc
              LEFT JOIN unidad u ON cc.id_unidad=u.id_unidad
              LEFT JOIN grifo g ON cc.id_grifo=g.id_grifo
              WHERE 1=1";
        $p=[];
        if($v=gget('id_unidad')){$sql.=' AND cc.id_unidad=?';$p[]=$v;}
        if($v=gget('fecha_desde')){$sql.=' AND cc.fecha>=?';$p[]=$v;}
        if($v=gget('fecha_hasta')){$sql.=' AND cc.fecha<=?';$p[]=$v;}
        if($v=gget('tipo_combustible')){$sql.=' AND cc.tipo_combustible=?';$p[]=$v;}
        if($v=gget('forma_pago')){$sql.=' AND cc.forma_pago=?';$p[]=$v;}
        $sql.=' ORDER BY cc.fecha DESC,cc.id_combustible DESC LIMIT 500';
        jout(qall($sql,$p));
    }
    if($r1===''&&$m==='POST'){
        $d=jbody();
        $cant=(float)($d['cantidad_gll']??0);
        $pu  =(float)($d['precio_unitario']??0);
        // precio_unitario ya incluye IGV: total = gll × precio
        // Si el front manda total explícito usarlo; sino calcular
        $total=isset($d['total'])&&(float)$d['total']>0
            ?(float)$d['total']
            :round($cant*$pu,2);
        // Descomponer: subtotal = total/1.18, igv = total - subtotal
        $sub =round($total/1.18,2);
        $igv =round($total-$sub,2);
        $id=qexec('INSERT INTO compra_combustible(fecha,tipo_comprobante,nro_comprobante,id_grifo,id_unidad,tipo_combustible,cantidad_gll,km_vehiculo,precio_unitario,subtotal,igv,total,forma_pago,tanqueo)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [$d['fecha']??date('Y-m-d'),$d['tipo_comprobante']??null,$d['nro_comprobante']??null,$d['id_grifo']??null,$d['id_unidad']??null,$d['tipo_combustible'],$cant,$d['km_vehiculo']??null,$pu,$sub,$igv,$total,$d['forma_pago']??'CONTADO',$d['tanqueo']??1]);
        if(($d['forma_pago']??'')==='CREDITO')registrar_movimiento_credito((int)$id,$total,($d['tipo_comprobante']??'').' '.($d['nro_comprobante']??''));
        // Asignación automática según tipo de unidad
        $asig=['rows_updated'=>0];
        if(!empty($d['id_unidad'])){
            $u_tipo_post=qone('SELECT tipo_unidad FROM unidad WHERE id_unidad=?',[$d['id_unidad']]);
            if(($u_tipo_post['tipo_unidad']??'')==='FLOTA'){
                $asig=run_assignment((int)$id);
            } elseif(($u_tipo_post['tipo_unidad']??'')==='MAQ. PESADA'){
                $asig=run_assignment_maq_bulk((int)$id);
            }
        }
        // Si la compra es para un GE (Perkins o Cattini) → ENTRADA en kardex del bidón
        // Excluir MEBA y GASOLINA del kardex del bidón
        $kardex_ge = false;
        if(!empty($d['id_unidad']) && ($d['tipo_combustible']??'')!=='GASOLINA'){
            $uid_tipo=qone('SELECT tipo_unidad,placa FROM unidad WHERE id_unidad=?',[$d['id_unidad']]);
            if(($uid_tipo['tipo_unidad']??'')==='GRUPO ELECTROGENO' && ($uid_tipo['placa']??'')!=='MEBA'){
                $fecha_compra = ($d['fecha']??date('Y-m-d')).' 00:00:00';
                reg_kardex_ge((int)$d['id_unidad'],'ENTRADA',$cant,(int)$id,
                    'Compra '.($d['tipo_comprobante']??'').' '.($d['nro_comprobante']??'').' · '.($uid_tipo['placa']??''),
                    $fecha_compra);
                $kardex_ge = true;
            }
        }
        jout(['id_combustible'=>(int)$id,'subtotal'=>$sub,'igv'=>$igv,'total'=>$total,'asignacion'=>$asig,'kardex_ge'=>$kardex_ge],201);
    }
    if($r1!==''&&is_numeric($r1)&&$m==='PUT'){
        $id=(int)$r1; $d=jbody();
        $cant =(float)($d['cantidad_gll']??0);
        $pu   =(float)($d['precio_unitario']??0);
        $total=isset($d['total'])&&(float)$d['total']>0?(float)$d['total']:round($cant*$pu,2);
        $sub  =round($total/1.18,2);
        $igv  =round($total-$sub,2);

        $ant      =qone('SELECT total,forma_pago FROM compra_combustible WHERE id_combustible=?',[$id]);
        $total_ant=(float)($ant['total']??0);
        $pago_ant =$ant['forma_pago']??'CONTADO';
        $pago_new =$d['forma_pago']??'CONTADO';
        $cambio   =abs($total-$total_ant)>0.01;

        qrows('UPDATE compra_combustible SET fecha=?,tipo_comprobante=?,nro_comprobante=?,id_grifo=?,id_unidad=?,tipo_combustible=?,cantidad_gll=?,km_vehiculo=?,precio_unitario=?,subtotal=?,igv=?,total=?,forma_pago=?,tanqueo=? WHERE id_combustible=?',
            [$d['fecha'],$d['tipo_comprobante']??null,$d['nro_comprobante']??null,$d['id_grifo']??null,$d['id_unidad']??null,$d['tipo_combustible'],$cant,$d['km_vehiculo']??null,$pu,$sub,$igv,$total,$pago_new,$d['tanqueo']??1,$id]);

        $saldo_msg=null;

        if($pago_ant==='CREDITO'&&$pago_new==='CREDITO'&&$cambio){
            $mov=qone("SELECT id_movimiento FROM movimientos_combustible WHERE id_compra=? AND tipo='COMPRA' ORDER BY id_movimiento DESC LIMIT 1",[$id]);
            if($mov){
                qrows('UPDATE movimientos_combustible SET monto=? WHERE id_movimiento=?',[$total,$mov['id_movimiento']]);
                $rsal=qall('SELECT id_movimiento,tipo,monto FROM movimientos_combustible ORDER BY id_movimiento ASC');
                $rs=0;foreach($rsal as $rm){$rs+=$rm['tipo']==='DEPOSITO'?(float)$rm['monto']:-(float)$rm['monto'];qrows('UPDATE movimientos_combustible SET saldo=? WHERE id_movimiento=?',[round($rs,2),$rm['id_movimiento']]);}
                $saldo_msg='Saldo actualizado';
            } else {
                registrar_movimiento_credito($id,$total,($d['tipo_comprobante']??'').' '.($d['nro_comprobante']??''));
                $saldo_msg='Movimiento creado';
            }
        } elseif($pago_ant==='CREDITO'&&$pago_new==='CONTADO'){
            $mov=qone("SELECT id_movimiento FROM movimientos_combustible WHERE id_compra=? AND tipo='COMPRA' LIMIT 1",[$id]);
            if($mov){
                qrows('DELETE FROM movimientos_combustible WHERE id_movimiento=?',[$mov['id_movimiento']]);
                $rsal=qall('SELECT id_movimiento,tipo,monto FROM movimientos_combustible ORDER BY id_movimiento ASC');
                $rs=0;foreach($rsal as $rm){$rs+=$rm['tipo']==='DEPOSITO'?(float)$rm['monto']:-(float)$rm['monto'];qrows('UPDATE movimientos_combustible SET saldo=? WHERE id_movimiento=?',[round($rs,2),$rm['id_movimiento']]);}
                $saldo_msg='Movimiento revertido';
            }
        } elseif($pago_ant==='CONTADO'&&$pago_new==='CREDITO'){
            registrar_movimiento_credito($id,$total,($d['tipo_comprobante']??'').' '.($d['nro_comprobante']??''));
            $saldo_msg='Movimiento de credito registrado';
        }

        // Si la compra es para un GE (no MEBA, no GASOLINA) → actualizar/crear ENTRADA en kardex
        if(!empty($d['id_unidad']) && ($d['tipo_combustible']??'')!=='GASOLINA'){
            $uid_tipo2=qone('SELECT tipo_unidad,placa FROM unidad WHERE id_unidad=?',[$d['id_unidad']]);
            if(($uid_tipo2['tipo_unidad']??'')==='GRUPO ELECTROGENO' && ($uid_tipo2['placa']??'')!=='MEBA'){
                // Buscar si ya hay un kardex ENTRADA para esta compra
                $kex=qone("SELECT id_kardex,galones FROM kardex_combustible WHERE id_combustible=? AND tipo_movimiento='ENTRADA' LIMIT 1",[$id]);
                if($kex){
                    if($monto_cambio){
                        // Actualizar galones y recalcular saldos
                        qrows('UPDATE kardex_combustible SET galones=?,observacion=? WHERE id_kardex=?',
                            [$cant,'Compra '.($d['tipo_comprobante']??'').' '.($d['nro_comprobante']??''),$kex['id_kardex']]);
                        // Recalcular saldos solo desde ese id en adelante
                        $saldo_prev_k=qone('SELECT saldo_gll FROM kardex_combustible WHERE id_kardex < ? ORDER BY id_kardex DESC LIMIT 1',[$kex['id_kardex']]);
                        $ksal=(float)($saldo_prev_k['saldo_gll']??0);
                        $kmovs=qall('SELECT id_kardex,tipo_movimiento,galones FROM kardex_combustible WHERE id_kardex >= ? ORDER BY id_kardex ASC',[$kex['id_kardex']]);
                        foreach($kmovs as $km){
                            $ksal+=$km['tipo_movimiento']==='ENTRADA'?(float)$km['galones']:-(float)$km['galones'];
                            qrows('UPDATE kardex_combustible SET saldo_gll=? WHERE id_kardex=?',[round($ksal,2),$km['id_kardex']]);
                        }
                    }
                } else {
                    // No existía → crear ENTRADA
                    $fecha_compra2 = ($d['fecha']??date('Y-m-d')).' 00:00:00';
                    reg_kardex_ge((int)$d['id_unidad'],'ENTRADA',$cant,$id,
                        'Compra '.($d['tipo_comprobante']??'').' '.($d['nro_comprobante']??'').' · '.($uid_tipo2['placa']??''),
                        $fecha_compra2);
                }
            }
        }
        jout(['ok'=>true,'subtotal'=>$sub,'igv'=>$igv,'total'=>$total,'saldo_msg'=>$saldo_msg]);
    }
    if($r1!==''&&is_numeric($r1)&&$m==='DELETE'){
        qrows('DELETE FROM compra_combustible WHERE id_combustible=?',[(int)$r1]);
        jout(['ok'=>true]);
    }
    // Asignación manual viaje flota
    // POST /api/compras/reasignar-vacios
    if($r1==='reasignar-vacios'&&$m==='POST'){
        $d=jbody();
        $solo_vacios=isset($d['solo_vacios'])?(bool)$d['solo_vacios']:true;
        $MARGEN=4;
        $viajes=qall(
            "SELECT cf.id_control,cf.id_unidad,cf.km_salida,cf.km_retorno
             FROM control_flota cf
             JOIN unidad u ON cf.id_unidad=u.id_unidad
             WHERE cf.km_salida IS NOT NULL AND cf.km_salida>0
               AND u.tipo_unidad='FLOTA'
             ORDER BY cf.fecha DESC,cf.id_control DESC LIMIT 500"
        );
        $asignados=0;$saltados=0;
        foreach($viajes as $v){
            $id_ctrl=(int)$v['id_control'];
            $id_u=(int)$v['id_unidad'];
            $km_sal=(float)$v['km_salida'];
            $km_ret=(float)($v['km_retorno']??0);
            $tiene=(int)(qval('SELECT COUNT(*) FROM detalle_consumo WHERE id_control=?',[$id_ctrl])??0);
            if($solo_vacios&&$tiene>0){$saltados++;continue;}
            $candidatas=qall(
                "SELECT id_combustible,km_vehiculo FROM compra_combustible WHERE id_unidad=? AND tipo_combustible='PETROLEO' AND km_vehiculo IS NOT NULL AND km_vehiculo>? ORDER BY km_vehiculo ASC",
                [$id_u,$km_sal]
            );
            $mejor=null;
            foreach($candidatas as $cp){
                $dif=$km_ret>0?$km_ret-(float)$cp['km_vehiculo']:-1;
                if($dif<=$MARGEN){$mejor=$cp;break;}
            }
            if(!$mejor){$saltados++;continue;}
            if($tiene>0)qrows('DELETE FROM detalle_consumo WHERE id_control=?',[$id_ctrl]);
            $ex=(int)(qval('SELECT COUNT(*) FROM detalle_consumo WHERE id_control=? AND id_combustible=?',[$id_ctrl,$mejor['id_combustible']])??0);
            if(!$ex){qrows('INSERT INTO detalle_consumo(id_control,id_combustible) VALUES(?,?)',[$id_ctrl,$mejor['id_combustible']]);$asignados++;}
        }
        jout(['ok'=>true,'asignados'=>$asignados,'saltados'=>$saltados,'total'=>count($viajes)]);
    }

    if($r1==='asignar-viaje'&&$m==='POST'){
        $d=jbody();
        $id_ctrl=(int)($d['id_control']??0);
        $id_comb=$d['id_combustible']!==null?(int)$d['id_combustible']:null;
        if(!$id_ctrl)jout(['error'=>'id_control requerido'],400);
        $det=qone('SELECT id_detalle FROM detalle_consumo WHERE id_control=?',[$id_ctrl]);
        if($det){
            qrows('UPDATE detalle_consumo SET id_combustible=? WHERE id_control=?',[$id_comb,$id_ctrl]);
        }else{
            $cf=qone('SELECT km_recorrido FROM control_flota WHERE id_control=?',[$id_ctrl]);
            qexec('INSERT INTO detalle_consumo(id_combustible,id_control,km_recorridos)VALUES(?,?,?)',[$id_comb,$id_ctrl,$cf['km_recorrido']??null]);
        }
        jout(['ok'=>true,'id_control'=>$id_ctrl,'id_combustible'=>$id_comb]);
    }
}

// ════════════════════════════════════════════════════════════
// MAQUINARIA
// ════════════════════════════════════════════════════════════
if($r0==='maquinaria'){
    if($r1==='ultimo-horometro'){
        $id=gget('id_unidad');
        jout(qone('SELECT horometro_final,fecha FROM retro_control_dia WHERE id_unidad=? AND horometro_final IS NOT NULL ORDER BY fecha DESC,horometro_final DESC LIMIT 1',[$id])??['horometro_final'=>0,'fecha'=>null]);
    }
    if($r1==='dias'&&$m==='GET'){
        $sql="SELECT rcd.*,u.placa,COALESCE(t.nombre_conductor,'') AS conductor_nombre
              FROM retro_control_dia rcd
              LEFT JOIN unidad u ON rcd.id_unidad=u.id_unidad
              LEFT JOIN conductor t ON rcd.id_conductor=t.id_conductor
              WHERE 1=1";
        $p=[];
        if($v=gget('id_unidad')){$sql.=' AND rcd.id_unidad=?';$p[]=$v;}
        if($v=gget('fecha_desde')){$sql.=' AND rcd.fecha>=?';$p[]=$v;}
        if($v=gget('fecha_hasta')){$sql.=' AND rcd.fecha<=?';$p[]=$v;}
        if(gget('solo_sin_asignar'))$sql.=' AND rcd.id_combustible IS NULL';
        $sql.=' ORDER BY rcd.fecha DESC,rcd.id_control_dia DESC LIMIT 300';
        jout(qall($sql,$p));
    }
    if($r1==='dias'&&$m==='POST'){
        $d=jbody();
        $hi=(float)($d['horometro_inicio']??0);$hf=(float)($d['horometro_final']??0);
        $hh=$hf>0?round($hf-$hi,2):null;
        $id=qexec('INSERT INTO retro_control_dia(fecha,id_unidad,id_conductor,horometro_inicio,horometro_final,horas_horometro,horas_ralenti,id_combustible)VALUES(?,?,?,?,?,?,?,NULL)',
            [$d['fecha']??date('Y-m-d'),$d['id_unidad'],$d['id_conductor']??null,$hi,$hf??null,$hh,$d['horas_ralenti']??0]);
        $asig=run_assignment_maq((int)$d['id_unidad'],$hi,$hf,(int)$id);
        jout(['id_control_dia'=>(int)$id,'horas_horometro'=>$hh,'asignacion'=>$asig],201);
    }
    if($r1==='dias'&&$r2!==''&&$m==='PUT'){
        $id=(int)$r2;$d=jbody();
        $hi=(float)($d['horometro_inicio']??0);$hf=(float)($d['horometro_final']??0);
        $hh=$hf>0?round($hf-$hi,2):null;
        qrows('UPDATE retro_control_dia SET fecha=?,id_unidad=?,id_conductor=?,horometro_inicio=?,horometro_final=?,horas_horometro=?,horas_ralenti=? WHERE id_control_dia=?',
            [$d['fecha'],$d['id_unidad'],$d['id_conductor']??null,$hi,$hf??null,$hh,$d['horas_ralenti']??0,$id]);
        jout(['ok'=>true,'horas_horometro'=>$hh]);
    }
    if($r1==='dias'&&$r2!==''&&$m==='DELETE'){
        qrows('DELETE FROM retro_control_actividad WHERE id_control_dia=?',[(int)$r2]);
        qrows('DELETE FROM retro_control_dia WHERE id_control_dia=?',[(int)$r2]);
        jout(['ok'=>true]);
    }
    if($r1==='actividades'&&$m==='GET'){
        $id=gget('id_control_dia');if(!$id)jout(['error'=>'id_control_dia requerido'],400);
        jout(qall('SELECT rca.*,ar.actividad,ar.factor_carga AS factor_carga_ref,ar.tipo_consumo FROM retro_control_actividad rca JOIN actividades_retro ar ON rca.id_actividad=ar.id_actividad WHERE rca.id_control_dia=? ORDER BY rca.hora_inicio',[$id]));
    }
    if($r1==='actividades'&&$m==='POST'){
        $d=jbody();
        $hi=$d['hora_inicio']??null;$hf=$d['hora_fin']??null;
        $th=($hi&&$hf)?round((strtotime($hf)-strtotime($hi))/3600,2):null;
        $act=qone('SELECT factor_carga FROM actividades_retro WHERE id_actividad=?',[$d['id_actividad']]);
        $ceq=$th!==null&&$act?round($th*(float)$act['factor_carga'],2):null;
        $id=qexec('INSERT INTO retro_control_actividad(id_control_dia,id_actividad,observacion,hora_inicio,hora_fin,total_hora)VALUES(?,?,?,?,?,?)',
            [$d['id_control_dia'],$d['id_actividad'],$d['observacion']??null,$hi,$hf,$th]);
        jout(['id_control_activ'=>(int)$id,'total_hora'=>$th],201);
    }
    if($r1==='actividades'&&$r2!==''&&$m==='DELETE'){
        qrows('DELETE FROM retro_control_actividad WHERE id_control_activ=?',[(int)$r2]);
        jout(['ok'=>true]);
    }
    if($r1==='asignar-combustible'&&$m==='POST'){
        $d=jbody();
        $id_dia=(int)($d['id_control_dia']??0);$id_comb=(int)($d['id_combustible']??0);
        if(!$id_dia||!$id_comb)jout(['error'=>'Parámetros requeridos'],400);
        qrows('UPDATE retro_control_dia SET id_combustible=? WHERE id_control_dia=?',[$id_comb,$id_dia]);
        jout(['ok'=>true]);
    }
    // Compras para asignar a maquinaria (por unidad + rango horómetro)
    if($r1==='compras-para-asignar'){
        $id_u=gget('id_unidad');$h_ini=(float)(gget('h_ini',0));$h_fin=(float)(gget('h_fin',0));
        if(!$id_u)jout(['error'=>'id_unidad requerido'],400);
        $compras=qall(
            "SELECT cc.id_combustible,cc.fecha,cc.nro_comprobante,cc.cantidad_gll,
                    cc.km_vehiculo AS horometro_compra,cc.precio_unitario,cc.total,
                    CASE WHEN cc.km_vehiculo>=? AND cc.km_vehiculo<=? THEN 1 ELSE 0 END AS en_rango
             FROM compra_combustible cc
             WHERE cc.id_unidad=? AND cc.tipo_combustible='PETROLEO' AND cc.km_vehiculo IS NOT NULL
             ORDER BY ABS(cc.km_vehiculo-?) ASC,cc.fecha DESC LIMIT 15",
            [$h_ini,$h_fin>0?$h_fin:999999,$id_u,$h_fin>0?$h_fin:$h_ini]
        );
        jout($compras);
    }
}

// ════════════════════════════════════════════════════════════
// GRUPOS ELECTRÓGENOS
// ════════════════════════════════════════════════════════════
if($r0==='ge'){
    // PUT /api/ge/registros/{id} — editar registro
    if($r1==='registros'&&$r2!==''&&is_numeric($r2)&&$m==='PUT'){
        $id = (int)$r2;
        $d  = jbody();
        $idU = (int)$d['id_unidad'];

        // Datos de la unidad
        $u   = qone('SELECT capacidad_tanque,potencia_kw,consumo_nominal_gal_hora FROM unidad WHERE id_unidad=?',[$idU]);
        $cap = (float)($u['capacidad_tanque']??0);
        $pkw = (float)($u['potencia_kw']??0);
        $nom = (float)($u['consumo_nominal_gal_hora']??0);

        $ci     = (float)($d['ci_porcentaje']??0);
        $cf     = (float)($d['cf_porcentaje']??0);
        $gllEch = (float)($d['galones_echados']??0);
        $hAct   = (float)($d['horometro']??0);

        // Horómetro anterior = penúltimo registro del mismo GE
        $prev = qone('SELECT horometro FROM consumo_grupo_electrogeno WHERE id_unidad=? AND id_registro!=? AND horometro IS NOT NULL ORDER BY fecha DESC,hora DESC LIMIT 1',[$idU,$id]);
        $hAnt = (float)($prev['horometro']??0);
        $horas = ($hAct>0&&$hAnt>0&&$hAct>$hAnt) ? round($hAct-$hAnt,2) : null;

        $gllCons = ($cap>0&&$ci>$cf) ? round($cap*($ci-$cf)/100,2) : ($gllEch>0?$gllEch:null);
        $gph  = ($horas&&$horas>0&&$gllCons&&$gllCons>0) ? round($gllCons/$horas,2) : null;
        $fc   = ($gph&&$nom>0) ? round($gph/$nom,2) : null;
        $kwh  = ($horas&&$pkw>0&&$fc) ? round($pkw*$horas*$fc,2) : ($horas&&$pkw>0?round($pkw*$horas,2):null);

        // Guardar valores anteriores para actualizar kardex
        $ant = qone('SELECT galones_echados,galones_consumidos,id_combustible FROM consumo_grupo_electrogeno WHERE id_registro=?',[$id]);
        $gllKardexAnt = ((float)($ant['galones_echados']??0))>0 ? (float)$ant['galones_echados'] : (float)($ant['galones_consumidos']??0);

        qrows('UPDATE consumo_grupo_electrogeno SET fecha=?,hora=?,id_unidad=?,ci_porcentaje=?,cf_porcentaje=?,galones_echados=?,horometro=?,horas_trabajadas=?,galones_consumidos=?,consumo_gal_hora=?,factor_carga=?,kwh_estimados=? WHERE id_registro=?',
            [$d['fecha'],$d['hora'],$idU,$ci,$cf,$gllEch,$hAct,$horas,$gllCons,$gph,$fc,$kwh,$id]);

        // Actualizar kardex: galones que salen del bidón
        $gllKardexNew = $gllEch>0 ? $gllEch : ($gllCons??0);
        $kex = qone("SELECT id_kardex FROM kardex_combustible WHERE id_unidad=? AND tipo_movimiento='SALIDA' AND observacion LIKE ? ORDER BY id_kardex DESC LIMIT 1",[$idU,'%turno '.$d['fecha'].'%']);

        if($kex){
            // Ya existía registro en kardex — actualizar galones y recalcular saldos
            if(abs($gllKardexNew-$gllKardexAnt)>0.01){
                qrows('UPDATE kardex_combustible SET galones=? WHERE id_kardex=?',[$gllKardexNew,$kex['id_kardex']]);
                // Recalcular saldos solo desde ese id_kardex en adelante
                $saldo_prev=qone('SELECT saldo_gll FROM kardex_combustible WHERE id_kardex < ? ORDER BY id_kardex DESC LIMIT 1',[$kex['id_kardex']]);
                $ksal=(float)($saldo_prev['saldo_gll']??0);
                $kmovs=qall('SELECT id_kardex,tipo_movimiento,galones FROM kardex_combustible WHERE id_kardex >= ? ORDER BY id_kardex ASC',[$kex['id_kardex']]);
                foreach($kmovs as $km){
                    $ksal+=$km['tipo_movimiento']==='ENTRADA'?(float)$km['galones']:-(float)$km['galones'];
                    qrows('UPDATE kardex_combustible SET saldo_gll=? WHERE id_kardex=?',[round($ksal,2),$km['id_kardex']]);
                }
            }
        } elseif($gllKardexNew>0){
            // No existía — crear SALIDA en kardex
            $id_comb=$ant['id_combustible']??null;
            reg_kardex_ge($idU,'SALIDA',$gllKardexNew,$id_comb,'Consumo turno '.$d['fecha']);
        }

        jout(['ok'=>true,'horas_trabajadas'=>$horas,'galones_consumidos'=>$gllCons,'consumo_gal_hora'=>$gph,'factor_carga'=>$fc,'kwh_estimados'=>$kwh]);
    }

    // DELETE /api/ge/registros/{id}
    if($r1==='registros'&&$r2!==''&&is_numeric($r2)&&$m==='DELETE'){
        $id = (int)$r2;
        $reg = qone('SELECT id_unidad,galones_echados,galones_consumidos,fecha FROM consumo_grupo_electrogeno WHERE id_registro=?',[$id]);
        if($reg){
            // Quitar del kardex
            $gll = ((float)($reg['galones_echados']??0))>0 ? (float)$reg['galones_echados'] : (float)($reg['galones_consumidos']??0);
            if($gll>0){
                $kex=qone("SELECT id_kardex FROM kardex_combustible WHERE id_unidad=? AND tipo_movimiento='SALIDA' AND observacion LIKE ? ORDER BY id_kardex DESC LIMIT 1",[(int)$reg['id_unidad'],'%turno '.$reg['fecha'].'%']);
                if($kex){
                    $id_kardex_del = $kex['id_kardex'];
                    $saldo_prev_del = qone('SELECT saldo_gll FROM kardex_combustible WHERE id_kardex < ? ORDER BY id_kardex DESC LIMIT 1',[$id_kardex_del]);
                    qrows('DELETE FROM kardex_combustible WHERE id_kardex=?',[$id_kardex_del]);
                    // Recalcular saldos solo desde el siguiente registro en adelante
                    $ksal_del=(float)($saldo_prev_del['saldo_gll']??0);
                    $kmovs_del=qall('SELECT id_kardex,tipo_movimiento,galones FROM kardex_combustible WHERE id_kardex > ? ORDER BY id_kardex ASC',[$id_kardex_del]);
                    foreach($kmovs_del as $km){
                        $ksal_del+=$km['tipo_movimiento']==='ENTRADA'?(float)$km['galones']:-(float)$km['galones'];
                        qrows('UPDATE kardex_combustible SET saldo_gll=? WHERE id_kardex=?',[round($ksal_del,2),$km['id_kardex']]);
                    }
                }
            }
            qrows('DELETE FROM consumo_grupo_electrogeno WHERE id_registro=?',[$id]);
        }
        jout(['ok'=>true]);
    }

    if($r1==='registros'&&$m==='GET'){
        $sql='SELECT cge.*,u.placa FROM consumo_grupo_electrogeno cge JOIN unidad u ON cge.id_unidad=u.id_unidad WHERE 1=1';
        $p=[];
        if($v=gget('id_unidad')){$sql.=' AND cge.id_unidad=?';$p[]=$v;}
        if($v=gget('fecha_desde')){$sql.=' AND cge.fecha>=?';$p[]=$v;}
        if($v=gget('fecha_hasta')){$sql.=' AND cge.fecha<=?';$p[]=$v;}
        $sql.=' ORDER BY cge.fecha DESC,cge.hora DESC LIMIT 500';
        jout(qall($sql,$p));
    }
    if($r1==='registros'&&$m==='POST'){
        $d    = jbody();
        $idU  = (int)$d['id_unidad'];
        $fecha= $d['fecha']??date('Y-m-d');
        $hora = $d['hora']??date('H:i:s');

        // Datos de la unidad
        $u   = qone('SELECT capacidad_tanque,potencia_kw,consumo_nominal_gal_hora FROM unidad WHERE id_unidad=?',[$idU]);
        $cap = (float)($u['capacidad_tanque']??0);
        $pkw = (float)($u['potencia_kw']??0);
        $nom = (float)($u['consumo_nominal_gal_hora']??0);

        // Porcentajes de tanque y galones echados al GE (desde el bidón)
        $ci     = (float)($d['ci_porcentaje']??0);
        $cf     = (float)($d['cf_porcentaje']??0);
        $gllEch = (float)($d['galones_echados']??0);

        // Horómetro anterior del mismo GE
        $prev = qone('SELECT horometro FROM consumo_grupo_electrogeno WHERE id_unidad=? AND horometro IS NOT NULL ORDER BY fecha DESC,hora DESC LIMIT 1',[$idU]);
        $hAnt = (float)($prev['horometro']??0);
        $hAct = (float)($d['horometro']??0);

        // Horas trabajadas = diferencia de horómetro
        $horas = ($hAct > 0 && $hAnt > 0 && $hAct > $hAnt) ? round($hAct - $hAnt, 2) : null;

        // Galones consumidos = capacidad × (CI% - CF%) / 100
        // Si el usuario echó galones (gllEch > 0), esos galones son lo que se sacó del bidón
        // Los galones consumidos = diferencia de nivel del tanque del GE
        $gllCons = ($cap > 0 && $ci > 0 && $cf >= 0 && $ci > $cf)
            ? round($cap * ($ci - $cf) / 100, 2)
            : ($gllEch > 0 ? $gllEch : null);

        // Consumo gal/hora
        $gph = ($horas && $horas > 0 && $gllCons && $gllCons > 0)
            ? round($gllCons / $horas, 2)
            : null;

        // Factor de carga = gal/hora_real / gal/hora_nominal
        $fc = ($gph && $nom > 0)
            ? round($gph / $nom, 2)
            : null;

        // kWh estimados = potencia_kw × horas × factor_carga
        $kwh = ($horas && $pkw > 0 && $fc)
            ? round($pkw * $horas * $fc, 2)
            : ($horas && $pkw > 0 ? round($pkw * $horas, 2) : null);

        // Combustible asociado: último ENTRADA del kardex global del bidón
        $kard    = qone("SELECT id_combustible FROM kardex_combustible WHERE tipo_movimiento='ENTRADA' ORDER BY fecha DESC,id_kardex DESC LIMIT 1");
        $id_comb = $kard['id_combustible'] ?? null;

        // Insertar registro de consumo
        $id = qexec(
            'INSERT INTO consumo_grupo_electrogeno(fecha,hora,id_unidad,ci_porcentaje,cf_porcentaje,galones_echados,horometro,horas_trabajadas,galones_consumidos,consumo_gal_hora,factor_carga,kwh_estimados,id_combustible)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [$fecha,$hora,$idU,$ci,$cf,$gllEch,$hAct,$horas,$gllCons,$gph,$fc,$kwh,$id_comb]
        );

        // Registrar SALIDA en kardex del bidón compartido
        // Excluir MEBA del kardex del bidón
        $u_placa = $u['placa'] ?? '';
        $gllKardex = $gllEch > 0 ? $gllEch : ($gllCons ?? 0);
        if ($gllKardex > 0 && $u_placa !== 'MEBA') {
            reg_kardex_ge($idU, 'SALIDA', $gllKardex, $id_comb,
                'Consumo ' . $u_placa . ' turno ' . $fecha . ($horas ? ' · '.$horas.'h' : ''),
                $fecha . ' ' . $hora);
        }

        jout([
            'id_registro'      => (int)$id,
            'horas_trabajadas' => $horas,
            'galones_consumidos'=> $gllCons,
            'galones_echados'  => $gllEch,
            'consumo_gal_hora' => $gph,
            'factor_carga'     => $fc,
            'kwh_estimados'    => $kwh,
            'kardex_registrado'=> $gllKardex > 0,
            'saldo_bidon'      => (float)(qval('SELECT saldo_gll FROM kardex_combustible ORDER BY id_kardex DESC LIMIT 1')??0),
        ], 201);
    }
    if($r1==='kardex')jout(qall('SELECT k.*,cc.nro_comprobante FROM kardex_combustible k LEFT JOIN compra_combustible cc ON k.id_combustible=cc.id_combustible WHERE k.id_unidad=? ORDER BY k.fecha DESC LIMIT 100',[gget('id_unidad')??0]));
    if($r1==='kardex-entrada'&&$m==='POST'){
        $d=jbody();
        reg_kardex_ge((int)$d['id_unidad'],'ENTRADA',(float)$d['galones'],$d['id_combustible']??null,$d['observacion']??'Entrada bidón');
        jout(['ok'=>true]);
    }
    // POST /api/ge/limpiar-desde-id — elimina todo anterior al id_kardex dado y recalcula
    if($r1==='limpiar-desde-id'&&$m==='POST'){
        $d      = jbody();
        $id_ini = (int)($d['id_kardex'] ?? 0);
        if($id_ini <= 0) jout(['ok'=>false,'msg'=>'id_kardex requerido'], 400);

        $n_del = (int)(qval('SELECT COUNT(*) FROM kardex_combustible WHERE id_kardex < ?', [$id_ini]) ?? 0);
        qrows('DELETE FROM kardex_combustible WHERE id_kardex < ?', [$id_ini]);

        // Recalcular saldos desde cero
        $movs = qall('SELECT id_kardex,tipo_movimiento,galones FROM kardex_combustible ORDER BY fecha ASC,id_kardex ASC');
        $sal  = 0;
        foreach($movs as $mv){
            $sal += $mv['tipo_movimiento']==='ENTRADA' ? (float)$mv['galones'] : -(float)$mv['galones'];
            qrows('UPDATE kardex_combustible SET saldo_gll=? WHERE id_kardex=?', [round($sal,2), $mv['id_kardex']]);
        }

        $saldo_final = (float)(qval('SELECT saldo_gll FROM kardex_combustible ORDER BY id_kardex DESC LIMIT 1') ?? 0);
        jout(['ok'=>true,'eliminados'=>$n_del,'movimientos'=>count($movs),'saldo_final'=>$saldo_final]);
    }

    // POST /api/ge/limpiar-kardex-pre-mayo
    // Elimina todos los movimientos del kardex anteriores a la primera ENTRADA de mayo
    // y recalcula los saldos en cadena desde ese punto
    if($r1==='limpiar-kardex-pre-mayo'&&$m==='POST'){
        // 1. Encontrar el id_kardex de la primera ENTRADA en mayo o posterior
        $primera = qone(
            "SELECT id_kardex, fecha FROM kardex_combustible
             WHERE tipo_movimiento = 'ENTRADA'
               AND fecha >= '2025-05-01'
             ORDER BY fecha ASC, id_kardex ASC
             LIMIT 1"
        );
        if(!$primera){
            jout(['ok'=>false,'msg'=>'No se encontró ninguna ENTRADA desde mayo 2025']);
            return;
        }
        $id_desde = (int)$primera['id_kardex'];

        // 2. Contar cuántos registros se van a eliminar
        $n_eliminar = (int)(qval(
            'SELECT COUNT(*) FROM kardex_combustible WHERE id_kardex < ?',
            [$id_desde]
        ) ?? 0);

        // 3. Eliminar todos los anteriores a ese id_kardex
        qrows('DELETE FROM kardex_combustible WHERE id_kardex < ?', [$id_desde]);

        // 4. Recalcular todos los saldos desde cero (empezando en 0)
        $movs = qall(
            'SELECT id_kardex, tipo_movimiento, galones
             FROM kardex_combustible
             ORDER BY fecha ASC, id_kardex ASC'
        );
        $saldo = 0;
        foreach($movs as $mv){
            $saldo += $mv['tipo_movimiento'] === 'ENTRADA'
                ? (float)$mv['galones']
                : -(float)$mv['galones'];
            qrows(
                'UPDATE kardex_combustible SET saldo_gll = ? WHERE id_kardex = ?',
                [round($saldo, 2), $mv['id_kardex']]
            );
        }

        // 5. Devolver resumen
        $saldo_final = (float)(qval(
            'SELECT saldo_gll FROM kardex_combustible ORDER BY fecha DESC, id_kardex DESC LIMIT 1'
        ) ?? 0);
        jout([
            'ok'            => true,
            'eliminados'    => $n_eliminar,
            'primera_fecha' => $primera['fecha'],
            'movimientos'   => count($movs),
            'saldo_final'   => $saldo_final,
        ]);
    }

        // POST /api/ge/reconstruir-kardex
    // Reconstruir kardex desde mayo en adelante, partiendo del saldo del kardex importado
    if($r1==='reconstruir-kardex'&&$m==='POST'){
        // 1. Eliminar solo los registros que estén DESPUÉS del último id importado (398)
        //    Los registros 193-398 fueron importados manualmente y son correctos — no tocarlos
        $ultimo_importado = 398;
        qrows('DELETE FROM kardex_combustible WHERE id_kardex > ?', [$ultimo_importado]);

        // 2. Tomar el saldo del último registro importado como punto de partida
        $base = qone('SELECT saldo_gll FROM kardex_combustible WHERE id_kardex = ?', [$ultimo_importado]);
        // Si no existe el 398, usar el último disponible
        if(!$base) $base = qone('SELECT saldo_gll FROM kardex_combustible ORDER BY id_kardex DESC LIMIT 1');
        // Inyectar ese saldo en la función reg_kardex_ge temporalmente no es posible,
        // así que recalculamos todos los saldos al final

        // 3. Insertar ENTRADAS desde compras GE desde mayo en adelante
        //    Incluye notas de crédito (galones negativos = reducción del bidón)
        $compras=qall(
            "SELECT cc.id_combustible, cc.fecha, cc.id_unidad, cc.cantidad_gll,
                    cc.tipo_comprobante, cc.nro_comprobante, u.placa
             FROM compra_combustible cc
             JOIN unidad u ON cc.id_unidad=u.id_unidad
             WHERE u.tipo_unidad = 'GRUPO ELECTROGENO'
               AND u.placa      != 'MEBA'
               AND cc.tipo_combustible != 'GASOLINA'
               AND cc.fecha     >= '2025-05-01'
             ORDER BY cc.fecha ASC, cc.id_combustible ASC"
        );

        $n_entradas = 0;
        foreach($compras as $cp){
            $gll   = (float)$cp['cantidad_gll'];
            $tipo  = $gll >= 0 ? 'ENTRADA' : 'SALIDA'; // nota de crédito = negativo = SALIDA
            $gll_a = abs($gll);
            $obs   = 'Compra '.($cp['tipo_comprobante']??'').' '.($cp['nro_comprobante']??'').' · '.$cp['placa'];
            qexec(
                'INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll)VALUES(?,?,?,?,?,?,0)',
                [($cp['fecha']??date('Y-m-d')).' 00:00:00', $cp['id_unidad'], $tipo, $gll_a, $cp['id_combustible'], $obs]
            );
            $n_entradas++;
        }

        // 4. Insertar SALIDAS desde consumo_grupo_electrogeno desde mayo
        $consumos=qall(
            "SELECT cge.*, u.placa FROM consumo_grupo_electrogeno cge
             JOIN unidad u ON cge.id_unidad=u.id_unidad
             WHERE u.placa != 'MEBA'
               AND cge.fecha >= '2025-05-01'
               AND (cge.galones_echados > 0 OR cge.galones_consumidos > 0)
             ORDER BY cge.fecha ASC, cge.hora ASC"
        );
        $n_salidas = 0;
        foreach($consumos as $cg){
            $gll = (float)($cg['galones_echados']??0) > 0
                ? (float)$cg['galones_echados']
                : (float)($cg['galones_consumidos']??0);
            if($gll <= 0) continue;
            $obs = 'Consumo '.$cg['placa'].' turno '.$cg['fecha'].($cg['horas_trabajadas']?' · '.$cg['horas_trabajadas'].'h':'');
            qexec(
                'INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll)VALUES(?,?,?,?,?,?,0)',
                [($cg['fecha']??date('Y-m-d')).' '.($cg['hora']??'00:00:00'), $cg['id_unidad'], 'SALIDA', $gll, $cg['id_combustible']??null, $obs]
            );
            $n_salidas++;
        }

        // 5. Recalcular todos los saldos en cadena desde el inicio del kardex
        $movs = qall('SELECT id_kardex,tipo_movimiento,galones FROM kardex_combustible ORDER BY fecha ASC,id_kardex ASC');
        $sal  = 0;
        foreach($movs as $mv){
            $sal += $mv['tipo_movimiento']==='ENTRADA' ? (float)$mv['galones'] : -(float)$mv['galones'];
            qrows('UPDATE kardex_combustible SET saldo_gll=? WHERE id_kardex=?', [round($sal,2), $mv['id_kardex']]);
        }

        $saldo_final = (float)(qval('SELECT saldo_gll FROM kardex_combustible ORDER BY id_kardex DESC LIMIT 1')??0);
        jout(['ok'=>true,'entradas'=>$n_entradas,'salidas'=>$n_salidas,'saldo_final'=>$saldo_final,'total_movs'=>count($movs)]);
    }    }

    if($r1==='saldo-bidon'){
        // El bidón es un reservorio compartido entre todos los GE
        // El saldo real = último saldo del kardex de cualquier GE (todos comparten el mismo bidón)
        $id=gget('id_unidad');

        // El saldo correcto es el del registro con mayor id_kardex (último insertado)
        // NO ordenar por fecha ya que puede haber registros con misma fecha
        $row=qone('SELECT saldo_gll,fecha FROM kardex_combustible ORDER BY id_kardex DESC LIMIT 1');
        $saldo=(float)($row['saldo_gll']??0);

        // Cuántos galones se necesitan para 7 días (basado en consumo promedio de la última semana)
        $consumo_semana=qone(
            'SELECT COALESCE(SUM(galones_consumidos),0) AS total FROM consumo_grupo_electrogeno
             WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)'
        );
        $prom_dia=(float)($consumo_semana['total']??0)/7;

        jout([
            'saldo_gll'   => $saldo,
            'alerta'      => $saldo <= 10,
            'alerta_leve' => $saldo <= 20,
            'prom_dia_gll'=> round($prom_dia,2),
            'dias_restantes'=> $prom_dia>0 ? round($saldo/$prom_dia,1) : null,
        ]);
    }
    if($r1==='causa-raiz'&&$r2!==''){
        $base=qone('SELECT cge.*,u.placa FROM consumo_grupo_electrogeno cge JOIN unidad u ON cge.id_unidad=u.id_unidad WHERE cge.id_registro=?',[(int)$r2]);
        if(!$base)jout(['error'=>'No encontrado'],404);
        $eq=qall('SELECT po.*,ep.nombre_equipo,ep.proceso,ep.hp,ep.kWh_hora,ROUND(ep.kWh_hora*po.horas_trabajo,2) AS kwh_consumidos,t.nombre_completo AS registrador_nombre FROM procesos_operacion po JOIN equipos_proceso ep ON po.id_equipo=ep.id_equipo LEFT JOIN trabajadores t ON po.id_trabajador=t.id_trabajador WHERE po.id_registro=? ORDER BY ep.kWh_hora DESC',[(int)$r2]);
        jout(array_merge($base,['equipos_activos'=>$eq]));
    }

// ════════════════════════════════════════════════════════════
// MANTENIMIENTO
// ════════════════════════════════════════════════════════════
if($r0==='mantenimiento'){
    if($r1==='historial'&&$m==='GET'){
        $sql='SELECT hm.*,u.placa,u.tipo_unidad FROM historial_mantenimiento hm JOIN unidad u ON hm.id_unidad=u.id_unidad WHERE 1=1';
        $p=[];
        if($v=gget('id_unidad')){$sql.=' AND hm.id_unidad=?';$p[]=$v;}
        if($v=gget('tipo_mantenimiento')){$sql.=' AND hm.tipo_mantenimiento=?';$p[]=$v;}
        if($v=gget('categoria')){$sql.=' AND hm.tipo_mant_categoria=?';$p[]=$v;}
        $sql.=' ORDER BY hm.fecha_ejecucion DESC LIMIT 500';
        jout(qall($sql,$p));
    }
    if($r1==='historial'&&$m==='POST'){
        $d=jbody();
        $cat=$d['tipo_mant_categoria']??'CORRECTIVO'; // PREVENTIVO o CORRECTIVO
        $costo_rep=(float)($d['costo_repuestos']??0);
        $costo_mo=(float)($d['costo_mano_obra']??0);
        $id=qexec('INSERT INTO historial_mantenimiento(id_unidad,fecha_ejecucion,tipo_mantenimiento,tipo_mant_categoria,km_registro,horometro_registro,descripcion_trabajo,marca,costo_repuestos,costo_mano_obra,costo_total_soles)VALUES(?,?,?,?,?,?,?,?,?,?,?)',
            [$d['id_unidad'],$d['fecha_ejecucion'],$d['tipo_mantenimiento'],$cat,$d['km_registro']??null,$d['horometro_registro']??null,$d['descripcion_trabajo']??'',$d['marca']??null,$costo_rep,$costo_mo,round($costo_rep+$costo_mo,2)]);
        jout(['id_mantenimiento'=>(int)$id],201);
    }
    if($r1==='historial'&&$r2!==''&&$m==='PUT'){
        $d=jbody();
        $cat=$d['tipo_mant_categoria']??'CORRECTIVO';
        $costo_rep=(float)($d['costo_repuestos']??0);
        $costo_mo=(float)($d['costo_mano_obra']??0);
        qrows('UPDATE historial_mantenimiento SET id_unidad=?,fecha_ejecucion=?,tipo_mantenimiento=?,tipo_mant_categoria=?,km_registro=?,horometro_registro=?,descripcion_trabajo=?,marca=?,costo_repuestos=?,costo_mano_obra=?,costo_total_soles=? WHERE id_mantenimiento=?',
            [$d['id_unidad'],$d['fecha_ejecucion'],$d['tipo_mantenimiento'],$cat,$d['km_registro']??null,$d['horometro_registro']??null,$d['descripcion_trabajo']??'',$d['marca']??null,$costo_rep,$costo_mo,round($costo_rep+$costo_mo,2),(int)$r2]);
        jout(['ok'=>true]);
    }
    if($r1==='historial'&&$r2!==''&&$m==='DELETE'){
        qrows('DELETE FROM historial_mantenimiento WHERE id_mantenimiento=?',[(int)$r2]);
        jout(['ok'=>true]);
    }
    if($r1==='alertas'){
        // KM actual por unidad (flota)
        $kmMap=array_column(qall("SELECT id_unidad,MAX(km_retorno) AS v FROM control_flota WHERE km_retorno IS NOT NULL GROUP BY id_unidad"),'v','id_unidad');
        // Horómetro actual por unidad (maquinaria)
        $hMap=array_column(qall("SELECT id_unidad,MAX(horometro_final) AS v FROM retro_control_dia WHERE horometro_final IS NOT NULL GROUP BY id_unidad"),'v','id_unidad');
        $hgeMap=array_column(qall("SELECT id_unidad,MAX(horometro) AS v FROM consumo_grupo_electrogeno WHERE horometro IS NOT NULL GROUP BY id_unidad"),'v','id_unidad');

        // Último mantenimiento por unidad+tarea (usando tarea = campo en plan_mantenimiento)
        $ultRows=qall("SELECT id_unidad,tipo_mantenimiento,MAX(km_registro) AS km_ult,MAX(horometro_registro) AS h_ult,MAX(fecha_ejecucion) AS fecha_ult FROM historial_mantenimiento WHERE tipo_mant_categoria='PREVENTIVO' OR tipo_mantenimiento IN (SELECT tarea FROM plan_mantenimiento) GROUP BY id_unidad,tipo_mantenimiento");
        $mantMap=[];
        foreach($ultRows as $u2) $mantMap[$u2['id_unidad']][$u2['tipo_mantenimiento']]=$u2;

        $planes=qall("SELECT pm.*,u.placa,u.tipo_unidad FROM plan_mantenimiento pm JOIN unidad u ON pm.id_unidad=u.id_unidad ORDER BY u.placa,pm.tarea");
        $result=[];
        foreach($planes as $p2){
            $id_u=(int)$p2['id_unidad'];
            $tarea=$p2['tarea'];
            $fkm=(float)($p2['frecuencia_km']??0);
            $fh=(float)($p2['frecuencia_horas']??0);
            $km_act=(float)($kmMap[$id_u]??0);
            $h_act=(float)($hMap[$id_u]??$hgeMap[$id_u]??0);

            // Buscar último mantenimiento de este tipo para esta unidad
            $ult=$mantMap[$id_u][$tarea]??null;
            $km_ult=(float)($ult['km_ult']??0);
            $h_ult=(float)($ult['h_ult']??0);
            $fecha_ult=$ult['fecha_ult']??null;

            // Si nunca se hizo: delta = km_actual (desde 0)
            $d_km=$fkm>0 ? ($km_act-$km_ult) : null;
            $d_h=$fh>0  ? ($h_act-$h_ult)  : null;

            // Porcentaje del intervalo consumido
            $pct_km=$fkm>0&&$d_km!==null ? round($d_km/$fkm*100,1) : 0;
            $pct_h=$fh>0&&$d_h!==null   ? round($d_h/$fh*100,1)   : 0;
            $pct=max($pct_km,$pct_h);

            // Estado
            $estado=$pct>=100?'VENCIDO':($pct>=90?'CRITICO':($pct>=75?'PROXIMO':'OK'));

            // Próximo mantenimiento en km/horas
            $prox_km=$fkm>0 ? round($km_ult+$fkm,0) : null;
            $prox_h=$fh>0  ? round($h_ult+$fh,1)  : null;
            $falta_km=$fkm>0 ? max(0,round($prox_km-$km_act,0)) : null;
            $falta_h=$fh>0  ? max(0,round($prox_h-$h_act,1))  : null;

            $result[]=array_merge($p2,[
                'km_actual'=>$km_act,'h_actual'=>$h_act,
                'km_ultimo'=>$km_ult,'h_ultimo'=>$h_ult,'fecha_ultimo'=>$fecha_ult,
                'km_proximo'=>$prox_km,'h_proximo'=>$prox_h,
                'delta_km'=>$d_km,'delta_h'=>$d_h,
                'pct'=>$pct,'pct_km'=>$pct_km,'pct_h'=>$pct_h,
                'estado'=>$estado,
                'falta_km'=>$falta_km,'falta_h'=>$falta_h,
            ]);
        }
        usort($result,function($a,$b){ return $b['pct']<=>$a['pct']; });
        jout($result);
    }
    // GET /api/mantenimiento/analisis-repuestos?id_unidad=X&tipo=CAMBIO LLANTAS
    if($r1==='analisis-repuestos'){
        $id_u=gget('id_unidad');$tipo=gget('tipo');
        $sql="SELECT hm.*,u.placa,
                     LAG(hm.fecha_ejecucion) OVER (PARTITION BY hm.id_unidad,hm.tipo_mantenimiento ORDER BY hm.fecha_ejecucion) AS fecha_anterior,
                     LAG(hm.km_registro)     OVER (PARTITION BY hm.id_unidad,hm.tipo_mantenimiento ORDER BY hm.km_registro)     AS km_anterior,
                     DATEDIFF(hm.fecha_ejecucion, LAG(hm.fecha_ejecucion) OVER (PARTITION BY hm.id_unidad,hm.tipo_mantenimiento ORDER BY hm.fecha_ejecucion)) AS dias_duracion,
                     (hm.km_registro - LAG(hm.km_registro) OVER (PARTITION BY hm.id_unidad,hm.tipo_mantenimiento ORDER BY hm.km_registro)) AS km_duracion
              FROM historial_mantenimiento hm JOIN unidad u ON hm.id_unidad=u.id_unidad
              WHERE 1=1";
        $p=[];
        if($id_u){$sql.=' AND hm.id_unidad=?';$p[]=$id_u;}
        if($tipo){$sql.=' AND hm.tipo_mantenimiento=?';$p[]=$tipo;}
        $sql.=' ORDER BY hm.id_unidad,hm.fecha_ejecucion';
        $rows=qall($sql,$p);
        // Stats por unidad+tipo
        $stats=[];
        foreach($rows as $r3){
            if($r3['dias_duracion']===null)continue;
            $key=$r3['placa'].'|'.$r3['tipo_mantenimiento'];
            if(!isset($stats[$key]))$stats[$key]=['placa'=>$r3['placa'],'tipo'=>$r3['tipo_mantenimiento'],'n'=>0,'dias'=>[],'km'=>[],'costo'=>[]];
            $stats[$key]['n']++;
            if($r3['dias_duracion']>0)$stats[$key]['dias'][]=(int)$r3['dias_duracion'];
            if($r3['km_duracion']>0)$stats[$key]['km'][]=(float)$r3['km_duracion'];
            $stats[$key]['costo'][]=(float)$r3['costo_total_soles'];
        }
        $resumen=[];
        foreach($stats as $s){
            $resumen[]=[
                'placa'=>$s['placa'],'tipo'=>$s['tipo'],'n_cambios'=>$s['n'],
                'dias_prom'=>$s['dias']?round(array_sum($s['dias'])/count($s['dias']),0):null,
                'dias_min'=>$s['dias']?min($s['dias']):null,'dias_max'=>$s['dias']?max($s['dias']):null,
                'km_prom'=>$s['km']?round(array_sum($s['km'])/count($s['km']),0):null,
                'km_min'=>$s['km']?min($s['km']):null,'km_max'=>$s['km']?max($s['km']):null,
                'costo_prom'=>$s['costo']?round(array_sum($s['costo'])/count($s['costo']),2):null,
                'costo_total'=>round(array_sum($s['costo']),2),
            ];
        }
        jout(['detalle'=>$rows,'resumen'=>$resumen]);
    }
    if($r1==='documentos'&&$m==='GET'){
        // Auto-actualizar campo vigente (boolean) según fecha actual
        qrows('UPDATE documento_unidad SET vigente=0 WHERE fecha_vencimiento < CURDATE()');
        qrows('UPDATE documento_unidad SET vigente=1 WHERE fecha_vencimiento >= CURDATE()');
        // Solo mostrar el documento más reciente por tipo+unidad
        $uid_f = gget('id_unidad');
        $sql = "SELECT du.*,u.placa,u.tipo_unidad,
                       DATEDIFF(du.fecha_vencimiento,CURDATE()) AS dias_restantes
                FROM documento_unidad du
                JOIN unidad u ON du.id_unidad=u.id_unidad
                WHERE du.id_documento IN (
                    SELECT MAX(d2.id_documento)
                    FROM documento_unidad d2
                    GROUP BY d2.id_unidad, d2.tipo_documento
                )";
        $p=[];
        if($uid_f){$sql.=' AND du.id_unidad=?';$p[]=$uid_f;}
        $sql.=' ORDER BY dias_restantes ASC';
        jout(qall($sql,$p));
    }
    if($r1==='documentos'&&$m==='POST'){
        $d=jbody();
        $vence=$d['fecha_vencimiento']??date('Y-m-d');
        $vigente=strtotime($vence)>=strtotime(date('Y-m-d'))?1:0;
        $id=qexec('INSERT INTO documento_unidad(id_unidad,tipo_documento,fecha_emision,fecha_vencimiento,alerta_dias_antes,vigente)VALUES(?,?,?,?,?,?)',
            [$d['id_unidad'],$d['tipo_documento'],$d['fecha_emision']??null,$vence,$d['alerta_dias_antes']??30,$vigente]);
        jout(['id_documento'=>(int)$id],201);
    }
    if($r1==='documentos'&&$r2!==''&&$m==='DELETE'){qrows('DELETE FROM documento_unidad WHERE id_documento=?',[(int)$r2]);jout(['ok'=>true]);}
}

// ════════════════════════════════════════════════════════════
// DASHBOARD
// ════════════════════════════════════════════════════════════
if($r0==='dashboard'){
    if($r1==='kpis'){
        $fd  = gget('fecha_desde', date('Y-m-01'));
        $fh  = gget('fecha_hasta', date('Y-m-d'));
        $fgr = gget('id_grifo');   // filtro por grifo
        $fac = gget('tipo_actividad'); // filtro por tipo actividad

        // Viajes (con filtro de actividad si aplica)
        $sql_v = 'SELECT COUNT(*) AS n FROM control_flota WHERE fecha BETWEEN ? AND ?';
        $p_v   = [$fd,$fh];
        if($fac){$sql_v.=' AND tipo_actividad=?';$p_v[]=$fac;}
        $v = qone($sql_v,$p_v);

        // Combustible (con filtro de grifo si aplica)
        $sql_c = 'SELECT COALESCE(SUM(cantidad_gll),0) AS gll, COALESCE(SUM(total),0) AS soles FROM compra_combustible WHERE fecha BETWEEN ? AND ?';
        $p_c   = [$fd,$fh];
        if($fgr){$sql_c.=' AND id_grifo=?';$p_c[]=$fgr;}
        $c = qone($sql_c,$p_c);

        // Gasto por grifo (desglose)
        $por_grifo = qall(
            'SELECT g.razon_social AS grifo, g.id_grifo,
                    COALESCE(SUM(cc.total),0) AS total_soles,
                    COALESCE(SUM(cc.cantidad_gll),0) AS total_gll
             FROM compra_combustible cc
             JOIN grifo g ON cc.id_grifo=g.id_grifo
             WHERE cc.fecha BETWEEN ? AND ?
             GROUP BY g.id_grifo, g.razon_social
             ORDER BY total_soles DESC',
            [$fd,$fh]
        );

        $ad = (int)(qval("SELECT COUNT(*) FROM documento_unidad WHERE DATEDIFF(fecha_vencimiento,CURDATE())<=alerta_dias_antes AND vigente=1")??0);
        $sg = (float)(qval('SELECT saldo FROM movimientos_combustible ORDER BY id_movimiento DESC LIMIT 1')??0);

        jout([
            'viajes'       => (int)$v['n'],
            'total_galones'=> (float)$c['gll'],
            'total_soles'  => (float)$c['soles'],
            'alertas_doc'  => $ad,
            'saldo_grifo'  => $sg,
            'por_grifo'    => $por_grifo,
        ]);
    }
    // GET /api/dashboard/compras-por-unidad-fecha — línea temporal de tanqueos por unidad
    if($r1==='compras-por-unidad-fecha'){
        $fd  = gget('fecha_desde', date('Y-m-01'));
        $fh  = gget('fecha_hasta', date('Y-m-d'));
        $fgr = gget('id_grifo');
        $uid = gget('id_unidad');
        $sql = "SELECT cc.id_combustible, cc.fecha, cc.km_vehiculo, cc.cantidad_gll,
                       cc.total, cc.precio_unitario, cc.tipo_combustible, cc.tanqueo,
                       u.placa, u.id_unidad,
                       g.razon_social AS grifo_nombre,
                       (SELECT COUNT(*) FROM detalle_consumo dc WHERE dc.id_combustible=cc.id_combustible) AS n_viajes_asignados
                FROM compra_combustible cc
                JOIN unidad u ON cc.id_unidad=u.id_unidad
                LEFT JOIN grifo g ON cc.id_grifo=g.id_grifo
                WHERE cc.fecha BETWEEN ? AND ?
                  AND cc.id_unidad IS NOT NULL
                  AND cc.tipo_combustible='PETROLEO'";
        $p = [$fd,$fh];
        if($fgr){$sql.=' AND cc.id_grifo=?';$p[]=$fgr;}
        if($uid){$sql.=' AND cc.id_unidad=?';$p[]=$uid;}
        $sql .= ' ORDER BY cc.fecha, u.placa';
        jout(qall($sql,$p));
    }

    if($r1==='consumo-semanal'){
        // Flota y GE: compras directas
        $directo=qall("SELECT YEARWEEK(cc.fecha,3) AS semana,
                              MIN(cc.fecha) AS inicio_semana,MAX(cc.fecha) AS fin_semana_real,
                              SUM(cc.cantidad_gll) AS galones,SUM(cc.total) AS soles,
                              u.tipo_unidad
                       FROM compra_combustible cc
                       JOIN unidad u ON cc.id_unidad=u.id_unidad
                       WHERE cc.fecha>=DATE_SUB(CURDATE(),INTERVAL 8 WEEK)
                         AND u.tipo_unidad != 'MAQ. PESADA'
                       GROUP BY semana,u.tipo_unidad ORDER BY semana");
        // Maquinaria pesada: via retro_control_dia
        $maq=qall("SELECT YEARWEEK(rcd.fecha,3) AS semana,
                          MIN(rcd.fecha) AS inicio_semana,MAX(rcd.fecha) AS fin_semana_real,
                          SUM(cc.cantidad_gll) AS galones,SUM(cc.total) AS soles,
                          'MAQ. PESADA' AS tipo_unidad
                   FROM retro_control_dia rcd
                   JOIN compra_combustible cc ON rcd.id_combustible=cc.id_combustible
                   WHERE rcd.fecha>=DATE_SUB(CURDATE(),INTERVAL 8 WEEK)
                   GROUP BY semana ORDER BY semana");
        jout(array_merge($directo,$maq));
    }
    if($r1==='rendimiento-flota'){
        $fd=gget('fecha_desde',date('Y-m-01'));$fh=gget('fecha_hasta',date('Y-m-d'));
        // Siempre excluir el primer tanqueo de cada vehículo (c1 debe existir)
        // y filtrar diferencias de km razonables (entre 1 y 2000 km entre tanqueos)
        jout(qall(
            "SELECT u.placa, u.tipo_unidad,
                    COUNT(c2.id_combustible) AS n_tanqueos,
                    SUM(c2.km_vehiculo - c1.km_vehiculo) AS km_totales,
                    SUM(c2.cantidad_gll) AS gll_totales,
                    ROUND(
                        SUM(c2.km_vehiculo - c1.km_vehiculo)
                        / NULLIF(SUM(c2.cantidad_gll), 0)
                    , 2) AS km_por_galon,
                    ROUND(SUM(c2.total), 2) AS costo_combustible
             FROM compra_combustible c2
             JOIN unidad u ON c2.id_unidad = u.id_unidad

             JOIN compra_combustible c1
               ON c1.id_unidad   = c2.id_unidad
              AND c1.km_vehiculo = (
                    SELECT MAX(cx.km_vehiculo)
                    FROM compra_combustible cx
                    WHERE cx.id_unidad        = c2.id_unidad
                      AND cx.km_vehiculo      < c2.km_vehiculo
                      AND cx.tipo_combustible = 'PETROLEO'
                )
             WHERE c2.tipo_combustible = 'PETROLEO'
               AND c2.id_unidad IS NOT NULL
               AND c2.km_vehiculo IS NOT NULL
               AND c2.fecha BETWEEN ? AND ?

               AND (c2.km_vehiculo - c1.km_vehiculo) BETWEEN 1 AND 3000
             GROUP BY u.id_unidad, u.placa, u.tipo_unidad
             ORDER BY km_por_galon DESC",
            [$fd, $fh]
        ));
    }
    // Rendimiento diario desde detalle_consumo: km/galón por día con viajes asociados
    if($r1==='rendimiento-diario'){
        $fd=gget('fecha_desde',date('Y-m-01'));$fh=gget('fecha_hasta',date('Y-m-d'));
        $uid=gget('id_unidad');
        $fgr = gget('id_grifo');
        $fac = gget('tipo_actividad');
        $where='WHERE cf.fecha BETWEEN ? AND ? AND u.tipo_unidad=\'FLOTA\'';
        $p=[$fd,$fh];
        if($uid){$where.=' AND cf.id_unidad=?';$p[]=$uid;}
        if($fac){$where.=' AND cf.tipo_actividad=?';$p[]=$fac;}
        if($fgr){$where.=' AND cc.id_grifo=?';$p[]=$fgr;}
        // Por tanqueo: cada compra = un bloque de viajes hasta la siguiente tanqueada
        $bloques=qall("SELECT cc.id_combustible,cc.fecha AS fecha_tanqueo,cc.km_vehiculo AS km_tanqueo,
                              cc.cantidad_gll,cc.total AS costo_tanqueo,cc.precio_unitario,
                              u.placa,u.id_unidad,
                              SUM(cf.km_recorrido) AS km_viajes_bloque,
                              COUNT(cf.id_control) AS n_viajes,
                              ROUND(SUM(cf.km_recorrido)/NULLIF(cc.cantidad_gll,0),2) AS km_por_galon,
                              ROUND(cc.total/NULLIF(SUM(cf.km_recorrido),0),4) AS sol_por_km,
                              MIN(cf.fecha) AS fecha_primer_viaje,
                              MAX(cf.fecha) AS fecha_ultimo_viaje
                       FROM compra_combustible cc
                       JOIN unidad u ON cc.id_unidad=u.id_unidad
                       JOIN detalle_consumo dc ON dc.id_combustible=cc.id_combustible
                       JOIN control_flota cf ON dc.id_control=cf.id_control
                       $where
                         AND cf.km_recorrido > 0
                       GROUP BY cc.id_combustible,cc.fecha,cc.km_vehiculo,cc.cantidad_gll,cc.total,cc.precio_unitario,u.placa,u.id_unidad
                       HAVING SUM(cf.km_recorrido) > 0
                       ORDER BY u.placa,cc.fecha,cc.km_vehiculo",$p);

        // Por tipo de actividad — qué consume más
        $por_actividad=qall("SELECT cf.tipo_actividad,
                                    COUNT(cf.id_control) AS n_viajes,
                                    SUM(cf.km_recorrido) AS km_total,
                                    SUM(cc.cantidad_gll) AS gll_total,
                                    ROUND(SUM(cf.km_recorrido)/NULLIF(SUM(cc.cantidad_gll),0),2) AS km_por_galon,
                                    ROUND(SUM(cc.total),2) AS costo_total
                             FROM control_flota cf
                             JOIN detalle_consumo dc ON dc.id_control=cf.id_control
                             JOIN compra_combustible cc ON dc.id_combustible=cc.id_combustible
                             JOIN unidad u ON cf.id_unidad=u.id_unidad
                             WHERE cf.fecha BETWEEN ? AND ? AND cf.tipo_actividad IS NOT NULL AND cf.km_recorrido > 0
                             GROUP BY cf.tipo_actividad ORDER BY gll_total DESC",[$fd,$fh]);

        jout(['bloques'=>$bloques,'por_actividad'=>$por_actividad]);
    }
    // Viajes de un bloque (tanqueo) específico
    if($r1==='viajes-bloque'){
        $id_comb=gget('id_combustible');if(!$id_comb)jout(['error'=>'id_combustible requerido'],400);
        jout(qall("SELECT cf.*,u.placa,COALESCE(t.nombre_conductor,'') AS conductor_nombre,
                          r.destino,r.origen,dc.km_recorridos
                   FROM detalle_consumo dc
                   JOIN control_flota cf ON dc.id_control=cf.id_control
                   JOIN unidad u ON cf.id_unidad=u.id_unidad
                   LEFT JOIN conductor t ON cf.id_conductor=t.id_conductor
                   LEFT JOIN ruta r ON cf.id_ruta=r.id_ruta
                   WHERE dc.id_combustible=?
                   ORDER BY cf.fecha,cf.hora_salida",[$id_comb]));
    }
    if($r1==='rendimiento-viajes'){
        $fd=gget('fecha_desde',date('Y-m-01'));$fh=gget('fecha_hasta',date('Y-m-d'));
        $uid=gget('id_unidad');$rid=gget('id_ruta');
        $where='WHERE cf.fecha BETWEEN ? AND ?';$p=[$fd,$fh];
        if($uid){$where.=' AND cf.id_unidad=?';$p[]=$uid;}
        if($rid){$where.=' AND cf.id_ruta=?';$p[]=$rid;}
        $viajes=qall("SELECT cf.id_control,cf.fecha,cf.km_recorrido,cf.tipo_actividad,cf.observacion,
                             u.placa,u.tipo_unidad,COALESCE(t.nombre_conductor,'') AS conductor_nombre,
                             CONCAT(COALESCE(r.origen,''),' → ',COALESCE(r.destino,'')) AS ruta_label,r.km_esperado,
                             SUM(cc.cantidad_gll) AS gll_consumidos,
                             ROUND(SUM(cc.total),2) AS costo_estimado
                      FROM control_flota cf
                      JOIN unidad u ON cf.id_unidad=u.id_unidad
                      LEFT JOIN conductor t ON cf.id_conductor=t.id_conductor
                      LEFT JOIN ruta r ON cf.id_ruta=r.id_ruta
                      LEFT JOIN detalle_consumo dc ON dc.id_control=cf.id_control
                      LEFT JOIN compra_combustible cc ON dc.id_combustible=cc.id_combustible
                      $where
                      GROUP BY cf.id_control,cf.fecha,cf.km_recorrido,cf.tipo_actividad,cf.observacion,u.placa,u.tipo_unidad,conductor_nombre,ruta_label,r.km_esperado
                      ORDER BY cf.fecha DESC LIMIT 500",$p);
        $con=array_filter($viajes,function($v3){ return $v3['gll_consumidos']>0; });
        $n=count($con);$skm=array_sum(array_column(array_values($con),'km_recorrido'));
        $sg=array_sum(array_column(array_values($con),'gll_consumidos'));$sc=array_sum(array_column(array_values($con),'costo_estimado'));
        $por_ruta=[];
        foreach($viajes as $v4){
            $k=$v4['ruta_label']??($v4['tipo_actividad']??'Sin ruta');
            if(!isset($por_ruta[$k]))$por_ruta[$k]=['ruta_label'=>$k,'n_viajes'=>0,'km'=>0,'gll'=>0,'costo'=>0];
            $por_ruta[$k]['n_viajes']++;$por_ruta[$k]['km']+=(float)$v4['km_recorrido'];
            $por_ruta[$k]['gll']+=(float)$v4['gll_consumidos'];$por_ruta[$k]['costo']+=(float)$v4['costo_estimado'];
        }
        $pra=array_values(array_map(function($r5){ return array_merge($r5,['km_por_galon_prom'=>$r5['gll']>0?round($r5['km']/$r5['gll'],2):0,'gal_por_viaje'=>$r5['n_viajes']>0?round($r5['gll']/$r5['n_viajes'],2):0]); },$por_ruta));
        usort($pra,function($a,$b){ return $b['km_por_galon_prom']<=>$a['km_por_galon_prom']; });
        jout(['viajes'=>$viajes,'stats'=>['n_viajes_con_datos'=>$n,'km_por_galon_prom'=>$sg>0?round($skm/$sg,2):0,'gal_por_viaje_prom'=>$n>0?round($sg/$n,2):0,'costo_por_viaje_prom'=>$n>0?round($sc/$n,2):0],'por_ruta'=>$pra]);
    }
    if($r1==='consumo-por-unidad'){
        $fd=gget('fecha_desde',date('Y-m-01'));$fh=gget('fecha_hasta',date('Y-m-d'));
        // Flota y GE: compras directas. Maquinaria: via retro_control_dia.
        // Hacemos dos consultas separadas y unimos en PHP para evitar problemas con UNION+comentarios SQL
        $tot = (float)(qval(
            "SELECT COALESCE(SUM(cantidad_gll),0) FROM compra_combustible WHERE fecha BETWEEN ? AND ?",
            [$fd,$fh]
        ) ?? 0);
        $directo = qall(
            "SELECT u.placa, u.tipo_unidad,
                    COALESCE(SUM(cc.cantidad_gll),0) AS galones,
                    COALESCE(SUM(cc.total),0) AS soles
             FROM compra_combustible cc
             JOIN unidad u ON cc.id_unidad=u.id_unidad
             WHERE cc.fecha BETWEEN ? AND ?
               AND u.tipo_unidad != 'MAQ. PESADA'
             GROUP BY u.id_unidad,u.placa,u.tipo_unidad",
            [$fd,$fh]
        );
        $maq = qall(
            "SELECT u.placa, u.tipo_unidad,
                    COALESCE(SUM(cc.cantidad_gll),0) AS galones,
                    COALESCE(SUM(cc.total),0) AS soles
             FROM retro_control_dia rcd
             JOIN unidad u ON rcd.id_unidad=u.id_unidad AND u.tipo_unidad='MAQ. PESADA'
             JOIN compra_combustible cc ON rcd.id_combustible=cc.id_combustible
             WHERE rcd.fecha BETWEEN ? AND ?
             GROUP BY u.id_unidad,u.placa,u.tipo_unidad",
            [$fd,$fh]
        );
        $all = array_merge($directo, $maq);
        // Agregar porcentaje en PHP
        foreach($all as &$row){
            $row['galones']    = (float)$row['galones'];
            $row['soles']      = (float)$row['soles'];
            $row['porcentaje'] = $tot > 0 ? round($row['galones'] / $tot * 100, 1) : 0;
        }
        unset($row);
        usort($all, function($a,$b){ return $b['galones']<=>$a['galones']; });
        jout($all);
    }

    // GET /api/dashboard/rendimiento-conductor?fecha_desde&fecha_hasta
    // Rendimiento y duración de viajes por conductor
    if($r1==='rendimiento-conductor'){
        $fd=gget('fecha_desde',date('Y-m-01'));$fh=gget('fecha_hasta',date('Y-m-d'));
        $uid=gget('id_unidad');$cid=gget('id_conductor');
        $sql="SELECT
                t.id_conductor, t.nombre_conductor,
                COUNT(DISTINCT cf.id_control) AS n_viajes,
                SUM(CASE WHEN cf.km_recorrido>0 THEN cf.km_recorrido ELSE 0 END) AS km_total,

                AVG(CASE WHEN cf.hora_salida IS NOT NULL AND cf.hora_regreso IS NOT NULL
                    THEN TIMESTAMPDIFF(MINUTE,
                        CONCAT(cf.fecha,' ',cf.hora_salida),
                        CONCAT(cf.fecha,' ',cf.hora_regreso))
                    END) AS min_prom_viaje,
                MIN(CASE WHEN cf.hora_salida IS NOT NULL AND cf.hora_regreso IS NOT NULL
                    THEN TIMESTAMPDIFF(MINUTE,
                        CONCAT(cf.fecha,' ',cf.hora_salida),
                        CONCAT(cf.fecha,' ',cf.hora_regreso))
                    END) AS min_min_viaje,
                MAX(CASE WHEN cf.hora_salida IS NOT NULL AND cf.hora_regreso IS NOT NULL
                    THEN TIMESTAMPDIFF(MINUTE,
                        CONCAT(cf.fecha,' ',cf.hora_salida),
                        CONCAT(cf.fecha,' ',cf.hora_regreso))
                    END) AS min_max_viaje,
                GROUP_CONCAT(DISTINCT cf.tipo_actividad ORDER BY cf.tipo_actividad SEPARATOR ', ') AS actividades,
                COUNT(DISTINCT u.placa) AS n_unidades
              FROM control_flota cf
              JOIN conductor t ON cf.id_conductor=t.id_conductor
              JOIN unidad u ON cf.id_unidad=u.id_unidad
              WHERE cf.fecha BETWEEN ? AND ?";
        $p=[$fd,$fh];
        if($uid){$sql.=' AND cf.id_unidad=?';$p[]=$uid;}
        if($cid){$sql.=' AND cf.id_conductor=?';$p[]=$cid;}
        $sql.=" GROUP BY t.id_conductor,t.nombre_conductor ORDER BY n_viajes DESC";
        $resumen=qall($sql,$p);

        // Detalle de viajes por conductor (para tabla)
        $sql2="SELECT cf.id_control,cf.fecha,cf.tipo_actividad,cf.observacion,
                      cf.hora_salida,cf.hora_regreso,
                      CASE WHEN cf.km_recorrido>0 THEN cf.km_recorrido ELSE 0 END AS km_recorrido,
                      cf.km_salida,cf.km_retorno,
                      u.placa,t.nombre_conductor,r.destino,r.origen,
                      TIMESTAMPDIFF(MINUTE,
                          CONCAT(cf.fecha,' ',cf.hora_salida),
                          CONCAT(cf.fecha,' ',cf.hora_regreso)) AS duracion_min
               FROM control_flota cf
               JOIN conductor t ON cf.id_conductor=t.id_conductor
               JOIN unidad u ON cf.id_unidad=u.id_unidad
               LEFT JOIN ruta r ON cf.id_ruta=r.id_ruta
               WHERE cf.fecha BETWEEN ? AND ?
                 AND cf.hora_salida IS NOT NULL";
        $p2=[$fd,$fh];
        if($uid){$sql2.=' AND cf.id_unidad=?';$p2[]=$uid;}
        if($cid){$sql2.=' AND cf.id_conductor=?';$p2[]=$cid;}
        $sql2.=" ORDER BY cf.fecha DESC,t.nombre_conductor,cf.hora_salida DESC LIMIT 500";
        $detalle=qall($sql2,$p2);

        jout(['resumen'=>$resumen,'detalle'=>$detalle]);
    }

    // GET /api/dashboard/viajes-por-actividad?fecha_desde&fecha_hasta&id_unidad&tipo_actividad
    if($r1==='viajes-por-actividad'){
        $fd  = gget('fecha_desde', date('Y-m-01'));
        $fh  = gget('fecha_hasta', date('Y-m-d'));
        $uid = gget('id_unidad');
        $fac = gget('tipo_actividad');

        // Resumen por actividad
        $sql_res = "SELECT cf.tipo_actividad,
                           COUNT(*) AS n_viajes,
                           ROUND(COUNT(*)*100.0/NULLIF((SELECT COUNT(*) FROM control_flota cf2
                               WHERE cf2.fecha BETWEEN ? AND ?
                               AND (? IS NULL OR cf2.id_unidad=?)
                               AND cf2.tipo_actividad IS NOT NULL),0),1) AS porcentaje,
                           COALESCE(SUM(CASE WHEN cf.km_recorrido>0 THEN cf.km_recorrido ELSE 0 END),0) AS km_total,
                           COALESCE(SUM(cc.cantidad_gll),0) AS gll_total,
                           COALESCE(SUM(cc.total),0) AS costo_total
                    FROM control_flota cf
                    LEFT JOIN detalle_consumo dc ON dc.id_control=cf.id_control
                    LEFT JOIN compra_combustible cc ON dc.id_combustible=cc.id_combustible
                    WHERE cf.fecha BETWEEN ? AND ?
                      AND cf.tipo_actividad IS NOT NULL";
        $p_res = [$fd,$fh, $uid, $uid, $fd,$fh];
        if($uid){$sql_res.=' AND cf.id_unidad=?';$p_res[]=$uid;}
        $sql_res .= ' GROUP BY cf.tipo_actividad ORDER BY n_viajes DESC';
        $resumen = qall($sql_res, $p_res);

        // Detalle de viajes (para tabla filtrable)
        $sql_det = "SELECT cf.id_control, cf.fecha, cf.tipo_actividad, cf.observacion,
                           cf.hora_salida, cf.hora_regreso,
                           CASE WHEN cf.km_recorrido>0 THEN cf.km_recorrido ELSE 0 END AS km_recorrido,
                           cf.km_salida, cf.km_retorno,
                           u.placa, COALESCE(t.nombre_conductor,'') AS conductor_nombre,
                           r.destino, r.origen
                    FROM control_flota cf
                    JOIN unidad u ON cf.id_unidad=u.id_unidad
                    LEFT JOIN conductor t ON cf.id_conductor=t.id_conductor
                    LEFT JOIN ruta r ON cf.id_ruta=r.id_ruta
                    WHERE cf.fecha BETWEEN ? AND ?
                      AND cf.tipo_actividad IS NOT NULL";
        $p_det = [$fd,$fh];
        if($uid){$sql_det.=' AND cf.id_unidad=?';$p_det[]=$uid;}
        if($fac){$sql_det.=' AND cf.tipo_actividad=?';$p_det[]=$fac;}
        $sql_det .= ' ORDER BY cf.fecha DESC, cf.hora_salida DESC LIMIT 500';
        $detalle = qall($sql_det, $p_det);

        jout(['resumen'=>$resumen,'detalle'=>$detalle]);
    }
}

// ════════════════════════════════════════════════════════════
// PROCESOS OPERACIÓN
// ════════════════════════════════════════════════════════════
if($r0==='procesos'){
    if($r1===''&&$m==='GET'){
        $sql="SELECT po.*,ep.nombre_equipo,ep.proceso,t.nombre_completo AS registrador_nombre FROM procesos_operacion po JOIN equipos_proceso ep ON po.id_equipo=ep.id_equipo LEFT JOIN trabajadores t ON po.id_trabajador=t.id_trabajador WHERE 1=1";
        $p=[];
        if($v=gget('id_registro')){$sql.=' AND po.id_registro=?';$p[]=$v;}
        if($v=gget('fecha')){$sql.=' AND po.fecha=?';$p[]=$v;}
        jout(qall($sql.' ORDER BY po.fecha DESC,po.hora_inicio',$p));
    }
    if($r1===''&&$m==='POST'){
        $d=jbody();
        $hi=$d['hora_inicio']??null;$hf=$d['hora_fin']??null;
        $hs=($hi&&$hf)?round((strtotime($hf)-strtotime($hi))/3600,2):null;
        $id=qexec('INSERT INTO procesos_operacion(fecha,id_equipo,lote_mineral,id_trabajador,cliente,hora_inicio,hora_fin,horas_trabajo,cantidad,observaciones,id_registro)VALUES(?,?,?,?,?,?,?,?,?,?,?)',
            [$d['fecha']??date('Y-m-d'),$d['id_equipo'],$d['lote_mineral']??null,$d['id_trabajador']??null,$d['cliente']??null,$hi,$hf,$hs,$d['cantidad']??null,$d['observaciones']??null,$d['id_registro']]);
        jout(['id_operacion'=>(int)$id,'horas_trabajo'=>$hs],201);
    }
    if($r1!==''&&$m==='DELETE'){qrows('DELETE FROM procesos_operacion WHERE id_operacion=?',[(int)$r1]);jout(['ok'=>true]);}
}

jout(['error'=>'Endpoint no encontrado'],404);
}

function reg_kardex_ge(int $id_u,string $tipo,float $gll,?int $id_comb,string $obs,string $fecha=''):void{
    // El bidon es compartido: el saldo es el ultimo saldo global de todos los GE
    $ul=qone('SELECT saldo_gll FROM kardex_combustible ORDER BY id_kardex DESC LIMIT 1');
    $saldo=(float)($ul['saldo_gll']??0);
    $nuevo=$tipo==='ENTRADA'?$saldo+$gll:$saldo-$gll;
    $f = $fecha!=='' ? $fecha : date('Y-m-d H:i:s');
    qexec('INSERT INTO kardex_combustible(fecha,id_unidad,tipo_movimiento,galones,id_combustible,observacion,saldo_gll)VALUES(?,?,?,?,?,?,?)',
        [$f,$id_u,$tipo,$gll,$id_comb,$obs,round($nuevo,2)]);
}

function run_assignment_maq(int $id_u,float $h_ini,float $h_fin,int $id_dia):array{
    $comb=qone(
        "SELECT id_combustible,km_vehiculo FROM compra_combustible
         WHERE id_unidad=? AND tipo_combustible='PETROLEO'
           AND km_vehiculo IS NOT NULL AND km_vehiculo>=?
         ORDER BY km_vehiculo ASC LIMIT 1",
        [$id_u,$h_ini]
    );
    if(!$comb)return['asignado'=>false];
    qrows('UPDATE retro_control_dia SET id_combustible=? WHERE id_control_dia=? AND id_combustible IS NULL',
        [$comb['id_combustible'],$id_dia]);
    return['asignado'=>true,'id_combustible'=>$comb['id_combustible']];
}

function run_assignment_maq_bulk(int $id_combustible):array{
    $c=qone('SELECT * FROM compra_combustible WHERE id_combustible=?',[$id_combustible]);
    if(!$c||($c['tipo_combustible']??'')!=='PETROLEO')return['rows_updated'=>0];
    $km_T=(float)($c['km_vehiculo']??0);
    if($km_T<=0)return['rows_updated'=>0,'msg'=>'Sin horometro en compra'];
    $updated=qrows(
        "UPDATE retro_control_dia
         SET id_combustible=?
         WHERE id_unidad=?
           AND id_combustible IS NULL
           AND horometro_inicio IS NOT NULL
           AND horometro_final IS NOT NULL
           AND horometro_inicio <= ?
           AND horometro_final >= ?",
        [$id_combustible,$c['id_unidad'],$km_T,$km_T]
    );
    return['rows_updated'=>$updated,'tipo'=>'MAQ. PESADA','horometro_tanqueo'=>$km_T];
}