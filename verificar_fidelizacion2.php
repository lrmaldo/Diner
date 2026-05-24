<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Carbon\Carbon;
use App\Models\Prestamo;

echo "\n=== VERIFICACIÓN GENERAL DE FIDELIZACIÓN ===\n\n";

// Ver todos los préstamos liquidados
$todosLiquidados = Prestamo::whereIn('estado', ['Pagado', 'Liquidado'])
    ->with(['pagos' => function ($q) {
        $q->orderBy('fecha_pago', 'desc');
    }, 'cliente'])
    ->get();

echo "Total préstamos Pagado/Liquidado en el sistema: " . $todosLiquidados->count() . "\n\n";

if ($todosLiquidados->isEmpty()) {
    echo "No hay préstamos liquidados en el sistema.\n";
    echo "\nEsto es normal si:\n";
    echo "- Los clientes aún están pagando sus préstamos\n";
    echo "- No se ha actualizado el estado a 'Pagado' o 'Liquidado' cuando terminan\n\n";
    
    // Ver estados actuales
    echo "--- ESTADOS ACTUALES DE PRÉSTAMOS ---\n";
    $estados = Prestamo::selectRaw('estado, count(*) as total')
        ->groupBy('estado')
        ->get();
    
    foreach ($estados as $e) {
        echo $e->estado . ": " . $e->total . "\n";
    }
    
    exit;
}

// Agrupar por mes de liquidación
echo "--- PRÉSTAMOS LIQUIDADOS POR MES ---\n\n";
$porMes = [];

foreach ($todosLiquidados as $p) {
    $ultimoPago = $p->pagos->first();
    if ($ultimoPago) {
        $mes = Carbon::parse($ultimoPago->fecha_pago)->format('Y-m');
        if (!isset($porMes[$mes])) {
            $porMes[$mes] = [];
        }
        $porMes[$mes][] = $p;
    }
}

krsort($porMes); // Ordenar por mes descendente

foreach ($porMes as $mes => $prestamos) {
    echo $mes . ": " . count($prestamos) . " préstamos\n";
}

// Analizar el mes más reciente con liquidaciones
$mesConDatos = array_key_first($porMes);
if (!$mesConDatos) {
    echo "\nNo se encontraron fechas de liquidación.\n";
    exit;
}

$fechaMes = Carbon::parse($mesConDatos . '-01');
$inicio = $fechaMes->copy()->startOfMonth();
$fin = $fechaMes->copy()->endOfMonth();

echo "\n=== ANÁLISIS DETALLADO DE " . $fechaMes->translatedFormat('F Y') . " ===\n\n";

$servicio = new \App\Services\ReportesControlService();
$fidelizacion = $servicio->calcularFidelizacion($inicio, $fin);

echo "Fidelización: " . $fidelizacion . "%\n\n";

// Análisis manual
$prestamosDelMes = collect($porMes[$mesConDatos]);
$clientesLiquidadosId = $prestamosDelMes->pluck('cliente_id')->unique();

echo "Total clientes que liquidaron: " . $clientesLiquidadosId->count() . "\n\n";

$clientesRenovados = 0;
$ejemplos = [];

foreach ($clientesLiquidadosId as $clienteId) {
    $prestamosDelCliente = $prestamosDelMes->where('cliente_id', $clienteId);
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
        $otrosPrestamosLiquidados = Prestamo::where('cliente_id', $clienteId)
            ->whereIn('estado', ['Pagado', 'Liquidado'])
            ->whereNotIn('id', $prestamosDelCliente->pluck('id')->toArray())
            ->with(['pagos' => function ($q) {
                $q->orderBy('fecha_pago', 'desc');
            }])
            ->get();
        
        $tieneRenovacion = false;
        $prestamosLiquidadosPosteriores = [];
        
        foreach ($otrosPrestamosLiquidados as $prestamoLiquidado) {
            $ultimoPagoOtroPrestamo = $prestamoLiquidado->pagos->first();
            if ($ultimoPagoOtroPrestamo) {
                $fechaLiquidacionOtroPrestamo = Carbon::parse($ultimoPagoOtroPrestamo->fecha_pago);
                if ($fechaLiquidacionOtroPrestamo > $fechaLiquidacionBase) {
                    $tieneRenovacion = true;
                    $prestamosLiquidadosPosteriores[] = [
                        'id' => $prestamoLiquidado->id,
                        'fecha_liquidacion' => $fechaLiquidacionOtroPrestamo->format('Y-m-d')
                    ];
                }
            }
        }
        
        if ($tieneRenovacion) {
            $clientesRenovados++;
        }
        
        $cliente = $prestamosDelCliente->first()->cliente;
        $ejemplos[] = [
            'cliente' => $cliente->nombre_completo ?? 'N/A',
            'cliente_id' => $clienteId,
            'fecha_liquidacion' => $fechaLiquidacionBase->format('Y-m-d'),
            'prestamo_liquidado_id' => $prestamosDelCliente->first()->id,
            'renovo' => $tieneRenovacion ? 'SÍ' : 'NO',
            'prestamos_posteriores' => count($prestamosLiquidadosPosteriores),
            'liquidaciones_posteriores' => $prestamosLiquidadosPosteriores
        ];
    }
}

echo "Clientes que SÍ renovaron: " . $clientesRenovados . "\n";
echo "Clientes que NO renovaron: " . ($clientesLiquidadosId->count() - $clientesRenovados) . "\n\n";

echo "--- EJEMPLOS ---\n";
foreach ($ejemplos as $ej) {
    echo "\n" . ($ej['renovo'] == 'SÍ' ? '✓ RENOVÓ' : '✗ NO RENOVÓ') . " - " . $ej['cliente'] . "\n";
    echo "  Liquidó préstamo #" . $ej['prestamo_liquidado_id'] . " el " . $ej['fecha_liquidacion'] . "\n";
    if ($ej['prestamos_posteriores'] > 0) {
        echo "  Liquidó " . $ej['prestamos_posteriores'] . " préstamo(s) después:\n";
        foreach ($ej['liquidaciones_posteriores'] as $liq) {
            echo "    - Préstamo #" . $liq['id'] . " liquidado el " . $liq['fecha_liquidacion'] . "\n";
        }
    } else {
        echo "  No ha liquidado otro préstamo después\n";
    }
}

echo "\n";
