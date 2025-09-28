<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
// bootstrap
$request = Illuminate\Http\Request::capture();
$kernel->bootstrap();
$r = Illuminate\Support\Facades\Route::getRoutes()->getByName('prestamos.create');
if ($r) {
    echo implode(', ', $r->gatherMiddleware());
} else {
    echo 'NO_ROUTE';
}
