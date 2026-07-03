<?php

namespace App\Livewire\Caja;

use App\Models\Capitalizacion;
use App\Models\Egreso;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Cambios extends Component
{
    public $pasoCambio = 'ingresa';

    public $comentariosCambio = '';

    public $billetesCambioEntrada = [
        '1000' => 0, '500' => 0, '200' => 0, '100' => 0, '50' => 0, '20' => 0,
    ];

    public $monedasCambioEntrada = [
        '20' => 0, '10' => 0, '5' => 0, '2' => 0, '1' => 0, '0_5' => 0,
    ];

    public $billetesCambioSalida = [
        '1000' => 0, '500' => 0, '200' => 0, '100' => 0, '50' => 0, '20' => 0,
    ];

    public $monedasCambioSalida = [
        '20' => 0, '10' => 0, '5' => 0, '2' => 0, '1' => 0, '0_5' => 0,
    ];

    public function mount(): void
    {
        if (! auth()->user()->hasRole('Administrador')) {
            abort(403, 'No tiene permisos para ver esta sección.');
        }
    }

    public function getTotalBilletesCambioEntradaProperty(): float
    {
        $total = 0;
        foreach ($this->billetesCambioEntrada as $denom => $qty) {
            $total += (float) $denom * (int) $qty;
        }

        return $total;
    }

    public function getTotalMonedasCambioEntradaProperty(): float
    {
        $total = 0;
        foreach ($this->monedasCambioEntrada as $denom => $qty) {
            $valor = $denom === '0_5' ? 0.5 : (float) $denom;
            $total += $valor * (int) $qty;
        }

        return $total;
    }

    public function getTotalCambioEntradaProperty(): float
    {
        return $this->totalBilletesCambioEntrada + $this->totalMonedasCambioEntrada;
    }

    public function getTotalBilletesCambioSalidaProperty(): float
    {
        $total = 0;
        foreach ($this->billetesCambioSalida as $denom => $qty) {
            $total += (float) $denom * (int) $qty;
        }

        return $total;
    }

    public function getTotalMonedasCambioSalidaProperty(): float
    {
        $total = 0;
        foreach ($this->monedasCambioSalida as $denom => $qty) {
            $valor = $denom === '0_5' ? 0.5 : (float) $denom;
            $total += $valor * (int) $qty;
        }

        return $total;
    }

    public function getTotalCambioSalidaProperty(): float
    {
        return $this->totalBilletesCambioSalida + $this->totalMonedasCambioSalida;
    }

    public function aceptarIngresoCambio(): void
    {
        if ($this->totalCambioEntrada <= 0) {
            $this->dispatch('toast', message: 'Debe ingresar al menos una denominacion en INGRESA.', type: 'error');

            return;
        }

        $this->pasoCambio = 'sale';
    }

    public function volverAIngresa(): void
    {
        $this->pasoCambio = 'ingresa';
    }

    public function guardarCambios(): void
    {
        if ($this->totalCambioEntrada <= 0) {
            $this->dispatch('toast', message: 'El total de INGRESA debe ser mayor a 0.', type: 'error');

            return;
        }

        if ($this->totalCambioSalida <= 0) {
            $this->dispatch('toast', message: 'El total de SALE debe ser mayor a 0.', type: 'error');

            return;
        }

        DB::transaction(function () {
            Capitalizacion::create([
                'monto' => $this->totalCambioEntrada,
                'origen_fondos' => 'externo',
                'desglose_billetes' => [
                    'billetes' => $this->billetesCambioEntrada,
                    'monedas' => $this->monedasCambioEntrada,
                ],
                'user_id' => auth()->id(),
                'comentarios' => $this->comentariosCambio !== '' ? $this->comentariosCambio : 'Ingreso por cambio de denominaciones',
            ]);

            Egreso::create([
                'origen' => 'caja',
                'monto' => $this->totalCambioSalida,
                'descripcion' => $this->comentariosCambio !== '' ? $this->comentariosCambio : 'Salida por cambio de denominaciones',
                'denominaciones' => [
                    'billetes' => $this->billetesCambioSalida,
                    'monedas' => $this->monedasCambioSalida,
                ],
                'user_id' => auth()->id(),
            ]);
        });

        $this->resetCambioForm();
        $this->dispatch('toast', message: 'Cambio aplicado correctamente.', type: 'success');
    }

    public function resetCambioForm(): void
    {
        $this->pasoCambio = 'ingresa';
        $this->comentariosCambio = '';

        $this->billetesCambioEntrada = [
            '1000' => 0, '500' => 0, '200' => 0, '100' => 0, '50' => 0, '20' => 0,
        ];
        $this->monedasCambioEntrada = [
            '20' => 0, '10' => 0, '5' => 0, '2' => 0, '1' => 0, '0_5' => 0,
        ];
        $this->billetesCambioSalida = [
            '1000' => 0, '500' => 0, '200' => 0, '100' => 0, '50' => 0, '20' => 0,
        ];
        $this->monedasCambioSalida = [
            '20' => 0, '10' => 0, '5' => 0, '2' => 0, '1' => 0, '0_5' => 0,
        ];
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.caja.cambios');
    }
}
