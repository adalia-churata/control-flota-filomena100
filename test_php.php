<?php
// Test PHP version and basic syntax
echo 'PHP version: ' . PHP_VERSION . PHP_EOL;

// Test strpos
$test = 'hello world';
echo 'strpos test: ' . (strpos($test, 'hello') === 0 ? 'OK' : 'FAIL') . PHP_EOL;

// Test require config
try {
    require_once __DIR__ . '/config/config.php';
    echo 'config.php: OK' . PHP_EOL;
    echo 'DB_HOST: ' . DB_HOST . PHP_EOL;
    echo 'APP_BASE: ' . APP_BASE . PHP_EOL;
} catch (Throwable $e) {
    echo 'config.php ERROR: ' . $e->getMessage() . PHP_EOL;
}

// Test router require
try {
    // Simulate API call
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/api/test';
    ob_start();
    require_once __DIR__ . '/api/router.php';
    $out = ob_get_clean();
    echo 'router.php: OK (output length: ' . strlen($out) . ')' . PHP_EOL;
} catch (Throwable $e) {
    ob_end_clean();
    echo 'router.php ERROR: ' . $e->getMessage() . ' at line ' . $e->getLine() . ' in ' . basename($e->getFile()) . PHP_EOL;
    echo 'Trace: ' . $e->getTraceAsString() . PHP_EOL;
}
