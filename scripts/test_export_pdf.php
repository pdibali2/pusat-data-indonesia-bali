<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $resp = app()->call('App\\Http\\Controllers\\AnomalyControlController@exportReport', ['anomalyId' => 880]);
    echo 'Response class: ' . get_class($resp) . PHP_EOL;
    if (method_exists($resp, 'getStatusCode')) {
        echo 'Status: ' . $resp->getStatusCode() . PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'ERROR: ' . get_class($e) . ' - ' . $e->getMessage() . PHP_EOL;
}
