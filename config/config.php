<?php
// ============================================================
// config/config.php
// ============================================================
date_default_timezone_set('America/Lima');

// ── Base de datos ─────────────────────────────────────────────
// InfinityFree: usa el host, usuario y BD que te dan en el panel
// cPanel → MySQL Databases → copia los datos y ponlos aquí
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_PORT',    getenv('DB_PORT')    ?: '3306');
define('DB_NAME',    getenv('DB_NAME')    ?: 'filomena_100');
define('DB_USER',    getenv('DB_USER')    ?: 'admin');
define('DB_PASS',    getenv('DB_PASS')    ?: '12345678');
define('DB_CHARSET', 'utf8mb4');
define('APP_NAME',   'FleetControl Pro');
define('APP_BASE',   _detectar_base());

// ── Detectar base de ruta automáticamente ────────────────────
// Funciona tanto en raíz (tudominio.com/) como en subcarpeta
// (tudominio.com/flota/) sin cambiar nada más en el código.
// ── Detectar base de ruta automáticamente ────────────────────
function _detectar_base(): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $dir    = rtrim(dirname($script), '/\\');
    // Si la llamada viene de dentro de /api, subimos un nivel a la raíz del proyecto
    if ($dir === '/api' || str_ends_with($dir, '/api')) {
        $dir = rtrim(dirname($dir), '/\\');
    }
    return ($dir === '.' || $dir === '/') ? '' : $dir;
}

function pdo(): PDO {
    static $conn = null;
    if ($conn === null) {
        $dsn  = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset='.DB_CHARSET;
        $conn = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone='-05:00'",
        ]);
    }
    return $conn;
}

function qall(string $sql, array $p = []): array {
    $s = pdo()->prepare($sql); $s->execute($p); return $s->fetchAll();
}
function qone(string $sql, array $p = []): ?array {
    $s = pdo()->prepare($sql); $s->execute($p); $r = $s->fetch(); return $r ?: null;
}
function qval(string $sql, array $p = []): mixed {
    $s = pdo()->prepare($sql); $s->execute($p); return $s->fetchColumn();
}
function qexec(string $sql, array $p = []): string {
    file_put_contents('/tmp/qexec_debug.txt', $sql . "\n", FILE_APPEND);
    $db = pdo(); $s = $db->prepare($sql); $s->execute($p); return $db->lastInsertId();
}
function qrows(string $sql, array $p = []): int {
    $s = pdo()->prepare($sql); $s->execute($p); return $s->rowCount();
}
function jout(mixed $d, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    exit;
}
function jbody(): array {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}
function gget(string $k, mixed $d = null): mixed {
    return (isset($_GET[$k]) && $_GET[$k] !== '') ? $_GET[$k] : $d;
}

// ── Asignación automática de combustible ─────────────────────
// Solo PETROLEO con tanqueo=1 inicia nueva ventana de km
// ── Asignación automática de combustible ─────────────────────
//
// LÓGICA (validada con el usuario):
// Un viaje pertenece al tanqueo T si:
//   1. km_T > km_salida          (tanqueo posterior al inicio del viaje)
//   2. (km_retorno - km_T) <= MARGEN_KM   (retorno no superó el tanqueo por más del margen)
//
// MARGEN_KM = 4 km (diferencia por ida al grifo desde la planta)
// Si km_retorno < km_T: diferencia negativa → siempre cumple (retornó antes de tanquear)
// Si km_retorno > km_T: el camión pasó por el grifo en el camino → solo si dif ≤ 4 km
//
// Ejemplos:
//   T1=km50, T2=km100
//   V1(20→35): T1(50)>20 ✓ y 35-50=-15≤4 ✓ → T1
//   V2(35→54): T1(50)>35 ✓ y 54-50=4≤4  ✓ → T1
//   V3(54→75): T1(50) falla cond 2: 75-50=25>4. T2(100)>54✓ y 75-100=-25≤4✓ → T2
//   V5(96→110): T2(100)>96✓ pero 110-100=10>4 → no. T3→T5 al siguiente
//   V6(96→104): T2(100)>96✓ y 104-100=4≤4 ✓ → T2
define('ASIGN_MARGEN_KM', 4);

function run_assignment(int $id_combustible): array {
    $c = qone('SELECT * FROM compra_combustible WHERE id_combustible=?', [$id_combustible]);
    if (!$c) return ['rows_updated'=>0, 'tipo'=>'DESCONOCIDO'];
    if (($c['tipo_combustible']??'') !== 'PETROLEO') return ['rows_updated'=>0, 'tipo'=>'NO_PETROLEO'];
    if ($c['id_unidad'] === null) return ['rows_updated'=>0, 'tipo'=>'BIDON'];

    $u    = qone('SELECT tipo_unidad FROM unidad WHERE id_unidad=?', [$c['id_unidad']]);
    $tipo = $u['tipo_unidad'] ?? 'FLOTA';
    $km_T = (float)($c['km_vehiculo'] ?? 0);
    $margen = ASIGN_MARGEN_KM;

    $updated = 0;
    if ($tipo === 'FLOTA') {
        // Asignar viajes sin combustible donde:
        //   km_salida < km_T  (condición 1: el tanqueo fue posterior a la salida)
        //   km_retorno - km_T <= margen  (condición 2: retorno dentro del margen del tanqueo)
        $updated = qrows(
            "UPDATE detalle_consumo dc
             JOIN control_flota cf ON dc.id_control = cf.id_control
             SET dc.id_combustible = ?
             WHERE cf.id_unidad       = ?
               AND dc.id_combustible IS NULL
               AND cf.km_salida      IS NOT NULL
               AND cf.km_retorno     IS NOT NULL
               AND cf.km_salida      < ?
               AND (cf.km_retorno - ?) <= ?",
            [$id_combustible, $c['id_unidad'], $km_T, $km_T, $margen]
        );
    } elseif ($tipo === 'MAQ. PESADA') {
        // Para maquinaria: horómetro_inicio < km_vehiculo (horómetro del tanqueo)
        // y (horometro_final - km_vehiculo) <= margen_horas (usamos 0.5 h = 30 min)
        $updated = qrows(
            "UPDATE retro_control_dia
             SET id_combustible = ?
             WHERE id_unidad        = ?
               AND id_combustible  IS NULL
               AND horometro_inicio IS NOT NULL
               AND horometro_final  IS NOT NULL
               AND horometro_inicio < ?
               AND (horometro_final - ?) <= 0.5",
            [$id_combustible, $c['id_unidad'], $km_T, $km_T]
        );
    }
    return ['rows_updated'=>$updated, 'tipo'=>$tipo, 'km_tanqueo'=>$km_T, 'margen'=>$margen];
}

// ── Registrar movimiento de crédito en grifo ─────────────────
function registrar_movimiento_credito(int $id_compra, float $monto, string $desc): void {
    $ul = qone('SELECT saldo FROM movimientos_combustible ORDER BY id_movimiento DESC LIMIT 1');
    $nuevo = (float)($ul['saldo'] ?? 0) - $monto;
    qexec(
        'INSERT INTO movimientos_combustible(fecha,tipo,descripcion,monto,id_compra,saldo)
         VALUES(CURDATE(),"COMPRA",?,?,?,?)',
        [$desc, $monto, $id_compra, $nuevo]
    );
}