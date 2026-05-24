<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Carbon\Carbon;
use App\Models\Prestamo;

echo "\n=== VERIFICACIÓN DE FIDELIZACIÓN ===\n\n";

// Tomar el mes actual
$inicio = Carbon::now()->startOfMonth();
$fin = Carbon::now()->endOfDay();

echo "Periodo: " . $inicio->format('Y-m-d') . " al " . $fin->format('Y-m-d') . "\n\n";

// 1. Obtener préstamos liquidados en el periodo
$prestamosLiquidados = Prestamo::whereIn('estado', ['Pagado', 'Liquidado'])
    ->with(['pagos' => function ($q) {
        $q->orderBy('fecha_pago', 'desc');
    }, 'cliente'])
    ->get()
    ->filter(function ($prestamo) use ($inicio, $fin) {
        $ultimoPago = $prestamo->pagos->first();
        if ($ultimoPago) {
            $fechaPago = Carbon::parse($ultimoPago->fecha_pago)->startOfDay();
            return $fechaPago->between($inicio->copy()->startOfDay(), $fin->copy()->endOfDay());
        }
        return false;
    });

echo "Total préstamos liquidados en el periodo: " . $prestamosLiquidados->count() . "\n";

if ($prestamosLiquidados->isEmpty()) {
    echo "\nNo hay préstamos liquidados en este periodo.\n";
    exit;
}

$clientesLiquidadosId = $prestamosLiquidados->pluck('cliente_id')->unique();
echo "Total CLIENTES únicos que liquidaron: " . $clientesLiquidadosId->count() . "\n\n";

echo "--- ANÁLISIS POR CLIENTE ---\n\n";

$clientesRenovados = 0;
$ejemplos = [];

foreach ($clientesLiquidadosId as $clienteId) {
    $prestamosDelCliente = $prestamosLiquidados->where('cliente_id', $clienteId);
    $fechaLiquidacionBase = null;
    
    foreach ($prestamosDelCliente as $p) {
        $ultimoPago = $p->pagos->first();
        if ($ultimoPago) {
            $f = Carbon::parse($ultimoPago->fecha_pago);
            if (!$fechaLiquidacionBase || $f > $fechaLiquidacionBase) {
                $fechaLiquidacionBase = $f;
            }
        }
    }
    
    if ($fechaLiquidacionBase) {
        $prestamosPosteriores = Prestamo::where('cliente_id', $clienteId)
            ->whereIn('estado', ['Entregado', 'Atrasado', 'Pagado', 'Liquidado'])
            ->where('fecha_entrega', '>=', $fechaLiquidacionBase->format('Y-m-d'))
            ->whereNotIn('id', $prestamosDelCliente->pluck('id')->toArray())
            ->get();
        
        $tieneRenovacion = $prestamosPosteriores->count() > 0;
        
        if ($tieneRenovacion) {
            $clientesRenovados++;
        }
        
        // Guardar ejemplos (máximo 10)
        if (count($ejemplos) < 10) {
            $cliente = $prestamosDelCliente->first()->cliente;
            $ejemplos[] = [
                'cliente' => $cliente->nombre_completo ?? 'N/A',
                'cliente_id' => $clienteId,
                'fecha_liquidacion' => $fechaLiquidacionBase->format('Y-m-d'),
                'prestamo_liquidado_id' => $prestamosDelCliente->first()->id,
                'renovo' => $tieneRenovacion ? 'SÍ' : 'NO',
                'prestamos_posteriores' => $prestamosPosteriores->count(),
                'ids_posteriores' => $prestamosPosteriores->pluck('id')->toArray()
            ];
        }
    }
}

echo "Clientes que SÍ renovaron: " . $clientesRenovados . "\n";
echo "Clientes que NO renovaron: " . ($clientesLiquidadosId->count() - $clientesRenovados) . "\n";

$porcentaje = $clientesLiquidadosId->count() > 0 
    ? round(($clientesRenovados / $clientesLiquidadosId->count()) * 100, 2) 
    : 0;

echo "\n*** FIDELIZACIÓN: " . $porcentaje . "% ***\n\n";

echo "--- EJEMPLOS DE CLIENTES ---\n";
foreach ($ejemplos as $ej) {
    echo "\n" . ($ej['renovo'] == 'SÍ' ? '✓' : '✗') . " Cliente: " . $ej['cliente'] . " (ID: " . $ej['cliente_id'] . ")\n";
    echo "  Liquidó préstamo #" . $ej['prestamo_liquidado_id'] . " el " . $ej['fecha_liquidacion'] . "\n";
    echo "  ¿Renovó? " . $ej['renovo'] . "\n";
    if ($ej['prestamos_posteriores'] > 0) {
        echo "  Préstamos posteriores: " . implode(', ', array_map(fn($id) => "#$id", $ej['ids_posteriores'])) . "\n";
    }
}

echo "\n--- COMPARACIÓN CON SERVICIO ---\n";
$servicio = new \App\Services\ReportesControlService();
$fidelizacionServicio = $servicio->calcularFidelizacion($inicio, $fin);
echo "Fidelización según ReportesControlService: " . $fidelizacionServicio . "%\n";
echo "Fidelización según verificación manual: " . $porcentaje . "%\n";
echo "¿Coinciden? " . ($fidelizacionServicio == $porcentaje ? "✓ SÍ" : "✗ NO") . "\n\n";
