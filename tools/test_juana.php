<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = App\Models\Prestamo::with(['pagos', 'cliente'])->limit(3)->get();
foreach($p as $prestamo) {
    echo "Prestamo ID: {$prestamo->id} - Cliente: {$prestamo->cliente->nombres}\n";
    foreach($prestamo->pagos as $pago) {
        echo "Pago: ID {$pago->id} | Num: {$pago->numero_pago} | Tipo: {$pago->tipo_pago} | Monto: {$pago->monto} | Moratorio: {$pago->moratorio_pagado}\n";
    }
}
