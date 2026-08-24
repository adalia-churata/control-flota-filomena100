<?php
require_once __DIR__ . '/config/config.php';
$router = file_get_contents(__DIR__ . '/api/router.php');
$pos = strpos($router, "actividades'&&\$m==='POST'");
if ($pos !== false) {
    echo "=== ACTIVIDADES POST BLOCK ===\n";
    echo substr($router, $pos - 10, 500);
} else {
    echo "Block not found";
}
echo "\n\n=== MD5 of router.php ===\n";
echo md5_file(__DIR__ . '/api/router.php');
echo "\n\n=== OPcache status ===\n";
if (function_exists('opcache_get_status')) {
    $s = opcache_get_status();
    echo "Enabled: " . ($s ? 'yes' : 'no') . "\n";
    if ($s) {
        opcache_reset();
        echo "OPcache RESET done\n";
    }
} else {
    echo "opcache functions not available\n";
}