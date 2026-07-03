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

    public function getDiferenciaCambioProperty(): float
    {
        return round($this->totalCambioSalida - $this->totalCambioEntrada, 2);
    }

    public function getMontosCuadranProperty(): bool
    {
        return $this->totalCambioEntrada > 0 && abs($this->diferenciaCambio) < 0.005;
    }

    public function aceptarIngresoCambio(): void
    {
        if ($this->totalCambioEntrada <= 0) {
            $this->dispatch('toast', message: 'Debe ingresar al menos una denominacion en INGRESA.', type: 'error');

            return;
        }

        $this->pasoCambio = 'sale';
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

        // Medida de seguridad: lo que sale debe ser exactamente igual a lo que ingresó.
        // No se permite dar de menos ni de más para no afectar el arqueo de caja.
        if (! $this->montosCuadran) {
            $diff = $this->diferenciaCambio;
            $mensaje = $diff < 0
                ? 'Monto incompleto: falta dar $'.number_format(abs($diff), 2).' para igualar lo que ingresó ($'.number_format($this->totalCambioEntrada, 2).').'
                : 'El monto que sale excede por $'.number_format($diff, 2).' lo que ingresó ($'.number_format($this->totalCambioEntrada, 2).').';
            $this->dispatch('toast', message: $mensaje, type: 'error');

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
