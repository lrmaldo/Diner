<?php

namespace App\Livewire\Caja;

use App\Models\Capitalizacion;
use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ArqueoCaja extends Component
{
    public function mount()
    {
        if (!auth()->user()->hasRole('Administrador')) {
            abort(403, 'No tiene permisos para ver esta sección.');
        }
    }

    public function getDenominacionesProperty()
    {
        // Inicializar contadores en 0
        $caja = [
            '1000' => 0, '500' => 0, '200' => 0, '100' => 0, '50' => 0, '20' => 0,
            '10' => 0, '5' => 0, '2' => 0, '1' => 0, '0.5' => 0
        ];

        // Helper para sumar al arqueo
        $sumarAlArqueo = function ($desglose, $factor = 1) use (&$caja) {
            if (!$desglose) return;
            
            // Si el desglose viene como string JSON (algunos casos en BD antigua), decodificar
            if (is_string($desglose)) {
                $desglose = json_decode($desglose, true);
            }
            
            // Normalizar estructura: a veces viene directo ['1000' => 5], a veces ['billetes' => [...]]
            $billetes = $desglose['billetes'] ?? $desglose ?? [];
            $monedas = $desglose['monedas'] ?? [];
            
            // Sumar billetes
            foreach ($billetes as $denom => $cant) {
                // Filtrar claves no numéricas o anidadas incorrectas si la estructura varía
                if (isset($caja[(string)$denom]) && is_numeric($cant)) {
                    $caja[(string)$denom] += ((int)$cant * $factor);
                }
            }
            
            // Sumar monedas
            foreach ($monedas as $denom => $cant) {
                if (isset($caja[(string)$denom]) && is_numeric($cant)) {
                    $caja[(string)$denom] += ((int)$cant * $factor);
                }
            }
        };

        // 1. Sumar Capitalizaciones (Entradas)
        $capitalizaciones = Capitalizacion::all();
        foreach ($capitalizaciones as $cap) {
            $sumarAlArqueo($cap->desglose_billetes, 1);
        }

        // 2. Sumar Pagos Recibidos (Entradas)
        // Solo pagos con desglose_efectivo guardado
        $pagos = Pago::whereNotNull('desglose_efectivo')->get();
        foreach ($pagos as $pago) {
            $sumarAlArqueo($pago->desglose_efectivo, 1);
        }

        // 3. Restar Entregas de Crédito (Salidas)
        // NOTA: Actualmente el desglose de entregas NO se guarda en la tabla prestamos.
        // El componente EntregaCredito guarda los datos en la bitácora como comentario de texto,
        // por lo que no es posible reconstruir el desglose de billetes para las salidas.
        // TODO: Implementar guardado de desglose_entrega en tabla prestamos para cálculo preciso.
        // Por ahora, el arqueo solo considera entradas (capitalizaciones y pagos).

        return $caja;
    }
    
    public function getTotalSistemaProperty()
    {
        $total = 0;
        $denominaciones = $this->getDenominacionesProperty();
        
        foreach ($denominaciones as $denom => $cantidad) {
            $total += (float)$denom * (int)$cantidad;
        }
        
        return $total;
    }

    public function render()
    {
        return view('livewire.caja.arqueo-caja', [
            'denominaciones' => $this->getDenominacionesProperty()
        ]);
    }
}
