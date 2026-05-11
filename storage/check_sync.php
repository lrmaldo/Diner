<?php

use App\Models\Prestamo;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = Prestamo::first();
if ($p && $p->clientes()->count() > 0) {
    $c = $p->clientes()->first();
    $old = $c->pivot->monto_solicitado;
    $p->clientes()->syncWithoutDetaching([$c->id => ['monto_solicitado' => 9999]]);
    $new = $p->clientes()->where('cliente_id', $c->id)->first()->pivot->monto_solicitado;
    $p->clientes()->updateExistingPivot($c->id, ['monto_solicitado' => $old]);
    echo "Old: $old, New via syncWithoutDetaching: $new\n";
} else {
    echo "No datan";
}
