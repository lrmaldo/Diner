<?php

namespace App\Livewire\Caja;

use App\Models\Capitalizacion;
use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ArqueoCaja extends Component
{
    // Propiedades para Capitalizar (Modal)
    public $showCapitalizarModal = false;
    public $showSuccessModal = false;
    public $comentariosCapital = '';

    public $billetesCapital = [
        '1000' => 0, '500' => 0, '200' => 0, '100' => 0, '50' => 0, '20' => 0,
    ];

    public $monedasCapital = [
        '20' => 0, '10' => 0, '5' => 0, '2' => 0, '1' => 0, '0.5' => 0,
    ];

    public function mount()
    {
        if (!auth()->user()->hasRole('Administrador')) {
            abort(403, 'No tiene permisos para ver esta sección.');
        }
    }

    // Computed properties para el modal de Capitalizar
    public function getTotalBilletesCapitalProperty()
    {
        $total = 0;
        foreach ($this->billetesCapital as $denom => $qty) {
            $total += (float)$denom * (int)$qty;
        }
        return $total;
    }

    public function getTotalMonedasCapitalProperty()
    {
        $total = 0;
        foreach ($this->monedasCapital as $denom => $qty) {
            $total += (float)$denom * (int)$qty;
        }
        return $total;
    }

    public function getTotalGeneralCapitalProperty()
    {
        return $this->totalBilletesCapital + $this->totalMonedasCapital;
    }

    public function abrirCapitalizar()
    {
        $this->reset(['billetesCapital', 'monedasCapital', 'comentariosCapital']);
        $this->showCapitalizarModal = true;
    }

    public function guardarCapital()
    {
        $this->validate([
            'comentariosCapital' => 'nullable|string|max:255',
        ]);

        if ($this->totalGeneralCapital <= 0) {
            $this->dispatch('toast', message: 'El monto total debe ser mayor a 0.', type: 'error');
            return;
        }

        Capitalizacion::create([
            'monto' => $this->totalGeneralCapital,
            'desglose_billetes' => [
                'billetes' => $this->billetesCapital,
                'monedas' => $this->monedasCapital,
            ],
            'user_id' => auth()->id(),
            'comentarios' => $this->comentariosCapital,
        ]);

        $this->showCapitalizarModal = false;
        $this->showSuccessModal = true;
        
        // No es necesario emitir evento para recargar, Livewire refrescará el componente automáticamente 
        // y recalculará el Arqueo porque getDenominacionesProperty es una computed property que se evalúa al renderizar.
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
        // Ya contamos con la columna desglose_entrega en la tabla prestamos.
        
        $prestamosEntregados = Prestamo::where('estado', 'entregado')
            ->whereNotNull('desglose_entrega') 
            ->get();
            
        foreach ($prestamosEntregados as $p) {
             $sumarAlArqueo($p->desglose_entrega, -1); // Restar salida
        }

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
