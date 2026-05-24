<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Carbon\Carbon;
use App\Models\Prestamo;

echo "\n=== VERIFICACIÓN DE CLIENTES ACTIVOS ===\n\n";

$fechaCorte = Carbon::now()->endOfDay();
echo "Fecha de corte: " . $fechaCorte->format('Y-m-d H:i:s') . "\n\n";

$prestamos = Prestamo::whereIn('estado', ['Entregado', 'Atrasado'])
    ->where('fecha_entrega', '<=', $fechaCorte)
    ->with('pagos', 'cliente')
    ->get();

echo "Total préstamos Entregado/Atrasado: " . $prestamos->count() . "\n\n";

$clientesActivos = [];
$ejemplos = [];
$contadores = [
    'con_saldo' => 0,
    'liquidados' => 0,
    'mas_365_dias' => 0,
    'activos' => 0
];

foreach($prestamos as $p) {
    $pagosHastaFecha = $p->pagos->where('fecha_pago', '<=', $fechaCorte);
    $capitalPagado = $pagosHastaFecha->sum('capital_pagado');
    $capitalAEntregar = $p->monto_autorizado ?? $p->monto_total;
    $saldoRestante = max(0, $capitalAEntregar - $capitalPagado);
    
    if ($saldoRestante <= 0.01) {
        $contadores['liquidados']++;
        continue;
    }
    
    $contadores['con_saldo']++;
    
    $ultimoPago = $pagosHastaFecha->sortByDesc('fecha_pago')->first();
    if ($ultimoPago) {
        $fechaUltimoPago = Carbon::parse($ultimoPago->fecha_pago);
        $diasDesdeUltimoPago = $fechaUltimoPago->diffInDays($fechaCorte);
    } else {
        $fechaEntrega = Carbon::parse($p->fecha_entrega);
        $diasDesdeUltimoPago = $fechaEntrega->diffInDays($fechaCorte);
    }
    
    if ($diasDesdeUltimoPago > 365) {
        $contadores['mas_365_dias']++;
        if (count($ejemplos) < 3) {
            $ejemplos[] = [
                'tipo' => 'EXCLUIDO (>365 días)',
                'cliente' => $p->cliente->nombre_completo ?? 'N/A',
                'prestamo_id' => $p->id,
                'saldo' => number_format($saldoRestante, 2),
                'ultimo_pago' => $ultimoPago ? $ultimoPago->fecha_pago : 'Sin pagos',
                'dias_desde_ultimo_pago' => $diasDesdeUltimoPago
            ];
        }
        continue;
    }
    
    $contadores['activos']++;
    $clientesActivos[$p->cliente_id] = true;
    
    if (count($ejemplos) < 5 && !isset($ejemplos['activo_' . $p->cliente_id])) {
        $ejemplos['activo_' . $p->cliente_id] = [
            'tipo' => 'ACTIVO',
            'cliente' => $p->cliente->nombre_completo ?? 'N/A',
            'prestamo_id' => $p->id,
            'saldo' => number_format($saldoRestante, 2),
            'ultimo_pago' => $ultimoPago ? $ultimoPago->fecha_pago : 'Sin pagos',
            'dias_desde_ultimo_pago' => $diasDesdeUltimoPago
        ];
    }
}

echo "--- CONTADORES ---\n";
echo "Préstamos con saldo > 0: " . $contadores['con_saldo'] . "\n";
echo "Préstamos liquidados (excluidos): " . $contadores['liquidados'] . "\n";
echo "Préstamos con >365 días desde último pago (excluidos): " . $contadores['mas_365_dias'] . "\n";
echo "Préstamos activos (incluidos): " . $contadores['activos'] . "\n\n";

echo "*** TOTAL CLIENTES ACTIVOS: " . count($clientesActivos) . " ***\n\n";

echo "--- EJEMPLOS ---\n";
foreach($ejemplos as $ej) {
    echo "\n" . $ej['tipo'] . ":\n";
    echo "  Cliente: " . $ej['cliente'] . "\n";
    echo "  Préstamo ID: " . $ej['prestamo_id'] . "\n";
    echo "  Saldo: $" . $ej['saldo'] . "\n";
    echo "  Último pago: " . $ej['ultimo_pago'] . "\n";
    echo "  Días desde último pago: " . $ej['dias_desde_ultimo_pago'] . "\n";
}

echo "\n--- COMPARACIÓN CON SERVICIO ---\n";
$servicio = new \App\Services\ReportesControlService();
$resultado = $servicio->calcularCarteraPorAsesor($fechaCorte);
$clientesSegunServicio = $resultado['totales']['clientes'];
echo "Clientes según ReportesControlService: " . $clientesSegunServicio . "\n";
echo "Clientes según verificación manual: " . count($clientesActivos) . "\n";
echo "¿Coinciden? " . ($clientesSegunServicio == count($clientesActivos) ? "✓ SÍ" : "✗ NO") . "\n\n";
