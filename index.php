<?php
// ============================================================
// index.php — Front Controller
// ============================================================
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Capturar errores fatales y mostrarlos como HTML legible
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
        <title>Error — FleetControl</title>
        <style>body{font-family:monospace;padding:40px;background:#fef2f2;color:#991b1b}
        pre{background:#fff;border:1px solid #fca5a5;padding:20px;border-radius:8px;overflow:auto}
        h2{color:#dc2626}</style></head><body>
        <h2>⚠ Error interno del servidor</h2>
        <pre>' . htmlspecialchars($err['message'])
            . "\n\nArchivo: " . htmlspecialchars(basename($err['file']))
            . "\nLínea:   " . $err['line'] . '</pre>
        <p>Revisa el log del servidor para más detalles.</p>
        </body></html>';
    }
});

// ── Cargar configuración ──────────────────────────────────────
try {
    require_once __DIR__ . '/config/config.php';
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error config</title>
    <style>body{font-family:monospace;padding:40px;background:#fef2f2}
    pre{background:#fff;border:1px solid #fca5a5;padding:20px;border-radius:8px}</style></head><body>
    <h2 style="color:#dc2626">Error al cargar config.php</h2>
    <pre>' . htmlspecialchars($e->getMessage())
        . "\n\nArchivo: " . htmlspecialchars(basename($e->getFile()))
        . "\nLínea:   " . $e->getLine() . '</pre></body></html>');
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// URI completa → ruta limpia
$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$base = rtrim(APP_BASE, '/');
$path = ($base !== '' && (strpos($uri, $base) === 0))
    ? substr($uri, strlen($base))
    : $uri;
$path = '/' . trim($path, '/');

// ── API → JSON ────────────────────────────────────────────────
if ((strpos($path, '/api/') === 0)) {
    require_once __DIR__ . '/api/router.php';
    exit;
}

// ── Vistas → HTML ─────────────────────────────────────────────
$viewMap = [
    '/'              => 'dashboard',
    '/dashboard'     => 'dashboard',
    '/garita'        => 'garita',
    '/compras'       => 'compras',
    '/maquinaria'    => 'maquinaria',
    '/ge'            => 'ge',
    '/mantenimiento' => 'mantenimiento',
    '/reportes'      => 'reportes',
];

$viewName = $viewMap[$path] ?? null;

if ($viewName === null) {
    http_response_code(404);
    $viewName = '404';
}

// Badge de alertas para el topbar
$alertas_count = 0;
try {
    $alertas_count = (int)(qval(
        "SELECT COUNT(*) FROM documento_unidad
         WHERE DATEDIFF(fecha_vencimiento, CURDATE()) <= alerta_dias_antes"
    ) ?? 0);
} catch (Throwable) {}

ob_clean(); // Limpiar cualquier output antes de empezar el HTML
require_once __DIR__ . '/views/layouts/layout.php';