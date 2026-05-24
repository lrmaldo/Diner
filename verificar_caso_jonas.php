<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Carbon\Carbon;
use App\Models\Prestamo;
use App\Models\Cliente;

echo "\n=== VERIFICACIÓN CASO JONAS NOH POOL ===\n\n";

// Buscar cliente Jonas
$jonas = Cliente::where('nombres', 'like', '%JONAS%')
    ->where(function($q) {
        $q->where('apellido_paterno', 'like', '%NOH%')
          ->orWhere('apellido_materno', 'like', '%POOL%');
    })
    ->first();

if (!$jonas) {
    echo "Cliente Jonas no encontrado. Buscando todos los clientes con prestamos liquidados en febrero...\n\n";
    
    $prestamosLiquidadosFeb = Prestamo::whereIn('estado', ['Pagado', 'Liquidado'])
        ->with(['pagos' => function($q) {
            $q->orderBy('fecha_pago', 'desc');
        }, 'cliente'])
        ->get()
        ->filter(function($p) {
            $ultimoPago = $p->pagos->first();
            if ($ultimoPago) {
                $fecha = Carbon::parse($ultimoPago->fecha_pago);
                return $fecha->format('Y-m') === '2026-02';
            }
            return false;
        });
    
    echo "Clientes que liquidaron en febrero 2026:\n";
    foreach($prestamosLiquidadosFeb as $p) {
        echo "- ID " . $p->cliente_id . ": " . $p->cliente->nombres . " " . 
             $p->cliente->apellido_paterno . " " . $p->cliente->apellido_materno . 
             " (Préstamo #" . $p->id . ")\n";
    }
    exit;
}

$nombreCompleto = $jonas->nombres . ' ' . $jonas->apellido_paterno . ' ' . $jonas->apellido_materno;
echo "Cliente: " . $nombreCompleto . " (ID: " . $jonas->id . ")\n\n";

// Obtener todos sus préstamos
$prestamos = Prestamo::where('cliente_id', $jonas->id)
    ->with(['pagos' => function($q) {
        $q->orderBy('fecha_pago', 'desc');
    }])
    ->orderBy('fecha_entrega')
    ->get();

echo "Total préstamos de Jonas: " . $prestamos->count() . "\n\n";

echo "--- LISTADO DE PRÉSTAMOS ---\n";
foreach ($prestamos as $p) {
    echo "\nPréstamo #" . $p->id . ":\n";
    echo "  Fecha entrega: " . $p->fecha_entrega . "\n";
    echo "  Estado: " . $p->estado . "\n";
    echo "  Monto: $" . number_format($p->monto_total, 2) . "\n";
    
    if ($p->pagos->count() > 0) {
        $ultimoPago = $p->pagos->first();
        echo "  Último pago: " . $ultimoPago->fecha_pago . "\n";
        
        $capitalPagado = $p->pagos->sum('capital_pagado');
        $capitalTotal = $p->monto_autorizado ?? $p->monto_total;
        $saldo = max(0, $capitalTotal - $capitalPagado);
        
        echo "  Capital pagado: $" . number_format($capitalPagado, 2) . "\n";
        echo "  Saldo: $" . number_format($saldo, 2) . "\n";
        
        if ($saldo <= 0.01) {
            echo "  ✓ LIQUIDADO el " . $ultimoPago->fecha_pago . "\n";
        }
    } else {
        echo "  Sin pagos\n";
    }
}

echo "\n--- ANÁLISIS DE FIDELIZACIÓN ---\n\n";

// Verificar si liquidó #75 en febrero y tomó #89 después
$prestamo75 = $prestamos->firstWhere('id', 75);
$prestamo89 = $prestamos->firstWhere('id', 89);

if ($prestamo75) {
    echo "Préstamo #75:\n";
    $ultimoPago75 = $prestamo75->pagos->first();
    if ($ultimoPago75) {
        $fechaLiquidacion = Carbon::parse($ultimoPago75->fecha_pago);
        echo "  Fecha liquidación: " . $fechaLiquidacion->format('Y-m-d') . "\n";
        echo "  Mes: " . $fechaLiquidacion->translatedFormat('F Y') . "\n";
        
        if ($prestamo89) {
            echo "\nPréstamo #89:\n";
            $fechaEntrega89 = Carbon::parse($prestamo89->fecha_entrega);
            echo "  Fecha entrega: " . $fechaEntrega89->format('Y-m-d') . "\n";
            echo "  Estado: " . $prestamo89->estado . "\n";
            
            if ($fechaEntrega89 >= $fechaLiquidacion) {
                echo "\n✓ #89 fue entregado el mismo día o DESPUÉS de liquidar #75\n";
                echo "✓ Jonas SÍ se considera FIDELIZADO en febrero 2026\n";
            } else {
                echo "\n✗ #89 NO fue entregado después de liquidar #75\n";
            }
        } else {
            echo "\nPréstamo #89 no encontrado\n";
        }
    }
}

echo "\n";
