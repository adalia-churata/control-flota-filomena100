<?php
// ============================================================
// config/config.php
// ============================================================
date_default_timezone_set('America/Lima');

// ── Base de datos ─────────────────────────────────────────────
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_PORT',    getenv('DB_PORT')    ?: '3306');
define('DB_NAME',    getenv('DB_NAME')    ?: 'filomena_100');
define('DB_USER',    getenv('DB_USER')    ?: 'admin');
define('DB_PASS',    getenv('DB_PASS')    ?: '12345678');
define('DB_CHARSET', 'utf8mb4');
define('APP_NAME',   'FleetControl Pro');
define('APP_BASE',   _detectar_base());

// ── Detectar base de ruta automáticamente ────────────────────
function _detectar_base(): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $dir    = rtrim(dirname($script), '/\\');
    if ($dir === '/api' || (strpos($dir, '/api') !== false && substr($dir, -4) === '/api')) {
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

// ── Constante de margen para asignación de combustible ───────
// La lógica de asignación está en router.php para evitar duplicados
define('ASIGN_MARGEN_KM', 4);

// ── Registrar movimiento de crédito en grifo ─────────────────
function registrar_movimiento_credito(int $id_compra, float $monto, string $desc): void {
    $ul    = qone('SELECT saldo FROM movimientos_combustible ORDER BY id_movimiento DESC LIMIT 1');
    $nuevo = (float)($ul['saldo'] ?? 0) - $monto;
    qexec(
        'INSERT INTO movimientos_combustible(fecha,tipo,descripcion,monto,id_compra,saldo)
         VALUES(CURDATE(),"COMPRA",?,?,?,?)',
        [$desc, $monto, $id_compra, $nuevo]
    );
}